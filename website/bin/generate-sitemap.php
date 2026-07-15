<?php
/**
 * Build sitemap index + chunked sitemap files (Google max 50,000 URLs per file).
 * Usage: php bin/generate-sitemap.php
 */
require_once __DIR__ . '/../config.php';

$base = rtrim(SITE_URL, '/');
$chunkSize = 45000; // under 50k limit
$all = [];
$seen = [];

$add = function (string $path, string $priority = '0.5') use (&$all, &$seen, $base) {
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    // Clean extensionless public URLs
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = preg_replace('#/index$#i', '', $path) ?? $path;
    if ($path === '') {
        $path = '/';
    }
    if (isset($seen[$path])) {
        return;
    }
    $seen[$path] = true;
    $loc = $path === '/' ? $base : ($base . $path);
    $all[] = ['loc' => $loc, 'priority' => $priority];
};

$exists = function (string $relPath): bool {
    $relPath = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relPath), DIRECTORY_SEPARATOR);
    return is_file(SITE_ROOT . DIRECTORY_SEPARATOR . $relPath);
};

// Core
$static = [
    ['/', '1.0', 'index.php'],
    ['/contact.php', '0.85', 'contact.php'],
    ['/privacy.php', '0.3', 'privacy.php'],
    ['/terms.php', '0.3', 'terms.php'],
    ['/shop/index.php', '0.85', 'shop/index.php'],
    ['/pages/about.php', '0.75', 'pages/about.php'],
    ['/pages/faq.php', '0.75', 'pages/faq.php'],
    ['/pages/landlords.php', '0.8', 'pages/landlords.php'],
    ['/pages/commercial.php', '0.8', 'pages/commercial.php'],
    ['/pages/packages.php', '0.8', 'pages/packages.php'],
    ['/pages/pricing.php', '0.75', 'pages/pricing.php'],
    ['/pages/care-homes.php', '0.75', 'pages/care-homes.php'],
    ['/pages/ev-chargers.php', '0.75', 'pages/ev-chargers.php'],
    ['/pages/maintenance.php', '0.75', 'pages/maintenance.php'],
    ['/pages/emergency.php', '0.75', 'pages/emergency.php'],
    ['/pages/reviews.php', '0.65', 'pages/reviews.php'],
    ['/pages/site-map.php', '0.45', 'pages/site-map.php'],
    ['/pages/resources/index.php', '0.75', 'pages/resources/index.php'],
    ['/pages/resources/eicr-guide.php', '0.7', 'pages/resources/eicr-guide.php'],
    ['/pages/resources/fire-alarm-servicing.php', '0.7', 'pages/resources/fire-alarm-servicing.php'],
    ['/pages/resources/landlord-compliance-checklist.php', '0.7', 'pages/resources/landlord-compliance-checklist.php'],
    ['/pages/resources/emergency-lighting-testing.php', '0.7', 'pages/resources/emergency-lighting-testing.php'],
    ['/pages/resources/cctv-for-business.php', '0.7', 'pages/resources/cctv-for-business.php'],
    ['/pages/resources/access-control-guide.php', '0.7', 'pages/resources/access-control-guide.php'],
    ['/pages/services/index.php', '0.9', 'pages/services/index.php'],
    ['/pages/areas/index.php', '0.9', 'pages/areas/index.php'],
    ['/pages/manufacturers/index.php', '0.9', 'pages/manufacturers/index.php'],
    ['/pages/keywords/index.php', '0.9', 'pages/keywords/index.php'],
];
foreach ($static as [$path, $pri, $file]) {
    if ($path === '/' || $exists($file)) {
        $add($path, $pri);
    }
}

foreach (getServices() as $slug => $name) {
    if ($exists('pages/services/' . $slug . '.php')) {
        $add('/pages/services/' . $slug . '.php', '0.85');
    }
}
foreach (getManufacturerCatalog() as $slug => $entry) {
    if ($exists('pages/manufacturers/' . $slug . '.php')) {
        $add('/pages/manufacturers/' . $slug . '.php', '0.72');
    }
}
foreach (getServices() as $sSlug => $sName) {
    foreach (getAreas() as $area) {
        $aSlug = areaSlug($area);
        if ($exists('pages/' . $sSlug . '/' . $aSlug . '.php')) {
            $add('/pages/' . $sSlug . '/' . $aSlug . '.php', '0.55');
        }
    }
}
foreach (getAreas() as $area) {
    $aSlug = areaSlug($area);
    if ($exists('pages/areas/' . $aSlug . '.php')) {
        $add('/pages/areas/' . $aSlug . '.php', '0.55');
    }
}

// Keyword hubs + keyword × area (do not require exists check for every area file — too slow at 100k+;
// include if keyword hub exists OR directory exists)
$kwDirsChecked = 0;
foreach (array_keys(getMajorKeywords()) as $kw) {
    $kSlug = keywordSlug($kw);
    if ($exists('pages/keywords/' . $kSlug . '.php')) {
        $add('/pages/keywords/' . $kSlug . '.php', '0.68');
    }
    $kwDir = SITE_ROOT . '/pages/keywords/' . $kSlug;
    if (!is_dir($kwDir)) {
        continue;
    }
    $kwDirsChecked++;
    foreach (getAreas() as $area) {
        $aSlug = areaSlug($area);
        // Prefer fast path: if directory exists, assume stubs generated for all areas
        $add('/pages/keywords/' . $kSlug . '/' . $aSlug . '.php', '0.5');
    }
}

// Write chunked sitemaps
$chunks = array_chunk($all, $chunkSize);
$sitemapFiles = [];
foreach ($chunks as $i => $chunk) {
    $n = $i + 1;
    $name = $n === 1 && count($chunks) === 1 ? 'sitemap-urls.xml' : "sitemap-{$n}.xml";
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($chunk as $u) {
        $xml .= '  <url><loc>' . htmlspecialchars($u['loc'], ENT_XML1) . '</loc>'
            . '<priority>' . $u['priority'] . '</priority></url>' . "\n";
    }
    $xml .= '</urlset>' . "\n";
    file_put_contents(SITE_ROOT . '/' . $name, $xml);
    $sitemapFiles[] = $name;
    echo "Wrote {$name} (" . count($chunk) . " URLs)\n";
}

// Sitemap index as sitemap.xml
$index = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($sitemapFiles as $name) {
    $index .= '  <sitemap><loc>' . htmlspecialchars($base . '/' . $name, ENT_XML1) . '</loc></sitemap>' . "\n";
}
$index .= '</sitemapindex>' . "\n";
file_put_contents(SITE_ROOT . '/sitemap.xml', $index);

$robots = "User-agent: *\nAllow: /\n\n"
    . "Sitemap: {$base}/sitemap.xml\n\n"
    . "Disallow: /admin/\n"
    . "Disallow: /bin/\n"
    . "Disallow: /data/\n"
    . "Disallow: /config.php\n"
    . "Disallow: /config.local.php\n";
file_put_contents(SITE_ROOT . '/robots.txt', $robots);

echo "Total URLs: " . count($all) . "\n";
echo "Keyword area dirs found: {$kwDirsChecked}\n";
echo "Sitemap index: sitemap.xml (" . count($sitemapFiles) . " parts)\n";
