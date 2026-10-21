<?php
/**
 * Quality-manager rules on rendered sample pages.
 * Usage: ICOMPLY_STATIC_EXPORT=1 php website/bin/check-qm-floor.php
 */
declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/router.php';
require_once dirname(__DIR__) . '/includes/matrix-catalogue.php';
require_once dirname(__DIR__) . '/includes/perf.php';

$fail = 0;
$say = static function (bool $ok, string $msg) use (&$fail): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $msg . "\n";
    if (!$ok) {
        $fail++;
    }
};

$pick = icomplyMatrixSelectPlaces(100000);
$places = array_keys($pick['selected']);
$say(count($places) === 269, 'dual-ring places selected=' . count($places) . ' available=' . ($pick['available'] ?? 0) . ' skipped=' . ($pick['skipped_duplicates'] ?? 0));

$removedFile = getenv('QM_SITEMAP_REMOVED');
if (!is_string($removedFile) || $removedFile === '') {
    $candidates = glob('/home/ubuntu/.cursor/projects/workspace/uploads/sitemap-removed*.txt') ?: [];
    $removedFile = $candidates[0] ?? '';
}
if (is_string($removedFile) && is_file($removedFile)) {
    $missingTowns = [];
    $placeSet = array_fill_keys($places, true);
    foreach (file($removedFile, FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim((string)$line);
        if (!preg_match('#^/pages/(?:keywords/[^/]+|jobs/[^/]+|[a-z0-9-]+)/([a-z0-9-]+)$#', $line, $m)) {
            continue;
        }
        if (!isset($placeSet[$m[1]])) {
            $missingTowns[$m[1]] = true;
        }
    }
    $say($missingTowns === [], 'sitemap-removed towns are in the 269' . ($missingTowns === [] ? '' : ' missing ' . implode(',', array_slice(array_keys($missingTowns), 0, 12))));
} else {
    $say(false, 'sitemap-removed file not found');
}

$paths = [
    '/',
    '/renewals',
    '/terms',
    '/pages/keywords/gas-engineer',
    '/pages/keywords/gas-installation',
    '/pages/keywords/gas-engineer-near-me',
    '/pages/keywords/cp12-gas-certificate',
    '/pages/keywords/gas-safety-certificate',
    '/pages/keywords/gas-certificate-same-day',
    '/pages/keywords/gas-safe-engineer',
    '/pages/keywords/gas-safe-engineer-stockport',
    '/pages/keywords/gas-safe-register-engineer',
    '/pages/keywords/gas-safe-registered-engineer',
    '/pages/keywords/same-day-cp12',
    '/pages/keywords/same-day-gas-safety-certificate',
    '/pages/aov/aberdeen',
    '/pages/aov/ashford',
    '/pages/aov/ashford-kent',
    '/pages/barriers/bolton',
    '/pages/fencing/accrington',
    '/pages/fencing/abram',
    '/pages/drainage/accrington',
    '/pages/drainage/abram',
    '/pages/services/fencing',
    '/pages/services/drainage',
    '/pages/manufacturers/dh-mechatronic',
    '/pages/manufacturers/d-h-mechatronic',
    '/pages/driveways/abram',
    '/pages/demolition/abram',
];

function qmWords(string $html): int
{
    $main = $html;
    if (preg_match('/<main\b[^>]*>(.*)<\/main>/is', $html, $m)) {
        $main = $m[1];
    }
    $main = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $main) ?? $main;
    $main = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $main) ?? $main;
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($main)) ?? '');
    return $text === '' ? 0 : count(preg_split('/\s+/', $text) ?: []);
}

function qmShingles(string $html): array
{
    $text = strtolower(trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? ''));
    $words = preg_split('/\s+/', $text) ?: [];
    $set = [];
    $n = count($words);
    for ($i = 0; $i + 4 < $n; $i++) {
        $set[implode(' ', array_slice($words, $i, 5))] = true;
    }
    return $set;
}

function qmJaccard(array $a, array $b): float
{
    if ($a === [] && $b === []) {
        return 1.0;
    }
    $inter = 0;
    foreach ($a as $k => $_) {
        if (isset($b[$k])) {
            $inter++;
        }
    }
    $union = count($a) + count($b) - $inter;
    return $union === 0 ? 1.0 : $inter / $union;
}

