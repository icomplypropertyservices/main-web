<?php
/**
 * Generate thin stubs: pages/keywords/{keyword}/{area}.php for every keyword × area.
 *
 * Usage:
 *   php bin/generate-keyword-area-pages.php
 *   php bin/generate-keyword-area-pages.php --only=eicr,eicr-report
 *   php bin/generate-keyword-area-pages.php --limit-keywords=10
 */
require_once __DIR__ . '/../config.php';

$options = getopt('', ['only::', 'limit-keywords::']);
$only = [];
if (!empty($options['only'])) {
    $only = array_filter(array_map('keywordSlug', explode(',', $options['only'])));
}
$limitKw = isset($options['limit-keywords']) ? (int)$options['limit-keywords'] : 0;

$keywords = getMajorKeywords();
if ($only) {
    $filtered = [];
    foreach ($only as $s) {
        if (isset($keywords[$s])) {
            $filtered[$s] = $keywords[$s];
        }
    }
    $keywords = $filtered;
}
if ($limitKw > 0) {
    $keywords = array_slice($keywords, 0, $limitKw, true);
}

$areas = getAreas();
$baseDir = SITE_ROOT . '/pages/keywords';
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
}

$total = 0;
$kwCount = 0;
$t0 = microtime(true);

echo "Keyword × Area generator\n";
echo "Keywords: " . count($keywords) . " × Areas: " . count($areas) . " = " . (count($keywords) * count($areas)) . " pages\n";
echo str_repeat('=', 50) . "\n";

foreach ($keywords as $kwSlug => $meta) {
    $kwSlug = keywordSlug($kwSlug);
    $dir = $baseDir . '/' . $kwSlug;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $kwExport = var_export($kwSlug, true);
    $kwCount++;

    foreach ($areas as $area) {
        $aSlug = areaSlug($area);
        $areaExport = var_export($area, true);
        $stub = "<?php\n"
            . "/** AUTO-GENERATED — php bin/generate-keyword-area-pages.php */\n"
            . "require_once __DIR__ . '/../../../includes/render.php';\n"
            . "renderKeywordAreaPage({$kwExport}, {$areaExport});\n";
        file_put_contents("{$dir}/{$aSlug}.php", $stub);
        $total++;
    }

    if ($kwCount % 25 === 0 || $kwCount === count($keywords)) {
        $elapsed = round(microtime(true) - $t0, 1);
        echo "  ... {$kwCount}/" . count($keywords) . " keywords ({$total} pages, {$elapsed}s)\n";
    }
}

$elapsed = round(microtime(true) - $t0, 1);
echo str_repeat('=', 50) . "\n";
echo "Done: {$total} keyword×area pages in {$elapsed}s\n";
echo "Example: pages/keywords/eicr/stockport.php\n";
