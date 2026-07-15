<?php
/**
 * Admin: connect Shopify + map product/collection IDs.
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/shopify.php';

if (empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$msg = '';
$err = '';

$localPath = SITE_ROOT . '/config.local.php';
$catalog = getShopCatalog();

function writeConfigLocal(array $overrides): bool {
    $path = SITE_ROOT . '/config.local.php';
    $existing = [];
    if (is_file($path)) {
        $loaded = include $path;
        if (is_array($loaded)) {
            $existing = $loaded;
        }
    }
    $merged = array_merge($existing, $overrides);
    $export = var_export($merged, true);
    $php = "<?php\n/** Auto-updated by admin/shopify.php — do not commit secrets. */\nreturn {$export};\n";
    return (bool)file_put_contents($path, $php);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_credentials') {
        $domain = trim((string)($_POST['SHOPIFY_DOMAIN'] ?? ''));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');
        $storeUrl = trim((string)($_POST['SHOPIFY_STORE_URL'] ?? ''));
        if ($storeUrl === '' && $domain !== '') {
            $storeUrl = 'https://' . $domain;
        }
        $token = trim((string)($_POST['SHOPIFY_STOREFRONT_TOKEN'] ?? ''));
        $coll = trim((string)($_POST['SHOPIFY_COLLECTION_ID'] ?? ''));
        $enabled = !empty($_POST['SHOPIFY_ENABLED']);

        if ($enabled && ($domain === '' || $token === '')) {
            $err = 'Domain and Storefront token are required when enabling Shopify.';
        } else {
            $ok = writeConfigLocal([
                'SHOPIFY_DOMAIN' => $domain,
                'SHOPIFY_STORE_URL' => $storeUrl,
                'SHOPIFY_STOREFRONT_TOKEN' => $token,
                'SHOPIFY_COLLECTION_ID' => $coll,
                'SHOPIFY_ENABLED' => $enabled,
            ]);
            if ($ok) {
                // Reload constants aren't redefinable — redirect so next request picks up new local
                header('Location: shopify.php?msg=' . rawurlencode('Credentials saved. Reload site pages to pick up Buy Buttons.'));
                exit;
            }
            $err = 'Could not write config.local.php — check file permissions.';
        }
    }

    if ($action === 'save_products') {
        $data = loadJsonData('shopify-products', ['collections' => [], 'products' => []]);
        $ids = $_POST['product_id'] ?? [];
        $handles = $_POST['product_handle'] ?? [];
        if (is_array($ids)) {
            foreach ($data['products'] as $i => $p) {
                $pid = $p['id'] ?? '';
                if (isset($ids[$pid])) {
                    $data['products'][$i]['shopify_product_id'] = preg_replace('/\D+/', '', (string)$ids[$pid]);
                }
                if (isset($handles[$pid])) {
                    $h = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$handles[$pid]));
                    if ($h !== '') {
                        $data['products'][$i]['handle'] = $h;
                    }
                }
            }
        }
        $cids = $_POST['collection_id'] ?? [];
        $chandles = $_POST['collection_handle'] ?? [];
        if (is_array($cids)) {
            foreach ($data['collections'] as $i => $c) {
                $cid = $c['id'] ?? '';
                if (isset($cids[$cid])) {
                    $data['collections'][$i]['shopify_collection_id'] = preg_replace('/\D+/', '', (string)$cids[$cid]);
                }
                if (isset($chandles[$cid])) {
                    $h = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$chandles[$cid]));
                    if ($h !== '') {
                        $data['collections'][$i]['handle'] = $h;
                    }
                }
            }
        }
        saveJsonData('shopify-products', $data);
        header('Location: shopify.php?msg=' . rawurlencode('Product & collection IDs saved.'));
        exit;
    }

    if ($action === 'test_api') {
        if (!shopifyEnabled()) {
            $err = 'Shopify not enabled / missing credentials. Save credentials first (then re-login session still works).';
        } else {
            $endpoint = 'https://' . preg_replace('#^https?://#', '', SHOPIFY_DOMAIN) . '/api/2024-01/graphql.json';
            $payload = json_encode([
                'query' => '{ shop { name primaryDomain { url } } products(first: 3) { edges { node { id title handle } } } }',
            ]);
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Shopify-Storefront-Access-Token: ' . SHOPIFY_STOREFRONT_TOKEN,
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);
            $raw = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $cerr = curl_error($ch);
            curl_close($ch);
            if ($raw === false) {
                $err = 'API request failed: ' . $cerr;
            } else {
                $json = json_decode($raw, true);
                if ($code >= 200 && $code < 300 && empty($json['errors'])) {
                    $shopName = $json['data']['shop']['name'] ?? 'OK';
                    $msg = "Storefront API OK — shop: {$shopName}. Sample products returned: "
                        . count($json['data']['products']['edges'] ?? []);
                } else {
                    $err = 'API error HTTP ' . $code . ': ' . substr($raw, 0, 400);
                }
            }
        }
    }
}

