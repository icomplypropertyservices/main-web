<?php
/**
 * Category job-pack overlays (sibling agents).
 * Last write wins on matching slugs already in the 1,753 master.
 */
declare(strict_types=1);

function jobPacksDir(): string
{
    return SITE_ROOT . '/data/job-packs';
}

function coreComplianceLiveIndexFile(): string
{
    return jobPacksDir() . '/core-compliance-live-index.json';
}

/** @return array{core_money?:list<string>,compliance_packs?:list<string>,live_keywords?:list<string>,live_services?:list<string>,canonical_jobs?:array<string,string>} */
function coreComplianceLiveIndex(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = coreComplianceLiveIndexFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<string> */
function coreComplianceLiveJobSlugs(): array
{
    $idx = coreComplianceLiveIndex();
    $out = [];
    foreach (['core_money', 'compliance_packs', 'live_keywords'] as $key) {
        foreach ($idx[$key] ?? [] as $slug) {
            $slug = keywordSlug((string)$slug);
            if ($slug !== '') {
                $out[$slug] = true;
            }
        }
    }
    foreach ($idx['canonical_jobs'] ?? [] as $jobSlug) {
        $slug = keywordSlug((string)$jobSlug);
        if ($slug !== '') {
            $out[$slug] = true;
        }
    }
    return array_keys($out);
}

/** @return list<string> */
function coreComplianceLiveServiceSlugs(): array
{
    $out = [];
    foreach (coreComplianceLiveIndex()['live_services'] ?? [] as $slug) {
        $slug = areaSlug((string)$slug);
        if ($slug !== '') {
            $out[] = $slug;
        }
    }
    return array_values(array_unique($out));
}

/**
 * @return list<array<string, mixed>>
 */
function jobPackFiles(): array
{
    $dir = jobPacksDir();
    if (!is_dir($dir)) {
        return [];
    }
    $files = glob($dir . '/*.json') ?: [];
    sort($files, SORT_STRING);
    $out = [];
    foreach ($files as $file) {
        $base = basename((string)$file);
        if (str_ends_with($base, '-index.json')) {
            continue;
        }
        $decoded = json_decode((string)file_get_contents($file), true);
        if (is_array($decoded)) {
            $out[] = $decoded;
        }
    }
    return $out;
}

/**
 * @param array<string, mixed> $pack
 * @return array<string, array<string, mixed>>
 */
function jobPackNormalize(array $pack): array
{
    if (isset($pack['jobs']) && is_array($pack['jobs'])) {
        $rows = $pack['jobs'];
        $isList = array_is_list($rows);
        $out = [];
        foreach ($rows as $key => $row) {
            if (!is_array($row)) {
                continue;
            }
            $slug = keywordSlug((string)($row['slug'] ?? ($isList ? '' : $key)));
            if ($slug === '') {
                continue;
            }
            $row['slug'] = $slug;
            $out[$slug] = $row;
        }
        return $out;
    }
    $out = [];
    foreach ($pack as $key => $row) {
        if (!is_array($row) || in_array($key, ['count', 'generated', 'slice', 'note'], true)) {
            continue;
        }
        $slug = keywordSlug((string)($row['slug'] ?? $key));
        if ($slug === '') {
            continue;
        }
        $row['slug'] = $slug;
        $out[$slug] = $row;
    }
    return $out;
}

/**
 * @return array<string, array<string, mixed>>
 */
function jobPackOverlays(): array
{
    static $merged = null;
    if ($merged !== null) {
        return $merged;
    }
    $merged = [];
    foreach (jobPackFiles() as $pack) {
        foreach (jobPackNormalize($pack) as $slug => $row) {
            $merged[$slug] = $row;
        }
    }
    return $merged;
}

/**
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function jobPacksApply(array $keywords): array
{
    $master = [];
    if (function_exists('jobTypesMasterSlugs')) {
        foreach (jobTypesMasterSlugs() as $slug) {
            $master[keywordSlug((string)$slug)] = true;
        }
    }
    foreach (jobPackOverlays() as $slug => $overlay) {
        if ($master && !isset($master[$slug])) {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $row = $base;
        foreach (['name', 'service', 'related', 'intro', 'body', 'meta_desc', 'seo_keywords', 'seo_title', 'h1', 'primary'] as $field) {
            if (!empty($overlay[$field]) && is_string($overlay[$field])) {
                $row[$field] = $overlay[$field];
            }
        }
        foreach (['focus_points', 'faq', 'secondaries', 'manufacturers', 'extra_manufacturer_names'] as $field) {
            if (!empty($overlay[$field]) && is_array($overlay[$field])) {
                $row[$field] = $overlay[$field];
            }
        }
        if (empty($row['name'])) {
            $row['name'] = keywordDisplayName($slug);
        }
        if (empty($row['service'])) {
            $row['service'] = areaSlug((string)($overlay['service'] ?? 'electrical'));
        }
        $keywords[$slug] = $row;
    }
    return $keywords;
}

/**
 * Featured money + compliance cards for the keywords hub.
 */
function coreComplianceLiveFeaturedHtml(): string
{
    $idx = coreComplianceLiveIndex();
    $keywords = getMajorKeywords();
    $html = '';
    $blocks = [
        ['core_money', 'Core money jobs', 'EICR, CP12, FRA, emergency lighting, fire alarms, PAT, Legionella, asbestos, fire doors, EPC.'],
        ['compliance_packs', 'Compliance packs', 'Landlord and HMO certificate sets — scoped POA, not a web price list.'],
    ];
    foreach ($blocks as [$key, $heading, $blurb]) {
        $html .= '<div class="mb-10">';
        $html .= '<h3 class="text-xl font-semibold text-black">' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '<p class="mt-1 text-sm text-zinc-600 mb-4">' . htmlspecialchars($blurb, ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">';
        foreach ($idx[$key] ?? [] as $slug) {
            $slug = keywordSlug((string)$slug);
            $row = $keywords[$slug] ?? [];
            $name = (string)($row['h1'] ?? $row['name'] ?? keywordDisplayName($slug));
            $href = htmlspecialchars(url('/pages/keywords/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
            $html .= '<a href="' . $href . '" class="group bg-zinc-50 border border-zinc-200 rounded-3xl p-5 hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">'
                . '<h4 class="font-semibold text-black leading-snug group-hover:text-[#ff6b00]">'
                . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h4>'
                . '<span class="mt-3 text-sm font-semibold text-[#ff6b00]">POA quote →</span></a>';
        }
        $html .= '</div></div>';
    }
    return $html;
}
