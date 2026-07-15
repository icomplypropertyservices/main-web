<?php
/**
 * Split catalog into 20 agent batch slug lists.
 */
require_once __DIR__ . '/../config.php';

$dir = SITE_ROOT . '/data/manufacturer-enrichment';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$catalog = getManufacturerCatalog();
$slugs = array_keys($catalog);
$n = max(1, (int)ceil(count($slugs) / 20));
$chunks = array_chunk($slugs, $n);

foreach ($chunks as $i => $chunk) {
    $batch = [];
    foreach ($chunk as $slug) {
        $batch[] = [
            'slug' => $slug,
            'name' => $catalog[$slug]['name'],
            'services' => $catalog[$slug]['services'],
        ];
    }
    $num = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
    file_put_contents(
        $dir . "/batch-{$num}-slugs.json",
        json_encode($batch, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
    echo "batch-{$num}: " . count($batch) . "\n";
}
echo 'Total batches: ' . count($chunks) . "\n";
