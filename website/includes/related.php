<?php
/**
 * Related content helpers — service cards, area chips, manufacturer cards.
 * Usage: require_once SITE_ROOT . '/includes/related.php';
 *        <?= relatedServicesHtml($serviceSlug) ?>
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Related service cards (excludes current slug).
 */
function relatedServicesHtml(string $currentSlug, int $limit = 6): string {
    $services = getServices();
    $items = [];
    foreach ($services as $slug => $name) {
        if ($slug === $currentSlug) {
            continue;
        }
        $items[$slug] = $name;
        if (count($items) >= $limit) {
            break;
        }
    }

    if (!$items) {
        return '';
    }

    $html = '<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">';
    foreach ($items as $slug => $name) {
        $href = htmlspecialchars(url('/pages/services/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $img = htmlspecialchars(url('/assets/images/services/' . $slug . '.jpg'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $blurb = htmlspecialchars(getServiceBlurb($slug, true), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="group bg-white border rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">'
            . '<div class="h-28 bg-zinc-100 overflow-hidden">'
            . '<img src="' . $img . '" alt="' . $label . '" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.parentElement.style.display=\'none\'">'
            . '</div>'
            . '<div class="p-5 flex-1 flex flex-col">'
            . '<h3 class="font-semibold text-black">' . $label . '</h3>'
            . '<p class="text-sm text-zinc-600 mt-2 flex-1 line-clamp-2">' . $blurb . '</p>'
            . '<span class="mt-3 text-sm font-semibold text-[#ff6b00]">Explore →</span>'
            . '</div></a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Related area chips linking to area hub pages.
 */
function relatedAreasHtml(int $limit = 12): string {
    $popular = [
        'Manchester', 'Stockport', 'Bolton', 'Salford', 'Oldham', 'Rochdale',
        'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool',
        'Bury', 'Sale', 'Altrincham', 'Macclesfield', 'Burnley', 'Blackburn',
        'St Helens', 'Worsley',
    ];
    $all = getAreas();
    $picked = [];
    foreach ($popular as $town) {
        if (in_array($town, $all, true)) {
            $picked[] = $town;
        }
        if (count($picked) >= $limit) {
            break;
        }
    }
    // Fill from full list if popular set is short
    if (count($picked) < $limit) {
        foreach ($all as $town) {
            if (!in_array($town, $picked, true)) {
                $picked[] = $town;
            }
            if (count($picked) >= $limit) {
                break;
            }
        }
    }

    if (!$picked) {
        return '';
    }

    $html = '<div class="flex flex-wrap gap-2">';
    foreach ($picked as $area) {
        $slug = areaSlug($area);
        $href = htmlspecialchars(url('/pages/areas/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($area, ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-4 py-2 bg-white border rounded-full text-sm font-medium text-black hover:border-[#ff6b00] hover:shadow-sm transition">'
            . $label . '</a>';
    }
    $html .= '<a href="' . htmlspecialchars(url('/pages/areas/index.php'), ENT_QUOTES, 'UTF-8') . '" class="px-4 py-2 text-sm font-semibold text-[#ff6b00]">All areas →</a>';
    $html .= '</div>';
    return $html;
}

/**
 * Related manufacturer chips/cards. When $serviceSlug is set, prefer brands for that service.
 */
function relatedManufacturersHtml(string $serviceSlug = '', int $limit = 8): string {
    $catalog = getManufacturerCatalog();
    if (!$catalog) {
        return '';
    }

    $serviceSlug = $serviceSlug !== '' ? areaSlug($serviceSlug) : '';
    $matched = [];
    $featured = [];
    $rest = [];

    foreach ($catalog as $slug => $entry) {
        $services = $entry['services'] ?? [];
        $isMatch = $serviceSlug === '' || in_array($serviceSlug, $services, true);
        if (!$isMatch) {
            continue;
        }
        $row = [
            'slug' => $slug,
            'name' => $entry['name'] ?? ucwords(str_replace('-', ' ', (string)$slug)),
            'featured' => !empty($entry['featured']),
            'blurb' => $entry['blurb'] ?? '',
        ];
        if ($row['featured']) {
            $featured[] = $row;
        } else {
            $rest[] = $row;
        }
    }

    $picked = array_slice(array_merge($featured, $rest), 0, $limit);

    // Fallback: any manufacturers if service filter emptied the list
    if (!$picked && $serviceSlug !== '') {
        return relatedManufacturersHtml('', $limit);
    }
    if (!$picked) {
        return '';
    }

    $fallbackImg = htmlspecialchars(url('/assets/images/services/' . ($serviceSlug !== '' ? $serviceSlug : 'fire-alarms') . '.jpg'), ENT_QUOTES, 'UTF-8');

    $html = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">';
    foreach ($picked as $row) {
        $slug = preg_replace('/[^a-z0-9\-]/', '', (string)$row['slug']);
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $src = htmlspecialchars(url('/assets/images/manufacturers/' . $slug . '.jpg'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="group bg-white border rounded-2xl overflow-hidden hover:border-[#ff6b00] hover:shadow-md transition block">'
            . '<div class="h-24 bg-zinc-100 overflow-hidden">'
            . '<img src="' . $src . '" alt="' . $label . ' products and service" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy" onerror="this.src=\'' . $fallbackImg . '\'">'
            . '</div>'
            . '<div class="p-3 text-center">'
            . '<div class="text-sm font-semibold text-black">' . $label . '</div>'
            . '<div class="text-xs text-[#ff6b00] mt-1 font-medium">View brand →</div>'
            . '</div></a>';
    }
    $html .= '</div>';

    // Chip row under cards for denser SEO links
    $html .= '<div class="flex flex-wrap gap-2 mt-5">';
    foreach ($picked as $row) {
        $slug = preg_replace('/[^a-z0-9\-]/', '', (string)$row['slug']);
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-3 py-1.5 bg-white border rounded-full text-xs font-medium text-zinc-700 hover:border-[#ff6b00] transition">'
            . $label . '</a>';
    }
    $html .= '<a href="' . htmlspecialchars(url('/pages/manufacturers/index.php'), ENT_QUOTES, 'UTF-8') . '" class="px-3 py-1.5 text-xs font-semibold text-[#ff6b00]">All manufacturers →</a>';
    $html .= '</div>';

    return $html;
}

/**
 * Keyword guide chips for a service (links to /pages/keywords/{slug}).
 */
function relatedKeywordsHtml(string $serviceSlug, int $limit = 0): string {
    $keywords = getKeywordsForService($serviceSlug);
    if (!$keywords) {
        return '';
    }
    if ($limit > 0) {
        $keywords = array_slice($keywords, 0, $limit, true);
    }

    $html = '<div class="flex flex-wrap gap-2">';
    foreach ($keywords as $slug => $meta) {
        $href = htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string)($meta['name'] ?? keywordDisplayName($slug)), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-3 py-1.5 bg-white border border-zinc-200 rounded-full text-xs font-medium text-zinc-800 hover:border-[#ff6b00] hover:text-[#ff6b00] transition">'
            . $label . '</a>';
    }
    $html .= '<a href="' . htmlspecialchars(url('/pages/keywords/index.php'), ENT_QUOTES, 'UTF-8') . '" class="px-3 py-1.5 text-xs font-semibold text-[#ff6b00]">All guides →</a>';
    $html .= '</div>';
    return $html;
}

/**
 * Keyword × area chips for one town (e.g. EICR Report in Stockport).
 * Uses popular + service-top keywords so every area links into local keyword pages.
 *
 * @param list<string>|null $extraSlugs
 */
function keywordAreaLinksHtml(string $area, ?array $extraSlugs = null, int $limit = 36): string {
    $areaSlug = areaSlug($area);
    $all = getMajorKeywords();
    $slugs = getPopularKeywordSlugs();
    if ($extraSlugs) {
        foreach ($extraSlugs as $s) {
            $s = keywordSlug((string)$s);
            if ($s !== '' && !in_array($s, $slugs, true)) {
                $slugs[] = $s;
            }
        }
    }
    // Pad with more from electrical / fire / gas if short
    if (count($slugs) < $limit) {
        foreach (['electrical', 'fire-alarms', 'gas-systems', 'emergency-lighting'] as $svc) {
            foreach (getKeywordsForService($svc) as $slug => $_) {
                if (!in_array($slug, $slugs, true)) {
                    $slugs[] = $slug;
                }
                if (count($slugs) >= $limit) {
                    break 2;
                }
            }
        }
    }
    $slugs = array_slice($slugs, 0, $limit);

    $html = '<div class="flex flex-wrap gap-2">';
    foreach ($slugs as $slug) {
        if (!isset($all[$slug])) {
            continue;
        }
        $name = (string)($all[$slug]['name'] ?? keywordDisplayName($slug));
        $href = htmlspecialchars(url('/pages/keywords/' . $slug . '/' . $areaSlug . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($name . ' in ' . $area, ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-3 py-1.5 bg-white border border-zinc-200 rounded-full text-xs font-medium text-zinc-800 hover:border-[#ff6b00] hover:text-[#ff6b00] transition">'
            . $label . '</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * For a keyword hub: link top related keywords under the same service.
 */
function siblingKeywordsHtml(string $keywordSlug, string $serviceSlug, int $limit = 16): string {
    $keywordSlug = keywordSlug($keywordSlug);
    $keywords = getKeywordsForService($serviceSlug);
    unset($keywords[$keywordSlug]);
    if (!$keywords) {
        return '';
    }
    $keywords = array_slice($keywords, 0, $limit, true);
    $html = '<div class="flex flex-wrap gap-2">';
    foreach ($keywords as $slug => $meta) {
        $href = htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string)($meta['name'] ?? keywordDisplayName($slug)), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-3 py-1.5 bg-white border-2 border-zinc-300 rounded-full text-xs font-semibold text-zinc-900 hover:border-[#ff6b00] hover:text-[#ff6b00]">'
            . $label . '</a>';
    }
    $html .= '</div>';
    return $html;
}
