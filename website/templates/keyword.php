<?php
/**
 * Keyword hub — high-contrast, unique SEO content per keyword.
 * Pure PHP vars via executeTemplateVars() (no {{}} / eval).
 * RELATED_SLUG, RELATED_NAME, MANUFACTURER_TAGS, SEO_KEYWORDS,
 * KEYWORD_INTRO, KEYWORD_BODY, KEYWORD_META, KEYWORD_FOCUS_HTML, KEYWORD_FAQ_HTML,
 * KEYWORD_IMAGE, SERVICE_IMAGE
 */
$pageTitle = $KEYWORD_NAME . ' | North West';
$metaDesc = $KEYWORD_META;
$metaKeywords = $SEO_KEYWORDS;
$ogImage = $KEYWORD_IMAGE;
$canonicalUrl = url('/pages/keywords/' . $KEYWORD_SLUG . '.php');

$keywordName = $KEYWORD_NAME;
$keywordSlug = $KEYWORD_SLUG;
$serviceName = $SERVICE_NAME;
$serviceSlug = $SERVICE_SLUG;
$relatedSlug = $RELATED_SLUG;
$relatedName = $RELATED_NAME;
$allAreas = getAreas();
$allServices = getServices();

$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
    fn($t) => in_array($t, $allAreas, true)
));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => $keywordName,
            'description' => $metaDesc,
            'provider' => ['@type' => 'LocalBusiness', 'name' => SITE_NAME, 'telephone' => PHONE, 'url' => SITE_URL],
            'areaServed' => 'North West England',
            'serviceType' => $serviceName,
            'url' => $canonicalUrl,
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Guides', 'item' => url('/pages/keywords/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $keywordName, 'item' => $canonicalUrl],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<!-- HERO: solid navy + image with dark overlay for readable text -->
<section class="relative overflow-hidden bg-[#061828] text-white">
    <div class="absolute inset-0">
        <img src="<?= htmlspecialchars($KEYWORD_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="" class="w-full h-full object-cover opacity-35" loading="eager"
             onerror="this.src=$SERVICE_IMAGE">
        <div class="absolute inset-0 bg-gradient-to-r from-[#061828] via-[#061828]/95 to-[#061828]/75"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/70 mb-5 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a><span class="text-white/40">/</span>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="hover:text-white">Guides</a><span class="text-white/40">/</span>
            <span class="text-white font-medium"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">
            <?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> guide
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white max-w-3xl leading-[1.08] drop-shadow-lg">
            <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="mt-5 text-lg md:text-xl text-white max-w-2xl leading-relaxed font-medium drop-shadow">
            <?= htmlspecialchars($KEYWORD_INTRO, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white shadow-lg">Get free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($keywordName . ' quote') ?>"
               target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-bold text-white shadow-lg">WhatsApp</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold shadow-lg"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<!-- TRUST: high contrast white on zinc -->
<section class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local engineers', 'Stockport base — 150+ North West towns'],
            ['Standards-led', 'British Standards & manufacturer guidance'],
            ['Fixed quotes', 'Clear scope before work starts'],
            ['Full paperwork', 'Certificates & logbooks for compliance'],
        ];
        foreach ($trust as [$t, $d]): ?>
        <div class="flex gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#061828] text-white flex items-center justify-center font-bold shrink-0">✓</div>
            <div>
                <div class="font-bold text-[#061828]"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-sm text-zinc-800 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- MAIN CONTENT -->
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-16">
        <div class="grid lg:grid-cols-5 gap-10">
            <div class="lg:col-span-3">
                <div class="bg-white border-2 border-zinc-200 rounded-3xl p-6 md:p-8 shadow-sm">
                    <h2 class="text-2xl md:text-3xl font-bold text-[#061828] tracking-tight">
                        About <?= htmlspecialchars($keywordName, ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p class="mt-4 text-base md:text-lg text-zinc-900 leading-relaxed font-medium"><?= htmlspecialchars($KEYWORD_INTRO, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-4 text-base md:text-lg text-zinc-900 leading-relaxed"><?= htmlspecialchars($KEYWORD_BODY, ENT_QUOTES, 'UTF-8') ?></p>
                    <ul class="mt-6 space-y-3"><?= $KEYWORD_FOCUS_HTML ?></ul>
                    <p class="mt-6 text-sm text-zinc-800">
                        Part of our
                        <a href="<?= url('/pages/services/' . $SERVICE_SLUG . '.php') ?>" class="font-bold text-[#ff6b00] hover:underline"><?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?></a>
                        service · Related:
                        <a href="<?= url('/pages/keywords/' . $RELATED_SLUG . '.php') ?>" class="font-bold text-[#ff6b00] hover:underline"><?= htmlspecialchars($RELATED_NAME, ENT_QUOTES, 'UTF-8') ?></a>
                    </p>
                </div>
            </div>
            <div class="lg:col-span-2 space-y-5">
                <div class="rounded-3xl overflow-hidden border-2 border-zinc-300 shadow-md bg-zinc-200">
                    <img src="<?= htmlspecialchars($KEYWORD_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> — Icomply Property Services"
                         class="w-full h-52 object-cover" loading="lazy"
                         onerror="this.src=$SERVICE_IMAGE">
                    <div class="p-3 bg-[#061828] text-white text-sm font-semibold text-center"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="rounded-3xl overflow-hidden border-2 border-zinc-300 shadow-md bg-zinc-200">
                    <img src="<?= htmlspecialchars($SERVICE_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> by Icomply"
                         class="w-full h-40 object-cover" loading="lazy">
                    <div class="p-3 bg-white text-[#061828] text-sm font-semibold text-center border-t-2 border-zinc-200"><?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> service</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MANUFACTURERS -->
<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Brands we install &amp; service</h2>
        <p class="mt-2 text-zinc-800 max-w-2xl">Click a manufacturer for products, kits and install quotes related to <?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> and <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?>.</p>
        <div class="mt-6 flex flex-wrap gap-2"><?= $MANUFACTURER_TAGS ?></div>
    </div>
</section>

<!-- AREAS — high contrast chips -->
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> by area</h2>
        <p class="mt-2 text-zinc-800">Local landing pages for every town we cover (<?= count($allAreas) ?> areas).</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php foreach ($popularTowns as $a): ?>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . areaSlug($a) . '.php') ?>"
                   class="px-4 py-2.5 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition shadow">
                    <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 flex flex-wrap gap-2 max-h-56 overflow-y-auto">
            <?php foreach ($allAreas as $a):
                if (in_array($a, $popularTowns, true)) continue;
            ?>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . areaSlug($a) . '.php') ?>"
                   class="px-3 py-1.5 bg-white border-2 border-zinc-300 text-zinc-900 rounded-full text-xs font-medium hover:border-[#ff6b00] hover:text-[#ff6b00]">
                    <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-white border-t-2 border-zinc-200">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828] text-center mb-8"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> FAQ</h2>
        <div class="space-y-3"><?= $KEYWORD_FAQ_HTML ?></div>
        <div class="mt-10"><?= shareButtonsHtml($keywordName, $metaDesc) ?></div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-[#061828] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-center">Quote for <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-2 text-center text-white/90">Fixed-price after scope is agreed. Stockport engineers · North West coverage.</p>
        <form action="<?= url('/contact.php') ?>" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4 shadow-xl">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <select name="service" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl bg-white font-medium">
                    <option value="<?= htmlspecialchars($keywordName, ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars($keywordName, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required placeholder="Postcode, property type, brand / system…" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
