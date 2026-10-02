<?php
/**
 * Commercial compliance job page.
 * Vars come from renderCommercialJobPage() — do not request this file directly.
 */
declare(strict_types=1);

/** @var array<string,mixed> $job */
$job = $GLOBALS['COMMERCIAL_JOB'] ?? [];
$serviceName = (string)($GLOBALS['COMMERCIAL_SERVICE_NAME'] ?? '');
$serviceSlug = (string)($GLOBALS['COMMERCIAL_SERVICE_SLUG'] ?? '');
$keywordHref = (string)($GLOBALS['COMMERCIAL_KEYWORD_HREF'] ?? '');
$related = $GLOBALS['COMMERCIAL_RELATED'] ?? [];
if (!is_array($related)) {
    $related = [];
}

$slug = keywordSlug((string)($job['slug'] ?? ''));
$name = (string)($job['name'] ?? keywordDisplayName($slug));
$h1 = (string)($job['h1'] ?? $name);
$h1Accent = (string)($job['h1_accent'] ?? '');
$kicker = (string)($job['kicker'] ?? 'Commercial compliance');
$intro = (string)($job['intro'] ?? '');
$honest = (string)($job['honest'] ?? 'Quote after we know the site. This page is not legal advice and it is not a published price list.');
$paragraphs = array_values(array_filter((array)($job['paragraphs'] ?? []), 'is_string'));
$points = array_values(array_filter((array)($job['points'] ?? []), 'is_string'));
$faqs = [];
foreach ((array)($job['faqs'] ?? []) as $faq) {
    if (!is_array($faq)) {
        continue;
    }
    $q = trim((string)($faq['q'] ?? ''));
    $a = trim((string)($faq['a'] ?? ''));
    if ($q !== '' && $a !== '') {
        $faqs[] = ['q' => $q, 'a' => $a];
    }
}
$standards = (string)($job['standards'] ?? '');
$premises = (string)($job['premises'] ?? '');
$deliverable = (string)($job['deliverable'] ?? '');
$group = (string)($job['group'] ?? 'Commercial');

if (!isset($pageTitle)) {
    $pageTitle = (string)($job['title'] ?? $name);
}
if (!isset($metaDesc)) {
    $metaDesc = (string)($job['meta'] ?? $intro);
}
if (!isset($metaKeywords)) {
    $metaKeywords = (string)($job['seo_keywords'] ?? '');
}
if (!isset($canonicalUrl)) {
    $canonicalUrl = url(commercialJobPath($slug) . '.php');
}
if (!isset($ogImage)) {
    $ogImage = url('/assets/images/services/' . ($serviceSlug !== '' ? $serviceSlug : 'fire-alarms') . '.jpg');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$faqEntities = [];
foreach ($faqs as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
    ];
}

require_once SITE_ROOT . '/includes/share.php';
require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => array_values(array_filter([
        [
            '@type' => 'Service',
            'name' => $name,
            'serviceType' => $serviceName,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'areaServed' => 'North West England',
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'telephone' => PHONE,
                'url' => rtrim(SITE_URL, '/') . '/',
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Commercial', 'item' => url('/pages/commercial.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $canonicalUrl],
            ],
        ],
        $faqEntities ? [
            '@type' => 'FAQPage',
            'mainEntity' => $faqEntities,
        ] : null,
    ])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/commercial.php') ?>" class="hover:text-white">Commercial</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                <?= htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') ?><?php if ($h1Accent !== ''): ?> <span class="text-[#ff6b00]"><?= htmlspecialchars($h1Accent, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl"><?= htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request a POA quote</a>
                <?php if ($serviceSlug !== ''): ?>
                    <a href="<?= url('/pages/services/' . $serviceSlug . '.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100"><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
                <a href="<?= url('/pages/commercial.php') ?>#jobs" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">All commercial jobs</a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-zinc-700">
        <?= htmlspecialchars($honest, ENT_QUOTES, 'UTF-8') ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-5 text-zinc-700 text-lg leading-relaxed">
            <h2 class="text-3xl font-semibold tracking-tight text-black">What this commercial job is</h2>
            <?php foreach ($paragraphs as $para): ?>
                <p><?= htmlspecialchars($para, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
            <?php if ($keywordHref !== ''): ?>
                <p class="text-base">
                    Wider topic guide:
                    <a class="font-semibold text-[#ff6b00]" href="<?= htmlspecialchars($keywordHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> guide</a>.
                </p>
            <?php endif; ?>
        </div>
        <aside class="space-y-4">
            <?php
            $facts = [
                'Premises' => $premises,
                'Standards we work to' => $standards,
                'What you get' => $deliverable,
                'Lane' => $group,
            ];
            foreach ($facts as $label => $value):
                if ($value === '') {
                    continue;
                }
            ?>
            <div class="bg-zinc-50 border border-zinc-200 rounded-3xl p-5">
                <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
                <p class="mt-2 text-sm text-zinc-700"><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endforeach; ?>
        </aside>
    </div>
    <?php if ($points): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-semibold tracking-tight text-black mb-6">On the visit</h2>
        <ul class="grid sm:grid-cols-2 gap-4">
            <?php foreach ($points as $point): ?>
                <li class="flex gap-3 bg-white border border-zinc-200 rounded-2xl p-5 text-sm text-zinc-700">
                    <span class="text-[#ff6b00] font-bold">✓</span>
                    <span><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</section>

<?php if ($faqs): ?>
<section class="bg-zinc-50 border-y">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black">Questions facilities teams ask</h2>
        <div class="mt-8 space-y-4">
            <?php foreach ($faqs as $faq): ?>
                <details class="bg-white border border-zinc-200 rounded-2xl p-5" open>
                    <summary class="font-semibold text-black cursor-pointer"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
                    <p class="mt-3 text-sm text-zinc-700"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($related): ?>
<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-semibold tracking-tight text-black">Related commercial jobs</h2>
    <div class="mt-6 flex flex-wrap gap-2">
        <?php foreach ($related as $rel):
            $relSlug = keywordSlug((string)($rel['slug'] ?? ''));
            $relName = (string)($rel['name'] ?? keywordDisplayName($relSlug));
        ?>
            <a href="<?= url(commercialJobPath($relSlug) . '.php') ?>"
               class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                <?= htmlspecialchars($relName, ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= url('/pages/commercial.php') ?>#jobs" class="px-4 py-2 text-sm font-semibold text-[#ff6b00]">Commercial hub →</a>
    </div>
</section>
<?php endif; ?>

<section id="quote" class="bg-[#0B1F3A] text-white">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Price on application</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2">Quote <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mt-3 text-white/80">Tell us the building, the kit you already have, and whether this is a one-off or a portfolio round. We price after that scope — not from a national grid.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white text-black border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="service" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
            <textarea name="message" rows="5" required maxlength="5000"
                      placeholder="Site address, use (office, warehouse, retail, mixed-use), access hours, and what you need quoted…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Request commercial quote</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-10">
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
