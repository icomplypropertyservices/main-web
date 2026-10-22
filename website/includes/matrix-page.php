<?php
/**
 * Compact quality HTML for the full keyword×town and service×area matrix.
 * Unique local copy (local-content / water-asbestos). Full area + keyword link clouds.
 * Fast enough for ~200k Netlify files. POA language for water/asbestos.
 */
declare(strict_types=1);

require_once __DIR__ . '/town-facts.php';

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
    // Same small call/WhatsApp prompt as includes/lead-popup.php (no form).
    ob_start();
    include __DIR__ . '/lead-popup.php';
    return (string) ob_get_clean();
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
        if (!is_array($meta) || !empty($meta['hub_only'])) {
            continue;
        }
        $svc = (string)($meta['service'] ?? '');
        if ($svc === '') {
            continue;
        }
        $kwByService[$svc][$slug] = (string)($meta['name'] ?? keywordDisplayName($slug));
    }

    $home = rtrim(SITE_URL, '/') . '/';
    $css = assetUrl('/assets/css/site.css');
    $phone = function_exists('icomplyPhoneDisplay') ? icomplyPhoneDisplay() : (defined('PHONE') ? PHONE : '');
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', defined('PHONE') ? (string)PHONE : $phone);
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
        'brand' => defined('SITE_NAME') ? SITE_NAME : 'iComply Property Services',
    ];
    return $s;
}

function icomplyMatrixChromeStart(string $title, string $desc, string $canonical, string $robots = 'index, follow', string $imagePath = '/assets/images/services/fire-alarms.jpg', string $serviceSlug = ''): string
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
    $mega = in_array($serviceSlug, ['fencing', 'drainage', 'driveways', 'demolition', 'roofing'], true);
    $head = '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="theme-color" content="#0B1F3A">'
        . '<meta name="robots" content="' . icomplyMatrixH($robots) . '">'
        . '<title>' . $t . '</title>'
        . '<meta name="description" content="' . $d . '">'
        . '<link rel="canonical" href="' . $c . '">'
        . '<meta property="og:type" content="website">'
        . '<meta property="og:title" content="' . $t . '">'
        . '<meta property="og:description" content="' . $d . '">'
        . '<meta property="og:url" content="' . $c . '">'
        . '<meta property="og:image" content="' . icomplyMatrixH(icomply_absolute_url($imagePath)) . '">'
        . '<link rel="stylesheet" href="' . icomplyMatrixH(assetUrl('/assets/css/utilities.css')) . '">'
        . '<link rel="stylesheet" href="' . $css . '">';
    if ($mega) {
        $head .= '<link rel="stylesheet" href="' . icomplyMatrixH(assetUrl('/assets/css/site.css')) . '">';
    }
    $head .= '<script type="application/ld+json">' . json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => 'https://icomplypropertyservices.co.uk/#business',
            'name' => 'iComply Property Services',
            'url' => 'https://icomplypropertyservices.co.uk/',
            'telephone' => '+447517806082',
            'email' => 'icomplypropertyservices@gmail.com',
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => '17 Woodlands Park Road, Offerton', 'addressLocality' => 'Stockport', 'postalCode' => 'SK2 5DE', 'addressCountry' => 'GB'],
            'areaServed' => ['Greater Manchester', 'North West England'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . '</script>'
        . '</head><body class="matrix-page bg-zinc-50 text-black">';
    if ($mega) {
        if (!function_exists('icomplyMegaHeaderHtml')) {
            require_once __DIR__ . '/site-nav.php';
        }
        return $head . icomplyMegaHeaderHtml();
    }
    return $head
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

function icomplyMatrixChromeEnd(string $footNote = ''): string
{
    $s = icomplyMatrixShared();
    $popup = icomplyMatrixLeadPopupHtml();
    $note = $footNote !== ''
        ? '<p>' . icomplyMatrixH($footNote) . '</p>'
        : '';
    return '<footer class="bg-[#0B1F3A] text-white mt-12">'
        . '<div class="matrix-wrap py-10 text-sm text-white/80 space-y-2">'
        . '<div class="font-semibold text-white">' . icomplyMatrixH($s['brand']) . '</div>'
        . '<p>Stockport SK2 5DE · Greater Manchester and the North West.</p>'
        . $note
        . '<div class="flex flex-wrap gap-4">'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['svcHub']) . '">All services</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['areasHub']) . '">All areas</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['kwHub']) . '">All service guides</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH(url('/pages/site-map.php')) . '">Site map / full inventory</a>'
        . '<a class="text-[#ff6b00]" href="' . icomplyMatrixH($s['contact']) . '">Contact / POA quote</a>'
        . '</div>'
        . '<p class="text-white/60 text-xs"></p>'
        . '</div></footer>' . $popup . '</body></html>';
}

