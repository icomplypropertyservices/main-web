<?php
/**
 * Compact, accurate sitemap — core pages + hubs + resources + services +
 * areas + manufacturers + keyword hubs + service×area landings.
 * Never includes keyword×area (the 200k+ junk that made live generate 470 parts / 500).
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
        '/shop' => true,
        '/products' => true,
        '/thank-you' => true,
    ];
}

/**
 * @return list<array{path:string,priority:string}>
 */
function icomplySitemapEntries(): array
{
    $banned = icomplySitemapBannedPaths();
    $entries = [];
    $seen = [];

    $add = static function (string $path, string $priority = '0.5') use (&$entries, &$seen, $banned): void {
        $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#\.php$#i', '', $path) ?? $path;
        $path = preg_replace('#/index$#i', '', $path) ?? $path;
        if ($path === '') {
            $path = '/';
        }
        if (isset($seen[$path]) || isset($banned[$path])) {
            return;
        }
        if (str_starts_with($path, '/shop') || str_starts_with($path, '/products')) {
            return;
        }
        if (preg_match('#-photo\.(jpe?g|png)$#i', $path)) {
            return;
        }
        $seen[$path] = true;
        $entries[] = ['path' => $path, 'priority' => $priority];
    };

    $exists = static function (string $relPath): bool {
        $relPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPath), DIRECTORY_SEPARATOR);
        return is_file(SITE_ROOT . DIRECTORY_SEPARATOR . $relPath);
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
    ];
    foreach ($static as [$path, $pri, $file]) {
        if ($path === '/' || $exists($file) || $exists(preg_replace('#\.php$#', '/index.php', $file) ?? $file)) {
            $add($path, $pri);
        }
    }

    // All resource articles (existing + wave-1 fortnight guides).
    foreach (glob(SITE_ROOT . '/pages/resources/*.php') ?: [] as $resFile) {
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

    if (function_exists('getServices')) {
        foreach (array_keys(getServices()) as $slug) {
            $add('/pages/services/' . $slug, '0.85');
        }
    }
    if (function_exists('getManufacturerCatalog')) {
        foreach (array_keys(getManufacturerCatalog()) as $slug) {
            $add('/pages/manufacturers/' . $slug, '0.72');
        }
    }
    if (function_exists('getAreas') && function_exists('areaSlug')) {
        foreach (getAreas() as $area) {
            $add('/pages/areas/' . areaSlug($area), '0.6');
        }
    }
    // Keyword hubs only — not keyword × area (that explosion 500'd live sitemap.xml).
    // Skip at request time: keywords.json is 3MB+ and is what OOMs the live 500.
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
        // Featured electrical + gas × popular towns so those families are crawlable
        // without dumping the full matrix into sitemap.xml.
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
    if (!$requestSafe && function_exists('getServices') && function_exists('getAreas') && function_exists('areaSlug')) {
        foreach (array_keys(getServices()) as $sSlug) {
            foreach (getAreas() as $area) {
                $add('/pages/' . $sSlug . '/' . areaSlug($area), '0.55');
            }
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
