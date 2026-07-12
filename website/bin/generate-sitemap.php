#!/usr/bin/env php
<?php
/**
 * Generates a full XML sitemap for all public pages.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/seo.php';

$base = rtrim(SITE_PUBLIC_URL, '/');
$urls = [];

$add = function (string $path, string $priority = '0.5', string $freq = 'weekly') use (&$urls, $base) {
    $loc = $base . '/' . ltrim($path, '/');
    $urls[] = compact('loc', 'priority', 'freq');
};

$add('', '1.0', 'daily');
$add('contact', '0.9', 'monthly');
$add('pages/services/index', '0.95', 'weekly');

$allServices = array_merge($services, loadServices());
foreach ($allServices as $slug => $name) {
    $add("pages/services/{$slug}.php", '0.9', 'weekly');
    foreach ($areas as $area) {
        $add("pages/{$slug}/" . areaSlug($area) . '.php', '0.7', 'monthly');
    }
}
foreach ($areas as $area) {
    $add('pages/areas/' . areaSlug($area) . '.php', '0.65', 'monthly');
}

$today = date('Y-m-d');
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    $xml .= "  <url>\n";
    $xml .= '    <loc>' . htmlspecialchars($u['loc']) . "</loc>\n";
    $xml .= "    <lastmod>{$today}</lastmod>\n";
    $xml .= '    <changefreq>' . $u['freq'] . "</changefreq>\n";
    $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";

$out = __DIR__ . '/../sitemap.xml';
file_put_contents($out, $xml);
echo 'Sitemap written: ' . count($urls) . " URLs → {$out}\n";
