#!/usr/bin/env php
<?php
/**
 * Jack 2026-10-19: no iComply page is ever noindexed or pruned from the sitemap.
 *
 * Static mode (no dist needed; runs inside check-sitemap.php):
 *   - the old sitemap-redirect-exclude.txt prune list is gone and nothing reads it
 *   - every path removed from that list that answered 200 is emitted by
 *     icomplySitemapEntries(); the 301 ones are not
 *
 * Export mode (after static-export.php):
 *   php website/bin/check-sitemap-no-prune.php --dist=/path/to/dist
 *   - sitemap.xml is a sitemapindex; every shard <= 50,000 URLs
 *   - every loc is listed exactly once across all shards
 *   - every removed-list path that answered 200 is present; 301 ones absent
 *   - no loc is an exact 301/302 in dist/_redirects
 *   - no exported sitemap page renders a noindex robots meta
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sitemap.php';

$noPruneFail = 0;
$noPruneSay = static function (bool $cond, string $msg) use (&$noPruneFail): void {
    if (!$cond) {
        $noPruneFail++;
        echo "FAIL: {$msg}\n";
    }
};
$base = 'https://icomplypropertyservices.co.uk';

$fixture = __DIR__ . '/fixtures/sitemap-exclusions-removed-2026-10-19.txt';
$want200 = [];
$want301 = [];
foreach (file($fixture, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    [$p, $code] = array_pad(preg_split('/\s+/', trim($line)), 2, '');
    if ($code === '200') {
        $want200[$p] = true;
    } else {
        $want301[$p] = true;
    }
}
$noPruneSay(count($want200) > 8000, 'fixture lists ' . count($want200) . ' removed 200 paths (want > 8000)');

// Static: the prune list and every reader of it are gone.
$noPruneSay(!is_file(SITE_ROOT . '/data/sitemap-redirect-exclude.txt'), 'website/data/sitemap-redirect-exclude.txt still exists');
foreach (['includes/sitemap.php', 'includes/matrix-catalogue.php', 'bin/static-export.php', 'bin/publish-sitemap.php', 'bin/generate-sitemap.php'] as $rel) {
    $src = (string)@file_get_contents(SITE_ROOT . '/' . $rel);
    $noPruneSay(!str_contains($src, "sitemap-redirect-exclude.txt'") && !str_contains($src, 'sitemap-redirect-exclude.txt"'), "{$rel} still reads a sitemap exclusion list");
}
$matrixSrc = (string)@file_get_contents(SITE_ROOT . '/includes/matrix-catalogue.php');
$noPruneSay(!preg_match('/return\s*\(bool\)preg_match\([^;]*noindex/', $matrixSrc), 'matrix sitemap still drops pages for a noindex meta');

// Static: the compact generator emits every removed 200 path (fire-family × mainland etc.).
$entries = [];
foreach (icomplySitemapEntries() as $e) {
    $entries[$e['path']] = ($entries[$e['path']] ?? 0) + 1;
}
$missing = array_keys(array_diff_key($want200, $entries));
$noPruneSay($missing === [], 'compact sitemap missing ' . count($missing) . ' removed-list 200 paths, e.g. ' . implode(',', array_slice($missing, 0, 6)));
$dupEntries = array_keys(array_filter($entries, static fn(int $n): bool => $n > 1));
$noPruneSay($dupEntries === [], 'compact sitemap lists duplicates: ' . implode(',', array_slice($dupEntries, 0, 6)));
foreach (['/pages/fire-risk-assessments/bolton', '/pages/fire-risk-assessments/manchester', '/pages/fire-alarms/leeds', '/pages/fire-extinguishers/birmingham', '/pages/fire-alarms/aberdeen', '/thank-you'] as $p) {
    $noPruneSay(isset($entries[$p]), "compact sitemap missing {$p}");
}
echo 'OK: no-prune static: removed-list 200 paths=' . count($want200) . ' present=' . (count($want200) - count($missing)) . "\n";

// Export mode.
$opts = getopt('', ['dist::']);
$dist = isset($opts['dist']) && is_string($opts['dist']) ? rtrim($opts['dist'], '/') : '';
if ($dist !== '') {
    $index = (string)@file_get_contents($dist . '/sitemap.xml');
    $noPruneSay(str_contains($index, '<sitemapindex'), 'dist sitemap.xml is not a sitemapindex');
    preg_match_all('#<loc>' . preg_quote($base, '#') . '/([^<]+)</loc>#', $index, $sm);
    $shards = $sm[1] ?? [];
    $seen = [];
    $dups = [];
    $maxShard = 0;
    foreach ($shards as $name) {
        $xml = (string)@file_get_contents($dist . '/' . $name);
        $noPruneSay($xml !== '' && str_contains($xml, '<urlset'), "shard {$name} missing or not a urlset");
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $lm);
        $n = count($lm[1]);
        $maxShard = max($maxShard, $n);
        $noPruneSay($n <= 50000, "shard {$name} has {$n} URLs (max 50,000)");
        foreach ($lm[1] as $loc) {
            $path = substr(html_entity_decode($loc, ENT_XML1), strlen($base));
            $path = $path === '' ? '/' : $path;
            if (isset($seen[$path])) {
                $dups[$path] = true;
            }
            $seen[$path] = true;
        }
    }
    $noPruneSay($dups === [], 'export sitemap lists ' . count($dups) . ' URLs twice, e.g. ' . implode(',', array_slice(array_keys($dups), 0, 6)));
    $miss = array_keys(array_diff_key($want200, $seen));
    $noPruneSay($miss === [], 'export sitemap missing ' . count($miss) . ' removed-list 200 paths, e.g. ' . implode(',', array_slice($miss, 0, 6)));
    $leak = array_keys(array_intersect_key($want301, $seen));
    $noPruneSay($leak === [], 'export sitemap lists ' . count($leak) . ' 301 paths, e.g. ' . implode(',', array_slice($leak, 0, 6)));

    $redirects = [];
    foreach (file($dist . '/_redirects', FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $parts = preg_split('/\s+/', trim((string)$line));
        if (count($parts) >= 3 && ($parts[0][0] ?? '#') === '/' && preg_match('/^30[12]!?$/', $parts[2]) && !str_contains($parts[0], '*') && !str_contains($parts[0], ':')) {
            $redirects[rtrim($parts[0], '/') ?: '/'] = true;
        }
    }
    $redir = array_values(array_intersect_key($redirects, $seen));
    $noPruneSay($redir === [], 'export sitemap lists ' . count($redir) . ' exact _redirects 301s, e.g. ' . implode(',', array_slice(array_keys(array_intersect_key($redirects, $seen)), 0, 6)));

    $scanned = 0;
    $noindex = [];
    foreach (array_keys($seen) as $path) {
        $rel = ltrim($path, '/');
        $file = null;
        foreach ($rel === '' ? ['index.html'] : [$rel, $rel . '.php', $rel . '.html', $rel . '/index.html'] as $c) {
            if (is_file($dist . '/' . $c)) {
                $file = $dist . '/' . $c;
                break;
            }
        }
        if ($file === null) {
            continue;
        }
        $scanned++;
        $fh = fopen($file, 'rb');
        $head = $fh ? (string)fread($fh, 16384) : '';
        if ($fh) {
            fclose($fh);
        }
        if (preg_match('/<meta[^>]+name=["\']robots["\'][^>]*noindex/i', $head)) {
            $noindex[] = $path;
        }
    }
    $noPruneSay($noindex === [], count($noindex) . ' exported sitemap pages render noindex, e.g. ' . implode(',', array_slice($noindex, 0, 6)));
    echo 'OK: no-prune export: shards=' . count($shards) . ' max_per_shard=' . $maxShard . ' unique_urls=' . count($seen)
        . ' duplicates=' . count($dups) . ' exported_pages_scanned=' . $scanned . ' noindex=' . count($noindex) . "\n";
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    echo $noPruneFail === 0 ? "PASS\n" : "FAIL ({$noPruneFail})\n";
    exit($noPruneFail === 0 ? 0 : 1);
}
