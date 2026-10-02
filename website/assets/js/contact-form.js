(function () {
  var forms = document.querySelectorAll('form[data-contact-form]');
  if (!forms.length) return;

  function encode(data) {
    return Object.keys(data)
      .map(function (k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k] == null ? '' : data[k]);
      })
      .join('&');
  }

  forms.forEach(function (form) {
    var btn = form.querySelector('[type="submit"]');
    var label = form.querySelector('[data-submit-label]');
    var loading = form.querySelector('[data-submit-loading]');

    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) return;
      e.preventDefault();
      if (btn) btn.disabled = true;
      if (label) label.classList.add('hidden');
      if (loading) loading.classList.remove('hidden');

      var payload = {};
      new FormData(form).forEach(function (v, k) { payload[k] = v; });
      payload['form-name'] = 'contact';

      fetch('/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: encode(payload)
      }).then(function (res) {
        return res.text().then(function (text) {
          if (!res.ok) throw new Error('status');
          if (text.indexOf('home-hero') !== -1) throw new Error('not-netlify');
          window.location.href = '/thank-you?ref=contact';
        });
      }).catch(function () {
        if (btn) btn.disabled = false;
        if (label) label.classList.remove('hidden');
        if (loading) loading.classList.add('hidden');
        HTMLFormElement.prototype.submit.call(form);
      });
    });
  });
})();
