<?php
/**
 * Shared renderer for HMO package variants.
 * Caller sets $HMO_VARIANT (array from hmoPackageVariants()).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
require_once SITE_ROOT . '/includes/partials.php';
require_once SITE_ROOT . '/includes/share.php';

if (empty($HMO_VARIANT) || !is_array($HMO_VARIANT)) {
    http_response_code(404);
    require SITE_ROOT . '/404.php';
    return;
}

$v = $HMO_VARIANT;
hmoEnsureSession();

$pageTitle = $v['pageTitle'];
$metaDesc = $v['metaDesc'];
$metaKeywords = $v['metaKeywords'] ?? 'HMO packages Greater Manchester, HMO compliance, HMO fire safety, HMO occupancy';
$ogImage = $v['ogImage'];
$canonicalUrl = url($v['path']);

$services = getServices();
$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$wa = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode('Hi Icomply, I need a quote for the ' . $v['name']);

$crumbs = [
    ['name' => 'Home', 'href' => $homeUrl, 'url' => $homeUrl],
    ['name' => 'Packages', 'href' => url('/pages/packages'), 'url' => url('/pages/packages')],
    ['name' => 'HMO packages', 'href' => url('/pages/packages/hmo'), 'url' => url('/pages/packages/hmo')],
    ['name' => $v['name'], 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true],
];

$steps = [
    ['1', 'Send the house', 'Address, storeys, number of lets, last certificate dates and whether a licence is in force or applied for.'],
    ['2', 'We scope & quote', 'POA until we confirm what is fitted. Then a fixed-price quote — no invented online price.'],
    ['3', 'Attend & document', 'Coordinated visits, certificates and a single pack for your agent or licence file.'],
];

$faqs = array_merge(hmoCommonFaqs(), $v['faqs'] ?? []);
$otherVariants = array_filter(hmoPackageVariants(), static fn($item) => ($item['id'] ?? '') !== ($v['id'] ?? ''));

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
                <?= htmlspecialchars($v['heroBadge'] ?? $v['badge'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                <?= htmlspecialchars($v['h1'] ?? $v['name'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="text-[#ff6b00]"><?= htmlspecialchars($v['h1Accent'] ?? 'package', ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl"><?= htmlspecialchars($v['intro'], ENT_QUOTES, 'UTF-8') ?></p>
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
    [$v['short'], $v['tagline']],
    ['POA only', 'Fixed price once we see the house'],
    ['Add-ons optional', 'Only if the house needs them'],
    ['Stockport based', 'Greater Manchester coverage'],
]) ?>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">What is included (indicative)</h2>
            <p class="mt-3 text-zinc-600"><?= htmlspecialchars($v['ideal'], ENT_QUOTES, 'UTF-8') ?> Final scope is written on your quote. Nothing below is a published price or a guarantee of licence grant.</p>
            <ul class="mt-6 space-y-3 text-zinc-800">
                <?php foreach ($v['includes'] as $item): ?>
                    <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span></li>
                <?php endforeach; ?>
            </ul>
            <?php if (!empty($v['not_included'])): ?>
                <h3 class="mt-10 text-xl font-semibold text-black">Not included</h3>
                <ul class="mt-4 space-y-2 text-zinc-700">
                    <?php foreach ($v['not_included'] as $item): ?>
                        <li class="flex gap-2"><span class="text-zinc-400 shrink-0">–</span><span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($v['service_slugs'])): ?>
                <h3 class="mt-10 text-xl font-semibold text-black">Related services</h3>
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($v['service_slugs'] as $slug):
                        if (!isset($services[$slug])) {
                            continue;
                        }
                        ?>
                        <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/services/' . $slug), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($services[$slug], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Optional add-ons</h2>
            <p class="mt-3 text-zinc-600">Only where the FRA, licence condition or existing system requires them — we will not pad the bundle.</p>
            <ul class="mt-6 space-y-4">
                <?php foreach ($v['addons'] as $addon):
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
    <h2 class="text-3xl font-semibold tracking-tight text-black">Other HMO variants</h2>
    <p class="mt-3 text-zinc-600 max-w-3xl">Pick the pack that matches the certificates that are actually due. All three are POA.</p>
    <div class="grid md:grid-cols-2 gap-6 mt-8">
        <?php foreach ($otherVariants as $other): ?>
            <a href="<?= htmlspecialchars(url($other['path']), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border rounded-3xl p-6 hover:border-[#ff6b00] hover:shadow-lg transition">
                <span class="inline-block text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-zinc-100 text-zinc-700"><?= htmlspecialchars($other['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                <h3 class="text-xl font-semibold text-black mt-3"><?= htmlspecialchars($other['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($other['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]">View pack →</span>
            </a>
        <?php endforeach; ?>
    </div>

    <h2 class="text-3xl font-semibold tracking-tight text-black mt-14">Stockport, Manchester &amp; Greater Manchester</h2>
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
        ['href' => url('/pages/hmo-landlords'), 'label' => 'HMO landlords hub'],
        ['href' => url('/pages/areas/stockport'), 'label' => 'Stockport area hub'],
        ['href' => url('/pages/areas/manchester'), 'label' => 'Manchester area hub'],
    ]) ?></div>

    <h2 class="text-3xl font-semibold tracking-tight text-black mt-14">Twelve HMO hubs</h2>
    <p class="mt-3 text-zinc-600">Quality pages only — no keyword × town doorway set.</p>
    <div class="mt-6"><?= hmoHubChipsHtml() ?></div>

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

<?= hmoQuoteFormHtml($_SESSION['csrf'], $v['defaultService']) ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
