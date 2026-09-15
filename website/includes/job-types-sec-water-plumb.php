<?php
/**
 * Security (164) + Water (130) + Plumbing (130) = 424 job-type slice.
 * Pages: /pages/keywords/<slug>
 */
declare(strict_types=1);

function jobTypesSecWaterPlumbExpected(): array
{
    return [
        'security' => 164,
        'water' => 130,
        'plumbing' => 130,
        'total' => 424,
    ];
}

function jobTypesSecWaterPlumbFile(): string
{
    return SITE_ROOT . '/data/job-types-sec-water-plumb.json';
}

function jobTypesSecWaterPlumbStubDir(): string
{
    return SITE_ROOT . '/pages/keywords';
}

/** @return array{count?:int,families?:array<string,int>,jobs?:list<array<string,mixed>>} */
function jobTypesSecWaterPlumbData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = jobTypesSecWaterPlumbFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function jobTypesSecWaterPlumbJobs(): array
{
    $jobs = jobTypesSecWaterPlumbData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return array<string, list<array<string,mixed>>> */
function jobTypesSecWaterPlumbByFamily(): array
{
    $out = ['security' => [], 'water' => [], 'plumbing' => []];
    foreach (jobTypesSecWaterPlumbJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $fam = (string)($job['family'] ?? '');
        if (isset($out[$fam])) {
            $out[$fam][] = $job;
        }
    }
    return $out;
}

/**
 * Merge the 424-job slice into the live keyword catalogue.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function jobTypesApplySecWaterPlumb(array $keywords): array
{
    foreach (jobTypesSecWaterPlumbJobs() as $job) {
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
        $synth = $job;
        if (function_exists('jobTypesSynthesize')) {
            $synth = jobTypesSynthesize($job, $base);
            foreach (['seo_title', 'h1', 'meta_desc', 'intro', 'body', 'faq', 'focus_points', 'secondaries', 'seo_keywords'] as $key) {
                if (!empty($job[$key])) {
                    $synth[$key] = $job[$key];
                }
            }
            $synth['family'] = (string)($job['family'] ?? $synth['family'] ?? '');
            $synth['service'] = areaSlug((string)($job['service'] ?? $synth['service'] ?? 'electrical'));
            $synth['name'] = (string)($job['name'] ?? $synth['name'] ?? keywordDisplayName($slug));
            $synth['related'] = keywordSlug((string)($job['related'] ?? $synth['related'] ?? $slug));
        }
        if (function_exists('jobTypesMergePreferExisting')) {
            $keywords[$slug] = jobTypesMergePreferExisting($base, $synth);
            // Family catalogue wins for unique title/H1/meta/FAQ on this slice.
            foreach (['seo_title', 'h1', 'meta_desc', 'intro', 'body', 'faq', 'focus_points', 'secondaries'] as $key) {
                if (!empty($job[$key])) {
                    $keywords[$slug][$key] = $job[$key];
                }
            }
        } else {
            $keywords[$slug] = array_merge($synth, array_filter($base, static fn($v) => $v !== null && $v !== '' && $v !== []));
            foreach (['seo_title', 'h1', 'meta_desc', 'intro', 'body', 'faq'] as $key) {
                if (!empty($job[$key])) {
                    $keywords[$slug][$key] = $job[$key];
                }
            }
        }
        $keywords[$slug]['family'] = (string)($job['family'] ?? '');
        $keywords[$slug]['sec_water_plumb'] = true;
    }
    return $keywords;
}

function jobTypesSecWaterPlumbStubContents(string $slug): string
{
    $slug = keywordSlug($slug);
    $export = var_export($slug, true);
    return "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-sec-water-plumb-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$export});\n";
}
