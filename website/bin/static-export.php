#!/usr/bin/env php
<?php
/**
 * Build-time static pre-render for Netlify.
 *
 * Netlify does not execute PHP at request time. Publishing raw `website/`
 * serves `.php` as source and 404s extensionless pretty URLs.
 *
 * This exporter renders public routes to HTML under `dist/`:
 *   /privacy            → dist/privacy.php  + dist/privacy/index.html
 *   /pages/about        → dist/pages/about.php + dist/pages/about/index.html
 *   /                   → dist/index.html + dist/index.php
 *
 * Netlify splat rewrite `/* → /:splat.php` (200, no force) then serves the
 * pre-rendered HTML. Existing files win, so `/assets/*` is untouched.
 *
 * Usage (repo root):
 *   php website/bin/static-export.php
 *   php website/bin/static-export.php --full
 *   php website/bin/static-export.php --out=dist
 *
 * --full also renders keyword hubs and service×area landings (large dist).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "static-export.php is a CLI build tool.\n");
    exit(1);
}

$options = getopt('', ['full', 'out::', 'help']);
if (isset($options['help'])) {
    echo "Usage: php website/bin/static-export.php [--full] [--out=dist]\n";
    exit(0);
}

$websiteRoot = dirname(__DIR__);
$repoRoot = dirname($websiteRoot);
$outOpt = $options['out'] ?? ($repoRoot . '/dist');
$dist = $outOpt;
if ($dist === '' || $dist === '.' || $dist === './') {
    $dist = $repoRoot . '/dist';
}
if ($dist[0] !== '/') {
    $dist = $repoRoot . '/' . ltrim($dist, '/');
}
$full = isset($options['full']);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

$siteUrl = getenv('SITE_URL');
if (!is_string($siteUrl) || $siteUrl === '' || str_contains($siteUrl, 'localhost')) {
    $siteUrl = 'https://icomplypropertyservices.co.uk';
    putenv('SITE_URL=' . $siteUrl);
    $_ENV['SITE_URL'] = $siteUrl;
}

$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'icomplypropertyservices.co.uk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['REQUEST_SCHEME'] = 'https';

require_once $websiteRoot . '/config.php';
require_once $websiteRoot . '/includes/router.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$log = static function (string $msg): void {
    fwrite(STDERR, $msg);
};

$log("Icomply static export (Netlify pre-render)\n");
$log("SITE_URL=" . SITE_URL . "\n");
$log("dist={$dist}\n");
$log($full ? "mode=full (hubs + keywords + service×area)\n" : "mode=default (core + hubs)\n");
$log(str_repeat('=', 56) . "\n");

icomplyResetDist($dist);

$routes = icomplyCollectExportRoutes($full);
sort($routes);
$routes = array_values(array_unique($routes));

$ok = 0;
$skip = 0;
$fail = 0;
$required = [
    '/',
    '/privacy',
    '/terms',
    '/contact',
    '/pages/about',
    '/pages/areas',
    '/pages/manufacturers',
    '/pages/resources',
];

foreach ($routes as $i => $path) {
    $result = icomplyRenderRoute($path);
    $status = $result['status'];
    $html = $result['html'];
    $n = $i + 1;
    $total = count($routes);

    if ($status >= 300 && $status < 400) {
        $skip++;
        $log(sprintf("[%d/%d] SKIP %s (redirect %d)\n", $n, $total, $path, $status));
        continue;
    }
    if ($status >= 400 || $html === '' || !icomplyLooksLikeHtml($html)) {
        $fail++;
        $log(sprintf("[%d/%d] FAIL %s (status=%d bytes=%d)\n", $n, $total, $path, $status, strlen($html)));
        continue;
    }
    if (str_contains($html, '<?php')) {
        $fail++;
        $log(sprintf("[%d/%d] FAIL %s (PHP source leaked into HTML)\n", $n, $total, $path));
        continue;
    }

    icomplyWritePrettyFiles($dist, $path, $html);
    $ok++;
    if ($n === 1 || $n === $total || $n % 50 === 0 || in_array($path, $required, true)) {
        $log(sprintf("[%d/%d] OK   %s (%d bytes)\n", $n, $total, $path, strlen($html)));
    }
}

$notFoundHtml = icomplyRenderRoute('/__icomply-missing-export-404__')['html'];
if (!icomplyLooksLikeHtml($notFoundHtml)) {
    $notFoundHtml = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><title>Not found</title></head><body><h1>Not found</h1></body></html>\n";
}
file_put_contents($dist . '/404.html', $notFoundHtml);

icomplyCopyStaticAssets($websiteRoot, $repoRoot, $dist);
icomplyWriteDistRedirects($dist);
icomplyWriteDistHeaders($dist);

$log(str_repeat('=', 56) . "\n");
$log("OK={$ok} SKIP={$skip} FAIL={$fail} routes=" . count($routes) . "\n");

foreach ($required as $need) {
    $php = $need === '/' ? $dist . '/index.html' : $dist . $need . '.php';
    $dir = $need === '/' ? $dist . '/index.html' : $dist . $need . '/index.html';
    if (!is_file($php) && !is_file($dir)) {
        fwrite(STDERR, "Missing required pretty-URL output: {$need}\n");
        $fail++;
    }
}

if ($fail > 0 || $ok < count($required)) {
    fwrite(STDERR, "static-export FAILED\n");
    exit(1);
}

$log("static-export OK → {$dist}\n");
exit(0);

/**
 * @return list<string>
 */
