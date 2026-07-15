<?php
/**
 * KEYWORDS_INDEX_V2 — keyword guides directory (handcrafted; generators skip overwrite).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$keywords = getMajorKeywords();
ksort($keywords);
$services = getServices();
$areas = getAreas();

// Counts per service for filter chips
$serviceCounts = [];
foreach ($keywords as $meta) {
    $svc = $meta['service'] ?? 'electrical';
    $serviceCounts[$svc] = ($serviceCounts[$svc] ?? 0) + 1;
}

$pageTitle = 'Keyword Guides | Property Compliance Topics';
$metaDesc = 'Browse ' . count($keywords) . '+ compliance keyword guides for fire alarms, EICR, CCTV, access control, gas safety and more. Installation, maintenance and certification topics across the North West.';
$metaKeywords = 'property compliance guides, EICR, fire alarm installation, CCTV, access control, gas safety, emergency lighting, North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/keywords/index.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Keyword Guides</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    SEO guides · <?= count($keywords) ?> topics
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Keyword<br>
                    <span class="text-[#ff6b00]">guides</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Explore installation, maintenance and certification topics — fire alarms, EICR, CCTV, access control and more.
                    Each guide links to local service pages across <?= count($areas) ?>+ North West towns.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#directory" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Browse guides</a>
                    <a href="<?= url('/pages/services/index.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">All services</a>
                    <a href="#quote" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Free quote</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($keywords) ?></span> keyword guides</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($services) ?></span> services covered</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns linked</div>
                </div>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-3xl p-6 md:p-8">
                <div class="text-xs uppercase tracking-[3px] text-white/50 mb-4">Jump by service</div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($services as $sSlug => $sName):
                        $cnt = $serviceCounts[$sSlug] ?? 0;
                        if ($cnt === 0) continue;
                    ?>
                        <button type="button"
                                data-jump-service="<?= htmlspecialchars($sSlug, ENT_QUOTES, 'UTF-8') ?>"
                                class="kw-jump px-4 py-2 rounded-full bg-white/10 text-sm font-medium hover:bg-[#ff6b00] transition text-left">
                            <?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>
                            <span class="text-white/50 ml-1"><?= (int)$cnt ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <p class="mt-6 text-sm text-white/50">Use search and filters below to find a specific topic fast.</p>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Practical guides', 'Installation, testing, certification and maintenance topics'],
            ['Service-linked', 'Every guide maps to a core compliance service'],
            ['Local coverage', count($areas) . '+ North West towns on related pages'],
            ['Fixed-price quotes', 'Clear scope before work starts'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars((string)$d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- DIRECTORY + FILTERS -->
<section id="directory" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Directory</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">All keyword guides</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">Search by topic or filter by service. Showing <span id="kw-visible-count"><?= count($keywords) ?></span> of <?= count($keywords) ?> guides.</p>
        </div>
        <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Manufacturers →</a>
    </div>

    <!-- Search + service filter (client-side) -->
    <div class="sticky top-0 z-10 -mx-2 px-2 py-4 mb-8 bg-zinc-50/95 backdrop-blur border-b">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-center">
            <div class="relative flex-1 max-w-xl">
                <label for="kw-search" class="sr-only">Search keyword guides</label>
                <input type="search" id="kw-search" autocomplete="off" placeholder="Search guides (e.g. EICR, Paxton, fire panel)…"
                       class="w-full border border-zinc-200 bg-white px-5 py-3.5 rounded-2xl text-sm text-black focus:outline-none focus:border-[#ff6b00] focus:ring-2 focus:ring-[#ff6b00]/20">
            </div>
            <div class="flex flex-wrap gap-2" id="kw-service-filters" role="group" aria-label="Filter by service">
                <button type="button" data-service="" class="kw-filter active px-4 py-2 rounded-full text-sm font-semibold border bg-[#0a2540] text-white border-[#0a2540]">
                    All <span class="opacity-70"><?= count($keywords) ?></span>
                </button>
                <?php foreach ($services as $sSlug => $sName):
                    $cnt = $serviceCounts[$sSlug] ?? 0;
                    if ($cnt === 0) continue;
                ?>
                    <button type="button"
                            data-service="<?= htmlspecialchars($sSlug, ENT_QUOTES, 'UTF-8') ?>"
                            class="kw-filter px-4 py-2 rounded-full text-sm font-medium border bg-white text-black border-zinc-200 hover:border-[#ff6b00] transition">
                        <?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>
                        <span class="text-zinc-400"><?= (int)$cnt ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Keyword grid -->
    <div id="kw-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach ($keywords as $slug => $meta):
            $name = $meta['name'] ?? keywordDisplayName($slug);
            $svc = $meta['service'] ?? 'electrical';
            $svcName = $services[$svc] ?? keywordDisplayName($svc);
            $href = url('/pages/keywords/' . $slug . '.php');
            $searchBlob = strtolower($name . ' ' . $slug . ' ' . $svcName . ' ' . $svc);
        ?>
            <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
               class="kw-card group bg-white border border-zinc-200 rounded-3xl p-5 hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col"
               data-service="<?= htmlspecialchars($svc, ENT_QUOTES, 'UTF-8') ?>"
               data-search="<?= htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8') ?>">
                <div class="flex items-start justify-between gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white flex items-center justify-center font-bold text-sm shrink-0">
                        <?= htmlspecialchars(strtoupper(substr($name, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider px-2 py-1 rounded-full bg-zinc-100 text-zinc-600 font-semibold">
                        <?= htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <h3 class="mt-4 font-semibold text-lg text-black leading-snug group-hover:text-[#ff6b00] transition">
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="mt-2 text-sm text-zinc-500 flex-1">Guide · local service links</p>
                <span class="mt-4 text-sm font-semibold text-[#ff6b00]">Read guide →</span>
            </a>
        <?php endforeach; ?>
    </div>

    <div id="kw-empty" class="hidden text-center py-16">
        <p class="text-lg font-semibold text-black">No guides match your search</p>
        <p class="mt-2 text-zinc-600">Try a different term or clear the service filter.</p>
        <button type="button" id="kw-reset" class="mt-6 px-6 py-3 rounded-2xl bg-[#0a2540] text-white font-semibold hover:bg-[#ff6b00] transition">Reset filters</button>
    </div>

    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<!-- PACKAGE CTA -->
<section class="max-w-7xl mx-auto px-6 pb-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Next steps</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Need the work done?</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Guides explain the topic — our engineers install, test and certify across Greater Manchester and the North West.
                Request a fixed-price quote or browse services and areas.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Standards-led install &amp; certification</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> <?= count($areas) ?>+ towns covered from Stockport</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Trade shop for kits &amp; parts</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/pages/services/index.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white font-semibold hover:bg-[#ff6b00] transition">Browse services</a>
                <a href="<?= url('/pages/areas/index.php') ?>" class="px-6 py-3 rounded-2xl border font-semibold hover:border-[#ff6b00] transition">Find your area</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk to us today</h3>
            <p class="mt-3 text-white/80">Call, WhatsApp or use the quote form — we aim to respond within 2 hours on business days.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                   class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="#quote" class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10">Quote form</a>
            </div>
        </div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request your free quote</h2>
            <p class="mt-3 text-zinc-600">All quotes are fixed-price after scope is agreed.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                    <option value="Shop / products">Shop / products</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, property type, panel brand / system details…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>

<script>
(function () {
    var searchEl = document.getElementById('kw-search');
    var grid = document.getElementById('kw-grid');
    var empty = document.getElementById('kw-empty');
    var countEl = document.getElementById('kw-visible-count');
    var resetBtn = document.getElementById('kw-reset');
    var filterBtns = document.querySelectorAll('.kw-filter');
    var jumpBtns = document.querySelectorAll('.kw-jump');
    var activeService = '';

    function applyFilters() {
        var q = (searchEl.value || '').toLowerCase().trim();
        var cards = grid.querySelectorAll('.kw-card');
        var visible = 0;
        cards.forEach(function (card) {
            var svc = card.getAttribute('data-service') || '';
            var blob = card.getAttribute('data-search') || '';
            var matchService = !activeService || svc === activeService;
            var matchSearch = !q || blob.indexOf(q) !== -1;
            var show = matchService && matchSearch;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.textContent = String(visible);
        if (empty) empty.classList.toggle('hidden', visible > 0);
        if (grid) grid.classList.toggle('hidden', visible === 0);
    }

    function setService(svc) {
        activeService = svc || '';
        filterBtns.forEach(function (btn) {
            var on = (btn.getAttribute('data-service') || '') === activeService;
            btn.classList.toggle('active', on);
            if (on) {
                btn.classList.add('bg-[#0a2540]', 'text-white', 'border-[#0a2540]', 'font-semibold');
                btn.classList.remove('bg-white', 'text-black', 'border-zinc-200', 'font-medium');
            } else {
                btn.classList.remove('bg-[#0a2540]', 'text-white', 'border-[#0a2540]', 'font-semibold');
                btn.classList.add('bg-white', 'text-black', 'border-zinc-200', 'font-medium');
            }
        });
        applyFilters();
    }

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setService(btn.getAttribute('data-service') || '');
        });
    });

    jumpBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var svc = btn.getAttribute('data-jump-service') || '';
            setService(svc);
            var dir = document.getElementById('directory');
            if (dir) dir.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    if (searchEl) {
        searchEl.addEventListener('input', applyFilters);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (searchEl) searchEl.value = '';
            setService('');
        });
    }
})();
</script>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
