#!/usr/bin/env php
<?php
/**
 * Thin stubs for /pages/areas/{slug}.php
 */
require_once __DIR__ . '/../config.php';

$dir = SITE_ROOT . '/pages/areas';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$n = 0;
foreach (getAreas() as $area) {
    $safe = areaSlug($area);
    $areaExport = var_export($area, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-area-hubs.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderAreaHubPage({$areaExport});\n";
    file_put_contents("{$dir}/{$safe}.php", $stub);
    $n++;
}

// Ensure areas index exists (runtime list of all towns)
$indexSrc = SITE_ROOT . '/pages/areas/index.php';
if (!is_file($indexSrc)) {
    // Fallback minimal index if missing
    file_put_contents($indexSrc, "<?php require_once __DIR__ . '/../../config.php'; \$pageTitle='Areas'; require SITE_ROOT.'/includes/header.php'; echo '<section class=\"max-w-6xl mx-auto px-6 py-16\"><h1>Areas</h1>'; foreach(getAreas() as \$a){ echo '<a class=\"block\" href=\"'.url('/pages/areas/'.areaSlug(\$a).'.php').'\">'.htmlspecialchars(\$a).'</a>'; } echo '</section>'; require SITE_ROOT.'/includes/footer.php';\n");
}

echo "Area hubs generated: {$n} (+ index)\n";
