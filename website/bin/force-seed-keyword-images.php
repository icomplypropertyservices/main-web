<?php
require_once __DIR__ . '/../config.php';
$kwDir = SITE_ROOT . '/assets/images/keywords';
$svcDir = SITE_ROOT . '/assets/images/services';
$n = 0;
foreach (getMajorKeywords() as $slug => $meta) {
    $svc = $meta['service'] ?? 'electrical';
    $src = $svcDir . '/' . $svc . '.jpg';
    if (!is_file($src)) continue;
    copy($src, $kwDir . '/' . $slug . '.jpg');
    $n++;
}
echo "Force-refreshed keyword images: $n\n";
