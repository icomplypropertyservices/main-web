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
    '/pages/keywords/eicr/stockport', // keyword×area junk sample
    '/pages/gas-systems/manchester', // service×town 404 on prod
    '/pages/epc/stockport',
    '/pages/emergency-lighting/stockport',
    '/pages/keywords/eicr-cost</loc>', // keyword hub 404 on draft
    '/pages/keywords/eicr-near-me</loc>',
    '/pages/keywords/aov-system</loc>',
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
if (preg_match_all('#<loc>https://icomplypropertyservices\.co\.uk(/pages/keywords/[^<]+)</loc>#', $xml, $km)) {
    foreach ($km[1] as $path) {
        if (!icomplySitemapUrlHasFile($path)) {
            echo "FAIL: sitemap keyword loc has no deploy file: {$path}\n";
            $fail++;
        }
        if (substr_count($path, '/') > 3) {
            echo "FAIL: keyword×town loc leaked into sitemap: {$path}\n";
            $fail++;
        }
    }
}

$count = substr_count($xml, '<url>');
if ($count < 30 || $count > 20000) {
    echo "FAIL: unexpected URL count {$count} (want 30–20000, built files only)\n";
    $fail++;
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
