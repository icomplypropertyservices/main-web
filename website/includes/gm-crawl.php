<?php
/**
 * Greater Manchester crawl allowlist.
 * Sitemap locs, nav, and matrix generation use these 60 towns.
 * Any other town matrix URL 301s to its GM parent hub.
 */
declare(strict_types=1);

function icomplyEnsureGmTownHelpers(): void
{
    if (!function_exists('icomplyGreaterManchesterTownNames')) {
        $file = __DIR__ . '/building-hub-copy.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}

/** @return list<string> */
function icomplyCrawlTownNames(): array
{
    icomplyEnsureGmTownHelpers();
    if (!function_exists('icomplyGreaterManchesterTownNames')) {
        return [];
    }
    return icomplyGreaterManchesterTownNames();
}

function icomplyCrawlTownSlug(string $areaOrSlug): bool
{
    return function_exists('icomplyIsGreaterManchesterAreaSlug')
        && icomplyIsGreaterManchesterAreaSlug($areaOrSlug);
}

/**
 * Non-GM matrix URL → the GM parent. Null means the URL stays (hub or GM town).
 */
function icomplyNonGmMatrixRedirect(string $path): ?string
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = preg_replace('#/index$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';

    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[2]) ? null : '/pages/keywords/' . $m[1];
    }
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[2]) ? null : '/pages/manufacturers/' . $m[1];
    }
    if (preg_match('#^/pages/jobs/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[2]) ? null : '/pages/jobs/' . $m[1];
    }
    if (preg_match('#^/pages/areas/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[1]) ? null : '/pages/areas';
    }
    if (preg_match('#^/pages/aov/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[1]) ? null : '/pages/aov';
    }
    if (preg_match('#^/pages/barriers/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[1]) ? null : '/pages/services/barriers';
    }
    if (preg_match('#^/pages/aov-air-handling/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[1]) ? null : '/pages/services/aov-air-handling';
    }
    if (preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        $reserved = [
            'keywords', 'services', 'areas', 'manufacturers', 'resources', 'packages',
            'jobs', 'commercial', 'aov', 'barriers', 'products', 'shop',
            'emergency-lighting-jobs', 'asbestos-jobs',
        ];
        if (in_array($m[1], $reserved, true)) {
            return null;
        }
        if (function_exists('getServices') && !isset(getServices()[$m[1]])) {
            return null;
        }
        return icomplyCrawlTownSlug($m[2]) ? null : '/pages/services/' . $m[1];
    }
    return null;
}

function icomplySitemapOmitsNonGm(string $path): bool
{
    return icomplyNonGmMatrixRedirect($path) !== null;
}
