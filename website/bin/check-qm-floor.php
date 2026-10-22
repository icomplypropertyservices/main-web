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
if (!function_exists('icomplySpokenLinkLabel')) {
    require_once dirname(__DIR__) . '/includes/related.php';
}
$say(icomplySpokenLinkLabel('ahu-commissioning', 'AHU Commissioning') === 'AHU commissioning', 'spoken AHU commissioning');
$say(icomplySpokenLinkLabel('eicr-report', 'EICR Report', 'disley') === 'EICR report in Disley', 'spoken EICR report in Disley');
$say(icomplySpokenLinkLabel('retaining-wall-build', 'Retaining Wall Build', 'Warrington') === 'Retaining wall construction in Warrington', 'spoken retaining wall construction');
$say(icomplySpokenLinkLabel('kitchen-fit', 'Kitchen Fit') === 'Kitchen fitting', 'spoken kitchen fitting');

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
    '/pages/demolition/bingley',
    '/pages/fencing/bingley',
    '/contact',
    '/pages/about',
    '/pages/jobs',
    '/become-a-subcontractor',
    '/directories',
    '/products',
    '/pages/manufacturers/apt-controls',
    '/pages/manufacturers/heald',
    '/pages/packages/compliance-bundle',
    '/pages/packages/landlord-pack',
    '/pages/services/plastering',
    '/pages/roofing/stockport',
    '/pages/demolition/bromborough',
    '/pages/drainage/bromborough',
    '/pages/fencing/stockport',
    '/pages/aov',
    '/pages/areas/stockport',
    '/pages/areas/bolton',
    '/pages/areas/wigan',
    '/pages/driveways/wigan',
    '/pages/aov/belfast',
    '/pages/aov-air-handling/york',
    '/pages/drainage/york',
    '/pages/demolition/stockport',
    '/pages/services/heating',
    '/pages/keywords/emergency-heating',
    '/pages/keywords/next-day-heating',
    '/pages/keywords/next-day-heating-engineer',
    '/pages/manufacturers/hysecurity',
    '/pages/manufacturers/designa',
    '/pages/manufacturers/vaillant',
    '/pages/landlords',
    '/pages/packages',
    '/pages/demolition/bolton',
    '/pages/jobs/aov-service',
    '/pages/manufacturers/dea',
    '/pages/manufacturers/eagle',
    '/pages/manufacturers/life',
    '/pages/manufacturers/newgate',
    '/pages/manufacturers/skidata',
    '/pages/packages/fire-ready',
    '/pages/packages/let-ready',
    '/pages/packages/workplace-essentials',
    '/pages/services/brickwork',
    '/pages/services/demolition',
    '/pages/areas/warrington',
    '/pages/areas/great-sankey',
    '/pages/services/driveways',
];

