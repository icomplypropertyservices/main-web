<?php
/**
 * Manufacturer hub template.
 * Pure PHP vars via executeTemplateVars() (no {{}} / eval).
 * MFR_PRODUCTS_HTML, MFR_RELATED_HTML, SERVICE_NAME (primary)
 */
require_once SITE_ROOT . '/includes/seo.php';
$seoFamily = 'manufacturer';
$pageTitle = $MFR_NAME . ' products and service';
$metaDesc = seo_fit_meta($MFR_BLURB . ' Serviced from Stockport SK2 by iComply. Call 07517806082.');
$metaKeywords = $MFR_SEO_KEYWORDS;
$canonicalUrl = url('/pages/manufacturers/' . $MFR_SLUG . '.php');

require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/shopify.php';

$mfrSlug = $MFR_SLUG;
$mfrName = $MFR_NAME;
$entry = getManufacturerBySlug($mfrSlug) ?? [];
$services = getServices();
$mfrServices = $entry['services'] ?? [];
$isBarrierBrand = in_array('barriers', $mfrServices, true);
$isPartner = !empty($entry['partner']);
$products = $entry['products'] ?? [];
$primaryService = $mfrServices[0] ?? 'fire-alarms';
$primaryServiceName = $services[$primaryService] ?? 'Compliance';
if (function_exists('icomplyStripTimingFragment')) {
    $MFR_BLURB = icomplyStripTimingFragment($MFR_BLURB);
}
$gasBrand = function_exists('icomplyManufacturerEntryIsGas') && icomplyManufacturerEntryIsGas($entry);
if ($gasBrand && function_exists('icomplyGasBrandBlurb')) {
    $MFR_BLURB = icomplyGasBrandBlurb($mfrName);
    $metaDesc = $MFR_BLURB;
} else {
    $metaDesc = $MFR_BLURB;
}
$ogImage = manufacturerImageUrl($mfrSlug, $primaryService);
if ($ogImage === '') {
    $ogImage = url('/assets/images/og-default.svg');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Brand',
            'name' => $mfrName,
            'url' => $canonicalUrl,
        ],
        [
            '@type' => 'Store',
            'name' => SITE_NAME . ' — ' . $mfrName,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'telephone' => PHONE,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road, Offerton',
                'addressLocality' => 'Stockport',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'brand' => ['@type' => 'Brand', 'name' => $mfrName],
        ],
        [
            '@type' => 'ItemList',
            'name' => $mfrName . ' products',
            'itemListElement' => array_values(array_map(function ($p, $i) use ($mfrName) {
                return [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $p['title'] ?? ($mfrName . ' product'),
                    'description' => $p['blurb'] ?? '',
                ];
            }, $products, array_keys($products))),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Manufacturers', 'item' => url('/pages/manufacturers/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $mfrName, 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $gasBrand ? ('Does iComply install or service ' . $mfrName . ' gas appliances?') : ('Do you install and service ' . $mfrName . ' systems?'),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $gasBrand
                            ? (icomplyGasLegalSentence() . ' Quotes for ' . $mfrName . ' appliances are price on application.')
                            : ($isPartner
                                ? 'Yes. iComply Property Services is a CAME partner. Gard rising-arm barriers are the range we specify. Other barrier brands are serviced or replaced and are not partnerships.'
                                : ($isBarrierBrand
                                    ? 'We service and replace ' . $mfrName . ' barriers already on private sites. That is not a ' . $mfrName . ' partnership. The barrier partnership is CAME.'
                                    : 'Yes. We install and service ' . $mfrName . ' equipment across Greater Manchester and the North West.')),
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Can I buy ' . $mfrName . ' parts and kits from you?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $isBarrierBrand
                        ? 'Barrier equipment is quoted on application after the lane is surveyed. This page does not publish a fee.'
                        : 'We supply trade kits and accessories for ' . $mfrName . ' via our shop (Shopify when live) and can quote project-specific equipment for install jobs.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Which areas do you cover for ' . $mfrName . ' work?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $isBarrierBrand
                        ? 'Barrier pages cover UK towns over 10,000 people. Attendance is planned from Stockport SK2. We do not claim a depot in each town.'
                        : 'We cover 150+ towns including Manchester, Stockport, Bolton, Liverpool, Preston and the wider North West from our Stockport base.',
                    ],
                ],
            ],
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO -->
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="hover:text-white">Manufacturers</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    <?= $gasBrand ? 'Brand · Gas Safe registered engineers · iComply is not Gas Safe registered' : 'Brand · Trade shop · Install &amp; service' ?>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?><br>
                    <span class="text-[#ff6b00]"><?= $gasBrand ? 'trade supply only' : 'products &amp; service' ?></span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl"><?= htmlspecialchars($MFR_BLURB, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#products" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Shop products</a>
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100"><?= $gasBrand ? 'Non-gas quote' : 'Install quote' ?></a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($mfrName . ' enquiry') ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                </div>
            </div>
            <div class="relative rounded-3xl overflow-hidden border border-white/10 min-h-[260px] bg-white/5">
                <?php $mfrHero = manufacturerImageUrl($mfrSlug, $primaryService); ?>
                <?php if ($mfrHero !== ''): ?>
                <img src="<?= htmlspecialchars($mfrHero, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> equipment — iComply Property Services"
                     width="1200" height="800"
                     class="absolute inset-0 w-full h-full object-cover opacity-70"
                     loading="eager">
                <?php endif; ?>
                <div class="relative p-6 md:p-8 flex flex-col justify-end min-h-[260px] bg-gradient-to-t from-[#0B1F3A]/90 via-transparent to-transparent">
                    <div class="text-sm text-white/70"><?php if ($gasBrand): ?>Trade supply only. iComply is not Gas Safe registered.<?php elseif ($isPartner): ?>CAME partner · install and service<?php elseif ($isBarrierBrand): ?>Service and replacement · not a brand partnership<?php else: ?>Install, service and trade supply<?php endif; ?></div>
                    <div class="text-2xl font-semibold mt-1"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> · <?= $isBarrierBrand ? 'UK towns over 10,000' : 'North West' ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES FOR THIS BRAND -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3">Related services</div>
        <div class="flex flex-wrap gap-2">
            <?= $MFR_SERVICES_HTML ?>
        </div>
    </div>
