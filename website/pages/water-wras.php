<?php
/**
 * Water / WRAS / drinking water job lane.
 * Draft catalogue only — not a production promote. POA. No scheme-membership claims.
 */
if (!defined('SITE_ROOT')) {
    require_once __DIR__ . '/../config.php';
}
require_once SITE_ROOT . '/includes/share.php';

$groups = function_exists('waterJobTypesBySublane') ? waterJobTypesBySublane() : [];
$expected = function_exists('waterJobTypesExpected') ? waterJobTypesExpected() : ['total' => 0];
$labels = [
    'wras' => ['WRAS and water fittings regulations', 'Notification, backflow devices, fluid categories and air gaps under the Water Supply (Water Fittings) Regulations 1999.'],
    'drinking-water' => ['Drinking water', 'Wholesome outlets, labelling, lead and flush-to-waste. Not a Drinking Water Inspectorate laboratory suite.'],
    'water-fittings' => ['Wholesome-water fittings', 'Tanks, valves, hoses and mixers looked at as fittings on the supply, not as a generic plumbing price list.'],
];

$pageTitle = 'Water, WRAS and Drinking Water Jobs';
$metaDesc = 'Water fittings, WRAS-style backflow protection and drinking-water points across the North West. Price on application from Stockport. Not a lab suite and not a Legionella test.';
$metaKeywords = 'WRAS fittings, water fittings regulations, backflow prevention, drinking water tap, wholesome water, Stockport, North West, POA';
$canonicalUrl = url('/pages/water-wras.php');
$ogImage = url('/assets/images/services/plumbing.jpg');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#061828] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/70 mb-5 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span class="text-white/40">/</span>
            <a href="<?= url('/pages/services/water-wras.php') ?>" class="hover:text-white">Water, WRAS &amp; drinking water</a>
            <span class="text-white/40">/</span>
            <span class="text-white font-medium">Job lane</span>
        </nav>
        <p class="text-xs font-bold tracking-widest uppercase text-[#ff6b00] mb-4">Job lane · <?= (int)($expected['total'] ?? 0) ?> guides · POA</p>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight max-w-3xl leading-[1.08]">Water, WRAS and drinking water</h1>
        <p class="mt-5 text-lg text-white/90 max-w-2xl">Scoped fittings and backflow work from Stockport. Where the water undertaker must be notified, we help describe the job. We do not approve it for them, and we do not sell a drinking-water laboratory certificate.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white">Get a POA quote</a>
            <a href="<?= url('/pages/services/legionella-risk-assessment.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold">Legionella is a separate service</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-bold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-8 grid md:grid-cols-3 gap-6">
        <?php foreach ($labels as $key => [$title, $text]): ?>
            <a href="#<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" class="block rounded-2xl border-2 border-zinc-200 p-5 hover:border-[#ff6b00]">
                <div class="text-xs font-bold uppercase tracking-wider text-[#ff6b00]"><?= count($groups[$key] ?? []) ?> jobs</div>
                <h2 class="mt-2 text-xl font-bold text-[#061828]"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mt-2 text-sm text-zinc-800"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php foreach ($labels as $key => [$title, $text]): ?>
<section id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" class="bg-zinc-100 border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-2 text-zinc-800 max-w-3xl"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></p>
        <ul class="mt-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($groups[$key] ?? [] as $job):
                $slug = keywordSlug((string)($job['slug'] ?? ''));
                $name = (string)($job['name'] ?? $slug);
                ?>
                <li>
                    <a class="block bg-white border-2 border-zinc-200 rounded-2xl px-4 py-3 font-semibold text-[#061828] hover:border-[#ff6b00]"
                       href="<?= url('/pages/keywords/' . rawurlencode($slug) . '.php') ?>">
                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endforeach; ?>

<section id="quote" class="bg-[#061828] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-center">Quote a water fittings job</h2>
        <p class="mt-2 text-center text-white/90">Price on application after scope. Tell us the fitting, the postcode, and whether the undertaker has written to you.</p>
        <form action="<?= url('/contact.php') ?>" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4 shadow-xl">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="text" name="service" value="Water, WRAS and drinking water" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <textarea name="message" rows="4" required placeholder="Postcode, fitting, and any undertaker letter…" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
