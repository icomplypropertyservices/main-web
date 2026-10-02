<?php
/**
 * HTML site map — human-friendly directory of main pages, services, areas, brands and resources.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Site Map | Icomply Property Services';
$metaDesc = 'Browse the Icomply site map — fire safety, professional and construction services, popular North West areas, manufacturers, keyword guides and XML sitemaps.';
$metaKeywords = 'Icomply site map, fire risk assessment, kitchen fitting, property compliance, North West areas, EICR guides';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/site-map.php');

$services = getServices();
$areas = getAreas();
$categories = getServiceCategories();

$featuredMfr = array_filter(getManufacturerCatalog(), fn($c) => !empty($c['featured']));
if (!$featuredMfr) {
    $featuredMfr = array_slice(getManufacturerCatalog(), 0, 12, true);
} else {
    $featuredMfr = array_slice($featuredMfr, 0, 12, true);
}

$popularAreas = array_values(array_unique(array_merge(
    ['Manchester', 'Stockport', 'Bolton', 'Oldham', 'Rochdale', 'Wigan', 'Salford', 'Liverpool', 'Preston', 'Blackpool', 'Chester', 'Warrington'],
    array_slice($areas, 0, 12)
)));
$areaSet = array_flip($areas);
$popularAreas = array_values(array_filter($popularAreas, function ($a) use ($areaSet) {
    return isset($areaSet[$a]);
}));
if (!$popularAreas) {
    $popularAreas = array_slice($areas, 0, 12);
}

$mainPages = [
    ['href' => rtrim(SITE_URL, '/') . '/', 'label' => 'Home'],
    ['href' => url('/pages/about.php'), 'label' => 'About'],
    ['href' => url('/pages/services/index.php'), 'label' => 'All services'],
    ['href' => url('/pages/areas/index.php'), 'label' => 'All areas'],
    ['href' => url('/pages/manufacturers/index.php'), 'label' => 'Manufacturers / brands'],
    ['href' => url('/pages/keywords/index.php'), 'label' => 'Keyword guides'],
    ['href' => '/shop/', 'label' => 'Shop / supplies hubs'],
    ['href' => '/shop/fire/', 'label' => 'Shop — Fire'],
    ['href' => '/shop/electrical/', 'label' => 'Shop — Electrical'],
    ['href' => '/shop/security/', 'label' => 'Shop — Security'],
    ['href' => '/shop/gas/', 'label' => 'Shop — Gas'],
    ['href' => '/products', 'label' => 'Products'],
    ['href' => url('/pages/packages.php'), 'label' => 'Packages'],
    ['href' => url('/pages/pricing.php'), 'label' => 'Pricing guide'],
    ['href' => url('/pages/landlords.php'), 'label' => 'Landlords'],
    ['href' => url('/pages/commercial.php'), 'label' => 'Commercial / FM'],
    ['href' => url('/pages/care-homes.php'), 'label' => 'Care homes'],
    ['href' => url('/pages/ev-chargers.php'), 'label' => 'EV chargers'],
    ['href' => url('/pages/maintenance.php'), 'label' => 'Maintenance contracts'],
    ['href' => url('/pages/emergency.php'), 'label' => 'Emergency call-out'],
    ['href' => url('/pages/reviews.php'), 'label' => 'Reviews'],
    ['href' => url('/pages/resources/index.php'), 'label' => 'Resources hub'],
    ['href' => url('/pages/faq.php'), 'label' => 'FAQ'],
    ['href' => url('/contact.php'), 'label' => 'Contact / free quote'],
    ['href' => url('/privacy.php'), 'label' => 'Privacy policy'],
    ['href' => url('/terms.php'), 'label' => 'Terms & conditions'],
    ['href' => url('/sitemap.xml'), 'label' => 'XML sitemap'],
];

$kwCount = count(getMajorKeywords());
$mfrCount = count(getManufacturerCatalog());

$resourceLinks = [
    ['href' => url('/pages/resources/index.php'), 'label' => 'Resources hub', 'blurb' => 'Guides, checklists and compliance topics'],
    ['href' => url('/pages/resources/eicr-guide.php'), 'label' => 'EICR guide', 'blurb' => 'Landlord & commercial electrical testing'],
    ['href' => url('/pages/resources/fire-alarm-servicing.php'), 'label' => 'Fire alarm servicing', 'blurb' => 'BS 5839 maintenance overview'],
    ['href' => url('/pages/resources/landlord-compliance-checklist.php'), 'label' => 'Landlord compliance checklist', 'blurb' => 'Certificates and safety checks'],
    ['href' => url('/pages/resources/emergency-lighting-testing.php'), 'label' => 'Emergency lighting testing', 'blurb' => 'BS 5266 monthly & annual tests'],
    ['href' => url('/pages/resources/cctv-for-business.php'), 'label' => 'CCTV for business', 'blurb' => 'Design, install and monitoring'],
    ['href' => url('/pages/resources/access-control-guide.php'), 'label' => 'Access control guide', 'blurb' => 'Doors, credentials and fire release'],
    ['href' => url('/pages/landlord-certificates'), 'label' => 'Landlord certificates hub', 'blurb' => 'EICR, gas, FRA, alarms and EPCs'],
    ['href' => url('/pages/gas-safety-certificate'), 'label' => 'Gas safety certificate', 'blurb' => 'Landlord gas records from SK2'],
    ['href' => url('/pages/fire-risk-assessment'), 'label' => 'Fire risk assessment', 'blurb' => 'Written FRA and action plan'],
    ['href' => url('/pages/electrical-safety-landlords'), 'label' => 'Electrical safety for landlords', 'blurb' => 'EICR and remedials'],
    ['href' => url('/pages/commercial-fire-safety'), 'label' => 'Commercial fire safety', 'blurb' => 'Alarms, lighting, doors, FRA'],
    ['href' => url('/pages/resources/index.php') . '#batch-a', 'label' => 'Batch A — days 1–5', 'blurb' => 'Gas, FRA, smoke/CO, PAT, EPC'],
    ['href' => url('/pages/resources/index.php') . '#batch-b', 'label' => 'Batch B — days 6–14', 'blurb' => 'Fire, commercial, care, booking, GM'],
    ['href' => url('/pages/resources/index.php') . '#batch-c', 'label' => 'Batch C — SEO hubs', 'blurb' => 'Twelve quality hubs (not HMO packages)'],
    ['href' => url('/pages/services/legionella-risk-assessment.php'), 'label' => 'Legionella risk assessment', 'blurb' => 'Water hygiene / Legionnaires — POA'],
    ['href' => url('/pages/services/asbestos-survey.php'), 'label' => 'Asbestos survey', 'blurb' => 'Management & refurbishment surveys — POA'],
    ['href' => url('/pages/resources/legionella-risk-assessment.php'), 'label' => 'Legionella guide', 'blurb' => 'L8 / HSG274 plain English'],
    ['href' => url('/pages/resources/asbestos-survey.php'), 'label' => 'Asbestos guide', 'blurb' => 'Duty to manage, CAR 2012'],
    ['href' => url('/pages/keywords/index.php'), 'label' => "Keyword guides ({$kwCount})", 'blurb' => 'EICR reports, gas certs, security lighting & more'],
    ['href' => url('/pages/manufacturers/index.php'), 'label' => "Manufacturers ({$mfrCount})", 'blurb' => 'Brand pages and trade kits'],
    ['href' => url('/pages/faq.php'), 'label' => 'FAQ', 'blurb' => 'Common compliance questions answered'],
    ['href' => url('/pages/packages.php'), 'label' => 'Packages', 'blurb' => 'Multi-service landlord & FM packages'],
    ['href' => url('/pages/landlords.php'), 'label' => 'Landlords', 'blurb' => 'EICR, gas, fire and emergency lighting'],
    ['href' => url('/pages/commercial.php'), 'label' => 'Commercial', 'blurb' => 'Fire, electrical and security for sites'],
];

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Site map</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                HTML directory · Easy navigation
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Site <span class="text-[#ff6b00]">map</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Directory of <?= count($services) ?> services (fire safety, professional &amp; construction),
                <?= count($areas) ?>+ towns, brands, keyword guides and resources.
                Machine-readable list:
                <a href="<?= url('/sitemap.xml') ?>" class="text-[#ff6b00] hover:underline font-medium">XML sitemap (core pages, services, towns, brands and guides — no junk keyword×area URLs)</a>.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#main-pages" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Main pages</a>
                <a href="#services" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">All services</a>
                <a href="#keywords" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Keywords</a>
                <a href="#areas" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Areas</a>
            </div>
        </div>
    </div>
</section>

<!-- TOC -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-6">
        <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3">On this page</div>
        <nav class="flex flex-wrap gap-2" aria-label="Site map sections">
            <?php
            $toc = [
                'main-pages' => 'Main pages',
                'services' => 'Services',
                'service-areas' => 'Service × area',
                'areas' => 'Popular areas',
                'keywords' => 'Keyword guides',
                'manufacturers' => 'Manufacturers',
                'resources' => 'Resources',
            ];
            foreach ($toc as $id => $label): ?>
                <a href="#<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-3 py-1.5 rounded-full text-xs font-medium border bg-white text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= url('/sitemap.xml') ?>"
               class="px-3 py-1.5 rounded-full text-xs font-medium border border-[#ff6b00]/40 bg-orange-50 text-[#ff6b00] hover:border-[#ff6b00] transition">
                XML sitemap
            </a>
        </nav>
    </div>
</section>

<!-- MAIN PAGES -->
<section id="main-pages" class="max-w-7xl mx-auto px-6 py-16 md:py-20 scroll-mt-24">
    <div class="mb-10">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Navigate</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Main pages</h2>
        <p class="mt-2 text-zinc-600 max-w-xl">Core site sections for quotes, coverage, brands and company information.</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach ($mainPages as $page): ?>
            <a href="<?= htmlspecialchars($page['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="flex items-center justify-between gap-3 bg-white border border-zinc-200 rounded-2xl px-5 py-4 text-black hover:border-[#ff6b00] hover:shadow-md transition group">
                <span class="font-medium"><?= htmlspecialchars($page['label'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-[#ff6b00] text-sm font-semibold opacity-70 group-hover:opacity-100">→</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- SERVICES BY CATEGORY -->
<section id="services" class="bg-zinc-50 border-y scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Catalogue</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">All <?= count($services) ?> services</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Fire safety, professional support and construction — each service has local pages for <?= count($areas) ?>+ towns.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Services hub →</a>
        </div>
        <?php foreach ($categories as $catKey => $cat):
            $catServices = getServicesInCategory($catKey);
            if (!$catServices) {
                continue;
            }
        ?>
        <div class="mb-12" id="map-<?= htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8') ?>">
            <h3 class="text-xl font-semibold text-black mb-2"><?= htmlspecialchars($cat['label'] ?? $catKey, ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if (!empty($cat['blurb'])): ?>
                <p class="text-sm text-zinc-600 mb-4 max-w-2xl"><?= htmlspecialchars($cat['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($catServices as $slug => $name): ?>
                    <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>"
                       class="bg-white border border-zinc-200 rounded-2xl px-5 py-4 hover:border-[#ff6b00] hover:shadow-md transition group flex items-center justify-between gap-3">
                        <span class="font-medium text-black group-hover:text-[#ff6b00]"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[#ff6b00] text-sm font-semibold shrink-0">→</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <p class="text-sm text-zinc-500">
            Full machine-readable list (<?= number_format(count($services) * count($areas) + count(getMajorKeywords()) * count($areas) + count($services) + count(getMajorKeywords()) + count($areas)) ?>+ URLs):
            <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/sitemap.xml') ?>">XML sitemap index</a>.
        </p>
    </div>
</section>

<!-- LOCAL LANDINGS — only URLs that exist on the default Netlify export -->
<section id="service-areas" class="max-w-7xl mx-auto px-6 py-16 md:py-20 scroll-mt-24">
    <div class="mb-10">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local landings</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Service hubs &amp; keyword towns</h2>
        <p class="mt-2 text-zinc-600 max-w-2xl">
            Browse a service hub, then open a keyword guide for your town. AOV and smoke control
            has a local page for every UK mainland town or city with more than 10,000 people
            (England, Wales and Scotland, including London boroughs). Each
            <code>/pages/aov-air-handling/{town}</code> URL is in the XML sitemap.
            Places at or under 10,000 people, and Northern Ireland, are not included.
            Other services stay on hubs plus keyword×town pages.
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php
        foreach (['electrical' => 'Electrical', 'gas-systems' => 'Gas Systems', 'aov-air-handling' => 'AOV & Smoke Control', 'fire-alarms' => 'Fire Alarms', 'cctv' => 'CCTV'] as $sSlug => $sName):
            if (!isset($services[$sSlug])) { continue; }
            ?>
            <a href="<?= url('/pages/services/' . rawurlencode($sSlug) . '.php') ?>"
               class="px-3 py-1.5 bg-white border rounded-full text-xs sm:text-sm text-black hover:border-[#ff6b00] transition">
                <?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?> hub
            </a>
        <?php endforeach;
        $egKwMap = getMajorKeywords();
        $egTowns = array_values(array_intersect($popularAreas, ['Stockport', 'Manchester', 'Bolton']));
        foreach (['rewire', 'emergency-electrician', 'boiler', 'cp12'] as $egSlug):
            if (!isset($egKwMap[$egSlug])) { continue; }
            $egName = $egKwMap[$egSlug]['name'] ?? $egSlug;
            foreach ($egTowns as $town):
                ?>
                <a href="<?= url('/pages/keywords/' . rawurlencode($egSlug) . '/' . areaSlug($town) . '.php') ?>"
                   class="px-3 py-1.5 bg-white border rounded-full text-xs sm:text-sm text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($egName . ' in ' . $town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach;
        endforeach;
        foreach (getAovNationwideServices() as $aovSlug):
            if (!isset($services[$aovSlug])) { continue; }
            $aovName = $services[$aovSlug];
            foreach ($egTowns as $town):
                ?>
                <a href="<?= url('/pages/' . rawurlencode($aovSlug) . '/' . areaSlug($town) . '.php') ?>"
                   class="px-3 py-1.5 bg-white border rounded-full text-xs sm:text-sm text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($aovName . ' in ' . $town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach;
        endforeach;
        foreach (getAovFeaturedKeywordSlugs() as $aovKw):
            if (!isset($egKwMap[$aovKw])) { continue; }
            $aovKwName = $egKwMap[$aovKw]['name'] ?? $aovKw;
            foreach ($egTowns as $town):
                ?>
                <a href="<?= url('/pages/keywords/' . rawurlencode($aovKw) . '/' . areaSlug($town) . '.php') ?>"
                   class="px-3 py-1.5 bg-white border rounded-full text-xs sm:text-sm text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($aovKwName . ' in ' . $town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach;
        endforeach; ?>
    </div>
    <p class="mt-6 text-sm text-zinc-500">
        Also browse by <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/services/index.php') ?>">service</a>
        or <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/areas/index.php') ?>">area hub</a>.
    </p>
</section>

<!-- KEYWORD GUIDES -->
<section id="keywords" class="bg-zinc-50 border-y scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">SEO guides</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Keyword guides</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">
                    <?= (int)$kwCount ?> topic pages (e.g. EICR, fire alarm servicing) plus local keyword × town landings.
                </p>
            </div>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All keyword guides →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php
            $kwSample = array_slice(getMajorKeywords(), 0, 24, true);
            foreach ($kwSample as $kSlug => $kMeta):
                $kName = is_array($kMeta) ? ($kMeta['name'] ?? $kSlug) : (string)$kMeta;
                ?>
                <a href="<?= url('/pages/keywords/' . rawurlencode($kSlug) . '.php') ?>"
                   class="px-3 py-1.5 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($kName, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="mt-4 text-sm text-zinc-500">
            Example local page:
            <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/keywords/eicr/stockport.php') ?>">EICR in Stockport</a>
            — every keyword also has per-town URLs via the router.
        </p>
        <?php
        $egFeatured = function_exists('getElectricalGasFeaturedKeywordSlugs') ? getElectricalGasFeaturedKeywordSlugs() : [];
        $allKwMap = getMajorKeywords();
        $elecKwN = count(getKeywordsForService('electrical'));
        $gasKwN = count(getKeywordsForService('gas-systems'));
        ?>
        <div id="electrical-gas-keywords" class="mt-12 grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-xl font-semibold text-black">Electrical keyword family</h3>
                <p class="mt-1 text-sm text-zinc-600"><?= (int)$elecKwN ?> guides × <?= count($areas) ?> towns. Rewire, EICR, consumer units and emergency electrician pages.</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php foreach ($egFeatured['electrical'] ?? [] as $egSlug):
                        if (!isset($allKwMap[$egSlug])) { continue; }
                        $egName = $allKwMap[$egSlug]['name'] ?? $egSlug;
                    ?>
                        <a href="<?= url('/pages/keywords/' . rawurlencode($egSlug) . '.php') ?>"
                           class="px-3 py-1.5 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]"><?= htmlspecialchars($egName, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                    <a href="<?= url('/pages/services/electrical.php') ?>" class="px-3 py-1.5 text-sm font-semibold text-[#ff6b00]">Electrical service hub →</a>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-black">Gas keyword family</h3>
                <p class="mt-1 text-sm text-zinc-600"><?= (int)$gasKwN ?> guides × <?= count($areas) ?> towns. Boiler, CP12, gas safety and emergency gas engineer pages.</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php foreach ($egFeatured['gas'] ?? [] as $egSlug):
                        if (!isset($allKwMap[$egSlug])) { continue; }
                        $egName = $allKwMap[$egSlug]['name'] ?? $egSlug;
                    ?>
                        <a href="<?= url('/pages/keywords/' . rawurlencode($egSlug) . '.php') ?>"
                           class="px-3 py-1.5 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]"><?= htmlspecialchars($egName, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                    <a href="<?= url('/pages/services/gas-systems.php') ?>" class="px-3 py-1.5 text-sm font-semibold text-[#ff6b00]">Gas service hub →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- POPULAR AREAS -->
<section id="areas" class="max-w-7xl mx-auto px-6 py-16 md:py-20 scroll-mt-24">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Popular areas</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">
                Key towns we cover — plus <?= count($areas) ?> towns in total across Greater Manchester and the North West.
            </p>
        </div>
        <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All <?= count($areas) ?> towns →</a>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($popularAreas as $area): ?>
            <a href="<?= url('/pages/areas/' . areaSlug($area) . '.php') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                <?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- FEATURED MANUFACTURERS -->
<section id="manufacturers" class="bg-zinc-50 border-y scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Brands</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Featured manufacturers</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Brands we install, service and supply — open a brand page for kits and local CTAs.</p>
            </div>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach ($featuredMfr as $mSlug => $mEntry): ?>
                <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>"
                   class="bg-white border border-zinc-200 rounded-3xl p-5 hover:border-[#ff6b00] hover:shadow-lg transition group">
                    <h3 class="font-semibold text-black group-hover:text-[#ff6b00] transition">
                        <?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>
                    <?php if (!empty($mEntry['blurb'])): ?>
                        <p class="text-sm text-zinc-600 mt-2 line-clamp-2"><?= htmlspecialchars($mEntry['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <span class="inline-block mt-3 text-sm font-semibold text-[#ff6b00]">View brand →</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- RESOURCES -->
<section id="resources" class="max-w-7xl mx-auto px-6 py-16 md:py-20 scroll-mt-24">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Guides</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Resources</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">Articles, checklists, keyword topics and audience landing pages.</p>
        </div>
        <a href="<?= url('/pages/resources/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Resources hub →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($resourceLinks as $link): ?>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="bg-white border border-zinc-200 rounded-3xl p-7 hover:border-[#ff6b00] hover:shadow-lg transition group">
                <h3 class="font-semibold text-xl text-black tracking-tight group-hover:text-[#ff6b00] transition">
                    <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($link['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="inline-block mt-5 text-sm font-semibold text-[#ff6b00]">Open →</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-16">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Next step</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2">Need a free quote?</h2>
                <p class="mt-4 text-white/80 text-lg">
                    Fixed-price quotes for electrical, fire, gas, emergency lighting, CCTV and access control across the North West.
                </p>
            </div>
            <div class="flex flex-wrap gap-3 lg:justify-end">
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                   class="px-6 py-3.5 rounded-2xl bg-white text-[#0B1F3A] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3.5 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="<?= url('/contact.php') ?>"
                   class="px-6 py-3.5 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Contact form</a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
