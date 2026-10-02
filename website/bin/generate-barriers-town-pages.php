<?php
/**
 * Write non-prod stubs: pages/barriers/{slug}.php
 * These URLs are excluded from static-export and sitemap.xml.
 *
 * Usage: php bin/generate-barriers-town-pages.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/barriers-pages.php';

$towns = barriersTowns();
$meta = barriersMeta();
$counts = $meta['counts'] ?? [];
if (count($towns) !== (int)($counts['pages'] ?? -1)) {
    fwrite(STDERR, "Count mismatch: towns=" . count($towns) . " meta=" . ($counts['pages'] ?? 'missing') . "\n");
    exit(1);
}

$slugs = [];
foreach ($towns as $town) {
    $pop = (int)($town['population'] ?? 0);
    $slug = (string)($town['slug'] ?? '');
    if ($pop <= 10000 || $slug === '' || isset($slugs[$slug])) {
        fwrite(STDERR, "Invalid town row: {$slug} pop={$pop}\n");
        exit(1);
    }
    $slugs[$slug] = true;
}

$dir = SITE_ROOT . '/pages/barriers';
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    fwrite(STDERR, "Cannot create {$dir}\n");
    exit(1);
}

$written = 0;
foreach ($towns as $town) {
    $slug = (string)$town['slug'];
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED non-prod draft — php bin/generate-barriers-town-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/barriers-pages.php';\n"
        . "renderBarriersTownPage({$slugExport});\n";
    file_put_contents($dir . '/' . $slug . '.php', $stub);
    $written++;
}

file_put_contents(
    $dir . '/index.php',
    "<?php\n"
    . "/** AUTO-GENERATED non-prod draft — php bin/generate-barriers-town-pages.php */\n"
    . "require_once __DIR__ . '/../../includes/barriers-pages.php';\n"
    . "renderBarriersIndexPage();\n"
);

echo "Barriers town pages: {$written} stubs + index\n";
echo "Non-prod: excluded from static export and sitemap\n";
echo 'England=' . (int)($counts['by_nation']['England'] ?? 0)
    . ' Scotland=' . (int)($counts['by_nation']['Scotland'] ?? 0)
    . ' Wales=' . (int)($counts['by_nation']['Wales'] ?? 0)
    . ' North West round=' . (int)($counts['northwest_round'] ?? 0)
    . "\n";
