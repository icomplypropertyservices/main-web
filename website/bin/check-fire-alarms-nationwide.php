#!/usr/bin/env php
<?php
/**
 * Fire alarms own every UK mainland area. Other services stay North West.
 * Not a production deploy.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$root = dirname(__DIR__);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once $root . '/config.php';
require_once $root . '/includes/router.php';
require_once $root . '/includes/matrix-page.php';
require_once $root . '/includes/local-content.php';

$pass = 0;
$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        echo "[PASS] {$msg}\n";
        return;
    }
    $fail++;
    echo "[FAIL] {$msg}\n";
};

$nw = getAreas();
$mainland = getMainlandAreas();
$records = getMainlandAreaRecords();
$ok(count($mainland) === count($records), 'mainland names match records (' . count($mainland) . ')');
$ok(count($mainland) > count($nw), 'mainland ' . count($mainland) . ' is larger than North West ' . count($nw));
$ok(count($nw) >= 160, 'North West list unchanged size ' . count($nw));

$missingNw = array_values(array_diff($nw, $mainland));
$ok($missingNw === [], 'every North West town is on the mainland list');

$slugs = [];
$dup = '';
foreach ($mainland as $name) {
    $slug = areaSlug($name);
    if (isset($slugs[$slug])) {
        $dup = $slug;
        break;
    }
    $slugs[$slug] = $name;
}
$ok($dup === '', 'mainland slugs are unique' . ($dup !== '' ? " (dup {$dup})" : ''));

$banned = [
    'Belfast', 'Derry', 'Londonderry', 'Lisburn', 'Newry', 'Armagh',
    'Douglas', 'St Helier', 'St Peter Port', 'Jersey', 'Guernsey',
    'Lerwick', 'Kirkwall', 'Stornoway', 'Portree', 'Holyhead',
    'Inverness', 'Fort William', 'Thurso', 'Wick', 'Oban', 'Ullapool',
    'Elgin', 'Peterhead', 'Fraserburgh', 'Dunoon', 'Campbeltown',
    'Ryde', 'Cowes',
];
$bannedHit = array_values(array_intersect($banned, $mainland));
$ok($bannedHit === [], 'quote-only places are absent' . ($bannedHit ? ': ' . implode(', ', $bannedHit) : ''));

$nations = [];
foreach ($records as $row) {
    $nations[(string)($row['nation'] ?? '')] = true;
}
$ok(isset($nations['England'], $nations['Wales'], $nations['Scotland']), 'England, Wales and Scotland are all present');

$ok(serviceOwnsMainlandAreas('fire-alarms'), 'fire-alarms owns mainland areas');
$ok(!serviceOwnsMainlandAreas('electrical'), 'electrical does not own mainland areas');
$ok(serviceOwnsMainlandAreas('emergency-lighting'), 'emergency lighting is a fire-family nationwide town service');
$ok(count(getAreasForService('fire-alarms')) === count($mainland), 'fire-alarms area count is the mainland list');
$ok(count(getAreasForService('electrical')) === count($nw), 'electrical area count is the North West list');

foreach (['London', 'Birmingham', 'Cardiff', 'Edinburgh', 'Glasgow', 'Stockport', 'Aberdeen'] as $need) {
    $ok(in_array($need, $mainland, true), "{$need} is a mainland fire-alarm area");
}

$london = area_profile('London');
$ok(($london['region'] ?? '') === 'Greater London', 'London region is Greater London');
$ok(str_contains((string)($london['travel'] ?? ''), 'scheduled'), 'London travel is scheduled, not a fake drive time');
$ok(!str_contains((string)($london['travel'] ?? ''), 'minutes'), 'London travel does not invent minutes from Stockport');
$stockport = area_profile('Stockport');
$ok(str_contains((string)($stockport['districts'] ?? ''), 'SK'), 'Stockport keeps its local profile');

$ok(str_contains(exportedServiceLocalUrl('fire-alarms', 'London'), '/pages/fire-alarms/london'), 'London fire-alarm URL is the service page');
$ok(!str_contains(exportedServiceLocalUrl('electrical', 'Manchester'), '/pages/electrical/'), 'electrical local URL stays off the service×town path');

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(8));
}

$matrix = icomplyRenderServiceAreaHtml('fire-alarms', 'London');
$ok(str_contains($matrix, 'London · UK mainland'), 'export HTML marks London as UK mainland');
$ok(str_contains($matrix, 'href="' . url('/pages/fire-alarms/cardiff')), 'export HTML links Cardiff fire alarms');
$ok(str_contains($matrix, 'href="' . url('/pages/fire-alarms/edinburgh')), 'export HTML links Edinburgh fire alarms');
$ok(!str_contains($matrix, 'belfast'), 'export HTML does not list Belfast');
$ok(!str_contains($matrix, 'inverness'), 'export HTML does not list Inverness');
$ok(str_contains($matrix, (string)count($mainland) . ' towns'), 'export HTML counts every mainland town');
$ok(!preg_match('/£\s*\d/', $matrix), 'London fire-alarm export has no invented £ price');

$elec = icomplyRenderServiceAreaHtml('electrical', 'London');
$ok($elec === '', 'electrical has no London page');

ob_start();
$londonOk = routerDispatchVirtual('/pages/fire-alarms/london');
$londonHtml = (string)ob_get_clean();
$ok($londonOk && str_contains($londonHtml, 'Fire Alarms'), 'router renders fire alarms in London');
$ok(str_contains($londonHtml, 'UK mainland'), 'router London page says UK mainland');
$ok(str_contains($londonHtml, 'Westminster') || str_contains($londonHtml, 'Croydon'), 'London page links other Greater London places');

ob_start();
$belfast = routerDispatchVirtual('/pages/fire-alarms/belfast');
ob_end_clean();
$ok($belfast === false, 'Belfast fire alarms 404');

ob_start();
$elecLondon = routerDispatchVirtual('/pages/electrical/london');
ob_end_clean();
$ok(
    $elecLondon === true
        && icomplyNonGmMatrixRedirect('/pages/electrical/london') === '/pages/services/electrical',
    'electrical London 301s to the service hub'
);

ob_start();
$stockportOk = routerDispatchVirtual('/pages/fire-alarms/stockport');
$stockportHtml = (string)ob_get_clean();
$ok($stockportOk && str_contains($stockportHtml, 'Stockport'), 'Stockport fire alarms still render');

ob_start();
renderServiceHubPage('fire-alarms');
$hub = (string)ob_get_clean();
$ok(str_contains($hub, 'UK mainland fire protection'), 'fire alarm hub title is nationwide');
$ok(str_contains($hub, '/pages/fire-alarms/london'), 'hub links the London fire alarm page');
$ok(str_contains($hub, '/pages/fire-alarms/cardiff'), 'hub links the Cardiff fire alarm page');
$ok(substr_count($hub, '/pages/fire-alarms/') >= count($mainland), 'hub lists a fire alarm link per mainland area');
$ok(!str_contains($hub, 'belfast'), 'hub does not list Belfast');
$ok(!preg_match('/£\s*\d/', $hub), 'fire alarm hub has no invented £ price');

echo "mainland=" . count($mainland) . " north_west=" . count($nw) . "\n";
echo ($fail === 0 ? "PASS" : "FAIL") . " ({$pass} pass, {$fail} fail)\n";
exit($fail === 0 ? 0 : 1);
