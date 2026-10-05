<?php
/**
 * Area hub template. Placeholders: AREA, AREA_SLUG, AREA_URL
 */
require_once SITE_ROOT . '/includes/seo.php';
$areaProfile = area_profile($AREA);
$seoFamily = 'area-hub';
$pageTitle = $AREA . ' property compliance';
$metaDesc = seo_fit_meta($AREA . ' (' . $areaProfile['districts'] . ') property compliance. ' . $areaProfile['stock'] . '. Fire, AOV, nurse call and barriers; other trades where the town is on the local list.');
$metaKeywords = $AREA . ' electrician, ' . $AREA . ' fire alarm installation, ' . $AREA . ' EICR, ' . $AREA . ' gas safety certificate, property compliance ' . $AREA . ', emergency lighting ' . $AREA;
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$areaHero = null;
if (function_exists('hubPageVisuals')) {
    $areaVisuals = hubPageVisuals('areas', $AREA, 1, 0, 'Property compliance in ' . $AREA);
    if (is_array($areaVisuals['primary'] ?? null) && !empty($areaVisuals['primary']['src'])) {
        $areaHero = $areaVisuals['primary'];
        $ogImage = (string)$areaHero['src'];
    }
}

$allServices = getServices();
$allAreas = getAreas();
$areaName = $AREA;
$areaSlugVal = $AREA_SLUG;

