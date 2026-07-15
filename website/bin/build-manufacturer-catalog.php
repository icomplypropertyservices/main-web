<?php
/**
 * Build comprehensive manufacturer catalog from expanded brand lists.
 * Merges into data/manufacturers.json (preserves images_by_service, keyword_images, seo_keywords).
 *
 * Usage: php bin/build-manufacturer-catalog.php
 */
require_once __DIR__ . '/../config.php';

$byService = [
    'electrical' => [
        'Schneider Electric', 'Hager', 'Wylex', 'MK Electric', 'Crabtree', 'Fusebox',
        'Rolec EV', 'Myenergi', 'GivEnergy', 'BG Electrical', 'Contactum', 'Lewden',
        'Timeguard', 'Greenbrook', 'Garo', 'Easee', 'Ohme', 'Wallbox', 'ABB',
        'Siemens', 'Legrand', 'Eaton', 'MEM', 'Click Scolmore', 'NAPIT Approved Equipment',
    ],
    'fire-alarms' => [
        'Kentec', 'Advanced Electronics', 'C-Tec', 'Morley', 'Hochiki', 'Apollo',
        'Gent', 'Notifier', 'Honeywell', 'Ziton', 'Haes Systems', 'EMS', 'Fike',
        'System Sensor', 'Nittan', 'Xtralis VESDA', 'FireClass', 'Sterling Safety',
        'Eaton Fire', 'Cooper Fire',
    ],
    'emergency-lighting' => [
        'Emergi-Lite', 'Mackwell', 'Cooper Lighting', 'Legrand', 'Ansell Lighting',
        'Thorlux', 'Fagerhult', 'Eaton', 'ABB', 'Zumtobel', 'JSB Electrical',
        'Beghelli', 'P4 Limited', 'Orbik', 'Menvier', 'Clevertronics', 'Luxonic',
        'D-Light', 'Thorn Lighting', 'Philips Emergency',
    ],
    'aov-air-handling' => [
        'SE Controls', 'Nuaire', 'Brooks', 'Ventilux', 'Geze', 'D+H Mechatronic',
        'TROX', 'Colt', 'Smoke Control', 'Assa Abloy', 'Group SCS', 'WindowMaster',
        'Simon RWA', 'Bilco', 'Cambric', 'Kingspan Air', 'FlaktGroup', 'Systemair',
    ],
    'nurse-call' => [
        'Courtney Thorne', 'Static Systems Group', 'Intercall', 'Aid Call', 'Tunstall',
        'Ascom', 'Schrack Seconet', 'Zettler', 'Ackermann', 'Rauland', 'Jeron',
        'Austco', 'TekTone', 'Caretech', 'Wandsworth', 'Quantec',
    ],
    'gas-systems' => [
        'Worcester Bosch', 'Vaillant', 'Ideal', 'Baxi', 'Glow-worm', 'Potterton',
        'Intergas', 'Remeha', 'Alpha', 'Ferroli', 'Viessmann', 'Ariston', 'Vokera',
        'Main Heating', 'Ravenheat', 'Navien', 'Grant', 'Firebird',
    ],
    'intruder-alarm' => [
        'Texecom', 'Honeywell', 'Pyronix', 'DSC', 'Visonic', 'Risco', 'Scantronic',
        'Ajax Systems', 'Paradox', 'GJD', 'Yale', 'Orisec', 'Crow Electronics',
        'Bosch Security', 'Hikvision AX PRO', 'Innerrange',
    ],
    'cctv' => [
        'Hikvision', 'Axis Communications', 'Dahua', 'Bosch', 'Hanwha Vision',
        'Avigilon', 'Vivotek', 'Uniview', 'Milesight', 'Wisenet', 'Mobotix',
        'FLIR', 'IDIS', 'Tiandy', 'Reolink', 'Panasonic i-PRO', 'Sony Security',
    ],
    'access-control' => [
        'Paxton', 'HID Global', 'Salto Systems', 'ASSA ABLOY', 'Honeywell',
        'Gallagher', 'Stanley Security', 'CDVI', 'TDSi', 'Kantech', 'Vanderbilt',
        'Nedap', 'Suprema', 'dormakaba', 'Allegion', 'LenelS2', 'Software House',
    ],
    'door-entry' => [
        'Videx', 'Fermax', 'BPT', 'Comelit', 'Aiphone', 'Paxton', 'Urmet',
        'Elvox', 'Golmar', '2N', 'Commax', 'Bitron Video', 'DoorKing',
        'Legrand Door Entry', 'BPT X1',
    ],
    'intercoms' => [
        'Aiphone', 'Commend', 'Zenitel', 'Barix', 'Stentofon', 'TOA', 'Siedle',
        'Clear-Com', 'Vingtor-Stentofon', 'Legrand', 'Algo', 'CyberData',
        'Axis Intercom', 'Akuvox',
    ],
];

