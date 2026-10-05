<?php
/**
 * Nationwide AOV town pages for published places of 10,000+ residents.
 * Facts come from website/data/uk-towns-10k.json. Copy is assembled from
 * those facts plus a pair of technical modules so a page is not a renamed twin.
 */
declare(strict_types=1);

function aovPlaces(): array
{
    static $places = null;
    if ($places !== null) {
        return $places;
    }
    $file = SITE_ROOT . '/data/uk-towns-10k.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $places = [];
    if (is_array($decoded)) {
        foreach ($decoded as $row) {
            if (!is_array($row) || empty($row['slug'])) {
                continue;
            }
            $places[(string)$row['slug']] = $row;
        }
    }
    return $places;
}

function aovPlace(string $slug): ?array
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : $slug;
    return aovPlaces()[$slug] ?? null;
}

/** @return list<array{0:string,1:string}> heading, body */
function aovTechnicalModules(): array
{
    return [
        ['Rain sensors lose to a fire input', 'Comfort mode may shut a vent when it rains. Smoke mode has to win. On a visit we simulate the fire-alarm contact and watch whether a wet sensor still holds the stair vent closed. If it does, the panel logic or the sensor wiring is wrong, and the roof hatch is not a smoke vent until that is fixed.'],
        ['Batteries, not a green LED', 'AOV panels are supposed to open the vents after the mains has failed. We check battery date, charge voltage and whether the connected actuators still move on batteries. A silent fault LED after a reset is not evidence the standby supply can throw a roof vent.'],
        ['Free area is not the hole', 'Louvre blades, frames and bird mesh steal geometric free area. The figure that matters is the ventilator’s declared free area, not the structural opening somebody measured with a tape. If the fire strategy names a free area and the installed vent’s data sheet does not meet it, that is a defect, not a rounding error.'],
        ['The fire-alarm contact is a named input', 'Most stair vents should open from a volt-free contact the cause-and-effect describes, often fire in the stair or a lobby, not “any device in the building”. We test the input the drawing names. After an alarm-panel change, that relay is the first thing that goes missing.'],
        ['Roof hatches fail in the weather', 'Head-of-stair lids take wind, water and bird mess. A perished cable gland full of water presents as a dead actuator. We look at hinges, the closer and the cable crossing the roof before we condemn the motor. A lid left open after a test is a leak on the top landing as well as a controls fault.'],
        ['Shafts are a sequence', 'A smoke shaft works only if the inlets and the outlet open in the combination the design states. Opening the head vent while every lobby damper stays shut is not a pass. We do not enter a shaft that has no safe access, and we write “not accessed” rather than “satisfactory”.'],
        ['Natural vents and fans are different machines', 'A chain actuator on a stair window is a natural ventilator, usually talked about under BS EN 12101-2. A car-park or basement extract fan is powered smoke control, usually BS EN 12101-3. We will not price one as the other. The phone call has to establish which machine is actually on the building.'],
        ['What to send before we travel', 'A useful pack is the panel make, a photo of the fault display, which core the vent sits on, and whether the fire alarm was swapped recently. Roof work needs an agreed access method. We will not guess a cherry picker from “there is a vent on the roof”.'],
        ['Manual control at the landing', 'Residents and the fire service use the manual point, often at stair level, not the panel in a locked riser. We operate that switch as well as the panel output. A vent that only moves from the engineer’s laptop is not commissioned.'],
        ['Higher-risk residential blocks in England', 'In England, residential buildings at or above 18 metres sit in the higher-risk regime. Accountable persons ask whether the smoke control still does what the safety case says. We can test and record the vents. We are not the Building Safety Regulator and we do not write the safety case.'],
        ['Air handling is the other half of this service', 'Some plant rooms contain an AOV panel and an air-handling unit. The AHU’s fire interlock should stop comfort fans fighting a smoke path. We record the two systems on separate lines so a passed stair vent cannot hide a fan that only runs in hand.'],
        ['Actuator swap after the vent will move', 'Force is in newtons and travel is in millimetres. A replacement that looks similar can stall if the stroke is short or the hinges have dropped. We free a dragging casement before fitting a new chain. Obsolete 24 V bodies are identified from the label.'],
        ['Paperwork is a visit record', 'You get a note of which vents moved, battery condition and what failed. That is not a BAFE certificate, a fire-risk assessment or building-control completion. Failed devices stay failed on the sheet.'],
        ['Travel is priced, not implied by a town page', 'The yard is 17 Woodlands Park Road, Offerton, Stockport, SK2 5DE. Manchester is a short run. Places further away, including Scotland, Wales and Northern Ireland, are quoted with travel and nights written down. A page for a town is not a claim that a depot sits there.'],
    ];
}

