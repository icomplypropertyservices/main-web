<?php
// Usage: php patch-shop-hubs.php <shop dir>. Adds breadcrumbs, call/WhatsApp CTA, FAQ + schema to /shop hubs.
require __DIR__ . '/../includes/shop-hub-extras.php';
$dir = rtrim($argv[1] ?? __DIR__ . '/../shop', '/');
foreach (['index' => '/index.html', 'fire' => '/fire/index.html', 'electrical' => '/electrical/index.html', 'security' => '/security/index.html', 'gas' => '/gas/index.html'] as $k => $rel) {
    if (is_file($dir . $rel)) { file_put_contents($dir . $rel, icomplyShopHubPatchHtml(file_get_contents($dir . $rel), $k)); echo "patched $rel\n"; }
}
