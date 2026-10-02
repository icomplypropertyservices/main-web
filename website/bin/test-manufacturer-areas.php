<?php
/**
 * Manufacturer × area coverage, product lines, AOV/barriers wizards.
 * Usage: php website/bin/test-manufacturer-areas.php
 */
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';

$fail = 0;
$check = static function (bool $ok, string $msg) use (&$fail): void {
    if ($ok) {
        echo "OK   {$msg}\n";
        return;
    }
    echo "FAIL {$msg}\n";
    $fail++;
};

$catalog = getManufacturerCatalog();
$check(!isset($catalog['tunstall']), 'Tunstall removed from catalog');
$check(!in_array('Tunstall', getManufacturers('nurse-call'), true), 'Tunstall removed from nurse-call list');
$check(!isset(getMajorKeywords()['tunstall-nurse-call']), 'Tunstall keyword hub excluded');

$came = $catalog['came'] ?? null;
$check(is_array($came), 'CAME catalog entry exists');
$check(is_array($came) && in_array('barriers', $came['services'], true), 'CAME service is barriers');
$check(is_array($came) && manufacturerIsBarriersPartner($came), 'CAME is the barriers partner');
$check(is_array($came) && manufacturerWizardFamily($came) === 'barriers', 'CAME wizard family is barriers');

$se = $catalog['se-controls'] ?? null;
$check(is_array($se) && manufacturerWizardFamily($se) === 'aov', 'SE Controls wizard family is aov');

$wylex = $catalog['wylex'] ?? null;
$check(is_array($wylex) && manufacturerCoverageMode($wylex) === 'local', 'Wylex is Manchester and Burnley scoped');
if (is_array($wylex)) {
    $wAreas = manufacturerAreasFor($wylex);
    $check(in_array('Manchester', $wAreas, true) && in_array('Burnley', $wAreas, true), 'Wylex includes Manchester and Burnley');
    $check(!in_array('Liverpool', $wAreas, true) && !in_array('Carlisle', $wAreas, true), 'Wylex excludes Liverpool and Carlisle');
}

$kentec = $catalog['kentec'] ?? null;
$check(is_array($kentec) && manufacturerCoverageMode($kentec) === 'nationwide', 'Kentec is nationwide');
if (is_array($kentec)) {
    $kAreas = manufacturerAreasFor($kentec);
    $check(in_array('Liverpool', $kAreas, true) && in_array('Carlisle', $kAreas, true), 'Kentec includes Liverpool and Carlisle');
    $check(count($kAreas) === count(getAreas()), 'Kentec town count matches the published area list');
}

$missingLogos = 0;
$shortLines = 0;
foreach ($catalog as $entry) {
    $lines = manufacturerProductLines($entry);
    if (count($lines) < 3) {
        $shortLines++;
    }
    foreach ($lines as $line) {
        if (!is_file(SITE_ROOT . $line['logo'])) {
            $missingLogos++;
        }
    }
}
$check($shortLines === 0, 'every manufacturer has at least 3 product lines');
$check($missingLogos === 0, 'every product line has a logo file (' . $missingLogos . ' missing)');

function stripTowns(string $text, array $towns): string
{
    usort($towns, static fn($a, $b) => strlen($b) <=> strlen($a));
    foreach ($towns as $town) {
        $text = str_ireplace($town, '', $text);
    }
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
}

if (is_array($se)) {
    $towns = manufacturerAreasFor($se);
    $seen = [];
    $strippedDup = 0;
    foreach ($towns as $town) {
        $intro = manufacturerLocalIntro($se, $town);
        $seen[$intro] = true;
        $stripped = stripTowns($intro, $towns);
        if (isset($seen['s:' . $stripped])) {
            $strippedDup++;
        }
        $seen['s:' . $stripped] = $town;
    }
    $check(count(array_filter(array_keys($seen), static fn($k) => !str_starts_with((string)$k, 's:'))) === count($towns), 'SE Controls intros differ for every town');
    $check($strippedDup === 0, 'SE Controls intros still differ after town names are removed');
}

if (is_array($came) && is_array($kentec)) {
    $a = stripTowns(manufacturerLocalIntro($came, 'Manchester'), getAreas());
    $b = stripTowns(manufacturerLocalIntro($kentec, 'Manchester'), getAreas());
    $check($a !== $b, 'CAME and Kentec Manchester intros are not the same skeleton');
    $check(str_contains(manufacturerLocalIntro($came, 'Burnley'), 'barriers partner'), 'CAME intro names the barriers partnership');
}

$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$render = static function (string $slug, string $area) {
    ob_start();
    renderManufacturerAreaPage($slug, areaSlug($area));
    return (string)ob_get_clean();
};

$aovHtml = $render('se-controls', 'Manchester');
$barHtml = $render('came', 'Burnley');
$localHtml = $render('wylex', 'Stockport');
$blocked = $render('wylex', 'Liverpool');

$check(str_contains($aovHtml, 'mfr-wizard') && str_contains($aovHtml, 'mfr-line-card'), 'AOV page has wizard and product-line cards');
$check(str_contains($aovHtml, 'id="aov-kits"') || str_contains($aovHtml, '#aov-kits'), 'AOV wizard links to products hub kits');
$check(!str_contains($aovHtml, 'product-card') && !str_contains($aovHtml, 'aov-kit-prices'), 'AOV page does not reuse shop or kit-strip card markup');
$check(str_contains($barHtml, 'data-family="barriers"') && str_contains($barHtml, 'mfr-partner-banner'), 'Barriers page has the CAME partner wizard');
$check(str_contains($barHtml, '#barrier-packs'), 'Barriers wizard links to barrier packs');
$check(!str_contains($barHtml, 'class="product-card"'), 'Barriers page does not use shop product-card');
$check(str_contains($localHtml, 'mfr-link-panel') && !str_contains($localHtml, 'data-mfr-wizard'), 'Scoped brands get links, not the AOV wizard');
$check(str_contains($blocked, 'Area not found'), 'Out-of-scope town returns not found');

foreach (['se-controls' => $aovHtml, 'came' => $barHtml, 'wylex' => $localHtml] as $name => $html) {
    $check((bool)preg_match('/<title>([^<]{15,70})<\/title>/', $html), $name . ' title length');
    $check((bool)preg_match('/name="description" content="([^"]{70,165})"/', $html), $name . ' meta length');
    $check(substr_count($html, '<img') >= 4, $name . ' has several images');
}

echo $fail === 0 ? "\nMANUFACTURER AREA TESTS PASSED\n" : "\n{$fail} FAILURES\n";
exit($fail > 0 ? 1 : 0);
