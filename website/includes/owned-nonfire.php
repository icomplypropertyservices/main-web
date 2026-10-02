<?php
/**
 * Owned non-fire local pages for Burnley and Manchester.
 *
 * Fire alarms, emergency lighting, FRAs and the rest of the fire-safety
 * category stay on nationwide service hubs. This file does not create
 * fire×town landings, and it does not own Bolton.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Towns with a written non-fire local set.
 *
 * @return array<string,array{name:string,slug:string,districts:string,region:string,stock:string,travel:string,neighbours:string,hub:string,lede:string}>
 */
function ownedNonFireTowns(): array
{
    return [
        'Burnley' => [
            'name' => 'Burnley',
            'slug' => 'burnley',
            'districts' => 'BB10–BB12',
            'region' => 'East Lancashire',
            'stock' => 'stone terraces, mill conversions and industrial estates',
            'travel' => 'typically 55–75 minutes from our Offerton SK2 yard',
            'neighbours' => 'Padiham, Nelson, Colne and, when the diary allows, Blackburn',
            'hub' => '/pages/burnley-property-compliance',
            'lede' => 'East Lancashire terraces, mill conversions and factory units in BB10–BB12. One Burnley hub with real geography, attended from Stockport SK2.',
        ],
        'Manchester' => [
            'name' => 'Manchester',
            'slug' => 'manchester',
            'districts' => 'M1–M40',
            'region' => 'Greater Manchester',
            'stock' => 'city-centre conversions, Victorian terraces, offices and mixed-use blocks',
            'travel' => 'typically under 40 minutes from our Offerton SK2 yard',
            'neighbours' => 'Salford, Trafford and the inner M postcodes',
            'hub' => '/pages/manchester-property-compliance',
            'lede' => 'City lets, basement rooms and mill or terrace conversions across M1–M40. Quoted after access, parking and the kit are known.',
        ],
    ];
}

/** @return array<string,mixed>|null */
function ownedNonFireTown(string $area): ?array
{
    $towns = ownedNonFireTowns();
    if (isset($towns[$area])) {
        return $towns[$area];
    }
    $slug = function_exists('areaSlug') ? areaSlug($area) : strtolower($area);
    foreach ($towns as $town) {
        if ($town['slug'] === $slug) {
            return $town;
        }
    }
    return null;
}

/** Fire-safety catalogue. These stay nationwide — no owned town landing. */
function ownedNationwideFireServiceSlugs(): array
{
    $cats = function_exists('getServiceCategories') ? getServiceCategories() : [];
    $slugs = $cats['fire-safety']['services'] ?? [
        'fire-alarms',
        'emergency-lighting',
        'fire-risk-assessments',
        'aov-air-handling',
        'fire-extinguishers',
        'fire-doors',
        'fire-stopping',
        'fire-suppression',
        'sprinkler-systems',
        'dry-risers',
        'fire-signage',
        'evacuation-alerts',
        'kitchen-fire-suppression',
        'fire-compartmentation',
        'smoke-co-alarms',
    ];
    $out = [];
    foreach ($slugs as $slug) {
        $out[] = function_exists('areaSlug') ? areaSlug((string)$slug) : (string)$slug;
    }
    return $out;
}

function ownedIsNationwideFireService(string $serviceSlug): bool
{
    $slug = function_exists('areaSlug') ? areaSlug($serviceSlug) : $serviceSlug;
    return in_array($slug, ownedNationwideFireServiceSlugs(), true);
}

/**
 * Highest-intent non-fire services with a written town page.
 *
 * @return list<string>
 */
function ownedTopNonFireServiceSlugs(): array
{
    return [
        'electrical',
        'gas-systems',
        'pat-testing',
        'epc',
        'landlord-compliance',
        'legionella-risk-assessment',
        'asbestos-survey',
        'cctv',
        'access-control',
        'nurse-call',
    ];
}

/**
 * @return list<array{href:string,label:string}>
 */
