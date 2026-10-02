#!/usr/bin/env php
<?php
/**
 * Core money (22) + compliance packs (17) + live keywords (52) + live services (38).
 * Usage: php website/bin/check-core-compliance-live.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/render.php';
require_once SITE_ROOT . '/includes/sitemap.php';
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_WARNING);

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

$idx = coreComplianceLiveIndex();
$core = $idx['core_money'] ?? [];
$packs = $idx['compliance_packs'] ?? [];
$liveKw = $idx['live_keywords'] ?? [];
$liveSvc = $idx['live_services'] ?? [];
$canonical = $idx['canonical_jobs'] ?? [];

if (count($core) !== 22) {
    $bad('core money count ' . count($core) . ' !== 22');
} else {
    $ok('core money 22');
}
if (count($packs) !== 17) {
    $bad('compliance packs count ' . count($packs) . ' !== 17');
} else {
    $ok('compliance packs 17');
}
if (count($liveKw) !== 52) {
    $bad('live keywords count ' . count($liveKw) . ' !== 52');
} else {
    $ok('live keywords 52');
}
if (count($liveSvc) !== 38) {
    $bad('live services count ' . count($liveSvc) . ' !== 38');
} else {
    $ok('live services 38');
}
echo 'SLICE_ROWS ' . (count($core) + count($packs) + count($liveKw) + count($liveSvc)) . " (22+17+52+38)\n";

$keywords = getMajorKeywords();
$master = array_fill_keys(jobTypesMasterSlugs(), true);
$overlays = jobPackOverlays();
$jobSlugs = coreComplianceLiveJobSlugs();

$missingMaster = [];
$missingOverlay = [];
foreach ($jobSlugs as $slug) {
    if (!isset($master[$slug])) {
        $missingMaster[] = $slug;
    }
    if (!isset($overlays[$slug])) {
        $missingOverlay[] = $slug;
    }
}
if ($missingMaster) {
    $bad('jobs missing from master: ' . implode(',', $missingMaster));
} else {
    $ok(count($jobSlugs) . ' unique job slugs in master');
}
if ($missingOverlay) {
    $bad('jobs missing pack overlay: ' . implode(',', array_slice($missingOverlay, 0, 12)));
} else {
    $ok(count($jobSlugs) . ' unique job slugs have pack overlays');
}

$titles = [];
$h1s = [];
$seoFail = [];
$rendered = 0;
foreach ($jobSlugs as $slug) {
    if (!isset($keywords[$slug])) {
        $seoFail[] = $slug . '(not-in-catalogue)';
        continue;
    }
    ob_start();
    renderKeywordPage($slug);
    $html = (string)ob_get_clean();
    $rendered++;
    $row = $keywords[$slug];
    $title = (string)($row['seo_title'] ?? $row['name'] ?? $slug);
    $h1 = (string)($row['h1'] ?? $row['name'] ?? $slug);
    $titles[$title] = ($titles[$title] ?? 0) + 1;
    $h1s[$h1] = ($h1s[$h1] ?? 0) + 1;
    $needles = [
        '<title>',
        '<h1',
        'name="description"',
        'FAQPage',
        '#quote',
        'Request POA quote',
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
    if (strlen($html) < 2200) {
        $missing[] = 'short-html';
    }
    if ($missing) {
        $seoFail[] = $slug . '(' . implode('|', $missing) . ')';
    }
}

$dupT = array_filter($titles, static fn(int $n): bool => $n > 1);
$dupH = array_filter($h1s, static fn(int $n): bool => $n > 1);
if ($dupT) {
    $bad('duplicate titles in slice: ' . count($dupT));
} else {
    $ok('unique titles on ' . count($titles) . ' slice jobs');
}
if ($dupH) {
    $bad('duplicate H1s in slice: ' . count($dupH));
} else {
    $ok('unique H1s on ' . count($h1s) . ' slice jobs');
}
if ($seoFail) {
    $bad('SEO fail on ' . count($seoFail) . ' jobs (sample ' . implode('; ', array_slice($seoFail, 0, 6)) . ')');
} else {
    $ok("required SEO + POA + FAQPage on {$rendered} job pages");
}

$svcMissing = [];
$svcSeo = [];
$services = getServices();
foreach ($liveSvc as $svc) {
    $svc = areaSlug((string)$svc);
    if (!isset($services[$svc])) {
        $svcMissing[] = $svc;
        continue;
    }
    if (!isset($canonical[$svc]) || !isset($keywords[keywordSlug((string)$canonical[$svc])])) {
        $svcMissing[] = $svc . '(no-canonical-job)';
    }
    ob_start();
    renderServiceHubPage($svc);
    $html = (string)ob_get_clean();
    $need = ['<h1', 'FAQPage', 'Request POA quote', '#0B1F3A', '#ff6b00'];
    $miss = [];
    foreach ($need as $n) {
        if (!str_contains($html, $n)) {
            $miss[] = $n;
        }
    }
    if (preg_match('/£\s*\d/', $html)) {
        $miss[] = 'invented-£';
    }
    if ($miss) {
        $svcSeo[] = $svc . '(' . implode('|', $miss) . ')';
    }
}
if ($svcMissing) {
    $bad('live services incomplete: ' . implode(',', $svcMissing));
} else {
    $ok('38 live services exist with canonical job pages');
}
if ($svcSeo) {
    $bad('live service hub SEO fail: ' . implode('; ', array_slice($svcSeo, 0, 6)));
} else {
    $ok('38 live service hubs have FAQ JSON-LD, POA CTA, brand colours');
}

$paths = array_column(icomplySitemapEntries(), 'path');
$smMiss = [];
foreach ($jobSlugs as $slug) {
    if (!in_array('/pages/keywords/' . $slug, $paths, true)) {
        $smMiss[] = $slug;
    }
}
foreach ($liveSvc as $svc) {
    if (!in_array('/pages/services/' . areaSlug((string)$svc), $paths, true)) {
        $smMiss[] = 'svc:' . $svc;
    }
}
if ($smMiss) {
    $bad('sitemap missing ' . count($smMiss) . ' (sample ' . implode(',', array_slice($smMiss, 0, 8)) . ')');
} else {
    $ok('sitemap lists all slice job + service hubs');
}

ob_start();
require SITE_ROOT . '/pages/keywords.php';
$hubHtml = (string)ob_get_clean();
$hubNeed = ['id="money-compliance"', 'Core money jobs', 'Compliance packs', 'Request POA quote', 'id="quote"', 'Submit request', '#0B1F3A', '#ff6b00'];
$hubMiss = [];
foreach ($hubNeed as $n) {
    if (!str_contains($hubHtml, $n)) {
        $hubMiss[] = $n;
    }
}
$hubLinkMiss = [];
foreach (array_merge($core, $packs) as $slug) {
    $slug = keywordSlug((string)$slug);
    if (!str_contains($hubHtml, '/pages/keywords/' . $slug)) {
        $hubLinkMiss[] = $slug;
    }
}
if ($hubMiss || $hubLinkMiss) {
    $bad('keywords hub featured slice missing: ' . implode('|', $hubMiss) . ' links ' . implode(',', array_slice($hubLinkMiss, 0, 8)));
} else {
    $ok('live /pages/keywords shows ' . count($core) . ' money + ' . count($packs) . ' pack cards and POA quote CTA');
}

echo PHP_EOL . "jobs=" . count($jobSlugs) . " rendered={$rendered} services=" . count($liveSvc) . PHP_EOL;
echo ($fail === 0 ? "PASS ({$pass})" : "FAIL ({$fail}) PASS ({$pass})") . PHP_EOL;
exit($fail === 0 ? 0 : 1);
