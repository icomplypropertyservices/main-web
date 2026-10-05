<?php
require_once SITE_ROOT . '/includes/aov.php';
$places = aovPlaces();
uasort($places, static function ($a, $b) {
    return strcasecmp((string)$a['name'], (string)$b['name']);
});
$byNation = [];
foreach ($places as $place) {
    $byNation[(string)$place['nation']][] = $place;
}
$pageTitle = 'AOV by UK town | Smoke vents nationwide';
$metaDesc = 'Automatic opening vent pages for UK places with a published population of 10,000 or more. Fire-protection smoke control, kit prices, installation POA. Call ' . PHONE . '.';
$canonicalUrl = url('/pages/aov');
$ogImage = url('/assets/images/services/aov-air-handling.jpg');
require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <nav class="text-xs text-white/60 mb-4"><a class="hover:text-white" href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>">Home</a> / <a class="hover:text-white" href="<?= url('/pages/services/aov-air-handling.php') ?>">AOV</a> / Towns</nav>
        <h1 class="text-4xl md:text-5xl font-semibold tracking-tight">AOV pages for <?= count($places) ?> UK places</h1>
        <p class="mt-4 text-white/80 max-w-3xl">Each page is a place with a published usual-resident count of at least 10,000: ONS built-up areas in England and Wales (2019), NRS Scottish localities (the public 2020 list used here starts at 15,010), and Northern Ireland settlements of 10,000 or more. Smoke vents are fire protection, so the service is quoted nationwide. A town page is not a depot.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" href="<?= url('/pages/services/aov-air-handling.php') ?>">AOV service hub</a>
            <a class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', (string)PHONE), ENT_QUOTES, 'UTF-8') ?>">Call <?= htmlspecialchars((string)PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
        <label class="mt-8 block text-sm font-semibold">Find a place
            <input id="aov-town-filter" type="search" placeholder="Type a town, city or county" class="mt-2 w-full max-w-md text-black px-4 py-3 rounded-xl border-0">
        </label>
    </div>
</section>
<section class="max-w-3xl mx-auto px-6 py-14">
    <?php
    require_once SITE_ROOT . '/includes/quality-bar.php';
    echo icomplyQualityBarImages('aov-air-handling', 'Automatic opening vents and smoke control', 'q6-aov-images')['html'];
    echo icomplyQualityBarAovProseHtml();
    echo icomplyQualityBarFaqHtml(
        icomplyQualityBarAovFaqs(),
        'q6-aov-faq',
        'q6-aov-faq-jsonld',
        'Questions about AOV and smoke control'
    );
    ?>
</section>
<section class="max-w-7xl mx-auto px-6 py-12 space-y-12">
    <?php foreach ($byNation as $nation => $rows): ?>
        <section>
            <h2 class="text-2xl font-semibold text-black"><?= htmlspecialchars($nation, ENT_QUOTES, 'UTF-8') ?> <span class="text-zinc-500 text-base font-normal">(<?= count($rows) ?>)</span></h2>
            <ul class="mt-4 flex flex-wrap gap-2" data-aov-town-list>
                <?php foreach ($rows as $row): ?>
                    <li>
                        <a class="inline-block px-3 py-1.5 bg-white border border-zinc-200 rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/aov/' . rawurlencode((string)$row['slug'])) ?>"><?= htmlspecialchars((string)$row['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
</section>
<script>
document.getElementById('aov-town-filter').addEventListener('input', function (ev) {
  var q = ev.target.value.trim().toLowerCase();
  document.querySelectorAll('[data-aov-town-list] a').forEach(function (a) {
    a.parentElement.style.display = !q || a.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
  });
});
</script>
<?php
aovQuoteForm(
    'Tell us the vent, not a catalogue code',
    'Phone if the stair vent is stuck. Otherwise send the panel brand, the town and whether you need a supply kit or an engineer. Installation is price on application.',
    'AOV and smoke control',
    'Town, postcode, stair or corridor, panel brand, stuck open or shut, roof access.'
);
require SITE_ROOT . '/includes/footer.php';
?>
