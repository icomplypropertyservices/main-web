<?php
/**
 * Unique local content engine — reduces doorway/template risk.
 * Deterministic per (service, area) so pages stay stable across regenerations.
 */
if (!function_exists('service_standards')) {
    $seoFile = __DIR__ . '/seo.php';
    if (is_file($seoFile)) {
        require_once $seoFile;
    }
}

function area_seed(string $area, string $extra = ''): int {
    return abs(crc32(mb_strtolower($area) . '|' . $extra));
}

function pick_seeded(array $pool, int $seed, int $offset = 0) {
    if (!$pool) return null;
    return $pool[($seed + $offset) % count($pool)];
}

/** Approximate postcode districts + region flavour for NW towns */
function area_profile(string $area): array {
    static $map = null;
    if ($map === null) {
        $map = [
            'Manchester' => ['districts' => 'M1–M40', 'region' => 'Greater Manchester core', 'stock' => 'city-centre apartments, Victorian terraces, commercial offices and multi-let stock', 'travel' => 'typically under 40 minutes from our Stockport base', 'focus' => 'high-rise residential, retail and office compliance', 'authority' => 'Manchester City Council'],
            'Salford' => ['districts' => 'M3, M5–M7, M50', 'region' => 'Greater Manchester', 'stock' => 'MediaCity offices, new-build apartments and industrial estates', 'travel' => 'typically under 35 minutes from Stockport', 'focus' => 'mixed-use and multi-tenant buildings', 'authority' => 'Salford City Council'],
            'Stockport' => ['districts' => 'SK1–SK8', 'region' => 'Greater Manchester', 'stock' => 'suburban housing, industrial units and town-centre retail', 'travel' => 'local Stockport coverage from SK2', 'focus' => 'landlord portfolios and SME commercial sites', 'authority' => 'Stockport Metropolitan Borough Council'],
            'Bolton' => ['districts' => 'BL1–BL7', 'region' => 'Greater Manchester', 'stock' => 'terraced housing, mills converted to commercial and retail parks', 'travel' => 'typically 35–50 minutes from Stockport', 'focus' => 'landlord and light-industrial compliance', 'authority' => 'Bolton Council'],
            'Oldham' => ['districts' => 'OL1–OL9', 'region' => 'Greater Manchester', 'stock' => 'terraced streets, industrial estates and local authority stock', 'travel' => 'typically 35–50 minutes from Stockport', 'focus' => 'residential portfolios and warehouse units', 'authority' => 'Oldham Council'],
            'Rochdale' => ['districts' => 'OL11–OL16', 'region' => 'Greater Manchester', 'stock' => 'mixed housing, town retail and industrial estates', 'travel' => 'typically 40–55 minutes from Stockport', 'focus' => 'landlords, agents and SME facilities'],
            'Bury' => ['districts' => 'BL9, M25–M26', 'region' => 'Greater Manchester', 'stock' => 'suburban homes, retail and light industry', 'travel' => 'typically 40–55 minutes from Stockport', 'focus' => 'residential and high-street commercial'],
            'Wigan' => ['districts' => 'WN1–WN6', 'region' => 'Greater Manchester', 'stock' => 'housing estates, industrial corridors and town-centre units', 'travel' => 'typically 50–70 minutes from Stockport', 'focus' => 'commercial and multi-site landlords', 'authority' => 'Wigan Council'],
            'Liverpool' => ['districts' => 'L1–L25', 'region' => 'Merseyside', 'stock' => 'city apartments, Georgian terraces, docks-side commercial and retail', 'travel' => 'typically 50–70 minutes from Stockport', 'focus' => 'HMOs, offices and hospitality sites', 'authority' => 'Liverpool City Council'],
            'Preston' => ['districts' => 'PR1–PR5', 'region' => 'Lancashire', 'stock' => 'city housing, student HMOs and industrial parks', 'travel' => 'typically 50–70 minutes from Stockport', 'focus' => 'student lets, offices and warehouses'],
            'Blackpool' => ['districts' => 'FY1–FY4', 'region' => 'Lancashire coast', 'stock' => 'HMOs, guest houses, seafront retail and leisure', 'travel' => 'typically 70–90 minutes from Stockport', 'focus' => 'hospitality and multi-let residential'],
            'Chester' => ['districts' => 'CH1–CH4', 'region' => 'Cheshire', 'stock' => 'historic city retail, offices and suburban housing', 'travel' => 'typically 50–70 minutes from Stockport', 'focus' => 'retail, heritage-sensitive commercial and residential'],
            'Warrington' => ['districts' => 'WA1–WA5', 'region' => 'Cheshire / Merseyside border', 'stock' => 'logistics warehouses, business parks and new housing', 'travel' => 'typically 35–50 minutes from Stockport', 'focus' => 'warehouses, offices and estates', 'authority' => 'Warrington Borough Council'],
            'Trafford' => ['districts' => 'M16, M17, M32, M33, M41, WA14–WA15', 'region' => 'Greater Manchester', 'stock' => 'suburban housing, market-town retail and commercial parks across Altrincham, Sale, Stretford and Urmston', 'travel' => 'typically 20–40 minutes from our Stockport base', 'focus' => 'residential portfolios and town-centre commercial sites', 'authority' => 'Trafford Council'],
            'Tameside' => ['districts' => 'OL5–OL7, SK14–SK16', 'region' => 'Greater Manchester', 'stock' => 'terraced housing, town-centre retail and industrial estates in Ashton-under-Lyne, Hyde, Denton, Dukinfield and Stalybridge', 'travel' => 'typically 20–40 minutes from our Stockport base', 'focus' => 'landlord housing and SME industrial sites', 'authority' => 'Tameside Metropolitan Borough Council'],
            'Altrincham' => ['districts' => 'WA14–WA15', 'region' => 'Trafford', 'stock' => 'premium housing, market-town retail and offices', 'travel' => 'typically 25–40 minutes from Stockport', 'focus' => 'professional offices and residential portfolios'],
            'Sale' => ['districts' => 'M33', 'region' => 'Trafford', 'stock' => 'suburban housing and local retail', 'travel' => 'typically 25–40 minutes from Stockport', 'focus' => 'landlords and small commercial'],
            'Wilmslow' => ['districts' => 'SK9', 'region' => 'Cheshire East', 'stock' => 'premium residential and town retail', 'travel' => 'typically 20–35 minutes from Stockport', 'focus' => 'high-spec residential and boutique commercial'],
            'Macclesfield' => ['districts' => 'SK10–SK11', 'region' => 'Cheshire East', 'stock' => 'market-town housing, mills and industrial estates', 'travel' => 'typically 25–40 minutes from Stockport', 'focus' => 'SME industrial and residential'],
            'Crewe' => ['districts' => 'CW1–CW2', 'region' => 'Cheshire East', 'stock' => 'rail-town housing, logistics and industrial', 'travel' => 'typically 40–55 minutes from Stockport', 'focus' => 'industrial and multi-let'],
            'Lancaster' => ['districts' => 'LA1–LA2', 'region' => 'Lancashire', 'stock' => 'university housing, city retail and offices', 'travel' => 'typically 70–90 minutes from Stockport', 'focus' => 'student HMOs and commercial'],
            'Burnley' => ['districts' => 'BB10–BB12', 'region' => 'East Lancashire', 'stock' => 'terraced housing and industrial estates', 'travel' => 'typically 55–75 minutes from Stockport', 'focus' => 'landlord portfolios and factories'],
            'Blackburn' => ['districts' => 'BB1–BB2', 'region' => 'East Lancashire', 'stock' => 'terraced streets, mills and retail parks', 'travel' => 'typically 50–70 minutes from Stockport', 'focus' => 'residential and industrial'],
            'Southport' => ['districts' => 'PR8–PR9', 'region' => 'Merseyside coast', 'stock' => 'Victorian housing, guest accommodation and retail', 'travel' => 'typically 60–80 minutes from Stockport', 'focus' => 'hospitality and residential'],
            'St Helens' => ['districts' => 'WA9–WA11', 'region' => 'Merseyside', 'stock' => 'housing estates and industrial corridors', 'travel' => 'typically 45–60 minutes from Stockport', 'focus' => 'commercial and social housing stock'],
            'Chorley' => ['districts' => 'PR6–PR7', 'region' => 'Lancashire', 'stock' => 'market-town housing and business parks', 'travel' => 'typically 45–60 minutes from Stockport', 'focus' => 'SME and residential'],
            'Kendal' => ['districts' => 'LA9', 'region' => 'Cumbria', 'stock' => 'market-town retail, tourism and residential', 'travel' => 'typically 80–100 minutes from Stockport', 'focus' => 'tourism hospitality and town retail'],
            'Carlisle' => ['districts' => 'CA1–CA3', 'region' => 'Cumbria', 'stock' => 'city housing, retail and logistics', 'travel' => 'typically 90–120 minutes from Stockport', 'focus' => 'commercial and multi-site'],
            'Windermere' => ['districts' => 'LA23', 'region' => 'Lake District', 'stock' => 'hotels, guest houses and holiday lets', 'travel' => 'typically 80–100 minutes from Stockport', 'focus' => 'hospitality fire and electrical compliance'],
            'Morecambe' => ['districts' => 'LA3–LA4', 'region' => 'Lancashire coast', 'stock' => 'seafront HMOs, guest houses and retail', 'travel' => 'typically 75–95 minutes from Stockport', 'focus' => 'hospitality and multi-let'],
            'Knutsford' => ['districts' => 'WA16', 'region' => 'Cheshire East', 'stock' => 'premium residential and town retail', 'travel' => 'typically 30–45 minutes from Stockport', 'focus' => 'high-spec residential and offices'],
            'Nantwich' => ['districts' => 'CW5', 'region' => 'Cheshire East', 'stock' => 'historic town retail and housing', 'travel' => 'typically 45–60 minutes from Stockport', 'focus' => 'town retail and residential'],
            'Didsbury' => ['districts' => 'M20', 'region' => 'South Manchester', 'stock' => 'period houses, apartments and high-street retail', 'travel' => 'typically 20–30 minutes from Stockport', 'focus' => 'landlord apartments and offices'],
            'Chorlton' => ['districts' => 'M21', 'region' => 'South Manchester', 'stock' => 'terraces, apartments and independent retail', 'travel' => 'typically 25–35 minutes from Stockport', 'focus' => 'residential portfolios'],
            'Withington' => ['districts' => 'M20', 'region' => 'South Manchester', 'stock' => 'student HMOs and terraced housing', 'travel' => 'typically 20–30 minutes from Stockport', 'focus' => 'HMO electrical and fire compliance'],
            'Wythenshawe' => ['districts' => 'M22–M23', 'region' => 'South Manchester', 'stock' => 'estate housing, retail and industrial', 'travel' => 'typically 20–35 minutes from Stockport', 'focus' => 'social housing and commercial'],
            'Eccles' => ['districts' => 'M30', 'region' => 'Salford', 'stock' => 'suburban housing and industrial', 'travel' => 'typically 30–45 minutes from Stockport', 'focus' => 'residential and light industry'],
            'Leigh' => ['districts' => 'WN7', 'region' => 'Wigan', 'stock' => 'town housing and industrial estates', 'travel' => 'typically 45–60 minutes from Stockport', 'focus' => 'commercial and landlord stock'],
            'Runcorn' => ['districts' => 'WA7', 'region' => 'Halton', 'stock' => 'new-town housing and chemical/industrial estates', 'travel' => 'typically 40–55 minutes from Stockport', 'focus' => 'industrial and multi-let'],
            'Widnes' => ['districts' => 'WA8', 'region' => 'Halton', 'stock' => 'housing and industrial riverside stock', 'travel' => 'typically 40–55 minutes from Stockport', 'focus' => 'industrial compliance'],
            'Birkenhead' => ['districts' => 'CH41–CH42', 'region' => 'Wirral', 'stock' => 'terraced housing, docks and retail', 'travel' => 'typically 55–75 minutes from Stockport', 'focus' => 'residential and commercial'],
            'Wallasey' => ['districts' => 'CH44–CH45', 'region' => 'Wirral', 'stock' => 'coastal housing and local retail', 'travel' => 'typically 60–80 minutes from Stockport', 'focus' => 'residential portfolios'],
        ];
    }

    if (isset($map[$area])) {
        return $map[$area] + ['name' => $area];
    }

    // Generic but still unique-ish profile for remaining towns
    $seed = area_seed($area);
    $regions = ['Greater Manchester fringe', 'Lancashire', 'Cheshire', 'Merseyside fringe', 'North West England'];
    $stocks = [
        'mixed residential terraces and local retail parades',
        'suburban housing, schools and small industrial units',
        'town-centre shops, offices and multi-let flats',
        'estate housing and light-industrial workshops',
        'period housing and independent high-street units',
    ];
    $focus = [
        'landlord and SME compliance',
        'residential portfolios and small commercial',
        'multi-let and facilities-managed sites',
        'retail, offices and HMO stock',
    ];
    $mins = 25 + ($seed % 55);
    return [
        'name' => $area,
        'districts' => 'local postcodes around ' . $area,
        'region' => pick_seeded($regions, $seed, 0),
        'stock' => pick_seeded($stocks, $seed, 1),
        'travel' => "typically {$mins}–" . ($mins + 20) . ' minutes from our Stockport SK2 base',
        'focus' => pick_seeded($focus, $seed, 2),
        'authority' => '',
    ];
}

