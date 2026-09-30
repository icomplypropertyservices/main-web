/**
 * Live total for /get-a-quote. Mirrors website/includes/quote-builder.php.
 * Discounted units round to the nearest £1, half up, then multiply.
 */
(function (root, factory) {
  var api = factory();
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = api;
  }
  if (root) {
    root.IcomplyQuote = api;
  }
})(typeof window !== 'undefined' ? window : global, function () {
  function clampProperties(n) {
    n = parseInt(n, 10);
    if (!isFinite(n) || n < 1) return 1;
    if (n > 999) return 999;
    return n;
  }

  function tierFor(catalog, properties) {
    var tiers = catalog.tiers || [];
    var fallback = { id: 'T0', min: 1, max: 1, percent: 0 };
    for (var i = 0; i < tiers.length; i++) {
      var tier = tiers[i];
      var min = tier.min || 1;
      var max = tier.max == null ? 999999 : tier.max;
      if (properties >= min && properties <= max) return tier;
      fallback = tier;
    }
    return fallback;
  }

  function percentHundredths(percent) {
    return Math.round(percent * 100);
  }

  function discountPence(listPence, hundredths) {
    if (hundredths <= 0 || listPence <= 0) return listPence;
    var scaled = listPence * (10000 - hundredths);
    var pounds = Math.floor((scaled + 500000) / 1000000);
    return pounds * 100;
  }

  function formatPence(pence) {
    var neg = pence < 0;
    var v = Math.abs(pence);
    var pounds = Math.floor(v / 100);
    var rem = v % 100;
    var body = String(pounds).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    if (rem !== 0) body += '.' + String(rem).padStart(2, '0');
    return (neg ? '-' : '') + '£' + body;
  }

  function formatPercent(percent) {
    return percent.toFixed(1).replace(/\.0$/, '') + '%';
  }

  function unitPence(service, hundredths) {
    if (service.poa || service.pence == null) return null;
    if (!service.discount) return service.pence;
    return discountPence(service.pence, hundredths);
  }

  function calculate(catalog, properties, selected) {
    properties = clampProperties(properties);
    var tier = tierFor(catalog, properties);
    var percent = Number(tier.percent || 0);
    var hundredths = percentHundredths(percent);
    var byId = {};
    (catalog.services || []).forEach(function (service) {
      byId[service.id] = service;
    });

    var active = {};
    Object.keys(selected || {}).forEach(function (id) {
      var qty = parseInt(selected[id], 10);
      if (qty > 0 && byId[id]) active[id] = Math.min(qty, 99);
    });

    var bundle = byId.bundle;
    var supersedes = bundle && bundle.supersedes ? bundle.supersedes.slice() : [];
    var bundleExplicit = !!(bundle && active.bundle);
    var bundleAuto = false;
    if (bundleExplicit) {
      supersedes.forEach(function (id) { delete active[id]; });
    } else if (bundle && supersedes.length) {
      var allOn = supersedes.every(function (id) { return !!active[id]; });
      if (allOn) {
        var separate = 0;
        supersedes.forEach(function (id) {
          separate += unitPence(byId[id], hundredths) * properties;
        });
        var bundleUnit = unitPence(bundle, hundredths);
        if (bundleUnit * properties <= separate) {
          supersedes.forEach(function (id) { delete active[id]; });
          active.bundle = 1;
          bundleAuto = true;
        }
      }
    }

    var lines = [];
    var total = 0;
    var saving = 0;
    var hasPoa = false;
    (catalog.services || []).forEach(function (service) {
      if (!active[service.id]) return;
      var mode = service.qty || 'once';
      var qty = mode === 'properties' ? properties : active[service.id];
      if (qty < 1) return;
      var poa = !!service.poa || service.pence == null;
      if (poa) {
        hasPoa = true;
        lines.push({
          id: service.id,
          code: service.code || '',
          name: service.name,
          qty: qty,
          poa: true,
          unitLabel: 'POA',
          lineLabel: 'POA'
        });
        return;
      }
      var list = service.pence;
      var discounted = !!service.discount && hundredths > 0;
      var unit = unitPence(service, discounted ? hundredths : 0);
      var line = unit * qty;
      total += line;
      if (discounted) saving += (list * qty) - line;
      lines.push({
        id: service.id,
        code: service.code || '',
        name: service.name,
        qty: qty,
        poa: false,
        unitPence: unit,
        linePence: line,
        unitLabel: formatPence(unit),
        lineLabel: formatPence(line)
      });
    });

    var priced = lines.some(function (line) { return !line.poa; });
    var totalLabel;
    if (priced && hasPoa) totalLabel = formatPence(total) + ' + POA';
    else if (priced) totalLabel = formatPence(total);
    else if (hasPoa) totalLabel = 'POA';
    else totalLabel = formatPence(0);

    var tierLabel = tier.id + ' · ';
    tierLabel += percent > 0
      ? formatPercent(percent) + ' off certificates and inspections'
      : 'list price';

    var announce = 'Total ' + totalLabel + '. Tier ' + tier.id + ', ';
    announce += percent > 0
      ? formatPercent(percent) + ' off certificates and inspections. '
      : 'list price. ';
    announce += properties + ' ' + (properties === 1 ? 'property.' : 'properties.');
    if (hasPoa && priced) announce += ' Some items are priced after scope.';
    else if (hasPoa) announce += ' Price confirmed after scope.';

    var bundleNote = '';
    if (bundleAuto) {
      bundleNote = 'FRA, EICR and gas are priced as the £650 bundle because that is lower than the three certificates separately. The discount applies to the bundle once.';
    } else if (bundleExplicit) {
      bundleNote = 'The bundle includes FRA, EICR and gas on the same property. Those three are not added again.';
    }

    var vat = catalog.vatNote || '';
    var baseline = catalog.baselineNote || '';
    var summary = [
      'Properties: ' + properties,
      'Tier: ' + tierLabel,
      'Indicative total: ' + totalLabel,
      vat
    ];
    if (bundleNote) summary.push(bundleNote);
    summary.push('');
    if (!lines.length) summary.push('No services selected.');
    lines.forEach(function (line) {
      if (line.poa) {
        summary.push(line.code + ' ' + line.name + ' × ' + line.qty + ' — POA (confirm after scope)');
      } else {
        summary.push(line.code + ' ' + line.name + ' × ' + line.qty + ' @ ' + line.unitLabel + ' = ' + line.lineLabel);
      }
    });
    summary.push('');
    summary.push(baseline);

    return {
      properties: properties,
      tierId: tier.id,
      tierPercent: percent,
      tierLabel: tierLabel,
      lines: lines,
      totalPence: total,
      totalLabel: totalLabel,
      hasPoa: hasPoa,
      savingPence: saving,
      savingLabel: saving > 0 ? ('Multi-property discount saves ' + formatPence(saving) + '.') : '',
      bundleAuto: bundleAuto,
      bundleExplicit: bundleExplicit,
      bundleNote: bundleNote,
      announce: announce,
      summary: summary.join('\n').trim()
    };
  }

  function normalisePostcode(raw) {
    var pc = String(raw || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (pc.length >= 5 && pc.length <= 7) {
      return pc.slice(0, -3) + ' ' + pc.slice(-3);
    }
    return String(raw || '').trim().toUpperCase();
  }

  function validPostcode(raw) {
    return /^[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2}$/.test(normalisePostcode(raw));
  }

  return {
    calculate: calculate,
    formatPence: formatPence,
    clampProperties: clampProperties,
    normalisePostcode: normalisePostcode,
    validPostcode: validPostcode
  };
});

(function () {
  if (typeof document === 'undefined') return;

  function boot() {
    var form = document.getElementById('quote-builder-form');
    var dataEl = document.getElementById('quote-builder-catalog');
    if (!form || !dataEl || !window.IcomplyQuote) return;
    var catalog;
    try {
      catalog = JSON.parse(dataEl.textContent || '{}');
    } catch (e) {
      return;
    }

    var countInput = document.getElementById('qb-property-count');
    var totalEl = document.getElementById('qb-total');
    var tierEl = document.getElementById('qb-tier');
    var announceEl = document.getElementById('qb-announce');
    var linesEl = document.getElementById('qb-lines');
    var savingEl = document.getElementById('qb-saving');
    var bundleEl = document.getElementById('qb-bundle-note');
    var summaryEl = document.getElementById('qb-services-summary');
    var totalInput = document.getElementById('qb-quote-total');
    var tierInput = document.getElementById('qb-tier-value');
    var errorsEl = document.getElementById('qb-errors');
    var statusEl = document.getElementById('qb-status');

    function selectedFromForm() {
      var selected = {};
      form.querySelectorAll('[data-svc]').forEach(function (input) {
        if (input.disabled || !input.checked) return;
        var id = input.getAttribute('data-svc');
        var mode = input.getAttribute('data-qty-mode');
        if (mode === 'each') {
          var qtyInput = form.querySelector('[data-each="' + id + '"]');
          var qty = qtyInput ? parseInt(qtyInput.value, 10) : 1;
          if (qty > 0) selected[id] = qty;
        } else {
          selected[id] = 1;
        }
      });
      form.querySelectorAll('[data-hours]').forEach(function (input) {
        var qty = parseInt(input.value, 10);
        if (qty > 0) selected[input.getAttribute('data-hours')] = qty;
      });
      return selected;
    }

    function lockBundleParts() {
      var bundle = form.querySelector('[data-svc="bundle"]');
      var on = !!(bundle && bundle.checked);
      form.querySelectorAll('[data-superseded]').forEach(function (input) {
        input.disabled = on;
        var card = input.closest('.qb-card');
        if (card) card.classList.toggle('is-locked', on);
      });
    }

    function paint(result) {
      if (totalEl) totalEl.textContent = result.totalLabel;
      if (tierEl) tierEl.textContent = result.tierLabel;
      if (announceEl) announceEl.textContent = result.announce;
      if (summaryEl) summaryEl.value = result.summary;
      if (totalInput) totalInput.value = result.totalLabel;
      if (tierInput) tierInput.value = result.tierLabel;
      if (savingEl) {
        savingEl.hidden = !result.savingLabel;
        savingEl.textContent = result.savingLabel;
      }
      if (bundleEl) {
        bundleEl.hidden = !result.bundleNote;
        bundleEl.textContent = result.bundleNote;
      }
      if (!linesEl) return;
      linesEl.innerHTML = '';
      if (!result.lines.length) {
        var empty = document.createElement('li');
        empty.textContent = 'No services selected yet.';
        linesEl.appendChild(empty);
        return;
      }
      result.lines.forEach(function (line) {
        var li = document.createElement('li');
        var name = document.createElement('span');
        name.textContent = line.name + ' × ' + line.qty;
        var amount = document.createElement('span');
        amount.textContent = line.lineLabel;
        li.appendChild(name);
        li.appendChild(amount);
        linesEl.appendChild(li);
      });
    }

    function refresh() {
      if (countInput) {
        var clamped = window.IcomplyQuote.clampProperties(countInput.value);
        if (String(clamped) !== String(parseInt(countInput.value, 10) || '')) {
          /* keep what they are typing until blur */
        }
      }
      var properties = countInput ? countInput.value : 1;
      lockBundleParts();
      paint(window.IcomplyQuote.calculate(catalog, properties, selectedFromForm()));
    }

    form.addEventListener('change', refresh);
    form.addEventListener('input', refresh);
    if (countInput) {
      countInput.addEventListener('blur', function () {
        countInput.value = String(window.IcomplyQuote.clampProperties(countInput.value));
        refresh();
      });
    }

    form.querySelectorAll('[data-svc][data-qty-mode="each"]').forEach(function (input) {
      input.addEventListener('change', function () {
        var wrap = form.querySelector('[data-qty-for="' + input.getAttribute('data-svc') + '"]');
        if (!wrap) return;
        if (input.checked) wrap.hidden = false;
        else wrap.hidden = true;
      });
    });

    function showErrors(msgs) {
      if (!errorsEl) return;
      errorsEl.innerHTML = '';
      msgs.forEach(function (msg) {
        var p = document.createElement('p');
        p.textContent = msg;
        errorsEl.appendChild(p);
      });
      errorsEl.hidden = false;
      errorsEl.focus();
    }

    form.addEventListener('submit', function (e) {
      if (countInput) countInput.value = String(window.IcomplyQuote.clampProperties(countInput.value));
      var postcode = form.querySelector('[name="postcode"]');
      if (postcode) postcode.value = window.IcomplyQuote.normalisePostcode(postcode.value);
      refresh();
      var result = window.IcomplyQuote.calculate(
        catalog,
        countInput ? countInput.value : 1,
        selectedFromForm()
      );
      var msgs = [];
      if (!result.lines.length) msgs.push('Please select at least one service.');
      if (postcode && !window.IcomplyQuote.validPostcode(postcode.value)) {
        msgs.push('Please enter a UK postcode.');
        postcode.setAttribute('aria-invalid', 'true');
      } else if (postcode) {
        postcode.removeAttribute('aria-invalid');
      }
      if (msgs.length) {
        e.preventDefault();
        showErrors(msgs);
        return;
      }
      if (errorsEl) errorsEl.hidden = true;

      if (form.getAttribute('data-netlify') === 'true') {
        e.preventDefault();
        var submit = form.querySelector('[type="submit"]');
        if (submit) submit.disabled = true;
        if (statusEl) statusEl.textContent = 'Sending…';
        var payload = {};
        new FormData(form).forEach(function (value, key) {
          if (payload[key] == null) payload[key] = value;
          else if (Array.isArray(payload[key])) payload[key].push(value);
          else payload[key] = [payload[key], value];
        });
        payload['form-name'] = 'quote-builder';
        var body = Object.keys(payload).map(function (key) {
          var val = payload[key];
          if (Array.isArray(val)) {
            return val.map(function (item) {
              return encodeURIComponent(key) + '=' + encodeURIComponent(item);
            }).join('&');
          }
          return encodeURIComponent(key) + '=' + encodeURIComponent(val == null ? '' : val);
        }).join('&');
        fetch('/', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body
        }).then(function (res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          window.location.href = '/thank-you?ref=quote-builder';
        }).catch(function () {
          if (submit) submit.disabled = false;
          var phone = form.getAttribute('data-phone') || '07517806082';
          if (statusEl) {
            statusEl.textContent = 'Could not send just now. Call ' + phone + ' or use WhatsApp and we will take the same details.';
          }
        });
      }
    });

    refresh();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
