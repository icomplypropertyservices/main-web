<?php
/**
 * Validate compact sitemap: 200-able paths only, no known 404s / junk.
 * Usage: php bin/check-sitemap.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sitemap.php';

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$fail = 0;

if (!str_contains($xml, '<urlset')) {
    echo "FAIL: not a urlset\n";
    $fail++;
}
if (str_contains($xml, '<sitemapindex')) {
    echo "FAIL: must not be a multi-part index\n";
    $fail++;
}

$bannedNeedles = [
    '/privacy-policy</loc>',
    '/terms-and-conditions</loc>',
    '/sitemap-1.xml',
    '-photo.jpg',
    '/pages/gas-systems/stockport</loc>',
    '/pages/electrical/stockport</loc>',
    '/pages/epc/stockport</loc>',
    '/pages/emergency-lighting/stockport</loc>',
    '/pages/emergency-lighting/manchester</loc>',
    '/pages/fire-alarms/liverpool</loc>',
    '/pages/fire-alarms/manchester</loc>',
    '/pages/fire-alarms/burnley</loc>',
];
foreach ($bannedNeedles as $n) {
    if (str_contains($xml, $n)) {
        echo "FAIL: banned URL in sitemap: {$n}\n";
        $fail++;
    }
}

// Service×town locs are only valid for the Jack pilot (rendered at export,
// no source stub required). Anything else still needs a real PHP file.
if (function_exists('getServices')) {
    foreach (array_keys(getServices()) as $sSlug) {
        foreach (['stockport', 'manchester', 'burnley'] as $town) {
            if (function_exists('jackPilotServiceAreaPublished') && jackPilotServiceAreaPublished($sSlug, $town)) {
                continue;
            }
            $rel = 'pages/' . $sSlug . '/' . $town . '.php';
            $needle = '/pages/' . $sSlug . '/' . $town . '</loc>';
            if (str_contains($xml, $needle) && !is_file(SITE_ROOT . '/' . $rel)) {
                echo "FAIL: dead service×town in sitemap (no PHP file): {$needle}\n";
                $fail++;
            }
        }
    }
}

$required = [
    'https://icomplypropertyservices.co.uk/</loc>',
    '/pages/areas</loc>',
    '/pages/manufacturers</loc>',
    '/pages/resources</loc>',
    '/pages/resources/eicr-guide</loc>',
    '/pages/resources/gas-safety-certificate-landlords</loc>',
    '/pages/landlord-certificates</loc>',
    '/pages/fire-risk-assessment</loc>',
    '/pages/keywords/eicr</loc>',
    '/pages/services/legionella-risk-assessment</loc>',
    '/pages/services/asbestos-survey</loc>',
    '/pages/resources/legionella-risk-assessment</loc>',
    '/pages/resources/asbestos-survey</loc>',
    '/shop</loc>',
    '/products</loc>',
    '/pages/services/fire-risk-assessments</loc>',
    '/pages/services/electrical</loc>',
    '/pages/services/gas-systems</loc>',
    '/pages/keywords/rewire</loc>',
    '/pages/keywords/boiler</loc>',
    '/pages/keywords/rewire/stockport</loc>',
    '/privacy</loc>',
    '/terms</loc>',
];
foreach ($required as $n) {
    if (!str_contains($xml, $n)) {
        echo "FAIL: missing required loc: {$n}\n";
        $fail++;
    }
}

// Every remaining /pages/services* and /pages/keywords/{slug} loc must have a file.
if (preg_match_all('#<loc>https://icomplypropertyservices\.co\.uk(/pages/services(?:/[^<]+)?)</loc>#', $xml, $sm)) {
    foreach ($sm[1] as $path) {
        if (!icomplySitemapUrlHasFile($path)) {
            echo "FAIL: sitemap services loc has no deploy file: {$path}\n";
            $fail++;
        }
    }
}
$kwTownCount = 0;
if (preg_match_all('#<loc>https://icomplypropertyservices\.co\.uk(/pages/keywords/[^<]+)</loc>#', $xml, $km)) {
    foreach ($km[1] as $path) {
        $depth = substr_count($path, '/');
        if ($depth === 4) {
            $kwTownCount++;
        }
        if ($depth > 4) {
            echo "FAIL: unexpected deep keyword loc leaked into sitemap: {$path}\n";
            $fail++;
        }
    }
}
if ($kwTownCount < 1 || $kwTownCount > 180) {
    echo "FAIL: sitemap keyword×town count {$kwTownCount} (want featured-only 1–180)\n";
    $fail++;
} else {
    echo "OK: sitemap keyword×town featured-only={$kwTownCount}\n";
}

$count = substr_count($xml, '<url>');
if ($count < 30 || $count > 20000) {
    echo "FAIL: unexpected URL count {$count} (want 30–20000, built files only)\n";
    $fail++;
}

$services = function_exists('getServices') ? getServices() : [];
$areaSlugs = [];
if (function_exists('getAreas') && function_exists('areaSlug')) {
    foreach (getAreas() as $area) {
        $areaSlugs[areaSlug((string)$area)] = true;
    }
}
preg_match_all('#<loc>https://icomplypropertyservices\.co\.uk(/pages/[^<]+)</loc>#', $xml, $locHits);
$serviceAreaHits = [];
foreach ($locHits[1] ?? [] as $path) {
    if (!preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        continue;
    }
    $first = $m[1];
    $second = $m[2];
    if (in_array($first, ['keywords', 'services', 'manufacturers', 'areas', 'resources', 'packages'], true)) {
        continue;
    }
    if (isset($services[$first]) && isset($areaSlugs[$second])) {
        $serviceAreaHits[] = $path;
    }
}
$pilotSlugs = function_exists('getJackPilotAreaSlugs') ? array_flip(getJackPilotAreaSlugs()) : ['manchester' => true, 'burnley' => true];
$pilotServices = function_exists('getJackPilotServices') ? getJackPilotServices() : [];
$badServiceArea = [];
$pilotServiceArea = 0;
foreach ($serviceAreaHits as $path) {
    if (!preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        $badServiceArea[] = $path;
        continue;
    }
    if (isset($pilotServices[$m[1]]) && isset($pilotSlugs[$m[2]])) {
        $pilotServiceArea++;
        continue;
    }
    $badServiceArea[] = $path;
}
if ($badServiceArea) {
    $fail++;
    echo 'FAIL: sitemap lists non-pilot service×area (sample): ' . implode(', ', array_slice($badServiceArea, 0, 8)) . "\n";
} else {
    echo "OK: service×area locs are Manchester/Burnley non-fire only ({$pilotServiceArea})\n";
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
