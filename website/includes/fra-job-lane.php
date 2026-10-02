<?php
/**
 * Fire risk assessment job lane.
 * Approved list: typical North West 6-bed HMO FRA £350.
 * Pack cross-link: FRA + EICR + gas £650 (replaces the three lines).
 * Draft content. Do not promote this branch to production.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

if (!function_exists('fraLaneRich')) {
    function fraLaneRich(string $text): string
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

if (!function_exists('fraLaneMoney')) {
    /** Nearest pound, halves away from zero (0.5 up). */
    function fraLaneMoney(int $pence, float $percentOff = 0.0): int
    {
        $pounds = ($pence / 100) * (1 - ($percentOff / 100));
        return (int)round($pounds, 0, PHP_ROUND_HALF_UP);
    }
}

if (!function_exists('fraLaneRates')) {
    /**
     * @return array<string,mixed>
     */
    function fraLaneRates(): array
    {
        return [
            'fra' => [
                'code' => 'FIRE-FRA-6BED-NW',
                'pence' => 35000,
                'name' => 'Fire risk assessment',
                'scope' => 'Typical 6-bed HMO, North West, per property',
            ],
            'bundle' => [
                'code' => 'LAND-PKG-FRA-EICR-GAS-6BED-NW',
                'pence' => 65000,
                'name' => 'Landlord pack: FRA + EICR + gas',
                'scope' => 'Same typical 6-bed HMO. Replaces the three separate lines.',
            ],
            'tiers' => [
                ['id' => 'T0', 'label' => '1 property', 'percent' => 0.0],
                ['id' => 'T1', 'label' => '2–5 properties', 'percent' => 5.0],
                ['id' => 'T2', 'label' => '6–10 properties', 'percent' => 10.0],
                ['id' => 'T3', 'label' => '11–20 properties', 'percent' => 12.5],
                ['id' => 'T4', 'label' => '21+ properties', 'percent' => 15.0],
            ],
        ];
    }
}

