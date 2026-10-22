<?php
/**
 * Manchester electrical P0: 89 hubs, GM-core 60 town pages, quality bar.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/manchester-electrical-p0.php';

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

$spec = manchesterElectricalP0Spec();
$intents = $spec['intents'] ?? [];
$towns = $spec['towns'] ?? [];
$ok(count($intents) === 89, 'P0 intents=89');
$ok(count($towns) === 60, 'GM-core towns=60');
$ok(count(manchesterElectricalP0JobTownPaths()) === 80 * 60, 'job×town paths=' . count(manchesterElectricalP0JobTownPaths()));

$pages = 0;
$keywordHubs = 0;
$jobHubs = 0;
foreach ($intents as $intent) {
    $slug = (string)$intent['slug'];
    $surfaces = ($intent['kind'] ?? '') === 'keyword' ? ['keyword'] : ['keyword', 'job'];
    if (in_array('keyword', $surfaces, true)) {
        $keywordHubs++;
    }
    if (in_array('job', $surfaces, true)) {
        $jobHubs++;
    }
    $places = array_merge([''], array_map(static fn(array $town): string => (string)$town['slug'], $towns));
    foreach ($surfaces as $surface) {
        foreach ($places as $town) {
            $page = manchesterElectricalP0Render($surface, $slug, $town);
            $pages++;
            if ($page === null) {
                $ok(false, "missing {$surface} {$slug} {$town}");
                continue;
            }
            $metaLen = mb_strlen($page['description']);
            $images = preg_match_all('/<img /', $page['html']);
            $bad = $page['words'] < 800
                || $metaLen < 140
                || $metaLen > 160
                || $images < 3
                || !str_contains($page['html'], 'rel="canonical" href="' . $page['canonical'] . '"')
                || !str_contains($page['html'], 'property="og:title"')
                || !str_contains($page['html'], 'property="og:description"')
                || !str_contains($page['html'], 'property="og:url"')
                || !str_contains($page['html'], 'property="og:image"')
                || !str_contains($page['html'], '<h2>' . htmlspecialchars((string)$intent['name'], ENT_QUOTES, 'UTF-8') . ' FAQ</h2>')
                || !str_contains($page['html'], '/pages/services/electrical')
                || stripos($page['html'], 'gas safe') !== false
                || stripos($page['html'], 'approved subcontractor') !== false;
            if ($slug !== 'eicr' && str_contains($page['html'], '£')) {
                $bad = true;
            }
            if ($slug === 'nic-electrician' && stripos($page['html'], 'does not claim NICEIC') === false) {
                $bad = true;
            }
            if ($bad) {
                $ok(false, "{$page['path']} words={$page['words']} meta={$metaLen} images={$images}");
            }
        }
    }
}
$ok($pages === 10309, "rendered pages={$pages}");
$ok($keywordHubs === 89, "keyword hubs={$keywordHubs}");
$ok($jobHubs === 80, "job hubs={$jobHubs}");
$ok(icomplyPathIsIndexable('/pages/keywords/eicr/stockport'), 'eicr stockport indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/boiler/stockport'), 'boiler stockport stays noindex');
$ok(str_contains((string)file_get_contents(SITE_ROOT . '/pages/keywords/three-phase-electrician.php'), 'manchesterElectricalP0Emit'), 'create stub exists');
$ok(is_file(SITE_ROOT . '/pages/jobs/eicr.php') && !str_contains((string)file_get_contents(SITE_ROOT . '/pages/jobs/eicr.php'), 'manchesterElectricalP0Emit'), 'existing EICR job hub kept');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
