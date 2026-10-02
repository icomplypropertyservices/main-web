<?php
/**
 * Access-control job lane — barriers (hardest), maglocks, door entry.
 *
 * NON-PROD catalogue. Draft PR only. Do not merge to main and do not
 * promote a Netlify production deploy from this branch.
 *
 * Source file: website/data/job-types-access-control.json
 * Rebuild:     php website/bin/build-access-control-jobs.php
 */
declare(strict_types=1);

function accessControlLaneFile(): string
{
    return SITE_ROOT . '/data/job-types-access-control.json';
}

function accessControlLaneStubPath(string $slug): string
{
    return SITE_ROOT . '/pages/keywords/' . keywordSlug($slug) . '.php';
}

/** @return array<string, mixed> */
function accessControlLaneData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = accessControlLaneFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string, mixed>> */
function accessControlLaneJobs(): array
{
    $jobs = accessControlLaneData()['jobs'] ?? [];
    return is_array($jobs) ? array_values($jobs) : [];
}

function accessControlLaneIsNonProd(): bool
{
    $data = accessControlLaneData();
    return ($data['non_prod'] ?? false) === true && ($data['promote'] ?? true) === false;
}

function accessControlLaneFamilyOf(string $slug): string
{
    $slug = keywordSlug($slug);
    foreach (accessControlLaneJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        if (keywordSlug((string)($job['slug'] ?? '')) === $slug) {
            return (string)($job['family'] ?? '');
        }
    }
    return '';
}

/**
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function accessControlJobsApplyOverlay(array $keywords): array
{
    if (!accessControlLaneIsNonProd()) {
        return $keywords;
    }
    foreach (accessControlLaneJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $synth = accessControlLaneSynthesize($job);
        $row = $synth;
        foreach ($base as $key => $val) {
            if ($val === null || $val === '' || $val === []) {
                continue;
            }
            $row[$key] = $val;
        }
        foreach (['name', 'service', 'related', 'intro', 'body', 'meta_desc', 'seo_keywords', 'focus_points', 'faq'] as $need) {
            if (empty($row[$need]) && !empty($synth[$need])) {
                $row[$need] = $synth[$need];
            }
        }
        $row['service'] = areaSlug((string)($job['service'] ?? $row['service'] ?? 'access-control'));
        if (!empty($job['name_lock']) && !empty($job['name'])) {
            $row['name'] = (string)$job['name'];
        }
        $row['lane'] = 'access-control';
        $row['family'] = (string)($job['family'] ?? 'access-control');
        $row['lane_status'] = 'draft';
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * Explicit rows added on top of keywords.json (access-control + door-entry).
 *
 * @return list<array{slug:string,name:string,family:string,service:string,related:string}>
 */
