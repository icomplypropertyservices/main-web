#!/usr/bin/env php
<?php
/**
 * Deterministic 1,753-job master validation.
 *
 * Usage:
 *   php website/bin/check-job-types-master.php
 *   php website/bin/check-job-types-master.php --dist=dist --require-dist
 */
declare(strict_types=1);

$options = getopt('', ['dist::', 'require-dist', 'skip-render']);
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once SITE_ROOT . '/includes/sitemap.php';

$repoRoot = dirname(SITE_ROOT);
$dist = $options['dist'] ?? ($repoRoot . '/dist');
if ($dist !== '' && $dist[0] !== '/') {
    $dist = $repoRoot . '/' . ltrim((string)$dist, '/');
}
$requireDist = isset($options['require-dist']);
$skipRender = isset($options['skip-render']);

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

$expected = jobTypesMasterExpectedCount();
$masterJobs = jobTypesMasterJobs();
$masterSlugs = jobTypesMasterSlugs();
$unique = array_values(array_unique($masterSlugs));

if (count($masterSlugs) !== $expected) {
    $bad('master slug count ' . count($masterSlugs) . " !== {$expected}");
} else {
    $ok("master has exactly {$expected} slugs");
}
if (count($unique) !== $expected) {
    $bad('master slugs not unique (' . count($unique) . ')');
} else {
    $ok('master slugs all unique');
}

$keywords = getMajorKeywords();
$missingKw = [];
foreach ($unique as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
    }
}
$coverage = $expected > 0 ? (int)round((($expected - count($missingKw)) / $expected) * 100) : 0;
if ($missingKw) {
    $bad('catalogue missing ' . count($missingKw) . ' master slugs (sample ' . implode(',', array_slice($missingKw, 0, 8)) . ')');
} else {
    $ok('all master slugs present in getMajorKeywords()');
}
echo "COVERAGE {$coverage}% ({$expected}/{$expected} target; missing=" . count($missingKw) . ")\n";
if ($coverage !== 100) {
    $bad("coverage {$coverage}% !== 100%");
} else {
    $ok('coverage 100%');
}

$entries = icomplySitemapEntries();
$sitemapPaths = array_column($entries, 'path');
$sitemapMissing = [];
foreach ($unique as $slug) {
    if (!in_array('/pages/keywords/' . $slug, $sitemapPaths, true)) {
        $sitemapMissing[] = $slug;
    }
}
if ($sitemapMissing) {
    $bad('sitemap missing ' . count($sitemapMissing) . ' job locs');
} else {
    $ok('sitemap includes all ' . $expected . ' job pages');
}

$titles = [];
$h1s = [];
$broken = [];
$seoFail = [];
$rendered = 0;

if (!$skipRender) {
    $knownPages = jobTypesKnownInternalPaths();
    foreach ($unique as $i => $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $row = $keywords[$slug] ?? [];
        $title = (string)($row['seo_title'] ?? $row['name'] ?? $slug);
        $h1 = (string)($row['h1'] ?? $row['name'] ?? $slug);
        $titles[$title] = ($titles[$title] ?? 0) + 1;
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;

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
        if ($missing) {
            $seoFail[] = $slug . '(' . implode('|', $missing) . ')';
        }

        if (preg_match_all('/href="([^"]+)"/', $html, $mm)) {
            foreach ($mm[1] as $href) {
                $err = jobTypesHrefError((string)$href, $slug, $knownPages);
                if ($err !== null) {
                    $broken[] = $slug . ' → ' . $err;
                }
            }
        }

        if (($i + 1) % 400 === 0) {
            fwrite(STDERR, 'rendered ' . ($i + 1) . "/{$expected}\n");
        }
    }

    $dupTitles = array_filter($titles, static fn(int $n): bool => $n > 1);
    $dupH1 = array_filter($h1s, static fn(int $n): bool => $n > 1);
    if ($dupTitles) {
        $bad('duplicate titles: ' . count($dupTitles));
    } else {
        $ok('unique titles for all rendered jobs');
    }
    if ($dupH1) {
        $bad('duplicate H1s: ' . count($dupH1));
    } else {
        $ok('unique H1s for all rendered jobs');
    }
    if ($seoFail) {
        $bad('SEO tags missing on ' . count($seoFail) . ' pages (sample ' . implode('; ', array_slice($seoFail, 0, 5)) . ')');
    } else {
        $ok("required SEO tags present on {$rendered} generated pages");
    }
    if ($broken) {
        $bad('broken internal links: ' . count($broken) . ' (sample ' . implode('; ', array_slice($broken, 0, 6)) . ')');
    } else {
        $ok('no broken internal links in generated job pages');
    }
}

