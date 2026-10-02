<?php
/**
 * Master job-type catalogue — exactly 1,753 unique slugs under /pages/keywords/<slug>.
 * Pages are rendered from the keyword template + structured service/category data.
 * Do not commit 1,753 stub PHP files.
 */
declare(strict_types=1);

function jobTypesMasterExpectedCount(): int
{
    return 1753;
}

function jobTypesMasterFile(): string
{
    return SITE_ROOT . '/data/job-types-master.json';
}

/** @return array{count?:int,jobs?:list<array<string,mixed>>} */
function jobTypesMasterData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = jobTypesMasterFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array{slug:string,name:string,service:string,related?:string}> */
function jobTypesMasterJobs(): array
{
    $jobs = jobTypesMasterData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function jobTypesMasterSlugs(): array
{
    $out = [];
    foreach (jobTypesMasterJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug !== '') {
            $out[] = $slug;
        }
    }
    return $out;
}

/**
 * Merge the 1,753-job master into the live keyword catalogue and fill unique SEO fields.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function jobTypesApplyMaster(array $keywords): array
{
    foreach (jobTypesMasterJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $synth = jobTypesSynthesize($job, $base);
        $keywords[$slug] = jobTypesMergePreferExisting($base, $synth);
    }
    $seenTitle = [];
    $seenH1 = [];
    foreach ($keywords as $slug => &$row) {
        $title = trim((string)($row['seo_title'] ?? $row['name'] ?? $slug));
        if ($title === '' || isset($seenTitle[$title])) {
            $title = (string)($row['name'] ?? keywordDisplayName($slug)) . ' · ' . $slug . ' | iComply';
            $row['seo_title'] = $title;
        }
        $seenTitle[$title] = true;
        $h1 = trim((string)($row['h1'] ?? $row['name'] ?? $slug));
        if ($h1 === '' || isset($seenH1[$h1])) {
            $h1 = (string)($row['name'] ?? keywordDisplayName($slug)) . ' (' . $slug . ')';
            $row['h1'] = $h1;
        }
        $seenH1[$h1] = true;
    }
    unset($row);
    return $keywords;
}

/**
 * @param array<string, mixed> $existing
 * @param array<string, mixed> $synth
 * @return array<string, mixed>
 */
function jobTypesMergePreferExisting(array $existing, array $synth): array
{
    $out = $synth;
    foreach ($existing as $key => $val) {
        if ($val === null || $val === '' || $val === []) {
            continue;
        }
        $out[$key] = $val;
    }
    foreach (['seo_title', 'h1', 'intro', 'body', 'meta_desc', 'faq', 'focus_points', 'secondaries'] as $need) {
        if (empty($out[$need]) && !empty($synth[$need])) {
            $out[$need] = $synth[$need];
        }
    }
    if (empty($out['name'])) {
        $out['name'] = $synth['name'] ?? '';
    }
    if (empty($out['service'])) {
        $out['service'] = $synth['service'] ?? 'electrical';
    }
    if (empty($out['related'])) {
        $out['related'] = $synth['related'] ?? ($out['name'] ?? '');
    }
    return $out;
}

/**
 * @return array{blurb:string,short:string,standards:string}
 */
function jobTypesServiceMeta(string $serviceSlug): array
{
    static $meta = null;
    if ($meta === null) {
        $meta = function_exists('loadJsonData') ? loadJsonData('service-meta', []) : [];
        if (!is_array($meta)) {
            $meta = [];
        }
    }
    $fallbacks = [
        'epc' => [
            'blurb' => 'Energy Performance Certificates for lets, sales and commercial buildings.',
            'short' => 'Domestic and commercial EPCs',
            'standards' => 'RdSAP / SBEM · MEES · landlord EPC rules',
        ],
        'smoke-co-alarms' => [
            'blurb' => 'Interlinked smoke and carbon monoxide alarms for landlords, HMOs and homes.',
            'short' => 'Smoke & CO alarm supply and install',
            'standards' => 'Smoke and Carbon Monoxide Alarm (England) Regulations · BS 5839-6',
        ],
        'pat-testing' => [
            'blurb' => 'Portable appliance testing for offices, HMOs, construction sites and commercial estates.',
            'short' => 'PAT testing and appliance labelling',
            'standards' => 'IETS Code of Practice · HSE guidance',
        ],
    ];
    $row = $meta[$serviceSlug] ?? ($fallbacks[$serviceSlug] ?? []);
    $name = function_exists('getServices') ? (getServices()[$serviceSlug] ?? keywordDisplayName($serviceSlug)) : keywordDisplayName($serviceSlug);
    return [
        'blurb' => (string)($row['blurb'] ?? ('Professional ' . $name . ' across the North West.')),
        'short' => (string)($row['short'] ?? $name),
        'standards' => (string)($row['standards'] ?? 'British Standards and manufacturer guidance'),
    ];
}

