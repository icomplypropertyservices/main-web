<?php
/**
 * Local crawl allowlists.
 *
 * Dual-ring 269 (GM core ∪ 50 miles of Manchester ∪ 50 miles of Burnley):
 * area hubs, local service×town, keyword×town and job×town.
 * Manufacturer×town stays on the Greater Manchester core of 60.
 * AOV, barriers and fire-family town pages stay UK-wide.
 */
declare(strict_types=1);

require_once __DIR__ . '/fire-alarm-installer.php';

/** @return list<array{slug:string,name:string,bucket:string}> */
function icomplyDualRingTownRows(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $file = dirname(__DIR__) . '/data/dual-ring-allowlist.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $rows = [];
    foreach (is_array($decoded['towns'] ?? null) ? $decoded['towns'] : [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = strtolower(trim((string)($row['slug'] ?? '')));
        $name = trim((string)($row['name'] ?? ''));
        if ($slug === '' || $name === '') {
            continue;
        }
        $rows[] = [
            'slug' => $slug,
            'name' => $name,
            'bucket' => (string)($row['bucket'] ?? ''),
        ];
    }
    return $rows;
}

/** @return list<string> */
function icomplyLocalTownNames(): array
{
    $names = [];
    foreach (icomplyDualRingTownRows() as $row) {
        $names[] = $row['name'];
    }
    return $names;
}

function icomplyLocalTownSlug(string $areaOrSlug): bool
{
    static $set = null;
    if ($set === null) {
        $set = [];
        foreach (icomplyDualRingTownRows() as $row) {
            $set[$row['slug']] = true;
        }
    }
    $slug = function_exists('areaSlug') ? areaSlug($areaOrSlug) : strtolower($areaOrSlug);
    return $slug !== '' && isset($set[$slug]);
}

function icomplyLocalTownName(string $areaOrSlug): ?string
{
    static $names = null;
    if ($names === null) {
        $names = [];
        foreach (icomplyDualRingTownRows() as $row) {
            $names[$row['slug']] = $row['name'];
        }
    }
    $slug = function_exists('areaSlug') ? areaSlug($areaOrSlug) : strtolower($areaOrSlug);
    return $names[$slug] ?? null;
}

function icomplyEnsureGmTownHelpers(): void
{
    if (!function_exists('icomplyGreaterManchesterTownNames')) {
        $file = __DIR__ . '/building-hub-copy.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}

/**
 * Greater Manchester core of 60. Manufacturer×town uses this list.
 *
 * @return list<string>
 */
function icomplyCrawlTownNames(): array
{
    icomplyEnsureGmTownHelpers();
    if (!function_exists('icomplyGreaterManchesterTownNames')) {
        return [];
    }
    return icomplyGreaterManchesterTownNames();
}

/** True for the Greater Manchester core. Not the wider dual-ring list. */
function icomplyCrawlTownSlug(string $areaOrSlug): bool
{
    return function_exists('icomplyIsGreaterManchesterAreaSlug')
        && icomplyIsGreaterManchesterAreaSlug($areaOrSlug);
}

/** AOV, barriers and every fire-safety service keep UK town pages. */
function icomplyNationwideTownService(string $slug): bool
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : strtolower($slug);
    if (in_array($slug, ['aov', 'barriers', 'aov-air-handling'], true)) {
        return true;
    }
    return function_exists('isFireSafetyService') && isFireSafetyService($slug);
}

function icomplyNationwideTownPath(string $path): bool
{
    return (bool)preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)
        && icomplyNationwideTownService($m[1]);
}

/**
 * Outside-allowlist matrix URL → the parent hub.
 * Null means the URL stays (hub, dual-ring local page, GM manufacturer page, or nationwide family).
 */
function icomplyNonGmMatrixRedirect(string $path): ?string
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = preg_replace('#/index$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';

    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        if (icomplyFireAlarmInstallerNationwidePath($path)) {
            return null;
        }
        return icomplyLocalTownSlug($m[2]) ? null : '/pages/keywords/' . $m[1];
    }
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[2]) ? null : '/pages/manufacturers/' . $m[1];
    }
    if (preg_match('#^/pages/jobs/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyLocalTownSlug($m[2]) ? null : '/pages/jobs/' . $m[1];
    }
    if (preg_match('#^/pages/areas/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyLocalTownSlug($m[1]) ? null : '/pages/areas';
    }
    if (icomplyNationwideTownPath($path)) {
        return null;
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
        return icomplyLocalTownSlug($m[2]) ? null : '/pages/services/' . $m[1];
    }
    return null;
}

function icomplySitemapOmitsNonGm(string $path): bool
{
    return icomplyNonGmMatrixRedirect($path) !== null;
}
