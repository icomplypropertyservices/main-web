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
    }
    return $pages;
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

function icomplyApplyMoneyCtr(string $path, string $html): string
{
    $pages = icomplyMoneyCtrPages();
    $page = $pages[$path] ?? null;
    if (!is_array($page)) return $html;
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
