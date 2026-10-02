<?php
/**
 * Courtney Thorne manufacturer cards render, and Tunstall stays unpublished.
 * Usage: php website/bin/check-manufacturer-cards.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
$siteUrl = 'https://icomplypropertyservices.co.uk';
putenv('SITE_URL=' . $siteUrl);
$_ENV['SITE_URL'] = $siteUrl;
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'icomplypropertyservices.co.uk';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['REQUEST_SCHEME'] = 'https';

require_once dirname(__DIR__) . '/includes/router.php';
require_once dirname(__DIR__) . '/includes/shopify.php';

function checkRender(string $path): array
{
    if (function_exists('header_remove')) {
        header_remove();
    }
    http_response_code(200);
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['QUERY_STRING'] = '';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    ob_start();
    try {
        routerHandleRequest();
        $html = (string)ob_get_clean();
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        fwrite(STDERR, "Render error {$path}: " . $e->getMessage() . "\n");
        return ['html' => '', 'status' => 500];
    }
    $status = (int)http_response_code();
    return ['html' => $html, 'status' => $status === 0 ? 200 : $status];
}

$fail = 0;
$lines = [];
$expect = function (bool $ok, string $message) use (&$fail, &$lines): void {
    if ($ok) {
        $lines[] = "PASS  {$message}";
        return;
    }
    $fail++;
    $lines[] = "FAIL  {$message}";
};

$expect(function_exists('shopifyCardFromManufacturerProduct'), 'shopifyCardFromManufacturerProduct() is defined');

$catalog = getManufacturerCatalog();
$expect(isset($catalog['courtney-thorne']), 'Courtney Thorne remains in the public catalog');
$expect(!isset($catalog['tunstall']), 'Tunstall is not in the public catalog');
$expect(!in_array('Tunstall', getManufacturers('nurse-call'), true), 'Nurse-call brand list omits Tunstall');
$expect(!isset(getMajorKeywords()['tunstall-nurse-call']), 'Tunstall keyword hub is not public');

$courtney = checkRender('/pages/manufacturers/courtney-thorne');
$expect($courtney['status'] === 200, 'Courtney Thorne manufacturer page is HTTP 200');
$expect(str_contains($courtney['html'], 'Courtney Thorne Call Point / Pear Lead Pack'), 'Courtney Thorne call-point card is rendered');
$expect(str_contains($courtney['html'], 'shop-product-card'), 'Product card markup is present');
$expect(str_contains($courtney['html'], 'Courtney Thorne Panel Battery Kit'), 'Courtney Thorne battery card is rendered');
$expect(!str_contains(strtolower($courtney['html']), 'tunstall'), 'Courtney Thorne page does not mention Tunstall');
$expect(!str_contains($courtney['html'], 'PLACEHOLDER_DO_NOT_KEEP'), 'Placeholder shopify.php is not emitted');

$tunstall = checkRender('/pages/manufacturers/tunstall');
$expect($tunstall['status'] === 404, 'Tunstall manufacturer page is HTTP 404');
$expect(str_contains($tunstall['html'], 'Manufacturer not found'), 'Tunstall manufacturer body is the not-found response');
$expect(!str_contains($tunstall['html'], 'shop-product-card'), 'Tunstall page does not render product cards');

$keyword = checkRender('/pages/keywords/tunstall-nurse-call');
$expect($keyword['status'] === 404, 'Tunstall keyword page is HTTP 404');
$expect(str_contains($keyword['html'], 'Keyword not found'), 'Tunstall keyword body is the not-found response');

$static = checkRender('/pages/manufacturers/static-systems-group');
$expect($static['status'] === 200, 'Static Systems manufacturer page still renders');
$expect(str_contains($static['html'], 'shop-product-card'), 'Static Systems product cards render');

echo implode("\n", $lines) . "\n";
if ($fail > 0) {
    fwrite(STDERR, "{$fail} check(s) failed\n");
    exit(1);
}
echo "All manufacturer card checks passed\n";
exit(0);
