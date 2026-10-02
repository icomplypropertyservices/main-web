<?php
/**
 * Manufacturer × area page.
 * Vars: MFR_SLUG, AREA, AREA_SLUG
 * AOV and barriers include the built-in wizard. Other brands get text links.
 * Product-line cards use .mfr-line-card — not shop .product-card or products-hub kit articles.
 */
$entry = getManufacturerBySlug($MFR_SLUG);
if (!$entry) {
    http_response_code(404);
    echo 'Manufacturer not found';
    return;
}
$areaName = $AREA;
$areaSlugVal = $AREA_SLUG;
$brand = (string)$entry['name'];
$slug = (string)$entry['slug'];
$services = getServices();
$primary = (string)(($entry['services'][0] ?? 'fire-alarms'));
$primaryName = $services[$primary] ?? $primary;
$lines = manufacturerProductLines($entry);
$intro = manufacturerLocalIntro($entry, $areaName);
$pageTitle = manufacturerAreaTitle($brand, $areaName);
$metaDesc = manufacturerAreaMeta($entry, $areaName);
$metaKeywords = trim($brand . ' ' . $areaName . ', ' . ($entry['seo_keywords'] ?? $brand));
$canonicalUrl = url('/pages/manufacturers/' . $slug . '/' . $areaSlugVal);
$ogImage = manufacturerImageUrl($slug, $primary);
$wizardFamily = manufacturerWizardFamily($entry);
$faqs = manufacturerAreaFaqs($entry, $areaName);
$pos = 1;
$areasForBrand = manufacturerAreasFor($entry);
$foundAt = array_search($areaName, $areasForBrand, true);
if ($foundAt !== false) {
    $pos = $foundAt + 1;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => $brand . ' in ' . $areaName,
            'serviceType' => $primaryName,
            'areaServed' => $areaName,
            'provider' => ['@type' => 'LocalBusiness', 'name' => SITE_NAME, 'telephone' => PHONE],
            'url' => $canonicalUrl,
            'description' => $metaDesc,
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static function ($f) {
                return [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                ];
            }, $faqs),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Manufacturers', 'item' => url('/pages/manufacturers')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $brand, 'item' => url('/pages/manufacturers/' . $slug)],
                ['@type' => 'ListItem', 'position' => 4, 'name' => $areaName, 'item' => $canonicalUrl],
            ],
        ],
    ],
];

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<article class="mfr-area-page">
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2" aria-label="Breadcrumb">
                <a class="hover:text-white" href="<?= rtrim(SITE_URL, '/') ?>/">Home</a><span>/</span>
                <a class="hover:text-white" href="<?= url('/pages/manufacturers') ?>">Manufacturers</a><span>/</span>
                <a class="hover:text-white" href="<?= url('/pages/manufacturers/' . $slug) ?>"><?= manufacturerH($brand) ?></a><span>/</span>
                <span class="text-white/80"><?= manufacturerH($areaName) ?></span>
            </nav>
            <p class="text-xs uppercase tracking-[0.2em] text-[#ff6b00] font-semibold mb-3"><?= manufacturerH(manufacturerCoverageLabel($entry)) ?> · town <?= (int)$pos ?> of <?= count($areasForBrand) ?></p>
            <h1 class="text-4xl md:text-5xl font-semibold tracking-tight leading-tight"><?= manufacturerH($brand) ?> <span class="text-[#ff6b00]">in <?= manufacturerH($areaName) ?></span></h1>
            <?php if (manufacturerIsBarriersPartner($entry)): ?>
                <p class="mfr-partner-banner mt-4">Barriers partner: CAME. Gard barriers and gate operators are specified with Icomply.</p>
            <?php endif; ?>
            <p class="mt-6 text-lg text-white/85 leading-relaxed"><?= manufacturerH($intro) ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <?php if ($wizardFamily): ?>
                    <a class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" href="#wizard">Open quote wizard</a>
                <?php else: ?>
                    <a class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" href="#next-steps">Next steps</a>
                <?php endif; ?>
                <a class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold" href="#quote">Send details</a>
                <a class="px-6 py-3 rounded-2xl border border-white/40 font-semibold" href="<?= url('/pages/services/' . $primary) ?>"><?= manufacturerH($primaryName) ?></a>
            </div>
        </div>
        <div class="relative rounded-3xl overflow-hidden min-h-[260px] border border-white/10">
            <img src="<?= manufacturerH($ogImage) ?>" alt="<?= manufacturerH($brand . ' equipment for ' . $areaName) ?>" class="absolute inset-0 w-full h-full object-cover" width="960" height="640">
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-14">
    <p class="mfr-kicker">Product lines</p>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Ranges we name for <?= manufacturerH($brand) ?></h2>
    <p class="mt-3 text-zinc-600 max-w-3xl">Each line has its own mark. The survey confirms which one is on site in <?= manufacturerH($areaName) ?> before parts are ordered.</p>
    <?= manufacturerLineCardsHtml($entry) ?>
