<?php
/**
 * Featured area index (Manchester, Burnley).
 * Lists every service. Fire links use national hubs so they stay valid
 * for any UK place, not only North West towns.
 *
 * Placeholders: AREA, AREA_SLUG, AREA_URL
 */
require_once SITE_ROOT . '/includes/local-content.php';

$areaName = $AREA;
$areaSlugVal = $AREA_SLUG;
$allServices = getServices();
$allAreas = getAreas();
$categories = getServiceCategories();
$fireSlugs = array_fill_keys(getFireSafetyServiceSlugs(), true);
$profile = area_profile($areaName);
$keywords = getMajorKeywords();

$serviceHref = static function (string $slug) use ($fireSlugs, $areaName): string {
    if (isset($fireSlugs[$slug])) {
        return fireNationwideServiceUrl($slug);
    }
    return exportedServiceLocalUrl($slug, $areaName, 'area');
};

$fireKeywords = [];
foreach (getPopularKeywordSlugs() as $slug) {
    $meta = $keywords[$slug] ?? null;
    if (!is_array($meta)) {
        continue;
    }
    $svc = (string)($meta['service'] ?? '');
    if (isset($fireSlugs[$svc])) {
        $fireKeywords[$slug] = $meta;
    }
}

$localKeywords = [];
foreach (getPopularKeywordSlugs() as $slug) {
    $meta = $keywords[$slug] ?? null;
    if (!is_array($meta)) {
        continue;
    }
    $svc = (string)($meta['service'] ?? '');
    if (isset($fireSlugs[$svc])) {
        continue;
    }
    $localKeywords[$slug] = $meta;
    if (count($localKeywords) >= 12) {
        break;
    }
}

$ukPlaces = ['London', 'Birmingham', 'Leeds', 'Cardiff', 'Glasgow', 'Bristol'];
$sister = $areaName === 'Manchester' ? 'Burnley' : ($areaName === 'Burnley' ? 'Manchester' : '');

if (!function_exists('icomplyGmAdjacentTownNames')) {
    require_once SITE_ROOT . '/includes/building-hub-copy.php';
}
$nearby = icomplyGmAdjacentTownNames($areaName, 12);
if (!$nearby) {
    $idx = array_search($areaName, $allAreas, true);
    if ($idx === false) {
        $nearby = array_slice($allAreas, 0, 12);
    } else {
        $start = max(0, (int)$idx - 6);
        $nearby = array_values(array_filter(array_slice($allAreas, $start, 14), static function ($a) use ($areaName) {
            return $a !== $areaName;
        }));
        $nearby = array_slice($nearby, 0, 12);
    }
}

$districts = (string)($profile['districts'] ?? '');
$region = (string)($profile['region'] ?? 'the North West');
$stock = (string)($profile['stock'] ?? 'local property');
$travel = (string)($profile['travel'] ?? 'from our Stockport base');
$focus = (string)($profile['focus'] ?? 'property compliance');

$pageTitle = $areaName . ' Services Index | All Property Services';
$metaDesc = 'Full ' . $areaName . ' service index — every iComply trade listed. Fire safety links open UK-wide hubs. Electrical, gas and building work booked locally from Stockport SK2.';
$metaKeywords = $areaName . ' property services, ' . $areaName . ' fire alarms, ' . $areaName . ' EICR, fire safety UK, ' . $districts;
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/pages/areas/' . $areaSlugVal . '.php');
$metaRobots = function_exists('icomplyRobotsMetaForPath')
    ? icomplyRobotsMetaForPath('/pages/areas/' . $areaSlugVal)
    : 'index, follow';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

require_once SITE_ROOT . '/includes/share.php';
require SITE_ROOT . '/includes/header.php';

