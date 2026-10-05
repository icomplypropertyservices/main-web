#!/usr/bin/env php
<?php
/**
 * W1a P0 job hubs: 800 words, FAQs, 3 images, full meta, one canonical URL.
 *
 * Usage: php website/bin/check-nationwide-p0-jobs.php
 */
declare(strict_types=1);

putenv('SITE_URL=https://icomplypropertyservices.co.uk');
$_ENV['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/nationwide-p0-jobs.php';
require_once SITE_ROOT . '/includes/sitemap.php';

$fail = 0;
$pass = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        echo "[PASS] {$msg}\n";
        return;
    }
    $fail++;
    echo "[FAIL] {$msg}\n";
};

$rows = nationwideP0Rows();
$jobs = nationwideP0Jobs();
$ok(count($rows) === 90, 'P0 rows=' . count($rows));

$hubs = 0;
$redirects = 0;
$firsts = [];
$redirectLines = nationwideP0RedirectLines();

foreach ($rows as $slug => $row) {
    $target = nationwideP0KeywordRedirect($slug);
    $file = SITE_ROOT . '/pages/jobs/' . $slug . '.php';
    if ($target !== null) {
        $redirects++;
        $ok(!is_file($file), $slug . ' has no duplicate job body');
        $ok(!isset($jobs[$slug]), $slug . ' has no job copy');
        $reserved = isset(nationwideP0KeywordCanonicalSlugs()[$slug]);
        $ok($reserved || isset(getMajorKeywords()[$slug]), $slug . ' keyword hub exists');
        $ok(str_contains($redirectLines, '/pages/jobs/' . $slug . ' '), $slug . ' redirect is emitted');
        continue;
    }
    $hubs++;
    $ok(is_file($file), $slug . ' job stub exists');
    $ok(isset($jobs[$slug]) && is_array($jobs[$slug]), $slug . ' copy exists');
}

$ok($hubs + $redirects === 90, "hubs={$hubs} keyword_canonical={$redirects}");

$render = static function (string $path): string {
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    ob_start();
    nationwideP0Render(basename($path));
    return (string)ob_get_clean();
};

$banned = [
    '£',
    'BAFE',
    'NSI',
    'FIRAS',
    'LPCB',
    'IFC',
    'Gas Safe',
    'authorised dealer',
    'authorized dealer',
    'approved subcontractor',
];

