#!/usr/bin/env php
<?php
/**
 * Verify dist/ from static-export.php: pretty URLs exist as HTML, not PHP source.
 *
 * Usage (repo root): php website/bin/check-static-export.php
 *        (after export): php website/bin/check-static-export.php --dist=dist
 */
declare(strict_types=1);

$options = getopt('', ['dist::']);
$websiteRoot = dirname(__DIR__);
$repoRoot = dirname($websiteRoot);
require_once $websiteRoot . '/config.php';
$dist = $options['dist'] ?? ($repoRoot . '/dist');
if ($dist !== '' && $dist[0] !== '/') {
    $dist = $repoRoot . '/' . ltrim($dist, '/');
}

$fail = 0;
$pass = 0;

$needHtml = [
    '/' => ['index.html', ['Icomply', '<!DOCTYPE']],
    '/privacy' => ['privacy.php', ['Privacy', '<!DOCTYPE']],
    '/terms' => ['terms.php', ['Terms', '<!DOCTYPE']],
    '/contact' => ['contact.php', ['Contact', '<!DOCTYPE']],
    '/pages/about' => ['pages/about.php', ['About', '<!DOCTYPE']],
    '/pages/areas' => ['pages/areas.php', ['Areas', '<!DOCTYPE']],
    '/pages/manufacturers' => ['pages/manufacturers.php', ['Manufacturer', '<!DOCTYPE']],
    '/pages/resources' => ['pages/resources.php', ['Resource', '<!DOCTYPE']],
    '/pages/resources/gas-safety-certificate-landlords' => ['pages/resources/gas-safety-certificate-landlords.php', ['gas safety', '<!DOCTYPE']],
    '/pages/landlord-certificates' => ['pages/landlord-certificates.php', ['Landlord certificates', '<!DOCTYPE']],
    '/pages/fire-risk-assessment' => ['pages/fire-risk-assessment.php', ['Fire risk', '<!DOCTYPE']],
    '/pages/keywords' => ['pages/keywords.php', ['Keyword', '<!DOCTYPE']],
    '/pages/keywords/eicr' => ['pages/keywords/eicr.php', ['EICR', '<!DOCTYPE']],
    '/pages/keywords/eicr-report' => ['pages/keywords/eicr-report.php', ['EICR', '<!DOCTYPE']],
    '/pages/keywords/eicr-certificate' => ['pages/keywords/eicr-certificate.php', ['EICR', '<!DOCTYPE']],
    '/pages/keywords/fire-risk-assessment' => ['pages/keywords/fire-risk-assessment.php', ['Fire', '<!DOCTYPE']],
    '/pages/keywords/cctv-installation' => ['pages/keywords/cctv-installation.php', ['CCTV', '<!DOCTYPE']],
    '/pages/keywords/eicr/stockport' => ['pages/keywords/eicr/stockport.php', ['EICR', 'Stockport', '<!DOCTYPE']],
    '/pages/services/legionella-risk-assessment' => ['pages/services/legionella-risk-assessment.php', ['Legionella', '<!DOCTYPE']],
    '/pages/services/asbestos-survey' => ['pages/services/asbestos-survey.php', ['Asbestos', '<!DOCTYPE']],
    '/pages/resources/legionella-risk-assessment' => ['pages/resources/legionella-risk-assessment.php', ['Legionella', '<!DOCTYPE']],
    '/pages/resources/asbestos-survey' => ['pages/resources/asbestos-survey.php', ['Asbestos', '<!DOCTYPE']],
    '/pages/keywords/legionella-risk-assessment' => ['pages/keywords/legionella-risk-assessment.php', ['Legionella', '<!DOCTYPE']],
    '/pages/keywords/asbestos-survey' => ['pages/keywords/asbestos-survey.php', ['Asbestos', '<!DOCTYPE']],
    '/pages/keywords/legionella-risk-assessment/stockport' => ['pages/keywords/legionella-risk-assessment/stockport.php', ['Legionella', 'Stockport', '<!DOCTYPE']],
    '/pages/keywords/asbestos-survey/manchester' => ['pages/keywords/asbestos-survey/manchester.php', ['Asbestos', 'Manchester', '<!DOCTYPE']],
    '/pages/legionella-risk-assessment/stockport' => ['pages/legionella-risk-assessment/stockport.php', ['Legionella', 'Stockport', '<!DOCTYPE']],
    '/pages/asbestos-survey/manchester' => ['pages/asbestos-survey/manchester.php', ['Asbestos', 'Manchester', '<!DOCTYPE']],
];

echo "Icomply static-export check  dist={$dist}\n";
echo str_repeat('=', 56) . "\n";