// Product templates per service (trade-oriented placeholders for Shopify)
$productTemplates = [
    'electrical' => [
        ['{brand} Consumer Unit Spares Kit', 'Trade spares pack for {brand} boards and breakers.', 'From £49'],
        ['{brand} Installation Accessory Pack', 'Mounting and termination accessories for {brand} installs.', 'From £35'],
    ],
    'fire-alarms' => [
        ['{brand} Panel Service Kit', 'Engineer kit for {brand} panels — batteries, keys, labels.', 'From £89'],
        ['{brand} Detector / Device Pack', 'Trade pack of detectors or call points for {brand} systems.', 'From £65'],
    ],
    'emergency-lighting' => [
        ['{brand} Emergency Fitting Pack', 'LED emergency luminaires for {brand} ranges.', 'From £55'],
        ['{brand} Battery Replacement Kit', 'Replacement packs for {brand} emergency units.', 'From £28'],
    ],
    'aov-air-handling' => [
        ['{brand} Actuator Service Kit', 'Service consumables for {brand} AOV actuators.', 'From £75'],
        ['{brand} Control Panel Spares', 'Fuses, batteries and keys for {brand} smoke control panels.', 'From £45'],
    ],
    'nurse-call' => [
        ['{brand} Call Point / Pear Lead Pack', 'Replacement call points and leads for {brand}.', 'From £42'],
        ['{brand} Panel Battery Kit', 'Backup batteries for {brand} nurse call panels.', 'From £38'],
    ],
    'gas-systems' => [
        ['{brand} Boiler Service Kit', 'Service consumables compatible with {brand} boilers.', 'From £32'],
        ['{brand} Flue / Fitting Accessory', 'Trade accessories for {brand} installs.', 'From £24'],
    ],
    'intruder-alarm' => [
        ['{brand} Panel Expansion Kit', 'Zones, keypads or modules for {brand} systems.', 'From £59'],
        ['{brand} PIR / Contact Pack', 'Trade sensors for {brand} installations.', 'From £48'],
    ],
    'cctv' => [
        ['{brand} Camera Mount Kit', 'Brackets and weatherproof mounts for {brand} cameras.', 'From £29'],
        ['{brand} NVR / HDD Accessory Pack', 'Cabling and storage accessories for {brand}.', 'From £45'],
    ],
    'access-control' => [
        ['{brand} Reader / Token Pack', 'Readers or credentials for {brand} access systems.', 'From £69'],
        ['{brand} Door Controller Spares', 'PSU fuses, locks and exit devices for {brand}.', 'From £55'],
    ],
    'door-entry' => [
        ['{brand} Handset / Panel Spares', 'Replacement handsets and faceplates for {brand}.', 'From £52'],
        ['{brand} Entrance Panel Accessory', 'Rain hoods, flush boxes and modules for {brand}.', 'From £36'],
    ],
    'intercoms' => [
        ['{brand} Master Station Accessory', 'Accessories for {brand} master stations.', 'From £48'],
        ['{brand} Door Station Spares', 'Weatherproof door stations and modules for {brand}.', 'From £62'],
    ],
];

