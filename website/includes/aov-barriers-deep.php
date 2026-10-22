<?php
/**
 * AOV + Barriers DEEP P0 pack (179 keyword hubs).
 *
 * SEO lock: icomply-ops/seo/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md (corrected 23:55 —
 * barrier restriction theme is manual barriers + WIDTH / HEIGHT; wind theme withdrawn).
 * Data: data/aov-barriers-deep-p0.json, built by bin/build-aov-barriers-deep-pack.py.
 *
 * Hubs are /pages/keywords/{slug} (nationwide). Town pages are hub × UK TOP 5000 towns
 * (GM core places outside that list stay on the GM path); manufacturer × job heads are
 * Greater Manchester core 60 only. Rendered on demand here and by the Netlify edge
 * function (netlify/lib/aov-barriers-deep.js) — no per-town HTML files.
 *
 * DEEP is a superset priority wave over the nationwide 3-line P0: where a slug is in
 * both, the DEEP copy, images and town renderer win (one canonical URL per slug).
 * POA only. No attendance-time promises. Dimensions are site-measured or the
 * manufacturer's published range — never invented ratings.
 */
declare(strict_types=1);

if (!defined('ICOMPLY_DEEP_NAP')) {
    define('ICOMPLY_DEEP_NAP', '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE');
}

/** @return array<string,mixed> */
function icomplyAovBarriersDeepPack(): array
{
    static $pack = null;
    if ($pack !== null) {
        return $pack;
    }
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $file = $root . '/data/aov-barriers-deep-p0.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $pack = is_array($decoded) ? $decoded : [];
    return $pack;
}

/** @return array<string,array<string,mixed>> slug => hub row */
function icomplyAovBarriersDeepHubs(): array
{
    static $by = null;
    if ($by !== null) {
        return $by;
    }
    $by = [];
    foreach ((array)(icomplyAovBarriersDeepPack()['hubs'] ?? []) as $hub) {
        if (is_array($hub) && !empty($hub['slug'])) {
            $by[(string)$hub['slug']] = $hub;
        }
    }
    return $by;
}

/** @return list<string> */
function icomplyAovBarriersDeepSlugs(): array
{
    return array_keys(icomplyAovBarriersDeepHubs());
}

/** @return array<string,array<string,mixed>> */
function icomplyAovBarriersDeepKeywords(): array
{
    $rows = icomplyAovBarriersDeepPack()['keywords'] ?? [];
    return is_array($rows) ? $rows : [];
}

function icomplyAovBarriersDeepNormalise(string $slug): string
{
    return function_exists('keywordSlug') ? keywordSlug($slug) : strtolower(trim($slug));
}

function icomplyAovBarriersDeepIsP0(string $slug): bool
{
    return isset(icomplyAovBarriersDeepHubs()[icomplyAovBarriersDeepNormalise($slug)]);
}

/** Manufacturer × job heads keep town pages on the Greater Manchester core 60 only. */
function icomplyAovBarriersDeepGmOnly(string $slug): bool
{
    $hub = icomplyAovBarriersDeepHubs()[icomplyAovBarriersDeepNormalise($slug)] ?? null;
    return is_array($hub) && ($hub['geo'] ?? '') === 'gm60';
}

/** @return list<string> */
function icomplyAovBarriersDeepImages(string $slug): array
{
    $hub = icomplyAovBarriersDeepHubs()[icomplyAovBarriersDeepNormalise($slug)] ?? null;
    $out = [];
    foreach ((array)($hub['images'] ?? []) as $image) {
        if (is_string($image) && $image !== '') {
            $out[] = $image;
        }
    }
    return array_slice($out, 0, 3);
}

function icomplyAovBarriersDeepGmTown(string $townSlug): bool
{
    return function_exists('icomplyCrawlTownSlug') && icomplyCrawlTownSlug($townSlug);
}

