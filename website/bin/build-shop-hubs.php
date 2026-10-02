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
    icomplyWriteShopCatalogue($outDir, $products, $hubs, $ctx);

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
    $gasCount = (int)$counts['gas'];
    $gasBlurb = $gasCount === 0
        ? 'No live gas SKUs in the Shopify dump. Enquire or POA only — nothing is invented here.'
        : 'Gas products that are clearly gas in the live Shopify dump. Install stays POA.';
    $cards = [
        ['fire', 'Fire', 'Panels, detectors, AOV, wireless fire and the rest of the live fire catalogue.', (int)$counts['fire']],
        ['electrical', 'Electrical', 'Thin live set: batteries and PSU cells, fire cable, clips, SigTEL / disabled refuge, remote LED indicators.', (int)$counts['electrical']],
        ['security', 'Security', 'CAME, Videx and Bell System — gates, barriers, garage doors, intercoms and automation.', (int)$counts['security']],
        ['gas', 'Gas', $gasBlurb, $gasCount],
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
    $browse = '<section class="section" style="padding-top:0"><div class="wrap">'
        . '<h2>Browse the catalogue</h2>'
        . '<p class="lead">Same shelves as the Shopify store — category, manufacturer, product type or line. Empty shelves say coming soon. Nothing is invented.</p>'
        . '<div class="hub-grid browse-hub-grid">'
        . '<a class="hub-card" href="/shop/browse/categories/"><h2>Categories</h2><p>Fire, electrical, security and gas.</p><span class="go">Open categories →</span></a>'
        . '<a class="hub-card" href="/shop/browse/manufacturers/"><h2>Manufacturers</h2><p>Brands that appear on a live Shopify product.</p><span class="go">Shop by brand →</span></a>'
        . '<a class="hub-card" href="/shop/browse/product-types/"><h2>Product types</h2><p>Types published on the live products.</p><span class="go">Browse types →</span></a>'
        . '<a class="hub-card" href="/shop/browse/product-lines/"><h2>Product lines</h2><p>Named ranges that appear in the live titles.</p><span class="go">Browse lines →</span></a>'
        . '</div></div></section>';

    $main = $browse
        . '<section class="section"><div class="wrap">'
        . '<h2>Browse by category</h2>'
        . '<p class="lead">Counts below are from the public Shopify products dump used to build these pages. Electrical stays the thin real set only. A zero count means that shelf is coming soon — no SKU is invented.</p>'
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
            'lead' => 'Gas products from the live Shopify dump. If none are clearly gas, this hub stays empty on purpose — enquire or POA. We will not invent gas SKUs.',
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

    $main = icomplyShopVendorShelfHtml($products)
        . '<section class="section"><div class="wrap">'
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
    $safeHandle = icomplyShopHandle($handle);
    $orderUrl = $safeHandle !== ''
        ? 'https://shop.icomplypropertyservices.co.uk/products/' . rawurlencode($safeHandle)
        : 'https://shop.icomplypropertyservices.co.uk/';
    $localUrl = $safeHandle !== '' ? '/shop/products/' . $safeHandle . '/' : '';
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
    $mediaInner = $image !== ''
        ? '<img src="' . icomplyH($image) . '" alt="' . icomplyH($title) . '" loading="lazy" width="480" height="360">'
        : '<div class="coming-soon">Image coming soon</div>';
    $media = '<div class="product-media">' . $mediaInner . '</div>';
    if ($localUrl !== '') {
        $media = '<a class="product-media-link" href="' . icomplyH($localUrl) . '">' . $media . '</a>';
    }

    $enquire = 'mailto:' . rawurlencode($email) . '?subject=' . rawurlencode('Enquire: ' . $title);
    $titleHtml = $localUrl !== ''
        ? '<h2><a href="' . icomplyH($localUrl) . '">' . icomplyH($title) . '</a></h2>'
        : '<h2>' . icomplyH($title) . '</h2>';

    $html = '<article class="product-card">'
        . $media
        . '<div class="product-body">'
        . $titleHtml
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
    $path = isset($hero['path']) ? (string)$hero['path'] : ($current === 'index' ? '/shop/' : '/shop/' . $current . '/');
    $canonical = 'https://icomplypropertyservices.co.uk' . $path;
    $pageTitle = isset($hero['documentTitle'])
        ? (string)$hero['documentTitle']
        : ($titles[$current] ?? ($hero['title'] . ' | iComply Supplies'));
    $pageDesc = isset($hero['description'])
        ? (string)$hero['description']
        : ($desc[$current] ?? $hero['lead']);

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
        ['Browse', '/shop/browse/', false, 'browse'],
        ['Manufacturers', '/shop/browse/manufacturers/', false, 'manufacturers'],
        ['Product types', '/shop/browse/product-types/', false, 'product-types'],
        ['Product lines', '/shop/browse/product-lines/', false, 'product-lines'],
        ['Install packages', '/shop/collections/service-packages/', false, 'service-packages'],
        ['Service contracts', '/shop/collections/service-contracts/', false, 'service-contracts'],
        ['Aircon', '/shop/collections/air-conditioning/', false, 'air-conditioning'],
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

function icomplyShopHandle(string $handle): string
{
    $handle = strtolower(trim($handle));
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $handle)) {
        return '';
    }
    return $handle;
}

