<?php
/**
 * Wave-1 marketing pages: 14 fortnight guides + 12 quality hubs exist.
 * HMO package landings and keyword-matrix files must not be added here.
 *
 * Usage: php website/bin/check-wave1.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/wave1.php';

$fail = 0;

$guides = wave1FortnightGuides();
$hubs = wave1QualityHubs();
$queue = wave1PublishQueue();
if (count($queue['A']['guides'] ?? []) !== 5) {
    echo 'FAIL: Batch A must be days 1–5 (5 guides), got ' . count($queue['A']['guides'] ?? []) . PHP_EOL;
    $fail++;
} else {
    echo "OK   Batch A days 1–5\n";
}
if (count($queue['B']['guides'] ?? []) !== 9) {
    echo 'FAIL: Batch B must be days 6–14 (9 guides), got ' . count($queue['B']['guides'] ?? []) . PHP_EOL;
    $fail++;
} else {
    echo "OK   Batch B days 6–14\n";
}
if (count($queue['C']['hubs'] ?? []) !== 12) {
    echo 'FAIL: Batch C must be 12 quality hubs, got ' . count($queue['C']['hubs'] ?? []) . PHP_EOL;
    $fail++;
} else {
    echo "OK   Batch C 12 hubs\n";
}
if (wave1ContactPath() !== '/contact') {
    echo "FAIL: CTA target must remain /contact\n";
    $fail++;
} else {
    echo "OK   CTA target /contact\n";
}
foreach ($queue['A']['guides'] as $g) {
    if (($g['batch'] ?? '') !== 'A' || (int)$g['day'] < 1 || (int)$g['day'] > 5) {
        echo "FAIL: Batch A item has wrong batch/day\n";
        $fail++;
        break;
    }
}
foreach ($queue['B']['guides'] as $g) {
    if (($g['batch'] ?? '') !== 'B' || (int)$g['day'] < 6 || (int)$g['day'] > 14) {
        echo "FAIL: Batch B item has wrong batch/day\n";
        $fail++;
        break;
    }
}
if (count($guides) !== 14) {
    echo "FAIL: expected 14 fortnight guides, got " . count($guides) . PHP_EOL;
    $fail++;
} else {
    echo "OK   14 fortnight guides\n";
}
if (count($hubs) !== 12) {
    echo "FAIL: expected 12 quality hubs, got " . count($hubs) . PHP_EOL;
    $fail++;
} else {
    echo "OK   12 quality hubs\n";
}

foreach (array_keys($guides) as $slug) {
    $rel = '/pages/resources/' . $slug . '.php';
    $ok = is_file(SITE_ROOT . $rel);
    echo ($ok ? 'OK   ' : 'MISS ') . $rel . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
}
foreach (array_keys($hubs) as $slug) {
    $rel = '/pages/' . $slug . '.php';
    $ok = is_file(SITE_ROOT . $rel);
    echo ($ok ? 'OK   ' : 'MISS ') . $rel . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
}

$forbidden = [
    '/pages/packages/hmo.php',
    '/pages/packages/hmo-compliance.php',
    '/pages/packages/hmo-fire-safety.php',
    '/pages/packages/hmo-occupancy.php',
    '/includes/hmo.php',
    '/includes/hmo-package-page.php',
];
foreach ($forbidden as $rel) {
    if (is_file(SITE_ROOT . $rel)) {
        echo "FAIL unexpected HMO package file on this branch: {$rel}\n";
        $fail++;
    } else {
        echo "OK   HMO package file absent (PR #3): {$rel}\n";
    }
}

$kwDir = SITE_ROOT . '/pages/keywords';
$newKw = [];
if (is_dir($kwDir)) {
    foreach (glob($kwDir . '/*.php') ?: [] as $f) {
        $base = basename($f);
        if ($base === 'index.php') {
            continue;
        }
        $newKw[] = $base;
    }
}
if ($newKw) {
    echo "NOTE keyword hub files present (pre-existing or other agent): " . count($newKw) . " — wave-1 must not add a deep matrix.\n";
}

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
