<?php
/**
 * Security dual-ring P0 (67 intents × 269 towns).
 * Keyword and job hubs, plus keyword×town and job×town for kind=both.
 * P1 (133) is follow-on and is not rendered here.
 */
declare(strict_types=1);

function securityDualRingPack(): array
{
    static $pack = null;
    if ($pack !== null) {
        return $pack;
    }
    $file = SITE_ROOT . '/data/security-dual-ring-p0.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $pack = is_array($decoded) ? $decoded : ['intents' => [], 'towns' => []];
    return $pack;
}

function securityDualRingIntent(string $slug): ?array
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    $intent = securityDualRingPack()['intents'][$slug] ?? null;
    return is_array($intent) ? $intent : null;
}

function securityDualRingTown(string $slug): ?array
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : strtolower($slug);
    $town = securityDualRingPack()['towns'][$slug] ?? null;
    return is_array($town) ? $town : null;
}

function securityDualRingSurfaceOk(string $surface, string $slug): bool
{
    $intent = securityDualRingIntent($slug);
    if ($intent === null) {
        return false;
    }
    if ($surface === 'job') {
        return ($intent['kind'] ?? '') === 'both';
    }
    return $surface === 'keyword';
}

function securityDualRingHandlesPath(string $path): bool
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    $path = preg_replace('#\.php$#i', '', $path) ?? $path;
    $path = rtrim($path, '/') ?: '/';
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return securityDualRingSurfaceOk('keyword', $m[1]) && securityDualRingTown($m[2]) !== null;
    }
    if (preg_match('#^/pages/jobs/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return securityDualRingSurfaceOk('job', $m[1]) && securityDualRingTown($m[2]) !== null;
    }
    return false;
}

/**
 * Sitemap additions that the Greater Manchester matrix does not already emit.
 *
 * @return list<string>
 */
function securityDualRingSitemapPaths(): array
{
    $pack = securityDualRingPack();
    $gm = [];
    if (!function_exists('icomplyCrawlTownNames')) {
        $crawl = __DIR__ . '/gm-crawl.php';
        if (is_file($crawl)) {
            require_once $crawl;
        }
    }
    if (function_exists('icomplyCrawlTownNames') && function_exists('areaSlug')) {
        foreach (icomplyCrawlTownNames() as $name) {
            $gm[areaSlug((string)$name)] = true;
        }
    }
    $paths = [];
    foreach ($pack['intents'] ?? [] as $slug => $intent) {
        if (!is_array($intent)) {
            continue;
        }
        foreach ($pack['towns'] ?? [] as $townSlug => $_town) {
            if ($townSlug === '' || !is_string($townSlug)) {
                continue;
            }
            if (empty($gm[$townSlug])) {
                $paths[] = '/pages/keywords/' . $slug . '/' . $townSlug;
            }
            if (($intent['kind'] ?? '') === 'both') {
                if ($slug === 'maglock-installation' && !empty($gm[$townSlug])) {
                    continue;
                }
                $paths[] = '/pages/jobs/' . $slug . '/' . $townSlug;
            }
        }
    }
    return $paths;
}

