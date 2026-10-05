<?php
/**
 * Nationwide 3-line P0 keywords (AOV, barriers, fire).
 * Hubs are /pages/keywords/{slug}. Town pages are P0 × UK TOP 5000,
 * rendered here and by the Netlify edge function. Not 380k static HTML files.
 * The 10 GM-core places missing from TOP 5000 stay on the dual area allowlist.
 */
declare(strict_types=1);

const ICOMPLY_NATIONWIDE_NAP = '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE';

function icomplyNationwide3lineFamily(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = SITE_ROOT . '/data/nationwide-3line-p0.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

function icomplyNationwide3lineKeywords(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = SITE_ROOT . '/data/nationwide-3line-p0-keywords.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

/** @return list<string> */
function icomplyNationwide3lineP0Slugs(): array
{
    $slugs = [];
    foreach (icomplyNationwide3lineFamily()['hubs'] ?? [] as $hub) {
        if (is_array($hub) && !empty($hub['slug'])) {
            $slugs[] = (string)$hub['slug'];
        }
    }
    return $slugs;
}

function icomplyNationwide3lineIsP0(string $slug): bool
{
    static $set = null;
    if ($set === null) {
        $set = array_fill_keys(icomplyNationwide3lineP0Slugs(), true);
    }
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    return isset($set[$slug]);
}

function icomplyNationwide3lineHub(string $slug): ?array
{
    static $by = null;
    if ($by === null) {
        $by = [];
        foreach (icomplyNationwide3lineFamily()['hubs'] ?? [] as $hub) {
            if (is_array($hub) && !empty($hub['slug'])) {
                $by[(string)$hub['slug']] = $hub;
            }
        }
    }
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    return $by[$slug] ?? null;
}

/** @return list<string> */
function icomplyNationwide3lineImages(string $slug): array
{
    $hub = icomplyNationwide3lineHub($slug);
    $images = is_array($hub) ? ($hub['images'] ?? []) : [];
    $out = [];
    foreach ($images as $image) {
        if (is_string($image) && $image !== '') {
            $out[] = $image;
        }
    }
    return array_slice($out, 0, 3);
}

function icomplyTop5000Dataset(): array
{
    $data = function_exists('loadJsonData') ? loadJsonData('uk-top5000-towns', []) : [];
    return is_array($data) ? $data : [];
}

/** @return list<array<string,mixed>> */
function icomplyTop5000Towns(): array
{
    static $towns = null;
    if ($towns !== null) {
        return $towns;
    }
    $towns = [];
    foreach (icomplyTop5000Dataset()['towns'] ?? [] as $row) {
        if (!is_array($row) || empty($row['slug']) || empty($row['name'])) {
            continue;
        }
        $towns[] = $row;
    }
    return $towns;
}

function icomplyTop5000TownBySlug(string $slug): ?array
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (icomplyTop5000Towns() as $town) {
            $index[(string)$town['slug']] = $town;
        }
    }
    $slug = function_exists('areaSlug') ? areaSlug($slug) : strtolower($slug);
    return $index[$slug] ?? null;
}

function icomplyNationwide3lineNationwidePath(string $path): bool
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if (!preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return false;
    }
    return icomplyNationwide3lineIsP0($m[1]) && icomplyTop5000TownBySlug($m[2]) !== null;
}

function icomplyNationwide3lineIndexablePath(string $path): bool
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if (!preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return false;
    }
    if (!icomplyNationwide3lineIsP0($m[1])) {
        return false;
    }
    if (icomplyTop5000TownBySlug($m[2]) !== null) {
        return true;
    }
    return function_exists('icomplyCrawlTownSlug') && icomplyCrawlTownSlug($m[2]);
}

/**
 * @param array<string,mixed> $keywords
 * @return array<string,mixed>
 */
