<?php
/**
 * 10,000 keyword variants per service, plus a Greater Manchester area page each.
 * Usage: php website/bin/check-keyword-variants.php
 */
declare(strict_types=1);

putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
putenv('SITE_URL=https://icomplypropertyservices.co.uk');
$_ENV['SITE_URL'] = 'https://icomplypropertyservices.co.uk';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
$_SERVER['HTTPS'] = 'on';

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/keyword-variants.php';

$fail = 0;
$bad = static function (string $message) use (&$fail): void {
    echo "FAIL: {$message}\n";
    $fail++;
};

$catalogue = icomplyKeywordVariantCatalogue();
$counts = icomplyKeywordVariantCounts($catalogue);
$services = $catalogue['services'];
// Property SEO pack services are deliberately kept off the 10k variant matrix.
$expectedServices = count(getServices()) - (function_exists('icomplyPropertyPackServiceSlugs') ? count(icomplyPropertyPackServiceSlugs()) : 0);
if (count($services) !== $expectedServices) {
    $bad('service count ' . count($services) . ' != ' . $expectedServices);
}
if ((int)$catalogue['per_service'] !== 10000) {
    $bad('per_service is not 10000');
}
$axes = count($catalogue['modifiers']) * count($catalogue['audiences']) * count($catalogue['scopes']) * count($catalogue['intents']);
if ($axes < 10000) {
    $bad('axis space ' . $axes . ' is under 10000');
}
$townSlugs = array_column($catalogue['towns'], 'slug');
if (in_array('burnley', $townSlugs, true)) {
    $bad('Burnley is in the Greater Manchester variant towns');
}
foreach (['bolton', 'manchester', 'stockport', 'trafford', 'tameside', 'worsley', 'chorlton', 'wythenshawe', 'shaw', 'milnrow'] as $need) {
    if (!in_array($need, $townSlugs, true)) {
        $bad('missing Greater Manchester town ' . $need);
    }
}
if (($catalogue['towns'][0]['slug'] ?? '') !== 'bolton') {
    $bad('first town should be bolton');
}
if (count($catalogue['towns']) < 58) {
    $bad('expected at least 58 Greater Manchester towns, got ' . count($catalogue['towns']));
}

$bySlug = [];
foreach ($services as $service) {
    $bySlug[$service['slug']] = $service;
    $space = count($service['stems']) * $axes;
    if ($space < 10000) {
        $bad($service['slug'] . ' variant space ' . $space . ' is under 10000');
    }
    if (count($service['stems']) < 1) {
        $bad($service['slug'] . ' has no stems');
    }
    $decoded = icomplyKeywordVariantDecode($service, $catalogue, 0);
    if (($decoded['modifier']['slug'] ?? '') !== 'emergency' || ($decoded['stem']['slug'] ?? '') !== $service['stems'][0]['slug']) {
        $bad($service['slug'] . ' index 0 is not the first stem and emergency');
    }
    $stemCount = count($service['stems']);
    for ($i = 0; $i < $stemCount; $i++) {
        $row = icomplyKeywordVariantDecode($service, $catalogue, $i);
        if (($row['stem']['slug'] ?? '') !== $service['stems'][$i]['slug']) {
            $bad($service['slug'] . ' stem ' . $i . ' is not on the fast axis');
            break;
        }
    }
}

