#!/usr/bin/env php
<?php
/**
 * Write thin stubs for every commercial compliance job.
 *
 * Usage: php website/bin/build-commercial-job-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/commercial-jobs.php';

$jobs = commercialJobs();
$expected = commercialJobsExpectedCount();
if ($expected < 1 || count($jobs) !== $expected) {
    fwrite(STDERR, 'Catalogue count ' . count($jobs) . " does not match declared {$expected}\n");
    exit(1);
}

$dir = commercialJobsStubDir();
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    fwrite(STDERR, "Cannot create {$dir}\n");
    exit(1);
}

$written = 0;
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '') {
        fwrite(STDERR, "Job missing slug\n");
        exit(1);
    }
    $export = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED commercial job stub — php website/bin/build-commercial-job-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/commercial-jobs.php';\n"
        . "renderCommercialJobPage({$export});\n";
    file_put_contents(commercialJobStubPath($slug), $stub);
    $written++;
}

echo "Wrote {$written} commercial job stubs → {$dir}\n";
exit(0);
