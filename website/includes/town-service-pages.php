<?php
/**
 * AOV and Barriers pages for every UK mainland town over 10,000 people.
 * Editorial copy is chosen per town so pages are not a single swapped template.
 */
declare(strict_types=1);

require_once __DIR__ . '/uk-towns.php';
require_once __DIR__ . '/gm-service-towns.php';

function icomplyTownFamilies(): array
{
    return ['aov', 'barriers'];
}

function icomplyTownBrandRows(string $family): array
{
    static $cache = [];
    if (isset($cache[$family])) {
        return $cache[$family];
    }
    if ($family === 'barriers') {
        $data = loadJsonData('town-manufacturers', []);
        $rows = [];
        foreach ($data['barriers'] ?? [] as $row) {
            if (!is_array($row) || empty($row['name'])) {
                continue;
            }
            if (stripos((string)$row['name'], 'tunstall') !== false) {
                continue;
            }
            $rows[] = [
                'name' => (string)$row['name'],
                'slug' => areaSlug((string)($row['slug'] ?? $row['name'])),
                'partner' => !empty($row['partner']),
                'group' => (string)($row['group'] ?? 'Barrier automation'),
                'note' => (string)($row['note'] ?? ''),
            ];
        }
        $cache[$family] = $rows;
        return $rows;
    }
    $rows = [];
    foreach (getManufacturers('aov-air-handling') as $name) {
        $name = (string)$name;
        if ($name === '' || stripos($name, 'tunstall') !== false) {
            continue;
        }
        $rows[] = [
            'name' => $name,
            'slug' => areaSlug($name),
            'partner' => false,
            'group' => 'AOV and smoke control',
            'note' => $name . ' actuators, vents or panels serviced and replaced to the fire strategy on site.',
        ];
    }
    $cache[$family] = $rows;
    return $rows;
}

function icomplyTownContext(array $town): array
{
    $miles = icomplyMilesFromStockport($town);
    $near = [];
    foreach (icomplyUkTownNeighbours((string)$town['slug'], 3) as $n) {
        $near[] = $n['name'] . ' (' . number_format($n['miles'], 1) . ' miles)';
    }
    $pop = (int)$town['population'];
    if ($pop >= 250000) {
        $band = 'city';
    } elseif ($pop >= 75000) {
        $band = 'large town';
    } elseif ($pop >= 25000) {
        $band = 'mid-sized town';
    } else {
        $band = 'smaller town';
    }
    if ($miles < 40) {
        $travel = 'local';
    } elseif ($miles < 100) {
        $travel = 'regional';
    } elseif ($miles < 220) {
        $travel = 'scheduled';
    } else {
        $travel = 'long';
    }
    return [
        'name' => (string)$town['name'],
        'county' => (string)($town['county'] ?? ''),
        'region' => (string)($town['region'] ?? ''),
        'nation' => (string)($town['nation'] ?? ''),
        'pop' => number_format($pop),
        'miles' => number_format($miles, 0),
        'near' => $near ? implode(', ', $near) : 'other mainland towns on this list',
        'band' => $band,
        'travel' => $travel,
        'slug' => (string)$town['slug'],
    ];
}

