#!/usr/bin/env php
<?php
/**
 * Build static iComply Supplies hubs under /shop/ from the live Shopify dump.
 *
 * Source: https://shop.icomplypropertyservices.co.uk/products.json
 * Does not invent products, prices, stock, or images.
 *
 * Usage:
 *   php website/bin/build-shop-hubs.php
 *   php website/bin/build-shop-hubs.php --out=website/shop
 *   php website/bin/build-shop-hubs.php --out=dist/shop
 */
declare(strict_types=1);

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $opts = getopt('', ['out::', 'help']);
    if (isset($opts['help'])) {
        echo "Usage: php website/bin/build-shop-hubs.php [--out=website/shop]\n";
        exit(0);
    }
    $websiteRoot = dirname(__DIR__);
    $out = $opts['out'] ?? ($websiteRoot . '/shop');
    if ($out !== '' && $out[0] !== '/') {
        $out = dirname($websiteRoot) . '/' . ltrim($out, '/');
    }
    try {
        $summary = icomplyBuildShopHubs($out);
        fwrite(STDERR, "shop hubs OK → {$out}\n");
        fwrite(STDERR, sprintf(
            "live=%d fire=%d electrical=%d security=%d gas=%d fetched=%s\n",
            $summary['total'],
            $summary['counts']['fire'],
            $summary['counts']['electrical'],
            $summary['counts']['security'],
            $summary['counts']['gas'],
            $summary['fetched_at']
        ));
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "shop hubs FAILED: " . $e->getMessage() . "\n");
        exit(1);
    }
}

/**
 * @return array{total:int,counts:array<string,int>,fetched_at:string}
 */
function icomplyBuildShopHubs(string $outDir): array
{
    $products = icomplyFetchShopifyProducts();
    if ($products === []) {
        throw new RuntimeException('Shopify products.json returned no products');
    }

    $hubs = [
        'fire' => [],
        'electrical' => [],
        'security' => [],
        'gas' => [],
    ];
    foreach ($products as $product) {
        if (!is_array($product)) {
            continue;
        }
        $hub = icomplyClassifyShopProduct($product);
        $hubs[$hub][] = $product;
    }

    foreach ($hubs as $slug => $list) {
        usort($hubs[$slug], static function (array $a, array $b): int {
            return strcasecmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
        });
    }

    $counts = [
        'fire' => count($hubs['fire']),
        'electrical' => count($hubs['electrical']),
        'security' => count($hubs['security']),
        'gas' => count($hubs['gas']),
    ];
    if (array_sum($counts) !== count($products)) {
        throw new RuntimeException('Hub classification lost or duplicated products');
    }

    $fetchedAt = gmdate('c');
    $ctx = [
        'counts' => $counts,
        'total' => count($products),
        'fetched_at' => $fetchedAt,
        'source' => 'https://shop.icomplypropertyservices.co.uk/products.json',
        'shop_url' => 'https://shop.icomplypropertyservices.co.uk/',
        'enquire_email' => 'icomplypropertyservices@gmail.com',
    ];

    if (!is_dir($outDir) && !mkdir($outDir, 0755, true) && !is_dir($outDir)) {
        throw new RuntimeException('Cannot create ' . $outDir);
    }
    foreach (['fire', 'electrical', 'security', 'gas'] as $slug) {
        $dir = $outDir . '/' . $slug;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create ' . $dir);
        }
    }

    file_put_contents($outDir . '/index.html', icomplyRenderShopIndex($ctx));
    file_put_contents($outDir . '/fire/index.html', icomplyRenderShopCategory('fire', $hubs['fire'], $ctx));
    file_put_contents($outDir . '/electrical/index.html', icomplyRenderShopCategory('electrical', $hubs['electrical'], $ctx));
    file_put_contents($outDir . '/security/index.html', icomplyRenderShopCategory('security', $hubs['security'], $ctx));
    file_put_contents($outDir . '/gas/index.html', icomplyRenderShopCategory('gas', $hubs['gas'], $ctx));

    return [
        'total' => count($products),
        'counts' => $counts,
        'fetched_at' => $fetchedAt,
    ];
}

/**
 * @return list<array<string,mixed>>
 */
