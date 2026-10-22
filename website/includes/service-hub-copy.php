<?php
/**
 * Honest hub copy and internal links for /pages/services/{slug}.

 */
declare(strict_types=1);

$buildingHubCopy = __DIR__ . '/building-hub-copy.php';
if (is_file($buildingHubCopy)) {
    require_once $buildingHubCopy;
}

/**
 * @return array<string,mixed>|null
 */
function icomplyServiceHubCopy(string $slug): ?array
{
    $slug = areaSlug($slug);
    if (!function_exists('icomplyNetworkingItServiceHubCopy')) {
        require_once __DIR__ . '/networking-it.php';
    }
    $networkingIt = icomplyNetworkingItServiceHubCopy($slug);
    if ($networkingIt !== null) {
        return $networkingIt;
    }
    if ($slug === 'gas-systems' && function_exists('icomplyGasServicePageCopy')) {
        return icomplyGasServicePageCopy();
    }
    if (function_exists('waterAsbestosServiceCopy')) {
        $water = waterAsbestosServiceCopy($slug);
        if ($water) {
            return $water;
        }
    }
    if ($slug === 'fire-risk-assessments') {
        $price = function_exists('fraGuidePrice') ? fraGuidePrice() : '£350';
        return [
            'hero_accent' => 'From ' . $price . ' for a typical 6-bed HMO.',
            'pillars' => [
                ['title' => 'This building', 'text' => 'A suitable and sufficient fire risk assessment describes the premises and the people in them, then a prioritised action list.'],
                ['title' => 'From ' . $price, 'text' => (function_exists('fraFromLine') ? fraFromLine() : 'From £350 for a typical 6-bed HMO in the North West; other properties, nationwide and multi-site jobs are POA.')],
                ['title' => 'Not a licence', 'text' => 'An FRA is not an HMO licence, not legal advice, and not a guarantee of an enforcement outcome. Any follow-on fire work gets its own written quote.'],
            ],
            'intro' => [
                'iComply writes fire risk assessments for shared houses, blocks, offices and other workplaces across England, Wales and mainland Scotland. The yard is Offerton, Stockport, SK2. Attendance outside the North West is scheduled and confirmed on the quote.',
                (function_exists('fraFromLine') ? fraFromLine() : 'From £350 for a typical 6-bed HMO in the North West; other properties, nationwide and multi-site jobs are POA.') . ' Every assessment is carried out fresh on site.',
                'Northern Ireland, the Scottish Highlands and Islands, the Isle of Man and the Channel Islands are not on this list.',
            ],
            'sections' => [
                [
                    'h2' => 'Assessment first',
                    'p' => [
                        'We walk the building, record hazards and existing precautions, and write the assessment. If detection, lighting or doors are short, that is an action — and a separate quote if you want the work done.',
                        'The quality landing for the same job is the fire risk assessment page. Alarm servicing and emergency lighting stay on their own service pages.',
                    ],
                ],
            ],
            'cta_line' => 'Address, use, storeys and whether anyone sleeps there. ' . $price . ' is the standard guide only.',
            'quote_placeholder' => 'Address, use, storeys, sleeping risk, last FRA date…',
        ];
    }
    if ($slug === 'epc') {
        return [
            'hero_accent' => 'Domestic or non-domestic. Free written quotes.',
            'pillars' => [
                ['title' => 'The right model', 'text' => 'Houses and flats use the domestic methodology. Shops and offices often need non-domestic. We will not force the cheaper model onto the wrong building.'],
                ['title' => 'Marketing, not a safety cert', 'text' => 'An EPC lets you advertise a let or a commercial unit. It does not prove the wiring or a gas appliance is safe this year.'],
                ['title' => 'Quoted on floor area', 'text' => 'Tell us the floor area and we will send a written quote. Ratings depend on the building itself.'],
            ],
            'intro' => [
                'iComply arranges Energy Performance Certificates for private lets and commercial units across the North West, from Offerton, Stockport SK2.',
                'Certificates usually last ten years unless the building changes. Minimum rental bands can move — confirm the current England and Wales threshold rather than treating a headline as legal advice.',
                'Tell us whether it is a home or a commercial unit, and roughly how big, and we will send a written quote.',
            ],
            'cta_line' => 'Home or commercial, and an approximate floor area.',
            'quote_placeholder' => 'Dwelling or commercial, floor area, last EPC date…',
        ];
    }
    if ($slug === 'pat-testing') {
        return [
            'hero_accent' => 'Appliances only. Not an EICR.',
            'pillars' => [
                ['title' => 'The plug-in list', 'text' => 'We inspect portable appliances and, where justified, test them. Failed items are listed. The fixed wiring stays on the EICR.'],
                ['title' => 'Risk, not a fake calendar', 'text' => 'There is no universal legal “every 12 months” for every appliance in England. Frequency follows risk unless an insurer or client sets one.'],
                ['title' => 'Quoted on a count', 'text' => 'A rough item count is enough for a written quote. Every item is tested on site.'],
            ],
            'intro' => [
                'PAT testing from iComply covers landlord-owned appliances in furnished lets and portable equipment in workplaces across Greater Manchester and the North West.',
                'It is not an EICR and it does not make an unsatisfactory electrical report satisfactory. Combined visits are common on voids when you ask for both.',
                'Send an approximate count for a written quote. We test the landlord or workplace items you need covered, and tenant-owned items too if you ask.',
            ],
            'cta_line' => 'Site type and a rough appliance count.',
            'quote_placeholder' => 'House, office or workshop, approximate item count, access hours…',
        ];
    }
    if ($slug === 'smoke-co-alarms') {
        return [
            'hero_accent' => 'Sited, tested, recorded.',
            'pillars' => [
                ['title' => 'Domestic alarms', 'text' => 'Many single lets need smoke cover on each storey and a CO alarm where a fixed combustion appliance sits. We site, fit and note the day-of-let test.'],
                ['title' => 'When a panel is the right answer', 'text' => 'Shared houses and licence conditions often need a designed fire alarm system. We will say so instead of selling a handful of domestics as if they were BS 5839.'],
                ['title' => 'Free written quote', 'text' => 'Tell us the number of storeys and the detectors already fitted, and we will send a written quote.'],
            ],
            'intro' => [
                'iComply supplies and sites smoke and carbon monoxide alarms for rented homes in the North West, and records what was tested when the tenancy file needs a note.',
                'England’s private-rented alarm rules are not the same document as a BS 5839 design for a shared house. If the layout needs a fire alarm system, that quote sits on the fire alarms page.',
                'Every job gets a written quote, based on your rooms and the alarms already fitted.',
            ],
            'cta_line' => 'Storeys, rooms and any gas appliances.',
            'quote_placeholder' => 'Storeys, existing alarms, gas appliances, let or shared house…',
        ];
    }
    if ($slug === 'emergency-lighting') {
        return [
            'hero_accent' => 'Tested, logged, upgraded when the route is dark.',
            'pillars' => [
                ['title' => 'The escape route', 'text' => 'Monthly-style function tests, an annual full-duration test, and a logbook the file can hold. BS 5266 is the practice we follow.'],
                ['title' => 'Upgrades when needed', 'text' => 'LED conversions and extra points when the existing fittings no longer cover the route. Every failed fitting is recorded and fixed or quoted.'],
                ['title' => 'Free written quote', 'text' => 'Height, wiring and access equipment all affect the job, so we quote each building individually.'],
            ],
            'intro' => [
                'iComply tests and upgrades emergency lighting for shared houses, blocks and workplaces across the North West, from the Stockport SK2 yard.',
                'A two-bed single let with no system is a different conversation from a protected stair. ',
                'We survey, test, log and install, with same-day and next-day slots often available and a written quote before any work starts.',
            ],
            'cta_line' => 'Existing fittings or a new system, plus the building type.',
            'quote_placeholder' => 'Building type, existing lighting, last test date, access…',
        ];
    }
    if ($slug === 'electrical') {
        return [
            'hero_accent' => 'EICR from £160, remedials and installs.',
            'pillars' => [
                ['title' => 'Condition reports', 'text' => 'Landlord and commercial EICRs to BS 7671 practice, with C1–C3 coded in the report. Remedials are quoted as found.'],
                ['title' => 'Installs', 'text' => 'Consumer units, rewires, first and second fix, and EV charge points under BS 7671 Section 722 when the supply can take them.'],
                ['title' => 'Written quotes', 'text' => 'EICRs start from £160 (HMOs £561). Larger buildings get a written quote based on circuit count and access.'],
            ],
            'intro' => [
                'iComply carries out electrical installation and inspection across Greater Manchester and the North West from Offerton, Stockport SK2.',
                'A satisfactory EICR is the document a tenancy file usually wants. PAT testing and an EPC are different jobs. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.',
                'Remedials and installs get a written quote once we know the board, the property type and the access. Same-day and next-day EICR slots are often available.',
            ],
            'cta_line' => 'Property type, consumer units and the last EICR date if you have it.',
            'quote_placeholder' => 'House, flat or commercial, postcode, boards, last EICR…',
        ];
    }
    if ($slug === 'heating') {
        return [
            'hero_accent' => 'Radiators, controls and gas records.',
            'pillars' => [
                ['title' => 'Wet heating', 'text' => 'Radiator changes, controls and system flushes, quoted after we see the layout.'],
                ['title' => 'Gas appliances', 'text' => 'Boiler installation, service and landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.'],
                ['title' => 'Free written quote', 'text' => 'Every heating and gas job gets a written quote. Gas work is carried out by Gas Safe registered engineers.'],
            ],
            'intro' => [
                'Heating installation from iComply covers radiators, controls and domestic or light-commercial systems across the North West.',
                'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.',
                'Tell us the postcode and whether you need radiators and controls, or a gas record. We will send a written quote for each.',
            ],
            'cta_line' => 'Postcode, radiators or controls, and any gas appliances on site.',
            'quote_placeholder' => 'Postcode, radiators or controls, any gas appliances on site…',
        ];
    }
    if (function_exists('icomplyBuildingHubCopy')) {
        $building = icomplyBuildingHubCopy($slug);
        if ($building) {
            return $building;
        }
    }
    return null;
}

