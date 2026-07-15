<?php
/**
 * Transparent pricing guide — "From £X" North West ballparks.
 * All figures are estimates / guide only, not fixed quotes.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Pricing Guide | From £X Estimates North West';
$metaDesc = 'Transparent property compliance pricing guide for Greater Manchester and the North West. EICR, gas safety, fire alarm service, emergency lighting tests, CCTV and more — From £X estimates clearly labelled as guide only, not a quote. Free fixed-price quotes.';
$metaKeywords = 'EICR cost North West, gas safety certificate price, fire alarm service cost, emergency lighting test price, CCTV camera install cost, property compliance pricing Manchester, Stockport';
$ogImage = url('/assets/images/services/electrical.jpg');
$canonicalUrl = url('/pages/pricing.php');

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
 * Guide-only ballparks for UK North West (Greater Manchester / surrounding).
 * price = "From £X" display; note explains typical scope. Never presented as fixed quote.
 */
$categories = [
    [
        'id' => 'electrical',
        'name' => 'Electrical & EICR',
        'icon' => '⚡',
        'service_slug' => 'electrical',
        'intro' => 'BS 7671 inspection, testing and certification. Circuit count, age of installation and access drive the final price.',
        'items' => [
            [
                'name' => 'EICR — 1-bed flat / small studio',
                'from' => '£129',
                'typical' => 'Guide for a compact flat with a standard consumer unit and straightforward access.',
                'includes' => 'Visual inspection, circuit testing, BS 7671 report & certificate',
            ],
            [
                'name' => 'EICR — 2–3 bed house',
                'from' => '£159',
                'typical' => 'Most common landlord / homeowner scope in Greater Manchester stock.',
                'includes' => 'Full installation condition report, C1/C2/FI coding, digital certificate',
            ],
            [
                'name' => 'EICR — 4+ bed / large house',
                'from' => '£219',
                'typical' => 'Larger domestic properties with more circuits or outbuildings.',
                'includes' => 'Extended circuit schedule, report & recommendations',
            ],
            [
                'name' => 'EICR — small commercial / multi-let',
                'from' => '£249',
                'typical' => 'Shops, small offices, HMOs — often POA once board layout is known.',
                'includes' => 'Commercial-grade report suitable for insurers & managing agents',
            ],
            [
                'name' => 'PAT testing (portable appliances)',
                'from' => '£49',
                'typical' => 'Call-out / first batch guide; per-item rates apply on larger inventories.',
                'includes' => 'Testing, labelling & schedule of results',
            ],
            [
                'name' => 'Consumer unit upgrade (domestic)',
                'from' => '£450',
                'typical' => 'Varies heavily with board type, RCD/RCBO layout and rewiring needed.',
                'includes' => 'Supply, install, certification (parts & labour scoped on quote)',
            ],
        ],
    ],
    [
        'id' => 'gas',
        'name' => 'Gas safety',
        'icon' => '🔥',
        'service_slug' => 'gas-systems',
        'intro' => 'Gas Safe landlord certificates and appliance checks. Appliance count and flue type affect price.',
        'items' => [
            [
                'name' => 'Landlord gas safety (CP12) — 1 appliance',
                'from' => '£69',
                'typical' => 'Typical single boiler / gas fire landlord check in the North West.',
                'includes' => 'Gas Safe record, safety checks, tenant-ready certificate',
            ],
            [
                'name' => 'Landlord gas safety — extra appliance',
                'from' => '£25',
                'typical' => 'Per additional appliance on the same visit (e.g. second fire / hob).',
                'includes' => 'Added to same CP12 visit where practical',
            ],
            [
                'name' => 'Boiler service (domestic)',
                'from' => '£79',
                'typical' => 'Annual service guide; manufacturer-specific work may differ.',
                'includes' => 'Service, safety checks & basic report',
            ],
            [
                'name' => 'Commercial gas safety',
                'from' => 'POA',
                'typical' => 'Plant rooms and multi-appliance commercial sites need a site scope.',
                'includes' => 'Survey-led quote, CP44 / commercial records as required',
            ],
        ],
    ],
    [
        'id' => 'fire',
        'name' => 'Fire alarms (BS 5839)',
        'icon' => '🚨',
        'service_slug' => 'fire-alarms',
        'intro' => 'Service, inspection and certification. Point count, panel type (conventional vs addressable) and building layout set the real figure.',
        'items' => [
            [
                'name' => 'Fire alarm service — small conventional',
                'from' => '£129',
                'typical' => 'Guide for a compact system (e.g. small shop / office) with limited devices.',
                'includes' => 'Service visit, function checks, certificate / logbook update',
            ],
            [
                'name' => 'Fire alarm service — addressable (small–medium)',
                'from' => '£189',
                'typical' => 'Typical multi-let or medium commercial panel with moderate device count.',
                'includes' => 'BS 5839 inspection, battery check, defect notes',
            ],
            [
                'name' => 'Fire alarm service — larger multi-zone / multi-panel',
                'from' => '£279',
                'typical' => 'Larger sites often move to planned contracts — ask for a package quote.',
                'includes' => 'Extended service, prioritised defect report',
            ],
            [
                'name' => 'New fire alarm design & install',
                'from' => 'POA',
                'typical' => 'Always survey-led — category, coverage and cable routes vary widely.',
                'includes' => 'Design, install, commission & BS 5839 certification',
            ],
        ],
    ],
    [
        'id' => 'emergency-lighting',
        'name' => 'Emergency lighting',
        'icon' => '💡',
        'service_slug' => 'emergency-lighting',
        'intro' => 'BS 5266 function and duration testing. Fitting count and access (ladders / out-of-hours) change the quote.',
        'items' => [
            [
                'name' => 'Emergency lighting annual duration test — small site',
                'from' => '£99',
                'typical' => 'Guide for a limited number of self-contained fittings on one visit.',
                'includes' => 'Duration / discharge test, results record, certificate',
            ],
            [
                'name' => 'Emergency lighting test — medium commercial',
                'from' => '£159',
                'typical' => 'Multi-floor offices, retail or residential blocks with more luminaires.',
                'includes' => 'BS 5266 testing programme entry & defect list',
            ],
            [
                'name' => 'Monthly function test programme',
                'from' => 'POA',
                'typical' => 'Recurring contracts priced on fitting count and visit frequency.',
                'includes' => 'Planned visits, logbook support, annual duration option',
            ],
            [
                'name' => 'LED emergency conversion / new fittings',
                'from' => '£85',
                'typical' => 'Per fitting guide where access is straightforward; bulk rates available.',
                'includes' => 'Supply & install scoped on site survey',
            ],
        ],
    ],
    [
        'id' => 'cctv-security',
        'name' => 'CCTV & security',
        'icon' => '📹',
        'service_slug' => 'cctv',
        'intro' => 'IP / HD cameras, NVRs and related security. Cabling distance, mounting height and network setup drive install cost.',
        'items' => [
            [
                'name' => 'CCTV — single camera add-on (existing system)',
                'from' => '£149',
                'typical' => 'Guide where spare NVR channel, power and nearby cable route exist.',
                'includes' => 'Camera, labour for straightforward add-on, basic config',
            ],
            [
                'name' => 'CCTV — single camera + basic recorder kit',
                'from' => '£349',
                'typical' => 'Entry-level 1-camera system with local recording (parts grade varies).',
                'includes' => 'Camera, NVR/DVR option, install & app setup where required',
            ],
            [
                'name' => 'CCTV — 4-camera domestic / small commercial',
                'from' => '£799',
                'typical' => 'Popular package size; final price depends on cable runs and brand.',
                'includes' => 'Multi-camera design, install, recording & remote viewing setup',
            ],
            [
                'name' => 'Intruder alarm service / health-check',
                'from' => '£89',
                'typical' => 'Service visit for an existing wired or wireless system.',
                'includes' => 'Function test, battery check, basic report',
            ],
            [
                'name' => 'Access control — single door',
                'from' => '£449',
                'typical' => 'Reader, lock hardware and controller complexity vary by brand (e.g. Paxton).',
                'includes' => 'Survey-led install quote; fire-release integration optional',
            ],
        ],
    ],
    [
        'id' => 'packages',
        'name' => 'Bundled & multi-service',
        'icon' => '📦',
        'service_slug' => null,
        'intro' => 'Combining certificates and services on one visit often reduces total cost versus booking separately.',
        'items' => [
            [
                'name' => 'Landlord essentials (EICR + gas safety)',
                'from' => '£199',
                'typical' => 'Guide where both can be coordinated on the same property access day.',
                'includes' => 'EICR + CP12 scoped together — see Packages for full bundles',
            ],
            [
                'name' => 'Fire + emergency lighting service visit',
                'from' => '£229',
                'typical' => 'Combined life-safety service where systems are on the same site.',
                'includes' => 'Coordinated BS 5839 & BS 5266 testing where practical',
            ],
            [
                'name' => 'Full FM / multi-site programme',
                'from' => 'POA',
                'typical' => 'Portfolios and facilities contracts are always bespoke.',
                'includes' => 'PPM calendar, multi-service SLAs, audit-ready documentation',
            ],
        ],
    ],
];