function icomplyFetchShopifyProducts(): array
{
    $all = [];
    $page = 1;
    while ($page <= 20) {
        $url = 'https://shop.icomplypropertyservices.co.uk/products.json?limit=250&page=' . $page;
        $json = icomplyHttpGetJson($url);
        $chunk = $json['products'] ?? null;
        if (!is_array($chunk) || $chunk === []) {
            break;
        }
        foreach ($chunk as $product) {
            if (is_array($product)) {
                $all[] = $product;
            }
        }
        if (count($chunk) < 250) {
            break;
        }
        $page++;
    }
    return $all;
}

/**
 * @return array<string,mixed>
 */
function icomplyHttpGetJson(string $url): array
{
    $body = '';
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: icomply-shop-hubs/1.0 (+https://icomplypropertyservices.co.uk/shop/)',
            ],
        ]);
        $body = (string)curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === '' || $status >= 400) {
            throw new RuntimeException("GET {$url} failed HTTP {$status} {$err}");
        }
    } else {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 30,
                'header' => "Accept: application/json\r\nUser-Agent: icomply-shop-hubs/1.0\r\n",
            ],
        ]);
        $body = (string)@file_get_contents($url, false, $ctx);
        if ($body === '') {
            throw new RuntimeException("GET {$url} failed");
        }
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON from {$url}");
    }
    return $data;
}

/**
 * Classify one live Shopify product into a supplies hub.
 * Security brands win first. Electrical is a thin explicit set. Gas only if
 * the product is clearly gas. Everything else is Fire.
 *
 * @param array<string,mixed> $product
 */
function icomplyClassifyShopProduct(array $product): string
{
    $vendor = trim((string)($product['vendor'] ?? ''));
    $type = trim((string)($product['product_type'] ?? ''));
    $blob = icomplyShopProductBlob($product);

    $securityVendors = ['came', 'videx', 'bell system'];
    if (in_array(strtolower($vendor), $securityVendors, true)) {
        return 'security';
    }

    $securityTypes = [
        'swing gate operator', 'sliding gate operator', 'underground gate operator',
        'swing gate kit', 'sliding gate kit', 'barrier', 'garage door operator',
        'video intercom', 'audio intercom', 'gsm intercom', 'ip intercom',
        'intercom module', 'automation accessory', 'safety photocells',
        'remote control',
    ];
    if (in_array(strtolower($type), $securityTypes, true)) {
        return 'security';
    }
    if (preg_match('/\b(gate operator|garage door|video intercom|audio intercom|door entry|barrier arm|photocell)\b/', $blob)) {
        return 'security';
    }

    $electricalVendors = ['yuasa', 'prysmian', 'linian'];
    if (in_array(strtolower($vendor), $electricalVendors, true)) {
        return 'electrical';
    }

    $electricalTypes = [
        'battery', 'cable clip', 'fire cable', 'remote indicator',
        'disabled refuge panel', 'disabled refuge outstation', 'fire telephone',
    ];
    if (in_array(strtolower($type), $electricalTypes, true)) {
        return 'electrical';
    }
    if (preg_match('/\b(sigtel|disabled refuge|evcs)\b/', $blob)) {
        return 'electrical';
    }

    if (preg_match('/\b(cp12|gas safety certificate|gas boiler|gas meter|landlord gas|lpg)\b/', $blob)
        && !preg_match('/\b(fire|smoke|aov|detector|intercom|gate|came)\b/', $blob)) {
        return 'gas';
    }
    if (preg_match('/^gas(\s|$)/i', $type) || preg_match('/\bgas\b/i', $vendor)) {
        return 'gas';
    }

    return 'fire';
}

/**
 * @param array<string,mixed> $product
 */
function icomplyShopProductBlob(array $product): string
{
    $tags = $product['tags'] ?? [];
    if (is_string($tags)) {
        $tagText = $tags;
    } elseif (is_array($tags)) {
        $tagText = implode(' ', array_map('strval', $tags));
    } else {
        $tagText = '';
    }
    $parts = [
        (string)($product['title'] ?? ''),
        (string)($product['handle'] ?? ''),
        (string)($product['product_type'] ?? ''),
        (string)($product['vendor'] ?? ''),
        $tagText,
        icomplyPlainText((string)($product['body_html'] ?? '')),
    ];
    return strtolower(trim(implode(' ', $parts)));
}

function icomplyPlainText(string $html): string
{
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
    return trim($text);
}

