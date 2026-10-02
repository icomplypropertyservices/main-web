#!/usr/bin/env php
<?php
/**
 * Build the security-systems lane catalogue (intruder / alarm only).
 *
 * Usage: php website/bin/build-security-systems-jobs.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/** @return list<string> */
function securitySystemsCanonicalSlugs(): array
{
    return [
        'ajax-alarm-installation',
        'alarm-after-false-trip-visit',
        'alarm-app-user-setup',
        'alarm-battery-replacement',
        'alarm-communicator-upgrade',
        'alarm-keyfob-programming',
        'alarm-keypad-installation',
        'alarm-keypad-replacement',
        'alarm-maintenance-contract',
        'alarm-monitoring-setup',
        'alarm-panel-upgrade',
        'alarm-system-design',
        'alarm-system-upgrade',
        'bs-4737',
        'burglar-alarm-installation',
        'burglar-alarm-service',
        'burglar-alarm-system',
        'business-premises-alarm',
        'commercial-intruder-alarm',
        'domestic-burglar-alarm',
        'door-contact-alarm-installation',
        'external-siren-replace',
        'grade-2-intruder-alarm',
        'grade-3-intruder-alarm',
        'grade-shift-survey',
        'home-intruder-alarm',
        'honeywell-intruder-alarm',
        'intruder-alarm-certification',
        'intruder-alarm-engineer',
        'intruder-alarm-engineers',
        'intruder-alarm-installation',
        'intruder-alarm-maintenance',
        'intruder-alarm-maintenance-contract',
        'intruder-alarm-monitoring',
        'intruder-alarm-near-me',
        'intruder-alarm-panel',
        'intruder-alarm-panel-replacement',
        'intruder-alarm-repair',
        'intruder-alarm-servicing',
        'intruder-alarm-testing',
        'landlord-void-alarm-set',
        'monitored-intruder-alarm',
        'multi-site-alarm-maintenance',
        'office-alarm-installation',
        'pd-6662-alarm-system',
        'pd6662-alarm-system',
        'pir-sensor-installation',
        'police-response-alarm',
        'pyronix-alarm-installation',
        'residential-intruder-alarm',
        'risco-alarm-installation',
        'security-alarm-installation',
        'shock-sensor-installation',
        'shop-alarm-installation',
        'smart-home-alarm-installation',
        'texecom-alarm-installation',
        'texecom-intruder-alarm',
        'warehouse-alarm-system',
        'warehouse-intruder-alarm',
        'wired-intruder-alarm',
        'wired-intruder-alarm-system',
        'wireless-intruder-alarm',
    ];
}

