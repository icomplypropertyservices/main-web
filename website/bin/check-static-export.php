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
    '/' => ['index.html', ['iComply', '<!DOCTYPE', 'mega-header', 'foot-drop', 'Keyword × town']],
    '/privacy' => ['privacy.php', ['Privacy', '<!DOCTYPE']],
    '/terms' => ['terms.php', ['Terms', '<!DOCTYPE']],
    '/contact' => ['contact.php', ['Contact', 'page-hero', '#0B1F3A', '<!DOCTYPE']],
    '/become-a-subcontractor' => ['become-a-subcontractor.php', ['Are you a highly skilled tradesperson', 'subcontractor-onboarding', 'WebPage', 'iComply Property Services', '<!DOCTYPE']],
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
    '/shop' => ['shop/index.html', ['Fire', 'Electrical', 'Security', 'Gas', 'shop.icomplypropertyservices.co.uk', '<!DOCTYPE']],
    '/shop/fire' => ['shop/fire/index.html', ['Fire', '<!DOCTYPE']],
    '/shop/electrical' => ['shop/electrical/index.html', ['Electrical', '<!DOCTYPE']],
    '/shop/security' => ['shop/security/index.html', ['Security', '<!DOCTYPE']],
    '/shop/gas' => ['shop/gas/index.html', ['Gas', '<!DOCTYPE']],
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

echo "iComply static-export check  dist={$dist}\n";
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

