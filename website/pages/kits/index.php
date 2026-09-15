<?php
/**
 * Hub — all trade kit builders.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/kit-wizard-catalog.php';

$pageTitle = 'Trade kit builders | Rewire, fire, AOV, gates & more';
$metaDesc = 'Build branded trade kits for rewire, heating, fire alarms, emergency lighting, AOV, intercom, access control, CAME gates and barriers. Live Shopify SKUs or enquire / POA. No invented prices.';
$metaKeywords = 'kit builder, rewire kit, fire alarm kit, AOV kit, CAME gates, Wylex, Click Scolmore, Apollo, Ventlux';
$canonicalUrl = url('/pages/kits');
$ogImage = url('/assets/images/services/electrical.jpg');
$extraStylesheets = ['/assets/css/kit-wizard.css'];

require SITE_ROOT . '/includes/header.php';
$wizards = kitWizardCatalog();
?>
<section class="page-hero bg-[#0B1F3A] text-white kit-wrap">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span> / </span>
            <span class="text-white/80">Kit builders</span>
        </nav>
        <p class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Icomply trade kits</p>
        <h1 class="text-4xl md:text-5xl font-semibold tracking-tighter mt-3">Kit builders</h1>
        <p class="mt-4 text-lg text-white/80 max-w-2xl">
            Step through a job, pick branded manufacturers, then enquire / POA or open a live Shopify SKU.
            Screwfix is a price reference for the same branded SKU only — we never sell Screwfix own-brand, LAP, Time/SFX or BG boards.
        </p>
    </div>
</section>
<section class="max-w-7xl mx-auto px-6 py-14 kit-wrap">
    <div class="kit-hub-grid">
        <?php foreach ($wizards as $w):
            $href = htmlspecialchars(url('/pages/kits/' . $w['slug']), ENT_QUOTES, 'UTF-8');
            $img = htmlspecialchars((string)($w['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8');
            ?>
            <a class="kit-hub-card" href="<?= $href ?>">
                <img src="<?= $img ?>" alt="<?= htmlspecialchars($w['title'], ENT_QUOTES, 'UTF-8') ?>" width="640" height="170">
                <div class="kit-hub-body">
                    <p class="text-xs uppercase tracking-[2px] text-[#FF6B00] font-semibold"><?= htmlspecialchars((string)($w['kicker'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <h2><?= htmlspecialchars($w['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="mt-2 text-sm"><?= htmlspecialchars($w['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="inline-block mt-3 text-sm font-semibold text-[#FF6B00]">Open wizard →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
