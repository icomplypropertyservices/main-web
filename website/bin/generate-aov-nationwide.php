#!/usr/bin/env php
<?php
/**
 * AOV × nationwide areas (non-prod).
 *
 * Calls the existing generators:
 *   php bin/generate-site.php --service=aov-air-handling
 *   php bin/generate-keyword-area-pages.php --only=<every AOV keyword>
 *
 * Writes PHP stubs under website/pages/ (gitignored). The Netlify static
 * export renders the same URLs into dist/. Does not edit sitemap.xml,
 * does not deploy, and does not promote a live host.
 *
 * Usage (repo root): php website/bin/generate-aov-nationwide.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$php = PHP_BINARY;
$fail = 0;

$run = static function (array $args) use ($php, &$fail): void {
    $cmd = array_merge([$php], $args);
    $line = implode(' ', array_map('escapeshellarg', $cmd));
    echo "→ {$line}\n";
    passthru($line, $code);
    if ($code !== 0) {
        $fail++;
        fwrite(STDERR, "generator exit {$code}\n");
    }
};

$services = getAovNationwideServices();
$keywords = getAovMatrixKeywordSlugs();
$areas = getAreas();

echo "AOV nationwide generator (non-prod, no sitemap promote)\n";
echo "Services: " . implode(', ', $services) . "\n";
echo "Keywords: " . count($keywords) . " × areas: " . count($areas) . "\n";
echo "Service×area pages: " . (count($services) * count($areas)) . "\n";
echo "Keyword×area pages: " . (count($keywords) * count($areas)) . "\n";
echo str_repeat('=', 56) . "\n";

foreach ($services as $svc) {
    $run([__DIR__ . '/generate-site.php', '--service=' . $svc]);
}

if ($keywords) {
    $run([__DIR__ . '/generate-keyword-area-pages.php', '--only=' . implode(',', $keywords)]);
}

$sample = SITE_ROOT . '/pages/aov-air-handling/stockport.php';
$kwSample = SITE_ROOT . '/pages/keywords/aov-installation/manchester.php';
echo str_repeat('=', 56) . "\n";
echo is_file($sample) ? "OK stub {$sample}\n" : "MISSING stub {$sample}\n";
echo is_file($kwSample) ? "OK stub {$kwSample}\n" : "MISSING stub {$kwSample}\n";
if (!is_file($sample) || !is_file($kwSample)) {
    $fail++;
}

exit($fail > 0 ? 1 : 0);
