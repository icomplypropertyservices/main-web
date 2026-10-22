<?php
/**
 * Margin DEEP pack checker (data/margin-deep/{pack}.json; locks in icomply-ops/seo, index
 * PROPERTY-SEO-LOCK-INDEX-2026-10-05.md).
 *
 * Checks every pack (or --pack=id): P0 list 1:1 with the lock P0 file when --seo-dir has it
 * (else the pack's own p0_count), catalogue + stubs + images, geo (dual-ring 269 kept,
 * outside towns → hub, manufacturer × job heads GM core 60 only), indexability, superseded
 * job URLs 301 to the keyword URLs, the quality bar on the pack copy (≥800 body words,
 * ≥3 FAQs, unique title / meta 140–160 / intro, POA only, no attendance-time promises,
 * no doubled words, no scaffold text, no wrong-service words, no registration claims,
 * gas wording only as "carried out by Gas Safe registered engineers"), sibling overlap
 * (5-word shingle Jaccard < 0.8; difflib is in check-margin-deep-sim.py), the ×town
 * renderer, and rendered hubs through the full site template.
 *
 * Usage: php bin/check-margin-deep.php [--pack=fencing] [--seo-dir=/workspace/icomply-ops/seo] [--sample=N]
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

$opts = getopt('', ['pack::', 'seo-dir::', 'sample::']);
$only = (string)($opts['pack'] ?? '');
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
$wordCount = static fn(string $t): int => preg_match_all("/[A-Za-z0-9'’-]+/u", $t) ?: 0;
$timingRe = '/(within \d+ ?(minutes|mins|hours|hrs|days)|\d+[- ]hour (response|call-?out)|same[- ]day|same[- ]week|next[- ]day|24\/7|24-hour|round[- ]the[- ]clock|fast response|rapid response|guaranteed (attendance|response|arrival)|we will be there|on site in \d|response time of)/i';
$priceRe = '/£\s*\d|\bfixed[- ]price\b|\bfrom only\b|\bcheapest\b/i';
$scaffoldRe = '/\{[A-Za-z_]+\}|\bTODO\b|\bTBD\b|\blorem\b|\bslugs?\b|\bplaceholder\b|\bjson\b|\b(P0|P1|DEEP|SEO|WT)\b|undefined|Array\b/';
$doubleRe = '/\b(\w+)\s+\1\b/i';
$certRe = '/\b(we are|iComply is|iComply are|our company is)\s+(an? )?(accredited|certified|registered|approved)\b/i';
$nap = '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE';

$packs = icomplyMarginDeepPacks();
if ($only !== '') {
    $packs = array_values(array_filter($packs, static fn(array $p): bool => ($p['pack'] ?? '') === $only));
}
$ok($packs !== [], 'margin DEEP packs found' . ($only !== '' ? " for {$only}" : ''));

$towns = json_decode((string)file_get_contents($root . '/data/dual-ring-allowlist.json'), true);
$townRows = (array)($towns['towns'] ?? $towns);
$gmTown = '';
$ringTown = '';
foreach ($townRows as $row) {
    if (($row['bucket'] ?? '') === 'gm_core' && $gmTown === '') {
        $gmTown = (string)$row['slug'];
    }
    if (($row['bucket'] ?? '') !== 'gm_core' && $ringTown === '') {
        $ringTown = (string)$row['slug'];
    }
}
$ok(count($townRows) === 269, 'dual-ring allowlist has 269 towns (got ' . count($townRows) . ')');
$outside = 'london';
$ok(icomplyMarginDeepTownRow($outside) === null, 'london is outside the dual ring');

$catalogue = getMajorKeywords();
$summary = [];
foreach ($packs as $pack) {
    $id = (string)$pack['pack'];
    $slugs = icomplyMarginDeepSlugs($id);
    $kw = (array)$pack['keywords'];
    $expect = (int)($pack['p0_count'] ?? 0);
    $banned = (string)($pack['banned'] ?? '');

    // 1. Lock sync.
    $p0File = $seoDir . '/' . (string)($pack['p0_source'] ?? '');
    if (($pack['p0_source'] ?? '') !== '' && is_file($p0File)) {
        $p0 = array_values(array_filter(array_map('trim', file($p0File) ?: []), static fn($l) => $l !== ''));
        $ok($p0 === $slugs, "{$id}: hubs match {$pack['p0_source']} 1:1 in lock order");
        $ok(is_file($seoDir . '/' . (string)$pack['lock']), "{$id}: lock file {$pack['lock']} present");
    } else {
        echo "[SKIP] {$id}: P0 lock file not found at {$p0File}; checking the pack's own count only\n";
    }
    $ok($expect > 0 && count($slugs) === $expect, "{$id}: hubs = {$expect} (got " . count($slugs) . ')');
    $ok(count(array_unique($slugs)) === count($slugs), "{$id}: hub slugs unique");
    $ok(array_keys($kw) === $slugs, "{$id}: keyword copy covers every hub, no extras");

    // 2. Catalogue, stubs, images, geo, routing.
    $gmOnly = 0;
    foreach ($slugs as $slug) {
        $ok(isset($catalogue[$slug]), "{$id}: catalogue serves {$slug}");
        $ok(($catalogue[$slug]['body'] ?? null) === ($kw[$slug]['body'] ?? ''), "{$id}: catalogue uses DEEP copy for {$slug}");
        $stub = $root . '/pages/keywords/' . $slug . '.php';
        $ok(is_file($stub) && str_contains((string)file_get_contents($stub), "renderKeywordPage('{$slug}')"), "{$id}: stub pages/keywords/{$slug}.php");
        $images = icomplyMarginDeepImages($slug);
        $ok(count($images) === 3 && count(array_unique($images)) === 3, "{$id}: {$slug} has 3 distinct images");
        foreach ($images as $image) {
            $ok(is_file($root . $image), "{$id}: {$slug} image {$image} exists");
        }
        $hub = '/pages/keywords/' . $slug;
        $ok(icomplyPathIsIndexable($hub), "{$id}: {$slug} hub indexable");
        if (icomplyMarginDeepGmOnly($slug)) {
            $gmOnly++;
            $ok(icomplyNonGmMatrixRedirect("{$hub}/{$gmTown}") === null, "{$id}: {$slug} × GM {$gmTown} kept");
            $ok(icomplyNonGmMatrixRedirect("{$hub}/{$ringTown}") === $hub, "{$id}: manufacturer head {$slug} × ring {$ringTown} → hub");
        } else {
            $ok(icomplyNonGmMatrixRedirect("{$hub}/{$gmTown}") === null, "{$id}: {$slug} × GM {$gmTown} kept");
            $ok(icomplyNonGmMatrixRedirect("{$hub}/{$ringTown}") === null, "{$id}: {$slug} × ring {$ringTown} kept");
            $ok(icomplyPathIsIndexable("{$hub}/{$ringTown}"), "{$id}: {$slug} × {$ringTown} indexable");
        }
        $ok(icomplyNonGmMatrixRedirect("{$hub}/{$outside}") === $hub, "{$id}: {$slug} × {$outside} → hub");
    }
    foreach ((array)($pack['redirects'] ?? []) as $from => $to) {
        $ok(icomplyMarginDeepRedirect((string)$from) === (string)$to, "{$id}: 301 {$from} → {$to}");
        $ok(in_array(preg_replace('#^/pages/keywords/#', '', (string)$to), $slugs, true), "{$id}: redirect target {$to} is a DEEP hub");
    }

    // 3. Copy quality bar.
    $titles = [];
    $metas = [];
    $intros = [];
    $shingles = [];
    $minBody = PHP_INT_MAX;
    $maxBody = 0;
    foreach ($slugs as $slug) {
        $k = (array)$kw[$slug];
        $faq = (array)($k['faq'] ?? []);
        $parts = [(string)$k['intro'], (string)$k['body']];
        foreach ((array)($k['focus_points'] ?? []) as $p) {
            $parts[] = (string)$p;
        }
        foreach ($faq as $f) {
            $parts[] = $f[0] . ' ' . $f[1];
        }
        $prose = implode(' ', $parts);
        $body = $wordCount((string)$k['body']);
        $minBody = min($minBody, $body);
        $maxBody = max($maxBody, $body);
        $ok($body >= 800, "{$id}: {$slug} body words {$body} ≥ 800");
        $ok(count($faq) >= 3, "{$id}: {$slug} FAQs " . count($faq) . ' ≥ 3');
        $title = (string)($k['seo_title'] ?? '');
        $meta = (string)($k['meta_desc'] ?? '');
        $ok(str_contains($title, 'iComply') && strlen($title) <= 70, "{$id}: {$slug} title '{$title}'");
        $ok(strlen($meta) >= 140 && strlen($meta) <= 160, "{$id}: {$slug} meta length " . strlen($meta));
        $ok(str_contains($meta, 'POA'), "{$id}: {$slug} meta says POA");
        $titles[$title] = true;
        $metas[$meta] = true;
        $intros[(string)$k['intro']] = true;
        $all = $prose . ' ' . $title . ' ' . $meta . ' ' . (string)($k['h1'] ?? '');
        $m = [];
        $ok(!preg_match($timingRe, $all, $m), "{$id}: {$slug} no attendance-time promise" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
        $m = [];
        $ok(!preg_match($priceRe, $all, $m), "{$id}: {$slug} POA only" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
        $m = [];
        $ok(!preg_match($scaffoldRe, $all, $m), "{$id}: {$slug} no scaffold text" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
        $m = [];
        $ok(!preg_match($doubleRe, $all, $m), "{$id}: {$slug} no doubled words" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
        $ok(!preg_match($certRe, $all), "{$id}: {$slug} no registration claim");
        if ($banned !== '') {
            $m = [];
            $ok(!preg_match('/' . str_replace('/', '\/', $banned) . '/i', $all, $m), "{$id}: {$slug} no wrong-service copy" . (isset($m[0]) ? " ('{$m[0]}')" : ''));
        }
        if (preg_match('/gas safe/i', $all)) {
            $ok(str_contains($all, 'carried out by Gas Safe registered engineers'), "{$id}: {$slug} gas wording");
        }
        $tokens = preg_split('/\W+/u', strtolower((string)$k['intro'] . ' ' . (string)$k['body']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $set = [];
        for ($i = 0; $i + 5 <= count($tokens); $i++) {
            $set[implode(' ', array_slice($tokens, $i, 5))] = true;
        }
        $shingles[$slug] = $set;
    }
    $ok(count($titles) === count($slugs), "{$id}: unique titles " . count($titles));
    $ok(count($metas) === count($slugs), "{$id}: unique meta descriptions " . count($metas));
    $ok(count($intros) === count($slugs), "{$id}: unique intros " . count($intros));
    $maxJ = 0.0;
    $maxPair = '';
    $n = count($slugs);
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
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
    $ok($maxJ < 0.8, sprintf('%s: max sibling 5-shingle Jaccard %.2f < 0.80 (%s)', $id, $maxJ, $maxPair));

    // 4. ×town renderer (PHP twin of netlify/lib/margin-deep.js).
    $minTown = PHP_INT_MAX;
    foreach ($slugs as $slug) {
        $town = icomplyMarginDeepGmOnly($slug) ? $gmTown : $ringTown;
        $page = icomplyMarginDeepTownPage($slug, $town);
        $label = "{$id}: town {$slug}/{$town}";
        $ok($page !== null, "{$label} renders");
        if ($page === null) {
            continue;
        }
        $html = (string)$page['html'];
        $ok(str_contains($html, 'rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/' . $slug . '/' . $town . '"'), "{$label} canonical");
        $ok(str_contains($html, '<meta name="robots" content="index, follow">'), "{$label} index,follow");
        $ok(str_contains($html, '<meta property="og:image" content="https://icomplypropertyservices.co.uk/assets/images/'), "{$label} og:image absolute");
        $len = strlen(html_entity_decode((string)$page['description'], ENT_QUOTES, 'UTF-8'));
        $ok($len >= 140 && $len <= 160, "{$label} meta length {$len}");
        preg_match('/<article id="local-copy">(.*?)<\/article>/s', $html, $art);
        $w = $wordCount(strip_tags($art[1] ?? ''));
        $minTown = min($minTown, $w);
        $ok($w >= 800, "{$label} words {$w}");
        $ok(substr_count($html, '<img ') >= 3, "{$label} 3 images");
        $ok(str_contains($html, '"FAQPage"') && substr_count($html, '<h3>') >= 4, "{$label} FAQ + FAQPage");
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $ok(!preg_match($timingRe, $text), "{$label} no attendance-time promise");
        $ok(!preg_match('/£\s*\d/', $text), "{$label} no £");
        $ok(str_contains($text, $nap), "{$label} NAP");
        $ok(str_contains($html, 'https://wa.me/447517806082') && str_contains($html, 'mailto:info@icomplypropertyservices.co.uk'), "{$label} WhatsApp + email");
        $ok(icomplyMarginDeepTownPage($slug, $outside) === null, "{$id}: {$slug} × {$outside} does not render");
    }

    // 5. Rendered hubs (fresh PHP process each, full site template).
    $targets = $sample >= 0 ? array_slice($slugs, 0, $sample) : $slugs;
    foreach ($targets as $slug) {
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/bin/render-margin-deep-page.php') . ' hub ' . escapeshellarg($slug) . ' 2>/dev/null';
        $html = (string)shell_exec($cmd);
        $label = "{$id}: hub {$slug}";
        $k = (array)$kw[$slug];
        $ok(strlen($html) > 5000 && !str_contains($html, 'Keyword not found'), "{$label} renders");
        $ok(str_contains($html, '<title>' . htmlspecialchars((string)$k['seo_title'], ENT_QUOTES, 'UTF-8') . '</title>'), "{$label} title from pack");
        preg_match('/<meta name="description" content="([^"]*)"/', $html, $d);
        $ok(html_entity_decode($d[1] ?? '', ENT_QUOTES, 'UTF-8') === (string)$k['meta_desc'], "{$label} meta description from pack");
        $ok(str_contains($html, '<link rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/' . $slug . '">'), "{$label} canonical");
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
            $ok($srcs[1] === icomplyMarginDeepImages($slug), "{$label} content figure uses the 3 DEEP images");
        } else {
            $ok(false, "{$label} content image figure");
        }
        $ok(str_contains($html, $nap), "{$label} NAP");
        $ok(str_contains($html, 'wa.me/447517806082') && str_contains($html, 'mailto:info@icomplypropertyservices.co.uk'), "{$label} WhatsApp + email");
    }
    $summary[] = sprintf('%s hubs=%d gm60=%d body_words=%d-%d min_town_words=%d max_jaccard=%.2f (%s) redirects=%d hubs_rendered=%d',
        $id, count($slugs), $gmOnly, $minBody, $maxBody, $minTown, $maxJ, $maxPair, count((array)($pack['redirects'] ?? [])), count($targets));
}

echo ($fail === 0 ? 'PASS' : 'FAIL') . " ({$pass} pass, {$fail} fail)\n";
foreach ($summary as $line) {
    echo $line . "\n";
}
exit($fail === 0 ? 0 : 1);
