<?php
require_once __DIR__ . '/../config.php';
$cat = getManufacturerCatalog();
$by = loadJsonData('manufacturers', [])['by_service'] ?? [];
echo "TOTAL MANUFACTURER PAGES: " . count($cat) . "\n\n";
foreach (getServices() as $slug => $name) {
    $list = $by[$slug] ?? [];
    echo "### {$name} — " . count($list) . " brands\n";
    foreach ($list as $i => $brand) {
        $s = areaSlug($brand);
        $ok = is_file(SITE_ROOT . '/pages/manufacturers/' . $s . '.php') ? 'OK' : 'MISS';
        echo sprintf("  %2d. %s → /pages/manufacturers/%s.php [%s]\n", $i + 1, $brand, $s, $ok);
    }
    echo "\n";
}
