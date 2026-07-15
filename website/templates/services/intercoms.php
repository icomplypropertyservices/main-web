<?php 
/**
 * Intercoms — 3 images · 3 paragraphs · manufacturers · SEO brand alts
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'Intercom system installation in {{AREA}}. Aiphone, Commend and Zenitel industrial and commercial panels — IP, video and PA.';
$metaKeywords = 'intercom installation {{AREA}}, Aiphone intercom {{AREA}}, Commend intercom {{AREA}}, Zenitel Stentofon {{AREA}}, industrial intercom {{AREA}}, IP intercom {{AREA}}';
$ogImage = url('/assets/images/services/intercoms.jpg');
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
      "description": "Audio, video and IP intercom systems with Aiphone, Commend and Zenitel stations in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/intercoms.jpg') ?>",
      "serviceType": "Intercoms",
      "category": "Intercoms",
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
        {"@type": "Question", "name": "What intercom systems do you install?", "acceptedAnswer": {"@type": "Answer", "text": "We install video, audio and IP-based intercom systems with smartphone integration."}},
        {"@type": "Question", "name": "Are your intercoms suitable for apartments?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, multi-tenant systems with directory and access features are our speciality."}},
        {"@type": "Question", "name": "Do you provide repairs for existing intercoms?", "acceptedAnswer": {"@type": "Answer", "text": "We repair and upgrade all major brands of audio and video intercom equipment."}},
        {"@type": "Question", "name": "Which intercom brands do you support?", "acceptedAnswer": {"@type": "Answer", "text": "We install and service Aiphone, Commend, Zenitel, Barix, Stentofon, TOA, Siedle, Clear-Com, Vingtor-Stentofon and Legrand."}}
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
        <img src="<?= url('/assets/images/services/intercoms.jpg') ?>"
             alt="Aiphone Commend Zenitel intercom master station and door panel installation in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional {{SERVICE_NAME}} equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services specialises in audio, video and IP <strong>{{SERVICE_NAME}}</strong> for offices, factories, healthcare, schools and commercial premises across <strong>{{AREA}}</strong> and the wider North West. We install and maintain <strong>Aiphone</strong>, <strong>Commend</strong> and <strong>Zenitel</strong> master stations, substations and weatherproof door units with crystal-clear duplex speech.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need hands-free industrial call points, explosion-proof stations, PoE SIP intercoms, PA paging or video door integration in {{AREA}}, our engineers design master/substation layouts that work in noisy and high-security environments. Systems link to access control, CCTV and nurse call where required for unified site communication.
    </p>

    <!-- IMAGE 2 + 3 grid -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/aiphone-intercom.jpg') ?>"
                 alt="Aiphone video intercom master station and door station panel installed by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/intercoms.jpg') ?>'">
            <p class="text-xs text-black mt-2">Intercom stations — Aiphone, Commend &amp; Zenitel</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/video-intercom.jpg') ?>"
                 alt="Commend and Zenitel IP industrial intercom panel installation and testing in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/intercoms.jpg') ?>'">
            <p class="text-xs text-black mt-2">IP PoE video intercoms &amp; industrial stations in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for an <strong>Aiphone intercom</strong>, <strong>Commend system</strong> or <strong>Zenitel / Stentofon</strong> engineer near you? We install, service and expand major intercom brands listed below. If you already have master stations or outdoor call points on site in {{AREA}}, we can diagnose faults, rewire, upgrade to IP and supply maintenance contracts with priority response.
    </p>

    <!-- Full manufacturer list -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Stations We Support</h2>
        <p class="text-black mb-6">We work with leading intercom manufacturers so customers searching for their exact panel, station or brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('intercoms') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('intercoms') ?>
        </div>
    </div>

    <!-- IMAGE 3: Brand focus -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/intercom-system.jpg') ?>"
             alt="Aiphone Commend Zenitel intercom panels master stations and industrial call points — Icomply {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/intercoms.jpg') ?>'">
        <p class="text-xs text-black mt-2">Manufacturer stations &amp; systems commonly installed and serviced in {{AREA}}</p>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Relevant Standards</h2>
        <p class="text-sm text-black">BS EN 50133 • EN 50131 • BS EN 62642 • IEC 60950 • BS 5839 Part 8 (voice alarm) • BS EN 60268</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Aiphone, Commend and Zenitel master/substation design for industrial and commercial sites in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Maintenance &amp; Response</h3>
            <p class="text-sm text-black">Speaker/mic testing, firmware updates, station repairs and planned preventative maintenance contracts.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">IP &amp; Integration</h3>
            <p class="text-sm text-black">PoE SIP intercoms, PA paging, door release, CCTV and access control unified communication.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Related Services in {{AREA}}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/door-entry.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Door Entry</a>
            <a href="<?= url('/pages/services/access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Access Control</a>
            <a href="<?= url('/pages/services/cctv.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">CCTV Systems</a>
            <a href="<?= url('/pages/services/nurse-call.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Nurse Call</a>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need {{SERVICE_NAME}} in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your station brand — Aiphone, Commend, Zenitel or other — we quote fast and book local engineers.</p>
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