/**
 * Nearby towns already on the North West list. Tier-1 uses real neighbours
 * from that list. Other towns use the next names in catalogue order.
 *
 * @return list<string>
 */
function icomplyNearbyTowns(string $area, int $limit = 6): array
{
    $curated = [
        'Stockport' => ['Cheadle', 'Cheadle Hulme', 'Bramhall', 'Hazel Grove', 'Marple', 'Romiley', 'Poynton', 'Manchester'],
        'Manchester' => ['Salford', 'Trafford', 'Stockport', 'Oldham', 'Tameside', 'Chorlton', 'Didsbury', 'Bolton'],
        'Salford' => ['Manchester', 'Eccles', 'Swinton', 'Walkden', 'Worsley', 'Trafford', 'Bolton', 'Irlam'],
        'Trafford' => ['Altrincham', 'Sale', 'Stretford', 'Urmston', 'Manchester', 'Salford', 'Chorlton', 'Stockport'],
        'Tameside' => ['Ashton-under-Lyne', 'Hyde', 'Denton', 'Dukinfield', 'Stalybridge', 'Mossley', 'Droylsden', 'Stockport', 'Oldham'],
        'Oldham' => ['Rochdale', 'Tameside', 'Ashton-under-Lyne', 'Chadderton', 'Royton', 'Shaw', 'Lees', 'Failsworth', 'Manchester'],
        'Bolton' => ['Bury', 'Wigan', 'Horwich', 'Farnworth', 'Westhoughton', 'Salford', 'Radcliffe', 'Manchester'],
        'Wigan' => ['Leigh', 'Bolton', 'Ashton-in-Makerfield', 'Hindley', 'Standish', 'Golborne', 'St Helens', 'Atherton'],
        'Liverpool' => ['Bootle', 'Birkenhead', 'Crosby', 'Wallasey', 'Maghull', 'Prescot', 'St Helens', 'Warrington'],
        'Warrington' => ['Lymm', 'Culcheth', 'Birchwood', 'Great Sankey', 'Newton-le-Willows', 'Runcorn', 'Widnes', 'Manchester'],
    ];
    $known = [];
    if (function_exists('getAreas')) {
        foreach (getAreas() as $name) {
            $known[(string)$name] = true;
        }
    }
    $picked = [];
    foreach ($curated[$area] ?? [] as $name) {
        if ($name !== $area && isset($known[$name])) {
            $picked[] = $name;
        }
        if (count($picked) >= $limit) {
            return $picked;
        }
    }
    if (count($picked) >= 4) {
        return $picked;
    }
    $names = array_keys($known);
    $idx = array_search($area, $names, true);
    if ($idx === false || !$names) {
        return $picked;
    }
    $n = count($names);
    for ($i = 1; $i <= $limit && count($picked) < $limit; $i++) {
        $name = $names[($idx + $i) % $n];
        if ($name !== $area && !in_array($name, $picked, true)) {
            $picked[] = $name;
        }
    }
    return $picked;
}

