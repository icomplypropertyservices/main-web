<?php
/**
 * Compact keyword × service × Greater Manchester variant catalogue.
 *
 * 10,000 variants per service are not stored as rows. A mixed-radix slug
 * is decoded at request time. Stem is the fastest axis so every existing
 * job appears inside the 10,000. Area pages are /pages/keywords/{slug}/{town}.
 */
declare(strict_types=1);

const ICOMPLY_VARIANT_KEYWORDS = 10000;

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantAudiences(): array
{
    return [
        ['slug' => 'landlord', 'label' => 'landlords', 'title' => 'Landlord', 'note' => 'Ideal for landlords and letting agents. We quote for the building, keep you updated and email the paperwork for your tenancy file.'],
        ['slug' => 'commercial', 'label' => 'commercial clients', 'title' => 'Commercial', 'note' => 'For commercial buildings. We plan around occupied floors and opening hours, and confirm the full scope in writing before we attend.'],
        ['slug' => 'domestic', 'label' => 'homeowners', 'title' => 'Domestic', 'note' => 'For homeowners. You get a clear written quote first, and the same standard of work and paperwork as our landlord clients.'],
        ['slug' => 'hmo', 'label' => 'HMO operators', 'title' => 'HMO', 'note' => 'For HMOs. We cover the rooms, the shared areas and the paperwork your licence asks for.'],
        ['slug' => 'void', 'label' => 'void properties', 'title' => 'Void', 'note' => 'For empty properties between tenancies. Access is easier, so we can often book quickly and get the paperwork back before the next tenant moves in.'],
        ['slug' => 'shop', 'label' => 'shops', 'title' => 'Shop', 'note' => 'For shops. We work around your trading hours and keep disruption at the shop front to a minimum.'],
        ['slug' => 'office', 'label' => 'offices', 'title' => 'Office', 'note' => 'For offices. We confirm which floors are included and plan the visit around your working day.'],
        ['slug' => 'flat', 'label' => 'flats', 'title' => 'Flat', 'note' => 'For flats. We separate shared risers and entrances from the flat itself, so you know exactly what is covered.'],
        ['slug' => 'house', 'label' => 'houses', 'title' => 'House', 'note' => 'For houses. The quote is based on your home, not a block, so you only pay for what the job needs.'],
        ['slug' => 'block', 'label' => 'blocks of flats', 'title' => 'Block', 'note' => 'For blocks. Common parts and individual flats are listed separately when both are included.'],
    ];
}

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantModifiers(): array
{
    return [
        ['slug' => 'emergency', 'label' => 'Emergency', 'title' => 'Emergency', 'note' => 'Need help urgently? Call 07517 806082 and we will arrange the earliest available engineer.'],
        ['slug' => 'next-day', 'label' => 'Next day', 'title' => 'Next day', 'note' => 'Next-day visits are often available. Call 07517 806082 and we will confirm the slot.'],
        ['slug' => 'near-me', 'label' => 'Near me', 'title' => 'Near me', 'note' => 'Local engineers from our Stockport base, covering Greater Manchester and beyond.'],
        ['slug' => 'same-day', 'label' => 'Same day', 'title' => 'Same day', 'note' => 'Same-day visits are often available. Call 07517 806082 early in the day and we will confirm the slot.'],
        ['slug' => '24-hour', 'label' => '24 hour', 'title' => '24 hour', 'note' => 'Send your enquiry any time, day or night. We reply as soon as an engineer is available.'],
        ['slug' => 'cheap', 'label' => 'Cheap', 'title' => 'Cheap', 'note' => 'Competitive, fixed written quotes with no hidden extras.'],
        ['slug' => 'local', 'label' => 'Local', 'title' => 'Local', 'note' => 'Local engineers from our Stockport base, covering every Greater Manchester town.'],
        ['slug' => 'stockport', 'label' => 'Stockport', 'title' => 'Stockport', 'note' => 'Based in Offerton, Stockport, so Stockport jobs are on our doorstep.'],
        ['slug' => 'manchester', 'label' => 'Manchester', 'title' => 'Manchester', 'note' => 'We cover Manchester and every Greater Manchester town from our Stockport base.'],
        ['slug' => 'greater-manchester', 'label' => 'Greater Manchester', 'title' => 'GM', 'note' => 'We cover every Greater Manchester town from our Stockport base, and each town has its own page.'],
    ];
}

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantScopes(): array
{
    return [
        ['slug' => 'install', 'label' => 'installation', 'title' => 'Install', 'note' => 'New installations, planned in writing. We confirm what is included, including making good, before we start.'],
        ['slug' => 'repair', 'label' => 'repair', 'title' => 'Repair', 'note' => 'We find the fault first, then confirm the repair and the price before any parts are fitted.'],
        ['slug' => 'service', 'label' => 'servicing', 'title' => 'Service', 'note' => 'Planned servicing against an agreed checklist, with a written record after every visit.'],
        ['slug' => 'inspection', 'label' => 'inspection', 'title' => 'Inspect', 'note' => 'Inspection and testing with a clear written report. Any repairs are quoted separately.'],
        ['slug' => 'replacement', 'label' => 'replacement', 'title' => 'Replace', 'note' => 'We replace worn or failed equipment and confirm in writing what is removed and what goes in.'],
        ['slug' => 'maintenance', 'label' => 'maintenance', 'title' => 'Maintain', 'note' => 'One-off or regular maintenance visits, booked to suit you.'],
        ['slug' => 'survey', 'label' => 'survey', 'title' => 'Survey', 'note' => 'A site survey before any work, so the quote reflects your building.'],
        ['slug' => 'supply', 'label' => 'supply', 'title' => 'Supply', 'note' => 'We supply the equipment listed in your quote, with fitting available on request.'],
    ];
}

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantIntents(): array
{
    return [
        ['slug' => 'quote', 'label' => 'Quote', 'title' => 'Quote', 'note' => 'Get a fixed written quote. Send the address and a few photos and we will come back to you quickly.'],
        ['slug' => 'book', 'label' => 'Book', 'title' => 'Book', 'note' => 'Book a visit. We confirm the date as soon as you accept the quote.'],
        ['slug' => 'arrange', 'label' => 'Arrange', 'title' => 'Arrange', 'note' => 'We arrange the visit around access, tenants and opening hours.'],
        ['slug' => 'find', 'label' => 'Find', 'title' => 'Find', 'note' => 'Looking for a reliable engineer in Greater Manchester? We cover every town from Stockport.'],
        ['slug' => 'compare', 'label' => 'Compare', 'title' => 'Compare', 'note' => 'Compare with confidence: every quote sets out the full scope and a fixed price in writing.'],
        ['slug' => 'schedule', 'label' => 'Schedule', 'title' => 'Schedule', 'note' => 'Pick a date that suits you, and we confirm it when you accept the quote.'],
        ['slug' => 'instruct', 'label' => 'Instruct', 'title' => 'Instruct', 'note' => 'Ready to go ahead? Accept the quote in writing and we will book the engineer.'],
        ['slug' => 'resurvey', 'label' => 'Resurvey', 'title' => 'Resurvey', 'note' => 'A return visit after an earlier job or a change to the building.'],
        ['slug' => 'return', 'label' => 'Return', 'title' => 'Return', 'note' => 'A follow-up visit to finish any items left from an earlier job.'],
        ['slug' => 'landlord-job', 'label' => 'Landlord job', 'title' => 'Landlord', 'note' => 'For landlords. Certificates and reports are emailed straight to you.'],
        ['slug' => 'agent-job', 'label' => 'Agent job', 'title' => 'Agent', 'note' => 'For letting agents. Tell us who owns the building and who should receive the paperwork.'],
        ['slug' => 'occupied', 'label' => 'Occupied', 'title' => 'Occupied', 'note' => 'For occupied buildings. We work around residents, staff and customers.'],
        ['slug' => 'vacant', 'label' => 'Vacant', 'title' => 'Vacant', 'note' => 'For vacant buildings. Tell us about keys and alarms and we will plan the access.'],
    ];
}

