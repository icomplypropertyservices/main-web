<?php
/**
 * Security systems job lane — intruder and alarm jobs only.
 *
 * Owns /pages/keywords/<slug> for the 62-row intruder master.
 * CCTV, access control, door entry and intercoms stay outside this lane.
 */
declare(strict_types=1);

function securitySystemsExpectedCount(): int
{
    return 62;
}

function securitySystemsCategory(): string
{
    return 'Security systems';
}

function securitySystemsLane(): string
{
    return 'security-systems';
}

function securitySystemsService(): string
{
    return 'intruder-alarm';
}

/** @return list<string> */
function securitySystemsExcludedServices(): array
{
    return ['cctv', 'access-control', 'door-entry', 'intercoms', 'nurse-call'];
}

function securitySystemsFile(): string
{
    return SITE_ROOT . '/data/security-systems-jobs.json';
}

function securitySystemsOutputDir(): string
{
    return SITE_ROOT . '/pages/keywords';
}

function securitySystemsStubPath(string $slug): string
{
    return securitySystemsOutputDir() . '/' . keywordSlug($slug) . '.php';
}

function securitySystemsReset(): void
{
    securitySystemsData(true);
}

/** @return array{count?:int,category?:string,lane?:string,jobs?:list<array<string,mixed>>} */
function securitySystemsData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = securitySystemsFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string,mixed>> */
function securitySystemsJobs(): array
{
    $jobs = securitySystemsData()['jobs'] ?? [];
    return is_array($jobs) ? $jobs : [];
}

/** @return list<string> */
function securitySystemsSlugs(): array
{
    $out = [];
    foreach (securitySystemsJobs() as $job) {
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

function securitySystemsSlugIsOutOfLane(string $slug): bool
{
    $slug = keywordSlug($slug);
    if ($slug === '') {
        return true;
    }
    if (preg_match('/(?:^|-)cctv(?:-|$)/', $slug)) {
        return true;
    }
    if (str_contains($slug, 'access-control')) {
        return true;
    }
    if (str_contains($slug, 'door-entry')) {
        return true;
    }
    if (preg_match('/(?:^|-)intercoms?(?:-|$)/', $slug)) {
        return true;
    }
    if (str_contains($slug, 'nurse-call')) {
        return true;
    }
    return false;
}

function securitySystemsFaqJsonLd(array $faqs): string
{
    $main = [];
    foreach ($faqs as $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $q = trim((string)$faq[0]);
        $a = trim((string)$faq[1]);
        if ($q === '' || $a === '') {
            continue;
        }
        $main[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $a,
            ],
        ];
    }
    if (!$main) {
        return '';
    }
    $json = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $main,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return is_string($json) ? $json : '';
}

/**
 * Merge the security-systems lane into the live keyword catalogue.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function securitySystemsApplyOverlay(array $keywords): array
{
    foreach (securitySystemsJobs() as $job) {
        if (!is_array($job)) {
            continue;
        }
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '' || securitySystemsSlugIsOutOfLane($slug)) {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $name = trim((string)($base['name'] ?? $job['name'] ?? keywordDisplayName($slug)));
        $row = $base;
        $row['name'] = $name;
        $row['service'] = securitySystemsService();
        $row['related'] = keywordSlug((string)($base['related'] ?? $job['related'] ?? 'intruder-alarm-installation'));
        $row['category'] = securitySystemsCategory();
        $row['lane'] = securitySystemsLane();
        $row['status'] = (string)($job['status'] ?? 'live');
        $row['service_type'] = securitySystemsService();
        $row['h1'] = trim((string)($job['h1'] ?? $name));
        $row['seo_title'] = trim((string)($job['seo_title'] ?? ($name . ' | Security systems | North West')));
        foreach (['intro', 'body', 'meta_desc', 'seo_keywords'] as $field) {
            if (empty($row[$field]) && !empty($job[$field]) && is_string($job[$field])) {
                $row[$field] = $job[$field];
            }
        }
        if (empty($row['focus_points']) && !empty($job['focus_points']) && is_array($job['focus_points'])) {
            $row['focus_points'] = $job['focus_points'];
        }
        if (empty($row['faq']) && !empty($job['faq']) && is_array($job['faq'])) {
            $row['faq'] = $job['faq'];
        }
        $keywords[$slug] = $row;
    }
    return $keywords;
}

function securitySystemsStubContents(string $slug): string
{
    $slug = keywordSlug($slug);
    $export = var_export($slug, true);
    return "<?php\n"
        . "/** AUTO-GENERATED stub — php bin/generate-security-systems-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$export});\n";
}