function icomplyMatrixAreaChips(string $hrefPrefix, ?array $areas = null): string
{
    if ($areas === null) {
        $areas = icomplyMatrixShared()['areas'];
    }
    $html = '<div class="chip-cloud">';
    foreach ($areas as $a) {
        $href = $hrefPrefix . $a['slug'];
        $html .= '<a class="area-chip" href="' . icomplyMatrixH($href) . '">' . icomplyMatrixH($a['name']) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * For town-facts services, show the 40 nearest towns (by coordinates) instead of
 * the full cloud, so each page's links are local. Falls back to the full list.
 */
function icomplyMatrixNearestAreas(array $areas, string $slug, string $service, int $limit = 40): ?array
{
    if (!icomplyTownFactsEnabled($service)) {
        return null;
    }
    $me = icomplyTownFactsGeo($slug);
    if (!$me) {
        return null;
    }
    $rows = [];
    foreach ($areas as $a) {
        $g = icomplyTownFactsGeo((string)$a['slug']);
        if (!$g || $a['slug'] === $slug) {
            continue;
        }
        $dy = ($g[5] - $me[5]) * 69.0;
        $dx = ($g[6] - $me[6]) * 69.0 * cos(deg2rad((float)$me[5]));
        $miles = sqrt($dx * $dx + $dy * $dy);
        if ($miles > 12.0) {
            continue;
        }
        $rows[] = [$miles, $a];
    }
    usort($rows, static fn($x, $y) => $x[0] <=> $y[0]);
    return array_map(static fn($r) => $r[1], array_slice($rows, 0, $limit));
}

/**
 * CLOSE-Q8 (2026-10-14): lean service x town pages link the other services
 * exported for the same town (up to 24), so the page keeps >= 80 internal
 * links without the old full keyword footer or the uncapped hub lists.
 */
function icomplyMatrixOtherServicesInTown(string $serviceSlug, string $areaName, int $limit = 24): string
{
    static $areaSets = [];
    $s = icomplyMatrixShared();
    $town = areaSlug($areaName);
    $links = '';
    $n = 0;
    foreach ($s['services'] as $slug => $name) {
        if ($slug === $serviceSlug) {
            continue;
        }
        if (!isset($areaSets[$slug])) {
            $list = function_exists('getAreasForService') ? getAreasForService((string)$slug) : getAreas();
            $areaSets[$slug] = array_flip(array_map('strval', (array)$list));
        }
        if (!isset($areaSets[$slug][$areaName])) {
            continue;
        }
        if (function_exists('icomplyTownPublishedForService') && !icomplyTownPublishedForService((string)$slug, $town)) {
            continue;
        }
        $links .= '<a class="area-chip" href="' . icomplyMatrixH(url('/pages/' . $slug . '/' . $town)) . '">'
            . icomplyMatrixH($name . ' in ' . $areaName) . '</a>';
        if (++$n >= $limit) {
            break;
        }
    }
    if ($links === '') {
        return '';
    }
    return '<section><h2 class="text-2xl font-semibold mb-3">Other services in ' . icomplyMatrixH($areaName) . '</h2>'
        . '<div class="chip-cloud">' . $links . '</div></section>';
}

function icomplyMatrixKeywordChips(string $serviceSlug): string
{
    if (!function_exists('icomplySpokenLinkLabel')) {
        require_once __DIR__ . '/related.php';
    }
    $s = icomplyMatrixShared();
    $kws = $s['kwByService'][$serviceSlug] ?? [];
    $html = '<div class="chip-cloud">';
    foreach ($kws as $slug => $name) {
        $label = function_exists('icomplySpokenLinkLabel')
            ? icomplySpokenLinkLabel((string)$slug, (string)$name)
            : (string)$name;
        $html .= '<a class="kw-chip" href="' . icomplyMatrixH(url('/pages/keywords/' . $slug)) . '">'
            . icomplyMatrixH($label) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function icomplyRenderKeywordTownHtml(string $keywordSlug, string $areaName): string
{
    if (!function_exists('manchesterElectricalP0Html')) {
        require_once __DIR__ . '/manchester-electrical-p0.php';
    }
    $rich = manchesterElectricalP0Html('keyword', $keywordSlug, $areaName);
    if ($rich !== '') {
        return $rich;
    }
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
        ? 'Price on application after we confirm property type, access and scope.'
        : 'Free written quote once we confirm the scope.';

    if (!function_exists('icomplyGmTownBlurb')) {
        $gmBlurbs = SITE_ROOT . '/includes/gm-blurbs.php';
        if (is_file($gmBlurbs)) {
            require_once $gmBlurbs;
        }
    }
    $packed = function_exists('icomplyGmTownBlurb')
        ? icomplyGmTownBlurb($keywordSlug, $areaSlugVal, 'keyword')
        : null;
    $intro = is_array($packed) && $packed['blurb'] !== ''
        ? $packed['blurb']
        : (function_exists('seo_unique_intro')
            ? seo_unique_intro($svcName, $svcSlug, $areaName)
            : $kwName . ' in ' . $areaName . ' from iComply Property Services.');
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

    require_once SITE_ROOT . '/includes/ctr-meta.php';
    $title = icomplyCtrTitle($kwName, $areaName, $keywordSlug, $svcSlug);
    $desc = icomplyCtrDesc($kwName, $areaName, $keywordSlug, $svcSlug);
    $twin = icomplyKeywordTwinIntent((string)$keywordSlug, (string)$kwName, (string)$areaName);
    if ($twin) { $title = $twin['title']; $desc = $twin['desc']; }
    $canonical = url('/pages/keywords/' . $keywordSlug . '/' . $areaSlugVal);
    $robots = function_exists('icomplyRobotsMetaForPath')
        ? icomplyRobotsMetaForPath('/pages/keywords/' . $keywordSlug . '/' . $areaSlugVal)
        : 'index, follow';

    if (!function_exists('icomplyGmBriefHtml')) {
        require_once SITE_ROOT . '/includes/gm-enrichment.php';
    }
    if (!function_exists('icomplyImagePick')) {
        require_once SITE_ROOT . '/includes/image-pool.php';
    }
    $kwImagePath = icomplyImagePick('/pages/keywords/' . $keywordSlug . '/' . $areaSlugVal, icomplyPageImageKey('/pages/keywords/' . $keywordSlug . '/' . $areaSlugVal), 1)[0];
    $html = icomplyMatrixChromeStart($title, $desc, $canonical, $robots, $kwImagePath);
    if (in_array($svcSlug, ['cctv', 'access-control', 'intruder-alarm', 'intruder-alarms', 'door-entry', 'intercoms', 'security-systems'], true)) {
        // QM #156 item 13: security town pages use the full branded site header.
        require_once __DIR__ . '/site-nav.php';
        if (function_exists('icomplyMegaHeaderHtml')) {
            $html = preg_replace('#<header class="matrix-header">.*?</header>#s', icomplyMegaHeaderHtml(), $html, 1) ?? $html;
        }
    }
    $html .= '<section class="matrix-hero"><div class="matrix-wrap">'
        . '<p class="text-xs uppercase tracking-widest text-white/60">' . icomplyMatrixH($svcName) . ' · ' . icomplyMatrixH($areaName) . '</p>'
        . '<h1>' . icomplyMatrixH($kwName) . ' <span class="accent">in ' . icomplyMatrixH($areaName) . ($twin ? icomplyMatrixH($twin['h1_tail']) : '') . '</span></h1>'
        . '<p class="mt-4 text-white/80 max-w-2xl">' . icomplyGmBriefHtml($intro) . '</p>'
        . '<div class="mt-6 flex flex-wrap gap-3">'
        . '<a class="matrix-cta matrix-cta-accent" href="' . icomplyMatrixH($s['contact']) . '">Request a quote</a>'
        . '<a class="matrix-cta matrix-cta-light" href="' . icomplyMatrixH($s['phoneHref']) . '">' . icomplyMatrixH($s['phone']) . '</a>'
        . '</div>'
        . '<p class="mt-4 text-sm text-white/60">' . icomplyMatrixH($priceLine) . '</p>'
        . '</div></section>';

    $packedHeading = is_array($packed) && ($packed['h2'] ?? '') !== ''
        ? (string)$packed['h2']
        : 'About this ' . $areaName . ' page';
    $packedCta = is_array($packed) ? (string)($packed['cta'] ?? '') : '';
    $kwImage = function_exists('keywordImageUrl') ? keywordImageUrl($keywordSlug, $svcSlug) : '';
    $kwFaqs = [];
    if (!empty($meta['faq']) && is_array($meta['faq'])) {
        foreach ($meta['faq'] as $faq) {
            if (is_array($faq) && count($faq) >= 2) {
                $kwFaqs[] = [(string)$faq[0], (string)$faq[1]];
            }
        }
    }
    $kwEnrich = function_exists('icomplyGmEnrichmentHtml')
        ? icomplyGmEnrichmentHtml([
            'chrome' => 'matrix',
            'topic' => $kwName,
            'townName' => $areaName,
            'serviceSlug' => $svcSlug,
            'serviceName' => $svcName,
            'showGuide' => !is_array($packed) && str_word_count(strip_tags($body)) < 80,
            'localProse' => is_array($packed) ? (string)($packed['blurb'] ?? '') : '',
            'imageSrc' => $kwImage,
            'imageAlt' => $kwName . ' in ' . $areaName,
            'extraFaqs' => $kwFaqs,
            'keywordSlug' => $keywordSlug,
            'kind' => 'keyword',
        ])
        : '';
    if (!function_exists('icomplyQualityBarThinBlock')) {
        require_once SITE_ROOT . '/includes/quality-bar.php';
    }
    $kwGas = $svcSlug === 'gas-systems' || $svcSlug === 'heating' || !empty($meta['gas']);
    $kwQuality = icomplyQualityBarThinBlock(
        'keyword',
        $kwName,
        $svcName,
        $svcSlug,
        $areaName,
        is_array($packed) ? (string)($packed['blurb'] ?? '') : '',
        $kwGas,
        $keywordSlug
    );
    $html .= '<main class="matrix-wrap py-10 space-y-10">'
        . '<article class="matrix-card space-y-4">'
        . '<h2 class="text-2xl font-semibold">' . icomplyMatrixH($packedHeading) . '</h2>'
        . ($packedCta !== '' ? '<p class="text-zinc-700 leading-relaxed">' . icomplyMatrixH($packedCta) . '</p>' : '')
        . '<p class="text-zinc-700 leading-relaxed">' . icomplyMatrixH($body) . '</p>'
        . $kwQuality
        . icomplyTownFactsHtml(areaSlug($areaName), $areaName, $kwName, $svcSlug)
        . $kwEnrich;
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

    $kwNear = icomplyMatrixNearestAreas($s['areas'], $areaSlugVal, $svcSlug);
    if ($kwNear !== null && $kwNear !== []) {
        $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($kwName) . ' near ' . icomplyMatrixH($areaName) . '</h2>'
            . '<p class="text-sm text-zinc-600 mb-4">The ' . count($kwNear) . ' nearest of ' . count($s['areas']) . ' towns. <a class="text-[#ff6b00] font-semibold" href="' . icomplyMatrixH(url('/pages/keywords/' . $keywordSlug)) . '">All towns for ' . icomplyMatrixH($kwName) . '</a>.</p>'
            . icomplyMatrixAreaChips(url('/pages/keywords/' . $keywordSlug . '/'), $kwNear)
            . '</section>';
    } elseif ($kwNear === null) {
        $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($kwName) . ' in every area we cover</h2>'
            . '<p class="text-sm text-zinc-600 mb-4">' . count($s['areas']) . ' towns — full list, not a short subset.</p>'
            . icomplyMatrixAreaChips(url('/pages/keywords/' . $keywordSlug . '/') )
            . '</section>';
    }

    $html .= '<section><h2 class="text-2xl font-semibold mb-3">All ' . icomplyMatrixH($svcName) . ' service guides</h2>'
        . '<p class="text-sm text-zinc-600 mb-4">Every topic under this service, each with the same full town list.</p>'
        . icomplyMatrixKeywordChips($svcSlug)
        . '</section></main>';

    // QM #156 (CLOSE 8): town-specific depth inside <main> so keyword × town pages clear 800 words and differ.
    static $kwDepth = null;
    if ($kwDepth === null) {
        $kwDepth = (array)json_decode((string)@file_get_contents(dirname(__DIR__) . '/data/keyword-intent-depth.json'), true);
    }
    $deep = '';
    if (isset($kwDepth[$keywordSlug]['p'])) {
        $sub = static fn(string $t): string => icomplyMatrixH(str_replace('{town}', $areaName, $t));
        $deep = '<section class="kw-intent-depth"><h2 class="text-2xl font-semibold mb-3">' . $sub((string)$kwDepth[$keywordSlug]['h2']) . '</h2>';
        foreach ((array)$kwDepth[$keywordSlug]['p'] as $p) { $deep .= '<p class="mt-3 text-zinc-700 leading-relaxed">' . $sub((string)$p) . '</p>'; }
        $deep .= '</section>';
    }
    {
        // Thin keyword × town pages (under ~850 prose words) get the town-depth block: postcode districts,
        // wards, housing and council facts for this town (QM #156 under_800_words).
        $mainTxt = preg_replace('#<(script|style|nav|header|footer)\b.*?</\1>#s', ' ', substr($html, (int)strpos($html, '<main')));
        if (str_word_count(strip_tags((string)$mainTxt . ' ' . $deep)) < 850) {
            require_once __DIR__ . '/town-deep.php';
            $deep .= ($deep === '' && function_exists('icomplyTownDeepHtml')) ? icomplyTownDeepHtml($areaSlugVal, $areaName, $kwName, $svcSlug) : '';
            static $svcDepth = null;
            if ($svcDepth === null) {
                $svcDepth = (array)json_decode((string)@file_get_contents(dirname(__DIR__) . '/data/service-depth.json'), true);
            }
            if (isset($svcDepth[$svcSlug]) && is_array($svcDepth[$svcSlug])) {
                $sub2 = static fn(string $t): string => icomplyMatrixH(str_replace(['{town}', '{kw}'], [$areaName, mb_strtolower($kwName)], $t));
                $deep = '<section class="svc-depth"><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($kwName . ' in ' . $areaName . ': what to expect') . '</h2>'
                    . implode('', array_map(static fn($p) => '<p class="mt-3 text-zinc-700 leading-relaxed">' . $sub2((string)$p) . '</p>', $svcDepth[$svcSlug]))
                    . '</section>' . $deep;
            }
        }
    }
    if ($deep !== '') {
        $pos = strrpos($html, '</main>');
        if ($pos !== false) { $html = substr($html, 0, $pos) . $deep . substr($html, $pos); }
    }
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
    $allowed = function_exists('getAreasForService') ? getAreasForService($serviceSlug) : getAreas();
    if (!in_array($areaName, $allowed, true)) {
        return '';
    }
    $areaSlugVal = areaSlug($areaName);
    $poa = function_exists('isPoaService') && isPoaService($serviceSlug);
    $priceLine = $poa
        ? 'Free written quote before any work starts.'
        : 'Written quote after scope is agreed.';
    if ($serviceSlug === 'fire-risk-assessments' && function_exists('fraGuidePrice')) {
        $priceLine = (function_exists('fraFromLine') ? fraFromLine() : 'From £350 for a typical 6-bed HMO in the North West; other properties, nationwide and multi-site jobs are POA.');
    }
    if (!function_exists('icomplyGmTownBlurb')) {
        $gmBlurbs = SITE_ROOT . '/includes/gm-blurbs.php';
        if (is_file($gmBlurbs)) {
            require_once $gmBlurbs;
        }
    }
    $packed = function_exists('icomplyGmTownBlurb')
        ? icomplyGmTownBlurb($serviceSlug, $areaSlugVal, 'service')
        : null;
    $intro = is_array($packed) && $packed['blurb'] !== ''
        ? $packed['blurb']
        : (function_exists('seo_unique_intro')
            ? seo_unique_intro($svcName, $serviceSlug, $areaName)
            : $svcName . ' in ' . $areaName . '.');
    if (function_exists('waterAsbestosAreaIntro')) {
        $extra = waterAsbestosAreaIntro($serviceSlug, $areaName);
        if ($extra !== '') {
            $intro .= ' ' . $extra;
        }
    }
    $blurb = getServiceBlurb($serviceSlug);
    $standards = getServiceStandards($serviceSlug);
    require_once SITE_ROOT . '/includes/ctr-meta.php';
    $title = icomplyCtrTitle($svcName, $areaName, $serviceSlug, $serviceSlug);
    $desc = icomplyCtrDesc($svcName, $areaName, $serviceSlug, $serviceSlug);
    $canonical = url('/pages/' . $serviceSlug . '/' . $areaSlugVal);
    $robots = function_exists('icomplyRobotsMetaForPath')
        ? icomplyRobotsMetaForPath('/pages/' . $serviceSlug . '/' . $areaSlugVal)
        : 'index, follow';
    $ownsMainland = function_exists('serviceOwnsMainlandAreas') && serviceOwnsMainlandAreas($serviceSlug);
    $coverageLabel = $ownsMainland ? 'UK mainland' : 'North West';
    $areaPairs = $s['areas'];
    if ($ownsMainland && function_exists('getMainlandAreas')) {
        $areaPairs = [];
        foreach (getMainlandAreas() as $area) {
            $areaPairs[] = ['name' => (string)$area, 'slug' => areaSlug((string)$area)];
        }
    }
    $areaPairs = array_values(array_filter($areaPairs, static function ($a) use ($serviceSlug) {
        $slug = (string)($a['slug'] ?? '');
        return $slug !== '' && (!function_exists('icomplyTownPublishedForService') || icomplyTownPublishedForService($serviceSlug, $slug));
    }));
    $townHub = (function_exists('icomplyLocalTownSlug') && icomplyLocalTownSlug($areaSlugVal))
        ? url('/pages/areas/' . $areaSlugVal)
        : url('/pages/areas');

    if (!function_exists('icomplyGmBriefHtml')) {
        require_once SITE_ROOT . '/includes/gm-enrichment.php';
    }
    if (!function_exists('icomplyTier1ServiceArticle')) {
        $tier1File = SITE_ROOT . '/includes/tier1-copy.php';
        if (is_file($tier1File)) {
            require_once $tier1File;
        }
    }
    $tier1 = function_exists('icomplyTier1ServiceArticle') ? icomplyTier1ServiceArticle($serviceSlug, $areaName) : '';
    $svcImagePath = '/assets/images/services/' . $serviceSlug . '.jpg';
    $html = icomplyMatrixChromeStart($title, $desc, $canonical, $robots, $svcImagePath);
    if (in_array($serviceSlug, ['cctv', 'access-control', 'intruder-alarm', 'intruder-alarms', 'door-entry', 'intercoms', 'security-systems'], true)) {
        // QM #156 item 13: security service x town pages use the full branded site header too.
        require_once __DIR__ . '/site-nav.php';
        if (function_exists('icomplyMegaHeaderHtml')) {
            $html = preg_replace('#<header class="matrix-header">.*?</header>#s', icomplyMegaHeaderHtml(), $html, 1) ?? $html;
        }
    }
    $html .= '<section class="matrix-hero"><div class="matrix-wrap">'
        . '<img src="' . icomplyMatrixH(function_exists('url') ? url($svcImagePath) : $svcImagePath) . '" alt="' . icomplyMatrixH($svcName . ' in ' . $areaName) . '" width="1200" height="800"' . $heroCredit . '>'
        . ($serviceSlug === 'demolition' ? '<p class="image-credit">Image credit: Tomwsulcer (CC0)</p>' : '')
        . '<p class="text-xs uppercase tracking-widest text-white/60">' . icomplyMatrixH($areaName) . ' · ' . icomplyMatrixH($coverageLabel) . '</p>'
        . '<h1>' . icomplyMatrixH($svcName) . ' <span class="accent">in ' . icomplyMatrixH($areaName) . '</span></h1>'
        . '<p class="mt-4 text-white/80 max-w-2xl">' . icomplyGmBriefHtml($intro) . '</p>'
        . '<div class="mt-6 flex flex-wrap gap-3">'
        . '<a class="matrix-cta matrix-cta-accent" href="' . icomplyMatrixH($s['contact']) . '">Request a quote</a>'
        . '<a class="matrix-cta matrix-cta-light" href="' . icomplyMatrixH($s['phoneHref']) . '">' . icomplyMatrixH($s['phone']) . '</a>'
        . '</div>'
        . '<p class="mt-4 text-sm text-white/60">' . icomplyMatrixH(
            ($priceLine !== '' && !preg_match('/written quote/i', $standards))
                ? ($standards !== '' ? $standards . ' · ' . $priceLine : $priceLine)
                : $standards
        ) . '</p>'
        . '</div></section>';

    $packedHeading = is_array($packed) && ($packed['h2'] ?? '') !== ''
        ? (string)$packed['h2']
        : 'What we do in ' . $areaName;
    $packedCta = is_array($packed) ? (string)($packed['cta'] ?? '') : '';
    $svcImage = function_exists('serviceImageUrl') ? serviceImageUrl($serviceSlug) : '';
    $svcEnrich = function_exists('icomplyGmEnrichmentHtml')
        ? icomplyGmEnrichmentHtml([
            'chrome' => 'matrix',
            'topic' => $svcName,
            'townName' => $areaName,
            'serviceSlug' => $serviceSlug,
            'serviceName' => $svcName,
            'tier1' => $tier1,
            'showGuide' => $tier1 === '' && !is_array($packed),
            'localProse' => is_array($packed) ? (string)($packed['blurb'] ?? '') : '',
            'imageSrc' => $svcImage,
            'imageAlt' => $svcName . ' in ' . $areaName,
        ])
        : '';
    if (!function_exists('icomplyFireFamilyDepthHtml')) {
        require_once SITE_ROOT . '/includes/fire-family-depth.php';
    }
    if (!function_exists('icomplyQualityBarThinBlock')) {
        require_once SITE_ROOT . '/includes/quality-bar.php';
    }
    $svcGas = $serviceSlug === 'gas-systems' || $serviceSlug === 'heating';
    $svcQuality = icomplyQualityBarThinBlock(
        'service',
        $svcName,
        $svcName,
        $serviceSlug,
        $areaName,
        is_array($packed) ? (string)($packed['blurb'] ?? '') : '',
        $svcGas,
        $serviceSlug
    );
    $html .= '<main class="matrix-wrap py-10 space-y-10">'
        . '<article class="matrix-card space-y-4">'
        . '<h2 class="text-2xl font-semibold">' . icomplyMatrixH($packedHeading) . '</h2>'
        . $svcEnrich
        . $svcQuality
        . ($ownsMainland ? icomplyFireFamilyDepthHtml($serviceSlug, $svcName, $areaName) : '')
        . icomplyTownFactsHtml(areaSlug($areaName), $areaName, $svcName, $serviceSlug)
        . '<p class="text-zinc-700 leading-relaxed">' . icomplyMatrixH($blurb) . '</p>'
        . ($packedCta !== '' ? '<p class="text-zinc-700 leading-relaxed">' . icomplyMatrixH($packedCta) . '</p>' : '')
        . '<p class="text-sm">Service hub: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH(url('/pages/services/' . $serviceSlug)) . '">' . icomplyMatrixH($svcName) . '</a>'
        . ' · Town: <a class="text-[#ff6b00] font-semibold" href="'
        . icomplyMatrixH($townHub) . '">' . icomplyMatrixH($areaName) . '</a></p>'
        . ($ownsMainland
            ? '<p class="text-zinc-700 leading-relaxed">We cover fire protection across mainland Britain: England, Wales and mainland Scotland, all from our Stockport base.</p>'
            : '')
        . '</article>';

    // CLOSE-Q8: towns with few other local services (mainland-only rows) take
    // more nearest-town links so every lean page still clears 80 internals.
    $otherSvc = icomplyMatrixOtherServicesInTown($serviceSlug, $areaName);
    $otherCount = substr_count($otherSvc, 'class="area-chip"');
    $smokeTown = icomplyMatrixSmokeCoSameTown($serviceSlug, $areaName);
    // Smoke & CO towns with few live same-town checks take extra nearest-town links instead.
    $smokeShort = $serviceSlug === 'smoke-co-alarms' ? max(0, 10 - substr_count($smokeTown, 'class="area-chip"')) : 0;
    $svNear = icomplyMatrixNearestAreas($areaPairs, areaSlug($areaName), $serviceSlug, ($otherCount < 24 ? 52 + (24 - $otherCount) : 40) + $smokeShort);
    if ($svNear !== null && $svNear !== []) {
        $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($svcName) . ' near ' . icomplyMatrixH($areaName) . '</h2>'
            . '<p class="text-sm text-zinc-600 mb-4">The ' . count($svNear) . ' nearest of ' . count($areaPairs) . ' towns. <a class="text-[#ff6b00] font-semibold" href="' . icomplyMatrixH(url('/pages/services/' . $serviceSlug)) . '">' . icomplyMatrixH($svcName) . ' hub</a>.</p>'
            . icomplyMatrixAreaChips(url('/pages/' . $serviceSlug . '/'), $svNear)
            . '</section>';
    } elseif ($svNear === null) {
        $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($svcName) . ' in every area</h2>'
            . '<p class="text-sm text-zinc-600 mb-4">' . count($areaPairs) . ' towns.</p>'
            . icomplyMatrixAreaChips(url('/pages/' . $serviceSlug . '/'), $areaPairs)
            . '</section>';
    }

    if ($svNear !== null) {
        $html .= $otherSvc;
        $html .= $smokeTown;
        $kwAll = icomplyMatrixShared()['kwByService'][$serviceSlug] ?? [];
        $kwKeys = array_keys($kwAll);
        $off = $kwKeys ? crc32(areaSlug($areaName)) % count($kwKeys) : 0;
        $pickKw = array_slice(array_merge(array_slice($kwKeys, $off), array_slice($kwKeys, 0, $off)), 0, 16);
        $html .= '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH($svcName) . ' guides</h2><div class="chip-cloud">';
        foreach ($pickKw as $k) {
            $html .= '<a class="kw-chip" href="' . icomplyMatrixH(url('/pages/keywords/' . $k)) . '">' . icomplyMatrixH($kwAll[$k]) . '</a>';
        }
        $html .= '</div><p class="text-sm mt-2"><a class="text-[#ff6b00] font-semibold" href="' . icomplyMatrixH(url('/pages/keywords')) . '">All service guides</a></p></section></main>';
    } else {
        $html .= '<section><h2 class="text-2xl font-semibold mb-3">All keywords for this service</h2>'
            . icomplyMatrixKeywordChips($serviceSlug)
            . '</section></main>';
    }

    $foot = $ownsMainland
        ? 'Fire alarms: UK mainland (England, Wales and mainland Scotland), scheduled from Stockport.'
        : '';
    $html .= icomplyMatrixChromeEnd($foot);
    return $html;
}


/**
 * Smoke & CO alarm town pages: same-town landlord checks that sit alongside the alarm
 * duty (CO alarm, gas safety record, EICR, fire risk assessment, HMO fire safety).
 * Only links URLs that stay live for that town (no 301 to a hub).
 */
function icomplyMatrixSmokeCoSameTown(string $serviceSlug, string $areaName): string
{
    if ($serviceSlug !== 'smoke-co-alarms') {
        return '';
    }
    if (!function_exists('icomplyNonGmMatrixRedirect')) {
        require_once SITE_ROOT . '/includes/gm-crawl.php';
    }
    $town = areaSlug($areaName);
    $targets = [
        ['keywords', 'smoke-alarm-installation', 'Smoke alarm installation'],
        ['keywords', 'carbon-monoxide-alarm-installation', 'Carbon monoxide alarm installation'],
        ['keywords', 'carbon-monoxide-flue-check', 'Carbon monoxide flue check'],
        ['keywords', 'smoke-detector-installation-commercial', 'Commercial smoke detectors'],
        ['keywords', 'fire-alarm-installer', 'Fire alarm installer'],
        ['jobs', 'landlord-compliance', 'Landlord compliance'],
        ['jobs', 'landlord-gas-safety', 'Landlord gas safety'],
        ['jobs', 'gas-safety-cp12', 'Gas safety record (CP12)'],
        ['jobs', 'eicr', 'EICR'],
        ['jobs', 'fire-risk-assessment', 'Fire risk assessment'],
        ['jobs', 'hmo-fire-safety', 'HMO fire safety'],
        ['jobs', 'hmo-compliance', 'HMO compliance'],
        ['jobs', 'fire-alarm-call-out', 'Fire alarm call-out'],
    ];
    $chips = '';
    foreach ($targets as [$kind, $slug, $label]) {
        $path = '/pages/' . $kind . '/' . $slug . '/' . $town;
        if (icomplyNonGmMatrixRedirect($path) !== null) {
            continue;
        }
        $chips .= '<a class="area-chip" href="' . icomplyMatrixH(url($path)) . '">' . icomplyMatrixH($label . ' in ' . $areaName) . '</a>';
    }
    if ($chips === '') {
        return '';
    }
    return '<section><h2 class="text-2xl font-semibold mb-3">' . icomplyMatrixH('Landlord safety checks in ' . $areaName) . '</h2>'
        . '<div class="chip-cloud">' . $chips . '</div></section>';
}
