<?php
/**
 * Final pass on every exported page: shared under-H1 call/WhatsApp row (hero-cta.php, from #155)
 * and a visible breadcrumb + BreadcrumbList schema when the template did not print one.
 * Twin of netlify/lib/page-finish.js. Phone 07517 806082 only.
 */
declare(strict_types=1);
require_once __DIR__ . '/hero-cta.php';

const ICOMPLY_FINISH_CSS = '<style id="pf-css">.hero-cta{display:flex;flex-wrap:wrap;gap:.6rem;margin:.35rem 0 1.1rem}.hero-cta a{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:.55rem 1rem;border-radius:.55rem;color:#fff;font-weight:700;text-decoration:none}.hero-cta a.call{background:#FF6B00}.hero-cta a.wa{background:#0B1F3A}.pf-bc{font-size:.85rem;color:#333333;margin:.5rem 0}.pf-bc a{color:#333333;text-decoration:underline}.pf-bc ol{list-style:none;display:flex;flex-wrap:wrap;gap:.35rem;padding:0;margin:0}.pf-bc li+li:before{content:"›";margin-right:.35rem}</style>';

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
    if (!str_contains($html, 'data-pe-slot') && preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $og)) {
        $src = preg_replace('#^https?://[^/]+#', '', $og[1]) ?? $og[1];
        $fig = '<figure data-pe-slot="q2-image-hero" class="pf-hero" style="margin:0 0 1.1rem"><img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') . '" width="1200" height="630" loading="eager" style="width:100%;height:auto;min-height:200px;max-height:420px;object-fit:cover;border-radius:.6rem"></figure>';
        $html = preg_replace('/(<div class="hero-cta" data-cta="hero">.*?<\/div>)/s', '$1' . $fig, $html, 1) ?? $html;
    }
    return $html;
}
