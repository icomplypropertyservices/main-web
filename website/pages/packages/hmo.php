<?php
/**
 * HMO Packages hub — three variants + twelve quality hubs.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
require_once SITE_ROOT . '/includes/partials.php';
require_once SITE_ROOT . '/includes/share.php';

hmoEnsureSession();

$pageTitle = 'HMO Packages | EICR, Gas Safety & FRA Greater Manchester';
$metaDesc = 'HMO packages for Stockport, Manchester and Greater Manchester — compliance (EICR + gas + FRA), fire safety, or occupancy / re-let. Optional fire alarms, emergency lighting and fire doors. POA after scope.';
$metaKeywords = 'HMO packages, HMO compliance package, HMO fire safety pack, HMO occupancy pack, HMO landlord Stockport, HMO licence compliance Manchester';
$ogImage = url('/assets/images/services/fire-risk-assessments.jpg');
$canonicalUrl = url('/pages/packages/hmo');

$variants = hmoPackageVariants();
$hubs = hmoQualityHubs();
$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$wa = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode('Hi Icomply, I need an HMO package quote');

$crumbs = [
    ['name' => 'Home', 'href' => $homeUrl, 'url' => $homeUrl],
    ['name' => 'Packages', 'href' => url('/pages/packages'), 'url' => url('/pages/packages')],
    ['name' => 'HMO packages', 'href' => $canonicalUrl, 'url' => $canonicalUrl, 'current' => true],
];

$topics = [
    [
        'href' => url('/pages/hmo-eicr'),
        'title' => 'HMO EICR',
        'blurb' => 'Electrical installation condition reports scoped for shared houses and licensed HMOs.',
        'img' => url('/assets/images/services/electrical.jpg'),
    ],
    [
        'href' => url('/pages/hmo-fra'),
        'title' => 'HMO fire risk assessment',
        'blurb' => 'Suitable and sufficient FRA with a prioritised action plan — not a licence decision.',
        'img' => url('/assets/images/services/fire-risk-assessments.jpg'),
    ],
    [
        'href' => url('/pages/hmo-gas-safety'),
        'title' => 'HMO gas safety',
        'blurb' => 'Annual landlord gas safety records for shared boilers and multiple cookers.',
        'img' => url('/assets/images/services/gas-systems.jpg'),
    ],
    [
        'href' => url('/pages/hmo-fire-alarms'),
        'title' => 'HMO fire alarms',
        'blurb' => 'Detection and warning as the FRA or licence condition actually requires.',
        'img' => url('/assets/images/services/fire-alarms.jpg'),
    ],
    [
        'href' => url('/pages/hmo-emergency-lighting'),
        'title' => 'HMO emergency lighting',
        'blurb' => 'Escape-route lighting where the house needs it — not a system for every terrace.',
        'img' => url('/assets/images/services/emergency-lighting.jpg'),
    ],
    [
        'href' => url('/pages/hmo-fire-doors'),
        'title' => 'HMO fire doors',
        'blurb' => 'Doorset survey and upgrades after a walk-through, not a catalogue FD30 for every opening.',
        'img' => url('/assets/images/services/fire-doors.jpg'),
    ],
];

$faqs = array_merge([
    [
        'q' => 'Who are HMO packages for?',
        'a' => 'Private landlords, letting agents and small portfolios running licensed or licensable houses in multiple occupation in Greater Manchester. Single-let landlords are usually better on the Let Ready or Landlord Essentials packages.',
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
                HMO landlords · Stockport SK2
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                HMO packages<br>
                <span class="text-[#ff6b00]">for Greater Manchester</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Three honest variants — core certificates, fire-focused life safety, or a re-let occupancy file —
                then add only the work the house actually needs. No invented catalogue prices.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#variants" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Compare packs</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">Free quote</a>
                <a href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
            <p class="mt-5 text-sm text-white/60">Pricing is <strong class="text-white">POA</strong>. We do not publish invented catalogue prices. Not legal advice.</p>
        </div>
    </div>
</section>

<?= sectionTrustStrip([
    ['Three variants', 'Compliance · fire safety · occupancy'],
    ['Honest pricing', 'POA — fixed quote after scope'],
    ['Local engineers', 'Offerton, Stockport SK2 5DE'],
    ['Optional add-ons', 'Alarms, lighting and fire doors'],
]) ?>

<section id="variants" class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">The variants</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Pick the pack that matches the house</h2>
            <p class="mt-2 text-zinc-600 max-w-2xl">Final inclusions are confirmed on your quote. We will not sell you a fire-door pack if the FRA is already in date and the doors are sound.</p>
        </div>
        <a href="<?= htmlspecialchars(url('/pages/hmo-landlords'), ENT_QUOTES, 'UTF-8') ?>" class="text-sm font-semibold text-[#ff6b00]">HMO landlords hub →</a>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <?php foreach ($variants as $variant): ?>
            <article class="bg-white border <?= !empty($variant['highlight']) ? 'border-[#ff6b00] ring-1 ring-[#ff6b00]/20 shadow-lg' : 'border-zinc-200' ?> rounded-3xl p-6 md:p-8 flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <span class="inline-block text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full <?= !empty($variant['highlight']) ? 'bg-[#ff6b00] text-white' : 'bg-zinc-100 text-zinc-700' ?>"><?= htmlspecialchars($variant['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="text-right shrink-0">
                        <div class="text-xs uppercase tracking-wider text-zinc-500">From</div>
                        <div class="text-xl font-semibold text-[#0B1F3A]"><?= htmlspecialchars($variant['price'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
                <h3 class="text-2xl font-semibold tracking-tight text-black mt-4"><?= htmlspecialchars($variant['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-zinc-600 text-sm flex-1"><?= htmlspecialchars($variant['tagline'], ENT_QUOTES, 'UTF-8') ?></p>
                <ul class="mt-5 space-y-2 text-sm text-zinc-800">
                    <?php foreach (array_slice($variant['includes'], 0, 4) as $item): ?>
                        <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span><span><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="<?= htmlspecialchars(url($variant['path']), ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-2xl <?= !empty($variant['highlight']) ? 'bg-[#ff6b00] hover:bg-orange-600 text-white' : 'border border-zinc-200 text-black hover:border-[#0B1F3A]' ?> font-semibold text-sm">Package details</a>
                    <a href="#quote" class="px-5 py-2.5 rounded-2xl border border-zinc-200 font-semibold text-sm text-black hover:border-[#0B1F3A]">Quote</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">High-intent guides</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Certificates and fire topics</h2>
        <p class="mt-2 text-zinc-600 max-w-2xl">Standalone hubs if you only need one job. Stockport and Manchester landings stay on EICR, FRA and gas only.</p>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
            <?php foreach ($topics as $topic): ?>
                <a href="<?= htmlspecialchars($topic['href'], ENT_QUOTES, 'UTF-8') ?>" class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                    <div class="h-40 bg-zinc-100 overflow-hidden">
                        <img src="<?= htmlspecialchars($topic['img'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8') ?> — Icomply Property Services" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="font-semibold text-xl text-black"><?= htmlspecialchars($topic['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($topic['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                        <span class="mt-4 text-sm font-semibold text-[#ff6b00]">Read guide →</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-8 flex flex-wrap gap-2">
            <a class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/hmo-eicr/stockport'), ENT_QUOTES, 'UTF-8') ?>">HMO EICR Stockport</a>
            <a class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/hmo-eicr/manchester'), ENT_QUOTES, 'UTF-8') ?>">HMO EICR Manchester</a>
            <a class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/hmo-fra/stockport'), ENT_QUOTES, 'UTF-8') ?>">HMO FRA Stockport</a>
            <a class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/hmo-fra/manchester'), ENT_QUOTES, 'UTF-8') ?>">HMO FRA Manchester</a>
            <a class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/hmo-gas-safety/stockport'), ENT_QUOTES, 'UTF-8') ?>">HMO gas Stockport</a>
            <a class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= htmlspecialchars(url('/pages/hmo-gas-safety/manchester'), ENT_QUOTES, 'UTF-8') ?>">HMO gas Manchester</a>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Quality over spam</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Twelve HMO hubs</h2>
    <p class="mt-2 text-zinc-600 max-w-2xl">The pages we stand behind. No keyword × town matrix.</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-10">
        <?php foreach ($hubs as $hub): ?>
            <a href="<?= htmlspecialchars(url($hub['path']), ENT_QUOTES, 'UTF-8') ?>" class="bg-white border rounded-2xl p-5 hover:border-[#ff6b00] transition">
                <h3 class="font-semibold text-black"><?= htmlspecialchars($hub['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-1"><?= htmlspecialchars($hub['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid lg:grid-cols-2 gap-12">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-black">Licence paperwork vs our work</h2>
                <p class="mt-4 text-zinc-700 leading-relaxed">
                    An HMO licence is granted by the local housing authority. We do not issue licences and we do not claim that a package “guarantees” approval.
                    What we do is the practical safety work and certificates that licence applications and inspections commonly ask for.
                    Use the
                    <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars(url('/pages/resources/hmo-licence-compliance-checklist'), ENT_QUOTES, 'UTF-8') ?>">HMO licence compliance checklist</a>
                    as a conversation starter — it is not legal advice.
                </p>
                <div class="mt-6"><?= hmoLinkChipsHtml([
                    ['href' => url('/pages/resources/hmo-licence-compliance-checklist'), 'label' => 'HMO licence checklist'],
                    ['href' => url('/pages/hmo-landlords'), 'label' => 'HMO landlords'],
                    ['href' => url('/pages/landlords'), 'label' => 'Landlord services'],
                    ['href' => url('/pages/packages/let-ready'), 'label' => 'Let Ready (single lets)'],
                    ['href' => url('/pages/packages'), 'label' => 'All packages'],
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
    </div>
</section>

<?= hmoQuoteFormHtml($_SESSION['csrf'], 'HMO Compliance Package') ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