/**
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopIndex(array $ctx): string
{
    $counts = $ctx['counts'];
    $cards = [
        ['fire', 'Fire', 'Panels, detectors, AOV, wireless fire and the rest of the live fire catalogue.', (int)$counts['fire']],
        ['electrical', 'Electrical', 'Thin live set: batteries and PSU cells, fire cable, clips, SigTEL / disabled refuge, remote LED indicators.', (int)$counts['electrical']],
        ['security', 'Security', 'CAME, Videx and Bell System — gates, barriers, garage doors, intercoms and automation.', (int)$counts['security']],
        ['gas', 'Gas', 'No live gas SKUs in the Shopify dump. Enquire or POA only — nothing is invented here.', (int)$counts['gas']],
    ];

    $cardHtml = '';
    foreach ($cards as [$slug, $label, $blurb, $count]) {
        $countLabel = $count === 1 ? '1 live product' : ($count . ' live products');
        $cardHtml .= '<a class="hub-card" href="/shop/' . $slug . '/">'
            . '<div class="hub-count">' . $count . '</div>'
            . '<h2>' . icomplyH($label) . '</h2>'
            . '<p>' . icomplyH($blurb) . '</p>'
            . '<span class="go">' . icomplyH($countLabel) . ' →</span>'
            . '</a>';
    }

    $hero = [
        'kicker' => 'iComply Supplies',
        'title' => 'Trade supplies, four honest hubs',
        'lead' => 'Fire, electrical, security and gas landings on this site. Titles, SKUs and GBP prices come from the live Shopify catalogue. Checkout stays on shop.icomplypropertyservices.co.uk — we do not invent stock or images.',
    ];
    $main = '<section class="section"><div class="wrap">'
        . '<h2>Browse by category</h2>'
        . '<p class="lead">Counts below are from the public Shopify products dump used to build these pages. Electrical is the thin real set only. Gas stays empty until a live gas SKU exists.</p>'
        . '<div class="hub-grid">' . $cardHtml . '</div>'
        . icomplyShopSourceNote($ctx)
        . '</div></section>';

    return icomplyShopPage('index', $hero, $main, $ctx);
}

/**
 * @param list<array<string,mixed>> $products
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopCategory(string $slug, array $products, array $ctx): string
{
    $meta = [
        'fire' => [
            'title' => 'Fire supplies',
            'lead' => 'Live fire catalogue from Shopify: control panels, detectors, AOV, wireless fire brands and related devices. Prices are shown as published on the store.',
        ],
        'electrical' => [
            'title' => 'Electrical supplies',
            'lead' => 'A thin live set only — batteries and PSU cells, fire cable, cable clips, SigTEL / disabled refuge, remote LED indicators, plus Yuasa, Prysmian and Linian where they appear in the dump. Nothing is padded.',
        ],
        'security' => [
            'title' => 'Security supplies',
            'lead' => 'CAME, Videx and Bell System from the live dump — gates, barriers, garage doors, intercoms and automation. Order on Shopify or enquire for a trade quote.',
        ],
        'gas' => [
            'title' => 'Gas supplies',
            'lead' => 'The live Shopify dump has no product that is clearly gas. This hub stays empty on purpose. Ask for a quote or POA — we will not invent gas SKUs.',
        ],
    ];
    $info = $meta[$slug];
    $count = count($products);
    $countLabel = $count === 1 ? '1 live product' : ($count . ' live products');

    $hero = [
        'kicker' => 'iComply Supplies · ' . $info['title'],
        'title' => $info['title'],
        'lead' => $info['lead'],
        'meta' => $countLabel . ' from the Shopify catalogue.',
    ];

    $grid = '';
    if ($products === []) {
        $grid = '<div class="empty-card">'
            . '<h2>Nothing to list yet</h2>'
            . '<p>No live gas products were returned by <code>products.json</code>. Use enquire or POA. When a real gas SKU is published on Shopify it will appear here.</p>'
            . '<div class="cta-row">'
            . '<a class="btn btn-orange" href="mailto:' . icomplyH((string)$ctx['enquire_email']) . '?subject=' . rawurlencode('Gas supplies enquiry / POA') . '">Enquire / POA</a>'
            . '<a class="btn btn-ghost" href="https://icomplypropertyservices.co.uk/contact">Free quote</a>'
            . '</div></div>';
    } else {
        $grid = '<div class="product-grid">';
        foreach ($products as $product) {
            $grid .= icomplyRenderProductCard($product, (string)$ctx['enquire_email']);
        }
        $grid .= '</div>';
    }

    $main = '<section class="section"><div class="wrap">'
        . $grid
        . icomplyShopSourceNote($ctx)
        . '</div></section>';

    return icomplyShopPage($slug, $hero, $main, $ctx);
}

/**
 * @param array<string,mixed> $product
 */
