#!/usr/bin/env php
<?php
/**
 * Manchester + Burnley non-fire service×area pilot.
 * Confirms slugs, export paths, guide prices, and that fire stays hub-only.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';
require_once __DIR__ . '/../includes/matrix-page.php';

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

$errors = jackPilotAreaSlugErrors();
$ok($errors === [], 'burnley + manchester slugs match areas.json' . ($errors ? ' (' . implode('; ', $errors) . ')' : ''));
$ok(areaSlug('Burnley') === 'burnley', 'Burnley slug is burnley');
$ok(areaSlug('Manchester') === 'manchester', 'Manchester slug is manchester');
$ok(areaFromSlug('burnley') === 'Burnley', 'areas.json resolves burnley');
$ok(areaFromSlug('manchester') === 'Manchester', 'areas.json resolves manchester');

$services = getJackPilotServices();
$fire = getFireServiceSlugs();
$ok(!isset($services['fire-alarms']) && !isset($services['emergency-lighting']), 'pilot services exclude fire');
$ok(isset($services['electrical']) && isset($services['gas-systems']), 'pilot includes electrical and gas');
$ok(isset($services['cctv']) && isset($services['access-control']), 'pilot includes CCTV and access control');
$ok(isset($services['legionella-risk-assessment']) && isset($services['asbestos-survey']), 'pilot includes legionella and asbestos');
$ok(isset($services['ev-charging']) && isset($services['plumbing']) && isset($services['building-maintenance']), 'pilot includes EV, plumbing and building');
foreach ($fire as $slug) {
    if (isset($services[$slug])) {
        $ok(false, "fire service leaked into pilot: {$slug}");
    }
}
$ok(true, 'fire slug list stays out of pilot services (' . count($fire) . ' fire)');

$paths = jackPilotExportPaths();
$expect = count($services) * 2;
$ok(count($paths) === $expect, 'export paths=' . count($paths) . " expect {$expect}");
$flip = array_flip($paths);
$ok(isset($flip['/pages/electrical/manchester']) && isset($flip['/pages/electrical/burnley']), 'electrical × both towns');
$ok(isset($flip['/pages/gas-systems/manchester']) && isset($flip['/pages/gas-systems/burnley']), 'gas × both towns');
$ok(isset($flip['/pages/ev-charging/manchester']) && isset($flip['/pages/plumbing/burnley']), 'EV and plumbing landings');
$ok(isset($flip['/pages/legionella-risk-assessment/burnley']) && isset($flip['/pages/building-maintenance/manchester']), 'water and building landings');
$ok(!isset($flip['/pages/fire-alarms/manchester']) && !isset($flip['/pages/electrical/stockport']), 'no fire and no Stockport service×area');

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'jackPilotExportPaths'), 'static-export adds pilot paths');
$ok(!str_contains($exportSrc, 'every service has every area'), 'nationwide service×area loop removed');

$samples = [
    'electrical' => 'manchester',
    'gas-systems' => 'burnley',
    'cctv' => 'manchester',
    'access-control' => 'burnley',
    'legionella-risk-assessment' => 'manchester',
    'asbestos-survey' => 'burnley',
    'ev-charging' => 'manchester',
    'plumbing' => 'burnley',
    'building-maintenance' => 'manchester',
];
foreach ($samples as $slug => $town) {
    $html = icomplyRenderServiceAreaHtml($slug, $town);
    $name = jackPilotAreaName($town);
    $ok($html !== '' && stripos($html, '<!DOCTYPE') !== false && $name !== null && str_contains($html, $name), "render {$slug}/{$town}");
    $ok(!str_contains($html, '/pages/fire-alarms/' . $town), "{$slug}/{$town} does not link fire×town");
    $ok(str_contains($html, '/pages/services/fire-alarms'), "{$slug}/{$town} links out to fire hub");
}

$elec = icomplyRenderServiceAreaHtml('electrical', 'Manchester');
$gas = icomplyRenderServiceAreaHtml('gas-systems', 'Burnley');
$leg = icomplyRenderServiceAreaHtml('legionella-risk-assessment', 'manchester');
$asb = icomplyRenderServiceAreaHtml('asbestos-survey', 'burnley');
$cctv = icomplyRenderServiceAreaHtml('cctv', 'manchester');
$ok(str_contains($elec, '£249') && str_contains($elec, 'EICR'), 'electrical Manchester shows EICR £249');
$ok(str_contains($gas, '£85'), 'gas Burnley shows £85 guide');
$ok(!preg_match('/£\s*\d/', $leg), 'legionella stays POA with no £ figure');
$ok(!preg_match('/£\s*\d/', $asb), 'asbestos stays POA with no £ figure');
$ok(!preg_match('/£\s*\d/', $cctv), 'CCTV landing does not invent a £ price');
$ok(icomplyRenderServiceAreaHtml('fire-alarms', 'manchester') === '', 'fire × Manchester is not rendered');
$ok(icomplyRenderServiceAreaHtml('electrical', 'Stockport') === '', 'electrical × Stockport is not rendered');
$ok(str_contains($elec, '/pages/plumbing/manchester') && str_contains($elec, '/pages/cctv/manchester'), 'electrical landing links plumbing and CCTV hubs');
$ok(str_contains($gas, '/pages/access-control/burnley') && str_contains($gas, '/pages/building-maintenance/burnley'), 'gas landing links security and building');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
