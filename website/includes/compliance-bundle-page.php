<?php
/**
 * Shared conversion page for the Compliance Bundle and the landlord-pack alias.
 * Set $COMPLIANCE_BUNDLE_VARIANT to 'bundle' or 'landlord' before including.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}
require_once SITE_ROOT . '/includes/compliance-bundle.php';
require_once SITE_ROOT . '/includes/share.php';

$bundle = icomplyComplianceBundle();
$variant = $COMPLIANCE_BUNDLE_VARIANT ?? 'bundle';
$isLandlord = $variant === 'landlord';

$canonicalUrl = url($bundle['canonical_path']);
$pageTitle = $isLandlord
    ? ($bundle['alias'] . ' | ' . $bundle['name'] . ' ' . $bundle['price_label'])
    : ($bundle['name'] . ' | ' . $bundle['alias'] . ' ' . $bundle['price_label']);
$metaDesc = $bundle['name'] . ' (' . strtolower($bundle['alias']) . ') at ' . $bundle['price_label']
    . ' for one residential property: EICR, landlord gas safety record and fire risk assessment, in one file. North West. Extras outside the published scope are quoted separately.';
$metaKeywords = 'compliance bundle, landlord pack, landlord compliance pack ' . $bundle['price_label']
    . ', EICR gas FRA bundle, landlord certificates North West, iComply';
$ogImage = url('/assets/images/services/landlord-compliance.jpg');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$waText = 'Hi iComply, I want the ' . $bundle['name'] . ' (' . $bundle['alias'] . ') at ' . $bundle['price_label'];
$waHref = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($waText);
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$h1 = $isLandlord ? $bundle['alias'] : $bundle['name'];
$h1Accent = $isLandlord ? $bundle['name'] . ' · ' . $bundle['price_label'] : $bundle['alias'] . ' · ' . $bundle['price_label'];
$jsonLd = icomplyComplianceBundleOfferJsonLd($canonicalUrl);

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/packages.php') ?>" class="hover:text-white">Packages</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="grid lg:grid-cols-[1.4fr_0.8fr] gap-10 items-end">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                    Published pack · one residential property
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                    <?= htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') ?><br>
                    <span class="text-[#ff6b00]"><?= htmlspecialchars($h1Accent, ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl"><?= htmlspecialchars($bundle['scope'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Book this pack</a>
                    <a href="#included" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">What’s included</a>
                    <a href="<?= htmlspecialchars($waHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
                </div>
            </div>
            <div class="rounded-3xl border border-white/15 bg-white/5 p-8">
                <div class="text-xs uppercase tracking-[3px] text-white/60">Published price</div>
                <div class="mt-2 text-6xl font-semibold tracking-tight" data-compliance-bundle-price><?= htmlspecialchars($bundle['price_label'], ENT_QUOTES, 'UTF-8') ?></div>
                <p class="mt-3 text-sm text-white/70">For the pack on this page. VAT is itemised on the written confirmation. Properties outside the scope are quoted before anyone attends.</p>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="inline-block mt-6 font-semibold text-[#ff6b00]">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-3 gap-6">
        <?php
        $trust = [
            ['EICR', 'Fixed installation, coded report'],
            ['Gas safety record', 'Where gas appliances are present'],
            ['Fire risk assessment', 'Same address, same file'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div>
                <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section id="included" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">In the <?= htmlspecialchars($bundle['price_label'], ENT_QUOTES, 'UTF-8') ?></div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">What the pack covers</h2>
            <ul class="mt-6 space-y-3 text-zinc-800">
                <?php foreach ($bundle['includes'] as $item): ?>
                    <li class="flex gap-3"><span class="text-[#ff6b00] font-bold">✓</span> <span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-3xl p-8">
            <h2 class="text-xl font-semibold text-black">Quoted separately</h2>
            <ul class="mt-5 space-y-3 text-zinc-700">
                <?php foreach ($bundle['excludes'] as $item): ?>
                    <li class="flex gap-3"><span class="text-[#0B1F3A] font-bold">–</span> <span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span></li>
                <?php endforeach; ?>
            </ul>
            <p class="mt-6 text-sm text-zinc-600">If the address does not fit the published scope, say so on the form. We confirm the difference in writing before the visit.</p>
        </div>
    </div>
    <div class="mt-10 flex flex-wrap gap-2">
        <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/services/electrical.php') ?>">EICR</a>
        <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/services/gas-systems.php') ?>">Gas safety</a>
        <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/services/fire-risk-assessments.php') ?>">Fire risk assessment</a>
        <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/landlords.php') ?>">Landlords</a>
    </div>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">How booking works</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Three steps, then the visit</h2>
        <div class="grid md:grid-cols-3 gap-6 mt-10">
            <?php
            $steps = [
                ['Send the address', 'Property type, postcode, whether gas is present, and access notes.'],
                ['We confirm the pack', 'If it matches the published scope, the price stays ' . $bundle['price_label'] . '. If it does not, you get a written difference before anyone attends.'],
                ['One file back', 'EICR, gas safety record and fire risk assessment issued for that address.'],
            ];
            $n = 1;
            foreach ($steps as [$title, $text]): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-7">
                <div class="text-[#ff6b00] font-semibold text-sm">Step <?= $n++ ?></div>
                <h3 class="font-semibold text-xl text-black mt-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-3"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-semibold tracking-tight text-black">Questions before you book</h2>
    <div class="mt-8 space-y-3">
        <?php
        $faqs = [
            ['Is ' . $bundle['price_label'] . ' the price of an EICR, a gas record, or an FRA on its own?', 'No. Those visits are quoted to the property. ' . $bundle['price_label'] . ' is only this pack: all three, for one residential address inside the published scope.'],
            ['What if there is no gas?', 'Tell us on the form. A property with no gas appliances is outside the published pack, and we quote the EICR and FRA without pretending the gas record was included.'],
            ['Do remedials come in the price?', 'No. C1/C2 electrical items, unsafe appliances and actions from the fire risk assessment are quoted separately before that work starts.'],
            ['Is this the Let Ready package?', 'No. Let Ready stays price on application and can include other certificates. This page is the only published ' . $bundle['price_label'] . ' pack.'],
        ];
        foreach ($faqs as [$q, $a]): ?>
        <details class="border border-zinc-200 rounded-2xl px-5 py-4 bg-white">
            <summary class="font-semibold cursor-pointer"><?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?></summary>
            <p class="mt-3 text-zinc-700"><?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?></p>
        </details>
        <?php endforeach; ?>
    </div>
</section>

<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <?php
        $services = [ $bundle['id'] => $bundle['service_value'] ] + getServices();
        $selectedService = $bundle['service_value'];
        $heading = 'Book the ' . $bundle['name'];
        $sub = 'Address, property type, and whether gas is on site. We confirm ' . $bundle['price_label'] . ' or a written difference before the visit.';
        require SITE_ROOT . '/includes/quote-form.php';
        ?>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
