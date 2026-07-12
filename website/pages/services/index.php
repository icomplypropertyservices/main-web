<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/seo.php';
$pageTitle = 'Compliance Services North West | EICR & Fire';
$metaDesc = 'UK property compliance services: EICR, fire alarms, gas, emergency lighting, CCTV & more across Greater Manchester & North West. Free quote.';
$metaKeywords = 'property compliance services Manchester, EICR, fire alarms, emergency lighting, gas safety, nurse call, CCTV, access control, door entry';
$canonicalUrl = site_url('pages/services/index.php');
require '../../includes/header.php';
$hubFaqs = [
    ['q' => 'Which compliance service do I need?', 'a' => 'It depends on your property type and legal duties. Landlords often need EICR and gas safety; commercial sites commonly need fire alarms, emergency lighting and sometimes AOV or access control. Tell us the building use and we will recommend the right package.'],
    ['q' => 'Do you cover the whole North West?', 'a' => 'Yes. From our Stockport base we cover Greater Manchester and 150+ towns including Liverpool, Preston, Blackpool, Chester and Warrington.'],
    ['q' => 'Can one contractor handle multiple systems?', 'a' => 'Yes. Icomply coordinates electrical, fire, gas, emergency lighting, nurse call, CCTV and access works so you deal with one UK compliance partner.'],
];
?>
<section class="relative text-white py-20 overflow-hidden">
    <img src="assets/images/heroes/home-hero.jpg" alt="UK property compliance services across the North West" class="absolute inset-0 w-full h-full object-cover" width="1600" height="700" fetchpriority="high">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative max-w-6xl mx-auto px-6 text-center">
        <div class="uppercase text-[#ff6b00] tracking-[.2em] text-xs font-semibold mb-3">UNITED KINGDOM · NORTH WEST</div>
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight">Property compliance services North West</h1>
        <p class="mt-4 text-lg text-white/85 max-w-2xl mx-auto">Expert installation, maintenance and certification for every major building compliance requirement across Greater Manchester and the North West.</p>
    </div>
</section>

<section class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-extrabold mb-8">Browse all 11 compliance services</h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($GLOBALS['services'] as $slug => $name): ?>
        <a href="<?= htmlspecialchars($slug) ?>.php" class="service-card bg-white rounded-3xl border group">
            <div class="aspect-[16/10] overflow-hidden bg-zinc-100">
                <img src="assets/images/services/<?= htmlspecialchars($slug) ?>-photo.jpg"
                     alt="<?= htmlspecialchars($name) ?> services North West UK"
                     class="img-cover group-hover:scale-105 transition duration-500"
                     width="640" height="400" loading="lazy"
                     onerror="this.src='assets/images/services/<?= htmlspecialchars($slug) ?>.png'">
            </div>
            <div class="p-7">
                <h3 class="font-bold text-2xl tracking-tight mb-2"><?= htmlspecialchars($name) ?></h3>
                <p class="text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars(service_blurb($slug)) ?></p>
                <div class="mt-4 text-sm font-semibold text-[#ff6b00]">View service and areas →</div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?= render_faq_section($hubFaqs, 'FAQs about our compliance services') ?>
    
    <div class="mt-16 text-center">
        <a href="index.php#quote" class="accent-btn inline-block px-10 py-4 rounded-2xl font-semibold">Get a quote for any service</a>
    </div>
</section>
<?php require '../../includes/footer.php'; ?>