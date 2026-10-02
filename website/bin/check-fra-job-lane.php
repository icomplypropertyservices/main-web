<?php
/**
 * Render the FRA job lane and assert list prices and pack cross-links.
 * Usage: php website/bin/check-fra-job-lane.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config.php';
require_once $root . '/includes/fra-job-lane.php';

$fail = 0;
$expect = [
    'fra' => 350,
    'bundle_1' => 650,
    'fra_t1' => 333,
    'fra_t2' => 315,
    'fra_t3' => 306,
    'fra_t4' => 298,
    'bundle_t1' => 618,
    'bundle_t2' => 585,
    'bundle_t3' => 569,
    'bundle_t4' => 553,
];
$rates = fraLaneRates();
$got = [
    'fra' => fraLaneMoney((int)$rates['fra']['pence']),
    'bundle_1' => fraLaneMoney((int)$rates['bundle']['pence']),
    'fra_t1' => fraLaneMoney((int)$rates['fra']['pence'], 5),
    'fra_t2' => fraLaneMoney((int)$rates['fra']['pence'], 10),
    'fra_t3' => fraLaneMoney((int)$rates['fra']['pence'], 12.5),
    'fra_t4' => fraLaneMoney((int)$rates['fra']['pence'], 15),
    'bundle_t1' => fraLaneMoney((int)$rates['bundle']['pence'], 5),
    'bundle_t2' => fraLaneMoney((int)$rates['bundle']['pence'], 10),
    'bundle_t3' => fraLaneMoney((int)$rates['bundle']['pence'], 12.5),
    'bundle_t4' => fraLaneMoney((int)$rates['bundle']['pence'], 15),
];
foreach ($expect as $key => $want) {
    if ($got[$key] !== $want) {
        fwrite(STDERR, "PRICE {$key} got {$got[$key]} want {$want}\n");
        $fail++;
    }
}

$paths = [
    '/pages/jobs/fra' => ['£350', '£650', '/pages/jobs/landlord-bundle', '/pages/jobs/fire-risk-assessment'],
    '/pages/jobs/fire-risk-assessment' => ['£350', '£650', 'FIRE-FRA-6BED-NW', '/pages/jobs/landlord-bundle', '£333', '£298'],
    '/pages/jobs/fra-other' => ['POA', '£350', '£650', 'FIRE-FRA-OTHER'],
    '/pages/jobs/landlord-bundle' => ['£650', '£350', 'LAND-PKG-FRA-EICR-GAS-6BED-NW', '£618', '£553'],
    '/pages/fire-risk-assessment' => ['£350', '£650', '/pages/jobs/fra', '/pages/jobs/landlord-bundle'],
    '/pages/pricing' => ['£350', '£650', 'approved list', '/pages/jobs/fire-risk-assessment', '/pages/jobs/landlord-bundle'],
    '/pages/packages' => ['£350', '£650', '/pages/jobs/landlord-bundle'],
];

$runner = sys_get_temp_dir() . '/fra-lane-render.php';
file_put_contents($runner, "<?php\n\$site = " . var_export($root, true) . ";\n" . <<<'PHP'
$path = $argv[1] ?? '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $path;
$_SERVER['QUERY_STRING'] = '';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['SCRIPT_NAME'] = '/router.php';
require $site . '/router.php';
PHP);

foreach ($paths as $path => $needles) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($runner) . ' ' . escapeshellarg($path);
    $html = (string)shell_exec($cmd . ' 2>/tmp/fra-lane-render.err');
    $err = (string)@file_get_contents('/tmp/fra-lane-render.err');
    if ($err !== '') {
        fwrite(STDERR, "STDERR {$path}\n{$err}\n");
    }
    if (!str_contains($html, '<h1')) {
        fwrite(STDERR, "NO HTML {$path} bytes=" . strlen($html) . "\n");
        $fail++;
        continue;
    }
    foreach ($needles as $needle) {
        if (!str_contains($html, $needle)) {
            fwrite(STDERR, "MISSING {$path} {$needle}\n");
            $fail++;
        }
    }
}
@unlink($runner);

if ($fail > 0) {
    fwrite(STDERR, "FRA lane check FAILED ({$fail})\n");
    exit(1);
}
fwrite(STDOUT, "FRA lane check OK\n");
exit(0);
