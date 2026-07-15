<?php
/**
 * Merge agent enrichment JSON files from data/manufacturer-enrichment/*.json
 * into data/manufacturers.json catalog entries.
 *
 * Each enrichment file: { "slug": { "blurb": "...", "seo_desc": "...", "seo_keywords": "...", "products": [...] } }
 */
require_once __DIR__ . '/../config.php';

$dir = SITE_ROOT . '/data/manufacturer-enrichment';
if (!is_dir($dir)) {
    echo "No enrichment dir\n";
    exit(0);
}

$mfr = loadJsonData('manufacturers', []);
$catalog = $mfr['catalog'] ?? [];
$merged = 0;

foreach (glob($dir . '/*-content.json') as $file) {
    $data = json_decode((string)file_get_contents($file), true);
    if (!is_array($data)) {
        continue;
    }
    foreach ($data as $slug => $patch) {
        if (!is_array($patch)) {
            continue;
        }
        $slug = areaSlug((string)$slug);
        if (!isset($catalog[$slug])) {
            continue;
        }
        foreach (['blurb', 'seo_title', 'seo_desc', 'seo_keywords'] as $k) {
            if (!empty($patch[$k]) && is_string($patch[$k])) {
                $catalog[$slug][$k] = $patch[$k];
            }
        }
        if (!empty($patch['products']) && is_array($patch['products'])) {
            $catalog[$slug]['products'] = $patch['products'];
        }
        $merged++;
    }
    echo 'Merged ' . basename($file) . "\n";
}

$mfr['catalog'] = $catalog;
saveJsonData('manufacturers', $mfr);
echo "Updated catalog entries: {$merged}\n";
