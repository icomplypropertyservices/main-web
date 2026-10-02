<?php
/**
 * CAME barrier town page. Vars from renderBarriersTownPage().
 * Draft / non-prod: noindex, and the static export skips /pages/barriers.
 *
 * @var array $town
 * @var string $intro
 * @var string $body
 * @var list<array{q:string,a:string}> $faqs
 * @var list<array<string,mixed>> $nearby
 * @var string $cameImage
 * @var string $csrf
 */
$name = (string)$town['name'];
$slug = (string)$town['slug'];
$region = (string)$town['region'];
$nation = (string)$town['nation'];
$pop = number_format((int)$town['population']);
$year = (int)$town['population_year'];
$pageTitle = 'Automatic barriers in ' . $name;
$metaDesc = 'CAME partner automatic barriers in ' . $name . '. Survey, supply and installation of CAME GARD barriers. Price on application. Population basis: ' . $pop . ' (' . $year . ').';
$metaKeywords = 'automatic barriers ' . $name . ', CAME barriers ' . $name . ', CAME GARD, car park barrier ' . $name . ', barrier installation ' . $region;
$metaRobots = 'noindex, nofollow';
$canonicalUrl = url('/pages/barriers/' . $slug . '.php');
$ogImage = $cameImage !== '' ? $cameImage : url('/assets/images/services/access-control.jpg');
$h = static function ($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-amber-50 border-b border-amber-200">
    <div class="max-w-7xl mx-auto px-6 py-3 text-sm text-amber-950">
        Draft — not published. This barriers page is excluded from the production site and the sitemap.
    </div>
</section>
<section class="relative overflow-hidden bg-[#061828] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/70 mb-4 flex flex-wrap gap-2">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a><span>/</span>
            <a href="<?= url('/pages/barriers/index.php') ?>" class="hover:text-white">Barriers</a><span>/</span>
            <span class="text-white font-semibold"><?= $h($name) ?></span>
        </nav>
        <div class="inline-block px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold uppercase tracking-wider mb-4">
            CAME partner · <?= $h($nation) ?>
        </div>
        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-[1.08]">
            Automatic barriers<br><span class="text-[#ff6b00]">in <?= $h($name) ?></span>
        </h1>
        <p class="mt-5 text-lg text-white/95 max-w-2xl leading-relaxed">
            Survey, supply and installation of CAME automatic barriers for <?= $h($name) ?>.
            Quotes are price on application after we have seen the lane.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white">Request a survey</a>
            <a href="https://wa.me/<?= $h(WHATSAPP) ?>?text=<?= rawurlencode('Automatic barriers in ' . $name) ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 font-bold text-white">WhatsApp</a>
            <a href="tel:<?= $h(preg_replace('/\s+/', '', PHONE)) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold"><?= $h(PHONE) ?></a>
        </div>
    </div>
</section>

<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-12 md:py-16 grid lg:grid-cols-5 gap-10">
        <div class="lg:col-span-3">
            <div class="bg-white border-2 border-zinc-300 rounded-3xl p-6 md:p-8">
                <h2 class="text-2xl font-bold text-[#061828]">CAME barriers for <?= $h($name) ?></h2>
                <p class="mt-4 text-lg text-zinc-900 leading-relaxed"><?= $h($intro) ?></p>
                <p class="mt-4 text-lg text-zinc-900 leading-relaxed"><?= $h($body) ?></p>
                <p class="mt-4 text-sm text-zinc-600"><?= $h($town['population_source']) ?>. <?= $h($region) ?>, <?= $h($nation) ?>.</p>
                <ul class="mt-6 space-y-3 text-zinc-900">
                    <li>CAME GARD automatic barriers, specified to the measured lane</li>
                    <li>Photocells, induction loops and safety edges to BS EN 12453</li>
                    <li>Links to access control, intercom or ANPR only after the fail-state is agreed</li>
                    <li>Repair of existing CAME barriers when they can still be made safe</li>
                    <li>Price on application — no fee is printed on this page</li>
                </ul>
            </div>
        </div>
        <div class="lg:col-span-2 space-y-4">
            <?php if ($cameImage !== ''): ?>
            <div class="rounded-3xl overflow-hidden border-2 border-zinc-300 bg-white">
                <img src="<?= $h($cameImage) ?>" alt="CAME GARD automatic barrier" class="w-full h-56 object-cover" width="640" height="360">
                <div class="bg-[#061828] text-white p-3 text-center font-bold text-sm">CAME GARD · <?= $h($name) ?></div>
            </div>
            <?php endif; ?>
            <div class="bg-white border-2 border-zinc-300 rounded-3xl p-6">
                <h2 class="text-lg font-bold text-[#061828]">Place facts</h2>
                <dl class="mt-4 space-y-2 text-sm text-zinc-800">
                    <div class="flex justify-between gap-4"><dt>Place</dt><dd class="font-semibold text-right"><?= $h($name) ?></dd></div>
                    <div class="flex justify-between gap-4"><dt>Nation</dt><dd class="font-semibold text-right"><?= $h($nation) ?></dd></div>
                    <div class="flex justify-between gap-4"><dt>Region</dt><dd class="font-semibold text-right"><?= $h($region) ?></dd></div>
                    <div class="flex justify-between gap-4"><dt>Population</dt><dd class="font-semibold text-right"><?= $h($pop) ?></dd></div>
                    <div class="flex justify-between gap-4"><dt>Year</dt><dd class="font-semibold text-right"><?= $h((string)$year) ?></dd></div>
                    <?php if (!empty($town['district'])): ?>
                    <div class="flex justify-between gap-4"><dt>Census label</dt><dd class="font-semibold text-right"><?= $h($town['settlement_name']) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>
            <div class="bg-[#061828] text-white rounded-3xl p-6">
                <h2 class="text-xl font-bold">Related</h2>
                <a class="block mt-3 font-semibold text-[#ff6b00] hover:underline" href="<?= url('/pages/services/access-control.php') ?>">Access control</a>
                <a class="block mt-2 font-semibold text-[#ff6b00] hover:underline" href="<?= url('/shop/security/') ?>">CAME security supplies</a>
                <a class="block mt-2 font-semibold text-[#ff6b00] hover:underline" href="<?= url('/pages/keywords/car-park-barrier-access.php') ?>">Car park barrier access</a>
            </div>
        </div>
    </div>
</section>

<?php if ($nearby): ?>
<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-xl font-bold text-[#061828]">Automatic barriers nearby</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($nearby as $other): ?>
            <a href="<?= url('/pages/barriers/' . rawurlencode((string)$other['slug']) . '.php') ?>" class="px-4 py-2 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00]">
                <?= $h($other['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="bg-zinc-100">
    <div class="max-w-3xl mx-auto px-6 py-12">
        <h2 class="text-2xl font-bold text-[#061828]">Questions about barriers in <?= $h($name) ?></h2>
        <div class="mt-6 space-y-4">
            <?php foreach ($faqs as $faq): ?>
            <div class="bg-white border-2 border-zinc-300 rounded-2xl p-5">
                <h3 class="font-bold text-[#061828]"><?= $h($faq['q']) ?></h3>
                <p class="mt-2 text-zinc-800 leading-relaxed"><?= $h($faq['a']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="quote" class="bg-white border-t-2 border-zinc-200">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-[#061828] text-center">Survey request — <?= $h($name) ?></h2>
        <p class="mt-3 text-center text-zinc-700">Tell us the postcode, the lane width and whether a barrier is already there. The quote is price on application.</p>
        <form action="<?= url('/contact.php') ?>" method="POST" class="mt-8 bg-zinc-50 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4">
            <input type="hidden" name="csrf" value="<?= $h($csrf) ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl">
                <input type="text" name="service" value="<?= $h('Automatic barriers in ' . $name) ?>" readonly class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl bg-white">
            </div>
            <textarea name="message" rows="4" required placeholder="<?= $h($name . ' postcode, lane width, CAME or other brand…') ?>" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
