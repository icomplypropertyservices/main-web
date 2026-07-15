<?php 
/**
 * Access Control — 3 images · 3 paragraphs · manufacturers · SEO brand alts
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'BS EN 50133 access control in {{AREA}}. Paxton, HID and Salto panels, readers and locks — install, program and maintain.';
$metaKeywords = 'access control installation {{AREA}}, Paxton Net2 {{AREA}}, HID reader {{AREA}}, Salto lock {{AREA}}, BS EN 50133 {{AREA}}, biometric access control {{AREA}}';
$ogImage = url('/assets/images/services/access-control.jpg');
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
      "description": "BS EN 50133 access control installation with Paxton, HID and Salto systems in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/access-control.jpg') ?>",
      "serviceType": "Access Control",
      "category": "Access Control",
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
        {"@type": "Question", "name": "What access control systems do you install?", "acceptedAnswer": {"@type": "Answer", "text": "We install biometric, card, fob and keypad systems for commercial and residential use."}},
        {"@type": "Question", "name": "Can access control integrate with fire alarms?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we specialise in integrated security and life safety systems."}},
        {"@type": "Question", "name": "Do you provide ongoing support?", "acceptedAnswer": {"@type": "Answer", "text": "Maintenance contracts and 24/7 support options are available."}},
        {"@type": "Question", "name": "Which access control brands do you support?", "acceptedAnswer": {"@type": "Answer", "text": "We install and service Paxton, HID Global, Salto Systems, ASSA ABLOY, Honeywell, Gallagher, Stanley Security, CDVI, TDSi and Kantech."}}
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
        <img src="<?= url('/assets/images/services/access-control.jpg') ?>"
             alt="Paxton HID Salto access control panel reader and door lock installation in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional {{SERVICE_NAME}} equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services designs, installs and maintains <strong>BS EN 50133</strong> and <strong>EN 60839</strong> compliant <strong>{{SERVICE_NAME}}</strong> across <strong>{{AREA}}</strong> and the wider North West. From single-door Paxton kits to multi-site HID and Salto platforms, our engineers deliver secure credential management, fire override release and full user training.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need proximity readers, biometric fingerprint or facial recognition, wireless Salto locks, turnstiles or car-park barriers in {{AREA}}, we survey, supply and commission systems that fit your building. Mobile credentials, time zones, anti-passback and audit trails are standard on modern Paxton Net2, HID and Salto installs.
    </p>

    <!-- IMAGE 2 + 3 grid -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/access-control-system.jpg') ?>"
                 alt="Paxton Net2 access control panel and HID proximity card reader installed by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/access-control.jpg') ?>'">
            <p class="text-xs text-black mt-2">Access control panels — Paxton, HID &amp; Salto</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/biometric-access-control.jpg') ?>"
                 alt="Salto wireless lock and biometric access control reader installation testing in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/access-control.jpg') ?>'">
            <p class="text-xs text-black mt-2">Biometric readers, wireless locks &amp; programming in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a <strong>Paxton Net2</strong>, <strong>HID reader</strong> or <strong>Salto lock</strong> engineer near you? We install, service and expand major access control brands listed below. If you already have a controller or door hardware on site in {{AREA}}, we can reprogram, repair, add doors or integrate with CCTV, intercoms and fire alarms.
    </p>

    <!-- Full manufacturer list -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Systems We Support</h2>
        <p class="text-black mb-6">We work with leading access control manufacturers so customers searching for their exact panel, reader or brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('access-control') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('access-control') ?>
        </div>
    </div>

    <!-- IMAGE 3: Brand focus -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/proximity-card-reader.jpg') ?>"
             alt="Paxton HID Salto proximity card readers controllers and door access panels — Icomply {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/access-control.jpg') ?>'">
        <p class="text-xs text-black mt-2">Manufacturer readers, controllers &amp; locks commonly installed and serviced in {{AREA}}</p>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Relevant Standards</h2>
        <p class="text-sm text-black">BS EN 50133-1 • EN 60839-11 • BS EN 50132 (CCTV Integration) • BS 8600 • Disability Discrimination Act (DDA) Compliance</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Paxton, HID and Salto design, door hardware, maglocks and controller programming for properties in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Support</h3>
            <p class="text-sm text-black">Token management, firmware updates, battery lock servicing and reactive door release repairs.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Integration</h3>
            <p class="text-sm text-black">Fire alarm free-exit, CCTV event linking, lift control, barriers and multi-site software platforms.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Related Services in {{AREA}}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/intruder-alarm.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Intruder Alarms</a>
            <a href="<?= url('/pages/services/cctv.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">CCTV Systems</a>
            <a href="<?= url('/pages/services/intercoms.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Intercoms</a>
            <a href="<?= url('/pages/services/door-entry.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Door Entry</a>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need {{SERVICE_NAME}} in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your panel or brand — Paxton, HID, Salto or other — we quote fast and book local engineers.</p>
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
