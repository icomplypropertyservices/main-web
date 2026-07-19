<?php
/**
 * Service hub template. Placeholders: SERVICE_NAME, SERVICE_SLUG, SEO_KEYWORDS,
 * MANUFACTURER_TAGS, MANUFACTURER_IMAGES
 */
$pageTitle = $SERVICE_NAME . ' Services | North West';
$metaDesc = 'Professional ' . $SERVICE_NAME . ' across Greater Manchester and the North West. Installation, maintenance, testing & certification. Fixed-price quotes. Local engineers from Stockport.';
$metaKeywords = $SEO_KEYWORDS;
$ogImage = url('/assets/images/services/' . $SERVICE_SLUG . '.jpg');

$allServices = getServices();
$allAreas = getAreas();
$serviceSlug = $SERVICE_SLUG;
$serviceName = $SERVICE_NAME;

$serviceFaqs = [
    'electrical' => [
        ['How often is an EICR required?', 'Landlords typically need an EICR every 5 years (or on change of tenancy). Commercial premises often follow a risk-based schedule of 1–5 years.'],
        ['Do you offer same-week electrical appointments?', 'Where engineer capacity and site access allow, yes — especially for landlord certificates and urgent remedial work across the North West.'],
        ['Can you upgrade consumer units and install EV chargers?', 'Yes. We design and install consumer unit upgrades, rewires, EV chargers and commercial electrical works to current regulations with full certification.'],
    ],
    'fire-alarms' => [
        ['What is BS 5839 compliance?', 'BS 5839 is the British Standard covering design, installation, commissioning and maintenance of fire detection and alarm systems in buildings.'],
        ['How often do fire alarms need servicing?', 'Most systems need servicing at least twice a year, with weekly user tests and full documentation for insurers and fire officers.'],
        ['Do you support existing panels (Kentec, Advanced, C-Tec)?', 'Yes. We install, service, reprogram and upgrade major brands including Kentec, Advanced, C-Tec, Morley, Hochiki and Apollo.'],
    ],
    'emergency-lighting' => [
        ['How often should emergency lighting be tested?', 'Monthly functional tests and annual full-duration tests are required under BS 5266, with records kept for compliance.'],
        ['Can you convert fluorescent emergency fittings to LED?', 'Yes. We supply and fit LED conversions and full system upgrades while maintaining correct coverage and certification.'],
        ['Do you issue emergency lighting certificates?', 'Every planned test and install includes documentation suitable for landlords, facilities managers and insurers.'],
    ],
    'default' => [
        ['What areas do you cover for ' . $SERVICE_NAME . '?', 'We cover 150+ towns across Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria from our Stockport base.'],
        ['Do you provide fixed-price quotes?', 'Yes. After we confirm scope, standards and access we issue a clear fixed-price quote with no jargon.'],
        ['Can you maintain systems already on site?', 'Absolutely — we inspect, service, repair and upgrade existing installations and issue matching compliance documentation.'],
    ],
];

$blurb = getServiceBlurb($serviceSlug);
$standards = getServiceStandards($serviceSlug);
$faqs = $serviceFaqs[$serviceSlug] ?? $serviceFaqs['default'];

$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool', 'Bury', 'Sale', 'Altrincham', 'Macclesfield', 'Burnley', 'Blackburn', 'Warrington', 'St Helens'],
    function ($t) use ($allAreas) {
        return in_array($t, $allAreas, true);
    }
));
$popularTowns = array_values(array_unique($popularTowns));
$popularTowns = array_slice($popularTowns, 0, 16);

$keywordImages = getKeywordImages($serviceSlug);
$img2 = $keywordImages[0] ?? $serviceSlug;
$img3 = $keywordImages[1] ?? $serviceSlug;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/related.php';
$canonicalUrl = url('/pages/services/' . $serviceSlug . '.php');
require SITE_ROOT . '/includes/header.php';

