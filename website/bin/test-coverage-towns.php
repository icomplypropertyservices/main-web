#!/usr/bin/env php
<?php
/**
 * Jack, Oct 2026: fire protection = UK mainland. Other services = Manchester + Burnley.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/local-content.php';
require_once __DIR__ . '/../includes/render.php';
require_once __DIR__ . '/../includes/sitemap.php';
require_once __DIR__ . '/../includes/matrix-page.php';

$fail = 0;
$pass = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        echo "[PASS] {$msg}\n";
    } else {
        $fail++;
        echo "[FAIL] {$msg}\n";
    }
};

$nonFire = icomplyNonFireCoverageTowns();
$ok($nonFire === ['Manchester', 'Burnley'], 'non-fire towns are Manchester and Burnley only');

$mainland = icomplyMainlandFireTowns();
$ok(in_array('Manchester', $mainland, true) && in_array('Burnley', $mainland, true), 'fire mainland includes Manchester and Burnley');
$ok(in_array('Birmingham', $mainland, true) && in_array('Cardiff', $mainland, true) && in_array('Edinburgh', $mainland, true), 'fire mainland includes England, Wales and mainland Scotland');
foreach (['Belfast', 'Derry', 'Inverness', 'Stornoway', 'Douglas', 'St Helier', 'Kirkwall'] as $excluded) {
    $ok(!in_array($excluded, $mainland, true), "excluded from mainland: {$excluded}");
}

$areas = getAreas();
$areaSlugs = [];
foreach ($areas as $area) {
    $areaSlugs[areaSlug((string)$area)] = (string)$area;
}
foreach ($mainland as $town) {
    $profile = area_profile($town);
    $districts = (string)($profile['districts'] ?? '');
    $ok($districts !== '' && !str_contains($districts, 'local postcodes'), "finished profile for {$town}");
    $ok(!str_contains((string)($profile['region'] ?? ''), 'Greater Manchester fringe'), "no generic region for {$town}");
    $slug = areaSlug($town);
    if (isset($areaSlugs[$slug])) {
        $ok($areaSlugs[$slug] === $town, "slug {$slug} matches directory name {$town}");
    } else {
        $ok(areaFromSlug($slug) === $town, "areaFromSlug resolves {$town}");
    }
}

foreach (['Nelson', 'Colne', 'Padiham', 'Accrington', 'Ashton-under-Lyne', 'Cheadle', 'Hyde'] as $neighbour) {
    $profile = area_profile($neighbour);
    $ok(!str_contains((string)($profile['districts'] ?? ''), 'local postcodes'), "finished incomplete neighbour {$neighbour}");
    $ok(in_array($neighbour, $areas, true), "{$neighbour} stays on the existing area list");
    $ok(!in_array($neighbour, $mainland, true), "{$neighbour} is not promoted onto the mainland sitemap list");
}

$ok(!icomplyIsFireProtectionService('electrical'), 'electrical is not fire protection');
$ok(!icomplyIsFireProtectionService('gas-systems'), 'gas is not fire protection');
$ok(!icomplyIsFireProtectionService('aov-air-handling'), 'AOV is not in the fire-protection coverage set');
$ok(icomplyIsFireProtectionService('fire-alarms'), 'fire alarms are fire protection');
$ok(icomplyIsFireProtectionService('emergency-lighting'), 'emergency lighting is fire protection');
$ok(icomplyTownsForService('cctv') === ['Manchester', 'Burnley'], 'CCTV towns are Manchester and Burnley');
$ok(icomplyTownsForService('fire-alarms') === $mainland, 'fire alarm towns are the mainland list');

$featured = getFireProtectionFeaturedKeywordSlugs();
$ok(in_array('fire-alarm-installation', $featured, true), 'featured fire keyword fire-alarm-installation');
$ok(!in_array('rewire', $featured, true), 'rewire is not a fire featured keyword');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(str_contains($xml, '/pages/keywords/fire-alarm-installation/birmingham</loc>'), 'sitemap lists Birmingham fire alarms');
$ok(str_contains($xml, '/pages/keywords/emergency-lighting-test/cardiff</loc>'), 'sitemap lists Cardiff emergency lighting');
$ok(str_contains($xml, '/pages/keywords/rewire/manchester</loc>'), 'sitemap lists Manchester rewire');
$ok(str_contains($xml, '/pages/keywords/boiler/burnley</loc>'), 'sitemap lists Burnley boiler');
$ok(!str_contains($xml, '/pages/keywords/rewire/birmingham'), 'sitemap has no Birmingham rewire');
$ok(!str_contains($xml, '/pages/keywords/boiler/london'), 'sitemap has no London boiler');
$ok(!str_contains($xml, '/pages/fire-alarms/birmingham'), 'sitemap has no service×town fire doorway');
$ok(!str_contains($xml, '/pages/electrical/manchester'), 'sitemap has no service×town electrical doorway');

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'getFireProtectionFeaturedKeywordSlugs'), 'static-export adds featured fire keywords');
$ok(str_contains($exportSrc, 'icomplyMainlandFireTowns'), 'static-export walks the mainland fire town list');
$ok(!in_array('Birmingham', $areas, true), 'Birmingham is not on the North West area list, so non-fire routes cannot include it');
$ok(in_array('Stockport', $areas, true) && in_array('Manchester', $areas, true), 'existing North West matrix towns remain');
$fireRoute = '/pages/keywords/fire-alarm-installation/' . areaSlug('Birmingham');
$ok(in_array('fire-alarm-installation', $featured, true) && in_array('Birmingham', $mainland, true), 'Birmingham fire-alarm route is in the fire coverage set (' . $fireRoute . ')');

$html = icomplyRenderKeywordTownHtml('fire-alarm-installation', 'Birmingham');
$ok(str_contains($html, 'Birmingham'), 'Birmingham fire page names Birmingham');
$ok(str_contains($html, 'B1'), 'Birmingham fire page uses the real postcode districts');
$ok(str_contains($html, 'fire protection only'), 'Birmingham page says fire protection only');
$ok(str_contains($html, 'Manchester and Burnley'), 'Birmingham page points other services at Manchester and Burnley');
$ok(!str_contains($html, '/pages/areas/birmingham'), 'Birmingham page does not link a missing area hub');
$ok(!preg_match('/£\s*\d/', $html), 'Birmingham fire page has no invented £ price');
$ok(str_contains($html, 'Price on application') || str_contains($html, 'price on application') || str_contains($html, 'Written quote'), 'Birmingham page keeps POA / written-quote language');

$elec = icomplyRenderKeywordTownHtml('rewire', 'Stockport');
$ok(!str_contains($elec, '/pages/keywords/rewire/birmingham'), 'Stockport rewire page does not link Birmingham');

ob_start();
renderServiceHubPage('electrical');
$elecHub = (string)ob_get_clean();
$ok(!preg_match('#/pages/electrical/[a-z0-9\-]+#', $elecHub), 'electrical hub has no service×town URL');
$ok(str_contains($elecHub, '/pages/keywords/rewire/manchester'), 'electrical hub links Manchester');
$ok(str_contains($elecHub, '/pages/keywords/rewire/burnley'), 'electrical hub links Burnley');
$ok(!str_contains($elecHub, '/pages/keywords/rewire/birmingham'), 'electrical hub does not link Birmingham');

ob_start();
renderServiceHubPage('fire-alarms');
$fireHub = (string)ob_get_clean();
$ok(str_contains($fireHub, '/pages/keywords/fire-alarm-installation/birmingham'), 'fire hub links Birmingham keyword page');
$ok(!preg_match('#/pages/fire-alarms/[a-z0-9\-]+#', $fireHub), 'fire hub has no service×town URL');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