/**
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function securityDualRingApplyKeywords(array $keywords): array
{
    foreach (securityDualRingPack()['intents'] ?? [] as $slug => $intent) {
        if (!is_array($intent)) {
            continue;
        }
        $row = $keywords[$slug] ?? [];
        if (!is_array($row)) {
            $row = [];
        }
        $row['name'] = (string)($intent['name'] ?? $slug);
        $row['service'] = (string)($intent['service'] ?? 'cctv');
        $row['related'] = (string)($intent['related'] ?? $slug);
        $paragraphs = $intent['paragraphs'] ?? [];
        if (is_array($paragraphs) && isset($paragraphs[0]) && is_string($paragraphs[0])) {
            $row['intro'] = $paragraphs[0];
            $row['body'] = implode("\n\n", array_map('strval', $paragraphs));
        }
        if (!empty($intent['meta_hub'])) {
            $row['meta_desc'] = (string)$intent['meta_hub'];
        }
        if (!empty($intent['title_hub'])) {
            $row['seo_title'] = (string)$intent['title_hub'];
        }
        $row['h1'] = (string)($intent['name'] ?? $slug);
        $faqs = [];
        foreach ($intent['faqs'] ?? [] as $faq) {
            if (is_array($faq) && isset($faq['q'], $faq['a'])) {
                $faqs[] = [(string)$faq['q'], (string)$faq['a']];
            }
        }
        if ($faqs) {
            $row['faq'] = $faqs;
        }
        $row['security_dual_ring_p0'] = true;
        $keywords[$slug] = $row;
    }
    return $keywords;
}

function securityDualRingFitMeta(string $text): string
{
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? trim($text);
    if (!str_contains($text, 'POA')) {
        $text = rtrim($text, '.') . '. Request a quote — POA.';
    }
    foreach ([' Call 07517806082.', ' Stockport base.', ' SK2 5DE.'] as $pad) {
        if (mb_strlen($text) >= 140) {
            break;
        }
        if (mb_strlen($text . $pad) <= 160) {
            $text .= $pad;
        }
    }
    if (mb_strlen($text) > 160) {
        $cut = mb_substr($text, 0, 160);
        $pos = mb_strrpos($cut, ' ');
        if ($pos !== false && $pos > 40) {
            $cut = mb_substr($cut, 0, $pos);
        }
        $text = rtrim($cut, " ,;:—-");
        if (!str_contains($text, 'POA')) {
            $suffix = ' POA.';
            $room = 160 - mb_strlen($suffix);
            $cut = mb_substr($text, 0, $room);
            $pos = mb_strrpos($cut, ' ');
            if ($pos !== false && $pos > 40) {
                $cut = mb_substr($cut, 0, $pos);
            }
            $text = rtrim($cut, " ,;:—-") . $suffix;
        }
    }
    if (mb_strlen($text) > 160) {
        $text = rtrim(mb_substr($text, 0, 160));
        $pos = mb_strrpos($text, ' ');
        if ($pos !== false) {
            $text = mb_substr($text, 0, $pos);
        }
    }
    return $text;
}

function securityDualRingTitle(string $name, string $town = ''): string
{
    if ($town === '') {
        $long = $name . ' | iComply Property Services';
        return mb_strlen($long) <= 65 ? $long : ($name . ' — iComply');
    }
    $long = $name . ' in ' . $town . ' | iComply Property Services';
    if (mb_strlen($long) <= 65) {
        return $long;
    }
    return $name . ' in ' . $town . ' — iComply';
}

/**
 * @param array<string, mixed> $intent
 * @param array<string, mixed> $town
 * @return list<string>
 */
