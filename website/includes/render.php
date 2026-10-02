<?php
/**
 * Runtime page renderer — single source of truth for combo / keyword / area hubs.
 *
 * All page templates use executeTemplateVars() (extract + include, no eval).
 * Legacy executeTemplate() remains only for emergency/debug use with old {{}} files.
 * Templates must only be controlled site files — never user-supplied content.
 */
require_once __DIR__ . '/../config.php';

/**
 * Apply {{KEY}} replacements (values must already be safe for their context).
 */
function applyTemplatePlaceholders(string $template, array $map): string {
    $keys = [];
    $vals = [];
    foreach ($map as $k => $v) {
        $keys[] = '{{' . $k . '}}';
        $vals[] = (string)$v;
    }
    return str_replace($keys, $vals, $template);
}

/**
 * Safer path: extract placeholder keys as local variables and include the template.
 * Template should use $KEYWORD_NAME, $AREA, etc. — not {{PLACEHOLDER}} tokens.
 * Keys must be valid PHP variable names (A-Z0-9_).
 */
function executeTemplateVars(string $absolutePath, array $placeholders): void {
    if (!is_file($absolutePath)) {
        http_response_code(500);
        echo 'Template missing: ' . htmlspecialchars(basename($absolutePath));
        icomplyRequestExit();
        return;
    }
    // Only extract string keys that form valid variable names
    $vars = [];
    foreach ($placeholders as $k => $v) {
        if (is_string($k) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $k)) {
            $vars[$k] = $v;
        }
    }
    extract($vars, EXTR_SKIP);
    include $absolutePath;
}

/**
 * Legacy: load template, substitute {{PLACEHOLDERS}}, execute via eval.
 * Controlled site templates only — not user input.
 * Prefer executeTemplateVars() for new / converted templates.
 */
function executeTemplate(string $absolutePath, array $placeholders): void {
    if (!is_file($absolutePath)) {
        http_response_code(500);
        echo 'Template missing: ' . htmlspecialchars(basename($absolutePath));
        icomplyRequestExit();
        return;
    }
    $code = applyTemplatePlaceholders((string)file_get_contents($absolutePath), $placeholders);
    // Controlled site templates only — not user input.
    eval('?>' . $code);
}

/** Single data-driven service × area template (service-meta + manufacturers). */
function comboTemplatePath(string $serviceSlug = ''): string {
    return SITE_ROOT . '/templates/combo.php';
}

/**
 * Service × area landing page.
 */
function renderServiceAreaPage(string $serviceSlug, string $area): void {
    $services = getServices();
    if (!isset($services[$serviceSlug])) {
        http_response_code(404);
        echo 'Service not found';
        icomplyRequestExit();
        return;
    }
    $serviceName = $services[$serviceSlug];
    $areaSlug = areaSlug($area);
    $imgs = getKeywordImages($serviceSlug);

    // Ensure globals templates may still read
    $GLOBALS['services'] = $services;
    $GLOBALS['areas'] = getAreas();

    executeTemplateVars(comboTemplatePath($serviceSlug), [
        'SERVICE_NAME' => $serviceName,
        'SERVICE_SLUG' => $serviceSlug,
        'AREA' => $area,
        'AREA_SLUG' => $areaSlug,
        'SEO_KEYWORDS' => getSeoKeywords($serviceSlug, $area),
        'MANUFACTURER_TAGS' => manufacturerTagsHtml($serviceSlug),
        'MANUFACTURER_IMAGES' => manufacturerImagesHtml($serviceSlug),
        'KEYWORD_IMAGE_1' => $imgs[0] ?? $serviceSlug,
        'KEYWORD_IMAGE_2' => $imgs[1] ?? $serviceSlug,
        'KEYWORD_IMAGE_3' => $imgs[2] ?? $serviceSlug,
    ]);
}

/**
 * Keyword guide page.
 */
function renderKeywordPage(string $slug): void {
    $keywords = getMajorKeywords();
    $slug = keywordSlug($slug);
    if (!isset($keywords[$slug])) {
        http_response_code(404);
        echo 'Keyword not found';
        icomplyRequestExit();
        return;
    }
    $meta = $keywords[$slug];
    $services = getServices();
    $serviceSlug = $meta['service'] ?? 'electrical';
    $relatedSlug = keywordSlug($meta['related'] ?? $slug);
    $relatedName = $keywords[$relatedSlug]['name'] ?? keywordDisplayName($relatedSlug);
    $serviceName = $services[$serviceSlug] ?? keywordDisplayName($serviceSlug);

    $GLOBALS['services'] = $services;
    $GLOBALS['areas'] = getAreas();

    executeTemplateVars(SITE_ROOT . '/templates/keyword.php', keywordTemplatePlaceholders($meta, $slug, $serviceSlug, $serviceName, $relatedSlug, $relatedName));
}

