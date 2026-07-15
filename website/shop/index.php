<?php
/**
 * Shop — Shopify-optimised product & collection cards + Buy Button widgets.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/shopify.php';

$pageTitle = 'Shop | Compliance Products & Kits';
$metaDesc = 'Shop fire safety, electrical, CCTV and emergency lighting products from Icomply. Trade kits and install accessories for North West engineers and landlords.';
$metaKeywords = 'fire alarm parts, emergency lighting buy, CCTV kits, electrical trade supplies Stockport';
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$catalog = getShopCatalog();
$collections = $catalog['collections'];
$products = $catalog['products'];
$filter = isset($_GET['c']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$_GET['c'])) : '';

if ($filter !== '') {
    $products = array_values(array_filter($products, function ($p) use ($filter) {
        return ($p['collection'] ?? '') === $filter;
    }));
}

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#0a2540] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-20">
        <div class="max-w-2xl">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Icomply shop</div>
            <h1 class="text-4xl md:text-5xl font-semibold tracking-tighter">Trade products &amp; install kits</h1>
            <p class="mt-4 text-lg text-white/80">Fire, electrical, security and emergency lighting gear — Shopify cart &amp; checkout when connected.</p>
            <?php if (shopifyEnabled()): ?>
                <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-200 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Shopify live — add to cart available on mapped products
                </div>
            <?php endif; ?>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#products" class="px-6 py-3 bg-[#ff6b00] hover:bg-orange-600 rounded-2xl font-semibold text-white">Browse products</a>
                <a href="<?= url('/contact.php') ?>" class="px-6 py-3 border border-white/40 hover:bg-white/10 rounded-2xl font-semibold">Trade account / bulk quote</a>
                <?php if (shopifyStoreUrl()): ?>
                    <a href="<?= htmlspecialchars(shopifyStoreUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-6 py-3 bg-white text-[#0a2540] rounded-2xl font-semibold">Open full Shopify store →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex items-end justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Shop by category</h2>
            <p class="text-zinc-600 mt-1">Direct collection cards — map each to a Shopify collection ID for live widgets.</p>
        </div>
        <a href="<?= url('/shop/index.php') ?>" class="text-sm font-semibold text-[#ff6b00] hidden sm:inline">All products</a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($collections as $col): ?>
            <?= shopifyCollectionCardHtml($col) ?>
        <?php endforeach; ?>
    </div>
</section>

<section id="products" class="bg-white border-t border-b">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-semibold tracking-tight text-black">Featured products</h2>
                <p class="text-zinc-600 mt-1">
                    <?php if ($filter): ?>
                        Showing: <strong><?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?></strong>
                        · <a href="<?= url('/shop/index.php') ?>" class="text-[#ff6b00]">Clear filter</a>
                    <?php else: ?>
                        Cards ready for Shopify Buy Button product IDs
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= url('/shop/index.php') ?>" class="px-3 py-1.5 rounded-full text-xs font-medium border <?= $filter === '' ? 'bg-[#0a2540] text-white border-[#0a2540]' : 'bg-white text-black' ?>">All</a>
                <?php foreach ($collections as $col):
                    $cid = $col['id'] ?? '';
                    $active = $filter === $cid;
                ?>
                    <a href="<?= url('/shop/index.php?c=' . rawurlencode($cid)) ?>"
                       class="px-3 py-1.5 rounded-full text-xs font-medium border <?= $active ? 'bg-[#0a2540] text-white border-[#0a2540]' : 'bg-white text-black hover:border-[#ff6b00]' ?>">
                        <?= htmlspecialchars($col['title'] ?? $cid, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$products): ?>
            <div class="p-10 border rounded-3xl text-center text-zinc-600">No products in this category yet.</div>
        <?php else: ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($products as $p): ?>
                    <?= shopifyProductCardHtml($p) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!shopifyEnabled()): ?>
            <div class="mt-10 p-6 rounded-3xl bg-amber-50 border border-amber-200 text-sm text-amber-900 space-y-3">
                <p><strong>Store linked:</strong> <code><?= htmlspecialchars(SHOPIFY_DOMAIN !== '' ? SHOPIFY_DOMAIN : 'icomply-supplys.myshopify.com', ENT_QUOTES, 'UTF-8') ?></code></p>
                <p><strong>Missing:</strong> Storefront API access token (Buy Buttons / cart).</p>
                <ol class="list-decimal ml-5 space-y-1">
                    <li>Shopify Admin → <strong>Settings → Apps → Develop apps</strong> → open <em>Product Import new</em></li>
                    <li><strong>API credentials</strong> → <strong>Storefront API</strong> section → reveal / copy the <em>Storefront API access token</em></li>
                    <li>Paste it at <a class="underline font-semibold" href="<?= url('/admin/shopify.php') ?>">Admin → Shopify setup</a> and save</li>
                    <li>Map each product’s numeric ID (Admin → Products → open product → ID in the URL)</li>
                </ol>
                <p class="text-xs">Do <strong>not</strong> use the Admin API access token — the site uses the Storefront Buy Button SDK.</p>
                <?php if (shopifyStoreUrl()): ?>
                    <p><a class="font-semibold text-[#0a2540] underline" href="<?= htmlspecialchars(shopifyStoreUrl(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Open Shopify storefront →</a></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="mt-8 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                Shopify connected to <strong><?= htmlspecialchars(SHOPIFY_DOMAIN, ENT_QUOTES, 'UTF-8') ?></strong> — Buy Buttons mount where product IDs are set.
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight text-black">Shop by manufacturer</h2>
            <p class="text-zinc-600 mt-1">Trade kits and spares from the brands we install across the North West.</p>
        </div>
        <a href="<?= url('/pages/manufacturers/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">View all manufacturers →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
        <?php
        $shopMfrTeaser = [
            ['name' => 'Kentec', 'slug' => 'kentec', 'img' => 'kentec.jpg'],
            ['name' => 'Paxton', 'slug' => 'paxton', 'img' => 'paxton.jpg'],
            ['name' => 'Hikvision', 'slug' => 'hikvision', 'img' => 'hikvision.jpg'],
            ['name' => 'Schneider', 'slug' => 'schneider-electric', 'img' => 'schneider-electrical.jpg'],
            ['name' => 'Myenergi', 'slug' => 'myenergi', 'img' => 'myenergi-ev-charger.jpg'],
            ['name' => 'Texecom', 'slug' => 'texecom', 'img' => 'texecom.jpg'],
        ];
        foreach ($shopMfrTeaser as $mfr):
        ?>
            <a href="<?= url('/pages/manufacturers/' . $mfr['slug'] . '.php') ?>"
               class="group p-4 bg-white border rounded-2xl hover:border-[#ff6b00] transition text-center">
                <img src="<?= url('/assets/images/manufacturers/' . $mfr['img']) ?>"
                     alt="<?= htmlspecialchars($mfr['name'], ENT_QUOTES, 'UTF-8') ?>"
                     class="h-12 w-auto mx-auto object-contain mb-3 group-hover:scale-105 transition"
                     loading="lazy" width="96" height="48">
                <div class="text-sm font-semibold text-black"><?= htmlspecialchars($mfr['name'], ENT_QUOTES, 'UTF-8') ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="p-8 bg-white border rounded-3xl">
            <div class="text-2xl mb-3">🚚</div>
            <h3 class="font-semibold text-lg">Trade supply</h3>
            <p class="text-sm text-zinc-600 mt-2">Kits aligned to the systems we install and maintain across the North West.</p>
        </div>
        <div class="p-8 bg-white border rounded-3xl">
            <div class="text-2xl mb-3">🛒</div>
            <h3 class="font-semibold text-lg">Shopify checkout</h3>
            <p class="text-sm text-zinc-600 mt-2">Secure cart and payment via Shopify when your store credentials are linked.</p>
        </div>
        <div class="p-8 bg-white border rounded-3xl">
            <div class="text-2xl mb-3">🔧</div>
            <h3 class="font-semibold text-lg">Need install too?</h3>
            <p class="text-sm text-zinc-600 mt-2">Pair any product with <a class="text-[#ff6b00] font-medium" href="<?= url('/contact.php') ?>">engineer installation</a> or servicing.</p>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-8">
    <?php require_once SITE_ROOT . '/includes/share.php'; ?>
    <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
</section>

<?= shopifyBuyButtonScript() ?>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
