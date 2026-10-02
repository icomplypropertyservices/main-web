#!/usr/bin/env php
<?php
/**
 * AOV × UK mainland towns with population over 10,000 (non-prod).
 *
 * Calls the existing generators for that town list only:
 *   php bin/generate-site.php --service=aov-air-handling --areas-json=data/aov-nationwide-areas.json
 *   php bin/generate-keyword-area-pages.php --only=<every AOV keyword> --areas-json=...
 *
 * Then deletes AOV stubs for towns that are not in the pop>10k list.
 * Does not deploy and does not promote a live host.
 *
 * Usage (repo root): php website/bin/generate-aov-nationwide.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$php = PHP_BINARY;
$fail = 0;
$json = SITE_ROOT . '/data/aov-nationwide-areas.json';

$run = static function (array $args) use ($php, &$fail): void {
    $cmd = array_merge([$php], $args);
    $line = implode(' ', array_map('escapeshellarg', $cmd));
    echo "→ {$line}\n";
    passthru($line, $code);
    if ($code !== 0) {
        $fail++;
        fwrite(STDERR, "generator exit {$code}\n");
    }
};

$services = getAovNationwideServices();
$keywords = getAovMatrixKeywordSlugs();
$areas = getAovNationwideAreas();
$keep = [];
foreach ($areas as $name) {
    $keep[areaSlug($name)] = true;
}

echo "AOV nationwide generator (pop>10k, non-prod, no live promote)\n";
echo "Services: " . implode(', ', $services) . "\n";
echo "Keywords: " . count($keywords) . " × towns: " . count($areas) . "\n";
echo "Service×area pages: " . (count($services) * count($areas)) . "\n";
echo "Keyword×area pages: " . (count($keywords) * count($areas)) . "\n";
echo str_repeat('=', 56) . "\n";

foreach ($services as $svc) {
    $run([
        __DIR__ . '/generate-site.php',
        '--service=' . $svc,
        '--areas-json=' . $json,
    ]);
}

if ($keywords) {
    $run([
        __DIR__ . '/generate-keyword-area-pages.php',
        '--only=' . implode(',', $keywords),
        '--areas-json=' . $json,
    ]);
}

$prune = static function (string $dir) use ($keep): int {
    if (!is_dir($dir)) {
        return 0;
    }
    $removed = 0;
    foreach (glob($dir . '/*.php') ?: [] as $file) {
        $slug = basename($file, '.php');
        if ($slug === 'index' || isset($keep[$slug])) {
            continue;
        }
        unlink($file);
        $removed++;
    }
    return $removed;
};

$removed = 0;
foreach ($services as $svc) {
    $removed += $prune(SITE_ROOT . '/pages/' . $svc);
}
foreach ($keywords as $slug) {
    $removed += $prune(SITE_ROOT . '/pages/keywords/' . $slug);
}
echo "Pruned {$removed} stubs outside the pop>10k list\n";

$sample = SITE_ROOT . '/pages/aov-air-handling/birmingham.php';
$glasgow = SITE_ROOT . '/pages/aov-air-handling/glasgow.php';
$paisley = SITE_ROOT . '/pages/keywords/aov-installation/paisley.php';
$kwSample = SITE_ROOT . '/pages/keywords/aov-installation/cardiff.php';
$small = SITE_ROOT . '/pages/aov-air-handling/whalley.php';
$belfast = SITE_ROOT . '/pages/aov-air-handling/belfast.php';
echo is_file($sample) ? "OK stub {$sample}\n" : "MISSING stub {$sample}\n";
echo is_file($glasgow) ? "OK stub {$glasgow}\n" : "MISSING stub {$glasgow}\n";
echo is_file($paisley) ? "OK stub {$paisley}\n" : "MISSING stub {$paisley}\n";
echo is_file($kwSample) ? "OK stub {$kwSample}\n" : "MISSING stub {$kwSample}\n";
echo is_file($small) ? "UNEXPECTED stub {$small}\n" : "OK no stub for Whalley\n";
echo is_file($belfast) ? "UNEXPECTED stub {$belfast}\n" : "OK no stub for Belfast\n";
if (!is_file($sample) || !is_file($glasgow) || !is_file($paisley) || !is_file($kwSample) || is_file($small) || is_file($belfast)) {
    $fail++;
}

exit($fail > 0 ? 1 : 0);
