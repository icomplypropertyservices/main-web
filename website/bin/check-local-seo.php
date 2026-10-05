#!/usr/bin/env php
<?php
/**
 * Local SEO gate for the homepage and service hubs.
 * Checks title length, meta description length, canonical host/path,
 * and LocalBusiness / Service JSON-LD. Non-production: does not deploy.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
putenv('SITE_URL=https://icomplypropertyservices.co.uk');
$_ENV['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_URI'] = '/';

require_once $root . '/config.php';
require_once $root . '/includes/render.php';

$fail = 0;

function localSeoFail(string $message): void
{
    global $fail;
    $fail++;
    fwrite(STDERR, "FAIL {$message}\n");
}

function localSeoCapture(callable $fn): string
{
    ob_start();
    try {
        $fn();
    } catch (Throwable $e) {
        ob_end_clean();
        localSeoFail('exception ' . $e->getMessage());
        return '';
    }
    return (string)ob_get_clean();
}

/** @return list<array<string,mixed>> */
function localSeoJsonLd(string $html): array
{
    $blocks = [];
    if (!preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches)) {
        return [];
    }
    foreach ($matches[1] as $raw) {
        $decoded = json_decode(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'), true);
        if (!is_array($decoded)) {
            localSeoFail('invalid json-ld');
            continue;
        }
        $blocks[] = $decoded;
    }
    return $blocks;
}

/** @param list<array<string,mixed>> $blocks */
function localSeoNodes(array $blocks): array
{
    $nodes = [];
    foreach ($blocks as $block) {
        if (isset($block['@graph']) && is_array($block['@graph'])) {
            foreach ($block['@graph'] as $node) {
                if (is_array($node)) {
                    $nodes[] = $node;
                }
            }
            continue;
        }
        $nodes[] = $block;
    }
    return $nodes;
}

function localSeoTypes(array $node): array
{
    $type = $node['@type'] ?? '';
    if (is_string($type)) {
        return [$type];
    }
    if (is_array($type)) {
        return array_values(array_filter($type, 'is_string'));
    }
    return [];
}

function localSeoCheckPage(string $label, string $html, string $canonical, bool $expectService): void
{
    if ($html === '' || str_contains($html, 'Fatal error') || str_contains($html, 'Parse error')) {
        localSeoFail("{$label} empty or php error");
        return;
    }
    if (!preg_match('/<title>([^<]{15,70})<\/title>/', $html, $title)) {
        $got = '';
        if (preg_match('/<title>([^<]*)<\/title>/', $html, $rawTitle)) {
            $got = $rawTitle[1] . ' (' . strlen($rawTitle[1]) . ' chars)';
        }
        localSeoFail("{$label} title length {$got}");
    } elseif (!str_contains($title[1], 'iComply') && !str_contains($title[1], 'Icomply')) {
        localSeoFail("{$label} title missing brand");
    }
    if (!preg_match('/name="description" content="([^"]{70,165})"/', $html, $desc)) {
        $got = '';
        if (preg_match('/name="description" content="([^"]*)"/', $html, $rawDesc)) {
            $got = strlen($rawDesc[1]) . ' chars';
        }
        localSeoFail("{$label} meta description length {$got}");
    }
    $canonicalQuoted = htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8');
    if (!str_contains($html, 'rel="canonical" href="' . $canonicalQuoted . '"')) {
        localSeoFail("{$label} canonical expected {$canonical}");
    }
    if (!str_contains($html, 'lang="en-GB"')) {
        localSeoFail("{$label} lang");
    }
    $blocks = localSeoJsonLd($html);
    if (!$blocks) {
        localSeoFail("{$label} missing json-ld");
        return;
    }
    $nodes = localSeoNodes($blocks);
    $business = null;
    $businessCount = 0;
    $hasService = false;
    foreach ($nodes as $node) {
        $types = localSeoTypes($node);
        if (in_array('LocalBusiness', $types, true)) {
            $businessCount++;
            $business = $node;
        }
        if (in_array('Service', $types, true)) {
            $hasService = true;
            $provider = $node['provider']['@id'] ?? '';
            if ($provider !== 'https://icomplypropertyservices.co.uk/#business') {
                localSeoFail("{$label} service provider @id {$provider}");
            }
            if (isset($node['offers']['availability'])) {
                localSeoFail("{$label} service offer must not claim stock");
            }
            if (isset($node['offers']['price'])) {
                localSeoFail("{$label} service offer must not invent a price");
            }
        }
    }
    if ($businessCount !== 1) {
        localSeoFail("{$label} expected 1 LocalBusiness, got {$businessCount}");
    }
    if ($expectService && !$hasService) {
        localSeoFail("{$label} missing Service");
    }
    if (is_array($business)) {
        $id = (string)($business['@id'] ?? '');
        if ($id !== 'https://icomplypropertyservices.co.uk/#business') {
            localSeoFail("{$label} business @id {$id}");
        }
        if (($business['telephone'] ?? '') !== '+447517806082') {
            localSeoFail("{$label} telephone " . ($business['telephone'] ?? ''));
        }
        $postcode = $business['address']['postalCode'] ?? '';
        if ($postcode !== 'SK2 5DE') {
            localSeoFail("{$label} postcode {$postcode}");
        }
        if (($business['url'] ?? '') !== 'https://icomplypropertyservices.co.uk/') {
            localSeoFail("{$label} business url " . ($business['url'] ?? ''));
        }
    }
}

$home = localSeoCapture(static function (): void {
    $_SERVER['REQUEST_URI'] = '/';
    include dirname(__DIR__) . '/index.php';
});
localSeoCheckPage('home', $home, 'https://icomplypropertyservices.co.uk/', false);
if (!str_contains($home, '"@type":"WebSite"') && !str_contains($home, '"@type": "WebSite"')) {
    localSeoFail('home missing WebSite');
}

$services = getServices();
$top = array_keys(icomply_top_service_labels());
foreach ($services as $slug => $name) {
    $_SERVER['REQUEST_URI'] = '/pages/services/' . $slug;
    $html = localSeoCapture(static function () use ($slug): void {
        renderServiceHubPage((string)$slug);
    });
    $expectTop = in_array($slug, $top, true);
    localSeoCheckPage(
        'service ' . $slug,
        $html,
        'https://icomplypropertyservices.co.uk/pages/services/' . $slug,
        true
    );
    if ($expectTop && !str_contains($html, 'Stockport')) {
        localSeoFail("service {$slug} missing Stockport in page");
    }
}

$_SERVER['REQUEST_URI'] = '/pages/services';
$indexHtml = localSeoCapture(static function (): void {
    include dirname(__DIR__) . '/pages/services/index.php';
});
localSeoCheckPage(
    'services index',
    $indexHtml,
    'https://icomplypropertyservices.co.uk/pages/services',
    false
);

$_SERVER['REQUEST_URI'] = '/pages/electrical/stockport';
$combo = localSeoCapture(static function (): void {
    renderServiceAreaPage('electrical', 'Stockport');
});
if ($combo === '' || str_contains($combo, 'Fatal error') || str_contains($combo, 'Parse error')) {
    localSeoFail('combo electrical/stockport php error');
} elseif (!str_contains($combo, 'https://icomplypropertyservices.co.uk/#business')) {
    localSeoFail('combo missing shared business id');
}

if ($fail === 0) {
    fwrite(STDOUT, "LOCAL SEO GATE PASSED\n");
    exit(0);
}
fwrite(STDERR, "{$fail} FAILURES\n");
exit(1);
