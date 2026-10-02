<?php
/**
 * One town barrier page. Expects $place and $brands.
 */
$name = (string)$place['name'];
$slug = (string)$place['slug'];
$country = (string)($place['country'] ?? '');
$region = (string)($place['region'] ?? '');
$population = (int)($place['population'] ?? 0);
$reading = (string)($place['reading'] ?? '');
$paragraphs = barriersReadingParagraphs($reading);
$pageTitle = 'Vehicle Barriers in ' . $name;
$metaDesc = $name . ' vehicle barriers. Official population ' . number_format($population) . '. CAME partner supply from Stockport, price on application. ' . PHONE . '.';
$metaKeywords = 'vehicle barriers ' . $name . ', rising arm barrier ' . $name . ', CAME Gard, ' . $region . ', price on application';
$canonicalUrl = url('/pages/barriers/' . $slug);
$ogImage = url('/assets/images/services/access-control.jpg');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$where = $region !== '' && $region !== $country ? $region . ', ' . $country : $country;
$statGroups = [
    'Accommodation' => $place['housing'] ?? [],
    'Tenure' => $place['tenure'] ?? [],
    'Industry' => $place['industry'] ?? [],
    'Occupation' => $place['occupation'] ?? [],
];

require SITE_ROOT . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => 'Vehicle barriers in ' . $name,
            'description' => $metaDesc,
            'url' => $canonicalUrl,
            'areaServed' => [
                '@type' => 'City',
                'name' => $name,
                'containedInPlace' => $country,
            ],
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => SITE_NAME,
                'telephone' => PHONE,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Stockport',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Vehicle barriers', 'item' => url('/pages/services/barriers')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $canonicalUrl],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/60 mb-6 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/services/barriers') ?>" class="hover:text-white">Vehicle barriers</a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <p class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold"><?= htmlspecialchars($where, ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="mt-3 text-4xl sm:text-5xl font-semibold tracking-tight">Vehicle barriers in <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="mt-5 text-lg text-white/80 max-w-3xl">
            <?= htmlspecialchars(number_format($population), ENT_QUOTES, 'UTF-8') ?> usual residents in the official count for this place.
            A barrier visit is planned from our Stockport SK2 base. The price is on application.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] font-semibold">Request a survey</a>
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="grid lg:grid-cols-5 gap-12">
        <div class="lg:col-span-3">
            <h2 class="text-2xl font-semibold text-black">This place</h2>
            <div class="mt-4 space-y-4 text-lg text-zinc-800 leading-relaxed">
                <?php foreach ($paragraphs as $para): ?>
                    <p><?= htmlspecialchars($para, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
            <p class="mt-6 text-sm text-zinc-500">
                Source: <?= htmlspecialchars((string)($place['source'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($place['code'])): ?>
                    · code <?= htmlspecialchars((string)$place['code'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
                . Counts follow that publisher's rounding. They are not a count of barriers already installed in <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>.
            </p>
        </div>
        <aside class="lg:col-span-2 space-y-4">
            <div class="bg-[#0B1F3A] text-white rounded-3xl p-6">
                <div class="text-xs uppercase tracking-wide text-[#ff6b00] font-semibold">CAME partner</div>
                <p class="mt-2 text-sm text-white/80">New rising arms are CAME Gard. This town page does not mean a CAME cabinet is already here.</p>
                <a class="inline-block mt-4 text-sm font-semibold underline" href="<?= url('/pages/manufacturers/came') ?>">CAME page</a>
                <span class="text-white/40"> · </span>
                <a class="inline-block mt-4 text-sm font-semibold underline" href="<?= url('/pages/keywords/came-gard-barrier') ?>">Gard guide</a>
                <span class="text-white/40"> · </span>
                <a class="inline-block mt-4 text-sm font-semibold underline" href="<?= url('/pages/keywords/came-partner') ?>">Partnership</a>
            </div>
            <div class="border rounded-3xl p-6">
                <div class="text-sm text-zinc-500">Population</div>
                <div class="text-3xl font-semibold"><?= number_format($population) ?></div>
                <?php if (isset($place['median_age'])): ?>
                    <div class="mt-3 text-sm text-zinc-600">Median age <?= htmlspecialchars((string)$place['median_age'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (isset($place['employed_pct'])): ?>
                    <div class="mt-1 text-sm text-zinc-600">Employed, 16 and over: <?= htmlspecialchars((string)$place['employed_pct'], ENT_QUOTES, 'UTF-8') ?>%</div>
                <?php endif; ?>
                <?php if (!empty($place['age_bands']) && is_array($place['age_bands'])): ?>
                    <?php
                    $ageLabels = ['under16' => 'Under 16', 'working' => 'Working age', 'over65' => '65 and over'];
                    $ageTotal = 0;
                    foreach ($place['age_bands'] as $ageValue) {
                        if (is_numeric($ageValue)) {
                            $ageTotal += (int)$ageValue;
                        }
                    }
                    ?>
                    <ul class="mt-3 text-sm text-zinc-700 space-y-1">
                        <?php foreach ($place['age_bands'] as $band => $bandRow): ?>
                            <?php if (is_array($bandRow)): ?>
                                <li><?= htmlspecialchars((string)($ageLabels[$band] ?? $band), ENT_QUOTES, 'UTF-8') ?>:
                                    <?= htmlspecialchars(barriersFormatCount($bandRow['count'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars((string)($bandRow['pct'] ?? ''), ENT_QUOTES, 'UTF-8') ?>%)</li>
                            <?php elseif (is_numeric($bandRow)): ?>
                                <li><?= htmlspecialchars((string)($ageLabels[$band] ?? $band), ENT_QUOTES, 'UTF-8') ?>:
                                    <?= number_format((int)$bandRow) ?>
                                    <?php if ($ageTotal > 0): ?>(<?= htmlspecialchars((string)round(((int)$bandRow) / $ageTotal * 100, 1), ENT_QUOTES, 'UTF-8') ?>%)<?php endif; ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <?php
    $hasStats = false;
    foreach ($statGroups as $rows) {
        if ($rows) {
            $hasStats = true;
            break;
        }
    }
    ?>
    <?php if ($hasStats): ?>
    <div class="mt-12 grid md:grid-cols-2 gap-6">
        <?php foreach ($statGroups as $label => $rows): ?>
            <?php if (!$rows) continue; ?>
            <div class="border rounded-3xl overflow-hidden">
                <h2 class="px-5 py-3 bg-zinc-50 font-semibold"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></h2>
                <table class="w-full text-sm">
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php if (!is_array($row)) continue; ?>
                        <tr class="border-t">
                            <td class="px-5 py-2"><?= htmlspecialchars((string)($row['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-3 py-2 text-right whitespace-nowrap"><?= htmlspecialchars(barriersFormatCount($row['count'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="px-5 py-2 text-right whitespace-nowrap"><?= htmlspecialchars((string)($row['pct'] ?? ''), ENT_QUOTES, 'UTF-8') ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($place['neighbours']) && is_array($place['neighbours'])): ?>
        <div class="mt-10">
            <h2 class="text-xl font-semibold">Nearby barrier pages</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                <?php foreach ($place['neighbours'] as $neighbour): ?>
                    <?php
                    $neighbour = (string)$neighbour;
                    $neighbourSlug = barriersPlaceSlugByName($neighbour);
                    ?>
                    <?php if ($neighbourSlug): ?>
                        <a class="px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="<?= url('/pages/barriers/' . $neighbourSlug) ?>"><?= htmlspecialchars($neighbour, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php else: ?>
                        <span class="px-4 py-2 border rounded-full text-sm"><?= htmlspecialchars($neighbour, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-2xl font-semibold">Brands, if a cabinet is already on the lane</h2>
        <p class="mt-2 text-zinc-600 max-w-3xl">CAME is the partner for a new arm. Any other name is service or replacement.</p>
        <div class="mt-5 flex flex-wrap gap-2">
            <?php foreach ($brands as $brandSlug => $entry): ?>
                <a class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00]" href="<?= url('/pages/manufacturers/' . $brandSlug) ?>">
                    <?= htmlspecialchars((string)$entry['name'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($entry['partner'])): ?><span class="text-[#ff6b00]"> · partner</span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="quote" class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <?php
        $services = getServices();
        $selectedService = 'Vehicle Barriers';
        $heading = 'Barrier quote for ' . $name;
        $sub = 'Price on application. Tell us the postcode and the brand on the cabinet if there is one.';
        require SITE_ROOT . '/includes/quote-form.php';
        ?>
    </div>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