function icomplyShopSlug(string $label): string
{
    $slug = strtolower(trim($label));
    $slug = str_replace('&', ' and ', $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;
    return trim($slug, '-');
}

function icomplyRemoveTree(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($dir);
}

function icomplyWriteShopHtml(string $dir, string $html): void
{
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create ' . $dir);
    }
    file_put_contents($dir . '/index.html', $html);
}

/**
 * @param list<array<string,mixed>> $products
 */
function icomplyShopVendorShelfHtml(array $products): string
{
    $vendors = [];
    foreach ($products as $product) {
        $label = trim((string)($product['vendor'] ?? ''));
        $slug = icomplyShopSlug($label);
        if ($label === '' || $slug === '') {
            continue;
        }
        if (!isset($vendors[$slug])) {
            $vendors[$slug] = ['label' => $label, 'image' => '', 'count' => 0];
        }
        $vendors[$slug]['count']++;
        if ($vendors[$slug]['image'] === '') {
            $vendors[$slug]['image'] = icomplyShopProductImage($product);
        }
    }
    if ($vendors === []) {
        return '';
    }
    uasort($vendors, static function (array $a, array $b): int {
        return strcasecmp($a['label'], $b['label']);
    });
    $cards = '';
    foreach ($vendors as $slug => $vendor) {
        $img = $vendor['image'] !== ''
            ? '<img src="' . icomplyH($vendor['image']) . '" alt="' . icomplyH($vendor['label']) . '" loading="lazy" width="168" height="72">'
            : '<span class="coming-soon">Image coming soon</span>';
        $cards .= '<a class="nav-logo-card" href="/shop/collections/' . icomplyH($slug) . '/">'
            . $img
            . '<span class="nav-logo-label">' . icomplyH($vendor['label']) . '</span>'
            . '</a>';
    }
    return '<section class="section" style="padding-top:0"><div class="wrap">'
        . '<h2>Shop by manufacturer</h2>'
        . '<p class="lead">Brands on the products in this hub. Photos are the live Shopify image, not a guessed logo.</p>'
        . '<div class="nav-logo-grid">' . $cards . '</div>'
        . '</div></section>';
}

/**
 * @param array<string,mixed> $product
 * @return list<string>
 */
function icomplyShopSpecialCollections(array $product): array
{
    $blob = icomplyShopProductBlob($product);
    $type = strtolower(trim((string)($product['product_type'] ?? '')));
    $handle = strtolower(trim((string)($product['handle'] ?? '')));
    $hits = [];
    if (str_contains($type, 'package') || str_contains($handle, 'package') || preg_match('/\binstall package\b/', $blob)) {
        $hits[] = 'service-packages';
    }
    if (str_contains($type, 'contract') || str_contains($handle, 'contract') || preg_match('/\bservice contract\b/', $blob)) {
        $hits[] = 'service-contracts';
    }
    if (preg_match('/\b(air conditioning|air-conditioning|aircon)\b/', $blob) || str_contains($type, 'air conditioning')) {
        $hits[] = 'air-conditioning';
    }
    return $hits;
}