$factors = [
    ['title' => 'Property size & complexity', 'text' => 'More circuits, devices, floors or outbuildings means more engineer time.'],
    ['title' => 'System type & brand', 'text' => 'Addressable fire panels, specialist access hardware or long CCTV cable runs cost more than simple kits.'],
    ['title' => 'Access & working hours', 'text' => 'Occupied sites, height access, parking restrictions or out-of-hours work affect labour.'],
    ['title' => 'Remedial works', 'text' => 'Inspection prices cover testing and reporting — C1/C2 fixes, batteries and parts are quoted separately.'],
    ['title' => 'Travel & multi-site', 'text' => 'Core North West coverage is standard; remote jobs or multi-site days are scoped on the quote.'],
    ['title' => 'Documentation needs', 'text' => 'Insurer packs, FRA support and portfolio reporting can be bundled into the fixed quote.'],
];

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <span class="text-white/80">Pricing</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Guide only · Not a quote
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Transparent<br>
                <span class="text-[#ff6b00]">pricing guide</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Honest <strong class="text-white">From £X</strong> ballparks for EICR, gas safety, fire service,
                emergency lighting, CCTV and more across Greater Manchester and the North West —
                so you can budget before you book.
            </p>
            <div class="mt-6 p-4 rounded-2xl bg-amber-500/15 border border-amber-400/30 text-sm text-amber-50/95 max-w-2xl">
                <strong class="text-amber-100">Important:</strong> Every figure on this page is a
                <strong class="text-white">guide estimate only</strong>, not a fixed price or formal quotation.
                Final prices depend on site survey, system condition and agreed scope. You always receive a
                <strong class="text-white">fixed-price quote</strong> before work starts.
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#guide" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">View guide prices</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Get a fixed quote</a>
                <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I saw the pricing guide and need a fixed quote') ?>"
                   target="_blank" rel="noopener"
                   class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['From £X guides', 'Ballpark ranges for budgeting — clearly not a quote'],
            ['Fixed quote after scope', 'Agreed price in writing before engineers attend'],
            ['North West focused', 'Stockport-based team · ' . count($areas) . '+ towns covered'],
            ['No hidden labour tricks', 'Remedials and parts quoted separately when needed'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- DISCLAIMER STRIP -->
<section class="bg-amber-50 border-b border-amber-100">
    <div class="max-w-7xl mx-auto px-6 py-5 text-sm text-amber-950/90 flex flex-col md:flex-row md:items-center gap-3 md:gap-6">
        <span class="inline-flex items-center gap-2 font-semibold shrink-0">
            <span class="w-8 h-8 rounded-xl bg-amber-200/80 flex items-center justify-center text-base" aria-hidden="true">ℹ</span>
            Guide only
        </span>
        <p class="text-amber-950/80">
            Prices are indicative UK North West market ballparks (ex VAT where applicable unless stated on your quote).
            They are <strong>not</strong> offers, catalogues or binding quotes. Site conditions, access, parts,
            out-of-hours work and remedials can increase or decrease the final figure. Request a free fixed-price quote for certainty.
        </p>
    </div>
</section>

<!-- JUMP LINKS -->
<section class="max-w-7xl mx-auto px-6 pt-10">
    <div class="flex flex-wrap gap-2">
        <?php foreach ($categories as $cat): ?>
            <a href="#<?= htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8') ?>"
               class="px-4 py-2 bg-white border border-zinc-200 rounded-full text-sm font-medium text-black hover:border-[#ff6b00] hover:text-[#ff6b00] transition">
                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
        <a href="#factors" class="px-4 py-2 bg-zinc-50 border border-zinc-200 rounded-full text-sm font-medium text-zinc-700 hover:border-[#0a2540] transition">What affects price</a>
        <a href="#quote" class="px-4 py-2 bg-[#ff6b00] text-white rounded-full text-sm font-semibold hover:bg-orange-600 transition">Free quote</a>
    </div>
</section>

<!-- PRICING GUIDE -->
<section id="guide" class="max-w-7xl mx-auto px-6 py-12 md:py-16 space-y-14">
    <div class="max-w-2xl">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">North West ballparks</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Service pricing guide</h2>
        <p class="mt-3 text-zinc-600">
            Each row shows a typical <strong class="text-black">From £X</strong> starting point for straightforward jobs.
            Use these to plan budgets, then convert to a real quote with postcode and site details below.
        </p>
    </div>

    <?php foreach ($categories as $cat): ?>
    <div id="<?= htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8') ?>" class="scroll-mt-24">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
            <div>
                <h3 class="text-2xl md:text-3xl font-semibold tracking-tight text-black flex items-center gap-2">
                    <span aria-hidden="true"><?= $cat['icon'] ?></span>
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <p class="mt-1 text-zinc-600 max-w-2xl"><?= htmlspecialchars($cat['intro'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php if (!empty($cat['service_slug']) && isset($services[$cat['service_slug']])): ?>
                <a href="<?= url('/pages/services/' . rawurlencode($cat['service_slug']) . '.php') ?>"
                   class="text-sm font-semibold text-[#ff6b00] shrink-0">
                    <?= htmlspecialchars($services[$cat['service_slug']], ENT_QUOTES, 'UTF-8') ?> hub →
                </a>
            <?php elseif ($cat['id'] === 'packages'): ?>
                <a href="<?= url('/pages/packages.php') ?>" class="text-sm font-semibold text-[#ff6b00] shrink-0">View packages →</a>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-zinc-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="hidden md:grid md:grid-cols-12 gap-4 px-6 py-3 bg-zinc-50 border-b text-xs uppercase tracking-wider text-zinc-500 font-semibold">
                <div class="md:col-span-4">Service</div>
                <div class="md:col-span-2">Guide from</div>
                <div class="md:col-span-3">Typical scope</div>
                <div class="md:col-span-3">Usually includes</div>
            </div>
            <ul class="divide-y divide-zinc-100">
                <?php foreach ($cat['items'] as $item):
                    $isPoa = strtoupper(ltrim($item['from'], '£')) === 'POA' || stripos($item['from'], 'POA') !== false;
                ?>
                <li class="px-5 md:px-6 py-5 md:grid md:grid-cols-12 md:gap-4 md:items-start hover:bg-zinc-50/80 transition">
                    <div class="md:col-span-4">
                        <div class="font-semibold text-black"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="md:hidden mt-2 flex items-baseline gap-2">
                            <span class="text-xs uppercase tracking-wider text-zinc-500">From</span>
                            <span class="text-xl font-semibold text-[#0a2540]"><?= htmlspecialchars($item['from'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-xs text-amber-700 font-medium">guide only</span>
                        </div>
                    </div>
                    <div class="hidden md:block md:col-span-2">
                        <?php if ($isPoa): ?>
                            <div class="text-lg font-semibold text-[#0a2540]">POA</div>
                            <div class="text-xs text-zinc-500">Survey required</div>
                        <?php else: ?>
                            <div class="text-xs uppercase tracking-wider text-zinc-500">From</div>
                            <div class="text-xl font-semibold text-[#0a2540]"><?= htmlspecialchars($item['from'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="text-xs text-amber-700 font-medium">guide only · not a quote</div>
                        <?php endif; ?>
                    </div>
                    <div class="md:col-span-3 mt-2 md:mt-0 text-sm text-zinc-600">
                        <?= htmlspecialchars($item['typical'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="md:col-span-3 mt-2 md:mt-0 text-sm text-zinc-600">
                        <?= htmlspecialchars($item['includes'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="px-5 md:px-6 py-4 bg-zinc-50 border-t flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs text-zinc-500">All amounts are guide estimates for straightforward North West jobs. Fixed quote on request.</p>
                <a href="#quote" class="inline-flex justify-center px-5 py-2.5 rounded-2xl bg-[#0a2540] hover:bg-[#ff6b00] text-white text-sm font-semibold transition"
                   data-service="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>">
                    Quote this category
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- WHAT AFFECTS PRICE -->
<section id="factors" class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Why quotes vary</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What affects the final price</h2>
            <p class="mt-3 text-zinc-600">
                Online “From £X” figures cannot see your consumer unit, fire panel or cable runs.
                These factors are why we confirm a fixed price after scope — never guess on the day.
            </p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($factors as $f): ?>
            <div class="border border-zinc-200 rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($f['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- HOW QUOTING WORKS -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-4">From guide price to fixed quote</h2>
    <p class="text-center text-zinc-600 max-w-2xl mx-auto mb-12">
        Use this page to sense-check budgets. Then tell us the postcode and job type — we turn the guide into a clear, fixed proposal.
    </p>
    <div class="grid md:grid-cols-3 gap-8">
        <?php
        $steps = [
            ['1', 'Check the guide', 'Find the closest From £X row for your service — treat it as a ballpark only.'],
            ['2', 'Send site details', 'Postcode, property type, system brand, photos and access notes make quotes accurate.'],
            ['3', 'Receive a fixed quote', 'We confirm scope and price in writing before any chargeable work begins.'],
        ];
        foreach ($steps as [$n, $t, $d]): ?>
        <div class="text-center px-4">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
            <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA BAND -->
<section class="max-w-7xl mx-auto px-6 pb-6">
    <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
        <div class="max-w-xl">
            <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">Ready for a real number?</h2>
            <p class="mt-3 text-white/80">
                Free fixed-price quotes for landlords, agents and facilities teams across
                <?= count($areas) ?>+ North West towns. No obligation — guide prices never replace a site-specific quote.
            </p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <a href="#quote" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Request free quote</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a fixed price quote (saw the pricing guide)') ?>"
               target="_blank" rel="noopener"
               class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
        </div>
    </div>
</section>

<!-- RELATED -->
<section class="max-w-7xl mx-auto px-6 py-14">
    <div class="grid lg:grid-cols-2 gap-10 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Also useful</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Packages &amp; single services</h2>
            <p class="mt-3 text-zinc-600">
                Multi-service landlord and FM packages can be more cost-effective than booking each certificate alone.
                Or open a service hub for local pages and manufacturer options.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="<?= url('/pages/packages.php') ?>" class="px-4 py-2 bg-[#0a2540] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition">Compliance packages</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">Landlords</a>
                <a href="<?= url('/pages/commercial.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">Commercial / FM</a>
                <?php foreach ($services as $slug => $name): ?>
                    <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="border border-zinc-200 rounded-3xl p-6 md:p-8 bg-zinc-50">
            <h3 class="text-xl font-semibold text-black">VAT, parts &amp; remedials</h3>
            <ul class="mt-4 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span>Guide prices may be shown ex VAT; your written quote states VAT treatment clearly.</span></li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span>Inspection / service fees cover the agreed test and certificate — not automatic repairs.</span></li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span>C1/C2 electrical remedials, batteries, devices and hardware are priced if needed.</span></li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span>Trade shop products are priced separately from installation labour.</span></li>
            </ul>
            <a href="<?= url('/pages/faq.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">Pricing FAQs →</a>
        </div>
    </div>
</section>

<!-- QUOTE FORM -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free fixed-price quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Convert a guide price into a real quote</h2>
            <p class="mt-3 text-zinc-600">
                Tell us the service and postcode — we’ll return a fixed-price proposal after scope is agreed.
                The figures above remain <strong class="text-black">guide only</strong> until then.
            </p>
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
                <select name="service" id="pricing-service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <option value="EICR">EICR</option>
                    <option value="Gas safety certificate">Gas safety certificate</option>
                    <option value="Fire alarm service">Fire alarm service</option>
                    <option value="Emergency lighting test">Emergency lighting test</option>
                    <option value="CCTV">CCTV</option>
                    <option value="Intruder alarm">Intruder alarm</option>
                    <option value="Access control">Access control</option>
                    <option value="Landlord package">Landlord package</option>
                    <option value="Multi-service package">Multi-service package</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Other / not sure">Other / not sure</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="Postcode, property type, number of circuits/appliances/cameras, system brands, preferred dates…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Request fixed-price quote</button>
            <p class="text-center text-xs text-zinc-500">
                Submitting this form requests a quote — it does not accept the guide prices on this page as a contract.
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm">
            <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a fixed price quote (pricing guide)') ?>"
               target="_blank" rel="noopener"
               class="px-5 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold">WhatsApp us instead</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0a2540]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            '@id' => url('/pages/pricing.php') . '#webpage',
            'url' => url('/pages/pricing.php'),
            'name' => 'Pricing Guide — ' . SITE_NAME,
            'description' => $metaDesc,
            'isPartOf' => ['@type' => 'WebSite', 'name' => SITE_NAME, 'url' => SITE_URL],
            'about' => [
                '@type' => 'Thing',
                'name' => 'Property compliance service pricing guide (estimates only)',
            ],
        ],
        [
            '@type' => 'LocalBusiness',
            'name' => SITE_NAME,
            'url' => SITE_URL,
            'telephone' => PHONE,
            'email' => EMAIL,
            'priceRange' => '££',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road, Offerton',
                'addressLocality' => 'Stockport',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'areaServed' => 'North West England',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>

<script>
(function () {
    var select = document.getElementById('pricing-service');
    if (!select) return;
    var map = {
        'Electrical & EICR': 'EICR',
        'Gas safety': 'Gas safety certificate',
        'Fire alarms (BS 5839)': 'Fire alarm service',
        'Emergency lighting': 'Emergency lighting test',
        'CCTV & security': 'CCTV',
        'Bundled & multi-service': 'Multi-service package'
    };
    document.querySelectorAll('[data-service]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name = btn.getAttribute('data-service');
            if (!name) return;
            var val = map[name] || name;
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
