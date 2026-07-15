<?php 
/**
 * CCTV Systems — 3 images · 3 paragraphs · manufacturers · SEO brand alts
 */
$pageTitle = '{{SERVICE_NAME}} in {{AREA}} | Icomply Property Services';
$metaDesc = 'BS EN 62676 CCTV installation in {{AREA}}. Hikvision, Axis and Dahua IP cameras, NVRs, ANPR and remote monitoring.';
$metaKeywords = 'cctv installation {{AREA}}, Hikvision CCTV {{AREA}}, Axis camera {{AREA}}, Dahua NVR {{AREA}}, BS EN 62676 {{AREA}}, IP CCTV {{AREA}}, ANPR {{AREA}}';
$ogImage = url('/assets/images/services/cctv.jpg');
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
      "description": "BS EN 62676 CCTV installation with Hikvision, Axis and Dahua cameras and NVRs in {{AREA}}.",
      "url": "<?= url('/pages/{{SERVICE_SLUG}}/{{AREA_SLUG}}.php') ?>",
      "image": "<?= url('/assets/images/services/cctv.jpg') ?>",
      "serviceType": "CCTV Systems",
      "category": "CCTV Systems",
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
        {"@type": "Question", "name": "What types of CCTV do you install?", "acceptedAnswer": {"@type": "Answer", "text": "We install IP, HD and wireless CCTV systems with remote viewing capabilities."}},
        {"@type": "Question", "name": "Is CCTV installation GDPR compliant?", "acceptedAnswer": {"@type": "Answer", "text": "Yes, all installations follow data protection guidelines with signage and secure storage."}},
        {"@type": "Question", "name": "Do you offer maintenance contracts for CCTV?", "acceptedAnswer": {"@type": "Answer", "text": "We provide ongoing maintenance, repairs and system health checks."}},
        {"@type": "Question", "name": "Which CCTV brands do you install?", "acceptedAnswer": {"@type": "Answer", "text": "We install and service Hikvision, Axis Communications, Dahua, Bosch, Hanwha Vision, Avigilon, Vivotek, Uniview, Milesight and Wisenet systems."}}
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
        <img src="<?= url('/assets/images/services/cctv.jpg') ?>"
             alt="Hikvision Axis Dahua CCTV camera and NVR panel installation in {{AREA}} by Icomply Property Services"
             width="1200" height="800"
             class="w-full h-72 md:h-96 object-cover rounded-3xl border"
             loading="eager">
        <p class="text-xs text-black mt-2">Professional {{SERVICE_NAME}} equipment and installation work in {{AREA}}</p>
    </div>

    <!-- PARAGRAPH 1 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Icomply Property Services provides complete <strong>BS EN 62676</strong> compliant <strong>{{SERVICE_NAME}}</strong> design, installation, remote viewing, ANPR and 24/7 monitoring across <strong>{{AREA}}</strong> and the wider North West. Our engineers specify Hikvision, Axis and Dahua IP cameras with professional NVR/DVR recording for clear, prosecutable evidence.
    </p>

    <!-- PARAGRAPH 2 -->
    <p class="mt-4 text-lg text-black max-w-3xl leading-relaxed">
        Whether you need a multi-camera commercial site, residential HD CCTV, thermal imaging, ANPR for car parks or a cloud-backed upgrade in {{AREA}}, we deliver fixed-price surveys, GDPR-aware signage and secure remote access apps. Systems integrate with intruder alarms, access control and fire detection where required.
    </p>

    <!-- IMAGE 2 + 3 grid -->
    <div class="mt-10 grid md:grid-cols-2 gap-6">
        <div>
            <img src="<?= url('/assets/images/keywords/hd-cctv-camera.jpg') ?>"
                 alt="Hikvision and Axis 4K IP CCTV camera dome and bullet cameras installed by Icomply in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/cctv.jpg') ?>'">
            <p class="text-xs text-black mt-2">HD/IP cameras — Hikvision, Axis &amp; Dahua</p>
        </div>
        <div>
            <img src="<?= url('/assets/images/keywords/ip-cctv-system.jpg') ?>"
                 alt="Dahua and Hikvision NVR recorder panel and IP CCTV system rack in {{AREA}}"
                 width="800" height="600"
                 class="w-full h-56 object-cover rounded-2xl border"
                 loading="lazy"
                 onerror="this.src='<?= url('/assets/images/services/cctv.jpg') ?>'">
            <p class="text-xs text-black mt-2">NVR panels, recording &amp; remote viewing setup in {{AREA}}</p>
        </div>
    </div>

    <!-- PARAGRAPH 3 -->
    <p class="mt-8 text-lg text-black max-w-3xl leading-relaxed">
        Searching for a <strong>Hikvision CCTV</strong>, <strong>Axis camera</strong> or <strong>Dahua NVR</strong> specialist near you? We install, service and expand major CCTV brands listed below. If you already have cameras or a recorder on site in {{AREA}}, we can audit coverage, repair faults, add AI analytics and supply matching maintenance certificates.
    </p>

    <!-- Full manufacturer list -->
    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-black mb-4">Manufacturers &amp; Equipment We Support</h2>
        <p class="text-black mb-6">We work with leading CCTV manufacturers so customers searching for their exact camera, NVR or brand can find local support in {{AREA}}.</p>
                <div class="flex flex-wrap gap-3">
            <?= manufacturerTagsHtml('cctv') ?>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <?= manufacturerImagesHtml('cctv') ?>
        </div>
    </div>

    <!-- IMAGE 3: Brand focus -->
    <div class="mt-10">
        <img src="<?= url('/assets/images/keywords/cctv-installation.jpg') ?>"
             alt="Hikvision Axis Dahua CCTV installation panels cameras and video wall equipment — Icomply {{AREA}}"
             width="1200" height="700"
             class="w-full h-64 md:h-80 object-cover rounded-3xl border"
             loading="lazy"
             onerror="this.src='<?= url('/assets/images/services/cctv.jpg') ?>'">
        <p class="text-xs text-black mt-2">Manufacturer cameras, NVRs &amp; systems commonly installed and serviced in {{AREA}}</p>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Relevant Standards</h2>
        <p class="text-sm text-black">BS EN 62676-1-1 • BS EN 62676-2 • BS EN 50132 • BS 8418 • ICO Data Protection (GDPR) Guidelines</p>
    </div>

    <div class="mt-12 grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Installation &amp; Design</h3>
            <p class="text-sm text-black">Site surveys, camera placement, PoE network design and Hikvision/Axis/Dahua supply-and-install in {{AREA}}.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Monitoring &amp; Storage</h3>
            <p class="text-sm text-black">NVR/cloud recording, remote apps, evidence export and managed storage retention policies.</p>
        </div>
        <div class="p-8 bg-white rounded-3xl border">
            <h3 class="font-semibold text-black mb-2">Analytics &amp; ANPR</h3>
            <p class="text-sm text-black">AI object detection, number plate recognition, perimeter alerts and integration with alarms.</p>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold text-black mb-4">Related Services in {{AREA}}</h2>
        <div class="flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/intruder-alarm.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Intruder Alarms</a>
            <a href="<?= url('/pages/services/access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Access Control</a>
            <a href="<?= url('/pages/services/electrical.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Electrical Systems</a>
            <a href="<?= url('/pages/services/door-entry.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]">Door Entry</a>
        </div>
    </div>

    <div class="mt-16 bg-[#0a2540] text-white p-12 rounded-3xl text-center">
        <h2 class="text-3xl font-semibold mb-4">Need {{SERVICE_NAME}} in {{AREA}}?</h2>
        <p class="max-w-md mx-auto text-white/90 mb-8">Tell us your camera or NVR brand — Hikvision, Axis, Dahua or other — we quote fast and book local engineers.</p>
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
