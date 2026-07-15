<?php
/**
 * Resource article — emergency lighting testing (high-level UK guidance, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Emergency Lighting Testing Guide | BS 5266 Overview';
$metaDesc = 'High-level UK guide to emergency lighting testing: monthly function checks, annual full-duration tests, BS 5266 practice, logbooks and when to upgrade for commercial and multi-let sites in the North West.';
$metaKeywords = 'emergency lighting testing, BS 5266, emergency lighting certificate, monthly emergency light test, annual emergency lighting test, commercial emergency lighting North West';
$ogImage = url('/assets/images/services/emergency-lighting.jpg');
$canonicalUrl = url('/pages/resources/emergency-lighting-testing.php');

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
            <span class="text-white/80">Emergency lighting testing</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Emergency lighting · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Emergency lighting testing<br>
                <span class="text-[#ff6b00]">explained</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                A practical overview of function tests, full-duration discharge tests, logbooks and common upgrade triggers for life-safety lighting.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> Emergency lighting duties depend on premises type, fire risk assessment and applicable regulations.
        This guide is general UK information only and does not replace BS 5266, manufacturer instructions or competent person inspection of your site.
    </div>

    <div class="space-y-8 text-black leading-relaxed">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Why emergency lighting is tested</h2>
            <p class="text-zinc-700">
                Emergency lighting helps people leave a building safely if the normal supply fails — on escape routes, at exits, in open areas and where tasks create higher risk.
                Batteries age, lamps fail and self-test modules can drift out of specification. Planned testing finds faults before they matter and creates the paper trail insurers,
                fire officers and managing agents often request.
                Good practice for many UK non-domestic systems is framed by
                <a href="<?= url('/pages/keywords/bs-5266.php') ?>" class="text-[#ff6b00] hover:underline">BS 5266</a>
                alongside your fire risk assessment.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Monthly checks vs annual full tests</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Monthly function test</h3>
                    <p class="text-sm text-zinc-600">
                        A short simulated mains failure (typically a few minutes) to confirm each luminaire or exit sign illuminates correctly.
                        Results should be recorded in the site logbook. Self-testing fittings still need review of indicators and any fault reports.
                    </p>
                </div>
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Annual full-duration test</h3>
                    <p class="text-sm text-zinc-600">
                        A full rated discharge (often three hours for many installations, subject to system design) followed by recharge checks.
                        Competent engineers commonly complete this with certificates, battery recommendations and schedule updates.
                    </p>
                </div>
            </div>
            <p class="text-zinc-700 mt-4">
                Exact intervals and durations depend on system type (maintained, non-maintained, central battery, self-test) and your risk assessment —
                always follow manufacturer guidance and the design documentation for the site.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What an engineer visit usually covers</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Visual inspection of fittings, exit signs and supply arrangements</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Function and/or full-duration discharge testing as scheduled</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Battery condition, charge status and replacement recommendations where due</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Review of self-test logs or central monitoring outputs where fitted</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Logbook update and certification for audits and insurers</li>
            </ul>
            <p class="text-zinc-700 mt-4">
                Pair emergency lighting work with
                <a href="<?= url('/pages/resources/fire-alarm-servicing.php') ?>" class="text-[#ff6b00] hover:underline">fire alarm servicing</a>
                where both sit in the same life-safety programme — one visit day often reduces disruption for multi-let and commercial sites.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Maintained, non-maintained and self-test</h2>
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="bg-white border rounded-2xl p-5">
                    <div class="font-semibold text-[#0a2540]">Maintained</div>
                    <p class="text-sm text-zinc-600 mt-1">Luminaires lit in normal use and on emergency supply — common for exit signs and some open areas.</p>
                </div>
                <div class="bg-white border rounded-2xl p-5">
                    <div class="font-semibold text-[#0a2540]">Non-maintained</div>
                    <p class="text-sm text-zinc-600 mt-1">Illuminates only when the normal supply fails — frequent on escape routes and plant areas.</p>
                </div>
                <div class="bg-white border rounded-2xl p-5">
                    <div class="font-semibold text-[#0a2540]">Self-test / automatic</div>
                    <p class="text-sm text-zinc-600 mt-1">On-board or networked testing reduces manual work, but faults still need investigation and records.</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Keep clear records</h2>
            <p class="text-zinc-700">
                Maintain a log (paper or digital) of monthly tests, annual discharge results, battery changes, new fittings and defects.
                After layout changes, refurbishments or a new fire risk assessment, review coverage — missing exit signs and dark corners are common findings on older stock.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">When to consider an upgrade</h2>
            <p class="text-zinc-700">
                Repeated battery failures, obsolete fluorescent packs, incomplete coverage after fit-outs, or a move to LED self-test systems are typical upgrade drivers.
                We can survey existing installations and quote replacements that align with current practice and your risk assessment.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Related Icomply services</h2>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('/pages/services/emergency-lighting.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Emergency lighting services</a>
                <a href="<?= url('/pages/keywords/emergency-lighting-testing.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Emergency lighting testing keyword</a>
                <a href="<?= url('/pages/keywords/bs-5266.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">BS 5266</a>
                <a href="<?= url('/pages/keywords/emergency-lighting-certificate.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Emergency lighting certificate</a>
                <a href="<?= url('/pages/keywords/periodic-emergency-lighting-test.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Periodic emergency lighting test</a>
                <a href="<?= url('/pages/keywords/landlord-emergency-lighting.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlord emergency lighting</a>
                <a href="<?= url('/pages/resources/fire-alarm-servicing.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Fire alarm servicing</a>
                <a href="<?= url('/commercial.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial compliance</a>
                <a href="<?= url('/packages.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Packages</a>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-14 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Book emergency lighting testing</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Share the postcode, number of fittings and last test date — we quote annual tests, battery replacements and multi-site contracts across the North West.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20emergency%20lighting%20testing"
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
        <a href="<?= url('/pages/services/emergency-lighting.php') ?>" class="text-[#ff6b00] hover:underline">Emergency lighting service hub</a>
    </p>
</article>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Request an emergency lighting quote</h2>
            <p class="mt-3 text-zinc-600">Annual full tests, remedial works or planned maintenance with full documentation.</p>
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
                    <option value="Emergency Lighting — Testing" selected>Emergency Lighting — Testing</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, approx. number of fittings / floors, last test date, self-test or conventional…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
