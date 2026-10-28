<?php
/**
 * Success page after Netlify Form "renewals" submit.
 */
require_once __DIR__ . '/../config.php';

$pageTitle = 'Renewal dates saved — thank you | iComply';
$metaDesc = 'Thanks for logging your certificate and service renewal dates with iComply. We will email a reminder before each one is due.';
$metaRobots = 'index, follow'; // Jack 2026-10-19: no page is ever noindexed
$canonicalUrl = url('/renewals/thanks.php');
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
  <div class="relative max-w-3xl mx-auto px-6 py-16 md:py-20 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-emerald-500/20 border border-emerald-400/40 text-3xl mb-6" aria-hidden="true">✓</div>
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-3">Dates logged</div>
    <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">Thank you — your renewal dates are with us.</h1>
    <p class="mt-5 text-lg md:text-xl text-white/80 max-w-xl mx-auto">
      We'll email a reminder before each certificate or service is due, based on the preference you chose. No spam.
    </p>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
      <a href="<?= url('/renewals.php') ?>" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Log another property</a>
      <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Call <?= htmlspecialchars(icomplyPhoneDisplay(), ENT_QUOTES, 'UTF-8') ?></a>
      <a href="<?= url('/') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Back to home</a>
    </div>
  </div>
</section>
<section class="bg-white border-b">
  <div class="max-w-3xl mx-auto px-6 py-12 text-center text-zinc-600">
    <p>Need a quote for an upcoming renewal? Email <a class="text-[#ff6b00] font-semibold" href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a> or
    <a class="text-[#ff6b00] font-semibold" href="https://icomplypropertyservices.co.uk/assets/contact/icomply-property-services.vcf">save our contact card</a>.</p>
  </div>
</section>
<section class="bg-zinc-50 border-b">
  <div class="max-w-3xl mx-auto px-6 py-12 text-zinc-700 space-y-5 text-left">
    <h2 class="text-2xl font-semibold text-[#0B1F3A]">What happens next</h2>
    <p>Your dates are now on our renewals list. A few weeks before each one is due we send a short email reminder with the property address, the certificate or service involved and the date it runs out, so you have time to book a visit without a gap in cover. If you chose a phone reminder as well, we will call from 07517 806082.</p>
    <h2 class="text-2xl font-semibold text-[#0B1F3A]">Typical renewal intervals for landlords and managing agents</h2>
    <ul class="list-disc pl-6 space-y-2">
      <li><strong>Landlord gas safety record (CP12):</strong> every 12 months, carried out by Gas Safe registered engineers.</li>
      <li><strong>EICR (electrical installation condition report):</strong> at least every five years for private rented homes, or sooner if the last report says so.</li>
      <li><strong>Fire alarm servicing:</strong> usually every six months under BS 5839, with weekly call point tests done on site.</li>
      <li><strong>Emergency lighting:</strong> monthly function checks and a full-duration test each year.</li>
      <li><strong>Fire risk assessment:</strong> review it at least yearly, and whenever the building, layout or occupancy changes.</li>
      <li><strong>HMO licence:</strong> normally up to five years, and the council will expect current gas, electrical and fire paperwork when you renew.</li>
    </ul>
    <h2 class="text-2xl font-semibold text-[#0B1F3A]">Want us to book the visit for you?</h2>
    <p>Reply to any reminder, or contact us now, and we will give you a free written quote and a date that suits your tenants. One visit can often cover several certificates at the same property, which saves arranging access more than once. Every report is sent to you in writing after the visit, ready for your records or your letting agent.</p>
  </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
