<?php 
/**
 * Gas Systems service template
 * 3 images · 3 paragraphs · manufacturers · SEO
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'Gas Safe registered engineers. Landlord gas safety certificates, boiler servicing and commercial gas in {{AREA}}. Worcester Bosch, Vaillant, Ideal, Baxi.';
$metaKeywords = 'gas safety certificate {{AREA}}, gas boiler servicing {{AREA}}, landlord gas safety {{AREA}}, Worcester Bosch, Vaillant, Ideal, Baxi, gas engineer {{AREA}}';
$ogImage = url('/assets/images/services/gas-systems.jpg');
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
      "description": "Gas safety certificates, boiler servicing and CP12 compliance in {{AREA}}. Worcester Bosch, Vaillant, Ideal and Baxi systems.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/gas-systems.jpg') ?>",
      "serviceType": "Gas Systems",
      "category": "Gas Systems",
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
        {"@type": "Question", "name": "What is a Gas Safety Certificate?", "acceptedAnswer": {"@type": "Answer", "text": "It is a legal requirement for landlords proving gas appliances are safe."}},
        {"@type": "Question", "name": "How often are gas safety checks needed?", "acceptedAnswer": {"@type": "Answer", "text": "Annual gas safety inspections are mandatory for rental properties."}},
        {"@type": "Question", "name": "Do you service commercial gas systems?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we handle commercial boilers, pipework and gas compliance."}},
        {"@type": "Question", "name": "Are your gas engineers Gas Safe registered?", "acceptedAnswer": {"@type": "Answer", "text": "All engineers are Gas Safe registered with current qualifications."}}
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
        <img src="<?= url('/assets/images/services/gas-systems.jpg') ?>"
             alt="Gas systems installation and boiler servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional gas systems equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services provides complete <strong>gas systems</strong> installation, boiler servicing, landlord gas safety certificates and commercial gas compliance across <strong>{{AREA}}</strong> and the wider North West. Our Gas Safe registered engineers deliver fixed-price quotes, same-week appointments and full certification on every job.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a new boiler, annual service, CP12 landlord certificate, gas pipework installation or emergency repair, we support commercial, industrial, residential and landlord properties in {{AREA}}. All work is carried out by Gas Safe registered engineers using manufacturer-approved parts for Worcester Bosch, Vaillant, Ideal and Baxi systems.
    </p>

    <!-- IMAGE 2 + keyword visuals -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/gas-installation.jpg') ?>"
                 alt="Gas installation and boiler equipment used by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/gas-systems.jpg') ?>'">
            <p class="text-xs text-black mt-2">Gas installation, boilers &amp; pipework systems</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/gas-servicing.jpg') ?>"
                 alt="Gas boiler servicing and safety checks in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/gas-systems.jpg') ?>'">
            <p class="text-xs text-black mt-2">Boiler servicing, safety checks &amp; certification in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a specific boiler manufacturer? We install, service and repair major gas systems from Worcester Bosch, Vaillant, Ideal and Baxi. If you already have a boiler or commercial gas appliance on site in {{AREA}}, we can inspect, service or upgrade it and issue the required gas safety certificates.
    </p>

    <!-- Manufacturers -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with leading gas and boiler manufacturers so customers searching for their exact brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('gas-systems') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('gas-systems') ?>
        </div>
    </div>

    <!-- IMAGE 3 -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/gas-engineer.jpg') ?>"
             alt="Gas Safe engineer servicing boilers — Worcester Bosch, Vaillant, Ideal, Baxi in {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/gas-systems.jpg') ?>'">
        <p class="text-xs text-black mt-2">Gas Safe engineers servicing major boiler brands across {{AREA}}</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Boiler install, gas pipework and commercial gas systems for properties in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Servicing &amp; Repairs</h3>
            <p class="text-sm text-black">Annual boiler services, breakdown repairs and parts for Worcester Bosch, Vaillant, Ideal and Baxi.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Safety Certificates</h3>
            <p class="text-sm text-black">Landlord CP12 gas safety certificates, tightness testing and full compliance documentation.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Frequently Asked Questions</h2>
        <div class="space-y-4 text-sm">
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">How often are gas safety checks needed?</summary>
                <p class="mt-2 text-black">Annual gas safety inspections are mandatory for rental properties. We issue CP12 certificates on completion.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Which boiler brands do you service?</summary>
                <p class="mt-2 text-black">We install and service Worcester Bosch, Vaillant, Ideal and Baxi boilers across {{AREA}}, plus other major UK brands.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Are your gas engineers Gas Safe registered?</summary>
                <p class="mt-2 text-black">Yes. All engineers are Gas Safe registered with current qualifications for domestic and commercial work.</p>
            </details>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need Gas Systems in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your boiler brand or job type — we quote fast and book local Gas Safe engineers.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Request Quote</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Gas%20Systems%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
