#!/usr/bin/env php
<?php
/**
 * Writes thin stubs for /pages/{service}/{area}.php
 * Content is rendered at request time from templates/services/{slug}.php (or combo.php).
 *
 * Usage:
 *   php bin/generate-site.php
 *   php bin/generate-site.php --service=electrical
 *
 * Default scope is the Jack pilot: non-fire services × Manchester and Burnley.
 * Fire × area nationwide is not generated here.
 */
require_once __DIR__ . '/../config.php';

$options = getopt('', ['service::']);
$onlyService = $options['service'] ?? null;

$allServices = function_exists('getJackPilotServices') ? getJackPilotServices() : getServices();
$areasToUse = function_exists('getJackPilotAreaNames') ? getJackPilotAreaNames() : ['Manchester', 'Burnley'];

echo "Icomply Site Generator (thin stubs → runtime render)\n";
echo "====================================================\n\n";

$total = 0;
foreach ($allServices as $sSlug => $sName) {
    if ($onlyService && $sSlug !== $onlyService) {
        continue;
    }
    if (function_exists('isFireServiceSlug') && isFireServiceSlug($sSlug)) {
        echo "  [{$sSlug}] skipped — fire × area is not this pilot\n";
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
