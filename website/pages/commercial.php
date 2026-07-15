<?php
/**
 * Commercial & facilities management landing —
 * multi-site compliance, fire, electrical, AOV, nurse call, CCTV, access, maintenance contracts.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Commercial & Facilities Management | Multi-Site Compliance';
$metaDesc = 'Icomply commercial & facilities management: multi-site compliance across fire alarms, electrical, AOV, nurse call, CCTV, access control and planned maintenance contracts. Fixed-price quotes across the North West.';
$metaKeywords = 'commercial facilities management, multi-site compliance, commercial fire alarms, commercial electrical, AOV maintenance, nurse call, CCTV, access control, maintenance contracts North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/commercial.php');

$services = getServices();
$areas = getAreas();
$mfrCatalog = getManufacturerCatalog();

// Core commercial / FM service pillars highlighted on this page
$commercialPillars = [
    'fire-alarms' => [
        'title' => 'Fire alarms',
        'blurb' => 'BS 5839 design, install, service and certification for multi-site portfolios — addressable, conventional and wireless.',
        'keywords' => ['BS 5839', 'Periodic service', 'Certification pack'],
    ],
    'electrical' => [
        'title' => 'Electrical',
        'blurb' => 'EICR programmes, commercial installs, consumer units, EV chargers and planned electrical maintenance to BS 7671.',
        'keywords' => ['EICR programmes', 'BS 7671', 'Planned maintenance'],
    ],
    'aov-air-handling' => [
        'title' => 'AOV & air handling',
        'blurb' => 'Smoke vents, AOV panels, AHU controls and smoke-control maintenance for high-rise and commercial blocks.',
        'keywords' => ['Smoke control', 'AOV panels', 'AHU controls'],
    ],
    'nurse-call' => [
        'title' => 'Nurse call',
        'blurb' => 'Care home and healthcare nurse call design, install, upgrades and HTM-aligned planned maintenance.',
        'keywords' => ['Care homes', 'HTM 08-03', 'Upgrades'],
    ],
    'cctv' => [
        'title' => 'CCTV',
        'blurb' => 'IP / HD CCTV design, multi-site recording, remote viewing and NVR estates for facilities and security teams.',
        'keywords' => ['IP / HD', 'Remote viewing', 'Multi-site NVR'],
    ],
    'access-control' => [
        'title' => 'Access control',
        'blurb' => 'Paxton, HID, Salto and door access with fire-override integration for offices, multi-tenant and commercial sites.',
        'keywords' => ['Paxton / HID / Salto', 'Fire override', 'Credentials'],
    ],
];

$extraServices = [
    'emergency-lighting' => 'BS 5266 testing, LED upgrades and monthly/annual certification across portfolios.',
    'intruder-alarm' => 'PD 6662 / BS EN 50131 wired and wireless systems with monitoring options.',
    'door-entry' => 'Video and audio door entry for multi-tenant commercial blocks.',
    'intercoms' => 'Master/substation and commercial intercom systems.',
    'gas-systems' => 'Commercial gas, landlord certs and safety checks for mixed portfolios.',
];

$contractFeatures = [
    ['title' => 'Multi-site scheduling', 'text' => 'One calendar for fire, electrical, AOV, emergency lighting and security across your estate.'],
    ['title' => 'Audit-ready packs', 'text' => 'Certificates, logbooks and service reports organised per site and per system.'],
    ['title' => 'Priority call-outs', 'text' => 'Contract holders get faster fault response and clear SLAs by system type.'],
    ['title' => 'Single point of contact', 'text' => 'One team for quotes, visits, documentation and manufacturer-backed parts.'],
];

$trust = [
    ['title' => 'FM-ready', 'text' => 'Built for facilities managers, landlords and multi-site operators'],
    ['title' => 'Standards-led', 'text' => 'BS 5839, BS 5266, BS 7671, smoke control & more'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope, documentation and certification'],
    ['title' => 'Trade shop', 'text' => 'Kits & parts for engineers and FM stores'],
];

$howItWorks = [
    ['1', 'Map the estate', 'Share sites, system types, panel brands and current cert expiry dates.'],
    ['2', 'Agree the programme', 'We quote install, remedial or planned maintenance — fixed price after scope.'],
    ['3', 'Deliver & document', 'Engineers attend, complete works and issue audit-ready certificates.'],
];

$featuredMfr = array_filter($mfrCatalog, fn($c) => !empty($c['featured']));
if (!$featuredMfr) {
    $featuredMfr = array_slice($mfrCatalog, 0, 16, true);
}
$featuredMfr = array_slice($featuredMfr, 0, 16, true);

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
            <span class="text-white/80">Commercial &amp; facilities</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Multi-site · FM · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Commercial &amp; facilities<br>
                    <span class="text-[#ff6b00]">compliance</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Multi-site fire, electrical, AOV, nurse call, CCTV and access control — plus planned maintenance
                    contracts with audit-ready documentation for facilities and property teams.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get FM quote</a>
                    <a href="<?= url('/pages/services/index.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">All services</a>
                    <a href="#contracts" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Maintenance contracts</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block"><?= count($services) ?></span> core services</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns covered</div>
                    <div><span class="text-white font-semibold text-xl block">One</span> compliance partner</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <?php
                $heroSlugs = ['fire-alarms', 'electrical', 'cctv', 'access-control'];
                foreach ($heroSlugs as $slug):
                    $name = $services[$slug] ?? ucwords(str_replace('-', ' ', $slug));
                    $img = url('/assets/images/services/' . $slug . '.jpg');
                ?>
                <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
                   class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[140px] bg-white/5 hover:border-[#ff6b00] transition">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition" loading="lazy"
                         onerror="this.style.display='none'">
                    <div class="relative p-5 h-full flex flex-col justify-end">
                        <div class="font-semibold text-white text-lg leading-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs text-white/70 mt-1">View service →</div>
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

<!-- MULTI-SITE INTRO -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Multi-site compliance</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">One programme for the whole estate</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Facilities managers and commercial landlords need consistent standards across offices, care homes,
                industrial units, retail and multi-tenant blocks. We install, service and certify the systems that keep
                sites safe, insured and audit-ready — with a single contact for the North West.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Portfolio-wide fire, electrical and emergency lighting schedules</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> AOV, nurse call, CCTV and access control under one contract</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Remedials, upgrades and new installs quoted with clear scope</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Manufacturer-backed parts via our trade shop</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/pages/services/index.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Browse services</a>
                <a href="<?= url('/pages/manufacturers/index.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Manufacturers</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Trade shop</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Ideal for</h3>
            <ul class="mt-6 space-y-4 text-sm text-white/90">
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Facilities management companies managing multi-site portfolios</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Commercial landlords and managing agents</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Care homes, clinics and healthcare estates</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Offices, industrial units, retail and mixed-use blocks</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Housing associations and multi-tenant residential blocks</li>
            </ul>
            <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold">Request commercial quote</a>
        </div>
    </div>
</section>

<!-- COMMERCIAL PILLARS -->
<section id="services" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Core systems</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fire · electrical · AOV · nurse call · CCTV · access</h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">The systems commercial and FM teams ask for most — open a hub for local coverage, manufacturers and a quote form.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($commercialPillars as $slug => $pillar):
                $name = $services[$slug] ?? $pillar['title'];
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-40 bg-zinc-100 overflow-hidden">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> for commercial &amp; facilities sites"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <h3 class="font-semibold text-xl text-black tracking-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($pillar['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        <?php foreach ($pillar['keywords'] as $kw): ?>
                            <span class="text-[11px] px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-600"><?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    </div>
                    <span class="mt-5 text-sm font-semibold text-[#ff6b00]">Explore service →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Supporting commercial services -->
        <div class="mt-12">
            <h3 class="text-lg font-semibold text-black mb-4">Also available for commercial sites</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($extraServices as $slug => $blurb):
                    if (!isset($services[$slug])) {
                        continue;
                    }
                    $name = $services[$slug];
                ?>
                <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
                   class="block bg-white border border-zinc-200 rounded-2xl p-5 hover:border-[#ff6b00] transition">
                    <div class="font-semibold text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                    <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="inline-block mt-3 text-sm font-semibold text-[#ff6b00]">View →</span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- MAINTENANCE CONTRACTS -->
<section id="contracts" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Planned maintenance</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Maintenance contracts built for FM</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Move from reactive call-outs to a planned programme — fire, electrical, AOV, nurse call, CCTV, access
                and emergency lighting on one schedule with priority response.
            </p>
        </div>
        <a href="#quote" class="text-sm font-semibold text-[#ff6b00]">Discuss a contract →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($contractFeatures as $f): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-6">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold mb-4">✓</div>
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($f['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($f['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-10 bg-zinc-50 border border-zinc-200 rounded-3xl p-8 md:p-10 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <h3 class="text-2xl font-semibold tracking-tight text-black">Typical contract cover</h3>
            <ul class="mt-4 space-y-2 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Fire alarm periodic service &amp; certification</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Emergency lighting monthly / annual testing</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> EICR programmes and electrical remedials</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> AOV / smoke-control inspections</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Nurse call, CCTV and access control servicing</li>
            </ul>
        </div>
        <div class="text-center md:text-right">
            <p class="text-zinc-600 mb-4">Tell us site count, system types and preferred visit windows — we return a fixed-price proposal.</p>
            <a href="#quote" class="inline-flex px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get contract quote</a>
        </div>
    </div>
</section>

<!-- MANUFACTURERS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Brands</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Manufacturers we install &amp; service</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">
                    Brand pages with trade kits, install quotes and local coverage —
                    <?= count($mfrCatalog) ?>+ manufacturers across fire, electrical, security and more.
                </p>
            </div>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($featuredMfr as $mSlug => $mEntry): ?>
                <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SHOP TEASER -->
<section class="bg-zinc-100 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Trade shop</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Parts &amp; kits for commercial jobs</h2>
                <p class="mt-4 text-zinc-600 text-lg">
                    Fire panels, detectors, emergency lighting, CCTV, access control hardware and more —
                    Shopify-ready checkout when live. Ideal for engineers and FM stores stocking common spares.
                </p>
                <a href="<?= url('/shop/index.php') ?>"
                   class="inline-flex mt-6 px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">
                    Visit shop →
                </a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <?php
                $shopTeasers = [
                    ['fire-alarms', 'Fire systems'],
                    ['electrical', 'Electrical'],
                    ['cctv', 'CCTV'],
                    ['access-control', 'Access control'],
                ];
                foreach ($shopTeasers as [$slug, $label]):
                    $img = url('/assets/images/services/' . $slug . '.jpg');
                ?>
                <a href="<?= url('/shop/index.php') ?>"
                   class="relative rounded-3xl overflow-hidden min-h-[120px] border border-zinc-200 group">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-90 transition" loading="lazy"
                         onerror="this.style.display='none'">
                    <div class="relative p-4 h-full flex items-end bg-gradient-to-t from-black/50 to-transparent">
                        <span class="text-white font-semibold text-sm"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How commercial projects work</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($howItWorks as [$n, $t, $d]): ?>
            <div class="text-center px-4">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COVERAGE + CTA -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Serving <?= count($areas) ?>+ towns</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Stockport-based engineers covering Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria —
                practical for multi-site FM rounds and commercial call-outs.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">View all areas →</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
                <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Manufacturers →</a>
                <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Shop →</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk to the commercial team</h3>
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
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request a commercial / FM quote</h2>
            <p class="mt-3 text-zinc-600">
                Include site count, postcodes, system types and whether you need install, remedials or a maintenance contract.
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
                    <option value="Multi-service package" selected>Multi-service / FM package</option>
                    <option value="Maintenance contract">Maintenance contract</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Shop / products">Shop / products</option>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Site count, postcodes, system types (fire, electrical, AOV, nurse call, CCTV, access…), panel brands, cert expiry dates, install or maintenance contract…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit commercial request</button>
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
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
