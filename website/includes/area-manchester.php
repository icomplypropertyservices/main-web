<?php
/**
 * Owned Manchester area hub and top service×Manchester landings.
 *
 * Fire protection is listed as a UK mainland service. Other trades are local
 * Manchester landings. Burnley is a separate area hub at /pages/areas/burnley
 * (draft branch cursor/area-hubs-mcr-burnley) — link it, do not rebuild it here.
 */
if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Top service×Manchester landings. Fire rows are nationwide; local rows are Manchester.
 *
 * @return array{fire: array<string,string>, local: array<string,string>}
 */
function manchesterOwnedLandings(): array
{
    return [
        'fire' => [
            'fire-alarms' => 'Addressable and conventional fire alarms for Manchester blocks, offices, warehouses and HMOs, designed and serviced to BS 5839. Fire-alarm attendance is a UK mainland service — this page is the Manchester landing, not the edge of the map. Panel takeovers, commissioning and planned servicing are quoted POA after we see the system.',
            'emergency-lighting' => 'BS 5266 emergency lighting tests, LED upgrades and logbooks for Manchester escape routes, plant rooms and HMOs. Duration testing sits with the same UK mainland fire-protection work as alarms and fire risk assessments. The visit is priced POA once we know the fitting count.',
            'fire-risk-assessments' => 'Suitable and sufficient fire risk assessments for Manchester landlords, RTMs, offices and multi-occupied buildings under the Fire Safety Order. FRA visits are part of nationwide fire protection, with Manchester on the regular diary. The written fee follows the building — we do not publish a made-up tariff here.',
            'fire-doors' => 'Fire door surveys, closer and seal repairs, and install work for Manchester stair cores and commercial units. Door sets are specified against the fire strategy and quoted POA. The same passive-fire team travels UK mainland; Manchester is the city landing.',
            'fire-extinguishers' => 'Supply, siting and BS 5306 servicing of portable extinguishers for Manchester offices, HMOs, warehouses and retail. Extinguisher routes are part of UK mainland fire protection. A written quote follows the schedule of points.',
            'smoke-co-alarms' => 'Interlinked smoke and carbon monoxide alarms for Manchester rented homes where a domestic grade set is the right answer, not a full BS 5839 panel. Landlord alarm visits are booked with the wider UK mainland fire offer. Scope depends on storeys and existing heads.',
            'aov-air-handling' => 'AOV and smoke-control maintenance for Manchester stair lobbies and smoke shafts, tied into the fire alarm cause-and-effect. Smoke ventilation is scheduled as UK mainland fire protection. Price is POA after we see the panel, actuators and fire strategy.',
        ],
        'local' => [
            'electrical' => 'EICR certificates, consumer-unit changes, rewires and remedials for Manchester rentals, offices and retail, to BS 7671. Electrical work on this hub is a local Manchester service. East Lancashire, including Burnley, is listed on the Burnley area hub rather than duplicated here.',
            'gas-systems' => 'Landlord gas safety records (CP12), boiler work and commercial gas checks for Manchester properties. Gas visits are booked locally from our Stockport base. Burnley and the rest of East Lancashire use the Burnley area hub.',
            'landlord-compliance' => 'Combined landlord visits for Manchester portfolios — electrical reports, gas records, alarms and the paperwork agents ask for in one schedule. This is a local Manchester package. Burnley landlords are pointed at the Burnley hub so that town is not dropped.',
            'pat-testing' => 'In-service PAT for Manchester offices, HMOs, warehouses and retail units, with a register you can file. PAT is a local service on this page. We quote POA from the item count and site access.',
            'epc' => 'Domestic and non-domestic energy performance certificates for Manchester sales, lets and commercial units. EPC visits are local to Manchester. The certificate is lodged after the survey; the fee is POA once we know the floor area.',
            'cctv' => 'IP CCTV design and install for Manchester shops, yards, blocks and warehouses, with GDPR-aware camera positions and a handover for remote viewing. CCTV on this hub is a Manchester local service. Quotes stay POA after a walk-round.',
            'access-control' => 'Fob, token and reader access control for Manchester offices, flats and industrial doors, including fire-release where the strategy needs it. Access control is booked as a local Manchester job. Burnley sites use the Burnley hub.',
            'door-entry' => 'Audio and video door entry for Manchester blocks and managed entrances, including panel swaps and riser repairs. Door entry is a local service from Stockport. We quote POA after we see the existing handsets and cable.',
            'legionella-risk-assessment' => 'Legionella risk assessments and sampling programmes for Manchester landlords and commercial water systems. Water hygiene on this page is local to Manchester. Written scope and POA quote follow the system schematic.',
            'asbestos-survey' => 'Asbestos management surveys for Manchester refurbishments, voids and commercial units, with a register the dutyholder can keep. Surveys are a local Manchester service. We do not invent sample counts or fees before the brief.',
            'nurse-call' => 'Nurse call install, takeover and planned maintenance for Manchester care settings, to the specification the home already runs. Nurse call is booked locally. Parts and labour are POA after we see the existing system.',
            'plumbing' => 'Plumbing repairs, replacements and let-ready works for Manchester homes and small commercial sites. Plumbing is a local trade on this hub, not part of the UK mainland fire programme. Burnley plumbing sits on the Burnley area hub.',
        ],
    ];
}

