#!/usr/bin/env php
<?php
/**
 * Full debug / health check suite for Icomply.
 * Exit 0 only if all critical checks pass.
 */
require_once __DIR__ . '/../includes/build-status.php';
require_once __DIR__ . '/../includes/render.php';

$fail = 0;
$warn = 0;
$pass = 0;

function check(string $name, bool $ok, string $detail = '', bool $critical = true): void {
    global $fail, $warn, $pass;
    if ($ok) {
        $pass++;
        echo "[PASS] {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    } elseif ($critical) {
        $fail++;
        echo "[FAIL] {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    } else {
        $warn++;
        echo "[WARN] {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    }
}

echo "Icomply DEBUG CHECK\n";
echo str_repeat('=', 50) . "\n\n";

// --- Config ---
echo "## Config\n";
check('SITE_URL defined', defined('SITE_URL') && SITE_URL !== '');
check('EMAIL looks like email', (bool)filter_var(EMAIL, FILTER_VALIDATE_EMAIL), EMAIL);
check('PHONE set', PHONE !== '');
check('WHATSAPP set', WHATSAPP !== '');
check('url() helper', url('/contact.php') === rtrim(SITE_URL, '/') . '/contact.php', url('/contact.php'));
check('areaSlug hyphens', areaSlug('Ashton-under-Lyne') === 'ashton-under-lyne', areaSlug('Ashton-under-Lyne'));
check('areaSlug spaces', areaSlug('Cheadle Hulme') === 'cheadle-hulme', areaSlug('Cheadle Hulme'));

// --- Data ---
echo "\n## Data inventory\n";
$services = getServices();
$areas = getAreas();
$keywords = getMajorKeywords();
check('services loaded', count($services) >= 11, (string)count($services));
check('areas loaded', count($areas) >= 100, (string)count($areas));
check('keywords loaded', count($keywords) >= 50, (string)count($keywords));
check('manufacturers.json', is_file(SITE_ROOT . '/data/manufacturers.json'));

// --- Build status ---
echo "\n## Build status\n";
$status = getBuildStatus();
foreach ($status['jobs'] as $key => $job) {
    check(
        "job:{$key}",
        !empty($job['ok']),
        "{$job['actual']}/{$job['expected']} — {$job['name']}"
    );
}
check('needs_regen is false', !$status['needs_regen'], $status['needs_regen'] ? implode('; ', $status['reasons']) : 'clean');
check('build not dirty', empty($status['dirty']), $status['dirty_reason'] ?? '');

// --- Sample stubs exist ---
echo "\n## Stub files\n";
foreach ($status['samples'] as $s) {
    if ($s['path'] === '/' || $s['path'] === '/contact.php' || $s['path'] === '/sitemap.xml') {
        check('file ' . $s['path'], $s['exists'], $s['label']);
        continue;
    }
    check('stub ' . $s['path'], $s['exists'], $s['label']);
}

// --- Runtime render ---
echo "\n## Runtime render\n";
$renderCases = [
    ['fire-alarms', 'Stockport', ['BS 5839', 'Kentec', 'Stockport']],
    ['electrical', 'Manchester', ['Manchester', 'EICR']],
];
foreach ($renderCases as [$svc, $area, $needles]) {
    ob_start();
    try {
        renderServiceAreaPage($svc, $area);
        $html = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        $html = '';
        check("render {$svc}/{$area}", false, $e->getMessage());
        continue;
    }
    $ok = strlen($html) > 5000 && strpos($html, 'DOCTYPE') !== false;
    foreach ($needles as $n) {
        if (stripos($html, $n) === false) {
            $ok = false;
            check("render {$svc}/{$area} contains {$n}", false, 'missing');
        }
    }
    check("render {$svc}/{$area} size", $ok, strlen($html) . ' bytes');
    // Absolute nav
    check(
        "render {$svc}/{$area} absolute nav",
        strpos($html, rtrim(SITE_URL, '/') . '/pages/services') !== false
        || strpos($html, rtrim(SITE_URL, '/') . '/contact.php') !== false,
        'SITE_URL links present'
    );
}

ob_start();
try {
    renderKeywordPage('kentec-fire-alarm-panel');
    $html = ob_get_clean();
    check('render keyword kentec', strlen($html) > 5000 && stripos($html, 'Kentec') !== false, strlen($html) . ' bytes');
} catch (Throwable $e) {
    ob_end_clean();
    check('render keyword kentec', false, $e->getMessage());
}

// --- Manufacturer images map ---
echo "\n## Manufacturer assets\n";
$slugs = getManufacturerImageSlugs('fire-alarms');
check('fire-alarms image slugs non-empty', count($slugs) > 0, implode(',', $slugs));
foreach ($slugs as $slug) {
    $path = SITE_ROOT . '/assets/images/manufacturers/' . $slug . '.jpg';
    check("mfr image {$slug}.jpg", is_file($path), $path, false);
}

// --- Templates ---
echo "\n## Templates\n";
foreach (array_keys($services) as $slug) {
    $p = SITE_ROOT . '/templates/services/' . $slug . '.php';
    check("template services/{$slug}.php", is_file($p), '', false);
}
check('template combo.php', is_file(SITE_ROOT . '/templates/combo.php'));
check('template keyword.php', is_file(SITE_ROOT . '/templates/keyword.php'));
check('template area.php', is_file(SITE_ROOT . '/templates/area.php'));

// --- Security basics ---
echo "\n## Security / admin paths\n";
check('data/.htaccess exists', is_file(SITE_ROOT . '/data/.htaccess'));
check('config.local.example exists', is_file(SITE_ROOT . '/config.local.php.example'), '', false);
$headerSrc = (string)file_get_contents(SITE_ROOT . '/includes/header.php');
check(
    'tracking gated on real IDs',
    strpos($headerSrc, 'GA_MEASUREMENT_ID !==') !== false || strpos($headerSrc, "GA_MEASUREMENT_ID !== ''") !== false,
    'gtag only when configured'
);
check('admin build-status wired', is_file(SITE_ROOT . '/includes/build-status.php'));
check('full-build script', is_file(SITE_ROOT . '/bin/full-build.php'));
check('http-check script', is_file(SITE_ROOT . '/bin/http-check.php'));

// --- Summary ---
echo "\n" . str_repeat('=', 50) . "\n";
echo "PASS={$pass}  FAIL={$fail}  WARN={$warn}\n";
if ($fail > 0) {
    echo "RESULT: FAILED\n";
    exit(1);
}
echo "RESULT: OK\n";
exit(0);
