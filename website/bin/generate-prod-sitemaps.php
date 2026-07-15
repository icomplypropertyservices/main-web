<?php
/**
 * Generate sitemaps + robots for production domain.
 * Usage: php bin/generate-prod-sitemaps.php [https://www.icomplypropertyservices.co.uk]
 */
$prod = $argv[1] ?? 'https://www.icomplypropertyservices.co.uk';
$prod = rtrim($prod, '/');

// Force SITE_URL before config defines constants
putenv('SITE_URL=' . $prod);
$_ENV['SITE_URL'] = $prod;
$_SERVER['SITE_URL'] = $prod;
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['HTTP_HOST'] = parse_url($prod, PHP_URL_HOST) ?: 'www.icomplypropertyservices.co.uk';

require __DIR__ . '/generate-sitemap.php';