function ownedNationwideFireLinks(): array
{
    return [
        ['href' => '/pages/services/fire-alarms', 'label' => 'Fire alarms'],
        ['href' => '/pages/services/emergency-lighting', 'label' => 'Emergency lighting'],
        ['href' => '/pages/services/fire-risk-assessments', 'label' => 'Fire risk assessments'],
        ['href' => '/pages/fire-risk-assessment', 'label' => 'FRA hub'],
        ['href' => '/pages/commercial-fire-safety', 'label' => 'Commercial fire safety'],
    ];
}

/**
 * @param array<string,string> $town
 */
function ownedFill(string $text, array $town): string
{
    return strtr($text, [
        '{area}' => $town['name'],
        '{districts}' => $town['districts'],
        '{region}' => $town['region'],
        '{stock}' => $town['stock'],
        '{travel}' => $town['travel'],
        '{neighbours}' => $town['neighbours'],
    ]);
}

/**
 * Unique copy for one owned service × town pair.
 *
 * @return array{service:string,name:string,town:array<string,string>,kicker:string,paragraphs:list<string>,work:list<string>,faqs:list<array{q:string,a:string}>}|null
 */
function ownedNonFirePair(string $serviceSlug, string $area): ?array
{
    $town = ownedNonFireTown($area);
    if ($town === null) {
        return null;
    }
    $slug = function_exists('areaSlug') ? areaSlug($serviceSlug) : $serviceSlug;
    if (ownedIsNationwideFireService($slug) || !in_array($slug, ownedTopNonFireServiceSlugs(), true)) {
        return null;
    }
    $services = function_exists('getServices') ? getServices() : [];
    if (!isset($services[$slug])) {
        return null;
    }

    $copy = [
        'electrical' => [
            'kicker' => 'EICR and remedials',
            'paragraphs' => [
                'Electrical work in {area} ({districts}) is quoted after we see the consumer unit, the circuits and the access. {stock} do not share one price.',
                'You get a coded EICR for the fixed installation. C1 and C2 remedials are a separate instruction. PAT and an EPC stay separate services when the file needs them on the same day.',
                'Travel from Offerton, Stockport SK2 is {travel}. Send the {area} postcode, whether anyone is in occupation, and the last report date if you have it.',
            ],
            'work' => [
                'Landlord and commercial EICRs in {area}',
                'Remedials and consumer-unit upgrades when you instruct them',
                'Optional PAT on the same access window for furnished lets',
            ],
            'faqs' => [
                ['q' => 'Do you publish an EICR price for {area}?', 'a' => 'No. Circuit count, access and remedials change the visit. Ask for a written quote.'],
                ['q' => 'Is this the fire-alarm page?', 'a' => 'No. Fire alarms, emergency lighting and fire risk assessments are booked on the nationwide fire pages.'],
            ],
        ],
        'gas-systems' => [
            'kicker' => 'Landlord gas records',
            'paragraphs' => [
                'Landlord gas safety records in {area} list the appliances we actually check. An unsafe appliance stays unsafe on the record.',
                'Engineers are Gas Safe registered for the work we accept. Boiler service can share the visit when you ask for it. There is no published per-appliance web price.',
                'Typical {area} stock in {districts} is {stock}. Neighbouring calls often sit around {neighbours}. Travel is {travel}.',
            ],
            'work' => [
                'Landlord gas safety records for houses, flats and shared lets in {area}',
                'Optional boiler service on the same attendance',
                'Clear isolation notes when an appliance fails',
            ],
            'faqs' => [
                ['q' => 'Is a CP12 the same as a landlord gas record?', 'a' => 'People still say CP12. What you receive is a current landlord gas safety record for the appliances we checked in {area}.'],
                ['q' => 'Do you price gas work from a national grid?', 'a' => 'No. Appliance count and access decide the quote.'],
            ],
        ],
        'pat-testing' => [
            'kicker' => 'Portable appliances',
            'paragraphs' => [
                'PAT in {area} covers landlord-owned or workplace appliances you put on the list. A tenant’s own hairdryer is not the default item.',
                'We fail the dangerous ones and leave the fixed wiring to the EICR. Furnished lets and small offices around {districts} are the usual bookings.',
                'Quotes follow the inventory and the floor count. Travel is {travel}.',
            ],
            'work' => [
                'In-service inspection of the appliances you schedule in {area}',
                'Pass and fail labels with a written list for the file',
                'Same-day notes when a lead or appliance must come out of use',
            ],
            'faqs' => [
                ['q' => 'Does PAT replace an EICR in {area}?', 'a' => 'No. PAT is the portable equipment. The fixed installation is the EICR.'],
                ['q' => 'Do you publish a per-item price?', 'a' => 'No. Send the approximate count and the postcode.'],
            ],
        ],
        'epc' => [
            'kicker' => 'Energy performance certificates',
            'paragraphs' => [
                'Domestic EPCs for marketing a let in {area}. A non-domestic certificate is a different survey — say which building you have.',
                'We do not promise a rating before the visit, and we do not publish a catalogue fee. Floor area, heating and access in {districts} decide the quote.',
                'Stock we see here: {stock}. Travel is {travel}.',
            ],
            'work' => [
                'Domestic EPCs when you are marketing a {area} let',
                'Non-domestic EPCs scoped separately for shops and offices',
                'Honest notes when the assessor cannot see a loft or a boiler',
            ],
            'faqs' => [
                ['q' => 'Can you guarantee a C rating?', 'a' => 'No. The certificate records the building we assess in {area}.'],
                ['q' => 'Is an EPC a gas safety record?', 'a' => 'No. Gas records and EICRs are separate visits unless you ask us to coordinate the diary.'],
            ],
        ],
        'landlord-compliance' => [
            'kicker' => 'Landlord file',
            'paragraphs' => [
                'A {area} let file usually needs a current EICR, a gas safety record where gas is present, smoke and carbon-monoxide alarms where the rules require them, and an EPC if you are marketing.',
                'We do not sell a fixed “pack” price. Shared houses and blocks add a fire risk assessment, which is booked on the nationwide fire pages rather than cloned as a {area} fire doorway.',
                'Agents around {neighbours} can cluster voids. Travel from SK2 is {travel}.',
            ],
            'work' => [
                'Certificate files for single lets in {area}',
                'Diary clustering when several {districts} addresses fall due together',
                'Written scope before anyone is sent',
            ],
            'faqs' => [
                ['q' => 'Is this an HMO package page?', 'a' => 'No. Existing Let Ready and workplace packages stay on their own pages.'],
                ['q' => 'Will you invent a landlord-pack price for {area}?', 'a' => 'No. Houses differ. Send what is already on the file.'],
            ],
        ],
        'legionella-risk-assessment' => [
            'kicker' => 'Water hygiene · POA',
            'paragraphs' => [
                'Legionella risk assessments in {area} are price on application. A combi-fed house is a different job from tanks, little-used showers or a shared system.',
                'We write what the water system actually is. Sampling is added only when the assessment supports it. We do not claim a named laboratory accreditation on this page.',
                'Coverage is {districts} and {region}. Travel is {travel}.',
            ],
            'work' => [
                'Written Legionella risk assessment for the system on site',
                'Temperature, storage and little-used outlet notes',
                'Samples only when justified — still POA',
            ],
            'faqs' => [
                ['q' => 'Do you publish a per-let Legionella fee?', 'a' => 'No. Quotes for {area} are POA after you describe stored water and access.'],
                ['q' => 'Is sampling automatic?', 'a' => 'No. The assessment comes first.'],
            ],
        ],
        'asbestos-survey' => [
            'kicker' => 'Asbestos survey · POA',
            'paragraphs' => [
                'Asbestos surveys in {area} are price on application under the Control of Asbestos Regulations 2012. Common parts and refurbishment opening-up are a different duty from a purely domestic terrace.',
                'We scope management versus refurbishment honestly. Licensed removal is by others. We do not claim a UKAS, BOHS or HSE licence on this page.',
                'Older {stock} in {districts} is the usual reason to book. Travel is {travel}.',
            ],
            'work' => [
                'Management surveys for normal occupation',
                'Refurbishment surveys before strip-out',
                'Inaccessible-area notes when voids were not opened',
            ],
            'faqs' => [
                ['q' => 'Do you remove asbestos in {area}?', 'a' => 'Licensed removal is not this service. If removal is required, appoint a suitable licensed contractor.'],
                ['q' => 'Is there a fixed survey price?', 'a' => 'No. Age, floor area and whether walls are coming open decide the POA quote.'],
            ],
        ],
        'cctv' => [
            'kicker' => 'CCTV',
            'paragraphs' => [
                'IP CCTV in {area} is designed around the yard, the entrance and the rooms you actually need to see. Camera angles stay mindful of neighbours and GDPR.',
                'We do not publish a per-camera price. Cabling, recording retention and access in {stock} change the quote.',
                'Postcodes {districts}. Travel is {travel}.',
            ],
            'work' => [
                'Survey, install and NVR setup for {area} sites',
                'Remote viewing for the people who should have it',
                'Takeovers of existing systems when the documentation allows',
            ],
            'faqs' => [
                ['q' => 'Will you point cameras at the public footpath by default?', 'a' => 'No. We agree coverage with you so the system is useful and proportionate.'],
                ['q' => 'Is CCTV a fire-alarm service?', 'a' => 'No. Life-safety fire systems are booked on the nationwide fire pages.'],
            ],
        ],
        'access-control' => [
            'kicker' => 'Door access',
            'paragraphs' => [
                'Access control in {area} covers fob, PIN or mobile credentials for offices and apartment entrances, with an audit trail the managing agent can read.',
                'Fire-door release stays safe. We will not lock an escape route to sell a reader. Quotes wait until we know the doors and the cabling.',
                'Typical buildings: {stock}. Travel is {travel}.',
            ],
            'work' => [
                'Readers, credentials and time zones for {area} entrances',
                'Fire-release left in line with the fire strategy',
                'User changes when tenants or cleaners change',
            ],
            'faqs' => [
                ['q' => 'Can one reader price cover a whole block?', 'a' => 'No. Door count, risers and fire interfaces in {area} decide the quote.'],
                ['q' => 'Do you copy credentials from an unknown panel?', 'a' => 'Only when the existing system and permissions are clear. Otherwise we say so before the visit.'],
            ],
        ],
        'nurse-call' => [
            'kicker' => 'Nurse call',
            'paragraphs' => [
                'Nurse call around {area} is for care homes and supported living that need a call the staff can hear and a log they can show.',
                'Maintenance follows the system you have. Where HTM 08-03 is the right reference we say so. We do not invent a per-room web price.',
                'Travel across {region} is {travel}. Tell us the bed count and the panel brand.',
            ],
            'work' => [
                'Servicing and fault attendance on existing {area} systems',
                'Handset and zone repairs without a full rip-out when that is honest',
                'Handover notes for the care team',
            ],
            'faqs' => [
                ['q' => 'Is nurse call a fire alarm?', 'a' => 'No. Fire detection is a separate nationwide service. Say if the home needs both and we will scope them apart.'],
                ['q' => 'Do you publish a per-bed price for {area}?', 'a' => 'No. Panel brand, cabling and room count come first.'],
            ],
        ],
    ];

    $block = $copy[$slug];
    $fill = static function (string $text) use ($town): string {
        return ownedFill($text, $town);
    };
    $paragraphs = array_map($fill, $block['paragraphs']);
    $work = array_map($fill, $block['work']);
    $faqs = [];
    foreach ($block['faqs'] as $faq) {
        $faqs[] = ['q' => $fill($faq['q']), 'a' => $fill($faq['a'])];
    }

    return [
        'service' => $slug,
        'name' => (string)$services[$slug],
        'town' => $town,
        'kicker' => $block['kicker'],
        'paragraphs' => $paragraphs,
        'work' => $work,
        'faqs' => $faqs,
    ];
}

