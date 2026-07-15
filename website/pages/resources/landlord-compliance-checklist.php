<?php
/**
 * Resource article — landlord compliance checklist (high-level UK guidance, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Landlord Compliance Checklist | UK Rented Property';
$metaDesc = 'Practical UK landlord compliance checklist covering gas safety, EICR, smoke and CO alarms, fire safety, emergency lighting and EPC — high-level guidance for portfolio owners in the North West.';
$metaKeywords = 'landlord compliance checklist, landlord safety certificates, EICR landlord, gas safety certificate, smoke alarms landlord, HMO fire safety North West';
$ogImage = url('/assets/images/services/gas-systems.jpg');
$canonicalUrl = url('/pages/resources/landlord-compliance-checklist.php');

$services = getServices();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$checklist = [
    [
        'title' => 'Gas safety (CP12 / landlord gas safety record)',
        'body' => 'Where gas appliances or flues are present, private landlords in Great Britain generally need an annual gas safety check by a Gas Safe registered engineer, with a record issued to tenants. Keep copies for your portfolio records.',
        'link' => url('/pages/keywords/landlord-gas-safety-certificate.php'),
        'linkLabel' => 'Landlord gas safety certificate',
    ],
    [
        'title' => 'Electrical Installation Condition Report (EICR)',
        'body' => 'In England, private rented sector electrical safety rules commonly require a satisfactory EICR at least every 5 years (and often at a change of tenancy). Remedial work from C1/C2/FI observations should be completed promptly. Other UK nations have their own arrangements.',
        'link' => url('/pages/resources/eicr-guide.php'),
        'linkLabel' => 'Read our EICR guide',
    ],
    [
        'title' => 'Smoke and carbon monoxide alarms',
        'body' => 'Ensure required smoke alarms and carbon monoxide alarms are fitted and working on day one of a tenancy, and checked regularly. Exact placement rules depend on property layout and national regulations — verify current requirements for each dwelling.',
        'link' => url('/pages/keywords/smoke-alarm-installation.php'),
        'linkLabel' => 'Smoke alarm installation',
    ],
    [
        'title' => 'Fire safety & risk assessment',
        'body' => 'HMOs, blocks of flats and some multi-occupied buildings have additional fire safety duties, often including a suitable and sufficient fire risk assessment, means of escape and, where specified, fire detection or emergency lighting systems. Single dwellings still need working smoke/CO alarms as required.',
        'link' => url('/pages/services/fire-alarms.php'),
        'linkLabel' => 'Fire alarm services',
    ],
    [
        'title' => 'Emergency lighting (where applicable)',
        'body' => 'Common parts of multi-occupied residential buildings and many commercial conversions may require emergency lighting maintained and tested to BS 5266 practice. Monthly functional checks and annual full-duration tests are typical user/engineer routines.',
        'link' => url('/pages/keywords/landlord-emergency-lighting.php'),
        'linkLabel' => 'Landlord emergency lighting',
    ],
    [
        'title' => 'Energy Performance Certificate (EPC)',
        'body' => 'Most private rented properties need a valid EPC when marketed or let, subject to exemptions. Minimum energy efficiency standards may also apply before a new tenancy can start — check current MEES rules for your nation.',
        'link' => url('/landlords.php'),
        'linkLabel' => 'Landlord services overview',
    ],
    [
        'title' => 'Documentation pack',
        'body' => 'Store gas records, EICRs, alarm checks, FRA documents, emergency lighting certificates and contractor details in one place per property. Agents, insurers and local authorities may request them at short notice.',
        'link' => url('/packages.php'),
        'linkLabel' => 'Multi-service packages',
    ],
];

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
            <span class="text-white/80">Landlord checklist</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Landlords · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Landlord compliance<br>
                <span class="text-[#ff6b00]">checklist</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                A practical, high-level list of common safety certificates and checks for UK rented property — use it as a conversation starter, not a legal determination.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> Landlord duties differ across England, Scotland, Wales and Northern Ireland, and between single lets, HMOs and blocks.
        This checklist is general guidance only. Confirm current statutory duties for each property and seek professional or legal advice where required.
    </div>

    <div class="space-y-6 text-black leading-relaxed">
        <p class="text-zinc-700 text-lg">
            Whether you manage one flat or a multi-site portfolio, a simple schedule of certificates keeps tenants safer and reduces last-minute scrambles at tenancy changeover.
            Icomply helps North West landlords combine
            <a href="<?= url('/pages/services/electrical.php') ?>" class="text-[#ff6b00] hover:underline">electrical</a>,
            <a href="<?= url('/pages/services/gas-systems.php') ?>" class="text-[#ff6b00] hover:underline">gas</a>,
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="text-[#ff6b00] hover:underline">fire</a>
            and
            <a href="<?= url('/pages/services/emergency-lighting.php') ?>" class="text-[#ff6b00] hover:underline">emergency lighting</a>
            work into one plan.
        </p>

        <?php foreach ($checklist as $i => $item): ?>
        <div class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-7">
            <div class="flex gap-4 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center shrink-0"><?= $i + 1 ?></div>
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-black"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="text-zinc-700 mt-2"><?= htmlspecialchars($item['body'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') ?>" class="inline-block mt-3 text-sm font-semibold text-[#ff6b00] hover:underline">
                        <?= htmlspecialchars($item['linkLabel'], ENT_QUOTES, 'UTF-8') ?> →
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Portfolio tips</h2>
            <ul class="space-y-2 text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Diary renewal dates 8–12 weeks ahead so access and remedials do not delay re-lets.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Bundle neighbouring properties into one engineer day where practical.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Align fire alarm and emergency lighting service dates for multi-occupied buildings.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> Keep digital copies of certificates with property IDs and engineer details.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3">Related resources</h2>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('/landlords.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlords hub</a>
                <a href="<?= url('/packages.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Packages</a>
                <a href="<?= url('/faq.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">FAQ</a>
                <a href="<?= url('/pages/resources/eicr-guide.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">EICR guide</a>
                <a href="<?= url('/pages/resources/fire-alarm-servicing.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Fire alarm servicing</a>
                <a href="<?= url('/pages/keywords/landlord-safety-certificate.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlord safety certificate</a>
                <a href="<?= url('/pages/keywords/landlord-fire-alarm.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Landlord fire alarm</a>
                <a href="<?= url('/commercial.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">Commercial</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]">All services</a>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-14 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Need a landlord compliance package?</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Tell us how many properties and which certificates are due — we quote fixed-price visits across Greater Manchester and the North West.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20landlord%20compliance"
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
        <a href="<?= url('/pages/keywords/index.php') ?>" class="text-[#ff6b00] hover:underline">Keyword guides</a>
        ·
        <a href="<?= url('/contact.php') ?>" class="text-[#ff6b00] hover:underline">Contact</a>
    </p>
</article>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Request your landlord quote</h2>
            <p class="mt-3 text-zinc-600">Single certificates or multi-property packages — fixed price after scope is agreed.</p>
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
                    <option value="Landlord compliance" selected>Landlord compliance</option>
                    <option value="Electrical — EICR">Electrical — EICR</option>
                    <option value="Gas safety certificate">Gas safety certificate</option>
                    <option value="Fire Alarms">Fire Alarms</option>
                    <option value="Emergency Lighting">Emergency Lighting</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Number of properties, postcodes, certificates due (EICR / gas / fire / emergency lighting)…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
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