</section>

<?php if (function_exists('manufacturerProductLines') && function_exists('manufacturerLineCardsHtml')): ?>
<section class="max-w-7xl mx-auto px-6 py-12">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Product lines</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> ranges</h2>
    <p class="mt-2 text-zinc-600 max-w-3xl">Each range has its own mark. Area pages repeat these lines with a local introduction.</p>
    <?= manufacturerLineCardsHtml($entry) ?>
    <?php if (function_exists('manufacturerWizardFamily') && manufacturerWizardFamily($entry)): ?>
        <p class="mt-4 text-sm text-zinc-600">AOV and barriers pages include a quote wizard. Kit list prices stay on the <a class="text-[#ff6b00] font-semibold" href="<?= url('/products') ?>#<?= manufacturerWizardFamily($entry) === 'barriers' ? 'barrier-packs' : 'aov-kits' ?>">products hub</a>.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- INTRO -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-5 gap-12">
        <div class="lg:col-span-3">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">About <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?></div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                <?= $gasBrand ? 'Supply listing for' : 'Install, service &amp; buy' ?> <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed"><?= htmlspecialchars($MFR_BLURB, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($gasBrand): ?>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed"><?= htmlspecialchars(icomplyGasLegalSentence(), ENT_QUOTES, 'UTF-8') ?> Trade kits for <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>, where listed, are supplies only.</p>
            <ul class="mt-6 space-y-2 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> iComply is not Gas Safe registered</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> No Gas Safe logo, badge, or registration number</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Non-gas compliance is quoted POA</li>
            </ul>
            <?php elseif ($isBarrierBrand): ?>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                <?php if ($isPartner): ?>
                    iComply is a CAME partner. Gard barriers are specified for private lanes, with safety devices taken from the CAME manual for that model.
                    UK towns over 10,000 each have a barrier page. Those pages carry official population figures. They do not invent a local depot.
                    The quote is on application.
                <?php else: ?>
                    <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> barriers are equipment we service or replace.
                    This is not a <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> partnership and not an authorised-dealer claim.
                    The barrier partnership is CAME. Town pages cover UK places over 10,000 people. Visits are planned from Stockport SK2. Price on application.
                <?php endif; ?>
            </p>
            <?php if ($isPartner): ?>
            <p class="mt-4 text-sm font-semibold flex flex-wrap gap-x-4 gap-y-2">
                <a class="text-[#ff6b00]" href="<?= url('/pages/services/barriers') ?>">Barriers hub</a>
                <a class="text-[#ff6b00]" href="<?= url('/pages/keywords/came-partner') ?>">Partnership guide</a>
                <a class="text-[#ff6b00]" href="<?= url('/pages/keywords/came-gard-barrier') ?>">Gard barrier</a>
                <a class="text-[#ff6b00]" href="<?= url('/pages/keywords/came-gard-pt') ?>">Gard PT</a>
            </p>
            <?php else: ?>
            <p class="mt-4 text-sm">
                <a class="font-semibold text-[#ff6b00]" href="<?= url('/pages/manufacturers/came') ?>">CAME partner page</a>
                <span class="text-zinc-400"> · </span>
                <a class="font-semibold text-[#ff6b00]" href="<?= url('/pages/services/barriers') ?>">All barrier towns</a>
            </p>
            <?php endif; ?>
            <?php else: ?>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Whether you need a new <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> system designed and commissioned,
                planned maintenance on existing equipment, or trade kits and spares, our Stockport-based engineers
                cover Manchester, Bolton, Liverpool, Preston and 150+ North West towns. All work is documented for
                landlords, insurers and facilities managers.
            </p>
            <ul class="mt-6 space-y-2 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> New installs &amp; system upgrades</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Servicing, repairs &amp; certification</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Trade products &amp; engineer kits (Shopify-ready)</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Multi-site &amp; landlord packages</li>
            </ul>
            <?php endif; ?>
        </div>
        <div class="lg:col-span-2 bg-[#0B1F3A] text-white rounded-3xl p-8">
            <h3 class="text-xl font-semibold">Need <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> support?</h3>
            <p class="mt-3 text-white/75 text-sm"><?= $gasBrand ? 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered. Quotes are price on application.' : 'Tell us your panel model, postcode and whether you need install, service or parts.' ?></p>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="block mt-6 px-5 py-3 bg-white text-[#0B1F3A] rounded-2xl font-semibold text-center"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($mfrName . ' quote') ?>"
               target="_blank" rel="noopener"
               class="block mt-3 px-5 py-3 bg-green-600 rounded-2xl font-semibold text-center">WhatsApp</a>
            <a href="<?= url('/shop/index.php') ?>" class="block mt-3 px-5 py-3 border border-white/30 rounded-2xl font-semibold text-center hover:bg-white/10">Full shop</a>
        </div>
    </div>
</section>

<?php
if (function_exists('manufacturerProductLinesForBrandHtml')) {
    $brandLines = manufacturerProductLinesForBrandHtml($mfrSlug);
    if ($brandLines !== '') {
        echo '<section class="max-w-7xl mx-auto px-6 pb-4">' . $brandLines . '</section>';
    }
}
?>

<!-- PRODUCTS -->
<section id="products" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Shop</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                    <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> products &amp; kits
                </h2>
                <p class="mt-2 text-zinc-600">Trade-oriented kits ready for Shopify Buy Buttons when credentials are configured.</p>
            </div>
            <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All shop products →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?= $MFR_PRODUCTS_HTML ?>
        </div>
        <p class="mt-8 text-sm text-zinc-600">
            Looking for more trade kits?
            <a href="<?= url('/shop/index.php') ?>" class="text-[#ff6b00] font-semibold">Open the full shop</a>
            <?php if (function_exists('shopifyStoreUrl') && shopifyStoreUrl()): ?>
                or
                <a href="<?= htmlspecialchars(shopifyStoreUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-[#ff6b00] font-semibold">checkout on Shopify →</a>
            <?php endif; ?>
        </p>
    </div>
</section>

<?php
$acnBrand = in_array('access-control', $mfrServices, true) && $mfrSlug !== 'tunstall';
if ($acnBrand && function_exists('acnCities')):
    $acnGuide = function_exists('acnBrandGuideSlug') ? acnBrandGuideSlug($mfrSlug) : 'access-control-system';
    $acnCitySample = array_slice(acnCities(), 0, 8);
?>
<section class="bg-[#061828] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> on UK access-control jobs</h2>
        <p class="mt-3 max-w-3xl text-white/85">This brand is on the access-control list. Tunstall is not. Nationwide city notes and the keyword guide sit with the Stockport workshop, not a local depot in every city.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <a class="px-4 py-2 rounded-full bg-[#ff6b00] font-semibold" href="<?= url('/pages/access-control-systems') ?>">Access control systems across the UK</a>
            <a class="px-4 py-2 rounded-full bg-white text-[#061828] font-semibold" href="<?= url('/pages/keywords/' . $acnGuide) ?>">Brand guide</a>
            <?php foreach ($acnCitySample as $acnCityRow): ?>
                <a class="px-4 py-2 rounded-full border border-white/40 text-sm font-semibold" href="<?= url('/pages/access-control-systems/' . areaSlug((string)$acnCityRow['slug'])) ?>"><?= htmlspecialchars((string)$acnCityRow['name'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- LOCAL AREAS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local install</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
        <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> near you
    </h2>
    <?php if ($isBarrierBrand): ?>
    <p class="mt-2 text-zinc-600 mb-6">Town pages use the official count for that place. Attendance is from Stockport. Price on application.</p>
    <div class="flex flex-wrap gap-2">
        <?php
        require_once SITE_ROOT . '/includes/barriers.php';
        foreach (['Manchester', 'Birmingham', 'Leeds', 'Glasgow', 'Cardiff', 'Westminster', 'Aberdeen', 'Stockport'] as $t):
            $tSlug = barriersPlaceSlugByName($t);
            if (!$tSlug) {
                continue;
            }
        ?>
            <a href="<?= url('/pages/barriers/' . $tSlug) ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">
                <?= htmlspecialchars('Barriers in ' . $t, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <p class="mt-2 text-zinc-600 mb-6"><?= function_exists('manufacturerCoverageLabel') ? htmlspecialchars(manufacturerCoverageLabel($entry), ENT_QUOTES, 'UTF-8') : 'Local pages' ?> — each town has its own <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> introduction.</p>
    <?php if (function_exists('manufacturerAreaChipsHtml')): ?>
        <?= manufacturerAreaChipsHtml($entry) ?>
    <?php endif; ?>
    <p class="mt-6 text-sm text-zinc-600">Service pages for context:</p>
    <div class="flex flex-wrap gap-2">
        <?php
        $scoped = function_exists('manufacturerAreasFor') ? manufacturerAreasFor($entry) : getAreas();
        $towns = array_values(array_filter(
            ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool', 'Burnley'],
            fn($t) => in_array($t, $scoped, true)
        ));
        foreach ($towns as $t):
        ?>
            <a href="<?= url('/pages/areas/' . areaSlug($t) . '.php') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">
                <?= htmlspecialchars($primaryServiceName . ' in ' . $t, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- RELATED BRANDS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Also stocked</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Related manufacturers</h2>
            </div>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?= $MFR_RELATED_HTML ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-zinc-50 border-t" data-seo-faq="1">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-10">
            <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> FAQ
        </h2>
        <div class="space-y-4">
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer">Do you install <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>?</summary>
                <p class="mt-3 text-sm text-zinc-600">Yes — design, supply, install, commission and certificate across the North West.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer">Can I buy <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> parts online?</summary>
                <p class="mt-3 text-sm text-zinc-600">Browse kits above or our shop. Shopify checkout activates when store credentials are set in config.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer">Do you maintain existing <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> systems?</summary>
                <p class="mt-3 text-sm text-zinc-600">We service, repair, reprogram and upgrade systems already on site, with full documentation.</p>
            </details>
        </div>
        <?= shareButtonsHtml($mfrName . ' Products & Service', $metaDesc) ?>
    </div>
</section>

<!-- CTA BAND -->
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Need <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?>?</h2>
            <p class="mt-3 text-white/75">Free fixed-price quotes. Install, service and trade products across the North West.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#quote" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request quote</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Trade shop</a>
                <a href="<?= url('/pages/packages.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Packages</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Landlords</a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Based in Stockport — North West coverage</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Install, service &amp; certification for <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?></li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Multi-service packages for landlords &amp; FM teams</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Quotes are POA</li>
        </ul>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                <?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> enquiry
            </h2>
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
                    <option value="<?= htmlspecialchars($mfrName . ' — install / service', ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> — install / service</option>
                    <option value="<?= htmlspecialchars($mfrName . ' — products / parts', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($mfrName, ENT_QUOTES, 'UTF-8') ?> — products / parts</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="Model / panel type, postcode, install or parts needed…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit enquiry</button>
        </form>
    </div>
</section>
<?= function_exists('shopifyBuyButtonScript') ? shopifyBuyButtonScript() : '' ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
