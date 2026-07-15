<?php
/**
 * Vercel serverless entry — clean extensionless URLs + virtual routes.
 * Deploy marker: 2026-07-15-seo-v2
 */
declare(strict_types=1);

// Prefer real host when present (custom domain / preview)
$host = $_SERVER['HTTP_HOST'] ?? 'www.icomplypropertyservices.co.uk';
$host = preg_replace('/:\d+$/', '', (string)$host) ?: 'www.icomplypropertyservices.co.uk';
if (str_contains($host, 'localhost') || $host === '') {
    $host = 'www.icomplypropertyservices.co.uk';
}
// Canonical public host
if (strcasecmp($host, 'icomplypropertyservices.co.uk') === 0) {
    $host = 'www.icomplypropertyservices.co.uk';
}
$base_url = 'https://' . $host;

// Force production SITE_URL before config loads (constants)
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

// Block internals (admin allowed)
if (preg_match('#^/(bin|templates|data|includes)(/|$)#i', $uri)) {
    if (preg_match('#^/admin#i', $uri)) {
        // fall through to file router
    } else {
        // Allow SEO cron with key only
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

// Dynamic multi-part sitemaps if static rewrite missed
if (preg_match('#^/sitemap(-[0-9]+)?\.xml$#i', $uri, $sm)) {
    $file = $root . ($sm[0] === '/sitemap.xml' || $sm[0] === '/sitemap.XML'
        ? '/sitemap.xml'
        : $sm[0]);
    // normalize
    $file = $root . str_replace('/', DIRECTORY_SEPARATOR, $uri);
    if (is_file($file)) {
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        readfile($file);
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

// Auto-amend: never leak localhost URLs on production HTML/XML
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
