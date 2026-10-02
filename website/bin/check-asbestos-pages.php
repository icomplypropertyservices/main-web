#!/usr/bin/env php
<?php
/**
 * Assert the asbestos survey/awareness lane: 36 unique hubs, POA, no removal claim.
 *
 * Usage: php website/bin/check-asbestos-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
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

$expected = asbestosJobsExpectedCount();
$data = asbestosJobsData();
$jobs = asbestosJobsJobs();
$slugs = asbestosJobsSlugs();
$unique = array_values(array_unique($slugs));
$lanes = asbestosJobsByLane();

echo 'asbestos_page_count=' . count($slugs) . " expected={$expected}\n";

if (count($slugs) !== $expected || count($unique) !== $expected) {
    $bad('asbestos_page_count ' . count($unique) . " !== {$expected}");
} else {
    $ok("asbestos_page_count == {$expected}");
}
if (($data['lanes']['survey'] ?? 0) !== count($lanes['survey']) || count($lanes['survey']) < 8) {
    $bad('survey lane count mismatch');
} else {
    $ok('survey lane count ' . count($lanes['survey']));
}
if (($data['lanes']['awareness'] ?? 0) !== count($lanes['awareness']) || count($lanes['awareness']) < 8) {
    $bad('awareness lane count mismatch');
} else {
    $ok('awareness lane count ' . count($lanes['awareness']));
}

$keywords = getMajorKeywords();
$missingFiles = [];
$missingKw = [];
$titles = [];
$h1s = [];
$metas = [];
$seoFail = [];
$copyFail = [];

foreach ($jobs as $job) {
    if (!is_array($job)) {
        $bad('non-array job row');
        continue;
    }
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $lane = (string)($job['lane'] ?? '');
    $path = asbestosJobsStubPath($slug);
    if (!is_file($path)) {
        $missingFiles[] = $slug;
    } else {
        $src = (string)file_get_contents($path);
        if (!str_contains($src, 'renderKeywordPage') || !str_contains($src, $slug)) {
            $missingFiles[] = $slug . '(bad-stub)';
        }
    }
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
        continue;
    }
    $row = $keywords[$slug];
    if (($row['service'] ?? '') !== 'asbestos-survey') {
        $copyFail[] = $slug . '(service)';
    }
    if (($row['asbestos_lane'] ?? '') !== $lane) {
        $copyFail[] = $slug . '(lane)';
    }
    $related = keywordSlug((string)($row['related'] ?? ''));
    if ($related === '' || !isset($keywords[$related])) {
        $copyFail[] = $slug . '(related)';
    }

    ob_start();
    renderKeywordPage($slug);
    $html = (string)ob_get_clean();

    if (preg_match('/<title>([^<]+)<\/title>/', $html, $m)) {
        $titles[$m[1]] = ($titles[$m[1]] ?? 0) + 1;
    } else {
        $seoFail[] = $slug . '(title)';
    }
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $html, $m)) {
        $h1 = trim(strip_tags($m[1]));
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
    } else {
        $seoFail[] = $slug . '(h1)';
    }
    if (preg_match('/name="description" content="([^"]*)"/', $html, $m)) {
        $metas[$m[1]] = ($metas[$m[1]] ?? 0) + 1;
    } else {
        $seoFail[] = $slug . '(meta)';
    }

    $needles = ['POA', 'Enquire', '#quote', 'FAQPage', '/pages/keywords/' . $slug, '#ff6b00'];
    foreach ($needles as $n) {
        if (!str_contains($html, $n)) {
            $seoFail[] = $slug . '(' . $n . ')';
        }
    }
    if (preg_match('/£\s*\d/', $html)) {
        $copyFail[] = $slug . '(invented-£)';
    }
    foreach (['UKATA', 'IATP'] as $banned) {
        if (stripos($html, $banned) !== false) {
            $copyFail[] = $slug . '(' . $banned . ')';
        }
    }
    if (preg_match('/\b(double-bag|wet the material|how to remove|drill into|scrape the)\b/i', $html)) {
        $copyFail[] = $slug . '(procedure)';
    }
    if (!preg_match('/licensed removal|removal is by others|not this service|not on this service/i', $html)) {
        $copyFail[] = $slug . '(removal-limit)';
    }
    if ($lane === 'awareness') {
        if (!str_contains($html, 'not an accredited training certificate')) {
            $copyFail[] = $slug . '(training-limit)';
        }
        if (!str_contains($html, 'does not replace')) {
            $copyFail[] = $slug . '(not-a-survey)';
        }
    }
}

if ($missingFiles) {
    $bad('missing files: ' . implode(',', array_slice($missingFiles, 0, 8)));
} else {
    $ok("all {$expected} keyword stubs exist");
}
if ($missingKw) {
    $bad('catalogue missing ' . implode(',', $missingKw));
} else {
    $ok('all slugs present in getMajorKeywords()');
}

$dup = static function (array $map): int {
    return count(array_filter($map, static fn(int $n): bool => $n > 1));
};
if ($dup($titles) > 0) {
    $bad('duplicate titles: ' . $dup($titles));
} else {
    $ok('unique title on every asbestos page');
}
if ($dup($h1s) > 0) {
    $bad('duplicate H1s: ' . $dup($h1s));
} else {
    $ok('unique H1 on every asbestos page');
}
if ($dup($metas) > 0) {
    $bad('duplicate meta descriptions: ' . $dup($metas));
} else {
    $ok('unique meta description on every asbestos page');
}
if ($seoFail) {
    $bad('SEO/POA missing on ' . count($seoFail) . ' (sample ' . implode('; ', array_slice($seoFail, 0, 6)) . ')');
} else {
    $ok('POA, Enquire, FAQ JSON-LD and canonical on every page');
}
if ($copyFail) {
    $bad('copy limits failed on ' . count($copyFail) . ' (sample ' . implode('; ', array_slice($copyFail, 0, 8)) . ')');
} else {
    $ok('removal, training and price limits hold');
}

ob_start();
require SITE_ROOT . '/pages/asbestos-jobs.php';
$hub = (string)ob_get_clean();
$hubMissing = [];
foreach (['Asbestos survey and awareness', 'Survey lane', 'Awareness lane', 'POA', 'Licensed asbestos removal is by others', '#0B1F3A', '#ff6b00'] as $n) {
    if (!str_contains($hub, $n)) {
        $hubMissing[] = $n;
    }
}
foreach ($unique as $slug) {
    if (!str_contains($hub, '/pages/keywords/' . $slug)) {
        $hubMissing[] = $slug;
    }
}
if ($hubMissing) {
    $bad('hub missing ' . implode(',', array_slice($hubMissing, 0, 8)));
} else {
    $ok('lane hub lists every survey and awareness page');
}

echo PHP_EOL . "asbestos_page_count=" . count($unique) . " expected={$expected}" . PHP_EOL;
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
