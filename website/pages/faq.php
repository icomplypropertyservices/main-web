<?php
/**
 * FAQ — property compliance questions (EICR, fire, gas, lighting, CCTV, access, quotes, shop).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'FAQ | Property Compliance Questions Answered';
$metaDesc = 'Frequently asked questions about EICR, BS 5839 fire alarms, emergency lighting, gas safety, CCTV, access control, response times, coverage areas, free quotes and the Icomply shop.';
$metaKeywords = 'property compliance FAQ, EICR questions, BS 5839 fire alarm, emergency lighting testing, gas safety certificate, CCTV installation, access control, North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/faq.php');

$services = getServices();
$areas = getAreas();
$areaCount = count($areas);

$faqs = [
    // Electrical / EICR
    [
        'cat' => 'Electrical & EICR',
        'q' => 'What is an EICR and how often do I need one?',
        'a' => 'An Electrical Installation Condition Report (EICR) is a formal inspection and test of the fixed wiring in a property against BS 7671. Landlords in England typically need a satisfactory EICR at least every 5 years (or sooner if the report recommends it), and when a new tenancy starts if the existing report has expired. Commercial and higher-risk premises may require more frequent testing. We issue clear certificates and remedial quotes where C1/C2/FI codes appear.',
    ],
    [
        'cat' => 'Electrical & EICR',
        'q' => 'Do you carry out rewires, consumer unit upgrades and EV charger installs?',
        'a' => 'Yes. Alongside EICR and PAT testing we install and upgrade consumer units, full and partial rewires, commercial electrical works and EV charge points (including popular brands such as myenergi and Rolec). All work is to BS 7671 with appropriate certification. See our Electrical service hub for local pages and a free quote.',
        'link' => ['/pages/services/electrical.php', 'Electrical services'],
    ],
    // Fire alarms
    [
        'cat' => 'Fire alarms (BS 5839)',
        'q' => 'What is BS 5839 and why does my fire alarm need to comply?',
        'a' => 'BS 5839 is the British Standard for fire detection and fire alarm systems in buildings. Part 1 covers non-domestic premises; Part 6 covers dwellings. Insurers, fire officers, landlords and responsible persons rely on BS 5839 design, installation, commissioning and maintenance so systems detect fire early and alert occupants reliably. We design, install, service and certify systems to the relevant parts of BS 5839 and manufacturer guidance.',
    ],
    [
        'cat' => 'Fire alarms (BS 5839)',
        'q' => 'How often should a commercial fire alarm be serviced?',
        'a' => 'Under BS 5839, fire alarm systems should be inspected and serviced by a competent person at least every 6 months (typically two visits per year), with weekly user tests and a maintained logbook. We offer planned maintenance contracts, reactive call-outs, battery replacements and full certification for addressable, conventional and wireless systems from brands such as Kentec, Advanced, C-Tec, Morley, Hochiki and Apollo.',
        'link' => ['/pages/services/fire-alarms.php', 'Fire alarm services'],
    ],
    [
        'cat' => 'Fire alarms (BS 5839)',
        'q' => 'Can you maintain or upgrade my existing fire panel?',
        'a' => 'Yes. We survey existing loops and devices, diagnose faults, replace batteries and devices, and recommend compliant upgrades or panel replacements where systems are obsolete or non-compliant. Work is commissioned with certificates and logbook updates suitable for insurers and fire risk assessments.',
    ],
    // Emergency lighting
    [
        'cat' => 'Emergency lighting',
        'q' => 'How often does emergency lighting need testing?',
        'a' => 'BS 5266 expects regular function tests (often monthly) and a full duration test at least annually, with records kept for the responsible person. We provide monthly/annual testing programmes, LED conversions, new installations and certification for escape routes, open areas and high-risk task lighting.',
        'link' => ['/pages/services/emergency-lighting.php', 'Emergency lighting services'],
    ],
    [
        'cat' => 'Emergency lighting',
        'q' => 'Do you convert old fluorescent emergency fittings to LED?',
        'a' => 'Yes. We upgrade tired fluorescent emergency luminaires to efficient LED self-contained or central-battery solutions, improving reliability and reducing maintenance while meeting BS 5266 performance requirements.',
    ],
    // Gas safety
    [
        'cat' => 'Gas safety',
        'q' => 'What is a landlord gas safety certificate (CP12)?',
        'a' => 'A landlord gas safety record (often called a CP12) confirms that gas appliances, flues and pipework in a rented property have been checked by a Gas Safe registered engineer. Landlords must have checks at least every 12 months and issue the record to tenants. We provide CP12 / CP44 landlord certificates, boiler servicing and commercial gas safety work.',
        'link' => ['/pages/services/gas-systems.php', 'Gas systems services'],
    ],
    [
        'cat' => 'Gas safety',
        'q' => 'Do you service commercial gas plant as well as domestic boilers?',
        'a' => 'Yes. We cover domestic landlord certificates and commercial gas systems where within our competence and registration, including safety checks and planned servicing. Tell us the appliance type and site postcode on the quote form for an accurate scope.',
    ],
    // CCTV
    [
        'cat' => 'CCTV',
        'q' => 'What CCTV systems do you install?',
        'a' => 'We design and install IP and HD CCTV for residential, multi-let, retail, industrial and commercial sites — including NVR/DVR recording, remote viewing, and cameras from manufacturers such as Hikvision, Dahua and Axis (subject to site suitability and current product availability). Systems are surveyed for coverage, lighting and network requirements.',
        'link' => ['/pages/services/cctv.php', 'CCTV services'],
    ],
    [
        'cat' => 'CCTV',
        'q' => 'Can I view my CCTV remotely on a phone?',
        'a' => 'Most modern IP systems support secure remote viewing via manufacturer apps or client software once network access is configured. We set up recording, user access and remote viewing as part of commissioning where required.',
    ],
    // Access control / door entry
    [
        'cat' => 'Access control & door entry',
        'q' => 'Which access control brands do you work with?',
        'a' => 'We install and maintain door access systems including Paxton, Salto, HID and other leading platforms, with credentials (fobs, cards, mobile), time schedules, fire-override integration and multi-door networks for flats, offices and sites.',
        'link' => ['/pages/services/access-control.php', 'Access control services'],
    ],
    [
        'cat' => 'Access control & door entry',
        'q' => 'Do you install video door entry and intercoms for apartment blocks?',
        'a' => 'Yes. We supply and install audio and video door entry plus multi-tenant intercoms (including brands such as Aiphone, Fermax and Videx where suitable), wired for flats, HMOs and commercial receptions. Fire release and access control can be integrated on the same project.',
        'link' => ['/pages/services/door-entry.php', 'Door entry services'],
    ],
    // Response times
    [
        'cat' => 'Response times & appointments',
        'q' => 'How quickly do you respond to enquiries and emergencies?',
        'a' => 'On business days we aim to respond to quote and contact requests within 2 hours during opening hours (typically Monday–Friday 08:00–18:00). Same-week appointments are often available subject to engineer capacity and site access. Urgent fault call-outs for fire, life-safety and security systems are prioritised where capacity allows — call or WhatsApp for the fastest response.',
    ],
    [
        'cat' => 'Response times & appointments',
        'q' => 'Do you offer planned maintenance contracts?',
        'a' => 'Yes. Many landlords and facilities managers use planned maintenance for fire alarms, emergency lighting, nurse call, AOV and other systems so visits, logbooks and certificates stay on schedule. Ask for a multi-service package if you need several compliance streams under one provider.',
    ],
    // Areas
    [
        'cat' => 'Areas we cover',
        'q' => 'Which areas do you cover?',
        'a' => 'We are based in Offerton, Stockport (SK2) and cover Greater Manchester, Cheshire, Lancashire, Merseyside and parts of Cumbria — ' . $areaCount . '+ towns including Manchester, Stockport, Bolton, Oldham, Rochdale, Wigan, Salford, Liverpool, Preston, Blackpool, Chester and Warrington. Check our areas index for a full town list and local service pages.',
        'link' => ['/pages/areas/index.php', 'All areas we cover'],
    ],
    [
        'cat' => 'Areas we cover',
        'q' => 'Will you travel outside Greater Manchester?',
        'a' => 'Yes, across the wider North West where travel is practical for the job size. Remote or specialist works may attract a travel consideration — we confirm this on the free quote before you book.',
    ],
    // Quotes
    [
        'cat' => 'Quotes & pricing',
        'q' => 'Are quotes free and fixed-price?',
        'a' => 'Yes — initial compliance quotes are free. We provide clear fixed-price scopes based on the information you supply (and a site survey where needed). Quotes may be revised if site conditions, access or system condition differ from what was described. Certification is included for the work agreed in the quote.',
        'link' => ['/contact.php', 'Request a free quote'],
    ],
    [
        'cat' => 'Quotes & pricing',
        'q' => 'What information helps you quote accurately?',
        'a' => 'Postcode and property type, system or panel brand (if known), number of circuits/doors/cameras/appliances, whether it is install vs service vs certificate only, access restrictions and any insurer or landlord deadlines. Photos of consumer units, fire panels or plant rooms speed things up. You can send details via the contact form, phone or WhatsApp.',
    ],
    // Shop
    [
        'cat' => 'Shop & products',
        'q' => 'What can I buy in the Icomply shop?',
        'a' => 'Our trade shop offers compliance-related kits, parts and products via Shopify checkout when the store is live. Product pages show descriptions and pricing; stock and shipping are handled at checkout.',
        'link' => ['/shop/index.php', 'Visit the shop'],
    ],
    [
        'cat' => 'Shop & products',
        'q' => 'Does buying a product include installation?',
        'a' => 'No — purchasing goods does not automatically include installation, commissioning or certification unless expressly stated. Installation is quoted separately so we can match labour and certification to your site. See our Terms for shop and service conditions.',
        'link' => ['/terms.php', 'Terms & conditions'],
    ],
    // Manufacturers / other
    [
        'cat' => 'Manufacturers & other services',
        'q' => 'Which manufacturers do you support?',
        'a' => 'We work with a wide range of industry brands across fire, electrical, gas, CCTV and access — including Apollo, Hochiki, Kentec, Advanced, C-Tec, Paxton, Salto, Hikvision, Dahua, Axis, Hager, Schneider, Worcester Bosch and more. Browse manufacturer pages for brand-specific guidance and related services.',
        'link' => ['/pages/manufacturers/index.php', 'All manufacturers'],
    ],
    [
        'cat' => 'Manufacturers & other services',
        'q' => 'Do you also handle AOV, nurse call and intruder alarms?',
        'a' => 'Yes. In addition to electrical, fire, emergency lighting, gas, CCTV and access control we provide AOV & air handling, nurse call systems, intruder alarms and intercoms — installation, maintenance and certification as appropriate. Start from the services index to open each hub.',
        'link' => ['/pages/services/index.php', 'All services'],
    ],
    [
        'cat' => 'Manufacturers & other services',
        'q' => 'How do I get started?',
        'a' => 'Call ' . PHONE . ', message us on WhatsApp, email ' . EMAIL . ', or use the free quote form on the contact page. Include your postcode and the service you need — we will confirm scope, price and the soonest suitable appointment.',
        'link' => ['/contact.php', 'Contact Icomply'],
    ],
];

// FAQPage JSON-LD
$faqEntities = [];
foreach ($faqs as $item) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $item['a'],
        ],
    ];
}

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'FAQPage',
            'name' => 'Property Compliance FAQ — Icomply Property Services',
            'description' => $metaDesc,
            'url' => url('/pages/faq.php'),
            'mainEntity' => $faqEntities,
            'publisher' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'url' => SITE_URL,
                'telephone' => PHONE,
                'email' => EMAIL,
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
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'FAQ', 'item' => url('/pages/faq.php')],
            ],
        ],
    ],
];

// Group FAQs by category for display
$grouped = [];
foreach ($faqs as $item) {
    $grouped[$item['cat']][] = $item;
}

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">FAQ</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Help centre · <?= count($faqs) ?> answers
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Property compliance<br>
                <span class="text-[#ff6b00]">FAQs</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Straight answers on EICR, BS&nbsp;5839 fire alarms, emergency lighting, gas safety, CCTV, access control,
                response times, coverage, quotes and the trade shop.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#faqs" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Browse FAQs</a>
                <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Free quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20have%20a%20question"
                   target="_blank" rel="noopener"
                   class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<!-- QUICK LINKS -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-4">Jump to</div>
        <div class="flex flex-wrap gap-2">
            <?php
            $anchors = [
                'Electrical & EICR' => 'electrical-eicr',
                'Fire alarms (BS 5839)' => 'fire-alarms-bs-5839',
                'Emergency lighting' => 'emergency-lighting',
                'Gas safety' => 'gas-safety',
                'CCTV' => 'cctv',
                'Access control & door entry' => 'access-control-door-entry',
                'Response times & appointments' => 'response-times-appointments',
                'Areas we cover' => 'areas-we-cover',
                'Quotes & pricing' => 'quotes-pricing',
                'Shop & products' => 'shop-products',
                'Manufacturers & other services' => 'manufacturers-other-services',
            ];
            foreach ($anchors as $label => $id): ?>
                <a href="#<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-4 py-2 rounded-full text-sm font-medium border bg-zinc-50 hover:border-[#ff6b00] hover:text-[#ff6b00] transition text-black">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQS -->
<section id="faqs" class="max-w-3xl mx-auto px-6 py-16 md:py-20">
    <?php
    $slugify = static function (string $cat): string {
        $map = [
            'Electrical & EICR' => 'electrical-eicr',
            'Fire alarms (BS 5839)' => 'fire-alarms-bs-5839',
            'Emergency lighting' => 'emergency-lighting',
            'Gas safety' => 'gas-safety',
            'CCTV' => 'cctv',
            'Access control & door entry' => 'access-control-door-entry',
            'Response times & appointments' => 'response-times-appointments',
            'Areas we cover' => 'areas-we-cover',
            'Quotes & pricing' => 'quotes-pricing',
            'Shop & products' => 'shop-products',
            'Manufacturers & other services' => 'manufacturers-other-services',
        ];
        return $map[$cat] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cat));
    };
    foreach ($grouped as $cat => $items):
        $catId = $slugify($cat);
    ?>
    <div id="<?= htmlspecialchars($catId, ENT_QUOTES, 'UTF-8') ?>" class="mb-12 scroll-mt-28">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></div>
        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-black mt-2 mb-6"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="space-y-4">
            <?php foreach ($items as $item): ?>
            <details class="bg-white border rounded-2xl p-5 group">
                <summary class="font-semibold text-black cursor-pointer list-none flex justify-between items-center gap-4">
                    <span><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-[#ff6b00] group-open:rotate-45 transition text-xl leading-none shrink-0">+</span>
                </summary>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($item['link'])): ?>
                <p class="mt-3">
                    <a href="<?= url($item['link'][0]) ?>" class="text-sm font-semibold text-[#ff6b00] hover:underline">
                        <?= htmlspecialchars($item['link'][1], ENT_QUOTES, 'UTF-8') ?> →
                    </a>
                </p>
                <?php endif; ?>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- RELATED HUBS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold text-center">Explore</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2 text-center mb-10">Services, areas, brands &amp; contact</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="<?= url('/pages/services/index.php') ?>" class="service-card bg-zinc-50 border rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <div class="text-2xl mb-3">⚡</div>
                <div class="font-semibold text-black text-lg">All services</div>
                <p class="mt-2 text-sm text-zinc-600">Electrical, fire, gas, lighting, CCTV, access and more.</p>
                <div class="mt-4 text-sm font-semibold text-[#ff6b00]">Browse services →</div>
            </a>
            <a href="<?= url('/pages/areas/index.php') ?>" class="service-card bg-zinc-50 border rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <div class="text-2xl mb-3">📍</div>
                <div class="font-semibold text-black text-lg">Areas we cover</div>
                <p class="mt-2 text-sm text-zinc-600"><?= (int)$areaCount ?>+ North West towns from our Stockport base.</p>
                <div class="mt-4 text-sm font-semibold text-[#ff6b00]">Find your town →</div>
            </a>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="service-card bg-zinc-50 border rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <div class="text-2xl mb-3">🏭</div>
                <div class="font-semibold text-black text-lg">Manufacturers</div>
                <p class="mt-2 text-sm text-zinc-600">Panel, camera and access brands we install and maintain.</p>
                <div class="mt-4 text-sm font-semibold text-[#ff6b00]">View brands →</div>
            </a>
            <a href="<?= url('/contact.php') ?>" class="service-card bg-zinc-50 border rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <div class="text-2xl mb-3">✉️</div>
                <div class="font-semibold text-black text-lg">Contact / free quote</div>
                <p class="mt-2 text-sm text-zinc-600">Call, WhatsApp or form — aim to reply within 2 hours on business days.</p>
                <div class="mt-4 text-sm font-semibold text-[#ff6b00]">Get a quote →</div>
            </a>
        </div>
        <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
            <a href="<?= url('/shop/index.php') ?>" class="font-semibold text-[#ff6b00] hover:underline">Trade shop →</a>
            <a href="<?= url('/pages/services/electrical.php') ?>" class="text-zinc-600 hover:text-[#ff6b00]">EICR / Electrical</a>
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="text-zinc-600 hover:text-[#ff6b00]">Fire alarms</a>
            <a href="<?= url('/pages/services/emergency-lighting.php') ?>" class="text-zinc-600 hover:text-[#ff6b00]">Emergency lighting</a>
            <a href="<?= url('/pages/services/gas-systems.php') ?>" class="text-zinc-600 hover:text-[#ff6b00]">Gas safety</a>
            <a href="<?= url('/pages/services/cctv.php') ?>" class="text-zinc-600 hover:text-[#ff6b00]">CCTV</a>
            <a href="<?= url('/pages/services/access-control.php') ?>" class="text-zinc-600 hover:text-[#ff6b00]">Access control</a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Still have a question?</h2>
            <p class="mt-3 text-white/75">Tell us your postcode and the system or certificate you need — free fixed-price quotes from Stockport-based engineers.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/contact.php') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request a quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20have%20a%20compliance%20question"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', PHONE), ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> BS 5839 · BS 5266 · BS 7671 · gas safety</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Installation, servicing and certification</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> <?= (int)$areaCount ?>+ towns across the North West</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Response aim: within 2 hours on business days</li>
        </ul>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
