<?php
/**
 * Mainland UK coverage matrix.
 *
 * Every area slug × every coverage service is a cell. Pages render from
 * templates/coverage/ at /pages/coverage/{service}/{area}. Copy is filled
 * per bucket by sibling agents. Guide prices come only from
 * data/coverage/pricing.json.
 *
 * This file does not deploy and does not write the live sitemap.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

function coverageBucketSize(): int
{
    return 40;
}

function coverageAreasFile(): string
{
    return SITE_ROOT . '/data/coverage/areas-mainland.txt';
}

function coveragePricingFile(): string
{
    return SITE_ROOT . '/data/coverage/pricing.json';
}

function coverageBucketsDir(): string
{
    return SITE_ROOT . '/data/coverage/buckets';
}

function coverageContentDir(): string
{
    return SITE_ROOT . '/data/coverage/content';
}

function coverageSitemapsDir(): string
{
    return SITE_ROOT . '/data/coverage/sitemaps';
}

function coverageServicesFile(): string
{
    return SITE_ROOT . '/data/coverage/services.json';
}

function coverageSummaryFile(): string
{
    return SITE_ROOT . '/data/coverage/matrix-summary.json';
}

/**
 * Published guide prices. Single source: data/coverage/pricing.json.
 *
 * @return array<string, array{label:string,amount:int,display:string,service:string}>
 */
function coveragePricing(): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $raw = [];
    $file = coveragePricingFile();
    if (is_file($file)) {
        $decoded = json_decode((string)file_get_contents($file), true);
        if (is_array($decoded)) {
            $raw = $decoded;
        }
    }
    $out = [];
    foreach ($raw as $key => $row) {
        if (!is_string($key) || !is_array($row)) {
            continue;
        }
        $amount = (int)($row['amount'] ?? 0);
        $out[$key] = [
            'label' => (string)($row['label'] ?? $key),
            'amount' => $amount,
            'display' => (string)($row['display'] ?? ('£' . $amount)),
            'service' => (string)($row['service'] ?? ''),
        ];
    }
    $cache = $out;
    return $out;
}

/**
 * @return array<string, array{label:string,hub:string,canonical_service:?string,price_key:?string,group:string}>
 */
function coverageRequiredDefinitions(): array
{
    return [
        'electrical' => [
            'label' => 'Electrical (EICR)',
            'hub' => '/pages/services/electrical',
            'canonical_service' => 'electrical',
            'price_key' => 'eicr',
            'group' => 'core',
        ],
        'gas-safety' => [
            'label' => 'Gas safety',
            'hub' => '/pages/gas-safety-certificate',
            'canonical_service' => 'gas-systems',
            'price_key' => 'gas',
            'group' => 'core',
        ],
        'fire-risk-assessments' => [
            'label' => 'Fire risk assessments (FRA)',
            'hub' => '/pages/services/fire-risk-assessments',
            'canonical_service' => 'fire-risk-assessments',
            'price_key' => 'fra',
            'group' => 'core',
        ],
        'fire-alarms' => [
            'label' => 'Fire alarms',
            'hub' => '/pages/services/fire-alarms',
            'canonical_service' => 'fire-alarms',
            'price_key' => null,
            'group' => 'core',
        ],
        'emergency-lighting' => [
            'label' => 'Emergency lighting',
            'hub' => '/pages/services/emergency-lighting',
            'canonical_service' => 'emergency-lighting',
            'price_key' => null,
            'group' => 'core',
        ],
        'cctv' => [
            'label' => 'CCTV',
            'hub' => '/pages/services/cctv',
            'canonical_service' => 'cctv',
            'price_key' => null,
            'group' => 'core',
        ],
        'access-control' => [
            'label' => 'Access control',
            'hub' => '/pages/services/access-control',
            'canonical_service' => 'access-control',
            'price_key' => null,
            'group' => 'core',
        ],
        'legionella' => [
            'label' => 'Legionella',
            'hub' => '/pages/services/legionella-risk-assessment',
            'canonical_service' => 'legionella-risk-assessment',
            'price_key' => null,
            'group' => 'core',
        ],
        'asbestos' => [
            'label' => 'Asbestos',
            'hub' => '/pages/services/asbestos-survey',
            'canonical_service' => 'asbestos-survey',
            'price_key' => null,
            'group' => 'core',
        ],
        'ev-chargers' => [
            'label' => 'EV chargers',
            'hub' => '/pages/ev-chargers',
            'canonical_service' => 'ev-charging',
            'price_key' => null,
            'group' => 'hub',
        ],
        'plumbing' => [
            'label' => 'Plumbing',
            'hub' => '/pages/services/plumbing',
            'canonical_service' => 'plumbing',
            'price_key' => null,
            'group' => 'hub',
        ],
        'water' => [
            'label' => 'Water hygiene',
            'hub' => '/pages/keywords/water-hygiene-testing',
            'canonical_service' => null,
            'price_key' => null,
            'group' => 'hub',
        ],
        'security' => [
            'label' => 'Security',
            'hub' => '/shop/security',
            'canonical_service' => null,
            'price_key' => null,
            'group' => 'hub',
        ],
        'building' => [
            'label' => 'Building maintenance',
            'hub' => '/pages/services/building-maintenance',
            'canonical_service' => 'building-maintenance',
            'price_key' => null,
            'group' => 'hub',
        ],
        'emergency' => [
            'label' => 'Emergency',
            'hub' => '/pages/emergency',
            'canonical_service' => null,
            'price_key' => null,
            'group' => 'hub',
        ],
        'commercial' => [
            'label' => 'Commercial',
            'hub' => '/pages/commercial',
            'canonical_service' => null,
            'price_key' => null,
            'group' => 'hub',
        ],
        'care-homes' => [
            'label' => 'Care homes',
            'hub' => '/pages/care-homes',
            'canonical_service' => null,
            'price_key' => null,
            'group' => 'hub',
        ],
        'hmo-compliance-pack' => [
            'label' => 'HMO compliance pack',
            'hub' => '/pages/packages',
            'canonical_service' => null,
            'price_key' => 'bundle',
            'group' => 'hub',
        ],
    ];
}

