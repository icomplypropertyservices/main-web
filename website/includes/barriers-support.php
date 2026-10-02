<?php
/**
 * Priority barrier support — mainland Britain, not a live promote.
 *
 * Manchester and Burnley stay the priority examples. Every other page is a
 * mainland England, Wales or Scotland area. Northern Ireland, the Isle of Man,
 * the Channel Islands and offshore islands are excluded.
 *
 * Supply £ figures are the published 5m CAME packs (ex VAT). Install and
 * mainland travel are POA and agreed before booking.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

function barriersH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Slugs we will not publish, even if they appear in a data file later. */
function barriersExcludedSlugSet(): array
{
    static $set = null;
    if ($set !== null) {
        return $set;
    }
    $slugs = [
        'belfast', 'londonderry', 'derry', 'lisburn', 'newry', 'bangor-northern-ireland',
        'armagh', 'omagh', 'enniskillen', 'coleraine', 'ballymena', 'craigavon',
        'douglas', 'ramsey', 'peel', 'castletown', 'port-erin',
        'st-helier', 'st-peter-port', 'jersey', 'guernsey', 'alderney', 'sark',
        'kirkwall', 'lerwick', 'stornoway', 'portree', 'tobermory', 'rothesay',
        'brodick', 'holyhead', 'llangefni', 'beaumaris', 'amlwch',
        'ryde', 'cowes', 'newport-isle-of-wight', 'sandown', 'shanklin', 'ventnor',
        'stormoway',
    ];
    $set = array_fill_keys($slugs, true);
    return $set;
}

/**
 * @return list<array{name:string,slug:string,nation:string,note:string,priority:bool,region:string}>
 */
function barriersMainlandAreas(): array
{
    static $areas = null;
    if ($areas !== null) {
        return $areas;
    }
    if (!function_exists('area_profile')) {
        require_once SITE_ROOT . '/includes/local-content.php';
    }

    $bySlug = [];
    foreach (getAreas() as $name) {
        $name = (string)$name;
        $slug = areaSlug($name);
        if ($slug === '' || isset(barriersExcludedSlugSet()[$slug])) {
            continue;
        }
        $profile = area_profile($name);
        $bySlug[$slug] = [
            'name' => $name,
            'slug' => $slug,
            'nation' => 'England',
            'region' => (string)($profile['region'] ?? 'North West'),
            'note' => trim((string)($profile['stock'] ?? '') . ' — ' . (string)($profile['focus'] ?? '')),
            'priority' => $slug === 'manchester' || $slug === 'burnley',
        ];
    }

    $path = SITE_ROOT . '/data/barriers-mainland-areas.json';
    $extra = [];
    if (is_file($path)) {
        $decoded = json_decode((string)file_get_contents($path), true);
        if (is_array($decoded)) {
            $extra = $decoded;
        }
    }
    foreach ($extra as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = trim((string)($row['name'] ?? ''));
        $nation = trim((string)($row['nation'] ?? ''));
        if ($name === '' || !in_array($nation, ['England', 'Wales', 'Scotland'], true)) {
            continue;
        }
        $slug = areaSlug($name);
        if ($slug === '' || isset($bySlug[$slug]) || isset(barriersExcludedSlugSet()[$slug])) {
            continue;
        }
        $bySlug[$slug] = [
            'name' => $name,
            'slug' => $slug,
            'nation' => $nation,
            'region' => $nation === 'England' ? 'Mainland England' : $nation,
            'note' => trim((string)($row['note'] ?? 'mainland commercial and residential parking')),
            'priority' => false,
        ];
    }

    $areas = array_values($bySlug);
    usort($areas, static function (array $a, array $b): int {
        $rank = static function (array $area): int {
            if ($area['slug'] === 'manchester') {
                return 0;
            }
            if ($area['slug'] === 'burnley') {
                return 1;
            }
            return 2;
        };
        $ra = $rank($a);
        $rb = $rank($b);
        if ($ra !== $rb) {
            return $ra <=> $rb;
        }
        $nation = strcmp($a['nation'], $b['nation']);
        if ($nation !== 0) {
            return $nation;
        }
        return strcasecmp($a['name'], $b['name']);
    });
    return $areas;
}

