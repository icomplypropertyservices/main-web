<?php
/**
 * FRA nationwide checks. Does not deploy and does not rewrite the live sitemap.
 *
 * Usage: php bin/check-fra-nationwide.php
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once SITE_ROOT . '/includes/matrix-page.php';

$fail = 0;
$say = static function (bool $ok, string $msg) use (&$fail): void {
    echo ($ok ? 'OK  ' : 'FAIL ') . $msg . "\n";
    if (!$ok) {
        $fail++;
    }
};

$say(function_exists('fraGuidePrice') && fraGuidePrice() === '£350', 'guide price is £350');
$say(fraGuidePriceGbp() === 350, 'guide price gbp is 350');

$records = getMainlandAreaRecords();
$slugs = [];
foreach ($records as $row) {
    $slugs[$row['slug']] = $row['name'];
}
$say(count($records) >= 400, 'mainland catalogue has ' . count($records) . ' towns');

foreach (getAreas() as $area) {
    $slug = areaSlug((string)$area);
    if (!isset($slugs[$slug])) {
        $say(false, 'North West area missing from mainland list: ' . $area);
        break;
    }
}
$say(isset($slugs['london']) && isset($slugs['cardiff']) && isset($slugs['aberdeen']) && isset($slugs['stockport']), 'London, Cardiff, Aberdeen and Stockport are on the list');
$say(areaFromSlug('london') === 'London', 'areaFromSlug resolves London');
$say(!isset($slugs['belfast']) && !isset($slugs['douglas']), 'Belfast and Douglas stay off the mainland list');

$hub = '';
ob_start();
try {
    renderServiceHubPage('fire-risk-assessments');
    $hub = (string)ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    $say(false, 'hub render: ' . $e->getMessage());
}
$say($hub !== '' && str_contains($hub, '£350') && str_contains($hub, 'UK mainland'), 'FRA hub shows £350 and UK mainland');
$say($hub !== '' && str_contains($hub, '/pages/fire-risk-assessments/london'), 'FRA hub links the London FRA page');

$areaHtml = '';
ob_start();
try {
    renderAreaHubPage('Stockport');
    $areaHtml = (string)ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    $say(false, 'area render: ' . $e->getMessage());
}
$say(str_contains($areaHtml, '£350') && str_contains($areaHtml, '/pages/fire-risk-assessments/stockport'), 'Stockport area page owns FRA at £350');

$local = '';
ob_start();
try {
    renderServiceAreaPage('fire-risk-assessments', 'London');
    $local = (string)ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    $say(false, 'London FRA render: ' . $e->getMessage());
}
$say(str_contains($local, '£350') && str_contains($local, 'London') && str_contains($local, 'UK mainland'), 'London FRA page shows price and coverage');

$matrix = icomplyRenderServiceAreaHtml('fire-risk-assessments', 'Birmingham');
$say(str_contains($matrix, '£350') && str_contains($matrix, 'Birmingham') && str_contains($matrix, '/pages/fire-risk-assessments/cardiff'), 'export HTML for Birmingham links other mainland towns');

$liveSitemap = dirname(SITE_ROOT) . '/sitemap.xml';
if (!is_file($liveSitemap)) {
    $liveSitemap = SITE_ROOT . '/sitemap.xml';
}
if (is_file($liveSitemap)) {
    $xml = (string)file_get_contents($liveSitemap);
    $say(!str_contains($xml, '/pages/fire-risk-assessments/london'), 'live sitemap is unchanged (no London FRA loc)');
} else {
    $say(true, 'no live sitemap.xml in tree to alter');
}

$dir = SITE_ROOT . '/data/coverage';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$draft = $dir . '/fra-nationwide-sitemap.xml';
$base = 'https://icomplypropertyservices.co.uk';
$xmlOut = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xmlOut .= "<!-- DRAFT ONLY. Not the live sitemap. Do not promote. -->\n";
$xmlOut .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$xmlOut .= '  <url><loc>' . $base . '/pages/services/fire-risk-assessments</loc></url>' . "\n";
$xmlOut .= '  <url><loc>' . $base . '/pages/fire-risk-assessment</loc></url>' . "\n";
foreach ($records as $row) {
    $xmlOut .= '  <url><loc>' . $base . '/pages/fire-risk-assessments/' . $row['slug'] . '</loc></url>' . "\n";
}
$xmlOut .= "</urlset>\n";
file_put_contents($draft, $xmlOut);
$say(is_file($draft) && substr_count($xmlOut, '<loc>') === count($records) + 2, 'draft sitemap written (' . (count($records) + 2) . ' locs, not live)');

echo $fail === 0 ? "PASS\n" : "FAIL ({$fail})\n";
exit($fail === 0 ? 0 : 1);
