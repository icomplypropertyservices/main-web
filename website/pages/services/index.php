<?php
/**
 * SERVICES_INDEX — conversion-focused hub listing all core services.
 */
require_once __DIR__ . '/../../config.php';

$pageTitle = 'All Services | Fire Safety, Professional & Construction | North West';
$metaDesc = 'Browse Icomply services: fire safety systems and fire risk assessments, electrical, gas, security, professional services, kitchens, bathrooms, renovation and construction across the North West.';
$metaKeywords = 'fire risk assessment, fire safety systems, kitchen fitting, bathroom renovation, plastering, landlord compliance, EICR, CCTV, Manchester, Stockport, North West';
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$services = getServices();
$areas = getAreas();
$categories = getServiceCategories();

// Full blurbs via getServiceBlurb($slug) — see includes/content.php / config.php

$popularTowns = array_values(array_filter(
    ['Manchester', 'Stockport', 'Bolton', 'Liverpool', 'Preston', 'Warrington'],
    function ($t) use ($areas) {
        return in_array($t, $areas, true);
    }
));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/80">Services</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= count($services) ?> core services · North West
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                Fire safety, professional<br>
                <span class="text-[#ff6b00]">&amp; construction services</span>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl">
                Full fire safety systems including fire risk assessments, electrical &amp; gas, security,
                professional compliance support, plus kitchens, bathrooms, renovation and building trades —
                fixed-price quotes across <?= count($areas) ?>+ North West towns.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#fire-safety" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Fire safety</a>
                <a href="#professional" class="px-8 py-4 rounded-2xl bg-white text-[#0a2540] font-semibold hover:bg-zinc-100">Professional</a>
                <a href="#construction" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Construction</a>
                <a href="<?= url('/pages/areas/index.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Areas</a>
            </div>
        </div>
    </div>
</section>

<!-- TRUST -->
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            ['Local engineers', 'Based in Stockport — covering Greater Manchester & the North West'],
            ['Standards-led', 'BS 5839, BS 5266, BS 7671, gas safety & more'],
            ['Fixed-price quotes', 'Clear scope, documentation and certification'],
            ['Trade shop', 'Kits & parts with Shopify checkout when live'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CATEGORY GRIDS -->
<?php
$catAlt = false;
foreach ($categories as $catKey => $cat):
    $catServices = getServicesInCategory($catKey);
    if (!$catServices) {
        continue;
    }
    $sectionClass = $catAlt ? 'bg-zinc-50 border-y' : '';
    $catAlt = !$catAlt;
?>
<section id="<?= htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8') ?>" class="<?= $sectionClass ?> scroll-mt-24">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($cat['label'] ?? $catKey, ENT_QUOTES, 'UTF-8') ?></div>
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2"><?= htmlspecialchars($cat['label'] ?? $catKey, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if (!empty($cat['blurb'])): ?>
                    <p class="mt-2 text-zinc-600 max-w-2xl"><?= htmlspecialchars($cat['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
            <span class="text-sm text-zinc-500"><?= count($catServices) ?> services · <?= count($areas) ?>+ towns each</span>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($catServices as $slug => $name):
                $blurb = getServiceBlurb($slug);
                $img = url('/assets/images/services/' . $slug . '.jpg');
            ?>
            <a href="<?= url('/pages/services/' . $slug . '.php') ?>"
               class="service-card group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">
                <div class="h-44 bg-zinc-100 overflow-hidden">
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> services by Icomply Property Services"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         loading="lazy"
                         onerror="this.src='<?= htmlspecialchars(url('/assets/images/services/fire-alarms.jpg'), ENT_QUOTES, 'UTF-8') ?>'">
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <h3 class="font-semibold text-xl text-black tracking-tight"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="mt-5 flex items-center justify-between">
                        <span class="text-sm font-semibold text-[#ff6b00]">View service →</span>
                        <span class="text-xs text-zinc-400"><?= count($areas) ?>+ areas</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<!-- POPULAR COMBOS -->
<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local pages</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Popular service × area pages</h2>
                <p class="mt-2 text-zinc-600">Jump straight to a town-specific landing page.</p>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All areas →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php
            $showcaseServices = array_slice($services, 0, 5, true);
            foreach ($popularTowns as $town):
                foreach ($showcaseServices as $sSlug => $sName):
            ?>
                <a href="<?= url('/pages/' . $sSlug . '/' . areaSlug($town) . '.php') ?>"
                   class="px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($sName . ' in ' . $town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; endforeach; ?>
        </div>
    </div>
</section>

<!-- PACKAGE CTA -->
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Packages</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Need more than one service?</h2>
            <p class="mt-4 text-zinc-600 text-lg">
                Landlords and facilities teams often combine EICR, fire alarms, emergency lighting and gas safety
                into one schedule. We quote multi-service packages with a single point of contact.
            </p>
            <ul class="mt-6 space-y-3 text-sm text-zinc-700">
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> One engineer visit plan where practical</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Shared documentation pack for audits</li>
                <li class="flex gap-2"><span class="text-[#ff6b00] font-bold">✓</span> Maintenance contracts available</li>
            </ul>
        </div>
        <div class="bg-[#0a2540] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold">Talk to us today</h3>
            <p class="mt-3 text-white/80">Call, WhatsApp or use the quote form — we aim to respond within 2 hours on business days.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
                   class="px-6 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
                <a href="#quote" class="px-6 py-3 rounded-2xl border border-white/30 font-semibold hover:bg-white/10">Quote form</a>
            </div>
        </div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request your free quote</h2>
            <p class="mt-3 text-zinc-600">All quotes are fixed-price after scope is agreed.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <?php foreach ($services as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                    <option value="Multi-service package">Multi-service package</option>
                    <option value="Shop / products">Shop / products</option>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000" placeholder="Postcode, property type, panel brand / system details…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
