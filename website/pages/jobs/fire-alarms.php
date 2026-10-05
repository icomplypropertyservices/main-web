<?php
/**
 * Fire Alarms job lane hub — install, maintain and service keyword pages.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/job-article.php';

$grouped = fireAlarmsLaneGrouped();
$counts = fireAlarmsLaneCounts();
$total = array_sum($counts);
$services = getServices();
$serviceName = $services['fire-alarms'] ?? 'Fire Alarms';

$pageTitle = 'Fire alarm jobs | install, maintenance and servicing | Stockport & North West';
$metaTitleExact = true;
$metaDesc = 'Fire alarm install, maintenance and servicing to BS 5839 across Stockport, Greater Manchester and the North West. '
    . $counts['install'] . ' install guides, ' . $counts['maintain'] . ' maintenance guides and ' . $counts['service']
    . ' service guides. Written quotes after scope. No published fees.';
$metaKeywords = 'fire alarm installation, fire alarm maintenance, fire alarm servicing, BS 5839, Stockport, Manchester, North West, iComply';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/jobs/fire-alarms');
$omitPriceRange = true;

$laneCopy = [
    'install' => [
        'title' => 'Install',
        'lead' => 'New systems, panel replacements, design and commissioning.',
        'text' => 'Design, supply and install of addressable, conventional and wireless fire detection, plus upgrades and commissioning packs.',
    ],
    'maintain' => [
        'title' => 'Maintain',
        'lead' => 'Planned visits, contracts, PPM and logbooks.',
        'text' => 'Competent-person inspection and servicing. For most BS 5839-1 systems the gap between visits should not exceed six months. The weekly user test stays on site.',
    ],
    'service' => [
        'title' => 'Service',
        'lead' => 'Repairs, faults, testing, call-outs and certificates.',
        'text' => 'Panel faults, false alarms, reactive repairs, inspections and the certificates that follow the work.',
    ],
];

$faqs = [
    [
        'What is the difference between fire alarm servicing and maintenance?',
        'On this site, maintenance is the planned programme: dated visits, contracts and logbooks. Servicing is the competent-person work on the day, including repairs, fault finding, tests and certificates. A maintenance contract usually includes the service visits.',
    ],
    [
        'How often should a BS 5839 system be serviced?',
        'For most non-domestic BS 5839-1 systems, inspection and servicing by a competent person should be no more than six months apart. The responsible person still carries out the weekly call-point test. Domestic BS 5839-6 systems follow the grade already installed.',
    ],
    [
        'Do you install a system and then maintain it?',
        'Yes. Installation, commissioning and the later servicing visits can sit with one Stockport team across Greater Manchester and the North West.',
    ],
    [
        'How do you price fire alarm work?',
        'Price on application after we know the panel, the device count, the sites and the access. We do not publish a fee for install, maintenance or call-out.',
    ],
];

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$faqEntities = [];
foreach ($faqs as [$q, $a]) {
    $faqEntities[] = [
        '@type' => 'Question',
        'name' => $q,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
    ];
}
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => 'Fire alarm installation, maintenance and servicing',
            'url' => $canonicalUrl,
            'description' => $metaDesc,
            'isPartOf' => ['@type' => 'WebSite', 'name' => SITE_NAME, 'url' => SITE_URL],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => url('/pages/services/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceName, 'item' => url('/pages/services/fire-alarms.php')],
                ['@type' => 'ListItem', 'position' => 4, 'name' => 'Install, maintain and service', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => $faqEntities,
        ],
    ],
];

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<section class="relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/60 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/index.php') ?>" class="hover:text-white">Services</a>
            <span>/</span>
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="hover:text-white"><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></a>
            <span>/</span>
            <span class="text-white/90">Install, maintain and service</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
            Fire alarms job lane · <?= (int)$total ?> guides
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05] max-w-4xl">
            Fire alarm installation,<br>
            <span class="text-[#ff6b00]">maintenance and servicing</span>
        </h1>
        <p class="mt-6 text-lg text-white/80 max-w-2xl">
            iComply designs, installs, maintains and services BS 5839 fire detection and alarm systems
            from Stockport across Greater Manchester and the North West. Pick a lane below.
            Quotes are written after scope. Price on application — we do not publish fees.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#install" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Install <?= (int)$counts['install'] ?></a>
            <a href="#maintain" class="px-6 py-3 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Maintain <?= (int)$counts['maintain'] ?></a>
            <a href="#service" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Service <?= (int)$counts['service'] ?></a>
            <a href="<?= url('/pages/services/fire-alarms.php') ?>" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Service hub</a>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-4">
        <?php foreach ($laneCopy as $lane => $copy): ?>
            <a href="#<?= htmlspecialchars($lane, ENT_QUOTES, 'UTF-8') ?>" class="block p-6 rounded-3xl border border-zinc-200 hover:border-[#ff6b00] transition">
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= (int)$counts[$lane] ?> guides</div>
                <h2 class="mt-2 text-2xl font-semibold text-[#0B1F3A]"><?= htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="mt-2 text-sm text-zinc-700"><?= htmlspecialchars($copy['lead'], ENT_QUOTES, 'UTF-8') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php foreach ($laneCopy as $lane => $copy): ?>
<section id="<?= htmlspecialchars($lane, ENT_QUOTES, 'UTF-8') ?>" class="<?= $lane === 'maintain' ? 'bg-zinc-50 border-y' : 'bg-white' ?>">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8') ?></div>
        <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#0B1F3A]"><?= htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8') ?> fire alarms</h2>
        <p class="mt-3 text-zinc-700 max-w-3xl"><?= htmlspecialchars($copy['text'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($grouped[$lane] as $job):
                $slug = (string)$job['slug'];
                $name = (string)($job['name'] ?? keywordDisplayName($slug));
                $jobPath = fireLanePublicPath($slug);
                if ($jobPath === null) {
                    continue;
                }
                ?>
                <a href="<?= url($jobPath) ?>"
                   class="px-4 py-3 bg-white border border-zinc-200 rounded-2xl text-sm font-semibold text-[#0B1F3A] hover:border-[#ff6b00] hover:text-[#ff6b00]">
                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<section class="bg-white border-t">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold text-[#0B1F3A] text-center">Fire alarm job lane FAQ</h2>
        <div class="mt-8 space-y-3">
            <?php foreach ($faqs as [$q, $a]): ?>
                <details class="bg-white border-2 border-zinc-200 rounded-2xl p-5">
                    <summary class="font-bold text-[#0B1F3A] cursor-pointer"><?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?></summary>
                    <p class="mt-3 text-sm text-zinc-800 leading-relaxed"><?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?></p>
                </details>
            <?php endforeach; ?>
        </div>
        <div class="mt-10"><?= shareButtonsHtml('Fire alarm installation, maintenance and servicing', $metaDesc) ?></div>
    </div>
</section>

<section id="quote" class="bg-[#0B1F3A] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold text-center">Quote for fire alarm install, maintenance or service</h2>
        <p class="mt-2 text-center text-white/80">Price on application after scope. Tell us the panel brand, the site and whether this is a new system, a contract or a fault.</p>
        <form action="<?= url('/contact.php') ?>" method="POST" class="mt-8 bg-white text-zinc-900 rounded-3xl p-6 md:p-8 space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                <select name="service" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl bg-white">
                    <option value="Fire alarm installation">Fire alarm installation</option>
                    <option value="Fire alarm maintenance">Fire alarm maintenance</option>
                    <option value="Fire alarm servicing">Fire alarm servicing</option>
                    <option value="Fire alarm call-out">Fire alarm call-out</option>
                </select>
            </div>
            <textarea name="message" rows="4" required placeholder="Postcode, panel brand, new install / contract / fault…" class="w-full border border-zinc-300 px-4 py-3 rounded-xl"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-semibold">Request a written quote</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
