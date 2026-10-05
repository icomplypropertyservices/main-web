<?php
/** @var array $place @var array $article */
require_once SITE_ROOT . '/includes/aov-brands.php';
$pageTitle = $article['title'] . ' | iComply';
$metaDesc = $article['meta'];
$metaKeywords = 'AOV ' . $place['name'] . ', smoke vent ' . $place['name'] . ', automatic opening vent ' . $place['region'] . ', smoke control ' . $place['nation'];
$canonicalUrl = url('/pages/aov/' . $place['slug']);
$ogImage = url('/assets/images/services/aov-air-handling.jpg');
require SITE_ROOT . '/includes/header.php';
$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
$pop = number_format((int)$place['pop']);
?>
<article class="bg-white">
    <header class="bg-[#0B1F3A] text-white">
        <div class="max-w-7xl mx-auto px-6 py-14">
            <nav class="text-xs text-white/60 mb-4" aria-label="Breadcrumb">
                <a class="hover:text-white" href="<?= $h(rtrim(SITE_URL, '/') . '/') ?>">Home</a>
                <span> / </span>
                <a class="hover:text-white" href="<?= $h(url('/pages/services/aov-air-handling.php')) ?>">AOV</a>
                <span> / </span>
                <a class="hover:text-white" href="<?= $h(url('/pages/aov')) ?>">Towns</a>
                <span> / </span>
                <span class="text-white"><?= $h((string)$place['name']) ?></span>
            </nav>
            <p class="text-xs uppercase tracking-[3px] text-[#ffb080]"><?= $h((string)$place['nation']) ?> · <?= $h((string)$place['region']) ?> · fire protection</p>
            <h1 class="mt-3 text-4xl md:text-5xl font-semibold tracking-tight max-w-3xl"><?= $h($article['h1']) ?></h1>
            <p class="mt-5 text-lg text-white/80 max-w-3xl"><?= $h($article['intro']) ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" href="#quote">Quote this building</a>
                <a class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold" href="<?= $h(aovPhoneHref()) ?>">Call <?= $h((string)PHONE) ?></a>
                <a class="px-6 py-3 rounded-2xl border border-white/30 font-semibold" href="#kit">Kit prices</a>
            </div>
        </div>
    </header>

    <section class="max-w-7xl mx-auto px-6 py-12 grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            <?php foreach ($article['modules'] as $mod): ?>
                <section>
                    <h2 class="text-2xl font-semibold text-black"><?= $h($mod[0]) ?></h2>
                    <p class="mt-3 text-zinc-700 leading-relaxed"><?= $h($mod[1]) ?></p>
                </section>
            <?php endforeach; ?>
            <?php if ($article['extra'] !== ''): ?>
                <section class="p-6 bg-[#fff7f0] border border-[#ffd7b8] rounded-3xl">
                    <h2 class="text-2xl font-semibold text-black">What changes the visit in <?= $h((string)$place['name']) ?></h2>
                    <p class="mt-3 text-zinc-800 leading-relaxed"><?= $h($article['extra']) ?></p>
                </section>
            <?php endif; ?>
            <p class="text-sm text-zinc-500">Automatic opening vents are life-safety smoke control, so this sits with fire protection and is quoted across the UK. Population figures are the published resident counts named above. They are not a survey of vents in <?= $h((string)$place['name']) ?>.</p>
        </div>
        <aside class="bg-zinc-50 border border-zinc-200 rounded-3xl p-6 h-fit">
            <h2 class="font-semibold text-black">Published figures</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-zinc-500">Place</dt><dd class="font-semibold"><?= $h((string)$place['name']) ?></dd></div>
                <div><dt class="text-zinc-500">Usual residents</dt><dd class="font-semibold"><?= $h($pop) ?> (<?= $h((string)$place['year']) ?>)</dd></div>
                <div><dt class="text-zinc-500">Where</dt><dd class="font-semibold"><?= $h((string)$place['region']) ?>, <?= $h((string)$place['nation']) ?></dd></div>
                <div><dt class="text-zinc-500">Source</dt><dd><?= $h((string)$place['source']) ?></dd></div>
            </dl>
            <?php if ($article['nearby']): ?>
                <h3 class="mt-6 font-semibold text-black">Other places in <?= $h((string)$place['region']) ?></h3>
                <ul class="mt-2 space-y-2 text-sm">
                    <?php foreach ($article['nearby'] as $near): ?>
                        <li><a class="text-[#ff6b00] font-semibold" href="<?= $h(url('/pages/aov/' . $near['slug'])) ?>"><?= $h($near['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a class="mt-6 inline-block text-sm font-semibold text-[#0B1F3A]" href="<?= $h(url('/pages/services/aov-air-handling.php')) ?>">Full AOV service →</a>
        </aside>
    </section>
</article>

<section class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-3xl font-semibold text-black">Manufacturers we will work on in <?= $h((string)$place['name']) ?></h2>
    <p class="mt-2 text-zinc-600 max-w-3xl">Every brand on our AOV list. Open the brand page for spares. The wordmark is an original name plate, not the manufacturer’s logo artwork, and it is not an accreditation.</p>
    <div class="mt-6"><?= aovBrandGridHtml() ?></div>
</section>

<?= aovKitWizardHtml((string)$place['name']) ?>

<?php
aovQuoteForm(
    'Quote smoke vents in ' . $place['name'],
    'Tell us the core, the panel brand and whether the vent is stuck. ' . $place['name'] . ' is quoted as fire-protection work. Travel is on the quote when the site is not a short run from Stockport.',
    'AOV in ' . $place['name'],
    'Postcode, panel brand, stuck open or shut, roof access, photo notes…'
);
require SITE_ROOT . '/includes/footer.php';
