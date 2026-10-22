<?php
/**
 * Visible breadcrumbs, FAQ schema, a real-photo slot, content-image credits
 * and a local essay built from service facts. Idempotent via data-quality-floor="1".
 */
declare(strict_types=1);

require_once __DIR__ . '/image-pool.php';

function icomplyQualityFloorH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** @return array<string, mixed> */
function icomplyQualityFloorCopy(): array
{
    static $doc = null;
    if ($doc !== null) {
        return $doc;
    }
    $file = dirname(__DIR__) . '/data/homeowner-copy.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $doc = is_array($decoded) ? $decoded : [];
    return $doc;
}

function icomplyQualityFloorTitleTown(string $slug): string
{
    $small = ['under' => true, 'upon' => true, 'le' => true, 'in' => true, 'the' => true, 'of' => true, 'on' => true, 'de' => true, 'y' => true];
    $parts = explode('-', $slug !== '' ? $slug : 'stockport');
    $out = [];
    foreach ($parts as $i => $word) {
        if ($word === 'st') {
            $out[] = 'St';
            continue;
        }
        if ($i > 0 && isset($small[$word])) {
            $out[] = $word;
            continue;
        }
        $out[] = ucfirst($word);
    }
    return implode(' ', $out);
}

function icomplyQualityFloorServiceKey(string $path): string
{
    $copy = icomplyQualityFloorCopy();
    $services = is_array($copy['services'] ?? null) ? $copy['services'] : [];
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    if (($parts[0] ?? '') !== 'pages') {
        return 'default';
    }
    $head = $parts[1] ?? '';
    if ($head === 'services') {
        return isset($services[$parts[2] ?? '']) ? (string)$parts[2] : 'default';
    }
    if ($head === 'aov') {
        return 'aov';
    }
    if ($head === 'barriers') {
        return 'barriers';
    }
    $brands = [
        'apt-controls' => true, 'atg-access' => true, 'avon-barrier' => true, 'dea' => true,
        'designa' => true, 'eagle' => true, 'frontier-pitts' => true, 'heald' => true,
        'hysecurity' => true, 'key-automation' => true, 'life' => true, 'newgate' => true, 'skidata' => true,
    ];
    if ($head === 'manufacturers' && isset($brands[$parts[2] ?? ''])) {
        return 'barriers';
    }
    return isset($services[$head]) ? $head : 'default';
}

function icomplyQualityFloorPlace(string $path): string
{
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    if (($parts[0] ?? '') !== 'pages') {
        return 'Stockport';
    }
    $head = $parts[1] ?? '';
    if (in_array($head, ['keywords', 'jobs', 'manufacturers'], true) && isset($parts[3])) {
        return icomplyQualityFloorTitleTown($parts[3]);
    }
    if ($head === 'areas' && isset($parts[2])) {
        return icomplyQualityFloorTitleTown($parts[2]);
    }
    if ($head === 'services') {
        return 'Stockport';
    }
    if (isset($parts[2]) && !in_array($head, ['keywords', 'jobs', 'manufacturers', 'areas', 'resources', 'packages'], true)) {
        return icomplyQualityFloorTitleTown($parts[2]);
    }
    return 'Stockport';
}

function icomplyQualityFloorSubject(string $subject, string $key, string $path = ''): string
{
    $services = function_exists('getServices') ? getServices() : [];
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $slug = ($parts[1] ?? '') === 'services' ? ($parts[2] ?? '') : ($parts[1] ?? '');
    if ($slug !== '' && isset($services[$slug]) && is_string($services[$slug]) && $services[$slug] !== '') {
        return $services[$slug];
    }
    if ($key !== '' && $key !== 'default' && isset($services[$key]) && is_string($services[$key]) && $services[$key] !== '') {
        return $services[$key];
    }
    $cleaned = trim((string)preg_replace('/\s+in\s+.+$/i', '', $subject));
    $cleaned = trim((string)preg_replace('/\.\s*POA\.?.*$/i', '', $cleaned));
    $cleaned = trim((string)preg_replace('/\btrade supply only\b/i', '', $cleaned));
    $cleaned = trim((string)preg_replace('/\bproducts\s*(?:&(?:amp;)?|and)\s*service\b/i', '', $cleaned));
    $leaf = strtolower((string)($parts[count($parts) - 1] ?? ''));
    if ($leaf !== '') {
        $cleaned = trim((string)preg_replace('/\s*\(' . preg_quote($leaf, '/') . '\)/i', '', $cleaned));
    }
    $cleaned = trim((string)preg_replace('/\s+/', ' ', $cleaned));
    $first = trim(explode('.', $cleaned)[0] ?? '');
    if ($first !== '') {
        return $first;
    }
    return $cleaned !== '' ? $cleaned : 'the work';
}

function icomplyQualityFloorOffice(): string
{
    return '17 Woodlands Park Road, Offerton, Stockport SK2 5DE';
}

function icomplyQualityFloorGasManufacturer(string $slug): bool
{
    static $slugs = [
        'alpha' => true, 'ariston' => true, 'baxi' => true, 'ferroli' => true, 'firebird' => true,
        'glow-worm' => true, 'grant' => true, 'ideal' => true, 'intergas' => true, 'main-heating' => true,
        'navien' => true, 'potterton' => true, 'ravenheat' => true, 'remeha' => true, 'vaillant' => true,
        'viessmann' => true, 'vokera' => true, 'worcester-bosch' => true,
    ];
    return isset($slugs[$slug]);
}

function icomplyQualityFloorIsGas(string $path, string $subject = ''): bool
{
    $path = rtrim($path, '/') ?: '/';
    if (in_array($path, ['/pages/packages/compliance-bundle', '/pages/packages/landlord-pack', '/pages/landlords'], true)) {
        return true;
    }
    if (preg_match('#^/pages/manufacturers/([a-z0-9-]+)$#', $path, $brand) && icomplyQualityFloorGasManufacturer($brand[1])) {
        return true;
    }
    if (preg_match('#/(?:gas-systems|heating)(?:/|$)#', $path)) {
        return true;
    }
    if (preg_match('/heating/i', $path . ' ' . $subject)) {
        return true;
    }
    return (bool)preg_match('/\bgas\b|cp12|boiler/i', $path . ' ' . $subject);
}

function icomplyQualityFloorFill(string $text, string $place, string $subject, int &$budget): string
{
    unset($budget);
    $town = $place !== '' ? $place : 'Stockport';
    $job = $subject !== '' ? $subject : 'the work';
    return str_replace(['{subject}', '{place}'], [$job, $town], $text);
}

