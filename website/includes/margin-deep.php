<?php
/**
 * Margin DEEP packs (fencing, perimeter detection, electric gates, fire dampers, AHU, BMS, AC).
 *
 * SEO locks: icomply-ops/seo/*-DEEP-LOCK-2026-10-05.md (index: PROPERTY-SEO-LOCK-INDEX-2026-10-05.md).
 * Each pack ships one data file, data/margin-deep/{pack}.json, built by bin/build-margin-deep-pack.py
 * from the copy modules in bin/margin_deep/{pack}/. This engine is pack-agnostic: it reads every
 * data/margin-deep/*.json file it finds, so packs can merge in any order.
 *
 * Hubs are /pages/keywords/{slug} (one canonical URL per slug; a DEEP hub wins over an older thin
 * keyword row with the same slug). Town pages are hub × dual-ring 269 towns (Greater Manchester
 * core 60 plus towns within 50 miles of Manchester or Burnley); manufacturer × job heads are
 * Greater Manchester core 60 only. Town pages are rendered on demand here and by the Netlify edge
 * twin (netlify/lib/margin-deep.js) with the same body HTML. No per-town HTML files.
 *
 * Price on application only. No attendance-time or response-time promises.
 */
declare(strict_types=1);

const ICOMPLY_MARGIN_DEEP_NAP = '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE';
const ICOMPLY_MARGIN_DEEP_PHONE = '07517 806082';
const ICOMPLY_MARGIN_DEEP_WA = 'https://wa.me/447517806082';
const ICOMPLY_MARGIN_DEEP_EMAIL = 'info@icomplypropertyservices.co.uk';
const ICOMPLY_MARGIN_DEEP_BASE = 'https://icomplypropertyservices.co.uk';

/** @return array<string,array<string,mixed>> pack id => decoded pack document */
function icomplyMarginDeepPacks(): array
{
    static $packs = null;
    if ($packs !== null) {
        return $packs;
    }
    $packs = [];
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $files = glob($root . '/data/margin-deep/*.json') ?: [];
    sort($files);
    foreach ($files as $file) {
        $doc = json_decode((string)file_get_contents($file), true);
        if (is_array($doc) && !empty($doc['pack']) && is_array($doc['hubs'] ?? null)) {
            $packs[(string)$doc['pack']] = $doc;
        }
    }
    return $packs;
}

/** @return array<string,array<string,mixed>> slug => hub row (with 'pack') */
function icomplyMarginDeepHubs(): array
{
    static $by = null;
    if ($by !== null) {
        return $by;
    }
    $by = [];
    foreach (icomplyMarginDeepPacks() as $id => $doc) {
        foreach ((array)$doc['hubs'] as $hub) {
            if (is_array($hub) && !empty($hub['slug']) && !isset($by[(string)$hub['slug']])) {
                $hub['pack'] = $id;
                $by[(string)$hub['slug']] = $hub;
            }
        }
    }
    return $by;
}

/** @return array<string,mixed> copy row for a slug (empty when not a DEEP hub) */
function icomplyMarginDeepCopy(string $slug): array
{
    $hub = icomplyMarginDeepHubs()[icomplyMarginDeepNormalise($slug)] ?? null;
    if (!is_array($hub)) {
        return [];
    }
    $row = icomplyMarginDeepPacks()[(string)$hub['pack']]['keywords'][(string)$hub['slug']] ?? null;
    return is_array($row) ? $row : [];
}

/** @return array<string,mixed> */
function icomplyMarginDeepPackFor(string $slug): array
{
    $hub = icomplyMarginDeepHubs()[icomplyMarginDeepNormalise($slug)] ?? null;
    return is_array($hub) ? (icomplyMarginDeepPacks()[(string)$hub['pack']] ?? []) : [];
}