function barriersFindArea(string $slug): ?array
{
    $slug = areaSlug($slug);
    if ($slug === '' || isset(barriersExcludedSlugSet()[$slug])) {
        return null;
    }
    foreach (barriersMainlandAreas() as $area) {
        if ($area['slug'] === $slug) {
            return $area;
        }
    }
    return null;
}

/**
 * Published 5m supply packs. Install remains POA.
 *
 * @return list<array{sku:string,handle:string,price:string,blurb:string,access:string}>
 */
function barriersKitPacks(): array
{
    return [
        [
            'sku' => 'BAR-5M-STD',
            'handle' => 'bar-5m-std',
            'price' => '£5,850.00',
            'blurb' => 'Standard 5m CAME barrier pack',
            'access' => 'Barrier supply. Readers and door credentials are scoped with access control.',
        ],
        [
            'sku' => 'BAR-5M-VIDEX',
            'handle' => 'bar-5m-videx',
            'price' => '£7,441.83',
            'blurb' => '5m barrier with Videx',
            'access' => 'Speech at the lane, alongside Videx door entry on the building.',
        ],
        [
            'sku' => 'BAR-5M-PAXTON',
            'handle' => 'bar-5m-paxton',
            'price' => '£8,375.45',
            'blurb' => '5m barrier with Paxton',
            'access' => 'Paxton at the lane so door fobs can open the barrier too.',
        ],
        [
            'sku' => 'BAR-5M-GSM',
            'handle' => 'bar-5m-gsm',
            'price' => '£7,393.18',
            'blurb' => '5m barrier with GSM',
            'access' => 'Phone open for visitors, cards or fobs for people who already have door access.',
        ],
        [
            'sku' => 'BAR-5M-ALLIN',
            'handle' => 'bar-5m-allin',
            'price' => '£5,199.99',
            'blurb' => '5m barrier all-in (Jack-confirmed supply price)',
            'access' => 'Supply pack only. Install and the access-control scope stay POA.',
        ],
    ];
}

function barriersKitImage(string $handle): string
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $path = SITE_ROOT . '/data/bar-5m-came-gard-images.json';
        if (is_file($path)) {
            $json = json_decode((string)file_get_contents($path), true);
            if (is_array($json)) {
                foreach ($json as $key => $url) {
                    if (is_string($key) && is_string($url) && $url !== '') {
                        $map[strtolower($key)] = $url;
                    }
                }
            }
        }
    }
    return $map[strtolower($handle)] ?? '';
}

/**
 * @return list<array{href:string,title:string,text:string}>
 */
function barriersAccessControlLinks(): array
{
    return [
        [
            'href' => '/pages/services/access-control',
            'title' => 'Access control',
            'text' => 'Doors, readers and credentials for the building the barrier is protecting.',
        ],
        [
            'href' => '/pages/keywords/access-control-installation',
            'title' => 'Access control installation',
            'text' => 'New readers and controllers, including a lane reader on the barrier cabinet.',
        ],
        [
            'href' => '/pages/keywords/access-control-system',
            'title' => 'Access control system',
            'text' => 'One credential platform for doors and the car park, with an audit trail.',
        ],
        [
            'href' => '/pages/keywords/car-park-barrier-access',
            'title' => 'Car park barrier access',
            'text' => 'Tokens, visitor rights and time zones on the lane itself.',
        ],
        [
            'href' => '/pages/keywords/commercial-access-control',
            'title' => 'Commercial access control',
            'text' => 'Offices, yards and multi-let sites that need staff and contractor rules.',
        ],
        [
            'href' => '/pages/keywords/anpr-cctv-system',
            'title' => 'ANPR CCTV',
            'text' => 'Plate read as a credential, still tied to the access-control allow list.',
        ],
        [
            'href' => '/pages/services/door-entry',
            'title' => 'Door entry',
            'text' => 'Videx and related speech panels when the lane and the lobby should match.',
        ],
        [
            'href' => '/pages/resources/access-control-guide',
            'title' => 'Access control guide',
            'text' => 'How fobs, fire release and admin rights fit together before anyone orders a boom.',
        ],
    ];
}

/**
 * @return list<array{path:string,priority:string}>
 */
function barriersSitemapEntries(): array
{
    $rows = [
        ['path' => '/pages/barriers', 'priority' => '0.84'],
    ];
    foreach (barriersMainlandAreas() as $area) {
        $priority = !empty($area['priority']) ? '0.74' : '0.58';
        $rows[] = [
            'path' => '/pages/barriers/' . $area['slug'],
            'priority' => $priority,
        ];
    }
    return $rows;
}

