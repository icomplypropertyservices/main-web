<?php
/**
 * Ensure every brand in manufacturers by_service has:
 *  - a catalog entry
 *  - a pages/manufacturers/{slug}.php stub
 *  - images_by_service includes real slugs
 * Regenerates manufacturer pages + prints inventory.
 */
require_once __DIR__ . '/../config.php';

$mfr = loadJsonData('manufacturers', []);
$byService = $mfr['by_service'] ?? [];
$catalog = $mfr['catalog'] ?? [];

$createdCatalog = 0;
$updatedServices = 0;

foreach ($byService as $service => $names) {
    foreach ($names as $name) {
        $name = trim((string)$name);
        if ($name === '') {
            continue;
        }
        $slug = areaSlug($name);
        if ($slug === '') {
            continue;
        }
        if (!isset($catalog[$slug])) {
            $catalog[$slug] = [
                'name' => $name,
                'slug' => $slug,
                'services' => [$service],
                'blurb' => "Icomply Property Services installs, services and supplies {$name} equipment across Greater Manchester and the North West. Trade kits, planned maintenance and fixed-price install quotes from our Stockport base.",
                'seo_title' => "{$name} Installation, Service & Products | North West",
                'seo_desc' => "Buy and install {$name} systems with Icomply. Installation, servicing, certification and trade products across the North West.",
                'seo_keywords' => "{$name}, {$name} installation, {$name} service, {$name} spares, {$name} North West, {$name} Stockport",
                'products' => [
                    [
                        'id' => $slug . '-service-kit',
                        'title' => "{$name} Service & Spares Kit",
                        'blurb' => "Engineer-ready consumables for {$name} systems.",
                        'price' => 'From £49',
                        'handle' => $slug . '-service-kit',
                        'shopify_product_id' => '',
                        'image' => '/assets/images/manufacturers/' . $slug . '.jpg',
                        'badge' => 'Trade',
                    ],
                    [
                        'id' => $slug . '-install-pack',
                        'title' => "{$name} Install Accessory Pack",
                        'blurb' => "Mounting and install accessories for {$name} projects.",
                        'price' => 'From £35',
                        'handle' => $slug . '-install-pack',
                        'shopify_product_id' => '',
                        'image' => '/assets/images/manufacturers/' . $slug . '.jpg',
                        'badge' => '',
                    ],
                ],
                'featured' => false,
            ];
            $createdCatalog++;
        } else {
            // Keep name/services in sync
            $catalog[$slug]['name'] = $name;
            $catalog[$slug]['slug'] = $slug;
            if (!isset($catalog[$slug]['services']) || !is_array($catalog[$slug]['services'])) {
                $catalog[$slug]['services'] = [];
            }
            if (!in_array($service, $catalog[$slug]['services'], true)) {
                $catalog[$slug]['services'][] = $service;
                $updatedServices++;
            }
            if (empty($catalog[$slug]['products'])) {
                $catalog[$slug]['products'] = [
                    [
                        'id' => $slug . '-service-kit',
                        'title' => "{$name} Service & Spares Kit",
                        'blurb' => "Engineer-ready consumables for {$name} systems.",
                        'price' => 'From £49',
                        'handle' => $slug . '-service-kit',
                        'shopify_product_id' => '',
                        'image' => '/assets/images/manufacturers/' . $slug . '.jpg',
                        'badge' => 'Trade',
                    ],
                ];
            }
        }
    }
}

// images_by_service: all brand slugs for each service (linked cards)
$imagesByService = $mfr['images_by_service'] ?? [];
foreach ($byService as $service => $names) {
    $slugs = [];
    foreach ($names as $name) {
        $slugs[] = areaSlug((string)$name);
    }
    $imagesByService[$service] = array_values(array_unique(array_filter($slugs)));
}

// Featured: keep existing or set popular brands
$featuredNames = [
    'Kentec', 'Advanced Electronics', 'C-Tec', 'Hochiki', 'Apollo',
    'Hikvision', 'Axis Communications', 'Paxton', 'Salto Systems',
    'Schneider Electric', 'Hager', 'Myenergi', 'Rolec EV',
    'Texecom', 'Worcester Bosch', 'Vaillant', 'Videx', 'Aiphone',
    'Emergi-Lite', 'SE Controls', 'Courtney Thorne', 'Honeywell',
];
foreach ($featuredNames as $fn) {
    $fs = areaSlug($fn);
    if (isset($catalog[$fs])) {
        $catalog[$fs]['featured'] = true;
    }
}

uasort($catalog, fn($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));

$mfr['catalog'] = $catalog;
$mfr['images_by_service'] = $imagesByService;
$mfr['by_service'] = $byService;
saveJsonData('manufacturers', $mfr);

// Generate stubs
$dir = SITE_ROOT . '/pages/manufacturers';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$stubs = 0;
foreach ($catalog as $slug => $entry) {
    $path = $dir . '/' . $slug . '.php';
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/sync-manufacturers.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderManufacturerPage({$slugExport});\n";
    file_put_contents($path, $stub);
    $stubs++;
}

// Preserve handcrafted index
$indexPath = $dir . '/index.php';
if (!is_file($indexPath) || strpos((string)file_get_contents($indexPath), 'MANUFACTURERS_INDEX') === false) {
    // leave existing if present; don't overwrite handcrafted
}

echo "Manufacturer sync complete\n";
echo "  Catalog entries: " . count($catalog) . "\n";
echo "  New catalog rows: {$createdCatalog}\n";
echo "  Service links added: {$updatedServices}\n";
echo "  Page stubs written: {$stubs}\n";
echo "  Brands per service:\n";
foreach ($byService as $svc => $names) {
    echo '    ' . $svc . ': ' . count($names) . "\n";
}
