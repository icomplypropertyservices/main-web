<?php
/**
 * Manchester electrical P0 hubs and GM-core 60 town pages.
 * Spec: assets/matrix/manchester-electrical-p0.json
 */
declare(strict_types=1);

const MANCHESTER_ELECTRICAL_P0_SITE = 'https://icomplypropertyservices.co.uk';

function manchesterElectricalP0Spec(): array
{
    static $spec = null;
    if ($spec !== null) {
        return $spec;
    }
    $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/assets/matrix/manchester-electrical-p0.json';
    $decoded = json_decode((string)file_get_contents($file), true);
    $spec = is_array($decoded) ? $decoded : [];
    return $spec;
}

function manchesterElectricalP0Hash(string $value): int
{
    $h = 2166136261;
    $len = strlen($value);
    for ($i = 0; $i < $len; $i++) {
        $h ^= ord($value[$i]);
        $h = ($h * 16777619) & 0xFFFFFFFF;
    }
    return $h;
}

function manchesterElectricalP0Fill(string $template, array $vars): string
{
    return (string)preg_replace_callback('/\{(\w+)\}/', static function (array $m) use ($vars): string {
        return (string)($vars[$m[1]] ?? '');
    }, $template);
}

function manchesterElectricalP0Intent(string $slug): ?array
{
    foreach (manchesterElectricalP0Spec()['intents'] ?? [] as $intent) {
        if (is_array($intent) && (string)($intent['slug'] ?? '') === $slug) {
            return $intent;
        }
    }
    return null;
}

function manchesterElectricalP0Town(string $value): ?array
{
    $slug = function_exists('areaSlug') ? areaSlug($value) : strtolower($value);
    foreach (manchesterElectricalP0Spec()['towns'] ?? [] as $town) {
        if (!is_array($town)) {
            continue;
        }
        if ((string)($town['slug'] ?? '') === $slug || strcasecmp((string)($town['name'] ?? ''), $value) === 0) {
            return $town;
        }
    }
    return null;
}

function manchesterElectricalP0Meta(string $name, string $where): string
{
    $bits = [
        ' Landlords, agents and commercial sites.',
        ' Qualified electricians to BS 7671.',
        ' Written scope from Stockport.',
        ' Request a quote — POA.',
        ' Greater Manchester cover.',
    ];
    $fillers = [' POA.', ' SK2 5DE.', ' Offerton.', ' Quote POA.', ' GM electricians.'];
    $text = $name . ' in ' . $where . '.';
    if (mb_strlen($text) > 160) {
        $text = $name . '. ' . $where . '.';
    }
    foreach (array_merge($bits, $fillers) as $bit) {
        if (mb_strlen($text) >= 140 && mb_strlen($text) <= 160) {
            break;
        }
        if (mb_strlen($text) + mb_strlen($bit) <= 160) {
            $text .= $bit;
        }
    }
    return $text;
}

function manchesterElectricalP0Title(string $name, string $where, bool $isHub): string
{
    $base = $isHub ? $name : ($name . ' in ' . $where);
    $brand = ' | iComply Property Services';
    if (mb_strlen($base . $brand) <= 70) {
        return $base . $brand;
    }
    return $base . ' — iComply';
}

/**
 * @return array{html:string,path:string,canonical:string,title:string,description:string,words:int}|null
 */