$schemaItems = [];
$pos = 0;
foreach ($allServices as $slug => $name) {
    $schemaItems[] = [
        '@type' => 'ListItem',
        'position' => ++$pos,
        'name' => $name . (isset($fireSlugs[$slug]) ? ' (UK-wide)' : ' in ' . $areaName),
        'url' => $serviceHref($slug),
    ];
}
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'LocalBusiness',
            'name' => SITE_NAME . ' — ' . $areaName,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'telephone' => PHONE,
            'email' => EMAIL,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '17 Woodlands Park Road, Offerton',
                'addressLocality' => 'Stockport',
                'addressRegion' => 'Cheshire',
                'postalCode' => 'SK2 5DE',
                'addressCountry' => 'GB',
            ],
            'areaServed' => [
                '@type' => 'City',
                'name' => $areaName,
            ],
            'priceRange' => '££',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Areas', 'item' => url('/pages/areas/index.php')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $areaName, 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => 'All services for ' . $areaName,
            'numberOfItems' => count($allServices),
            'itemListElement' => $schemaItems,
        ],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/areas/index.php') ?>" class="hover:text-white">Areas</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Service index · <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                All property services in<br>
                <span class="text-[#ff6b00]"><?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></span>
            </h1>
            <p class="mt-6 text-lg text-white/80 max-w-2xl">
                Every service we quote, in one index for <?= htmlspecialchars($districts !== '' ? $districts : $areaName, ENT_QUOTES, 'UTF-8') ?>.
                Fire safety links on this page open <strong class="text-white">UK-wide</strong> hubs.
                Electrical, gas, security and building work stay local to <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> and the North West.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#services" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Browse all <?= count($allServices) ?> services</a>
                <a href="#fire-uk" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Fire links, UK-wide</a>
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold hover:bg-zinc-100">Free quote</a>
            </div>
            <div class="mt-8 flex flex-wrap gap-6 text-sm text-white/70">
                <div><span class="text-white font-semibold text-xl block"><?= count($allServices) ?></span> services listed</div>
                <div><span class="text-white font-semibold text-xl block"><?= count($fireSlugs) ?></span> fire links, UK-wide</div>
                <div><span class="text-white font-semibold text-xl block">SK2</span> yard in Stockport</div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $trust = [
            [$areaName . ' cover', $region . ' · ' . $stock],
            ['Travel', ucfirst($travel)],
            ['Fire links', 'National hubs — valid for any UK place, not only North West towns'],
            ['Quotes', 'Scoped after the site is known. No fee is published here'],
        ];
        foreach ($trust as [$t, $d]): ?>
            <div class="flex gap-3 items-start">
                <div class="w-10 h-10 rounded-2xl bg-[#0B1F3A]/10 flex items-center justify-center text-[#0B1F3A] font-bold shrink-0">✓</div>
                <div>
                    <div class="font-semibold text-black"><?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-sm text-zinc-600 mt-0.5"><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?></div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">
                <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> service index
            </h2>
            <p class="mt-5 text-lg text-zinc-700 leading-relaxed">
                <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($districts !== ''): ?>(<?= htmlspecialchars($districts, ENT_QUOTES, 'UTF-8') ?>)<?php endif; ?>
                is <?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?>.
                Typical focus here is <?= htmlspecialchars($focus, ENT_QUOTES, 'UTF-8') ?>.
                Engineers travel <?= htmlspecialchars($travel, ENT_QUOTES, 'UTF-8') ?>.
            </p>
            <p class="mt-4 text-lg text-zinc-700 leading-relaxed">
                This page lists the full catalogue — fire, electrical, gas, security, professional support and construction.
                It is an index, not a cloned doorway for every street.
                <?php if ($areaName === 'Manchester'): ?>
                    The quality local guide is also on the
                    <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/manchester-property-compliance') ?>">Manchester property compliance hub</a>.
                <?php endif; ?>
                <?php if ($sister !== ''): ?>
                    Sister index:
                    <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= url('/pages/areas/' . areaSlug($sister) . '.php') ?>"><?= htmlspecialchars($sister, ENT_QUOTES, 'UTF-8') ?></a>.
                <?php endif; ?>
            </p>
        </div>
        <div class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">
            <h3 class="text-2xl font-semibold tracking-tight">How the links work</h3>
            <ul class="mt-6 space-y-3 text-sm text-white/90">
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Fire services open the national hub. The same link still works for London, Birmingham or any other UK place.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Electrical and gas open a real keyword page for <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> Other trades open their service hub, booked for <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> when diary allows.</li>
                <li class="flex gap-2"><span class="text-[#ff6b00]">●</span> We do not publish a price on this index. Legionella and asbestos stay POA.</li>
            </ul>
            <a href="#services" class="inline-block mt-8 px-6 py-3 bg-[#ff6b00] rounded-2xl font-semibold hover:bg-orange-600">Jump to the index</a>
        </div>
    </div>
</section>

