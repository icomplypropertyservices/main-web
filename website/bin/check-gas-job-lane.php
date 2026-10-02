<?php
/**
 * Gas safety / CP12 job lane: hub, £85, CTAs, landlord links.
 * Usage: php website/bin/check-gas-job-lane.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/config.php';
require_once $root . '/includes/router.php';

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

$fail = 0;
$pass = 0;
$log = [];

$say = static function (bool $ok, string $label, string $detail = '') use (&$fail, &$pass, &$log): void {
    if ($ok) {
        $pass++;
        $log[] = "PASS  {$label}";
        return;
    }
    $fail++;
    $log[] = "FAIL  {$label}" . ($detail !== '' ? " — {$detail}" : '');
};

$paths = [
    '/pages/jobs/gas-safety',
    '/pages/jobs/gas-safety-cp12',
    '/pages/jobs/landlord-gas-safety',
];

$render = static function (string $path): string {
    if (function_exists('header_remove')) {
        header_remove();
    }
    http_response_code(200);
    $_GET = [];
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['QUERY_STRING'] = '';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    ob_start();
    routerHandleRequest();
    return (string)ob_get_clean();
};

$landlordNeedles = [
    '/pages/landlords',
    '/pages/services/landlord-compliance',
    '/pages/landlord-certificates',
    '/pages/resources/gas-safety-certificate-landlords',
    '/pages/resources/landlord-compliance-checklist',
];

foreach ($paths as $path) {
    $html = $render($path);
    $status = (int)http_response_code();
    $say($status === 200 && str_contains($html, '<h1'), $path . ' renders', 'status ' . $status);
    $say(str_contains($html, '£85') && str_contains($html, 'data-gas-price="85"'), $path . ' shows £85');
    $say(str_contains($html, 'Book CP12') && str_contains($html, 'id="book"') && str_contains($html, 'WhatsApp') && str_contains($html, 'tel:'), $path . ' has CTAs');
    foreach ($landlordNeedles as $needle) {
        $say(str_contains($html, $needle), $path . ' links ' . $needle);
    }
    $say(str_contains($html, '"@type":"Offer"') && str_contains($html, '"price":"85.00"'), $path . ' offer JSON-LD');
    $say(!str_contains($html, '£69') && !str_contains($html, '£25'), $path . ' does not invent other gas prices');
}

$hub = $render('/pages/jobs/gas-safety');
$say(str_contains($hub, '/pages/jobs/gas-safety-cp12') && str_contains($hub, '/pages/jobs/landlord-gas-safety'), 'hub links both lane pages');

require_once $root . '/includes/sitemap.php';
$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
foreach ($paths as $path) {
    $say(str_contains($xml, $path . '</loc>'), 'sitemap lists ' . $path);
}

echo implode("\n", $log) . "\n\nPASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
