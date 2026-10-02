#!/usr/bin/env php
<?php
/**
 * Open job lanes landed from the remaining draft PRs.
 * New hubs only. Published phone, WhatsApp, EICR £249, gas £85, FRA £350 and the £650 bundle stay.
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

$kw = getMajorKeywords();
$fabric = openJobRows(openJobFabricFile());
$building = openJobRows(openJobBuildingFile());
$gas = openJobRows(openJobGasFile());
$electrical = openJobRows(openJobElectricalFile());
$core = openJobRows(openJobCoreFile());

$ok(count($fabric) === 207, 'building fabric jobs=' . count($fabric));
$ok(count($building) === 247, 'building jobs=' . count($building));
$ok(count($gas) === 223, 'gas jobs=' . count($gas));
$ok(count($electrical) === 340, 'electrical jobs=' . count($electrical));
$ok(count($core) === 7, 'core extra jobs=' . count($core));

$missing = [];
foreach ([$fabric, $building, $gas, $electrical, $core] as $jobs) {
    foreach ($jobs as $job) {
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '' || !isset($kw[$slug])) {
            $missing[] = $slug;
        }
    }
}
$ok($missing === [], 'every open-lane slug is in the catalogue' . ($missing ? ' missing=' . implode(',', array_slice($missing, 0, 8)) : ''));

$fabricSlug = 'access-equipment-survey-support';
$intro = (string)($kw[$fabricSlug]['intro'] ?? '');
$ok(isset($kw[$fabricSlug]) && str_contains($intro, 'reached safely'), 'fabric copy is specific on ' . $fabricSlug);
$ok(!empty($kw[$fabricSlug]['hub_only']), $fabricSlug . ' is a new hub-only page');

$ok(isset($kw['rewire']) && empty($kw['rewire']['hub_only']), 'rewire stays on the town matrix');
$ok(isset($kw['boiler']) && empty($kw['boiler']['hub_only']), 'boiler stays on the town matrix');
$ok(isset($kw['eicr']) && ($kw['eicr']['service'] ?? '') === 'electrical', 'eicr stays electrical');
$ok(isset($kw['gas-safety']) && ($kw['gas-safety']['service'] ?? '') === 'gas-systems', 'gas-safety stays gas-systems');
$ok(!isset($kw['tunstall-nurse-call']), 'tunstall-nurse-call stays unpublished');

$newGas = 0;
$gasPound = [];
foreach ($gas as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '' || empty($kw[$slug]['gas_open_lane'])) {
        continue;
    }
    $newGas++;
    $ok(!empty($kw[$slug]['hub_only']), $slug . ' gas hub is hub-only');
    $blob = (string)($kw[$slug]['intro'] ?? '') . (string)($kw[$slug]['body'] ?? '') . (string)($kw[$slug]['meta_desc'] ?? '');
    if (icomplyBlobHasInventedPrice($blob)) {
        $gasPound[] = $slug;
    }
}
$ok($newGas > 0, 'new gas hubs=' . $newGas);
$cp12Body = (string)($kw['how-much-is-a-cp12']['body'] ?? '');
$ok(str_contains($cp12Body, '£85') && !str_contains($cp12Body, 'no catalogue price'), 'new CP12 price hub cites £85');
$ok($gasPound === [], 'new gas hubs have no invented £' . ($gasPound ? ' ' . implode(',', array_slice($gasPound, 0, 6)) : ''));

$newElec = 0;
$elecPound = [];
foreach ($electrical as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '' || empty($kw[$slug]['electrical_open_lane'])) {
        continue;
    }
    $newElec++;
    if (empty($kw[$slug]['hub_only'])) {
        $ok(false, $slug . ' electrical hub is hub-only');
    }
    $blob = (string)($kw[$slug]['intro'] ?? '') . (string)($kw[$slug]['body'] ?? '') . (string)($kw[$slug]['meta_desc'] ?? '') . json_encode($kw[$slug]['faq'] ?? []);
    if (icomplyBlobHasInventedPrice($blob)) {
        $elecPound[] = $slug;
    }
}
$ok($newElec > 100, 'new electrical hubs=' . $newElec);
$ok($elecPound === [], 'new electrical hubs have no invented £' . ($elecPound ? ' ' . implode(',', array_slice($elecPound, 0, 6)) : ''));

$matrix = array_fill_keys(getElectricalGasMatrixKeywordSlugs(), true);
$ok(isset($matrix['rewire']) && isset($matrix['boiler']), 'town matrix still includes rewire and boiler');
$ok(!isset($matrix['hmo-eicr']) && !isset($matrix[$fabricSlug]), 'new hubs stay out of the town matrix');

$ok(PHONE === '07517806082', 'phone unchanged');
$ok(WHATSAPP === '447517806082', 'whatsapp unchanged');
$eicrSrc = (string)file_get_contents(SITE_ROOT . '/includes/eicr-lane.php');
$gasSrc = (string)file_get_contents(SITE_ROOT . '/includes/gas-job-lane.php');
$ok(str_contains($eicrSrc, '£249'), 'EICR list price still £249');
$ok(str_contains($gasSrc, '£85'), 'gas list price still £85');
$fra = SITE_ROOT . '/includes/fra-job-lane.php';
$ok(is_file($fra) && str_contains((string)file_get_contents($fra), '£350'), 'FRA list price still £350');
$came = (string)file_get_contents(SITE_ROOT . '/data/barrier-manufacturers.json');
$ok(str_contains($came, 'CAME partner'), 'CAME stays the barrier partner');

ob_start();
renderKeywordPage($fabricSlug);
$fabricHtml = (string)ob_get_clean();
$ok(stripos($fabricHtml, '<!DOCTYPE') !== false && str_contains($fabricHtml, 'reached safely'), 'render fabric hub');

ob_start();
renderKeywordPage('eicr');
$eicrHtml = (string)ob_get_clean();
$ok(str_contains($eicrHtml, '£249') && str_contains($eicrHtml, '07517806082'), 'EICR hub still shows £249 and the phone');

$GAS_JOB = 'gas-safety';
ob_start();
require SITE_ROOT . '/includes/gas-job-lane.php';
$gasHtml = (string)ob_get_clean();
$ok(str_contains($gasHtml, '£85') && str_contains($gasHtml, 'wa.me/447517806082'), 'gas job hub still shows £85 and WhatsApp');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
