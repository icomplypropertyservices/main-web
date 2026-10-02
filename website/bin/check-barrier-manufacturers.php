<?php
/**
 * Assert barrier manufacturer pages: every brand, CAME partner framing, no Tunstall.
 * Usage: php bin/check-barrier-manufacturers.php
 */
require_once __DIR__ . '/../config.php';

$fail = 0;
function check(bool $ok, string $label): void
{
    global $fail;
    echo ($ok ? 'OK  ' : 'FAIL ') . $label . "\n";
    if (!$ok) {
        $fail++;
    }
}

$records = barrierManufacturerRecords();
$slugs = array_map(static fn($b) => (string)$b['slug'], $records);
check(count($records) >= 16, 'at least 16 barrier manufacturers (' . count($records) . ')');
check(in_array('came', $slugs, true), 'CAME is in the set');
check(!in_array('tunstall', $slugs, true), 'Tunstall is excluded');
foreach (['nice', 'faac', 'bft', 'hormann', 'magnetic', 'elka'] as $need) {
    check(in_array($need, $slugs, true), $need . ' present');
}

$came = barrierManufacturerBySlug('came');
check(is_array($came) && !empty($came['partner']), 'CAME partner flag');
check(is_array($came) && str_contains((string)($came['partner_image'] ?? ''), 'nav-came-line.jpg'), 'CAME uses partner image, not a generated logo');
check(barrierLogoUrl($came ?? []) === (string)($came['partner_image'] ?? ''), 'barrierLogoUrl returns the partner image');

$hub = barrierPagesBlockHtml('barriers', '', true);
check(str_contains($hub, 'Authorised CAME partner'), 'hub partner heading');
check(str_contains($hub, 'nav-came-line.jpg'), 'hub shows CAME partner image');
check(str_contains($hub, 'id="came-lane-builder"'), 'hub has CAME lane builder');
check(str_contains($hub, 'kit-wizard') === false || str_contains($hub, 'separate from the AOV'), 'lane builder does not load kit-wizard templates');
check(!str_contains($hub, 'accreditation number is'), 'no invented accreditation number');
check(str_contains($hub, 'We do not publish a partner or accreditation number'), 'states no published partner number');
check(!preg_match('/Tunstall/i', $hub) || str_contains($hub, 'Tunstall is not'), 'Tunstall is not listed as a barrier brand');
check(str_contains($hub, '£5,850.00'), 'published 5m standard price is present');
check(str_contains($hub, 'boom==="5m"'), 'price only when boom is 5m');
check(str_contains($hub, 'Quote after survey'), 'other lengths say quote after survey');

foreach ($records as $brand) {
    $slug = (string)$brand['slug'];
    $name = (string)$brand['name'];
    check(str_contains($hub, '/pages/manufacturers/' . $slug), $name . ' linked from barriers hub');
    $lines = $brand['lines'] ?? [];
    check(is_array($lines) && $lines !== [], $name . ' has product lines');
    if (!empty($brand['partner'])) {
        check(str_contains($hub, 'nav-came-line.jpg'), $name . ' partner image on hub');
    } else {
        $logo = SITE_ROOT . '/assets/images/manufacturers/' . $slug . '-logo.svg';
        check(is_file($logo), $name . ' wordmark file exists');
        check(str_contains($hub, $slug . '-logo.svg'), $name . ' wordmark on hub');
    }
}

$town = barrierPagesBlockHtml('barriers', 'Stockport', false);
check(str_contains($town, '/pages/manufacturers/faac/stockport'), 'Stockport barriers page links FAAC area');
check(str_contains($town, '/pages/manufacturers/came/stockport'), 'Stockport barriers page links CAME area');
check(!str_contains($town, 'id="came-lane-builder"'), 'town matrix block does not duplicate the lane builder');

$access = camePartnerPanelHtml('access-control');
check(str_contains($access, 'Authorised CAME partner'), 'access-control partner panel');
check(str_contains($access, 'nav-came-line.jpg'), 'access-control uses partner image');

$products = camePartnerPanelHtml('products');
check(str_contains($products, '5m packs'), 'products partner panel mentions published 5m packs');

$catalog = getManufacturerCatalog();
check(isset($catalog['came']['partner']) && $catalog['came']['partner'] === true, 'catalog marks CAME partner');
check(isset($catalog['faac']['product_lines']) && count($catalog['faac']['product_lines']) >= 2, 'FAAC product lines merged');
check(!isset($catalog['tunstall']['services']) || !in_array('barriers', $catalog['tunstall']['services'] ?? [], true), 'Tunstall catalog is not a barriers brand');

$area = barrierManufacturerAreaInnerHtml(barrierManufacturerBySlug('faac'), 'Stockport');
check(str_contains($area, 'FAAC barriers in Stockport'), 'FAAC Stockport area heading');
check(str_contains($area, 'B680H'), 'FAAC area lists a product line');
check(str_contains($area, 'Supply quoted after survey'), 'non-CAME area has no invented price');

$cameArea = barrierManufacturerAreaInnerHtml($came, 'Manchester');
check(str_contains($cameArea, 'came-gard-gt4.jpg'), 'CAME area uses Gard product photo');
check(str_contains($cameArea, 'authorised CAME partner'), 'CAME area partner copy');

$export = barrierManufacturerAreaExportHtml('nice', 'Bolton');
check(str_contains($export, 'Nice barriers in Bolton'), 'export HTML for Nice Bolton');

$phone = defined('PHONE') ? (string)PHONE : '';
check($phone === '07517806082', 'PHONE constant is 07517806082');
check(str_contains($hub, '07517806082'), 'hub shows the site phone');

if (!is_file(SITE_ROOT . '/includes/kit-wizard.php') && !is_file(SITE_ROOT . '/pages/kits/barriers.php')) {
    check(true, 'does not vendor the sibling kit-wizard barriers template');
} else {
    check(false, 'kit-wizard barriers template should stay on its own branch');
}

echo $fail === 0 ? "PASS\n" : "FAILS {$fail}\n";
exit($fail === 0 ? 0 : 1);
