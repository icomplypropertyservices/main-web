<?php
/**
 * Commercial property-compliance job lane.
 * Catalogue: website/data/commercial-jobs.json
 * Stubs: website/pages/commercial/{slug}.php
 * Hub: website/pages/commercial.php
 *
 * Non-production content lane. Quotes are POA after scope. No published prices.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

function commercialJobsFile(): string
{
    return SITE_ROOT . '/data/commercial-jobs.json';
}

function commercialJobsStubDir(): string
{
    return SITE_ROOT . '/pages/commercial';
}

/** @return array{count?:int,lane?:string,jobs?:list<array<string,mixed>>} */
function commercialJobsData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = commercialJobsFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function commercialJobs(): array
{
    $jobs = commercialJobsData()['jobs'] ?? [];
    return is_array($jobs) ? array_values(array_filter($jobs, 'is_array')) : [];
}

function commercialJobsExpectedCount(): int
{
    $count = commercialJobsData()['count'] ?? 0;
    return is_int($count) ? $count : (int)$count;
}

/** @return array<string,mixed>|null */
function commercialJob(string $slug): ?array
{
    $slug = keywordSlug($slug);
    foreach (commercialJobs() as $job) {
        if (keywordSlug((string)($job['slug'] ?? '')) === $slug) {
            return $job;
        }
    }
    return null;
}

/**
 * @return array<string,list<array<string,mixed>>>
 */
function commercialJobGroups(): array
{
    $out = [];
    foreach (commercialJobs() as $job) {
        $group = trim((string)($job['group'] ?? 'Commercial'));
        if ($group === '') {
            $group = 'Commercial';
        }
        $out[$group][] = $job;
    }
    return $out;
}

function commercialJobPath(string $slug): string
{
    return '/pages/commercial/' . keywordSlug($slug);
}

function commercialJobStubPath(string $slug): string
{
    return commercialJobsStubDir() . '/' . keywordSlug($slug) . '.php';
}

function renderCommercialJobPage(string $slug): void
{
    $job = commercialJob($slug);
    if ($job === null) {
        http_response_code(404);
        echo 'Commercial job not found';
        icomplyRequestExit();
        return;
    }
    $slug = keywordSlug((string)$job['slug']);
    $services = getServices();
    $serviceSlug = areaSlug((string)($job['service'] ?? ''));
    $serviceName = $services[$serviceSlug] ?? keywordDisplayName($serviceSlug);
    $keywordSlug = keywordSlug((string)($job['keyword'] ?? ''));
    $keywords = getMajorKeywords();
    $keywordHref = ($keywordSlug !== '' && isset($keywords[$keywordSlug]))
        ? url('/pages/keywords/' . $keywordSlug . '.php')
        : '';

    $related = [];
    foreach ((array)($job['related'] ?? []) as $rel) {
        $relJob = commercialJob((string)$rel);
        if ($relJob === null) {
            continue;
        }
        $related[] = $relJob;
    }

    $GLOBALS['COMMERCIAL_JOB'] = $job;
    $GLOBALS['COMMERCIAL_SERVICE_NAME'] = $serviceName;
    $GLOBALS['COMMERCIAL_SERVICE_SLUG'] = $serviceSlug;
    $GLOBALS['COMMERCIAL_KEYWORD_HREF'] = $keywordHref;
    $GLOBALS['COMMERCIAL_RELATED'] = $related;

    $pageTitle = trim((string)($job['title'] ?? $job['name'] ?? 'Commercial compliance'));
    $metaDesc = trim((string)($job['meta'] ?? ''));
    $metaKeywords = trim((string)($job['seo_keywords'] ?? ''));
    $canonicalUrl = url(commercialJobPath($slug) . '.php');
    $ogImage = url('/assets/images/services/' . ($serviceSlug !== '' ? $serviceSlug : 'fire-alarms') . '.jpg');

    require SITE_ROOT . '/templates/commercial-job.php';
}