/** @return list<string> */
function icomplyMarginDeepSlugs(?string $pack = null): array
{
    $out = [];
    foreach (icomplyMarginDeepHubs() as $slug => $hub) {
        if ($pack === null || $hub['pack'] === $pack) {
            $out[] = (string)$slug;
        }
    }
    return $out;
}

function icomplyMarginDeepNormalise(string $slug): string
{
    return function_exists('keywordSlug') ? keywordSlug($slug) : strtolower(trim($slug));
}

function icomplyMarginDeepIsP0(string $slug): bool
{
    return isset(icomplyMarginDeepHubs()[icomplyMarginDeepNormalise($slug)]);
}

/** Manufacturer × job heads keep town pages on the Greater Manchester core 60 only. */
function icomplyMarginDeepGmOnly(string $slug): bool
{
    $hub = icomplyMarginDeepHubs()[icomplyMarginDeepNormalise($slug)] ?? null;
    return is_array($hub) && ($hub['geo'] ?? '') === 'gm60';
}

/** @return list<string> */
function icomplyMarginDeepImages(string $slug): array
{
    $hub = icomplyMarginDeepHubs()[icomplyMarginDeepNormalise($slug)] ?? null;
    $out = [];
    foreach ((array)($hub['images'] ?? []) as $image) {
        if (is_string($image) && $image !== '') {
            $out[] = $image;
        }
    }
    return array_slice($out, 0, 3);
}

/** @return array<string,mixed>|null dual-ring allowlist row (slug, name, bucket, mi_manchester, mi_burnley) */
function icomplyMarginDeepTownRow(string $townSlug): ?array
{
    static $rows = null;
    if ($rows === null) {
        $rows = [];
        $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
        $doc = json_decode((string)@file_get_contents($root . '/data/dual-ring-allowlist.json'), true);
        foreach ((array)($doc['towns'] ?? []) as $row) {
            if (is_array($row) && !empty($row['slug'])) {
                $rows[strtolower((string)$row['slug'])] = $row;
            }
        }
    }
    $townSlug = function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug);
    return $rows[$townSlug] ?? null;
}

function icomplyMarginDeepGmTown(string $townSlug): bool
{
    $row = icomplyMarginDeepTownRow($townSlug);
    if ($row !== null && ($row['bucket'] ?? '') === 'gm_core') {
        return true;
    }
    return function_exists('icomplyCrawlTownSlug') && icomplyCrawlTownSlug($townSlug);
}

/** True when {keyword}/{town} is a DEEP town page that renders 200 and is indexable. */
function icomplyMarginDeepKeepsTown(string $keywordSlug, string $townSlug): bool
{
    if (!icomplyMarginDeepIsP0($keywordSlug)) {
        return false;
    }
    if (icomplyMarginDeepGmOnly($keywordSlug)) {
        return icomplyMarginDeepGmTown($townSlug);
    }
    return icomplyMarginDeepTownRow($townSlug) !== null;
}

/** @return list<string> town names linked from a DEEP hub (269 dual ring, or GM core 60). */
function icomplyMarginDeepTownNames(string $keywordSlug): array
{
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $doc = json_decode((string)@file_get_contents($root . '/data/dual-ring-allowlist.json'), true);
    $gmOnly = icomplyMarginDeepGmOnly($keywordSlug);
    $names = [];
    foreach ((array)($doc['towns'] ?? []) as $row) {
        if (!is_array($row) || empty($row['name'])) {
            continue;
        }
        if ($gmOnly && ($row['bucket'] ?? '') !== 'gm_core') {
            continue;
        }
        $names[] = (string)$row['name'];
    }
    return $names;
}

/**
 * Keyword catalogue overlay: DEEP copy wins for its slugs (one canonical URL per slug).
 *
 * @param array<string,mixed> $keywords
 * @return array<string,mixed>
 */