function manchesterBurnleyHubUrl(): string
{
    return url('/pages/areas/burnley');
}

function manchesterHubUrl(): string
{
    return url('/pages/areas/manchester');
}

function manchesterLandingUrl(string $slug): string
{
    return url('/pages/areas/manchester/' . areaSlug($slug));
}

/**
 * @return array{group:string,slug:string,name:string,blurb:string}|null
 */
function manchesterLandingRecord(string $slug): ?array
{
    $slug = areaSlug($slug);
    $services = getServices();
    if (!isset($services[$slug])) {
        return null;
    }
    $owned = manchesterOwnedLandings();
    if (isset($owned['fire'][$slug])) {
        return [
            'group' => 'fire',
            'slug' => $slug,
            'name' => (string)$services[$slug],
            'blurb' => $owned['fire'][$slug],
        ];
    }
    if (isset($owned['local'][$slug])) {
        return [
            'group' => 'local',
            'slug' => $slug,
            'name' => (string)$services[$slug],
            'blurb' => $owned['local'][$slug],
        ];
    }
    return null;
}

function manchesterH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function manchesterEnsureCsrf(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['csrf'];
}

/**
 * @return list<array{0:string,1:string}>
 */
function manchesterHubFaqs(): array
{
    return [
        [
            'Is fire protection only in Manchester?',
            'No. Fire alarms, emergency lighting, fire risk assessments and the related fire trades are scheduled across UK mainland. This hub is the Manchester landing for that nationwide fire work, plus the local non-fire services we run in the city.',
        ],
        [
            'Where is Burnley listed?',
            'Burnley has its own area hub for East Lancashire local services. It is linked from this Manchester page so it is not folded into the city menu or dropped.',
        ],
        [
            'Do you publish a price list for Manchester jobs?',
            'No. Quotes are POA after we know the property, the system and the access. We do not invent a pound figure on these pages.',
        ],
        [
            'Which Manchester services have their own page?',
            'The top fire and local services each have a page under this hub. Every other trade is still listed and links to its service hub.',
        ],
    ];
}

/**
 * @return list<array{0:string,1:string}>
 */