function aovPlaceIntro(array $t): string
{
    $name = (string)$t['name'];
    $region = (string)$t['region'];
    $nation = (string)$t['nation'];
    $pop = number_format((int)$t['pop']);
    $year = (string)$t['year'];
    $source = (string)$t['source'];
    $size = trim((string)($t['size'] ?? ''));
    $work = trim((string)($t['workplace'] ?? ''));
    $income = trim((string)($t['income'] ?? ''));
    $coast = trim((string)($t['coastal'] ?? ''));
    $growth = trim((string)($t['growth'] ?? ''));
    $cluster = trim((string)($t['cluster'] ?? ''));

    $seed = crc32($name . '|' . $nation);
    $lead = [
        "{$name} ({$region}, {$nation}) has a published usual-resident population of {$pop} in {$year}. Source: {$source}. That figure is residents, not a count of blocks with smoke vents.",
        "For smoke-vent quotes in {$name} we start from the published geography: {$region}, {$nation}, {$pop} usual residents in {$year} ({$source}). We do not invent a local depot to match the place name.",
        "{$pop} people were the published usual residents of {$name} in {$year}. The place sits in {$region}, {$nation}. The source line is {$source}.",
    ][$seed % 3];

    $extra = [];
    if ($size !== '') {
        $extra[] = "Classification on that source: {$size}.";
    }
    if ($work !== '') {
        $extra[] = "Workplace profile in the ONS towns file: {$work}.";
    }
    if ($income !== '') {
        $extra[] = "Deprivation band on the same file: {$income}.";
    }
    if ($coast !== '') {
        $extra[] = "Coastal classification: {$coast}.";
    }
    if ($growth !== '' && is_numeric($growth)) {
        $extra[] = "ONS records population change of {$growth}% between 2001 and 2019 for this built-up area.";
    }
    if ($cluster !== '' && $nation === 'Scotland') {
        $extra[] = "The wider settlement name on the NRS table is {$cluster}.";
    }
    if ($nation === 'England' && str_contains($size, 'London')) {
        $extra[] = "{$name} is an ONS London built-up area ({$size}), which is not the same thing as a council boundary or a single stair core.";
    }
    if ($nation === 'Northern Ireland') {
        $extra[] = "Fire safety law and building regulations in Northern Ireland are not the English Approved Document B set. We quote to the strategy and the rules that apply there, and we say so on the visit record.";
    }
    if ($nation === 'Scotland') {
        $extra[] = "Scottish technical standards are not a renamed copy of English Approved Document B. The vent still has to match the fire strategy written for the building.";
    }
    if ($nation === 'Wales') {
        $extra[] = "Wales has its own building regulations. A smoke vent is still judged against the fire strategy for that building, not against an English town page.";
    }

    $order = $seed % max(1, count($extra));
    if ($extra) {
        $rotated = array_merge(array_slice($extra, $order), array_slice($extra, 0, $order));
        $lead .= ' ' . implode(' ', $rotated);
    }
    return $lead;
}