if (!function_exists('fraLanePages')) {
    /**
     * @return array<string,array<string,mixed>>
     */
    function fraLanePages(): array
    {
        $rates = fraLaneRates();
        $fra = '£' . fraLaneMoney((int)$rates['fra']['pence']);
        $pack = '£' . fraLaneMoney((int)$rates['bundle']['pence']);

        return [
            'hub' => [
                'path' => '/pages/jobs/fra',
                'pageTitle' => 'Fire risk assessment jobs | ' . $fra . ' list',
                'metaDesc' => 'FRA job lane. A typical North West 6-bed HMO is listed at ' . $fra . '. Other premises are priced after scope. The landlord pack with EICR and gas is ' . $pack . '.',
                'ogTitle' => 'Fire risk assessment job lane',
                'kicker' => 'Job lane · Fire risk assessment',
                'h1' => 'Fire risk assessment jobs',
                'lede' => 'One lane for the written FRA. A typical North West 6-bed HMO is **' . $fra . '**. The [landlord pack](/pages/jobs/landlord-bundle) is **' . $pack . '** when EICR and gas safety sit on the same property.',
                'note' => 'General information, not legal advice. Remedials, parts and follow-on installs are quoted after the assessment. iComply is not VAT registered, so VAT is not added to these list prices.',
                'crumbs' => [
                    ['name' => 'Fire risk assessment', 'href' => '/pages/fire-risk-assessment'],
                    ['name' => 'Job lane', 'href' => '/pages/jobs/fra', 'current' => true],
                ],
                'priceCards' => ['fra', 'bundle'],
                'cards' => [
                    [
                        'href' => '/pages/jobs/fire-risk-assessment',
                        'title' => 'Typical 6-bed HMO',
                        'price' => $fra,
                        'text' => 'Approved list, per property. Code ' . $rates['fra']['code'] . '. Written FRA and a prioritised action plan. Remedials are separate.',
                    ],
                    [
                        'href' => '/pages/jobs/fra-other',
                        'title' => 'Other premises',
                        'price' => 'POA',
                        'text' => 'Offices, shops, blocks and anything that is not a typical 6-bed HMO. Code FIRE-FRA-OTHER. We confirm a figure after scope.',
                    ],
                    [
                        'href' => '/pages/jobs/landlord-bundle',
                        'title' => 'Landlord pack',
                        'price' => $pack,
                        'text' => 'FRA, EICR and gas safety on the same typical 6-bed HMO. The pack replaces those three lines. It is not added on top of ' . $fra . '.',
                    ],
                ],
                'sections' => [
                    [
                        'h2' => 'What this lane is',
                        'p' => [
                            'The [quality hub](/pages/fire-risk-assessment) explains the assessment. This lane is the job and the list price. The [service page](/pages/services/fire-risk-assessments) stays the service route.',
                            'A suitable FRA describes the building and the people in it, then an action list you can schedule. It is not a licence, not legal advice, and not a guarantee of an enforcement outcome.',
                        ],
                    ],
                    [
                        'h2' => 'How the two prices sit together',
                        'p' => [
                            '**' . $fra . '** is the FRA on its own for a typical North West 6-bed HMO.',
                            '**' . $pack . '** is the pack: that FRA, plus an EICR, plus a landlord gas safety record, on the same property. Book the pack and you do not also pay ' . $fra . ' on top.',
                        ],
                    ],
                ],
            ],
            'fra' => [
                'path' => '/pages/jobs/fire-risk-assessment',
                'pageTitle' => 'Fire risk assessment ' . $fra . ' | 6-bed HMO',
                'metaDesc' => 'Fire risk assessment for a typical North West 6-bed HMO. List price ' . $fra . ' per property. Remedials quoted after the visit. Pack with EICR and gas is ' . $pack . '.',
                'ogTitle' => 'Fire risk assessment — ' . $fra,
                'kicker' => 'List price · Typical 6-bed HMO',
                'h1' => 'Fire risk assessment — ' . $fra,
                'lede' => 'Approved list for a **typical 6-bed HMO in the North West**, per property. Code **' . $rates['fra']['code'] . '**. You get a written assessment and a prioritised action plan.',
                'note' => 'This figure is the assessment. Alarms, emergency lighting, doors and other remedials are quoted after the visit. Buildings that are not a typical 6-bed HMO are [priced after scope](/pages/jobs/fra-other).',
                'crumbs' => [
                    ['name' => 'Job lane', 'href' => '/pages/jobs/fra'],
                    ['name' => 'Typical 6-bed HMO', 'href' => '/pages/jobs/fire-risk-assessment', 'current' => true],
                ],
                'priceCards' => ['fra', 'bundle'],
                'showTiers' => 'fra',
                'sections' => [
                    [
                        'h2' => 'What ' . $fra . ' covers',
                        'p' => [
                            'A walk of that typical 6-bed HMO, the written fire risk assessment, and an action list that separates management tasks from physical upgrades.',
                            'Follow-on work is a separate quote unless you book the [landlord pack at ' . $pack . '](/pages/jobs/landlord-bundle).',
                        ],
                    ],
                    [
                        'h2' => 'What it does not cover',
                        'p' => [
                            'It does not include an EICR or a gas safety record. Those sit in the **' . $pack . '** pack, which replaces the separate FRA line.',
                            'It does not cover offices, shops, larger blocks or other layouts. Use [FRA — other premises](/pages/jobs/fra-other).',
                            'Travel outside the North West is agreed before booking. An aborted visit with no access may be chargeable; the amount is confirmed if it applies.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Is £350 a from-price?',
                        'a' => 'No. £350 is the list for a typical North West 6-bed HMO. A portfolio discount can reduce the unit price. Other buildings are not this list.',
                    ],
                    [
                        'q' => 'Does the pack add £350 on top of £650?',
                        'a' => 'No. The landlord pack is £650 for FRA, EICR and gas together on that same typical 6-bed HMO. Booking the pack means you do not also pay the £350 line.',
                    ],
                    [
                        'q' => 'Are remedials included?',
                        'a' => 'No. The list is the assessment and the action plan. Physical upgrades are quoted after the visit.',
                    ],
                ],
            ],
            'other' => [
                'path' => '/pages/jobs/fra-other',
                'pageTitle' => 'Fire risk assessment for other premises | POA',
                'metaDesc' => 'Fire risk assessments for premises that are not a typical North West 6-bed HMO. Priced after scope. The 6-bed list is £350. The landlord pack is £650.',
                'ogTitle' => 'FRA for other premises',
                'kicker' => 'Priced after scope',
                'h1' => 'Fire risk assessment — other premises',
                'lede' => 'Offices, shops, warehouses, blocks and shared houses that are **not** a typical 6-bed HMO. Code **FIRE-FRA-OTHER**. There is no list price until the building is known.',
                'note' => 'If the building is a typical North West 6-bed HMO, use the [' . $fra . ' job](/pages/jobs/fire-risk-assessment) instead.',
                'crumbs' => [
                    ['name' => 'Job lane', 'href' => '/pages/jobs/fra'],
                    ['name' => 'Other premises', 'href' => '/pages/jobs/fra-other', 'current' => true],
                ],
                'priceCards' => ['fra', 'bundle'],
                'sections' => [
                    [
                        'h2' => 'Why this is not ' . $fra,
                        'p' => [
                            'The **' . $fra . '** list is only the typical 6-bed HMO in the North West. Storeys, sleeping risk, common parts and commercial use change the time on site.',
                            'Tell us the address, use and storeys. We confirm a figure before anyone is booked.',
                        ],
                    ],
                    [
                        'h2' => 'Pack cross-link',
                        'p' => [
                            'The [landlord pack](/pages/jobs/landlord-bundle) at **' . $pack . '** is also the typical 6-bed HMO (FRA, EICR and gas). It is not a price for a commercial unit or a larger block.',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Can you use the £350 figure for an office?',
                        'a' => 'No. £350 is the typical 6-bed HMO list. An office, shop or block is scoped first.',
                    ],
                    [
                        'q' => 'Where is the £650 pack explained?',
                        'a' => 'On the landlord pack page. It is FRA, EICR and gas for the same typical 6-bed HMO, and it replaces those three separate prices.',
                    ],
                ],
            ],
            'bundle' => [
                'path' => '/pages/jobs/landlord-bundle',
                'pageTitle' => 'Landlord pack ' . $pack . ' | FRA, EICR and gas',
                'metaDesc' => 'Landlord pack for a typical North West 6-bed HMO: fire risk assessment, EICR and gas safety for ' . $pack . '. The FRA alone is ' . $fra . '. The pack is not added on top.',
                'ogTitle' => 'Landlord pack — ' . $pack,
                'kicker' => 'Pack · FRA + EICR + gas',
                'h1' => 'Landlord pack — ' . $pack,
                'lede' => '**FRA, EICR and gas safety** on the same typical North West 6-bed HMO. Code **' . $rates['bundle']['code'] . '**. The discount, when a portfolio qualifies, applies to **' . $pack . '**.',
                'note' => 'The three certificates are not added on top of this figure. The FRA on its own remains [' . $fra . '](/pages/jobs/fire-risk-assessment).',
                'crumbs' => [
                    ['name' => 'Job lane', 'href' => '/pages/jobs/fra'],
                    ['name' => 'Landlord pack ' . $pack, 'href' => '/pages/jobs/landlord-bundle', 'current' => true],
                ],
                'priceCards' => ['bundle', 'fra'],
                'showTiers' => 'bundle',
                'sections' => [
                    [
                        'h2' => 'What ' . $pack . ' includes',
                        'p' => [
                            'A fire risk assessment, an EICR, and a landlord gas safety record for that typical 6-bed HMO.',
                            'Remedials and parts after those visits are quoted separately. Travel outside the North West is agreed before booking.',
                        ],
                    ],
                    [
                        'h2' => 'How it relates to the ' . $fra . ' FRA',
                        'p' => [
                            'Book the FRA only and the list is **' . $fra . '**. Book the pack and the list is **' . $pack . '** for all three. You do not pay ' . $fra . ' as well.',
                            'This pack is not the wider Fire Ready or Full FM conversation. Those stay on [packages](/pages/packages).',
                        ],
                    ],
                ],
                'faqs' => [
                    [
                        'q' => 'Is £650 FRA plus EICR plus gas added together?',
                        'a' => 'No. £650 is the pack price for the three on one typical 6-bed HMO. The separate FRA list of £350 is not added on top.',
                    ],
                    [
                        'q' => 'Does a multi-property discount stack on each certificate?',
                        'a' => 'No. The portfolio percentage applies to the £650 pack. It is not applied again to FRA, EICR and gas inside that pack.',
                    ],
                ],
            ],
        ];
    }
}

if (!isset($FRA_LANE) || $FRA_LANE === '') {
    return;
}

$fraLaneSlug = $FRA_LANE;
$fraLanePages = fraLanePages();
$fraPage = $fraLanePages[$fraLaneSlug] ?? null;
if (!$fraPage) {
    http_response_code(404);
    require SITE_ROOT . '/404.php';
    return;
}

$fraRates = fraLaneRates();
$pageTitle = $fraPage['pageTitle'];
$metaDesc = $fraPage['metaDesc'];
$ogTitle = $fraPage['ogTitle'];
$ogImage = url('/assets/images/services/fire-risk-assessments.jpg');
$ogImageAlt = 'Fire risk assessment — iComply Property Services, Stockport';
$canonicalUrl = url($fraPage['path']);
$metaKeywords = 'fire risk assessment, FRA £350, landlord pack £650, HMO fire risk assessment Stockport';

$home = rtrim(SITE_URL, '/');
$crumbs = $fraPage['crumbs'];
$faqEntities = [];
foreach ($fraPage['faqs'] ?? [] as $faq) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
    ];
}
$crumbItems = [[
    '@type' => 'ListItem',
    'position' => 1,
    'name' => 'Home',
    'item' => $home . '/',
]];
$pos = 2;
foreach ($crumbs as $crumb) {
    $crumbItems[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'name' => $crumb['name'],
        'item' => $home . $crumb['href'],
    ];
}
$jsonLd = [[
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $crumbItems,
]];
if ($faqEntities) {
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqEntities,
    ];
}
if (in_array($fraLaneSlug, ['fra', 'bundle'], true)) {
    $rateKey = $fraLaneSlug === 'bundle' ? 'bundle' : 'fra';
    $jsonLd[] = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $fraRates[$rateKey]['name'],
        'serviceType' => $fraRates[$rateKey]['name'],
        'areaServed' => 'North West England',
        'provider' => [
            '@type' => 'LocalBusiness',
            'name' => 'iComply Property Services',
            'telephone' => '+447517806082',
        ],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'GBP',
            'price' => (string)fraLaneMoney((int)$fraRates[$rateKey]['pence']),
            'description' => $fraRates[$rateKey]['scope'] . '. All-in. VAT is not added.',
        ],
    ];
}

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-sm text-white mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[#ff6b00]">Home</a>
            <?php foreach ($crumbs as $crumb): ?>
                <span aria-hidden="true">/</span>
                <?php if (!empty($crumb['current'])): ?>
                    <span><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($crumb['href'], ENT_QUOTES, 'UTF-8') ?>" class="hover:text-[#ff6b00]"><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5 text-white">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars($fraPage['kicker'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05] text-white">
                <?= htmlspecialchars($fraPage['h1'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white max-w-2xl"><?= fraLaneRich($fraPage['lede']) ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="/contact" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
                <a href="tel:+447517806082" class="px-8 py-4 rounded-2xl border border-white font-semibold text-white hover:bg-white/10">Call 07517806082</a>
            </div>
            <p class="mt-6 text-sm text-white max-w-2xl"><?= fraLaneRich($fraPage['note']) ?></p>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10">
    <h2 class="text-2xl font-semibold text-white">List prices</h2>
    <div class="mt-6 grid md:grid-cols-2 gap-4">
        <?php foreach ($fraPage['priceCards'] as $key):
            $rate = $fraRates[$key];
            $href = $key === 'bundle' ? '/pages/jobs/landlord-bundle' : '/pages/jobs/fire-risk-assessment';
            $amount = '£' . fraLaneMoney((int)$rate['pence']);
        ?>
        <a class="panel-light block rounded-3xl border p-7 hover:border-[#ff6b00]" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= $key === 'bundle' ? 'Pack' : 'FRA' ?></div>
            <p class="text-5xl font-semibold mt-2 text-black"><?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-3 font-semibold text-black"><?= htmlspecialchars($rate['name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-2 text-sm"><?= htmlspecialchars($rate['scope'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-3 text-sm">Code <?= htmlspecialchars($rate['code'], ENT_QUOTES, 'UTF-8') ?></p>
        </a>
        <?php endforeach; ?>
    </div>
    <p class="mt-4 text-sm text-white">All-in list prices. VAT is not added. The pack is not stacked on the FRA line.</p>
</section>

<?php if (!empty($fraPage['cards'])): ?>
<section class="max-w-7xl mx-auto px-6 pb-12">
    <h2 class="text-3xl font-semibold text-white">Pages in this lane</h2>
    <div class="mt-6 grid md:grid-cols-3 gap-4">
        <?php foreach ($fraPage['cards'] as $card): ?>
        <a class="block rounded-3xl border border-white/20 p-6 hover:border-[#ff6b00]" href="<?= htmlspecialchars($card['href'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="text-[#ff6b00] font-semibold"><?= htmlspecialchars($card['price'], ENT_QUOTES, 'UTF-8') ?></div>
            <h3 class="text-xl font-semibold text-white mt-2"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="mt-3 text-sm text-white"><?= htmlspecialchars($card['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php foreach ($fraPage['sections'] as $section): ?>
<section class="max-w-3xl mx-auto px-6 py-8">
    <h2 class="text-3xl font-semibold text-white"><?= htmlspecialchars($section['h2'], ENT_QUOTES, 'UTF-8') ?></h2>
    <?php foreach ($section['p'] as $para): ?>
        <p class="mt-4 text-white leading-relaxed"><?= fraLaneRich($para) ?></p>
    <?php endforeach; ?>
</section>
<?php endforeach; ?>

<?php if (!empty($fraPage['showTiers'])):
    $tierKey = $fraPage['showTiers'];
    $tierPence = (int)$fraRates[$tierKey]['pence'];
?>
<section class="max-w-3xl mx-auto px-6 py-8">
    <h2 class="text-3xl font-semibold text-white">Portfolio discount on this list</h2>
    <p class="mt-4 text-white leading-relaxed">Certificates and this pack only. Each discounted unit is rounded to the nearest pound (0.5 up), then multiplied by the number of properties. The pack percentage applies to £<?= fraLaneMoney($tierPence) ?> and does not stack separate FRA, EICR and gas lines.</p>
    <div class="mt-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-white">
            <thead>
                <tr class="border-b border-white/20">
                    <th class="py-3 pr-4 font-semibold">Properties</th>
                    <th class="py-3 pr-4 font-semibold">Discount</th>
                    <th class="py-3 font-semibold">Unit price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fraRates['tiers'] as $tier): ?>
                <tr class="border-b border-white/15">
                    <td class="py-3 pr-4"><?= htmlspecialchars($tier['label'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="py-3 pr-4"><?= htmlspecialchars(rtrim(rtrim(number_format((float)$tier['percent'], 1), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>%</td>
                    <td class="py-3 font-semibold">£<?= fraLaneMoney($tierPence, (float)$tier['percent']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($fraPage['faqs'])): ?>
<section class="max-w-3xl mx-auto px-6 py-10">
    <h2 class="text-3xl font-semibold text-white">Questions</h2>
    <div class="mt-6 space-y-3">
        <?php foreach ($fraPage['faqs'] as $faq): ?>
        <details class="border border-white/20 rounded-2xl p-5">
            <summary class="font-semibold text-white cursor-pointer"><?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?></summary>
            <p class="mt-3 text-white text-sm leading-relaxed"><?= fraLaneRich($faq['a']) ?></p>
        </details>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-3xl font-semibold text-white">Cross-links</h2>
    <ul class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php
        $links = [
            ['/pages/jobs/fra', 'FRA job hub'],
            ['/pages/jobs/fire-risk-assessment', 'FRA £' . fraLaneMoney((int)$fraRates['fra']['pence'])],
            ['/pages/jobs/fra-other', 'FRA — other premises (POA)'],
            ['/pages/jobs/landlord-bundle', 'Landlord pack £' . fraLaneMoney((int)$fraRates['bundle']['pence'])],
            ['/pages/fire-risk-assessment', 'Fire risk assessment hub'],
            ['/pages/services/fire-risk-assessments', 'FRA service'],
            ['/pages/resources/fire-risk-assessment-guide', 'FRA guide'],
            ['/pages/pricing', 'Pricing guide'],
            ['/pages/packages', 'Packages'],
            ['/pages/landlords', 'Landlords'],
            ['/shop/fire/', 'Fire supplies'],
            ['/contact', 'Get a quote'],
        ];
        foreach ($links as [$href, $label]):
        ?>
        <li>
            <a class="block border border-white/20 rounded-2xl px-5 py-4 font-semibold text-white hover:border-[#ff6b00]" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
    <p class="mt-8 text-sm text-white">
        iComply Property Services, 17 Woodlands Park Road, Offerton, Stockport SK2 5DE
        · <a class="underline text-white" href="tel:+447517806082">07517806082</a>
        · <a class="underline text-white" href="mailto:info@icomplypropertyservices.co.uk">info@icomplypropertyservices.co.uk</a>
    </p>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
