<?php
/**
 * PHP built-in server router — cleaner paths + security.
 * php -S localhost:8000 router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Redirect legacy .php URLs to their clean extensionless route when available.
if ($uri !== '/' && preg_match('#\.php$#i', $uri)) {
    $extensionless = preg_replace('#\.php$#i', '', $uri);
    $extensionlessFile = __DIR__ . $extensionless;
    $extensionlessPhpFile = $extensionlessFile . '.php';
    $extensionlessDirIndex = $extensionlessFile . '/index.php';
    $target = $extensionless === '/index' ? '/' : $extensionless;

    if ($target !== '/' || $uri === '/index.php') {
        if (is_file($extensionlessPhpFile) || is_file($extensionlessDirIndex) || $uri === '/index.php') {
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            if ($qs !== '') {
                $target .= '?' . $qs;
            }
            http_response_code(301);
            header('Location: ' . $target, true, 301);
            return true;
        }
    }
}

// Serve real files as-is
if ($uri !== '/' && is_file($file)) {
    return false;
}

// Support extensionless routes such as /contact or /pages/services/gas-systems
if ($uri !== '/' && $uri !== '') {
    $phpFile = $file . '.php';
    if (is_file($phpFile)) {
        require $phpFile;
        return true;
    }

    $dirIndex = $file . '/index.php';
    if (is_file($dirIndex)) {
        require $dirIndex;
        return true;
    }
}

// Block sensitive paths
foreach (['/admin/', '/bin/', '/templates/', '/data/', '/includes/'] as $blocked) {
    if (strpos($uri, $blocked) === 0 && !preg_match('#^/admin/(index|generate)\.php#', $uri)) {
        // allow admin PHP entry points only
    }
}
if (preg_match('#^/(bin|templates|data|includes)(/|$)#', $uri)) {
    http_response_code(404);
    echo 'Not found';
    return true;
}

// Map pretty-ish paths if needed later; default front controller to index
if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
    return true;
}

// Fallback: 404 for unknown
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="en-GB"><head><title>Page not found | Icomply</title><base href="/">';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css"></head>';
echo '<body class="bg-zinc-50 flex items-center justify-center min-h-screen"><div class="text-center p-10">';
echo '<h1 class="text-4xl font-bold mb-4">Page not found</h1>';
echo '<p class="text-zinc-600 mb-6">That URL is not on this site.</p>';
echo '<a class="px-6 py-3 bg-[#0a2540] text-white rounded-xl" href="/">Back to home</a>';
echo '</div></body></html>';
return true;