/**
 * Build shared placeholders for keyword + keyword×area templates.
 */
function keywordTemplatePlaceholders(
    array $meta,
    string $slug,
    string $serviceSlug,
    string $serviceName,
    string $relatedSlug,
    string $relatedName,
    string $areaName = ''
): array {
    $name = $meta['name'] ?? keywordDisplayName($slug);
    $h1 = (string)($meta['h1'] ?? $name);
    $seoTitle = (string)($meta['seo_title'] ?? ($name . ' | ' . $serviceName . ' North West'));
    $intro = (string)($meta['intro'] ?? "Professional {$name} from Icomply Property Services across the North West.");
    $body = (string)($meta['body'] ?? "We install, service and certify {$name} as part of our {$serviceName} range for landlords, FM teams and commercial sites.");
    $metaDesc = (string)($meta['meta_desc'] ?? "{$name} across Greater Manchester & the North West. Fixed-price quotes. Local engineers.");
    $seoKw = (string)($meta['seo_keywords'] ?? getSeoKeywords($serviceSlug, $areaName));
    $category = function_exists('seoIaCategoryForService')
        ? seoIaCategoryForService($serviceSlug)
        : ['key' => 'all', 'label' => 'Services'];

    $focusHtml = '';
    $points = $meta['focus_points'] ?? [
        "Survey and fixed-price quote for {$name}",
        'Local North West engineers from Stockport',
        'Testing and compliance documentation',
        "Manufacturer-aware {$serviceName} support",
    ];
    foreach ($points as $p) {
        $focusHtml .= '<li class="flex gap-2 text-zinc-900 font-medium"><span class="text-[#ff6b00] font-bold">●</span><span>'
            . htmlspecialchars((string)$p, ENT_QUOTES, 'UTF-8') . '</span></li>';
    }

    $faqHtml = '';
    $faqs = $meta['faq'] ?? [];
    if (!$faqs) {
        $faqs = [
            ["What does {$name} include?", "Scope is confirmed in your quote — typically labour, agreed materials, and certification where required."],
            ["Do you cover the North West for {$name}?", 'Yes — 150+ towns from our Stockport base including Manchester, Bolton, Liverpool and Preston.'],
        ];
    }
    foreach ($faqs as $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $q = htmlspecialchars((string)$faq[0], ENT_QUOTES, 'UTF-8');
        $a = htmlspecialchars((string)$faq[1], ENT_QUOTES, 'UTF-8');
        $faqHtml .= '<details class="bg-white border-2 border-zinc-300 rounded-2xl p-5 group">'
            . '<summary class="font-bold text-[#061828] cursor-pointer list-none flex justify-between gap-3">'
            . $q . '<span class="text-[#ff6b00] text-xl leading-none">+</span></summary>'
            . '<p class="mt-3 text-sm text-zinc-900 leading-relaxed font-medium">' . $a . '</p></details>';
    }

    $kwImgRel = '/assets/images/keywords/' . $slug . '.jpg';
    $kwImg = is_file(SITE_ROOT . $kwImgRel) ? url($kwImgRel) : (function_exists('serviceImageUrl') ? serviceImageUrl($serviceSlug) : url('/assets/images/og-default.svg'));
    $svcImg = function_exists('serviceImageUrl') ? serviceImageUrl($serviceSlug) : url('/assets/images/services/' . $serviceSlug . '.jpg');
    if (!is_file(SITE_ROOT . '/assets/images/services/' . $serviceSlug . '.jpg')
        && !is_file(SITE_ROOT . '/assets/images/services/' . $serviceSlug . '.png')
        && is_file(SITE_ROOT . '/assets/images/og-default.svg')) {
        $svcImg = url('/assets/images/og-default.svg');
        if ($kwImg === url($kwImgRel) || str_contains($kwImg, '/assets/images/services/')) {
            $kwImg = $svcImg;
        }
    }

    $jobMfr = $meta['manufacturers'] ?? [];
    $extraMfr = $meta['extra_manufacturer_names'] ?? [];
    $mfrTags = (function_exists('seoIaManufacturerTagsHtml') && ($jobMfr || $extraMfr))
        ? seoIaManufacturerTagsHtml($serviceSlug, is_array($jobMfr) ? $jobMfr : [], is_array($extraMfr) ? $extraMfr : [])
        : manufacturerTagsHtml($serviceSlug);

    $secondaries = $meta['secondaries'] ?? [];
    $secondaryHtml = '';
    if (is_array($secondaries) && $secondaries) {
        $secondaryHtml .= '<div class="mt-8">';
        $secondaryHtml .= '<h2 class="text-2xl md:text-3xl font-bold text-[#061828] tracking-tight">Also searched as</h2>';
        $secondaryHtml .= '<p class="mt-3 text-base text-zinc-900 leading-relaxed">People looking for '
            . htmlspecialchars($h1, ENT_QUOTES, 'UTF-8')
            . ' also ask about ';
        $bits = [];
        foreach ($secondaries as $sec) {
            $bits[] = htmlspecialchars((string)$sec, ENT_QUOTES, 'UTF-8');
        }
        $secondaryHtml .= implode(', ', $bits) . '. Those topics sit under our '
            . htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8')
            . ' service and the FAQs below.</p></div>';
    }

    $faqLd = function_exists('seoIaFaqPageJsonLd') ? seoIaFaqPageJsonLd($faqs) : null;
    $faqJson = $faqLd ? json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';

    return [
        'KEYWORD_NAME' => $name,
        'KEYWORD_H1' => $h1,
        'KEYWORD_SEO_TITLE' => $seoTitle,
        'KEYWORD_SLUG' => $slug,
        'SERVICE_NAME' => $serviceName,
        'SERVICE_SLUG' => $serviceSlug,
        'CATEGORY_KEY' => $category['key'],
        'CATEGORY_LABEL' => $category['label'],
        'RELATED_SLUG' => $relatedSlug,
        'RELATED_NAME' => $relatedName,
        'MANUFACTURER_TAGS' => $mfrTags,
        'KEYWORD_SECONDARY_HTML' => $secondaryHtml,
        'KEYWORD_FAQ_JSON' => $faqJson,
        'SEO_KEYWORDS' => $seoKw,
        'KEYWORD_INTRO' => $intro,
        'KEYWORD_BODY' => $body,
        'KEYWORD_META' => $metaDesc,
        'KEYWORD_FOCUS_HTML' => $focusHtml,
        'KEYWORD_FAQ_HTML' => $faqHtml,
        'KEYWORD_IMAGE' => $kwImg,
        'SERVICE_IMAGE' => $svcImg,
        'KEYWORD_POA_ENQUIRE' => !empty($meta['sec_water_plumb']),
        'KEYWORD_SIBLING_LIMIT' => !empty($meta['sec_water_plumb']) ? 8 : 0,
    ];
}