/**
 * Curated internal links. Every target is a real hub, not a town doorway.
 *
 * @return list<array{0:string,1:string}>
 */
function icomplyServiceHubLinks(string $slug): array
{
    $slug = areaSlug($slug);
    if (!function_exists('icomplyNetworkingItServiceHubLinks')) {
        require_once __DIR__ . '/networking-it.php';
    }
    $networkingItLinks = icomplyNetworkingItServiceHubLinks($slug);
    if ($networkingItLinks) {
        return $networkingItLinks;
    }
    if (function_exists('icomplyBuildingHubLinks')) {
        $buildingLinks = icomplyBuildingHubLinks($slug);
        if ($buildingLinks) {
            return $buildingLinks;
        }
    }
    $curated = [
        'electrical' => [
            ['/pages/electrical-safety-landlords', 'Landlord electrical safety'],
            ['/pages/services/ev-chargers', 'EV chargers'],
            ['/pages/services/pat-testing', 'PAT testing'],
            ['/pages/services/smoke-co-alarms', 'Smoke and CO alarms'],
        ],
        'gas-systems' => [
            ['/pages/gas-safety-certificate', 'Gas safety certificate'],
            ['/pages/services/electrical', 'Electrical'],
            ['/pages/services/smoke-co-alarms', 'Smoke and CO alarms'],
            ['/pages/landlord-certificates', 'Landlord certificates'],
        ],
        'fire-alarms' => [
            ['/pages/commercial-fire-safety', 'Commercial fire safety'],
            ['/pages/fire-risk-assessment', 'Fire risk assessment'],
            ['/pages/services/emergency-lighting', 'Emergency lighting'],
            ['/pages/services/fire-doors', 'Fire doors'],
        ],
        'emergency-lighting' => [
            ['/pages/emergency-lighting-compliance', 'Emergency lighting compliance'],
            ['/pages/services/fire-alarms', 'Fire alarms'],
            ['/pages/services/fire-risk-assessments', 'Fire risk assessments'],
        ],
        'fire-risk-assessments' => [
            ['/pages/fire-risk-assessment', 'FRA landing'],
            ['/pages/services/fire-alarms', 'Fire alarms'],
            ['/pages/services/emergency-lighting', 'Emergency lighting'],
            ['/pages/services/fire-doors', 'Fire doors'],
        ],
        'asbestos-survey' => [
            ['/pages/asbestos-landlords', 'Asbestos for landlords'],
            ['/pages/services/legionella-risk-assessment', 'Legionella risk assessment'],
            ['/pages/services/building-surveys', 'Building surveys'],
        ],
        'legionella-risk-assessment' => [
            ['/pages/legionella-landlords', 'Legionella for landlords'],
            ['/pages/services/water-wras', 'Water fittings'],
            ['/pages/services/asbestos-survey', 'Asbestos survey'],
        ],
        'nurse-call' => [
            ['/pages/nurse-call-systems', 'Nurse call hub'],
            ['/pages/nurse-call-manchester', 'Nurse call in Manchester'],
            ['/pages/nurse-call/stockport', 'Nurse call in Stockport'],
            ['/pages/care-homes', 'Care homes'],
        ],
        'fire-doors' => [
            ['/pages/fire-door-compliance', 'Fire door compliance'],
            ['/pages/services/fire-risk-assessments', 'Fire risk assessments'],
            ['/pages/services/fire-stopping', 'Fire stopping'],
        ],
        'epc' => [
            ['/pages/energy-performance-certificates', 'EPC landing'],
            ['/pages/services/electrical', 'Electrical'],
            ['/pages/services/insulation', 'Insulation'],
        ],
        'pat-testing' => [
            ['/pages/portable-appliance-testing', 'PAT testing landing'],
            ['/pages/services/electrical', 'Electrical'],
            ['/pages/electrical-safety-landlords', 'Landlord electrical safety'],
        ],
        'smoke-co-alarms' => [
            ['/pages/smoke-carbon-monoxide-alarms', 'Smoke and CO landing'],
            ['/pages/services/fire-alarms', 'Fire alarms'],
            ['/pages/services/gas-systems', 'Gas records'],
        ],
        'ev-chargers' => [
            ['/pages/services/ev-chargers', 'EV charger brands'],
            ['/pages/services/electrical', 'Electrical'],
        ],
        'aov-air-handling' => [
            ['/pages/services/fire-alarms', 'Fire alarms'],
            ['/pages/commercial-fire-safety', 'Commercial fire safety'],
        ],
        'water-wras' => [
            ['/pages/services/water-wras', 'Water fittings jobs'],
            ['/pages/services/legionella-risk-assessment', 'Legionella'],
            ['/pages/services/plumbing', 'Plumbing'],
        ],
        'heating' => [
            ['/pages/gas-safety-certificate', 'Gas safety certificate'],
            ['/pages/services/plumbing', 'Plumbing'],
            ['/pages/services/electrical', 'Electrical'],
        ],
    ];
    if (isset($curated[$slug])) {
        return $curated[$slug];
    }
    $links = [['/pages/services', 'All services']];
    if (!function_exists('getServiceCategories') || !function_exists('getServices')) {
        return $links;
    }
    $services = getServices();
    foreach (getServiceCategories() as $cat) {
        $list = array_values(array_filter(
            $cat['services'] ?? [],
            static fn($s) => isset($services[$s])
        ));
        $idx = array_search($slug, $list, true);
        if ($idx === false) {
            continue;
        }
        foreach ([-1, 1] as $delta) {
            $other = $list[$idx + $delta] ?? null;
            if ($other !== null) {
                $links[] = ['/pages/services/' . $other, (string)$services[$other]];
            }
        }
        break;
    }
    return $links;
}