$blurbs = [
    'default' => 'Icomply Property Services installs, services and supplies {brand} equipment across Greater Manchester and the North West. We support new installs, upgrades and planned maintenance, and stock trade kits for engineers and facilities teams.',
];

$existing = loadJsonData('manufacturers', []);
$catalog = [];
$nameToSlug = [];

foreach ($byService as $service => $brands) {
    foreach ($brands as $name) {
        $slug = areaSlug($name);
        // Fix awkward slugs
        $slug = str_replace(['c-tec', 'd-h-mechatronic', 'd-light'], ['c-tec', 'dh-mechatronic', 'd-light'], $slug);
        if (!isset($catalog[$slug])) {
            $tpls = $productTemplates[$service] ?? $productTemplates['electrical'];
            $products = [];
            foreach ($tpls as $i => $t) {
                $products[] = [
                    'id' => $slug . '-product-' . ($i + 1),
                    'title' => str_replace('{brand}', $name, $t[0]),
                    'blurb' => str_replace('{brand}', $name, $t[1]),
                    'price' => $t[2],
                    'handle' => $slug . '-product-' . ($i + 1),
                    'shopify_product_id' => '',
                    'image' => '/assets/images/manufacturers/' . $slug . '.jpg',
                    'badge' => $i === 0 ? 'Trade' : '',
                ];
            }
            $catalog[$slug] = [
                'name' => $name,
                'slug' => $slug,
                'services' => [$service],
                'blurb' => str_replace('{brand}', $name, $blurbs['default']),
                'seo_title' => $name . ' Products & Service | North West',
                'seo_desc' => 'Buy and install ' . $name . ' systems with Icomply Property Services. Trade kits, installation, servicing and certification across Greater Manchester and the North West.',
                'seo_keywords' => $name . ', ' . $name . ' installation, ' . $name . ' service, ' . $name . ' spares, ' . $name . ' North West, trade ' . $name,
                'products' => $products,
                'featured' => in_array($name, [
                    'Kentec', 'Advanced Electronics', 'C-Tec', 'Hochiki', 'Apollo',
                    'Hikvision', 'Axis Communications', 'Paxton', 'Salto Systems',
                    'Schneider Electric', 'Hager', 'Myenergi', 'Rolec EV',
                    'Texecom', 'Worcester Bosch', 'Vaillant', 'Videx', 'Aiphone',
                    'Emergi-Lite', 'SE Controls', 'Courtney Thorne', 'Honeywell',
                ], true),
            ];
            $nameToSlug[$name] = $slug;
        } else {
            if (!in_array($service, $catalog[$slug]['services'], true)) {
                $catalog[$slug]['services'][] = $service;
            }
        }
    }
}

// Sort catalog by name
uasort($catalog, function ($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Expand images_by_service using catalog slugs (first 5 per service)
$imagesByService = $existing['images_by_service'] ?? [];
foreach ($byService as $service => $brands) {
    $slugs = [];
    foreach ($brands as $name) {
        $slugs[] = $nameToSlug[$name] ?? areaSlug($name);
        if (count($slugs) >= 6) {
            break;
        }
    }
    $imagesByService[$service] = $slugs;
}

$out = [
    'by_service' => $byService,
    'catalog' => $catalog,
    'images_by_service' => $imagesByService,
    'keyword_images' => $existing['keyword_images'] ?? [],
    'seo_keywords' => $existing['seo_keywords'] ?? [],
];

saveJsonData('manufacturers', $out);

echo 'Manufacturers by service brands: ' . array_sum(array_map('count', $byService)) . "\n";
echo 'Unique catalog entries: ' . count($catalog) . "\n";
echo 'Featured: ' . count(array_filter($catalog, fn($c) => !empty($c['featured']))) . "\n";
echo "Wrote data/manufacturers.json\n";
