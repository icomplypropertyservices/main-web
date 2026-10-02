#!/usr/bin/env php
<?php
/**
 * Published service prices come only from includes/service-prices.php.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';

$fail = 0;
$pass = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        echo "[PASS] {$msg}\n";
    } else {
        $fail++;
        echo "[FAIL] {$msg}\n";
    }
};

$catalog = icomplyServicePriceCatalog();
$ok(array_keys($catalog) === ['eicr', 'fra', 'gas-safety', 'compliance-bundle'], 'catalog ids');
$ok($catalog['eicr']['amount'] === 249 && $catalog['eicr']['display'] === '£249', 'EICR £249');
$ok($catalog['fra']['amount'] === 350 && $catalog['fra']['display'] === '£350', 'FRA £350');
$ok($catalog['gas-safety']['amount'] === 85 && $catalog['gas-safety']['display'] === '£85', 'gas safety £85');
$ok($catalog['compliance-bundle']['amount'] === 650 && $catalog['compliance-bundle']['display'] === '£650', 'compliance bundle £650');

$expect = [
    'electrical' => '£249',
    'fire-risk-assessments' => '£350',
    'gas-systems' => '£85',
    'landlord-compliance' => '£650',
];
foreach ($expect as $slug => $display) {
    ob_start();
    renderServiceHubPage($slug);
    $html = (string)ob_get_clean();
    $ok(str_contains($html, $display), "{$slug} hub shows {$display}");
    foreach ($expect as $other => $otherDisplay) {
        if ($otherDisplay === $display) {
            continue;
        }
        $ok(!str_contains($html, $otherDisplay), "{$slug} hub does not show {$otherDisplay}");
    }
}

ob_start();
renderServiceHubPage('fire-alarms');
$fireAlarms = (string)ob_get_clean();
$ok(!str_contains($fireAlarms, '£350') && !str_contains($fireAlarms, '£249'), 'fire alarm hub has no EICR/FRA price');

ob_start();
renderKeywordPage('rewire');
$rewire = (string)ob_get_clean();
$ok(!preg_match('/£\s*\d/', $rewire), 'rewire keyword has no pound price');

ob_start();
renderKeywordPage('eicr-price');
$eicrPrice = (string)ob_get_clean();
$ok(str_contains($eicrPrice, '£249') && !str_contains($eicrPrice, '£85'), 'eicr-price shows £249 only');

ob_start();
renderKeywordAreaPage('cp12', 'Stockport');
$cp12 = (string)ob_get_clean();
$ok(str_contains($cp12, '£85') && str_contains($cp12, 'Stockport'), 'cp12 in Stockport shows £85');

ob_start();
renderAreaHubPage('Stockport');
$area = (string)ob_get_clean();
foreach (['£249', '£350', '£85', '£650'] as $display) {
    $ok(str_contains($area, $display), "Stockport area hub shows {$display}");
}

$guide = [
    ['items' => [
        ['name' => 'EICR', 'from' => '£1'],
        ['name' => 'EICR — 1-bed flat', 'from' => '£129'],
        ['name' => 'Landlord gas safety (CP12)', 'from' => '£69'],
        ['name' => 'FRA (fire risk assessment)', 'from' => 'POA'],
        ['name' => 'Compliance bundle', 'from' => '£199'],
        ['name' => 'Landlord essentials (EICR + gas safety)', 'from' => '£199'],
        ['name' => 'Commercial gas safety', 'from' => '£85'],
        ['name' => 'LED emergency conversion', 'from' => '£85'],
    ]],
];
$synced = icomplySyncGuidePriceCategories($guide);
$byName = [];
foreach ($synced[0]['items'] as $item) {
    $byName[$item['name']] = $item['from'];
}
$ok($byName['EICR'] === '£249', 'guide sync EICR');
$ok($byName['EICR — 1-bed flat'] === 'POA', 'guide sync does not invent an EICR size price');
$ok($byName['Landlord gas safety (CP12)'] === '£85', 'guide sync gas safety');
$ok($byName['FRA (fire risk assessment)'] === '£350', 'guide sync FRA');
$ok($byName['Compliance bundle'] === '£650', 'guide sync bundle');
$ok($byName['Landlord essentials (EICR + gas safety)'] === 'POA', 'guide sync does not price a different bundle');
$ok($byName['Commercial gas safety'] === 'POA', 'guide sync does not price commercial gas');
$ok($byName['LED emergency conversion'] === 'POA', 'guide sync does not keep a stray £85');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
