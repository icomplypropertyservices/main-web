<?php
/**
 * Batch 1 (KEYWORD-JOBTYPE-SEO-PLAN-2026-10-21): hub-side pieces for Manchester building work.
 * Hero call + WhatsApp under the H1, same-family photo, and links to every GM-60 town page.
 */
function icomplyB1Data(): array {
    static $d = null;
    if ($d === null) {
        $d = json_decode((string) @file_get_contents(SITE_ROOT . '/data/building-batch1.json'), true) ?: [];
        $d['photos'] = json_decode((string) @file_get_contents(SITE_ROOT . '/data/building-batch1-photos.json'), true) ?: [];
    }
    return $d;
}
function icomplyB1IsKeyword(string $slug): bool { $d = icomplyB1Data(); return isset($d['keywords'][$slug]); }
function icomplyB1IsService(string $slug): bool { $d = icomplyB1Data(); return isset($d['services'][$slug]); }
function icomplyB1Family(string $kind, string $slug): string {
    $d = icomplyB1Data();
    return $kind === 'keyword' ? (string) ($d['keyword_family'][$slug] ?? '') : $slug;
}
/** First same-family photo, or '' when the family has none yet (photo gap). */
function icomplyB1Photo(string $kind, string $slug): string {
    $d = icomplyB1Data();
    $set = $d['photos'][icomplyB1Family($kind, $slug)] ?? [];
    return $set ? (string) $set[0]['src'] : '';
}
function icomplyB1HeroCta(string $label): string {
    return '<div class="mt-4 flex flex-wrap gap-3" data-b1="hero-cta">'
        . '<a href="tel:07517806082" class="px-6 py-4 rounded-2xl font-bold text-white shadow-lg" style="background:#FF6B00">Call 07517 806082</a>'
        . '<a href="https://wa.me/447517806082?text=' . rawurlencode($label . ' quote') . '" target="_blank" rel="noopener" class="px-6 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-bold text-white shadow-lg">WhatsApp us</a>'
        . '</div>';
}
function icomplyB1TownLinks(string $kind, string $slug): string {
    $d = icomplyB1Data();
    $label = $kind === 'keyword' ? ($d['keywords'][$slug] ?? '') : ($d['services'][$slug] ?? '');
    if ($label === '') return '';
    $base = $kind === 'keyword' ? '/pages/keywords/' . $slug . '/' : '/pages/' . $slug . '/';
    $towns = $d['gm60'] ?? [];
    usort($towns, fn($a, $b) => ($a[0] === 'manchester' ? -1 : ($b[0] === 'manchester' ? 1 : strcmp($a[1], $b[1]))));
    $h = '<section class="bg-white border-t border-zinc-200" data-b1="gm60-links"><div class="max-w-7xl mx-auto px-6 py-12">'
        . '<h2 class="text-2xl md:text-3xl font-bold text-[#061828]">' . htmlspecialchars($label . ' in Manchester and Greater Manchester', ENT_QUOTES, 'UTF-8') . '</h2>'
        . '<p class="mt-2 text-zinc-800">Pick your town for local details, nearby areas and a free quote. Same/next-day visits from our Stockport team.</p>'
        . '<ul class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-2">';
    foreach ($towns as [$t, $n]) {
        $h .= '<li><a class="text-[#061828] font-semibold hover:text-[#ff6b00] underline-offset-2 hover:underline" href="' . htmlspecialchars(url($base . $t), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label . ' in ' . $n, ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    return $h . '</ul></div></section>';
}
/** Homepage block: Manchester trade pages for every Batch 1 building service and the top keywords. */
function icomplyB1HomeBlock(): string {
    $d = icomplyB1Data();
    $h = '<section class="bg-white border-t border-zinc-200" data-b1="home-manchester-trades"><div class="max-w-7xl mx-auto px-6 py-12">'
        . '<h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Building work in Manchester</h2>'
        . '<p class="mt-2 text-zinc-800">Homeowners and landlords: same/next-day visits and a free written quote. Call <a class="font-semibold text-[#ff6b00]" href="tel:07517806082">07517 806082</a>.</p>'
        . '<ul class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-2">';
    foreach (($d['services'] ?? []) as $s => $label) {
        $h .= '<li><a class="text-[#061828] font-semibold hover:text-[#ff6b00]" href="' . htmlspecialchars(url('/pages/' . $s . '/manchester'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label . ' in Manchester', ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    foreach (['plasterboard-installation', 'loft-conversion', 'house-renovation', 'damp-proofing', 'brickwork-repairs', 'emergency-plumber', 'boiler-repair', 'boiler-installation'] as $k) {
        if (!isset($d['keywords'][$k])) continue;
        $h .= '<li><a class="text-[#061828] font-semibold hover:text-[#ff6b00]" href="' . htmlspecialchars(url('/pages/keywords/' . $k . '/manchester'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($d['keywords'][$k] . ' in Manchester', ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    return $h . '</ul></div></section>';
}

require_once __DIR__ . '/photo-credits.php';
/** Visible credit line under a stock/Commons photo, or '' for our own photos. */
function icomplyB1Credit(string $src, string $class = 'mt-1 px-3 pb-2 text-xs text-zinc-600'): string {
    $path = (string) (parse_url($src, PHP_URL_PATH) ?? $src);
    $c = icomplyPhotoCredits()[$path] ?? null;
    if (!$c) return '';
    $h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    return '<p class="b1-credit ' . $h($class) . '" data-b1="photo-credit">Example photo, not an iComply job. Photo: ' . $h($c['artist']) . ', ' . $h($c['license'])
        . ', via <a class="underline" href="' . $h($c['source']) . '" rel="nofollow noopener" target="_blank">Wikimedia Commons</a>. <a class="underline" href="/pages/photo-credits">All photo credits</a></p>';
}

/** Spec §4 hub title (no town): ≤60 chars. */
function icomplyB1HubTitle(string $label): string {
    foreach ([$label . ' | Same/Next-Day | iComply', $label . ' | Same/Next-Day', $label . ' | iComply'] as $t) {
        if (mb_strlen($t) <= 60) return $t;
    }
    return mb_substr($label, 0, 60);
}
/** Spec §4 hub meta (no town): 140–155 chars; optional plain phrases fill short labels. */
function icomplyB1HubMeta(string $label, bool $gas): string {
    $fill = $gas
        ? [' for landlords and homes', ' with same/next-day slots', ' from Stockport', ' (CP12 records)']
        : [' Homes, landlords and businesses.', ' Written quote first.', ' Travel included.'];
    $build = fn(string $f) => $gas
        ? $label . ' across Greater Manchester' . $f . ', carried out by Gas Safe registered engineers. Call 07517 806082.'
        : $label . ' across Greater Manchester.' . $f . ' Same/next-day visits from our Stockport team. Free quote, call 07517 806082.';
    $best = $build('');
    for ($mask = 0; $mask < (1 << count($fill)); $mask++) {
        $f = '';
        foreach ($fill as $k => $p) { if ($mask & (1 << $k)) $f .= $p; }
        $m = $build($f); $n = mb_strlen($m);
        if ($n >= 140 && $n <= 155) return $m;
    }
    return $best;
}
