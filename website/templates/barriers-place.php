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
$ogImage = url('/assets/images/services/barriers-photo.jpg');

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
            <a href="tel:<?= preg_replace('/\s+/', '', PHONE) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold"><?= htmlspecialchars(icomplyPhoneDisplay(), ENT_QUOTES, 'UTF-8') ?></a>
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
<?php
// QM #156 under_800_words: barrier planning notes driven by this place's own census figures.
$bpPct = static function (array $rows, string $needle): float {
    foreach ($rows as $r) { if (stripos((string)($r['label'] ?? ''), $needle) !== false) { return (float)($r['pct'] ?? 0); } }
    return 0.0;
};
$bpFlats = $bpPct((array)($place['housing'] ?? []), 'Flat');
$bpSocial = $bpPct((array)($place['tenure'] ?? []), 'Social rented');
$bpPrivate = $bpPct((array)($place['tenure'] ?? []), 'Private rented');
$bpTopInd = (string)(((array)($place['industry'] ?? []))[0]['label'] ?? '');
$bpAge = (int)($place['median_age'] ?? 0);
$bpN = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$bpP = [];
$bpP[] = $population >= 100000
    ? "{$bpN} is a large place, with " . number_format($population) . " residents at the last census, so barrier work here is spread across retail parks, hospital and office car parks, apartment developments and industrial estates. Larger sites often have more than one lane, an intercom to a staffed desk and a link to the site's access control, and we survey each lane separately."
    : ($population >= 30000
        ? "With " . number_format($population) . " residents at the last census, {$bpN} has a mix of town-centre car parks, business units and residential developments with their own parking. Most barrier jobs are one or two lanes, and the main questions are who needs to get in, how they will be recognised, and what happens when the power or the network is down."
        : "{$bpN} is a smaller place, with " . number_format($population) . " residents at the last census. Barriers here are usually on a single private car park: a block of flats, a surgery, a works yard or a pub. A single rising-arm barrier with a keypad, fob reader or app control usually does the job, with a loop to stop the arm closing on a vehicle.");
if ($bpFlats >= 20) {
    $bpP[] = "Flats, maisonettes and apartments make up about " . number_format($bpFlats, 0) . "% of homes in {$bpN}, which is high enough that residents' car parks are a regular source of barrier enquiries. For a residents' car park, think about how fobs are issued and cancelled when people move, how visitors and deliveries get in, and how the fire service and ambulances get through. A barrier must never block an emergency route, and many blocks fit a fire-alarm release or a key switch for the fire service.";
} else {
    $bpP[] = "Only about " . number_format($bpFlats, 0) . "% of homes in {$bpN} are flats, so most barrier enquiries come from businesses rather than residents: yards, depots, surgeries, schools and private car parks. For a business site the key decisions are opening hours, whether the barrier should stay up during the day, how staff and visitors are recognised, and how deliveries reach the loading area without someone walking out to the gate.";
}
if ($bpSocial + $bpPrivate >= 35) {
    $bpP[] = "Renting is common in {$bpN} (around " . number_format($bpSocial, 0) . "% social and " . number_format($bpPrivate, 0) . "% private rented), so managing agents and housing providers commission much of the work. They usually need a written maintenance schedule, a log of tests and repairs, and clear responsibility for lost fobs and damage, and we set those out in the quote.";
}
if ($bpTopInd !== '') {
    $bpP[] = "The largest employment sector recorded for {$bpN} is " . htmlspecialchars(strtolower(preg_replace('/;.*$/', '', $bpTopInd)), ENT_QUOTES, 'UTF-8') . ". Sites in that sector often need barriers that cope with shift changes, so we look at traffic at peak times, arm speed and whether a second lane or an exit loop is needed to stop queues backing onto the road.";
}
if ($bpAge >= 45) {
    $bpP[] = "The median age in {$bpN} is {$bpAge}, older than the national figure, which matters for retirement developments and care homes: intercoms with large buttons, longer opening times and clear signs help residents and visitors who drive less often.";
}
$bpReg = htmlspecialchars((string)($place['region'] ?? ''), ENT_QUOTES, 'UTF-8');
$bpP[] = "Before installation in {$bpN}" . ($bpReg !== '' ? " ({$bpReg})" : '') . " we check the ground where the barrier base will sit, whether a duct can be cut across the lane for the loop and cabling, and where the nearest suitable power supply is. Tarmac, block paving and concrete each need a different base, and a shared driveway may need the landlord's or neighbours' consent first. We also note how wide the lane is, whether the arm needs a skirt or a support post, and whether the barrier should open automatically for vehicles leaving.";
$bpP[] = "Every barrier we quote in {$bpN} is supplied as a CAME partner from Stockport, with installation price on application after a survey of the lane, the ground, the power supply and any ducting. Ongoing servicing checks the arm, the safety loops and photocells, the control board and the battery backup, and we record each visit for the site's maintenance file.";
?>
<section class="max-w-7xl mx-auto px-6 py-10">
    <h2 class="text-2xl font-semibold text-black">Planning a vehicle barrier in <?= $bpN ?></h2>
    <?php foreach ($bpP as $bpLine): ?><p class="mt-3 text-zinc-700 leading-relaxed"><?= $bpLine ?></p><?php endforeach; ?>
</section>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
