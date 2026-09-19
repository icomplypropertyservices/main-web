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
