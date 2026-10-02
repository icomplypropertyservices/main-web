#!/usr/bin/env php
<?php
/**
 * Build the access-control job lane (barriers, maglocks, door entry).
 *
 * NON-PROD. Writes a draft catalogue and keyword stubs. Do not merge this
 * branch to main and do not promote a production deploy.
 *
 * Usage: php website/bin/build-access-control-jobs.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/access-control-jobs.php';

$jobs = [];
$seen = [];

$add = static function (string $slug, string $name, string $family, string $service, string $related) use (&$jobs, &$seen): void {
    $slug = keywordSlug($slug);
    if ($slug === '' || isset($seen[$slug])) {
        return;
    }
    $seen[$slug] = true;
    $jobs[] = [
        'category' => 'Access Control',
        'family' => $family,
        'service' => areaSlug($service),
        'slug' => $slug,
        'status' => 'draft',
        'name' => $name !== '' ? $name : keywordDisplayName($slug),
        'related' => keywordSlug($related !== '' ? $related : $slug),
    ];
};

$raw = loadJsonData('keywords', []);
foreach ($raw as $slug => $meta) {
    if (!is_array($meta)) {
        continue;
    }
    $service = areaSlug((string)($meta['service'] ?? ''));
    if (!in_array($service, ['access-control', 'door-entry'], true)) {
        continue;
    }
    $slug = keywordSlug((string)$slug);
    $family = accessControlLaneGuessFamily($slug, $service);
    $add(
        $slug,
        (string)($meta['name'] ?? keywordDisplayName($slug)),
        $family,
        $service,
        (string)($meta['related'] ?? $slug)
    );
}

foreach (accessControlLaneSeedRows() as $row) {
    $add($row['slug'], $row['name'], $row['family'], $row['service'], $row['related']);
}

$byName = [];
foreach ($jobs as $i => $job) {
    $byName[(string)$job['name']][] = $i;
}
foreach ($byName as $indexes) {
    if (count($indexes) < 2) {
        continue;
    }
    usort($indexes, static function (int $a, int $b) use ($jobs): int {
        return strlen((string)$jobs[$b]['slug']) <=> strlen((string)$jobs[$a]['slug']);
    });
    array_shift($indexes);
    foreach ($indexes as $idx) {
        $slug = (string)$jobs[$idx]['slug'];
        $jobs[$idx]['name'] = keywordDisplayName($slug);
        $jobs[$idx]['name_lock'] = true;
    }
}

$names = [];
$dupNames = [];
foreach ($jobs as $job) {
    $name = (string)$job['name'];
    if (isset($names[$name])) {
        $dupNames[] = $name . ' (' . $job['slug'] . ' & ' . $names[$name] . ')';
    } else {
        $names[$name] = (string)$job['slug'];
    }
}
if ($dupNames) {
    fwrite(STDERR, "Duplicate job names:\n- " . implode("\n- ", $dupNames) . "\n");
    exit(1);
}

$counts = ['barriers' => 0, 'maglock' => 0, 'door-entry' => 0, 'access-control' => 0];
$towns = ['manchester' => 0, 'burnley' => 0];
foreach ($jobs as $job) {
    $family = (string)$job['family'];
    if (isset($counts[$family])) {
        $counts[$family]++;
    }
    if (str_ends_with((string)$job['slug'], '-manchester')) {
        $towns['manchester']++;
    }
    if (str_ends_with((string)$job['slug'], '-burnley')) {
        $towns['burnley']++;
    }
}

$payload = [
    'lane' => 'access-control',
    'non_prod' => true,
    'promote' => false,
    'status' => 'draft',
    'towns' => ['Manchester', 'Burnley'],
    'counts' => $counts,
    'town_pages' => $towns,
    'generated' => gmdate('c'),
    'jobs' => $jobs,
];
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    fwrite(STDERR, "JSON encode failed\n");
    exit(1);
}
file_put_contents(accessControlLaneFile(), $json . "\n");
accessControlLaneData(true);

$written = 0;
foreach ($jobs as $job) {
    $slug = (string)$job['slug'];
    $slugExport = var_export($slug, true);
    $stub = "<?php\n"
        . "/** AUTO-GENERATED access-control lane stub — php website/bin/build-access-control-jobs.php */\n"
        . "require_once __DIR__ . '/../../includes/render.php';\n"
        . "renderKeywordPage({$slugExport});\n";
    file_put_contents(accessControlLaneStubPath($slug), $stub);
    $written++;
}

echo 'Wrote ' . count($jobs) . ' access-control lane jobs → ' . accessControlLaneFile() . "\n";
echo "Wrote {$written} stubs\n";
echo 'counts ' . json_encode($counts) . ' towns ' . json_encode($towns) . "\n";
echo "non_prod=1 promote=0\n";
exit(0);