function icomplyVariedH1(string $label, string $area): string
{
    $p = area_profile($area);
    $seed = area_seed($area, 'h1|' . $label);
    $focus = (string)($p['focus'] ?? 'local property compliance');
    $districts = (string)($p['districts'] ?? $area);
    $patterns = [
        "{$label} in {$area}",
        "{$area} {$label} — {$focus}",
        "{$label} across {$area} ({$districts})",
        "{$area}: {$label} for local property stock",
    ];
    return $patterns[$seed % count($patterns)];
}

/**
 * Town-specific copy. Council names are only the ones stored on the profile.
 */
function icomplyLocalEssay(string $serviceName, string $slug, string $area): string
{
    $p = area_profile($area);
    $nearby = icomplyNearbyTowns($area, 6);
    $nearbyText = $nearby ? implode(', ', $nearby) : 'other North West towns on our list';
    $authority = trim((string)($p['authority'] ?? ''));
    $gasTopic = function_exists('icomplyCopyIsGasTopic') && icomplyCopyIsGasTopic($slug, $serviceName);
    $angle = function_exists('service_local_angle')
        ? service_local_angle($slug, $serviceName, $area)
        : "{$serviceName} in {$area} is scoped before any quote.";
    $phone = defined('PHONE') ? PHONE : '';
    if ($authority !== '') {
        $authorityLine = "{$authority} is the local authority for {$area}. iComply Property Services is a separate business in {$area}. This page does not speak for {$authority}, and it does not claim a {$area} council contract or a council accreditation.";
    } else {
        $authorityLine = "This {$area} page does not name a local authority, because the town profile does not store one. No council contract is claimed for {$area}.";
    }
    $sentences = [
        $gasTopic
            ? "{$serviceName} in {$area} is not a visit iComply carries out. Call {$phone} for non-gas compliance in {$area}. Price on application."
            : "{$serviceName} in {$area} is POA. Call {$phone} and ask for a {$area} quote once property type and access are known.",
        "Postcode districts used for {$area}: {$p['districts']}. Regional setting for {$area}: {$p['region']}.",
        "Building mix already recorded for {$area}: {$p['stock']}.",
        "Work this {$area} page is written around: {$p['focus']}.",
        "Travel to {$area} from the Stockport SK2 base: {$p['travel']}.",
        "Nearby towns we also cover from the {$area} page, all of them already on the North West list: {$nearbyText}.",
        $authorityLine,
        $angle,
        $gasTopic
            ? "Any {$serviceName} check in {$area} means landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not carry out that gas work, does not issue the certificate, and is not Gas Safe registered. Non-gas compliance in {$area} is quoted POA. Call {$phone}."
            : "A {$area} {$serviceName} visit is quoted only after scope is agreed. There is no catalogue price for {$serviceName} in {$area}.",
    ];
    $seed = area_seed($area, 'essay|' . $slug);
    $rot = $seed % count($sentences);
    $ordered = array_merge(array_slice($sentences, $rot), array_slice($sentences, 0, $rot));
    return implode(' ', $ordered);
}

