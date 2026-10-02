<?php
/**
 * Coverage matrix index. Vars: $catalogue, $buckets, $areas, $pricing,
 * $pageTitle, $metaDesc, $metaKeywords, $canonicalUrl, $metaRobots.
 */
require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#0B1F3A] text-white py-16">
    <div class="max-w-6xl mx-auto px-6">
        <p class="text-[#ff6b00] text-xs tracking-[.2em] uppercase font-semibold">Coverage index</p>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-3">Mainland UK service coverage</h1>
        <p class="mt-4 text-white/80 max-w-2xl">Every mainland area slug has a slot for every service hub. Local copy is filled one area bucket at a time. This index stays out of the live sitemap.</p>
        <p class="mt-6 text-sm text-white/70">Areas <?= count($areas) ?> · Services <?= count($catalogue) ?> · Buckets <?= count($buckets) ?></p>
    </div>
</section>
<section class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-extrabold mb-4">Guide prices</h2>
    <ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($pricing as $row): ?>
        <li class="border rounded-2xl p-5 bg-white">
            <p class="text-sm text-zinc-500"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-3xl font-extrabold mt-1"><?= htmlspecialchars($row['display'], ENT_QUOTES, 'UTF-8') ?></p>
        </li>
        <?php endforeach; ?>
    </ul>
    <h2 class="text-2xl font-extrabold mt-12 mb-4">Services</h2>
    <ul class="divide-y border rounded-2xl bg-white">
        <?php foreach ($catalogue as $slug => $row): ?>
        <?php $price = coveragePriceForService($slug); ?>
        <li class="px-5 py-3 flex flex-wrap gap-3 justify-between">
            <span>
                <a class="font-semibold text-[#0B1F3A] hover:text-[#ff6b00]" href="<?= htmlspecialchars(url($row['hub']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></a>
                <span class="text-xs uppercase tracking-wide text-zinc-400"><?= htmlspecialchars($row['group'], ENT_QUOTES, 'UTF-8') ?></span>
            </span>
            <span class="text-sm text-zinc-600"><?= htmlspecialchars($price['display'] ?? 'POA', ENT_QUOTES, 'UTF-8') ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
