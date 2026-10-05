<?php
/**
 * Town × keyword and town × service catalogue for the Netlify edge renderer.
 *
 * Pages are not stored as a million HTML files. static-export writes a compact
 * catalogue under dist/assets/matrix/ and a sitemap index whose locs the edge
 * function answers with HTTP 200. Places are the 60 Greater Manchester towns.
 * Non-GM gazetteer rows are not added to reach ICOMPLY_MATRIX_URL_TARGET.
 */
declare(strict_types=1);

const ICOMPLY_MATRIX_URL_TARGET = 1000000;
const ICOMPLY_MATRIX_SITEMAP_CHUNK = 45000;

/** @return list<string> */
function icomplyMatrixExcludedServiceSlugs(): array
{
    return ['barriers', 'aov-air-handling'];
}

function icomplyMatrixSlug(string $value): string
{
    $slug = function_exists('areaSlug') ? areaSlug($value) : strtolower($value);
    return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) ? $slug : '';
}

/** Named here so older catalogue files still load. Sitemap area pages stay on the published town list, which includes Greater Manchester. */
function icomplyMatrixFamilyServiceSlugs(): array
{
    return [
        'fire-alarms', 'fire-risk-assessments', 'fire-extinguishers', 'fire-doors',
        'fire-stopping', 'fire-suppression', 'fire-signage', 'fire-compartmentation',
        'dry-risers', 'emergency-lighting', 'barriers', 'aov-air-handling',
    ];
}

function icomplyMatrixNormalName(string $name): string
{
    $name = preg_replace('/\s*\([^)]*\)/u', '', $name) ?? $name;
    return icomplyMatrixSlug($name);
}

/**
 * @param array<string,array<string,mixed>> $bySlug
 * @param array<string,mixed> $row
 */
function icomplyMatrixRememberPlace(array &$bySlug, string $slug, array $row, int $tier): void
{
    if ($slug === '' || isset($bySlug[$slug])) {
        return;
    }
    $row['slug'] = $slug;
    $row['tier'] = $tier;
    $bySlug[$slug] = $row;
}

/**
 * @return array{selected:array<string,array<string,mixed>>,available:int,remaining:int,skipped_duplicates:int}
 */