</section>

<?php if ($wizardFamily): ?>
    <?= manufacturerWizardHtml($entry, $areaName, $lines) ?>
<?php else: ?>
    <?= manufacturerNextStepLinksHtml($entry, $areaName) ?>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-6 py-14">
    <p class="mfr-kicker">Photographs</p>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2"><?= manufacturerH($brand) ?> and <?= manufacturerH($areaName) ?></h2>
    <p class="mt-3 text-zinc-600 max-w-3xl">Reference images. AOV kit and CAME 5m pack prices stay on the products hub so this page does not redraw those cards.</p>
    <?= manufacturerGalleryHtml($entry, $areaName) ?>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold tracking-tight text-black"><?= manufacturerH($brand) ?> in <?= manufacturerH($areaName) ?> — questions</h2>
        <div class="mt-8 space-y-4">
            <?php foreach ($faqs as $faq): ?>
                <details class="bg-white border rounded-2xl p-5" open>
                    <summary class="font-semibold cursor-pointer"><?= manufacturerH($faq['q']) ?></summary>
                    <p class="mt-3 text-sm text-zinc-700 leading-relaxed"><?= manufacturerH($faq['a']) ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-14">
    <h2 class="text-2xl font-semibold text-black"><?= manufacturerH($brand) ?> in other towns</h2>
    <p class="mt-2 text-sm text-zinc-600 mb-4"><?= manufacturerH(manufacturerCoverageLabel($entry)) ?>. <?= count($areasForBrand) ?> pages, each with its own introduction.</p>
    <?= manufacturerAreaChipsHtml($entry) ?>
</section>

<section id="quote" class="bg-[#0B1F3A] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold">Quote <?= manufacturerH($brand) ?> in <?= manufacturerH($areaName) ?></h2>
        <p class="mt-3 text-white/75">Installation is POA. If you used the wizard, the choices are copied into the message.</p>
        <form class="mt-8 space-y-4" method="post" action="<?= url('/contact') ?>" data-mfr-quote>
            <input type="hidden" name="csrf" value="<?= manufacturerH((string)($_SESSION['csrf'] ?? '')) ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input class="w-full rounded-2xl px-4 py-3 text-black" name="name" required maxlength="120" placeholder="Name" aria-label="Name">
                <input class="w-full rounded-2xl px-4 py-3 text-black" name="email" type="email" required placeholder="Email" aria-label="Email">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input class="w-full rounded-2xl px-4 py-3 text-black" name="phone" required maxlength="40" placeholder="Phone" aria-label="Phone">
                <input class="w-full rounded-2xl px-4 py-3 text-black" name="service" required value="<?= manufacturerH($primaryName) ?>" aria-label="Service">
            </div>
            <textarea class="w-full rounded-2xl px-4 py-3 text-black" name="message" required maxlength="5000" rows="5" placeholder="Postcode, what is on site, and the product line if you know it."><?= manufacturerH($brand . ' in ' . $areaName . '. ') ?></textarea>
            <button class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" type="submit">Request quote</button>
        </form>
    </div>
</section>
</article>
<?php if ($wizardFamily): ?>
<script src="<?= manufacturerH(assetUrl('/assets/js/mfr-wizard.js')) ?>" defer></script>
<?php endif; ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
