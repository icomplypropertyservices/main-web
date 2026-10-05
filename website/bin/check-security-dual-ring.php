<?php
/**
 * Security dual-ring P0 gate: 67 hubs, dual 269 towns, quality bar on samples.
 * Usage: php website/bin/check-security-dual-ring.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once SITE_ROOT . '/includes/security-dual-ring.php';
require_once SITE_ROOT . '/includes/gm-crawl.php';

$fail = 0;
$bad = static function (string $message) use (&$fail): void {
    echo "FAIL {$message}\n";
    $fail++;
};

$pack = securityDualRingPack();
$towns = $pack['towns'] ?? [];
$intents = $pack['intents'] ?? [];
if (count($towns) !== 269) {
    $bad('town count ' . count($towns));
}
if (count($intents) !== 67) {
    $bad('intent count ' . count($intents));
}
if (!str_contains((string)($pack['follow_on'] ?? ''), '133')) {
    $bad('P1 follow-on note missing');
}
if (($pack['nap'] ?? '') !== '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE') {
    $bad('NAP missing');
}

$words = static function (string $html): int {
    $text = trim(html_entity_decode(strip_tags($html)));
    preg_match_all("/[A-Za-z0-9']+/", $text, $m);
    return count($m[0]);
};

$gm = [];
foreach (icomplyCrawlTownNames() as $name) {
    $slug = areaSlug((string)$name);
    $gm[$slug] = true;
    if (!isset($towns[$slug])) {
        $bad('GM town missing from dual 269: ' . $slug);
    }
}

$creates = 0;
$both = 0;
$keywordOnly = 0;
foreach ($intents as $slug => $intent) {
    $body = implode(' ', $intent['paragraphs'] ?? []);
    if ($words($body) < 800) {
        $bad($slug . ' hub words ' . $words($body));
    }
    if (($intent['kind'] ?? '') === 'both') {
        $both++;
        $jobBody = implode(' ', $intent['job_paragraphs'] ?? []);
        if ($words($jobBody) < 800) {
            $bad($slug . ' job words ' . $words($jobBody));
        }
        if (!is_file(SITE_ROOT . '/pages/jobs/' . $slug . '.php')) {
            $bad('missing job stub ' . $slug);
        }
    } elseif (($intent['kind'] ?? '') === 'keyword') {
        $keywordOnly++;
        if (securityDualRingHandlesPath('/pages/jobs/' . $slug . '/stockport')) {
            $bad('keyword-only has job town ' . $slug);
        }
    } else {
        $bad('bad kind ' . $slug);
    }
    if (count($intent['images'] ?? []) < 3) {
        $bad($slug . ' images');
    }
    if (count($intent['faqs'] ?? []) < 3) {
        $bad($slug . ' faqs');
    }
    $meta = (string)($intent['meta_hub'] ?? '');
    if (mb_strlen($meta) < 140 || mb_strlen($meta) > 160) {
        $bad($slug . ' meta length ' . mb_strlen($meta));
    }
    if (!str_contains((string)($intent['title_hub'] ?? ''), 'iComply')) {
        $bad($slug . ' title');
    }
    if (($intent['status'] ?? '') === 'create') {
        $creates++;
        if (!is_file(SITE_ROOT . '/pages/keywords/' . $slug . '.php')) {
            $bad('missing keyword stub ' . $slug);
        }
    }
    $blob = $body . ' ' . implode(' ', $intent['job_paragraphs'] ?? []);
    if (preg_match('/£|\bapproved subcontractors?\b|\bb\d{4,}\b/i', $blob)) {
        $bad('banned token in ' . $slug);
    }
    if (preg_match('/\b(NSI|SSAIB)\s+(registered|member|approved)\b/i', $blob)) {
        $bad('invented scheme claim in ' . $slug);
    }
}
if ($creates !== 15 || $both !== 55 || $keywordOnly !== 12) {
    $bad("counts create={$creates} both={$both} keyword_only={$keywordOnly}");
}

$expectNull = [
    '/pages/keywords/cctv-installation/burnley',
    '/pages/keywords/cctv-installation/stockport',
    '/pages/jobs/cctv-installation/york',
    '/pages/jobs/maglock-installation/accrington',
    '/pages/keywords/anpr-near-me/blackpool',
];
foreach ($expectNull as $path) {
    if (icomplyNonGmMatrixRedirect($path) !== null) {
        $bad('redirected dual-ring path ' . $path);
    }
}
$outside = icomplyNonGmMatrixRedirect('/pages/keywords/cctv-installation/aberdeen');
if ($outside !== '/pages/keywords/cctv-installation') {
    $bad('aberdeen should leave the security ring, got ' . var_export($outside, true));
}
$otherFamily = icomplyNonGmMatrixRedirect('/pages/keywords/eicr/york');
if ($otherFamily === null) {
    $bad('non-security york page must stay on the GM redirect');
}

$paths = securityDualRingSitemapPaths();
$nonGm = 0;
foreach ($towns as $slug => $_town) {
    if (empty($gm[$slug])) {
        $nonGm++;
    }
}
$expected = ($nonGm * 67) + ($both * 269) - count(array_filter(array_keys($gm), static fn(string $slug): bool => isset($towns[$slug])));
if (count($paths) !== $expected) {
    $bad('sitemap extra paths ' . count($paths) . ' expected ' . $expected);
}
if (in_array('/pages/keywords/cctv-installation/stockport', $paths, true)) {
    $bad('duplicate GM keyword path in security sitemap extras');
}
if (!in_array('/pages/keywords/cctv-installation/burnley', $paths, true)) {
    $bad('burnley keyword path missing');
}
if (!in_array('/pages/jobs/cctv-installation/stockport', $paths, true)) {
    $bad('job stockport path missing');
}
if (in_array('/pages/jobs/maglock-installation/stockport', $paths, true)) {
    $bad('maglock GM job duplicated');
}

$render = static function (string $surface, string $slug, string $town) use ($words, $bad): string {
    ob_start();
    securityDualRingRender($surface, $slug, $town);
    $html = (string)ob_get_clean();
    if (!str_contains($html, '<title>') || !str_contains($html, 'rel="canonical"') || !str_contains($html, 'property="og:image"') || !str_contains($html, 'property="og:title"') || !str_contains($html, 'name="description"')) {
        $bad("meta missing {$surface} {$slug} {$town}");
    }
    if (substr_count($html, '<img ') < 3) {
        $bad("images {$surface} {$slug} {$town}");
    }
    if (!str_contains($html, 'data-seo-faq="1"') || !str_contains($html, 'data-seo-body="1"')) {
        $bad("markers {$surface} {$slug} {$town}");
    }
    if (!preg_match('/<article id="local-copy" data-seo-body="1".*?<\/article>/s', $html, $m)) {
        $bad("article {$surface} {$slug} {$town}");
        return $html;
    }
    if ($words($m[0]) < 800) {
        $bad("rendered words {$words($m[0])} {$surface} {$slug} {$town}");
    }
    if (preg_match('/£|\bapproved subcontractors?\b|\bb\d{4,}\b/i', $m[0])) {
        $bad("banned in render {$slug}");
    }
    if (!str_contains($html, 'https://')) {
        $bad("canonical/og not absolute {$slug}");
    }
    if (preg_match('/<meta name="description" content="([^"]*)"/', $html, $desc)) {
        $decoded = html_entity_decode($desc[1], ENT_QUOTES, 'UTF-8');
        $len = mb_strlen($decoded);
        if ($len < 140 || $len > 160) {
            $bad('rendered meta ' . $len . " {$slug} {$town} {$decoded}");
        }
    }
    return $html;
};

$render('keyword', 'cctv-installation', '');
$render('keyword', 'cctv-installation', 'burnley');
$render('keyword', 'cctv-monitoring', 'stockport');
$render('job', 'maglock-installation', '');
$render('job', 'anpr-installation', 'york');
$render('keyword', 'emergency-cctv-repair', 'ashton-under-lyne');

if ($fail > 0) {
    echo "FAILED {$fail}\n";
    exit(1);
}
echo "OK security dual-ring P0 towns=269 intents=67 sitemap_extra=" . count($paths) . "\n";
exit(0);
