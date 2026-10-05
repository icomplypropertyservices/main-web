<?php
/**
 * Emergency Lighting job lane page.
 * Category (Emergency Lighting) → Service → Job.
 * Brand: navy #0B1F3A, orange #ff6b00. Enquire / POA only.
 */
$pageTitle = $EL_PAGE_TITLE;
$metaDesc = $EL_META;
$metaKeywords = $EL_SEO_KEYWORDS;
$ogImage = $EL_SERVICE_IMAGE;
$canonicalUrl = $EL_CANONICAL;

$allAreas = getAreas();
$allServices = getServices();
$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
    static fn($town) => in_array($town, $allAreas, true)
));

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= $EL_JSON_LD ?></script>

<section class="relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="absolute inset-0">
        <img src="<?= htmlspecialchars($EL_SERVICE_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?> — iComply Property Services" class="w-full h-full object-cover opacity-30" loading="eager">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0B1F3A] via-[#0B1F3A]/95 to-[#0B1F3A]/75"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/80 mb-5 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span class="text-white/40">/</span>
            <a href="<?= htmlspecialchars($EL_CATEGORY_URL, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Emergency Lighting</a>
            <span class="text-white/40">/</span>
            <a href="<?= htmlspecialchars($EL_SERVICE_URL, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white"><?= htmlspecialchars($EL_SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-white/40">/</span>
            <span class="text-white font-medium"><?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">
            Emergency Lighting job
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white max-w-3xl leading-[1.08]">
            <?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="mt-5 text-lg md:text-xl text-white max-w-2xl leading-relaxed font-medium">
            <?= htmlspecialchars($EL_INTRO, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white shadow-lg">Enquire for a POA quote</a>
            <a href="https://wa.me/<?= htmlspecialchars($EL_WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($EL_NAME . ' quote') ?>"
               target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-bold text-white shadow-lg">WhatsApp</a>
            <a href="tel:<?= preg_replace('/\s+/', '', $EL_PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-bold shadow-lg"><?= htmlspecialchars($EL_PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['BS 5266', 'Function tests, duration tests and logbooks'],
            ['POA quotes', 'Price follows a survey of the fittings on site'],
            ['Stockport base', 'Greater Manchester and the North West'],
            ['Paperwork', 'Certificates for the work that was done'],
        ];
        foreach ($trust as [$title, $detail]): ?>
        <div>
            <div class="font-bold text-[#0B1F3A]"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="text-sm text-zinc-800 mt-0.5"><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-16">
        <div class="bg-white border-2 border-zinc-200 rounded-3xl p-6 md:p-8 shadow-sm">
            <h2 class="text-2xl md:text-3xl font-bold text-[#0B1F3A] tracking-tight">About <?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mt-4 text-base md:text-lg text-zinc-900 leading-relaxed font-medium"><?= htmlspecialchars($EL_INTRO, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-4 text-base md:text-lg text-zinc-900 leading-relaxed"><?= htmlspecialchars($EL_BODY, ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="mt-6 space-y-3"><?= $EL_FOCUS_HTML ?></ul>
            <p class="mt-6 text-sm text-zinc-800">
                Service:
                <a href="<?= htmlspecialchars($EL_SERVICE_URL, ENT_QUOTES, 'UTF-8') ?>" class="font-bold text-[#ff6b00] hover:underline"><?= htmlspecialchars($EL_SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?></a>
                · Related job:
                <a href="<?= htmlspecialchars(url('/pages/keywords/' . $EL_RELATED_SLUG . '.php'), ENT_QUOTES, 'UTF-8') ?>" class="font-bold text-[#ff6b00] hover:underline"><?= htmlspecialchars($EL_RELATED_NAME, ENT_QUOTES, 'UTF-8') ?></a>
                · <a href="<?= htmlspecialchars(url('/pages/areas'), ENT_QUOTES, 'UTF-8') ?>" class="font-bold text-[#ff6b00] hover:underline">Areas we cover</a>
            </p>
        </div>
    </div>
</section>

<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#0B1F3A]">Brands we install and service</h2>
        <p class="mt-2 text-zinc-800 max-w-2xl">Manufacturer-aware support for <?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?>.</p>
        <div class="mt-6 flex flex-wrap gap-2"><?= $EL_MANUFACTURER_TAGS ?></div>
    </div>
</section>

<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#0B1F3A]"><?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?> by area</h2>
        <p class="mt-2 text-zinc-800">Local pages for the towns we cover. See the <a class="font-bold text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/areas'), ENT_QUOTES, 'UTF-8') ?>">areas hub</a> for the full list.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php foreach ($popularTowns as $town): ?>
                <a href="<?= htmlspecialchars(url('/pages/keywords/' . $EL_SLUG . '/' . areaSlug($town) . '.php'), ENT_QUOTES, 'UTF-8') ?>"
                   class="px-4 py-2.5 bg-[#0B1F3A] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition">
                    <?= htmlspecialchars($EL_NAME . ' in ' . $town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#0B1F3A]">Other emergency lighting jobs</h2>
        <p class="mt-2 text-zinc-800">Every job in this lane. The <a class="font-bold text-[#ff6b00] hover:underline" href="<?= htmlspecialchars($EL_CATEGORY_URL, ENT_QUOTES, 'UTF-8') ?>">emergency lighting jobs index</a> lists them together.</p>
        <div class="mt-6 flex flex-wrap gap-2"><?= $EL_SIBLINGS_HTML ?></div>
    </div>
</section>

<section class="bg-white border-t-2 border-zinc-200">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#0B1F3A] text-center mb-8"><?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?> FAQ</h2>
        <div class="space-y-3"><?= $EL_FAQ_HTML ?></div>
    </div>
</section>

<section id="quote" class="bg-[#0B1F3A] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-center">Enquire for a POA quote</h2>
        <p class="mt-2 text-center text-white/90">Tell us the site, fitting count and whether you need a test, install or remedial. We price after the survey.</p>
        <form action="<?= htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') ?>" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4 shadow-xl">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($EL_CSRF, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="service" value="<?= htmlspecialchars($EL_NAME, ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="text" name="postcode" placeholder="Site postcode" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <textarea name="message" rows="4" required placeholder="Fitting count, central battery or self-contained, last test date, access notes…" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
