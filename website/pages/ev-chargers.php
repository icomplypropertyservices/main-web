<?php
/**
 * EV charger installation landing —
 * Rolec, Myenergi, Easee, Ohme + electrical service links + quote form.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'EV Charger Installation | Rolec, Myenergi, Easee & Ohme';
$metaDesc = 'Professional EV charger installation across Stockport, Greater Manchester and the North West. Rolec, Myenergi, Easee and Ohme charge points — BS 7671 certified, load assessment and fixed-price quotes.';
$metaKeywords = 'EV charger installation, Rolec EV, Myenergi zappi, Easee charger, Ohme Home Pro, electric vehicle charge point North West, home EV charger Stockport, workplace EV charging Manchester, BS 7671 EV install';
$ogImage = url('/assets/images/keywords/ev-charger-installation.jpg');
$canonicalUrl = url('/pages/ev-chargers.php');

$services = getServices();
$areas = getAreas();

$brands = [
    [
        'name' => 'Rolec EV',
        'slug' => 'rolec-ev',
        'badge' => 'Workplaces & car parks',
        'blurb' => 'Rolec charge points for homes, workplaces and multi-bay car parks — robust UK-built units with full BS 7671 install, isolation and certification.',
        'keywords' => ['Wall & pedestal', 'Multi-bay ready', 'BS 7671 cert'],
        'img' => '/assets/images/manufacturers/rolec-ev.jpg',
    ],
    [
        'name' => 'Myenergi',
        'slug' => 'myenergi',
        'badge' => 'zappi · eddi',
        'blurb' => 'Myenergi zappi (and eddi where solar is fitted) — smart home and small commercial charging with solar divert options, app control and clear handover.',
        'keywords' => ['Smart charging', 'Solar-ready', 'App setup'],
        'img' => '/assets/images/manufacturers/myenergi-ev-charger.jpg',
    ],
    [
        'name' => 'Easee',
        'slug' => 'easee',
        'badge' => 'Home & Charge',
        'blurb' => 'Easee Home and Charge units for residential drives and shared parking — compact design, load management and professional commissioning included.',
        'keywords' => ['Load management', 'Compact install', 'App setup'],
        'img' => '/assets/images/keywords/ev-charger-installation.jpg',
    ],
    [
        'name' => 'Ohme',
        'slug' => 'ohme',
        'badge' => 'Home Pro',
        'blurb' => 'Ohme EV charge points for homes, workplaces and landlord parking — load assessment, scheduled charging and full electrical certification.',
        'keywords' => ['Scheduled charge', 'Home & fleet', 'BS 7671 cert'],
        'img' => '/assets/images/services/electrical.jpg',
    ],
];

$included = [
    ['title' => 'Site survey & load check', 'text' => 'Supply capacity, consumer unit condition, earthing and cable route assessed before we quote a fixed price.'],
    ['title' => 'Supply & install', 'text' => 'Wall or pedestal mount, dedicated circuit, isolation, RCD protection and tidy cable runs to current regs.'],
    ['title' => 'BS 7671 certification', 'text' => 'Electrical installation certificate / minor works as appropriate — paperwork you can keep for insurers and property files.'],
    ['title' => 'Commissioning & handover', 'text' => 'Unit powered, app/user setup guided, and charging demonstrated so you leave ready to plug in.'],
];

$trust = [
    ['title' => 'BS 7671 installs', 'text' => 'Every charge point certified to current wiring regs'],
    ['title' => 'Leading brands', 'text' => 'Rolec, Myenergi, Easee & Ohme supported'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope after survey — no hidden extras'],
    ['title' => 'North West coverage', 'text' => 'Stockport-based · ' . count($areas) . '+ towns'],
];

$howItWorks = [
    ['1', 'Tell us the site', 'Postcode, property type, preferred brand (or “advise me”), parking layout and any solar / three-phase notes.'],
    ['2', 'Survey & fixed quote', 'We confirm supply capacity, cable route and unit choice — then issue a clear fixed-price proposal.'],
    ['3', 'Install & certify', 'Engineers fit the charger, commission the unit and issue electrical certification with a full handover.'],
];

$faqs = [
    [
        'q' => 'Which EV charger brands do you install?',
        'a' => 'We install Rolec EV, Myenergi (including zappi), Easee and Ohme charge points for homes, workplaces and landlord parking across the North West. If you already have a preferred unit, tell us on the quote form.',
    ],
    [
        'q' => 'Do I need a consumer unit upgrade for an EV charger?',
        'a' => 'Not always. We assess spare ways, protective devices and overall load first. If an upgrade or additional distribution is required, we quote it clearly before work starts.',
    ],
    [
        'q' => 'Is the install certified?',
        'a' => 'Yes. Work is completed to BS 7671 with the appropriate electrical certificate. Building-reg / Part P notification is arranged where required for the property type.',
    ],
    [
        'q' => 'Can you install workplace or multi-bay chargers?',
        'a' => 'Yes — single points through to multi-bay Rolec and managed workplace schemes. Share bay count, three-phase availability and access details for a commercial quote.',
    ],
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

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$waBase = 'https://wa.me/' . WHATSAPP;
$homeUrl = rtrim(SITE_URL, '/') . '/';

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span aria-hidden="true">/</span>
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span aria-hidden="true">/</span>
            <a href="<?= url('/pages/services/electrical.php') ?>" class="hover:text-white">Electrical</a>
            <span aria-hidden="true">/</span>
            <span class="text-white/80">EV chargers</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    EV charging · BS 7671 · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    EV charger<br>
                    <span class="text-[#ff6b00]">installation</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Home, workplace and multi-bay charge points from
                    <strong class="text-white font-semibold">Rolec</strong>,
                    <strong class="text-white font-semibold">Myenergi</strong>,
                    <strong class="text-white font-semibold">Easee</strong> and
                    <strong class="text-white font-semibold">Ohme</strong> —
                    surveyed, installed and certified by Stockport-based electrical engineers.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get EV install quote</a>
                    <a href="#brands" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">View brands</a>
                    <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need an EV charger installation quote') ?>"
                       target="_blank" rel="noopener"
                       class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block">4 brands</span> Rolec · Myenergi · Easee · Ohme</div>
                    <div><span class="text-white font-semibold text-xl block">BS 7671</span> Full certification</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns covered</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($brands as $brand):
                    $img = url($brand['img']);
                ?>
                <a href="<?= url('/pages/manufacturers/' . rawurlencode($brand['slug']) . '.php') ?>"
                   class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[140px] bg-white/5 hover:border-[#ff6b00] transition">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition" loading="lazy"
                         onerror="this.style.display='none'">
                    <div class="relative p-5 h-full flex flex-col justify-end">
                        <div class="text-[10px] uppercase tracking-wider text-[#ff6b00] font-semibold mb-1"><?= htmlspecialchars($brand['badge'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="font-semibold text-white text-lg leading-tight"><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs text-white/70 mt-1">Manufacturer page →</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
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

<!-- INTRO + ELECTRICAL LINK -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Electric vehicle charging</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Charge points done properly</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                An EV charger is more than a wall box. Supply capacity, protective devices, cable sizing, earthing and
                isolation all need to be right for a safe, reliable install. Icomply designs and installs charge points
                as part of our wider
                <a href="<?= url('/pages/services/electrical.php') ?>" class="text-[#ff6b00] font-semibold hover:underline">electrical services</a>
                — from single home units to workplace multi-bay schemes.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Homes, driveways and garage installs</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Workplace, fleet and landlord parking</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Consumer unit upgrades when required</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Solar / generation integration advice (e.g. Myenergi)</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/pages/services/electrical.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Electrical services</a>
                <a href="<?= url('/pages/keywords/ev-charger-installation.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">EV install guide</a>
                <a href="<?= url('/pages/manufacturers/index.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">All manufacturers</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Ideal for</h3>
            <ul class="mt-6 space-y-4 text-sm text-white/90">
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Homeowners adding a dedicated driveway or garage charger</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Landlords and agents fitting parking-bay charge points</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Offices, industrial units and fleet depots</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Multi-tenant blocks planning shared EV bays</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Sites pairing EV with solar generation</li>
            </ul>
            <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold hover:bg-orange-600 transition">Request EV quote</a>
        </div>
    </div>
</section>

<!-- BRANDS -->
<section id="brands" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Manufacturers</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Rolec · Myenergi · Easee · Ohme</h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">
                    Open a brand page for install details, trade accessories and a focused quote — or ask us to recommend the right unit for your supply and parking layout.
                </p>
            </div>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($brands as $brand):
                $img = url($brand['img']);
            ?>
            <a href="<?= url('/pages/manufacturers/' . rawurlencode($brand['slug']) . '.php') ?>"
               class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-40 bg-zinc-100 overflow-hidden">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?> EV charger installation by Icomply"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         loading="lazy"
                         onerror="this.src='<?= htmlspecialchars(url('/assets/images/services/electrical.jpg'), ENT_QUOTES, 'UTF-8') ?>'">
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <div class="text-[10px] uppercase tracking-wider text-[#ff6b00] font-semibold"><?= htmlspecialchars($brand['badge'], ENT_QUOTES, 'UTF-8') ?></div>
                    <h3 class="font-semibold text-xl text-black tracking-tight mt-1"><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($brand['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        <?php foreach ($brand['keywords'] as $kw): ?>
                            <span class="text-[11px] px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-600"><?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <span class="mt-5 text-sm font-semibold text-[#ff6b00]">View manufacturer →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- WHAT'S INCLUDED -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Scope</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What’s included in an install</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Every job is scoped to your property — the steps below are typical for a single home or workplace point.
                Multi-bay and three-phase schemes are quoted separately.
            </p>
        </div>
        <a href="<?= url('/pages/services/electrical.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Full electrical service →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($included as $item): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-6">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold mb-4">✓</div>
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-10 bg-zinc-50 border border-zinc-200 rounded-3xl p-8 md:p-10 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <h3 class="text-2xl font-semibold tracking-tight text-black">Related electrical work</h3>
            <ul class="mt-4 space-y-2 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Consumer unit / distribution board upgrades</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> EICR inspection before or after install</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Additional circuits and isolation</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Commercial electrical programmes for multi-site fleets</li>
            </ul>
        </div>
        <div class="text-center md:text-right space-y-3">
            <p class="text-zinc-600 mb-2">Need more than a single charger? Bundle with our electrical hub or a commercial package.</p>
            <div class="flex flex-wrap md:justify-end gap-3">
                <a href="<?= url('/pages/services/electrical.php') ?>" class="inline-flex px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Electrical hub</a>
                <a href="<?= url('/pages/commercial.php') ?>" class="inline-flex px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Commercial / FM</a>
                <a href="<?= url('/shop/index.php') ?>" class="inline-flex px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Trade shop</a>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How EV installs work</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($howItWorks as [$n, $t, $d]): ?>
            <div class="text-center px-4">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= htmlspecialchars($n, ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">FAQ</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Common EV questions</h2>
        </div>
        <div class="space-y-4">
            <?php foreach ($faqs as $faq): ?>
            <details class="bg-white border border-zinc-200 rounded-2xl p-5 group">
                <summary class="font-semibold text-black cursor-pointer list-none flex justify-between items-center gap-4">
                    <span><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-[#ff6b00] shrink-0 group-open:rotate-45 transition text-xl leading-none">+</span>
                </summary>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COVERAGE -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Serving <?= count($areas) ?>+ North West towns</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Based in Offerton, Stockport — covering Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria
                for home and commercial EV installs.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php foreach ($popularTowns as $town): ?>
                    <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]"><?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All areas →</a>
                <a href="<?= url('/pages/services/electrical.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Electrical service →</a>
                <a href="<?= url('/pages/keywords/ev-charger-installation.php') ?>" class="text-sm font-semibold text-[#ff6b00]">EV keyword guide →</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk to the electrical team</h3>
            <p class="mt-3 text-white/80">Call, WhatsApp or use the quote form — include postcode, brand preference and single- or three-phase supply if known.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need an EV charger installation quote') ?>"
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
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request an EV charger quote</h2>
            <p class="mt-3 text-zinc-600">
                Tell us the postcode, preferred brand (Rolec / Myenergi / Easee / Ohme / advise me) and property type.
                All quotes are fixed-price after scope is agreed.
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
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <option value="EV Charger Installation" selected>EV Charger Installation</option>
                    <option value="Rolec EV Install">Rolec EV Install</option>
                    <option value="Myenergi Install">Myenergi Install</option>
                    <option value="Easee Install">Easee Install</option>
                    <option value="Ohme Install">Ohme Install</option>
                    <option value="Electrical">Electrical (general)</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <?php if ($slug === 'electrical') {
                            continue;
                        } ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Postcode, property type (home / workplace / multi-bay), preferred brand (Rolec / Myenergi / Easee / Ohme / advise me), single- or three-phase if known, parking notes…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit EV quote request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0a2540] hover:text-[#ff6b00]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0a2540] hover:text-[#ff6b00]"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="font-semibold text-green-700 hover:text-green-600">WhatsApp</a>
        </div>

        <div class="mt-10 flex flex-wrap justify-center gap-2">
            <?php foreach ($brands as $brand): ?>
                <a href="<?= url('/pages/manufacturers/' . rawurlencode($brand['slug']) . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= url('/pages/services/electrical.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00] transition">Electrical services</a>
            <a href="<?= url('/pages/keywords/ev-charger-installation.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00] transition">EV install guide</a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
