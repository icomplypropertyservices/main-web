<?php
/**
 * Networking, data cabling and mesh Wi-Fi pack (lock 2026-10-20).
 * Hubs are pre-rendered into data/network-pack-hubs.json by
 * bin/build-network-pack.py; town pages are rendered by
 * netlify/lib/network-pack.js. index, follow and self-canonical.
 */
if (!defined('SITE_ROOT')) {
    require_once __DIR__ . '/../config.php';
}

function icomplyNetworkPackHubs(): array
{
    static $hubs = null;
    if ($hubs === null) {
        $raw = @file_get_contents(SITE_ROOT . '/data/network-pack-hubs.json');
        $hubs = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
    }
    return $hubs;
}

function icomplyNetworkPackData(): array
{
    static $data = null;
    if ($data === null) {
        $raw = @file_get_contents(SITE_ROOT . '/data/network-pack.json');
        $data = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
    }
    return $data;
}

/** Town paths the edge renders for the pack (keyword x GM-60, job x GM-60). Service x 269 comes from the matrix. */
function icomplyNetworkPackTownPaths(): array
{
    $d = icomplyNetworkPackData();
    $out = [];
    foreach (array_keys($d['keywords'] ?? []) as $slug) {
        foreach ($d['gm60'] ?? [] as $town) {
            $out[] = '/pages/keywords/' . $slug . '/' . $town;
        }
    }
    foreach (array_keys($d['jobs'] ?? []) as $slug) {
        foreach ($d['gm60'] ?? [] as $town) {
            $out[] = '/pages/jobs/' . $slug . '/' . $town;
        }
    }
    foreach (array_keys($d['services'] ?? []) as $slug) {
        foreach ($d['dual269'] ?? [] as $town) {
            $out[] = '/pages/' . $slug . '/' . $town;
        }
    }
    return $out;
}

/** Links block for area hubs, CCTV / door-entry / intercom hubs and the 7 live cabling hubs. */
function icomplyNetworkPackLinksHtml(string $townSlug = '', string $townName = ''): string
{
    $d = icomplyNetworkPackData();
    if (!$d) {
        return '';
    }
    $items = [];
    foreach ($d['services'] ?? [] as $slug => $svc) {
        $items[] = [url('/pages/services/' . $slug), (string)$svc['name']];
        if ($townSlug !== '' && in_array($townSlug, $d['dual269'] ?? [], true)) {
            $items[] = [url('/pages/' . $slug . '/' . $townSlug), $svc['name'] . ' in ' . $townName];
        }
    }
    foreach (['mesh-wifi-installation', 'wifi-dead-spot-fix', 'data-point-installation', 'poe-installation', 'cctv-on-sky-hub', 'cctv-on-bt-hub', 'door-entry-wifi-connection', 'network-installation'] as $kw) {
        if (isset($d['keywords'][$kw])) {
            $items[] = [url('/pages/keywords/' . $kw), (string)$d['keywords'][$kw]['name']];
        }
    }
    $h = $townName !== '' ? 'Networking, data cabling and Wi-Fi in ' . $townName : 'Networking, data cabling and Wi-Fi';
    $html = '<section class="max-w-7xl mx-auto px-6 py-10 np-reverse-links" data-pack="network-2026-10-20"><h2 class="text-2xl font-bold mb-4">' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</h2>'
        . '<p class="mb-4">Data points, Cat6 and fibre cabling, PoE for cameras and access points, mesh Wi-Fi and CCTV or door entry that works with your Sky or BT hub. Same/Next-Day visits, price on application.</p><ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">';
    foreach ($items as [$href, $label]) {
        $html .= '<li><a class="underline" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    return $html . '</ul></section>';
}

function renderNetworkPackHub(string $key): void
{
    $hub = icomplyNetworkPackHubs()[$key] ?? null;
    if (!is_array($hub)) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }
    $pageTitle = (string)$hub['title'];
    $metaTitleExact = true;
    $metaDesc = (string)$hub['meta'];
    $metaRobots = 'index, follow, max-image-preview:large';
    $canonicalUrl = 'https://icomplypropertyservices.co.uk' . $hub['url'];
    $ogImage = url((string)$hub['og']);
    $ogImageAlt = (string)$hub['name'];
    $metaKeywords = strtolower((string)$hub['name']) . ', networking, data cabling, mesh wifi, Stockport, Greater Manchester';
    require SITE_ROOT . '/includes/header.php';
    $crumbs = [];
    foreach ($hub['crumbs'] as $i => [$href, $label]) {
        $crumbs[] = $i === count($hub['crumbs']) - 1
            ? '<span aria-current="page">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>'
            : '<a class="hover:underline" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    echo '<script type="application/ld+json">' . json_encode($hub['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    echo '<style>.np-hub .np-images{display:grid;grid-template-columns:2fr 1fr 1fr;gap:.5rem;margin:0 0 1rem}.np-hub .np-images img{width:100%;height:100%;max-height:260px;object-fit:cover;border-radius:10px}.np-hub .np-images figcaption{grid-column:1/-1;font-size:.75rem;color:#64748b}.np-hub .np-body p{margin:0 0 1rem;line-height:1.65}.np-hub .np-body h2{font-size:1.4rem;font-weight:800;color:#0B1F3A;margin:2rem 0 .75rem}.np-hub .np-link-list{columns:3;column-gap:1.5rem;margin:0 0 1rem}.np-hub .np-link-list a{text-decoration:underline}.np-hub .faq-item{border:1px solid #e2e8f0;border-radius:8px;padding:.75rem 1rem;margin:.5rem 0}.np-hub .faq-item summary{font-weight:700;cursor:pointer}@media(max-width:640px){.np-hub .np-images{grid-template-columns:1fr 1fr}.np-hub .np-images img:first-child{grid-column:1/-1}.np-hub .np-images img{max-height:200px}.np-hub .np-link-list{columns:1}}</style>';
    echo '<main class="np-hub max-w-5xl mx-auto px-5 py-10 text-slate-800">';
    echo '<nav class="text-sm text-slate-500 mb-4 flex flex-wrap gap-2" aria-label="Breadcrumb">' . implode(' <span>/</span> ', $crumbs) . '</nav>';
    echo '<h1 class="text-3xl md:text-4xl font-extrabold text-[#0B1F3A] mb-6">' . htmlspecialchars((string)$hub['h1'], ENT_QUOTES, 'UTF-8') . '</h1>';
    echo $hub['images'];
    echo '<p class="np-cta my-6"><a class="inline-block px-5 py-3 rounded-lg bg-[#ff6b00] text-white font-bold" href="tel:07517806082">Call 07517 806082</a> <a class="inline-block px-5 py-3 rounded-lg border border-[#0B1F3A] font-bold" href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a></p>';
    echo '<article class="np-body prose max-w-none">' . $hub['html'] . '</article>';
    echo '</main>';
    require SITE_ROOT . '/includes/footer.php';
}
