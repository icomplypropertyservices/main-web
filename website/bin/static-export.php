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
 *   php website/bin/static-export.php --keyword-towns=all
 *   php website/bin/static-export.php --out=dist
 *
 * Default export includes every sitemap keyword hub (/pages/keywords/{slug})
 * plus town combos that the previous PHP router served from chrome:
 *   popular towns × all keywords, and all towns × priority keywords
 *   (eicr, eicr-report, FRA, gas, CCTV, …).
 * Electrical and gas keyword families always get the FULL areas list
 * (keyword × every town) so the Netlify static export includes that matrix.
 * --keyword-towns=all renders every keyword×area (~200k HTML files).
 * --full also renders service×area landings.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "static-export.php is a CLI build tool.\n");
    exit(1);
}

$options = getopt('', ['full', 'out::', 'help', 'keyword-towns::']);
if (isset($options['help'])) {
    echo "Usage: php website/bin/static-export.php [--full] [--keyword-towns=priority|popular|all|none] [--out=dist]\n";
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
$keywordTowns = strtolower(trim((string)($options['keyword-towns'] ?? 'all')));
if (!in_array($keywordTowns, ['priority', 'popular', 'all', 'none'], true)) {
    fwrite(STDERR, "Invalid --keyword-towns={$keywordTowns} (use priority|popular|all|none)\n");
    exit(1);
}

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
require_once $websiteRoot . '/includes/matrix-page.php';
require_once $websiteRoot . '/bin/build-shop-hubs.php';

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
$log($full ? "mode=full (core + hubs + keywords + service×area)\n" : "mode=default (core + hubs + keywords)\n");
$log("keyword-towns={$keywordTowns}\n");
$log(str_repeat('=', 56) . "\n");

icomplyResetDist($dist);
// Copy CSS/JS/favicons first so a long export still has /assets even if interrupted.
icomplyCopyStaticAssets($websiteRoot, $repoRoot, $dist);

$routes = icomplyCollectExportRoutes($full, $keywordTowns);
sort($routes);
$routes = array_values(array_unique($routes));
$kwHubs = 0;
$kwTowns = 0;
foreach ($routes as $path) {
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path)) {
        $kwTowns++;
    } elseif (preg_match('#^/pages/keywords/([a-z0-9\-]+)$#', $path) && $path !== '/pages/keywords/index') {
        $kwHubs++;
    }
}
$log("keyword hubs={$kwHubs} keyword×town={$kwTowns} total_routes=" . count($routes) . "\n");

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
    '/pages/keywords',
    '/pages/keywords/eicr',
];

