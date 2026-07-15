<?php
/**
 * Multi-service compliance packages — Landlord, Fire, Security, Full FM.
 * Pricing shown as "from" / POA only (no fabricated fixed prices).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/partials.php';

$pageTitle = 'Service Packages | Landlord, Fire Safety, Security & Projects';
$metaDesc = 'Multi-service packages for landlords and facilities teams: Landlord Essentials, Fire Safety (incl. FRA), Security, Full FM and project/refurb support. From / POA. Free quotes across the North West.';
$metaKeywords = 'compliance packages, fire risk assessment package, landlord compliance, fire package, security package, void renovation package, FM North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$services = getServices();
$areas = getAreas();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$waBase = 'https://wa.me/' . WHATSAPP;
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

/**
 * Packages: honest "from" / POA only — no invented catalogue prices.
 * Related service slugs link to existing hub pages.
 */
$packages = [
    [
        'id' => 'landlord',
        'name' => 'Landlord Essentials',
        'tagline' => 'Core certificates for rented residential stock',
        'badge' => 'Most popular',
        'highlight' => true,
        'price_label' => 'From',
        'price' => 'POA',
        'price_note' => 'Scoped per property / portfolio — fixed quote after survey',
        'ideal' => 'Private landlords, letting agents and HMO operators',
        'includes' => [
            'EICR (Electrical Installation Condition Report)',
            'Gas safety certificate (CP12 / CP44 as required)',
            'Smoke & heat / CO alarm checks where applicable',
            'Emergency lighting visual / test if fitted',
            'Single documentation pack for tenancy / agent files',
            'Coordinated visit planning to minimise access days',
        ],
        'service_slugs' => ['electrical', 'gas-systems', 'emergency-lighting', 'fire-alarms'],
        'wa_text' => 'Hi Icomply, I need a quote for the Landlord Essentials package',
    ],
    [
        'id' => 'fire',
        'name' => 'Fire Package',
        'tagline' => 'Life-safety systems design, test & certification',
        'badge' => 'Life safety',
        'highlight' => false,
        'price_label' => 'From',
        'price' => 'POA',
        'price_note' => 'Depends on system type, points and building complexity',
        'ideal' => 'Flats, commercial units, care settings and multi-occupancy sites',
        'includes' => [
            'Fire alarm service / inspection to BS 5839',
            'Emergency lighting testing to BS 5266',
            'AOV / smoke-control checks where fitted',
            'Defect report with prioritised recommendations',
            'Certification suitable for insurers & audits',
            'Optional design / upgrade quote if systems fail',
        ],
        'service_slugs' => ['fire-alarms', 'emergency-lighting', 'aov-air-handling'],
        'wa_text' => 'Hi Icomply, I need a quote for the Fire Package',
    ],
    [
        'id' => 'security',
        'name' => 'Security Package',
        'tagline' => 'Integrated CCTV, access and intrusion cover',
        'badge' => 'Site security',
        'highlight' => false,
        'price_label' => 'From',
        'price' => 'POA',
        'price_note' => 'Quoted from site survey — cameras, doors & monitoring options',
        'ideal' => 'Offices, warehouses, retail, blocks and managed estates',
        'includes' => [
            'CCTV design / health-check or install options',
            'Access control review or new door hardware',
            'Door entry / intercom integration where needed',
            'Intruder alarm service or new PD 6662 system',
            'Remote viewing / user setup guidance',
            'One point of contact for all security trades',
        ],
        'service_slugs' => ['cctv', 'access-control', 'door-entry', 'intercoms', 'intruder-alarm'],
        'wa_text' => 'Hi Icomply, I need a quote for the Security Package',
    ],
    [
        'id' => 'full-fm',
        'name' => 'Full FM',
        'tagline' => 'End-to-end compliance for facilities & portfolios',
        'badge' => 'Enterprise',
        'highlight' => false,
        'price_label' => '',
        'price' => 'POA',
        'price_note' => 'Bespoke SLA — multi-site schedules, planned maintenance & callouts',
        'ideal' => 'FM teams, housing associations, care groups and multi-site operators',
        'includes' => [
            'Electrical, fire, emergency lighting & gas programmes',
            'AOV / air handling and nurse call where required',
            'Security systems (CCTV, access, door entry, alarms)',
            'Planned preventative maintenance (PPM) calendar',
            'Audit-ready documentation & single account contact',
            'Reactive callout options within agreed SLAs',
        ],
        'service_slugs' => [
            'electrical', 'fire-alarms', 'emergency-lighting', 'gas-systems',
            'aov-air-handling', 'nurse-call', 'cctv', 'access-control',
            'door-entry', 'intercoms', 'intruder-alarm',
        ],
        'wa_text' => 'Hi Icomply, I need a quote for the Full FM compliance package',
    ],
];

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <span class="text-white/80">Packages</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Multi-service · Landlords &amp; FM
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Compliance<br>
                <span class="text-[#ff6b00]">packages</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Bundle electrical, fire, gas, emergency lighting and security into one schedule —
                one engineer plan, one documentation pack, one point of contact across
                <?= count($areas) ?>+ North West towns.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#packages" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">View packages</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Free quote</a>
                <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a multi-service compliance package quote') ?>"
                   target="_blank" rel="noopener"
                   class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<?= sectionTrustStrip([
    ['Honest pricing', 'Shown as From / POA — fixed quote after we agree scope'],
    ['One visit plan', 'Combine services where practical to cut access days'],
    ['Audit-ready packs', 'Certificates & reports in one place for insurers'],
    ['Local engineers', 'Stockport-based team covering Greater Manchester & NW'],
]) ?>

