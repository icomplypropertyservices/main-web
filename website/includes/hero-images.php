<?php
/**
 * Primary hero assignment for lanes that must not share one stock photo.
 *
 * v1 lanes: AOV (service hub, service × town, keyword guides, keyword × town)
 * and vehicle-barrier guides (car-park barrier + gate access).
 *
 * Threshold: the same image src is allowed on at most HERO_DUP_MAX (8) pages
 * in a single lane. Near neighbours are towns in the same cluster of
 * HERO_CLUSTER_SIZE (8) consecutive entries in data/areas.json, or keyword
 * guides that sit next to each other in that lane. Assignment is modulo a
 * pool of at least 21 distinct files, so a cluster of 8 never repeats and a
 * 168-town lane stays at 7 uses per file (under the cap of 8).
 *
 * Manufacturer cards are not given these stock photos. A missing brand file
 * stays blank rather than borrowing another brand's picture.
 */
declare(strict_types=1);

const HERO_DUP_MAX = 8;
const HERO_CLUSTER_SIZE = 8;

function icomplyHeroConfig(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }
    $path = SITE_ROOT . '/data/hero-pools.json';
    $raw = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
    $cfg = is_array($raw) ? $raw : ['pools' => [], 'service_pools' => [], 'barrier_keywords' => []];
    return $cfg;
}

/** @return list<string> */
function icomplyHeroPoolFiles(string $pool): array
{
    $files = icomplyHeroConfig()['pools'][$pool] ?? [];
    if (!is_array($files)) {
        return [];
    }
    $out = [];
    foreach ($files as $rel) {
        if (!is_string($rel) || $rel === '') {
            continue;
        }
        $rel = '/' . ltrim($rel, '/');
        if (is_file(SITE_ROOT . $rel)) {
            $out[] = $rel;
        }
    }
    return $out;
}

function icomplyPoolForService(string $serviceSlug): ?string
{
    $map = icomplyHeroConfig()['service_pools'] ?? [];
    $pool = $map[$serviceSlug] ?? null;
    if (!is_string($pool) || $pool === '') {
        return null;
    }
    return icomplyHeroPoolFiles($pool) ? $pool : null;
}

function icomplyPoolForKeyword(string $keywordSlug, string $serviceSlug): ?string
{
    $barriers = icomplyHeroConfig()['barrier_keywords'] ?? [];
    if (is_array($barriers) && in_array($keywordSlug, $barriers, true)) {
        return icomplyHeroPoolFiles('barriers') ? 'barriers' : null;
    }
    return icomplyPoolForService($serviceSlug);
}

function icomplyIndexedHero(string $pool, int $index): string
{
    $files = icomplyHeroPoolFiles($pool);
    $n = count($files);
    if ($n < 1) {
        return '';
    }
    $i = $index % $n;
    if ($i < 0) {
        $i += $n;
    }
    return $files[$i];
}

function icomplyTownIndex(string $area): int
{
    $want = function_exists('areaSlug') ? areaSlug($area) : strtolower($area);
    $areas = function_exists('getAreas') ? getAreas() : [];
    foreach ($areas as $i => $name) {
        $slug = function_exists('areaSlug') ? areaSlug((string) $name) : strtolower((string) $name);
        if ($slug === $want || strcasecmp((string) $name, $area) === 0) {
            return (int) $i;
        }
    }
    return abs(crc32($want));
}

/** @return list<string> */
function icomplyLaneKeywords(string $pool): array
{
    static $cache = [];
    if (isset($cache[$pool])) {
        return $cache[$pool];
    }
    $slugs = [];
    if ($pool === 'barriers') {
        $list = icomplyHeroConfig()['barrier_keywords'] ?? [];
        foreach ($list as $slug) {
            if (is_string($slug) && $slug !== '') {
                $slugs[] = $slug;
            }
        }
        $cache[$pool] = $slugs;
        return $slugs;
    }
    $service = null;
    foreach (icomplyHeroConfig()['service_pools'] ?? [] as $svc => $poolName) {
        if ($poolName === $pool) {
            $service = (string) $svc;
            break;
        }
    }
    if ($service !== null && function_exists('getKeywordsForService')) {
        $slugs = array_keys(getKeywordsForService($service));
        sort($slugs);
    }
    $cache[$pool] = $slugs;
    return $slugs;
}

