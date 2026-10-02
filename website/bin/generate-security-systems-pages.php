#!/usr/bin/env php
<?php
/**
 * Thin stubs for the security-systems lane.
 * Stubs are gitignored (website/pages/keywords/*.php); the router also
 * renders these slugs without a file.
 *
 * Usage: php website/bin/generate-security-systems-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$jobs = securitySystemsJobs();
if (!$jobs) {
    fwrite(STDERR, "Catalogue empty — run build-security-systems-jobs.php first\n");
    exit(1);
}

$dir = securitySystemsOutputDir();
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    fwrite(STDERR, "Cannot mkdir {$dir}\n");
    exit(1);
}

$n = 0;
foreach (securitySystemsSlugs() as $slug) {
    file_put_contents(securitySystemsStubPath($slug), securitySystemsStubContents($slug));
    $n++;
}

echo "security_systems_stubs={$n}\n";
exit(0);
