<?php
/**
 * Netlify Forms onboarding form for /become-a-subcontractor.
 * Field names are fixed so Netlify's HTML parser and the live page match.
 */
declare(strict_types=1);

function icomplySubcontractorFormMarkup(): string
{
    $privacy = htmlspecialchars(url('/privacy.php'), ENT_QUOTES, 'UTF-8');
    $input = 'w-full border border-zinc-200 px-5 py-3.5 rounded-2xl bg-white';
    $label = 'block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-1.5';
    $trades = [
        'Electrical / EICR',
        'Fire alarms',
        'Emergency lighting',
        'AOV smoke vents',
        'CCTV / access control',
        'Nurse call',
        'General maintenance',
    ];
    $tradeHtml = '';
    foreach ($trades as $i => $trade) {
        $safe = htmlspecialchars($trade, ENT_QUOTES, 'UTF-8');
        $id = 'sub-trade-' . $i;
        $tradeHtml .= '<label class="choice text-sm" for="' . $id . '">'
            . '<input id="' . $id . '" type="checkbox" name="trades" value="' . $safe . '">'
            . '<span>' . $safe . '</span></label>';
    }

    return <<<HTML
<form name="subcontractor-onboarding" method="POST" action="/thank-you" enctype="multipart/form-data" data-netlify="true" netlify-honeypot="bot-field" class="sub-form bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 space-y-5 shadow-sm" aria-labelledby="onboarding-heading">
  <input type="hidden" name="form-name" value="subcontractor-onboarding">
  <p class="sr-only">
    <label>Leave this field blank <input name="bot-field" tabindex="-1" autocomplete="off"></label>
  </p>
  <div id="sub-errors" class="form-errors" role="alert" tabindex="-1" hidden></div>
  <div>
    <label for="sub-name" class="{$label}">Name</label>
    <input id="sub-name" name="name" type="text" required maxlength="120" autocomplete="name" class="{$input}">
  </div>
  <fieldset class="space-y-1">
    <legend class="{$label}">Business type</legend>
    <label class="choice text-sm" for="sub-biz-company"><input id="sub-biz-company" type="radio" name="business_type" value="company" required> Company</label>
    <label class="choice text-sm" for="sub-biz-sole"><input id="sub-biz-sole" type="radio" name="business_type" value="sole_trader" required> Sole trader</label>
  </fieldset>
  <div>
    <label for="sub-company" class="{$label}">Company name</label>
    <input id="sub-company" name="company_name" type="text" maxlength="160" autocomplete="organization" class="{$input}">
  </div>
  <fieldset class="space-y-1" aria-describedby="sub-trades-help">
    <legend class="{$label}" id="sub-trades-legend">Trades</legend>
    <p id="sub-trades-help" class="text-sm text-zinc-600">Select every trade you can carry out.</p>
    <div class="grid sm:grid-cols-2 gap-1">{$tradeHtml}</div>
  </fieldset>
  <div>
    <label for="sub-quals" class="{$label}">Qualifications</label>
    <textarea id="sub-quals" name="qualifications" rows="4" class="{$input}" placeholder="Qualifications and cards, for example 18th Edition, ECS/CSCS, FIA"></textarea>
  </div>
  <fieldset class="space-y-1">
    <legend class="{$label}">Public liability insurance</legend>
    <label class="choice text-sm" for="sub-insured-yes"><input id="sub-insured-yes" type="radio" name="insured" value="yes" required> Yes</label>
    <label class="choice text-sm" for="sub-insured-no"><input id="sub-insured-no" type="radio" name="insured" value="no" required> No</label>
  </fieldset>
  <div>
    <label for="sub-expiry" class="{$label}">Insurance expiry</label>
    <input id="sub-expiry" name="insurance_expiry" type="date" class="{$input}">
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="sub-postcodes" class="{$label}">Coverage postcodes</label>
      <input id="sub-postcodes" name="coverage_postcodes" type="text" maxlength="240" autocomplete="postal-code" class="{$input}">
    </div>
    <div>
      <label for="sub-radius" class="{$label}">Travel radius (miles)</label>
      <input id="sub-radius" name="travel_radius" type="number" min="0" max="500" inputmode="numeric" class="{$input}">
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="sub-phone" class="{$label}">Phone</label>
      <input id="sub-phone" name="phone" type="tel" required maxlength="40" autocomplete="tel" inputmode="tel" class="{$input}">
    </div>
    <div>
      <label for="sub-email" class="{$label}">Email</label>
      <input id="sub-email" name="email" type="email" required maxlength="160" autocomplete="email" inputmode="email" class="{$input}">
    </div>
  </div>
  <div>
    <label for="sub-availability" class="{$label}">Availability</label>
    <textarea id="sub-availability" name="availability" rows="3" class="{$input}" placeholder="Days or areas you can cover"></textarea>
  </div>
  <div>
    <label for="sub-docs" class="{$label}">Documents (optional)</label>
    <input id="sub-docs" name="documents" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" aria-describedby="sub-docs-help" class="block w-full text-sm">
    <p id="sub-docs-help" class="mt-2 text-xs text-zinc-500">Optional. PDF, JPG or PNG. Maximum 8MB.</p>
  </div>
  <label class="choice text-sm" for="sub-gdpr">
    <input id="sub-gdpr" type="checkbox" name="gdpr_consent" value="yes" required>
    <span>I agree that iComply Property Services may use these details and any uploaded documents to vet this application and assign work.</span>
  </label>
  <p class="text-sm">See the <a href="{$privacy}" class="form-legal">privacy notice</a>.</p>
  <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit application</button>
</form>
HTML;
}
