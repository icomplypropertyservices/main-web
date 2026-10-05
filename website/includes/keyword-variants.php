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
        ['slug' => 'landlord', 'label' => 'landlords', 'title' => 'Landlord', 'note' => 'The instruction comes from a landlord or the agent acting for that landlord. The quote names the building and is price on application.'],
        ['slug' => 'commercial', 'label' => 'commercial clients', 'title' => 'Commercial', 'note' => 'The instruction is for a commercial building. Occupied floors, access times and the written scope are agreed before anyone attends. The quote is price on application.'],
        ['slug' => 'domestic', 'label' => 'homeowners', 'title' => 'Domestic', 'note' => 'The instruction is for a home. The visit is still scoped in writing and quoted price on application, including where a landlord later asks for the same address.'],
        ['slug' => 'hmo', 'label' => 'HMO operators', 'title' => 'HMO', 'note' => 'The instruction is for an HMO. Rooms, shared parts and the paperwork the operator has to keep are named in the scope. The quote is price on application.'],
        ['slug' => 'void', 'label' => 'void properties', 'title' => 'Void', 'note' => 'The instruction is for a void property between tenancies. Access is usually easier, and the scope still has to say what is included. The quote is price on application.'],
        ['slug' => 'shop', 'label' => 'shops', 'title' => 'Shop', 'note' => 'The instruction is for a shop. Trading hours and the shop front are part of the access note. The quote is price on application.'],
        ['slug' => 'office', 'label' => 'offices', 'title' => 'Office', 'note' => 'The instruction is for an office. The scope says which floors are included. The quote is price on application.'],
        ['slug' => 'flat', 'label' => 'flats', 'title' => 'Flat', 'note' => 'The instruction is for a flat. Shared risers, the entrance and the demised rooms are separated in the scope. The quote is price on application.'],
        ['slug' => 'house', 'label' => 'houses', 'title' => 'House', 'note' => 'The instruction is for a house. The scope follows that building rather than a block. The quote is price on application.'],
        ['slug' => 'block', 'label' => 'blocks of flats', 'title' => 'Block', 'note' => 'The instruction is for a block. Common parts and individual homes are listed separately if both are in scope. The quote is price on application.'],
    ];
}

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantModifiers(): array
{
    return [
        ['slug' => 'emergency', 'label' => 'Emergency', 'title' => 'Emergency', 'note' => 'The enquiry asks for an emergency attendance. A time is agreed when the quote is accepted. This page does not promise a night call-out.'],
        ['slug' => 'next-day', 'label' => 'Next day', 'title' => 'Next day', 'note' => 'Next day means the enquiry asks for the following working day where the diary allows. It is not a guaranteed slot. The quote is price on application.'],
        ['slug' => 'near-me', 'label' => 'Near me', 'title' => 'Near me', 'note' => 'Near me on this matrix means Greater Manchester, arranged from Stockport. It is not a UK-wide search result.'],
        ['slug' => 'same-day', 'label' => 'Same day', 'title' => 'Same day', 'note' => 'Same day means the enquiry asks for today where someone is free. It is not a guaranteed slot. The quote is price on application.'],
        ['slug' => '24-hour', 'label' => '24 hour', 'title' => '24 hour', 'note' => '24 hour means the enquiry can be sent at any time. Attendance is agreed when someone is available. This page does not promise a night call-out.'],
        ['slug' => 'cheap', 'label' => 'Cheap', 'title' => 'Cheap', 'note' => 'Cheap on this page means a competitive quote. The figure is price on application. This page does not publish a low rate.'],
        ['slug' => 'local', 'label' => 'Local', 'title' => 'Local', 'note' => 'Local means a Greater Manchester visit arranged from Stockport. Town pages name the building\'s town rather than a national script.'],
        ['slug' => 'stockport', 'label' => 'Stockport', 'title' => 'Stockport', 'note' => 'The enquiry names Stockport. If the building is in another Greater Manchester town, that town page is the one to use. The quote is price on application.'],
        ['slug' => 'manchester', 'label' => 'Manchester', 'title' => 'Manchester', 'note' => 'The enquiry names Manchester. Coverage on this matrix is Greater Manchester, town by town. The quote is price on application.'],
        ['slug' => 'greater-manchester', 'label' => 'Greater Manchester', 'title' => 'GM', 'note' => 'The enquiry is for Greater Manchester, not a UK-wide call-out. Each town already on this list has its own page. The quote is price on application.'],
    ];
}

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantScopes(): array
{
    return [
        ['slug' => 'install', 'label' => 'installation', 'title' => 'Install', 'note' => 'Installation means new work set out in a written scope. Making good and what is left out are named before the quote. The quote is price on application.'],
        ['slug' => 'repair', 'label' => 'repair', 'title' => 'Repair', 'note' => 'Repair means the failed part is identified first. The scope says whether the visit is diagnose only or diagnose and repair. The quote is price on application.'],
        ['slug' => 'service', 'label' => 'servicing', 'title' => 'Service', 'note' => 'Servicing means a planned visit against the agreed checklist. It is not an open-ended repair. The quote is price on application.'],
        ['slug' => 'inspection', 'label' => 'inspection', 'title' => 'Inspect', 'note' => 'Inspection means a look, a test or a report as agreed. Any later repair is a separate instruction. The quote is price on application.'],
        ['slug' => 'replacement', 'label' => 'replacement', 'title' => 'Replace', 'note' => 'Replacement means the existing item and the new scope are both named. Disposal is included only if the quote says so. The quote is price on application.'],
        ['slug' => 'maintenance', 'label' => 'maintenance', 'title' => 'Maintain', 'note' => 'Maintenance means repeat visits or a single planned visit, as the instruction says. The quote is price on application.'],
        ['slug' => 'survey', 'label' => 'survey', 'title' => 'Survey', 'note' => 'Survey means a written look at the building before any install or repair is instructed. The quote is price on application.'],
        ['slug' => 'supply', 'label' => 'supply', 'title' => 'Supply', 'note' => 'Supply means equipment or materials listed in the scope. Fitting is included only when the scope says so. The quote is price on application.'],
    ];
}