$distMissing = [];
$distPresent = is_dir($dist);
if ($distPresent) {
    foreach ($unique as $slug) {
        $php = $dist . '/pages/keywords/' . $slug . '.php';
        $idx = $dist . '/pages/keywords/' . $slug . '/index.html';
        if (!is_file($php) && !is_file($idx)) {
            $distMissing[] = $slug;
        }
    }
    if ($distMissing) {
        $bad('dist missing ' . count($distMissing) . ' output files (sample ' . implode(',', array_slice($distMissing, 0, 8)) . ')');
    } else {
        $ok("all {$expected} output files exist under dist/pages/keywords/");
    }
} elseif ($requireDist) {
    $bad("dist/ missing at {$dist} (--require-dist)");
} else {
    echo "SKIP dist file check (no dist/; use --require-dist after static-export)\n";
}

echo PHP_EOL . "jobs={$expected} coverage={$coverage}% rendered={$rendered}\n";
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);

/** @return array<string, true> */
function jobTypesKnownInternalPaths(): array
{
    $known = [
        '/' => true,
        '/contact' => true,
        '/contact.php' => true,
        '/privacy' => true,
        '/terms' => true,
        '/pages/about' => true,
        '/pages/faq' => true,
        '/pages/keywords' => true,
        '/pages/keywords/index' => true,
        '/pages/services' => true,
        '/pages/services/index' => true,
        '/pages/areas' => true,
        '/pages/areas/index' => true,
        '/pages/manufacturers' => true,
        '/pages/manufacturers/index' => true,
        '/pages/resources' => true,
        '/shop' => true,
        '/products' => true,
        '/sitemap.xml' => true,
    ];
    foreach (array_keys(getServices()) as $slug) {
        $known['/pages/services/' . $slug] = true;
    }
    foreach (array_keys(getMajorKeywords()) as $slug) {
        $known['/pages/keywords/' . keywordSlug((string)$slug)] = true;
    }
    foreach (getAreas() as $area) {
        $known['/pages/areas/' . areaSlug((string)$area)] = true;
    }
    if (function_exists('getManufacturerCatalog')) {
        foreach (array_keys(getManufacturerCatalog()) as $slug) {
            $known['/pages/manufacturers/' . areaSlug((string)$slug)] = true;
        }
    }
    if (function_exists('seoIaWave1ManufacturerSlugs')) {
        foreach (seoIaWave1ManufacturerSlugs() as $slug) {
            $known['/pages/manufacturers/' . areaSlug((string)$slug)] = true;
        }
    }
    return $known;
}

function jobTypesHrefError(string $href, string $fromSlug, array $known): ?string
{
    $href = html_entity_decode($href, ENT_QUOTES, 'UTF-8');
    if ($href === '' || $href[0] === '#' || str_starts_with($href, 'tel:') || str_starts_with($href, 'mailto:')) {
        return null;
    }
    if (preg_match('#^(https?:)?//#i', $href)) {
        $host = (string)(parse_url($href, PHP_URL_HOST) ?? '');
        $internalHosts = [
            'icomplypropertyservices.co.uk',
            'www.icomplypropertyservices.co.uk',
            'localhost',
        ];
        if (!in_array($host, $internalHosts, true)) {
            return null;
        }
        $path = (string)(parse_url($href, PHP_URL_PATH) ?? '/');
    } else {
        $path = $href;
    }
    $path = preg_replace('/[?#].*$/', '', $path) ?? $path;
    if (str_starts_with($path, '/assets/') || str_starts_with($path, '/favicon')) {
        return null;
    }
    $pretty = preg_replace('/\.php$/', '', $path) ?? $path;
    $pretty = rtrim($pretty, '/') ?: '/';
    if (isset($known[$pretty]) || isset($known[$path])) {
        return null;
    }
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $pretty, $m)) {
        if (function_exists('jobTypeAreaComboExported') && jobTypeAreaComboExported($m[1], $m[2])) {
            return null;
        }
        return $pretty . ' (keyword×area not exported)';
    }
    if (preg_match('#^/pages/(resources|packages)/#', $pretty)) {
        return null;
    }
    return $pretty;
}
