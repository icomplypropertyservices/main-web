#!/usr/bin/env php
<?php
/**
 * Anti-slop gate for sitemap town pages.
 *
 * Every indexable service×town article must be long enough, free of doorway
 * filler, and must not share a 6-word run with another indexable town article
 * once the town name and service name are blanked out. Thin clones stay
 * noindex and out of sitemap.xml.
 *
 * Usage: php website/bin/check-town-uniqueness.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/tier1-copy.php';
require_once __DIR__ . '/../includes/sitemap.php';
require_once __DIR__ . '/../includes/matrix-page.php';

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

$minWords = 110;
$shingle = 6;

$banned = [
    'niceic',
    'part p',
    'certified electrician',
    'same day',
    'on time, every time',
    'looking for professional',
    'looking for expert',
    'if you manage property',
    'searching for',
    'is not optional',
    'subcontract',
    'napit',
];

$articles = [];
$services = array_keys(icomplyTier1CopyMap()['services']);
foreach (icomplyTier1Towns() as $town) {
    $ok(icomplyIsTier1Area($town), "tier-1 town is on the areas list: {$town}");
    foreach ($services as $svc) {
        $text = icomplyTier1ServiceArticle($svc, $town);
        $path = '/pages/' . $svc . '/' . areaSlug($town);
        $articles[$path] = [
            'text' => $text,
            'town' => $town,
            'service' => (string)(getServices()[$svc] ?? $svc),
            'slug' => $svc,
        ];
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $ok(count($words) >= $minWords, "{$path} article words=" . count($words) . " (>={$minWords})");
        $low = mb_strtolower($text);
        $hit = [];
        foreach ($banned as $needle) {
            if (str_contains($low, $needle)) {
                $hit[] = $needle;
            }
        }
        if (preg_match('/£\s*\d/', $text)) {
            $hit[] = 'invented price';
        }
        if (preg_match('/\bpartners?\b/i', $text)) {
            $hit[] = 'partner';
        }
        $ok($hit === [], "{$path} has no doorway or banned phrasing" . ($hit ? ' (' . implode(', ', $hit) . ')' : ''));
        $ok(icomplyPathIsIndexable($path), "{$path} is indexable");
        $html = icomplyRenderServiceAreaHtml($svc, $town);
        $shown = '';
        if (preg_match('#id="local-copy"[^>]*>\s*<p>(.*?)</p>#s', $html, $shownMatch)) {
            $shown = html_entity_decode(strip_tags($shownMatch[1]), ENT_QUOTES, 'UTF-8');
        }
        $ok($shown === $text, "{$path} renders the article unchanged");
        $ok(str_contains($html, 'index, follow') && !str_contains($html, 'noindex'), "{$path} robots index, follow");
        $ok(!str_contains($html, 'Looking for professional') && !str_contains($html, 'If you manage property'), "{$path} HTML omits spun openers");
        if ($svc === 'gas-systems') {
            $ok(str_contains($html, 'Gas Safe registered engineers') && str_contains($html, 'does not'), "{$path} keeps the gas-work refusal");
        }
    }
}

$ok(!icomplyPathIsIndexable('/pages/electrical/preston'), 'preston electrical is noindex');
$ok(!icomplyPathIsIndexable('/pages/plastering/stockport'), 'plastering stockport has no bespoke article');
$ok(!icomplyPathIsIndexable('/pages/areas/stockport'), 'templated area towns stay noindex');
$ok(icomplyPathIsIndexable('/pages/areas/manchester'), 'Manchester area hub is indexable');
$ok(icomplyPathIsIndexable('/pages/areas/burnley'), 'Burnley area hub is indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/eicr/stockport'), 'keyword×town stays noindex');
$ok(icomplyPathIsIndexable('/pages/areas'), 'areas index stays indexable');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
foreach ($articles as $path => $row) {
    $ok(!str_contains($xml, $path . '</loc>'), "sitemap omits unpublished {$path}");
}
$ok(!str_contains($xml, '/pages/electrical/preston</loc>'), 'sitemap omits preston');
$ok(!str_contains($xml, '/pages/plastering/stockport</loc>'), 'sitemap omits thin plastering town page');
$ok(!str_contains($xml, '/pages/areas/stockport</loc>'), 'sitemap omits templated area hub');
$ok(!preg_match('#/pages/keywords/[a-z0-9\-]+/[a-z0-9\-]+</loc>#', $xml), 'sitemap omits keyword×town');

$seen = [];
$collisions = [];
foreach ($articles as $path => $row) {
    $norm = icomplySlopNormalize((string)$row['text'], (string)$row['town'], (string)$row['service']);
    $grams = icomplySlopShingles($norm, $shingle);
    foreach ($grams as $gram => $_) {
        if (isset($seen[$gram]) && $seen[$gram] !== $path && count($collisions) < 12) {
            $collisions[] = $gram . ' :: ' . $seen[$gram] . ' <> ' . $path;
        }
        if (!isset($seen[$gram])) {
            $seen[$gram] = $path;
        }
    }
}
$ok($collisions === [], 'no shared 6-word run across town articles' . ($collisions ? "\n  " . implode("\n  ", $collisions) : ''));

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail} articles=" . count($articles) . "\n";
exit($fail > 0 ? 1 : 0);

function icomplySlopNormalize(string $text, string $town, string $serviceName): string
{
    $text = mb_strtolower($text);
    $text = str_replace(mb_strtolower($town), ' ', $text);
    $text = str_replace(mb_strtolower($serviceName), ' ', $text);
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text) ?? $text;
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
}

/**
 * @return array<string, true>
 */
function icomplySlopShingles(string $text, int $n): array
{
    $words = $text === '' ? [] : explode(' ', $text);
    $out = [];
    $len = count($words);
    for ($i = 0; $i <= $len - $n; $i++) {
        $gram = implode(' ', array_slice($words, $i, $n));
        if (substr_count($gram, ' ') < $n - 1) {
            continue;
        }
        $out[$gram] = true;
    }
    return $out;
}