$rendered = [];
$titles = [];
$metas = [];
$h1s = [];
$off = ['#ffb27a', '#ffb080', '#f7f4ef', '#ffd3b0', '#fff4eb', '#0f172a', '#f4f6f9'];
foreach ($paths as $path) {
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    ob_start();
    $handled = routerDispatchVirtual($path);
    if (!$handled) {
        routerHandleRequest();
    }
    $html = (string)ob_get_clean();
    $html = icomplyPerfRewriteHtml($html);
    $rendered[$path] = $html;
    $words = qmWords($html);
    $say(strlen($html) > 500, $path . ' rendered ' . strlen($html) . ' bytes');
    $say($words >= 800, $path . ' words=' . $words);
    $say(str_contains($html, 'data-real-job-photo="owner"'), $path . ' real-job photo');
    $say(str_contains($html, 'itemtype="https://schema.org/BreadcrumbList"'), $path . ' visible breadcrumbs');
    $say(str_contains($html, 'BreadcrumbList'), $path . ' breadcrumb schema');
    $say(str_contains($html, 'FAQPage'), $path . ' faq schema');
    $say(!preg_match('/<img\b(?![^>]*data-credit=)[^>]*>/i', $html), $path . ' every image credited');
    $say(!str_contains($html, 'you@company.co.uk'), $path . ' no placeholder email');
    $say(!str_contains($html, 'owner-job.svg'), $path . ' no placeholder svg');
    $lower = strtolower($html);
    $colourHit = '';
    foreach ($off as $hex) {
        if (str_contains($lower, $hex)) {
            $colourHit = $hex;
            break;
        }
    }
    $say($colourHit === '', $path . ' colours' . ($colourHit === '' ? '' : ' ' . $colourHit));
    $say(!preg_match('/iComply(?: Property Services)? (?:is|are|supply|supplies|arrange|arranges) Gas Safe/i', $html), $path . ' no Gas Safe company claim');
    $say(!preg_match('/\b(?:Are|Is) [^<]{0,80}Gas Safe registered\?/i', $html), $path . ' no Gas Safe yes-question');
    $say(!preg_match('/Does iComply hold a Gas Safe/i', $html), $path . ' no registration question');
    if (str_contains($path, 'fencing') || str_contains($path, 'drainage')) {
        $say(!str_contains(strtolower($html), 'fixed pricing'), $path . ' no fixed pricing');
        $say(str_contains($html, 'site-nav') || str_contains($html, 'mega'), $path . ' mega nav');
    }
    if (preg_match('/<title>([^<]*)<\/title>/i', $html, $m)) {
        $titles[$m[1]][] = $path;
    }
    if (preg_match('/<meta name="description" content="([^"]*)"/i', $html, $m)) {
        $metas[$m[1]][] = $path;
    }
    if (preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/is', $html, $hm)) {
        $say(count($hm[0]) === 1, $path . ' single h1');
        $h1 = trim(preg_replace('/\s+/', ' ', strip_tags($hm[1][0])) ?? '');
        $h1s[$h1][] = $path;
    } else {
        $say(false, $path . ' missing h1');
    }
}

foreach (['title' => $titles, 'meta' => $metas, 'h1' => $h1s] as $kind => $groups) {
    foreach ($groups as $value => $urls) {
        if (count($urls) > 1) {
            $say(false, 'duplicate ' . $kind . ' ' . implode(' ', $urls) . ' :: ' . substr((string)$value, 0, 80));
        }
    }
}
$say(true, 'duplicate scan finished');

$pairs = [
    ['/pages/fencing/accrington', '/pages/fencing/abram'],
    ['/pages/drainage/accrington', '/pages/drainage/abram'],
    ['/pages/aov/ashford', '/pages/aov/ashford-kent'],
];
foreach ($pairs as [$a, $b]) {
    if (!isset($rendered[$a], $rendered[$b])) {
        continue;
    }
    $j = qmJaccard(qmShingles($rendered[$a]), qmShingles($rendered[$b]));
    $say($j < 0.72, sprintf('near-dup %s vs %s j=%.3f', $a, $b, $j));
}

echo $fail === 0 ? "QM FLOOR OK\n" : "QM FLOOR FAILED {$fail}\n";
exit($fail === 0 ? 0 : 1);