$subPage = is_file($dist . '/become-a-subcontractor.php') ? (string)file_get_contents($dist . '/become-a-subcontractor.php') : '';
$subReg = is_file($dist . '/subcontractor-onboarding-form.html') ? (string)file_get_contents($dist . '/subcontractor-onboarding-form.html') : '';
$subFields = ['name="subcontractor-onboarding"', 'name="form-name"', 'name="business_type"', 'value="company"', 'value="sole_trader"', 'name="company_name"', 'name="trades"', 'name="qualifications"', 'name="insured"', 'name="insurance_expiry"', 'name="coverage_postcodes"', 'name="travel_radius"', 'name="phone"', 'name="email"', 'name="availability"', 'name="documents"', 'name="gdpr_consent"', 'netlify-honeypot="bot-field"', 'enctype="multipart/form-data"', 'action="/thank-you"', 'data-netlify="true"'];
$subOk = $subPage !== '' && $subReg !== '' && !str_contains($subPage, 'JobPosting') && !str_contains($subReg, 'JobPosting');
foreach ($subFields as $field) {
    if (!str_contains($subPage, $field) || !str_contains($subReg, $field)) {
        $subOk = false;
        echo "[FAIL] subcontractor form missing {$field}\n";
    }
}
if ($subOk) {
    $pass++;
    echo "[PASS] subcontractor form fields match the Netlify HTML registration file\n";
} else {
    $fail++;
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
    'subcontractor-onboarding-form.html',
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

$elecGasSlugs = function_exists('getElectricalGasMatrixKeywordSlugs') ? getElectricalGasMatrixKeywordSlugs() : [];
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
foreach ([
    '/pages/keywords/eicr/:town',
    '/pages/electrical/:town',
    '/pages/asbestos-survey/:town',
    '/pages/legionella-risk-assessment/:town',
] as $from) {
    if (preg_match('#^' . preg_quote($from, '#') . '\\s+\\S+\\s+301!#m', $redirects)) {
        $fail++;
        echo "[FAIL] matrix page still 301s {$from}\n";
    } else {
        $pass++;
        echo "[PASS] {$from} is not a 301\n";
    }
}
$svcAreaFiles = 0;
$svcCount = function_exists('getServices') ? count(getServices()) : 0;
foreach (array_keys(function_exists('getServices') ? getServices() : []) as $svcSlug) {
    $dir = $dist . '/pages/' . $svcSlug;
    if (!is_dir($dir)) {
        continue;
    }
    foreach (glob($dir . '/*.php') ?: [] as $file) {
        $svcAreaFiles++;
    }
}
$svcAreaExpect = $svcCount * $areaCount;
if ($svcAreaFiles >= (int)floor($svcAreaExpect * 0.98)) {
    $pass++;
    echo "[PASS] service×area exported={$svcAreaFiles} (expect ~{$svcAreaExpect})\n";
} else {
    $fail++;
    echo "[FAIL] service×area exported={$svcAreaFiles} (expect ~{$svcAreaExpect})\n";
}
if (preg_match('#^/\\*\\s+/\\s+301#m', $redirects) || preg_match('#^/\\s+/\\s+301#m', $redirects)) {
    $fail++;
    echo "[FAIL] _redirects soft-404s to the homepage\n";
} else {
    $pass++;
    echo "[PASS] _redirects has no homepage soft-404\n";
}
$sitemapDist = is_file($dist . '/sitemap.xml') ? (string)file_get_contents($dist . '/sitemap.xml') : '';
$productsCanonical = 'https://icomplypropertyservices.co.uk/products';
foreach ([
    '/products' => 'products.php',
    '/pages/products' => 'pages/products.php',
] as $productsUrl => $productsRel) {
    $productsBody = is_file($dist . '/' . $productsRel) ? (string)file_get_contents($dist . '/' . $productsRel) : '';
    $productsCanon = $productsBody !== ''
        && !str_contains($productsBody, '<?php')
        && str_contains($productsBody, 'rel="canonical" href="' . $productsCanonical . '"');
    if ($productsCanon) {
        $pass++;
        echo "[PASS] {$productsUrl} is HTML and canonical {$productsCanonical}\n";
    } else {
        $fail++;
        echo "[FAIL] {$productsUrl} must be a direct HTML page with canonical {$productsCanonical}\n";
    }
}
if (str_contains($sitemapDist, '/pages/products</loc>') || substr_count($sitemapDist, '/products</loc>') !== 1) {
    $fail++;
    echo "[FAIL] sitemap must list /products once and omit /pages/products\n";
} else {
    $pass++;
    echo "[PASS] sitemap lists /products only\n";
}
$indexMode = function_exists('icomplyIndexMode') ? icomplyIndexMode() : 'tiered';
if ($indexMode === 'tiered') {
    $tierOk = str_contains($sitemapDist, '/pages/electrical/stockport</loc>')
        && str_contains($sitemapDist, '/pages/electrical/trafford</loc>')
        && !str_contains($sitemapDist, '/pages/keywords/eicr/stockport</loc>')
        && !str_contains($sitemapDist, '/pages/electrical/preston</loc>');
    $kwSample = is_file($dist . '/pages/keywords/eicr/stockport.php')
        ? (string)file_get_contents($dist . '/pages/keywords/eicr/stockport.php')
        : '';
    $tierSample = is_file($dist . '/pages/electrical/stockport.php')
        ? (string)file_get_contents($dist . '/pages/electrical/stockport.php')
        : '';
    $offSample = is_file($dist . '/pages/electrical/preston.php')
        ? (string)file_get_contents($dist . '/pages/electrical/preston.php')
        : '';
    $robotsOk = str_contains($kwSample, 'noindex, follow')
        && str_contains($offSample, 'noindex, follow')
        && str_contains($tierSample, 'index, follow')
        && !str_contains($tierSample, 'noindex');
    if ($tierOk && $robotsOk) {
        $pass++;
        echo "[PASS] tiered sitemap lists Tier-1 service×area only; noindex pages stay out\n";
    } else {
        $fail++;
        echo "[FAIL] tiered sitemap/robots mismatch (sitemap samples or noindex meta)\n";
    }
} elseif (str_contains($sitemapDist, '/pages/electrical/stockport</loc>') && str_contains($sitemapDist, '/pages/keywords/eicr/stockport</loc>')) {
    $pass++;
    echo "[PASS] dist/sitemap.xml lists service×area and keyword×area samples\n";
} else {
    $fail++;
    echo "[FAIL] dist/sitemap.xml is missing service×area or keyword×area samples\n";
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

/**
 * Every HTML page canonical must be that page. /pages/products may point at /products.
 */
$canonBad = 0;
$canonSeen = 0;
$canonIter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dist, FilesystemIterator::SKIP_DOTS)
);
foreach ($canonIter as $canonFile) {
    if (!$canonFile->isFile()) {
        continue;
    }
    $ext = strtolower($canonFile->getExtension());
    if (!in_array($ext, ['php', 'html', 'htm'], true)) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($canonFile->getPathname(), strlen($dist) + 1));
    if (str_starts_with($rel, 'assets/')) {
        continue;
    }
    $page = '/' . $rel;
    if ($rel === 'index.html' || $rel === 'index.php') {
        $page = '/';
    } elseif (str_ends_with($rel, '/index.html') || str_ends_with($rel, '/index.php')) {
        $page = '/' . substr($rel, 0, (int)strrpos($rel, '/index.'));
        if (str_starts_with($page, '/shop') && !str_ends_with($page, '/')) {
            $page .= '/';
        }
    } elseif ($rel === '404.html') {
        $page = '/404';
    } elseif (str_ends_with($rel, '.php')) {
        $page = '/' . substr($rel, 0, -4);
    }
    $head = (string)file_get_contents($canonFile->getPathname(), false, null, 0, 12000);
    if (!preg_match('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $head, $tag)) {
        $canonBad++;
        if ($canonBad <= 8) {
            echo "[FAIL] missing canonical on {$page}\n";
        }
        continue;
    }
    if (!preg_match('/href=["\']([^"\']+)["\']/', $tag[0], $href)) {
        $canonBad++;
        if ($canonBad <= 8) {
            echo "[FAIL] canonical without href on {$page}\n";
        }
        continue;
    }
    $canonPath = (string)(parse_url($href[1], PHP_URL_PATH) ?: '/');
    if (str_ends_with($canonPath, '.php')) {
        $canonPath = substr($canonPath, 0, -4) ?: '/';
    }
    if (str_ends_with($canonPath, '/index')) {
        $canonPath = substr($canonPath, 0, -6) ?: '/';
    }
    if ($canonPath !== '/' && str_ends_with($canonPath, '/') && !str_starts_with($canonPath, '/shop')) {
        $canonPath = rtrim($canonPath, '/');
    }
    $canonSeen++;
    $allowed = $page === $canonPath || ($page === '/pages/products' && $canonPath === '/products');
    if (!$allowed) {
        $canonBad++;
        if ($canonBad <= 8) {
            echo "[FAIL] canonical {$canonPath} is not {$page}\n";
        }
    }
}
if ($canonBad === 0 && $canonSeen > 1000) {
    $pass++;
    echo "[PASS] canonicals are self-referencing ({$canonSeen} pages; /pages/products → /products only)\n";
} else {
    $fail++;
    echo "[FAIL] canonical mismatches={$canonBad} checked={$canonSeen}\n";
}

echo str_repeat('=', 56) . "\n";
echo "PASS={$pass} FAIL={$fail}\n";
exit($fail > 0 ? 1 : 0);
