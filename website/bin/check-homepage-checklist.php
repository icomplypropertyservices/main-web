<?php
/**
 * Homepage launch-checklist assertions (source + optional HTTP).
 * Usage: php bin/check-homepage-checklist.php
 *        php bin/check-homepage-checklist.php http://127.0.0.1:8000
 */
require_once __DIR__ . '/../config.php';

$fail = 0;
$say = static function (bool $ok, string $name, string $detail = '') use (&$fail): void {
    echo ($ok ? 'PASS' : 'FAIL') . ' ' . $name . ($detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
};

$header = (string)file_get_contents(SITE_ROOT . '/includes/header.php');
$index = (string)file_get_contents(SITE_ROOT . '/index.php');
$footer = (string)file_get_contents(SITE_ROOT . '/includes/footer.php');
$redirects = (string)file_get_contents(SITE_ROOT . '/_redirects');

$say(is_file(SITE_ROOT . '/assets/images/services/fire-risk-assessments.jpg'), 'FRA working twin on disk');
$say(is_file(SITE_ROOT . '/assets/images/services/fire-risk-assessments-photo.jpg'), 'FRA photo alias on disk');
$say(
    str_contains($index, 'fire-risk-assessments.jpg') && !str_contains($index, 'fire-risk-assessments-photo'),
    'homepage OG/refs use working FRA twin, not -photo'
);
$say(str_contains($redirects, '/privacy-policy') && str_contains($redirects, '/privacy'), 'privacy-policy 301 in _redirects');
$say(str_contains($redirects, '/terms-and-conditions') && str_contains($redirects, '/terms'), 'terms-and-conditions 301 in _redirects');
$say(str_contains($header, 'function gtag') && str_contains($header, 'googletagmanager.com'), 'gtag snippet in header');
$say(str_contains($header, 'icomply_cookie_consent'), 'analytics cookie-gated');
$say(str_contains($header, 'Icomply Property Services logo'), 'nav logo has meaningful alt');
$say(str_contains($footer, 'cookie-banner') || is_file(SITE_ROOT . '/includes/cookie-banner.php'), 'cookie banner include');
$say(str_contains($footer, 'mobile-sticky-cta'), 'sticky mobile CTA');

$base = $argv[1] ?? '';
if ($base !== '') {
    $base = rtrim($base, '/');
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true, 'follow_location' => 0]]);
    $home = @file_get_contents($base . '/', false, $ctx) ?: '';
    $say($home !== '', 'homepage HTTP body', $home === '' ? 'empty' : (string)strlen($home));
    if ($home !== '') {
        $say(str_contains($home, 'id="cookie-banner"'), 'rendered #cookie-banner');
        $say(str_contains($home, 'function gtag') && str_contains($home, 'googletagmanager.com'), 'rendered gtag snippet');
        $say(str_contains($home, 'SK2 5DE'), 'Stockport SK2 5DE');
        $say(str_contains($home, '07517806082'), 'phone');
        $say(str_contains($home, 'info@icomplypropertyservices.co.uk'), 'email');
        $say(!str_contains($home, 'fire-risk-assessments-photo'), 'rendered HTML has no FRA -photo src');
        preg_match_all('/<img[^>]*>/i', $home, $imgs);
        $empty = 0;
        foreach ($imgs[0] as $im) {
            if (!preg_match('/\balt=("([^"]+)"|\'([^\']+)\')/', $im, $m) || trim($m[2] ?? $m[3] ?? '') === '') {
                $empty++;
            }
        }
        $say($empty === 0, 'no empty alt=""', 'imgs=' . count($imgs[0]) . " empty={$empty}");
    }
    foreach (['/privacy-policy' => '/privacy', '/terms-and-conditions' => '/terms'] as $from => $to) {
        $hdrs = @get_headers($base . $from, true);
        $line = is_array($hdrs) ? (string)($hdrs[0] ?? '') : '';
        $loc = '';
        if (is_array($hdrs)) {
            $locRaw = $hdrs['Location'] ?? $hdrs['location'] ?? '';
            $loc = is_array($locRaw) ? (string)end($locRaw) : (string)$locRaw;
        }
        $say(str_contains($line, '301') && str_contains($loc, $to), "{$from} 301 → {$to}", trim($line . ' ' . $loc));
    }
}

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