function icomplyRenderProductCard(array $product, string $email): string
{
    $title = trim((string)($product['title'] ?? 'Untitled product'));
    $handle = trim((string)($product['handle'] ?? ''));
    $vendor = trim((string)($product['vendor'] ?? ''));
    $type = trim((string)($product['product_type'] ?? ''));
    $orderUrl = $handle !== ''
        ? 'https://shop.icomplypropertyservices.co.uk/products/' . rawurlencode($handle)
        : 'https://shop.icomplypropertyservices.co.uk/';
    $image = icomplyShopProductImage($product);
    $variants = is_array($product['variants'] ?? null) ? $product['variants'] : [];
    $prices = [];
    $skus = [];
    $variantItems = '';
    $multi = count($variants) > 1;

    foreach ($variants as $variant) {
        if (!is_array($variant)) {
            continue;
        }
        $vTitle = trim((string)($variant['title'] ?? ''));
        $sku = trim((string)($variant['sku'] ?? ''));
        $priceRaw = $variant['price'] ?? null;
        $priceLabel = icomplyFormatShopifyPrice($priceRaw);
        if ($priceLabel !== null) {
            $prices[] = (float)$priceRaw;
        }
        if ($sku !== '') {
            $skus[] = $sku;
        }
        if ($multi) {
            $skuBit = $sku !== '' ? $sku : 'SKU coming soon';
            $priceBit = $priceLabel ?? 'Price coming soon';
            $nameBit = ($vTitle !== '' && strcasecmp($vTitle, 'Default Title') !== 0) ? $vTitle : 'Variant';
            $variantItems .= '<li>' . icomplyH($nameBit) . ' · ' . icomplyH($skuBit) . ' · ' . icomplyH($priceBit) . '</li>';
        }
    }

    $skuLine = $skus === [] ? 'SKU coming soon' : 'SKU ' . implode(' · ', array_slice($skus, 0, 4));
    if (count($skus) > 4) {
        $skuLine .= ' · +' . (count($skus) - 4) . ' more';
    }

    $priceLine = 'Price on application';
    if ($prices !== []) {
        $min = min($prices);
        $max = max($prices);
        $priceLine = $min === $max
            ? icomplyFormatShopifyPrice((string)$min)
            : ('From ' . icomplyFormatShopifyPrice((string)$min));
    }

    $specs = icomplyShopSpecs($product, $vendor, $type);
    $media = $image !== ''
        ? '<img src="' . icomplyH($image) . '" alt="' . icomplyH($title) . '" loading="lazy" width="480" height="360">'
        : '<div class="coming-soon">Image coming soon</div>';

    $enquire = 'mailto:' . rawurlencode($email) . '?subject=' . rawurlencode('Enquire: ' . $title);

    $html = '<article class="product-card">'
        . '<div class="product-media">' . $media . '</div>'
        . '<div class="product-body">'
        . '<h2>' . icomplyH($title) . '</h2>'
        . '<div class="sku">' . icomplyH($skuLine) . '</div>'
        . '<div class="price">' . icomplyH((string)$priceLine) . '</div>'
        . ($specs !== '' ? '<p class="specs">' . icomplyH($specs) . '</p>' : '')
        . ($variantItems !== '' ? '<ul class="variants">' . $variantItems . '</ul>' : '')
        . '<div class="cta-row">'
        . '<a class="btn btn-orange" href="' . icomplyH($orderUrl) . '" rel="noopener">Order on Shopify</a>'
        . '<a class="btn btn-ghost" href="' . icomplyH($enquire) . '">Enquire</a>'
        . '</div></div></article>';

    return $html;
}

/**
 * @param array<string,mixed> $product
 */
