<?php
/**
 * Fire category job-type catalogue — exactly 390 slugs under /pages/keywords/<slug>.
 * Source of truth is website/data/fire-job-types.json (emitted by bin/build-fire-job-pages.php).
 * Do not hand-author the 390 stubs; the generator writes them.
 */
declare(strict_types=1);

function fireJobTypesExpectedCount(): int
{
    return 390;
}

function fireJobTypesFile(): string
{
    return SITE_ROOT . '/data/fire-job-types.json';
}

function fireJobTypesOutputDir(): string
{
    return SITE_ROOT . '/pages/keywords';
}

function fireJobTypesStubPath(string $slug): string
{
    return fireJobTypesOutputDir() . '/' . keywordSlug($slug) . '.php';
}

function fireJobTypesReset(): void
{
    fireJobTypesData(true);
}

/** @return array{count?:int,category?:string,jobs?:list<array<string,mixed>>} */
function fireJobTypesData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = fireJobTypesFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function fireJobTypesJobs(): array
{
    $jobs = fireJobTypesData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function fireJobTypesSlugs(): array
{
    $out = [];
    foreach (fireJobTypesJobs() as $job) {
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

function fireJobIsExcluded(string $slug): bool
{
    $slug = keywordSlug($slug);
    if ($slug === '') {
        return true;
    }
    // HVAC / AHU plant — not Fire AOV / smoke-control job types.
    if (preg_match('/(?:^|-)ahu(?:-|$)/', $slug) || str_contains($slug, 'air-handling') || str_starts_with($slug, 'hvac-')) {
        return true;
    }
    // Directory / geo doorway pages — not distinct Fire jobs.
    if (str_contains($slug, 'near-me')) {
        return true;
    }
    if ($slug === 'fire-risk-assessment-north-west') {
        return true;
    }
    return false;
}

/**
 * Fire service slugs (site Fire Safety Systems + nurse-call when reconstructing).
 *
 * @return list<string>
 */
function fireJobServiceSlugs(): array
{
    $cats = function_exists('getServiceCategories') ? getServiceCategories() : [];
    $slugs = $cats['fire-safety']['services'] ?? [];
    $out = [];
    foreach (is_array($slugs) ? $slugs : [] as $slug) {
        $slug = areaSlug((string)$slug);
        if ($slug !== '') {
            $out[] = $slug;
        }
    }
    return $out;
}

function fireJobIsFireService(string $serviceSlug): bool
{
    return in_array(areaSlug($serviceSlug), fireJobServiceSlugs(), true);
}

/**
 * Merge the 390 Fire jobs into the live keyword catalogue.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function fireJobsApplyOverlay(array $keywords): array
{
    foreach (fireJobTypesJobs() as $job) {
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
        $service = areaSlug((string)($job['service_type'] ?? $job['service'] ?? $base['service'] ?? 'fire-alarms'));
        $name = trim((string)($base['name'] ?? $job['name'] ?? keywordDisplayName($slug)));
        $row = $base;
        if (empty($row['name'])) {
            $row['name'] = $name;
        }
        if (empty($row['service'])) {
            $row['service'] = $service;
        }
        if (empty($row['related'])) {
            $row['related'] = keywordSlug((string)($job['related'] ?? $base['related'] ?? $slug));
        }
        $row['category'] = 'Fire';
        $row['status'] = (string)($job['status'] ?? 'live');
        $row['fire_master'] = true;
        if (empty($row['seo_title']) || empty($row['h1']) || empty($row['intro']) || empty($row['faq'])) {
            $synthJob = [
                'slug' => $slug,
                'name' => $name,
                'service' => $service,
                'related' => (string)($row['related'] ?? $slug),
            ];
            if (function_exists('jobTypesSynthesize')) {
                $synth = jobTypesSynthesize($synthJob, $row);
                $row = function_exists('jobTypesMergePreferExisting')
                    ? jobTypesMergePreferExisting($row, $synth)
                    : array_merge($synth, $row);
            }
        }
        $keywords[$slug] = $row;
    }
    return $keywords;
}
