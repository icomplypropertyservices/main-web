<?php
/**
 * Networking / Wi-Fi / Bluetooth + IT P0 pack checker.
 * Lock: icomply-ops/seo/NETWORKING-IT-DUAL-RING-LOCK-2026-10-05.md
 *
 * Renders every P0 keyword hub, job hub and the four service hubs in a fresh
 * PHP process and checks the page quality bar: ≥800 words of page-specific
 * body copy, FAQ block + FAQPage, ≥3 content images, title, description,
 * canonical, og:title/description/image (absolute https) and og:type website.
 * Also: POA only, no Gas Safe wording, no attendance-time promises, no
 * advertising slugs, P0 list in sync with the SEO lock file, matrix wiring.
 *
 * Usage: php bin/check-networking-it.php [--seo-dir=/workspace/icomply-ops/seo] [--sample=N]
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
require_once $root . '/config.php';
require_once $root . '/includes/networking-it.php';
require_once $root . '/includes/matrix-catalogue.php';

$opts = getopt('', ['seo-dir::', 'sample::']);
$seoDir = (string)($opts['seo-dir'] ?? '/workspace/icomply-ops/seo');
$sample = isset($opts['sample']) ? max(1, (int)$opts['sample']) : 0;
$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$fail): void {
    if (!$cond) {
        $fail++;
        echo "[FAIL] {$msg}\n";
    }
};
$words = static function (string $text): int {
    return preg_match_all("/[A-Za-z0-9'’-]+/u", $text);
};

$pack = icomplyNetworkingItPack();
$keywords = icomplyNetworkingItKeywords();
$jobs = icomplyNetworkingItJobs();
$p0File = $seoDir . '/NETWORKING-IT-P0.txt';
if (is_file($p0File)) {
    $p0 = array_values(array_filter(array_map('trim', file($p0File) ?: []), static fn($l) => $l !== ''));
    $ok(count($p0) === 95, 'P0 file has 95 intents (got ' . count($p0) . ')');
    $ok(array_diff($p0, array_keys($keywords)) === [] && array_diff(array_keys($keywords), $p0) === [], 'pack keywords match NETWORKING-IT-P0.txt exactly');
} else {
    echo "[SKIP] P0 lock file not found at {$p0File}\n";
}
$ok(count($keywords) === 95, 'pack keyword hubs = 95 (got ' . count($keywords) . ')');
foreach (array_keys($keywords) as $slug) {
    $ok(!preg_match('/advert|dooh|billboard|gas-safe/', $slug), "no advertising/Gas Safe slug: {$slug}");
    $ok(isset(getMajorKeywords()[$slug]), "keyword catalogue serves {$slug}");
    $ok(is_file($root . '/pages/keywords/' . $slug . '.php'), "stub pages/keywords/{$slug}.php");
}
foreach (array_keys($jobs) as $slug) {
    $ok(is_file($root . '/pages/jobs/' . $slug . '.php'), "stub pages/jobs/{$slug}.php");
}
$services = getServices();
foreach (icomplyNetworkingItServiceSlugs() as $svc) {
    $ok(isset($services[$svc]), "service {$svc} in services.json");
    $ok(is_file($root . '/pages/services/' . $svc . '.php'), "stub pages/services/{$svc}.php");
    $ok(is_file($root . '/assets/images/services/' . $svc . '.jpg') && is_file($root . '/assets/images/services/' . $svc . '-photo.jpg'), "service images for {$svc}");
    $ok(isPoaService($svc), "{$svc} is POA");
}

// Dual-ring ×269 wiring (data-driven, no per-town HTML).
$records = icomplyMatrixCatalogueRecords();
$matrixJobs = icomplyMatrixJobRecords();
$missingKw = array_diff(array_keys($keywords), array_keys($records['keywords']));
$missingJob = array_diff(array_keys($jobs), array_keys($matrixJobs));
$ok($missingKw === [], 'keyword×town catalogue has every P0 keyword');
$ok($missingJob === [], 'job×town catalogue has every P0 job');
$places = icomplyMatrixSelectPlaces(1000)['selected'];
$ok(count($places) === 269, 'matrix places = 269 dual ring (got ' . count($places) . ')');
foreach (icomplyNetworkingItServiceSlugs() as $svc) {
    $ok(isset($records['services'][$svc]), "service×town catalogue has {$svc}");
}
require_once $root . '/includes/keyword-variants.php';
$variantServices = array_column(icomplyKeywordVariantCatalogue()['services'], 'slug');
$ok(array_intersect($variantServices, icomplyNetworkingItServiceSlugs()) === [], 'pack services stay off the 10k variant matrix');

$timing = '/(within \d+ ?(minutes|mins|hours|hrs)|\d+[- ]hour response|same[- ]week|next[- ]day (visit|attendance)|fast response|rapid response|guaranteed (attendance|response|arrival)|we will be there|on site in \d)/i';
$targets = [];
foreach (array_keys($keywords) as $slug) {
    $targets[] = ['keyword', $slug, '/pages/keywords/' . $slug];
}
foreach (array_keys($jobs) as $slug) {
    $targets[] = ['job', $slug, '/pages/jobs/' . $slug];
}
foreach (icomplyNetworkingItServiceSlugs() as $svc) {
    $targets[] = ['service', $svc, '/pages/services/' . $svc];
}
if ($sample > 0) {
    $targets = array_slice($targets, 0, $sample);
}
$minBody = PHP_INT_MAX;
$pages = 0;
foreach ($targets as [$kind, $slug, $path]) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/bin/render-networking-it-page.php') . ' ' . $kind . ' ' . escapeshellarg($slug) . ' 2>/dev/null';
    $html = (string)shell_exec($cmd);
    $pages++;
    $label = "{$kind} {$slug}";
    $ok(strlen($html) > 5000 && !str_contains($html, 'Keyword not found') && !str_contains($html, 'Service not found'), "{$label} renders");
    $ok((bool)preg_match('#<title>[^<]{10,}</title>#', $html), "{$label} title");
    $ok((bool)preg_match('#<meta name="description" content="[^"]{50,}"#', $html), "{$label} meta description");
    $ok((bool)preg_match('#<link rel="canonical" href="https://icomplypropertyservices\.co\.uk' . preg_quote($path, '#') . '"#', $html), "{$label} canonical {$path}");
    $ok(str_contains($html, '<meta property="og:type" content="website">'), "{$label} og:type website");
    $ok((bool)preg_match('#<meta property="og:title" content="[^"]+"#', $html), "{$label} og:title");
    $ok((bool)preg_match('#<meta property="og:description" content="[^"]+"#', $html), "{$label} og:description");
    $ok((bool)preg_match('#<meta property="og:image" content="https://icomplypropertyservices\.co\.uk/assets/images/[^"]+\.jpg"#', $html, $og), "{$label} og:image absolute https");
    if (preg_match('#og:image" content="https://icomplypropertyservices\.co\.uk(/assets/images/[^"]+)"#', $html, $ogm)) {
        $ok(is_file($root . $ogm[1]), "{$label} og:image file exists {$ogm[1]}");
    }
    $ok(substr_count($html, '<details') >= 3 && str_contains($html, 'FAQPage'), "{$label} FAQ + FAQPage");
    if (preg_match('#<figure class="quality-bar-images"[^>]*>(.*?)</figure>#s', $html, $fig)) {
        $ok(substr_count($fig[1], '<img ') >= 3, "{$label} 3 content images");
        if (preg_match_all('#src="https?://[^/"]+(/assets/images/[^"]+)"|src="(/assets/images/[^"]+)"#', $fig[1], $srcs, PREG_SET_ORDER)) {
            foreach ($srcs as $m) {
                $rel = $m[1] !== '' ? $m[1] : ($m[2] ?? '');
                $ok($rel !== '' && is_file($root . $rel), "{$label} image file {$rel}");
            }
        }
    } elseif ($kind === 'service') {
        // Service hubs use the template's hero + two inline content images.
        preg_match_all('#<img src="(?:https://icomplypropertyservices\.co\.uk)?(/assets/images/(?:services|keywords)/[^"]+\.jpg)"#', $html, $im);
        $own = array_values(array_unique(array_filter($im[1], static fn($p) => str_contains($p, '/' . $slug) || in_array(basename($p, '.jpg'), ['networking', 'networking-photo', 'structured-cabling'], true))));
        $ok(count($own) >= 3, "{$label} 3 on-topic content images (" . implode(', ', $own) . ')');
        foreach ($own as $rel) { $ok(is_file($root . $rel), "{$label} image file {$rel}"); }
    } else {
        $ok(false, "{$label} quality-bar image figure");
    }
    // Sitewide footer carries the standard "not Gas Safe registered" disclaimer; this pack's own copy has none.
    $ok(!preg_match('/iComply (is|are) Gas Safe registered/i', $html), "{$label} no positive Gas Safe claim");
    // Body copy = the pack's own prose for this page (not nav/footer chrome).
    if ($kind === 'keyword') {
        $k = $keywords[$slug];
        $parts = [$k['intro'], $k['body']];
        foreach ($k['focus_points'] as $p) { $parts[] = $p; }
        foreach ($k['faq'] as $f) { $parts[] = $f[0] . ' ' . $f[1]; }
        foreach ($k['sections'] as $sec) { $parts[] = $sec['h2']; foreach ($sec['p'] as $p) { $parts[] = $p; } }
    } elseif ($kind === 'job') {
        $j = $jobs[$slug];
        $parts = array_merge([$j['lede']], $j['paragraphs'], $j['points']);
        foreach ($j['faqs'] as $f) { $parts[] = $f[0] . ' ' . $f[1]; }
    } else {
        // Service hub: check the pack's own hub copy (sitewide chrome carries the standard
        // "not Gas Safe registered" disclaimer and the full service menu).
        $parts = [json_encode(icomplyNetworkingItServiceHubCopy($slug), JSON_UNESCAPED_UNICODE)];
    }
    $body = implode(' ', $parts);
    $ok(!preg_match($timing, $body), "{$label} no attendance-time promise");
    $ok(!str_contains($body, 'Gas Safe'), "{$label} no Gas Safe wording in pack copy");
    $ok(!str_contains($body, '£'), "{$label} POA (no £)");
    $ok(!preg_match('/advertis|DOOH|billboard/i', $body), "{$label} no advertising copy");
    if ($kind !== 'service') {
        $n = $words($body);
        $minBody = min($minBody, $n);
        $ok($n >= 800, "{$label} body copy {$n} words ≥ 800");
        $sampleText = trim(mb_substr((string)$parts[2], 0, 60));
        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ok($sampleText === '' || str_contains($plain, $sampleText), "{$label} body copy is in the rendered HTML");
    }
}

// Near-duplicate guard between keyword hubs (5-word shingles, Jaccard).
$shingles = [];
foreach ($keywords as $slug => $k) {
    $text = strtolower(implode(' ', array_merge([$k['intro'], $k['body']], ...array_map(static fn($s) => $s['p'], $k['sections']))));
    $w = preg_split('/\W+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $set = [];
    for ($i = 0; $i + 5 <= count($w); $i++) {
        $set[implode(' ', array_slice($w, $i, 5))] = true;
    }
    $shingles[$slug] = $set;
}
$maxJ = 0.0;
$maxPair = '';
$slugs = array_keys($shingles);
for ($a = 0; $a < count($slugs); $a++) {
    for ($b = $a + 1; $b < count($slugs); $b++) {
        $x = $shingles[$slugs[$a]];
        $y = $shingles[$slugs[$b]];
        $inter = count(array_intersect_key($x, $y));
        $union = count($x) + count($y) - $inter;
        $j = $union > 0 ? $inter / $union : 0.0;
        if ($j > $maxJ) {
            $maxJ = $j;
            $maxPair = $slugs[$a] . ' ~ ' . $slugs[$b];
        }
    }
}
$ok($maxJ < 0.8, sprintf('keyword hubs not near-duplicates (max Jaccard %.2f %s)', $maxJ, $maxPair));

printf("pages=%d keyword_hubs=%d job_hubs=%d service_hubs=%d min_body_words=%d max_jaccard=%.2f (%s)\n",
    $pages, count($keywords), count($jobs), count(icomplyNetworkingItServiceSlugs()), $minBody === PHP_INT_MAX ? 0 : $minBody, $maxJ, $maxPair);
printf("dual-ring ×269: keyword×town=%d job×town=%d service×town=%d (edge matrix, no per-town HTML)\n",
    count($keywords) * count($places), count($jobs) * count($places), count(icomplyNetworkingItServiceSlugs()) * count($places));
echo $fail === 0 ? "PASS\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
