#!/usr/bin/env php
<?php
/**
 * Emergency lighting must own every published UK mainland area.
 * Other services stay on the North West list. No production promote.
 *
 * Usage: php website/bin/check-emergency-lighting-nationwide.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/matrix-page.php';
ob_start();

$fail = 0;
$ok = static function (string $msg): void {
    echo "OK: {$msg}\n";
};
$bad = static function (string $msg) use (&$fail): void {
    echo "FAIL: {$msg}\n";
    $fail++;
};

if (!function_exists('getMainlandAreas')) {
    $bad('mainland helpers missing');
    exit(1);
}

$mainland = getMainlandAreas();
$nw = getAreas();
$nwCount = count($nw);
if ($nwCount < 160) {
    $bad("North West list shrank to {$nwCount}");
} else {
    $ok("North West list unchanged at {$nwCount}");
}

$mainlandSlugs = [];
foreach ($mainland as $name) {
    $s = areaSlug($name);
    if (isset($mainlandSlugs[$s])) {
        $bad("duplicate mainland slug {$s}");
    }
    $mainlandSlugs[$s] = $name;
}
if (count($mainland) < 1000) {
    $bad('mainland list too small: ' . count($mainland));
} else {
    $ok('mainland areas=' . count($mainland));
}

foreach ($nw as $name) {
    if (!isset($mainlandSlugs[areaSlug((string)$name)])) {
        $bad('North West town missing from mainland set: ' . $name);
    }
}
$ok('every North West town is inside the mainland set');

$must = ['London', 'Birmingham', 'Cardiff', 'Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee', 'Swansea', 'Stockport', 'Newport', 'Newport, Shropshire'];
foreach ($must as $name) {
    if (resolveServiceAreaName('emergency-lighting', areaSlug($name)) !== $name) {
        $bad("emergency lighting does not resolve {$name}");
    }
}
$ok('capital and national cities resolve');

$banned = ['Belfast', 'Inverness', 'Stornoway', 'Kirkwall', 'Lerwick', 'Tobermory', 'Millport', 'Hugh Town', 'Douglas', 'St Helier'];
foreach ($banned as $name) {
    if (mainlandAreaRecord($name) !== null || resolveServiceAreaName('emergency-lighting', areaSlug($name)) !== null) {
        $bad("excluded place leaked into emergency lighting: {$name}");
    }
}
$ok('Highlands, islands, Northern Ireland and offshore places are excluded');

if (resolveServiceAreaName('fire-alarms', 'london') !== null) {
    $bad('fire-alarms must not own London');
} else {
    $ok('fire-alarms does not own London');
}
if (count(getAreasForService('fire-alarms')) !== $nwCount) {
    $bad('fire-alarms area count changed');
} else {
    $ok('other services stay on the North West list');
}

$samples = ['London', 'Birmingham', 'Cardiff', 'Edinburgh', 'Glasgow', 'Stockport'];
foreach ($samples as $name) {
    $html = icomplyRenderServiceAreaHtml('emergency-lighting', $name);
    if ($html === '' || !str_contains($html, '<!DOCTYPE') || !str_contains($html, $name)) {
        $bad("matrix render failed for {$name}");
        continue;
    }
    if (!str_contains($html, 'UK mainland')) {
        $bad("{$name} page does not say UK mainland");
    }
    if (str_contains($html, 'minutes from')) {
        $bad("{$name} invents a drive time");
    }
    if (preg_match('/£\s*\d/', $html)) {
        $bad("{$name} invents a price");
    }
    $needle = '/pages/emergency-lighting/' . areaSlug($name);
    if (!str_contains($html, $needle)) {
        $bad("{$name} missing canonical path {$needle}");
    }
}
$ok('sample mainland pages render without prices or fake drive times');

$inverness = icomplyRenderServiceAreaHtml('emergency-lighting', 'Inverness');
if ($inverness !== '') {
    $bad('Inverness rendered an emergency lighting page');
} else {
    $ok('Inverness does not render');
}

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
if (!str_contains($exportSrc, "getAreasForService('emergency-lighting')")) {
    $bad('static export does not add mainland emergency-lighting routes');
}
$routes = [];
foreach (array_keys(getServices()) as $sSlug) {
    foreach (getAreas() as $area) {
        $routes['/pages/' . $sSlug . '/' . areaSlug((string)$area)] = true;
    }
}
foreach (getAreasForService('emergency-lighting') as $area) {
    $routes['/pages/emergency-lighting/' . areaSlug((string)$area)] = true;
}
if (isset($routes['/pages/fire-alarms/london'])) {
    $bad('export gave fire-alarms a London page');
} else {
    $ok('export does not add London for other services');
}
$elRoutes = 0;
foreach (array_keys($routes) as $path) {
    if (str_starts_with($path, '/pages/emergency-lighting/')) {
        $elRoutes++;
    }
}
if ($elRoutes < count($mainland)) {
    $bad('export missing emergency-lighting mainland routes: ' . $elRoutes . ' vs ' . count($mainland));
} else {
    $ok('export owns emergency-lighting × mainland (' . $elRoutes . ' routes)');
}

require_once dirname(__DIR__) . '/includes/render.php';
ob_start();
renderServiceAreaPage('emergency-lighting', 'London');
$hubHtml = (string)ob_get_clean();
if (!str_contains($hubHtml, 'UK mainland') || !str_contains($hubHtml, 'London') || preg_match('/£\s*\d/', $hubHtml)) {
    $bad('service template for London is missing mainland copy or invents a price');
} else {
    $ok('service template renders London on the UK mainland');
}
ob_start();
renderServiceAreaPage('emergency-lighting', 'Inverness');
$blocked = (string)ob_get_clean();
if (!str_contains($blocked, 'Area not found') || str_contains($blocked, '<h1')) {
    $bad('Inverness was not rejected by the service renderer');
} else {
    $ok('service renderer rejects Inverness');
}

echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
ob_end_flush();
exit($fail === 0 ? 0 : 1);
