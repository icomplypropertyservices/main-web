<?php
/**
 * Fire protection — emergency lighting owns every published UK mainland area.
 *
 * England, Wales and mainland Scotland. Not Northern Ireland, the Scottish
 * Highlands, Scottish islands, the Isles of Scilly, the Isle of Man or the
 * Channel Islands. Other services stay on getAreas() (North West).
 */
declare(strict_types=1);

function nationwideMainlandServiceSlug(): string
{
    return 'emergency-lighting';
}

function isNationwideMainlandService(string $slug): bool
{
    return areaSlug($slug) === nationwideMainlandServiceSlug();
}

/**
 * @return list<array{name:string,country:string,county:string}>
 */
function mainlandAreaRecords(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $data = loadJsonData('mainland-areas', []);
    $list = $data['areas'] ?? $data;
    $rows = [];
    if (!is_array($list)) {
        return $rows;
    }
    foreach ($list as $row) {
        if (is_string($row)) {
            $name = trim($row);
            $country = '';
            $county = '';
        } elseif (is_array($row)) {
            $name = trim((string)($row['name'] ?? ''));
            $country = trim((string)($row['country'] ?? ''));
            $county = trim((string)($row['county'] ?? ''));
        } else {
            continue;
        }
        if ($name === '') {
            continue;
        }
        $rows[] = ['name' => $name, 'country' => $country, 'county' => $county];
    }
    return $rows;
}

/** @return array{name:string,country:string,county:string}|null */
function mainlandAreaRecord(string $nameOrSlug): ?array
{
    $slug = areaSlug($nameOrSlug);
    foreach (mainlandAreaRecords() as $row) {
        if (areaSlug($row['name']) === $slug || strcasecmp($row['name'], $nameOrSlug) === 0) {
            return $row;
        }
    }
    return null;
}

/** @return list<string> */
function getMainlandAreas(): array
{
    $names = [];
    foreach (mainlandAreaRecords() as $row) {
        $names[] = $row['name'];
    }
    return $names;
}

/**
 * Areas a service page may link and render.
 * Emergency lighting: full mainland set. Everything else: North West getAreas().
 *
 * @return list<string>
 */
function getAreasForService(string $serviceSlug): array
{
    if (isNationwideMainlandService($serviceSlug)) {
        return getMainlandAreas();
    }
    return getAreas();
}

/**
 * Canonical display name for a service × area slug, or null when that
 * service does not own the place.
 */
function resolveServiceAreaName(string $serviceSlug, string $nameOrSlug): ?string
{
    $serviceSlug = areaSlug($serviceSlug);
    if (isNationwideMainlandService($serviceSlug)) {
        $row = mainlandAreaRecord($nameOrSlug);
        return $row['name'] ?? null;
    }
    $slug = areaSlug($nameOrSlug);
    foreach (getAreas() as $area) {
        if (areaSlug((string)$area) === $slug || strcasecmp((string)$area, $nameOrSlug) === 0) {
            return (string)$area;
        }
    }
    return null;
}
