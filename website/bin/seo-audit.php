#!/usr/bin/env php
<?php
/**
 * CLI SEO audit sample across page types.
 */
$root = realpath(__DIR__ . '/..');
$base = 'http://localhost:8000';

$samples = [
    '/',
    '/contact.php',
    '/pages/services/index.php',
    '/pages/services/electrical.php',
    '/pages/services/fire-alarms.php',
    '/pages/electrical/manchester.php',
    '/pages/fire-alarms/stockport.php',
    '/pages/cctv/liverpool.php',
    '/pages/areas/manchester.php',
    '/pages/gas-systems/bolton.php',
    '/robots.txt',
    '/sitemap.xml',
];

$checks = [
    'title' => '/<title>[^<]{20,}<\/title>/i',
    'meta_desc' => '/name="description" content="[^"]{50,}"/i',
    'canonical' => '/rel="canonical"/i',
    'h1' => '/<h1[\s>]/i',
    'schema' => '/application\/ld\+json/i',
    'image' => '/assets\/images\//i',
    'faq_or_local' => '/FAQPage|areaServed|LocalBusiness/i',
];

$fail = 0;
foreach ($samples as $path) {
    $url = $base . $path;
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        echo "FAIL fetch {$path}\n";
        $fail++;
        continue;
    }
    if (preg_match('/\.(xml|txt)$/', $path)) {
        $ok = strlen($body) > 50;
        echo ($ok ? 'OK  ' : 'FAIL') . " static {$path} (" . strlen($body) . " bytes)\n";
        if (!$ok) $fail++;
        continue;
    }
    $missing = [];
    foreach ($checks as $name => $re) {
        if (!preg_match($re, $body)) $missing[] = $name;
    }
    if (preg_match('/Fatal error|Parse error|Warning:/', $body)) $missing[] = 'php_error';
    if ($missing) {
        echo 'FAIL ' . $path . ' missing: ' . implode(', ', $missing) . "\n";
        $fail++;
    } else {
        echo "OK   {$path}\n";
    }
}
echo $fail === 0 ? "\nALL SEO CHECKS PASSED\n" : "\n{$fail} FAILURES\n";
exit($fail > 0 ? 1 : 0);
