<?php
/**
 * Render a stratified HTML sample for scripts/seo_ai_page_review.py.
 *
 * Usage: ICOMPLY_STATIC_EXPORT=1 php website/bin/render-seo-sample.php /tmp/seo-ai-sample
 * Not a production deploy. Canonicals use the public host so the gate sees real URLs.
 */
declare(strict_types=1);

$out = $argv[1] ?? '';
if ($out === '') {
    fwrite(STDERR, "Usage: php website/bin/render-seo-sample.php <output-dir>\n");
    exit(1);
}
if (!is_dir($out) && !mkdir($out, 0777, true) && !is_dir($out)) {
    fwrite(STDERR, "Cannot create {$out}\n");
    exit(1);
}

$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';

require_once dirname(__DIR__) . '/includes/render.php';

function seo_sample_capture(callable $fn): string
{
    ob_start();
    try {
        $fn();
        $html = ob_get_clean();
        return is_string($html) ? $html : '';
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        return "<!-- RENDER_ERROR " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . " -->\n";
    }
}

function seo_sample_write(string $dir, array &$manifest, array $row, string $html): void
{
    $file = $row['file'];
    file_put_contents($dir . '/' . $file, $html);
    $row['bytes'] = strlen($html);
    $manifest[] = $row;
}

$manifest = [];
$services = getServices();
$areas = getAreas();

$nationwideTowns = ['Manchester', 'Stockport', 'Burnley', 'Liverpool', 'Carlisle'];
$nationwideServices = ['fire-alarms', 'aov-air-handling', 'nurse-call', 'access-control', 'emergency-lighting', 'fire-risk-assessments'];
$localTowns = ['Manchester', 'Stockport', 'Burnley'];
$localServices = ['electrical', 'gas-systems', 'cctv', 'kitchens', 'legionella-risk-assessment', 'pat-testing'];
$outOfPolicy = [
    ['kitchens', 'Carlisle'],
    ['electrical', 'Preston'],
];

foreach ($services as $slug => $name) {
    $file = 'service-hub__' . $slug . '.html';
    $html = seo_sample_capture(static function () use ($slug) {
        renderServiceHubPage($slug);
    });
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => 'service-hub',
        'path' => '/pages/services/' . $slug,
        'service' => $slug,
        'service_name' => $name,
        'area' => '',
        'keyword' => '',
        'keyword_name' => '',
        'expect_index' => true,
    ], $html);
}

$pairs = [];
foreach ($nationwideServices as $slug) {
    foreach ($nationwideTowns as $town) {
        $pairs[] = [$slug, $town];
    }
}
foreach ($localServices as $slug) {
    foreach ($localTowns as $town) {
        $pairs[] = [$slug, $town];
    }
}
foreach ($outOfPolicy as $pair) {
    $pairs[] = $pair;
}

foreach ($pairs as [$slug, $town]) {
    if (!isset($services[$slug]) || !in_array($town, $areas, true)) {
        fwrite(STDERR, "Skip missing {$slug} / {$town}\n");
        continue;
    }
    $file = 'service-area__' . $slug . '__' . areaSlug($town) . '.html';
    $html = seo_sample_capture(static function () use ($slug, $town) {
        renderServiceAreaPage($slug, $town);
    });
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => 'service-area',
        'path' => '/pages/' . $slug . '/' . areaSlug($town),
        'service' => $slug,
        'service_name' => $services[$slug],
        'area' => $town,
        'keyword' => '',
        'keyword_name' => '',
        'expect_index' => seo_local_landing_indexable($slug, $town),
    ], $html);
}

foreach (['Manchester', 'Stockport', 'Burnley', 'Carlisle'] as $town) {
    $file = 'area-hub__' . areaSlug($town) . '.html';
    $html = seo_sample_capture(static function () use ($town) {
        renderAreaHubPage($town);
    });
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => 'area-hub',
        'path' => '/pages/areas/' . areaSlug($town),
        'service' => '',
        'service_name' => '',
        'area' => $town,
        'keyword' => '',
        'keyword_name' => '',
        'expect_index' => true,
    ], $html);
}

