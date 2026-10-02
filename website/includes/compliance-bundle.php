<?php
/**
 * Compliance Bundle / landlord pack — single source of truth.
 * The published pack price lives only in icomplyComplianceBundlePriceGbp().
 * Pages, nav, schema and cross-sell must read this file. Do not hardcode the figure.
 */
declare(strict_types=1);

/** Published pack price in whole pounds. Change the offer here only. */
function icomplyComplianceBundlePriceGbp(): int
{
    return 650;
}

function icomplyComplianceBundlePriceLabel(): string
{
    return '£' . number_format(icomplyComplianceBundlePriceGbp(), 0, '.', ',');
}

/**
 * @return array{
 *   id:string,
 *   name:string,
 *   alias:string,
 *   service_value:string,
 *   price_gbp:int,
 *   price_label:string,
 *   canonical_path:string,
 *   alias_path:string,
 *   nav_label:string,
 *   scope:string,
 *   includes:list<string>,
 *   excludes:list<string>,
 *   cross_sell:array<string,array{eyebrow:string,title:string,body:string}>
 * }
 */
function icomplyComplianceBundle(): array
{
    $price = icomplyComplianceBundlePriceGbp();
    $label = icomplyComplianceBundlePriceLabel();
    $name = 'Compliance Bundle';
    $alias = 'Landlord pack';

    return [
        'id' => 'compliance-bundle',
        'name' => $name,
        'alias' => $alias,
        'service_value' => $name . ' (' . strtolower($alias) . ')',
        'price_gbp' => $price,
        'price_label' => $label,
        'canonical_path' => '/pages/packages/compliance-bundle.php',
        'alias_path' => '/pages/packages/landlord-pack.php',
        'nav_label' => $name . ' · ' . $label,
        'scope' => 'One residential property in the North West — a house, a flat, or a small shared house — where the EICR, the landlord gas safety record and the fire risk assessment can be completed with ordinary access. The published price is ' . $label . ' for that pack. It is not a “from” guide, and it is not the price of any one certificate on its own.',
        'includes' => [
            'EICR for the fixed electrical installation at that address',
            'Landlord gas safety record (CP12) where gas appliances are present',
            'Fire risk assessment for the same address',
            'One documentation pack for the landlord or letting-agent file',
            'Coordinated attendance so the three items share the same access plan',
        ],
        'excludes' => [
            'Remedial works, parts, failed appliances and actions from the FRA',
            'EPC, PAT, smoke or CO alarm supply, fire-alarm installation and emergency lighting',
            'Commercial premises, care homes and blocks with extensive common parts',
            'A second visit, out-of-hours work, or specialist access — quoted before anyone attends',
        ],
        'cross_sell' => [
            'electrical' => [
                'eyebrow' => 'Booking an EICR?',
                'title' => 'Add gas and a fire risk assessment',
                'body' => 'This page is the electrical report on its own, still quoted to the property. The ' . $name . ' (' . strtolower($alias) . ') adds the landlord gas safety record and a fire risk assessment for the same residential address.',
            ],
            'gas-systems' => [
                'eyebrow' => 'Booking a gas safety record?',
                'title' => 'Add the EICR and a fire risk assessment',
                'body' => 'A gas record on its own is still quoted from the appliances on site. The ' . $name . ' (' . strtolower($alias) . ') adds the EICR and a fire risk assessment for the same residential address.',
            ],
            'fire-risk-assessments' => [
                'eyebrow' => 'Booking a fire risk assessment?',
                'title' => 'Add the EICR and the gas safety record',
                'body' => 'An FRA on its own is still quoted to the building. The ' . $name . ' (' . strtolower($alias) . ') adds the EICR and the landlord gas safety record for the same residential address.',
            ],
            'landlords' => [
                'eyebrow' => 'One published pack',
                'title' => $name . ' for a single residential let',
                'body' => 'EICR, landlord gas safety record and fire risk assessment for one house, flat or small shared house, in one file. Portfolio programmes and anything outside that scope stay on a written quote.',
            ],
            'packages' => [
                'eyebrow' => 'Published pack price',
                'title' => $name . ' / ' . strtolower($alias),
                'body' => 'The only fixed pack price on this site. Let Ready, Fire Ready, Workplace Essentials and the bundles below are quoted after scope.',
            ],
            'pricing' => [
                'eyebrow' => 'Not a guide ballpark',
                'title' => $name . ' is a published pack price',
                'body' => 'Every other figure on this page is a From £X estimate. The ' . $name . ' (' . strtolower($alias) . ') is the fixed published price for one residential property in the scope on its page.',
            ],
        ],
    ];
}

