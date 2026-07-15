<?php
/**
 * Planned maintenance contracts —
 * fire alarms, emergency lighting, nurse call, CCTV (PPM / SLA).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Planned Maintenance Contracts | Fire, Emergency Lighting, Nurse Call & CCTV';
$metaDesc = 'Planned preventative maintenance (PPM) contracts for fire alarms, emergency lighting, nurse call and CCTV. Audit-ready certification, multi-site schedules and priority call-outs across the North West.';
$metaKeywords = 'maintenance contracts, planned maintenance, PPM, fire alarm service contract, emergency lighting testing, nurse call maintenance, CCTV maintenance, BS 5839, BS 5266, North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/maintenance.php');

$services = getServices();
$areas = getAreas();

$waBase = 'https://wa.me/' . WHATSAPP;
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

/** Core systems covered by planned maintenance contracts on this page */
$contractSystems = [
    'fire-alarms' => [
        'title' => 'Fire alarms',
        'badge' => 'BS 5839',
        'blurb' => 'Periodic service, inspection and certification for addressable, conventional and wireless fire detection systems — panels, detectors, call points and sounders.',
        'includes' => [
            'Scheduled service visits to BS 5839',
            'Device test, clean & fault log review',
            'Certificate / report pack for audits & insurers',
            'Defect report with prioritised remedials',
            'Optional priority call-out within SLA',
        ],
        'frequency' => 'Typically 6-monthly (system-dependent)',
        'wa_text' => 'Hi Icomply, I need a fire alarm maintenance contract quote',
    ],
    'emergency-lighting' => [
        'title' => 'Emergency lighting',
        'badge' => 'BS 5266',
        'blurb' => 'Function tests, duration tests, logbooks and LED upgrades for escape routes, common parts and commercial estates — kept ready for inspection.',
        'includes' => [
            'Monthly function / annual duration testing',
            'BS 5266 logbook entries & certification',
            'Failed fitting identification & swap options',
            'LED conversion quotes where economic',
            'Portfolio scheduling across multi-site stock',
        ],
        'frequency' => 'Monthly + annual duration',
        'wa_text' => 'Hi Icomply, I need an emergency lighting maintenance contract quote',
    ],
    'nurse-call' => [
        'title' => 'Nurse call',
        'badge' => 'HTM 08-03',
        'blurb' => 'Planned maintenance for care home and healthcare nurse call — call points, pear leads, panels, overdoor lights and logging for compliance records.',
        'includes' => [
            'HTM-aligned planned preventative visits',
            'Call point, lead and indicator checks',
            'Panel battery / power health checks',
            'Fault diagnosis and parts supply options',
            'Documentation for CQC / estate audits',
        ],
        'frequency' => 'Agreed PPM calendar',
        'wa_text' => 'Hi Icomply, I need a nurse call maintenance contract quote',
    ],
    'cctv' => [
        'title' => 'CCTV',
        'badge' => 'IP / HD',
        'blurb' => 'Camera health-checks, NVR / DVR housekeeping, remote viewing checks and reactive support so coverage stays reliable for FM and security teams.',
        'includes' => [
            'Camera clean, focus & field-of-view review',
            'Recorder health, storage & firmware checks',
            'Remote viewing / user access verification',
            'Fault report with upgrade options if needed',
            'Priority engineer response on contract',
        ],
        'frequency' => 'Quarterly or bi-annual',
        'wa_text' => 'Hi Icomply, I need a CCTV maintenance contract quote',
    ],
];

$benefits = [
    ['title' => 'Planned, not reactive', 'text' => 'Move from emergency call-outs to a calendar of visits you can budget and audit against.'],
    ['title' => 'Standards-led', 'text' => 'BS 5839 fire, BS 5266 emergency lighting, HTM-aligned nurse call and structured CCTV checks.'],
    ['title' => 'Audit-ready packs', 'text' => 'Certificates, logbooks and service reports organised per site and system.'],
    ['title' => 'Priority response', 'text' => 'Contract holders get faster fault attendance under agreed SLAs.'],
];

$howItWorks = [
    ['1', 'Share your systems', 'Site list, panel / NVR brands, last service dates and preferred visit windows.'],
    ['2', 'Agree the SLA', 'We propose visit frequency, inclusions and fixed-price cover after scope is clear.'],
    ['3', 'Service & document', 'Engineers attend on schedule, complete works and issue audit-ready reports.'],
];

