<?php
/**
 * Emergency Lighting job-lane index.
 */
$pageTitle = 'Emergency Lighting Jobs | BS 5266 | North West';
$metaDesc = 'Emergency lighting job guides from Icomply: BS 5266 testing, installation, certificates, LED conversions and maintenance across the North West. Enquire for a POA quote.';
$metaKeywords = 'emergency lighting jobs, BS 5266, emergency lighting testing, emergency lighting installation, North West, Stockport';
$canonicalUrl = $EL_CANONICAL;
$ogImage = url('/assets/images/services/emergency-lighting.jpg');

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/80 mb-5 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span class="text-white/40">/</span>
            <span class="text-white font-medium">Emergency Lighting</span>
        </nav>
        <p class="text-xs font-bold tracking-widest uppercase text-[#ff6b00]">Job lane</p>
        <h1 class="mt-3 text-4xl sm:text-5xl font-bold tracking-tight max-w-3xl">Emergency lighting jobs</h1>
        <p class="mt-5 text-lg text-white/90 max-w-2xl"><?= htmlspecialchars($EL_HUB_COUNT, ENT_QUOTES, 'UTF-8') ?> guides for BS 5266 testing, installation, certificates and planned maintenance. Each page is a specific job. Quotes are POA after a survey of the fittings.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white">Enquire for a POA quote</a>
            <a href="<?= htmlspecialchars(url('/pages/services/emergency-lighting.php'), ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-bold">Emergency lighting service</a>
            <a href="<?= htmlspecialchars(url('/pages/areas'), ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white/40 text-white font-bold">Areas we cover</a>
        </div>
    </div>
</section>

<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#0B1F3A]">All <?= htmlspecialchars($EL_HUB_COUNT, ENT_QUOTES, 'UTF-8') ?> jobs</h2>
        <p class="mt-2 text-zinc-800 max-w-2xl">Monthly and annual tests, duration tests, central battery, self-test, LED conversions, landlord and care-home lighting, exit signs and service contracts.</p>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?= $EL_HUB_CARDS ?>
        </div>
    </div>
</section>

<section id="quote" class="bg-[#0B1F3A] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-center">Enquire for a POA quote</h2>
        <p class="mt-2 text-center text-white/90">Share the site, how many fittings, and whether you need a test, an install or a contract.</p>
        <form action="<?= htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') ?>" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($EL_CSRF, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="service" value="Emergency lighting">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            <textarea name="message" rows="4" required placeholder="Site postcode, fitting count, last test date…" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