function manchesterElectricalP0Render(string $surface, string $slug, string $townSlug = ''): ?array
{
    $surface = $surface === 'job' ? 'job' : 'keyword';
    $intent = manchesterElectricalP0Intent($slug);
    if ($intent === null) {
        return null;
    }
    $kind = (string)($intent['kind'] ?? 'both');
    if ($surface === 'job' && $kind === 'keyword') {
        return null;
    }
    if ($surface === 'keyword' && $kind === 'job') {
        return null;
    }
    $isHub = $townSlug === '';
    $town = $isHub ? null : manchesterElectricalP0Town($townSlug);
    if (!$isHub && $town === null) {
        return null;
    }
    $spec = manchesterElectricalP0Spec();
    $where = $isHub ? 'Greater Manchester' : (string)$town['name'];
    $fact = $isHub
        ? 'Manchester is the primary hub city, and the team is based in Offerton, Stockport'
        : (string)$town['fact'];
    $related = manchesterElectricalP0Intent((string)($intent['related'] ?? ''));
    $relatedName = $related['name'] ?? 'EICR';
    $relatedSlug = $related['slug'] ?? 'eicr';
    $vars = [
        'intent' => (string)$intent['name'],
        'town' => $where,
        'local' => $fact,
        'nap' => (string)$spec['nap'],
        'phone' => (string)$spec['phone'],
        'related' => (string)$relatedName,
    ];
    $paragraphs = [
        manchesterElectricalP0Fill((string)$spec['intro'], $vars),
        (string)$intent['angle'],
        $where . ' is on the Greater Manchester electrical matrix. ' . $fact . '. Travel for ' . $intent['name'] . ' is planned from ' . $spec['nap'] . '. Landlords, agents and commercial occupiers can combine certificates, fault finding, board changes and rewires on one enquiry, with each item scoped in writing.',
    ];
    $blocks = $spec['blocks'] ?? [];
    $seed = manchesterElectricalP0Hash($surface . '|' . $intent['slug'] . '|' . ($isHub ? 'hub' : $town['slug']));
    $order = array_keys($blocks);
    for ($i = count($order) - 1; $i > 0; $i--) {
        $j = ($seed + $i * 17) % ($i + 1);
        $swap = $order[$i];
        $order[$i] = $order[$j];
        $order[$j] = $swap;
    }
    foreach ($order as $index) {
        $paragraphs[] = manchesterElectricalP0Fill((string)$blocks[$index], $vars);
    }
    $paragraphs[] = manchesterElectricalP0Fill((string)$spec['closer'], $vars);

    $prefix = $surface === 'job' ? '/pages/jobs/' : '/pages/keywords/';
    $path = $prefix . $intent['slug'] . ($isHub ? '' : '/' . $town['slug']);
    $canonical = MANCHESTER_ELECTRICAL_P0_SITE . $path;
    $title = manchesterElectricalP0Title((string)$intent['name'], $where, $isHub);
    $description = manchesterElectricalP0Meta((string)$intent['name'], $where);
    $h1 = $isHub ? ($intent['name'] . ' across Greater Manchester') : ($intent['name'] . ' in ' . $where);
    $images = $spec['images'] ?? [];
    $picked = [];
    $imageCount = count($images);
    for ($step = 0; $imageCount > 0 && count($picked) < 3; $step++) {
        $picked[] = $images[($seed + $step) % $imageCount];
    }
    while (count($picked) < 3) {
        $picked[] = '/assets/images/services/electrical.jpg';
    }
    $ogImage = MANCHESTER_ELECTRICAL_P0_SITE . $picked[0];
    $area = $isHub ? 'manchester' : (string)$town['slug'];
    $faqs = [];
    foreach ($spec['faqs'] ?? [] as $faq) {
        if (!is_array($faq)) {
            continue;
        }
        $faqs[] = [
            manchesterElectricalP0Fill((string)($faq['q'] ?? ''), $vars),
            manchesterElectricalP0Fill((string)($faq['a'] ?? ''), $vars),
        ];
    }
    if ($intent['slug'] === 'nic-electrician') {
        $faqs[] = [
            'Are you NICEIC registered?',
            'No. iComply does not claim NICEIC, NAPIT or Elecsa membership. Ask what the attending electrician can show, and we will say if a named scheme contractor is required.',
        ];
    }
    if ($intent['slug'] === 'eicr') {
        $faqs[] = [
            'Is there a published EICR price?',
            'The published EICR list price is £249 for a typical North West 6-bed HMO. Other domestic sizes and commercial EICRs stay price on application.',
        ];
    }
    $h = static function (string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    };
    $paraHtml = '';
    foreach ($paragraphs as $paragraph) {
        $paraHtml .= '<p>' . $h($paragraph) . '</p>';
    }
    $imageHtml = '';
    foreach ($picked as $index => $src) {
        $imageHtml .= '<figure><img src="' . $h((string)$src) . '" alt="' . $h($intent['name'] . ' in ' . $where . ' — photograph ' . ($index + 1)) . '" width="1200" height="800"><figcaption>' . $h($intent['name'] . ' · ' . $where) . '</figcaption></figure>';
    }
    $faqHtml = '';
    $faqEntities = [];
    foreach ($faqs as $faq) {
        $faqHtml .= '<details><summary>' . $h($faq[0]) . '</summary><p>' . $h($faq[1]) . '</p></details>';
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
        ];
    }
    $sibling = '';
    if ($kind === 'both') {
        $siblingSurface = $surface === 'job' ? 'keywords' : 'jobs';
        $sibling = '/pages/' . $siblingSurface . '/' . $intent['slug'] . ($isHub ? '' : '/' . $town['slug']);
    }
    $links = '<a href="' . $h((string)($spec['service_path'] ?? '/pages/services/electrical')) . '">Electrical services</a>'
        . ' · <a href="/pages/keywords/' . $h((string)$relatedSlug) . '">' . $h((string)$relatedName) . '</a>'
        . ' · <a href="/pages/areas/' . $h($area) . '">' . $h($where === 'Greater Manchester' ? 'Manchester' : $where) . ' area</a>'
        . ' · <a href="/pages/keywords/' . $h((string)$intent['slug']) . '/manchester">' . $h((string)$intent['name']) . ' in Manchester</a>';
    if ($sibling !== '') {
        $links .= ' · <a href="' . $h($sibling) . '">' . $h($surface === 'job' ? 'Keyword guide' : 'Job page') . '</a>';
    }
    $faqSchema = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $crumbTail = $isHub ? '' : (' / ' . $h($where));
    $section = $surface === 'job' ? 'Jobs' : 'Keywords';
    $sectionHref = $surface === 'job' ? '/pages/jobs' : '/pages/keywords';
    $html = '<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . $h($title) . '</title>