function icomplyQualityFloorPhotoTemplate(string $path): string
{
    static $doc = null;
    if ($doc === null) {
        $file = dirname(__DIR__) . '/data/photo-ask.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $doc = is_array($decoded) ? $decoded : [];
    }
    $generic = (string)($doc['generic'] ?? 'Useful photographs before {subject} in {place} show the part that has failed and the access.');
    $family = icomplyQualityFloorFamily($path);
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $head = ($parts[0] ?? '') === 'pages' ? ($parts[1] ?? '') : ($parts[0] ?? '');
    if (
        str_starts_with($family, 'hub:')
        || str_starts_with($family, 'directory:')
        || $head === 'areas'
        || $head === 'landlords'
        || str_contains($path, '/landlords')
    ) {
        return $generic;
    }
    $families = is_array($doc['families'] ?? null) ? $doc['families'] : [];
    if (isset($families[$family]) && is_string($families[$family]) && $families[$family] !== '') {
        return $families[$family];
    }
    $services = function_exists('getServices') ? getServices() : [];
    $label = '';
    if (isset($services[$family]) && is_string($services[$family]) && $services[$family] !== '') {
        $label = $services[$family];
    } elseif ($family !== '') {
        $label = ucwords(str_replace('-', ' ', $family));
    }
    $fallback = (string)($doc['fallback'] ?? '');
    if ($label === '' || $fallback === '') {
        return $generic;
    }
    return str_replace('{trade}', $label, $fallback);
}

function icomplyQualityFloorAbsoluteImageUrl(string $url): string
{
    $url = trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if (preg_match('#^https://#i', $url)) {
        return $url;
    }
    if (preg_match('#^http://#i', $url)) {
        return 'https://' . substr($url, 7);
    }
    if (str_starts_with($url, '//')) {
        return 'https:' . $url;
    }
    $path = $url;
    if (preg_match('~^https?://[^/]+(/[^?#]*)~i', $url, $m)) {
        $path = $m[1];
    }
    if ($path === '' || ($path[0] ?? '') !== '/') {
        $path = '/' . ltrim($path, '/');
    }
    return 'https://icomplypropertyservices.co.uk' . $path;
}

function icomplyQualityFloorCapMentions(string $text, string $place, int $max = 6): string
{
    unset($place, $max);
    return $text;
}

function icomplyQualityFloorLandlord(string $path, string $subject, string $place): string
{
    static $banks = null;
    if ($banks === null) {
        $file = dirname(__DIR__) . '/data/landlord-lines.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $banks = is_array($decoded['banks'] ?? null) ? $decoded['banks'] : [];
    }
    $job = $subject !== '' ? $subject : 'the work';
    $town = $place !== '' ? $place : 'Stockport';
    $parts = [];
    foreach ($banks as $index => $bank) {
        if (!is_array($bank) || $bank === []) {
            continue;
        }
        $hash = hash('sha256', $path . '|' . ($index + 1));
        $n = hexdec(substr($hash, 0, 8));
        $line = (string)$bank[$n % count($bank)];
        if (str_starts_with($line, 'Useful photographs')) {
            $line = icomplyQualityFloorPhotoTemplate($path);
        }
        $parts[] = str_replace(['{subject}', '{place}'], [$job, $town], $line);
    }
    return implode("\n", $parts);
}

/** @return list<string>|null */
function icomplyQualityFloorDistinct(string $path): ?array
{
    static $doc = null;
    if ($doc === null) {
        $file = dirname(__DIR__) . '/data/distinct-essays.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $doc = is_array($decoded) ? $decoded : [];
    }
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $head = $parts[1] ?? '';
    $leaf = $parts[2] ?? '';
    if ($head === 'manufacturers' && is_array($doc['manufacturers'][$leaf] ?? null)) {
        return $doc['manufacturers'][$leaf];
    }
    if ($head === 'packages' && is_array($doc['packages'][$leaf] ?? null)) {
        return $doc['packages'][$leaf];
    }
    return null;
}

/** @return array<string, string> */
function icomplyQualityFloorKeywordServices(): array
{
    static $map = null;
    if ($map === null) {
        $file = dirname(__DIR__) . '/data/keyword-services.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $map = is_array($decoded) ? $decoded : [];
        $extraFile = dirname(__DIR__) . '/data/keyword-service-fallback.json';
        $extra = is_file($extraFile) ? json_decode((string)file_get_contents($extraFile), true) : [];
        if (is_array($extra)) {
            foreach ($extra as $slug => $service) {
                if (!isset($map[$slug]) && is_string($service) && $service !== '') {
                    $map[(string)$slug] = $service;
                }
            }
        }
        if (function_exists('getMajorKeywords')) {
            foreach (getMajorKeywords() as $slug => $row) {
                if (isset($map[$slug]) || !is_array($row)) {
                    continue;
                }
                $service = (string)($row['service'] ?? '');
                if ($service !== '') {
                    $map[(string)$slug] = $service;
                }
            }
        }
        foreach ($map as $slug => $service) {
            if ($service === 'aov') {
                $map[$slug] = 'aov-air-handling';
            }
        }
    }
    return $map;
}

/** @return array<string, string> */
function icomplyQualityFloorHashSources(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    if (!function_exists('icomplyPerfManifest')) {
        $perf = __DIR__ . '/perf.php';
        if (is_file($perf)) {
            require_once $perf;
        }
    }
    $manifest = function_exists('icomplyPerfManifest') ? icomplyPerfManifest() : [];
    foreach ($manifest as $src => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        foreach ($meta['variants'] ?? [] as $variant) {
            if (!is_array($variant) || empty($variant['webp'])) {
                continue;
            }
            if (preg_match('#/opt/([a-f0-9]+)-#i', (string)$variant['webp'], $m) && !isset($map[$m[1]])) {
                $map[$m[1]] = (string)$src;
            }
        }
    }
    return $map;
}

