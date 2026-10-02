#!/usr/bin/env php
<?php
/**
 * Mainland UK coverage matrix generator.
 *
 * Builds the area × service catalogue, stable area buckets, and sitemap
 * tier fragments under website/data/coverage/. Does not edit sitemap.xml,
 * robots.txt, or dist/, and does not deploy.
 *
 * Usage:
 *   php website/bin/generate-coverage.php
 *   php website/bin/generate-coverage.php --verify
 *   php website/bin/generate-coverage.php --verify --strict
 *   php website/bin/generate-coverage.php --claim=b001 --agent=sibling-north
 *   php website/bin/generate-coverage.php --release=b001 --agent=sibling-north
 *   php website/bin/generate-coverage.php --publish-bucket=b001
 *   php website/bin/generate-coverage.php --materialize=b001
 *   php website/bin/generate-coverage.php --bucket=b001
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "generate-coverage.php is a CLI tool.\n");
    exit(1);
}

foreach ($argv as $arg) {
    if (preg_match('/netlify|--prod|deploy/i', (string)$arg)) {
        fwrite(STDERR, "Refusing: this generator does not deploy and must not be pointed at production.\n");
        exit(2);
    }
}

require_once dirname(__DIR__) . '/includes/coverage.php';

$options = getopt('', [
    'verify',
    'strict',
    'claim:',
    'release:',
    'agent:',
    'publish-bucket:',
    'materialize:',
    'bucket:',
    'force-repartition',
]);

if (isset($options['bucket']) && !isset($options['verify']) && !isset($options['claim']) && !isset($options['release']) && !isset($options['publish-bucket']) && !isset($options['materialize']) && !isset($options['force-repartition'])) {
    $id = (string)$options['bucket'];
    $map = coverageBucketMap();
    if (!isset($map[$id])) {
        fwrite(STDERR, "Unknown bucket {$id}\n");
        exit(1);
    }
    $bucket = $map[$id];
    echo $bucket['id'] . ' status=' . $bucket['status']
        . ' agent=' . ($bucket['agent'] ?? '-')
        . ' areas=' . count($bucket['areas'])
        . ' first=' . ($bucket['areas'][0] ?? '-')
        . ' last=' . ($bucket['areas'][count($bucket['areas']) - 1] ?? '-')
        . "\n";
    exit(0);
}

if (isset($options['claim']) || isset($options['release']) || isset($options['publish-bucket'])) {
    $agent = trim((string)($options['agent'] ?? ''));
    if (isset($options['claim'])) {
        coverageCommandClaim((string)$options['claim'], $agent);
    } elseif (isset($options['release'])) {
        coverageCommandRelease((string)$options['release'], $agent);
    } else {
        coverageCommandPublish((string)$options['publish-bucket']);
    }
}

if (isset($options['materialize'])) {
    $count = coverageMaterializeBucket((string)$options['materialize']);
    echo "Materialized {$count} stub(s) for {$options['materialize']} under website/pages/coverage/ (gitignored, not exported).\n";
    exit(0);
}

if (isset($options['verify']) && !isset($options['force-repartition'])) {
    exit(coverageReportVerify(isset($options['strict'])));
}

if (isset($options['force-repartition'])) {
    $locked = [];
    foreach (coverageLoadBuckets() as $bucket) {
        if ($bucket['status'] !== 'open') {
            $locked[] = $bucket['id'];
        }
    }
    if ($locked !== []) {
        fwrite(STDERR, 'Refusing --force-repartition while buckets are locked: ' . implode(', ', $locked) . "\n");
        exit(1);
    }
    coverageWriteBuckets(coverageFreshBuckets(coverageLoadAreas()));
    coverageFinishGenerate([]);
    exit(coverageReportVerify(isset($options['strict'])));
}

$areas = coverageLoadAreas();
$merged = coverageMergeBuckets($areas, coverageLoadBuckets());
coverageWriteBuckets($merged['buckets']);
coverageFinishGenerate($merged['warnings']);
exit(coverageReportVerify(isset($options['strict'])));

/** @param list<string> $warnings */
function coverageFinishGenerate(array $warnings): void
{
    coverageWriteCatalogueSnapshot();
    $buckets = coverageLoadBuckets();
    $summary = coverageBuildSummary($buckets);
    coverageWriteJson(coverageSummaryFile(), $summary);
    $tiers = coverageWriteSitemapTiers($buckets);
    echo "Mainland UK coverage matrix\n";
    echo "Areas: {$summary['areas']}\n";
    echo "Services: {$summary['services']}\n";
    echo "Cells: {$summary['cells']}\n";
    echo "Buckets: {$summary['buckets']} (size {$summary['bucket_size']})\n";
    echo "Ready cells: {$summary['content_ready_cells']}\n";
    echo "Sitemap tiers (data/coverage/sitemaps, not live): hubs={$tiers['tier1']} areas={$tiers['tier2']} service×area={$tiers['tier3']}\n";
    echo "Live sitemap.xml was not modified. Coverage paths stay excluded.\n";
    foreach (coveragePricing() as $key => $row) {
        echo "Price {$key}: {$row['display']}\n";
    }
    foreach ($warnings as $warning) {
        echo "WARN: {$warning}\n";
    }
}

