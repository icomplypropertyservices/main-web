<?php
/**
 * Shared chrome for landlord conversion landings.
 * Quote CTAs link to the existing /contact form. This file does not render a form.
 *
 * Expects $landing (array) plus the usual header vars already set by the page.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

if (!function_exists('landingRich')) {
/** Escape, bold, and markdown links (root-relative or http). */
function landingRich(string $text): string
{
    $out = '';
    $offset = 0;
    $len = strlen($text);
    while ($offset < $len) {
        if (preg_match('/\[([^\]]+)\]\(((?:\/|https?:\/\/)[^)\s]+)\)/', $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $start = (int)$m[0][1];
            $out .= htmlspecialchars(substr($text, $offset, $start - $offset), ENT_QUOTES, 'UTF-8');
            $href = $m[2][0];
            $external = str_starts_with($href, 'http');
            $rel = $external ? ' target="_blank" rel="noopener"' : '';
            $out .= '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="text-[#ff6b00] font-semibold hover:underline"' . $rel . '>'
                . htmlspecialchars($m[1][0], ENT_QUOTES, 'UTF-8') . '</a>';
            $offset = $start + strlen($m[0][0]);
            continue;
        }
        $out .= htmlspecialchars(substr($text, $offset), ENT_QUOTES, 'UTF-8');
        break;
    }
    return preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $out) ?? $out;
}
}

$landing = $landing ?? [];
$home = rtrim(SITE_URL, '/');
$quoteHref = '/contact';
$callHref = 'tel:+447517806082';
$callLabel = 'Call 07517806082';
$crumbs = $landing['crumbs'] ?? [];
$faqs = $landing['faqs'] ?? [];