foreach ($routes as $i => $path) {
    $result = icomplyRenderExportRoute($path);
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
    $every = $total > 2000 ? 500 : ($total > 400 ? 100 : 50);
    if ($n === 1 || $n === $total || $n % $every === 0 || in_array($path, $required, true)) {
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
 * Featured towns linked from homepage / keyword hubs (must exist as HTML).
 *
 * @return list<string>
 */
function icomplyPopularTownNames(): array
{
    $areas = function_exists('getAreas') ? getAreas() : [];
    $popular = [
        'Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale',
        'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool',
    ];
    return array_values(array_filter(
        $popular,
        static fn(string $t): bool => in_array($t, $areas, true)
    ));
}

/**
 * Keyword hubs (sitemap) plus town combos from the previous PHP router set.
 *
 * @return list<string>
 */
function icomplyCollectKeywordRoutes(string $townMode): array
{
    $routes = [];
    if (!function_exists('getMajorKeywords') || !function_exists('keywordSlug')) {
        return $routes;
    }
    $keywords = array_keys(getMajorKeywords());
    foreach ($keywords as $kw) {
        $routes[] = '/pages/keywords/' . keywordSlug($kw);
    }

    if ($townMode === 'none' || !function_exists('getAreas') || !function_exists('areaSlug')) {
        return $routes;
    }

    $areas = getAreas();
    $popularTowns = icomplyPopularTownNames();
    $priorityKw = [];
    if (function_exists('getPopularKeywordSlugs')) {
        foreach (getPopularKeywordSlugs() as $slug) {
            $priorityKw[keywordSlug($slug)] = true;
        }
    }
    $familyKw = [];
    if (function_exists('getElectricalGasMatrixKeywordSlugs')) {
        foreach (getElectricalGasMatrixKeywordSlugs() as $slug) {
            $familyKw[keywordSlug($slug)] = true;
        }
    }

    foreach ($keywords as $kw) {
        $slug = keywordSlug($kw);
        $fullTowns = $townMode === 'all'
            || isset($familyKw[$slug])
            || ($townMode === 'priority' && isset($priorityKw[$slug]));
        if ($fullTowns) {
            $towns = $areas;
        } else {
            $towns = $popularTowns;
        }
        foreach ($towns as $area) {
            $routes[] = '/pages/keywords/' . $slug . '/' . areaSlug((string)$area);
        }
    }

    return $routes;
}

/**
 * @return list<string>
 */
function icomplyCollectExportRoutes(bool $full, string $keywordTowns = 'priority'): array
{
    $routes = [
        '/',
        '/privacy',
        '/terms',
        '/contact',
        '/thank-you',
        '/products',
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
    if (function_exists('barrierManufacturerRecords')) {
        foreach (barrierManufacturerRecords() as $brand) {
            $bSlug = (string)($brand['slug'] ?? '');
            if ($bSlug === '') {
                continue;
            }
            foreach (getAreas() as $area) {
                $routes[] = '/pages/manufacturers/' . $bSlug . '/' . areaSlug((string)$area);
            }
        }
    }

    foreach (icomplyCollectKeywordRoutes($keywordTowns) as $path) {
        $routes[] = $path;
    }

    // Jack: every service has every area landing (not only --full).
    foreach (array_keys(getServices()) as $sSlug) {
        foreach (getAreas() as $area) {
            $routes[] = '/pages/' . $sSlug . '/' . areaSlug((string)$area);
        }
    }

    return $routes;
}

/**
 * Fast path for the full keyword×town and service×area matrix.
 *
 * @return array{html:string,status:int}
 */
function icomplyRenderExportRoute(string $path): array
{
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)
        && function_exists('barrierManufacturerAreaExportHtml')) {
        $area = function_exists('areaFromSlug') ? areaFromSlug($m[2]) : null;
        if ($area !== null) {
            $html = barrierManufacturerAreaExportHtml($m[1], $area);
            if ($html !== '' && icomplyLooksLikeHtml($html)) {
                return ['html' => $html, 'status' => 200];
            }
        }
    }
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        $area = function_exists('areaFromSlug') ? areaFromSlug($m[2]) : $m[2];
        $html = icomplyRenderKeywordTownHtml($m[1], (string)($area ?: $m[2]));
        if ($html !== '' && icomplyLooksLikeHtml($html)) {
            return ['html' => $html, 'status' => 200];
        }
    }
    if (preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)
        && function_exists('getServices')
        && isset(getServices()[$m[1]])) {
        $area = function_exists('areaFromSlug') ? areaFromSlug($m[2]) : $m[2];
        $html = icomplyRenderServiceAreaHtml($m[1], (string)($area ?: $m[2]));
        if ($html !== '' && icomplyLooksLikeHtml($html)) {
            return ['html' => $html, 'status' => 200];
        }
    }
    return icomplyRenderRoute($path);
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
    $phpDir = dirname($phpFile);
    if (!is_dir($phpDir) && !mkdir($phpDir, 0755, true) && !is_dir($phpDir)) {
        throw new RuntimeException('Cannot mkdir ' . $phpDir);
    }
    // Only write the .php HTML sibling. A {path}/index.html directory makes
    // Netlify 301 /path → /path/ and breaks the 200 pretty-URL rewrite.
    file_put_contents($phpFile, $html);
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
        'manifest.webmanifest',
        'site.webmanifest',
        'favicon.ico',
        'lead-popup-form.html',
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

    // PR #9 trade hubs: /shop/index.html + Fire/Electrical/Security/Gas (never PHP source).
    icomplyCopyShopStatic($websiteRoot . '/shop', $dist . '/shop');
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

