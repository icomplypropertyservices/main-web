<?php
/**
 * Landlords & letting agents landing page —
 * EICR, gas CP12/CP44, fire alarms, emergency lighting, multi-property packages.
 */
require_once __DIR__ . '/../config.php';

$pageTitle = 'Landlord & Letting Agent Compliance | North West';
$metaDesc = 'Landlord compliance packages for Stockport & the North West — EICR, gas CP12/CP44, fire alarms, emergency lighting and multi-property schedules. Free fixed-price quotes for landlords and letting agents.';
$metaKeywords = 'landlord compliance North West, EICR landlords Stockport, CP12 gas safety certificate, CP44 landlord gas, fire alarm landlords, emergency lighting rental, multi-property compliance package, letting agent certificates Manchester';
$ogImage = url('/assets/images/services/electrical.jpg');
$canonicalUrl = url('/pages/landlords.php');

$services = getServices();
$areas = getAreas();

// Core landlord-focused services (priority order)
$landlordServices = [
    'electrical' => [
        'title' => 'EICR & electrical',
        'blurb' => 'Landlord electrical installation condition reports (EICR), remedial works, consumer unit upgrades and PAT for HMOs and portfolios.',
        'badge' => 'Every 5 years*',
    ],
    'gas-systems' => [
        'title' => 'Gas CP12 / CP44',
        'blurb' => 'Annual landlord gas safety certificates (CP12/CP44), appliance checks, boilers and full Gas Safe documentation for tenancies.',
        'badge' => 'Annual',
    ],
    'fire-alarms' => [
        'title' => 'Fire alarms',
        'blurb' => 'BS 5839 design, install, servicing and certification for HMOs, flats and rental stock — including Grade D/LD2/LD3 schemes.',
        'badge' => 'BS 5839',
    ],
    'emergency-lighting' => [
        'title' => 'Emergency lighting',
        'blurb' => 'BS 5266 testing, logbooks, LED upgrades and duration tests for common parts, HMOs and multi-occupancy buildings.',
        'badge' => 'BS 5266',
    ],
];

$extraLandlordServices = [
    'cctv' => 'IP / HD CCTV for communal areas and plant rooms',
    'access-control' => 'Paxton, HID & Salto door access for blocks',
    'door-entry' => 'Video & audio entry for flats and HMOs',
    'intercoms' => 'Multi-tenant intercom systems',
    'intruder-alarm' => 'Wired & wireless intruder systems',
    'aov-air-handling' => 'Smoke vents, AOV panels & AHU controls',
];

$packages = [
    [
        'name' => 'Single property',
        'text' => 'One-off EICR, gas CP12/CP44, fire alarm service or emergency lighting test with certificates ready for your tenancy file.',
        'points' => ['Fixed-price quote', 'Digital certificates', 'Remedial advice included'],
    ],
    [
        'name' => 'Portfolio package',
        'text' => 'Bundle multiple properties into one schedule — ideal for landlords and letting agents managing 5–50+ units.',
        'points' => ['Shared visit days', 'Portfolio discount', 'One point of contact'],
    ],
    [
        'name' => 'Annual compliance plan',
        'text' => 'Year-round cover for gas safety, fire alarm servicing, emergency lighting tests and EICR renewals as they fall due.',
        'points' => ['Renewal reminders', 'Priority booking', 'Audit-ready paperwork'],
    ],
];

