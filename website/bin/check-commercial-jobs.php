#!/usr/bin/env php
<?php
/**
 * Commercial property-compliance lane.
 * Renders every /pages/commercial/{slug} job, the hub, and sitemap locs.
 *
 * Usage: php website/bin/check-commercial-jobs.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/commercial-jobs.php';
require_once SITE_ROOT . '/includes/sitemap.php';

$fail = 0;
$bad = static function (string $msg) use (&$fail): void {
    echo "FAIL: {$msg}\n";
    $fail++;
};

$jobs = commercialJobs();
$expected = commercialJobsExpectedCount();
if (count($jobs) !== $expected || $expected < 40) {
    $bad('catalogue count ' . count($jobs) . " declared {$expected}");
}

$services = getServices();
$keywords = getMajorKeywords();
$seen = [
    'slug' => [],
    'title' => [],
    'h1' => [],
    'meta' => [],
    'intro' => [],
    'accent' => [],
];
$slugs = [];

foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $slugs[$slug] = true;
    $service = areaSlug((string)($job['service'] ?? ''));
    if ($slug === '' || isset($seen['slug'][$slug])) {
        $bad("bad slug {$slug}");
        continue;
    }
    $seen['slug'][$slug] = true;
    if (!isset($services[$service])) {
        $bad("{$slug} service {$service} missing");
    }
    foreach (['title', 'h1', 'meta', 'intro'] as $field) {
        $value = strtolower(trim((string)($job[$field] ?? '')));
        if ($value === '') {
            $bad("{$slug} empty {$field}");
            continue;
        }
        if (isset($seen[$field][$value])) {
            $bad("{$slug} duplicate {$field}");
        }
        $seen[$field][$value] = true;
    }
    $accent = strtolower(trim((string)($job['h1_accent'] ?? '')));
    if ($accent === '' || isset($seen['accent'][$accent])) {
        $bad("{$slug} accent");
    }
    $seen['accent'][$accent] = true;
    if (count((array)($job['paragraphs'] ?? [])) < 2 || count((array)($job['points'] ?? [])) < 4 || count((array)($job['faqs'] ?? [])) < 3) {
        $bad("{$slug} thin content");
    }
    $blob = json_encode($job);
    if (!is_string($blob) || str_contains($blob, '£') || preg_match('/\bfrom\s+\d/i', $blob)) {
        $bad("{$slug} looks priced");
    }
    $kw = keywordSlug((string)($job['keyword'] ?? ''));
    if ($kw !== '' && isset($keywords[$kw])) {
        $existingIntro = trim((string)($keywords[$kw]['intro'] ?? ''));
        if ($existingIntro !== '' && $existingIntro === trim((string)$job['intro'])) {
            $bad("{$slug} intro copies keyword guide");
        }
    }
    if (!is_file(commercialJobStubPath($slug))) {
        $bad("{$slug} stub missing");
    }
    foreach ((array)($job['related'] ?? []) as $rel) {
        if (commercialJob((string)$rel) === null) {
            $bad("{$slug} related " . (string)$rel);
        }
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$render = static function (string $file) use ($bad): string {
    if (!is_file($file)) {
        $bad("missing {$file}");
        return '';
    }
    ob_start();
    try {
        require $file;
    } catch (Throwable $e) {
        ob_end_clean();
        $bad($e->getMessage() . ' in ' . basename($file));
        return '';
    }
    return (string)ob_get_clean();
};

foreach ($jobs as $job) {
    $slug = keywordSlug((string)$job['slug']);
    $_SERVER['REQUEST_URI'] = commercialJobPath($slug);
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $html = $render(commercialJobStubPath($slug));
    if ($html === '' || !str_contains($html, '<!DOCTYPE')) {
        $bad("{$slug} did not render");
        continue;
    }
    $name = (string)$job['name'];
    $h1 = (string)$job['h1'];
    $canonical = url(commercialJobPath($slug) . '.php');
    foreach ([$name, $h1, 'FAQPage', 'Price on application', $canonical, 'Request a POA quote'] as $needle) {
        if (!str_contains($html, $needle)) {
            $bad("{$slug} missing " . substr($needle, 0, 80));
        }
    }
    $priceScan = preg_replace('/"priceRange"\s*:\s*"££"/', '', $html) ?? $html;
    if (str_contains($priceScan, '£')) {
        $bad("{$slug} rendered a price");
    }
    $titleHtml = htmlspecialchars((string)$job['title'], ENT_QUOTES, 'UTF-8');
    if (!str_contains($html, '<title>' . $titleHtml)) {
        $bad("{$slug} title tag");
    }
}

$_SERVER['REQUEST_URI'] = '/pages/commercial';
$hub = $render(SITE_ROOT . '/pages/commercial.php');
if (!str_contains($hub, 'id="jobs"')) {
    $bad('hub missing #jobs');
}
foreach (array_keys($slugs) as $slug) {
    if (!str_contains($hub, commercialJobPath($slug))) {
        $bad("hub missing link {$slug}");
    }
}

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
if (!str_contains($xml, '/pages/commercial</loc>')) {
    $bad('sitemap missing commercial hub');
}
foreach (array_keys($slugs) as $slug) {
    if (!str_contains($xml, commercialJobPath($slug) . '</loc>')) {
        $bad("sitemap missing {$slug}");
    }
}

if ($fail === 0) {
    echo 'PASS commercial jobs ' . count($jobs) . " rendered, hub linked, sitemap locs present\n";
    exit(0);
}
echo "FAILED {$fail}\n";
exit(1);
