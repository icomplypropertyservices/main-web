<?php
/**
 * Service hub template. Placeholders: SERVICE_NAME, SERVICE_SLUG, SEO_KEYWORDS,
 * MANUFACTURER_TAGS, MANUFACTURER_IMAGES
 */
$poaService = function_exists('isPoaService') && isPoaService($SERVICE_SLUG);
$ownsMainland = function_exists('serviceOwnsMainlandAreas') && serviceOwnsMainlandAreas($SERVICE_SLUG);
$metaRow = function_exists('getServiceMeta') ? getServiceMeta($SERVICE_SLUG) : [];
$mainlandJobPlural = $SERVICE_SLUG === 'fire-risk-assessments' ? 'fire risk assessments' : 'fire alarms';
$seoTitle = trim((string)($metaRow['seo_title'] ?? ''));
$seoDesc = trim((string)($metaRow['seo_desc'] ?? ''));
if ($seoTitle !== '') {
    $pageTitle = $seoTitle;
} elseif ($ownsMainland && $SERVICE_SLUG === 'fire-alarms') {
    $pageTitle = $SERVICE_NAME . ' | UK mainland fire protection | iComply';
} else {
    $pageTitle = $SERVICE_NAME . ' | iComply ' . ($ownsMainland ? 'UK mainland' : 'North West');
}
if ($seoDesc !== '') {
    $metaDesc = $seoDesc;
} elseif ($ownsMainland && $SERVICE_SLUG === 'fire-alarms') {
    $metaDesc = 'Fire alarm design, installation, servicing and certification across UK mainland — England, Wales and mainland Scotland. Scheduled from Stockport. Written quote after scope.';
} elseif ($poaService) {
    $metaDesc = $SERVICE_NAME . ' across the North West, priced on application after scope. Local team from Stockport. No invented fees.';
} else {
    $metaDesc = $SERVICE_NAME . ' across Greater Manchester and the North West. Written quotes after scope. Local team from Stockport.';
}
$metaKeywords = $SEO_KEYWORDS;
$hubHeroRel = function_exists('icomplyHubHero') ? icomplyHubHero($SERVICE_SLUG) : null;
$hubInline1 = function_exists('icomplyHubInline') ? icomplyHubInline($SERVICE_SLUG, 1) : null;
$hubInline2 = function_exists('icomplyHubInline') ? icomplyHubInline($SERVICE_SLUG, 2) : null;
$ogImage = url($hubHeroRel ?: ('/assets/images/services/' . $SERVICE_SLUG . '.jpg'));

$allServices = getServices();
$allAreas = $ownsMainland && function_exists('getMainlandAreas') ? getMainlandAreas() : getAreas();
$coverageLabel = $ownsMainland ? 'UK mainland' : 'North West';
$areaServedNames = $ownsMainland
    ? ['England', 'Wales', 'Mainland Scotland']
    : ['Greater Manchester', 'Lancashire', 'Cheshire', 'Merseyside', 'Cumbria', 'North West England'];
$serviceSlug = $SERVICE_SLUG;
$serviceName = $SERVICE_NAME;