/** Hub content-image figure (same PE slots as icomplyQualityBarImages) using the DEEP photographs. */
function icomplyAovBarriersDeepImageFigure(string $slug, string $altPrefix): string
{
    $h = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $slots = ['q2-image-hero' => '', 'q2-image-work' => ' — on site', 'q2-image-context' => ' — site context'];
    $images = icomplyAovBarriersDeepImages($slug);
    $html = '<figure class="quality-bar-images" data-pe-slot="q2-hub-images">';
    $i = 0;
    foreach ($slots as $slot => $suffix) {
        $src = (string)($images[$i++] ?? '');
        if ($src === '') {
            continue;
        }
        $html .= '<img data-pe-slot="' . $slot . '" src="' . $h(function_exists('url') ? url($src) : $src) . '" alt="'
            . $h($altPrefix . $suffix) . '" width="1200" height="630" loading="lazy">';
    }
    return $html . '</figure>';
}

/** True when {keyword}/{town} is a DEEP town page that should render (200, indexable). */
function icomplyAovBarriersDeepKeepsTown(string $keywordSlug, string $townSlug): bool
{
    if (!icomplyAovBarriersDeepIsP0($keywordSlug)) {
        return false;
    }
    $townSlug = function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug);
    if (icomplyAovBarriersDeepGmOnly($keywordSlug)) {
        return icomplyAovBarriersDeepGmTown($townSlug);
    }
    return (function_exists('icomplyTop5000TownBySlug') && icomplyTop5000TownBySlug($townSlug) !== null)
        || icomplyAovBarriersDeepGmTown($townSlug);
}

/** TOP 5000 (non-GM path) town page for a DEEP hub — mirrors icomplyNationwide3lineNationwidePath(). */
function icomplyAovBarriersDeepNationwideTown(string $keywordSlug, string $townSlug): bool
{
    return icomplyAovBarriersDeepIsP0($keywordSlug)
        && !icomplyAovBarriersDeepGmOnly($keywordSlug)
        && function_exists('icomplyTop5000TownBySlug')
        && icomplyTop5000TownBySlug($townSlug) !== null;
}

/**
 * Town rows linked from a DEEP hub (TOP 5000, or GM core 60 for manufacturer heads).
 *
 * @return list<array<string,mixed>>
 */
function icomplyAovBarriersDeepTownRows(string $keywordSlug): array
{
    if (!icomplyAovBarriersDeepGmOnly($keywordSlug)) {
        return function_exists('icomplyTop5000Towns') ? icomplyTop5000Towns() : [];
    }
    $rows = [];
    $names = function_exists('icomplyCrawlTownNames') ? icomplyCrawlTownNames() : [];
    foreach ($names as $name) {
        $name = (string)$name;
        $rows[] = ['name' => $name, 'slug' => function_exists('areaSlug') ? areaSlug($name) : strtolower($name)];
    }
    return $rows;
}

/**
 * Keyword catalogue overlay (DEEP wins over earlier packs for its 179 slugs).
 *
 * @param array<string,mixed> $keywords
 * @return array<string,mixed>
 */