function icomplyKeywordVariantSlugPart(string $value): string
{
    $slug = function_exists('areaSlug') ? areaSlug($value) : strtolower(trim($value));
    if (!function_exists('areaSlug')) {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');
    }
    return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) ? $slug : '';
}

function icomplyKeywordVariantCleanLabel(string $label, string $slug): string
{
    $label = trim(html_entity_decode(strip_tags($label), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $label = str_replace(['£', '€', '$', '&'], ['', '', '', ' and '], $label);
    $label = preg_replace('/\b(?:NICEIC|BAFE|CHAS|SafeContractor|REFCOM)\b/i', 'certification scheme', $label) ?? $label;
    $label = str_replace(['"', "'"], '', $label);
    $label = preg_replace('/\s+/u', ' ', $label) ?? $label;
    $label = trim($label, " \t\n\r\0\x0B-");
    if ($label === '') {
        $label = ucwords(str_replace('-', ' ', $slug));
    }
    if (function_exists('mb_substr')) {
        $label = mb_substr($label, 0, 80);
    } else {
        $label = substr($label, 0, 80);
    }
    return trim($label);
}

/**
 * @param array<string,array{slug:string,label:string,gas:bool}> $bucket
 */
function icomplyKeywordVariantAddStem(array &$bucket, string $slug, string $label, bool $gas): void
{
    $slug = icomplyKeywordVariantSlugPart($slug);
    if ($slug === '') {
        return;
    }
    if (isset($bucket[$slug])) {
        if ($gas) {
            $bucket[$slug]['gas'] = true;
        }
        return;
    }
    if (count($bucket) >= ICOMPLY_VARIANT_KEYWORDS) {
        return;
    }
    $bucket[$slug] = [
        'slug' => $slug,
        'label' => icomplyKeywordVariantCleanLabel($label, $slug),
        'gas' => $gas,
    ];
}

/**
 * @return list<string>
 */
function icomplyKeywordVariantJobFiles(): array
{
    $files = array_merge(
        glob(SITE_ROOT . '/data/*job*.json') ?: [],
        glob(SITE_ROOT . '/data/job-types-*.json') ?: []
    );
    $files = array_values(array_unique(array_filter($files, 'is_file')));
    sort($files);
    return $files;
}

/**
 * @return array<string,mixed>
 */
function icomplyKeywordVariantCatalogue(): array
{
    if (!function_exists('icomplyGreaterManchesterTownNames')) {
        require_once SITE_ROOT . '/includes/building-hub-copy.php';
    }
    if (!function_exists('icomplyCopyIsGasTopic')) {
        $gasFile = SITE_ROOT . '/includes/gas-legal.php';
        if (is_file($gasFile)) {
            require_once $gasFile;
        }
    }
    $profiles = [];
    $profileFile = SITE_ROOT . '/includes/area-profiles.php';
    if (is_file($profileFile)) {
        $loaded = include $profileFile;
        if (is_array($loaded)) {
            $profiles = $loaded;
        }
    }
    $boroughFallbacks = [
        'Trafford' => [
            'districts' => 'M16, M17, M32, M33, M41 and WA14–WA15',
            'stock' => 'borough housing from Stretford through Sale to Altrincham, plus retail and offices',
            'focus' => 'Trafford landlords, shops and offices',
        ],
        'Tameside' => [
            'districts' => 'OL5–OL7, SK14–SK16, M34 and M43',
            'stock' => 'borough housing and town centres from Ashton-under-Lyne through Hyde and Stalybridge',
            'focus' => 'Tameside landlords, shops and industrial units',
        ],
    ];

    $serviceNames = getServices();
    $byService = [];
    foreach ($serviceNames as $slug => $label) {
        $slug = icomplyKeywordVariantSlugPart((string)$slug);
        if ($slug === '') {
            continue;
        }
        $byService[$slug] = [];
    }

    foreach (getMajorKeywords() as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $service = icomplyKeywordVariantSlugPart((string)($meta['service'] ?? ''));
        if ($service === '' || !array_key_exists($service, $byService)) {
            continue;
        }
        $gas = $service === 'gas-systems';
        if (!$gas && function_exists('icomplyKeywordRecordIsGas')) {
            $gas = icomplyKeywordRecordIsGas($meta, (string)$slug);
        }
        icomplyKeywordVariantAddStem(
            $byService[$service],
            (string)$slug,
            (string)($meta['name'] ?? $meta['h1'] ?? ''),
            $gas
        );
    }

    $jobRows = [];
    foreach (icomplyKeywordVariantJobFiles() as $file) {
        $decoded = json_decode((string)file_get_contents($file), true);
        if (!is_array($decoded)) {
            continue;
        }
        $jobs = $decoded['jobs'] ?? $decoded;
        if (!is_array($jobs)) {
            continue;
        }
        foreach ($jobs as $job) {
            if (!is_array($job)) {
                continue;
            }
            $service = (string)($job['service'] ?? $job['service_type'] ?? '');
            $jobRows[] = [
                'slug' => (string)($job['slug'] ?? ''),
                'name' => (string)($job['name'] ?? $job['h1'] ?? ''),
                'service' => $service,
            ];
        }
    }
    usort($jobRows, static function (array $a, array $b): int {
        return [$a['service'], $a['slug']] <=> [$b['service'], $b['slug']];
    });
    foreach ($jobRows as $job) {
        $service = icomplyKeywordVariantSlugPart($job['service']);
        if ($service === '' || !array_key_exists($service, $byService)) {
            continue;
        }
        $gas = $service === 'gas-systems';
        if (!$gas && function_exists('icomplyCopyIsGasTopic')) {
            $gas = icomplyCopyIsGasTopic($job['slug'], $job['name']);
        }
        icomplyKeywordVariantAddStem($byService[$service], $job['slug'], $job['name'], $gas);
    }

    $services = [];
    foreach ($serviceNames as $slug => $label) {
        $slug = icomplyKeywordVariantSlugPart((string)$slug);
        if ($slug === '' || !array_key_exists($slug, $byService)) {
            continue;
        }
        if ($byService[$slug] === []) {
            icomplyKeywordVariantAddStem($byService[$slug], $slug, (string)$label, $slug === 'gas-systems');
        }
        $image = $slug;
        if (!is_file(SITE_ROOT . '/assets/images/services/' . $slug . '.jpg')) {
            $image = 'building-maintenance';
        }
        $stems = array_values($byService[$slug]);
        $seenLabels = [];
        foreach ($stems as $stemIndex => $stem) {
            $label = $stem['label'];
            if (isset($seenLabels[$label])) {
                $label = icomplyKeywordVariantCleanLabel(ucwords(str_replace('-', ' ', $stem['slug'])), $stem['slug']);
                if (isset($seenLabels[$label])) {
                    $label = $stem['slug'];
                }
            }
            $seenLabels[$label] = true;
            $stems[$stemIndex]['label'] = $label;
        }
        $services[] = [
            'slug' => $slug,
            'label' => icomplyKeywordVariantCleanLabel((string)$label, $slug),
            'gas' => $slug === 'gas-systems',
            'image' => $image,
            'stems' => $stems,
        ];
    }

    $towns = [];
    if (!function_exists('icomplyLocalTownNames')) {
        $crawlFile = SITE_ROOT . '/includes/gm-crawl.php';
        if (is_file($crawlFile)) {
            require_once $crawlFile;
        }
    }
    $variantTowns = function_exists('icomplyLocalTownNames')
        ? icomplyLocalTownNames()
        : icomplyGreaterManchesterTownNames();
    foreach ($variantTowns as $name) {
        $name = (string)$name;
        $slug = icomplyKeywordVariantSlugPart($name);
        if ($slug === '' || isset($towns[$slug])) {
            continue;
        }
        $profile = $profiles[$name] ?? $boroughFallbacks[$name] ?? null;
        $districts = is_array($profile) ? trim((string)($profile['districts'] ?? '')) : '';
        $stock = is_array($profile) ? trim((string)($profile['stock'] ?? '')) : '';
        $focus = is_array($profile) ? trim((string)($profile['focus'] ?? '')) : '';
        if ($districts === '') {
            $districts = $name;
        }
        if ($stock === '') {
            $stock = 'mixed housing and commercial buildings in ' . $name;
        }
        if ($focus === '') {
            $focus = 'sites in ' . $name;
        }
        $towns[$slug] = [
            'slug' => $slug,
            'name' => $name,
            'districts' => $districts,
            'stock' => $stock,
            'focus' => $focus,
        ];
    }

    $chunk = defined('ICOMPLY_MATRIX_SITEMAP_CHUNK') ? (int)ICOMPLY_MATRIX_SITEMAP_CHUNK : 45000;
    return [
        'version' => 1,
        'per_service' => ICOMPLY_VARIANT_KEYWORDS,
        'chunk' => $chunk,
        'site' => 'https://icomplypropertyservices.co.uk',
        'audiences' => icomplyKeywordVariantAudiences(),
        'modifiers' => icomplyKeywordVariantModifiers(),
        'scopes' => icomplyKeywordVariantScopes(),
        'intents' => icomplyKeywordVariantIntents(),
        'towns' => array_values($towns),
        'services' => $services,
    ];
}

/**
 * @param array<string,mixed> $service
 * @param array<string,mixed> $catalogue
 * @return array{stem:array<string,mixed>,modifier:array<string,mixed>,audience:array<string,mixed>,scope:array<string,mixed>,intent:array<string,mixed>}
 */
function icomplyKeywordVariantDecode(array $service, array $catalogue, int $n): array
{
    $stems = $service['stems'];
    $mods = $catalogue['modifiers'];
    $auds = $catalogue['audiences'];
    $scopes = $catalogue['scopes'];
    $intents = $catalogue['intents'];
    $s = count($stems);
    $r = intdiv($n, $s);
    $stem = $stems[$n % $s];
    $modifier = $mods[$r % count($mods)];
    $r = intdiv($r, count($mods));
    $audience = $auds[$r % count($auds)];
    $r = intdiv($r, count($auds));
    $scope = $scopes[$r % count($scopes)];
    $r = intdiv($r, count($scopes));
    $intent = $intents[$r % count($intents)];
    return [
        'stem' => $stem,
        'modifier' => $modifier,
        'audience' => $audience,
        'scope' => $scope,
        'intent' => $intent,
    ];
}

/**
 * @param array{stem:array<string,mixed>,audience:array<string,mixed>,modifier:array<string,mixed>,scope:array<string,mixed>,intent:array<string,mixed>} $decoded
 */
function icomplyKeywordVariantSlug(string $serviceSlug, array $decoded): string
{
    return $serviceSlug
        . '--' . $decoded['stem']['slug']
        . '--' . $decoded['audience']['slug']
        . '--' . $decoded['modifier']['slug']
        . '--' . $decoded['scope']['slug']
        . '--' . $decoded['intent']['slug'];
}

function icomplyKeywordVariantEncode(array $service, array $catalogue, int $stemIndex, int $modIndex, int $audIndex, int $scopeIndex, int $intentIndex): int
{
    $s = count($service['stems']);
    $m = count($catalogue['modifiers']);
    $a = count($catalogue['audiences']);
    $c = count($catalogue['scopes']);
    return $stemIndex + $s * ($modIndex + $m * ($audIndex + $a * ($scopeIndex + $c * $intentIndex)));
}

function icomplyKeywordVariantPathAt(array $catalogue, int $global): string
{
    $towns = $catalogue['towns'];
    $per = (int)$catalogue['per_service'];
    $stride = 1 + count($towns);
    $perService = $per * $stride;
    $service = $catalogue['services'][intdiv($global, $perService)];
    $local = $global % $perService;
    $n = intdiv($local, $stride);
    $slot = $local % $stride;
    $decoded = icomplyKeywordVariantDecode($service, $catalogue, $n);
    $slug = icomplyKeywordVariantSlug((string)$service['slug'], $decoded);
    if ($slot === 0) {
        return '/pages/keywords/' . $slug;
    }
    return '/pages/keywords/' . $slug . '/' . $towns[$slot - 1]['slug'];
}

/**
 * @param array<string,mixed> $catalogue
 * @return array<string,mixed>
 */
function icomplyKeywordVariantCounts(array $catalogue): array
{
    $towns = count($catalogue['towns']);
    $per = (int)$catalogue['per_service'];
    $chunk = max(1, (int)$catalogue['chunk']);
    $rows = [];
    $urls = 0;
    foreach ($catalogue['services'] as $service) {
        $serviceUrls = $per * (1 + $towns);
        $urls += $serviceUrls;
        $rows[] = [
            'slug' => $service['slug'],
            'label' => $service['label'],
            'stems' => count($service['stems']),
            'keywords' => $per,
            'hubs' => $per,
            'area_pages' => $per * $towns,
            'urls' => $serviceUrls,
            'gas' => (bool)$service['gas'],
        ];
    }
    return [
        'services' => count($catalogue['services']),
        'towns' => $towns,
        'keywords_per_service' => $per,
        'hubs' => $per * count($catalogue['services']),
        'area_pages' => $per * count($catalogue['services']) * $towns,
        'urls' => $urls,
        'sitemap_parts' => (int)ceil($urls / $chunk),
        'per_service' => $rows,
    ];
}