function ownedH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * On Burnley and Manchester pages, fire services link to the nationwide hub.
 * Other services keep their town URL.
 */
function ownedLocalServiceHref(string $serviceSlug, string $area): string
{
    $slug = function_exists('areaSlug') ? areaSlug($serviceSlug) : $serviceSlug;
    $town = ownedNonFireTown($area);
    if ($town !== null && ownedIsNationwideFireService($slug)) {
        return url('/pages/services/' . $slug);
    }
    $areaSlug = $town['slug'] ?? (function_exists('areaSlug') ? areaSlug($area) : $area);
    return url('/pages/' . $slug . '/' . $areaSlug);
}

function ownedNonFireLandingSectionHtml(string $serviceSlug, string $area): string
{
    $pair = ownedNonFirePair($serviceSlug, $area);
    if ($pair === null) {
        return '';
    }
    $town = $pair['town'];
    $serviceUrl = url('/pages/services/' . $pair['service']);
    $areaUrl = url('/pages/areas/' . $town['slug']);
    $hubUrl = url($town['hub']);
    $html = '<section class="bg-white border-b" data-owned-nonfire="' . ownedH($town['slug']) . '" data-owned-service="' . ownedH($pair['service']) . '">'
        . '<div class="max-w-7xl mx-auto px-6 py-14">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Owned local page · non-fire</div>'
        . '<h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">'
        . ownedH($pair['name'] . ' in ' . $town['name']) . '</h2>'
        . '<p class="mt-3 text-sm text-zinc-500">' . ownedH($pair['kicker']) . ' · ' . ownedH($town['districts']) . '</p>';
    foreach ($pair['paragraphs'] as $para) {
        $html .= '<p class="mt-4 text-lg text-zinc-700 leading-relaxed max-w-3xl">' . ownedH($para) . '</p>';
    }
    $html .= '<ul class="mt-6 space-y-2 text-zinc-800 max-w-3xl">';
    foreach ($pair['work'] as $item) {
        $html .= '<li class="flex gap-2"><span class="text-[#ff6b00] font-bold">●</span><span>' . ownedH($item) . '</span></li>';
    }
    $html .= '</ul>';
    $html .= '<div class="mt-8 space-y-3 max-w-3xl">';
    foreach ($pair['faqs'] as $faq) {
        $html .= '<details class="bg-zinc-50 border border-zinc-200 rounded-2xl p-5">'
            . '<summary class="font-semibold cursor-pointer">' . ownedH($faq['q']) . '</summary>'
            . '<p class="mt-3 text-sm text-zinc-700 leading-relaxed">' . ownedH($faq['a']) . '</p></details>';
    }
    $html .= '</div>';
    $html .= '<p class="mt-8 text-sm text-zinc-600">Service hub: <a class="text-[#ff6b00] font-semibold" href="'
        . ownedH($serviceUrl) . '">' . ownedH($pair['name']) . '</a>'
        . ' · Town hub: <a class="text-[#ff6b00] font-semibold" href="' . ownedH($areaUrl) . '">' . ownedH($town['name']) . '</a>'
        . ' · Quality hub: <a class="text-[#ff6b00] font-semibold" href="' . ownedH($hubUrl) . '">' . ownedH($town['name'] . ' property compliance') . '</a></p>';
    $html .= '<p class="mt-3 text-sm text-zinc-600">Fire alarms, emergency lighting and fire risk assessments are booked on the nationwide fire pages.';
    foreach (ownedNationwideFireLinks() as $link) {
        $html .= ' <a class="text-[#ff6b00] font-semibold" href="' . ownedH(url($link['href'])) . '">' . ownedH($link['label']) . '</a>';
    }
    $html .= '</p></div></section>';
    return $html;
}