function service_local_angle(string $slug, string $serviceName, string $area): string {
    $seed = area_seed($area, $slug);
    if (function_exists('icomplyCopyIsGasTopic') && icomplyCopyIsGasTopic($slug, $serviceName) && function_exists('icomplyGasLocalAngles')) {
        return pick_seeded(icomplyGasLocalAngles($serviceName, $area), $seed, 0);
    }
    $angles = [
        'electrical' => [
            "In {$area}, EICR demand is driven by landlord regulations and insurer checks on older consumer units.",
            "{$area} properties often need consumer unit upgrades alongside EICR remedial works.",
            "PAT testing and periodic inspection programmes are popular with {$area} offices and warehouses.",
            "EV charger installs and rewires are increasingly requested on {$area} residential and commercial stock.",
        ],
        'fire-alarms' => [
            "{$area} multi-let and commercial buildings often need BS 5839 category reviews after fire risk assessments.",
            "Addressable upgrades are common in {$area} blocks where conventional systems no longer match the fire strategy.",
            "Landlords and RTMs in {$area} book six-monthly servicing with full certificate packs for insurers.",
            "Cause-and-effect testing is critical for {$area} sites with access control and door release interfaces.",
        ],
        'emergency-lighting' => [
            "Escape-route lighting failures are a frequent audit finding in {$area} commercial and HMO stock.",
            "Self-test LED upgrades cut monthly test labour for {$area} multi-site landlords.",
            "BS 5266 duration testing programmes keep {$area} logbooks ready for inspections.",
            "Industrial and warehouse sites around {$area} often need IP-rated emergency fittings.",
        ],
        'aov-air-handling' => [
            "Smoke ventilation and AOV reliability is vital for multi-storey residential stock in and around {$area}.",
            "{$area} apartment blocks often need actuator, panel and interface health checks against the fire strategy.",
            "Air handling and smoke shaft maintenance supports safe means of escape in taller {$area} buildings.",
            "We coordinate AOV works with fire alarm cause-and-effect on {$area} mixed-use sites.",
        ],
        'nurse-call' => [
            "Care homes and supported living around {$area} need dependable nurse call with clear call logging.",
            "HTM-aligned maintenance plans help {$area} care providers evidence system reliability.",
            "Wireless expansions are useful where {$area} buildings cannot take new hard wiring easily.",
            "Handset and panel upgrades restore coverage room-by-room without full rip-outs in {$area}.",
        ],
        'gas-systems' => function_exists('icomplyGasLocalAngles')
            ? icomplyGasLocalAngles($serviceName, $area)
            : [
                "Landlord gas safety certificates (CP12) in {$area}, carried out by Gas Safe registered engineers. iComply does not issue them.",
            ],
        'intruder-alarm' => [
            "{$area} retail and SME units often upgrade to app-connected hybrid intruder systems.",
            "PIR, door contacts and shock sensors are tailored to {$area} building layouts.",
            "ARC-ready installs support insurer requirements for higher-risk {$area} premises.",
            "Takeovers of legacy panels are common after {$area} tenants change or expand sites.",
        ],
        'cctv' => [
            "IP CCTV with remote viewing is popular for {$area} retail parks, yards and apartment blocks.",
            "Camera placement around {$area} sites balances coverage with GDPR-aware privacy angles.",
            "NVR upgrades and storage expansions keep evidence retention workable for {$area} managers.",
            "Multi-building {$area} estates benefit from unified viewing for facilities teams.",
        ],
        'access-control' => [
            "Card/fob access with audit trails suits multi-tenant offices and blocks across {$area}.",
            "Time zones and user groups help {$area} landlords control cleaners, contractors and tenants.",
            "Fire door release strategies must stay safe while securing {$area} entry points.",
            "Biometric and mobile credentials are increasingly specified on newer {$area} fit-outs.",
        ],
        'door-entry' => [
            "Video door entry upgrades are frequent on {$area} apartment risers and older audio panels.",
            "Block handset replacements restore service without full building downtime in {$area}.",
            "Gated developments around {$area} often combine door entry with access control.",
            "We survey panel condition, cabling and power before quoting {$area} block upgrades.",
        ],
        'intercoms' => [
            "Video intercoms improve visitor screening for {$area} flats and office suites.",
            "Faulty handsets and door stations are a common reactive callout across {$area}.",
            "Multi-tenant intercom design must match the building directory structure in {$area}.",
            "Integration with door release keeps {$area} visitor journeys simple for residents and staff.",
        ],
        'legionella-risk-assessment' => [
            "Rented stock in {$area} often needs a Legionella risk record when stored water or little-used showers exist.",
            "Commercial and multi-let buildings around {$area} are quoted POA once plant and outlets are known.",
            "Simple combi-fed houses in {$area} are usually lower risk than tanked systems — we still write that down.",
            "Travel to {$area} is from our Stockport SK2 base; sampling is only added when the assessment supports it.",
        ],
        'asbestos-survey' => [
            "Older {$area} commercial and common-parts stock is a typical reason to book an asbestos management survey.",
            "Refurbishment in {$area} needs a more intrusive survey than a manage-in-place visit — we scope that honestly.",
            "Quotes for {$area} are POA after age, access and planned opening-up are clear.",
            "Licensed removal, if the {$area} survey finds it necessary, is by others — not this page.",
        ],
    ];
    $pool = $angles[$slug] ?? ["Professional {$serviceName} is available across {$area} and nearby postcodes."];
    return pick_seeded($pool, $seed, 0);
}

