<?php
/**
 * Shopify product cards + Buy Button / Storefront widget helpers.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/package-public.php';
require_once __DIR__ . '/aov-kit-prices.php';

function shopifyEnabled(): bool {
    $domain = defined('SHOPIFY_DOMAIN') ? trim((string)SHOPIFY_DOMAIN) : '';
    $token = defined('SHOPIFY_STOREFRONT_TOKEN') ? trim((string)SHOPIFY_STOREFRONT_TOKEN) : '';
    if ($domain === '' || $token === '') {
        return false;
    }
    if (defined('SHOPIFY_ENABLED') && SHOPIFY_ENABLED === false) {
        return false;
    }
    return true;
}

function shopifyStoreUrl(): string {
    if (SHOPIFY_STORE_URL !== '') {
        return rtrim(SHOPIFY_STORE_URL, '/');
    }
    if (SHOPIFY_DOMAIN !== '') {
        return 'https://' . preg_replace('#^https?://#', '', SHOPIFY_DOMAIN);
    }
    return '';
}

function getShopCatalog(): array {
    $data = loadJsonData('shopify-products', ['collections' => [], 'products' => []]);
    $products = $data['products'] ?? [];
    // Optional hub shards (full ~721 catalogue) — website/data/shopify-products-{hub}.json
    $shards = $data['shards'] ?? ['shopify-products-fire.json', 'shopify-products-electrical.json', 'shopify-products-security.json', 'shopify-products-gas.json'];
    if ($shards && (count($products) < 400)) {
        $merged = [];
        $seen = [];
        foreach ($products as $product) {
            if (!is_array($product)) {
                continue;
            }
            $h = (string)($product['handle'] ?? $product['id'] ?? '');
            if ($h !== '' && isset($seen[$h])) {
                continue;
            }
            if ($h !== '') {
                $seen[$h] = true;
            }
            $merged[] = $product;
        }
        foreach ($shards as $shardName) {
            $shardName = basename((string)$shardName);
            if ($shardName === '' || !str_ends_with($shardName, '.json')) {
                continue;
            }
            $key = preg_replace('/\.json$/', '', $shardName);
            $shard = loadJsonData($key, ['products' => []]);
            foreach ($shard['products'] ?? [] as $product) {
                if (!is_array($product)) {
                    continue;
                }
                $h = (string)($product['handle'] ?? $product['id'] ?? '');
                if ($h !== '' && isset($seen[$h])) {
                    continue;
                }
                if ($h !== '') {
                    $seen[$h] = true;
                }
                $merged[] = $product;
            }
        }
        $products = $merged;
    }
    // Normalize public package fields (AOV package → POA; dead handles retargeted).
    foreach ($products as $i => $product) {
        if (!is_array($product)) {
            continue;
        }
        $over = icomplyPackagePublicOverride($product);
        $kitPrice = function_exists('icomplyAovKitPriceOverride') ? icomplyAovKitPriceOverride($product) : null;
        if ($kitPrice !== null) {
            $products[$i]['price'] = $kitPrice;
        } elseif ($over['price'] !== null) {
            $products[$i]['price'] = $over['price'];
        }
        if ($over['href'] !== null) {
            $products[$i]['public_href'] = $over['href'];
        }
        if ($over['cta'] !== null) {
            $products[$i]['public_cta'] = $over['cta'];
        }
        if ($over['is_aov_package']) {
            $products[$i]['badge'] = 'POA';
        }
    }
    return [
        'collections' => $data['collections'] ?? [],
        'products' => $products,
    ];
}

function shopifyProductUrl(array $product): string {
    if (!empty($product['public_href'])) {
        return (string)$product['public_href'];
    }
    $over = icomplyPackagePublicOverride($product);
    if ($over['href'] !== null) {
        return $over['href'];
    }
    $handle = $product['handle'] ?? '';
    if ($handle) {
        // Prefer on-site Netlify PDP (SEO crawlable); Shopify remains checkout CTA on PDP.
        return url('/products/product/' . rawurlencode((string)$handle));
    }
    return url('/shop/index.php') . '#' . rawurlencode($product['id'] ?? 'product');
}

function shopifyCollectionUrl(array $collection): string {
    $store = shopifyStoreUrl();
    $handle = $collection['handle'] ?? '';
    if ($store && $handle) {
        return $store . '/collections/' . rawurlencode($handle);
    }
    return url('/shop/index.php') . '#collection-' . rawurlencode($collection['id'] ?? '');
}

function shopifyImageSrc(string $path, string $hint = ''): string {
    $path = trim($path);
    if ($path !== '' && !preg_match('#^https?://#i', $path)) {
        $rel = '/' . ltrim(str_replace('\\', '/', $path), '/');
        if (is_file(SITE_ROOT . $rel)) {
            return url($rel);
        }
    }
    $hay = strtolower($path . ' ' . $hint);
    $aliases = [
        'electrical' => 'electrical',
        'fire-alarm' => 'fire-alarms',
        'emergency-lighting' => 'emergency-lighting',
        'aov' => 'aov-air-handling',
        'air-handling' => 'aov-air-handling',
        'nurse-call' => 'nurse-call',
        'gas-safety' => 'gas-systems',
        'gas' => 'gas-systems',
        'intruder' => 'intruder-alarm',
        'cctv' => 'cctv',
        'access-control' => 'access-control',
        'door-entry' => 'door-entry',
        'intercom' => 'intercoms',
        'landlord' => 'landlord-compliance',
        'fire-risk' => 'fire-risk-assessments',
        'kentec' => 'fire-alarms',
        'apollo' => 'fire-alarms',
        'hochiki' => 'fire-alarms',
        'paxton' => 'access-control',
        'hikvision' => 'cctv',
    ];
    foreach ($aliases as $needle => $slug) {
        if (str_contains($hay, $needle)) {
            return function_exists('serviceImageUrl') ? serviceImageUrl($slug) : url('/assets/images/services/' . $slug . '.jpg');
        }
    }
    if (function_exists('getServices')) {
        foreach (array_keys(getServices()) as $slug) {
            if ($slug !== '' && str_contains($hay, $slug)) {
                return serviceImageUrl($slug);
            }
        }
    }
    return url('/assets/images/services/fire-alarms.jpg');
}

function shopifyCardFromManufacturerProduct(array $p, string $mfrSlug, string $mfrName): string {
    $product = [
        'id' => $p['id'] ?? ($mfrSlug . '-product'),
        'title' => $p['title'] ?? ($mfrName . ' product'),
        'blurb' => $p['blurb'] ?? '',
        'price' => $p['price'] ?? 'POA',
        'image' => $p['image'] ?? (function_exists('manufacturerImageUrl') ? manufacturerImageUrl($mfrSlug) : ('/assets/images/manufacturers/' . $mfrSlug . '.jpg')),
        'handle' => $p['handle'] ?? '',
        'shopify_product_id' => $p['shopify_product_id'] ?? '',
        'badge' => $p['badge'] ?? '',
    ];
    // Placeholder brand cards are not Shopify PDPs. Land on the brand page
    // anchor, which the static export actually publishes.
    $anchorId = (string)($p['id'] ?? ($mfrSlug . '-product'));
    $product['public_href'] = url('/pages/manufacturers/' . rawurlencode($mfrSlug) . '.php')
        . '#' . rawurlencode($anchorId);
    return shopifyProductCardHtml($product, false);
}

function shopifyProductCardHtml(array $product, bool $compact = false): string {
    $over = icomplyPackagePublicOverride($product);
    if ($over['price'] !== null) {
        $product['price'] = $over['price'];
    }
    if ($over['href'] !== null) {
        $product['public_href'] = $over['href'];
    }
    if ($over['is_aov_package']) {
        $product['badge'] = 'POA';
    }
    if (!empty($over['blurb'])) {
        $product['blurb'] = $over['blurb'];
    }

    $id = htmlspecialchars($product['id'] ?? '', ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($product['title'] ?? 'Product', ENT_QUOTES, 'UTF-8');
    $blurb = htmlspecialchars($product['blurb'] ?? '', ENT_QUOTES, 'UTF-8');
    $price = htmlspecialchars($product['price'] ?? '', ENT_QUOTES, 'UTF-8');
    $badge = trim((string)($product['badge'] ?? ''));
    $img = htmlspecialchars(shopifyImageSrc(
        (string)($product['image'] ?? ''),
        (string)(($product['id'] ?? '') . ' ' . ($product['handle'] ?? '') . ' ' . ($product['title'] ?? ''))
    ), ENT_QUOTES, 'UTF-8');
    $fallback = htmlspecialchars(url('/assets/images/services/fire-alarms.jpg'), ENT_QUOTES, 'UTF-8');
    $href = htmlspecialchars(shopifyProductUrl($product), ENT_QUOTES, 'UTF-8');
    $shopifyId = preg_replace('/\D+/', '', (string)($product['shopify_product_id'] ?? ''));
    $mountId = 'shopify-buy-' . preg_replace('/[^a-z0-9\-]/i', '', $product['id'] ?? uniqid('p'));
    $ctaLabel = htmlspecialchars((string)($product['public_cta'] ?? $over['cta'] ?? 'View in shop'), ENT_QUOTES, 'UTF-8');

    $badgeHtml = $badge !== ''
        ? '<span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide bg-[#ff6b00] text-white">' . htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') . '</span>'
        : '';

    $imgH = $compact ? 'h-36' : 'h-44';
    // Never mount Buy Button on retargeted / AOV-POA package cards
    $useLive = ($shopifyId !== '' && shopifyEnabled() && empty($product['public_href']) && !$over['is_aov_package']);
    $liveWidget = $useLive
        ? '<div id="' . $mountId . '" class="shopify-buy-mount mt-3" data-product-id="' . htmlspecialchars($shopifyId, ENT_QUOTES, 'UTF-8') . '"></div>'
        : '<a href="' . $href . '" class="mt-4 inline-flex items-center justify-center w-full py-3 rounded-xl bg-[#0B1F3A] hover:bg-[#ff6b00] text-white text-sm font-semibold transition">' . $ctaLabel . '</a>';

    return '<article id="' . $id . '" class="shop-product-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">'
        . '<a href="' . $href . '" class="relative block bg-zinc-100 overflow-hidden">'
        . $badgeHtml
        . '<img src="' . $img . '" alt="' . $title . '" class="w-full ' . $imgH . ' object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.src=\'' . $fallback . '\'">'
        . '</a>'
        . '<div class="p-5 flex flex-col flex-1">'
        . '<div class="text-xs font-semibold text-[#ff6b00] mb-1">' . $price . '</div>'
        . '<h3 class="font-semibold text-lg text-black leading-snug"><a href="' . $href . '" class="hover:text-[#ff6b00]">' . $title . '</a></h3>'
        . '<p class="mt-2 text-sm text-zinc-600 flex-1">' . $blurb . '</p>'
        . $liveWidget
        . '</div></article>';
}

function shopifyCollectionCardHtml(array $collection): string {
    $title = htmlspecialchars($collection['title'] ?? 'Collection', ENT_QUOTES, 'UTF-8');
    $blurb = htmlspecialchars($collection['blurb'] ?? '', ENT_QUOTES, 'UTF-8');
    $img = htmlspecialchars(shopifyImageSrc(
        (string)($collection['image'] ?? ''),
        (string)(($collection['id'] ?? '') . ' ' . ($collection['handle'] ?? '') . ' ' . ($collection['title'] ?? ''))
    ), ENT_QUOTES, 'UTF-8');
    $href = htmlspecialchars(shopifyCollectionUrl($collection), ENT_QUOTES, 'UTF-8');
    $id = htmlspecialchars($collection['id'] ?? '', ENT_QUOTES, 'UTF-8');
    $collId = preg_replace('/\D+/', '', (string)($collection['shopify_collection_id'] ?? ''));
    $mountId = 'shopify-coll-' . preg_replace('/[^a-z0-9\-]/i', '', $collection['id'] ?? uniqid('c'));

    $extra = ($collId !== '' && shopifyEnabled())
        ? '<div id="' . $mountId . '" class="shopify-collection-mount mt-3" data-collection-id="' . htmlspecialchars($collId, ENT_QUOTES, 'UTF-8') . '"></div>'
        : '<span class="inline-block mt-4 text-sm font-semibold text-[#ff6b00]">Shop collection →</span>';
    $fallback = htmlspecialchars(url('/assets/images/services/fire-alarms.jpg'), ENT_QUOTES, 'UTF-8');

    return <<<HTML
<a id="collection-{$id}" href="{$href}" class="shop-collection-card group block bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition">
  <div class="relative h-40 overflow-hidden bg-zinc-100">
    <img src="{$img}" alt="{$title} — iComply Property Services" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.src='{$fallback}'">
    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
    <div class="absolute bottom-3 left-4 right-4 text-white font-semibold text-lg">{$title}</div>
  </div>
  <div class="p-5">
    <p class="text-sm text-zinc-600">{$blurb}</p>
    {$extra}
  </div>
</a>
HTML;
}

function shopifyBuyButtonScript(): string {
    if (!shopifyEnabled()) {
        return '<!-- Shopify Buy Button inactive: set SHOPIFY_DOMAIN + SHOPIFY_STOREFRONT_TOKEN in config.local.php -->';
    }
    $domain = json_encode(SHOPIFY_DOMAIN);
    $token = json_encode(SHOPIFY_STOREFRONT_TOKEN);
    return <<<HTML
<script src="https://sdks.shopifycdn.com/buy-button/latest/buy-button-storefront.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof ShopifyBuy === 'undefined') return;
  var client = ShopifyBuy.buildClient({
    domain: {$domain},
    storefrontAccessToken: {$token}
  });
  ShopifyBuy.UI.onReady(client).then(function (ui) {
    document.querySelectorAll('.shopify-buy-mount[data-product-id]').forEach(function (el) {
      var pid = el.getAttribute('data-product-id');
      if (!pid) return;
      ui.createComponent('product', {
        id: pid,
        node: el,
        moneyFormat: '%C2%A3%7B%7Bamount%7D%7D',
        options: {
          product: {
            styles: {
              product: { '@media (min-width: 601px)': { 'max-width': '100%', 'margin-left': '0', 'margin-bottom': '0' } },
              button: {
                'background-color': '#0B1F3A',
                ':hover': { 'background-color': '#ff6b00' },
                'border-radius': '12px',
                'font-weight': '600'
              }
            },
            contents: { img: false, title: false, price: true, description: false },
            text: { button: 'Add to cart' }
          },
          cart: {
            styles: {
              button: { 'background-color': '#0B1F3A', ':hover': { 'background-color': '#ff6b00' }, 'border-radius': '12px' }
            },
            text: { total: 'Subtotal', button: 'Checkout' }
          }
        }
      });
    });
    document.querySelectorAll('.shopify-collection-mount[data-collection-id]').forEach(function (el) {
      var cid = el.getAttribute('data-collection-id');
      if (!cid) return;
      ui.createComponent('collection', {
        id: cid,
        node: el,
        moneyFormat: '%C2%A3%7B%7Bamount%7D%7D',
        options: {
          product: {
            styles: {
              button: { 'background-color': '#0B1F3A', ':hover': { 'background-color': '#ff6b00' }, 'border-radius': '12px' }
            },
            text: { button: 'Add to cart' }
          }
        }
      });
    });
  });
});
</script>
HTML;
}
