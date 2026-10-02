<?php
/**
 * One service in one mainland area.
 * Vars: $row, $service, $areaSlug, $areaLabel, $cell, $price, $bucketId, $pricing,
 * $pageTitle, $metaDesc, $metaKeywords, $canonicalUrl, $metaRobots.
 */
require SITE_ROOT . '/includes/header.php';
?>
<!-- coverage-bucket:<?= htmlspecialchars($bucketId, ENT_QUOTES, 'UTF-8') ?> -->
<section class="bg-[#0B1F3A] text-white py-16">
    <div class="max-w-6xl mx-auto px-6">
        <p class="text-[#ff6b00] text-xs tracking-[.2em] uppercase font-semibold"><?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-3"><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($price): ?>
        <p class="mt-4 text-2xl font-bold"><?= htmlspecialchars($price['display'], ENT_QUOTES, 'UTF-8') ?> <span class="text-base font-medium text-white/70">guide price</span></p>
        <?php else: ?>
        <p class="mt-4 text-white/80">Price on application after scope.</p>
        <?php endif; ?>
    </div>
</section>
<section class="max-w-6xl mx-auto px-6 py-12">
    <p class="text-lg text-zinc-700 leading-relaxed max-w-3xl"><?= htmlspecialchars($cell['intro'], ENT_QUOTES, 'UTF-8') ?></p>
    <p class="mt-6">
        <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url($row['hub']), ENT_QUOTES, 'UTF-8') ?>">Open the <?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?> hub</a>
        · <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url(coverageAreaPath($areaSlug)), ENT_QUOTES, 'UTF-8') ?>">All services in <?= htmlspecialchars($areaLabel, ENT_QUOTES, 'UTF-8') ?></a>
    </p>
    <h2 class="text-2xl font-extrabold mt-12 mb-4">Published guide prices</h2>
    <ul class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($pricing as $prow): ?>
        <li class="border rounded-2xl p-5 bg-white">
            <p class="text-sm text-zinc-500"><?= htmlspecialchars($prow['label'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-3xl font-extrabold mt-1"><?= htmlspecialchars($prow['display'], ENT_QUOTES, 'UTF-8') ?></p>
        </li>
        <?php endforeach; ?>
    </ul>
    <p class="mt-10"><a class="accent-btn inline-block px-8 py-3 rounded-2xl font-semibold" href="<?= htmlspecialchars(url('/contact'), ENT_QUOTES, 'UTF-8') ?>">Request a survey</a></p>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
