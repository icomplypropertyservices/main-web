<?php
/**
 * Resource — asbestos survey / testing for landlords (UK guidance, not legal advice). POA.
 * Source briefs (service-asbestos-landlords.md / seo-outlines) were not mounted;
 * copy follows CAR 2012 duty to manage only — no invented prices, certs or reviews.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';

$pageTitle = 'Asbestos Survey for Landlords | Testing & Duty to Manage';
$metaDesc = 'Plain-English asbestos management and refurbishment surveys for UK landlords and dutyholders. CAR 2012. POA. Licensed removal is by others. Not legal advice.';
$metaKeywords = 'asbestos survey, landlord asbestos, asbestos testing, management survey, refurbishment survey, Stockport, Manchester';
$ogImage = url('/assets/images/services/building-surveys.jpg');
$canonicalUrl = url('/pages/resources/asbestos-survey.php');
$services = getServices();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a> / <a href="<?= url('/pages/resources') ?>" class="hover:text-white">Resources</a> / <span class="text-white/80">Asbestos</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#FF6B00]"></span>
            Landlords · Duty to manage · Resource guide
        </div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Asbestos survey<br><span class="text-[#FF6B00]">for landlords</span></h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">Management versus refurbishment surveys, sampling, and honest limits. Price on application. Licensed removal is by others.</p>
    </div>
</section>
<article class="max-w-3xl mx-auto px-6 py-12 space-y-8">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950">
        <strong>Not legal advice.</strong> The Control of Asbestos Regulations 2012 sit behind the duty to manage. We do not claim a UKAS, BOHS or HSE licence on this page. Licensed removal is by others.
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Survey types</h2>
        <p class="text-zinc-700">A <strong>management survey</strong> is for normal occupation and maintenance access. A <strong>refurbishment or demolition survey</strong> is more intrusive and is used before strip-out or opening-up. We will not sell one as if it were the other.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Common parts versus a single let</h2>
        <p class="text-zinc-700">A single private house that is purely domestic is a different duty from a house converted to flats with shared halls. Landlords of non-domestic parts and many blocks need to manage asbestos in common areas. Inside a tenanted dwelling, access and type of survey still follow the planned works. Not every rented terrace automatically needs a full commercial-style survey.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Testing</h2>
        <p class="text-zinc-700">Sampling suspect material is part of a scoped visit when needed. Do not post debris. Results belong on a register with locations — a lone sample is not a building-wide all-clear. Analysis method is confirmed at quote time. We do not advertise a consumer kit price.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">What we do not do here</h2>
        <p class="text-zinc-700">Licensed asbestos removal is not this service. If the survey says removal is required, appoint a suitable licensed contractor. Re-inspections check condition of items already on a register. A first visit to an unknown building is usually a survey, not a ten-minute glance. Quotes for surveys are POA after age, access and planned works are known.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">Common questions</h2>
        <div class="space-y-3">
            <details class="bg-white border rounded-2xl p-5"><summary class="font-semibold cursor-pointer">Which survey do I need?</summary><p class="mt-3 text-sm text-zinc-600">A management survey is for normal occupation. A refurbishment or demolition survey is for intrusive works. Tell us the planned work.</p></details>
            <details class="bg-white border rounded-2xl p-5"><summary class="font-semibold cursor-pointer">Do you remove asbestos?</summary><p class="mt-3 text-sm text-zinc-600">Not as a licensed removal contractor on this page. If removal is required we say so and you appoint a suitable licensed contractor.</p></details>
            <details class="bg-white border rounded-2xl p-5"><summary class="font-semibold cursor-pointer">What does it cost?</summary><p class="mt-3 text-sm text-zinc-600">POA. Size, age, access and how intrusive the survey must be all change the quote. We do not publish a fake starting price.</p></details>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/asbestos-jobs') ?>">Survey and awareness jobs</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/services/asbestos-survey') ?>">Service page</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/asbestos-landlords') ?>">Landlord hub</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/asbestos-survey') ?>">Keyword hub</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/asbestos-management-survey') ?>">Management survey</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/contact') ?>">POA quote</a>
    </div>
    <div class="bg-[#0B1F3A] text-white p-8 rounded-3xl text-center">
        <h2 class="text-2xl font-semibold mb-3">Request a POA quote</h2>
        <p class="text-white/85 mb-6">Building age, floor area, access and whether walls are coming open.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="<?= url('/contact') ?>" class="bg-[#FF6B00] px-8 py-3.5 rounded-2xl font-semibold inline-block">Contact</a>
            <a href="tel:07517806082" class="bg-white text-[#0B1F3A] px-8 py-3.5 rounded-2xl font-semibold inline-block">Call 07517806082</a>
            <a href="https://wa.me/447517806082" target="_blank" rel="noopener" class="border border-white/40 px-8 py-3.5 rounded-2xl font-semibold inline-block">WhatsApp</a>
        </div>
    </div>
    <?= resourceRelatedHtml('asbestos-survey') ?>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
