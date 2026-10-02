<?php
/**
 * Emergency Lighting job lane — 62 slugs under /pages/keywords/<slug>.
 *
 * Source of truth: website/data/emergency-lighting-job-types.json
 * (emitted by website/bin/build-emergency-lighting-job-pages.php from
 * keywords.json rows whose service is emergency-lighting).
 *
 * Near-me doorway slugs are excluded. Copy stays enquire / POA — no £ prices.
 */
declare(strict_types=1);

function emergencyLightingJobTypesExpectedCount(): int
{
    return 62;
}

function emergencyLightingJobTypesFile(): string
{
    return SITE_ROOT . '/data/emergency-lighting-job-types.json';
}

function emergencyLightingJobTypesOutputDir(): string
{
    return SITE_ROOT . '/pages/keywords';
}

function emergencyLightingJobTypesStubPath(string $slug): string
{
    return emergencyLightingJobTypesOutputDir() . '/' . keywordSlug($slug) . '.php';
}

function emergencyLightingJobTypesReset(): void
{
    emergencyLightingJobTypesData(true);
}

/** @return array{count?:int,category?:string,jobs?:list<array<string,mixed>>} */
function emergencyLightingJobTypesData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = emergencyLightingJobTypesFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function emergencyLightingJobTypesJobs(): array
{
    $jobs = emergencyLightingJobTypesData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function emergencyLightingJobTypesSlugs(): array
{
    $out = [];
    foreach (emergencyLightingJobTypesJobs() as $job) {
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

function emergencyLightingJobIsExcluded(string $slug): bool
{
    $slug = keywordSlug($slug);
    if ($slug === '') {
        return true;
    }
    // Directory doorway pages are not distinct emergency lighting jobs.
    return str_contains($slug, 'near-me');
}

/**
 * Rows for the catalogue, taken from keywords.json (not the overlay).
 *
 * @return list<array{category:string,service_type:string,slug:string,status:string,name:string,related:string}>
 */
function emergencyLightingCollectJobs(): array
{
    $raw = loadJsonData('keywords', []);
    $out = [];
    foreach ($raw as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        if (areaSlug((string)($meta['service'] ?? '')) !== 'emergency-lighting') {
            continue;
        }
        $slug = keywordSlug((string)$slug);
        if (emergencyLightingJobIsExcluded($slug)) {
            continue;
        }
        $name = trim((string)($meta['name'] ?? ''));
        if ($name === '') {
            $name = keywordDisplayName($slug);
        }
        $related = keywordSlug((string)($meta['related'] ?? $slug));
        if ($related === '' || emergencyLightingJobIsExcluded($related)) {
            $related = 'emergency-lighting-testing';
        }
        $out[$slug] = [
            'category' => 'Emergency Lighting',
            'service_type' => 'emergency-lighting',
            'slug' => $slug,
            'status' => 'live',
            'name' => $name,
            'related' => $related,
        ];
    }
    ksort($out);
    return array_values($out);
}

/**
 * Merge the lane into the live keyword catalogue. Existing intro, FAQ and
 * meta win; missing fields get a POA-only fallback that names the job.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function emergencyLightingJobsApplyOverlay(array $keywords): array
{
    foreach (emergencyLightingJobTypesJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '' || emergencyLightingJobIsExcluded($slug)) {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $name = trim((string)($base['name'] ?? $job['name'] ?? keywordDisplayName($slug)));
        $row = $base;
        $row['name'] = $name;
        $row['service'] = 'emergency-lighting';
        $related = keywordSlug((string)($base['related'] ?? $job['related'] ?? 'emergency-lighting-testing'));
        if ($related === '' || emergencyLightingJobIsExcluded($related)) {
            $related = 'emergency-lighting-testing';
        }
        $row['related'] = $related;
        $row['category'] = 'Emergency Lighting';
        $row['status'] = (string)($job['status'] ?? 'live');
        $row['el_master'] = true;
        if (empty($row['h1'])) {
            $row['h1'] = $name;
        }
        if (empty($row['seo_title'])) {
            $row['seo_title'] = $name . ' | Emergency Lighting | North West';
        }
        $synth = emergencyLightingSynthesizeCopy($name);
        foreach (['intro', 'body', 'meta_desc', 'focus_points', 'faq', 'seo_keywords'] as $field) {
            if (empty($row[$field])) {
                $row[$field] = $synth[$field];
            }
        }
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * POA-only fallback. Used only when a catalogue row has no authored copy.
 *
 * @return array{intro:string,body:string,meta_desc:string,focus_points:list<string>,faq:list<array{0:string,1:string}>,seo_keywords:string}
 */
function emergencyLightingSynthesizeCopy(string $name): array
{
    return [
        'intro' => $name . ' from Icomply Property Services for landlords, facilities teams and commercial sites across Greater Manchester and the North West. Scope is confirmed on survey. Enquire for a POA quote — we do not publish a fixed £ figure before the site is seen.',
        'body' => 'Work follows BS 5266 and BS EN 1838: escape-route, open-area and high-risk task lighting, maintained and non-maintained fittings, central battery and self-test systems. Engineers work from our Stockport base. Handover includes the logbook notes and certificates the visit actually produced.',
        'meta_desc' => $name . ' across the North West. BS 5266 testing, installation and certification. Enquire for a POA quote from Icomply in Stockport.',
        'focus_points' => [
            'Survey before a POA quote for ' . $name,
            'BS 5266 monthly function and annual duration testing where the visit requires it',
            'Logbook and certificate paperwork for the work completed',
            'Stockport engineers covering Greater Manchester and the North West',
        ],
        'faq' => [
            [
                'How is ' . $name . ' quoted?',
                'We survey the site and send a POA quote. The price depends on fitting count, access and whether remedials are included. Nothing is invented up front.',
            ],
            [
                'Which standard applies to ' . $name . '?',
                'Emergency lighting design, installation and testing follow BS 5266 and BS EN 1838, plus the manufacturer’s test method for the fittings on site.',
            ],
            [
                'Do you cover the North West for ' . $name . '?',
                'Yes. Engineers attend from Stockport across Greater Manchester, Cheshire, Lancashire and Merseyside.',
            ],
        ],
        'seo_keywords' => $name . ', emergency lighting, BS 5266, North West, Stockport, Manchester',
    ];
}

/**
 * Lane job page. Category → service → job breadcrumbs, FAQPage JSON-LD,
 * navy #0B1F3A and orange #ff6b00, enquire / POA only.
 *
 * @param array<string, mixed> $meta
 */
function emergencyLightingRenderJobPage(
    string $slug,
    array $meta,
    string $serviceSlug,
    string $serviceName,
    string $relatedSlug,
    string $relatedName
): void {
    $slug = keywordSlug($slug);
    $name = (string)($meta['name'] ?? keywordDisplayName($slug));
    $faqs = [];
    foreach ($meta['faq'] ?? [] as $faq) {
        if (is_array($faq) && count($faq) >= 2) {
            $faqs[] = [(string)$faq[0], (string)$faq[1]];
        }
    }
    if (!$faqs) {
        $faqs = emergencyLightingSynthesizeCopy($name)['faq'];
    }

    $points = $meta['focus_points'] ?? emergencyLightingSynthesizeCopy($name)['focus_points'];
    $focusHtml = '';
    foreach ($points as $point) {
        $focusHtml .= '<li class="flex gap-2 text-zinc-900 font-medium"><span class="text-[#ff6b00] font-bold">●</span><span>'
            . htmlspecialchars((string)$point, ENT_QUOTES, 'UTF-8') . '</span></li>';
    }

    $faqHtml = '';
    $faqEntities = [];
    foreach ($faqs as $faq) {
        $q = (string)$faq[0];
        $a = (string)$faq[1];
        $faqHtml .= '<details class="bg-white border-2 border-zinc-300 rounded-2xl p-5 group">'
            . '<summary class="font-bold text-[#0B1F3A] cursor-pointer list-none flex justify-between gap-3">'
            . htmlspecialchars($q, ENT_QUOTES, 'UTF-8')
            . '<span class="text-[#ff6b00] text-xl leading-none">+</span></summary>'
            . '<p class="mt-3 text-sm text-zinc-900 leading-relaxed font-medium">'
            . htmlspecialchars($a, ENT_QUOTES, 'UTF-8') . '</p></details>';
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }

    $canonical = url('/pages/keywords/' . $slug . '.php');
    $categoryUrl = url('/pages/emergency-lighting-jobs.php');
    $serviceUrl = url('/pages/services/' . $serviceSlug . '.php');
    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                'name' => $name,
                'serviceType' => $serviceName,
                'description' => (string)($meta['meta_desc'] ?? ''),
                'url' => $canonical,
                'provider' => [
                    '@type' => 'LocalBusiness',
                    'name' => SITE_NAME,
                    'telephone' => PHONE,
                    'url' => SITE_URL,
                ],
                'areaServed' => 'North West England',
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Emergency Lighting', 'item' => $categoryUrl],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceName, 'item' => $serviceUrl],
                    ['@type' => 'ListItem', 'position' => 4, 'name' => $name, 'item' => $canonical],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities,
            ],
        ],
    ];

    $siblings = '';
    foreach (emergencyLightingJobTypesJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $sib = keywordSlug((string)($job['slug'] ?? ''));
        if ($sib === '' || $sib === $slug) {
            continue;
        }
        $sibName = (string)($job['name'] ?? keywordDisplayName($sib));
        $siblings .= '<a href="' . htmlspecialchars(url('/pages/keywords/' . $sib . '.php'), ENT_QUOTES, 'UTF-8') . '"'
            . ' class="px-3 py-1.5 bg-white border-2 border-zinc-300 rounded-full text-xs font-semibold text-zinc-900 hover:border-[#ff6b00] hover:text-[#ff6b00]">'
            . htmlspecialchars($sibName, ENT_QUOTES, 'UTF-8') . '</a>';
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    $GLOBALS['services'] = getServices();
    $GLOBALS['areas'] = getAreas();

    executeTemplateVars(SITE_ROOT . '/templates/emergency-lighting-job.php', [
        'EL_JSON_LD' => json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        'EL_PAGE_TITLE' => (string)($meta['seo_title'] ?? ($name . ' | Emergency Lighting | North West')),
        'EL_META' => (string)($meta['meta_desc'] ?? ''),
        'EL_SEO_KEYWORDS' => (string)($meta['seo_keywords'] ?? getSeoKeywords($serviceSlug)),
        'EL_CANONICAL' => $canonical,
        'EL_CATEGORY_URL' => $categoryUrl,
        'EL_SERVICE_URL' => $serviceUrl,
        'EL_NAME' => $name,
        'EL_SLUG' => $slug,
        'EL_INTRO' => (string)($meta['intro'] ?? ''),
        'EL_BODY' => (string)($meta['body'] ?? ''),
        'EL_FOCUS_HTML' => $focusHtml,
        'EL_FAQ_HTML' => $faqHtml,
        'EL_SIBLINGS_HTML' => $siblings,
        'EL_SERVICE_NAME' => $serviceName,
        'EL_SERVICE_SLUG' => $serviceSlug,
        'EL_RELATED_SLUG' => $relatedSlug,
        'EL_RELATED_NAME' => $relatedName,
        'EL_IMAGE' => url('/assets/images/keywords/' . $slug . '.jpg'),
        'EL_SERVICE_IMAGE' => url('/assets/images/services/' . $serviceSlug . '.jpg'),
        'EL_MANUFACTURER_TAGS' => function_exists('manufacturerTagsHtml') ? manufacturerTagsHtml($serviceSlug) : '',
        'EL_CSRF' => (string)$_SESSION['csrf'],
        'EL_PHONE' => PHONE,
        'EL_WHATSAPP' => WHATSAPP,
    ]);
}

function renderEmergencyLightingJobsHub(): void
{
    $jobs = emergencyLightingJobTypesJobs();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    $GLOBALS['services'] = getServices();
    $GLOBALS['areas'] = getAreas();

    $cards = '';
    foreach ($jobs as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        $name = (string)($job['name'] ?? keywordDisplayName($slug));
        if ($slug === '') {
            continue;
        }
        $cards .= '<a href="' . htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8') . '"'
            . ' class="block bg-white border-2 border-zinc-200 rounded-2xl p-4 hover:border-[#ff6b00]">'
            . '<span class="text-xs font-bold uppercase tracking-widest text-[#ff6b00]">Emergency Lighting</span>'
            . '<span class="mt-1 block font-bold text-[#0B1F3A]">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>'
            . '</a>';
    }

    executeTemplateVars(SITE_ROOT . '/templates/emergency-lighting-jobs-hub.php', [
        'EL_HUB_COUNT' => (string)count($jobs),
        'EL_HUB_CARDS' => $cards,
        'EL_CSRF' => (string)$_SESSION['csrf'],
        'EL_PHONE' => PHONE,
        'EL_CANONICAL' => url('/pages/emergency-lighting-jobs.php'),
    ]);
}