$faqEntities = [];
foreach ($faqs as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq['a'],
        ],
    ];
}
$crumbItems = [
    [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => $home . '/',
    ],
];
$pos = 2;
foreach ($crumbs as $crumb) {
    $crumbItems[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'name' => $crumb['name'],
        'item' => $home . $crumb['href'],
    ];
}
$jsonLd = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $crumbItems,
    ],
];
if ($faqEntities) {
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ];
}

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/70 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="/" class="hover:text-white">Home</a>
            <?php foreach ($crumbs as $crumb): ?>
                <span aria-hidden="true">/</span>
                <?php if (!empty($crumb['current'])): ?>
                    <span class="text-white"><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($crumb['href'], ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white"><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars((string)($landing['kicker'] ?? 'Stockport & Greater Manchester'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05] text-white">
                <?= htmlspecialchars((string)$landing['h1'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white max-w-2xl"><?= landingRich((string)$landing['lede']) ?></p>
            <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white">
                <?php foreach ($landing['proof'] ?? [] as $item): ?>
                    <li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?= htmlspecialchars($quoteHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
                <a href="<?= htmlspecialchars($callHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white font-semibold text-white hover:bg-white/10"><?= htmlspecialchars($callLabel, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
            <?php if (!empty($landing['note'])): ?>
                <p class="mt-6 text-sm text-white max-w-2xl"><?= landingRich((string)$landing['note']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="bg-[#0B1F3A] border-y border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-5 text-sm text-white">
        Landlords and agents: services on this page. Trades: materials stay in the site menu under Shop.
        <?php if (!empty($landing['shop'])): ?>
            Supply and fit for alarms is linked below.
        <?php endif; ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Scope</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2"><?= htmlspecialchars((string)$landing['scopeTitle'], ENT_QUOTES, 'UTF-8') ?></h2>
    <?php foreach ($landing['scope'] ?? [] as $para): ?>
        <p class="mt-4 text-lg leading-relaxed max-w-3xl"><?= landingRich((string)$para) ?></p>
    <?php endforeach; ?>
    <?php if (!empty($landing['scopeItems'])): ?>
        <ul class="mt-8 grid md:grid-cols-2 gap-4 max-w-4xl">
            <?php foreach ($landing['scopeItems'] as $item): ?>
                <li class="border border-white/15 rounded-2xl p-5">
                    <h3 class="font-semibold text-lg"><?= htmlspecialchars((string)$item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-2 text-sm leading-relaxed"><?= landingRich((string)$item['text']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<section class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">How it works</div>
        <h2 class="text-3xl font-semibold tracking-tight mt-2"><?= htmlspecialchars((string)($landing['stepsTitle'] ?? 'Three steps, then a fixed quote'), ENT_QUOTES, 'UTF-8') ?></h2>
        <div class="grid md:grid-cols-3 gap-6 mt-10">
            <?php $n = 1; foreach ($landing['steps'] ?? [] as $step): ?>
                <div class="border border-white/15 rounded-3xl p-7">
                    <div class="text-[#ff6b00] font-semibold text-sm">Step <?= $n++ ?></div>
                    <h3 class="font-semibold text-xl mt-2"><?= htmlspecialchars((string)$step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm mt-3 leading-relaxed"><?= landingRich((string)$step['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($landing['shop'])): ?>
<section class="bg-[#0B1F3A] text-white border-y border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Supply or supply and fit</div>
        <h2 class="text-3xl font-semibold tracking-tight mt-2 text-white"><?= htmlspecialchars((string)$landing['shop']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-4 text-white max-w-3xl leading-relaxed"><?= landingRich((string)$landing['shop']['text']) ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars($quoteHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
            <a href="<?= htmlspecialchars($callHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white font-semibold text-white hover:bg-white/10"><?= htmlspecialchars($callLabel, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars((string)$landing['shop']['href'], ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white font-semibold text-white hover:bg-white/10"><?= htmlspecialchars((string)$landing['shop']['label'], ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="max-w-3xl mx-auto px-6 py-16 md:py-20">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">FAQ</div>
    <h2 class="text-3xl font-semibold tracking-tight mt-2">Questions we can answer accurately</h2>
    <div class="mt-8 space-y-3">
        <?php foreach ($faqs as $faq): ?>
            <details class="border border-white/15 rounded-2xl p-5 group">
                <summary class="font-semibold cursor-pointer list-none flex items-center justify-between gap-4">
                    <span><?= htmlspecialchars((string)$faq['q'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-[#ff6b00] text-xl leading-none group-open:rotate-45 transition shrink-0" aria-hidden="true">+</span>
                </summary>
                <p class="mt-3 text-sm leading-relaxed"><?= landingRich((string)$faq['a']) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Related pages</div>
        <h2 class="text-3xl font-semibold tracking-tight mt-2">Services and guides</h2>
        <ul class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($landing['links'] ?? [] as $link): ?>
                <li>
                    <a class="block border border-white/15 rounded-2xl px-5 py-4 font-semibold hover:border-[#ff6b00]" href="<?= htmlspecialchars((string)$link['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$link['label'], ENT_QUOTES, 'UTF-8') ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if (!empty($landing['sources'])): ?>
            <h3 class="text-lg font-semibold mt-12">Official sources</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <?php foreach ($landing['sources'] as $source): ?>
                    <li>
                        <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= htmlspecialchars((string)$source['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$source['label'], ENT_QUOTES, 'UTF-8') ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

<section class="page-hero bg-[#0B1F3A] text-white border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-white"><?= htmlspecialchars((string)$landing['ctaTitle'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-4 text-white max-w-2xl leading-relaxed"><?= landingRich((string)$landing['ctaText']) ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars($quoteHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
            <a href="<?= htmlspecialchars($callHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white font-semibold text-white hover:bg-white/10"><?= htmlspecialchars($callLabel, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
        <p class="mt-8 text-sm text-white leading-relaxed">
            iComply Property Services, 17 Woodlands Park Road, Offerton, Stockport SK2 5DE
            · <a class="underline" href="<?= htmlspecialchars($callHref, ENT_QUOTES, 'UTF-8') ?>">07517806082</a>
            · <a class="underline" href="mailto:info@icomplypropertyservices.co.uk">info@icomplypropertyservices.co.uk</a>
        </p>
        <p class="mt-3 text-sm text-white">Fixed quote after scope (POA until then). No obligation until you accept a fixed price. We aim to respond within 2 hours on business days — an aim, not a contractual SLA.</p>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
