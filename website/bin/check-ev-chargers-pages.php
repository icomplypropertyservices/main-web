#!/usr/bin/env php
<?php
/**
 * EV chargers job lane: service hub + every keyword stub.
 *
 * Usage:
 *   php website/bin/check-ev-chargers-pages.php
 *   php website/bin/check-ev-chargers-pages.php --skip-render
 */
declare(strict_types=1);

$options = getopt('', ['skip-render']);
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

$expected = evChargersJobsExpectedCount();
$slugs = evChargersJobsSlugs();
$unique = array_values(array_unique($slugs));
$count = count($slugs);

echo "ev_chargers_page_count={$count}\n";

if ($count !== $expected || $count < 1) {
    $bad("ev_chargers_page_count {$count} !== {$expected}");
} else {
    $ok("ev_chargers_page_count == {$expected}");
}
if (count($unique) !== $expected) {
    $bad('EV charger slugs not unique');
} else {
    $ok('EV charger slugs all unique');
}

$services = getServices();
if (($services['ev-chargers'] ?? '') === '') {
    $bad('services.json missing ev-chargers');
} else {
    $ok('service ev-chargers is registered');
}
$cats = getServiceCategories();
$inCat = in_array('ev-chargers', $cats['electrical-gas']['services'] ?? [], true);
if (!$inCat) {
    $bad('ev-chargers not in electrical-gas category');
} else {
    $ok('ev-chargers listed under Electrical & Gas');
}
if (!is_file(SITE_ROOT . '/pages/services/ev-chargers.php')) {
    $bad('missing pages/services/ev-chargers.php');
} else {
    $ok('pages/services/ev-chargers.php exists');
}

$missingFiles = [];
foreach ($unique as $slug) {
    $path = evChargersJobsStubPath($slug);
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
    $bad('missing keyword files: ' . implode(',', array_slice($missingFiles, 0, 8)));
} else {
    $ok("all {$expected} keyword stubs exist");
}

$keywords = getMajorKeywords();
$missingKw = [];
$wrongService = [];
foreach ($unique as $slug) {
    if (!isset($keywords[$slug])) {
        $missingKw[] = $slug;
        continue;
    }
    if (($keywords[$slug]['service'] ?? '') !== 'ev-chargers') {
        $wrongService[] = $slug;
    }
}
if ($missingKw) {
    $bad('catalogue missing ' . count($missingKw) . ' EV slugs');
} else {
    $ok('all EV slugs present in getMajorKeywords()');
}
if ($wrongService) {
    $bad('wrong service on ' . implode(',', array_slice($wrongService, 0, 6)));
} else {
    $ok('all lane slugs tagged service=ev-chargers');
}

$owned = getKeywordsForService('ev-chargers');
if (count($owned) < $expected) {
    $bad('getKeywordsForService(ev-chargers) returned ' . count($owned));
} else {
    $ok('service keyword index includes the lane');
}

$skipRender = isset($options['skip-render']);
$titles = [];
$h1s = [];
$metas = [];
$seoFail = [];
$rendered = 0;

if (!$skipRender) {
    ob_start();
    renderServiceHubPage('ev-chargers');
    $hub = (string)ob_get_clean();
    $hubNeedles = [
        '<h1',
        'EV Chargers',
        '/pages/services/ev-chargers',
        '/pages/keywords/ev-charger-installation',
        'FAQPage',
        '#0B1F3A',
        '#ff6b00',
        'POA',
    ];
    $hubMissing = [];
    foreach ($hubNeedles as $n) {
        if (!str_contains($hub, $n)) {
            $hubMissing[] = $n;
        }
    }
    if (preg_match('/£\s*\d/', $hub)) {
        $hubMissing[] = 'invented-£';
    }
    if ($hubMissing) {
        $bad('service hub missing ' . implode('|', $hubMissing));
    } else {
        $ok('service hub renders with lane keywords, FAQ schema and POA');
    }

    foreach ($unique as $slug) {
        ob_start();
        renderKeywordPage($slug);
        $html = (string)ob_get_clean();
        $rendered++;
        $row = $keywords[$slug] ?? [];
        $title = (string)($row['seo_title'] ?? '');
        $h1 = (string)($row['h1'] ?? '');
        $meta = (string)($row['meta_desc'] ?? '');
        $titles[$title] = ($titles[$title] ?? 0) + 1;
        $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
        $metas[$meta] = ($metas[$meta] ?? 0) + 1;

        $needles = [
            '<title>' . $title,
            '<h1',
            $h1,
            'name="description"',
            '/pages/keywords/' . $slug,
            '/pages/services/ev-chargers',
            'FAQPage',
            'Electrical &amp; Gas',
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
        if ($missing) {
            $seoFail[] = $slug . '(' . implode('|', $missing) . ')';
        }
    }

    $dup = static function (array $map): int {
        return count(array_filter($map, static fn(int $n): bool => $n > 1));
    };
    if ($dup($titles) > 0) {
        $bad('duplicate titles: ' . $dup($titles));
    } else {
        $ok('unique seo title for every EV keyword page');
    }
    if ($dup($h1s) > 0) {
        $bad('duplicate H1s: ' . $dup($h1s));
    } else {
        $ok('unique H1 for every EV keyword page');
    }
    if ($dup($metas) > 0) {
        $bad('duplicate meta descriptions: ' . $dup($metas));
    } else {
        $ok('unique meta description for every EV keyword page');
    }
    if ($seoFail) {
        $bad('SEO/POA missing on ' . count($seoFail) . ' (sample ' . implode('; ', array_slice($seoFail, 0, 4)) . ')');
    } else {
        $ok("required SEO, breadcrumbs, FAQ JSON-LD and POA on {$rendered} keyword pages");
    }
}

echo PHP_EOL . "ev_chargers_page_count={$count} expected={$expected} rendered={$rendered}" . PHP_EOL;
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