function icomplyGuideOrdinal(string $pool, string $keywordSlug): int
{
    $slugs = icomplyLaneKeywords($pool);
    $pos = array_search($keywordSlug, $slugs, true);
    if ($pos === false) {
        return abs(crc32($pool . '|' . $keywordSlug)) % 997;
    }
    return (int) $pos;
}

/**
 * Shift for a keyword × town hero. Never equal to 1, which is the service × town
 * shift, so the same town's service page and keyword page do not share a src.
 */
function icomplyKeywordTownShift(string $pool, int $ordinal): int
{
    $n = count(icomplyHeroPoolFiles($pool));
    if ($n < 2) {
        return 0;
    }
    $x = $ordinal % ($n - 1);
    return $x === 0 ? 0 : $x + 1;
}

function icomplyAreaHero(string $serviceSlug, string $area): ?string
{
    $pool = icomplyPoolForService($serviceSlug);
    if ($pool === null) {
        return null;
    }
    return icomplyIndexedHero($pool, icomplyTownIndex($area) + 1);
}

function icomplyAreaInline(string $serviceSlug, string $area, int $slot): ?string
{
    $pool = icomplyPoolForService($serviceSlug);
    if ($pool === null) {
        return null;
    }
    $extra = $slot === 2 ? 17 : ($slot === 3 ? 13 : 9);
    return icomplyIndexedHero($pool, icomplyTownIndex($area) + 1 + $extra);
}

function icomplyHubHero(string $serviceSlug): ?string
{
    $pool = icomplyPoolForService($serviceSlug);
    if ($pool === null) {
        return null;
    }
    return icomplyIndexedHero($pool, 0);
}

function icomplyHubInline(string $serviceSlug, int $slot): ?string
{
    // Property SEO pack services: on-topic inline images from the pack.
    if (!function_exists('icomplyPropertyPackServiceImages')) {
        require_once __DIR__ . '/property-packs.php';
    }
    $packImages = icomplyPropertyPackServiceImages($serviceSlug);
    if ($packImages !== null) {
        return $packImages[$slot === 2 ? 2 : 1];
    }
    $pool = icomplyPoolForService($serviceSlug);
    if ($pool === null) {
        return null;
    }
    $extra = $slot === 2 ? 10 : 5;
    return icomplyIndexedHero($pool, $extra);
}

/** @return array{primary:string,inline:string,pool:?string} */
function icomplyKeywordHero(string $keywordSlug, string $serviceSlug, string $area = ''): array
{
    $fallback = [
        'primary' => '/assets/images/keywords/' . $keywordSlug . '.jpg',
        'inline' => '/assets/images/services/' . ($serviceSlug !== '' ? $serviceSlug : 'fire-alarms') . '.jpg',
        'pool' => null,
    ];
    $pool = icomplyPoolForKeyword($keywordSlug, $serviceSlug);
    if ($pool === null) {
        return $fallback;
    }
    $ord = icomplyGuideOrdinal($pool, $keywordSlug);
    if ($area === '') {
        return [
            'primary' => icomplyIndexedHero($pool, $ord + 2),
            'inline' => icomplyIndexedHero($pool, $ord + 2 + 9),
            'pool' => $pool,
        ];
    }
    $shift = icomplyKeywordTownShift($pool, $ord);
    return [
        'primary' => icomplyIndexedHero($pool, icomplyTownIndex($area) + $shift),
        'inline' => icomplyIndexedHero($pool, icomplyTownIndex($area) + $shift + 9),
        'pool' => $pool,
    ];
}
