<?php
/**
 * Fire alarms job lane — install, maintain and service keyword pages plus the hub.
 * Catalogue: website/data/fire-alarms-lane.json (php website/bin/build-fire-alarms-lane.php).
 */
declare(strict_types=1);

function fireAlarmsLaneFile(): string
{
    return SITE_ROOT . '/data/fire-alarms-lane.json';
}

function fireAlarmsLaneHubPath(): string
{
    return '/pages/jobs/fire-alarms.php';
}

function fireAlarmsLaneStubPath(string $slug): string
{
    return SITE_ROOT . '/pages/keywords/' . keywordSlug($slug) . '.php';
}

function fireAlarmsLaneReset(): void
{
    fireAlarmsLaneData(true);
}

/** @return array<string, mixed> */
function fireAlarmsLaneData(bool $reset = false): array
{
    static $data = null;
    if ($reset) {
        $data = null;
    }
    if ($data !== null) {
        return $data;
    }
    $file = fireAlarmsLaneFile();
    if (!is_file($file)) {
        return $data = [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return $data = (is_array($decoded) ? $decoded : []);
}

/** @return list<array<string, mixed>> */
function fireAlarmsLaneJobs(): array
{
    $jobs = fireAlarmsLaneData()['jobs'] ?? [];
    return is_array($jobs) ? array_values(array_filter($jobs, 'is_array')) : [];
}

function fireAlarmsLaneIsExcluded(string $slug): bool
{
    $slug = keywordSlug($slug);
    if ($slug === '') {
        return true;
    }
    if (str_contains($slug, 'near-me')) {
        return true;
    }
    return in_array($slug, [
        'fire-door-inspection',
        'fire-risk-assessment-support',
        'fire-safety-order-compliance',
        'fire-safety-certificate',
    ], true);
}

/**
 * Install = new work and systems. Maintain = planned visits. Service = faults, tests, certificates.
 */
function fireAlarmsLaneClassify(string $slug): string
{
    $slug = keywordSlug($slug);
    if (preg_match('/(?:^|-)(?:maintenance|ppm|logbook)(?:-|$)/', $slug)) {
        return 'maintain';
    }
    if (
        str_contains($slug, 'battery-replacement')
        || str_contains($slug, 'weekly-test')
        || str_contains($slug, 'loop-tester')
        || str_contains($slug, 'compliance')
        || str_contains($slug, 'call-out')
        || str_contains($slug, 'false-alarm')
    ) {
        return 'service';
    }
    if (preg_match('/(?:^|-)(?:installation|install|commissioning|design|upgrade|replacement)(?:-|$)/', $slug)) {
        return 'install';
    }
    if (preg_match('/(?:^|-)(?:service|servicing|repair|fault|testing|test|inspection|certificate|certification|engineer|monitoring)(?:-|$)/', $slug)) {
        return 'service';
    }
    return 'install';
}

function fireAlarmsLaneLabel(string $lane): string
{
    return match ($lane) {
        'install' => 'Install',
        'maintain' => 'Maintain',
        'service' => 'Service',
        default => '',
    };
}

/** @return array<string, int> */
function fireAlarmsLaneCounts(): array
{
    $counts = ['install' => 0, 'maintain' => 0, 'service' => 0];
    foreach (fireAlarmsLaneJobs() as $job) {
        $lane = (string)($job['lane'] ?? '');
        if (isset($counts[$lane])) {
            $counts[$lane]++;
        }
    }
    return $counts;
}

function fireAlarmsLaneForSlug(string $slug): ?string
{
    $slug = keywordSlug($slug);
    foreach (fireAlarmsLaneJobs() as $job) {
        if (keywordSlug((string)($job['slug'] ?? '')) === $slug) {
            $lane = (string)($job['lane'] ?? '');
            return $lane !== '' ? $lane : null;
        }
    }
    return null;
}

/**
 * @param list<array<string, mixed>> $jobs
 * @return list<array<string, mixed>>
 */
function fireAlarmsLaneSort(array $jobs, string $lane): array
{
    $priority = [
        'install' => [
            'fire-alarm-installation',
            'fire-alarm-design',
            'fire-alarm-commissioning',
            'fire-alarm-upgrade',
            'fire-alarm-replacement',
            'domestic-fire-alarm-installation',
            'office-fire-alarm-installation',
            'commercial-fire-alarm',
            'addressable-fire-alarm-system',
            'addressable-fire-alarm',
            'wireless-fire-alarm',
        ],
        'maintain' => [
            'fire-alarm-maintenance',
            'fire-alarm-maintenance-contract',
            'fire-alarm-ppm',
            'bs-5839-maintenance',
            'multi-site-fire-alarm-maintenance',
            'fire-alarm-logbook',
        ],
        'service' => [
            'fire-alarm-servicing',
            'fire-alarm-service',
            'periodic-fire-alarm-service',
            'fire-alarm-repair',
            'fire-alarm-call-out',
            'false-alarm-investigation',
            'fire-alarm-fault-finding',
            'fire-alarm-testing',
            'fire-alarm-inspection',
            'fire-alarm-certification',
        ],
    ];
    $rank = array_flip($priority[$lane] ?? []);
    usort($jobs, static function (array $a, array $b) use ($rank): int {
        $as = (string)($a['slug'] ?? '');
        $bs = (string)($b['slug'] ?? '');
        $ar = $rank[$as] ?? 1000;
        $br = $rank[$bs] ?? 1000;
        if ($ar !== $br) {
            return $ar <=> $br;
        }
        return strcasecmp((string)($a['name'] ?? $as), (string)($b['name'] ?? $bs));
    });
    return $jobs;
}

/**
 * @return array<string, list<array<string, mixed>>>
 */
function fireAlarmsLaneGrouped(): array
{
    $grouped = ['install' => [], 'maintain' => [], 'service' => []];
    foreach (fireAlarmsLaneJobs() as $job) {
        $lane = (string)($job['lane'] ?? '');
        if (isset($grouped[$lane])) {
            $grouped[$lane][] = $job;
        }
    }
    foreach ($grouped as $lane => $jobs) {
        $grouped[$lane] = fireAlarmsLaneSort($jobs, $lane);
    }
    return $grouped;
}

/**
 * Merge lane jobs into the live keyword catalogue.
 * Existing copy is kept. Generic "Searching for professional…" intros and brand-new slugs take lane content.
 *
 * @param array<string, array<string, mixed>> $keywords
 * @return array<string, array<string, mixed>>
 */
function fireAlarmsLaneApplyOverlay(array $keywords): array
{
    foreach (fireAlarmsLaneJobs() as $job) {
        $slug = keywordSlug((string)($job['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $base = $keywords[$slug] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        $content = is_array($job['content'] ?? null) ? $job['content'] : [];
        $intro = (string)($base['intro'] ?? '');
        $replace = $base === [] || str_starts_with($intro, 'Searching for professional');
        if ($replace && $content !== []) {
            if ($base === []) {
                $base['name'] = (string)($job['name'] ?? keywordDisplayName($slug));
                $base['service'] = 'fire-alarms';
                $base['related'] = keywordSlug((string)($job['related'] ?? 'fire-alarm-installation'));
            }
            foreach (['intro', 'body', 'meta_desc', 'seo_keywords'] as $field) {
                if (!empty($content[$field]) && is_string($content[$field])) {
                    $base[$field] = $content[$field];
                }
            }
            if (!empty($content['focus_points']) && is_array($content['focus_points'])) {
                $base['focus_points'] = $content['focus_points'];
            }
            if (!empty($content['faq']) && is_array($content['faq'])) {
                $base['faq'] = $content['faq'];
            }
        }
        if (empty($base['name'])) {
            $base['name'] = (string)($job['name'] ?? keywordDisplayName($slug));
        }
        if (empty($base['service'])) {
            $base['service'] = 'fire-alarms';
        }
        if (empty($base['related'])) {
            $base['related'] = keywordSlug((string)($job['related'] ?? $slug));
        }
        $base['lane'] = (string)($job['lane'] ?? '');
        $base['fire_alarms_lane'] = true;
        $keywords[$slug] = $base;
    }
    return $keywords;
}
