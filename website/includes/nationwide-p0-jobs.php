<?php
/**
 * Nationwide 3-line P0 job hubs (wave W1a).
 * One canonical URL per slug: keyword hub wins when that slug is already published.
 */
declare(strict_types=1);

function nationwideP0Rows(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $rows = [];
    $file = SITE_ROOT . '/data/nationwide-p0.csv';
    if (!is_file($file)) {
        return $rows;
    }
    $handle = fopen($file, 'rb');
    if ($handle === false) {
        return $rows;
    }
    $header = fgetcsv($handle);
    if (!is_array($header)) {
        fclose($handle);
        return $rows;
    }
    while (($data = fgetcsv($handle)) !== false) {
        if (!is_array($data) || !isset($data[0])) {
            continue;
        }
        $row = [];
        foreach ($header as $i => $key) {
            $row[(string)$key] = (string)($data[$i] ?? '');
        }
        $slug = function_exists('keywordSlug') ? keywordSlug($row['slug'] ?? '') : (string)($row['slug'] ?? '');
        if ($slug === '') {
            continue;
        }
        $row['slug'] = $slug;
        $rows[$slug] = $row;
    }
    fclose($handle);
    return $rows;
}

function nationwideP0Jobs(): array
{
    static $jobs = null;
    if ($jobs !== null) {
        return $jobs;
    }
    $file = SITE_ROOT . '/data/nationwide-p0-jobs.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
    $jobs = is_array($decoded) ? $decoded : [];
    return $jobs;
}

/**
 * P0 slugs whose keyword hub is the canonical URL even when that hub
 * is published by the nationwide keywords pack rather than this branch.
 *
 * @return array<string, true>
 */
function nationwideP0KeywordCanonicalSlugs(): array
{
    return [
        'barrier-installation' => true,
        'barrier-maintenance' => true,
        'barrier-repair' => true,
        'barrier-service' => true,
        'car-park-barrier-installation' => true,
        'emergency-aov-repair' => true,
        'rising-arm-barrier-installation' => true,
        'smoke-ventilation-installation' => true,
        'vehicle-barrier-installation' => true,
    ];
}

/**
 * W1a P0 slugs whose job page stays canonical even though a later keyword
 * family (#113 fire-alarm-installer) added a keyword entry for the same slug.
 * Keeps W1a at 52 job hubs + 38 redirects.
 *
 * @return array<string, true>
 */
function nationwideP0JobCanonicalSlugs(): array
{
    return [
        'addressable-fire-alarm-installation' => true,
        'conventional-fire-alarm-installation' => true,
    ];
}

/**
 * Keyword URL when this P0 slug already has a keyword hub. Null when the job page is canonical.
 */
function nationwideP0KeywordRedirect(string $slug): ?string
{
    $slug = function_exists('keywordSlug') ? keywordSlug($slug) : $slug;
    if ($slug === '' || !isset(nationwideP0Rows()[$slug])) {
        return null;
    }
    if (isset(nationwideP0JobCanonicalSlugs()[$slug])) {
        return null;
    }
    if (isset(nationwideP0KeywordCanonicalSlugs()[$slug])) {
        return '/pages/keywords/' . $slug;
    }
    if (!function_exists('getMajorKeywords')) {
        return null;
    }
    $keywords = getMajorKeywords();
    if (!isset($keywords[$slug])) {
        return null;
    }
    return '/pages/keywords/' . $slug;
}

function nationwideP0RedirectLines(): string
{
    $lines = "# W1a P0 job slugs that already have a keyword hub. One canonical URL.\n";
    foreach (nationwideP0Rows() as $slug => $_row) {
        $target = nationwideP0KeywordRedirect($slug);
        if ($target === null) {
            continue;
        }
        $lines .= "/pages/jobs/{$slug}    {$target}    301!\n";
        $lines .= "/pages/jobs/{$slug}/   {$target}    301!\n";
    }
    return $lines;
}

/**
 * @return list<array{href:string,label:string,line:string,card:string}>
 */
function nationwideP0IndexItems(): array
{
    $items = [];
    $jobs = nationwideP0Jobs();
    $keywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
    foreach (nationwideP0Rows() as $slug => $row) {
        $target = nationwideP0KeywordRedirect($slug);
        if ($target !== null) {
            $label = trim((string)($keywords[$slug]['name'] ?? ''));
            if ($label === '') {
                $label = ucwords(str_replace('-', ' ', $slug));
            }
            $items[] = [
                'href' => $target,
                'label' => $label,
                'line' => (string)($row['line'] ?? ''),
                'card' => 'Published guide for this job. Price on application after survey.',
            ];
            continue;
        }
        $job = $jobs[$slug] ?? null;
        if (!is_array($job)) {
            continue;
        }
        $items[] = [
            'href' => '/pages/jobs/' . $slug,
            'label' => (string)($job['h1'] ?? $slug),
            'line' => (string)($row['line'] ?? ''),
            'card' => (string)($job['card'] ?? 'Price on application after survey.'),
        ];
    }
    return $items;
}

function nationwideP0Render(string $slug): void
{
    $slug = keywordSlug($slug);
    $target = nationwideP0KeywordRedirect($slug);
    if ($target !== null) {
        header('Location: ' . url($target), true, 301);
        icomplyRequestExit();
        return;
    }
    $job = nationwideP0Jobs()[$slug] ?? null;
    if (!is_array($job)) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }
    require_once SITE_ROOT . '/includes/job-article.php';
    renderJobArticle($job);
}
