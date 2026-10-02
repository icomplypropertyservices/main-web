<?php
/**
 * Compliance Bundle SSOT: one published price, both conversion URLs, EICR/gas/FRA cross-sell.
 * Usage: php website/bin/check-compliance-bundle.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/compliance-bundle.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$fail): void {
    if ($cond) {
        echo "OK   {$msg}\n";
        return;
    }
    echo "FAIL {$msg}\n";
    $fail++;
};

$bundle = icomplyComplianceBundle();
$label = icomplyComplianceBundlePriceLabel();
$ok(icomplyComplianceBundlePriceGbp() === 650, 'price gbp is 650');
$ok($label === '£650', 'price label is £650');
$ok($bundle['price_label'] === $label, 'bundle array uses the label helper');
$ok($bundle['price_gbp'] === 650, 'bundle array price matches helper');

$root = SITE_ROOT;
$skip = [
    $root . '/includes/compliance-bundle.php',
    $root . '/bin/check-compliance-bundle.php',
];
$hardcoded = [];
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $path = $file->getPathname();
    if (in_array($path, $skip, true)) {
        continue;
    }
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['php', 'html', 'js', 'css', 'md'], true)) {
        continue;
    }
    $rel = str_replace($root . '/', '', $path);
    if (str_starts_with($rel, 'shop/') || str_starts_with($rel, 'data/')) {
        continue;
    }
    $contents = (string)file_get_contents($path);
    if (str_contains($contents, '£650')) {
        $hardcoded[] = str_replace($root . '/', '', $path);
    }
}
$ok($hardcoded === [], 'no hardcoded £650 outside the SSOT (' . implode(', ', $hardcoded) . ')');

$keys = icomplyComplianceBundleCrossSellKeys();
$ok($keys === ['electrical', 'gas-systems', 'fire-risk-assessments'], 'cross-sell keys are EICR, gas, FRA');

foreach (['electrical', 'gas-systems', 'fire-risk-assessments', 'electrical-safety-landlords', 'gas-safety-certificate', 'fire-risk-assessment', 'landlords', 'packages', 'pricing'] as $from) {
    $html = icomplyComplianceBundleCrossSellHtml($from);
    $ok(str_contains($html, $label), "cross-sell {$from} shows the published price");
    $ok(str_contains($html, '/pages/packages/compliance-bundle'), "cross-sell {$from} links the canonical pack");
    $ok(!str_contains($html, '£199') && !str_contains($html, 'POA'), "cross-sell {$from} does not invent another pack price");
}
$ok(icomplyComplianceBundleCrossSellHtml('cctv') === '', 'unrelated services get no cross-sell');

$render = static function (string $script): string {
    putenv('ICOMPLY_STATIC_EXPORT=1');
    $_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
    $_SERVER['REQUEST_URI'] = $script;
    $_SERVER['HTTP_HOST'] = 'localhost';
    ob_start();
    try {
        include SITE_ROOT . $script;
    } catch (Throwable $e) {
        ob_end_clean();
        return 'ERR ' . $e->getMessage();
    }
    return (string)ob_get_clean();
};

$bundleHtml = $render('/pages/packages/compliance-bundle.php');
$packHtml = $render('/pages/packages/landlord-pack.php');
$ok(!str_starts_with($bundleHtml, 'ERR'), 'compliance-bundle page renders');
$ok(!str_starts_with($packHtml, 'ERR'), 'landlord-pack page renders');
$ok(str_contains($bundleHtml, $label) && str_contains($packHtml, $label), 'both conversion pages show the SSOT price');
$ok(str_contains($bundleHtml, 'Compliance Bundle') && str_contains($packHtml, 'Landlord pack'), 'both headlines are present');
$ok(substr_count($bundleHtml, 'rel="canonical"') >= 1, 'bundle page has a canonical');
$canonicals = [];
foreach ([$bundleHtml, $packHtml] as $html) {
    if (preg_match('#rel="canonical" href="([^"]+)"#', $html, $m)) {
        $canonicals[] = $m[1];
    }
}
$ok(count($canonicals) === 2 && $canonicals[0] === $canonicals[1], 'both URLs canonicalise to the Compliance Bundle');
$ok(str_contains($canonicals[0] ?? '', '/pages/packages/compliance-bundle'), 'canonical path is the bundle URL');
$ok(str_contains($bundleHtml, '"price":"650"') && str_contains($packHtml, '"price":"650"'), 'Offer schema price is 650');
$ok(str_contains($bundleHtml, 'name="service"') && str_contains($bundleHtml, $bundle['service_value']), 'quote form selects the pack');

foreach (['/pages/services/electrical.php', '/pages/services/gas-systems.php', '/pages/services/fire-risk-assessments.php'] as $svc) {
    $html = $render($svc);
    $ok(!str_starts_with($html, 'ERR'), "{$svc} renders");
    $ok(str_contains($html, 'data-compliance-bundle='), "{$svc} has the cross-sell");
    $ok(str_contains($html, $label), "{$svc} cross-sell uses the SSOT price");
}

foreach (['/pages/packages.php', '/pages/landlords.php', '/pages/pricing.php', '/pages/electrical-safety-landlords.php', '/pages/gas-safety-certificate.php', '/pages/fire-risk-assessment.php'] as $hub) {
    $html = $render($hub);
    $ok(!str_starts_with($html, 'ERR'), "{$hub} renders");
    $ok(str_contains($html, 'data-compliance-bundle='), "{$hub} has the cross-sell");
}

if ($fail > 0) {
    fwrite(STDERR, "{$fail} check(s) failed\n");
    exit(1);
}
echo "All compliance-bundle checks passed\n";
exit(0);
