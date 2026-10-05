<?php
/**
 * Quality-bar slots for Page Enrichment.
 *
 * Live thin ×town HTML (service, keyword, job) is rendered by
 * netlify/edge-functions/town-matrix.js via netlify/lib/thin-quality-bar.js.
 * Open Graph for those URLs is thinOgMeta() in that module — do not add a
 * second edge helper. This PHP file is the same floor for hub, area, home,
 * AOV and the PHP/matrix twins of thin pages.
 *
 * Hook names: docs/pe-quality-bar-slots.md
 */
declare(strict_types=1);

const ICOMPLY_NAP = '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE';
const ICOMPLY_GAS_ENGINEERS = 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.';
const ICOMPLY_GAS_NOT_REGISTERED = 'iComply is not Gas Safe registered.';

function icomplyQualityBarH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** @return array{banks:list<list<string>>} */
function icomplyQualityBarFragments(): array
{
    static $data = null;
    if ($data === null) {
        $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/quality-bar-prose.json';
        $decoded = json_decode((string)file_get_contents($file), true);
        $data = is_array($decoded) ? $decoded : ['banks' => []];
    }
    return $data;
}

function icomplyQualityBarHash(string $value): int
{
    $h = 2166136261;
    $len = strlen($value);
    for ($i = 0; $i < $len; $i++) {
        $h ^= ord($value[$i]);
        $h = ($h * 16777619) & 0xFFFFFFFF;
    }
    return $h;
}

function icomplyQualityBarFill(string $template, array $ctx): string
{
    return strtr($template, [
        '{place}' => (string)($ctx['place'] ?? ''),
        '{subject}' => (string)($ctx['subject'] ?? ''),
        '{service}' => (string)($ctx['service'] ?? ''),
        '{audience}' => (string)($ctx['audience'] ?? 'landlords'),
        '{visit}' => (string)($ctx['visit'] ?? 'a planned daytime visit'),
        '{near}' => (string)($ctx['near'] ?? 'Stockport and Manchester'),
        '{housing}' => (string)($ctx['housing'] ?? ''),
        '{industry}' => (string)($ctx['industry'] ?? ''),
        '{pop}' => (string)($ctx['pop'] ?? ''),
    ]);
}

/**
 * Q1 thin prose. $kind is service, keyword or job.
 */
function icomplyQualityBarThinProseHtml(string $kind, array $ctx): string
{
    $banks = icomplyQualityBarFragments()['banks'] ?? [];
    $seed = icomplyQualityBarHash($kind . '|' . ($ctx['subject'] ?? '') . '|' . ($ctx['service'] ?? '') . '|' . ($ctx['place'] ?? ''));
    $parts = [];
    $blurb = trim((string)($ctx['blurb'] ?? ''));
    if ($blurb !== '') {
        $parts[] = '<p data-pe-slot="q1-town-blurb">' . icomplyQualityBarH($blurb) . '</p>';
    }
    foreach ([0, 1] as $pass) {
        foreach ($banks as $i => $bank) {
            if (!is_array($bank) || $bank === []) {
                continue;
            }
            $mixed = ($seed ^ (($i + 1) * 0x9e3779b9) ^ (($pass + 1) * 0x85ebca6b)) & 0xFFFFFFFF;
            $mixed = ($mixed ^ ($mixed >> 16)) * 0x45d9f3b;
            $mixed &= 0xFFFFFFFF;
            $raw = (string)$bank[$mixed % count($bank)];
            $parts[] = '<p>' . icomplyQualityBarH(icomplyQualityBarFill($raw, $ctx)) . '</p>';
        }
    }
    $parts[] = '<p>' . icomplyQualityBarH(icomplyQualityBarFill(
        '{pop} {housing} {industry} Places named alongside {place} include {near}.',
        $ctx
    )) . '</p>';
    if (!empty($ctx['gas'])) {
        $parts[] = '<p>' . icomplyQualityBarH(ICOMPLY_GAS_ENGINEERS . ' ' . ICOMPLY_GAS_NOT_REGISTERED) . '</p>';
    }
    $slot = 'q1-thin-prose';
    if ($kind === 'job') {
        $slot = 'q1-job-town-prose';
    } elseif ($kind === 'keyword') {
        $slot = 'q1-keyword-town-prose';
    } elseif ($kind === 'service') {
        $slot = 'q1-service-town-prose';
    }
    return '<div data-pe-slot="' . $slot . '" id="q1-thin-prose">' . implode('', $parts) . '</div>';
}

