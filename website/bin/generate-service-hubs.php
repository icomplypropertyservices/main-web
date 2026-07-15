#!/usr/bin/env php
<?php
/**
 * Thin stubs for /pages/services/{slug}.php + index.
 */
require_once __DIR__ . '/../config.php';

$dir = SITE_ROOT . '/pages/services';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$services = getServices();
foreach ($services as $slug => $name) {
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-service-hubs.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderServiceHubPage({$slugExport});\n";
    file_put_contents("{$dir}/{$slug}.php", $stub);
    echo "  services/{$slug}.php\n";
}

// Preserve handcrafted overhaul index if present
$indexPath = "{$dir}/index.php";
if (!is_file($indexPath) || strpos((string)file_get_contents($indexPath), 'SERVICES_INDEX') === false) {
    // Only create a minimal stub if missing — do not overwrite overhauled index.php
    if (!is_file($indexPath)) {
        file_put_contents($indexPath, "<?php\nrequire_once __DIR__ . '/../../config.php';\n"
            . "header('Location: ' . url('/pages/services/index.php'));\n");
    }
    echo "  services/index.php (preserved / minimal)\n";
} else {
    echo "  services/index.php (handcrafted, skipped)\n";
}
echo "Service hubs: " . count($services) . " + index\n";
