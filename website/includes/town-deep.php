<?php
declare(strict_types=1);

/**
 * Town-specific depth for service x town pages, built only from published data:
 *  - data/town-geo.json (postcodes.io: postcode districts, wards, councils, constituencies, lat/lon)
 *  - data/barriers-places.json (ONS Census 2021 built-up areas / NRS) or data/la-census.json (whole council)
 * Every number is quoted with its source. No reviews, projects or invented stats.
 */
require_once __DIR__ . '/town-depth.php';

function icomplyTownDeepGroup(string $service): string
{
    $s = $service;
    if (preg_match('/fire|emergency-lighting|aov|smoke|sprink|suppression|dry-riser|extinguish/', $s)) return 'fire';
    if (preg_match('/eicr|electric|pat|rewir|consumer|ev-|solar|lighting/', $s)) return 'electrical';
    if (preg_match('/gas|boiler|cp12|heating|legionella|plumb/', $s)) return 'gas';
    if (preg_match('/cctv|alarm|access|intercom|security|barrier|gate|fenc|door-entry|nurse-call/', $s)) return 'security';
    if (preg_match('/hmo|landlord|epc|inventory|asbestos|damp|survey|licen/', $s)) return 'landlord';
    if (preg_match('/build|roof|brick|plaster|kitchen|bathroom|extension|renov|decor|floor|window|joinery|carpent/', $s)) return 'building';
    return 'general';
}

/** @return array<string,float> label=>pct */
function icomplyTownDeepRows(?array $rows): array
{
    $o = [];
    foreach ((array)$rows as $r) {
        if (is_array($r) && isset($r['label'], $r['pct'])) {
            $o[(string)$r['label']] = (float)$r['pct'];
        } elseif (is_array($r) && isset($r[0], $r[2])) {
            $o[(string)$r[0]] = (float)$r[2];
        }
    }
    return $o;
}

function icomplyTownDeepFind(array $rows, string $needle): ?float
{
    foreach ($rows as $k => $v) {
        if (stripos($k, $needle) !== false) return $v;
    }
    return null;
}

