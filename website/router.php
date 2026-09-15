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

// Legacy legal aliases
if ($uri === '/privacy-policy') {
    header('Location: /privacy', true, 301);
    return true;
}
if ($uri === '/terms-and-conditions') {
    header('Location: /terms', true, 301);
    return true;
}

// Serve real files as-is (including manifest.json, images, xml)
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

// Virtual routes (area hubs, manufacturer brands, keyword × area, service × area)
require_once __DIR__ . '/includes/router.php';
routerHandleRequest();
return true;