function icomplyQualityFloorEssay(string $path, string $subject, string $place): string
{
    $town = $place !== '' ? $place : 'Stockport';
    $own = icomplyQualityFloorDistinct($path);
    if (is_array($own)) {
        $html = '';
        foreach ($own as $item) {
            if (!is_string($item) || $item === '') {
                continue;
            }
            $html .= '<p>' . icomplyQualityFloorH(icomplyQualityFloorCapMentions($item, $town)) . '</p>';
        }
        return $html;
    }
    $copy = icomplyQualityFloorCopy();
    $key = icomplyQualityFloorServiceKey($path);
    $services = is_array($copy['services'] ?? null) ? $copy['services'] : [];
    $specific = $key !== 'default' && isset($services[$key]);
    $job = icomplyQualityFloorSubject($subject, $specific ? $key : 'default', $path);
    $budget = 0;
    $blocks = [];
    if ($specific) {
        $lists = [
            $services[$key] ?? [],
            is_array($copy['notes'][$key] ?? null) ? $copy['notes'][$key] : [],
            is_array($copy['facts'][$key] ?? null) ? $copy['facts'][$key] : [],
        ];
        foreach ($lists as $list) {
            foreach ($list as $item) {
                if (!is_string($item) || $item === '') {
                    continue;
                }
                $blocks[] = icomplyQualityFloorFill($item, $town, $job, $budget);
            }
        }
    }
    foreach (explode("\n", icomplyQualityFloorLandlord($path, $job, $town)) as $line) {
        if ($line !== '') {
            $blocks[] = $line;
        }
    }
    $text = implode("\n", $blocks);
    $html = '';
    foreach (explode("\n", $text) as $line) {
        if ($line === '') {
            continue;
        }
        $html .= '<p>' . icomplyQualityFloorH($line) . '</p>';
    }
    return $html;
}

function icomplyQualityFloorFamily(string $path): string
{
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $head = ($parts[0] ?? '') === 'pages' ? ($parts[1] ?? '') : ($parts[0] ?? '');
    $leaf = strtolower($parts[count($parts) - 1] ?? '');
    $services = function_exists('getServices') ? getServices() : [];
    $keywords = icomplyQualityFloorKeywordServices();
    $brands = [
        'apt-controls', 'atg-access', 'avon-barrier', 'dea', 'designa', 'eagle',
        'frontier-pitts', 'heald', 'hysecurity', 'key-automation', 'life', 'newgate', 'skidata',
    ];
    if ($path === '/' || $path === '') {
        return 'hub:home';
    }
    if ($head === 'services' && isset($parts[2], $services[$parts[2]])) {
        return (string)$parts[2];
    }
    if ($head === 'aov') {
        return 'aov-air-handling';
    }
    if ($head === 'barriers' || ($head === 'manufacturers' && in_array($parts[2] ?? '', $brands, true))) {
        return 'barriers';
    }
    if ($head === 'keywords' && isset($keywords[$parts[2] ?? ''])) {
        return icomplyImageAlias((string)$keywords[$parts[2]]);
    }
    if ($head === 'jobs' && isset($parts[2]) && (isset($services[$parts[2]]) || isset($keywords[$parts[2]]))) {
        return isset($services[$parts[2]]) ? (string)$parts[2] : icomplyImageAlias((string)$keywords[$parts[2]]);
    }
    if ($head === 'jobs' || $leaf === 'jobs' || $leaf === 'become-a-subcontractor' || str_contains($path, 'become-a-subcontractor')) {
        return 'hub:company';
    }
    if ($head === 'packages' || $leaf === 'packages') {
        return 'hub:packages';
    }
    if ($head === 'areas' || $leaf === 'areas') {
        return 'hub:areas';
    }
    if (in_array($head, ['resources', 'directories'], true) || in_array($leaf, ['resources', 'directories'], true)) {
        return 'hub:resources';
    }
    if ($head === 'manufacturers' || $leaf === 'manufacturers') {
        return 'hub:manufacturers';
    }
    if ($head === 'care-homes' || str_contains($path, 'care-home')) {
        return 'nurse-call';
    }
    if (str_contains($path, 'asbestos')) {
        return 'asbestos-survey';
    }
    if ($head === 'landlords' || str_contains($leaf, 'landlord')) {
        return 'landlord-compliance';
    }
    if (str_starts_with($head, 'commercial') && isset($services['commercial-fit-out'])) {
        return 'commercial-fit-out';
    }
    $skip = ['keywords', 'jobs', 'areas', 'manufacturers', 'packages', 'resources'];
    if (isset($services[$head]) && !in_array($head, $skip, true)) {
        return $head;
    }
    $real = ['index', 'company', 'packages', 'resources', 'manufacturers', 'areas', 'about', 'privacy', 'terms', 'renewals', 'contact', 'home'];
    if (in_array($leaf, $real, true)) {
        return 'hub:' . ($leaf === 'index' ? 'home' : $leaf);
    }
    if (in_array($head, $real, true)) {
        return 'hub:' . ($head === 'index' ? 'home' : $head);
    }
    if (in_array($head, ['products', 'shop'], true) || in_array($leaf, ['products', 'shop'], true)) {
        return 'products';
    }
    if ($head === 'services' && !isset($parts[2])) {
        return 'directory:services';
    }
    if ($head === 'keywords' && !isset($parts[2])) {
        return 'directory:keywords';
    }
    return 'directory:unknown';
}

function icomplyQualityFloorCanonicalSrc(string $path): string
{
    if ($path === '/' || $path === '') {
        return '/assets/images/real-job/home.jpg';
    }
    $family = icomplyQualityFloorFamily($path);
    if ($family === 'products' || str_starts_with($family, 'directory:')) {
        return '/assets/images/real-job/resources.jpg';
    }
    if (str_starts_with($family, 'hub:')) {
        $leaf = substr($family, 4);
        if ($leaf === 'company' || $leaf === 'home') {
            return '/assets/images/real-job/resources.jpg';
        }
        $rel = '/assets/images/real-job/' . $leaf . '.jpg';
        return is_file(dirname(__DIR__) . $rel) ? $rel : '/assets/images/real-job/resources.jpg';
    }
    if ($family === 'aov-air-handling') {
        return icomplyImagePick($path, $family, 2)[0];
    }
    $rel = '/assets/images/services/' . $family . '.jpg';
    if (is_file(dirname(__DIR__) . $rel)) {
        return $rel;
    }
    return icomplyImagePick($path, $family, 1)[0];
}

function icomplyQualityFloorImageSource(string $src): string
{
    $clean = (string)strtok($src, '?');
    if (preg_match('#/opt/([a-f0-9]+)-#i', $clean, $m)) {
        $mapped = icomplyQualityFloorHashSources()[$m[1]] ?? '';
        if ($mapped !== '') {
            return $mapped;
        }
    }
    return $clean;
}

