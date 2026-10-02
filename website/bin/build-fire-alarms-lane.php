#!/usr/bin/env php
<?php
/**
 * Build the Fire Alarms job lane (install / maintain / service) and emit keyword stubs.
 *
 * Usage: php website/bin/build-fire-alarms-lane.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * New jobs, plus replacements for the three generic fire-alarm intros.
 *
 * @return list<array<string, mixed>>
 */
function fireAlarmsLaneContentLibrary(): array
{
    $faq = static function (string $q1, string $a1, string $q2, string $a2, string $q3, string $a3): array {
        return [[$q1, $a1], [$q2, $a2], [$q3, $a3]];
    };

    return [
        [
            'slug' => 'fire-alarm-replacement',
            'lane' => 'install',
            'name' => 'Fire Alarm Replacement',
            'related' => 'fire-alarm-installation',
            'intro' => 'Replacing an ageing fire alarm panel, or a whole detection system, is often safer than another round of fault chasing. Icomply surveys what is on site, agrees a BS 5839 replacement scope, and commissions the new system with certificates for premises across Stockport and the North West.',
            'body' => 'We replace conventional panels with addressable systems where the building has outgrown its zones, and we renew standby power as part of a planned change. Existing cable is reused only when tests show it is still suitable. Cause-and-effect, the zone chart and the logbook are updated at handover. The quote is written after we see the panel, the device count and the access — price on application, not a catalogue fee.',
            'meta_desc' => 'Fire alarm panel and system replacement to BS 5839 across Stockport, Manchester and the North West. Survey, commission and certify. Quote after scope.',
            'seo_keywords' => 'fire alarm replacement, fire alarm panel replacement, replace fire alarm system, BS 5839 replacement Stockport, fire alarm upgrade Manchester',
            'focus_points' => [
                'Survey of the existing panel, loops and coverage',
                'Like-for-like or addressable replacement to an agreed category',
                'Commissioning, zone chart and certificate pack',
                'Written quote after scope',
            ],
            'faq' => $faq(
                'Can you replace the panel and keep the detectors?',
                'Sometimes, if the devices use a compatible protocol. We identify the heads before promising a panel-only swap.',
                'Will the building be unprotected during the change?',
                'The changeover is planned so detection is not left off overnight. Occupied sites are phased.',
                'Do you publish a replacement price?',
                'No. Loop count, device type, access and whether cabling must be renewed all change the quote.'
            ),
        ],
        [
            'slug' => 'bs-5839-maintenance',
            'lane' => 'maintain',
            'name' => 'BS 5839 Maintenance',
            'related' => 'fire-alarm-maintenance',
            'intro' => 'BS 5839 maintenance is the planned inspection and servicing that keeps a fire detection system fit for purpose between the weekly user tests. Icomply carries out competent-person visits for non-domestic systems across Greater Manchester and the North West, with a logbook entry and a clear defect list.',
            'body' => 'For most BS 5839-1 systems the interval between inspection and servicing visits should not exceed six months. The responsible person on site still runs the weekly call-point test. We check the panel, standby power, a sample of devices, and interfaces where they can be tested without an unsafe plant shutdown. Defects are written up so repairs can be authorised separately from the planned visit. Domestic BS 5839-6 systems are maintained to the grade and category already installed.',
            'meta_desc' => 'BS 5839 fire alarm maintenance across Stockport and the North West. Six-monthly servicing rhythm, logbook entries and a written defect list. Quote after the asset list.',
            'seo_keywords' => 'BS 5839 maintenance, BS 5839 servicing, fire alarm maintenance schedule, six monthly fire alarm service, Stockport, Manchester',
            'focus_points' => [
                'Six-monthly inspection and servicing rhythm for typical BS 5839-1 systems',
                'Panel, power, device sample and logbook entry',
                'Defect list kept separate from the planned visit',
                'Domestic BS 5839-6 work matched to the installed grade',
            ],
            'faq' => $faq(
                'How often is BS 5839 maintenance due?',
                'On most non-domestic systems the gap between competent-person visits should not exceed six months. The weekly user test still sits with the responsible person.',
                'Is maintenance the same as a repair?',
                'No. Maintenance records condition and carries out the scheduled service. Repairs are quoted when a fault is found.',
                'What does it cost?',
                'Price on application after we know the panel, the device count and how many sites are on the contract.'
            ),
        ],
        [
            'slug' => 'fire-alarm-ppm',
            'lane' => 'maintain',
            'name' => 'Fire Alarm PPM',
            'related' => 'fire-alarm-maintenance-contract',
            'intro' => 'Fire alarm PPM is a planned preventive maintenance programme: dated visits, a named system list and paperwork a facilities manager can file. Icomply builds PPM schedules for single sites and small estates across the North West.',
            'body' => 'A PPM plan states visit frequency, what is tested, how defects are reported and how the logbook is updated. It sits alongside the weekly user test, which stays with the responsible person on site. Multi-site PPM uses one schedule so Kentec, Advanced, C-Tec, Morley, Hochiki and Apollo panels are not split across unrelated contractors. Fees are quoted from the asset list. We do not print a per-visit pound rate.',
            'meta_desc' => 'Fire alarm PPM programmes for single sites and estates. BS 5839 visit calendar, defect reporting and logbooks. Quoted from your asset list.',
            'seo_keywords' => 'fire alarm PPM, planned fire alarm maintenance, fire alarm maintenance programme, multi-site fire alarm PPM, Stockport, North West',
            'focus_points' => [
                'Visit calendar aligned to BS 5839 servicing intervals',
                'Asset list of panels and sites',
                'Defect reporting separate from the planned visit',
                'One contract for mixed panel brands',
            ],
            'faq' => $faq(
                'Does PPM replace the weekly test?',
                'No. Weekly call-point tests stay with the premises. PPM is the competent-person inspection and servicing programme.',
                'Can one PPM cover several buildings?',
                'Yes. Share the site list, panel brands and last service dates and we quote one schedule.',
                'Will I get certificates?',
                'Each planned visit produces the service record and logbook entry agreed in the contract.'
            ),
        ],
        [
            'slug' => 'false-alarm-investigation',
            'lane' => 'service',
            'name' => 'False Alarm Investigation',
            'related' => 'fire-alarm-fault-finding',
            'intro' => 'Repeated false alarms waste evacuations and can draw unwanted fire and rescue attendance. Icomply investigates the device, the cause and the panel history so the fault is fixed rather than silenced.',
            'body' => 'We attend with the logbook, identify the address or zone, and look for cooking fumes, steam, dust, plant interfaces, damaged call points and devices at end of life. On an addressable system we use the device text and the event log. The recommendation may be a detector-type change, a cause-and-effect tidy-up, or a maintenance visit if the system has lapsed. The visit is quoted after you tell us the panel make and how often it has activated.',
            'meta_desc' => 'False alarm investigation for commercial fire alarm systems across Greater Manchester and the North West. Device, cause and a written next step. Quote after the panel details.',
            'seo_keywords' => 'false alarm investigation, fire alarm false alarms, unwanted fire alarm signals, fire alarm fault finding Stockport, Manchester',
            'focus_points' => [
                'Device or zone identified before parts are changed',
                'Event log and logbook reviewed',
                'Cause traced to the environment, the device or an interface',
                'Written next step: clean, replace or reprogramme',
            ],
            'faq' => $faq(
                'Can you stop false alarms from a kitchen detector?',
                'Often a heat or multi-sensor device suits a kitchen better than a smoke detector. We confirm that against the fire strategy rather than swapping heads ad hoc.',
                'Do you silence the panel and leave?',
                'Silencing is not the repair. We record the event and agree the remedial work.',
                'Is there a call-out price list?',
                'No. Share the panel brand, postcode and whether the system is still in alarm.'
            ),
        ],
        [
            'slug' => 'fire-alarm-call-out',
            'lane' => 'service',
            'name' => 'Fire Alarm Call-Out',
            'related' => 'fire-alarm-repair',
            'intro' => 'A fire alarm call-out is for a panel in fault, a system stuck in alarm, or a site that cannot reset. Icomply sends a fire alarm engineer from Stockport across the North West to make the system safe and record what failed.',
            'body' => 'Tell us the panel brand, the text on the display, and whether people are still in the building. We restore indication where we can, isolate only with the responsible person’s agreement, and replace common service parts such as batteries when they are the cause. If the fault needs a return visit or a replacement device, that work is quoted separately. We do not publish a call-out tariff.',
            'meta_desc' => 'Fire alarm engineer call-out across Stockport, Manchester and the North West. Panel faults, systems that will not reset, and a written fault note. Price on application.',
            'seo_keywords' => 'fire alarm call-out, fire alarm engineer call out, fire alarm panel fault, emergency fire alarm repair, Stockport, North West',
            'focus_points' => [
                'Panel brand and fault text captured before attendance',
                'Make-safe attendance and a written fault note',
                'Common service parts fitted when they are the cause',
                'Follow-up repair quoted separately',
            ],
            'faq' => $faq(
                'What should we do while we wait?',
                'Follow the premises fire procedure. If the system will not reset, keep the responsible person on site and do not leave a fault isolated without a record.',
                'Which panels do you attend?',
                'Kentec, Advanced, C-Tec, Morley, Hochiki, Apollo and other common UK commercial systems.',
                'How is the visit priced?',
                'Price on application. Distance, time on site and parts are confirmed from the fault, not from a menu.'
            ),
        ],
        [
            'slug' => 'addressable-fire-alarm',
            'lane' => 'install',
            'name' => 'Addressable Fire Alarm',
            'related' => 'bs-5839-fire-alarm',
            'intro' => 'An addressable fire alarm names the exact detector or call point in alarm, instead of a whole zone. Icomply designs, installs and services addressable systems to BS 5839 for offices, factories, care premises and multi-let buildings across Stockport and the North West.',
            'body' => 'We size the loops, write plain-English device text, and programme cause-and-effect for doors, plant and interfaces. Where a conventional system has outgrown its zones, the changeover is planned so the building stays covered. The quote follows a survey of the building and the existing panel — price on application.',
            'meta_desc' => 'Addressable fire alarm design, install and service to BS 5839. Stockport, Manchester and the North West. Quote after survey.',
            'seo_keywords' => 'addressable fire alarm, addressable fire alarm installation, BS 5839 addressable, fire alarm North West, Stockport',
            'focus_points' => [
                'Loop design and plain-English device text',
                'Cause-and-effect for doors, plant and interfaces',
                'Commissioning, zone chart and user handover',
                'Service visits available after install',
            ],
            'faq' => $faq(
                'When is addressable the right choice?',
                'Larger buildings, sites with repeated false alarms, or premises that need the exact device location usually justify addressable equipment.',
                'Can you take over an addressable system someone else installed?',
                'Yes. We service common UK commercial panels, clear faults and update the logbook.',
                'How is it priced?',
                'After survey. Device count, loop length and whether the existing cable can stay all change the scope.'
            ),
        ],
        [
            'slug' => 'commercial-fire-alarm',
            'lane' => 'install',
            'name' => 'Commercial Fire Alarm',
            'related' => 'fire-alarm-installation',
            'intro' => 'A commercial fire alarm has to match the premises, the fire strategy and the people who use the building. Icomply surveys, installs and maintains commercial detection for offices, retail, industrial units and multi-occupied blocks across Greater Manchester and the North West.',
            'body' => 'Category, and whether the system is conventional or addressable, comes from the building rather than a default package. We commission to BS 5839, hand over a zone chart and logbook, and can stay on for planned servicing. The figure is quoted after we know the floor area, the device count and the access.',
            'meta_desc' => 'Commercial fire alarm installation and servicing to BS 5839 across Stockport, Manchester and the North West. Written quote after survey.',
            'seo_keywords' => 'commercial fire alarm, commercial fire alarm installation, office fire alarm, industrial fire alarm, BS 5839, Manchester, Stockport',
            'focus_points' => [
                'Survey against the building and the fire strategy',
                'Conventional or addressable system to an agreed category',
                'Commissioning, zone chart and logbook',
                'Planned servicing offered with the install',
            ],
            'faq' => $faq(
                'Do you cover offices and industrial units?',
                'Yes. Shops, offices, warehouses and multi-let commercial buildings are typical commercial fire alarm work for us.',
                'Will you maintain it after installation?',
                'Yes. Installation and the BS 5839 servicing visits can sit on one contract.',
                'Do you list commercial prices?',
                'No. The quote follows the survey. We do not publish a fee for a commercial system.'
            ),
        ],
        [
            'slug' => 'wireless-fire-alarm',
            'lane' => 'install',
            'name' => 'Wireless Fire Alarm',
            'related' => 'wireless-fire-alarm-system',
            'intro' => 'A wireless fire alarm is used where cabling is disruptive: occupied lets, sensitive building fabric, or short-term cover while a wired system is planned. Icomply surveys radio coverage and documents the system for the responsible person.',
            'body' => 'Wireless is not a shortcut around BS 5839-1 or BS 5839-6. We check construction, interference and battery maintenance before recommending it, and the logbook and service rhythm still apply. A hybrid of wired and wireless is used where only part of the building cannot be cabled. Price on application after the survey.',
            'meta_desc' => 'Wireless fire alarm surveys and installation across the North West. Radio coverage, hybrid options and BS 5839 documentation. Quote after survey.',
            'seo_keywords' => 'wireless fire alarm, wireless fire alarm installation, radio fire alarm, hybrid fire alarm, BS 5839, Stockport',
            'focus_points' => [
                'Radio survey before equipment is specified',
                'Hybrid wired and wireless where only part of the building needs it',
                'Battery maintenance included in the service rhythm',
                'Logbook and certificates, same as a wired system',
            ],
            'faq' => $faq(
                'Is wireless acceptable in a commercial building?',
                'It can be, when the survey shows reliable coverage and the category matches the fire strategy. It is not automatic.',
                'Do wireless devices need maintenance?',
                'Yes. Battery condition and signal integrity are part of the planned service, not an optional extra.',
                'How do you price a wireless system?',
                'After the radio survey. Building construction and device count decide the scope.'
            ),
        ],
    ];
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function fireAlarmsLaneContentBlock(array $row): array
{
    return [
        'intro' => (string)$row['intro'],
        'body' => (string)$row['body'],
        'meta_desc' => (string)$row['meta_desc'],
        'seo_keywords' => (string)$row['seo_keywords'],
        'focus_points' => $row['focus_points'],
        'faq' => $row['faq'],
    ];
}

function fireAlarmsLaneBuild(): int
{
    $library = [];
    foreach (fireAlarmsLaneContentLibrary() as $row) {
        $library[keywordSlug((string)$row['slug'])] = $row;
    }

    $raw = loadJsonData('keywords', []);
    $jobs = [];
    $seen = [];
    foreach ($raw as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        if (areaSlug((string)($meta['service'] ?? '')) !== 'fire-alarms') {
            continue;
        }
        $slug = keywordSlug((string)$slug);
        if ($slug === '' || isset($seen[$slug]) || fireAlarmsLaneIsExcluded($slug)) {
            continue;
        }
        $seen[$slug] = true;
        $lane = isset($library[$slug]['lane']) ? (string)$library[$slug]['lane'] : fireAlarmsLaneClassify($slug);
        $job = [
            'lane' => $lane,
            'slug' => $slug,
            'name' => (string)($meta['name'] ?? $library[$slug]['name'] ?? keywordDisplayName($slug)),
            'service' => 'fire-alarms',
            'related' => keywordSlug((string)($meta['related'] ?? $library[$slug]['related'] ?? $slug)),
            'status' => 'live',
        ];
        if (isset($library[$slug])) {
            $job['content'] = fireAlarmsLaneContentBlock($library[$slug]);
        }
        $jobs[] = $job;
    }

    foreach ($library as $slug => $row) {
        if (isset($seen[$slug])) {
            continue;
        }
        $seen[$slug] = true;
        $jobs[] = [
            'lane' => (string)($row['lane'] ?? fireAlarmsLaneClassify($slug)),
            'slug' => $slug,
            'name' => (string)$row['name'],
            'service' => 'fire-alarms',
            'related' => keywordSlug((string)$row['related']),
            'status' => 'live',
            'content' => fireAlarmsLaneContentBlock($row),
        ];
    }

    usort($jobs, static function (array $a, array $b): int {
        $lane = strcmp((string)$a['lane'], (string)$b['lane']);
        return $lane !== 0 ? $lane : strcmp((string)$a['slug'], (string)$b['slug']);
    });

    $counts = ['install' => 0, 'maintain' => 0, 'service' => 0];
    foreach ($jobs as $job) {
        if (isset($counts[$job['lane']])) {
            $counts[$job['lane']]++;
        }
    }

    $payload = [
        'service' => 'fire-alarms',
        'lanes' => ['install', 'maintain', 'service'],
        'count' => count($jobs),
        'counts' => $counts,
        'generated' => gmdate('c'),
        'jobs' => $jobs,
    ];
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        fwrite(STDERR, "JSON encode failed\n");
        return 1;
    }
    file_put_contents(fireAlarmsLaneFile(), $json . "\n");
    fireAlarmsLaneReset();
    if (function_exists('loadJsonData')) {
        loadJsonData('__clear__');
    }

    $outDir = SITE_ROOT . '/pages/keywords';
    if (!is_dir($outDir)) {
        mkdir($outDir, 0755, true);
    }
    $written = 0;
    foreach ($jobs as $job) {
        $slug = $job['slug'];
        $stub = "<?php\n"
            . "/** AUTO-GENERATED Fire alarms lane stub — php website/bin/build-fire-alarms-lane.php */\n"
            . "require_once __DIR__ . '/../../includes/render.php';\n"
            . 'renderKeywordPage(' . var_export($slug, true) . ");\n";
        file_put_contents(fireAlarmsLaneStubPath($slug), $stub);
        $written++;
    }

    echo 'Wrote ' . count($jobs) . ' Fire Alarms lane jobs → ' . fireAlarmsLaneFile() . "\n";
    echo "Wrote {$written} stubs → {$outDir}/<slug>.php\n";
    echo 'fire_alarms_lane_count=' . count($jobs)
        . ' install=' . $counts['install']
        . ' maintain=' . $counts['maintain']
        . ' service=' . $counts['service'] . "\n";
    return 0;
}

if (isset($_SERVER['SCRIPT_FILENAME']) && realpath((string)$_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    exit(fireAlarmsLaneBuild());
}
