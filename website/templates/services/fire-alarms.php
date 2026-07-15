<?php 
$pageTitle = '{{SERVICE_NAME}} in {{AREA}}';
$metaDesc = 'BS 5839 fire alarm installation, servicing and certification in {{AREA}}. Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo systems.';
$metaKeywords = 'fire alarm installation {{AREA}}, BS 5839 {{AREA}}, fire detection {{AREA}}, Kentec fire alarm, Advanced fire alarm, C-Tec, Morley, Hochiki, Apollo';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
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
      "description": "BS 5839 fire alarm installation, servicing and certification in {{AREA}}. Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo systems.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/fire-alarms.jpg') ?>",
      "serviceType": "Fire Alarm Systems",
      "category": "Fire Alarm Systems",
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
        {"@type": "Question", "name": "What is BS 5839 compliance for fire alarms?", "acceptedAnswer": {"@type": "Answer", "text": "BS 5839 is the British Standard for fire detection and alarm systems in buildings, ensuring proper design, installation and maintenance."}},
        {"@type": "Question", "name": "How often do fire alarms need servicing?", "acceptedAnswer": {"@type": "Answer", "text": "Fire alarm systems should be serviced at least twice a year with full certification."}},
        {"@type": "Question", "name": "Do you install wireless fire alarm systems?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we install addressable, conventional and wireless solutions from leading manufacturers."}},
        {"@type": "Question", "name": "Can you maintain existing fire alarm systems?", "acceptedAnswer": {"@type": "Answer", "text": "Absolutely, we provide reactive repairs, planned maintenance contracts and annual testing."}}
      ]
    }
  ]
}
</script>
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-sm uppercase tracking-[3px] text-[#ff6b00] mb-2">FIRE DETECTION • {{AREA}}</div>
    <h1 class="text-5xl md:text-6xl font-semibold tracking-tighter text-black">{{SERVICE_NAME}} in {{AREA}}</h1>

    <!-- IMAGE 1: Hero service image -->
    <div class="mt-8">
        <img src="<?= url('/assets/images/services/fire-alarms.jpg') ?>"
             alt="{{SERVICE_NAME}} installation and servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional fire alarm equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services designs, installs, commissions and maintains <strong>BS 5839 fire alarm systems</strong> across <strong>{{AREA}}</strong> and the wider North West. From addressable multi-loop panels in commercial buildings to conventional and wireless systems for landlords and smaller premises, our engineers deliver fixed-price quotes, same-week appointments and full certification on every job.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a new fire detection system, a panel upgrade, bi-annual servicing, battery replacement or emergency call-out repairs, we support offices, industrial units, retail, care facilities, multi-let blocks and residential properties in {{AREA}}. All work follows BS 5839 Parts 1 and 6, BS EN 54 and manufacturer guidance, with clear logbooks and compliance certificates for insurers and fire officers.
    </p>

    <!-- IMAGE 2 & 3: Keyword visuals (exactly 3 images total with hero) -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/fire-alarm-panel.jpg') ?>"
                 alt="Fire alarm panel and control equipment used by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/fire-alarms.jpg') ?>'">
            <p class="text-xs text-black mt-2">Fire alarm control panels &amp; detection equipment</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/kentec-fire-alarm-panel.jpg') ?>"
                 alt="Kentec Advanced C-Tec Morley Hochiki Apollo fire alarm panels installed and serviced by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/fire-alarms.jpg') ?>'">
            <p class="text-xs text-black mt-2">Kentec, Advanced, C-Tec, Morley, Hochiki &amp; Apollo panels in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a specific fire alarm panel brand? We install, service and replace major systems including <strong>Kentec</strong>, <strong>Advanced</strong>, <strong>C-Tec</strong>, <strong>Morley</strong>, <strong>Hochiki</strong> and <strong>Apollo</strong>. If you already have a panel on site in {{AREA}} and are searching for local support for that exact manufacturer, we can inspect, maintain, reprogram or upgrade it and issue matching certificates — so people looking for their panel brand can find us.
    </p>

    <!-- Manufacturers (search-friendly) -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with every major fire alarm manufacturer so customers searching for their exact panel or brand can find local support in {{AREA}}. Whether your system is Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo or another leading make, our engineers know the hardware.</p>
        <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('fire-alarms') ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('fire-alarms') ?>
        </div>
        </div>
        <div class="mt-6 grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-black mb-3">System Types</h3>
                <ul class="text-sm text-black space-y-1">
                    <li>• Addressable fire alarm panels</li>
                    <li>• Conventional fire alarm systems</li>
                    <li>• Wireless fire alarm solutions</li>
                    <li>• Aspirating smoke detection (VESDA)</li>
                    <li>• Beam detection systems</li>
                    <li>• Voice alarm &amp; sounder/beacon networks</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-black mb-3">Relevant Standards</h3>
                <p class="text-sm text-black">BS 5839 Part 1 • BS 5839 Part 6 • BS EN 54 • BS 9991 • Fire Safety Order compliance documentation</p>
            </div>
        </div>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Full BS 5839 design, supply and install of addressable, conventional and wireless fire detection for properties in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Servicing</h3>
            <p class="text-sm text-black">Planned contracts, reactive repairs, battery replacements and bi-annual services for all major panel brands.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Testing &amp; Certification</h3>
            <p class="text-sm text-black">Statutory tests, zone checks and full documentation for landlords, facilities managers and commercial sites.</p>
        </div>
    </div>

    <div class="mt-12">
        <h3 class="font-semibold text-black mb-4">Service-Specific FAQs</h3>
        <div class="space-y-4 text-sm">
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">How often do fire alarms need servicing?</div>
                <p class="mt-1 text-black">Under BS 5839, systems should be serviced at least twice a year with full certification and logbook updates.</p>
            </div>
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">Do you work on Kentec, Advanced and C-Tec panels?</div>
                <p class="mt-1 text-black">Yes. We install and maintain Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo and other major brands — ideal if you searched for your panel make and need local engineers in {{AREA}}.</p>
            </div>
            <div class="border rounded-2xl p-5">
                <div class="font-semibold text-black">Can you upgrade an existing fire alarm system?</div>
                <p class="mt-1 text-black">We survey existing loops and devices, recommend compliant upgrades or panel replacements, and commission with full certificates.</p>
            </div>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need Fire Alarms in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your panel brand — Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo or another make — and we quote fast with local engineers.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Get Quote in {{AREA}}</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Fire%20Alarms%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