$faqEntities = [];
foreach ($faqs as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => str_replace($SERVICE_NAME, $serviceName, $faq[0]),
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => str_replace($SERVICE_NAME, $serviceName, $faq[1]),
        ],
    ];
}
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            '@id' => $canonicalUrl . '#service',
            'name' => $serviceName . ' Services',
            'alternateName' => $serviceName . ' installation, maintenance and certification',
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'image' => $ogImage,
            'serviceType' => $serviceName,
            'category' => $serviceName,
            'provider' => [
                '@type' => 'LocalBusiness',
                '@id' => rtrim(SITE_URL, '/') . '/#business',
                'name' => SITE_NAME,
                'url' => SITE_URL,
                'telephone' => PHONE,
                'email' => EMAIL,
                'image' => $ogImage,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '17 Woodlands Park Road',
                    'addressLocality' => 'Offerton, Stockport',
                    'addressRegion' => 'Greater Manchester',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => '53.3904',
                    'longitude' => '-2.1219',
                ],
                'priceRange' => '££',
            ],
            'areaServed' => array_map(static function ($region) {
                return ['@type' => 'AdministrativeArea', 'name' => $region];
            }, ['Greater Manchester', 'Lancashire', 'Cheshire', 'Merseyside', 'Cumbria', 'North West England']),
            'offers' => [
                '@type' => 'Offer',
                'name' => 'Free fixed-price quote — ' . $serviceName,
                'description' => 'Request a free fixed-price quote for ' . $serviceName . ' installation, servicing and certification.',
                'availability' => 'https://schema.org/InStock',
                'priceCurrency' => 'GBP',
                'url' => url('/contact.php'),
            ],
            'brand' => [
                '@type' => 'Brand',
                'name' => SITE_NAME,
            ],
            'termsOfService' => url('/terms.php'),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl . '#webpage',
                'url' => $canonicalUrl,
                'name' => $pageTitle,
                'description' => $metaDesc,
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => SITE_NAME,
                    'url' => SITE_URL,
                ],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => url('/pages/services/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceName, 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => $canonicalUrl . '#faq',
            'mainEntity' => $faqEntities,
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 15% 30%,#ff6b00,transparent 42%),radial-gradient(circle at 85% 10%,#3b82f6,transparent 38%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?>.<br>
                    <span class="text-[#ff6b00]">Installed, tested, certified.</span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get free quote</a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Quote for ' . $serviceName) ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                    <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <p class="mt-6 text-sm text-white/60"><?= htmlspecialchars($standards, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="relative rounded-3xl overflow-hidden border border-white/10 min-h-[260px] bg-white/5">
                <img src="<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> by Icomply Property Services"
                     class="absolute inset-0 w-full h-full object-cover opacity-70"
                     loading="eager"
                     onerror="this.style.display='none'">
                <div class="relative p-6 md:p-8 flex flex-col justify-end min-h-[260px] bg-gradient-to-t from-[#0a2540]/90 via-[#0a2540]/20 to-transparent">
                    <div class="text-sm text-white/70">Serving <?= count($allAreas) ?>+ towns</div>
                    <div class="text-2xl font-semibold mt-1">Local engineers · Fixed-price quotes</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local response', 'Stockport-based engineers across Greater Manchester & the North West'],
            ['Standards-led', $standards],
            ['Full certification', 'Documentation for landlords, insurers and fire officers'],
            ['Trade shop', 'Kits & parts via our Shopify-ready shop'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- INTRO + IMAGES -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-5 gap-12">
        <div class="lg:col-span-3">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Overview</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Expert <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> across the North West
            </h2>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">
                Icomply Property Services designs, installs, commissions, maintains and certifies
                <strong><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></strong>
                for commercial, industrial, multi-let, care and residential properties across Greater Manchester,
                Lancashire, Cheshire, Merseyside and Cumbria.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                From new system design to reactive call-outs and planned maintenance contracts, our engineers deliver
                fixed-price quotes, clear scope and full compliance documentation. Based in Stockport (SK2), we cover
                Manchester, Bolton, Oldham, Rochdale, Wigan, Liverpool, Preston and 140+ surrounding towns.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Searching for a specific manufacturer? We install and service the major brands listed below so you can
                find local support for the exact panel or equipment already on site.
            </p>
        </div>
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-3xl overflow-hidden border bg-zinc-100">
                <img src="<?= url('/assets/images/keywords/' . htmlspecialchars($img2, ENT_QUOTES, 'UTF-8') . '.jpg') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> equipment"
                     class="w-full h-44 object-cover"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
            </div>
            <div class="rounded-3xl overflow-hidden border bg-zinc-100">
                <img src="<?= url('/assets/images/keywords/' . htmlspecialchars($img3, ENT_QUOTES, 'UTF-8') . '.jpg') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> installation work"
                     class="w-full h-44 object-cover"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
            </div>
        </div>
    </div>

    <!-- Pillars -->
    <div class="mt-14 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border hover:border-[#ff6b00] transition">
            <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white flex items-center justify-center font-bold mb-4">1</div>
            <h3 class="font-semibold text-xl text-black mb-2">Installation &amp; design</h3>
            <p class="text-sm text-zinc-600">Full design, supply and install of new <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> systems to current British Standards and manufacturer guidance.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border hover:border-[#ff6b00] transition">
            <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white flex items-center justify-center font-bold mb-4">2</div>
            <h3 class="font-semibold text-xl text-black mb-2">Maintenance &amp; servicing</h3>
            <p class="text-sm text-zinc-600">Planned contracts, reactive repairs, battery replacements and panel upgrades for systems already on site.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border hover:border-[#ff6b00] transition">
            <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white flex items-center justify-center font-bold mb-4">3</div>
            <h3 class="font-semibold text-xl text-black mb-2">Testing &amp; certification</h3>
            <p class="text-sm text-zinc-600">Statutory tests with logbooks, certificates and documentation ready for audits, insurers and landlords.</p>
        </div>
    </div>
</section>

<!-- MANUFACTURERS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Manufacturers</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Brands we install &amp; service</h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">Looking for your exact panel brand? We support major <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> manufacturers across the North West.</p>
            </div>
            <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Browse trade shop →</a>
        </div>
        <div class="flex flex-wrap gap-3 mb-8"><?= $MANUFACTURER_TAGS ?></div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4"><?= $MANUFACTURER_IMAGES ?></div>
        <div class="mt-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Related brands</div>
            <h3 class="text-xl font-semibold tracking-tight text-black mb-5">More manufacturers for this service</h3>
            <?= relatedManufacturersHtml($serviceSlug, 8) ?>
        </div>
    </div>
</section>

<!-- KEYWORD GUIDES (every topic for this service → each has pages for all areas) -->
<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Topic guides</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> keywords &amp; local pages
                </h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">
                    Every guide below has a dedicated page for each town we cover
                    (e.g. <strong>EICR report in Stockport</strong>). Click a topic, then pick your area.
                </p>
            </div>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All keyword guides →</a>
        </div>
        <?php
        $svcKeywords = getKeywordsForService($serviceSlug);
        if ($svcKeywords):
            $kwPreviewTowns = array_slice($popularTowns, 0, 6);
        ?>
        <div class="mb-8">
            <?= relatedKeywordsHtml($serviceSlug, 0) ?>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $shown = 0;
            foreach ($svcKeywords as $kwSlug => $kwMeta):
                if ($shown >= 18) {
                    break;
                }
                $shown++;
                $kwName = (string)($kwMeta['name'] ?? keywordDisplayName($kwSlug));
            ?>
            <div class="p-5 bg-zinc-50 border border-zinc-200 rounded-2xl hover:border-[#ff6b00] transition">
                <a href="<?= url('/pages/keywords/' . rawurlencode($kwSlug) . '.php') ?>" class="font-semibold text-black hover:text-[#ff6b00]">
                    <?= htmlspecialchars($kwName, ENT_QUOTES, 'UTF-8') ?>
                </a>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <?php foreach ($kwPreviewTowns as $town): ?>
                        <a href="<?= url('/pages/keywords/' . rawurlencode($kwSlug) . '/' . areaSlug($town) . '.php') ?>"
                           class="text-[11px] px-2 py-1 bg-white border rounded-full text-zinc-700 hover:border-[#ff6b00] hover:text-[#ff6b00]">
                            <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('/pages/keywords/' . rawurlencode($kwSlug) . '.php') ?>"
                       class="text-[11px] px-2 py-1 font-semibold text-[#ff6b00]">
                        +<?= max(0, count($allAreas) - count($kwPreviewTowns)) ?> towns →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($svcKeywords) > 18): ?>
            <p class="mt-6 text-sm text-zinc-600">
                Showing 18 of <?= count($svcKeywords) ?> <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> guides —
                <a href="<?= url('/pages/keywords/index.php') ?>" class="font-semibold text-[#ff6b00]">view full keyword index</a>.
            </p>
        <?php endif; ?>
        <?php else: ?>
            <p class="text-zinc-600">Keyword guides for this service are being expanded. See the <a class="text-[#ff6b00] font-semibold" href="<?= url('/pages/keywords/index.php') ?>">full guides index</a>.</p>
        <?php endif; ?>
    </div>
</section>

<!-- AREAS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local pages</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> near you
            </h2>
            <p class="mt-2 text-zinc-600">Pick a town for a dedicated <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> landing page with local SEO and quote CTAs.</p>
        </div>
        <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All areas →</a>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($popularTowns as $a): ?>
            <a href="<?= url('/pages/' . $SERVICE_SLUG . '/' . areaSlug($a) . '.php') ?>"
               class="px-5 py-2.5 bg-white border rounded-full text-sm font-medium text-black hover:border-[#ff6b00] hover:shadow-sm transition">
                <?= htmlspecialchars($serviceName . ' in ' . $a, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 flex flex-wrap gap-2">
        <?php foreach (array_slice($allAreas, 0, 40) as $a):
            if (in_array($a, $popularTowns, true)) continue;
        ?>
            <a href="<?= url('/pages/' . $SERVICE_SLUG . '/' . areaSlug($a) . '.php') ?>"
               class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs text-zinc-700 hover:border-[#ff6b00]">
                <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= url('/pages/areas/index.php') ?>" class="px-3 py-1.5 text-xs font-semibold text-[#ff6b00]">+ more towns</a>
    </div>
</section>

<!-- RELATED SERVICES -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Also available</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Related compliance services</h2>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php foreach ($allServices as $slug => $name):
                if ($slug === $serviceSlug) continue;
                $rBlurb = getServiceBlurb($slug, true);
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="group bg-white border rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-32 bg-zinc-100 overflow-hidden">
                    <img src="<?= url('/assets/images/services/' . $slug . '.jpg') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-semibold text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1 line-clamp-2"><?= htmlspecialchars($rBlurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-3 text-sm font-semibold text-[#ff6b00]">Explore →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold text-center">FAQ</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2 text-center mb-10">
            <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> questions
        </h2>
        <div class="space-y-4">
            <?php foreach ($faqs as $faq):
                $q = str_replace($SERVICE_NAME, $serviceName, $faq[0]);
                $a = str_replace($SERVICE_NAME, $serviceName, $faq[1]);
            ?>
            <details class="bg-white border rounded-2xl p-5 group">
                <summary class="font-semibold text-black cursor-pointer list-none flex justify-between items-center gap-4">
                    <?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>
                    <span class="text-[#ff6b00] group-open:rotate-45 transition text-xl leading-none">+</span>
                </summary>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA BAND -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Need <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?>?</h2>
            <p class="mt-3 text-white/75">Free fixed-price quotes. Same-week appointments where capacity allows. Full certification on every job.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Quote for ' . $serviceName) ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp quote</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Trade shop</a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Based in Stockport — North West coverage</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Installation, servicing and certification</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Multi-service packages for landlords &amp; FM teams</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Response aim: within 2 hours on business days</li>
        </ul>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 pt-8">
    <?= shareButtonsHtml($serviceName . ' Services', $metaDesc) ?>
</section>

<?php
require_once SITE_ROOT . '/includes/testimonials.php';
echo testimonialsSectionHtml();
?>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Request <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> quote
            </h2>
            <p class="mt-3 text-zinc-600">Tell us the postcode, property type and any panel brand — we aim to respond within 2 hours on business days.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $slug === $serviceSlug ? ' selected' : '' ?>>
                            <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="Postcode, property type, panel brand / system details…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