$contractTiers = [
    [
        'name' => 'Single system',
        'text' => 'One contract for fire, emergency lighting, nurse call or CCTV on a single site or small portfolio.',
        'points' => ['Fixed annual POA quote', 'Scheduled visits', 'Digital certificates'],
    ],
    [
        'name' => 'Multi-system',
        'text' => 'Bundle fire + emergency lighting (and more) into one programme — fewer access days, one account contact.',
        'points' => ['Combined visit days', 'One documentation pack', 'Clear remedials pricing'],
    ],
    [
        'name' => 'Multi-site / FM',
        'text' => 'Estate-wide PPM for facilities managers — fire, emergency lighting, nurse call and CCTV across multiple buildings.',
        'points' => ['Portfolio calendar', 'Priority call-outs', 'Per-site reporting'],
    ],
];

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
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <span class="text-white/80">Maintenance contracts</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    PPM · SLA · North West
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Planned maintenance<br>
                    <span class="text-[#ff6b00]">contracts</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Fire alarms, emergency lighting, nurse call and CCTV on one preventative programme —
                    scheduled visits, priority call-outs and audit-ready certification for landlords and FM teams.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get contract quote</a>
                    <a href="#systems" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">View systems</a>
                    <a href="<?= url('/pages/packages.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Compliance packages</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block">4</span> core systems</div>
                    <div><span class="text-white font-semibold text-xl block"><?= count($areas) ?>+</span> towns covered</div>
                    <div><span class="text-white font-semibold text-xl block">From / POA</span> fixed after scope</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($contractSystems as $slug => $card):
                    $img = url('/assets/images/services/' . $slug . '.jpg');
                ?>
                <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
                   class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[140px] bg-white/5 hover:border-[#ff6b00] transition">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt=""
                         class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition" loading="lazy"
                         onerror="this.style.display='none'">
                    <div class="relative p-5 h-full flex flex-col justify-end">
                        <div class="text-[10px] uppercase tracking-wider text-[#ff6b00] font-semibold mb-1"><?= htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="font-semibold text-white text-lg leading-tight"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs text-white/70 mt-1">View service →</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($benefits as $b): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($b['text'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- INTRO + PACKAGES LINK -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Why planned maintenance</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Stay compliant without chasing call-outs</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Reactive repairs cost more and leave gaps in your paper trail. A planned preventative maintenance (PPM)
                contract puts fire, emergency lighting, nurse call and CCTV on a clear calendar — with certificates
                ready for insurers, auditors and facilities records.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Budgetable annual cover with fixed-price quotes after survey</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Multi-site scheduling for FM teams and portfolio landlords</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Manufacturer-aware engineers across major panel and NVR brands</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Remedials and upgrades quoted clearly when systems need work</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/pages/packages.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">View compliance packages</a>
                <a href="<?= url('/pages/commercial.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Commercial / FM</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">All services</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Ideal for</h3>
            <ul class="mt-6 space-y-4 text-sm text-white/90">
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Facilities managers needing multi-site fire &amp; life-safety PPM</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Care homes and healthcare estates with nurse call programmes</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Commercial landlords and multi-tenant blocks</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Sites with CCTV estates that need regular health-checks</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Housing associations and managing agents</li>
            </ul>
            <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold hover:bg-orange-600 transition">Request maintenance quote</a>
        </div>
    </div>
</section>

<!-- SYSTEMS -->
<section id="systems" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Contract cover</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fire · emergency lighting · nurse call · CCTV</h2>
                <p class="mt-2 text-zinc-600 max-w-2xl">
                    Four high-demand systems for planned maintenance. Open a service hub for manufacturers, local coverage
                    and standalone install quotes — or request a multi-system contract below.
                </p>
            </div>
            <a href="<?= url('/pages/packages.php') ?>" class="text-sm font-semibold text-[#ff6b00]">See multi-service packages →</a>
        </div>

        <div class="grid md:grid-cols-2 gap-6 lg:gap-8">
            <?php foreach ($contractSystems as $slug => $sys):
                $name = $services[$slug] ?? $sys['title'];
                $img = url('/assets/images/services/' . $slug . '.jpg');
                $waUrl = $waBase . '?text=' . rawurlencode($sys['wa_text']);
            ?>
            <article id="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"
                     class="bg-white border border-zinc-200 rounded-3xl overflow-hidden flex flex-col hover:border-[#ff6b00] transition">
                <div class="h-40 bg-zinc-100 overflow-hidden relative">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> planned maintenance"
                         class="w-full h-full object-cover"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                    <div class="absolute top-4 left-4">
                        <span class="inline-block text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-[#0a2540] text-white">
                            <?= htmlspecialchars($sys['badge'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </div>
                <div class="p-6 md:p-8 flex-1 flex flex-col">
                    <h3 class="text-2xl font-semibold tracking-tight text-black"><?= htmlspecialchars($sys['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-2 text-sm text-zinc-600 flex-1"><?= htmlspecialchars($sys['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-3 text-xs text-zinc-500">
                        <span class="font-semibold text-black">Typical frequency:</span>
                        <?= htmlspecialchars($sys['frequency'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <ul class="mt-5 space-y-2 text-sm text-zinc-700">
                        <?php foreach ($sys['includes'] as $item): ?>
                            <li class="flex gap-2">
                                <span class="text-[#ff6b00] font-bold shrink-0">✓</span>
                                <span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#quote"
                           class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 text-white font-semibold text-sm"
                           data-contract="<?= htmlspecialchars($sys['title'], ENT_QUOTES, 'UTF-8') ?>">
                            Request quote
                        </a>
                        <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                           target="_blank" rel="noopener"
                           class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold text-sm">
                            WhatsApp
                        </a>
                        <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
                           class="px-6 py-3 rounded-2xl border border-zinc-200 hover:border-[#0a2540] font-semibold text-sm text-black">
                            Service hub →
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CONTRACT TIERS -->
<section id="contracts" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">How we package cover</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Single system to full estate</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Pricing is <strong class="text-black">from / POA</strong> until we agree scope — you always get a fixed quote before the contract starts.
                Prefer bundled compliance installs? See our
                <a href="<?= url('/pages/packages.php') ?>" class="font-semibold text-[#ff6b00] hover:underline">compliance packages</a>.
            </p>
        </div>
        <a href="#quote" class="text-sm font-semibold text-[#ff6b00]">Discuss a contract →</a>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
        <?php foreach ($contractTiers as $tier): ?>
        <div class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 hover:border-[#ff6b00] transition">
            <h3 class="text-xl font-semibold text-black tracking-tight"><?= htmlspecialchars($tier['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mt-3 text-sm text-zinc-600"><?= htmlspecialchars($tier['text'], ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="mt-6 space-y-2 text-sm text-zinc-800">
                <?php foreach ($tier['points'] as $pt): ?>
                    <li class="flex gap-2"><span class="text-[#ff6b00] shrink-0">●</span> <?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-10 bg-zinc-50 border border-zinc-200 rounded-3xl p-8 md:p-10 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <h3 class="text-2xl font-semibold tracking-tight text-black">Need install + ongoing cover?</h3>
            <p class="mt-3 text-zinc-600">
                Our multi-service packages combine landlord essentials, fire, security or full FM programmes —
                ideal when you want new works and planned maintenance under one plan.
            </p>
        </div>
        <div class="text-center md:text-right flex flex-col md:items-end gap-3">
            <a href="<?= url('/pages/packages.php') ?>"
               class="inline-flex px-8 py-4 rounded-2xl bg-[#0a2540] hover:bg-[#ff6b00] font-semibold text-white transition">
                Browse compliance packages →
            </a>
            <a href="<?= url('/pages/commercial.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Commercial &amp; facilities →</a>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-4">How maintenance contracts work</h2>
        <p class="text-center text-zinc-600 max-w-2xl mx-auto mb-12">
            Simple process, clear documentation — no surprise call-out culture once the programme is live.
        </p>
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

<!-- RELATED + CTA -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Also available</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Extend cover beyond the core four</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Many contracts also include electrical programmes, AOV / smoke control, access control, door entry
                and intruder alarms. Ask us to map the full estate.
            </p>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php
                $extra = ['electrical', 'aov-air-handling', 'access-control', 'door-entry', 'intercoms', 'intruder-alarm', 'gas-systems'];
                foreach ($extra as $slug):
                    if (!isset($services[$slug])) {
                        continue;
                    }
                ?>
                    <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                        <?= htmlspecialchars($services[$slug], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 flex flex-wrap gap-4 text-sm">
                <a href="<?= url('/pages/packages.php') ?>" class="font-semibold text-[#ff6b00]">Packages →</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="font-semibold text-[#ff6b00]">Landlords →</a>
                <a href="<?= url('/pages/commercial.php') ?>" class="font-semibold text-[#ff6b00]">Commercial / FM →</a>
                <a href="<?= url('/pages/areas/index.php') ?>" class="font-semibold text-[#ff6b00]">Areas →</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk maintenance today</h3>
            <p class="mt-3 text-white/80">Call, WhatsApp or use the quote form — we aim to respond within 2 hours on business days.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a planned maintenance contract quote') ?>"
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
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request a maintenance contract quote</h2>
            <p class="mt-3 text-zinc-600">
                Tell us which systems (fire, emergency lighting, nurse call, CCTV), site count and last service dates.
                All contracts are quoted fixed-price after scope is agreed — no obligation.
            </p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name / company" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" id="contract-service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select contract type…</option>
                    <option value="Maintenance contract" selected>Multi-system maintenance contract</option>
                    <option value="Fire Alarms maintenance">Fire alarms maintenance</option>
                    <option value="Emergency Lighting maintenance">Emergency lighting maintenance</option>
                    <option value="Nurse Call maintenance">Nurse call maintenance</option>
                    <option value="CCTV maintenance">CCTV maintenance</option>
                    <option value="Full FM package">Full FM / multi-service package</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> (service)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Site count, postcodes, systems (fire / emergency lighting / nurse call / CCTV), panel or NVR brands, last service dates, preferred visit windows…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit maintenance request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm">
            <a href="<?= htmlspecialchars($waBase, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi Icomply, I need a planned maintenance contract quote') ?>"
               target="_blank" rel="noopener"
               class="px-5 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold">WhatsApp us instead</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0a2540]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= url('/pages/packages.php') ?>"
               class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#ff6b00]">View packages</a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<script>
(function () {
    var select = document.getElementById('contract-service');
    if (!select) return;
    document.querySelectorAll('[data-contract]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name = btn.getAttribute('data-contract');
            if (!name) return;
            var map = {
                'Fire alarms': 'Fire Alarms maintenance',
                'Emergency lighting': 'Emergency Lighting maintenance',
                'Nurse call': 'Nurse Call maintenance',
                'CCTV': 'CCTV maintenance'
            };
            var val = map[name] || 'Maintenance contract';
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