function qmBodyWords(string $html): int
{
    return qmWords(qmBody($html));
}

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
    $say(str_contains($html, 'data-pe-slot="real-photo"'), $path . ' real-photo slot');
    $say(str_contains($html, 'itemtype="https://schema.org/BreadcrumbList"'), $path . ' visible breadcrumbs');
    $say(str_contains($html, 'BreadcrumbList'), $path . ' breadcrumb schema');
    $say(str_contains($html, 'FAQPage'), $path . ' faq schema');
    $say(!preg_match('/records\s+[a-z]+,\s+then\b/i', $html), $path . ' no salad records');
    $say(!preg_match('/gets\s+[a-z]+\s+in writing/i', $html), $path . ' no salad writing');
    $header = '';
    if (preg_match('/<header\b[^>]*>.*?<\/header>/is', $html, $hm)) {
        $header = $hm[0];
    }
    $say($header === '' || (!str_contains($header, 'image-credit') && !str_contains($header, 'data-credit=')), $path . ' no header credit');
    $say(!str_contains($html, 'data-credit="iComply Property Services"'), $path . ' no false iComply image credit');
    if (preg_match_all('#/assets/images/real-job/([a-z0-9-]+)\.jpg#i', $html, $rm)) {
        foreach (array_unique($rm[1]) as $leaf) {
            $file = dirname(__DIR__) . '/assets/images/real-job/' . strtolower((string)$leaf) . '.jpg';
            $say(is_file($file), $path . ' real-job file ' . $leaf);
        }
    }
    if (preg_match_all('/href\s*=\s*"([^"]*)"/i', $html, $hrefs)) {
        $bad = [];
        foreach ($hrefs[1] as $href) {
            if (icomplyQualityFloorRewriteHref($href) !== $href) {
                $bad[] = $href;
            }
        }
        $say($bad === [], $path . ' broken links ' . ($bad === [] ? '0' : implode(' ', array_slice($bad, 0, 3))));
    } else {
        $say(true, $path . ' broken links 0');
    }
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
    if (str_contains($path, 'fencing') || str_contains($path, 'drainage') || str_contains($path, 'roofing') || str_contains($path, 'demolition')) {
        $say(!str_contains(strtolower($html), 'fixed pricing'), $path . ' no fixed pricing');
        $say(str_contains($html, 'mega-header') || str_contains($html, 'data-mega-nav'), $path . ' mega nav');
        $say(!preg_match('/-(?:photo|context)\.jpg/i', $html), $path . ' no placeholder graphic');
    }
    $say(!str_contains($html, 'pOA.'), $path . ' no garbled POA');
    $priceHtml = preg_replace('/[a-z0-9]+-fixed-price(?:-[a-z0-9]+)+/i', '', $html) ?? $html;
    $say(!preg_match('/fixed written quote|fixed prices|fixed-prices|fixed-pricing|\bfixed-price\b|\bfixed quotes?\b|\bfixed price\b|fixed pricing/i', $priceHtml), $path . ' no fixed-price wording');
    $say(!str_contains($html, 'panel, board or door'), $path . ' no generic photo line');
    $say(!preg_match('/trade supply only in/i', $html), $path . ' no heading leak');
    $say($path === '/' || !str_contains($html, 'real-job/company.jpg'), $path . ' company.jpg only on home');
    $say(!preg_match('/do not describe iComply as Gas Safe/i', $html), $path . ' no gas denial');
    $garden = (bool)preg_match('#/(?:fencing|landscaping|demolition|driveways)(?:/|$)#', $path);
    $say($garden || !preg_match('/fill a skip and call the garden clear/i', $html), $path . ' no off-family skip sentence');
    $gasPage = (bool)preg_match('/gas|cp12|boiler|heating|compliance-bundle|landlord/i', $path)
        || (bool)preg_match('#/manufacturers/(?:alpha|ariston|baxi|ferroli|firebird|glow-worm|grant|ideal|intergas|main-heating|navien|potterton|ravenheat|remeha|vaillant|viessmann|vokera|worcester-bosch)$#', $path);
    $say($gasPage || !preg_match('/Gas Safe registered/i', $html), $path . ' gas sentence only on gas pages');
    $say(!preg_match('/\b(?:a|an|the) the area\b/i', $html), $path . ' no broken area grammar');
    $ogBad = [];
    if (preg_match_all('/<meta\b[^>]*(?:property|name)\s*=\s*"(?:og:image(?::secure_url)?|twitter:image)"[^>]*>/i', $html, $ogTags)) {
        foreach ($ogTags[0] as $tag) {
            if (!preg_match('/\bcontent\s*=\s*"([^"]*)"/i', $tag, $cm) || !str_starts_with($cm[1], 'https://')) {
                $ogBad[] = $tag;
            }
        }
    }
    $say($ogTags[0] !== [] && $ogBad === [], $path . ' absolute og:image');
    if ($path === '/pages/packages') {
        $say(!str_contains($html, 'arefrom') && !str_contains($html, 'POAonly'), $path . ' package price spacing');
    }
    if ($path === '/pages/jobs/aov-service') {
        $say(substr_count($html, 'To book, call or WhatsApp 07517 806082') === 1, $path . ' booking paragraph once');
        $say(!str_contains($html, 'a055da46974c'), $path . ' no collapsed aov webp');
    }
    if (str_contains($path, 'plastering')) {
        $say(!preg_match('/Useful photographs[^<]{0,500}fence run/i', $html), $path . ' photo line is plastering');
    }
    if (str_contains($path, 'gas-engineer')) {
        $say(!preg_match('/Useful photographs[^<]{0,500}when the job is the roof/i', $html), $path . ' photo line is not the roof');
    }
    if ($path === '/pages/landlords') {
        $say(!preg_match('/Useful photographs[^<]{0,500}when the job is a driveway/i', $html), $path . ' landlord photo line is generic');
    }
    if ($path === '/pages/areas/stockport' || $path === '/pages/areas/wigan' || $path === '/pages/areas/bolton') {
        $say(!preg_match('/Useful photographs[^<]{0,500}when the job is the roof/i', $html), $path . ' town photo line is generic');
    }
    if (str_contains($path, 'driveways')) {
        $say(str_contains($html, 'Robin Stott'), $path . ' driveway credit');
        $say(!str_contains($html, 'Dave Spicer'), $path . ' driveway credit is not the old lane');
        $say(!preg_match('/data-credit="iComply Property Services"[^>]*driveways|driveways[^>]*data-credit="iComply Property Services"/i', $html), $path . ' driveway image credit is not iComply');
    }
    if (str_contains($path, 'brickwork')) {
        $say(str_contains($html, 'Bob Harvey'), $path . ' brickwork credit');
        $say(!str_contains($html, 'Library of Congress') && !str_contains($html, 'PPOC'), $path . ' brickwork credit is not the archive photo');
    }
    if (str_contains($path, 'fencing') || str_contains($path, 'drainage')) {
        $say(!preg_match('/\bcertificate (?:Fencing|Drainage)\b/i', $html) && !str_contains($html, 'and certificate '), $path . ' certificate is not used as a verb');
    }
    if ($path === '/pages/services/demolition') {
        $say(!str_contains($html, 'Installed, tested, certified.'), $path . ' demolition hero is not an install line');
        $say(!str_contains($html, 'Demolitionfor') && !str_contains($html, 'certifies Demolition'), $path . ' demolition sentence has a space and the right verb');
    }
    if ($path === '/pages/areas/warrington' || $path === '/pages/areas/great-sankey') {
        $say(!str_contains($html, 'WA55'), $path . ' WA55 is not listed');
    }
    $say(!str_contains($html, '&amp;amp;'), $path . ' no double-escaped amp');
    $say(!preg_match('/\balt="[^"]*\bCompleted\b[^"]*\sby\s/i', $html), $path . ' alt does not say completed by');
    if (str_contains($path, 'plastering')) {
        $say(str_contains($html, 'Pingstone') || str_contains($html, 'Arpingstone'), $path . ' plastering credit');
    }
    $say(!str_contains($html, 'dump 200,000') && !str_contains($html, '200,000 links'), $path . ' no hub dump line');
    $say(!str_contains($html, 'arrive unannounced to a tenant') && !str_contains($html, 'will not arrive unannounced'), $path . ' no shared landlord paragraph');
    $say($path === '/' || !str_contains($html, 'real-job/home.jpg'), $path . ' home.jpg only on home');
    preg_match_all('/\bIn [A-Z][A-Za-z\' .-]{1,40}, /', $html, $prefixHits);
    $prefixCount = count($prefixHits[0]);
    $say($prefixCount <= 6, $path . ' town prefix x' . $prefixCount);
    $unbuiltAov = ['bromborough', 'burngreave', 'cleckheaton', 'halewood', 'haxby', 'ince-in-makerfield', 'kippax', 'morley', 'old-swan', 'shirebrook', 'wath-upon-dearne'];
    $badAov = [];
    foreach ($unbuiltAov as $town) {
        if (str_contains($html, '/pages/aov-air-handling/' . $town)) {
            $badAov[] = $town;
        }
    }
    $say($badAov === [], $path . ' unbuilt aov links ' . ($badAov === [] ? '0' : implode(',', $badAov)));
    if (str_contains($path, 'demolition') && preg_match('/<section class="(?:matrix-hero|page-hero|hero)\b[\s\S]*?<\/section>/i', $html, $hero)) {
        $say(preg_match('/<img\b/i', $hero[0]) === 1, $path . ' hero has an image');
        $say(str_contains($hero[0], 'Tomwsulcer (CC0)'), $path . ' demolition hero credit');
    }
    if ($path === '/pages/areas/wigan') {
        $say(!str_contains($html, 'WN1–WN6in') && !str_contains($html, 'WN1-WN6in'), $path . ' district spacing');
    }
    $aovEquipment = '#/services/(?:smoke-vent-|smoke-shaft-|smoke-exhaust-|smoke-stair-|aov-release-switch-|aov-control-panel-|aov-roof-hatch-|aov-damper-actuator-|aov-smoke-damper-)#';
    $airSet = '#/(?:page-sets/air-duct-pipes|services/aov-air-handling-unit-|services/aov-rooftop-unit-)#';
    if (preg_match('#/aov-air-handling(?:/|$)#', $path)) {
        $say(!str_contains($html, 'a055da46974c') && !str_contains($html, '/services/aov-air-handling.jpg'), $path . ' aov image not collapsed');
        $say(!preg_match($aovEquipment, $html), $path . ' air handling does not use aov equipment');
        $say(preg_match($airSet, $html) === 1, $path . ' air handling photo set');
        $say(!str_contains($html, '21bb8660f87c') && !str_contains($html, '/services/electrical.jpg'), $path . ' no electrical hero');
    } elseif (preg_match('#/aov(?:/|$)#', $path)) {
        $say(!str_contains($html, 'a055da46974c') && !str_contains($html, '/services/aov-air-handling.jpg'), $path . ' aov image not collapsed');
        $hasSmoke = (bool)preg_match('/(?:smoke-vent|smoke-shaft|shaft|ventilation)/i', $html);
        $hasAov = (bool)preg_match('#/services/(?:aov-release-switch-|aov-control-panel-|aov-roof-hatch-|aov-damper-actuator-|aov-smoke-damper-)#', $html);
        $say($hasSmoke && $hasAov, $path . ' smoke and aov photos');
        $say(!preg_match($airSet, $html) && !str_contains($html, '/page-sets/'), $path . ' aov does not use the air handling set');
    }
    if (preg_match('#/keywords/[^/]+#', $path)) {
        $say(!preg_match($aovEquipment, $html), $path . ' keyword does not use aov equipment');
        $say(!preg_match('#/page-sets/(?:air-handler-unit|air-duct-pipes|rooftop-packaged-units|combination-spanners|tape-measure|safety-boots|workshop-interior|mechanics-tool-set|desk-notebook|public-bookcase)\.jpg#', $html) && !preg_match($airSet, $html), $path . ' keyword does not use another page set');
        $say(preg_match('#/page-types/keywords/[a-z0-9-]+\.jpg#', $html) === 1, $path . ' keyword trade photo');
        $say(!str_contains($html, '21bb8660f87c') && !str_contains($html, '/services/electrical.jpg') && !str_contains($html, '/assets/images/keywords/'), $path . ' keyword does not reuse electrical or old keyword files');
    }
    if (preg_match('#/jobs/[^/]+#', $path) && !str_ends_with($path, '/jobs')) {
        $say(!preg_match($aovEquipment, $html), $path . ' job does not use aov equipment');
        $say(preg_match('#/page-types/jobs/[a-z0-9-]+\.jpg#', $html) === 1, $path . ' job trade photo');
        $say(!str_contains($html, '21bb8660f87c') && !str_contains($html, '/services/electrical.jpg'), $path . ' job does not use electrical hero');
    }
    if (preg_match('#/resources/[^/]+#', $path) && !str_ends_with($path, '/resources')) {
        $say(preg_match('#/page-types/resources/[a-z0-9-]+\.jpg#', $html) === 1, $path . ' resource trade photo');
        $say(!str_contains($html, '21bb8660f87c') && !str_contains($html, '/services/electrical.jpg'), $path . ' resource does not use electrical hero');
    }
    if ($path === '/pages/manufacturers/vaillant') {
        $say(qmBodyWords($html) >= 800, $path . ' manufacturer body words=' . qmBodyWords($html));
        $say(!preg_match('/Vaillant \(vaillant\)|trade supply only in/i', $html), $path . ' brand subject clean');
    }
    if (preg_match('#/manufacturers/(?:hysecurity|heald|designa)$#', $path)) {
        $say(qmBodyWords($html) >= 800, $path . ' manufacturer body words=' . qmBodyWords($html));
    }
    if (!str_contains($path, 'building-maintenance')) {
        $say(!str_contains($html, '19f957bd50f5') && !str_contains($html, '/assets/images/services/building-maintenance.jpg'), $path . ' no shared maintenance photo');
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

function qmBody(string $html): string
{
    $html = preg_replace('/<(header|nav|footer)\b[^>]*>.*?<\/\1>/is', ' ', $html) ?? $html;
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html;
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $html) ?? $html;
    return $html;
}

