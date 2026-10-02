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
    '/pages/gas-systems/manchester</loc>',
    '/pages/electrical/stockport</loc>',
    '/pages/electrical/manchester</loc>',
    '/pages/epc/stockport</loc>',
    '/pages/electrical/liverpool</loc>',
    '/pages/cctv/stockport</loc>',
    '/pages/access-control/liverpool</loc>',
];
foreach ($bannedNeedles as $n) {
    if (str_contains($xml, $n)) {
        echo "FAIL: banned URL in sitemap: {$n}\n";
        $fail++;
    }
}

// Live sitemap listed ~114–120 /pages/{service}/{stockport|manchester} that 404.
// Generation must not invent those unless a real PHP file exists.
if (function_exists('getServices')) {
    foreach (array_keys(getServices()) as $sSlug) {
        foreach (['stockport', 'manchester'] as $town) {
            $rel = 'pages/' . $sSlug . '/' . $town . '.php';
            $needle = '/pages/' . $sSlug . '/' . $town . '</loc>';
            if (str_contains($xml, $needle) && !is_file(SITE_ROOT . '/' . $rel)) {
                if (function_exists('isFireProtectionService') && isFireProtectionService($sSlug)) {
                    continue;
                }
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
    '/pages/aov-air-handling/manchester</loc>',
    '/pages/aov-air-handling/burnley</loc>',
    '/pages/fire-alarms/liverpool</loc>',
    '/pages/fire-risk-assessments/stockport</loc>',
    '/pages/emergency-lighting/stockport</loc>',
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
$fireServices = function_exists('getFireProtectionServices') ? getFireProtectionServices() : [];
$expectedFire = 0;
foreach ($fireServices as $slug => $_name) {
    $expectedFire += count(function_exists('areasForService') ? areasForService($slug) : []);
}
$nonFireHits = [];
$fireHits = 0;
foreach ($serviceAreaHits as $path) {
    if (preg_match('#^/pages/([a-z0-9\-]+)/#', $path, $m) && isset($fireServices[$m[1]])) {
        $fireHits++;
    } else {
        $nonFireHits[] = $path;
    }
}
if ($nonFireHits) {
    $fail++;
    echo 'FAIL: sitemap lists non-fire service×area (sample): ' . implode(', ', array_slice($nonFireHits, 0, 8)) . "\n";
}
if ($expectedFire > 0 && $fireHits !== $expectedFire) {
    $fail++;
    echo "FAIL: fire×area sitemap count {$fireHits} (expected {$expectedFire})\n";
} else {
    echo "OK: fire-protection×area locs={$fireHits}\n";
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
