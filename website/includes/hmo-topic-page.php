<?php
/**
 * Shared renderer for HMO topic + area landings.
 * Caller sets $HMO_PAGE (array from hmoTopicLandings / hmoAreaTopicLandings).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
require_once SITE_ROOT . '/includes/share.php';

if (empty($HMO_PAGE) || !is_array($HMO_PAGE)) {
    http_response_code(404);
    require SITE_ROOT . '/404.php';
    return;
}

$p = $HMO_PAGE;
hmoEnsureSession();

$pageTitle = $p['pageTitle'];
$metaDesc = $p['metaDesc'];
$metaKeywords = $p['metaKeywords'] ?? 'HMO compliance Greater Manchester, HMO EICR, HMO FRA, HMO gas safety';
$ogImage = $p['ogImage'];
$canonicalUrl = url($p['path']);

$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$wa = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode('Hi Icomply, I need a quote for ' . $p['h1']);
$areaName = $p['areaName'] ?? 'Greater Manchester';
$isArea = isset($p['areaName']);

$crumbs = [
    ['name' => 'Home', 'href' => $homeUrl, 'url' => $homeUrl],
    ['name' => 'HMO packages', 'href' => url('/pages/packages/hmo'), 'url' => url('/pages/packages/hmo')],
];
if ($isArea) {
    $parent = hmoTopicLandings()[$p['parentTopic']] ?? null;
    if ($parent) {
        $crumbs[] = ['name' => $parent['h1'], 'href' => url($parent['path']), 'url' => url($parent['path'])];
    }
    $crumbs[] = ['name' => $areaName, 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true];
} else {
    $crumbs[] = ['name' => $p['h1'], 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true];
}

require SITE_ROOT . '/includes/header.php';
echo hmoBreadcrumbJsonLd($crumbs);
echo hmoFaqJsonLd($p['faqs']);
?>

<section class="relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <?= hmoHeroBreadcrumbs($crumbs) ?>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars($p['badge'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                <?= htmlspecialchars($p['h1'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="text-[#ff6b00]"><?= htmlspecialchars($p['h1Accent'], ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl"><?= htmlspecialchars($p['intro'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request quote</a>
                <a href="<?= htmlspecialchars(url('/pages/packages/hmo-compliance'), ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">HMO package</a>
                <a href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
            <p class="mt-5 text-sm text-white/60">Price: <strong class="text-white">POA</strong> — fixed quote after we agree scope. Not a licence decision or legal advice.</p>
        </div>
    </div>
</section>

<?php
require_once SITE_ROOT . '/includes/partials.php';
echo sectionTrustStrip([
    ['POA then fixed quote', 'No catalogue prices — scope first'],
    ['Stockport SK2 base', 'Greater Manchester & North West cover'],
    ['One documentation pack', 'For agents, licence files and insurers'],
    ['Add-ons on request', 'Alarms, lighting and fire doors if needed'],
]);
?>

<article class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-3 gap-12">
        <div class="lg:col-span-2 space-y-6 text-zinc-700 leading-relaxed">
            <?php if (!empty($p['stock'])): ?>
                <p>Typical stock we see in <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($p['stock'], ENT_QUOTES, 'UTF-8') ?>.</p>
            <?php endif; ?>
            <?php foreach ($p['body'] as $para): ?>
                <p><?= htmlspecialchars($para, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>

            <h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-black pt-4">What we check</h2>
            <ul class="space-y-2">
                <?php foreach ($p['points'] as $point): ?>
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></span></li>
                <?php endforeach; ?>
            </ul>

            <h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-black pt-4">Bundle with an HMO package</h2>
            <p>
                If several certificates fall due together, pick a variant:
                <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/packages/hmo-compliance'), ENT_QUOTES, 'UTF-8') ?>">HMO Compliance</a>
                (EICR + gas + FRA),
                <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/packages/hmo-fire-safety'), ENT_QUOTES, 'UTF-8') ?>">HMO Fire Safety</a>
                (FRA plus life-safety work), or
                <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/packages/hmo-occupancy'), ENT_QUOTES, 'UTF-8') ?>">HMO Occupancy</a>
                (re-let file). Add
                <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/hmo-emergency-lighting'), ENT_QUOTES, 'UTF-8') ?>">emergency lighting</a>,
                <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/hmo-fire-alarms'), ENT_QUOTES, 'UTF-8') ?>">fire alarms</a>
                or
                <a class="text-[#ff6b00] hover:underline" href="<?= htmlspecialchars(url('/pages/hmo-fire-doors'), ENT_QUOTES, 'UTF-8') ?>">fire doors</a>
                only where the house needs them.
            </p>

            <h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-black pt-4">Frequently asked questions</h2>
            <div class="space-y-4">
                <?php foreach ($p['faqs'] as $faq): ?>
                    <details class="bg-white border rounded-2xl p-5">
                        <summary class="font-semibold cursor-pointer text-black"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                        <p class="mt-3 text-sm"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
        <aside class="space-y-6">
            <div class="bg-[#0B1F3A] text-white rounded-3xl p-7">
                <h2 class="text-xl font-semibold">Talk to Stockport</h2>
                <p class="mt-2 text-white/80 text-sm">17 Woodlands Park Road, Offerton, Stockport SK2 5DE</p>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="block mt-4 font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="block text-white/80 text-sm break-all"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="#quote" class="inline-block mt-6 px-5 py-3 rounded-2xl bg-[#ff6b00] font-semibold">Free quote</a>
            </div>
            <div class="bg-white border rounded-3xl p-7">
                <h2 class="text-lg font-semibold text-black">Related HMO pages</h2>
                <div class="mt-4"><?= hmoLinkChipsHtml($p['areaLinks']) ?></div>
            </div>
        </aside>
    </div>

    <?php if (!empty($p['towns'])): ?>
    <div class="mt-16">
        <h2 class="text-2xl font-semibold tracking-tight text-black mb-4">Coverage</h2>
        <p class="text-zinc-600 mb-4">HMO work across <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> and neighbouring Greater Manchester towns. Pick a local hub or area page.</p>
        <?= hmoLinkChipsHtml($p['towns']) ?>
    </div>
    <?php endif; ?>

    <div class="mt-12">
        <h2 class="text-2xl font-semibold tracking-tight text-black mb-4">More HMO &amp; landlord links</h2>
        <?= hmoLinkChipsHtml() ?>
    </div>

    <div class="mt-10"><?= shareButtonsHtml($pageTitle, $metaDesc) ?></div>
</article>

<?= hmoQuoteFormHtml($_SESSION['csrf'], $p['defaultService']) ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
