<?php
/**
 * Nationwide access-control lane.
 * Hub: /pages/access-control-systems
 * Cities: /pages/access-control-systems/{slug}
 * Tunstall is nurse call and is never listed here.
 */
declare(strict_types=1);

function acnNationwideData(): array
{
    $data = loadJsonData('access-control-nationwide', []);
    return is_array($data) ? $data : [];
}

/** @return list<array<string,mixed>> */
function acnCities(): array
{
    $cities = acnNationwideData()['cities'] ?? [];
    return is_array($cities) ? array_values($cities) : [];
}

function acnCity(string $slug): ?array
{
    $slug = areaSlug($slug);
    foreach (acnCities() as $city) {
        if (areaSlug((string)($city['slug'] ?? '')) === $slug) {
            return $city;
        }
    }
    return null;
}

/** @return list<string> */
function acnRoutes(): array
{
    $routes = ['/pages/access-control-systems'];
    foreach (acnCities() as $city) {
        $slug = areaSlug((string)($city['slug'] ?? ''));
        if ($slug !== '') {
            $routes[] = '/pages/access-control-systems/' . $slug;
        }
    }
    return $routes;
}

/**
 * Access-control manufacturers with a real catalogue page.
 * Tunstall is excluded even if it is later tagged onto this service.
 *
 * @return list<array{name:string,slug:string}>
 */
function acnManufacturers(): array
{
    $out = [];
    foreach (getManufacturers('access-control') as $name) {
        $name = (string)$name;
        $slug = manufacturerSlugFromName($name);
        if ($slug === '' || $slug === 'tunstall' || strcasecmp($name, 'Tunstall') === 0) {
            continue;
        }
        $entry = getManufacturerBySlug($slug);
        if (!$entry) {
            continue;
        }
        $out[] = [
            'name' => (string)($entry['name'] ?? $name),
            'slug' => $slug,
        ];
    }
    return $out;
}

function acnBrandGuideSlug(string $manufacturerSlug): string
{
    $map = [
        'paxton' => 'paxton-access-control',
        'hid-global' => 'hid-access-control',
        'salto-systems' => 'salto-access-control',
        'assa-abloy' => 'assa-abloy-access-control',
    ];
    $slug = $map[$manufacturerSlug] ?? 'access-control-system';
    $keywords = getMajorKeywords();
    return isset($keywords[$slug]) ? $slug : 'access-control-system';
}

/** @return array<string,array> */
function acnKeywords(): array
{
    return getKeywordsForService('access-control');
}

function acnIsExcludedManufacturer(string $nameOrSlug): bool
{
    $slug = manufacturerSlugFromName($nameOrSlug);
    return $slug === 'tunstall' || strcasecmp($nameOrSlug, 'Tunstall') === 0;
}

function acnPhoneHref(): string
{
    return 'tel:' . preg_replace('/\s+/', '', (string)PHONE);
}

function acnStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
}

/** @param list<array{0:string,1:string}> $faqs */
function acnFaqSchema(array $faqs): array
{
    $entities = [];
    foreach ($faqs as $faq) {
        $entities[] = [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
        ];
    }
    return $entities;
}

