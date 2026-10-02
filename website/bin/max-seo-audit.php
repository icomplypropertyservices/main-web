#!/usr/bin/env php
<?php
/**
 * Strict SEO quality gate — exit 1 if material issues remain.
 */
$base = 'http://localhost:8000';
$samples = [
    '/',
    '/contact.php',
    '/pages/services/index.php',
    '/pages/services/electrical.php',
    '/pages/services/fire-alarms.php',
    '/pages/electrical/manchester.php',
    '/pages/electrical/stockport.php',
    '/pages/fire-alarms/liverpool.php',
    '/pages/cctv/preston.php',
    '/pages/gas-systems/blackpool.php',
    '/pages/areas/manchester.php',
    '/pages/areas/wilmslow.php',
    '/pages/door-entry/chester.php',
    '/pages/nurse-call/bolton.php',
    '/pages/aov-air-handling/warrington.php',
];

function fetch(string $url): string {
    $ctx = stream_context_create(['http' => ['timeout' => 12, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? '' : $body;
}

$fail = 0;
foreach ($samples as $path) {
    $body = fetch($base . $path);
    $issues = [];
    if ($body === '') $issues[] = 'empty';
    if (preg_match('/Fatal error|Parse error|Warning:/', $body)) $issues[] = 'php_error';
    if (!preg_match('/<title>([^<]{15,70})<\/title>/i', $body, $tm)) $issues[] = 'title_len';
    if (!preg_match('/name="description" content="([^"]{70,165})"/i', $body)) $issues[] = 'meta_len';
    if (!preg_match('/rel="canonical"/i', $body)) $issues[] = 'canonical';
    if (!preg_match('/<h1[\s>]/i', $body)) $issues[] = 'h1';
    if (!preg_match('/application\/ld\+json/i', $body)) $issues[] = 'schema';
    if (!preg_match('/LocalBusiness|Organization/i', $body)) $issues[] = 'org_schema';
    if (!preg_match('/assets\/images\//i', $body)) $issues[] = 'images';
    if (strpos($path, '/pages/') === 0 && strpos($path, '/services/') === false && strpos($path, '/areas/') === false) {
        // combo pages should have unique local signals
        if (!preg_match('/postcode|districts|Stockport|FAQPage|HowTo/i', $body)) $issues[] = 'local_depth';
    }
    if ($issues) {
        echo "FAIL {$path} :: " . implode(', ', $issues) . "\n";
        $fail++;
    } else {
        echo "OK   {$path}\n";
    }
}

// Uniqueness smoke: two cities must not share a town-name-swap of #local-intro
$a = fetch($base . '/pages/electrical/manchester.php');
$b = fetch($base . '/pages/electrical/blackpool.php');
$grab = static function (string $html): string {
    if (!preg_match('/id="local-intro"[^>]*>(.*?)<\/p>/is', $html, $m)) {
        return '';
    }
    $text = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    return str_ireplace(['Manchester', 'Blackpool'], '{AREA}', $text);
};
$ia = $grab($a);
$ib = $grab($b);
if ($ia === '' || $ib === '' || $ia === $ib) {
    echo "FAIL uniqueness electrical manchester vs blackpool intros\n";
    $fail++;
} else {
    echo "OK   uniqueness electrical intros differ\n";
}

echo $fail === 0 ? "\nMAX SEO GATE PASSED\n" : "\n{$fail} FAILURES\n";
exit($fail > 0 ? 1 : 0);
