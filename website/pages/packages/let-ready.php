<?php
/** Wave 1 package — Let Ready. Structure first. Copy via Marketing/CoS. POA only. */
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Let Ready Package | Landlord compliance';
$metaDesc = 'Let Ready package for landlords and voids: EICR, gas, smoke & CO, optional PAT and domestic EPC. POA after scope. Copy via Marketing/CoS.';
$canonicalUrl = url('/pages/packages/let-ready.php');
$ogImage = url('/assets/images/services/electrical.jpg');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
  <div class="relative max-w-7xl mx-auto px-6 py-14">
    <nav class="text-xs text-white/50 mb-6"><a href="<?= rtrim(SITE_URL,'/') ?>/" class="hover:text-white">Home</a> / <a href="<?= url('/pages/packages.php') ?>" class="hover:text-white">Packages</a> / Let Ready</nav>
    <p class="text-xs uppercase tracking-widest text-white/60 mb-3">Domestic · Wave 1</p>
    <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Let Ready <span class="text-[#ff6b00]">package</span></h1>
    <p class="mt-4 text-white/80 max-w-2xl">Landlord / void compliance pack so a property can be let with core certificates in one visit plan. Price: <strong>POA</strong> after scope. Copy via Marketing/CoS.</p>
    <a href="#quote" class="inline-block mt-8 px-8 py-4 rounded-2xl bg-[#ff6b00] font-semibold">Request quote</a>
  </div>
</section>
<section class="max-w-7xl mx-auto px-6 py-16">
  <h2 class="text-3xl font-semibold">What is included (indicative)</h2>
  <p class="mt-2 text-zinc-600">Final scope is confirmed in your quote. No invented catalogue prices.</p>
  <ul class="mt-6 space-y-2 text-zinc-800">
    <li>✓ EICR (as required for the tenancy)</li>
    <li>✓ Gas safety certificate where a gas supply is present</li>
    <li>✓ Smoke &amp; CO alarm check / install as required</li>
    <li>✓ Optional PAT for furnished lets</li>
    <li>✓ Optional domestic EPC</li>
    <li>✓ Single documentation pack for agent / landlord files</li>
  </ul>
  <div class="mt-8 flex flex-wrap gap-2">
    <a class="px-4 py-2 border rounded-full text-sm" href="<?= url('/pages/services/electrical.php') ?>">Electrical</a>
    <a class="px-4 py-2 border rounded-full text-sm" href="<?= url('/pages/services/gas-systems.php') ?>">Gas</a>
    <a class="px-4 py-2 border rounded-full text-sm" href="<?= url('/pages/services/smoke-co-alarms.php') ?>">Smoke &amp; CO</a>
    <a class="px-4 py-2 border rounded-full text-sm" href="<?= url('/pages/services/pat-testing.php') ?>">PAT</a>
    <a class="px-4 py-2 border rounded-full text-sm" href="<?= url('/pages/services/epc.php') ?>">EPC</a>
  </div>
</section>
<section id="quote" class="bg-zinc-50 border-t"><div class="max-w-3xl mx-auto px-6 py-16">
  <h2 class="text-3xl font-semibold text-center">Quote Let Ready</h2>
  <?= icomplyQuoteFormOpen('mt-8 bg-white border rounded-3xl p-6 space-y-4') ?>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="text" name="name" placeholder="Full name" required class="w-full border px-5 py-3.5 rounded-2xl">
    <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
    <input type="tel" name="phone" placeholder="Phone" required class="w-full border px-5 py-3.5 rounded-2xl">
    <input type="text" name="service" value="Let Ready package" readonly class="w-full border px-5 py-3.5 rounded-2xl bg-zinc-50">
    <textarea name="message" rows="4" required placeholder="Postcode, number of properties…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
    <button class="w-full modern-btn text-white py-4 rounded-2xl font-semibold">Submit</button>
  </form>
</div></section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
