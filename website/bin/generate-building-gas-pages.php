#!/usr/bin/env php
<?php
/**
 * Thin stubs for Building (247) + Gas (223) job hubs at /pages/keywords/<slug>.
 *
 * Usage:
 *   php website/bin/generate-building-gas-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$outDir = SITE_ROOT . '/pages/keywords';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$jobs = function_exists('jobTypesBuildingGasJobs') ? jobTypesBuildingGasJobs() : [];
$building = function_exists('jobTypesBuildingJobs') ? jobTypesBuildingJobs() : [];
$gas = function_exists('jobTypesGasJobs') ? jobTypesGasJobs() : [];

echo "iComply Building + Gas keyword generator\n";
echo "========================================\n";
echo 'Building: ' . count($building) . "\n";
echo 'Gas: ' . count($gas) . "\n";
echo 'Total: ' . count($jobs) . "\n\n";

$total = 0;
foreach ($jobs as $job) {
    if (!is_array($job)) {
        continue;
    }
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '' || $slug === 'index') {
        continue;
    }
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-building-gas-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
    file_put_contents("{$outDir}/{$slug}.php", $stub);
    $total++;
}

echo "Generated {$total} keyword stubs under pages/keywords/.\n";
echo 'building_count=' . count($building) . ' gas_count=' . count($gas) . ' total=' . $total . PHP_EOL;
exit($total === 470 ? 0 : 1);
