(function () {
  var KEY_SEEN = 'icomply_lead_popup_seen';
  var KEY_SENT = 'icomply_lead_popup_sent';
  var DELAY_MS = 10000;
  var root = document.getElementById('lead-popup');
  var form = document.getElementById('lead-popup-form');
  var statusEl = document.getElementById('lead-popup-status');
  if (!root || !form) return;

  var lastFocus = null;
  var opened = false;

  function storeGet(k) {
    try { return sessionStorage.getItem(k) || localStorage.getItem(k); } catch (e) { return null; }
  }
  function storeSet(k, v, sessionOnly) {
    try {
      if (sessionOnly) sessionStorage.setItem(k, v);
      else localStorage.setItem(k, v);
    } catch (e) {}
  }

  function focusables() {
    return root.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
  }

  function open() {
    if (opened || storeGet(KEY_SENT) === '1' || storeGet(KEY_SEEN) === '1') return;
    opened = true;
    storeSet(KEY_SEEN, '1', true);
    lastFocus = document.activeElement;
    root.hidden = false;
    root.removeAttribute('hidden');
    var dialog = root.querySelector('.lead-popup-dialog');
    if (dialog) dialog.focus();
    document.addEventListener('keydown', onKey);
  }

  function close() {
    root.hidden = true;
    root.setAttribute('hidden', '');
    document.removeEventListener('keydown', onKey);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  function onKey(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      close();
      return;
    }
    if (e.key !== 'Tab') return;
    var nodes = Array.prototype.slice.call(focusables());
    if (!nodes.length) return;
    var first = nodes[0];
    var last = nodes[nodes.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }

  root.addEventListener('click', function (e) {
    if (e.target === root || (e.target && e.target.getAttribute && e.target.hasAttribute('data-lead-close'))) {
      close();
    }
  });

  function encode(data) {
    return Object.keys(data)
      .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k] == null ? '' : data[k]); })
      .join('&');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (statusEl) {
      statusEl.textContent = 'Sending…';
      statusEl.setAttribute('data-ok', '');
    }
    var fd = new FormData(form);
    var payload = {};
    fd.forEach(function (v, k) { payload[k] = v; });
    payload['form-name'] = 'lead-popup';

    fetch('/', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: encode(payload)
    }).then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      storeSet(KEY_SENT, '1', false);
      storeSet(KEY_SEEN, '1', true);
      if (statusEl) {
        statusEl.textContent = 'Sent. We will reply to your email. You can also call 07517806082.';
        statusEl.setAttribute('data-ok', '1');
      }
      form.reset();
      window.setTimeout(function () {
        window.location.href = '/thank-you?ref=lead-popup';
      }, 900);
    }).catch(function () {
      if (statusEl) {
        statusEl.textContent = 'Could not send from this preview. Use Call / WhatsApp, or the contact page.';
        statusEl.setAttribute('data-ok', '0');
      }
      form.submit();
    });
  });

  window.setTimeout(open, DELAY_MS);
})();
