#!/usr/bin/env php
<?php
/**
 * Build the Fire job-type catalogue (exactly 390 slugs) and emit thin keyword stubs.
 *
 * Reconstructs Fire rows from existing keyword / extras / wave-1 data plus
 * standard verb expansions (installation, repair, maintenance, inspection,
 * testing, certification, …). HVAC/AHU and near-me doorway slugs are excluded.
 *
 * Usage: php website/bin/build-fire-job-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

const FIRE_JOB_TARGET = 390;
const FIRE_JOB_CATEGORY = 'Fire';
const FIRE_JOB_STATUS = 'live';

$jobs = fireJobCollectExactly(FIRE_JOB_TARGET);
if (count($jobs) !== FIRE_JOB_TARGET) {
    fwrite(STDERR, 'Built ' . count($jobs) . ' Fire jobs, expected ' . FIRE_JOB_TARGET . "\n");
    exit(1);
}

$payload = [
    'count' => FIRE_JOB_TARGET,
    'category' => FIRE_JOB_CATEGORY,
    'generated' => gmdate('c'),
    'jobs' => array_values($jobs),
];
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "JSON encode failed\n");
    exit(1);
}
$file = fireJobTypesFile();
file_put_contents($file, $json . "\n");

$outDir = fireJobTypesOutputDir();
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$written = 0;
foreach ($jobs as $job) {
    $slug = $job['slug'];
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED Fire job stub — php website/bin/build-fire-job-pages.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
    file_put_contents(fireJobTypesStubPath($slug), $stub);
    $written++;
}

echo 'Wrote ' . count($jobs) . " Fire jobs → {$file}\n";
echo "Wrote {$written} stubs → {$outDir}/<slug>.php\n";
echo 'fire_page_count=' . count($jobs) . ' expected=' . FIRE_JOB_TARGET . "\n";
exit(0);

/**
 * @return list<array{category:string,service_type:string,slug:string,status:string,name:string,related:string}>
 */
function fireJobCollectExactly(int $target): array
{
    $seen = [];
    $ordered = [];

    foreach (fireJobCandidateRows() as $row) {
        $slug = $row['slug'];
        if ($slug === '' || isset($seen[$slug]) || fireJobIsExcluded($slug)) {
            continue;
        }
        $seen[$slug] = true;
        $ordered[] = $row;
        if (count($ordered) >= $target) {
            break;
        }
    }

    return array_slice($ordered, 0, $target);
}

/**
 * Priority: existing Fire keywords, extras, wave-1 Fire jobs, then verb expansions.
 *
 * @return list<array{category:string,service_type:string,slug:string,status:string,name:string,related:string}>
 */