function icomplyCollectExportRoutes(bool $full): array
{
    $routes = [
        '/',
        '/privacy',
        '/terms',
        '/contact',
        '/thank-you',
    ];

    $skipBasenames = [
        'about.php' => true,
        'packages.php' => true,
        'faq.php' => true,
        'landlords.php' => true,
        'commercial.php' => true,
        'care-homes.php' => true,
        'router.php' => true,
        'config.php' => true,
        'config.local.php' => true,
        '404.php' => true,
    ];

    foreach (glob(SITE_ROOT . '/*.php') ?: [] as $file) {
        $base = basename($file);
        if (isset($skipBasenames[$base])) {
            continue;
        }
        $routes[] = '/' . basename($base, '.php');
    }

    $skipDirs = ['admin', 'bin', 'includes', 'templates', 'data', 'shop', 'api'];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(SITE_ROOT . '/pages', FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }
        $rel = substr($file->getPathname(), strlen(SITE_ROOT));
        $rel = str_replace('\\', '/', $rel);
        $parts = explode('/', trim($rel, '/'));
        if (isset($parts[0]) && in_array($parts[0], $skipDirs, true)) {
            continue;
        }
        $path = preg_replace('#\.php$#i', '', $rel) ?? $rel;
        $path = preg_replace('#/index$#i', '', $path) ?? $path;
        if ($path === '' || $path === '/') {
            continue;
        }
        if (preg_match('#^/pages/services/([a-z0-9\-]+)$#', $path, $m)
            && !isset(getServices()[$m[1]])) {
            continue;
        }
        $routes[] = $path;
    }

    foreach (array_keys(getServices()) as $slug) {
        $routes[] = '/pages/services/' . $slug;
    }
    foreach (getAreas() as $area) {
        $routes[] = '/pages/areas/' . areaSlug((string)$area);
    }
    foreach (array_keys(getManufacturerCatalog()) as $slug) {
        $routes[] = '/pages/manufacturers/' . $slug;
    }

    if ($full) {
        if (function_exists('getMajorKeywords') && function_exists('keywordSlug')) {
            foreach (array_keys(getMajorKeywords()) as $kw) {
                $routes[] = '/pages/keywords/' . keywordSlug($kw);
            }
        }
        foreach (array_keys(getServices()) as $sSlug) {
            foreach (getAreas() as $area) {
                $routes[] = '/pages/' . $sSlug . '/' . areaSlug((string)$area);
            }
        }
    }

    return $routes;
}