function icomplyMarginDeepApplyKeywords(array $keywords): array
{
    foreach (icomplyMarginDeepHubs() as $slug => $hub) {
        $meta = icomplyMarginDeepCopy((string)$slug);
        if ($meta === []) {
            continue;
        }
        $row = [
            'name' => (string)($meta['name'] ?? $slug),
            'service' => (string)($meta['service'] ?? 'building-maintenance'),
            'related' => icomplyMarginDeepNormalise((string)($meta['related'] ?? $slug)),
        ];
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords', 'seo_title', 'h1'] as $field) {
            if (!empty($meta[$field]) && is_string($meta[$field])) {
                $row[$field] = $meta[$field];
            }
        }
        foreach (['focus_points', 'faq'] as $field) {
            if (!empty($meta[$field]) && is_array($meta[$field])) {
                $row[$field] = $meta[$field];
            }
        }
        $keywords[(string)$slug] = $row;
    }
    return $keywords;
}

/**
 * 301 map for older URLs that a DEEP keyword hub supersedes (job hub → keyword hub,
 * job × town → keyword × town where that town is kept).
 */
function icomplyMarginDeepRedirect(string $path): ?string
{
    $path = '/' . ltrim($path, '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    foreach (icomplyMarginDeepPacks() as $doc) {
        $map = (array)($doc['redirects'] ?? []);
        if (isset($map[$path])) {
            return (string)$map[$path];
        }
        if (preg_match('#^/pages/jobs/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m) && isset($map['/pages/jobs/' . $m[1]])) {
            $target = (string)$map['/pages/jobs/' . $m[1]];
            $kw = basename($target);
            return icomplyMarginDeepKeepsTown($kw, $m[2]) ? $target . '/' . $m[2] : $target;
        }
    }
    return null;
}

/** @return list<string> old job slugs that a DEEP hub supersedes (dropped from the job sitemap). */
function icomplyMarginDeepSupersededJobs(): array
{
    $out = [];
    foreach (icomplyMarginDeepPacks() as $doc) {
        foreach (array_keys((array)($doc['redirects'] ?? [])) as $from) {
            if (preg_match('#^/pages/jobs/([a-z0-9\-]+)$#', (string)$from, $m)) {
                $out[] = $m[1];
            }
        }
    }
    return $out;
}

/** Hub content-image figure (same PE slots as icomplyQualityBarImages) using the pack photographs. */
function icomplyMarginDeepImageFigure(string $slug, string $altPrefix): string
{
    $h = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $slots = ['q2-image-hero' => '', 'q2-image-work' => ' — on site', 'q2-image-context' => ' — site context'];
    $images = icomplyMarginDeepImages($slug);
    $html = '<figure class="quality-bar-images" data-pe-slot="q2-hub-images">';
    $i = 0;
    foreach ($slots as $slot => $suffix) {
        $src = (string)($images[$i++] ?? '');
        if ($src === '') {
            continue;
        }
        $html .= '<img data-pe-slot="' . $slot . '" src="' . $h(function_exists('url') ? url($src) : $src) . '" alt="'
            . $h($altPrefix . $suffix) . '" width="1200" height="800" loading="lazy">';
    }
    return $html . '</figure>';
}

/** Contact paragraph used on hubs and town pages. */
function icomplyMarginDeepContactText(): string
{
    return 'Workshop: ' . ICOMPLY_MARGIN_DEEP_NAP . '. Phone or WhatsApp ' . ICOMPLY_MARGIN_DEEP_PHONE
        . ' (' . ICOMPLY_MARGIN_DEEP_WA . '), or email ' . ICOMPLY_MARGIN_DEEP_EMAIL . '. Every quote is price on application.';
}

function icomplyMarginDeepTitle(string $name, ?string $town = null): string
{
    $lead = $town ? ($name . ' in ' . $town) : $name;
    $full = $lead . ' | iComply Property Services';
    if (strlen($full) <= 60) {
        return $full;
    }
    return strlen($lead . ' | iComply') <= 70 ? $lead . ' | iComply' : $lead;
}

function icomplyMarginDeepFitMeta(string $text): string
{
    $text = trim((string)preg_replace('/\s+/', ' ', $text));
    if (strlen($text) > 160) {
        $words = explode(' ', $text);
        while (count($words) > 1 && strlen(implode(' ', $words)) > 158) {
            array_pop($words);
        }
        $text = (string)preg_replace('/[ .,;:—-]+$/u', '', implode(' ', $words)) . '.';
    }
    foreach ([' Quote POA.', ' Survey first.', ' Written scope.', ' Stockport base.', ' POA.'] as $pad) {
        if (strlen($text) >= 140) {
            break;
        }
        if (strlen($text . $pad) <= 160 && !str_contains($text, trim($pad))) {
            $text .= $pad;
        }
    }
    return $text;
}

function icomplyMarginDeepTownMeta(string $name, string $place, string $who): string
{
    $text = $name . ' in ' . $place . ' for ' . $who . '. Surveyed and scoped from Stockport. Request a quote — POA.';
    if (strlen($text) > 160) {
        $text = $name . ' in ' . $place . '. Surveyed and scoped from Stockport. Request a quote — POA.';
    }
    if (strlen($text) > 160) {
        $text = $name . ' in ' . $place . '. Scoped from Stockport. Quote POA.';
    }
    return icomplyMarginDeepFitMeta($text);
}

/** 32-bit FNV-1a (matches hashStr() in the edge twin). */
function icomplyMarginDeepHash(string $value): int
{
    $h = 2166136261;
    $len = strlen($value);
    for ($i = 0; $i < $len; $i++) {
        $h ^= ord($value[$i]);
        $h = ($h * 16777619) & 0xFFFFFFFF;
    }
    return $h;
}

/** Local paragraph for a DEEP town page (same wording as the edge twin). */
function icomplyMarginDeepLocal(string $keywordSlug, string $name, array $town, array $pack): string
{
    $place = (string)$town['name'];
    $mcr = (string)($town['mi_manchester'] ?? '');
    $bly = (string)($town['mi_burnley'] ?? '');
    if (($town['bucket'] ?? '') === 'gm_core') {
        $where = $place . ' is in the Greater Manchester core of our service area, about ' . $mcr . ' miles from Manchester city centre on the town list this page uses.';
    } else {
        $where = $place . ' is on our Manchester and Burnley service ring, about ' . $mcr . ' miles from Manchester and ' . $bly . ' miles from Burnley on the town list this page uses.';
    }
    $angles = (array)($pack['town_angles'] ?? []);
    $angle = $angles !== [] ? (string)$angles[icomplyMarginDeepHash($keywordSlug . '|' . (string)$town['slug']) % count($angles)] : '';
    $angle = str_replace(['{place}', '{name}'], [$place, $name], $angle);
    return trim($where . ' ' . $name . ' in ' . $place . ' is quoted from ' . ICOMPLY_MARGIN_DEEP_NAP . '. ' . $angle
        . ' The quote is price on application once the scope is written down, and travel from the Stockport workshop is part of that quote. No attendance time is promised on this page.');
}

/**
 * Sibling DEEP hubs linked from a town page: the related hub first, then the same pack in lock
 * order starting after this slug (stable and page-specific; the edge twin matches).
 *
 * @return list<string>
 */
function icomplyMarginDeepSiblings(string $keywordSlug, string $related = '', int $limit = 18): array
{
    $hubs = icomplyMarginDeepHubs();
    $pack = (string)($hubs[$keywordSlug]['pack'] ?? '');
    $same = icomplyMarginDeepSlugs($pack);
    $at = array_search($keywordSlug, $same, true);
    $at = $at === false ? 0 : (int)$at;
    $ordered = array_merge(array_slice($same, $at + 1), array_slice($same, 0, $at));
    if ($related !== '' && $related !== $keywordSlug && isset($hubs[$related])) {
        $ordered = array_merge([$related], array_values(array_diff($ordered, [$related])));
    }
    return array_slice($ordered, 0, $limit);
}

/** @return array{html:string,title:string,description:string}|null */
function icomplyMarginDeepTownPage(string $keywordSlug, string $townSlug): ?array
{
    $keywordSlug = icomplyMarginDeepNormalise($keywordSlug);
    $townSlug = function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug);
    if (!icomplyMarginDeepKeepsTown($keywordSlug, $townSlug)) {
        return null;
    }
    $copy = icomplyMarginDeepCopy($keywordSlug);
    $pack = icomplyMarginDeepPackFor($keywordSlug);
    $town = icomplyMarginDeepTownRow($townSlug);
    if ($copy === [] || $town === null) {
        return null;
    }
    $h = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $name = (string)$copy['name'];
    $place = (string)$town['name'];
    $title = icomplyMarginDeepTitle($name, $place);
    $description = icomplyMarginDeepTownMeta($name, $place, (string)($pack['town_who'] ?? 'property owners and managers'));
    $canonical = ICOMPLY_MARGIN_DEEP_BASE . '/pages/keywords/' . $keywordSlug . '/' . $townSlug;
    $images = icomplyMarginDeepImages($keywordSlug);
    $ogImage = ICOMPLY_MARGIN_DEEP_BASE . ($images[0] ?? '/assets/images/services/building-maintenance.jpg');

    $paras = preg_split("/\R\R+/", trim((string)($copy['body'] ?? ''))) ?: [];
    array_unshift($paras, icomplyMarginDeepLocal($keywordSlug, $name, $town, $pack), (string)($copy['intro'] ?? ''));
    $bodyHtml = '';
    foreach ($paras as $para) {
        $para = trim((string)$para);
        if ($para !== '') {
            $bodyHtml .= '<p>' . $h($para) . '</p>';
        }
    }
    $focusHtml = '';
    foreach ((array)($copy['focus_points'] ?? []) as $point) {
        $focusHtml .= '<li>' . $h((string)$point) . '</li>';
    }
    $faqs = (array)($copy['faq'] ?? []);
    $faqs[] = [
        'Do you cover ' . $place . ' for ' . $name . '?',
        'Yes. ' . $place . ' is on the published town list and is quoted from the Stockport workshop. Phone or WhatsApp ' . ICOMPLY_MARGIN_DEEP_PHONE . ', or email ' . ICOMPLY_MARGIN_DEEP_EMAIL . ', and name the site in ' . $place . '. The reply is price on application.',
    ];
    $faqHtml = '';
    $faqSchema = [];
    foreach ($faqs as $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $faqHtml .= '<h3>' . $h((string)$faq[0]) . '</h3><p>' . $h((string)$faq[1]) . '</p>';
        $faqSchema[] = ['@type' => 'Question', 'name' => (string)$faq[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string)$faq[1]]];
    }
    $links = '';
    foreach ((array)($pack['parents'] ?? []) as $parent) {
        if (is_array($parent) && count($parent) >= 2) {
            $links .= '<li><a href="' . $h((string)$parent[0]) . '">' . $h((string)$parent[1]) . '</a></li>';
        }
    }
    $links .= '<li><a href="' . $h('/pages/keywords/' . $keywordSlug) . '">' . $h($name . ' guide') . '</a></li>';
    $links .= '<li><a href="/contact">Request a quote</a></li>';
    if (icomplyMarginDeepGmTown($townSlug)) {
        $links .= '<li><a href="' . $h('/pages/areas/' . $townSlug) . '">' . $h('Property services in ' . $place) . '</a></li>';
    } else {
        $links .= '<li><a href="/pages/areas/stockport">Stockport area hub</a></li>';
    }
    $related = (string)($copy['related'] ?? '');
    foreach (icomplyMarginDeepSiblings($keywordSlug, $related) as $other) {
        $otherTown = icomplyMarginDeepKeepsTown($other, $townSlug);
        $href = '/pages/keywords/' . $other . ($otherTown ? '/' . $townSlug : '');
        $label = (string)(icomplyMarginDeepCopy($other)['name'] ?? $other) . ($otherTown ? ' in ' . $place : '');
        $links .= '<li><a href="' . $h($href) . '">' . $h($label) . '</a></li>';
    }
    $gallery = '';
    foreach ($images as $i => $src) {
        $gallery .= '<figure><img src="' . $h($src) . '" alt="' . $h($name . ' in ' . $place . ' — photograph ' . ($i + 1)) . '" width="1200" height="800" loading="' . ($i === 0 ? 'eager' : 'lazy') . '"></figure>';
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                'name' => $name . ' in ' . $place,
                'description' => $description,
                'areaServed' => $place,
                'url' => $canonical,
                'provider' => ['@type' => 'LocalBusiness', 'name' => 'iComply Property Services', 'telephone' => ICOMPLY_MARGIN_DEEP_PHONE, 'email' => ICOMPLY_MARGIN_DEEP_EMAIL, 'address' => ICOMPLY_MARGIN_DEEP_NAP],
            ],
            ['@type' => 'FAQPage', 'mainEntity' => $faqSchema],
        ],
    ];
    $contact = '<section id="contact"><h2>Contact</h2><p>Phone <a href="tel:07517806082">' . $h(ICOMPLY_MARGIN_DEEP_PHONE) . '</a> · WhatsApp <a href="'
        . $h(ICOMPLY_MARGIN_DEEP_WA) . '">' . $h(ICOMPLY_MARGIN_DEEP_PHONE) . '</a> · Email <a href="mailto:' . $h(ICOMPLY_MARGIN_DEEP_EMAIL) . '">'
        . $h(ICOMPLY_MARGIN_DEEP_EMAIL) . '</a></p><p>Workshop: ' . $h(ICOMPLY_MARGIN_DEEP_NAP) . '. Price on application.</p></section>';
    $html = '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . $h($title) . '</title>'
        . '<meta name="description" content="' . $h($description) . '">'
        . '<meta name="robots" content="index, follow">'
        . '<link rel="canonical" href="' . $h($canonical) . '">'
        . '<meta property="og:type" content="website">'
        . '<meta property="og:locale" content="en_GB">'
        . '<meta property="og:site_name" content="iComply Property Services">'
        . '<meta property="og:title" content="' . $h($title) . '">'
        . '<meta property="og:description" content="' . $h($description) . '">'
        . '<meta property="og:url" content="' . $h($canonical) . '">'
        . '<meta property="og:image" content="' . $h($ogImage) . '">'
        . '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
        . '</head><body><main>'
        . '<nav><a href="/">Home</a> / <a href="' . $h('/pages/keywords/' . $keywordSlug) . '">' . $h($name) . '</a> / <span>' . $h($place) . '</span></nav>'
        . '<h1>' . $h($name . ' in ' . $place) . '</h1>'
        . $gallery
        . '<article id="local-copy">' . $bodyHtml . '<ul>' . $focusHtml . '</ul></article>'
        . '<section><h2>' . $h($name . ' FAQ') . '</h2>' . $faqHtml . '</section>'
        . '<section><h2>Related guides</h2><ul>' . $links . '</ul></section>'
        . $contact
        . '</main></body></html>';
    return ['html' => $html, 'title' => $title, 'description' => $description];
}

function icomplyMarginDeepRenderTown(string $keywordSlug, string $townSlug): void
{
    $page = icomplyMarginDeepTownPage($keywordSlug, $townSlug);
    if ($page === null) {
        if (!headers_sent()) {
            http_response_code(404);
        }
        echo 'Keyword town page not found';
        return;
    }
    echo $page['html'];
}
