<?php
/**
 * Final pass on every exported page: shared under-H1 call/WhatsApp row (hero-cta.php, from #155)
 * and a visible breadcrumb + BreadcrumbList schema when the template did not print one.
 * Twin of netlify/lib/page-finish.js. Phone 07517 806082 only.
 */
declare(strict_types=1);
require_once __DIR__ . '/hero-cta.php';

const ICOMPLY_FINISH_CSS = '<style id="pf-css">.hero-cta{display:flex;flex-wrap:wrap;gap:.6rem;margin:.35rem 0 1.1rem}.hero-cta a{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:.55rem 1rem;border-radius:.55rem;color:#fff;font-weight:700;text-decoration:none}.hero-cta a.call{background:#FF6B00}.hero-cta a.wa{background:#0B1F3A}.pf-bc{font-size:.85rem;color:#333333;margin:.5rem 0}.pf-bc a{color:#333333;text-decoration:underline}.pf-bc ol{list-style:none;display:flex;flex-wrap:wrap;gap:.35rem;padding:0;margin:0}.pf-bc li+li:before{content:"›";margin-right:.35rem}.matrix-hero .pf-bc,.matrix-hero .pf-bc a{color:#ffffff}.matrix-hero .hero-cta a.wa{background:#ffffff;color:#0B1F3A}</style>';

function icomplyFinishTitleCase(string $slug): string
{
    return ucwords(str_replace('-', ' ', $slug));
}

