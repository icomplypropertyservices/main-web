<?php
/**
 * Front-controller dispatch for clean (extensionless) URLs.
 * Returns true if a page was handled.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/render.php';

/**
 * Normalize request path relative to site root (no leading SITE path prefix).
 * e.g. /icomply/pages/keywords/eicr/stockport → /pages/keywords/eicr/stockport
 */
function routerRequestPath(): string {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uri = rawurldecode($uri);
    $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
    $basePath = rtrim($basePath, '/');
    if ($basePath !== '' && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath)) ?: '/';
    }
    $uri = '/' . ltrim($uri, '/');
    // Strip trailing slash (except root)
    if ($uri !== '/' && str_ends_with($uri, '/')) {
        $uri = rtrim($uri, '/');
    }
    // Strip .php if someone hits old URLs
    if (str_ends_with(strtolower($uri), '.php')) {
        $uri = substr($uri, 0, -4);
        if ($uri === '') {
            $uri = '/';
        }
    }
    return $uri;
}

/**
 * Try to serve a physical PHP file for a clean path.
 */
function routerTryFile(string $relPath): bool {
    $relPath = '/' . ltrim(str_replace('\\', '/', $relPath), '/');
    $candidates = [
        SITE_ROOT . $relPath . '.php',
        SITE_ROOT . $relPath . '/index.php',
        SITE_ROOT . $relPath,
    ];
    $rootReal = realpath(SITE_ROOT) ?: SITE_ROOT;
    foreach ($candidates as $file) {
        $real = realpath($file);
        if ($real === false || !is_file($real)) {
            continue;
        }
        if (!str_starts_with($real, $rootReal)) {
            continue;
        }
        if (!str_ends_with(strtolower($real), '.php')) {
            continue;
        }
        require $real;
        return true;
    }
    return false;
}

/**
 * Virtual routes that do not need per-URL stub files.
 */
