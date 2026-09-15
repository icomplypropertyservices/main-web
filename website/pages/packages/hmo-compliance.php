<?php
/**
 * HMO Compliance Package landing — EICR + gas safety + FRA.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
require_once SITE_ROOT . '/includes/partials.php';
require_once SITE_ROOT . '/includes/share.php';

hmoEnsureSession();

$pageTitle = 'HMO Compliance Package | EICR + Gas + FRA';
$metaDesc = 'HMO compliance package for Greater Manchester: EICR, landlord gas safety and fire risk assessment in one coordinated visit plan. Optional emergency lighting, fire alarms and fire doors. POA.';
$metaKeywords = 'HMO compliance package, HMO EICR gas FRA, HMO landlord package Stockport, HMO licence certificates Manchester';
$ogImage = url('/assets/images/services/electrical.jpg');
$canonicalUrl = url('/pages/packages/hmo-compliance');

$bundle = hmoComplianceBundle();
$services = getServices();
$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$wa = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode('Hi Icomply, I need a quote for the HMO Compliance Package');

$crumbs = [
    ['name' => 'Home', 'href' => $homeUrl, 'url' => $homeUrl],
    ['name' => 'Packages', 'href' => url('/pages/packages'), 'url' => url('/pages/packages')],
    ['name' => 'HMO packages', 'href' => url('/pages/packages/hmo'), 'url' => url('/pages/packages/hmo')],
    ['name' => 'HMO compliance', 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true],
];

$steps = [
    ['1', 'Send the house', 'Address, storeys, number of lets, last certificate dates and whether a licence is in force or applied for.'],
    ['2', 'We scope & quote', 'POA until we confirm what is fitted (gas, detection, lighting). Then a fixed-price quote — no invented online price.'],
    ['3', 'Attend & document', 'Coordinated visits, certificates and a single pack for your agent or licence file.'],
];

$faqs = hmoCommonFaqs();
$faqs[] = [
    'q' => 'What if the HMO has no gas?',
    'a' => 'We drop the gas safety record from the bundle and quote EICR + FRA (plus any fire add-ons). Tell us on the form so we do not price a CP12 you do not need.',
];

require SITE_ROOT . '/includes/header.php';
echo hmoBreadcrumbJsonLd($crumbs);
echo hmoFaqJsonLd($faqs);
?>

<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <?= hmoHeroBreadcrumbs($crumbs) ?>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Package · EICR + gas + FRA
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                HMO compliance<br>
                <span class="text-[#ff6b00]">package</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                One coordinator, one visit plan and one documentation pack for the three certificates HMO landlords
                most often need together: electrical (EICR), gas safety and fire risk assessment.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request package quote</a>
                <a href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">WhatsApp</a>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
            <p class="mt-5 text-sm text-white/60">From <strong class="text-white">POA</strong> — fixed quote after scope. We do not sell licences.</p>
        </div>
    </div>
</section>

<?= sectionTrustStrip([
    ['Three core certificates', 'EICR, gas safety, FRA'],
    ['POA only', 'Fixed price once we see the house'],
    ['Add-ons optional', 'Alarms, lighting, fire doors'],
    ['Stockport based', 'Greater Manchester coverage'],
]) ?>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">What is included (indicative)</h2>
            <p class="mt-3 text-zinc-600">Final scope is written on your quote. Nothing below is a published price or a guarantee of licence grant.</p>
            <ul class="mt-6 space-y-3 text-zinc-800">
                <?php foreach ($bundle['includes'] as $item): ?>
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span></li>
                <?php endforeach; ?>
            </ul>
            <h3 class="mt-10 text-xl font-semibold text-black">Related services</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/services/electrical'), ENT_QUOTES, 'UTF-8') ?>">Electrical / EICR</a>
                <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/services/gas-systems'), ENT_QUOTES, 'UTF-8') ?>">Gas systems</a>
                <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/services/fire-risk-assessments'), ENT_QUOTES, 'UTF-8') ?>">Fire risk assessments</a>
            </div>
        </div>
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Optional add-ons</h2>
            <p class="mt-3 text-zinc-600">Only where the FRA, licence condition or existing system requires them — we will not pad the bundle.</p>
            <ul class="mt-6 space-y-4">
                <?php foreach ($bundle['addons'] as $addon):
                    if (!isset($services[$addon['slug']])) {
                        continue;
                    }
                    ?>
                    <li class="bg-white border rounded-2xl p-5">
                        <a href="<?= htmlspecialchars(url('/pages/services/' . $addon['slug']), ENT_QUOTES, 'UTF-8') ?>" class="font-semibold text-black hover:text-[#ff6b00]">
                            <?= htmlspecialchars($addon['label'], ENT_QUOTES, 'UTF-8') ?> →
                        </a>
                        <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars(getServiceBlurb($addon['slug'], true), ENT_QUOTES, 'UTF-8') ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black text-center mb-12">How the package quote works</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($steps as [$n, $t, $d]): ?>
                <div class="text-center px-4">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-[#0a2540] text-white font-bold flex items-center justify-center text-lg"><?= htmlspecialchars($n, ENT_QUOTES, 'UTF-8') ?></div>
                    <h3 class="mt-4 font-semibold text-xl text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-semibold tracking-tight text-black">Stockport, Manchester &amp; Greater Manchester</h2>
    <p class="mt-3 text-zinc-700 max-w-3xl">
        We run this package from 17 Woodlands Park Road, Offerton, Stockport SK2 5DE across Greater Manchester.
        Dedicated topic pages if you only need one certificate in a named town:
    </p>
    <div class="mt-6"><?= hmoLinkChipsHtml([
        ['href' => url('/pages/hmo-eicr/stockport'), 'label' => 'HMO EICR Stockport'],
        ['href' => url('/pages/hmo-eicr/manchester'), 'label' => 'HMO EICR Manchester'],
        ['href' => url('/pages/hmo-fra/stockport'), 'label' => 'HMO FRA Stockport'],
        ['href' => url('/pages/hmo-fra/manchester'), 'label' => 'HMO FRA Manchester'],
        ['href' => url('/pages/hmo-gas-safety/stockport'), 'label' => 'HMO gas Stockport'],
        ['href' => url('/pages/hmo-gas-safety/manchester'), 'label' => 'HMO gas Manchester'],
        ['href' => url('/pages/areas/stockport'), 'label' => 'Stockport area hub'],
        ['href' => url('/pages/areas/manchester'), 'label' => 'Manchester area hub'],
    ]) ?></div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-14">FAQ</h2>
    <div class="mt-6 space-y-4 max-w-3xl">
        <?php foreach ($faqs as $faq): ?>
            <details class="bg-white border rounded-2xl p-5">
                <summary class="font-semibold cursor-pointer text-black"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                <p class="mt-3 text-sm text-zinc-600"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
        <?php endforeach; ?>
    </div>
    <div class="mt-10"><?= shareButtonsHtml($pageTitle, $metaDesc) ?></div>
</section>

<?= hmoQuoteFormHtml($_SESSION['csrf'], 'HMO Compliance Package') ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
