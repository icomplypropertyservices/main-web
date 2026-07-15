#!/usr/bin/env php
<?php
/**
 * HTTP smoke tests against SITE_URL (requires Apache/XAMPP running).
 */
require_once __DIR__ . '/../config.php';

$base = rtrim(SITE_URL, '/');
$paths = [
    '/' => ['min' => 2000, 'needles' => ['Icomply']],
    '/contact.php' => ['min' => 1000, 'needles' => ['csrf', 'gclid', '@']],
    '/pages/services/index.php' => ['min' => 2000, 'needles' => ['Services']],
    '/pages/services/fire-alarms.php' => ['min' => 2000, 'needles' => ['Fire']],
    '/pages/fire-alarms/stockport.php' => ['min' => 8000, 'needles' => ['BS 5839', 'Kentec', 'Stockport']],
    '/pages/fire-alarms/ashton-under-lyne.php' => ['min' => 5000, 'needles' => ['Ashton']],
    '/pages/fire-alarms/cheadle-hulme.php' => ['min' => 5000, 'needles' => ['Cheadle']],
    '/pages/electrical/manchester.php' => ['min' => 5000, 'needles' => ['Manchester']],
    '/pages/keywords/kentec-fire-alarm-panel.php' => ['min' => 5000, 'needles' => ['Kentec']],
    '/pages/keywords/eicr.php' => ['min' => 5000, 'needles' => ['EICR']],
    '/pages/areas/manchester.php' => ['min' => 2000, 'needles' => ['Manchester']],
    '/sitemap.xml' => ['min' => 5000, 'needles' => ['<urlset', '<url>']],
    '/admin/index.php' => ['min' => 200, 'needles' => ['Admin']],
];

$fail = 0;
$pass = 0;

echo "Icomply HTTP CHECK  base={$base}\n";
echo str_repeat('=', 50) . "\n";

foreach ($paths as $path => $spec) {
    $url = $base . ($path === '/' ? '/' : $path);
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 25,
            'ignore_errors' => true,
            'header' => "User-Agent: IcomplyHttpCheck/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    $len = is_string($body) ? strlen($body) : 0;
    $ok = $code === 200 && $len >= ($spec['min'] ?? 0);
    $missing = [];
    if (is_string($body)) {
        foreach ($spec['needles'] ?? [] as $n) {
            if (stripos($body, $n) === false) {
                $missing[] = $n;
                $ok = false;
            }
        }
        // Nested pages should not use relative index.php alone in nav
        if (strpos($path, '/pages/') === 0 && preg_match('/href="index\.php"/', $body)) {
            $missing[] = 'relative-index.php-nav';
            $ok = false;
        }
    } else {
        $ok = false;
    }

    if ($ok) {
        $pass++;
        echo "[PASS] {$code} {$len} {$path}\n";
    } else {
        $fail++;
        echo "[FAIL] {$code} {$len} {$path}" . ($missing ? ' missing:' . implode(',', $missing) : '') . "\n";
    }
}

echo str_repeat('=', 50) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
