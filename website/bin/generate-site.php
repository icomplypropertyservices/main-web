#!/usr/bin/env php
<?php
/**
 * Icomply Property Services - Site Generator
 * 
 * Generates all /{service}/{area}.php SEO landing pages from templates/combo.php
 * 
 * Usage:
 *   php bin/generate-site.php
 *   php bin/generate-site.php --limit=50     (limit areas per service)
 *   php bin/generate-site.php --service=electrical
 */

require_once __DIR__ . '/../config.php';

$options = getopt('', ['limit::', 'service::']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 999;
$onlyService = $options['service'] ?? null;

$allServices = array_merge($services, loadServices());
$areasToUse = array_slice($areas, 0, $limit);

echo "Icomply Site Generator\n";
echo "======================\n\n";

$total = 0;
foreach ($allServices as $sSlug => $sName) {
    if ($onlyService && $sSlug !== $onlyService) continue;

    $dir = __DIR__ . "/../pages/{$sSlug}";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    foreach ($areasToUse as $area) {
        $aSlug = areaSlug($area);
        $file = "{$dir}/{$aSlug}.php";

        $tpl = file_get_contents(__DIR__ . '/../templates/combo.php');
        $tpl = str_replace('{{SERVICE_NAME}}', $sName, $tpl);
        $tpl = str_replace('{{SERVICE_SLUG}}', $sSlug, $tpl);
        $tpl = str_replace('{{AREA}}', $area, $tpl);
        $tpl = str_replace('{{SEO_KEYWORDS}}', getSeoKeywords($sSlug, $area), $tpl);
        $tpl = str_replace('{{AREA_SLUG}}', $aSlug, $tpl);

        file_put_contents($file, $tpl);
        $total++;
    }
    echo "  [{$sSlug}] {$sName} → " . count($areasToUse) . " pages\n";
}

echo "\n✅ Generated {$total} pages total.\n";
echo "Run with --limit=150 for full production site.\n";
?>