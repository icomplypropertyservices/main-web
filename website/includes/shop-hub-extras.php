<?php
/** Breadcrumbs, call/WhatsApp CTA, FAQ + JSON-LD for the static /shop hubs (QM #158 item 4). */
function icomplyShopHubLabel(string $current): string
{
    return ['index' => 'Supplies', 'fire' => 'Fire supplies', 'electrical' => 'Electrical supplies', 'security' => 'Security supplies', 'gas' => 'Gas supplies'][$current] ?? 'Supplies';
}

function icomplyShopHubFaqs(string $current): array
{
    $topic = ['index' => 'trade supplies', 'fire' => 'fire safety products', 'electrical' => 'electrical products', 'security' => 'security products', 'gas' => 'gas products'][$current] ?? 'trade supplies';
    $fit = $current === 'gas' ? 'and installation is carried out by Gas Safe registered engineers' : 'and our engineers can supply and fit';
    return [
        ['Can I buy ' . $topic . ' and have them installed?', 'Yes. Order through the Shopify checkout, or call or WhatsApp 07517 806082, ' . $fit . '. Installation is priced on application after a short scope.'],
        ['How current are the listed prices?', 'Prices are shown as published on the Shopify store at build time. The checkout price is the one that applies.'],
        ['Do you deliver across the UK?', 'Most items ship UK-wide. Delivery options and costs are shown at checkout; email icomplypropertyservices@gmail.com for bulk or trade orders.'],
        ['Can you help me choose the right product?', 'Yes. Tell us the property type and what the system must do, and we will suggest compatible parts before you order.'],
    ];
}

function icomplyShopHubBreadcrumbs(string $current): string
{
    $items = [['Home', 'https://icomplypropertyservices.co.uk/'], ['Shop', 'https://icomplypropertyservices.co.uk/shop/']];
    if ($current !== 'index') {
        $items[] = [icomplyShopHubLabel($current), 'https://icomplypropertyservices.co.uk/shop/' . $current . '/'];
    }
    $html = '<nav class="shop-breadcrumbs" aria-label="Breadcrumb" style="font-size:.85rem;margin:0 0 .75rem"><ol style="display:flex;gap:.4rem;list-style:none;padding:0;margin:0;flex-wrap:wrap">';
    $last = count($items) - 1;
    foreach ($items as $i => [$name, $url]) {
        $html .= '<li>' . ($i === $last ? '<span aria-current="page">' . htmlspecialchars($name) . '</span>' : '<a href="' . htmlspecialchars($url) . '" style="color:inherit">' . htmlspecialchars($name) . '</a> ›') . '</li>';
    }
    return $html . '</ol></nav>';
}

function icomplyShopHubCta(): string
{
    return '<p class="shop-hub-cta" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem">'
        . '<a href="tel:07517806082" style="background:#FF6B00;color:#fff;padding:.7rem 1.2rem;border-radius:999px;font-weight:700;text-decoration:none">Call 07517 806082</a>'
        . '<a href="https://wa.me/447517806082" style="background:#0B1F3A;border:2px solid #FF6B00;color:#fff;padding:.7rem 1.2rem;border-radius:999px;font-weight:700;text-decoration:none">WhatsApp us</a></p>';
}

