#!/usr/bin/env php
<?php
/**
 * Assert the CCTV job lane: 73 slugs, every keyword file, unique SEO, service hub links.
 *
 * Usage:
 *   php website/bin/check-cctv-pages.php
 *   php website/bin/check-cctv-pages.php --skip-render
 */
declare(strict_types=1);

$options = getopt('', ['skip-render']);
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';

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

$expected = cctvJobsExpectedCount();
$slugs = cctvJobsSlugs();
$unique = array_values(array_unique($slugs));
$count = count($slugs);
$uniqueCount = count($unique);

echo "cctv_page_count={$count}\n";

if ($count !== $expected || $expected !== 73) {
    $bad("cctv_page_count {$count} expected 73 (catalogue says {$expected})");
} else {
    $ok('cctv_page_count == 73');
}
if ($uniqueCount !== $expected) {
    $bad("CCTV slugs not unique ({$uniqueCount})");
} else {
    $ok('CCTV slugs all unique');
}

$missingFiles = [];
foreach ($unique as $slug) {
    $path = cctvJobsStubPath($slug);
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
    $bad('missing CCTV files: ' . count($missingFiles) . ' (sample ' . implode(',', array_slice($missingFiles, 0, 8)) . ')');
} else {
    $ok("all {$expected} CCTV files exist under pages/keywords/");
}

$keywords = getMajorKeywords();
$missingKw = [];
$wrongService = [];
foreach ($unique as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
        continue;
    }
    if (($keywords[$slug]['service'] ?? '') !== 'cctv') {
        $wrongService[] = $slug;
    }
}
if ($missingKw) {
    $bad('catalogue missing ' . count($missingKw) . ' CCTV slugs (sample ' . implode(',', array_slice($missingKw, 0, 6)) . ')');
} else {
    $ok('all CCTV slugs present in getMajorKeywords()');
}
if ($wrongService) {
    $bad('non-cctv service on ' . count($wrongService) . ' CCTV slugs');
} else {
    $ok('all CCTV lane slugs tagged service=cctv');
}

$serviceFile = SITE_ROOT . '/pages/services/cctv.php';
if (!is_file($serviceFile) || !str_contains((string)file_get_contents($serviceFile), "renderServiceHubPage('cctv')")) {
    $bad('pages/services/cctv.php does not render the CCTV hub');
} else {
    $ok('pages/services/cctv.php is the CCTV service hub');
}

$skipRender = isset($options['skip-render']);
$titles = [];
$h1s = [];
$metas = [];
$rendered = 0;
$priceHits = [];
$faqMiss = [];
$brandMiss = [];

if (!$skipRender) {
    foreach ($unique as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $row = $keywords[$slug] ?? [];
        $name = (string)($row['name'] ?? $slug);
        if (!preg_match('#<title>(.*?)</title>#s', $html, $tm)) {
            $faqMiss[] = $slug . ':title';
            continue;
        }
        $title = html_entity_decode(trim($tm[1]), ENT_QUOTES, 'UTF-8');
        if (!preg_match('#<h1[^>]*>(.*?)</h1>#s', $html, $hm)) {
            $faqMiss[] = $slug . ':h1';
            continue;
        }
        $h1 = trim(html_entity_decode(strip_tags($hm[1]), ENT_QUOTES, 'UTF-8'));
        if (!preg_match('#name="description" content="(.*?)"#s', $html, $mm)) {
            $faqMiss[] = $slug . ':meta';
            continue;
        }
        $meta = html_entity_decode(trim($mm[1]), ENT_QUOTES, 'UTF-8');
        $titles[$title] = ($titles[$title] ?? 0) + 1;
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
        $metas[$meta] = ($metas[$meta] ?? 0) + 1;
        if ($h1 !== $name) {
            $faqMiss[] = $slug . ':h1-name';
        }
        if (!str_contains($html, 'FAQPage') || !str_contains($html, '/pages/keywords/' . $slug)) {
            $faqMiss[] = $slug . ':schema';
        }
        if (!str_contains($html, '#ff6b00') || (!str_contains($html, '#061828') && !str_contains($html, '#0B1F3A'))) {
            $brandMiss[] = $slug;
        }
        if (preg_match('/£\s*\d/', $html)) {
            $priceHits[] = $slug;
        }
    }

    $dup = static function (array $map): int {
        $n = 0;
        foreach ($map as $c) {
            if ($c > 1) {
                $n++;
            }
        }
        return $n;
    };
    if ($rendered !== $expected) {
        $bad("rendered {$rendered} !== {$expected}");
    } else {
        $ok("rendered {$rendered} CCTV keyword pages");
    }
    if ($dup($titles) > 0) {
        $bad('duplicate title strings: ' . $dup($titles));
    } else {
        $ok('unique <title> per CCTV slug');
    }
    if ($dup($h1s) > 0) {
        $bad('duplicate h1 strings: ' . $dup($h1s));
    } else {
        $ok('unique <h1> per CCTV slug');
    }
    if ($dup($metas) > 0) {
        $bad('duplicate meta descriptions: ' . $dup($metas));
    } else {
        $ok('unique meta description per CCTV slug');
    }
    if ($faqMiss) {
        $bad('SEO/FAQ gaps: ' . count($faqMiss) . ' (sample ' . implode(',', array_slice($faqMiss, 0, 6)) . ')');
    } else {
        $ok('FAQPage JSON-LD, canonical path and H1 name on every CCTV page');
    }
    if ($brandMiss) {
        $bad('brand tokens missing on ' . count($brandMiss));
    } else {
        $ok('navy and #ff6b00 present on CCTV keyword pages');
    }
    if ($priceHits) {
        $bad('invented £ prices on ' . implode(',', array_slice($priceHits, 0, 8)));
    } else {
        $ok('no £ digit prices on CCTV keyword pages');
    }

    ob_start();
    renderServiceHubPage('cctv');
    $hub = (string)ob_get_clean();
    $hubMiss = [];
    foreach ($unique as $slug) {
        if (!str_contains($hub, '/pages/keywords/' . $slug)) {
            $hubMiss[] = $slug;
        }
    }
    if (!str_contains($hub, 'CCTV job lane') || !str_contains($hub, 'FAQPage')) {
        $bad('CCTV service hub missing lane copy or FAQPage');
    } elseif ($hubMiss) {
        $bad('service hub missing ' . count($hubMiss) . ' keyword links (sample ' . implode(',', array_slice($hubMiss, 0, 6)) . ')');
    } elseif (preg_match('/£\s*\d/', $hub)) {
        $bad('invented £ price on the CCTV service hub');
    } else {
        $ok('pages/services/cctv lists all 73 CCTV keyword guides');
    }
}

echo $fail === 0 ? "PASS ({$pass})\n" : "FAIL {$fail} (pass {$pass})\n";
exit($fail === 0 ? 0 : 1);