function jobTypesIntent(string $slug, string $name): string
{
    $hay = strtolower($slug . ' ' . $name);
    if (preg_match('/certificate|cert\b|cp12|cp44|\beicr\b|\bepc\b|\bfra\b|logbook|compliance pack/', $hay)) {
        return 'certificate';
    }
    if (preg_match('/survey|inspection|assessment|audit|snagging/', $hay)) {
        return 'survey';
    }
    if (preg_match('/repair|fault|remedial|leak|patch|making good/', $hay)) {
        return 'repair';
    }
    if (preg_match('/service|servicing|maintenance|ppm|contract|aftercare/', $hay)) {
        return 'maintenance';
    }
    if (preg_match('/test|testing|duration test|pat\b/', $hay)) {
        return 'testing';
    }
    if (preg_match('/design|commission|cause and effect/', $hay)) {
        return 'design';
    }
    if (preg_match('/install|fitting|fit out|fit-out|conversion|replacement|upgrade|supply/', $hay)) {
        return 'install';
    }
    return 'works';
}

function jobTypesAudience(string $slug, string $name): string
{
    $hay = strtolower($slug . ' ' . $name);
    if (preg_match('/\bhmo\b/', $hay)) {
        return 'HMO operators and managing agents';
    }
    if (preg_match('/landlord|lett|tenanc|void/', $hay)) {
        return 'landlords and letting agents';
    }
    if (preg_match('/care home|hospital|nurse|nursing/', $hay)) {
        return 'care and healthcare providers';
    }
    if (preg_match('/commercial|office|warehouse|retail|factory|shop|industrial/', $hay)) {
        return 'commercial occupiers and FM teams';
    }
    if (preg_match('/domestic|homeowner|house |home /', $hay)) {
        return 'homeowners and small landlords';
    }
    return 'landlords, FM teams and commercial occupiers';
}

/**
 * @param array<string, mixed> $job
 * @param array<string, mixed> $existing
 * @return array<string, mixed>
 */
function jobTypesSynthesize(array $job, array $existing = []): array
{
    $slug = keywordSlug((string)($job['slug'] ?? $existing['slug'] ?? ''));
    $name = trim((string)($job['name'] ?? $existing['name'] ?? keywordDisplayName($slug)));
    $serviceSlug = areaSlug((string)($job['service'] ?? $existing['service'] ?? 'electrical'));
    $related = keywordSlug((string)($job['related'] ?? $existing['related'] ?? $slug));
    $services = function_exists('getServices') ? getServices() : [];
    $serviceName = (string)($services[$serviceSlug] ?? keywordDisplayName($serviceSlug));
    $category = function_exists('seoIaCategoryForService')
        ? seoIaCategoryForService($serviceSlug)
        : ['key' => 'all', 'label' => 'Services'];
    $svcMeta = jobTypesServiceMeta($serviceSlug);
    $intent = jobTypesIntent($slug, $name);
    $audience = jobTypesAudience($slug, $name);
    $variant = abs(crc32($slug)) % 4;
    $catLabel = (string)($category['label'] ?? 'Services');

    $intentVerb = [
        'certificate' => 'inspect, document and certificate',
        'survey' => 'survey, record findings and recommend remedials',
        'repair' => 'diagnose, repair and make good',
        'maintenance' => 'service, test and keep on a planned cycle',
        'testing' => 'test, record results and flag failures',
        'design' => 'design, commission and handover',
        'install' => 'specify, install and commission',
        'works' => 'scope, deliver and hand over',
    ][$intent];

    $h1Choices = [
        $name,
        $name . ' across the North West',
        $name . ' for ' . $audience,
        $serviceName . ': ' . $name,
    ];
    $h1 = $h1Choices[$variant];
    // Keep the exact job name visible and unique.
    if (!str_contains(strtolower($h1), strtolower($name))) {
        $h1 = $name;
    }

    $titleChoices = [
        $name . ' | ' . $serviceName . ' North West',
        $name . ' — ' . $svcMeta['short'] . ' | iComply',
        $name . ' | ' . $catLabel . ' | iComply North West',
        $name . ' in Greater Manchester & the North West',
    ];
    $seoTitle = $titleChoices[$variant];
    if (strlen($seoTitle) > 65) {
        $seoTitle = $name . ' | ' . $serviceName . ' | iComply';
    }

    $meta = $name . ' for ' . $audience . ' across Greater Manchester and the North West. '
        . $svcMeta['standards'] . '. Written POA quote from Stockport — no invented £ prices.';
    if (strlen($meta) > 165) {
        $meta = $name . ' across the North West. ' . $svcMeta['short'] . '. POA after scope from Stockport engineers.';
    }

    $intro = $name . ' sits under our ' . $serviceName . ' service in the ' . $catLabel
        . ' group. We ' . $intentVerb . ' for the job you actually have — not a generic package — for '
        . $audience . ' from our Stockport SK2 base.';

    $bodyBits = [
        $svcMeta['blurb'] . ' For ' . $name . ' that means a site look, a written scope and a POA figure once access, standards and any existing equipment are clear.',
        'Typical North West stock for this job includes terraces, purpose-built flats, HMOs, offices and light industrial. We say when ' . $name . ' is the right next step and when a wider ' . $serviceName . ' visit is safer.',
        'Paperwork is issued after the agreed works or inspection. Related ' . $serviceName . ' jobs, and the towns we cover, are linked on this page.',
        'Enquire with postcode, property type and any brand already on site. WhatsApp and phone are on this page — we do not publish catalogue pound figures.',
    ];
    // Rotate starting sentence so neighbouring slugs do not share the same first line.
    $rot = $variant;
    $body = $bodyBits[$rot] . ' ' . $bodyBits[($rot + 1) % 4] . ' ' . $bodyBits[($rot + 2) % 4];

    $focus = [
        'Scope confirmed against ' . $svcMeta['standards'],
        'Written POA quote after we know the property — no invented £',
        $intentVerb . ' as part of ' . $serviceName,
        'Stockport engineers covering 150+ North West towns',
    ];

    $faqs = [
        ['What does ' . $name . ' include?', 'Scope is confirmed in your quote. We typically ' . $intentVerb . ' under ' . $serviceName . ', then issue the paperwork that job actually needs.'],
        ['Do you cover my town for ' . $name . '?', 'Yes across Greater Manchester, Cheshire, Lancashire and the wider North West from Stockport. Use the area links on this page or the areas hub.'],
        ['How do you price ' . $name . '?', 'Price on application after we confirm access, standards and materials. We do not invent a catalogue £ figure on this page.'],
        ['Who is ' . $name . ' for?', $audience . ' booking through iComply. Tell us the postcode, property type and any panel, boiler or door brand already on site.'],
    ];
    // Drop one FAQ by variant so pages are not identical lists.
    unset($faqs[$variant]);
    $faqs = array_values($faqs);

    $secondaries = [];
    $bits = preg_split('/\s+/', $name) ?: [];
    if (count($bits) > 1) {
        $secondaries[] = $bits[0] . ' ' . $intent;
    }
    $secondaries[] = $serviceName . ' ' . $intent;
    $secondaries[] = $name . ' North West';

    return [
        'name' => $name,
        'service' => $serviceSlug,
        'related' => $related !== '' ? $related : $slug,
        'seo_title' => $seoTitle,
        'h1' => $h1,
        'intro' => $intro,
        'body' => $body,
        'meta_desc' => $meta,
        'seo_keywords' => $name . ', ' . $serviceName . ', ' . $catLabel . ', North West, Stockport',
        'focus_points' => $focus,
        'faq' => $faqs,
        'secondaries' => $secondaries,
        'master' => true,
    ];
}

