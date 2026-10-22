<?php
/**
 * P0-CTR-MONEY (SEO brief 2026-10-19, C1-C15) for statically exported pages.
 * Twin of netlify/lib/money-ctr.js; same data file. Exact title/meta, plus
 * authored depth/FAQ/related links where the brief asks. Never touches
 * robots, canonical or the sitemap.
 */

function icomplyMoneyCtrPages(): array
{
    static $pages = null;
    if ($pages === null) {
        $file = dirname(__DIR__) . '/data/money-ctr-c1-c15.json';
        $doc = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
        $pages = is_array($doc['pages'] ?? null) ? $doc['pages'] : [];
        // GSC 21 Oct upgrade queue; C1-C15 entries win on overlap.
        $gscFile = dirname(__DIR__) . '/data/gsc-upgrade-2026-10-21.json';
        $gsc = is_file($gscFile) ? json_decode((string)file_get_contents($gscFile), true) : null;
        if (is_array($gsc['pages'] ?? null)) {
            $pages = $pages + $gsc['pages'];
        }
        // GSC rankings upgrade 22 Oct wins over C1-C15 and 21 Oct.
        $rank = icomplyRankDoc();
        if (is_array($rank['pages'] ?? null)) {
            $pages = $rank['pages'] + $pages;
        }
    }
    return $pages;
}

function icomplyRankDoc(): array
{
    static $doc = null;
    if ($doc === null) {
        $f = dirname(__DIR__) . '/data/gsc-upgrade-2026-10-22.json';
        $doc = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    }
    return $doc;
}

/** Hub -> upgraded page links (22 Oct queue). Twin of applyRankInbound in money-ctr.js. */
function icomplyApplyRankInbound(string $path, string $html): string
{
    $list = icomplyRankDoc()['inbound'][$path] ?? null;
    if (!is_array($list) || str_contains($html, 'data-pe-slot="rank-inbound"')) return $html;
    $have = icomplyMoneyCtrInternalHrefs($html);
    $add = array_values(array_filter($list, fn($l) => $l[0] !== $path && !isset($have[$l[0]])));
    if (!$add) return $html;
    $nav = '<nav class="money-ctr-links" data-pe-slot="rank-inbound" aria-label="Popular local pages" style="max-width:72rem;margin:2rem auto;padding:0 1rem"><h2>Popular local pages</h2><ul>';
    foreach ($add as [$href, $label]) $nav .= '<li><a href="' . icomplyMoneyCtrEsc($href) . '">' . icomplyMoneyCtrEsc($label) . '</a></li>';
    $nav .= '</ul></nav>';
    foreach (['<footer', '</body>'] as $anchor) {
        $i = strpos($html, $anchor);
        if ($i !== false) return substr($html, 0, $i) . $nav . substr($html, $i);
    }
    return $html;
}

