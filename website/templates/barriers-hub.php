<?php
/**
 * Nationwide vehicle barriers hub.
 * Expects $places, $grouped, $brands, $keywords from renderBarriersHubPage().
 */
$pageTitle = 'Vehicle Barriers | CAME Partner | UK Towns over 10,000';
$metaDesc = 'iComply is a CAME partner for rising-arm vehicle barriers. A page for every UK town over 10,000 people, with that town\'s official population profile. Other barrier brands are serviced, not partnerships. Price on application. ' . PHONE . '.';
$metaKeywords = 'CAME partner, vehicle barrier, rising arm barrier, CAME Gard, UK towns over 10000, barrier servicing, price on application';
$canonicalUrl = url('/pages/services/barriers');
$ogImage = url('/assets/images/services/access-control.jpg');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$placeCount = count($places);
$came = $brands['came'] ?? null;

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => 'Vehicle barriers',
            'serviceType' => 'Rising-arm vehicle barrier installation and servicing',
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'telephone' => PHONE,
                'url' => SITE_URL,
            ],
            'areaServed' => 'United Kingdom mainland towns over 10,000 population',
            'brand' => ['@type' => 'Brand', 'name' => 'CAME'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => url('/pages/services')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => 'Vehicle Barriers', 'item' => $canonicalUrl],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/60 mb-6 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <span class="text-white/80">Vehicle barriers</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">CAME partner</div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05] max-w-4xl">
            Vehicle barriers for every UK town over 10,000
        </h1>
        <p class="mt-6 text-lg text-white/80 max-w-3xl">
            iComply Property Services is a CAME partner. New rising arms are specified from the CAME Gard range.
            <?= number_format($placeCount) ?> town pages sit under this hub. Each one uses that settlement's official Census, NRS or borough count.
            We do not claim a depot in every town. Visits are planned from Stockport, SK2. The quote is on application after the lane is seen.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Price on application</a>
            <a href="<?= url('/pages/manufacturers/came') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">CAME partnership</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-6">
        <div>
            <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold">Partnership</div>
            <p class="mt-2 text-zinc-800">CAME only. Gard 4, Gard 8, and articulated Gard PT / PX where a straight arm will not clear.</p>
        </div>
        <div>
            <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold">Other manufacturers</div>
            <p class="mt-2 text-zinc-800">FAAC, BFT, Nice, Magnetic, ELKA and the rest of the list are equipment we service or replace. Not partners. Not “authorised”.</p>
        </div>
        <div>
            <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold">Where</div>
            <p class="mt-2 text-zinc-800">England and Wales built-up areas, London boroughs, and Scottish localities, each at 10,000 people or more. Private land only.</p>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16" id="came">
    <div class="grid lg:grid-cols-5 gap-10">
        <div class="lg:col-span-3">
            <h2 class="text-3xl font-semibold tracking-tight text-black">CAME partnership</h2>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                The partnership is how Gard barriers, spares and commissioning are specified.
                It is not a claim about FAAC, BFT, Nice, Magnetic Autocontrol or any other name below.
                A short CAME Gard is not forced onto an HGV lane that needs a longer arm.
                Powered gates are a different safety job: BS EN 12453 applies to powered gates, not automatically to a rising-arm barrier.
                Barriers follow the manufacturer's instructions and the site risk assessment.
            </p>
            <div class="mt-6 flex flex-wrap gap-3 text-sm font-semibold">
                <a class="px-4 py-2 rounded-full bg-[#0B1F3A] text-white" href="<?= url('/pages/manufacturers/came') ?>">CAME manufacturer page</a>
                <a class="px-4 py-2 rounded-full border border-zinc-300 hover:border-[#ff6b00]" href="<?= url('/pages/keywords/came-partner') ?>">Partnership guide</a>
                <a class="px-4 py-2 rounded-full border border-zinc-300 hover:border-[#ff6b00]" href="<?= url('/pages/keywords/came-gard-barrier') ?>">Gard 4 and Gard 8</a>
                <a class="px-4 py-2 rounded-full border border-zinc-300 hover:border-[#ff6b00]" href="<?= url('/pages/keywords/came-gard-pt') ?>">Gard PT low headroom</a>
            </div>
        </div>
        <aside class="lg:col-span-2 bg-[#0B1F3A] text-white rounded-3xl p-8">
            <h3 class="text-xl font-semibold">Ask for a lane survey</h3>
            <p class="mt-3 text-white/75 text-sm">Width, headroom, duty and who owns the land. No fee is published here.</p>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="block mt-6 px-5 py-3 bg-white text-[#0B1F3A] rounded-2xl font-semibold text-center"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Vehicle barrier quote') ?>" target="_blank" rel="noopener" class="block mt-3 px-5 py-3 bg-green-600 rounded-2xl font-semibold text-center">WhatsApp</a>
        </aside>
    </div>
