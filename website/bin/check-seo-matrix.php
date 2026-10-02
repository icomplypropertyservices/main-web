#!/usr/bin/env php
<?php
/**
 * Assert Marketing seo-matrix-*.md is the electrical/gas source of truth:
 * every listed slug exists, POA holds, HMO package landings are present, sitemap is 200-only.
 *
 * Usage: php website/bin/check-seo-matrix.php
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
    } else {
        $fail++;
        echo "[FAIL] {$msg}\n";
    }
};

$parseSlugs = static function (string $file): array {
    $path = SITE_ROOT . '/data/' . $file;
    if (!is_file($path)) {
        return [];
    }
    $slugs = [];
    foreach (preg_split('/\R/', (string)file_get_contents($path)) ?: [] as $line) {
        if (preg_match('/^- `([a-z0-9\-]+)` —/', $line, $m)) {
            $slugs[$m[1]] = true;
        }
    }
    return array_keys($slugs);
};

$ok(is_file(SITE_ROOT . '/data/seo-matrix-electrical.md'), 'seo-matrix-electrical.md present');
$ok(is_file(SITE_ROOT . '/data/seo-matrix-gas.md'), 'seo-matrix-gas.md present');
$ok(is_file(SITE_ROOT . '/data/seo-matrix-rollout-notes.md'), 'seo-matrix-rollout-notes.md present');

$notes = is_file(SITE_ROOT . '/data/seo-matrix-rollout-notes.md')
    ? (string)file_get_contents(SITE_ROOT . '/data/seo-matrix-rollout-notes.md')
    : '';
$ok(str_contains($notes, 'POA') && str_contains($notes, 'P090'), 'rollout notes cover POA + P090');
$ok(str_contains($notes, 'HMO') && str_contains($notes, 'sitemap'), 'rollout notes cover HMO packages + sitemap');

$elecMd = $parseSlugs('seo-matrix-electrical.md');
$gasMd = $parseSlugs('seo-matrix-gas.md');
$ok(count($elecMd) >= 100, 'electrical matrix slugs=' . count($elecMd) . ' (>=100)');
$ok(count($gasMd) >= 100, 'gas matrix slugs=' . count($gasMd) . ' (>=100)');

$kw = getMajorKeywords();
$elec = getKeywordsForService('electrical');
$gas = getKeywordsForService('gas-systems');
$missingElec = [];
$wrongElec = [];
foreach ($elecMd as $slug) {
    if (!isset($kw[$slug])) {
        $missingElec[] = $slug;
    } elseif (($kw[$slug]['service'] ?? '') !== 'electrical') {
        $wrongElec[] = $slug;
    }
}
$missingGas = [];
$wrongGas = [];
foreach ($gasMd as $slug) {
    if (!isset($kw[$slug])) {
        $missingGas[] = $slug;
    } elseif (($kw[$slug]['service'] ?? '') !== 'gas-systems') {
        $wrongGas[] = $slug;
    }
}
$ok($missingElec === [], 'all electrical matrix slugs exist in keywords.json' . ($missingElec ? ' missing=' . implode(',', array_slice($missingElec, 0, 8)) : ''));
$ok($wrongElec === [], 'electrical matrix slugs are service=electrical');
$ok($missingGas === [], 'all gas matrix slugs exist in keywords.json' . ($missingGas ? ' missing=' . implode(',', array_slice($missingGas, 0, 8)) : ''));
$ok($wrongGas === [], 'gas matrix slugs are service=gas-systems');

$required = [
    'rewire', 'domestic-rewire', 'emergency-electrician', 'consumer-unit',
    'fuse-board', 'eicr', 'price-of-rewire', 'partial-rewire',
    'boiler', 'boiler-install', 'boiler-repair', 'cp12', 'gas-safety',
    'emergency-gas-engineer', 'landlord-gas',
];
foreach ($required as $slug) {
    $in = in_array($slug, $elecMd, true) || in_array($slug, $gasMd, true);
    $ok($in, "Jack required slug listed in matrix md: {$slug}");
}

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
$ok($poundHits === [], 'no invented £ prices' . ($poundHits ? ' (' . implode(',', $poundHits) . ')' : ''));
$ok($costMissingPoa === [], 'cost/price keywords mention POA' . ($costMissingPoa ? ' (' . implode(',', $costMissingPoa) . ')' : ''));

$hmoPackages = [
    '/pages/packages/hmo.php',
    '/pages/packages/hmo-compliance.php',
    '/pages/packages/hmo-fire-safety.php',
    '/pages/packages/hmo-occupancy.php',
    '/includes/hmo-package-page.php',
];
$hmoPresent = [];
foreach ($hmoPackages as $rel) {
    if (is_file(SITE_ROOT . $rel)) {
        $hmoPresent[] = $rel;
    }
}
$ok(count($hmoPresent) === count($hmoPackages), 'HMO package pages are present');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(!preg_match('#/pages/gas-systems/[a-z0-9\-]+</loc>#', $xml), 'sitemap has zero /pages/gas-systems/{town}');
$ok(!preg_match('#/pages/electrical/[a-z0-9\-]+</loc>#', $xml), 'sitemap has zero /pages/electrical/{town}');
$ok(str_contains($xml, '/pages/packages/hmo</loc>'), 'sitemap lists the HMO packages hub');
$ok(str_contains($xml, '/pages/packages/hmo-compliance</loc>'), 'sitemap lists HMO compliance');
$ok(str_contains($xml, '/pages/hmo-eicr/stockport</loc>'), 'sitemap lists HMO EICR Stockport');
$ok(!preg_match('#/pages/hmo-fire-alarms/[a-z0-9\-]+</loc>#', $xml), 'sitemap has no HMO fire-alarm town doorway');
$kwTown = preg_match_all('#/pages/keywords/[a-z0-9\-]+/[a-z0-9\-]+</loc>#', $xml);
$ok($kwTown > 0 && $kwTown <= 180, 'sitemap keyword×town is featured-only (' . $kwTown . ', not the full matrix)');

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'getElectricalGasMatrixKeywordSlugs'), 'static-export still wires full electrical+gas × areas');

$areas = getAreas();
$ok(count($areas) >= 150, 'full areas list still ' . count($areas) . ' towns');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
