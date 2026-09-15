<?php
/**
 * Trade products hub — links out to Shopify shop.* plus Electrical / Fire / Security / Gas.
 * Must not 301 to /pages/packages.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/shopify.php';

$pageTitle = 'Trade Products | Electrical, Fire, Security & Gas';
$metaDesc = 'iComply trade products and materials — Electrical, Fire, Security and Gas. Shop at shop.icomplypropertyservices.co.uk or enquire from Stockport SK2.';
$metaKeywords = 'icomply shop, trade electrical, fire safety products, security products, gas enquire';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/products.php');
$shop = icomplyTradeShopUrl();

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
?>
<section class="relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a> / <span class="text-white/80">Products</span>
        </nav>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Trade products<br><span class="text-[#FF6B00]">&amp; materials</span></h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">Electrical, Fire, Security and Gas — buy on the Shopify store or pair with an install from the Stockport team. This hub does not redirect to packages.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars($shop, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#FF6B00] font-semibold" target="_blank" rel="noopener">Open shop.icomplypropertyservices.co.uk</a>
            <a href="<?= url('/shop/index.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">On-site trade shop</a>
            <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Enquire</a>
        </div>
    </div>
</section>
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="<?= url('/pages/electrical-safety-landlords') ?>">
            <div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Electrical</div>
            <h2 class="text-xl font-semibold mt-2 text-black">Electrical</h2>
            <p class="mt-2 text-sm text-zinc-600">Landlord EICR and electrical safety hub. Trade parts via the shop.</p>
        </a>
        <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="<?= url('/pages/commercial-fire-safety') ?>">
            <div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Fire</div>
            <h2 class="text-xl font-semibold mt-2 text-black">Fire</h2>
            <p class="mt-2 text-sm text-zinc-600">Commercial fire safety hub. Alarms, lighting and extinguishers in the shop.</p>
        </a>
        <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="<?= url('/pages/services/cctv') ?>">
            <div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Security</div>
            <h2 class="text-xl font-semibold mt-2 text-black">Security</h2>
            <p class="mt-2 text-sm text-zinc-600">CCTV and access control. Kits and accessories on shop.*</p>
        </a>
        <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="<?= url('/pages/gas-safety-certificate') ?>">
            <div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Gas</div>
            <h2 class="text-xl font-semibold mt-2 text-black">Gas</h2>
            <p class="mt-2 text-sm text-zinc-600">Enquire hub for landlord gas safety records — not a published parts catalogue.</p>
        </a>
    </div>
    <?php /* Marketing subdomain optional until DNS live: https://marketing.icomplypropertyservices.co.uk */ ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