function icomplyComplianceBundleCanonicalPath(): string
{
    return icomplyComplianceBundle()['canonical_path'];
}

function icomplyComplianceBundleHref(): string
{
    $path = icomplyComplianceBundleCanonicalPath();
    return function_exists('url') ? url($path) : $path;
}

/** @return list<string> */
function icomplyComplianceBundleCrossSellKeys(): array
{
    return ['electrical', 'gas-systems', 'fire-risk-assessments'];
}

function icomplyComplianceBundleCrossSellKey(string $from): ?string
{
    $map = [
        'electrical' => 'electrical',
        'electrical-safety-landlords' => 'electrical',
        'gas-systems' => 'gas-systems',
        'gas-safety-certificate' => 'gas-systems',
        'fire-risk-assessments' => 'fire-risk-assessments',
        'fire-risk-assessment' => 'fire-risk-assessments',
        'landlords' => 'landlords',
        'packages' => 'packages',
        'pricing' => 'pricing',
    ];
    return $map[$from] ?? null;
}

function icomplyComplianceBundleCrossSellHtml(string $from): string
{
    $key = icomplyComplianceBundleCrossSellKey($from);
    if ($key === null) {
        return '';
    }
    $bundle = icomplyComplianceBundle();
    $copy = $bundle['cross_sell'][$key];
    $href = htmlspecialchars(icomplyComplianceBundleHref(), ENT_QUOTES, 'UTF-8');
    $quote = htmlspecialchars(icomplyComplianceBundleHref() . '#quote', ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($bundle['price_label'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($bundle['name'], ENT_QUOTES, 'UTF-8');
    $alias = htmlspecialchars($bundle['alias'], ENT_QUOTES, 'UTF-8');
    $eyebrow = htmlspecialchars($copy['eyebrow'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8');
    $body = htmlspecialchars($copy['body'], ENT_QUOTES, 'UTF-8');

    return '<aside class="compliance-bundle-crosssell" data-compliance-bundle="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '">'
        . '<div class="max-w-7xl mx-auto px-6 py-8">'
        . '<div class="rounded-3xl bg-[#0B1F3A] text-white p-7 md:p-10 grid lg:grid-cols-[1fr_auto] gap-8 items-center">'
        . '<div>'
        . '<p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">' . $eyebrow . '</p>'
        . '<h2 class="text-2xl md:text-3xl font-semibold tracking-tight mt-2">' . $title . '</h2>'
        . '<p class="mt-3 text-white/80 max-w-2xl">' . $body . '</p>'
        . '<p class="mt-3 text-sm text-white/60">Standalone EICR, gas and FRA visits stay quoted to the property. ' . $label . ' is only the ' . $name . '.</p>'
        . '<div class="mt-6 flex flex-wrap gap-3">'
        . '<a href="' . $quote . '" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Book the ' . $name . '</a>'
        . '<a href="' . $href . '" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">See what ' . $label . ' includes</a>'
        . '</div>'
        . '</div>'
        . '<div class="lg:text-right">'
        . '<div class="text-xs uppercase tracking-wider text-white/60">' . $alias . '</div>'
        . '<div class="text-5xl font-semibold tracking-tight text-white">' . $label . '</div>'
        . '<div class="text-sm text-white/70 mt-1">published pack price</div>'
        . '</div>'
        . '</div></div></aside>';
}

/** @return array<string,mixed> */
function icomplyComplianceBundleOfferJsonLd(string $canonicalUrl): array
{
    $bundle = icomplyComplianceBundle();
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Offer',
        'name' => $bundle['name'],
        'alternateName' => $bundle['alias'],
        'description' => $bundle['scope'],
        'url' => $canonicalUrl,
        'price' => (string)$bundle['price_gbp'],
        'priceCurrency' => 'GBP',
        'availability' => 'https://schema.org/InStock',
        'seller' => [
            '@type' => 'LocalBusiness',
            'name' => defined('SITE_NAME') ? SITE_NAME : 'Icomply Property Services',
        ],
    ];
}
