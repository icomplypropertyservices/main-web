<?php
/**
 * FAQ — property compliance questions (EICR, fire, gas, lighting, CCTV, access, quotes, shop).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'FAQ | Property Compliance Questions Answered';
$metaDesc = 'FAQ for iComply Property Services, Stockport. EICR, fire alarms, emergency lighting, gas safety, CCTV, access, construction, quotes and coverage. Call 07517806082. No scheme badges we cannot verify.';
$metaKeywords = 'property compliance FAQ, EICR questions, BS 5839 fire alarm, emergency lighting testing, gas safety certificate, CCTV installation, access control, North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/faq.php');

$services = getServices();
$areas = getAreas();
$areaCount = count($areas);

$faqs = [
    [
        'cat' => 'HMO packages',
        'q' => 'How much is the HMO compliance bundle?',
        'a' => '£650 for a fire risk assessment, an EICR and landlord gas safety on the same typical 6-bed HMO in the North West. Those three are not charged again on top. Portfolio discounts apply to that £650. Remedials, parts, alarms, lighting and travel outside the North West are separate. It is not an HMO licence and not legal advice.',
        'link' => ['/pages/jobs/hmo-compliance.php', 'HMO compliance bundle'],
    ],
    // Electrical / EICR
    [
        'cat' => 'Electrical & EICR',
        'q' => 'What is an EICR and how often do I need one?',
        'a' => 'An Electrical Installation Condition Report (EICR) is an inspection and test of the fixed wiring against BS 7671. For private rented homes in England, the usual rule is a satisfactory report at intervals of no more than 5 years, given to the tenant, and a new one before a tenancy if the current report has run out. The inspector can recommend a shorter interval. Commercial premises follow risk, insurer and client requirements. This is not legal advice. We issue the report for the inspection we carry out and quote remedials where C1, C2 or FI codes appear. We do not name a competent-person scheme on this page.',
    ],
    [
        'cat' => 'Electrical & EICR',
        'q' => 'Do you carry out rewires, consumer unit upgrades and EV charger installs?',
        'a' => 'Yes. The electrical catalogue covers EICR, PAT testing, consumer-unit upgrades, full and partial rewires, commercial electrical work and EV charge points (including equipment from brands such as myenergi and Rolec, subject to what is suitable and available). Work is carried out with BS 7671 in mind. You get the electrical certificate that matches the completed job — an Electrical Installation Certificate or a Minor Electrical Installation Works Certificate where that is the right form. Naming a brand is not an approved-installer badge, and we do not claim NICEIC or another scheme membership here.',
        'link' => ['/pages/services/electrical.php', 'Electrical services'],
    ],
    // Fire alarms
    [
        'cat' => 'Fire alarms (BS 5839)',
        'q' => 'What is BS 5839 and why does my fire alarm need to comply?',
        'a' => 'BS 5839 is the British Standard for fire detection and fire alarm systems. Part 1 is the usual reference for non-domestic premises; Part 6 covers dwellings. Responsible persons, insurers and fire officers use it as the reference for design, installation, commissioning and maintenance. We design, install and service systems with the relevant part of BS 5839 and the manufacturer’s instructions in mind, and we issue the records for the work agreed. We do not claim BAFE or another named fire-scheme membership on this page.',
    ],
    [
        'cat' => 'Fire alarms (BS 5839)',
        'q' => 'How often should a commercial fire alarm be serviced?',
        'a' => 'BS 5839-1 expects a competent person to inspect and service many non-domestic systems at least every 6 months, alongside weekly tests by the user and a logbook. That is a standard, not a promise that every building has the same legal timetable. Where we agree a contract we can plan those visits, attend faults and replace batteries on addressable, conventional and wireless systems. Equipment we work on includes Kentec, Advanced, C-TEC, Morley, Hochiki and Apollo where it is already on site or specified for the job. Listing a brand is not a manufacturer partnership or a scheme badge.',
        'link' => ['/pages/services/fire-alarms.php', 'Fire alarm services'],
    ],
    [
        'cat' => 'Fire alarms (BS 5839)',
        'q' => 'Can you maintain or upgrade my existing fire panel?',
        'a' => 'Yes, after a survey of the loops and devices. We diagnose faults, replace batteries and devices, and recommend an upgrade or panel change where the system is obsolete or does not match the fire risk assessment. Commissioning notes and logbook updates cover the work we did. They are not a BAFE certificate and they do not replace the fire risk assessment.',
    ],
    // Emergency lighting
    [
        'cat' => 'Emergency lighting',
        'q' => 'How often does emergency lighting need testing?',
        'a' => 'BS 5266 is the usual UK reference. The responsible person normally keeps a monthly function test and a full-duration test at least once a year. We can quote a testing programme, LED conversions and new installations for escape routes, open areas and high-risk task areas, and we leave the test records for those visits. We do not invent a legal monthly visit for a home that has no emergency lighting system.',
        'link' => ['/pages/services/emergency-lighting.php', 'Emergency lighting services'],
    ],
    [
        'cat' => 'Emergency lighting',
        'q' => 'Do you convert old fluorescent emergency fittings to LED?',
        'a' => 'Yes, where the existing fittings are due for replacement. We quote LED self-contained or central-battery luminaires and check the result against the BS 5266 design for that building. A lamp swap on its own is not a design certificate.',
    ],
    // Gas safety
    [
        'cat' => 'Gas safety',
        'q' => 'What is a landlord gas safety certificate (CP12)?',
        'a' => 'A landlord gas safety record — people still say CP12 — is the written check of gas appliances, flues and pipework in a rented property. Where those are present, private landlords in Great Britain generally need that check at least every 12 months and must give the record to tenants. Only a Gas Safe registered engineer can do the gas work. This page does not print a company Gas Safe registration number. If we take the job, ask for the attending engineer’s ID and check it on the Gas Safe Register before the visit. CP44 is a name you may hear for some non-domestic records; the document has to match the appliances on site. This is not legal advice.',
        'link' => ['/pages/services/gas-systems.php', 'Gas systems services'],
    ],
    [
        'cat' => 'Gas safety',
        'q' => 'Do you service commercial gas plant as well as domestic boilers?',
        'a' => 'Domestic landlord records and boiler servicing are in the gas catalogue. Commercial plant is quoted only when the attending engineer’s Gas Safe registration covers that appliance type. If it does not, we say so rather than stretch the job. Tell us the appliance and postcode so the scope is honest.',
    ],
    // CCTV
    [
        'cat' => 'CCTV',
        'q' => 'What CCTV systems do you install?',
        'a' => 'We design and install IP and HD CCTV for homes, multi-lets, retail, industrial and commercial sites, including NVR or DVR recording and remote viewing. Cameras may be Hikvision, Dahua, Axis or another brand that suits the site and is available. That list is equipment, not a manufacturer accreditation. Camera positions should respect privacy; we are not a data-protection certification body.',
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
        'a' => 'We install and maintain door access using equipment such as Paxton, Salto and HID, plus other platforms already on site, with fobs, cards or mobile credentials, time schedules and fire-release integration for flats, offices and multi-door sites. Naming those brands is not an approved-partner badge.',
        'link' => ['/pages/services/access-control.php', 'Access control services'],
    ],
    [
        'cat' => 'Access control & door entry',
        'q' => 'Do you install video door entry and intercoms for apartment blocks?',
        'a' => 'Yes. Audio and video door entry and multi-tenant intercoms are in the catalogue, including equipment such as Aiphone, Fermax and Videx where it suits the riser and is available. Flats, HMOs and commercial receptions are typical jobs. Fire door release and access control can be included when the quote says so. Brand names are not scheme memberships.',
        'link' => ['/pages/services/door-entry.php', 'Door entry services'],
    ],
    // Response times
    [
        'cat' => 'Response times & appointments',
        'q' => 'How quickly do you respond to enquiries and emergencies?',
        'a' => 'On business days we aim to reply to quote and contact messages within 2 hours during typical hours (Monday–Friday 08:00–18:00). That is a target, not a service-level agreement and not a 24-hour emergency contract. Same-week appointments depend on engineer capacity and access. Urgent faults on fire, life-safety and security systems are prioritised when someone is free. Call ' . PHONE . ' or WhatsApp the same mobile.',
    ],
    [
        'cat' => 'Response times & appointments',
        'q' => 'Do you offer planned maintenance contracts?',
        'a' => 'Yes, where we agree a contract. Landlords and facilities managers book planned visits for fire alarms, emergency lighting, nurse call, AOV and other systems we already look after, so the logbook matches the visits. Ask if you want more than one service on the same schedule. We do not sell a one-line national retainer or a badge that covers every trade.',
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
        'q' => 'Are quotes free, and are they always a fixed price?',
        'a' => 'Asking for a quote does not cost a fee. Where the scope is clear from what you send, we confirm a written price. Legionella, asbestos surveys and any job that depends on access or plant condition are priced after that scope is known — we do not invent a catalogue fee. A quote can change if the site differs from the description. Paperwork covers the work in the quote, not a scheme membership.',
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
        'q' => 'What can I buy in the iComply shop?',
        'a' => 'The trade shop lists kits, parts and products. Checkout is the Shopify store when it is connected. Prices and stock on a product page are the store’s, not figures we invent on this FAQ. If checkout is not connected, ask us and we will say so.',
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
        'a' => 'We install and maintain equipment from brands that include Apollo, Hochiki, Kentec, Advanced, C-TEC, Paxton, Salto, Hikvision, Dahua, Axis, Hager, Schneider and Worcester Bosch, among others in the manufacturer index. A brand page is a guide to that equipment. It is not an approved-installer accreditation, a manufacturer partnership, or proof we stock every part.',
        'link' => ['/pages/manufacturers/index.php', 'All manufacturers'],
    ],
    [
        'cat' => 'Manufacturers & other services',
        'q' => 'Do you also handle AOV, nurse call and intruder alarms?',
        'a' => 'Yes. The catalogue also includes AOV and smoke control, nurse call, intruder alarms and intercoms, plus fire risk assessments, extinguishers, fire doors, smoke and CO alarms, PAT testing, EPC, landlord support, kitchens, bathrooms and construction trades. Each service page states the work. Installation or maintenance is what the quote says. We issue records for the visit we complete; we do not add a scheme logo to make the list longer.',
        'link' => ['/pages/services/index.php', 'All services'],
    ],
    [
        'cat' => 'Manufacturers & other services',
        'q' => 'How do I get started?',
        'a' => 'Call ' . PHONE . ', WhatsApp the same mobile, email ' . EMAIL . ', or use the quote form on the contact page. Include your postcode and the service. We confirm scope before we talk about a date.',
        'link' => ['/contact.php', 'Contact iComply'],
    ],
    [
        'cat' => 'Fire risk assessments',
        'q' => 'Do you carry out fire risk assessments?',
        'a' => 'Yes. We survey the premises and write a fire risk assessment with an action list for landlords, shared houses, offices and other workplaces in the catalogue. An assessment is not a licence, not legal advice, and not a guarantee of what an enforcing authority will decide. Follow-on alarm, lighting or door work is a separate quote unless you asked for it in the same scope.',
        'link' => ['/pages/services/fire-risk-assessments.php', 'Fire risk assessments'],
    ],
    [
        'cat' => 'Construction & fit-out',
        'q' => 'Do you fit kitchens, bathrooms and other building work?',
        'a' => 'Yes. The construction catalogue includes kitchen and bathroom fitting, renovation, plastering, joinery, roofing, extensions, loft conversions and related trades. Each job is scoped and quoted. Building Regulations apply where the work needs them. We do not claim a construction-scheme membership, an NHBC registration, or a “fully certified builder” badge on this page.',
        'link' => ['/pages/services/kitchens.php', 'Kitchen fitting'],
    ],
    [
        'cat' => 'Water, asbestos & surveys',
        'q' => 'Do you do Legionella risk assessments and asbestos surveys?',
        'a' => 'Yes, as price-on-application visits. Legionella work follows HSE L8 and HSG274 as guidance: a written assessment of the water system, with sampling only if it helps. Asbestos surveys are management or refurbishment surveys under the Control of Asbestos Regulations 2012, scoped to the building and the planned works. We do not claim UKAS, BOHS or an HSE licence. Licensed asbestos removal is by others.',
        'link' => ['/pages/services/legionella-risk-assessment.php', 'Legionella risk assessment'],
    ],
    [
        'cat' => 'Accreditations',
        'q' => 'Are you NICEIC, BAFE or Gas Safe registered as a company?',
        'a' => 'This page does not claim NICEIC, BAFE, CHAS, SafeContractor, UKAS, a company Gas Safe registration number, or an EPC assessor accreditation number. Gas work is only booked when the engineer who attends is on the Gas Safe Register for that appliance — ask for the ID and check the register. An energy performance certificate has to be produced by an accredited assessor; we do not print that number here. Electrical, fire and emergency-lighting paperwork is for the visit we complete. If you need a named scheme member and we cannot show that registration, we will say so before you book. Call ' . PHONE . '.',
        'link' => ['/pages/about.php', 'About iComply'],
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
            'name' => 'Property Compliance FAQ — iComply Property Services',
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
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
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
                fire risk assessments, construction, quotes and coverage. Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>.
                We do not list scheme badges we cannot verify.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#faqs" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Browse FAQs</a>
                <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">Free quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20iComply%2C%20I%20have%20a%20question"
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
                'Fire risk assessments' => 'fire-risk-assessments',
                'Construction & fit-out' => 'construction-fit-out',
                'Water, asbestos & surveys' => 'water-asbestos-surveys',
                'Accreditations' => 'accreditations',
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
            'Fire risk assessments' => 'fire-risk-assessments',
            'Construction & fit-out' => 'construction-fit-out',
            'Water, asbestos & surveys' => 'water-asbestos-surveys',
            'Accreditations' => 'accreditations',
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
                <p class="mt-2 text-sm text-zinc-600">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>, WhatsApp or the form. Reply aim: 2 hours on business days.</p>
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
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Still have a question?</h2>
            <p class="mt-3 text-white/75">Tell us your postcode and the job. Written quotes from Stockport — call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>. Some visits are priced only after a survey.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/contact.php') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request a quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20iComply%2C%20I%20have%20a%20compliance%20question"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', PHONE), ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> BS 5839, BS 5266 and BS 7671 as job references — not scheme memberships</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Installation, servicing and records for the work agreed</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> <?= (int)$areaCount ?>+ towns on our North West list</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Reply aim: within 2 hours on business days. Phone <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></li>
        </ul>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
