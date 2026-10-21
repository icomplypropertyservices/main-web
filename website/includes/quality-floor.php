<?php
/**
 * Visible breadcrumbs, FAQ schema, a real-photo slot, content-image credits
 * and a local essay built from service facts. Idempotent via data-quality-floor="1".
 */
declare(strict_types=1);

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

function icomplyQualityFloorSubject(string $subject, string $key): string
{
    if (function_exists('getServices')) {
        $services = getServices();
        if (isset($services[$key]) && is_string($services[$key]) && $services[$key] !== '') {
            return $services[$key];
        }
    }
    $cleaned = trim((string)preg_replace('/\s+in\s+.+$/i', '', $subject));
    return $cleaned !== '' ? $cleaned : 'the work';
}

function icomplyQualityFloorLocalise(string $text, string $place, string $subject): string
{
    $town = $place !== '' ? $place : 'Stockport';
    $job = $subject !== '' ? $subject : 'the work';
    $text = str_replace(['{place}', '{subject}'], [$town, $job], $text);
    $parts = preg_split('/(?<=[.!?])\s+/', trim($text)) ?: [];
    $out = [];
    foreach ($parts as $sentence) {
        $line = trim((string)$sentence);
        if ($line === '') {
            continue;
        }
        if (stripos($line, $town) === false) {
            $line = 'In ' . $town . ', ' . lcfirst($line);
        }
        $out[] = $line;
    }
    return implode(' ', $out);
}

function icomplyQualityFloorEssay(string $path, string $subject, string $place): string
{
    $copy = icomplyQualityFloorCopy();
    $key = icomplyQualityFloorServiceKey($path);
    $services = is_array($copy['services'] ?? null) ? $copy['services'] : [];
    if (!isset($services[$key])) {
        $key = 'default';
    }
    $job = icomplyQualityFloorSubject($subject, $key);
    $town = $place !== '' ? $place : 'Stockport';
    $lists = [
        $services[$key] ?? [],
        is_array($copy['notes'][$key] ?? null) ? $copy['notes'][$key] : [],
        is_array($copy['facts'][$key] ?? null) ? $copy['facts'][$key] : [],
    ];
    $html = '';
    foreach ($lists as $list) {
        foreach ($list as $item) {
            if (!is_string($item) || $item === '') {
                continue;
            }
            $html .= '<p>' . icomplyQualityFloorH(icomplyQualityFloorLocalise($item, $town, $job)) . '</p>';
        }
    }
    return $html;
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
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $head = ($parts[0] ?? '') === 'pages' ? ($parts[1] ?? '') : ($parts[0] ?? '');
    $leaf = strtolower($parts[count($parts) - 1] ?? 'home');
    $known = [
        'aov' => 'aov-air-handling',
        'barriers' => 'barriers',
        'fencing' => 'fencing',
        'drainage' => 'drainage',
        'driveways' => 'driveways',
        'demolition' => 'demolition',
    ];
    $services = function_exists('getServices') ? getServices() : [];
    if ($head === 'services' && isset($parts[2], $services[$parts[2]])) {
        return '/assets/images/services/' . $parts[2] . '.jpg';
    }
    if (isset($known[$head])) {
        return '/assets/images/services/' . $known[$head] . '.jpg';
    }
    $skip = ['keywords', 'jobs', 'areas', 'manufacturers'];
    if (($parts[0] ?? '') === 'pages' && isset($services[$head]) && !in_array($head, $skip, true)) {
        return '/assets/images/services/' . $head . '.jpg';
    }
    $brands = [
        'apt-controls', 'atg-access', 'avon-barrier', 'dea', 'designa', 'eagle',
        'frontier-pitts', 'heald', 'hysecurity', 'key-automation', 'life', 'newgate', 'skidata',
    ];
    if (in_array($leaf, $brands, true)) {
        return '/assets/images/services/barriers.jpg';
    }
    $real = ['index', 'company', 'packages', 'resources', 'manufacturers', 'areas', 'about', 'privacy', 'terms', 'renewals', 'contact', 'home'];
    if ($head === 'manufacturers' || $leaf === 'manufacturers') {
        return '/assets/images/real-job/manufacturers.jpg';
    }
    if ($leaf === 'jobs' || $head === 'jobs' || $leaf === 'become-a-subcontractor' || str_contains($path, 'become-a-subcontractor')) {
        return '/assets/images/services/building-maintenance.jpg';
    }
    if ($leaf === 'directories' || $head === 'directories') {
        return '/assets/images/real-job/resources.jpg';
    }
    if ($leaf === 'products' || $head === 'products') {
        return '/assets/images/services/building-maintenance.jpg';
    }
    if (in_array($leaf, $real, true)) {
        return '/assets/images/real-job/' . $leaf . '.jpg';
    }
    if (in_array($head, $real, true)) {
        return '/assets/images/real-job/' . ($head === 'index' ? 'home' : $head) . '.jpg';
    }
    if ($path === '/' || $path === '') {
        return '/assets/images/real-job/home.jpg';
    }
    return '/assets/images/services/building-maintenance.jpg';
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
    $path = (string)strtok($src, '?');
    $row = icomplyQualityFloorCredits()[$path] ?? null;
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
        if (icomplyQualityFloorSkipImg($attrs) || str_contains($attrs, 'data-credit-stamped=')) {
            return '<img' . $attrs . '>';
        }
        $src = '';
        if (preg_match('/\bsrc\s*=\s*"([^"]*)"/i', $attrs, $sm)) {
            $src = $sm[1];
        }
        $name = icomplyQualityFloorH(icomplyQualityFloorCreditName($src));
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
    $src = icomplyQualityFloorSlotSrc($path);
    $name = icomplyQualityFloorCreditName($src);
    return '<figure class="real-job-photo" data-pe-slot="real-photo" data-real-job-photo="owner"><img src="'
        . icomplyQualityFloorH($src) . '" alt="' . icomplyQualityFloorH('Completed ' . $subject . ' by ' . $name)
        . '" data-pe-slot="real-photo" data-credit="' . icomplyQualityFloorH($name)
        . '" data-credit-stamped="1" width="1200" height="800"><figcaption>Image credit: '
        . icomplyQualityFloorH($name) . '</figcaption></figure>';
}

function icomplyQualityFloorScrubPricing(string $html, string $path): string
{
    if (!preg_match('#/(?:fencing|drainage)(?:/|$)#', $path)) {
        return $html;
    }
    $out = preg_replace('/fixed pricing/i', 'a written quote', $html);
    return is_string($out) ? $out : $html;
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
            $a1 = 'Call 07517 806082 or WhatsApp the same number. The office is 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. You get a written quote before work is agreed.';
            $q2 = 'Who attends in ' . $place . '?';
            $a2 = 'iComply arranges the visit from Stockport. Gas work is carried out by Gas Safe registered engineers.';
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
    $html = icomplyStampImageCredits($html);
    return icomplyQualityFloorScrubPricing($html, $path);
}
