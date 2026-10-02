#!/usr/bin/env php
<?php
/**
 * Assert Security 164 + Water 130 + Plumbing 130 and that stub files exist.
 *
 * Usage:
 *   php website/bin/check-sec-water-plumb.php
 *   php website/bin/check-sec-water-plumb.php --skip-render
 */
declare(strict_types=1);

$options = getopt('', ['skip-render', 'dist::']);
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';

$skipRender = isset($options['skip-render']);
if (PHP_SAPI === 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$repoRoot = dirname(SITE_ROOT);
$dist = $options['dist'] ?? '';
if ($dist !== '' && $dist[0] !== '/') {
    $dist = $repoRoot . '/' . ltrim((string)$dist, '/');
}

$fail = 0;
$pass = 0;
$ok = static function (string $msg) use (&$pass): void {
    echo "OK   {$msg}\n";
    $pass++;
};
$bad = static function (string $msg) use (&$fail): void {
    echo "FAIL {$msg}\n";
    $fail++;
};

$expected = jobTypesSecWaterPlumbExpected();
$jobs = jobTypesSecWaterPlumbJobs();
$by = jobTypesSecWaterPlumbByFamily();
$slugs = [];
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    if ($slug !== '') {
        $slugs[] = $slug;
    }
}
$unique = array_values(array_unique($slugs));

if (count($jobs) !== $expected['total']) {
    $bad('catalogue count ' . count($jobs) . ' !== ' . $expected['total']);
} else {
    $ok('catalogue has exactly 424 jobs');
}
if (count($unique) !== $expected['total']) {
    $bad('slugs not unique (' . count($unique) . ')');
} else {
    $ok('all 424 slugs unique');
}

foreach (['security' => 164, 'water' => 130, 'plumbing' => 130] as $fam => $need) {
    $got = count($by[$fam] ?? []);
    if ($got !== $need) {
        $bad("{$fam} count {$got} !== {$need}");
    } else {
        $ok("{$fam} has exactly {$need} jobs");
    }
}

$stubDir = jobTypesSecWaterPlumbStubDir();
$missingFiles = [];
$badStubs = [];
foreach ($unique as $slug) {
    $path = $stubDir . '/' . $slug . '.php';
    if (!is_file($path)) {
        $missingFiles[] = $slug;
        continue;
    }
    $src = (string)file_get_contents($path);
    if (!str_contains($src, 'renderKeywordPage') || !str_contains($src, $slug)) {
        $badStubs[] = $slug;
    }
}
if ($missingFiles) {
    $bad('missing stub files: ' . count($missingFiles) . ' (sample ' . implode(',', array_slice($missingFiles, 0, 8)) . ')');
} else {
    $ok('all 424 /pages/keywords/<slug>.php files exist');
}
if ($badStubs) {
    $bad('stub contents invalid: ' . count($badStubs));
} else {
    $ok('stubs call renderKeywordPage(<slug>)');
}

$keywords = getMajorKeywords();
$missingKw = [];
foreach ($unique as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
    }
}
if ($missingKw) {
    $bad('getMajorKeywords() missing ' . count($missingKw) . ' family slugs');
} else {
    $ok('all 424 slugs present in getMajorKeywords()');
}

$titles = [];
$h1s = [];
$metas = [];
foreach ($jobs as $job) {
    $title = trim((string)($job['seo_title'] ?? ''));
    $h1 = trim((string)($job['h1'] ?? ''));
    $meta = trim((string)($job['meta_desc'] ?? ''));
    if ($title === '') {
        $bad('empty seo_title on ' . ($job['slug'] ?? '?'));
    }
    if ($h1 === '') {
        $bad('empty h1 on ' . ($job['slug'] ?? '?'));
    }
    if ($meta === '') {
        $bad('empty meta_desc on ' . ($job['slug'] ?? '?'));
    }
    $titles[$title] = ($titles[$title] ?? 0) + 1;
    $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
    $metas[$meta] = ($metas[$meta] ?? 0) + 1;
    $faq = $job['faq'] ?? [];
    if (!is_array($faq) || count($faq) < 2) {
        $bad('FAQ too short on ' . ($job['slug'] ?? '?'));
    }
}
$dupT = array_filter($titles, static fn(int $n): bool => $n > 1);
$dupH = array_filter($h1s, static fn(int $n): bool => $n > 1);
$dupM = array_filter($metas, static fn(int $n): bool => $n > 1);
if ($dupT) {
    $bad('duplicate titles: ' . count($dupT));
} else {
    $ok('unique titles for 424 jobs');
}
if ($dupH) {
    $bad('duplicate H1s: ' . count($dupH));
} else {
    $ok('unique H1s for 424 jobs');
}
if ($dupM) {
    $bad('duplicate meta descriptions: ' . count($dupM));
} else {
    $ok('unique meta descriptions for 424 jobs');
}

