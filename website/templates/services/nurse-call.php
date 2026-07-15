<?php 
/**
 * Nurse Call Systems service template
 * 3 images · 3 paragraphs · manufacturers · SEO
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'Nurse call system installation, wireless and wired solutions, servicing in {{AREA}}. Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann, Rauland for care homes and hospitals.';
$metaKeywords = 'nurse call system {{AREA}}, care home nurse call {{AREA}}, hospital call system {{AREA}}, wireless nurse call, HTM 08-03, Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann, Rauland';
$ogImage = url('/assets/images/services/nurse-call.jpg');
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
      "description": "HTM 08-03 nurse call system installation and maintenance in {{AREA}}. Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann and Rauland systems.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/nurse-call.jpg') ?>",
      "serviceType": "Nurse Call Systems",
      "category": "Nurse Call Systems",
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
        {"@type": "Question", "name": "What is HTM 08-03?", "acceptedAnswer": {"@type": "Answer", "text": "Health Technical Memorandum for nurse call systems in healthcare environments."}},
        {"@type": "Question", "name": "Do you work with care homes?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, we specialise in nurse call for care homes, hospitals and sheltered housing."}},
        {"@type": "Question", "name": "Can nurse call integrate with other systems?", "acceptedAnswer": {"@type": "Answer", "text": "Integration with fire alarms, door entry and paging systems is standard."}},
        {"@type": "Question", "name": "Do you provide training on nurse call systems?", "acceptedAnswer": {"@type": "Answer", "text": "Full staff training and user manuals are included with every installation."}}
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
        <img src="<?= url('/assets/images/services/nurse-call.jpg') ?>"
             alt="Nurse call system installation and servicing in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional nurse call equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services provides complete <strong>nurse call system</strong> design, installation, commissioning, maintenance and certification across <strong>{{AREA}}</strong> and the wider North West. Our engineers deliver fixed-price quotes, same-week appointments and full HTM 08-03 compliant documentation for care homes, hospitals and assisted living facilities.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a new wired or wireless nurse call system, a panel upgrade, periodic testing or emergency repairs, we support healthcare, residential care and commercial properties in {{AREA}}. All work uses manufacturer-approved equipment from Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann and Rauland.
    </p>

    <!-- IMAGE 2 + keyword visuals -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/nurse-call-system.jpg') ?>"
                 alt="Nurse call system panel and equipment used by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/nurse-call.jpg') ?>'">
            <p class="text-xs text-black mt-2">Nurse call control panels, pendants &amp; over-door lights</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/care-home-nurse-call.jpg') ?>"
                 alt="Care home nurse call installation and testing in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/nurse-call.jpg') ?>'">
            <p class="text-xs text-black mt-2">Care home nurse call installation, testing &amp; certification in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a specific nurse call manufacturer or panel brand? We install, service and replace major systems from Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann and Rauland. If you already have a nurse call panel on site in {{AREA}}, we can inspect, maintain or upgrade it and supply matching certificates for CQC and HTM compliance.
    </p>

    <!-- Manufacturers -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with leading nurse call manufacturers so customers searching for their exact panel or brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('nurse-call') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('nurse-call') ?>
        </div>
    </div>

    <!-- IMAGE 3 -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/hospital-nurse-call-system.jpg') ?>"
             alt="Hospital nurse call manufacturer panels — Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann, Rauland — Icomply {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/nurse-call.jpg') ?>'">
        <p class="text-xs text-black mt-2">Manufacturer nurse call panels &amp; systems commonly installed and serviced in {{AREA}}</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Wired, wireless and IP nurse call design and install to HTM 08-03 for care sites in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Servicing</h3>
            <p class="text-sm text-black">Contracts, reactive repairs and battery backups for Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann and Rauland.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Testing &amp; Certification</h3>
            <p class="text-sm text-black">System tests, CQC-ready documentation and staff training for hospitals and care homes.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Frequently Asked Questions</h2>
        <div class="space-y-4 text-sm">
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Do you offer wireless nurse call solutions?</summary>
                <p class="mt-2 text-black">Yes. We install fully wireless systems from Courtney Thorne and other leading brands that require minimal disruption for care homes and hospitals in {{AREA}}.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">Which nurse call manufacturers do you support?</summary>
                <p class="mt-2 text-black">We install and service Courtney Thorne, Tunstall, Ascom, Zettler, Ackermann and Rauland systems.</p>
            </details>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-medium cursor-pointer text-black">What maintenance is required for nurse call systems?</summary>
                <p class="mt-2 text-black">Quarterly inspections, battery checks and full system tests are recommended. We provide comprehensive service contracts with detailed compliance reporting.</p>
            </details>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need Nurse Call Systems in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your panel brand or system type — we quote fast and book local engineers.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= url('/contact.php') ?>" class="bg-[#ff6b00] px-10 py-4 rounded-2xl font-semibold">Request Quote</a>
            <a href="https://wa.me/<?= WHATSAPP ?>?text=Quote%20for%20Nurse%20Call%20in%20{{AREA}}"
               class="border border-white/40 px-10 py-4 rounded-2xl font-semibold">WhatsApp Us</a>
        </div>
    </div>

    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