/** @return list<array{name:string,slug:string}> */
function aovNearbyPlaces(array $t, int $limit = 4): array
{
    $out = [];
    foreach (aovPlaces() as $slug => $other) {
        if ($slug === ($t['slug'] ?? '') || ($other['region'] ?? '') !== ($t['region'] ?? '')) {
            continue;
        }
        $other['_d'] = abs((int)$other['pop'] - (int)$t['pop']);
        $out[] = $other;
    }
    usort($out, static function ($a, $b) {
        return ($a['_d'] <=> $b['_d']);
    });
    $picked = [];
    foreach (array_slice($out, 0, $limit) as $row) {
        $picked[] = ['name' => (string)$row['name'], 'slug' => (string)$row['slug']];
    }
    return $picked;
}

function aovPlaceArticle(array $t): array
{
    $modules = aovTechnicalModules();
    $n = count($modules);
    $seed = crc32((string)$t['slug'] . '|aov');
    $i1 = $seed % $n;
    $i2 = ($seed + 5 + ($seed % 7)) % $n;
    if ($i2 === $i1) {
        $i2 = ($i1 + 3) % $n;
    }
    $pair = [$modules[$i1], $modules[$i2]];
    if (($seed % 2) === 1) {
        $pair = array_reverse($pair);
    }

    $name = (string)$t['name'];
    $title = "AOV and smoke vents in {$name}";
    $meta = "Automatic opening vents and smoke control for {$name}, {$t['region']}. Supply kits priced, installation POA. Call " . PHONE . ". No scheme badge claimed.";
    $h1 = "Smoke vents in {$name}";
    if (($seed % 4) === 1) {
        $h1 = "Automatic opening vents for {$name}";
    } elseif (($seed % 4) === 2) {
        $h1 = "{$name} stair and corridor smoke control";
    } elseif (($seed % 4) === 3) {
        $h1 = "AOV repairs and tests in {$name}";
    }

    $extra = '';
    if (($t['slug'] ?? '') === 'manchester') {
        $h1 = 'Smoke vents on Manchester blocks';
        $extra = 'Manchester work is usually a head-of-stair roof vent on a purpose-built core, or a corridor vent into a shaft, across M postcodes from the city centre through Hulme, Ardwick and the apartment stock towards Ancoats. Wind-driven rain on exposed lids and a fire-alarm swap that drops the interface are the two faults we ask about first. Higher-risk residential buildings need a test record for the safety case. We supply the record. We are not the Building Safety Regulator. From Stockport SK2 the drive is often under 40 minutes, which is why Manchester visits are the easiest to fit around the diary.';
    } elseif (($t['slug'] ?? '') === 'burnley') {
        $h1 = 'Automatic opening vents in Burnley';
        $extra = 'Burnley (BB10–BB12) is mill conversions and newer infill blocks, plus industrial air handling on the estates toward the M65, not a glass city-centre atrium. Retrofit louvres on converted stairs often have no free-area note in the file. ONS puts the 2019 usual residents at 83,364 and classes the built-up area as a larger non-coastal working town. The drive from Stockport is typically 55–75 minutes, so East Lancashire visits are batched. We will not pretend it is a 20-minute call-out. Pennine rain seizes roof hatches; a lid that will not close is both a leak and a failed smoke vent.';
    }

    return [
        'title' => $title,
        'meta' => $meta,
        'h1' => $h1,
        'intro' => aovPlaceIntro($t),
        'modules' => $pair,
        'extra' => $extra,
        'nearby' => aovNearbyPlaces($t),
    ];
}

function aovRenderDirectory(): void
{
    require SITE_ROOT . '/templates/aov/directory.php';
}

function aovRenderPlace(string $slug): void
{
    $place = aovPlace($slug);
    if ($place === null) {
        header('Location: ' . url('/pages/aov'), true, 301);
        if (function_exists('icomplyRequestExit')) {
            icomplyRequestExit();
        }
        return;
    }
    $article = aovPlaceArticle($place);
    require SITE_ROOT . '/templates/aov/town.php';
}