$trust = [
    ['title' => 'Stockport-based', 'text' => 'Local engineers covering Greater Manchester & the North West'],
    ['title' => 'Letting-agent ready', 'text' => 'Certificates and reports formatted for tenancy files & portals'],
    ['title' => 'Multi-property quotes', 'text' => 'Fixed prices across portfolios — no per-visit surprises'],
    ['title' => 'Standards-led', 'text' => 'BS 7671, BS 5839, BS 5266, Gas Safe & more'],
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

require_once SITE_ROOT . '/includes/share.php';
require SITE_ROOT . '/includes/header.php';
$homeUrl = rtrim(SITE_URL, '/') . '/';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-16 md:py-24 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <nav class="text-xs text-white/50 mb-5 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
                <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
                <span aria-hidden="true">/</span>
                <span class="text-white/80">Landlords &amp; letting agents</span>
            </nav>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Stockport · Greater Manchester &amp; North West
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Landlord compliance.<br>
                <span class="text-[#ff6b00]">Certificates on time, every time.</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-xl">
                EICR, gas CP12/CP44, fire alarms and emergency lighting for private landlords and letting agents —
                plus multi-property packages with one schedule and full documentation.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get portfolio quote</a>
                <a href="#packages" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">View packages</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20need%20a%20landlord%20compliance%20quote"
                   target="_blank" rel="noopener"
                   class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
            <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                <div><span class="text-white font-semibold text-xl block">EICR</span> BS 7671 reports</div>
                <div><span class="text-white font-semibold text-xl block">CP12 / CP44</span> Gas Safe certs</div>
                <div><span class="text-white font-semibold text-xl block">Multi-unit</span> portfolio plans</div>
            </div>
            <p class="mt-3 text-[11px] text-white/40">*EICR frequency depends on property type and previous report recommendations.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <?php foreach ($landlordServices as $slug => $card):
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="group relative rounded-3xl overflow-hidden border border-white/10 min-h-[140px] bg-white/5 hover:border-[#ff6b00] transition">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-55 transition" loading="lazy"
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

<!-- LANDLORD SERVICES -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Statutory compliance</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What every landlord needs covered</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">From private landlords with a single buy-to-let to letting agents running full portfolios — we install, test and certify to the standards tenants, councils and insurers expect.</p>
        </div>
        <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($landlordServices as $slug => $card):
            $img = url('/assets/images/services/' . $slug . '.jpg');
            $svcName = $services[$slug] ?? $card['title'];
        ?>
        <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
           class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
            <div class="h-36 bg-zinc-100 overflow-hidden">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy"
                     onerror="this.parentElement.style.display='none'">
            </div>
            <div class="p-5 flex-1 flex flex-col">
                <div class="text-[10px] uppercase tracking-wider text-[#ff6b00] font-semibold"><?= htmlspecialchars($card['badge'], ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="font-semibold text-lg text-black mt-1"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($card['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-4 text-sm font-semibold text-[#ff6b00]">Explore →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="mt-10">
        <h3 class="font-semibold text-black mb-3">Also available for rental &amp; block stock</h3>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($extraLandlordServices as $slug => $blurb):
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

<!-- MULTI-PROPERTY PACKAGES -->
<section id="packages" class="bg-zinc-100 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Packages</div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Multi-property packages</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Combine EICR, gas safety, fire alarms and emergency lighting into one visit schedule for landlords and letting agents.</p>
            </div>
            <a href="#quote" class="inline-flex px-5 py-2.5 rounded-full bg-[#0a2540] text-white text-sm font-semibold hover:bg-[#ff6b00] transition">Request package quote</a>
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
        <div class="mt-10 bg-[#0a2540] text-white rounded-3xl p-8 md:p-10 grid lg:grid-cols-2 gap-8 items-center">
            <div>
                <h3 class="text-2xl font-semibold tracking-tight">Built for letting agents &amp; portfolio landlords</h3>
                <p class="mt-3 text-white/80">Send a property list or spreadsheet — we’ll map due dates for EICR, CP12/CP44, fire alarm service and emergency lighting, then quote a single multi-property package.</p>
            </div>
            <ul class="space-y-3 text-sm text-white/90">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Certificates emailed and ready for your agent portal</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Reminder support before gas and electrical renewals</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Same engineers across Stockport and the North West</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Remedial works quoted clearly after inspection</li>
            </ul>
        </div>
    </div>
</section>

<!-- WHY LANDLORDS -->
<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Why Icomply</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Stockport engineers, North West coverage</h2>
            <p class="mt-4 text-zinc-600 leading-relaxed">
                Based in Offerton, Stockport, Icomply Property Services supports landlords and letting agents across
                Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria. We focus on clear scope, fixed-price
                quotes and paperwork that stands up to tenancy deposits, local authority checks and insurer audits.
            </p>
            <div class="mt-8 grid sm:grid-cols-2 gap-4">
                <?php
                $why = [
                    ['EICR', 'Condition reports and remedial electrical works for rental stock'],
                    ['Gas CP12 / CP44', 'Annual landlord gas safety with Gas Safe engineers'],
                    ['Fire alarms', 'Install, service and certify to BS 5839'],
                    ['Emergency lighting', 'Function & duration tests with logbooks'],
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
            <p class="mt-3 text-zinc-600">Pick a town for local service links — or request a multi-area portfolio quote covering your full rental map.</p>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php foreach ($popularTowns as $town): ?>
                    <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                       class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]"><?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="inline-block mt-6 text-sm font-semibold text-[#ff6b00]">View all <?= count($areas) ?>+ areas →</a>

            <div class="mt-10 bg-zinc-50 border rounded-3xl p-6">
                <h3 class="font-semibold text-black">Related landlord guides</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/landlord-electrical-certificate.php') ?>">Landlord electrical certificate (EICR)</a></li>
                    <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/landlord-gas-safety-certificate.php') ?>">Landlord gas safety certificate</a></li>
                    <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/landlord-fire-alarm.php') ?>">Landlord fire alarms</a></li>
                    <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/landlord-emergency-lighting.php') ?>">Landlord emergency lighting</a></li>
                    <li><a class="text-[#ff6b00] font-medium hover:underline" href="<?= url('/pages/keywords/landlord-safety-certificate.php') ?>">Landlord safety certificates</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How landlord bookings work</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php
            $steps = [
                ['1', 'Send property details', 'Addresses, tenancy dates, certificate types due, and access notes — form, phone or WhatsApp.'],
                ['2', 'Fixed multi-unit quote', 'We confirm scope (EICR, CP12/CP44, fire, emergency lighting) and a clear price per property or package.'],
                ['3', 'Attend, certify, file', 'Engineers complete the work and issue certificates you can pass straight to tenants and agents.'],
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

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Landlord &amp; agent quote request</h2>
            <p class="mt-3 text-zinc-600">Tell us how many properties and which certificates you need. We aim to respond within 2 hours on business days.</p>
        </div>

        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name / agency" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <option value="Multi-service package" selected>Multi-property / compliance package</option>
                    <option value="Electrical">EICR / Electrical</option>
                    <option value="Gas Systems">Gas CP12 / CP44</option>
                    <option value="Fire Alarms">Fire alarms</option>
                    <option value="Emergency Lighting">Emergency lighting</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <?php if (in_array($slug, ['electrical', 'gas-systems', 'fire-alarms', 'emergency-lighting'], true)) {
                            continue;
                        } ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Number of properties, postcodes, certificates due (EICR / CP12 / fire / emergency lighting), access notes…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit landlord quote</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>

        <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', PHONE), ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0a2540] hover:text-[#ff6b00]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-[#0a2540] hover:text-[#ff6b00]"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
            <span class="text-zinc-300">|</span>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="font-semibold text-green-700 hover:text-green-600">WhatsApp</a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
