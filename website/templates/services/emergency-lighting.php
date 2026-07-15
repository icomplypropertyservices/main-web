<?php 
$pageTitle = '{{SERVICE_NAME}} in {{AREA}}';
$metaDesc = 'BS 5266 emergency lighting installation, testing and certification in {{AREA}}. Emergi-Lite, Mackwell, Cooper, Legrand, Thorlux systems. 24/7 compliance.';
$metaKeywords = 'emergency lighting {{AREA}}, BS 5266 {{AREA}}, emergency lighting testing {{AREA}}, Emergi-Lite, Mackwell, Cooper Lighting, LED emergency lighting';
$ogImage = url('/assets/images/services/emergency-lighting.jpg');
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
      "description": "BS 5266 emergency lighting installation, testing and certification in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/emergency-lighting.jpg') ?>",
      "serviceType": "Emergency Lighting",
      "category": "Emergency Lighting",
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
        {"@type": "Question", "name": "What is BS 5266?", "acceptedAnswer": {"@type": "Answer", "text": "BS 5266 is the code of practice for emergency lighting systems."}},
        {"@type": "Question", "name": "How frequently is emergency lighting testing required?", "acceptedAnswer": {"@type": "Answer", "text": "Monthly function tests and annual full duration tests are mandatory."}},
        {"@type": "Question", "name": "Do you provide emergency lighting certification?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, full documentation and compliance certificates are provided after every test."}},
        {"@type": "Question", "name": "Can you install new emergency lighting systems?", "acceptedAnswer": {"@type": "Answer", "text": "We design, supply and install complete emergency lighting solutions to BS 5266."}}
      ]
    }
  ]
}
</script>
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-sm uppercase tracking-[3px] text-[#ff6b00] mb-2">LIFE SAFETY LIGHTING • {{AREA}}</div>
    <h1 class="text-5xl md:text-6xl font-semibold tracking-tighter text-black">{{SERVICE_NAME}} in {{AREA}}</h1>

    <!-- IMAGE 1: Hero service image -->
    <div class="mt-8">
        <img src="<?= url('/assets/images/services/emergency-lighting.jpg') ?>"
             alt="{{SERVICE_NAME}} installation and servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional emergency lighting equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services provides complete <strong>BS 5266 emergency lighting</strong> design, installation, commissioning, testing and maintenance across <strong>{{AREA}}</strong> and the wider North West. We cover self-contained LED luminaires, central battery systems, exit signage and open-area illumination so escape routes remain safe during power failure — with fixed-price quotes and full compliance documentation on every job.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a new emergency lighting scheme, an LED upgrade, monthly function tests, annual duration testing, battery replacement or reactive repairs, we support commercial, industrial, retail, landlord and multi-occupancy properties in {{AREA}}. All work meets BS 5266-1, BS EN 50172 and BS EN 1838, with logbooks and certificates ready for HSE, insurers and fire risk assessors.
    </p>

    <!-- IMAGE 2 & 3: Keyword visuals (exactly 3 images total with hero) -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/emergency-lighting-system.jpg') ?>"
                 alt="Emergency lighting system and control equipment used by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/emergency-lighting.jpg') ?>'">
            <p class="text-xs text-black mt-2">Emergency lighting systems &amp; luminaires</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/emergency-exit-lighting.jpg') ?>"
                 alt="Emergency exit lighting installation and testing in {{AREA}} by Icomply"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/emergency-lighting.jpg') ?>'">
            <p class="text-xs text-black mt-2">Exit signs, LED fittings &amp; certification in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a specific emergency lighting brand or central battery make? We install, service and replace systems from <strong>Emergi-Lite</strong>, <strong>Mackwell</strong>, <strong>Cooper Lighting</strong>, <strong>Legrand</strong>, <strong>Ansell</strong>, <strong>Thorlux</strong>, <strong>Fagerhult</strong>, Eaton, ABB, Zumtobel and more. If you already have fittings or an inverter pack on site in {{AREA}}, we can inspect, maintain or upgrade it and supply matching certificates so people searching for their equipment brand can find local support.
    </p>

    <!-- Manufacturers (search-friendly) -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with every major emergency lighting manufacturer so customers searching for their exact luminaires, exit signs or central battery system can find local support in {{AREA}}.</p>
        <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('emergency-lighting') ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('emergency-lighting') ?>
        </div>
        </div>
        <div class="mt-6 grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-black mb-3">Typical Systems &amp; Equipment</h3>
                <ul class="text-sm text-black space-y-1">
                    <li>• Self-contained LED luminaires (maintained &amp; non-maintained)</li>
                    <li>• Central battery systems &amp; inverter packs</li>
                    <li>• Addressable emergency lighting monitoring</li>
                    <li>• Bulkhead, exit signs &amp; downlighter units</li>
                    <li>• Wireless testing &amp; self-test systems</li>
                    <li>• High-bay &amp; external emergency lighting</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-black mb-3">Relevant Standards</h3>
                <p class="text-sm text-black">BS 5266-1 • BS EN 50172 • BS EN 60598-2-22 • BS 5839-1 (integration) • BS 9999 • BS EN 1838</p>
            </div>
        </div>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Full Compliance Testing</h3>
            <p class="text-sm text-black">Monthly function tests, annual duration tests, logbook management and certification to meet HSE and fire safety requirements in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Design &amp; Installation</h3>
            <p class="text-sm text-black">Point-by-point lighting calculations, CAD design and professional installation ensuring escape routes and open areas are correctly illuminated.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">24/7 Monitoring &amp; Maintenance</h3>
            <p class="text-sm text-black">Central monitoring solutions, rapid response repairs and seamless integration with fire alarm and electrical systems.</p>
        </div>
    </div>

    <div class="mt-12">
        <h3 class="font-semibold text-black mb-4">Service-Specific FAQs</h3>
        <div class="space-y-4 text-sm">
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">How often should emergency lighting be tested?</div>
                <p class="mt-1 text-black">Monthly short-duration tests and annual full-duration tests are required under BS 5266. We provide automated self-test systems and detailed compliance reports.</p>
            </div>
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">Can emergency lighting integrate with our fire alarm system?</div>
                <p class="mt-1 text-black">Yes. Many modern systems link directly to addressable fire panels (e.g. Kentec, Advanced) for automatic activation of emergency lighting on alarm.</p>
            </div>
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">What is the difference between maintained and non-maintained luminaires?</div>
                <p class="mt-1 text-black">Maintained luminaires stay on during normal power and switch to battery in failure. Non-maintained only illuminate during power failure. We advise based on building use.</p>
            </div>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need Emergency Lighting in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your fittings or central battery brand — we quote fast and book local engineers for installation, testing and certification.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Get Quote in {{AREA}}</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Emergency%20Lighting%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">Fire Alarm services →</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
