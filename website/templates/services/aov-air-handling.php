<?php 
$pageTitle = '{{SERVICE_NAME}} in {{AREA}}';
$metaDesc = 'BS EN 12101 AOV and smoke ventilation systems installation, commissioning and maintenance in {{AREA}}. SE Controls, Nuaire, Brooks, Geze, D+H systems.';
$metaKeywords = 'AOV {{AREA}}, smoke ventilation {{AREA}}, automatic opening vents {{AREA}}, BS EN 12101 {{AREA}}, natural smoke ventilation, air handling unit';
$ogImage = url('/assets/images/services/aov-air-handling.jpg');
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
      "description": "AOV smoke ventilation and air handling system installation to BS 9991 in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/aov-air-handling.jpg') ?>",
      "serviceType": "AOV & Air Handling",
      "category": "AOV & Air Handling",
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
        {"@type": "Question", "name": "What is an AOV system?", "acceptedAnswer": {"@type": "Answer", "text": "Automatic Opening Vents for smoke control and ventilation in buildings."}},
        {"@type": "Question", "name": "Are AOV systems required by building regs?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, required under BS 9991 for certain multi-storey and commercial buildings."}},
        {"@type": "Question", "name": "Do you maintain AOV systems?", "acceptedAnswer": {"@type": "Answer", "text": "We offer full maintenance contracts including actuator testing and certification."}},
        {"@type": "Question", "name": "Can AOV integrate with fire alarms?", "acceptedAnswer": {"@type": "Answer", "text": "Full integration with fire detection systems for automatic activation."}}
      ]
    }
  ]
}
</script>
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-sm uppercase tracking-[3px] text-[#ff6b00] mb-2">SMOKE CONTROL • {{AREA}}</div>
    <h1 class="text-5xl md:text-6xl font-semibold tracking-tighter text-black">{{SERVICE_NAME}} in {{AREA}}</h1>

    <!-- IMAGE 1: Hero service image -->
    <div class="mt-8">
        <img src="<?= url('/assets/images/services/aov-air-handling.jpg') ?>"
             alt="{{SERVICE_NAME}} installation and servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional AOV and air handling equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services designs, installs, commissions and maintains <strong>Automatic Opening Vent (AOV)</strong> and <strong>air handling</strong> systems across <strong>{{AREA}}</strong> and the wider North West. From natural smoke ventilation on stairwells and façades to mechanical extraction, pressurisation and AHU control, our engineers deliver BS EN 12101-compliant solutions with fixed-price quotes and full certification.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a new smoke control scheme, actuator replacement, control panel upgrades, quarterly servicing or fire-alarm interface work, we support residential blocks, commercial offices, industrial units and multi-storey developments in {{AREA}}. All work aligns with BS EN 12101, BS 9991, Approved Document B and manufacturer guidance, with clear records for building control and fire safety officers.
    </p>

    <!-- IMAGE 2 & 3: Keyword visuals (exactly 3 images total with hero) -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/aov-system.jpg') ?>"
                 alt="AOV smoke ventilation system and control equipment used by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/aov-air-handling.jpg') ?>'">
            <p class="text-xs text-black mt-2">AOV control panels, actuators &amp; vents</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/air-handling-unit-installation.jpg') ?>"
                 alt="Air handling unit and smoke vent installation and testing in {{AREA}} by Icomply"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/aov-air-handling.jpg') ?>'">
            <p class="text-xs text-black mt-2">Smoke vents, AHU systems &amp; certification in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a specific AOV or air handling brand? We install, service and replace systems from <strong>SE Controls</strong>, <strong>Nuaire</strong>, <strong>Brooks</strong>, <strong>Ventilux</strong>, <strong>Geze</strong>, <strong>D+H Mechatronic</strong>, <strong>TROX</strong>, Colt, Smoke Control and Assa Abloy. If you already have roof AOVs, window actuators or an AHU control panel on site in {{AREA}}, we can inspect, maintain or upgrade it and issue matching certificates so people searching for their equipment brand can find local support.
    </p>

    <!-- Manufacturers (search-friendly) -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with every major AOV and air handling manufacturer so customers searching for their exact control panel, actuator or smoke vent brand can find local support in {{AREA}}.</p>
        <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('aov-air-handling') ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('aov-air-handling') ?>
        </div>
        </div>
        <div class="mt-6 grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-black mb-3">Typical Systems &amp; Equipment</h3>
                <ul class="text-sm text-black space-y-1">
                    <li>• Natural smoke ventilation AOVs (roof &amp; façade)</li>
                    <li>• Stairwell &amp; lobby smoke control systems</li>
                    <li>• Mechanical smoke extraction &amp; pressurisation</li>
                    <li>• Chain actuators, linear actuators &amp; smoke dampers</li>
                    <li>• Intelligent control panels with fire alarm integration</li>
                    <li>• Weather sensors, rain sensors &amp; wind monitoring</li>
                    <li>• Air handling unit (AHU) electrical control &amp; commissioning</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-black mb-3">Relevant Standards</h3>
                <p class="text-sm text-black">BS EN 12101-1 • BS EN 12101-2 • BS EN 12101-3 • BS 7346 • Approved Document B • BS 9991 • BS EN 1366-2</p>
            </div>
        </div>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Smoke Control Design</h3>
            <p class="text-sm text-black">CFD-aware design support, smoke layer calculations and full system design compliant with ADB and BS EN 12101 for life safety in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Fire Alarm Integration</h3>
            <p class="text-sm text-black">Direct connection to addressable fire panels for automatic activation. Works seamlessly with Kentec, Advanced and C-Tec systems.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Certification</h3>
            <p class="text-sm text-black">Quarterly and annual servicing, actuator testing, damper operation checks and full compliance documentation.</p>
        </div>
    </div>

    <div class="mt-12">
        <h3 class="font-semibold text-black mb-4">Service-Specific FAQs</h3>
        <div class="space-y-4 text-sm">
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">What is an AOV system and where is it required?</div>
                <p class="mt-1 text-black">Automatic Opening Vents provide natural smoke ventilation in stairwells, lobbies and atria. Required under Approved Document B for buildings over certain heights and in residential blocks.</p>
            </div>
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">How does AOV integrate with fire detection?</div>
                <p class="mt-1 text-black">AOV control panels receive signals from fire alarm detectors or manual call points. We specialise in integration with existing fire alarm systems.</p>
            </div>
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">Do AOV systems require regular testing?</div>
                <p class="mt-1 text-black">Yes. BS EN 12101 requires regular functional testing of actuators, dampers and control panels. We offer scheduled maintenance contracts with full certification.</p>
            </div>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need AOV &amp; Air Handling in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your control panel or actuator brand — SE Controls, Nuaire, Brooks, Geze, D+H and more — we quote fast with local engineers.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Get Quote in {{AREA}}</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20AOV%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">Fire Alarm integration →</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
