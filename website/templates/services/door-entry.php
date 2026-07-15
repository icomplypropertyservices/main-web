<?php 
/**
 * Door Entry Systems — 3 images · 3 paragraphs · manufacturers · SEO brand alts
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'Door entry installation in {{AREA}}. Videx, Fermax and Aiphone video/audio panels — multi-tenant, GSM and IP systems.';
$metaKeywords = 'door entry installation {{AREA}}, Videx door entry {{AREA}}, Fermax panel {{AREA}}, Aiphone video door entry {{AREA}}, apartment door entry {{AREA}}, BS EN 50133 {{AREA}}';
$ogImage = url('/assets/images/services/door-entry.jpg');
require SITE_ROOT . '/includes/header.php'; 
?>
<!-- Schema.org Service + FAQPage Markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Service",
      "@id": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>#service",
      "name": "{{SERVICE_NAME}} in {{AREA}}",
      "description": "Video and audio door entry systems with Videx, Fermax and Aiphone panels in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/door-entry.jpg') ?>",
      "serviceType": "Door Entry Systems",
      "category": "Door Entry Systems",
      "provider": {
        "@type": "LocalBusiness",
        "@id": "<?= rtrim(SITE_URL, '/') ?>/#business",
        "name": <?= json_encode(SITE_NAME) ?>,
        "url": <?= json_encode(SITE_URL) ?>,
        "telephone": <?= json_encode(PHONE) ?>,
        "email": <?= json_encode(EMAIL) ?>,
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "17 Woodlands Park Road",
          "addressLocality": "Offerton, Stockport",
          "addressRegion": "Greater Manchester",
          "postalCode": "SK2 5DE",
          "addressCountry": "GB"
        },
        "priceRange": "££"
      },
      "areaServed": {"@type": "City", "name": "{{AREA}}"},
      "offers": {
        "@type": "Offer",
        "name": "Free fixed-price quote — {{SERVICE_NAME}} in {{AREA}}",
        "availability": "https://schema.org/InStock",
        "priceCurrency": "GBP",
        "url": "<?= url('/contact.php') ?>"
      },
      "brand": {"@type": "Brand", "name": <?= json_encode(SITE_NAME) ?>}
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {"@type": "Question", "name": "Do you install video door entry systems?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we specialise in both audio and video door entry solutions for multi-tenant properties."}},
        {"@type": "Question", "name": "Can door entry link to smartphones?", "acceptedAnswer": {"@type": "Answer", "text": "Modern systems allow remote access via mobile apps."}},
        {"@type": "Question", "name": "What is the typical lifespan of door entry equipment?", "acceptedAnswer": {"@type": "Answer", "text": "With regular maintenance, systems last 10+ years."}},
        {"@type": "Question", "name": "Which door entry brands do you install?", "acceptedAnswer": {"@type": "Answer", "text": "We install and service Videx, Fermax, BPT, Comelit, Aiphone, Paxton, Door Entry Direct, Urmet, Elvox and Golmar."}}
      ]
    }
  ]
}
</script>
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-sm uppercase tracking-[3px] text-[#ff6b00] mb-2">SECURITY SYSTEMS • {{AREA}}</div>
    <h1 class="text-5xl md:text-6xl font-semibold tracking-tighter text-black">{{SERVICE_NAME}} in {{AREA}}</h1>

    <!-- IMAGE 1: Hero -->
    <div class="mt-8">
        <img src="<?= url('/assets/images/services/door-entry.jpg') ?>"
             alt="Videx Fermax Aiphone video door entry panel and handset installation in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional {{SERVICE_NAME}} equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services provides complete audio and video <strong>{{SERVICE_NAME}}</strong> design, installation and maintenance for residential, multi-tenant and commercial properties across <strong>{{AREA}}</strong> and the wider North West. We specialise in <strong>Videx</strong>, <strong>Fermax</strong> and <strong>Aiphone</strong> entrance panels with colour video, GSM/4G and IP smartphone unlock.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you manage an apartment block, office reception, gated development or care facility in {{AREA}}, we supply outdoor panels, flat handsets, trades buttons, electric strikes and maglocks. Systems integrate with Paxton access control, CCTV and fire free-exit so visitors are verified before entry without compromising life safety.
    </p>

    <!-- IMAGE 2 + 3 grid -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/video-door-entry.jpg') ?>"
                 alt="Videx and Fermax video door entry panel with camera installed by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/door-entry.jpg') ?>'">
            <p class="text-xs text-black mt-2">Video door entry panels — Videx, Fermax &amp; Aiphone</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/audio-door-entry.jpg') ?>"
                 alt="Aiphone audio door entry handset and entrance panel system testing in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/door-entry.jpg') ?>'">
            <p class="text-xs text-black mt-2">Audio handsets, monitors &amp; release wiring in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a <strong>Videx door entry</strong>, <strong>Fermax panel</strong> or <strong>Aiphone video system</strong> engineer near you? We install, service and replace major door entry brands listed below. If you already have an entrance panel or handset network on site in {{AREA}}, we can repair faults, upgrade to IP/video and supply matching certificates.
    </p>

    <!-- Full manufacturer list -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Panels We Support</h2>
        <p class="text-black mb-6">We work with leading door entry manufacturers so customers searching for their exact panel or brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('door-entry') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('door-entry') ?>
        </div>
    </div>

    <!-- IMAGE 3: Brand focus -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/door-entry-system.jpg') ?>"
             alt="Videx Fermax Aiphone door entry panels handsets and electric release systems — Icomply {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/door-entry.jpg') ?>'">
        <p class="text-xs text-black mt-2">Manufacturer panels &amp; multi-tenant systems commonly installed and serviced in {{AREA}}</p>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Relevant Standards</h2>
        <p class="text-sm text-black">BS EN 50133 • BS EN 62642 • EN 50131-1 • BS 8243 • BS EN 50132 • BS 5839 (integrated fire systems)</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Videx, Fermax and Aiphone audio/video panel design for apartments, offices and gated sites in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Upgrades</h3>
            <p class="text-sm text-black">Panel repairs, monitor replacements, GSM module swaps and full multi-tenant expansions.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Smart Integration</h3>
            <p class="text-sm text-black">Smartphone unlock apps, CCTV overlay, Paxton access and fire-safe door release integration.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Related Services in {{AREA}}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/intercoms.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Intercoms</a>
            <a href="<?= url('/pages/services/access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Access Control</a>
            <a href="<?= url('/pages/services/cctv.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">CCTV Systems</a>
            <a href="<?= url('/pages/services/intruder-alarm.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Intruder Alarms</a>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need {{SERVICE_NAME}} in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your panel brand — Videx, Fermax, Aiphone or other — we quote fast and book local engineers.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Request Quote</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20{{SERVICE_NAME}}%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
