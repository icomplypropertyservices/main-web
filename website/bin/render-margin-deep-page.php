<?php
/**
 * Render one margin DEEP page to stdout (used by bin/check-margin-deep.php).
 * Usage: php bin/render-margin-deep-page.php hub {slug}
 *        php bin/render-margin-deep-page.php town {slug} {town}
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}
$root = dirname(__DIR__);
if (!getenv('SITE_URL')) {
    putenv('SITE_URL=https://icomplypropertyservices.co.uk');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once $root . '/config.php';
require_once $root . '/includes/router.php';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';

$kind = (string)($argv[1] ?? '');
$slug = (string)($argv[2] ?? '');
$town = (string)($argv[3] ?? '');
if ($slug === '' || !in_array($kind, ['hub', 'town'], true) || ($kind === 'town' && $town === '')) {
    fwrite(STDERR, "usage: hub {slug} | town {slug} {town}\n");
    exit(2);
}
ob_start();
routerDispatchVirtual('/pages/keywords/' . $slug . ($kind === 'town' ? '/' . $town : ''));
echo (string)ob_get_clean();
