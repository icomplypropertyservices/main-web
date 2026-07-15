<?php
require_once __DIR__ . '/../config.php';
$dir = SITE_ROOT . '/data/keyword-enrichment';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$kw = getMajorKeywords();
$slugs = array_keys($kw);
$n = max(1, (int)ceil(count($slugs) / 16));
$chunks = array_chunk($slugs, $n);
foreach ($chunks as $i => $chunk) {
    $batch = [];
    foreach ($chunk as $s) {
        $batch[] = [
            'slug' => $s,
            'name' => $kw[$s]['name'],
            'service' => $kw[$s]['service'],
        ];
    }
    $num = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
    file_put_contents($dir . "/batch-{$num}-slugs.json", json_encode($batch, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "batch-{$num}: " . count($batch) . "\n";
}
