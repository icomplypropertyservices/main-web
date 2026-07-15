<?php
/**
 * Vercel serverless entry — clean extensionless URLs + virtual routes.
 */
declare(strict_types=1);

// Prefer real host when present (custom domain / preview)
$host = $_SERVER['HTTP_HOST'] ?? 'icomplypropertyservices.co.uk';
if (str_contains($host, 'localhost') || $host === '') {
    $host = 'icomplypropertyservices.co.uk';
}
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$base_url = ($https ? 'https' : 'https') . '://' . preg_replace('/:\d+$/', '', $host);

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'website';
if (!is_dir($root)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Website root not found.';
    exit;
}

chdir($root);

// Align SITE_URL for url() helper when config loads
if (!defined('SITE_URL_OVERRIDE')) {
    // config may define SITE_URL; we set env-style before require via local merge in render
}

// Load router (defines SITE_ROOT via config)
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'router.php';

// Override SITE_URL for production clean links if still localhost in config
// (constants can't be redefined — set only if config.local isn't used; use output base tag)

$uri = routerRequestPath();
// On Vercel, SITE_URL path may be empty — routerRequestPath already normalizes

// Block internals (admin allowed)
if (preg_match('#^/(bin|templates|data|includes)(/|$)#i', $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not found';
    exit;
}

ob_start();
$handled = false;

// Home
if ($uri === '/' || $uri === '/index') {
    require $root . DIRECTORY_SEPARATOR . 'index.php';
    $handled = true;
} elseif (routerDispatchVirtual($uri)) {
    $handled = true;
} elseif (routerTryFile($uri)) {
    $handled = true;
} elseif (preg_match('#^/([a-z0-9\-]+)$#', $uri, $m)) {
    if (routerTryFile('/' . $m[1]) || routerTryFile('/pages/' . $m[1])) {
        $handled = true;
    }
}

if (!$handled) {
    http_response_code(404);
    if (is_file($root . '/404.php')) {
        require $root . '/404.php';
    } else {
        echo 'Not found';
    }
}

$output = ob_get_clean();
if (stripos($output, '<head') !== false && stripos($output, '<base ') === false) {
    $output = preg_replace('/(<head[^>]*>)/i', '$1<base href="' . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . '/">', $output, 1);
}
echo $output;
