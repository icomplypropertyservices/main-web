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
<form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST" class="js-quote-form bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm" novalidate>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="js-form-errors hidden rounded-2xl border border-red-200 bg-red-50 text-red-800 text-sm px-4 py-3" role="alert"></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="sr-only" for="quote-name">Full name</label>
            <input id="quote-name" type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="name">
        </div>
        <div>
            <label class="sr-only" for="quote-email">Email</label>
            <input id="quote-email" type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="email">
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="sr-only" for="quote-phone">Phone</label>
            <input id="quote-phone" type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl" autocomplete="tel">
        </div>
        <div>
            <label class="sr-only" for="quote-service">Service</label>
            <select id="quote-service" name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
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
    </div>
    <div>
        <label class="sr-only" for="quote-message">Message</label>
        <textarea id="quote-message" name="message" rows="4" required maxlength="5000" placeholder="Postcode, property type, panel brand / system details…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
    </div>
    <button type="submit" class="js-quote-submit w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">
        <span class="js-quote-submit-label">Submit request</span>
        <span class="js-quote-submit-loading hidden">Sending…</span>
    </button>
    <p class="text-center text-xs text-zinc-500">
        By submitting you agree to our
        <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
        and
        <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
    </p>
</form>
<script>
(function () {
    var form = document.currentScript && document.currentScript.previousElementSibling;
    if (!form || !form.classList.contains('js-quote-form')) {
        form = document.querySelector('.js-quote-form');
    }
    if (!form) return;
    var box = form.querySelector('.js-form-errors');
    var submit = form.querySelector('.js-quote-submit');
    var label = form.querySelector('.js-quote-submit-label');
    var loading = form.querySelector('.js-quote-submit-loading');
    function showErrors(msgs) {
        if (!box) return;
        box.innerHTML = msgs.map(function (m) { return '<p>' + m + '</p>'; }).join('');
        box.classList.remove('hidden');
        box.focus && box.setAttribute('tabindex', '-1');
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function clearErrors() {
        if (!box) return;
        box.classList.add('hidden');
        box.innerHTML = '';
        form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
    }
    form.addEventListener('submit', function (e) {
        clearErrors();
        var msgs = [];
        var name = form.querySelector('[name="name"]');
        var email = form.querySelector('[name="email"]');
        var phone = form.querySelector('[name="phone"]');
        var service = form.querySelector('[name="service"]');
        var message = form.querySelector('[name="message"]');
        if (name && !name.value.trim()) { msgs.push('Please enter your name.'); name.setAttribute('aria-invalid', 'true'); }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) { msgs.push('Please enter a valid email.'); email.setAttribute('aria-invalid', 'true'); }
        if (phone && !phone.value.trim()) { msgs.push('Please enter a phone number.'); phone.setAttribute('aria-invalid', 'true'); }
        if (service && !service.value) { msgs.push('Please select a service.'); service.setAttribute('aria-invalid', 'true'); }
        if (message && !message.value.trim()) { msgs.push('Please enter a short message.'); message.setAttribute('aria-invalid', 'true'); }
        if (msgs.length) {
            e.preventDefault();
            showErrors(msgs);
            return;
        }
        if (submit) submit.disabled = true;
        if (label) label.classList.add('hidden');
        if (loading) loading.classList.remove('hidden');
    });
})();
</script>
