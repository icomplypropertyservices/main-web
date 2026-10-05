<?php
/**
 * Keyword hub — high-contrast, unique SEO content per keyword.
 * Pure PHP vars via executeTemplateVars() (no {{}} / eval).
 * RELATED_SLUG, RELATED_NAME, MANUFACTURER_TAGS, SEO_KEYWORDS,
 * KEYWORD_INTRO, KEYWORD_BODY, KEYWORD_META, KEYWORD_FOCUS_HTML, KEYWORD_FAQ_HTML,
 * KEYWORD_IMAGE, SERVICE_IMAGE
 */
$pageTitle = !empty($KEYWORD_SEO_TITLE) ? $KEYWORD_SEO_TITLE : ($KEYWORD_NAME . ' | North West');
$metaDesc = $KEYWORD_META;
$metaKeywords = $SEO_KEYWORDS;
$ogImage = $KEYWORD_IMAGE;
$canonicalUrl = url('/pages/keywords/' . $KEYWORD_SLUG . '.php');

$keywordName = $KEYWORD_NAME;
$keywordH1 = !empty($KEYWORD_H1) ? $KEYWORD_H1 : $KEYWORD_NAME;
$keywordSlug = $KEYWORD_SLUG;
$serviceName = $SERVICE_NAME;
$serviceSlug = $SERVICE_SLUG;
$fireInstallerFamily = function_exists('icomplyFireAlarmInstallerIsFamily')
    && icomplyFireAlarmInstallerIsFamily((string)$keywordSlug);
$fireInstallerP0 = function_exists('icomplyFireAlarmInstallerIsP0')
    && icomplyFireAlarmInstallerIsP0((string)$keywordSlug);
$nationwideP0 = function_exists('icomplyNationwide3lineIsP0') && icomplyNationwide3lineIsP0((string)$keywordSlug);
// og:image is made absolute in includes/header.php (main #113).
$poaService = (function_exists('isPoaService') && isPoaService((string)$serviceSlug)) || $nationwideP0 || $fireInstallerFamily;
$fireLane = function_exists('fireAlarmsLaneForSlug') ? fireAlarmsLaneForSlug((string)$keywordSlug) : null;
$fireLaneLabel = $fireLane ? fireAlarmsLaneLabel($fireLane) : '';
$fireLaneHub = url('/pages/jobs/fire-alarms.php');
$relatedSlug = $RELATED_SLUG;
$relatedName = $RELATED_NAME;
$allAreas = function_exists('icomplyLocalTownNames') ? icomplyLocalTownNames() : getAreas();
$allServices = getServices();
$top5000Towns = [];
if ($nationwideP0 && function_exists('icomplyTop5000Towns')) {
    $top5000Towns = icomplyTop5000Towns();
}

$popularTowns = array_values(array_filter(
    ['Manchester', 'Salford', 'Bolton', 'Bury', 'Oldham', 'Rochdale', 'Stockport', 'Tameside', 'Trafford', 'Wigan', 'Altrincham', 'Sale', 'Ashton-under-Lyne'],
    fn($t) => in_array($t, $allAreas, true)
));
if ($nationwideP0 && $top5000Towns) {
    $popularWanted = ['London', 'Birmingham', 'Leeds', 'Glasgow', 'Cardiff', 'Edinburgh', 'Manchester', 'Stockport', 'Bristol', 'Liverpool'];
    $byName = [];
    foreach ($top5000Towns as $townRow) {
        $byName[(string)$townRow['name']] = (string)$townRow['slug'];
    }
    $popularTowns = [];
    foreach ($popularWanted as $wanted) {
        if (isset($byName[$wanted])) {
            $popularTowns[] = $wanted;
        }
    }
} elseif ($fireInstallerP0 && function_exists('icomplyUkTowns')) {
    $allAreas = [];
    foreach (icomplyUkTowns() as $townRow) {
        if (!empty($townRow['name'])) {
            $allAreas[] = (string)$townRow['name'];
        }
    }
    $popularTowns = array_values(array_filter(
        ['London', 'Birmingham', 'Leeds', 'Glasgow', 'Cardiff', 'Edinburgh', 'Manchester', 'Stockport', 'Bristol', 'Liverpool'],
        static fn($t) => in_array($t, $allAreas, true)
    ));
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => $keywordName,
            'description' => $metaDesc,
            'provider' => ['@type' => 'LocalBusiness', 'name' => SITE_NAME, 'telephone' => PHONE, 'url' => SITE_URL],
            'areaServed' => ($nationwideP0 || $fireInstallerFamily) ? 'United Kingdom' : 'North West England',
            'serviceType' => $serviceName,
            'url' => $canonicalUrl,
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Guides', 'item' => url('/pages/keywords/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $keywordName, 'item' => $canonicalUrl],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php if (!empty($KEYWORD_FAQ_JSON)): ?>
<script type="application/ld+json"><?= $KEYWORD_FAQ_JSON ?></script>
<?php endif; ?>