function icomplyMatrixSelectPlaces(int $wanted): array
{
    $bySlug = [];

    foreach (getAreas() as $name) {
        $name = (string)$name;
        $slug = icomplyMatrixSlug($name);
        icomplyMatrixRememberPlace($bySlug, $slug, [
            'name' => $name,
            'region' => 'North West',
            'country' => 'England',
            'population' => 0,
            'housing' => '',
            'industry' => '',
            'neighbours' => [],
            'source' => 'areas',
        ], 1);
    }

    $extraFile = SITE_ROOT . '/data/matrix-extra-places.json';
    $extras = is_file($extraFile) ? json_decode((string)file_get_contents($extraFile), true) : [];
    if (is_array($extras)) {
        foreach ($extras as $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = icomplyMatrixSlug((string)($row['slug'] ?? $row['name'] ?? ''));
            $neighbours = [];
            foreach ($row['neighbours'] ?? [] as $neighbour) {
                if (is_string($neighbour) && $neighbour !== '') {
                    $neighbours[] = $neighbour;
                }
            }
            icomplyMatrixRememberPlace($bySlug, $slug, [
                'name' => (string)($row['name'] ?? $slug),
                'region' => (string)($row['region'] ?? 'North West'),
                'country' => (string)($row['country'] ?? 'England'),
                'population' => 0,
                'housing' => '',
                'industry' => '',
                'neighbours' => array_slice($neighbours, 0, 3),
                'source' => 'extra',
            ], 0);
        }
    }

    $regionTier = [
        'North West' => 2,
        'Yorkshire and The Humber' => 3,
        'North East' => 4,
        'West Midlands' => 5,
        'East Midlands' => 6,
        'East of England' => 7,
        'South West' => 8,
        'South East' => 9,
        'London' => 10,
        'Wales' => 11,
    ];
    $barriersFile = SITE_ROOT . '/data/barriers-places.json';
    $barriers = is_file($barriersFile) ? json_decode((string)file_get_contents($barriersFile), true) : [];
    if (is_array($barriers)) {
        foreach ($barriers as $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = icomplyMatrixSlug((string)($row['slug'] ?? ''));
            $region = (string)($row['region'] ?? '');
            $tier = $regionTier[$region] ?? 12;
            $neighbours = [];
            foreach ($row['neighbours'] ?? [] as $neighbour) {
                if (is_array($neighbour) && !empty($neighbour['name'])) {
                    $neighbours[] = (string)$neighbour['name'];
                } elseif (is_string($neighbour) && $neighbour !== '') {
                    $neighbours[] = $neighbour;
                }
            }
            $housing = '';
            if (isset($row['housing'][0]) && is_array($row['housing'][0])) {
                $pct = (float)($row['housing'][0]['pct'] ?? 0);
                $label = strtolower(trim((string)($row['housing'][0]['label'] ?? '')));
                if ($pct > 0 && $label !== '') {
                    $housing = rtrim(rtrim(number_format($pct, 1, '.', ''), '0'), '.') . '% ' . $label;
                }
            }
            $industry = '';
            if (isset($row['industry'][0]) && is_array($row['industry'][0])) {
                $label = strtolower(trim((string)($row['industry'][0]['label'] ?? '')));
                $pct = (float)($row['industry'][0]['pct'] ?? 0);
                if ($label !== '' && $pct > 0) {
                    $industry = rtrim(rtrim(number_format($pct, 1, '.', ''), '0'), '.') . '% ' . $label;
                }
            }
            icomplyMatrixRememberPlace($bySlug, $slug, [
                'name' => (string)($row['name'] ?? $slug),
                'region' => $region !== '' ? $region : 'England',
                'country' => (string)($row['country'] ?? 'England'),
                'population' => (int)($row['population'] ?? 0),
                'housing' => $housing,
                'industry' => $industry,
                'neighbours' => array_slice($neighbours, 0, 3),
                'source' => 'barriers-places',
            ], $tier);
        }
    }

    $mainland = SITE_ROOT . '/data/mainland-areas.txt';
    if (is_file($mainland)) {
        foreach (file($mainland, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $line = trim((string)$line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $slug = icomplyMatrixSlug($line);
            $name = ucwords(str_replace('-', ' ', $slug));
            icomplyMatrixRememberPlace($bySlug, $slug, [
                'name' => $name,
                'region' => 'United Kingdom',
                'country' => 'United Kingdom',
                'population' => 0,
                'housing' => '',
                'industry' => '',
                'neighbours' => [],
                'source' => 'mainland-areas',
            ], 20);
        }
    }

    $gmCopy = SITE_ROOT . '/includes/building-hub-copy.php';
    if (!function_exists('icomplyGreaterManchesterTownNames') && is_file($gmCopy)) {
        require_once $gmCopy;
    }
    if (function_exists('icomplyGreaterManchesterTownNames')) {
        $gmSlugs = [];
        foreach (icomplyGreaterManchesterTownNames() as $gmName) {
            $gmSlug = icomplyMatrixSlug((string)$gmName);
            if ($gmSlug !== '') {
                $gmSlugs[$gmSlug] = (string)$gmName;
            }
        }
        foreach ($bySlug as $slug => $row) {
            if (isset($gmSlugs[$slug])) {
                $bySlug[$slug]['tier'] = 0;
            }
        }
        $gmOnly = [];
        foreach ($gmSlugs as $gmSlug => $gmName) {
            if (isset($bySlug[$gmSlug])) {
                $gmOnly[$gmSlug] = $bySlug[$gmSlug];
                $gmOnly[$gmSlug]['tier'] = 0;
                continue;
            }
            icomplyMatrixRememberPlace($gmOnly, $gmSlug, [
                'name' => $gmName,
                'region' => 'Greater Manchester',
                'country' => 'England',
                'population' => 0,
                'housing' => '',
                'industry' => '',
                'neighbours' => [],
                'source' => 'gm-allowlist',
            ], 0);
        }
        $bySlug = $gmOnly;
    }

    $ranked = array_values($bySlug);
    usort($ranked, static function (array $a, array $b): int {
        $tier = ($a['tier'] ?? 99) <=> ($b['tier'] ?? 99);
        if ($tier !== 0) {
            return $tier;
        }
        return strcmp((string)$a['slug'], (string)$b['slug']);
    });

    $deduped = [];
    $seenNames = [];
    $skippedDuplicates = 0;
    foreach ($ranked as $row) {
        $slug = (string)$row['slug'];
        $nameKey = icomplyMatrixNormalName((string)($row['name'] ?? $slug));
        if (preg_match('/^([a-z0-9]+)-\1$/', $slug) || ($nameKey !== '' && isset($seenNames[$nameKey]))) {
            $skippedDuplicates++;
            continue;
        }
        if ($nameKey !== '') {
            $seenNames[$nameKey] = $slug;
        }
        $deduped[] = $row;
    }

    $available = count($deduped);
    if ($wanted < 1) {
        $wanted = $available;
    }
    $chosen = array_slice($deduped, 0, min($wanted, $available));
    $selected = [];
    $names = [];
    foreach ($chosen as $row) {
        $names[$row['slug']] = (string)$row['name'];
    }
    foreach ($chosen as $row) {
        $neighbours = [];
        foreach ($row['neighbours'] as $neighbour) {
            $neighbourSlug = icomplyMatrixSlug((string)$neighbour);
            if ($neighbourSlug !== '' && isset($names[$neighbourSlug]) && $neighbourSlug !== $row['slug']) {
                $neighbours[] = $names[$neighbourSlug];
            } elseif (is_string($neighbour) && $neighbour !== '' && !isset($bySlug[icomplyMatrixSlug($neighbour)])) {
                $neighbours[] = $neighbour;
            }
            if (count($neighbours) >= 3) {
                break;
            }
        }
        if (count($neighbours) < 2) {
            foreach ($chosen as $other) {
                if ($other['slug'] === $row['slug'] || ($other['region'] ?? '') !== ($row['region'] ?? '')) {
                    continue;
                }
                $neighbours[] = (string)$other['name'];
                if (count($neighbours) >= 3) {
                    break;
                }
            }
        }
        $selected[$row['slug']] = [
            'name' => (string)$row['name'],
            'region' => (string)$row['region'],
            'country' => (string)$row['country'],
            'population' => (int)$row['population'],
            'housing' => (string)$row['housing'],
            'industry' => (string)$row['industry'],
            'neighbours' => array_values(array_unique($neighbours)),
        ];
    }

    return [
        'selected' => $selected,
        'available' => $available,
        'remaining' => max(0, $available - count($selected)),
        'skipped_duplicates' => $skippedDuplicates,
    ];
}

/**
 * UK towns over 10,000. Used for fire, barriers, AOV, nationwide manufacturers and those job types.
 *
 * @return array<string,array<string,mixed>>
 */
function icomplyMatrixNationwidePlaces(): array
{
    $file = SITE_ROOT . '/data/uk-towns-10k.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $places = [];
    if (!is_array($decoded)) {
        return $places;
    }
    foreach ($decoded as $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = icomplyMatrixSlug((string)($row['slug'] ?? ''));
        if ($slug === '' || isset($places[$slug])) {
            continue;
        }
        $workplace = trim((string)($row['workplace'] ?? ''));
        $places[$slug] = [
            'name' => (string)($row['name'] ?? $slug),
            'region' => (string)($row['region'] ?? 'United Kingdom'),
            'country' => (string)($row['nation'] ?? 'United Kingdom'),
            'population' => (int)($row['pop'] ?? 0),
            'housing' => '',
            'industry' => $workplace !== '' ? 'published workplace class ' . strtolower($workplace) : '',
            'neighbours' => [],
        ];
    }
    return $places;
}

function icomplyMatrixManufacturerMode(string $slug, array $entry): ?string
{
    if ($slug === '' || $slug === 'tunstall') {
        return null;
    }
    if (function_exists('manufacturerCoverageMode')) {
        $entry['slug'] = $slug;
        return manufacturerCoverageMode($entry);
    }
    $national = ['fire-alarms', 'aov-air-handling', 'barriers', 'access-control', 'nurse-call'];
    foreach ($entry['services'] ?? [] as $service) {
        if (in_array((string)$service, $national, true)) {
            return 'nationwide';
        }
    }
    return !empty($entry['services']) ? 'local' : null;
}

/**
 * @return array<string,array{name:string,service:string,nationwide:bool}>
 */
function icomplyMatrixManufacturerRecords(): array
{
    $out = [];
    if (!function_exists('getManufacturerCatalog')) {
        return $out;
    }
    foreach (getManufacturerCatalog() as $slug => $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $slug = icomplyMatrixSlug((string)$slug);
        $mode = icomplyMatrixManufacturerMode($slug, $entry);
        if ($mode === null) {
            continue;
        }
        $service = '';
        foreach ($entry['services'] ?? [] as $candidate) {
            $service = icomplyMatrixSlug((string)$candidate);
            if ($service !== '') {
                break;
            }
        }
        if ($service === '') {
            $service = 'building-maintenance';
        }
        $name = trim((string)($entry['name'] ?? ''));
        $out[$slug] = [
            'name' => $name !== '' ? $name : ucwords(str_replace('-', ' ', $slug)),
            'service' => $service,
            'nationwide' => $mode === 'nationwide',
        ];
    }
    return $out;
}

/**
 * Job-type landings under /pages/jobs/{slug}. Nationwide when the trade is fire, barriers or AOV.
 *
 * @return array<string,array{name:string,service:string,gas:bool,nationwide:bool}>
 */
function icomplyMatrixJobRecords(): array
{
    $rows = [
        'bs-5839-maintenance' => ['BS 5839 fire alarm maintenance', 'fire-alarms', false, true],
        'came-gard-gt4' => ['CAME Gard GT4 barrier', 'barriers', false, true],
        'car-park-barrier' => ['Car park barrier', 'barriers', false, true],
        'eicr' => ['EICR', 'electrical', false, false],
        'false-alarm-investigation' => ['False alarm investigation', 'fire-alarms', false, true],
        'fire-alarm-call-out' => ['Fire alarm call out', 'fire-alarms', false, true],
        'fire-alarm-ppm' => ['Fire alarm PPM', 'fire-alarms', false, true],
        'fire-alarm-replacement' => ['Fire alarm replacement', 'fire-alarms', false, true],
        'fire-alarms' => ['Fire alarms', 'fire-alarms', false, true],
        'fire-risk-assessment' => ['Fire risk assessment', 'fire-risk-assessments', false, true],
        'fra' => ['Fire risk assessment', 'fire-risk-assessments', false, true],
        'fra-other' => ['Fire risk assessment, other premises', 'fire-risk-assessments', false, true],
        'gas-safety' => ['Gas safety', 'gas-systems', true, false],
        'gas-safety-cp12' => ['Landlord gas safety certificate', 'gas-systems', true, false],
        'hmo-compliance' => ['HMO compliance', 'landlord-compliance', false, false],
        'hmo-fire-safety' => ['HMO fire safety', 'fire-alarms', false, true],
        'hmo-occupancy' => ['HMO occupancy', 'landlord-compliance', false, false],
        'hmo' => ['HMO', 'landlord-compliance', false, false],
        'landlord-bundle' => ['Landlord compliance bundle', 'landlord-compliance', false, false],
        'landlord-compliance' => ['Landlord compliance', 'landlord-compliance', false, false],
        'landlord-gas-safety' => ['Landlord gas safety', 'gas-systems', true, false],
        'maglock-installation' => ['Maglock installation', 'access-control', false, false],
    ];
    $out = [];
    foreach ($rows as $slug => $row) {
        $out[$slug] = [
            'name' => $row[0],
            'service' => $row[1],
            'gas' => $row[2],
            'nationwide' => $row[3],
        ];
    }
    return $out;
}

/**
 * @return array{keywords:array<string,array<string,mixed>>,services:array<string,string>,service_keywords:array<string,list<string>>}
 */
function icomplyMatrixCatalogueRecords(): array
{
    if (!function_exists('icomplyKeywordRecordIsGas')) {
        $gas = SITE_ROOT . '/includes/gas-legal.php';
        if (is_file($gas)) {
            require_once $gas;
        }
    }
    $keywords = [];
    $serviceKeywords = [];
    foreach (getMajorKeywords() as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $slug = icomplyMatrixSlug((string)$slug);
        if ($slug === '') {
            continue;
        }
        $name = trim((string)($meta['name'] ?? ''));
        if ($name === '') {
            $name = function_exists('keywordDisplayName') ? keywordDisplayName($slug) : ucwords(str_replace('-', ' ', $slug));
        }
        $service = icomplyMatrixSlug((string)($meta['service'] ?? 'electrical'));
        if ($service === '') {
            $service = 'electrical';
        }
        $intro = trim((string)($meta['intro'] ?? ''));
        if (function_exists('mb_substr')) {
            $intro = mb_substr($intro, 0, 320);
        } else {
            $intro = substr($intro, 0, 320);
        }
        $focus = [];
        foreach ($meta['focus_points'] ?? [] as $point) {
            $point = trim((string)$point);
            if ($point === '') {
                continue;
            }
            $focus[] = function_exists('mb_substr') ? mb_substr($point, 0, 160) : substr($point, 0, 160);
            if (count($focus) >= 4) {
                break;
            }
        }
        $gasTopic = function_exists('icomplyKeywordRecordIsGas')
            ? icomplyKeywordRecordIsGas($meta, $slug)
            : ($service === 'gas-systems');
        $keywords[$slug] = [
            'name' => $name,
            'service' => $service,
            'intro' => $intro,
            'focus' => $focus,
            'gas' => (bool)$gasTopic,
        ];
        $serviceKeywords[$service][] = $slug;
    }

    $extraKeywords = SITE_ROOT . '/data/matrix-extra-keywords.json';
    if (is_file($extraKeywords)) {
        $decoded = json_decode((string)file_get_contents($extraKeywords), true);
        if (is_array($decoded)) {
            foreach ($decoded as $slug => $meta) {
                if (!is_array($meta)) {
                    continue;
                }
                $slug = icomplyMatrixSlug((string)$slug);
                if ($slug === '' || isset($keywords[$slug])) {
                    continue;
                }
                $service = icomplyMatrixSlug((string)($meta['service'] ?? 'building-maintenance'));
                if ($service === '') {
                    $service = 'building-maintenance';
                }
                $focus = [];
                foreach ($meta['focus'] ?? [] as $point) {
                    $point = trim((string)$point);
                    if ($point !== '') {
                        $focus[] = $point;
                    }
                }
                $keywords[$slug] = [
                    'name' => trim((string)($meta['name'] ?? '')) !== '' ? trim((string)$meta['name']) : ucwords(str_replace('-', ' ', $slug)),
                    'service' => $service,
                    'intro' => trim((string)($meta['intro'] ?? '')),
                    'focus' => array_slice($focus, 0, 4),
                    'gas' => false,
                ];
                $serviceKeywords[$service][] = $slug;
            }
        }
    }

    $services = [];
    $labels = [];
    $excluded = array_fill_keys(icomplyMatrixExcludedServiceSlugs(), true);
    foreach (getServices() as $slug => $label) {
        $slug = icomplyMatrixSlug((string)$slug);
        if ($slug === '') {
            continue;
        }
        $labels[$slug] = trim((string)$label) !== '' ? trim((string)$label) : ucwords(str_replace('-', ' ', $slug));
        if (!isset($serviceKeywords[$slug])) {
            $serviceKeywords[$slug] = [];
        }
        $serviceKeywords[$slug] = array_slice($serviceKeywords[$slug], 0, 4);
        if (!isset($excluded[$slug])) {
            $services[$slug] = $labels[$slug];
        }
    }

    return [
        'keywords' => $keywords,
        'services' => $services,
        'labels' => $labels,
        'service_keywords' => $serviceKeywords,
    ];
}

function icomplyMatrixJson(mixed $value): string
{
    return (string)json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @param list<array{path:string,priority:string}> $hubEntries
 * @return array<string,mixed>
 */
function icomplyPublishTownMatrix(string $dist, array $hubEntries, callable $log): array
{
    $base = 'https://icomplypropertyservices.co.uk';
    $records = icomplyMatrixCatalogueRecords();
    $keywords = $records['keywords'];
    $services = $records['services'];
    $labels = $records['labels'];
    $hubs = count($hubEntries);
    $perPlace = count($keywords) + count($services);
    if ($perPlace < 1) {
        throw new RuntimeException('Town matrix catalogue is empty');
    }
    $placesNeeded = (int)ceil(max(1, ICOMPLY_MATRIX_URL_TARGET - $hubs) / $perPlace);
    $placePick = icomplyMatrixSelectPlaces($placesNeeded);
    $places = $placePick['selected'];
    if (count($places) < 1) {
        throw new RuntimeException('Town matrix has no places');
    }

    $assetDir = $dist . '/assets/matrix';
    if (!is_dir($assetDir) && !mkdir($assetDir, 0755, true) && !is_dir($assetDir)) {
        throw new RuntimeException('Cannot mkdir ' . $assetDir);
    }
    $nationwide = icomplyMatrixNationwidePlaces();
    $manufacturers = icomplyMatrixManufacturerRecords();
    $jobs = icomplyMatrixJobRecords();
    $family = icomplyMatrixFamilyServiceSlugs();
    file_put_contents($assetDir . '/keywords.json', icomplyMatrixJson($keywords));
    file_put_contents($assetDir . '/places.json', icomplyMatrixJson($places));
    file_put_contents($assetDir . '/nationwide.json', icomplyMatrixJson($nationwide));
    file_put_contents($assetDir . '/manufacturers.json', icomplyMatrixJson($manufacturers));
    file_put_contents($assetDir . '/jobs.json', icomplyMatrixJson($jobs));
    file_put_contents($assetDir . '/services.json', icomplyMatrixJson([
        'labels' => $labels,
        'keywords' => $records['service_keywords'],
        'excluded' => icomplyMatrixExcludedServiceSlugs(),
        'family' => $family,
    ]));
    if (!function_exists('icomplyKeywordVariantCatalogue')) {
        require_once __DIR__ . '/keyword-variants.php';
    }
    $variantCatalogue = icomplyKeywordVariantCatalogue();
    $variantMeta = icomplyKeywordVariantCounts($variantCatalogue);
    file_put_contents($assetDir . '/variants.json', icomplyMatrixJson($variantCatalogue));
    file_put_contents($dist . '/variant-stats.json', json_encode($variantMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

    $hubXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $hubXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $urlList = fopen($dist . '/sitemap-urls.txt', 'wb');
    if ($urlList === false) {
        throw new RuntimeException('Cannot write sitemap-urls.txt');
    }
    foreach ($hubEntries as $entry) {
        $path = (string)($entry['path'] ?? '/');
        $loc = $path === '/' ? $base . '/' : $base . $path;
        $priority = (string)($entry['priority'] ?? '0.5');
        $hubXml .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc><priority>' . htmlspecialchars($priority, ENT_XML1) . '</priority></url>' . "\n";
        fwrite($urlList, $loc . "\n");
    }
    $hubXml .= '</urlset>' . "\n";
    file_put_contents($dist . '/sitemap0.xml', $hubXml);

    $chunkPaths = [];
    $chunkIndex = 0;
    $chunkCount = 0;
    $chunkHandle = null;
    $openChunk = static function () use (&$chunkIndex, &$chunkCount, &$chunkHandle, &$chunkPaths, $dist): void {
        if ($chunkHandle !== null) {
            fwrite($chunkHandle, '</urlset>' . "\n");
            fclose($chunkHandle);
        }
        $chunkIndex++;
        $name = 'sitemap' . $chunkIndex . '.xml';
        $chunkPaths[] = $name;
        $chunkHandle = fopen($dist . '/' . $name, 'wb');
        if ($chunkHandle === false) {
            throw new RuntimeException('Cannot write ' . $name);
        }
        fwrite($chunkHandle, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        fwrite($chunkHandle, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");
        $chunkCount = 0;
    };
    $writeLoc = static function (string $path) use (&$chunkCount, &$chunkHandle, $openChunk, $urlList, $base): void {
        if (!function_exists('icomplySitemapOmitsNonGm')) {
            require_once __DIR__ . '/gm-crawl.php';
        }
        if (icomplySitemapOmitsNonGm($path)) {
            return;
        }
        if ($chunkHandle === null || $chunkCount >= ICOMPLY_MATRIX_SITEMAP_CHUNK) {
            $openChunk();
        }
        $loc = $base . $path;
        fwrite($chunkHandle, '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc><priority>0.6</priority></url>' . "\n");
        fwrite($urlList, $loc . "\n");
        $chunkCount++;
    };

    $keywordTown = 0;
    foreach (array_keys($keywords) as $keywordSlug) {
        foreach (array_keys($places) as $placeSlug) {
            $writeLoc('/pages/keywords/' . $keywordSlug . '/' . $placeSlug);
            $keywordTown++;
        }
    }
    $serviceTown = 0;
    foreach (array_keys($services) as $serviceSlug) {
        foreach (array_keys($places) as $placeSlug) {
            $writeLoc('/pages/' . $serviceSlug . '/' . $placeSlug);
            $serviceTown++;
        }
    }

    $coreKeys = array_keys($places);
    $familyKeywordTown = 0;
    if (function_exists('icomplyBuildingDualExtraKeywordTownPaths')) {
        foreach (icomplyBuildingDualExtraKeywordTownPaths($places) as $dualPath) {
            $writeLoc($dualPath);
            $familyKeywordTown++;
        }
    }
    $familyServiceTown = 0;
    $manufacturerTown = 0;
    foreach (array_keys($manufacturers) as $brandSlug) {
        foreach ($coreKeys as $placeSlug) {
            $writeLoc('/pages/manufacturers/' . $brandSlug . '/' . $placeSlug);
            $manufacturerTown++;
        }
    }
    $jobTown = 0;
    foreach (array_keys($jobs) as $jobSlug) {
        foreach ($coreKeys as $placeSlug) {
            $writeLoc('/pages/jobs/' . $jobSlug . '/' . $placeSlug);
            $jobTown++;
        }
    }
    $buildingJobTown = 0;
    if (function_exists('icomplyBuildingDualJobTownPaths')) {
        foreach (icomplyBuildingDualJobTownPaths() as $dualPath) {
            $writeLoc($dualPath);
            $buildingJobTown++;
        }
    }
    if ($chunkHandle !== null) {
        fwrite($chunkHandle, '</urlset>' . "\n");
        fclose($chunkHandle);
    }
    fclose($urlList);

    $index = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $index .= '  <sitemap><loc>' . $base . '/sitemap0.xml</loc></sitemap>' . "\n";
    foreach ($chunkPaths as $name) {
        $index .= '  <sitemap><loc>' . $base . '/' . $name . '</loc></sitemap>' . "\n";
    }
    $variantPartCount = (int)($variantMeta['sitemap_parts'] ?? 0);
    for ($variantPart = 0; $variantPart < $variantPartCount; $variantPart++) {
        $index .= '  <sitemap><loc>' . $base . '/matrix-sitemap/' . $variantPart . '.xml</loc></sitemap>' . "\n";
    }
    $index .= '</sitemapindex>' . "\n";
    file_put_contents($dist . '/sitemap.xml', $index);

    $leaf = static function (string $loc) use ($base): string {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . '  <url><loc>' . $loc . '</loc></url>' . "\n"
            . '</urlset>' . "\n";
    };
    if (!is_dir($dist . '/products')) {
        mkdir($dist . '/products', 0755, true);
    }
    if (!is_dir($dist . '/shop')) {
        mkdir($dist . '/shop', 0755, true);
    }
    file_put_contents($dist . '/products/sitemap.xml', $leaf($base . '/products'));
    file_put_contents($dist . '/shop/sitemap.xml', $leaf($base . '/shop/'));

    $robots = "User-agent: *\nAllow: /\n\nSitemap: {$base}/sitemap.xml\n\n"
        . "Disallow: /admin/\nDisallow: /bin/\nDisallow: /data/\n"
        . "Disallow: /config.php\nDisallow: /config.local.php\n";
    file_put_contents($dist . '/robots.txt', $robots);
    $edgeDir = dirname(SITE_ROOT) . '/netlify/edge-functions';
    if (!is_dir($edgeDir)) {
        mkdir($edgeDir, 0755, true);
    }
    file_put_contents($edgeDir . '/sitemap.js', <<<'JS'
// Static sitemap.xml is the index. No path config, so this cannot shadow it.
export default async () => new Response('', { status: 204 });
JS);
    $robotsJs = str_replace(['\\', '`', '${'], ['\\\\', '\\`', '\\${'], $robots);
    file_put_contents($edgeDir . '/robots.js', <<<JS
export default async () => {
  return new Response(`{$robotsJs}`, {
    headers: {
      "content-type": "text/plain; charset=utf-8",
      "cache-control": "public, max-age=3600",
    },
  });
};

export const config = { path: "/robots.txt" };
JS);

    $sitemapUrls = $hubs + $keywordTown + $serviceTown + $familyKeywordTown + $familyServiceTown + $manufacturerTown + $jobTown + $buildingJobTown;
    $stats = [
        'target' => ICOMPLY_MATRIX_URL_TARGET,
        'hub_urls' => $hubs,
        'keywords' => count($keywords),
        'services' => count($services),
        'places' => count($places),
        'places_available' => $placePick['available'],
        'skipped_duplicate_places' => $placePick['skipped_duplicates'] ?? 0,
        'nationwide_places' => count($nationwide),
        'manufacturers' => count($manufacturers),
        'jobs' => count($jobs),
        'next_chunk_places' => $placePick['remaining'],
        'keyword_town_urls' => $keywordTown,
        'service_town_urls' => $serviceTown,
        'family_keyword_town_urls' => $familyKeywordTown,
        'family_service_town_urls' => $familyServiceTown,
        'manufacturer_town_urls' => $manufacturerTown,
        'job_town_urls' => $jobTown,
        'building_job_town_urls' => $buildingJobTown,
        'sitemap_urls' => $sitemapUrls,
        'variant_services' => $variantMeta['services'] ?? 0,
        'variant_towns' => $variantMeta['towns'] ?? 0,
        'variant_keywords_per_service' => $variantMeta['keywords_per_service'] ?? 0,
        'variant_hubs' => $variantMeta['hubs'] ?? 0,
        'variant_area_pages' => $variantMeta['area_pages'] ?? 0,
        'variant_urls' => $variantMeta['urls'] ?? 0,
        'variant_sitemap_parts' => $variantMeta['sitemap_parts'] ?? 0,
        'variant_per_service' => $variantMeta['per_service'] ?? [],
        'sitemap_parts' => array_merge(['sitemap.xml', 'sitemap0.xml'], $chunkPaths),
        'url_list' => '/sitemap-urls.txt',
        'blocker' => count($places) === 60 && ($placePick['remaining'] ?? 0) === 0
            ? null
            : 'Greater Manchester matrix is short: places=' . count($places) . ' remaining=' . (int)($placePick['remaining'] ?? 0) . '.',
        'next_chunk' => 'Matrix places are the 60 Greater Manchester towns. Non-GM gazetteer rows stay out of the sitemap.',
    ];
    file_put_contents($dist . '/matrix-stats.json', json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    $log('town matrix places=' . count($places)
        . ' skipped_duplicates=' . ($placePick['skipped_duplicates'] ?? 0)
        . ' keyword×town=' . $keywordTown
        . ' service×town=' . $serviceTown
        . ' family_keyword×town=' . $familyKeywordTown
        . ' family_service×town=' . $familyServiceTown
        . ' manufacturer×town=' . $manufacturerTown
        . ' job×town=' . $jobTown
        . ' building_job×town=' . $buildingJobTown
        . ' sitemap_urls=' . $sitemapUrls
        . ' variant_urls=' . ($variantMeta['urls'] ?? 0) . "\n");
    return $stats;
}
