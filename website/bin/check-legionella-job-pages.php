#!/usr/bin/env php
<?php
/**
 * Legionella / water hygiene job lane checks.
 * Usage (repo root): php website/bin/check-legionella-job-pages.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once SITE_ROOT . '/includes/render.php';

$fail = 0;
$ok = static function (string $msg) : void {
    fwrite(STDOUT, "OK  {$msg}\n");
};
$bad = static function (string $msg) use (&$fail): void {
    fwrite(STDERR, "FAIL {$msg}\n");
    $fail++;
};

$expected = legionellaJobTypesExpectedCount();
$jobs = legionellaJobTypesJobs();
if (count($jobs) !== $expected) {
    $bad('job count ' . count($jobs) . " expected {$expected}");
} else {
    $ok("count={$expected}");
}

$slugs = [];
$banned = ['ukas', '£', 'fixed-price', 'fixed price', 'disease-free', 'niceic', 'guaranteed legionella'];
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '' || isset($slugs[$slug])) {
        $bad("bad or duplicate slug {$slug}");
        continue;
    }
    $slugs[$slug] = true;
    if (($job['service'] ?? '') !== 'legionella-risk-assessment') {
        $bad("{$slug} service is not legionella-risk-assessment");
    }
    $blob = strtolower(json_encode($job) ?: '');
    foreach ($banned as $word) {
        if (str_contains($blob, $word)) {
            $bad("{$slug} contains {$word}");
        }
    }
    if (!str_contains(strtolower((string)($job['intro'] ?? '')), 'price on application')) {
        $bad("{$slug} intro missing price on application");
    }
    $stub = SITE_ROOT . '/pages/keywords/' . $slug . '.php';
    if (is_file($stub)) {
        $src = (string)file_get_contents($stub);
        if (!str_contains($src, 'renderKeywordPage(')) {
            $bad("{$slug} stub does not call renderKeywordPage");
        }
    }
}
if (count($slugs) === $expected) {
    $ok('slugs unique; router serves them from the catalogue');
}

foreach ($jobs as $job) {
    $related = keywordSlug((string)($job['related'] ?? ''));
    if ($related === '' || !isset($slugs[$related])) {
        $bad(($job['slug'] ?? '') . " related {$related} not in lane");
    }
}

$catalog = getMajorKeywords();
$preserved = $catalog['legionella-risk-assessment']['intro'] ?? '';
if (!str_contains($preserved, 'A Legionella risk assessment looks at how stored and circulated water')) {
    $bad('existing Legionella risk assessment intro was overwritten');
} else {
    $ok('existing assessment intro kept');
}

$sample = $catalog['calorifier-temperature-check'] ?? null;
if (!is_array($sample) || ($sample['service'] ?? '') !== 'legionella-risk-assessment') {
    $bad('calorifier job missing from keyword catalogue');
} elseif (!str_contains((string)($sample['intro'] ?? ''), 'Calorifier Temperature Check')) {
    $bad('calorifier intro missing job name');
} else {
    $ok('new job merged into keyword catalogue');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['HTTPS'] = 'on';

$renderSlug = static function (string $slug) use ($bad, $ok): void {
    ob_start();
    renderKeywordPage($slug);
    $html = (string)ob_get_clean();
    if (!str_contains($html, '<!DOCTYPE') && !str_contains($html, '<html')) {
        $bad("{$slug} did not render HTML");
        return;
    }
    if (str_contains($html, '<?php')) {
        $bad("{$slug} leaked PHP");
        return;
    }
    if (!str_contains(strtolower($html), 'price on application') && !str_contains($html, 'POA')) {
        $bad("{$slug} HTML missing POA");
        return;
    }
    if (str_contains($html, 'Fixed-price')) {
        $bad("{$slug} HTML still says Fixed-price");
        return;
    }
    $ok("rendered {$slug}");
};

$renderSlug('calorifier-temperature-check');
$renderSlug('hmo-water-hygiene-visit');
$renderSlug('legionella-risk-assessment');

ob_start();
include SITE_ROOT . '/pages/resources/legionella-jobs.php';
$index = (string)ob_get_clean();
if (!str_contains($index, 'job lane') || !str_contains($index, 'Calorifier Temperature Check')) {
    $bad('job lane index missing heading or a job');
} else {
    $ok('job lane index renders');
}

if (substr_count($index, 'pages/keywords/') < 100) {
    $bad('job lane index has too few job links');
} else {
    $ok('job lane index lists the jobs');
}

if ($fail > 0) {
    fwrite(STDERR, "legionella lane check FAILED ({$fail})\n");
    exit(1);
}
fwrite(STDOUT, "legionella lane check OK\n");
exit(0);