/**
 * Required hubs plus every existing service hub that is not already aliased.
 *
 * @return array<string, array{label:string,hub:string,canonical_service:?string,price_key:?string,group:string}>
 */
function coverageCatalogue(): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    $required = coverageRequiredDefinitions();
    $consumed = [];
    foreach ($required as $slug => $row) {
        $consumed[$slug] = true;
        if (!empty($row['canonical_service'])) {
            $consumed[(string)$row['canonical_service']] = true;
        }
    }
    $extras = [];
    if (function_exists('getServices')) {
        foreach (getServices() as $slug => $label) {
            $slug = (string)$slug;
            if (isset($consumed[$slug]) || isset($extras[$slug])) {
                continue;
            }
            $extras[$slug] = [
                'label' => (string)$label,
                'hub' => '/pages/services/' . $slug,
                'canonical_service' => $slug,
                'price_key' => null,
                'group' => 'catalogue',
            ];
        }
    }
    foreach (glob(SITE_ROOT . '/pages/services/*.php') ?: [] as $file) {
        $slug = basename($file, '.php');
        if ($slug === 'index' || isset($consumed[$slug]) || isset($extras[$slug])) {
            continue;
        }
        $extras[$slug] = [
            'label' => function_exists('keywordDisplayName') ? keywordDisplayName($slug) : $slug,
            'hub' => '/pages/services/' . $slug,
            'canonical_service' => $slug,
            'price_key' => null,
            'group' => 'catalogue',
        ];
    }
    ksort($extras);
    $cache = $required + $extras;
    return $cache;
}

/** @return list<string> */
function coverageLoadAreas(): array
{
    $file = coverageAreasFile();
    if (!is_file($file)) {
        return [];
    }
    $lines = preg_split('/\R/', (string)file_get_contents($file)) ?: [];
    $out = [];
    foreach ($lines as $line) {
        $slug = strtolower(trim($line));
        if ($slug === '' || str_starts_with($slug, '#')) {
            continue;
        }
        $out[] = $slug;
    }
    return $out;
}

function coverageAreaLabel(string $slug): string
{
    if (function_exists('areaFromSlug')) {
        $known = areaFromSlug($slug);
        if (is_string($known) && $known !== '') {
            return $known;
        }
    }
    if (function_exists('keywordDisplayName')) {
        return keywordDisplayName($slug);
    }
    return ucwords(str_replace('-', ' ', $slug));
}

function coverageServiceAreaPath(string $service, string $area): string
{
    return '/pages/coverage/' . $service . '/' . $area;
}

function coverageAreaPath(string $area): string
{
    return '/pages/coverage/areas/' . $area;
}

/**
 * @return list<array{id:string,status:string,agent:?string,claimed_at:?string,published_at:?string,areas:list<string>}>
 */
