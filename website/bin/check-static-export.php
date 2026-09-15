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
    '/pages/keywords/rewire' => ['pages/keywords/rewire.php', ['Rewire', '<!DOCTYPE']],
    '/pages/keywords/domestic-rewire' => ['pages/keywords/domestic-rewire.php', ['Domestic Rewire', '<!DOCTYPE']],
    '/pages/keywords/emergency-electrician' => ['pages/keywords/emergency-electrician.php', ['Emergency Electrician', '<!DOCTYPE']],
    '/pages/keywords/boiler' => ['pages/keywords/boiler.php', ['Boiler', '<!DOCTYPE']],
    '/pages/keywords/rewire/stockport' => ['pages/keywords/rewire/stockport.php', ['Rewire', 'Stockport', '<!DOCTYPE']],
    '/pages/keywords/domestic-rewire/manchester' => ['pages/keywords/domestic-rewire/manchester.php', ['Domestic Rewire', 'Manchester', '<!DOCTYPE']],
    '/pages/keywords/emergency-electrician/bolton' => ['pages/keywords/emergency-electrician/bolton.php', ['Emergency Electrician', 'Bolton', '<!DOCTYPE']],
    '/pages/keywords/boiler/stockport' => ['pages/keywords/boiler/stockport.php', ['Boiler', 'Stockport', '<!DOCTYPE']],
    '/pages/keywords/price-of-rewire' => ['pages/keywords/price-of-rewire.php', ['POA', '<!DOCTYPE']],
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
if ($kwTownFiles >= 12) {
    $pass++;
    echo "[PASS] keyword×town exported={$kwTownFiles}\n";
} else {
    $fail++;
    echo "[FAIL] keyword×town exported={$kwTownFiles} (need town combos)\n";
}

require_once $websiteRoot . '/config.php';
$elecGasSlugs = function_exists('getElectricalGasMatrixKeywordSlugs') ? getElectricalGasMatrixKeywordSlugs() : [];
$areaCount = function_exists('getAreas') ? count(getAreas()) : 0;
$matrixExpect = count($elecGasSlugs) * $areaCount;
$matrixHave = 0;
foreach ($elecGasSlugs as $slug) {
    $dir = $kwDir . '/' . $slug;
    if (!is_dir($dir)) {
        continue;
    }
    foreach (glob($dir . '/*.php') ?: [] as $file) {
        $matrixHave++;
    }
}
if ($areaCount > 0 && $matrixHave >= (int)floor($matrixExpect * 0.98)) {
    $pass++;
    echo "[PASS] electrical+gas keyword×area exported={$matrixHave} (expect ~{$matrixExpect})\n";
} else {
    $fail++;
    echo "[FAIL] electrical+gas keyword×area exported={$matrixHave} (expect ~{$matrixExpect})\n";
}
foreach (['rewire', 'domestic-rewire', 'emergency-electrician', 'boiler'] as $needSlug) {
    $sample = $kwDir . '/' . $needSlug . '/stockport.php';
    if (is_file($sample)) {
        $pass++;
        echo "[PASS] sample {$needSlug}/stockport.php\n";
    } else {
        $fail++;
        echo "[FAIL] missing sample {$needSlug}/stockport.php\n";
    }
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
$sitemapDist = is_file($dist . '/sitemap.xml') ? (string)file_get_contents($dist . '/sitemap.xml') : '';
if (preg_match('#/pages/gas-systems/[a-z0-9\-]+</loc>#', $sitemapDist) || preg_match('#/pages/electrical/[a-z0-9\-]+</loc>#', $sitemapDist)) {
    $fail++;
    echo "[FAIL] dist/sitemap.xml still lists /pages/{service}/{town} 404s\n";
} else {
    $pass++;
    echo "[PASS] dist/sitemap.xml has no service×area 404 locs\n";
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