function icomplyQualityFloorImageFamily(string $src): string
{
    $origin = icomplyQualityFloorImageSource($src);
    if (preg_match('#/assets/images/services/([a-z0-9-]+?)(?:-(?:photo|context))?\.jpe?g$#i', $origin, $m)) {
        return $m[1];
    }
    if (preg_match('#/assets/images/lanes/([a-z0-9-]+)/#i', $origin, $m) || preg_match('#/lanes/([a-z0-9-]+)/#i', $src, $m)) {
        return $m[1];
    }
    if (preg_match('#/assets/images/keywords/([a-z0-9-]+)\.jpe?g$#i', $origin, $m)) {
        return icomplyImageAlias((string)(icomplyQualityFloorKeywordServices()[$m[1]] ?? ''));
    }
    if (preg_match('#/assets/images/trades/([a-z0-9-]+)--#i', $origin, $m)) {
        $trade = $m[1] === 'aov' ? 'aov-air-handling' : $m[1];
        return icomplyImageAlias($trade);
    }
    if (preg_match('#/assets/images/real-job/([a-z0-9-]+)\.jpe?g$#i', $origin, $m)) {
        return 'hub:' . $m[1];
    }
    if (preg_match('#/assets/images/(?:products|shop)/#i', $origin)) {
        return 'products';
    }
    return '';
}

function icomplyQualityFloorPlaceholderImage(string $src): bool
{
    $origin = icomplyQualityFloorImageSource($src);
    if (str_contains($origin, '/lanes/') || str_contains($src, '/lanes/')) {
        return true;
    }
    if (preg_match('#/aov-air-handling-(?:photo|context)\.jpe?g$#i', $origin)) {
        return false;
    }
    return (bool)preg_match('/-(?:photo|context)\./i', $origin);
}

function icomplyQualityFloorPath(string $html): string
{
    if (preg_match('/<link[^>]+rel="canonical"[^>]+href="([^"]+)"/i', $html, $m)
        || preg_match('/<link[^>]+href="([^"]+)"[^>]+rel="canonical"/i', $html, $m)) {
        $path = (string)(parse_url($m[1], PHP_URL_PATH) ?: '/');
        return rtrim($path, '/') ?: '/';
    }
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $path = (string)(parse_url($uri, PHP_URL_PATH) ?: '/');
    return rtrim($path, '/') ?: '/';
}

function icomplyQualityFloorH1(string $html): string
{
    if (!preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        return 'Property work';
    }
    $text = trim((string)preg_replace('/\s+/', ' ', strip_tags($m[1])));
    return $text !== '' ? $text : 'Property work';
}

function icomplyQualityFloorSlotSrc(string $path): string
{
    return icomplyQualityFloorCanonicalSrc($path);
}

function icomplyQualityFloorRewritePath(string $path): string
{
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = preg_replace('#/index$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if ($path === '/pages') {
        return '/pages/services';
    }
    if (preg_match('#^/pages/([a-z0-9\-]+)$#', $path, $m)) {
        $slug = $m[1];
        $reserved = [
            'keywords', 'services', 'areas', 'manufacturers', 'resources', 'packages', 'jobs',
            'commercial', 'aov', 'barriers', 'aov-air-handling', 'products', 'shop',
            'emergency-lighting-jobs', 'asbestos-jobs',
        ];
        $page = dirname(__DIR__) . '/pages/' . $slug . '.php';
        $services = function_exists('getServices') ? getServices() : [];
        if (!is_file($page) && !in_array($slug, $reserved, true) && isset($services[$slug])) {
            return '/pages/services/' . $slug;
        }
        return $path;
    }
    if (!function_exists('icomplyNonGmMatrixRedirect')) {
        $gm = __DIR__ . '/gm-crawl.php';
        if (is_file($gm)) {
            require_once $gm;
        }
    }
    if (function_exists('icomplyNonGmMatrixRedirect')) {
        $to = icomplyNonGmMatrixRedirect($path);
        if (is_string($to) && $to !== '') {
            return $to;
        }
    }
    return $path;
}

function icomplyQualityFloorRewriteHref(string $href): string
{
    $raw = trim($href);
    if ($raw === '' || str_starts_with($raw, '#') || preg_match('#^(?:mailto:|tel:|javascript:)#i', $raw)) {
        return $href;
    }
    $origin = '';
    $path = $raw;
    $suffix = '';
    if (preg_match('#^https?://#i', $raw)) {
        $host = (string)parse_url($raw, PHP_URL_HOST);
        if (!preg_match('#(^|\.)icomplypropertyservices\.co\.uk$#i', $host)) {
            return $href;
        }
        $origin = ((string)parse_url($raw, PHP_URL_SCHEME)) . '://' . $host;
        $path = (string)(parse_url($raw, PHP_URL_PATH) ?: '/');
        $query = parse_url($raw, PHP_URL_QUERY);
        $hash = parse_url($raw, PHP_URL_FRAGMENT);
        $suffix = (is_string($query) && $query !== '' ? '?' . $query : '') . (is_string($hash) && $hash !== '' ? '#' . $hash : '');
    } elseif (str_starts_with($raw, '/')) {
        $cut = strcspn($raw, '?#');
        $path = substr($raw, 0, $cut);
        $suffix = substr($raw, $cut);
    } else {
        return $href;
    }
    $next = icomplyQualityFloorRewritePath($path);
    $norm = rtrim($path, '/') ?: '/';
    if ($next === $path || $next === $norm) {
        return $href;
    }
    return $origin . $next . $suffix;
}

function icomplySanitizeInternalLinks(string $html): string
{
    $out = preg_replace_callback('/href\s*=\s*(["\'])([^"\']*)\1/i', static function (array $m): string {
        $next = icomplyQualityFloorRewriteHref($m[2]);
        if ($next === $m[2]) {
            return $m[0];
        }
        return 'href=' . $m[1] . $next . $m[1];
    }, $html);
    return is_string($out) ? $out : $html;
}

/** @return array<string, array<string, string>> */
function icomplyQualityFloorCredits(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    $file = dirname(__DIR__) . '/data/hero-image-credits.json';
    $rows = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    foreach (is_array($rows) ? $rows : [] as $row) {
        if (!is_array($row) || !isset($row['file'])) {
            continue;
        }
        $map[(string)$row['file']] = $row;
    }
    return $map;
}

function icomplyQualityFloorCreditName(string $src): string
{
    $path = icomplyQualityFloorImageSource($src);
    if (!str_starts_with($path, '/')) {
        $path = (string)preg_replace('#^https?://[^/]+#i', '', $path);
    }
    if (preg_match('#/demolition(?:-(?:photo|context))?\.jpe?g$#i', $path)) {
        return 'Tomwsulcer (CC0)';
    }
    $base = (string)preg_replace('#-(?:photo|context)(\.[a-z0-9]+)$#i', '$1', $path);
    $row = icomplyQualityFloorCredits()[$path] ?? icomplyQualityFloorCredits()[$base] ?? null;
    if (!is_array($row) || !preg_match('#^https?://#i', (string)($row['source'] ?? ''))) {
        return 'iComply Property Services';
    }
    $artist = trim((string)preg_replace('/\s+/', ' ', strip_tags((string)($row['artist'] ?? ''))));
    $license = trim(strip_tags((string)($row['license'] ?? '')));
    $name = $artist !== '' ? $artist : 'Wikimedia Commons';
    if (strlen($name) > 120) {
        $name = substr($name, 0, 120);
    }
    return $license !== '' ? $name . ' (' . $license . ')' : $name;
}

