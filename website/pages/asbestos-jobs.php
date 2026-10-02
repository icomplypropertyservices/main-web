<?php
/**
 * Asbestos survey and awareness job lane.
 * Survey visits and dutyholder briefings only. POA. Licensed removal is by others.
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/share.php';

$lanes = asbestosJobsByLane();
$surveyJobs = $lanes['survey'];
$awarenessJobs = $lanes['awareness'];
$pageTitle = 'Asbestos survey and awareness jobs';
$metaDesc = 'Asbestos survey and awareness job pages for North West dutyholders. Management, refurbishment and briefings. POA. Licensed removal is by others. Not an accredited training certificate.';
$metaKeywords = 'asbestos survey, asbestos awareness briefing, asbestos management survey, Stockport, Manchester, POA';
$ogImage = url('/assets/images/services/asbestos-survey.jpg');
$canonicalUrl = url('/pages/asbestos-jobs.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';

$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
$card = static function (array $job) use ($h): void {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $name = (string)($job['name'] ?? keywordDisplayName($slug));
    $intro = (string)($job['intro'] ?? '');
    ?>
    <a href="<?= $h(url('/pages/keywords/' . $slug)) ?>" class="block bg-white border border-zinc-200 rounded-2xl p-5 hover:border-[#ff6b00]">
        <h3 class="font-semibold text-[#0B1F3A]"><?= $h($name) ?></h3>
        <p class="mt-2 text-sm text-zinc-600 leading-relaxed"><?= $h($intro) ?></p>
    </a>
    <?php
};
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-16">
        <nav class="text-xs text-white/60 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span class="text-white/40"> / </span>
            <a href="<?= url('/pages/services/asbestos-survey') ?>" class="hover:text-white">Asbestos survey</a>
            <span class="text-white/40"> / </span>
            <span class="text-white">Job pages</span>
        </nav>
        <p class="text-xs uppercase tracking-[0.2em] text-[#ff6b00] font-semibold">Survey lane · Awareness lane</p>
        <h1 class="mt-3 text-4xl sm:text-5xl font-semibold tracking-tight max-w-3xl">Asbestos survey and awareness</h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">Two job lanes for dutyholders in Greater Manchester and the North West. Surveys record materials. Awareness briefings tell people when to stop. Price on application. Licensed asbestos removal is by others.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#survey-lane" class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold text-white">Survey lane</a>
            <a href="#awareness-lane" class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Awareness lane</a>
            <a href="<?= url('/contact') ?>" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold">Enquire for POA</a>
        </div>
    </div>
</section>

<article class="max-w-7xl mx-auto px-6 py-12 space-y-12">
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-950 max-w-3xl">
        <p>These pages do not offer licensed asbestos removal. An awareness briefing is not an accredited training certificate and does not replace a management or refurbishment survey. Quotes are POA. This is not legal advice.</p>
    </div>

    <section id="survey-lane">
        <h2 class="text-3xl font-semibold text-[#0B1F3A]">Survey lane</h2>
        <p class="mt-2 text-zinc-600 max-w-3xl">Management surveys, refurbishment and demolition liaison, reinspections, sampling inside a scoped visit, and register updates. <?= count($surveyJobs) ?> pages.</p>
        <div class="mt-6 grid md:grid-cols-2 gap-4">
            <?php foreach ($surveyJobs as $job) { $card($job); } ?>
        </div>
    </section>

    <section id="awareness-lane">
        <h2 class="text-3xl font-semibold text-[#0B1F3A]">Awareness lane</h2>
        <p class="mt-2 text-zinc-600 max-w-3xl">Site briefings for landlords, agents, facilities teams and contractors who might disturb finishes. <?= count($awarenessJobs) ?> pages. Each one sits beside a survey.</p>
        <div class="mt-6 grid md:grid-cols-2 gap-4">
            <?php foreach ($awarenessJobs as $job) { $card($job); } ?>
        </div>
    </section>

    <section class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">
        <h2 class="text-2xl font-semibold">Enquire for a POA quote</h2>
        <p class="mt-3 text-white/80 max-w-2xl">Tell us the building age, whether it is occupied, and if walls are coming open. Survey and awareness are scoped separately. Licensed asbestos removal is by others.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= url('/contact') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold">Contact</a>
            <a href="<?= url('/pages/services/asbestos-survey') ?>" class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Asbestos service</a>
            <a href="<?= url('/pages/asbestos-landlords') ?>" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold">Landlord hub</a>
            <a href="<?= url('/pages/resources/asbestos-survey') ?>" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold">Guide</a>
        </div>
    </section>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
