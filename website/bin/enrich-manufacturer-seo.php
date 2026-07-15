<?php
/**
 * Generate unique SEO blurbs + product copy for every manufacturer catalog entry.
 */
require_once __DIR__ . '/../config.php';

$servicePhrases = [
    'electrical' => [
        'install' => 'electrical installation and consumer unit work',
        'std' => 'BS 7671',
        'buy' => 'boards, breakers, EV accessories and engineer spares',
    ],
    'fire-alarms' => [
        'install' => 'fire detection and alarm installation',
        'std' => 'BS 5839',
        'buy' => 'panel service kits, detectors and call-point packs',
    ],
    'emergency-lighting' => [
        'install' => 'emergency lighting installation and testing',
        'std' => 'BS 5266',
        'buy' => 'LED fittings, batteries and exit signage',
    ],
    'aov-air-handling' => [
        'install' => 'AOV and smoke-control installation',
        'std' => 'BS 9991 / EN 12101',
        'buy' => 'actuator spares and control panel consumables',
    ],
    'nurse-call' => [
        'install' => 'nurse call system installation',
        'std' => 'HTM 08-03 guidance',
        'buy' => 'call points, pear leads and panel batteries',
    ],
    'gas-systems' => [
        'install' => 'gas appliance and boiler work',
        'std' => 'Gas Safe practice',
        'buy' => 'service kits and flue accessories',
    ],
    'intruder-alarm' => [
        'install' => 'intruder alarm installation',
        'std' => 'PD 6662 / BS EN 50131',
        'buy' => 'PIRs, contacts and panel expansions',
    ],
    'cctv' => [
        'install' => 'CCTV design and installation',
        'std' => 'BS EN 62676',
        'buy' => 'camera mounts, cabling and NVR accessories',
    ],
    'access-control' => [
        'install' => 'access control installation',
        'std' => 'BS EN 50133',
        'buy' => 'readers, tokens and controller spares',
    ],
    'door-entry' => [
        'install' => 'door entry system installation',
        'std' => 'multi-tenant best practice',
        'buy' => 'handsets, panels and entrance accessories',
    ],
    'intercoms' => [
        'install' => 'intercom system installation',
        'std' => 'commercial intercom standards',
        'buy' => 'master station and door station accessories',
    ],
];

$mfr = loadJsonData('manufacturers', []);
$catalog = $mfr['catalog'] ?? [];
$updated = 0;

foreach ($catalog as $slug => &$entry) {
    $name = $entry['name'];
    $services = $entry['services'] ?? ['fire-alarms'];
    $primary = $services[0];
    $p = $servicePhrases[$primary] ?? $servicePhrases['fire-alarms'];
    $svcList = implode(', ', array_map(function ($s) {
        $map = getServices();
        return $map[$s] ?? $s;
    }, $services));

    $entry['blurb'] = "Icomply Property Services is your North West partner for {$name} — "
        . "{$p['install']} to {$p['std']}, plus planned maintenance and reactive repairs. "
        . "We supply trade {$p['buy']} for {$name} and support landlords, FM teams and contractors "
        . "across Greater Manchester, Lancashire, Cheshire and Merseyside.";

    $entry['seo_title'] = "{$name} Installation, Service & Products | North West";
    $entry['seo_desc'] = "Buy and install {$name} systems with Icomply. {$p['install']}, certification and trade kits "
        . "across Stockport, Manchester and the North West. Free quotes.";
    $entry['seo_keywords'] = implode(', ', [
        $name,
        "{$name} installation",
        "{$name} service",
        "{$name} maintenance",
        "{$name} spares",
        "{$name} trade",
        "{$name} North West",
        "{$name} Manchester",
        "{$name} Stockport",
        $svcList,
    ]);

    // Ensure 2 product cards with strong SEO titles
    $products = $entry['products'] ?? [];
    if (count($products) < 2) {
        $products = [
            [
                'id' => $slug . '-service-kit',
                'title' => "{$name} Service & Spares Kit",
                'blurb' => "Engineer-ready consumables for {$name} systems — ideal for maintenance contracts.",
                'price' => 'From £49',
                'handle' => $slug . '-service-kit',
                'shopify_product_id' => '',
                'image' => '/assets/images/manufacturers/' . $slug . '.jpg',
                'badge' => 'Trade',
            ],
            [
                'id' => $slug . '-install-pack',
                'title' => "{$name} Install Accessory Pack",
                'blurb' => "Mounting and install accessories commonly used on {$name} projects in the North West.",
                'price' => 'From £35',
                'handle' => $slug . '-install-pack',
                'shopify_product_id' => '',
                'image' => '/assets/images/manufacturers/' . $slug . '.jpg',
                'badge' => '',
            ],
        ];
    } else {
        foreach ($products as &$prod) {
            if (empty($prod['shopify_product_id'])) {
                $prod['shopify_product_id'] = '';
            }
            if (empty($prod['image'])) {
                $prod['image'] = '/assets/images/manufacturers/' . $slug . '.jpg';
            }
        }
        unset($prod);
    }
    $entry['products'] = $products;
    $updated++;
}
unset($entry);

$mfr['catalog'] = $catalog;
saveJsonData('manufacturers', $mfr);
echo "SEO-enriched manufacturers: {$updated}\n";