function securityDualRingLocalParagraphs(array $intent, array $town): array
{
    $pack = securityDualRingPack();
    $name = (string)($intent['name'] ?? '');
    $townName = (string)($town['name'] ?? '');
    $county = trim((string)($town['county'] ?? ''));
    if ($county === '') {
        $county = 'the dual ring';
    }
    $pop = $town['population'] ?? null;
    if (is_numeric($pop) && (int)$pop > 0) {
        $popSentence = $townName . ' has a published population of about ' . number_format((int)$pop) . '.';
    } else {
        $popSentence = 'A population figure is not invented for ' . $townName . '. The survey uses the address you send.';
    }
    $mm = $town['mi_manchester'] ?? null;
    $mb = $town['mi_burnley'] ?? null;
    if (is_numeric($mm) && is_numeric($mb)) {
        $miles = $townName . ' is about ' . $mm . ' miles from Manchester and about ' . $mb . ' miles from Burnley.';
    } else {
        $miles = 'Miles from Manchester and Burnley for ' . $townName . ' are taken from the postcode on the quote, not from a blank row.';
    }
    $near = [];
    foreach ($town['near'] ?? [] as $nearSlug) {
        $row = $pack['towns'][$nearSlug] ?? null;
        if (is_array($row) && !empty($row['name'])) {
            $near[] = (string)$row['name'];
        }
    }
    $nearText = $near ? implode(', ', $near) : 'the neighbouring towns on the dual ring';
    $nap = (string)($pack['nap'] ?? '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE');
    $phone = (string)($pack['phone'] ?? '07517806082');
    return [
        $name . ' in ' . $townName . ' is scoped for buildings in ' . $county . '. ' . $popSentence . ' ' . $miles . ' Nearby dual-ring towns for the same work include ' . $nearText . '.',
        'Engineers travel from ' . $nap . '. The quote for ' . $name . ' in ' . $townName . ' is price on application after the building, the existing equipment and the access are known. Travel sits inside that quote. There is no catalogue fee and no separate mystery call-out price on this page.',
        'This town page keeps the full description of the work, then adds ' . $townName . ' to the heading, the opening and the request. It is the local page for a search in this town, and it links back to the hub and to the service page rather than to a removed URL.',
        'Ask for ' . $name . ' in ' . $townName . ' with the postcode and a photo of the panel, recorder or door. Phone ' . $phone . '. Say whether the building is a home, a rented block, a shop or a warehouse so the quote names the right scope.',
    ];
}