/** @return list<string> */
function barriersExportRoutes(): array
{
    $routes = ['/pages/barriers'];
    foreach (barriersMainlandAreas() as $area) {
        $routes[] = '/pages/barriers/' . $area['slug'];
    }
    return $routes;
}

function barriersStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
}

function barriersAccessControlHtml(): string
{
    $html = '<section class="bg-white border-y-2 border-zinc-200" id="access-control">'
        . '<div class="max-w-7xl mx-auto px-6 py-14">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-bold">Cross-sell · access control</div>'
        . '<h2 class="mt-2 text-2xl md:text-3xl font-bold text-[#061828]">The barrier should use the same access control as the doors</h2>'
        . '<p class="mt-3 max-w-3xl text-zinc-900 leading-relaxed">A boom on its own is a remote that gets copied. We specify CAME lane hardware with Paxton, Videx or GSM so staff, residents and contractors use the credential they already have for the building. Time zones, visitor rights and an audit trail sit on the access-control system. Safety loops, photocells and fire release stay part of the lane design — the arm must not become a locked exit.</p>'
        . '<div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">';
    foreach (barriersAccessControlLinks() as $link) {
        $html .= '<a class="block p-5 bg-zinc-50 border-2 border-zinc-200 rounded-2xl hover:border-[#ff6b00]" href="'
            . barriersH(url($link['href'])) . '">'
            . '<div class="font-bold text-[#061828]">' . barriersH($link['title']) . '</div>'
            . '<p class="mt-2 text-sm text-zinc-800 leading-relaxed">' . barriersH($link['text']) . '</p>'
            . '<div class="mt-3 text-sm font-bold text-[#ff6b00]">Open →</div></a>';
    }
    $html .= '</div></div></section>';
    return $html;
}

function barriersKitsHtml(): string
{
    $html = '<section class="bg-zinc-100" id="kits">'
        . '<div class="max-w-7xl mx-auto px-6 py-14">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-bold">Manufacturers · kits</div>'
        . '<h2 class="mt-2 text-2xl md:text-3xl font-bold text-[#061828]">CAME 5m packs, with Paxton, Videx or GSM</h2>'
        . '<p class="mt-3 max-w-3xl text-zinc-900 leading-relaxed">CAME is the barrier manufacturer on these packs. Paxton and Videx are the access-control and door-entry brands we pair with the lane. Prices below are published supply prices, ex VAT. Installation, civils, induction loops and mainland travel are POA and agreed before booking.</p>'
        . '<div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">';
    foreach (barriersKitPacks() as $pack) {
        $img = barriersKitImage($pack['handle']);
        $html .= '<article class="p-5 bg-white border-2 border-zinc-200 rounded-3xl">';
        if ($img !== '') {
            $html .= '<img src="' . barriersH($img) . '" alt="' . barriersH($pack['sku'] . ' CAME barrier pack') . '" class="w-full h-36 object-contain mb-4" loading="lazy" width="320" height="144">';
        }
        $html .= '<div class="text-xs font-bold uppercase tracking-wider text-[#ff6b00]">' . barriersH($pack['sku']) . '</div>'
            . '<h3 class="mt-1 font-bold text-lg text-[#061828]">' . barriersH($pack['blurb']) . '</h3>'
            . '<p class="mt-2 text-sm text-zinc-800">' . barriersH($pack['access']) . '</p>'
            . '<div class="mt-4 text-[#ff6b00] font-bold">' . barriersH($pack['price'])
            . ' <span class="text-xs text-zinc-600 font-medium">ex VAT · install POA</span></div>'
            . '<a class="mt-4 inline-flex text-sm font-bold text-[#061828]" href="' . barriersH(url('/products') . '#barrier-packs') . '">Trade products →</a>'
            . '</article>';
    }
    $html .= '</div>'
        . '<p class="mt-6 text-sm text-zinc-800">Brands on this range: <strong>CAME</strong> barriers, <strong>Paxton</strong> access control, <strong>Videx</strong> door entry, plus GSM where the lane is opened by phone. '
        . '<a class="font-bold text-[#ff6b00]" href="' . barriersH(url('/pages/services/access-control')) . '">Access control service</a>'
        . ' · <a class="font-bold text-[#ff6b00]" href="' . barriersH(url('/products') . '#barrier-packs') . '">Barrier packs on the products hub</a>.</p>'
        . '</div></section>';
    return $html;
}