function icomplyAovBarriersDeepApplyKeywords(array $keywords): array
{
    foreach (icomplyAovBarriersDeepKeywords() as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $slug = icomplyAovBarriersDeepNormalise((string)$slug);
        if ($slug === '') {
            continue;
        }
        $row = [
            'name' => (string)($meta['name'] ?? $slug),
            'service' => (string)($meta['service'] ?? 'barriers'),
            'related' => icomplyAovBarriersDeepNormalise((string)($meta['related'] ?? $slug)),
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
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * Extra matrix sitemap paths: DEEP hub × TOP 5000 (skipping matrix GM places, which
 * the GM keyword×town loop already writes). Manufacturer heads add nothing here.
 *
 * @param array<string,mixed> $gmPlaces
 * @return list<string>
 */
function icomplyAovBarriersDeepExtraTownPaths(array $gmPlaces = []): array
{
    $paths = [];
    $towns = function_exists('icomplyTop5000Towns') ? icomplyTop5000Towns() : [];
    foreach (icomplyAovBarriersDeepSlugs() as $keyword) {
        if (icomplyAovBarriersDeepGmOnly($keyword)) {
            continue;
        }
        foreach ($towns as $town) {
            $slug = (string)($town['slug'] ?? '');
            if ($slug === '' || isset($gmPlaces[$slug])) {
                continue;
            }
            $paths[] = '/pages/keywords/' . $keyword . '/' . $slug;
        }
    }
    return $paths;
}

/**
 * SEO ruling 2026-10-06: the DEEP keyword slug wins the canonical. Job hubs that
 * overlap a DEEP hub (main's /pages/jobs/car-park-barrier and the #114 W1a job hubs)
 * 301 to the matching keyword hub. Map: data/aov-barriers-deep-job-redirects.json.
 *
 * @return array<string,string> job slug => DEEP keyword slug
 */
function icomplyAovBarriersDeepJobRedirects(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $file = $root . '/data/aov-barriers-deep-job-redirects.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    foreach ((array)($decoded['jobs'] ?? []) as $job => $row) {
        $job = icomplyAovBarriersDeepNormalise((string)$job);
        $keyword = icomplyAovBarriersDeepNormalise((string)(is_array($row) ? ($row['keyword'] ?? '') : $row));
        if ($job !== '' && $keyword !== '' && icomplyAovBarriersDeepIsP0($keyword)) {
            $map[$job] = $keyword;
        }
    }
    return $map;
}

/** DEEP keyword slug that a job slug 301s to, or null when the job hub stays. */
function icomplyAovBarriersDeepJobTarget(string $jobSlug): ?string
{
    return icomplyAovBarriersDeepJobRedirects()[icomplyAovBarriersDeepNormalise($jobSlug)] ?? null;
}

/** True when /pages/keywords/{keyword}/{town} is a live 200 page (DEEP town rules or dual-ring matrix). */
function icomplyAovBarriersDeepKeywordTownExists(string $keywordSlug, string $townSlug): bool
{
    if (icomplyAovBarriersDeepKeepsTown($keywordSlug, $townSlug)) {
        return true;
    }
    if (icomplyAovBarriersDeepGmOnly($keywordSlug)) {
        return false;
    }
    if (!function_exists('icomplyLocalTownSlug')) {
        $gm = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/includes/gm-crawl.php';
        if (is_file($gm)) {
            require_once $gm;
        }
    }
    return function_exists('icomplyLocalTownSlug') && icomplyLocalTownSlug($townSlug);
}

/**
 * 301 target for an overlapping job URL: /pages/jobs/{job} → /pages/keywords/{kw};
 * /pages/jobs/{job}/{town} → /pages/keywords/{kw}/{town} where that town page exists,
 * else the keyword hub. Null for every other path.
 */
function icomplyAovBarriersDeepJobRedirectPath(string $path): ?string
{
    $path = rtrim($path, '/');
    if (!preg_match('#^/pages/jobs/([a-z0-9\-]+)(?:/([a-z0-9\-]+))?$#', $path, $m)) {
        return null;
    }
    $keyword = icomplyAovBarriersDeepJobTarget($m[1]);
    if ($keyword === null) {
        return null;
    }
    $town = (string)($m[2] ?? '');
    if ($town !== '' && icomplyAovBarriersDeepKeywordTownExists($keyword, $town)) {
        return '/pages/keywords/' . $keyword . '/' . $town;
    }
    return '/pages/keywords/' . $keyword;
}

/**
 * Netlify _redirects lines for the overlapping job hubs (hub + job×town, forced 301).
 * Skips a hub line that the #114 W1a helper already writes with the same target, so a
 * merged tree never carries two rules for one URL. The edge function issues the
 * job×town 301 first (it also knows which keyword×town pages exist); the :town line
 * here is the static fallback.
 */
function icomplyAovBarriersDeepJobRedirectLines(): string
{
    $w1aFile = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/includes/nationwide-p0-jobs.php';
    if (!function_exists('nationwideP0KeywordRedirect') && is_file($w1aFile)) {
        require_once $w1aFile;
    }
    $lines = "# AOV + Barriers DEEP wins the canonical: overlapping job hubs → DEEP keyword hub (SEO ruling 2026-10-06).\n";
    foreach (icomplyAovBarriersDeepJobRedirects() as $job => $keyword) {
        $target = '/pages/keywords/' . $keyword;
        $w1a = function_exists('nationwideP0KeywordRedirect') ? nationwideP0KeywordRedirect($job) : null;
        if ($w1a !== $target) {
            $lines .= "/pages/jobs/{$job}    {$target}    301!\n";
            $lines .= "/pages/jobs/{$job}/   {$target}    301!\n";
        }
        $lines .= "/pages/jobs/{$job}/:town    {$target}/:town    301!\n";
        $lines .= "/pages/jobs/{$job}/:town/   {$target}/:town    301!\n";
    }
    return $lines . "\n";
}

function icomplyAovBarriersDeepParentPaths(string $service): array
{
    return $service === 'aov-air-handling'
        ? ['/pages/services/aov-air-handling', '/pages/aov']
        : ['/pages/services/barriers', '/pages/barriers'];
}

function icomplyAovBarriersDeepTitle(string $name, ?string $town = null): string
{
    $lead = $town ? ($name . ' in ' . $town) : $name;
    $full = $lead . ' | iComply Property Services';
    return strlen($full) <= 60 ? $full : $lead . ' — iComply';
}

function icomplyAovBarriersDeepTownMeta(string $name, string $place, string $line): string
{
    $who = $line === 'aov' ? 'landlords, agents and building managers' : 'car parks, estates and private sites';
    $text = $name . ' in ' . $place . ' for ' . $who . '. Measured, scoped from Stockport. Request a quote — POA.';
    if (strlen($text) > 160) {
        $text = $name . ' in ' . $place . '. Measured and scoped from Stockport. Request a quote — POA.';
    }
    if (strlen($text) > 160) {
        $text = $name . ' in ' . $place . '. Scoped from Stockport. Quote POA.';
    }
    return function_exists('icomplyNationwide3lineFitMeta') ? icomplyNationwide3lineFitMeta($text) : $text;
}

/** @return array<string,mixed>|null */
function icomplyAovBarriersDeepTownRow(string $keywordSlug, string $townSlug): ?array
{
    $town = function_exists('icomplyTop5000TownBySlug') ? icomplyTop5000TownBySlug($townSlug) : null;
    $gm = icomplyAovBarriersDeepGmTown($townSlug);
    if (icomplyAovBarriersDeepGmOnly($keywordSlug)) {
        if (!$gm) {
            return null;
        }
    } elseif ($town === null && !$gm) {
        return null;
    }
    if ($town === null) {
        $townName = function_exists('areaFromSlug') ? (string)(areaFromSlug($townSlug) ?: $townSlug) : $townSlug;
        $town = [
            'name' => $townName,
            'slug' => $townSlug,
            'county' => 'Greater Manchester',
            'region' => 'North West',
            'nation' => 'England',
            'population' => 0,
        ];
    }
    return $town;
}

/** Local paragraph for a DEEP town page (shared wording with the edge twin). */
function icomplyAovBarriersDeepLocal(string $name, array $town, string $line): string
{
    $place = (string)$town['name'];
    $pop = (int)($town['population'] ?? 0);
    $where = $place . ' is in ' . (string)($town['county'] ?? '') . ', ' . (string)($town['region'] ?? '') . ', ' . (string)($town['nation'] ?? 'England') . '.';
    $popLine = $pop > 0 ? ' Published population on the town list used for this page is ' . number_format($pop) . '.' : '';
    $subject = $line === 'aov'
        ? 'Buildings in ' . $place . ' are surveyed on their own drawings, fire strategy and access, not on a national script.'
        : 'Entrances in ' . $place . ' are measured on site — clear width, headroom, ground and turning space — before anything is specified.';
    return $where . $popLine . ' ' . $name . ' in ' . $place . ' is quoted from ' . ICOMPLY_DEEP_NAP . '. ' . $subject
        . ' The quote is price on application after the scope is written down, and travel from the Stockport workshop is part of that quote. No attendance time is promised on this page.';
}

/**
 * Sibling DEEP hubs linked from a town page: the related hub first, then the same line
 * in lock order starting after this slug (stable and page-specific; edge twin matches).
 *
 * @return list<string>
 */
function icomplyAovBarriersDeepSiblings(string $keywordSlug, string $related = '', int $limit = 24): array
{
    $hubs = icomplyAovBarriersDeepHubs();
    $line = (string)($hubs[$keywordSlug]['line'] ?? '');
    $same = [];
    foreach ($hubs as $slug => $hub) {
        if (($hub['line'] ?? '') === $line) {
            $same[] = (string)$slug;
        }
    }
    $at = array_search($keywordSlug, $same, true);
    $at = $at === false ? 0 : (int)$at;
    $ordered = array_merge(array_slice($same, $at + 1), array_slice($same, 0, $at));
    if ($related !== '' && $related !== $keywordSlug && isset($hubs[$related])) {
        $ordered = array_merge([$related], array_values(array_diff($ordered, [$related])));
    }
    return array_slice($ordered, 0, $limit);
}

function icomplyAovBarriersDeepRenderTown(string $keywordSlug, string $townSlug): void
{
    $keywordSlug = icomplyAovBarriersDeepNormalise($keywordSlug);
    $townSlug = function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug);
    $pack = icomplyAovBarriersDeepKeywords()[$keywordSlug] ?? null;
    $hub = icomplyAovBarriersDeepHubs()[$keywordSlug] ?? null;
    $town = is_array($pack) ? icomplyAovBarriersDeepTownRow($keywordSlug, $townSlug) : null;
    if (!is_array($pack) || !is_array($hub) || $town === null) {
        if (!headers_sent()) {
            http_response_code(404);
        }
        echo 'Keyword town page not found';
        return;
    }
    $h = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    $name = (string)$pack['name'];
    $line = (string)($hub['line'] ?? 'barrier');
    $service = (string)($pack['service'] ?? 'barriers');
    $place = (string)$town['name'];
    $title = icomplyAovBarriersDeepTitle($name, $place);
    $description = icomplyAovBarriersDeepTownMeta($name, $place, $line);
    $canonical = 'https://icomplypropertyservices.co.uk/pages/keywords/' . $keywordSlug . '/' . $townSlug;
    $images = icomplyAovBarriersDeepImages($keywordSlug);
    $ogImage = 'https://icomplypropertyservices.co.uk' . ($images[0] ?? '/assets/images/services/' . $service . '.jpg');

    $paras = preg_split("/\R\R+/", trim((string)($pack['body'] ?? ''))) ?: [];
    array_unshift($paras, icomplyAovBarriersDeepLocal($name, $town, $line), (string)($pack['intro'] ?? ''));
    $bodyHtml = '';
    foreach ($paras as $para) {
        $para = trim((string)$para);
        if ($para !== '') {
            $bodyHtml .= '<p>' . $h($para) . '</p>';
        }
    }
    $focusHtml = '';
    foreach ((array)($pack['focus_points'] ?? []) as $point) {
        $focusHtml .= '<li>' . $h((string)$point) . '</li>';
    }
    $faqs = (array)($pack['faq'] ?? []);
    $faqs[] = [
        'Do you cover ' . $place . ' for ' . $name . '?',
        'Yes. ' . $place . ' is quoted from the Stockport workshop at ' . ICOMPLY_DEEP_NAP . '. Phone 07517806082 or use the contact form and name the site in ' . $place . '. The reply is price on application.',
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
    foreach (icomplyAovBarriersDeepParentPaths($service) as $href) {
        $links .= '<li><a href="' . $h($href) . '">' . $h(trim(str_replace('-', ' ', basename($href)))) . '</a></li>';
    }
    $links .= '<li><a href="' . $h('/pages/keywords/' . $keywordSlug) . '">' . $h($name . ' guide') . '</a></li>';
    $links .= '<li><a href="/contact">Request a quote</a></li>';
    $links .= '<li><a href="/pages/areas/stockport">Stockport area hub</a></li>';
    if (icomplyAovBarriersDeepGmTown($townSlug)) {
        $links .= '<li><a href="' . $h('/pages/areas/' . $townSlug) . '">' . $h('Property services in ' . $place) . '</a></li>';
    }
    $keywords = icomplyAovBarriersDeepKeywords();
    $related = (string)($pack['related'] ?? '');
    $siblings = icomplyAovBarriersDeepSiblings($keywordSlug, $related);
    foreach ($siblings as $other) {
        $otherTown = icomplyAovBarriersDeepKeepsTown($other, $townSlug);
        $href = '/pages/keywords/' . $other . ($otherTown ? '/' . $townSlug : '');
        $label = (string)($keywords[$other]['name'] ?? $other) . ($otherTown ? ' in ' . $place : '');
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
                'provider' => ['@type' => 'LocalBusiness', 'name' => 'iComply Property Services', 'telephone' => '07517806082', 'address' => ICOMPLY_DEEP_NAP],
            ],
            ['@type' => 'FAQPage', 'mainEntity' => $faqSchema],
        ],
    ];
    echo '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
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
        . '<p>Workshop: ' . $h(ICOMPLY_DEEP_NAP) . '. Phone 07517806082. Price on application.</p>'
        . '</main></body></html>';
}