function coverageLoadBuckets(): array
{
    $dir = coverageBucketsDir();
    if (!is_dir($dir)) {
        return [];
    }
    $files = glob($dir . '/b*.json') ?: [];
    sort($files, SORT_STRING);
    $out = [];
    foreach ($files as $file) {
        $decoded = json_decode((string)file_get_contents($file), true);
        if (!is_array($decoded) || empty($decoded['id'])) {
            continue;
        }
        $areas = [];
        foreach ($decoded['areas'] ?? [] as $area) {
            if (is_string($area) && $area !== '') {
                $areas[] = $area;
            }
        }
        $out[] = [
            'id' => (string)$decoded['id'],
            'status' => (string)($decoded['status'] ?? 'open'),
            'agent' => isset($decoded['agent']) && is_string($decoded['agent']) && $decoded['agent'] !== ''
                ? $decoded['agent'] : null,
            'claimed_at' => isset($decoded['claimed_at']) && is_string($decoded['claimed_at'])
                ? $decoded['claimed_at'] : null,
            'published_at' => isset($decoded['published_at']) && is_string($decoded['published_at'])
                ? $decoded['published_at'] : null,
            'areas' => $areas,
        ];
    }
    return $out;
}

/** @return array<string, array{id:string,status:string,agent:?string,claimed_at:?string,published_at:?string,areas:list<string>}> */
function coverageBucketMap(): array
{
    $map = [];
    foreach (coverageLoadBuckets() as $bucket) {
        $map[$bucket['id']] = $bucket;
    }
    return $map;
}

/** @return array<string, string> area slug => bucket id */
function coverageAreaIndex(): array
{
    $index = [];
    foreach (coverageLoadBuckets() as $bucket) {
        foreach ($bucket['areas'] as $area) {
            $index[$area] = $bucket['id'];
        }
    }
    return $index;
}

function coverageBucketIdForArea(string $area): ?string
{
    return coverageAreaIndex()[$area] ?? null;
}

/**
 * @param list<string> $areas
 * @return list<array{id:string,status:string,agent:?string,claimed_at:?string,published_at:?string,areas:list<string>}>
 */
function coverageFreshBuckets(array $areas): array
{
    $chunks = array_chunk(array_values($areas), coverageBucketSize());
    $buckets = [];
    $i = 1;
    foreach ($chunks as $chunk) {
        $buckets[] = [
            'id' => sprintf('b%03d', $i),
            'status' => 'open',
            'agent' => null,
            'claimed_at' => null,
            'published_at' => null,
            'areas' => array_values($chunk),
        ];
        $i++;
    }
    return $buckets;
}

/**
 * Keep claimed membership stable. New slugs append into new buckets.
 * Removed slugs drop out of open buckets. Claimed buckets keep removed
 * slugs so an agent does not lose in-progress work; verify reports them.
 *
 * @param list<string> $areas
 * @param list<array{id:string,status:string,agent:?string,claimed_at:?string,published_at:?string,areas:list<string>}> $existing
 * @return array{buckets:list<array<string,mixed>>,warnings:list<string>}
 */
function coverageMergeBuckets(array $areas, array $existing): array
{
    if ($existing === []) {
        return ['buckets' => coverageFreshBuckets($areas), 'warnings' => []];
    }
    $warnings = [];
    $source = array_fill_keys($areas, true);
    $assigned = [];
    $buckets = [];
    foreach ($existing as $bucket) {
        $kept = [];
        foreach ($bucket['areas'] as $area) {
            if (isset($assigned[$area])) {
                $warnings[] = "Duplicate area {$area} in {$bucket['id']} and {$assigned[$area]}";
                continue;
            }
            $locked = in_array($bucket['status'], ['claimed', 'published'], true);
            if (!isset($source[$area]) && !$locked) {
                $warnings[] = "Dropped {$area} from open bucket {$bucket['id']} (not in areas file)";
                continue;
            }
            if (!isset($source[$area]) && $locked) {
                $warnings[] = "Kept {$area} in {$bucket['status']} bucket {$bucket['id']} even though it left the areas file";
            }
            $assigned[$area] = $bucket['id'];
            $kept[] = $area;
        }
        $bucket['areas'] = $kept;
        $buckets[] = $bucket;
    }
    $fresh = [];
    foreach ($areas as $area) {
        if (!isset($assigned[$area])) {
            $fresh[] = $area;
        }
    }
    if ($fresh !== []) {
        $appended = coverageFreshBuckets($fresh);
        $max = 0;
        foreach ($buckets as $bucket) {
            if (preg_match('/^b(\d+)$/', $bucket['id'], $m)) {
                $max = max($max, (int)$m[1]);
            }
        }
        foreach ($appended as $bucket) {
            $max++;
            $bucket['id'] = sprintf('b%03d', $max);
            $buckets[] = $bucket;
        }
        $warnings[] = 'Appended ' . count($fresh) . ' new area slug(s) into new buckets';
    }
    return ['buckets' => $buckets, 'warnings' => $warnings];
}

