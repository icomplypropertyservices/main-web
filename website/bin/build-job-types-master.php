#!/usr/bin/env php
<?php
/**
 * Build website/data/job-types-master.json — exactly 1,753 unique job slugs.
 * Existing catalogue first, then extras, then deterministic service fillers.
 *
 * Usage: php website/bin/build-job-types-master.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

const JOB_TYPES_MASTER_TARGET = 1753;

$existing = [];
$raw = loadJsonData('keywords', []);
foreach ($raw as $slug => $meta) {
    if (!is_array($meta)) {
        continue;
    }
    $slug = keywordSlug((string)$slug);
    if ($slug === '') {
        continue;
    }
    $existing[$slug] = [
        'slug' => $slug,
        'name' => (string)($meta['name'] ?? keywordDisplayName($slug)),
        'service' => areaSlug((string)($meta['service'] ?? 'electrical')),
        'related' => keywordSlug((string)($meta['related'] ?? $slug)),
    ];
}
if (function_exists('seoIaJobs')) {
    foreach (seoIaJobs() as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $slug = keywordSlug((string)$slug);
        if ($slug === '' || isset($existing[$slug])) {
            continue;
        }
        $existing[$slug] = [
            'slug' => $slug,
            'name' => (string)($meta['name'] ?? keywordDisplayName($slug)),
            'service' => areaSlug((string)($meta['service'] ?? 'electrical')),
            'related' => keywordSlug((string)($meta['related'] ?? $slug)),
        ];
    }
}

$jobs = $existing;
$extrasFile = SITE_ROOT . '/data/job-types-extras.json';
if (is_file($extrasFile)) {
    $extraData = json_decode((string)file_get_contents($extrasFile), true);
    foreach (($extraData['jobs'] ?? []) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = keywordSlug((string)($row['slug'] ?? $row['name'] ?? ''));
        if ($slug === '' || isset($jobs[$slug])) {
            continue;
        }
        $jobs[$slug] = [
            'slug' => $slug,
            'name' => (string)($row['name'] ?? keywordDisplayName($slug)),
            'service' => areaSlug((string)($row['service'] ?? 'electrical')),
            'related' => keywordSlug((string)($row['related'] ?? $slug)),
        ];
    }
}

$services = getServices();
$fillers = jobTypesFillerCandidates($services, $jobs);
foreach ($fillers as $row) {
    if (count($jobs) >= JOB_TYPES_MASTER_TARGET) {
        break;
    }
    $slug = $row['slug'];
    if (isset($jobs[$slug])) {
        continue;
    }
    $jobs[$slug] = $row;
}

if (count($jobs) < JOB_TYPES_MASTER_TARGET) {
    fwrite(STDERR, 'Only ' . count($jobs) . ' unique slugs; need ' . JOB_TYPES_MASTER_TARGET . "\n");
    exit(1);
}

// Prefer existing catalogue order, then extras alphabetically, trim to 1753.
$existingSlugs = array_keys($existing);
$rest = array_values(array_diff(array_keys($jobs), $existingSlugs));
sort($rest, SORT_STRING);
$ordered = array_merge($existingSlugs, $rest);
if (count($ordered) > JOB_TYPES_MASTER_TARGET) {
    $ordered = array_slice($ordered, 0, JOB_TYPES_MASTER_TARGET);
}

$outJobs = [];
$seen = [];
foreach ($ordered as $slug) {
    if (isset($seen[$slug]) || !isset($jobs[$slug])) {
        continue;
    }
    $seen[$slug] = true;
    $row = $jobs[$slug];
    if ($row['related'] === '' || $row['related'] === $slug) {
        $row['related'] = jobTypesPickRelated($row['service'], $slug, $jobs);
    }
    $outJobs[] = $row;
}

if (count($outJobs) !== JOB_TYPES_MASTER_TARGET) {
    fwrite(STDERR, 'Built ' . count($outJobs) . " jobs, expected " . JOB_TYPES_MASTER_TARGET . "\n");
    exit(1);
}

$payload = [
    'count' => JOB_TYPES_MASTER_TARGET,
    'generated' => gmdate('c'),
    'jobs' => $outJobs,
];
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "JSON encode failed\n");
    exit(1);
}
$file = SITE_ROOT . '/data/job-types-master.json';
file_put_contents($file, $json . "\n");
$csvFile = SITE_ROOT . '/data/job-types-master.csv';
$fh = fopen($csvFile, 'w');
if ($fh === false) {
    fwrite(STDERR, "Could not write {$csvFile}\n");
    exit(1);
}
fputcsv($fh, ['slug', 'name', 'service', 'related']);
foreach ($outJobs as $row) {
    fputcsv($fh, [
        $row['slug'],
        $row['name'],
        $row['service'],
        $row['related'] ?? $row['slug'],
    ]);
}
fclose($fh);
echo 'Wrote ' . count($outJobs) . " jobs → {$file}\n";
echo 'Wrote CSV → ' . $csvFile . "\n";
echo 'existing=' . count($existing) . ' added=' . (count($outJobs) - count($existing)) . "\n";
exit(0);

/**
 * @param array<string, string> $services
 * @param array<string, array<string, string>> $have
 * @return list<array{slug:string,name:string,service:string,related:string}>
 */
function jobTypesFillerCandidates(array $services, array $have): array
{
    $property = [
        'Apartment Block', 'HMO', 'Office', 'Warehouse', 'Retail Unit', 'Care Home',
        'School', 'Workshop', 'Student Let', 'Communal Stair',
    ];
    $actions = [
        'Survey', 'Remedial Works', 'Maintenance Visit', 'Aftercare Visit',
        'Commissioning', 'Certificate Support', 'Inspection', 'Upgrade',
    ];
    $out = [];
    foreach ($services as $svc => $svcName) {
        foreach ($actions as $action) {
            foreach ($property as $place) {
                $name = $svcName . ' ' . $action . ' — ' . $place;
                $slug = keywordSlug($svc . '-' . $action . '-' . $place);
                if ($slug === '' || isset($have[$slug])) {
                    continue;
                }
                $out[] = [
                    'slug' => $slug,
                    'name' => $name,
                    'service' => $svc,
                    'related' => $svc,
                ];
            }
        }
    }
    return $out;
}

/**
 * @param array<string, array<string, string>> $jobs
 */
function jobTypesPickRelated(string $service, string $slug, array $jobs): string
{
    foreach ($jobs as $other => $row) {
        if ($other !== $slug && ($row['service'] ?? '') === $service) {
            return $other;
        }
    }
    return $slug;
}