function icomplyShopProductImage(array $product): string
{
    $images = $product['images'] ?? [];
    if (is_array($images)) {
        foreach ($images as $image) {
            $src = '';
            if (is_array($image)) {
                $src = (string)($image['src'] ?? '');
            } elseif (is_string($image)) {
                $src = $image;
            }
            if (str_contains($src, 'cdn.shopify.com')) {
                return $src;
            }
        }
    }
    $single = $product['image'] ?? null;
    if (is_array($single) && str_contains((string)($single['src'] ?? ''), 'cdn.shopify.com')) {
        return (string)$single['src'];
    }
    return '';
}

function icomplyFormatShopifyPrice(mixed $price): ?string
{
    if ($price === null || $price === '') {
        return null;
    }
    if (!is_numeric($price)) {
        return null;
    }
    return '£' . number_format((float)$price, 2, '.', ',');
}

/**
 * @param array<string,mixed> $product
 */
function icomplyShopSpecs(array $product, string $vendor, string $type): string
{
    $bits = array_values(array_filter([$type, $vendor !== '' ? $vendor : '']));
    $head = implode(' · ', $bits);
    $body = icomplyPlainText((string)($product['body_html'] ?? ''));
    $body = preg_replace('/Prices? ex VAT.*$/i', '', $body) ?? $body;
    $body = trim($body);
    if ($body !== '') {
        if (strlen($body) > 180) {
            $cut = substr($body, 0, 180);
            $stop = strrpos($cut, '. ');
            $body = trim($stop !== false ? substr($cut, 0, $stop + 1) : rtrim($cut, ' ,;:') . '…');
        }
    }
    if ($head !== '' && $body !== '') {
        return $head . '. ' . $body;
    }
    return $head !== '' ? $head : $body;
}

/**
 * @param array<string,mixed> $ctx
 */
function icomplyShopSourceNote(array $ctx): string
{
    return '<p class="note">Source: <a href="' . icomplyH((string)$ctx['source']) . '">' . icomplyH((string)$ctx['source']) . '</a>'
        . ' · fetched ' . icomplyH((string)$ctx['fetched_at'])
        . ' · ' . (int)$ctx['total'] . ' live products classified into these hubs. '
        . 'GBP as published on Shopify — VAT is not invented here. '
        . 'Checkout remains on <a href="' . icomplyH((string)$ctx['shop_url']) . '">shop.icomplypropertyservices.co.uk</a>.</p>';
}

/**
 * @param array{kicker:string,title:string,lead:string,meta?:string} $hero
 * @param array<string,mixed> $ctx
 */
function icomplyShopPage(string $current, array $hero, string $main, array $ctx): string
{
    $titles = [
        'index' => 'iComply Supplies | Fire, electrical, security and gas hubs',
        'fire' => 'Fire supplies | iComply Supplies',
        'electrical' => 'Electrical supplies | iComply Supplies',
        'security' => 'Security supplies | iComply Supplies',
        'gas' => 'Gas supplies | iComply Supplies',
    ];
    $desc = [
        'index' => 'iComply Supplies category hubs built from the live Shopify catalogue. Fire, electrical, security and gas. Checkout stays on the Shopify shop host.',
        'fire' => 'Live fire supplies from the iComply Shopify catalogue — panels, detectors, AOV and wireless fire. Order on Shopify or enquire.',
        'electrical' => 'Thin live electrical set from Shopify: batteries, fire cable, clips, SigTEL / disabled refuge and remote LED indicators.',
        'security' => 'CAME, Videx and Bell System supplies from the live Shopify catalogue — gates, barriers, garage doors, intercoms and automation.',
        'gas' => 'Gas supplies hub. No live gas SKUs in the Shopify dump. Enquire or POA only.',
    ];
    $path = $current === 'index' ? '/shop/' : '/shop/' . $current . '/';
    $canonical = 'https://icomplypropertyservices.co.uk' . $path;
    $pageTitle = $titles[$current];
    $pageDesc = $desc[$current];

    $nav = icomplyShopNav($current);

    return '<!DOCTYPE html>' . "\n"
        . '<html lang="en-GB">'
        . '<head>'
        . '<meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . icomplyH($pageTitle) . '</title>'
        . '<meta name="description" content="' . icomplyH($pageDesc) . '">'
        . '<meta name="theme-color" content="#0B1F3A">'
        . '<link rel="canonical" href="' . icomplyH($canonical) . '">'
        . '<link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">'
        . '<link rel="icon" href="/favicon.ico" sizes="any">'
        . '<link rel="stylesheet" href="/shop/assets/shop.css">'
        . '</head>'
        . '<body>'
        . '<a class="skip" href="#main">Skip to content</a>'
        . '<header class="site-header"><div class="wrap header-inner">'
        . '<a class="brand" href="/shop/">'
        . '<img src="/shop/assets/logo.svg" width="36" height="36" alt="iComply logo">'
        . '<span class="brand-name">iComply<span>Supplies</span></span>'
        . '</a>'
        . '<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="shop-nav">Menu</button>'
        . $nav
        . '</div></header>'
        . '<section class="hero"><div class="wrap">'
        . '<p class="hero-kicker">' . icomplyH($hero['kicker']) . '</p>'
        . '<h1>' . icomplyH($hero['title']) . '</h1>'
        . '<p>' . icomplyH($hero['lead']) . '</p>'
        . (isset($hero['meta']) ? '<p class="hero-meta">' . icomplyH($hero['meta']) . '</p>' : '')
        . '</div></section>'
        . '<main id="main">' . $main . '</main>'
        . '<footer class="site-footer"><div class="wrap footer-inner">'
        . '<div>iComply Property Services · 17 Woodlands Park Road, Offerton, Stockport SK2 5DE</div>'
        . '<div><a href="https://shop.icomplypropertyservices.co.uk/">Shopify checkout</a> · <a href="https://icomplypropertyservices.co.uk/contact">Free quote</a> · <a href="mailto:' . icomplyH((string)$ctx['enquire_email']) . '">Enquire</a></div>'
        . '</div></footer>'
        . '<script>'
        . 'document.querySelector(".menu-toggle")?.addEventListener("click",function(){var n=document.getElementById("shop-nav");var open=n.classList.toggle("is-open");this.setAttribute("aria-expanded",open?"true":"false");});'
        . '</script>'
        . '</body></html>' . "\n";
}