function securityDualRingRender(string $surface, string $slug, string $townSlug = ''): void
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : strtolower($slug);
    $townSlug = $townSlug === '' ? '' : (function_exists('areaSlug') ? areaSlug($townSlug) : strtolower($townSlug));
    $intent = securityDualRingIntent($slug);
    if ($intent === null || !securityDualRingSurfaceOk($surface, $slug)) {
        http_response_code(404);
        echo 'Security page not found';
        return;
    }
    $town = null;
    if ($townSlug !== '') {
        $town = securityDualRingTown($townSlug);
        if ($town === null) {
            http_response_code(404);
            echo 'Security town page not found';
            return;
        }
    }
    $pack = securityDualRingPack();
    $name = (string)$intent['name'];
    $townName = $town ? (string)$town['name'] : '';
    $paragraphs = $surface === 'job' ? ($intent['job_paragraphs'] ?? []) : ($intent['paragraphs'] ?? []);
    if (!is_array($paragraphs) || $paragraphs === []) {
        $paragraphs = $intent['paragraphs'] ?? [];
    }
    if ($town) {
        $paragraphs = array_merge(securityDualRingLocalParagraphs($intent, $town), $paragraphs);
    }
    $h1 = $town ? ($name . ' in ' . $townName) : $name;
    $pageTitle = securityDualRingTitle($name, $townName);
    $who = 'landlords, agents and commercial occupiers';
    $metaDesc = $town
        ? securityDualRingFitMeta($name . ' in ' . $townName . '. ' . ($town['county'] ?: 'The dual ring') . ' sites for landlords, agents and commercial occupiers. Request a quote — POA.')
        : (string)($intent['meta_hub'] ?? securityDualRingFitMeta($name . ' across the Manchester and Burnley ring. Request a quote — POA.'));
    $path = $surface === 'job' ? '/pages/jobs/' . $slug : '/pages/keywords/' . $slug;
    if ($town) {
        $path .= '/' . $townSlug;
    }
    $canonicalUrl = function_exists('url') ? url($path) : $path;
    $images = $intent['images'] ?? [];
    $firstSrc = is_array($images[0] ?? null) ? (string)$images[0]['src'] : '/assets/images/services/cctv.jpg';
    $ogImage = rtrim((string)SITE_URL, '/') . $firstSrc;
    $faqs = is_array($intent['faqs'] ?? null) ? $intent['faqs'] : [];
    $h = static function (string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    };

    $metaTitleExact = true;
    $ogTitle = $pageTitle;
    $ogDescription = $metaDesc;
    $metaRobots = 'index, follow';
    $GLOBALS['pageTitle'] = $pageTitle;
    $GLOBALS['metaDesc'] = $metaDesc;
    $GLOBALS['canonicalUrl'] = $canonicalUrl;
    $GLOBALS['ogImage'] = $ogImage;
    $GLOBALS['ogTitle'] = $ogTitle;
    $GLOBALS['ogDescription'] = $ogDescription;
    $GLOBALS['metaTitleExact'] = true;
    $GLOBALS['metaRobots'] = $metaRobots;

    $faqEntities = [];
    foreach ($faqs as $faq) {
        if (!is_array($faq)) {
            continue;
        }
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => (string)($faq['q'] ?? ''),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string)($faq['a'] ?? '')],
        ];
    }
    $faqJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    require SITE_ROOT . '/includes/header.php';
    echo '<script type="application/ld+json">' . $faqJson . '</script>';
    echo '<article id="local-copy" data-seo-body="1" class="max-w-3xl mx-auto px-6 py-12 text-zinc-900">';
    echo '<nav class="text-sm text-zinc-600 mb-4" aria-label="Breadcrumb">';
    echo '<a class="text-[#ff6b00] font-semibold" href="' . $h(url('/')) . '">Home</a> / ';
    echo '<a class="text-[#ff6b00] font-semibold" href="' . $h(url($surface === 'job' ? '/pages/jobs' : '/pages/keywords')) . '">' . ($surface === 'job' ? 'Jobs' : 'Guides') . '</a> / ';
    if ($town) {
        $hubPath = $surface === 'job' ? '/pages/jobs/' . $slug : '/pages/keywords/' . $slug;
        echo '<a class="text-[#ff6b00] font-semibold" href="' . $h(url($hubPath)) . '">' . $h($name) . '</a> / ';
        echo '<span>' . $h($townName) . '</span>';
    } else {
        echo '<span>' . $h($name) . '</span>';
    }
    echo '</nav>';
    echo '<p class="text-xs font-bold uppercase tracking-wider text-[#ff6b00]">' . $h((string)$intent['service_name']) . ' · Price on application</p>';
    echo '<h1 class="mt-2 text-4xl font-semibold tracking-tight text-[#061828]">' . $h($h1) . '</h1>';
    foreach ($paragraphs as $paragraph) {
        if (!is_string($paragraph) || trim($paragraph) === '') {
            continue;
        }
        echo '<p class="mt-4 leading-relaxed">' . $h($paragraph) . '</p>';
    }
    echo '<h2 class="mt-10 text-2xl font-semibold text-[#061828]">Photographs</h2>';
    echo '<div class="mt-4 grid sm:grid-cols-3 gap-3">';
    foreach ($images as $image) {
        if (!is_array($image)) {
            continue;
        }
        $src = (string)($image['src'] ?? '');
        if ($src === '') {
            continue;
        }
        echo '<figure class="bg-white border border-zinc-200 rounded-2xl overflow-hidden">';
        echo '<img src="' . $h($src) . '" alt="' . $h((string)($image['alt'] ?? $name)) . '" width="640" height="360" loading="lazy">';
        echo '</figure>';
    }
    echo '</div>';
    echo '<h2 class="mt-10 text-2xl font-semibold text-[#061828]">Related pages</h2><ul class="mt-3 space-y-2">';
    $links = [
        [(string)$intent['service_href'], (string)$intent['service_name']],
        ['/contact', 'Request a quote'],
        ['/pages/areas', 'Areas'],
    ];
    if ($surface === 'keyword' && ($intent['kind'] ?? '') === 'both') {
        $links[] = ['/pages/jobs/' . $slug . ($town ? '/' . $townSlug : ''), $name . ' job'];
    }
    if ($surface === 'job') {
        $links[] = ['/pages/keywords/' . $slug . ($town ? '/' . $townSlug : ''), $name . ' guide'];
    }
    $related = (string)($intent['related'] ?? '');
    if ($related !== '' && $related !== $slug) {
        $links[] = ['/pages/keywords/' . $related, 'Related guide'];
    }
    foreach ($intent['extra_links'] ?? [] as $extra) {
        if (is_array($extra) && !empty($extra['href'])) {
            $links[] = [(string)$extra['href'], (string)($extra['label'] ?? 'Related')];
        }
    }
    if ($town && function_exists('icomplyCrawlTownSlug') && icomplyCrawlTownSlug($townSlug)) {
        $links[] = ['/pages/areas/' . $townSlug, 'Property services in ' . $townName];
    }
    if (!$town) {
        $links[] = [$path . '/stockport', $name . ' in Stockport'];
        $links[] = [$path . '/manchester', $name . ' in Manchester'];
        $links[] = [$path . '/burnley', $name . ' in Burnley'];
    } else {
        foreach ($town['near'] ?? [] as $nearSlug) {
            $nearRow = $pack['towns'][$nearSlug] ?? null;
            if (!is_array($nearRow)) {
                continue;
            }
            $base = $surface === 'job' ? '/pages/jobs/' . $slug : '/pages/keywords/' . $slug;
            $links[] = [$base . '/' . $nearSlug, $name . ' in ' . (string)$nearRow['name']];
        }
    }
    foreach ($links as [$href, $label]) {
        echo '<li><a class="text-[#ff6b00] font-semibold" href="' . $h(url($href)) . '">' . $h($label) . '</a></li>';
    }
    echo '</ul>';
    echo '<p class="mt-6 text-sm text-zinc-700">Workshop: ' . $h((string)($pack['nap'] ?? '')) . '. Phone ' . $h((string)($pack['phone'] ?? '')) . '.</p>';
    echo '</article>';
    echo '<section data-seo-faq="1" class="max-w-3xl mx-auto px-6 pb-16">';
    echo '<h2 class="text-2xl font-semibold text-[#061828]">' . $h($name) . ' questions</h2>';
    echo '<div class="mt-4 space-y-3">';
    foreach ($faqs as $faq) {
        if (!is_array($faq)) {
            continue;
        }
        echo '<details class="border border-zinc-200 rounded-2xl p-4 bg-white">';
        echo '<summary class="font-semibold cursor-pointer">' . $h((string)($faq['q'] ?? '')) . '</summary>';
        echo '<p class="mt-2 leading-relaxed">' . $h((string)($faq['a'] ?? '')) . '</p>';
        echo '</details>';
    }
    echo '</div></section>';
    require SITE_ROOT . '/includes/footer.php';
}

