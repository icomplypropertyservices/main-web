<?php
/**
 * Emergency / reactive call-out landing —
 * fire panel faults, power issues, security failures.
 * Priority reactive attendance where capacity allows (not a 24/7 guarantee).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Emergency Call-Outs | Fire, Power & Security Faults';
$metaDesc = 'Priority reactive call-outs for fire panel faults, power issues and security failures across Stockport, Greater Manchester & the North West. Phone or WhatsApp for priority reactive attendance where capacity allows.';
$metaKeywords = 'emergency call out Stockport, fire panel fault, fire alarm engineer call out, power failure electrician, security system failure, reactive maintenance North West, CCTV fault, access control failure';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/emergency.php');

$services = getServices();
$areas = getAreas();

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$waText = rawurlencode('Hi Icomply, I need a priority reactive call-out. Fault type / postcode: ');
$waUrl = 'https://wa.me/' . WHATSAPP . '?text=' . $waText;

// Primary reactive scenarios
$scenarios = [
    'fire-alarms' => [
        'title' => 'Fire panel faults',
        'badge' => 'Fire systems',
        'blurb' => 'Panel in fault, sounders going off, devices offline, network/loop issues, batteries failing or a system that will not reset after activation.',
        'points' => ['Panel diagnostics', 'Device / loop faults', 'Battery & PSU issues', 'Reset & certification notes'],
    ],
    'electrical' => [
        'title' => 'Power issues',
        'badge' => 'Electrical',
        'blurb' => 'Partial or full power loss, RCD/MCB trips that will not stay up, damaged consumer units, supply issues affecting critical plant or common parts.',
        'points' => ['Trip investigation', 'Consumer unit faults', 'Safe isolation', 'Remedial electrical works'],
    ],
    'intruder-alarm' => [
        'title' => 'Security failures',
        'badge' => 'Security',
        'blurb' => 'Intruder alarms in permanent fault, false activations, keypad/panel failures, CCTV downtime, door access not releasing or locking correctly.',
        'points' => ['Intruder panel faults', 'CCTV / NVR downtime', 'Access control failures', 'Door entry & intercoms'],
    ],
];

$extraReactive = [
    'emergency-lighting' => 'Emergency lighting not charging, bulkheads failed, or duration-test failures after a power event.',
    'cctv' => 'Camera outages, recorder faults, remote viewing down or hard-drive / storage failures.',
    'access-control' => 'Readers offline, maglocks stuck, fire-override concerns or credential system faults.',
    'door-entry' => 'Handsets dead, door not releasing, video entry black screens or trade-button failures.',
    'aov-air-handling' => 'AOV panel faults, vents stuck open/closed or smoke-control system warnings.',
    'nurse-call' => 'Care-home nurse call panels, handsets or zone faults needing reactive attendance.',
];

$whatToTellUs = [
    ['Site & postcode', 'Full address and access notes so the nearest available engineer can be routed.'],
    ['System type', 'Fire panel, electrical, CCTV, access, door entry, AOV, nurse call — brand if known.'],
    ['What is happening', 'Fault light, constant alarm, no power, doors stuck, cameras offline, etc.'],
    ['Urgency & occupancy', 'Occupied building, evacuation risk, commercial downtime or empty premises.'],
];

$trust = [
    ['title' => 'Phone & WhatsApp first', 'text' => 'For reactive jobs, call or message — fastest route to an engineer'],
    ['title' => 'Priority where capacity allows', 'text' => 'We prioritise live faults; attendance depends on engineer availability'],
    ['title' => 'Fire · power · security', 'text' => 'Panel faults, electrical issues and security system failures'],
    ['title' => 'Stockport-based NW cover', 'text' => 'Greater Manchester, Lancashire, Cheshire, Merseyside & Cumbria'],
];

$howItWorks = [
    ['1', 'Call or WhatsApp', 'Tell us the site, postcode, system type and what the fault looks like — photos help.'],
    ['2', 'We triage priority', 'We confirm capacity, estimated attendance window and any immediate safety steps.'],
    ['3', 'Attend & make safe', 'Engineer diagnoses, repairs where possible, or quotes remedials and documents the visit.'],
];

$faqs = [
    [
        'q' => 'Do you offer a 24/7 emergency service?',
        'a' => 'We provide priority reactive attendance where capacity allows — not a guaranteed 24/7 SLA. For the fastest response, phone or WhatsApp with the site postcode and fault details. Contract holders and live safety-critical faults are prioritised where we can.',
    ],
    [
        'q' => 'What faults can you attend?',
        'a' => 'Common reactive jobs include fire panel faults and false activations, power trips and supply issues, intruder alarm failures, CCTV downtime, access control and door entry faults, emergency lighting failures, AOV warnings and nurse call issues.',
    ],
    [
        'q' => 'What should I do if a fire alarm will not silence?',
        'a' => 'Follow your site fire procedures first. If it is a genuine fire, call 999. For system faults after evacuation checks, contact us with the panel brand, any fault codes and whether sounders are still running.',
    ],
    [
        'q' => 'Are call-outs available for non-contract customers?',
        'a' => 'Yes, subject to capacity. Existing maintenance customers are prioritised where possible. Ad-hoc call-outs are quoted clearly before works beyond initial diagnosis and make-safe.',
    ],
];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
$homeUrl = rtrim(SITE_URL, '/') . '/';
?>

<!-- HERO — phone / WhatsApp prominent -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 15% 30%,#ff6b00,transparent 42%),radial-gradient(circle at 85% 10%,#ef4444,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span aria-hidden="true">/</span>
            <span class="text-white/80">Emergency call-outs</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 border border-red-400/30 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></span>
                    Reactive · Priority where capacity allows
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Fire panel, power<br>
                    &amp; security faults.<br>
                    <span class="text-[#ff6b00]">Call us now.</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Priority reactive attendance for fire system faults, electrical / power issues and security failures
                    across Stockport, Greater Manchester and the North West —
                    <strong class="text-white font-semibold">where capacity allows</strong>.
                </p>

                <!-- Primary CTAs: phone + WhatsApp -->
                <div class="mt-8 flex flex-col sm:flex-row flex-wrap gap-3">
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                       class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white text-lg shadow-lg shadow-orange-900/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold text-white text-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp now
                    </a>
                    <a href="#quote"
                       class="inline-flex items-center justify-center px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">
                        Non-urgent form
                    </a>
                </div>

                <p class="mt-5 text-sm text-white/55 max-w-lg">
                    Not a guaranteed 24/7 SLA. We aim for priority reactive attendance where capacity allows —
                    live safety-critical and contract sites first.
                </p>

                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block">Fire</span> Panel &amp; device faults</div>
                    <div><span class="text-white font-semibold text-xl block">Power</span> Trips &amp; supply issues</div>
                    <div><span class="text-white font-semibold text-xl block">Security</span> Alarm · CCTV · access</div>
                </div>
            </div>

            <!-- Sticky-feel contact card -->
            <div class="bg-white/5 border border-white/15 rounded-3xl p-7 md:p-9 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Fastest response</div>
                <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">Need an engineer on site?</h2>
                <p class="mt-3 text-white/75 text-sm leading-relaxed">
                    Phone or WhatsApp with postcode, system type and fault description.
                    We will confirm capacity and an attendance window — we do not promise instant or 24-hour cover.
                </p>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="mt-6 flex items-center gap-4 p-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 transition group">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-white/80 uppercase tracking-wider">Call now</div>
                        <div class="text-xl font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </a>
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener"
                   class="mt-3 flex items-center gap-4 p-4 rounded-2xl bg-green-600 hover:bg-green-500 transition">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-white/80 uppercase tracking-wider">Message</div>
                        <div class="text-xl font-semibold">WhatsApp</div>
                    </div>
                </a>
                <div class="mt-5 pt-5 border-t border-white/10 text-xs text-white/50 space-y-1">
                    <p>Email: <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="text-white/70 hover:text-white underline"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a></p>
                    <p>Base: <?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
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

<!-- SCENARIOS -->
<section id="faults" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Reactive call-outs</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What we attend for</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Live faults on fire, electrical and security systems — diagnosis, make-safe and repair where capacity allows.
                Planned servicing remains the best way to reduce emergency visits.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-full bg-[#ff6b00] text-white text-sm font-semibold hover:bg-orange-600"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-5 py-2.5 rounded-full bg-green-600 text-white text-sm font-semibold hover:bg-green-500">WhatsApp</a>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($scenarios as $slug => $card):
            $img = url('/assets/images/services/' . $slug . '.jpg');
            $svcName = $services[$slug] ?? $card['title'];
        ?>
        <div class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-40 bg-zinc-100 overflow-hidden relative">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8') ?> reactive call-out"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy"
                     onerror="this.parentElement.style.display='none'">
                <div class="absolute top-3 left-3 text-[10px] uppercase tracking-wider font-semibold px-2.5 py-1 rounded-full bg-[#0a2540] text-white">
                    <?= htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
            <div class="p-6 flex-1 flex flex-col">
                <h3 class="font-semibold text-xl text-black tracking-tight"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($card['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <ul class="mt-4 space-y-1.5 text-sm text-zinc-700">
                    <?php foreach ($card['points'] as $pt): ?>
                        <li class="flex gap-2"><span class="text-[#ff6b00] shrink-0">●</span> <?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= url('/pages/services/' . $slug . '.php') ?>" class="mt-5 text-sm font-semibold text-[#ff6b00]">Service hub →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-12">
        <h3 class="text-lg font-semibold text-black mb-4">Also covered on reactive visits</h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($extraReactive as $slug => $blurb):
                if (!isset($services[$slug])) {
                    continue;
                }
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="block bg-zinc-50 border border-zinc-200 rounded-2xl p-5 hover:border-[#ff6b00] transition">
                <div class="font-semibold text-black"><?= htmlspecialchars($services[$slug], ENT_QUOTES, 'UTF-8') ?></div>
                <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CAPACITY NOTICE -->
<section class="bg-amber-50 border-y border-amber-100">
    <div class="max-w-7xl mx-auto px-6 py-10 md:py-12">
        <div class="flex flex-col md:flex-row gap-6 md:items-center">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-700 font-bold text-xl shrink-0">!</div>
            <div class="flex-1">
                <h2 class="text-xl md:text-2xl font-semibold text-black tracking-tight">Priority reactive attendance — where capacity allows</h2>
                <p class="mt-2 text-zinc-700 text-sm md:text-base max-w-3xl">
                    We are not advertising a guaranteed 24/7 emergency service. Response depends on engineer availability,
                    location, weather and existing booked work. Safety-critical fire and power faults are triaged first;
                    contract customers are prioritised where possible. Always call 999 for an actual fire or life-threatening emergency.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold text-center hover:bg-[#ff6b00] transition">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-6 py-3 rounded-2xl bg-green-600 text-white text-sm font-semibold text-center hover:bg-green-500">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<!-- WHAT TO TELL US -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Before you call</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What to tell us</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Clear details help us confirm capacity and send the right engineer with the right parts.
            </p>
            <div class="mt-8 grid sm:grid-cols-2 gap-4">
                <?php foreach ($whatToTellUs as [$t, $d]): ?>
                <div class="bg-white border border-zinc-200 rounded-2xl p-5">
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Ideal for</h3>
            <ul class="mt-6 space-y-4 text-sm text-white/90">
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Landlords &amp; agents with a live fire or electrical fault</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Facilities managers needing a reactive engineer</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Care homes and commercial sites with system downtime</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Sites with CCTV, access or door-entry failures</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Customers who want planned maintenance after the fix</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Call now</a>
                <a href="<?= url('/pages/commercial.php') ?>" class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10">FM contracts</a>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-4">How reactive call-outs work</h2>
        <p class="text-center text-zinc-600 max-w-2xl mx-auto mb-12">Phone or WhatsApp first for live faults. The form below is better for non-urgent follow-ups and planned work.</p>
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

<!-- REDUCE FUTURE CALLOUTS -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Prevention</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fewer emergencies with planned cover</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Many reactive visits follow skipped servicing — fire panels, emergency lighting, electrical inspections
                and security systems all benefit from a planned schedule. After we clear the fault, we can quote a
                maintenance programme so the next visit is planned, not urgent.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/pages/packages.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">View packages</a>
                <a href="<?= url('/pages/commercial.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Commercial / FM</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Landlords</a>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <?php
            $prevSlugs = [
                'fire-alarms' => 'Fire servicing',
                'electrical' => 'EICR programmes',
                'emergency-lighting' => 'Emergency lighting',
                'cctv' => 'Security systems',
            ];
            foreach ($prevSlugs as $slug => $label):
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="relative rounded-3xl overflow-hidden min-h-[130px] border border-zinc-200 group">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt=""
                     class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-90 transition" loading="lazy"
                     onerror="this.style.display='none'">
                <div class="relative p-4 h-full flex items-end bg-gradient-to-t from-black/55 to-transparent">
                    <span class="text-white font-semibold text-sm"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQs -->
<section class="bg-white border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">FAQs</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Emergency &amp; reactive questions</h2>
        </div>
        <div class="space-y-4">
            <?php foreach ($faqs as $faq): ?>
            <details class="group bg-zinc-50 border border-zinc-200 rounded-2xl p-5 open:border-[#ff6b00]/40">
                <summary class="font-semibold text-black cursor-pointer list-none flex justify-between items-center gap-4">
                    <?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?>
                    <span class="text-[#ff6b00] text-xl shrink-0 group-open:rotate-45 transition">+</span>
                </summary>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- COVERAGE + CONTACT CTA -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Serving <?= count($areas) ?>+ towns</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Stockport-based engineers covering Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria.
                Travel time and capacity affect how quickly we can attend — always phone or WhatsApp for live faults.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">View all areas →</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
                <a href="<?= url('/contact.php') ?>" class="text-sm font-semibold text-[#ff6b00]">Contact →</a>
            </div>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk to us now</h3>
            <p class="mt-3 text-white/80">For reactive jobs, phone and WhatsApp beat the form. We confirm priority reactive attendance where capacity allows.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="#quote" class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10">Non-urgent form</a>
            </div>
        </div>
    </div>
</section>

<!-- QUOTE (non-urgent) -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Non-urgent</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Follow-up or planned request</h2>
            <p class="mt-3 text-zinc-600">
                For <strong>live faults</strong>, please
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] font-semibold hover:underline">call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                or
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-green-700 font-semibold hover:underline">WhatsApp</a>
                first. Use this form for non-urgent remedials, quotes after a visit, or planned maintenance.
            </p>
        </div>

        <!-- Mobile sticky-style contact strip -->
        <div class="mb-8 grid sm:grid-cols-2 gap-3">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 text-white font-semibold">
                Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank" rel="noopener"
               class="flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold">
                WhatsApp
            </a>
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
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select fault / service…</option>
                    <option value="Emergency / reactive call-out" selected>Emergency / reactive call-out</option>
                    <option value="Fire Alarms">Fire panel / fire alarm fault</option>
                    <option value="Electrical">Power / electrical issue</option>
                    <option value="Intruder Alarm">Intruder alarm fault</option>
                    <option value="CCTV">CCTV failure</option>
                    <option value="Access Control">Access control failure</option>
                    <option value="Door Entry">Door entry fault</option>
                    <option value="Emergency Lighting">Emergency lighting fault</option>
                    <option value="AOV / Air Handling">AOV / smoke control fault</option>
                    <option value="Nurse Call">Nurse call fault</option>
                    <option value="Maintenance contract">Maintenance contract (prevent future call-outs)</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Postcode / address, system type & brand if known, fault description, when it started, occupancy, access notes…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
                Live faults: call or WhatsApp for priority reactive attendance where capacity allows.
            </p>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0a2540] hover:text-[#ff6b00]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0a2540] hover:text-[#ff6b00]"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="font-semibold text-green-700 hover:text-green-600">WhatsApp</a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