function manchesterLandingFaqs(array $record): array
{
    $name = $record['name'];
    if ($record['group'] === 'fire') {
        return [
            [
                "Do you only attend {$name} jobs in Manchester?",
                "No. {$name} is part of UK mainland fire protection. Manchester is the city landing; the same service is scheduled elsewhere on the mainland, including a separate Burnley area hub for East Lancashire.",
            ],
            [
                "How is {$name} in Manchester quoted?",
                'POA after scope. Tell us the building, the existing system and when you need the visit. We confirm the fee in writing before work is booked.',
            ],
            [
                'Who issues the paperwork?',
                'Icomply Property Services, from 17 Woodlands Park Road, Offerton, Stockport, SK2 5DE. Certificates and logbooks follow the visit where the standard requires them.',
            ],
        ];
    }
    return [
        [
            "Is {$name} a nationwide page?",
            "No. {$name} on this URL is a local Manchester landing. Fire protection is the nationwide offer. Burnley and East Lancashire local jobs are on the Burnley area hub.",
        ],
        [
            "How do I book {$name} in Manchester?",
            'Use the quote form, phone or WhatsApp with the Manchester postcode and a short description of the property. We reply with a POA quote after scope.',
        ],
        [
            'Where are you based?',
            'Stockport SK2. Manchester is typically under 40 minutes from that base, subject to traffic and engineer diaries.',
        ],
    ];
}

/**
 * @param list<array{0:string,1:string}> $faqs
 */
function manchesterFaqHtml(array $faqs): string
{
    $html = '';
    foreach ($faqs as $faq) {
        $html .= '<details class="bg-white border border-zinc-200 rounded-2xl p-5">'
            . '<summary class="font-semibold text-black cursor-pointer">' . manchesterH($faq[0]) . '</summary>'
            . '<p class="mt-3 text-sm text-zinc-700 leading-relaxed">' . manchesterH($faq[1]) . '</p>'
            . '</details>';
    }
    return $html;
}

/**
 * @param list<array{0:string,1:string}> $faqs
 * @return array<string,mixed>
 */
function manchesterFaqSchema(array $faqs): array
{
    $main = [];
    foreach ($faqs as $faq) {
        $main[] = [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq[1],
            ],
        ];
    }
    return [
        '@type' => 'FAQPage',
        'mainEntity' => $main,
    ];
}

