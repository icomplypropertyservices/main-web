#!/usr/bin/env php
<?php
/**
 * Burtonwood–Crosby (and any later area-coverage batches):
 * every core service × every listed place is a service×area route,
 * coverage-only places are not in areas.json, and sitemap.xml
 * does not list /pages/{service}/{town}.
 *
 * Usage: php website/bin/check-area-coverage.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/local-content.php';
require_once __DIR__ . '/../includes/matrix-page.php';
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

$batches = coverageAreaBatches();
$ok($batches !== [], 'at least one coverage batch');
$b02 = null;
foreach ($batches as $batch) {
    if (($batch['id'] ?? '') === 'b02-burtonwood-crosby') {
        $b02 = $batch;
    }
}
$ok($b02 !== null, 'batch b02-burtonwood-crosby loaded');
$rows = $b02['areas'] ?? [];
$ok(count($rows) === 100, 'batch count=' . count($rows) . ' (want 100)');
$ok(($rows[0]['slug'] ?? '') === 'burtonwood', 'first slug burtonwood');
$ok(($rows[count($rows) - 1]['slug'] ?? '') === 'crosby', 'last slug crosby');

$coreSlugs = [];
foreach (getAreas() as $area) {
    $coreSlugs[areaSlug((string)$area)] = true;
}
$ok(count($coreSlugs) >= 150, 'core areas.json unchanged size=' . count($coreSlugs));
$ok(!isset($coreSlugs['burtonwood']), 'burtonwood is coverage-only');
$ok(isset($coreSlugs['crosby']) && isset($coreSlugs['bury']), 'crosby and bury stay core towns');

$services = getServices();
$ok(count($services) >= 59, 'core services=' . count($services));
$paths = coverageServiceAreaPaths();
$ok(count($paths) === count($rows) * count($services), 'service×area paths=' . count($paths));

$only = coverageOnlyAreas();
$ok(count($only) === 83, 'coverage-only places=' . count($only) . ' (want 83)');
foreach ($only as $place) {
    if (isset($coreSlugs[$place['slug']])) {
        $ok(false, $place['slug'] . ' leaked into areas.json');
        break;
    }
    if (areaFromSlug($place['slug']) !== $place['name']) {
        $ok(false, 'areaFromSlug mismatch ' . $place['slug']);
        break;
    }
    if (areaSlug($place['name']) !== $place['slug']) {
        $ok(false, 'slug round-trip ' . $place['slug'] . ' name=' . $place['name']);
        break;
    }
}
$ok(areaFromSlug('burtonwood') === 'Burtonwood', 'areaFromSlug burtonwood');
$ok(areaFromSlug('connah-s-quay') === "Connah's Quay", 'areaFromSlug connah-s-quay');
$ok(areaFromSlug('crosby') === 'Crosby', 'core crosby name wins');

$sitemap = (string)file_get_contents(SITE_ROOT . '/sitemap.xml');
$banned = [
    '/pages/electrical/burtonwood',
    '/pages/fire-alarms/crosby',
    '/pages/areas/burtonwood',
    '/pages/legionella-risk-assessment/connah-s-quay',
];
foreach ($banned as $needle) {
    $ok(!str_contains($sitemap, $needle), 'sitemap omits ' . $needle);
}

$profile = area_profile('Burtonwood');
$ok(($profile['districts'] ?? '') === 'WA5', 'Burtonwood profile WA5');
$ok(str_contains((string)($profile['region'] ?? ''), 'Warrington'), 'Burtonwood region');
$ambiguous = area_profile('City Centre');
$ok(str_contains((string)($ambiguous['stock'] ?? ''), 'postcode'), 'City Centre copy asks for the postcode');

$samples = [
    ['electrical', 'Burtonwood', false],
    ['fire-alarms', 'City Centre', false],
    ['legionella-risk-assessment', "Connah's Quay", true],
    ['asbestos-survey', 'Colwyn Bay', true],
    ['gas-systems', 'Crosby', false],
];
foreach ($samples as [$svc, $area, $poa]) {
    $html = icomplyRenderServiceAreaHtml($svc, $area);
    $name = $services[$svc] ?? $svc;
    $areaSeen = str_contains($html, $area) || str_contains($html, htmlspecialchars($area, ENT_QUOTES, 'UTF-8'));
    $ok(str_contains($html, $name) && $areaSeen, "render {$svc} / {$area}");
    $ok(!str_contains($html, '£'), "no £ on {$svc} / {$area}");
    if ($poa) {
        $ok(str_contains($html, 'Price on application'), "POA line {$svc}");
    }
    if (isCoverageOnlyArea($area)) {
        $heading = 'Every core service in ' . htmlspecialchars($area, ENT_QUOTES, 'UTF-8');
        $ok(str_contains($html, $heading), "service list heading {$area}");
        $ok(substr_count($html, '/pages/') >= count($services), "service links {$area}");
    }
}

ob_start();
renderAreaHubPage('Burtonwood');
$hub = (string)ob_get_clean();
$ok(str_contains($hub, 'Burtonwood'), 'area hub renders Burtonwood');
$ok(str_contains($hub, '/pages/electrical/burtonwood'), 'hub links electrical×burtonwood');
$ok(str_contains($hub, '/pages/asbestos-survey/burtonwood'), 'hub links asbestos×burtonwood');
$ok(!str_contains($hub, '/pages/keywords/eicr/burtonwood'), 'hub does not link a missing keyword×town');
$linkCount = preg_match_all('#/pages/[a-z0-9\-]+/burtonwood#', $hub);
$ok($linkCount >= count($services), "hub service×area links={$linkCount}");

ob_start();
renderAreaHubPage('Crosby');
$coreHub = (string)ob_get_clean();
$ok(str_contains($coreHub, '/pages/keywords/'), 'core Crosby hub still links keyword guides');
$ok(!str_contains($coreHub, '/pages/electrical/crosby'), 'core Crosby hub keeps the existing local URL');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
