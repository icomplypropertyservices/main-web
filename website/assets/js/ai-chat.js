/* iComply AI assistant — lazy-loaded on first click of #icw-chat.
 * Talks to /api/chat (Netlify Function). Leads also go to Netlify Forms (form "lead-popup").
 * Nothing is stored in the browser except the open conversation in memory. */
(function () {
  if (window.icwOpen) return;
  var PHONE = '07517 806082', TEL = 'tel:07517806082';
  var WA = 'https://wa.me/447517806082?text=' + encodeURIComponent('Hi iComply, I need a quote');
  var FALLBACK = 'Sorry, the assistant is not available right now. Please call ' + PHONE + ' or WhatsApp us — we usually reply within 2 hours on business days.';
  var msgs = [], busy = false, leadSent = false, panel, log, input;

  var css = '.icw-panel{position:fixed;z-index:9991;left:max(.75rem,env(safe-area-inset-left));bottom:calc(5rem + env(safe-area-inset-bottom,0px));width:min(370px,calc(100vw - 1.5rem));height:min(520px,calc(100vh - 8rem));background:#fff;color:#111;border-radius:16px;box-shadow:0 20px 50px rgba(0,0,0,.35);display:flex;flex-direction:column;overflow:hidden;font:15px/1.4 system-ui,-apple-system,Segoe UI,sans-serif}'
    + '@media (max-width:767px){.icw-panel{bottom:calc(9.5rem + env(safe-area-inset-bottom,0px));height:min(480px,calc(100vh - 11rem))}}'
    + '.icw-hd{background:#0B1F3A;color:#fff;padding:.7rem .9rem;display:flex;justify-content:space-between;align-items:center;gap:.5rem}'
    + '.icw-hd b{font-size:15px}.icw-hd small{display:block;opacity:.75;font-size:12px}.icw-x{background:none;border:0;color:#fff;font-size:22px;cursor:pointer;line-height:1}'
    + '.icw-log{flex:1;overflow-y:auto;padding:.75rem;background:#f4f5f7}.icw-m{margin:.35rem 0;padding:.55rem .75rem;border-radius:12px;max-width:88%;white-space:pre-wrap;word-wrap:break-word}'
    + '.icw-a{background:#fff;border:1px solid #e3e5e8}.icw-u{background:#ff6b00;color:#fff;margin-left:auto}'
    + '.icw-act{display:flex;gap:.4rem;padding:.5rem .75rem;border-top:1px solid #eee}.icw-act a{flex:1;text-align:center;padding:.45rem;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px}'
    + '.icw-call{background:#0B1F3A;color:#fff}.icw-wa2{background:#16a34a;color:#fff}'
    + '.icw-f{display:flex;gap:.4rem;padding:.6rem .75rem;border-top:1px solid #eee}.icw-f input{flex:1;border:1px solid #cfd3d8;border-radius:10px;padding:.55rem .65rem;font:inherit;color:#111;background:#fff}.icw-f button{background:#ff6b00;color:#fff;border:0;border-radius:10px;padding:0 .9rem;font-weight:700;cursor:pointer}'
    + '.icw-note{font-size:11px;color:#666;padding:0 .75rem .5rem}';

  function el(t, c, txt) { var e = document.createElement(t); if (c) e.className = c; if (txt != null) e.textContent = txt; return e; }
  function add(role, text) {
    var m = el('div', 'icw-m ' + (role === 'user' ? 'icw-u' : 'icw-a'), text);
    log.appendChild(m); log.scrollTop = log.scrollHeight; return m;
  }

  function build() {
    var st = el('style'); st.textContent = css; document.head.appendChild(st);
    panel = el('div', 'icw-panel'); panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-label', 'iComply AI assistant');
    var hd = el('div', 'icw-hd'), t = el('div');
    t.appendChild(el('b', null, 'iComply assistant'));
    t.appendChild(el('small', null, 'AI assistant · a real person (Jack) follows up'));
    var x = el('button', 'icw-x', '×'); x.type = 'button'; x.setAttribute('aria-label', 'Close chat');
    x.onclick = function () { panel.style.display = 'none'; };
    hd.appendChild(t); hd.appendChild(x);
    log = el('div', 'icw-log'); log.setAttribute('aria-live', 'polite');
    var act = el('div', 'icw-act');
    var c = el('a', 'icw-call', 'Call ' + PHONE); c.href = TEL;
    var w = el('a', 'icw-wa2', 'WhatsApp'); w.href = WA; w.target = '_blank'; w.rel = 'noopener';
    act.appendChild(c); act.appendChild(w);
    var f = el('form', 'icw-f'); input = el('input'); input.type = 'text'; input.maxLength = 600;
    input.placeholder = 'Type your question…'; input.setAttribute('aria-label', 'Your message');
    var b = el('button', null, 'Send'); b.type = 'submit';
    f.appendChild(input); f.appendChild(b);
    f.onsubmit = function (e) { e.preventDefault(); send(); };
    var note = el('div', 'icw-note', 'You are chatting with an AI assistant. Details you share are sent to iComply only to arrange your quote.');
    panel.appendChild(hd); panel.appendChild(log); panel.appendChild(act); panel.appendChild(f); panel.appendChild(note);
    document.body.appendChild(panel);
    add('assistant', 'Hi! I\'m iComply\'s AI assistant. What do you need — EICR, gas safety, fire alarms, AOVs, barriers or something else? Tell me the job and your postcode and I\'ll get Jack to call you back.');
  }

  function submitLead(lead) {
    if (leadSent || !lead) return;
    leadSent = true;
    var body = {
      'form-name': 'lead-popup', 'bot-field': '', source: 'ai-chat',
      name: lead.name || '', phone: lead.phone || '', email: lead.email || '',
      service: lead.job || '', message: 'AI chat lead. Postcode: ' + (lead.postcode || '?') + '. Job: ' + (lead.job || '?') + '. Page: ' + location.pathname
    };
    var enc = Object.keys(body).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(body[k]); }).join('&');
    fetch('/', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: enc }).catch(function () {});
  }

  function send() {
    var text = (input.value || '').trim();
    if (!text || busy) return;
    input.value = ''; add('user', text);
    msgs.push({ role: 'user', content: text });
    if (msgs.length > 24) msgs = msgs.slice(-24);
    busy = true; var wait = add('assistant', '…');
    fetch('/api/chat', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ messages: msgs, page: location.pathname, leadSent: leadSent })
    }).then(function (r) { return r.json(); }).then(function (d) {
      var reply = (d && d.reply) || FALLBACK;
      wait.textContent = reply;
      msgs.push({ role: 'assistant', content: reply });
      if (d && d.lead) submitLead(d.lead);
    }).catch(function () { wait.textContent = FALLBACK; })
      .then(function () { busy = false; log.scrollTop = log.scrollHeight; input.focus(); });
  }

  window.icwOpen = function () {
    if (!panel) build();
    panel.style.display = 'flex';
    setTimeout(function () { input && input.focus(); }, 50);
  };
})();