function accessControlLaneSeedRows(): array
{
    $rows = [];
    $push = static function (string $slug, string $name, string $family, string $service, string $related) use (&$rows): void {
        $rows[] = [
            'slug' => keywordSlug($slug),
            'name' => $name,
            'family' => $family,
            'service' => $service,
            'related' => keywordSlug($related),
        ];
    };

    $barriers = [
        ['car-park-barrier', 'Car Park Barrier', 'car-park-barrier'],
        ['car-park-barrier-installation', 'Car Park Barrier Installation', 'car-park-barrier'],
        ['car-park-barrier-repair', 'Car Park Barrier Repair', 'car-park-barrier'],
        ['car-park-barrier-maintenance', 'Car Park Barrier Maintenance', 'car-park-barrier'],
        ['car-park-barrier-servicing', 'Car Park Barrier Servicing', 'car-park-barrier'],
        ['automatic-barrier', 'Automatic Barrier', 'car-park-barrier'],
        ['automatic-barrier-installation', 'Automatic Barrier Installation', 'automatic-barrier'],
        ['automatic-barrier-repair', 'Automatic Barrier Repair', 'automatic-barrier'],
        ['automatic-barrier-maintenance', 'Automatic Barrier Maintenance', 'automatic-barrier'],
        ['rising-arm-barrier', 'Rising Arm Barrier', 'car-park-barrier'],
        ['rising-arm-barrier-installation', 'Rising Arm Barrier Installation', 'rising-arm-barrier'],
        ['rising-arm-barrier-repair', 'Rising Arm Barrier Repair', 'rising-arm-barrier'],
        ['vehicle-barrier', 'Vehicle Barrier', 'car-park-barrier'],
        ['vehicle-barrier-installation', 'Vehicle Barrier Installation', 'vehicle-barrier'],
        ['vehicle-barrier-repair', 'Vehicle Barrier Repair', 'vehicle-barrier'],
        ['barrier-installation', 'Barrier Installation', 'car-park-barrier'],
        ['barrier-repair', 'Barrier Repair', 'car-park-barrier'],
        ['barrier-arm-replacement', 'Barrier Arm Replacement', 'car-park-barrier'],
        ['barrier-boom-replacement', 'Barrier Boom Replacement', 'car-park-barrier'],
        ['barrier-spring-replacement', 'Barrier Spring Replacement', 'car-park-barrier'],
        ['barrier-motor-replacement', 'Barrier Motor Replacement', 'car-park-barrier'],
        ['barrier-gearbox-repair', 'Barrier Gearbox Repair', 'car-park-barrier'],
        ['barrier-control-cabinet', 'Barrier Control Cabinet', 'came-barrier'],
        ['barrier-limit-switch', 'Barrier Limit Switch', 'barrier-repair'],
        ['barrier-manual-release', 'Barrier Manual Release', 'barrier-repair'],
        ['barrier-loop-detector', 'Barrier Loop Detector', 'car-park-barrier'],
        ['barrier-safety-loop', 'Barrier Safety Loop', 'barrier-loop-detector'],
        ['barrier-photocell', 'Barrier Photocell', 'barrier-safety-devices'],
        ['barrier-safety-edge', 'Barrier Safety Edge', 'barrier-safety-devices'],
        ['barrier-safety-devices', 'Barrier Safety Devices', 'car-park-barrier'],
        ['anti-trap-barrier', 'Anti-Trap Barrier', 'barrier-safety-devices'],
        ['barrier-ups-battery', 'Barrier UPS Battery', 'barrier-control-cabinet'],
        ['barrier-foundation', 'Barrier Foundation', 'barrier-civil-works'],
        ['barrier-island', 'Barrier Island', 'barrier-civil-works'],
        ['barrier-civil-works', 'Barrier Civil Works', 'barrier-installation'],
        ['entry-exit-barrier', 'Entry and Exit Barrier Pair', 'car-park-barrier'],
        ['twin-lane-barrier', 'Twin Lane Barrier', 'entry-exit-barrier'],
        ['staff-car-park-barrier', 'Staff Car Park Barrier', 'car-park-barrier'],
        ['visitor-car-park-barrier', 'Visitor Car Park Barrier', 'car-park-barrier'],
        ['residential-car-park-barrier', 'Residential Car Park Barrier', 'car-park-barrier'],
        ['apartment-car-park-barrier', 'Apartment Car Park Barrier', 'car-park-barrier'],
        ['commercial-car-park-barrier', 'Commercial Car Park Barrier', 'car-park-barrier'],
        ['industrial-yard-barrier', 'Industrial Yard Barrier', 'vehicle-barrier'],
        ['warehouse-yard-barrier', 'Warehouse Yard Barrier', 'vehicle-barrier'],
        ['office-car-park-barrier', 'Office Car Park Barrier', 'car-park-barrier'],
        ['retail-park-barrier', 'Retail Park Barrier', 'car-park-barrier'],
        ['barrier-access-integration', 'Barrier Access Control Integration', 'car-park-barrier'],
        ['barrier-fob-reader', 'Barrier Fob Reader', 'barrier-access-integration'],
        ['barrier-keypad', 'Barrier Keypad', 'barrier-access-integration'],
        ['barrier-gsm-entry', 'Barrier GSM Entry', 'barrier-access-integration'],
        ['barrier-intercom', 'Barrier Intercom', 'barrier-access-integration'],
        ['barrier-anpr', 'Barrier ANPR Integration', 'barrier-access-integration'],
        ['barrier-credential-interface', 'Barrier Credential Interface', 'barrier-access-integration'],
        ['came-barrier', 'CAME Barrier', 'car-park-barrier'],
        ['came-gard-barrier', 'CAME Gard Barrier', 'came-barrier'],
        ['came-gard-gt4', 'CAME Gard GT4 Barrier', 'came-barrier'],
        ['came-gard-gt8', 'CAME Gard GT8 Barrier', 'came-barrier'],
        ['came-barrier-installation', 'CAME Barrier Installation', 'came-barrier'],
        ['came-barrier-repair', 'CAME Barrier Repair', 'came-barrier'],
        ['came-barrier-maintenance', 'CAME Barrier Maintenance', 'came-barrier'],
        ['five-metre-barrier', '5 Metre Barrier Arm', 'came-gard-barrier'],
        ['barrier-commissioning', 'Barrier Commissioning', 'barrier-installation'],
        ['barrier-maintenance-contract', 'Barrier Maintenance Contract', 'car-park-barrier'],
        ['barrier-engineer', 'Barrier Engineer', 'barrier-repair'],
        ['barrier-fault-finding', 'Barrier Fault Finding', 'barrier-repair'],
        ['barrier-stuck-down', 'Barrier Stuck Down', 'barrier-repair'],
        ['barrier-stuck-up', 'Barrier Stuck Up', 'barrier-repair'],
        ['barrier-fails-to-close', 'Barrier Fails to Close', 'barrier-repair'],
        ['barrier-fails-to-open', 'Barrier Fails to Open', 'barrier-repair'],
        ['barrier-after-vehicle-strike', 'Barrier After Vehicle Strike', 'barrier-repair'],
        ['barrier-emergency-access', 'Barrier Emergency Access', 'barrier-manual-release'],
        ['pedestrian-gate-with-barrier', 'Pedestrian Gate with Barrier', 'car-park-barrier'],
        ['swing-gate-with-barrier', 'Swing Gate with Barrier', 'car-park-barrier'],
    ];
    foreach ($barriers as [$slug, $name, $related]) {
        $push($slug, $name, 'barriers', 'access-control', $related);
    }

    $townBases = [
        ['car-park-barrier', 'Car Park Barrier'],
        ['car-park-barrier-installation', 'Car Park Barrier Installation'],
        ['car-park-barrier-repair', 'Car Park Barrier Repair'],
        ['automatic-barrier', 'Automatic Barrier'],
        ['came-barrier', 'CAME Barrier'],
        ['came-gard-gt4', 'CAME Gard GT4 Barrier'],
        ['rising-arm-barrier', 'Rising Arm Barrier'],
        ['vehicle-barrier', 'Vehicle Barrier'],
        ['barrier-loop-detector', 'Barrier Loop Detector'],
        ['barrier-stuck-down', 'Barrier Stuck Down'],
        ['barrier-after-vehicle-strike', 'Barrier After Vehicle Strike'],
        ['barrier-installation', 'Barrier Installation'],
        ['barrier-repair', 'Barrier Repair'],
        ['barrier-maintenance-contract', 'Barrier Maintenance Contract'],
    ];
    foreach ($townBases as [$base, $label]) {
        $push($base . '-manchester', $label . ' in Manchester', 'barriers', 'access-control', $base);
        $push($base . '-burnley', $label . ' in Burnley', 'barriers', 'access-control', $base);
    }

    $maglocks = [
        ['maglock-installation', 'Maglock Installation', 'magnetic-lock-installation'],
        ['maglock-repair', 'Maglock Repair', 'maglock-installation'],
        ['maglock-replacement', 'Maglock Replacement', 'maglock-installation'],
        ['maglock-maintenance', 'Maglock Maintenance', 'maglock-installation'],
        ['maglock-servicing', 'Maglock Servicing', 'maglock-installation'],
        ['fail-safe-maglock', 'Fail Safe Maglock', 'maglock-installation'],
        ['fail-secure-maglock', 'Fail Secure Maglock', 'maglock-installation'],
        ['monitored-maglock', 'Monitored Maglock', 'maglock-installation'],
        ['double-door-maglock', 'Double Door Maglock', 'maglock-installation'],
        ['shear-maglock', 'Shear Maglock', 'maglock-installation'],
        ['maglock-power-supply', 'Maglock Power Supply', 'maglock-installation'],
        ['maglock-fire-release', 'Maglock Fire Release', 'maglock-installation'],
        ['maglock-holding-force', 'Maglock Holding Force Check', 'maglock-installation'],
        ['maglock-bracket', 'Maglock Bracket Kit', 'maglock-installation'],
        ['maglock-door-loop', 'Maglock Door Loop', 'maglock-installation'],
        ['glass-door-maglock', 'Glass Door Maglock', 'maglock-installation'],
        ['communal-entrance-maglock', 'Communal Entrance Maglock', 'maglock-installation'],
        ['maglock-battery-backup', 'Maglock Battery Backup', 'maglock-power-supply'],
        ['maglock-armature-plate', 'Maglock Armature Plate', 'maglock-installation'],
        ['maglock-alignment', 'Maglock Alignment', 'maglock-repair'],
        ['maglock-after-door-change', 'Maglock After Door Change', 'maglock-installation'],
        ['electric-drop-bolt', 'Electric Drop Bolt', 'electric-strike-installation'],
        ['maglock-survey', 'Maglock Survey', 'maglock-installation'],
        ['maglock-manchester', 'Maglocks in Manchester', 'maglock-installation'],
        ['maglock-burnley', 'Maglocks in Burnley', 'maglock-installation'],
        ['maglock-installation-manchester', 'Maglock Installation in Manchester', 'maglock-installation'],
        ['maglock-installation-burnley', 'Maglock Installation in Burnley', 'maglock-installation'],
        ['maglock-repair-manchester', 'Maglock Repair in Manchester', 'maglock-repair'],
        ['maglock-repair-burnley', 'Maglock Repair in Burnley', 'maglock-repair'],
    ];
    foreach ($maglocks as [$slug, $name, $related]) {
        $push($slug, $name, 'maglock', 'access-control', $related);
    }

    $doors = [
        ['door-entry-manchester', 'Door Entry in Manchester', 'door-entry-installation'],
        ['door-entry-burnley', 'Door Entry in Burnley', 'door-entry-installation'],
        ['video-door-entry-manchester', 'Video Door Entry in Manchester', 'video-door-entry'],
        ['video-door-entry-burnley', 'Video Door Entry in Burnley', 'video-door-entry'],
        ['bell-system-panel-service', 'Bell System Door Panel Service', 'door-entry-panel-replacement'],
        ['door-entry-after-water-ingress', 'Door Entry After Water Ingress', 'door-entry-repair'],
        ['door-entry-backup-battery', 'Door Entry Backup Battery', 'door-entry-maintenance'],
        ['door-entry-camera-module', 'Door Entry Camera Module', 'video-door-entry'],
        ['door-entry-fire-override-check', 'Door Entry Fire Override Check', 'door-entry-installation'],
        ['gsm-sim-door-entry', 'GSM SIM Door Entry', 'gsm-door-entry'],
        ['landlord-handset-swap-programme', 'Landlord Handset Swap Programme', 'door-entry-handset-replacement'],
    ];
    foreach ($doors as [$slug, $name, $related]) {
        $push($slug, $name, 'door-entry', 'door-entry', $related);
    }

    $access = [
        ['access-control-manchester', 'Access Control in Manchester', 'access-control-installation'],
        ['access-control-burnley', 'Access Control in Burnley', 'access-control-installation'],
        ['access-control-after-staff-change', 'Access Control After Staff Change', 'access-control-card-programming'],
        ['access-control-fire-interface-test', 'Access Control Fire Interface Test', 'fire-release-access-control'],
        ['access-level-holiday-calendar', 'Access Level Holiday Calendar', 'access-control-card-programming'],
        ['broken-fob-emergency-visit', 'Broken Fob Emergency Visit', 'fob-access-control'],
        ['controller-battery-replace', 'Access Controller Battery Replace', 'access-control-maintenance'],
        ['fail-secure-lock-assessment', 'Fail Secure Lock Assessment', 'maglock-installation'],
        ['gate-access-control-liaison', 'Gate Access Control Liaison', 'gate-access-control'],
        ['lift-floor-access-integration-advice', 'Lift Floor Access Integration Advice', 'lift-access-control'],
    ];
    foreach ($access as [$slug, $name, $related]) {
        $family = $slug === 'fail-secure-lock-assessment' ? 'maglock' : 'access-control';
        $push($slug, $name, $family, 'access-control', $related);
    }

    return $rows;
}