function icomplyTownExtra(string $family, array $ctx, array &$ids): string
{
    $opens = $family === 'barriers'
        ? [
            'For {name} the lane is specified before anyone orders a boom.',
            'The {name} entrance is written up as a lane, not as a generic gate.',
            '{name} gets a survey note that names the cabinet and the release method.',
            'We treat the {name} barrier as plant that has to fail safe.',
            'A {name} quote starts with how the boom is opened today.',
            'Residents and drivers in {name} need the lane to move only when it is clear.',
            'The {county} entrance at {name} is not priced from a photograph alone.',
            '{name} car parks and yards are quoted as separate lanes when they behave differently.',
            'Whatever brand is on the {name} cabinet is recorded before parts are discussed.',
            'CAME is the new-lane default in {name}; the incumbent is kept only when it is sound.',
            'Staff in {name} should not be left with a barrier that has no manual release.',
            'The {region} trip to {name} is costed as travel, not hidden inside a boom price.',
        ]
        : [
            'For {name} the stair vent is specified against the fire strategy.',
            'The {name} AOV is identified before a replacement actuator is ordered.',
            '{name} smoke control is logged as a test, not as a promise that the window opens.',
            'We write down the panel manufacturer in {name} before talking about a takeover.',
            'A {name} stair that relies on one actuator gets that actuator named in the quote.',
            'Common parts in {name} are surveyed with the fire-alarm interface in view.',
            'The {county} building at {name} keeps its existing brand when that brand can still be tested.',
            '{name} refurbishments often inherit a vent nobody has cycled for a year.',
            'Shafts and stair windows in {name} are different jobs and are quoted that way.',
            'The {region} visit to {name} includes time to find the panel, not only the vent.',
            'A failed battery in {name} is still a smoke-control fault.',
            'We will say if the {name} system should be kept, repaired or replaced.',
        ];
    $mids = [
        'Population on this list is {pop}, which is why the page exists.',
        'The council area is {county}.',
        'Nearest published towns are {near}.',
        'Road distance from Offerton is about {miles} miles.',
        'This is a {band} in {region}.',
        'The nation file says {nation}.',
        'Neighbours used for internal links: {near}.',
        'Travel is planned from Stockport SK2, about {miles} miles.',
        '{region} is the region label on the town file.',
        'The gazetteer headcount is {pop}.',
        'County label: {county}.',
        'Other pages within a short radius: {near}.',
    ];
    $ends = $family === 'barriers'
        ? [
            'Call 07517806082 and say which lane.',
            'The wizard on this page already selects CAME unless you change it.',
            'Safety edges stay in circuit.',
            'Five-metre CAME packs are on the products hub; fitting is still POA.',
            'Paxton or Videx release is specified separately from the boom.',
            'We will not bridge out a loop to force a close.',
            'Send the rating plate if you have it.',
            'One survey can cover more than one {name} lane.',
            'Manual release is demonstrated before we leave.',
            'The partner for a new boom is CAME.',
            'Existing manufacturers on the grid are still serviced.',
            'Phone 07517806082 rather than guessing the board from a boom colour.',
        ]
        : [
            'Call 07517806082 and name the panel if you can.',
            'EN 12101 is the reference, the building decides the category.',
            'The manufacturer grid is the full AOV list for this page.',
            'A photo of the panel door speeds the {name} quote.',
            'We test what the fire alarm actually opens.',
            'Stuck-open vents are logged as faults.',
            'Takeover is only offered when the brand can still be supported.',
            'Storey count and stair count belong in the wizard.',
            'The logbook should show a functional test.',
            'Interface faults are not left as a key-switch mystery.',
            'Phone 07517806082 for the {name} stair.',
            'POA means the survey comes before the price.',
        ];
    $notes = $family === 'barriers'
        ? [
            'Boom length is measured, not assumed.',
            'The cabinet label is part of the note.',
            'Loop tails are photographed if they are visible.',
            'Spring tension is left alone until the lane is isolated.',
            'A second lane is a second line on the quote.',
            'Reader wiring is not the same trade as the boom motor.',
            'We ask who holds the manual release key.',
            'Cones and a banksman are allowed for in the visit.',
            'Radio fobs are counted before any delete.',
            'The resting position of the boom is recorded.',
            'Pedestrian gates beside the lane are out of scope unless listed.',
            'Hydraulic leaks are a reason to keep the visit short and safe.',
            'Timer close is not extended to hide a slow loop.',
            'The fire switch is identified before the logic is changed.',
            'CAME parts and incumbent parts are not mixed on one quote line.',
            'Night access to {name} is only booked when the site can open the lane.',
        ]
        : [
            'Actuator stroke is checked against the vent, not a catalogue photo.',
            'The panel key location is written down.',
            'Rain-sensor faults are included in the test.',
            'A stuck vent is made safe before it is diagnosed.',
            'End-of-line resistors are not guessed.',
            'The stair is walked before the quote is closed.',
            'Battery date codes are read, not assumed.',
            'Cause and effect is witnessed with the alarm maintainer when they are on site.',
            'Window restrictors are not removed to make a test easier.',
            'Shaft doors are only opened when access is agreed.',
            'A smoke curtain, if present, is a separate line.',
            'We record whether the vent failed open or failed shut.',
            'Interface relays are labelled in the note for {name}.',
            'Decorative grilles that block a vent are flagged.',
            'The fire strategy drawing is requested when {county} building control will ask for it.',
            'Weekly bell tests are not accepted as the AOV test.',
        ];
    $o = (int)(abs(crc32($family . '|xopen|' . $ctx['slug'])) % count($opens));
    $m = (int)(abs(crc32($family . '|xmid|' . $ctx['slug'])) % count($mids));
    $e = (int)(abs(crc32($family . '|xend|' . $ctx['slug'])) % count($ends));
    $n = (int)(abs(crc32($family . '|xnote|' . $ctx['slug'])) % count($notes));
    $ids[] = "extra:{$o}:{$m}:{$e}:{$n}";
    return icomplyTownFill($opens[$o] . ' ' . $mids[$m] . ' ' . $ends[$e] . ' ' . $notes[$n], $ctx);
}

function icomplyTownFill(string $template, array $ctx): string
{
    $out = $template;
    foreach ($ctx as $key => $value) {
        $out = str_replace('{' . $key . '}', (string)$value, $out);
    }
    return $out;
}