function ownedNonFireAreaSectionHtml(string $area): string
{
    $town = ownedNonFireTown($area);
    if ($town === null) {
        return '';
    }
    $services = function_exists('getServices') ? getServices() : [];
    $html = '<section class="bg-[#0B1F3A] text-white" data-owned-area="' . ownedH($town['slug']) . '">'
        . '<div class="max-w-7xl mx-auto px-6 py-16">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Non-fire · ' . ownedH($town['region']) . '</div>'
        . '<h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2">Top services in ' . ownedH($town['name']) . '</h2>'
        . '<p class="mt-4 text-white/80 max-w-3xl">' . ownedH($town['lede']) . ' Travel is ' . ownedH($town['travel']) . '.</p>'
        . '<div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">';
    foreach (ownedTopNonFireServiceSlugs() as $slug) {
        if (!isset($services[$slug])) {
            continue;
        }
        $href = url('/pages/' . $slug . '/' . $town['slug']);
        $html .= '<a class="block rounded-2xl border border-white/15 p-4 hover:border-[#ff6b00]" href="' . ownedH($href) . '">'
            . '<div class="font-semibold">' . ownedH((string)$services[$slug]) . '</div>'
            . '<div class="text-sm text-white/70 mt-1">in ' . ownedH($town['name']) . ' →</div></a>';
    }
    $html .= '</div>';
    $html .= '<div class="mt-8 rounded-2xl bg-white/5 border border-white/10 p-5 max-w-3xl">'
        . '<div class="font-semibold">Fire systems — nationwide</div>'
        . '<p class="mt-2 text-sm text-white/75">Fire alarms, emergency lighting, fire risk assessments and the rest of the life-safety range are booked on the nationwide fire pages. This ' . ownedH($town['name']) . ' section stays on electrical, gas, certificates, water, asbestos and security.</p>'
        . '<p class="mt-3 text-sm">';
    $bits = [];
    foreach (ownedNationwideFireLinks() as $link) {
        $bits[] = '<a class="text-[#ff6b00] font-semibold" href="' . ownedH(url($link['href'])) . '">' . ownedH($link['label']) . '</a>';
    }
    $html .= implode(' · ', $bits);
    $html .= '</p><p class="mt-3 text-sm"><a class="text-white font-semibold underline" href="' . ownedH(url($town['hub'])) . '">Open the ' . ownedH($town['name']) . ' quality hub</a></p>'
        . '</div></div></section>';
    return $html;
}

