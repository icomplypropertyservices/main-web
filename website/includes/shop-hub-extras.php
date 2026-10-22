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
        ['Are the prices on this page current?', 'Prices are shown as published on the Shopify store at build time. The checkout price is the one that applies.'],
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
        return $html;
    }
    $html = preg_replace('#<p class="hero-kicker">#', icomplyShopHubBreadcrumbs($current) . '<p class="hero-kicker">', $html, 1);
    $html = preg_replace('#</div></section><main id="main">#', icomplyShopHubCta() . '</div></section><main id="main">', $html, 1);
    return preg_replace('#</main>#', icomplyShopHubFaqSection($current) . '</main>', $html, 1);
}
