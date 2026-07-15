<?php
/**
 * Thin stubs for /pages/manufacturers/{slug}.php
 * Usage: php bin/generate-manufacturer-pages.php
 */
require_once __DIR__ . '/../config.php';

$dir = SITE_ROOT . '/pages/manufacturers';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$catalog = getManufacturerCatalog();
foreach ($catalog as $slug => $entry) {
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-manufacturer-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderManufacturerPage({$slugExport});\n";
    file_put_contents("{$dir}/{$slug}.php", $stub);
}

// Do not overwrite handcrafted index if present with overhaul marker
$indexPath = $dir . '/index.php';
if (!is_file($indexPath) || strpos((string)file_get_contents($indexPath), 'MANUFACTURERS_INDEX_V2') === false) {
    // Leave index to dedicated file written by deploy — only create minimal if missing
    if (!is_file($indexPath)) {
        file_put_contents($indexPath, "<?php\nrequire_once __DIR__ . '/../../config.php';\n"
            . "header('Location: ' . url('/pages/manufacturers/index.php'));\n");
    }
}

echo 'Manufacturer pages: ' . count($catalog) . " stubs in pages/manufacturers/\n";
