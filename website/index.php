<?php
/**
 * Homepage — conversion-focused overhaul with services, areas, shop cards, trust, quote.
 */
require_once __DIR__ . '/config.php';
require_once SITE_ROOT . '/includes/shopify.php';

$pageTitle = 'Property Compliance Experts | North West';
$metaDesc = 'Icomply Property Services — EICR, fire alarms, gas safety, emergency lighting, CCTV & access control across Greater Manchester and the North West. Free quotes. Trade shop.';
$metaKeywords = 'property compliance Manchester, EICR Stockport, fire alarm installation, gas safety certificate, emergency lighting, CCTV installation North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$services = getServices();
$areas = getAreas();
$catalog = getShopCatalog();
$featuredProducts = array_slice($catalog['products'], 0, 4);
$shopCollections = array_slice($catalog['collections'], 0, 4);

$trust = [
    ['title' => 'Local engineers', 'text' => 'Based in Stockport — covering 150+ North West towns'],
    ['title' => 'Standards-led', 'text' => 'BS 5839, BS 5266, BS 7671, gas safety & more'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope, documentation and certification'],
    ['title' => 'Trade shop', 'text' => 'Kits & parts with Shopify checkout when live'],
];

$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
    function ($t) use ($areas) {
        return in_array($t, $areas, true);
    }
));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
$homeUrl = rtrim(SITE_URL, '/') . '/';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-16 md:py-24 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Greater Manchester &amp; North West
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Property compliance.<br>
                <span class="text-[#ff6b00]">Installed, tested, certified.</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                Electrical, fire alarms, gas, emergency lighting, AOV, nurse call, CCTV and access control —
                plus a trade shop for kits and parts.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get free quote</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Shop products</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
                   class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
            <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                <div><span class="text-white font-semibold text-xl block"><?= count($services) ?></span> core services</div>
                <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns covered</div>
                <div><span class="text-white font-semibold text-xl block">Same-week</span> appointments*</div>
            </div>
            <p class="mt-3 text-[11px] text-white/40">*Subject to engineer capacity and site access.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <?php
            $heroCards = array_slice($services, 0, 4, true);
            foreach ($heroCards as $slug => $name):
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[140px] bg-white/5 hover:border-[#ff6b00] transition">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition" loading="lazy"
                     onerror="this.style.display='none'">
                <div class="relative p-5 h-full flex flex-col justify-end">
                    <div class="font-semibold text-white text-lg leading-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-xs text-white/70 mt-1">View service →</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TRUST STRIP -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($trust as $t): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($t['text'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- SERVICES GRID -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Services</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Everything for property compliance</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">From landlord certificates to commercial fire systems — install, maintain and certify.</p>
        </div>
        <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($services as $slug => $name):
            $blurb = getServiceBlurb($slug, true);
            $img = url('/assets/images/services/' . $slug . '.jpg');
        ?>
        <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
           class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-36 bg-zinc-100 overflow-hidden">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy"
                     onerror="this.parentElement.style.display='none'">
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-4 text-sm font-semibold text-[#ff6b00]">Explore →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- AUDIENCE CARDS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Who we help</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Built for your property type</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Landlord portfolios, commercial estates or multi-service packages — pick the path that fits.</p>
            </div>
            <a href="<?= url('/pages/resources/index.php') ?>" class="text-sm font-semibold text-zinc-500 hover:text-[#ff6b00] transition">Resources →</a>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            <a href="<?= url('/pages/landlords.php') ?>"
               class="group bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="w-12 h-12 rounded-2xl bg-[#0a2540]/10 text-[#0a2540] font-bold flex items-center justify-center text-lg group-hover:bg-[#ff6b00] group-hover:text-white transition">L</div>
                <h3 class="mt-5 font-semibold text-xl text-black tracking-tight">Landlords &amp; agents</h3>
                <p class="mt-2 text-sm text-zinc-600 flex-1">EICR, gas CP12/CP44, fire alarms and emergency lighting for single lets, HMOs and multi-property portfolios.</p>
                <span class="mt-5 text-sm font-semibold text-[#ff6b00]">Landlord compliance →</span>
            </a>
            <a href="<?= url('/pages/commercial.php') ?>"
               class="group bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="w-12 h-12 rounded-2xl bg-[#0a2540]/10 text-[#0a2540] font-bold flex items-center justify-center text-lg group-hover:bg-[#ff6b00] group-hover:text-white transition">C</div>
                <h3 class="mt-5 font-semibold text-xl text-black tracking-tight">Commercial &amp; FM</h3>
                <p class="mt-2 text-sm text-zinc-600 flex-1">Multi-site fire, electrical, AOV, nurse call, CCTV and access control with planned maintenance for facilities teams.</p>
                <span class="mt-5 text-sm font-semibold text-[#ff6b00]">Commercial services →</span>
            </a>
            <a href="<?= url('/pages/packages.php') ?>"
               class="group bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="w-12 h-12 rounded-2xl bg-[#0a2540]/10 text-[#0a2540] font-bold flex items-center justify-center text-lg group-hover:bg-[#ff6b00] group-hover:text-white transition">P</div>
                <h3 class="mt-5 font-semibold text-xl text-black tracking-tight">Compliance packages</h3>
                <p class="mt-2 text-sm text-zinc-600 flex-1">Bundle landlord essentials, fire, security or full FM into one fixed-scope quote and visit schedule.</p>
                <span class="mt-5 text-sm font-semibold text-[#ff6b00]">View packages →</span>
            </a>
        </div>
    </div>
</section>

<!-- MANUFACTURERS TEASER -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Brands</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Manufacturers we install &amp; sell</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">Dedicated brand pages with trade kits, install quotes and local coverage — <?= count(getManufacturerCatalog()) ?>+ manufacturers.</p>
        </div>
        <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php
        $homeMfr = array_filter(getManufacturerCatalog(), fn($c) => !empty($c['featured']));
        if (!$homeMfr) {
            $homeMfr = array_slice(getManufacturerCatalog(), 0, 16, true);
        }
        foreach (array_slice($homeMfr, 0, 20, true) as $mSlug => $mEntry):
        ?>
            <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00] transition">
                <?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- SHOP TEASER -->
<section class="bg-zinc-100 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Shop</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Products &amp; trade kits</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Shopify-ready cards and Buy Button mounts — shop fire, electrical, security and emergency lighting gear.</p>
            </div>
            <a href="<?= url('/shop/index.php') ?>" class="inline-flex px-5 py-2.5 rounded-full bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Visit shop</a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <?php foreach ($shopCollections as $col): ?>
                <?= shopifyCollectionCardHtml($col) ?>
            <?php endforeach; ?>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($featuredProducts as $p): ?>
                <?= shopifyProductCardHtml($p, true) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- AREAS -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Serving <?= count($areas) ?>+ towns</h2>
            <p class="mt-3 text-zinc-600">Local response across Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria. Pick a town for full service links.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php foreach ($popularTowns as $town): ?>
                    <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]"><?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">View all areas →</a>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Need a compliance package?</h3>
            <p class="mt-3 text-white/80">Combine EICR, fire alarms, emergency lighting and gas safety into one visit schedule for landlords and facilities teams.</p>
            <ul class="mt-6 space-y-3 text-sm text-white/90">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Fixed-price multi-service quotes</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Full documentation for audits &amp; insurers</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Maintenance contracts available</li>
            </ul>
            <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold">Start your quote</a>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How it works</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            $steps = [
                ['1', 'Tell us the job', 'Service, postcode, panel brand or system type — via form, phone or WhatsApp.'],
                ['2', 'Get a fixed quote', 'We confirm scope, standards and timeline. No jargon, clear price.'],
                ['3', 'We deliver & certify', 'Engineers attend, complete the work and issue documentation.'],
            ];
            foreach ($steps as [$n, $t, $d]): ?>
            <div class="text-center px-4">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once SITE_ROOT . '/includes/testimonials.php';
echo testimonialsSectionHtml();
?>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request your free quote</h2>
            <p class="mt-3 text-zinc-600">We aim to respond within 2 hours on business days. All quotes are fixed-price after scope is agreed.</p>
        </div>

        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm" aria-label="Free quote form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quote-name" class="sr-only">Full name</label>
                    <input id="quote-name" type="text" name="name" placeholder="Full name" required aria-required="true" maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="name">
                </div>
                <div>
                    <label for="quote-email" class="sr-only">Email</label>
                    <input id="quote-email" type="email" name="email" placeholder="Email" required aria-required="true" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="email">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quote-phone" class="sr-only">Phone</label>
                    <input id="quote-phone" type="tel" name="phone" placeholder="Phone" required aria-required="true" maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="tel">
                </div>
                <div>
                    <label for="quote-service" class="sr-only">Service</label>
                    <select id="quote-service" name="service" required aria-required="true" class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                        <option value="">Select service…</option>
                        <?php foreach ($services as $slug => $name): ?>
                            <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                        <option value="Shop / products">Shop / products</option>
                        <option value="Multi-service package">Multi-service package</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="quote-message" class="sr-only">Message</label>
                <textarea id="quote-message" name="message" rows="4" required aria-required="true" maxlength="5000" placeholder="Postcode, property type, panel brand / system details…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            </div>
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

<section class="max-w-3xl mx-auto px-6 py-10">
    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?= shopifyBuyButtonScript() ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
