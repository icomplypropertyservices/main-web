<?php
/**
 * Access-control nationwide lane: hub, city notes, keywords, manufacturer links.
 * Tunstall must not be linked. No invented £ prices.
 *
 * Usage: php website/bin/check-access-control-nationwide.php
 */
declare(strict_types=1);

ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/router.php';

$fail = 0;
$ok = static function (bool $pass, string $msg) use (&$fail): void {
    echo ($pass ? 'OK   ' : 'FAIL ') . $msg . PHP_EOL;
    if (!$pass) {
        $fail++;
    }
};

if (PHONE !== '07517806082') {
    $ok(false, 'PHONE constant must stay 07517806082, got ' . PHONE);
} else {
    $ok(true, 'phone 07517806082');
}

$cities = acnCities();
$ok(count($cities) >= 30, 'city notes ' . count($cities) . ' (want at least 30)');

$slugs = [];
$locals = [];
foreach ($cities as $city) {
    $slug = areaSlug((string)($city['slug'] ?? ''));
    $slugs[$slug] = true;
    foreach (['name', 'nation', 'local', 'doors', 'visit', 'meta', 'faq_q', 'faq_a'] as $field) {
        $ok(trim((string)($city[$field] ?? '')) !== '', "{$slug} has {$field}");
    }
    $local = (string)$city['local'];
    $ok(strlen($local) >= 180, "{$slug} local length " . strlen($local));
    $ok(stripos($local, (string)$city['name']) !== false, "{$slug} local names the city");
    $locals[$slug] = $local . ' ' . (string)$city['doors'] . ' ' . (string)$city['visit'];
    $ok(!preg_match('/£\s*\d/', $locals[$slug]), "{$slug} has no £ price");
    foreach ((array)($city['nearby'] ?? []) as $near) {
        $ok(acnCity((string)$near) !== null, "{$slug} nearby {$near} exists");
    }
}

$banned = ['nestled', 'bustling', 'vibrant', 'look no further', 'one-stop', 'world-class', 'cutting-edge', 'your trusted', 'second to none'];
$blobAll = strtolower(implode("\n", $locals));
foreach ($banned as $word) {
    $ok(!str_contains($blobAll, $word), "city copy avoids “{$word}”");
}

$keys = array_keys($locals);
for ($i = 0; $i < count($keys); $i++) {
    for ($j = $i + 1; $j < count($keys); $j++) {
        similar_text($locals[$keys[$i]], $locals[$keys[$j]], $pct);
        if ($pct >= 48.0) {
            $ok(false, "copy too similar {$keys[$i]} / {$keys[$j]} ({$pct}%)");
        }
    }
}
$ok(true, 'pairwise city copy compared');

$mfrs = acnManufacturers();
$ok(count($mfrs) >= 15, 'manufacturers linked ' . count($mfrs));
foreach ($mfrs as $mfr) {
    $ok($mfr['slug'] !== 'tunstall', 'manufacturer list excludes tunstall slug');
    $ok(getManufacturerBySlug($mfr['slug']) !== null, 'catalogue page for ' . $mfr['slug']);
}

$keywords = acnKeywords();
$ok(count($keywords) >= 60, 'access-control keywords ' . count($keywords));
foreach ([
    'access-control-after-staff-change',
    'access-control-fire-interface-test',
    'access-level-holiday-calendar',
    'broken-fob-emergency-visit',
    'controller-battery-replace',
    'fail-secure-lock-assessment',
    'gate-access-control-liaison',
    'lift-floor-access-integration-advice',
] as $needed) {
    $ok(isset($keywords[$needed]), "keyword {$needed}");
}

$capture = static function (string $path): string {
    if (function_exists('header_remove')) {
        header_remove();
    }
    http_response_code(200);
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['QUERY_STRING'] = '';
    ob_start();
    routerHandleRequest();
    return (string)ob_get_clean();
};

$hub = $capture('/pages/access-control-systems');
$ok(str_contains($hub, '07517806082'), 'hub shows phone');
$ok(stripos($hub, 'price on application') !== false || stripos($hub, '>POA<') !== false || stripos($hub, 'POA') !== false, 'hub says POA');
$ok(!preg_match('/£\s*\d/', $hub), 'hub has no £ price');
$ok(!str_contains($hub, '/manufacturers/tunstall'), 'hub does not link Tunstall');
$ok(str_contains($hub, 'Access control systems across the UK'), 'hub H1');
foreach ($mfrs as $mfr) {
    $ok(str_contains($hub, '/pages/manufacturers/' . $mfr['slug']), 'hub links ' . $mfr['slug']);
}
foreach (array_keys($keywords) as $kw) {
    $ok(str_contains($hub, '/pages/keywords/' . $kw), 'hub links keyword ' . $kw);
}
foreach (array_keys($slugs) as $slug) {
    $ok(str_contains($hub, '/pages/access-control-systems/' . $slug), 'hub links city ' . $slug);
}

$samples = ['london', 'glasgow', 'belfast', 'stockport', 'cardiff'];
foreach ($samples as $slug) {
    $html = $capture('/pages/access-control-systems/' . $slug);
    $ok(str_contains($html, '<h1'), "{$slug} renders");
    $ok(str_contains($html, '07517806082'), "{$slug} phone");
    $ok(!str_contains($html, '/manufacturers/tunstall'), "{$slug} no Tunstall link");
    $ok(str_contains($html, 'FAQPage'), "{$slug} FAQ schema");
    foreach ($mfrs as $mfr) {
        if (!str_contains($html, '/pages/manufacturers/' . $mfr['slug'])) {
            $ok(false, "{$slug} missing manufacturer {$mfr['slug']}");
            break;
        }
    }
}

$missing = $capture('/pages/access-control-systems/not-a-real-city');
$ok(str_contains($missing, 'find that page'), 'unknown city uses the 404 page');
$ok(!str_contains($missing, 'Access control systems in Not'), 'unknown city does not render a city page');

$paxton = $capture('/pages/manufacturers/paxton');
$ok(str_contains($paxton, '/pages/access-control-systems'), 'Paxton page links the nationwide hub');
$tunstall = $capture('/pages/manufacturers/tunstall');
$ok(!str_contains($tunstall, 'on UK access-control jobs'), 'Tunstall page does not get the access-control nationwide block');

$keywordHtml = $capture('/pages/keywords/fail-secure-lock-assessment');
$ok(str_contains($keywordHtml, 'Fail Secure Lock Assessment'), 'new keyword renders');
$ok(!str_contains($keywordHtml, '/manufacturers/tunstall'), 'keyword tags skip Tunstall');

echo $fail === 0 ? "PASS\n" : "FAIL {$fail}\n";
ob_end_flush();
exit($fail === 0 ? 0 : 1);
