#!/usr/bin/env php
<?php
/**
 * Emit CCTV /pages/keywords/<slug>.php stubs from data/cctv-jobs.json.
 *
 * Usage:
 *   php website/bin/generate-cctv-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$expected = cctvJobsExpectedCount();
$slugs = cctvJobsSlugs();
$unique = array_values(array_unique($slugs));

if (count($unique) !== $expected) {
    fwrite(STDERR, 'CCTV lane has ' . count($unique) . " unique slugs; expected {$expected}\n");
    exit(1);
}

$outDir = SITE_ROOT . '/pages/keywords';
if (!is_dir($outDir) && !mkdir($outDir, 0755, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

echo "CCTV keyword page generator\n";
echo "===========================\n";
echo "Target: {$expected}\n\n";

$written = 0;
foreach ($unique as $slug) {
    $path = cctvJobsStubPath($slug);
    file_put_contents($path, cctvJobsStubPhp($slug));
    $written++;
}

echo "cctv_page_count={$written}\n";
echo "Wrote {$written} stubs → {$outDir}/<slug>.php\n";
exit($written === $expected ? 0 : 1);
