<?php
/**
 * HTTP test: sitemap.xml is 200 + urlset, sample locs are not 404.
 * Usage: php bin/check-sitemap-http.php [http://127.0.0.1:8000]
 */
require_once __DIR__ . '/../config.php';

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$fail = 0;
$say = static function (bool $ok, string $name, string $detail = '') use (&$fail): void {
    echo ($ok ? 'PASS' : 'FAIL') . ' ' . $name . ($detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
};

$fetch = static function (string $url, bool $follow = true): array {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 25,
            'ignore_errors' => true,
            'follow_location' => $follow ? 1 : 0,
            'header' => "User-Agent: iComplySitemapHttp/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $code = (int)$m[1];
    }
    return [$code, is_string($body) ? $body : ''];
};

[$code, $xml] = $fetch($base . '/sitemap.xml');
$say($code === 200, 'GET /sitemap.xml HTTP 200', (string)$code);
$say($xml !== '' && str_contains($xml, '<urlset'), 'body is <urlset> not a 470-part index');
$say(!str_contains($xml, '<sitemapindex'), 'not a sitemapindex');
$say(!str_contains($xml, '/privacy-policy'), 'no /privacy-policy');
$say(!str_contains($xml, '/pages/gas-systems/stockport'), 'no /pages/gas-systems/{town} (404 on default export)');
$say(!str_contains($xml, '/pages/electrical/manchester'), 'no /pages/electrical/{town} (404 on default export)');
$say(str_contains($xml, '/pages/areas') && str_contains($xml, '/pages/resources/eicr-guide'), 'includes hubs + resource guide');

$samples = [
    '/',
    '/pages/areas',
    '/pages/manufacturers',
    '/pages/resources',
    '/pages/resources/eicr-guide',
    '/pages/resources/fire-alarm-servicing',
    '/pages/resources/emergency-lighting-testing',
    '/pages/resources/cctv-for-business',
    '/pages/resources/access-control-guide',
    '/pages/resources/landlord-compliance-checklist',
    '/pages/services/fire-risk-assessments',
    '/pages/areas/stockport',
    '/privacy',
    '/terms',
];
foreach ($samples as $path) {
    $needle = $path === '/' ? null : $path . '</loc>';
    $listed = $path === '/'
        ? (bool)preg_match('#<loc>https?://[^<]+/</loc>#', $xml)
        : str_contains($xml, $needle);
    $say($listed, "sitemap lists {$path}");
    [$c] = $fetch($base . $path);
    $say($c === 200, "sample {$path} not 404", (string)$c);
}

[$partCode] = $fetch($base . '/sitemap-1.xml', false);
$say(in_array($partCode, [200, 301], true), 'GET /sitemap-1.xml is 200 or 301 (not 500)', (string)$partCode);

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
