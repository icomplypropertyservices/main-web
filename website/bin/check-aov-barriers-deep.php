<?php
/**
 * AOV + Barriers DEEP P0 pack checker (179 hubs).
 * Lock: icomply-ops/seo/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md (corrected 23:55 —
 * barrier theme = manual barriers + WIDTH / HEIGHT; wind / Beaufort / storm / coastal
 * barrier restriction pages are withdrawn and must not ship).
 *
 * Checks: P0 list in sync with the lock file 1:1, catalogue + stubs, geo rules
 * (TOP 5000 + GM keep; manufacturer heads GM core 60 only), page quality bar
 * (≥800 words of page-specific prose, ≥3 FAQs + FAQPage, 3 on-topic images,
 * unique title / meta 140–160 / canonical / og:*), POA only, no attendance-time
 * promises, no £ prices, no Gas Safe wording, zero wind-theme barrier copy,
 * sibling overlap (5-word shingle Jaccard < 0.8), and the ×town renderer.
 *
 * Usage: php bin/check-aov-barriers-deep.php [--seo-dir=/workspace/icomply-ops/seo] [--sample=N]
 *   --sample=N renders only the first N hubs in a fresh PHP process (default: all 179).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}
$root = dirname(__DIR__);
if (!getenv('SITE_URL')) {
    putenv('SITE_URL=https://icomplypropertyservices.co.uk');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once $root . '/config.php';
require_once $root . '/includes/router.php';

$opts = getopt('', ['seo-dir::', 'sample::']);
$seoDir = (string)($opts['seo-dir'] ?? '/workspace/icomply-ops/seo');
$sample = isset($opts['sample']) ? max(0, (int)$opts['sample']) : -1;

$pass = 0;
$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        return;
    }
    $fail++;
    echo "[FAIL] {$msg}\n";
};
$wordCount = static fn(string $t): int => preg_match_all("/[A-Za-z0-9'’—-]+/u", $t) ?: 0;

// Wind-theme barrier restriction copy is withdrawn (lock correction). Word boundaries keep "window" legal.
$windRe = '/\b(wind|winds|windy|wind-?load(?:ing|s)?|beaufort|storms?|stormy|coastal|coast|hurricanes?|gales?|gusts?|gusting|exposed sites?)\b/i';
$timingRe = '/(within \d+ ?(minutes|mins|hours|hrs)|\d+[- ]hour response|same[- ]day|same[- ]week|next[- ]day|24\/7|24-hour|round[- ]the[- ]clock|fast response|rapid response|guaranteed (attendance|response|arrival)|on site in \d+)/i';
$EXPECT = 179;
$GM_ONLY = ['came-barrier-installation', 'colt-aov-installation', 'se-controls-aov-installation'];

$pack = icomplyAovBarriersDeepPack();
$hubs = icomplyAovBarriersDeepHubs();
$kw = icomplyAovBarriersDeepKeywords();
$slugs = icomplyAovBarriersDeepSlugs();

// 1. Lock sync.
$lockFile = $seoDir . '/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md';
$p0File = $seoDir . '/AOV-BARRIERS-DEEP-P0.txt';
if (is_file($p0File)) {
    $p0 = array_values(array_filter(array_map('trim', file($p0File) ?: []), static fn($l) => $l !== ''));
    $ok(count($p0) === $EXPECT, "P0 file has {$EXPECT} slugs (got " . count($p0) . ')');
    $ok($p0 === $slugs, 'pack hubs match AOV-BARRIERS-DEEP-P0.txt 1:1 (same order)');
    $ok(is_file($lockFile), 'lock file present');
} else {
    echo "[SKIP] P0 lock file not found at {$p0File} — checking the pack's own count only\n";
}
$ok(count($slugs) === $EXPECT, "pack hubs = {$EXPECT} (got " . count($slugs) . ')');
$ok(array_keys($kw) === $slugs, 'pack keyword copy covers every hub, no extras');
$ok(str_contains((string)($pack['lock'] ?? ''), 'AOV-BARRIERS-DEEP-LOCK-2026-10-05'), 'pack cites the lock');
$ok(stripos((string)($pack['theme'] ?? ''), 'width') !== false && stripos((string)($pack['theme'] ?? ''), 'height') !== false, 'pack theme is manual + width/height');

// 2. Catalogue, stubs, images, geo.
$catalogue = getMajorKeywords();
$lines = ['aov' => 0, 'barrier' => 0];
$gmOnly = [];
foreach ($slugs as $slug) {
    $hub = $hubs[$slug];
    $lines[$hub['line']] = ($lines[$hub['line']] ?? 0) + 1;
    $ok(!preg_match('/(^|-)(wind|beaufort|storm|coastal|hurricane|gale|exposed)(-|$)/', $slug), "no wind-theme slug: {$slug}");
    $ok(!preg_match('/fenc|perimeter|gate|hmo-licen|facial|gap-fill/', $slug) || str_starts_with($slug, 'hmo-'), "no out-of-pack slug: {$slug}");
    $ok(isset($catalogue[$slug]), "catalogue serves {$slug}");
    $ok(($catalogue[$slug]['body'] ?? '') === ($kw[$slug]['body'] ?? null), "catalogue uses DEEP copy for {$slug}");
    $ok(is_file($root . '/pages/keywords/' . $slug . '.php'), "stub pages/keywords/{$slug}.php");
    $ok(icomplyNationwide3lineIsP0($slug), "{$slug} uses the nationwide hub template");
    $images = icomplyAovBarriersDeepImages($slug);
    $ok(count($images) === 3 && count(array_unique($images)) === 3, "{$slug} has 3 distinct images");
    $ok(icomplyNationwide3lineImages($slug) === $images, "{$slug} DEEP images win on overlap");
    foreach ($images as $image) {
        $ok(is_file($root . $image), "{$slug} image {$image}");
        $ok(!preg_match('#/(cctv|gas-systems|electrical|fencing)[^/]*\.jpg$#', $image), "{$slug} image on-topic {$image}");
    }
    if (($hub['geo'] ?? '') === 'gm60') {
        $gmOnly[] = $slug;
    } else {
        $ok(($hub['geo'] ?? '') === 'top5000', "{$slug} geo top5000");
    }
}
sort($gmOnly);
$ok($gmOnly === $GM_ONLY, 'manufacturer × job heads are GM core 60 only: ' . implode(', ', $gmOnly));
$ok($lines['aov'] + $lines['barrier'] === $EXPECT, "lines aov={$lines['aov']} barrier={$lines['barrier']}");

// 3. Copy quality bar (pack's own prose).
$titles = [];
$metas = [];
$intros = [];
$shingles = [];
$minWords = PHP_INT_MAX;
$minBody = PHP_INT_MAX;
foreach ($slugs as $slug) {
    $k = $kw[$slug];
    $faq = (array)($k['faq'] ?? []);
    $parts = [(string)$k['intro'], (string)$k['body']];
    foreach ((array)($k['focus_points'] ?? []) as $p) {
        $parts[] = (string)$p;
    }
    foreach ($faq as $f) {
        $parts[] = $f[0] . ' ' . $f[1];
    }
    $prose = implode(' ', $parts);
    $words = $wordCount($prose);
    $bodyWords = $wordCount((string)$k['body']);
    $minWords = min($minWords, $words);
    $minBody = min($minBody, $bodyWords);
    $ok($bodyWords >= 800, "{$slug} body words {$bodyWords} ≥ 800");
    $ok(count($faq) >= 3, "{$slug} FAQs " . count($faq) . ' ≥ 3');
    $title = (string)($k['seo_title'] ?? '');
    $meta = (string)($k['meta_desc'] ?? '');
    $ok(str_contains($title, 'iComply') && strlen($title) <= 70, "{$slug} title '{$title}'");
    $ok(strlen($meta) >= 140 && strlen($meta) <= 160, "{$slug} meta length " . strlen($meta));
    $ok(str_contains($meta, 'POA'), "{$slug} meta says POA");
    $titles[$title] = true;
    $metas[$meta] = true;
    $intros[(string)$k['intro']] = true;
    $all = $prose . ' ' . $title . ' ' . $meta . ' ' . (string)($k['h1'] ?? '') . ' ' . (string)($k['seo_keywords'] ?? '');
    $ok(!preg_match($windRe, $all, $m), "{$slug} no wind-theme copy" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
    $m = [];
    $ok(!preg_match($timingRe, $all, $m), "{$slug} no attendance-time promise" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
    $ok(!preg_match('/£\s*\d|fixed[- ]price/i', $all), "{$slug} POA only (no £ / fixed price)");
    $ok(!preg_match('/gas safe/i', $all), "{$slug} no Gas Safe wording");
    $ok(!str_contains(strtolower($all), 'approved subcontractor'), "{$slug} no approved-subcontractor wording");
    $tokens = preg_split('/\W+/u', strtolower((string)$k['intro'] . ' ' . (string)$k['body']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $set = [];
    for ($i = 0; $i + 5 <= count($tokens); $i++) {
        $set[implode(' ', array_slice($tokens, $i, 5))] = true;
    }
    $shingles[$slug] = $set;
}
$ok(count($titles) === $EXPECT, 'unique titles ' . count($titles));
$ok(count($metas) === $EXPECT, 'unique meta descriptions ' . count($metas));
$ok(count($intros) === $EXPECT, 'unique intros ' . count($intros));
$maxJ = 0.0;
$maxPair = '';
for ($i = 0; $i < count($slugs); $i++) {
    for ($j = $i + 1; $j < count($slugs); $j++) {
        $a = $shingles[$slugs[$i]];
        $b = $shingles[$slugs[$j]];
        $inter = count(array_intersect_key($a, $b));
        $union = count($a) + count($b) - $inter;
        $jac = $union > 0 ? $inter / $union : 0.0;
        if ($jac > $maxJ) {
            $maxJ = $jac;
            $maxPair = $slugs[$i] . ' ~ ' . $slugs[$j];
        }
    }
}
$ok($maxJ < 0.8, sprintf('max sibling 5-shingle Jaccard %.2f < 0.80 (%s)', $maxJ, $maxPair));

// 4. Geo + routing.
$towns = icomplyTop5000Towns();
$gm = [];
foreach (icomplyCrawlTownNames() as $name) {
    $gm[areaSlug((string)$name)] = true;
}
$nonGmTop = 0;
foreach ($towns as $town) {
    if (!isset($gm[(string)$town['slug']])) {
        $nonGmTop++;
    }
}
$extra = icomplyAovBarriersDeepExtraTownPaths($gm);
$expectedExtra = ($EXPECT - count($GM_ONLY)) * $nonGmTop;
$ok(count($extra) === $expectedExtra, 'DEEP extra town paths ' . count($extra) . " = {$expectedExtra}");
$ok(count(icomplyNationwide3lineP0Slugs()) === 76, '3-line P0 slug list unchanged (76)');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/manual-height-barrier/leeds') === null, 'TOP5000 Leeds kept');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/width-restriction-barrier/cardiff') === null, 'TOP5000 Cardiff kept');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/aov-control-panel-installation/chorlton') === null, 'GM Chorlton kept');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/manual-height-barrier/belfast') === '/pages/keywords/manual-height-barrier', 'Belfast (outside TOP5000) → hub');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/colt-aov-installation/cardiff') === '/pages/keywords/colt-aov-installation', 'manufacturer head × Cardiff → hub (GM 60 only)');
$ok(icomplyNonGmMatrixRedirect('/pages/keywords/came-barrier-installation/stockport') === null, 'manufacturer head × Stockport kept');
$ok(icomplyPathIsIndexable('/pages/keywords/manual-height-barrier/leeds'), 'DEEP × Leeds indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/colt-aov-installation/leeds'), 'manufacturer head × Leeds not indexable');
$ok(icomplyPathIsIndexable('/pages/keywords/se-controls-aov-installation/bolton'), 'manufacturer head × Bolton indexable');
$ok(!icomplyPathIsIndexable('/pages/keywords/eicr/stockport'), 'other keyword×town stays noindex');

// 5. ×town renderer: every hub × one kept town (in-process; the edge twin is checked by the .mjs).
$townWords = PHP_INT_MAX;
foreach ($slugs as $slug) {
    $town = in_array($slug, $GM_ONLY, true) ? 'stockport' : ($hubs[$slug]['line'] === 'aov' ? 'leeds' : 'cardiff');
    ob_start();
    icomplyAovBarriersDeepRenderTown($slug, $town);
    $html = (string)ob_get_clean();
    $label = "town {$slug}/{$town}";
    $ok(str_contains($html, 'rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/' . $slug . '/' . $town . '"'), "{$label} canonical");
    $ok(str_contains($html, '<meta property="og:image" content="https://icomplypropertyservices.co.uk/assets/images/'), "{$label} og:image absolute");
    preg_match('/<meta name="description" content="([^"]*)"/', $html, $d);
    $len = strlen(html_entity_decode($d[1] ?? '', ENT_QUOTES, 'UTF-8'));
    $ok($len >= 140 && $len <= 160, "{$label} meta length {$len}");
    preg_match('/<article id="local-copy">(.*?)<\/article>/s', $html, $art);
    $w = $wordCount(strip_tags($art[1] ?? ''));
    $townWords = min($townWords, $w);
    $ok($w >= 800, "{$label} words {$w}");
    $ok(substr_count($html, '<img ') >= 3, "{$label} 3 images");
    $ok(str_contains($html, '"FAQPage"') && substr_count($html, '<h3>') >= 4, "{$label} FAQ + FAQPage");
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
    $m = [];
    $ok(!preg_match($windRe, $text, $m), "{$label} no wind-theme copy" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
    $ok(!preg_match($timingRe, $text), "{$label} no attendance-time promise");
    $ok(!preg_match('/£\s*\d/', $text), "{$label} no £");
}
ob_start();
@icomplyAovBarriersDeepRenderTown('colt-aov-installation', 'leeds');
$none = (string)ob_get_clean();
$ok(str_contains($none, 'not found'), 'manufacturer head × Leeds does not render');
@http_response_code(200);

// 6. Rendered hubs (fresh PHP process each — the full site template).
$targets = $sample >= 0 ? array_slice($slugs, 0, $sample) : $slugs;
$rendered = 0;
foreach ($targets as $slug) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/bin/render-aov-barriers-deep-page.php') . ' hub ' . escapeshellarg($slug) . ' 2>/dev/null';
    $html = (string)shell_exec($cmd);
    $rendered++;
    $label = "hub {$slug}";
    $k = $kw[$slug];
    $ok(strlen($html) > 5000 && !str_contains($html, 'Keyword not found'), "{$label} renders");
    $ok(str_contains($html, '<title>' . htmlspecialchars((string)$k['seo_title'], ENT_QUOTES, 'UTF-8') . '</title>'), "{$label} title from pack");
    preg_match('/<meta name="description" content="([^"]*)"/', $html, $d);
    $ok(html_entity_decode($d[1] ?? '', ENT_QUOTES, 'UTF-8') === (string)$k['meta_desc'], "{$label} meta description from pack");
    $ok(str_contains($html, '<link rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/' . $slug . '">'), "{$label} canonical");
    $ok(str_contains($html, '<meta property="og:type" content="website">'), "{$label} og:type");
    $ok((bool)preg_match('#<meta property="og:title" content="[^"]+"#', $html), "{$label} og:title");
    $ok((bool)preg_match('#<meta property="og:description" content="[^"]+"#', $html), "{$label} og:description");
    $ok((bool)preg_match('#<meta property="og:image" content="https://icomplypropertyservices\.co\.uk(/assets/images/[^"]+)"#', $html, $og) && is_file($root . $og[1]), "{$label} og:image absolute + exists");
    $ok(str_contains($html, 'FAQPage'), "{$label} FAQPage");
    $faqSeen = 0;
    foreach ((array)$k['faq'] as $f) {
        if (str_contains($html, htmlspecialchars((string)$f[0], ENT_QUOTES, 'UTF-8'))) {
            $faqSeen++;
        }
    }
    $ok($faqSeen >= 3, "{$label} shows {$faqSeen} pack FAQs");
    if (preg_match('#<figure class="quality-bar-images"[^>]*>(.*?)</figure>#s', $html, $fig)) {
        preg_match_all('#src="(?:https?://[^/"]+)?(/assets/images/[^"]+)"#', $fig[1], $srcs);
        $ok($srcs[1] === icomplyAovBarriersDeepImages($slug), "{$label} content figure uses the 3 DEEP images");
    } else {
        $ok(false, "{$label} content image figure");
    }
    $ok(str_contains($html, '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE'), "{$label} NAP");
    if (in_array($slug, $GM_ONLY, true)) {
        $ok(!str_contains($html, '/pages/keywords/' . $slug . '/leeds"'), "{$label} does not link non-GM towns");
        $ok(str_contains($html, '/pages/keywords/' . $slug . '/stockport"'), "{$label} links GM towns");
    } else {
        $ok(str_contains($html, '/pages/keywords/' . $slug . '/leeds"'), "{$label} links TOP5000 towns");
    }
}

echo ($fail === 0 ? 'PASS' : 'FAIL') . " ({$pass} pass, {$fail} fail)\n";
printf(
    "hubs=%d aov=%d barrier=%d gm60=%d min_prose_words=%d min_body_words=%d min_town_words=%d max_jaccard=%.2f extra_town_paths=%d hubs_rendered=%d\n",
    count($slugs), $lines['aov'], $lines['barrier'], count($gmOnly), $minWords, $minBody, $townWords, $maxJ, count($extra), $rendered
);
exit($fail === 0 ? 0 : 1);
