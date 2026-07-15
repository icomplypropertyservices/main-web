<?php
/**
 * Merge data/keyword-enrichment/*-content.json into data/keywords.json
 */
require_once __DIR__ . '/../config.php';

$dir = SITE_ROOT . '/data/keyword-enrichment';
$kw = loadJsonData('keywords', []);
$merged = 0;

foreach (glob($dir . '/*-content.json') ?: [] as $file) {
    $data = json_decode((string)file_get_contents($file), true);
    if (!is_array($data)) {
        continue;
    }
    foreach ($data as $slug => $patch) {
        if (!is_array($patch)) {
            continue;
        }
        $slug = keywordSlug((string)$slug);
        if (!isset($kw[$slug])) {
            continue;
        }
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords', 'name'] as $k) {
            if (!empty($patch[$k]) && is_string($patch[$k])) {
                $kw[$slug][$k] = $patch[$k];
            }
        }
        if (!empty($patch['focus_points']) && is_array($patch['focus_points'])) {
            $kw[$slug]['focus_points'] = $patch['focus_points'];
        }
        if (!empty($patch['faq']) && is_array($patch['faq'])) {
            $kw[$slug]['faq'] = $patch['faq'];
        }
        $merged++;
    }
    echo 'Merged ' . basename($file) . "\n";
}

saveJsonData('keywords', $kw);
echo "Updated keyword entries: {$merged}\n";
