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

// Legacy aliases (with or without trailing slash)
$aliasPath = rtrim($uri, '/') ?: '/';
$legacyAliases = [
    '/privacy-policy' => '/privacy',
    '/terms-and-conditions' => '/terms',
    '/about-us' => '/pages/about',
    '/contact-us' => '/contact',
    '/cookie-policy' => '/privacy',
    '/blog' => '/pages/resources',
    '/news' => '/pages/resources',
    '/pages/keywords/tunstall-nurse-call' => '/pages/nurse-call-systems',
    '/pages/manufacturers/tunstall' => '/pages/nurse-call-systems',
];
if (isset($legacyAliases[$aliasPath])) {
    header('Location: ' . $legacyAliases[$aliasPath], true, 301);
    return true;
}

// Missing service stills (Wave-1 slugs etc.) → fire-alarms fallback
if (preg_match('#^/assets/images/services/([a-z0-9\-]+)\.(jpe?g|png)$#i', $uri, $svcMatch)
    && !str_ends_with(strtolower($svcMatch[1]), '-photo')) {
    $ext = strtolower($svcMatch[2]) === 'png' ? 'png' : 'jpg';
    $wanted = __DIR__ . '/assets/images/services/' . $svcMatch[1] . '.' . $ext;
    $fallback = __DIR__ . '/assets/images/services/fire-alarms.jpg';
    if (!is_file($wanted) && is_file($fallback)) {
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        readfile($fallback);
        return true;
    }
}

// Missing manufacturer logos → service fallback (existing files still served above)
if (preg_match('#^/assets/images/manufacturers/([a-z0-9\-]+)\.(jpe?g|png)$#i', $uri, $mfrMatch)) {
    $ext = strtolower($mfrMatch[2]) === 'png' ? 'png' : 'jpg';
    $wanted = __DIR__ . '/assets/images/manufacturers/' . $mfrMatch[1] . '.' . $ext;
    $fallback = __DIR__ . '/assets/images/services/fire-alarms.jpg';
    $serve = is_file($wanted) ? $wanted : (is_file($fallback) ? $fallback : '');
    if ($serve !== '') {
        header('Content-Type: ' . ($ext === 'png' && $serve === $wanted ? 'image/png' : 'image/jpeg'));
        header('Cache-Control: public, max-age=86400');
        readfile($serve);
        return true;
    }
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

// Compact sitemap — serve disk urlset; never 500 from keyword generation
if (preg_match('#^/sitemap(-[0-9]+)?\.xml$#i', $uri)) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    if (preg_match('#^/sitemap-[0-9]+\.xml$#i', $uri)) {
        header('Location: /sitemap.xml', true, 301);
        return true;
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'icomplypropertyservices.co.uk';
    $host = preg_replace('/:\d+$/', '', (string)$host) ?: 'icomplypropertyservices.co.uk';
    $base = (str_contains($host, 'localhost') || $host === '127.0.0.1'
        ? 'https://icomplypropertyservices.co.uk'
        : ('https://' . $host));
    try {
        require_once __DIR__ . '/includes/sitemap.php';
        echo icomplyServeSitemapXml($base);
    } catch (Throwable $e) {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . '  <url><loc>https://icomplypropertyservices.co.uk/</loc></url>' . "\n"
            . '</urlset>' . "\n";
    }
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
