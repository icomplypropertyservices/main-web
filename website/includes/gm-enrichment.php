<?php
/**
 * Extra body, FAQ, image and internal links for Greater Manchester town pages.
 *
 * Builds on icomplyGmTownBlurb() and the existing chip clouds. Does not
 * replace those blocks. Uniqueness tokens (bNNNNN) stay in the HTML and are
 * hidden with .seo-brief-id.
 */
declare(strict_types=1);

function icomplyGmBriefHtml(string $text): string
{
    $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $hidden = preg_replace(
        '/\b(b\d{4,6})\b/',
        '<span class="seo-brief-id">$1</span>',
        $safe
    );
    return is_string($hidden) ? $hidden : $safe;
}

function icomplyGmTownAllowed(string $townName, string $kind = 'service'): bool
{
    $slug = function_exists('areaSlug') ? areaSlug($townName) : strtolower(trim($townName));
    if ($kind === 'keyword') {
        return in_array($slug, ['bolton', 'manchester', 'stockport'], true);
    }
    if (function_exists('icomplyIsGreaterManchesterAreaSlug')) {
        return icomplyIsGreaterManchesterAreaSlug($townName);
    }
    return false;
}

function icomplyGmTownTag(string $townSlug): string
{
    static $tags = null;
    if ($tags === null) {
        $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/gm-blurbs/_town_tags.json';
        $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $tags = is_array($decoded) ? $decoded : [];
    }
    return (string)($tags[$townSlug] ?? '');
}

/**
 * @return array{summary:string,points:list<string>,gas:bool,siblings:list<string>,keywords:list<string>,job:string}
 */
