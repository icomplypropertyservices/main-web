<?php
/**
 * Resource — Legionella / water hygiene (UK guidance, not legal advice). POA.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';

$pageTitle = 'Legionella Risk Assessment Guide | Water Hygiene';
$metaDesc = 'Plain-English Legionella risk assessment and water hygiene testing for UK landlords and workplaces. HSE L8 / HSG274 language. POA. Not legal advice.';
$metaKeywords = 'legionella risk assessment, water hygiene, Legionnaires disease, landlord legionella, Stockport, Manchester';
$ogImage = url('/assets/images/services/plumbing.jpg');
$canonicalUrl = url('/pages/resources/legionella-risk-assessment.php');
$services = getServices();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
?>
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a> / <a href="<?= url('/pages/resources') ?>" class="hover:text-white">Resources</a> / <span class="text-white/80">Legionella</span>
        </nav>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Legionella risk assessment<br><span class="text-[#ff6b00]">&amp; water hygiene</span></h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">What a dutyholder assessment covers, when sampling helps, and how we quote (POA only).</p>
    </div>
</section>
<article class="max-w-3xl mx-auto px-6 py-12 space-y-8">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950">
        <strong>Not legal advice.</strong> Duties follow current HSE guidance (ACOP L8 and HSG274). We do not invent accreditations or prices on this page.
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">What it is</h2>
        <p class="text-zinc-700">A Legionella risk assessment looks at stored and circulated water — tanks, calorifiers, showers, little-used outlets — and records proportionate controls. “Legionnaires’ disease risk assessment” is the same duty under a different search name.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Testing</h2>
        <p class="text-zinc-700">Water samples are not automatic. Assessment comes first. Sampling is useful on some complex or stored-water systems and is quoted separately as POA if we recommend it. This page does not claim a named laboratory badge.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Who typically asks</h2>
        <ul class="space-y-2 text-zinc-700">
            <li>Landlords and agents with tanks, unused en-suites or shared systems</li>
            <li>Workplaces, blocks and care settings with plant rooms</li>
            <li>Simple combi-fed houses are often lower risk — we still write that down if you ask us to assess</li>
        </ul>
    </div>
    <div class="flex flex-wrap gap-2">
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/services/legionella-risk-assessment') ?>">Service page</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/legionella-risk-assessment') ?>">Keyword hub</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/landlord-legionella-risk-assessment') ?>">Landlord RA</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/contact') ?>">POA quote</a>
    </div>
    <div class="bg-[#0a2540] text-white p-8 rounded-3xl text-center">
        <h2 class="text-2xl font-semibold mb-3">Request a POA quote</h2>
        <p class="text-white/85 mb-6">Postcode, property type and whether you have stored water. No catalogue fee.</p>
        <a href="<?= url('/contact') ?>" class="bg-[#ff6b00] px-8 py-3.5 rounded-2xl font-semibold inline-block">Contact</a>
    </div>
    <?= resourceRelatedHtml('legionella-risk-assessment') ?>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
