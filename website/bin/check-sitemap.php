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
];
foreach ($bannedNeedles as $n) {
    if (str_contains($xml, $n)) {
        echo "FAIL: banned URL in sitemap: {$n}\n";
        $fail++;
    }
}

// Service×town locs must have a rendered file in dist or source.
if (function_exists('getServices')) {
    $distRoot = dirname(SITE_ROOT) . '/dist';
    foreach (array_keys(getServices()) as $sSlug) {
        foreach (['stockport', 'manchester'] as $town) {
            $rel = 'pages/' . $sSlug . '/' . $town . '.php';
            $needle = '/pages/' . $sSlug . '/' . $town . '</loc>';
            $has = is_file(SITE_ROOT . '/' . $rel) || is_file($distRoot . '/' . $rel);
            $virtualTown = in_array($sSlug, ['aov', 'barriers', 'aov-air-handling', 'nurse-call'], true);
            $indexableTown = function_exists('icomplyPathIsIndexable')
                && icomplyPathIsIndexable('/pages/' . $sSlug . '/' . $town);
            if (str_contains($xml, $needle) && !$has && !$virtualTown && !$indexableTown) {
                echo "FAIL: service×town in sitemap has no file: {$needle}\n";
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
    '/shop/</loc>',
    '/shop/fire/</loc>',
    '/shop/electrical/</loc>',
    '/shop/security/</loc>',
    '/shop/gas/</loc>',
    '/products</loc>',
    '/pages/services/fire-risk-assessments</loc>',
    '/pages/services/electrical</loc>',
    '/pages/services/gas-systems</loc>',
    '/pages/keywords/rewire</loc>',
    '/pages/keywords/boiler</loc>',
    '/pages/areas/manchester</loc>',
    '/pages/areas/stockport</loc>',
    '/pages/areas/wigan</loc>',
    '/pages/jobs</loc>',
    '/directories</loc>',
    '/pages/manufacturers/abb</loc>',
    '/become-a-subcontractor</loc>',
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
$kwExpect = 0;
if (function_exists('getMajorKeywords') && function_exists('getAreas')) {
    $kwExpect = count(getMajorKeywords()) * count(getAreas());
}
$indexMode = function_exists('icomplyIndexMode') ? icomplyIndexMode() : 'all';
if ($indexMode === 'tiered') {
    if ($kwTownCount !== 0) {
        echo "FAIL: tiered sitemap must omit keyword×town (found {$kwTownCount})\n";
        $fail++;
    } else {
        echo "OK: sitemap keyword×town count=0 (tiered noindex)\n";
    }
} elseif ($kwTownCount < (int)floor($kwExpect * 0.98)) {
    echo "FAIL: sitemap keyword×town count {$kwTownCount} (want about {$kwExpect})\n";
    $fail++;
} else {
    echo "OK: sitemap keyword×town count={$kwTownCount}\n";
}
if (str_contains($xml, '/pages/products</loc>')) {
    echo "FAIL: /pages/products is a duplicate; sitemap lists /products only\n";
    $fail++;
}
if (substr_count($xml, '/products</loc>') !== 1) {
    echo "FAIL: sitemap must list /products exactly once\n";
    $fail++;
}
if (str_contains($xml, '/shop/sitemap') || str_contains($xml, '/products/sitemap')) {
    echo "FAIL: nested shop/products sitemap loc\n";
    $fail++;
}
// Live /shop and /shop/fire 301 to the trailing-slash canonical. List only that URL.
$nonCanonicalShop = [
    'https://icomplypropertyservices.co.uk/shop</loc>',
    'https://icomplypropertyservices.co.uk/shop/fire</loc>',
    'https://icomplypropertyservices.co.uk/shop/electrical</loc>',
    'https://icomplypropertyservices.co.uk/shop/security</loc>',
    'https://icomplypropertyservices.co.uk/shop/gas</loc>',
];
foreach ($nonCanonicalShop as $bare) {
    if (str_contains($xml, $bare)) {
        echo "FAIL: shop loc is not the trailing-slash canonical: {$bare}\n";
        $fail++;
    }
}
$robots = icomplyRobotsTxt('https://icomplypropertyservices.co.uk');
if (substr_count($robots, 'Sitemap:') !== 1 || !str_contains($robots, 'https://icomplypropertyservices.co.uk/sitemap.xml')) {
    echo "FAIL: robots.txt must name only the apex sitemap\n";
    $fail++;
}
if (str_contains($robots, '/shop/sitemap') || str_contains($robots, '/products/sitemap')) {
    echo "FAIL: robots.txt still points at shop/products sitemaps\n";
    $fail++;
}

$count = substr_count($xml, '<url>');
if ($count < 30 || $count > 400000) {
    echo "FAIL: unexpected URL count {$count} (want 30–400000)\n";
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
$svcCount = function_exists('getServices') ? count(getServices()) : 0;
$tier1Count = 0;
if (function_exists('icomplyTier1Towns') && function_exists('areaSlug')) {
    foreach (icomplyTier1Towns() as $town) {
        if (isset($areaSlugs[areaSlug($town)])) {
            $tier1Count++;
        }
    }
}
$virtualTownPrefixes = ['aov' => true, 'barriers' => true, 'aov-air-handling' => true, 'nurse-call' => true];
$svcAreaExpect = $indexMode === 'tiered' ? ($svcCount * $tier1Count) : ($svcCount * count($areaSlugs));
if ($indexMode === 'tiered') {
    $redirectTown = [];
    $unexpectedTown = [];
    $redirectServices = ['electrical' => true, 'gas-systems' => true, 'fire-alarms' => true, 'emergency-lighting' => true];
    foreach ($serviceAreaHits as $path) {
        if (!preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
            continue;
        }
        if (isset($virtualTownPrefixes[$m[1]])) {
            continue;
        }
        if (isset($redirectServices[$m[1]])) {
            $redirectTown[] = $path;
            continue;
        }
        $unexpectedTown[] = $path;
    }
    if ($redirectTown !== []) {
        $fail++;
        echo 'FAIL: tiered sitemap lists unpublished service×town ' . implode(',', array_slice($redirectTown, 0, 6)) . "\n";
    }
    if ($unexpectedTown !== []) {
        $fail++;
        echo 'FAIL: tiered sitemap lists a service×town that 301s ' . implode(',', array_slice($unexpectedTown, 0, 6)) . "\n";
    }
    if ($redirectTown === [] && $unexpectedTown === []) {
        echo 'OK: sitemap service×area count=' . count($serviceAreaHits) . " (AOV/barrier towns only; unpublished service×town omitted)\n";
    }
} elseif ($indexMode !== 'tiered' && count($serviceAreaHits) < (int)floor($svcAreaExpect * 0.98)) {
    $fail++;
    echo 'FAIL: sitemap service×area count ' . count($serviceAreaHits) . " (want about {$svcAreaExpect})\n";
} else {
    echo 'OK: sitemap service×area count=' . count($serviceAreaHits) . "\n";
}
if ($indexMode === 'tiered') {
    if (str_contains($xml, '/pages/keywords/eicr/stockport</loc>') || str_contains($xml, '/pages/electrical/stockport</loc>') || str_contains($xml, '/pages/electrical/preston</loc>')) {
        $fail++;
        echo "FAIL: tiered sitemap lists a redirecting town URL\n";
    }
    if (str_contains($xml, '/pages/ev-chargers</loc>') || str_contains($xml, '/pages/manufacturers/tunstall</loc>')) {
        $fail++;
        echo "FAIL: tiered sitemap lists a 404\n";
    }
    if (!str_contains($xml, '/pages/areas/stockport</loc>') || !str_contains($xml, '/pages/areas/wigan</loc>') || !str_contains($xml, '/pages/areas/altrincham</loc>')) {
        $fail++;
        echo "FAIL: tiered sitemap missing a Greater Manchester area hub\n";
    }
    if (str_contains($xml, '/pages/areas/liverpool</loc>') || str_contains($xml, '/pages/areas/burnley</loc>')) {
        $fail++;
        echo "FAIL: tiered sitemap lists a non-GM area hub\n";
    }
    if (!str_contains($xml, '/pages/areas/manchester</loc>')) {
        $fail++;
        echo "FAIL: tiered sitemap missing Manchester\n";
    }
    if (function_exists('icomplyGreaterManchesterTownNames') && function_exists('areaSlug')) {
        preg_match_all('#/pages/areas/([a-z0-9\-]+)</loc>#', $xml, $areaLocs);
        $listed = array_values(array_unique($areaLocs[1] ?? []));
        $gm = [];
        foreach (icomplyGreaterManchesterTownNames() as $name) {
            $gm[] = areaSlug((string)$name);
        }
        sort($listed);
        sort($gm);
        if (count($gm) !== 60 || $listed !== $gm) {
            $fail++;
            $missing = array_values(array_diff($gm, $listed));
            $extra = array_values(array_diff($listed, $gm));
            echo 'FAIL: sitemap area hubs ' . count($listed) . ' (want the 60 GM towns)'
                . ($missing ? ' missing=' . implode(',', array_slice($missing, 0, 6)) : '')
                . ($extra ? ' extra=' . implode(',', array_slice($extra, 0, 6)) : '')
                . "\n";
        } else {
            echo "OK: sitemap Greater Manchester area hubs=60\n";
        }
    }
    if (stripos($xml, 'burnley') !== false) {
        $fail++;
        echo "FAIL: tiered sitemap still names Burnley\n";
    }
}

$committed = is_file(SITE_ROOT . '/sitemap.xml') ? (string)file_get_contents(SITE_ROOT . '/sitemap.xml') : '';
foreach ([
    '/pages/areas/manchester</loc>',
    '/pages/areas/stockport</loc>',
    '/pages/areas/wigan</loc>',
    '/pages/jobs</loc>',
    '/directories</loc>',
    '/pages/areas</loc>',
] as $need) {
    if (!str_contains($committed, $need)) {
        $fail++;
        echo "FAIL: committed sitemap missing {$need}\n";
    }
}
foreach ([
    '/pages/ev-chargers</loc>',
    '/pages/manufacturers/tunstall</loc>',
    '/pages/areas/liverpool</loc>',
    '/pages/areas/burnley</loc>',
    '/pages/electrical/stockport</loc>',
    '/pages/keywords/rewire/stockport</loc>',
] as $ban) {
    if (str_contains($committed, $ban)) {
        $fail++;
        echo "FAIL: committed sitemap lists {$ban}\n";
    }
}
if (stripos($committed, 'burnley') !== false) {
    $fail++;
    echo "FAIL: committed sitemap still names Burnley\n";
}

$bannedTowns = ['burnley', 'liverpool', 'preston', 'chester', 'warrington', 'blackpool'];
foreach (['generated' => $xml, 'committed' => $committed] as $label => $blob) {
    foreach ($bannedTowns as $town) {
        if (preg_match('#(?:^|[^a-z])' . $town . '(?:[^a-z]|$)#i', $blob)) {
            $fail++;
            echo "FAIL: {$label} sitemap names {$town}\n";
        }
    }
}

$redirectCases = [
    '/pages/keywords/access-control-near-me/burnley' => '/pages/keywords/access-control-near-me',
    '/pages/keywords/rewire/liverpool' => '/pages/keywords/rewire',
    '/pages/access-control/burnley' => '/pages/services/access-control',
    '/pages/barriers/liverpool' => '/pages/services/barriers',
    '/pages/aov/liverpool' => '/pages/aov',
    '/pages/areas/liverpool' => '/pages/areas',
    '/pages/areas/burnley' => '/pages/areas',
    '/pages/aov-air-handling/blackpool' => '/pages/services/aov-air-handling',
    '/pages/windows-doors/warrington' => '/pages/services/windows-doors',
    '/pages/nurse-call/cardiff' => '/pages/services/nurse-call',
];
$stayCases = [
    '/pages/aov/chorlton',
    '/pages/aov/manchester',
    '/pages/aov/cadishead',
    '/pages/barriers/trafford',
    '/pages/barriers/milnrow',
    '/pages/areas/stockport',
    '/pages/keywords/eicr/stockport',
    '/pages/electrical/stockport',
    '/pages/nurse-call/manchester',
    '/pages/services/electrical',
];
if (!function_exists('icomplyNonGmMatrixRedirect')) {
    $fail++;
    echo "FAIL: icomplyNonGmMatrixRedirect missing\n";
} else {
    foreach ($redirectCases as $path => $dest) {
        $got = icomplyNonGmMatrixRedirect($path);
        if ($got !== $dest || !icomplySitemapOmitsNonGm($path)) {
            $fail++;
            echo "FAIL: {$path} redirects to " . ($got ?? 'null') . " (want {$dest})\n";
        }
    }
    foreach ($stayCases as $path) {
        if (icomplyNonGmMatrixRedirect($path) !== null || icomplySitemapOmitsNonGm($path)) {
            $fail++;
            echo "FAIL: GM URL must stay: {$path}\n";
        }
    }
}

$matrixFile = SITE_ROOT . '/includes/matrix-catalogue.php';
if (is_file($matrixFile)) {
    require_once $matrixFile;
}
if (!function_exists('icomplyMatrixSelectPlaces') || !function_exists('icomplyGreaterManchesterTownNames') || !function_exists('areaSlug')) {
    $fail++;
    echo "FAIL: matrix place selector missing\n";
} else {
    $selected = array_keys(icomplyMatrixSelectPlaces(500)['selected']);
    $want = [];
    foreach (icomplyGreaterManchesterTownNames() as $name) {
        $want[] = areaSlug((string)$name);
    }
    sort($selected);
    sort($want);
    if (count($want) !== 60 || $selected !== $want) {
        $fail++;
        $missing = array_values(array_diff($want, $selected));
        $extra = array_values(array_diff($selected, $want));
        echo 'FAIL: matrix places ' . count($selected) . ' (want the 60 GM towns)'
            . ($missing ? ' missing=' . implode(',', array_slice($missing, 0, 8)) : '')
            . ($extra ? ' extra=' . implode(',', array_slice($extra, 0, 8)) : '')
            . "\n";
    } else {
        echo "OK: matrix places are the 60 Greater Manchester towns\n";
    }
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
