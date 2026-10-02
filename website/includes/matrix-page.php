<?php
/**
 * Compact quality HTML for the full keyword×town and service×area matrix.
 * Unique local copy (local-content / water-asbestos). Full area + keyword link clouds.
 * Fast enough for ~200k Netlify files. POA language for water/asbestos.
 */
declare(strict_types=1);

function icomplyMatrixIsExport(): bool
{
    return !empty($_ENV['ICOMPLY_STATIC_EXPORT'])
        || getenv('ICOMPLY_STATIC_EXPORT') === '1'
        || !empty($_SERVER['ICOMPLY_STATIC_EXPORT']);
}

function icomplyMatrixH(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function icomplyMatrixLeadPopupHtml(): string
{
    $s = icomplyMatrixShared();
    $js = icomplyMatrixH(assetUrl('/assets/js/lead-popup.js'));
    $contact = icomplyMatrixH($s['contact']);
    $phone = icomplyMatrixH($s['phone']);
    $phoneHref = icomplyMatrixH($s['phoneHref']);
    $wa = icomplyMatrixH($s['whatsapp']);
    return '<form name="lead-popup" method="POST" action="/thank-you" data-netlify="true" netlify-honeypot="bot-field" hidden>'
        . '<input type="hidden" name="form-name" value="lead-popup">'
        . '<input name="bot-field"><input name="name"><input name="email"><input name="phone">'
        . '<input name="service"><textarea name="message"></textarea><input name="source" value="lead-popup">'
        . '</form>'
        . '<div id="lead-popup" class="lead-popup" hidden>'
        . '<div class="lead-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="lead-popup-title" tabindex="-1">'
        . '<button type="button" class="lead-close" data-lead-close aria-label="Close quote popup">&times;</button>'
        . '<h2 id="lead-popup-title">Need a compliance quote?</h2>'
        . '<p>Price on application. Call, WhatsApp, or send a short note.</p>'
        . '<div class="lead-quick">'
        . '<a class="lead-call" href="' . $phoneHref . '">Call ' . $phone . '</a>'
        . '<a class="lead-wa" href="https://wa.me/' . $wa . '?text=Hi%20iComply%2C%20I%20need%20a%20quote" target="_blank" rel="noopener">WhatsApp</a>'
        . '<a class="lead-book" href="' . $contact . '">Book quote</a>'
        . '</div>'
        . '<form id="lead-popup-form" name="lead-popup" method="POST" action="/thank-you" data-netlify="true" netlify-honeypot="bot-field">'
        . '<input type="hidden" name="form-name" value="lead-popup">'
        . '<label for="lp-name">Name</label><input id="lp-name" name="name" required maxlength="120">'
        . '<label for="lp-email">Email</label><input id="lp-email" name="email" type="email" required>'
        . '<label for="lp-phone">Phone</label><input id="lp-phone" name="phone" required maxlength="40">'
        . '<label for="lp-service">Service interest</label>'
        . '<select id="lp-service" name="service" required>'
        . '<option value="">Select…</option>'
        . '<option value="Legionella Risk Assessment">Legionella risk assessment</option>'
        . '<option value="Asbestos Survey">Asbestos survey</option>'
        . '<option value="Electrical">Electrical</option>'
        . '<option value="Fire Risk Assessments">Fire risk assessments</option>'
        . '<option value="Multi-service / not sure">Multi-service / not sure</option>'
        . '</select>'
        . '<label for="lp-message">Message (short)</label>'
        . '<textarea id="lp-message" name="message" rows="3" required maxlength="800"></textarea>'
        . '<input type="hidden" name="source" value="lead-popup">'
        . '<button type="submit">Send to iComply</button>'
        . '<p class="lead-status" id="lead-popup-status" role="status" aria-live="polite"></p>'
        . '</form></div></div>'
        . '<script src="' . $js . '" defer></script>';
}

function icomplyMatrixShared(): array
{
    static $s = null;
    if ($s !== null) {
        return $s;
    }
    if (!function_exists('isPoaService')) {
        $wa = SITE_ROOT . '/includes/water-asbestos.php';
        if (is_file($wa)) {
            require_once $wa;
        }
    }
    if (!function_exists('area_profile')) {
        require_once SITE_ROOT . '/includes/local-content.php';
    }
    if (!function_exists('service_standards')) {
        require_once SITE_ROOT . '/includes/seo.php';
    }

    $areas = getAreas();
    $areaPairs = [];
    foreach ($areas as $area) {
        $areaPairs[] = ['name' => (string)$area, 'slug' => areaSlug((string)$area)];
    }

    $kwByService = [];
    foreach (getMajorKeywords() as $slug => $meta) {
        $svc = (string)($meta['service'] ?? '');
        if ($svc === '') {
            continue;
        }
        $kwByService[$svc][$slug] = (string)($meta['name'] ?? keywordDisplayName($slug));
    }

    $home = rtrim(SITE_URL, '/') . '/';
    $css = assetUrl('/assets/css/site.css');
    $phone = defined('PHONE') ? PHONE : '';
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', $phone);
    $wa = defined('WHATSAPP') ? WHATSAPP : '';

    $s = [
        'areas' => $areaPairs,
        'kwByService' => $kwByService,
        'home' => $home,
        'css' => $css,
        'phone' => $phone,
        'phoneHref' => $phoneHref,
        'whatsapp' => $wa,
        'services' => getServices(),
        'contact' => url('/contact'),
        'areasHub' => url('/pages/areas'),
        'kwHub' => url('/pages/keywords'),
        'svcHub' => url('/pages/services'),
        'brand' => defined('SITE_NAME') ? SITE_NAME : 'Icomply Property Services',
    ];
    return $s;
}

function icomplyMatrixChromeStart(string $title, string $desc, string $canonical, string $robots = 'index, follow'): string
{
    $s = icomplyMatrixShared();
    $t = icomplyMatrixH($title);
    $d = icomplyMatrixH($desc);
    $c = icomplyMatrixH($canonical);
    $css = icomplyMatrixH($s['css']);
    $home = icomplyMatrixH($s['home']);
    $brand = icomplyMatrixH($s['brand']);
    $phone = icomplyMatrixH($s['phone']);
    $phoneHref = icomplyMatrixH($s['phoneHref']);
    return '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="theme-color" content="#0B1F3A">'
        . '<meta name="robots" content="' . icomplyMatrixH($robots) . '">'
        . '<title>' . $t . '</title>'
        . '<meta name="description" content="' . $d . '">'
        . '<link rel="canonical" href="' . $c . '">'
        . '<meta property="og:title" content="' . $t . '">'
        . '<meta property="og:description" content="' . $d . '">'
        . '<meta property="og:url" content="' . $c . '">'
        . '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">'
        . '<link rel="stylesheet" href="' . $css . '">'
        . '</head><body class="matrix-page bg-zinc-50 text-black">'
        . '<header class="matrix-header">'
        . '<div class="matrix-wrap py-3 flex flex-wrap items-center justify-between gap-3 text-sm">'
        . '<a class="font-semibold text-lg" href="' . $home . '">' . $brand . '</a>'
        . '<nav class="flex flex-wrap gap-3 font-medium">'
        . '<a href="' . icomplyMatrixH($s['svcHub']) . '">Services</a>'
        . '<a href="' . icomplyMatrixH($s['areasHub']) . '">Areas</a>'
        . '<a href="' . icomplyMatrixH($s['kwHub']) . '">Keywords</a>'
        . '<a href="' . icomplyMatrixH($s['contact']) . '">Contact</a>'
        . '<a href="' . $phoneHref . '">' . $phone . '</a>'
        . '</nav></div></header>';
}

function icomplyMatrixChromeEnd(): string
{
    $s = icomplyMatrixShared();
    $popup = icomplyMatrixLeadPopupHtml();
    return '<footer class="bg-[#0B1F3A] text-white mt-12">'
        . '<div class="matrix-wrap py-10 text-sm text-white/80 space-y-2">'
        . '<div class="font-semibold text-white">' . icomplyMatrixH($s['brand']) . '</div>'
        . '<p>Stockport SK2 5DE · Greater Manchester and the North West.</p>'
        . '<div class="flex flex-wrap gap-4">'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['svcHub']) . '">All services</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['areasHub']) . '">All areas</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['kwHub']) . '">All keyword guides</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH(url('/pages/site-map.php')) . '">Site map / full inventory</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['contact']) . '">Contact / POA quote</a>'
        . '</div>'
        . '<p class="text-white/60 text-xs">Hub pages carry the mega menu and footer dropdowns for every service, town and keyword family. This matrix page stays compact on purpose.</p>'
        . '</div></footer>' . $popup . '</body></html>';
}

