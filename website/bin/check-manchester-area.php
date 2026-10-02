<?php
/**
 * Render the owned Manchester hub and service landings.
 * Usage: php website/bin/check-manchester-area.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/router.php';
require_once __DIR__ . '/../includes/area-manchester.php';

$fail = 0;
$pass = 0;
$lines = [];
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail, &$lines): void {
    if ($cond) {
        $pass++;
        $lines[] = "[PASS] {$msg}";
    } else {
        $fail++;
        $lines[] = "[FAIL] {$msg}";
    }
};

$owned = manchesterOwnedLandings();
$services = getServices();
foreach (['fire', 'local'] as $group) {
    foreach (array_keys($owned[$group]) as $slug) {
        $ok(isset($services[$slug]), "service exists: {$slug}");
        $file = SITE_ROOT . '/pages/areas/manchester/' . $slug . '.php';
        $ok(is_file($file), "landing file: {$slug}");
        $ok(manchesterLandingRecord($slug)['group'] === $group, "group {$group}: {$slug}");
    }
}
$ok(is_file(SITE_ROOT . '/pages/areas/manchester.php'), 'owned hub file');
$ok(str_contains((string)file_get_contents(SITE_ROOT . '/pages/areas/manchester.php'), 'OWNED AREA HUB'), 'hub marked owned');
$ok(in_array('Burnley', getAreas(), true), 'Burnley remains in the area list');

$render = static function (string $path): string {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['QUERY_STRING'] = '';
    http_response_code(200);
    ob_start();
    routerHandleRequest();
    return (string)ob_get_clean();
};

$hub = $render('/pages/areas/manchester');
$ok(str_contains($hub, '<h1'), 'hub has h1');
$ok(str_contains($hub, 'UK mainland'), 'hub states UK mainland fire');
$ok(str_contains($hub, 'Burnley'), 'hub names Burnley');
$ok(str_contains($hub, '/pages/areas/burnley'), 'hub links Burnley hub');
$ok(str_contains($hub, '/pages/areas/manchester/fire-alarms'), 'hub links fire landing');
$ok(str_contains($hub, '/pages/areas/manchester/electrical'), 'hub links local landing');
$ok(!preg_match('/£\s*\d/', $hub), 'hub does not invent a numeric price');
$ok(!str_contains($hub, '<?php'), 'hub does not leak PHP');

$fire = $render('/pages/areas/manchester/fire-alarms');
$ok(str_contains($fire, 'Fire Alarms in'), 'fire landing h1');
$ok(str_contains($fire, 'UK mainland'), 'fire landing nationwide story');
$ok(str_contains($fire, '/pages/areas/burnley'), 'fire landing links Burnley');
$ok(!preg_match('/£\s*\d/', $fire), 'fire landing has no numeric price');

$local = $render('/pages/areas/manchester/electrical');
$ok(str_contains($local, 'Electrical in'), 'electrical landing h1');
$ok(str_contains($local, 'local Manchester'), 'electrical landing is local');
$ok(str_contains($local, '/pages/areas/burnley'), 'electrical landing links Burnley');
$ok(!preg_match('#/pages/electrical/manchester#', $local), 'no thin service×town URL');

$stockport = $render('/pages/areas/stockport');
$ok(str_contains($stockport, 'Stockport'), 'generic Stockport hub still renders');
$ok(!str_contains($stockport, 'OWNED AREA HUB'), 'Stockport is not the Manchester owned hub');

echo implode("\n", $lines) . "\n";
echo "pass={$pass} fail={$fail}\n";
exit($fail === 0 ? 0 : 1);