/** @return array<string, array<string, mixed>> */
function securitySystemsGapJobs(): array
{
    $poa = 'Price on application after a survey. We do not publish a pound figure for this visit.';
    return [
        'alarm-after-false-trip-visit' => [
            'name' => 'Alarm After False Trip Visit',
            'related' => 'intruder-alarm-repair',
            'intro' => 'An alarm after false trip visit finds why a panel keeps setting off when the building is empty or when staff are still inside. Icomply engineers from Stockport check detectors, door contacts, pets, heaters and user habits, then leave the system stable enough to set with confidence.',
            'body' => 'We read the event log, walk the zones that tripped, and test contacts and PIRs in the conditions that caused the activation. Loose contacts, spiders in sensors, door sag and keypad codes left unset are common. The visit is intruder-alarm work: we do not add CCTV cameras or door-entry handsets on this call. ' . $poa,
            'meta_desc' => 'Alarm after false trip visit across Greater Manchester and the North West. Log check, zone walk and reset. Price on application.',
            'seo_keywords' => 'alarm false alarm visit, intruder alarm false trip, alarm keeps going off, burglar alarm reset North West, Stockport alarm engineer',
            'focus_points' => [
                'Event log and zone walk after a false trip',
                'Contacts, PIRs and user setting checked on site',
                'Intruder system only — CCTV and access control quoted separately',
                'Price on application once the cause is known',
            ],
            'faq' => [
                ['Why does my alarm keep going off when the shop is closed?', 'Often a door contact, a heater under a PIR, or a zone left open. We read the log and test the zone that tripped rather than swapping the whole panel first.'],
                ['Will you change the monitoring on this visit?', 'Only if the signalling path is part of the fault. A straight false-trip visit is diagnosis and a practical fix, with monitoring changes quoted separately if needed.'],
                ['Is this a CCTV call-out?', 'No. This lane is intruder and alarm systems. Camera or access-control faults are separate services.'],
            ],
        ],
        'alarm-app-user-setup' => [
            'name' => 'Alarm App User Setup',
            'related' => 'smart-home-alarm-installation',
            'intro' => 'Alarm app user setup puts the right people on the panel app with the right permissions — set, unset, and alerts — without handing everyone engineer-level access. Icomply programmes apps for homes, shops and small offices across the North West after the panel is already online.',
            'body' => 'We confirm the panel supports the manufacturer app, create users, and show keyholders how to set part areas and what a tamper alert means. Wi-Fi or dual-path signalling has to be healthy first; if the communicator is old we say so before creating accounts. This is alarm user setup, not a CCTV remote-viewing install. ' . $poa,
            'meta_desc' => 'Alarm app user setup for intruder panels. Keyholder permissions and handover across Manchester, Stockport and the North West. Price on application.',
            'seo_keywords' => 'alarm app setup, intruder alarm app users, burglar alarm smartphone, panel app programming, North West alarm app',
            'focus_points' => [
                'App users created with the right set and unset rights',
                'Part-set and alert meanings explained at handover',
                'Communicator checked before accounts are issued',
                'Price on application — no app-store price list',
            ],
            'faq' => [
                ['Can every staff member have the alarm app?', 'Yes, with limits. We give managers fuller control and staff a set/unset user so engineer menus stay locked.'],
                ['Does the app replace the keypad?', 'Usually no. The keypad stays as the local fallback if the phone or broadband drops.'],
                ['Do you set up CCTV apps on the same visit?', 'Not as part of this job. Camera viewing is a CCTV service. This visit is the intruder panel app only.'],
            ],
        ],
        'alarm-communicator-upgrade' => [
            'name' => 'Alarm Communicator Upgrade',
            'related' => 'alarm-monitoring-setup',
            'intro' => 'An alarm communicator upgrade replaces a tired digital dialler or single-path device with signalling that still reaches an alarm receiving centre when the phone line or broadband fails. Icomply surveys the existing panel and paths, then fits a suitable communicator for homes and commercial sites in the North West.',
            'body' => 'Many older panels still rely on a PSTN path that networks have withdrawn. We identify the panel, the current path, and whether dual-path (radio plus IP) is what the insurer or ARC expects. The upgrade is signalling for the intruder system. It is not an access-control network or a CCTV NVR. ' . $poa,
            'meta_desc' => 'Alarm communicator upgrade for intruder panels. Dual-path and ARC signalling across the North West. Price on application after survey.',
            'seo_keywords' => 'alarm communicator upgrade, dual path alarm signalling, PSTN alarm replacement, intruder alarm ARC, digi modem upgrade Stockport',
            'focus_points' => [
                'Panel and existing signalling path identified first',
                'PSTN replacements and dual-path options explained',
                'ARC polling checked where monitoring is already in place',
                'Price on application after the survey',
            ],
            'faq' => [
                ['My phone line is being withdrawn. Will the alarm still signal?', 'Not if it only uses that line. We survey the panel and quote a communicator that matches the monitoring you need.'],
                ['Do you move the monitoring contract?', 'We fit and test the path. The monitoring contract stays with your ARC unless you ask us to help introduce a new one.'],
                ['Is this the same as adding cameras?', 'No. A communicator sends alarm events. Cameras are CCTV and are quoted as a different service.'],
            ],
        ],
        'alarm-keyfob-programming' => [
            'name' => 'Alarm Keyfob Programming',
            'related' => 'alarm-keypad-installation',
            'intro' => 'Alarm keyfob programming adds, replaces or deletes radio fobs so only current keyholders can set and unset the intruder system. Icomply attends homes, HMOs and small commercial sites across Greater Manchester when fobs are lost, staff change, or a new fob will not learn.',
            'body' => 'We identify the panel and receiver, delete lost fobs so they cannot unset the system, and teach replacements. If the radio receiver is full or the fob is the wrong family, we say so before ordering parts. Programming is intruder-user work. It is not access-control fob enrolment for door readers. ' . $poa,
            'meta_desc' => 'Alarm keyfob programming and lost-fob deletion for intruder panels. North West engineers. Price on application.',
            'seo_keywords' => 'alarm keyfob programming, burglar alarm fob, lost alarm fob delete, intruder fob replacement, Stockport alarm fob',
            'focus_points' => [
                'Lost fobs deleted so they cannot unset the system',
                'Replacement fobs learned to the correct user',
                'Panel family confirmed before parts are ordered',
                'Price on application — alarm fobs, not door-access tokens',
            ],
            'faq' => [
                ['A member of staff left with the alarm fob. What should we do?', 'Delete that fob from the panel before you rely on the system. We can do that on site and add a replacement for the new keyholder.'],
                ['Will a door-access fob work on the alarm?', 'No. Alarm fobs talk to the intruder receiver. Door tokens are access control and need a separate enrolment.'],
                ['Can you programme fobs without the engineer code?', 'We need the engineer or master access that the panel requires. If those codes are lost, the visit may include a panel reset, quoted once we see the equipment.'],
            ],
        ],
        'external-siren-replace' => [
            'name' => 'External Siren Replace',
            'related' => 'intruder-alarm-repair',
            'intro' => 'External siren replace swaps a failed, faded or constantly tampered bell box so the intruder system can still warn outside the building. Icomply fits a siren matched to the panel, with a working tamper and strobe, for homes and commercial frontages across the North West.',
            'body' => 'We check why the old box failed — battery, tamper switch, cable, or water in the cover — before fitting a replacement. The strobe and sounder are proved from a test activation, not left assumed. This is the alarm bell box, not a CCTV speaker or an access-control door buzzer. ' . $poa,
            'meta_desc' => 'External siren and bell-box replacement for intruder alarms. Tamper and strobe proved on site. North West. Price on application.',
            'seo_keywords' => 'external siren replacement, alarm bell box replace, intruder sounder, bell box tamper, burglar alarm siren Stockport',
            'focus_points' => [
                'Cause of the failed bell box checked before parts',
                'Tamper and strobe proved from a test activation',
                'Siren matched to the existing intruder panel',
                'Price on application after we see the mounting and cable',
            ],
            'faq' => [
                ['The bell box is silent but the keypad still beeps. Do I need a new panel?', 'Not always. A dead external siren, flat bell battery or open tamper is common. We test the output before condemning the panel.'],
                ['Will a new siren stop false alarms?', 'It restores the external warning. If the system is tripping falsely, that is a separate zone fault and we will say so on the visit.'],
                ['Can you hide the siren for aesthetics?', 'The box still needs a sound path and a tamper. We agree a practical position on the elevation rather than burying it where it cannot be heard.'],
            ],
        ],
        'grade-shift-survey' => [
            'name' => 'Grade Shift Survey',
            'related' => 'grade-2-intruder-alarm',
            'intro' => 'A grade shift survey checks whether an intruder system should move up or down a grade — typically toward Grade 2 or Grade 3 — after an insurer letter, a change of stock, or a refit. Icomply surveys detection, tamper and signalling against PD 6662 and BS EN 50131 practice and writes what would have to change.',
            'body' => 'We walk the risk points, note the current panel grade claim, and list gaps: missing contacts, single-path signalling, or detection that no longer matches how the building is used. The survey is advice and a scope for later works. It is not a CCTV design and not an access-control schedule. ' . $poa,
            'meta_desc' => 'Intruder alarm grade shift survey to PD 6662 / BS EN 50131. Written scope for Grade 2 or Grade 3. Price on application.',
            'seo_keywords' => 'alarm grade survey, Grade 2 upgrade survey, PD 6662 grade shift, BS EN 50131 survey, intruder grade North West',
            'focus_points' => [
                'Current grade claim compared with how the building is used',
                'Detection, tamper and signalling gaps listed in writing',
                'PD 6662 / BS EN 50131 used as the reference',
                'Price on application — survey first, upgrade quoted separately',
            ],
            'faq' => [
                ['Our insurer asked for Grade 2. Is the survey the upgrade?', 'The survey says what is already compliant and what would have to change. The upgrade is a separate install once you accept the scope.'],
                ['Can you confirm Grade 3 on paper without a visit?', 'No. Grade depends on the devices, the panel and the signalling we can see on site.'],
                ['Does the survey include cameras?', 'No. This is an intruder grade survey. CCTV coverage is a different design service.'],
            ],
        ],
        'landlord-void-alarm-set' => [
            'name' => 'Landlord Void Alarm Set',
            'related' => 'intruder-alarm-maintenance',
            'intro' => 'A landlord void alarm set makes an empty rental ready to leave secure between tenancies: panel healthy, codes changed, and a named person able to set and unset. Icomply attends voids across Greater Manchester so agents are not relying on a previous tenant’s fob or a panel showing a fault.',
            'body' => 'We change user codes, remove old fobs, test setting, and note any zone that will not set because a contact or PIR has failed. If the system is beyond a void tidy-up we quote repair separately. The visit is the intruder alarm in the void, not cameras in the communal stair or a door-entry handset. ' . $poa,
            'meta_desc' => 'Landlord void alarm set: codes changed, old fobs removed, system proved. North West rentals. Price on application.',
            'seo_keywords' => 'landlord void alarm, alarm code change tenancy, empty property intruder alarm, rental alarm set, Stockport landlord alarm',
            'focus_points' => [
                'Previous tenant codes and fobs removed',
                'Set and unset proved before the property is left empty',
                'Faults that block setting reported before we leave',
                'Price on application for the void visit',
            ],
            'faq' => [
                ['The last tenant still has the alarm code. Can you change it?', 'Yes. We change user codes and delete their fobs so the void is not left on the old credentials.'],
                ['The alarm will not set. Is that included?', 'We find the zone that is stopping the set. A failed detector or contact is quoted as a repair if it is more than the void tidy-up.'],
                ['Do you install CCTV in the void as well?', 'Not on this job. Ask for CCTV separately if the brief includes cameras.'],
            ],
        ],
    ];
}

