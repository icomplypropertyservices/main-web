<?php
/**
 * Session lead popup + static Netlify Form (email notification).
 * Hidden fields must exist in published HTML so Netlify registers the form.
 */
if (!defined('SITE_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}
$lpPhone = defined('PHONE') ? PHONE : '07517806082';
$lpPhoneHref = 'tel:' . preg_replace('/\s+/', '', $lpPhone);
$lpWa = defined('WHATSAPP') ? WHATSAPP : '447517806082';
$lpServices = function_exists('getServices') ? getServices() : [];
?>
<!-- Netlify Forms registration (must be in static HTML at deploy). -->
<form name="lead-popup" method="POST" action="/thank-you" data-netlify="true" netlify-honeypot="bot-field" hidden>
    <input type="hidden" name="form-name" value="lead-popup">
    <input name="bot-field">
    <input name="name">
    <input name="email">
    <input name="phone">
    <input name="service">
    <textarea name="message"></textarea>
    <input name="source" value="lead-popup">
</form>

<div id="lead-popup" class="lead-popup" hidden>
    <div class="lead-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="lead-popup-title" tabindex="-1">
        <button type="button" class="lead-close" data-lead-close aria-label="Close quote popup">&times;</button>
        <h2 id="lead-popup-title">Need a compliance quote?</h2>
        <p>Tell us the property and service. Price on application — no invented fees. Or call / WhatsApp now.</p>
        <div class="lead-quick">
            <a class="lead-call" href="<?= htmlspecialchars($lpPhoneHref, ENT_QUOTES, 'UTF-8') ?>">Call <?= htmlspecialchars($lpPhone, ENT_QUOTES, 'UTF-8') ?></a>
            <a class="lead-wa" href="https://wa.me/<?= htmlspecialchars($lpWa, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi iComply, I need a quote') ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a class="lead-book" href="<?= htmlspecialchars(url('/get-a-quote.php'), ENT_QUOTES, 'UTF-8') ?>">Get a quote</a>
        </div>
        <form id="lead-popup-form" name="lead-popup" method="POST" action="/thank-you" data-netlify="true" netlify-honeypot="bot-field">
            <input type="hidden" name="form-name" value="lead-popup">
            <p class="sr-only" aria-hidden="true">
                <label>Leave blank<input name="bot-field" tabindex="-1" autocomplete="off"></label>
            </p>
            <label for="lp-name">Name</label>
            <input id="lp-name" name="name" type="text" required maxlength="120" autocomplete="name">
            <label for="lp-email">Email</label>
            <input id="lp-email" name="email" type="email" required autocomplete="email">
            <label for="lp-phone">Phone</label>
            <input id="lp-phone" name="phone" type="tel" required maxlength="40" autocomplete="tel">
            <label for="lp-service">Service interest</label>
            <select id="lp-service" name="service" required>
                <option value="">Select…</option>
                <option value="Legionella Risk Assessment">Legionella risk assessment</option>
                <option value="Asbestos Survey">Asbestos survey</option>
                <?php foreach ($lpServices as $name): ?>
                    <option value="<?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
                <option value="Multi-service / not sure">Multi-service / not sure</option>
            </select>
            <label for="lp-message">Message (short)</label>
            <textarea id="lp-message" name="message" rows="3" required maxlength="800" placeholder="Postcode, property type, what you need…"></textarea>
            <input type="hidden" name="source" value="lead-popup">
            <button type="submit">Send to iComply</button>
            <p class="lead-status" id="lead-popup-status" role="status" aria-live="polite"></p>
        </form>
    </div>
</div>
<script src="<?= htmlspecialchars(assetUrl('/assets/js/lead-popup.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
