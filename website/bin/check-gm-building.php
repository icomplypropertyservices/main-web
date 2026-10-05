<?php
/**
 * Existing building trades have Greater Manchester town coverage and POA hub copy.
 * Usage: php website/bin/check-gm-building.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
putenv('SITE_URL=https://icomplypropertyservices.co.uk');
$_ENV['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/matrix-catalogue.php';

$fail = 0;
$bad = static function (string $message) use (&$fail): void {
    echo "FAIL: {$message}\n";
    $fail++;
};

$services = getServices();
$areas = array_fill_keys(getAreas(), true);
$categories = getServiceCategories();
$construction = $categories['construction']['services'] ?? [];
if (count($construction) < 20) {
    $bad('construction category is shorter than the existing trade list');
}

$gm = icomplyGreaterManchesterTownNames();
$boroughs = icomplyGreaterManchesterBoroughs();
if (count($boroughs) !== 10) {
    $bad('expected 10 Greater Manchester boroughs, got ' . count($boroughs));
}
if (count($gm) < 58) {
    $bad('expected the existing Greater Manchester towns, got ' . count($gm));
}
foreach ($gm as $name) {
    if (!isset($areas[$name])) {
        $bad("GM town missing from areas.json: {$name}");
    }
}
if (in_array('Burnley', $gm, true)) {
    $bad('Burnley is outside Greater Manchester and should not be in the GM list');
}

$selected = icomplyMatrixSelectPlaces(500)['selected'];
foreach ($gm as $name) {
    $slug = icomplyMatrixSlug($name);
    if (!isset($selected[$slug])) {
        $bad("GM town missing from matrix places: {$name}");
    }
}
foreach (array_keys($services) as $slug) {
    if (in_array($slug, icomplyMatrixExcludedServiceSlugs(), true)) {
        continue;
    }
    foreach (['stockport', 'trafford', 'tameside', 'ashton-under-lyne'] as $town) {
        if (!isset($selected[$town])) {
            $bad("matrix place missing: {$town}");
            break;
        }
    }
    break;
}

$copySlugs = icomplyBuildingWorkSlugs();
foreach ($construction as $slug) {
    if (!isset($services[$slug])) {
        $bad("construction slug missing from services.json: {$slug}");
        continue;
    }
    if ($slug === 'heating') {
        $copy = icomplyServiceHubCopy($slug);
        if (!$copy) {
            $bad('heating hub copy missing');
        }
        continue;
    }
    if (!in_array($slug, $copySlugs, true)) {
        $bad("no building hub copy for existing trade: {$slug}");
        continue;
    }
    $copy = icomplyServiceHubCopy($slug);
    $blob = json_encode($copy);
    if (!is_string($blob) || !str_contains($blob, 'price on application') && !str_contains(strtolower($blob), 'poa')) {
        $bad("{$slug} copy is not POA");
    }
    if (is_string($blob) && str_contains($blob, '£')) {
        $bad("{$slug} copy contains a price");
    }
    if (is_string($blob) && str_contains($blob, 'fixed-price')) {
        $bad("{$slug} copy still says fixed-price");
    }
    $meta = getServiceMeta($slug);
    if (strtoupper((string)($meta['pricing'] ?? '')) !== 'POA') {
        $bad("{$slug} meta pricing is not POA");
    }
    $desc = (string)($meta['seo_desc'] ?? '');
    if (strlen($desc) < 40 || strlen($desc) > 180 || !str_contains($desc, 'Greater Manchester')) {
        $bad("{$slug} meta description is not a Greater Manchester POA line ({$desc})");
    }
}

foreach (['kitchens', 'bathrooms', 'plumbing', 'renovation', 'property-refurbishment-pm'] as $slug) {
    $copy = icomplyServiceHubCopy($slug);
    $blob = json_encode($copy);
    if (!is_string($blob) || !str_contains($blob, 'iComply is not Gas Safe registered.')) {
        $bad("{$slug} is missing the gas sentence");
    }
}

$renderSlugs = ['kitchens', 'plastering', 'carpentry', 'windows-doors', 'dry-lining'];
foreach ($renderSlugs as $slug) {
    $_SERVER['REQUEST_URI'] = '/pages/services/' . $slug;
    ob_start();
    include SITE_ROOT . '/pages/services/' . $slug . '.php';
    $html = (string)ob_get_clean();
    $path = '/tmp/icomply-' . $slug . '.html';
    file_put_contents($path, $html);
    if (!str_contains($html, 'Greater Manchester')) {
        $bad("{$slug} hub does not mention Greater Manchester");
    }
    if (!str_contains($html, '/pages/' . $slug . '/trafford') && !str_contains($html, '/pages/' . $slug . '/trafford.php')) {
        $bad("{$slug} hub does not link Trafford");
    }
    if (!str_contains($html, '/pages/' . $slug . '/tameside')) {
        $bad("{$slug} hub does not link Tameside");
    }
    if (!str_contains($html, '/pages/' . $slug . '/ashton-under-lyne')) {
        $bad("{$slug} hub does not link Ashton-under-Lyne");
    }
    if (str_contains($html, 'fixed-price')) {
        $bad("{$slug} hub still says fixed-price");
    }
    if (!str_contains(strtolower($html), 'price on application')) {
        $bad("{$slug} hub does not say price on application");
    }
    if (!str_contains($html, 'approved subcontractors')) {
        $bad("{$slug} hub missing subcontractor sentence");
    }
    echo "rendered {$slug} bytes=" . strlen($html) . "\n";
}

if ($fail === 0) {
    echo "PASS\n";
    exit(0);
}
echo "FAIL {$fail}\n";
exit(1);
