(function () {
  var root = document.querySelector('[data-mfr-wizard]');
  if (!root) return;
  var steps = Array.prototype.slice.call(root.querySelectorAll('.mfr-wizard-step'));
  var dots = Array.prototype.slice.call(root.querySelectorAll('[data-dot]'));
  var index = 0;

  function show(i) {
    index = Math.max(0, Math.min(steps.length - 1, i));
    steps.forEach(function (step, n) {
      step.hidden = n !== index;
    });
    dots.forEach(function (dot, n) {
      dot.setAttribute('data-current', n === index ? 'true' : 'false');
    });
    var back = root.querySelector('[data-wiz-back]');
    var next = root.querySelector('[data-wiz-next]');
    if (back) back.hidden = index === 0;
    if (next) next.textContent = index === steps.length - 1 ? 'Continue to quote' : 'Next';
  }

  var nextBtn = root.querySelector('[data-wiz-next]');
  var backBtn = root.querySelector('[data-wiz-back]');
  if (nextBtn) nextBtn.addEventListener('click', function () {
    if (index >= steps.length - 1) {
      var quote = document.getElementById('quote');
      if (quote) quote.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    show(index + 1);
  });
  if (backBtn) backBtn.addEventListener('click', function () { show(index - 1); });
  show(0);

  var form = document.querySelector('form[data-mfr-quote]');
  if (!form) return;
  form.addEventListener('submit', function () {
    var message = form.querySelector('[name="message"]');
    if (!message) return;
    var picked = [];
    ['wizard_job', 'wizard_line', 'wizard_kit'].forEach(function (name) {
      var el = root.querySelector('input[name="' + name + '"]:checked');
      if (el && el.value) picked.push(el.value);
    });
    if (!picked.length) return;
    var note = 'Wizard: ' + picked.join(' | ') + '.';
    if (message.value.indexOf('Wizard:') === -1) {
      message.value = (message.value ? message.value.replace(/\s+$/, '') + '\n' : '') + note;
    }
  });
})();
