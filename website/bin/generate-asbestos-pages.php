#!/usr/bin/env php
<?php
/**
 * Emit Asbestos survey/awareness keyword stubs from asbestos-jobs.json.
 *
 * Usage: php website/bin/generate-asbestos-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$expected = asbestosJobsExpectedCount();
$slugs = asbestosJobsSlugs();
$unique = array_values(array_unique($slugs));

if (count($unique) !== $expected) {
    fwrite(STDERR, 'Asbestos lane has ' . count($unique) . " unique slugs; expected {$expected}\n");
    exit(1);
}

$outDir = SITE_ROOT . '/pages/keywords';
if (!is_dir($outDir) && !mkdir($outDir, 0755, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$written = 0;
foreach ($unique as $slug) {
    $path = asbestosJobsStubPath($slug);
    if (is_file($path) && basename($path) === 'index.php') {
        fwrite(STDERR, "Refusing to overwrite {$path}\n");
        exit(1);
    }
    file_put_contents($path, asbestosJobsStubPhp($slug));
    $written++;
}

echo "asbestos_page_count={$written}\n";
echo "Wrote {$written} stubs → {$outDir}/<slug>.php\n";
exit($written === $expected ? 0 : 1);