function acnManufacturerHtml(): string
{
    $html = '';
    foreach (acnManufacturers() as $mfr) {
        $href = htmlspecialchars(url('/pages/manufacturers/' . $mfr['slug']), ENT_QUOTES, 'UTF-8');
        $guide = htmlspecialchars(url('/pages/keywords/' . acnBrandGuideSlug($mfr['slug'])), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($mfr['name'], ENT_QUOTES, 'UTF-8');
        $html .= '<div class="bg-white border-2 border-zinc-200 rounded-2xl p-4 hover:border-[#ff6b00]">'
            . '<a class="font-semibold text-[#061828] hover:text-[#ff6b00]" href="' . $href . '">' . $name . '</a>'
            . '<div class="mt-2 flex flex-wrap gap-3 text-sm">'
            . '<a class="text-[#ff6b00] font-semibold hover:underline" href="' . $href . '">Brand page</a>'
            . '<a class="text-[#061828] font-semibold hover:underline" href="' . $guide . '">Related guide</a>'
            . '</div></div>';
    }
    return $html;
}

/** @param array<string,array> $keywords */
function acnKeywordLinksHtml(array $keywords): string
{
    $html = '';
    foreach ($keywords as $slug => $meta) {
        $name = htmlspecialchars((string)($meta['name'] ?? $slug), ENT_QUOTES, 'UTF-8');
        $href = htmlspecialchars(url('/pages/keywords/' . $slug), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-4 py-2 bg-white border-2 border-zinc-200 rounded-full text-sm font-semibold text-[#061828] hover:border-[#ff6b00]">' . $name . '</a>';
    }
    return $html;
}

function acnQuoteForm(string $place): void
{
    $prefill = "Lane: access-control-nationwide\nPlace: {$place}\nController brand, door count and postcode:\n";
    ?>
    <section id="quote" class="bg-[#061828] text-white">
        <div class="max-w-3xl mx-auto px-6 py-14">
            <h2 class="text-3xl font-bold text-center">Quote for access control<?= $place !== '' ? ' in ' . htmlspecialchars($place, ENT_QUOTES, 'UTF-8') : '' ?></h2>
            <p class="mt-3 text-center text-white/90">Price on application after the doors and the controller are known. Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>.</p>
            <form action="<?= url('/contact') ?>" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars((string)($_SESSION['csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="service" value="Access Control">
                <div class="grid md:grid-cols-2 gap-4">
                    <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                    <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                </div>
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <textarea name="message" rows="5" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium"><?= htmlspecialchars($prefill, ENT_QUOTES, 'UTF-8') ?></textarea>
                <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold">Request a POA quote</button>
            </form>
        </div>
    </section>
    <?php
}

function acnRender(array $view): void
{
    acnStartSession();
    $pageTitle = $view['title'];
    $metaDesc = $view['meta'];
    $metaKeywords = $view['keywords'];
    $canonicalUrl = $view['canonical'];
    $ogImage = url('/assets/images/services/access-control.jpg');
    $faqs = $view['faqs'];
    $crumbs = $view['crumbs'];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                'name' => $view['h1'],
                'description' => $metaDesc,
                'url' => $canonicalUrl,
                'serviceType' => 'Access Control',
                'telephone' => PHONE,
                'provider' => [
                    '@type' => 'LocalBusiness',
                    'name' => SITE_NAME,
                    'telephone' => PHONE,
                    'url' => SITE_URL,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => '17 Woodlands Park Road',
                        'addressLocality' => 'Offerton, Stockport',
                        'addressRegion' => 'Greater Manchester',
                        'postalCode' => 'SK2 5DE',
                        'addressCountry' => 'GB',
                    ],
                ],
                'areaServed' => $view['areaServed'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $crumbs,
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => acnFaqSchema($faqs),
            ],
        ],
    ];

    require SITE_ROOT . '/includes/header.php';
    ?>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <section class="bg-[#061828] text-white">
        <div class="max-w-7xl mx-auto px-6 py-14 md:py-20">
            <nav class="text-xs text-white/70 mb-5 flex flex-wrap gap-2" aria-label="Breadcrumb">
                <?php foreach ($view['crumbLinks'] as $i => $c): ?>
                    <?php if ($i > 0): ?><span class="text-white/40">/</span><?php endif; ?>
                    <?php if (!empty($c['href'])): ?>
                        <a class="hover:text-white" href="<?= htmlspecialchars($c['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                        <span class="text-white font-medium"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Access control systems</p>
            <h1 class="mt-3 text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tight max-w-4xl"><?= htmlspecialchars($view['h1'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="mt-5 text-lg text-white/90 max-w-3xl"><?= htmlspecialchars($view['lead'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-bold">Request a POA quote</a>
                <a href="<?= htmlspecialchars(acnPhoneHref(), ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($view['wa']) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-bold">WhatsApp</a>
            </div>
        </div>
    </section>
    <?php
    echo $view['body'];
    ?>
    <section id="manufacturers" class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <h2 class="text-3xl font-semibold text-[#061828]">Manufacturers on this work</h2>
            <p class="mt-3 max-w-3xl text-zinc-800">Every access-control brand in our catalogue is linked below. Tunstall is a nurse-call system and is not part of this list. We install, take over or service these platforms. We do not claim a dealer badge we have not been given.</p>
            <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-4"><?= acnManufacturerHtml() ?></div>
        </div>
    </section>
    <?php
    acnQuoteForm($view['place']);
    require SITE_ROOT . '/includes/footer.php';
}

function acnRenderHub(): void
{
    $cities = acnCities();
    $keywords = acnKeywords();
    $byNation = [];
    foreach ($cities as $city) {
        $byNation[(string)($city['nation'] ?? 'UK')][] = $city;
    }
    $canonical = url('/pages/access-control-systems');
    $faqs = [
        ['Where are the engineers based?', 'At 17 Woodlands Park Road, Offerton, Stockport, SK2 5DE. The North West diary runs from there. Other UK cities on this site are attended by arrangement. There is no second depot.'],
        ['Which manufacturers do you cover?', 'The access-control brands linked on this page, including Paxton, HID Global, Salto Systems and ASSA ABLOY. Tunstall is nurse call and is excluded.'],
        ['How is the work priced?', 'Price on application after a survey or clear photos of the doors and the controller. No door price is published here.'],
        ['What standard do you read electronic access control against?', 'BS EN 60839-11 for the electronic access-control system. Door release on a fire alarm is read against BS 7273-4. Older specifications still say BS EN 50133. That standard was withdrawn. We do not treat it as the current design code.'],
        ['Do you offer a 24-hour access-control contract on this page?', 'No. Help in the North West depends on the diary. It is not an SLA.'],
    ];
    ob_start();
    ?>
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-6 py-14 grid lg:grid-cols-2 gap-10">
            <div>
                <h2 class="text-3xl font-semibold text-[#061828]">What the visit actually is</h2>
                <p class="mt-4 text-zinc-900 leading-relaxed">A survey names each door, the lock type, whether it should unlock or stay locked on power loss, and how the fire alarm releases it. Install, takeover and repair then follow that note. Programming covers leavers, holiday calendars and door groups. We do not sell a “solution” in place of a door schedule.</p>
                <p class="mt-4 text-zinc-900 leading-relaxed">Biometric readers store special-category data. If you want them, the DPIA is yours. We can fit the reader the platform supports. We do not write your data-protection policy.</p>
            </div>
            <div>
                <h2 class="text-3xl font-semibold text-[#061828]">Where we will travel</h2>
                <p class="mt-4 text-zinc-900 leading-relaxed">Stockport, Manchester and the rest of the published North West town list are diary work. The city pages below are the UK set we will quote properly: London, the Midlands, Scotland, Wales, Northern Ireland and the other cities named. Towns without a page — including Newport, Dundee and Blackpool — can still be quoted. They do not get a thin doorway page.</p>
                <p class="mt-4 text-zinc-900 leading-relaxed">North West town hubs for every trade stay on the <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/areas') ?>">areas index</a>. The access-control service hub stays at <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/services/access-control') ?>">access control services</a>.</p>
            </div>
        </div>
    </section>
    <section id="areas" class="bg-zinc-50 border-t">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <h2 class="text-3xl font-semibold text-[#061828]">City pages</h2>
            <p class="mt-3 max-w-3xl text-zinc-800">Each city has its own note on buildings, doors and how the visit is booked. The notes are not the same paragraph with the place name swapped.</p>
            <?php foreach ($byNation as $nation => $list): ?>
                <h3 class="mt-8 text-xl font-semibold text-[#061828]"><?= htmlspecialchars($nation, ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php foreach ($list as $city): ?>
                        <a class="px-4 py-2 bg-white border-2 border-zinc-200 rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= url('/pages/access-control-systems/' . areaSlug((string)$city['slug'])) ?>"><?= htmlspecialchars((string)$city['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <section id="keywords" class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <h2 class="text-3xl font-semibold text-[#061828]">Keyword guides</h2>
            <p class="mt-3 max-w-3xl text-zinc-800"><?= count($keywords) ?> access-control guides, including staff-change programming, fire-interface tests and lock assessments. Each guide links to the same manufacturer list, without Tunstall.</p>
            <div class="mt-6 flex flex-wrap gap-2"><?= acnKeywordLinksHtml($keywords) ?></div>
        </div>
    </section>
    <section class="bg-zinc-50 border-t">
        <div class="max-w-3xl mx-auto px-6 py-14">
            <h2 class="text-3xl font-semibold text-[#061828]">Questions</h2>
            <div class="mt-6 space-y-3">
                <?php foreach ($faqs as $faq): ?>
                    <details class="bg-white border-2 border-zinc-200 rounded-2xl p-5">
                        <summary class="font-semibold cursor-pointer"><?= htmlspecialchars($faq[0], ENT_QUOTES, 'UTF-8') ?></summary>
                        <p class="mt-3 text-zinc-800"><?= htmlspecialchars($faq[1], ENT_QUOTES, 'UTF-8') ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    $body = (string)ob_get_clean();
    $crumbLinks = [
        ['name' => 'Home', 'href' => rtrim(SITE_URL, '/') . '/'],
        ['name' => 'Access control systems', 'href' => ''],
    ];
    acnRender([
        'title' => 'Access control systems UK | iComply',
        'meta' => 'Access control systems across the UK from our Stockport workshop. City pages, keyword guides and manufacturer links. POA. ' . PHONE . '.',
        'keywords' => 'access control systems UK, access control installation, Paxton, HID, Salto, door access control, ' . PHONE,
        'canonical' => $canonical,
        'h1' => 'Access control systems across the UK',
        'lead' => 'Install, takeover and repair of electronic door access, quoted from Stockport. North West sites are on the diary. Other UK cities are attended when the door count justifies the trip.',
        'faqs' => $faqs,
        'crumbs' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Access control systems', 'item' => $canonical],
        ],
        'crumbLinks' => $crumbLinks,
        'areaServed' => [
            ['@type' => 'AdministrativeArea', 'name' => 'North West England'],
            ['@type' => 'Country', 'name' => 'United Kingdom'],
        ],
        'body' => $body,
        'place' => 'the UK',
        'wa' => 'Access control systems quote',
    ]);
}

function acnRenderCity(array $city): void
{
    $name = (string)$city['name'];
    $slug = areaSlug((string)$city['slug']);
    $canonical = url('/pages/access-control-systems/' . $slug);
    $hub = url('/pages/access-control-systems');
    $faqs = [
        [(string)$city['faq_q'], (string)$city['faq_a']],
        ['Do you publish a price for ' . $name . '?', 'No. The visit is price on application. Door count, the controller and the travel decide the quote.'],
        ['Which phone number do I use?', 'Call ' . PHONE . '. The workshop is in Offerton, Stockport, even when the doors are in ' . $name . '.'],
    ];
    $keywords = acnKeywords();
    $window = 8;
    $span = max(1, count($keywords) - $window + 1);
    $offset = (int)(abs((int)crc32($slug)) % $span);
    $shown = array_slice($keywords, $offset, $window, true);
    if (count($shown) < 8) {
        $shown = array_slice($keywords, 0, 8, true);
    }
    ob_start();
    ?>
    <section class="bg-white">
        <div class="max-w-3xl mx-auto px-6 py-14 space-y-5 text-zinc-900 leading-relaxed">
            <h2 class="text-3xl font-semibold text-[#061828]">On the ground in <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars((string)$city['local'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2 class="text-2xl font-semibold text-[#061828]">Doors we expect</h2>
            <p><?= htmlspecialchars((string)$city['doors'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2 class="text-2xl font-semibold text-[#061828]">How the visit is booked</h2>
            <p><?= htmlspecialchars((string)$city['visit'], ENT_QUOTES, 'UTF-8') ?></p>
            <p>Electronic access control is read against BS EN 60839-11. Fire-alarm release of a lock is read against BS 7273-4. We do not cite the withdrawn BS EN 50133 as if it were current, and we do not claim an NSI or SSAIB badge on this page.</p>
        </div>
    </section>
    <section class="bg-zinc-50 border-t">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <h2 class="text-2xl font-semibold text-[#061828]">Other <?= htmlspecialchars((string)$city['nation'], ENT_QUOTES, 'UTF-8') ?> notes, and nearby pages</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ((array)($city['nearby'] ?? []) as $nearSlug): ?>
                    <?php $near = acnCity((string)$nearSlug); if (!$near) continue; ?>
                    <a class="px-4 py-2 bg-white border-2 border-zinc-200 rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= url('/pages/access-control-systems/' . areaSlug((string)$near['slug'])) ?>"><?= htmlspecialchars((string)$near['name'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
                <a class="px-4 py-2 bg-[#061828] text-white rounded-full text-sm font-semibold" href="<?= htmlspecialchars($hub, ENT_QUOTES, 'UTF-8') ?>">All UK city pages</a>
            </div>
        </div>
    </section>
    <section class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <h2 class="text-2xl font-semibold text-[#061828]">Guides that sit beside <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> door work</h2>
            <div class="mt-4 flex flex-wrap gap-2"><?= acnKeywordLinksHtml($shown) ?></div>
        </div>
    </section>
    <section class="bg-zinc-50 border-t">
        <div class="max-w-3xl mx-auto px-6 py-14">
            <h2 class="text-3xl font-semibold text-[#061828]">Questions about <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="mt-6 space-y-3">
                <?php foreach ($faqs as $faq): ?>
                    <details class="bg-white border-2 border-zinc-200 rounded-2xl p-5">
                        <summary class="font-semibold cursor-pointer"><?= htmlspecialchars($faq[0], ENT_QUOTES, 'UTF-8') ?></summary>
                        <p class="mt-3 text-zinc-800"><?= htmlspecialchars($faq[1], ENT_QUOTES, 'UTF-8') ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
    $body = (string)ob_get_clean();
    acnRender([
        'title' => 'Access control in ' . $name . ' | iComply',
        'meta' => (string)$city['meta'],
        'keywords' => 'access control systems ' . $name . ', door access ' . $name . ', ' . PHONE,
        'canonical' => $canonical,
        'h1' => 'Access control systems in ' . $name,
        'lead' => (string)$city['region'] . '. Engineers are based in Stockport. This page is the ' . $name . ' note, not a claim of a local branch.',
        'faqs' => $faqs,
        'crumbs' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Access control systems', 'item' => $hub],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $canonical],
        ],
        'crumbLinks' => [
            ['name' => 'Home', 'href' => rtrim(SITE_URL, '/') . '/'],
            ['name' => 'Access control systems', 'href' => $hub],
            ['name' => $name, 'href' => ''],
        ],
        'areaServed' => ['@type' => 'City', 'name' => $name],
        'body' => $body,
        'place' => $name,
        'wa' => 'Access control systems in ' . $name,
    ]);
}
