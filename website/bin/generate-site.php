#!/usr/bin/env php
<?php
/**
 * Writes thin stubs for /pages/{service}/{area}.php
 * Content is rendered at request time from templates/services/{slug}.php (or combo.php).
 *
 * Usage:
 *   php bin/generate-site.php
 *   php bin/generate-site.php --limit=150
 *   php bin/generate-site.php --service=fire-alarms
 */
require_once __DIR__ . '/../config.php';

$options = getopt('', ['limit::', 'service::']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 0;
$onlyService = $options['service'] ?? null;

$allServices = getServices();
$areasToUse = getAreas();
if ($limit > 0) {
    $areasToUse = array_slice($areasToUse, 0, $limit);
}

echo "Icomply Site Generator (thin stubs → runtime render)\n";
echo "====================================================\n\n";

$total = 0;
foreach ($allServices as $sSlug => $sName) {
    if ($onlyService && $sSlug !== $onlyService) {
        continue;
    }

    $dir = SITE_ROOT . "/pages/{$sSlug}";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Remove obsolete files for areas no longer in the list (optional: keep for safety)
    foreach ($areasToUse as $area) {
        $aSlug = areaSlug($area);
        $file = "{$dir}/{$aSlug}.php";
        $areaExport = var_export($area, true);
        $slugExport = var_export($sSlug, true);
        $stub = "<?php\n"
            . "/** AUTO-GENERATED stub — php bin/generate-site.php */\n"
            . "require_once __DIR__ . '/../../includes/render.php';\n"
            . "renderServiceAreaPage({$slugExport}, {$areaExport});\n";
        file_put_contents($file, $stub);
        $total++;
    }

    $tpl = is_file(SITE_ROOT . "/templates/services/{$sSlug}.php") ? "services/{$sSlug}.php" : 'combo.php';
    echo "  [{$sSlug}] {$sName} → " . count($areasToUse) . " stubs | template: {$tpl}\n";
}

echo "\nGenerated {$total} service×area stubs.\n";
