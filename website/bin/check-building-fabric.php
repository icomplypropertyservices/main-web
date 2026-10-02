#!/usr/bin/env php
<?php
/**
 * BUILDING fabric / repairs lane — copy quality and a render spot-check.
 *
 * Usage:
 *   php website/bin/check-building-fabric.php
 *   php website/bin/check-building-fabric.php --render-all
 */
declare(strict_types=1);

$options = getopt('', ['render-all']);
$renderAll = isset($options['render-all']);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
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

$laneServices = [
    'brickwork' => true,
    'roofing' => true,
    'rendering' => true,
    'damp-proofing' => true,
    'plastering' => true,
    'dry-lining' => true,
    'insulation' => true,
    'windows-doors' => true,
    'building-maintenance' => true,
    'building-surveys' => true,
    'joinery' => true,
    'carpentry' => true,
];

$packFile = SITE_ROOT . '/data/job-packs/building-fabric.json';
$pack = json_decode((string)file_get_contents($packFile), true);
$jobs = is_array($pack['jobs'] ?? null) ? $pack['jobs'] : [];
$bySlug = [];
foreach ($jobs as $job) {
    if (!is_array($job)) {
        $bad('pack row not an object');
        continue;
    }
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '' || isset($bySlug[$slug])) {
        $bad('bad or duplicate pack slug ' . $slug);
        continue;
    }
    $bySlug[$slug] = $job;
}

$masterLane = [];
foreach (jobTypesMasterJobs() as $row) {
    $service = (string)($row['service'] ?? '');
    if (!isset($laneServices[$service])) {
        continue;
    }
    $slug = keywordSlug((string)($row['slug'] ?? ''));
    $masterLane[$slug] = $service;
}

$missing = array_diff(array_keys($masterLane), array_keys($bySlug));
$extra = array_diff(array_keys($bySlug), array_keys($masterLane));
if ($missing === [] && $extra === []) {
    $ok('pack covers every fabric/repairs master slug (' . count($masterLane) . ')');
} else {
    $bad('coverage missing=' . count($missing) . ' extra=' . count($extra));
}

$expected = 207;
if (count($bySlug) === $expected) {
    $ok("building_fabric_count=={$expected}");
} else {
    $bad('building_fabric_count==' . $expected . ' got ' . count($bySlug));
}

$banned = ['sits under our', 'not a generic package', 'typical north west stock includes'];
$copyFail = [];
$titles = [];
$h1s = [];
foreach ($bySlug as $slug => $job) {
    $blob = (string)($job['intro'] ?? '') . ' ' . (string)($job['body'] ?? '') . ' ' . (string)($job['meta_desc'] ?? '')
        . ' ' . json_encode($job['faq'] ?? []);
    $low = strtolower($blob);
    if (preg_match('/£\s*\d/', $blob)) {
        $copyFail[] = $slug . ':£';
    }
    if (!str_contains($blob, 'POA') && !str_contains($low, 'price on application')) {
        $copyFail[] = $slug . ':POA';
    }
    if (!str_contains($low, 'icomply')) {
        $copyFail[] = $slug . ':brand';
    }
    foreach ($banned as $phrase) {
        if (str_contains($low, $phrase)) {
            $copyFail[] = $slug . ':' . $phrase;
        }
    }
    if (strlen((string)($job['intro'] ?? '')) < 100 || strlen((string)($job['body'] ?? '')) < 240) {
        $copyFail[] = $slug . ':short';
    }
    $title = (string)($job['seo_title'] ?? '');
    $h1 = (string)($job['h1'] ?? '');
    $titles[$title] = ($titles[$title] ?? 0) + 1;
    $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
    $service = (string)($job['service'] ?? '');
    if (!isset($laneServices[$service]) || $service !== ($masterLane[$slug] ?? '')) {
        $copyFail[] = $slug . ':service';
    }
}
if ($copyFail === []) {
    $ok('lane copy is specific, branded, POA, and has no catalogue £');
} else {
    $bad('copy issues ' . count($copyFail) . ' sample=' . implode(',', array_slice($copyFail, 0, 6)));
}
$dupT = array_filter($titles, static fn(int $n): bool => $n > 1);
$dupH = array_filter($h1s, static fn(int $n): bool => $n > 1);
if ($dupT === [] && $dupH === []) {
    $ok('lane titles and H1s unique');
} else {
    $bad('duplicate title/h1 in lane');
}

$keywords = getMajorKeywords();
$overlayFail = [];
foreach ($bySlug as $slug => $job) {
    $live = $keywords[$slug] ?? null;
    if (!is_array($live)) {
        $overlayFail[] = $slug . ':missing';
        continue;
    }
    if ((string)($live['intro'] ?? '') !== (string)$job['intro']) {
        $overlayFail[] = $slug . ':intro';
    }
    if ((string)($live['seo_title'] ?? '') !== (string)$job['seo_title']) {
        $overlayFail[] = $slug . ':title';
    }
    if ((string)($live['h1'] ?? '') !== (string)$job['h1']) {
        $overlayFail[] = $slug . ':h1';
    }
    if ((string)($live['body'] ?? '') !== (string)$job['body']) {
        $overlayFail[] = $slug . ':body';
    }
    if (str_contains(strtolower((string)($live['intro'] ?? '')), 'sits under our')) {
        $overlayFail[] = $slug . ':template-intro';
    }
}
if ($overlayFail === []) {
    $ok('getMajorKeywords() is serving the fabric pack, not the template paragraph');
} else {
    $bad('overlay not live sample=' . implode(',', array_slice($overlayFail, 0, 6)));
}

$renderSlugs = $renderAll
    ? array_keys($bySlug)
    : ['fabric-repairs-package', 'roof-leak-repair', 'chemical-dpc-injection', 'fire-door-joinery-support', 'loft-insulation', 'masonry-crack-repair'];
$renderFail = [];
foreach ($renderSlugs as $slug) {
    if (!isset($bySlug[$slug])) {
        $renderFail[] = $slug . ':not-in-lane';
        continue;
    }
    ob_start();
    renderKeywordPage($slug);
    $html = (string)ob_get_clean();
    $introNeedle = htmlspecialchars((string)$bySlug[$slug]['intro'], ENT_QUOTES, 'UTF-8');
    foreach (['<h1', 'FAQPage', 'rel="canonical"', '#quote', 'POA', $introNeedle] as $needle) {
        if (!str_contains($html, $needle)) {
            $renderFail[] = $slug . ':missing-' . substr($needle, 0, 24);
        }
    }
    if (preg_match('/£\s*\d/', $html)) {
        $renderFail[] = $slug . ':£';
    }
    if (str_contains($html, 'sits under our')) {
        $renderFail[] = $slug . ':template';
    }
}
if ($renderFail === []) {
    $ok('rendered ' . count($renderSlugs) . ' fabric pages with FAQ, canonical, POA and the pack intro');
} else {
    $bad('render sample=' . implode(',', array_slice($renderFail, 0, 6)));
}

echo PHP_EOL . 'building_fabric_count=' . count($bySlug) . PHP_EOL;
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
