(function () {
  // Small call/WhatsApp prompt. No form. Shown once, then hidden for 7 days.
  var KEY = 'icomply_lead_popup_closed_at';
  var WEEK = 7 * 24 * 3600 * 1000;
  var DELAY_MS = 20000;
  var root = document.getElementById('lead-popup');
  if (!root) return;
  function recently() {
    try { var t = +localStorage.getItem(KEY) || 0; return Date.now() - t < WEEK || sessionStorage.getItem(KEY); } catch (e) { return false; }
  }
  function remember() { try { localStorage.setItem(KEY, String(Date.now())); sessionStorage.setItem(KEY, '1'); } catch (e) {} }
  function close() { root.hidden = true; root.setAttribute('hidden', ''); remember(); }
  root.addEventListener('click', function (e) {
    var t = e.target;
    if (t && t.closest && t.closest('[data-lead-close]')) { e.preventDefault(); close(); }
    else if (t && t.closest && t.closest('a')) { remember(); }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !root.hidden) close(); });
  if (recently()) return;
  window.setTimeout(function () { if (!recently()) { root.hidden = false; root.removeAttribute('hidden'); remember(); } }, DELAY_MS);
})();