foreach ($jobs as $slug => $job) {
    if (!is_array($job)) {
        $ok(false, $slug . ' record');
        continue;
    }
    $html = $render('/pages/jobs/' . $slug);
    $ok(!str_contains($html, 'Not found') && !str_contains($html, 'Keyword not found'), $slug . ' renders');
    if (!preg_match('/<section[^>]*id="job-body"[^>]*>(.*?)<\/section>/s', $html, $bodyMatch)) {
        $ok(false, $slug . ' has job body');
        continue;
    }
    $bodyText = trim(preg_replace('/\s+/', ' ', strip_tags($bodyMatch[1])) ?? '');
    $words = preg_match_all("/[A-Za-z0-9+’']+/", $bodyText);
    $ok($words >= 800, $slug . " words={$words}");
    if (preg_match('/<p[^>]*>(.*?)<\/p>/s', $bodyMatch[1], $first)) {
        $intro = trim(preg_replace('/\s+/', ' ', strip_tags($first[1])) ?? '');
        $ok($intro !== '' && !isset($firsts[$intro]), $slug . ' unique intro');
        $firsts[$intro] = $slug;
    } else {
        $ok(false, $slug . ' intro paragraph');
    }
    $ok(substr_count($html, 'FAQPage') >= 1, $slug . ' FAQ schema');
    $ok(substr_count(strtolower($html), '<details') >= 3, $slug . ' FAQ details');
    $images = $job['images'] ?? [];
    $ok(is_array($images) && count($images) >= 3, $slug . ' image records');
    $seenSrc = [];
    foreach ((array)$images as $image) {
        if (!is_array($image) || !isset($image[0])) {
            continue;
        }
        $src = (string)$image[0];
        $seenSrc[$src] = true;
        $ok(str_contains($html, $src), $slug . ' shows ' . $src);
    }
    $ok(count($seenSrc) >= 3, $slug . ' three distinct images');
    $title = (string)($job['title'] ?? '');
    $ok(str_contains($html, htmlspecialchars($title, ENT_QUOTES, 'UTF-8')), $slug . ' title');
    $ok(str_ends_with($title, '| iComply Property Services') && !str_contains($title, '…'), $slug . ' title pattern');
    $meta = (string)($job['meta'] ?? '');
    $metaLen = function_exists('mb_strlen') ? mb_strlen($meta) : strlen($meta);
    $ok($metaLen >= 140 && $metaLen <= 160, $slug . " meta length={$metaLen}");
    $ok(str_contains($meta, 'Request a quote — POA.'), $slug . ' meta CTA');
    $ok(str_contains($html, 'property="og:title"'), $slug . ' og:title');
    $ok(str_contains($html, 'property="og:description"'), $slug . ' og:description');
    $ok(str_contains($html, 'property="og:image"'), $slug . ' og:image');
    $canonical = 'https://icomplypropertyservices.co.uk/pages/jobs/' . $slug;
    $ok(str_contains($html, 'rel="canonical" href="' . $canonical . '"'), $slug . ' canonical');
    $ok(str_contains($html, 'property="og:url" content="' . $canonical . '"'), $slug . ' og:url');
    $ok(str_contains($html, 'Cheshire SK2 5DE'), $slug . ' NAP');
    $ok(str_contains($html, '07517806082'), $slug . ' phone');
    $ok(stripos($html, 'price on application') !== false, $slug . ' POA');
    $copy = strtolower($bodyText . ' ' . $title . ' ' . $meta);
    foreach ($banned as $needle) {
        if (in_array($needle, ['NSI', 'IFC', 'BAFE', 'FIRAS', 'LPCB'], true)) {
            $ok(!preg_match('/\b' . preg_quote($needle, '/') . '\b/i', $bodyText . ' ' . $title . ' ' . $meta), $slug . ' no ' . $needle);
            continue;
        }
        if ($needle === 'Gas Safe') {
            $ok(!preg_match('/\bGas Safe\b/i', $bodyText . ' ' . $title . ' ' . $meta), $slug . ' no Gas Safe');
            continue;
        }
        $ok(!str_contains($copy, strtolower($needle)), $slug . ' no ' . $needle);
    }
    $policy = (string)($job['policy'] ?? '');
    if ($policy === 'came_new' || $policy === 'came_service') {
        $ok(stripos($html, 'CAME partner') !== false, $slug . ' CAME partner wording');
    }
    if ($policy === 'incumbent') {
        $ok(stripos($html, 'existing') !== false, $slug . ' existing-kit wording');
        $ok(stripos($html, 'do not offer a new') !== false, $slug . ' no new other-brand install');
    }
}

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$committed = is_file(SITE_ROOT . '/sitemap.xml') ? (string)file_get_contents(SITE_ROOT . '/sitemap.xml') : '';
foreach (array_keys($jobs) as $slug) {
    if (nationwideP0KeywordRedirect($slug) !== null) {
        continue;
    }
    $ok(str_contains($xml, '/pages/jobs/' . $slug . '</loc>'), 'sitemap lists /pages/jobs/' . $slug);
    $ok(str_contains($committed, '/pages/jobs/' . $slug . '</loc>'), 'committed sitemap lists /pages/jobs/' . $slug);
}
foreach ($rows as $slug => $_row) {
    if (nationwideP0KeywordRedirect($slug) === null) {
        continue;
    }
    $ok(!str_contains($xml, '/pages/jobs/' . $slug . '</loc>'), 'sitemap omits redirected /pages/jobs/' . $slug);
    $ok(!str_contains($committed, '/pages/jobs/' . $slug . '</loc>'), 'committed sitemap omits redirected /pages/jobs/' . $slug);
    if (isset(getMajorKeywords()[$slug])) {
        $ok(str_contains($xml, '/pages/keywords/' . $slug . '</loc>'), 'sitemap keeps keyword canonical /pages/keywords/' . $slug);
    }
}

echo ($fail === 0 ? "PASS ({$pass})\n" : "FAIL ({$fail}) pass={$pass}\n");
exit($fail === 0 ? 0 : 1);
