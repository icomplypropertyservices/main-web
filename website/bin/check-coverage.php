#!/usr/bin/env php
<?php
/**
 * Render coverage templates and confirm the matrix, prices, and sitemap gate.
 * Usage: php website/bin/check-coverage.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "check-coverage.php is a CLI tool.\n");
    exit(1);
}

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once dirname(__DIR__) . '/includes/coverage.php';
require_once dirname(__DIR__) . '/includes/router.php';
require_once dirname(__DIR__) . '/includes/sitemap.php';

$fail = 0;
$bad = static function (string $msg) use (&$fail): void {
    fwrite(STDERR, "FAIL: {$msg}\n");
    $fail++;
};

$errors = coverageVerify(false);
foreach ($errors as $error) {
    $bad($error);
}

if (icomplySitemapPathAllowed('/pages/coverage/electrical/abbey-village')) {
    $bad('live sitemap gate allowed a coverage URL');
}
if (!icomplySitemapPathAllowed('/pages/services/electrical')) {
    $bad('live sitemap gate blocked a service hub');
}

$render = static function (string $path) use ($bad): string {
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    http_response_code(200);
    ob_start();
    try {
        routerHandleRequest();
        $html = (string)ob_get_clean();
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $bad($path . ' threw ' . $e->getMessage());
        return '';
    }
    $status = (int)http_response_code();
    if ($status !== 200) {
        $bad($path . ' status ' . $status);
    }
    if (!str_contains($html, '<html')) {
        $bad($path . ' did not render HTML');
    }
    return $html;
};

$areas = coverageLoadAreas();
$first = $areas[0] ?? '';
if ($first === '') {
    $bad('no areas loaded');
    echo "FAIL ({$fail})\n";
    exit(1);
}

$areaHtml = $render('/pages/coverage/areas/' . $first);
foreach (['£249', '£85', '£350', '£650'] as $price) {
    if (!str_contains($areaHtml, $price)) {
        $bad("area hub missing {$price}");
    }
}
if (!str_contains($areaHtml, 'noindex')) {
    $bad('pending area hub should be noindex');
}
if (!str_contains($areaHtml, 'coverage-bucket:')) {
    $bad('area hub missing bucket marker');
}

$electrical = $render('/pages/coverage/electrical/' . $first);
if (!str_contains($electrical, '£249') || !str_contains($electrical, '£650')) {
    $bad('electrical coverage page missing SSOT prices');
}
if (!str_contains($electrical, 'noindex')) {
    $bad('pending electrical page should be noindex');
}

$gas = $render('/pages/coverage/gas-safety/' . $first);
if (!str_contains($gas, '£85')) {
    $bad('gas coverage page missing £85');
}

$fra = $render('/pages/coverage/fire-risk-assessments/' . $first);
if (!str_contains($fra, '£350')) {
    $bad('FRA coverage page missing £350');
}

$bundle = $render('/pages/coverage/hmo-compliance-pack/' . $first);
if (!str_contains($bundle, '£650')) {
    $bad('bundle coverage page missing £650');
}

$cctv = $render('/pages/coverage/cctv/' . $first);
if (!str_contains($cctv, 'Price on application after scope.')) {
    $bad('CCTV page should stay POA');
}

$index = $render('/pages/coverage');
if (!str_contains($index, '£249') || !str_contains($index, 'noindex')) {
    $bad('coverage index missing price or noindex');
}

$_SERVER['REQUEST_URI'] = '/pages/coverage/electrical/not-a-real-mainland-slug';
http_response_code(200);
ob_start();
routerHandleRequest();
$missing = (string)ob_get_clean();
if (http_response_code() !== 404 || !str_contains($missing, 'not in the mainland')) {
    $bad('unknown area should 404, got ' . http_response_code());
}

$catalogue = coverageCatalogue();
$summaryFile = coverageSummaryFile();
$summary = json_decode((string)file_get_contents($summaryFile), true);
if (($summary['areas'] ?? 0) !== 1131) {
    $bad('summary areas != 1131');
}
if (($summary['services'] ?? 0) !== count($catalogue)) {
    $bad('summary services != catalogue');
}
if (($summary['cells'] ?? 0) !== 1131 * count($catalogue)) {
    $bad('summary cell count mismatch');
}
if (($summary['live_sitemap_includes_coverage'] ?? true) !== false) {
    $bad('summary must record that the live sitemap excludes coverage');
}

$tier3 = (string)file_get_contents(coverageSitemapsDir() . '/tier-3-service-area.xml');
if (substr_count($tier3, '<url>') !== 0) {
    $bad('tier 3 should be empty before any bucket is filled');
}
if (!str_contains($tier3, 'NON-PROD')) {
    $bad('tier 3 missing NON-PROD marker');
}

$export = (string)file_get_contents(dirname(__DIR__) . '/bin/static-export.php');
if (!str_contains($export, 'ICOMPLY_EXPORT_COVERAGE')) {
    $bad('static export is missing the coverage skip');
}

echo ($fail === 0 ? "PASS\n" : "FAIL ({$fail})\n");
exit($fail === 0 ? 0 : 1);