function icomplyTownDeepHtml(string $slug, string $town, string $serviceName, string $serviceSlug): string
{
    $g = townDepthGeo($slug);
    if (!$g) {
        return '';
    }
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $group = icomplyTownDeepGroup($serviceSlug);
    $seed = crc32($slug . '|' . $serviceSlug . '|deep');
    $council = townDepthCouncil($g);
    $out = '<section id="town-deep" class="max-w-7xl mx-auto px-6 py-12 space-y-8" data-town-deep="1">';

    // 1. Postcode districts and wards (postcodes.io).
    $districts = array_values(array_filter((array)($g['districts'] ?? []), static fn($d) => !empty($d['oc'])));
    if ($districts) {
        $out .= '<div><h2 class="text-2xl font-semibold text-black">' . $h(townDepthPick($seed, ["Postcode districts and wards across {$town}", "{$town} by postcode district", "Where {$town} addresses fall: districts and wards"])) . '</h2>';
        $out .= '<p class="mt-2 text-sm text-zinc-600">' . $h('Postcode districts nearest the centre of ' . $town . ', with the council wards each one touches (postcodes.io, ONS boundaries). Quote requests are scoped to the full postcode.') . '</p>';
        $out .= '<table class="mt-4 w-full text-sm border"><thead><tr class="bg-zinc-50"><th class="text-left p-2">District</th><th class="text-left p-2">Wards</th><th class="text-left p-2">Council</th></tr></thead><tbody>';
        foreach (array_slice($districts, 0, 8) as $d) {
            $w = array_values(array_filter((array)($d['wards'] ?? []), static fn($x) => stripos((string)$x, 'tunstall') === false));
            $out .= '<tr><td class="p-2 font-semibold">' . $h((string)$d['oc']) . '</td><td class="p-2">' . $h(implode('; ', $w)) . '</td><td class="p-2">' . $h((string)($d['council'] ?? $council)) . '</td></tr>';
        }
        $out .= '</tbody></table>';
        $cons = (array)($g['constituencies'] ?? []);
        if ($cons) {
            $out .= '<p class="mt-2 text-sm text-zinc-600">' . $h((count($cons) > 1 ? 'Parliamentary constituencies covering these districts: ' : 'Parliamentary constituency: ') . townDepthList($cons) . '.') . '</p>';
        }
        $out .= '</div>';
    }

    // 2. Census.
    $c = townDepthCensus($slug);
    $scope = $town;
    $src = '';
    if ($c && !empty($c['housing'])) {
        $src = (string)($c['source'] ?? 'ONS Census 2021') . (!empty($c['code']) ? ' ' . $c['code'] : '');
    } else {
        $la = townDepthLaCensus($council);
        if ($la) {
            $c = ['housing' => $la['housing'], 'tenure' => $la['tenure']];
            $scope = "the {$council} council area (whole authority)";
            $src = (string)($la['source'] ?? 'ONS Census 2021 TS044/TS054') . ', ' . $la['code'] . ', whole council area';
        } else {
            $c = null;
        }
    }
    if ($c) {
        $hs = icomplyTownDeepRows($c['housing'] ?? []);
        $tn = icomplyTownDeepRows($c['tenure'] ?? []);
        $ind = icomplyTownDeepRows($c['industry'] ?? []);
        $occ = icomplyTownDeepRows($c['occupation'] ?? []);
        $out .= '<div><h2 class="text-2xl font-semibold text-black">' . $h(townDepthPick($seed >> 3, ["Homes and households in {$town}", "{$town} housing stock in numbers", "Who lives in {$town}: census housing figures"])) . '</h2>';
        $facts = [];
        if (!empty($c['population'])) {
            $facts[] = number_format((float)$c['population']) . ' usual residents';
        }
        if (!empty($c['median_age'])) {
            $facts[] = 'median age ' . $c['median_age'];
        }
        if (!empty($c['employed_pct'])) {
            $facts[] = $c['employed_pct'] . '% of over-16s in work';
        }
        if ($facts) {
            $out .= '<p class="mt-2 text-zinc-700">' . $h(ucfirst($scope) . ': ' . implode(', ', $facts) . '.') . '</p>';
        }
        $out .= '<div class="mt-4 grid md:grid-cols-2 gap-6">';
        foreach (['Accommodation type' => $hs, 'Tenure' => $tn, 'Largest industries (residents)' => $ind, 'Occupations (residents)' => $occ] as $label => $rows) {
            if (!$rows) continue;
            arsort($rows);
            $out .= '<table class="w-full text-sm border"><caption class="text-left font-semibold p-2">' . $h($label) . '</caption><tbody>';
            foreach (array_slice($rows, 0, 5, true) as $k => $v) {
                $out .= '<tr><td class="p-2">' . $h(str_replace('Whole house or bungalow: ', '', $k)) . '</td><td class="p-2 text-right">' . $h(round($v, 1) . '%') . '</td></tr>';
            }
            $out .= '</tbody></table>';
        }
        $out .= '</div><p class="mt-2 text-xs text-zinc-500">' . $h('Source: ' . $src . '. Shares of households or residents, rounded. They describe homes and people, not installed equipment or our customers.') . '</p>';

        // 3. What it means for this service, computed from the figures above.
        $flats = icomplyTownDeepFind($hs, 'Flat');
        $det = icomplyTownDeepFind($hs, 'Detached');
        $priv = icomplyTownDeepFind($tn, 'Private');
        $soc = icomplyTownDeepFind($tn, 'ocial');
        $outright = icomplyTownDeepFind($tn, 'outright');
        $m = [];
        $fmt = static fn(?float $x): string => $x === null ? '' : (string)round($x, 1) . '%';
        if ($flats !== null) {
            if ($group === 'fire') {
                $m[] = $flats >= 25
                    ? "With flats at {$fmt($flats)} of homes in {$scope}, a large share of {$serviceName} work here is in the common parts of purpose-built and converted blocks, where the Fire Safety Order puts duties on the responsible person."
                    : "Flats are {$fmt($flats)} of homes in {$scope}, so {$serviceName} requests lean towards shops, offices, schools, care settings and the smaller number of blocks rather than ordinary houses.";
            } elseif ($group === 'electrical') {
                $m[] = "Flats are {$fmt($flats)} of homes in {$scope}; landlord electrical checks in blocks often split between the flat's own installation and the landlord's supply to common parts.";
            } elseif ($group === 'gas') {
                $m[] = "Flats make up {$fmt($flats)} of homes in {$scope}. In flats, flue routes and appliance position matter for a gas safety check, so the quote asks what is fitted.";
            } elseif ($group === 'security') {
                $m[] = $flats >= 25
                    ? "{$fmt($flats)} of homes in {$scope} are flats, so shared entrances, bin stores and car parks are a frequent {$serviceName} brief."
                    : "Only {$fmt($flats)} of homes in {$scope} are flats; most {$serviceName} enquiries come from businesses, schools and houses.";
            } else {
                $m[] = "Flats are {$fmt($flats)} of homes in {$scope}" . ($det !== null ? " and detached houses {$fmt($det)}" : '') . ", which shapes access, parking and how a {$serviceName} visit is planned.";
            }
        }
        if ($priv !== null) {
            $m[] = match ($group) {
                'electrical' => "Private renting accounts for {$fmt($priv)} of households in {$scope}. In England, private landlords need an electrical installation condition report at least every five years.",
                'gas' => "Private renting is {$fmt($priv)} of households in {$scope}; landlords need an annual gas safety record for each let property with gas appliances.",
                'landlord' => "{$fmt($priv)} of households in {$scope} rent privately, which is the market most {$serviceName} work serves.",
                'fire' => "{$fmt($priv)} of households in {$scope} rent privately; HMOs and converted flats in that stock usually need a fire risk assessment of the shared parts.",
                default => "{$fmt($priv)} of households in {$scope} rent privately" . ($soc !== null ? " and {$fmt($soc)} rent from a council or housing association" : '') . '.',
            };
        }
        if ($outright !== null && $group !== 'landlord') {
            $m[] = "{$fmt($outright)} of households own their home outright, so they arrange and pay for {$serviceName} work directly rather than through a landlord.";
        }
        if ($ind) {
            arsort($ind);
            $top = array_key_first($ind);
            $m[] = 'The largest industry among working residents is ' . strtolower((string)$top) . ' (' . $fmt($ind[$top]) . ').';
        }
        if ($m) {
            $out .= '<h3 class="mt-6 text-xl font-semibold text-black">' . $h("What those figures mean for {$serviceName} in {$town}") . '</h3>';
            foreach ($m as $line) {
                $out .= '<p class="mt-2 text-zinc-700 leading-relaxed">' . $h($line) . '</p>';
            }
        }
        $out .= '</div>';
    }

    // 4. Neighbouring towns by distance (text only; no links, so no risk of dead routes).
    $all = [];
    foreach (townDepthAllGeo() as $s => $og) {
        if ($s === $slug) continue;
        $mi = townDepthMiles((float)$g['lat'], (float)$g['lon'], (float)$og['lat'], (float)$og['lon']);
        if ($mi >= 0.5 && $mi <= 15) {
            $all[] = [(string)(townDepthOns($s)['name'] ?? ucwords(str_replace('-', ' ', $s))), $mi, townDepthBearing((float)$g['lat'], (float)$g['lon'], (float)$og['lat'], (float)$og['lon'])];
        }
    }
    usort($all, static fn($a, $b) => $a[1] <=> $b[1]);
    if ($all) {
        $bits = array_map(static fn($x) => $x[0] . ' (' . round($x[1], 1) . ' mi ' . $x[2] . ')', array_slice($all, 0, 8));
        $out .= '<div><h2 class="text-2xl font-semibold text-black">' . $h("Towns around {$town}") . '</h2><p class="mt-2 text-zinc-700">' . $h('Straight-line distance from central ' . $town . ': ' . implode(', ', $bits) . '.') . '</p></div>';
    }
    return $out . '</section>';
}
