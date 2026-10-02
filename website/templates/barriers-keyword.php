<?php
/**
 * National barrier keyword article. Expects $slug, $meta, $brands, $samples.
 */
$name = (string)($meta['name'] ?? keywordDisplayName($slug));
$intro = (string)($meta['intro'] ?? '');
$body = (string)($meta['body'] ?? '');
$points = $meta['focus_points'] ?? [];
$faqs = $meta['faq'] ?? [];
$relatedSlug = keywordSlug((string)($meta['related'] ?? 'came-partner'));
$relatedMeta = getMajorKeywords()[$relatedSlug] ?? [];
$relatedName = (string)($relatedMeta['name'] ?? keywordDisplayName($relatedSlug));
$pageTitle = $name . ' | Vehicle Barriers';
$metaDesc = (string)($meta['meta_desc'] ?? ($name . '. CAME partner. Price on application. ' . PHONE . '.'));
$metaKeywords = (string)($meta['seo_keywords'] ?? $name);
$canonicalUrl = url('/pages/keywords/' . $slug);
$ogImage = url('/assets/images/services/access-control.jpg');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$faqEntities = [];
foreach ($faqs as $faq) {
    if (!is_array($faq) || count($faq) < 2) {
        continue;
    }
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => (string)$faq[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string)$faq[1]],
    ];
}

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Article',
            'headline' => $name,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'author' => ['@type' => 'Organization', 'name' => SITE_NAME],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => $faqEntities,
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="relative overflow-hidden bg-[#061828] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/70 mb-5 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/barriers') ?>" class="hover:text-white">Vehicle barriers</a>
            <span>/</span>
            <span><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">Barrier guide</div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight max-w-3xl"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="mt-5 text-lg text-white/85 max-w-3xl leading-relaxed"><?= htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-semibold">Price on application</a>
            <a href="<?= url('/pages/manufacturers/came') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-semibold">CAME partner</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl border border-white/30 font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
        <div><div class="font-semibold">CAME partner</div><div class="text-zinc-600">The only barrier partnership stated</div></div>
        <div><div class="font-semibold">Price on application</div><div class="text-zinc-600">No catalogue fee on this guide</div></div>
        <div><div class="font-semibold">Stockport SK2</div><div class="text-zinc-600">Distant towns are planned visits</div></div>
        <div><div class="font-semibold">Private land</div><div class="text-zinc-600">Not a public-highway product</div></div>
    </div>
</section>

<section class="bg-zinc-50">
    <div class="max-w-7xl mx-auto px-6 py-14 grid lg:grid-cols-5 gap-10">
        <article class="lg:col-span-3 bg-white border rounded-3xl p-6 md:p-8">
            <h2 class="text-2xl font-semibold">What this covers</h2>
            <p class="mt-4 text-lg text-zinc-800 leading-relaxed"><?= htmlspecialchars($body, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($points): ?>
                <ul class="mt-6 space-y-2">
                    <?php foreach ($points as $point): ?>
                        <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">●</span><span><?= htmlspecialchars((string)$point, ENT_QUOTES, 'UTF-8') ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p class="mt-6 text-sm">
                <a class="font-semibold text-[#ff6b00]" href="<?= url('/pages/services/barriers') ?>">All barrier towns</a>
                <span class="text-zinc-400"> · </span>
                <a class="font-semibold text-[#ff6b00]" href="<?= url('/pages/keywords/' . $relatedSlug) ?>"><?= htmlspecialchars($relatedName, ENT_QUOTES, 'UTF-8') ?></a>
            </p>
        </article>
        <aside class="lg:col-span-2 space-y-4">
            <div class="bg-[#0B1F3A] text-white rounded-3xl p-6">
                <h2 class="font-semibold">CAME, specifically</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a class="underline" href="<?= url('/pages/manufacturers/came') ?>">CAME manufacturer page</a></li>
                    <li><a class="underline" href="<?= url('/pages/keywords/came-partner') ?>">Partnership</a></li>
                    <li><a class="underline" href="<?= url('/pages/keywords/came-gard-barrier') ?>">Gard barrier</a></li>
                    <li><a class="underline" href="<?= url('/pages/keywords/came-gard-pt') ?>">Gard PT</a></li>
                </ul>
            </div>
            <?php if ($samples): ?>
                <div class="border rounded-3xl p-6 bg-white">
                    <h2 class="font-semibold">Town pages</h2>
                    <p class="mt-2 text-sm text-zinc-600">Each link is that town's own census or NRS profile, not a copy of this article.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <?php foreach ($samples as $sample): ?>
                            <a class="px-3 py-1.5 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/barriers/' . $sample['slug']) ?>"><?= htmlspecialchars((string)$sample['name'], ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php if ($faqs): ?>
<section class="max-w-3xl mx-auto px-6 py-12 space-y-3">
    <h2 class="text-2xl font-semibold mb-4">Questions</h2>
    <?php foreach ($faqs as $faq): ?>
        <?php if (!is_array($faq) || count($faq) < 2) continue; ?>
        <details class="bg-white border rounded-2xl p-5">
            <summary class="font-semibold cursor-pointer"><?= htmlspecialchars((string)$faq[0], ENT_QUOTES, 'UTF-8') ?></summary>
            <p class="mt-3 text-sm text-zinc-700 leading-relaxed"><?= htmlspecialchars((string)$faq[1], ENT_QUOTES, 'UTF-8') ?></p>
        </details>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-xl font-semibold">Manufacturers on the hub</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($brands as $brandSlug => $entry): ?>
                <a class="px-3 py-1.5 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/manufacturers/' . $brandSlug) ?>"><?= htmlspecialchars((string)$entry['name'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="quote" class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <?php
        $services = getServices();
        $selectedService = 'Vehicle Barriers';
        $heading = $name . ' — quote on application';
        $sub = 'No published fee. Call ' . PHONE . ' or send the lane details.';
        require SITE_ROOT . '/includes/quote-form.php';
        ?>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
