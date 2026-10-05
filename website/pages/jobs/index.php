<?php
/**
 * Job-type family index. Town pages are /pages/jobs/{job}/{town}.
 */
require_once dirname(__DIR__, 2) . '/config.php';

$pageTitle = 'Job types | iComply Property Services';
$metaDesc = 'Job types arranged from Stockport for Greater Manchester: EICR, fire alarms, landlord gas safety, barriers and related visits. Quotes are price on application.';
$canonicalUrl = url('/pages/jobs');
$metaRobots = 'index, follow';

$catalogueFile = SITE_ROOT . '/includes/matrix-catalogue.php';
if (!function_exists('icomplyMatrixJobRecords') && is_file($catalogueFile)) {
    require_once $catalogueFile;
}
$jobs = function_exists('icomplyMatrixJobRecords') ? icomplyMatrixJobRecords() : [];
foreach (glob(SITE_ROOT . '/pages/jobs/*.php') ?: [] as $file) {
    $slug = basename($file, '.php');
    if ($slug === 'index' || isset($jobs[$slug])) {
        continue;
    }
    $jobs[$slug] = [
        'name' => ucwords(str_replace('-', ' ', $slug)),
        'service' => '',
        'gas' => false,
        'nationwide' => false,
    ];
}
uasort($jobs, static function (array $a, array $b): int {
    return strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
});

$services = function_exists('getServices') ? getServices() : [];
$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#061828] text-white">
    <div class="max-w-5xl mx-auto px-6 py-14">
        <nav class="text-xs text-white/60 mb-4" aria-label="Breadcrumb">
            <a class="hover:text-white" href="<?= $h(rtrim(SITE_URL, '/') . '/') ?>">Home</a>
            <span> / </span>
            <span>Jobs</span>
        </nav>
        <h1 class="text-4xl font-semibold tracking-tight">Job types</h1>
        <p class="mt-4 text-lg text-white/80 max-w-3xl">Each job type has its own guide. Greater Manchester towns have a page for that job. The quote is price on application after the building and the scope are known.</p>
        <p class="mt-3 text-white/70 max-w-3xl">Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.</p>
    </div>
</section>
<main class="max-w-5xl mx-auto px-6 py-12">
    <section class="related-links" aria-label="Job type hubs">
        <h2 class="text-2xl font-semibold text-[#061828]">Job hubs</h2>
        <ul class="mt-6 grid sm:grid-cols-2 gap-3">
            <?php foreach ($jobs as $slug => $job):
                $name = (string)($job['name'] ?? $slug);
                $serviceSlug = (string)($job['service'] ?? '');
                $serviceName = $services[$serviceSlug] ?? '';
                ?>
            <li class="border border-zinc-200 rounded-2xl p-4 bg-white">
                <a class="font-semibold text-[#061828] hover:text-[#ff6b00]" href="<?= $h(url('/pages/jobs/' . $slug)) ?>"><?= $h($name) ?></a>
                <?php if ($serviceName !== ''): ?>
                <p class="mt-1 text-sm text-zinc-600">Under <a class="text-[#ff6b00]" href="<?= $h(url('/pages/services/' . $serviceSlug)) ?>"><?= $h($serviceName) ?></a></p>
                <?php endif; ?>
                <p class="mt-2 text-sm"><a class="text-[#ff6b00] font-medium" href="<?= $h(url('/pages/jobs/' . $slug . '/stockport')) ?>"><?= $h($name) ?> in Stockport</a></p>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="mt-12">
        <h2 class="text-2xl font-semibold text-[#061828]">Also on this site</h2>
        <ul class="mt-4 flex flex-wrap gap-3">
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold" href="<?= $h(url('/pages/services')) ?>">Services</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold" href="<?= $h(url('/pages/keywords')) ?>">Keyword guides</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold" href="<?= $h(url('/pages/areas')) ?>">Areas</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold" href="<?= $h(url('/pages/manufacturers')) ?>">Manufacturers</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold" href="<?= $h(url('/contact')) ?>">Request a quote</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold" href="<?= $h(url('/directories')) ?>">Listings and directories</a></li>
        </ul>
    </section>
</main>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