/** @param list<array<string,mixed>> $buckets */
function coverageWriteBuckets(array $buckets): void
{
    $dir = coverageBucketsDir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create ' . $dir);
    }
    $keep = [];
    foreach ($buckets as $bucket) {
        $id = (string)$bucket['id'];
        $keep[$id] = true;
        $payload = [
            'id' => $id,
            'status' => (string)($bucket['status'] ?? 'open'),
            'agent' => $bucket['agent'] ?? null,
            'claimed_at' => $bucket['claimed_at'] ?? null,
            'published_at' => $bucket['published_at'] ?? null,
            'area_count' => count($bucket['areas'] ?? []),
            'areas' => array_values($bucket['areas'] ?? []),
        ];
        $path = $dir . '/' . $id . '.json';
        coverageWriteJson($path, $payload);
    }
    foreach (glob($dir . '/b*.json') ?: [] as $file) {
        $id = basename($file, '.json');
        if (!isset($keep[$id])) {
            unlink($file);
        }
    }
}

function coverageWriteJson(string $path, array $payload): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create ' . $dir);
    }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('JSON encode failed for ' . $path);
    }
    file_put_contents($path, $json . "\n");
}

function coverageWriteCatalogueSnapshot(): void
{
    $rows = [];
    foreach (coverageCatalogue() as $slug => $row) {
        $rows[] = [
            'slug' => $slug,
            'label' => $row['label'],
            'hub' => $row['hub'],
            'canonical_service' => $row['canonical_service'],
            'price_key' => $row['price_key'],
            'group' => $row['group'],
        ];
    }
    coverageWriteJson(coverageServicesFile(), [
        'generated_by' => 'website/bin/generate-coverage.php',
        'note' => 'Snapshot of coverageCatalogue(). Edit coverageRequiredDefinitions() and re-run the generator. Do not hand-edit prices here — prices live in pricing.json.',
        'count' => count($rows),
        'services' => $rows,
    ]);
}

/**
 * @param list<array<string,mixed>> $buckets
 * @return array<string,mixed>
 */
function coverageBuildSummary(array $buckets): array
{
    $areas = coverageLoadAreas();
    $services = coverageCatalogue();
    $pricing = coveragePricing();
    $ready = coverageReadyCells();
    $bucketRows = [];
    foreach ($buckets as $bucket) {
        $bucketRows[] = [
            'id' => $bucket['id'],
            'status' => $bucket['status'],
            'agent' => $bucket['agent'],
            'area_count' => count($bucket['areas']),
            'first_area' => $bucket['areas'][0] ?? null,
            'last_area' => $bucket['areas'] ? $bucket['areas'][count($bucket['areas']) - 1] : null,
        ];
    }
    $priceOut = [];
    foreach ($pricing as $key => $row) {
        $priceOut[$key] = $row['display'];
    }
    return [
        'generated_by' => 'website/bin/generate-coverage.php',
        'non_prod' => true,
        'live_sitemap' => 'website/sitemap.xml',
        'live_sitemap_includes_coverage' => false,
        'areas' => count($areas),
        'services' => count($services),
        'cells' => count($areas) * count($services),
        'buckets' => count($buckets),
        'bucket_size' => coverageBucketSize(),
        'content_ready_cells' => count($ready),
        'pricing' => $priceOut,
        'url_patterns' => [
            'area_hub' => '/pages/coverage/areas/{area}',
            'service_area' => '/pages/coverage/{service}/{area}',
            'index' => '/pages/coverage',
        ],
        'buckets_index' => $bucketRows,
    ];
}

/**
 * @return list<array{service:string,area:string,path:string,bucket:string}>
 */
function coverageReadyCells(): array
{
    $catalogue = coverageCatalogue();
    $index = coverageAreaIndex();
    $dir = coverageContentDir();
    $files = glob($dir . '/b*/*.json') ?: [];
    sort($files, SORT_STRING);
    $out = [];
    foreach ($files as $file) {
        $base = basename($file, '.json');
        if ($base === 'README') {
            continue;
        }
        $decoded = json_decode((string)file_get_contents($file), true);
        if (!is_array($decoded)) {
            continue;
        }
        $area = (string)($decoded['area'] ?? $base);
        $bucket = (string)($index[$area] ?? basename(dirname($file)));
        $services = $decoded['services'] ?? [];
        if (!is_array($services)) {
            continue;
        }
        foreach ($services as $service => $cell) {
            if (!is_string($service) || !isset($catalogue[$service]) || !is_array($cell)) {
                continue;
            }
            if (($cell['status'] ?? '') !== 'ready') {
                continue;
            }
            if (!coverageIntroAcceptable((string)($cell['intro'] ?? ''), coverageAreaLabel($area))) {
                continue;
            }
            $out[] = [
                'service' => $service,
                'area' => $area,
                'path' => coverageServiceAreaPath($service, $area),
                'bucket' => $bucket,
            ];
        }
    }
    return $out;
}

