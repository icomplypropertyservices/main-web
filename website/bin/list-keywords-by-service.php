<?php
/**
 * List all keyword pages grouped by service.
 * Usage: php bin/list-keywords-by-service.php
 */
require_once __DIR__ . '/../config.php';

$keywords = getMajorKeywords();
$services = getServices();
$by = [];

foreach ($keywords as $slug => $meta) {
    $svc = $meta['service'] ?? 'unknown';
    $by[$svc][] = [
        'slug' => $slug,
        'name' => $meta['name'] ?? keywordDisplayName($slug),
    ];
}

// Sort each service alphabetically by name
foreach ($by as $svc => &$list) {
    usort($list, fn($a, $b) => strcasecmp($a['name'], $b['name']));
}
unset($list);

ksort($by);

echo "Icomply keyword inventory\n";
echo str_repeat('=', 50) . "\n";
echo "TOTAL KEYWORD PAGES: " . count($keywords) . "\n\n";

foreach ($services as $slug => $name) {
    $list = $by[$slug] ?? [];
    echo "### {$name} ({$slug}) — " . count($list) . " keywords\n";
    foreach ($list as $i => $item) {
        $n = $i + 1;
        echo sprintf("  %3d. %s  [%s]\n", $n, $item['name'], $item['slug']);
    }
    echo "\n";
}

// Any orphans
foreach ($by as $svc => $list) {
    if (!isset($services[$svc])) {
        echo "### UNKNOWN SERVICE ({$svc}) — " . count($list) . "\n";
        foreach ($list as $item) {
            echo "  - {$item['name']} [{$item['slug']}]\n";
        }
        echo "\n";
    }
}

echo str_repeat('=', 50) . "\n";
echo "Summary by service:\n";
foreach ($services as $slug => $name) {
    $c = count($by[$slug] ?? []);
    echo sprintf("  %-22s %4d\n", $name, $c);
}
echo sprintf("  %-22s %4d\n", 'TOTAL', count($keywords));