function seo_unique_intro(string $serviceName, string $slug, string $area): string {
    $p = area_profile($area);
    $angle = service_local_angle($slug, $serviceName, $area);
    $standards = implode(', ', array_slice(service_standards($slug), 0, 3));
    $seed = area_seed($area, $slug . 'intro');
    $openers = [
        "If you manage property in {$area} ({$p['districts']}), reliable {$serviceName} is not optional — it is how you stay audit-ready.",
        "For {$serviceName} in {$area}, iComply Property Services supports landlords, agents and businesses across {$p['region']}.",
        "{$area} sites — from {$p['stock']} — need {$serviceName} that matches UK standards and real building use.",
        "Searching for {$serviceName} near {$area}? Our Stockport team covers {$p['districts']} with documented install and service work.",
    ];
    $mid = (function_exists('isPoaService') && isPoaService($slug))
        ? "We scope {$serviceName} against {$standards} for the system or building you actually have — written notes for the file, not a catalogue install. {$angle}"
        : "We design, install, maintain and certificate {$serviceName} with attention to {$standards}. {$angle}";
    $close = (function_exists('isPoaService') && isPoaService($slug))
        ? "Travel to {$area} is {$p['travel']}. Typical focus in this area: {$p['focus']}. Quotes are price on application after we confirm scope — no invented fee on this page."
        : "Travel to {$area} is {$p['travel']}. Typical focus in this area: {$p['focus']}. Quotes are free; fixed pricing is used whenever the scope is clear after survey or photos.";
    return pick_seeded($openers, $seed, 0) . ' ' . $mid . ' ' . $close;
}

