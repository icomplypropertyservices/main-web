<?php
/**
 * Lightweight UK GDPR cookie notice.
 * Consent stored in localStorage — no external dependencies.
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
$privacyUrl = function_exists('url') ? url('/privacy.php') : (rtrim(SITE_URL, '/') . '/privacy.php');
?>
<div id="cookie-banner" role="dialog" aria-label="Cookie notice" aria-live="polite" hidden>
    <style>
        #cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: #0a2540;
            color: #fff;
            padding: 1rem 1.25rem;
            box-shadow: 0 -4px 20px rgba(0,0,0,.18);
            font-size: 0.875rem;
            line-height: 1.5;
        }
        #cookie-banner[hidden] { display: none !important; }
        #cookie-banner .cb-inner {
            max-width: 72rem;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem 1.25rem;
        }
        #cookie-banner p { margin: 0; flex: 1 1 16rem; color: rgba(255,255,255,.9); }
        #cookie-banner a { color: #ff6b00; text-decoration: underline; }
        #cookie-banner a:hover { color: #fff; }
        #cookie-banner .cb-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }
        #cookie-banner button {
            background: #ff6b00;
            color: #fff;
            border: none;
            border-radius: 9999px;
            padding: 0.55rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            white-space: nowrap;
        }
        #cookie-banner button:hover { background: #e65f00; }
        #cookie-banner button:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
    </style>
    <div class="cb-inner">
        <p>
            We use essential cookies to make this site work and, with your consent, analytics cookies to improve it.
            See our <a href="<?= htmlspecialchars($privacyUrl, ENT_QUOTES, 'UTF-8') ?>">Privacy Policy</a> for details.
        </p>
        <div class="cb-actions">
            <button type="button" id="cookie-accept">Accept</button>
        </div>
    </div>
</div>
<script>
(function () {
    var KEY = 'icomply_cookie_consent';
    var banner = document.getElementById('cookie-banner');
    var btn = document.getElementById('cookie-accept');
    if (!banner || !btn) return;
    try {
        if (localStorage.getItem(KEY)) return;
    } catch (e) { /* private mode — still show banner */ }
    banner.hidden = false;
    btn.addEventListener('click', function () {
        try { localStorage.setItem(KEY, 'accepted'); } catch (e) {}
        banner.hidden = true;
    });
})();
</script>
