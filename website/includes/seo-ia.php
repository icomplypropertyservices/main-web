<?php
/**
 * Wave-1 SEO IA overlay — Category → Service → Job → Area.
 * Lean upgrades for money job pages, manufacturer aliases and new area hubs.
 * Does not expand the full keyword×town matrix.
 */
declare(strict_types=1);

function seoIaData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = SITE_ROOT . '/data/seo-ia-wave1.json';
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return array<string, array<string, mixed>> */
function seoIaJobs(): array
{
    $jobs = seoIaData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function seoIaWave1JobSlugs(): array
{
    return array_keys(seoIaJobs());
}

/** @return array<string, string> */
function seoIaManufacturerAliases(): array
{
    $aliases = seoIaData()['aliases'] ?? [];
    return is_array($aliases) ? $aliases : [];
}

/** @return array<string, array<string, mixed>> */
function seoIaManufacturerExtras(): array
{
    $mfr = seoIaData()['manufacturers'] ?? [];
    return is_array($mfr) ? $mfr : [];
}

/** @return list<string> */
function seoIaWave1ManufacturerSlugs(): array
{
    return [
        'apollo', 'hochiki', 'c-tec', 'advanced', 'kentec', 'vesda', 'ventlux',
        'came', 'videx', 'bell-system', 'paxton', 'aico', 'wylex', 'hager',
        'worcester-bosch', 'vaillant', 'hikvision', 'ajax', 'fike', 'ems',
    ];
}

function seoIaIsWave1Manufacturer(string $slug): bool
{
    $slug = areaSlug($slug);
    return in_array($slug, seoIaWave1ManufacturerSlugs(), true)
        || isset(seoIaManufacturerExtras()[$slug])
        || isset(seoIaManufacturerAliases()[$slug]);
}

/** @return list<array<string, mixed>> */
function seoIaWave1Areas(): array
{
    $areas = seoIaData()['areas'] ?? [];
    return is_array($areas) ? $areas : [];
}

/** @return list<string> */
function seoIaWave1AreaSlugs(): array
{
    $out = [];
    foreach (seoIaWave1Areas() as $row) {
        $slug = areaSlug((string)($row['slug'] ?? $row['name'] ?? ''));
        if ($slug !== '') {
            $out[] = $slug;
        }
    }
    return $out;
}

/** @param list<string> $areas */
function seoIaEnsureAreas(array $areas): array
{
    $have = [];
    foreach ($areas as $name) {
        $have[areaSlug((string)$name)] = true;
    }
    foreach (seoIaWave1Areas() as $row) {
        $name = trim((string)($row['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $slug = areaSlug((string)($row['slug'] ?? $name));
        if (!isset($have[$slug])) {
            $areas[] = $name;
            $have[$slug] = true;
        }
    }
    return $areas;
}

function seoIaAreaOverlay(string $areaNameOrSlug): ?array
{
    $want = areaSlug($areaNameOrSlug);
    foreach (seoIaWave1Areas() as $row) {
        $slug = areaSlug((string)($row['slug'] ?? $row['name'] ?? ''));
        if ($slug === $want) {
            return $row;
        }
    }
    return null;
}

/**
 * Merge wave-1 job overlay (unique titles, FAQs, missing keywords) into the keyword catalogue.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function seoIaApplyJobOverlay(array $keywords): array
{
    foreach (seoIaJobs() as $slug => $overlay) {
        if (!is_array($overlay)) {
            continue;
        }
        $slug = keywordSlug((string)$slug);
        $base = $keywords[$slug] ?? [];
        $row = $base;
        foreach (['name', 'service', 'related', 'intro', 'body', 'meta_desc', 'seo_keywords'] as $field) {
            if (!empty($overlay[$field]) && is_string($overlay[$field])) {
                $row[$field] = $overlay[$field];
            }
        }
        if (!empty($overlay['focus_points']) && is_array($overlay['focus_points'])) {
            $row['focus_points'] = $overlay['focus_points'];
        }
        if (!empty($overlay['faq']) && is_array($overlay['faq'])) {
            $row['faq'] = $overlay['faq'];
        }
        foreach (['seo_title', 'h1', 'primary'] as $field) {
            if (!empty($overlay[$field]) && is_string($overlay[$field])) {
                $row[$field] = $overlay[$field];
            }
        }
        if (!empty($overlay['secondaries']) && is_array($overlay['secondaries'])) {
            $row['secondaries'] = $overlay['secondaries'];
        }
        if (!empty($overlay['manufacturers']) && is_array($overlay['manufacturers'])) {
            $row['manufacturers'] = $overlay['manufacturers'];
        }
        if (!empty($overlay['extra_manufacturer_names']) && is_array($overlay['extra_manufacturer_names'])) {
            $row['extra_manufacturer_names'] = $overlay['extra_manufacturer_names'];
        }
        if (empty($row['name'])) {
            $row['name'] = keywordDisplayName($slug);
        }
        if (empty($row['service'])) {
            $row['service'] = 'electrical';
        }
        if (empty($row['related'])) {
            $row['related'] = $slug;
        }
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * @param array<string, array<string, mixed>> $catalog
 * @return array<string, array<string, mixed>>
 */
function seoIaMergeManufacturerCatalog(array $catalog): array
{
    foreach (seoIaManufacturerExtras() as $slug => $entry) {
        $slug = areaSlug((string)$slug);
        if ($slug === '') {
            continue;
        }
        $aliasOf = areaSlug((string)($entry['alias_of'] ?? ''));
        $base = [];
        if ($aliasOf !== '' && isset($catalog[$aliasOf])) {
            $base = $catalog[$aliasOf];
        } elseif (isset($catalog[$slug])) {
            $base = $catalog[$slug];
        }
        $merged = $base;
        $merged['name'] = (string)($entry['name'] ?? $merged['name'] ?? keywordDisplayName($slug));
        $merged['slug'] = $slug;
        if (!empty($entry['services']) && is_array($entry['services'])) {
            $merged['services'] = $entry['services'];
        } elseif (empty($merged['services'])) {
            $merged['services'] = ['fire-alarms'];
        }
        if (!empty($entry['blurb'])) {
            $merged['blurb'] = (string)$entry['blurb'];
        } elseif (empty($merged['blurb'])) {
            $merged['blurb'] = 'iComply installs and services ' . $merged['name'] . ' equipment across the North West. Enquire for a written POA quote.';
        }
        if (!empty($entry['seo_title'])) {
            $merged['seo_title'] = (string)$entry['seo_title'];
        }
        if (!empty($entry['seo_keywords'])) {
            $merged['seo_keywords'] = (string)$entry['seo_keywords'];
        }
        if (!empty($entry['jobs']) && is_array($entry['jobs'])) {
            $merged['jobs'] = $entry['jobs'];
        }
        if (!empty($entry['lines']) && is_array($entry['lines'])) {
            $merged['lines'] = $entry['lines'];
        }
        if (isset($entry['featured'])) {
            $merged['featured'] = (bool)$entry['featured'];
        }
        if (empty($merged['seo_desc']) && !empty($merged['blurb'])) {
            $merged['seo_desc'] = $merged['blurb'];
        }
        if (empty($merged['products'])) {
            $merged['products'] = [];
        }
        $catalog[$slug] = $merged;
    }
    return $catalog;
}

function seoIaResolveManufacturerSlug(string $slug): string
{
    $slug = areaSlug($slug);
    $aliases = seoIaManufacturerAliases();
    return $aliases[$slug] ?? $slug;
}

/**
 * Category key + label for a service slug.
 *
 * @return array{key:string,label:string}
 */
function seoIaCategoryForService(string $serviceSlug): array
{
    $serviceSlug = areaSlug($serviceSlug);
    foreach (getServiceCategories() as $key => $cat) {
        $slugs = $cat['services'] ?? [];
        if (is_array($slugs) && in_array($serviceSlug, $slugs, true)) {
            return [
                'key' => (string)$key,
                'label' => (string)($cat['label'] ?? $key),
            ];
        }
    }
    return ['key' => 'all', 'label' => 'Services'];
}

/**
 * Job-specific manufacturer pills (wave-1 money pages) plus honest name-only chips.
 */
function seoIaManufacturerTagsHtml(string $serviceSlug, array $jobSlugs = [], array $extraNames = []): string
{
    $html = '';
    $seen = [];
    foreach ($jobSlugs as $slug) {
        $slug = areaSlug((string)$slug);
        if ($slug === '' || isset($seen[$slug])) {
            continue;
        }
        $seen[$slug] = true;
        $entry = getManufacturerBySlug($slug);
        $label = htmlspecialchars((string)($entry['name'] ?? keywordDisplayName($slug)), ENT_QUOTES, 'UTF-8');
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" '
            . 'class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border-2 border-zinc-200 rounded-full text-sm text-black font-semibold hover:border-[#ff6b00] hover:text-[#ff6b00] hover:shadow-sm transition" '
            . 'title="View ' . $label . ' products and service page">'
            . $label
            . '<span class="text-[#ff6b00]" aria-hidden="true">→</span></a>';
    }
    foreach ($extraNames as $name) {
        $name = trim((string)$name);
        if ($name === '') {
            continue;
        }
        $guess = areaSlug($name);
        if (isset($seen[$guess])) {
            continue;
        }
        $entry = getManufacturerBySlug($guess);
        if ($entry) {
            $seen[$guess] = true;
            $label = htmlspecialchars((string)$entry['name'], ENT_QUOTES, 'UTF-8');
            $href = htmlspecialchars(url('/pages/manufacturers/' . $entry['slug'] . '.php'), ENT_QUOTES, 'UTF-8');
            $html .= '<a href="' . $href . '" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border-2 border-zinc-200 rounded-full text-sm text-black font-semibold hover:border-[#ff6b00] hover:text-[#ff6b00] transition">'
                . $label . '<span class="text-[#ff6b00]" aria-hidden="true">→</span></a>';
            continue;
        }
        $label = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $html .= '<span class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-zinc-50 border-2 border-dashed border-zinc-300 rounded-full text-sm text-zinc-700 font-semibold" title="Brand we install — dedicated page not published in wave 1">'
            . $label . '</span>';
    }
    if ($html === '') {
        return manufacturerTagsHtml($serviceSlug);
    }
    $allHref = htmlspecialchars(url('/pages/manufacturers/index.php'), ENT_QUOTES, 'UTF-8');
    $html .= '<a href="' . $allHref . '" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#0B1F3A] text-white rounded-full text-sm font-semibold hover:bg-[#ff6b00] transition">All brands →</a>';
    return $html;
}

/**
 * Related job chips for a manufacturer page.
 */
function seoIaManufacturerJobsHtml(string $mfrSlug): string
{
    $entry = getManufacturerBySlug($mfrSlug) ?? [];
    $jobs = $entry['jobs'] ?? [];
    if (!$jobs) {
        $jobs = [];
        foreach (seoIaJobs() as $kw => $meta) {
            $list = $meta['manufacturers'] ?? [];
            if (is_array($list) && in_array(areaSlug($mfrSlug), array_map('areaSlug', $list), true)) {
                $jobs[] = $kw;
            }
        }
    }
    $keywords = getMajorKeywords();
    $html = '';
    foreach ($jobs as $slug) {
        $slug = keywordSlug((string)$slug);
        if ($slug === '' || !isset($keywords[$slug])) {
            continue;
        }
        $name = (string)($keywords[$slug]['name'] ?? keywordDisplayName($slug));
        $href = htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-4 py-2.5 bg-white border-2 border-zinc-200 rounded-2xl text-sm font-semibold hover:border-[#ff6b00] hover:text-[#ff6b00]">'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ' →</a>';
    }
    if ($html === '') {
        $svc = $entry['services'][0] ?? '';
        if ($svc !== '') {
            $html = '<a href="' . htmlspecialchars(url('/pages/services/' . $svc . '.php'), ENT_QUOTES, 'UTF-8') . '" class="px-4 py-2.5 bg-white border-2 border-zinc-200 rounded-2xl text-sm font-semibold hover:border-[#ff6b00]">Related service →</a>';
        }
    }
    return $html;
}

/** Honest placeholder when no real manufacturer photo exists in the repo. */
function seoIaManufacturerImageHtml(string $slug, string $name, string $fallbackService = 'fire-alarms'): string
{
    $rel = '/assets/images/manufacturers/' . areaSlug($slug) . '.jpg';
    if (is_file(SITE_ROOT . $rel)) {
        $src = htmlspecialchars(url($rel), ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($name . ' equipment — Icomply Property Services', ENT_QUOTES, 'UTF-8');
        return '<img src="' . $src . '" alt="' . $alt . '" class="absolute inset-0 w-full h-full object-cover opacity-70" loading="eager">';
    }
    $safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    return '<div class="absolute inset-0 flex flex-col justify-end p-6 md:p-8 bg-[#0B1F3A]">'
        . '<div class="w-14 h-14 rounded-2xl bg-[#FF6B00] flex items-center justify-center mb-4" aria-hidden="true">'
        . '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="#0B1F3A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        . '</div>'
        . '<p class="text-sm text-white/80">No stock photograph of ' . $safe . ' equipment is published in this repo. We do not use AI-generated images. Ask for recent North West install photos when you enquire.</p>'
        . '</div>';
}

function seoIaPoaPrice(string $price): string
{
    if ($price === '' || preg_match('/£\s*\d/', $price)) {
        return 'POA / enquire';
    }
    return $price;
}

/**
 * @param list<array{0?:string,1?:string}|array{q?:string,a?:string}> $faqs
 */
function seoIaFaqPageJsonLd(array $faqs): ?array
{
    $entities = [];
    foreach ($faqs as $faq) {
        if (!is_array($faq)) {
            continue;
        }
        $q = (string)($faq['q'] ?? $faq[0] ?? '');
        $a = (string)($faq['a'] ?? $faq[1] ?? '');
        if ($q === '' || $a === '') {
            continue;
        }
        $entities[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $a,
            ],
        ];
    }
    if (!$entities) {
        return null;
    }
    return [
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ];
}

/** Featured money-job cards for hubs. */
function seoIaJobIndexHtml(): string
{
    $jobs = seoIaJobs();
    $keywords = getMajorKeywords();
    $html = '<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">';
    foreach ($jobs as $slug => $meta) {
        $slug = keywordSlug((string)$slug);
        $row = $keywords[$slug] ?? $meta;
        $name = (string)($row['h1'] ?? $row['name'] ?? keywordDisplayName($slug));
        $svc = (string)($row['service'] ?? 'electrical');
        $svcName = getServices()[$svc] ?? keywordDisplayName($svc);
        $href = htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="group bg-white border border-zinc-200 rounded-3xl p-5 hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">'
            . '<span class="text-[10px] uppercase tracking-wider px-2 py-1 rounded-full bg-zinc-100 text-zinc-600 font-semibold w-fit">'
            . htmlspecialchars($svcName, ENT_QUOTES, 'UTF-8') . '</span>'
            . '<h3 class="mt-3 font-semibold text-lg text-black leading-snug group-hover:text-[#ff6b00]">'
            . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h3>'
            . '<span class="mt-3 text-sm font-semibold text-[#ff6b00]">Job page →</span></a>';
    }
    $html .= '</div>';
    return $html;
}