function icomplyMatrixAreaChips(string $hrefPrefix): string
{
    $s = icomplyMatrixShared();
    $html = '<div class="chip-cloud">';
    foreach ($s['areas'] as $a) {
        $href = $hrefPrefix . $a['slug'];
        $html .= '<a class="area-chip" href="' . icomplyMatrixH($href) . '">' . icomplyMatrixH($a['name']) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function icomplyMatrixKeywordChips(string $serviceSlug): string
{
    $s = icomplyMatrixShared();
    $kws = $s['kwByService'][$serviceSlug] ?? [];
    $html = '<div class="chip-cloud">';
    foreach ($kws as $slug => $name) {
        $html .= '<a class="kw-chip" href="' . icomplyMatrixH(url('/pages/keywords/' . $slug)) . '">'
            . icomplyMatrixH($name) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function icomplyRenderKeywordTownHtml(string $keywordSlug, string $areaName): string
{
    $s = icomplyMatrixShared();
    $keywords = getMajorKeywords();
    $keywordSlug = keywordSlug($keywordSlug);
    if (!isset($keywords[$keywordSlug])) {
        return '';
    }
    $meta = $keywords[$keywordSlug];
    $kwName = (string)($meta['name'] ?? keywordDisplayName($keywordSlug));
    $svcSlug = (string)($meta['service'] ?? 'electrical');
    $svcName = $s['services'][$svcSlug] ?? keywordDisplayName($svcSlug);
    $areaSlugVal = areaSlug($areaName);
    $poa = function_exists('isPoaService') && isPoaService($svcSlug);
    $priceLine = $poa
        ? 'Price on application after we confirm property type, access and scope. No catalogue fee.'
        : 'Written quote after we confirm scope. We do not invent a price on this page.';

    $intro = function_exists('seo_unique_intro')
        ? seo_unique_intro($svcName, $svcSlug, $areaName)
        : $kwName . ' in ' . $areaName . ' from iComply Property Services.';
    if (function_exists('waterAsbestosAreaIntro')) {
        $extra = waterAsbestosAreaIntro($svcSlug, $areaName);
        if ($extra !== '') {
            $intro .= ' ' . $extra;
        }
    }
    $body = (string)($meta['body'] ?? '');
    $bullets = function_exists('seo_unique_local_block')
        ? seo_unique_local_block($svcName, $svcSlug, $areaName)
        : [];

    $title = $kwName . ' in ' . $areaName . ' | ' . $s['brand'];
    $desc = $kwName . ' in ' . $areaName . '. ' . $priceLine;
    if (strlen($desc) > 160) {
        $desc = substr($desc, 0, 157) . '…';
    }
    $canonical = url('/pages/keywords/' . $keywordSlug . '/' . $areaSlugVal);

    $html = icomplyMatrixChromeStart($title, $desc, $canonical);
    $html .= '<section class="matrix-hero"><div class="matrix-wrap">'
        . '<p class="text-xs uppercase tracking-widest text-white/60">' . icomplyMatrixH($svcName) . ' · ' . icomplyMatrixH($areaName) . '</p>'
        . '<h1>' . icomplyMatrixH($kwName) . ' <span class="accent">in ' . icomplyMatrixH($areaName) . '</span></h1>'
        . '<p class="mt-4 text-white/80 max-w-2xl">' . icomplyMatrixH($intro) . '</p>'
        . '<div class="mt-6 flex flex-wrap gap-3">'
        . '<a class="matrix-cta matrix-cta-accent" href="' . icomplyMatrixH($s['contact']) . '">Request a quote</a>'
        . '<a class="matrix-cta matrix-cta-light" href="' . icomplyMatrixH($s['phoneHref']) . '">' . icomplyMatrixH($s['phone']) . '</a>'
        . '</div>'
        . '<p class="mt-4 text-sm text-white/60">' . icomplyMatrixH($priceLine) . '</p>'
        . '</div></section>';

    $html .= '<main class="matrix-wrap py-10 space-y-10">'
        . '<article class="matrix-card space-y-4">'
        . '<h2 class="text-2xl font-semibold">About this ' . icomplyMatrixH($areaName) . ' page</h2>'
        . '<p class="text-zinc-700 leading-relaxed">' . icomplyMatrixH($body) . '</p>';
    if ($bullets) {
        $html .= '<ul class="space-y-2 text-zinc-700">';
        foreach ($bullets as $b) {
            $html .= '<li><span class="text-[#ff6b00]">●</span> ' . icomplyMatrixH((string)$b) . '</li>';
        }
        $html .= '</ul>';
    }
    $html .= '<p class="text-sm">Service hub: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH(url('/pages/services/' . $svcSlug)) . '">' . icomplyMatrixH($svcName) . '</a>'
        . ' · Keyword hub: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH(url('/pages/keywords/' . $keywordSlug)) . '">' . icomplyMatrixH($kwName) . '</a>'
        . ' · Town hub: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH(url('/pages/areas/' . $areaSlugVal)) . '">' . icomplyMatrixH($areaName) . '</a></p>'
        . '</article>';

    $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($kwName) . ' in every area we cover</h2>'
        . '<p class="text-sm text-zinc-600 mb-4">' . count($s['areas']) . ' towns — full list, not a short subset.</p>'
        . icomplyMatrixAreaChips(url('/pages/keywords/' . $keywordSlug . '/') )
        . '</section>';

    $html .= '<section><h2 class="text-2xl font-semibold mb-3">All ' . icomplyMatrixH($svcName) . ' keyword guides</h2>'
        . '<p class="text-sm text-zinc-600 mb-4">Every topic under this service, each with the same full town list.</p>'
        . icomplyMatrixKeywordChips($svcSlug)
        . '</section></main>';

    $html .= icomplyMatrixChromeEnd();
    return $html;
}

function icomplyRenderServiceAreaHtml(string $serviceSlug, string $areaName): string
{
    $s = icomplyMatrixShared();
    $serviceSlug = areaSlug($serviceSlug);
    if (!isset($s['services'][$serviceSlug])) {
        return '';
    }
    $svcName = $s['services'][$serviceSlug];
    $areaSlugVal = areaSlug($areaName);
    $poa = function_exists('isPoaService') && isPoaService($serviceSlug);
    $priceLine = $poa
        ? 'Price on application after scope. No invented catalogue price.'
        : 'Written quote after scope is agreed.';
    $intro = function_exists('seo_unique_intro')
        ? seo_unique_intro($svcName, $serviceSlug, $areaName)
        : $svcName . ' in ' . $areaName . '.';
    if (function_exists('waterAsbestosAreaIntro')) {
        $extra = waterAsbestosAreaIntro($serviceSlug, $areaName);
        if ($extra !== '') {
            $intro .= ' ' . $extra;
        }
    }
    $blurb = getServiceBlurb($serviceSlug);
    $standards = getServiceStandards($serviceSlug);
    $title = $svcName . ' in ' . $areaName . ' | ' . $s['brand'];
    $desc = $svcName . ' in ' . $areaName . '. ' . $priceLine;
    $canonical = url('/pages/' . $serviceSlug . '/' . $areaSlugVal);

    $html = icomplyMatrixChromeStart($title, $desc, $canonical);
    $html .= '<section class="matrix-hero"><div class="matrix-wrap">'
        . '<p class="text-xs uppercase tracking-widest text-white/60">' . icomplyMatrixH($areaName) . ' · North West</p>'
        . '<h1>' . icomplyMatrixH($svcName) . ' <span class="accent">in ' . icomplyMatrixH($areaName) . '</span></h1>'
        . '<p class="mt-4 text-white/80 max-w-2xl">' . icomplyMatrixH($intro) . '</p>'
        . '<div class="mt-6 flex flex-wrap gap-3">'
        . '<a class="matrix-cta matrix-cta-accent" href="' . icomplyMatrixH($s['contact']) . '">Request a quote</a>'
        . '<a class="matrix-cta matrix-cta-light" href="' . icomplyMatrixH($s['phoneHref']) . '">' . icomplyMatrixH($s['phone']) . '</a>'
        . '</div>'
        . '<p class="mt-4 text-sm text-white/60">' . icomplyMatrixH($standards) . ' · ' . icomplyMatrixH($priceLine) . '</p>'
        . '</div></section>';

    $bullets = function_exists('seo_unique_local_block')
        ? seo_unique_local_block($svcName, $serviceSlug, $areaName)
        : [];
    $html .= '<main class="matrix-wrap py-10 space-y-10">'
        . '<article class="matrix-card space-y-4">'
        . '<h2 class="text-2xl font-semibold">What we do in ' . icomplyMatrixH($areaName) . '</h2>'
        . '<p class="text-zinc-700 leading-relaxed">' . icomplyMatrixH($blurb) . '</p>';
    if ($bullets) {
        $html .= '<ul class="space-y-2 text-zinc-700">';
        foreach ($bullets as $b) {
            $html .= '<li><span class="text-[#ff6b00]">●</span> ' . icomplyMatrixH((string)$b) . '</li>';
        }
        $html .= '</ul>';
    }
    $html .= '<p class="text-sm">Service hub: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH(url('/pages/services/' . $serviceSlug)) . '">' . icomplyMatrixH($svcName) . '</a>'
        . ' · Town: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH(url('/pages/areas/' . $areaSlugVal)) . '">' . icomplyMatrixH($areaName) . '</a></p>'
        . '</article>';
    if (function_exists('icomplyAreaExpansionRecords')) {
        $bucketRows = icomplyAreaExpansionRecords();
        if (isset($bucketRows[areaSlug($areaName)])) {
            $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($svcName) . ' across this local batch</h2>'
                . '<p class="text-sm text-zinc-600 mb-4">Beech through Burton — every place in this batch has the same service page. Not listed in the XML sitemap.</p><div class="chip-cloud">';
            foreach ($bucketRows as $bSlug => $bRow) {
                $html .= '<a class="area-chip" href="' . icomplyMatrixH(url('/pages/' . $serviceSlug . '/' . $bSlug)) . '">'
                    . icomplyMatrixH((string)$bRow['name']) . '</a>';
            }
            $html .= '</div></section>';
        }
    }

    $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($svcName) . ' in every area</h2>'
        . '<p class="text-sm text-zinc-600 mb-4">' . count($s['areas']) . ' towns.</p>'
        . icomplyMatrixAreaChips(url('/pages/' . $serviceSlug . '/'))
        . '</section>';

    $html .= '<section><h2 class="text-2xl font-semibold mb-3">All keywords for this service</h2>'
        . icomplyMatrixKeywordChips($serviceSlug)
        . '</section></main>';

    $html .= icomplyMatrixChromeEnd();
    return $html;
}
