<?php
/**
 * Nav / footer / HTML site map / XML sitemap must expose every major job hub.
 * AOV and Barriers are the primary featured positions.
 * Usage: php website/bin/check-nav-ia.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/site-nav.php';
require_once SITE_ROOT . '/includes/sitemap.php';

$fail = 0;
$pass = 0;
$ok = static function (string $msg) use (&$pass): void {
    echo "OK   {$msg}\n";
    $pass++;
};
$bad = static function (string $msg) use (&$fail): void {
    echo "FAIL {$msg}\n";
    $fail++;
};

$header = icomplyMegaHeaderHtml();
$footer = icomplyFooterHtml();

ob_start();
include SITE_ROOT . '/pages/site-map.php';
$siteMap = (string)ob_get_clean();

$must = [];
foreach (icomplyFeaturedPushHubs() as $hub) {
    $must[] = $hub['path'];
}
$must[] = icomplyBarrierJobHub()['path'];
foreach (getServices() as $slug => $_name) {
    $must[] = '/pages/services/' . $slug;
}
foreach (icomplyQualityHubLinks() as $hub) {
    $must[] = $hub['path'];
}
foreach (icomplyPackageHubLinks() as $hub) {
    $must[] = $hub['path'];
}
$must[] = '/pages/site-map';
$must[] = '/pages/keywords';
$must = array_values(array_unique($must));

$bodyStart = strpos($siteMap, 'id="priority"');
$bodyEnd = strpos($siteMap, 'data-site-footer');
$siteMapBody = ($bodyStart !== false && $bodyEnd !== false && $bodyEnd > $bodyStart)
    ? substr($siteMap, $bodyStart, $bodyEnd - $bodyStart)
    : '';
if ($siteMapBody === '') {
    $bad('HTML site map priority section missing');
}

$chromeMust = $must;
$bodyMust = array_values(array_filter($must, static fn(string $path): bool => $path !== '/pages/site-map'));
foreach (['header' => $header, 'footer' => $footer] as $surface => $html) {
    foreach ($chromeMust as $path) {
        if (!str_contains($html, $path)) {
            $bad("{$surface} missing {$path}");
        }
    }
}
foreach ($bodyMust as $path) {
    if (!str_contains($siteMapBody, $path)) {
        $bad("sitemap-html missing {$path}");
    }
}
if ($fail === 0) {
    $ok(count($must) . ' hub paths present on nav, footer and HTML site map');
}

$featured = icomplyFeaturedPushHubs();
$aovPos = strpos($header, $featured[0]['path']);
$barPos = strpos($header, $featured[1]['path']);
$servicesPos = strpos($header, 'mega-services');
if ($aovPos === false || $barPos === false || $servicesPos === false || $aovPos > $servicesPos || $barPos > $servicesPos || $aovPos > $barPos) {
    $bad('header primary order must be AOV then Barriers before the Services menu');
} else {
    $ok('header primary order AOV, Barriers, then Services');
}
if (substr_count($header, 'nav-link--featured') < 2) {
    $bad('header missing two nav-link--featured pills');
} else {
    $ok('header featured pills');
}
$drawerAov = strpos($header, 'drawer-priority');
if ($drawerAov === false || strpos($header, $featured[0]['label']) === false) {
    $bad('mobile drawer missing priority AOV/Barriers');
} else {
    $ok('mobile drawer priority links');
}

$footMark = strpos($footer, 'foot-featured');
$footDrops = strpos($footer, 'foot-drops');
if ($footMark === false || $footDrops === false || $footMark > $footDrops) {
    $bad('footer featured cards must sit above the link directories');
} else {
    $ok('footer featured cards above directories');
}
if (!str_contains($footer, $featured[0]['path']) || !str_contains($footer, $featured[1]['path'])) {
    $bad('footer featured cards missing AOV or Barriers');
} else {
    $ok('footer featured AOV and Barriers');
}

$GLOBALS['ICOMPLY_SITEMAP_REQUEST_SAFE'] = true;
$entries = icomplySitemapEntries();
unset($GLOBALS['ICOMPLY_SITEMAP_REQUEST_SAFE']);
$paths = array_column($entries, 'path');
$xmlNeed = [
    '/pages/services/aov-air-handling',
    '/products',
    '/pages/keywords/car-park-barrier-access',
    '/pages/packages/let-ready',
    '/pages/packages/workplace-essentials',
    '/pages/packages/fire-ready',
];
foreach (getServices() as $slug => $_name) {
    $xmlNeed[] = '/pages/services/' . $slug;
}
foreach (icomplyQualityHubLinks() as $hub) {
    $xmlNeed[] = $hub['path'];
}
foreach ($xmlNeed as $path) {
    if (!in_array($path, $paths, true)) {
        $bad("xml sitemap missing {$path}");
    }
}
$ok('xml sitemap includes featured, services, packages and quality hubs');

if (!is_file(SITE_ROOT . '/pages/products.php') || !str_contains((string)file_get_contents(SITE_ROOT . '/includes/products-hub-body.php'), 'id="barriers"')) {
    $bad('products hub missing #barriers anchor');
} else {
    $ok('products hub #barriers anchor');
}

echo PHP_EOL . ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
