<?php
/**
 * Wave-1 SEO IA checks: money jobs, manufacturer children, new areas.
 * Usage: php website/bin/check-seo-ia-wave1.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';

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

$jobs = seoIaWave1JobSlugs();
$keywords = getMajorKeywords();
if (count($jobs) < 28) {
    $bad('expected 28+ wave-1 jobs, got ' . count($jobs));
} else {
    $ok(count($jobs) . ' wave-1 job slugs');
}

foreach ($jobs as $slug) {
    if (!isset($keywords[$slug])) {
        $bad("keyword missing: {$slug}");
        continue;
    }
    $row = $keywords[$slug];
    if (empty($row['name']) || empty($row['service'])) {
        $bad("{$slug} missing name/service");
    }
}

foreach (['trafford', 'glossop', 'buxton'] as $areaSlug) {
    $name = areaFromSlug($areaSlug);
    if ($name === null) {
        $bad("area missing: {$areaSlug}");
    } else {
        $ok("area {$areaSlug} = {$name}");
    }
}

foreach (seoIaWave1ManufacturerSlugs() as $slug) {
    $entry = getManufacturerBySlug($slug);
    if (!$entry) {
        $bad("manufacturer missing: {$slug}");
    }
}

$sampleJobs = ['eicr', 'eicr-certificate', 'gas-safety-certificate', 'cp12', 'fire-risk-assessment', 'pat-testing', 'epc', 'fire-door-compliance', 'cctv-installation'];
foreach ($sampleJobs as $slug) {
    ob_start();
    renderKeywordPage($slug);
    $html = (string)ob_get_clean();
    $row = $keywords[$slug] ?? [];
    $h1 = (string)($row['h1'] ?? $row['name'] ?? $slug);
    $needles = [
        '<h1',
        'FAQPage',
        'og:image',
        htmlspecialchars($h1, ENT_QUOTES, 'UTF-8'),
        '#0B1F3A',
        '#ff6b00',
        'All brands',
    ];
    $missing = [];
    foreach ($needles as $n) {
        if (!str_contains($html, $n)) {
            $missing[] = $n;
        }
    }
    if (preg_match('/£\s*\d/', $html)) {
        $missing[] = 'invented-£-price';
    }
    if ($missing || strlen($html) < 2000) {
        $bad("job {$slug} " . implode(', ', $missing) . ' len=' . strlen($html));
    } else {
        $ok("job {$slug} title/H1/FAQ/OG/mfr");
    }
}

$sampleMfr = ['apollo', 'advanced', 'ventlux', 'aico', 'came', 'bell-system', 'ajax', 'vesda'];
foreach ($sampleMfr as $slug) {
    ob_start();
    renderManufacturerPage($slug);
    $html = (string)ob_get_clean();
    $missing = [];
    foreach (['<h1', 'FAQPage', 'og:image', 'Related job', 'POA'] as $n) {
        if (!str_contains($html, $n)) {
            $missing[] = $n;
        }
    }
    if ($missing || strlen($html) < 1500) {
        $bad("mfr {$slug} " . implode(', ', $missing) . ' len=' . strlen($html));
    } else {
        $ok("mfr {$slug} H1/FAQ/jobs/POA");
    }
}

foreach (['Trafford', 'Glossop', 'Buxton'] as $area) {
    ob_start();
    renderAreaHubPage($area);
    $html = (string)ob_get_clean();
    $missing = [];
    foreach (['<h1', 'FAQPage', htmlspecialchars($area, ENT_QUOTES, 'UTF-8'), 'og:image'] as $n) {
        if (!str_contains($html, $n)) {
            $missing[] = $n;
        }
    }
    if ($missing) {
        $bad("area {$area} " . implode(', ', $missing));
    } else {
        $ok("area {$area} unique+FAQ+OG");
    }
}

require_once SITE_ROOT . '/includes/sitemap.php';
$entries = icomplySitemapEntries();
$paths = array_column($entries, 'path');
foreach (['/pages/keywords/eicr', '/pages/keywords/epc', '/pages/keywords/fire-door-compliance', '/pages/manufacturers/advanced', '/pages/manufacturers/ventlux', '/pages/areas/trafford', '/pages/areas/glossop', '/pages/areas/buxton'] as $need) {
    if (!in_array($need, $paths, true)) {
        $bad("sitemap missing {$need}");
    } else {
        $ok("sitemap {$need}");
    }
}

echo PHP_EOL . ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