function icomplyGmServicePack(string $slug): array
{
    $packs = [
        'access-control' => [
            'summary' => 'Access control covers readers, tokens, keypads and releases on the doors that are actually in use. The visit checks fire-alarm release, power backup and who can administer fobs before any hardware is specified.',
            'points' => [
                'Door count, lock type and whether the stair is a fire escape',
                'Fob, code or phone credentials, and a named person who will manage them',
                'Fail-safe release tied to the fire alarm where that interface exists',
                'POA quote after the door schedule is known',
            ],
            'siblings' => ['cctv', 'door-entry', 'intercoms', 'barriers'],
            'keywords' => ['access-control-near-me', 'access-control-installation'],
            'job' => '',
        ],
        'cctv' => [
            'summary' => 'CCTV work is a coverage plan, not a box of cameras. Entrances, yards, stairs and stock areas are walked so the recorder, retention and signage match how the building is used.',
            'points' => [
                'Camera positions agreed on site before cable routes are fixed',
                'Recorder storage sized for the retention the occupier actually needs',
                'Remote viewing only for named users, with passwords left in their control',
                'Signage and viewing angles discussed so private rooms are not filmed by accident',
            ],
            'siblings' => ['access-control', 'intruder-alarm', 'door-entry', 'intercoms'],
            'keywords' => ['cctv-installation', 'cctv-near-me'],
            'job' => '',
        ],
        'fire-alarms' => [
            'summary' => 'Fire alarm attendance starts from the panel, the zone chart and the devices that are really on the ceilings. Conventional heads and addressable loops are not written up as the same system.',
            'points' => [
                'Panel location, zone list and any door-release or plant interface',
                'Service or fault visit booked against the previous certificate when you have it',
                'Devices that cannot be reached are logged as not tested',
                'POA after the category and device count are known',
            ],
            'siblings' => ['emergency-lighting', 'fire-risk-assessments', 'fire-doors', 'aov-air-handling'],
            'keywords' => ['fire-alarm-servicing', 'fire-alarm-installation'],
            'job' => 'fire-alarms',
        ],
        'emergency-lighting' => [
            'summary' => 'Emergency lighting tests follow the fittings that can be found on the escape route. Duration is taken from the label on the luminaire, and locked stairs are recorded as not entered.',
            'points' => [
                'Monthly function or annual duration test, stated on the quote',
                'Maintained and non-maintained fittings listed separately',
                'Failed batteries identified by location, not by a bare count',
                'Logbook page left for the dutyholder file',
            ],
            'siblings' => ['fire-alarms', 'fire-risk-assessments', 'electrical', 'aov-air-handling'],
            'keywords' => ['emergency-lighting-test', 'emergency-lighting-installation'],
            'job' => '',
        ],
        'electrical' => [
            'summary' => 'Electrical visits for landlords and managers are usually an inspection of the fixed installation: boards, circuits and what could not be isolated because someone was still in the building.',
            'points' => [
                'Board location, approximate circuit count and whether the loft is boarded',
                'EICR coded to the installation as found, including older fuses',
                'Remedial work quoted after the report, not before the test',
                'POA once access and the number of boards are confirmed',
            ],
            'siblings' => ['pat-testing', 'emergency-lighting', 'landlord-compliance', 'ev-chargers'],
            'keywords' => ['eicr', 'eicr-near-me'],
            'job' => 'eicr',
        ],
        'gas-systems' => [
            'summary' => 'Landlord gas safety records list the appliances, flues and pipework that were checked. The gas work is carried out by Gas Safe registered engineers. iComply does not print its own Gas Safe registration on this page.',
            'points' => [
                'Appliance list and meter location sent with the enquiry',
                'Safety record for the letting file, separate from a full boiler service',
                'Defects written on the record before any remedial visit is priced',
                'POA after the appliance count is known',
            ],
            'gas' => true,
            'siblings' => ['heating', 'electrical', 'landlord-compliance', 'fire-alarms'],
            'keywords' => ['gas-safety-certificate', 'gas-safety-near-me'],
            'job' => 'gas-safety-cp12',
        ],
        'heating' => [
            'summary' => 'Heating enquiries cover boilers, controls and heat emitters that are already in the property. Where the appliance is gas, that gas work is carried out by Gas Safe registered engineers.',
            'points' => [
                'Boiler type, age and whether the property is occupied',
                'Service, repair or replacement scoped separately',
                'No catalogue boiler price on this page',
                'POA fixed quote after scope',
            ],
            'gas' => true,
            'siblings' => ['gas-systems', 'plumbing', 'electrical', 'landlord-compliance'],
            'keywords' => ['boiler', 'boiler-service'],
            'job' => '',
        ],
        'nurse-call' => [
            'summary' => 'Nurse call work is planned around the rooms that staff actually answer: bedrooms, bathrooms, WCs and the panel at the staff base. Existing systems are identified before parts are proposed.',
            'points' => [
                'Room list and whether residents stay in place during the visit',
                'Pull cords, call points and reset units checked against the panel',
                'Faults logged by room, not as a single “system down” line',
                'POA after the room count and brand are known',
            ],
            'siblings' => ['fire-alarms', 'door-entry', 'access-control', 'emergency-lighting'],
            'keywords' => ['nurse-call-near-me', 'care-home-nurse-call'],
            'job' => '',
        ],
        'barriers' => [
            'summary' => 'Vehicle barriers are scoped on the lane that exists: arm length, loops, safety edges and who holds the remotes. A quote is not taken from a brochure width.',
            'points' => [
                'Lane width, arm length and whether the barrier is left up overnight',
                'Safety edges, photocells and loop positions walked on site',
                'Access for a van and a place to isolate power',
                'POA after the lane is measured',
            ],
            'siblings' => ['access-control', 'cctv', 'door-entry', 'intercoms'],
            'keywords' => ['car-park-barrier', 'barrier-installation'],
            'job' => 'car-park-barrier',
        ],
        'aov-air-handling' => [
            'summary' => 'Smoke control and AOV visits start from the vents, the control panel and the cause-and-effect the building was actually designed with. We do not invent a category the panel does not support.',
            'points' => [
                'Vent locations, manual controls and the panel log',
                'Interfaces with the fire alarm confirmed before a test',
                'Failed or seized vents written up by stair or shaft',
                'POA after access to the roof or shaft is confirmed',
            ],
            'siblings' => ['fire-alarms', 'emergency-lighting', 'fire-risk-assessments', 'fire-doors'],
            'keywords' => ['aov-near-me', 'smoke-vent-service'],
            'job' => '',
        ],
        'door-entry' => [
            'summary' => 'Door entry covers audio and video panels, handsets and the release on the communal door. The survey notes how residents buzz visitors in, and whether the fire alarm must drop the lock.',
            'points' => [
                'Number of flats and whether the panel is audio or video',
                'Trades button, fob override and fire-alarm release',
                'Handsets that do not ring are listed by flat',
                'POA after the riser and the lock are seen',
            ],
            'siblings' => ['access-control', 'intercoms', 'cctv', 'barriers'],
            'keywords' => ['door-entry-systems', 'video-door-entry'],
            'job' => '',
        ],
        'fire-risk-assessments' => [
            'summary' => 'A fire risk assessment records hazards, people at risk and the measures already in the building. It is not an install package. Alarm, lighting and door work is quoted separately.',
            'points' => [
                'Floors, escape routes and how the building is occupied',
                'Significant findings and a review note for the file',
                'HMO licence conditions checked against what is on site, not assumed',
                'POA after the size and use of the building are known',
            ],
            'siblings' => ['fire-alarms', 'emergency-lighting', 'fire-doors', 'aov-air-handling'],
            'keywords' => ['fire-risk-assessment', 'hmo-fire-risk-assessment'],
            'job' => 'fire-risk-assessment',
        ],
        'asbestos-survey' => [
            'summary' => 'Asbestos survey scope depends on whether you need a management look at accessible materials or a more intrusive survey before refurbishment. Samples are only taken when that scope says so.',
            'points' => [
                'Building age, what work is planned, and occupied rooms',
                'Accessible materials recorded; voids opened only if agreed',
                'Report for the dutyholder file, not a removal quote by default',
                'POA after the survey type is agreed',
            ],
            'siblings' => ['legionella-risk-assessment', 'epc', 'building-surveys', 'fire-risk-assessments'],
            'keywords' => ['asbestos-survey', 'asbestos-management-survey'],
            'job' => '',
        ],
        'building-maintenance' => [
            'summary' => 'Building maintenance visits are booked against a defect list: leaks, doors, finishes and small repairs that agents need closed before a tenancy or an audit.',
            'points' => [
                'Photos and a room list before anyone travels',
                'Occupied homes and voids are different access notes',
                'Trade mix confirmed so the right person attends',
                'POA per item after the list is scoped',
            ],
            'siblings' => ['building-surveys', 'plumbing', 'electrical', 'heating'],
            'keywords' => ['building-maintenance', 'property-maintenance'],
            'job' => '',
        ],
        'epc' => [
            'summary' => 'An EPC is an energy assessment of the dwelling as it stands. Floor area, heating and insulation are recorded from the visit, not from a listing photograph.',
            'points' => [
                'Property type, floor area and heating system',
                'Access to loft, boiler and windows',
                'Certificate lodged for the address that was inspected',
                'POA after the property type is confirmed',
            ],
            'siblings' => ['landlord-compliance', 'heating', 'electrical', 'asbestos-survey'],
            'keywords' => ['epc', 'epc-for-landlords'],
            'job' => '',
        ],
        'intruder-alarm' => [
            'summary' => 'Intruder alarm work covers panels, detectors and signalling that the occupier will actually set. A service visit is not treated as a new design unless the quote says so.',
            'points' => [
                'Panel brand, zone count and whether the system is monitored',
                'False alarms and device faults written into the visit note',
                'Codes changed only when the occupier asks and is present',
                'POA after the panel and the signalling are known',
            ],
            'siblings' => ['cctv', 'access-control', 'door-entry', 'fire-alarms'],
            'keywords' => ['intruder-alarm-near-me', 'intruder-alarm-service'],
            'job' => '',
        ],
        'intercoms' => [
            'summary' => 'Intercoms are scoped by the entrance and the handsets that must answer it. Audio-only and video systems are not swapped in the quote without a survey.',
            'points' => [
                'Entrance count and flat or office count',
                'Cable routes in the riser, or a radio system if the riser is blocked',
                'Handsets that fail are listed individually',
                'POA after the entrance is seen',
            ],
            'siblings' => ['door-entry', 'access-control', 'cctv', 'nurse-call'],
            'keywords' => ['intercom-near-me', 'intercom-installation'],
            'job' => '',
        ],
        'pat-testing' => [
            'summary' => 'PAT is a check of portable appliances, not a substitute for an EICR on the fixed wiring. The quote is based on the appliance count and whether the building stays open.',
            'points' => [
                'Appliance count, or a walk-round if the count is unknown',
                'Failed items labelled and listed for the file',
                'Fixed wiring reported separately when an EICR is also due',
                'POA after the count and access are known',
            ],
            'siblings' => ['electrical', 'landlord-compliance', 'emergency-lighting', 'fire-alarms'],
            'keywords' => ['pat-testing', 'pat-testing-near-me'],
            'job' => '',
        ],
        'legionella-risk-assessment' => [
            'summary' => 'A legionella risk assessment looks at the water system that is installed: tanks, calorifiers, outlets and how little-used rooms are flushed. It is not a treatment contract unless the quote adds one.',
            'points' => [
                'Storeys, outlets and any cold-water storage',
                'Void flats and little-used outlets called out in the findings',
                'Review date written for the dutyholder',
                'POA after the system is described',
            ],
            'siblings' => ['asbestos-survey', 'water-wras', 'plumbing', 'landlord-compliance'],
            'keywords' => ['legionella-risk-assessment', 'legionella-testing'],
            'job' => '',
        ],
        'plumbing' => [
            'summary' => 'Plumbing visits follow a leak, a failed fitting or a void that needs the water safe before a let. The scope is the fitting in front of the engineer, not a whole-house refit, unless that is what was asked for.',
            'points' => [
                'What is leaking, and whether the supply can be isolated',
                'Occupied homes need a tenant slot; voids need keys',
                'Parts confirmed before they are fitted',
                'POA after photos or a short description',
            ],
            'siblings' => ['heating', 'bathrooms', 'landlord-compliance', 'building-maintenance'],
            'keywords' => ['emergency-plumber', 'landlord-plumbing'],
            'job' => '',
        ],
        'landlord-compliance' => [
            'summary' => 'Landlord compliance visits line up the documents a letting file usually needs: electrical condition, alarm servicing where fitted, and gas records where appliances exist. Each trade stays in its own scope.',
            'points' => [
                'Portfolio list with postcodes, not a single town name',
                'Gas records carried out by Gas Safe registered engineers where gas is present',
                'One diary where the scopes can share access',
                'POA per certificate after each property is described',
            ],
            'gas' => true,
            'siblings' => ['electrical', 'gas-systems', 'fire-alarms', 'epc'],
            'keywords' => ['landlord-compliance', 'landlord-gas-safety-certificate'],
            'job' => 'landlord-compliance',
        ],
    ];

    $base = [
        'summary' => 'The visit is scoped to the building you name. Tell us the postcode, whether it is occupied, and which certificate or repair you need. The figure is POA after that scope, not a published tariff.',
        'points' => [
            'Address, access and the outcome you need on the file',
            'Survey or attendance only for the items on the quote',
            'Paperwork labelled for the agent or dutyholder',
            'POA fixed quote after scope',
        ],
        'gas' => false,
        'siblings' => ['electrical', 'fire-alarms', 'emergency-lighting', 'landlord-compliance'],
        'keywords' => [],
        'job' => '',
    ];
    $row = $packs[$slug] ?? $base;
    $row['gas'] = (bool)($row['gas'] ?? false);
    $row['job'] = (string)($row['job'] ?? '');
    $row['points'] = array_values($row['points'] ?? $base['points']);
    $row['siblings'] = array_values($row['siblings'] ?? []);
    $row['keywords'] = array_values($row['keywords'] ?? []);
    $row['summary'] = (string)($row['summary'] ?? $base['summary']);
    return $row;
}