function accessControlLaneGuessFamily(string $slug, string $service): string
{
    $slug = keywordSlug($slug);
    $overrides = [
        'magnetic-lock-installation' => 'maglock',
        'electric-strike-installation' => 'maglock',
        'fail-secure-lock-assessment' => 'maglock',
        'electric-door-release' => 'maglock',
        'car-park-barrier-access' => 'barriers',
    ];
    if (isset($overrides[$slug])) {
        return $overrides[$slug];
    }
    if (str_contains($slug, 'barrier') || str_contains($slug, 'came-gard') || str_contains($slug, 'came-barrier')) {
        return 'barriers';
    }
    if (preg_match('/maglock|magnetic-lock|electric-strike|electric-drop-bolt|shear-maglock|armature-plate/', $slug)) {
        return 'maglock';
    }
    if ($service === 'door-entry' || str_contains($slug, 'door-entry') || str_contains($slug, 'intercom')) {
        if ($service === 'door-entry' || str_contains($slug, 'door-entry')) {
            return 'door-entry';
        }
    }
    return 'access-control';
}

/**
 * @param array<string, mixed> $job
 * @return array<string, mixed>
 */
function accessControlLaneSynthesize(array $job): array
{
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $name = trim((string)($job['name'] ?? keywordDisplayName($slug)));
    $family = (string)($job['family'] ?? 'access-control');
    $service = areaSlug((string)($job['service'] ?? 'access-control'));
    $related = keywordSlug((string)($job['related'] ?? $slug));
    $town = '';
    if (str_ends_with($slug, '-manchester') || $slug === 'maglock-manchester' || $slug === 'access-control-manchester' || $slug === 'door-entry-manchester') {
        $town = 'Manchester';
    } elseif (str_ends_with($slug, '-burnley') || $slug === 'maglock-burnley' || $slug === 'access-control-burnley' || $slug === 'door-entry-burnley') {
        $town = 'Burnley';
    }
    $place = $town === 'Manchester' ? 'Manchester (MCR)' : ($town === 'Burnley' ? 'Burnley' : 'Greater Manchester and the North West');
    $variant = abs(crc32($slug)) % 3;
    $services = function_exists('getServices') ? getServices() : [];
    $serviceName = (string)($services[$service] ?? 'Access Control');

    if ($family === 'barriers') {
        $hard = 'Rising-arm barriers are the hardest job on this access-control lane: boom length, induction loops, photocells, the safety edge, the cabinet and the credential that opens the lane all have to work together.';
        $intro = $name . ' for ' . $place . '. ' . $hard;
        $bits = [
            'Where the lane suits a CAME Gard cabinet we install and service GT4 and GT8 operators and 5 metre arms. Scope is agreed after a site survey. There is no catalogue price on this page.',
            'A boom stuck up or stuck down is treated as a safety fault first. Manual release, vehicle-strike damage, springs and the gearbox are checked before parts are ordered.',
            'Pedestrian doors beside the lane — maglocks, strikes and video door entry — are looked at on the same visit so the car park and the front door do not fight each other.',
        ];
        $focus = [
            'Lane survey: boom length, island, loops, photocells and safety edge',
            'CAME Gard GT4 / GT8 and 5 metre arms where the lane needs them',
            'Credential interface: fob, keypad, GSM, intercom or ANPR',
            'POA written quote — no invented barrier price',
        ];
        $faqs = [
            ['Why are car park barriers the hardest access job?', 'The civil work, safety devices and the operator have to match the lane. A door maglock does not prepare a rising arm that must stop for a vehicle.'],
            ['Do you cover ' . ($town !== '' ? $town : 'Manchester and Burnley') . ' for barriers?', $town === 'Burnley'
                ? 'Yes. Burnley is a named town on this lane, alongside Manchester (MCR), attended from our Stockport base.'
                : 'Yes. Manchester (MCR) and Burnley are named towns on this lane, attended from our Stockport base.'],
            ['How is ' . $name . ' priced?', 'Price on application after survey. Boom length, power, loops and safety devices change the scope. We do not print a pound figure here.'],
        ];
    } elseif ($family === 'maglock') {
        $intro = $name . ' for ' . $place . '. Maglocks, monitored locks and electric strikes sit beside the barrier lane: the pedestrian door still has to release on fire alarm and still has to hold when the credential is refused.';
        $bits = [
            'We check holding force, armature alignment, Z and L brackets, the power supply and the fire-release interface before we call a maglock finished. Fail-safe and fail-secure are chosen for the door, not copied from the last job.',
            'Double doors, glass doors and communal entrances need a bracket and a door loop that survive daily use. A barrier cabinet in the car park does not power this lock.',
            'Manchester (MCR) and Burnley entrances are surveyed the same way as the rest of the North West: photos of the door edge, the supply and the fire interface, then a POA quote.',
        ];
        $focus = [
            'Fail-safe or fail-secure chosen for the actual door',
            'Fire-release interface checked with the door unlocked',
            'Brackets, armature plate and power supply included in the survey',
            'POA quote — no invented maglock price',
        ];
        $faqs = [
            ['Is a maglock the same job as a car park barrier?', 'No. Barriers are the hardest lane job. A maglock is the pedestrian door beside that lane, or a communal entrance, and it is scoped separately.'],
            ['Will the door release if the fire alarm sounds?', 'Where a fire interface is required we test release on that door and record it. We do not leave a maglock holding against an alarm.'],
            ['How is ' . $name . ' priced?', 'Price on application after we see the door, the supply and the bracket. No catalogue price on this page.'],
        ];
    } elseif ($family === 'door-entry') {
        $intro = $name . ' for ' . $place . '. Audio and video door entry for flats, HMOs and receptions, including panels that release a maglock or call a barrier lane.';
        $bits = [
            'We survey the panel, handsets, cabling and the lock it releases. A 5 metre barrier arm is a different job; if the entrance has both, both are listed on the quote.',
            'Video panels, GSM SIM panels and handset swap programmes are commissioned with the resident or FM list, then left with a working trade button where the block needs one.',
            'Fire override on the door release is checked where the strategy requires it. Manchester (MCR) and Burnley blocks are named towns on this lane.',
        ];
        $focus = [
            'Panel, handset and cabling survey before any swap',
            'Lock release integrated with maglock or strike',
            'Barrier lanes quoted separately when the site has both',
            'POA quote — no invented door-entry price',
        ];
        $faqs = [
            ['Can door entry open a car park barrier?', 'Sometimes, when the panel has a clean release into the barrier controller. That interface is surveyed. It is not assumed.'],
            ['Do you cover ' . $place . '?', 'Yes, from our Stockport base. Manchester (MCR) and Burnley have their own pages on this lane.'],
            ['How is ' . $name . ' priced?', 'Price on application after panel condition, handset count and cabling are known.'],
        ];
    } else {
        $intro = $name . ' for ' . $place . '. Door access — readers, credentials and controllers — with car park barriers treated as the hardest related job, not a footnote.';
        $bits = [
            'Paxton, HID and Salto-style credentials are programmed for joiners and leavers. If the site also has a rising-arm barrier, that lane is surveyed on its own.',
            'Fire release, anti-passback and time zones are set for the doors you actually have. We write the scope before any install date is fixed.',
            'Manchester (MCR) and Burnley are priority towns. The rest of the North West is covered from Stockport. Quotes are POA.',
        ];
        $focus = [
            'Readers, controllers and credentials scoped per door',
            'Barrier lane called out separately when the site has one',
            'Fire release tested where the door strategy needs it',
            'POA quote — no invented access-control price',
        ];
        $faqs = [
            ['Do you install barriers as well as door access?', 'Yes. Barriers are the hardest part of this lane and have their own pages, including Manchester and Burnley.'],
            ['Which towns are named on this lane?', 'Manchester (MCR) and Burnley, plus the wider North West from Stockport.'],
            ['How is ' . $name . ' priced?', 'Price on application after door count, brand and any barrier lane are confirmed.'],
        ];
    }

    $body = $bits[$variant] . ' ' . $bits[($variant + 1) % 3];
    if ($town !== '') {
        $body .= ' This page is the ' . $place . ' job, not a generic North West doorway.';
    } else {
        $body .= ' See the Manchester (MCR) and Burnley pages when the site is in those towns.';
    }

    $meta = $name . ' in ' . $place . '. Surveyed POA quote from Stockport. No catalogue price.';
    if (strlen($meta) > 165) {
        $meta = $name . ' — ' . $place . '. POA after survey. No catalogue price.';
    }

    return [
        'name' => $name,
        'service' => $service,
        'related' => $related !== '' ? $related : $slug,
        'intro' => $intro,
        'body' => $body,
        'meta_desc' => $meta,
        'seo_keywords' => $name . ', ' . $serviceName . ', ' . $place . ', car park barrier, maglock, Stockport',
        'focus_points' => $focus,
        'faq' => $faqs,
    ];
}