/**
 * Named ranges that actually appear in the live title, type, vendor or tags.
 *
 * @param array<string,mixed> $product
 * @return array<string,string> slug => label
 */
function icomplyShopProductLines(array $product): array
{
    $blob = icomplyShopProductBlob($product);
    $map = [
        'xp95' => 'XP95',
        'alarmsense' => 'AlarmSense',
        'discovery' => 'Discovery',
        'soteria' => 'Soteria',
        'orbis' => 'Orbis',
        'mxpro' => 'MxPro',
        'evacgo' => 'EvacGo',
        'cast' => 'CAST',
    ];
    $hits = [];
    foreach ($map as $slug => $label) {
        if (preg_match('/\b' . preg_quote($slug, '/') . '\b/i', $blob)) {
            $hits[$slug] = $label;
        }
    }
    return $hits;
}

/**
 * @param list<array<string,mixed>> $products
 */
function icomplyShopPriceLine(array $product): string
{
    $prices = [];
    $variants = is_array($product['variants'] ?? null) ? $product['variants'] : [];
    foreach ($variants as $variant) {
        if (!is_array($variant) || !is_numeric($variant['price'] ?? null)) {
            continue;
        }
        $prices[] = (float)$variant['price'];
    }
    if ($prices === []) {
        return 'Price on application';
    }
    $min = min($prices);
    $max = max($prices);
    $formatted = icomplyFormatShopifyPrice((string)$min);
    if ($formatted === null) {
        return 'Price on application';
    }
    return $min === $max ? $formatted : ('From ' . $formatted);
}

/**
 * @param list<array<string,mixed>> $products
 * @param array<string,list<array<string,mixed>>> $hubs
 * @param array<string,mixed> $ctx
 */
