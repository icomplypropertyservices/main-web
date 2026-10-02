<?php
/**
 * AOV town index. Variables from renderAovTownIndex().
 *
 * @var list<array<string,mixed>> $towns
 * @var array<string,mixed> $catalogue
 * @var array<string,list<array<string,mixed>>> $byLetter
 * @var string $canonicalUrl
 */
$count = count($towns);
$england = 0;
$wales = 0;
$scotland = 0;
foreach ($towns as $town) {
    if ($town['nation'] === 'England') {
        $england++;
    } elseif ($town['nation'] === 'Wales') {
        $wales++;
    } else {
        $scotland++;
    }
}
$serviceUrl = url('/pages/services/aov-air-handling');
?>
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-6xl mx-auto px-6 py-14">
        <nav class="text-xs text-white/50 mb-6 flex gap-2">
            <a class="hover:text-white" href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>">Home</a>
            <span>/</span>
            <span class="text-white/80">AOV towns</span>
        </nav>
        <p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Mainland list · population over 10,000</p>
        <h1 class="mt-3 text-4xl md:text-5xl font-semibold tracking-tight">AOV pages for <?= (int)$count ?> towns</h1>
        <p class="mt-5 max-w-3xl text-lg text-white/80">Each page is one GeoNames place in England, Wales, or mainland Scotland with a recorded population over 10,000. The page states the population, the county, and the straight-line distance from Stockport. It does not invent a depot or a price.</p>
        <dl class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><dt class="text-white/60">Towns</dt><dd class="text-2xl font-semibold"><?= (int)$count ?></dd></div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><dt class="text-white/60">England</dt><dd class="text-2xl font-semibold"><?= (int)$england ?></dd></div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><dt class="text-white/60">Scotland</dt><dd class="text-2xl font-semibold"><?= (int)$scotland ?></dd></div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-4"><dt class="text-white/60">Wales</dt><dd class="text-2xl font-semibold"><?= (int)$wales ?></dd></div>
        </dl>
    </div>
</section>
<section class="max-w-6xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-semibold text-black">What was left out</h2>
    <p class="mt-3 text-zinc-700 max-w-3xl">Northern Ireland, the Isle of Wight, Anglesey, and Na h-Eileanan Siar are not on this list. Place-sections inside a larger town (a GeoNames PPLX record) are not on it either. Thornton-Cleveleys and Deeside are included because those are the names used for those settlements. Population figures are the GeoNames gazetteer totals, not a new census count.</p>
    <p class="mt-3 text-sm text-zinc-500"><?= htmlspecialchars((string)($catalogue['source'] ?? 'GeoNames'), ENT_QUOTES, 'UTF-8') ?>. <?= htmlspecialchars((string)($catalogue['distance_from'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    <p class="mt-4"><a class="font-semibold text-[#ff6b00]" href="<?= htmlspecialchars($serviceUrl, ENT_QUOTES, 'UTF-8') ?>">AOV and smoke control service</a></p>

    <nav class="mt-8 flex flex-wrap gap-2" aria-label="Letters">
        <?php foreach (array_keys($byLetter) as $letter): ?>
            <a class="px-3 py-1 border rounded-full text-sm hover:border-[#ff6b00]" href="#letter-<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </nav>

    <?php foreach ($byLetter as $letter => $group): ?>
        <h2 id="letter-<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>" class="mt-10 text-xl font-semibold text-black"><?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?></h2>
        <ul class="mt-3 columns-1 sm:columns-2 lg:columns-3 gap-x-8">
            <?php foreach ($group as $town): ?>
                <li class="py-1 break-inside-avoid">
                    <a class="text-[#0B1F3A] hover:text-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/aov/' . $town['slug']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$town['label'], ENT_QUOTES, 'UTF-8') ?></a>
                    <span class="text-zinc-500 text-sm"> · <?= htmlspecialchars(number_format((int)$town['population']), ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
