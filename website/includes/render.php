<?php
/**
 * Runtime page renderer — single source of truth for combo / keyword / area hubs.
 *
 * All page templates use executeTemplateVars() (extract + include, no eval).
 * Legacy executeTemplate() remains only for emergency/debug use with old {{}} files.
 * Templates must only be controlled site files — never user-supplied content.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/seo.php';

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
    if ($serviceSlug === 'aov-air-handling') {
        require_once SITE_ROOT . '/includes/aov.php';
        require_once SITE_ROOT . '/includes/aov-place.php';
        $slug = areaSlug($area);
        if (aovPlace($slug)) {
            header('Location: ' . url('/pages/aov/' . $slug), true, 301);
        } else {
            header('Location: ' . url('/pages/services/aov-air-handling.php'), true, 301);
        }
        icomplyRequestExit();
        return;
    }
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
    $intro = scrub_unverified_accreditation((string)($meta['intro'] ?? "{$name} from Icomply Property Services, booked from Stockport SK2."));
    $body = scrub_unverified_accreditation((string)($meta['body'] ?? "We install, service and document {$name} as part of our {$serviceName} range for landlords, FM teams and commercial sites."));
    $metaDesc = scrub_unverified_accreditation((string)($meta['meta_desc'] ?? "{$name} across Greater Manchester and the North West. Written quote after scope. Stockport SK2."));
    $seoKw = (string)($meta['seo_keywords'] ?? getSeoKeywords($serviceSlug, $areaName));

    $focusHtml = '';
    $points = $meta['focus_points'] ?? [
        "Survey and written quote for {$name}",
        'Engineers booked from Stockport SK2',
        'Testing notes and compliance paperwork',
        "Manufacturer-aware {$serviceName} support",
    ];
    foreach ($points as $p) {
        $focusHtml .= '<li class="flex gap-2 text-zinc-900 font-medium"><span class="text-[#ff6b00] font-bold">●</span><span>'
            . htmlspecialchars(scrub_unverified_accreditation((string)$p), ENT_QUOTES, 'UTF-8') . '</span></li>';
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
        $q = htmlspecialchars(scrub_unverified_accreditation((string)$faq[0]), ENT_QUOTES, 'UTF-8');
        $a = htmlspecialchars(scrub_unverified_accreditation((string)$faq[1]), ENT_QUOTES, 'UTF-8');
        $faqHtml .= '<details class="bg-white border-2 border-zinc-300 rounded-2xl p-5 group">'
            . '<summary class="font-bold text-[#061828] cursor-pointer list-none flex justify-between gap-3">'
            . $q . '<span class="text-[#ff6b00] text-xl leading-none">+</span></summary>'
            . '<p class="mt-3 text-sm text-zinc-900 leading-relaxed font-medium">' . $a . '</p></details>';
    }

    $kwImg = keywordImageUrl($slug, $serviceSlug);
    $svcImg = serviceImageUrl($serviceSlug);

    return [
        'KEYWORD_NAME' => $name,
        'KEYWORD_SLUG' => $slug,
        'SERVICE_NAME' => $serviceName,
        'SERVICE_SLUG' => $serviceSlug,
        'RELATED_SLUG' => $relatedSlug,
        'RELATED_NAME' => $relatedName,
        'MANUFACTURER_TAGS' => manufacturerTagsHtml($serviceSlug),
        'SEO_KEYWORDS' => $seoKw,
        'KEYWORD_INTRO' => $intro,
        'KEYWORD_BODY' => $body,
        'KEYWORD_META' => $metaDesc,
        'KEYWORD_FOCUS_HTML' => $focusHtml,
        'KEYWORD_FAQ_HTML' => $faqHtml,
        'KEYWORD_IMAGE' => $kwImg,
        'SERVICE_IMAGE' => $svcImg,
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
    if ($serviceSlug === 'aov-air-handling') {
        header('Location: ' . url('/pages/keywords/' . $keywordSlug), true, 301);
        icomplyRequestExit();
        return;
    }
    $serviceName = $services[$serviceSlug] ?? keywordDisplayName($serviceSlug);
    $relatedSlug = keywordSlug($meta['related'] ?? $keywordSlug);
    $relatedName = $keywords[$relatedSlug]['name'] ?? keywordDisplayName($relatedSlug);

    $GLOBALS['services'] = $services;
    $GLOBALS['areas'] = $areas;

    $ph = keywordTemplatePlaceholders($meta, $keywordSlug, $serviceSlug, $serviceName, $relatedSlug, $relatedName, $areaName);
    $ph['AREA'] = $areaName;
    $ph['AREA_SLUG'] = $areaSlugVal;
    $ph['AREA_URL'] = rawurlencode($areaName);
    $profile = area_profile($areaName);
    $districts = (string)$profile['districts'];
    $stock = (string)$profile['stock'];
    $localLead = $areaName . ' (' . $districts . '): ' . $stock . '. ';
    $ph['KEYWORD_BODY'] = $localLead . $ph['KEYWORD_BODY'];
    $ph['KEYWORD_INTRO'] = $localLead . $ph['KEYWORD_INTRO'];
    $suffix = ' Usual buildings in ' . $districts . ': ' . $stock . '.';
    $faqs = $meta['faq'] ?? [];
    if (!$faqs) {
        $faqs = [
            ["What does {$ph['KEYWORD_NAME']} include?", 'Scope is confirmed in the quote, including paperwork where the visit produces it.'],
            ["Do you cover {$areaName} for {$ph['KEYWORD_NAME']}?", 'Yes when the outward code is ' . $districts . ' and the diary has a slot.'],
        ];
    }
    $faqHtml = '';
    foreach ($faqs as $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $q = htmlspecialchars(scrub_unverified_accreditation((string)$faq[0]), ENT_QUOTES, 'UTF-8');
        $a = htmlspecialchars(scrub_unverified_accreditation((string)$faq[1]) . $suffix, ENT_QUOTES, 'UTF-8');
        $faqHtml .= '<details class="bg-white border-2 border-zinc-300 rounded-2xl p-5 group">'
            . '<summary class="font-bold text-[#061828] cursor-pointer list-none flex justify-between gap-3">'
            . $q . '<span class="text-[#ff6b00] text-xl leading-none">+</span></summary>'
            . '<p class="mt-3 text-sm text-zinc-900 leading-relaxed font-medium">' . $a . '</p></details>';
    }
    foreach (seo_town_faqs($serviceSlug, $serviceName, $areaName) as $faq) {
        $q = htmlspecialchars((string)$faq['q'], ENT_QUOTES, 'UTF-8');
        $a = htmlspecialchars((string)$faq['a'], ENT_QUOTES, 'UTF-8');
        $faqHtml .= '<details class="bg-white border-2 border-zinc-300 rounded-2xl p-5 group">'
            . '<summary class="font-bold text-[#061828] cursor-pointer list-none flex justify-between gap-3">'
            . $q . '<span class="text-[#ff6b00] text-xl leading-none">+</span></summary>'
            . '<p class="mt-3 text-sm text-zinc-900 leading-relaxed font-medium">' . $a . '</p></details>';
    }
    $ph['KEYWORD_FAQ_HTML'] = $faqHtml;

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

    executeTemplateVars(SITE_ROOT . '/templates/area.php', [
        'AREA' => $area,
        'AREA_SLUG' => $areaSlug,
        'AREA_URL' => rawurlencode($area),
        'SERVICE_NAME' => 'Compliance',
    ]);
}

/**
 * Top-level service hub (pages/services/{slug}.php).
 */