$keywordSlugs = [
    'fire-alarm-installation',
    'nurse-call-system',
    'aov-system',
    'car-park-barrier-access',
    'eicr',
    'legionella-risk-assessment',
];
$keywords = getMajorKeywords();
foreach ($keywordSlugs as $kw) {
    if (!isset($keywords[$kw])) {
        fwrite(STDERR, "Missing keyword {$kw}\n");
        continue;
    }
    $file = 'keyword-hub__' . $kw . '.html';
    $html = seo_sample_capture(static function () use ($kw) {
        renderKeywordPage($kw);
    });
    $svc = (string)($keywords[$kw]['service'] ?? '');
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => 'keyword-hub',
        'path' => '/pages/keywords/' . $kw,
        'service' => $svc,
        'service_name' => $services[$svc] ?? $svc,
        'area' => '',
        'keyword' => $kw,
        'keyword_name' => (string)($keywords[$kw]['name'] ?? $kw),
        'expect_index' => true,
    ], $html);
}

$keywordTowns = [
    ['fire-alarm-installation', 'Manchester'],
    ['fire-alarm-installation', 'Carlisle'],
    ['nurse-call-system', 'Burnley'],
    ['aov-system', 'Liverpool'],
    ['car-park-barrier-access', 'Preston'],
    ['eicr', 'Manchester'],
    ['eicr', 'Carlisle'],
    ['legionella-risk-assessment', 'Stockport'],
];
foreach ($keywordTowns as [$kw, $town]) {
    if (!isset($keywords[$kw])) {
        continue;
    }
    $file = 'keyword-area__' . $kw . '__' . areaSlug($town) . '.html';
    $html = seo_sample_capture(static function () use ($kw, $town) {
        renderKeywordAreaPage($kw, $town);
    });
    $svc = (string)($keywords[$kw]['service'] ?? '');
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => 'keyword-area',
        'path' => '/pages/keywords/' . $kw . '/' . areaSlug($town),
        'service' => $svc,
        'service_name' => $services[$svc] ?? $svc,
        'area' => $town,
        'keyword' => $kw,
        'keyword_name' => (string)($keywords[$kw]['name'] ?? $kw),
        'expect_index' => seo_local_landing_indexable($svc, $town),
    ], $html);
}

foreach (['kentec', 'se-controls'] as $mfr) {
    $file = 'manufacturer__' . $mfr . '.html';
    $html = seo_sample_capture(static function () use ($mfr) {
        renderManufacturerPage($mfr);
    });
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => 'manufacturer',
        'path' => '/pages/manufacturers/' . $mfr,
        'service' => '',
        'service_name' => '',
        'area' => '',
        'keyword' => '',
        'keyword_name' => $mfr,
        'expect_index' => true,
    ], $html);
}

$static = [
    ['home', '/', static function () { require SITE_ROOT . '/index.php'; }, 'home'],
    ['about', '/pages/about', static function () { require SITE_ROOT . '/pages/about.php'; }, 'static'],
    ['contact', '/contact', static function () { require SITE_ROOT . '/contact.php'; }, 'static'],
    ['resource-fire-alarm', '/pages/resources/fire-alarm-servicing', static function () { require SITE_ROOT . '/pages/resources/fire-alarm-servicing.php'; }, 'resource'],
];
foreach ($static as [$id, $path, $fn, $family]) {
    $file = 'static__' . $id . '.html';
    $html = seo_sample_capture($fn);
    seo_sample_write($out, $manifest, [
        'file' => $file,
        'family' => $family,
        'path' => $path,
        'service' => '',
        'service_name' => '',
        'area' => '',
        'keyword' => '',
        'keyword_name' => '',
        'expect_index' => true,
    ], $html);
}

file_put_contents($out . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$failRender = 0;
foreach ($manifest as $row) {
    if (str_contains((string)file_get_contents($out . '/' . $row['file']), 'RENDER_ERROR') || str_contains((string)file_get_contents($out . '/' . $row['file']), 'Fatal error')) {
        $failRender++;
        fwrite(STDERR, "RENDER FAIL {$row['file']}\n");
    }
}
fwrite(STDOUT, 'Wrote ' . count($manifest) . " pages to {$out} ({$failRender} render failures)\n");
exit($failRender > 0 ? 2 : 0);
