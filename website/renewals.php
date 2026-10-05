<?php
/**
 * Certificate / service renewal date logger.
 * Netlify Form "renewals" → notifications to info@icomplypropertyservices.co.uk
 * Success page: /renewals/thanks
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Log your renewal dates | Certificate reminders — iComply';
$metaDesc = 'Log EICR, gas safety CP12, fire risk assessment, fire alarm, emergency lighting, PAT, legionella, AOV, barrier and access control renewal dates. We email a reminder before each one is due — no spam.';
$metaKeywords = 'certificate renewal reminder, EICR due date, gas safety CP12 reminder, fire risk assessment renewal, landlord compliance diary, Stockport';
$ogImage = url('/assets/images/brand/icomply-mark-512.png');
$canonicalUrl = url('/renewals.php');

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

$certFields = [
    ['name' => 'eicr_date', 'label' => 'EICR (electrical installation condition report)'],
    ['name' => 'gas_cp12_date', 'label' => 'Gas Safety CP12'],
    ['name' => 'fra_date', 'label' => 'Fire Risk Assessment'],
    ['name' => 'fire_alarm_date', 'label' => 'Fire alarm service'],
    ['name' => 'emergency_lighting_date', 'label' => 'Emergency lighting test'],
    ['name' => 'pat_date', 'label' => 'PAT (portable appliance testing)'],
    ['name' => 'legionella_date', 'label' => 'Legionella risk assessment'],
    ['name' => 'aov_date', 'label' => 'AOV / smoke vent service'],
    ['name' => 'barrier_date', 'label' => 'Barrier / gate service'],
    ['name' => 'access_cctv_date', 'label' => 'Access control / CCTV service'],
];

require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
  <div class="relative max-w-3xl mx-auto px-6 py-14 md:py-18 text-center">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Free renewal diary</div>
    <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">Log your certificate &amp; service renewal dates</h1>
    <p class="mt-5 text-lg text-white/80 max-w-2xl mx-auto">One form for one property or a whole portfolio. We'll email you a reminder before each one's due — no spam.</p>
  </div>
</section>

<section class="bg-[#F4F6F9] border-b">
  <div class="max-w-3xl mx-auto px-6 py-10 md:py-14">
    <form name="renewals" method="POST" action="/renewals/thanks" data-netlify="true" netlify-honeypot="bot-field" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 space-y-6 shadow-sm" id="renewals-form">
      <input type="hidden" name="form-name" value="renewals">
      <p class="hidden" aria-hidden="true">
        <label>Don't fill this out: <input name="bot-field" tabindex="-1" autocomplete="off"></label>
      </p>

      <div>
        <h2 class="text-xl font-semibold text-[#0B1F3A]">Your details</h2>
        <p class="text-sm text-zinc-500 mt-1">So we know who to remind.</p>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <label class="block">
          <span class="text-sm font-medium text-[#0B1F3A]">Full name <span class="text-[#ff6b00]">*</span></span>
          <input required name="name" type="text" maxlength="120" autocomplete="name" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#ff6b00]/ placeholder="Jane Smith">
        </label>
        <label class="block">
          <span class="text-sm font-medium text-[#0B1F3A]">Company / organisation</span>
          <input name="company" type="text" maxlength="160" autocomplete="organization" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#ff6b00]" placeholder="Optional">
        </label>
        <label class="block">
          <span class="text-sm font-medium text-[#0B1F3A]">Email <span class="text-[#ff6b00]">*</span></span>
          <input required name="email" type="email" maxlength="160" autocomplete="email" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#ff6b00]" placeholder="you@company.co.uk">
        </label>
        <label class="block">
          <span class="text-sm font-medium text-[#0B1F3A]">Phone <span class="text-[#ff6b00]">*</span></span>
          <input required name="phone" type="tel" maxlength="40" autocomplete="tel" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#ff6b00]" placeholder="07…">
        </label>
      </div>

      <div class="border-t border-zinc-200 pt-6">
        <h2 class="text-xl font-semibold text-[#0B1F3A]">Properties</h2>
        <p class="text-sm text-zinc-500 mt-1">Fill in only the dates that apply. Leave the rest blank. Without JavaScript this form supports one property — use “Add another property” if you have more.</p>
      </div>

      <div id="property-list" class="space-y-6">
        <fieldset class="property-block border border-zinc-200 rounded-2xl p-5 bg-[#F4F6F9]" data-property-index="0">
          <legend class="px-2 text-sm font-semibold uppercase tracking-wide text-[#ff6b00]">Property 1</legend>
          <div class="grid md:grid-cols-3 gap-4 mt-2">
            <label class="block md:col-span-2">
              <span class="text-sm font-medium text-[#0B1F3A]">Address <span class="text-[#ff6b00]">*</span></span>
              <input required name="property_address[]" type="text" maxlength="200" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-[#ff6b00]" placeholder="Building / street">
            </label>
            <label class="block">
              <span class="text-sm font-medium text-[#0B1F3A]">Postcode <span class="text-[#ff6b00]">*</span></span>
              <input required name="property_postcode[]" type="text" maxlength="12" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-[#ff6b00]" placeholder="SK2 5DE">
            </label>
          </div>
          <div class="grid sm:grid-cols-2 gap-3 mt-4">
<?php foreach ($certFields as $f): ?>
            <label class="block">
              <span class="text-sm font-medium text-[#0B1F3A]"><?= htmlspecialchars($f['label'], ENT_QUOTES, 'UTF-8') ?></span>
              <input name="<?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?>[]" type="date" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-[#ff6b00]">
            </label>
<?php endforeach; ?>
          </div>
          <label class="block mt-4">
            <span class="text-sm font-medium text-[#0B1F3A]">Other (describe + date)</span>
            <input name="other_notes[]" type="text" maxlength="300" class="mt-1 w-full rounded-xl border border-zinc-300 px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-[#ff6b00]" placeholder="e.g. Dry riser test — 2027-03-01">
          </label>
        </fieldset>
      </div>

      <div>
        <button type="button" id="add-property" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 border-[#0B1F3A] text-[#0B1F3A] font-semibold hover:bg-[#0B1F3A] hover:text-white transition">
          + Add another property
        </button>
        <noscript><p class="text-sm text-zinc-500 mt-2">JavaScript is off — submit one property now, or email <a class="text-[#ff6b00] font-semibold" href="mailto:info@icomplypropertyservices.co.uk">info@icomplypropertyservices.co.uk</a> a list for the rest.</p></noscript>
      </div>

      <div class="border-t border-zinc-200 pt-6 space-y-4">
        <h2 class="text-xl font-semibold text-[#0B1F3A]">Reminders</h2>
        <fieldset>
          <legend class="text-sm font-medium text-[#0B1F3A] mb-2">Email me a reminder <span class="text-[#ff6b00]">*</span></legend>
          <div class="flex flex-wrap gap-4 text-sm text-[#0F172A]">
            <label class="inline-flex items-center gap-2"><input required type="radio" name="reminder_days" value="60" class="accent-[#ff6b00]"> 60 days before</label>
            <label class="inline-flex items-center gap-2"><input type="radio" name="reminder_days" value="30" checked class="accent-[#ff6b00]"> 30 days before</label>
            <label class="inline-flex items-center gap-2"><input type="radio" name="reminder_days" value="14" class="accent-[#ff6b00]"> 14 days before</label>
          </div>
        </fieldset>
        <label class="flex items-start gap-3 text-sm text-[#0F172A]">
          <input required type="checkbox" name="gdpr_consent" value="yes" class="mt-1 accent-[#ff6b00]">
          <span>I agree that iComply Property Services may email me reminders about the dates I have logged, and store this information for that purpose. I can ask to be forgotten at any time. <span class="text-[#ff6b00]">*</span></span>
        </label>
      </div>

      <div class="rounded-2xl bg-[#FFF4EB] border border-[#FFD3B0] p-4 text-sm text-[#0F172A]">
        <strong class="text-[#0B1F3A]">Privacy note.</strong>
        We use your details only to send the reminders you asked for and to follow up if a certificate is due with us. We do not sell your data. See our
        <a href="<?= url('/privacy.php') ?>" class="text-[#ff6b00] font-semibold underline">privacy policy</a>.
        Questions: <a href="mailto:info@icomplypropertyservices.co.uk" class="text-[#ff6b00] font-semibold">info@icomplypropertyservices.co.uk</a>
        or <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="text-[#ff6b00] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>.
      </div>

      <button type="submit" class="w-full md:w-auto px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white text-lg shadow-lg">
        Save my renewal dates
      </button>
    </form>
  </div>
</section>

<script>
(function () {
  var list = document.getElementById('property-list');
  var btn = document.getElementById('add-property');
  if (!list || !btn) return;
  btn.addEventListener('click', function () {
    var blocks = list.querySelectorAll('.property-block');
    var first = blocks[0];
    if (!first) return;
    var clone = first.cloneNode(true);
    var idx = blocks.length;
    clone.setAttribute('data-property-index', String(idx));
    var legend = clone.querySelector('legend');
    if (legend) legend.textContent = 'Property ' + (idx + 1);
    clone.querySelectorAll('input').forEach(function (input) {
      if (input.type === 'date' || input.type === 'text') input.value = '';
      if (input.hasAttribute('required') && idx > 0 && (input.name.indexOf('address') !== -1 || input.name.indexOf('postcode') !== -1)) {
        /* keep required on address/postcode for every property */
      }
    });
    var remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'mt-3 text-sm font-semibold text-red-700 underline';
    remove.textContent = 'Remove this property';
    remove.addEventListener('click', function () { clone.remove(); renumber(); });
    clone.appendChild(remove);
    list.appendChild(clone);
  });
  function renumber() {
    list.querySelectorAll('.property-block').forEach(function (el, i) {
      el.setAttribute('data-property-index', String(i));
      var legend = el.querySelector('legend');
      if (legend) legend.textContent = 'Property ' + (i + 1);
    });
  }
})();
</script>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
