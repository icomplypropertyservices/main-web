<?php
/**
 * Front controller — clean extensionless URLs + virtual routes.
 * Host-agnostic: Vercel interim + Netlify static-export source.
 * Deploy marker: 2026-09-11-seo-p0-netlify
 */
declare(strict_types=1);

// Prefer real host when present (custom domain / preview / Netlify)
$host = $_SERVER['HTTP_HOST'] ?? 'icomplypropertyservices.co.uk';
$host = preg_replace('/:\d+$/', '', (string)$host) ?: 'icomplypropertyservices.co.uk';
if (str_contains($host, 'localhost') || $host === '') {
    $host = 'icomplypropertyservices.co.uk';
}
// Canonical public host: apex (www redirects at edge)
if (strcasecmp($host, 'www.icomplypropertyservices.co.uk') === 0) {
    $host = 'icomplypropertyservices.co.uk';
}
$base_url = 'https://' . $host;

// Force SITE_URL before config loads (constants)
putenv('SITE_URL=' . $base_url);
$_ENV['SITE_URL'] = $base_url;
$_SERVER['SITE_URL'] = $base_url;
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'website';
if (!is_dir($root)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Website root not found.';
    exit;
}

chdir($root);

require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'router.php';
require_once $root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'sitemap.php';

$uri = routerRequestPath();

/** Rewrite apex/www hosts in sitemap/robots to the live request host. */
function seoRewritePublicHost(string $body, string $baseUrl): string {
    $body = str_replace(
        [
            'https://www.icomplypropertyservices.co.uk',
            'http://www.icomplypropertyservices.co.uk',
            'https://icomplypropertyservices.co.uk',
            'http://icomplypropertyservices.co.uk',
        ],
        $baseUrl,
        $body
    );
    return $body;
}

/** Compact accurate sitemap (shared builder — never 500, never keyword×area junk). */
function seoCompactSitemap(string $baseUrl): string {
    if (function_exists('icomplyBuildSitemapXml')) {
        return icomplyBuildSitemapXml($baseUrl);
    }
    $base = rtrim($baseUrl, '/');
    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
        . '  <url><loc>' . htmlspecialchars($base . '/', ENT_XML1) . '</loc></url>' . "\n"
        . '  <url><loc>' . htmlspecialchars($base . '/pages/areas', ENT_XML1) . '</loc></url>' . "\n"
        . '  <url><loc>' . htmlspecialchars($base . '/pages/manufacturers', ENT_XML1) . '</loc></url>' . "\n"
        . '  <url><loc>' . htmlspecialchars($base . '/pages/resources', ENT_XML1) . '</loc></url>' . "\n"
        . '</urlset>' . "\n";
}

/** Drop shop/products locs from served sitemaps (trade hubs stay on /shop and /products). */
function seoStripShopProductUrls(string $xml): string {
    $xml = preg_replace(
        '#<url>\s*<loc>[^<]*/(?:shop|products)(?:/[^<]*)?</loc>.*?</url>\s*#is',
        '',
        $xml
    ) ?? $xml;
    return $xml;
}

// Block internals (admin allowed)
if (preg_match('#^/(bin|templates|data|includes)(/|$)#i', $uri)) {
    if (preg_match('#^/admin#i', $uri)) {
        // fall through to file router
    } else {
        if (preg_match('#^/bin/cron-seo\.php$#i', $uri) || $uri === '/bin/cron-seo') {
            require $root . '/bin/cron-seo.php';
            exit;
        }
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not found';
        exit;
    }
}

// Legacy aliases (also in netlify.toml / _redirects)
$aliasPath = rtrim($uri, '/') ?: '/';
$legacyAliases = [
    '/privacy-policy' => '/privacy',
    '/terms-and-conditions' => '/terms',
    '/about-us' => '/pages/about',
    '/contact-us' => '/contact',
    '/cookie-policy' => '/privacy',
    '/pages/privacy' => '/privacy',
    '/pages/terms' => '/terms',
    '/cookies' => '/privacy',
    '/thanks' => '/thank-you',
    '/thankyou' => '/thank-you',
    '/pages/residential' => '/pages/landlords',
    '/pages/keywords/air-source-heat-pumps-service-agreement' => '/pages/services/heating',
    '/pages/keywords/bs-5306-extinguisher-service-cost' => '/pages/services/fire-extinguishers',
    '/pages/keywords/loft-conversion-fixed-price-package' => '/pages/services/loft-conversions',
    '/blog' => '/pages/resources',
    '/news' => '/pages/resources',
    '/group' => '/',
];
if (isset($legacyAliases[$aliasPath])) {
    header('Location: ' . $base_url . $legacyAliases[$aliasPath], true, 301);
    exit;
}

