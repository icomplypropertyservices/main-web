<?php
/**
 * Build inventory + regen indicators for admin and CLI checks.
 */
require_once __DIR__ . '/../config.php';

function buildManifestPath(): string {
    return SITE_ROOT . '/data/build-manifest.json';
}

function loadBuildManifest(): array {
    $path = buildManifestPath();
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string)file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveBuildManifest(array $manifest): void {
    $dir = SITE_ROOT . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $manifest['updated_at'] = date('c');
    file_put_contents(
        buildManifestPath(),
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

/** Mark site as needing regeneration (e.g. after data edit). */
function markBuildDirty(string $reason = 'data changed'): void {
    $m = loadBuildManifest();
    $m['dirty'] = true;
    $m['dirty_reason'] = $reason;
    $m['dirty_at'] = date('c');
    saveBuildManifest($m);
}

function clearBuildDirty(): void {
    $m = loadBuildManifest();
    $m['dirty'] = false;
    $m['dirty_reason'] = null;
    $m['built_at'] = date('c');
    saveBuildManifest($m);
}

/** Count PHP files in a directory (non-recursive or recursive). */
function countPhpFiles(string $dir, bool $recursive = false): int {
    if (!is_dir($dir)) {
        return 0;
    }
    if (!$recursive) {
        $n = 0;
        foreach (glob(rtrim($dir, '/\\') . '/*.php') ?: [] as $f) {
            $n++;
        }
        return $n;
    }
    $n = 0;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $n++;
        }
    }
    return $n;
}

/** Latest mtime among data source files that affect stubs. */
function dataSourcesMtime(): int {
    $files = [
        SITE_ROOT . '/data/services.json',
        SITE_ROOT . '/data/services-custom.json',
        SITE_ROOT . '/data/areas.json',
        SITE_ROOT . '/data/keywords.json',
        SITE_ROOT . '/data/manufacturers.json',
    ];
    $max = 0;
    foreach ($files as $f) {
        if (is_file($f)) {
            $max = max($max, (int)filemtime($f));
        }
    }
    return $max;
}

/** Latest mtime of a sample of generated stubs / sitemap. */
function generatedArtifactsMtime(): int {
    $candidates = [
        SITE_ROOT . '/sitemap.xml',
        SITE_ROOT . '/pages/services/index.php',
        SITE_ROOT . '/pages/keywords/index.php',
    ];
    // Sample one combo stub if present
    $services = getServices();
    $areas = getAreas();
    if ($services && $areas) {
        $sSlug = array_key_first($services);
        $aSlug = areaSlug($areas[0]);
        $candidates[] = SITE_ROOT . "/pages/{$sSlug}/{$aSlug}.php";
    }
    $max = 0;
    foreach ($candidates as $f) {
        if (is_file($f)) {
            $max = max($max, (int)filemtime($f));
        }
    }
    return $max;
}

/**
 * Full build status snapshot for admin / debug.
 *
 * @return array{
 *   expected: array, actual: array, missing: array, extra: array,
 *   needs_regen: bool, reasons: string[], dirty: bool, dirty_reason: ?string,
 *   built_at: ?string, data_mtime: int, artifacts_mtime: int,
 *   samples: array, jobs: array
 * }
 */
