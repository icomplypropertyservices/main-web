<?php
/**
 * Anti-slop and coverage checks for AOV and Barriers town pages.
 * Usage: php website/bin/check-aov-barriers-towns.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once SITE_ROOT . '/includes/town-service-pages.php';

$fail = static function (string $msg): void {
    fwrite(STDERR, "FAIL {$msg}\n");
    exit(1);
};

$towns = icomplyUkTowns();
if (count($towns) < 900) {
    $fail('expected 900+ mainland towns, got ' . count($towns));
}
foreach ($towns as $town) {
    if ((int)$town['population'] <= 10000) {
        $fail('population <= 10000: ' . $town['name']);
    }
    if (!in_array($town['nation'], ['England', 'Scotland', 'Wales'], true)) {
        $fail('not mainland nation: ' . $town['name']);
    }
    $county = (string)($town['county'] ?? '');
    foreach (['Isle of Wight', 'Anglesey', 'Shetland', 'Orkney', 'Eilean Siar'] as $island) {
        if (stripos($county, $island) !== false) {
            $fail('island county kept: ' . $town['name']);
        }
    }
}

$aovBrands = icomplyTownBrandRows('aov');
$barBrands = icomplyTownBrandRows('barriers');
if (count($aovBrands) < 15) {
    $fail('AOV manufacturer list too short');
}
if (count($barBrands) < 20) {
    $fail('barrier manufacturer list too short');
}
$partner = 0;
foreach ($barBrands as $row) {
    if (!empty($row['partner']) && $row['name'] === 'CAME') {
        $partner++;
    }
    if (stripos($row['name'], 'tunstall') !== false) {
        $fail('Tunstall in barrier brands');
    }
}
if ($partner !== 1) {
    $fail('CAME partner missing');
}

$sigs = ['aov' => [], 'barriers' => []];
$minWords = 100000;
$sampleTowns = [];
foreach ($towns as $i => $town) {
    if (in_array($town['slug'], ['stockport', 'london', 'glasgow', 'cardiff', 'newport-wales', 'inverness'], true)) {
        $sampleTowns[] = $town;
    }
    foreach (['aov', 'barriers'] as $family) {
        $edit = icomplyTownEditorial($family, $town);
        $sig = implode('|', $edit['ids']);
        if (isset($sigs[$family][$sig])) {
            $fail("duplicate editorial signature {$family} {$town['slug']} and {$sigs[$family][$sig]}");
        }
        $sigs[$family][$sig] = $town['slug'];
        $text = implode(' ', $edit['paragraphs']);
        $words = preg_match_all('/[A-Za-z0-9\']+/', $text);
        if ($words < 220) {
            $fail("thin copy {$family} {$town['slug']} words={$words}");
        }
        $minWords = min($minWords, (int)$words);
        if (!str_contains($text, (string)$town['name']) || !str_contains($text, '07517806082')) {
            $fail("missing town or phone in editorial {$family} {$town['slug']}");
        }
        if (stripos($text, 'tunstall') !== false) {
            $fail("Tunstall in editorial {$family} {$town['slug']}");
        }
        $html = icomplyTownManufacturerHtml($family, $town);
        if (stripos($html, 'tunstall') !== false) {
            $fail("Tunstall in manufacturers {$family} {$town['slug']}");
        }
        foreach (icomplyTownBrandRows($family) as $brand) {
            if (!str_contains($html, 'data-manufacturer="' . $brand['slug'] . '"')) {
                $fail("missing brand {$brand['slug']} on {$family} {$town['slug']}");
            }
            if (!str_contains($html, '/assets/images/manufacturers/nameplates/' . $brand['slug'] . '.svg')) {
                $fail("missing logo {$brand['slug']}");
            }
            if (!str_contains($html, '/pages/manufacturers/' . $brand['slug'])) {
                $fail("missing link {$brand['slug']}");
            }
        }
        if ($family === 'barriers' && !str_contains($html, 'Partner')) {
            $fail('CAME partner badge missing ' . $town['slug']);
        }
        if ($i === 0) {
            $wizard = icomplyTownWizardHtml($family, $town, 'csrf-test');
            if (!str_contains($wizard, 'town-spec-wizard') || !str_contains($wizard, '07517806082')) {
                $fail('wizard incomplete');
            }
            if ($family === 'barriers' && !preg_match('/<option selected>CAME<\/option>/', $wizard)) {
                $fail('wizard does not default to CAME');
            }
        }
    }
}

if (!function_exists('session_start') || session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
$_SERVER['REQUEST_URI'] = '/pages/aov/stockport';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';
ob_start();
icomplyRenderTownPage('aov', 'stockport');
$aovHtml = (string)ob_get_clean();
ob_start();
icomplyRenderTownPage('barriers', 'cardiff');
$barHtml = (string)ob_get_clean();
foreach (['aov' => $aovHtml, 'barriers' => $barHtml] as $label => $html) {
    if (!str_contains($html, '<h1') || !str_contains($html, '07517806082') || !str_contains($html, 'id="manufacturers"')) {
        $fail("full render missing structure {$label}");
    }
    if (!preg_match('/<article\b.*<\/article>/s', $html, $articleMatch)) {
        $fail("article missing {$label}");
    }
    if (stripos($articleMatch[0], 'tunstall') !== false) {
        $fail("Tunstall in article {$label}");
    }
}
if (!str_contains($barHtml, 'CAME is the partner') && !str_contains($barHtml, 'Partner')) {
    $fail('full barriers page missing CAME partner');
}
if (getManufacturerBySlug('came') === null) {
    $fail('CAME manufacturer page missing from catalogue');
}

$routes = icomplyUkTownRoutes();
$expected = 2 + (count($towns) * 2);
if (count($routes) !== $expected) {
    $fail("route count {$expected} expected, got " . count($routes));
}

echo 'OK towns=' . count($towns)
    . ' aov_pages=' . count($towns)
    . ' barrier_pages=' . count($towns)
    . ' routes=' . count($routes)
    . ' aov_brands=' . count($aovBrands)
    . ' barrier_brands=' . count($barBrands)
    . ' min_editorial_words=' . $minWords
    . "\n";
