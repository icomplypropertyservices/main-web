<?php
/**
 * Barriers priority support: mainland coverage, access-control cross-sell, kit prices.
 * Usage: php website/bin/check-barriers-support.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$websiteRoot = dirname(__DIR__);
require_once $websiteRoot . '/config.php';
require_once SITE_ROOT . '/includes/barriers-support.php';

if (in_array('--render', $argv, true)) {
    $path = $argv[array_search('--render', $argv, true) + 1] ?? '/';
    $_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['SERVER_NAME'] = 'icomplypropertyservices.co.uk';
    require_once SITE_ROOT . '/includes/router.php';
    ob_start();
    routerHandleRequest();
    $html = (string)ob_get_clean();
    $status = (int)http_response_code();
    echo $status . "\n" . $html;
    exit(0);
}

$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$fail): void {
    if ($cond) {
        echo "OK: {$msg}\n";
        return;
    }
    echo "FAIL: {$msg}\n";
    $fail++;
};

$areas = barriersMainlandAreas();
$slugs = [];
foreach ($areas as $area) {
    $slugs[$area['slug']] = $area;
}
$ok(count($areas) > count(getAreas()), 'mainland list is larger than the North West area file (' . count($areas) . ' > ' . count(getAreas()) . ')');
$ok(($areas[0]['slug'] ?? '') === 'manchester', 'Manchester is the first priority area');
$ok(($areas[1]['slug'] ?? '') === 'burnley', 'Burnley is the second priority area');
$ok(isset($slugs['london'], $slugs['cardiff'], $slugs['edinburgh'], $slugs['glasgow'], $slugs['birmingham']), 'London, Cardiff, Edinburgh, Glasgow and Birmingham are included');

$nations = [];
$badNation = [];
foreach ($areas as $area) {
    $nations[$area['nation']] = true;
    if (!in_array($area['nation'], ['England', 'Wales', 'Scotland'], true)) {
        $badNation[] = $area['slug'];
    }
}
$ok($badNation === [], 'every area nation is England, Wales or Scotland');
$ok(isset($nations['England'], $nations['Wales'], $nations['Scotland']), 'England, Wales and Scotland are all present');

foreach (['belfast', 'douglas', 'st-helier', 'kirkwall', 'lerwick', 'stornoway', 'portree', 'holyhead', 'ryde', 'cowes'] as $banned) {
    $ok(!isset($slugs[$banned]) && barriersFindArea($banned) === null, $banned . ' is not a mainland barrier area');
}

$missingExisting = [];
foreach (getAreas() as $name) {
    $slug = areaSlug((string)$name);
    if (!isset($slugs[$slug])) {
        $missingExisting[] = (string)$name;
    }
}
$ok($missingExisting === [], 'every existing North West area is on the mainland list' . ($missingExisting ? ' missing ' . implode(', ', $missingExisting) : ''));

$packs = barriersKitPacks();
$prices = [];
foreach ($packs as $pack) {
    $prices[$pack['sku']] = $pack['price'];
}
$ok($prices === [
    'BAR-5M-STD' => '£5,850.00',
    'BAR-5M-VIDEX' => '£7,441.83',
    'BAR-5M-PAXTON' => '£8,375.45',
    'BAR-5M-GSM' => '£7,393.18',
    'BAR-5M-ALLIN' => '£5,199.99',
], 'published 5m supply prices unchanged');

$render = static function (string $path) use ($websiteRoot): array {
    $cmd = 'php ' . escapeshellarg($websiteRoot . '/bin/check-barriers-support.php') . ' --render ' . escapeshellarg($path);
    $out = shell_exec($cmd);
    $out = (string)$out;
    $nl = strpos($out, "\n");
    $status = (int)substr($out, 0, $nl === false ? 0 : $nl);
    if ($status === 0) {
        $status = 200;
    }
    $html = $nl === false ? '' : substr($out, $nl + 1);
    return [$status, $html];
};

$expect = [
    '/pages/barriers' => ['Vehicle barriers for mainland Britain', 'Access control', 'BAR-5M-PAXTON', '£8,375.45', 'CAME', 'Paxton', 'Videx', 'Manchester', 'Burnley', 'install POA'],
    '/pages/barriers/manchester' => ['Vehicle barriers in Manchester', 'Priority area', 'access control', 'BAR-5M-VIDEX', 'Paxton'],
    '/pages/barriers/burnley' => ['Vehicle barriers in Burnley', 'Priority area', 'East Lancashire', 'BAR-5M-GSM'],
    '/pages/barriers/london' => ['Vehicle barriers in London', 'Mainland coverage', 'access-control'],
    '/pages/barriers/cardiff' => ['Vehicle barriers in Cardiff', 'Wales'],
    '/pages/barriers/edinburgh' => ['Vehicle barriers in Edinburgh', 'Scotland'],
];
foreach ($expect as $path => $needles) {
    [$status, $html] = $render($path);
    $ok($status === 200 && str_contains($html, '<html'), $path . ' renders HTML 200');
    foreach ($needles as $needle) {
        $ok(stripos($html, $needle) !== false, $path . ' contains ' . $needle);
    }
    $ok(!str_contains($html, '£350') && !preg_match('/travel uplift of £/i', $html), $path . ' does not invent a travel price');
}

foreach (['/pages/barriers/belfast', '/pages/barriers/kirkwall', '/pages/barriers/not-a-real-town'] as $path) {
    [$status, $html] = $render($path);
    $ok($status === 404, $path . ' is 404');
    $ok(!str_contains($html, 'BAR-5M-PAXTON'), $path . ' does not render a barrier kit page');
}

$routes = barriersExportRoutes();
$ok(in_array('/pages/barriers', $routes, true) && in_array('/pages/barriers/manchester', $routes, true) && count($routes) === count($areas) + 1, 'export routes cover the hub and every mainland area');

require_once SITE_ROOT . '/includes/sitemap.php';
$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(str_contains($xml, '/pages/barriers</loc>'), 'sitemap generator lists the hub');
$ok(str_contains($xml, '/pages/barriers/manchester</loc>'), 'sitemap generator lists Manchester');
$ok(str_contains($xml, '/pages/barriers/burnley</loc>'), 'sitemap generator lists Burnley');
$ok(str_contains($xml, '/pages/barriers/cardiff</loc>'), 'sitemap generator lists Cardiff');
$ok(!str_contains($xml, '/pages/barriers/belfast</loc>'), 'sitemap generator omits Belfast');
$ok(!str_contains($xml, '/pages/access-control/manchester</loc>'), 'sitemap still has no access-control service×town loc');

$file = SITE_ROOT . '/sitemap.xml';
$fileXml = is_file($file) ? (string)file_get_contents($file) : '';
$ok(str_contains($fileXml, '/pages/barriers</loc>'), 'committed sitemap.xml lists the hub');
$ok(str_contains($fileXml, '/pages/barriers/manchester</loc>'), 'committed sitemap.xml lists Manchester');
$ok(str_contains($fileXml, '/pages/barriers/burnley</loc>'), 'committed sitemap.xml lists Burnley');
$ok(str_contains($fileXml, '/pages/barriers/edinburgh</loc>'), 'committed sitemap.xml lists Edinburgh');
$ok(!str_contains($fileXml, '/pages/barriers/belfast</loc>'), 'committed sitemap.xml omits Belfast');

echo $fail === 0 ? "PASS\n" : "FAIL ({$fail})\n";
exit($fail === 0 ? 0 : 1);
