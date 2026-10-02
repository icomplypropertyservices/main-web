<?php
/**
 * Measure the public-page performance pass and fail if the budget slips.
 *
 *   php website/bin/check-perf.php
 */
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);
ini_set('session.save_path', sys_get_temp_dir());

$websiteRoot = dirname(__DIR__);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_SCHEME'] = 'http';

require_once $websiteRoot . '/config.php';
require_once $websiteRoot . '/includes/router.php';
require_once $websiteRoot . '/includes/matrix-page.php';

$fail = 0;
$log = [];
$say = static function (bool $ok, string $name, string $detail = '') use (&$fail, &$log): void {
    $log[] = ($ok ? 'PASS' : 'FAIL') . ' ' . $name . ($detail !== '' ? " — {$detail}" : '');
    if (!$ok) {
        $fail++;
    }
};

$util = $websiteRoot . '/assets/css/utilities.css';
$utilBytes = is_file($util) ? (int)filesize($util) : 0;
$utilGzip = $utilBytes > 0 ? strlen(gzencode((string)file_get_contents($util), 9)) : 0;
$say($utilBytes > 0 && $utilBytes < 80000, 'utilities.css under 80KB', $utilBytes . ' bytes, gzip ' . $utilGzip);
$say(!str_contains((string)file_get_contents($websiteRoot . '/includes/header.php'), 'cdn.jsdelivr.net/npm/tailwindcss'), 'header.php has no Tailwind CDN');
$say(!str_contains((string)file_get_contents($websiteRoot . '/includes/matrix-page.php'), 'cdn.jsdelivr.net/npm/tailwindcss'), 'matrix chrome has no Tailwind CDN');
$say(str_contains((string)file_get_contents($util), '.max-w-7xl{') || str_contains((string)file_get_contents($util), '.max-w-7xl '), 'utilities keep max-w-7xl');

$tailwindBytes = 2934019;
$tailwindGzip = 300244;

function perfRender(string $path): string
{
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['QUERY_STRING'] = '';
    http_response_code(200);
    ob_start();
    try {
        routerHandleRequest();
        $html = (string)ob_get_clean();
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        return 'RENDER-ERROR ' . $e->getMessage();
    }
    return icomplyPerfRewriteHtml($html);
}

/** @return array{imgs:int,lazy:int,eager:int,dims:int,perf:int,mobile:int,desktop:int,orig:int} */
function perfImageStats(string $html): array
{
    $stats = ['imgs' => 0, 'lazy' => 0, 'eager' => 0, 'dims' => 0, 'perf' => 0, 'mobile' => 0, 'desktop' => 0, 'orig' => 0, 'logos' => 0];
    $manifest = icomplyPerfManifest();
    $byWebp = [];
    foreach ($manifest as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        foreach ($entry['variants'] ?? [] as $variant) {
            if (is_array($variant) && !empty($variant['webp'])) {
                $byWebp[(string)$variant['webp']] = $entry;
            }
        }
    }
    if (!preg_match_all('/<img\b[^>]*>/i', $html, $tags)) {
        return $stats;
    }
    foreach ($tags[0] as $tag) {
        $stats['imgs']++;
        $isLogo = str_contains($tag, 'mega-logo') || (preg_match('/\bwidth="(\d+)"/', $tag, $w) && (int)$w[1] <= 64 && !str_contains($tag, 'srcset="'));
        if ($isLogo) {
            $stats['logos'] = ($stats['logos'] ?? 0) + 1;
        }
        if (preg_match('/\bloading="([^"]+)"/', $tag, $m)) {
            if ($m[1] === 'lazy') {
                $stats['lazy']++;
            } elseif ($m[1] === 'eager') {
                $stats['eager']++;
            }
        }
        if (preg_match('/\bwidth="\d+"/', $tag) && preg_match('/\bheight="\d+"/', $tag)) {
            $stats['dims']++;
        }
        if (str_contains($tag, 'data-perf="1"') && str_contains($tag, 'srcset="')) {
            $stats['perf']++;
        }
        if (!preg_match('/\bsrcset="([^"]+)"/', $tag, $srcsetMatch) || !preg_match('/\bsizes="([^"]+)"/', $tag, $sizesMatch)) {
            continue;
        }
        $candidates = [];
        foreach (explode(',', $srcsetMatch[1]) as $part) {
            $part = trim($part);
            if (preg_match('#^(\S+)\s+(\d+)w$#', $part, $c)) {
                $rel = parse_url($c[1], PHP_URL_PATH) ?: $c[1];
                $file = SITE_ROOT . $rel;
                $candidates[] = [
                    'w' => (int)$c[2],
                    'bytes' => is_file($file) ? (int)filesize($file) : 0,
                    'rel' => $rel,
                ];
            }
        }
        if (!$candidates) {
            continue;
        }
        usort($candidates, static fn($a, $b) => $a['w'] <=> $b['w']);
        $pick = static function (int $need) use ($candidates): int {
            foreach ($candidates as $c) {
                if ($c['w'] >= $need) {
                    return $c['bytes'];
                }
            }
            return $candidates[count($candidates) - 1]['bytes'];
        };
        $sizes = html_entity_decode($sizesMatch[1], ENT_QUOTES, 'UTF-8');
        $mobileCss = perfCssPx($sizes, 390);
        $desktopCss = perfCssPx($sizes, 1280);
        $stats['mobile'] += $pick($mobileCss * 2);
        $stats['desktop'] += $pick($desktopCss * 2);
        $entry = $byWebp[$candidates[0]['rel']] ?? null;
        if (is_array($entry)) {
            $stats['orig'] += (int)($entry['origBytes'] ?? 0);
        }
    }
    return $stats;
}