function coverageIntroAcceptable(string $intro, string $areaLabel): bool
{
    $intro = trim($intro);
    if (strlen($intro) < 160) {
        return false;
    }
    if (stripos($intro, 'waiting for a local write-up') !== false) {
        return false;
    }
    if ($areaLabel === '') {
        return true;
    }
    if (stripos($intro, $areaLabel) !== false) {
        return true;
    }
    $slugWords = strtolower(str_replace('-', ' ', coverageSlugFromLabel($areaLabel)));
    return $slugWords !== '' && stripos(strtolower($intro), $slugWords) !== false;
}

function coverageSlugFromLabel(string $label): string
{
    $s = strtolower(trim($label));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? $s;
    return trim($s, '-');
}

function coverageScaffoldIntro(string $serviceLabel, string $areaLabel): string
{
    return $serviceLabel . ' in ' . $areaLabel
        . ' is a mainland UK coverage slot. This page is waiting for a local write-up from the area bucket. '
        . 'Book a survey and Icomply will confirm scope before any work.';
}

/**
 * @return array{status:string,intro:string,meta_desc:string,ready:bool}
 */
function coverageCell(string $service, string $area): array
{
    $catalogue = coverageCatalogue();
    $label = $catalogue[$service]['label'] ?? $service;
    $areaLabel = coverageAreaLabel($area);
    $scaffold = coverageScaffoldIntro($label, $areaLabel);
    $status = 'pending';
    $intro = $scaffold;
    $meta = $label . ' in ' . $areaLabel . '. Mainland UK coverage by Icomply Property Services.';
    $bucketId = coverageBucketIdForArea($area);
    if ($bucketId !== null) {
        $path = coverageContentDir() . '/' . $bucketId . '/' . $area . '.json';
        if (is_file($path)) {
            $decoded = json_decode((string)file_get_contents($path), true);
            $cell = is_array($decoded) ? ($decoded['services'][$service] ?? null) : null;
            if (is_array($cell)) {
                $status = (string)($cell['status'] ?? 'pending');
                if (!empty($cell['intro']) && is_string($cell['intro'])) {
                    $intro = trim($cell['intro']);
                }
                if (!empty($cell['meta_desc']) && is_string($cell['meta_desc'])) {
                    $meta = trim($cell['meta_desc']);
                }
            }
        }
    }
    $ready = $status === 'ready' && coverageIntroAcceptable($intro, $areaLabel);
    return [
        'status' => $ready ? 'ready' : ($status === 'ready' ? 'pending' : $status),
        'intro' => $intro,
        'meta_desc' => $meta,
        'ready' => $ready,
    ];
}

/** @return array{label:string,amount:int,display:string,service:string}|null */
function coveragePriceForService(string $service): ?array
{
    $catalogue = coverageCatalogue();
    $key = $catalogue[$service]['price_key'] ?? null;
    if (!is_string($key) || $key === '') {
        return null;
    }
    return coveragePricing()[$key] ?? null;
}

/**
 * @param list<array<string,mixed>> $buckets
 * @return array{tier1:int,tier2:int,tier3:int}
 */
