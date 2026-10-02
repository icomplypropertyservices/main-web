#!/usr/bin/env php
<?php
/**
 * Assert Building (247) + Gas (223) = 470 job-type pages exist.
 *
 * Usage:
 *   php website/bin/check-building-gas.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$fail = 0;
$pass = 0;
$ok = static function (bool $cond, string $msg) use (&$pass, &$fail): void {
    if ($cond) {
        $pass++;
        echo "OK   {$msg}\n";
    } else {
        $fail++;
        echo "FAIL {$msg}\n";
    }
};

$building = jobTypesBuildingJobs();
$gas = jobTypesGasJobs();
$building_count = count($building);
$gas_count = count($gas);
$total = $building_count + $gas_count;

$ok($building_count === 247, "building_count==247 (got {$building_count})");
$ok($gas_count === 223, "gas_count==223 (got {$gas_count})");
$ok($total === 470, "total==470 (got {$total})");

$slugs = [];
foreach (array_merge($building, $gas) as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug === '') {
        $fail++;
        echo "FAIL empty slug\n";
        continue;
    }
    if (isset($slugs[$slug])) {
        $fail++;
        echo "FAIL duplicate slug {$slug}\n";
        continue;
    }
    $slugs[$slug] = $job;
}
$ok(count($slugs) === 470, 'all 470 slugs unique');

$keywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
$missingKw = [];
foreach (array_keys($slugs) as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
    }
}
$ok($missingKw === [], 'all 470 slugs present in getMajorKeywords()' . ($missingKw ? ' missing=' . implode(',', array_slice($missingKw, 0, 8)) : ''));

$outDir = SITE_ROOT . '/pages/keywords';
$missingFiles = [];
foreach (array_keys($slugs) as $slug) {
    $path = $outDir . '/' . $slug . '.php';
    if (!is_file($path)) {
        $missingFiles[] = $slug;
    }
}
$ok($missingFiles === [], 'all 470 outputs exist under /pages/keywords/<slug>' . ($missingFiles ? ' missing=' . count($missingFiles) : ''));

$titles = [];
$h1s = [];
$seoFail = [];
$wave1 = [];
if (function_exists('seoIaWave1JobSlugs')) {
    foreach (seoIaWave1JobSlugs() as $wSlug) {
        $wave1[keywordSlug((string)$wSlug)] = true;
    }
}
foreach ($slugs as $slug => $job) {
    $row = $keywords[$slug] ?? $job;
    $title = trim((string)($row['seo_title'] ?? $row['name'] ?? $slug));
    $h1 = trim((string)($row['h1'] ?? $row['name'] ?? $slug));
    $titles[$title] = ($titles[$title] ?? 0) + 1;
    $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
    $blob = $title . ' ' . (string)($row['intro'] ?? '') . ' ' . (string)($row['body'] ?? '') . ' ' . (string)($row['meta_desc'] ?? '') . ' ' . json_encode($row['faq'] ?? []);
    $familyBlob = (string)($job['intro'] ?? '') . ' ' . (string)($job['body'] ?? '') . ' ' . (string)($job['meta_desc'] ?? '') . ' ' . json_encode($job['faq'] ?? []);
    if (preg_match('/£\s*\d/', $blob) || preg_match('/£\s*\d/', $familyBlob)) {
        $seoFail[] = $slug . ':invented-£';
    }
    if (empty($job['faq']) && empty($row['faq'])) {
        $seoFail[] = $slug . ':no-faq';
    }
    if (isset($wave1[$slug])) {
        continue;
    }
    if (!str_contains($blob, 'POA') && !str_contains(strtolower($blob), 'price on application')
        && !str_contains($familyBlob, 'POA')) {
        $seoFail[] = $slug . ':no-POA';
    }
    if (!str_contains($blob, 'iComply') && !str_contains($blob, 'Icomply')
        && !str_contains($familyBlob, 'iComply')) {
        $seoFail[] = $slug . ':no-brand';
    }
}
$dupTitles = array_filter($titles, static fn(int $n): bool => $n > 1);
$dupH1 = array_filter($h1s, static fn(int $n): bool => $n > 1);
$ok($dupTitles === [], 'unique SEO titles' . ($dupTitles ? ' dups=' . count($dupTitles) : ''));
$ok($dupH1 === [], 'unique H1s' . ($dupH1 ? ' dups=' . count($dupH1) : ''));
$ok($seoFail === [], 'FAQ / brand lock / POA CTAs' . ($seoFail ? ' sample=' . implode(';', array_slice($seoFail, 0, 6)) : ''));

$gasWrong = [];
foreach ($gas as $job) {
    if (($job['service'] ?? '') !== 'gas-systems') {
        $gasWrong[] = $job['slug'] ?? '?';
    }
}
$ok($gasWrong === [], 'all gas jobs service=gas-systems');

$protected = ['window-sealing', 'smell-of-gas', 'suspected-gas-leak', 'repairing-a-boiler', 'void-gas-safety-certificate'];
$missingProtected = array_values(array_filter($protected, static fn(string $slug): bool => !isset($slugs[$slug])));
$ok($missingProtected === [], 'safety and verb forms kept' . ($missingProtected ? ' missing=' . implode(',', $missingProtected) : ''));

$dropped = [
    'boiler-install-stockport', 'cp12-north-west', 'gas-engineer-manchester',
    'gas-safety-certificate-stockport', 'gas-safety-greater-manchester', 'landlord-gas-stockport',
    'carbon-monoxide-alarm-for-landlords', 'co-alarm-near-boiler', 'bonding-gas-pipe',
];
$stillThere = array_values(array_filter($dropped, static fn(string $slug): bool => isset($slugs[$slug])));
$ok($stillThere === [], 'town doorway and wrong-trade pages are not in this pack' . ($stillThere ? ' still=' . implode(',', $stillThere) : ''));

$copyFail = [];
foreach ($slugs as $slug => $job) {
    $blob = (string)($job['seo_title'] ?? '')
        . ' ' . (string)($job['intro'] ?? '')
        . ' ' . (string)($job['body'] ?? '')
        . ' ' . (string)($job['meta_desc'] ?? '')
        . ' ' . json_encode($job['faq'] ?? [])
        . ' ' . json_encode($job['focus_points'] ?? []);
    if (str_contains((string)($job['seo_title'] ?? ''), 'Building & Gas')) {
        $copyFail[] = $slug . ':building-and-gas-title';
    }
    if (str_contains($blob, 'panel, boiler or door brand')) {
        $copyFail[] = $slug . ':boilerplate-brand-ask';
    }
    if (str_contains($blob, 'BS 5628')) {
        $copyFail[] = $slug . ':bs5628';
    }
    if (str_contains($blob, 'CP44') && !preg_match('/commercial|catering|plant|cp44/', $slug)) {
        $copyFail[] = $slug . ':cp44';
    }
    if (str_contains($slug, 'cp44') && !str_contains($blob, 'not a domestic CP12')) {
        $copyFail[] = $slug . ':cp44-called-domestic';
    }
    $name = (string)($job['name'] ?? '');
    if ($name !== '' && str_starts_with((string)($job['intro'] ?? ''), $name . ' sits under')) {
        $copyFail[] = $slug . ':name-sits';
    }
    $rel = keywordSlug((string)($job['related'] ?? ''));
    $relService = (string)($slugs[$rel]['service'] ?? '');
    if ($rel === '' || $relService !== (string)($job['service'] ?? '')) {
        $copyFail[] = $slug . ':related-other-service';
    }
    if (preg_match('/gas-leak|suspected-gas|smell-of-gas/', $slug) && !str_contains($blob, '0800 111 999')) {
        $copyFail[] = $slug . ':no-gas-emergency-number';
    }
    if ($slug === 'commercial-cp12' && !str_contains($blob, 'domestic CP12')) {
        $copyFail[] = $slug . ':commercial-cp12';
    }
    if (preg_match('/tenant/', $slug) && !str_contains(strtolower($blob), 'landlord')) {
        $copyFail[] = $slug . ':tenant-no-landlord';
    }
    if (preg_match('/24-hour|same-day|weekend/', $slug) && !str_contains($slug, 'gas-leak') && !str_contains(strtolower($blob), 'guarantee')) {
        $copyFail[] = $slug . ':availability-claim';
    }
}
$ok($copyFail === [], 'trade-specific copy' . ($copyFail ? ' sample=' . implode(';', array_slice($copyFail, 0, 6)) : ''));

echo PHP_EOL . "building_count={$building_count} gas_count={$gas_count} total={$total}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
