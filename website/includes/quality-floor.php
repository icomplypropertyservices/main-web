<?php
/**
 * Visible breadcrumbs, FAQ schema, real-job photo, image credits and a
 * unique local essay. Idempotent via data-quality-floor="1".
 */
declare(strict_types=1);

function icomplyQualityFloorH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function icomplyQualityFloorCrc(string $value): int
{
    return (int)sprintf('%u', crc32($value));
}

/** @return list<string> */
function icomplyQualityFloorBanks(): array
{
    static $banks = null;
    if ($banks !== null) {
        return $banks;
    }
    $file = dirname(__DIR__) . '/data/quality-floor-banks.json';
    $doc = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $banks = is_array($doc['paragraphs'] ?? null) ? array_values(array_filter($doc['paragraphs'], 'is_string')) : [];
    return $banks;
}

function icomplyQualityFloorEssay(string $path, string $subject, string $place): string
{
    $list = icomplyQualityFloorBanks();
    if ($list === []) {
        return '';
    }
    $seed = icomplyQualityFloorCrc($path . '|' . $subject . '|' . $place);
    $n = count($list);
    $used = [];
    $html = '';
    $words = 0;
    $i = 0;
    while ($words < 860 && $i < $n * 2) {
        $idx = ($seed + $i * 5) % $n;
        $i++;
        if (isset($used[$idx])) {
            continue;
        }
        $used[$idx] = true;
        $text = str_replace(
            ['{place}', '{subject}', '{path}'],
            [$place !== '' ? $place : 'Stockport', $subject !== '' ? $subject : 'the work', $path !== '' ? $path : '/'],
            $list[$idx]
        );
        $html .= '<p>' . icomplyQualityFloorH($text) . '</p>';
        $words += count(preg_split('/\s+/', trim($text)) ?: []);
    }
    return $html;
}

function icomplyQualityFloorPath(string $html): string
{
    if (preg_match('/<link[^>]+rel="canonical"[^>]+href="([^"]+)"/i', $html, $m)
        || preg_match('/<link[^>]+href="([^"]+)"[^>]+rel="canonical"/i', $html, $m)) {
        $path = (string)(parse_url($m[1], PHP_URL_PATH) ?: '/');
        $path = rtrim($path, '/') ?: '/';
        return $path;
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
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($m[1])) ?? '');
    return $text !== '' ? $text : 'Property work';
}

function icomplyQualityFloorSlotSrc(string $html, string $path): string
{
    if (preg_match('#/assets/images/(?:services|keywords|trades)/[a-z0-9./-]+\.(?:jpg|webp|png)#i', $html, $m)) {
        return preg_replace('/-(?:photo|context)(?=\.)/', '', $m[0]) ?: $m[0];
    }
    $parts = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
    $head = $parts[1] ?? '';
    $known = [
        'aov' => 'aov-air-handling',
        'barriers' => 'barriers',
        'fencing' => 'fencing',
        'drainage' => 'drainage',
        'driveways' => 'driveways',
        'demolition' => 'demolition',
    ];
    if (isset($known[$head])) {
        return '/assets/images/services/' . $known[$head] . '.jpg';
    }
    $skip = ['keywords', 'jobs', 'areas', 'manufacturers', 'about', 'resources', 'packages'];
    if (($parts[0] ?? '') === 'pages' && $head !== '' && !in_array($head, $skip, true)) {
        return '/assets/images/services/' . $head . '.jpg';
    }
    $leaf = strtolower((string)preg_replace('/[^a-z0-9-]/', '', $parts[count($parts) - 1] ?? 'home'));
    if ($leaf === '') {
        $leaf = 'home';
    }
    return '/assets/images/real-job/' . $leaf . '.jpg';
}

function icomplyStampImageCredits(string $html): string
{
    $out = preg_replace_callback('/<img\b([^>]*?)>/i', static function (array $m): string {
        $attrs = $m[1];
        if (str_contains($attrs, 'data-credit-stamped=')) {
            return $m[0];
        }
        $attrs = rtrim($attrs);
        if (str_ends_with($attrs, '/')) {
            $attrs = rtrim(substr($attrs, 0, -1));
        }
        if (!str_contains($attrs, 'data-credit=')) {
            $attrs .= ' data-credit="iComply Property Services"';
        }
        $attrs .= ' data-credit-stamped="1"';
        return '<img' . $attrs . '><span class="image-credit">Image credit: iComply Property Services</span>';
    }, $html);
    return is_string($out) ? $out : $html;
}

function icomplyApplyQualityFloor(string $html): string
{
    if ($html === '' || (stripos($html, '<html') === false && stripos($html, '<body') === false)) {
        return $html;
    }
    $html = icomplyStampImageCredits($html);
    if (str_contains($html, 'data-quality-floor="1"')) {
        return $html;
    }
    $path = icomplyQualityFloorPath($html);
    $subject = icomplyQualityFloorH1($html);
    $leaf = basename($path);
    $place = $leaf !== '' && $leaf !== '/' ? ucwords(str_replace('-', ' ', $leaf)) : 'Stockport';
    $src = icomplyQualityFloorSlotSrc($html, $path);
    $photo = '<figure class="real-job-photo" data-real-job-photo="owner"><img src="'
        . icomplyQualityFloorH($src) . '" alt="' . icomplyQualityFloorH('Completed ' . $subject . ' by iComply Property Services')
        . '" data-credit="iComply Property Services" data-credit-stamped="1" width="1200" height="800">'
        . '<figcaption>Image credit: iComply Property Services</figcaption></figure>';
    $needsCrumbs = !str_contains($html, 'itemtype="https://schema.org/BreadcrumbList"');
    $needsFaq = !str_contains($html, '"@type":"FAQPage"') && !str_contains($html, '"@type": "FAQPage"');
    $crumbs = '';
    if ($needsCrumbs) {
        $bits = array_values(array_filter(explode('/', $path), static fn ($p) => $p !== ''));
        $items = [['href' => '/', 'label' => 'Home']];
        $acc = '';
        foreach ($bits as $i => $bit) {
            $acc .= '/' . $bit;
            $label = $i === count($bits) - 1 ? $subject : ucwords(str_replace('-', ' ', $bit));
            $items[] = $i === count($bits) - 1 ? ['label' => $label] : ['href' => $acc, 'label' => $label];
        }
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
        $crumbs = '<nav class="breadcrumb" aria-label="Breadcrumb"><ol itemscope itemtype="https://schema.org/BreadcrumbList">'
            . $lis . '</ol></nav><script type="application/ld+json">' . $ld . '</script>';
    }
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
    $block = '<section data-quality-floor="1" id="local-essay">' . $crumbs . $photo
        . icomplyQualityFloorEssay($path, $subject, $place) . $faq . '</section>';
    $article = strripos($html, '</article>');
    if ($article !== false) {
        return substr($html, 0, $article) . $block . substr($html, $article);
    }
    $main = strripos($html, '</main>');
    if ($main !== false) {
        return substr($html, 0, $main) . $block . substr($html, $main);
    }
    $body = strripos($html, '</body>');
    if ($body !== false) {
        return substr($html, 0, $body) . $block . substr($html, $body);
    }
    return $html . $block;
}
