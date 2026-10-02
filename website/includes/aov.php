<?php
/**
 * AOV / smoke-vent lane.
 *
 * Automatic opening vents are life-safety smoke control, so they sit inside
 * fire protection and are quoted nationwide. Place pages live at /pages/aov/{slug}
 * for published populations of 10,000 or more. Manchester and Burnley carry extra local copy.
 */
declare(strict_types=1);

function aovServiceSlug(): string
{
    return 'aov-air-handling';
}

/** @return array<string,string> slug => display name */
function aovLocalLandingTowns(): array
{
    return [
        'manchester' => 'Manchester',
        'burnley' => 'Burnley',
    ];
}

function aovIsLocalLandingTown(string $areaOrSlug): bool
{
    $slug = function_exists('areaSlug') ? areaSlug($areaOrSlug) : strtolower($areaOrSlug);
    return isset(aovLocalLandingTowns()[$slug]);
}

function aovHubPath(): string
{
    return '/pages/services/aov-air-handling';
}

function aovTownPath(string $townSlug): string
{
    return '/pages/aov-air-handling/' . areaSlug($townSlug);
}

function aovPhoneHref(): string
{
    return 'tel:' . preg_replace('/\s+/', '', (string)PHONE);
}

function aovWhatsappUrl(string $text): string
{
    return 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($text);
}

/**
 * @return array{heading:string,paragraphs:list<string>}|null
 */
function aovKeywordNote(string $slug): ?array
{
    static $notes = null;
    if ($notes === null) {
        $file = __DIR__ . '/aov-notes.php';
        $notes = is_file($file) ? require $file : [];
    }
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : $slug;
    $row = $notes[$slug] ?? null;
    return is_array($row) ? $row : null;
}

function aovRenderHub(): void
{
    require_once SITE_ROOT . '/includes/aov-brands.php';
    require_once SITE_ROOT . '/includes/aov-place.php';
    require SITE_ROOT . '/templates/aov/hub.php';
}

function aovRenderTown(string $townSlug): void
{
    require_once SITE_ROOT . '/includes/aov-place.php';
    aovRenderPlace($townSlug);
}

function aovRedirectOtherTown(string $area): void
{
    header('Location: ' . url(aovHubPath()), true, 301);
    if (function_exists('icomplyRequestExit')) {
        icomplyRequestExit();
    }
}

/**
 * Quote form. Heading and intro are passed in so each page's ask is different.
 */
function aovQuoteForm(string $heading, string $intro, string $serviceValue, string $placeholder): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    ?>
    <section id="quote" class="bg-[#061828] text-white">
        <div class="max-w-3xl mx-auto px-6 py-16">
            <p class="text-xs uppercase tracking-[3px] text-[#ffb080] font-semibold text-center">Call <?= $h((string)PHONE) ?> or send the form</p>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-center mt-3"><?= $h($heading) ?></h2>
            <p class="mt-3 text-center text-white/80 max-w-xl mx-auto"><?= $h($intro) ?></p>
            <div class="mt-6 flex flex-wrap gap-3 justify-center">
                <a href="<?= $h(aovPhoneHref()) ?>" class="px-6 py-3 rounded-2xl bg-white text-[#061828] font-semibold">Call <?= $h((string)PHONE) ?></a>
                <a href="<?= $h(aovWhatsappUrl($serviceValue . ' quote')) ?>" class="px-6 py-3 rounded-2xl bg-green-600 font-semibold" target="_blank" rel="noopener">WhatsApp</a>
            </div>
            <form action="<?= $h(url('/contact.php')) ?>" method="POST" class="mt-8 bg-white text-zinc-900 rounded-3xl p-6 md:p-8 space-y-4">
                <input type="hidden" name="csrf" value="<?= $h((string)$_SESSION['csrf']) ?>">
                <div class="grid md:grid-cols-2 gap-4">
                    <input type="text" name="name" placeholder="Full name" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                    <input type="email" name="email" placeholder="Email" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <input type="tel" name="phone" placeholder="Phone" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                    <input type="text" name="service" value="<?= $h($serviceValue) ?>" readonly class="w-full border border-zinc-300 px-4 py-3 rounded-xl bg-zinc-50">
                </div>
                <textarea name="message" rows="5" required placeholder="<?= $h($placeholder) ?>" class="w-full border border-zinc-300 px-4 py-3 rounded-xl"></textarea>
                <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] text-white font-semibold">Request a written quote</button>
                <p class="text-xs text-zinc-500">Price on application after scope. No catalogue fee and no invented accreditation on this form.</p>
            </form>
        </div>
    </section>
    <?php
}
