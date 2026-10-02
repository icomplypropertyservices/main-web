#!/usr/bin/env php
<?php
/**
 * Beech → Burton (bucket B01): every core service × each locality renders,
 * core areas.json is unchanged, and the XML sitemap does not list the bucket.
 *
 * Usage: php website/bin/check-area-bucket-b01.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/local-content.php';
require_once __DIR__ . '/../includes/render.php';
require_once __DIR__ . '/../includes/matrix-page.php';
require_once __DIR__ . '/../includes/sitemap.php';

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

$rows = icomplyAreaExpansionRecords();
$slugs = array_keys($rows);
$ok(count($rows) === 100, 'bucket has 100 localities (' . count($rows) . ')');
$ok(($slugs[0] ?? '') === 'beech', 'first slug is beech');
$ok(($slugs[count($slugs) - 1] ?? '') === 'burton', 'last slug is burton');
$ok($slugs === array_values(array_unique($slugs)), 'slugs are unique');

$sorted = $slugs;
sort($sorted);
$ok($sorted === $slugs, 'slugs run beech through burton in slug order');

$coreBefore = count(getAreas());
$ok($coreBefore === 168, 'areas.json stays at 168 towns (' . $coreBefore . ')');
$ok(!in_array('Beech', getAreas(), true), 'Beech is not in the core town list');
$ok(in_array('Bolton', getAreas(), true), 'Bolton remains a core town');

$nameMismatches = [];
foreach ($rows as $slug => $row) {
    if (areaSlug($row['name']) !== $slug) {
        $nameMismatches[] = $slug;
    }
    if (str_contains($row['stock'] . $row['focus'] . $row['region'] . $row['travel'], '£')) {
        $nameMismatches[] = $slug . ':price';
    }
}
$ok($nameMismatches === [], 'display names slug back to keys and carry no prices');

$only = icomplyExpansionOnlyRecords();
$overlap = count($rows) - count($only);
$ok($overlap === 9, 'nine bucket places already in areas.json (' . $overlap . ')');
$ok(isset($only['beech']) && isset($only['burton']) && !isset($only['bolton']), 'expansion-only includes Beech and Burton, not Bolton');

$services = getServices();
$paths = icomplyExpansionExportPaths();
$expectPaths = count($only) * (count($services) + 1);
$ok(count($paths) === $expectPaths, 'export paths = areas + every core service (' . count($paths) . '/' . $expectPaths . ')');
$ok(in_array('/pages/electrical/beech', $paths, true), 'export includes /pages/electrical/beech');
$ok(in_array('/pages/asbestos-survey/burton', $paths, true), 'export includes /pages/asbestos-survey/burton');
$ok(in_array('/pages/areas/beeley', $paths, true), 'export includes Beeley area hub');
$ok(!in_array('/pages/electrical/bolton', $paths, true), 'Bolton service page is not duplicated as expansion-only');
$ok(!in_array('/pages/keywords/eicr/beech', $paths, true), 'bucket does not add keyword×town routes');

$exportSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$ok(str_contains($exportSrc, 'icomplyExpansionExportPaths'), 'static export calls expansion paths');
$kwSrc = (string)file_get_contents(__DIR__ . '/static-export.php');
$kwFn = '';
if (preg_match('/function icomplyCollectKeywordRoutes\(.*?^}/ms', $kwSrc, $m)) {
    $kwFn = $m[0];
}
$ok($kwFn !== '' && !str_contains($kwFn, 'Expansion'), 'keyword route collector ignores expansion buckets');

$ok(areaFromSlug('beech') === 'Beech', 'areaFromSlug(beech) = Beech');
$ok(areaFromSlug('burton') === 'Burton', 'areaFromSlug(burton) = Burton');
$ok(areaFromSlug('bottom-o-th-moor') === "Bottom o' th' Moor", 'areaFromSlug keeps Bottom o\' th\' Moor');
$ok(areaFromSlug('bolton') === 'Bolton', 'areaFromSlug still prefers core Bolton');
$ok(area_profile('Beech')['districts'] === 'ST4', 'Beech profile is ST4 Staffordshire');
$ok(str_contains(area_profile('Burton')['region'], 'Wirral'), 'Burton profile is the Wirral village');
$ok(str_contains(area_profile('Burton')['region'], 'not Burtonwood'), 'Burton copy is not the Burtonwood page');
$ok(str_contains(area_profile('Bowness')['stock'], 'Bowness-on-Windermere'), 'Bowness copy names Bowness-on-Windermere');
$ok(str_contains(area_profile('Bridge')['districts'], 'confirm the postcode'), 'Bridge does not invent a postcode');
$ok(str_contains(area_profile('Broken')['focus'], 'Broken Cross'), 'Broken stays separate from Broken Cross');

$poaSlugs = ['legionella-risk-assessment', 'asbestos-survey'];
$samples = ['beech', 'bridge', 'broken', 'bowness', 'burton', 'bolton', 'billington-village-lancs'];
$seenIntro = [];
$renderFail = 0;
$checked = 0;
foreach ($rows as $slug => $row) {
    $areaName = $row['name'];
    foreach ($services as $serviceSlug => $serviceName) {
        $html = icomplyRenderServiceAreaHtml($serviceSlug, $areaName);
        $checked++;
        $canonicalPath = '/pages/' . $serviceSlug . '/' . $slug;
        $h = static function (string $s): string {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        };
        $bad = $html === ''
            || !str_contains($html, '<h1')
            || !str_contains($html, $h($areaName))
            || !str_contains($html, $h($serviceName))
            || !str_contains($html, $canonicalPath)
            || str_contains($html, '£')
            || !str_contains($html, $h($row['districts']));
        if (in_array($serviceSlug, $poaSlugs, true) && !str_contains($html, 'Price on application')) {
            $bad = true;
        }
        if (!in_array($serviceSlug, $poaSlugs, true) && !str_contains($html, 'Written quote after scope')) {
            $bad = true;
        }
        if ($bad) {
            $renderFail++;
            if ($renderFail <= 8) {
                echo "[FAIL] render {$serviceSlug}/{$slug}\n";
            }
        }
        if (in_array($slug, $samples, true) && in_array($serviceSlug, ['electrical', 'plumbing', 'epc', 'legionella-risk-assessment'], true)) {
            $seenIntro[$serviceSlug . '|' . $slug] = $html;
        }
    }
}
$ok($renderFail === 0, 'all ' . $checked . ' service×area pages rendered (' . $renderFail . ' failed)');
$ok(
    isset($seenIntro['electrical|beech'], $seenIntro['plumbing|beech'])
    && $seenIntro['electrical|beech'] !== $seenIntro['plumbing|beech'],
    'Beech electrical and plumbing copy differ'
);
$ok(
    isset($seenIntro['electrical|beech'], $seenIntro['electrical|burton'])
    && $seenIntro['electrical|beech'] !== $seenIntro['electrical|burton'],
    'Beech and Burton electrical copy differ'
);

ob_start();
renderAreaHubPage('Beech');
$beechHub = (string)ob_get_clean();
$ok(str_contains($beechHub, '/pages/electrical/beech'), 'Beech hub links to electrical service page');
$ok(str_contains($beechHub, '/pages/asbestos-survey/beech'), 'Beech hub links to asbestos service page');
$serviceLinks = 0;
foreach (array_keys($services) as $serviceSlug) {
    if (str_contains($beechHub, '/pages/' . $serviceSlug . '/beech')) {
        $serviceLinks++;
    }
}
$ok($serviceLinks === count($services), 'Beech hub links every core service (' . $serviceLinks . '/' . count($services) . ')');

ob_start();
renderAreaHubPage('Stockport');
$stockportHub = (string)ob_get_clean();
$ok(!preg_match('#/pages/(gas-systems|electrical|fire-alarms)/[a-z0-9\-]+#', $stockportHub), 'Stockport hub still has no service×area links');

$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$ok(!str_contains($xml, '/pages/electrical/beech'), 'sitemap omits /pages/electrical/beech');
$ok(!str_contains($xml, '/pages/areas/beech'), 'sitemap omits /pages/areas/beech');
$ok(!str_contains($xml, '/pages/areas/burton'), 'sitemap omits /pages/areas/burton');
$ok(str_contains($xml, '/pages/areas/stockport') || str_contains($xml, '/pages/services/electrical'), 'sitemap still lists core hubs');

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail} rendered={$checked}\n";
exit($fail > 0 ? 1 : 0);
