<?php
/**
 * Compact, accurate sitemap — core pages + shop/products + service hubs +
 * areas + manufacturers + keyword hubs + featured electrical/gas keyword×town.
 * Never lists /pages/{service}/{town} (those 404 as sitemap locs).
 * Never dumps the full keyword×area matrix (that 500'd live).
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

/** True when the pretty URL has a built file on the publish root. */
function icomplySitemapUrlHasFile(string $urlPath): bool
{
    $urlPath = '/' . ltrim(str_replace('\\', '/', $urlPath), '/');
    $urlPath = preg_replace('#\.php$#i', '', $urlPath) ?? $urlPath;
    $urlPath = preg_replace('#/index$#i', '', $urlPath) ?? $urlPath;
    if ($urlPath === '') {
        $urlPath = '/';
    }
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
        if (preg_match('#-photo\.(jpe?g|png)$#i', $path)) {
            return;
        }
        // Hard reject /pages/{service}/{town} even if a leftover matrix file
        // sits in dist/. Keep hub prefixes, plus the Jack pilot
        // (non-fire × manchester|burnley). Fire × town stays out.
        $isPilotLoc = false;
        if (preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
            $okPrefix = ['services', 'keywords', 'areas', 'manufacturers', 'resources', 'packages'];
            $isPilotLoc = function_exists('jackPilotServiceAreaPublished')
                && jackPilotServiceAreaPublished($m[1], $m[2]);
            if (!in_array($m[1], $okPrefix, true) && !$isPilotLoc) {
                return;
            }
        }
        // Keyword hubs, featured keyword×town, and Jack pilot landings are
        // generated at export time. Do not require a source PHP file.
        $isKeywordLoc = (bool)preg_match('#^/pages/keywords(/[a-z0-9\-]+){1,2}$#', $path);
        if (!$isKeywordLoc && !$isPilotLoc && !icomplySitemapUrlHasFile($path)) {
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
        ['/privacy', '0.3', 'privacy.php'],
        ['/terms', '0.3', 'terms.php'],
        ['/pages/about', '0.75', 'pages/about.php'],
        ['/pages/faq', '0.75', 'pages/faq.php'],
        ['/pages/landlords', '0.8', 'pages/landlords.php'],
        ['/pages/commercial', '0.8', 'pages/commercial.php'],
        ['/pages/packages', '0.8', 'pages/packages.php'],
        ['/pages/pricing', '0.75', 'pages/pricing.php'],
        ['/pages/care-homes', '0.75', 'pages/care-homes.php'],
        ['/pages/ev-chargers', '0.75', 'pages/ev-chargers.php'],
        ['/pages/maintenance', '0.75', 'pages/maintenance.php'],
        ['/pages/emergency', '0.75', 'pages/emergency.php'],
        ['/pages/reviews', '0.65', 'pages/reviews.php'],
        ['/pages/site-map', '0.7', 'pages/site-map.php'],
        ['/pages/resources', '0.75', 'pages/resources.php'],
        ['/pages/resources/eicr-guide', '0.7', 'pages/resources/eicr-guide.php'],
        ['/pages/resources/fire-alarm-servicing', '0.7', 'pages/resources/fire-alarm-servicing.php'],
        ['/pages/resources/landlord-compliance-checklist', '0.7', 'pages/resources/landlord-compliance-checklist.php'],
        ['/pages/resources/emergency-lighting-testing', '0.7', 'pages/resources/emergency-lighting-testing.php'],
        ['/pages/resources/cctv-for-business', '0.7', 'pages/resources/cctv-for-business.php'],
        ['/pages/resources/access-control-guide', '0.7', 'pages/resources/access-control-guide.php'],
        ['/pages/services', '0.95', 'pages/services.php'],
        ['/pages/areas', '0.9', 'pages/areas.php'],
        ['/pages/manufacturers', '0.9', 'pages/manufacturers.php'],
        ['/pages/keywords', '0.9', 'pages/keywords.php'],
        ['/shop', '0.8', 'shop/index.html'],
        ['/shop/fire', '0.75', 'shop/fire/index.html'],
        ['/shop/electrical', '0.75', 'shop/electrical/index.html'],
        ['/shop/security', '0.75', 'shop/security/index.html'],
        ['/shop/gas', '0.75', 'shop/gas/index.html'],
        ['/products', '0.8', 'pages/products.php'],
    ];
    foreach ($static as [$path, $pri, $file]) {
        if ($path === '/' || $exists($file) || $exists(preg_replace('#\.php$#', '/index.php', $file) ?? $file)) {
            $add($path, $pri);
        }
    }

    // Resource articles that exist on the publish root (not source-only).
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
    // Keyword hubs from the catalogue (generated at export) plus a small
    // featured electrical/gas × town sample. Skip at request time so
    // keywords.json cannot OOM the live sitemap.
    $requestSafe = !empty($GLOBALS['ICOMPLY_SITEMAP_REQUEST_SAFE']);
    if (!$requestSafe && function_exists('getMajorKeywords') && function_exists('keywordSlug')) {
        $family = [];
        if (function_exists('getElectricalGasMatrixKeywordSlugs')) {
            foreach (getElectricalGasMatrixKeywordSlugs() as $fs) {
                $family[keywordSlug($fs)] = true;
            }
        }
        foreach (array_keys(getMajorKeywords()) as $kw) {
            $slug = keywordSlug($kw);
            $add('/pages/keywords/' . $slug, isset($family[$slug]) ? '0.78' : '0.68');
        }
        if (function_exists('getElectricalGasFeaturedKeywordSlugs') && function_exists('getAreas') && function_exists('areaSlug')) {
            $areasFlip = array_flip(getAreas());
            $sampleTowns = ['Stockport', 'Manchester', 'Bolton', 'Liverpool', 'Preston', 'Warrington'];
            $featured = getElectricalGasFeaturedKeywordSlugs();
            $allKw = getMajorKeywords();
            foreach (['electrical', 'gas'] as $fam) {
                foreach ($featured[$fam] ?? [] as $kwSlug) {
                    $kwSlug = keywordSlug((string)$kwSlug);
                    if ($kwSlug === '' || !isset($allKw[$kwSlug])) {
                        continue;
                    }
                    foreach ($sampleTowns as $town) {
                        if (!isset($areasFlip[$town])) {
                            continue;
                        }
                        $add('/pages/keywords/' . $kwSlug . '/' . areaSlug($town), '0.62');
                    }
                }
            }
        }
    }

    if (!$requestSafe && function_exists('jackPilotExportPaths')) {
        foreach (jackPilotExportPaths() as $path) {
            $add($path, '0.64');
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

/**
 * Write a single compact sitemap.xml and remove stale chunk files.
 * @return array{urls:int,file:string}
 */
function icomplyWriteSitemapFiles(string $baseUrl): array
{
    foreach (glob(SITE_ROOT . '/sitemap-*.xml') ?: [] as $old) {
        @unlink($old);
    }
    @unlink(SITE_ROOT . '/sitemap-urls.xml');

    $xml = icomplyBuildSitemapXml($baseUrl);
    $file = SITE_ROOT . '/sitemap.xml';
    file_put_contents($file, $xml);
    $distMap = dirname(SITE_ROOT) . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'sitemap.xml';
    if (is_dir(dirname($distMap))) {
        file_put_contents($distMap, $xml);
    }

    $base = rtrim($baseUrl, '/');
    if (str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
        $base = 'https://icomplypropertyservices.co.uk';
    }
    $robots = "User-agent: *\nAllow: /\n\n"
        . "Sitemap: {$base}/sitemap.xml\n\n"
        . "Disallow: /admin/\n"
        . "Disallow: /bin/\n"
        . "Disallow: /data/\n"
        . "Disallow: /config.php\n"
        . "Disallow: /config.local.php\n";
    file_put_contents(SITE_ROOT . '/robots.txt', $robots);

    return ['urls' => substr_count($xml, '<url>'), 'file' => $file];
}
