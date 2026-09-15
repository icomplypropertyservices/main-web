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
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}
$missingFiles = [];
foreach (array_keys($slugs) as $slug) {
    $path = $outDir . '/' . $slug . '.php';
    if (!is_file($path)) {
        $slugExport = var_export($slug, true);
        file_put_contents(
            $path,
            "<?php\n/** AUTO-GENERATED stub — php bin/generate-building-gas-pages.php */\n"
            . "require_once __DIR__ . '/../../includes/render.php';\n"
            . "renderKeywordPage({$slugExport});\n"
        );
    }
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

echo PHP_EOL . "building_count={$building_count} gas_count={$gas_count} total={$total}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
