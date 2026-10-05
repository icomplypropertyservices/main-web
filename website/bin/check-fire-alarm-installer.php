<?php
/**
 * Fire-alarm-installer family: 44 nationwide hubs, P0 keyword×town on uk-mainland-towns-10k.
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

$hubs = icomplyFireAlarmInstallerHubSlugs();
$p0 = icomplyFireAlarmInstallerP0Slugs();
$keywords = getMajorKeywords();
$ok(count($hubs) === 44, 'hub list is 44');
$ok(count($p0) === 16, 'P0 list is 16');
$ok(count(array_intersect($p0, $hubs)) === 16, 'every P0 slug is a hub');

$created = 0;
$existing = [
    'domestic-fire-alarm-installation',
    'emergency-fire-alarm',
    'factory-fire-alarm-system',
    'fire-alarm-call-point-installation',
    'fire-alarm-engineer',
    'fire-alarm-engineer-near-me',
    'fire-alarm-engineers-near-me',
    'fire-alarm-installation',
    'fire-alarm-installation-certificate',
    'fire-alarm-strobe-installation',
    'office-fire-alarm-installation',
];
foreach ($hubs as $slug) {
    $ok(isset($keywords[$slug]), "{$slug} is in keywords.json");
    if (!isset($keywords[$slug])) {
        continue;
    }
    $row = $keywords[$slug];
    $ok(($row['service'] ?? '') === 'fire-alarms', "{$slug} service is fire-alarms");
    $ok(trim((string)($row['intro'] ?? '')) !== '', "{$slug} has an intro");
    if (in_array($slug, $p0, true)) {
        $ok(empty($row['hub_only']), "{$slug} is not hub-only");
    } else {
        $ok(!empty($row['hub_only']), "{$slug} stays hub-only in this wave");
    }
    if (!in_array($slug, $existing, true)) {
        $created++;
    }
}
$ok($created === 33, "created hubs={$created}");
$intros = [];
foreach ($hubs as $slug) {
    $intros[(string)($keywords[$slug]['intro'] ?? '')] = $slug;
}
$ok(count($intros) === 44, '44 unique hub intros');

$towns = icomplyUkTowns();
$ok(count($towns) >= 900, 'mainland town count ' . count($towns));
$gm = [];
foreach (icomplyCrawlTownNames() as $name) {
    $gm[areaSlug((string)$name)] = true;
}
$extra = icomplyFireAlarmInstallerExtraTownPaths($gm);
$expectedExtra = 0;
foreach ($p0 as $slug) {
    foreach ($towns as $town) {
        if (!isset($gm[(string)$town['slug']])) {
            $expectedExtra++;
        }
    }
}
$ok(count($extra) === $expectedExtra, 'P0 extra town paths ' . count($extra) . ' (mainland outside the GM matrix)');
$ok(count($p0) * count($towns) > count($extra), 'GM overlap is not recounted in the extra set');

$ok(icomplyNonGmMatrixRedirect('/pages/keywords/fire-alarm-installer/leeds') === null, 'Leeds installer page stays');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/fire-alarm-installer/belfast') === '/pages/keywords/fire-alarm-installer', 'Belfast redirects to the hub');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/fire-alarm-company/liverpool') === null, 'non-P0 Liverpool stays inside the dual ring');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/fire-alarm-company/birmingham') === '/pages/keywords/fire-alarm-company', 'non-P0 Birmingham stays on the hub');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/rewire/liverpool') === null, 'rewire Liverpool stays inside the dual ring');
$ok(icomplyNonGmMatrixRedirect('/pages/electrical/london') === '/pages/services/electrical', 'electrical London still redirects');
$ok(icomplyPathIsIndexable('/pages/keywords/fire-alarm-installer/cardiff'), 'Cardiff installer page is indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/eicr/stockport'), 'other keyword×town stays noindex');
$ok(!icomplyPathIsIndexable('/pages/keywords/fire-alarm-company/cardiff'), 'non-P0 town URL stays noindex');

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(8));
}
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';

$samples = [
    ['/pages/keywords/fire-alarm-installer', 'Fire alarm installer', true],
    ['/pages/keywords/fire-alarm-company', 'Fire alarm company', false],
    ['/pages/keywords/fire-alarm-installer/london', 'London', true],
    ['/pages/keywords/commercial-fire-alarm-installation/glasgow', 'Glasgow', true],
    ['/pages/keywords/domestic-fire-alarm-installation/cardiff', 'Cardiff', true],
];
foreach ($samples as [$path, $needle, $townPage]) {
    ob_start();
    $rendered = routerDispatchVirtual($path);
    $html = (string)ob_get_clean();
    $copy = $html;
    $mainStart = strpos($html, 'id="main-content"');
    $mainEnd = strpos($html, '<!-- /#main-content -->');
    if ($mainStart !== false && $mainEnd !== false && $mainEnd > $mainStart) {
        $copy = substr($html, $mainStart, $mainEnd - $mainStart);
    }
    $ok($rendered && stripos($html, $needle) !== false, "{$path} renders {$needle}");
    $ok(!str_contains($copy, 'approved subcontractor'), "{$path} has no approved-subcontractor wording");
    $ok(!preg_match('/£\s*\d/', $copy), "{$path} has no £ price");
    $ok(!preg_match('/\bb\d{5,}\b/', $copy), "{$path} has no visible uniqueness token");
    $ok(stripos($copy, 'fixed-price') === false && stripos($copy, 'fixed price') === false, "{$path} is POA only");
    if (preg_match('#^/pages/keywords/[^/]+/[^/]+$#', $path)) {
        foreach (['og:title', 'og:description', 'og:url', 'og:type', 'og:image'] as $prop) {
            $ok(str_contains($html, 'property="' . $prop . '"'), "{$path} has {$prop}");
        }
        $ok((bool)preg_match('/property="og:image" content="https:\/\//', $html), "{$path} og:image is absolute https");
        $ok(str_contains($html, 'property="og:type" content="website"'), "{$path} og:type is website");
    }
    if ($townPage && str_contains($path, '/london') || str_contains($path, '/glasgow') || str_contains($path, '/cardiff')) {
        $ok(str_contains($html, '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE'), "{$path} shows the NAP");
        $ok(str_contains($html, '/pages/services/fire-alarms'), "{$path} links the fire alarm service");
        preg_match_all('/href="([^"]+)"/', $html, $hrefs);
        $unique = array_unique($hrefs[1] ?? []);
        $ok(count($unique) >= 80, "{$path} unique links " . count($unique));
    }
}

ob_start();
routerDispatchVirtual('/pages/keywords/fire-alarm-installer');
$hubHtml = (string)ob_get_clean();
$ok(substr_count($hubHtml, '/pages/keywords/fire-alarm-installer/') >= 900, 'installer hub links the mainland town set');
$ok(str_contains($hubHtml, 'United Kingdom') || str_contains($hubHtml, 'UK mainland'), 'installer hub is nationwide');

echo ($fail === 0 ? 'PASS' : 'FAIL') . " ({$pass} pass, {$fail} fail)\n";
echo 'p0=' . count($p0) . ' hubs=' . count($hubs) . ' towns=' . count($towns) . ' p0_town_slots=' . (count($p0) * count($towns)) . ' extra_non_gm=' . count($extra) . "\n";
exit($fail === 0 ? 0 : 1);
