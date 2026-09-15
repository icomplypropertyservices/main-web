<?php
/**
 * Building (247) + Gas (223) job-type families for Jack's 100% jobs push.
 * Source: website/data/job-types-building.json and job-types-gas.json
 * Pages: /pages/keywords/<slug> via generator stubs or router virtual routes.
 */
declare(strict_types=1);

function jobTypesBuildingFile(): string
{
    return SITE_ROOT . '/data/job-types-building.json';
}

function jobTypesGasFile(): string
{
    return SITE_ROOT . '/data/job-types-gas.json';
}

/** @return array{family?:string,count?:int,jobs?:list<array<string,mixed>>} */
function jobTypesFamilyData(string $file): array
{
    static $cache = [];
    if (isset($cache[$file])) {
        return $cache[$file];
    }
    if (!is_file($file)) {
        return $cache[$file] = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $cache[$file] = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function jobTypesBuildingJobs(): array
{
    $jobs = jobTypesFamilyData(jobTypesBuildingFile())['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<array<string,mixed>> */
function jobTypesGasJobs(): array
{
    $jobs = jobTypesFamilyData(jobTypesGasFile())['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<array<string,mixed>> */
function jobTypesBuildingGasJobs(): array
{
    return array_merge(jobTypesBuildingJobs(), jobTypesGasJobs());
}

function jobTypesBuildingExpectedCount(): int
{
    return 247;
}

function jobTypesGasExpectedCount(): int
{
    return 223;
}

/**
 * Merge Building + Gas family jobs into the live keyword catalogue.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function jobTypesApplyBuildingGas(array $keywords): array
{
    $preserve = [];
    if (function_exists('seoIaWave1JobSlugs')) {
        foreach (seoIaWave1JobSlugs() as $wSlug) {
            $preserve[keywordSlug((string)$wSlug)] = true;
        }
    }
    foreach (jobTypesBuildingGasJobs() as $job) {
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
        if (function_exists('jobTypesSynthesize') && function_exists('jobTypesMergePreferExisting')) {
            $synth = jobTypesSynthesize($job, $base);
            $merged = jobTypesMergePreferExisting($base, $synth);
        } else {
            $merged = $base + $job;
        }
        $keepWave1 = isset($preserve[$slug]);
        foreach (['seo_title', 'h1', 'intro', 'body', 'meta_desc', 'seo_keywords', 'focus_points', 'faq', 'secondaries'] as $field) {
            if (!empty($job[$field]) && (!$keepWave1 || empty($merged[$field]))) {
                $merged[$field] = $job[$field];
            }
        }
        if (empty($merged['name'])) {
            $merged['name'] = (string)($job['name'] ?? keywordDisplayName($slug));
        }
        if (empty($merged['service'])) {
            $merged['service'] = (string)($job['service'] ?? 'electrical');
        }
        if (empty($merged['related'])) {
            $merged['related'] = (string)($job['related'] ?? $slug);
        }
        $merged['family'] = (string)($job['family'] ?? ($merged['family'] ?? ''));
        if (!empty($job['hub_only'])) {
            $merged['hub_only'] = true;
        }
        $keywords[$slug] = $merged;
    }
    return $keywords;
}