if (isset($_GET['msg'])) {
    $msg = (string)$_GET['msg'];
}

// Re-read catalog after possible save
$catalog = getShopCatalog();
$products = $catalog['products'];
$collections = $catalog['collections'];
$live = shopifyEnabled();
$mapped = count(array_filter($products, fn($p) => trim((string)($p['shopify_product_id'] ?? '')) !== ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopify · Admin · Icomply</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">
</head>
<body class="bg-zinc-50 text-black">
<div class="max-w-5xl mx-auto p-8">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
        <div>
            <div class="text-xs uppercase tracking-widest text-[#ff6b00] font-semibold">Admin</div>
            <h1 class="text-3xl font-semibold tracking-tight">Shopify connection</h1>
            <p class="text-sm text-zinc-600 mt-1">
                Status:
                <?php if ($live): ?>
                    <span class="text-emerald-700 font-semibold">LIVE</span>
                    · <?= htmlspecialchars(SHOPIFY_DOMAIN, ENT_QUOTES, 'UTF-8') ?>
                    · <?= $mapped ?>/<?= count($products) ?> products mapped
                <?php else: ?>
                    <span class="text-amber-700 font-semibold">NOT CONNECTED</span>
                    — paste credentials below (from the agent / Shopify Admin)
                <?php endif; ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="index.php" class="px-4 py-2 bg-white border rounded-xl text-sm">← Dashboard</a>
            <a href="<?= url('/shop/index.php') ?>" target="_blank" class="px-4 py-2 bg-[#0a2540] text-white rounded-xl text-sm">Open shop</a>
        </div>
    </div>

    <?php if ($msg): ?><div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($err): ?><div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-sm"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <section class="bg-white border rounded-3xl p-6 md:p-8 mb-8">
        <h2 class="text-xl font-semibold mb-2">1. Store credentials</h2>
        <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-sm text-amber-950 space-y-2">
            <p class="font-semibold">Do not paste Client ID / Client secret (shpss_…) here.</p>
            <p>Those are OAuth app credentials. This site needs the <strong>Storefront API access token</strong> from the same app (under <em>API credentials → Storefront API</em>).</p>
            <p>Admin API tokens start with <code>shpat_</code> — also wrong for Buy Buttons (use only if you build a private Admin sync tool).</p>
        </div>
        <p class="text-sm text-zinc-600 mb-6">
            Shopify Admin → <strong>Settings → Apps → Develop apps</strong> → <em>Product Import new</em> →
            <strong>API credentials</strong> → <strong>Storefront API</strong> → reveal token → paste below.
            Product IDs: Admin → Products → open product → URL ends with <code>/products/1234567890</code>.
        </p>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="save_credentials">
            <div class="grid md:grid-cols-2 gap-4">
                <label class="block text-sm">
                    <span class="font-medium">SHOPIFY_DOMAIN</span>
                    <input name="SHOPIFY_DOMAIN" value="<?= htmlspecialchars(SHOPIFY_DOMAIN, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="your-store.myshopify.com" class="mt-1 w-full border px-4 py-3 rounded-xl">
                </label>
                <label class="block text-sm">
                    <span class="font-medium">SHOPIFY_STORE_URL</span>
                    <input name="SHOPIFY_STORE_URL" value="<?= htmlspecialchars(SHOPIFY_STORE_URL, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="https://your-store.myshopify.com" class="mt-1 w-full border px-4 py-3 rounded-xl">
                </label>
            </div>
            <label class="block text-sm">
                <span class="font-medium">SHOPIFY_STOREFRONT_TOKEN</span>
                <input name="SHOPIFY_STOREFRONT_TOKEN" value="<?= htmlspecialchars(SHOPIFY_STOREFRONT_TOKEN, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Storefront API access token" class="mt-1 w-full border px-4 py-3 rounded-xl font-mono text-xs">
            </label>
            <label class="block text-sm">
                <span class="font-medium">SHOPIFY_COLLECTION_ID (optional default)</span>
                <input name="SHOPIFY_COLLECTION_ID" value="<?= htmlspecialchars((string)SHOPIFY_COLLECTION_ID, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="numeric collection id" class="mt-1 w-full border px-4 py-3 rounded-xl">
            </label>
            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="checkbox" name="SHOPIFY_ENABLED" value="1" <?= SHOPIFY_ENABLED || shopifyEnabled() ? 'checked' : '' ?>>
                Enable Shopify Buy Buttons
            </label>
            <div class="flex flex-wrap gap-3">
                <button class="px-6 py-3 bg-[#0a2540] text-white rounded-2xl font-semibold">Save credentials</button>
            </div>
        </form>
        <form method="POST" class="mt-4">
            <input type="hidden" name="action" value="test_api">
            <button class="px-5 py-2.5 border rounded-xl text-sm font-medium hover:border-[#ff6b00]">Test Storefront API</button>
        </form>
    </section>

    <section class="bg-white border rounded-3xl p-6 md:p-8 mb-8">
        <h2 class="text-xl font-semibold mb-2">2. Collection IDs</h2>
        <form method="POST">
            <input type="hidden" name="action" value="save_products">
            <div class="space-y-3 mb-8">
                <?php foreach ($collections as $c):
                    $cid = $c['id'] ?? '';
                ?>
                <div class="grid md:grid-cols-3 gap-3 items-end border-b pb-3">
                    <div class="text-sm font-medium"><?= htmlspecialchars($c['title'] ?? $cid, ENT_QUOTES, 'UTF-8') ?></div>
                    <label class="text-xs">Shopify collection ID
                        <input name="collection_id[<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>]"
                               value="<?= htmlspecialchars((string)($c['shopify_collection_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               class="mt-1 w-full border px-3 py-2 rounded-lg">
                    </label>
                    <label class="text-xs">Handle
                        <input name="collection_handle[<?= htmlspecialchars($cid, ENT_QUOTES, 'UTF-8') ?>]"
                               value="<?= htmlspecialchars((string)($c['handle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               class="mt-1 w-full border px-3 py-2 rounded-lg">
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <h2 class="text-xl font-semibold mb-2">3. Product IDs</h2>
            <p class="text-sm text-zinc-600 mb-4">Map each local card to a Shopify product. Leave blank to show “View in shop” link only.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="text-left border-b">
                        <th class="py-2 pr-2">Product</th>
                        <th class="py-2 pr-2">Shopify product ID</th>
                        <th class="py-2">Handle</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($products as $p):
                        $pid = $p['id'] ?? '';
                    ?>
                    <tr class="border-b align-top">
                        <td class="py-3 pr-2">
                            <div class="font-medium"><?= htmlspecialchars($p['title'] ?? $pid, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="text-xs text-zinc-500"><?= htmlspecialchars($pid, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($p['price'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td class="py-3 pr-2">
                            <input name="product_id[<?= htmlspecialchars($pid, ENT_QUOTES, 'UTF-8') ?>]"
                                   value="<?= htmlspecialchars((string)($p['shopify_product_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full border px-3 py-2 rounded-lg font-mono text-xs" placeholder="e.g. 8123456789">
                        </td>
                        <td class="py-3">
                            <input name="product_handle[<?= htmlspecialchars($pid, ENT_QUOTES, 'UTF-8') ?>]"
                                   value="<?= htmlspecialchars((string)($p['handle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full border px-3 py-2 rounded-lg text-xs">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="mt-6 px-6 py-3 bg-[#ff6b00] text-white rounded-2xl font-semibold">Save product &amp; collection mapping</button>
        </form>
    </section>

    <p class="text-xs text-zinc-500">Credentials write to <code>config.local.php</code>. Product IDs write to <code>data/shopify-products.json</code>.</p>
</div>
</body>
</html>
