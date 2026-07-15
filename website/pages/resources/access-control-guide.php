<?php
/**
 * Resource article — access control guide (high-level UK guidance, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Access Control Guide | Doors, Cards & Commercial Systems';
$metaDesc = 'High-level UK guide to commercial access control: fobs, cards, biometrics, door hardware, audit trails and maintenance for offices, multi-let and industrial sites in the North West.';
$metaKeywords = 'access control guide, commercial access control, door access system, proximity card reader, Paxton access, access control installation North West';
$ogImage = url('/assets/images/services/access-control.jpg');
$canonicalUrl = url('/pages/resources/access-control-guide.php');

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
            <span class="text-white/80">Access control guide</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Access control · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Access control<br>
                <span class="text-[#ff6b00]">guide for business</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                A plain-English overview of how door access systems work, what to specify, and how to keep them reliable day to day.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> Access control intersects with fire safety (means of escape), employment and data protection considerations.
        This guide is general UK information only. Confirm requirements for your premises with competent fire, security and legal advisers where needed.
    </div>

    <div class="space-y-8 text-black leading-relaxed">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What access control does</h2>
            <p class="text-zinc-700">
                An access control system decides who can open which doors (or gates), when, and leaves an audit trail of attempts.
                Typical credentials include proximity fobs/cards, PIN pads, mobile credentials and, in higher-security areas,
                <a href="<?= url('/pages/keywords/biometric-access-control.php') ?>" class="text-[#ff6b00] hover:underline">biometrics</a>.
                Controllers, readers, locks and software work together — replacing a lock alone is rarely enough for multi-door commercial sites.
            </p>
            <p class="text-zinc-700 mt-3">
                Many buildings combine access control with
                <a href="<?= url('/pages/services/door-entry.php') ?>" class="text-[#ff6b00] hover:underline">door entry / intercoms</a>
                for visitors and with
                <a href="<?= url('/pages/resources/cctv-for-business.php') ?>" class="text-[#ff6b00] hover:underline">CCTV</a>
                at main entrances for visual verification.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Common credential options</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Fobs &amp; cards</h3>
                    <p class="text-sm text-zinc-600">
                        Widely used, low training burden and easy to issue or revoke. Choose encrypted formats where practical and keep a clear leaver process so credentials cannot outlive employment.
                    </p>
                </div>
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">PIN &amp; mobile</h3>
                    <p class="text-sm text-zinc-600">
                        PINs suit low-traffic internal doors but can be shared. Mobile credentials reduce plastic fobs; plan for lost phones and offline doors if the network drops.
                    </p>
                </div>
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Biometrics</h3>
                    <p class="text-sm text-zinc-600">
                        Useful for higher-security zones. Consider hygiene, enrolment time, false rejects and data protection duties before rolling out site-wide.
                    </p>
                </div>
                <div class="bg-white border rounded-2xl p-6">
                    <h3 class="font-semibold text-lg text-black mb-2">Visitor workflows</h3>
                    <p class="text-sm text-zinc-600">
                        Temporary cards, receptionist release or video door entry keep contractors and guests out of permanent user lists while maintaining a record of entry.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Fire safety and fail-safe design</h2>
            <p class="text-zinc-700">
                Controlled doors on escape routes must still allow people to leave safely in an emergency.
                Maglocks, electric strikes and release mechanisms need correct power-fail behaviour, break-glass or green-box overrides where required,
                and integration with the fire alarm where the fire risk assessment specifies free exit or automatic release.
                Never “secure” a door in a way that traps occupants — coordinate security goals with your fire strategy and competent installers.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">What a typical project includes</h2>
            <ol class="list-decimal pl-5 space-y-2 text-zinc-700">
                <li>Site survey — door schedule, traffic patterns, network/power, fail-safe requirements.</li>
                <li>Hardware selection — readers, locks, door contacts, request-to-exit devices, controllers.</li>
                <li>Software set-up — time zones, access levels, anti-passback where used, admin users.</li>
                <li>Commissioning &amp; training — test every door, hand over manuals, issue first credentials.</li>
                <li>Ongoing admin — add/remove users, report on events, schedule maintenance.</li>
            </ol>
            <p class="text-zinc-700 mt-3">
                Brands such as
                <a href="<?= url('/pages/keywords/paxton-access.php') ?>" class="text-[#ff6b00] hover:underline">Paxton</a>
                are common on UK commercial stock; we also support multi-door networked systems for larger portfolios.
            </p>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Day-to-day management tips</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Disable leavers the same day — do not wait for a monthly tidy-up.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Use named credentials, not shared “contractor” fobs that never expire.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Review event logs after incidents; keep admin passwords under dual control.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Book periodic service for door closers, locks, batteries and controller health.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> After office moves or new tenancies, re-map access levels rather than cloning old rules blindly.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Related Icomply services</h2>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('/pages/services/access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Access control services</a>
                <a href="<?= url('/pages/keywords/access-control-installation.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Access control installation</a>
                <a href="<?= url('/pages/keywords/commercial-access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial access control</a>
                <a href="<?= url('/pages/keywords/access-control-maintenance.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Access control maintenance</a>
                <a href="<?= url('/pages/keywords/door-access-control.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Door access control</a>
                <a href="<?= url('/pages/keywords/office-access-control-system.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Office access control</a>
                <a href="<?= url('/pages/resources/cctv-for-business.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">CCTV for business</a>
                <a href="<?= url('/pages/services/door-entry.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Door entry</a>
                <a href="<?= url('/commercial.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial services</a>
                <a href="<?= url('/packages.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Packages</a>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-14 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Plan your access control project</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Share door count, credential preference and site type — we quote installs, upgrades and maintenance across Greater Manchester and the North West.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20access%20control"
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
        <a href="<?= url('/pages/services/access-control.php') ?>" class="text-[#ff6b00] hover:underline">Access control service hub</a>
    </p>
</article>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Request an access control quote</h2>
            <p class="mt-3 text-zinc-600">Single doors, multi-door networks or service contracts — fixed price after survey.</p>
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
                    <option value="Access Control" selected>Access Control</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, number of doors, fob/card/mobile preference, existing system brand if any…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