function icomplyQualityFloorSkipImg(string $attrs): bool
{
    $src = '';
    $alt = '';
    $class = '';
    $width = 0;
    if (preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $attrs, $m)) {
        $src = $m[1];
    }
    if (preg_match('/\balt\s*=\s*"([^"]*)"/i', $attrs, $m)) {
        $alt = $m[1];
    }
    if (preg_match('/\bclass\s*=\s*"([^"]*)"/i', $attrs, $m)) {
        $class = $m[1];
    }
    if (preg_match('/\bwidth\s*=\s*"(\d+)"/i', $attrs, $m)) {
        $width = (int)$m[1];
    }
    if (preg_match('/\.svg(?:$|\?)/i', $src) || preg_match('/favicon|\/logo/i', $src)) {
        return true;
    }
    if (preg_match('/\blogo\b|\bicon\b|\bbrand\b|mega-logo/i', $class) || preg_match('/\blogo\b/i', $alt)) {
        return true;
    }
    return $width > 0 && $width <= 64;
}

function icomplyStampImageCredits(string $html): string
{
    $held = [];
    $masked = preg_replace_callback('/<(header|nav|footer)\b[^>]*>.*?<\/\1>/is', static function (array $m) use (&$held): string {
        $cleaned = preg_replace('/\sdata-credit="[^"]*"/i', '', $m[0]) ?? $m[0];
        $cleaned = preg_replace('/\sdata-credit-stamped="[^"]*"/i', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/<span class="image-credit">.*?<\/span>/is', '', $cleaned) ?? $cleaned;
        $held[] = $cleaned;
        return "\0HOLD" . (count($held) - 1) . "\0";
    }, $html);
    if (!is_string($masked)) {
        $masked = $html;
    }
    $stamped = preg_replace_callback('/<img\b([^>]*?)>/i', static function (array $m): string {
        $attrs = rtrim($m[1]);
        if (str_ends_with($attrs, '/')) {
            $attrs = rtrim(substr($attrs, 0, -1));
        }
        $src = '';
        if (preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $attrs, $sm)) {
            $src = $sm[1];
        }
        $origin = icomplyQualityFloorImageSource($src);
        $demolition = (bool)preg_match('#/demolition(?:-(?:photo|context))?\.jpe?g$#i', $origin);
        if (icomplyQualityFloorSkipImg($attrs) || (str_contains($attrs, 'data-credit-stamped=') && !$demolition)) {
            return '<img' . $attrs . '>';
        }
        if ($src === '' || (icomplyQualityFloorPlaceholderImage($src) && !$demolition)) {
            return '<img' . $attrs . '>';
        }
        $name = icomplyQualityFloorH(icomplyQualityFloorCreditName($src));
        if ($name !== 'iComply Property Services' && preg_match('/\balt\s*=\s*"([^"]*)"/i', $attrs, $am)) {
            $alt = str_replace('by iComply Property Services', 'by ' . $name, $am[1]);
            if ($alt !== $am[1]) {
                $attrs = preg_replace('/\balt\s*=\s*"[^"]*"/i', 'alt="' . $alt . '"', $attrs) ?? $attrs;
            }
        }
        if ($demolition && str_contains($attrs, 'data-credit-stamped=')) {
            if (preg_match('/\bdata-credit="/i', $attrs)) {
                $attrs = preg_replace('/\bdata-credit="[^"]*"/i', 'data-credit="' . $name . '"', $attrs) ?? $attrs;
            } else {
                $attrs .= ' data-credit="' . $name . '"';
            }
            return '<img' . $attrs . '>';
        }
        if (preg_match('/\bdata-credit="/i', $attrs)) {
            $attrs = preg_replace('/\bdata-credit="[^"]*"/i', 'data-credit="' . $name . '"', $attrs) ?? $attrs;
        } else {
            $attrs .= ' data-credit="' . $name . '"';
        }
        $attrs .= ' data-credit-stamped="1"';
        return '<img' . $attrs . '><span class="image-credit">Image credit: ' . $name . '</span>';
    }, $masked);
    if (!is_string($stamped)) {
        $stamped = $masked;
    }
    $restored = preg_replace_callback('/\0HOLD(\d+)\0/', static function (array $m) use ($held): string {
        return $held[(int)$m[1]] ?? '';
    }, $stamped);
    return is_string($restored) ? $restored : $stamped;
}

function icomplyQualityFloorCrumbs(string $path, string $subject): string
{
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $items = [['href' => '/', 'label' => 'Home']];
    $head = ($parts[0] ?? '') === 'pages' ? ($parts[1] ?? '') : '';
    $services = function_exists('getServices') ? getServices() : [];
    $push = static function (string $href, string $label) use (&$items): void {
        $items[] = ['href' => $href, 'label' => $label];
    };
    if ($head === 'services') {
        $push('/pages/services', 'Services');
    } elseif ($head === 'keywords') {
        $push('/pages/keywords', 'Guides');
        if (isset($parts[2], $parts[3])) {
            $push('/pages/keywords/' . $parts[2], ucwords(str_replace('-', ' ', $parts[2])));
        }
    } elseif ($head === 'jobs') {
        $push('/pages/jobs', 'Jobs');
        if (isset($parts[2], $parts[3])) {
            $push('/pages/jobs/' . $parts[2], ucwords(str_replace('-', ' ', $parts[2])));
        }
    } elseif ($head === 'areas') {
        $push('/pages/areas', 'Areas');
    } elseif ($head === 'manufacturers') {
        $push('/pages/manufacturers', 'Manufacturers');
        if (isset($parts[2], $parts[3])) {
            $push('/pages/manufacturers/' . $parts[2], ucwords(str_replace('-', ' ', $parts[2])));
        }
    } elseif ($head === 'aov' || $head === 'barriers') {
        $push('/pages/' . $head, $head === 'aov' ? 'AOV' : 'Barriers');
    } elseif (isset($services[$head]) && is_string($services[$head])) {
        $push('/pages/services/' . $head, $services[$head]);
    } elseif ($head !== '') {
        $push('/pages/' . $head, ucwords(str_replace('-', ' ', $head)));
    }
    $items[] = ['label' => $subject !== '' ? $subject : 'This page'];
    $lis = '';
    $ldItems = [];
    foreach ($items as $i => $item) {
        $name = icomplyQualityFloorH((string)$item['label']);
        $href = isset($item['href']) ? (string)$item['href'] : '';
        $body = $href !== '' && $i !== count($items) - 1
            ? '<a itemprop="item" href="' . icomplyQualityFloorH($href) . '"><span itemprop="name">' . $name . '</span></a>'
            : '<span itemprop="name">' . $name . '</span>';
        $lis .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">' . $body
            . '<meta itemprop="position" content="' . ($i + 1) . '"></li>';
        $ldItems[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => (string)$item['label'],
            'item' => 'https://icomplypropertyservices.co.uk' . ($href !== '' ? $href : $path),
        ];
    }
    $ld = json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $ldItems], JSON_UNESCAPED_SLASHES);
    return '<nav class="breadcrumb" aria-label="Breadcrumb"><ol itemscope itemtype="https://schema.org/BreadcrumbList">'
        . $lis . '</ol></nav><script type="application/ld+json">' . $ld . '</script>';
}

