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

/** Outward codes and building stock. See area-profiles.php for the full town list. */
function area_profile(string $area): array {
    static $map = null;
    if ($map === null) {
        $loaded = require __DIR__ . '/area-profiles.php';
        $map = is_array($loaded) ? $loaded : [];
    }

    if (isset($map[$area])) {
        $row = $map[$area];
        return [
            'name' => $area,
            'districts' => (string)$row['districts'],
            'region' => (string)$row['region'],
            'stock' => (string)$row['stock'],
            'travel' => 'scheduled from our Stockport SK2 base — we confirm a diary slot rather than a drive time',
            'focus' => (string)$row['focus'],
        ];
    }

    return [
        'name' => $area,
        'districts' => 'unspecified',
        'region' => 'North West',
        'stock' => 'mixed property — confirm on survey',
        'travel' => 'scheduled from our Stockport SK2 base — we confirm a diary slot rather than a drive time',
        'focus' => 'confirm the building before quoting',
    ];
}

function service_local_angle(string $slug, string $serviceName, string $area): string {
    $seed = area_seed($area, $slug);
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
        'gas-systems' => [
            "Landlord gas safety certificates remain a core compliance duty for rented stock in {$area}.",
            "Boiler servicing and breakdown cover are high demand for {$area} residential portfolios.",
            "Commercial kitchens and plant rooms around {$area} need planned gas maintenance.",
            "We prioritise unsafe situations and diary routine CP12-style checks across {$area} postcodes.",
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
            "Door controllers and vehicle barriers in {$area} are surveyed separately: safety edges, induction loops and fire-release are confirmed on site, not from a national price list.",
            "Card, fob and barrier readers in {$area} need a user list and a fail-safe exit path before we quote.",
            "Car-park barriers around {$area} are quoted after the loop, pedestal and existing controller are seen.",
            "Pedestrian doors and vehicle gates in {$area} stay on one access schedule only when the fire strategy allows it.",
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
    if (isset($angles[$slug])) {
        return pick_seeded($angles[$slug], $seed, 0);
    }
    $blurb = function_exists('getServiceBlurb') ? getServiceBlurb($slug) : $serviceName;
    $standards = function_exists('getServiceStandards') ? getServiceStandards($slug) : 'the standard named in the quote';
    return $blurb . ' In ' . $area . ' the notes cite ' . $standards . '.';
}

function seo_unique_intro(string $serviceName, string $slug, string $area): string {
    $p = area_profile($area);
    $angle = service_local_angle($slug, $serviceName, $area);
    $standards = implode(', ', array_slice(service_standards($slug), 0, 3));
    $seed = area_seed($area, $slug . 'intro');
    $openers = [
        "If you manage property in {$area} ({$p['districts']}), reliable {$serviceName} is not optional — it is how you stay audit-ready.",
        "For {$serviceName} in {$area}, Icomply Property Services supports landlords, agents and businesses across {$p['region']}.",
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
        "Same-week slots often available depending on {$area} diary load",
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
    $d = $p['districts'];
    $angle = service_local_angle($slug, $serviceName, $area);
    $extras = [
        ['q' => "Which postcodes do you cover for {$serviceName} around {$area}?", 'a' => "For {$serviceName} the outward codes we use for {$area} are {$d}. Neighbouring streets in {$p['region']} are booked when the diary allows. {$angle}"],
        ['q' => "How do you schedule {$serviceName} in {$area}?", 'a' => "Work in {$d} is scheduled from Stockport SK2. We confirm a slot for {$area}. We do not print a drive-time promise on this page. {$angle}"],
        ['q' => "What property types in {$area} do you work on?", 'a' => "In {$d} the buildings we usually see are {$p['stock']}. Tell us use, floors and access so {$serviceName} is scoped to that building. {$angle}"],
        ['q' => "Can you coordinate {$serviceName} with other compliance works in {$area}?", 'a' => "Yes, when the {$d} site can take a combined visit — for example fire alarms with emergency lighting, or door access with a vehicle barrier. Each system is still written up separately. {$angle}"],
        ['q' => "Do you leave paperwork after {$serviceName} in {$area}?", 'a' => "Yes. After testing or commissioning on a {$d} site you get the notes for that visit. We do not add a NICEIC or Gas Safe badge to the {$area} page. {$angle}"],
    ];
    return [
        pick_seeded($extras, $seed, 0),
        pick_seeded($extras, $seed, 2),
    ];
}

/** Town FAQs. Every answer includes the outward-code string so pages are not town-name swaps. */
function seo_town_faqs(string $slug, string $serviceName, string $area): array {
    $p = area_profile($area);
    $d = $p['districts'];
    $angle = service_local_angle($slug, $serviceName, $area);
    $barrier = $slug === 'access-control'
        ? " Vehicle barriers in {$d} are quoted only after the induction loop, safety edge and pedestal are seen."
        : '';
    return [
        [
            'q' => "Where in {$area} do you cover {$serviceName}?",
            'a' => "For {$serviceName} we schedule work in {$d}. Buildings we usually see: {$p['stock']}. {$angle}{$barrier}",
        ],
        [
            'q' => "What should I send for a {$area} {$serviceName} quote?",
            'a' => "A postcode in {$d}, the property use, and photos of the existing kit. {$serviceName} is quoted after that scope, from the Stockport SK2 office. Call 07517806082 if you need a diary slot. {$angle}",
        ],
        [
            'q' => "Do you show an accreditation badge for {$serviceName} in {$area}?",
            'a' => "No. This {$area} page ({$d}) does not claim NICEIC, Gas Safe, BAFE or CHAS for {$serviceName}. If a task needs a registered engineer, that check happens when the job is accepted. {$angle}",
        ],
    ];
}

function howto_schema(string $serviceName, string $area): array {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => "How to book {$serviceName} in {$area}",
        'description' => "Steps to arrange professional {$serviceName} with Icomply Property Services in {$area}.",
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
        'sameAs' => [
            'https://wa.me/' . WHATSAPP,
        ],
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
