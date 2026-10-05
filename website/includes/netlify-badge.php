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

    return '<a href="' . $safe . '" target="_blank" rel="noopener" aria-label="WhatsApp" class="wa-float">💬</a>';
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