function icomplyQualityFloorPhoto(string $path, string $subject): string
{
    $family = icomplyQualityFloorFamily($path);
    $srcs = $family === 'aov-air-handling'
        ? array_slice(icomplyImagePick($path, $family, 2), 0, 2)
        : [icomplyQualityFloorSlotSrc($path)];
    $html = '<figure class="real-job-photo" data-pe-slot="real-photo" data-real-job-photo="owner">';
    foreach ($srcs as $index => $src) {
        $name = icomplyQualityFloorCreditName($src);
        $html .= '<img src="' . icomplyQualityFloorH($src) . '" alt="' . icomplyQualityFloorH('Completed ' . $subject . ' by ' . $name)
            . '" data-pe-slot="real-photo" data-credit="' . icomplyQualityFloorH($name)
            . '" data-credit-stamped="1" width="1200" height="800"><figcaption>Image credit: '
            . icomplyQualityFloorH($name) . '</figcaption>';
        unset($index);
    }
    return $html . '</figure>';
}

function icomplyQualityFloorScrubPricing(string $html): string
{
    $held = [];
    $masked = preg_replace_callback('/[a-z0-9]+-fixed-price(?:-[a-z0-9]+)+/i', static function (array $m) use (&$held): string {
        $held[] = $m[0];
        return "\x01SLUG" . (count($held) - 1) . "\x01";
    }, $html);
    if (!is_string($masked)) {
        $masked = $html;
    }
    $steps = [
        '/No fixed price is published/i' => 'Price on application',
        '/fixed written quotes/i' => 'free written quotes',
        '/fixed written quote/i' => 'free written quote',
        '/Every quote is fixed and written/i' => 'Every quote is a free written quote',
        '/fixed-pricing/i' => 'a free written quote',
        '/fixed pricing/i' => 'a free written quote',
        '/fixed-prices/i' => 'free written quotes',
        '/fixed prices/i' => 'free written quotes',
        '/fixed-price/i' => 'free written quote',
        '/\bPOA fixed quote\b/i' => 'a free written quote',
        '/fixed quotes/i' => 'free written quotes',
        '/fixed quote/i' => 'free written quote',
        '/\bfixed price\b/i' => 'free written quote',
    ];
    foreach ($steps as $pattern => $replacement) {
        $next = preg_replace_callback($pattern, static function (array $m) use ($replacement): string {
            if (preg_match('/^[A-Z]/', $m[0]) && preg_match('/^[a-z]/', $replacement)) {
                return ucfirst($replacement);
            }
            return $replacement;
        }, $masked);
        if (is_string($next)) {
            $masked = $next;
        }
    }
    $restored = preg_replace_callback('/\x01SLUG(\d+)\x01/', static function (array $m) use ($held): string {
        return $held[(int)$m[1]] ?? '';
    }, $masked);
    return is_string($restored) ? $restored : $masked;
}

function icomplyQualityFloorScrubSentences(string $html, string $path, string $subject): string
{
    $gas = icomplyQualityFloorIsGas($path, $subject);
    $family = icomplyQualityFloorFamily($path);
    $garden = in_array($family, ['fencing', 'landscaping', 'demolition', 'driveways', 'garden-clearance', 'garden-maintenance', 'patios'], true);
    $held = [];
    $masked = preg_replace_callback('/<(script|style)\b[^>]*>.*?<\/\1>/is', static function (array $m) use (&$held, $gas): string {
        $block = $m[0];
        if (!$gas) {
            $block = preg_replace('/do not describe iComply as Gas Safe[^.!?]*[.!?]/i', 'The office confirms who attends before the visit.', $block) ?? $block;
            $block = preg_replace('/do not claim the company is Gas Safe[^.!?]*[.!?]/i', 'The office confirms who attends before the visit.', $block) ?? $block;
            $block = preg_replace('/Landlord gas safety certificates \(CP12\) are carried out by Gas Safe registered engineers\./i', 'The office confirms who attends before the visit.', $block) ?? $block;
            $block = preg_replace('/Gas work is carried out by Gas Safe registered engineers\./i', 'The office confirms who attends before the visit.', $block) ?? $block;
        }
        $held[] = $block;
        return "\0SCRUB" . (count($held) - 1) . "\0";
    }, $html);
    if (!is_string($masked)) {
        $masked = $html;
    }
    $out = preg_replace_callback('/>([^<]+)</', static function (array $m) use ($gas, $garden): string {
        $text = $m[1];
        if (!preg_match('/[.!?]/', $text)) {
            return $m[0];
        }
        $parts = preg_split('/(?<=[.!?])\s+/', $text) ?: [];
        $kept = [];
        foreach ($parts as $sentence) {
            $line = trim((string)$sentence);
            if ($line === '') {
                continue;
            }
            if (preg_match('/do not describe iComply as Gas Safe/i', $line)) {
                continue;
            }
            if (preg_match('/do not claim the company is Gas Safe/i', $line)) {
                continue;
            }
            if (!$garden && preg_match('/fill a skip and call the garden clear/i', $line)) {
                continue;
            }
            if (!$gas && preg_match('/Gas Safe registered/i', $line)) {
                continue;
            }
            if (preg_match('/same shape every time/i', $line)) {
                continue;
            }
            $kept[] = $line;
        }
        return '>' . implode(' ', $kept) . '<';
    }, $masked);
    if (!is_string($out)) {
        $out = $masked;
    }
    $restored = preg_replace_callback('/\0SCRUB(\d+)\0/', static function (array $m) use ($held): string {
        return $held[(int)$m[1]] ?? '';
    }, $out);
    return is_string($restored) ? $restored : $out;
}

