<?php
/**
 * Friendly 404 — matches site brand (header/footer, navy + orange).
 * Served via .htaccess ErrorDocument or direct request.
 */
require_once __DIR__ . '/config.php';

http_response_code(404);

$pageTitle = 'Page Not Found';
$metaDesc = 'Sorry — that page could not be found on Icomply Property Services. Browse our services, areas, manufacturers or contact us for a free quote.';
$metaRobots = 'noindex, follow';
$canonicalUrl = url('/404.php');

$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

// What the visitor tried to open (when Apache ErrorDocument hands off)
$requested = (string)($_SERVER['REQUEST_URI'] ?? '');
// Avoid showing the 404 script path itself as the “missing” URL
if ($requested !== '' && preg_match('#/404\.php(\?|$)#i', $requested)) {
    $requested = '';
}

$quickLinks = [
    ['label' => 'Home', 'href' => $homeUrl, 'blurb' => 'Back to the homepage'],
    ['label' => 'Services', 'href' => url('/pages/services/index.php'), 'blurb' => 'Electrical, fire, gas, CCTV & more'],
    ['label' => 'Areas', 'href' => url('/pages/areas/index.php'), 'blurb' => 'Towns we cover across the North West'],
    ['label' => 'Manufacturers', 'href' => url('/pages/manufacturers/index.php'), 'blurb' => 'Brands we install & supply'],
    ['label' => 'Contact', 'href' => url('/contact.php'), 'blurb' => 'Free quote — call, WhatsApp or form'],
];

require SITE_ROOT . '/includes/header.php';
?>
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-4xl mx-auto px-6 py-16 md:py-24 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-6">
            <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
            Error 404
        </div>
        <p class="text-7xl md:text-8xl font-semibold tracking-tighter text-[#ff6b00] leading-none">404</p>
        <h1 class="mt-4 text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tighter">
            We can’t find that page
        </h1>
        <p class="mt-5 text-lg text-white/80 max-w-xl mx-auto">
            The link may be out of date, or the page may have moved.
            Use the shortcuts below — or get a free compliance quote.
        </p>
        <?php if ($requested !== ''): ?>
            <p class="mt-4 text-sm text-white/50 break-all">
                Requested: <span class="text-white/70 font-mono text-xs"><?= htmlspecialchars($requested, ENT_QUOTES, 'UTF-8') ?></span>
            </p>
        <?php endif; ?>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>"
               class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">
                Go to homepage
            </a>
            <a href="<?= url('/contact.php') ?>"
               class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">
                Contact / free quote
            </a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20need%20help%20finding%20a%20page"
               target="_blank" rel="noopener"
               class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">
                WhatsApp
            </a>
        </div>
    </div>
</section>

<section class="max-w-5xl mx-auto px-6 py-14">
    <h2 class="text-2xl font-semibold tracking-tight text-black text-center mb-2">Where to next?</h2>
    <p class="text-center text-zinc-600 mb-10">Popular sections of <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></p>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($quickLinks as $link): ?>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"
               class="service-card block bg-white border border-zinc-200 rounded-2xl p-6 hover:border-[#ff6b00] group">
                <div class="font-semibold text-lg text-black group-hover:text-[#ff6b00] transition">
                    <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
                    <span class="text-[#ff6b00] ml-1">→</span>
                </div>
                <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars($link['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mt-12 rounded-3xl bg-white border border-zinc-200 p-8 md:p-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h3 class="text-xl font-semibold text-black">Still stuck?</h3>
            <p class="mt-2 text-zinc-600 max-w-md">
                Call or message us — we’ll point you to the right service or arrange a free quote.
            </p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"
               class="modern-btn px-6 py-3.5 rounded-2xl text-white font-semibold">
                <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a href="<?= url('/contact.php') ?>"
               class="px-6 py-3.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#ff6b00] hover:text-[#ff6b00] transition">
                Contact form
            </a>
        </div>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