/**
 * Towns that static-export will actually write for a keyword hub (pretty URLs).
 *
 * @return list<string> display names
 */
function jobTypeExportedAreaNames(string $keywordSlug): array
{
    $keywordSlug = keywordSlug($keywordSlug);
    $areas = function_exists('getAreas') ? getAreas() : [];
    $popular = [
        'Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale',
        'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool',
    ];
    $popular = array_values(array_filter($popular, static fn(string $t): bool => in_array($t, $areas, true)));

    $family = [];
    if (function_exists('getElectricalGasMatrixKeywordSlugs')) {
        foreach (getElectricalGasMatrixKeywordSlugs() as $slug) {
            $family[keywordSlug((string)$slug)] = true;
        }
    }
    $priority = [];
    if (function_exists('getPopularKeywordSlugs')) {
        foreach (getPopularKeywordSlugs() as $slug) {
            $priority[keywordSlug((string)$slug)] = true;
        }
    }
    $radiusOnly = [];
    if (function_exists('seoIaRadiusOnlyAreaSlugs')) {
        foreach (seoIaRadiusOnlyAreaSlugs() as $slug) {
            $radiusOnly[areaSlug((string)$slug)] = true;
        }
    }
    $radiusKw = [];
    if (function_exists('seoIaRadiusKeywordSlugs')) {
        foreach (seoIaRadiusKeywordSlugs() as $slug) {
            $radiusKw[keywordSlug((string)$slug)] = true;
        }
    }

    $fullTowns = isset($family[$keywordSlug]) || isset($priority[$keywordSlug]);
    $towns = $fullTowns ? $areas : $popular;
    $out = [];
    foreach ($towns as $area) {
        $townSlug = areaSlug((string)$area);
        if (isset($radiusOnly[$townSlug]) && !isset($radiusKw[$keywordSlug])) {
            continue;
        }
        $out[] = (string)$area;
    }
    return $out;
}

function jobTypeAreaComboExported(string $keywordSlug, string $areaNameOrSlug): bool
{
    $want = areaSlug($areaNameOrSlug);
    foreach (jobTypeExportedAreaNames($keywordSlug) as $name) {
        if (areaSlug($name) === $want) {
            return true;
        }
    }
    return false;
}