// Missing service stills → fire-alarms fallback
if (preg_match('#^/assets/images/services/([a-z0-9\-]+)\.(jpe?g|png)$#i', $uri, $svcMatch)
    && !str_ends_with(strtolower($svcMatch[1]), '-photo')) {
    $ext = strtolower($svcMatch[2]) === 'png' ? 'png' : 'jpg';
    $wanted = $root . '/assets/images/services/' . $svcMatch[1] . '.' . $ext;
    $fallback = $root . '/assets/images/services/fire-alarms.jpg';
    if (!is_file($wanted) && is_file($fallback)) {
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        readfile($fallback);
        exit;
    }
}

// Missing manufacturer logos → working service fallback
if (preg_match('#^/assets/images/manufacturers/([a-z0-9\-]+)\.(jpe?g|png)$#i', $uri, $mfrMatch)) {
    $ext = strtolower($mfrMatch[2]) === 'png' ? 'png' : 'jpg';
    $wanted = $root . '/assets/images/manufacturers/' . $mfrMatch[1] . '.' . $ext;
    $fallback = $root . '/assets/images/services/fire-alarms.jpg';
    $serve = is_file($wanted) ? $wanted : (is_file($fallback) ? $fallback : '');
    if ($serve !== '') {
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        readfile($serve);
        exit;
    }
}

// Missing *-photo.jpg → working twin (Netlify static publish also has _redirects)
if (preg_match('#^/assets/images/services/([a-z0-9\-]+)-photo\.(jpe?g|png)$#i', $uri, $photoMatch)) {
    $ext = strtolower($photoMatch[2]) === 'png' ? 'png' : 'jpg';
    $photo = $root . '/assets/images/services/' . $photoMatch[1] . '-photo.' . $ext;
    $twin = $root . '/assets/images/services/' . $photoMatch[1] . '.' . $ext;
    $serve = is_file($photo) ? $photo : (is_file($twin) ? $twin : '');
    if ($serve !== '') {
        header('Content-Type: ' . ($ext === 'png' ? 'image/png' : 'image/jpeg'));
        header('Cache-Control: public, max-age=86400');
        readfile($serve);
        exit;
    }
}

// Web app manifest — PHP front controller would otherwise 404 this static file
if (strcasecmp($uri, '/manifest.json') === 0) {
    $manifestCandidates = [
        $root . DIRECTORY_SEPARATOR . 'manifest.json',
        dirname($root) . DIRECTORY_SEPARATOR . 'manifest.json',
    ];
    foreach ($manifestCandidates as $manifestFile) {
        if (is_file($manifestFile)) {
            header('Content-Type: application/manifest+json; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
            echo (string)file_get_contents($manifestFile);
            exit;
        }
    }
}

// Root favicon when requested as /favicon.ico
if (strcasecmp($uri, '/favicon.ico') === 0) {
    $icoCandidates = [
        $root . DIRECTORY_SEPARATOR . 'favicon.ico',
        $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'favicon.ico',
    ];
    foreach ($icoCandidates as $ico) {
        if (is_file($ico)) {
            header('Content-Type: image/x-icon');
            header('Cache-Control: public, max-age=86400');
            readfile($ico);
            exit;
        }
    }
}

// robots.txt — host-aware Sitemap line
if (strcasecmp($uri, '/robots.txt') === 0) {
    $file = $root . DIRECTORY_SEPARATOR . 'robots.txt';
    if (is_file($file)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo seoRewritePublicHost((string)file_get_contents($file), $base_url);
        exit;
    }
}

// Sitemaps — always 200 urlset. Prefer on-disk file; never rebuild keyword catalogues.
if (preg_match('#^/sitemap(-[0-9]+)?\.xml$#i', $uri)) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    try {
        if (preg_match('#^/sitemap-[0-9]+\.xml$#i', $uri)) {
            header('Location: ' . $base_url . '/sitemap.xml', true, 301);
            exit;
        }
        echo function_exists('icomplyServeSitemapXml')
            ? icomplyServeSitemapXml($base_url)
            : seoRewritePublicHost(seoCompactSitemap($base_url), $base_url);
        exit;
    } catch (Throwable $e) {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . '  <url><loc>' . htmlspecialchars($base_url . '/', ENT_XML1) . '</loc></url>' . "\n"
            . '  <url><loc>' . htmlspecialchars($base_url . '/pages/areas', ENT_XML1) . '</loc></url>' . "\n"
            . '  <url><loc>' . htmlspecialchars($base_url . '/pages/resources', ENT_XML1) . '</loc></url>' . "\n"
            . '</urlset>' . "\n";
        exit;
    }
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

$output = (string)ob_get_clean();

if ($output !== '') {
    $output = str_replace(
        [
            'http://localhost/icomply',
            'https://localhost/icomply',
            'http://localhost',
            'https://localhost',
        ],
        [
            $base_url,
            $base_url,
            $base_url,
            $base_url,
        ],
        $output
    );
}

if (stripos($output, '<head') !== false && stripos($output, '<base ') === false) {
    $output = preg_replace(
        '/(<head[^>]*>)/i',
        '$1<base href="' . htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8') . '/">',
        $output,
        1
    ) ?? $output;
}

echo $output;
