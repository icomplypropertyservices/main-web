#!/usr/bin/env php
<?php
/**
 * Assert the access-control lane is barrier-led, draft, and not a prod promote.
 *
 * Usage: php website/bin/check-access-control-jobs.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

$options = getopt('', ['skip-build', 'skip-render']);
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once SITE_ROOT . '/includes/access-control-jobs.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

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

if (!isset($options['skip-build'])) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/build-access-control-jobs.php');
    passthru($cmd, $buildCode);
    if ($buildCode !== 0) {
        $bad('build-access-control-jobs.php exited ' . $buildCode);
        exit(1);
    }
    accessControlLaneData(true);
    loadJsonData('__clear__');
}

if (!accessControlLaneIsNonProd()) {
    $bad('catalogue must stay non_prod=true and promote=false');
} else {
    $ok('catalogue is non-prod and not flagged for promote');
}

$jobs = accessControlLaneJobs();
$counts = ['barriers' => 0, 'maglock' => 0, 'door-entry' => 0, 'access-control' => 0];
$slugs = [];
$names = [];
$manchesterBarriers = 0;
$burnleyBarriers = 0;
$live = 0;
foreach ($jobs as $job) {
    if (!is_array($job)) {
        continue;
    }
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $family = (string)($job['family'] ?? '');
    $name = (string)($job['name'] ?? '');
    if ($slug === '' || $name === '' || $family === '') {
        $bad('row missing slug/name/family');
        continue;
    }
    $slugs[] = $slug;
    $names[$name] = ($names[$name] ?? 0) + 1;
    if (isset($counts[$family])) {
        $counts[$family]++;
    }
    if (($job['status'] ?? '') !== 'draft') {
        $live++;
    }
    if ($family === 'barriers' && str_ends_with($slug, '-manchester')) {
        $manchesterBarriers++;
    }
    if ($family === 'barriers' && str_ends_with($slug, '-burnley')) {
        $burnleyBarriers++;
    }
}

$mins = ['barriers' => 90, 'maglock' => 24, 'door-entry' => 40, 'access-control' => 40];
foreach ($mins as $family => $min) {
    if ($counts[$family] < $min) {
        $bad("{$family} count {$counts[$family]} < {$min}");
    } else {
        $ok("{$family} count {$counts[$family]}");
    }
}
if ($counts['barriers'] <= $counts['maglock'] + $counts['door-entry']) {
    $bad('barriers are not the largest family');
} else {
    $ok('barriers are the largest family');
}
if ($manchesterBarriers < 12) {
    $bad("Manchester barrier pages {$manchesterBarriers} < 12");
} else {
    $ok("Manchester barrier pages {$manchesterBarriers}");
}
if ($burnleyBarriers < 12) {
    $bad("Burnley barrier pages {$burnleyBarriers} < 12");
} else {
    $ok("Burnley barrier pages {$burnleyBarriers}");
}
if ($live > 0) {
    $bad("{$live} jobs marked live — lane status must stay draft");
} else {
    $ok('every lane job status is draft');
}
$dupNames = array_filter($names, static fn(int $n): bool => $n > 1);
if ($dupNames) {
    $bad('duplicate names: ' . implode(', ', array_slice(array_keys($dupNames), 0, 6)));
} else {
    $ok('job names unique');
}

$keywords = getMajorKeywords();
$missingKw = [];
$missingFiles = [];
foreach ($slugs as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
    }
    if (!is_file(accessControlLaneStubPath($slug))) {
        $missingFiles[] = $slug;
    }
}
if ($missingKw) {
    $bad('catalogue missing from getMajorKeywords: ' . implode(',', array_slice($missingKw, 0, 6)));
} else {
    $ok('all lane slugs overlay into getMajorKeywords()');
}
if ($missingFiles) {
    $bad('stubs missing: ' . implode(',', array_slice($missingFiles, 0, 6)));
} else {
    $ok('all lane stubs exist');
}

$requiredTowns = [
    'car-park-barrier-manchester',
    'car-park-barrier-burnley',
    'came-gard-gt4-manchester',
    'came-gard-gt4-burnley',
    'barrier-repair-manchester',
    'barrier-repair-burnley',
    'maglock-manchester',
    'maglock-burnley',
    'door-entry-manchester',
    'door-entry-burnley',
];
foreach ($requiredTowns as $slug) {
    if (!in_array($slug, $slugs, true)) {
        $bad("missing required page {$slug}");
    }
}
$ok('required Manchester and Burnley slugs present');

if (!isset($options['skip-render'])) {
    ob_start();
    renderServiceHubPage('access-control');
    $accessHtml = (string)ob_get_clean();
    ob_start();
    renderServiceHubPage('door-entry');
    $doorHtml = (string)ob_get_clean();

    foreach (['access-control' => $accessHtml, 'door-entry' => $doorHtml] as $hub => $html) {
        $need = ['id="barriers"', 'Manchester', 'Burnley', 'car-park-barrier-manchester', 'car-park-barrier-burnley', 'CAME Gard', 'Maglock', '#0B1F3A', '#ff6b00'];
        $miss = [];
        foreach ($need as $n) {
            if (!str_contains($html, $n)) {
                $miss[] = $n;
            }
        }
        if (preg_match('/£\s*\d/', $html)) {
            $miss[] = 'invented-£';
        }
        if ($miss) {
            $bad($hub . ' hub missing ' . implode('|', $miss));
        } else {
            $ok($hub . ' hub features barriers for Manchester and Burnley');
        }
    }

    $titles = [];
    $h1s = [];
    $seoFail = [];
    foreach ($slugs as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $row = $keywords[$slug] ?? [];
        $title = '';
        $h1 = '';
        if (preg_match('/<title>(.*?)<\/title>/s', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1])));
        }
        if (preg_match('/<h1\b[^>]*>(.*?)<\/h1>/s', $html, $m)) {
            $h1 = trim(html_entity_decode(strip_tags($m[1])));
        }
        if ($title !== '') {
            $titles[$title] = ($titles[$title] ?? 0) + 1;
        }
        if ($h1 !== '') {
            $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
        }
        $needles = [
            '<title>',
            '<h1',
            'name="description"',
            'rel="canonical"',
            '/pages/keywords/' . $slug,
            'id="quote"',
            'Submit request',
            '#ff6b00',
            '#0B1F3A',
            'data-lane="access-control"',
        ];
        $missing = [];
        foreach ($needles as $n) {
            if (!str_contains($html, $n)) {
                $missing[] = $n;
            }
        }
        $family = accessControlLaneFamilyOf($slug);
        if ($family === 'barriers' && !str_contains(strtolower($html), 'barrier')) {
            $missing[] = 'barrier-copy';
        }
        if (str_contains($slug, 'manchester') && !str_contains($html, 'Manchester')) {
            $missing[] = 'manchester';
        }
        if (str_contains($slug, 'burnley') && !str_contains($html, 'Burnley')) {
            $missing[] = 'burnley';
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
    if ($seoFail) {
        $bad('SEO gaps on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 5)) . ')');
    } else {
        $ok('SEO tags present on ' . count($slugs) . ' lane pages');
    }

    ob_start();
    renderKeywordAreaPage('car-park-barrier', 'Manchester');
    $mcrArea = (string)ob_get_clean();
    ob_start();
    renderKeywordAreaPage('car-park-barrier', 'Burnley');
    $burnleyArea = (string)ob_get_clean();
    if (!str_contains($mcrArea, 'Manchester') || !str_contains($mcrArea, 'data-lane="access-control"')) {
        $bad('keyword×Manchester barrier page incomplete');
    } else {
        $ok('car park barrier × Manchester renders');
    }
    if (!str_contains($burnleyArea, 'Burnley') || !str_contains($burnleyArea, 'data-lane="access-control"')) {
        $bad('keyword×Burnley barrier page incomplete');
    } else {
        $ok('car park barrier × Burnley renders');
    }
}

echo PHP_EOL . 'lane_counts=' . json_encode($counts) . " manchester_barriers={$manchesterBarriers} burnley_barriers={$burnleyBarriers}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
