<?php
/**
 * One mainland town AOV page. Variables from renderAovTownPage().
 *
 * @var array $town
 * @var string $opening
 * @var list<array{heading:string,paragraph:string,points?:list<string>}> $sections
 * @var list<array{q:string,a:string}> $faqs
 * @var string $canonicalUrl
 */
$label = (string)$town['label'];
$pop = number_format((int)$town['population']);
$serviceUrl = url('/pages/services/aov-air-handling');
$indexUrl = url('/pages/aov');
$contactUrl = url('/contact');
$phoneHref = 'tel:' . preg_replace('/\s+/', '', (string)PHONE);

$faqEntities = [];
foreach ($faqs as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
    ];
}
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'AOV towns', 'item' => $indexUrl],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $label, 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'Service',
            'name' => 'AOV smoke control in ' . $label,
            'serviceType' => 'Automatic opening vent survey and maintenance',
            'url' => $canonicalUrl,
            'areaServed' => [
                '@type' => 'City',
                'name' => (string)$town['name'],
                'containedInPlace' => (string)$town['county'],
            ],
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'telephone' => PHONE,
                'email' => EMAIL,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '17 Woodlands Park Road, Offerton',
                    'addressLocality' => 'Stockport',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
            ],
            'description' => $metaDesc,
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => $faqEntities,
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<article class="max-w-3xl mx-auto px-6 py-14">
    <nav class="text-xs text-zinc-500 mb-6 flex flex-wrap gap-2">
        <a class="hover:text-black" href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>">Home</a>
        <span>/</span>
        <a class="hover:text-black" href="<?= htmlspecialchars($indexUrl, ENT_QUOTES, 'UTF-8') ?>">AOV towns</a>
        <span>/</span>
        <span class="text-zinc-800"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
    </nav>
    <p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Smoke control · <?= htmlspecialchars((string)$town['nation'], ENT_QUOTES, 'UTF-8') ?></p>
    <h1 class="mt-2 text-4xl md:text-5xl font-semibold tracking-tight text-black">AOV smoke control in <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="mt-4 text-sm text-zinc-600"><?= htmlspecialchars((string)$town['county'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string)$town['region'], ENT_QUOTES, 'UTF-8') ?> · population <?= htmlspecialchars($pop, ENT_QUOTES, 'UTF-8') ?></p>

    <img src="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>"
         alt="Automatic opening vent equipment. Used on the <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> page. Not a photograph of <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>."
         width="1200" height="800"
         class="mt-8 w-full h-56 md:h-72 object-cover rounded-3xl border">

    <p class="mt-8 text-lg leading-relaxed text-black"><?= htmlspecialchars($opening, ENT_QUOTES, 'UTF-8') ?></p>

    <?php foreach ($sections as $section): ?>
        <h2 class="mt-10 text-2xl font-semibold tracking-tight text-black"><?= htmlspecialchars($section['heading'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-3 leading-relaxed text-zinc-800"><?= htmlspecialchars($section['paragraph'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if (!empty($section['points'])): ?>
            <ul class="mt-4 space-y-2 text-zinc-800">
                <?php foreach ($section['points'] as $point): ?>
                    <li class="pl-4 border-l-2 border-[#ff6b00]"><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endforeach; ?>

    <h2 class="mt-10 text-2xl font-semibold tracking-tight text-black">Nearest towns on this list</h2>
    <ul class="mt-3 space-y-2">
        <?php foreach ($town['near'] as $near): ?>
            <li>
                <a class="font-semibold text-[#0B1F3A] hover:text-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/aov/' . $near['slug']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$near['name'], ENT_QUOTES, 'UTF-8') ?></a>
                <span class="text-zinc-600"> · <?= htmlspecialchars((string)$near['km'], ENT_QUOTES, 'UTF-8') ?> km</span>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2 class="mt-10 text-2xl font-semibold tracking-tight text-black">Questions about <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="mt-4 space-y-4">
        <?php foreach ($faqs as $faq): ?>
            <div class="border rounded-2xl p-5">
                <h3 class="font-semibold text-black"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-zinc-800"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="mt-8 text-sm text-zinc-600">Population and coordinates come from GeoNames (CC-BY 4.0), not from a site survey. The service overview is <a class="font-semibold text-[#ff6b00]" href="<?= htmlspecialchars($serviceUrl, ENT_QUOTES, 'UTF-8') ?>">AOV and smoke control</a>.</p>

    <div class="mt-10 bg-[#0B1F3A] text-white p-8 rounded-3xl">
        <h2 class="text-2xl font-semibold">Quote the building, not the town</h2>
        <p class="mt-3 text-white/80">Send the address in <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>, the floor count, and a photo of the panel if you have one.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="bg-[#ff6b00] px-6 py-3 rounded-2xl font-semibold" href="<?= htmlspecialchars($contactUrl, ENT_QUOTES, 'UTF-8') ?>">Request a survey</a>
            <a class="px-6 py-3 rounded-2xl border border-white/40 font-semibold" href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
