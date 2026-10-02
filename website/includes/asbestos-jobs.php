<?php
/**
 * Asbestos survey + awareness job lane.
 * Pages: /pages/asbestos-jobs and /pages/keywords/<slug>.
 * POA only. Licensed removal is by others. No training-body or lab claims.
 */
declare(strict_types=1);

function asbestosJobsFile(): string
{
    return SITE_ROOT . '/data/asbestos-jobs.json';
}

function asbestosJobsExpectedCount(): int
{
    return 36;
}

/** @return array{count?:int,lanes?:array<string,int>,jobs?:list<array<string,mixed>>} */
function asbestosJobsData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = asbestosJobsFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function asbestosJobsJobs(): array
{
    $jobs = asbestosJobsData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function asbestosJobsSlugs(): array
{
    $out = [];
    foreach (asbestosJobsJobs() as $job) {
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

/**
 * @return array{survey:list<array<string,mixed>>,awareness:list<array<string,mixed>>}
 */
function asbestosJobsByLane(): array
{
    $out = ['survey' => [], 'awareness' => []];
    foreach (asbestosJobsJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $lane = (string)($job['lane'] ?? '');
        if (!isset($out[$lane])) {
            continue;
        }
        $out[$lane][] = $job;
    }
    return $out;
}

function asbestosJobsHubPath(): string
{
    return '/pages/asbestos-jobs';
}

function asbestosJobsStubPath(string $slug): string
{
    return SITE_ROOT . '/pages/keywords/' . keywordSlug($slug) . '.php';
}

function asbestosJobsStubPhp(string $slug): string
{
    $slugExport = var_export(keywordSlug($slug), true);
    return "<?php\n"
        . "/** Asbestos survey/awareness hub — must stay 200 indexable (Jack: never prune keywords). */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
}

/**
 * Merge the lane into the live keyword catalogue.
 * Existing keywords.json copy wins so the eight established hubs stay as written.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function asbestosJobsApply(array $keywords): array
{
    foreach (asbestosJobsJobs() as $job) {
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
        $merged = $base;
        foreach (['name', 'related', 'intro', 'body', 'meta_desc', 'seo_keywords', 'seo_title', 'h1', 'focus_points', 'faq'] as $field) {
            $current = $merged[$field] ?? null;
            $empty = $current === null || $current === '' || $current === [];
            if ($empty && isset($job[$field]) && $job[$field] !== '' && $job[$field] !== []) {
                $merged[$field] = $job[$field];
            }
        }
        if (empty($merged['name'])) {
            $merged['name'] = (string)($job['name'] ?? keywordDisplayName($slug));
        }
        if (empty($merged['related'])) {
            $merged['related'] = keywordSlug((string)($job['related'] ?? 'asbestos-survey'));
        }
        $merged['service'] = 'asbestos-survey';
        $merged['asbestos_lane'] = (string)($job['lane'] ?? '');
        $body = (string)($merged['body'] ?? '');
        if (!preg_match('/licensed removal|removal is by others|not this service/i', $body)) {
            $body = rtrim($body) . ' Licensed asbestos removal is by others.';
        }
        if (($merged['asbestos_lane'] ?? '') === 'awareness'
            && !str_contains($body, 'not an accredited training certificate')) {
            $body = rtrim($body) . ' This briefing is not an accredited training certificate and does not replace a management or refurbishment survey.';
        }
        $merged['body'] = $body;
        $keywords[$slug] = $merged;
    }
    return $keywords;
}