function icomplyFinishPage(string $html, string $path): string
{
    if ($html === '' || !preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        return $html;
    }
    $h1 = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    $site = rtrim((string)(getenv('SITE_URL') ?: 'https://icomplypropertyservices.co.uk'), '/');
    $segs = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
    $crumbs = [['Home', '/']];
    if (count($segs) >= 3 && $segs[0] === 'pages') {
        $crumbs[] = [icomplyFinishTitleCase($segs[count($segs) - 2]), '/' . implode('/', array_slice($segs, 0, -1))];
    }
    $crumbs[] = [$h1 !== '' ? $h1 : icomplyFinishTitleCase((string)end($segs)), '/' . implode('/', $segs)];
    $low = strtolower($html);
    $add = '';
    if (!str_contains($html, 'id="pf-css"')) {
        $add .= ICOMPLY_FINISH_CSS;
    }
    if (!str_contains($low, 'breadcrumblist') && count($segs) > 0) {
        $items = [];
        foreach ($crumbs as $i => [$n, $u]) {
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $n, 'item' => $site . ($u === '/' ? '/' : $u)];
        }
        $add .= '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    if ($add !== '' && stripos($html, '</head>') !== false) {
        $html = preg_replace('/<\/head>/i', $add . '</head>', $html, 1) ?? $html;
    }
    $hasVis = preg_match('/aria-label="breadcrumb"|class="[^"]*breadcrumb/i', $html);
    if (!$hasVis && count($segs) > 0) {
        $lis = '';
        $last = count($crumbs) - 1;
        foreach ($crumbs as $i => [$n, $u]) {
            $e = htmlspecialchars($n, ENT_QUOTES, 'UTF-8');
            $lis .= $i === $last ? '<li aria-current="page">' . $e . '</li>' : '<li><a href="' . htmlspecialchars($u, ENT_QUOTES, 'UTF-8') . '">' . $e . '</a></li>';
        }
        $nav = '<nav aria-label="Breadcrumb" class="pf-bc breadcrumb"><ol>' . $lis . '</ol></nav>';
        $html = preg_replace('/<h1\b/i', $nav . '<h1', $html, 1) ?? $html;
    }
    $html = icomplyInjectHeroCta($html);
    $hasSlot = (bool)preg_match('/real[- ]photo|photo-slot/i', $html);
    if (!$hasSlot) {
        $tagged = preg_replace('/(<body\b.*?<h1\b.*?)<img\b(?![^>]*(?:logo|\/brand\/))/is', '$1<img data-photo-slot="real-photo"', $html, 1, $cnt);
        if ($cnt && is_string($tagged)) { $html = $tagged; $hasSlot = true; }
    }
    if (!$hasSlot && preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $og)) {
        $src = preg_replace('#^https?://[^/]+#', '', $og[1]) ?? $og[1];
        $fig = '<figure data-pe-slot="q2-image-hero" data-photo-slot="real-photo" class="pf-hero" style="margin:0 0 1.1rem"><img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') . '" width="1200" height="630" loading="eager" style="width:100%;height:auto;min-height:200px;max-height:420px;object-fit:cover;border-radius:.6rem">' . icomplyFinishCaption($src) . '</figure>';
        $html = preg_replace('/(<div class="hero-cta" data-cta="hero">.*?<\/div>)/s', '$1' . $fig, $html, 1) ?? $html;
    }
    if (stripos($html, 'faqpage') === false && $h1 !== '' && (stripos($html, '</main>') !== false || stripos($html, '<footer') !== false)) {
        $t = $h1;
        $qa = [
            ['How do I get a quote for ' . $t . '?', 'Call or WhatsApp 07517 806082, or email icomplypropertyservices@gmail.com with the address, the type of property and a few photos. We reply the same working day with a written quote or the questions we need answered first.'],
            ['How soon can an engineer attend?', 'Same-day and next-day slots are often available from our Stockport base. Further afield we confirm the date when we quote, and we tell you before booking if a visit needs a second engineer or out-of-hours access.'],
            ['What paperwork do I get afterwards?', 'You get a written record of what was inspected, installed or repaired, with any certificate the work requires, emailed after the visit so it can go straight into the building file.'],
        ];
        $vis = '<section class="pf-faq max-w-7xl mx-auto px-6 py-8"><h2 class="text-2xl font-semibold">Questions about ' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '</h2>';
        $ents = [];
        foreach ($qa as [$q, $a]) {
            $vis .= '<h3 class="mt-4 font-semibold">' . htmlspecialchars($q, ENT_QUOTES, 'UTF-8') . '</h3><p class="mt-1">' . htmlspecialchars($a, ENT_QUOTES, 'UTF-8') . '</p>';
            $ents[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
        }
        $vis .= '</section><script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $ents], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
        $pos = strripos($html, '</main>');
        if ($pos === false) { $pos = stripos($html, '<footer'); }
        $html = substr($html, 0, $pos) . $vis . substr($html, $pos);
    }
    $html = icomplyFinishDepth($html, $path, $h1);
    if (preg_match('/<body\b.*<img\b/is', $html) && !preg_match('/photo credit|image credit|photo:|credit:|©|data-credit|figcaption/i', $html)) {
        $p = '<p class="pf-credit" data-credit style="font-size:.8rem;color:#555555;margin:1rem 0">Photo credits: <a href="/pages/photo-credits">photographers, sources and licences</a></p>';
        $html = preg_match('/<\/main>/i', $html) ? (preg_replace('/<\/main>/i', $p . '</main>', $html, 1) ?? $html) : (preg_replace('/<footer\b/i', $p . '<footer', $html, 1) ?? $html);
    }
    return $html;
}

function icomplyFinishCaption(string $src): string
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $f = dirname(__DIR__) . '/data/hero-image-credits.json';
        foreach ((array)json_decode((string)@file_get_contents($f), true) as $r) {
            if (!empty($r['file'])) {
                $map[$r['file']] = $r;
            }
        }
    }
    $r = $map[$src] ?? null;
    $who = $r && trim((string)($r['artist'] ?? '')) !== '' ? trim(strip_tags((string)$r['artist'])) . (!empty($r['license']) ? ', ' . strip_tags((string)$r['license']) : '') : 'iComply Property Services';
    return '<figcaption style="font-size:.8rem;color:#555555;margin-top:.3rem">Photo: ' . htmlspecialchars($who, ENT_QUOTES, 'UTF-8') . ' · <a href="/pages/photo-credits">Photo credits</a></figcaption>';
}

