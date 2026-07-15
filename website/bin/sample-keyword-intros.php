<?php
require_once __DIR__ . '/../config.php';
$k = getMajorKeywords();
$samples = ['eicr', 'eicr-report', 'fire-alarm-installation', 'landlord-gas-safety-certificate', 'cctv-installation', 'paxton-net2-install'];
foreach ($samples as $s) {
    $intro = $k[$s]['intro'] ?? '(missing)';
    echo "=== $s ===\n" . substr($intro, 0, 180) . "...\n\n";
}
echo "Total with intro: " . count(array_filter($k, fn($m) => !empty($m['intro']))) . "\n";
