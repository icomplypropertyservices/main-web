#!/usr/bin/env php
<?php
/**
 * Assert Fire category job coverage is 390/390 and every output file exists.
 *
 * Usage:
 *   php website/bin/check-fire-job-pages.php
 *   php website/bin/check-fire-job-pages.php --dist=dist --require-dist
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

$expected = fireJobTypesExpectedCount();

if (!$skipBuild) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/build-fire-job-pages.php');
    passthru($cmd, $buildCode);
    if ($buildCode !== 0) {
        $bad('build-fire-job-pages.php exited ' . $buildCode);
        echo "fire_page_count=0 expected={$expected}\n";
        exit(1);
    }
    fireJobTypesReset();
    loadJsonData('__clear__');
}

$jobs = fireJobTypesJobs();
$slugs = fireJobTypesSlugs();
$unique = array_values(array_unique($slugs));
$firePageCount = count($unique);

if (count($slugs) !== $expected) {
    $bad('Fire slug count ' . count($slugs) . " !== {$expected}");
} else {
    $ok("Fire catalogue has exactly {$expected} slugs");
}
if ($firePageCount !== $expected) {
    $bad('Fire slugs not unique (' . $firePageCount . ')');
} else {
    $ok('Fire slugs all unique');
}

$wrongCat = [];
foreach ($jobs as $job) {
    if (!is_array($job)) {
        continue;
    }
    if (($job['category'] ?? '') !== 'Fire') {
        $wrongCat[] = (string)($job['slug'] ?? '?');
    }
    if (empty($job['service_type']) || empty($job['slug']) || empty($job['status'])) {
        $wrongCat[] = (string)($job['slug'] ?? '?') . ':missing-fields';
    }
}
if ($wrongCat) {
    $bad('Fire rows missing category/service_type/slug/status: ' . implode(',', array_slice($wrongCat, 0, 8)));
} else {
    $ok('every Fire row has category, service_type, slug, status');
}

$keywords = getMajorKeywords();
$missingKw = [];
foreach ($unique as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
    }
}
if ($missingKw) {
    $bad('catalogue missing ' . count($missingKw) . ' Fire slugs (sample ' . implode(',', array_slice($missingKw, 0, 8)) . ')');
} else {
    $ok('all Fire slugs present in getMajorKeywords()');
}

$missingFiles = [];
foreach ($unique as $slug) {
    if (!is_file(fireJobTypesStubPath($slug))) {
        $missingFiles[] = $slug;
    }
}
if ($missingFiles) {
    $bad('output files missing: ' . count($missingFiles) . ' (sample ' . implode(',', array_slice($missingFiles, 0, 8)) . ')');
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
    $bad('sitemap missing ' . count($sitemapMissing) . ' Fire job locs');
} else {
    $ok('sitemap includes all ' . $expected . ' Fire job pages');
}

$titles = [];
$h1s = [];
$seoFail = [];
$rendered = 0;

if (!$skipRender) {
    foreach ($unique as $i => $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $row = $keywords[$slug] ?? [];
        $title = (string)($row['seo_title'] ?? $row['name'] ?? $slug);
        $h1 = (string)($row['h1'] ?? $row['name'] ?? $slug);
        $titles[$title] = ($titles[$title] ?? 0) + 1;
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;

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
            '/contact',
            'Submit request',
        ];
        $missing = [];
        foreach ($needles as $n) {
            if (!str_contains($html, $n)) {
                $missing[] = $n;
            }
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

        if (($i + 1) % 100 === 0) {
            fwrite(STDERR, 'rendered ' . ($i + 1) . "/{$expected}\n");
        }
    }

    $dupTitles = array_filter($titles, static fn(int $n): bool => $n > 1);
    $dupH1 = array_filter($h1s, static fn(int $n): bool => $n > 1);
    if ($dupTitles) {
        $bad('duplicate titles: ' . count($dupTitles));
    } else {
        $ok('unique titles for all Fire jobs');
    }
    if ($dupH1) {
        $bad('duplicate H1s: ' . count($dupH1));
    } else {
        $ok('unique H1s for all Fire jobs');
    }
    if ($seoFail) {
        $bad('SEO tags missing on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 5)) . ')');
    } else {
        $ok("required SEO tags present on {$rendered} Fire pages");
    }
}

$distMissing = [];
$distPresent = is_dir($dist);
if ($distPresent) {
    foreach ($unique as $slug) {
        $php = $dist . '/pages/keywords/' . $slug . '.php';
        $idx = $dist . '/pages/keywords/' . $slug . '/index.html';
        if (!is_file($php) && !is_file($idx)) {
            $distMissing[] = $slug;
        }
    }
    if ($distMissing) {
        $bad('dist missing ' . count($distMissing) . ' Fire output files');
    } else {
        $ok("all {$expected} Fire output files exist under dist/pages/keywords/");
    }
} elseif ($requireDist) {
    $bad("dist/ missing at {$dist} (--require-dist)");
} else {
    echo "SKIP dist file check (no dist/; use --require-dist after static-export)\n";
}

echo PHP_EOL . "fire_page_count={$firePageCount} expected={$expected} rendered={$rendered}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
