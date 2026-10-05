#!/usr/bin/env php
<?php
/**
 * Rewrite dist/sitemap.xml from pages that were actually exported.
 * Usage: php website/bin/publish-sitemap.php [/abs/path/to/dist]
 */
declare(strict_types=1);

$dist = $argv[1] ?? (dirname(__DIR__, 2) . '/dist');
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/sitemap.php';

$base = getenv('SITE_URL');
if (!is_string($base) || $base === '') {
    $base = 'https://icomplypropertyservices.co.uk';
}

if (!is_dir($dist) || !is_file($dist . '/index.html')) {
    fwrite(STDERR, "publish-sitemap: dist not ready at {$dist}\n");
    exit(1);
}

$result = icomplyWriteSitemapForDist($dist, $base);
if (!empty($result['kept_matrix'])) {
    echo "Kept matrix sitemap index at {$result['file']}\n";
    echo "Removed file-crawl chunks under dist/sitemaps if a plugin wrote them\n";
    exit(0);
}
echo "Published sitemap {$result['urls']} URLs → {$result['file']}\n";
echo "Removed dist/sitemaps chunks if a file-crawl plugin wrote them\n";
