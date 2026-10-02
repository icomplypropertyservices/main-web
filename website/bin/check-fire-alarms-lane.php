#!/usr/bin/env php
<?php
/**
 * Assert the Fire Alarms job lane: install, maintain and service pages plus the hub.
 *
 * Usage: php website/bin/check-fire-alarms-lane.php
 */
declare(strict_types=1);

$options = getopt('', ['skip-build', 'skip-render']);
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once __DIR__ . '/build-fire-alarms-lane.php';
ob_start();

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$fail = 0;
$pass = 0;
$ok = static function (string $msg) use (&$pass): void {
    echo "OK   {$msg}\n";
    $pass++;
};
$bad = static function (string $msg) use (&$fail): void {
    echo "FAIL {$msg}\n";
    $fail++;
};

if (!isset($options['skip-build'])) {
    $code = fireAlarmsLaneBuild();
    if ($code !== 0) {
        $bad('build-fire-alarms-lane.php exited ' . $code);
        echo "fire_alarms_lane_count=0\n";
        exit(1);
    }
    fireAlarmsLaneReset();
    loadJsonData('__clear__');
}

$jobs = fireAlarmsLaneJobs();
$counts = fireAlarmsLaneCounts();
$total = count($jobs);
echo 'fire_alarms_lane_count=' . $total
    . ' install=' . $counts['install']
    . ' maintain=' . $counts['maintain']
    . ' service=' . $counts['service'] . "\n";

if ($counts['install'] < 10 || $counts['maintain'] < 3 || $counts['service'] < 10) {
    $bad('lane counts too small');
} else {
    $ok('install, maintain and service each have a working set of pages');
}
if (($counts['install'] + $counts['maintain'] + $counts['service']) !== $total) {
    $bad('lane counts do not add up to the catalogue');
} else {
    $ok('every job is in install, maintain or service');
}

$slugs = [];
$names = [];
$required = [
    'fire-alarm-installation' => 'install',
    'fire-alarm-replacement' => 'install',
    'fire-alarm-maintenance' => 'maintain',
    'fire-alarm-ppm' => 'maintain',
    'bs-5839-maintenance' => 'maintain',
    'fire-alarm-servicing' => 'service',
    'fire-alarm-call-out' => 'service',
    'false-alarm-investigation' => 'service',
];
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $lane = (string)($job['lane'] ?? '');
    $name = (string)($job['name'] ?? '');
    if ($slug === '' || $lane === '' || $name === '' || ($job['service'] ?? '') !== 'fire-alarms') {
        $bad('incomplete row ' . $slug);
        continue;
    }
    if (fireAlarmsLaneIsExcluded($slug)) {
        $bad('excluded slug leaked into the lane: ' . $slug);
    }
    if (isset($slugs[$slug])) {
        $bad('duplicate slug ' . $slug);
    }
    $slugs[$slug] = $lane;
    $key = strtolower($name);
    if (isset($names[$key])) {
        $bad('duplicate name ' . $name);
    }
    $names[$key] = $slug;
    if (fireAlarmsLaneClassify($slug) !== $lane && !isset($required[$slug])) {
        $bad($slug . ' lane ' . $lane . ' != classifier ' . fireAlarmsLaneClassify($slug));
    }
}
$missingRequired = [];
foreach ($required as $slug => $lane) {
    if (($slugs[$slug] ?? '') !== $lane) {
        $missingRequired[] = $slug;
    }
}
if ($missingRequired) {
    $bad('missing required jobs: ' . implode(',', $missingRequired));
} else {
    $ok('canonical install, maintain and service jobs are present');
}

$raw = loadJsonData('keywords', []);
$missingFromLane = [];
foreach ($raw as $slug => $meta) {
    if (!is_array($meta) || areaSlug((string)($meta['service'] ?? '')) !== 'fire-alarms') {
        continue;
    }
    $slug = keywordSlug((string)$slug);
    if (fireAlarmsLaneIsExcluded($slug)) {
        if (isset($slugs[$slug])) {
            $bad('excluded keyword still listed: ' . $slug);
        }
        continue;
    }
    if (!isset($slugs[$slug])) {
        $missingFromLane[] = $slug;
    }
}
if ($missingFromLane) {
    $bad('fire-alarms keywords missing from the lane: ' . implode(',', array_slice($missingFromLane, 0, 8)));
} else {
    $ok('every in-scope fire-alarms keyword is on the lane');
}

