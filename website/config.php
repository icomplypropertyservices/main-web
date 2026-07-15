<?php
/**
 * Icomply Property Services — site config + helpers.
 * Data lives in data/*.json; optional overrides in config.local.php.
 */
define('SITE_ROOT', __DIR__);

// Defaults (overridable via config.local.php / env / Vercel)
$siteDefaults = [
    'SITE_NAME' => 'Icomply Property Services',
    'SITE_URL' => 'http://localhost/icomply',
    'PHONE' => '07517806082',
    'EMAIL' => 'info@icomplypropertyservices.co.uk',
    // Lead form notify address (empty = use EMAIL)
    'LEADS_NOTIFY_EMAIL' => '',
    'ADDRESS' => '17 Woodlands Park Road, Offerton, Stockport, SK2 5DE',
    'WHATSAPP' => '447517806082',
    'ADMIN_USER' => 'admin',
    // Never commit real passwords — set ADMIN_PASS in config.local.php
    'ADMIN_PASS' => '',
    'GA_MEASUREMENT_ID' => '',
    'AW_CONVERSION_ID' => '',
    // Google Search Console HTML-tag verification (content= value only)
    'GOOGLE_SITE_VERIFICATION' => '',
    // Bing/Yandex IndexNow key (optional; enables /{key}.txt + ping)
    'INDEXNOW_KEY' => '',
    // Secret for /bin/cron-seo.php?key=… (set in config.local / Vercel env)
    'SEO_CRON_KEY' => '',
    // Shopify Storefront / Buy Button (set in config.local.php)
    // Domain example: your-store.myshopify.com  |  Store URL: https://your-store.myshopify.com
    'SHOPIFY_DOMAIN' => '',
    'SHOPIFY_STORE_URL' => '',
    'SHOPIFY_STOREFRONT_TOKEN' => '',
    'SHOPIFY_COLLECTION_ID' => '',
    'SHOPIFY_ENABLED' => false,
    // Social profiles (leave empty to hide; WhatsApp always available via WHATSAPP)
    'SOCIAL_FACEBOOK' => 'https://www.facebook.com/icomplypropertyservices',
    'SOCIAL_INSTAGRAM' => 'https://www.instagram.com/icomplypropertyservices',
    'SOCIAL_LINKEDIN' => 'https://www.linkedin.com/company/icomply-property-services',
    'SOCIAL_TWITTER' => 'https://twitter.com/icomplyps',
    'SOCIAL_YOUTUBE' => '',
    'SOCIAL_TIKTOK' => '',
    'SOCIAL_GOOGLE' => 'https://g.page/icomply-property-services',
];

// Environment overrides (Vercel Project → Settings → Environment Variables)
$envMap = [
    'SITE_URL' => 'SITE_URL',
    'PHONE' => 'PHONE',
    'EMAIL' => 'EMAIL',
    'WHATSAPP' => 'WHATSAPP',
    'ADMIN_USER' => 'ADMIN_USER',
    'ADMIN_PASS' => 'ADMIN_PASS',
    'GA_MEASUREMENT_ID' => 'GA_MEASUREMENT_ID',
    'AW_CONVERSION_ID' => 'AW_CONVERSION_ID',
    'GOOGLE_SITE_VERIFICATION' => 'GOOGLE_SITE_VERIFICATION',
    'INDEXNOW_KEY' => 'INDEXNOW_KEY',
    'SEO_CRON_KEY' => 'SEO_CRON_KEY',
    'SHOPIFY_DOMAIN' => 'SHOPIFY_DOMAIN',
    'SHOPIFY_STORE_URL' => 'SHOPIFY_STORE_URL',
    'SHOPIFY_STOREFRONT_TOKEN' => 'SHOPIFY_STOREFRONT_TOKEN',
];
foreach ($envMap as $const => $envName) {
    $v = getenv($envName);
    if ($v === false || $v === '') {
        $v = $_ENV[$envName] ?? $_SERVER[$envName] ?? '';
    }
    if (is_string($v) && $v !== '') {
        $siteDefaults[$const] = $v;
    }
}

// On Vercel / production hosts, prefer public HTTPS origin (never localhost canonicals)
$isVercel = (string)(getenv('VERCEL') ?: ($_ENV['VERCEL'] ?? '')) !== '';
$host = (string)($_SERVER['HTTP_HOST'] ?? '');
$host = preg_replace('/:\d+$/', '', $host);
if ($isVercel || preg_match('/icomplypropertyservices\.co\.uk$/i', $host)) {
    if ($host === '' || str_contains($host, 'localhost')) {
        $host = 'www.icomplypropertyservices.co.uk';
    }
    // Canonical host: www
    if (strcasecmp($host, 'icomplypropertyservices.co.uk') === 0) {
        $host = 'www.icomplypropertyservices.co.uk';
    }
    if (empty($siteDefaults['SITE_URL']) || str_contains((string)$siteDefaults['SITE_URL'], 'localhost')) {
        $siteDefaults['SITE_URL'] = 'https://' . $host;
    }
}

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = include $localFile;
    if (is_array($local)) {
        $siteDefaults = array_merge($siteDefaults, $local);
    }
}

