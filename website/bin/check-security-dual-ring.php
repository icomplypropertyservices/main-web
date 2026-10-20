<?php
/**
 * Security P0 gate: 67 keyword intents on the 269-town list; job twins 301 to
 * keyword equivalents; existing live keyword copy untouched; no builder wording.
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
        if ($slug === 'maglock-installation') {
            if (securityDualRingJobRedirect('/pages/jobs/maglock-installation') !== null) {
                $bad('maglock-installation live job hub must not redirect');
            }
        } else {
            if (securityDualRingJobRedirect('/pages/jobs/' . $slug) !== '/pages/keywords/' . $slug) {
                $bad('job twin hub must 301 to keyword hub ' . $slug);
            }
            if (securityDualRingJobRedirect('/pages/jobs/' . $slug . '/burnley') !== '/pages/keywords/' . $slug . '/burnley') {
                $bad('job twin x town must 301 to keyword x town ' . $slug);
            }
            if (is_file(SITE_ROOT . '/pages/jobs/' . $slug . '.php')) {
                $bad('job twin stub still present ' . $slug);
            }
        }
    } elseif (($intent['kind'] ?? '') === 'keyword') {
        $keywordOnly++;
        if (securityDualRingJobRedirect('/pages/jobs/' . $slug . '/stockport') !== null) {
            $bad('keyword-only has job redirect ' . $slug);
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
    if (preg_match('/(?<!not )\b(NSI|SSAIB)\s+(registered|member|approved)\b/i', $blob)) {
        $bad('invented scheme claim in ' . $slug);
    }
    $faqBlob = implode(' ', array_map(static fn($f) => (string)($f['q'] ?? '') . ' ' . (string)($f['a'] ?? ''), $intent['faqs'] ?? []));
    foreach (['on this page', 'this page', 'do not claim', 'does not claim', 'do not invent', 'does not invent', 'dual ring', 'this guide', 'this town page', 'catalogue fee', 'removed url', 'job×town', 'info@', 'not gas safe'] as $phrase) {
        if (stripos($blob . ' ' . $faqBlob . ' ' . (string)($intent['meta_hub'] ?? ''), $phrase) !== false) {
            $bad("builder wording '{$phrase}' in {$slug}");
        }
    }
}
if ($creates !== 15 || $both !== 55 || $keywordOnly !== 12) {
    $bad("counts create={$creates} both={$both} keyword_only={$keywordOnly}");
}

$expectNull = [
    '/pages/keywords/cctv-installation/burnley',
    '/pages/keywords/cctv-installation/stockport',
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
// Main now keeps every family on the dual 269 ring (York is local), so test a town outside it.
$otherFamily = icomplyNonGmMatrixRedirect('/pages/keywords/eicr/birmingham');
if ($otherFamily === null) {
    $bad('non-security birmingham page must stay on the local-ring redirect');
}

$paths = securityDualRingSitemapPaths();
$nonGm = 0;
foreach ($towns as $slug => $_town) {
    if (empty($gm[$slug])) {
        $nonGm++;
    }
}
$expected = $nonGm * 67;
if (count($paths) !== $expected) {
    $bad('sitemap extra paths ' . count($paths) . ' expected ' . $expected);
}
if (in_array('/pages/keywords/cctv-installation/stockport', $paths, true)) {
    $bad('duplicate GM keyword path in security sitemap extras');
}
if (!in_array('/pages/keywords/cctv-installation/burnley', $paths, true)) {
    $bad('burnley keyword path missing');
}
foreach ($paths as $path) {
    if (str_starts_with($path, '/pages/jobs/')) {
        $bad('job twin listed in sitemap ' . $path);
        break;
    }
}
$lines = securityDualRingRedirectLines();
if (!str_contains($lines, '/pages/jobs/cctv-installation/:town    /pages/keywords/cctv-installation/:town    301!')) {
    $bad('redirect lines missing job x town 301');
}
if (str_contains($lines, '/pages/jobs/maglock-installation ')) {
    $bad('maglock-installation must keep its live job hub');
}
// Existing live keyword copy is never overwritten by the security pack.
$kw = getMajorKeywords();
foreach ($intents as $slug => $intent) {
    if (($intent['status'] ?? '') === 'existing' && !empty($kw[$slug]['security_dual_ring_p0'])) {
        $bad('existing live keyword copy overwritten ' . $slug);
    }
    if (!isset($kw[$slug])) {
        $bad('keyword missing from catalogue ' . $slug);
    }
}

if ($fail > 0) {
    echo "FAILED {$fail}\n";
    exit(1);
}
echo "OK security dual-ring P0 towns=269 intents=67 sitemap_extra=" . count($paths) . "\n";
exit(0);
