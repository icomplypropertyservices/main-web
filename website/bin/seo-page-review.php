#!/usr/bin/env php
<?php
/**
 * Full-site SEO page review — sample every template family against hard guidelines.
 *
 * Rules (aligned with seo_title / seo_meta_description / max-seo-audit / P090 copy rules):
 * - <title> 15–60 characters
 * - meta description 70–165 characters
 * - exactly one <h1>
 * - rel=canonical
 * - JSON-LD including LocalBusiness or Organization
 * - at least one /assets/images/ reference
 * - no AI-boilerplate phrases in rendered HTML
 * - area pages expose #local-intro that is not a town-name swap of another town
 * - POA / cost pages say POA or "price on application" and do not publish a £ digit
 *
 * Usage: php website/bin/seo-page-review.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once SITE_ROOT . '/includes/matrix-page.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$boilerplate = [
    'Looking for professional',
    'Looking for expert',
    'Searching for professional',
    'peace of mind',
    'Whether you need a new',
    'comprehensive solution',
    'cutting-edge',
    'state-of-the-art',
    'one-stop shop',
    'we pride ourselves',
    "in today's",
    'tailored to your needs',
    'world-class',
    'provides complete',
];

function review_capture(callable $fn): string
{
    ob_start();
    try {
        $fn();
    } catch (Throwable $e) {
        $buf = (string)ob_get_clean();
        return $buf . "\nEXCEPTION " . $e->getMessage();
    }
    return (string)ob_get_clean();
}

function review_local_intro(string $html): string
{
    if (!preg_match('/id="local-intro"[^>]*>(.*?)<\/p>/is', $html, $m)) {
        return '';
    }
    $text = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
}

function review_issues(string $label, string $html, array $opts = []): array
{
    global $boilerplate;
    $issues = [];
    if ($html === '') {
        return ['empty'];
    }
    if (preg_match('/Fatal error|Parse error|EXCEPTION /', $html)) {
        $issues[] = 'php_error';
    }
    if (!preg_match('/<title>([^<]*)<\/title>/i', $html, $tm)) {
        $issues[] = 'title_missing';
    } else {
        $len = mb_strlen(html_entity_decode($tm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($len < 15 || $len > 60) {
            $issues[] = 'title_len:' . $len . ':' . $tm[1];
        }
    }
    if (!preg_match('/name="description" content="([^"]*)"/i', $html, $dm)) {
        $issues[] = 'meta_missing';
    } else {
        $mlen = mb_strlen(html_entity_decode($dm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($mlen < 70 || $mlen > 165) {
            $issues[] = 'meta_len:' . $mlen;
        }
    }
    $h1 = preg_match_all('/<h1[\s>]/i', $html);
    if ($h1 !== 1) {
        $issues[] = 'h1_count:' . $h1;
    }
    if (!preg_match('/rel="canonical"/i', $html)) {
        $issues[] = 'canonical';
    }
    if (!preg_match('/application\/ld\+json/i', $html)) {
        $issues[] = 'schema';
    }
    if (!preg_match('/LocalBusiness|Organization/i', $html)) {
        $issues[] = 'org_schema';
    }
    if (!preg_match('/assets\/images\//i', $html)) {
        $issues[] = 'images';
    }
    $visible = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html;
    $visible = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $visible) ?? $visible;
    $visible = html_entity_decode(strip_tags($visible), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    foreach ($boilerplate as $phrase) {
        if (stripos($visible, $phrase) !== false) {
            $issues[] = 'boilerplate:' . $phrase;
        }
    }
    if (!empty($opts['local'])) {
        $intro = review_local_intro($html);
        if ($intro === '') {
            $issues[] = 'local_intro_missing';
        } elseif (!preg_match('/[A-Z]{1,2}\d|postcode|district|SK\d|M\d|travel|minutes/i', $intro)) {
            $issues[] = 'local_depth';
        }
    }
    if (!empty($opts['poa'])) {
        if (!preg_match('/\bPOA\b|price on application/i', $html)) {
            $issues[] = 'poa_missing';
        }
        if (preg_match('/£\s?\d/', $visible)) {
            $issues[] = 'invented_price';
        }
    }
    return $issues;
}

$areas = getAreas();
$need = ['Manchester', 'Blackpool', 'Warrington', 'Bolton', 'Liverpool', 'Chester', 'Oldham', 'Wilmslow', 'Preston'];
foreach ($need as $n) {
    if (!in_array($n, $areas, true)) {
        fwrite(STDERR, "Missing area {$n}\n");
        exit(1);
    }
}

$samples = [];

$samples[] = ['home', '/', static function (): void {
    require SITE_ROOT . '/index.php';
}, []];

foreach (['electrical', 'fire-alarms', 'aov-air-handling', 'nurse-call'] as $svc) {
    $samples[] = ['service-hub', '/pages/services/' . $svc, static function () use ($svc): void {
        renderServiceHubPage($svc);
    }, []];
}

foreach (['eicr', 'aov-actuator', 'aov-actuator-installation', 'care-home-nurse-call', 'car-park-barrier-access', 'eicr-cost', 'boiler-service-cost'] as $kw) {
    $opts = in_array($kw, ['eicr-cost', 'boiler-service-cost'], true) ? ['poa' => true] : [];
    $samples[] = ['keyword', '/pages/keywords/' . $kw, static function () use ($kw): void {
        renderKeywordPage($kw);
    }, $opts];
}

$combo = [
    ['electrical', 'Manchester'],
    ['electrical', 'Blackpool'],
    ['aov-air-handling', 'Warrington'],
    ['nurse-call', 'Bolton'],
    ['fire-alarms', 'Liverpool'],
    ['legionella-risk-assessment', 'Manchester'],
    ['asbestos-survey', 'Chester'],
];
foreach ($combo as [$svc, $area]) {
    $opts = ['local' => true];
    if (in_array($svc, ['legionella-risk-assessment', 'asbestos-survey'], true)) {
        $opts['poa'] = true;
    }
    $samples[] = ['area-service', '/pages/' . $svc . '/' . areaSlug($area), static function () use ($svc, $area): void {
        renderServiceAreaPage($svc, $area);
    }, $opts];
}

foreach (['eicr', 'aov-actuator', 'care-home-nurse-call', 'car-park-barrier-access', 'eicr-cost'] as $kw) {
    foreach (['Manchester', 'Preston'] as $area) {
        $opts = ['local' => true];
        if ($kw === 'eicr-cost') {
            $opts['poa'] = true;
        }
        $samples[] = ['keyword-area', '/pages/keywords/' . $kw . '/' . areaSlug($area), static function () use ($kw, $area): void {
            renderKeywordAreaPage($kw, $area);
        }, $opts];
    }
}

$samples[] = ['landlord', '/pages/landlords', static function (): void {
    require SITE_ROOT . '/pages/landlords.php';
}, []];
foreach (['electrical-safety-landlords', 'legionella-landlords', 'asbestos-landlords'] as $hub) {
    $opts = $hub === 'electrical-safety-landlords' ? [] : ['poa' => true];
    $samples[] = ['landlord', '/pages/' . $hub, static function () use ($hub): void {
        require SITE_ROOT . '/pages/' . $hub . '.php';
    }, $opts];
}

foreach (['Manchester', 'Wilmslow'] as $area) {
    $samples[] = ['area-hub', '/pages/areas/' . areaSlug($area), static function () use ($area): void {
        renderAreaHubPage($area);
    }, ['local' => true]];
}

$matrixPairs = [];
foreach ([
    ['aov-actuator', 'Manchester'],
    ['aov-actuator', 'Liverpool'],
    ['care-home-nurse-call', 'Bolton'],
    ['car-park-barrier-access', 'Preston'],
] as [$kw, $area]) {
    $html = icomplyRenderKeywordTownHtml($kw, $area);
    $matrixPairs[$kw . '|' . $area] = $html;
    $issues = review_issues('matrix-keyword', $html, ['local' => true, 'poa' => $kw === 'eicr-cost']);
    $samples[] = ['matrix-keyword', '/pages/keywords/' . $kw . '/' . areaSlug($area), null, ['local' => true], $html, $issues];
}
foreach ([['nurse-call', 'Chester'], ['nurse-call', 'Oldham'], ['aov-air-handling', 'Warrington']] as [$svc, $area]) {
    $html = icomplyRenderServiceAreaHtml($svc, $area);
    $issues = review_issues('matrix-service', $html, ['local' => true]);
    $samples[] = ['matrix-service', '/pages/' . $svc . '/' . areaSlug($area), null, ['local' => true], $html, $issues];
}

$fail = 0;
$intros = [];
echo "SEO PAGE REVIEW\n";
foreach ($samples as $row) {
    [$family, $path, $fn, $opts] = $row;
    if (isset($row[5])) {
        $html = $row[4];
        $issues = $row[5];
    } else {
        $html = review_capture($fn);
        $issues = review_issues($family, $html, $opts);
    }
    if (!empty($opts['local'])) {
        $intros[$path] = review_local_intro($html);
    }
    if ($issues) {
        echo 'FAIL ' . $family . ' ' . $path . ' :: ' . implode(', ', $issues) . "\n";
        $fail++;
    } else {
        echo 'OK   ' . $family . ' ' . $path . "\n";
    }
}

$dupChecks = [
    ['/pages/electrical/' . areaSlug('Manchester'), '/pages/electrical/' . areaSlug('Blackpool')],
    ['/pages/areas/' . areaSlug('Manchester'), '/pages/areas/' . areaSlug('Wilmslow')],
    ['/pages/keywords/eicr/' . areaSlug('Manchester'), '/pages/keywords/eicr/' . areaSlug('Preston')],
    ['/pages/keywords/aov-actuator/' . areaSlug('Manchester'), '/pages/keywords/aov-actuator/' . areaSlug('Preston')],
    ['/pages/keywords/eicr/' . areaSlug('Manchester'), '/pages/keywords/aov-actuator/' . areaSlug('Manchester')],
    ['/pages/keywords/aov-actuator/' . areaSlug('Manchester'), '/pages/keywords/aov-actuator/' . areaSlug('Liverpool')],
    ['/pages/nurse-call/' . areaSlug('Chester'), '/pages/nurse-call/' . areaSlug('Oldham')],
];
foreach ($dupChecks as [$a, $b]) {
    $ia = $intros[$a] ?? '';
    $ib = $intros[$b] ?? '';
    $sa = str_ireplace(['Manchester', 'Blackpool', 'Wilmslow', 'Preston', 'Liverpool', 'Chester', 'Oldham', 'Warrington', 'Bolton'], '{AREA}', $ia);
    $sb = str_ireplace(['Manchester', 'Blackpool', 'Wilmslow', 'Preston', 'Liverpool', 'Chester', 'Oldham', 'Warrington', 'Bolton'], '{AREA}', $ib);
    if ($ia === '' || $ib === '' || $sa === $sb) {
        echo "FAIL duplicate {$a} vs {$b}\n";
        $fail++;
    } else {
        echo "OK   unique {$a} vs {$b}\n";
    }
}

// Catalogue residue: exact duplicate intros and boilerplate still stored (render-time cleaner covers openers).
$kw = getMajorKeywords();
$introMap = [];
$boilHits = 0;
foreach ($kw as $slug => $meta) {
    $intro = trim((string)($meta['intro'] ?? ''));
    if ($intro === '') {
        continue;
    }
    $introMap[$intro][] = $slug;
    foreach ($boilerplate as $phrase) {
        if (stripos($intro, $phrase) !== false) {
            $boilHits++;
            break;
        }
    }
}
$dupIntros = 0;
$dupGroups = 0;
foreach ($introMap as $intro => $slugs) {
    if (count($slugs) > 1) {
        $dupGroups++;
        $dupIntros += count($slugs);
    }
}
echo "DATA keyword intros={$dupGroups} duplicate-groups covering {$dupIntros} slugs; boilerplate-opener intros={$boilHits}\n";
if ($boilHits > 0) {
    echo "NOTE boilerplate remains in keywords.json; keyword renderer strips it at output.\n";
}

echo $fail === 0 ? "\nSEO PAGE REVIEW PASSED\n" : "\n{$fail} FAILURES\n";
exit($fail > 0 ? 1 : 0);