$keywords = getMajorKeywords();
$missingKw = [];
$missingFiles = [];
foreach (array_keys($slugs) as $slug) {
    if (!isset($keywords[$slug]) || ($keywords[$slug]['service'] ?? '') !== 'fire-alarms') {
        $missingKw[] = $slug;
    }
    if (!is_file(fireAlarmsLaneStubPath($slug))) {
        $missingFiles[] = $slug;
    }
    $intro = (string)($keywords[$slug]['intro'] ?? '');
    if (str_starts_with($intro, 'Searching for professional')) {
        $bad('generic intro still live: ' . $slug);
    }
}
if ($missingKw) {
    $bad('getMajorKeywords() missing ' . implode(',', array_slice($missingKw, 0, 8)));
} else {
    $ok('all lane slugs are in getMajorKeywords()');
}
if ($missingFiles) {
    $bad('stubs missing: ' . implode(',', array_slice($missingFiles, 0, 8)));
} else {
    $ok('all lane stubs exist');
}

$hubFile = SITE_ROOT . fireAlarmsLaneHubPath();
if (!is_file($hubFile)) {
    $bad('hub file missing');
} else {
    $ok('hub file exists');
}

$price = static function (string $html): bool {
    return (bool)preg_match('/£\s*\d/', $html);
};

$render = static function (string $path) use ($bad): string {
    $_SERVER['REQUEST_URI'] = $path;
    ob_start();
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)$#', $path, $m)) {
        renderKeywordPage($m[1]);
    } else {
        require SITE_ROOT . $path;
    }
    return (string)ob_get_clean();
};

if (!isset($options['skip-render'])) {
    $hubHtml = $render(fireAlarmsLaneHubPath());
    $h1s = [];
    if (!preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $hubHtml, $h1)) {
        $bad('hub has no h1');
    } else {
        $h1s[] = trim(strip_tags($h1[1]));
        $ok('hub h1: ' . $h1s[0]);
    }
    foreach (['id="install"', 'id="maintain"', 'id="service"', 'FAQPage', 'bg-[#0B1F3A]', '#ff6b00'] as $needle) {
        if (!str_contains($hubHtml, $needle)) {
            $bad('hub missing ' . $needle);
        }
    }
    if (str_contains($hubHtml, 'id="install"') && str_contains($hubHtml, 'FAQPage')) {
        $ok('hub has three lanes, brand colours and FAQPage');
    }
    if ($price($hubHtml)) {
        $bad('hub contains an invented £ price');
    } else {
        $ok('hub has no £ digit price');
    }
    if (!str_contains($hubHtml, 'Price on application') && !str_contains(strtolower($hubHtml), 'written quote')) {
        $bad('hub does not say how pricing works');
    } else {
        $ok('hub prices on application');
    }

    $samples = ['fire-alarm-installation', 'fire-alarm-replacement', 'fire-alarm-maintenance', 'bs-5839-maintenance', 'fire-alarm-servicing', 'fire-alarm-call-out', 'addressable-fire-alarm', 'wireless-fire-alarm'];
    foreach ($samples as $slug) {
        $html = $render('/pages/keywords/' . $slug . '.php');
        if (!preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $html, $m)) {
            $bad($slug . ' has no h1');
            continue;
        }
        $h1 = trim(preg_replace('/\s+/', ' ', strip_tags($m[1])) ?? '');
        if (isset($h1s[$h1])) {
            $bad('duplicate h1 ' . $h1);
        }
        $h1s[$h1] = $slug;
        if (!str_contains($html, 'FAQPage')) {
            $bad($slug . ' missing FAQPage');
        }
        if ($price($html)) {
            $bad($slug . ' contains an invented £ price');
        }
        $lane = $slugs[$slug] ?? '';
        if (!str_contains($html, '/pages/jobs/fire-alarms#' . $lane)) {
            $bad($slug . ' does not link its lane on the hub');
        }
    }
    if (count($h1s) >= count($samples)) {
        $ok('sample keyword pages have unique H1s, FAQPage, lane links and no £ prices');
    }
}

echo ($fail === 0 ? "PASS ({$pass})\n" : "FAIL ({$fail}) pass={$pass}\n");
exit($fail === 0 ? 0 : 1);
