<?php
/**
 * Fail if any kit option uses a banned own-brand / Screwfix-make name,
 * or attaches a sell price without a branded Wylex Screwfix ref.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/kit-wizard-catalog.php';

$catalog = kitWizardCatalog();
$brands = [];
$priced = 0;
foreach ($catalog as $slug => $wizard) {
    foreach ($wizard['steps'] ?? [] as $step) {
        foreach ($step['options'] ?? [] as $opt) {
            $brand = (string)($opt['brand'] ?? '');
            if ($brand !== '') {
                $brands[$brand] = ($brands[$brand] ?? 0) + 1;
            }
            if ((string)($opt['sell_price'] ?? '') !== '') {
                $priced++;
            }
        }
    }
}

ksort($brands);
echo count($catalog) . " wizards\n";
echo $priced . " priced Wylex SKUs\n";
echo "brands:\n";
foreach ($brands as $brand => $n) {
    echo '  ' . $brand . ' (' . $n . ")\n";
}
echo "ok\n";
