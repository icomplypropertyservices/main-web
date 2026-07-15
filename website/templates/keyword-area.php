<?php
/**
 * Keyword × area — pure PHP vars via executeTemplateVars() (no {{PLACEHOLDER}} / eval).
 * Expected vars from extract($placeholders): KEYWORD_*, SERVICE_*, AREA*, RELATED_*,
 * MANUFACTURER_TAGS, KEYWORD_INTRO, KEYWORD_BODY, KEYWORD_META, KEYWORD_FOCUS_HTML,
 * KEYWORD_IMAGE, SERVICE_IMAGE, SEO_KEYWORDS
 */
$pageTitle = $KEYWORD_NAME . ' in ' . $AREA;
$metaDesc = $KEYWORD_NAME . ' in ' . $AREA . '. ' . $KEYWORD_META;
$metaKeywords = $KEYWORD_NAME . ' ' . $AREA . ', ' . $SERVICE_NAME . ' ' . $AREA . ', ' . $SEO_KEYWORDS;
$ogImage = $KEYWORD_IMAGE;
$canonicalUrl = url('/pages/keywords/' . $KEYWORD_SLUG . '/' . $AREA_SLUG . '.php');

$keywordName = $KEYWORD_NAME;
$keywordSlug = $KEYWORD_SLUG;
$serviceName = $SERVICE_NAME;
$serviceSlug = $SERVICE_SLUG;
$areaName = $AREA;
$areaSlugVal = $AREA_SLUG;
$allServices = getServices();
$allAreas = getAreas();

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
require SITE_ROOT . '/includes/header.php';