<section id="fire-uk" class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Fire · nationwide</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Fire links that work across the UK</h2>
        <p class="mt-3 text-zinc-600 max-w-3xl">
            Fire detection, emergency lighting, fire risk assessments and the rest of the life-safety range can be quoted across the UK mainland.
            Travel is from Offerton, Stockport SK2 and is confirmed on the quote.
            These links stay on the national hubs, so they do not 404 when the place is outside our North West town list.
        </p>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($allServices as $slug => $name):
                if (!isset($fireSlugs[$slug])) {
                    continue;
                }
                $href = fireNationwideServiceUrl($slug);
            ?>
            <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
               data-fire-nationwide="1"
               class="group bg-white border border-zinc-200 rounded-3xl p-5 hover:border-[#ff6b00] hover:shadow-lg transition">
                <div class="text-[11px] uppercase tracking-widest text-[#ff6b00] font-semibold">UK-wide</div>
                <h3 class="mt-2 font-semibold text-lg text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-2"><?= htmlspecialchars(getServiceBlurb($slug, true), ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-3 inline-block text-sm font-semibold text-[#ff6b00]">National hub →</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if ($fireKeywords): ?>
        <div class="mt-10">
            <h3 class="text-xl font-semibold text-black">Fire guides (national, not locked to <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>)</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ($fireKeywords as $slug => $meta): ?>
                    <a href="<?= htmlspecialchars(fireNationwideKeywordUrl($slug), ENT_QUOTES, 'UTF-8') ?>"
                       data-fire-nationwide="1"
                       class="px-3 py-1.5 bg-white border border-zinc-200 rounded-full text-xs font-medium text-zinc-800 hover:border-[#ff6b00] hover:text-[#ff6b00]">
                        <?= htmlspecialchars((string)($meta['name'] ?? keywordDisplayName($slug)), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="mt-8">
            <h3 class="text-xl font-semibold text-black">Same fire-alarms hub, any UK place</h3>
            <p class="mt-2 text-sm text-zinc-600 max-w-3xl">Each link below opens the national fire-alarms page. The place is only a fragment, so London or Glasgow does not depend on a North West town file.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ($ukPlaces as $place): ?>
                    <a href="<?= htmlspecialchars(fireNationwideServiceUrl('fire-alarms', $place), ENT_QUOTES, 'UTF-8') ?>"
                       data-fire-nationwide="1"
                       class="px-4 py-2 bg-white border rounded-full text-sm font-medium text-black hover:border-[#ff6b00]">
                        Fire alarms · <?= htmlspecialchars($place, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<section id="services" class="max-w-7xl mx-auto px-6 py-16">
    <div class="mb-10">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Full catalogue</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Every service for <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-2 text-zinc-600 max-w-2xl"><?= count($allServices) ?> services. Fire rows use the UK-wide hub. Everything else is the local link that already returns a page.</p>
    </div>
    <?php
    $listed = [];
    foreach ($categories as $catKey => $cat):
        $inCat = getServicesInCategory((string)$catKey);
        if (!$inCat) {
            continue;
        }
    ?>
    <div class="mb-12" id="cat-<?= htmlspecialchars(areaSlug((string)$catKey), ENT_QUOTES, 'UTF-8') ?>">
        <h3 class="text-2xl font-semibold text-black"><?= htmlspecialchars((string)($cat['label'] ?? $catKey), ENT_QUOTES, 'UTF-8') ?></h3>
        <?php
        $catBlurb = (string)($cat['blurb'] ?? '');
        if ((string)$catKey === 'fire-safety') {
            $catBlurb = 'Detection, suppression, doors, FRA and life-safety. Every link in this group opens a UK-wide hub.';
        }
        if ($catBlurb !== ''): ?>
            <p class="mt-2 text-sm text-zinc-600 max-w-3xl"><?= htmlspecialchars($catBlurb, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <div class="mt-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($inCat as $slug => $name):
                $listed[$slug] = true;
                $isFire = isset($fireSlugs[$slug]);
                $href = $serviceHref($slug);
            ?>
            <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
               <?= $isFire ? 'data-fire-nationwide="1"' : '' ?>
               class="group bg-white border border-zinc-200 rounded-3xl p-5 hover:border-[#ff6b00] hover:shadow-md transition flex flex-col">
                <div class="text-[11px] uppercase tracking-widest font-semibold <?= $isFire ? 'text-[#ff6b00]' : 'text-zinc-400' ?>">
                    <?= $isFire ? 'UK-wide fire link' : 'Local to ' . htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <h4 class="mt-2 font-semibold text-lg text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="text-sm text-zinc-600 mt-2 flex-1"><?= htmlspecialchars(getServiceBlurb($slug, true), ENT_QUOTES, 'UTF-8') ?></p>
                <span class="mt-3 text-sm font-semibold text-[#ff6b00]"><?= $isFire ? 'Open UK-wide page →' : 'View service →' ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php
    $orphans = array_diff_key($allServices, $listed);
    if ($orphans):
    ?>
    <div class="mb-12">
        <h3 class="text-2xl font-semibold text-black">Other services</h3>
        <div class="mt-5 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($orphans as $slug => $name):
                $isFire = isset($fireSlugs[$slug]);
            ?>
            <a href="<?= htmlspecialchars($serviceHref($slug), ENT_QUOTES, 'UTF-8') ?>"
               <?= $isFire ? 'data-fire-nationwide="1"' : '' ?>
               class="bg-white border rounded-3xl p-5 hover:border-[#ff6b00]">
                <h4 class="font-semibold text-black"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h4>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php if ($localKeywords): ?>
<section class="bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local guides</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Non-fire guides for <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-2 text-zinc-600 max-w-2xl">Electrical, gas and other local topics keep a <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> page. Fire topics stay on the national hubs above.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php foreach ($localKeywords as $slug => $meta): ?>
                <a href="<?= htmlspecialchars(url('/pages/keywords/' . $slug . '/' . $areaSlugVal . '.php'), ENT_QUOTES, 'UTF-8') ?>"
                   class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs font-medium text-zinc-800 hover:border-[#ff6b00]">
                    <?= htmlspecialchars((string)($meta['name'] ?? keywordDisplayName($slug)) . ' in ' . $areaName, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="related-links bg-white border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Local keyword pages</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Guides for <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-2 text-zinc-600 max-w-2xl">Every keyword guide has a <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> page, the same set the other Greater Manchester area hubs link.</p>
        <?php
        require_once SITE_ROOT . '/includes/related.php';
        echo keywordAreaLinksHtml($AREA, null, 0);
        ?>
    </div>
</section>

<?php if ($nearby): ?>
<section class="bg-zinc-50 border-t">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Nearby</div>
                <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Other towns we cover</h2>
            </div>
            <a href="<?= url('/pages/areas/index.php') ?>" class="text-sm font-semibold text-[#ff6b00]">All <?= count($allAreas) ?> areas →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($nearby as $town): ?>
                <a href="<?= url('/pages/areas/' . areaSlug($town) . '.php') ?>"
                   class="px-5 py-2.5 bg-white border rounded-full text-sm font-medium text-black hover:border-[#ff6b00]">
                    <?= htmlspecialchars($town, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
require_once SITE_ROOT . '/includes/gm-enrichment.php';
$areaFaqs = [
    [
        'How do I book work in ' . $areaName . '?',
        'Use the contact form or call ' . (defined('PHONE') ? PHONE : '07517806082') . ' with the ' . $areaName . ' postcode and the service you need. The quote is POA after scope. The office is 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE.',
    ],
    [
        'Do fire links on this ' . $areaName . ' index stay on national hubs?',
        'Yes. Fire safety links open the UK-wide service hubs so they stay valid outside the North West town list. Electrical, gas and building pages stay on the local ' . $areaName . ' routes.',
    ],
    [
        'Who carries out gas work for ' . $areaName . ' landlords?',
        'Gas work is carried out by Gas Safe registered engineers. This index does not show an iComply Gas Safe registration number.',
    ],
    [
        'Which other Greater Manchester hubs should I open?',
        'Stockport and Bolton use the same town-hub pattern as the rest of Greater Manchester. Manchester keeps this full service index, with the local guides linked above.',
    ],
];
echo icomplyGmFaqHtml('Questions about property services in ' . $areaName, $areaFaqs, 'site');
?>

<section class="bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
        <div>
            <h2 class="text-3xl font-semibold tracking-tight">Quote for <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mt-2 text-white/75">Say the postcode and the service. Fire jobs can be anywhere in the UK mainland; other trades are booked around <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/<?= htmlspecialchars(WHATSAPP, ENT_QUOTES, 'UTF-8') ?>?text=Quote%20for%20<?= htmlspecialchars($AREA_URL, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank" rel="noopener"
               class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold">WhatsApp</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>"
               class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 pt-8">
    <?= shareButtonsHtml($areaName . ' services index', $metaDesc) ?>
</section>

<?php
require_once SITE_ROOT . '/includes/testimonials.php';
echo testimonialsSectionHtml();
?>

<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request a <?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> quote</h2>
            <p class="mt-3 text-zinc-600">Postcode, property type and the service. No price is invented on this page.</p>
        </div>
        <form action="<?= url('/contact.php') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select service…</option>
                    <?php foreach ($allServices as $slug => $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($name . (isset($fireSlugs[$slug]) ? ' (UK-wide)' : ' in ' . $areaName), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required maxlength="5000"
                      placeholder="<?= htmlspecialchars($areaName, ENT_QUOTES, 'UTF-8') ?> postcode, property type, what you need…"
                      class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit request</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= url('/privacy.php') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= url('/terms.php') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
