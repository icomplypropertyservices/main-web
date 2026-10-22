<?php
/**
 * Building services P0 on the Manchester + Burnley dual ring (269 towns).
 * Keyword hubs, job hubs, keyword×town and job×town. P1/P2 stay out.
 */
declare(strict_types=1);

function icomplyBuildingDualPack(): array
{
    static $pack = null;
    if ($pack !== null) {
        return $pack;
    }
    $file = SITE_ROOT . '/data/building-services-dual-p0.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $pack = is_array($decoded) ? $decoded : ['slugs' => [], 'towns' => []];
    return $pack;
}

/** @return array<string,array<string,mixed>> */
function icomplyBuildingDualSlugMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    foreach (icomplyBuildingDualPack()['slugs'] ?? [] as $row) {
        if (is_array($row) && !empty($row['slug'])) {
            $map[(string)$row['slug']] = $row;
        }
    }
    return $map;
}

/** @return array<string,array<string,mixed>> */
function icomplyBuildingDualTownMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    foreach (icomplyBuildingDualPack()['towns'] ?? [] as $row) {
        if (is_array($row) && !empty($row['slug'])) {
            $map[(string)$row['slug']] = $row;
        }
    }
    return $map;
}

function icomplyBuildingDualIsP0(string $slug): bool
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    return isset(icomplyBuildingDualSlugMap()[$slug]);
}

function icomplyBuildingDualCovers(string $slug, string $town): bool
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    $town = function_exists('areaSlug') ? areaSlug($town) : strtolower($town);
    return isset(icomplyBuildingDualSlugMap()[$slug], icomplyBuildingDualTownMap()[$town]);
}

function icomplyBuildingDualCoversPath(string $path): bool
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if (!preg_match('#^/pages/(keywords|jobs)/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return false;
    }
    return icomplyBuildingDualCovers($m[2], $m[3]);
}

/** @return list<string> */
function icomplyBuildingDualSlugs(): array
{
    return array_keys(icomplyBuildingDualSlugMap());
}

/**
 * P0 keyword × dual town paths that are not already in the GM place set.
 *
 * @param array<string,mixed> $gmPlaces
 * @return list<string>
 */
function icomplyBuildingDualExtraKeywordTownPaths(array $gmPlaces = []): array
{
    $paths = [];
    foreach (icomplyBuildingDualSlugs() as $slug) {
        foreach (icomplyBuildingDualTownMap() as $townSlug => $town) {
            if (isset($gmPlaces[$townSlug])) {
                continue;
            }
            $paths[] = '/pages/keywords/' . $slug . '/' . $townSlug;
        }
    }
    return $paths;
}

/** @return list<string> */
function icomplyBuildingDualJobTownPaths(): array
{
    $paths = [];
    foreach (icomplyBuildingDualSlugs() as $slug) {
        foreach (array_keys(icomplyBuildingDualTownMap()) as $townSlug) {
            $paths[] = '/pages/jobs/' . $slug . '/' . $townSlug;
        }
    }
    return $paths;
}

function icomplyBuildingDualH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function icomplyBuildingDualFitTitle(string $raw): string
{
    $title = trim(preg_replace('/\s+/u', ' ', $raw) ?? $raw);
    if (stripos($title, 'icomply') === false) {
        $full = $title . ' | iComply Property Services';
        $short = $title . ' | iComply';
        $title = mb_strlen($full) <= 65 ? $full : $short;
    }
    if (mb_strlen($title) > 65) {
        $cut = rtrim(mb_substr($title, 0, 65));
        $word = preg_replace('/\s+\S*$/u', '', $cut);
        $title = (is_string($word) && mb_strlen($word) >= 30) ? $word : $cut;
    }
    if (mb_strlen($title) < 30) {
        $padded = $title . ' | iComply Property Services';
        $title = mb_strlen($padded) <= 65 ? $padded : rtrim(mb_substr($padded, 0, 65));
    }
    return $title;
}

