<?php
/**
 * Build a single compact sitemap.xml (Google-safe, no keyword×area junk).
 *
 * Usage: php bin/generate-sitemap.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/sitemap.php';

$base = rtrim(SITE_URL, '/');
$result = icomplyWriteSitemapFiles($base);

$svc = function_exists('getServices') ? count(getServices()) : 0;
$areas = function_exists('getAreas') ? count(getAreas()) : 0;
$kw = function_exists('getMajorKeywords') ? count(getMajorKeywords()) : 0;
$mfr = function_exists('getManufacturerCatalog') ? count(getManufacturerCatalog()) : 0;

echo "Catalogue: services={$svc} areas={$areas} keywords={$kw} manufacturers={$mfr}\n";
echo "Wrote sitemap.xml ({$result['urls']} URLs, compact urlset — hubs + featured elec/gas samples, no service×town 404s)\n";
echo "Removed stale sitemap-*.xml parts if present\n";