function icomplyQualityFloorCapPrefix(string $html, string $place): string
{
    $town = $place !== '' ? $place : 'Stockport';
    $seen = 0;
    $out = preg_replace_callback('/\bIn ' . preg_quote($town, '/') . ',\\s+/', static function (array $m) use (&$seen): string {
        $seen++;
        return $seen <= 4 ? $m[0] : "\x02";
    }, $html);
    if (!is_string($out)) {
        return $html;
    }
    $out = (string)preg_replace_callback('/\x02([a-z])/', static function (array $m): string {
        return strtoupper($m[1]);
    }, $out);
    return str_replace("\x02", '', $out);
}

function icomplyQualityFloorRewriteMetaImages(string $html, string $path): string
{
    $want = icomplyQualityFloorCanonicalSrc($path);
    $family = icomplyQualityFloorFamily($path);
    $out = preg_replace_callback('/<meta\b[^>]*>/i', static function (array $m) use ($want, $family, $path): string {
        $tag = $m[0];
        if (!preg_match('/\b(?:property|name)\s*=\s*"(?:og:image(?::secure_url)?|twitter:image)"/i', $tag)) {
            return $tag;
        }
        $next = preg_replace_callback('/\bcontent\s*=\s*"([^"]*)"/i', static function (array $cm) use ($want, $family, $path): string {
            $pathOnly = (string)preg_replace('#^https?://[^/]+#i', '', $cm[1]);
            $origin = icomplyQualityFloorImageSource($pathOnly);
            $imgFamily = icomplyQualityFloorImageFamily($pathOnly);
            $sharedHub = (bool)preg_match('#/real-job/(?:company|home)\.jpe?g$#i', $origin) && $path !== '/';
            $collapsed = icomplyImageCollapsedAov($origin) || icomplyImageCollapsedAov($pathOnly);
            $url = $cm[1];
            if ($sharedHub || $collapsed || !($imgFamily === '' || ($imgFamily === $family && !icomplyQualityFloorPlaceholderImage($pathOnly)))) {
                $swapped = preg_replace('#/assets/images/[^"?]+#', $want, $cm[1]);
                if (is_string($swapped) && $swapped !== '') {
                    $url = $swapped;
                }
            }
            return 'content="' . icomplyQualityFloorAbsoluteImageUrl($url) . '"';
        }, $tag);
        return is_string($next) ? $next : $tag;
    }, $html);
    return is_string($out) ? $out : $html;
}

function icomplyQualityFloorConfineImages(string $html, string $path): string
{
    $want = icomplyQualityFloorCanonicalSrc($path);
    $wantFamily = icomplyQualityFloorFamily($path);
    $allowProducts = $wantFamily === 'products';
    $directory = str_starts_with($wantFamily, 'directory:') || str_starts_with($wantFamily, 'hub:');
    $slot = 0;
    $out = preg_replace_callback('/<img\b([^>]*?)>/i', static function (array $m) use ($want, $wantFamily, $allowProducts, $directory, $path, &$slot): string {
        $attrs = rtrim($m[1]);
        if (str_ends_with($attrs, '/')) {
            $attrs = rtrim(substr($attrs, 0, -1));
        }
        if (icomplyQualityFloorSkipImg($attrs)) {
            return '<img' . $attrs . '>';
        }
        $src = '';
        if (preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $attrs, $sm)) {
            $src = $sm[1];
        }
        if ($src === '') {
            return '<img' . $attrs . '>';
        }
        $origin = icomplyQualityFloorImageSource($src);
        $family = icomplyQualityFloorImageFamily($src);
        $product = $family === 'products' || (bool)preg_match('#/(?:products|shop)/#i', $src);
        $placeholder = icomplyQualityFloorPlaceholderImage($src);
        $sharedHub = (bool)preg_match('#/real-job/(?:company|home)\.jpe?g$#i', $origin) && $path !== '/';
        $collapsed = icomplyImageCollapsedAov($origin) || icomplyImageCollapsedAov($src);
        $owner = icomplyImageOwner($origin);
        if ($owner === '' ) {
            $owner = icomplyImageOwner($src);
        }
        $servicePage = $wantFamily !== ''
            && !str_starts_with($wantFamily, 'hub:')
            && !str_starts_with($wantFamily, 'directory:')
            && $wantFamily !== 'products';
        $foreign = $servicePage && $owner !== '' && $owner !== $wantFamily;
        $sharedMaintenance = $wantFamily !== 'building-maintenance' && (
            str_contains($origin, '/services/building-maintenance.jpg') || str_contains($src, '19f957bd50f5')
        );
        $swap = static function (string $attrs, string $src): string {
            $attrs = preg_replace('/\bsrc\s*=\s*"[^"]*"/i', 'src="' . $src . '"', $attrs) ?? $attrs;
            if (!preg_match('/\bsrc\s*=/i', $attrs)) {
                $attrs = ' src="' . $src . '"' . $attrs;
            }
            $attrs = preg_replace('/\s+srcset\s*=\s*"[^"]*"/i', '', $attrs) ?? $attrs;
            $attrs = preg_replace('/\s+data-srcset\s*=\s*"[^"]*"/i', '', $attrs) ?? $attrs;
            $attrs = preg_replace('/\s+data-perf\s*=\s*"[^"]*"/i', '', $attrs) ?? $attrs;
            $attrs = preg_replace('/\s+onerror\s*=\s*"[^"]*"/i', '', $attrs) ?? $attrs;
            return $attrs;
        };
        $nextSrc = static function () use ($want, $wantFamily, $path, &$slot): string {
            if (str_starts_with($wantFamily, 'hub:') || str_starts_with($wantFamily, 'directory:') || $wantFamily === 'products' || $wantFamily === '') {
                return $want;
            }
            $picks = icomplyImagePick($path, $wantFamily, 6);
            $choice = $picks[$slot % count($picks)];
            $slot++;
            return $choice;
        };
        if ($sharedHub || $collapsed || $sharedMaintenance || $foreign) {
            return '<img' . $swap($attrs, $nextSrc()) . '>';
        }
        if ($directory) {
            if (!$placeholder && $family !== '' && !str_starts_with($family, 'hub:') && $family !== 'products') {
                return '<img' . $attrs . '>';
            }
            if ($placeholder || $family === 'hub:home' || $family === 'hub:company' || $family === '') {
                return '<img' . $swap($attrs, $want) . '>';
            }
            return '<img' . $attrs . '>';
        }
        if (!$placeholder && $family === $wantFamily && $family !== '') {
            return '<img' . $attrs . '>';
        }
        if ($allowProducts && $product) {
            return '<img' . $attrs . '>';
        }
        return '<img' . $swap($attrs, $nextSrc()) . '>';
    }, $html);
    if (!is_string($out)) {
        $out = $html;
    }
    $replacement = $wantFamily === 'aov-air-handling'
        ? icomplyImagePick($path, $wantFamily, 2)[0]
        : $want;
    if (!str_contains($replacement, 'a055da46974c') && !str_contains($replacement, 'aov-air-handling.jpg')) {
        $out = preg_replace('#(?:https?://[^"\'\s]+)?/assets/images/opt/a055da46974c-\d+\.webp#i', $replacement, $out) ?? $out;
        if ($wantFamily === 'aov-air-handling') {
            $out = preg_replace('#(?:https?://[^"\'\s]+)?/assets/images/services/aov-air-handling(?:-(?:photo|context))?\.jpe?g#i', $replacement, $out) ?? $out;
        }
    }
    return $out;
}

