(function () {
  var root = document.getElementById('kit-wizard');
  if (!root) return;

  var data;
  try {
    data = JSON.parse(root.getAttribute('data-wizard') || '{}');
  } catch (e) {
    return;
  }

  var steps = data.steps || [];
  var state = {};
  var idx = 0;
  var shopHost = data.shop_host || 'https://shop.icomplypropertyservices.co.uk';
  var wa = root.getAttribute('data-wa') || '';
  var contact = root.getAttribute('data-contact') || '/contact';

  var progress = root.querySelector('[data-kit-progress]');
  var panel = root.querySelector('[data-kit-panel]');

  function selectedIds(step) {
    var cur = state[step.id];
    if (!cur) return [];
    return Array.isArray(cur) ? cur : [cur];
  }

  function optionById(step, id) {
    return (step.options || []).find(function (o) { return o.id === id; });
  }

  function visibleOptions(step) {
    return (step.options || []).filter(function (opt) {
      var rule = opt.show_if;
      if (!rule || !rule.step) return true;
      var picked = state[rule.step];
      var values = rule.values || [];
      if (Array.isArray(picked)) {
        return picked.some(function (p) { return values.indexOf(p) !== -1; });
      }
      return values.indexOf(picked) !== -1;
    });
  }

  function firstIncomplete() {
    for (var i = 0; i < steps.length; i++) {
      if (!selectedIds(steps[i]).length && !steps[i].optional) return i;
    }
    return steps.length - 1;
  }

  function renderProgress() {
    if (!progress) return;
    progress.innerHTML = '';
    steps.forEach(function (step, i) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = (i + 1) + '. ' + step.short;
      if (i === idx) btn.setAttribute('aria-current', 'step');
      if (selectedIds(step).length) btn.classList.add('is-done');
      btn.addEventListener('click', function () {
        if (i <= firstIncomplete() || i <= idx) {
          idx = i;
          render();
        }
      });
      progress.appendChild(btn);
    });
  }

  function toggle(step, id) {
    if (step.multi) {
      var cur = selectedIds(step);
      var pos = cur.indexOf(id);
      if (pos === -1) cur.push(id);
      else cur.splice(pos, 1);
      state[step.id] = cur;
    } else {
      state[step.id] = id;
    }
  }

  function collectLines() {
    var lines = [data.title || 'Kit builder'];
    var shopItems = [];
    steps.forEach(function (step) {
      selectedIds(step).forEach(function (id) {
        var opt = optionById(step, id);
        if (!opt) return;
        var bit = step.title + ': ' + opt.label;
        if (opt.sku) bit += ' (SKU ' + opt.sku + ')';
        if (opt.screwfix_ref) bit += ' [trade/Screwfix ref ' + opt.screwfix_ref + ']';
        if (opt.sell_price) bit += ' — sell £' + opt.sell_price;
        if (opt.cta === 'poa') bit += ' — enquire/POA';
        lines.push(bit);
        if (opt.handle && opt.cta === 'shop') {
          shopItems.push(opt);
        }
      });
    });
    return { lines: lines, shopItems: shopItems };
  }

  function renderSummary() {
    var picked = collectLines();
    var html = '<h2>' + escapeHtml(data.summary_title || 'Your kit') + '</h2>';
    html += '<p class="kit-muted">' + escapeHtml(data.summary_blurb || 'No invented catalogue prices. Branded manufacturers only.') + '</p>';
    html += '<div class="kit-summary">';
    steps.forEach(function (step) {
      selectedIds(step).forEach(function (id) {
        var opt = optionById(step, id);
        if (!opt) return;
        html += '<div class="kit-summary-row">';
        if (opt.image) {
          html += '<img src="' + escapeAttr(opt.image) + '" alt="' + escapeAttr(opt.label) + '">';
        } else {
          html += '<img src="' + escapeAttr(data.hero_image || '') + '" alt="">';
        }
        html += '<div><strong>' + escapeHtml(opt.label) + '</strong>';
        html += '<div class="kit-muted">' + escapeHtml(step.title) + (opt.brand ? ' · ' + opt.brand : '') + '</div>';
        if (opt.sell_price) {
          html += '<div class="kit-price">Sell £' + escapeHtml(opt.sell_price) + '</div>';
          if (opt.screwfix_ref) {
            html += '<span class="kit-ref">Trade / Screwfix ref ' + escapeHtml(opt.screwfix_ref) + ' (price reference only)</span>';
          }
        } else if (opt.cta === 'poa' || opt.cta === 'enquire') {
          html += '<div class="kit-price">Enquire / POA</div>';
        }
        html += '</div></div>';
      });
    });
    html += '</div>';

    var msg = picked.lines.join('\n');
    var waHref = wa ? ('https://wa.me/' + wa + '?text=' + encodeURIComponent('Hi iComply, kit builder:\n' + msg)) : contact;
    var contactHref = contact + (contact.indexOf('?') === -1 ? '?' : '&') + 'kit=' + encodeURIComponent(msg);

    html += '<div class="kit-nav">';
    html += '<button type="button" class="kit-btn kit-btn-ghost" data-kit-back>Back</button>';
    html += '<a class="kit-btn kit-btn-orange" href="' + escapeAttr(contactHref) + '">Enquire / POA</a>';
    html += '<a class="kit-btn kit-btn-navy" target="_blank" rel="noopener" href="' + escapeAttr(waHref) + '">WhatsApp this kit</a>';
    if (picked.shopItems.length === 1 && picked.shopItems[0].handle) {
      html += '<a class="kit-btn kit-btn-navy" target="_blank" rel="noopener" href="' + escapeAttr(shopHost + '/products/' + picked.shopItems[0].handle) + '">Open on Shopify</a>';
    } else if (picked.shopItems.length > 1) {
      var cart = picked.shopItems.filter(function (i) { return i.variant_id; }).map(function (i) {
        return i.variant_id + ':1';
      }).join(',');
      if (cart) {
        html += '<a class="kit-btn kit-btn-navy" target="_blank" rel="noopener" href="' + escapeAttr(shopHost + '/cart/' + cart) + '">Add known SKUs to Shopify cart</a>';
      }
    }
    html += '</div>';
    panel.innerHTML = html;
    bindNav();
  }

  function renderStep() {
    var step = steps[idx];
    if (!step) { renderSummary(); return; }
    var opts = visibleOptions(step);
    var html = '<h2>' + escapeHtml(step.title) + '</h2>';
    html += '<p class="kit-muted">' + escapeHtml(step.blurb || '') + '</p>';
    html += '<div class="kit-grid" role="group" aria-label="' + escapeAttr(step.title) + '">';
    opts.forEach(function (opt) {
      var on = selectedIds(step).indexOf(opt.id) !== -1;
      html += '<button type="button" class="kit-card' + (on ? ' is-selected' : '') + '" data-kit-opt="' + escapeAttr(opt.id) + '">';
      html += '<div class="kit-card-media">';
      if (opt.image) {
        html += '<img class="' + (opt.cover ? 'kit-cover' : '') + '" src="' + escapeAttr(opt.image) + '" alt="' + escapeAttr(opt.label) + '">';
      }
      if (opt.logo) {
        html += '<img class="kit-logo" src="' + escapeAttr(opt.logo) + '" alt="' + escapeAttr(opt.brand || opt.label) + '">';
      } else if (opt.brand) {
        html += '<span class="kit-logo-fallback">' + escapeHtml(opt.brand) + '</span>';
      }
      if (opt.cta === 'poa' || opt.cta === 'enquire') {
        html += '<span class="kit-badge">Enquire / POA</span>';
      } else if (opt.cta === 'shop') {
        html += '<span class="kit-badge">Shop SKU</span>';
      }
      html += '</div><div class="kit-card-body"><h3>' + escapeHtml(opt.label) + '</h3>';
      if (opt.blurb) html += '<p>' + escapeHtml(opt.blurb) + '</p>';
      if (opt.sell_price) {
        html += '<div class="kit-price">Sell £' + escapeHtml(opt.sell_price) + '</div>';
        if (opt.screwfix_ref) {
          html += '<span class="kit-ref">Screwfix ref ' + escapeHtml(opt.screwfix_ref) + ' · branded ' + escapeHtml(opt.brand || '') + ' (price reference only)</span>';
        }
      } else if (opt.sku && opt.cta === 'shop') {
        html += '<span class="kit-ref">SKU ' + escapeHtml(opt.sku) + '</span>';
      }
      html += '</div></button>';
    });
    html += '</div>';
    if (step.note) html += '<p class="kit-note">' + escapeHtml(step.note) + '</p>';
    html += '<div class="kit-nav">';
    if (idx > 0) html += '<button type="button" class="kit-btn kit-btn-ghost" data-kit-back>Back</button>';
    html += '<button type="button" class="kit-btn kit-btn-orange" data-kit-next>' + (idx === steps.length - 1 ? 'Review kit' : 'Continue') + '</button>';
    html += '</div>';
    panel.innerHTML = html;

    panel.querySelectorAll('[data-kit-opt]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        toggle(step, btn.getAttribute('data-kit-opt'));
        render();
      });
    });
    bindNav();
  }

  function bindNav() {
    var next = panel.querySelector('[data-kit-next]');
    var back = panel.querySelector('[data-kit-back]');
    if (next) {
      next.addEventListener('click', function () {
        var step = steps[idx];
        if (step && !step.optional && !selectedIds(step).length) return;
        if (idx >= steps.length - 1) {
          idx = steps.length;
        } else {
          idx += 1;
        }
        render();
      });
    }
    if (back) {
      back.addEventListener('click', function () {
        idx = Math.max(0, idx - 1);
        render();
      });
    }
  }

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
    });
  }
  function escapeAttr(s) { return escapeHtml(s); }

  function render() {
    renderProgress();
    if (idx >= steps.length) renderSummary();
    else renderStep();
    root.scrollIntoView({ block: 'start', behavior: 'smooth' });
  }

  render();
})();
