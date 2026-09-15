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
    '/pages/resources/gas-safety-certificate-landlords.php',
    '/pages/resources/fire-risk-assessment-guide.php',
    '/pages/resources/smoke-and-co-alarms.php',
    '/pages/resources/pat-testing-guide.php',
    '/pages/resources/epc-for-landlords.php',
    '/pages/resources/fire-door-inspection.php',
    '/pages/resources/fire-extinguisher-servicing.php',
    '/pages/resources/kitchen-fire-suppression-guide.php',
    '/pages/resources/let-ready-void-checklist.php',
    '/pages/resources/commercial-fire-safety-basics.php',
    '/pages/resources/care-home-fire-and-nurse-call.php',
    '/pages/resources/electrical-safety-rented-homes.php',
    '/pages/resources/booking-compliance-certificates.php',
    '/pages/resources/greater-manchester-property-compliance.php',
    '/pages/landlord-certificates.php',
    '/pages/gas-safety-certificate.php',
    '/pages/fire-risk-assessment.php',
    '/pages/electrical-safety-landlords.php',
    '/pages/commercial-fire-safety.php',
    '/pages/smoke-carbon-monoxide-alarms.php',
    '/pages/portable-appliance-testing.php',
    '/pages/energy-performance-certificates.php',
    '/pages/fire-door-compliance.php',
    '/pages/emergency-lighting-compliance.php',
    '/pages/stockport-property-compliance.php',
    '/pages/manchester-property-compliance.php',
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