function icomplyNationwide3lineApplyKeywords(array $keywords): array
{
    foreach (icomplyNationwide3lineKeywords() as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $slug = function_exists('keywordSlug') ? keywordSlug((string)$slug) : strtolower((string)$slug);
        if ($slug === '') {
            continue;
        }
        $row = [
            'name' => (string)($meta['name'] ?? $slug),
            'service' => (string)($meta['service'] ?? 'fire-alarms'),
            'related' => function_exists('keywordSlug')
                ? keywordSlug((string)($meta['related'] ?? $slug))
                : (string)($meta['related'] ?? $slug),
        ];
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords', 'seo_title', 'h1'] as $field) {
            if (!empty($meta[$field]) && is_string($meta[$field])) {
                $row[$field] = $meta[$field];
            }
        }
        if (!empty($meta['focus_points']) && is_array($meta['focus_points'])) {
            $row['focus_points'] = $meta['focus_points'];
        }
        if (!empty($meta['faq']) && is_array($meta['faq'])) {
            $row['faq'] = $meta['faq'];
        }
        unset($row['hub_only']);
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * @param array<string,mixed> $gmPlaces
 * @return list<string>
 */
function icomplyNationwide3lineExtraTownPaths(array $gmPlaces = []): array
{
    $paths = [];
    foreach (icomplyNationwide3lineP0Slugs() as $keyword) {
        foreach (icomplyTop5000Towns() as $town) {
            $slug = (string)($town['slug'] ?? '');
            if ($slug === '' || isset($gmPlaces[$slug])) {
                continue;
            }
            $paths[] = '/pages/keywords/' . $keyword . '/' . $slug;
        }
    }
    return $paths;
}

function icomplyNationwide3lineParentPaths(string $service): array
{
    $map = [
        'aov-air-handling' => ['/pages/services/aov-air-handling', '/pages/aov'],
        'barriers' => ['/pages/services/barriers', '/pages/barriers'],
        'fire-alarms' => ['/pages/services/fire-alarms'],
        'emergency-lighting' => ['/pages/services/emergency-lighting', '/pages/services/fire-alarms'],
        'fire-doors' => ['/pages/services/fire-doors', '/pages/services/fire-alarms'],
        'fire-extinguishers' => ['/pages/services/fire-extinguishers', '/pages/services/fire-alarms'],
        'fire-risk-assessments' => ['/pages/services/fire-risk-assessments', '/pages/services/fire-alarms'],
        'fire-stopping' => ['/pages/services/fire-stopping', '/pages/services/fire-alarms'],
        'fire-suppression' => ['/pages/services/fire-suppression', '/pages/services/fire-alarms'],
        'sprinkler-systems' => ['/pages/services/sprinkler-systems', '/pages/services/fire-alarms'],
    ];
    return $map[$service] ?? ['/pages/services/fire-alarms'];
}

function icomplyNationwide3lineTitle(string $name, ?string $town = null): string
{
    $lead = $town ? ($name . ' in ' . $town) : $name;
    $full = $lead . ' | iComply Property Services';
    if (strlen($full) <= 60) {
        return $full;
    }
    return $lead . ' — iComply';
}

function icomplyNationwide3lineFitMeta(string $text): string
{
    $text = trim((string)preg_replace('/\s+/', ' ', $text));
    // An em dash is 3 bytes. Swap it for a hyphen only when that is what exceeds 160.
    if (strlen($text) > 160 && str_contains($text, '—')) {
        $text = preg_replace('/—/u', '-', $text, 1) ?? $text;
    }
    if (strlen($text) > 160) {
        $cut = substr($text, 0, 160);
        $space = strrpos($cut, ' ');
        if ($space !== false && $space > 40) {
            $cut = substr($cut, 0, $space);
        }
        $text = rtrim($cut, " .,;:—-");
        $extra = ' Request a quote — POA.';
        if (!str_ends_with($text, 'POA') && strlen($text) + strlen($extra) <= 160) {
            $text .= $extra;
        }
    }
    $pads = [' Scope is written first.', ' No catalogue price.', ' Call 07517806082.', ' Scope first.', ' POA only.'];
    foreach ($pads as $pad) {
        if (strlen($text) >= 140 && mb_strlen($text) >= 140) {
            break;
        }
        if (strlen($text) + strlen($pad) <= 160) {
            $text .= $pad;
        }
    }
    return $text;
}

function icomplyNationwide3lineMeta(string $name, string $place): string
{
    $text = $name . ' in ' . $place . ' for landlords, agents and commercial sites. Arranged from Stockport. Request a quote — POA.';
    if (strlen($text) > 160) {
        $text = $name . ' in ' . $place . ' for landlords and agents. Request a quote — POA.';
    }
    if (strlen($text) > 160) {
        $text = $name . ' in ' . $place . '. Scoped from Stockport. Request a quote — POA.';
    }
    return icomplyNationwide3lineFitMeta($text);
}

/**
 * @param array<string,mixed> $gmPlaces
 */
function icomplyNationwide3lineRenderTown(string $keywordSlug, string $townSlug): void
{
    $keywordSlug = function_exists('keywordSlug') ? keywordSlug($keywordSlug) : strtolower($keywordSlug);
    $townSlug = function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug);
    $pack = icomplyNationwide3lineKeywords()[$keywordSlug] ?? null;
    $town = icomplyTop5000TownBySlug($townSlug);
    $gm = $town === null && function_exists('icomplyCrawlTownSlug') && icomplyCrawlTownSlug($townSlug);
    if (!is_array($pack) || !icomplyNationwide3lineIsP0($keywordSlug) || ($town === null && !$gm)) {
        http_response_code(404);
        echo 'Keyword town page not found';
        return;
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
            'lat' => 53.48,
            'lng' => -2.24,
        ];
    }
    $name = (string)$pack['name'];
    $service = (string)($pack['service'] ?? 'fire-alarms');
    $place = (string)$town['name'];
    $title = icomplyNationwide3lineTitle($name, $place);
    $description = icomplyNationwide3lineMeta($name, $place);
    $canonical = 'https://icomplypropertyservices.co.uk/pages/keywords/' . $keywordSlug . '/' . $townSlug;
    $images = icomplyNationwide3lineImages($keywordSlug);
    $ogImage = 'https://icomplypropertyservices.co.uk' . ($images[0] ?? '/assets/images/services/fire-alarms.jpg');
    $h = static function (string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    };
    $local = $place . ' is in ' . (string)($town['county'] ?? '') . ', ' . (string)($town['region'] ?? '')
        . ', ' . (string)($town['nation'] ?? 'England')
        . '. Published population on this town list is ' . number_format((int)($town['population'] ?? 0))
        . '. ' . $name . ' in ' . $place . ' is quoted from ' . ICOMPLY_NATIONWIDE_NAP
        . '. The quote is price on application after the scope is written down. Travel from the Stockport workshop is part of that quote, not a hidden extra.';
    $paras = preg_split("/\R\R+/", trim((string)($pack['body'] ?? ''))) ?: [];
    array_unshift($paras, $local);
    $bodyHtml = '';
    foreach ($paras as $para) {
        $para = trim((string)$para);
        if ($para === '') {
            continue;
        }
        $bodyHtml .= '<p>' . $h($para) . '</p>';
    }
    $faqHtml = '';
    $faqSchema = [];
    $faqs = $pack['faq'] ?? [];
    if (is_array($faqs)) {
        $faqs[] = [
            'How do I ask for ' . $name . ' in ' . $place . '?',
            'Phone 07517806082 or use the contact form. Name the building in ' . $place . '. The reply is price on application. Workshop: ' . ICOMPLY_NATIONWIDE_NAP . '.',
        ];
        foreach ($faqs as $faq) {
            if (!is_array($faq) || count($faq) < 2) {
                continue;
            }
            $faqHtml .= '<h3>' . $h((string)$faq[0]) . '</h3><p>' . $h((string)$faq[1]) . '</p>';
            $faqSchema[] = [
                '@type' => 'Question',
                'name' => (string)$faq[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string)$faq[1]],
            ];
        }
    }
    $links = '';
    foreach (icomplyNationwide3lineParentPaths($service) as $href) {
        $links .= '<li><a href="' . $h($href) . '">' . $h(trim(str_replace('-', ' ', basename($href)))) . '</a></li>';
    }
    $links .= '<li><a href="' . $h('/pages/keywords/' . $keywordSlug) . '">' . $h($name . ' guide') . '</a></li>';
    $links .= '<li><a href="/contact">Request a quote</a></li>';
    $links .= '<li><a href="/pages/areas/stockport">Stockport area hub</a></li>';
    if (function_exists('icomplyCrawlTownSlug') && icomplyCrawlTownSlug($townSlug)) {
        $links .= '<li><a href="' . $h('/pages/areas/' . $townSlug) . '">' . $h('Property services in ' . $place) . '</a></li>';
    }
    foreach (icomplyNationwide3lineP0Slugs() as $other) {
        if ($other === $keywordSlug) {
            continue;
        }
        $label = (string)(icomplyNationwide3lineKeywords()[$other]['name'] ?? $other);
        $links .= '<li><a href="' . $h('/pages/keywords/' . $other) . '">' . $h($label) . '</a></li>';
    }
    $gallery = '';
    foreach ($images as $index => $src) {
        $alt = $name . ' in ' . $place . ' — photograph ' . ($index + 1);
        $gallery .= '<figure><img src="' . $h($src) . '" alt="' . $h($alt) . '" width="1200" height="630"></figure>';
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
                'provider' => [
                    '@type' => 'LocalBusiness',
                    'name' => 'iComply Property Services',
                    'telephone' => '07517806082',
                    'address' => ICOMPLY_NATIONWIDE_NAP,
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => $faqSchema,
            ],
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
        . '<article id="local-copy">' . $bodyHtml . '</article>'
        . '<section><h2>' . $h($name . ' FAQ') . '</h2>' . $faqHtml . '</section>'
        . '<section><h2>Related guides</h2><ul>' . $links . '</ul></section>'
        . '<p>Workshop: ' . $h(ICOMPLY_NATIONWIDE_NAP) . '. Phone 07517806082. Price on application.</p>'
        . '</main></body></html>';
}
