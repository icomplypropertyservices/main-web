#!/usr/bin/env php
<?php
/**
 * Assert electrical + gas keyword families are ~100 each, required slugs
 * exist, cost keywords are POA-only, and the static-export route collector
 * includes the full keyword×area matrix.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';

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

$kw = getMajorKeywords();
$areas = getAreas();
$elec = getKeywordsForService('electrical');
$gas = getKeywordsForService('gas-systems');

$ok(count($areas) >= 150, 'areas=' . count($areas) . ' (>=150)');
$ok(count($elec) >= 100, 'electrical keywords=' . count($elec) . ' (>=100)');
$ok(count($gas) >= 100, 'gas keywords=' . count($gas) . ' (>=100)');

$required = [
    'rewire', 'domestic-rewire', 'emergency-electrician', 'consumer-unit',
    'fuse-board', 'eicr', 'price-of-rewire', 'partial-rewire',
    'boiler', 'boiler-install', 'boiler-repair', 'cp12', 'gas-safety',
    'emergency-gas-engineer', 'landlord-gas',
];
foreach ($required as $slug) {
    $ok(isset($kw[$slug]), "required slug {$slug}");
}

$ok(($elec['rewire']['service'] ?? '') === 'electrical', 'rewire is electrical');
$ok(($elec['domestic-rewire']['service'] ?? '') === 'electrical', 'domestic-rewire is electrical');
$ok(($gas['boiler']['service'] ?? '') === 'gas-systems', 'boiler is gas-systems');

$poundHits = [];
$costMissingPoa = [];
foreach (array_merge($elec, $gas) as $slug => $meta) {
    $name = (string)($meta['name'] ?? $slug);
    $blob = implode(' ', [
        (string)($meta['intro'] ?? ''),
        (string)($meta['body'] ?? ''),
        (string)($meta['meta_desc'] ?? ''),
        json_encode($meta['faq'] ?? []),
    ]);
    if (function_exists('icomplyBlobHasInventedPrice') ? icomplyBlobHasInventedPrice($blob) : preg_match('/£\s*\d/', $blob)) {
        $poundHits[] = $slug;
    }
    if (isCostStyleKeyword((string)$slug, $name) && !preg_match('/\bPOA\b/i', $blob)) {
        $costMissingPoa[] = $slug;
    }
}
$ok($poundHits === [], 'no invented £ prices in electrical/gas copy' . ($poundHits ? ' (' . implode(',', $poundHits) . ')' : ''));
$ok($costMissingPoa === [], 'cost/price keywords mention POA' . ($costMissingPoa ? ' (' . implode(',', $costMissingPoa) . ')' : ''));

foreach (['rewire', 'domestic-rewire', 'emergency-electrician', 'boiler'] as $slug) {
    ob_start();
    renderKeywordPage($slug);
    $html = (string)ob_get_clean();
    $ok($html !== '' && !str_contains($html, '<?php') && stripos($html, '<!DOCTYPE') !== false, "hub render {$slug}");
    ob_start();
    renderKeywordAreaPage($slug, 'Stockport');
    $areaHtml = (string)ob_get_clean();
    $ok(stripos($areaHtml, 'Stockport') !== false && stripos($areaHtml, '<!DOCTYPE') !== false, "area render {$slug}/stockport");
}

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'getElectricalGasMatrixKeywordSlugs'), 'static-export wires electrical+gas full-town matrix');
$ok(str_contains($exportSrc, 'isset($familyKw[$slug])'), 'static-export familyKw uses all areas');

$family = array_fill_keys(getElectricalGasMatrixKeywordSlugs(), true);
$ok(isset($family['rewire']) && isset($family['domestic-rewire']) && isset($family['boiler']), 'matrix slug list includes rewire/domestic-rewire/boiler');
$expectMatrix = count($family) * count($areas);
$ok($expectMatrix >= 100 * count($areas) * 2, 'matrix size=' . $expectMatrix . ' (>= 100×areas×2)');
$ok(in_array('Ramsbottom', $areas, true) && in_array('Stockport', $areas, true), 'areas include Stockport and Ramsbottom');

$featured = getElectricalGasFeaturedKeywordSlugs();
$ok(in_array('rewire', $featured['electrical'] ?? [], true), 'featured electrical includes rewire');
$ok(in_array('boiler', $featured['gas'] ?? [], true), 'featured gas includes boiler');

require_once SITE_ROOT . '/includes/sitemap.php';
$sitemapXml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(!str_contains($sitemapXml, '/pages/gas-systems/stockport</loc>'), 'sitemap omits unpublished gas-systems/stockport');
$ok(!str_contains($sitemapXml, '/pages/electrical/stockport</loc>'), 'sitemap omits unpublished electrical/stockport');
$ok(!str_contains($sitemapXml, '/pages/emergency-lighting/stockport</loc>'), 'sitemap omits unpublished emergency-lighting/stockport');
$ok(!str_contains($sitemapXml, '/pages/electrical/preston</loc>'), 'sitemap omits electrical/preston');
$ok(str_contains($sitemapXml, '/pages/areas/manchester</loc>') && str_contains($sitemapXml, '/pages/areas/burnley</loc>'), 'sitemap lists Manchester and Burnley area hubs');
$ok(!str_contains($sitemapXml, '/pages/epc/stockport'), 'sitemap has no thin /pages/epc/stockport');
$ok(str_contains($sitemapXml, '/pages/services/gas-systems</loc>'), 'sitemap still lists gas-systems service hub');
$ok(str_contains($sitemapXml, '/pages/keywords/boiler</loc>'), 'sitemap still lists boiler keyword hub');
$ok(is_file(SITE_ROOT . '/data/seo-matrix-electrical.md') && is_file(SITE_ROOT . '/data/seo-matrix-gas.md'), 'Marketing seo-matrix md files present');
$sitemapSrc = (string)file_get_contents(SITE_ROOT . '/includes/sitemap.php');
$ok(str_contains($sitemapSrc, 'icomplyPathIsIndexable'), 'sitemap omits noindex town templates');
$ok(is_file(SITE_ROOT . '/data/seo-matrix-rollout-notes.md'), 'seo-matrix-rollout-notes.md present');

$areaSet = [];
foreach ($areas as $areaName) {
    $areaSet[areaSlug((string)$areaName)] = true;
}
$assertTownLinks = static function (string $html, string $service) use ($ok, $areaSet): void {
    if (in_array($service, getElectricalGasFamilyServices(), true)) {
        preg_match_all('#/pages/keywords/[a-z0-9\-]+/([a-z0-9\-]+)#', $html, $m);
        $towns = array_values(array_unique($m[1] ?? []));
        $unknown = array_values(array_filter($towns, static fn (string $t): bool => !isset($areaSet[$t])));
        $ok(
            $unknown === [] && in_array('stockport', $towns, true) && !str_contains($html, '/pages/' . $service . '/stockport'),
            $service . ' hub uses keyword×town links' . ($unknown ? ' unknown=' . implode(',', $unknown) : '')
        );
        return;
    }
    preg_match_all('#/pages/' . preg_quote($service, '#') . '/([a-z0-9\-]+)#', $html, $m);
    $towns = array_values(array_unique($m[1] ?? []));
    $unknown = array_values(array_filter($towns, static fn (string $t): bool => !isset($areaSet[$t])));
    $ok($unknown === [] && in_array('stockport', $towns, true), $service . ' hub town links are real areas' . ($unknown ? ' unknown=' . implode(',', $unknown) : ''));
};
ob_start();
renderServiceHubPage('gas-systems');
$gasHub = (string)ob_get_clean();
$assertTownLinks($gasHub, 'gas-systems');
ob_start();
renderServiceHubPage('electrical');
$elecHub = (string)ob_get_clean();
$assertTownLinks($elecHub, 'electrical');
ob_start();
renderAreaHubPage('Stockport');
$areaHub = (string)ob_get_clean();
preg_match_all('#/pages/([a-z0-9\-]+)/([a-z0-9\-]+)#', $areaHub, $am);
$badAreaLinks = [];
$services = getServices();
foreach ($am[1] as $i => $first) {
    if (!isset($services[$first])) {
        continue;
    }
    $town = $am[2][$i];
    if (!isset($areaSet[$town])) {
        $badAreaLinks[] = $first . '/' . $town;
    }
}
$ok($badAreaLinks === [] && str_contains($areaHub, 'index, follow') && !str_contains($areaHub, 'noindex'), 'Stockport area hub links real towns and is indexable' . ($badAreaLinks ? ' bad=' . implode(',', array_slice($badAreaLinks, 0, 4)) : ''));

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