function icomplyShopNav(string $current): string
{
    $items = [
        ['Services', 'https://icomplypropertyservices.co.uk/pages/services', false, ''],
        ['Products', 'https://icomplypropertyservices.co.uk/products', false, ''],
        ['Shop', 'https://shop.icomplypropertyservices.co.uk/', true, 'shop-live'],
        ['Supplies', '/shop/', false, 'index'],
        ['Fire', '/shop/fire/', false, 'fire'],
        ['Electrical', '/shop/electrical/', false, 'electrical'],
        ['Security', '/shop/security/', false, 'security'],
        ['Gas', '/shop/gas/', false, 'gas'],
        ['Free quote', 'https://icomplypropertyservices.co.uk/contact', false, 'quote'],
    ];
    $html = '<nav id="shop-nav" class="nav" aria-label="Supplies">';
    foreach ($items as [$label, $href, $shopLive, $key]) {
        $class = [];
        if ($shopLive) {
            $class[] = 'shop-live';
        }
        if ($key === 'quote') {
            $class[] = 'quote';
        }
        $attr = $class !== [] ? ' class="' . implode(' ', $class) . '"' : '';
        $cur = ($key !== '' && $key === $current) ? ' aria-current="page"' : '';
        $html .= '<a href="' . icomplyH($href) . '"' . $attr . $cur . '>' . icomplyH($label) . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

function icomplyH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Copy static shop files (never PHP source) from website/shop → dest.
 */
function icomplyCopyShopStatic(string $src, string $dest): void
{
    if (!is_dir($src)) {
        return;
    }
    if (!is_dir($dest) && !mkdir($dest, 0755, true) && !is_dir($dest)) {
        throw new RuntimeException('Cannot mkdir ' . $dest);
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    $srcLen = strlen($src);
    foreach ($it as $file) {
        $rel = substr($file->getPathname(), $srcLen);
        $target = $dest . $rel;
        if ($file->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                throw new RuntimeException('Cannot mkdir ' . $target);
            }
            continue;
        }
        $ext = strtolower((string)$file->getExtension());
        if ($ext === 'php') {
            continue;
        }
        $targetDir = dirname($target);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Cannot mkdir ' . $targetDir);
        }
        if ($ext === 'html' && function_exists('icomplyApplyGasLegalHtml')) {
            $raw = (string)file_get_contents($file->getPathname());
            file_put_contents($target, icomplyApplyGasLegalHtml($raw));
        } else {
            copy($file->getPathname(), $target);
        }
    }
}
