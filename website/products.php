<?php
/**
 * Products / life-safety systems hub.
 * Featured first: AOV & Smoke Control. Service deep-links only — POA / Get a quote.
 * No invented catalogue or seed prices.
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Products | Life-safety systems';
$metaDesc = 'Life-safety systems from iComply: AOV & smoke control, fire alarms, emergency lighting, fire doors, sprinklers, dry risers and evacuation alerts. POA after scope — request a quote. Trade supplies via the fire shop hub.';
$metaKeywords = 'AOV, smoke control, fire alarms, emergency lighting, fire doors, sprinkler systems, dry risers, evacuation alerts, life safety products, North West';
$canonicalUrl = url('/products.php');
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$metaRobots = 'index, follow, max-image-preview:large';

$services = getServices();

$featured = [
    [
        'slug' => 'aov-air-handling',
        'label' => 'AOV & Smoke Control',
        'blurb' => 'Smoke vents, AOV panels and smoke-control systems for blocks and commercial sites — EN 12101 / BS 9991. Priority product line. Quotes POA after scope.',
        'priority' => true,
    ],
    [
        'slug' => 'fire-alarms',
        'label' => 'Fire alarms',
        'blurb' => 'Design, install, service and certification to BS 5839. POA after survey.',
        'priority' => false,
    ],
    [
        'slug' => 'emergency-lighting',
        'label' => 'Emergency lighting',
        'blurb' => 'Testing and upgrades to BS 5266. POA after scope.',
        'priority' => false,
    ],
    [
        'slug' => 'fire-doors',
        'label' => 'Fire doors',
        'blurb' => 'Inspection, certification and replacement where required. POA.',
        'priority' => false,
    ],
    [
        'slug' => 'sprinkler-systems',
        'label' => 'Sprinkler systems',
        'blurb' => 'Inspection and support for suppression systems. POA after survey.',
        'priority' => false,
    ],
    [
        'slug' => 'dry-risers',
        'label' => 'Dry risers',
        'blurb' => 'Testing and maintenance for rising mains. POA.',
        'priority' => false,
    ],
    [
        'slug' => 'evacuation-alerts',
        'label' => 'Evacuation alerts',
        'blurb' => 'Evacuation alert systems for residential and multi-occupancy. POA.',
        'priority' => false,
    ],
];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
  <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
    <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
      <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
      <span>/</span>
      <span class="text-white/80">Products</span>
    </nav>
    <div class="max-w-3xl">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
        <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
        Life-safety · POA after scope
      </div>
      <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
        Products /<br>
        <span class="text-[#ff6b00]">life-safety systems</span>
      </h1>
      <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
        Service and systems hubs for AOV &amp; smoke control and other fire life-safety lines.
        All work is <strong>POA</strong> until scoped — no catalogue prices online. Get a quote for your site.
      </p>
      <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?= url('/pages/services/aov-air-handling.php') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">AOV &amp; Smoke Control</a>
        <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">Get a quote</a>
        <a href="/shop/fire/" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Trade supplies →</a>
      </div>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
    <div>
      <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Featured first</div>
      <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Life-safety systems</h2>
      <p class="mt-2 text-zinc-600 max-w-2xl">
        Priority: AOV &amp; smoke control. Then core fire life-safety services. Parts and trade lines via the shop hub — service work remains quote-only (POA).
      </p>
    </div>
    <a href="<?= url('/pages/services/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All services →</a>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($featured as $card):
        $slug = $card['slug'];
        $name = isset($services[$slug]) ? (string)$services[$slug] : $card['label'];
        $href = url('/pages/services/' . rawurlencode($slug) . '.php');
        $border = !empty($card['priority'])
            ? 'border-[#ff6b00] ring-1 ring-[#ff6b00]/20 shadow-lg'
            : 'border-zinc-200 hover:border-[#ff6b00]';
    ?>
    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
       class="group bg-white border rounded-3xl p-6 md:p-8 flex flex-col transition <?= $border ?>">
      <?php if (!empty($card['priority'])): ?>
        <span class="inline-block self-start text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-[#ff6b00] text-white mb-3">Priority</span>
      <?php endif; ?>
      <h3 class="font-semibold text-xl text-black tracking-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
      <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($card['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="mt-5 flex items-center justify-between gap-3">
        <span class="text-sm font-semibold text-[#ff6b00]">View service →</span>
        <span class="text-xs text-zinc-400">POA</span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="mt-12 p-6 md:p-8 bg-zinc-50 border border-zinc-200 rounded-3xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h3 class="font-semibold text-lg text-black">Trade supplies</h3>
      <p class="text-sm text-zinc-600 mt-1">Fire category hub for trade materials. Service installs and AOV work stay POA / Get a quote — we do not invent product SKUs or catalogue prices here.</p>
    </div>
    <a href="/shop/fire/" class="shrink-0 px-6 py-3 rounded-2xl bg-[#0B1F3A] text-white font-semibold text-sm hover:bg-[#0B1F3A]/90">Open /shop/fire/ →</a>
  </div>
</section>

<section class="bg-zinc-50 border-t">
  <div class="max-w-3xl mx-auto px-6 py-16 text-center">
    <h2 class="text-3xl font-semibold tracking-tight text-black">Need a scoped quote?</h2>
    <p class="mt-3 text-zinc-600">Tell us the system type and postcode. All life-safety work is priced POA after scope — no seed or catalogue rates on this page.</p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
      <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
      <a href="<?= url('/pages/services/aov-air-handling.php') ?>" class="px-8 py-4 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0B1F3A]">AOV &amp; Smoke Control</a>
      <a href="<?= url('/pages/packages/fire-ready.php') ?>" class="px-8 py-4 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0B1F3A]">Fire Ready package</a>
    </div>
  </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
