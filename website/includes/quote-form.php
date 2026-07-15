<?php
/**
 * Shared quote form partial.
 * Expects: $services (array), optional $selectedService (name string), $formAction, $heading, $sub
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$services = $services ?? getServices();
$formAction = $formAction ?? url('/contact.php');
$selectedService = $selectedService ?? '';
$heading = $heading ?? 'Request your free quote';
$sub = $sub ?? 'We aim to respond within 2 hours on business days.';
$showHeading = $showHeading ?? true;
?>
<?php if ($showHeading): ?>
<div class="text-center mb-10">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="mt-3 text-zinc-600"><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<?php endif; ?>
<form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
        <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
        <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
            <option value="">Select service…</option>
            <?php foreach ($services as $slug => $name): ?>
                <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"<?= $selectedService === $name ? ' selected' : '' ?>>
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
            <option value="Multi-service package"<?= $selectedService === 'Multi-service package' ? ' selected' : '' ?>>Multi-service package</option>
            <option value="Shop / products"<?= $selectedService === 'Shop / products' ? ' selected' : '' ?>>Shop / products</option>
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
