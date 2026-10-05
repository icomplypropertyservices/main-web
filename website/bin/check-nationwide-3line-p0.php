<?php
/**
 * Nationwide 3-line P0: 76 hubs, TOP 5000 towns, meta, FAQ, images, words.
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

$slugs = icomplyNationwide3lineP0Slugs();
$keywords = getMajorKeywords();
$towns = icomplyTop5000Towns();
$ok(count($slugs) === 76, '76 P0 slugs');
$ok(count($towns) === 5000, 'TOP 5000 towns loaded');
$ok(count(array_unique(array_map(static fn(array $t): string => (string)$t['slug'], $towns))) === 5000, 'town slugs unique');

$kept = ['cadishead', 'chorlton', 'heywood', 'pendlebury', 'romiley', 'tameside', 'trafford', 'withington', 'worsley', 'wythenshawe'];
foreach ($kept as $slug) {
    $ok(icomplyTop5000TownBySlug($slug) === null, "{$slug} stays out of TOP5000");
    $ok(icomplyCrawlTownSlug($slug), "{$slug} stays on the GM crawl list");
}

$created = 0;
$existingBefore = 0;
$raw = json_decode((string)file_get_contents($root . '/data/keywords.json'), true);
$barrierRaw = json_decode((string)file_get_contents($root . '/data/barriers-keywords.json'), true);
$intros = [];
$titles = [];
$metas = [];
foreach ($slugs as $slug) {
    $ok(isset($keywords[$slug]), "{$slug} is served by getMajorKeywords");
    $ok(is_file($root . '/pages/keywords/' . $slug . '.php'), "{$slug} hub stub exists");
    $row = $keywords[$slug] ?? [];
    $body = (string)($row['body'] ?? '');
    $words = preg_match_all("/[A-Za-z0-9'’—-]+/u", $body) ?: 0;
    $ok($words >= 800, "{$slug} body words {$words}");
    $ok(count($row['faq'] ?? []) >= 3, "{$slug} has FAQs");
    $images = icomplyNationwide3lineImages($slug);
    $ok(count($images) === 3, "{$slug} has 3 images");
    foreach ($images as $image) {
        $ok(is_file($root . $image), "{$slug} image {$image}");
    }
    $title = (string)($row['seo_title'] ?? '');
    $meta = (string)($row['meta_desc'] ?? '');
    $ok(str_contains($title, 'iComply'), "{$slug} title has brand");
    $ok(strlen($meta) >= 140 && strlen($meta) <= 160, "{$slug} meta length " . strlen($meta));
    $intros[$row['intro'] ?? ''] = $slug;
    $titles[$title] = $slug;
    $metas[$meta] = $slug;
    $blob = $body . ' ' . ($row['intro'] ?? '') . ' ' . $meta;
    $ok(!preg_match('/£\s*\d/', $blob), "{$slug} has no £ price");
    $ok(!preg_match('/fixed[- ]price/i', $blob), "{$slug} has no fixed price");
    $ok(!str_contains(strtolower($blob), 'approved subcontractor'), "{$slug} has no approved-subcontractor wording");
    $ok(!preg_match('/\bb\d{5,}\b/', $blob), "{$slug} has no visible token");
    if (isset($raw[$slug]) || (is_array($barrierRaw) && isset($barrierRaw[$slug]))) {
        $existingBefore++;
    } else {
        $created++;
    }
}
$ok(count($intros) === 76, 'unique intros');
$ok(count($titles) === 76, 'unique titles');
$ok(count($metas) === 76, 'unique meta descriptions');
$ok($created + $existingBefore === 76, "created={$created} existing={$existingBefore}");

$gm = [];
foreach (icomplyCrawlTownNames() as $name) {
    $gm[areaSlug((string)$name)] = true;
}
$extra = icomplyNationwide3lineExtraTownPaths($gm);
$expected = 0;
foreach ($slugs as $slug) {
    foreach ($towns as $town) {
        if (!isset($gm[(string)$town['slug']])) {
            $expected++;
        }
    }
}
$ok(count($extra) === $expected, 'P0 extra town paths ' . count($extra));
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/aov-installer/leeds') === null, 'Leeds AOV stays');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/aov-installer/belfast') === '/pages/keywords/aov-installer', 'Belfast redirects');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/aov-installer/chorlton') === null, 'Chorlton stays on the GM path');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/rewire/birmingham') === '/pages/keywords/rewire', 'rewire Birmingham still redirects');
$ok(icomplyPathIsIndexable('/pages/keywords/fire-alarm-installer/cardiff'), 'Cardiff installer is indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/eicr/stockport'), 'other keyword×town stays noindex');
$ok(icomplyPathIsIndexable('/pages/keywords/barrier-installer/cadishead'), 'Cadishead barrier page stays indexable via GM');

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(8));
}
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
routerDispatchVirtual('/pages/keywords/aov-installer');
$hub = (string)ob_get_clean();
$ok(str_contains($hub, '<title>'), 'hub has a title');
$ok(str_contains($hub, 'rel="canonical"'), 'hub has a canonical');
$ok(str_contains($hub, 'property="og:title"'), 'hub has og:title');
$ok(str_contains($hub, 'property="og:description"'), 'hub has og:description');
$ok(str_contains($hub, 'property="og:image" content="https://icomplypropertyservices.co.uk/'), 'hub og:image is absolute');
preg_match('/<meta name="description" content="([^"]*)"/', $hub, $hubDesc);
$hubDescLen = strlen(html_entity_decode($hubDesc[1] ?? '', ENT_QUOTES, 'UTF-8'));
$ok($hubDescLen >= 140 && $hubDescLen <= 160, "hub meta length {$hubDescLen}");
$ok(str_contains($hub, 'rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/aov-installer"'), 'hub canonical is exact');
$ok(str_contains($hub, 'AOV Installer FAQ') || str_contains($hub, 'FAQ'), 'hub has an FAQ heading');
preg_match_all('/<img /', $hub, $imgs);
$ok(count($imgs[0]) >= 3, 'hub images ' . count($imgs[0]));
$ok(str_contains($hub, '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE'), 'hub shows NAP');
$ok(str_contains($hub, '/pages/keywords/aov-installer/leeds'), 'hub links a TOP5000 town');
$ok(str_contains($hub, '/pages/areas/chorlton') || str_contains($hub, '/pages/areas/cadishead'), 'hub still links a GM place outside TOP5000');
$ok(substr_count($hub, '/pages/keywords/aov-installer/') >= 5000, 'hub links the TOP5000 set');

ob_start();
routerDispatchVirtual('/pages/keywords/fire-alarm-installer/leeds');
$town = (string)ob_get_clean();
$ok(str_contains($town, '<h1>Fire Alarm Installer in Leeds</h1>'), 'Leeds H1');
$ok(str_contains($town, 'rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/fire-alarm-installer/leeds"'), 'Leeds canonical');
$ok(str_contains($town, 'property="og:image"'), 'Leeds og:image');
$ok(str_contains($town, 'property="og:url"'), 'Leeds og:url');
preg_match('/<meta name="description" content="([^"]*)"/', $town, $desc);
$descLen = strlen(html_entity_decode($desc[1] ?? '', ENT_QUOTES, 'UTF-8'));
$ok($descLen >= 140 && $descLen <= 160, "Leeds meta length {$descLen}");
preg_match('/<article id="local-copy">(.*)<\/article>/s', $town, $article);
$articleWords = preg_match_all("/[A-Za-z0-9'’—-]+/u", strip_tags($article[1] ?? '')) ?: 0;
$ok($articleWords >= 800, "Leeds body words {$articleWords}");
preg_match_all('/<img /', $town, $townImgs);
$ok(count($townImgs[0]) >= 3, 'Leeds images ' . count($townImgs[0]));
$ok(str_contains($town, '<h2>Fire Alarm Installer FAQ</h2>'), 'Leeds FAQ');
$ok(str_contains($town, '/pages/services/fire-alarms'), 'Leeds links fire alarms');
$ok(!str_contains($town, 'approved subcontractor'), 'Leeds has no approved-subcontractor wording');
preg_match_all('/href="([^"]+)"/', $town, $hrefs);
$ok(count(array_unique($hrefs[1] ?? [])) >= 80, 'Leeds unique links ' . count(array_unique($hrefs[1] ?? [])));

echo ($fail === 0 ? 'PASS' : 'FAIL') . " ({$pass} pass, {$fail} fail)\n";
echo "created={$created} existing={$existingBefore} towns=5000 p0_town_slots=" . (76 * 5000) . " extra_non_gm=" . count($extra) . "\n";
exit($fail === 0 ? 0 : 1);