function coverageReportVerify(bool $strict): int
{
    $errors = coverageVerify($strict);
    if ($errors === []) {
        $areas = count(coverageLoadAreas());
        $services = count(coverageCatalogue());
        echo "PASS coverage verify: {$areas} areas × {$services} services"
            . ($strict ? " (strict)\n" : " (structure)\n");
        return 0;
    }
    foreach ($errors as $error) {
        fwrite(STDERR, "FAIL: {$error}\n");
    }
    fwrite(STDERR, 'FAIL coverage verify (' . count($errors) . ")\n");
    return 1;
}

function coverageCommandClaim(string $id, string $agent): void
{
    if ($agent === '') {
        fwrite(STDERR, "Claim needs --agent=NAME\n");
        exit(1);
    }
    $buckets = coverageLoadBuckets();
    $found = false;
    foreach ($buckets as &$bucket) {
        if ($bucket['id'] !== $id) {
            continue;
        }
        $found = true;
        if ($bucket['status'] !== 'open') {
            fwrite(STDERR, "{$id} is {$bucket['status']} by " . ($bucket['agent'] ?? 'unknown') . "\n");
            exit(1);
        }
        $bucket['status'] = 'claimed';
        $bucket['agent'] = $agent;
        $bucket['claimed_at'] = date('c');
        $bucket['published_at'] = null;
    }
    unset($bucket);
    if (!$found) {
        fwrite(STDERR, "Unknown bucket {$id}\n");
        exit(1);
    }
    coverageWriteBuckets($buckets);
    coverageFinishGenerate([]);
    echo "Claimed {$id} for {$agent}\n";
    exit(0);
}

function coverageCommandRelease(string $id, string $agent): void
{
    if ($agent === '') {
        fwrite(STDERR, "Release needs --agent=NAME matching the claim\n");
        exit(1);
    }
    $buckets = coverageLoadBuckets();
    $found = false;
    foreach ($buckets as &$bucket) {
        if ($bucket['id'] !== $id) {
            continue;
        }
        $found = true;
        if ($bucket['status'] === 'open') {
            echo "{$id} is already open\n";
            exit(0);
        }
        if ($bucket['agent'] !== $agent) {
            fwrite(STDERR, "{$id} is held by " . ($bucket['agent'] ?? 'unknown') . "\n");
            exit(1);
        }
        $bucket['status'] = 'open';
        $bucket['agent'] = null;
        $bucket['claimed_at'] = null;
        $bucket['published_at'] = null;
    }
    unset($bucket);
    if (!$found) {
        fwrite(STDERR, "Unknown bucket {$id}\n");
        exit(1);
    }
    coverageWriteBuckets($buckets);
    coverageFinishGenerate([]);
    echo "Released {$id}\n";
    exit(0);
}

function coverageCommandPublish(string $id): void
{
    $errors = coveragePublishErrors($id);
    if ($errors !== []) {
        foreach ($errors as $error) {
            fwrite(STDERR, "FAIL: {$error}\n");
        }
        fwrite(STDERR, "Not published. Fill every service for every area in {$id} first.\n");
        exit(1);
    }
    $buckets = coverageLoadBuckets();
    foreach ($buckets as &$bucket) {
        if ($bucket['id'] !== $id) {
            continue;
        }
        $bucket['status'] = 'published';
        $bucket['published_at'] = date('c');
    }
    unset($bucket);
    coverageWriteBuckets($buckets);
    coverageFinishGenerate([]);
    echo "Published {$id} into sitemap tier 3 (data fragment only, not the live sitemap).\n";
    exit(0);
}

function coverageMaterializeBucket(string $id): int
{
    $map = coverageBucketMap();
    if (!isset($map[$id])) {
        fwrite(STDERR, "Unknown bucket {$id}\n");
        exit(1);
    }
    $root = SITE_ROOT . '/pages/coverage';
    $n = 0;
    foreach ($map[$id]['areas'] as $area) {
        $areaDir = $root . '/areas';
        if (!is_dir($areaDir)) {
            mkdir($areaDir, 0755, true);
        }
        $areaFile = $areaDir . '/' . $area . '.php';
        file_put_contents($areaFile, coverageStub('renderCoverageAreaHub', [$area]));
        $n++;
        foreach (array_keys(coverageCatalogue()) as $service) {
            $dir = $root . '/' . $service;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir . '/' . $area . '.php', coverageStub('renderCoverageServiceArea', [$service, $area]));
            $n++;
        }
    }
    return $n;
}

/** @param list<string> $args */
function coverageStub(string $fn, array $args): string
{
    $exported = array_map(static fn (string $arg): string => var_export($arg, true), $args);
    return "<?php\n"
        . "/** AUTO-GENERATED coverage stub — gitignored. Not part of the default static export. */\n"
        . "require_once __DIR__ . '/../../../includes/coverage.php';\n"
        . $fn . '(' . implode(', ', $exported) . ");\n";
}
