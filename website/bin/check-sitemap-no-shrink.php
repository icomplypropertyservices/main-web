#!/usr/bin/env php
<?php
/**
 * Jack 2026-10-28: the sitemap must never shrink again.
 * 23 Oct the live sitemap fell from 1,093,075 to 336,039 URLs. This guard fails CI
 * if the exported sitemap index lists fewer URLs than the 22 Oct baseline or
 * drops any baseline path.
 *
 *   php website/bin/check-sitemap-no-shrink.php --dist=/path/to/dist
 */
declare(strict_types=1);
$opts = getopt('', ['dist:', 'baseline::']);
$dist = rtrim((string)($opts['dist'] ?? ''), '/');
$baselineFile = (string)($opts['baseline'] ?? __DIR__ . '/fixtures/sitemap-baseline-2026-10-22.txt.gz');
$base = 'https://icomplypropertyservices.co.uk';
if ($dist === '' || !is_file($dist . '/sitemap.xml')) {
    fwrite(STDERR, "FAIL: --dist with sitemap.xml required\n");
    exit(1);
}
$want = [];
foreach (gzfile($baselineFile) ?: [] as $l) {
    $l = trim($l);
    if ($l !== '') {
        $want[$l] = true;
    }
}
$index = (string)file_get_contents($dist . '/sitemap.xml');
preg_match_all('#<loc>' . preg_quote($base, '#') . '/([^<]+)</loc>#', $index, $sm);
$seen = [];
$maxShard = 0;
foreach ($sm[1] as $name) {
    $xml = (string)@file_get_contents($dist . '/' . $name);
    preg_match_all('#<loc>([^<]+)</loc>#', $xml, $lm);
    $maxShard = max($maxShard, count($lm[1]));
    foreach ($lm[1] as $loc) {
        $p = substr(html_entity_decode($loc, ENT_XML1), strlen($base));
        $seen[$p === '' ? '/' : $p] = true;
    }
}
$missing = array_keys(array_diff_key($want, $seen));
$fail = 0;
if (count($seen) < count($want)) {
    echo 'FAIL: sitemap has ' . count($seen) . ' URLs, baseline ' . count($want) . "\n";
    $fail++;
}
if ($missing !== []) {
    echo 'FAIL: sitemap dropped ' . count($missing) . ' baseline URLs, e.g. ' . implode(',', array_slice($missing, 0, 8)) . "\n";
    $fail++;
}
if ($maxShard > 50000) {
    echo "FAIL: a shard has {$maxShard} URLs (max 50,000)\n";
    $fail++;
}
echo 'sitemap urls=' . count($seen) . ' baseline=' . count($want) . ' missing=' . count($missing) . ' new=' . (count($seen) - count($want) + count($missing)) . ' shards=' . count($sm[1]) . " max_per_shard={$maxShard}\n";
echo $fail ? "FAIL\n" : "PASS\n";
exit($fail ? 1 : 0);
