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

// Legacy legal aliases (with or without trailing slash)
$aliasPath = rtrim($uri, '/') ?: '/';
if ($aliasPath === '/privacy-policy') {
    header('Location: /privacy', true, 301);
    return true;
}
if ($aliasPath === '/terms-and-conditions') {
    header('Location: /terms', true, 301);
    return true;
}

// Missing *-photo.jpg → working twin (php -S serves assets before PHP otherwise 404s)
if (preg_match('#^/assets/images/services/([a-z0-9\-]+)-photo\.(jpe?g|png)$#i', $uri, $photoMatch)) {
    $ext = strtolower($photoMatch[2]) === 'png' ? 'png' : 'jpg';
    $photo = __DIR__ . '/assets/images/services/' . $photoMatch[1] . '-photo.' . $ext;
    $twin = __DIR__ . '/assets/images/services/' . $photoMatch[1] . '.' . $ext;
    $serve = is_file($photo) ? $photo : (is_file($twin) ? $twin : '');
    if ($serve !== '') {
        header('Content-Type: ' . ($ext === 'png' ? 'image/png' : 'image/jpeg'));
        header('Cache-Control: public, max-age=86400');
        readfile($serve);
        return true;
    }
}

// Compact sitemap even if stale chunk files linger on disk
if (preg_match('#^/sitemap(-[0-9]+)?\.xml$#i', $uri)) {
    require_once __DIR__ . '/includes/sitemap.php';
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    if (preg_match('#^/sitemap-[0-9]+\.xml$#i', $uri)) {
        header('Location: /sitemap.xml', true, 301);
        return true;
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'icomplypropertyservices.co.uk';
    $host = preg_replace('/:\d+$/', '', (string)$host) ?: 'icomplypropertyservices.co.uk';
    $base = (str_contains($host, 'localhost') ? 'https://icomplypropertyservices.co.uk' : ('https://' . $host));
    echo icomplyBuildSitemapXml($base);
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
