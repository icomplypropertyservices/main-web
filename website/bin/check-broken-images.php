<?php
/**
 * Sample home + service/manufacturer/resource hubs: every <img> must 200.
 * Strips local SITE_URL prefixes (e.g. /icomply) so php -S -t website works.
 *
 * Usage: php bin/check-broken-images.php [http://127.0.0.1:8000]
 */
require_once __DIR__ . '/../config.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$pages = [
    '/',
    '/pages/areas',
    '/pages/manufacturers',
    '/pages/resources',
    '/pages/resources/eicr-guide',
    '/pages/services',
    '/pages/services/fire-risk-assessments',
];

$prefix = (string)(parse_url(SITE_URL, PHP_URL_PATH) ?: '');
$prefix = rtrim($prefix, '/');

$fail = 0;
$checked = [];

$fetch = static function (string $url, bool $follow = true): array {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 20,
            'ignore_errors' => true,
            'follow_location' => $follow ? 1 : 0,
            'header' => "User-Agent: IcomplyImageCheck/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    return [$code, is_string($body) ? $body : ''];
};

foreach ($pages as $path) {
    [$code, $html] = $fetch($base . $path);
    if ($code !== 200 || $html === '') {
        echo "FAIL page {$path} HTTP {$code}\n";
        $fail++;
        continue;
    }
    echo "PAGE {$path} {$code} bytes=" . strlen($html) . PHP_EOL;
    if (str_contains($html, 'cdn.shopify.com')) {
        echo "FAIL {$path} emits cdn.shopify.com\n";
        $fail++;
    }
    preg_match_all('/<img[^>]+src=["\']([^"\']+)/i', $html, $m);
    foreach (array_unique($m[1]) as $src) {
        if (preg_match('#^https?://#i', $src) && !str_contains($src, '127.0.0.1') && !str_contains($src, 'localhost')) {
            echo "FAIL external img {$src}\n";
            $fail++;
            continue;
        }
        $rel = $src;
        $rel = preg_replace('#^https?://[^/]+#', '', $rel) ?? $rel;
        if ($prefix !== '' && str_starts_with($rel, $prefix . '/')) {
            $rel = substr($rel, strlen($prefix)) ?: '/';
        }
        if ($rel === '' || $rel[0] !== '/') {
            $rel = '/' . ltrim($rel, '/');
        }
        if (isset($checked[$rel])) {
            continue;
        }
        $checked[$rel] = true;
        [$imgCode, $bytes] = $fetch($base . $rel);
        $ok = $imgCode === 200 && strlen($bytes) > 50;
        echo ($ok ? '  OK  ' : '  FAIL ') . "{$imgCode} " . strlen($bytes) . " {$rel}\n";
        if (!$ok) {
            $fail++;
        }
    }
}

echo PHP_EOL . 'assets=' . count($checked) . ' ' . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