if (!function_exists('icomplyGmAdjacentTownNames')) {
    require_once SITE_ROOT . '/includes/building-hub-copy.php';
}
$nearby = icomplyGmAdjacentTownNames($areaName, 12);
if (!$nearby) {
    $idx = array_search($areaName, $allAreas, true);
    if ($idx === false) {
        $nearby = array_slice($allAreas, 0, 12);
    } else {
        $start = max(0, $idx - 6);
        $nearby = array_slice($allAreas, $start, 14);
        $nearby = array_values(array_filter($nearby, function ($a) use ($areaName) {
            return $a !== $areaName;
        }));
        $nearby = array_slice($nearby, 0, 12);
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
$canonicalUrl = url('/pages/areas/' . $areaSlugVal . '.php');
$metaRobots = function_exists('icomplyRobotsMetaForPath')
    ? icomplyRobotsMetaForPath('/pages/areas/' . $areaSlugVal)
    : 'noindex, follow';
require SITE_ROOT . '/includes/header.php';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'LocalBusiness',
            'name' => SITE_NAME . ' — ' . $areaName,
            'description' => $metaDesc,
            'url' => url('/pages/areas/' . $areaSlugVal . '.php'),
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
                '@type' => 'City',
                'name' => $areaName,
            ],
            'priceRange' => 'POA',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Areas', 'item' => url('/pages/areas/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $areaName, 'item' => url('/pages/areas/' . $areaSlugVal . '.php')],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Compliance services in ' . $areaName,
            'itemListElement' => (function () use ($allServices, $areaSlugVal, $areaName) {
                $items = [];
                $i = 0;
                foreach ($allServices as $slug => $name) {
                    $items[] = [
                        '@type' => 'ListItem',
                        'position' => ++$i,
                        'name' => $name . ' in ' . $areaName,
                        'url' => exportedServiceLocalUrl($slug, $areaName, 'area'),
                    ];
                }
                return $items;
            })(),
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
            <a href="<?= url('/pages/areas/index.php') ?>" class="hover:text-white">Areas</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Local engineers · <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Property compliance in<br>
                    <span class="text-[#ff6b00]"><?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl">
                    Electrical, fire alarms, emergency lighting, CCTV and access control for properties in <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> and nearby postcodes.
                    Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get free quote</a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20<?= htmlspecialchars($AREA_URL, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                    <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($allServices) ?></span> core services</div>
                    <div><span class="text-white font-semibold text-xl block">Diary</span> appointments*</div>
                    <div><span class="text-white font-semibold text-xl block">Fixed-price</span> quotes</div>
                </div>
                <p class="mt-3 text-[11px] text-white/40">*Subject to engineer capacity and site access.</p>
            </div>
            <div class="space-y-3">
                <?php if (is_array($areaHero)): ?>
                <figure class="relative rounded-3xl overflow-hidden border border-white/10 min-h-[180px] bg-white/5">
                    <img src="<?= htmlspecialchars((string)$areaHero['src'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars((string)($areaHero['alt'] ?? ('Property compliance in ' . $AREA)), ENT_QUOTES, 'UTF-8') ?>"
                         class="w-full h-48 <?= (($areaHero['fit'] ?? 'cover') === 'contain') ? 'object-contain bg-white' : 'object-cover' ?>"
                         width="960" height="540"
                         loading="eager">
                </figure>
                <?php endif; ?>
            <div class="grid grid-cols-2 gap-3">
                <?php
                $heroCards = array_slice($allServices, 0, 4, true);
                foreach ($heroCards as $slug => $name):
                ?>
                <a href="<?= htmlspecialchars(exportedServiceLocalUrl($slug, $AREA, 'area'), ENT_QUOTES, 'UTF-8') ?>"
                   class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[130px] bg-white/5 hover:border-[#ff6b00] transition">
                    <img src="<?= htmlspecialchars(function_exists('serviceImageUrl') ? serviceImageUrl($slug) : url('/assets/images/services/' . $slug . '.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(($services[$slug] ?? $slug) . ' in ' . ($areaName ?? $AREA ?? 'the North West'), ENT_QUOTES, 'UTF-8') ?>"
                         width="640" height="360"
                         class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition"
                         loading="<?= $slug === array_key_first($heroCards) ? 'eager' : 'lazy' ?>" onerror="this.style.display='none'">
                    <div class="relative p-4 h-full flex flex-col justify-end min-h-[130px]">
                        <div class="font-semibold text-white leading-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs text-white/70 mt-1">in <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> →</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Fire risk assessment</div>
            <h2 class="text-2xl font-semibold tracking-tight text-black mt-1">
                FRA in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(function_exists('fraGuidePrice') ? fraGuidePrice() : '£350', ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Suitable and sufficient fire risk assessment for this town. Guide price
                <?= htmlspecialchars(function_exists('fraGuidePrice') ? fraGuidePrice() : '£350', ENT_QUOTES, 'UTF-8') ?>
                for a standard FRA. UK mainland coverage. Larger premises are confirmed in writing.
            </p>
        </div>
        <a href="<?= htmlspecialchars(url('/pages/fire-risk-assessments/' . $areaSlugVal . '.php'), ENT_QUOTES, 'UTF-8') ?>"
           class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white shrink-0">
            FRA in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> →
        </a>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local to ' . $AREA, 'Engineers covering ' . $AREA . ' and surrounding postcodes from Stockport'],
            ['Standards-led', 'BS 5839, BS 5266, BS 7671. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers'],
            ['Full paperwork', 'Certificates and logbooks for landlords, insurers & FM'],
            ['One team', 'Multi-service packages in a single visit schedule'],
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

<!-- INTRO -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">About our <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> cover</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Compliance engineers for <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">
                Property compliance for <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> uses outward codes
                <strong><?= htmlspecialchars($areaProfile['districts'], ENT_QUOTES, 'UTF-8') ?></strong>
                in <?= htmlspecialchars($areaProfile['region'], ENT_QUOTES, 'UTF-8') ?>.
                The buildings we usually see are <?= htmlspecialchars($areaProfile['stock'], ENT_QUOTES, 'UTF-8') ?>.
                Local priority on this page: <?= htmlspecialchars($areaProfile['focus'], ENT_QUOTES, 'UTF-8') ?>.
                Fire alarms, AOV and smoke control, nurse call, and vehicle barriers are covered here.
                Other trades are indexed when the town is in Greater Manchester.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                The office is 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. Visits in
                <?= htmlspecialchars($areaProfile['districts'], ENT_QUOTES, 'UTF-8') ?> are diary-booked.
                Phone <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>.
                We do not print a NICEIC or Gas Safe registration number on this town page.
            </p>
        </div>
        <div class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight"><?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> compliance package</h3>
            <p class="mt-3 text-white/80">Combine EICR, fire alarms and emergency lighting into one visit schedule for landlords and FM teams in <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?>. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
            <ul class="mt-6 space-y-3 text-sm text-white/90">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Fixed-price multi-service quotes</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Full documentation for audits &amp; insurers</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Maintenance contracts available</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> WhatsApp, phone or form — send the postcode to start a quote</li>
            </ul>
            <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold hover:bg-orange-600">Start your quote</a>
        </div>
    </div>
</section>

<!-- POPULAR KEYWORD × THIS AREA (EICR report, FRA, gas cert, etc.) -->
<section class="related-links max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local keyword pages</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Guides for <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                High-intent topics with a dedicated page for <strong><?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?></strong>
                — EICR report, fire risk assessment, and more. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.
            </p>
        </div>
        <a href="<?= url('/pages/keywords/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All guides →</a>
    </div>
    <?php
    require_once SITE_ROOT . '/includes/related.php';
    echo keywordAreaLinksHtml($AREA, null, 0);
    ?>
</section>

<!-- ALL SERVICES -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Services in <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?></div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Everything we do locally</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Every service has a page for <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?>. Quotes are POA. Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All service hubs →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($allServices as $slug => $name):
                $blurb = getServiceBlurb($slug, true);
            ?>
            <a href="<?= htmlspecialchars(exportedServiceLocalUrl($slug, $AREA, 'area'), ENT_QUOTES, 'UTF-8') ?>"
               class="group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-36 bg-zinc-100 overflow-hidden">
                    <img src="<?= htmlspecialchars(serviceImageUrl($slug), ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?>"
                         width="640" height="360"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-4 text-sm font-semibold text-[#ff6b00]">View service →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How it works in <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="grid md:grid-cols-3 gap-8">
        <?php
        $steps = [
            ['1', 'Tell us the job', 'Service, postcode in or near ' . $AREA . ', panel brand or system type — form, phone or WhatsApp.'],
            ['2', 'Get a fixed quote', 'We confirm scope, standards and timeline for your ' . $AREA . ' site. Clear price, no jargon.'],
            ['3', 'We deliver & certify', 'Engineers attend, complete the work and issue documentation for compliance.'],
        ];
        foreach ($steps as [$n, $t, $d]): ?>
        <div class="text-center px-4">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0B1F3A] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
            <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- NEARBY -->
<?php if (!empty($nearby)): ?>
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nearby</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Other towns we cover</h2>
                <p class="mt-2 text-zinc-600">Also serving areas near <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> across the North West.</p>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All <?= count(function_exists('icomplyLocalTownNames') ? icomplyLocalTownNames() : $allAreas) ?> areas →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($nearby as $town): ?>
                <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                   class="px-5 py-2.5 bg-zinc-50 border rounded-full text-sm font-medium text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
require_once SITE_ROOT . '/includes/quality-bar.php';
echo icomplyQualityBarFaqHtml(
    icomplyQualityBarAreaFaqs($areaName),
    'q5-area-faq',
    'q5-area-faq-jsonld',
    'Questions about ' . $areaName
);
?>

<!-- CTA -->
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Ready for a <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> quote?</h2>
            <p class="mt-2 text-white/75">Chat on WhatsApp or call — we will reply with a quote.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20<?= htmlspecialchars($AREA_URL, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank" rel="noopener"
               class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp for <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
               class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 pt-8">
    <?= shareButtonsHtml($areaName . ' Property Compliance', $metaDesc) ?>
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
                Request your <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> quote
            </h2>
            <p class="mt-3 text-zinc-600">Include your <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> postcode, property type and any panel brands already on site.</p>
        </div>
        <?= icomplyQuoteFormOpen('bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm') ?>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($name . ' in ' . $areaName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package — <?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="<?= htmlspecialchars($AREA, ENT_QUOTES, 'UTF-8') ?> postcode, property type, panel brand / system details…"
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
