<?php
/**
 * Job-type family index. Town pages are /pages/jobs/{job}/{town}.
 * Copy: SEO CLOSE-6 JOBS-INDEX (22 hubs).
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/aov-barriers-deep.php';

$pageTitle = 'Job types | Greater Manchester | iComply';
$metaDesc = 'Job hubs for landlords, fire alarms, gas, electrical, barriers and call-outs across Greater Manchester. POA after scope. Call 07517806082.';
$canonicalUrl = url('/pages/jobs');
$metaRobots = 'index, follow';

$groups = [
    'Landlord & HMO' => [
        ['slug' => 'hmo', 'blurb' => 'HMO compliance visits for shared houses and bedsits.'],
        ['slug' => 'hmo-compliance', 'blurb' => 'HMO licence evidence packs and remedial tracking.'],
        ['slug' => 'hmo-fire-safety', 'blurb' => 'HMO fire safety checks aligned to the property layout.'],
        ['slug' => 'hmo-occupancy', 'blurb' => 'HMO occupancy and amenity evidence for licensing files.'],
        ['slug' => 'landlord-bundle', 'blurb' => 'Landlord multi-cert bundle planned as one diary.'],
        ['slug' => 'landlord-compliance', 'blurb' => 'Landlord compliance visits across electrical, fire and gas scopes.'],
        ['slug' => 'landlord-gas-safety', 'blurb' => 'Landlord gas safety (CP12) visits for rented stock.'],
    ],
    'Fire & alarms' => [
        ['slug' => 'bs-5839-maintenance', 'blurb' => 'BS 5839 fire alarm maintenance and service records.'],
        ['slug' => 'false-alarm-investigation', 'blurb' => 'False alarm investigation with cause notes for the site log.'],
        ['slug' => 'fire-alarm-call-out', 'blurb' => 'Fire alarm call-outs for faults and urgent attendances.'],
        ['slug' => 'fire-alarm-ppm', 'blurb' => 'Fire alarm planned preventive maintenance visits.'],
        ['slug' => 'fire-alarm-replacement', 'blurb' => 'Fire alarm replacement and panel/device change-outs.'],
        ['slug' => 'fire-alarms', 'blurb' => 'Fire alarm installation, service and certification support.'],
        ['slug' => 'fire-risk-assessment', 'blurb' => 'Fire risk assessments for residential and commercial stock.'],
        ['slug' => 'fra', 'blurb' => 'FRA visits with prioritised action lists.'],
        ['slug' => 'fra-other', 'blurb' => 'FRA support for atypical or multi-site portfolios.'],
    ],
    'Gas & electrical' => [
        ['slug' => 'eicr', 'blurb' => 'EICR inspection and testing for landlords and managers.'],
        ['slug' => 'gas-safety', 'blurb' => 'Gas safety checks arranged for rented and commercial sites.'],
        ['slug' => 'gas-safety-cp12', 'blurb' => 'CP12 landlord gas safety certificates for lettings files.'],
    ],
    'Access / barriers / call-outs' => [
        ['slug' => 'came-gard-gt4', 'blurb' => 'CAME GARD GT4 barrier support and lane upgrades.'],
        ['slug' => 'car-park-barrier', 'blurb' => 'Car park barrier service, loops and safety edges.'],
        ['slug' => 'maglock-installation', 'blurb' => 'Maglock installation and access hardware fitting.'],
    ],
];

$faqs = [
    [
        'Do you publish fixed prices on job pages?',
        'No. Every job type is quoted as POA after we know the building, access and certificate scope. We do not show £ bands or “from £X” on these pages.',
    ],
    [
        'Is gas work available?',
        'Yes. Gas safety and CP12 visits are offered. They are carried out by Gas Safe registered engineers. iComply is not itself described as Gas Safe registered.',
    ],
    [
        'Which towns get job×town pages?',
        'Towns in the dual ring (Greater Manchester plus 50 miles of Manchester and of Burnley) have job×town URLs. Manufacturer×town pages stay on the Greater Manchester core. Start from the hub, then pick your town (Stockport is linked on each card as a working example).',
    ],
    [
        'How do I request a visit?',
        'Call 07517806082, email info@icomplypropertyservices.co.uk, or send the contact form with the job type, address and whether the building is occupied. We reply with a POA quote before locking a date.',
    ],
];

$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
$phone = defined('PHONE') ? PHONE : '07517806082';
$email = defined('EMAIL') ? EMAIL : 'info@icomplypropertyservices.co.uk';

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#061828] text-white">
    <div class="max-w-5xl mx-auto px-6 py-14">
        <nav class="text-xs text-white/60 mb-4" aria-label="Breadcrumb">
            <a class="hover:text-white" href="<?= $h(rtrim(SITE_URL, '/') . '/') ?>">Home</a>
            <span> / </span>
            <span>Jobs</span>
        </nav>
        <h1 class="text-4xl font-semibold tracking-tight">Job types for Greater Manchester property work</h1>
        <p class="mt-4 text-lg text-white/80 max-w-3xl">iComply Property Services lists the job types we arrange from Stockport across Greater Manchester. Each hub explains the visit, the paperwork and how we quote. Pricing is always price on application (POA) after the building and scope are known — never a published pound figure.</p>
    </div>
</section>
<main class="max-w-5xl mx-auto px-6 py-12">
    <p class="text-zinc-700 leading-relaxed max-w-3xl">Use this index to reach the right hub before you request a date. Landlord and HMO jobs sit beside fire alarm maintenance, FRAs, electrical testing, gas safety and access hardware. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. Barrier and maglock work is scoped on site so lane lengths, loops and power are clear before a quote. Call 07517806082, email info@icomplypropertyservices.co.uk, or use <a class="text-[#ff6b00] font-semibold" href="<?= $h(url('/contact')) ?>">https://icomplypropertyservices.co.uk/contact</a>. Footer and citation pages must keep Cheshire in the Stockport address.</p>

    <?php foreach ($groups as $group => $jobs): ?>
    <section class="related-links mt-12" aria-label="<?= $h($group) ?>">
        <h2 class="text-2xl font-semibold text-[#061828]"><?= $h($group) ?></h2>
        <ul class="mt-6 grid sm:grid-cols-2 gap-3">
            <?php foreach ($jobs as $job):
                $slug = $job['slug'];
                $name = ucwords(str_replace('-', ' ', $slug));
                // DEEP keyword hub is canonical for overlapping barrier/AOV jobs (301 from /pages/jobs/{slug}).
                $deepKeyword = function_exists('icomplyAovBarriersDeepJobTarget') ? icomplyAovBarriersDeepJobTarget($slug) : null;
                $hubHref = $deepKeyword !== null ? '/pages/keywords/' . $deepKeyword : '/pages/jobs/' . $slug;
                ?>
            <li class="border border-zinc-200 rounded-2xl p-4 bg-white">
                <a class="font-semibold text-[#061828] hover:text-[#ff6b00]" href="<?= $h(url($hubHref)) ?>"><?= $h($name) ?></a>
                <p class="mt-1 text-sm text-zinc-600"><?= $h($job['blurb']) ?></p>
                <p class="mt-2 text-sm"><a class="text-[#ff6b00] font-medium" href="<?= $h(url($hubHref . '/stockport')) ?>"><?= $h($name) ?> in Stockport</a></p>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endforeach; ?>

    <section class="mt-12" aria-label="Questions">
        <h2 class="text-2xl font-semibold text-[#061828]">Questions</h2>
        <div class="mt-6 space-y-4">
            <?php foreach ($faqs as [$question, $answer]): ?>
            <details class="border border-zinc-200 rounded-2xl p-4 bg-white">
                <summary class="font-semibold text-[#061828] cursor-pointer"><?= $h($question) ?></summary>
                <p class="mt-2 text-zinc-700"><?= $h($answer) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="mt-12 bg-zinc-50 border border-zinc-200 rounded-2xl p-6">
        <h2 class="text-2xl font-semibold text-[#061828]">Request a POA quote</h2>
        <p class="mt-3 text-zinc-700"><a class="text-[#ff6b00] font-semibold" href="tel:<?= $h(preg_replace('/\s+/', '', $phone)) ?>"><?= $h($phone) ?></a> · <a class="text-[#ff6b00] font-semibold" href="mailto:<?= $h($email) ?>"><?= $h($email) ?></a> · <a class="text-[#ff6b00] font-semibold" href="<?= $h(url('/contact')) ?>">https://icomplypropertyservices.co.uk/contact</a></p>
        <p class="mt-2 text-zinc-700">Quote line: “POA fixed quote after scope”.</p>
    </section>
</main>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