$relatedCounts = [];
$introNorms = [];
$slugSet = array_fill_keys($unique, true);
foreach ($jobs as $job) {
    $slug = keywordSlug((string)($job['slug'] ?? ''));
    $related = keywordSlug((string)($job['related'] ?? ''));
    $name = (string)($job['name'] ?? '');
    if ($related === '' || $related === $slug || !isset($slugSet[$related])) {
        $bad('related link is not another family job on ' . $slug);
    }
    $relatedCounts[$related] = ($relatedCounts[$related] ?? 0) + 1;
    $intro = (string)($job['intro'] ?? '');
    $norm = trim((string)preg_replace('/\s+/', ' ', str_ireplace($name, '', $intro)));
    $introNorms[$norm] = ($introNorms[$norm] ?? 0) + 1;
    foreach (['intro', 'body', 'meta_desc', 'h1', 'seo_title'] as $field) {
        $text = (string)($job[$field] ?? '');
        if (preg_match('/£\s*\d/', $text) || str_contains($text, 'Fixed-price') || str_contains($text, 'Fixed quotes')) {
            $bad("{$field} still sells a fixed price on {$slug}");
        }
    }
}
$relatedMax = $relatedCounts ? max($relatedCounts) : 0;
if ($relatedMax > 1) {
    $bad('related slug reused (max ' . $relatedMax . ')');
} else {
    $ok('each related slug is used once');
}
$introWorst = $introNorms ? max($introNorms) : 0;
if ($introWorst > 8 || count($introNorms) < 80) {
    $bad('intro copy still collapses (worst ' . $introWorst . ', patterns ' . count($introNorms) . ')');
} else {
    $ok('intro copy has ' . count($introNorms) . ' patterns (worst repeat ' . $introWorst . ')');
}

$rendered = 0;
$seoFail = [];
if (!$skipRender && !$missingKw && !$missingFiles) {
    foreach ($unique as $i => $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $needles = [
            '<title>',
            '<h1',
            'name="description"',
            'rel="canonical"',
            '/pages/keywords/' . $slug,
            'FAQPage',
            '#quote',
            '#0B1F3A',
            '#ff6b00',
            'Submit request',
            'Enquire for POA',
            'Price on application',
        ];
        $missing = [];
        foreach ($needles as $n) {
            if (!str_contains($html, $n)) {
                $missing[] = $n;
            }
        }
        if (!preg_match('/POA|Price on application|enquire/i', $html)) {
            $missing[] = 'poa-enquire';
        }
        if (preg_match('/£\s*\d/', $html)) {
            $missing[] = 'invented-£';
        }
        if (str_contains($html, 'Fixed quotes') || str_contains($html, 'Fixed-price')) {
            $missing[] = 'fixed-price-copy';
        }
        if (substr_count(strtolower($html), '<h1') !== 1) {
            $missing[] = 'h1-count';
        }
        if (strlen($html) < 1800) {
            $missing[] = 'short-html';
        }
        if ($missing) {
            $seoFail[] = $slug . '(' . implode('|', $missing) . ')';
        }
    }
    if ($seoFail) {
        $bad('SEO tags missing on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 5)) . ')');
    } else {
        $ok("required SEO + brand + POA CTA present on {$rendered} pages");
    }
    foreach (['anpr-cctv-system', 'calorifier-temperature-check', 'blocked-toilet-clearance'] as $sample) {
        ob_start();
        renderKeywordAreaPage($sample, 'Stockport');
        $townHtml = (string)ob_get_clean();
        $townBad = [];
        if (!str_contains($townHtml, 'Enquire for POA') || !str_contains($townHtml, 'Price on application')) {
            $townBad[] = 'missing-poa';
        }
        if (str_contains($townHtml, 'Fixed-price') || str_contains($townHtml, 'Fixed quotes')) {
            $townBad[] = 'fixed-price-copy';
        }
        if (preg_match('/£\s*\d/', $townHtml)) {
            $townBad[] = 'invented-£';
        }
        if ($townBad) {
            $bad('Stockport town page ' . $sample . ' (' . implode('|', $townBad) . ')');
        } else {
            $ok('Stockport town page is POA for ' . $sample);
        }
    }
} elseif ($skipRender) {
    echo "SKIP render checks (--skip-render)\n";
}

if ($dist !== '') {
    $distMissing = [];
    foreach ($unique as $slug) {
        $php = $dist . '/pages/keywords/' . $slug . '.php';
        $idx = $dist . '/pages/keywords/' . $slug . '/index.html';
        if (!is_file($php) && !is_file($idx)) {
            $distMissing[] = $slug;
        }
    }
    if ($distMissing) {
        $bad('dist missing ' . count($distMissing) . ' output files');
    } else {
        $ok('all 424 output files exist under dist/pages/keywords/');
    }
}

$sec = count($by['security'] ?? []);
$wat = count($by['water'] ?? []);
$plu = count($by['plumbing'] ?? []);
echo PHP_EOL . "security={$sec} water={$wat} plumbing={$plu} total=" . count($jobs) . " files=" . (424 - count($missingFiles)) . " rendered={$rendered}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
