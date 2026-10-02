<?php
/**
 * Extra service×area places that are not part of areas.json.
 * They do not expand the keyword×town matrix and they are not sitemap locs.
 */
declare(strict_types=1);

function coverageAreaBatchFiles(): array
{
    $dir = SITE_ROOT . '/data/area-coverage';
    $files = glob($dir . '/*.json') ?: [];
    sort($files);
    return $files;
}

/**
 * @return list<array{id:string,label:string,first:string,last:string,areas:list<array>}>
 */
function coverageAreaBatches(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    foreach (coverageAreaBatchFiles() as $file) {
        $data = json_decode((string)file_get_contents($file), true);
        if (!is_array($data) || empty($data['areas']) || !is_array($data['areas'])) {
            continue;
        }
        $cache[] = $data;
    }
    return $cache;
}

/**
 * @return array<string, array{slug:string,name:string,profile?:array,batch:string}>
 */
function coverageAreaIndex(): array
{
    static $index = null;
    if ($index !== null) {
        return $index;
    }
    $index = [];
    foreach (coverageAreaBatches() as $batch) {
        $id = (string)($batch['id'] ?? '');
        foreach ($batch['areas'] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = areaSlug((string)($row['slug'] ?? ''));
            $name = trim((string)($row['name'] ?? ''));
            if ($slug === '' || $name === '') {
                continue;
            }
            $index[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'profile' => is_array($row['profile'] ?? null) ? $row['profile'] : [],
                'batch' => $id,
            ];
        }
    }
    return $index;
}

function coverageAreaName(string $slug): ?string
{
    $slug = areaSlug($slug);
    $row = coverageAreaIndex()[$slug] ?? null;
    return $row['name'] ?? null;
}

function isCoverageBatchArea(string $areaOrSlug): bool
{
    return isset(coverageAreaIndex()[areaSlug($areaOrSlug)]);
}

/** In a coverage batch and not already a core town in areas.json. */
function isCoverageOnlyArea(string $areaOrSlug): bool
{
    $slug = areaSlug($areaOrSlug);
    if (!isset(coverageAreaIndex()[$slug])) {
        return false;
    }
    foreach (getAreas() as $area) {
        if (areaSlug((string)$area) === $slug) {
            return false;
        }
    }
    return true;
}

/**
 * Local profile override for a coverage place. Empty when none was written.
 *
 * @return array{districts:string,region:string,stock:string,travel:string,focus:string}
 */
function coverageAreaProfile(string $areaOrSlug): array
{
    $row = coverageAreaIndex()[areaSlug($areaOrSlug)] ?? null;
    if ($row === null) {
        return [];
    }
    $profile = $row['profile'];
    $out = [];
    foreach (['districts', 'region', 'stock', 'travel', 'focus'] as $key) {
        if (!empty($profile[$key]) && is_string($profile[$key])) {
            $out[$key] = $profile[$key];
        }
    }
    return $out;
}

/**
 * @return list<array{slug:string,name:string,batch:string}>
 */
function coverageOnlyAreas(): array
{
    $out = [];
    foreach (coverageAreaIndex() as $slug => $row) {
        if (!isCoverageOnlyArea($slug)) {
            continue;
        }
        $out[] = [
            'slug' => $slug,
            'name' => $row['name'],
            'batch' => $row['batch'],
        ];
    }
    return $out;
}

/**
 * /pages/{service}/{area} for every coverage place × every core service.
 *
 * @return list<string>
 */
function coverageServiceAreaPaths(): array
{
    $paths = [];
    foreach (coverageAreaIndex() as $slug => $_row) {
        foreach (array_keys(getServices()) as $serviceSlug) {
            $paths[] = '/pages/' . $serviceSlug . '/' . $slug;
        }
    }
    return $paths;
}

/**
 * Area hubs for coverage places that are not already in areas.json.
 *
 * @return list<string>
 */
/** Local service link: coverage places go to /pages/{service}/{area}. Core towns stay on the existing 200 URL. */
function coverageServiceLocalUrl(string $serviceSlug, string $area): string
{
    if (isCoverageOnlyArea($area)) {
        return url('/pages/' . areaSlug($serviceSlug) . '/' . areaSlug($area));
    }
    return exportedServiceLocalUrl($serviceSlug, $area, 'area');
}

function coverageOnlyAreaHubPaths(): array
{
    $paths = [];
    foreach (coverageOnlyAreas() as $row) {
        $paths[] = '/pages/areas/' . $row['slug'];
    }
    return $paths;
}