// Production/Vercel: never keep localhost SITE_URL after local overrides
$isVercelFinal = (string)(getenv('VERCEL') ?: ($_ENV['VERCEL'] ?? '')) !== '';
$hostFinal = preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
if ($isVercelFinal || preg_match('/icomplypropertyservices\.co\.uk$/i', (string)$hostFinal)) {
    if ($hostFinal === '' || str_contains($hostFinal, 'localhost')) {
        $hostFinal = 'www.icomplypropertyservices.co.uk';
    }
    if (strcasecmp($hostFinal, 'icomplypropertyservices.co.uk') === 0) {
        $hostFinal = 'www.icomplypropertyservices.co.uk';
    }
    if (str_contains((string)$siteDefaults['SITE_URL'], 'localhost')) {
        $siteDefaults['SITE_URL'] = 'https://' . $hostFinal;
    }
}
// Explicit env always wins for SITE_URL when set (deploy / prod sitemap builds)
$forcedSite = getenv('SITE_URL');
if (is_string($forcedSite) && $forcedSite !== '' && !str_contains($forcedSite, 'localhost')) {
    $siteDefaults['SITE_URL'] = rtrim($forcedSite, '/');
}

foreach ($siteDefaults as $key => $value) {
    if (!defined($key)) {
        define($key, $value);
    }
}

/** @return array decoded JSON file or $default */
function loadJsonData(string $name, $default = []) {
    static $cache = [];
    if ($name === '__clear__') {
        $cache = [];
        return [];
    }
    if (array_key_exists($name, $cache)) {
        return $cache[$name];
    }
    $file = SITE_ROOT . '/data/' . $name . '.json';
    if (!is_file($file)) {
        return $cache[$name] = $default;
    }
    $data = json_decode((string)file_get_contents($file), true);
    return $cache[$name] = (is_array($data) ? $data : $default);
}

function saveJsonData(string $name, $data): void {
    $dir = SITE_ROOT . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents(
        $dir . '/' . $name . '.json',
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );
    loadJsonData('__clear__');
}

/**
 * Public absolute URL — extensionless (no .php).
 * Accepts paths with or without .php; always emits clean URLs.
 */
function url(string $path = '/'): string {
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    // Drop query for normalization; re-append later if present
    $query = '';
    if (str_contains($path, '?')) {
        [$path, $q] = explode('?', $path, 2);
        $query = '?' . $q;
    }
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    // /foo/index → /foo
    $path = preg_replace('#/index$#i', '', $path) ?? $path;
    if ($path === '' || $path === '/') {
        return rtrim(SITE_URL, '/') . $query;
    }
    return rtrim(SITE_URL, '/') . $path . $query;
}

/** Alias used by older templates / seo helpers */
function site_url(string $path = ''): string {
    return url('/' . ltrim($path, '/'));
}

/** Core services (data/services.json) + any admin-added customs */
function getServices(): array {
    $base = loadJsonData('services', []);
    $custom = loadJsonData('services-custom', []);
    return array_merge($base, $custom);
}

/**
 * Grouped service catalogue for nav / hubs.
 * @return array<string, array{label:string,blurb?:string,services:list<string>}>
 */
function getServiceCategories(): array {
    $cats = loadJsonData('service-categories', []);
    if ($cats) {
        return $cats;
    }
    // Fallback: single bucket if categories file missing
    return [
        'all' => [
            'label' => 'All services',
            'blurb' => 'Full service catalogue',
            'services' => array_keys(getServices()),
        ],
    ];
}

/** Services for a category key (only those present in getServices()). */
function getServicesInCategory(string $categoryKey): array {
    $all = getServices();
    $cats = getServiceCategories();
    $slugs = $cats[$categoryKey]['services'] ?? [];
    $out = [];
    foreach ($slugs as $slug) {
        if (isset($all[$slug])) {
            $out[$slug] = $all[$slug];
        }
    }
    return $out;
}

/** @deprecated use getServices() — kept for templates that still read $services */
function loadServices(): array {
    return loadJsonData('services-custom', []);
}

function saveServices(array $custom): void {
    saveJsonData('services-custom', $custom);
}

function getAreas(): array {
    return loadJsonData('areas', []);
}

/**
 * Canonical slug: lowercase, non-alnum → hyphen, collapse hyphens.
 * "Ashton-under-Lyne" → ashton-under-lyne
 * "Cheadle Hulme" → cheadle-hulme
 */
function areaSlug(string $area): string {
    $s = strtolower(trim($area));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string)$s, '-');
}

