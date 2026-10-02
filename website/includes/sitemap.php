<?php
/**
 * Compact sitemap — hubs plus Tier-1 service×town pages that have a bespoke
 * article. AOV and barrier town pages for places over 10,000 stay in.
 * Keyword×town, non-Tier-1 towns, area-town templates, and services
 * without their own town copy stay out. Broken or unknown URLs stay out.
 * Never lists /shop/sitemap.xml or /products/sitemap.xml (separate sites; 404 here).
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

/** Paths that 404 or 301 — never list these. */
function icomplySitemapBannedPaths(): array
{
    return [
        '/privacy-policy' => true,
        '/privacy-policy/' => true,
        '/terms-and-conditions' => true,
        '/terms-and-conditions/' => true,
        '/thank-you' => true,
        '/404' => true,
        '/404/' => true,
        '/lead-popup-form' => true,
        '/lead-popup-form/' => true,
        '/shop/sitemap' => true,
        '/shop/sitemap/' => true,
        '/products/sitemap' => true,
        '/products/sitemap/' => true,
    ];
}

/** Prefer dist/ (what Netlify publishes) when a build is present. */
function icomplySitemapPublishRoot(): string
{
    $dist = dirname(SITE_ROOT) . DIRECTORY_SEPARATOR . 'dist';
    if (is_dir($dist) && is_file($dist . DIRECTORY_SEPARATOR . 'index.html')) {
        return $dist;
    }
    return SITE_ROOT;
}

/**
 * File lookup path. Trailing slashes stay in the public loc (shop canonicals)
 * but the built file is shop/index.html, not a directory URL.
 */
function icomplySitemapLookupPath(string $urlPath): string
{
    $urlPath = '/' . ltrim(str_replace('\\', '/', $urlPath), '/');
    $urlPath = preg_replace('#\.php$#i', '', $urlPath) ?? $urlPath;
    $urlPath = preg_replace('#/index$#i', '', $urlPath) ?? $urlPath;
    if ($urlPath !== '/') {
        $urlPath = rtrim($urlPath, '/');
    }
    if ($urlPath === '') {
        $urlPath = '/';
    }
    return $urlPath;
}

/** True when the pretty URL has a built file on the publish root. */
function icomplySitemapUrlHasFile(string $urlPath): bool
{
    $urlPath = icomplySitemapLookupPath($urlPath);
    $root = icomplySitemapPublishRoot();
    if ($urlPath === '/') {
        return is_file($root . DIRECTORY_SEPARATOR . 'index.html')
            || is_file($root . DIRECTORY_SEPARATOR . 'index.php');
    }
    $rel = ltrim($urlPath, '/');
    $candidates = [
        $rel . '.php',
        $rel . '.html',
        $rel . DIRECTORY_SEPARATOR . 'index.php',
        $rel . DIRECTORY_SEPARATOR . 'index.html',
        $rel,
        'pages/' . $rel . '.php',
        'pages/' . $rel . DIRECTORY_SEPARATOR . 'index.php',
    ];
    foreach ($candidates as $c) {
        if (is_file($root . DIRECTORY_SEPARATOR . $c)) {
            return true;
        }
    }
    return false;
}

/**
 * @return list<array{path:string,priority:string}>
 */