function icomplyWriteShopCatalogue(string $outDir, array $products, array $hubs, array $ctx): void
{
    foreach (['browse', 'collections', 'products'] as $dir) {
        icomplyRemoveTree($outDir . '/' . $dir);
    }

    $byVendor = [];
    $byType = [];
    $byLine = [];
    $special = [
        'service-packages' => [],
        'service-contracts' => [],
        'air-conditioning' => [],
    ];
    $specialLabels = [
        'service-packages' => 'Install packages',
        'service-contracts' => 'Service contracts',
        'air-conditioning' => 'Air conditioning',
    ];
    $lineLabels = [];

    foreach ($products as $product) {
        if (!is_array($product)) {
            continue;
        }
        $vendor = trim((string)($product['vendor'] ?? ''));
        $vendorSlug = icomplyShopSlug($vendor);
        if ($vendor !== '' && $vendorSlug !== '') {
            $byVendor[$vendorSlug]['label'] = $vendor;
            $byVendor[$vendorSlug]['products'][] = $product;
        }
        $type = trim((string)($product['product_type'] ?? ''));
        $typeSlug = icomplyShopSlug($type);
        if ($type !== '' && $typeSlug !== '') {
            $byType[$typeSlug]['label'] = $type;
            $byType[$typeSlug]['products'][] = $product;
        }
        foreach (icomplyShopProductLines($product) as $slug => $label) {
            $lineLabels[$slug] = $label;
            $byLine[$slug][] = $product;
        }
        foreach (icomplyShopSpecialCollections($product) as $slug) {
            $special[$slug][] = $product;
        }
    }

    $knownVendors = [
        'advanced' => 'Advanced',
        'apollo' => 'Apollo',
        'c-tec' => 'C-TEC',
        'ems' => 'EMS',
        'fike' => 'Fike',
        'haes' => 'Haes',
        'hochiki' => 'Hochiki',
        'kac' => 'KAC',
        'kentec' => 'Kentec',
    ];
    foreach ($knownVendors as $slug => $label) {
        if (!isset($byVendor[$slug])) {
            $byVendor[$slug] = ['label' => $label, 'products' => []];
        }
    }

    icomplyWriteShopHtml($outDir . '/browse', icomplyRenderShopBrowseIndex($ctx));
    icomplyWriteShopHtml($outDir . '/browse/categories', icomplyRenderShopBrowseCategories($ctx));
    icomplyWriteShopHtml(
        $outDir . '/browse/manufacturers',
        icomplyRenderShopGroupIndex('manufacturers', 'Manufacturers', 'Brands published on a live Shopify product. A brand with no current SKU is marked coming soon.', $byVendor, '/shop/collections/', $ctx)
    );
    icomplyWriteShopHtml(
        $outDir . '/browse/product-types',
        icomplyRenderShopGroupIndex('product-types', 'Product types', 'Product types taken from the live Shopify dump. Empty types are not listed.', $byType, '/shop/browse/product-types/', $ctx)
    );
    $lineGroups = [];
    foreach ($byLine as $slug => $list) {
        $lineGroups[$slug] = ['label' => $lineLabels[$slug] ?? $slug, 'products' => $list];
    }
    icomplyWriteShopHtml(
        $outDir . '/browse/product-lines',
        icomplyRenderShopGroupIndex('product-lines', 'Product lines', 'Named ranges that appear in live titles, types or tags. Ranges with no match are omitted.', $lineGroups, '/shop/browse/product-lines/', $ctx)
    );

    $collectionIndex = [];
    foreach ($specialLabels as $slug => $label) {
        $collectionIndex[$slug] = ['label' => $label, 'products' => $special[$slug]];
        icomplyWriteShopHtml(
            $outDir . '/collections/' . $slug,
            icomplyRenderShopShelf($slug, $label, $special[$slug], $ctx, 'The live dump has no product in this shelf yet.', '/shop/collections/' . $slug . '/')
        );
    }
    foreach ($byVendor as $slug => $group) {
        if (isset($specialLabels[$slug])) {
            continue;
        }
        $collectionIndex[$slug] = $group;
        icomplyWriteShopHtml(
            $outDir . '/collections/' . $slug,
            icomplyRenderShopShelf($slug, (string)$group['label'], $group['products'], $ctx, 'No live Shopify product is filed under this brand yet.', '/shop/collections/' . $slug . '/')
        );
    }
    icomplyWriteShopHtml(
        $outDir . '/collections',
        icomplyRenderShopGroupIndex('collections', 'Collections', 'Manufacturer shelves and the package, contract and air-conditioning shelves. Coming soon means the live dump has no matching SKU.', $collectionIndex, '/shop/collections/', $ctx)
    );

    foreach ($byType as $slug => $group) {
        icomplyWriteShopHtml(
            $outDir . '/browse/product-types/' . $slug,
            icomplyRenderShopShelf('product-types', (string)$group['label'], $group['products'], $ctx, 'No live product uses this type.', '/shop/browse/product-types/' . $slug . '/')
        );
    }
    foreach ($byLine as $slug => $list) {
        icomplyWriteShopHtml(
            $outDir . '/browse/product-lines/' . $slug,
            icomplyRenderShopShelf('product-lines', $lineLabels[$slug] ?? $slug, $list, $ctx, 'No live product is in this range.', '/shop/browse/product-lines/' . $slug . '/')
        );
    }

    $listed = 0;
    foreach ($products as $product) {
        if (!is_array($product)) {
            continue;
        }
        $handle = icomplyShopHandle((string)($product['handle'] ?? ''));
        if ($handle === '') {
            continue;
        }
        icomplyWriteShopHtml(
            $outDir . '/products/' . $handle,
            icomplyRenderShopProductPage($product, $ctx)
        );
        $listed++;
    }
    icomplyWriteShopHtml($outDir . '/products', icomplyRenderShopProductIndex($hubs, $ctx));
    file_put_contents($outDir . '/sitemap.xml', icomplyRenderShopSitemapXml($products, $byVendor, $byType, $lineLabels, $specialLabels));
    $ctx['product_pages'] = $listed;
}