function icomplyShopHubFaqSection(string $current): string
{
    $faqs = icomplyShopHubFaqs($current);
    $html = '<section class="section shop-hub-faq"><div class="wrap"><h2>' . htmlspecialchars(icomplyShopHubLabel($current)) . ': frequently asked questions</h2>';
    $ld = [];
    foreach ($faqs as [$q, $a]) {
        $html .= '<h3>' . htmlspecialchars($q) . '</h3><p>' . htmlspecialchars($a) . '</p>';
        $ld[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
    }
    $crumbs = [['Home', 'https://icomplypropertyservices.co.uk/'], ['Shop', 'https://icomplypropertyservices.co.uk/shop/']];
    if ($current !== 'index') {
        $crumbs[] = [icomplyShopHubLabel($current), 'https://icomplypropertyservices.co.uk/shop/' . $current . '/'];
    }
    $bl = [];
    foreach ($crumbs as $i => [$n, $u]) {
        $bl[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $n, 'item' => $u];
    }
    $json = json_encode(['@context' => 'https://schema.org', '@graph' => [['@type' => 'FAQPage', 'mainEntity' => $ld], ['@type' => 'BreadcrumbList', 'itemListElement' => $bl]]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return $html . '</div></section><script type="application/ld+json">' . $json . '</script>';
}

/** Idempotent patch of an already-built hub HTML file. */
function icomplyShopHubPatchHtml(string $html, string $current): string
{
    if (str_contains($html, 'shop-hub-cta')) {
        return icomplyShopHubPatchGuide($html, $current);
    }
    $html = preg_replace('#<p class="hero-kicker">#', icomplyShopHubBreadcrumbs($current) . '<p class="hero-kicker">', $html, 1);
    $html = preg_replace('#</div></section><main id="main">#', icomplyShopHubCta() . '</div></section><main id="main">', $html, 1);
    return icomplyShopHubPatchGuide((string)preg_replace('#</main>#', icomplyShopHubFaqSection($current) . '</main>', $html, 1), $current);
}

/** QM #158 r2: extra buying guidance so /shop/ and /shop/gas/ clear 800 prose words. */
function icomplyShopHubGuide(string $current): string
{
    $g = [
        'index' => [
            'How to use the iComply trade shop',
            [
                'The shop is split into four hubs: fire, electrical, security and gas. Each hub lists products from our Shopify catalogue with the price published on the store, the brand, and the main specification, so you can compare like for like before you order.',
                'If you are a landlord, letting agent or facilities manager, the most common reason to buy is to replace a failed part found at an inspection: a smoke or heat detector, an emergency light fitting, a consumer unit component or a door entry handset. Check the make and model on the existing unit first. Matching the brand usually avoids rewiring and keeps the system under one maintenance regime.',
                'Contractors and maintenance teams often buy in small batches for planned work. If you need more than a handful of one item, or a mixed order for a refurbishment, email icomplypropertyservices@gmail.com with the list and the delivery postcode and we will confirm stock and lead times before you pay.',
                'Not sure which product suits the building? Tell us the property type, how many floors and rooms are involved, and what the system has to do. We will suggest compatible parts and explain any installation or certification the work needs. Supply-only orders are welcome, and we can also arrange fitting by our own engineers.',
                'Installation, testing and certification are quoted separately and priced on application, because they depend on access, cabling routes and the condition of the existing system. Typical inspection prices are shown on our service pages: an EICR from £160, and a CP12 gas safety record at £85.',
                'Fire products cover control panels, detectors, sounders, call points, emergency lighting and smoke ventilation parts. Electrical products cover consumer units, protective devices, accessories and test equipment. Security products cover CCTV, intruder alarms, access control and door entry. Gas products cover boilers, flues and heating controls.',
                'Where a product needs commissioning or a certificate after fitting, such as a fire alarm panel or a consumer unit, we explain what paperwork the building will need and who can issue it. That avoids buying a part that cannot be signed off.',
                'Returns follow the terms shown at checkout. Keep products sealed and in their original packaging until you are sure they match, and check fittings against the existing unit before opening a sealed item.',
                'Every hub page includes breadcrumbs, a short FAQ and direct contact buttons. Call or WhatsApp 07517 806082 if you want to talk an order through with someone who fits these products for a living.',
            ],
        ],
        'gas' => [
            'Buying gas products: what to check first',
            [
                'The gas hub lists boilers, flues, controls and related parts from our Shopify catalogue. Prices are shown as published on the store, and the checkout price is the one that applies.',
                'Any work on a gas appliance, flue or pipework must be carried out by Gas Safe registered engineers. Buying a part does not make it legal to fit it yourself. If you are a landlord, the appliance also needs an annual gas safety check, and our CP12 gas safety record is £85.',
                'Before you order a boiler or flue, check the existing model, the flue type and route, the fuel type and the output the property needs. A combi boiler suits most flats and smaller houses; system and regular boilers suit homes with a hot water cylinder or higher demand. Flue kits are model-specific, so match the manufacturer and the length before ordering.',
                'Controls such as programmers, room thermostats and smart controls are usually compatible across brands, but wiring centres and older timers can differ. Send us a photo of the existing control and its wiring label if you are not sure.',
                'For supply and installation together, we survey first and send a written quote. Installation is priced on application because it depends on the location, the flue route and any pipework changes. We will also tell you if a part can be repaired rather than replaced.',
                'Carbon monoxide alarms are listed in the fire and safety range rather than here, but they belong with every gas appliance. Landlords must fit one in any room with a fixed combustion appliance, and we recommend one near every boiler regardless of the regulations.',
                'If you are replacing a boiler because of repeated faults, ask for a repair assessment first. Many faults come down to a pump, fan, diverter valve or printed circuit board, and a repair can be cheaper than a full replacement. Our engineers will tell you honestly which option makes sense for the age and condition of the appliance.',
                'When a new boiler is fitted, the installer registers it with the manufacturer and Building Control, and the warranty usually depends on an annual service. Keep the benchmark checklist and the commissioning record with the property file, because buyers, tenants and insurers often ask for them.',
                'Heating controls can make a real difference to running costs. A programmable room thermostat, thermostatic radiator valves and a correctly set boiler flow temperature are often the cheapest upgrades a landlord or homeowner can make, and they are simple to add during a service visit.',
                'If the property has a hot water cylinder, check its age, insulation and the type of cylinder thermostat before choosing a new boiler. An unvented cylinder needs its own annual check by a qualified engineer, and pairing the wrong boiler with an old cylinder can cause poor hot water performance.',
                'Delivery options and costs are shown at checkout. Large items such as boilers ship on a pallet and need someone at the address to sign for them, so choose a delivery date when the property is occupied or the site is open.',
                'For trade or bulk orders, email icomplypropertyservices@gmail.com with the list and the delivery postcode. For anything urgent, call or WhatsApp 07517 806082.',
            ],
        ],
    ];
    if (!isset($g[$current])) {
        return '';
    }
    [$h, $ps] = $g[$current];
    $out = '<section class="section shop-hub-guide"><div class="wrap"><h2>' . htmlspecialchars($h) . '</h2>';
    foreach ($ps as $p) {
        $out .= '<p>' . htmlspecialchars($p) . '</p>';
    }
    return $out . '</div></section>';
}

function icomplyShopHubPatchGuide(string $html, string $current): string
{
    if (str_contains($html, 'shop-hub-guide')) {
        return $html;
    }
    $g = icomplyShopHubGuide($current);
    if ($g === '') {
        return $html;
    }
    $pos = strpos($html, '<section class="section shop-hub-faq">');
    return $pos === false ? $html : substr($html, 0, $pos) . $g . substr($html, $pos);
}
