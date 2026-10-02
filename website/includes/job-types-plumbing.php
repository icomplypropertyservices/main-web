<?php
/**
 * Plumbing job-type lane — 130 keyword hubs.
 * Subset of the sec-water-plumb family. Security and water are not loaded here.
 * Draft / non-production content. Prices stay POA.
 */
declare(strict_types=1);

function jobTypesPlumbingExpected(): int
{
    return 130;
}

function jobTypesPlumbingFile(): string
{
    return SITE_ROOT . '/data/job-types-plumbing.json';
}

function jobTypesPlumbingStubDir(): string
{
    return SITE_ROOT . '/pages/keywords';
}

/** @return array{count?:int,family?:string,jobs?:list<array<string,mixed>>} */
function jobTypesPlumbingData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = jobTypesPlumbingFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function jobTypesPlumbingJobs(): array
{
    $jobs = jobTypesPlumbingData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return array<string,array<string,mixed>> */
function jobTypesPlumbingBySlug(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    foreach (jobTypesPlumbingJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug !== '') {
            $map[$slug] = $job;
        }
    }
    return $map;
}

/**
 * Overlay the 130 plumbing jobs onto the keyword catalogue.
 * Lane copy wins over thin generated rows already in keywords.json.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function jobTypesApplyPlumbing(array $keywords): array
{
    foreach (jobTypesPlumbingJobs() as $job) {
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
        $keywords[$slug] = array_merge($base, [
            'name' => (string)($job['name'] ?? keywordDisplayName($slug)),
            'service' => 'plumbing',
            'related' => keywordSlug((string)($job['related'] ?? $slug)),
            'intro' => (string)($job['intro'] ?? ''),
            'body' => (string)($job['body'] ?? ''),
            'meta_desc' => (string)($job['meta_desc'] ?? ''),
            'seo_keywords' => (string)($job['seo_keywords'] ?? ''),
            'seo_title' => (string)($job['seo_title'] ?? ''),
            'h1' => (string)($job['h1'] ?? ''),
            'focus_points' => $job['focus_points'] ?? [],
            'faq' => $job['faq'] ?? [],
            'secondaries' => $job['secondaries'] ?? [],
            'peers' => $job['peers'] ?? [],
            'cluster' => (string)($job['cluster'] ?? ''),
            'family' => 'plumbing',
            'plumbing_lane' => true,
        ]);
    }
    return $keywords;
}

function jobTypesPlumbingStubContents(string $slug): string
{
    $slug = keywordSlug($slug);
    $export = var_export($slug, true);
    return "<?php\n"
        . "/** Plumbing job-type hub — php website/bin/generate-plumbing-job-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$export});\n";
}

/**
 * Hub links for one plumbing keyword page.
 */
function jobTypesPlumbingPageHubHtml(string $slug, array $meta): string
{
    $slug = keywordSlug($slug);
    $keywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $links = [
        [url('/pages/services/index.php') . '#construction', 'Construction & Fit-Out hub'],
        [url('/pages/services/plumbing.php') . '#plumbing-job-types', 'Plumbing service hub'],
        [url('/pages/keywords/index.php'), 'Keyword guide hub'],
        [url('/pages/areas/index.php'), 'Area hubs'],
    ];
    $related = keywordSlug((string)($meta['related'] ?? ''));
    if ($related !== '' && $related !== $slug) {
        $relatedName = (string)($keywords[$related]['name'] ?? $meta['related'] ?? $related);
        $links[] = [url('/pages/keywords/' . $related . '.php'), 'Related: ' . $relatedName];
    }
    $peers = $meta['peers'] ?? [];
    if (is_array($peers)) {
        foreach ($peers as $peer) {
            $peer = keywordSlug((string)$peer);
            if ($peer === '' || $peer === $slug) {
                continue;
            }
            $peerName = (string)($keywords[$peer]['name'] ?? keywordDisplayName($peer));
            $links[] = [url('/pages/keywords/' . $peer . '.php'), $peerName];
        }
    }

    $html = '<nav class="mt-6 flex flex-wrap gap-2" data-plumbing-hubs="1" aria-label="Plumbing hubs">';
    foreach ($links as [$href, $label]) {
        $html .= '<a href="' . $h($href) . '" class="px-3 py-1.5 bg-[#0B1F3A] text-white rounded-full text-xs font-semibold hover:bg-[#ff6b00]">'
            . $h($label) . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

/**
 * Full job-type index for the plumbing service hub.
 */
function jobTypesPlumbingServiceIndexHtml(): string
{
    $jobs = jobTypesPlumbingJobs();
    if (!$jobs) {
        return '';
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $html = '<div id="plumbing-job-types" class="mt-8 scroll-mt-24" data-plumbing-job-index="1">';
    $html .= '<h3 class="text-xl font-semibold text-black">Plumbing job types</h3>';
    $html .= '<p class="mt-2 text-sm text-zinc-600 max-w-2xl">All ' . count($jobs)
        . ' plumbing guides. Each one links back here, to the area hubs, and to a related job. Price on application after scope.</p>';
    $html .= '<div class="mt-4 flex flex-wrap gap-2">';
    foreach ($jobs as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $name = (string)($job['name'] ?? keywordDisplayName($slug));
        $html .= '<a href="' . $h(url('/pages/keywords/' . $slug . '.php')) . '" class="px-3 py-1.5 bg-white border border-zinc-200 rounded-full text-xs font-medium text-zinc-800 hover:border-[#ff6b00] hover:text-[#ff6b00]">'
            . $h($name) . '</a>';
    }
    $html .= '</div></div>';
    return $html;
}
