<?php
/**
 * Water / WRAS / drinking water job lane.
 * Exactly 120 keyword hubs under /pages/keywords/<slug>, parent service water-wras.
 * Source: website/data/job-types-water.json from bin/build-water-job-pages.py.
 * POA only. No WRAS scheme membership, laboratory accreditation, or catalogue prices.
 */
declare(strict_types=1);

function waterJobTypesExpected(): array
{
    return [
        'wras' => 40,
        'drinking-water' => 40,
        'water-fittings' => 40,
        'total' => 120,
    ];
}

function waterJobTypesFile(): string
{
    return SITE_ROOT . '/data/job-types-water.json';
}

function waterJobTypesStubDir(): string
{
    return SITE_ROOT . '/pages/keywords';
}

function waterJobTypesReset(): void
{
    waterJobTypesData(true);
}

/** @return array{count?:int,lane?:string,sublanes?:array<string,int>,jobs?:list<array<string,mixed>>} */
function waterJobTypesData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = waterJobTypesFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function waterJobTypesJobs(): array
{
    $jobs = waterJobTypesData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return array<string, list<array<string,mixed>>> */
function waterJobTypesBySublane(): array
{
    $out = ['wras' => [], 'drinking-water' => [], 'water-fittings' => []];
    foreach (waterJobTypesJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $lane = (string)($job['sublane'] ?? '');
        if (isset($out[$lane])) {
            $out[$lane][] = $job;
        }
    }
    return $out;
}

/**
 * Merge the 120-job lane into the live keyword catalogue.
 * Existing keywords.json rows win on collision so this lane cannot overwrite them.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function waterJobsApplyOverlay(array $keywords): array
{
    foreach (waterJobTypesJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        if (isset($keywords[$slug])) {
            continue;
        }
        $keywords[$slug] = [
            'name' => (string)($job['name'] ?? keywordDisplayName($slug)),
            'service' => areaSlug((string)($job['service'] ?? 'water-wras')),
            'related' => keywordSlug((string)($job['related'] ?? $slug)),
            'intro' => (string)($job['intro'] ?? ''),
            'body' => (string)($job['body'] ?? ''),
            'meta_desc' => (string)($job['meta_desc'] ?? ''),
            'seo_keywords' => (string)($job['seo_keywords'] ?? ''),
            'focus_points' => is_array($job['focus_points'] ?? null) ? $job['focus_points'] : [],
            'faq' => is_array($job['faq'] ?? null) ? $job['faq'] : [],
            'seo_title' => (string)($job['seo_title'] ?? ''),
            'h1' => (string)($job['h1'] ?? ''),
            'quote_note' => (string)($job['quote_note'] ?? ''),
            'sublane' => (string)($job['sublane'] ?? ''),
            'family' => 'water',
            'water_lane' => true,
        ];
    }
    return $keywords;
}

function waterJobTypesStubContents(string $slug): string
{
    $slug = keywordSlug($slug);
    $export = var_export($slug, true);
    return "<?php\n"
        . "/** AUTO-GENERATED Water / WRAS / drinking water job stub — python3 website/bin/build-water-job-pages.py */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$export});\n";
}

/**
 * Same-sublane chips for a water job page.
 */
function waterJobRelatedHtml(string $slug, int $limit = 8): string
{
    $slug = keywordSlug($slug);
    $current = null;
    foreach (waterJobTypesJobs() as $job) {
        if (keywordSlug((string)($job['slug'] ?? '')) === $slug) {
            $current = $job;
            break;
        }
    }
    if (!is_array($current)) {
        return '';
    }
    $sub = (string)($current['sublane'] ?? '');
    $html = '<div class="flex flex-wrap gap-2">';
    $shown = 0;
    foreach (waterJobTypesBySublane()[$sub] ?? [] as $job) {
        $other = keywordSlug((string)($job['slug'] ?? ''));
        if ($other === '' || $other === $slug) {
            continue;
        }
        $href = htmlspecialchars(url('/pages/keywords/' . $other . '.php'), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string)($job['name'] ?? keywordDisplayName($other)), ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="px-3 py-1.5 bg-white border-2 border-zinc-300 rounded-full text-xs font-semibold text-zinc-900 hover:border-[#ff6b00] hover:text-[#ff6b00]">'
            . $label . '</a>';
        $shown++;
        if ($limit > 0 && $shown >= $limit) {
            break;
        }
    }
    $html .= '</div>';
    return $html;
}
