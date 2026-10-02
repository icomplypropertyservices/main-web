#!/usr/bin/env php
<?php
/**
 * Build one expansion batch of core service×area stubs.
 *
 * Reads a slug list (one slug per line), writes display names to
 * data/areas-bNN.json, and writes thin stubs:
 *   pages/{service}/{area}.php → renderServiceAreaPage()
 *
 * Canonical getAreas() is unchanged. Towns already in that list are still
 * given stubs, but they are not duplicated in the batch JSON.
 *
 * Usage:
 *   php bin/generate-area-batch.php --batch=b00 --slugs=/path/slugs.txt --limit=100
 */
require_once __DIR__ . '/../config.php';

$options = getopt('', ['batch:', 'slugs:', 'limit::']);
$batch = strtolower(trim((string)($options['batch'] ?? '')));
$slugsFile = (string)($options['slugs'] ?? '');
$limit = isset($options['limit']) ? (int)$options['limit'] : 0;

if (!preg_match('/^b\d{2}$/', $batch) || $slugsFile === '' || !is_file($slugsFile)) {
    fwrite(STDERR, "Usage: php bin/generate-area-batch.php --batch=b00 --slugs=slugs.txt [--limit=100]\n");
    exit(1);
}

$raw = file($slugsFile, FILE_IGNORE_NEW_LINES);
if ($raw === false) {
    fwrite(STDERR, "Cannot read {$slugsFile}\n");
    exit(1);
}
$slugs = [];
foreach ($raw as $line) {
    $slug = areaSlug(trim($line));
    if ($slug === '') {
        continue;
    }
    $slugs[] = $slug;
    if ($limit > 0 && count($slugs) >= $limit) {
        break;
    }
}

$particles = ['in', 'on', 'under', 'upon', 'le', 'the', 'of', 'and', 'by', 'with', 'en', 'de'];

$displayName = static function (string $slug) use ($particles): string {
    $parts = explode('-', $slug);
    $groups = [];
    $i = 0;
    $n = count($parts);
    while ($i < $n) {
        $tok = $parts[$i];
        if ($i + 1 < $n && in_array($parts[$i + 1], $particles, true)) {
            $group = [ucfirst($tok)];
            $i++;
            while ($i < $n && in_array($parts[$i], $particles, true)) {
                $group[] = $parts[$i];
                $i++;
            }
            if ($i < $n) {
                $group[] = ucfirst($parts[$i]);
                $i++;
            }
            $groups[] = implode('-', $group);
            continue;
        }
        $groups[] = ucfirst($tok);
        $i++;
    }
    return implode(' ', $groups);
};

$canonical = [];
foreach (getAreas() as $area) {
    $canonical[areaSlug((string)$area)] = (string)$area;
}

$names = [];
$seen = [];
foreach ($slugs as $slug) {
    if (isset($seen[$slug])) {
        fwrite(STDERR, "Duplicate slug: {$slug}\n");
        exit(1);
    }
    $seen[$slug] = true;
    $name = $canonical[$slug] ?? $displayName($slug);
    if (areaSlug($name) !== $slug) {
        fwrite(STDERR, "Slug round-trip failed: {$slug} → {$name} → " . areaSlug($name) . "\n");
        exit(1);
    }
    $names[] = $name;
}

$dataFile = SITE_ROOT . '/data/areas-' . $batch . '.json';
file_put_contents(
    $dataFile,
    json_encode($names, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

$services = getServices();
$core = getCoreAreaServiceSlugs();
$written = 0;
foreach ($core as $serviceSlug) {
    if (!isset($services[$serviceSlug])) {
        fwrite(STDERR, "Unknown core service: {$serviceSlug}\n");
        exit(1);
    }
    $dir = SITE_ROOT . '/pages/' . $serviceSlug;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        fwrite(STDERR, "Cannot mkdir {$dir}\n");
        exit(1);
    }
    foreach ($names as $name) {
        $aSlug = areaSlug($name);
        $stub = "<?php\n"
            . "/** AUTO-GENERATED — php bin/generate-area-batch.php --batch={$batch} */\n"
            . "require_once __DIR__ . '/../../includes/render.php';\n"
            . 'renderServiceAreaPage(' . var_export($serviceSlug, true) . ', ' . var_export($name, true) . ");\n";
        file_put_contents($dir . '/' . $aSlug . '.php', $stub);
        $written++;
    }
}

echo "Batch {$batch}: " . count($names) . " towns (" . ($names[0] ?? '') . " … " . ($names[count($names) - 1] ?? '') . ")\n";
echo "Data: {$dataFile}\n";
echo "Stubs: {$written} (" . count($core) . " core services)\n";
