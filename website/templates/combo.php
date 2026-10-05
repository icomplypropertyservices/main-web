<?php
/**
 * Single data-driven service × area template (all services).
 * Pure PHP vars via executeTemplateVars() (no {{}} / eval).
 * Content: getServiceBlurb / getServiceStandards + manufacturer helpers.
 * Vars: SERVICE_NAME, SERVICE_SLUG, AREA, AREA_SLUG, SEO_KEYWORDS,
 * MANUFACTURER_TAGS, MANUFACTURER_IMAGES, KEYWORD_IMAGE_1/2/3
 */
$poaCombo = function_exists('isPoaService') && isPoaService($SERVICE_SLUG);
$ownsMainland = function_exists('serviceOwnsMainlandAreas') && serviceOwnsMainlandAreas($SERVICE_SLUG);
$coveragePhrase = $ownsMainland ? 'UK mainland' : 'the North West';
$pageTitle = $SERVICE_NAME . ' in ' . $AREA . ' | iComply Property Services';
$metaDesc = $poaCombo
    ? ('Expert ' . $SERVICE_NAME . ' in ' . $AREA . '. Price on application after scope. Local North West team from Stockport.')
    : ('Expert ' . $SERVICE_NAME . ' in ' . $AREA . '. Installation, maintenance, testing and certification. ' . ($ownsMainland ? 'UK mainland fire protection, scheduled from Stockport.' : 'Local engineers.') . ' Written quote after scope.');
if ($SERVICE_SLUG === 'fire-risk-assessments') {
    $fraGuide = function_exists('fraGuidePrice') ? fraGuidePrice() : '£350';
    $metaDesc = 'Fire risk assessment in ' . $AREA . '. Guide price ' . $fraGuide . ' for a standard FRA on UK mainland. Larger premises confirmed in writing.';
}
$metaKeywords = $SEO_KEYWORDS;
$ogImage = function_exists('serviceImageUrl')
    ? serviceImageUrl($SERVICE_SLUG)
    : url('/assets/images/services/' . $SERVICE_SLUG . '.jpg');
if ($SERVICE_SLUG === 'barriers' && function_exists('barrierManufacturerBySlug')) {
    $cameOg = barrierManufacturerBySlug('came');
    if (is_array($cameOg) && !empty($cameOg['partner_image'])) {
        $ogImage = (string)$cameOg['partner_image'];
    }
}

$allServices = getServices();
$allAreas = function_exists('getAreasForService') ? getAreasForService($SERVICE_SLUG) : getAreas();
$serviceSlug = $SERVICE_SLUG;
$serviceName = $SERVICE_NAME;
$areaName = $AREA;
$areaSlugVal = $AREA_SLUG;
$ncNationwide = ($serviceSlug === 'nurse-call' && function_exists('nationwideAreaRow'))
    ? nationwideAreaRow($areaName)
    : null;
if (is_array($ncNationwide)) {
    $metaDesc = $serviceName . ' in ' . $areaName . ' (' . $ncNationwide['region'] . ', ' . $ncNationwide['nation']
        . '). Quote after scope (POA). Stockport base — visit confirmed after the postcode.';
    if (strlen($metaDesc) > 165) {
        $metaDesc = $serviceName . ' in ' . $areaName . '. Quote after scope (POA). Visit confirmed after the postcode.';
    }
}

// Use getServiceBlurb / getServiceStandards (config.php ← data/service-meta.json). Do not hardcode $serviceBlurbs.
$blurb = getServiceBlurb($serviceSlug);
$standards = getServiceStandards($serviceSlug);
$svcCopy = function_exists('waterAsbestosServiceCopy') ? waterAsbestosServiceCopy($serviceSlug) : null;
$areaExtra = function_exists('waterAsbestosAreaIntro') ? waterAsbestosAreaIntro($serviceSlug, $areaName) : '';

// Nearby towns for “popular nearby” note (same service, other areas)
$nearby = [];
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
if ($ownsMainland && function_exists('mainlandAreaRecord') && function_exists('getMainlandAreaRecords')) {
    $here = mainlandAreaRecord($areaName);
    $region = is_array($here) ? (string)($here['region'] ?? '') : '';
    if ($region !== '' && empty($here['local'])) {
        $same = [];
        foreach (getMainlandAreaRecords() as $row) {
            if ((string)($row['region'] ?? '') === $region && (string)$row['name'] !== $areaName) {
                $same[] = (string)$row['name'];
            }
        }
        if ($same) {
            $nearby = array_slice($same, 0, 12);
        }
    }
}