$serviceFaqs = [
    'gas-systems' => [
        ['Who carries out the gas check?', 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.'],
        ['Is iComply Gas Safe registered?', 'No. iComply is not Gas Safe registered. This page does not show a registration number or a Gas Safe badge.'],
        ['What does the quote need?', 'Postcode and appliance count. The price is on application. There is no per-appliance fee list on this page.'],
    ],
    'heating' => [
        ['Do you install boilers?', 'Boiler installation, servicing and landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.'],
        ['What heating work is quoted here?', 'Radiators, controls and system flushes, after we see the layout. Price on application. No published boiler price.'],
    ],
    'electrical' => [
        ['How often is an EICR required?', 'Landlords typically need an EICR every 5 years (or on change of tenancy). Commercial premises often follow a risk-based schedule of 1–5 years.'],
        ['Do you offer same-week electrical appointments?', 'Where engineer capacity and site access allow, yes — especially for landlord certificates and urgent remedial work across the North West.'],
        ['Can you upgrade consumer units and install EV chargers?', 'Yes. We design and install consumer unit upgrades, rewires, EV chargers and commercial electrical works to current regulations with full certification.'],
    ],
    'fire-alarms' => [
        ['What is BS 5839 compliance?', 'BS 5839 is the British Standard covering design, installation, commissioning and maintenance of fire detection and alarm systems in buildings.'],
        ['How often do fire alarms need servicing?', 'Most systems need servicing at least twice a year, with weekly user tests and full documentation for insurers and fire officers.'],
        ['Do you support existing panels (Kentec, Advanced, C-Tec)?', 'Yes. We install, service, reprogram and upgrade major brands including Kentec, Advanced, C-Tec, Morley, Hochiki and Apollo.'],
        ['Do you cover fire alarms outside the North West?', 'Yes. Fire alarms are scheduled across England, Wales and mainland Scotland. Northern Ireland, the Scottish Highlands and Islands, the Isle of Man and the Channel Islands are not on this list.'],
    ],
    'emergency-lighting' => [
        ['How often should emergency lighting be tested?', 'Monthly functional tests and annual full-duration tests are required under BS 5266, with records kept for compliance.'],
        ['Can you convert fluorescent emergency fittings to LED?', 'Yes. We supply and fit LED conversions and full system upgrades while maintaining correct coverage and certification.'],
        ['Do you issue emergency lighting certificates?', 'Every planned test and install includes documentation suitable for landlords, facilities managers and insurers.'],
    ],
    'legionella-risk-assessment' => [
        ['What is a Legionella risk assessment?', 'A written look at how the water system could allow Legionella to grow, and what controls are proportionate. UK dutyholders use HSE L8 and HSG274 as the usual reference.'],
        ['Do you always take water samples?', 'No. Assessment comes first. Sampling is only recommended when the system and occupancy justify it, and it is quoted POA.'],
        ['What does it cost?', 'Price on application. We do not publish a made-up fee. Tell us property type, stored water and access.'],
    ],
    'asbestos-survey' => [
        ['What survey do I need?', 'A management survey is for normal occupation. A refurbishment or demolition survey is for intrusive works. We scope the type to the building and the planned job.'],
        ['Do you remove asbestos?', 'Licensed removal is not this service. If the survey says removal is required, that work is appointed separately.'],
        ['What does it cost?', 'Price on application. Size, access and how intrusive the survey must be all change the quote. No invented starting price.'],
    ],
    'access-control' => [
        ['Why are car park barriers the hardest access job?', 'A rising arm needs the boom, induction loops, safety devices and the credential that opens the lane. A door maglock does not cover that work. Manchester and Stockport have their own barrier pages.'],
        ['Do you install maglocks as well as barriers?', 'Yes. Maglocks, strikes and fire release are scoped for the pedestrian door. The barrier lane is quoted separately, POA after survey.'],
        ['How do you price access control?', 'Price on application after we confirm doors, any barrier lane, brand and access. No catalogue price on this page.'],
    ],
    'door-entry' => [
        ['Can door entry open a car park barrier?', 'Sometimes, when the panel has a clean release into the barrier controller. Barriers stay the hardest job and are surveyed on their own, including for Manchester and Stockport.'],
        ['Do you replace flat handsets and panels?', 'Yes. We survey the panel, cabling and the lock it releases, then quote POA. Maglocks on that door are included in the survey when they are part of the release.'],
        ['How do you price door entry?', 'Price on application after panel condition, handset count and cabling are known. No catalogue price on this page.'],
    ],
    'default' => [
        ['What areas do you cover for ' . $SERVICE_NAME . '?', 'We cover the Greater Manchester towns on the published areas list from our Stockport base.'],
        ['How do you price the work?', $poaService ? 'Price on application after we confirm scope, standards and access. No catalogue prices on this page.' : 'After we confirm scope, standards and access we issue a written quote. We do not invent a fee here.'],
        ['Can you maintain systems already on site?', 'Where the service is about existing systems, we inspect, service and document. For survey-led work we record what is there and what should happen next.'],
    ],
];

$blurb = getServiceBlurb($serviceSlug);
$standards = getServiceStandards($serviceSlug);
$faqs = $serviceFaqs[$serviceSlug] ?? $serviceFaqs['default'];
$svcCopy = function_exists('icomplyServiceHubCopy') ? icomplyServiceHubCopy($serviceSlug) : null;
if (!empty($svcCopy['faq']) && is_array($svcCopy['faq'])) {
    $faqs = $svcCopy['faq'];
}
$hubLinks = function_exists('icomplyServiceHubLinks') ? icomplyServiceHubLinks($serviceSlug) : [];

$nwAreas = function_exists('icomplyLocalTownNames') ? icomplyLocalTownNames() : getAreas();
$keywordTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Bury', 'Sale', 'Altrincham'],
    function ($t) use ($nwAreas) {
        return in_array($t, $nwAreas, true);
    }
));
$popularTowns = $keywordTowns;
if ($ownsMainland) {
    $popularTowns = array_values(array_filter(
        ['London', 'Birmingham', 'Leeds', 'Bristol', 'Cardiff', 'Edinburgh', 'Glasgow', 'Manchester', 'Stockport', 'Newcastle upon Tyne', 'Southampton', 'Nottingham'],
        function ($t) use ($allAreas) {
            return in_array($t, $allAreas, true);
        }
    ));
}
$popularTowns = array_values(array_unique($popularTowns));

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
            'alternateName' => $serviceSlug === 'gas-systems'
                ? 'Landlord gas safety records, carried out by Gas Safe registered engineers'
                : ($serviceName . ' installation, maintenance and certification'),
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
            }, $areaServedNames),
            'offers' => [
                '@type' => 'Offer',
                'name' => ($poaService ? 'Price on application — ' : 'Written quote — ') . $serviceName,
                'description' => $poaService
                    ? ('Request a scoped POA quote for ' . $serviceName . '. No published fee list.')
                    : ('Request a written quote for ' . $serviceName . ' installation, servicing and certification.'),
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
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
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
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($coverageLabel, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?>.<br>
                    <span class="text-[#ff6b00]"><?= htmlspecialchars($svcCopy['hero_accent'] ?? ($poaService ? 'Surveyed, documented, POA.' : 'Installed, tested, certified.'), ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($serviceSlug === 'fire-risk-assessments' && !empty($metaRow['guide_price_label'])): ?>
                <p class="mt-4 text-sm text-white/80 max-w-xl">Guide price <?= htmlspecialchars((string)$metaRow['guide_price_label'], ENT_QUOTES, 'UTF-8') ?> for a standard fire risk assessment on UK mainland. Larger or higher-risk premises are confirmed in writing.</p>
                <?php endif; ?>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white"><?= $poaService ? 'Request POA quote' : 'Get free quote' ?></a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Quote for ' . $serviceName) ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                    <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <p class="mt-6 text-sm text-white/60"><?= htmlspecialchars($standards, ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($serviceSlug === 'access-control'): ?>
                    <p class="mt-4 text-sm"><a class="font-semibold text-[#ff6b00] hover:underline" href="<?= url('/pages/access-control-systems') ?>">Access control systems across the UK, with city notes and manufacturer links</a></p>
                <?php endif; ?>
            </div>
            <div class="relative rounded-3xl overflow-hidden border border-white/10 min-h-[260px] bg-white/5">
                <img src="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> by iComply Property Services"
                     class="absolute inset-0 w-full h-full object-cover opacity-70"
                     loading="eager"
                     onerror="this.style.display='none'">
                <div class="relative p-6 md:p-8 flex flex-col justify-end min-h-[260px] bg-gradient-to-t from-[#0B1F3A]/90 via-[#0B1F3A]/20 to-transparent">
                    <div class="text-sm text-white/70">Serving <?= count($allAreas) ?>+ towns</div>
                    <div class="text-2xl font-semibold mt-1"><?= $poaService ? 'Local team · Price on application' : 'Local engineers · Written quotes' ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-[#061828]">How a <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> visit runs</h2>
        <p class="mt-3 text-zinc-800 max-w-3xl">Work is arranged from Stockport. The quote for <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> is price on application. The visit is carried out by people qualified for that trade.</p>
        <ol class="mt-6 grid md:grid-cols-2 gap-4 text-zinc-800">
            <li class="rounded-2xl border border-zinc-200 p-4"><strong>1. The building.</strong> Send the address, what <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> has to cover, and whether the site is occupied.</li>
            <li class="rounded-2xl border border-zinc-200 p-4"><strong>2. The scope.</strong> iComply confirms the visit in writing. This page does not publish a price for that visit.</li>
            <li class="rounded-2xl border border-zinc-200 p-4"><strong>3. The attendance.</strong> Access and occupation in the town decide the appointment. Travel is part of the POA quote.</li>
            <li class="rounded-2xl border border-zinc-200 p-4"><strong>4. The record.</strong> Certificates, test sheets or reports go to the instructing client.</li>
        </ol>
        <?php if ($serviceSlug === 'gas-systems' && function_exists('icomplyGasLegalSentence')): ?>
            <p class="mt-4 text-zinc-800 max-w-3xl"><?= htmlspecialchars(icomplyGasLegalSentence(), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <p class="mt-4 text-sm"><a class="font-semibold text-[#ff6b00]" href="<?= url('/contact') ?>">Request a <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> quote</a> · <a class="font-semibold text-[#ff6b00]" href="<?= url('/pages/areas') ?>">Towns covered from Stockport</a></p>
    </div>
</section>

<?php if ($serviceSlug === 'nurse-call'): ?>
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-10">
        <h2 class="text-2xl font-semibold tracking-tight text-black">Care home, ward, or warden scheme</h2>
        <p class="mt-3 text-zinc-700 max-w-3xl leading-relaxed">A bedroom pear lead, a ward staff station and a sheltered-scheme speech unit are three quotes. The specification is on the nurse call hub. Manchester and Stockport have their own pages. Fire and lighting for the same home are on the care homes page. Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>.</p>
        <div class="mt-5 flex flex-wrap gap-2">
            <a href="<?= url('/pages/nurse-call-systems') ?>" class="px-4 py-2 rounded-full bg-[#0B1F3A] text-white text-sm font-semibold hover:bg-[#ff6b00]">Nurse call hub</a>
            <a href="<?= url('/pages/nurse-call-manchester') ?>" class="px-4 py-2 rounded-full border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00]">Manchester</a>
            <a href="<?= url('/pages/nurse-call/stockport') ?>" class="px-4 py-2 rounded-full border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00]">Stockport</a>
            <a href="<?= url('/pages/care-homes') ?>" class="px-4 py-2 rounded-full border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00]">Care homes</a>
            <a href="<?= url('/pages/keywords/warden-call') ?>" class="px-4 py-2 rounded-full border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00]">Warden call</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($serviceSlug === 'fire-alarms' && function_exists('fireAlarmsLaneCounts')):
    $fireLaneCounts = fireAlarmsLaneCounts();
?>
<section class="bg-[#0B1F3A] text-white border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Job lane</div>
            <h2 class="mt-1 text-2xl font-semibold">Install, maintain and service</h2>
            <p class="mt-1 text-sm text-white/75">
                <?= (int)$fireLaneCounts['install'] ?> install guides,
                <?= (int)$fireLaneCounts['maintain'] ?> maintenance guides,
                <?= (int)$fireLaneCounts['service'] ?> service guides.
                Price on application.
            </p>
        </div>
        <a href="<?= url('/pages/jobs/fire-alarms.php') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white text-center">Open the fire alarm job lane</a>
    </div>
</section>
<?php endif; ?>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local response', $ownsMainland
                ? ('Stockport base — ' . $mainlandJobPlural . ' scheduled across UK mainland')
                : 'Stockport-based engineers across Greater Manchester & the North West'],
            ['Standards-led', $standards],
            ['Full documentation', 'Records for landlords, insurers, agents and dutyholders'],
            [$poaService ? 'POA quotes' : 'Written quotes', $poaService ? 'No invented prices — scoped after we see the job' : 'Clear scope before work starts'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0B1F3A]/10 flex items-center justify-center text-[#0B1F3A] font-bold shrink-0">✓</div>
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
                Expert <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> across <?= $ownsMainland ? 'UK mainland' : 'the North West' ?>
            </h2>
            <?php if (!empty($svcCopy['intro']) && is_array($svcCopy['intro'])): ?>
                <?php foreach ($svcCopy['intro'] as $para): ?>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed"><?= htmlspecialchars((string)$para, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">
                iComply Property Services designs, installs, commissions, maintains and certifies
                <strong><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if ($ownsMainland): ?>
                for commercial, industrial, multi-let, care and residential properties across England, Wales and mainland Scotland.
                <?php else: ?>
                for commercial, industrial, multi-let, care and residential properties across Greater Manchester,
                Lancashire, Cheshire, Merseyside and Cumbria.
                <?php endif; ?>
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                <?php if ($ownsMainland): ?>
                <?= $serviceSlug === 'fire-risk-assessments'
                    ? 'These assessments are listed for UK mainland. Each place below has its own fire risk assessment page.'
                    : 'Fire protection on these pages is UK mainland. Each place below has its own fire alarm page.' ?>
                Northern Ireland, the Scottish Highlands and Islands, the Isle of Man and the Channel Islands are not listed.
                Attendance outside the North West is scheduled from our Stockport (SK2) base and confirmed on the quote.
                <?php else: ?>
                From new system design to reactive call-outs and planned maintenance contracts, our engineers deliver
                a written quote that is price on application, with a clear scope and full compliance documentation. Based in Stockport (SK2), we cover
                Manchester, Stockport, Bolton, Salford, Oldham, Rochdale, Wigan, Bury, Trafford and Tameside.
                <?php endif; ?>
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Searching for a specific manufacturer? We install and service the major brands listed below so you can
                find local support for the exact panel or equipment already on site.
            </p>
            <?php endif; ?>
        </div>
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-3xl overflow-hidden border bg-zinc-100">
                <img src="<?= htmlspecialchars(url($hubInline1 ?: ('/assets/images/keywords/' . $img2 . '.jpg')), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> equipment"
                     class="w-full h-44 object-cover"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
            </div>
            <div class="rounded-3xl overflow-hidden border bg-zinc-100">
                <img src="<?= htmlspecialchars(url($hubInline2 ?: ('/assets/images/keywords/' . $img3 . '.jpg')), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> installation work"
                     class="w-full h-44 object-cover"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
            </div>
        </div>
    </div>

    <!-- Pillars -->
    <div class="mt-14 grid md:grid-cols-3 gap-6">
        <?php
        $pillars = !empty($svcCopy['pillars']) && is_array($svcCopy['pillars']) ? $svcCopy['pillars'] : [
            ['title' => 'Installation & design', 'text' => 'Full design, supply and install of new ' . $serviceName . ' systems to current British Standards and manufacturer guidance.'],
            ['title' => 'Maintenance & servicing', 'text' => 'Planned contracts, reactive repairs, battery replacements and panel upgrades for systems already on site.'],
            ['title' => 'Testing & certification', 'text' => 'Statutory tests with logbooks, certificates and documentation ready for audits, insurers and landlords.'],
        ];
        $pi = 1;
        foreach ($pillars as $pillar):
        ?>
        <div class="p-8 bg-white rounded-3xl border hover:border-[#ff6b00] transition">
            <div class="w-10 h-10 rounded-2xl bg-[#0B1F3A] text-white flex items-center justify-center font-bold mb-4"><?= $pi++ ?></div>
            <h3 class="font-semibold text-xl text-black mb-2"><?= htmlspecialchars((string)$pillar['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-sm text-zinc-600"><?= htmlspecialchars((string)$pillar['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($svcCopy['sections']) && is_array($svcCopy['sections'])): ?>
    <div class="mt-14 space-y-10 max-w-3xl">
        <?php foreach ($svcCopy['sections'] as $sec): ?>
        <div>
            <h3 class="text-2xl font-semibold tracking-tight text-black mb-3"><?= htmlspecialchars((string)($sec['h2'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
            <?php foreach (($sec['p'] ?? []) as $para): ?>
            <p class="mt-3 text-zinc-700 leading-relaxed"><?= htmlspecialchars((string)$para, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php
require_once SITE_ROOT . '/includes/access-control-jobs.php';
if (function_exists('accessControlLaneHubSection')) {
    echo accessControlLaneHubSection($serviceSlug);
}
?>

<?php if (!$poaService): ?>
<!-- MANUFACTURERS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Manufacturers</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Brands we install &amp; service</h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">Looking for your exact panel brand? We support major <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> manufacturers across the North West.</p>
            </div>
            <a href="<?= htmlspecialchars(function_exists('icomplyTradeShopUrl') ? icomplyTradeShopUrl() : url('/shop/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm font-semibold text-[#ff6b00]">Browse trade shop →</a>
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
<?php endif; ?>

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
                    <?php if ($ownsMainland): ?>
                    Topic guides stay on the North West town set. The area list further down is every UK mainland <?= htmlspecialchars($mainlandJobPlural, ENT_QUOTES, 'UTF-8') ?> page.
                    <?php else: ?>
                    Every guide below has a dedicated page for each town we cover
                    (e.g. <strong>EICR report in Stockport</strong>). Click a topic, then pick your area.
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All keyword guides →</a>
        </div>
        <?php
        $svcKeywords = getKeywordsForService($serviceSlug);
        if (function_exists('accessControlLaneSortKeywordMap') && in_array($serviceSlug, ['access-control', 'door-entry'], true)) {
            $svcKeywords = accessControlLaneSortKeywordMap($svcKeywords);
        }
        if ($svcKeywords):
            $kwPreviewTowns = array_slice($keywordTowns, 0, 6);
        ?>
        <div class="mb-8">
            <?= relatedKeywordsHtml($serviceSlug, 0) ?>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $shown = 0;
            $cardLimit = in_array($serviceSlug, getElectricalGasFamilyServices(), true) ? 0 : 18;
            if ($serviceSlug === 'access-control') {
                $cardLimit = 24;
            }
            foreach ($svcKeywords as $kwSlug => $kwMeta):
                if ($cardLimit > 0 && $shown >= $cardLimit) {
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
                    <?php foreach ($keywordTowns as $town): ?>
                        <a href="<?= url('/pages/keywords/' . rawurlencode($kwSlug) . '/' . areaSlug($town) . '.php') ?>"
                           class="text-[11px] px-2 py-1 bg-white border rounded-full text-zinc-700 hover:border-[#ff6b00] hover:text-[#ff6b00]">
                            <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('/pages/keywords/' . rawurlencode($kwSlug) . '.php') ?>"
                       class="text-[11px] px-2 py-1 font-semibold text-[#ff6b00]">
                        All <?= count($nwAreas) ?> towns →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($cardLimit > 0 && count($svcKeywords) > $cardLimit): ?>
            <p class="mt-6 text-sm text-zinc-600">
                Showing <?= (int)$cardLimit ?> of <?= count($svcKeywords) ?> <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> keyword guides —
                <a href="<?= url('/pages/keywords/index.php') ?>" class="font-semibold text-[#ff6b00]">view full keyword index</a>.
            </p>
        <?php else: ?>
            <p class="mt-6 text-sm text-zinc-600">
                All <?= count($svcKeywords) ?> <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> keyword guides, each with every town we cover.
            </p>
        <?php endif; ?>
        <?php else: ?>
            <p class="text-zinc-600">Keyword guides for this service are being expanded. See the <a class="text-[#ff6b00] font-semibold" href="<?= url('/pages/keywords/index.php') ?>">full guides index</a>.</p>
        <?php endif; ?>
    </div>
</section>

<!-- AREAS -->
<?php
$gmTowns = function_exists('icomplyGreaterManchesterTownNames') ? icomplyGreaterManchesterTownNames() : [];
$gmBoroughs = function_exists('icomplyGreaterManchesterBoroughs') ? icomplyGreaterManchesterBoroughs() : [];
?>
<section class="max-w-7xl mx-auto px-6 py-16" id="greater-manchester">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Greater Manchester</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> in Greater Manchester
            </h2>
            <p class="mt-2 text-zinc-600 max-w-3xl"><?= htmlspecialchars((string)($svcCopy['areas_note'] ?? 'The ten boroughs, then the Greater Manchester towns already published on the site. Each link is this service in that place. Quotes are price on application.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All areas →</a>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($gmBoroughs as $a): ?>
            <a href="<?= htmlspecialchars(exportedServiceLocalUrl($SERVICE_SLUG, $a, 'service'), ENT_QUOTES, 'UTF-8') ?>"
               class="px-5 py-2.5 bg-white border rounded-full text-sm font-medium text-black hover:border-[#ff6b00] hover:shadow-sm transition">
                <?= htmlspecialchars($serviceName . ' in ' . $a, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="mt-6 flex flex-wrap gap-2">
        <?php foreach ($gmTowns as $a):
            if (in_array($a, $gmBoroughs, true)) continue;
        ?>
            <a href="<?= htmlspecialchars(exportedServiceLocalUrl($SERVICE_SLUG, $a, 'service'), ENT_QUOTES, 'UTF-8') ?>"
               class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs text-zinc-700 hover:border-[#ff6b00]">
                <?= htmlspecialchars($serviceName . ' in ' . $a, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php if ($ownsMainland): ?>
    <h3 class="mt-10 text-lg font-semibold text-black">UK mainland places</h3>
    <p class="mt-2 text-zinc-600">Every UK mainland place we list for <?= htmlspecialchars($mainlandJobPlural, ENT_QUOTES, 'UTF-8') ?>. Each link is that place’s page.</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <?php foreach ($popularTowns as $a): ?>
            <a href="<?= htmlspecialchars(exportedServiceLocalUrl($SERVICE_SLUG, $a, 'service'), ENT_QUOTES, 'UTF-8') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">
                <?= htmlspecialchars($serviceName . ' in ' . $a, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <h3 class="mt-10 text-lg font-semibold text-black">Other published towns</h3>
    <div class="mt-4 flex flex-wrap gap-2">
        <?php foreach ($allAreas as $a):
            if (in_array($a, $gmTowns, true)) continue;
        ?>
            <a href="<?= htmlspecialchars(exportedServiceLocalUrl($SERVICE_SLUG, $a, 'service'), ENT_QUOTES, 'UTF-8') ?>"
               class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs text-zinc-700 hover:border-[#ff6b00]">
                <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <p class="mt-6 text-sm"><a href="<?= url('/pages/areas/index.php') ?>" class="font-semibold text-[#ff6b00]">All <?= count($gmTowns) ?> Greater Manchester areas →</a></p>
</section>

<?php if ($serviceSlug === 'nurse-call'): ?>
<section id="nurse-call-uk" class="max-w-7xl mx-auto px-6 pb-16 scroll-mt-24">
    <div class="mb-8">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nurse call</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Nurse call in Greater Manchester</h2>
        <p class="mt-2 text-zinc-600 max-w-3xl">
            Town pages are the Greater Manchester list. iComply is based in Stockport SK2.
            A visit outside that list is confirmed after you send the postcode. Quotes are POA after scope.
            Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>.
        </p>
    </div>
</section>
<?php endif; ?>

<?php if ($hubLinks): ?>
<section class="bg-zinc-50 border-t">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-2xl font-semibold tracking-tight text-black">Also see</h2>
        <ul class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($hubLinks as $hubLink):
                $hubHref = (string)($hubLink[0] ?? '');
                $hubLabel = (string)($hubLink[1] ?? '');
                if ($hubHref === '' || $hubLabel === '') {
                    continue;
                }
            ?>
            <li>
                <a href="<?= htmlspecialchars(url($hubHref), ENT_QUOTES, 'UTF-8') ?>" class="inline-block px-4 py-2 rounded-full bg-white border text-sm font-semibold text-black hover:border-[#ff6b00]"><?= htmlspecialchars($hubLabel, ENT_QUOTES, 'UTF-8') ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

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
                    <img src="<?= htmlspecialchars(serviceImageUrl($slug), ENT_QUOTES, 'UTF-8') ?>"
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
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Need <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?>?</h2>
            <p class="mt-3 text-white/75"><?= $poaService
                ? 'Price on application after we confirm the property and access. Call, WhatsApp or send the form — no invented fee list.'
                : 'Written quotes after scope. Same-week appointments where capacity allows. Full certification on every job.' ?></p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Quote for ' . $serviceName) ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp quote</a>
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= url('/contact.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Book / contact</a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Based in Stockport — <?= $ownsMainland ? ('UK mainland ' . htmlspecialchars($mainlandJobPlural, ENT_QUOTES, 'UTF-8')) : 'North West coverage' ?></li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> <?= $poaService ? 'Written assessment or survey notes for your file' : 'Installation, servicing and certification' ?></li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> <?= $poaService ? 'POA only — no invented prices, certs or reviews' : 'Multi-service packages for landlords & FM teams' ?></li>
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
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= $poaService ? 'POA quote' : 'Free quote' ?></div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Request <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> quote
            </h2>
            <p class="mt-3 text-zinc-600"><?= htmlspecialchars((string)($svcCopy['cta_line'] ?? 'Tell us the postcode, property type and any panel brand — we aim to respond within 2 hours on business days.'), ENT_QUOTES, 'UTF-8') ?></p>
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
                      placeholder="<?= htmlspecialchars((string)($svcCopy['quote_placeholder'] ?? 'Postcode, property type, panel brand / system details…'), ENT_QUOTES, 'UTF-8') ?>"
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