function icomplySitemapEntries(): array
{
    $banned = icomplySitemapBannedPaths();
    $entries = [];
    $seen = [];
    $serviceSlugs = function_exists('getServices') ? getServices() : [];
    $areaSlugSet = [];
    if (function_exists('getAreas') && function_exists('areaSlug')) {
        foreach (getAreas() as $area) {
            $areaSlugSet[areaSlug((string)$area)] = true;
        }
    }

    $add = static function (string $path, string $priority = '0.5') use (&$entries, &$seen, $banned, $serviceSlugs, $areaSlugSet): void {
        $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#\.php$#i', '', $path) ?? $path;
        $path = preg_replace('#/index$#i', '', $path) ?? $path;
        if ($path === '') {
            $path = '/';
        }
        if (isset($seen[$path]) || isset($banned[$path])) {
            return;
        }
        if (preg_match('#^/shop/[^/]+/.+#', $path) || preg_match('#^/products/.+#', $path)) {
            return;
        }
        if (str_contains($path, '/sitemap')) {
            return;
        }
        if (preg_match('#^/pages/packages/hmo(?:-|$)#', $path)) {
            return;
        }
        if (preg_match('#-photo\.(jpe?g|png)$#i', $path)) {
            return;
        }
        // Hard reject /pages/{service}/{town} even if a leftover matrix file
        // sits in dist/. Keep hub prefixes, plus Tier-1 service×town pages
        // that have a bespoke article (electrical/stockport, gas/warrington).
        $tier1ServiceTown = false;
        if (preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
            $okPrefix = ['services', 'keywords', 'areas', 'manufacturers', 'resources', 'packages', 'jobs', 'commercial', 'nurse-call', 'aov', 'aov-air-handling', 'barriers'];
            $tier1ServiceTown = !in_array($m[1], $okPrefix, true)
                && function_exists('icomplyPathIsIndexable')
                && icomplyPathIsIndexable($path);
            if (!in_array($m[1], $okPrefix, true) && !$tier1ServiceTown) {
                return;
            }
        }
        // Keyword hubs and AOV/barrier town pages are generated at export time.
        $isKeywordLoc = (bool)preg_match('#^/pages/keywords(/[a-z0-9\-]+){1,2}$#', $path);
        $isTownLoc = (bool)preg_match('#^/pages/(aov|barriers)(/[a-z0-9\-]+)?$#', $path);
        $isMfrTown = (bool)preg_match('#^/pages/(aov-air-handling|barriers)/([a-z0-9\-]+)$#', $path);
        $isNurseNationwide = false;
        if (preg_match('#^/pages/nurse-call/([a-z0-9\-]+)$#', $path, $ncMatch) && function_exists('nationwideAreaRow')) {
            $isNurseNationwide = nationwideAreaRow($ncMatch[1]) !== null;
        }
        $isManufacturerHub = false;
        $isBarrierBrandLoc = false;
        if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)$#', $path, $brandMatch) && function_exists('getManufacturerBySlug')) {
            $brandEntry = getManufacturerBySlug($brandMatch[1]);
            $isManufacturerHub = is_array($brandEntry);
            $isBarrierBrandLoc = $isManufacturerHub && in_array('barriers', $brandEntry['services'] ?? [], true);
        }
        if (!$isKeywordLoc && !$isTownLoc && !$isMfrTown && !$isBarrierBrandLoc && !$isManufacturerHub && !$tier1ServiceTown && !$isNurseNationwide && !icomplySitemapUrlHasFile($path)) {
            return;
        }
        $seen[$path] = true;
        $entries[] = ['path' => $path, 'priority' => $priority];
    };

    $publish = icomplySitemapPublishRoot();
    $exists = static function (string $relPath) use ($publish): bool {
        $relPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPath), DIRECTORY_SEPARATOR);
        return is_file($publish . DIRECTORY_SEPARATOR . $relPath)
            || is_file(SITE_ROOT . DIRECTORY_SEPARATOR . $relPath);
    };

    $static = [
        ['/', '1.0', 'index.php'],
        ['/contact', '0.85', 'contact.php'],
        ['/become-a-subcontractor', '0.7', 'become-a-subcontractor.php'],
        ['/privacy', '0.3', 'privacy.php'],
        ['/terms', '0.3', 'terms.php'],
        ['/pages/about', '0.75', 'pages/about.php'],
        ['/pages/faq', '0.75', 'pages/faq.php'],
        ['/pages/landlords', '0.8', 'pages/landlords.php'],
        ['/pages/commercial', '0.8', 'pages/commercial.php'],
        ['/pages/packages', '0.8', 'pages/packages.php'],
        ['/pages/packages/compliance-bundle', '0.85', 'pages/packages/compliance-bundle.php'],
        ['/pages/packages/landlord-pack', '0.8', 'pages/packages/landlord-pack.php'],
        ['/pages/pricing', '0.75', 'pages/pricing.php'],
        ['/pages/care-homes', '0.75', 'pages/care-homes.php'],
        ['/pages/ev-chargers', '0.75', 'pages/ev-chargers.php'],
        ['/pages/maintenance', '0.75', 'pages/maintenance.php'],
        ['/pages/emergency', '0.75', 'pages/emergency.php'],
        ['/pages/reviews', '0.65', 'pages/reviews.php'],
        ['/pages/site-map', '0.7', 'pages/site-map.php'],
        ['/pages/asbestos-jobs', '0.8', 'pages/asbestos-jobs.php'],
        ['/pages/resources', '0.75', 'pages/resources.php'],
        ['/pages/resources/eicr-guide', '0.7', 'pages/resources/eicr-guide.php'],
        ['/pages/resources/fire-alarm-servicing', '0.7', 'pages/resources/fire-alarm-servicing.php'],
        ['/pages/resources/landlord-compliance-checklist', '0.7', 'pages/resources/landlord-compliance-checklist.php'],
        ['/pages/resources/emergency-lighting-testing', '0.7', 'pages/resources/emergency-lighting-testing.php'],
        ['/pages/emergency-lighting-jobs', '0.8', 'pages/emergency-lighting-jobs.php'],
        ['/pages/resources/cctv-for-business', '0.7', 'pages/resources/cctv-for-business.php'],
        ['/pages/resources/access-control-guide', '0.7', 'pages/resources/access-control-guide.php'],
        ['/pages/services/aov-air-handling', '0.96', 'pages/services/aov-air-handling.php'],
        ['/pages/packages/let-ready', '0.78', 'pages/packages/let-ready.php'],
        ['/pages/packages/workplace-essentials', '0.78', 'pages/packages/workplace-essentials.php'],
        ['/pages/packages/fire-ready', '0.78', 'pages/packages/fire-ready.php'],
        ['/pages/services', '0.95', 'pages/services.php'],
        ['/pages/areas', '0.9', 'pages/areas.php'],
        ['/pages/areas/manchester', '0.8', 'pages/areas/manchester.php'],
        ['/pages/areas/burnley', '0.8', 'pages/areas/burnley.php'],
        ['/pages/manufacturers', '0.9', 'pages/manufacturers.php'],
        ['/pages/keywords', '0.9', 'pages/keywords.php'],
        ['/pages/aov', '0.85', 'pages/aov/index.php'],
        // Trailing slash is the canonical (live /shop 301s to /shop/).
        ['/shop/', '0.8', 'shop/index.html'],
        ['/shop/fire/', '0.75', 'shop/fire/index.html'],
        ['/shop/electrical/', '0.75', 'shop/electrical/index.html'],
        ['/shop/security/', '0.75', 'shop/security/index.html'],
        ['/shop/gas/', '0.75', 'shop/gas/index.html'],
        ['/products', '0.8', 'pages/products.php'],
    ];
    foreach ($static as [$path, $pri, $file]) {
        if ($path === '/' || $exists($file) || $exists(preg_replace('#\.php$#', '/index.php', $file) ?? $file)) {
            $add($path, $pri);
        }
    }
    // Barriers job hub is routed from the keyword catalogue (no stub file).
    $add('/pages/keywords/car-park-barrier-access', '0.8');

    // Resource articles that exist on the publish root (not source-only).
    foreach (glob($publish . '/pages/jobs/*.php') ?: [] as $jobFile) {
        $base = basename($jobFile, '.php');
        if ($base === 'index') {
            continue;
        }
        $add('/pages/jobs/' . $base, '0.8');
    }
    foreach (glob($publish . '/pages/commercial/*.php') ?: [] as $jobFile) {
        $base = basename($jobFile, '.php');
        if ($base === 'index') {
            continue;
        }
        $add('/pages/commercial/' . $base, '0.7');
    }
    if (is_file($publish . '/pages/water-wras.php')) {
        $add('/pages/water-wras', '0.8');
    }
    foreach (glob($publish . '/pages/resources/*.php') ?: [] as $resFile) {
        $base = basename($resFile, '.php');
        if ($base === 'index') {
            continue;
        }
        $add('/pages/resources/' . $base, '0.7');
    }

    // Quality SEO hubs (wave 1). Never include HMO package landings from PR #3.
    if (!function_exists('wave1SitemapEntries')) {
        $wave1 = SITE_ROOT . '/includes/wave1.php';
        if (is_file($wave1)) {
            require_once $wave1;
        }
    }
    if (function_exists('wave1SitemapEntries')) {
        foreach (wave1SitemapEntries() as $w) {
            $add($w['path'], $w['priority']);
        }
    }

    // Service / manufacturer / area / keyword hubs: only files that exist
    // on the publish root. Do not emit the full catalogue (Quality 404).
    foreach (glob($publish . '/pages/services/*.php') ?: [] as $svcFile) {
        $base = basename($svcFile, '.php');
        if ($base === 'index') {
            continue;
        }
        // Exporter skips service stubs that are not in the live catalogue.
        if (function_exists('getServices') && !isset(getServices()[$base])) {
            continue;
        }
        $add('/pages/services/' . $base, '0.85');
    }
    foreach (glob($publish . '/pages/manufacturers/*.php') ?: [] as $mFile) {
        $base = basename($mFile, '.php');
        if ($base === 'index') {
            continue;
        }
        $add('/pages/manufacturers/' . $base, '0.72');
    }
    foreach (glob($publish . '/pages/areas/*.php') ?: [] as $aFile) {
        $base = basename($aFile, '.php');
        if ($base === 'index') {
            continue;
        }
        $add('/pages/areas/' . $base, '0.6');
    }
    foreach (glob($publish . '/pages/keywords/*.php') ?: [] as $kwFile) {
        $base = basename($kwFile, '.php');
        if ($base === 'index') {
            continue;
        }
        $add('/pages/keywords/' . $base, '0.68');
    }
    // Keyword hubs and keyword×town pages from the catalogue.
    // Skip the catalogue at request time so keywords.json cannot OOM a live PHP sitemap.
    $requestSafe = !empty($GLOBALS['ICOMPLY_SITEMAP_REQUEST_SAFE']);
    // AOV and barriers manufacturer×area pages only. Other brand×town URLs
    // are exported but omitted here, same rule as the full keyword×town matrix.
    if (!$requestSafe && function_exists('getManufacturerCatalog') && function_exists('manufacturerWizardFamily')) {
        foreach (getManufacturerCatalog() as $mSlug => $mEntry) {
            if (!is_array($mEntry) || manufacturerWizardFamily($mEntry) === null) {
                continue;
            }
            foreach (manufacturerAreasFor($mEntry) as $town) {
                $add('/pages/manufacturers/' . $mSlug . '/' . areaSlug((string)$town), '0.64');
            }
        }
    }
    if (!$requestSafe && function_exists('getMajorKeywords') && function_exists('keywordSlug')) {
        $family = [];
        if (function_exists('getElectricalGasMatrixKeywordSlugs')) {
            foreach (getElectricalGasMatrixKeywordSlugs() as $fs) {
                $family[keywordSlug($fs)] = true;
            }
        }
        $areaSlugs = [];
        if (function_exists('getAreas') && function_exists('areaSlug')) {
            foreach (getAreas() as $areaName) {
                $areaSlugs[] = areaSlug((string)$areaName);
            }
        }
        $keywordMeta = getMajorKeywords();
        foreach (array_keys($keywordMeta) as $kw) {
            $slug = keywordSlug($kw);
            $add('/pages/keywords/' . $slug, isset($family[$slug]) ? '0.78' : '0.68');
            if (function_exists('icomplyIndexMode') && icomplyIndexMode() === 'tiered') {
                continue;
            }
            if (!empty($keywordMeta[$slug]['hub_only'])) {
                continue;
            }
            foreach ($areaSlugs as $town) {
                if ($town === '') {
                    continue;
                }
                $add('/pages/keywords/' . $slug . '/' . $town, '0.55');
            }
        }
    }
    $barriersInc = SITE_ROOT . '/includes/barriers.php';
    if (is_file($barriersInc)) {
        require_once $barriersInc;
        if (function_exists('barriersPlaces')) {
            foreach (barriersPlaces() as $place) {
                $slug = (string)($place['slug'] ?? '');
                if ($slug !== '') {
                    $add('/pages/barriers/' . $slug, '0.55');
                }
            }
        }
    }
    if (function_exists('getManufacturerCatalog')) {
        foreach (getManufacturerCatalog() as $mSlug => $mEntry) {
            if (!is_array($mEntry) || !in_array('barriers', $mEntry['services'] ?? [], true)) {
                continue;
            }
            $add('/pages/manufacturers/' . $mSlug, !empty($mEntry['partner']) ? '0.8' : '0.7');
        }
    }
    if (!$requestSafe && function_exists('getNationwideAreaRows')) {
        foreach (getNationwideAreaRows() as $row) {
            $slug = (string)($row['slug'] ?? '');
            if ($slug !== '') {
                $add('/pages/nurse-call/' . $slug, '0.62');
            }
        }
    }
    if (function_exists('getAreas') && function_exists('areaSlug')) {
        foreach (getAreas() as $area) {
            $add('/pages/areas/' . areaSlug((string)$area), '0.6');
        }
    }
    if (function_exists('getManufacturerCatalog')) {
        foreach (array_keys(getManufacturerCatalog()) as $slug) {
            $slug = (string)$slug;
            if ($slug === '' || $slug === 'index') {
                continue;
            }
            $add('/pages/manufacturers/' . $slug, '0.72');
        }
    }
    if (function_exists('getServices')) {
        $townSlugs = [];
        if (function_exists('getAreas') && function_exists('areaSlug')) {
            foreach (getAreas() as $areaName) {
                $town = areaSlug((string)$areaName);
                if ($town !== '') {
                    $townSlugs[] = $town;
                }
            }
        }
        foreach (array_keys(getServices()) as $slug) {
            $slug = (string)$slug;
            $add('/pages/services/' . $slug, '0.85');
            foreach ($townSlugs as $town) {
                $add('/pages/' . $slug . '/' . $town, '0.5');
            }
        }
    }

    if (!function_exists('aovPlaces')) {
        $aovPlaceFile = SITE_ROOT . '/includes/aov-place.php';
        if (is_file($aovPlaceFile)) {
            require_once $aovPlaceFile;
        }
    }
    if (function_exists('aovPlaces')) {
        $add('/pages/aov', '0.85');
        foreach (array_keys(aovPlaces()) as $slug) {
            $add('/pages/aov/' . $slug, '0.64');
        }
    }

    if (!$requestSafe && function_exists('icomplyMfrIndexableTowns') && function_exists('areaSlug')) {
        foreach (['aov-air-handling', 'barriers'] as $svc) {
            foreach (icomplyMfrIndexableTowns() as $town) {
                $add('/pages/' . $svc . '/' . areaSlug($town), '0.64');
            }
        }
    }

    if (!function_exists('icomplyUkTownRoutes')) {
        $townFile = SITE_ROOT . '/includes/uk-towns.php';
        if (is_file($townFile)) {
            require_once $townFile;
        }
    }
    if (function_exists('icomplyUkTownRoutes')) {
        foreach (icomplyUkTownRoutes() as $townPath) {
            $depth = substr_count(trim((string)$townPath, '/'), '/');
            $add((string)$townPath, $depth > 1 ? '0.64' : '0.86');
        }
    }

    return $entries;
}

