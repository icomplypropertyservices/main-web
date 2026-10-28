<?php
/**
 * Local crawl allowlists.
 *
 * Dual-ring 269 (GM core ∪ 50 miles of Manchester ∪ 50 miles of Burnley):
 * area hubs, local service×town, keyword×town and job×town.
 * Manufacturer×town stays on the Greater Manchester core of 60.
 * AOV, barriers and fire-family town pages stay UK-wide.
 * Security P0 keyword×town and job×town stay on the dual 269 ring.
 */
declare(strict_types=1);

require_once __DIR__ . '/nationwide-3line.php';
require_once __DIR__ . '/fire-alarm-installer.php';
require_once __DIR__ . '/margin-deep.php';

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

/**
 * True when a service×town URL is actually published.
 * Fire-family services use the mainland list. Every other service stays on the 269 dual-ring towns.
 */
function icomplyTownPublishedForService(string $serviceSlug, string $areaOrSlug): bool
{
    $slug = function_exists('areaSlug') ? areaSlug($areaOrSlug) : strtolower(trim($areaOrSlug));
    if ($slug === '') {
        return false;
    }
    if (function_exists('serviceOwnsMainlandAreas') && serviceOwnsMainlandAreas($serviceSlug)) {
        static $mainland = null;
        if ($mainland === null) {
            $mainland = [];
            if (function_exists('getMainlandAreaRecords')) {
                foreach (getMainlandAreaRecords() as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $name = (string)($row['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $mainland[function_exists('areaSlug') ? areaSlug($name) : strtolower($name)] = true;
                }
            }
        }
        if ($serviceSlug === 'aov-air-handling') {
            return isset($mainland[$slug]);
        }
        return isset($mainland[$slug]) || icomplyLocalTownSlug($slug);
    }
    return icomplyLocalTownSlug($slug);
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
/** Jack 2026-10-28: keyword/job x town pages stay live (200) in every dual-ring matrix town, never pruned. */
function icomplyMatrixTownSlug(string $slug): bool
{
    static $set = null;
    if ($set === null) {
        $set = array_fill_keys(array_column(icomplyDualRingTownRows(), 'slug'), true);
    }
    return icomplyCrawlTownSlug($slug) || isset($set[strtolower($slug)]);
}

function icomplyNonGmMatrixRedirect(string $path): ?string
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = preg_replace('#/index$#i', '', $path) ?? $path;
    static $gscKeep = null;
    if ($gscKeep === null) {
        $f = dirname(__DIR__) . '/data/gsc-keep-towns.json';
        $doc = is_file($f) ? json_decode((string)file_get_contents($f), true) : [];
        $gscKeep = array_fill_keys((array)($doc['paths'] ?? []), true);
    }
    if (isset($gscKeep[$path])) {
        return null; // GSC-ranked town page: keep (mirrors edge GSC_KEEP)
    }
    $path = rtrim($path, '/') ?: '/';

    if (!function_exists('securityDualRingHandlesPath')) {
        $security = __DIR__ . '/security-dual-ring.php';
        if (is_file($security)) {
            require_once $security;
        }
    }
    if (!function_exists('icomplyBuildingDualCoversPath') && is_file(__DIR__ . '/building-services-dual.php')) {
        require_once __DIR__ . '/building-services-dual.php';
    }
    if (function_exists('securityDualRingHandlesPath') && securityDualRingHandlesPath($path)) {
        return null;
    }

    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        if (function_exists('icomplyBuildingDualCoversPath') && icomplyBuildingDualCoversPath($path)) {
            return null;
        }
        foreach (['nationwide-3line.php', 'fire-alarm-installer.php', 'aov-barriers-deep.php'] as $inc) {
            if (is_file(__DIR__ . '/' . $inc)) {
                require_once __DIR__ . '/' . $inc;
            }
        }
        // Jack 2026-10-28 superset-never-prune: nationwide fire / AOV / barrier town pages stay.
        if ((function_exists('icomplyNationwide3lineNationwidePath') && icomplyNationwide3lineNationwidePath($path))
            || (function_exists('icomplyFireAlarmInstallerNationwidePath') && icomplyFireAlarmInstallerNationwidePath($path))
            || (function_exists('icomplyAovBarriersDeepKeepsTown') && icomplyAovBarriersDeepKeepsTown($m[1], $m[2]))) {
            return null;
        }
        return icomplyMatrixTownSlug($m[2]) ? null : '/pages/keywords/' . $m[1];
    }
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyCrawlTownSlug($m[2]) ? null : '/pages/manufacturers/' . $m[1];
    }
    if (preg_match('#^/pages/jobs/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        if (function_exists('icomplyBuildingDualCoversPath') && icomplyBuildingDualCoversPath($path)) {
            return null;
        }
        return icomplyMatrixTownSlug($m[2]) ? null : '/pages/jobs/' . $m[1];
    }
    if (preg_match('#^/pages/areas/([a-z0-9\-]+)$#', $path, $m)) {
        return icomplyLocalTownSlug($m[1]) ? null : '/pages/areas';
    }
    if (preg_match('#^/pages/aov-air-handling/([a-z0-9\-]+)$#', $path, $m)) {
        $built = false;
        if (function_exists('getMainlandAreaRecords')) {
            foreach (getMainlandAreaRecords() as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $name = (string)($row['name'] ?? '');
                $rowSlug = function_exists('areaSlug') ? areaSlug($name) : strtolower($name);
                if ($rowSlug === $m[1]) {
                    $built = true;
                    break;
                }
            }
        }
        return $built ? null : '/pages/services/aov-air-handling';
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
