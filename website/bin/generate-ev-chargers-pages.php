#!/usr/bin/env php
<?php
/**
 * Emit EV chargers keyword stubs: website/pages/keywords/<slug>.php
 *
 * Usage:
 *   php website/bin/generate-ev-chargers-pages.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

$expected = evChargersJobsExpectedCount();
$slugs = evChargersJobsSlugs();
$unique = array_values(array_unique($slugs));

if (count($unique) !== $expected) {
    fwrite(STDERR, 'EV chargers lane has ' . count($unique) . " unique slugs; expected {$expected}\n");
    exit(1);
}

$outDir = SITE_ROOT . '/pages/keywords';
if (!is_dir($outDir) && !mkdir($outDir, 0755, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$written = 0;
foreach ($unique as $slug) {
    file_put_contents(evChargersJobsStubPath($slug), evChargersJobsStubPhp($slug));
    $written++;
}

echo "ev_chargers_page_count={$written}\n";
echo "Wrote {$written} stubs → {$outDir}/<slug>.php\n";
exit($written === $expected ? 0 : 1);