function icomplyApplyQualityFloor(string $html): string
{
    if ($html === '' || (stripos($html, '<html') === false && stripos($html, '<body') === false)) {
        return $html;
    }
    $path = icomplyQualityFloorPath($html);
    $subject = icomplyQualityFloorH1($html);
    $place = icomplyQualityFloorPlace($path);
    if (!str_contains($html, 'data-quality-floor="1"')) {
        $needsCrumbs = !str_contains($html, 'itemtype="https://schema.org/BreadcrumbList"');
        $needsFaq = !str_contains($html, '"@type":"FAQPage"') && !str_contains($html, '"@type": "FAQPage"');
        $faq = '';
        if ($needsFaq) {
            $q1 = 'How is ' . $subject . ' booked?';
            $a1 = 'Call 07517 806082 or WhatsApp the same number. The office is 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. You get a free written quote before work is agreed.';
            $q2 = 'Who attends the visit?';
            $a2 = icomplyQualityFloorIsGas($path, $subject)
                ? 'Gas work is carried out by Gas Safe registered engineers.'
                : 'iComply arranges the visit from the office at 17 Woodlands Park Road, Offerton, SK2 5DE.';
            $faqLd = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => $q1, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a1]],
                    ['@type' => 'Question', 'name' => $q2, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a2]],
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $faq = '<section class="faq" id="quality-floor-faq"><h2>Questions</h2><details><summary>'
                . icomplyQualityFloorH($q1) . '</summary><p>' . icomplyQualityFloorH($a1) . '</p></details><details><summary>'
                . icomplyQualityFloorH($q2) . '</summary><p>' . icomplyQualityFloorH($a2) . '</p></details></section>'
                . '<script type="application/ld+json">' . $faqLd . '</script>';
        }
        $block = '<section data-quality-floor="1" id="local-essay">'
            . ($needsCrumbs ? icomplyQualityFloorCrumbs($path, $subject) : '')
            . icomplyQualityFloorPhoto($path, $subject)
            . icomplyQualityFloorEssay($path, $subject, $place)
            . $faq
            . '</section>';
        $article = strripos($html, '</article>');
        if ($article !== false) {
            $html = substr($html, 0, $article) . $block . substr($html, $article);
        } else {
            $main = strripos($html, '</main>');
            if ($main !== false) {
                $html = substr($html, 0, $main) . $block . substr($html, $main);
            } else {
                $body = strripos($html, '</body>');
                if ($body !== false) {
                    $html = substr($html, 0, $body) . $block . substr($html, $body);
                } else {
                    $html .= $block;
                }
            }
        }
    } elseif (!str_contains($html, 'data-pe-slot="real-photo"')) {
        $figure = icomplyQualityFloorPhoto($path, $subject);
        $body = strripos($html, '</body>');
        $html = $body === false ? $html . $figure : substr($html, 0, $body) . $figure . substr($html, $body);
    }
    $html = icomplySanitizeInternalLinks($html);
    $html = icomplyQualityFloorConfineImages($html, $path);
    $html = icomplyQualityFloorRewriteMetaImages($html, $path);
    $html = icomplyQualityFloorScrubSentences($html, $path, $subject);
    $html = icomplyQualityFloorCapPrefix($html, $place);
    $html = str_replace('pOA.', 'POA.', $html);
    $html = icomplyQualityFloorScrubPricing($html);
    if (!str_contains($html, '"@type":"FAQPage"') && !str_contains($html, '"@type": "FAQPage"') && !str_contains($html, 'itemtype="https://schema.org/FAQPage"')) {
        $q1 = 'How is ' . $subject . ' booked?';
        $a1 = 'Call 07517 806082 or WhatsApp the same number. The office is 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. You get a free written quote before work is agreed.';
        $q2 = 'Who attends the visit?';
        $a2 = icomplyQualityFloorIsGas($path, $subject)
            ? 'Gas work is carried out by Gas Safe registered engineers.'
            : 'iComply arranges the visit from the office at 17 Woodlands Park Road, Offerton, SK2 5DE.';
        $faqLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                ['@type' => 'Question', 'name' => $q1, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a1]],
                ['@type' => 'Question', 'name' => $q2, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a2]],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $faq = '<section class="faq" id="quality-floor-faq"><h2>Questions</h2><details><summary>'
            . icomplyQualityFloorH($q1) . '</summary><p>' . icomplyQualityFloorH($a1) . '</p></details><details><summary>'
            . icomplyQualityFloorH($q2) . '</summary><p>' . icomplyQualityFloorH($a2) . '</p></details></section>'
            . '<script type="application/ld+json">' . $faqLd . '</script>';
        $body = strripos($html, '</body>');
        $html = $body === false ? $html . $faq : substr($html, 0, $body) . $faq . substr($html, $body);
    }
    $html = icomplyQualityFloorDedupeBooking($html);
    return icomplyStampImageCredits($html);
}

function icomplyQualityFloorDedupeBooking(string $html): string
{
    $seen = 0;
    $out = preg_replace_callback(
        '/<p\b[^>]*>[^<]*To book, call or WhatsApp 07517 806082[^<]*<\/p>/i',
        static function (array $m) use (&$seen): string {
            $seen++;
            return $seen === 1 ? $m[0] : '';
        },
        $html
    );
    return is_string($out) ? $out : $html;
}