/** @return array<string,list<string>> */
function icomplyTownPools(string $family): array
{
    if ($family === 'barriers') {
        return [
            'lede' => [
                '{name} vehicle entrances need a barrier that fails safe and still lets traffic through. This {band} is in {county}, {region} ({nation}), population {pop}. The workshop in Offerton is about {miles} miles away.',
                'Car-park and yard lanes in {name} are quoted from the lane we can see, not from a national price list. Gazetteer population {pop}. Council area {county}. Distance from Stockport SK2: {miles} miles.',
                'A boom in {name} is a safety device with a motor attached. {band} profile, {county}, {region}. Headcount on the town file: {pop}. Road miles from the SK2 bench: {miles}.',
                'Barrier calls in {name} are usually a tired loop, a bent boom or a cabinet nobody has opened for years. Place: {county}, {nation}. Population {pop}. Trip length about {miles} miles.',
                '{name} ({region}) keeps staff, visitors and residents on the same piece of tarmac. We treat that as a surveyed lane. Population {pop} in {county}. Mileage from Offerton {miles}.',
                'If the entrance in {name} sticks open or will not rise, the first job is the safety circuit. {band}, {county}, {nation}. Listed population {pop}. About {miles} miles from Woodlands Park Road.',
            ],
            'stock' => [
                'Typical lanes around {name} are retail car parks, office decks, hospital drop-offs, industrial yards and gated housing. Neighbours on this mainland list include {near}.',
                'Around {name} the useful sites are multi-storey decks, supermarket yards, school fronts and factory gates. Nearby listed towns: {near}.',
                '{name} work tends to be one or two lanes, not a motorway toll plaza. The closest other towns we publish are {near}.',
                'Housing schemes near {name} often share one barrier between residents and a delivery window. Other local pages: {near}.',
                'Commercial estates serving {name} usually want a boom, a loop and a reader, with a manual release the fire strategy will accept. Also see {near}.',
                'Town-centre car parks in {name} punish a slow boom. Yards on the edge of {county} punish a light one. Related towns: {near}.',
            ],
            'partner' => [
                'CAME is the barrier partner. New booms are specified on the CAME GARD range unless the survey shows the incumbent cabinet, springs and safety edges are still the right kit to keep.',
                'Partner supply is CAME. A replacement lane is a CAME GARD barrier with loops and edges. We still service the other manufacturers listed on this page.',
                'Where {name} needs a new lane, the default specification is CAME. Taking over an existing BFT, FAAC, Nice or Magnetic cabinet is a different quote and stays on that manufacturer if the hardware is sound.',
                'CAME partnership covers new vehicle barriers and the 5m packs on the products hub. It does not mean we refuse to maintain another brand that is already installed in {name}.',
                'The partner badge on this page is CAME only. Every other logo is a brand we will open, test and either keep or replace after the survey in {county}.',
                'New {name} installs name CAME on the quotation. Service visits name whatever rating plate is on the cabinet.',
            ],
            'safety' => [
                'We do not raise force to hide a fault. Loops, photocells and safety edges are tested and written down. A barrier that cannot see a vehicle is not a finished job.',
                'Induction loops, photocells and the boom skirt are part of the quote. If a reader is releasing the lane, that access brand is specified separately from the barrier manufacturer.',
                'Fire-service or break-glass release is agreed before we change a controller. A convenient barrier that traps an escape route is the wrong outcome.',
                'Manual release, spring condition and the resting position of the boom are checked on every service. Remote fobs are not a substitute for a working safety edge.',
                'ANPR or a Paxton, Videx or HID reader can request an opening. The barrier still has to decide it is safe to move.',
                'Night-time faults in {name} are often a loop that has been planed off or a photocell full of water. We replace the failed device rather than bridging it out.',
            ],
            'travel' => [
                'local' => [
                    '{name} is close enough for a planned day from Stockport SK2, about {miles} miles. We still book the lane closure rather than arriving unannounced.',
                    'At roughly {miles} miles, {name} sits in the local run from Offerton. Access, induction time and a working height for the boom are confirmed first.',
                    'Same-week attendance is realistic for {name} when the lane can be coned. Distance is about {miles} miles from the workshop.',
                ],
                'regional' => [
                    '{name} is a regional visit, about {miles} miles from SK2. We group it with other {region} work where the diaries allow, and we still quote the single lane on its own.',
                    'Budget a half day of travel for {name}. The measured distance is near {miles} miles. Parts for a CAME lane can travel with the engineer; unusual incumbent boards may be a second visit.',
                    'A {name} survey is diaried from Stockport, not from a pretend local depot. About {miles} miles, {region}.',
                ],
                'scheduled' => [
                    '{name} is scheduled, not a casual call-out. About {miles} miles from Offerton. We say so in the quote so the attendance window is honest.',
                    'Reaching {name} ({nation}) is a planned run of roughly {miles} miles. Faults that need a special board are quoted as supply plus a return visit.',
                    'For {county} we batch surveys when more than one lane is in the same week. {name} itself is about {miles} miles from SK2.',
                ],
                'long' => [
                    '{name} is a long mainland trip, about {miles} miles from Stockport. We will quote it. We will not pretend there is a {name} depot.',
                    'Attendance in {name} is a dedicated visit. Distance about {miles} miles. The specification wizard on this page is how we decide whether the first journey is a survey or a repair with parts.',
                    'Scotland, the far south-west and the north of England are on this list because the population test says so. {name} is about {miles} miles away and is booked as such.',
                ],
            ],
            'close' => [
                'Call 07517806082 for a {name} barrier, or use the specification wizard. Say whether the lane is CAME already or another brand.',
                'The useful first message for {name} is lane count, boom length and the name on the cabinet. Phone 07517806082.',
                'Quotes for {name} are POA after scope. The phone number is 07517806082. WhatsApp uses the same line.',
                'If you manage several entrances around {name}, list them. One survey can cover more than one lane. 07517806082.',
                'Do not send only a photo of a bent boom and expect a fixed price. Send the cabinet label as well. {name} desk: 07517806082.',
                'Service contracts for {name} record the manufacturer, the loop test and the release method. Start with 07517806082.',
            ],
        ];
    }
    return [
        'lede' => [
            'Smoke control in {name} is the vent, the actuator and the cause-and-effect, not a window that happens to open. This {band} is in {county}, {region} ({nation}). Population on the file: {pop}. About {miles} miles from Offerton.',
            'AOV work for {name} starts from the fire strategy and the panel that is actually on the wall. {county} / {region}. Gazetteer population {pop}. SK2 distance about {miles} miles.',
            'Stair and lobby vents in {name} are life safety. {band}, {nation}, council area {county}. People counted: {pop}. Road miles from Stockport: {miles}.',
            'If an AOV in {name} is stuck open in the rain or shut during a fire signal, the quote names the actuator and the interface. Place: {region}. Population {pop}. Trip about {miles} miles.',
            '{name} blocks with a smoke shaft need the same discipline as a new stair core: test, record, then replace what failed. {county}. Population {pop}. Mileage {miles} from Woodlands Park Road.',
            'We survey AOVs in {name} before we sell a panel. {band} in {region}, {nation}. Listed population {pop}. Distance from the workshop about {miles} miles.',
        ],
        'stock' => [
            'The usual buildings are residential stairs, offices over a podium, schools, hotels and older commercial cores. Closest other towns on this list: {near}.',
            'Around {name} the AOV is often a chain actuator on a stair window, a roof vent, or a shaft the fire alarm is supposed to open. Nearby pages: {near}.',
            '{name} common parts fail when the vent motor is tired and the panel battery is older than the logbook. Other mainland towns close by: {near}.',
            'Mixed-use schemes in {county} put shops under flats. The smoke control has to match that stack, including sites around {name}. See also {near}.',
            'Taller residential stock is where {name} AOVs earn their keep. Low industrial units nearby may only need a smoke vent because the fire strategy says so. Neighbours: {near}.',
            'Refurbishments in {name} often inherit a panel the current maintainer cannot log into. We identify the manufacturer from the list below before promising a takeover. Local towns: {near}.',
        ],
        'partner' => [
            'There is no single AOV house brand on this page. SE Controls, Nuaire, Brooks, Geze, D+H, Colt, TROX and the others below are all in scope. The survey says which one is installed.',
            'Every AOV manufacturer on the grid is a real support line for {name}, with a nameplate, a link to the brand page and a way to name that brand in the wizard.',
            'We will not invent a preferred smoke-vent brand for {name}. If the fire strategy names an actuator, that is the one we test.',
            'Panel, actuator, vent and interface are quoted as separate lines when {name} only needs one of them. Full replacements are quoted when the system cannot be kept.',
            'Air-handling and smoke-shaft controls sit with the AOV scope where they are part of the same cause-and-effect in {county}.',
            'Brand logos below are our nameplates, linked to the manufacturer page, so a search for the equipment in {name} lands on a page that admits the brand.',
        ],
        'safety' => [
            'BS EN 12101, BS 9991 and Approved Document B are the reference points. We record the fire-alarm interface rather than assuming the vent will open because a magnet dropped out.',
            'A weekly visual check is not a substitute for a functional test of the actuator and the panel. {name} logbooks should show both.',
            'Cause-and-effect with the fire alarm is tested on the day, including what happens if the signal clears. We do not leave a stair vent that only works from a key switch nobody can find.',
            'Batteries, rain sensors and end-of-travel faults are ordinary. They are still written up. A stuck-open vent in weather is a building problem, not a cosmetic one.',
            'We coordinate with the fire-alarm maintainer when the interface is theirs. The AOV certificate should say what was witnessed in {name}.',
            'Replacement actuators have to suit the vent size and the reveal. A like-for-like guess from a photo is how shafts get the wrong stroke.',
        ],
        'travel' => [
            'local' => [
                '{name} is on the local run, about {miles} miles from SK2. Stair access and a permit to test the alarm are the usual constraints, not the distance.',
                'Engineers can reach {name} from Offerton in a normal working day. About {miles} miles. We still want the panel location before the visit.',
                'At {miles} miles, {name} is diaried with other {region} AOV visits when that helps the building, not when it hides a second call-out.',
            ],
            'regional' => [
                '{name} is a regional AOV visit of about {miles} miles. Allow time for keys, the fire panel and a talk with whoever owns the fire strategy.',
                'Parts for common actuators can travel to {name}. Obsolete panels may need a survey first. Distance about {miles} miles from Stockport.',
                'We do not claim a {name} store. The workshop is in Offerton and the journey is about {miles} miles.',
            ],
            'scheduled' => [
                '{name} AOV visits are scheduled. About {miles} miles, {nation}. A failed actuator on a live stair is prioritised ahead of a routine logbook visit.',
                'Travel to {county} is planned. {name} is roughly {miles} miles from SK2, so the quote shows attendance separately from the hardware.',
                'For {region} we would rather test properly once than promise a same-morning arrival we cannot make. {name}: about {miles} miles.',
            ],
            'long' => [
                '{name} is a long way from Stockport, about {miles} miles, and the page exists because the population is over 10,000 on the mainland list. Attendance is a booked visit.',
                'A {name} survey is worth doing when the stair is in use and the vent is part of the fire strategy. It is not a drop-in. Distance about {miles} miles.',
                'Remote mainland towns are quoted honestly. {name} ({region}) is about {miles} miles from the SK2 workshop.',
            ],
        ],
        'close' => [
            'Call 07517806082 and name the AOV panel if you know it. The wizard on this page lists every manufacturer we support in {name}.',
            'Phone 07517806082 for {name} smoke control. Tell us storeys, stair count and whether the vent is open, shut or unknown.',
            'Quotes are POA. The number is 07517806082. Photos of the panel door help; they do not replace the survey.',
            'If the {name} system is one of the brands below, pick it in the wizard so the engineer brings the right interface notes. Phone 07517806082.',
            'Maintenance for {name} is a dated test record, not a van on a retainer we cannot staff. Start at 07517806082.',
            'New stairs and inherited panels are different jobs. Say which one {name} is. 07517806082.',
        ],
    ];
}

