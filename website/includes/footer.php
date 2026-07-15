<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/share.php';
$services = getServices();
$areas = getAreas();
$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$footerMfr = array_filter(getManufacturerCatalog(), fn($c) => !empty($c['featured']));
if (!$footerMfr) {
    $footerMfr = array_slice(getManufacturerCatalog(), 0, 10, true);
} else {
    $footerMfr = array_slice($footerMfr, 0, 10, true);
}
$popularAreas = array_values(array_unique(array_merge(
    ['Manchester', 'Stockport', 'Bolton', 'Oldham', 'Rochdale', 'Wigan', 'Salford', 'Liverpool', 'Preston', 'Blackpool', 'Chester', 'Warrington'],
    array_slice($areas, 0, 12)
)));
// Keep only areas that exist in the list
$areaSet = array_flip($areas);
$popularAreas = array_values(array_filter($popularAreas, function ($a) use ($areaSet) {
    return isset($areaSet[$a]);
}));
if (!$popularAreas) {
    $popularAreas = array_slice($areas, 0, 12);
}
?>
</div><!-- /#main-content -->
<footer class="bg-[#0a2540] text-white mt-8">
    <div class="max-w-7xl mx-auto px-6 pt-16 pb-10">
        <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 text-sm">
            <!-- Brand / contact -->
            <div>
                <div class="font-semibold text-white text-xl mb-2"><?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></div>
                <p class="text-white/70 text-sm leading-relaxed mb-5">Property compliance specialists — electrical, fire, gas, emergency lighting, CCTV and access control across the North West.</p>
                <div class="space-y-2 text-white/80 text-sm mb-5">
                    <div>
                        <span class="text-white/50 text-xs uppercase tracking-wider block mb-0.5">Phone</span>
                        <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-white hover:text-[#ff6b00] font-medium"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                    <div>
                        <span class="text-white/50 text-xs uppercase tracking-wider block mb-0.5">Email</span>
                        <a href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>" class="text-white hover:text-[#ff6b00] break-all"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
                    </div>
                    <div>
                        <span class="text-white/50 text-xs uppercase tracking-wider block mb-0.5">Address</span>
                        <span class="text-white/90">17 Woodlands Park Road<br>Offerton, Stockport SK2 5DE</span>
                    </div>
                </div>
                <div class="text-white/50 text-xs uppercase tracking-wider mb-2">Follow us</div>
                <?= socialIconsHtml('dark') ?>
            </div>

            <!-- All services -->
            <div>
                <div class="font-semibold text-white mb-4 text-base">Services</div>
                <div class="space-y-2 text-white/75">
                    <a href="<?= url('/pages/services/index.php') ?>" class="block text-[#ff6b00] hover:text-white font-medium">All services →</a>
                    <?php foreach ($services as $slug => $name): ?>
                        <a href="<?= url('/pages/services/' . rawurlencode($slug) . '.php') ?>" class="block hover:text-white"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Areas -->
            <div>
                <div class="font-semibold text-white mb-4 text-base">Areas we cover</div>
                <div class="space-y-2 text-white/75">
                    <a href="<?= url('/pages/areas/index.php') ?>" class="block text-[#ff6b00] hover:text-white font-medium">All <?= count($areas) ?> towns →</a>
                    <?php foreach ($popularAreas as $area): ?>
                        <a href="<?= url('/pages/areas/' . areaSlug($area) . '.php') ?>" class="block hover:text-white"><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Manufacturers -->
            <div>
                <div class="font-semibold text-white mb-4 text-base">Brands</div>
                <div class="space-y-2 text-white/75">
                    <a href="<?= url('/pages/manufacturers/index.php') ?>" class="block text-[#ff6b00] hover:text-white font-medium">All manufacturers →</a>
                    <?php foreach ($footerMfr as $mSlug => $mEntry): ?>
                        <a href="<?= url('/pages/manufacturers/' . rawurlencode($mSlug) . '.php') ?>" class="block hover:text-white"><?= htmlspecialchars($mEntry['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Explore + CTA -->
            <div>
                <div class="font-semibold text-white mb-4 text-base">Explore</div>
                <div class="space-y-2 text-white/75 mb-8">
                    <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="block hover:text-white">Home</a>
                    <a href="<?= url('/pages/about.php') ?>" class="block hover:text-white">About</a>
                    <a href="<?= url('/shop/index.php') ?>" class="block hover:text-white text-[#ff6b00] font-medium">Shop</a>
                    <a href="<?= url('/pages/services/index.php') ?>" class="block hover:text-white">Services</a>
                    <a href="<?= url('/pages/areas/index.php') ?>" class="block hover:text-white">Areas</a>
                    <a href="<?= url('/pages/manufacturers/index.php') ?>" class="block hover:text-white">Manufacturers</a>
                    <a href="<?= url('/pages/packages.php') ?>" class="block hover:text-white">Packages</a>
                    <a href="<?= url('/pages/pricing.php') ?>" class="block hover:text-white">Pricing guide</a>
                    <a href="<?= url('/pages/landlords.php') ?>" class="block hover:text-white">Landlords</a>
                    <a href="<?= url('/pages/commercial.php') ?>" class="block hover:text-white">Commercial / FM</a>
                    <a href="<?= url('/pages/care-homes.php') ?>" class="block hover:text-white">Care homes</a>
                    <a href="<?= url('/pages/ev-chargers.php') ?>" class="block hover:text-white">EV chargers</a>
                    <a href="<?= url('/pages/maintenance.php') ?>" class="block hover:text-white">Maintenance</a>
                    <a href="<?= url('/pages/emergency.php') ?>" class="block hover:text-white">Emergency call-out</a>
                    <a href="<?= url('/pages/resources/index.php') ?>" class="block hover:text-white">Resources</a>
                    <a href="<?= url('/pages/keywords/index.php') ?>" class="block hover:text-white">Keyword guides</a>
                    <a href="<?= url('/pages/faq.php') ?>" class="block hover:text-white">FAQ</a>
                    <a href="<?= url('/pages/reviews.php') ?>" class="block hover:text-white">Reviews</a>
                    <a href="<?= url('/contact.php') ?>" class="block hover:text-white">Contact / free quote</a>
                    <a href="<?= url('/privacy.php') ?>" class="block hover:text-white">Privacy policy</a>
                    <a href="<?= url('/terms.php') ?>" class="block hover:text-white">Terms &amp; conditions</a>
                    <a href="<?= url('/pages/site-map.php') ?>" class="block hover:text-white">Site map</a>
                    <a href="<?= url('/sitemap.xml') ?>" class="block hover:text-white">XML sitemap</a>
                </div>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20need%20a%20quote"
                   target="_blank" rel="noopener"
                   class="block bg-green-600 hover:bg-green-500 text-center py-3.5 rounded-2xl font-semibold text-white mb-3">
                    Chat on WhatsApp
                </a>
                <a href="<?= url('/contact.php') ?>"
                   class="block border border-white/30 hover:bg-white/10 text-center py-3.5 rounded-2xl font-medium text-white">
                    Request a quote
                </a>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-white/10 flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-xs text-white/45">
            <div>© <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</div>
            <div class="flex flex-wrap gap-4">
                <a href="<?= url('/shop/index.php') ?>" class="hover:text-white">Shop</a>
                <a href="<?= url('/pages/reviews.php') ?>" class="hover:text-white">Reviews</a>
                <a href="<?= url('/pages/pricing.php') ?>" class="hover:text-white">Pricing</a>
                <a href="<?= url('/pages/emergency.php') ?>" class="hover:text-white">Emergency</a>
                <a href="<?= url('/contact.php') ?>" class="hover:text-white">Contact</a>
                <a href="<?= url('/privacy.php') ?>" class="hover:text-white">Privacy</a>
                <a href="<?= url('/terms.php') ?>" class="hover:text-white">Terms</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
                <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white" target="_blank" rel="noopener">WhatsApp</a>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp floating button -->
<a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Hi%20Icomply%2C%20I%20need%20a%20quote%20for%20compliance%20services"
   target="_blank" rel="noopener" aria-label="WhatsApp"
   class="fixed bottom-6 right-6 bg-green-600 hover:bg-green-500 text-white w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-xl z-50 transition transform hover:scale-105">
    💬
</a>
<?php require_once __DIR__ . '/cookie-banner.php'; ?>
</body>
</html>