function icomplyGmKeywordExists(string $slug): bool
{
    if (!function_exists('getMajorKeywords')) {
        return false;
    }
    $all = getMajorKeywords();
    return isset($all[$slug]);
}

function icomplyGmJobHref(string $jobSlug): string
{
    if ($jobSlug === '') {
        return '';
    }
    $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/pages/jobs/' . $jobSlug . '.php';
    if (!is_file($file)) {
        return '';
    }
    return url('/pages/jobs/' . $jobSlug);
}

function icomplyGmServiceTownHref(string $serviceSlug, string $townName): string
{
    if (!function_exists('getServices') || !isset(getServices()[$serviceSlug])) {
        return '';
    }
    $areas = function_exists('getAreasForService') ? getAreasForService($serviceSlug) : (function_exists('getAreas') ? getAreas() : []);
    if (in_array($townName, $areas, true)) {
        return url('/pages/' . $serviceSlug . '/' . areaSlug($townName));
    }
    return url('/pages/services/' . $serviceSlug);
}

/**
 * Keyword × town is exported for these towns on the default build.
 * Other towns only get that URL for priority keyword families, so we link the hub.
 *
 * @return list<string>
 */
function icomplyGmPopularTowns(): array
{
    return ['Manchester', 'Stockport', 'Bolton'];
}