/**
 * @return array{paragraphs:list<string>,ids:list<string>,faqs:list<array{0:string,1:string}>}
 */
function icomplyTownEditorial(string $family, array $town): array
{
    $ctx = icomplyTownContext($town);
    $pools = icomplyTownPools($family);
    $seed = crc32($family . '|' . $ctx['slug']);
    $paragraphs = [];
    $ids = [];
    $slot = 0;
    foreach (['lede', 'stock', 'partner', 'safety', 'close'] as $key) {
        $pool = $pools[$key];
        $idx = (int)(abs(crc32($family . '|' . $key . '|' . $ctx['slug'])) % count($pool));
        $ids[] = $key . ':' . $idx;
        $paragraphs[] = icomplyTownFill($pool[$idx], $ctx);
        if ($key === 'stock' || $key === 'safety' || $key === 'partner') {
            $idx2 = ($idx + 3) % count($pool);
            $ids[] = $key . 'b:' . $idx2;
            $paragraphs[] = icomplyTownFill($pool[$idx2], $ctx);
        }
        $slot++;
    }
    $travelPool = $pools['travel'][$ctx['travel']] ?? $pools['travel']['regional'];
    $tIdx = (int)(abs(crc32($family . '|travel|' . $ctx['slug'])) % count($travelPool));
    $ids[] = 'travel-' . $ctx['travel'] . ':' . $tIdx;
    $paragraphs[] = icomplyTownFill($travelPool[$tIdx], $ctx);
    $paragraphs[] = icomplyTownExtra($family, $ctx, $ids);

    $faqPools = $family === 'barriers'
        ? [
            ['Who is the barrier partner for {name}?', 'CAME. New lanes are specified on CAME GARD unless the survey supports keeping the existing manufacturer. Call 07517806082.'],
            ['Will you service a non-CAME barrier in {name}?', 'Yes. The manufacturer grid lists the barrier and access brands this page covers. We identify the cabinet before ordering parts.'],
            ['How far is {name} from your workshop?', 'About {miles} miles from Offerton, Stockport SK2. Attendance is booked around that distance.'],
            ['What should we send for a {name} quote?', 'Lane count, approximate boom length, a photo of the rating plate and how the barrier opens today (loop, fob, reader or attendant).'],
            ['Do you remove safety devices to make a boom move?', 'No. Loops, photocells and safety edges stay in the circuit. A barrier that cannot see an obstruction is not signed off.'],
            ['Can access control release the {name} lane?', 'Yes when the fire strategy allows it. Paxton, Videx, HID, Comelit and BPT are listed separately from the barrier manufacturer.'],
        ]
        : [
            ['Which AOV brands do you support in {name}?', 'Every manufacturer in the grid on this page, including SE Controls, Nuaire, Brooks, Geze, D+H, Colt and the rest. The survey confirms which one is installed.'],
            ['Are AOV quotes fixed before a visit?', 'No. {name} smoke control is POA after the stair, the panel and the fire-alarm interface are known.'],
            ['How far do you travel for {name}?', 'About {miles} miles from our Stockport SK2 workshop. The visit is planned around that.'],
            ['Do AOVs need testing?', 'Yes. Functional tests of actuators, vents and the panel should be in the {name} logbook, not only a visual look.'],
            ['Can you take over an existing system in {name}?', 'Often, once the manufacturer is identified. If the panel cannot be supported, we say so and quote a replacement.'],
            ['Will the vent work with the fire alarm?', 'That is the point of the interface test. We record what opened, and what did not, on the day in {name}.'],
        ];
    $faqs = [];
    $offset = (int)($seed % count($faqPools));
    for ($i = 0; $i < 4; $i++) {
        $fq = $faqPools[($offset + $i * 2) % count($faqPools)];
        $ids[] = 'faq:' . (($offset + $i * 2) % count($faqPools));
        $faqs[] = [icomplyTownFill($fq[0], $ctx), icomplyTownFill($fq[1], $ctx)];
    }
    return ['paragraphs' => $paragraphs, 'ids' => $ids, 'faqs' => $faqs, 'ctx' => $ctx];
}