function icomplyMoneyCtrEsc(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function icomplyMoneyCtrInternalHrefs(string $html): array
{
    $site = 'https://icomplypropertyservices.co.uk';
    $out = [];
    if (preg_match_all('/href="([^"]+)"/i', $html, $m)) {
        foreach ($m[1] as $h) {
            $h = html_entity_decode($h, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (str_starts_with($h, $site)) {
                $h = substr($h, strlen($site)) ?: '/';
            }
            if (!str_starts_with($h, '/') || str_starts_with($h, '//')) continue;
            $h = explode('?', explode('#', $h)[0])[0];
            if ($h === '' || str_starts_with($h, '/assets/') || preg_match('/\.(css|js|png|jpe?g|webp|svg|ico|xml|json|webmanifest)$/i', $h)) continue;
            if (strlen($h) > 1) $h = rtrim($h, '/');
            $out[$h] = true;
        }
    }
    return $out;
}

function icomplyMoneyCtrSetMeta(string $html, string $attr, string $key, string $value): string
{
    $re = '/(<meta\s+' . $attr . '="' . preg_quote($key, '/') . '"\s+content=")[^"]*(")/i';
    return preg_replace_callback($re, fn($m) => $m[1] . icomplyMoneyCtrEsc($value) . $m[2], $html, 1) ?? $html;
}

/** 22 Oct pages: Service / FAQPage JSON-LD only where the page has none. Twin of rankSchema(). */
function icomplyRankSchema(string $path, array $page, string $html): string
{
    if (($page['brief'] ?? '') !== 'GSC-2026-10-22') return '';
    preg_match_all('#<script type="application/ld\+json">.*?</script>#s', $html, $m);
    $ld = implode(' ', $m[0]);
    $site = 'https://icomplypropertyservices.co.uk';
    $out = [];
    if (!preg_match('/"@type"\s*:\s*"Service"/', $ld)) {
        $out[] = ['@context' => 'https://schema.org', '@type' => 'Service', 'name' => explode(' | ', (string)$page['title'])[0], 'url' => $site . $path, 'description' => $page['meta'],
            'provider' => ['@type' => 'LocalBusiness', 'name' => 'iComply Property Services', 'telephone' => '07517 806082', 'email' => 'icomplypropertyservices@gmail.com', 'url' => $site],
            'areaServed' => $page['town'] ?? 'Greater Manchester and the North West'];
    }
    if (!preg_match('/"@type"\s*:\s*"FAQPage"/', $ld) && !empty($page['faqs'])) {
        $out[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $page['faqs'])];
    }
    $s = '';
    foreach ($out as $o) $s .= '<script type="application/ld+json">' . json_encode($o, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>';
    return $s;
}

function icomplyApplyMoneyCtr(string $path, string $html): string
{
    $html = icomplyApplyRankInbound($path, $html);
    $pages = icomplyMoneyCtrPages();
    $page = $pages[$path] ?? null;
    if (!is_array($page)) return $html;
    if (($page['brief'] ?? '') === 'GSC-2026-10-22' && !preg_match('/gas|boiler|cp12|lgsr|heating|flue|cooker|hob/', $path)) {
        $html = preg_replace('#<details class="foot-sub"><summary>Gas Systems</summary>.*?</details>#s', '', $html, 1) ?? $html;
    }
    $schema = icomplyRankSchema($path, $page, $html);
    if ($schema !== '') $html = stripos($html, '</head>') !== false ? preg_replace('#</head>#i', $schema . '</head>', $html, 1) : $html . $schema;
    $out = preg_replace('/<title>[\s\S]*?<\/title>/i', '<title>' . icomplyMoneyCtrEsc($page['title']) . '</title>', $html, 1) ?? $html;
    $out = icomplyMoneyCtrSetMeta($out, 'name', 'description', $page['meta']);
    $out = icomplyMoneyCtrSetMeta($out, 'property', 'og:title', $page['title']);
    $out = icomplyMoneyCtrSetMeta($out, 'property', 'og:description', $page['meta']);
    $out = icomplyMoneyCtrSetMeta($out, 'name', 'twitter:title', $page['title']);
    $out = icomplyMoneyCtrSetMeta($out, 'name', 'twitter:description', $page['meta']);
    if (str_contains($out, 'data-pe-slot="money-ctr"')) return $out;

    $s = '<section class="money-ctr" style="max-width:72rem;margin:2rem auto;padding:0 1rem" data-pe-slot="money-ctr" data-brief="' . icomplyMoneyCtrEsc($page['brief']) . '">';
    if (!empty($page['h2']) && is_array($page['paras'] ?? null)) {
        $s .= '<h2>' . icomplyMoneyCtrEsc($page['h2']) . '</h2>';
        foreach ($page['paras'] as $p) $s .= '<p>' . icomplyMoneyCtrEsc($p) . '</p>';
    }
    if (!empty($page['faqs'])) {
        $s .= '<h2>' . icomplyMoneyCtrEsc($page['faqHeading'] ?? 'Common questions') . '</h2>';
        foreach ($page['faqs'] as [$q, $a]) {
            $s .= '<details class="faq-item"><summary>' . icomplyMoneyCtrEsc($q) . '</summary><p>' . icomplyMoneyCtrEsc($a) . '</p></details>';
        }
    }
    $have = icomplyMoneyCtrInternalHrefs($out);
    $target = (int)($page['linkTarget'] ?? 0);
    $hubs = $page['hubs'] ?? [];
    $add = [];
    foreach ($page['links'] ?? [] as [$href, $label]) {
        if (isset($have[$href]) || $href === $path) continue;
        if (!in_array($href, $hubs, true) && $target && count($have) >= $target) continue;
        $add[] = [$href, $label];
        $have[$href] = true;
    }
    if ($add) {
        $s .= '<nav class="money-ctr-links" aria-label="Related pages"><h2>' . icomplyMoneyCtrEsc($page['linksHeading'] ?? 'Related pages') . '</h2><ul>';
        foreach ($add as [$href, $label]) $s .= '<li><a href="' . icomplyMoneyCtrEsc($href) . '">' . icomplyMoneyCtrEsc($label) . '</a></li>';
        $s .= '</ul></nav>';
    }
    $s .= '</section>';
    foreach (['<section id="faq"', '</article>', '</main>', '<footer', '</body>'] as $anchor) {
        $i = strpos($out, $anchor);
        if ($i !== false) return substr($out, 0, $i) . $s . substr($out, $i);
    }
    return $out;
}
