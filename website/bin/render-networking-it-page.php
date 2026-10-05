<?php
/** CLI: php bin/render-networking-it-page.php keyword|job|service {slug} — prints the page HTML. */
declare(strict_types=1);
$kind = $argv[1] ?? '';
$slug = $argv[2] ?? '';
$paths = ['keyword' => '/pages/keywords/', 'job' => '/pages/jobs/', 'service' => '/pages/services/'];
if (!isset($paths[$kind]) || !preg_match('/^[a-z0-9-]+$/', $slug)) {
    fwrite(STDERR, "usage: keyword|job|service slug\n");
    exit(2);
}
$_SERVER['REQUEST_URI'] = $paths[$kind] . $slug;
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
require dirname(__DIR__) . '/pages/' . ['keyword' => 'keywords', 'job' => 'jobs', 'service' => 'services'][$kind] . '/' . $slug . '.php';