function seo_unique_local_block(string $serviceName, string $slug, string $area): array {
    $p = area_profile($area);
    $seed = area_seed($area, $slug . 'block');
    $bullets = [
        "Postcode focus: {$p['districts']} and surrounding {$area} streets",
        "Building stock we regularly see: {$p['stock']}",
        "Regional context: {$p['region']} compliance expectations",
        "Response: {$p['travel']}",
        "Local priority use-cases: {$p['focus']}",
        service_local_angle($slug, $serviceName, $area),
    ];
    // shuffle deterministically
    usort($bullets, function ($a, $b) use ($seed) {
        return (area_seed($a, (string)$seed) <=> area_seed($b, (string)$seed));
    });
    return $bullets;
}

function seo_unique_why(string $serviceName, string $area): array {
    $p = area_profile($area);
    $seed = area_seed($area, 'why');
    $all = [
        "Stockport SK2 base with scheduled {$area} attendance ({$p['travel']})",
        "Paperwork landlords, freeholders and insurers in {$p['region']} expect to see",
        "{$serviceName} plus related fire/electrical/security trades under one contractor",
        "Clear scope — fixed-price quotes when survey/photos define the works",
        "Experience with {$p['stock']} typical of {$area}",
        "Diary slots for {$area} depend on access and the published town list",
        "Remedial advice prioritised so {$area} sites pass the next inspection first time where practical",
    ];
    $out = [];
    for ($i = 0; $i < 5; $i++) {
        $out[] = pick_seeded($all, $seed, $i);
    }
    return array_values(array_unique($out));
}