/**
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopBrowseIndex(array $ctx): string
{
    $hero = [
        'kicker' => 'iComply Supplies · Browse',
        'title' => 'Browse the catalogue',
        'lead' => 'Category, manufacturer, product type and product line. Counts come from the live Shopify dump.',
        'path' => '/shop/browse/',
        'documentTitle' => 'Browse supplies | iComply Supplies',
        'description' => 'Browse iComply supplies by category, manufacturer, product type and product line.',
    ];
    $main = '<section class="section"><div class="wrap"><div class="hub-grid">'
        . '<a class="hub-card" href="/shop/browse/categories/"><h2>Categories</h2><p>Fire, electrical, security and gas.</p><span class="go">Open categories →</span></a>'
        . '<a class="hub-card" href="/shop/browse/manufacturers/"><h2>Manufacturers</h2><p>Brands on live products, plus known shelves marked coming soon when empty.</p><span class="go">Shop by brand →</span></a>'
        . '<a class="hub-card" href="/shop/browse/product-types/"><h2>Product types</h2><p>Types published on Shopify.</p><span class="go">Browse types →</span></a>'
        . '<a class="hub-card" href="/shop/browse/product-lines/"><h2>Product lines</h2><p>Named ranges found in the live catalogue.</p><span class="go">Browse lines →</span></a>'
        . '<a class="hub-card" href="/shop/collections/service-packages/"><h2>Install packages</h2><p>Products whose type or handle is a package.</p><span class="go">Open shelf →</span></a>'
        . '<a class="hub-card" href="/shop/collections/service-contracts/"><h2>Service contracts</h2><p>Products filed as a contract.</p><span class="go">Open shelf →</span></a>'
        . '<a class="hub-card" href="/shop/collections/air-conditioning/"><h2>Air conditioning</h2><p>Live air-conditioning SKUs only.</p><span class="go">Open shelf →</span></a>'
        . '<a class="hub-card" href="/shop/products/"><h2>All products</h2><p>Every live handle with its own page.</p><span class="go">Open the list →</span></a>'
        . '</div>' . icomplyShopSourceNote($ctx) . '</div></section>';
    return icomplyShopPage('browse', $hero, $main, $ctx);
}

/**
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopBrowseCategories(array $ctx): string
{
    $counts = $ctx['counts'];
    $cards = [
        ['fire', 'Fire', (int)$counts['fire']],
        ['electrical', 'Electrical', (int)$counts['electrical']],
        ['security', 'Security', (int)$counts['security']],
        ['gas', 'Gas', (int)$counts['gas']],
    ];
    $html = '';
    foreach ($cards as [$slug, $label, $count]) {
        $note = $count === 0 ? 'Coming soon — no live SKU in this category.' : ($count === 1 ? '1 live product' : ($count . ' live products'));
        $html .= '<a class="hub-card" href="/shop/' . $slug . '/"><div class="hub-count">' . $count . '</div><h2>' . icomplyH($label) . '</h2><p>' . icomplyH($note) . '</p><span class="go">Open ' . icomplyH($label) . ' →</span></a>';
    }
    $hero = [
        'kicker' => 'iComply Supplies · Categories',
        'title' => 'Categories',
        'lead' => 'Fire, electrical, security and gas. An empty count is a coming-soon shelf, not a missing page.',
        'path' => '/shop/browse/categories/',
        'documentTitle' => 'Supply categories | iComply Supplies',
        'description' => 'Browse iComply supply categories: fire, electrical, security and gas.',
    ];
    $main = '<section class="section"><div class="wrap"><div class="hub-grid">' . $html . '</div>' . icomplyShopSourceNote($ctx) . '</div></section>';
    return icomplyShopPage('categories', $hero, $main, $ctx);
}

/**
 * @param array<string,array{label:string,products:list<array<string,mixed>>}> $groups
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopGroupIndex(string $current, string $title, string $lead, array $groups, string $hrefPrefix, array $ctx): string
{
    uasort($groups, static function (array $a, array $b): int {
        return strcasecmp((string)$a['label'], (string)$b['label']);
    });
    $cards = '';
    foreach ($groups as $slug => $group) {
        $count = count($group['products']);
        $note = $count === 0 ? 'Coming soon' : ($count === 1 ? '1 live product' : ($count . ' live products'));
        $cards .= '<a class="hub-card" href="' . icomplyH(rtrim($hrefPrefix, '/') . '/' . $slug . '/') . '">'
            . '<div class="hub-count">' . $count . '</div>'
            . '<h2>' . icomplyH((string)$group['label']) . '</h2>'
            . '<p>' . icomplyH($note) . '</p>'
            . '<span class="go">' . icomplyH($count === 0 ? 'Coming soon →' : 'Open shelf →') . '</span>'
            . '</a>';
    }
    if ($cards === '') {
        $cards = '<div class="empty-card"><h2>Coming soon</h2><p>Nothing in the live Shopify dump matches this browse list yet.</p></div>';
    }
    $path = $current === 'collections' ? '/shop/collections/' : '/shop/browse/' . $current . '/';
    $hero = [
        'kicker' => 'iComply Supplies · ' . $title,
        'title' => $title,
        'lead' => $lead,
        'path' => $path,
        'documentTitle' => $title . ' | iComply Supplies',
        'description' => $lead,
    ];
    $main = '<section class="section"><div class="wrap"><div class="hub-grid">' . $cards . '</div>' . icomplyShopSourceNote($ctx) . '</div></section>';
    return icomplyShopPage($current, $hero, $main, $ctx);
}

/**
 * @param list<array<string,mixed>> $products
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopShelf(string $current, string $title, array $products, array $ctx, string $emptyCopy, string $path): string
{
    $count = count($products);
    $hero = [
        'kicker' => 'iComply Supplies',
        'title' => $title,
        'lead' => $count === 0
            ? 'Coming soon. ' . $emptyCopy
            : ($count === 1 ? '1 live product from Shopify.' : ($count . ' live products from Shopify.')),
        'meta' => $count === 0 ? 'Coming soon' : ($count . ' live'),
        'path' => $path,
        'documentTitle' => $title . ' | iComply Supplies',
        'description' => $count === 0 ? ('Coming soon — ' . $title) : ($title . ' from the live iComply Shopify catalogue.'),
    ];
    if ($count === 0) {
        $grid = '<div class="empty-card"><h2>Coming soon</h2><p>' . icomplyH($emptyCopy) . ' Checkout stays on Shopify when a real SKU is published. This page does not invent stock, prices or photos.</p>'
            . '<div class="cta-row"><a class="btn btn-orange" href="mailto:' . icomplyH((string)$ctx['enquire_email']) . '?subject=' . rawurlencode($title . ' — coming soon') . '">Enquire</a>'
            . '<a class="btn btn-ghost" href="https://icomplypropertyservices.co.uk/contact">Free quote</a></div></div>';
    } else {
        $grid = '<div class="product-grid">';
        foreach ($products as $product) {
            $grid .= icomplyRenderProductCard($product, (string)$ctx['enquire_email']);
        }
        $grid .= '</div>';
    }
    $main = '<section class="section"><div class="wrap">' . $grid . icomplyShopSourceNote($ctx) . '</div></section>';
    return icomplyShopPage($current, $hero, $main, $ctx);
}

/**
 * @param array<string,mixed> $product
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopProductPage(array $product, array $ctx): string
{
    $handle = icomplyShopHandle((string)($product['handle'] ?? ''));
    $title = trim((string)($product['title'] ?? 'Product'));
    $hero = [
        'kicker' => 'iComply Supplies · Product',
        'title' => $title,
        'lead' => 'Trade listing from the live Shopify catalogue. Install is not included. Checkout stays on the Shopify shop.',
        'path' => '/shop/products/' . $handle . '/',
        'documentTitle' => $title . ' | iComply Supplies',
        'description' => $title . ' — live Shopify listing. Order on Shopify or enquire.',
    ];
    $card = icomplyRenderProductCard($product, (string)$ctx['enquire_email']);
    $main = '<section class="section"><div class="wrap"><p class="lead"><a href="/shop/products/">All products</a> · <a href="/shop/">Supplies</a></p>'
        . '<div class="product-grid product-grid-single">' . $card . '</div>'
        . icomplyShopSourceNote($ctx)
        . '</div></section>';
    return icomplyShopPage('product', $hero, $main, $ctx);
}

/**
 * @param array<string,list<array<string,mixed>>> $hubs
 * @param array<string,mixed> $ctx
 */
