<?php
/**
 * Facts-only local block for static service x town and keyword x town matrix
 * pages (fire family, fencing, AOV, barriers). PHP twin of netlify/lib/town-facts.js.
 * Data: data/town-geo-slim.json (postcodes.io / ONSPD; census from ONS 2021).
 */
declare(strict_types=1);

function icomplyTownFactsGeo(string $slug): ?array
{
    static $geo = null;
    if ($geo === null) {
        $file = SITE_ROOT . '/data/town-geo-slim.json';
        $d = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $geo = is_array($d) ? $d : [];
    }
    return $geo[$slug] ?? null;
}

/** Town facts now apply to every service family (real geo/census only); keep a list for opt-outs. */
function icomplyTownFactsEnabled(string $service): bool
{
    return $service !== '' && !in_array($service, icomplyTownFactsOptOut(), true);
}

function icomplyTownFactsOptOut(): array
{
    return [];
}

function icomplyTownFactsServices(): array
{
    return ['fire-alarms', 'emergency-lighting', 'fire-risk-assessments', 'fire-extinguishers', 'fire-doors', 'fire-stopping',
        'fire-suppression', 'fire-signage', 'fire-compartmentation', 'kitchen-fire-suppression', 'aov-air-handling', 'barriers', 'fencing'];
}

function icomplyTownFactsHtml(string $slug, string $town, string $subject, string $service): string
{
    if (!icomplyTownFactsEnabled($service)) {
        return '';
    }
    $g = icomplyTownFactsGeo($slug);
    if (!$g) {
        return '';
    }
    [$council, $county, $ocs, $wards, $cons, $lat, $lng, $census] = $g + [null, null, [], [], '', 0, 0, null];
    require_once SITE_ROOT . '/includes/town-depth.php';
    $mi = (int)round(townDepthMiles(TOWN_DEPTH_YARD[0], TOWN_DEPTH_YARD[1], (float)$lat, (float)$lng));
    $dir = townDepthBearing(TOWN_DEPTH_YARD[0], TOWN_DEPTH_YARD[1], (float)$lat, (float)$lng);
    $seed = crc32($slug . '|' . $service);
    $p = [];
    $lead = $council ? "{$town} sits in the {$council} council area" . ($county && $county !== $council ? ", {$county}" : '') . '.' : "{$town}" . ($county ? " is in {$county}" : '') . '.';
    if ($ocs) {
        $lead .= townDepthPick($seed, [
            ' Postcode districts around the centre: ' . townDepthList($ocs) . '.',
            ' Addresses here mostly start ' . townDepthList($ocs, 'or') . '.',
        ]);
    }
    if ($wards) {
        $lead .= ' Wards in those districts include: ' . implode('; ', $wards) . '.';
    }
    if ($cons) {
        $lead .= " Constituency: {$cons}.";
    }
    $p[] = $lead;
    $p[] = $mi <= 3
        ? "Our yard at Offerton, Stockport SK2 5DE is close by, so visits in {$town} fit around other local work."
        : "{$town} is about {$mi} miles {$dir} of our Offerton, Stockport SK2 5DE yard in a straight line. " . ($mi <= 25 ? 'That is a day visit, with travel as a small line on the quote.' : 'Travel is written on the quote.');
    if (is_array($census)) {
        [$flats, $social, $ind, $indPct, $scope] = $census + [null, null, null, null, null];
        $where = ($scope === 'la' || $scope === 'las') ? "the {$council} council area (whole authority)" : $town;
        $bits = [];
        if ($flats !== null) {
            $bits[] = "flats are {$flats}% of homes";
        }
        if ($social !== null) {
            $bits[] = "social renting is {$social}% of households";
        }
        if ($bits) {
            $line = "In {$where}, " . townDepthList($bits) . ($scope === 'las' ? " (Scotland's Census 2022)." : ' (ONS Census 2021).');
            if (str_starts_with($service, 'fire') || $service === 'emergency-lighting' || $service === 'kitchen-fire-suppression') {
                $line .= ' Common parts of flats and HMOs carry duties under the Regulatory Reform (Fire Safety) Order 2005 in England and Wales, or the Fire (Scotland) Act 2005 in Scotland.';
            } elseif ($service === 'fencing') {
                $line .= ' Boundary fencing questions usually follow the housing mix: shared grounds at flat blocks, garden boundaries on houses.';
            }
            if ($ind) {
                $line .= ' The largest employment group among residents is ' . strtolower((string)$ind) . " ({$indPct}%).";
            }
            $p[] = $line;
        }
    }
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $out = '<section id="town-facts" class="space-y-3"><h2 class="text-2xl font-semibold">' . $h("{$town}: council, postcodes and travel") . '</h2>';
    foreach ($p as $para) {
        $out .= '<p class="text-zinc-700 leading-relaxed">' . $h($para) . '</p>';
    }
    return $out . '</section>';
}
