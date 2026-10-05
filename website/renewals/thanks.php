<?php
/**
 * Success page after Netlify Form "renewals" submit.
 */
require_once __DIR__ . '/../config.php';

$pageTitle = 'Renewal dates saved — thank you | iComply';
$metaDesc = 'Thanks for logging your certificate and service renewal dates with iComply. We will email a reminder before each one is due.';
$metaRobots = 'noindex, follow';
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
      <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
      <a href="<?= url('/') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Back to home</a>
    </div>
  </div>
</section>
<section class="bg-white border-b">
  <div class="max-w-3xl mx-auto px-6 py-12 text-center text-zinc-600">
    <p>Need a quote for an upcoming renewal? Email <a class="text-[#ff6b00] font-semibold" href="mailto:info@icomplypropertyservices.co.uk">info@icomplypropertyservices.co.uk</a> or
    <a class="text-[#ff6b00] font-semibold" href="https://icomplypropertyservices.co.uk/assets/contact/icomply-property-services.vcf">save our contact card</a>.</p>
  </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