function securityDualRingJobIndexHtml(): string
{
    $h = static function (string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    };
    $html = '<section class="related-links mt-12" aria-label="Security dual ring"><h2 class="text-2xl font-semibold text-[#061828]">Security jobs (dual ring, P0)</h2>';
    $html .= '<p class="mt-2 text-zinc-700">CCTV, access control, intruder alarms, door entry and intercoms for the 269-town Manchester and Burnley ring. P1 security jobs are a follow-on wave.</p><ul class="mt-6 grid sm:grid-cols-2 gap-3">';
    foreach (securityDualRingPack()['intents'] ?? [] as $slug => $intent) {
        if (!is_array($intent) || ($intent['kind'] ?? '') !== 'both') {
            continue;
        }
        $name = (string)($intent['name'] ?? $slug);
        $html .= '<li class="border border-zinc-200 rounded-2xl p-4 bg-white">';
        $html .= '<a class="font-semibold text-[#061828] hover:text-[#ff6b00]" href="' . $h(url('/pages/jobs/' . $slug)) . '">' . $h($name) . '</a>';
        $html .= '<p class="mt-2 text-sm"><a class="text-[#ff6b00] font-medium" href="' . $h(url('/pages/jobs/' . $slug . '/stockport')) . '">' . $h($name . ' in Stockport') . '</a></p>';
        $html .= '</li>';
    }
    $html .= '</ul></section>';
    return $html;
}