$h = static function ($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $keywordName . ' in ' . $areaName,
    'description' => $metaDesc,
    'provider' => ['@type' => 'LocalBusiness', 'name' => SITE_NAME, 'telephone' => PHONE, 'url' => SITE_URL],
    'areaServed' => ['@type' => 'City', 'name' => $areaName],
    'url' => $canonicalUrl,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="relative overflow-hidden bg-[#061828] text-white">
    <div class="absolute inset-0">
        <img src="<?= $h($KEYWORD_IMAGE) ?>" alt="" class="w-full h-full object-cover opacity-30" loading="eager"
             onerror="this.src='<?= $h($SERVICE_IMAGE) ?>'">
        <div class="absolute inset-0 bg-gradient-to-r from-[#061828] via-[#061828]/95 to-[#061828]/80"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/70 mb-4 flex flex-wrap gap-2">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a><span>/</span>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="hover:text-white">Guides</a><span>/</span>
            <a href="<?= url('/pages/keywords/' . rawurlencode($KEYWORD_SLUG) . '.php') ?>" class="hover:text-white"><?= $h($KEYWORD_NAME) ?></a><span>/</span>
            <span class="text-white font-semibold"><?= $h($AREA) ?></span>
        </nav>
        <div class="inline-block px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold uppercase tracking-wider mb-4">
            <?= $h($SERVICE_NAME) ?> · <?= $h($AREA) ?>
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white leading-[1.08] drop-shadow-lg">
            <?= $h($KEYWORD_NAME) ?><br><span class="text-[#ff6b00]">in <?= $h($AREA) ?></span>
        </h1>
        <p class="mt-5 text-lg text-white font-medium max-w-2xl leading-relaxed drop-shadow">
            Local engineers for <strong><?= $h($KEYWORD_NAME) ?></strong> in <strong><?= $h($AREA) ?></strong> and nearby postcodes.
            Fixed-price quotes · Stockport-based team covering the North West.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white shadow-lg">Get free quote</a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($keywordName . ' in ' . $areaName) ?>"
               target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 font-bold text-white shadow-lg">WhatsApp</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold shadow-lg"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-12 md:py-16 grid lg:grid-cols-5 gap-10">
        <div class="lg:col-span-3">
            <div class="bg-white border-2 border-zinc-300 rounded-3xl p-6 md:p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-[#061828]"><?= $h($KEYWORD_NAME) ?> for properties in <?= $h($AREA) ?></h2>
                <p class="mt-4 text-lg text-zinc-900 leading-relaxed font-medium"><?= $h($KEYWORD_INTRO) ?></p>
                <p class="mt-4 text-lg text-zinc-900 leading-relaxed">
                    Serving <strong class="text-[#061828]"><?= $h($AREA) ?></strong>: <?= $h($KEYWORD_BODY) ?>
                </p>
                <p class="mt-4 text-base text-zinc-900 leading-relaxed">
                    Searching for <strong><?= $h($KEYWORD_NAME) ?> near <?= $h($AREA) ?></strong>? Book Icomply for install, service, testing or certification.
                    Also see
                    <a class="font-bold text-[#ff6b00] hover:underline" href="<?= url('/pages/keywords/' . rawurlencode($RELATED_SLUG) . '/' . rawurlencode($AREA_SLUG) . '.php') ?>"><?= $h($RELATED_NAME) ?> in <?= $h($AREA) ?></a>
                    and
                    <a class="font-bold text-[#ff6b00] hover:underline" href="<?= url('/pages/' . rawurlencode($SERVICE_SLUG) . '/' . rawurlencode($AREA_SLUG) . '.php') ?>"><?= $h($SERVICE_NAME) ?> in <?= $h($AREA) ?></a>.
                </p>
                <ul class="mt-6 space-y-3"><?= $KEYWORD_FOCUS_HTML ?></ul>
            </div>
            <div class="mt-6 grid sm:grid-cols-3 gap-4">
                <?php foreach (['Install' => 'New works in ' . $AREA, 'Service' => 'Repairs & maintenance', 'Certify' => 'Compliance paperwork'] as $t => $d): ?>
                <div class="p-5 bg-white border-2 border-zinc-300 rounded-2xl shadow-sm">
                    <div class="font-bold text-[#061828] text-lg"><?= $h($t) ?></div>
                    <p class="text-sm text-zinc-800 mt-1 font-medium"><?= $h($d) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-3xl overflow-hidden border-2 border-zinc-400 shadow-md">
                <img src="<?= $h($KEYWORD_IMAGE) ?>" alt="<?= $h($KEYWORD_NAME . ' in ' . $AREA) ?>" class="w-full h-48 object-cover" loading="eager"
                     onerror="this.src='<?= $h($SERVICE_IMAGE) ?>'">
                <div class="bg-[#061828] text-white p-3 text-center font-bold text-sm"><?= $h($KEYWORD_NAME) ?> · <?= $h($AREA) ?></div>
            </div>
            <div class="bg-[#061828] text-white rounded-3xl p-6 shadow-lg">
                <h3 class="text-xl font-bold">Quote — <?= $h($AREA) ?></h3>
                <p class="mt-2 text-white/95 text-sm font-medium">Same-week visits where capacity allows. Include postcode &amp; brand.</p>
                <a href="#quote" class="inline-block mt-4 px-5 py-3 bg-[#ff6b00] rounded-xl font-bold">Request quote</a>
                <a href="<?= url('/pages/areas/' . rawurlencode($AREA_SLUG) . '.php') ?>" class="block mt-3 text-sm font-semibold text-[#ff6b00] hover:underline">All services in <?= $h($AREA) ?> →</a>
            </div>
            <div class="bg-white border-2 border-zinc-300 rounded-3xl p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-zinc-600 mb-2">Brands</div>
                <div class="flex flex-wrap gap-2"><?= $MANUFACTURER_TAGS ?></div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-xl font-bold text-[#061828]"><?= $h($KEYWORD_NAME) ?> nearby</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php
            $towns = array_values(array_filter(
                ['Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Liverpool', 'Preston', 'Warrington', 'Chester', 'Blackpool'],
                fn($t) => in_array($t, $allAreas, true) && $t !== $areaName
            ));
            foreach ($towns as $t):
            ?>
            <a href="<?= url('/pages/keywords/' . rawurlencode($KEYWORD_SLUG) . '/' . areaSlug($t) . '.php') ?>"
               class="px-4 py-2 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00]">
                in <?= $h($t) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-6"><?= shareButtonsHtml($keywordName . ' in ' . $areaName, $metaDesc) ?></section>

<section id="quote" class="bg-zinc-100 border-t-2 border-zinc-300">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-[#061828] text-center"><?= $h($KEYWORD_NAME) ?> in <?= $h($AREA) ?></h2>
        <form action="<?= url('/contact.php') ?>" method="POST" class="mt-8 bg-white border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4 shadow-md">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium text-zinc-900">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium text-zinc-900">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium text-zinc-900">
                <select name="service" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl bg-white font-medium text-zinc-900">
                    <option value="<?= $h($keywordName . ' in ' . $areaName) ?>" selected><?= $h($keywordName . ' in ' . $areaName) ?></option>
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= $h($name) ?>"><?= $h($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required placeholder="<?= $h($AREA . ' postcode, property type, brand…') ?>" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium text-zinc-900"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
