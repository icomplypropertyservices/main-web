<?php
/**
 * CLI check: hub + resource + manifest paths resolve to physical files.
 * Usage: php bin/check-hub-404s.php
 */
require_once __DIR__ . '/../config.php';

$required = [
    '/pages/areas.php',
    '/pages/manufacturers.php',
    '/pages/resources.php',
    '/pages/services.php',
    '/pages/keywords.php',
    '/pages/resources/eicr-guide.php',
    '/pages/resources/fire-alarm-servicing.php',
    '/pages/resources/emergency-lighting-testing.php',
    '/pages/resources/cctv-for-business.php',
    '/pages/resources/access-control-guide.php',
    '/pages/resources/landlord-compliance-checklist.php',
    '/manifest.json',
    '/favicon.ico',
    '/assets/images/favicon.ico',
    '/assets/images/favicon.svg',
    '/assets/images/services/fire-risk-assessments-photo.jpg',
    '/privacy.php',
    '/terms.php',
    '/404.php',
    '/thank-you.php',
    '/robots.txt',
    '/sitemap.xml',
];

$fail = 0;
foreach ($required as $rel) {
    $abs = SITE_ROOT . $rel;
    $ok = is_file($abs);
    echo ($ok ? 'OK   ' : 'MISS ') . $rel . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
}

echo PHP_EOL . ($fail === 0 ? 'PASS' : "FAIL ({$fail} missing)") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
