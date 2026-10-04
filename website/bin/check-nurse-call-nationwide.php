#!/usr/bin/env php
<?php
/**
 * Nurse call × nationwide areas.
 * Places live in data/nationwide-areas.json and are not merged into areas.json.
 * Static export emits /pages/nurse-call/{place} and every nurse-call keyword × place.
 * Sitemap lists neither /pages/nurse-call/{place} nor keyword×town URLs (both 301).
 *
 * Usage: php website/bin/check-nurse-call-nationwide.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';
require_once __DIR__ . '/../includes/matrix-page.php';
require_once __DIR__ . '/../includes/sitemap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$fail = 0;
$pass = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        echo "[PASS] {$msg}\n";
    } else {
        $fail++;
        echo "[FAIL] {$msg}\n";
    }
};

$rows = getNationwideAreaRows();
$areas = getAreas();
$nwSlugs = [];
foreach ($areas as $area) {
    $nwSlugs[areaSlug((string)$area)] = true;
}
$slugs = [];
$nations = [];
$dup = [];
$overlap = [];
foreach ($rows as $row) {
    $nations[$row['nation']] = true;
    if (isset($slugs[$row['slug']])) {
        $dup[] = $row['slug'];
    }
    $slugs[$row['slug']] = $row['name'];
    if (isset($nwSlugs[$row['slug']])) {
        $overlap[] = $row['slug'];
    }
}

$expectedAreas = 260;
$ok(count($rows) === $expectedAreas, 'nationwide_areas=' . count($rows) . " (expected {$expectedAreas})");
$ok($dup === [], 'nationwide slugs unique' . ($dup ? ' (' . implode(',', $dup) . ')' : ''));
$ok($overlap === [], 'no overlap with North West areas.json' . ($overlap ? ' (' . implode(',', $overlap) . ')' : ''));
$ok(count($areas) === 168, 'North West areas.json unchanged at ' . count($areas));
foreach (['England', 'Scotland', 'Wales', 'Northern Ireland'] as $nation) {
    $ok(isset($nations[$nation]), "nation present: {$nation}");
}
foreach (['Birmingham', 'Leeds', 'London', 'Edinburgh', 'Cardiff', 'Belfast', 'Newcastle upon Tyne', "King's Lynn"] as $city) {
    $row = nationwideAreaRow($city);
    $ok($row !== null && $row['name'] === $city, "place {$city}");
}
$ok(nationwideAreaRow('Stockport') === null, 'Stockport stays a North West area, not nationwide');
$ok(nationwideAreaRow('Bangor') !== null && nationwideAreaRow('Bangor')['nation'] === 'Wales', 'Bangor is the Welsh town');
$ok(nationwideAreaRow('Bangor, County Down') !== null, 'Bangor, County Down is separate');

$keywords = getNurseCallKeywordSlugs();
$ok(count($keywords) >= 70, 'nurse-call keywords=' . count($keywords) . ' (>=70)');
foreach (getNurseCallFeaturedKeywordSlugs() as $slug) {
    $ok(in_array($slug, $keywords, true), "featured nurse-call keyword {$slug}");
}

$paths = nurseCallNationwideExportPaths();
$servicePaths = 0;
$keywordPaths = 0;
$foreign = 0;
foreach ($paths as $path) {
    if (preg_match('#^/pages/nurse-call/[a-z0-9\-]+$#', $path)) {
        $servicePaths++;
    } elseif (preg_match('#^/pages/keywords/[a-z0-9\-]+/[a-z0-9\-]+$#', $path)) {
        $keywordPaths++;
    } else {
        $foreign++;
    }
}
$ok($foreign === 0, 'export paths are nurse-call service or keyword×place only');
$ok($servicePaths === $expectedAreas, "service_pages={$servicePaths}");
$ok($keywordPaths === count($keywords) * $expectedAreas, "keyword_area_pages={$keywordPaths}");
$ok(in_array('/pages/nurse-call/birmingham', $paths, true), 'route /pages/nurse-call/birmingham');
$ok(in_array('/pages/keywords/nurse-call-system/edinburgh', $paths, true), 'route /pages/keywords/nurse-call-system/edinburgh');
$ok(!in_array('/pages/electrical/birmingham', $paths, true), 'electrical is not given Birmingham');
$ok(!in_array('/pages/nurse-call/stockport', $paths, true), 'Stockport is not duplicated onto the nationwide list');

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'nurseCallNationwideExportPaths'), 'static-export wires nurse-call nationwide paths');

$bham = icomplyRenderServiceAreaHtml('nurse-call', 'Birmingham');
$ok(stripos($bham, '<h1') !== false && str_contains($bham, 'Birmingham'), 'matrix service page H1 Birmingham');
$ok(str_contains($bham, 'West Midlands') && str_contains($bham, 'England'), 'Birmingham region and nation');
$ok(str_contains($bham, 'POA'), 'Birmingham page says POA');
$ok(!preg_match('/£\s*\d/', $bham), 'Birmingham page has no £ price');
$bhamHero = '';
if (preg_match('/<section class="matrix-hero">.*?<\/section>/s', $bham, $bhamHeroMatch)) {
    $bhamHero = $bhamHeroMatch[0];
}
$ok($bhamHero !== '' && !str_contains($bhamHero, 'North West'), 'Birmingham hero does not say North West');
$ok(!str_contains($bham, '/pages/areas/birmingham'), 'Birmingham does not link a missing area hub');
$ok(str_contains($bham, '/pages/services/nurse-call'), 'Birmingham links the nurse call service hub');
$ok(str_contains($bham, '#0B1F3A') && str_contains($bham, '#ff6b00'), 'matrix page keeps navy and orange');

$edi = icomplyRenderKeywordTownHtml('care-home-nurse-call', 'Edinburgh');
$ok(str_contains($edi, 'Edinburgh') && str_contains($edi, 'Lothian') && str_contains($edi, 'Scotland'), 'Edinburgh keyword page place');
$ok(str_contains($edi, 'POA') && !preg_match('/£\s*\d/', $edi), 'Edinburgh keyword page is POA without a £ price');
$ok(!str_contains($edi, '/pages/areas/edinburgh'), 'Edinburgh keyword page does not link a missing area hub');
$ok(str_contains($edi, '/pages/nurse-call/edinburgh'), 'Edinburgh keyword page links the nurse call area page');

$stockport = icomplyRenderServiceAreaHtml('nurse-call', 'Stockport');
$stockportHero = '';
if (preg_match('/<section class="matrix-hero">.*?<\/section>/s', $stockport, $stockportHeroMatch)) {
    $stockportHero = $stockportHeroMatch[0];
}
$ok($stockportHero !== '' && str_contains($stockportHero, 'North West'), 'Stockport nurse call hero still says North West');

ob_start();
renderServiceAreaPage('nurse-call', 'Belfast');
$combo = (string)ob_get_clean();
$ok(str_contains($combo, 'Belfast') && str_contains($combo, 'County Antrim'), 'router combo names Belfast');
$ok(str_contains($combo, 'POA') && !preg_match('/£\s*\d/', $combo), 'router combo is POA without a £ price');
$ok(str_contains($combo, 'Nurse call in Belfast'), 'router combo hero is nurse call in Belfast');
$ok(!str_contains($combo, 'and the wider North West'), 'router combo does not place Belfast in the wider North West');
$ok(!str_contains($combo, 'within 2 hours'), 'router combo does not promise a 2 hour response in Belfast');
$ok(!str_contains($combo, 'Same-week'), 'router combo does not promise same-week in Belfast');

ob_start();
renderServiceHubPage('nurse-call');
$hub = (string)ob_get_clean();
$ok(str_contains($hub, 'id="nurse-call-uk"'), 'nurse call hub has the UK list');
$ok(str_contains($hub, '/pages/nurse-call/cardiff'), 'hub links Cardiff');
$ok(substr_count($hub, '/pages/nurse-call/') >= $expectedAreas, 'hub links every nationwide nurse call page');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(!preg_match('#/pages/nurse-call/[a-z0-9\-]+</loc>#', $xml), 'sitemap has zero /pages/nurse-call/{place}');
$ok(!str_contains($xml, '/pages/keywords/nurse-call-system/birmingham</loc>'), 'sitemap omits nurse-call-system/birmingham (301)');
$ok(!str_contains($xml, '/pages/keywords/care-home-nurse-call/belfast</loc>'), 'sitemap omits care-home-nurse-call/belfast (301)');
$kwTown = preg_match_all('#/pages/keywords/[a-z0-9\-]+/[a-z0-9\-]+</loc>#', $xml);
$ok($kwTown === 0, 'sitemap keyword×town count is 0 (' . $kwTown . ')');

echo str_repeat('=', 56) . "\n";
echo "nationwide_areas={$expectedAreas} nurse_call_keywords=" . count($keywords)
    . " service_pages={$servicePaths} keyword_area_pages={$keywordPaths}\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