function fireJobCandidateRows(): array
{
    $fireServices = array_fill_keys(fireJobServiceSlugs(), true);
    $out = [];

    $raw = loadJsonData('keywords', []);
    foreach ($raw as $slug => $meta) {
        if (!is_array($meta)) {
            continue;
        }
        $service = areaSlug((string)($meta['service'] ?? ''));
        if (!isset($fireServices[$service])) {
            continue;
        }
        $slug = keywordSlug((string)$slug);
        $out[] = fireJobRow($slug, (string)($meta['name'] ?? keywordDisplayName($slug)), $service, (string)($meta['related'] ?? $slug));
    }

    $extrasFile = SITE_ROOT . '/data/job-types-extras.json';
    if (is_file($extrasFile)) {
        $extraData = json_decode((string)file_get_contents($extrasFile), true);
        foreach (($extraData['jobs'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $service = areaSlug((string)($row['service'] ?? ''));
            if (!isset($fireServices[$service])) {
                continue;
            }
            $slug = keywordSlug((string)($row['slug'] ?? $row['name'] ?? ''));
            $out[] = fireJobRow($slug, (string)($row['name'] ?? keywordDisplayName($slug)), $service, (string)($row['related'] ?? $slug));
        }
    }

    if (function_exists('seoIaJobs')) {
        foreach (seoIaJobs() as $slug => $meta) {
            if (!is_array($meta)) {
                continue;
            }
            $service = areaSlug((string)($meta['service'] ?? ''));
            if ($service === '' || !isset($fireServices[$service])) {
                // Wave-1 Fire money jobs often omit service; infer from slug / known list.
                $guess = fireJobGuessService((string)$slug);
                if ($guess === '' || !isset($fireServices[$guess])) {
                    continue;
                }
                $service = $guess;
            }
            $slug = keywordSlug((string)$slug);
            $out[] = fireJobRow(
                $slug,
                (string)($meta['name'] ?? $meta['h1'] ?? keywordDisplayName($slug)),
                $service,
                (string)($meta['related'] ?? $slug)
            );
        }
    }

    foreach (fireJobVerbExpansions() as $row) {
        if (!isset($fireServices[$row['service_type']])) {
            continue;
        }
        $out[] = $row;
    }

    return $out;
}

/**
 * @return array{category:string,service_type:string,slug:string,status:string,name:string,related:string}
 */
function fireJobRow(string $slug, string $name, string $service, string $related): array
{
    $slug = keywordSlug($slug);
    return [
        'category' => FIRE_JOB_CATEGORY,
        'service_type' => areaSlug($service),
        'slug' => $slug,
        'status' => FIRE_JOB_STATUS,
        'name' => $name !== '' ? $name : keywordDisplayName($slug),
        'related' => keywordSlug($related !== '' ? $related : $slug),
    ];
}

function fireJobGuessService(string $slug): string
{
    $slug = keywordSlug($slug);
    $map = [
        'fire-risk-assessment' => 'fire-risk-assessments',
        'annual-fire-risk-assessment' => 'fire-risk-assessments',
        'fire-alarm' => 'fire-alarms',
        'emergency-lighting' => 'emergency-lighting',
        'fire-door' => 'fire-doors',
        'aov-' => 'aov-air-handling',
        'smoke-alarm' => 'smoke-co-alarms',
        'sprinkler' => 'sprinkler-systems',
        'dry-riser' => 'dry-risers',
        'extinguisher' => 'fire-extinguishers',
        'fire-stopp' => 'fire-stopping',
        'suppression' => 'fire-suppression',
        'evac' => 'evacuation-alerts',
        'nurse-call' => 'nurse-call',
        'compart' => 'fire-compartmentation',
        'signage' => 'fire-signage',
    ];
    foreach ($map as $needle => $service) {
        if (str_contains($slug, $needle)) {
            return $service;
        }
    }
    return '';
}

/**
 * Base Fire systems × standard verbs (used only if existing lists fall short of 390).
 *
 * @return list<array{category:string,service_type:string,slug:string,status:string,name:string,related:string}>
 */
function fireJobVerbExpansions(): array
{
    $systems = [
        'fire-alarm' => ['Fire Alarm', 'fire-alarms'],
        'fire-risk-assessment' => ['Fire Risk Assessment', 'fire-risk-assessments'],
        'emergency-lighting' => ['Emergency Lighting', 'emergency-lighting'],
        'aov' => ['AOV', 'aov-air-handling'],
        'sprinkler-system' => ['Sprinkler System', 'sprinkler-systems'],
        'dry-riser' => ['Dry Riser', 'dry-risers'],
        'wet-riser' => ['Wet Riser', 'dry-risers'],
        'fire-extinguisher' => ['Fire Extinguisher', 'fire-extinguishers'],
        'fire-door' => ['Fire Door', 'fire-doors'],
        'fire-stopping' => ['Fire Stopping', 'fire-stopping'],
        'fire-suppression' => ['Fire Suppression', 'fire-suppression'],
        'kitchen-fire-suppression' => ['Kitchen Fire Suppression', 'kitchen-fire-suppression'],
        'fire-compartmentation' => ['Fire Compartmentation', 'fire-compartmentation'],
        'fire-signage' => ['Fire Signage', 'fire-signage'],
        'evacuation-alert' => ['Evacuation Alert', 'evacuation-alerts'],
        'smoke-alarm' => ['Smoke Alarm', 'smoke-co-alarms'],
        'nurse-call' => ['Nurse Call', 'nurse-call'],
        'addressable-fire-alarm' => ['Addressable Fire Alarm', 'fire-alarms'],
        'wireless-fire-alarm' => ['Wireless Fire Alarm', 'fire-alarms'],
        'conventional-fire-alarm' => ['Conventional Fire Alarm', 'fire-alarms'],
    ];
    $verbs = [
        'installation',
        'repair',
        'maintenance',
        'inspection',
        'testing',
        'certification',
        'servicing',
        'commissioning',
        'upgrade',
        'replacement',
    ];
    $audiences = ['landlord', 'hmo', 'commercial', 'care-home', 'office', 'warehouse'];
    $out = [];
    foreach ($systems as $base => [$label, $service]) {
        foreach ($verbs as $verb) {
            $slug = keywordSlug($base . '-' . $verb);
            $out[] = fireJobRow($slug, $label . ' ' . ucfirst($verb), $service, $base);
        }
        foreach ($audiences as $who) {
            $slug = keywordSlug($who . '-' . $base);
            $out[] = fireJobRow($slug, ucwords(str_replace('-', ' ', $who)) . ' ' . $label, $service, $base);
        }
    }
    return $out;
}
