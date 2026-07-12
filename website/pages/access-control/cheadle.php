<?php
/**
 * AUTO-GENERATED — php bin/generate-site.php
 * Maximum local SEO landing: unique area copy + full schema stack
 */
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/seo.php';
require_once __DIR__ . '/../../includes/local-content.php';

$serviceSlug = 'access-control';
$serviceName = 'Access Control';
$areaName = 'Cheadle';
$areaSlugVal = 'cheadle';
$pageTitle = 'Access Control in Cheadle | UK Install & Cert';
$metaDesc = 'Access Control in Cheadle — install, service & certificates. Stockport UK engineers covering Cheadle. Free quote.';
$metaKeywords = 'access control installation, biometric access control, door access control Cheadle, Cheadle renewable energy, Cheadle electrician, Access Control Cheadle, Cheadle Access Control engineer, Cheadle compliance';
$ogImage = service_image($serviceSlug);
$canonicalUrl = site_url("pages/{$serviceSlug}/{$areaSlugVal}");
$ogType = 'article';
require __DIR__ . '/../../includes/header.php';

$profile = area_profile($areaName);
$faqs = array_merge(
    service_faqs($serviceSlug, $serviceName, $areaName),
    seo_extra_faqs($serviceSlug, $serviceName, $areaName)
);
$standards = service_standards($serviceSlug);
$process = seo_combo_process($serviceName, $areaName);
$why = seo_unique_why($serviceName, $areaName);
$localBullets = seo_unique_local_block($serviceName, $serviceSlug, $areaName);
$nearby = nearby_areas($areaName, 16);
$intro = seo_unique_intro($serviceName, $serviceSlug, $areaName);
$angle = service_local_angle($serviceSlug, $serviceName, $areaName);
$crumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Services', 'url' => '/pages/services/index'],
    ['name' => $serviceName, 'url' => "/pages/services/{$serviceSlug}"],
    ['name' => $areaName, 'url' => "/pages/{$serviceSlug}/{$areaSlugVal}"],
];
$features = service_features($serviceSlug);
?>
<section class="relative text-white py-16 md:py-20 overflow-hidden">
    <img src="assets/images/services/access-control-photo.jpg"
         alt="Access Control in Cheadle — UK property compliance engineers"
         class="absolute inset-0 w-full h-full object-cover" width="1600" height="700" fetchpriority="high"
         onerror="this.src='assets/images/services/access-control.png'">
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative max-w-5xl mx-auto px-6">
        <div class="text-white/90 text-sm mb-4"><?= render_breadcrumbs($crumbs) ?></div>
        <p class="text-xs uppercase tracking-[.2em] text-[#ff6b00] font-semibold mb-3">Access Control · Cheadle · <?= htmlspecialchars($profile['region']) ?></p>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">Access Control in Cheadle</h1>
        <p class="mt-4 max-w-2xl text-lg text-white/90">Install · service · test · certificate for landlords, agents and businesses in Cheadle (<?= htmlspecialchars($profile['districts']) ?>).</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/contact" class="accent-btn px-8 py-3 rounded-2xl font-semibold">Free Cheadle quote</a>
            <a href="tel:<?= PHONE ?>" class="px-8 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= PHONE ?></a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Access Control%20in%20Cheadle" class="px-8 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 py-14">
    <article itemscope itemtype="https://schema.org/Service">
        <meta itemprop="name" content="Access Control in Cheadle">
        <meta itemprop="serviceType" content="Access Control">

        <h2 class="text-3xl font-extrabold tracking-tight">Access Control specialists covering Cheadle</h2>
        <p class="mt-4 text-zinc-700 leading-relaxed text-lg" itemprop="description"><?= htmlspecialchars($intro) ?></p>
        <p class="mt-4 text-zinc-700 leading-relaxed"><?= htmlspecialchars($angle) ?> Icomply Property Services is based at <?= ADDRESS ?> and documents work to UK practice including <?= htmlspecialchars(implode(', ', array_slice($standards, 0, 4))) ?>.</p>

        <div class="mt-8 flex flex-wrap gap-2" aria-label="Standards and keywords">
            <?php foreach ($standards as $s): ?>
                <span class="px-3 py-1.5 rounded-full bg-[#0a2540]/5 text-[#0a2540] text-xs font-semibold border border-[#0a2540]/10"><?= htmlspecialchars($s) ?></span>
            <?php endforeach; ?>
            <span class="px-3 py-1.5 rounded-full bg-[#ff6b00]/10 text-[#ff6b00] text-xs font-semibold">Cheadle</span>
            <span class="px-3 py-1.5 rounded-full bg-zinc-100 text-zinc-700 text-xs font-semibold"><?= htmlspecialchars($profile['districts']) ?></span>
        </div>

        <h2 class="mt-14 text-2xl font-extrabold">Access Control for Cheadle property types</h2>
        <div class="mt-4 grid md:grid-cols-2 gap-6">
            <div class="bg-white border rounded-3xl p-7">
                <h3 class="font-bold text-lg mb-3">Local building stock</h3>
                <ul class="space-y-2 text-sm text-zinc-700">
                    <?php foreach ($localBullets as $b): ?>
                        <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span><span><?= htmlspecialchars($b) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="bg-white border rounded-3xl p-7">
                <h3 class="font-bold text-lg mb-3">What we deliver</h3>
                <ul class="space-y-3 text-sm text-zinc-700">
                    <?php foreach ($features as $f): ?>
                        <li><strong class="text-zinc-900"><?= htmlspecialchars($f['title']) ?>:</strong> <?= htmlspecialchars($f['text']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <h2 class="mt-14 text-2xl font-extrabold">Access Control installation, service and certification in Cheadle</h2>
        <div class="mt-6 grid md:grid-cols-3 gap-6">
            <div class="p-7 bg-white rounded-3xl border">
                <h3 class="font-bold text-lg mb-2">Installation in Cheadle</h3>
                <p class="text-sm text-zinc-600 leading-relaxed">Design, supply and install matched to your fire strategy, electrical design or security brief for Cheadle homes and commercial sites.</p>
            </div>
            <div class="p-7 bg-white rounded-3xl border">
                <h3 class="font-bold text-lg mb-2">Maintenance in Cheadle</h3>
                <p class="text-sm text-zinc-600 leading-relaxed">Planned visits and reactive repairs so Cheadle systems stay reliable between formal inspections and insurer reviews.</p>
            </div>
            <div class="p-7 bg-white rounded-3xl border">
                <h3 class="font-bold text-lg mb-2">Testing &amp; certificates</h3>
                <p class="text-sm text-zinc-600 leading-relaxed">Documented results and certificates landlords, freeholders and insurers expect for Access Control in Cheadle.</p>
            </div>
        </div>

        <h2 class="mt-14 text-2xl font-extrabold">Why Cheadle clients choose Icomply</h2>
        <ul class="mt-4 space-y-3">
            <?php foreach ($why as $item): ?>
                <li class="flex gap-3 text-zinc-700"><span class="text-[#ff6b00] font-bold">✓</span><span><?= htmlspecialchars($item) ?></span></li>
            <?php endforeach; ?>
        </ul>

        <h2 class="mt-14 text-2xl font-extrabold">How booking Access Control in Cheadle works</h2>
        <ol class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 list-none p-0">
            <?php foreach ($process as $p): ?>
            <li class="bg-white border rounded-2xl p-5">
                <div class="w-9 h-9 rounded-full bg-[#0a2540] text-white flex items-center justify-center font-bold text-sm mb-3"><?= htmlspecialchars($p['step']) ?></div>
                <h3 class="font-bold"><?= htmlspecialchars($p['title']) ?></h3>
                <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($p['text']) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>

        <div class="mt-14 grid md:grid-cols-2 gap-8 items-center bg-white border rounded-3xl overflow-hidden">
            <img src="assets/images/services/access-control.png"
                 alt="Access Control compliance package for Cheadle UK properties"
                 class="w-full h-64 object-contain bg-zinc-50 p-8" loading="lazy" width="600" height="400">
            <div class="p-8">
                <h2 class="text-2xl font-extrabold">Who we help in Cheadle</h2>
                <p class="mt-3 text-zinc-600 text-sm leading-relaxed">Landlords, letting agents, RTM companies, care providers, retailers, offices, industrial units and public-sector sites needing dependable Access Control across <?= htmlspecialchars($profile['districts']) ?> with clear paperwork and local attendance.</p>
                <p class="mt-3 text-zinc-600 text-sm leading-relaxed">Tell us storeys, occupancy, existing equipment and access windows — we scope Access Control for real Cheadle conditions, not a generic checklist alone.</p>
                <a href="/contact" class="inline-block mt-6 accent-btn px-6 py-3 rounded-xl font-semibold">Request Cheadle quote</a>
            </div>
        </div>

        <h2 class="mt-14 text-2xl font-extrabold">Compliance notes for Cheadle managers</h2>
        <p class="mt-3 text-zinc-700 leading-relaxed text-sm md:text-base">Keep a simple evidence pack for each Cheadle site: latest certificates, service sheets, device lists and any remedial schedule. When insurers, freeholders or local authority officers ask for proof of Access Control, organised documents reduce delays. If you manage multiple units in <?= htmlspecialchars($profile['region']) ?>, we can align visit cycles so related systems (for example fire alarms and emergency lighting) are serviced efficiently.</p>

        <?= render_faq_section($faqs, "FAQs: {$serviceName} in {$areaName}") ?>

        <div class="mt-14 bg-zinc-900 text-white p-10 rounded-3xl">
            <h2 class="text-2xl font-bold mb-3">Book Access Control in Cheadle today</h2>
            <p class="text-white/80 mb-6">Speak to a UK compliance specialist. Travel to Cheadle is <?= htmlspecialchars($profile['travel']) ?>.</p>
            <div class="flex flex-wrap gap-3">
                <a href="/contact" class="inline-block bg-[#ff6b00] px-8 py-3 rounded-2xl font-semibold">Free quote</a>
                <a href="tel:<?= PHONE ?>" class="inline-block bg-white text-[#0a2540] px-8 py-3 rounded-2xl font-semibold"><?= PHONE ?></a>
                <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Access Control%20in%20Cheadle" class="inline-block border border-white/40 px-8 py-3 rounded-2xl font-semibold">WhatsApp</a>
            </div>
            <p class="mt-6 text-xs text-white/50">Icomply Property Services · <?= ADDRESS ?> · <?= EMAIL ?></p>
        </div>
    </article>

    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => "Access Control in Cheadle",
        'serviceType' => 'Access Control',
        'description' => $metaDesc,
        'url' => site_url("pages/{$serviceSlug}/{$areaSlugVal}"),
        'image' => service_image($serviceSlug),
        'provider' => ['@id' => site_url() . '#business'],
        'areaServed' => [
            '@type' => 'City',
            'name' => 'Cheadle',
            'containedInPlace' => ['@type' => 'AdministrativeArea', 'name' => $profile['region']],
        ],
        'offers' => [
            '@type' => 'Offer',
            'availability' => 'https://schema.org/InStock',
            'priceCurrency' => 'GBP',
            'url' => site_url('contact.php'),
            'description' => "Free quote for {$serviceName} in {$areaName}",
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    <script type="application/ld+json"><?= json_encode(howto_schema($serviceName, $areaName), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

    <div class="mt-16">
        <h2 class="font-bold text-xl mb-4">Other compliance services in Cheadle</h2>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($GLOBALS['services'] as $sSlug => $sName): if ($sSlug === $serviceSlug) continue; ?>
                <a href="/pages/<?= htmlspecialchars($sSlug) ?>/<?= areaSlug($areaName) ?>" class="service-card bg-white border rounded-2xl overflow-hidden group">
                    <div class="h-28 overflow-hidden">
                        <img src="assets/images/services/<?= htmlspecialchars($sSlug) ?>-photo.jpg"
                             alt="<?= htmlspecialchars($sName) ?> in Cheadle"
                             class="img-cover group-hover:scale-105 transition duration-500" loading="lazy" width="400" height="160"
                             onerror="this.src='assets/images/services/<?= htmlspecialchars($sSlug) ?>.png'">
                    </div>
                    <div class="p-4 font-semibold text-sm"><?= htmlspecialchars($sName) ?> in Cheadle</div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="font-bold text-xl mb-4">Access Control near Cheadle</h2>
        <div class="flex flex-wrap gap-2 text-sm">
            <?php foreach ($nearby as $a): ?>
                <a href="/pages/access-control/<?= areaSlug($a) ?>" class="px-4 py-1.5 bg-white border rounded-full hover:border-[#ff6b00]"><?= htmlspecialchars($a) ?> Access Control</a>
            <?php endforeach; ?>
        </div>
        <p class="mt-6 text-sm text-zinc-500">
            <a class="text-[#ff6b00] font-semibold hover:underline" href="/pages/services/access-control">All Access Control services</a>
            · <a class="text-[#ff6b00] font-semibold hover:underline" href="/pages/areas/<?= areaSlug($areaName) ?>">All compliance in Cheadle</a>
            · <a class="text-[#ff6b00] font-semibold hover:underline" href="/contact">Free quote</a>
        </p>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
