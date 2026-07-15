<?php
/**
 * Conversion / thank-you page after successful lead form submit.
 * Reached via PRG redirect from contact.php (?ref=contact).
 */
require_once __DIR__ . '/config.php';

$ref = preg_replace('/[^a-z0-9\-_]/', '', strtolower((string)($_GET['ref'] ?? 'contact')));
if ($ref === '') {
    $ref = 'contact';
}

$pageTitle = 'Thank You — Request Received';
$metaDesc = 'Thanks for contacting Icomply Property Services. We will respond shortly. Prefer faster help? Call or WhatsApp us now.';
$metaKeywords = 'Icomply contact, property compliance quote North West';
$metaRobots = 'noindex, follow'; // post-submit page — avoid thin/duplicate indexing
$canonicalUrl = url('/thank-you.php');

$services = getServices();
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$waText = rawurlencode('Hi Icomply, I just submitted a quote request and would like a quicker reply.');
$waUrl = 'https://wa.me/' . WHATSAPP . '?text=' . $waText;

$serviceBlurbs = [
    'electrical' => 'EICR, rewires, EV chargers, PAT & commercial installs',
    'fire-alarms' => 'BS 5839 design, install, service & certification',
    'emergency-lighting' => 'BS 5266 testing, upgrades & LED conversions',
    'aov-air-handling' => 'Smoke vents, AOV panels & AHU controls',
    'nurse-call' => 'Care home & hospital nurse call systems',
    'gas-systems' => 'CP44 landlord certs, boilers & commercial gas',
    'intruder-alarm' => 'Wired & wireless intruder systems',
    'cctv' => 'IP / HD CCTV design, install & monitoring setup',
    'access-control' => 'Paxton, HID, Salto door access',
    'door-entry' => 'Video & audio door entry for flats & sites',
    'intercoms' => 'Multi-tenant & commercial intercom systems',
];

// Highlight a few related services for cross-sell / next steps
$featuredSlugs = array_slice(array_keys($services), 0, 6, true);

require SITE_ROOT . '/includes/header.php';
?>
<!-- HERO CONFIRMATION -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#22c55e,transparent 35%);"></div>
    <div class="relative max-w-3xl mx-auto px-6 py-16 md:py-20 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-emerald-500/20 border border-emerald-400/40 text-3xl mb-6" aria-hidden="true">✓</div>
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Request received</div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
            Thank you — we’ll be in touch.
        </h1>
        <p class="mt-5 text-lg md:text-xl text-white/80 max-w-xl mx-auto">
            A member of the team aims to respond within <strong class="text-white">2 hours</strong> on business days.
            Need something sooner? Call or WhatsApp us now.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank" rel="noopener"
               class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold text-white shadow-lg">
                Message on WhatsApp
            </a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">
                Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a href="<?= url('/contact.php') ?>"
               class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">
                Back to contact
            </a>
        </div>
        <?php if ($ref !== ''): ?>
            <p class="mt-6 text-xs text-white/40">Ref: <?= htmlspecialchars($ref, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- NEXT STEPS -->
<section class="bg-white border-b">
    <div class="max-w-5xl mx-auto px-6 py-12 md:py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">What happens next</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Three simple steps</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php
            $steps = [
                ['1', 'We review your request', 'Service, postcode and details you sent — no spam, just a clear scope.'],
                ['2', 'We contact you', 'Usually by phone or email within 2 hours on business days.'],
                ['3', 'Fixed-price quote', 'Clear price after scope is agreed, then we book engineers.'],
            ];
            foreach ($steps as [$n, $t, $d]): ?>
            <div class="bg-zinc-50 border rounded-3xl p-6 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= $n ?></div>
                <h3 class="mt-4 font-semibold text-lg text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- DIRECT CONTACT CTAs -->
<section class="max-w-5xl mx-auto px-6 py-12 md:py-16">
    <div class="grid md:grid-cols-2 gap-6">
        <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
           target="_blank" rel="noopener"
           class="group block bg-white border rounded-3xl p-8 hover:border-green-500 transition shadow-sm">
            <div class="text-xs uppercase tracking-[3px] text-green-600 font-semibold mb-2">Fastest reply</div>
            <h2 class="text-2xl font-semibold text-black group-hover:text-green-700">Chat on WhatsApp</h2>
            <p class="mt-3 text-zinc-600 text-sm">Send photos, postcodes or panel brands — ideal if you need a same-day steer.</p>
            <div class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 text-white font-semibold text-sm group-hover:bg-green-500">
                Open WhatsApp →
            </div>
        </a>
        <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
           class="group block bg-white border rounded-3xl p-8 hover:border-[#ff6b00] transition shadow-sm">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-2">Speak to us</div>
            <h2 class="text-2xl font-semibold text-black group-hover:text-[#ff6b00]">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mt-3 text-zinc-600 text-sm">Mon–Fri 08:00–18:00. Based in Stockport, covering the North West.</p>
            <div class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-2xl modern-btn text-white font-semibold text-sm">
                Tap to call →
            </div>
        </a>
    </div>
    <div class="mt-6 bg-white border rounded-3xl p-6 md:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="font-semibold text-black">Prefer email?</div>
            <div class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] font-semibold hover:underline break-all">
            <?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>
</section>

<!-- RELATED SERVICES -->
<section class="bg-zinc-50 border-t">
    <div class="max-w-7xl mx-auto px-6 py-12 md:py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">While you wait</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Explore related services</h2>
                <p class="mt-2 text-zinc-600 max-w-xl">Browse what we install, test and certify across Greater Manchester and the North West.</p>
            </div>
            <a href="<?= url('/pages/services/index.php') ?>" class="text-[#ff6b00] font-semibold hover:underline shrink-0">All services →</a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($featuredSlugs as $slug):
                $name = $services[$slug] ?? ucwords(str_replace('-', ' ', $slug));
                $blurb = $serviceBlurbs[$slug] ?? 'Professional install, service and certification.';
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>"
               class="service-card group bg-white border rounded-3xl overflow-hidden hover:border-[#ff6b00]">
                <div class="relative h-36 bg-zinc-100">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                         class="w-full h-full object-cover"
                         loading="lazy"
                         onerror="this.style.display='none'">
                </div>
                <div class="p-5">
                    <h3 class="font-semibold text-lg text-black group-hover:text-[#ff6b00]"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-1.5 text-sm text-zinc-600"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="mt-3 text-sm font-semibold text-[#ff6b00]">View service →</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FINAL CTA STRIP -->
<section class="bg-[#0a2540] text-white">
    <div class="max-w-3xl mx-auto px-6 py-12 text-center">
        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">Questions before we call you back?</h2>
        <p class="mt-3 text-white/75">WhatsApp is usually quickest — or call us direct.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"
               class="px-7 py-3.5 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="px-7 py-3.5 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold">Call now</a>
            <a href="<?= url('/') ?>"
               class="px-7 py-3.5 rounded-2xl border border-white/30 hover:bg-white/10 font-medium">Home</a>
        </div>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
