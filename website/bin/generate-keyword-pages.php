#!/usr/bin/env php
<?php
/**
 * Thin stubs for /pages/keywords/{slug}.php + index.
 *
 * Usage:
 *   php bin/generate-keyword-pages.php
 *   php bin/generate-keyword-pages.php --limit=50
 *   php bin/generate-keyword-pages.php --only=eicr,fire-alarm-panel
 */
require_once __DIR__ . '/../config.php';

$options = getopt('', ['limit::', 'only::']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 0;
$only = [];
if (!empty($options['only'])) {
    $only = array_filter(array_map('keywordSlug', explode(',', $options['only'])));
}

$keywords = getMajorKeywords();

if ($only) {
    $filtered = [];
    foreach ($only as $slug) {
        if (isset($keywords[$slug])) {
            $filtered[$slug] = $keywords[$slug];
        } else {
            fwrite(STDERR, "  ! unknown keyword: {$slug}\n");
        }
    }
    $keywords = $filtered;
}

if ($limit > 0) {
    $keywords = array_slice($keywords, 0, $limit, true);
}

$outDir = SITE_ROOT . '/pages/keywords';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

echo "Icomply Keyword Page Generator (thin stubs)\n";
echo "==========================================\n";
echo "Keywords: " . count($keywords) . "\n\n";

$total = 0;
$byService = [];
$indexLinks = [];

foreach ($keywords as $slug => $meta) {
    $slug = keywordSlug($slug);
    $name = $meta['name'] ?? keywordDisplayName($slug);
    $serviceSlug = $meta['service'] ?? 'electrical';
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-keyword-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
    file_put_contents("{$outDir}/{$slug}.php", $stub);
    $total++;
    $byService[$serviceSlug] = ($byService[$serviceSlug] ?? 0) + 1;
    $indexLinks[$slug] = $name;
    echo "  [{$serviceSlug}] {$slug}.php\n";
}

// Index page (runtime list)
$indexPhp = "<?php\n"
    . "/** AUTO-GENERATED — php bin/generate-keyword-pages.php */\n"
    . "require_once __DIR__ . '/../../config.php';\n"
    . "\$pageTitle = 'Keyword Guides | Icomply Property Services';\n"
    . "\$metaDesc = 'Browse compliance keyword guides for fire alarms, EICR, CCTV, access control and more.';\n"
    . "require SITE_ROOT . '/includes/header.php';\n"
    . "\$keywords = getMajorKeywords();\n"
    . "ksort(\$keywords);\n"
    . "?>\n"
    . "<section class=\"max-w-6xl mx-auto px-6 py-16\">\n"
    . "    <div class=\"text-sm uppercase tracking-[3px] text-[#ff6b00] mb-2\">SEO KEYWORD GUIDES</div>\n"
    . "    <h1 class=\"text-5xl font-semibold tracking-tighter text-black\">Keyword Guides</h1>\n"
    . "    <p class=\"mt-4 text-lg text-black max-w-3xl\">Explore installation, maintenance and certification topics. Each page links to local area service pages.</p>\n"
    . "    <div class=\"mt-10 flex flex-wrap gap-2\">\n"
    . "    <?php foreach (\$keywords as \$slug => \$meta): ?>\n"
    . "        <a href=\"<?= url('/pages/keywords/' . \$slug . '.php') ?>\" class=\"px-4 py-2 bg-white border rounded-full text-sm text-black hover:border-[#ff6b00]\"><?= htmlspecialchars(\$meta['name'] ?? keywordDisplayName(\$slug), ENT_QUOTES, 'UTF-8') ?></a>\n"
    . "    <?php endforeach; ?>\n"
    . "    </div>\n"
    . "</section>\n"
    . "<?php require SITE_ROOT . '/includes/footer.php'; ?>\n";

// Preserve handcrafted overhaul index
$indexPath = "{$outDir}/index.php";
if (is_file($indexPath) && strpos((string)file_get_contents($indexPath), 'KEYWORDS_INDEX') !== false) {
    echo "  keywords/index.php (handcrafted, skipped)\n";
} else {
    file_put_contents($indexPath, $indexPhp);
    echo "  keywords/index.php\n";
}

echo "\nBy service:\n";
ksort($byService);
foreach ($byService as $s => $c) {
    echo "  {$s}: {$c}\n";
}
echo "\nGenerated {$total} keyword stubs + index.\n";
