<?php
/**
 * Vercel serverless entry (must live at /api/index.php).
 * Boots the PHP site from /website.
 */
declare(strict_types=1);

// === FORCE CUSTOM DOMAIN (fixes subpages showing Vercel URL) ===
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$base_url = 'https://icomplypropertyservices.co.uk';

// ===============================================================

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'website';
if (!is_dir($root)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Website root not found.';
    exit;
}

chdir($root);

$uri = urldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
$uri = $uri === '' ? '/' : $uri;

// Block internal / sensitive paths
if (preg_match('#^/(bin|templates|data|includes|api)(/|$)#i', $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not found';
    exit;
}

// Home
if ($uri === '/' || $uri === '/index.php' || $uri === '/index') {
    require $root . DIRECTORY_SEPARATOR . 'index.php';
    exit;
}

// Resolve PHP script under website/
$candidates = [];
if (str_ends_with(strtolower($uri), '.php')) {
    $candidates[] = $root . $uri;
} else {
    $candidates[] = $root . $uri . '.php';
    $candidates[] = $root . $uri . DIRECTORY_SEPARATOR . 'index.php';
    $candidates[] = $root . $uri;
}

$rootReal = realpath($root) ?: $root;
foreach ($candidates as $file) {
    $real = realpath($file);
    if ($real === false || !is_file($real)) {
        continue;
    }
    // Path traversal guard
    if (!str_starts_with($real, $rootReal)) {
        continue;
    }
    if (!str_ends_with(strtolower($real), '.php')) {
        continue;
    }
    require $real;
    exit;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="en-GB"><head><meta charset="UTF-8"><title>Page not found | Icomply</title>';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css"></head>';
echo '<body class="bg-zinc-50 flex items-center justify-center min-h-screen"><div class="text-center p-10">';
echo '<h1 class="text-4xl font-bold mb-4">Page not found</h1>';
echo '<p class="text-zinc-600 mb-6">That URL is not on this site.</p>';
echo '<a class="px-6 py-3 bg-[#0a2540] text-white rounded-xl" href="/">Back to home</a>';
echo '</div></body></html>';