$electrical = $bySlug['electrical'] ?? null;
$gas = $bySlug['gas-systems'] ?? null;
$lighting = $bySlug['emergency-lighting'] ?? null;
if (!$electrical || !$gas || !$lighting) {
    $bad('electrical, gas-systems or emergency-lighting missing');
} else {
    if (!empty($electrical['gas'])) {
        $bad('electrical service flagged as gas');
    }
    if (empty($gas['gas'])) {
        $bad('gas-systems service not flagged as gas');
    }
    $stemSlugs = array_column($electrical['stems'], 'slug');
    foreach (['rewire', 'eicr'] as $stem) {
        if (!in_array($stem, $stemSlugs, true) && !in_array($stem . '-report', $stemSlugs, true)) {
            if ($stem === 'eicr' && (in_array('eicr', $stemSlugs, true) || in_array('eicr-report', $stemSlugs, true))) {
                continue;
            }
            $bad('electrical missing stem ' . $stem);
        }
    }
    if (!in_array('eicr', $stemSlugs, true) && !in_array('eicr-report', $stemSlugs, true)) {
        $bad('electrical missing eicr stem');
    }
    $lightStems = array_column($lighting['stems'], 'slug');
    if (!in_array('external-security-lighting', $lightStems, true)) {
        $bad('emergency-lighting missing external-security-lighting');
    }
    $rewireIndex = array_search('rewire', $stemSlugs, true);
    $cheapIndex = 0;
    foreach ($catalogue['modifiers'] as $i => $modifier) {
        if ($modifier['slug'] === 'cheap') {
            $cheapIndex = $i;
        }
    }
    if ($rewireIndex === false) {
        $bad('rewire stem missing');
    } else {
        $n = icomplyKeywordVariantEncode($electrical, $catalogue, (int)$rewireIndex, $cheapIndex, 0, 0, 0);
        if ($n >= 10000) {
            $bad('rewire cheap variant index ' . $n . ' is outside 10000');
        }
        $decoded = icomplyKeywordVariantDecode($electrical, $catalogue, $n);
        if (($decoded['stem']['slug'] ?? '') !== 'rewire' || ($decoded['modifier']['slug'] ?? '') !== 'cheap') {
            $bad('rewire cheap decode mismatch');
        }
    }
}

$blob = json_encode($catalogue);
if (str_contains((string)$blob, '£') || str_contains((string)$blob, 'NICEIC')) {
    $bad('catalogue contains a price mark or NICEIC');
}
if (!str_contains((string)$blob, 'does not publish a low rate')) {
    $bad('cheap note missing');
}
if (str_contains((string)$blob, 'does not carry out gas') || str_contains((string)$blob, "don't do gas")) {
    $bad('catalogue denies gas work');
}

$smallest = $services[0];
foreach ($services as $service) {
    if (count($service['stems']) < count($smallest['stems'])) {
        $smallest = $service;
    }
}
foreach ([$electrical, $smallest] as $service) {
    if (!$service) {
        continue;
    }
    $seen = [];
    for ($n = 0; $n < 10000; $n++) {
        $decoded = icomplyKeywordVariantDecode($service, $catalogue, $n);
        $slug = icomplyKeywordVariantSlug($service['slug'], $decoded);
        if (isset($seen[$slug])) {
            $bad($service['slug'] . ' duplicate variant slug at ' . $n);
            break;
        }
        $seen[$slug] = true;
    }
    if (count($seen) !== 10000 && $fail === 0) {
        $bad($service['slug'] . ' produced ' . count($seen) . ' slugs');
    }
}

$samples = [
    'paths' => [],
    'total' => $counts['urls'],
    'chunk' => $catalogue['chunk'],
    'towns' => $counts['towns'],
    'services' => $counts['services'],
    'parts' => $counts['sitemap_parts'],
];
foreach ([0, 1, (int)$counts['towns'], (int)$counts['towns'] + 1, $counts['urls'] - 1] as $global) {
    $samples['paths'][(string)$global] = icomplyKeywordVariantPathAt($catalogue, (int)$global);
}
if ($electrical) {
    $samples['electrical0'] = icomplyKeywordVariantSlug('electrical', icomplyKeywordVariantDecode($electrical, $catalogue, 0));
}

$specPath = '/tmp/icomply-variant-spec.json';
$samplePath = '/tmp/icomply-variant-samples.json';
file_put_contents($specPath, json_encode($catalogue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
file_put_contents($samplePath, json_encode($samples, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents('/tmp/icomply-variant-counts.json', json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "services={$counts['services']} towns={$counts['towns']} keywords_per_service={$counts['keywords_per_service']}\n";
echo "hubs={$counts['hubs']} area_pages={$counts['area_pages']} urls={$counts['urls']} sitemap_parts={$counts['sitemap_parts']}\n";
foreach ($counts['per_service'] as $row) {
    echo $row['slug'] . "\tstems=" . $row['stems'] . "\tkeywords=" . $row['keywords'] . "\tarea_pages=" . $row['area_pages'] . "\turls=" . $row['urls'] . "\n";
}

if ($fail > 0) {
    echo "FAIL {$fail}\n";
    exit(1);
}
echo "PASS\n";
exit(0);
