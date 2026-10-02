<?php
/**
 * HMO landlords audience hub — Greater Manchester.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
require_once SITE_ROOT . '/includes/partials.php';
require_once SITE_ROOT . '/includes/share.php';

hmoEnsureSession();

$pageTitle = 'HMO Landlords | Compliance Greater Manchester';
$metaDesc = 'HMO landlord services from Offerton, Stockport SK2 — packages for EICR, gas safety, fire risk assessment, alarms, emergency lighting and fire doors across Greater Manchester. POA after scope. Not a licence grant.';
$metaKeywords = 'HMO landlords Greater Manchester, HMO landlord Stockport, HMO compliance Manchester, HMO certificates SK2';
$ogImage = url('/assets/images/services/fire-risk-assessments.jpg');
$canonicalUrl = url('/pages/hmo-landlords');

$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$wa = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode('Hi Icomply, I need HMO landlord compliance');

$crumbs = [
    ['name' => 'Home', 'href' => $homeUrl, 'url' => $homeUrl],
    ['name' => 'Landlords', 'href' => url('/pages/landlords'), 'url' => url('/pages/landlords')],
    ['name' => 'HMO landlords', 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true],
];

$variants = hmoPackageVariants();
$hubs = hmoQualityHubs();
$faqs = array_merge([
    [
        'q' => 'Who is this hub for?',
        'a' => 'Private landlords, letting agents and small portfolios running licensed or licensable houses in multiple occupation in Greater Manchester. Single-let landlords should start on the Landlords page or Let Ready.',
    ],
], hmoCommonFaqs());

require SITE_ROOT . '/includes/header.php';
echo hmoBreadcrumbJsonLd($crumbs);
echo hmoFaqJsonLd($faqs);
?>

<section class="relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <?= hmoHeroBreadcrumbs($crumbs) ?>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Audience · HMO landlords
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                HMO landlords<br>
                <span class="text-[#ff6b00]">in Greater Manchester</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Practical certificates and fire-safety work for shared houses — from a Stockport SK2 yard,
                without a published price list we cannot stand behind and without pretending we grant the licence.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars(url('/pages/packages/hmo'), ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">HMO packages</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">Free quote</a>
                <a href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
            <p class="mt-5 text-sm text-white/60">17 Woodlands Park Road, Offerton, Stockport SK2 5DE · <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>

<?= sectionTrustStrip([
    ['Three package variants', 'Compliance, fire safety, occupancy'],
    ['Honest pricing', 'POA — fixed quote after scope'],
    ['Local engineers', 'Offerton, Stockport SK2 5DE'],
    ['Not a licence office', 'We certificate; the council licences'],
]) ?>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">What we do for HMO landlords</h2>
            <p class="mt-4 text-zinc-700 leading-relaxed">
                Icomply is a property-services contractor, not a licensing solicitor. We inspect, test, install and
                document the work that licence applications, agents and insurers commonly ask for: electrical condition
                reports, landlord gas safety records, fire risk assessments, detection, emergency lighting and fire doors.
                Occupancy, storeys and local schemes decide whether a house needs a licence at all — that call sits with
                the local housing authority.
            </p>
            <p class="mt-4 text-zinc-700 leading-relaxed">
                Use the
                <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/resources/hmo-licence-compliance-checklist'), ENT_QUOTES, 'UTF-8') ?>">HMO licence compliance checklist</a>
                as a conversation starter. It is not legal advice and it cannot tell you whether Stockport, Manchester
                or another Greater Manchester scheme applies to a given address.
            </p>
        </div>
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Choose a package</h2>
            <div class="mt-6 space-y-4">
                <?php foreach ($variants as $variant): ?>
                    <a href="<?= htmlspecialchars(url($variant['path']), ENT_QUOTES, 'UTF-8') ?>" class="block bg-white border rounded-2xl p-5 hover:border-[#ff6b00] transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-[#ff6b00]"><?= htmlspecialchars($variant['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                                <h3 class="font-semibold text-lg text-black mt-1"><?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($variant['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <span class="text-sm font-semibold text-zinc-500 shrink-0"><?= htmlspecialchars($variant['price'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Quality hubs</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Twelve HMO pages — not doorway spam</h2>
        <p class="mt-2 text-zinc-600 max-w-2xl">Named Stockport and Manchester landings exist only for EICR, FRA and gas safety. Fire topics stay as Greater Manchester hubs.</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-10">
            <?php foreach ($hubs as $hub): ?>
                <a href="<?= htmlspecialchars(url($hub['path']), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border rounded-2xl p-5 hover:border-[#ff6b00] transition">
                    <h3 class="font-semibold text-black"><?= htmlspecialchars($hub['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($hub['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Single-let vs HMO</h2>
            <p class="mt-4 text-zinc-700 leading-relaxed">
                Family ASTs and one-bed flats belong on
                <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/landlords'), ENT_QUOTES, 'UTF-8') ?>">Landlord services</a>
                or the
                <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/packages/let-ready'), ENT_QUOTES, 'UTF-8') ?>">Let Ready</a>
                pack. Shared houses need a wider scope — extra boards, shared plant, escape routes and (often) a written FRA.
                If you are not sure which side of the line a house sits on, say so on the quote form; we will not invent a licence category for you.
            </p>
            <div class="mt-6"><?= hmoLinkChipsHtml([
                ['href' => url('/pages/packages/hmo'), 'label' => 'All HMO packages'],
                ['href' => url('/pages/landlords'), 'label' => 'All landlord services'],
                ['href' => url('/pages/packages/let-ready'), 'label' => 'Let Ready (single lets)'],
                ['href' => url('/pages/resources/landlord-compliance-checklist'), 'label' => 'Landlord checklist'],
            ]) ?></div>
        </div>
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Frequently asked questions</h2>
            <div class="mt-6 space-y-4">
                <?php foreach ($faqs as $faq): ?>
                    <details class="bg-white border rounded-2xl p-5">
                        <summary class="font-semibold cursor-pointer text-black"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                        <p class="mt-3 text-sm text-zinc-600"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="mt-12"><?= shareButtonsHtml($pageTitle, $metaDesc) ?></div>
</section>

<?= hmoQuoteFormHtml($_SESSION['csrf'], 'HMO Compliance Package', 'HMO address / postcode, number of lets, licence status, certificates due…') ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