function icomplyGmKeywordHref(string $keywordSlug, string $townName): string
{
    if (!icomplyGmKeywordExists($keywordSlug)) {
        return '';
    }
    if (in_array($townName, icomplyGmPopularTowns(), true) && function_exists('getAreas') && in_array($townName, getAreas(), true)) {
        return url('/pages/keywords/' . $keywordSlug . '/' . areaSlug($townName));
    }
    return url('/pages/keywords/' . $keywordSlug);
}

/**
 * @param list<array{0:string,1:string}> $faqs
 */
function icomplyGmFaqHtml(string $heading, array $faqs, string $chrome): string
{
    if (!$faqs) {
        return '';
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $entities = [];
    $items = '';
    foreach ($faqs as $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $q = (string)$faq[0];
        $a = (string)$faq[1];
        if ($q === '' || $a === '') {
            continue;
        }
        $entities[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
        if ($chrome === 'matrix') {
            $items .= '<details class="matrix-card"><summary>' . $h($q) . '</summary><p>' . $h($a) . '</p></details>';
        } else {
            $items .= '<details class="bg-white border border-zinc-200 rounded-2xl p-4"><summary class="font-semibold text-[#061828] cursor-pointer">'
                . $h($q) . '</summary><p class="mt-2 text-sm text-zinc-700 leading-relaxed">' . $h($a) . '</p></details>';
        }
    }
    if ($items === '') {
        return '';
    }
    $schema = '<script type="application/ld+json">' . json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    if ($chrome === 'matrix') {
        return $schema . '<section class="gm-faq" aria-label="Questions"><h2 class="text-2xl font-semibold">'
            . $h($heading) . '</h2><div class="mt-4 space-y-3">' . $items . '</div></section>';
    }
    return $schema . '<section class="gm-faq mt-8" aria-label="Questions"><h2 class="text-2xl font-bold text-[#061828]">'
        . $h($heading) . '</h2><div class="mt-4 space-y-3">' . $items . '</div></section>';
}

function icomplyGmImageHtml(string $src, string $alt, string $chrome): string
{
    if ($src === '') {
        return '';
    }
    $tag = '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="'
        . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8')
        . '" width="1200" height="800" loading="lazy" decoding="async" class="w-full h-48 object-cover">';
    if (function_exists('icomplyPerfRewriteHtml')) {
        $tag = icomplyPerfRewriteHtml($tag);
    }
    if ($chrome === 'matrix') {
        return '<figure class="matrix-figure matrix-card">' . $tag . '</figure>';
    }
    return '<figure class="mt-6 overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-100">' . $tag
        . '<figcaption class="px-4 py-2 text-xs text-zinc-600">' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '</figcaption></figure>';
}

/**
 * @param list<array{href:string,label:string}> $links
 */
function icomplyGmLinksHtml(string $heading, array $links, string $chrome): string
{
    $clean = [];
    foreach ($links as $link) {
        $href = (string)($link['href'] ?? '');
        $label = (string)($link['label'] ?? '');
        if ($href === '' || $label === '') {
            continue;
        }
        $clean[$href] = $label;
    }
    if (!$clean) {
        return '';
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $items = '';
    foreach ($clean as $href => $label) {
        if ($chrome === 'matrix') {
            $items .= '<a class="text-[#ff6b00] font-semibold" href="' . $h($href) . '">' . $h($label) . '</a>';
        } else {
            $items .= '<a class="px-4 py-2 bg-white border border-zinc-200 rounded-full text-sm font-semibold text-[#061828] hover:border-[#ff6b00]" href="'
                . $h($href) . '">' . $h($label) . '</a>';
        }
    }
    if ($chrome === 'matrix') {
        return '<section><h2 class="text-2xl font-semibold">' . $h($heading) . '</h2><p class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm">'
            . $items . '</p></section>';
    }
    return '<section class="mt-8"><h2 class="text-2xl font-bold text-[#061828]">' . $h($heading)
        . '</h2><div class="mt-4 flex flex-wrap gap-2">' . $items . '</div></section>';
}

/**
 * @param array{h2?:string,blurb?:string,cta?:string}|null $packed
 * @param list<array{0:string,1:string}> $extraFaqs
 * @return array{0:string,1:string}[]
 */
function icomplyGmBuildFaqs(string $topic, string $townName, string $serviceSlug, bool $gas, array $extraFaqs): array
{
    $place = $townName;
    $faqs = [];
    foreach ($extraFaqs as $faq) {
        if (is_array($faq) && count($faq) >= 2 && (string)$faq[0] !== '' && (string)$faq[1] !== '') {
            $faqs[] = [(string)$faq[0], (string)$faq[1]];
        }
    }
    $faqs[] = [
        'How is ' . $topic . ' quoted in ' . $townName . '?',
        'Send the ' . $place . ' address, access notes and what you need on the file. The reply is a POA fixed quote after scope. No fee is printed on this page.',
    ];
    if ($gas || $serviceSlug === 'gas-systems' || $serviceSlug === 'heating') {
        $faqs[] = [
            'Who carries out gas work in ' . $townName . '?',
            'Gas work for ' . $townName . ' is carried out by Gas Safe registered engineers. This page does not show an iComply Gas Safe registration number. Confirm the engineer on the public register when they attend.',
        ];
    } else {
        $faqs[] = [
            'What should I send before a ' . $townName . ' visit?',
            'The postcode in ' . $place . ', whether anyone is living there, and photos of the panel, board or door if you have them. That keeps the first visit to the agreed scope.',
        ];
    }
    $faqs[] = [
        'Where is the ' . $townName . ' page linked from?',
        'Use the ' . $townName . ' area hub for every local service, or the contact form for a POA fixed quote after scope. The office is 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE.',
    ];
    return array_slice($faqs, 0, 6);
}

/**
 * @param array{
 *   chrome?:string,
 *   topic:string,
 *   townName:string,
 *   serviceSlug:string,
 *   serviceName?:string,
 *   tier1?:string,
 *   showGuide?:bool,
 *   imageSrc?:string,
 *   imageAlt?:string,
 *   extraFaqs?:list<array{0:string,1:string}>,
 *   keywordSlug?:string
 * } $ctx
 */
function icomplyGmEnrichmentHtml(array $ctx): string
{
    $chrome = (string)($ctx['chrome'] ?? 'matrix');
    $topic = (string)($ctx['topic'] ?? '');
    $townName = (string)($ctx['townName'] ?? '');
    $serviceSlug = (string)($ctx['serviceSlug'] ?? '');
    if ($topic === '' || $townName === '' || $serviceSlug === '') {
        return '';
    }
    $kind = (string)($ctx['kind'] ?? 'service');
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $tier1Early = trim((string)($ctx['tier1'] ?? ''));
    if (!icomplyGmTownAllowed($townName, $kind)) {
        if ($tier1Early === '') {
            return '';
        }
        return '<div id="local-copy"><p>' . $h($tier1Early) . '</p></div>';
    }
    $pack = icomplyGmServicePack($serviceSlug);
    $gas = (bool)$pack['gas'] || $serviceSlug === 'gas-systems';

    $html = '';
    $tier1 = $tier1Early;
    if ($tier1 !== '') {
        $html .= '<div id="local-copy"><p>' . $h($tier1) . '</p></div>';
    }

    $showGuide = (bool)($ctx['showGuide'] ?? ($tier1 === ''));
    if ($showGuide) {
        $profile = function_exists('area_profile') ? area_profile($townName) : [];
        $stock = trim((string)($profile['stock'] ?? ''));
        $districts = trim((string)($profile['districts'] ?? ''));
        $local = '';
        if ($stock !== '') {
            $local = $townName . ($districts !== '' ? ' (' . $districts . ')' : '') . ' is ' . $stock . '. ';
        }
        if ($chrome === 'matrix') {
            $html .= '<div class="space-y-3"><p>' . $h($local . $pack['summary']) . '</p><ul class="list-disc pl-5 space-y-1">';
            foreach ($pack['points'] as $point) {
                $html .= '<li>' . $h((string)$point) . '</li>';
            }
            $html .= '</ul></div>';
        } else {
            $html .= '<div class="mt-6 space-y-3 text-zinc-800"><p class="text-lg leading-relaxed">' . $h($local . $pack['summary']) . '</p><ul class="list-disc pl-5 space-y-2">';
            foreach ($pack['points'] as $point) {
                $html .= '<li>' . $h((string)$point) . '</li>';
            }
            $html .= '</ul></div>';
        }
    }

    $imageSrc = (string)($ctx['imageSrc'] ?? '');
    if ($imageSrc === '' && empty($ctx['skipImage']) && function_exists('serviceImageUrl')) {
        $imageSrc = serviceImageUrl($serviceSlug);
    }
    $imageAlt = (string)($ctx['imageAlt'] ?? ($topic . ' in ' . $townName));
    $html .= icomplyGmImageHtml($imageSrc, $imageAlt, $chrome);

    $faqs = icomplyGmBuildFaqs($topic, $townName, $serviceSlug, $gas, $ctx['extraFaqs'] ?? []);
    $html .= icomplyGmFaqHtml('Questions about ' . $topic . ' in ' . $townName, $faqs, $chrome);

    $links = [];
    if (function_exists('getAreas') && in_array($townName, getAreas(), true)) {
        $links[] = ['href' => url('/pages/areas/' . areaSlug($townName)), 'label' => $townName . ' area hub'];
    }
    $links[] = ['href' => url('/pages/services/' . $serviceSlug), 'label' => ($ctx['serviceName'] ?? $topic) . ' service hub'];
    $services = function_exists('getServices') ? getServices() : [];
    foreach ($pack['siblings'] as $sibling) {
        if (!isset($services[$sibling]) || $sibling === $serviceSlug) {
            continue;
        }
        $links[] = [
            'href' => icomplyGmServiceTownHref($sibling, $townName),
            'label' => (string)$services[$sibling] . ' in ' . $townName,
        ];
    }
    foreach ($pack['keywords'] as $keywordSlug) {
        $href = icomplyGmKeywordHref($keywordSlug, $townName);
        if ($href === '') {
            continue;
        }
        $name = function_exists('keywordDisplayName') ? keywordDisplayName($keywordSlug) : $keywordSlug;
        if (function_exists('getMajorKeywords')) {
            $meta = getMajorKeywords()[$keywordSlug] ?? null;
            if (is_array($meta) && !empty($meta['name'])) {
                $name = (string)$meta['name'];
            }
        }
        $links[] = ['href' => $href, 'label' => $name];
    }
    $keywordSlug = (string)($ctx['keywordSlug'] ?? '');
    if ($keywordSlug !== '') {
        $links[] = ['href' => url('/pages/keywords/' . $keywordSlug), 'label' => $topic . ' guide'];
    }
    $jobHref = icomplyGmJobHref((string)$pack['job']);
    if ($jobHref !== '') {
        $links[] = ['href' => $jobHref, 'label' => 'Job hub'];
    }
    $links[] = ['href' => url('/contact'), 'label' => 'POA quote'];
    $html .= icomplyGmLinksHtml('Related pages for ' . $townName, $links, $chrome);

    $cta = 'POA fixed quote after scope. Call ' . (defined('PHONE') ? PHONE : '07517806082')
        . ' or use the contact form with the ' . $townName . ' postcode.';
    if ($gas) {
        $cta .= ' Gas work is carried out by Gas Safe registered engineers.';
    }
    if ($chrome === 'matrix') {
        $html .= '<p><a class="matrix-cta matrix-cta-accent" href="' . $h(url('/contact')) . '">Request a POA quote</a></p>'
            . '<p class="text-sm">' . $h($cta) . '</p>';
    } else {
        $html .= '<p class="mt-6"><a class="inline-block px-6 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold" href="'
            . $h(url('/contact')) . '">Request a POA quote</a></p><p class="mt-3 text-sm text-zinc-700">' . $h($cta) . '</p>';
    }
    return $html;
}

/**
 * Town links for a job hub. Only paths the default export already publishes.
 *
 * @param list<string> $towns
 */
function icomplyGmJobTownStripHtml(string $topic, array $towns, string $serviceSlug, string $keywordSlug): string
{
    if (!$towns) {
        return '';
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $cards = '';
    foreach ($towns as $town) {
        $town = (string)$town;
        if ($town === '' || !function_exists('getAreas') || !in_array($town, getAreas(), true)) {
            continue;
        }
        $bits = [];
        $bits[] = '<a class="text-[#ff6b00] font-semibold" href="' . $h(url('/pages/areas/' . areaSlug($town))) . '">' . $h($town) . ' area hub</a>';
        $svc = icomplyGmServiceTownHref($serviceSlug, $town);
        if ($svc !== '') {
            $bits[] = '<a class="text-[#ff6b00] font-semibold" href="' . $h($svc) . '">' . $h($topic) . ' service page</a>';
        }
        $kw = $keywordSlug !== '' ? icomplyGmKeywordHref($keywordSlug, $town) : '';
        if ($kw !== '') {
            $bits[] = '<a class="text-[#ff6b00] font-semibold" href="' . $h($kw) . '">Local guide</a>';
        }
        $cards .= '<article class="border border-zinc-200 rounded-2xl p-4 bg-white"><h3 class="font-semibold text-[#061828]">' . $h($topic . ' in ' . $town)
            . '</h3><p class="mt-2 text-sm text-zinc-700">Book ' . $h($town) . ' from the Stockport office. POA fixed quote after scope.</p><p class="mt-3 flex flex-wrap gap-3 text-sm">' . implode('', array_map(static function (string $bit): string {
                return '<span>' . $bit . '</span>';
            }, $bits)) . '</p></article>';
    }
    if ($cards === '') {
        return '';
    }
    $gasNote = ($serviceSlug === 'gas-systems' || $serviceSlug === 'heating')
        ? ' Gas work is carried out by Gas Safe registered engineers.'
        : '';
    return '<section class="max-w-7xl mx-auto px-6 py-16" aria-label="Greater Manchester towns"><div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Greater Manchester</div>'
        . '<h2 class="text-3xl font-semibold tracking-tight mt-2 text-[#061828]">Town pages that are already live</h2>'
        . '<p class="mt-3 max-w-3xl text-zinc-700">These links go to the area hub, the service page and the keyword guide for each town. Job pages stay on the hub above.'
        . $h($gasNote) . '</p>'
        . '<div class="mt-8 grid md:grid-cols-2 gap-4">' . $cards . '</div></section>';
}
