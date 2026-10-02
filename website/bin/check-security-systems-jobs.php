#!/usr/bin/env php
<?php
/**
 * Assert the security-systems lane is the intruder/alarm set and nothing
 * from CCTV, access control, door entry or intercoms.
 *
 * Usage:
 *   php website/bin/check-security-systems-jobs.php
 *   php website/bin/check-security-systems-jobs.php --skip-build
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

$options = getopt('', ['dist::', 'require-dist', 'skip-render', 'skip-build']);
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once SITE_ROOT . '/includes/sitemap.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$repoRoot = dirname(SITE_ROOT);
$dist = $options['dist'] ?? ($repoRoot . '/dist');
if ($dist !== '' && $dist[0] !== '/') {
    $dist = $repoRoot . '/' . ltrim((string)$dist, '/');
}
$requireDist = isset($options['require-dist']);
$skipRender = isset($options['skip-render']);
$skipBuild = isset($options['skip-build']);

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

$expected = securitySystemsExpectedCount();

if (!$skipBuild) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/build-security-systems-jobs.php');
    passthru($cmd, $buildCode);
    if ($buildCode !== 0) {
        $bad('build-security-systems-jobs.php exited ' . $buildCode);
        echo "security_page_count=0 expected={$expected}\n";
        exit(1);
    }
    securitySystemsReset();
    loadJsonData('__clear__');
}

$gen = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/generate-security-systems-pages.php');
passthru($gen, $genCode);
if ($genCode !== 0) {
    $bad('generate-security-systems-pages.php exited ' . $genCode);
}

$jobs = securitySystemsJobs();
$slugs = securitySystemsSlugs();
$unique = array_values(array_unique($slugs));
$pageCount = count($unique);

if (count($slugs) !== $expected) {
    $bad('Security systems slug count ' . count($slugs) . " !== {$expected}");
} else {
    $ok("catalogue has exactly {$expected} slugs");
}
if ($pageCount !== $expected) {
    $bad('slugs not unique (' . $pageCount . ')');
} else {
    $ok('slugs all unique');
}

$wrong = [];
$excluded = securitySystemsExcludedServices();
foreach ($jobs as $job) {
    if (!is_array($job)) {
        $wrong[] = 'non-array';
        continue;
    }
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $service = (string)($job['service_type'] ?? '');
    if (($job['category'] ?? '') !== securitySystemsCategory() || ($job['lane'] ?? '') !== securitySystemsLane()) {
        $wrong[] = $slug . ':category';
    }
    if ($service !== securitySystemsService() || in_array($service, $excluded, true)) {
        $wrong[] = $slug . ':service';
    }
    if (securitySystemsSlugIsOutOfLane($slug)) {
        $wrong[] = $slug . ':out-of-lane';
    }
    foreach (['slug', 'status', 'name', 'h1', 'seo_title'] as $field) {
        if (trim((string)($job[$field] ?? '')) === '') {
            $wrong[] = $slug . ':' . $field;
        }
    }
}
if ($wrong) {
    $bad('lane rows invalid: ' . implode(',', array_slice($wrong, 0, 8)));
} else {
    $ok('every row is intruder-alarm Security systems (no CCTV/access/door-entry/intercom)');
}

$keywords = getMajorKeywords();
$missingKw = [];
foreach ($unique as $slug) {
    $row = $keywords[$slug] ?? null;
    if (!is_array($row) || ($row['service'] ?? '') !== securitySystemsService() || ($row['lane'] ?? '') !== securitySystemsLane()) {
        $missingKw[] = $slug;
    }
}
if ($missingKw) {
    $bad('catalogue missing or wrong service on ' . count($missingKw) . ' slugs');
} else {
    $ok('all slugs present in getMajorKeywords() as intruder-alarm');
}

$leaked = [];
foreach ($keywords as $slug => $row) {
    if (!is_array($row) || ($row['lane'] ?? '') !== securitySystemsLane()) {
        continue;
    }
    if (securitySystemsSlugIsOutOfLane((string)$slug) || ($row['service'] ?? '') !== securitySystemsService()) {
        $leaked[] = (string)$slug;
    }
}
if ($leaked) {
    $bad('lane overlay leaked non-alarm slugs: ' . implode(',', array_slice($leaked, 0, 8)));
} else {
    $ok('overlay contains no CCTV, access, door-entry or intercom jobs');
}

$missingFiles = [];
foreach ($unique as $slug) {
    if (!is_file(securitySystemsStubPath($slug))) {
        $missingFiles[] = $slug;
    }
}
if ($missingFiles) {
    $bad('output files missing: ' . count($missingFiles));
} else {
    $ok("all {$expected} output files exist under pages/keywords/");
}

$entries = icomplySitemapEntries();
$sitemapPaths = array_column($entries, 'path');
$sitemapMissing = [];
foreach ($unique as $slug) {
    if (!in_array('/pages/keywords/' . $slug, $sitemapPaths, true)) {
        $sitemapMissing[] = $slug;
    }
}
if ($sitemapMissing) {
    $bad('sitemap missing ' . count($sitemapMissing) . ' security-systems locs');
} else {
    $ok('sitemap includes all ' . $expected . ' security-systems pages');
}

$titles = [];
$h1s = [];
$metas = [];
$seoFail = [];
$rendered = 0;

if (!$skipRender) {
    foreach ($unique as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $row = $keywords[$slug] ?? [];
        $title = (string)($row['seo_title'] ?? '');
        $h1 = (string)($row['h1'] ?? '');
        $meta = (string)($row['meta_desc'] ?? '');
        $titles[$title] = ($titles[$title] ?? 0) + 1;
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
        $metas[$meta] = ($metas[$meta] ?? 0) + 1;

        $needles = [
            '<title>',
            '<h1',
            'name="description"',
            'rel="canonical"',
            '/pages/keywords/' . $slug,
            'FAQPage',
            'Security systems',
            'Intruder Alarms',
            'Price on application',
            '#quote',
            '#0B1F3A',
            '#ff6b00',
            '/pages/areas',
            '/contact',
            'Submit request',
        ];
        $missing = [];
        foreach ($needles as $needle) {
            if (!str_contains($html, $needle)) {
                $missing[] = $needle;
            }
        }
        if (!str_contains($html, $h1)) {
            $missing[] = 'h1-text';
        }
        if (preg_match('/£\s*\d/', $html)) {
            $missing[] = 'invented-£';
        }
        if (strlen($html) < 1800) {
            $missing[] = 'short-html';
        }
        if ($missing) {
            $seoFail[] = $slug . '(' . implode('|', $missing) . ')';
        }
    }

    $dupTitles = array_filter($titles, static fn(int $n): bool => $n > 1);
    $dupH1 = array_filter($h1s, static fn(int $n): bool => $n > 1);
    $dupMeta = array_filter($metas, static fn(int $n): bool => $n > 1 || $n === 0);
    if ($dupTitles) {
        $bad('duplicate titles: ' . count($dupTitles));
    } else {
        $ok('unique titles for all security-systems jobs');
    }
    if ($dupH1) {
        $bad('duplicate H1s: ' . count($dupH1));
    } else {
        $ok('unique H1s for all security-systems jobs');
    }
    if (isset($metas['']) || $dupMeta) {
        $bad('duplicate or empty meta descriptions: ' . count($dupMeta));
    } else {
        $ok('unique meta descriptions');
    }
    if ($seoFail) {
        $bad('SEO tags missing on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 5)) . ')');
    } else {
        $ok("required SEO tags present on {$rendered} security-systems pages");
    }
}

echo PHP_EOL . "security_page_count={$pageCount} expected={$expected} rendered={$rendered}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
