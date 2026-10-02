<?php
require_once SITE_ROOT . '/includes/aov-place.php';
$placeCount = count(aovPlaces());
$keywords = function_exists('getKeywordsForService') ? getKeywordsForService('aov-air-handling') : [];
$pageTitle = 'AOV and smoke control | Automatic opening vents';
$metaDesc = 'Automatic opening vents, stair smoke vents and smoke-control panels. Supply kits with list prices. Installation POA. Quoted nationwide as fire protection. Call ' . PHONE . '.';
$metaKeywords = 'AOV, automatic opening vent, smoke vent, smoke control, EN 12101, BS 9991, SE Controls, WindowMaster';
$canonicalUrl = url('/pages/services/aov-air-handling.php');
$ogImage = url('/assets/images/services/aov-air-handling.jpg');
require SITE_ROOT . '/includes/header.php';
$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
?>
<section class="bg-[#061828] text-white">
    <div class="max-w-7xl mx-auto px-6 py-16 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <p class="text-xs uppercase tracking-[3px] text-[#ffb080]">Fire protection · nationwide</p>
            <h1 class="mt-3 text-4xl md:text-6xl font-semibold tracking-tight leading-[1.05]">Automatic opening vents.<br><span class="text-[#ff6b00]">The stair has to open.</span></h1>
            <p class="mt-5 text-lg text-white/80 max-w-xl">An AOV is a ventilator that opens on a fire signal: usually a roof hatch or façade vent at the head of a stair, sometimes a corridor or lobby opening into a smoke shaft. It is life-safety equipment, so we treat it as fire protection and quote it across the UK from our Stockport yard. We do not claim BAFE, FIRAS or approved-installer status for any brand.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" href="#quote">Get a written quote</a>
                <a class="px-6 py-3 rounded-2xl bg-white text-[#061828] font-semibold" href="<?= $h(aovPhoneHref()) ?>">Call <?= $h((string)PHONE) ?></a>
                <a class="px-6 py-3 rounded-2xl border border-white/30 font-semibold" href="#kit">Kit wizard</a>
                <a class="px-6 py-3 rounded-2xl border border-white/30 font-semibold" href="<?= $h(url('/pages/aov')) ?>"><?= (int)$placeCount ?> town pages</a>
            </div>
        </div>
        <img src="<?= $h(url('/assets/images/services/aov-air-handling.jpg')) ?>" alt="Smoke vent and AOV equipment" class="w-full h-80 object-cover rounded-3xl border border-white/10" width="640" height="320">
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-3 gap-6">
    <article class="p-6 border border-zinc-200 rounded-3xl">
        <h2 class="text-xl font-semibold">What we actually test</h2>
        <p class="mt-3 text-zinc-700">Full travel of the vents the strategy names, the manual point at the landing, battery standby, and the fire-alarm contact. A green panel with a seized roof lid is a fail.</p>
    </article>
    <article class="p-6 border border-zinc-200 rounded-3xl">
        <h2 class="text-xl font-semibold">What we will not sign</h2>
        <p class="mt-3 text-zinc-700">We do not invent a smoke-control design, a free-area calculation or a scheme certificate. If there is no fire strategy, the next step is a fire engineer, then an install.</p>
    </article>
    <article class="p-6 border border-zinc-200 rounded-3xl">
        <h2 class="text-xl font-semibold">Where the pages are</h2>
        <p class="mt-3 text-zinc-700">Dedicated pages exist for published places of 10,000 or more residents, including <a class="text-[#ff6b00] font-semibold" href="<?= $h(url('/pages/aov/manchester')) ?>">Manchester</a> and <a class="text-[#ff6b00] font-semibold" href="<?= $h(url('/pages/aov/burnley')) ?>">Burnley</a>. Smaller places are still quoted. They do not get a cloned town URL.</p>
    </article>
</section>

<section class="max-w-7xl mx-auto px-6 pb-4">
    <h2 class="text-3xl font-semibold">Standards we work against</h2>
    <p class="mt-3 text-zinc-700 max-w-3xl">BS EN 12101-2 for natural smoke ventilators, BS EN 12101-10 for power supplies, BS EN 12101-3 when the kit is a powered fan, and BS 9991 where the residential fire strategy cites it. Naming a standard is not a claim that we are accredited to it.</p>
</section>

<section class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-semibold">All <?= count(aovManufacturers()) ?> AOV manufacturers</h2>
            <p class="mt-2 text-zinc-600 max-w-3xl">Open a brand for the service page and spare kits. Wordmarks are original name plates so you can scan the set. They are not the companies’ trademark logos and they are not partnership badges.</p>
        </div>
        <a class="text-sm font-semibold text-[#ff6b00]" href="<?= $h(url('/pages/manufacturers/index.php')) ?>">Full brand directory →</a>
    </div>
    <div class="mt-6"><?= aovBrandGridHtml() ?></div>
</section>

<?= aovKitWizardHtml('UK smoke-vent enquiry') ?>

<section class="max-w-7xl mx-auto px-6 py-14">
    <h2 class="text-3xl font-semibold">Guides</h2>
    <p class="mt-2 text-zinc-600">Each guide is one job — actuator, panel, shaft, annual test — not a town with the name swapped.</p>
    <div class="mt-6 flex flex-wrap gap-2">
        <?php foreach ($keywords as $slug => $meta): ?>
            <a class="px-3 py-1.5 border border-zinc-200 rounded-full text-sm hover:border-[#ff6b00]" href="<?= $h(url('/pages/keywords/' . $slug . '.php')) ?>"><?= $h((string)($meta['name'] ?? $slug)) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<?php
aovQuoteForm(
    'Tell us the vent, not a catalogue code',
    'Phone 07517806082 if the stair vent is stuck. Otherwise send the panel brand, the town and whether you need a supply kit or an engineer.',
    'AOV and smoke control',
    'Town, postcode, stair or corridor, panel brand, stuck open/shut, roof access…'
);
require SITE_ROOT . '/includes/footer.php';
