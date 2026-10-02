<?php
/**
 * Draft index of UK mainland barrier town pages.
 *
 * @var array<string, array<string, list<array<string,mixed>>>> $groups
 * @var array $meta
 * @var int $townCount
 */
$pageTitle = 'Automatic barriers by UK mainland town';
$metaDesc = 'Draft CAME partner barrier pages for ' . $townCount . ' UK mainland towns and London boroughs with population over 10,000. Not published.';
$metaKeywords = 'CAME barriers, automatic barriers, UK towns';
$metaRobots = 'noindex, nofollow';
$canonicalUrl = url('/pages/barriers/index.php');
$counts = is_array($meta['counts'] ?? null) ? $meta['counts'] : [];
$h = static function ($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-amber-50 border-b border-amber-200">
    <div class="max-w-7xl mx-auto px-6 py-3 text-sm text-amber-950">
        Draft catalogue — not in the production export or the sitemap.
    </div>
</section>
<section class="bg-[#061828] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <p class="text-xs font-bold uppercase tracking-wider text-[#ff6b00]">CAME partner · non-production</p>
        <h1 class="mt-3 text-4xl sm:text-5xl font-bold">Automatic barriers by town</h1>
        <p class="mt-4 max-w-3xl text-lg text-white/90 leading-relaxed">
            <?= $h((string)$townCount) ?> pages for UK mainland places with a published population over 10,000.
            <?= $h((string)($meta['mainland'] ?? '')) ?>
        </p>
        <dl class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div class="bg-white/10 rounded-2xl p-4"><dt class="text-white/70">Pages</dt><dd class="text-2xl font-bold"><?= $h((string)$townCount) ?></dd></div>
            <div class="bg-white/10 rounded-2xl p-4"><dt class="text-white/70">England</dt><dd class="text-2xl font-bold"><?= $h((string)($counts['by_nation']['England'] ?? 0)) ?></dd></div>
            <div class="bg-white/10 rounded-2xl p-4"><dt class="text-white/70">Scotland</dt><dd class="text-2xl font-bold"><?= $h((string)($counts['by_nation']['Scotland'] ?? 0)) ?></dd></div>
            <div class="bg-white/10 rounded-2xl p-4"><dt class="text-white/70">Wales</dt><dd class="text-2xl font-bold"><?= $h((string)($counts['by_nation']['Wales'] ?? 0)) ?></dd></div>
        </dl>
    </div>
</section>
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-12 space-y-10">
        <?php foreach ($groups as $nation => $regions): ?>
        <div>
            <h2 class="text-2xl font-bold text-[#061828]"><?= $h($nation) ?></h2>
            <?php foreach ($regions as $region => $towns): ?>
            <h3 class="mt-6 text-sm font-bold uppercase tracking-wider text-zinc-500"><?= $h($region) ?> · <?= count($towns) ?></h3>
            <div class="mt-3 flex flex-wrap gap-2">
                <?php foreach ($towns as $town): ?>
                <a href="<?= url('/pages/barriers/' . rawurlencode((string)$town['slug']) . '.php') ?>" class="px-3 py-1.5 bg-white border border-zinc-300 rounded-full text-sm font-semibold text-[#061828] hover:border-[#ff6b00] hover:text-[#ff6b00]">
                    <?= $h($town['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