function coverageWriteSitemapTiers(array $buckets): array
{
    $dir = coverageSitemapsDir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create ' . $dir);
    }
    $catalogue = coverageCatalogue();
    $hubs = [];
    foreach ($catalogue as $row) {
        $hubs[$row['hub']] = true;
    }
    $tier1 = array_keys($hubs);
    sort($tier1, SORT_STRING);

    $tier2 = [];
    foreach ($buckets as $bucket) {
        foreach ($bucket['areas'] as $area) {
            $tier2[] = coverageAreaPath((string)$area);
        }
    }

    $tier3 = [];
    foreach (coverageReadyCells() as $cell) {
        $tier3[] = $cell['path'];
    }
    sort($tier3, SORT_STRING);

    file_put_contents($dir . '/tier-1-hubs.xml', coverageUrlsetXml($tier1, '0.7'));
    file_put_contents($dir . '/tier-2-areas.xml', coverageUrlsetXml($tier2, '0.5'));
    file_put_contents($dir . '/tier-3-service-area.xml', coverageUrlsetXml($tier3, '0.4'));

    $index = [
        'live_sitemap' => 'website/sitemap.xml',
        'live_tier' => '0',
        'robots' => 'Tier fragments are not linked from robots.txt. website/data/ is disallowed.',
        'deploy' => 'Do not copy these files over sitemap.xml. Do not run netlify deploy --prod.',
        'tiers' => [
            [
                'id' => '0',
                'name' => 'live-compact',
                'file' => 'website/sitemap.xml',
                'includes_coverage_matrix' => false,
                'written_by' => 'website/bin/generate-sitemap.php',
            ],
            [
                'id' => '1',
                'name' => 'service-hubs',
                'file' => 'website/data/coverage/sitemaps/tier-1-hubs.xml',
                'urls' => count($tier1),
                'rule' => 'Canonical hub URL for every coverage service. Planning fragment only.',
            ],
            [
                'id' => '2',
                'name' => 'area-hubs',
                'file' => 'website/data/coverage/sitemaps/tier-2-areas.xml',
                'urls' => count($tier2),
                'rule' => 'One /pages/coverage/areas/{slug} URL per mainland area. Not in the live sitemap.',
            ],
            [
                'id' => '3',
                'name' => 'service-area',
                'file' => 'website/data/coverage/sitemaps/tier-3-service-area.xml',
                'urls' => count($tier3),
                'rule' => 'One /pages/coverage/{service}/{area} URL per cell whose content status is ready. Empty until sibling agents fill a bucket.',
            ],
        ],
    ];
    coverageWriteJson($dir . '/tier-index.json', $index);
    return [
        'tier1' => count($tier1),
        'tier2' => count($tier2),
        'tier3' => count($tier3),
    ];
}

/** @param list<string> $paths */
function coverageUrlsetXml(array $paths, string $priority): string
{
    $base = 'https://icomplypropertyservices.co.uk';
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= "<!-- NON-PROD coverage tier fragment. Not linked from robots.txt. Do not deploy as the live sitemap. -->\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($paths as $path) {
        $loc = htmlspecialchars($base . $path, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml .= '  <url><loc>' . $loc . '</loc><priority>' . $priority . '</priority></url>' . "\n";
    }
    $xml .= "</urlset>\n";
    return $xml;
}

/**
 * Structural check. With $strict, every cell must have acceptable ready copy.
 *
 * @return list<string>
 */
