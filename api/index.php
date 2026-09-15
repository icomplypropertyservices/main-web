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

/** Drop shop/products locs from served sitemaps (they 301 to packages). */
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

// Legacy legal aliases (also in netlify.toml / _redirects)
if ($uri === '/privacy-policy') {
    header('Location: ' . $base_url . '/privacy', true, 301);
    exit;
}
if ($uri === '/terms-and-conditions') {
    header('Location: ' . $base_url . '/terms', true, 301);
    exit;
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

// Sitemaps — self-host locs on preview/prod host; keep all keyword URLs
if (preg_match('#^/sitemap(-[0-9]+)?\.xml$#i', $uri)) {
    $file = $root . str_replace('/', DIRECTORY_SEPARATOR, $uri);
    if (is_file($file)) {
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        $xml = (string)file_get_contents($file);
        $xml = seoStripShopProductUrls($xml);
        echo seoRewritePublicHost($xml, $base_url);
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
