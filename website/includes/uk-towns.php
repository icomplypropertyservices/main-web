<?php
/**
 * UK mainland towns with population over 10,000.
 * Source notes: data/UK-MAINLAND-TOWNS-10K.md
 */
declare(strict_types=1);

function icomplyUkTownDataset(): array
{
    $data = loadJsonData('uk-mainland-towns-10k', []);
    return is_array($data) ? $data : [];
}

/** @return list<array<string,mixed>> */
function icomplyUkTowns(): array
{
    static $towns = null;
    if ($towns !== null) {
        return $towns;
    }
    $rows = icomplyUkTownDataset()['towns'] ?? [];
    $towns = [];
    foreach ($rows as $row) {
        if (!is_array($row) || empty($row['slug']) || empty($row['name'])) {
            continue;
        }
        $pop = (int)($row['population'] ?? 0);
        if ($pop <= 10000) {
            continue;
        }
        $towns[] = $row;
    }
    return $towns;
}

function icomplyUkTownBySlug(string $slug): ?array
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (icomplyUkTowns() as $town) {
            $index[(string)$town['slug']] = $town;
        }
    }
    $slug = function_exists('areaSlug') ? areaSlug($slug) : strtolower($slug);
    return $index[$slug] ?? null;
}

function icomplyHaversineMiles(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earth = 3958.8;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return $earth * (2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a))));
}

/** Offerton workshop, Stockport SK2 — approximate, for travel context. */
function icomplyStockportWorkshop(): array
{
    return ['lat' => 53.405, 'lng' => -2.158];
}

function icomplyMilesFromStockport(array $town): float
{
    $base = icomplyStockportWorkshop();
    return icomplyHaversineMiles(
        (float)$base['lat'],
        (float)$base['lng'],
        (float)($town['lat'] ?? $base['lat']),
        (float)($town['lng'] ?? $base['lng'])
    );
}

/**
 * @return list<array{name:string,slug:string,miles:float}>
 */
function icomplyUkTownNeighbours(string $slug, int $limit = 3): array
{
    static $cache = [];
    $key = $slug . ':' . $limit;
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    $town = icomplyUkTownBySlug($slug);
    if ($town === null) {
        return [];
    }
    $ranked = [];
    foreach (icomplyUkTowns() as $other) {
        if (($other['slug'] ?? '') === $slug) {
            continue;
        }
        $miles = icomplyHaversineMiles(
            (float)($town['lat'] ?? 0),
            (float)($town['lng'] ?? 0),
            (float)($other['lat'] ?? 0),
            (float)($other['lng'] ?? 0)
        );
        $ranked[] = [
            'name' => (string)$other['name'],
            'slug' => (string)$other['slug'],
            'miles' => $miles,
        ];
    }
    usort($ranked, static fn(array $a, array $b): int => $a['miles'] <=> $b['miles']);
    $cache[$key] = array_slice($ranked, 0, $limit);
    return $cache[$key];
}

/** @return list<string> */
function icomplyUkTownRoutes(): array
{
    $routes = ['/pages/aov', '/pages/barriers'];
    foreach (icomplyUkTowns() as $town) {
        $slug = (string)$town['slug'];
        $routes[] = '/pages/aov/' . $slug;
        $routes[] = '/pages/barriers/' . $slug;
    }
    return $routes;
}
