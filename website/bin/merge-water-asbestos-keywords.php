#!/usr/bin/env php
<?php
declare(strict_types=1);
$root = dirname(__DIR__);
require_once $root . '/config.php';
$file = $root . '/data/keywords.json';
$kw = json_decode((string)file_get_contents($file), true);
if (!is_array($kw)) {
    fwrite(STDERR, "keywords.json invalid\n");
    exit(1);
}
$add = waterAsbestosKeywordCatalog();
$n = 0;
foreach ($add as $slug => $row) {
    $kw[$slug] = $row;
    $n++;
}
ksort($kw, SORT_NATURAL | SORT_FLAG_CASE);
$json = json_encode($kw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "encode failed\n");
    exit(1);
}
file_put_contents($file, $json . "\n");
echo "Merged {$n} water/asbestos keywords. Total=" . count($kw) . "\n";