function renderServiceHubPage(string $serviceSlug): void {
    if ($serviceSlug === 'aov-air-handling') {
        require_once SITE_ROOT . '/includes/aov.php';
        aovRenderHub();
        return;
    }
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
    $fallbackImg = htmlspecialchars(serviceImageUrl($primary), ENT_QUOTES, 'UTF-8');

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
        $productsHtml = '<p class="text-zinc-600 col-span-full">Contact us for ' . htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8') . ' pricing and availability, or <a class="text-[#ff6b00] font-semibold" href="' . htmlspecialchars(url('/shop/index.php'), ENT_QUOTES, 'UTF-8') . '">browse the shop</a>.</p>';
    } else {
        foreach ($products as $p) {
            $productsHtml .= shopifyCardFromManufacturerProduct($p, $entry['slug'], $entry['name']);
        }
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
        'MFR_SEO_KEYWORDS' => $entry['seo_keywords'] ?? $entry['name'],
        'MFR_SERVICES_HTML' => $servicesHtml,
        'MFR_PRODUCTS_HTML' => $productsHtml,
        'MFR_RELATED_HTML' => $relatedHtml,
        'SERVICE_NAME' => $services[$primary] ?? 'Compliance',
    ]);
}

/**
 * Manufacturer × area. 404 when the brand is excluded or the town is outside its coverage.
 */
function renderManufacturerAreaPage(string $mfrSlug, string $areaSlugVal): void {
    $entry = getManufacturerBySlug($mfrSlug);
    if (!$entry || (function_exists('manufacturerIsExcluded') && manufacturerIsExcluded($mfrSlug))) {
        http_response_code(404);
        echo 'Manufacturer not found';
        icomplyRequestExit();
        return;
    }
    $area = areaFromSlug($areaSlugVal);
    if ($area === null || !manufacturerAreaAllowed($entry, $area)) {
        http_response_code(404);
        echo 'Area not found';
        icomplyRequestExit();
        return;
    }
    $GLOBALS['services'] = getServices();
    $GLOBALS['areas'] = getAreas();
    executeTemplateVars(SITE_ROOT . '/templates/manufacturer-area.php', [
        'MFR_SLUG' => $entry['slug'],
        'AREA' => $area,
        'AREA_SLUG' => areaSlug($area),
    ]);
}