function icomplyBuildingDualFitMeta(string $raw): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $raw) ?? $raw);
    foreach ([' Request a quote — POA.', ' Call 07517806082.', ' Stockport SK2.', ' Manchester and Burnley.'] as $pad) {
        if (mb_strlen($text) >= 140) {
            break;
        }
        $text .= $pad;
    }
    if (mb_strlen($text) > 160) {
        $cut = rtrim(mb_substr($text, 0, 157));
        $word = preg_replace('/\s+\S*$/u', '', $cut);
        $text = (is_string($word) && mb_strlen($word) >= 70) ? $word : rtrim(mb_substr($text, 0, 159));
    }
    return $text;
}

/** @param array<string,mixed> $town */
function icomplyBuildingDualPopSentence(array $town): string
{
    $row = (int)($town['row'] ?? 0);
    $pop = $town['population'] ?? null;
    if ($pop === null || $pop === '' || (int)$pop === 0) {
        return 'The dual-ring allowlist does not print a population on row ' . $row . '.';
    }
    return 'The allowlist population for row ' . $row . ' is ' . number_format((int)$pop) . '.';
}

/** @param array<string,mixed> $town */
function icomplyBuildingDualMileSentence(array $town): string
{
    $row = (int)($town['row'] ?? 0);
    if (!isset($town['mi_manchester'], $town['mi_burnley']) || $town['mi_manchester'] === null || $town['mi_burnley'] === null) {
        return 'Miles from Manchester and Burnley are not printed on row ' . $row . '.';
    }
    return 'Straight-line figures on the allowlist put row ' . $row . ' about ' . $town['mi_manchester'] . ' miles from Manchester and ' . $town['mi_burnley'] . ' miles from Burnley.';
}

/**
 * @param array<string,mixed> $meta
 * @param array<string,mixed> $town
 * @return list<string>
 */
function icomplyBuildingDualTownParagraphs(array $meta, array $town, string $surface): array
{
    $pack = icomplyBuildingDualPack();
    $cluster = $pack['clusters'][$meta['cluster']] ?? ['facts' => []];
    $facts = $cluster['facts'] ?? [];
    $sections = $pack['sections'] ?? [];
    $label = $surface === 'job' ? 'job page' : 'keyword guide';
    $out = [];
    foreach ($sections as $index => $heading) {
        $fact = (string)($facts[$index % max(1, count($facts))] ?? '');
        $out[] = $meta['name'] . ' in ' . $town['name'] . ' is the ' . $label . ' for “' . $heading . '”. '
            . 'District marker ' . $town['district'] . ', dual-ring row ' . $town['row'] . ', ' . $town['county'] . '. '
            . icomplyBuildingDualPopSentence($town) . ' ' . icomplyBuildingDualMileSentence($town) . ' '
            . $meta['angle'] . ' ' . $fact . ' '
            . 'The quote on this ' . $label . ' is price on application. Call ' . ($pack['phone'] ?? '07517806082') . '. '
            . 'Workshop: ' . ($pack['nap'] ?? '') . '.';
    }
    return $out;
}

/**
 * @param array<string,mixed> $meta
 * @return list<string>
 */
function icomplyBuildingDualHubParagraphs(array $meta, string $surface): array
{
    $pack = icomplyBuildingDualPack();
    $towns = icomplyBuildingDualTownMap();
    $manchester = $towns['manchester'] ?? ['name' => 'Manchester', 'district' => 'M1 Piccadilly', 'row' => 0];
    $burnley = $towns['burnley'] ?? ['name' => 'Burnley', 'district' => 'BB11 the town hall', 'row' => 0];
    $cluster = $pack['clusters'][$meta['cluster']] ?? ['facts' => []];
    $facts = $cluster['facts'] ?? [];
    $sections = $pack['sections'] ?? [];
    $label = $surface === 'job' ? 'job hub' : 'keyword guide';
    $nap = (string)($pack['nap'] ?? '');
    $phone = (string)($pack['phone'] ?? '07517806082');
    $out = [];
    foreach ($sections as $index => $heading) {
        $fact = (string)($facts[$index % max(1, count($facts))] ?? '');
        $out[] = $meta['name'] . ' is the ' . $label . ' section “' . $heading . '” for the Manchester and Burnley dual ring. '
            . 'Manchester is district ' . $manchester['district'] . ' on row ' . $manchester['row'] . '. '
            . 'Burnley is district ' . $burnley['district'] . ' on row ' . $burnley['row'] . '. '
            . $meta['angle'] . ' ' . $fact . ' '
            . 'Town pages for this intent use the 269-place allowlist only. The quote is price on application. '
            . 'Call ' . $phone . '. Workshop: ' . $nap . '.';
    }
    return $out;
}