function keywordSlug($phrase): string {
    return areaSlug((string)$phrase);
}

function keywordDisplayName($slugOrName): string {
    $name = str_replace('-', ' ', (string)$slugOrName);
    $name = ucwords($name);
    $acronyms = [
        'Eicr' => 'EICR', 'Pat' => 'PAT', 'Ev' => 'EV', 'Aov' => 'AOV', 'Ahu' => 'AHU',
        'Cctv' => 'CCTV', 'Ip' => 'IP', 'Hd' => 'HD', 'Bs' => 'BS', 'Htm' => 'HTM',
        'Niceic' => 'NICEIC', 'Lpg' => 'LPG', 'Ptz' => 'PTZ', 'Cp44' => 'CP44',
        'Cp12' => 'CP12', 'Fra' => 'FRA', 'Cdm' => 'CDM', 'Hmo' => 'HMO', 'Epc' => 'EPC',
        'Fd30' => 'FD30', 'Fd60' => 'FD60', 'Anpr' => 'ANPR', 'Nvr' => 'NVR', 'Dvr' => 'DVR',
        'Ppm' => 'PPM', 'Gsm' => 'GSM', 'Epdm' => 'EPDM', 'Pir' => 'PIR',
    ];
    foreach ($acronyms as $from => $to) {
        $name = preg_replace('/\b' . preg_quote($from, '/') . '\b/', $to, $name);
    }
    return $name;
}

function getMajorKeywords(): array {
    $kw = loadJsonData('keywords', []);
    $normalized = [];
    foreach ($kw as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $slug = keywordSlug($slug);
        $row = [
            'name' => $meta['name'] ?? keywordDisplayName($slug),
            'service' => $meta['service'] ?? 'electrical',
            'related' => keywordSlug($meta['related'] ?? $slug),
        ];
        // Unique SEO content (from enrich / agent merge)
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords'] as $field) {
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
        $normalized[$slug] = $row;
    }
    return $normalized;
}

function getSeoKeywords(string $service, string $area = ''): string {
    $mfr = loadJsonData('manufacturers', []);
    $base = $mfr['seo_keywords'][$service] ?? $service;
    return $area !== '' ? "{$base} {$area}, {$area} electrician, {$area} fire safety" : $base;
}

/** Canonical service blurbs / standards (data/service-meta.json) */
function getServiceMeta(string $slug = ''): array {
    $all = loadJsonData('service-meta', []);
    if ($slug === '') {
        return $all;
    }
    return $all[$slug] ?? [
        'blurb' => 'Installation, maintenance, testing and certification.',
        'short' => 'Installation, maintenance and certification',
        'standards' => 'British Standards · manufacturer guidance · full certification',
    ];
}

function getServiceBlurb(string $slug, bool $short = false): string {
    $m = getServiceMeta($slug);
    return $short ? (string)($m['short'] ?? $m['blurb'] ?? '') : (string)($m['blurb'] ?? '');
}

function getServiceStandards(string $slug): string {
    return (string)(getServiceMeta($slug)['standards'] ?? '');
}

function getManufacturers(string $serviceSlug): array {
    $mfr = loadJsonData('manufacturers', []);
    return $mfr['by_service'][$serviceSlug] ?? ['Industry Standard Equipment'];
}

/** Full manufacturer catalog keyed by slug */
function getManufacturerCatalog(): array {
    $mfr = loadJsonData('manufacturers', []);
    $catalog = $mfr['catalog'] ?? [];
    if ($catalog) {
        return $catalog;
    }
    // Fallback: build minimal catalog from by_service names
    $built = [];
    foreach ($mfr['by_service'] ?? [] as $service => $names) {
        foreach ($names as $name) {
            $slug = areaSlug((string)$name);
            if (!isset($built[$slug])) {
                $built[$slug] = [
                    'name' => $name,
                    'slug' => $slug,
                    'services' => [$service],
                    'blurb' => "Icomply installs and services {$name} equipment across the North West.",
                    'seo_title' => "{$name} Products & Service",
                    'seo_desc' => "{$name} installation, servicing and trade products from Icomply Property Services.",
                    'seo_keywords' => $name,
                    'products' => [],
                    'featured' => false,
                ];
            } elseif (!in_array($service, $built[$slug]['services'], true)) {
                $built[$slug]['services'][] = $service;
            }
        }
    }
    return $built;
}

function getManufacturerBySlug(string $slug): ?array {
    $slug = areaSlug($slug);
    $catalog = getManufacturerCatalog();
    return $catalog[$slug] ?? null;
}

function manufacturerSlugFromName(string $name): string {
    return areaSlug($name);
}

function manufacturerImageSlug(string $name): string {
    return areaSlug($name);
}

