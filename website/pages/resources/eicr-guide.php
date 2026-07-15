<?php
/**
 * Resource article — EICR guide (high-level UK guidance, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'EICR Guide for Landlords & Commercial Sites';
$metaDesc = 'Plain-English EICR guide for UK landlords and commercial properties — what an Electrical Installation Condition Report covers, typical intervals, C1–C3 codes and how to book testing in the North West.';
$metaKeywords = 'EICR guide, Electrical Installation Condition Report, landlord EICR, commercial EICR, BS 7671, electrical safety certificate North West';
$ogImage = url('/assets/images/services/electrical.jpg');
$canonicalUrl = url('/pages/resources/eicr-guide.php');

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
            <span class="text-white/80">EICR guide</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Electrical · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                EICR guide for landlords<br>
                <span class="text-[#ff6b00]">&amp; commercial sites</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                What an Electrical Installation Condition Report is, why it matters, and what to expect from a professional inspection.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> This page is general UK guidance only. Duties vary by tenure, nation and premises type.
        Confirm current requirements for your property and take professional advice where needed.
    </div>

    <div class="prose-like space-y-8 text-black leading-relaxed">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What is an EICR?</h2>
            <p class="text-zinc-700">
                An <strong>Electrical Installation Condition Report (EICR)</strong> is a formal inspection and test of the fixed electrical installation —
                consumer units, wiring, sockets, lighting circuits and related accessories — against the current edition of
                <a href="<?= url('/pages/keywords/bs-7671.php') ?>" class="text-[#ff6b00] hover:underline">BS 7671</a>
                (IET Wiring Regulations). It is carried out by a competent person and results in a coded report with observations and recommendations.
            </p>
            <p class="text-zinc-700 mt-3">
                An EICR is not the same as
                <a href="<?= url('/pages/keywords/pat-testing.php') ?>" class="text-[#ff6b00] hover:underline">PAT testing</a>
                (portable appliances) or a simple visual check. It focuses on the fixed installation condition and safety.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Who typically needs an EICR?</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> <span><strong>Private landlords (England):</strong> electrical safety regulations generally require a satisfactory EICR at least every 5 years and at a change of tenancy in many cases. Scotland, Wales and Northern Ireland have their own frameworks — check local rules.</span></li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> <span><strong>Commercial &amp; multi-let sites:</strong> often scheduled on a risk-based frequency (commonly 1–5 years) as part of health &amp; safety and insurance programmes.</span></li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> <span><strong>Homeowners:</strong> recommended periodically, especially before sale, after major works, or if the installation is older or modified.</span></li>
            </ul>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What the report codes mean</h2>
            <div class="grid sm:grid-cols-2 gap-4 not-prose">
                <?php
                $codes = [
                    ['C1', 'Danger present — immediate remedial action required.'],
                    ['C2', 'Potentially dangerous — urgent remedial action required.'],
                    ['C3', 'Improvement recommended — not necessarily unsatisfactory alone.'],
                    ['FI', 'Further investigation required without delay.'],
                ];
                foreach ($codes as [$code, $desc]): ?>
                <div class="bg-white border rounded-2xl p-5">
                    <div class="font-semibold text-[#0a2540] text-lg"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></div>
                    <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="text-zinc-700 mt-4">
                An overall outcome of <strong>satisfactory</strong> or <strong>unsatisfactory</strong> is recorded.
                Unsatisfactory outcomes (typically involving C1, C2 or FI) need prompt remedial work and often a re-check so you hold a clear certificate for tenants, agents or insurers.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What happens on the day?</h2>
            <ol class="list-decimal pl-5 space-y-2 text-zinc-700">
                <li>Access arranged to the consumer unit, distribution boards and key circuits.</li>
                <li>Visual inspection plus dead and live testing where safe and practical.</li>
                <li>Observations coded and discussed where issues are found.</li>
                <li>Written EICR issued with circuit details and recommended next inspection date.</li>
            </ol>
            <p class="text-zinc-700 mt-3">
                Expect some temporary power isolation during testing. For multi-occupancy or commercial sites, plan around business hours and sensitive equipment.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Related Icomply services</h2>
            <div class="flex flex-wrap gap-2 not-prose">
                <a href="<?= url('/pages/services/electrical.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Electrical services</a>
                <a href="<?= url('/pages/keywords/eicr.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">EICR keyword guide</a>
                <a href="<?= url('/pages/keywords/landlord-electrical-certificate.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlord electrical certificate</a>
                <a href="<?= url('/pages/keywords/electrical-installation-condition-report.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">EICR explained</a>
                <a href="<?= url('/landlords.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlord services</a>
                <a href="<?= url('/packages.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Compliance packages</a>
                <a href="<?= url('/pages/resources/landlord-compliance-checklist.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlord checklist</a>
                <a href="<?= url('/pages/resources/fire-alarm-servicing.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Fire alarm servicing</a>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-14 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Book an EICR quote</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Tell us the postcode, property type and access notes — we provide fixed-price quotes across Greater Manchester and the North West.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20EICR"
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
        <a href="<?= url('/pages/keywords/index.php') ?>" class="text-[#ff6b00] hover:underline">Keyword guides</a>
    </p>
</article>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Request your EICR quote</h2>
            <p class="mt-3 text-zinc-600">Fixed-price after scope is agreed. Certificates issued after inspection.</p>
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
                    <option value="Electrical — EICR" selected>Electrical — EICR</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, property type (flat / HMO / office), number of consumer units…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
