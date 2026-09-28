<?php 
/**
 * Gas Systems service template
 * 3 images · 3 paragraphs · manufacturers · SEO
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | iComply Property Services';
$metaDesc = 'Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not carry out gas work or issue CP12 certificates in {{AREA}}.';
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
      "description": "Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not carry out gas work or issue CP12 certificates in {{AREA}}.",
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
        "priceRange": "POA"
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
        {"@type": "Question", "name": "What is a Gas safety Certificate?", "acceptedAnswer": {"@type": "Answer", "text": "It is a legal requirement for landlords proving gas appliances are safe."}},
        {"@type": "Question", "name": "How often are gas safety checks needed?", "acceptedAnswer": {"@type": "Answer", "text": "Annual gas safety inspections are mandatory for rental properties."}},
        {"@type": "Question", "name": "Does iComply service commercial gas systems?", "acceptedAnswer": {"@type": "Answer", "text": "No. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not carry out gas work."}},
        {"@type": "Question", "name": "Is iComply Gas Safe registered?", "acceptedAnswer": {"@type": "Answer", "text": "No. iComply is not Gas Safe registered and does not show a Gas Safe logo or registration number."}}
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
             alt="Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not issue them."
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional gas systems equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply Property Services does not carry out gas work in <strong>{{AREA}}</strong> and does not issue CP12 or gas safety certificates.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Boiler installation, servicing and repair in {{AREA}} is gas work. iComply is not Gas Safe registered and does not show a Gas Safe logo or registration number. Non-gas compliance on the same property is quoted POA.
    </p>

    <!-- IMAGE 2 + keyword visuals -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/gas-installation.jpg') ?>"
                 alt="Gas installation and boiler equipment used by iComply in {{AREA}}"
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
        Brand names on this page are trade-supply references only. iComply does not install, service, or repair Worcester Bosch, Vaillant, Ideal or Baxi appliances in {{AREA}}, and does not issue gas safety certificates.
    </p>

    <!-- Manufacturers -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Brands listed as supplies only</h2>
        <p class="text-black mb-6">iComply does not install or service these gas brands in {{AREA}}. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
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
             alt="Engineer servicing boilers — Worcester Bosch, Vaillant, Ideal, Baxi in {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/gas-systems.jpg') ?>'">
        <p class="text-xs text-black mt-2">Engineers servicing major boiler brands across {{AREA}}</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Not carried out by iComply</h3>
            <p class="text-sm text-black">Boiler install and gas pipework in {{AREA}} are gas work. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Servicing stays with a Gas Safe engineer</h3>
            <p class="text-sm text-black">iComply does not service or repair boilers. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Safety Certificates</h3>
            <p class="text-sm text-black">Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not issue them.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Frequently Asked Questions</h2>
        <div class="space-y-4 text-sm">
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">How often are gas safety checks needed?</summary>
                <p class="mt-2 text-black">Annual gas safety inspections are a landlord duty where gas is present. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not issue them.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Which boiler brands do you service?</summary>
                <p class="mt-2 text-black">iComply does not install or service boilers in {{AREA}}. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Does iComply hold a Gas Safe registration?</summary>
                <p class="mt-2 text-black">No. iComply does not hold a Gas Safe registration. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
            </details>
        </div>
    </div>

    <div class="mt-16 bg-[#0B1F3A] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need Gas Systems in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">iComply does not carry out gas work. Ask for a non-gas compliance quote. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.</p>
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