function renderManchesterAreaHub(): void
{
    $services = getServices();
    $owned = manchesterOwnedLandings();
    $cats = getServiceCategories();
    $fireSlugs = $cats['fire-safety']['services'] ?? array_keys($owned['fire']);
    $faqs = manchesterHubFaqs();
    $pageTitle = 'Manchester Property Compliance | Fire (UK mainland) & Local Services';
    $metaDesc = 'Manchester area hub from Icomply in Stockport. Fire alarms, FRA and emergency lighting are UK mainland. Electrical, gas, security and landlord services are local — Burnley has its own hub.';
    $metaKeywords = 'Manchester fire alarm, Manchester EICR, Manchester gas safety, Manchester FRA, property compliance Manchester, Burnley area hub';
    $canonicalUrl = manchesterHubUrl();
    $ogImage = url('/assets/images/services/fire-alarms.jpg');
    $csrf = manchesterEnsureCsrf();

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME . ' — Manchester',
                'description' => $metaDesc,
                'url' => $canonicalUrl,
                'telephone' => PHONE,
                'email' => EMAIL,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '17 Woodlands Park Road, Offerton',
                    'addressLocality' => 'Stockport',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
                'areaServed' => [
                    ['@type' => 'City', 'name' => 'Manchester'],
                    ['@type' => 'Country', 'name' => 'United Kingdom'],
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Areas', 'item' => url('/pages/areas')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'Manchester', 'item' => $canonicalUrl],
                ],
            ],
            manchesterFaqSchema($faqs),
        ],
    ];

    require SITE_ROOT . '/includes/header.php';
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    ?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= manchesterH(rtrim(SITE_URL, '/') . '/') ?>" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= manchesterH(url('/pages/areas')) ?>" class="hover:text-white">Areas</a>
            <span>/</span>
            <span class="text-white/80">Manchester</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
            Manchester hub · Stockport SK2
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05] max-w-4xl">
            Property compliance in<br><span class="text-[#ff6b00]">Manchester</span>
        </h1>
        <p class="mt-6 text-lg text-white/80 max-w-3xl">
            Fire protection on this page is a UK mainland service — alarms, emergency lighting, fire risk assessments and related fire trades.
            Electrical, gas, security, water hygiene and landlord certificates are the local Manchester menu.
            Burnley is not folded in here; it keeps its own area hub.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#fire" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Fire — UK mainland</a>
            <a href="#local" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Local Manchester services</a>
            <a href="<?= manchesterH(manchesterBurnleyHubUrl()) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Burnley hub</a>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Fire, UK mainland', 'Alarms, lighting, FRA and related fire trades beyond Manchester'],
            ['Local Manchester menu', 'Electrical, gas, security, landlord, water and building trades'],
            ['Burnley stays listed', 'East Lancashire local work sits on the Burnley area hub'],
            ['POA quotes', 'Written price after scope — no invented tariff'],
        ];
        foreach ($trust as [$title, $body]): ?>
            <div>
                <div class="font-semibold text-black"><?= manchesterH($title) ?></div>
                <div class="text-sm text-zinc-600 mt-1"><?= manchesterH($body) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="fire" class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nationwide fire story</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fire protection across UK mainland</h2>
    <p class="mt-4 text-lg text-zinc-700 max-w-3xl">
        Manchester is where many of our fire diaries start, not where they stop. Fire alarms, emergency lighting,
        fire risk assessments, fire doors, extinguishers, smoke and CO alarms, and AOV are scheduled across UK mainland.
        The cards below open a Manchester landing where we have written one, or the service hub for the rest of the fire catalogue.
    </p>
    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($fireSlugs as $slug):
            if (!isset($services[$slug])) {
                continue;
            }
            $hasLanding = isset($owned['fire'][$slug]);
            $href = $hasLanding ? manchesterLandingUrl($slug) : url('/pages/services/' . $slug);
            $name = (string)$services[$slug];
            ?>
            <a href="<?= manchesterH($href) ?>" class="group bg-white border border-zinc-200 rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#ff6b00]">UK mainland</div>
                <h3 class="mt-2 text-xl font-semibold text-black"><?= manchesterH($name) ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= manchesterH(getServiceBlurb($slug, true)) ?></p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]"><?= $hasLanding ? 'Manchester landing →' : 'Service hub →' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section id="local" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local services</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Manchester services that are not the fire programme</h2>
        <p class="mt-4 text-lg text-zinc-700 max-w-3xl">
            Electrical, gas, security, landlord certificates, water hygiene and the building trades are local.
            Top jobs have a Manchester page. Everything else still links to the live service hub.
            For Burnley and East Lancashire, use the Burnley hub rather than these city URLs.
        </p>
        <?php
        $localGroups = $cats;
        unset($localGroups['fire-safety']);
        foreach ($localGroups as $cat):
            $slugs = $cat['services'] ?? [];
            ?>
            <h3 class="mt-12 text-2xl font-semibold text-black"><?= manchesterH((string)($cat['label'] ?? 'Services')) ?></h3>
            <div class="mt-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($slugs as $slug):
                    if (!isset($services[$slug])) {
                        continue;
                    }
                    $hasLanding = isset($owned['local'][$slug]);
                    $href = $hasLanding ? manchesterLandingUrl($slug) : url('/pages/services/' . $slug);
                    ?>
                    <a href="<?= manchesterH($href) ?>" class="bg-white border border-zinc-200 rounded-2xl p-5 hover:border-[#ff6b00] transition">
                        <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500"><?= $hasLanding ? 'Manchester page' : 'Service hub' ?></div>
                        <div class="mt-1 font-semibold text-black"><?= manchesterH((string)$services[$slug]) ?></div>
                        <p class="mt-2 text-sm text-zinc-600"><?= manchesterH(getServiceBlurb($slug, true)) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="burnley" class="max-w-7xl mx-auto px-6 py-16">
    <div class="rounded-3xl bg-[#0B1F3A] text-white p-8 md:p-10 grid lg:grid-cols-2 gap-8 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">East Lancashire</div>
            <h2 class="text-3xl font-semibold tracking-tight mt-2">Burnley is a separate hub</h2>
            <p class="mt-4 text-white/80">
                Burnley (BB10–BB12) keeps its own area hub for local services — terraces, mills and industrial estates,
                typically 55–75 minutes from Stockport. This Manchester page links it so East Lancashire is not forgotten
                and is not rewritten as a Manchester URL.
            </p>
        </div>
        <div class="flex flex-wrap gap-3 lg:justify-end">
            <a href="<?= manchesterH(manchesterBurnleyHubUrl()) ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-semibold">Open the Burnley hub</a>
            <a href="<?= manchesterH(url('/pages/areas/blackburn')) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">Blackburn</a>
            <a href="<?= manchesterH(url('/pages/areas/accrington')) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">Accrington</a>
        </div>
    </div>
