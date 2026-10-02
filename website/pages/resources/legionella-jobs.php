<?php
/**
 * Legionella / water hygiene job lane index.
 * Every job is POA. No catalogue fees, laboratory badges or medical claims.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/resource-related.php';

$jobs = function_exists('legionellaJobTypesJobs') ? legionellaJobTypesJobs() : [];
$groups = function_exists('legionellaJobGroups') ? legionellaJobGroups() : [];
$byGroup = [];
foreach ($jobs as $job) {
    if (!is_array($job)) {
        continue;
    }
    $key = (string)($job['group'] ?? '');
    $byGroup[$key][] = $job;
}

$pageTitle = 'Legionella Job Lane | Water Hygiene POA';
$metaDesc = 'Legionella and water hygiene jobs across the North West: assessments, tanks, temperatures, flushing, sampling and premises visits. Price on application from Stockport. No catalogue fees.';
$metaKeywords = 'legionella risk assessment, water hygiene jobs, tank inspection, sentinel temperature, HSE L8, HSG274, Stockport, Manchester, POA';
$ogImage = url('/assets/images/services/plumbing.jpg');
$canonicalUrl = url('/pages/resources/legionella-jobs.php');
$services = getServices();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require SITE_ROOT . '/includes/header.php';
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-12 md:py-16">
        <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            /
            <a href="<?= url('/pages/resources') ?>" class="hover:text-white">Resources</a>
            /
            <span class="text-white/80">Legionella jobs</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#FF6B00]"></span>
            Water hygiene · <?= count($jobs) ?> jobs · POA
        </div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter max-w-3xl">Legionella and water hygiene <span class="text-[#FF6B00]">job lane</span></h1>
        <p class="mt-5 text-lg text-white/80 max-w-2xl">Assessments, tanks, temperatures, flushing, sampling and premises visits. HSE L8 and HSG274 are the usual references. Every item is price on application after we know the system. We do not publish a fee, a laboratory badge or a medical opinion.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= url('/pages/services/legionella-risk-assessment') ?>" class="px-5 py-3 rounded-2xl bg-[#FF6B00] font-semibold">Service page</a>
            <a href="<?= url('/pages/legionella-landlords') ?>" class="px-5 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Landlord hub</a>
            <a href="<?= url('/contact') ?>" class="px-5 py-3 rounded-2xl border border-white/40 font-semibold">POA quote</a>
        </div>
    </div>
</section>

<article class="max-w-7xl mx-auto px-6 py-12 space-y-10">
    <p class="text-zinc-700 max-w-3xl">Sampling, tank cleaning and any chemical step are separate lines, and only when the system needs them. A temperature or a sample is evidence on the day. It is not a certificate that the building stays safe, and it is not legal advice.</p>
    <?php if (!$jobs): ?>
        <p class="text-zinc-700">The job list is not loaded.</p>
    <?php endif; ?>
    <?php foreach ($groups as $group):
        $items = $byGroup[$group['key']] ?? [];
        if (!$items) {
            continue;
        }
        usort($items, static function ($a, $b) {
            return strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
        });
    ?>
    <section>
        <h2 class="text-2xl font-semibold text-[#0B1F3A]"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-1 text-sm text-zinc-500"><?= count($items) ?> jobs · price on application</p>
        <ul class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($items as $job):
                $slug = (string)($job['slug'] ?? '');
                $name = (string)($job['name'] ?? $slug);
            ?>
            <li class="border border-zinc-200 rounded-2xl p-4 bg-white">
                <a class="font-semibold text-black hover:text-[#FF6B00]" href="<?= url('/pages/keywords/' . rawurlencode($slug)) ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></a>
                <p class="mt-2 text-sm text-zinc-600">POA after scope. North West from Stockport.</p>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endforeach; ?>
    <?= resourceRelatedHtml('legionella-risk-assessment') ?>
</article>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