/**
 * @param array<string,array<string,mixed>> $keywords
 * @return array<string,array<string,mixed>>
 */
function icomplyBuildingDualApplyKeywords(array $keywords): array
{
    $pack = icomplyBuildingDualPack();
    foreach (icomplyBuildingDualSlugMap() as $slug => $meta) {
        $name = (string)$meta['name'];
        $keywords[$slug] = [
            'name' => $name,
            'service' => (string)$meta['service'],
            'related' => (string)($meta['related'] ?? $slug),
            'intro' => (string)$meta['angle'],
            'body' => $name . ' is arranged from ' . ($pack['nap'] ?? '') . '. The quote is price on application.',
            'meta_desc' => icomplyBuildingDualFitMeta($name . ' for landlords and agents across the Manchester and Burnley dual ring. Price on application.'),
            'seo_title' => icomplyBuildingDualFitTitle($name . ' guide'),
            'h1' => $name . ' from Stockport',
            'focus_points' => [
                'Scope written before a date is fixed',
                'Price on application',
                'Manchester and Burnley dual ring, 269 places',
                'Workshop at ' . ($pack['nap'] ?? ''),
            ],
            'faq' => [
                ['Where is the workshop?', 'The workshop address is ' . ($pack['nap'] ?? '') . '.'],
                ['Do you publish a price?', 'No. The quote is price on application after the building is known.'],
                ['Which towns are in this wave?', 'The 269 places on the Manchester and Burnley dual-ring allowlist.'],
            ],
            'seo_keywords' => $name . ', Stockport, Manchester, Burnley',
            'building_dual_p0' => true,
        ];
    }
    return $keywords;
}