/**
 * @return list<array{slug:string,label:string,title:string,note:string}>
 */
function icomplyKeywordVariantIntents(): array
{
    return [
        ['slug' => 'quote', 'label' => 'Quote', 'title' => 'Quote', 'note' => 'The page is for a written quote. No fee is published here. The figure is price on application after the scope is clear.'],
        ['slug' => 'book', 'label' => 'Book', 'title' => 'Book', 'note' => 'The page is for a booking request. The date is agreed after the quote, not taken from a live diary on this page.'],
        ['slug' => 'arrange', 'label' => 'Arrange', 'title' => 'Arrange', 'note' => 'The page is for arranging a visit around access. The quote is price on application and names who instructed the work.'],
        ['slug' => 'find', 'label' => 'Find', 'title' => 'Find', 'note' => 'The page is for finding cover in Greater Manchester. The visit is still scoped and quoted price on application.'],
        ['slug' => 'compare', 'label' => 'Compare', 'title' => 'Compare', 'note' => 'Compare means you want this scope set out clearly. There is no price list on this page. The quote is price on application.'],
        ['slug' => 'schedule', 'label' => 'Schedule', 'title' => 'Schedule', 'note' => 'Schedule means you want a proposed date. Attendance is confirmed only when the quote is accepted.'],
        ['slug' => 'instruct', 'label' => 'Instruct', 'title' => 'Instruct', 'note' => 'Instruct means you are ready to commission the agreed scope. The quote is price on application and is accepted in writing.'],
        ['slug' => 'resurvey', 'label' => 'Resurvey', 'title' => 'Resurvey', 'note' => 'Resurvey means a return look after an earlier visit or a change in the building. The quote is price on application.'],
        ['slug' => 'return', 'label' => 'Return', 'title' => 'Return', 'note' => 'Return means a follow-up visit. The scope says what was left outstanding. The quote is price on application.'],
        ['slug' => 'landlord-job', 'label' => 'Landlord job', 'title' => 'Landlord', 'note' => 'The page is a landlord instruction. The report or certificate is left with the instructing client. The quote is price on application.'],
        ['slug' => 'agent-job', 'label' => 'Agent job', 'title' => 'Agent', 'note' => 'The page is an agent instruction. Say who owns the building and who receives the paperwork. The quote is price on application.'],
        ['slug' => 'occupied', 'label' => 'Occupied', 'title' => 'Occupied', 'note' => 'The building is occupied. The scope has to respect residents, staff or customers on site. The quote is price on application.'],
        ['slug' => 'vacant', 'label' => 'Vacant', 'title' => 'Vacant', 'note' => 'The building is vacant. Keys, alarms and the void condition are part of the access note. The quote is price on application.'],
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
