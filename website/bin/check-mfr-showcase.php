<?php
/**
 * Assert AOV + barriers manufacturer boards: every brand, CAME partner, no Tunstall,
 * only approved prices, phone, and pop>10k town links.
 */
declare(strict_types=1);
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
require_once dirname(__DIR__) . '/includes/render.php';

$fail = 0;
$ok = static function (bool $cond, string $msg) use (&$fail): void {
    if ($cond) {
        echo "OK   {$msg}\n";
        return;
    }
    echo "FAIL {$msg}\n";
    $fail++;
};

$render = static function (callable $fn): string {
    ob_start();
    $fn();
    return (string)ob_get_clean();
};

$aovHub = $render(static function (): void { renderServiceHubPage('aov-air-handling'); });
$barHub = $render(static function (): void { renderServiceHubPage('barriers'); });
$aovTown = $render(static function (): void { renderServiceAreaPage('aov-air-handling', 'Manchester'); });
$barTown = $render(static function (): void { renderServiceAreaPage('barriers', 'Stockport'); });
$small = $render(static function (): void { renderServiceAreaPage('barriers', 'Keswick'); });

$board = static function (string $html): string {
    return preg_match('/<section class="mfr-board"[\s\S]*?<\/section>/', $html, $m) ? $m[0] : '';
};
foreach (['aov hub' => $aovHub, 'barriers hub' => $barHub, 'aov Manchester' => $aovTown, 'barriers Stockport' => $barTown] as $label => $html) {
    $ok($html !== '' && !preg_match('/Fatal error|Parse error/', $html), $label . ' renders');
    $ok(stripos($board($html), 'tunstall') === false, $label . ' manufacturer grid has no Tunstall');
    $ok(str_contains($html, '07517806082'), $label . ' shows phone');
    $ok(str_contains($html, 'id="mfr-quote"'), $label . ' has quote helper');
    $ok(str_contains($html, 'mfr-wordmark'), $label . ' has wordmarks');
}

$aovNames = icomplyCoverageManufacturerNames('aov-air-handling');
$barNames = icomplyCoverageManufacturerNames('barriers');
$ok(count($aovNames) === 18, 'AOV coverage count is 18 (got ' . count($aovNames) . ')');
$ok(count($barNames) === 13, 'barriers coverage count is 13 (got ' . count($barNames) . ')');
foreach ($aovNames as $name) {
    $ok(str_contains($aovHub, $name) && str_contains($aovTown, $name), 'AOV lists ' . $name);
}
foreach ($barNames as $name) {
    $ok(str_contains($barHub, $name) && str_contains($barTown, $name), 'barriers lists ' . $name);
}
$ok(str_contains($barHub, 'Barrier partner') && str_contains($barHub, 'id="mfr-came"'), 'CAME partner card');
$ok(str_contains($barHub, '£5,850.00') && str_contains($barHub, '£5,199.99') && str_contains($barHub, '£8,375.45'), 'published CAME pack prices');
$ok(str_contains($aovHub, '£2,450') && str_contains($aovHub, '£275'), 'locked AOV kit prices');
preg_match_all('/£[0-9][0-9,]*(?:\.[0-9]{2})?/', $barHub . $aovHub, $prices);
$allowed = ['£5,850.00', '£7,441.83', '£8,375.45', '£7,393.18', '£5,199.99', '£275', '£950', '£400', '£60', '£2,450'];
$odd = array_values(array_diff(array_unique($prices[0] ?? []), $allowed));
$ok($odd === [], 'no invented prices (' . implode(', ', $odd) . ')');
$towns = icomplyMfrIndexableTowns();
$ok(count($towns) > 100, 'pop>10k towns linked from data (' . count($towns) . ')');
$ok(str_contains($barHub, '/pages/barriers/manchester'), 'hub links a pop>10k town');
$ok(!icomplyMfrTownIndexable('Keswick') && str_contains($small, 'noindex'), 'under 10k town is noindex');
$ok(str_contains($aovTown, 'Manchester') && str_contains($barTown, 'Stockport'), 'town pages name the town');

echo $fail === 0 ? "\nMFR SHOWCASE PASSED\n" : "\n{$fail} FAILURES\n";
exit($fail > 0 ? 1 : 0);
