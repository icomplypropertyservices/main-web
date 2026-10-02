<?php
/**
 * Fail if any kit option uses a banned own-brand / Screwfix-make name,
 * or attaches a sell price that is not a branded Wylex Screwfix inc-VAT figure + 15%.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/kit-wizard-catalog.php';
// Second include must not redeclare helpers during a long static export.
require __DIR__ . '/../includes/kit-wizard-catalog.php';
require __DIR__ . '/../includes/kit-wizard.php';
require __DIR__ . '/../includes/kit-wizard.php';

$fail = 0;
try {
    $catalog = kitWizardCatalog();
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}

$brands = [];
$priced = 0;
foreach ($catalog as $slug => $wizard) {
    if ((string)($wizard['slug'] ?? '') !== (string)$slug) {
        fwrite(STDERR, "FAIL: wizard key {$slug} does not match slug\n");
        $fail++;
    }
    foreach ($wizard['steps'] ?? [] as $step) {
        foreach ($step['options'] ?? [] as $opt) {
            $brand = (string)($opt['brand'] ?? '');
            $label = (string)($opt['label'] ?? '');
            if ($brand !== '') {
                $brands[$brand] = ($brands[$brand] ?? 0) + 1;
            }
            if (kitBrandIsBanned($brand) || kitBrandIsBanned($label)) {
                fwrite(STDERR, "FAIL: banned brand in {$slug}: {$brand} / {$label}\n");
                $fail++;
            }
            foreach (kitBannedBrandKeys() as $banned) {
                if (preg_match('/^' . preg_quote($banned, '/') . '\b/i', $label)) {
                    fwrite(STDERR, "FAIL: {$slug} label starts with banned brand: {$label}\n");
                    $fail++;
                }
            }
            $sell = (string)($opt['sell_price'] ?? '');
            if ($sell !== '') {
                $priced++;
                $inc = (string)($opt['screwfix_inc'] ?? '');
                $ref = (string)($opt['screwfix_ref'] ?? '');
                if ($brand !== 'Wylex' || $ref === '' || $inc === '') {
                    fwrite(STDERR, "FAIL: {$slug} sell £{$sell} without a branded Wylex Screwfix ref\n");
                    $fail++;
                    continue;
                }
                $expected = number_format(round((float)$inc * 1.15, 2), 2, '.', '');
                if ($expected !== $sell) {
                    fwrite(STDERR, "FAIL: {$ref} sell £{$sell} is not Screwfix £{$inc} inc VAT + 15% (£{$expected})\n");
                    $fail++;
                }
            }
        }
    }
}

if ($priced < 1) {
    fwrite(STDERR, "FAIL: expected priced Wylex boards\n");
    $fail++;
}

if (kitWizardSlugsForService('access-control') !== ['barriers']
    || kitWizardSlugsForService('aov-air-handling') !== ['aov']
    || kitWizardSlugsForService('nurse-call') !== []
    || kitWizardSlugsForTown() !== ['barriers', 'aov']) {
    fwrite(STDERR, "FAIL: barriers and AOV must be the built-in service/town wizards (no Tunstall / nurse-call)\n");
    $fail++;
}

ksort($brands);
echo count($catalog) . " wizards\n";
echo $priced . " priced Wylex SKUs\n";
echo "brands:\n";
foreach ($brands as $brand => $n) {
    echo '  ' . $brand . ' (' . $n . ")\n";
}

if ($fail > 0) {
    fwrite(STDERR, "check-kit-brands FAILED ({$fail})\n");
    exit(1);
}

echo "ok\n";