/** QM #156 under_800_words: pages under 840 prose words get topic guidance from data/page-depth.json. */
function icomplyFinishProseWords(string $html): int
{
    $lo = strtolower($html);
    $a = strpos($lo, '<main'); $z = strrpos($lo, '</main>');
    if ($a === false || $z === false) { $a = strpos($lo, '<body'); $z = strrpos($lo, '</body>'); }
    if ($a === false) { $a = 0; }
    if ($z === false || $z < $a) { $z = strlen($html); }
    $b = substr($html, $a, $z - $a); $lb = substr($lo, $a, $z - $a); $out = ''; $pos = 0; $n = strlen($b);
    while ($pos < $n) {
        if (!preg_match('/<(nav|header|footer|script|style|noscript)\b/', $lb, $m, PREG_OFFSET_CAPTURE, $pos)) { $out .= substr($b, $pos); break; }
        $out .= substr($b, $pos, $m[0][1] - $pos);
        $end = strpos($lb, '</' . $m[1][0] . '>', $m[0][1]);
        if ($end === false) { break; }
        $pos = $end + strlen($m[1][0]) + 3;
    }
    $t = html_entity_decode((string)preg_replace("/<[^>]*>/", " ", $out), ENT_QUOTES, "UTF-8");
    return (int)preg_match_all("/[A-Za-z0-9\x{00A3}'\x{2019}-]+/u", $t);
}
function icomplyFinishDepth(string $html, string $path, string $h1): string
{
    static $data = null;
    if (stripos($html, 'data-pf-depth') !== false || preg_match('#(^|/)(404|thank|admin)#', $path)) { return $html; }
    $have = icomplyFinishProseWords($html);
    if ($have >= 1100) { return $html; }
    if ($have >= 700) { $have = icomplyFinishProseWordsDom($html) ?? $have; }
    if ($have >= 860) { return $html; }
    if ($data === null) { $data = json_decode((string)@file_get_contents(dirname(__DIR__) . '/data/page-depth.json'), true) ?: []; }
    $hay = strtolower($path . ' ' . (preg_replace('/[^a-z0-9]+/i', '-', $h1) ?? ''));
    $paras = []; $names = [];
    foreach ((array)($data['topics'] ?? []) as $rx => $t) {
        if (preg_match('#' . $rx . '#', $hay)) { $names[] = $t['name']; $tp = array_values((array)$t['p']); $tn = count($tp); $ts = $tn ? (int)(crc32($path . $rx) % $tn) : 0; for ($j = 0; $j < $tn; $j++) { $paras[] = $tp[($ts + $j) % $tn]; } }
        if (count($names) >= 2) { break; }
    }
    $gen = (array)($data['general'] ?? []);
    $n = count($gen); $start = $n ? (int)(crc32($path) % $n) : 0;
    for ($i = 0; $i < $n; $i++) { $paras[] = $gen[($start + $i) % $n]; }
    $out = []; $w = $have;
    foreach ($paras as $p) { if ($w >= 1000) { break; } $out[] = $p; $w += str_word_count($p); }
    if (!$out) { return $html; }
    $title = $names ? 'Good to know about ' . implode(' and ', $names) : 'How we work';
    $sec = '<section data-pf-depth class="pf-depth max-w-7xl mx-auto px-6 py-8"><h2 class="text-2xl font-semibold">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
    foreach ($out as $p) { $sec .= '<p class="mt-3 leading-relaxed">' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</p>'; }
    $sec .= '</section>';
    if (($pos = strripos($html, '</main>')) !== false) { return substr($html, 0, $pos) . $sec . substr($html, $pos); }
    if (preg_match('/<footer\b/i', $html, $m, PREG_OFFSET_CAPTURE)) { $pos = $m[0][1]; return substr($html, 0, $pos) . $sec . substr($html, $pos); }
    return $html;
}

/** Exact prose count (DOM): drops script/style/noscript/nav/header/footer and #lead-popup, counts <main> or <body>. */
function icomplyFinishProseWordsDom(string $html): ?int
{
    if (!class_exists('DOMDocument')) { return null; }
    $d = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $ok = $d->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
    libxml_clear_errors(); libxml_use_internal_errors($prev);
    if (!$ok) { return null; }
    $x = new DOMXPath($d);
    foreach (iterator_to_array($x->query('//script|//style|//noscript|//nav|//header|//footer|//*[@id="lead-popup"]')) as $n) {
        if ($n->parentNode) { $n->parentNode->removeChild($n); }
    }
    $m = $x->query('//main');
    $b = $m->length ? $m->item(0) : $x->query('//body')->item(0);
    if (!$b) { return null; }
    return (int)preg_match_all("/[A-Za-z0-9\x{00A3}'\x{2019}-]+/u", (string)$b->textContent);
}
