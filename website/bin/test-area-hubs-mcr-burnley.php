#!/usr/bin/env php
<?php
/**
 * Manchester and Burnley area indexes list every service.
 * Fire links resolve on national hubs (any UK place), not town doorways.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/render.php';

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

$services = getServices();
$fire = getFireSafetyServiceSlugs();
$ok(count($services) >= 50, 'services=' . count($services));
$ok(count($fire) >= 10, 'fire services=' . count($fire));
$ok(isFeaturedAreaIndexHub('Manchester') && isFeaturedAreaIndexHub('burnley'), 'featured index hubs');
$ok(!isFeaturedAreaIndexHub('Stockport'), 'Stockport stays on the generic area hub');

$london = fireNationwideServiceUrl('fire-alarms', 'London');
$ok(str_contains($london, '/pages/services/fire-alarms'), 'London fire link stays on the national hub');
$ok(str_contains($london, '#uk-london'), 'London is a fragment, not a town path');
$ok(!preg_match('#/pages/fire-alarms/london#', $london), 'no /pages/fire-alarms/london doorway');
$ok(!in_array('London', getAreas(), true), 'London is outside the North West areas list');

$birmingham = fireNationwideServiceUrl('emergency-lighting', 'Birmingham');
$ok(str_contains($birmingham, '/pages/services/emergency-lighting#uk-birmingham'), 'Birmingham emergency lighting is nationwide-capable');

foreach (['Manchester', 'Burnley'] as $area) {
    ob_start();
    renderAreaHubPage($area);
    $html = (string)ob_get_clean();
    $slug = areaSlug($area);
    $ok(stripos($html, '<!DOCTYPE') !== false && !str_contains($html, '<?php'), "{$area} renders HTML");
    $ok(stripos($html, $area) !== false, "{$area} names the town");
    $ok(stripos($html, 'UK-wide') !== false, "{$area} says fire links are UK-wide");
    $ok(str_contains($html, 'id="services"'), "{$area} has a service index");

    foreach ($services as $svcSlug => $name) {
        $ok(str_contains($html, $name), "{$area} lists {$name}");
        if (isFireSafetyService($svcSlug)) {
            $hub = '/pages/services/' . $svcSlug;
            $ok(str_contains($html, $hub), "{$area} fire link {$svcSlug} → national hub");
            $ok(!preg_match('#/pages/' . preg_quote($svcSlug, '#') . '/' . preg_quote($slug, '#') . '#', $html), "{$area} has no /pages/{$svcSlug}/{$slug} doorway");
        }
    }

    $ok(str_contains($html, '/pages/keywords/rewire/' . $slug), "{$area} keeps local rewire link");
    $ok(!preg_match('#/pages/keywords/fire-alarm-service/' . preg_quote($slug, '#') . '#', $html), "{$area} fire-alarm-service keyword is not town-locked");
    $ok(str_contains($html, '/pages/keywords/fire-alarm-service'), "{$area} links the national fire-alarm-service guide");
    $ok(str_contains($html, '#uk-london'), "{$area} includes a London fire fragment");
    $ok(str_contains($html, '#uk-glasgow'), "{$area} includes a Glasgow fire fragment");
    $body = $html;
    $contentStart = strpos($html, 'id="main-content"');
    $contentEnd = strpos($html, '<footer');
    if ($contentStart !== false && $contentEnd !== false && $contentEnd > $contentStart) {
        $body = substr($html, $contentStart, $contentEnd - $contentStart);
    }
    $ok(!preg_match('/£\s*\d/', $body), "{$area} has no invented £ price");
}

ob_start();
renderAreaHubPage('Stockport');
$stockport = (string)ob_get_clean();
$stockportDoorways = [];
if (preg_match_all('#/pages/(gas-systems|electrical|fire-alarms)/([a-z0-9\-]+)#', $stockport, $doorwayHits, PREG_SET_ORDER)) {
    foreach ($doorwayHits as $hit) {
        $doorway = '/pages/' . $hit[1] . '/' . $hit[2];
        if (!function_exists('icomplyPathIsIndexable') || !icomplyPathIsIndexable($doorway)) {
            $stockportDoorways[] = $doorway;
        }
    }
}
$ok($stockportDoorways === [], 'Stockport area hub service links are indexable tier-1 pages' . ($stockportDoorways ? ' (' . implode(',', $stockportDoorways) . ')' : ''));
$ok(!str_contains($stockport, 'id="fire-uk"'), 'Stockport does not use the featured index template');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
