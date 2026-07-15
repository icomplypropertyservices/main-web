<?php
/**
 * Resource article — CCTV for business (high-level UK guidance, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'CCTV for Business | Commercial Camera Systems Guide';
$metaDesc = 'High-level UK guide to CCTV for business: camera types, recording, remote viewing, maintenance and practical privacy considerations for commercial and multi-site premises in the North West.';
$metaKeywords = 'CCTV for business, commercial CCTV system, CCTV installation, IP CCTV, CCTV maintenance, business security cameras North West';
$ogImage = url('/assets/images/services/cctv.jpg');
$canonicalUrl = url('/pages/resources/cctv-for-business.php');

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
            <span class="text-white/80">CCTV for business</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                CCTV · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                CCTV for business<br>
                <span class="text-[#ff6b00]">a practical guide</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                How commercial camera systems are typically planned, recorded and maintained — plus what to discuss before you install or upgrade.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> CCTV use in the UK can engage data protection and privacy duties (for example under UK GDPR and the Data Protection Act 2018).
        This page is general information only. Confirm your legal obligations for staff, visitors and public areas, and take professional advice where needed.
    </div>

    <div class="space-y-8 text-black leading-relaxed">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Why businesses invest in CCTV</h2>
            <p class="text-zinc-700">
                Well-designed camera coverage deters opportunistic crime, supports incident investigation and gives managers visibility of entrances, yards, stock areas and out-of-hours activity.
                For many commercial and multi-let sites it sits alongside
                <a href="<?= url('/pages/services/access-control.php') ?>" class="text-[#ff6b00] hover:underline">access control</a>,
                <a href="<?= url('/pages/services/intruder-alarm.php') ?>" class="text-[#ff6b00] hover:underline">intruder alarms</a>
                and door entry as part of a layered security plan — not a stand-alone fix for every risk.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Planning coverage that works</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Map entrances, fire exits, car parks, loading bays, plant rooms and high-value stock zones first</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Match camera type to the job — fixed, PTZ, turret/dome, bullet, or specialised low-light models</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Agree image quality goals (identification at a door vs overview of a yard) before buying megapixels</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Check power, network capacity and mounting heights early to avoid rework</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">●</span> Avoid unnecessary capture of neighbouring private property where practicable</li>
            </ul>
            <p class="text-zinc-700 mt-4">
                A short site survey usually saves cost compared with adding cameras after the fact.
                See also our keyword guides on
                <a href="<?= url('/pages/keywords/commercial-cctv-system.php') ?>" class="text-[#ff6b00] hover:underline">commercial CCTV systems</a>
                and
                <a href="<?= url('/pages/keywords/ip-cctv-system.php') ?>" class="text-[#ff6b00] hover:underline">IP CCTV</a>.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Recording, retention and remote viewing</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">On-site recording</h3>
                    <p class="text-sm text-zinc-600">
                        NVR/DVR systems store footage locally. Choose retention periods that match your risk profile and data protection policy,
                        with enough disk space for continuous recording at the agreed resolution and frame rate.
                    </p>
                </div>
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Remote access</h3>
                    <p class="text-sm text-zinc-600">
                        Secure apps and browser views let managers check sites out of hours.
                        Use strong credentials, restrict user accounts and keep firmware updated — remote access without basic cyber hygiene creates new risks.
                    </p>
                </div>
            </div>
            <p class="text-zinc-700 mt-4">
                Document who can view live feeds, who can export clips, and how long footage is kept.
                That paperwork helps if an employee, insurer or police force requests evidence after an incident.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Practical privacy steps (high level)</h2>
            <p class="text-zinc-700">
                Many organisations treat CCTV as personal data processing. Common good practice includes a clear purpose for the system,
                signage where cameras operate, a privacy notice, limited access to recordings, and a process for subject access requests.
                Exact duties depend on your organisation and camera locations — this is not a complete compliance checklist.
                The Information Commissioner’s Office (ICO) publishes guidance on workplace and public-facing surveillance that many businesses use as a starting point.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Installation and ongoing maintenance</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Professional install with tidy cabling, weather-rated housings and correct focussing / angles</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Commissioning pack: camera list, IP plan, passwords handed over securely, user training</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Periodic maintenance — clean lenses, check recording health, replace failing hard drives, apply firmware updates</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> After fit-outs or yard changes, reassess blind spots rather than assuming the original layout still works</li>
            </ul>
            <p class="text-zinc-700 mt-4">
                Read more on
                <a href="<?= url('/pages/keywords/cctv-installation.php') ?>" class="text-[#ff6b00] hover:underline">CCTV installation</a>
                and
                <a href="<?= url('/pages/keywords/cctv-maintenance.php') ?>" class="text-[#ff6b00] hover:underline">CCTV maintenance</a>.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Related Icomply services</h2>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('/pages/services/cctv.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">CCTV services</a>
                <a href="<?= url('/pages/keywords/cctv-installation.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">CCTV installation</a>
                <a href="<?= url('/pages/keywords/commercial-cctv-system.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial CCTV system</a>
                <a href="<?= url('/pages/keywords/cctv-maintenance.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">CCTV maintenance</a>
                <a href="<?= url('/pages/keywords/remote-cctv-access.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Remote CCTV access</a>
                <a href="<?= url('/pages/resources/access-control-guide.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Access control guide</a>
                <a href="<?= url('/pages/services/intruder-alarm.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Intruder alarms</a>
                <a href="<?= url('/commercial.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial services</a>
                <a href="<?= url('/packages.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Packages</a>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-14 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Get a business CCTV quote</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Tell us the site type, number of entrances and whether you need new install, upgrade or maintenance — we cover Greater Manchester and the wider North West.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20business%20CCTV"
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
        <a href="<?= url('/pages/services/cctv.php') ?>" class="text-[#ff6b00] hover:underline">CCTV service hub</a>
    </p>
</article>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Request a CCTV quote</h2>
            <p class="mt-3 text-zinc-600">New systems, camera additions, NVR upgrades or service contracts — fixed price after survey.</p>
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
                    <option value="CCTV — Business system" selected>CCTV — Business system</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, premises type, number of cameras needed, indoor/outdoor, remote viewing required…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