function icomplyBuildingDualRenderHub(string $surface, string $slug): void
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    $meta = icomplyBuildingDualSlugMap()[$slug] ?? null;
    if (!is_array($meta)) {
        http_response_code(404);
        echo 'Page not found';
        return;
    }
    $pack = icomplyBuildingDualPack();
    $surface = $surface === 'job' ? 'job' : 'keyword';
    $paras = icomplyBuildingDualHubParagraphs($meta, $surface);
    $name = (string)$meta['name'];
    $h1 = $surface === 'job' ? ($name . ' job from Stockport') : ($name . ' from Stockport');
    if (mb_strlen($h1) < 12) {
        $h1 = $name . ' service from Stockport';
    }
    $title = icomplyBuildingDualFitTitle($surface === 'job' ? ($name . ' job') : ($name . ' guide'));
    $description = icomplyBuildingDualFitMeta($name . ' for landlords and agents on the Manchester and Burnley dual ring. Price on application.');
    $path = ($surface === 'job' ? '/pages/jobs/' : '/pages/keywords/') . $slug;
    $canonical = function_exists('url') ? url($path) : $path;
    $images = $pack['clusters'][$meta['cluster']]['images'] ?? [];
    $og = 'https://icomplypropertyservices.co.uk' . ($images[0] ?? '/assets/images/og-default.svg');
    $pageTitle = $title;
    $metaDesc = $description;
    $canonicalUrl = $canonical;
    $ogTitle = $title;
    $ogDescription = $description;
    $ogImage = $og;
    $metaKeywords = $name . ', Stockport, Manchester, Burnley';
    $omitPriceRange = true;
    $seoFamily = $surface === 'job' ? 'keyword-hub' : 'keyword-hub';
    $h = 'icomplyBuildingDualH';
    require SITE_ROOT . '/includes/header.php';
    echo '<nav aria-label="Breadcrumb"><a href="' . $h(url('/')) . '">Home</a> / <span>' . $h($h1) . '</span></nav>';
    echo '<h1>' . $h($h1) . '</h1>';
    foreach ($images as $i => $src) {
        $lazy = $i === 0 ? '' : ' loading="lazy"';
        echo '<figure><img src="' . $h(url($src)) . '" alt="' . $h($name . ' image ' . ($i + 1)) . '" width="1200" height="800"' . $lazy . '></figure>';
    }
    echo '<article data-seo-faq="1">';
    $sections = $pack['sections'] ?? [];
    foreach ($paras as $i => $paragraph) {
        $heading = (string)($sections[$i] ?? 'Scope');
        echo '<h2>' . $h($heading) . '</h2><p>' . $h($paragraph) . '</p>';
    }
    if (!empty($meta['gas'])) {
        echo '<p>' . $h((string)$pack['gas_duty']) . ' ' . $h((string)$pack['gas_denial']) . '</p>';
    } else {
        echo '<p>' . $h((string)$pack['arrange']) . '</p>';
    }
    $other = $surface === 'job' ? '/pages/keywords/' . $slug : '/pages/jobs/' . $slug;
    echo '<p><a href="' . $h(url($other)) . '">' . $h($name . ($surface === 'job' ? ' guide' : ' job')) . '</a> · '
        . '<a href="' . $h(url('/pages/services/' . $meta['service'])) . '">' . $h(str_replace('-', ' ', (string)$meta['service'])) . '</a> · '
        . '<a href="' . $h(url('/pages/keywords/' . $slug . '/manchester')) . '">' . $h($name . ' in Manchester') . '</a> · '
        . '<a href="' . $h(url('/pages/keywords/' . $slug . '/burnley')) . '">' . $h($name . ' in Burnley') . '</a> · '
        . '<a href="' . $h(url('/pages/jobs/' . $slug . '/stockport')) . '">' . $h($name . ' job in Stockport') . '</a> · '
        . '<a href="' . $h(url('/pages/areas/manchester')) . '">Manchester area hub</a> · '
        . '<a href="' . $h(url('/pages/areas/stockport')) . '">Stockport area hub</a></p>';
    echo '<h2>Questions</h2>';
    $faqs = [
        ['What area does this hub cover?', 'The Manchester and Burnley dual ring: 269 places on the allowlist. Town pages are linked from Manchester, Burnley and Stockport.'],
        ['Is there a published price?', 'No. The quote is price on application after the building, access and scope are known.'],
        ['Where is the workshop?', 'The workshop is ' . ($pack['nap'] ?? '') . '. Call ' . ($pack['phone'] ?? '07517806082') . '.'],
    ];
    if (!empty($meta['gas'])) {
        $faqs[] = ['Who carries out the gas work?', (string)$pack['gas_duty'] . ' ' . (string)$pack['gas_denial']];
    }
    foreach ($faqs as [$q, $a]) {
        echo '<details><summary>' . $h($q) . '</summary><p>' . $h($a) . '</p></details>';
    }
    echo '</article>';
    require SITE_ROOT . '/includes/footer.php';
}

