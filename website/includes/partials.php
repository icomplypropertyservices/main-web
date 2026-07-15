<?php
/**
 * Reusable landing-page section partials (navy #0a2540 / orange #ff6b00).
 *
 * Usage:
 *   require_once SITE_ROOT . '/includes/partials.php';
 *   echo sectionTrustStrip($items);
 *   echo sectionQuoteForm($services, $_SESSION['csrf'], $defaultService);
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Trust strip: white bar with check icons.
 *
 * Each item may be:
 *   ['title' => string, 'text' => string]
 *   or [0 => title, 1 => text] / ['title', 'text']
 *
 * @param list<array{title?:string,text?:string}|array{0?:string,1?:string}> $items
 * @return string HTML
 */
function sectionTrustStrip(array $items): string
{
    if ($items === []) {
        return '';
    }

    ob_start();
    ?>
<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($items as $item):
            if (isset($item['title']) || isset($item['text'])) {
                $title = (string) ($item['title'] ?? '');
                $text = (string) ($item['text'] ?? '');
            } else {
                $title = (string) ($item[0] ?? '');
                $text = (string) ($item[1] ?? '');
            }
            if ($title === '' && $text === '') {
                continue;
            }
            ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0a2540]/10 flex items-center justify-center text-[#0a2540] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
    <?php
    return (string) ob_get_clean();
}

/**
 * Free quote section (#quote) with CSRF and service select.
 *
 * $services: typically slug => display name (from getServices()).
 * Values posted are the display names. Also offers Multi-service package
 * and Shop / products options.
 *
 * @param array<string|int, string> $services
 * @param string $csrf CSRF token
 * @param string $defaultService Selected service name (or slug matched against keys)
 * @return string HTML
 */
function sectionQuoteForm(array $services, string $csrf, string $defaultService = ''): string
{
    $formAction = function_exists('url') ? url('/contact.php') : '/contact.php';
    $privacyUrl = function_exists('url') ? url('/privacy.php') : '/privacy.php';
    $termsUrl = function_exists('url') ? url('/terms.php') : '/terms.php';

    // Resolve default: exact name match, or slug key match
    $selected = $defaultService;
    if ($selected !== '' && !in_array($selected, $services, true) && isset($services[$selected])) {
        $selected = (string) $services[$selected];
    }

    $gclid = isset($_GET['gclid']) ? (string) $_GET['gclid'] : '';
    $fbclid = isset($_GET['fbclid']) ? (string) $_GET['fbclid'] : '';

    ob_start();
    ?>
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request your free quote</h2>
            <p class="mt-3 text-zinc-600">We aim to respond within 2 hours on business days. All quotes are fixed-price after scope is agreed.</p>
        </div>

        <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm" aria-label="Free quote form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($gclid, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($fbclid, ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quote-name" class="sr-only">Full name</label>
                    <input id="quote-name" type="text" name="name" placeholder="Full name" required aria-required="true" maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="name">
                </div>
                <div>
                    <label for="quote-email" class="sr-only">Email</label>
                    <input id="quote-email" type="email" name="email" placeholder="Email" required aria-required="true" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="email">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quote-phone" class="sr-only">Phone</label>
                    <input id="quote-phone" type="tel" name="phone" placeholder="Phone" required aria-required="true" maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="tel">
                </div>
                <div>
                    <label for="quote-service" class="sr-only">Service</label>
                    <select id="quote-service" name="service" required aria-required="true" class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                        <option value="">Select service…</option>
                        <?php foreach ($services as $slug => $name):
                            $name = (string) $name;
                            $isSelected = ($selected !== '' && ($selected === $name || (string) $slug === $selected));
                            ?>
                            <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $isSelected ? ' selected' : '' ?>>
                                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Multi-service package"<?= $selected === 'Multi-service package' ? ' selected' : '' ?>>Multi-service package</option>
                        <option value="Shop / products"<?= $selected === 'Shop / products' ? ' selected' : '' ?>>Shop / products</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="quote-message" class="sr-only">Message</label>
                <textarea id="quote-message" name="message" rows="4" required aria-required="true" maxlength="5000" placeholder="Postcode, property type, panel brand / system details…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            </div>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= htmlspecialchars($privacyUrl, ENT_QUOTES, 'UTF-8') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= htmlspecialchars($termsUrl, ENT_QUOTES, 'UTF-8') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>
    <?php
    return (string) ob_get_clean();
}