/** Compact paragraphs for the static-export matrix renderer. */
function ownedNonFireMatrixArticleHtml(string $serviceSlug, string $area): string
{
    $pair = ownedNonFirePair($serviceSlug, $area);
    if ($pair === null) {
        return '';
    }
    $town = $pair['town'];
    $html = '<div data-owned-nonfire="' . ownedH($town['slug']) . '" data-owned-service="' . ownedH($pair['service']) . '">';
    $html .= '<h2 class="text-2xl font-semibold">' . ownedH($pair['kicker'] . ' in ' . $town['name']) . '</h2>';
    foreach ($pair['paragraphs'] as $para) {
        $html .= '<p class="text-zinc-700 leading-relaxed">' . ownedH($para) . '</p>';
    }
    $html .= '<ul class="space-y-2 text-zinc-700">';
    foreach ($pair['work'] as $item) {
        $html .= '<li><span class="text-[#ff6b00]">●</span> ' . ownedH($item) . '</li>';
    }
    $html .= '</ul>';
    $html .= '<p class="text-sm">Fire systems stay nationwide: ';
    $bits = [];
    foreach (ownedNationwideFireLinks() as $link) {
        $bits[] = '<a class="text-[#ff6b00] font-semibold" href="' . ownedH(url($link['href'])) . '">' . ownedH($link['label']) . '</a>';
    }
    $html .= implode(', ', $bits) . '.</p></div>';
    return $html;
}
