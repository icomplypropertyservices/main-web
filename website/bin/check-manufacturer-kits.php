<?php
/**
 * Manufacturer hub kit deep links: real catalogue handles, no placeholder prices.
 * Usage: php bin/check-manufacturer-kits.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/manufacturer-kits.php';
require_once SITE_ROOT . '/includes/render.php';

$fail = 0;
$check = static function (bool $ok, string $label) use (&$fail): void {
    echo ($ok ? 'OK   ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
};

$check(strpos((string)file_get_contents(SITE_ROOT . '/includes/shopify.php'), 'PLACEHOLDER_DO_NOT_KEEP') === false, 'shopify.php is not a placeholder');
$check(function_exists('shopifyProductCardHtml'), 'shopifyProductCardHtml exists');
$check(function_exists('manufacturerCatalogueKits'), 'manufacturerCatalogueKits exists');

$worcester = manufacturerCatalogueKits('worcester-bosch', 12);
$handles = array_map(static fn(array $p): string => (string)($p['handle'] ?? ''), $worcester);
$check(in_array('worcester-greenstar-4000-combi', $handles, true), 'Worcester deep-links Greenstar 4000 combi');
$check(in_array('worcester-condensfit-ii-horizontal-60-100', $handles, true), 'Worcester deep-links Condensfit flue');
foreach ($handles as $handle) {
    if (preg_match('/-product-\d+$/', $handle) || str_contains($handle, 'service-kit')) {
        $check(false, 'Worcester handle is a template placeholder: ' . $handle);
    }
}
foreach ($worcester as $kit) {
    $price = (string)($kit['price'] ?? '');
    if (stripos($price, 'From £') === 0) {
        $check(false, 'Worcester kit uses a From £ placeholder: ' . $price);
    }
}
$check($worcester !== [], 'Worcester has catalogue kits');

$vaillant = array_map(static fn(array $p): string => (string)($p['handle'] ?? ''), manufacturerCatalogueKits('vaillant', 12));
$check(in_array('vaillant-ecotec-plus-combi', $vaillant, true), 'Vaillant deep-links ecoTEC plus combi');

$kentec = manufacturerCatalogueKits('kentec', 8);
$check($kentec === [], 'Kentec has no invented catalogue kits');

$paxton = manufacturerPageKits('paxton', 8);
$paxtonHrefs = array_map(static fn(array $p): string => shopifyProductUrl($p), $paxton);
$check((bool)array_filter($paxtonHrefs, static fn(string $h): bool => str_contains($h, '#barrier-packs')), 'Paxton deep-links barrier pack anchor');

$aov = manufacturerPageKits('ventilux', 8);
$aovHrefs = array_map(static fn(array $p): string => shopifyProductUrl($p), $aov);
$check((bool)array_filter($aovHrefs, static fn(string $h): bool => str_contains($h, '#aov-kits')), 'Ventilux deep-links AOV kit anchor');

$index = manufacturerCatalogueKitIndex();
$check(isset($index['worcester-bosch'], $index['baxi'], $index['ideal']), 'Hub index includes boiler brands with catalogue SKUs');
$check(!isset($index['kentec']), 'Hub index omits brands with no SKU');
$check(getManufacturerBySlug('tunstall') === null, 'Tunstall is not in the catalogue');
$check(getManufacturerBySlug('tubstall') === null, 'Tubstall is not in the catalogue');
$came = getManufacturerBySlug('came');
$check($came !== null && !empty($came['partner']) && !empty($came['barrier']), 'CAME is the barrier partner');
$cameLines = array_map(static fn(array $l): string => $l['name'], manufacturerProductLines($came ?? []));
$check(in_array('GARD GT4', $cameLines, true), 'CAME product line GARD GT4');
$check(manufacturerLogoHtml('CAME', 'came') !== '', 'CAME has a logo mark');
$check(count(manufacturerAreaRoutes()) === count(getManufacturerCatalog()) * count(getAreas()), 'Manufacturer × every area routes are prepared');

ob_start();
renderManufacturerPage('worcester-bosch');
$html = (string)ob_get_clean();
$check(str_contains($html, '/products/product/worcester-greenstar-4000-combi'), 'Worcester page HTML deep-links Greenstar PDP');
$check(!str_contains($html, 'From £'), 'Worcester page HTML has no From £ placeholder');
$check(str_contains($html, 'Installation is POA') || str_contains($html, 'install POA') || str_contains($html, 'Installation is POA.'), 'Worcester page states install POA');

ob_start();
renderManufacturerPage('kentec');
$kentecHtml = (string)ob_get_clean();
$check(str_contains($kentecHtml, 'quoted to order'), 'Kentec page is quote-only');
$check(!str_contains($kentecHtml, 'From £'), 'Kentec page HTML has no From £ placeholder');
$check(!str_contains($kentecHtml, 'kentec-product-1'), 'Kentec page does not link the template product id');

ob_start();
require SITE_ROOT . '/pages/manufacturers/index.php';
$hub = (string)ob_get_clean();
$check(str_contains($hub, 'Trade kits with deep links'), 'Hub has kit section');
$check(str_contains($hub, '/products/product/worcester-greenstar-2000-combi') || str_contains($hub, '/products/product/worcester-greenstar-4000-combi'), 'Hub deep-links a Worcester PDP');
$check(str_contains($hub, 'id="mfr-search"'), 'Hub has brand search');
$check(str_contains($hub, 'Barrier manufacturers'), 'Hub features barrier manufacturers');
$check(str_contains($hub, 'Partner'), 'Hub marks the CAME partner');
$check(stripos($hub, 'Tunstall') === false, 'Hub HTML does not name Tunstall');
$check(!str_contains($hub, 'From £'), 'Hub HTML has no From £ placeholder');

ob_start();
renderManufacturerAreaPage('came', 'manchester');
$areaHtml = (string)ob_get_clean();
$check(str_contains($areaHtml, 'CAME') && str_contains($areaHtml, 'Manchester'), 'CAME × Manchester page renders');
$check(str_contains($areaHtml, 'GARD GT4'), 'Area page lists a CAME product line');
$check(str_contains($areaHtml, '#barrier-packs'), 'CAME product line deep-links barrier packs');
$check(stripos($areaHtml, 'Tunstall') === false, 'Area page does not name Tunstall');

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
