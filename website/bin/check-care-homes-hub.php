<?php
/**
 * Care-homes lane checks. Non-production: does not deploy.
 *
 * php website/bin/check-care-homes-hub.php
 */
define('SITE_ROOT', dirname(__DIR__));

$fail = 0;
$ok = static function (bool $pass, string $msg) use (&$fail): void {
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $msg . "\n";
    if (!$pass) {
        $fail++;
    }
};

$page = SITE_ROOT . '/pages/care-homes.php';
$mirror = dirname(SITE_ROOT) . '/pages/care-homes.php';
$src = is_file($page) ? (string)file_get_contents($page) : '';
$keywords = json_decode((string)file_get_contents(SITE_ROOT . '/data/keywords.json'), true);

$ok($src !== '', 'care-homes hub source exists');
$ok(is_file($mirror) && str_contains((string)file_get_contents($mirror), 'website/pages/care-homes.php'), 'repo-root mirror requires the hub');
$ok(str_contains($src, 'data-cross-sell="nurse-call"'), 'nurse call cross-sell band is present');
$ok(str_contains($src, 'data-cross-sell="nurse-call-quote"'), 'quote block repeats the nurse call ask');
$ok(str_contains($src, "url('/pages/services/nurse-call.php')"), 'links to the nurse call service hub');
$ok(str_contains($src, '?focus=nurse-call#quote'), 'primary actions open a nurse-call quote');
$ok(!preg_match('/£\s*\d/', $src), 'hub does not publish a pound price');
$ok(stripos($src, 'Portfolio discount') === false, 'no invented portfolio discount');
$ok(stripos($src, 'not a CQC') !== false || stripos($src, 'not CQC') !== false, 'states this is not a CQC inspection');

$requiredJobs = [
    'care-home-nurse-call',
    'care-home-call-system',
    'nursing-home-nurse-call',
    'residential-care-nurse-call',
    'nurse-call-system-installation',
    'nurse-call-upgrade',
    'nurse-call-maintenance',
    'nurse-call-service-contract',
    'nurse-call-repair',
    'nurse-call-pear-lead-replacement',
    'multi-site-nurse-call-maintenance',
    'nursing-home-call-system',
    'care-home-fire-risk-assessment',
    'care-home-fire-alarm',
    'care-home-emergency-lighting',
    'care-home-evacuation-alerts',
    'cctv-for-care-homes',
];
foreach ($requiredJobs as $slug) {
    $ok(isset($keywords[$slug]) && is_array($keywords[$slug]), "keyword exists: {$slug}");
    $ok(str_contains($src, "'" . $slug . "'"), "hub lists job: {$slug}");
}

echo $fail === 0 ? "PASS care-homes hub\n" : "FAIL care-homes hub ({$fail})\n";
exit($fail === 0 ? 0 : 1);