function icomplyTownManufacturerHtml(string $family, array $town): string
{
    $rows = icomplyTownBrandRows($family);
    $name = htmlspecialchars((string)$town['name'], ENT_QUOTES, 'UTF-8');
    $html = '<section class="mt-12" id="manufacturers">';
    $html .= '<h2 class="text-3xl font-semibold text-black">Manufacturers for ' . $name . '</h2>';
    if ($family === 'barriers') {
        $html .= '<p class="mt-3 text-black max-w-3xl">CAME is the partner for new vehicle barriers in ' . $name . '. Every brand below has a nameplate and a link to its brand page. Use “Specify” to open the wizard with that manufacturer selected. Installation is POA.</p>';
        $html .= '<p class="mt-3"><a class="inline-flex items-center font-semibold text-[#ff6b00]" href="' . htmlspecialchars(url('/products.php'), ENT_QUOTES, 'UTF-8') . '">CAME GARD 5m barrier packs on the products hub →</a></p>';
    } else {
        $html .= '<p class="mt-3 text-black max-w-3xl">These are the AOV and smoke-control manufacturers we install and service in ' . $name . '. Each card links to the brand page. “Specify” opens the on-page wizard with that name selected.</p>';
    }
    $current = '';
    $html .= '<div class="mt-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
    foreach ($rows as $row) {
        if ($row['group'] !== $current) {
            $current = $row['group'];
        }
        $slug = htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $note = htmlspecialchars($row['note'], ENT_QUOTES, 'UTF-8');
        $href = htmlspecialchars(url('/pages/manufacturers/' . $row['slug'] . '.php'), ENT_QUOTES, 'UTF-8');
        $logo = htmlspecialchars(url('/assets/images/manufacturers/nameplates/' . $row['slug'] . '.svg'), ENT_QUOTES, 'UTF-8');
        $border = $row['partner'] ? 'border-[#ff6b00] border-2' : 'border-zinc-200 border';
        $badge = $row['partner'] ? '<span class="inline-block mt-1 text-xs font-semibold uppercase tracking-wide text-[#ff6b00]">Partner</span>' : '';
        $html .= '<article class="bg-white ' . $border . ' rounded-2xl overflow-hidden flex flex-col" data-manufacturer="' . $slug . '">';
        $html .= '<a href="' . $href . '"><img class="mfr-nameplate w-full h-28 object-cover" src="' . $logo . '" alt="' . $label . ' nameplate" width="320" height="160" loading="lazy"></a>';
        $html .= '<div class="p-3 flex-1 flex flex-col">';
        $html .= '<a class="font-semibold text-black hover:text-[#ff6b00]" href="' . $href . '">' . $label . '</a>';
        $html .= $badge;
        $html .= '<p class="mt-2 text-xs text-zinc-700 flex-1">' . $note . '</p>';
        $html .= '<a class="mt-3 text-sm font-semibold text-[#ff6b00]" href="#town-spec-wizard" data-specify-brand="' . $label . '">Specify ' . $label . '</a>';
        $html .= '</div></article>';
    }
    $html .= '</div></section>';
    return $html;
}

