<?php
/**
 * One mainland area across every coverage service.
 * Vars: $catalogue, $areaSlug, $areaLabel, $bucketId, $pricing,
 * $pageTitle, $metaDesc, $metaKeywords, $canonicalUrl, $metaRobots.
 */
require SITE_ROOT . '/includes/header.php';
?>
<!-- coverage-bucket:<?= htmlspecialchars($bucketId, ENT_QUOTES, 'UTF-8') ?> -->
<section class="bg-[#0B1F3A] text-white py-16">
    <div class="max-w-6xl mx-auto px-6">
        <p class="text-[#ff6b00] text-xs tracking-[.2em] uppercase font-semibold">Mainland UK · <?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-3">Property compliance in <?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="mt-4 text-white/80 max-w-2xl">Service slots for <?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?>. Guide prices below are the published figures. Other work is priced after a survey.</p>
    </div>
</section>
<section class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-extrabold mb-4">Guide prices</h2>
    <ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <?php foreach ($pricing as $row): ?>
        <li class="border rounded-2xl p-5 bg-white">
            <p class="text-sm text-zinc-500"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-3xl font-extrabold mt-1"><?= htmlspecialchars($row['display'], ENT_QUOTES, 'UTF-8') ?></p>
        </li>
        <?php endforeach; ?>
    </ul>
    <h2 class="text-2xl font-extrabold mb-4">Services in <?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?></h2>
    <ul class="grid md:grid-cols-2 gap-4">
        <?php foreach ($catalogue as $slug => $row): ?>
        <?php
            $cell = coverageCell($slug, $areaSlug);
            $price = coveragePriceForService($slug);
            $href = url(coverageServiceAreaPath($slug, $areaSlug));
        ?>
        <li class="border rounded-2xl p-5 bg-white">
            <a class="font-semibold text-lg hover:text-[#ff6b00]" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></a>
            <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($price['display'] ?? 'POA', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($cell['ready'] ? 'Local copy ready' : 'Slot open', ENT_QUOTES, 'UTF-8') ?></p>
        </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
