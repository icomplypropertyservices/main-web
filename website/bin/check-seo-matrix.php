#!/usr/bin/env php
<?php
/**
 * Assert Marketing seo-matrix-*.md is the electrical/gas source of truth:
 * every listed slug exists, POA holds, HMO packages stay out, sitemap is 200-only.
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
    if (function_exists('icomplyBlobHasInventedPrice') ? icomplyBlobHasInventedPrice($blob) : preg_match('/£\s*\d/', $blob)) {
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
$ok($hmoPresent === [], 'HMO package pages stay out');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(!str_contains($xml, '/pages/electrical/stockport</loc>') && !str_contains($xml, '/pages/gas-systems/warrington</loc>'), 'sitemap omits unpublished electrical and gas town pages');
$ok(!str_contains($xml, '/pages/electrical/preston</loc>') && !str_contains($xml, '/pages/gas-systems/bury</loc>'), 'sitemap omits non-Tier-1 electrical and gas town pages');
$ok(str_contains($xml, '/pages/areas/manchester</loc>') && str_contains($xml, '/pages/areas/burnley</loc>'), 'sitemap lists Manchester and Burnley area hubs');
$ok(str_contains($xml, '/pages/areas/stockport</loc>') && str_contains($xml, '/pages/areas/oldham</loc>'), 'sitemap lists Greater Manchester area hubs');
$ok(str_contains($xml, '/pages/areas/liverpool</loc>') && str_contains($xml, '/pages/areas/york</loc>'), 'sitemap lists dual-ring area hubs');
$ok(!str_contains($xml, '/pages/areas/birmingham</loc>'), 'sitemap omits towns outside the rings');
$ok(!str_contains($xml, '/pages/packages/hmo'), 'sitemap has no HMO package locs');
$kwTown = preg_match_all('#/pages/keywords/[a-z0-9\-]+/[a-z0-9\-]+</loc>#', $xml);
$ok($kwTown === 0, 'sitemap keyword×town count is 0 (' . $kwTown . ')');

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'getElectricalGasMatrixKeywordSlugs'), 'static-export still wires full electrical+gas × areas');

$areas = getAreas();
$ok(count($areas) >= 150, 'full areas list still ' . count($areas) . ' towns');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