$popularTowns = array_values(array_filter(
    $ownsMainland
        ? ['London', 'Birmingham', 'Leeds', 'Bristol', 'Cardiff', 'Edinburgh', 'Glasgow', 'Manchester', 'Stockport', 'Liverpool', 'Newcastle upon Tyne', 'Southampton']
        : ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool', 'Bury', 'Sale', 'Altrincham', 'Macclesfield'],
    function ($t) use ($allAreas, $areaName) {
        return $t !== $areaName && in_array($t, $allAreas, true);
    }
));
$popularTowns = array_slice(array_values(array_unique($popularTowns)), 0, 12);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
$canonicalUrl = url('/pages/' . $SERVICE_SLUG . '/' . $AREA_SLUG . '.php');
require SITE_ROOT . '/includes/header.php';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            '@id' => $canonicalUrl . '#service',
            'name' => $serviceName . ' in ' . $areaName,
            'alternateName' => $serviceName . ' installation and maintenance in ' . $areaName,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'image' => $ogImage ?? url('/assets/images/services/' . $serviceSlug . '.jpg'),
            'serviceType' => $serviceName,
            'category' => $serviceName,
            'provider' => [
                '@type' => 'LocalBusiness',
                '@id' => rtrim(SITE_URL, '/') . '/#business',
                'name' => SITE_NAME,
                'url' => SITE_URL,
                'telephone' => PHONE,
                'email' => EMAIL,
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
            'areaServed' => [
                '@type' => 'City',
                'name' => $areaName,
            ],
            'offers' => [
                '@type' => 'Offer',
                'name' => ($poaCombo ? 'Price on application — ' : 'Written quote — ') . $serviceName . ' in ' . $areaName,
                'description' => $poaCombo
                    ? ('Request a scoped POA quote for ' . $serviceName . ' in ' . $areaName . '. No published fee list.')
                    : ('Request a written quote for ' . $serviceName . ' in ' . $areaName . '.'),
                'availability' => 'https://schema.org/InStock',
                'priceCurrency' => 'GBP',
                'url' => url('/contact.php'),
            ],
            'brand' => [
                '@type' => 'Brand',
                'name' => SITE_NAME,
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl . '#webpage',
                'url' => $canonicalUrl,
                'name' => $pageTitle,
                'description' => $metaDesc,
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => url('/pages/services/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceName, 'item' => url('/pages/services/' . $serviceSlug . '.php')],
                ['@type' => 'ListItem', 'position' => 4, 'name' => $serviceName . ' in ' . $areaName, 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'LocalBusiness',
            '@id' => $canonicalUrl . '#local',
            'name' => SITE_NAME . ' — ' . $serviceName . ' in ' . $areaName,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'telephone' => PHONE,
            'email' => EMAIL,
            'image' => $ogImage ?? url('/assets/images/services/' . $serviceSlug . '.jpg'),
            'areaServed' => [
                '@type' => 'City',
                'name' => $areaName,
            ],
            'priceRange' => '££',
            'parentOrganization' => [
                '@type' => 'LocalBusiness',
                '@id' => rtrim(SITE_URL, '/') . '/#business',
                'name' => SITE_NAME,
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
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <a href="<?= url('/pages/services/' . $SERVICE_SLUG . '.php') ?>" class="hover:text-white"><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> in<br>
                    <span class="text-[#ff6b00]"><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <p class="mt-6 text-lg text-white/80 max-w-xl">
                    <?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?>
                    <?php if (is_array($ncNationwide)): ?>
                    Care homes, clinics and supported living in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>. Quote after scope (POA). A visit is confirmed after the postcode.
                    <?php else: ?>
                    Local engineers covering <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> and nearby postcodes.
                    <?php endif; ?>
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get free quote</a>
                    <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Quote for ' . $serviceName . ' in ' . $areaName) ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                    <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                       class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                </div>
                <p class="mt-6 text-sm text-white/60"><?= htmlspecialchars($standards, ENT_QUOTES, 'UTF-8') ?></p>
                <?php if ($serviceSlug === 'fire-risk-assessments'):
                    $fraGuide = function_exists('fraGuidePrice') ? fraGuidePrice() : '£350';
                ?>
                <p class="mt-3 text-sm text-white/80">Guide price <?= htmlspecialchars($fraGuide, ENT_QUOTES, 'UTF-8') ?> for a standard fire risk assessment on UK mainland. Larger or higher-risk premises are confirmed in writing.</p>
                <?php endif; ?>
            </div>
            <div class="relative rounded-3xl overflow-hidden border border-white/10 min-h-[260px] bg-white/5">
                <img src="<?= htmlspecialchars(serviceImageUrl($serviceSlug), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> installation and servicing in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> by iComply Property Services"
                     width="1200" height="800"
                     class="absolute inset-0 w-full h-full object-cover opacity-70"
                     loading="eager"
                     onerror="this.style.display='none'">
                <div class="relative p-6 md:p-8 flex flex-col justify-end min-h-[260px] bg-gradient-to-t from-[#0B1F3A]/90 via-[#0B1F3A]/20 to-transparent">
                    <div class="text-sm text-white/70"><?php if (is_array($ncNationwide)): ?>Nurse call in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($ncNationwide['region'] . ', ' . $ncNationwide['nation'], ENT_QUOTES, 'UTF-8') ?><?php else: ?>Serving <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> &amp; <?= htmlspecialchars($coveragePhrase, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div>
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
        $trust = $poaCombo
            ? [
                ['Local to ' . $areaName, 'Stockport-based team covering ' . $areaName . ' and surrounding postcodes'],
                ['Standards-led', $standards],
                ['File-ready notes', 'Written assessment or survey notes for landlords, agents and dutyholders'],
                ['POA quotes', 'No invented prices — scoped after we know the property'],
            ]
            : [
                ['Local to ' . $areaName, 'Stockport-based engineers covering ' . $areaName . ' and surrounding postcodes'],
                ['Standards-led', $standards],
                ['Full certification', 'Documentation for landlords, insurers and fire officers'],
                ['Fixed-price quotes', 'Clear scope, same-week appointments where capacity allows'],
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
                Expert <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <?php if ($areaExtra !== ''): ?>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed"><?= htmlspecialchars($areaExtra, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($svcCopy['intro']) && is_array($svcCopy['intro'])): ?>
                <?php foreach (array_slice($svcCopy['intro'], 0, 2) as $para): ?>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed"><?= htmlspecialchars((string)$para, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">
                iComply Property Services provides complete <strong><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></strong>
                design, installation, commissioning, maintenance and certification across
                <strong><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></strong> and the wider <?= htmlspecialchars($coveragePhrase, ENT_QUOTES, 'UTF-8') ?>.
                Our qualified engineers deliver fixed-price quotes, same-week appointments and full compliance documentation on every job.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Whether you need a new system, an upgrade, periodic testing or emergency repairs, we support commercial,
                industrial, residential and landlord properties in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>.
                All work is carried out to current British Standards with manufacturer-approved equipment where required.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                Searching for a specific manufacturer or panel brand? We install, service and replace major
                <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> systems including the brands listed below.
                If you already have a panel on site in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>,
                we can inspect, maintain or upgrade it and supply matching certificates.
            </p>
            <?php endif; ?>
        </div>
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-3xl overflow-hidden border bg-zinc-100">
                <img src="<?= url('/assets/images/keywords/' . $KEYWORD_IMAGE_1 . '.jpg') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> panel and equipment used by iComply in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>"
                     width="800" height="600"
                     class="w-full h-44 object-cover"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
                <p class="text-xs text-zinc-500 px-3 py-2"><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> control equipment &amp; panels</p>
            </div>
            <div class="rounded-3xl overflow-hidden border bg-zinc-100">
                <img src="<?= url('/assets/images/keywords/' . $KEYWORD_IMAGE_2 . '.jpg') ?>"
                     alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> installation work and testing in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>"
                     width="800" height="600"
                     class="w-full h-44 object-cover"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
                <p class="text-xs text-zinc-500 px-3 py-2">Installation, testing &amp; certification in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>

    <?php
    require_once SITE_ROOT . '/includes/gm-enrichment.php';
    if (!function_exists('icomplyGmTownBlurb')) {
        require_once SITE_ROOT . '/includes/gm-blurbs.php';
    }
    $gmPacked = icomplyGmTownBlurb($serviceSlug, $areaSlugVal, 'service');
    if (is_array($gmPacked) && ($gmPacked['blurb'] ?? '') !== ''):
    ?>
    <div class="mt-10 max-w-3xl">
        <h3 class="text-2xl font-semibold text-black"><?= htmlspecialchars((string)$gmPacked['h2'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="mt-3 text-lg text-zinc-700 leading-relaxed"><?= icomplyGmBriefHtml((string)$gmPacked['blurb']) ?></p>
        <?php if (($gmPacked['cta'] ?? '') !== ''): ?>
        <p class="mt-3 text-sm font-semibold text-[#061828]"><?= htmlspecialchars((string)$gmPacked['cta'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?= icomplyGmEnrichmentHtml([
        'chrome' => 'site',
        'topic' => $serviceName,
        'townName' => $areaName,
        'serviceSlug' => $serviceSlug,
        'serviceName' => $serviceName,
        'showGuide' => false,
        'skipImage' => true,
    ]) ?>

    <!-- Pillars -->
    <div class="mt-14 grid md:grid-cols-3 gap-6">
        <?php
        $comboPillars = !empty($svcCopy['pillars']) && is_array($svcCopy['pillars']) ? $svcCopy['pillars'] : [
            ['title' => 'Installation & design', 'text' => 'Full design, supply and install of new ' . $serviceName . ' systems to current British Standards for properties in ' . $areaName . '.'],
            ['title' => 'Maintenance & servicing', 'text' => 'Planned contracts, reactive repairs, battery replacements and panel upgrades for systems already on site in ' . $areaName . '.'],
            ['title' => 'Testing & certification', 'text' => 'Statutory tests with logbooks, certificates and documentation ready for audits, insurers and landlords.'],
        ];
        $cpi = 1;
        foreach ($comboPillars as $pillar):
        ?>
        <div class="p-8 bg-white rounded-3xl border hover:border-[#ff6b00] transition">
            <div class="w-10 h-10 rounded-2xl bg-[#0B1F3A] text-white flex items-center justify-center font-bold mb-4"><?= $cpi++ ?></div>
            <h3 class="font-semibold text-xl text-black mb-2"><?= htmlspecialchars((string)$pillar['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-sm text-zinc-600"><?= htmlspecialchars((string)$pillar['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($serviceSlug === 'barriers' && function_exists('barrierPagesBlockHtml')): ?>
<?= barrierPagesBlockHtml('barriers', $areaName, false) ?>
<?php elseif ($serviceSlug === 'access-control' && function_exists('camePartnerPanelHtml')): ?>
<?= camePartnerPanelHtml('access-control') ?>
<?php endif; ?>
<?php if (!$poaCombo && $serviceSlug !== 'barriers'): ?>
<!-- MANUFACTURERS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Manufacturers</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Brands we install &amp; service in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">Looking for your exact panel brand? We support major <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> manufacturers so customers searching for their equipment in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> find iComply.</p>
            </div>
            <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Browse trade shop →</a>
        </div>
        <div class="flex flex-wrap gap-3 mb-8">
            <?= $MANUFACTURER_TAGS ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?= $MANUFACTURER_IMAGES ?>
        </div>
        <div class="mt-10 rounded-3xl overflow-hidden border bg-white">
            <img src="<?= url('/assets/images/keywords/' . $KEYWORD_IMAGE_3 . '.jpg') ?>"
                 alt="<?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> manufacturer panels and equipment — iComply <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>"
                 width="1200" height="700"
                 class="w-full h-64 md:h-80 object-cover"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/' . $SERVICE_SLUG . '.jpg') ?>'">
            <p class="text-xs text-zinc-500 px-4 py-3">Manufacturer panels &amp; systems commonly installed and serviced in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- RELATED SERVICES -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Also available</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Related services in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mt-2 text-zinc-600">Combine multiple compliance services into one visit schedule for landlords and FM teams.</p>
        </div>
        <div class="flex flex-wrap gap-4">
            <a href="<?= url('/pages/areas/' . $AREA_SLUG . '.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> →</a>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-zinc-500 hover:text-[#ff6b00]">Service hubs →</a>
        </div>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($allServices as $slug => $name):
            if ($slug === $serviceSlug) continue;
            $rBlurb = getServiceBlurb($slug);
        ?>
        <a href="<?= url('/pages/' . $slug . '/' . $AREA_SLUG . '.php') ?>"
           class="group bg-white border rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-32 bg-zinc-100 overflow-hidden">
                <img src="<?= htmlspecialchars(serviceImageUrl($slug), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy"
                     onerror="this.parentElement.style.display='none'">
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <h3 class="font-semibold text-black">
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                    <span class="text-zinc-400 font-normal text-sm">in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
                </h3>
                <p class="text-sm text-zinc-600 mt-2 flex-1 line-clamp-2"><?= htmlspecialchars($rBlurb, ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-3 text-sm font-semibold text-[#ff6b00]">View <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> page →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- POPULAR NEARBY -->
<?php if (!empty($nearby) || !empty($popularTowns)): ?>
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Popular nearby</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">
                    <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> near <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
                </h2>
                <p class="mt-2 text-zinc-600">
                    We also cover towns near <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> —
                    open a dedicated local page for the same service.
                </p>
            </div>
            <a href="<?= url('/pages/services/' . $SERVICE_SLUG . '.php') ?>" class="text-sm font-semibold text-[#ff6b00]">
                <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> hub →
            </a>
        </div>
        <?php if (!empty($popularTowns)): ?>
        <div class="flex flex-wrap gap-2 mb-4">
            <?php foreach ($popularTowns as $town): ?>
                <a href="<?= url('/pages/' . $SERVICE_SLUG . '/' . areaSlug($town) . '.php') ?>"
                   class="px-5 py-2.5 bg-white border rounded-full text-sm font-medium text-black hover:border-[#ff6b00] hover:shadow-sm transition">
                    <?= htmlspecialchars($serviceName . ' in ' . $town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($nearby)): ?>
        <p class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3 mt-6">Nearby towns</p>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($nearby as $town):
                if (in_array($town, $popularTowns, true)) continue;
            ?>
                <a href="<?= url('/pages/' . $SERVICE_SLUG . '/' . areaSlug($town) . '.php') ?>"
                   class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs text-zinc-700 hover:border-[#ff6b00]">
                    <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= url('/pages/areas/index.php') ?>" class="px-3 py-1.5 text-xs font-semibold text-[#ff6b00]">+ more towns</a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA BAND -->
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Need <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>?</h2>
            <p class="mt-3 text-white/75"><?= $poaCombo
                ? 'Price on application after we confirm the property in ' . htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') . '. No invented fee list — call, WhatsApp or send the form.'
                : 'Written quotes after scope. Same-week appointments where capacity allows. Full certification on every job. Tell us your panel brand or system type.' ?></p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#quote" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Quote for ' . $serviceName . ' in ' . $areaName) ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp quote</a>
                <a href="<?= url('/contact.php?service=' . rawurlencode($serviceName) . '&area=' . rawurlencode($areaName)) ?>"
                   class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Contact form</a>
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                   class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= url('/shop/index.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Trade shop</a>
                <a href="<?= url('/pages/packages.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Packages</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="px-6 py-3 rounded-2xl bg-white/10 border border-white/20 font-semibold hover:bg-white/15">Landlords</a>
            </div>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Based in Stockport — covering <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> &amp; <?= htmlspecialchars($coveragePhrase, ENT_QUOTES, 'UTF-8') ?></li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Installation, servicing and certification</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Multi-service packages for landlords &amp; FM teams</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Response aim: within 2 hours on business days</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Manufacturer brands supported — see tags above</li>
        </ul>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 pt-8">
    <?= shareButtonsHtml($serviceName . ' in ' . $areaName, $metaDesc) ?>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                Request <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?> quote in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <p class="mt-3 text-zinc-600">
                Service and area are pre-filled below. Add your postcode, property type and any panel brand —
                we aim to respond within 2 hours on business days.
            </p>
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
                            <?= htmlspecialchars($name . ' in ' . $areaName, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package — <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="<?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> postcode, property type, panel brand / system details…"
                      class="w-full border px-5 py-3.5 rounded-2xl"><?= htmlspecialchars($serviceName . ' enquiry for a property in ' . $areaName . '. ', ENT_QUOTES, 'UTF-8') ?></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                Prefer email?
                <a href="<?= url('/contact.php?service=' . rawurlencode($serviceName) . '&area=' . rawurlencode($areaName)) ?>"
                   class="underline hover:text-black">Open the contact page</a>
                with service and area noted.
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
