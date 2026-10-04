<?php
/**
 * Fire risk assessments — UK mainland coverage and the published guide price.
 * Jack: FRA £350. Other services stay on their own pricing rules.
 */
declare(strict_types=1);

function fraServiceSlug(): string
{
    return 'fire-risk-assessments';
}

function isFraService(string $slug): bool
{
    return areaSlug($slug) === fraServiceSlug();
}

/**
 * Published guide price for a standard fire risk assessment.
 * Larger or higher-risk premises are confirmed in writing before the visit.
 */
function fraGuidePrice(): string
{
    $meta = function_exists('getServiceMeta') ? getServiceMeta(fraServiceSlug()) : [];
    $label = trim((string)($meta['guide_price_label'] ?? ''));
    if ($label !== '') {
        return $label;
    }
    $gbp = $meta['guide_price_gbp'] ?? 350;
    if (is_numeric($gbp)) {
        return '£' . number_format((float)$gbp, 0, '.', ',');
    }
    return '£350';
}

function fraGuidePriceGbp(): int
{
    $meta = function_exists('getServiceMeta') ? getServiceMeta(fraServiceSlug()) : [];
    $gbp = $meta['guide_price_gbp'] ?? 350;
    return is_numeric($gbp) ? (int)$gbp : 350;
}

function fraPriceNote(): string
{
    return 'Guide price ' . fraGuidePrice() . ' for a standard fire risk assessment. Larger, multi-storey or higher-risk premises are confirmed in writing before we attend.';
}

/**
 * @return list<array{slug:string,name:string}>
 */
if (!function_exists('getMainlandAreaRecords')) {
function getMainlandAreaRecords(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $named = [];
    if (function_exists('getAreas')) {
        foreach (getAreas() as $name) {
            $name = (string)$name;
            $named[areaSlug($name)] = $name;
        }
    }
    $path = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/mainland-areas.txt';
    $lines = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
    $out = [];
    $seen = [];
    foreach ($lines ?: [] as $line) {
        $line = trim((string)$line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $name = '';
        if (str_contains($line, '|')) {
            [$slugRaw, $name] = explode('|', $line, 2);
            $slug = areaSlug($slugRaw);
            $name = trim($name);
        } else {
            $slug = areaSlug($line);
        }
        if ($slug === '' || isset($seen[$slug])) {
            continue;
        }
        if ($name === '') {
            $name = $named[$slug] ?? keywordDisplayName($slug);
        }
        $seen[$slug] = true;
        $out[] = ['slug' => $slug, 'name' => $name];
    }
    foreach ($named as $slug => $name) {
        if (!isset($seen[$slug])) {
            $out[] = ['slug' => $slug, 'name' => $name];
        }
    }
    $cache = $out;
    return $cache;
}
}

/** @return list<string> */
function getMainlandAreaNames(): array
{
    $names = [];
    foreach (getMainlandAreaRecords() as $row) {
        $names[] = $row['name'];
    }
    return $names;
}

function mainlandAreaName(string $slugOrName): ?string
{
    $slug = areaSlug($slugOrName);
    foreach (getMainlandAreaRecords() as $row) {
        if ($row['slug'] === $slug || strcasecmp($row['name'], $slugOrName) === 0) {
            return $row['name'];
        }
    }
    return null;
}

function fraLocalPath(string $areaOrSlug): string
{
    return '/pages/' . fraServiceSlug() . '/' . areaSlug($areaOrSlug);
}