function getManufacturerImageSlugs(string $serviceSlug): array {
    $mfr = loadJsonData('manufacturers', []);
    if (!empty($mfr['images_by_service'][$serviceSlug])) {
        return $mfr['images_by_service'][$serviceSlug];
    }
    $slugs = [];
    foreach (getManufacturers($serviceSlug) as $name) {
        $slugs[] = manufacturerSlugFromName($name);
        if (count($slugs) >= 6) {
            break;
        }
    }
    return $slugs ?: ['kentec'];
}

/**
 * Linked brand pill buttons for a service (every name → manufacturer page).
 */
function manufacturerTagsHtml(string $serviceSlug): string {
    $html = '';
    foreach (getManufacturers($serviceSlug) as $m) {
        $slug = manufacturerSlugFromName($m);
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($m, ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" '
            . 'class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border-2 border-zinc-200 rounded-full text-sm text-black font-semibold hover:border-[#ff6b00] hover:text-[#ff6b00] hover:shadow-sm transition" '
            . 'title="View ' . $label . ' products and service page">'
            . $label
            . '<span class="text-[#ff6b00]" aria-hidden="true">→</span></a>';
    }
    // Always offer full directory
    $allHref = htmlspecialchars(url('/pages/manufacturers/index.php'), ENT_QUOTES, 'UTF-8');
    $html .= '<a href="' . $allHref . '" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#0a2540] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition">All brands →</a>';
    return $html;
}

/**
 * Linked brand image cards for a service (all brands when available).
 * @param int $limit 0 = all brands for service
 */
function manufacturerImagesHtml(string $serviceSlug, int $limit = 0): string {
    $slugs = getManufacturerImageSlugs($serviceSlug);
    // Prefer full by_service list so every mentioned brand has a card
    $fromNames = [];
    foreach (getManufacturers($serviceSlug) as $m) {
        $fromNames[] = manufacturerSlugFromName($m);
    }
    if ($fromNames) {
        $slugs = $fromNames;
    }
    if ($limit > 0) {
        $slugs = array_slice($slugs, 0, $limit);
    }
    $catalog = getManufacturerCatalog();
    $html = '';
    $fallback = htmlspecialchars(url('/assets/images/services/' . $serviceSlug . '.jpg'), ENT_QUOTES, 'UTF-8');
    foreach ($slugs as $slug) {
        $slug = preg_replace('/[^a-z0-9\-]/', '', (string)$slug);
        if ($slug === '') {
            continue;
        }
        $entry = $catalog[$slug] ?? null;
        $label = htmlspecialchars($entry['name'] ?? ucwords(str_replace('-', ' ', $slug)), ENT_QUOTES, 'UTF-8');
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $src = htmlspecialchars(url('/assets/images/manufacturers/' . $slug . '.jpg'), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="bg-white border-2 border-zinc-200 rounded-2xl overflow-hidden hover:border-[#ff6b00] hover:shadow-md transition block group">'
            . '<img src="' . $src . '" alt="' . $label . ' products and service — Icomply" '
            . 'class="w-full h-28 object-cover group-hover:scale-105 transition duration-300" loading="lazy" '
            . 'onerror="this.src=\'' . $fallback . '\'">'
            . '<div class="p-3 text-sm text-black text-center font-semibold">' . $label
            . ' <span class="text-[#ff6b00]">→</span></div>'
            . '</a>';
    }
    return $html;
}

/**
 * Turn brand names into links when they match the manufacturer catalog.
 * Safe for plain text paragraphs (escapes HTML first, then injects links).
 */
function linkManufacturerNamesInText(string $text): string {
    $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $catalog = getManufacturerCatalog();
    // Longest names first so "Advanced Electronics" wins over shorter fragments
    $names = [];
    foreach ($catalog as $slug => $entry) {
        $n = (string)($entry['name'] ?? '');
        if ($n !== '') {
            $names[$n] = $slug;
        }
    }
    uksort($names, fn($a, $b) => strlen($b) - strlen($a));
    foreach ($names as $name => $slug) {
        $escaped = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $link = '<a href="' . $href . '" class="font-semibold text-[#ff6b00] hover:underline">' . $escaped . '</a>';
        $safe = preg_replace('/\b' . preg_quote($escaped, '/') . '\b/u', $link, $safe);
    }
    return $safe;
}

function getKeywordImages(string $serviceSlug): array {
    $mfr = loadJsonData('manufacturers', []);
    return $mfr['keyword_images'][$serviceSlug] ?? [$serviceSlug, $serviceSlug, $serviceSlug];
}

/** Resolve area display name from slug (or return title-cased slug). */
function areaFromSlug(string $slug): ?string {
    $slug = areaSlug($slug);
    foreach (getAreas() as $area) {
        if (areaSlug($area) === $slug) {
            return $area;
        }
    }
    return null;
}

// Back-compat globals used by some templates/includes
$services = getServices();
$areas = getAreas();