$expected = securitySystemsExpectedCount();
$slugs = securitySystemsCanonicalSlugs();
$gaps = securitySystemsGapJobs();

if (count($slugs) !== $expected || count(array_unique($slugs)) !== $expected) {
    fwrite(STDERR, "Canonical slug list is " . count($slugs) . " unique " . count(array_unique($slugs)) . ", expected {$expected}\n");
    exit(1);
}

$keywords = loadJsonData('keywords', []);
if (!is_array($keywords)) {
    fwrite(STDERR, "keywords.json unreadable\n");
    exit(1);
}

$jobs = [];
$missing = [];
foreach ($slugs as $slug) {
    if (securitySystemsSlugIsOutOfLane($slug)) {
        fwrite(STDERR, "Out-of-lane slug in canonical list: {$slug}\n");
        exit(1);
    }
    $base = $keywords[$slug] ?? null;
    $gap = $gaps[$slug] ?? null;
    if (!is_array($base) && !is_array($gap)) {
        $missing[] = $slug;
        continue;
    }
    $name = '';
    if (is_array($base) && !empty($base['name'])) {
        $name = (string)$base['name'];
    } elseif (is_array($gap)) {
        $name = (string)$gap['name'];
    }
    $related = 'intruder-alarm-installation';
    if (is_array($base) && !empty($base['related'])) {
        $related = keywordSlug((string)$base['related']);
    } elseif (is_array($gap) && !empty($gap['related'])) {
        $related = keywordSlug((string)$gap['related']);
    }
    $job = [
        'category' => securitySystemsCategory(),
        'lane' => securitySystemsLane(),
        'service_type' => securitySystemsService(),
        'slug' => $slug,
        'status' => 'live',
        'name' => $name,
        'related' => $related,
        'h1' => $name,
        'seo_title' => $name . ' | Security systems | North West',
    ];
    if (!is_array($base) && is_array($gap)) {
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords', 'focus_points', 'faq'] as $field) {
            if (isset($gap[$field])) {
                $job[$field] = $gap[$field];
            }
        }
    }
    $jobs[] = $job;
}

if ($missing) {
    fwrite(STDERR, 'Missing content for: ' . implode(', ', $missing) . "\n");
    exit(1);
}

$payload = [
    'count' => count($jobs),
    'category' => securitySystemsCategory(),
    'lane' => securitySystemsLane(),
    'service_type' => securitySystemsService(),
    'scope' => 'Intruder and alarm systems only. Excludes CCTV, access control, door entry and intercoms.',
    'excluded_services' => securitySystemsExcludedServices(),
    'generated' => 'security-systems-lane',
    'jobs' => $jobs,
];

$path = securitySystemsFile();
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if (!is_string($json)) {
    fwrite(STDERR, "JSON encode failed\n");
    exit(1);
}
file_put_contents($path, $json . "\n");
securitySystemsReset();
loadJsonData('__clear__');

echo 'security_systems_jobs=' . count($jobs) . ' file=' . $path . PHP_EOL;
exit(count($jobs) === $expected ? 0 : 1);
