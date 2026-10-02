<?php
/**
 * Shared renderer for wave-1 quality SEO hubs (not doorway pages).
 * Set $WAVE1_HUB before including.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/wave1.php';

$slug = $WAVE1_HUB ?? '';
$hub = $slug !== '' ? wave1Hub($slug) : null;
if (!$hub) {
    http_response_code(404);
    require SITE_ROOT . '/404.php';
    return;
}

$pageTitle = $hub['pageTitle'];
$metaDesc = $hub['metaDesc'];
$metaKeywords = $hub['metaKeywords'];
$ogImage = url($hub['ogImage']);
$canonicalUrl = url('/pages/' . $slug);

$home = rtrim(SITE_URL, '/');
$crumbs = [
    ['name' => 'Home', 'item' => $home . '/'],
    ['name' => $hub['crumb'], 'item' => $canonicalUrl],
];
$jsonLd = [wave1BreadcrumbJsonLd($crumbs)];
$faqLd = wave1FaqJsonLd($hub['faqs'] ?? []);
if ($faqLd) {
    $jsonLd[] = $faqLd;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$guides = wave1FortnightGuides();
require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($home . '/', ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($hub['crumb'], ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars($hub['kicker'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                <?= htmlspecialchars($hub['h1'], ENT_QUOTES, 'UTF-8') ?><br>
                <span class="text-[#ff6b00]"><?= htmlspecialchars($hub['h1Accent'], ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl"><?= htmlspecialchars($hub['lede'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($hub['price'])): ?>
            <p class="mt-4 text-sm text-white/80">Guide price <strong class="text-white"><?= htmlspecialchars((string)$hub['price'], ENT_QUOTES, 'UTF-8') ?></strong><?php if (!empty($hub['priceNote'])): ?> — <?= htmlspecialchars((string)$hub['priceNote'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
            <?php endif; ?>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request a quote</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-6">
        <p class="text-sm text-zinc-600 leading-relaxed max-w-4xl">
            <strong class="text-black">Honest scope:</strong> <?= htmlspecialchars($hub['honest'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-2 gap-12">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">What this hub covers</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2"><?= htmlspecialchars($hub['coverTitle'], ENT_QUOTES, 'UTF-8') ?></h2>
            <?php foreach ($hub['cover'] as $para): ?>
                <p class="mt-4 text-zinc-700 text-lg leading-relaxed"><?= wave1Rich($para) ?></p>
            <?php endforeach; ?>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-3xl p-8">
            <h3 class="text-xl font-semibold text-black">Typical work</h3>
            <ul class="mt-5 space-y-3 text-zinc-700">
                <?php foreach ($hub['work'] as $item): ?>
                <li class="flex gap-3"><span class="text-[#ff6b00] font-bold">✓</span> <span><?= wave1Rich($item) ?></span></li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= url('/contact.php') ?>" class="inline-block mt-8 px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Ask for a scoped quote</a>
        </div>
    </div>
</section>

<?php if (!empty($hub['who'])): ?>
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Who it is for</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2"><?= htmlspecialchars($hub['whoTitle'], ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="grid md:grid-cols-3 gap-6 mt-10">
            <?php foreach ($hub['who'] as $card): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-7">
                <h3 class="font-semibold text-xl text-black"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-3"><?= wave1Rich($card['text']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">How we work</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Three steps, then a written quote</h2>
    <div class="grid md:grid-cols-3 gap-6 mt-10">
        <?php
        $steps = $hub['steps'] ?? [
            ['title' => 'Send the property', 'text' => 'Address, property type, certificates already on file, and access notes.'],
            ['title' => 'We scope & quote', 'text' => 'No catalogue prices here — layouts vary. You get a written quote after scope.'],
            ['title' => 'Attend & document', 'text' => 'Visit, inspect or install as agreed, then issue the records your file needs.'],
        ];
        $n = 1;
        foreach ($steps as $step):
        ?>
        <div class="border border-zinc-200 rounded-3xl p-7">
            <div class="text-[#ff6b00] font-semibold text-sm">Step <?= $n++ ?></div>
            <h3 class="font-semibold text-xl text-black mt-2"><?= htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-sm text-zinc-600 mt-3"><?= wave1Rich($step['text']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!empty($hub['relatedGuides'])): ?>
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Read next</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Guides on this topic</h2>
        <div class="grid md:grid-cols-3 gap-6 mt-10">
            <?php foreach ($hub['relatedGuides'] as $gSlug):
                $g = $guides[$gSlug] ?? wave1ExistingResourceCard($gSlug);
                if (!$g) { continue; }
            ?>
            <a href="<?= url('/pages/resources/' . $gSlug . '.php') ?>" class="bg-zinc-50 border border-zinc-200 rounded-3xl p-7 hover:border-[#ff6b00] transition">
                <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($g['tag'], ENT_QUOTES, 'UTF-8') ?></div>
                <h3 class="font-semibold text-xl text-black mt-2"><?= htmlspecialchars($g['cardTitle'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars($g['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <span class="inline-block mt-5 text-sm font-semibold text-[#ff6b00]">Read article →</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($hub['mainlandService']) && function_exists('getMainlandAreaRecords')): ?>
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">UK mainland</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Fire risk assessment in every mainland town</h2>
    <p class="mt-3 text-zinc-600 max-w-3xl">Each town opens its own FRA page. Guide price <?= htmlspecialchars((string)($hub['price'] ?? '£350'), ENT_QUOTES, 'UTF-8') ?> for a standard assessment.</p>
    <div class="mt-8 flex flex-wrap gap-2">
        <?php foreach (getMainlandAreaRecords() as $row): ?>
        <a href="<?= htmlspecialchars(url('/pages/' . $hub['mainlandService'] . '/' . $row['slug'] . '.php'), ENT_QUOTES, 'UTF-8') ?>"
           class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs text-zinc-700 hover:border-[#ff6b00]">
            <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($hub['faqs'])): ?>
<section class="bg-zinc-50 border-y">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black">Questions we hear first</h2>
        <div class="mt-8 space-y-3">
            <?php foreach ($hub['faqs'] as $faq): ?>
            <details class="border border-zinc-200 rounded-2xl px-5 py-4 bg-white">
                <summary class="font-semibold cursor-pointer"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                <p class="mt-3 text-zinc-700"><?= wave1Rich($faq['a']) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?= wave1QuoteFormHtml(
    $hub['formService'],
    $hub['formHeading'],
    $hub['formIntro'],
    $hub['formPlaceholder']
) ?>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