/**
 * Keyword × area landing page (e.g. EICR Report in Stockport).
 */
function renderKeywordAreaPage(string $keywordSlug, string $area): void {
    $keywords = getMajorKeywords();
    $keywordSlug = keywordSlug($keywordSlug);
    if (!isset($keywords[$keywordSlug])) {
        http_response_code(404);
        echo 'Keyword not found';
        icomplyRequestExit();
        return;
    }
    $areas = getAreas();
    // Accept display name or slug for area
    $areaName = $area;
    $areaSlugVal = areaSlug($area);
    $found = false;
    foreach ($areas as $a) {
        if (areaSlug($a) === $areaSlugVal || strcasecmp($a, $area) === 0) {
            $areaName = $a;
            $areaSlugVal = areaSlug($a);
            $found = true;
            break;
        }
    }
    if (!$found) {
        // Still allow if slug-like string
        $areaName = keywordDisplayName($areaSlugVal);
    }

    $meta = $keywords[$keywordSlug];
    $services = getServices();
    $serviceSlug = $meta['service'] ?? 'electrical';
    $serviceName = $services[$serviceSlug] ?? keywordDisplayName($serviceSlug);
    $relatedSlug = keywordSlug($meta['related'] ?? $keywordSlug);
    $relatedName = $keywords[$relatedSlug]['name'] ?? keywordDisplayName($relatedSlug);

    $GLOBALS['services'] = $services;
    $GLOBALS['areas'] = $areas;

    $ph = keywordTemplatePlaceholders($meta, $keywordSlug, $serviceSlug, $serviceName, $relatedSlug, $relatedName, $areaName);
    $ph['AREA'] = $areaName;
    $ph['AREA_SLUG'] = $areaSlugVal;
    $ph['AREA_URL'] = rawurlencode($areaName);
    // Localise meta for area pages
    $ph['KEYWORD_META'] = $meta['meta_desc'] ?? $ph['KEYWORD_META'];
    $ph['KEYWORD_BODY'] = rtrim($ph['KEYWORD_BODY'], '.')
        . '. Our engineers regularly attend jobs in ' . $areaName
        . ' and surrounding postcodes for ' . ($meta['name'] ?? $keywordSlug) . '.';

    // Pure-PHP template (no {{}} / eval)
    executeTemplateVars(SITE_ROOT . '/templates/keyword-area.php', $ph);
}

