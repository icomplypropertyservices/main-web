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

$options = getopt('', ['full', 'out::', 'help', 'keyword-towns::', 'print-redirects', 'collect-only']);
if (isset($options['help'])) {
    echo "Usage: php website/bin/static-export.php [--full] [--keyword-towns=priority|popular|all|none] [--out=dist] [--print-redirects]\n";
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
$keywordTowns = strtolower(trim((string)($options['keyword-towns'] ?? 'priority')));
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
require_once $websiteRoot . '/includes/manufacturer-hub.php';
require_once $websiteRoot . '/includes/router.php';
if (function_exists('manufacturerWriteLineLogos')) {
    manufacturerWriteLineLogos();
}
require_once $websiteRoot . '/includes/matrix-page.php';
require_once $websiteRoot . '/bin/build-shop-hubs.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

if (isset($options['print-redirects'])) {
    $txt = icomplyPrettyUrlRedirects();
    $needles = [
        '/products/aov-air-handling-package',
        '/products/intruder-alarm-package',
        '/manifest.webmanifest',
        '/manifest.json',
        '/group',
        '/pages/products',
        '/shop/fire/index.html',
    ];
    foreach ($needles as $n) {
        if (!str_contains($txt, $n)) {
            fwrite(STDERR, "redirects missing {$n}\n");
            exit(1);
        }
    }
    if (preg_match('#^/pages/keywords/:slug/:town\\s+\\S+\\s+301#m', $txt)
        || preg_match('#^/pages/electrical/:town\\s+\\S+\\s+301#m', $txt)
        || preg_match('#^/pages/windows-doors/:town\\s+\\S+\\s+301#m', $txt)) {
        fwrite(STDERR, "keyword×town and service×town must not 301 to a hub\n");
        exit(1);
    }
    if (preg_match('#^/shop\\s+/pages/packages#m', $txt) || preg_match('#^/products\\s+/pages/packages#m', $txt)) {
        fwrite(STDERR, "redirects still send /shop or /products to /pages/packages\n");
        exit(1);
    }
    // AOV / Barriers town exports are .php siblings. A 301 (even without !)
    // matches before the splat and would hide /pages/aov/{town}.php.
    $protected = [
        '/pages/aov/:town',
        '/pages/aov/:town/',
        '/pages/barriers/:town',
        '/pages/barriers/:town/',
        '/pages/aov-air-handling/:town',
        '/pages/aov-air-handling/:town/',
        '/pages/services/aov/:town',
        '/pages/services/aov/:town/',
        '/pages/services/aov-air-handling/:town',
        '/pages/services/aov-air-handling/:town/',
        '/pages/services/barriers/:town',
        '/pages/services/barriers/:town/',
    ];
    $seen = [];
    foreach (preg_split("/\\r?\\n/", $txt) as $line) {
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = preg_split('/\\s+/', trim($line));
        if (count($parts) < 3) {
            continue;
        }
        [$from, $to, $status] = [$parts[0], $parts[1], $parts[2]];
        if (!in_array($from, $protected, true)) {
            continue;
        }
        $seen[$from] = true;
        if ($status !== '200' || !str_ends_with($to, '.php')) {
            fwrite(STDERR, "AOV/Barriers town rule must be an unforced 200 to the exported .php file: {$line}\n");
            exit(1);
        }
    }
    foreach ($protected as $from) {
        if (!isset($seen[$from])) {
            fwrite(STDERR, "missing unforced 200 for {$from}\n");
            exit(1);
        }
    }
    echo $txt;
    exit(0);
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
if (isset($options['collect-only'])) {
    echo count($routes) . "\n";
    exit(0);
}
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
    '/renewals',
    '/renewals/thanks',
    '/pages/about',
    '/pages/areas',
    '/pages/manufacturers',
    '/pages/resources',
    '/pages/keywords',
    '/pages/keywords/eicr',
];

foreach ($routes as $i => $path) {
    $result = icomplyRenderExportRoute($path);
    if (isset($result['html']) && is_string($result['html']) && function_exists('icomplyPerfRewriteHtml')) {
        $result['html'] = icomplyPerfRewriteHtml($result['html']);
    }
    $status = $result['status'];
    $html = $result['html'];
    $n = $i + 1;
    $total = count($routes);

    if ($status >= 300 && $status < 400) {
        // /pages/products 301s to /products, and the checker still wants the alias file.
        if ($path === '/pages/products') {
            $alias = icomplyRenderExportRoute('/products');
            $aliasHtml = is_string($alias['html'] ?? null) ? $alias['html'] : '';
            if (function_exists('icomplyPerfRewriteHtml')) {
                $aliasHtml = icomplyPerfRewriteHtml($aliasHtml);
            }
            if (($alias['status'] ?? 500) === 200 && icomplyLooksLikeHtml($aliasHtml) && !str_contains($aliasHtml, '<?php')) {
                icomplyWritePrettyFiles($dist, '/pages/products', $aliasHtml);
                $ok++;
                $log(sprintf("[%d/%d] OK   %s (alias of /products, %d bytes)\n", $n, $total, $path, strlen($aliasHtml)));
                continue;
            }
        }
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
$productPages = icomplyExportProductPages($dist, $log);
$log("product pages={$productPages}\n");
icomplyWriteDistRedirects($dist);
icomplyWriteDistHeaders($dist);
if (!icomplyPublishDistSitemap($websiteRoot, $dist, $log)) {
    $fail++;
}

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
        'Manchester', 'Burnley', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale',
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

    $keywordMeta = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
    foreach ($keywords as $kw) {
        $slug = keywordSlug($kw);
        if (!empty($keywordMeta[$slug]['hub_only'])) {
            continue;
        }
        $kwService = $keywordMeta[$slug]['service'] ?? '';
        if ($kwService === 'barriers' || $kwService === 'aov-air-handling') {
            continue;
        }
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
        '/renewals',
        '/renewals/thanks',
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
    foreach (getManufacturerCatalog() as $slug => $entry) {
        $routes[] = '/pages/manufacturers/' . $slug;
        if (is_array($entry) && function_exists('manufacturerAreasFor')) {
            foreach (manufacturerAreasFor($entry) as $area) {
                $routes[] = '/pages/manufacturers/' . $slug . '/' . areaSlug((string)$area);
            }
        }
    }

    foreach (icomplyCollectKeywordRoutes($keywordTowns) as $path) {
        $routes[] = $path;
    }

    if (function_exists('acnRoutes')) {
        foreach (acnRoutes() as $path) {
            $routes[] = $path;
        }
    }

    // Jack: every service has every area landing (not only --full).
    // AOV and barriers use their own town lists, not the North West matrix.
    // Match router allowlists so export does not queue soft-404 boroughs.
    foreach (array_keys(getServices()) as $sSlug) {
        if ($sSlug === 'barriers' || $sSlug === 'aov-air-handling') {
            continue;
        }
        $areasFor = function_exists('getAreasForService') ? getAreasForService($sSlug) : getAreas();
        foreach ($areasFor as $area) {
            $routes[] = '/pages/' . $sSlug . '/' . areaSlug((string)$area);
        }
    }
    $barriersInc = SITE_ROOT . '/includes/barriers.php';
    if (is_file($barriersInc)) {
        require_once $barriersInc;
        if (function_exists('barriersPlaces')) {
            foreach (barriersPlaces() as $place) {
                $slug = (string)($place['slug'] ?? '');
                if ($slug !== '') {
                    $routes[] = '/pages/barriers/' . $slug;
                }
            }
        }
    }

    if (!function_exists('icomplyUkTownRoutes')) {
        require_once SITE_ROOT . '/includes/uk-towns.php';
    }
    foreach (icomplyUkTownRoutes() as $townPath) {
        $routes[] = $townPath;
    }
    require_once SITE_ROOT . '/includes/aov-place.php';
    $routes[] = '/pages/aov';
    foreach (array_keys(aovPlaces()) as $slug) {
        $routes[] = '/pages/aov/' . $slug;
    }

    // FRA only: every UK mainland town, including places outside the North West list.
    if (function_exists('getMainlandAreaRecords')) {
        foreach (getMainlandAreaRecords() as $row) {
            $slug = (string)($row['slug'] ?? '');
            if ($slug !== '') {
                $routes[] = '/pages/fire-risk-assessments/' . $slug;
            }
        }
    }
    // Nurse call only: places outside the North West list. Other services stay local.
    if (function_exists('getNationwideAreaRows')) {
        foreach (getNationwideAreaRows() as $row) {
            $slug = (string)($row['slug'] ?? '');
            if ($slug !== '') {
                $routes[] = '/pages/nurse-call/' . $slug;
            }
        }
    }

    // Manufacturer × every area is routed now. --full adds the matrix to the export.
    if ($full && function_exists('manufacturerAreaRoutes')) {
        foreach (manufacturerAreaRoutes() as $path) {
            $routes[] = $path;
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
    if (preg_match('#^/pages/barriers/([a-z0-9\-]+)$#', $path)) {
        return icomplyRenderRoute($path);
    }
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        $kwMeta = function_exists('getMajorKeywords') ? (getMajorKeywords()[function_exists('keywordSlug') ? keywordSlug($m[1]) : $m[1]] ?? null) : null;
        if (is_array($kwMeta) && ($kwMeta['service'] ?? '') === 'barriers') {
            return icomplyRenderRoute($path);
        }
        $area = function_exists('areaFromSlug') ? areaFromSlug($m[2]) : $m[2];
        $html = icomplyRenderKeywordTownHtml($m[1], (string)($area ?: $m[2]));
        if ($html !== '' && icomplyLooksLikeHtml($html)) {
            return ['html' => $html, 'status' => 200];
        }
    }
    if (preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)
        && $m[1] !== 'barriers'
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

function icomplyExportProductPages(string $dist, callable $log): int
{
    require_once SITE_ROOT . '/includes/product-pdp.php';
    $catalog = getShopCatalog();
    $seen = [];
    $written = 0;
    foreach ($catalog['products'] ?? [] as $product) {
        if (!is_array($product)) {
            continue;
        }
        $handle = strtolower(trim((string)($product['handle'] ?? '')));
        if ($handle === '' || isset($seen[$handle]) || !preg_match('/^[a-z0-9\-]+$/', $handle)) {
            continue;
        }
        $seen[$handle] = true;
        foreach (['/products/product', '/shop/products'] as $prefix) {
            $_GET['handle'] = $handle;
            $_SERVER['REQUEST_URI'] = $prefix . '/' . $handle;
            $_SERVER['QUERY_STRING'] = 'handle=' . $handle;
            ob_start();
            icomplyRenderProductPdp($prefix);
            $html = ob_get_clean();
            if (!is_string($html) || !icomplyLooksLikeHtml($html) || str_contains($html, 'Product not found')) {
                $log("FAIL product {$prefix}/{$handle}\n");
                throw new RuntimeException('Product page failed for ' . $handle);
            }
            icomplyWritePrettyFiles($dist, $prefix . '/' . $handle, $html);
        }
        $written++;
    }
    $_GET = [];
    $required = [
        'svc-ac-fgas-tm44',
        'svc-ac-domestic-annual',
        'svc-ac-commercial-quarterly',
        'svc-fire-eml-contract-combo',
    ];
    foreach ($required as $handle) {
        if (!isset($seen[$handle])) {
            throw new RuntimeException('Catalog is missing product ' . $handle);
        }
    }
    return $written;
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
        'subcontractor-onboarding-form.html',
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

/**
 * Publish the tiered sitemap into dist. The committed website/sitemap.xml can
 * still list keyword×town URLs; the checker requires the priority export's
 * sitemap to match icomplySitemapEntries().
 */
function icomplyPublishDistSitemap(string $websiteRoot, string $dist, callable $log): bool
{
    $sitemapFile = $websiteRoot . '/includes/sitemap.php';
    if (is_file($sitemapFile)) {
        require_once $sitemapFile;
    }
    if (!function_exists('icomplySitemapEntries') || !function_exists('icomplySitemapXmlFromEntries') || !function_exists('icomplyDistHasPage')) {
        fwrite(STDERR, "sitemap helpers missing; left copied sitemap.xml in place\n");
        return false;
    }
    $baseUrl = defined('SITE_URL') ? rtrim((string)SITE_URL, '/') : 'https://icomplypropertyservices.co.uk';
    $entries = [];
    foreach (icomplySitemapEntries() as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        if (icomplyDistHasPage($dist, (string)($entry['path'] ?? ''))) {
            $entries[] = $entry;
        }
    }
    if (count($entries) < 800) {
        fwrite(STDERR, 'Published sitemap has only ' . count($entries) . " URLs\n");
        return false;
    }
    $log('sitemap hub urls=' . count($entries) . "\n");
    $matrixFile = $websiteRoot . '/includes/matrix-catalogue.php';
    if (!is_file($matrixFile)) {
        fwrite(STDERR, "matrix catalogue missing\n");
        return false;
    }
    require_once $matrixFile;
    $stats = icomplyPublishTownMatrix($dist, $entries, $log);
    if (($stats['sitemap_urls'] ?? 0) < 1000000) {
        fwrite(STDERR, 'Town matrix sitemap has ' . ($stats['sitemap_urls'] ?? 0) . " URLs, under 1000000\n");
        return false;
    }
    return true;
}

function icomplyPrettyUrlRedirects(): string
{
    $base = <<<'TXT'
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
/access-control-systems  /pages/access-control-systems 301
/access-control-systems/ /pages/access-control-systems 301

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
/pages/privacy           /privacy    301
/pages/privacy/          /privacy    301
/pages/terms             /terms      301
/pages/terms/            /terms      301
/cookies                 /privacy    301
/cookies/                /privacy    301
/thanks                  /thank-you  301
/thanks/                 /thank-you  301
/thankyou                /thank-you  301
/thankyou/               /thank-you  301
/pages/residential       /pages/landlords 301
/pages/residential/      /pages/landlords 301
/blog                    /pages/resources 301
/blog/                   /pages/resources 301
/news                    /pages/resources 301
/news/                   /pages/resources 301

# Retired group host paths
/group                   /            301
/group/                  /            301
/group/*                 /            301

# Dead Shopify package handles → the live service hub (before /products rules)
/products/aov-air-handling-package       /pages/services/aov-air-handling     301
/products/electrical-compliance-package  /pages/services/electrical           301
/products/emergency-lighting-package     /pages/services/emergency-lighting   301
/products/fire-alarm-service-package     /pages/services/fire-alarms          301
/products/nurse-call-systems-package     /pages/services/nurse-call           301
/products/gas-safety-package             /pages/services/gas-systems          301
/products/intruder-alarm-package         /pages/services/intruder-alarm       301
/products/cctv-systems-package           /pages/services/cctv                 301
/products/access-control-package         /pages/services/access-control       301
/products/door-entry-package             /pages/services/door-entry           301
/products/intercoms-package              /pages/services/intercoms            301

# Catalogue PDPs are pre-rendered HTML stored as {handle}.php.
/products/product/:handle     /products/product/:handle.php    200!
/products/product/:handle/    /products/product/:handle.php    200!
/shop/products/:handle        /shop/products/:handle.php       200!
/shop/products/:handle/       /shop/products/:handle.php       200!
# Real XML files are written at /products/sitemap.xml and /shop/sitemap.xml.

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
/pages/products          /products                     301
/pages/products/         /products                     301

# PWA manifests. Non-force: a real file wins. Missing .webmanifest
# (live 404) falls through to manifest.json, which is published.
/manifest.webmanifest    /manifest.json               200
/site.webmanifest        /manifest.json               200
/manifest.json           /manifest.json               200!

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
/renewals                /renewals.php                200!
/renewals/               /renewals.php                200!
/renewals/thanks         /renewals/thanks.php         200!
/renewals/thanks/        /renewals/thanks.php         200!
/pages/renewals          /renewals                    301
/pages/renewals/         /renewals                    301
/become-a-subcontractor  /become-a-subcontractor.php  200!
/become-a-subcontractor/ /become-a-subcontractor.php  200!
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

__MATRIX_REDIRECTS__
# Keyword hubs have child town files (pages/keywords/{slug}/*.php).
# force so /pages/keywords/eicr does not 301 to /pages/keywords/eicr/.
/pages/keywords/:slug    /pages/keywords/:slug.php    200!
/pages/keywords/:slug/   /pages/keywords/:slug.php    200!

# Barrier town pages are files under /pages/barriers/. Force the hub redirect
# and the town pretty URL so a directory does not 301 the leaf.
/pages/barriers          /pages/services/barriers.php  301
/pages/barriers/         /pages/services/barriers.php  301
/pages/barriers/:slug    /pages/barriers/:slug.php     200!
/pages/barriers/:slug/   /pages/barriers/:slug.php     200!

# /pages/aov/{town}.php makes /pages/aov a directory. Force the directory index.
/pages/aov               /pages/aov.php               200!
/pages/aov/              /pages/aov.php               200!

# These slugs are services. The PHP router 301s the bare path to the service hub,
# and the town directory would otherwise 404 the pretty URL on Netlify.
/pages/ev-chargers       /pages/services/ev-chargers   301!
/pages/ev-chargers/      /pages/services/ev-chargers   301!
/pages/water-wras        /pages/services/water-wras    301!
/pages/water-wras/       /pages/services/water-wras    301!
/pages/windows-doors     /pages/services/windows-doors 301!
/pages/windows-doors/    /pages/services/windows-doors 301!

TXT;
    $txt = str_replace("__MATRIX_REDIRECTS__\n", icomplyUnpublishedMatrixRedirects(), $base);
    // Exact place-alias 301s must come before /pages/barriers/:slug and /pages/aov 200! rules.
    return icomplyPlaceAliasRedirectLines()
        . rtrim($txt, "\r\n") . "\n"
        . icomplyAovLegacyRedirectLines() . <<<'TXT'

# Splat pretty URLs. No force — /assets and real files win.
/*                       /:splat.php                  200
TXT;
}

/**
 * True for AOV and Barriers town exports. Those pages are real files
 * (towns with population over 10k), stored as /pages/.../{town}.php.
 * They must never be 301'd. A non-force 301 still wins over a .php sibling
 * because Netlify only skips an unforced rule when a file exists at the
 * request path itself.
 */
function icomplyTownExportMustNotRedirect(string $slug): bool
{
    return $slug === 'aov'
        || $slug === 'barriers'
        || str_starts_with($slug, 'aov-')
        || str_starts_with($slug, 'barriers');
}

/**
 * Keyword×town and service×town URLs are linked from hubs but are not in the
 * published file set (live 404). Send them to the hub that does exist.
 * AOV and Barriers town paths are excluded and rewritten 200 to their .php file.
 */
function icomplyUnpublishedMatrixRedirects(): string
{
    $lines = [
        '# AOV and Barriers × town (pop > 10k) are real exports. Never 301 them.',
        '# 200 with no ! serves /pages/aov/{town}.php, /pages/barriers/{town}.php,',
        '# /pages/services/aov/{town}.php and /pages/services/aov-air-handling/{town}.php.',
        '# An exported file at the request path still wins. A 301 is not used.',
        '/pages/aov/:town                              /pages/aov/:town.php                              200',
        '/pages/aov/:town/                             /pages/aov/:town.php                              200',
        '/pages/barriers/:town                         /pages/barriers/:town.php                         200',
        '/pages/barriers/:town/                        /pages/barriers/:town.php                         200',
        '/pages/aov-air-handling/:town                 /pages/aov-air-handling/:town.php                 200',
        '/pages/aov-air-handling/:town/                /pages/aov-air-handling/:town.php                 200',
        '/pages/services/aov/:town                     /pages/services/aov/:town.php                     200',
        '/pages/services/aov/:town/                    /pages/services/aov/:town.php                     200',
        '/pages/services/aov-air-handling/:town        /pages/services/aov-air-handling/:town.php        200',
        '/pages/services/aov-air-handling/:town/       /pages/services/aov-air-handling/:town.php        200',
        '/pages/services/barriers/:town                /pages/services/barriers/:town.php                200',
        '/pages/services/barriers/:town/               /pages/services/barriers/:town.php                200',
        '',
        '# Keyword×town and service×town are real pages (edge function, HTTP 200).',
        '# Do not 301 them to the hub. AOV and Barriers keep the 200 rewrites above.',
        '',
    ];
    return implode("\n", $lines);
}


function icomplyPlaceAliasRedirectLines(): string
{
    $lines = [
        '# Place aliases FIRST: missing area slugs → published barrier/AOV towns',
        '# (must precede barrier/AOV town force-rewrites later in this file)',
    ];
    $aliasFile = SITE_ROOT . '/data/place-aliases.json';
    if (!is_file($aliasFile)) {
        return implode("\n", $lines) . "\n\n";
    }
    $decoded = json_decode((string)file_get_contents($aliasFile), true);
    $bp = [];
    $rows = is_file(SITE_ROOT . '/data/barriers-places.json')
        ? json_decode((string)file_get_contents(SITE_ROOT . '/data/barriers-places.json'), true)
        : [];
    foreach (is_array($rows) ? $rows : [] as $row) {
        if (is_array($row) && !empty($row['slug'])) {
            $bp[(string)$row['slug']] = true;
        }
    }
    $aov = [];
    $aovFile = SITE_ROOT . '/data/uk-towns-10k.json';
    if (is_file($aovFile)) {
        $rows = json_decode((string)file_get_contents($aovFile), true);
        foreach (is_array($rows) ? $rows : [] as $row) {
            if (is_array($row) && !empty($row['slug'])) {
                $aov[(string)$row['slug']] = true;
            }
        }
    }
    foreach (['barriers' => '/pages/barriers/', 'aov' => '/pages/aov/'] as $family => $prefix) {
        $map = is_array($decoded[$family] ?? null) ? $decoded[$family] : [];
        foreach ($map as $from => $to) {
            $from = trim((string)$from);
            $to = trim((string)$to);
            if ($from === '' || $to === '' || $from === $to) {
                continue;
            }
            if ($family === 'barriers' && isset($bp[$from])) {
                continue;
            }
            if ($family === 'aov' && isset($aov[$from])) {
                continue;
            }
            $src = $prefix . $from;
            $dst = $prefix . $to;
            $lines[] = $src . '  ' . $dst . '  301!';
            $lines[] = $src . '/  ' . $dst . '  301!';
        }
    }
    return implode("\n", $lines) . "\n\n";
}

function icomplyAovLegacyRedirectLines(): string
{
    $lines = [
        '# AOV doorway URLs: old service×town and keyword×town',
    ];
    if (!function_exists('aovPlaces')) {
        require_once SITE_ROOT . '/includes/aov-place.php';
    }
    $places = aovPlaces();
    if (function_exists('getAreas') && function_exists('areaSlug')) {
        foreach (getAreas() as $area) {
            $slug = areaSlug((string)$area);
            $dest = isset($places[$slug])
                ? '/pages/aov/' . $slug
                : '/pages/services/aov-air-handling';
            // Prefer a published place alias when the area slug itself is missing.
            if (!isset($places[$slug]) && function_exists('icomplyResolvePlaceSlug')) {
                $resolved = icomplyResolvePlaceSlug('aov', $slug);
                if ($resolved !== '' && isset($places[$resolved])) {
                    $dest = '/pages/aov/' . $resolved;
                }
            }
            $lines[] = '/pages/aov-air-handling/' . $slug . '   ' . $dest . '  301';
            $lines[] = '/pages/aov-air-handling/' . $slug . '/  ' . $dest . '  301';
        }
    }

    // Do not add /pages/keywords/{slug}/* . That splat is more specific than
    // the hub rewrite and Netlify answers the hub itself with 404.
    // Keyword×town is served by netlify/edge-functions/town-matrix.js.
    return implode("\n", $lines) . "\n";
}

function icomplyPrettyUrlHeaders(): string
{
    return <<<'TXT'
# Pre-rendered HTML is stored with a .php filename so splat rewrites work.
# Headers match the *request* path (pretty URL), not only the .php destination.
#
# Powered by Netlify is injected at the edge as /.netlify/scripts/hud.
# script-src allows inline scripts and /assets/ only — no 'self' — so that
# HUD script cannot run even if the Netlify UI badge toggle is on.
/*
  Content-Security-Policy: script-src 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com https://ssl.google-analytics.com https://www.google.com https://www.googleadservices.com https://googleads.g.doubleclick.net https://icomplypropertyservices.co.uk/assets/ https://www.icomplypropertyservices.co.uk/assets/ https://*.netlify.app/assets/
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

/renewals
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/renewals/thanks
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/pages/services
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/pages/*
  Content-Type: text/html; charset=utf-8
  X-Content-Type-Options: nosniff

/assets/contact/*.vcf
  Content-Type: text/vcard; charset=utf-8
  Content-Disposition: attachment
  X-Content-Type-Options: nosniff
  Cache-Control: public, max-age=86400

/robots.txt
  Cache-Control: public, max-age=3600

/assets/css/*
  Content-Type: text/css; charset=utf-8
  Cache-Control: public, max-age=3600

/assets/images/opt/*
  Cache-Control: public, max-age=31536000, immutable

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
