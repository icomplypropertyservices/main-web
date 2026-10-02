#!/usr/bin/env php
<?php
/**
 * Assert Electrical master coverage: exactly 340 slugs and every hub file exists.
 *
 * Usage:
 *   php website/bin/check-electrical-pages.php
 *   php website/bin/check-electrical-pages.php --skip-render
 */
declare(strict_types=1);

$options = getopt('', ['skip-render']);
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
putenv('SITE_URL=https://icomplypropertyservices.co.uk');
$_ENV['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
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

$expected = electricalJobsExpectedCount();
$slugs = electricalJobsSlugs();
$unique = array_values(array_unique($slugs));
$count = count($slugs);
$uniqueCount = count($unique);

echo "electrical_page_count={$count}\n";

if ($count !== $expected) {
    $bad("electrical_page_count {$count} !== {$expected}");
} else {
    $ok("electrical_page_count == {$expected}");
}
if ($uniqueCount !== $expected) {
    $bad("Electrical slugs not unique ({$uniqueCount})");
} else {
    $ok('Electrical slugs all unique');
}

$missingFiles = [];
foreach ($unique as $slug) {
    $path = electricalJobsStubPath($slug);
    if (!is_file($path)) {
        $missingFiles[] = $slug;
        continue;
    }
    $src = (string)file_get_contents($path);
    if (!str_contains($src, 'renderKeywordPage') || !str_contains($src, $slug)) {
        $missingFiles[] = $slug . '(bad-stub)';
    }
}
if ($missingFiles) {
    $bad('missing Electrical files: ' . count($missingFiles) . ' (sample ' . implode(',', array_slice($missingFiles, 0, 8)) . ')');
} else {
    $ok("all {$expected} Electrical files exist under pages/keywords/");
}

$keywords = getMajorKeywords();
$missingKw = [];
$wrongService = [];
foreach ($unique as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
        continue;
    }
    if (($keywords[$slug]['service'] ?? '') !== 'electrical') {
        $wrongService[] = $slug;
    }
}
if ($missingKw) {
    $bad('catalogue missing ' . count($missingKw) . ' Electrical slugs');
} else {
    $ok('all Electrical slugs present in getMajorKeywords()');
}
if ($wrongService) {
    $bad('non-electrical service on ' . count($wrongService) . ' Electrical slugs');
} else {
    $ok('all Electrical master slugs tagged service=electrical');
}

$skipRender = isset($options['skip-render']);
$titles = [];
$h1s = [];
$metas = [];
$seoFail = [];
$rendered = 0;

if (!$skipRender) {
    foreach ($unique as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $row = $keywords[$slug] ?? [];
        $title = (string)($row['seo_title'] ?? $row['name'] ?? $slug);
        $h1 = (string)($row['h1'] ?? $row['name'] ?? $slug);
        $meta = (string)($row['meta_desc'] ?? '');
        $titles[$title] = ($titles[$title] ?? 0) + 1;
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
        $metas[$meta] = ($metas[$meta] ?? 0) + 1;

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
            'POA',
            'Enquire',
        ];
        $missing = [];
        foreach ($needles as $n) {
            if (!str_contains($html, $n)) {
                $missing[] = $n;
            }
        }
        if (preg_match('/£\s*\d/', $html)) {
            $missing[] = 'invented-£';
        }
        if (strlen($html) < 1800) {
            $missing[] = 'short-html';
        }
        $canon = 'https://icomplypropertyservices.co.uk/pages/keywords/' . $slug;
        if (!str_contains($html, 'rel="canonical" href="' . $canon . '"')) {
            $missing[] = 'canonical';
        }
        if (str_contains($html, 'this.src=$SERVICE_IMAGE') || str_contains($html, 'this.src=$KEYWORD')) {
            $missing[] = 'broken-image-fallback';
        }
        if (stripos($html, 'boiler or door brand') !== false || stripos($html, 'IETS Code of Practice') !== false) {
            $missing[] = 'wrong-claim';
        }
        if (preg_match('/fixed[ -]?price/i', $html)) {
            $missing[] = 'fixed-price-claim';
        }
        if (str_contains($html, 'every North West town')) {
            $missing[] = 'every-town-claim';
        }
        $siblings = preg_match_all('/rounded-full text-xs font-semibold text-zinc-900/', $html);
        if ($siblings > 16) {
            $missing[] = 'sibling-overflow-' . $siblings;
        }
        $areaLinks = preg_match_all('#/pages/keywords/' . preg_quote($slug, '#') . '/#', $html);
        $matrix = function_exists('electricalTownMatrixSlugs') ? electricalTownMatrixSlugs() : [];
        if (isset($matrix[$slug])) {
            if ($areaLinks < 100) {
                $missing[] = 'matrix-areas-' . $areaLinks;
            }
        } elseif ($areaLinks > 16) {
            $missing[] = 'hub-area-overflow-' . $areaLinks;
        }
        if ($missing) {
            $seoFail[] = $slug . '(' . implode('|', $missing) . ')';
        }
    }

    $dupTitles = array_filter($titles, static fn(int $n): bool => $n > 1);
    $dupH1 = array_filter($h1s, static fn(int $n): bool => $n > 1);
    $dupMeta = array_filter($metas, static fn(int $n): bool => $n > 1 && $n !== '');
    if ($dupTitles) {
        $bad('duplicate Electrical titles: ' . count($dupTitles));
    } else {
        $ok('unique title for every Electrical page');
    }
    if ($dupH1) {
        $bad('duplicate Electrical H1s: ' . count($dupH1));
    } else {
        $ok('unique H1 for every Electrical page');
    }
    if ($dupMeta) {
        $bad('duplicate Electrical meta descriptions: ' . count($dupMeta));
    } else {
        $ok('unique meta description for every Electrical page');
    }
    if ($seoFail) {
        $bad('SEO/brand/POA missing on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 5)) . ')');
    } else {
        $ok("required SEO, FAQ JSON-LD, brand tokens and POA CTAs on {$rendered} Electrical pages");
    }
}

echo PHP_EOL . "electrical_page_count={$count} expected={$expected} rendered={$rendered} files_missing=" . count($missingFiles) . PHP_EOL;
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
