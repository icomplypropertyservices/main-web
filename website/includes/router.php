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
    // Directory indexes (url() strips /index)
    $indexes = [
        '/pages/keywords' => '/pages/keywords/index',
        '/pages/services' => '/pages/services/index',
        '/pages/manufacturers' => '/pages/manufacturers/index',
        '/pages/areas' => '/pages/areas/index',
        '/pages/resources' => '/pages/resources/index',
        '/shop' => '/shop/index',
    ];
    if (isset($indexes[$path])) {
        return routerTryFile($indexes[$path]);
    }

    // /pages/keywords/{kw}/{area}
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        renderKeywordAreaPage($m[1], $m[2]);
        return true;
    }
    // /pages/keywords/{kw}
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)$#', $path, $m)) {
        renderKeywordPage($m[1]);
        return true;
    }
    // /pages/services/{slug}
    if (preg_match('#^/pages/services/([a-z0-9\-]+)$#', $path, $m)) {
        renderServiceHubPage($m[1]);
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
        $reserved = ['keywords', 'services', 'manufacturers', 'areas', 'resources'];
        if (in_array($serviceSlug, $reserved, true)) {
            return false;
        }
        $services = getServices();
        if (!isset($services[$serviceSlug])) {
            return false;
        }
        $area = null;
        foreach (getAreas() as $a) {
            if (areaSlug($a) === $areaSlugVal) {
                $area = $a;
                break;
            }
        }
        if ($area === null) {
            // allow loose slug
            $area = areaFromSlug($areaSlugVal) ?? keywordDisplayName($areaSlugVal);
        }
        renderServiceAreaPage($serviceSlug, $area);
        return true;
    }
    return false;
}

/**
 * Full dispatch. Call from front controller.
 */
function routerHandleRequest(): void {
    $path = routerRequestPath();

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
