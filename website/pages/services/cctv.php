<?php 
/**
 * AUTO-GENERATED — php bin/generate-service-pages.php
 * Rank-focused service hub page
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/seo.php';
require_once __DIR__ . '/../../includes/local-content.php';
$serviceSlug = 'cctv';
$serviceName = 'CCTV Systems';
$pageTitle = 'CCTV Systems North West | Install & Service';
$metaDesc = 'CCTV Systems across Greater Manchester & North West UK. Install, service & certification. Stockport engineers. Free quote.';
$metaKeywords = 'CCTV installation, IP CCTV system, commercial CCTV, video surveillance, CCTV Systems Manchester, CCTV Systems Stockport, CCTV Systems North West';
$ogImage = service_image($serviceSlug);
$canonicalUrl = site_url("pages/services/{$serviceSlug}");
require __DIR__ . '/../../includes/header.php';
$features = service_features($serviceSlug);
$faqs = service_faqs($serviceSlug, $serviceName, '');
$standards = service_standards($serviceSlug);
// Prioritise major commercial towns for internal linking equity
$priority = ['Manchester','Stockport','Bolton','Salford','Oldham','Rochdale','Liverpool','Preston','Blackpool','Chester','Warrington','Wigan','Burnley','Blackburn','Macclesfield','Crewe','Lancaster','Bury','St Helens','Southport','Chorley','Wilmslow','Altrincham','Sale'];
$topAreas = array_values(array_unique(array_merge($priority, array_slice($GLOBALS['areas'], 0, 40))));
$crumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Services', 'url' => '/pages/services/index'],
    ['name' => $serviceName, 'url' => "/pages/services/{$serviceSlug}"],
];
?>
<section class="relative text-white py-16 md:py-20 overflow-hidden">
    <img src="assets/images/services/cctv-photo.jpg" alt="CCTV Systems services across Greater Manchester and North West UK" class="absolute inset-0 w-full h-full object-cover" width="1600" height="700" fetchpriority="high" onerror="this.src='assets/images/services/cctv.png'">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative max-w-5xl mx-auto px-6">
        <?= render_breadcrumbs($crumbs) ?>
        <div class="text-sm uppercase tracking-[3px] text-[#ff6b00] mb-3">UK PROPERTY COMPLIANCE · NORTH WEST</div>
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight">CCTV Systems services</h1>
        <p class="mt-4 max-w-2xl text-lg text-white/90">Expert CCTV Systems installation, servicing, maintenance and certification for landlords, agents and businesses across Manchester, Stockport, Bolton, Liverpool, Preston and 140+ North West towns.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/contact" class="accent-btn px-8 py-3 rounded-2xl font-semibold">Free quote</a>
            <a href="tel:<?= PHONE ?>" class="px-8 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= PHONE ?></a>
            <a href="#areas" class="px-8 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Areas covered</a>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 py-14">
    <h2 class="text-3xl font-extrabold tracking-tight">What our CCTV Systems service includes</h2>
    <p class="mt-4 text-zinc-700 leading-relaxed text-lg"><?= htmlspecialchars(service_blurb($serviceSlug)) ?> Icomply Property Services is based in Offerton, Stockport (SK2) and delivers UK-standard workmanship with documentation you can pass to insurers and freeholders.</p>
    <p class="mt-3 text-zinc-700 leading-relaxed">From Manchester and Stockport to Liverpool, Preston, Blackpool, Chester and Warrington, we schedule CCTV Systems around access windows that work for landlords and FM teams. Standards we align to include <?= htmlspecialchars(implode(', ', service_standards($serviceSlug))) ?>.</p>
    <p class="mt-3 text-zinc-700 leading-relaxed"><?= htmlspecialchars(service_local_angle($serviceSlug, $serviceName, 'Manchester')) ?> Similar patterns appear across the wider North West portfolio we support.</p>

    <div class="mt-6 flex flex-wrap gap-2">
        <?php foreach ($standards as $s): ?>
            <span class="px-3 py-1.5 rounded-full bg-[#0a2540]/5 text-[#0a2540] text-xs font-semibold border"><?= htmlspecialchars($s) ?></span>
        <?php endforeach; ?>
    </div>

    <div class="mt-10 grid md:grid-cols-3 gap-6">
        <?php foreach ($features as $f): ?>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($f['title']) ?></h3>
            <p class="text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($f['text']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-14 rounded-3xl overflow-hidden border grid md:grid-cols-2 bg-white">
        <img src="assets/images/services/cctv.png" alt="CCTV Systems UK compliance package" class="w-full h-64 md:h-full object-contain bg-zinc-50 p-8" loading="lazy" width="600" height="400">
        <div class="p-8 md:p-10 flex flex-col justify-center">
            <h2 class="text-2xl font-extrabold">Why North West clients choose Icomply</h2>
            <ul class="mt-4 space-y-2 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Stockport-based team covering Greater Manchester &amp; the North West</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Clear scope and fixed-price quotes where possible</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Certificates and reports suitable for landlords &amp; insurers</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> One contractor for multi-discipline compliance works</li>
            </ul>
            <a href="/contact" class="inline-block mt-6 modern-btn px-6 py-3 rounded-xl font-semibold w-max">Book CCTV Systems</a>
        </div>
    </div>

    <?= render_faq_section($faqs, "FAQs about {$serviceName}") ?>

    <div class="mt-16" id="areas">
        <h2 class="font-extrabold text-2xl mb-2">CCTV Systems by town</h2>
        <p class="text-zinc-600 text-sm mb-5">Jump to a local landing page optimised for CCTV Systems in that area.</p>
        <div class="flex flex-wrap gap-2 text-sm">
            <?php foreach ($topAreas as $a): ?>
                <a href="/pages/cctv/<?= areaSlug($a) ?>" class="px-4 py-1.5 bg-white border rounded-full hover:border-[#ff6b00]"><?= htmlspecialchars($a) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mt-14 bg-zinc-900 text-white p-10 rounded-3xl text-center">
        <h2 class="text-2xl font-bold">Need CCTV Systems this week?</h2>
        <p class="mt-2 text-white/80">Get a free quote — typical response within 2 business hours.</p>
        <a href="/contact" class="inline-block mt-6 accent-btn px-10 py-4 rounded-2xl font-semibold">Request free quote</a>
    </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $serviceName,
    'serviceType' => $serviceName,
    'description' => $metaDesc,
    'url' => site_url("pages/services/{$serviceSlug}"),
    'image' => service_image($serviceSlug),
    'provider' => ['@id' => site_url() . '#business'],
    'areaServed' => 'North West England',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