/**
 * @return array{html:string,status:int}
 */
function icomplyRenderRoute(string $path): array
{
    if (function_exists('header_remove')) {
        header_remove();
    }
    http_response_code(200);
    $_GET = [];
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $path === '/' ? '/' : $path;
    $_SERVER['QUERY_STRING'] = '';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';

    ob_start();
    try {
        routerHandleRequest();
        $html = (string)ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        fwrite(STDERR, "Render error {$path}: " . $e->getMessage() . "\n");
        return ['html' => '', 'status' => 500];
    }

    $status = (int)http_response_code();
    if ($status === 0) {
        $status = 200;
    }
    foreach (headers_list() as $header) {
        if (stripos($header, 'Location:') === 0) {
            if ($status < 300 || $status >= 400) {
                $status = 301;
            }
            break;
        }
    }

    return ['html' => $html, 'status' => $status];
}

function icomplyLooksLikeHtml(string $html): bool
{
    $trim = ltrim($html);
    return $trim !== ''
        && (
            str_starts_with($trim, '<!DOCTYPE')
            || str_starts_with($trim, '<!doctype')
            || str_starts_with($trim, '<html')
            || str_contains($html, '<body')
        );
}

function icomplyWritePrettyFiles(string $dist, string $path, string $html): void
{
    if ($path === '/' || $path === '') {
        file_put_contents($dist . '/index.html', $html);
        file_put_contents($dist . '/index.php', $html);
        return;
    }
    $rel = trim(str_replace('\\', '/', $path), '/');
    $phpFile = $dist . '/' . $rel . '.php';
    $dir = $dist . '/' . $rel;
    $phpDir = dirname($phpFile);
    if (!is_dir($phpDir) && !mkdir($phpDir, 0755, true) && !is_dir($phpDir)) {
        throw new RuntimeException('Cannot mkdir ' . $phpDir);
    }
    file_put_contents($phpFile, $html);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot mkdir ' . $dir);
    }
    file_put_contents($dir . '/index.html', $html);
}

function icomplyResetDist(string $dist): void
{
    if (is_dir($dist)) {
        icomplyRmrf($dist);
    }
    if (!mkdir($dist, 0755, true) && !is_dir($dist)) {
        throw new RuntimeException('Cannot create dist ' . $dist);
    }
}

function icomplyRmrf(string $path): void
{
    if (!is_dir($path)) {
        if (is_file($path)) {
            unlink($path);
        }
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($path);
}

function icomplyCopyStaticAssets(string $websiteRoot, string $repoRoot, string $dist): void
{
    icomplyCopyDir($websiteRoot . '/assets', $dist . '/assets');

    $copyFiles = [
        'robots.txt',
        'sitemap.xml',
        'manifest.json',
        'favicon.ico',
    ];
    foreach ($copyFiles as $name) {
        $src = $websiteRoot . '/' . $name;
        if (is_file($src)) {
            copy($src, $dist . '/' . $name);
        }
    }

    foreach (glob($websiteRoot . '/google-site-verification=*.txt') ?: [] as $src) {
        copy($src, $dist . '/' . basename($src));
    }
    foreach (glob($repoRoot . '/google-site-verification=*.txt') ?: [] as $src) {
        $dest = $dist . '/' . basename($src);
        if (!is_file($dest)) {
            copy($src, $dest);
        }
    }
}

function icomplyCopyDir(string $src, string $dest): void
{
    if (!is_dir($src)) {
        return;
    }
    if (!is_dir($dest) && !mkdir($dest, 0755, true) && !is_dir($dest)) {
        throw new RuntimeException('Cannot mkdir ' . $dest);
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    $srcLen = strlen($src);
    foreach ($it as $file) {
        $rel = substr($file->getPathname(), $srcLen);
        $target = $dest . $rel;
        if ($file->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                throw new RuntimeException('Cannot mkdir ' . $target);
            }
            continue;
        }
        $targetDir = dirname($target);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Cannot mkdir ' . $targetDir);
        }
        copy($file->getPathname(), $target);
    }
}

