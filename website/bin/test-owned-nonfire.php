#!/usr/bin/env php
<?php
/**
 * Burnley + Manchester non-fire ownership.
 * Fire services stay nationwide. Bolton is not an owned town.
 */
declare(strict_types=1);

ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';
require_once __DIR__ . '/../includes/owned-nonfire.php';
require_once __DIR__ . '/../includes/matrix-page.php';
require_once __DIR__ . '/../includes/wave1.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

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

$ok(ownedNonFireTown('Bolton') === null, 'Bolton is not an owned town');
$ok(ownedNonFireTown('Burnley') !== null, 'Burnley is owned');
$ok(ownedNonFireTown('manchester') !== null, 'Manchester slug resolves');
$ok(ownedNonFirePair('fire-alarms', 'Burnley') === null, 'no fire-alarms × Burnley landing');
$ok(ownedNonFirePair('emergency-lighting', 'Manchester') === null, 'no emergency-lighting × Manchester landing');
$ok(ownedNonFirePair('fire-risk-assessments', 'Burnley') === null, 'no FRA × Burnley landing');
$ok(ownedNonFirePair('electrical', 'Bolton') === null, 'no electrical × Bolton landing');
$ok(ownedNonFirePair('kitchens', 'Burnley') === null, 'construction trades are not top landings');
$ok(ownedNonFirePair('electrical', 'Burnley') !== null, 'electrical × Burnley exists');
$ok(ownedNonFirePair('legionella-risk-assessment', 'Manchester') !== null, 'legionella × Manchester exists');

$expected = ownedTopNonFireServiceSlugs();
$ok(count($expected) === 10, 'ten top non-fire services');
foreach ($expected as $slug) {
    $ok(!ownedIsNationwideFireService($slug), $slug . ' is not a fire service');
    foreach (['Burnley', 'Manchester'] as $town) {
        $pair = ownedNonFirePair($slug, $town);
        $ok($pair !== null, "{$slug} × {$town} copy");
        if ($pair !== null) {
            $blob = implode(' ', $pair['paragraphs']);
            $ok(stripos($blob, '£') === false, "{$slug} × {$town} has no invented £ price");
        }
    }
}

$leg = ownedNonFirePair('legionella-risk-assessment', 'Burnley');
$ok($leg !== null && stripos(implode(' ', $leg['paragraphs']), 'price on application') !== false, 'Burnley Legionella is POA');
$asb = ownedNonFirePair('asbestos-survey', 'Manchester');
$ok($asb !== null && stripos(implode(' ', $asb['paragraphs']), 'Licensed removal is by others') !== false, 'asbestos removal stays with others');

$hub = wave1Hub('burnley-property-compliance');
$ok(is_array($hub), 'Burnley quality hub is registered');
$ok(is_file(SITE_ROOT . '/pages/burnley-property-compliance.php'), 'Burnley hub file exists');
$ok(is_array($hub) && stripos((string)$hub['honest'], 'nationwide fire') !== false, 'Burnley hub sends fire nationwide');

ob_start();
renderAreaHubPage('Burnley');
$burnleyHub = (string)ob_get_clean();
$ok(str_contains($burnleyHub, 'data-owned-area="burnley"'), 'Burnley area hub has owned non-fire section');
$ok(str_contains($burnleyHub, '/pages/electrical/burnley'), 'Burnley hub links electrical landing');
$ok(!str_contains($burnleyHub, '/pages/fire-alarms/burnley'), 'Burnley hub has no fire-alarms town URL');
$ok(!str_contains($burnleyHub, '/pages/emergency-lighting/burnley'), 'Burnley hub has no emergency-lighting town URL');

ob_start();
renderAreaHubPage('Manchester');
$mancHub = (string)ob_get_clean();
$ok(str_contains($mancHub, 'data-owned-area="manchester"'), 'Manchester area hub has owned non-fire section');
$ok(str_contains($mancHub, '/pages/gas-systems/manchester'), 'Manchester hub links gas landing');
$ok(!str_contains($mancHub, '/pages/fire-alarms/manchester'), 'Manchester hub has no fire-alarms town URL');

ob_start();
renderAreaHubPage('Stockport');
$stockport = (string)ob_get_clean();
$ok(!str_contains($stockport, 'data-owned-area='), 'Stockport area hub stays on the shared template');
$ok(!preg_match('#/pages/(gas-systems|electrical|fire-alarms)/[a-z0-9\-]+#', $stockport), 'Stockport hub still has no service×area URLs');

ob_start();
renderServiceAreaPage('electrical', 'Burnley');
$elec = (string)ob_get_clean();
$ok(str_contains($elec, 'data-owned-nonfire="burnley"'), 'electrical × Burnley renders owned copy');
$ok(str_contains($elec, 'BB10'), 'electrical × Burnley names BB postcodes');
$ok(!str_contains($elec, '/pages/fire-alarms/burnley'), 'electrical landing does not link a fire town page');

ob_start();
renderServiceAreaPage('fire-alarms', 'Burnley');
$fire = (string)ob_get_clean();
$ok(!str_contains($fire, 'data-owned-nonfire='), 'fire-alarms × Burnley is not an owned landing');

$matrix = icomplyRenderServiceAreaHtml('gas-systems', 'Manchester');
$ok(str_contains($matrix, 'data-owned-nonfire="manchester"'), 'static matrix includes gas × Manchester copy');
$ok(str_contains($matrix, 'M1'), 'gas × Manchester names M postcodes');
$fireMatrix = icomplyRenderServiceAreaHtml('fire-alarms', 'Manchester');
$ok(!str_contains($fireMatrix, 'data-owned-nonfire='), 'static matrix does not own fire × Manchester');

echo "\n{$pass} passed, {$fail} failed\n";
exit($fail === 0 ? 0 : 1);
