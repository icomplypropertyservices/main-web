#!/usr/bin/env php
<?php
/**
 * Assert the Emergency Lighting job lane is 62/62 and every page renders.
 *
 * Usage:
 *   php website/bin/check-emergency-lighting-job-pages.php
 *   php website/bin/check-emergency-lighting-job-pages.php --skip-build
 *   php website/bin/check-emergency-lighting-job-pages.php --dist=dist --require-dist
 */
declare(strict_types=1);

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

$expected = emergencyLightingJobTypesExpectedCount();

if (!$skipBuild) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/build-emergency-lighting-job-pages.php');
    passthru($cmd, $buildCode);
    if ($buildCode !== 0) {
        $bad('build-emergency-lighting-job-pages.php exited ' . $buildCode);
        echo "emergency_lighting_page_count=0 expected={$expected} rendered=0\n";
        exit(1);
    }
    emergencyLightingJobTypesReset();
    loadJsonData('__clear__');
}

$jobs = emergencyLightingJobTypesJobs();
$slugs = emergencyLightingJobTypesSlugs();
$unique = array_values(array_unique($slugs));
$pageCount = count($unique);

if (count($slugs) !== $expected) {
    $bad('Emergency lighting slug count ' . count($slugs) . " !== {$expected}");
} else {
    $ok("catalogue has exactly {$expected} slugs");
}
if ($pageCount !== $expected) {
    $bad('slugs not unique (' . $pageCount . ')');
} else {
    $ok('slugs all unique');
}

$badRows = [];
foreach ($jobs as $job) {
    if (!is_array($job)) {
        $badRows[] = '?';
        continue;
    }
    if (($job['category'] ?? '') !== 'Emergency Lighting') {
        $badRows[] = (string)($job['slug'] ?? '?');
    }
    if (($job['service_type'] ?? '') !== 'emergency-lighting') {
        $badRows[] = (string)($job['slug'] ?? '?') . ':service';
    }
    if (empty($job['slug']) || empty($job['status']) || empty($job['name'])) {
        $badRows[] = (string)($job['slug'] ?? '?') . ':missing-fields';
    }
    if (emergencyLightingJobIsExcluded((string)($job['slug'] ?? ''))) {
        $badRows[] = (string)$job['slug'] . ':excluded';
    }
}
if ($badRows) {
    $bad('rows failed shape check: ' . implode(',', array_slice($badRows, 0, 8)));
} else {
    $ok('every row is Emergency Lighting / emergency-lighting / named / live');
}

$keywords = getMajorKeywords();
$missingKw = [];
foreach ($unique as $slug) {
    $row = $keywords[$slug] ?? null;
    if (!is_array($row) || empty($row['el_master']) || ($row['service'] ?? '') !== 'emergency-lighting') {
        $missingKw[] = $slug;
    }
}
if ($missingKw) {
    $bad('getMajorKeywords() missing ' . count($missingKw) . ' lane slugs');
} else {
    $ok('all lane slugs present in getMajorKeywords()');
}

if (isset($keywords['emergency-lighting-near-me']['el_master'])) {
    $bad('near-me doorway was marked as a lane job');
} else {
    $ok('near-me doorway stays outside the lane');
}

$missingFiles = [];
foreach ($unique as $slug) {
    if (!is_file(emergencyLightingJobTypesStubPath($slug))) {
        $missingFiles[] = $slug;
    }
}
if ($missingFiles) {
    $bad('stubs missing: ' . count($missingFiles));
} else {
    $ok("all {$expected} stubs exist under pages/keywords/");
}

if (!is_file(SITE_ROOT . '/pages/emergency-lighting-jobs.php')) {
    $bad('lane index page missing');
} else {
    $ok('lane index page exists');
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
    $bad('sitemap missing ' . count($sitemapMissing) . ' job locs');
} else {
    $ok('sitemap includes all ' . $expected . ' job pages');
}
if (!in_array('/pages/emergency-lighting-jobs', $sitemapPaths, true)) {
    $bad('sitemap missing /pages/emergency-lighting-jobs');
} else {
    $ok('sitemap includes the lane index');
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
        $title = (string)($row['seo_title'] ?? $row['name'] ?? $slug);
        $h1 = (string)($row['h1'] ?? $row['name'] ?? $slug);
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
            '#quote',
            '#0B1F3A',
            '#ff6b00',
            '/pages/areas',
            '/pages/emergency-lighting-jobs',
            '/pages/services/emergency-lighting',
            '/contact',
            'Submit request',
            'POA',
        ];
        $missing = [];
        foreach ($needles as $needle) {
            if (!str_contains($html, $needle)) {
                $missing[] = $needle;
            }
        }
        if (!str_contains($html, '>' . htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') . '<')) {
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

    ob_start();
    renderEmergencyLightingJobsHub();
    $hub = (string)ob_get_clean();
    $hubMissing = [];
    foreach (['<h1', '#0B1F3A', '#ff6b00', 'POA', '/pages/areas', 'Submit request', '/contact'] as $needle) {
        if (!str_contains($hub, $needle)) {
            $hubMissing[] = $needle;
        }
    }
    if (preg_match('/£\s*\d/', $hub)) {
        $hubMissing[] = 'invented-£';
    }
    foreach ($unique as $slug) {
        if (!str_contains($hub, '/pages/keywords/' . $slug)) {
            $hubMissing[] = $slug;
            break;
        }
    }
    if ($hubMissing) {
        $bad('lane index render missing ' . implode('|', array_slice($hubMissing, 0, 6)));
    } else {
        $ok('lane index lists every job');
    }

    $dupTitles = array_filter($titles, static fn(int $n): bool => $n > 1);
    $dupH1 = array_filter($h1s, static fn(int $n): bool => $n > 1);
    $dupMeta = array_filter($metas, static fn(int $n): bool => $n > 1);
    if ($dupTitles) {
        $bad('duplicate titles: ' . count($dupTitles));
    } else {
        $ok('unique titles');
    }
    if ($dupH1) {
        $bad('duplicate H1s: ' . count($dupH1));
    } else {
        $ok('unique H1s');
    }
    if ($dupMeta) {
        $bad('duplicate meta descriptions: ' . count($dupMeta));
    } else {
        $ok('unique meta descriptions');
    }
    if ($seoFail) {
        $bad('SEO tags missing on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 4)) . ')');
    } else {
        $ok("required SEO tags present on {$rendered} pages");
    }
}

$distPresent = is_dir($dist);
if ($distPresent) {
    $distMissing = [];
    foreach ($unique as $slug) {
        $php = $dist . '/pages/keywords/' . $slug . '.php';
        $idx = $dist . '/pages/keywords/' . $slug . '/index.html';
        if (!is_file($php) && !is_file($idx)) {
            $distMissing[] = $slug;
        }
    }
    $hubDist = $dist . '/pages/emergency-lighting-jobs.php';
    $hubIdx = $dist . '/pages/emergency-lighting-jobs/index.html';
    if (!is_file($hubDist) && !is_file($hubIdx)) {
        $distMissing[] = 'emergency-lighting-jobs';
    }
    if ($distMissing) {
        $bad('dist missing ' . count($distMissing) . ' lane files');
    } else {
        $ok('dist contains the lane index and all job pages');
    }
} elseif ($requireDist) {
    $bad("dist/ missing at {$dist}");
} else {
    echo "SKIP dist file check (no dist/; use --require-dist after static-export)\n";
}

echo PHP_EOL . "emergency_lighting_page_count={$pageCount} expected={$expected} rendered={$rendered}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
