#!/usr/bin/env php
<?php
/**
 * Building services P0 on the dual 269 ring: hubs, path counts, sample HTML.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sitemap.php';

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

$slugs = icomplyBuildingDualSlugs();
$towns = icomplyBuildingDualTownMap();
$ok(count($slugs) === 100, 'P0 slugs=' . count($slugs));
$ok(count($towns) === 269, 'dual towns=' . count($towns));
$gm = 0;
foreach ($towns as $town) {
    if (!empty($town['gm'])) {
        $gm++;
    }
}
$ok($gm === 60, 'gm towns inside the ring=' . $gm);

$gmPlaces = [];
if (function_exists('icomplyGreaterManchesterTownNames')) {
    foreach (icomplyGreaterManchesterTownNames() as $name) {
        $gmPlaces[areaSlug((string)$name)] = true;
    }
}
$ok(count($gmPlaces) === 60, 'GM place keys=' . count($gmPlaces));
$extra = icomplyBuildingDualExtraKeywordTownPaths($gmPlaces);
$jobs = icomplyBuildingDualJobTownPaths();
$ok(count($extra) === 100 * (269 - 60), 'extra keyword×town=' . count($extra));
$ok(count($jobs) === 100 * 269, 'job×town=' . count($jobs));
$ok(in_array('/pages/keywords/plasterers/york', $extra, true), 'plasterers/york is an extra keyword path');
$ok(!in_array('/pages/keywords/plasterers/stockport', $extra, true), 'stockport keyword path stays in the GM counter');
$ok(in_array('/pages/jobs/cp12/burnley', $jobs, true), 'cp12/burnley is a job path');
$ok(in_array('/pages/jobs/fire-door-installer/manchester', $jobs, true), 'fire-door job reaches Manchester');

$capture = static function (callable $render): string {
    ob_start();
    $render();
    return (string)ob_get_clean();
};

$samples = [
    ['keyword hub plasterers', $capture(static function (): void {
        icomplyBuildingDualRenderHub('keyword', 'plasterers');
    }), false],
    ['job hub cp12', $capture(static function (): void {
        icomplyBuildingDualRenderHub('job', 'cp12');
    }), true],
    ['keyword york plasterers', $capture(static function (): void {
        icomplyBuildingDualRenderTown('keyword', 'plasterers', 'york');
    }), false],
    ['job burnley cp12', $capture(static function (): void {
        icomplyBuildingDualRenderTown('job', 'cp12', 'burnley');
    }), true],
    ['keyword stockport garden walls', $capture(static function (): void {
        icomplyBuildingDualRenderTown('keyword', 'garden-wall-building', 'stockport');
    }), false],
    ['job manchester dryliners', $capture(static function (): void {
        icomplyBuildingDualRenderTown('job', 'dryliners', 'manchester');
    }), false],
];

foreach ($samples as [$label, $html, $gas]) {
    $ok(str_contains($html, '<!DOCTYPE') || str_contains($html, '<html'), $label . ' renders');
    if (preg_match('#<article\b[^>]*>(.*)</article>#is', $html, $article)) {
        $prose = trim(preg_replace('/\s+/', ' ', strip_tags($article[1])) ?? '');
    } else {
        $prose = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
    }
    $words = $prose === '' ? 0 : count(preg_split('/\s+/', $prose) ?: []);
    $ok($words >= 800, $label . ' words=' . $words);
    $ok(preg_match_all('#<img\b#i', $html) >= 3, $label . ' has 3 images');
    $ok(str_contains($html, 'data-seo-faq="1"') && str_contains($html, '<details'), $label . ' has FAQ');
    $ok(substr_count(strtolower($html), '<h1') === 1, $label . ' one h1');
    $ok(str_contains($html, 'rel="canonical"'), $label . ' canonical');
    $ok(str_contains($html, 'property="og:title"') && str_contains($html, 'property="og:description"') && str_contains($html, 'property="og:url"') && str_contains($html, 'property="og:image"'), $label . ' OG');
    $ok((bool)preg_match('/price on application|\bPOA\b/i', $html), $label . ' POA');
    $articleHtml = $html;
    if (preg_match('#<article\b[^>]*>.*</article>#is', $html, $articleTag)) {
        $articleHtml = $articleTag[0];
    }
    $ok(str_contains($articleHtml, '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE'), $label . ' NAP');
    $ok(!str_contains($articleHtml, '£'), $label . ' no price');
    $ok(!str_contains($html, 'approved subcontractors'), $label . ' no approved subcontractors');
    $ok(!preg_match('/\bb\d{5}\b/', $html), $label . ' no bNNNNN');
    $ok(!preg_match('/\bBAFE\b(?! or NSI)/', $articleHtml) || str_contains($articleHtml, 'does not claim BAFE or NSI'), $label . ' no fake BAFE');
    if ($gas) {
        $ok(str_contains($articleHtml, 'The visit is carried out by Gas Safe registered engineers.'), $label . ' gas duty');
        $ok(str_contains($articleHtml, 'iComply does not claim a Gas Safe registration.'), $label . ' gas denial');
        $ok(!preg_match('/iComply is Gas Safe registered\./', str_replace('iComply is not Gas Safe registered.', '', $articleHtml)), $label . ' no positive Gas Safe claim');
    } else {
        $ok(!str_contains($articleHtml, 'Gas Safe registered'), $label . ' no Gas Safe line');
    }
    if (preg_match('#<title>([^<]*)</title>#', $html, $title)) {
        $len = mb_strlen(html_entity_decode($title[1]));
        $ok($len >= 30 && $len <= 65, $label . ' title length=' . $len);
    } else {
        $ok(false, $label . ' title');
    }
    if (preg_match('#<meta name="description" content="([^"]*)"#', $html, $meta)) {
        $len = mb_strlen(html_entity_decode($meta[1]));
        $ok($len >= 140 && $len <= 160, $label . ' meta length=' . $len);
    } else {
        $ok(false, $label . ' meta');
    }
}

$ok(icomplyPathIsIndexable('/pages/keywords/plasterers/burnley'), 'plasterers/burnley indexable');
$ok(icomplyPathIsIndexable('/pages/keywords/plasterers/york'), 'plasterers/york indexable');
$ok(icomplyPathIsIndexable('/pages/jobs/cp12/york'), 'cp12/york indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/eicr/stockport'), 'eicr/stockport stays noindex');
$ok(icomplyNonGmMatrixRedirect('/pages/jobs/cp12/york') === null, 'cp12/york stays');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/plasterers/burnley') === null, 'plasterers/burnley stays');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/plasterers/aberdeen') === '/pages/keywords/plasterers', 'aberdeen redirects to the hub');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/rewire/liverpool') === '/pages/keywords/rewire', 'rewire Liverpool still redirects');
$ok(icomplyNonGmMatrixRedirect('/pages/jobs/eicr/burnley') === '/pages/jobs/eicr', 'eicr Burnley still redirects');
$ok(icomplyNonGmMatrixRedirect('/pages/areas/burnley') === '/pages/areas', 'Burnley area hub still redirects');

$paths = [];
foreach (icomplySitemapEntries() as $entry) {
    $paths[(string)($entry['path'] ?? '')] = true;
}
$ok(isset($paths['/pages/keywords/plasterers']), 'compact sitemap lists the plasterers keyword hub');
$ok(isset($paths['/pages/jobs/plasterers']), 'compact sitemap lists the plasterers job hub');
$ok(isset($paths['/pages/jobs/cp12']), 'compact sitemap lists the cp12 job hub');
$ok(!isset($paths['/pages/keywords/plasterers/york']), 'compact sitemap omits keyword×town');
$ok(!isset($paths['/pages/keywords/plasterers/burnley']), 'compact sitemap omits Burnley keyword×town');
$ok(!isset($paths['/pages/jobs/cp12/burnley']), 'compact sitemap omits job×town');

echo $fail === 0 ? "OK building dual {$pass}\n" : "FAIL building dual pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