function seo_extra_faqs(string $slug, string $serviceName, string $area): array {
    $p = area_profile($area);
    $seed = area_seed($area, $slug . 'faq');
    $extras = [
        ['q' => "Which postcodes do you cover for {$serviceName} around {$area}?", 'a' => "We regularly serve {$p['districts']} and neighbouring {$area} streets, plus wider {$p['region']} when diary capacity allows."],
        ['q' => "How quickly can engineers reach {$area}?", 'a' => "From Stockport SK2, travel is {$p['travel']}. Urgent unsafe situations are prioritised; routine works are booked to the next suitable slot."],
        ['q' => "What property types in {$area} do you work on?", 'a' => "Typical {$area} stock includes {$p['stock']}. Tell us building use, floors and access so we can scope {$serviceName} correctly."],
        ['q' => "Can you coordinate {$serviceName} with other compliance works in {$area}?", 'a' => "Yes. Many {$area} clients book combined visits (for example fire alarms with emergency lighting, or access control with door entry) to reduce disruption."],
        ['q' => "Do you leave certificates after {$serviceName} in {$area}?", 'a' => "Yes. You receive documentation suitable for landlords, managing agents and insurers after testing/commissioning on your {$area} site."],
    ];
    return [
        pick_seeded($extras, $seed, 0),
        pick_seeded($extras, $seed, 2),
    ];
}