/**
 * Area hub page listing all services for a town.
 */
function renderAreaHubPage(string $area): void {
    $GLOBALS['services'] = getServices();
    $GLOBALS['areas'] = getAreas();
    $areaSlug = areaSlug($area);
    $overlay = function_exists('seoIaAreaOverlay') ? seoIaAreaOverlay($area) : null;

    $faqs = [
        ['What compliance work do you do in ' . $area . '?', 'EICR, fire risk assessment, gas safety / CP12, fire alarms, emergency lighting, PAT, EPC and related landlord certificates — scoped after we know the property in ' . $area . '.'],
        ['Do you cover postcodes around ' . $area . '?', 'Yes. Engineers travel from Stockport SK2. Nearby towns are linked on this hub. Quotes are POA after scope — we do not invent pound prices.'],
        ['How do I book in ' . $area . '?', 'Use the quote form, WhatsApp or phone. Tell us the job type, postcode and any panel or boiler brand already on site.'],
    ];
    $faqHtml = '';
    foreach ($faqs as $faq) {
        $faqHtml .= '<details class="bg-white border border-zinc-200 rounded-2xl p-5">'
            . '<summary class="font-semibold cursor-pointer">' . htmlspecialchars($faq[0], ENT_QUOTES, 'UTF-8') . '</summary>'
            . '<p class="mt-3 text-sm text-zinc-600">' . htmlspecialchars($faq[1], ENT_QUOTES, 'UTF-8') . '</p></details>';
    }
    $faqLd = function_exists('seoIaFaqPageJsonLd') ? seoIaFaqPageJsonLd($faqs) : null;

    executeTemplateVars(SITE_ROOT . '/templates/area.php', [
        'AREA' => $area,
        'AREA_SLUG' => $areaSlug,
        'AREA_URL' => rawurlencode($area),
        'AREA_SEO_TITLE' => (string)($overlay['seo_title'] ?? ($area . ' Property Compliance Services')),
        'AREA_H1' => (string)($overlay['h1'] ?? ('Property compliance in ' . $area)),
        'AREA_META' => (string)($overlay['meta_desc'] ?? ''),
        'AREA_LEDE' => (string)($overlay['lede'] ?? ''),
        'AREA_FAQ_HTML' => $faqHtml,
        'AREA_FAQ_JSON' => $faqLd ? json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '',
        'SERVICE_NAME' => 'Compliance',
    ]);
}

/**
 * Top-level service hub (pages/services/{slug}.php).
 */
function renderServiceHubPage(string $serviceSlug): void {
    $services = getServices();
    if (!isset($services[$serviceSlug])) {
        http_response_code(404);
        echo 'Service not found';
        icomplyRequestExit();
        return;
    }
    $GLOBALS['services'] = $services;
    $GLOBALS['areas'] = getAreas();

    $tpl = SITE_ROOT . '/templates/service.php';
    executeTemplateVars($tpl, [
        'SERVICE_NAME' => $services[$serviceSlug],
        'SERVICE_SLUG' => $serviceSlug,
        'SEO_KEYWORDS' => getSeoKeywords($serviceSlug),
        'MANUFACTURER_TAGS' => manufacturerTagsHtml($serviceSlug),
        'MANUFACTURER_IMAGES' => manufacturerImagesHtml($serviceSlug),
    ]);
}

/**
 * Manufacturer brand / product hub (pages/manufacturers/{slug}.php).
 */
