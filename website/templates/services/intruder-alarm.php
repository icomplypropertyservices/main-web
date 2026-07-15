<?php 
/**
 * Intruder Alarms — 3 images · 3 paragraphs · manufacturers · SEO brand alts
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'BS EN 50131 intruder alarm systems in {{AREA}}. Texecom, Honeywell and Pyronix panels — design, install, monitoring and maintenance.';
$metaKeywords = 'intruder alarm installation {{AREA}}, Texecom panel {{AREA}}, Honeywell alarm {{AREA}}, Pyronix alarm {{AREA}}, BS EN 50131 {{AREA}}, burglar alarm {{AREA}}, PD 6662 {{AREA}}';
$ogImage = url('/assets/images/services/intruder-alarm.jpg');
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
      "description": "BS EN 50131 and PD 6662 intruder alarm installation with Texecom, Honeywell and Pyronix panels in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/intruder-alarm.jpg') ?>",
      "serviceType": "Intruder Alarms",
      "category": "Intruder Alarms",
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
        {"@type": "Question", "name": "What standards apply to intruder alarms?", "acceptedAnswer": {"@type": "Answer", "text": "Systems are installed to BS EN 50131 and PD 6662 standards."}},
        {"@type": "Question", "name": "Do you offer monitored intruder alarms?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we provide connections to ARC monitoring centres for 24/7 response."}},
        {"@type": "Question", "name": "Can intruder alarms integrate with CCTV?", "acceptedAnswer": {"@type": "Answer", "text": "Full integration with CCTV, access control and fire systems is available."}},
        {"@type": "Question", "name": "Which panels do you install and service?", "acceptedAnswer": {"@type": "Answer", "text": "We install and service Texecom, Honeywell, Pyronix, DSC, Visonic, Risco, Scantronic, Ajax, Paradox and GJD control panels."}}
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
        <img src="<?= url('/assets/images/services/intruder-alarm.jpg') ?>"
             alt="Texecom Honeywell Pyronix intruder alarm panel installation and servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional {{SERVICE_NAME}} equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services designs, installs and maintains <strong>PD 6662</strong> and <strong>BS EN 50131</strong> compliant <strong>{{SERVICE_NAME}}</strong> across <strong>{{AREA}}</strong> and the wider North West. From Grade 2 domestic kits to Grade 3 commercial systems with police response, our engineers deliver fixed-price quotes, same-week appointments and full commissioning documentation.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a new Texecom or Honeywell control panel, a Pyronix wireless upgrade, ARC monitoring, or annual maintenance on an existing burglar alarm in {{AREA}}, we support commercial, industrial, residential and landlord properties. Wired, wireless and hybrid systems are available with app control, GSM/IP signalling and insurance-approved design.
    </p>

    <!-- IMAGE 2 + 3 grid -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/intruder-alarm-panel.jpg') ?>"
                 alt="Texecom and Honeywell Grade 2 Grade 3 intruder alarm control panel installed by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/intruder-alarm.jpg') ?>'">
            <p class="text-xs text-black mt-2">Intruder alarm control panels — Texecom, Honeywell &amp; Pyronix</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/wireless-intruder-alarm.jpg') ?>"
                 alt="Pyronix wireless intruder alarm sensors and keypad system installation testing in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/intruder-alarm.jpg') ?>'">
            <p class="text-xs text-black mt-2">Wireless &amp; hybrid alarm installation, testing &amp; certification in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a <strong>Texecom panel</strong>, <strong>Honeywell alarm</strong> or <strong>Pyronix system</strong> engineer near you? We install, service and replace major intruder alarm brands listed below. If you already have a control panel on site in {{AREA}}, we can inspect, maintain or upgrade it and supply matching certificates and ARC reconnection.
    </p>

    <!-- Full manufacturer list -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Panels We Support</h2>
        <p class="text-black mb-6">We work with leading intruder alarm manufacturers so customers searching for their exact panel or brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('intruder-alarm') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('intruder-alarm') ?>
        </div>
    </div>

    <!-- IMAGE 3: Brand / panel focus -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/burglar-alarm-system.jpg') ?>"
             alt="Texecom Honeywell Pyronix burglar alarm panels and PIR sensors serviced by Icomply in {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/intruder-alarm.jpg') ?>'">
        <p class="text-xs text-black mt-2">Manufacturer panels &amp; systems commonly installed and serviced in {{AREA}}</p>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Relevant Standards</h2>
        <p class="text-sm text-black">BS EN 50131-1 • PD 6662:2017 • BS 8243 • BS EN 50136 (Alarm Transmission) • EN 50131-6 (Power Supplies)</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Grade 2 &amp; Grade 3 system design, Texecom/Honeywell/Pyronix panel supply and install to British Standards in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Monitoring &amp; Servicing</h3>
            <p class="text-sm text-black">24/7 ARC monitoring, police response, annual maintenance contracts and reactive repairs for all major brands.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Integration &amp; Upgrades</h3>
            <p class="text-sm text-black">CCTV, access control and fire system integration plus wireless expansions and app-based control upgrades.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Related Services in {{AREA}}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/cctv.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">CCTV Systems</a>
            <a href="<?= url('/pages/services/access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Access Control</a>
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Fire Alarms</a>
            <a href="<?= url('/pages/services/door-entry.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Door Entry</a>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need {{SERVICE_NAME}} in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your panel brand — Texecom, Honeywell, Pyronix or other — we quote fast and book local engineers.</p>
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
