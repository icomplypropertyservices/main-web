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
    '/' => ['index.html', ['Icomply', '<!DOCTYPE', 'mega-header', 'foot-drop', 'Keyword × town']],
    '/privacy' => ['privacy.php', ['Privacy', '<!DOCTYPE']],
    '/terms' => ['terms.php', ['Terms', '<!DOCTYPE']],
    '/contact' => ['contact.php', ['Contact', 'page-hero', '#0B1F3A', '<!DOCTYPE']],
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
    '/shop' => ['shop/index.html', ['Fire', 'Electrical', 'Security', 'Gas', 'shop.icomplypropertyservices.co.uk', '<!DOCTYPE']],
    '/shop/fire' => ['shop/fire/index.html', ['Fire', '<!DOCTYPE']],
    '/shop/electrical' => ['shop/electrical/index.html', ['Electrical', '<!DOCTYPE']],
    '/shop/security' => ['shop/security/index.html', ['Security', '<!DOCTYPE']],
    '/shop/gas' => ['shop/gas/index.html', ['Gas', '<!DOCTYPE']],
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
    'assets/css/site.css',
    'assets/js/site-nav.js',
    'assets/js/lead-popup.js',
    'assets/images/favicon.svg',
    'assets/images/favicon-32.png',
    'assets/images/android-chrome-192.png',
    'assets/images/brand/icomply-mark.svg',
    'assets/images/brand/icomply-logo.svg',
    'assets/images/brand/icomply-logo-on-dark.svg',
    'assets/images/brand/icomply-mark-512.png',
    'assets/images/apple-touch-icon.png',
    'assets/images/android-chrome-512.png',
    'manifest.json',
    'manifest.webmanifest',
    'site.webmanifest',
    'robots.txt',
    'sitemap.xml',
    'favicon.ico',
    '_redirects',
    '_headers',
    '404.html',
    'shop/index.html',
    'shop/fire/index.html',
    'shop/electrical/index.html',
    'shop/security/index.html',
    'shop/gas/index.html',
    'shop/assets/shop.css',
    'shop/assets/logo.svg',
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

$cssFile = $dist . '/assets/css/site.css';
$homeHtml = is_file($dist . '/index.html') ? (string)file_get_contents($dist . '/index.html') : '';
if (is_file($cssFile) && str_contains((string)file_get_contents($cssFile), '--brand') && str_contains((string)file_get_contents($cssFile), '#0B1F3A')) {
    $pass++;
    echo "[PASS] assets/css/site.css is brand CSS\n";
} else {
    $fail++;
    echo "[FAIL] assets/css/site.css missing brand tokens\n";
}
if (str_contains($homeHtml, 'href="/assets/css/site.css"') && !str_contains($homeHtml, 'https://icomplypropertyservices.co.uk/assets/css/site.css')) {
    $pass++;
    echo "[PASS] homepage CSS href is root-relative /assets/css/site.css\n";
} else {
    $fail++;
    echo "[FAIL] homepage CSS href must be /assets/css/site.css (not a production domain)\n";
}
if (stripos($homeHtml, '#0a2540') !== false) {
    $fail++;
    echo "[FAIL] homepage still contains #0a2540 — must be #0B1F3A\n";
} else {
    $pass++;
    echo "[PASS] homepage has no leftover #0a2540\n";
}
if (str_contains($homeHtml, '#0B1F3A') && str_contains($homeHtml, 'href="/manifest.webmanifest"')) {
    $pass++;
    echo "[PASS] homepage has #0B1F3A and /manifest.webmanifest\n";
} else {
    $fail++;
    echo "[FAIL] homepage missing #0B1F3A or /manifest.webmanifest\n";
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

$redirectNeedles = ['/*', '/:splat.php', '/privacy', '/pages/about', '/assets/', '/pages/keywords', '/pages/keywords/:slug', '/shop/index.html', '/shop/fire/index.html', '/products.php'];
foreach ($redirectNeedles as $n) {
    if (!str_contains($redirects, $n)) {
        $fail++;
        echo "[FAIL] _redirects missing {$n}\n";
    } else {
        $pass++;
        echo "[PASS] _redirects has {$n}\n";
    }
}
if (preg_match('#^/shop\\s+/pages/packages#m', $redirects)) {
    $fail++;
    echo "[FAIL] _redirects still 301s /shop to /pages/packages\n";
} else {
    $pass++;
    echo "[PASS] _redirects does not send /shop to packages\n";
}
if (!str_contains($headerFile, 'text/html')) {
    $fail++;
    echo "[FAIL] _headers missing text/html for .php\n";
} else {
    $pass++;
    echo "[PASS] _headers Content-Type text/html\n";
}
if (preg_match('#^/shop/\\*\\s*\n\\s*Content-Type:\\s*text/html#m', $headerFile)) {
    $fail++;
    echo "[FAIL] _headers blanket /shop/* text/html would break shop.css MIME\n";
} else {
    $pass++;
    echo "[PASS] _headers does not force /shop/* as text/html\n";
}
if (!str_contains($headerFile, '/shop/assets/*.css')) {
    $fail++;
    echo "[FAIL] _headers missing /shop/assets/*.css text/css\n";
} else {
    $pass++;
    echo "[PASS] _headers has /shop/assets/*.css\n";
}

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
