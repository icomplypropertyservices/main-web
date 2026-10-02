(function () {
  document.querySelectorAll('[data-kit-wizard]').forEach(function (root) {
  if (root.getAttribute('data-kit-booted') === '1') return;
  root.setAttribute('data-kit-booted', '1');

  var data;
  try {
    data = JSON.parse(root.getAttribute('data-wizard') || '{}');
  } catch (e) {
    return;
  }

  var steps = data.steps || [];
  var state = {};
  var idx = 0;
  var areaName = (root.getAttribute('data-area') || '').trim();
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
      if (!stepVisible(i)) continue;
      if (!selectedIds(steps[i]).length && !steps[i].optional) return i;
    }
    return steps.length - 1;
  }

  function renderProgress() {
    if (!progress) return;
    progress.innerHTML = '';
    var n = 0;
    steps.forEach(function (step, i) {
      if (!stepVisible(i) && !selectedIds(step).length) return;
      n += 1;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = n + '. ' + step.short;
      if (i === idx) btn.setAttribute('aria-current', 'step');
      if (selectedIds(step).length) btn.classList.add('is-done');
      btn.addEventListener('click', function () {
        if (i <= firstIncomplete() || i <= idx) {
          idx = i;
          render(true);
        }
      });
      progress.appendChild(btn);
    });
  }

  function stepVisible(i) {
    return visibleOptions(steps[i] || {}).length > 0;
  }

  function nextVisibleIdx(from) {
    for (var i = from + 1; i < steps.length; i++) {
      if (stepVisible(i)) return i;
    }
    return steps.length;
  }

  function prevVisibleIdx(from) {
    for (var i = from - 1; i >= 0; i--) {
      if (stepVisible(i)) return i;
    }
    return 0;
  }

  function clearDownstream(fromStep) {
    var si = steps.indexOf(fromStep);
    for (var i = si + 1; i < steps.length; i++) {
      delete state[steps[i].id];
    }
  }

  function toggle(step, id) {
    if (step.multi) {
      var cur = selectedIds(step);
      var pos = cur.indexOf(id);
      if (pos === -1) cur.push(id);
      else cur.splice(pos, 1);
      state[step.id] = cur;
      clearDownstream(step);
      return;
    }
    if (state[step.id] === id) return;
    state[step.id] = id;
    clearDownstream(step);
  }

  function collectLines() {
    var lines = [data.title || 'Kit builder'];
    if (areaName) lines.push('Town: ' + areaName);
    var shopItems = [];
    steps.forEach(function (step) {
      selectedIds(step).forEach(function (id) {
        var opt = optionById(step, id);
        if (!opt) return;
        var bit = step.title + ': ' + opt.label;
        if (opt.sku) bit += ' (SKU ' + opt.sku + ')';
        if (opt.screwfix_ref) bit += ' [price ref Screwfix ' + opt.screwfix_ref + (opt.screwfix_inc ? ' £' + opt.screwfix_inc + ' inc VAT' : '') + ' for branded ' + (opt.brand || 'SKU') + ']';
        if (opt.sell_price) bit += ' — sell £' + opt.sell_price;
        if (opt.cta === 'poa' || opt.cta === 'enquire') bit += ' — enquire/POA';
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
    if (areaName) html += '<p class="kit-note">Town: ' + escapeHtml(areaName) + '</p>';
    html += '<p class="kit-muted">' + escapeHtml(data.summary_blurb || data.brand_policy || 'No invented catalogue prices. Branded manufacturers only.') + '</p>';
    if (data.brand_policy && data.summary_blurb && data.brand_policy !== data.summary_blurb) {
      html += '<p class="kit-note">' + escapeHtml(data.brand_policy) + '</p>';
    }
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
            html += '<span class="kit-ref">Price reference (Screwfix) ' + escapeHtml(opt.screwfix_ref);
            if (opt.screwfix_inc) html += ' · £' + escapeHtml(opt.screwfix_inc) + ' inc VAT';
            html += ' for this branded ' + escapeHtml(opt.brand || 'SKU') + ' + 15%</span>';
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
      if (!opt.sell_price && (opt.cta === 'poa' || opt.cta === 'enquire')) {
        html += '<span class="kit-badge">Enquire / POA</span>';
      } else if (opt.cta === 'shop') {
        html += '<span class="kit-badge">Shop SKU</span>';
      }
      html += '</div><div class="kit-card-body"><h3>' + escapeHtml(opt.label) + '</h3>';
      if (opt.blurb) html += '<p>' + escapeHtml(opt.blurb) + '</p>';
      if (opt.sell_price) {
        html += '<div class="kit-price">Sell £' + escapeHtml(opt.sell_price) + '</div>';
        if (opt.screwfix_ref) {
          html += '<span class="kit-ref">Price reference (Screwfix) ' + escapeHtml(opt.screwfix_ref);
          if (opt.screwfix_inc) html += ' · £' + escapeHtml(opt.screwfix_inc) + ' inc VAT';
          html += ' · branded ' + escapeHtml(opt.brand || 'SKU') + ' + 15%</span>';
        }
      } else if (opt.sku && opt.cta === 'shop') {
        html += '<span class="kit-ref">SKU ' + escapeHtml(opt.sku) + '</span>';
      }
      html += '</div></button>';
    });
    html += '</div>';
    if (step.note) html += '<p class="kit-note">' + escapeHtml(step.note) + '</p>';
    var needsPick = !step.optional && !selectedIds(step).length;
    var atEnd = nextVisibleIdx(idx) >= steps.length;
    html += '<div class="kit-nav">';
    if (idx > 0) html += '<button type="button" class="kit-btn kit-btn-ghost" data-kit-back>Back</button>';
    html += '<button type="button" class="kit-btn kit-btn-orange" data-kit-next' + (needsPick ? ' disabled' : '') + '>' + (atEnd ? 'Review kit' : 'Continue') + '</button>';
    html += '</div>';
    if (needsPick) html += '<p class="kit-note">Choose an option to continue.</p>';
    panel.innerHTML = html;

    panel.querySelectorAll('[data-kit-opt]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-kit-opt');
        toggle(step, id);
        render(false);
        var again = panel.querySelector('[data-kit-opt="' + (window.CSS && CSS.escape ? CSS.escape(id) : id) + '"]');
        if (again) again.focus({ preventScroll: true });
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
        idx = nextVisibleIdx(idx);
        render(true);
      });
    }
    if (back) {
      back.addEventListener('click', function () {
        if (idx >= steps.length) {
          idx = prevVisibleIdx(steps.length);
        } else {
          idx = prevVisibleIdx(idx);
        }
        render(true);
      });
    }
  }

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
    });
  }
  function escapeAttr(s) { return escapeHtml(s); }

  function render(scroll) {
    if (idx < steps.length && !stepVisible(idx)) {
      idx = nextVisibleIdx(idx - 1);
    }
    renderProgress();
    if (idx >= steps.length) renderSummary();
    else renderStep();
    if (scroll) {
      root.scrollIntoView({ block: 'start', behavior: 'smooth' });
    }
  }

  render(false);
  });
})();
