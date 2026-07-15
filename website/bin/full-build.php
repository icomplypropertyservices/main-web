#!/usr/bin/env php
<?php
/**
 * Run all generators and write build manifest.
 * Usage: php bin/full-build.php
 */
require_once __DIR__ . '/../includes/build-status.php';

$php = PHP_BINARY ?: 'php';
if (stripos(PHP_OS, 'WIN') === 0 && is_file('C:\\xampp\\php\\php.exe')) {
    $php = 'C:\\xampp\\php\\php.exe';
}

$jobs = [
    'combo' => 'generate-site.php',
    'keywords' => 'generate-keyword-pages.php',
    'keyword-areas' => 'generate-keyword-area-pages.php',
    'areas' => 'generate-area-hubs.php',
    'services' => 'generate-service-hubs.php',
    'manufacturers' => 'generate-manufacturer-pages.php',
    'sitemap' => 'generate-sitemap.php',
];

echo "Icomply FULL BUILD\n";
echo str_repeat('=', 40) . "\n";

$results = [];
$allOk = true;
$logs = [];

foreach ($jobs as $key => $script) {
    $path = __DIR__ . '/' . $script;
    echo "\n>> {$script}\n";
    if (!is_file($path)) {
        echo "   MISSING\n";
        $results[$key] = ['ok' => false, 'exit' => 127, 'out' => 'missing'];
        $allOk = false;
        continue;
    }
    $cmd = escapeshellarg($php) . ' ' . escapeshellarg($path) . ' 2>&1';
    $out = [];
    $code = 0;
    exec($cmd, $out, $code);
    $text = implode("\n", $out);
    echo $text . "\n";
    $ok = ($code === 0);
    $results[$key] = ['ok' => $ok, 'exit' => $code, 'out' => trim(implode(' | ', array_slice($out, -5)))];
    $logs[] = "{$script}: exit {$code}";
    if (!$ok) {
        $allOk = false;
    }
}

recordBuildComplete($results, implode('; ', $logs));

$status = getBuildStatus();
echo "\n" . str_repeat('=', 40) . "\n";
echo "BUILD STATUS\n";
echo "  combo:     {$status['actual']['combo']}/{$status['expected']['combo']}\n";
echo "  keywords:  {$status['actual']['keywords']}/{$status['expected']['keywords']}\n";
echo "  areas:     {$status['actual']['area_hubs']}/{$status['expected']['area_hubs']}\n";
echo "  services:  {$status['actual']['service_hubs']}/{$status['expected']['service_hubs']}\n";
echo "  sitemap:   {$status['actual']['sitemap_urls']} URLs\n";
echo "  pages PHP: {$status['actual']['total_php_pages']}\n";
echo "  needs_regen: " . ($status['needs_regen'] ? 'YES' : 'NO') . "\n";
if ($status['reasons']) {
    foreach ($status['reasons'] as $r) {
        echo "   - {$r}\n";
    }
}
echo $allOk && !$status['needs_regen'] ? "\nFULL BUILD OK\n" : "\nFULL BUILD ISSUES — see above\n";
exit($allOk && !$status['needs_regen'] ? 0 : 1);