function accessControlLaneCameImageUrl(): string
{
    $file = SITE_ROOT . '/data/bar-5m-came-gard-images.json';
    if (is_file($file)) {
        $map = json_decode((string)file_get_contents($file), true);
        if (is_array($map)) {
            foreach (['bar-5m-std', 'bar-5m-paxton', 'bar-5m-videx'] as $key) {
                if (!empty($map[$key]) && is_string($map[$key])) {
                    return $map[$key];
                }
            }
        }
    }
    return '';
}

/**
 * Hub block for access-control, door-entry, and the keyword index.
 * Empty for every other service.
 */
function accessControlLaneHubSection(string $context): string
{
    if (!in_array($context, ['access-control', 'door-entry', 'keywords'], true)) {
        return '';
    }
    if (!accessControlLaneIsNonProd() && accessControlLaneJobs() === []) {
        return '';
    }

    $groups = [
        'Manchester (MCR)' => [
            'car-park-barrier-manchester' => 'Car park barriers',
            'barrier-installation-manchester' => 'Barrier installation',
            'barrier-repair-manchester' => 'Barrier repair',
            'came-barrier-manchester' => 'CAME barriers',
            'came-gard-gt4-manchester' => 'CAME Gard GT4',
            'rising-arm-barrier-manchester' => 'Rising-arm barriers',
            'barrier-loop-detector-manchester' => 'Loop detectors',
            'barrier-after-vehicle-strike-manchester' => 'After a vehicle strike',
            'barrier-stuck-down-manchester' => 'Barrier stuck down',
            'maglock-manchester' => 'Maglocks',
            'door-entry-manchester' => 'Door entry',
        ],
        'Burnley' => [
            'car-park-barrier-burnley' => 'Car park barriers',
            'barrier-installation-burnley' => 'Barrier installation',
            'barrier-repair-burnley' => 'Barrier repair',
            'came-barrier-burnley' => 'CAME barriers',
            'came-gard-gt4-burnley' => 'CAME Gard GT4',
            'rising-arm-barrier-burnley' => 'Rising-arm barriers',
            'barrier-loop-detector-burnley' => 'Loop detectors',
            'barrier-after-vehicle-strike-burnley' => 'After a vehicle strike',
            'barrier-stuck-down-burnley' => 'Barrier stuck down',
            'maglock-burnley' => 'Maglocks',
            'door-entry-burnley' => 'Door entry',
        ],
    ];

    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };

    $img = accessControlLaneCameImageUrl();
    $imgHtml = '';
    if ($img !== '') {
        $imgHtml = '<img src="' . $h($img) . '" alt="CAME Gard rising-arm car park barrier" class="w-full h-52 object-cover rounded-2xl border border-white/10" loading="lazy">';
    }

    $cards = '';
    foreach ($groups as $townLabel => $links) {
        $items = '';
        foreach ($links as $slug => $label) {
            $href = htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
            $items .= '<a href="' . $href . '" class="block px-3 py-2 rounded-xl bg-white/10 hover:bg-[#ff6b00] text-sm font-semibold">'
                . $h($label) . '</a>';
        }
        $cards .= '<div class="p-6 rounded-3xl bg-white/5 border border-white/10">'
            . '<h3 class="text-2xl font-semibold">' . $h($townLabel) . '</h3>'
            . '<p class="mt-2 text-sm text-white/75">Named town on the barrier lane. Each link is a real job page, quoted POA after survey.</p>'
            . '<div class="mt-4 grid grid-cols-2 gap-2">' . $items . '</div>'
            . '</div>';
    }

    $counts = accessControlLaneData()['counts'] ?? [];
    $barrierCount = (int)($counts['barriers'] ?? 0);
    $countLine = $barrierCount > 0
        ? $barrierCount . ' barrier job pages on this lane, ahead of maglocks and door entry.'
        : 'Barrier job pages lead this lane, ahead of maglocks and door entry.';

    return '<section id="barriers" data-lane="access-control" class="bg-[#0B1F3A] text-white">'
        . '<div class="max-w-7xl mx-auto px-6 py-16">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Hardest job on the access lane</div>'
        . '<h2 class="mt-2 text-3xl md:text-4xl font-semibold tracking-tight">Car park barriers — Manchester and Burnley</h2>'
        . '<p class="mt-4 max-w-3xl text-lg text-white/85">Barriers are the hardest access-control job we take. A rising arm needs the boom, the loops, the safety devices and the credential that opens the lane. Maglocks and door entry are scoped beside that lane, not instead of it. ' . $h($countLine) . '</p>'
        . '<div class="mt-8 grid lg:grid-cols-5 gap-8 items-start">'
        . '<div class="lg:col-span-2 space-y-4">' . $imgHtml
        . '<p class="text-sm text-white/70">CAME Gard GT4 and GT8 cabinets and 5 metre arms, where the lane needs them. Installation, repair and maintenance are POA — no catalogue price.</p>'
        . '<a href="' . $h(url('/pages/keywords/came-gard-gt4.php')) . '" class="inline-flex px-5 py-3 rounded-2xl bg-[#ff6b00] font-semibold">CAME Gard GT4</a>'
        . '</div>'
        . '<div class="lg:col-span-3 grid md:grid-cols-2 gap-4">' . $cards . '</div>'
        . '</div>'
        . '<div class="mt-8 flex flex-wrap gap-3 text-sm">'
        . '<a class="px-4 py-2 rounded-full bg-white text-[#0B1F3A] font-semibold" href="' . $h(url('/pages/keywords/car-park-barrier.php')) . '">All-areas car park barrier</a>'
        . '<a class="px-4 py-2 rounded-full border border-white/30 font-semibold" href="' . $h(url('/pages/keywords/maglock-installation.php')) . '">Maglock installation</a>'
        . '<a class="px-4 py-2 rounded-full border border-white/30 font-semibold" href="' . $h(url('/pages/services/door-entry.php')) . '">Door entry</a>'
        . '<a class="px-4 py-2 rounded-full border border-white/30 font-semibold" href="#quote">POA quote</a>'
        . '</div>'
        . '</div></section>';
}