<meta name="description" content="' . $h($description) . '">
<meta name="robots" content="index, follow">
<link rel="canonical" href="' . $h($canonical) . '">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_GB">
<meta property="og:site_name" content="iComply Property Services">
<meta property="og:title" content="' . $h($title) . '">
<meta property="og:description" content="' . $h($description) . '">
<meta property="og:url" content="' . $h($canonical) . '">
<meta property="og:image" content="' . $h($ogImage) . '">
<meta property="og:image:alt" content="' . $h($intent['name'] . ' in ' . $where) . '">
</head>
<body>
<header>
<p><a href="/">iComply Property Services</a> · <a href="/pages/services/electrical">Electrical</a> · <a href="/pages/keywords">Keywords</a> · <a href="/pages/jobs">Jobs</a> · <a href="/pages/areas">Areas</a> · <a href="/contact">Contact</a></p>
<p>' . $h((string)$spec['nap']) . ' · ' . $h((string)$spec['phone']) . '</p>
</header>
<main>
<p><a href="/">Home</a> / <a href="' . $sectionHref . '">' . $section . '</a> / ' . $h((string)$intent['name']) . $crumbTail . '</p>
<h1>' . $h($h1) . '</h1>
<article id="guide">
' . $paraHtml . '
<h2>Photographs</h2>
' . $imageHtml . '
<h2>' . $h((string)$intent['name']) . ' FAQ</h2>
' . $faqHtml . '
</article>
<p>' . $links . '</p>
</main>
<footer>
<p>iComply Property Services, ' . $h((string)$spec['nap']) . '. Phone ' . $h((string)$spec['phone']) . '. Electrical quotes are price on application.</p>
</footer>
<script type="application/ld+json">' . str_replace('<', '\\u003c', (string)$faqSchema) . '</script>
</body>
</html>';
    $words = preg_split('/\s+/', trim(implode(' ', $paragraphs))) ?: [];
    return [
        'html' => $html,
        'path' => $path,
        'canonical' => $canonical,
        'title' => $title,
        'description' => $description,
        'words' => count(array_filter($words, static fn($w) => $w !== '')),
    ];
}

function manchesterElectricalP0Emit(string $surface, string $slug, string $townSlug = ''): bool
{
    $page = manchesterElectricalP0Render($surface, $slug, $townSlug);
    if ($page === null) {
        return false;
    }
    echo $page['html'];
    return true;
}

function manchesterElectricalP0Html(string $surface, string $slug, string $townSlug = ''): string
{
    $page = manchesterElectricalP0Render($surface, $slug, $townSlug);
    return $page['html'] ?? '';
}

function manchesterElectricalP0Handle(string $surface, string $slug, string $townSlug = ''): bool
{
    if ($surface === 'job' && $townSlug === '') {
        $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/pages/jobs/' . $slug . '.php';
        if (is_file($file) && !str_contains((string)file_get_contents($file), 'manchesterElectricalP0Emit')) {
            return false;
        }
    }
    return manchesterElectricalP0Emit($surface, $slug, $townSlug);
}

function manchesterElectricalP0TownIndexable(string $surface, string $slug, string $townSlug): bool
{
    return manchesterElectricalP0Render($surface, $slug, $townSlug) !== null;
}

/** @return list<string> */
function manchesterElectricalP0JobTownPaths(): array
{
    $paths = [];
    foreach (manchesterElectricalP0Spec()['intents'] ?? [] as $intent) {
        if (!is_array($intent) || (string)($intent['kind'] ?? '') === 'keyword') {
            continue;
        }
        $slug = (string)($intent['slug'] ?? '');
        if ($slug === '') {
            continue;
        }
        foreach (manchesterElectricalP0Spec()['towns'] ?? [] as $town) {
            $townSlug = (string)($town['slug'] ?? '');
            if ($townSlug !== '') {
                $paths[] = '/pages/jobs/' . $slug . '/' . $townSlug;
            }
        }
    }
    return $paths;
}
