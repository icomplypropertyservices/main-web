<?php
/**
 * Resource — asbestos survey / testing (UK guidance, not legal advice). POA.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';

$pageTitle = 'Asbestos Survey Guide | Testing & Duty to Manage';
$metaDesc = 'Plain-English asbestos management and refurbishment surveys for UK dutyholders. CAR 2012. POA. Licensed removal is by others. Not legal advice.';
$metaKeywords = 'asbestos survey, asbestos testing, management survey, refurbishment survey, Stockport, Manchester';
$ogImage = url('/assets/images/services/building-surveys.jpg');
$canonicalUrl = url('/pages/resources/asbestos-survey.php');
$services = getServices();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
?>
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a> / <a href="<?= url('/pages/resources') ?>" class="hover:text-white">Resources</a> / <span class="text-white/80">Asbestos</span>
        </nav>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Asbestos survey<br><span class="text-[#ff6b00]">&amp; testing</span></h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">Management versus refurbishment surveys, sampling, and honest limits. Price on application.</p>
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
        <h2 class="text-2xl font-semibold mb-3">Testing</h2>
        <p class="text-zinc-700">Sampling suspect material is part of a scoped visit when needed. Do not post debris. Results belong on a register with locations — a lone sample is not a building-wide all-clear.</p>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-3">What we do not do here</h2>
        <p class="text-zinc-700">Licensed asbestos removal is not this service. If the survey says removal is required, appoint a suitable licensed contractor. Quotes for surveys are POA after age, access and planned works are known.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/services/asbestos-survey') ?>">Service page</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/asbestos-survey') ?>">Keyword hub</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/pages/keywords/asbestos-management-survey') ?>">Management survey</a>
        <a class="px-4 py-2 bg-white border rounded-full text-sm" href="<?= url('/contact') ?>">POA quote</a>
    </div>
    <div class="bg-[#0a2540] text-white p-8 rounded-3xl text-center">
        <h2 class="text-2xl font-semibold mb-3">Request a POA quote</h2>
        <p class="text-white/85 mb-6">Building age, floor area, access and whether walls are coming open.</p>
        <a href="<?= url('/contact') ?>" class="bg-[#ff6b00] px-8 py-3.5 rounded-2xl font-semibold inline-block">Contact</a>
    </div>
    <?= resourceRelatedHtml('asbestos-survey') ?>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