function icomplyBuildingDualRenderTown(string $surface, string $slug, string $townSlug): void
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    $townSlug = function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug);
    $meta = icomplyBuildingDualSlugMap()[$slug] ?? null;
    $town = icomplyBuildingDualTownMap()[$townSlug] ?? null;
    if (!is_array($meta) || !is_array($town)) {
        http_response_code(404);
        echo 'Page not found';
        return;
    }
    $surface = $surface === 'job' ? 'job' : 'keyword';
    $pack = icomplyBuildingDualPack();
    $paras = icomplyBuildingDualTownParagraphs($meta, $town, $surface);
    $name = (string)$meta['name'];
    $h1 = ($surface === 'job' ? $name . ' job in ' : $name . ' in ') . $town['name'];
    if (mb_strlen($h1) < 12) {
        $h1 = $name . ' service in ' . $town['name'];
    }
    $title = icomplyBuildingDualFitTitle($h1);
    $description = icomplyBuildingDualFitMeta($name . ' in ' . $town['name'] . ' (' . $town['district'] . '). ' . $town['county'] . '. Price on application from Stockport.');
    $path = ($surface === 'job' ? '/pages/jobs/' : '/pages/keywords/') . $slug . '/' . $townSlug;
    $canonical = 'https://icomplypropertyservices.co.uk' . $path;
    $images = $pack['clusters'][$meta['cluster']]['images'] ?? [];
    $h = 'icomplyBuildingDualH';
    $pageTitle = $title;
    $metaDesc = $description;
    $canonicalUrl = $canonical;
    $ogTitle = $title;
    $ogDescription = $description;
    $ogImage = 'https://icomplypropertyservices.co.uk' . ($images[0] ?? '/assets/images/og-default.svg');
    $metaKeywords = $name . ', ' . $town['name'] . ', Stockport';
    $metaRobots = 'index, follow';
    $omitPriceRange = true;
    $seoFamily = 'keyword-area';
    require SITE_ROOT . '/includes/header.php';
    echo '<article id="local-copy" data-seo-faq="1">';
    echo '<h1>' . $h($h1) . '</h1>';
    foreach ($images as $i => $src) {
        $lazy = $i === 0 ? '' : ' loading="lazy"';
        echo '<figure><img src="' . $h((string)$src) . '" alt="' . $h($name . ' in ' . $town['name'] . ' ' . ($i + 1)) . '" width="1200" height="800"' . $lazy . '></figure>';
    }
    $sections = $pack['sections'] ?? [];
    foreach ($paras as $i => $paragraph) {
        echo '<h2>' . $h((string)($sections[$i] ?? 'Scope') . ' in ' . $town['name']) . '</h2><p>' . $h($paragraph) . '</p>';
    }
    if (!empty($meta['gas'])) {
        echo '<p>' . $h((string)$pack['gas_duty']) . ' ' . $h((string)$pack['gas_denial']) . '</p>';
    } else {
        echo '<p>' . $h((string)$pack['arrange']) . '</p>';
    }
    $otherSurface = $surface === 'job' ? 'keywords' : 'jobs';
    echo '<p><a href="' . $h(url('/pages/' . ($surface === 'job' ? 'jobs' : 'keywords') . '/' . $slug)) . '">' . $h($name . ' hub') . '</a> · '
        . '<a href="' . $h(url('/pages/' . $otherSurface . '/' . $slug . '/' . $townSlug)) . '">' . $h($name . ' ' . ($surface === 'job' ? 'guide' : 'job') . ' in ' . $town['name']) . '</a> · '
        . '<a href="' . $h(url('/pages/services/' . $meta['service'])) . '">Service</a> · '
        . '<a href="' . $h(url('/pages/keywords/' . $slug . '/manchester')) . '">Manchester</a> · '
        . '<a href="' . $h(url('/pages/keywords/' . $slug . '/burnley')) . '">Burnley</a></p>';
    echo '<h2>Questions about ' . $h((string)$town['name']) . '</h2>';
    $angle = (string)$meta['angle'];
    $faqs = [
        ['What does the page cover in ' . $town['name'] . '?', $angle . ' District ' . $town['district'] . ' on row ' . $town['row'] . '.'],
        ['How is the price set?', 'The quote for ' . $town['name'] . ' is price on application. ' . icomplyBuildingDualPopSentence($town) . ' ' . $angle],
        ['Where is the workshop?', 'Arranged from ' . ($pack['nap'] ?? '') . '. ' . icomplyBuildingDualMileSentence($town) . ' ' . $angle],
    ];
    foreach ($faqs as [$q, $a]) {
        echo '<details><summary>' . $h($q) . '</summary><p>' . $h($a) . '</p></details>';
    }
    echo '</article>';
    require SITE_ROOT . '/includes/footer.php';
}
