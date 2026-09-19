<?php
/**
 * Trade product PDP — /products/product/{handle} and /shop/products/{handle}.
 * Kit £ SoT via aov-kit-prices + package-public; install remains POA.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/shopify.php';
if (is_file(__DIR__ . '/package-public.php')) {
    require_once __DIR__ . '/package-public.php';
}
if (is_file(__DIR__ . '/aov-kit-prices.php')) {
    require_once __DIR__ . '/aov-kit-prices.php';
}


function icomplyProductHandleFromRequest(): string
{
    $handle = trim((string)($_GET['handle'] ?? ''));
    if ($handle !== '') {
        return strtolower(rawurldecode($handle));
    }
    $uri = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
    if (preg_match('#/(?:products/product|shop/products)/([a-z0-9\-]+)/?$#i', $uri, $m)) {
        return strtolower($m[1]);
    }
    return '';
}

function icomplyFindCatalogProduct(string $handle): ?array
{
    if ($handle === '') {
        return null;
    }
    $catalog = getShopCatalog();
    foreach ($catalog['products'] as $product) {
        if (!is_array($product)) {
            continue;
        }
        if (strtolower((string)($product['handle'] ?? '')) === $handle) {
            return $product;
        }
    }
    return null;
}

function icomplyRenderProductPdp(string $pathPrefix = '/products/product'): void
{
    $handle = icomplyProductHandleFromRequest();
    $product = icomplyFindCatalogProduct($handle);
    if ($product === null) {
        http_response_code(404);
        if (is_file(SITE_ROOT . '/404.php')) {
            require SITE_ROOT . '/404.php';
        } else {
            echo 'Product not found';
        }
        return;
    }

    $over = icomplyPackagePublicOverride($product);
    $kitPrice = function_exists('icomplyAovKitPriceOverride') ? icomplyAovKitPriceOverride($product) : null;
    if ($kitPrice !== null) {
        $product['price'] = $kitPrice;
    } elseif ($over['price'] !== null) {
        $product['price'] = $over['price'];
    }
    if (!empty($over['blurb'])) {
        $product['blurb'] = $over['blurb'];
    }

    $title = (string)($product['title'] ?? 'Product');
    $price = (string)($product['price'] ?? 'POA');
    $blurb = (string)($product['blurb'] ?? $product['product_type'] ?? '');
    $img = shopifyImageSrc(
        (string)($product['image'] ?? ''),
        (string)(($product['id'] ?? '') . ' ' . $handle . ' ' . $title)
    );
    $canonicalPath = rtrim($pathPrefix, '/') . '/' . rawurlencode($handle);
    $canonicalUrl = url($canonicalPath);
    $shopHref = shopifyStoreUrl();
    if ($shopHref !== '' && $handle !== '') {
        $shopHref = rtrim($shopHref, '/') . '/products/' . rawurlencode($handle);
    } else {
        $shopHref = url('/contact.php');
    }
    $cta = (string)($product['public_cta'] ?? $over['cta'] ?? 'Buy / enquire');
    if (!empty($product['public_href'])) {
        $shopHref = (string)$product['public_href'];
    } elseif ($over['href'] !== null) {
        $shopHref = $over['href'];
    }

    $pageTitle = $title . ' | iComply Trade';
    $metaDesc = trim($blurb !== '' ? $blurb : ($title . ' — trade supply. Install POA.'));
    $ogImage = $img;
    $isInstallPoa = true;
    $priceLabel = $price;
    if (stripos($priceLabel, 'poa') === false && $priceLabel !== '') {
        if ($priceLabel[0] !== '£' && is_numeric(str_replace([',', ' '], '', $priceLabel))) {
            $priceLabel = '£' . $priceLabel;
        }
        $priceLabel .= ' ex VAT';
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    require SITE_ROOT . '/includes/header.php';
    ?>
<section class="max-w-7xl mx-auto px-6 py-12">
  <nav class="text-xs text-zinc-500 mb-6" aria-label="Breadcrumb">
    <a href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>" class="hover:text-[#FF6B00]">Home</a>
    / <a href="<?= htmlspecialchars(url('/products'), ENT_QUOTES, 'UTF-8') ?>" class="hover:text-[#FF6B00]">Products</a>
    / <span class="text-zinc-800"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
  </nav>
  <div class="grid lg:grid-cols-2 gap-10 items-start">
    <div class="bg-zinc-100 rounded-3xl overflow-hidden border border-zinc-200">
      <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" class="w-full h-80 object-contain bg-white" loading="eager" width="640" height="320" onerror="this.src='<?= htmlspecialchars(url('/assets/images/services/fire-alarms.jpg'), ENT_QUOTES, 'UTF-8') ?>'">
    </div>
    <div>
      <?php if (!empty($product['badge']) || !empty($over['is_aov_package'])): ?>
      <span class="inline-block text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-[#FF6B00] text-white mb-3"><?= htmlspecialchars((string)($product['badge'] ?? 'POA'), ENT_QUOTES, 'UTF-8') ?></span>
      <?php endif; ?>
      <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-black"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="mt-2 text-sm text-zinc-500">SKU handle: <code><?= htmlspecialchars($handle, ENT_QUOTES, 'UTF-8') ?></code></p>
      <div class="mt-6 text-2xl font-semibold text-[#FF6B00]"><?= htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') ?></div>
      <?php if ($isInstallPoa): ?>
      <p class="mt-1 text-sm text-zinc-600">Installation / labour: <strong>POA</strong></p>
      <?php endif; ?>
      <?php if ($blurb !== ''): ?>
      <p class="mt-6 text-zinc-700 leading-relaxed"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?= htmlspecialchars($shopHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#FF6B00] text-white font-semibold"<?= str_starts_with($shopHref, 'http') ? ' target="_blank" rel="noopener"' : '' ?>><?= htmlspecialchars($cta, ENT_QUOTES, 'UTF-8') ?></a>
        <a href="<?= htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-zinc-300 font-semibold text-[#0B1F3A]">Get install quote (POA)</a>
      </div>
    </div>
  </div>
</section>
    <?php
    require SITE_ROOT . '/includes/footer.php';
}

function icomplyProductSitemapXml(string $pathPrefix): string
{
    $base = rtrim((string)(defined('SITE_URL') ? SITE_URL : 'https://icomplypropertyservices.co.uk'), '/');
    $prefix = '/' . trim($pathPrefix, '/');
    $catalog = getShopCatalog();
    $lines = [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
    ];
    $seen = [];
    foreach ($catalog['products'] as $product) {
        if (!is_array($product)) {
            continue;
        }
        $h = strtolower(trim((string)($product['handle'] ?? '')));
        if ($h === '' || isset($seen[$h])) {
            continue;
        }
        $seen[$h] = true;
        $loc = $base . $prefix . '/' . rawurlencode($h);
        $lines[] = '  <url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc><changefreq>weekly</changefreq><priority>0.6</priority></url>';
    }
    $lines[] = '</urlset>';
    return implode("\n", $lines) . "\n";
}
