<?php
/**
 * HMO job-lane pages: £561 bundle only where it is the offer, CTAs present.
 * Usage: php bin/check-hmo-jobs.php
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/hmo-jobs.php';

$fail = 0;
$say = static function (bool $ok, string $label) use (&$fail): void {
    echo ($ok ? 'OK   ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
};

$say(hmoJobFormatPence(hmoJobBundleListPence()) === '£561', 'list bundle is £561');
$say(hmoJobFormatPercent(0) === '0%' && hmoJobFormatPercent(12.5) === '12.5%', 'percent labels');
$tiers = hmoJobBundleTiers();
$expect = ['£561', '£533', '£505', '£491', '£477'];
$got = array_column($tiers, 'each');
$say($got === $expect, 'portfolio rounding ' . implode(', ', $got));

$ids = ['hmo', 'hmo-compliance', 'hmo-fire-safety', 'hmo-occupancy'];
foreach ($ids as $id) {
    $page = hmoJobPage($id);
    $file = SITE_ROOT . $page['file'];
    $say(is_file($file), 'file ' . $page['file']);
}

foreach ($ids as $id) {
    $page = hmoJobPage($id);
    $runner = '$_SERVER["REQUEST_METHOD"]="GET"; $_SERVER["HTTP_HOST"]="127.0.0.1"; $_SERVER["REQUEST_URI"]='
        . var_export($page['path'], true)
        . '; $_GET=[]; $_POST=[]; require ' . var_export(SITE_ROOT . $page['file'], true) . ';';
    $html = (string)shell_exec('php -r ' . escapeshellarg($runner) . ' 2>/dev/null');
    $say($html !== '', $id . ' rendered');
    $say(str_contains($html, '<h1'), $id . ' has h1');
    $say(str_contains($html, 'href="#quote"'), $id . ' quote CTA');
    $say(str_contains($html, 'tel:07517806082'), $id . ' call CTA');
    $say(str_contains($html, 'https://wa.me/447517806082'), $id . ' WhatsApp CTA');
    $say(str_contains($html, 'Not an HMO licence'), $id . ' licence disclaimer');
    $say(str_contains($html, 'not legal advice'), $id . ' legal disclaimer');
    $say(stripos($html, 'Icomply') === false || str_contains($html, 'iComply'), $id . ' uses iComply in new copy');
    $bundleAttr = substr_count($html, 'data-hmo-bundle="561"');
    if (!empty($page['primaryBundle'])) {
        $say($bundleAttr >= 1, $id . ' marks £561 offer');
        $say(str_contains($html, '"price":"561"') || str_contains($html, '"price": "561"'), $id . ' Offer price 561');
        $say(str_contains($html, '£533') && str_contains($html, '£477'), $id . ' portfolio prices');
    } elseif ($id !== 'hmo') {
        $say($bundleAttr === 0, $id . ' does not mark itself as the £561 offer');
    }
    if ($id === 'hmo') {
        $say($bundleAttr === 1, 'hub marks only the compliance card as £561');
        $say(str_contains($html, '£561'), 'hub mentions £561');
    }
    if ($id === 'hmo-fire-safety') {
        $say(str_contains($html, 'Is the fire pack £561?'), 'fire FAQ refuses £561 as the pack price');
        $say(str_contains($html, '£350'), 'fire page shows FRA £350');
    }
    if ($id === 'hmo-occupancy') {
        $say(str_contains($html, '£160') && str_contains($html, '£85'), 'occupancy shows EICR and gas list');
    }
}

require_once SITE_ROOT . '/includes/sitemap.php';
$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
foreach (['/pages/jobs/hmo</loc>', '/pages/jobs/hmo-compliance</loc>', '/pages/jobs/hmo-fire-safety</loc>', '/pages/jobs/hmo-occupancy</loc>'] as $loc) {
    $say(str_contains($xml, $loc), 'sitemap ' . $loc);
}

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