</section>

<section class="bg-zinc-50 border-y" id="brands">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black">Barrier manufacturers</h2>
        <p class="mt-3 text-zinc-700 max-w-3xl">Every brand linked here has its own page. CAME is marked as the partner. The others are service and replacement.</p>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($brands as $slug => $entry): ?>
                <a href="<?= url('/pages/manufacturers/' . $slug) ?>" class="block bg-white border rounded-2xl p-5 hover:border-[#ff6b00]">
                    <div class="font-semibold text-black"><?= htmlspecialchars((string)$entry['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($entry['partner'])): ?>
                        <div class="text-xs font-bold uppercase tracking-wide text-[#ff6b00] mt-1">CAME partner</div>
                    <?php else: ?>
                        <div class="text-xs text-zinc-500 mt-1">Service and replacement</div>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16" id="guides">
    <h2 class="text-3xl font-semibold tracking-tight text-black">Barrier guides</h2>
    <p class="mt-3 text-zinc-700 max-w-3xl">National articles. They are not copied once per town.</p>
    <ul class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach ($keywords as $kwSlug => $meta): ?>
            <li>
                <a class="block px-4 py-3 bg-zinc-50 border rounded-2xl hover:border-[#ff6b00] font-medium" href="<?= url('/pages/keywords/' . $kwSlug) ?>">
                    <?= htmlspecialchars((string)($meta['name'] ?? $kwSlug), ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="bg-white border-t" id="towns">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black"><?= number_format($placeCount) ?> town pages</h2>
        <p class="mt-3 text-zinc-700 max-w-3xl">Open a town for its own population, age and housing or industry figures. The page does not invent a business park, a hospital or a local engineer for that town.</p>
        <div class="mt-8 space-y-4">
            <?php foreach ($grouped as $country => $regions): ?>
                <details class="border rounded-2xl bg-zinc-50" <?= $country === 'England' ? 'open' : '' ?>>
                    <summary class="cursor-pointer px-5 py-4 font-semibold text-[#061828]"><?= htmlspecialchars($country, ENT_QUOTES, 'UTF-8') ?></summary>
                    <div class="px-5 pb-5 space-y-6">
                        <?php foreach ($regions as $region => $regionPlaces): ?>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wide text-zinc-500"><?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <?php foreach ($regionPlaces as $row): ?>
                                        <a class="px-3 py-1.5 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/barriers/' . $row['slug']) ?>">
                                            <?= htmlspecialchars((string)$row['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="bg-zinc-50 border-y" id="method">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black">How the town list was built</h2>
        <div class="mt-4 space-y-3 text-zinc-700 max-w-3xl leading-relaxed">
            <p>England and Wales use ONS Census 2021 built-up areas with at least 10,000 usual residents. Greater London is not split into those built-up areas, so the 32 London boroughs at or above 10,000 are listed separately. The City of London is under that threshold and is not listed.</p>
            <p>Scotland uses NRS mid-2020 localities of at least 10,000. Isle of Wight settlements are excluded because this service is mainland. Scottish island councils are excluded on the same basis. Northern Ireland is not in these three sources, so it is not in the list.</p>
            <p>ONS counts are rounded to the nearest five, and cells under ten can be suppressed. A town page is that settlement's published profile plus a barrier survey from Stockport. It is not a claim that a barrier is already installed there.</p>
        </div>
    </div>
</section>

<section id="quote" class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <?php
        $services = getServices();
        $selectedService = 'Vehicle Barriers';
        $heading = 'Barrier quote on application';
        $sub = 'Postcode, lane width if you know it, and the brand on the cabinet. No catalogue price.';
        require SITE_ROOT . '/includes/quote-form.php';
        ?>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
