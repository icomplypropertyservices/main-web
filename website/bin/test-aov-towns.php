<?php
/**
 * Anti-slop checks for mainland AOV town pages.
 * Usage: php bin/test-aov-towns.php
 */
require_once __DIR__ . '/../includes/aov-towns.php';

$fail = 0;
$ok = static function (bool $cond, string $message) use (&$fail): void {
    if ($cond) {
        echo "OK   {$message}\n";
        return;
    }
    echo "FAIL {$message}\n";
    $fail++;
};

$towns = aovTowns();
$ok(count($towns) > 1000, 'town count ' . count($towns) . ' is over 1000');
$slugs = [];
$labels = [];
$bodies = [];
$banned = [
    'nestled', 'vibrant', 'bustling', 'tapestry', 'look no further', 'in today',
    'cutting-edge', 'delve', 'leverage', 'world-class', 'passionate', 'one-stop',
    'seamless', 'bespoke', 'state-of-the-art', 'premier', 'game-changer',
    'we recently', 'years of experience', 'your trusted', 'comprehensive solution',
];
$layouts = [];
$nations = ['England' => 0, 'Wales' => 0, 'Scotland' => 0];

foreach ($towns as $town) {
    $slug = (string)$town['slug'];
    $ok(!isset($slugs[$slug]), "unique slug {$slug}");
    $slugs[$slug] = true;
    $label = (string)$town['label'];
    $ok(!isset($labels[$label]), "unique label {$label}");
    $labels[$label] = true;
    $pop = (int)$town['population'];
    $ok($pop > 10000, "{$slug} population {$pop} > 10000");
    $ok(in_array($town['nation'], ['England', 'Wales', 'Scotland'], true), "{$slug} nation");
    $nations[$town['nation']]++;
    $name = strtolower((string)$town['name']);
    $ok(!str_contains($name, 'isle of') && !str_contains($name, 'island'), "{$slug} not an island name");
    $ok($town['county'] !== 'Isle of Wight' && $town['county'] !== 'Anglesey', "{$slug} not IOW/Anglesey");

    $blob = aovOpening($town) . "\n" . aovRulesParagraph($town) . "\n" . aovBookingParagraph($town) . "\n" . aovSurveyParagraph($town);
    foreach (aovFaqs($town) as $faq) {
        $blob .= "\n" . $faq['q'] . "\n" . $faq['a'];
    }
    $low = strtolower($blob);
    foreach ($banned as $word) {
        if (str_contains($low, $word)) {
            $ok(false, "{$slug} contains banned phrase {$word}");
        }
    }
    $ok(!str_contains($blob, '£'), "{$slug} has no pound price");
    $ok(str_contains($blob, number_format($pop)) || str_contains($blob, (string)$pop), "{$slug} states population");
    $hash = md5($blob);
    $ok(!isset($bodies[$hash]), "{$slug} body is unique");
    $bodies[$hash] = $slug;

    $meta = aovMetaDescription($town);
    $metaLen = mb_strlen($meta);
    $ok($metaLen >= 70 && $metaLen <= 165, "{$slug} meta length {$metaLen}");

    $layouts[aovSeed($slug, 'layout') % 3] = true;

    if ($town['nation'] === 'Scotland') {
        $ok(str_contains($blob, 'Technical Handbook'), "{$slug} cites Technical Handbook");
        $ok(str_contains($blob, 'wrong document'), "{$slug} rejects English guidance");
        $ok(!str_contains($blob, 'Approved Document B is the usual guidance'), "{$slug} does not apply English ADB");
    } elseif ($town['nation'] === 'Wales') {
        $ok(str_contains($blob, 'issued for Wales'), "{$slug} cites Welsh ADB");
        $ok(!str_contains($blob, 'Building Safety Act'), "{$slug} does not apply English HRB Act");
    } else {
        $ok(str_contains($blob, 'Building Regulations for England'), "{$slug} cites English regulations");
        $ok(str_contains($blob, 'Approved Document B'), "{$slug} cites ADB");
        $ok(!str_contains($blob, 'Technical Handbook'), "{$slug} does not cite Scottish handbook");
    }
}

$ok($nations['England'] > 0 && $nations['Wales'] > 0 && $nations['Scotland'] > 0, 'all three nations present ' . json_encode($nations));
$ok(count($layouts) === 3, 'three section orders in use');
$ok(aovTownBySlug('belfast') === null, 'Belfast excluded');
$ok(aovTownBySlug('ryde') === null && aovTownBySlug('holyhead') === null, 'Ryde and Holyhead excluded');
$ok(aovTownBySlug('stockport') !== null && aovTownBySlug('edinburgh') !== null && aovTownBySlug('cardiff') !== null, 'sample towns exist');

$samples = ['stockport', 'london', 'edinburgh', 'cardiff', 'penzance', 'inverness', 'hayes', 'hayes-bromley', 'newport', 'newport-telford-and-wrekin'];
foreach ($samples as $sample) {
    $town = aovTownBySlug($sample);
    $ok($town !== null, "sample {$sample} loaded");
    if ($town === null) {
        continue;
    }
    ob_start();
    renderAovTownPage($town);
    $html = (string)ob_get_clean();
    $ok(str_contains($html, '<h1'), "{$sample} has h1");
    $ok(str_contains($html, 'rel="canonical"'), "{$sample} has canonical");
    $ok(str_contains($html, 'application/ld+json'), "{$sample} has schema");
    $ok(str_contains($html, '/pages/aov/' . $sample), "{$sample} canonical path");
    $ok(!preg_match('/Fatal error|Parse error|Warning:/', $html), "{$sample} renders clean");
}

ob_start();
renderAovTownIndex();
$index = (string)ob_get_clean();
$ok(str_contains($index, (string)count($towns)), 'index shows town count');
$ok(substr_count($index, '/pages/aov/') >= count($towns), 'index links every town');

require_once SITE_ROOT . '/includes/sitemap.php';
$xml = icomplyBuildSitemapXml('https://icomplypropertyservices.co.uk');
$aovLocs = preg_match_all('#/pages/aov(?:/[a-z0-9\-]+)?</loc>#', $xml);
$ok($aovLocs === count($towns) + 1, "sitemap AOV locs {$aovLocs} expected " . (count($towns) + 1));
$ok(!preg_match('#/pages/aov-air-handling/[a-z0-9\-]+</loc>#', $xml), 'sitemap has no service×town AOV doorways');
$ok(str_contains($xml, '/pages/aov/edinburgh</loc>'), 'sitemap lists Edinburgh');
$ok(!str_contains($xml, '/pages/aov/belfast'), 'sitemap omits Belfast');

echo $fail === 0 ? "\nAOV TOWN GATE PASSED towns=" . count($towns) . " pages=" . (count($towns) + 1) . "\n" : "\n{$fail} FAILURES\n";
exit($fail > 0 ? 1 : 0);