function barriersAreaChipsHtml(array $areas): string
{
    $html = '<div class="mt-6 flex flex-wrap gap-2">';
    foreach ($areas as $area) {
        $label = $area['name'];
        if (!empty($area['priority'])) {
            $label .= ' · priority';
        }
        $html .= '<a class="px-3 py-1.5 bg-white border-2 border-zinc-300 rounded-full text-xs font-semibold text-zinc-900 hover:border-[#ff6b00] hover:text-[#ff6b00]" href="'
            . barriersH(url('/pages/barriers/' . $area['slug'])) . '">'
            . barriersH($label) . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function barriersQuoteHtml(string $areaName): string
{
    $services = getServices();
    $csrf = barriersH((string)($_SESSION['csrf'] ?? ''));
    $html = '<section id="quote" class="bg-[#061828] text-white"><div class="max-w-3xl mx-auto px-6 py-14">'
        . '<h2 class="text-3xl font-bold text-center">Quote vehicle barriers'
        . ($areaName !== '' ? ' in ' . barriersH($areaName) : '') . '</h2>'
        . '<p class="mt-2 text-center text-white/90">Supply packs are priced above. Install and mainland travel are POA after the lane is scoped.</p>'
        . '<form action="' . barriersH(url('/contact')) . '" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4 shadow-xl">'
        . '<input type="hidden" name="csrf" value="' . $csrf . '">'
        . '<div class="grid md:grid-cols-2 gap-4">'
        . '<input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">'
        . '<input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">'
        . '</div><div class="grid md:grid-cols-2 gap-4">'
        . '<input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">'
        . '<select name="service" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl bg-white font-medium">'
        . '<option value="Vehicle barriers" selected>Vehicle barriers</option>'
        . '<option value="Access Control">Access control</option>';
    foreach ($services as $name) {
        $html .= '<option value="' . barriersH((string)$name) . '">' . barriersH((string)$name) . '</option>';
    }
    $preset = $areaName !== ''
        ? 'Barriers in ' . $areaName . '. Lane width, existing access control brand, and whether doors should share fobs.'
        : 'Town, lane width, existing access control brand, and whether doors should share fobs.';
    $html .= '</select></div>'
        . '<textarea name="message" rows="4" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium" placeholder="'
        . barriersH($preset) . '"></textarea>'
        . '<button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Request a POA quote</button>'
        . '</form></div></section>';
    return $html;
}

function barriersRenderHead(string $title, string $desc, string $keywords, string $canonical, string $imageAlt): void
{
    $pageTitle = $title;
    $metaDesc = $desc;
    $metaKeywords = $keywords;
    $canonicalUrl = $canonical;
    $img = barriersKitImage('bar-5m-std');
    $ogImage = $img !== '' ? $img : url('/assets/images/services/access-control.jpg');
    $ogImageAlt = $imageAlt;
    $metaRobots = 'index, follow, max-image-preview:large';
    barriersStartSession();
    require SITE_ROOT . '/includes/header.php';
}

function renderBarriersHubPage(): void
{
    $areas = barriersMainlandAreas();
    $count = count($areas);
    $nations = ['England' => 0, 'Wales' => 0, 'Scotland' => 0];
    foreach ($areas as $area) {
        $nations[$area['nation']] = ($nations[$area['nation']] ?? 0) + 1;
    }
    $title = 'Vehicle barriers across mainland Britain';
    $desc = 'CAME vehicle barriers for mainland England, Wales and Scotland, with Paxton, Videx and GSM access control. Manchester and Burnley are priority examples. Supply packs priced; install POA.';
    barriersRenderHead(
        $title . ' | Icomply Property Services',
        $desc,
        'vehicle barriers, CAME barrier, car park barrier, barrier access control, Paxton barrier, Videx barrier, Manchester barriers, Burnley barriers, mainland UK barriers',
        url('/pages/barriers'),
        'CAME vehicle barrier packs with access control'
    );
    $phone = defined('PHONE') ? PHONE : '';
    echo '<section class="bg-[#061828] text-white"><div class="max-w-7xl mx-auto px-6 py-14 md:py-20">';
    echo '<nav class="text-xs text-white/70 mb-5" aria-label="Breadcrumb"><a class="hover:text-white" href="' . barriersH(rtrim(SITE_URL, '/') . '/') . '">Home</a> <span class="text-white/40">/</span> <span class="text-white font-medium">Vehicle barriers</span></nav>';
    echo '<div class="inline-flex px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">Priority support · mainland</div>';
    echo '<h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight max-w-4xl leading-[1.05]">Vehicle barriers for mainland Britain</h1>';
    echo '<p class="mt-5 text-lg md:text-xl max-w-3xl leading-relaxed">Manchester and Burnley were the first priority pair. Coverage is now every mainland area we publish: ' . (int)$nations['England'] . ' in England, ' . (int)$nations['Wales'] . ' in Wales and ' . (int)$nations['Scotland'] . ' in Scotland (' . (int)$count . ' in total). Northern Ireland, the Isle of Man, the Channel Islands and offshore islands are not on this list.</p>';
    echo '<div class="mt-8 flex flex-wrap gap-3">';
    echo '<a class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-bold text-white" href="#access-control">Access control</a>';
    echo '<a class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold" href="' . barriersH(url('/pages/barriers/manchester')) . '">Manchester</a>';
    echo '<a class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold" href="' . barriersH(url('/pages/barriers/burnley')) . '">Burnley</a>';
    echo '<a class="px-8 py-4 rounded-2xl border border-white/40 font-bold" href="tel:' . barriersH(preg_replace('/\s+/', '', $phone)) . '">' . barriersH($phone) . '</a>';
    echo '</div></div></section>';
    echo barriersAccessControlHtml();
    echo barriersKitsHtml();
    echo '<section class="bg-white" id="areas"><div class="max-w-7xl mx-auto px-6 py-14">';
    echo '<h2 class="text-2xl md:text-3xl font-bold text-[#061828]">All mainland areas</h2>';
    echo '<p class="mt-2 text-zinc-800 max-w-3xl">Priority pages first, then England, Wales and Scotland. Each town has its own barrier page with the same access-control cross-sell and the same published kit prices.</p>';
    echo barriersAreaChipsHtml($areas);
    echo '</div></section>';
    echo barriersQuoteHtml('');
    require SITE_ROOT . '/includes/footer.php';
}

function renderBarriersAreaPage(string $slug): bool
{
    $area = barriersFindArea($slug);
    if ($area === null) {
        return false;
    }
    $name = $area['name'];
    $seed = abs(crc32(mb_strtolower($name) . '|barriers'));
    $openers = [
        "Vehicle barriers in {$name} are a lane-control job. The access-control system decides who the arm opens for.",
        "{$name} car parks and yards need a barrier that fails safe and still matches the door credentials.",
        "A boom in {$name} earns its keep when Paxton, Videx or GSM access control is designed with it, not bolted on later.",
    ];
    $opener = $openers[$seed % count($openers)];
    $priority = !empty($area['priority']);
    $badge = $priority ? 'Priority area · mainland' : 'Mainland coverage';
    $title = 'Vehicle barriers in ' . $name;
    $desc = 'CAME vehicle barriers in ' . $name . ' (' . $area['nation'] . '), tied to Paxton, Videx or GSM access control. Published 5m supply prices. Install POA.';
    barriersRenderHead(
        $title . ' | Icomply Property Services',
        $desc,
        'vehicle barriers ' . $name . ', CAME barrier ' . $name . ', car park barrier ' . $name . ', barrier access control ' . $name . ', Paxton barrier, Videx barrier',
        url('/pages/barriers/' . $area['slug']),
        'Vehicle barriers in ' . $name
    );
    echo '<script type="application/ld+json">' . json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $title,
        'areaServed' => ['@type' => 'Place', 'name' => $name],
        'provider' => ['@type' => 'LocalBusiness', 'name' => SITE_NAME, 'telephone' => PHONE],
        'url' => url('/pages/barriers/' . $area['slug']),
        'description' => $desc,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    echo '<section class="bg-[#061828] text-white"><div class="max-w-7xl mx-auto px-6 py-14 md:py-20">';
    echo '<nav class="text-xs text-white/70 mb-5" aria-label="Breadcrumb"><a class="hover:text-white" href="' . barriersH(rtrim(SITE_URL, '/') . '/') . '">Home</a> <span class="text-white/40">/</span> <a class="hover:text-white" href="' . barriersH(url('/pages/barriers')) . '">Vehicle barriers</a> <span class="text-white/40">/</span> <span class="text-white font-medium">' . barriersH($name) . '</span></nav>';
    echo '<div class="inline-flex px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">' . barriersH($badge) . '</div>';
    echo '<h1 class="text-4xl sm:text-5xl font-bold tracking-tight max-w-4xl">' . barriersH($title) . '</h1>';
    echo '<p class="mt-5 text-lg max-w-3xl leading-relaxed">' . barriersH($opener) . '</p>';
    echo '<p class="mt-4 text-base max-w-3xl leading-relaxed text-white/90">' . barriersH($name) . ' is ' . barriersH($area['region']) . ' (' . barriersH($area['nation']) . ') — ' . barriersH($area['note']) . '. We survey the lane, safety devices and where the reader or intercom sits. Published 5m CAME supply prices are below. Installation and mainland travel are agreed before booking and stay POA.</p>';
    echo '<div class="mt-8 flex flex-wrap gap-3">';
    echo '<a class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-bold text-white" href="#access-control">Pair with access control</a>';
    echo '<a class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold" href="#kits">CAME kits</a>';
    echo '<a class="px-8 py-4 rounded-2xl border border-white/40 font-bold" href="' . barriersH(url('/pages/barriers')) . '">All mainland areas</a>';
    echo '</div></div></section>';
    echo barriersAccessControlHtml();
    echo barriersKitsHtml();
    $related = [];
    foreach (barriersMainlandAreas() as $other) {
        if ($other['slug'] === 'manchester' || $other['slug'] === 'burnley') {
            $related[$other['slug']] = $other;
        }
    }
    $pool = barriersMainlandAreas();
    $added = 0;
    $i = 0;
    while ($added < 8 && $i < count($pool)) {
        $candidate = $pool[($seed + $i) % count($pool)];
        $i++;
        if ($candidate['slug'] === $area['slug'] || isset($related[$candidate['slug']])) {
            continue;
        }
        $related[$candidate['slug']] = $candidate;
        $added++;
    }
    echo '<section class="bg-white"><div class="max-w-7xl mx-auto px-6 py-14">';
    echo '<h2 class="text-2xl font-bold text-[#061828]">Other mainland barrier pages</h2>';
    echo '<p class="mt-2 text-zinc-800">Manchester and Burnley stay pinned. The full list is on the <a class="font-bold text-[#ff6b00]" href="' . barriersH(url('/pages/barriers')) . '">mainland hub</a>.</p>';
    echo barriersAreaChipsHtml(array_values($related));
    echo '</div></section>';
    echo '<section class="bg-zinc-100"><div class="max-w-3xl mx-auto px-6 py-14">';
    echo '<h2 class="text-2xl font-bold text-[#061828]">' . barriersH($name) . ' barrier questions</h2>';
    $faqs = [
        ['Does this page cover ' . $name . ' only?', 'This page is the ' . $name . ' landing. The same service is published for every mainland area on the hub. Northern Ireland, the Isle of Man, the Channel Islands and offshore islands are outside that list.'],
        ['Can the barrier use the same fob as the doors in ' . $name . '?', 'Often yes. Paxton and similar access-control platforms can share credentials between building doors and the lane. We confirm that on survey rather than assuming the old remotes should stay.'],
        ['Are the kit prices the installed price in ' . $name . '?', 'No. The £ figures are published supply prices, ex VAT, for the 5m CAME packs. Installation, civils and mainland travel are POA and agreed before booking.'],
    ];
    echo '<div class="mt-6 space-y-3">';
    foreach ($faqs as [$q, $a]) {
        echo '<details class="bg-white border-2 border-zinc-300 rounded-2xl p-5"><summary class="font-bold cursor-pointer">' . barriersH($q) . '</summary><p class="mt-3 text-sm text-zinc-900 leading-relaxed">' . barriersH($a) . '</p></details>';
    }
    echo '</div></div></section>';
    echo barriersQuoteHtml($name);
    require SITE_ROOT . '/includes/footer.php';
    return true;
}
