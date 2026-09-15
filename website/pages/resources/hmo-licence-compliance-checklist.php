<?php
/**
 * Resource — HMO licence compliance checklist (high-level, not legal advice).
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';

hmoEnsureSession();

$pageTitle = 'HMO Licence Compliance Checklist | England Guidance';
$metaDesc = 'High-level HMO licence compliance checklist for landlords in Greater Manchester — gas safety, EICR, fire risk assessment, alarms, emergency lighting and documentation. Not legal advice.';
$metaKeywords = 'HMO licence checklist, HMO licence compliance, HMO landlord certificates, HMO fire safety checklist Stockport, HMO licence Manchester';
$ogImage = url('/assets/images/services/fire-risk-assessments.jpg');
$canonicalUrl = url('/pages/resources/hmo-licence-compliance-checklist');

$homeUrl = rtrim(SITE_URL, '/') . '/';
$crumbs = [
    ['name' => 'Home', 'href' => $homeUrl, 'url' => $homeUrl],
    ['name' => 'Resources', 'href' => url('/pages/resources'), 'url' => url('/pages/resources')],
    ['name' => 'HMO licence checklist', 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true],
];

$checklist = [
    [
        'title' => 'Confirm whether the house is an HMO — and which licence',
        'body' => 'Mandatory licensing, additional licensing and selective licensing are different. Occupancy, storeys and local schemes decide whether you need a licence at all. Check the current scheme for the property’s council (Stockport, Manchester and other GM authorities differ). This page cannot determine that for you.',
        'link' => url('/pages/landlords'),
        'linkLabel' => 'Landlord services overview',
    ],
    [
        'title' => 'Gas safety record (where gas is present)',
        'body' => 'Annual landlord gas safety checks by a Gas Safe engineer remain a core duty. In an HMO, list shared boilers and landlord-supplied cookers clearly. Keep the record with the licence file.',
        'link' => url('/pages/hmo-gas-safety'),
        'linkLabel' => 'HMO gas safety',
    ],
    [
        'title' => 'Electrical Installation Condition Report (EICR)',
        'body' => 'England’s private rented electrical rules commonly expect a satisfactory EICR at least every five years. Define landlord common parts versus individual lets so the report matches how the HMO is wired.',
        'link' => url('/pages/hmo-eicr'),
        'linkLabel' => 'HMO EICR',
    ],
    [
        'title' => 'Fire risk assessment',
        'body' => 'A suitable and sufficient FRA for the HMO / common parts is routinely asked for at application, inspection and insurance. It is not a fire certificate and it is not the licence itself.',
        'link' => url('/pages/hmo-fra'),
        'linkLabel' => 'HMO fire risk assessment',
    ],
    [
        'title' => 'Fire detection and warning',
        'body' => 'Licence conditions and the FRA usually specify a detection grade/category (often BS 5839-6 for houses). Interlinked smoke/heat/CO in smaller houses is not always enough for a larger HMO. We quote fire alarms as an add-on, not as a hidden package line.',
        'link' => url('/pages/hmo-fire-alarms'),
        'linkLabel' => 'HMO fire alarms',
    ],
    [
        'title' => 'Emergency lighting (where required)',
        'body' => 'Common parts of larger or higher-risk HMOs often need emergency lighting, tested to BS 5266 practice, with a logbook. Not every small HMO needs a full commercial system — the FRA and licence conditions decide.',
        'link' => url('/pages/hmo-emergency-lighting'),
        'linkLabel' => 'HMO emergency lighting',
    ],
    [
        'title' => 'Fire doors and means of escape',
        'body' => 'Self-closers, intumescent strips, vision panels and clear escape routes are frequent licence and FRA findings. We survey and upgrade fire doors as an optional add-on after we have seen the house.',
        'link' => url('/pages/hmo-fire-doors'),
        'linkLabel' => 'HMO fire doors',
    ],
    [
        'title' => 'Smoke and carbon monoxide alarms',
        'body' => 'Working alarms on day one of each tenancy remain a baseline duty even where a full fire alarm system is also fitted. Check placement against current national rules and any licence condition.',
        'link' => url('/pages/services/smoke-co-alarms'),
        'linkLabel' => 'Smoke & CO alarms',
    ],
    [
        'title' => 'Documentation pack',
        'body' => 'Store the licence (or application), gas records, EICR, FRA, alarm/emergency lighting certificates, floor plans if requested, and contractor details in one place. Inspectors ask for them together.',
        'link' => url('/pages/packages/hmo-compliance'),
        'linkLabel' => 'HMO compliance package',
    ],
];

$faqs = [
    [
        'q' => 'Does completing this checklist get me an HMO licence?',
        'a' => 'No. Licensing is a local-authority process. This list is a practical prompt for certificates and fire precautions landlords commonly need. It is not an application form and not legal advice.',
    ],
    [
        'q' => 'Can Icomply apply for the licence on my behalf?',
        'a' => 'We provide the compliance work and documents. Licence applications are made to the council that covers the property. We can tell you which certificates we can supply for the pack.',
    ],
    [
        'q' => 'Should I book the HMO package or individual certificates?',
        'a' => 'If EICR, gas and FRA are all due, the HMO compliance package is the usual booking. If only one is expired, use the matching topic page so you are not quoted work you do not need.',
    ],
];

require SITE_ROOT . '/includes/header.php';
echo hmoBreadcrumbJsonLd($crumbs);
echo hmoFaqJsonLd($faqs);
?>

<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <?= hmoHeroBreadcrumbs($crumbs) ?>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                HMO landlords · Resource guide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                HMO licence<br>
                <span class="text-[#ff6b00]">compliance checklist</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                A high-level list of certificates and fire precautions Greater Manchester landlords often need when applying for or holding an HMO licence — use it to brief your agent, not as a legal determination.
            </p>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> HMO definitions, amenity standards and licensing schemes differ by local authority and change over time.
        Confirm current duties with Stockport, Manchester or the council that covers the property. This checklist does not grant a licence.
    </div>

    <p class="text-zinc-700 text-lg leading-relaxed mb-8">
        Icomply is based at 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. We carry out
        <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/hmo-eicr'), ENT_QUOTES, 'UTF-8') ?>">HMO EICR</a>,
        <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/hmo-gas-safety'), ENT_QUOTES, 'UTF-8') ?>">gas safety</a>
        and
        <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/hmo-fra'), ENT_QUOTES, 'UTF-8') ?>">fire risk assessments</a>
        — separately or as the
        <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/packages/hmo-compliance'), ENT_QUOTES, 'UTF-8') ?>">HMO compliance package</a>.
    </p>

    <div class="space-y-6">
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
    </div>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold tracking-tight text-black mb-3">FAQ</h2>
        <div class="space-y-4">
            <?php foreach ($faqs as $faq): ?>
                <details class="bg-white border rounded-2xl p-5">
                    <summary class="font-semibold cursor-pointer text-black"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                    <p class="mt-3 text-sm text-zinc-600"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>

    <?= resourceRelatedHtml('hmo-licence-compliance-checklist') ?>

    <div class="mt-10 bg-[#0a2540] text-white p-8 md:p-10 rounded-3xl text-center">
        <h2 class="text-2xl md:text-3xl font-semibold mb-3">Need the certificates behind the checklist?</h2>
        <p class="text-white/85 max-w-md mx-auto mb-6">Tell us the postcode, storeys and which documents are due. Quotes are POA until scope is agreed.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="#quote" class="bg-[#ff6b00] hover:bg-orange-600 px-8 py-3.5 rounded-2xl font-semibold">Request free quote</a>
            <a href="<?= htmlspecialchars(url('/pages/packages/hmo'), ENT_QUOTES, 'UTF-8') ?>" class="border border-white/40 px-8 py-3.5 rounded-2xl font-semibold hover:bg-white/10">HMO packages</a>
        </div>
    </div>

    <div class="mt-10"><?= shareButtonsHtml($pageTitle, $metaDesc) ?></div>
    <p class="mt-8 text-sm text-zinc-500">
        <a href="<?= htmlspecialchars(url('/pages/resources'), ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] hover:underline">← Back to resources</a>
        ·
        <a href="<?= htmlspecialchars(url('/pages/resources/landlord-compliance-checklist'), ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] hover:underline">Landlord checklist</a>
    </p>
</article>

<?= hmoQuoteFormHtml($_SESSION['csrf'], 'HMO licence compliance') ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
