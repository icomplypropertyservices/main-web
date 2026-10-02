<?php
/**
 * Shared renderer for wave-1 fortnight resource articles.
 * Set $WAVE1_SLUG before including.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';
require_once SITE_ROOT . '/includes/wave1.php';

$slug = $WAVE1_SLUG ?? '';
$guide = $slug !== '' ? wave1Guide($slug) : null;
if (!$guide) {
    http_response_code(404);
    require SITE_ROOT . '/404.php';
    return;
}

$pageTitle = $guide['pageTitle'];
$metaDesc = $guide['metaDesc'];
$metaKeywords = $guide['metaKeywords'];
$ogImage = url($guide['ogImage']);
$canonicalUrl = url('/pages/resources/' . $slug);

$home = rtrim(SITE_URL, '/');
$crumbs = [
    ['name' => 'Home', 'item' => $home . '/'],
    ['name' => 'Resources', 'item' => url('/pages/resources')],
    ['name' => $guide['crumb'], 'item' => $canonicalUrl],
];
$jsonLd = [wave1BreadcrumbJsonLd($crumbs)];
$faqLd = wave1FaqJsonLd($guide['faqs'] ?? []);
if ($faqLd) {
    $jsonLd[] = $faqLd;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($home . '/', ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/resources/index.php') ?>" class="hover:text-white">Resources</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($guide['crumb'], ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars($guide['kicker'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                <?= htmlspecialchars($guide['h1'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="text-[#ff6b00]"><?= htmlspecialchars($guide['h1Accent'], ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl"><?= htmlspecialchars($guide['lede'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request a quote</a>
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
    </div>
</section>

<article class="max-w-3xl mx-auto px-6 py-12 md:py-16">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 leading-relaxed mb-10">
        <strong>Not legal advice.</strong> This page is general UK guidance only. Duties vary by tenure, nation and premises type.
        Confirm current requirements for your property and take professional advice where needed.
    </div>

    <div class="prose-like space-y-8 text-black leading-relaxed">
        <?php foreach ($guide['sections'] as $section): ?>
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-3"><?= htmlspecialchars($section['h2'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php foreach ($section['p'] ?? [] as $para): ?>
                <p class="text-zinc-700<?= $para === ($section['p'][0] ?? null) ? '' : ' mt-3' ?>"><?= wave1Rich($para) ?></p>
            <?php endforeach; ?>
            <?php if (!empty($section['ul'])): ?>
            <ul class="space-y-2 text-zinc-700 mt-3">
                <?php foreach ($section['ul'] as $item): ?>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold shrink-0">✓</span> <span><?= wave1Rich($item) ?></span></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php if (!empty($section['note'])): ?>
            <p class="mt-3 text-sm text-zinc-600 bg-zinc-50 border border-zinc-200 rounded-2xl px-4 py-3"><?= wave1Rich($section['note']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($guide['faqs'])): ?>
        <div>
            <h2 class="text-2xl font-semibold tracking-tight mb-4">Common questions</h2>
            <div class="space-y-3">
                <?php foreach ($guide['faqs'] as $faq): ?>
                <details class="border border-zinc-200 rounded-2xl px-5 py-4 bg-white">
                    <summary class="font-semibold cursor-pointer"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                    <p class="mt-3 text-zinc-700"><?= wave1Rich($faq['a']) ?></p>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-10">
        <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
    </div>

    <p class="mt-8 text-sm text-zinc-500">
        <a href="<?= url('/pages/resources/index.php') ?>" class="text-[#ff6b00] hover:underline">← Back to resources</a>
        ·
        <a href="<?= url('/contact.php') ?>" class="text-[#ff6b00] hover:underline">Contact</a>
        ·
        <a href="<?= url('/pages/faq.php') ?>" class="text-[#ff6b00] hover:underline">FAQ</a>
    </p>
</article>

<?= wave1QuoteFormHtml(
    $guide['formService'],
    $guide['formHeading'],
    $guide['formIntro'],
    $guide['formPlaceholder']
) ?>

<section class="max-w-3xl mx-auto px-6 pb-12"><?= resourceRelatedHtml($slug) ?></section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