function routerDispatchVirtual(string $path): bool {
    // Trade shop hubs are static HTML. Prefer them over shop/index.php,
    // which needs Shopify helpers and 500s when that file is unavailable.
    if (preg_match('#^/shop(?:/(fire|electrical|security|gas))?$#', $path, $shopMatch)) {
        $rel = '/shop' . (isset($shopMatch[1]) ? '/' . $shopMatch[1] : '') . '/index.html';
        $html = SITE_ROOT . $rel;
        if (is_file($html)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($html);
            return true;
        }
    }

    // Dead package handles and the retired group path (also in _redirects).
    $packageHubs = [
        '/products/aov-air-handling-package' => '/pages/services/aov-air-handling',
        '/products/electrical-compliance-package' => '/pages/services/electrical',
        '/products/emergency-lighting-package' => '/pages/services/emergency-lighting',
        '/products/fire-alarm-service-package' => '/pages/services/fire-alarms',
        '/products/nurse-call-systems-package' => '/pages/services/nurse-call',
        '/products/gas-safety-package' => '/pages/services/gas-systems',
        '/products/intruder-alarm-package' => '/pages/services/intruder-alarm',
        '/products/cctv-systems-package' => '/pages/services/cctv',
        '/products/access-control-package' => '/pages/services/access-control',
        '/products/door-entry-package' => '/pages/services/door-entry',
        '/products/intercoms-package' => '/pages/services/intercoms',
        '/pages/products' => '/products',
        '/group' => '/',
    ];
    if (isset($packageHubs[$path])) {
        header('Location: ' . url($packageHubs[$path]), true, 301);
        icomplyRequestExit();
        return true;
    }

    // Directory indexes (url() strips /index)
    // keywords-hub lives outside pages/keywords/** so it survives vercelignore of stubs.
    $indexes = [
        // Prefer physical /pages/{hub}.php (live pretty-URL pattern) then directory index.
        '/pages/keywords' => ['/pages/keywords', '/pages/keywords/index', '/pages/keywords-hub'],
        '/pages/services' => ['/pages/services', '/pages/services/index'],
        '/pages/manufacturers' => ['/pages/manufacturers', '/pages/manufacturers/index'],
        '/pages/areas' => ['/pages/areas', '/pages/areas/index'],
        '/pages/resources' => ['/pages/resources', '/pages/resources/index'],
        '/shop' => ['/shop/index'],
        '/products' => ['/pages/products', '/products'],
    ];
    if (isset($indexes[$path])) {
        foreach ($indexes[$path] as $candidate) {
            if (routerTryFile($candidate)) {
                return true;
            }
        }
        return false;
    }

    if (!function_exists('icomplyDispatchTownPath')) {
        require_once __DIR__ . '/town-service-pages.php';
    }
    if (icomplyDispatchTownPath($path)) {
        return true;
    }

    // Resource guides: /pages/resources/{slug} → pages/resources/{slug}.php
    if (preg_match('#^/pages/resources/([a-z0-9\-]+)$#', $path, $m) && $m[1] !== 'index') {
        return routerTryFile('/pages/resources/' . $m[1]);
    }

    // Nationwide AOV town pages (not the North West service×area matrix).
    if ($path === '/pages/aov') {
        require_once SITE_ROOT . '/includes/aov.php';
        require_once SITE_ROOT . '/includes/aov-place.php';
        aovRenderDirectory();
        return true;
    }
    if (preg_match('#^/pages/aov/([a-z0-9\-]+)$#', $path, $m)) {
        require_once SITE_ROOT . '/includes/aov.php';
        aovRenderTown($m[1]);
        return true;
    }

    // /pages/keywords/{kw}/{area}
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        renderKeywordAreaPage($m[1], $m[2]);
        return true;
    }
    // /pages/keywords/{kw}  (not the directory index)
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)$#', $path, $m) && $m[1] !== 'index') {
        renderKeywordPage($m[1]);
        return true;
    }
    // /pages/services/{slug}
    if (preg_match('#^/pages/services/([a-z0-9\-]+)$#', $path, $m)) {
        $retiredServicePages = [
            'ev-charging' => '/pages/services/ev-chargers',
            'solar-pv' => '/pages/services',
            'battery-storage' => '/pages/services',
            'air-source-heat-pumps' => '/pages/services',
            'ground-source-heat-pumps' => '/pages/services',
            'solar-thermal' => '/pages/services',
        ];
        if (isset($retiredServicePages[$m[1]])) {
            header('Location: ' . url($retiredServicePages[$m[1]]), true, 301);
            icomplyRequestExit();
            return true;
        }
        renderServiceHubPage($m[1]);
        return true;
    }
    // Access control nationwide hub + city pages.
    if ($path === '/pages/access-control-systems') {
        require_once SITE_ROOT . '/includes/access-control-nationwide.php';
        acnRenderHub();
        return true;
    }
    if (preg_match('#^/pages/access-control-systems/([a-z0-9\-]+)$#', $path, $m)) {
        require_once SITE_ROOT . '/includes/access-control-nationwide.php';
        $city = acnCity($m[1]);
        if ($city === null) {
            http_response_code(404);
            echo 'Access control city not found';
            return true;
        }
        acnRenderCity($city);
        return true;
    }

    // /pages/barriers/{town} — census places only. Unknown slugs 404.
    if (preg_match('#^/pages/barriers/([a-z0-9\-]+)$#', $path, $m)) {
        require_once SITE_ROOT . '/includes/barriers.php';
        renderBarriersPlacePage($m[1]);
        return true;
    }
    // /pages/manufacturers/{slug}/{area}
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        renderManufacturerAreaPage($m[1], $m[2]);
        return true;
    }
    // /pages/manufacturers/{slug}
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)$#', $path, $m)) {
        renderManufacturerPage($m[1]);
        return true;
    }
    // /pages/areas/{slug}
    if (preg_match('#^/pages/areas/([a-z0-9\-]+)$#', $path, $m)) {
        $area = areaFromSlug($m[1]);
        if ($area === null) {
            foreach (getAreas() as $a) {
                if (areaSlug($a) === $m[1]) {
                    $area = $a;
                    break;
                }
            }
        }
        if ($area !== null) {
            renderAreaHubPage($area);
            return true;
        }
        return false;
    }
    // /pages/{service}/{area}
    if (preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        $serviceSlug = $m[1];
        $areaSlugVal = $m[2];
        $reserved = ['keywords', 'services', 'manufacturers', 'areas', 'resources', 'packages', 'jobs'];
        if (in_array($serviceSlug, $reserved, true)) {
            return false;
        }
        $services = getServices();
        if (!isset($services[$serviceSlug])) {
            return false;
        }
        $allowed = function_exists('getAreasForService') ? getAreasForService($serviceSlug) : getAreas();
        $area = null;
        foreach ($allowed as $a) {
            if (areaSlug((string)$a) === $areaSlugVal) {
                $area = (string)$a;
                break;
            }
        }
        if ($area === null && $serviceSlug === 'nurse-call' && function_exists('nationwideAreaRow')) {
            $row = nationwideAreaRow($areaSlugVal);
            if ($row !== null) {
                $area = $row['name'];
            }
        }
        if ($area === null) {
            return false;
        }
        renderServiceAreaPage($serviceSlug, $area);
        return true;
    }
    // /pages/{service-slug} → canonical /pages/services/{slug}
    if (preg_match('#^/pages/([a-z0-9\-]+)$#', $path, $m)) {
        $slug = $m[1];
        $reserved = ['keywords', 'services', 'manufacturers', 'areas', 'resources', 'packages', 'jobs'];
        if (!in_array($slug, $reserved, true) && isset(getServices()[$slug])) {
            header('Location: ' . url('/pages/services/' . $slug), true, 301);
            icomplyRequestExit();
            return true;
        }
    }

    // Trade PDPs (Netlify also rewrites these in _redirects before splat)
    if (preg_match('#^/products/product/([a-z0-9\-]+)$#', $path, $m)) {
        $_GET['handle'] = $m[1];
        return routerTryFile('/products/product');
    }
    if (preg_match('#^/shop/products/([a-z0-9\-]+)$#', $path, $m)) {
        $_GET['handle'] = $m[1];
        return routerTryFile('/shop/products');
    }
    // Product sitemaps
    if ($path === '/products/sitemap') {
        return routerTryFile('/products/sitemap');
    }
    if ($path === '/shop/sitemap') {
        return routerTryFile('/shop/sitemap');
    }

    return false;
}

