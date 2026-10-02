#!/usr/bin/env php
<?php
/**
 * Optional local stubs for the 130 plumbing job types.
 * website/pages/keywords/*.php is gitignored — the live route is virtual
 * via jobTypesApplyPlumbing() and renderKeywordPage(). Do not commit stubs.
 *
 * Usage: php website/bin/generate-plumbing-job-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$jobs = jobTypesPlumbingJobs();
$expected = jobTypesPlumbingExpected();
if (count($jobs) !== $expected) {
    fwrite(STDERR, 'Plumbing catalogue has ' . count($jobs) . " jobs, expected {$expected}\n");
    exit(1);
}

$outDir = jobTypesPlumbingStubDir();
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$written = 0;
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '' || ($job['family'] ?? '') !== 'plumbing') {
        fwrite(STDERR, "Skip invalid job\n");
        exit(1);
    }
    $path = $outDir . '/' . $slug . '.php';
    file_put_contents($path, jobTypesPlumbingStubContents($slug));
    $written++;
}

echo "Plumbing job-type stubs written={$written} dir={$outDir}\n";
exit($written === $expected ? 0 : 1);
