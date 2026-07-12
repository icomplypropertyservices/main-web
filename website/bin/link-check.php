<?php
/**
 * Local link + asset checker for the iComply site.
 * Usage: php bin/link-check.php [baseUrl]
 */
declare(strict_types=1);

$base = rtrim($argv[1] ?? 'http://localhost:8000', '/');
$root = dirname(__DIR__);

$broken = [];
$ok = 0
;
$checked = [];
$warnings = [];

function http_status(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'icomply-link-check/1.0',
        CURLOPT_HEADER => true,
    ]);
    $raw = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $body = '';
    if (is_string($raw)) {
        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($raw, $headerSize);
    }
    curl_close($ch);
    return [$code, $err, $ctype, $body];
}

function check_url(string $url, string $from, array &$checked, array &$broken, int &$ok): void {
    $key = $url;
    if (isset($checked[$key])) {
        return;
    }
    $checked[$key] = true;
    [$code, $err] = http_status($url);
    if ($code >= 200 && $code < 400) {
        $ok++;
        return;
    }
    $broken[] = [
        'url' => $url,
        'from' => $from,
        'code' => $code,
        'error' => $err,
    ];
}

function resolve(string $href, string $base): ?string {
    $href = trim($href);
    if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:')
        || str_starts_with($href, 'tel:') || str_starts_with($href, 'javascript:')
        || str_starts_with($href, 'data:') || str_starts_with($href, 'whatsapp:')) {
        return null;
    }
    if (str_starts_with($href, '//')) {
        return null; // external protocol-relative
    }
    if (preg_match('#^https?://#i', $href)) {
        if (stripos($href, 'localhost:8000') !== false) {
            return $href;
        }
        return null; // skip external
    }
    return rtrim($base, '/') . '/' . ltrim($href, '/');
}

function extract_links(string $html): array {
    $out = [];
    if (preg_match_all('/(?:href|src)\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
        foreach ($m[1] as $h) {
            $out[] = $h;
        }
    }
    return $out;
}

echo "Base: {$base}\n";
echo "Scanning filesystem pages...\n";

// 1) Every PHP page under website (except bin/templates/includes/api)
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$phpFiles = [];
foreach ($rii as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    if (!str_ends_with(strtolower($path), '.php')) continue;
    $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
    if (preg_match('#^(bin|templates|includes|api)/#', $rel)) continue;
    $phpFiles[] = $rel;
}
sort($phpFiles);
echo "PHP pages on disk: " . count($phpFiles) . "\n";

$crawlBodies = [];
$i = 0;
foreach ($phpFiles as $rel) {
    $i++;
    $url = $base . '/' . $rel;
    [$code, $err, $ctype, $body] = http_status($url);
    if ($code >= 200 && $code < 400) {
        $ok++;
        $checked[$url] = true;
        if (str_contains((string)$ctype, 'text/html') || str_ends_with($rel, '.php')) {
            // sample link extraction: all hubs + first 80 combo pages + all root pages
            $isHub = str_starts_with($rel, 'pages/services/') || !str_contains($rel, '/');
            $isSample = $i <= 120 || $isHub || preg_match('#pages/(electrical|fire-alarms|cctv|areas)/(manchester|stockport|liverpool|bolton)\.php#', $rel);
            if ($isSample) {
                $crawlBodies[$rel] = $body;
            }
        }
    } else {
        $broken[] = ['url' => $url, 'from' => 'filesystem', 'code' => $code, 'error' => $err];
        $checked[$url] = true;
    }
    if ($i % 200 === 0) {
        echo "  checked {$i}/" . count($phpFiles) . " pages...\n";
    }
}

// 2) Static essentials
foreach (['robots.txt', 'sitemap.xml', 'assets/images/og-image.jpg', 'assets/images/heroes/home-hero.jpg'] as $static) {
    check_url($base . '/' . $static, 'static-essentials', $checked, $broken, $ok);
}

// 3) Extract links from sampled HTML + home
[$cHome, $eHome, $tHome, $homeHtml] = http_status($base . '/');
if ($cHome >= 200 && $cHome < 400) {
    $crawlBodies['/'] = $homeHtml;
    $ok++;
} else {
    $broken[] = ['url' => $base . '/', 'from' => 'home', 'code' => $cHome, 'error' => $eHome];
}

$linkTargets = [];
foreach ($crawlBodies as $from => $html) {
    foreach (extract_links($html) as $href) {
        $abs = resolve($href, $base);
        if ($abs === null) continue;
        $linkTargets[$abs] = $from;
    }
}
echo "Unique internal link targets from samples: " . count($linkTargets) . "\n";

$j = 0;
foreach ($linkTargets as $url => $from) {
    $j++;
    check_url($url, (string)$from, $checked, $broken, $ok);
    if ($j % 100 === 0) {
        echo "  link targets checked {$j}/" . count($linkTargets) . "...\n";
    }
}

// 4) All service images on disk referenced
$serviceImgs = glob($root . '/assets/images/services/*.{jpg,png}', GLOB_BRACE) ?: [];
foreach ($serviceImgs as $img) {
    $rel = 'assets/images/services/' . basename($img);
    check_url($base . '/' . $rel, 'service-images-disk', $checked, $broken, $ok);
}

// Report
$report = [
    'base' => $base,
    'when' => date('c'),
    'php_files_on_disk' => count($phpFiles),
    'urls_checked' => count($checked),
    'ok' => $ok,
    'broken_count' => count($broken),
    'broken' => $broken,
];
$out = $root . '/bin/link-check-report.json';
file_put_contents($out, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "\n==== SUMMARY ====\n";
echo "PHP files on disk: " . count($phpFiles) . "\n";
echo "URLs checked: " . count($checked) . "\n";
echo "OK responses: {$ok}\n";
echo "Broken: " . count($broken) . "\n";
if ($broken) {
    echo "\n==== BROKEN (up to 50) ====\n";
    foreach (array_slice($broken, 0, 50) as $b) {
        echo "[{$b['code']}] {$b['url']}  (from {$b['from']})" . ($b['error'] ? " err={$b['error']}" : "") . "\n";
    }
}
echo "\nReport: {$out}\n";
exit(count($broken) > 0 ? 1 : 0);
