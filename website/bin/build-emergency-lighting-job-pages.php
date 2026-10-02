#!/usr/bin/env php
<?php
/**
 * Build the Emergency Lighting job catalogue (exactly 62 slugs) and emit thin stubs.
 *
 * Usage: php website/bin/build-emergency-lighting-job-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$expected = emergencyLightingJobTypesExpectedCount();
$jobs = emergencyLightingCollectJobs();
if (count($jobs) !== $expected) {
    fwrite(STDERR, 'Built ' . count($jobs) . ' emergency lighting jobs, expected ' . $expected . "\n");
    exit(1);
}

$payload = [
    'count' => $expected,
    'category' => 'Emergency Lighting',
    'note' => 'Service emergency-lighting keywords, excluding near-me doorway slugs. Enquire / POA only.',
    'jobs' => $jobs,
];
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "JSON encode failed\n");
    exit(1);
}
$file = emergencyLightingJobTypesFile();
file_put_contents($file, $json . "\n");
emergencyLightingJobTypesReset();
loadJsonData('__clear__');

$outDir = emergencyLightingJobTypesOutputDir();
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$written = 0;
foreach ($jobs as $job) {
    $slug = $job['slug'];
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED Emergency Lighting job stub — php website/bin/build-emergency-lighting-job-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
    file_put_contents(emergencyLightingJobTypesStubPath($slug), $stub);
    $written++;
}

echo 'Wrote ' . count($jobs) . " emergency lighting jobs → {$file}\n";
echo "Wrote {$written} stubs → {$outDir}/<slug>.php\n";
echo 'emergency_lighting_page_count=' . count($jobs) . ' expected=' . $expected . "\n";
exit(0);
