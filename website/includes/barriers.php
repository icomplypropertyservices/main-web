<?php
/**
 * Nationwide vehicle-barrier pages for UK towns over 10,000.
 * Place copy comes from website/data/barriers-places.json (ONS / NRS / London boroughs).
 * CAME is the only partner. Other brands are service or replacement.
 */
declare(strict_types=1);

function barrierCameHeroImage(): string
{
    $map = barrierCameImageMap();
    $url = (string)($map['bar-5m-std'] ?? $map['bar-5m-allin'] ?? '');
    if ($url !== '') {
        return $url;
    }
    return 'https://cdn.shopify.com/s/files/1/1073/5550/4972/files/came-gard-gt4.jpg?v=1788884803';
}

/** @return array<string,string> */
function barrierCameImageMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $file = SITE_ROOT . '/data/bar-5m-came-gard-images.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $map = is_array($decoded) ? $decoded : [];
    return $map;
}

function barriersPlaces(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $loaded = loadJsonData('barriers-places', []);
    $rows = [];
    foreach ($loaded as $row) {
        if (!is_array($row) || empty($row['slug'])) {
            continue;
        }
        $rows[] = $row;
    }
    return $rows;
}

function barriersPlaceIndex(): array
{
    static $index = null;
    if ($index !== null) {
        return $index;
    }
    $index = ['slug' => [], 'name' => []];
    foreach (barriersPlaces() as $row) {
        $index['slug'][(string)$row['slug']] = $row;
        $index['name'][mb_strtolower((string)$row['name'])] = (string)$row['slug'];
    }
    return $index;
}

function barriersPlaceBySlug(string $slug): ?array
{
    $slug = areaSlug($slug);
    $index = barriersPlaceIndex();
    return $index['slug'][$slug] ?? null;
}

function barriersPlaceSlugByName(string $name): ?string
{
    $index = barriersPlaceIndex();
    return $index['name'][mb_strtolower(trim($name))] ?? null;
}

/** @return array<string, array<string, list<array>>> */
function barriersPlacesGrouped(): array
{
    $grouped = [];
    foreach (barriersPlaces() as $row) {
        $country = (string)($row['country'] ?? 'UK');
        $region = (string)($row['region'] ?? $country);
        $grouped[$country][$region][] = $row;
    }
    return $grouped;
}

function barriersBrandEntries(): array
{
    $out = [];
    foreach (getManufacturerCatalog() as $slug => $entry) {
        if (!in_array('barriers', $entry['services'] ?? [], true)) {
            continue;
        }
        $out[$slug] = $entry;
    }
    uasort($out, static function ($a, $b) {
        $ap = !empty($a['partner']) ? 0 : 1;
        $bp = !empty($b['partner']) ? 0 : 1;
        if ($ap !== $bp) {
            return $ap <=> $bp;
        }
        return strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
    });
    return $out;
}

function barriersReadingParagraphs(string $reading): array
{
    $reading = trim($reading);
    if ($reading === '') {
        return [];
    }
    $parts = preg_split('/(?<=\.)\s+(?=[A-Z])/', $reading) ?: [$reading];
    $paras = [];
    $buf = [];
    foreach ($parts as $sentence) {
        $sentence = trim((string)$sentence);
        if ($sentence === '') {
            continue;
        }
        $buf[] = $sentence;
        if (count($buf) >= 2) {
            $paras[] = implode(' ', $buf);
            $buf = [];
        }
    }
    if ($buf) {
        $paras[] = implode(' ', $buf);
    }
    return $paras;
}

function barriersFormatCount(int|float|string $n): string
{
    if (!is_numeric($n)) {
        return (string)$n;
    }
    return number_format((float)$n);
}

function barriersNotFound(): void
{
    http_response_code(404);
    require SITE_ROOT . '/404.php';
    icomplyRequestExit();
}

function renderBarriersHubPage(): void
{
    $places = barriersPlaces();
    $grouped = barriersPlacesGrouped();
    $brands = barriersBrandEntries();
    $keywords = [];
    foreach (getMajorKeywords() as $slug => $meta) {
        if (($meta['service'] ?? '') === 'barriers') {
            $keywords[$slug] = $meta;
        }
    }
    require SITE_ROOT . '/templates/barriers-hub.php';
}

function renderBarriersPlacePage(string $slug): void
{
    $place = barriersPlaceBySlug($slug);
    if ($place === null) {
        barriersNotFound();
        return;
    }
    $brands = barriersBrandEntries();
    require SITE_ROOT . '/templates/barriers-place.php';
}

function renderBarriersKeywordPage(string $slug, array $meta): void
{
    $brands = barriersBrandEntries();
    $samples = [];
    foreach (['manchester', 'birmingham', 'leeds', 'glasgow', 'cardiff', 'westminster', 'aberdeen', 'stockport', 'inverness'] as $sampleSlug) {
        $row = barriersPlaceBySlug($sampleSlug);
        if ($row) {
            $samples[] = $row;
        }
    }
    require SITE_ROOT . '/templates/barriers-keyword.php';
}
