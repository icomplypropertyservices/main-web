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
    '/shop</loc>',
    '/shop/',
    '/products</loc>',
    '/products/',
    '/sitemap-1.xml',
    '-photo.jpg',
    '/pages/gas-systems/stockport</loc>',
    '/pages/gas-systems/manchester</loc>',
    '/pages/electrical/stockport</loc>',
    '/pages/electrical/manchester</loc>',
    '/pages/fire-alarms/liverpool</loc>',
];
foreach ($bannedNeedles as $n) {
    if (str_contains($xml, $n)) {
        echo "FAIL: banned URL in sitemap: {$n}\n";
        $fail++;
    }
}

$required = [
    'https://icomplypropertyservices.co.uk/</loc>',
    '/pages/areas</loc>',
    '/pages/manufacturers</loc>',
    '/pages/resources</loc>',
    '/pages/resources/eicr-guide</loc>',
    '/pages/resources/fire-alarm-servicing</loc>',
    '/pages/resources/emergency-lighting-testing</loc>',
    '/pages/resources/cctv-for-business</loc>',
    '/pages/resources/access-control-guide</loc>',
    '/pages/resources/landlord-compliance-checklist</loc>',
    '/pages/resources/gas-safety-certificate-landlords</loc>',
    '/pages/landlord-certificates</loc>',
    '/pages/gas-safety-certificate</loc>',
    '/pages/fire-risk-assessment</loc>',
    '/pages/stockport-property-compliance</loc>',
    '/pages/manchester-property-compliance</loc>',
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

$count = substr_count($xml, '<url>');
if ($count < 200 || $count > 20000) {
    echo "FAIL: unexpected URL count {$count} (want 200–20000 compact)\n";
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
if ($serviceAreaHits) {
    $fail++;
    echo 'FAIL: sitemap lists service×area 404s (sample): ' . implode(', ', array_slice($serviceAreaHits, 0, 8)) . "\n";
} else {
    echo "OK: no /pages/{service}/{town} service×area locs\n";
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