if (!is_dir($dist)) {
    fwrite(STDERR, "dist/ missing — run php website/bin/static-export.php first\n");
    exit(1);
}

foreach ($needHtml as $url => $spec) {
    [$rel, $needles] = $spec;
    $file = $dist . '/' . $rel;
    $dirIndex = $url === '/' ? $dist . '/index.html' : $dist . $url . '/index.html';
    $body = is_file($file) ? (string)file_get_contents($file) : '';
    if ($body === '' && is_file($dirIndex)) {
        $body = (string)file_get_contents($dirIndex);
        $file = $dirIndex;
    }
    $ok = $body !== '' && !str_contains($body, '<?php');
    $missing = [];
    if ($ok) {
        foreach ($needles as $n) {
            if (stripos($body, $n) === false) {
                $missing[] = $n;
                $ok = false;
            }
        }
    } else {
        $missing[] = is_file($file) ? 'php-source-or-empty' : 'missing-file';
    }
    if ($ok) {
        $pass++;
        echo "[PASS] {$url} → {$rel} (" . strlen($body) . " bytes)\n";
    } else {
        $fail++;
        echo "[FAIL] {$url} → {$rel} " . implode(',', $missing) . "\n";
    }
}

$mustExist = [
    'assets',
    'manifest.json',
    'robots.txt',
    'sitemap.xml',
    'favicon.ico',
    '_redirects',
    '_headers',
    '404.html',
];
foreach ($mustExist as $rel) {
    $path = $dist . '/' . $rel;
    $ok = is_file($path) || is_dir($path);
    if ($ok) {
        $pass++;
        echo "[PASS] {$rel}\n";
    } else {
        $fail++;
        echo "[FAIL] missing {$rel}\n";
    }
}

$mustNotExist = [
    'config.php',
    'config.local.php',
    'includes/header.php',
    'bin/static-export.php',
    'data/leads.jsonl',
    'admin/index.php',
];
foreach ($mustNotExist as $rel) {
    $path = $dist . '/' . $rel;
    if (is_file($path) || is_dir($path)) {
        $fail++;
        echo "[FAIL] must-not-publish {$rel}\n";
    } else {
        $pass++;
        echo "[PASS] not-published {$rel}\n";
    }
}

$redirects = is_file($dist . '/_redirects') ? (string)file_get_contents($dist . '/_redirects') : '';
$headerFile = is_file($dist . '/_headers') ? (string)file_get_contents($dist . '/_headers') : '';
$kwDir = $dist . '/pages/keywords';
$kwHubFiles = 0;
$kwTownFiles = 0;
if (is_dir($kwDir)) {
    foreach (glob($kwDir . '/*.php') ?: [] as $file) {
        if (strtolower(basename($file)) === 'index.php') {
            continue;
        }
        $kwHubFiles++;
    }
    $townIter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($kwDir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($townIter as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }
        $rel = substr($file->getPathname(), strlen($kwDir) + 1);
        if (substr_count(str_replace('\\', '/', $rel), '/') >= 1) {
            $kwTownFiles++;
        }
    }
}
if ($kwHubFiles >= 1200) {
    $pass++;
    echo "[PASS] keyword hubs exported={$kwHubFiles}\n";
} else {
    $fail++;
    echo "[FAIL] keyword hubs exported={$kwHubFiles} (need >= 1200 sitemap slugs)\n";
}
$areaCount = function_exists('getAreas') ? count(getAreas()) : 168;
$expectTowns = $kwHubFiles * $areaCount;
if ($kwTownFiles >= max(12, (int)floor($expectTowns * 0.95))) {
    $pass++;
    echo "[PASS] keyword×town exported={$kwTownFiles} (hubs={$kwHubFiles} areas={$areaCount})\n";
} else {
    $fail++;
    echo "[FAIL] keyword×town exported={$kwTownFiles} (need ~{$expectTowns} = hubs×all areas)\n";
}

$redirectNeedles = ['/*', '/:splat.php', '/privacy', '/pages/about', '/assets/', '/pages/keywords', '/pages/keywords/:slug'];
foreach ($redirectNeedles as $n) {
    if (!str_contains($redirects, $n)) {
        $fail++;
        echo "[FAIL] _redirects missing {$n}\n";
    } else {
        $pass++;
        echo "[PASS] _redirects has {$n}\n";
    }
}
if (!str_contains($headerFile, 'text/html')) {
    $fail++;
    echo "[FAIL] _headers missing text/html for .php\n";
} else {
    $pass++;
    echo "[PASS] _headers Content-Type text/html\n";
}

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
