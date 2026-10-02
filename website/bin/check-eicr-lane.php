<?php
/**
 * EICR testing lane checks. Non-production. Does not deploy.
 *
 * Usage: php website/bin/check-eicr-lane.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/pages/keywords/eicr';

require_once $root . '/config.php';
require_once $root . '/includes/render.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$fail): void {
    if ($cond) {
        echo "OK  $msg\n";
        return;
    }
    echo "FAIL $msg\n";
    $fail++;
};

$price = eicrLaneListPrice();
$ok($price['display'] === '£249', 'list display is £249');
$ok($price['amount'] === '249.00', 'list amount is 249.00');
$ok($price['code'] === 'ELEC-EICR-6BED-NW', 'rate code is the 6-bed HMO code');

$keywords = getMajorKeywords();
foreach (['eicr', 'eicr-testing', 'eicr-cost', 'eicr-price', 'landlord-eicr', 'commercial-eicr', 'domestic-eicr', 'fixed-wire-testing'] as $slug) {
    $ok(isset($keywords[$slug]), "keyword data has $slug");
    $intro = (string)($keywords[$slug]['intro'] ?? '');
    $ok(str_contains($intro, '£249'), "$slug intro states £249");
    $ok(stripos($intro, '6-bed') !== false, "$slug intro scopes 6-bed");
    $ok(stripos($intro, 'price on application') !== false, "$slug intro keeps other sizes POA");
    $blob = $intro . ' ' . (string)($keywords[$slug]['body'] ?? '');
    $ok(!str_contains($blob, 'does not invent pound'), "$slug dropped the old no-price denial");
    $ok(stripos($blob, 'NICEIC') === false, "$slug copy does not claim NICEIC");
}

$render = static function (callable $fn): string {
    ob_start();
    $fn();
    return (string)ob_get_clean();
};

$pages = [
    'keyword eicr' => static function () {
        renderKeywordPage('eicr');
    },
    'keyword eicr-cost' => static function () {
        renderKeywordPage('eicr-cost');
    },
    'keyword commercial-eicr' => static function () {
        renderKeywordPage('commercial-eicr');
    },
    'keyword area stockport' => static function () {
        renderKeywordAreaPage('eicr', 'Stockport');
    },
    'service electrical' => static function () {
        renderServiceHubPage('electrical');
    },
];

foreach ($pages as $label => $fn) {
    $html = $render($fn);
    $ok(strlen($html) > 2000, "$label rendered");
    $ok(str_contains($html, 'id="eicr-lane"'), "$label has the lane panel");
    $ok(str_contains($html, 'data-eicr-price="249"'), "$label exposes 249");
    $ok(str_contains($html, 'ELEC-EICR-6BED-NW'), "$label shows the rate code");
    $ok(str_contains($html, 'price on application'), "$label says other scopes are POA");
    $ok(str_contains($html, 'pages/keywords/eicr-testing'), "$label links to EICR testing");
    $ok(str_contains($html, 'pages/services/electrical'), "$label links to the service hub");
    $ok(str_contains($html, 'pages/resources/eicr-guide'), "$label links to the EICR guide");
    $ok(str_contains($html, 'Request an EICR quote'), "$label has the quote CTA");
    $ok(str_contains($html, 'wa.me/'), "$label has a WhatsApp CTA");
    foreach (['£129', '£159', '£219', 'does not invent pound', 'from-£ EICR list', 'NICEIC certified approach'] as $bad) {
        $ok(!str_contains($html, $bad), "$label does not contain [$bad]");
    }
    $ok(!preg_match('/NICEIC certified approach|iComply.s NICEIC certified|provide membership details/i', $html), "$label does not claim NICEIC membership");
}

$niceic = (string)($keywords['niceic-certified']['intro'] ?? '');
$ok(isset($keywords['niceic-certified']), 'niceic-certified keyword still exists');
$ok(stripos($niceic, 'does not claim to be NICEIC certified') !== false, 'NICEIC page denies a membership claim');
$partp = (string)($keywords['part-p-certified']['intro'] ?? '');
$ok(stripos($partp, 'does not claim a Part P scheme registration') !== false, 'Part P page denies a scheme claim');

$pricing = (string)file_get_contents($root . '/pages/pricing.php');
$ok(str_contains($pricing, "'from' => '£249'"), 'pricing guide keeps the £249 list row');
$ok(str_contains($pricing, 'typical 6-bed HMO'), 'pricing guide names the 6-bed scope');
$ok(str_contains($pricing, 'EICR — 1-bed / small dwelling'), 'pricing guide lists 1-bed as its own row');
$ok(str_contains($pricing, 'EICR — commercial'), 'pricing guide lists commercial as its own row');
$electricalBlock = '';
if (preg_match("/'id' => 'electrical',(.*?)'id' => 'gas'/s", $pricing, $m)) {
    $electricalBlock = $m[1];
}
$ok($electricalBlock !== '', 'pricing guide electrical block found');
$ok(!str_contains($electricalBlock, '£129'), 'electrical pricing block does not invent £129');
$ok(!str_contains($electricalBlock, '£159'), 'electrical pricing block does not invent £159');
$ok(!str_contains($electricalBlock, '£49'), 'electrical pricing block does not invent a PAT fee');
$ok(!str_contains($electricalBlock, '£450'), 'electrical pricing block does not invent a consumer-unit fee');
$ok(!str_contains($pricing, '£219'), 'pricing guide does not invent £219 for a larger house');
$ok(!str_contains($pricing, 'EICR — 2–3 bed house'), 'pricing guide does not keep an invented 2–3 bed EICR row');

$guide = (string)file_get_contents($root . '/pages/resources/eicr-guide.php');
$ok(str_contains($guide, 'eicrLanePanelHtml'), 'EICR guide renders the lane panel');

echo $fail === 0 ? "PASS\n" : "FAIL $fail\n";
exit($fail === 0 ? 0 : 1);
