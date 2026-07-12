<?php
/**
 * Maximum SEO area hub — unique local profile + all services
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/seo.php';
require_once __DIR__ . '/../../includes/local-content.php';
$areaName = 'Formby';
$profile = area_profile($areaName);
$pageTitle = 'Formby Property Compliance | EICR & Fire';
$metaDesc = 'Formby (' . $profile['districts'] . ') compliance: EICR, fire, gas, emergency lighting, CCTV. Free quote from Stockport engineers.';
if (mb_strlen($metaDesc) > 158) {
    $metaDesc = 'Formby property compliance: EICR, fire alarms, gas, emergency lighting & CCTV. North West engineers. Free quote.';
}
$metaKeywords = 'Formby electrician, Formby fire alarm, Formby EICR, Formby gas safety, property compliance Formby';
$canonicalUrl = site_url('pages/areas/' . areaSlug($areaName) . '.php');
require __DIR__ . '/../../includes/header.php';
$crumbs = [
    ['name' => 'Home', 'url' => 'index.php'],
    ['name' => 'Services', 'url' => 'pages/services/index.php'],
    ['name' => $areaName, 'url' => 'pages/areas/' . areaSlug($areaName) . '.php'],
];
$faqs = [
    ['q' => "What compliance services are available in {$areaName}?", 'a' => "Electrical (including EICR), fire alarms, emergency lighting, AOV, nurse call, gas systems, intruder alarms, CCTV, access control, door entry and intercoms across {$areaName} and {$profile['districts']}."],
    ['q' => "How far is {$areaName} from your engineers?", 'a' => "We are based in Stockport SK2. Travel to {$areaName} is {$profile['travel']}."],
    ['q' => "What building types do you cover in {$areaName}?", 'a' => "Typical stock includes {$profile['stock']}. Primary focus: {$profile['focus']}."],
];
?>
<section class="relative text-white py-16 md:py-20 overflow-hidden">
    <img src="assets/images/heroes/manchester.jpg" alt="Property compliance in Formby, <?= htmlspecialchars($profile['region']) ?> UK" class="absolute inset-0 w-full h-full object-cover" width="1600" height="700" fetchpriority="high">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative max-w-5xl mx-auto px-6">
        <div class="text-white/90 text-sm mb-4"><?= render_breadcrumbs($crumbs) ?></div>
        <p class="text-xs uppercase tracking-[.2em] text-[#ff6b00] font-semibold mb-3"><?= htmlspecialchars($profile['region']) ?> · <?= htmlspecialchars($profile['districts']) ?></p>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">Formby property compliance</h1>
        <p class="mt-4 max-w-2xl text-lg text-white/90">Local UK engineers for Formby — electrical, fire, gas, emergency lighting, security and more. Certificates landlords and insurers accept.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="contact.php" class="accent-btn px-8 py-3 rounded-2xl font-semibold">Get a Formby quote</a>
            <a href="tel:<?= PHONE ?>" class="px-8 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= PHONE ?></a>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 py-14">
    <h2 class="text-3xl font-extrabold tracking-tight">Compliance services for Formby properties</h2>
    <p class="mt-4 text-zinc-700 leading-relaxed">Icomply Property Services supports landlords, managing agents and businesses in Formby (<?= htmlspecialchars($profile['districts']) ?>). Local stock typically includes <?= htmlspecialchars($profile['stock']) ?>. Travel from Stockport is <?= htmlspecialchars($profile['travel']) ?>. Focus areas: <?= htmlspecialchars($profile['focus']) ?>.</p>

    <div class="mt-10 grid sm:grid-cols-2 gap-5">
        <?php foreach ($GLOBALS['services'] as $sSlug => $sName): ?>
            <a href="pages/<?= htmlspecialchars($sSlug) ?>/<?= areaSlug($areaName) ?>.php" class="service-card bg-white border rounded-2xl overflow-hidden flex group">
                <div class="w-28 shrink-0 overflow-hidden">
                    <img src="assets/images/services/<?= htmlspecialchars($sSlug) ?>-photo.jpg" alt="<?= htmlspecialchars($sName) ?> in Formby" class="img-cover h-full min-h-[7rem] group-hover:scale-105 transition duration-500" loading="lazy" width="160" height="120" onerror="this.src='assets/images/services/<?= htmlspecialchars($sSlug) ?>.png'">
                </div>
                <div class="p-5 flex-1 flex flex-col justify-center">
                    <span class="font-bold"><?= htmlspecialchars($sName) ?> in Formby</span>
                    <span class="text-xs text-zinc-500 mt-1"><?= htmlspecialchars(service_blurb($sSlug)) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mt-14 bg-white border rounded-3xl p-8">
        <h2 class="text-xl font-extrabold">Why book Icomply in Formby?</h2>
        <ul class="mt-4 space-y-2 text-sm text-zinc-700">
            <?php foreach (seo_unique_why('property compliance', $areaName) as $item): ?>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?= render_faq_section($faqs, "FAQs about compliance in {$areaName}") ?>

    <div class="mt-12 text-center">
        <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Formby" class="inline-block px-10 py-4 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-semibold">WhatsApp for a Formby quote</a>
        <p class="mt-4 text-sm text-zinc-500">Call <a class="text-[#ff6b00] font-semibold" href="tel:<?= PHONE ?>"><?= PHONE ?></a> · <a class="text-[#ff6b00] font-semibold" href="contact.php">contact form</a> · <?= ADDRESS ?></p>
    </div>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Property compliance services in Formby',
    'description' => $metaDesc,
    'provider' => ['@id' => site_url() . '#business'],
    'areaServed' => ['@type' => 'City', 'name' => 'Formby'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