function icomplyRenderShopProductIndex(array $hubs, array $ctx): string
{
    $blocks = '';
    $labels = ['fire' => 'Fire', 'electrical' => 'Electrical', 'security' => 'Security', 'gas' => 'Gas'];
    foreach ($labels as $slug => $label) {
        $list = $hubs[$slug] ?? [];
        $items = '';
        foreach ($list as $product) {
            if (!is_array($product)) {
                continue;
            }
            $handle = icomplyShopHandle((string)($product['handle'] ?? ''));
            $name = trim((string)($product['title'] ?? 'Product'));
            if ($handle === '') {
                continue;
            }
            $items .= '<li><a href="/shop/products/' . icomplyH($handle) . '/">' . icomplyH($name) . '</a> <span>' . icomplyH(icomplyShopPriceLine($product)) . '</span></li>';
        }
        if ($items === '') {
            $blocks .= '<h2>' . icomplyH($label) . '</h2><p class="lead">Coming soon — no live product in this category.</p>';
            continue;
        }
        $blocks .= '<h2>' . icomplyH($label) . '</h2><ul class="link-list">' . $items . '</ul>';
    }
    $hero = [
        'kicker' => 'iComply Supplies · Products',
        'title' => 'All products',
        'lead' => 'Every live Shopify handle on its own page. Prices are the store prices. Missing photos say coming soon.',
        'path' => '/shop/products/',
        'documentTitle' => 'All products | iComply Supplies',
        'description' => 'All live iComply Shopify products, with a page for each handle.',
    ];
    $main = '<section class="section"><div class="wrap">' . $blocks . icomplyShopSourceNote($ctx) . '</div></section>';
    return icomplyShopPage('products', $hero, $main, $ctx);
}