function renderManufacturerPage(string $mfrSlug): void {
    $entry = getManufacturerBySlug($mfrSlug);
    if (!$entry) {
        http_response_code(404);
        echo 'Manufacturer not found';
        icomplyRequestExit();
        return;
    }
    $services = getServices();
    $GLOBALS['services'] = $services;
    $GLOBALS['areas'] = getAreas();

    $primary = $entry['services'][0] ?? 'fire-alarms';
    $fallbackImg = htmlspecialchars(url('/assets/images/services/' . $primary . '.jpg'), ENT_QUOTES, 'UTF-8');

    // Services chips
    $servicesHtml = '';
    foreach ($entry['services'] as $sSlug) {
        $sName = $services[$sSlug] ?? $sSlug;
        $servicesHtml .= '<a href="' . htmlspecialchars(url('/pages/services/' . $sSlug . '.php'), ENT_QUOTES, 'UTF-8') . '" '
            . 'class="px-4 py-2 bg-zinc-50 border rounded-full text-sm font-medium hover:border-[#ff6b00]">'
            . htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') . '</a>';
    }

    // Product cards (Shopify-ready via shared card helper)
    require_once SITE_ROOT . '/includes/shopify.php';
    $productsHtml = '';
    $products = $entry['products'] ?? [];
    if (!$products) {
        $productsHtml = '<p class="text-zinc-600 col-span-full">Trade kits for ' . htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') . ' are quoted POA / enquire — we do not invent pound prices. <a class="text-[#ff6b00] font-semibold" href="' . htmlspecialchars(url('/shop/index.php'), ENT_QUOTES, 'UTF-8') . '">Browse the shop</a> or ask for a written quote.</p>';
    } else {
        foreach ($products as $p) {
            if (function_exists('seoIaIsWave1Manufacturer') && seoIaIsWave1Manufacturer($entry['slug'])) {
                $p['price'] = function_exists('seoIaPoaPrice') ? seoIaPoaPrice((string)($p['price'] ?? '')) : 'POA / enquire';
            }
            $productsHtml .= shopifyCardFromManufacturerProduct($p, $entry['slug'], $entry['name']);
        }
    }

    $jobsHtml = function_exists('seoIaManufacturerJobsHtml') ? seoIaManufacturerJobsHtml($entry['slug']) : '';
    $linesHtml = '';
    foreach (($entry['lines'] ?? []) as $line) {
        $linesHtml .= '<li class="flex gap-2"><span class="text-[#ff6b00]">●</span><span>'
            . htmlspecialchars((string)$line, ENT_QUOTES, 'UTF-8') . '</span></li>';
    }

    // Related manufacturers (same primary service)
    $relatedHtml = '';
    $catalog = getManufacturerCatalog();
    $count = 0;
    foreach ($catalog as $slug => $other) {
        if ($slug === $entry['slug']) {
            continue;
        }
        $otherServices = $other['services'] ?? [];
        if (!array_intersect($entry['services'], $otherServices)) {
            continue;
        }
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($other['name'], ENT_QUOTES, 'UTF-8');
        $relatedHtml .= '<a href="' . $href . '" class="p-4 bg-zinc-50 border rounded-2xl hover:border-[#ff6b00] transition">'
            . '<div class="font-semibold text-black">' . $name . '</div>'
            . '<div class="text-xs text-[#ff6b00] mt-1">View brand →</div></a>';
        if (++$count >= 8) {
            break;
        }
    }

    executeTemplateVars(SITE_ROOT . '/templates/manufacturer.php', [
        'MFR_NAME' => $entry['name'],
        'MFR_SLUG' => $entry['slug'],
        'MFR_BLURB' => $entry['blurb'] ?? '',
        'MFR_SEO_TITLE' => $entry['seo_title'] ?? ($entry['name'] . ' Products & Service'),
        'MFR_SEO_KEYWORDS' => $entry['seo_keywords'] ?? $entry['name'],
        'MFR_SERVICES_HTML' => $servicesHtml,
        'MFR_PRODUCTS_HTML' => $productsHtml,
        'MFR_RELATED_HTML' => $relatedHtml,
        'MFR_JOBS_HTML' => $jobsHtml,
        'MFR_LINES_HTML' => $linesHtml,
        'MFR_IMAGE_HTML' => function_exists('seoIaManufacturerImageHtml')
            ? seoIaManufacturerImageHtml($entry['slug'], $entry['name'], $primary)
            : '',
        'SERVICE_NAME' => $services[$primary] ?? 'Compliance',
    ]);
}
