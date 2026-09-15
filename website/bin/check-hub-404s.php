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
    '/pages/resources/hmo-licence-compliance-checklist.php',
    '/pages/packages/hmo.php',
    '/pages/packages/hmo-compliance.php',
    '/pages/packages/hmo-fire-safety.php',
    '/pages/packages/hmo-occupancy.php',
    '/pages/hmo-landlords.php',
    '/pages/hmo-eicr.php',
    '/pages/hmo-fire-alarms.php',
    '/pages/hmo-emergency-lighting.php',
    '/pages/hmo-fire-doors.php',
    '/pages/hmo-eicr/stockport.php',
    '/pages/hmo-eicr/manchester.php',
    '/pages/hmo-fra.php',
    '/pages/hmo-gas-safety.php',
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
