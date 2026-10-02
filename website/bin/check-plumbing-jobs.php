#!/usr/bin/env php
<?php
/**
 * Plumbing lane only: 130 job-type keyword pages, unique copy, hub links.
 *
 * Usage: php website/bin/check-plumbing-jobs.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';

if (PHP_SAPI === 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
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

$expected = jobTypesPlumbingExpected();
$jobs = jobTypesPlumbingJobs();
$slugs = [];
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug !== '') {
        $slugs[] = $slug;
    }
}
$unique = array_values(array_unique($slugs));

if (count($jobs) !== $expected || count($unique) !== $expected) {
    $bad('catalogue ' . count($jobs) . ' unique ' . count($unique) . " !== {$expected}");
} else {
    $ok("catalogue has exactly {$expected} unique plumbing jobs");
}

$keywords = getMajorKeywords();
$missingKw = [];
$badFamily = [];
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
        continue;
    }
    if (($keywords[$slug]['service'] ?? '') !== 'plumbing' || empty($keywords[$slug]['plumbing_lane'])) {
        $badFamily[] = $slug;
    }
    if (($job['family'] ?? '') !== 'plumbing' || ($job['service'] ?? '') !== 'plumbing') {
        $badFamily[] = $slug;
    }
}
if ($missingKw) {
    $bad('getMajorKeywords missing ' . count($missingKw));
} else {
    $ok('all plumbing slugs are in getMajorKeywords()');
}
if ($badFamily) {
    $bad('family/service not plumbing on ' . count(array_unique($badFamily)));
} else {
    $ok('every row is family=plumbing service=plumbing');
}

$titles = [];
$h1s = [];
$metas = [];
$intros = [];
$bodies = [];
$notes = [];
foreach ($jobs as $job) {
    $slug = (string)($job['slug'] ?? '?');
    foreach (['seo_title', 'h1', 'meta_desc', 'intro', 'body', 'note'] as $field) {
        if (trim((string)($job[$field] ?? '')) === '') {
            $bad("empty {$field} on {$slug}");
        }
    }
    if (strlen((string)($job['note'] ?? '')) < 120) {
        $bad("short note on {$slug}");
    }
    $metaDesc = trim((string)($job['meta_desc'] ?? ''));
    if (strlen($metaDesc) > 160 || !str_ends_with($metaDesc, '.')) {
        $bad("meta not a finished sentence on {$slug}");
    }
    $blob = (string)($job['intro'] ?? '') . (string)($job['body'] ?? '') . (string)($job['note'] ?? '');
    if (preg_match('/£\s*\d/', $blob)) {
        $bad("invented £ on {$slug}");
    }
    if (!preg_match('/price on application/i', (string)($job['intro'] ?? '') . (string)($job['body'] ?? ''))) {
        $bad("missing price on application on {$slug}");
    }
    $related = keywordSlug((string)($job['related'] ?? ''));
    if ($related === '' || $related === keywordSlug($slug) || !isset($keywords[$related])) {
        $bad("related hub missing on {$slug}");
    }
    $faq = $job['faq'] ?? [];
    if (!is_array($faq) || count($faq) < 3) {
        $bad("FAQ short on {$slug}");
    }
    $titles[] = (string)($job['seo_title'] ?? '');
    $h1s[] = (string)($job['h1'] ?? '');
    $metas[] = (string)($job['meta_desc'] ?? '');
    $intros[] = (string)($job['intro'] ?? '');
    $bodies[] = (string)($job['body'] ?? '');
    $notes[] = (string)($job['note'] ?? '');
}
foreach (['titles' => $titles, 'h1s' => $h1s, 'metas' => $metas, 'intros' => $intros, 'bodies' => $bodies, 'notes' => $notes] as $label => $vals) {
    if (count($vals) === $expected && count(array_unique($vals)) === $expected) {
        $ok("unique {$label}");
    } else {
        $bad("duplicate {$label} unique=" . count(array_unique($vals)));
    }
}

$stubDir = jobTypesPlumbingStubDir();
$presentStubs = 0;
foreach ($unique as $slug) {
    $path = $stubDir . '/' . $slug . '.php';
    if (!is_file($path)) {
        continue;
    }
    $presentStubs++;
    $src = (string)file_get_contents($path);
    if (!str_contains($src, 'renderKeywordPage') || !str_contains($src, $slug)) {
        $bad("bad stub {$slug}");
    }
}
if ($presentStubs === 0) {
    $ok('routes are virtual (keyword stubs stay gitignored; catalogue overlay serves them)');
} elseif ($presentStubs === $expected) {
    $ok('optional keyword stubs are present and call renderKeywordPage');
} else {
    $bad("partial stubs {$presentStubs}/{$expected}");
}
$missingFiles = [];

$renderedFail = [];
if (!$missingKw && !$missingFiles) {
    foreach ($unique as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $needles = [
            'data-plumbing-hubs="1"',
            '/pages/services/plumbing',
            '/pages/areas',
            '/pages/keywords/' . $slug,
            '#construction',
            'FAQPage',
            'Price on application',
            '#0B1F3A',
            '#ff6b00',
            'Submit request',
        ];
        $missing = [];
        foreach ($needles as $n) {
            if (!str_contains($html, $n)) {
                $missing[] = $n;
            }
        }
        $related = keywordSlug((string)($keywords[$slug]['related'] ?? ''));
        if ($related !== '' && !str_contains($html, '/pages/keywords/' . $related)) {
            $missing[] = 'related';
        }
        if (preg_match('/£\s*\d/', $html)) {
            $missing[] = 'invented-£';
        }
        if ($missing) {
            $renderedFail[] = $slug . '(' . implode('|', $missing) . ')';
        }
    }
}
if ($renderedFail) {
    $bad('page hub/quality miss ' . count($renderedFail) . ' sample ' . implode('; ', array_slice($renderedFail, 0, 4)));
} else {
    $ok('130 pages render hub links, FAQ schema, POA and brand');
}

ob_start();
renderKeywordAreaPage('burst-pipe-repair', 'Stockport');
$townHtml = (string)ob_get_clean();
if (!str_contains($townHtml, 'data-plumbing-hubs="1"') || !str_contains($townHtml, '/pages/services/plumbing') || !str_contains($townHtml, '/pages/areas')) {
    $bad('Stockport town page missing plumbing hub links');
} else {
    $ok('sample town page links the plumbing and area hubs');
}

ob_start();
renderServiceHubPage('plumbing');
$hub = (string)ob_get_clean();
$hubMiss = [];
if (!str_contains($hub, 'id="plumbing-job-types"') && !str_contains($hub, "id='plumbing-job-types'")) {
    $hubMiss[] = 'anchor';
}
foreach ($unique as $slug) {
    if (!str_contains($hub, '/pages/keywords/' . $slug)) {
        $hubMiss[] = $slug;
    }
}
if ($hubMiss) {
    $bad('plumbing service hub missing ' . count($hubMiss) . ' sample ' . implode(',', array_slice($hubMiss, 0, 6)));
} else {
    $ok('plumbing service hub links every job type');
}

ob_start();
include SITE_ROOT . '/pages/services/index.php';
$servicesIndex = (string)ob_get_clean();
if (!str_contains($servicesIndex, '/pages/services/plumbing') || !str_contains($servicesIndex, 'plumbing-job-types') || !str_contains($servicesIndex, '/pages/keywords/emergency-plumber')) {
    $bad('services index missing plumbing hub links');
} else {
    $ok('services index links the plumbing hub and job types');
}

echo PHP_EOL . 'plumbing=' . count($jobs) . ' rendered=' . count($unique) . PHP_EOL;
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