</section>

<section class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-8">Manchester hub FAQ</h2>
        <div class="space-y-4"><?= manchesterFaqHtml($faqs) ?></div>
    </div>
</section>

<?php manchesterQuoteForm('Manchester', 'Property compliance in Manchester', $csrf, $services); ?>
<?php
    require SITE_ROOT . '/includes/footer.php';
}

function renderManchesterServiceLanding(string $slug): void
{
    $record = manchesterLandingRecord($slug);
    if ($record === null) {
        http_response_code(404);
        echo 'Manchester landing not found';
        return;
    }
    $services = getServices();
    $name = $record['name'];
    $isFire = $record['group'] === 'fire';
    $faqs = manchesterLandingFaqs($record);
    $scope = $isFire ? 'UK mainland fire protection' : 'Local Manchester service';
    $pageTitle = $name . ' in Manchester | ' . ($isFire ? 'UK mainland fire' : 'Local service');
    $metaDesc = $isFire
        ? ($name . ' in Manchester by Icomply. Part of UK mainland fire protection, attended from Stockport. POA after scope. Burnley has its own hub.')
        : ($name . ' in Manchester by Icomply. Local service from Stockport SK2. POA after scope. Burnley local jobs use the Burnley area hub.');
    $metaKeywords = $name . ' Manchester, ' . $name . ' Stockport, ' . ($isFire ? 'UK mainland fire protection' : 'Manchester property compliance');
    $canonicalUrl = manchesterLandingUrl($record['slug']);
    $ogImage = serviceImageUrl($record['slug']);
    $csrf = manchesterEnsureCsrf();
    $standards = getServiceStandards($record['slug']);

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                'name' => $name . ' in Manchester',
                'description' => $metaDesc,
                'url' => $canonicalUrl,
                'serviceType' => $name,
                'areaServed' => $isFire
                    ? [['@type' => 'City', 'name' => 'Manchester'], ['@type' => 'Country', 'name' => 'United Kingdom']]
                    : [['@type' => 'City', 'name' => 'Manchester']],
                'provider' => [
                    '@type' => 'LocalBusiness',
                    'name' => SITE_NAME,
                    'telephone' => PHONE,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => '17 Woodlands Park Road, Offerton',
                        'addressLocality' => 'Stockport',
                        'postalCode' => 'SK2 5DE',
                        'addressCountry' => 'GB',
                    ],
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Areas', 'item' => url('/pages/areas')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'Manchester', 'item' => manchesterHubUrl()],
                    ['@type' => 'ListItem', 'position' => 4, 'name' => $name, 'item' => $canonicalUrl],
                ],
            ],
            manchesterFaqSchema($faqs),
        ],
    ];

    require SITE_ROOT . '/includes/header.php';
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    $owned = manchesterOwnedLandings();
    ?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= manchesterH(rtrim(SITE_URL, '/') . '/') ?>" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= manchesterH(url('/pages/areas')) ?>" class="hover:text-white">Areas</a>
            <span>/</span>
            <a href="<?= manchesterH(manchesterHubUrl()) ?>" class="hover:text-white">Manchester</a>
            <span>/</span>
            <span class="text-white/80"><?= manchesterH($name) ?></span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
            <?= manchesterH($scope) ?>
        </div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05] max-w-4xl">
            <?= manchesterH($name) ?> in <span class="text-[#ff6b00]">Manchester</span>
        </h1>
        <p class="mt-6 text-lg text-white/80 max-w-3xl"><?= manchesterH($record['blurb']) ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a POA quote</a>
            <a href="<?= manchesterH(url('/pages/services/' . $record['slug'])) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Service hub</a>
            <a href="<?= manchesterH(manchesterBurnleyHubUrl()) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Burnley hub</a>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 grid lg:grid-cols-3 gap-10">
    <div class="lg:col-span-2">
        <h2 class="text-3xl font-semibold tracking-tight text-black">What this Manchester page covers</h2>
        <p class="mt-4 text-lg text-zinc-700 leading-relaxed"><?= manchesterH(getServiceBlurb($record['slug'])) ?></p>
        <p class="mt-4 text-zinc-700 leading-relaxed">
            <?php if ($isFire): ?>
                Treat this URL as the Manchester entry to a UK mainland fire service. It does not replace the service hub, and it does not replace the Burnley area hub.
            <?php else: ?>
                Treat this URL as a local Manchester landing. It is not a nationwide doorway. East Lancashire local enquiries belong on the Burnley area hub.
            <?php endif; ?>
        </p>
        <?php if ($standards !== ''): ?>
            <p class="mt-4 text-sm text-zinc-600"><span class="font-semibold text-black">Standards: </span><?= manchesterH($standards) ?></p>
        <?php endif; ?>
    </div>
    <aside class="bg-zinc-50 border border-zinc-200 rounded-3xl p-6">
        <h2 class="font-semibold text-black">Also on the Manchester hub</h2>
        <ul class="mt-4 space-y-2 text-sm">
            <?php
            $peers = array_merge(array_keys($owned['fire']), array_keys($owned['local']));
            foreach ($peers as $peer):
                if ($peer === $record['slug'] || !isset($services[$peer])) {
                    continue;
                }
                ?>
                <li><a class="text-[#ff6b00] font-semibold hover:underline" href="<?= manchesterH(manchesterLandingUrl($peer)) ?>"><?= manchesterH((string)$services[$peer]) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <a class="mt-6 inline-block text-sm font-semibold text-black underline" href="<?= manchesterH(manchesterHubUrl()) ?>">Back to the Manchester hub</a>
    </aside>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-8"><?= manchesterH($name) ?> FAQ</h2>
        <div class="space-y-4"><?= manchesterFaqHtml($faqs) ?></div>
    </div>