function icomplyTownWizardHtml(string $family, array $town, string $csrf): string
{
    $rows = icomplyTownBrandRows($family);
    $townName = (string)$town['name'];
    $service = $family === 'barriers' ? 'Vehicle barriers (CAME)' : 'AOV & Smoke Control';
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $jobs = $family === 'barriers'
        ? ['New lane', 'Service', 'Fault', 'Takeover of existing barrier', 'Access integration']
        : ['New smoke control', 'Actuator or vent replacement', 'Panel takeover', 'Service and test', 'Fault'];
    $sites = $family === 'barriers'
        ? ['Car park', 'Industrial yard', 'Residential gate', 'Hospital or campus', 'Retail park']
        : ['Residential stair', 'Office or mixed use', 'School or hotel', 'Smoke shaft', 'Not sure'];
    $html = '<section class="mt-12 bg-white border border-zinc-200 rounded-3xl p-6 md:p-8" id="town-spec-wizard">';
    $html .= '<h2 class="text-2xl font-semibold text-black">Specification wizard — ' . $h($townName) . '</h2>';
    $html .= '<p class="mt-2 text-sm text-zinc-700 max-w-3xl">Embedded on this page. It posts to the contact form with the town, brand and job attached. You can also call <a class="font-semibold text-[#ff6b00]" href="tel:07517806082">07517806082</a>.</p>';
    $html .= '<form class="mt-6 grid md:grid-cols-2 gap-4" method="post" action="' . $h(url('/contact.php')) . '">';
    $html .= '<input type="hidden" name="csrf" value="' . $h($csrf) . '">';
    $html .= '<input type="hidden" name="service" value="' . $h($service) . '">';
    $html .= '<input type="hidden" name="town" value="' . $h($townName) . '">';
    $html .= '<label class="block text-sm font-semibold">Job<select class="mt-1 w-full border px-4 py-3 rounded-2xl" name="job_type">';
    foreach ($jobs as $job) {
        $html .= '<option>' . $h($job) . '</option>';
    }
    $html .= '</select></label>';
    $html .= '<label class="block text-sm font-semibold">Site<select class="mt-1 w-full border px-4 py-3 rounded-2xl" name="site_type">';
    foreach ($sites as $site) {
        $html .= '<option>' . $h($site) . '</option>';
    }
    $html .= '</select></label>';
    $html .= '<label class="block text-sm font-semibold md:col-span-2">Manufacturer<select class="mt-1 w-full border px-4 py-3 rounded-2xl" name="brand" id="town-brand-select">';
    if ($family !== 'barriers') {
        $html .= '<option>Not sure — identify on survey</option>';
    }
    foreach ($rows as $row) {
        $selected = ($family === 'barriers' && $row['partner']) ? ' selected' : '';
        $html .= '<option' . $selected . '>' . $h($row['name']) . '</option>';
    }
    $html .= '</select></label>';
    $detailLabel = $family === 'barriers' ? 'Lanes and boom length' : 'Stairs, storeys or known panel';
    $html .= '<label class="block text-sm font-semibold md:col-span-2">' . $h($detailLabel);
    $html .= '<input class="mt-1 w-full border px-4 py-3 rounded-2xl" name="detail" maxlength="160" placeholder="' . $h($detailLabel) . '"></label>';
    $html .= '<label class="block text-sm font-semibold">Name<input class="mt-1 w-full border px-4 py-3 rounded-2xl" name="name" required maxlength="120"></label>';
    $html .= '<label class="block text-sm font-semibold">Email<input class="mt-1 w-full border px-4 py-3 rounded-2xl" type="email" name="email" required maxlength="160"></label>';
    $html .= '<label class="block text-sm font-semibold">Phone<input class="mt-1 w-full border px-4 py-3 rounded-2xl" type="tel" name="phone" required maxlength="40"></label>';
    $html .= '<label class="block text-sm font-semibold md:col-span-2">Message<textarea class="mt-1 w-full border px-4 py-3 rounded-2xl" name="message" required maxlength="5000" rows="4">';
    $html .= $h('Please quote ' . ($family === 'barriers' ? 'vehicle barriers' : 'AOV / smoke control') . ' in ' . $townName . '.');
    $html .= '</textarea></label>';
    $html .= '<div class="md:col-span-2 flex flex-wrap gap-3">';
    $html .= '<button class="bg-[#ff6b00] text-white px-8 py-3 rounded-2xl font-semibold" type="submit">Send specification</button>';
    $wa = 'https://wa.me/' . rawurlencode((string)WHATSAPP) . '?text=' . rawurlencode('Quote for ' . ($family === 'barriers' ? 'barriers' : 'AOV') . ' in ' . $townName);
    $html .= '<a class="px-8 py-3 rounded-2xl border border-zinc-300 font-semibold" href="' . $h($wa) . '">WhatsApp</a>';
    $html .= '</div></form>';
    $html .= '<script>(function(){var sel=document.getElementById("town-brand-select");if(!sel)return;document.querySelectorAll("[data-specify-brand]").forEach(function(a){a.addEventListener("click",function(){var name=a.getAttribute("data-specify-brand");if(!name)return;for(var i=0;i<sel.options.length;i++){if(sel.options[i].text===name){sel.selectedIndex=i;break;}}});});})();</script>';
    $html .= '</section>';
    return $html;
}

function icomplyTownCsrf(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['csrf'];
}