# Shop / products — trade hubs (never 301 to packages)
/shop                    /shop/index.html              200!
/shop/                   /shop/index.html              200!
/shop/fire               /shop/fire/index.html         200!
/shop/fire/              /shop/fire/index.html         200!
/shop/electrical         /shop/electrical/index.html   200!
/shop/electrical/        /shop/electrical/index.html   200!
/shop/security           /shop/security/index.html     200!
/shop/security/          /shop/security/index.html     200!
/shop/gas                /shop/gas/index.html          200!
/shop/gas/               /shop/gas/index.html          200!
/products                /products.php                 200!
/products/               /products.php                 200!

# PWA manifest aliases (Ellie live 404 on /manifest.webmanifest)
/manifest.webmanifest    /manifest.webmanifest    200!
/site.webmanifest        /site.webmanifest        200!
/manifest.json           /manifest.json           200!

# Brand CSS must never be splat-rewritten to .php
/assets/css/site.css     /assets/css/site.css     200!
/assets/css/*            /assets/css/:splat       200

# Old 470-part sitemap index → single compact urlset
/sitemap-*.xml           /sitemap.xml    301

# Missing *-photo.jpg → working twin (static assets bypass PHP)
/assets/images/services/*-photo.jpg    /assets/images/services/:splat.jpg    200

# Missing manufacturer logos (existing files still win) → service fallback
/assets/images/manufacturers/*    /assets/images/services/fire-alarms.jpg    200

# Explicit pretty URLs → pre-rendered HTML stored as .php
# 200! (force) so hubs with child files (e.g. /pages/areas/manchester.php)
# do not 301 /pages/areas → /pages/areas/. Splat below stays unforced.
/privacy                 /privacy.php                 200!
/privacy/                /privacy.php                 200!
/terms                   /terms.php                   200!
/terms/                  /terms.php                   200!
/contact                 /contact.php                 200!
/contact/                /contact.php                 200!
/pages/about             /pages/about.php             200!
/pages/about/            /pages/about.php             200!
/pages/areas             /pages/areas.php             200!
/pages/areas/            /pages/areas.php             200!
/pages/services          /pages/services.php          200!
/pages/services/         /pages/services.php          200!
/pages/manufacturers     /pages/manufacturers.php     200!
/pages/manufacturers/    /pages/manufacturers.php     200!
/pages/resources         /pages/resources.php         200!
/pages/resources/        /pages/resources.php         200!
/pages/keywords          /pages/keywords.php          200!
/pages/keywords/         /pages/keywords.php          200!

# Keyword hubs have child town files (pages/keywords/{slug}/*.php).
# force so /pages/keywords/eicr does not 301 to /pages/keywords/eicr/.
/pages/keywords/:slug    /pages/keywords/:slug.php    200!
/pages/keywords/:slug/   /pages/keywords/:slug.php    200!

# Splat pretty URLs. No force — /assets and real files win.
/*                       /:splat.php                  200
TXT;
}

function icomplyPrettyUrlHeaders(): string
{
    return <<<'TXT'
# Pre-rendered HTML is stored with a .php filename so splat rewrites work.
# Headers match the *request* path (pretty URL), not only the .php destination.
/*.php
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/privacy
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/terms
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/contact
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/fire
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/fire/
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/electrical
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/electrical/
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/security
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/security/
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/gas
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/gas/
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/shop/assets/*.css
  Content-Type: text/css; charset=utf-8

/shop/assets/*.svg
  Content-Type: image/svg+xml

/products
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/thank-you
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/pages/services
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/pages/*
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/robots.txt
  Cache-Control: public, max-age=3600

/assets/css/*
  Content-Type: text/css; charset=utf-8

/manifest.json
  Content-Type: application/manifest+json; charset=utf-8
  Cache-Control: public, max-age=3600

/manifest.webmanifest
  Content-Type: application/manifest+json; charset=utf-8
  Cache-Control: public, max-age=3600

/site.webmanifest
  Content-Type: application/manifest+json; charset=utf-8
  Cache-Control: public, max-age=3600

/sitemap*.xml
  Content-Type: application/xml; charset=utf-8
  Cache-Control: public, max-age=3600
TXT;
}