function perfCssPx(string $sizes, int $viewport): int
{
    $sizes = trim($sizes);
    if ($sizes === '') {
        return $viewport;
    }
    foreach (explode(',', $sizes) as $clause) {
        $clause = trim($clause);
        if (preg_match('/min-width:\s*(\d+)px\)\s*(\d+)px/', $clause, $m)) {
            if ($viewport >= (int)$m[1]) {
                return (int)$m[2];
            }
            continue;
        }
        if (preg_match('/min-width:\s*(\d+)px\)\s*(\d+)vw/', $clause, $m)) {
            if ($viewport >= (int)$m[1]) {
                return (int)round($viewport * ((int)$m[2] / 100));
            }
            continue;
        }
        if (preg_match('/^(\d+)vw$/', $clause, $m)) {
            return (int)round($viewport * ((int)$m[1] / 100));
        }
        if (preg_match('/^(\d+)px$/', $clause, $m)) {
            return (int)$m[1];
        }
    }
    return $viewport;
}

$pages = [
    'home' => perfRender('/'),
    'service' => perfRender('/pages/services/electrical'),
    'keyword' => perfRender('/pages/keywords/eicr'),
    'landlords' => perfRender('/pages/landlords'),
    'commercial' => perfRender('/pages/commercial'),
    'contact' => perfRender('/contact'),
];
$matrix = icomplyRenderKeywordTownHtml('eicr', 'Stockport');
$pages['keyword-town'] = $matrix;

$pageReport = [];
foreach ($pages as $name => $html) {
    $ok = !str_starts_with($html, 'RENDER-ERROR') && strlen($html) > 2000 && stripos($html, '<html') !== false;
    $say($ok, "render {$name}", $ok ? (strlen($html) . ' bytes') : substr($html, 0, 240));
    if (!$ok) {
        continue;
    }
    $cdn = str_contains($html, 'cdn.jsdelivr.net/npm/tailwindcss');
    $say(!$cdn, "{$name} no Tailwind CDN");
    $say(str_contains($html, '/assets/css/utilities.css'), "{$name} links utilities.css");
    $say(str_contains($html, '/assets/css/site.css'), "{$name} links site.css");
    $stats = perfImageStats($html);
    $pageReport[$name] = $stats + ['htmlBytes' => strlen($html)];
    if ($stats['imgs'] > 0) {
        $say($stats['dims'] === $stats['imgs'], "{$name} images have width and height", "{$stats['dims']}/{$stats['imgs']}");
        $contentImgs = $stats['imgs'] - $stats['logos'];
        $say($stats['lazy'] + $stats['eager'] >= $contentImgs, "{$name} images set loading", "lazy {$stats['lazy']} eager {$stats['eager']} logos {$stats['logos']}");
        $localPerf = $stats['perf'];
        $say($contentImgs === 0 || $localPerf > 0, "{$name} uses resized srcset", (string)$localPerf);
        if ($stats['orig'] > 0) {
            $say($stats['mobile'] < $stats['orig'] * 0.5, "{$name} mobile image bytes under half", $stats['mobile'] . ' vs ' . $stats['orig']);
        }
    }
    if ($name === 'home') {
        $say(str_contains($html, 'media="print"'), 'home defers utilities.css');
        $say(str_contains($html, 'class="home-hero"'), 'home hero markup present');
        $say(substr_count($html, 'fetchpriority="high"') <= 1, 'home has at most one high-priority image');
    }
    if ($name === 'service' || $name === 'keyword') {
        $say(str_contains($html, 'fetchpriority="high"'), "{$name} hero image is high priority");
        $say(!str_contains($html, 'media="print"'), "{$name} keeps utilities render-blocking");
    }
}

$blockingBefore = $tailwindGzip + strlen(gzencode((string)file_get_contents($websiteRoot . '/assets/css/site.css'), 9));
$siteGzip = strlen(gzencode((string)file_get_contents($websiteRoot . '/assets/css/site.css'), 9));
$blockingHome = $siteGzip;
$blockingInner = $utilGzip + $siteGzip;

echo implode(PHP_EOL, $log) . PHP_EOL . PHP_EOL;
echo "CSS gzip home critical: {$blockingBefore} -> {$blockingHome} bytes" . PHP_EOL;
echo "CSS gzip inner pages: {$blockingBefore} -> {$blockingInner} bytes" . PHP_EOL;
foreach ($pageReport as $name => $stats) {
    if (($stats['orig'] ?? 0) <= 0) {
        echo "IMG {$name}: none measured" . PHP_EOL;
        continue;
    }
    $pct = (int)round(100 - ($stats['mobile'] / $stats['orig'] * 100));
    echo "IMG {$name}: orig {$stats['orig']} mobile {$stats['mobile']} desktop {$stats['desktop']} ({$pct}% less on mobile, {$stats['imgs']} imgs)" . PHP_EOL;
}

$reportPath = $websiteRoot . '/data/perf-report.json';
$report = is_file($reportPath) ? json_decode((string)file_get_contents($reportPath), true) : [];
if (!is_array($report)) {
    $report = [];
}
$report['pages'] = [
    'cssGzip' => [
        'beforeHomeAndInner' => $blockingBefore,
        'afterHomeCritical' => $blockingHome,
        'afterInnerBlocking' => $blockingInner,
        'utilities' => $utilGzip,
        'site' => $siteGzip,
        'tailwindCdn' => $tailwindGzip,
    ],
    'templates' => $pageReport,
];
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
