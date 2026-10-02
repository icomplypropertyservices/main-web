<?php
/**
 * Quote builder rates, tiers, rounding and bundle rules.
 * Usage: php website/bin/check-quote-builder.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once SITE_ROOT . '/includes/quote-builder.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$fail = 0;
$pass = 0;

function qbSay(bool $ok, string $label, string $detail = ''): void
{
    global $fail, $pass;
    if ($ok) {
        $pass++;
        echo "[PASS] {$label}\n";
        return;
    }
    $fail++;
    echo "[FAIL] {$label}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
}

$catalog = quoteBuilderCatalog();
$byId = [];
foreach ($catalog['services'] as $service) {
    if (is_array($service) && isset($service['id'])) {
        $byId[(string)$service['id']] = $service;
    }
}
qbSay(($byId['eicr']['pence'] ?? 0) === 24900, 'approved EICR list is £249');
qbSay(($byId['fra']['pence'] ?? 0) === 35000, 'approved FRA list is £350');
qbSay(($byId['gas']['pence'] ?? 0) === 8500, 'approved gas safety list is £85');
qbSay(($byId['bundle']['pence'] ?? 0) === 65000, 'approved bundle list is £650');

$oneEach = quoteBuilderCalculate(1, ['eicr' => 1, 'fra' => 1, 'gas' => 1]);
qbSay($oneEach['bundleAuto'] === true && $oneEach['totalPence'] === 65000, '1× FRA+EICR+gas is the £650 bundle', (string)$oneEach['totalLabel']);
qbSay($oneEach['nextTierLabel'] === '1 more property unlocks 5% off certificates and inspections.', '1 property is one step from 5%', (string)$oneEach['nextTierLabel']);

$eicr10 = quoteBuilderCalculate(10, ['eicr' => 1]);
qbSay($eicr10['tierId'] === 'T2' && $eicr10['totalPence'] === 224000, '10× EICR is £224 × 10 = £2,240', (string)$eicr10['totalLabel']);
qbSay($eicr10['nextTierLabel'] === '1 more property unlocks 12.5% off certificates and inspections.', '10 properties are one step from 12.5%', (string)$eicr10['nextTierLabel']);
qbSay(($eicr10['lines'][0]['unitPence'] ?? 0) === 22400, 'EICR unit rounds £224.10 to £224');

$bundle10 = quoteBuilderCalculate(10, ['bundle' => 1]);
qbSay($bundle10['totalPence'] === 585000, '10× bundle at 10% is £5,850', (string)$bundle10['totalLabel']);

$trio = quoteBuilderCalculate(10, ['fra' => 1, 'eicr' => 1, 'gas' => 1]);
qbSay($trio['bundleAuto'] === true && $trio['totalPence'] === 585000, 'FRA+EICR+gas uses the bundle, not three lines', (string)$trio['totalLabel']);
qbSay(count(array_filter($trio['lines'], static fn($l) => !$l['poa'])) === 1, 'bundle path is a single priced line');

$stacked = quoteBuilderCalculate(10, ['bundle' => 1, 'fra' => 1, 'eicr' => 1, 'gas' => 1]);
qbSay($stacked['totalPence'] === 585000 && $stacked['bundleExplicit'] === true, 'bundle plus separates does not stack', (string)$stacked['totalLabel']);

$portfolio = quoteBuilderCalculate(25, ['eicr' => 1, 'gas' => 1]);
qbSay($portfolio['tierId'] === 'T4' && $portfolio['totalPence'] === 710000, '25× EICR+gas at 15% is £7,100', (string)$portfolio['totalLabel']);

$withCall = quoteBuilderCalculate(25, ['eicr' => 1, 'gas' => 1, 'callout-first' => 1, 'callout-extra' => 2]);
qbSay($withCall['totalPence'] === 710000 + 8500 + 11000, 'call-out £85 + 2×£55 is not discounted or multiplied', (string)$withCall['totalLabel']);

$callOnly = quoteBuilderCalculate(10, ['callout-first' => 1]);
qbSay($callOnly['tierId'] === 'T2' && $callOnly['totalPence'] === 8500 && $callOnly['savingPence'] === 0, '10 properties still leaves a call-out at £85');

$fra4 = quoteBuilderCalculate(4, ['fra' => 1]);
qbSay($fra4['tierId'] === 'T1' && ($fra4['lines'][0]['unitPence'] ?? 0) === 33300 && $fra4['totalPence'] === 133200, '4× FRA rounds £332.50 to £333', (string)$fra4['totalLabel']);

$eicr11 = quoteBuilderCalculate(11, ['eicr' => 1]);
qbSay($eicr11['tierId'] === 'T3' && ($eicr11['lines'][0]['unitPence'] ?? 0) === 21800, '11× EICR at 12.5% rounds to £218', (string)($eicr11['lines'][0]['unitLabel'] ?? ''));

$nurse = quoteBuilderCalculate(2, ['nurse' => 1]);
qbSay($nurse['totalPence'] === 39998 && $nurse['savingPence'] === 0, '2× nurse-call stays £199.99 with no discount', (string)$nurse['totalLabel']);

$mix = quoteBuilderCalculate(10, ['eicr' => 1, 'downlight' => 2, 'callout-first' => 1]);
qbSay($mix['totalPence'] === 224000 + 8000 + 8500, 'downlights stay £40 each and are not multiplied by properties', (string)$mix['totalLabel']);

$poa = quoteBuilderCalculate(3, ['eicr-1bed' => 1, 'travel' => 1]);
qbSay($poa['totalLabel'] === 'POA' && $poa['totalPence'] === 0 && $poa['hasPoa'] === true, 'POA lines add no pounds');

$edges = [
    1 => 'T0',
    2 => 'T1',
    5 => 'T1',
    6 => 'T2',
    10 => 'T2',
    11 => 'T3',
    20 => 'T3',
    21 => 'T4',
    40 => 'T4',
];
foreach ($edges as $n => $id) {
    $row = quoteBuilderCalculate($n, ['aov' => 1]);
    qbSay($row['tierId'] === $id, "tier {$id} at {$n} properties", $row['tierId']);
}

$bundle21 = quoteBuilderCalculate(21, ['bundle' => 1]);
qbSay(($bundle21['lines'][0]['unitPence'] ?? 0) === 55300, '21× bundle at 15% rounds £552.50 to £553', (string)($bundle21['lines'][0]['unitLabel'] ?? ''));
qbSay($bundle21['nextTierLabel'] === '', '21 properties have no further tier', (string)$bundle21['nextTierLabel']);

$extraOnly = quoteBuilderCalculate(3, ['callout-extra' => 2]);
qbSay($extraOnly['totalPence'] === 8500 + 11000 && $extraOnly['calloutImplied'] === true, 'extra hours include the £85 first hour and are not multiplied', (string)$extraOnly['totalLabel']);
qbSay($extraOnly['calloutNote'] === 'Additional hours include the £85 first-hour call-out.', 'call-out note names the first hour');

quoteBuilderRememberForm([
    'name' => 'Alex Landlord',
    'property_count' => '10',
    'svc_eicr' => 'EICR — £249',
    'notes' => 'Gate code',
    'csrf' => 'should-not-stick',
], ['Please enter a UK postcode.']);
$flash = quoteBuilderTakeFormFlash();
qbSay(($flash['errors'][0] ?? '') === 'Please enter a UK postcode.' && ($flash['post']['svc_eicr'] ?? '') === 'EICR — £249' && ($flash['post']['property_count'] ?? '') === '10', 'failed quote returns the selection');
qbSay(!isset($flash['post']['csrf']) && quoteBuilderTakeFormFlash()['post'] === [], 'quote flash is one-shot and drops the token');

$catalogRaw = (string)file_get_contents(SITE_ROOT . '/data/quote-builder.json');
$pageRaw = (string)file_get_contents(SITE_ROOT . '/get-a-quote.php');
$jsRaw = (string)file_get_contents(SITE_ROOT . '/assets/js/quote-builder.js');
$blob = $catalogRaw . $pageRaw . $jsRaw;
foreach (['From £149', 'From £199', 'From £299', 'From £349', 'subcontract'] as $banned) {
    qbSay(stripos($blob, $banned) === false, "no banned copy: {$banned}");
}
qbSay(str_contains($catalogRaw, 'Gas Safe registered engineers'), 'gas line credits Gas Safe registered engineers');
qbSay(stripos($blob, 'NICEIC') === false, 'quote builder does not claim NICEIC');

$lead = quoteBuilderLeadFromPost([
    'name' => 'Alex Landlord',
    'email' => 'alex@example.com',
    'phone' => '07517806082',
    'postcode' => 'sk25de',
    'property_count' => '10',
    'notes' => 'Side gate',
    'svc_eicr' => '1',
]);
qbSay($lead['errors'] === [] && ($lead['lead']['quote_total'] ?? '') === '£2,240', 'lead recalculates 10× EICR to £2,240', implode('; ', $lead['errors']));
qbSay(($lead['lead']['postcode'] ?? '') === 'SK2 5DE', 'postcode normalised', (string)($lead['lead']['postcode'] ?? ''));

$bad = quoteBuilderLeadFromPost(['name' => '', 'email' => 'nope', 'phone' => '', 'postcode' => 'XYZ', 'property_count' => '0']);
qbSay($bad['errors'] !== [], 'empty lead is rejected');

echo "\n{$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
