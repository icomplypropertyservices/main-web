<?php
/**
 * Resource article — fire alarm servicing (high-level UK guidance, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Fire Alarm Servicing Explained | BS 5839 Guidance';
$metaDesc = 'High-level UK guide to fire alarm servicing: user tests, periodic maintenance, logbooks and BS 5839 best practice for commercial, multi-let and care premises in the North West.';
$metaKeywords = 'fire alarm servicing, fire alarm maintenance, BS 5839, fire alarm testing, commercial fire alarm service, fire alarm logbook North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/resources/fire-alarm-servicing.php');

$services = getServices();

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
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/resources/index.php') ?>" class="hover:text-white">Resources</a>
            <span>/</span>
            <span class="text-white/80">Fire alarm servicing</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Fire alarms · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Fire alarm servicing<br>
                <span class="text-[#ff6b00]">explained</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                A practical overview of user checks, periodic servicing and documentation for life-safety fire detection systems.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> Fire safety duties depend on premises type, occupancy and your fire risk assessment.
        This guide is general information only and does not replace BS 5839, manufacturer instructions or competent person advice.
    </div>

    <div class="space-y-8 text-black leading-relaxed">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Why servicing matters</h2>
            <p class="text-zinc-700">
                Fire detection and alarm systems protect people and property. Regular testing and maintenance help ensure devices operate when needed,
                batteries and power supplies remain healthy, and faults are found before they compromise the system.
                For non-domestic and many multi-occupancy premises in the UK, good practice is framed by
                <a href="<?= url('/pages/keywords/bs-5839.php') ?>" class="text-[#ff6b00] hover:underline">BS 5839</a>
                (notably Part 1 for non-domestic systems), alongside your fire risk assessment and any insurer or enforcing authority expectations.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">User tests vs engineer servicing</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Routine user checks</h3>
                    <p class="text-sm text-zinc-600">
                        Typically includes weekly operation of a manual call point (rotating through points over time), checking the panel is clear of faults,
                        and recording results in the logbook. Follow the procedure for your system grade and site instructions.
                    </p>
                </div>
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Periodic engineer service</h3>
                    <p class="text-sm text-zinc-600">
                        Competent engineers inspect and test devices, interfaces, batteries, sounders and panel functions on a planned schedule
                        (often six-monthly for many BS 5839-1 systems, subject to risk assessment and system design). Certificates and recommendations are issued.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What a service visit usually covers</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Panel status, event log review and fault investigation</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Sampling / testing of detectors, call points and sounders as per schedule</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Battery and power supply checks; replacements where due</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Cause-and-effect and interface checks (e.g. doors, AOV, monitoring) where fitted</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Logbook update and service documentation for audits</li>
            </ul>
            <p class="text-zinc-700 mt-4">
                Exact scope depends on system type (conventional, addressable, wireless), category and manufacturer guidance.
                Pair fire alarm work with
                <a href="<?= url('/pages/services/emergency-lighting.php') ?>" class="text-[#ff6b00] hover:underline">emergency lighting testing</a>
                where both form part of your life-safety regime.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Keep a clear paper trail</h2>
            <p class="text-zinc-700">
                Maintain a site logbook (or digital equivalent) with weekly tests, engineer visits, false alarms, modifications and battery changes.
                Insurers, fire officers and managing agents commonly ask for these records during audits or after incidents.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">When to consider an upgrade</h2>
            <p class="text-zinc-700">
                Obsolete panels, unsupported detectors, repeated nuisance alarms, incomplete coverage after layout changes, or missing interfaces with other life-safety systems
                are common triggers for redesign. We can survey existing systems and quote upgrades that align with current standards and your risk assessment.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Related Icomply services</h2>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Fire alarm services</a>
                <a href="<?= url('/pages/keywords/fire-alarm-servicing.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Fire alarm servicing keyword</a>
                <a href="<?= url('/pages/keywords/fire-alarm-maintenance.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Fire alarm maintenance</a>
                <a href="<?= url('/pages/keywords/periodic-fire-alarm-service.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Periodic fire alarm service</a>
                <a href="<?= url('/pages/keywords/fire-alarm-maintenance-contract.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Maintenance contracts</a>
                <a href="<?= url('/commercial.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial compliance</a>
                <a href="<?= url('/packages.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Packages</a>
                <a href="<?= url('/pages/resources/eicr-guide.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">EICR guide</a>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-14 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Book fire alarm servicing</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Share panel brand, site type and last service date — we quote maintenance visits and multi-site contracts across the North West.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20fire%20alarm%20servicing"
               target="_blank" rel="noopener"
               class="border border-white/40 px-8 py-3.5 rounded-2xl font-semibold hover:bg-white/10">WhatsApp us</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
               class="border border-white/40 px-8 py-3.5 rounded-2xl font-semibold hover:bg-white/10"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>

    <div class="mt-10">
        <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
    </div>

    <p class="mt-8 text-sm text-zinc-500">
        <a href="<?= url('/pages/resources/index.php') ?>" class="text-[#ff6b00] hover:underline">← Back to resources</a>
        ·
        <a href="<?= url('/faq.php') ?>" class="text-[#ff6b00] hover:underline">FAQ</a>
        ·
        <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="text-[#ff6b00] hover:underline">Fire alarms service hub</a>
    </p>
</article>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Request a fire alarm service quote</h2>
            <p class="mt-3 text-zinc-600">One-off visits or planned maintenance contracts with full documentation.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="Fire Alarms — Servicing" selected>Fire Alarms — Servicing</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, panel brand, number of devices / floors, last service date…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