/**
 * @param list<array<string,mixed>> $products
 * @param array<string,array{label:string,products:list<array<string,mixed>>}> $byVendor
 * @param array<string,array{label:string,products:list<array<string,mixed>>}> $byType
 * @param array<string,string> $lineLabels
 * @param array<string,string> $specialLabels
 */
function icomplyRenderShopSitemapXml(array $products, array $byVendor, array $byType, array $lineLabels, array $specialLabels): string
{
    $base = 'https://icomplypropertyservices.co.uk';
    $locs = [
        '/shop/',
        '/shop/fire/',
        '/shop/electrical/',
        '/shop/security/',
        '/shop/gas/',
        '/shop/browse/',
        '/shop/browse/categories/',
        '/shop/browse/manufacturers/',
        '/shop/browse/product-types/',
        '/shop/browse/product-lines/',
        '/shop/collections/',
        '/shop/products/',
    ];
    foreach (array_keys($specialLabels) as $slug) {
        $locs[] = '/shop/collections/' . $slug . '/';
    }
    foreach (array_keys($byVendor) as $slug) {
        $locs[] = '/shop/collections/' . $slug . '/';
    }
    foreach (array_keys($byType) as $slug) {
        $locs[] = '/shop/browse/product-types/' . $slug . '/';
    }
    foreach (array_keys($lineLabels) as $slug) {
        $locs[] = '/shop/browse/product-lines/' . $slug . '/';
    }
    foreach ($products as $product) {
        if (!is_array($product)) {
            continue;
        }
        $handle = icomplyShopHandle((string)($product['handle'] ?? ''));
        if ($handle !== '') {
            $locs[] = '/shop/products/' . $handle . '/';
        }
    }
    $locs = array_values(array_unique($locs));
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($locs as $loc) {
        $xml .= '  <url><loc>' . htmlspecialchars($base . $loc, ENT_XML1) . '</loc></url>' . "\n";
    }
    $xml .= '</urlset>' . "\n";
    return $xml;
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
        copy($file->getPathname(), $target);
    }
}