function coverageVerify(bool $strict = false): array
{
    $errors = [];
    $areas = coverageLoadAreas();
    if (count($areas) !== 1131) {
        $errors[] = 'Expected 1131 mainland area slugs, found ' . count($areas);
    }
    $seen = [];
    foreach ($areas as $area) {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $area)) {
            $errors[] = 'Invalid area slug: ' . $area;
        }
        if (isset($seen[$area])) {
            $errors[] = 'Duplicate area slug: ' . $area;
        }
        $seen[$area] = true;
    }
    $catalogue = coverageCatalogue();
    foreach (array_keys(coverageRequiredDefinitions()) as $slug) {
        if (!isset($catalogue[$slug])) {
            $errors[] = 'Missing required coverage service: ' . $slug;
        }
    }
    $represented = [];
    foreach ($catalogue as $slug => $row) {
        $represented[$slug] = true;
        if (!empty($row['canonical_service'])) {
            $represented[(string)$row['canonical_service']] = true;
        }
    }
    if (function_exists('getServices')) {
        foreach (array_keys(getServices()) as $slug) {
            if (!isset($represented[(string)$slug])) {
                $errors[] = 'Service missing from coverage catalogue: ' . $slug;
            }
        }
    }
    foreach (glob(SITE_ROOT . '/pages/services/*.php') ?: [] as $file) {
        $slug = basename($file, '.php');
        if ($slug === 'index') {
            continue;
        }
        if (!isset($represented[$slug])) {
            $errors[] = 'Service hub page missing from coverage catalogue: ' . $slug;
        }
    }
    $pricing = coveragePricing();
    $expect = ['eicr' => 249, 'gas' => 85, 'fra' => 350, 'bundle' => 650];
    foreach ($expect as $key => $amount) {
        if (!isset($pricing[$key])) {
            $errors[] = 'Pricing SSOT missing key ' . $key;
            continue;
        }
        if ((int)$pricing[$key]['amount'] !== $amount) {
            $errors[] = 'Pricing SSOT ' . $key . ' is ' . $pricing[$key]['amount'] . ', expected ' . $amount;
        }
        $display = '£' . $amount;
        if ($pricing[$key]['display'] !== $display) {
            $errors[] = 'Pricing display for ' . $key . ' is ' . $pricing[$key]['display'] . ', expected ' . $display;
        }
    }
    foreach (['eicr' => 'electrical', 'gas' => 'gas-safety', 'fra' => 'fire-risk-assessments', 'bundle' => 'hmo-compliance-pack'] as $key => $service) {
        if (($pricing[$key]['service'] ?? '') !== $service) {
            $errors[] = 'Pricing key ' . $key . ' is not tied to ' . $service;
        }
        if (($catalogue[$service]['price_key'] ?? null) !== $key) {
            $errors[] = 'Service ' . $service . ' price_key is not ' . $key;
        }
    }

    $buckets = coverageLoadBuckets();
    if ($buckets === []) {
        $errors[] = 'No coverage buckets. Run php website/bin/generate-coverage.php';
        return $errors;
    }
    $union = [];
    foreach ($buckets as $bucket) {
        if (count($bucket['areas']) > coverageBucketSize()) {
            $errors[] = $bucket['id'] . ' has ' . count($bucket['areas']) . ' areas (max ' . coverageBucketSize() . ')';
        }
        if (!in_array($bucket['status'], ['open', 'claimed', 'published'], true)) {
            $errors[] = $bucket['id'] . ' has unknown status ' . $bucket['status'];
        }
        foreach ($bucket['areas'] as $area) {
            if (isset($union[$area])) {
                $errors[] = 'Area ' . $area . ' is in ' . $union[$area] . ' and ' . $bucket['id'];
            }
            $union[$area] = $bucket['id'];
        }
    }
    foreach ($areas as $area) {
        if (!isset($union[$area])) {
            $errors[] = 'Area not assigned to a bucket: ' . $area;
        }
    }
    foreach ($union as $area => $bucketId) {
        if (!isset($seen[$area])) {
            $errors[] = 'Bucket ' . $bucketId . ' contains slug missing from areas file: ' . $area;
        }
    }

    $summaryPath = coverageSummaryFile();
    if (!is_file($summaryPath)) {
        $errors[] = 'Missing matrix-summary.json';
    }
    $tierIndex = coverageSitemapsDir() . '/tier-index.json';
    if (!is_file($tierIndex)) {
        $errors[] = 'Missing sitemap tier index';
    } else {
        $idx = json_decode((string)file_get_contents($tierIndex), true);
        $byId = [];
        foreach ($idx['tiers'] ?? [] as $tier) {
            if (is_array($tier) && isset($tier['id'])) {
                $byId[(string)$tier['id']] = $tier;
            }
        }
        if (($byId['0']['includes_coverage_matrix'] ?? true) !== false) {
            $errors[] = 'Tier 0 must keep the live sitemap free of the coverage matrix';
        }
        $tier2file = coverageSitemapsDir() . '/tier-2-areas.xml';
        if (!is_file($tier2file)) {
            $errors[] = 'Missing tier-2-areas.xml';
        } else {
            $xml = (string)file_get_contents($tier2file);
            $count = substr_count($xml, '<url>');
            if ($count !== count($areas)) {
                $errors[] = 'Tier 2 URL count ' . $count . ' != areas ' . count($areas);
            }
            if (!str_contains($xml, 'NON-PROD')) {
                $errors[] = 'Tier 2 fragment is missing the NON-PROD marker';
            }
        }
        $tier3file = coverageSitemapsDir() . '/tier-3-service-area.xml';
        if (!is_file($tier3file)) {
            $errors[] = 'Missing tier-3-service-area.xml';
        } else {
            $count = substr_count((string)file_get_contents($tier3file), '<url>');
            $ready = count(coverageReadyCells());
            if ($count !== $ready) {
                $errors[] = 'Tier 3 URL count ' . $count . ' != ready cells ' . $ready;
            }
        }
    }

    if (is_file(SITE_ROOT . '/sitemap.xml') && str_contains((string)file_get_contents(SITE_ROOT . '/sitemap.xml'), '/pages/coverage/')) {
        $errors[] = 'Live sitemap.xml contains /pages/coverage/ URLs';
    }

    if ($strict) {
        foreach ($areas as $area) {
            foreach (array_keys($catalogue) as $service) {
                $cell = coverageCell($service, $area);
                if (!$cell['ready']) {
                    $errors[] = 'Cell not ready: ' . $service . ' / ' . $area;
                    if (count($errors) > 40) {
                        $errors[] = 'Strict verify stopped after 40 missing cells';
                        return $errors;
                    }
                }
            }
        }
    }
    return $errors;
}

