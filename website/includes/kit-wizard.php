<?php
/**
 * Shared kit-builder page renderer (navy #0B1F3A / orange #FF6B00).
 */
declare(strict_types=1);

require_once __DIR__ . '/kit-wizard-catalog.php';

if (!function_exists('kitWizardPage')) {

function kitWizardBySlug(string $slug): ?array
{
    $catalog = kitWizardCatalog();
    return $catalog[$slug] ?? null;
}

function kitWizardPage(string $slug): void
{
    $wizard = kitWizardBySlug($slug);
    if ($wizard === null) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }

    $pageTitle = $wizard['title'] . ' | Trade kit builders';
    $metaDesc = $wizard['blurb'];
    $metaKeywords = $wizard['title'] . ', kit builder, ' . ($wizard['kicker'] ?? '') . ', Icomply trade shop';
    $canonicalUrl = url('/pages/kits/' . $wizard['slug']);
    $hero = (string)($wizard['hero_image'] ?? '/assets/images/services/electrical.jpg');
    $ogImage = str_starts_with($hero, 'http') ? $hero : url($hero);
    $extraStylesheets = ['/assets/css/kit-wizard.css'];

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    require SITE_ROOT . '/includes/header.php';
    kitWizardRender($wizard);
    echo '<script src="' . htmlspecialchars(assetUrl('/assets/js/kit-wizard.js'), ENT_QUOTES, 'UTF-8') . '" defer></script>';
    require SITE_ROOT . '/includes/footer.php';
}

