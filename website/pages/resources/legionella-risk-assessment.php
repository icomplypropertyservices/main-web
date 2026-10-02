<?php
/**
 * Resource — Legionella / water hygiene for landlords (UK guidance, not legal advice). POA.
 * Source briefs (service-legionella-landlords.md / seo-outlines) were not mounted;
 * copy follows HSE L8 / HSG274 landlord duties only — no invented prices, certs or reviews.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';

$pageTitle = 'Legionella Risk Assessment for Landlords | Water Hygiene';
$metaDesc = 'Plain-English Legionella risk assessment for UK landlords and workplaces. HSE L8 / HSG274. Sampling only when justified. POA from Stockport. Not legal advice.';
$metaKeywords = 'legionella risk assessment, landlord legionella, water hygiene, Legionnaires disease, water risk assessment, Stockport, Manchester';
$ogImage = url('/assets/images/services/plumbing.jpg');
$canonicalUrl = url('/pages/resources/legionella-risk-assessment.php');
$services = getServices();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a> / <a href="<?= url('/pages/resources') ?>" class="hover:text-white">Resources</a> / <span class="text-white/80">Legionella</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#FF6B00]"></span>
            Landlords · Water hygiene · Resource guide
        </div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Legionella risk assessment<br><span class="text-[#FF6B00]">for landlords</span></h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">What a dutyholder assessment covers, when sampling helps, and how we quote (POA only). Not a published annual-test product.</p>
    </div>
</section>
<article class="max-w-3xl mx-auto px-6 py-12 space-y-8">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950">
        <strong>Not legal advice.</strong> Duties follow current HSE guidance (ACOP L8 and HSG274). We do not invent accreditations, prices or reviews on this page.
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">What it is</h2>
        <p class="text-zinc-700">A Legionella risk assessment looks at stored and circulated water — tanks, calorifiers, showers, little-used outlets — and records proportionate controls. “Legionnaires’ disease risk assessment” and “water risk assessment” are the same duty under different search names.</p>
        <p class="text-zinc-700 mt-3">Landlords remain responsible for considering that risk in rented homes. A combi-fed house with no stored cold-water tank is often lower risk than a property with tanks, unused en-suites or a shared system. We still write that down if you ask us to assess.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">What a visit typically records</h2>
        <ul class="space-y-2 text-zinc-700">
            <li class="flex gap-2"><span class="text-[#FF6B00] font-bold shrink-0">✓</span> <span>How water is stored and circulated, and who might be exposed</span></li>
            <li class="flex gap-2"><span class="text-[#FF6B00] font-bold shrink-0">✓</span> <span>Temperature, storage and little-used outlet notes</span></li>
            <li class="flex gap-2"><span class="text-[#FF6B00] font-bold shrink-0">✓</span> <span>Who is responsible for flushing or temperature checks if those are the agreed controls</span></li>
            <li class="flex gap-2"><span class="text-[#FF6B00] font-bold shrink-0">✓</span> <span>A written record for the management file — not a medical “all-clear”</span></li>
        </ul>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Testing</h2>
        <p class="text-zinc-700">Water samples are not automatic. Assessment comes first. Sampling is useful on some complex or stored-water systems and is quoted separately as POA if we recommend it. A result is evidence at a point in time. This page does not claim a named laboratory badge.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Who typically asks</h2>
        <ul class="space-y-2 text-zinc-700">
            <li>Landlords and agents with tanks, unused en-suites or shared systems</li>
            <li>Workplaces, blocks and care settings with plant rooms</li>
            <li>Simple combi-fed houses that still need a written lower-risk note for the file</li>
        </ul>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Review frequency</h2>
        <p class="text-zinc-700">Review when the system or occupancy changes, and at an interval that matches risk — not a date invented for marketing. Many landlords book water hygiene in the same conversation as an EICR or gas safety record. Each item is still scoped and POA.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Common questions</h2>
        <div class="space-y-3">
            <details class="bg-white border rounded-2xl p-5"><summary class="font-semibold cursor-pointer">Do landlords always need a Legionella risk assessment?</summary><p class="mt-3 text-sm text-zinc-600">HSE expects dutyholders to consider Legionella risk. Many simple domestic systems are lower risk, but rented and commercial sites still need a suitable assessment. We confirm what is appropriate after you describe the property.</p></details>
            <details class="bg-white border rounded-2xl p-5"><summary class="font-semibold cursor-pointer">Do you always take water samples?</summary><p class="mt-3 text-sm text-zinc-600">No. Assessment comes first. Sampling is useful on some stored-water or complex systems and is quoted separately as POA if recommended.</p></details>
            <details class="bg-white border rounded-2xl p-5"><summary class="font-semibold cursor-pointer">What does it cost?</summary><p class="mt-3 text-sm text-zinc-600">Price on application. Outlets, tanks, access and whether sampling is needed all change the work. We do not publish a made-up price.</p></details>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/services/legionella-risk-assessment') ?>">Service page</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/legionella-landlords') ?>">Landlord hub</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/legionella-risk-assessment') ?>">Keyword hub</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/landlord-legionella-risk-assessment') ?>">Landlord RA keyword</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/contact') ?>">POA quote</a>
    </div>
    <div class="bg-[#0B1F3A] text-white p-8 rounded-3xl text-center">
        <h2 class="text-2xl font-semibold mb-3">Request a POA quote</h2>
        <p class="text-white/85 mb-6">Postcode, property type and whether you have stored water. No catalogue fee.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="<?= url('/contact') ?>" class="bg-[#FF6B00] px-8 py-3.5 rounded-2xl font-semibold inline-block">Contact</a>
            <a href="tel:07517806082" class="bg-white text-[#0B1F3A] px-8 py-3.5 rounded-2xl font-semibold inline-block">Call 07517806082</a>
            <a href="https://wa.me/447517806082" target="_blank" rel="noopener" class="border border-white/40 px-8 py-3.5 rounded-2xl font-semibold inline-block">WhatsApp</a>
        </div>
    </div>
    <?= resourceRelatedHtml('legionella-risk-assessment') ?>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