/**
 * @return list<string> errors for one bucket. Empty means publishable.
 */
function coveragePublishErrors(string $bucketId): array
{
    $map = coverageBucketMap();
    if (!isset($map[$bucketId])) {
        return ['Unknown bucket ' . $bucketId];
    }
    $errors = [];
    $intros = [];
    $catalogue = coverageCatalogue();
    foreach ($map[$bucketId]['areas'] as $area) {
        $label = coverageAreaLabel($area);
        foreach (array_keys($catalogue) as $service) {
            $cell = coverageCell($service, $area);
            if (!$cell['ready']) {
                $errors[] = $bucketId . ' ' . $area . ' ' . $service . ' is not ready';
                if (count($errors) > 30) {
                    $errors[] = 'Stopped after 30 publish errors';
                    return $errors;
                }
                continue;
            }
            $hash = md5(strtolower(trim($cell['intro'])));
            if (isset($intros[$hash])) {
                $errors[] = 'Duplicate intro in ' . $bucketId . ': ' . $intros[$hash] . ' and ' . $service . '/' . $area;
            }
            $intros[$hash] = $service . '/' . $area;
        }
    }
    return $errors;
}

function renderCoverageIndex(): void
{
    $catalogue = coverageCatalogue();
    $buckets = coverageLoadBuckets();
    $areas = coverageLoadAreas();
    $pricing = coveragePricing();
    $pageTitle = 'Mainland UK coverage matrix';
    $metaDesc = 'Coverage index for Icomply services across mainland UK areas. Area pages are filled per bucket and stay out of the live sitemap until copy is ready.';
    $metaKeywords = 'property compliance coverage, EICR, gas safety, fire risk assessment';
    $canonicalUrl = url('/pages/coverage');
    $metaRobots = 'noindex, follow';
    require SITE_ROOT . '/templates/coverage/index.php';
}

function renderCoverageAreaHub(string $areaSlug): void
{
    $areas = array_fill_keys(coverageLoadAreas(), true);
    $index = coverageAreaIndex();
    if (!isset($areas[$areaSlug]) && !isset($index[$areaSlug])) {
        http_response_code(404);
        echo 'Area not in the mainland coverage list';
        icomplyRequestExit();
        return;
    }
    $catalogue = coverageCatalogue();
    $areaLabel = coverageAreaLabel($areaSlug);
    $bucketId = coverageBucketIdForArea($areaSlug) ?? '';
    $pageTitle = 'Property compliance in ' . $areaLabel;
    $metaDesc = 'Electrical, gas safety, fire risk assessment and compliance services in ' . $areaLabel . '. Mainland UK coverage from Icomply.';
    $metaKeywords = 'property compliance ' . $areaLabel . ', EICR, gas safety, FRA';
    $canonicalUrl = url(coverageAreaPath($areaSlug));
    $anyReady = false;
    foreach (array_keys($catalogue) as $service) {
        if (coverageCell($service, $areaSlug)['ready']) {
            $anyReady = true;
            break;
        }
    }
    $metaRobots = $anyReady ? 'index, follow' : 'noindex, follow';
    $pricing = coveragePricing();
    require SITE_ROOT . '/templates/coverage/area-hub.php';
}

function renderCoverageServiceArea(string $service, string $areaSlug): void
{
    $catalogue = coverageCatalogue();
    if (!isset($catalogue[$service])) {
        http_response_code(404);
        echo 'Coverage service not found';
        icomplyRequestExit();
        return;
    }
    $knownAreas = array_fill_keys(coverageLoadAreas(), true);
    $index = coverageAreaIndex();
    if (!isset($knownAreas[$areaSlug]) && !isset($index[$areaSlug])) {
        http_response_code(404);
        echo 'Area not in the mainland coverage list';
        icomplyRequestExit();
        return;
    }
    $row = $catalogue[$service];
    $areaLabel = coverageAreaLabel($areaSlug);
    $cell = coverageCell($service, $areaSlug);
    $price = coveragePriceForService($service);
    $bucketId = coverageBucketIdForArea($areaSlug) ?? '';
    $pageTitle = $row['label'] . ' in ' . $areaLabel;
    $metaDesc = $cell['meta_desc'];
    $metaKeywords = $row['label'] . ' ' . $areaLabel . ', property compliance';
    $canonicalUrl = url(coverageServiceAreaPath($service, $areaSlug));
    $metaRobots = $cell['ready'] ? 'index, follow' : 'noindex, follow';
    $pricing = coveragePricing();
    require SITE_ROOT . '/templates/coverage/service-area.php';
}
