<?php
/**
 * Property SEO packs — data-driven hubs shared by every locked pack.
 *
 * Each pack is one file: data/property-packs/{pack}.json, written by
 * bin/build-{pack}-pack.py from its SEO lock in icomply-ops/seo/. A pack holds
 * its keyword hubs (/pages/keywords/{slug}), job hubs (/pages/jobs/{slug}) and
 * any new service hubs (/pages/services/{slug}), with images and credits.
 * Keyword×town, job×town and service×town are served by the edge matrix on the
 * dual-ring 269 from the catalogue (matrix-catalogue.php) — no HTML per town.
 *
 * This file is identical in every pack PR so that packs merge independently.
 */
declare(strict_types=1);

/** @return array<string,array<string,mixed>> pack id => pack */
function icomplyPropertyPacks(): array
{
    static $packs = null;
    if ($packs !== null) {
        return $packs;
    }
    $packs = [];
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $files = glob($root . '/data/property-packs/*.json') ?: [];
    sort($files);
    foreach ($files as $file) {
        $decoded = json_decode((string)file_get_contents($file), true);
        if (is_array($decoded)) {
            $packs[basename($file, '.json')] = $decoded;
        }
    }
    return $packs;
}

/** @return array<string,mixed> */
function icomplyPropertyPack(string $id): array
{
    return icomplyPropertyPacks()[$id] ?? [];
}

/**
 * @param 'keywords'|'jobs'|'services' $part
 * @return array<string,array<string,mixed>>
 */
function icomplyPropertyPackPart(string $part, ?string $id = null): array
{
    $out = [];
    foreach (icomplyPropertyPacks() as $packId => $pack) {
        if ($id !== null && $packId !== $id) {
            continue;
        }
        foreach ((array)($pack[$part] ?? []) as $slug => $row) {
            if (is_array($row) && !isset($out[(string)$slug])) {
                $row['pack'] = (string)$packId;
                $out[(string)$slug] = $row;
            }
        }
    }
    return $out;
}

/** @return array<string,array<string,mixed>> */
function icomplyPropertyPackKeywords(?string $id = null): array
{
    return icomplyPropertyPackPart('keywords', $id);
}

/** @return array<string,array<string,mixed>> */
function icomplyPropertyPackJobs(?string $id = null): array
{
    return icomplyPropertyPackPart('jobs', $id);
}

/** @return array<string,array<string,mixed>> */
function icomplyPropertyPackServices(?string $id = null): array
{
    return icomplyPropertyPackPart('services', $id);
}

/** @return list<string> */
function icomplyPropertyPackServiceSlugs(?string $id = null): array
{
    return array_keys(icomplyPropertyPackServices($id));
}

/** Merge pack services into getServices(). Existing labels win. */
function icomplyPropertyPackMergeServices(array $services): array
{
    foreach (icomplyPropertyPackServices() as $slug => $svc) {
        if (!isset($services[$slug])) {
            $services[$slug] = (string)($svc['label'] ?? ucwords(str_replace('-', ' ', $slug)));
        }
    }
    return $services;
}

/** Merge pack service meta (seo_title, seo_desc, blurb, short, standards, pricing) into getServiceMeta(). */
function icomplyPropertyPackMergeServiceMeta(array $meta): array
{
    foreach (icomplyPropertyPackServices() as $slug => $svc) {
        if (!isset($meta[$slug]) && is_array($svc['meta'] ?? null)) {
            $meta[$slug] = $svc['meta'];
        }
    }
    return $meta;
}

/** Merge pack categories into getServiceCategories(). */
function icomplyPropertyPackMergeCategories(array $cats): array
{
    foreach (icomplyPropertyPacks() as $pack) {
        foreach ((array)($pack['categories'] ?? []) as $key => $cat) {
            if (!is_array($cat)) {
                continue;
            }
            if (!isset($cats[$key])) {
                $cats[$key] = $cat;
                continue;
            }
            foreach ((array)($cat['services'] ?? []) as $slug) {
                if (!in_array($slug, (array)($cats[$key]['services'] ?? []), true)) {
                    $cats[$key]['services'][] = $slug;
                }
            }
        }
    }
    return $cats;
}

/**
 * Pack keyword hubs into getMajorKeywords(). A pack row replaces an older thin
 * row only when the builder marked it `replaces_existing` (the lock says ship
 * that slug once, from this pack, with rich copy).
 */
function icomplyPropertyPackApplyKeywords(array $normalized): array
{
    foreach (icomplyPropertyPackKeywords() as $slug => $meta) {
        if ($slug === '' || (isset($normalized[$slug]) && empty($meta['replaces_existing']))) {
            continue;
        }
        $row = [
            'name' => (string)($meta['name'] ?? $slug),
            'service' => (string)($meta['service'] ?? 'building-maintenance'),
            'related' => (string)($meta['related'] ?? $slug),
            'pack' => (string)$meta['pack'],
        ];
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords', 'seo_title', 'h1'] as $field) {
            if (!empty($meta[$field]) && is_string($meta[$field])) {
                $row[$field] = $meta[$field];
            }
        }
        foreach (['focus_points', 'faq', 'sections', 'images'] as $field) {
            if (!empty($meta[$field]) && is_array($meta[$field])) {
                $row[$field] = $meta[$field];
            }
        }
        $normalized[$slug] = $row;
    }
    return $normalized;
}

