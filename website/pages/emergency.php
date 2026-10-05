<?php
/**
 * Emergency call-out / 24-hour job lane.
 * Fire, power, gas (after the official emergency service) and security faults.
 * Make-safe attendance where capacity allows — not a contracted 24/7 SLA.
 * No published call-out fees. Terms are explained before an engineer travels.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Emergency Call-Out & 24-Hour Lane | Fire, Power & Security';
$metaDesc = 'Emergency call-out and 24-hour make-safe lane for fire panel faults, power loss, gas breakdowns and security failures across Stockport, Greater Manchester and the North West. Attendance where capacity allows — terms explained before we travel. Not a guaranteed 24/7 SLA.';
$metaKeywords = 'emergency call out Stockport, 24 hour emergency electrician, fire panel fault, emergency gas engineer, security system failure, reactive maintenance North West, out of hours electrician';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/emergency.php');

$services = getServices();
$areas = getAreas();

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$waText = rawurlencode('Hi iComply, emergency / 24h lane. Fault type, postcode and whether this is live or within 24 hours: ');
$waUrl = 'https://wa.me/' . WHATSAPP . '?text=' . $waText;

$windows = [
    [
        'kicker' => 'Now',
        'title' => 'Live fault',
        'text' => 'Phone or WhatsApp. We triage the site, postcode and what is happening, then confirm whether an engineer can attend and on what terms — before anyone travels.',
    ],
    [
        'kicker' => 'Priority',
        'title' => 'Make-safe first',
        'text' => 'Priority make-safe where capacity, travel and the diary allow. Night and weekend visits focus on isolation and making safe. Permanent repairs may wait for parts or daylight.',
    ],
    [
        'kicker' => 'Planned',
        'title' => 'After the fault',
        'text' => 'Use the form for remedials, a quote after a visit, or a maintenance programme so the next job is booked rather than urgent.',
    ],
];

$scenarios = [
    'fire-alarms' => [
        'title' => 'Fire panel faults',
        'badge' => 'Fire systems',
        'blurb' => 'Panel in fault, sounders running, devices offline, loop or network issues, batteries failing, or a system that will not reset after activation.',
        'points' => ['Panel diagnostics', 'Device / loop faults', 'Battery & PSU issues', 'Make-safe & notes'],
        'href' => '/pages/services/fire-alarms.php',
    ],
    'electrical' => [
        'title' => 'Power & 24h electrical',
        'badge' => 'Electrical',
        'blurb' => 'Power loss, burning smells, water near electrics, or trips that will not stay up. The 24-hour lane is make-safe first, then a planned repair.',
        'points' => ['Isolation & make-safe', 'Trip investigation', 'Consumer unit faults', 'Follow-up repair quote'],
        'href' => '/pages/keywords/24-hour-emergency-electrician.php',
    ],
    'gas-systems' => [
        'title' => 'Gas & heating breakdowns',
        'badge' => 'Gas Safe',
        'blurb' => 'No heat, no hot water, or an appliance lock-out. If you smell gas, call the National Gas Emergency Service on 0800 111 999 before you call us.',
        'points' => ['Smell of gas: 0800 111 999 first', 'Breakdowns after it is safe', 'Isolation of faulty appliances', 'Fixed quote after diagnosis'],
        'href' => '/pages/keywords/emergency-gas-engineer.php',
    ],
    'intruder-alarm' => [
        'title' => 'Security failures',
        'badge' => 'Security',
        'blurb' => 'Intruder alarms stuck in fault, false activations, CCTV down, or doors that will not lock or release.',
        'points' => ['Intruder panel faults', 'CCTV / NVR downtime', 'Access control failures', 'Door entry & intercoms'],
        'href' => '/pages/services/intruder-alarm.php',
    ],
];

$extraReactive = [
    'emergency-lighting' => 'Emergency lighting not charging, failed bulkheads, or duration-test failures after a power event.',
    'cctv' => 'Camera outages, recorder faults, remote viewing down, or storage failures.',
    'access-control' => 'Readers offline, maglocks stuck, fire-override concerns, or credential faults.',
    'door-entry' => 'Handsets dead, door not releasing, video entry black screens, or trade-button failures.',
    'aov-air-handling' => 'AOV panel faults, vents stuck, or smoke-control warnings.',
    'nurse-call' => 'Care-home nurse call panels, handsets, or zone faults needing reactive attendance.',
];

$guides = [
    ['/pages/keywords/24-hour-emergency-electrician.php', '24 hour emergency electrician'],
    ['/pages/keywords/emergency-electrician.php', 'Emergency electrician'],
    ['/pages/keywords/emergency-gas-engineer.php', 'Emergency gas engineer'],
    ['/pages/services/electrical.php', 'Electrical services'],
    ['/pages/services/gas-systems.php', 'Gas systems'],
    ['/pages/maintenance.php', 'Planned maintenance'],
];

$whatToTellUs = [
    ['Site & postcode', 'Full address and access notes so we can judge travel and who can attend.'],
    ['System type', 'Fire panel, electrical, gas, CCTV, access, door entry, AOV, nurse call — brand if known.'],
    ['What is happening', 'Fault light, constant alarm, no power, smell of gas, doors stuck, cameras offline.'],
    ['Window', 'Live now, within 24 hours, or a planned follow-up. Say if anyone is vulnerable on site.'],
];

$trust = [
    ['title' => 'Phone & WhatsApp first', 'text' => 'Live faults: call or message. That is faster than the form.'],
    ['title' => '24-hour lane', 'text' => 'Make-safe attendance where capacity allows — not a contracted 24/7 SLA.'],
    ['title' => 'Terms before travel', 'text' => 'Attendance is POA. We explain terms on the call before despatch.'],
    ['title' => 'Stockport-based NW cover', 'text' => 'Greater Manchester, Lancashire, Cheshire, Merseyside & Cumbria'],
];

$howItWorks = [
    ['1', 'Call or WhatsApp', 'Site, postcode, system and what the fault looks like. Photos help. Say if it is live or within 24 hours.'],
    ['2', 'We confirm the window', 'Capacity, an honest attendance window, and POA terms — before an engineer is sent.'],
    ['3', 'Make safe, then repair', 'Isolate and make safe first. Lasting repairs are quoted and booked when parts and safe conditions allow.'],
];

$faqs = [
    [
        'q' => 'Is the 24-hour lane a guaranteed 24/7 SLA?',
        'a' => 'No. It is a priority make-safe lane for genuine safety or business-critical faults, attended where engineer capacity, travel and the diary allow. It is not a contracted 24/7 response time. Contract sites and live safety-critical faults are prioritised where we can. Terms are explained before anyone travels.',
    ],
    [
        'q' => 'What counts as a 24-hour call-out?',
        'a' => 'Power loss with a safety risk, burning smells, water near electrics, a fire panel that will not reset, a security system that has failed, or a heating breakdown that cannot wait for a routine slot. Routine upgrades, quotes and certificate renewals use the planned window.',
    ],
    [
        'q' => 'Will you finish a full repair in the middle of the night?',
        'a' => 'Make-safe is the priority. Permanent repairs go ahead when parts, access and safe working conditions allow. You will know that before we despatch.',
    ],
    [
        'q' => 'Do you publish a call-out price?',
        'a' => 'No. Emergency attendance is price on application. Out-of-hours work usually carries a premium. The terms are explained on the phone so you can decide before an engineer sets off. We do not list a pound call-out fee on this page.',
    ],
    [
        'q' => 'What should I do if I smell gas?',
        'a' => 'Do not use switches or naked flames. Open windows if it is safe, turn the gas off at the meter if you can do so safely, leave if advised, and call the National Gas Emergency Service on 0800 111 999. Contact us after the situation is safe for a Gas Safe breakdown or repair visit.',
    ],
    [
        'q' => 'What if a fire alarm will not silence?',
        'a' => 'Follow your site fire procedure first. If it is a genuine fire, call 999. For a system fault after evacuation checks, contact us with the panel brand, any fault codes and whether sounders are still running.',
    ],
    [
        'q' => 'Can non-contract customers use this lane?',
        'a' => 'Yes, subject to capacity. Existing maintenance customers are prioritised where possible. Work beyond initial diagnosis and make-safe is quoted before it goes ahead.',
    ],
];

$faqEntities = [];
foreach ($faqs as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq['a'],
        ],
    ];
}
$pageUrl = url('/pages/emergency.php');
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => 'Emergency call-out and 24-hour make-safe lane',
            'serviceType' => 'Reactive property maintenance call-out',
            'description' => $metaDesc,
            'url' => $pageUrl,
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'telephone' => PHONE,
                'email' => EMAIL,
                'url' => rtrim(SITE_URL, '/') . '/',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '17 Woodlands Park Road, Offerton',
                    'addressLocality' => 'Stockport',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
            ],
            'areaServed' => 'North West England',
        ],
        [
            '@type' => 'FAQPage',
            'url' => $pageUrl,
            'mainEntity' => $faqEntities,
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Emergency call-out / 24h lane', 'item' => $pageUrl],
            ],
        ],
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
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<style>
@media (max-width: 767px) {
    #mobile-sticky-cta { display: none !important; }
    #emergency-lane-sticky.mobile-sticky-cta { display: flex; }
    #emergency-lane-sticky.is-hidden { display: none !important; }
}
</style>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white" data-job-lane="emergency-24h">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span aria-hidden="true">/</span>
            <span class="text-white/80">Emergency call-out / 24h lane</span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 border border-red-400/30 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-red-400 animate-pulse" aria-hidden="true"></span>
                    Emergency · 24-hour make-safe lane
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    Emergency call-out<br>
                    &amp; 24-hour lane.<br>
                    <span class="text-[#ff6b00]">Call us now.</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                    Fire panel faults, power loss, gas and heating breakdowns, and security failures
                    across Stockport, Greater Manchester and the North West.
                    We attend <strong class="text-white font-semibold">when engineer capacity, travel and the diary allow</strong>
                    — make-safe first, then a planned repair.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row flex-wrap gap-3">
                    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                       class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white text-lg shadow-lg shadow-orange-900/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold text-white text-lg">
                        WhatsApp now
                    </a>
                    <a href="#windows"
                       class="inline-flex items-center justify-center px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">
                        24h windows
                    </a>
                </div>

                <p class="mt-5 text-sm text-white/55 max-w-lg">
                    Not a guaranteed 24/7 SLA and not a published call-out fee.
                    Life-threatening emergencies: call 999. Smell of gas: National Gas Emergency Service 0800 111 999 first.
                </p>

                <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                    <div><span class="text-white font-semibold text-xl block">Fire</span> Panel &amp; device faults</div>
                    <div><span class="text-white font-semibold text-xl block">Power</span> 24h electrical make-safe</div>
                    <div><span class="text-white font-semibold text-xl block">Gas</span> After 0800 111 999</div>
                    <div><span class="text-white font-semibold text-xl block">Security</span> Alarm · CCTV · access</div>
                </div>
            </div>

            <div class="bg-white/5 border border-white/15 rounded-3xl p-7 md:p-9 backdrop-blur-sm">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Phone or WhatsApp</div>
                <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">Need an engineer on site?</h2>
                <p class="mt-3 text-white/75 text-sm leading-relaxed">
                    Phone or WhatsApp with postcode, fault and whether you need someone now or within 24 hours.
                    We confirm capacity and POA terms before despatch.
                </p>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
                   class="mt-6 flex items-center gap-4 p-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 transition">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-white/80 uppercase tracking-wider">Call now</div>
                        <div class="text-xl font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </a>
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener"
                   class="mt-3 flex items-center gap-4 p-4 rounded-2xl bg-green-600 hover:bg-green-500 transition">
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0 text-lg font-semibold" aria-hidden="true">W</div>
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

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($trust as $t): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0B1F3A]/10 flex items-center justify-center text-[#0B1F3A] font-bold shrink-0" aria-hidden="true">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($t['text'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="windows" class="bg-zinc-50 border-b">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Job lane</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Three windows on this lane</h2>
        <p class="mt-3 text-zinc-600 max-w-2xl">
            The emergency lane is for faults that should not wait for a routine diary slot.
            Attendance inside 24 hours depends on engineer availability, location and what is already booked.
        </p>
        <div class="mt-10 grid md:grid-cols-3 gap-6">
            <?php foreach ($windows as $w): ?>
            <article class="bg-white border border-zinc-200 rounded-3xl p-6">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($w['kicker'], ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="mt-2 text-xl font-semibold text-black"><?= htmlspecialchars($w['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($w['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="bg-red-50 border-b border-red-100" aria-label="Official emergency numbers">
    <div class="max-w-7xl mx-auto px-6 py-8 grid md:grid-cols-2 gap-6">
        <div>
            <h2 class="font-semibold text-black">Fire or a life-threatening emergency</h2>
            <p class="mt-1 text-sm text-zinc-700">Call <a class="font-semibold text-[#0B1F3A] underline" href="tel:999">999</a>. This page is for property-system faults after the emergency services, not a substitute for them.</p>
        </div>
        <div>
            <h2 class="font-semibold text-black">Smell of gas</h2>
            <p class="mt-1 text-sm text-zinc-700">Call the National Gas Emergency Service on <a class="font-semibold text-[#0B1F3A] underline" href="tel:0800111999">0800 111 999</a>. Book us after the supply is safe.</p>
        </div>
    </div>
</section>

<section id="faults" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">What this lane attends</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fire, power, gas and security</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                Diagnosis and make-safe on live faults. Planned servicing is still the better way to avoid the next emergency visit.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-full bg-[#ff6b00] text-white text-sm font-semibold hover:bg-orange-600"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-5 py-2.5 rounded-full bg-green-600 text-white text-sm font-semibold hover:bg-green-500">WhatsApp</a>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <?php foreach ($scenarios as $slug => $card):
            $img = url('/assets/images/services/' . $slug . '.jpg');
        ?>
        <article class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-40 bg-zinc-100 overflow-hidden relative">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?> — emergency call-out"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     loading="lazy"
                     onerror="this.parentElement.style.display='none'">
                <div class="absolute top-3 left-3 text-[10px] uppercase tracking-wider font-semibold px-2.5 py-1 rounded-full bg-[#0B1F3A] text-white">
                    <?= htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
            <div class="p-6 flex-1 flex flex-col">
                <h3 class="font-semibold text-xl text-black tracking-tight"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($card['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <ul class="mt-4 space-y-1.5 text-sm text-zinc-700">
                    <?php foreach ($card['points'] as $pt): ?>
                        <li class="flex gap-2"><span class="text-[#ff6b00] shrink-0" aria-hidden="true">●</span> <?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= url($card['href']) ?>" class="mt-5 text-sm font-semibold text-[#ff6b00]">Related page →</a>
            </div>
        </article>
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

<section class="bg-amber-50 border-y border-amber-100">
    <div class="max-w-7xl mx-auto px-6 py-10 md:py-12">
        <div class="flex flex-col md:flex-row gap-6 md:items-center">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center text-amber-700 font-bold text-xl shrink-0" aria-hidden="true">!</div>
            <div class="flex-1">
                <h2 class="text-xl md:text-2xl font-semibold text-black tracking-tight">Attendance depends on capacity</h2>
                <p class="mt-2 text-zinc-700 text-sm md:text-base max-w-3xl">
                    Response depends on engineer availability, location and existing booked work.
                    Safety-critical fire and power faults are triaged first. Out-of-hours attendance is POA and explained before despatch.
                    This lane does not promise a clock-start SLA.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-3 rounded-2xl bg-[#0B1F3A] text-white text-sm font-semibold text-center hover:bg-[#ff6b00] transition">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-6 py-3 rounded-2xl bg-green-600 text-white text-sm font-semibold text-center hover:bg-green-500">WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Before you call</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What to tell us</h2>
            <p class="mt-4 text-zinc-600 text-lg">Clear details help us confirm a window and send the right engineer.</p>
            <div class="mt-8 grid sm:grid-cols-2 gap-4">
                <?php foreach ($whatToTellUs as [$t, $d]): ?>
                <div class="bg-white border border-zinc-200 rounded-2xl p-5">
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">Who this lane is for</h3>
            <ul class="mt-6 space-y-4 text-sm text-white/90">
                <li class="flex gap-3"><span class="text-[#ff6b00]" aria-hidden="true">●</span> Landlords and agents with a live fire, power or heating fault</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]" aria-hidden="true">●</span> Facilities managers who need a reactive engineer</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]" aria-hidden="true">●</span> Care homes and commercial sites with system downtime</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]" aria-hidden="true">●</span> Sites with CCTV, access or door-entry failures</li>
                <li class="flex gap-3"><span class="text-[#ff6b00]" aria-hidden="true">●</span> Customers who want planned maintenance after the fix</li>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Call now</a>
                <a href="<?= url('/pages/maintenance.php') ?>" class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10">Maintenance contracts</a>
            </div>
        </div>
    </div>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-4">How the 24-hour lane works</h2>
        <p class="text-center text-zinc-600 max-w-2xl mx-auto mb-12">Phone or WhatsApp for live faults. The form is for non-urgent follow-ups and for logging a within-24-hours request if you cannot call.</p>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($howItWorks as [$n, $t, $d]): ?>
            <div class="text-center px-4">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0B1F3A] text-white font-bold flex items-center justify-center text-lg"><?= htmlspecialchars($n, ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Prevention</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fewer emergencies with planned cover</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Many reactive visits follow skipped servicing. After we clear the fault, we can quote a maintenance programme so the next visit is planned.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/pages/maintenance.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0B1F3A] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Maintenance contracts</a>
                <a href="<?= url('/pages/packages.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Packages</a>
                <a href="<?= url('/pages/landlords.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Landlords</a>
            </div>
        </div>
        <div>
            <h3 class="font-semibold text-black mb-4">Related guides</h3>
            <ul class="space-y-2">
                <?php foreach ($guides as [$href, $label]): ?>
                <li><a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url($href) ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?> →</a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="bg-white border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">FAQs</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Emergency and 24-hour questions</h2>
        </div>
        <div class="space-y-4">
            <?php foreach ($faqs as $faq): ?>
            <details class="group bg-zinc-50 border border-zinc-200 rounded-2xl p-5 open:border-[#ff6b00]/40">
                <summary class="font-semibold text-black cursor-pointer list-none flex justify-between items-center gap-4">
                    <?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?>
                    <span class="text-[#ff6b00] text-xl shrink-0 group-open:rotate-45 transition" aria-hidden="true">+</span>
                </summary>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Serving <?= count($areas) ?>+ towns</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Stockport-based engineers covering Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria.
                Travel time affects whether a within-24-hours visit is realistic — phone or WhatsApp for a live fault.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">View all areas →</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
            </div>
        </div>
        <div class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk to us now</h3>
            <p class="mt-3 text-white/80">For live faults, phone and WhatsApp beat the form. We confirm a window and POA terms before travel.</p>
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

<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Request</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Log a 24-hour or follow-up job</h2>
            <p class="mt-3 text-zinc-600">
                For a <strong>live fault</strong>,
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] font-semibold hover:underline">call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                or
                <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-green-700 font-semibold hover:underline">WhatsApp</a>
                first. This form logs a within-24-hours request or a planned follow-up. It does not dispatch an engineer by itself.
            </p>
        </div>

        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="lane" value="emergency-24h">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name / company" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="name">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="email">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="tel">
                <select name="urgency" required class="w-full border px-5 py-3.5 rounded-2xl bg-white" aria-label="Attendance window">
                    <option value="">Attendance window…</option>
                    <option value="Live fault — please call me">Live fault — please call me</option>
                    <option value="Within 24 hours">Within 24 hours</option>
                    <option value="Planned follow-up">Planned follow-up</option>
                </select>
            </div>
            <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white" aria-label="Fault type">
                <option value="">Fault type…</option>
                <option value="Emergency 24h lane — fire panel fault">Fire panel / fire alarm fault</option>
                <option value="Emergency 24h lane — power / electrical">Power / 24h electrical make-safe</option>
                <option value="Emergency 24h lane — gas breakdown">Gas / heating breakdown (not a smell-of-gas emergency)</option>
                <option value="Emergency 24h lane — intruder alarm">Intruder alarm fault</option>
                <option value="Emergency 24h lane — CCTV">CCTV failure</option>
                <option value="Emergency 24h lane — access control">Access control failure</option>
                <option value="Emergency 24h lane — door entry">Door entry fault</option>
                <option value="Emergency 24h lane — emergency lighting">Emergency lighting fault</option>
                <option value="Emergency 24h lane — AOV">AOV / smoke control fault</option>
                <option value="Emergency 24h lane — nurse call">Nurse call fault</option>
                <option value="Maintenance contract">Maintenance contract after the fault</option>
            </select>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Postcode, system brand if known, what is happening, occupancy, access notes…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
                Live faults: call or WhatsApp. Attendance is where capacity allows, POA, and not a 24/7 SLA.
            </p>
        </form>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<div id="emergency-lane-sticky" class="mobile-sticky-cta is-hidden" role="region" aria-label="Emergency contact">
    <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>">Call now</a>
    <a class="sticky-quote" style="background:#16a34a" href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">WhatsApp</a>
</div>

<script>
(function () {
    var bar = document.getElementById('emergency-lane-sticky');
    var hero = document.querySelector('[data-job-lane]');
    if (!bar || !hero || !('IntersectionObserver' in window)) {
        if (bar) bar.classList.remove('is-hidden');
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        bar.classList.toggle('is-hidden', entries[0].isIntersecting);
    }, { threshold: 0.2 });
    io.observe(hero);
})();
</script>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