function howto_schema(string $serviceName, string $area): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => "How to book {$serviceName} in {$area}",
        'description' => "Steps to arrange professional {$serviceName} with iComply Property Services in {$area}.",
        'step' => [
            ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Request a quote', 'text' => "Share your {$area} postcode, property type and {$serviceName} requirement."],
            ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Survey / scope', 'text' => 'We confirm standards, access and existing equipment.'],
            ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Works on site', 'text' => "Engineers complete install or service at your {$area} property."],
            ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Certification', 'text' => 'You receive certificates and recommendations for ongoing compliance.'],
        ],
    ];
}

function organization_schema(): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        '@id' => site_url() . '#organization',
        'name' => SITE_NAME,
        'url' => site_url(),
        'logo' => site_url('assets/images/og-image.jpg'),
        'email' => EMAIL,
        'telephone' => PHONE,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '17 Woodlands Park Road, Offerton',
            'addressLocality' => 'Stockport',
            'addressRegion' => 'Greater Manchester',
            'postalCode' => 'SK2 5DE',
            'addressCountry' => 'GB',
        ],
        'sameAs' => array_values(array_filter([
            defined('SOCIAL_GOOGLE') ? SOCIAL_GOOGLE : '',
            defined('SOCIAL_YOUTUBE') ? SOCIAL_YOUTUBE : '',
            'https://wa.me/' . WHATSAPP,
        ])),
    ];
}

function website_schema(): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => site_url() . '#website',
        'url' => site_url(),
        'name' => SITE_NAME,
        'publisher' => ['@id' => site_url() . '#organization'],
        'inLanguage' => 'en-GB',
        'potentialAction' => [
            '@type' => 'CommunicateAction',
            'name' => 'Request a free compliance quote',
            'target' => site_url('contact.php'),
        ],
    ];
}
