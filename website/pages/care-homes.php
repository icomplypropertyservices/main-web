<?php
/**
 * Care homes compliance job hub.
 * Nurse call is the lead cross-sell; fire, lighting, access and CCTV sit beside it.
 * Quotes are POA after survey. This page does not publish prices and does not claim a CQC inspection.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Care Home Compliance | Nurse Call First | North West';
$metaDesc = 'Care home compliance hub for Stockport and the North West. Start with nurse call — install, repair and planned maintenance — then fire risk assessment, fire alarms, emergency lighting and CCTV. Quoted after survey. Not a CQC inspection.';
$metaKeywords = 'care home nurse call, nurse call maintenance, care home fire alarm, care home fire risk assessment, emergency lighting care home, CQC care home compliance Stockport Manchester';
$ogImage = url('/assets/images/services/nurse-call.jpg');
$canonicalUrl = url('/pages/care-homes.php');

$services = getServices();
$areas = getAreas();
$mfrCatalog = getManufacturerCatalog();
$mfrData = loadJsonData('manufacturers', []);

// Core care-home service pillars (priority order)
$careServices = [
    'nurse-call' => [
        'title' => 'Nurse call',
        'blurb' => 'Design, install, upgrade and planned maintenance for care home nurse call — wired, wireless and hybrid systems with HTM 08-03 guidance and full certification.',
        'badge' => 'HTM 08-03',
        'keywords' => ['Call points', 'Pear leads', 'Staff indicators'],
    ],
    'fire-alarms' => [
        'title' => 'Fire alarms',
        'blurb' => 'BS 5839 design, install, servicing and certification for residential care, nursing homes and supported living — addressable, conventional and wireless.',
        'badge' => 'BS 5839',
        'keywords' => ['Periodic service', 'False-alarm reduction', 'Certification'],
    ],
    'emergency-lighting' => [
        'title' => 'Emergency lighting',
        'blurb' => 'BS 5266 function and duration tests, LED upgrades, logbooks and monthly/annual certification for corridors, stairs and escape routes.',
        'badge' => 'BS 5266',
        'keywords' => ['Duration tests', 'LED upgrades', 'Logbooks'],
    ],
    'access-control' => [
        'title' => 'Access control',
        'blurb' => 'Paxton, HID and Salto door access with fire-override integration — secure staff/visitor control while keeping escape routes compliant.',
        'badge' => 'Secure access',
        'keywords' => ['Paxton / HID / Salto', 'Fire override', 'Staff credentials'],
    ],
    'cctv' => [
        'title' => 'CCTV',
        'blurb' => 'IP / HD CCTV for entrances, car parks, plant rooms and common areas — discreet recording, remote viewing and NVR estates for care operators.',
        'badge' => 'IP / HD',
        'keywords' => ['Entrances', 'Remote viewing', 'NVR estates'],
    ],
];

$extraCareServices = [
    'door-entry' => 'Video & audio door entry for main receptions and annexes',
    'intercoms' => 'Staff and multi-area intercom systems',
    'intruder-alarm' => 'Wired & wireless intruder systems with monitoring options',
    'electrical' => 'EICR programmes, consumer units and electrical remedials',
    'gas-systems' => 'Gas safety checks and commercial / landlord certs',
    'aov-air-handling' => 'Smoke vents, AOV panels & air-handling controls',
];

$packages = [
    [
        'name' => 'Single-home package',
        'text' => 'Nurse call service, fire alarm certification, emergency lighting tests and a security review for one care home — fixed price after survey.',
        'points' => ['Site survey included', 'Digital certificates', 'Remedial advice'],
    ],
    [
        'name' => 'Multi-home estate',
        'text' => 'Bundle several homes into one visit schedule — ideal for groups and FM partners managing 2–20+ care sites across the North West.',
        'points' => ['Shared visit days', 'Quoted after survey', 'One point of contact'],
    ],
    [
        'name' => 'Annual compliance plan',
        'text' => 'Year-round cover for nurse call first, then fire, emergency lighting, access and CCTV as due dates fall — paperwork for the responsible person, CQC file and insurers.',
        'points' => ['Nurse call on the calendar', 'Renewal reminders', 'HTM / BS documentation'],
    ],
];

$trust = [
    ['title' => 'Care-home ready', 'text' => 'Nurse call, fire, lighting, access & CCTV under one partner'],
    ['title' => 'HTM & BS standards', 'text' => 'HTM 08-03, BS 5839, BS 5266 and fire-override access'],
    ['title' => 'Manufacturer brands', 'text' => 'Courtney Thorne, Static Systems, Intercall & more'],
    ['title' => 'Fixed-price quotes', 'text' => 'Clear scope, certification and multi-home options'],
];

// Nurse-call manufacturers from featured list + catalog (Courtney Thorne first)
$nurseCallSlugs = $mfrData['featured_by_service']['nurse-call']
    ?? $mfrData['images_by_service']['nurse-call']
    ?? ['courtney-thorne', 'static-systems-group', 'intercall', 'aid-call', 'ascom'];

// Prefer preferred order if present in catalog
$preferredNurseOrder = [
    'courtney-thorne',
    'static-systems-group',
    'intercall',
    'aid-call',
    'ascom',
    'caretech',
    'quantec',
    'wandsworth',
    'zettler',
];
$nurseMfr = [];
foreach (array_unique(array_merge($preferredNurseOrder, (array)$nurseCallSlugs)) as $slug) {
    if (isset($mfrCatalog[$slug])) {
        $nurseMfr[$slug] = $mfrCatalog[$slug];
    }
}
// Also pull any catalog entry tagged nurse-call not already listed
foreach ($mfrCatalog as $slug => $entry) {
    if (isset($nurseMfr[$slug])) {
        continue;
    }
    $svc = $entry['services'] ?? [];
    if (in_array('nurse-call', $svc, true)) {
        $nurseMfr[$slug] = $entry;
    }
}
$nurseMfr = array_slice($nurseMfr, 0, 12, true);

// Supporting fire / access / CCTV brands for care homes
$supportMfrSlugs = [
    'c-tec', 'kentec', 'advanced-electronics', 'hochiki', 'apollo',
    'paxton', 'salto-systems', 'hid-global',
    'hikvision', 'axis-communications', 'dahua',
];
$supportMfr = [];
foreach ($supportMfrSlugs as $slug) {
    if (isset($mfrCatalog[$slug])) {
        $supportMfr[$slug] = $mfrCatalog[$slug];
    }
}

$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
    function ($t) use ($areas) {
        return in_array($t, $areas, true);
    }
));

$keywords = getMajorKeywords();

// Nurse call is the lead offer on this hub. Other compliance jobs stay listed below.
$nurseCallCrossSell = [
    'care-home-nurse-call' => 'Design, install, upgrade and planned maintenance for the home you run today.',
    'care-home-call-system' => 'Bedroom points, pear leads, overdoor lights and the staff display.',
    'nursing-home-nurse-call' => 'Higher-dependency layouts, including rooms staff treat as clinical.',
    'residential-care-nurse-call' => 'Occupied residential homes — phased works, not a weekend rip-out.',
    'nurse-call-system-installation' => 'A new system or a wing extension, commissioned and handed over.',
    'nurse-call-upgrade' => 'Replace an ageing panel after we see what cabling can stay.',
    'nurse-call-maintenance' => 'Planned tests, batteries and a written report for the file.',
    'nurse-call-service-contract' => 'A visit calendar for one home or a small group.',
    'nurse-call-repair' => 'Panel faults, dead zones and failed call points.',
    'nurse-call-pear-lead-replacement' => 'The part residents handle most — fitted and call-tested.',
    'multi-site-nurse-call-maintenance' => 'The same test method and report across several homes.',
    'nursing-home-call-system' => 'Nursing-home call survey, upgrade path and service records.',
];
$nurseCallJobs = [];
foreach ($nurseCallCrossSell as $slug => $blurb) {
    if (!isset($keywords[$slug])) {
        continue;
    }
    $nurseCallJobs[$slug] = [
        'name' => (string)($keywords[$slug]['name'] ?? keywordDisplayName($slug)),
        'blurb' => $blurb,
        'href' => url('/pages/keywords/' . $slug . '.php'),
    ];
}

$complianceJobCopy = [
    'care-home-fire-risk-assessment' => 'Written around residents, night staffing and how this home actually moves people.',
    'care-home-fire-alarm' => 'BS 5839 design, service and certificates. Category follows the fire risk assessment.',
    'care-home-emergency-lighting' => 'BS 5266 function and duration tests on escape and staff routes.',
    'care-home-evacuation-alerts' => 'Only where the fire strategy needs an evacuation alert system.',
    'cctv-for-care-homes' => 'Entrances, car parks and plant rooms — a stated purpose, not covert monitoring.',
];
$complianceJobs = [];
foreach ($complianceJobCopy as $slug => $blurb) {
    if (!isset($keywords[$slug])) {
        continue;
    }
    $complianceJobs[$slug] = [
        'name' => (string)($keywords[$slug]['name'] ?? keywordDisplayName($slug)),
        'blurb' => $blurb,
        'href' => url('/pages/keywords/' . $slug . '.php'),
    ];
}

$nurseCallFocus = (string)($_GET['focus'] ?? '') === 'nurse-call';

$careFaqs = [
    [
        'q' => 'Should we book nurse call with the rest of the care-home survey?',
        'a' => 'Yes. If the home can only start with one system, start with nurse call. Fire alarms, emergency lighting and CCTV can be walked on the same visit, and the quote still separates each system. We are not a CQC inspector.',
    ],
    [
        'q' => 'Do you publish a nurse call price?',
        'a' => 'No. Nurse call, fire and the other care-home jobs on this page are quoted after a survey. Bed count, brand and whether the home is occupied change the visit. Nothing on this page is a fixed package price.',
    ],
    [
        'q' => 'Are you a CQC inspector?',
        'a' => 'No. We install, maintain and document nurse call, fire and related systems. A service visit is not a regulatory inspection and we do not issue a CQC rating.',
    ],
    [
        'q' => 'Can you work in an occupied home?',
        'a' => 'Yes. Nurse call upgrades are phased so a wing is not left without a way to call staff. Tell us occupancy, the panel brand and any rooms that must stay covered.',
    ],
    [
        'q' => 'Which nurse call brands do you service?',
        'a' => 'Courtney Thorne is the usual starting point, alongside other nurse call brands listed on this page when we hold a manufacturer page for them. Send a photo of the panel if the badge is worn.',
    ],
];

$careSchema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => 'Care home nurse call',
            'serviceType' => 'Nurse call installation, repair and planned maintenance',
            'description' => $metaDesc,
            'url' => url('/pages/care-homes.php'),
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'telephone' => PHONE,
                'email' => EMAIL,
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
            'mainEntity' => array_map(static function (array $item): array {
                return [
                    '@type' => 'Question',
                    'name' => $item['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['a'],
                    ],
                ];
            }, $careFaqs),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Care homes', 'item' => url('/pages/care-homes.php')],
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
<script type="application/ld+json"><?= json_encode($careSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>


<!-- HERO -->
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-16 md:py-24 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <nav class="text-xs text-white/50 mb-5 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
                <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
                <span aria-hidden="true">/</span>
                <span class="text-white/80">Care homes</span>
            </nav>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Care homes · Stockport &amp; North West
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Care home systems.<br>
                <span class="text-[#ff6b00]">Nurse call, fire &amp; security.</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                Nurse call, fire alarms, emergency lighting, access control and CCTV for residential care,
                nursing homes and supported living — install, service and certification with CQC-ready paperwork.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="?focus=nurse-call#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Book a nurse call survey</a>
                <a href="<?= url('/pages/services/nurse-call.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">Nurse call service</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Full care-home quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20iComply%2C%20I%20need%20a%20care%20home%20nurse%20call%20quote"
                   target="_blank" rel="noopener"
                   class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp nurse call</a>
            </div>
            <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                <div><span class="text-white font-semibold text-xl block">Nurse call</span> HTM 08-03</div>
                <div><span class="text-white font-semibold text-xl block">Fire &amp; EL</span> BS 5839 / 5266</div>
                <div><span class="text-white font-semibold text-xl block">Access &amp; CCTV</span> Secure estates</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <?php
            $heroSlugs = ['nurse-call', 'fire-alarms', 'emergency-lighting', 'cctv'];
            foreach ($heroSlugs as $slug):
                $card = $careServices[$slug] ?? null;
                $name = $card['title'] ?? ($services[$slug] ?? ucwords(str_replace('-', ' ', $slug)));
                $badge = $card['badge'] ?? '';
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[140px] bg-white/5 hover:border-[#ff6b00] transition">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> for care homes — iComply Property Services" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition" loading="lazy"
                     onerror="this.style.display='none'">
                <div class="relative p-5 h-full flex flex-col justify-end">
                    <?php if ($badge !== ''): ?>
                        <div class="text-[10px] uppercase tracking-wider text-[#ff6b00] font-semibold mb-1"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <div class="font-semibold text-white text-lg leading-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-xs text-white/70 mt-1">View service →</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- NURSE CALL CROSS-SELL — lead offer on this hub -->
<section id="nurse-call" data-cross-sell="nurse-call" class="bg-[#ff6b00] text-[#0B1F3A]">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-16">
        <div class="grid lg:grid-cols-12 gap-10 items-start">
            <div class="lg:col-span-5">
                <div class="text-xs uppercase tracking-[3px] font-semibold">Nurse call · book this first</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2 leading-tight">The care-home job we want on the survey.</h2>
                <p class="mt-4 text-[#0B1F3A]/90 leading-relaxed">
                    Fire, lighting and cameras matter. They do not replace a call point that staff can hear at night.
                    If you are comparing systems for this home, start with nurse call — install, repair, pear leads or a maintenance visit —
                    and add the other compliance jobs to the same walk-round.
                </p>
                <p class="mt-3 text-sm text-[#0B1F3A]/80">Quoted after survey. We do not publish a nurse-call price, and we are not CQC.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="?focus=nurse-call#quote" class="px-6 py-3 rounded-2xl bg-[#0B1F3A] text-white text-sm font-semibold hover:bg-black transition">Nurse call quote</a>
                    <a href="<?= url('/pages/services/nurse-call.php') ?>" class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] text-sm font-semibold hover:bg-zinc-100 transition">Open the nurse call service</a>
                </div>
            </div>
            <div class="lg:col-span-7 grid sm:grid-cols-2 gap-3">
                <?php foreach ($nurseCallJobs as $job): ?>
                <a href="<?= htmlspecialchars($job['href'], ENT_QUOTES, 'UTF-8') ?>"
                   class="block bg-white/95 rounded-2xl p-4 hover:bg-white shadow-sm">
                    <div class="font-semibold text-[#0B1F3A]"><?= htmlspecialchars($job['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <p class="text-sm text-zinc-700 mt-1"><?= htmlspecialchars($job['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-2 inline-block text-sm font-semibold text-[#0B1F3A]">View job →</span>
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
                <div class="w-10 h-10 rounded-2xl bg-[#0B1F3A]/10 flex items-center justify-center text-[#0B1F3A] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($t['text'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CARE HOME SERVICES -->
<section id="services" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Care home systems</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Nurse call · fire · lighting · access · CCTV</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">
                The systems care providers and estates teams need covered — open a service hub for local coverage,
                manufacturers and a quote form. Start with our
                <a href="<?= url('/pages/services/nurse-call.php') ?>" class="text-[#ff6b00] font-semibold hover:underline">nurse call service</a>.
            </p>
        </div>
        <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($careServices as $slug => $card):
            $img = url('/assets/images/services/' . $slug . '.jpg');
            $svcName = $services[$slug] ?? $card['title'];
        ?>
        <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
           class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-36 bg-zinc-100 overflow-hidden">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8') ?> for care homes"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy"
                     onerror="this.parentElement.style.display='none'">
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <div class="text-[10px] uppercase tracking-wider text-[#ff6b00] font-semibold"><?= htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="font-semibold text-lg text-black mt-1"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($card['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php if (!empty($card['keywords'])): ?>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <?php foreach ($card['keywords'] as $kw): ?>
                        <span class="text-[11px] px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-600"><?= htmlspecialchars($kw, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <span class="mt-4 text-sm font-semibold text-[#ff6b00]">Explore →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="mt-10">
        <h3 class="font-semibold text-black mb-3">Also available for care estates</h3>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($extraCareServices as $slug => $blurb):
                if (!isset($services[$slug])) {
                    continue;
                }
            ?>
                <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00] transition"
                   title="<?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($services[$slug], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- OTHER COMPLIANCE JOBS -->
<?php if ($complianceJobs): ?>
<section id="compliance-jobs" class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="max-w-2xl">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Compliance jobs</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">The rest of the care-home file</h2>
            <p class="mt-3 text-zinc-600">These sit next to nurse call. Each one is its own job page and its own quote line — not a bundled price.</p>
        </div>
        <div class="mt-8 grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($complianceJobs as $job): ?>
            <a href="<?= htmlspecialchars($job['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="block border border-zinc-200 rounded-3xl p-6 hover:border-[#ff6b00] transition">
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars($job['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($job['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]">Open job →</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- NURSE CALL HIGHLIGHT -->
<section class="bg-zinc-100 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nurse call systems</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Responsive call systems for care homes</h2>
                <p class="mt-4 text-zinc-600 leading-relaxed">
                    From bedside call points and pear leads to overdoor lights, staff indicators and control panels —
                    we design, install, expand and service nurse call systems aligned with HTM 08-03 guidance.
                    Planned maintenance, battery changes and certification keep systems ready for CQC and insurer scrutiny.
                </p>
                <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> New installs, upgrades and system expansions</li>
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Wired, wireless and hybrid nurse call platforms</li>
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Call points, pull cords, pear leads &amp; panel batteries</li>
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Planned service with certification-ready records</li>
                </ul>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="<?= url('/pages/services/nurse-call.php') ?>" class="px-6 py-3 rounded-2xl bg-[#0B1F3A] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Nurse call service →</a>
                    <a href="<?= url('/pages/keywords/care-home-nurse-call.php') ?>" class="px-6 py-3 rounded-2xl border border-zinc-300 text-sm font-semibold hover:border-[#ff6b00] transition">Care home nurse call guide</a>
                </div>
            </div>
            <div class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">
                <h3 class="text-2xl font-semibold tracking-tight">Ideal for</h3>
                <ul class="mt-6 space-y-4 text-sm text-white/90">
                    <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Residential care &amp; nursing homes</li>
                    <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Supported living &amp; extra-care schemes</li>
                    <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Care groups managing multi-home estates</li>
                    <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> FM partners &amp; estates managers</li>
                    <li class="flex gap-3"><span class="text-[#ff6b00]">●</span> Clinics and healthcare annexes on care sites</li>
                </ul>
                <a href="#quote" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold">Request care home quote</a>
            </div>
        </div>
    </div>
</section>

<!-- PACKAGES -->
<section id="packages" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Packages</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Care home compliance packages</h2>
            <p class="mt-2 text-zinc-600 max-w-xl">Combine nurse call, fire alarms, emergency lighting, access and CCTV into one schedule for single homes or multi-site groups.</p>
        </div>
        <a href="#quote" class="inline-flex px-5 py-2.5 rounded-full bg-[#0B1F3A] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Request package quote</a>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
        <?php foreach ($packages as $pkg): ?>
        <div class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 hover:border-[#ff6b00] transition">
            <h3 class="text-xl font-semibold text-black tracking-tight"><?= htmlspecialchars($pkg['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mt-3 text-sm text-zinc-600"><?= htmlspecialchars($pkg['text'], ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="mt-6 space-y-2 text-sm text-zinc-800">
                <?php foreach ($pkg['points'] as $pt): ?>
                    <li class="flex gap-2"><span class="text-[#ff6b00] shrink-0">●</span> <?= htmlspecialchars($pt, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="mt-10 bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10 grid lg:grid-cols-2 gap-8 items-center">
        <div>
            <h3 class="text-2xl font-semibold tracking-tight">Built for care operators &amp; FM partners</h3>
            <p class="mt-3 text-white/80">Share site list, panel brands and certificate due dates — we’ll map nurse call, fire, emergency lighting, access and CCTV into a single compliance programme.</p>
        </div>
        <ul class="space-y-3 text-sm text-white/90">
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Certificates and service reports ready for audits</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Nurse call faults booked with the home manager</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Same engineers across Stockport and the North West</li>
            <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Trade spares for call points, batteries and detectors</li>
        </ul>
    </div>
</section>

<!-- MANUFACTURERS — NURSE CALL -->
<?php if ($nurseMfr): ?>
<section id="manufacturers" class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nurse call brands</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Manufacturers we install &amp; service</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">
                    Brand pages with install quotes, planned service and trade kits —
                    including <a href="<?= url('/pages/manufacturers/courtney-thorne.php') ?>" class="text-[#ff6b00] font-semibold hover:underline">Courtney Thorne</a>
                    and other care-home nurse call platforms.
                </p>
            </div>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All manufacturers →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($nurseMfr as $mSlug => $mEntry): ?>
                <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00] transition<?= $mSlug === 'courtney-thorne' ? ' border-[#ff6b00] text-[#ff6b00]' : '' ?>">
                    <?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($supportMfr): ?>
        <div class="mt-10">
            <h3 class="font-semibold text-black mb-3">Fire, access &amp; CCTV brands for care homes</h3>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($supportMfr as $mSlug => $mEntry): ?>
                    <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>"
                       class="px-4 py-2 bg-zinc-50 border rounded-full text-sm hover:border-[#ff6b00] transition">
                        <?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- WHY CARE HOMES -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-start">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Why iComply</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Stockport engineers, North West care coverage</h2>
                <p class="mt-4 text-zinc-600 leading-relaxed">
                    Based in Offerton, Stockport, iComply Property Services supports care homes, nursing homes and
                    supported living across Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria.
                    We focus on clear scope, fixed-price quotes and documentation that stands up to CQC visits,
                    fire risk assessments and insurer audits.
                </p>
                <div class="mt-8 grid sm:grid-cols-2 gap-4">
                    <?php
                    $why = [
                        ['Nurse call', 'Install, upgrade and HTM-aligned planned maintenance'],
                        ['Fire alarms', 'BS 5839 design, service and certification'],
                        ['Emergency lighting', 'BS 5266 function & duration tests with logbooks'],
                        ['Access & CCTV', 'Secure doors, cameras and remote viewing'],
                    ];
                    foreach ($why as [$t, $d]): ?>
                    <div class="bg-white border rounded-2xl p-5">
                        <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                        <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Coverage</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Popular areas</h2>
                <p class="mt-3 text-zinc-600">Pick a town for local service links — or request a multi-home quote covering your full care map.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <?php foreach ($popularTowns as $town): ?>
                        <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                           class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]"><?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
                <a href="<?= url('/pages/areas/index.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">View all <?= count($areas) ?>+ areas →</a>

                <div class="mt-10 bg-white border rounded-3xl p-6">
                    <h3 class="font-semibold text-black">Nurse call, then the rest</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/services/nurse-call.php') ?>">Nurse call service hub</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/care-home-nurse-call.php') ?>">Care home nurse call</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/nurse-call-maintenance.php') ?>">Nurse call maintenance</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/nurse-call-repair.php') ?>">Nurse call repair</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/manufacturers/courtney-thorne.php') ?>">Courtney Thorne nurse call</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/resources/care-home-fire-and-nurse-call.php') ?>">Care home fire and nurse call guide</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/services/fire-alarms.php') ?>">Fire alarm installation &amp; servicing</a></li>
                        <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/services/emergency-lighting.php') ?>">Emergency lighting</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How care home projects work</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            $steps = [
                ['1', 'Tell us about the home', 'Site type, bed count, existing nurse call / fire brands, cert due dates and access notes — form, phone or WhatsApp.'],
                ['2', 'Fixed-price quote', 'We confirm scope (nurse call, fire, emergency lighting, access, CCTV) and a clear install or maintenance price.'],
                ['3', 'Attend, certify, support', 'Engineers complete the work, issue certificates and keep planned maintenance on schedule.'],
            ];
            foreach ($steps as [$n, $t, $d]): ?>
            <div class="text-center px-4">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0B1F3A] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
                <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section id="care-home-faq" class="bg-white border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Questions</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Nurse call and the care-home file</h2>
        <div class="mt-8 space-y-4">
            <?php foreach ($careFaqs as $item): ?>
            <details class="border border-zinc-200 rounded-2xl p-5 bg-zinc-50">
                <summary class="font-semibold text-black cursor-pointer"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                <p class="mt-3 text-sm text-zinc-700 leading-relaxed"><?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div data-cross-sell="nurse-call-quote" class="mb-8 rounded-3xl bg-[#0B1F3A] text-white p-6 md:p-8">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nurse call on this quote</div>
            <h2 class="text-2xl font-semibold tracking-tight mt-2">Add nurse call even if you came here for fire or CCTV.</h2>
            <p class="mt-3 text-white/80 text-sm leading-relaxed">Name the call-system brand, how many beds, and whether you need install, repair or maintenance. We price it after the survey, on its own line.</p>
            <a href="<?= url('/pages/services/nurse-call.php') ?>" class="inline-block mt-4 text-sm font-semibold text-[#ff6b00] hover:underline">Nurse call service hub →</a>
        </div>
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2"><?= $nurseCallFocus ? 'Nurse call quote for this care home' : 'Care home quote request' ?></h2>
            <p class="mt-3 text-zinc-600"><?= $nurseCallFocus
                ? 'Panel brand, bed count, and install, repair or maintenance. Fire and lighting can share the visit. The figure comes after we see the home.'
                : 'Tell us about the home, the nurse call brand and any other systems. We aim to respond within 2 hours on business days.' ?></p>
        </div>

        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name / care home / group" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <option value="Multi-service package"<?= $nurseCallFocus ? '' : ' selected' ?>>Care home / multi-system package</option>
                    <option value="Nurse Call"<?= $nurseCallFocus ? ' selected' : '' ?>>Nurse call</option>
                    <option value="Fire Alarms">Fire alarms</option>
                    <option value="Emergency Lighting">Emergency lighting</option>
                    <option value="Access Control">Access control</option>
                    <option value="CCTV">CCTV</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <?php if (in_array($slug, ['nurse-call', 'fire-alarms', 'emergency-lighting', 'access-control', 'cctv'], true)) {
                            continue;
                        } ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Home name / postcode, bed count, systems needed (nurse call, fire, emergency lighting, access, CCTV), panel brands (e.g. Courtney Thorne), install or service…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit care home quote</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', PHONE), ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0B1F3A] hover:text-[#ff6b00]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0B1F3A] hover:text-[#ff6b00]"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="font-semibold text-green-700 hover:text-green-600">WhatsApp</a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