function icomplyBuildSitemapXml(string $baseUrl): string
{
    $base = rtrim($baseUrl, '/');
    if (str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
        $base = 'https://icomplypropertyservices.co.uk';
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (icomplySitemapEntries() as $u) {
        $loc = $u['path'] === '/' ? $base . '/' : $base . $u['path'];
        $xml .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
            . '<priority>' . $u['priority'] . '</priority></url>' . "\n";
    }
    $xml .= '</urlset>' . "\n";
    return $xml;
}

/** Tiny urlset that never touches keywords.json — last-resort 200. */
function icomplyBuildSafeSitemapXml(string $baseUrl): string
{
    $GLOBALS['ICOMPLY_SITEMAP_REQUEST_SAFE'] = true;
    try {
        return icomplyBuildSitemapXml($baseUrl);
    } finally {
        unset($GLOBALS['ICOMPLY_SITEMAP_REQUEST_SAFE']);
    }
}

function icomplyRewriteSitemapHost(string $xml, string $baseUrl): string
{
    $base = rtrim($baseUrl, '/');
    if (str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
        $base = 'https://icomplypropertyservices.co.uk';
    }
    return str_replace(
        [
            'https://www.icomplypropertyservices.co.uk',
            'http://www.icomplypropertyservices.co.uk',
            'https://icomplypropertyservices.co.uk',
            'http://icomplypropertyservices.co.uk',
            'http://localhost/icomply',
            'https://localhost/icomply',
        ],
        $base,
        $xml
    );
}

/**
 * Request-time sitemap: prefer the pre-built urlset on disk.
 * Never generate keyword catalogues here (that is what 500'd live).
 */
function icomplyServeSitemapXml(string $baseUrl): string
{
    $file = SITE_ROOT . '/sitemap.xml';
    if (is_file($file) && is_readable($file)) {
        $xml = (string)file_get_contents($file);
        if ($xml !== '' && str_contains($xml, '<urlset') && !str_contains($xml, '<sitemapindex')) {
            return icomplyRewriteSitemapHost($xml, $baseUrl);
        }
    }
    return icomplyBuildSafeSitemapXml($baseUrl);
}

function icomplyCanonicalSiteBase(string $baseUrl): string
{
    $base = rtrim($baseUrl, '/');
    if ($base === '' || str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
        return 'https://icomplypropertyservices.co.uk';
    }
    return $base;
}

/** robots.txt may name only the one sitemap this site actually publishes. */
function icomplyRobotsTxt(string $baseUrl): string
{
    $base = icomplyCanonicalSiteBase($baseUrl);
    return "User-agent: *\nAllow: /\n\n"
        . "Sitemap: {$base}/sitemap.xml\n\n"
        . "Disallow: /admin/\n"
        . "Disallow: /bin/\n"
        . "Disallow: /data/\n"
        . "Disallow: /config.php\n"
        . "Disallow: /config.local.php\n";
}

function icomplyDeleteSitemapChunks(string $root): void
{
    if ($root === '' || !is_dir($root)) {
        return;
    }
    foreach (glob($root . '/sitemap-*.xml') ?: [] as $old) {
        @unlink($old);
    }
    @unlink($root . '/sitemap-urls.xml');
    $dir = $root . '/sitemaps';
    if (!is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '/*') ?: [] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    @rmdir($dir);
}

function icomplyJsTemplateLiteral(string $value): string
{
    return str_replace(['\\', '`', '${'], ['\\\\', '\\`', '\\${'], $value);
}

/**
 * Edge functions serve the curated sitemap and robots even if a Netlify
 * file-crawl plugin rewrites dist/sitemap.xml after the build command.
 */
function icomplyWriteSitemapEdgeFunctions(string $xml, string $robots): void
{
    $dir = dirname(SITE_ROOT) . '/netlify/edge-functions';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot mkdir ' . $dir);
    }
    $robotsJs = icomplyJsTemplateLiteral($robots);
    // A full matrix urlset is tens of megabytes. Publish sitemap.xml as a file.
    // This module has no path config, so it cannot shadow that file.
    if (strlen($xml) > 200000) {
        file_put_contents($dir . '/sitemap.js', <<<'JS'
// Static sitemap.xml is the published urlset. No path config on purpose.
export default async () => new Response('', { status: 204 });
JS);
        file_put_contents($dir . '/robots.js', <<<JS
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
        return;
    }
    $xmlJs = icomplyJsTemplateLiteral($xml);
    file_put_contents($dir . '/sitemap.js', <<<JS
export default async () => {
  return new Response(`{$xmlJs}`, {
    headers: {
      "content-type": "application/xml; charset=utf-8",
      "cache-control": "public, max-age=3600",
    },
  });
};

export const config = { path: "/sitemap.xml" };
JS);
    file_put_contents($dir . '/robots.js', <<<JS
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
}

/**
 * @param list<array{path:string,priority:string}> $entries
 */
function icomplySitemapXmlFromEntries(string $baseUrl, array $entries): string
{
    $base = icomplyCanonicalSiteBase($baseUrl);
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($entries as $u) {
        $loc = $u['path'] === '/' ? $base . '/' : $base . $u['path'];
        $xml .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
            . '<priority>' . $u['priority'] . '</priority></url>' . "\n";
    }
    $xml .= '</urlset>' . "\n";
    return $xml;
}

function icomplyDistHasPage(string $dist, string $path): bool
{
    $path = icomplySitemapLookupPath($path);
    if ($path === '/') {
        return is_file($dist . '/index.html') || is_file($dist . '/index.php');
    }
    $rel = ltrim($path, '/');
    foreach ([$rel . '.php', $rel . '.html', $rel . '/index.html', $rel . '/index.php'] as $candidate) {
        if (is_file($dist . '/' . $candidate)) {
            return true;
        }
    }
    return false;
}

/**
 * @return array{urls:int,file:string,xml:string}
 */
function icomplyInstallSitemap(string $baseUrl, string $xml, ?string $dist = null): array
{
    if (!str_contains($xml, '<urlset') || str_contains($xml, '<sitemapindex')) {
        throw new RuntimeException('Refusing to publish a sitemap index');
    }
    if (str_contains($xml, '/products/sitemap') || str_contains($xml, '/shop/sitemap')) {
        throw new RuntimeException('Refusing to publish nested shop/products sitemap locs');
    }

    icomplyDeleteSitemapChunks(SITE_ROOT);
    $file = SITE_ROOT . '/sitemap.xml';
    file_put_contents($file, $xml);

    $robots = icomplyRobotsTxt($baseUrl);
    file_put_contents(SITE_ROOT . '/robots.txt', $robots);
    icomplyWriteSitemapEdgeFunctions($xml, $robots);

    if ($dist !== null && is_dir($dist)) {
        icomplyDeleteSitemapChunks($dist);
        file_put_contents($dist . '/sitemap.xml', $xml);
        file_put_contents($dist . '/robots.txt', $robots);
    }

    return ['urls' => substr_count($xml, '<url>'), 'file' => $file, 'xml' => $xml];
}

/**
 * Write a single compact sitemap.xml and remove stale chunk files.
 * @return array{urls:int,file:string}
 */
function icomplyWriteSitemapFiles(string $baseUrl): array
{
    $installed = icomplyInstallSitemap($baseUrl, icomplyBuildSitemapXml($baseUrl), null);
    $dist = dirname(SITE_ROOT) . DIRECTORY_SEPARATOR . 'dist';
    if (is_dir($dist)) {
        icomplyDeleteSitemapChunks($dist);
        file_put_contents($dist . '/sitemap.xml', $installed['xml']);
        file_put_contents($dist . '/robots.txt', icomplyRobotsTxt($baseUrl));
    }
    return ['urls' => $installed['urls'], 'file' => $installed['file']];
}

/**
 * Publish only allowlisted locs whose HTML was actually written into dist.
 * @return array{urls:int,file:string}
 */
function icomplyWriteSitemapForDist(string $dist, string $baseUrl): array
{
    $entries = [];
    foreach (icomplySitemapEntries() as $entry) {
        if (icomplyDistHasPage($dist, $entry['path'])) {
            $entries[] = $entry;
        }
    }
    if (count($entries) < 800) {
        throw new RuntimeException('Published sitemap has only ' . count($entries) . ' URLs (expected hubs plus town pages)');
    }
    $xml = icomplySitemapXmlFromEntries($baseUrl, $entries);
    $installed = icomplyInstallSitemap($baseUrl, $xml, $dist);
    return ['urls' => $installed['urls'], 'file' => $installed['file']];
}