function kitWizardRender(array $wizard): void
{
    $payload = $wizard;
    $payload['shop_host'] = function_exists('icomplyTradeShopUrl')
        ? icomplyTradeShopUrl()
        : 'https://shop.icomplypropertyservices.co.uk';
    $json = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    $wa = defined('WHATSAPP') ? WHATSAPP : '447517806082';
    $contact = htmlspecialchars(url('/contact'), ENT_QUOTES, 'UTF-8');
    $hero = htmlspecialchars((string)($wizard['hero_image'] ?? ''), ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars((string)$wizard['title'], ENT_QUOTES, 'UTF-8');
    $kicker = htmlspecialchars((string)($wizard['kicker'] ?? 'Kit builder'), ENT_QUOTES, 'UTF-8');
    $blurb = htmlspecialchars((string)$wizard['blurb'], ENT_QUOTES, 'UTF-8');
    $hub = htmlspecialchars(url('/pages/kits'), ENT_QUOTES, 'UTF-8');
    $svc = htmlspecialchars((string)($wizard['service'] ?? 'electrical'), ENT_QUOTES, 'UTF-8');
    ?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white kit-wrap">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-18">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= $hub ?>" class="hover:text-white">Kit builders</a>
            <span>/</span>
            <span class="text-white/80"><?= $title ?></span>
        </nav>
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#FF6B00]"></span>
                    <?= $kicker ?>
                </div>
                <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]"><?= $title ?></h1>
                <p class="mt-5 text-lg text-white/80 max-w-xl"><?= $blurb ?></p>
                <p class="mt-4 text-sm text-white/60"><?= htmlspecialchars((string)($wizard['brand_policy'] ?? 'Branded manufacturers only. Screwfix is a price reference for the same branded SKU — never Screwfix own-brand, LAP, Time/SFX, SFX accessories, BG boards or unbranded sockets. No invented £.'), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php if ($hero !== ''): ?>
            <img src="<?= $hero ?>" alt="<?= $title ?>" class="w-full h-64 object-cover rounded-3xl border border-white/10" width="800" height="256">
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="kit-wrap kit-stage">
    <div class="max-w-7xl mx-auto px-6 py-10">
        <div id="kit-wizard" data-kit-wizard data-wizard="<?= $json ?>" data-wa="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" data-contact="<?= $contact ?>">
            <div data-kit-progress class="kit-progress kit-progress--stage" aria-label="Kit steps"></div>
            <div data-kit-panel class="kit-panel mt-6"></div>
        </div>
        <p class="mt-6 text-sm text-[#5B6472]">
            Related:
            <a class="text-[#FF6B00] font-semibold" href="<?= htmlspecialchars(url('/pages/services/' . $svc), ENT_QUOTES, 'UTF-8') ?>">service hub</a>
            · <a class="text-[#FF6B00] font-semibold" href="<?= $hub ?>">all kit builders</a>
            · <a class="text-[#FF6B00] font-semibold" href="/shop/">trade shop</a>
        </p>
    </div>
</section>
    <?php
}

/**
 * Built-in wizards for service and town pages.
 * Barriers (CAME partner) and AOV only — never nurse-call brands such as Tunstall.
 *
 * @return list<string>
 */
function kitWizardSlugsForService(string $serviceSlug): array
{
    return match ($serviceSlug) {
        'access-control' => ['barriers'],
        'aov-air-handling' => ['aov'],
        default => [],
    };
}

/** Town hubs lead with the same two builders. @return list<string> */
function kitWizardSlugsForTown(): array
{
    return ['barriers', 'aov'];
}

function kitWizardPrintAssets(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $css = htmlspecialchars(assetUrl('/assets/css/kit-wizard.css'), ENT_QUOTES, 'UTF-8');
    $js = htmlspecialchars(assetUrl('/assets/js/kit-wizard.js'), ENT_QUOTES, 'UTF-8');
    echo '<link rel="stylesheet" href="' . $css . '">' . "\n";
    echo '<script src="' . $js . '" defer></script>' . "\n";
}

/** Drop a wizard into a service hub, service×town page, or town hub. */
function kitWizardEmbed(string $slug, string $area = ''): void
{
    $wizard = kitWizardBySlug($slug);
    if ($wizard === null) {
        return;
    }
    kitWizardPrintAssets();
    $payload = $wizard;
    $payload['shop_host'] = function_exists('icomplyTradeShopUrl')
        ? icomplyTradeShopUrl()
        : 'https://shop.icomplypropertyservices.co.uk';
    if ($area !== '') {
        $payload['area'] = $area;
    }
    $json = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    $wa = defined('WHATSAPP') ? WHATSAPP : '447517806082';
    $contact = htmlspecialchars(url('/contact'), ENT_QUOTES, 'UTF-8');
    $id = 'kit-wizard-' . preg_replace('/[^a-z0-9\-]+/', '-', strtolower($slug));
    $title = htmlspecialchars((string)$wizard['title'], ENT_QUOTES, 'UTF-8');
    $kicker = htmlspecialchars((string)($wizard['kicker'] ?? 'Kit builder'), ENT_QUOTES, 'UTF-8');
    $blurb = htmlspecialchars((string)$wizard['blurb'], ENT_QUOTES, 'UTF-8');
    $full = htmlspecialchars(url('/pages/kits/' . $wizard['slug']), ENT_QUOTES, 'UTF-8');
    $areaAttr = $area !== '' ? ' data-area="' . htmlspecialchars($area, ENT_QUOTES, 'UTF-8') . '"' : '';
    $where = $area !== '' ? ' in ' . htmlspecialchars($area, ENT_QUOTES, 'UTF-8') : '';
    ?>
<section class="kit-wrap kit-stage border-y" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>-section">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <p class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold"><?= $kicker ?></p>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-[#0B1F3A] mt-2"><?= $title ?><?= $where ?></h2>
        <p class="mt-3 text-[#5B6472] max-w-3xl"><?= $blurb ?></p>
        <div class="mt-6" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-kit-wizard data-wizard="<?= $json ?>" data-wa="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" data-contact="<?= $contact ?>"<?= $areaAttr ?>>
            <div data-kit-progress class="kit-progress kit-progress--stage" aria-label="Kit steps"></div>
            <div data-kit-panel class="kit-panel mt-6"></div>
        </div>
        <p class="mt-4 text-sm text-[#5B6472]"><a class="text-[#FF6B00] font-semibold" href="<?= $full ?>">Open the full kit page</a></p>
    </div>
</section>
    <?php
}

/** @param list<string> $slugs */
function kitWizardEmbedList(array $slugs, string $area = ''): void
{
    foreach ($slugs as $slug) {
        kitWizardEmbed($slug, $area);
    }
}
}