/**
 * Three distinct content images. $slot is the figure hook PE swaps.
 *
 * @return array{html:string,hero:string,og:string}
 */
function icomplyQualityBarImages(string $serviceSlug, string $altPrefix, string $slot = 'q2-content-images'): array
{
    $photo = [
        'access-control', 'aov-air-handling', 'cctv', 'door-entry', 'electrical',
        'emergency-lighting', 'fire-alarms', 'fire-risk-assessments', 'gas-systems',
        'intercoms', 'intruder-alarm', 'nurse-call',
    ];
    $pool = [
        'building-maintenance', 'fire-alarms', 'electrical', 'cctv',
        'access-control', 'emergency-lighting', 'gas-systems', 'door-entry',
    ];
    $slug = $serviceSlug !== '' ? $serviceSlug : 'fire-alarms';
    $primary = '/assets/images/services/' . $slug . '.jpg';
    $second = in_array($slug, $photo, true)
        ? '/assets/images/services/' . $slug . '-photo.jpg'
        : '/assets/images/services/' . $pool[abs(crc32($slug)) % count($pool)] . '.jpg';
    if ($second === $primary) {
        $second = '/assets/images/services/building-maintenance.jpg';
    }
    $third = '/assets/images/services/' . $pool[(abs(crc32($slug)) + 3) % count($pool)] . '.jpg';
    if ($third === $primary || $third === $second) {
        $third = '/assets/images/services/cctv.jpg';
    }
    if ($third === $primary || $third === $second) {
        $third = '/assets/images/services/electrical.jpg';
    }
    $src = static function (string $path): string {
        return function_exists('url') ? url($path) : $path;
    };
    $h = 'icomplyQualityBarH';
    $html = '<figure class="quality-bar-images" data-pe-slot="' . $h($slot) . '">'
        . '<img data-pe-slot="q2-image-hero" src="' . $h($src($primary)) . '" alt="' . $h($altPrefix) . '" width="1200" height="630">'
        . '<img data-pe-slot="q2-image-work" src="' . $h($src($second)) . '" alt="' . $h($altPrefix . ' — equipment') . '" width="1200" height="630">'
        . '<img data-pe-slot="q2-image-context" src="' . $h($src($third)) . '" alt="' . $h($altPrefix . ' — site context') . '" width="1200" height="630">'
        . '</figure>';
    $og = function_exists('icomply_absolute_url') ? icomply_absolute_url($primary) : $primary;
    return ['html' => $html, 'hero' => $primary, 'og' => $og];
}

/**
 * @param list<array{0:string,1:string}> $faqs
 */
