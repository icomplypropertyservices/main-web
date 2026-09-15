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
    if (preg_match('/£\s*\d/', $blob)) {
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
$ok(!preg_match('#/pages/gas-systems/[a-z0-9\-]+</loc>#', $sitemapXml), 'sitemap has zero /pages/gas-systems/{town}');
$ok(!preg_match('#/pages/electrical/[a-z0-9\-]+</loc>#', $sitemapXml), 'sitemap has zero /pages/electrical/{town}');
$ok(str_contains($sitemapXml, '/pages/services/gas-systems</loc>'), 'sitemap still lists gas-systems service hub');
$ok(str_contains($sitemapXml, '/pages/keywords/boiler</loc>'), 'sitemap still lists boiler keyword hub');
$ok(is_file(SITE_ROOT . '/data/seo-matrix-electrical.md') && is_file(SITE_ROOT . '/data/seo-matrix-gas.md'), 'Marketing seo-matrix md files present');
$ok(is_file(SITE_ROOT . '/data/seo-matrix-rollout-notes.md'), 'seo-matrix-rollout-notes.md present');

ob_start();
renderServiceHubPage('gas-systems');
$gasHub = (string)ob_get_clean();
$ok(!preg_match('#/pages/gas-systems/[a-z0-9\-]+#', $gasHub), 'gas-systems hub HTML has no /pages/gas-systems/{town} 404s');
ob_start();
renderServiceHubPage('electrical');
$elecHub = (string)ob_get_clean();
$ok(!preg_match('#/pages/electrical/[a-z0-9\-]+#', $elecHub), 'electrical hub HTML has no /pages/electrical/{town} 404s');
ob_start();
renderAreaHubPage('Stockport');
$areaHub = (string)ob_get_clean();
$ok(!preg_match('#/pages/(gas-systems|electrical|fire-alarms)/[a-z0-9\-]+#', $areaHub), 'area hub HTML has no service×area 404s');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