/**
 * Full dispatch. Call from front controller.
 */
function routerHandleRequest(): void {
    $path = routerRequestPath();

    // Legacy aliases (also in netlify.toml / _redirects)
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
    if (isset($legacyAliases[$path])) {
        header('Location: ' . url($legacyAliases[$path]), true, 301);
        icomplyRequestExit();
        return;
    }

    // Home
    if ($path === '/' || $path === '/index') {
        require SITE_ROOT . '/index.php';
        return;
    }

    // Block internals
    if (preg_match('#^/(bin|templates|data|includes|admin)(/|$)#i', $path)) {
        // allow admin UI
        if (preg_match('#^/admin#i', $path)) {
            if (routerTryFile($path)) {
                return;
            }
        }
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }

    // Virtual dynamic routes first (no stub required)
    if (routerDispatchVirtual($path)) {
        return;
    }

    // Physical pages (extensionless → .php)
    if (routerTryFile($path)) {
        return;
    }

    // Root-level landings: /about, /contact, etc.
    if (preg_match('#^/([a-z0-9\-]+)$#', $path, $m)) {
        if (routerTryFile('/' . $m[1])) {
            return;
        }
        // pages/* landings
        if (routerTryFile('/pages/' . $m[1])) {
            return;
        }
    }

    http_response_code(404);
    if (is_file(SITE_ROOT . '/404.php')) {
        require SITE_ROOT . '/404.php';
    } else {
        echo 'Not found';
    }
}
