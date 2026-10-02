<?php
/**
 * MANUFACTURERS_INDEX_V2 — brand directory for install + trade products.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/manufacturer-kits.php';
require_once SITE_ROOT . '/includes/manufacturer-hub.php';

$catalog = getManufacturerCatalog();
$kitIndex = manufacturerCatalogueKitIndex();
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

$barrierBrands = [];
foreach ($catalog as $slug => $entry) {
    if (!empty($entry['barrier'])) {
        $barrierBrands[$slug] = $entry;
    }
}
uasort($barrierBrands, static function (array $a, array $b): int {
    $pa = !empty($a['partner']) ? 0 : 1;
    $pb = !empty($b['partner']) ? 0 : 1;
    if ($pa !== $pb) {
        return $pa <=> $pb;
    }
    return strcasecmp((string)$a['name'], (string)$b['name']);
});

$pageTitle = 'Manufacturers & Brands | Fire, Electrical & Security';
$metaDesc = 'Browse ' . count($catalog) . '+ manufacturers we install, service and supply — fire detection, electrical, CCTV, access control, gas and related systems. Trade kits and local teams across the North West.';
$metaKeywords = 'fire alarm manufacturers, Kentec, Paxton, Hikvision, Schneider Electric, trade electrical, CCTV suppliers North West, fire safety brands';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/manufacturers');

require SITE_ROOT . '/includes/header.php';
?>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
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
                <a href="#kits" class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-semibold text-white">Trade kits</a>
                <a href="#directory" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Browse A–Z</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">Shop</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">Services</a>
            </div>
        </div>
    </div>
</section>

<section id="barriers" class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nationwide priority</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2">Barrier manufacturers</h2>
        <p class="mt-3 text-white/75 max-w-2xl">Boom and parking barriers are covered nationwide. <strong class="text-white">CAME</strong> is the partner brand — GARD packs deep-link to the products hub. Every other barrier brand is quote-only. Installation is POA.</p>
        <div class="mt-8 grid lg:grid-cols-3 gap-4">
            <?php foreach ($barrierBrands as $slug => $entry):
                $isPartner = !empty($entry['partner']);
            ?>
            <article class="<?= $isPartner ? 'lg:col-span-3 bg-white text-black' : 'bg-white/5 border border-white/10' ?> rounded-3xl p-5">
                <div class="flex items-start gap-3">
                    <?= manufacturerLogoHtml($entry['name'], $slug, 'w-12 h-12') ?>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-[#ff6b00]"><?= $isPartner ? 'Partner' : 'Barrier brand' ?></div>
                        <h3 class="font-semibold text-lg"><a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>" class="hover:text-[#ff6b00]"><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                        <div class="mt-2 flex flex-wrap gap-1.5"><?= manufacturerProductLinesHtml($entry, $isPartner ? 8 : 3) ?></div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="kits" class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Catalogue</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Trade kits with deep links</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">These brands match live catalogue SKUs. Each link opens the product page. Supply £ is the catalogue figure. Installation is POA. Brands without a SKU stay quote-only — no placeholder prices.</p>
        </div>
        <a href="<?= url('/products.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All products →</a>
    </div>
    <?php if (!$kitIndex): ?>
        <p class="text-zinc-600">No catalogue SKUs are matched to a manufacturer yet.</p>
    <?php else: ?>
    <div class="grid lg:grid-cols-2 gap-5">
        <?php foreach ($kitIndex as $slug => $kits):
            $entry = $catalog[$slug] ?? null;
            if (!$entry) continue;
        ?>
        <article class="bg-white border rounded-3xl p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-lg text-black">
                        <a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>" class="hover:text-[#ff6b00]"><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    </h3>
                    <p class="text-xs text-zinc-500 mt-1"><?= count($kits) ?> kit<?= count($kits) === 1 ? '' : 's' ?></p>
                </div>
                <a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>#products" class="text-sm font-semibold text-[#ff6b00] shrink-0">Brand page →</a>
            </div>
            <ul class="mt-4 space-y-2">
                <?php foreach (array_slice($kits, 0, 4) as $kit):
                    $href = shopifyProductUrl($kit);
                    $price = trim((string)($kit['price'] ?? ''));
                ?>
                <li>
                    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="flex items-baseline justify-between gap-3 text-sm hover:text-[#ff6b00]">
                        <span><?= htmlspecialchars((string)($kit['title'] ?? 'Kit'), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($price !== ''): ?>
                        <span class="text-[#ff6b00] font-semibold shrink-0"><?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="max-w-7xl mx-auto px-6 pb-16">
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
            <div class="h-32 bg-zinc-100 overflow-hidden relative">
                <img src="<?= htmlspecialchars(manufacturerImageUrl($slug, $primary), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?> equipment — Icomply Property Services"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy">
                <span class="absolute bottom-2 left-2"><?= manufacturerLogoHtml($entry['name'], $slug, 'w-9 h-9') ?></span>
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="mt-2 flex flex-wrap gap-1"><?= manufacturerProductLinesHtml($entry, 2) ?></div>
                <p class="text-sm text-zinc-600 mt-2 line-clamp-2 flex-1"><?= htmlspecialchars($entry['blurb'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <?php $featKits = $kitIndex[$slug] ?? []; ?>
                <?php if ($featKits): ?>
                <span class="mt-3 text-xs font-semibold text-zinc-500"><?= count($featKits) ?> catalogue kit<?= count($featKits) === 1 ? '' : 's' ?></span>
                <?php endif; ?>
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
    <label for="mfr-search" class="block text-sm font-semibold text-zinc-700 mb-2">Find a brand</label>
    <input id="mfr-search" type="search" placeholder="Kentec, Worcester Bosch, Paxton…" autocomplete="off"
           class="w-full md:max-w-md border px-5 py-3.5 rounded-2xl bg-white mb-6">
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
                    <div class="w-12 h-12 rounded-2xl bg-[#0B1F3A] text-white font-bold text-xl flex items-center justify-center"><?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="h-px flex-1 bg-zinc-200"></div>
                    <div class="text-xs text-zinc-400"><?= count($items) ?></div>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php foreach ($items as $slug => $entry):
                        $rowKits = count($kitIndex[$slug] ?? []);
                    ?>
                        <?php
                        $lineNames = array_map(static fn(array $line): string => $line['name'], manufacturerProductLines($entry));
                        ?>
                        <a href="<?= url('/pages/manufacturers/' . $slug . '.php') ?>"
                           data-mfr="<?= htmlspecialchars(strtolower($entry['name'] . ' ' . $slug . ' ' . implode(' ', $lineNames)), ENT_QUOTES, 'UTF-8') ?>"
                           class="mfr-row px-4 py-3 bg-white border rounded-2xl text-sm font-medium hover:border-[#ff6b00] transition flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2 min-w-0">
                                <?= manufacturerLogoHtml($entry['name'], $slug, 'w-8 h-8') ?>
                                <span class="min-w-0">
                                    <span class="block truncate"><?= htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="mt-1 flex flex-wrap gap-1"><?= manufacturerProductLinesHtml($entry, 2) ?></span>
                                </span>
                            </span>
                            <span class="text-[#ff6b00] shrink-0"><?= $rowKits > 0 ? $rowKits . ' kits' : '→' ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p id="mfr-search-empty" class="hidden mt-6 text-sm text-zinc-500">No brand matches that search.</p>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<script>
document.getElementById('mfr-search')?.addEventListener('input', function () {
    var q = (this.value || '').trim().toLowerCase();
    var any = false;
    document.querySelectorAll('[data-mfr]').forEach(function (el) {
        var show = q === '' || (el.getAttribute('data-mfr') || '').indexOf(q) !== -1;
        el.classList.toggle('hidden', !show);
        if (show) any = true;
    });
    document.querySelectorAll('[id^="mfr-"]').forEach(function (group) {
        if (!group.id || group.id === 'mfr-search' || group.id === 'mfr-search-empty') return;
        var visible = group.querySelector('[data-mfr]:not(.hidden)');
        group.classList.toggle('hidden', !visible);
    });
    var empty = document.getElementById('mfr-search-empty');
    if (empty) empty.classList.toggle('hidden', any || q === '');
});
</script>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