<!-- HERO: solid navy + image with dark overlay for readable text -->
<section class="relative overflow-hidden bg-[#061828] text-white">
    <div class="absolute inset-0">
        <img src="<?= htmlspecialchars($KEYWORD_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($KEYWORD_NAME ?? 'Property compliance', ENT_QUOTES, 'UTF-8') ?> — iComply Property Services" class="w-full h-full object-cover opacity-35" loading="eager"
             onerror="this.src=$SERVICE_IMAGE">
        <div class="absolute inset-0 bg-gradient-to-r from-[#061828] via-[#061828]/95 to-[#061828]/75"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/70 mb-5 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a><span class="text-white/40">/</span>
            <?php if ($fireLane): ?>
            <a href="<?= htmlspecialchars($fireLaneHub, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Fire alarm jobs</a><span class="text-white/40">/</span>
            <a href="<?= htmlspecialchars($fireLaneHub . '#' . $fireLane, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white"><?= htmlspecialchars($fireLaneLabel, ENT_QUOTES, 'UTF-8') ?></a><span class="text-white/40">/</span>
            <?php else: ?>
            <a href="<?= url('/pages/keywords/index.php') ?>" class="hover:text-white">Guides</a><span class="text-white/40">/</span>
            <?php endif; ?>
            <span class="text-white font-medium"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ff6b00] text-white text-xs font-bold tracking-widest uppercase mb-5">
            <?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> guide
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight text-white max-w-3xl leading-[1.08] drop-shadow-lg">
            <?= htmlspecialchars($keywordH1, ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="mt-5 text-lg md:text-xl text-white max-w-2xl leading-relaxed font-medium drop-shadow">
            <?= htmlspecialchars($KEYWORD_INTRO, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-bold text-white shadow-lg"><?= $poaService ? 'Enquire for POA' : 'Get free quote' ?></a>
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=<?= rawurlencode($keywordName . ' quote') ?>"
               target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-bold text-white shadow-lg">WhatsApp</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#061828] font-bold shadow-lg"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<?php
require_once SITE_ROOT . '/includes/access-control-jobs.php';
if (function_exists('accessControlLaneKeywordStrip')) {
    echo accessControlLaneKeywordStrip($keywordSlug);
}
?>

<!-- TRUST: high contrast white on zinc -->
<section class="bg-white border-b border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            [($nationwideP0 || $fireInstallerFamily) ? 'UK mainland' : 'Local engineers', $nationwideP0 ? 'Stockport base — TOP 5000 towns' : ($fireInstallerFamily ? 'Stockport base — mainland towns over 10,000' : 'Stockport base — 150+ North West towns')],
            ['Standards-led', 'British Standards & manufacturer guidance'],
            [$poaService ? 'POA / enquire' : 'Fixed quotes', $poaService ? 'Written scope — no invented £' : 'Clear scope before work starts'],
            ['Full paperwork', $poaService ? 'Survey or briefing notes for the dutyholder file' : 'Certificates & logbooks for compliance'],
        ];
        foreach ($trust as [$t, $d]): ?>
        <div class="flex gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#061828] text-white flex items-center justify-center font-bold shrink-0">✓</div>
            <div>
                <div class="font-bold text-[#061828]"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="text-sm text-zinc-800 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- MAIN CONTENT -->
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-16">
        <div class="grid lg:grid-cols-5 gap-10">
            <div class="lg:col-span-3">
                <div class="bg-white border-2 border-zinc-200 rounded-3xl p-6 md:p-8 shadow-sm">
                    <h2 class="text-2xl md:text-3xl font-bold text-[#061828] tracking-tight">
                        About <?= htmlspecialchars($keywordName, ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <p class="mt-4 text-base md:text-lg text-zinc-900 leading-relaxed font-medium"><?= htmlspecialchars($KEYWORD_INTRO, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php
                    $bodyParas = preg_split("/\R\R+/", trim((string)$KEYWORD_BODY)) ?: [];
                    if ($bodyParas === [] || $bodyParas === ['']) {
                        $bodyParas = [(string)$KEYWORD_BODY];
                    }
                    foreach ($bodyParas as $bodyPara):
                        $bodyPara = trim((string)$bodyPara);
                        if ($bodyPara === '') {
                            continue;
                        }
                    ?>
                    <p class="mt-4 text-base md:text-lg text-zinc-900 leading-relaxed"><?= htmlspecialchars($bodyPara, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endforeach; ?>
                    <?php if ($nationwideP0): ?>
                    <p class="mt-4 text-base text-zinc-900 leading-relaxed">Workshop: 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Quotes are price on application. iComply does not claim BAFE or NSI badges.</p>
                    <?php elseif ($fireInstallerFamily): ?>
                    <p class="mt-4 text-base text-zinc-900 leading-relaxed">Workshop: 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Design, installation and commissioning follow BS 5839. Quotes are price on application.</p>
                    <?php endif; ?>
                    <ul class="mt-6 space-y-3"><?= $KEYWORD_FOCUS_HTML ?></ul>
                    <p class="mt-6 text-sm text-zinc-800">
                        Part of our
                        <a href="<?= url('/pages/services/' . $SERVICE_SLUG . '.php') ?>" class="font-bold text-[#ff6b00] hover:underline"><?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?></a>
                        service · Related:
                        <a href="<?= url('/pages/keywords/' . $RELATED_SLUG . '.php') ?>" class="font-bold text-[#ff6b00] hover:underline"><?= htmlspecialchars($RELATED_NAME, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php if ($fireLane): ?>
                        · Lane:
                        <a href="<?= htmlspecialchars($fireLaneHub . '#' . $fireLane, ENT_QUOTES, 'UTF-8') ?>" class="font-bold text-[#ff6b00] hover:underline">Fire alarms <?= htmlspecialchars($fireLaneLabel, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="lg:col-span-2 space-y-5">
                <div class="rounded-3xl overflow-hidden border-2 border-zinc-300 shadow-md bg-zinc-200">
                    <img src="<?= htmlspecialchars($KEYWORD_INLINE ?? $KEYWORD_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> equipment — iComply Property Services"
                         class="w-full h-52 object-cover" loading="lazy"
                         onerror="this.src=$SERVICE_IMAGE">
                    <div class="p-3 bg-[#061828] text-white text-sm font-semibold text-center"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="rounded-3xl overflow-hidden border-2 border-zinc-300 shadow-md bg-zinc-200">
                    <img src="<?= htmlspecialchars($SERVICE_IMAGE, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> by iComply"
                         class="w-full h-40 object-cover" loading="lazy">
                    <div class="p-3 bg-white text-[#061828] text-sm font-semibold text-center border-t-2 border-zinc-200"><?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> service</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once SITE_ROOT . '/includes/quality-bar.php';
echo '<section class="max-w-7xl mx-auto px-6 py-10">';
echo icomplyQualityBarImages((string)$serviceSlug, (string)$keywordName, 'q2-hub-images')['html'];
echo '</section>';
?>

<!-- MANUFACTURERS -->
<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Brands we install &amp; service</h2>
        <p class="mt-2 text-zinc-800 max-w-2xl">Click a manufacturer for products, kits and install quotes related to <?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> and <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?>.</p>
        <div class="mt-6 flex flex-wrap gap-2"><?= $MANUFACTURER_TAGS ?></div>
    </div>
</section>

<?php if (($SERVICE_SLUG ?? '') === 'nurse-call'): ?>
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Manchester, Stockport, and the hub</h2>
        <p class="mt-2 text-zinc-800 max-w-3xl">Nurse call is written up for the Greater Manchester towns on the areas list, including Manchester and Stockport. Care, ward and warden scopes are split on the hub. Fire and lighting for the same operator sit on the care homes page.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php
            $nurseCallLocals = [
                ['/pages/nurse-call-systems', 'Nurse call systems'],
                ['/pages/nurse-call-manchester', 'Nurse call in Manchester'],
                ['/pages/nurse-call/stockport', 'Nurse call in Stockport'],
                ['/pages/care-homes', 'Care homes'],
                ['/pages/services/nurse-call', 'Nurse call service'],
            ];
            foreach ($nurseCallLocals as [$href, $label]): ?>
                <a href="<?= url($href) ?>" class="px-4 py-2.5 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition shadow"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php elseif ($nationwideP0): ?>
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> across the UK mainland</h2>
        <p class="mt-2 text-zinc-800">TOP 5000 towns by population (<?= count($top5000Towns) ?> places). Each place has its own page. Greater Manchester area hubs, including places outside that population list, stay linked below.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php foreach ($popularTowns as $a):
                $popularSlug = '';
                foreach ($top5000Towns as $townRow) {
                    if ((string)$townRow['name'] === $a) {
                        $popularSlug = (string)$townRow['slug'];
                        break;
                    }
                }
                if ($popularSlug === '') {
                    continue;
                }
            ?>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . $popularSlug) ?>"
                   class="px-4 py-2.5 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition shadow">
                    <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($top5000Towns as $townRow):
                $townName = (string)$townRow['name'];
                if (in_array($townName, $popularTowns, true)) {
                    continue;
                }
            ?>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . (string)$townRow['slug']) ?>"
                   class="px-3 py-1.5 bg-white border-2 border-zinc-300 text-zinc-900 rounded-full text-xs font-medium hover:border-[#ff6b00] hover:text-[#ff6b00]">
                    <?= htmlspecialchars($KEYWORD_NAME . ' · ' . $townName, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php elseif ($fireInstallerFamily && !$fireInstallerP0): ?>
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">UK mainland, quoted from Stockport</h2>
        <p class="mt-2 text-zinc-800 max-w-3xl">This guide is nationwide. Town pages for the installer, installation and engineer wording are published separately. Open the fire alarm service and the related guides below.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/fire-alarms') ?>" class="px-4 py-2.5 bg-[#ff6b00] text-white rounded-full text-sm font-semibold">Fire alarms</a>
            <?php
            $familyLinks = function_exists('icomplyFireAlarmInstallerP0Slugs') ? icomplyFireAlarmInstallerP0Slugs() : [];
            $familyKeywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
            foreach ($familyLinks as $familySlug):
                if ($familySlug === $keywordSlug || !isset($familyKeywords[$familySlug])) {
                    continue;
                }
                $familyLabel = (string)($familyKeywords[$familySlug]['name'] ?? $familySlug);
            ?>
            <a href="<?= url('/pages/keywords/' . $familySlug) ?>" class="px-4 py-2.5 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00]"><?= htmlspecialchars($familyLabel, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<!-- AREAS — every town linked (keyword × area pages) -->
<section class="bg-zinc-100">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> by area</h2>
        <p class="mt-2 text-zinc-800"><?= $fireInstallerP0
            ? 'UK mainland towns with population over 10,000 (' . count($allAreas) . ' places), each with its own page.'
            : 'Local landing pages for every town we cover (' . count($allAreas) . ' areas) — e.g. ' . htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') . ' in Stockport.' ?></p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php foreach ($popularTowns as $a): ?>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . areaSlug($a) . '.php') ?>"
                   class="px-4 py-2.5 bg-[#061828] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition shadow">
                    <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> in <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($allAreas as $a):
                if (in_array($a, $popularTowns, true)) continue;
            ?>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . areaSlug($a) . '.php') ?>"
                   class="px-3 py-1.5 bg-white border-2 border-zinc-300 text-zinc-900 rounded-full text-xs font-medium hover:border-[#ff6b00] hover:text-[#ff6b00]">
                    <?= htmlspecialchars($KEYWORD_NAME . ' · ' . $a, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($fireInstallerFamily): ?>
<section class="bg-white border-t-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Related fire alarm installer guides</h2>
        <p class="mt-2 text-zinc-800 max-w-3xl">These pages sit with the fire alarm service. This wave publishes town pages for the installer, installation and engineer wording. The other guides stay as hubs until a later town wave.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <a href="<?= url('/pages/services/fire-alarms') ?>" class="px-4 py-2.5 bg-[#ff6b00] text-white rounded-full text-sm font-semibold">Fire alarms</a>
            <?php
            $familyHubSlugs = function_exists('icomplyFireAlarmInstallerHubSlugs') ? icomplyFireAlarmInstallerHubSlugs() : [];
            $familyKeywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
            foreach ($familyHubSlugs as $familySlug):
                if ($familySlug === $keywordSlug || !isset($familyKeywords[$familySlug])) {
                    continue;
                }
                $familyLabel = (string)($familyKeywords[$familySlug]['name'] ?? $familySlug);
            ?>
            <a href="<?= url('/pages/keywords/' . $familySlug) ?>" class="px-3 py-1.5 bg-white border-2 border-zinc-300 text-zinc-900 rounded-full text-xs font-semibold hover:border-[#ff6b00]"><?= htmlspecialchars($familyLabel, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="related-links bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Greater Manchester area hubs</h2>
        <p class="mt-2 text-zinc-800">Each town hub below is the parent for <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> in that town.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php
            if (!function_exists('icomplyGreaterManchesterTownNames')) {
                require_once SITE_ROOT . '/includes/building-hub-copy.php';
            }
            foreach (icomplyGreaterManchesterTownNames() as $gmTown):
                $gmSlug = areaSlug((string)$gmTown);
            ?>
                <a href="<?= url('/pages/areas/' . $gmSlug) ?>" class="px-3 py-1.5 bg-white border-2 border-zinc-300 text-zinc-900 rounded-full text-xs font-medium hover:border-[#ff6b00]"><?= htmlspecialchars((string)$gmTown, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= url('/pages/keywords/' . $KEYWORD_SLUG . '/' . $gmSlug) ?>" class="px-3 py-1.5 bg-[#061828] text-white rounded-full text-xs font-semibold hover:bg-[#ff6b00]"><?= htmlspecialchars($KEYWORD_NAME . ' in ' . $gmTown, ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- RELATED KEYWORDS same service -->
<section class="bg-white border-y-2 border-zinc-200">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828]">Related <?= htmlspecialchars($SERVICE_NAME, ENT_QUOTES, 'UTF-8') ?> guides</h2>
        <?php if (($SERVICE_SLUG ?? '') === 'nurse-call'): ?>
        <p class="mt-2 text-zinc-800">More nurse-call topics. Manchester and Stockport are on the Greater Manchester list.</p>
        <?php else: ?>
        <p class="mt-2 text-zinc-800">More topics under the same service — each also has pages for every Greater Manchester town.</p>
        <?php endif; ?>
        <div class="mt-6">
            <?php
            require_once SITE_ROOT . '/includes/related.php';
            echo siblingKeywordsHtml($KEYWORD_SLUG, $SERVICE_SLUG, 0);
            ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="bg-white border-t-2 border-zinc-200">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-2xl md:text-3xl font-bold text-[#061828] text-center mb-8"><?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?> FAQ</h2>
        <div class="space-y-3"><?= $KEYWORD_FAQ_HTML ?></div>
        <div class="mt-10"><?= shareButtonsHtml($keywordName, $metaDesc) ?></div>
    </div>
</section>

<!-- QUOTE -->
<section id="quote" class="bg-[#061828] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-bold text-center">Quote for <?= htmlspecialchars($KEYWORD_NAME, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-2 text-center text-white/90">Price on application after scope. No catalogue fee. Stockport base, with North West coverage and further travel quoted where the service is published.</p>
        <form action="<?= url('/contact.php') ?>" method="POST" class="mt-8 bg-white text-zinc-900 border-2 border-zinc-300 rounded-3xl p-6 md:p-8 space-y-4 shadow-xl">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <input type="email" name="email" placeholder="Email" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium">
                <select name="service" required class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl bg-white font-medium">
                    <option value="<?= htmlspecialchars($keywordName, ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars($keywordName, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required placeholder="Postcode, property type, brand / system…" class="w-full border-2 border-zinc-300 px-4 py-3 rounded-xl font-medium"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-bold text-lg">Submit request</button>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
