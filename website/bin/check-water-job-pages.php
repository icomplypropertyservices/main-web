#!/usr/bin/env php
<?php
/**
 * Assert the Water / WRAS / drinking water lane: 120 jobs, unique copy, stubs, render.
 *
 * Usage:
 *   php website/bin/check-water-job-pages.php
 *   php website/bin/check-water-job-pages.php --skip-render
 */
declare(strict_types=1);

$options = getopt('', ['skip-render']);
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';

if (PHP_SAPI === 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$skipRender = isset($options['skip-render']);
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

$expected = waterJobTypesExpected();
$jobs = waterJobTypesJobs();
$by = waterJobTypesBySublane();
$keywords = getMajorKeywords();

if (count($jobs) !== $expected['total']) {
    $bad('catalogue count ' . count($jobs) . ' !== ' . $expected['total']);
} else {
    $ok('catalogue has exactly 120 jobs');
}

$slugs = [];
foreach ($jobs as $job) {
    $slugs[] = keywordSlug((string)($job['slug'] ?? ''));
}
if (count(array_unique($slugs)) !== $expected['total']) {
    $bad('slugs not unique');
} else {
    $ok('all 120 slugs unique');
}

foreach (['wras' => 40, 'drinking-water' => 40, 'water-fittings' => 40] as $lane => $need) {
    $got = count($by[$lane] ?? []);
    if ($got !== $need) {
        $bad("{$lane} count {$got} !== {$need}");
    } else {
        $ok("{$lane} has exactly {$need} jobs");
    }
}

$banned = [
    '/£\s*\d/',
    '/UKAS/i',
    '/WaterSafe/i',
    '/WRAS approved/i',
    '/approved installer/i',
    '/G3 registered/i',
    '/G3 qualified/i',
];
$intros = [];
$bodies = [];
$metas = [];
$titles = [];
$h1s = [];
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $intro = (string)($job['intro'] ?? '');
    $body = (string)($job['body'] ?? '');
    $meta = (string)($job['meta_desc'] ?? '');
    $title = (string)($job['seo_title'] ?? '');
    $h1 = (string)($job['h1'] ?? '');
    $blob = $intro . ' ' . $body . ' ' . $meta . ' ' . $title . ' ' . json_encode($job['faq'] ?? []);
    if ($intro === '' || $body === '' || $meta === '' || $title === '' || $h1 === '') {
        $bad("empty copy on {$slug}");
    }
    if (!preg_match('/price on application/i', $blob)) {
        $bad("missing price on application on {$slug}");
    }
    foreach ($banned as $pat) {
        if (preg_match($pat, $blob)) {
            $bad("banned pattern {$pat} on {$slug}");
        }
    }
    if (($job['service'] ?? '') !== 'water-wras') {
        $bad("service is not water-wras on {$slug}");
    }
    if (!isset($keywords[$slug])) {
        $bad("getMajorKeywords missing {$slug}");
    } elseif (($keywords[$slug]['service'] ?? '') !== 'water-wras') {
        $bad("overlay service mismatch {$slug}");
    }
    $intros[$intro] = true;
    $bodies[$body] = true;
    $metas[$meta] = true;
    $titles[$title] = true;
    $h1s[$h1] = true;

    $path = waterJobTypesStubDir() . '/' . $slug . '.php';
    if (!is_file($path)) {
        $bad("missing stub {$slug}");
        continue;
    }
    $src = (string)file_get_contents($path);
    if (!str_contains($src, 'renderKeywordPage') || !str_contains($src, $slug)) {
        $bad("stub contents invalid {$slug}");
    }
}
if (count($intros) === 120 && count($bodies) === 120 && count($metas) === 120 && count($titles) === 120 && count($h1s) === 120) {
    $ok('intros, bodies, metas, titles and h1s are unique');
} else {
    $bad('copy uniqueness intro=' . count($intros) . ' body=' . count($bodies) . ' meta=' . count($metas) . ' title=' . count($titles) . ' h1=' . count($h1s));
}
$ok('stub and overlay checks finished');

$rawKeywords = json_decode((string)file_get_contents(SITE_ROOT . '/data/keywords.json'), true);
$rawSlugs = [];
if (is_array($rawKeywords)) {
    foreach (array_keys($rawKeywords) as $k) {
        $rawSlugs[keywordSlug((string)$k)] = true;
    }
}
$clashes = array_values(array_filter($slugs, static fn(string $s): bool => isset($rawSlugs[$s])));
if ($clashes) {
    $bad('clashes with keywords.json: ' . implode(',', array_slice($clashes, 0, 8)));
} else {
    $ok('no clash with existing keywords.json slugs');
}

if (!is_file(SITE_ROOT . '/pages/water-wras.php') || !is_file(SITE_ROOT . '/pages/services/water-wras.php')) {
    $bad('lane hub or service hub file missing');
} else {
    $ok('lane hub and service hub files exist');
}

if (!$skipRender) {
    putenv('ICOMPLY_STATIC_EXPORT=1');
    $_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
    $samples = [
        'wras-fittings-advice',
        'regulation-5-water-notification',
        'drinking-water-point-installation',
        'legionella-versus-drinking-water',
        'internal-stopcock-water-regs',
        'water-regs-log-for-landlords',
    ];
    foreach ($samples as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        if (!str_contains($html, '<h1') || !str_contains($html, (string)($keywords[$slug]['h1'] ?? $keywords[$slug]['name']))) {
            $bad("render missing h1 for {$slug}");
            continue;
        }
        if (!str_contains($html, 'Price on application')) {
            $bad("render missing POA for {$slug}");
            continue;
        }
        if (preg_match('/£\s*\d/', $html)) {
            $bad("render contains a £ price for {$slug}");
            continue;
        }
        if (stripos($html, 'WRAS approved') !== false || stripos($html, 'UKAS') !== false) {
            $bad("render contains a banned claim for {$slug}");
            continue;
        }
        $ok("rendered {$slug}");
    }

    ob_start();
    include SITE_ROOT . '/pages/water-wras.php';
    $hub = (string)ob_get_clean();
    if (!str_contains($hub, 'Water, WRAS and drinking water') || substr_count($hub, '/pages/keywords/') < 120) {
        $bad('lane hub did not list all jobs');
    } else {
        $ok('lane hub lists all 120 jobs');
    }
}

echo ($fail === 0 ? "PASS" : "FAIL") . " pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