function icomplyRenderTownHub(string $family): void
{
    $towns = array_values(array_filter(
        icomplyUkTowns(),
        static function (array $town): bool {
            return function_exists('icomplyIsGreaterManchesterAreaSlug')
                && icomplyIsGreaterManchesterAreaSlug((string)($town['slug'] ?? ''));
        }
    ));
    $title = $family === 'barriers' ? 'Vehicle barriers by town' : 'AOV and smoke control by town';
    $pageTitle = $title;
    $metaDesc = $family === 'barriers'
        ? 'CAME partner vehicle-barrier pages for Greater Manchester. Phone 07517806082.'
        : 'AOV and smoke-control pages for Greater Manchester. Phone 07517806082.';
    $metaKeywords = $family === 'barriers'
        ? 'CAME barriers, vehicle barriers, car park barrier, UK towns'
        : 'AOV, smoke control, automatic opening vent, UK towns';
    $canonicalUrl = url($family === 'barriers' ? '/pages/barriers' : '/pages/aov');
    $other = $family === 'barriers' ? '/pages/aov' : '/pages/barriers';
    $otherLabel = $family === 'barriers' ? 'AOV towns' : 'Barrier towns';
    require SITE_ROOT . '/includes/header.php';
    echo '<section class="max-w-6xl mx-auto px-6 py-16">';
    echo '<p class="text-sm uppercase tracking-[3px] text-[#ff6b00]">Greater Manchester</p>';
    echo '<h1 class="mt-2 text-4xl md:text-5xl font-semibold tracking-tight">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p class="mt-4 text-lg max-w-3xl">iComply publishes these ' . count($towns) . ' Greater Manchester town pages. ';
    echo $family === 'barriers'
        ? 'CAME is the barrier partner. Other barrier and access manufacturers are listed on every town page.'
        : 'Each town page lists the AOV manufacturers we support, with nameplates and links.';
    echo ' Call <a class="font-semibold text-[#ff6b00]" href="tel:07517806082">07517806082</a>. ';
    echo '<a class="font-semibold text-[#ff6b00]" href="' . htmlspecialchars(url($other), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($otherLabel, ENT_QUOTES, 'UTF-8') . '</a>.</p>';
    $letters = [];
    foreach ($towns as $town) {
        $letter = strtoupper(substr((string)$town['name'], 0, 1));
        $letters[$letter][] = $town;
    }
    ksort($letters);
    echo '<nav class="mt-8 flex flex-wrap gap-2" aria-label="Letters">';
    foreach (array_keys($letters) as $letter) {
        echo '<a class="px-3 py-1 border rounded-full text-sm font-semibold" href="#letter-' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    echo '</nav>';
    if (!function_exists('icomplyGmServiceTownRecords')) {
        require_once __DIR__ . '/gm-service-towns.php';
    }
    $gmTowns = [];
    foreach (icomplyGmServiceTownRecords() as $gmTown) {
        if (in_array($family, $gmTown['families'], true)) {
            $gmTowns[] = $gmTown;
        }
    }
    if ($gmTowns !== []) {
        echo '<h2 class="mt-10 text-2xl font-semibold" id="greater-manchester">Greater Manchester</h2>';
        echo '<p class="mt-2 max-w-3xl">These districts and boroughs are published because iComply already covers them from Stockport. They are not clones of the nearest gazetteer town.</p>';
        echo '<ul class="mt-3 grid sm:grid-cols-2 md:grid-cols-3 gap-2">';
        foreach ($gmTowns as $gmTown) {
            $href = url('/pages/' . $family . '/' . $gmTown['slug']);
            echo '<li><a class="text-[#0B1F3A] hover:text-[#ff6b00] font-medium" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars((string)$gmTown['name'], ENT_QUOTES, 'UTF-8') . '</a> '
                . '<span class="text-xs text-zinc-500">' . htmlspecialchars((string)$gmTown['county'], ENT_QUOTES, 'UTF-8') . '</span></li>';
        }
        echo '</ul>';
    }
    foreach ($letters as $letter => $group) {
        echo '<h2 class="mt-10 text-2xl font-semibold" id="letter-' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<ul class="mt-3 grid sm:grid-cols-2 md:grid-cols-3 gap-2">';
        foreach ($group as $town) {
            $href = url('/pages/' . $family . '/' . $town['slug']);
            echo '<li><a class="text-[#0B1F3A] hover:text-[#ff6b00] font-medium" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars((string)$town['name'], ENT_QUOTES, 'UTF-8') . '</a> '
                . '<span class="text-xs text-zinc-500">' . htmlspecialchars((string)$town['county'], ENT_QUOTES, 'UTF-8') . '</span></li>';
        }
        echo '</ul>';
    }
    echo '</section>';
    require SITE_ROOT . '/includes/footer.php';
}

function icomplyTownPageRecord(string $slug): ?array
{
    $town = icomplyUkTownBySlug($slug);
    if ($town !== null) {
        return $town;
    }
    if (!function_exists('icomplyGmServiceTown')) {
        require_once __DIR__ . '/gm-service-towns.php';
    }
    return icomplyGmServiceTown($slug);
}

function icomplyRenderTownPage(string $family, string $slug): void
{
    $town = icomplyTownPageRecord($slug);
    if ($town === null || !in_array($family, icomplyTownFamilies(), true)) {
        http_response_code(404);
        echo 'Town page not found';
        return;
    }
    if (!empty($town['families']) && !in_array($family, $town['families'], true)) {
        http_response_code(404);
        echo 'Town page not found';
        return;
    }
    $edit = icomplyTownEditorial($family, $town);
    $ctx = $edit['ctx'];
    $csrf = icomplyTownCsrf();
    $place = $ctx['name'] . ', ' . $ctx['county'];
    if ($family === 'barriers') {
        $pageTitle = 'CAME vehicle barriers in ' . $place;
        $metaDesc = 'Vehicle barriers in ' . $ctx['name'] . ' (' . $ctx['pop'] . ' people, ' . $ctx['county'] . '). CAME partner. About ' . $ctx['miles'] . ' miles from Stockport. 07517806082.';
        $kicker = 'Vehicle barriers · CAME partner';
        $h1 = 'Vehicle barriers in ' . $ctx['name'];
    } else {
        $pageTitle = 'AOV smoke control in ' . $place;
        $metaDesc = 'AOV and smoke control in ' . $ctx['name'] . ' (' . $ctx['pop'] . ' people, ' . $ctx['county'] . '). EN 12101. About ' . $ctx['miles'] . ' miles from Stockport. 07517806082.';
        $kicker = 'AOV and smoke control';
        $h1 = 'AOV and smoke control in ' . $ctx['name'];
    }
    if (!empty($town['gm_local'])) {
        $metaTitleExact = true;
        $pageTitle = (string)($town['titles'][$family] ?? $pageTitle);
        $metaDesc = (string)($town['metas'][$family] ?? $metaDesc);
        $local = $town['copy'][$family] ?? [];
        if (is_array($local) && $local !== []) {
            $edit['paragraphs'] = array_merge($local, $edit['paragraphs']);
        }
        $why = 'Population on this list is ' . $ctx['pop'] . ', which is why the page exists.';
        $instead = $ctx['name'] . ' is published because iComply covers this Greater Manchester place from Stockport, including ' . (string)($town['outward'] ?? '') . '. It is not a renamed copy of the nearest gazetteer town.';
        foreach ($edit['paragraphs'] as $i => $paragraph) {
            $edit['paragraphs'][$i] = str_replace($why, $instead, $paragraph);
        }
    }
    $metaKeywords = $h1 . ', ' . $ctx['county'] . ', ' . $ctx['region'];
    $canonicalUrl = url('/pages/' . $family . '/' . $ctx['slug']);
    $metaRobots = 'index, follow, max-image-preview:large';
    require SITE_ROOT . '/includes/header.php';
    $faqEntities = [];
    foreach ($edit['faqs'] as $faq) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $faq[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
        ];
    }
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                'name' => $h1,
                'areaServed' => ['@type' => 'City', 'name' => $ctx['name']],
                'provider' => [
                    '@type' => 'LocalBusiness',
                    'name' => SITE_NAME,
                    'telephone' => PHONE,
                    'email' => EMAIL,
                ],
                'url' => $canonicalUrl,
            ],
            ['@type' => 'FAQPage', 'mainEntity' => $faqEntities],
        ],
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    echo '<article class="max-w-6xl mx-auto px-6 py-16">';
    echo '<p class="text-sm uppercase tracking-[3px] text-[#ff6b00]">' . htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<h1 class="mt-2 text-4xl md:text-5xl font-semibold tracking-tight text-black">' . htmlspecialchars($h1, ENT_QUOTES, 'UTF-8') . '</h1>';
    echo '<p class="mt-3 text-sm text-zinc-600">' . htmlspecialchars($ctx['band'] . ' · ' . $ctx['region'] . ' · population ' . $ctx['pop'], ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<div class="mt-8 max-w-3xl space-y-4 text-lg leading-relaxed" data-editorial="1">';
    foreach ($edit['paragraphs'] as $paragraph) {
        echo '<p>' . htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') . '</p>';
    }
    echo '</div>';
    echo icomplyTownManufacturerHtml($family, $town);
    echo icomplyTownWizardHtml($family, $town, $csrf);
    echo '<section class="mt-12"><h2 class="text-2xl font-semibold">Questions for ' . htmlspecialchars($ctx['name'], ENT_QUOTES, 'UTF-8') . '</h2><div class="mt-4 space-y-3">';
    foreach ($edit['faqs'] as $faq) {
        echo '<div class="border rounded-2xl p-5"><h3 class="font-semibold">' . htmlspecialchars($faq[0], ENT_QUOTES, 'UTF-8') . '</h3>';
        echo '<p class="mt-1 text-sm">' . htmlspecialchars($faq[1], ENT_QUOTES, 'UTF-8') . '</p></div>';
    }
    echo '</div></section>';
    echo '<section class="mt-12"><h2 class="text-2xl font-semibold">Nearby towns</h2><ul class="mt-3 flex flex-wrap gap-3">';
    $nearList = icomplyUkTownNeighbours($ctx['slug'], 6);
    if (!empty($town['neighbours'][$family]) && is_array($town['neighbours'][$family])) {
        $nearList = [];
        foreach ($town['neighbours'][$family] as $nslug) {
            $n = icomplyUkTownBySlug((string)$nslug);
            if ($n === null) {
                $n = icomplyGmServiceTown((string)$nslug);
                if ($n !== null && !in_array($family, $n['families'] ?? [], true)) {
                    $n = null;
                }
            }
            if ($n !== null) {
                $nearList[] = ['name' => (string)$n['name'], 'slug' => (string)$n['slug']];
            }
        }
    }
    foreach ($nearList as $n) {
        $href = url('/pages/' . $family . '/' . $n['slug']);
        echo '<li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($n['name'], ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    echo '</ul>';
    $hub = url($family === 'barriers' ? '/pages/barriers' : '/pages/aov');
    $switchFamily = $family === 'barriers' ? 'aov' : 'barriers';
    $switchSlug = function_exists('icomplyResolvePlaceSlug')
        ? icomplyResolvePlaceSlug($switchFamily, (string)$ctx['slug'])
        : (string)$ctx['slug'];
    if ($switchSlug === '' || (function_exists('icomplyPlaceSlugExists') && !icomplyPlaceSlugExists($switchFamily, $switchSlug))) {
        $switch = $hub === url('/pages/barriers') ? url('/pages/aov') : url('/pages/barriers');
        $switchLabel = $family === 'barriers' ? 'AOV towns' : 'Barrier towns';
    } else {
        $switch = url(($switchFamily === 'aov' ? '/pages/aov/' : '/pages/barriers/') . $switchSlug);
        $switchLabel = $family === 'barriers' ? 'AOV in ' . $ctx['name'] : 'Barriers in ' . $ctx['name'];
    }
    echo '<p class="mt-4 text-sm"><a class="text-[#ff6b00] font-semibold" href="' . htmlspecialchars($hub, ENT_QUOTES, 'UTF-8') . '">All towns</a>';
    echo ' · <a class="text-[#ff6b00] font-semibold" href="' . htmlspecialchars($switch, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($switchLabel, ENT_QUOTES, 'UTF-8') . '</a>';
    if (!empty($town['gm_local'])) {
        $areaHref = url((string)($town['area_hub'] ?? ('/pages/areas/' . $ctx['slug'])));
        $servicePath = $family === 'barriers' ? '/pages/services/barriers' : '/pages/services/aov-air-handling';
        $serviceLabel = $family === 'barriers' ? 'Barriers hub' : 'AOV hub';
        echo ' · <a class="text-[#ff6b00] font-semibold" href="' . htmlspecialchars($areaHref, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($ctx['name'], ENT_QUOTES, 'UTF-8') . ' area</a>';
        echo ' · <a class="text-[#ff6b00] font-semibold" href="' . htmlspecialchars(url($servicePath), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($serviceLabel, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    echo '</p>';
    echo '</section>';
    echo '<section class="mt-12 bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">';
    echo '<h2 class="text-2xl font-semibold">Speak to iComply about ' . htmlspecialchars($ctx['name'], ENT_QUOTES, 'UTF-8') . '</h2>';
    echo '<p class="mt-2 text-white/80">Offerton, Stockport, SK2 5DE. About ' . htmlspecialchars($ctx['miles'], ENT_QUOTES, 'UTF-8') . ' miles.</p>';
    echo '<a class="inline-block mt-4 bg-[#ff6b00] px-8 py-3 rounded-2xl font-semibold" href="tel:07517806082">Call 07517806082</a>';
    echo '</section></article>';
    require SITE_ROOT . '/includes/footer.php';
}

function icomplyDispatchTownPath(string $path): bool
{
    if ($path === '/pages/aov' || $path === '/pages/barriers') {
        icomplyRenderTownHub($path === '/pages/barriers' ? 'barriers' : 'aov');
        return true;
    }
    if (preg_match('#^/pages/(aov|barriers)/([a-z0-9\-]+)$#', $path, $m)) {
        // Only claim census towns we know. Other published AOV / barrier place
        // slugs fall through to aov-place.php / barriers.php handlers.
        if (icomplyUkTownBySlug($m[2]) === null && !icomplyGmServiceTownServes($m[1], $m[2])) {
            return false;
        }
        icomplyRenderTownPage($m[1], $m[2]);
        return true;
    }
    return false;
}
