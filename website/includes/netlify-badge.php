<?php
/**
 * Sitewide WhatsApp bubble + removal of Netlify's edge-injected badge.
 *
 * Netlify adds <script src="/.netlify/scripts/hud"> on the CDN (Powered by
 * Netlify / pre-launch toolbar). The publish headers omit 'self' from
 * script-src so that URL cannot execute. This script removes the node if
 * the tag still lands in the DOM.
 */
declare(strict_types=1);

function icomplyWhatsappFloatHtml(?string $number = null): string
{
    $digits = preg_replace('/\D+/', '', (string)($number ?? (defined('WHATSAPP') ? WHATSAPP : '')));
    if ($digits === '') {
        $digits = '447517806082';
    }
    $href = 'https://wa.me/' . $digits . '?text=' . rawurlencode('Hi iComply, I need a quote for compliance services');
    $safe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
    // UK display form of the same number (447517806082 -> 07517806082).
    $display = str_starts_with($digits, '44') ? '0' . substr($digits, 2) : $digits;
    $label = htmlspecialchars('WhatsApp iComply on ' . $display, ENT_QUOTES, 'UTF-8');

    return '<a href="' . $safe . '" target="_blank" rel="noopener" aria-label="' . $label . '" title="' . $label . '" class="wa-float">'
        . '<svg aria-hidden="true" focusable="false" viewBox="0 0 32 32" width="30" height="30" fill="currentColor">'
        . '<path d="M16.04 3C8.86 3 3.03 8.82 3.03 16c0 2.3.6 4.53 1.74 6.5L3 29l6.68-1.74A12.96 12.96 0 0 0 16.04 29C23.2 29 29 23.18 29 16S23.2 3 16.04 3zm0 23.7c-2 0-3.95-.54-5.65-1.55l-.4-.24-3.96 1.03 1.06-3.86-.26-.4A10.66 10.66 0 0 1 5.33 16c0-5.9 4.8-10.7 10.71-10.7 5.9 0 10.66 4.8 10.66 10.7 0 5.9-4.77 10.7-10.66 10.7zm5.86-8c-.32-.16-1.9-.94-2.2-1.04-.3-.11-.51-.16-.73.16-.21.32-.83 1.04-1.02 1.26-.19.21-.38.24-.7.08-.32-.16-1.35-.5-2.58-1.6-.95-.85-1.6-1.9-1.78-2.22-.19-.32-.02-.5.14-.65.14-.14.32-.38.48-.56.16-.19.21-.32.32-.54.1-.21.05-.4-.03-.56-.08-.16-.72-1.74-.99-2.38-.26-.62-.52-.54-.72-.55h-.62c-.21 0-.56.08-.85.4-.3.32-1.12 1.1-1.12 2.67 0 1.58 1.15 3.1 1.3 3.32.17.21 2.26 3.45 5.47 4.84.77.33 1.36.53 1.83.68.77.24 1.47.2 2.02.12.62-.09 1.9-.78 2.17-1.53.27-.75.27-1.4.19-1.53-.08-.13-.29-.21-.61-.37z"/>'
        . '</svg><span class="sr-only">WhatsApp ' . htmlspecialchars($display, ENT_QUOTES, 'UTF-8') . '</span></a>';
}

function icomplyNetlifyBadgeStripHtml(): string
{
    return <<<'HTML'
<script>
(function () {
  var re = /(?:^|\/)\.netlify\/scripts\/hud|netlify-identity|netlify-drawer|powered-by-netlify/i;
  function bad(el) {
    if (!el || el.nodeType !== 1 || !el.getAttribute) return false;
    var src = el.getAttribute('src') || el.getAttribute('href') || '';
    var id = el.id || '';
    var cls = typeof el.className === 'string' ? el.className : '';
    var label = (el.getAttribute('aria-label') || '') + ' ' + (el.getAttribute('title') || '');
    if (el.getAttribute('data-nf-variant') || el.getAttribute('data-netlify-site-id')) return true;
    if (re.test(src + ' ' + id + ' ' + cls)) return true;
    return /powered by netlify/i.test(label);
  }
  var queued = false;
  function sweep() {
    queued = false;
    var nodes = document.querySelectorAll('script,iframe,a,div,button,aside');
    for (var i = 0; i < nodes.length; i++) {
      if (bad(nodes[i]) && nodes[i].parentNode) nodes[i].parentNode.removeChild(nodes[i]);
    }
  }
  function schedule() {
    if (queued) return;
    queued = true;
    setTimeout(sweep, 0);
  }
  sweep();
  if (window.MutationObserver) {
    new MutationObserver(schedule).observe(document.documentElement, { childList: true, subtree: true });
  }
})();
</script>
HTML;
}
