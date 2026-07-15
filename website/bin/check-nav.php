<?php
/**
 * Verify key navigation destinations return HTTP 200 (or 301/302 chain to 200).
 * Usage: php bin/check-nav.php
 */
require_once __DIR__ . '/../config.php';

$paths = [
    '/',
    '/contact.php',
    '/privacy.php',
    '/terms.php',
    '/shop/index.php',
    '/pages/about.php',
    '/pages/faq.php',
    '/pages/landlords.php',
    '/pages/commercial.php',
    '/pages/packages.php',
    '/pages/pricing.php',
    '/pages/care-homes.php',
    '/pages/ev-chargers.php',
    '/pages/maintenance.php',
    '/pages/emergency.php',
    '/pages/reviews.php',
    '/pages/site-map.php',
    '/pages/services/index.php',
    '/pages/areas/index.php',
    '/pages/manufacturers/index.php',
    '/pages/keywords/index.php',
    '/pages/resources/index.php',
    '/pages/resources/eicr-guide.php',
    '/pages/services/fire-alarms.php',
    '/pages/services/electrical.php',
    '/pages/areas/stockport.php',
    '/pages/areas/manchester.php',
    '/pages/manufacturers/kentec.php',
    '/pages/keywords/eicr.php',
    '/pages/keywords/eicr-report.php',
    '/pages/keywords/landlord-gas-safety-certificate.php',
    '/pages/keywords/security-lighting-installation.php',
    '/pages/fire-alarms/stockport.php',
    '/sitemap.xml',
];

// Add every service hub
foreach (array_keys(getServices()) as $slug) {
    $paths[] = '/pages/services/' . $slug . '.php';
}

$paths = array_values(array_unique($paths));
$base = rtrim(SITE_URL, '/');
$ok = 0;
$bad = 0;
$failures = [];

foreach ($paths as $path) {
    $url = $base . $path;
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'follow_location' => 1,
            'max_redirects' => 5,
            'ignore_errors' => true,
            'header' => "User-Agent: Icomply-Nav-Check/1.0\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $status = (int)$m[1];
    }
    $err = is_string($body) && preg_match('/Fatal error|Parse error|Template missing|not found/i', $body);
    if ($status >= 200 && $status < 400 && !$err) {
        $ok++;
        echo "OK  {$status}  {$path}\n";
    } else {
        $bad++;
        $failures[] = "{$status} {$path}";
        echo "BAD {$status}  {$path}\n";
    }
}

echo "\n---\nOK={$ok} BAD={$bad} TOTAL=" . count($paths) . "\n";
if ($failures) {
    echo "Failures:\n  " . implode("\n  ", $failures) . "\n";
    exit(1);
}
exit(0);