/** @return list<string>|null three on-topic image paths for a pack service */
function icomplyPropertyPackServiceImages(string $serviceSlug): ?array
{
    $svc = icomplyPropertyPackServices()[$serviceSlug] ?? null;
    $images = is_array($svc) ? array_values(array_filter((array)($svc['images'] ?? []), 'is_string')) : [];
    return count($images) >= 3 ? array_slice($images, 0, 3) : null;
}

/** @return list<string>|null three image paths for a pack keyword hub */
function icomplyPropertyPackKeywordImages(string $slug): ?array
{
    $kw = icomplyPropertyPackKeywords()[$slug] ?? null;
    $images = is_array($kw) ? array_values(array_filter((array)($kw['images'] ?? []), 'is_string')) : [];
    return count($images) >= 3 ? array_slice($images, 0, 3) : null;
}

/**
 * Matrix job records for job×town on the dual-ring 269.
 *
 * @return array<string,array{name:string,service:string,gas:bool,nationwide:bool,focus:list<string>}>
 */
function icomplyPropertyPackMatrixJobs(): array
{
    $out = [];
    foreach (icomplyPropertyPackJobs() as $slug => $job) {
        $out[$slug] = [
            'name' => (string)($job['name'] ?? $slug),
            'service' => (string)($job['service'] ?? 'building-maintenance'),
            'gas' => false,
            'nationwide' => false,
            'focus' => array_values(array_map('strval', (array)($job['focus'] ?? []))),
        ];
    }
    return $out;
}

/** Edge-matrix image triples for pack services (assets/matrix/services.json → pack_images). */
function icomplyPropertyPackMatrixImages(): array
{
    $out = [];
    foreach (icomplyPropertyPackServiceSlugs() as $slug) {
        $images = icomplyPropertyPackServiceImages($slug);
        if ($images !== null) {
            $out[$slug] = $images;
        }
    }
    return $out;
}

/** @return array<string,mixed>|null */
function icomplyPropertyPackServiceHubCopy(string $slug): ?array
{
    $svc = icomplyPropertyPackServices()[$slug] ?? null;
    return is_array($svc) && is_array($svc['hub'] ?? null) ? $svc['hub'] : null;
}

/** @return list<array{0:string,1:string}> */
function icomplyPropertyPackServiceHubLinks(string $slug): array
{
    $out = [];
    foreach ((array)(icomplyPropertyPackServiceHubCopy($slug)['links'] ?? []) as $link) {
        if (is_array($link) && count($link) >= 2) {
            $out[] = [(string)$link[0], (string)$link[1]];
        }
    }
    return $out;
}

/** Job index groups: label => list of {slug,name,blurb}. */
function icomplyPropertyPackJobGroups(): array
{
    $groups = [];
    $services = icomplyPropertyPackServices();
    foreach (icomplyPropertyPackJobs() as $slug => $job) {
        $svc = (string)($job['service'] ?? '');
        $label = (string)($job['group'] ?? ($services[$svc]['label'] ?? 'Property services'));
        $groups[$label][] = [
            'slug' => $slug,
            'name' => (string)($job['name'] ?? $slug),
            'blurb' => (string)($job['blurb'] ?? ($job['lede'] ?? '')),
        ];
    }
    return $groups;
}

/** Prose sections for pack keyword hubs (h2 + paragraphs). @param mixed $sections */
function icomplyPropertyPackSectionsHtml($sections): string
{
    if (!is_array($sections) || $sections === []) {
        return '';
    }
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $html = '<div class="mt-8 space-y-8" data-pe-slot="q1-keyword-hub-prose">';
    foreach ($sections as $section) {
        if (!is_array($section)) {
            continue;
        }
        $h2 = trim((string)($section['h2'] ?? ''));
        $paras = array_values(array_filter(array_map('strval', (array)($section['p'] ?? [])), static fn($p) => trim($p) !== ''));
        if ($h2 === '' || $paras === []) {
            continue;
        }
        $html .= '<div><h2 class="text-xl md:text-2xl font-bold text-[#061828] tracking-tight">' . $h($h2) . '</h2>';
        foreach ($paras as $p) {
            $html .= '<p class="mt-3 text-base md:text-lg text-zinc-900 leading-relaxed">' . $h($p) . '</p>';
        }
        $html .= '</div>';
    }
    return $html . '</div>';
}

function renderPropertyPackJob(string $slug): void
{
    require_once SITE_ROOT . '/includes/job-article.php';
    $job = icomplyPropertyPackJobs()[$slug] ?? null;
    if (!is_array($job)) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }
    renderJobArticle($job);
}
