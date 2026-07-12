<?php
// Icomply Property Services - Config
define('SITE_NAME', 'Icomply Property Services');
define('SITE_URL', 'http://localhost:8000');
define('PHONE', '07517806082');
define('EMAIL', 'info@icomplypropertyservices.co.uk');
define('ADDRESS', '17 Woodlands Park Road, Offerton, Stockport, SK2 5DE');
define('WHATSAPP', '447517806082');

// Hardcoded admin
define('ADMIN_USER', 'jackscott');
define('ADMIN_PASS', 'Neverknow1');

/** Build an absolute site URL path (works from any nested page). */
function site_url(string $path = ''): string {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

// Services list (extendable via admin)
$services = [
    'electrical' => 'Electrical',
    'fire-alarms' => 'Fire Alarms',
    'emergency-lighting' => 'Emergency Lighting',
    'aov-air-handling' => 'AOV & Air Handling',
    'nurse-call' => 'Nurse Call Systems',
    'gas-systems' => 'Gas Systems',
    'intruder-alarm' => 'Intruder Alarms',
    'cctv' => 'CCTV Systems',
    'access-control' => 'Access Control',
    'door-entry' => 'Door Entry Systems',
    'intercoms' => 'Intercoms'
];

/** UK photo path for a service (realistic photos preferred over package icons). */
function service_image(string $slug, string $type = 'photo'): string {
    $photo = "assets/images/services/{$slug}-photo.jpg";
    $pack  = "assets/images/services/{$slug}.png";
    $path  = ($type === 'photo' && file_exists(__DIR__ . '/' . $photo)) ? $photo
           : (file_exists(__DIR__ . '/' . $pack) ? $pack : 'assets/images/heroes/home-hero.jpg');
    return site_url($path);
}

/** Short blurb for service cards */
function service_blurb(string $slug): string {
    $blurbs = [
        'electrical' => 'EICR, PAT, rewires, EV chargers & commercial installs to BS 7671.',
        'fire-alarms' => 'Design, install & service BS 5839 fire detection systems.',
        'emergency-lighting' => 'BS 5266 emergency lighting install, testing & certification.',
        'aov-air-handling' => 'Smoke vents, AOV systems & air handling maintenance.',
        'nurse-call' => 'Care home & hospital nurse call install and HTM-aligned service.',
        'gas-systems' => 'Gas Safe certificates, boiler service & landlord CP12/CP44.',
        'intruder-alarm' => 'Wired & wireless burglar alarms for homes and businesses.',
        'cctv' => 'IP CCTV design, install & remote monitoring setup.',
        'access-control' => 'Card, fob & biometric door access for multi-tenant sites.',
        'door-entry' => 'Audio & video door entry for flats, offices and estates.',
        'intercoms' => 'Video and audio intercom systems for residential & commercial.',
    ];
    return $blurbs[$slug] ?? 'Professional installation, maintenance and certification.';
}

/** Feature bullets for service landing pages */
function service_features(string $slug): array {
    $all = [
        'electrical' => [
            ['title' => 'Installation & upgrades', 'text' => 'New installs, full rewires, EV chargers, consumer unit upgrades and commercial wiring.'],
            ['title' => 'Testing & certification', 'text' => 'EICR, PAT testing, landlord certificates and BS 7671 periodic inspections.'],
            ['title' => 'Maintenance & emergency', 'text' => 'Planned contracts, fault finding and rapid callouts across the North West.'],
        ],
        'fire-alarms' => [
            ['title' => 'System design & install', 'text' => 'Conventional and addressable fire alarm systems designed to BS 5839.'],
            ['title' => 'Servicing & maintenance', 'text' => 'Scheduled servicing, battery checks and full documentation packs.'],
            ['title' => 'Testing & certification', 'text' => 'Commissioning certificates and ongoing compliance reports for insurers and landlords.'],
        ],
        'emergency-lighting' => [
            ['title' => 'LED emergency lighting', 'text' => 'Maintained and non-maintained bulkheads, exit signs and industrial fittings.'],
            ['title' => 'BS 5266 testing', 'text' => 'Monthly function tests, annual full-duration tests and logbooks.'],
            ['title' => 'Self-test systems', 'text' => 'Modern self-test emergency lighting for lower ongoing labour cost.'],
        ],
        'aov-air-handling' => [
            ['title' => 'AOV installation', 'text' => 'Automatic opening vents and smoke shaft systems for multi-storey buildings.'],
            ['title' => 'Air handling service', 'text' => 'Planned maintenance of AHUs and smoke control plant.'],
            ['title' => 'Compliance support', 'text' => 'Inspection, certification and remedial works to fire strategy requirements.'],
        ],
        'nurse-call' => [
            ['title' => 'Care home systems', 'text' => 'Wired and wireless nurse call for care homes, hospitals and supported living.'],
            ['title' => 'HTM-aligned service', 'text' => 'Maintenance schedules aligned with HTM 08-03 best practice.'],
            ['title' => 'Upgrades & repairs', 'text' => 'Panel upgrades, handset replacements and full system health checks.'],
        ],
        'gas-systems' => [
            ['title' => 'Gas safety certificates', 'text' => 'Landlord gas safety checks and certification for rented properties.'],
            ['title' => 'Boiler servicing', 'text' => 'Annual boiler service, breakdown repair and efficiency checks.'],
            ['title' => 'Commercial gas work', 'text' => 'Commercial plant rooms, catering gas and multi-unit estates.'],
        ],
        'intruder-alarm' => [
            ['title' => 'Home & business alarms', 'text' => 'Wired and wireless intruder systems with app control options.'],
            ['title' => 'Detection coverage', 'text' => 'PIRs, door contacts, shock sensors and external detectors.'],
            ['title' => 'Monitoring ready', 'text' => 'Installations prepared for ARC monitoring where required.'],
        ],
        'cctv' => [
            ['title' => 'IP CCTV design', 'text' => 'HD/4K camera layouts for retail, warehouses, offices and estates.'],
            ['title' => 'Recording & remote view', 'text' => 'NVR systems with secure remote viewing for managers and owners.'],
            ['title' => 'Service & expansion', 'text' => 'Camera upgrades, hard drive replacement and multi-site expansions.'],
        ],
        'access-control' => [
            ['title' => 'Door access systems', 'text' => 'Card, fob, PIN and biometric readers for single and multi-door sites.'],
            ['title' => 'Multi-tenant control', 'text' => 'Time zones, user groups and audit trails for landlords and FM teams.'],
            ['title' => 'Integration ready', 'text' => 'Works with door entry, intercoms and fire door release strategies.'],
        ],
        'door-entry' => [
            ['title' => 'Video door entry', 'text' => 'Colour video panels for apartments, offices and gated developments.'],
            ['title' => 'Audio systems', 'text' => 'Robust audio door entry for budgets and retrofit blocks.'],
            ['title' => 'Block upgrades', 'text' => 'Full riser upgrades and handset replacements across multi-storey buildings.'],
        ],
        'intercoms' => [
            ['title' => 'Video intercoms', 'text' => 'Internal handsets and door stations with clear two-way communication.'],
            ['title' => 'Multi-tenant setups', 'text' => 'Systems for flats, offices and mixed-use buildings.'],
            ['title' => 'Service & repair', 'text' => 'Fault finding, handset swaps and panel replacements.'],
        ],
    ];
    return $all[$slug] ?? [
        ['title' => 'Installation', 'text' => 'Full design, supply and install to current UK standards.'],
        ['title' => 'Maintenance', 'text' => 'Planned servicing and reactive repairs.'],
        ['title' => 'Certification', 'text' => 'Testing with full compliance documentation.'],
    ];
}

// Major areas (from user list)
$areas = ['Manchester','Salford','Bolton','Bury','Oldham','Rochdale','Stockport','Wigan','Leigh','Atherton','Tyldesley','Horwich','Westhoughton','Farnworth','Kearsley','Little Lever','Radcliffe','Whitefield','Prestwich','Swinton','Eccles','Walkden','Worsley','Pendlebury','Irlam','Cadishead','Altrincham','Sale','Stretford','Urmston','Chorlton','Didsbury','Withington','Wythenshawe','Cheadle','Cheadle Hulme','Bramhall','Hazel Grove','Marple','Romiley','Hyde','Stalybridge','Dukinfield','Ashton-under-Lyne','Mossley','Droylsden','Denton','Failsworth','Middleton','Chadderton','Heywood','Milnrow','Littleborough','Shaw','Royton','Lees','Uppermill','Saddleworth','Liverpool','Birkenhead','Wallasey','Bebington','Heswall','West Kirby','Hoylake','Bootle','Crosby','Maghull','Formby','Ainsdale','Southport','Ormskirk','Burscough','Skelmersdale','Rainford','Prescot','Whiston','Rainhill','St Helens','Haydock','Newton-le-Willows','Golborne','Ashton-in-Makerfield','Hindley','Ince','Standish','Shevington','Preston','Leyland','Chorley','Bamber Bridge','Penwortham','Fulwood','Longridge','Garstang','Kirkham','Wesham','Lytham St Annes','Blackpool','Fleetwood','Thornton-Cleveleys','Poulton-le-Fylde','Lancaster','Morecambe','Carnforth','Heysham','Barrow-in-Furness','Ulverston','Dalton-in-Furness','Millom','Kendal','Windermere','Ambleside','Keswick','Whitehaven','Workington','Maryport','Cockermouth','Penrith','Carlisle','Chester','Ellesmere Port','Neston','Frodsham','Helsby','Tarvin','Tarporley','Nantwich','Crewe','Sandbach','Middlewich','Winsford','Northwich','Knutsford','Wilmslow','Handforth','Poynton','Macclesfield','Congleton','Holmes Chapel','Alsager','Warrington','Runcorn','Widnes','Halton','Appleton','Stockton Heath','Lymm','Culcheth','Risley','Birchwood','Great Sankey','Penketh','Blackburn','Darwen','Accrington','Burnley','Nelson','Colne','Rawtenstall','Bacup','Rossendale','Clitheroe','Whalley','Padiham','Great Harwood','Clayton-le-Moors','Ramsbottom'];

// Ensure templates using $GLOBALS always resolve correctly
$GLOBALS['services'] = $services;
$GLOBALS['areas'] = $areas;

function getSeoKeywords($service, $area = '') {
    $base = [
        'electrical' => 'electrical installation, EICR, PAT testing, certified electrician, commercial electrician, EV charger installation, electrical compliance',
        'fire-alarms' => 'fire alarm installation, fire alarm servicing, BS 5839, fire detection system, fire alarm certification, commercial fire alarm',
        'emergency-lighting' => 'emergency lighting installation, BS 5266, emergency lighting testing, emergency lighting certification, landlord emergency lighting',
        'aov-air-handling' => 'AOV installation, AOV maintenance, smoke vent system, BS 9991, automatic opening vent',
        'nurse-call' => 'nurse call system installation, nurse call maintenance, HTM 08-03, care home nurse call',
        'gas-systems' => 'gas safety certificate, gas boiler servicing, landlord gas safety, CP44, gas installation',
        'intruder-alarm' => 'intruder alarm installation, burglar alarm, BS 4737, commercial intruder alarm',
        'cctv' => 'CCTV installation, IP CCTV system, commercial CCTV, video surveillance',
        'access-control' => 'access control installation, biometric access control, door access control',
        'door-entry' => 'door entry installation, video door entry, audio door entry, apartment door entry',
        'intercoms' => 'intercom installation, video intercom, audio intercom, multi tenant intercom'
    ];
    $kw = $base[$service] ?? $service;
    return $area ? "$kw $area, $area electrician, $area fire safety" : $kw;
}

function areaSlug($area) {
    return strtolower(str_replace([' ', '-'], ['-', ''], $area));
}

// Simple JSON-backed service storage for admin
function loadServices() {
    $file = __DIR__ . '/data/services.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?: [];
    }
    return [];
}

function saveServices($services) {
    if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0777, true);
    file_put_contents(__DIR__ . '/data/services.json', json_encode($services, JSON_PRETTY_PRINT));
}
?>