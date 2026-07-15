<?php 
/**
 * Electrical service template
 * 3 images · 3 paragraphs · manufacturers · SEO
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'NICEIC Part P electrical installation, EICR, PAT testing, EV charger installation and commercial electrician services in {{AREA}}. Schneider, Hager, Wylex, Rolec, Myenergi.';
$metaKeywords = 'electrician {{AREA}}, EICR {{AREA}}, electrical installation {{AREA}}, EV charger {{AREA}}, certified electrician {{AREA}}, commercial electrician {{AREA}}, PAT testing {{AREA}}, Schneider, Hager, Wylex, Rolec, Myenergi';
$ogImage = url('/assets/images/services/electrical.jpg');
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
      "description": "Professional electrical installation, EICR testing and certification in {{AREA}}. Schneider, Hager, Wylex, Rolec and Myenergi systems.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/electrical.jpg') ?>",
      "serviceType": "Electrical Services",
      "category": "Electrical Services",
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
        {"@type": "Question", "name": "What is an EICR?", "acceptedAnswer": {"@type": "Answer", "text": "An Electrical Installation Condition Report (EICR) is a periodic inspection to check the safety of electrical systems."}},
        {"@type": "Question", "name": "How often should commercial properties have electrical testing?", "acceptedAnswer": {"@type": "Answer", "text": "Every 5 years or upon change of tenancy for most commercial premises."}},
        {"@type": "Question", "name": "Do you install EV chargers?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we provide full EV charger installation services with Rolec and Myenergi systems, compliant with current regulations."}},
        {"@type": "Question", "name": "Are your electricians certified?", "acceptedAnswer": {"@type": "Answer", "text": "All our engineers are fully qualified, Part P registered and insured."}}
      ]
    }
  ]
}
</script>
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-sm uppercase tracking-[3px] text-[#ff6b00] mb-2">COMPLIANCE SERVICES • {{AREA}}</div>
    <h1 class="text-5xl md:text-6xl font-semibold tracking-tighter text-black">{{SERVICE_NAME}} in {{AREA}}</h1>

    <!-- IMAGE 1: Hero service image -->
    <div class="mt-8">
        <img src="<?= url('/assets/images/services/electrical.jpg') ?>"
             alt="Electrical installation and servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional electrical equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services provides complete <strong>electrical</strong> design, installation, commissioning, testing and certification across <strong>{{AREA}}</strong> and the wider North West. Our NICEIC and Part P registered engineers deliver fixed-price quotes, same-week appointments and full BS 7671 compliance documentation on every job — from domestic rewires to commercial distribution boards.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a consumer unit upgrade, EICR inspection, PAT testing, EV charger installation or emergency fault finding, we support commercial, industrial, residential and landlord properties in {{AREA}}. All work uses manufacturer-approved equipment from leading brands including Schneider, Hager, Wylex, Rolec and Myenergi.
    </p>

    <!-- IMAGE 2 + 3 side-by-side keywords -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/electrical-installation.jpg') ?>"
                 alt="Electrical installation panel and wiring equipment used by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/electrical.jpg') ?>'">
            <p class="text-xs text-black mt-2">Electrical installation, consumer units &amp; distribution boards</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/eicr.jpg') ?>"
                 alt="EICR electrical testing and certification in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/electrical.jpg') ?>'">
            <p class="text-xs text-black mt-2">EICR testing, PAT testing &amp; electrical certification in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a specific electrical manufacturer or EV brand? We install, service and replace major systems from Schneider Electric, Hager, Wylex, Rolec and Myenergi. If you already have a consumer unit, distribution board or EV charger on site in {{AREA}}, we can inspect, maintain or upgrade it and supply matching certificates.
    </p>

    <!-- Manufacturers -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with leading electrical manufacturers so customers searching for their exact brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('electrical') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('electrical') ?>
        </div>
    </div>

    <!-- IMAGE 3: Keyword focus -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/ev-charger-installation.jpg') ?>"
             alt="EV charger installation — Rolec and Myenergi systems by Icomply in {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/electrical.jpg') ?>'">
        <p class="text-xs text-black mt-2">EV charger installation, Rolec &amp; Myenergi systems commonly installed in {{AREA}}</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Full design, supply and install to BS 7671 for properties in {{AREA}} — rewires, boards, lighting and data cabling.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Repairs</h3>
            <p class="text-sm text-black">Fault finding, circuit repairs, consumer unit upgrades and reactive electrical call-outs for all major brands.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Testing &amp; Certification</h3>
            <p class="text-sm text-black">EICR, PAT testing, landlord certificates and EV charger commissioning with full documentation.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Frequently Asked Questions</h2>
        <div class="space-y-4 text-sm">
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">How often should I have an EICR carried out?</summary>
                <p class="mt-2 text-black">Every 5 years for rental properties, or upon change of tenancy. Commercial premises often require more frequent testing.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Do you install EV chargers at domestic and commercial sites?</summary>
                <p class="mt-2 text-black">Yes – we install Rolec and Myenergi EV chargers with full certification for homes and workplaces in {{AREA}}.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">What electrical manufacturers do you support?</summary>
                <p class="mt-2 text-black">We install and service Schneider, Hager, Wylex, Rolec and Myenergi equipment, plus other major UK electrical brands.</p>
            </details>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need Electrical Services in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your board brand or job type — we quote fast and book local engineers.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Request Quote</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Electrical%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