function icomplyWriteDistRedirects(string $dist): void
{
    file_put_contents($dist . '/_redirects', icomplyPrettyUrlRedirects());
}

function icomplyWriteDistHeaders(string $dist): void
{
    file_put_contents($dist . '/_headers', icomplyPrettyUrlHeaders());
}

function icomplyPrettyUrlRedirects(): string
{
    return <<<'TXT'
# Generated by website/bin/static-export.php — do not publish raw website/.
# Existing files win (no force) so /assets/* is never rewritten.

# Sensitive source paths (must never be served)
/config.php              /404.html    404
/config.local.php        /404.html    404
/router.php              /404.html    404
/includes/*              /404.html    404
/bin/*                   /404.html    404
/data/*                  /404.html    404
/admin/*                 /404.html    404
/templates/*             /404.html    404

# Root PHP aliases that 301 in website/*.php
/about                   /pages/about    301
/about/                  /pages/about    301
/faq                     /pages/faq      301
/faq/                    /pages/faq      301
/packages                /pages/packages 301
/packages/               /pages/packages 301
/landlords               /pages/landlords 301
/landlords/              /pages/landlords 301
/commercial              /pages/commercial 301
/commercial/             /pages/commercial 301
/care-homes              /pages/care-homes 301
/care-homes/             /pages/care-homes 301

# Legacy legal aliases
/privacy-policy          /privacy    301
/privacy-policy/         /privacy    301
/terms-and-conditions    /terms      301
/terms-and-conditions/   /terms      301
/about-us                /pages/about 301
/about-us/               /pages/about 301
/contact-us              /contact    301
/contact-us/             /contact    301
/cookie-policy           /privacy    301
/cookie-policy/          /privacy    301
/blog                    /pages/resources 301
/blog/                   /pages/resources 301
/news                    /pages/resources 301
/news/                   /pages/resources 301

# Shop / products → packages
/shop                    /pages/packages    301
/shop/*                  /pages/packages    301
/products                /pages/packages    301
/products/*              /pages/packages    301

# Old 470-part sitemap index → single compact urlset
/sitemap-*.xml           /sitemap.xml    301

# Missing *-photo.jpg → working twin (static assets bypass PHP)
/assets/images/services/*-photo.jpg    /assets/images/services/:splat.jpg    200

# Missing manufacturer logos (existing files still win) → service fallback
/assets/images/manufacturers/*    /assets/images/services/fire-alarms.jpg    200

# Explicit pretty URLs → pre-rendered HTML stored as .php
/privacy                 /privacy.php                 200
/privacy/                /privacy.php                 200
/terms                   /terms.php                   200
/terms/                  /terms.php                   200
/contact                 /contact.php                 200
/contact/                /contact.php                 200
/pages/about             /pages/about.php             200
/pages/about/            /pages/about.php             200
/pages/areas             /pages/areas.php             200
/pages/areas/            /pages/areas.php             200
/pages/manufacturers     /pages/manufacturers.php     200
/pages/manufacturers/    /pages/manufacturers.php     200
/pages/resources         /pages/resources.php         200
/pages/resources/        /pages/resources.php         200

# Splat pretty URLs. No force — /assets and real files win.
/*                       /:splat.php                  200
TXT;
}

function icomplyPrettyUrlHeaders(): string
{
    return <<<'TXT'
# Pre-rendered HTML is stored with a .php filename so splat rewrites work.
# Force browsers to treat it as HTML, never as downloadable source.
/*.php
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/robots.txt
  Cache-Control: public, max-age=3600

/manifest.json
  Content-Type: application/manifest+json; charset=utf-8
  Cache-Control: public, max-age=3600

/sitemap*.xml
  Content-Type: application/xml; charset=utf-8
  Cache-Control: public, max-age=3600
TXT;
}