function getBuildStatus(): array {
    $services = getServices();
    $areas = getAreas();
    $keywords = getMajorKeywords();

    $serviceCount = count($services);
    $areaCount = count($areas);
    $keywordCount = count($keywords);

    $expectedCombo = $serviceCount * $areaCount;
    $expectedKeywords = $keywordCount; // + index separate
    $expectedAreaHubs = $areaCount;
    $expectedServiceHubs = $serviceCount; // + index separate

    $actualCombo = 0;
    $comboByService = [];
    foreach ($services as $slug => $name) {
        $dir = SITE_ROOT . '/pages/' . $slug;
        $n = countPhpFiles($dir, false);
        $comboByService[$slug] = $n;
        $actualCombo += $n;
    }

    $actualKeywords = 0;
    $kwDir = SITE_ROOT . '/pages/keywords';
    if (is_dir($kwDir)) {
        foreach (glob($kwDir . '/*.php') ?: [] as $f) {
            if (basename($f) === 'index.php') {
                continue;
            }
            $actualKeywords++;
        }
    }
    $hasKeywordIndex = is_file($kwDir . '/index.php');

    $actualAreaHubs = countPhpFiles(SITE_ROOT . '/pages/areas', false);
    $actualServiceHubs = 0;
    $svcDir = SITE_ROOT . '/pages/services';
    if (is_dir($svcDir)) {
        foreach (glob($svcDir . '/*.php') ?: [] as $f) {
            if (basename($f) === 'index.php') {
                continue;
            }
            $actualServiceHubs++;
        }
    }
    $hasServiceIndex = is_file($svcDir . '/index.php');
    $hasSitemap = is_file(SITE_ROOT . '/sitemap.xml');
    $sitemapUrls = 0;
    if ($hasSitemap) {
        $xml = (string)file_get_contents(SITE_ROOT . '/sitemap.xml');
        $sitemapUrls = substr_count($xml, '<url>');
    }

    $expected = [
        'combo' => $expectedCombo,
        'keywords' => $expectedKeywords,
        'keyword_index' => 1,
        'area_hubs' => $expectedAreaHubs,
        'service_hubs' => $expectedServiceHubs,
        'service_index' => 1,
        'sitemap' => 1,
        'sitemap_urls_min' => 1 + 1 + 1 + $expectedServiceHubs + $expectedCombo + $expectedAreaHubs + 1 + $expectedKeywords,
    ];
    $actual = [
        'combo' => $actualCombo,
        'keywords' => $actualKeywords,
        'keyword_index' => $hasKeywordIndex ? 1 : 0,
        'area_hubs' => $actualAreaHubs,
        'service_hubs' => $actualServiceHubs,
        'service_index' => $hasServiceIndex ? 1 : 0,
        'sitemap' => $hasSitemap ? 1 : 0,
        'sitemap_urls' => $sitemapUrls,
        'combo_by_service' => $comboByService,
        'total_php_pages' => countPhpFiles(SITE_ROOT . '/pages', true),
    ];

    $missing = [];
    $reasons = [];
    if ($actual['combo'] < $expected['combo']) {
        $missing['combo'] = $expected['combo'] - $actual['combo'];
        $reasons[] = "Missing {$missing['combo']} service×area pages ({$actual['combo']}/{$expected['combo']})";
    }
    if ($actual['keywords'] < $expected['keywords']) {
        $missing['keywords'] = $expected['keywords'] - $actual['keywords'];
        $reasons[] = "Missing {$missing['keywords']} keyword pages ({$actual['keywords']}/{$expected['keywords']})";
    }
    if (!$hasKeywordIndex) {
        $missing['keyword_index'] = 1;
        $reasons[] = 'Keyword index missing';
    }
    if ($actual['area_hubs'] < $expected['area_hubs']) {
        $missing['area_hubs'] = $expected['area_hubs'] - $actual['area_hubs'];
        $reasons[] = "Missing {$missing['area_hubs']} area hubs";
    }
    if ($actual['service_hubs'] < $expected['service_hubs']) {
        $missing['service_hubs'] = $expected['service_hubs'] - $actual['service_hubs'];
        $reasons[] = "Missing {$missing['service_hubs']} service hubs";
    }
    if (!$hasServiceIndex) {
        $missing['service_index'] = 1;
        $reasons[] = 'Service index missing';
    }
    if (!$hasSitemap) {
        $missing['sitemap'] = 1;
        $reasons[] = 'sitemap.xml missing';
    } elseif ($sitemapUrls < $expected['sitemap_urls_min'] * 0.9) {
        $reasons[] = "Sitemap looks incomplete ({$sitemapUrls} URLs, expect ~{$expected['sitemap_urls_min']})";
    }

    foreach ($comboByService as $slug => $n) {
        if ($n < $areaCount) {
            $reasons[] = "Service {$slug}: {$n}/{$areaCount} area pages";
        }
    }

    $manifest = loadBuildManifest();
    $dirty = !empty($manifest['dirty']);
    $dirtyReason = $manifest['dirty_reason'] ?? null;
    if ($dirty) {
        $reasons[] = 'Build marked dirty: ' . ($dirtyReason ?: 'unknown');
    }

    $dataMtime = dataSourcesMtime();
    $artMtime = generatedArtifactsMtime();
    $builtAt = $manifest['built_at'] ?? null;
    if ($dataMtime > 0 && $artMtime > 0 && $dataMtime > $artMtime + 2) {
        $reasons[] = 'Data files newer than generated pages (regen needed)';
    }
    if ($builtAt) {
        $builtTs = strtotime($builtAt) ?: 0;
        if ($builtTs && $dataMtime > $builtTs + 2) {
            $reasons[] = 'Data changed since last recorded build';
        }
    } elseif ($actual['combo'] === 0) {
        $reasons[] = 'No build recorded yet';
    }

    // Dedupe reasons
    $reasons = array_values(array_unique($reasons));
    $needsRegen = count($reasons) > 0;

    // Sample page links for admin
    $samples = [];
    if ($services && $areas) {
        $sSlug = array_key_first($services);
        $sName = $services[$sSlug];
        $area = $areas[0];
        $samples[] = [
            'label' => "{$sName} in {$area}",
            'path' => '/pages/' . $sSlug . '/' . areaSlug($area) . '.php',
            'exists' => is_file(SITE_ROOT . '/pages/' . $sSlug . '/' . areaSlug($area) . '.php'),
        ];
        // Prefer fire-alarms/stockport if present
        if (isset($services['fire-alarms'])) {
            $samples[] = [
                'label' => 'Fire Alarms in Stockport',
                'path' => '/pages/fire-alarms/stockport.php',
                'exists' => is_file(SITE_ROOT . '/pages/fire-alarms/stockport.php'),
            ];
        }
    }
    if (isset($keywords['kentec-fire-alarm-panel'])) {
        $samples[] = [
            'label' => 'Keyword: Kentec Fire Alarm Panel',
            'path' => '/pages/keywords/kentec-fire-alarm-panel.php',
            'exists' => is_file(SITE_ROOT . '/pages/keywords/kentec-fire-alarm-panel.php'),
        ];
    }
    if (isset($keywords['eicr'])) {
        $samples[] = [
            'label' => 'Keyword: EICR',
            'path' => '/pages/keywords/eicr.php',
            'exists' => is_file(SITE_ROOT . '/pages/keywords/eicr.php'),
        ];
    }
    $samples[] = [
        'label' => 'All Services',
        'path' => '/pages/services/index.php',
        'exists' => $hasServiceIndex,
    ];
    if ($areas) {
        $samples[] = [
            'label' => 'Area hub: ' . $areas[0],
            'path' => '/pages/areas/' . areaSlug($areas[0]) . '.php',
            'exists' => is_file(SITE_ROOT . '/pages/areas/' . areaSlug($areas[0]) . '.php'),
        ];
    }
    $samples[] = [
        'label' => 'Home',
        'path' => '/',
        'exists' => is_file(SITE_ROOT . '/index.php'),
    ];
    $samples[] = [
        'label' => 'Contact',
        'path' => '/contact.php',
        'exists' => is_file(SITE_ROOT . '/contact.php'),
    ];
    $samples[] = [
        'label' => 'Sitemap',
        'path' => '/sitemap.xml',
        'exists' => $hasSitemap,
    ];

    $jobs = [
        'combo' => [
            'name' => 'Service × area pages',
            'script' => 'generate-site.php',
            'expected' => $expectedCombo,
            'actual' => $actualCombo,
            'ok' => $actualCombo >= $expectedCombo,
        ],
        'keywords' => [
            'name' => 'Keyword pages',
            'script' => 'generate-keyword-pages.php',
            'expected' => $expectedKeywords,
            'actual' => $actualKeywords,
            'ok' => $actualKeywords >= $expectedKeywords && $hasKeywordIndex,
        ],
        'areas' => [
            'name' => 'Area hubs',
            'script' => 'generate-area-hubs.php',
            'expected' => $expectedAreaHubs,
            'actual' => $actualAreaHubs,
            'ok' => $actualAreaHubs >= $expectedAreaHubs,
        ],
        'services' => [
            'name' => 'Service hubs',
            'script' => 'generate-service-hubs.php',
            'expected' => $expectedServiceHubs,
            'actual' => $actualServiceHubs,
            'ok' => $actualServiceHubs >= $expectedServiceHubs && $hasServiceIndex,
        ],
        'sitemap' => [
            'name' => 'Sitemap',
            'script' => 'generate-sitemap.php',
            'expected' => $expected['sitemap_urls_min'],
            'actual' => $sitemapUrls,
            'ok' => $hasSitemap && $sitemapUrls >= (int)($expected['sitemap_urls_min'] * 0.9),
        ],
    ];

    return [
        'expected' => $expected,
        'actual' => $actual,
        'missing' => $missing,
        'needs_regen' => $needsRegen,
        'reasons' => $reasons,
        'dirty' => $dirty,
        'dirty_reason' => $dirtyReason,
        'built_at' => $builtAt,
        'data_mtime' => $dataMtime,
        'artifacts_mtime' => $artMtime,
        'samples' => $samples,
        'jobs' => $jobs,
        'inventory' => [
            'services' => $serviceCount,
            'areas' => $areaCount,
            'keywords' => $keywordCount,
        ],
        'last_log' => $manifest['last_log'] ?? null,
        'last_jobs' => $manifest['last_jobs'] ?? [],
    ];
}

/** Record a completed full (or partial) build. */
function recordBuildComplete(array $jobResults, string $log = ''): void {
    $status = getBuildStatus();
    saveBuildManifest([
        'dirty' => false,
        'dirty_reason' => null,
        'built_at' => date('c'),
        'last_log' => $log,
        'last_jobs' => $jobResults,
        'snapshot' => [
            'combo' => $status['actual']['combo'],
            'keywords' => $status['actual']['keywords'],
            'area_hubs' => $status['actual']['area_hubs'],
            'service_hubs' => $status['actual']['service_hubs'],
            'sitemap_urls' => $status['actual']['sitemap_urls'],
            'total_php_pages' => $status['actual']['total_php_pages'],
        ],
    ]);
}
