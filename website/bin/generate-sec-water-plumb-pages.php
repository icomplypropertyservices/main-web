#!/usr/bin/env php
<?php
/**
 * Write /pages/keywords/<slug>.php stubs for Security + Water + Plumbing (424).
 *
 * Usage: php website/bin/generate-sec-water-plumb-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$expected = jobTypesSecWaterPlumbExpected();
$jobs = jobTypesSecWaterPlumbJobs();
if (count($jobs) !== $expected['total']) {
    fwrite(STDERR, 'Family catalogue has ' . count($jobs) . ' jobs, expected ' . $expected['total'] . "\n");
    exit(1);
}

$outDir = jobTypesSecWaterPlumbStubDir();
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$by = ['security' => 0, 'water' => 0, 'plumbing' => 0];
$written = 0;
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $fam = (string)($job['family'] ?? '');
    if ($slug === '' || !isset($by[$fam])) {
        fwrite(STDERR, "Skip invalid job\n");
        continue;
    }
    $path = $outDir . '/' . $slug . '.php';
    file_put_contents($path, jobTypesSecWaterPlumbStubContents($slug));
    $by[$fam]++;
    $written++;
}

echo "Icomply Security + Water + Plumbing page generator\n";
echo "=================================================\n";
echo "security={$by['security']} water={$by['water']} plumbing={$by['plumbing']} total={$written}\n";
echo "dir={$outDir}\n";

foreach (['security' => 164, 'water' => 130, 'plumbing' => 130] as $fam => $need) {
    if ($by[$fam] !== $need) {
        fwrite(STDERR, "FAIL {$fam} wrote {$by[$fam]} !== {$need}\n");
        exit(1);
    }
}
if ($written !== 424) {
    fwrite(STDERR, "FAIL wrote {$written} !== 424\n");
    exit(1);
}
echo "Generated 424 keyword stubs.\n";
exit(0);