</section>

<?php manchesterQuoteForm('Manchester', $name . ' in Manchester', $csrf, $services, $name); ?>
<?php
    require SITE_ROOT . '/includes/footer.php';
}

/**
 * @param array<string,string> $services
 */
function manchesterQuoteForm(string $area, string $heading, string $csrf, array $services, string $selected = ''): void
{
    ?>
<section id="quote" class="bg-white border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center"><?= manchesterH($heading) ?></h2>
        <p class="mt-3 text-center text-zinc-600">Postcode, property type and the system already on site. Quotes are POA.</p>
        <form action="<?= manchesterH(url('/contact')) ?>" method="POST" class="mt-8 bg-zinc-50 border rounded-3xl p-6 md:p-8 space-y-4">
            <input type="hidden" name="csrf" value="<?= manchesterH($csrf) ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <?php foreach ($services as $slug => $label): ?>
                        <option value="<?= manchesterH((string)$label) ?>"<?= $selected !== '' && $selected === (string)$label ? ' selected' : '' ?>>
                            <?= manchesterH((string)$label . ' in ' . $area) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Manchester postcode, property type, and what you need quoted…" class="w-full border px-5 py-3.5 rounded-2xl bg-white"></textarea>
            <button type="submit" class="w-full bg-[#ff6b00] text-white py-4 rounded-2xl font-semibold">Request quote</button>
        </form>
        <p class="mt-4 text-center text-sm text-zinc-500">
            <a class="underline" href="tel:<?= manchesterH(preg_replace('/\s+/', '', PHONE)) ?>"><?= manchesterH(PHONE) ?></a>
            · WhatsApp <?= manchesterH(PHONE) ?>
        </p>
    </div>
</section>
    <?php
}
