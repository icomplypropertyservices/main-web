<?php
/**
 * MANUFACTURERS_INDEX_V2 — brand directory for install + trade products.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$catalog = getManufacturerCatalog();
$services = getServices();
$featured = array_filter($catalog, fn($c) => !empty($c['featured']));
if (!$featured) {
    $featured = array_slice($catalog, 0, 12, true);
}

// Group A–Z
$byLetter = [];
foreach ($catalog as $slug => $entry) {
    $letter = strtoupper(substr($entry['name'], 0, 1));
    if (!isset($byLetter[$letter])) {
        $byLetter[$letter] = [];
    }
    $byLetter[$letter][$slug] = $entry;
}
ksort($byLetter);

// Filter by service
$byService = [];
foreach ($catalog as $slug => $entry) {
    foreach ($entry['services'] ?? [] as $s) {
        $byService[$s][$slug] = $entry;
    }
}

$pageTitle = 'Manufacturers & Brands | Fire, Electrical & Security North West';
$metaDesc = 'Browse ' . count($catalog) . '+ manufacturers we install, service and supply — fire detection, electrical, CCTV, access control, gas and related systems. Trade kits and local teams across the North West.';
$metaKeywords = 'fire alarm manufacturers, Kentec, Paxton, Hikvision, Schneider Electric, trade electrical, CCTV suppliers North West, fire safety brands';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/manufacturers/index.php');

require SITE_ROOT . '/includes/header.php';
?>

<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Manufacturers</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= count($catalog) ?> brands · Install · Service · Shop
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Manufacturers<br>
                <span class="text-[#ff6b00]">&amp; trade products</span>
            </h1>
            <p class="mt-4 text-sm text-white/70">Pair brand pages with our <?= count($services) ?> services — fire safety systems, electrical, security and more — across every North West town we cover.</p>
            <p class="mt-4 text-lg text-white/80">
                Every major brand we install and service has a dedicated page with kits, SEO content and quote CTAs —
                ready for Shopify product IDs when you sell online.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#directory" class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-semibold text-white">Browse A–Z</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold">Shop</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">Services</a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Featured brands</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Popular manufacturers</h2>
        </div>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($featured as $slug => $entry):
            $primary = $entry['services'][0] ?? 'fire-alarms';
        ?>
        <a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>"
           class="group bg-white border rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-32 bg-zinc-100 overflow-hidden">
                <img src="<?= url('/assets/images/manufacturers/' . $slug . '.jpg') ?>"
                     alt="<?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy"
                     onerror="this.src='<?= url('/assets/images/services/' . $primary . '.jpg') ?>'">
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2 line-clamp-2 flex-1"><?= htmlspecialchars($entry['blurb'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-3 text-sm font-semibold text-[#ff6b00]">View brand →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black mb-8">Browse by service</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($services as $sSlug => $sName):
                $count = count($byService[$sSlug] ?? []);
                if ($count === 0) continue;
            ?>
            <div class="bg-white border rounded-3xl p-6">
                <a href="<?= url('/pages/services/' . $sSlug . '.php') ?>" class="font-semibold text-lg text-black hover:text-[#ff6b00]">
                    <?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>
                </a>
                <p class="text-xs text-zinc-500 mt-1 mb-4"><?= $count ?> manufacturers</p>
                <div class="flex flex-wrap gap-1.5">
                    <?php
                    $i = 0;
                    foreach ($byService[$sSlug] as $slug => $entry):
                        if ($i++ >= 8) break;
                    ?>
                        <a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>"
                           class="px-2.5 py-1 bg-zinc-50 border rounded-full text-xs hover:border-[#ff6b00]">
                            <?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($count > 8): ?>
                        <span class="px-2.5 py-1 text-xs text-zinc-400">+<?= $count - 8 ?> more</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="directory" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="mb-8">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Directory</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">All <?= count($catalog) ?> manufacturers</h2>
    </div>
    <div class="flex flex-wrap gap-1.5 mb-10 sticky top-0 bg-zinc-50/95 backdrop-blur py-3 z-10 border-b">
        <?php foreach (array_keys($byLetter) as $letter): ?>
            <a href="#mfr-<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>"
               class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-semibold bg-white border hover:border-[#ff6b00]">
                <?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="space-y-12">
        <?php foreach ($byLetter as $letter => $items): ?>
            <div id="mfr-<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#0a2540] text-white font-bold text-xl flex items-center justify-center"><?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="h-px flex-1 bg-zinc-200"></div>
                    <div class="text-xs text-zinc-400"><?= count($items) ?></div>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php foreach ($items as $slug => $entry): ?>
                        <a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>"
                           class="px-4 py-3 bg-white border rounded-2xl text-sm font-medium hover:border-[#ff6b00] transition flex justify-between gap-2">
                            <span><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-[#ff6b00]">→</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