function accessControlLaneKeywordStrip(string $slug, string $area = ''): string
{
    $slug = keywordSlug($slug);
    $family = accessControlLaneFamilyOf($slug);
    if ($family === '') {
        return '';
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $townNote = '';
    if ($area === 'Manchester' || str_contains($slug, 'manchester')) {
        $townNote = ' Manchester (MCR) is a named town on this lane.';
    } elseif ($area === 'Burnley' || str_contains($slug, 'burnley')) {
        $townNote = ' Burnley is a named town on this lane.';
    }
    $lead = $family === 'barriers'
        ? 'This is a barrier job — the hardest work on the access-control lane.'
        : 'Barriers are the hardest job on this lane. This page sits beside that work.';

    $links = [
        'car-park-barrier-manchester' => 'Barriers in Manchester',
        'car-park-barrier-burnley' => 'Barriers in Burnley',
        'came-gard-gt4' => 'CAME Gard GT4',
        'maglock-installation' => 'Maglocks',
        'door-entry-installation' => 'Door entry',
    ];
    $linkHtml = '';
    foreach ($links as $linkSlug => $label) {
        if ($linkSlug === $slug) {
            continue;
        }
        $linkHtml .= '<a class="px-3 py-1.5 rounded-full bg-white border border-zinc-300 text-sm font-semibold text-[#061828] hover:border-[#ff6b00]" href="'
            . $h(url('/pages/keywords/' . $linkSlug . '.php')) . '">' . $h($label) . '</a>';
    }

    return '<section data-lane="access-control" data-family="' . $h($family) . '" class="bg-white border-b border-zinc-200">'
        . '<div class="max-w-7xl mx-auto px-6 py-6">'
        . '<p class="text-sm font-semibold text-[#0B1F3A]">' . $h($lead . $townNote) . '</p>'
        . '<div class="mt-3 flex flex-wrap gap-2">' . $linkHtml
        . '<a class="px-3 py-1.5 rounded-full bg-[#0B1F3A] text-white text-sm font-semibold" href="'
        . $h(url('/pages/services/access-control.php')) . '#barriers">Barrier hub</a>'
        . '</div></div></section>';
}

/**
 * Barriers first, then maglocks, with Manchester and Burnley ahead of generic slugs.
 *
 * @param array<string, array<string, mixed>> $map
 * @return array<string, array<string, mixed>>
 */
function accessControlLaneSortKeywordMap(array $map): array
{
    uksort($map, static function (string $a, string $b): int {
        $rank = static function (string $slug): array {
            $family = accessControlLaneFamilyOf($slug);
            $familyRank = ['barriers' => 0, 'maglock' => 1, 'door-entry' => 2, 'access-control' => 3][$family] ?? 9;
            $town = str_ends_with($slug, '-manchester') ? 0 : (str_ends_with($slug, '-burnley') ? 1 : 2);
            return [$familyRank, $town, $slug];
        };
        return $rank($a) <=> $rank($b);
    });
    return $map;
}
