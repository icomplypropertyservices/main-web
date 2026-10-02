<?php
/**
 * Fail when one primary hero is reused too widely inside a lane.
 *
 * Threshold (HERO_DUP_MAX in includes/hero-images.php): 8.
 * A lane may use the same image src on at most 8 pages. Near neighbours are a
 * town cluster: 8 consecutive areas in data/areas.json (the order is geographic).
 * Two pages in the same cluster must not share a primary hero. Adjacent pages
 * in the lane must not share one either.
 *
 * v1 lanes:
 *   area:aov-air-handling          service × town
 *   guide:aov-air-handling         AOV keyword guides
 *   kwarea:{aov-keyword}           that guide × town
 *   guide:barriers                 car-park barrier + gate access guides
 *   kwarea:{barrier-keyword}       those guides × town
 *   products:aov                   one src per AOV kit card
 *   products:barriers              one src per 5m barrier pack
 *
 * Also fails if two manufacturer image files are byte-identical (a brand must
 * not wear another brand's photo).
 *
 * Usage: php bin/check-hero-duplicates.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$max = HERO_DUP_MAX;
$cluster = HERO_CLUSTER_SIZE;
$errors = [];
$fixed = 0;

function heroLaneErrors(string $lane, array $srcs, int $max, int $cluster): array
{
    $errors = [];
    if ($srcs === []) {
        return ["{$lane}: no pages"];
    }
    foreach ($srcs as $i => $src) {
        if (!is_string($src) || $src === '') {
            $errors[] = "{$lane}: empty hero at index {$i}";
        }
    }
    $counts = array_count_values(array_filter($srcs, 'is_string'));
    foreach ($counts as $src => $count) {
        if ($count > $max) {
            $errors[] = "{$lane}: {$src} used {$count} times (max {$max})";
        }
    }
    $n = count($srcs);
    for ($i = 1; $i < $n; $i++) {
        if ($srcs[$i] !== '' && $srcs[$i] === $srcs[$i - 1]) {
            $errors[] = "{$lane}: adjacent pages {$i}-" . ($i - 1) . ' share ' . $srcs[$i];
        }
    }
    for ($start = 0; $start < $n; $start += $cluster) {
        $slice = array_slice($srcs, $start, $cluster);
        $slice = array_values(array_filter($slice, static fn($s) => $s !== ''));
        if (count($slice) !== count(array_unique($slice))) {
            $errors[] = "{$lane}: town/guide cluster starting at {$start} reuses a hero";
        }
    }
    return $errors;
}

function heroFileMd5(string $rel): string
{
    $path = SITE_ROOT . '/' . ltrim($rel, '/');
    if (!is_file($path)) {
        return '';
    }
    return md5_file($path) ?: '';
}

// --- Audit: how many byte-identical clusters exist on disk ---
$byHash = [];
$scanDirs = [SITE_ROOT . '/assets/images/services', SITE_ROOT . '/assets/images/keywords', SITE_ROOT . '/assets/images/lanes', SITE_ROOT . '/assets/images/products'];
foreach ($scanDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            continue;
        }
        $hash = md5_file($file->getPathname());
        if ($hash === false) {
            continue;
        }
        $byHash[$hash][] = $file->getPathname();
    }
}
$dupClusters = 0;
$dupFiles = 0;
foreach ($byHash as $group) {
    if (count($group) > 1) {
        $dupClusters++;
        $dupFiles += count($group);
    }
}

$areas = getAreas();
$aovKeywords = array_keys(getKeywordsForService('aov-air-handling'));
sort($aovKeywords);
$barrierKeywords = icomplyHeroConfig()['barrier_keywords'] ?? [];

// Service × town
$areaSrcs = [];
$oldArea = '/assets/images/services/aov-air-handling.jpg';
foreach ($areas as $i => $area) {
    $src = icomplyAreaHero('aov-air-handling', (string) $area);
    $areaSrcs[] = (string) $src;
    if ($src && $src !== $oldArea) {
        $fixed++;
    }
    $inlines = [
        icomplyAreaInline('aov-air-handling', (string) $area, 1),
        icomplyAreaInline('aov-air-handling', (string) $area, 2),
        icomplyAreaInline('aov-air-handling', (string) $area, 3),
    ];
    foreach ($inlines as $inline) {
        if ($inline === $src) {
            $errors[] = 'area inline matches hero for ' . $area;
        }
    }
    if ($i > 0) {
        foreach ($inlines as $inline) {
            if ($inline === $areaSrcs[$i - 1]) {
                $errors[] = 'area inline matches neighbour hero at ' . $area;
            }
        }
    }
}
$errors = array_merge($errors, heroLaneErrors('area:aov-air-handling', $areaSrcs, $max, $cluster));

$hub = icomplyHubHero('aov-air-handling');
if ($hub && $hub !== $oldArea) {
    $fixed++;
}
if ($hub && ($areaSrcs[0] ?? '') === $hub) {
    $errors[] = 'AOV hub hero matches the first town hero';
}

// Keyword guides + keyword × town
$guideSrcs = [];
foreach ($aovKeywords as $slug) {
    $hero = icomplyKeywordHero($slug, 'aov-air-handling', '');
    $guideSrcs[] = $hero['primary'];
    $old = '/assets/images/keywords/' . $slug . '.jpg';
    if ($hero['primary'] !== $old) {
        $fixed++;
    }
    if ($hero['inline'] === $hero['primary']) {
        $errors[] = "guide {$slug}: inline matches hero";
    }
    $townSrcs = [];
    foreach ($areas as $area) {
        $town = icomplyKeywordHero($slug, 'aov-air-handling', (string) $area);
        $townSrcs[] = $town['primary'];
        if ($town['primary'] !== $old) {
            $fixed++;
        }
        if ($town['inline'] === $town['primary']) {
            $errors[] = "kwarea {$slug} {$area}: inline matches hero";
        }
        $areaHero = icomplyAreaHero('aov-air-handling', (string) $area);
        if ($town['primary'] === $areaHero) {
            $errors[] = "kwarea {$slug} shares the AOV service hero in {$area}";
        }
    }
    $errors = array_merge($errors, heroLaneErrors('kwarea:' . $slug, $townSrcs, $max, $cluster));
}
$errors = array_merge($errors, heroLaneErrors('guide:aov-air-handling', $guideSrcs, $max, $cluster));

$barrierGuideSrcs = [];
foreach ($barrierKeywords as $slug) {
    if (!is_string($slug) || $slug === '') {
        continue;
    }
    $meta = getMajorKeywords()[$slug] ?? null;
    $service = is_array($meta) ? (string) ($meta['service'] ?? 'access-control') : 'access-control';
    $hero = icomplyKeywordHero($slug, $service, '');
    $barrierGuideSrcs[] = $hero['primary'];
    $old = '/assets/images/keywords/' . $slug . '.jpg';
    if ($hero['primary'] !== $old) {
        $fixed++;
    }
    $townSrcs = [];
    foreach ($areas as $area) {
        $town = icomplyKeywordHero($slug, $service, (string) $area);
        $townSrcs[] = $town['primary'];
        if ($town['primary'] !== $old) {
            $fixed++;
        }
    }
    $errors = array_merge($errors, heroLaneErrors('kwarea:' . $slug, $townSrcs, $max, $cluster));
}
$errors = array_merge($errors, heroLaneErrors('guide:barriers', $barrierGuideSrcs, $max, min($cluster, max(1, count($barrierGuideSrcs)))));

// Product cards: one src each, files must exist and differ by bytes.
$productSets = [
    'products:aov' => [
        'file' => SITE_ROOT . '/data/aov-kit-cdn-images.json',
        'keys' => ['aov-ctrl', 'aov-sensor', 'aov-act', 'aov-act-hvy', 'aov-motor', 'aov-motor-hvy', 'aov-kit-1m2'],
    ],
    'products:barriers' => [
        'file' => SITE_ROOT . '/data/bar-5m-came-gard-images.json',
        'keys' => ['bar-5m-std', 'bar-5m-videx', 'bar-5m-paxton', 'bar-5m-gsm', 'bar-5m-allin'],
    ],
];
foreach ($productSets as $lane => $spec) {
    $map = json_decode((string) file_get_contents($spec['file']), true);
    if (!is_array($map)) {
        $errors[] = "{$lane}: image map unreadable";
        continue;
    }
    $srcs = [];
    $hashes = [];
    foreach ($spec['keys'] as $key) {
        $src = (string) ($map[$key] ?? '');
        $srcs[] = $src;
        if ($src === '' || !is_file(SITE_ROOT . '/' . ltrim($src, '/'))) {
            $errors[] = "{$lane}: missing file for {$key} ({$src})";
            continue;
        }
        $hashes[] = heroFileMd5($src);
        $fixed++;
    }
    if (count($srcs) !== count(array_unique($srcs))) {
        $errors[] = "{$lane}: duplicate image src";
    }
    $hashes = array_filter($hashes);
    if (count($hashes) !== count(array_unique($hashes))) {
        $errors[] = "{$lane}: two cards use byte-identical photos";
    }
}

// Manufacturer files must not be copies of each other.
$mfrDir = SITE_ROOT . '/assets/images/manufacturers';
$mfrHash = [];
if (is_dir($mfrDir)) {
    foreach (scandir($mfrDir) ?: [] as $name) {
        $path = $mfrDir . '/' . $name;
        if (!is_file($path)) {
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            continue;
        }
        $hash = md5_file($path);
        $mfrHash[$hash][] = $name;
    }
}
foreach ($mfrHash as $hash => $names) {
    if (count($names) > 1) {
        $errors[] = 'manufacturer photos repeat across brands: ' . implode(', ', $names);
    }
}

echo "HERO_DUP_MAX={$max} cluster={$cluster}\n";
echo "Duplicate file clusters on disk (services/keywords/lanes/products): {$dupClusters} clusters, {$dupFiles} files\n";
echo "Pages whose primary hero src changed: {$fixed}\n";
echo 'AOV keyword guides: ' . count($aovKeywords) . ' · towns: ' . count($areas) . " · barrier guides: " . count($barrierGuideSrcs) . "\n";

if ($errors) {
    echo "FAIL " . count($errors) . " problem(s)\n";
    foreach (array_slice($errors, 0, 40) as $err) {
        echo "  - {$err}\n";
    }
    if (count($errors) > 40) {
        echo '  … ' . (count($errors) - 40) . " more\n";
    }
    exit(1);
}

echo "OK primary heroes stay within {$max} uses per lane and differ inside each town cluster\n";
exit(0);
