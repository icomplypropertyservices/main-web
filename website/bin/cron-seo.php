<?php
/**
 * SEO monitor + ping job.
 * - Verifies key URLs return 200
 * - Pings Google/Bing with sitemap
 * - IndexNow notify for sample priority URLs
 * - Writes data/seo-health.json report
 * - Applies safe auto-fixes (robots/sitemap SITE_URL, missing IndexNow key file)
 *
 * CLI:  php bin/cron-seo.php
 * Web:  /bin/cron-seo.php?key=SEO_CRON_KEY  (blocked on public host unless key matches)
 */
require_once __DIR__ . '/../config.php';

$isCli = (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
if (!$isCli) {
    $key = (string)($_GET['key'] ?? '');
    if (SEO_CRON_KEY === '' || !hash_equals((string)SEO_CRON_KEY, $key)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Forbidden\n";
        exit;
    }
    header('Content-Type: application/json; charset=utf-8');
}

$base = rtrim(SITE_URL, '/');
// Never report localhost as production health target
if (str_contains($base, 'localhost')) {
    $base = 'https://www.icomplypropertyservices.co.uk';
}

$report = [
    'ran_at' => gmdate('c'),
    'site_url' => $base,
    'checks' => [],
    'fixes' => [],
    'pings' => [],
    'ok' => true,
];

$paths = [
    '/',
    '/pages/services',
    '/pages/services/fire-risk-assessments',
    '/pages/services/kitchens',
    '/pages/fire-risk-assessments/stockport',
    '/pages/kitchens/manchester',
    '/pages/areas',
    '/pages/areas/stockport',
    '/pages/keywords',
    '/pages/keywords/eicr',
    '/pages/keywords/fire-risk-assessment',
    '/pages/site-map',
    '/contact',
    '/sitemap.xml',
    '/robots.txt',
];

$httpGet = static function (string $url, int $timeout = 25): array {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeout,
            'header' => "User-Agent: IcomplySeoMonitor/1.0\r\nAccept: */*\r\n",
            'follow_location' => 1,
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $status = (int)$m[1];
    }
    return [$status, is_string($body) ? $body : ''];
};

foreach ($paths as $path) {
    $url = $base . $path;
    [$status, $body] = $httpGet($url);
    $hasLocalhost = str_contains($body, 'http://localhost');
    $hasH1 = (bool)preg_match('/<h1/i', $body);
    $ok = $status >= 200 && $status < 400 && !$hasLocalhost;
    // XML/robots don't need h1
    if (str_ends_with($path, '.xml') || $path === '/robots.txt') {
        $ok = $status >= 200 && $status < 400 && strlen($body) > 20;
    } elseif ($path === '/' || str_starts_with($path, '/pages') || $path === '/contact') {
        $ok = $ok && ($hasH1 || strlen($body) > 2000);
    }
    $report['checks'][] = [
        'path' => $path,
        'status' => $status,
        'ok' => $ok,
        'localhost_leak' => $hasLocalhost,
        'bytes' => strlen($body),
    ];
    if (!$ok) {
        $report['ok'] = false;
    }
}

// Auto-fix: robots.txt Sitemap line + production SITE_URL in local files
$robotsPath = SITE_ROOT . '/robots.txt';
$robotsWant = "User-agent: *\nAllow: /\n\n"
    . "Sitemap: {$base}/sitemap.xml\n\n"
    . "Disallow: /admin/\n"
    . "Disallow: /bin/\n"
    . "Disallow: /data/\n"
    . "Disallow: /config.php\n"
    . "Disallow: /config.local.php\n";
$curRobots = is_file($robotsPath) ? (string)file_get_contents($robotsPath) : '';
if (!str_contains($curRobots, $base . '/sitemap.xml') || str_contains($curRobots, 'localhost')) {
    file_put_contents($robotsPath, $robotsWant);
    $report['fixes'][] = 'Updated robots.txt Sitemap to ' . $base;
}

// Ensure sitemap index references production host
$sm = SITE_ROOT . '/sitemap.xml';
if (is_file($sm)) {
    $xml = (string)file_get_contents($sm);
    if (str_contains($xml, 'localhost') || str_contains($xml, 'http://')) {
        $xml2 = preg_replace('#https?://[^/]+/#', $base . '/', $xml) ?? $xml;
        if ($xml2 !== $xml) {
            file_put_contents($sm, $xml2);
            $report['fixes'][] = 'Rewrote sitemap.xml hosts to ' . $base;
        }
    }
}
foreach (glob(SITE_ROOT . '/sitemap-*.xml') ?: [] as $part) {
    $xml = (string)file_get_contents($part);
    if (str_contains($xml, 'localhost')) {
        $xml2 = str_replace(
            ['http://localhost/icomply', 'https://localhost/icomply'],
            [$base, $base],
            $xml
        );
        if ($xml2 !== $xml) {
            file_put_contents($part, $xml2);
            $report['fixes'][] = 'Rewrote localhost in ' . basename($part);
        }
    }
}

// IndexNow key file (public)
$indexKey = defined('INDEXNOW_KEY') ? (string)INDEXNOW_KEY : '';
if ($indexKey !== '' && preg_match('/^[a-zA-Z0-9\-]{8,128}$/', $indexKey)) {
    $keyFile = SITE_ROOT . '/' . $indexKey . '.txt';
    if (!is_file($keyFile) || trim((string)file_get_contents($keyFile)) !== $indexKey) {
        file_put_contents($keyFile, $indexKey);
        $report['fixes'][] = 'Wrote IndexNow key file';
    }
}

// Ping search engines with sitemap
$sitemapUrl = $base . '/sitemap.xml';
$pingTargets = [
    'google' => 'https://www.google.com/ping?sitemap=' . rawurlencode($sitemapUrl),
    'bing' => 'https://www.bing.com/ping?sitemap=' . rawurlencode($sitemapUrl),
];
foreach ($pingTargets as $name => $pingUrl) {
    [$st] = $httpGet($pingUrl, 15);
    $report['pings'][] = ['engine' => $name, 'status' => $st, 'url' => $pingUrl];
}

// IndexNow batch for priority URLs (Bing/Yandex; Google may ignore)
if ($indexKey !== '') {
    $host = parse_url($base, PHP_URL_HOST) ?: 'www.icomplypropertyservices.co.uk';
    $urlList = array_map(static fn($p) => $base . $p, array_slice($paths, 0, 12));
    $payload = json_encode([
        'host' => $host,
        'key' => $indexKey,
        'keyLocation' => $base . '/' . $indexKey . '.txt',
        'urlList' => $urlList,
    ], JSON_UNESCAPED_SLASHES);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json; charset=utf-8\r\n",
            'content' => $payload,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents('https://api.indexnow.org/indexnow', false, $ctx);
    $st = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $st = (int)$m[1];
    }
    $report['pings'][] = ['engine' => 'indexnow', 'status' => $st, 'urls' => count($urlList)];
}

// Persist report
$out = SITE_ROOT . '/data/seo-health.json';
@file_put_contents($out, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Catalogue stats for Google readiness
$report['catalogue'] = [
    'services' => count(getServices()),
    'areas' => count(getAreas()),
    'keywords' => count(getMajorKeywords()),
    'service_area_urls' => count(getServices()) * count(getAreas()),
    'keyword_area_urls' => count(getMajorKeywords()) * count(getAreas()),
];

if ($isCli) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit($report['ok'] ? 0 : 2);
}
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