function icomplyQualityBarFaqHtml(array $faqs, string $slot, string $jsonId, string $heading): string
{
    $items = '';
    $entities = [];
    foreach ($faqs as $faq) {
        $q = trim((string)($faq[0] ?? ''));
        $a = trim((string)($faq[1] ?? ''));
        if ($q === '' || $a === '') {
            continue;
        }
        $items .= '<details class="faq-item"><summary>' . icomplyQualityBarH($q) . '</summary><p>' . icomplyQualityBarH($a) . '</p></details>';
        $entities[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }
    $json = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return '<section id="faq" class="faq" data-pe-slot="' . icomplyQualityBarH($slot) . '">'
        . '<h2>' . icomplyQualityBarH($heading) . '</h2>'
        . $items
        . '</section>'
        . '<script type="application/ld+json" id="' . icomplyQualityBarH($jsonId) . '">' . $json . '</script>';
}

/** @return list<array{0:string,1:string}> */
function icomplyQualityBarAreaFaqs(string $areaName): array
{
    return [
        [
            'How do I request a quote for work in ' . $areaName . '?',
            'Tell us the postcode, the property type and the system. The quote for ' . $areaName . ' is price on application after the scope is clear. Nothing on this page is a fixed fee.',
        ],
        [
            'Where is iComply based for ' . $areaName . ' visits?',
            'The office is ' . ICOMPLY_NAP . '. Visits in ' . $areaName . ' are diary-booked from that Stockport base.',
        ],
        [
            'Who carries out landlord gas safety checks?',
            ICOMPLY_GAS_ENGINEERS . ' ' . ICOMPLY_GAS_NOT_REGISTERED,
        ],
        [
            'Which services are listed for ' . $areaName . '?',
            'This page links the services published for ' . $areaName . '. Fire protection, electrical, security and building work are quoted after a survey. Price on application.',
        ],
    ];
}

/** @return list<array{0:string,1:string}> */
function icomplyQualityBarHomeFaqs(): array
{
    return [
        [
            'What does iComply quote?',
            'AOV and smoke control, vehicle barriers, electrical, fire, CCTV, access control and landlord compliance visits. The quote is price on application after we know the site.',
        ],
        [
            'Where are you based?',
            'The office is ' . ICOMPLY_NAP . '. That is the address for correspondence and the base visits are diary-booked from.',
        ],
        [
            'Who carries out gas work?',
            ICOMPLY_GAS_ENGINEERS . ' ' . ICOMPLY_GAS_NOT_REGISTERED,
        ],
        [
            'How quickly do you reply?',
            'We aim to reply within 2 hours on business days. That is a target, not a guaranteed call-out. Phone or WhatsApp if the visit is urgent and say the postcode.',
        ],
    ];
}

/** @return list<array{0:string,1:string}> */
function icomplyQualityBarAovFaqs(): array
{
    return [
        [
            'How is AOV installation priced?',
            'Installation and engineer visits are price on application after we know the vent, the panel and the access. This page does not publish a fee.',
        ],
        [
            'Do you claim a smoke-control scheme registration?',
            'No. We do not claim BAFE, FIRAS or approved-installer status. Naming BS EN 12101 is not an accreditation.',
        ],
        [
            'Where is the team based?',
            'Automatic opening vent visits are scheduled nationwide from ' . ICOMPLY_NAP . '. A town page is a place with a published population, not a depot.',
        ],
        [
            'What should I send with an AOV enquiry?',
            'Panel brand, town, whether the vent is stuck open or shut, and whether the roof or stair is accessible. We quote after that scope.',
        ],
    ];
}

function icomplyQualityBarThinBlock(string $kind, string $subject, string $serviceName, string $serviceSlug, string $place, string $blurb, bool $gas): string
{
    $ctx = [
        'place' => $place,
        'subject' => $subject,
        'service' => $serviceName,
        'audience' => 'landlords and managing agents',
        'visit' => 'a planned daytime visit',
        'near' => 'nearby towns on the published list',
        'housing' => '',
        'industry' => '',
        'pop' => '',
        'gas' => $gas,
        'blurb' => $blurb,
    ];
    $faqs = [
        ['How is ' . $subject . ' in ' . $place . ' quoted?', 'The quote is price on application after the scope names the building and the access. This page does not publish a fee.'],
        ['Where is the team that covers ' . $place . ' based?', 'Visits are arranged from ' . ICOMPLY_NAP . '.'],
        ['What should be sent before a ' . $place . ' visit?', 'Send the postcode, the property type and anything already known about the installation.'],
    ];
    if ($gas) {
        $faqs[] = ['Who carries out gas work for this ' . $place . ' visit?', ICOMPLY_GAS_ENGINEERS . ' ' . ICOMPLY_GAS_NOT_REGISTERED];
    }
    return icomplyQualityBarImages($serviceSlug, $subject . ' in ' . $place, 'q2-thin-images')['html']
        . icomplyQualityBarThinProseHtml($kind, $ctx)
        . icomplyQualityBarFaqHtml($faqs, 'q5-thin-faq', 'q5-thin-faq-jsonld', 'Questions about ' . $place);
}

function icomplyQualityBarAovProseHtml(): string
{
    $paragraphs = [
        'An automatic opening vent is a ventilator that opens when the fire strategy says it should. On most of the buildings we are asked about, that is a roof hatch or a façade vent at the head of a stair, sometimes a corridor or lobby opening into a smoke shaft. The vent is life-safety equipment. We treat the work as fire protection and quote it across the UK from our Stockport yard. This index lists the town pages. It is not a depot map and it is not a catalogue of fixed install prices.',
        'The visit we can actually describe is a test of the equipment that is already there, or an install that has a written scope. Full travel of the vents the strategy names, the manual point at the landing, battery standby, and the fire-alarm contact are the usual checks. A green panel with a seized roof lid is a fail. We write down what moved and what did not. We do not turn a failed travel test into a pass because the panel lamp is healthy.',
        'We do not invent a smoke-control design, a free-area calculation or a scheme certificate. If there is no fire strategy, the next step is a fire engineer, then an install. Naming BS EN 12101-2 for natural smoke ventilators, BS EN 12101-10 for power supplies, BS EN 12101-3 when the kit is a powered fan, or BS 9991 where a residential strategy cites it, is not a claim that we are accredited to that standard. We do not claim BAFE, FIRAS or approved-installer status for any brand.',
        'Supply kits are described on the AOV service hub. Installation, adaptation and engineer attendance on this page are price on application. No pound figure is printed here. A quote names the vent, the panel, the access and whether the job is test-only or supply and fit. Occupied stairs, scaffold and a lid that has to come off a roof are scope, not small print added after someone is already on site.',
        'Town pages exist for published places with a usual-resident count that meets the list we publish, including Manchester and Burnley, which carry extra local notes. Smaller places are still quoted. They do not get a cloned town URL. A town page is not a store and it is not a promise that an engineer lives in that town. Travel is scheduled from Stockport. Same-week attendance is something we will talk about when the diary allows. It is not a contract on this index.',
        'Natural vents and powered smoke fans are different jobs. A roof hatch that opens on a fire signal is not the same instruction as a shaft fan with a duty and standby arrangement. The enquiry should say which one the building has. If the file only says “AOV”, we ask for a photograph of the panel and of the vent before we pretend to know the scope. Interface wiring to the fire alarm is part of that question. A panel swap that drops the contact is a common reason a vent no longer opens.',
        'Weather and access decide whether a visit is realistic. Pennine rain and exposed lids seize roof hatches. A lid that will not close is both a leak and a failed smoke vent. We will not sign the vent as healthy because someone closed it by hand once. Roof access, edge protection and whether the stair can be taken out of use for the test are stated before the date is agreed. If the building cannot offer that access, the visit moves.',
        'Paperwork stays with the instructing client. We supply the test record we agreed to write. We are not the Building Safety Regulator and we do not file a safety case for the accountable person. Higher-risk residential buildings may need that record. Asking us for the record is not the same as asking us to take the statutory role. The quote says which document we will leave and which we will not.',
        'Brands are listed on the service hub so a caretaker can match a panel name. Wordmarks there are name plates for scanning the set. They are not partnership badges and they are not a claim that every manufacturer has appointed us. If a spare is a supply-only kit, fitting is included only when the quote says so. Disposal of a failed actuator is included only when the quote says so.',
        'To ask for a quote, send the town, the postcode, the stair or corridor, the panel brand, and whether the vent is stuck open or shut. Phone if the stair vent is stuck and people are in the building. The form on this page logs the enquiry. It does not dispatch an engineer by itself. We aim to reply on business days. That is a target, not a 24-hour contract. The correspondence address is ' . ICOMPLY_NAP . '.',
        'This index sits beside the AOV service hub, not instead of it. The hub explains the equipment and the guides. These town links exist so a search for a place resolves to a page about that place. Fire-protection coverage on those pages is UK-wide because smoke control is quoted nationwide. Other trades on this website keep their own town rules. Do not read an AOV town link as permission to invent a non-fire service page for the same place.',
        'What we will not do on the back of this index: invent a price, invent an accreditation, or copy one town’s paragraph onto the next with only the name swapped. Each town page is its own URL. This index is the directory. If you are a landlord, an agent or a facilities manager, say who will meet the engineer and who will hold the record. The quote is price on application after that is clear.',
    ];
    $html = '';
    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . icomplyQualityBarH($paragraph) . '</p>';
    }
    return '<div data-pe-slot="q6-aov-prose" id="q6-aov-prose">' . $html . '</div>';
}
