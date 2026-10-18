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

<div id="lead-popup" class="lead-popup lp-mini" hidden>
    <div class="lead-popup-dialog" role="dialog" aria-modal="false" aria-labelledby="lead-popup-title" tabindex="-1">
        <button type="button" class="lead-close" data-lead-close aria-label="Close">&times;</button>
        <p id="lead-popup-title" class="lp-title">Need a quote? Talk to us now.</p>
        <div class="lead-quick">
            <a class="lead-wa" href="https://wa.me/<?= htmlspecialchars($lpWa, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode('Hi iComply, I need a quote') ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a class="lead-call" href="<?= htmlspecialchars($lpPhoneHref, ENT_QUOTES, 'UTF-8') ?>" aria-label="Call 07517 806082">Call us</a>
        </div>
    </div>
</div>
<style>
.lead-popup.lp-mini{position:fixed!important;inset:calc(4.75rem + env(safe-area-inset-top,0px)) auto auto 50%!important;transform:translateX(-50%);background:none!important;width:min(20rem,calc(100vw - 2rem));height:auto!important;z-index:9995;display:block;padding:0!important}
.lead-popup.lp-mini[hidden]{display:none!important}
.lp-mini .lead-popup-dialog{position:relative;max-width:none!important;width:100%!important;max-height:none!important;margin:0!important;padding:.85rem 2.6rem .85rem .9rem!important;border-radius:.8rem;background:#0B1F3A!important;color:#fff!important;box-shadow:0 12px 30px rgba(0,0,0,.35);overflow:visible!important}
.lp-mini .lp-title{margin:0 0 .55rem;font:700 .95rem/1.3 system-ui,sans-serif;color:#fff}
.lp-mini .lead-quick{display:flex;gap:.5rem;margin:0}
.lp-mini .lead-quick a{flex:1;text-align:center;padding:.55rem .4rem;border-radius:.5rem;font:700 .85rem/1.2 system-ui,sans-serif;text-decoration:none;color:#fff!important}
.lp-mini .lead-wa{background:#16a34a!important}.lp-mini .lead-call{background:#FF6B00!important}
.lp-mini .lead-close{position:absolute;top:.3rem;right:.3rem;width:2.2rem;height:2.2rem;border:0;border-radius:999px;background:rgba(255,255,255,.15);color:#fff;font-size:1.4rem;line-height:1;cursor:pointer}
</style>
<script src="<?= htmlspecialchars(assetUrl('/assets/js/lead-popup.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
