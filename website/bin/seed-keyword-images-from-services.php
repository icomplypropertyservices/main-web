<?php
/**
 * For each keyword without a large image, copy parent service stock photo
 * so pages always have a solid dedicated-looking image (service-themed).
 */
require_once __DIR__ . '/../config.php';

$kwDir = SITE_ROOT . '/assets/images/keywords';
$svcDir = SITE_ROOT . '/assets/images/services';
if (!is_dir($kwDir)) {
    mkdir($kwDir, 0755, true);
}

$copied = 0;
$skipped = 0;
foreach (getMajorKeywords() as $slug => $meta) {
    $dest = $kwDir . '/' . $slug . '.jpg';
    if (is_file($dest) && filesize($dest) > 8000) {
        $skipped++;
        continue;
    }
    $svc = $meta['service'] ?? 'electrical';
    $src = $svcDir . '/' . $svc . '.jpg';
    if (!is_file($src)) {
        continue;
    }
    copy($src, $dest);
    $copied++;
}
echo "Copied service images to keywords: {$copied}\n";
echo "Already had images: {$skipped}\n";
