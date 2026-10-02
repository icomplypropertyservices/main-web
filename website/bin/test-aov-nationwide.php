#!/usr/bin/env php
<?php
/**
 * AOV × nationwide areas: full town matrix, POA copy, fire-protection
 * adjacent hubs, and no /pages/aov-air-handling/{town} in the sitemap.
 *
 * Usage: php website/bin/test-aov-nationwide.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';
require_once __DIR__ . '/../includes/seo.php';
require_once __DIR__ . '/../includes/matrix-page.php';
require_once __DIR__ . '/../includes/sitemap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(8));
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

$areas = getAreas();
$aovKw = getKeywordsForService('aov-air-handling');
$matrix = getAovMatrixKeywordSlugs();
$featured = getAovFeaturedKeywordSlugs();
$adjacent = getFireProtectionAdjacentServices();

$ok(count($areas) >= 150, 'areas=' . count($areas));
$ok(count($aovKw) >= 60, 'aov keywords=' . count($aovKw));
$ok(count($matrix) === count($aovKw), 'matrix slugs match keyword catalogue');
$ok(isAovNationwideService('aov-air-handling'), 'aov-air-handling is the nationwide service');
$ok(isPoaService('aov-air-handling'), 'AOV service quotes are POA');

foreach (['aov-installation', 'aov-maintenance', 'automatic-opening-vent', 'smoke-shaft-aov', 'stairwell-smoke-vent'] as $slug) {
    $ok(isset($aovKw[$slug]), "required AOV slug {$slug}");
}
foreach ($featured as $slug) {
    $ok(isset($aovKw[$slug]), "featured AOV slug {$slug}");
}
$services = getServices();
foreach ($adjacent as $slug) {
    $ok(isset($services[$slug]), "fire-adjacent hub {$slug}");
}

$expectServiceArea = count(getAovNationwideServices()) * count($areas);
$expectKeywordArea = count($matrix) * count($areas);
$ok($expectServiceArea >= 150, "service×area count={$expectServiceArea}");
$ok($expectKeywordArea >= 60 * count($areas), "keyword×area count={$expectKeywordArea}");

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'getAovMatrixKeywordSlugs'), 'static-export wires AOV keyword×all areas');
$ok(str_contains($exportSrc, 'getAovNationwideServices'), 'static-export wires AOV service×area');

$local = exportedServiceLocalUrl('aov-air-handling', 'Stockport', 'area');
$ok(str_contains($local, '/pages/aov-air-handling/stockport'), 'area chrome links AOV service×town');
$elec = exportedServiceLocalUrl('electrical', 'Stockport', 'area');
$ok(!str_contains($elec, '/pages/electrical/stockport'), 'electrical still avoids thin service×town');

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
$matrixHtml = icomplyRenderServiceAreaHtml('aov-air-handling', 'Ramsbottom');
$ok(stripos($matrixHtml, 'Ramsbottom') !== false, 'matrix service×area names Ramsbottom');
$ok(stripos($matrixHtml, 'Price on application') !== false, 'matrix service×area is POA');
$ok(stripos($matrixHtml, 'Fire Alarms') !== false && stripos($matrixHtml, '/pages/services/fire-alarms') !== false, 'matrix links fire alarms hub');
$ok(stripos($matrixHtml, 'Emergency Lighting') !== false, 'matrix links emergency lighting');
$ok(stripos($matrixHtml, 'Fire Doors') !== false, 'matrix links fire doors');
$ok(!preg_match('/£\s*\d/', $matrixHtml), 'matrix HTML has no invented £ price');

$kwHtml = icomplyRenderKeywordTownHtml('aov-installation', 'Manchester');
$ok(stripos($kwHtml, 'Manchester') !== false && stripos($kwHtml, 'AOV Installation') !== false, 'keyword×town AOV installation in Manchester');

ob_start();
renderServiceAreaPage('aov-air-handling', 'Stockport');
$combo = (string)ob_get_clean();
$ok(stripos($combo, '<!DOCTYPE') !== false && stripos($combo, 'Stockport') !== false, 'combo render AOV in Stockport');
$ok(stripos($combo, 'Alongside AOV') !== false, 'combo links fire-protection adjacent services');
$ok(stripos($combo, 'Price on application') !== false, 'combo copy is POA');
$ok(!preg_match('/£\s*\d/', $combo), 'combo HTML has no invented £ price');

ob_start();
renderServiceHubPage('aov-air-handling');
$hub = (string)ob_get_clean();
$ok(str_contains($hub, '/pages/aov-air-handling/stockport'), 'AOV hub links a town service page');
$ok(substr_count($hub, 'aov-installation') >= 1, 'AOV hub lists keyword guides');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(!preg_match('#/pages/aov-air-handling/[a-z0-9\-]+</loc>#', $xml), 'sitemap has zero /pages/aov-air-handling/{town}');
$ok(str_contains($xml, '/pages/services/aov-air-handling</loc>'), 'sitemap still lists the AOV service hub');
$ok(str_contains($xml, '/pages/keywords/aov-installation/stockport</loc>'), 'sitemap lists featured AOV keyword×Stockport');
$kwTown = preg_match_all('#/pages/keywords/[a-z0-9\-]+/[a-z0-9\-]+</loc>#', $xml);
$ok($kwTown > 0 && $kwTown <= 180, 'sitemap keyword×town stays featured-only (' . $kwTown . ')');

$notes = (string)file_get_contents(SITE_ROOT . '/data/seo-matrix-rollout-notes.md');
$ok(str_contains($notes, 'AOV nationwide') && str_contains($notes, 'No live promote'), 'rollout notes cover AOV non-prod');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
echo "scale service×area={$expectServiceArea} keyword×area={$expectKeywordArea}\n";
exit($fail > 0 ? 1 : 0);
