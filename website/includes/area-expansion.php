<?php
/**
 * Extra localities for service×area pages.
 *
 * These records are NOT merged into getAreas(). The keyword×town matrix,
 * homepage town list and XML sitemap stay on the core 168 towns.
 * Static export adds /pages/{service}/{slug} for every core service, plus
 * an area hub so those pages do not link to a 404.
 *
 * Buckets live in data/area-expansion/*.json. Draft only — do not promote.
 */
declare(strict_types=1);

/**
 * @return array<string, array{slug:string,name:string,districts:string,region:string,stock:string,travel:string,focus:string,bucket:string}>
 */
function icomplyAreaExpansionRecords(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $rows = [];
    $dir = SITE_ROOT . '/data/area-expansion';
    if (!is_dir($dir)) {
        return $rows;
    }
    $files = glob($dir . '/*.json') ?: [];
    sort($files);
    foreach ($files as $file) {
        $data = json_decode((string)file_get_contents($file), true);
        if (!is_array($data)) {
            continue;
        }
        $bucket = basename($file, '.json');
        foreach ($data as $slug => $meta) {
            if (!is_string($slug) || !is_array($meta)) {
                continue;
            }
            $slug = areaSlug($slug);
            $name = trim((string)($meta['name'] ?? ''));
            if ($name === '') {
                $name = keywordDisplayName($slug);
            }
            $rows[$slug] = [
                'slug' => $slug,
                'name' => $name,
                'districts' => trim((string)($meta['districts'] ?? ('local postcodes around ' . $name))),
                'region' => trim((string)($meta['region'] ?? 'North West England')),
                'stock' => trim((string)($meta['stock'] ?? 'mixed housing and small commercial buildings')),
                'travel' => trim((string)($meta['travel'] ?? 'scheduled from our Stockport SK2 base')),
                'focus' => trim((string)($meta['focus'] ?? 'local compliance and maintenance')),
                'bucket' => $bucket,
            ];
        }
    }
    return $rows;
}

/**
 * Expansion localities that are not already in areas.json.
 *
 * @return array<string, array{slug:string,name:string,districts:string,region:string,stock:string,travel:string,focus:string,bucket:string}>
 */
function icomplyExpansionOnlyRecords(): array
{
    $core = [];
    if (function_exists('getAreas')) {
        foreach (getAreas() as $area) {
            $core[areaSlug((string)$area)] = true;
        }
    }
    $out = [];
    foreach (icomplyAreaExpansionRecords() as $slug => $row) {
        if (!isset($core[$slug])) {
            $out[$slug] = $row;
        }
    }
    return $out;
}

function icomplyIsExpansionOnlyArea(string $area): bool
{
    $slug = areaSlug($area);
    $rows = icomplyAreaExpansionRecords();
    if (!isset($rows[$slug]) && function_exists('getAreas')) {
        foreach ($rows as $row) {
            if (strcasecmp($row['name'], $area) === 0) {
                $slug = $row['slug'];
                break;
            }
        }
    }
    return isset(icomplyExpansionOnlyRecords()[$slug]);
}

/**
 * Paths added to the static export for expansion-only localities.
 * Core towns already receive service×area routes from getAreas().
 *
 * @return list<string>
 */
function icomplyExpansionExportPaths(): array
{
    if (!function_exists('getServices')) {
        return [];
    }
    $paths = [];
    $services = array_keys(getServices());
    foreach (icomplyExpansionOnlyRecords() as $slug => $row) {
        $paths[] = '/pages/areas/' . $slug;
        foreach ($services as $serviceSlug) {
            $paths[] = '/pages/' . $serviceSlug . '/' . $slug;
        }
    }
    return $paths;
}
