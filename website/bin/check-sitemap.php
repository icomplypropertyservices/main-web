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
    '/pages/services/legionella-risk-assessment</loc>',
    '/pages/services/asbestos-survey</loc>',
    '/pages/resources/legionella-risk-assessment</loc>',
    '/pages/resources/asbestos-survey</loc>',
    '/pages/legionella-landlords</loc>',
    '/pages/asbestos-landlords</loc>',
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

$count = substr_count($xml, '<url>');
if ($count < 200 || $count > 20000) {
    echo "FAIL: unexpected URL count {$count} (want 200–20000 compact)\n";
    $fail++;
}

echo "URLs={$count} bytes=" . strlen($xml) . PHP_EOL;
echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