<!-- PACKAGES GRID -->
<section id="packages" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Choose a package</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Four ways to stay compliant</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Packages are starting points — we tailor scope to your buildings, system brands and renewals.
                Prices are <strong class="text-black">from / POA</strong> only; you always get a fixed quote before work starts.
            </p>
        </div>
        <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Browse single services →</a>
    </div>

    <div class="grid md:grid-cols-2 gap-6 lg:gap-8">
        <?php foreach ($packages as $pkg):
            $waUrl = $waBase . '?text=' . rawurlencode($pkg['wa_text']);
            $quoteHref = url('/contact.php') . '?service=' . rawurlencode($pkg['name'] . ' package');
            $border = $pkg['highlight']
                ? 'border-[#ff6b00] shadow-lg ring-1 ring-[#ff6b00]/20'
                : 'border-zinc-200 hover:border-[#ff6b00]';
        ?>
        <article id="<?= htmlspecialchars($pkg['id'], ENT_QUOTES, 'UTF-8') ?>"
                 class="bg-white border rounded-3xl overflow-hidden flex flex-col transition <?= $border ?>">
            <div class="p-6 md:p-8 flex-1 flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="inline-block text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full <?= $pkg['highlight'] ? 'bg-[#ff6b00] text-white' : 'bg-zinc-100 text-zinc-700' ?>">
                            <?= htmlspecialchars($pkg['badge'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-black mt-3">
                            <?= htmlspecialchars($pkg['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        <p class="mt-1 text-zinc-600"><?= htmlspecialchars($pkg['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="text-right shrink-0">
                        <?php if ($pkg['price_label'] !== ''): ?>
                            <div class="text-xs uppercase tracking-wider text-zinc-500"><?= htmlspecialchars($pkg['price_label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        <div class="text-2xl font-semibold text-[#0a2540]"><?= htmlspecialchars($pkg['price'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>

                <p class="mt-3 text-xs text-zinc-500"><?= htmlspecialchars($pkg['price_note'], ENT_QUOTES, 'UTF-8') ?></p>

                <div class="mt-5 text-sm">
                    <span class="font-semibold text-black">Ideal for:</span>
                    <span class="text-zinc-600"> <?= htmlspecialchars($pkg['ideal'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <ul class="mt-5 space-y-2.5 text-sm text-zinc-700 flex-1">
                    <?php foreach ($pkg['includes'] as $item): ?>
                        <li class="flex gap-2">
                            <span class="text-[#ff6b00] font-bold shrink-0">✓</span>
                            <span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="mt-6">
                    <div class="text-xs uppercase tracking-wider text-zinc-500 mb-2">Related services</div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($pkg['service_slugs'] as $sSlug):
                            if (!isset($services[$sSlug])) {
                                continue;
                            }
                        ?>
                            <a href="<?= url('/pages/services/' . rawurlencode($sSlug) . '.php') ?>"
                               class="px-3 py-1.5 bg-zinc-50 border border-zinc-200 rounded-full text-xs font-medium text-black hover:border-[#ff6b00] hover:text-[#ff6b00] transition">
                                <?= htmlspecialchars($services[$sSlug], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote"
                       class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 text-white font-semibold text-sm"
                       data-package="<?= htmlspecialchars($pkg['name'], ENT_QUOTES, 'UTF-8') ?>">
                        Request quote
                    </a>
                    <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener"
                       class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold text-sm">
                        WhatsApp
                    </a>
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                       class="px-6 py-3 rounded-2xl border border-zinc-200 hover:border-[#0a2540] font-semibold text-sm text-black">
                        Call
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- HOW PACKAGES WORK -->
<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-4">How package quoting works</h2>
        <p class="text-center text-zinc-600 max-w-2xl mx-auto mb-12">
            We never publish fake fixed prices online — every building is different.
            You get a clear, fixed-price quote once scope is agreed.
        </p>
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            $steps = [
                ['1', 'Tell us the package', 'Pick Landlord Essentials, Fire, Security or Full FM — plus postcode and property type.'],
                ['2', 'We scope & quote', 'We confirm systems, standards and access. Pricing is From / POA until the fixed quote is issued.'],
                ['3', 'Deliver & certify', 'Engineers complete the works, issue certificates and hand over an audit-ready pack.'],
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

<!-- ALL SERVICES LINKS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Or go à la carte</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Prefer a single service?</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Every package is built from our core services. Open a hub for manufacturers, local pages and standalone quotes.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php foreach ($services as $slug => $name): ?>
                    <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">All services →</a>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk packages today</h3>
            <p class="mt-3 text-white/80">Call, WhatsApp or use the quote form — we aim to respond within 2 hours on business days.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a compliance package quote') ?>"
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
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request a package quote</h2>
            <p class="mt-3 text-zinc-600">Tell us which package and your postcode — we’ll return a fixed-price proposal after scope is agreed. No obligation.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" id="package-service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select package…</option>
                    <option value="Landlord Essentials package">Landlord Essentials</option>
                    <option value="Fire Package">Fire Package</option>
                    <option value="Security Package">Security Package</option>
                    <option value="Full FM package">Full FM</option>
                    <option value="Multi-service package">Custom multi-service</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> (single)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="Postcode, number of properties, system brands, renewal dates…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit package request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm">
            <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a compliance package quote') ?>"
               target="_blank" rel="noopener"
               class="px-5 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold">WhatsApp us instead</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0a2540]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<script>
(function () {
    var select = document.getElementById('package-service');
    if (!select) return;
    document.querySelectorAll('[data-package]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name = btn.getAttribute('data-package');
            if (!name) return;
            var map = {
                'Landlord Essentials': 'Landlord Essentials package',
                'Fire Package': 'Fire Package',
                'Security Package': 'Security Package',
                'Full FM': 'Full FM package'
            };
            var val = map[name] || 'Multi-service package';
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value === val) {
                    select.selectedIndex = i;
                    break;
                }
            }
        });
    });
})();
</script>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
