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

$dom = new DOMDocument();
$prevXmlErrors = libxml_use_internal_errors(true);
$xmlOk = $dom->loadXML($xml);
$xmlErrors = libxml_get_errors();
libxml_clear_errors();
libxml_use_internal_errors($prevXmlErrors);
if (!$xmlOk || $xmlErrors !== []) {
    echo "FAIL: sitemap XML is not well-formed\n";
    $fail++;
}
$locValues = [];
foreach ($dom->getElementsByTagName('loc') as $locNode) {
    $locValues[] = trim($locNode->textContent);
}
if (count($locValues) !== count(array_unique($locValues))) {
    echo "FAIL: sitemap has duplicate loc values\n";
    $fail++;
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
    '/pages/manufacturers/abb</loc>',
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
$indexablePairs = [];
if ($indexMode === 'tiered' && function_exists('icomplyTier1Towns') && function_exists('icomplyPathIsIndexable')) {
    foreach (array_keys($services) as $svcSlug) {
        foreach (icomplyTier1Towns() as $town) {
            $pair = '/pages/' . $svcSlug . '/' . areaSlug($town);
            if (icomplyPathIsIndexable($pair)) {
                $indexablePairs[] = $pair;
            }
        }
    }
    foreach (icomplyTier1Towns() as $town) {
        if (!isset($areaSlugs[areaSlug($town)])) {
            $fail++;
            echo "FAIL: tier-1 town missing from areas.json: {$town}\n";
        }
    }
}
$svcAreaExpect = $indexMode === 'tiered' ? count($indexablePairs) : ($svcCount * count($areaSlugs));
if ($indexMode === 'tiered' && count($serviceAreaHits) !== $svcAreaExpect) {
    $fail++;
    echo 'FAIL: tiered sitemap service×area count ' . count($serviceAreaHits) . " (want exactly {$svcAreaExpect} bespoke articles)\n";
} elseif ($indexMode !== 'tiered' && count($serviceAreaHits) < (int)floor($svcAreaExpect * 0.98)) {
    $fail++;
    echo 'FAIL: sitemap service×area count ' . count($serviceAreaHits) . " (want about {$svcAreaExpect})\n";
} else {
    echo 'OK: sitemap service×area count=' . count($serviceAreaHits) . "\n";
}
if ($indexMode === 'tiered') {
    if (str_contains($xml, '/pages/keywords/eicr/stockport</loc>') || str_contains($xml, '/pages/electrical/preston</loc>') || str_contains($xml, '/pages/areas/stockport</loc>') || str_contains($xml, '/pages/plastering/stockport</loc>')) {
        $fail++;
        echo "FAIL: tiered sitemap lists a noindex town template\n";
    }
    $missingPairs = 0;
    foreach ($indexablePairs as $pair) {
        if (!str_contains($xml, $pair . '</loc>')) {
            $missingPairs++;
            if ($missingPairs <= 6) {
                echo "FAIL: tiered sitemap missing bespoke loc {$pair}\n";
            }
        }
    }
    if ($missingPairs > 0) {
        $fail += $missingPairs;
    }
}
$diskSitemap = is_file(SITE_ROOT . '/sitemap.xml') ? (string)file_get_contents(SITE_ROOT . '/sitemap.xml') : '';
if ($diskSitemap !== $xml) {
    $fail++;
    echo "FAIL: website/sitemap.xml does not match the generator\n";
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
