<?php
/**
 * Netlify Forms onboarding form for /become-a-subcontractor.
 * Field names are fixed so Netlify's HTML parser and the live page match.
 */
declare(strict_types=1);

function icomplySubcontractorFormMarkup(): string
{
    $privacy = htmlspecialchars(url('/privacy.php'), ENT_QUOTES, 'UTF-8');
    $input = 'w-full border border-zinc-200 px-5 py-3.5 rounded-2xl bg-white focus:outline-none focus:border-[#ff6b00] focus:ring-1 focus:ring-[#ff6b00]';
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
    foreach ($trades as $trade) {
        $safe = htmlspecialchars($trade, ENT_QUOTES, 'UTF-8');
        $tradeHtml .= '<label class="flex items-start gap-3 text-sm text-zinc-800">'
            . '<input type="checkbox" name="trades" value="' . $safe . '" class="mt-1 accent-[#ff6b00]">'
            . '<span>' . $safe . '</span></label>';
    }

    return <<<HTML
<form name="subcontractor-onboarding" method="POST" action="/thank-you" enctype="multipart/form-data" data-netlify="true" netlify-honeypot="bot-field" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
  <input type="hidden" name="form-name" value="subcontractor-onboarding">
  <p hidden>
    <label>Leave this field blank <input name="bot-field" tabindex="-1" autocomplete="off"></label>
  </p>
  <div>
    <label for="sub-name" class="{$label}">Name</label>
    <input id="sub-name" name="name" type="text" required maxlength="120" autocomplete="name" class="{$input}">
  </div>
  <fieldset class="space-y-2">
    <legend class="{$label}">Business type</legend>
    <label class="flex items-center gap-3 text-sm text-zinc-800"><input type="radio" name="business_type" value="company" required class="accent-[#ff6b00]"> Company</label>
    <label class="flex items-center gap-3 text-sm text-zinc-800"><input type="radio" name="business_type" value="sole_trader" required class="accent-[#ff6b00]"> Sole trader</label>
  </fieldset>
  <div>
    <label for="sub-company" class="{$label}">Company name</label>
    <input id="sub-company" name="company_name" type="text" maxlength="160" autocomplete="organization" class="{$input}">
  </div>
  <fieldset class="space-y-2">
    <legend class="{$label}">Trades</legend>
    <p class="text-sm text-zinc-600">Select every trade you can carry out.</p>
    <div class="grid sm:grid-cols-2 gap-2">{$tradeHtml}</div>
  </fieldset>
  <div>
    <label for="sub-quals" class="{$label}">Qualifications</label>
    <textarea id="sub-quals" name="qualifications" rows="4" class="{$input}" placeholder="Qualifications and cards, for example 18th Edition, ECS/CSCS, FIA"></textarea>
  </div>
  <fieldset class="space-y-2">
    <legend class="{$label}">Public liability insurance</legend>
    <label class="flex items-center gap-3 text-sm text-zinc-800"><input type="radio" name="insured" value="yes" required class="accent-[#ff6b00]"> Yes</label>
    <label class="flex items-center gap-3 text-sm text-zinc-800"><input type="radio" name="insured" value="no" required class="accent-[#ff6b00]"> No</label>
  </fieldset>
  <div>
    <label for="sub-expiry" class="{$label}">Insurance expiry</label>
    <input id="sub-expiry" name="insurance_expiry" type="date" class="{$input}">
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="sub-postcodes" class="{$label}">Coverage postcodes</label>
      <input id="sub-postcodes" name="coverage_postcodes" type="text" maxlength="240" class="{$input}">
    </div>
    <div>
      <label for="sub-radius" class="{$label}">Travel radius (miles)</label>
      <input id="sub-radius" name="travel_radius" type="number" min="0" max="500" inputmode="numeric" class="{$input}">
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="sub-phone" class="{$label}">Phone</label>
      <input id="sub-phone" name="phone" type="tel" required maxlength="40" autocomplete="tel" class="{$input}">
    </div>
    <div>
      <label for="sub-email" class="{$label}">Email</label>
      <input id="sub-email" name="email" type="email" required maxlength="160" autocomplete="email" class="{$input}">
    </div>
  </div>
  <div>
    <label for="sub-availability" class="{$label}">Availability</label>
    <textarea id="sub-availability" name="availability" rows="3" class="{$input}" placeholder="Days or areas you can cover"></textarea>
  </div>
  <div>
    <label for="sub-docs" class="{$label}">Documents (optional)</label>
    <input id="sub-docs" name="documents" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="block w-full text-sm text-zinc-700">
    <p class="mt-2 text-xs text-zinc-500">Optional. PDF, JPG or PNG. Maximum 8MB.</p>
  </div>
  <label class="flex items-start gap-3 text-sm text-zinc-800">
    <input type="checkbox" name="gdpr_consent" value="yes" required class="mt-1 accent-[#ff6b00]">
    <span>I agree that iComply Property Services may use these details and any uploaded documents to vet this application and assign work. See the <a href="{$privacy}" class="text-[#ff6b00] underline">privacy notice</a>.</span>
  </label>
  <button type="submit" class="w-full bg-[#ff6b00] hover:bg-orange-600 text-white py-4 text-lg font-semibold rounded-2xl">Submit application</button>
</form>
HTML;
}

function icomplySubcontractorFormRegistrationDocument(): string
{
    $form = icomplySubcontractorFormMarkup();
    return <<<HTML
<!DOCTYPE html>
<html lang="en-GB">
<head>
  <meta charset="utf-8">
  <title>Onboarding form (Netlify registration)</title>
  <meta name="robots" content="noindex">
  <link rel="canonical" href="https://icomplypropertyservices.co.uk/subcontractor-onboarding-form.html">
</head>
<body>
  <!-- Netlify parses .html at deploy. Pages are published as .php, so this file registers the form. -->
  {$form}
</body>
</html>
HTML;
}