$pairs = [
    ['/pages/demolition/bingley', '/pages/fencing/bingley'],
    ['/pages/manufacturers/apt-controls', '/pages/manufacturers/heald'],
    ['/pages/packages/compliance-bundle', '/pages/packages/landlord-pack'],
];
foreach ($pairs as [$a, $b]) {
    if (!isset($rendered[$a], $rendered[$b])) {
        continue;
    }
    $j = qmJaccard(qmShingles(qmBody($rendered[$a])), qmShingles(qmBody($rendered[$b])));
    $say($j < 0.72, sprintf('near-dup %s vs %s j=%.3f', $a, $b, $j));
}

$sentenceCounts = [];
$sentencePages = 0;
foreach (array_slice($places, 0, 40) as $town) {
    foreach (['plumbing', 'kitchens', 'plastering', 'electrical', 'roofing'] as $service) {
        $sentencePages++;
        $note = icomplyQualityFloorLandlord('/pages/' . $service . '/' . $town, 'SUBJECT', 'TOWNNAME');
        $norm = str_replace(['SUBJECT', 'TOWNNAME'], '', $note);
        foreach (preg_split('/(?<=[.!?])\s+/', $norm) ?: [] as $sentence) {
            $sentence = trim((string)$sentence);
            if ($sentence === '') {
                continue;
            }
            $sentenceCounts[$sentence] = ($sentenceCounts[$sentence] ?? 0) + 1;
        }
    }
}
$maxShare = $sentencePages > 0 && $sentenceCounts !== [] ? max($sentenceCounts) / $sentencePages : 1;
$say($maxShare < 0.5, 'landlord sentence max share ' . round($maxShare * 100, 1) . '% of ' . $sentencePages);

echo $fail === 0 ? "QM FLOOR OK\n" : "QM FLOOR FAILED {$fail}\n";
exit($fail === 0 ? 0 : 1);
