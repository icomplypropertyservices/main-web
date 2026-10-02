<?php
/**
 * Locked AOV equipment kit list prices (ex VAT). Jack / CoS.
 * Installation / labour / INSP / SVC / CO / FAULT / COMM / ACCESS / MULTI = POA only
 * when the product is AOV-scoped (not every product with "access" in the name).
 */
declare(strict_types=1);

/** @return array<string, array{price:string,label:string}> */
function icomplyAovKitSkuCatalog(): array
{
    return [
        'aov-motor' => ['price' => '£275', 'label' => 'AOV-MOTOR'],
        'aov-motor-hvy' => ['price' => '£950', 'label' => 'AOV-MOTOR-HVY'],
        'aov-act' => ['price' => '£275', 'label' => 'AOV-ACT'],
        'aov-act-hvy' => ['price' => '£950', 'label' => 'AOV-ACT-HVY'],
        'aov-ctrl' => ['price' => '£400', 'label' => 'AOV-CTRL'],
        'aov-sensor' => ['price' => '£60', 'label' => 'AOV-SENSOR'],
        'aov-kit-1m2' => ['price' => '£2,450', 'label' => 'AOV-KIT-1M2 stairwell package'],
    ];
}

function icomplyAovInstallPoaBlob(string $blob): bool
{
    if (!str_contains($blob, 'aov')) {
        return false;
    }
    foreach (['install', 'installation', 'labour', 'labor', 'insp', 'svc', 'fault', 'comm', 'access', 'multi', 'service-call', 'call-out', 'callout'] as $n) {
        if (str_contains($blob, $n)) {
            return true;
        }
    }
    return (bool) preg_match('/\bco\b/', $blob);
}

/**
 * @param array<string,mixed> $product
 */
function icomplyAovKitPriceOverride(array $product): ?string
{
    $handle = strtolower((string) ($product['handle'] ?? $product['id'] ?? ''));
    $title = strtolower((string) ($product['title'] ?? ''));
    $sku = strtolower((string) ($product['sku'] ?? ''));
    $blob = $handle . ' ' . $title . ' ' . $sku;

    if ($handle === 'aov-air-handling-package' || str_contains($handle, 'aov-air-handling-package')) {
        return null;
    }
    if (str_contains($blob, 'package') && str_contains($blob, 'aov') && !str_contains($blob, 'kit-1m2') && !str_contains($blob, 'stairwell')) {
        return null;
    }
    if (icomplyAovInstallPoaBlob($blob)) {
        return 'POA';
    }
    foreach (icomplyAovKitSkuCatalog() as $slug => $row) {
        if ($handle === $slug || $sku === $slug || str_contains($blob, $slug)) {
            return $row['price'];
        }
    }
    if (!str_contains($blob, 'aov')) {
        return null;
    }
    if (str_contains($blob, 'stairwell') || str_contains($blob, '1m2') || str_contains($blob, '1 m2')) {
        return '£2,450';
    }
    if (str_contains($blob, 'sensor')) {
        return '£60';
    }
    if (str_contains($blob, 'ctrl') || str_contains($blob, 'control panel') || str_contains($blob, 'controller')) {
        return '£400';
    }
    if ((str_contains($blob, 'hvy') || str_contains($blob, 'heavy')) && (str_contains($blob, 'motor') || str_contains($blob, 'act'))) {
        return '£950';
    }
    if (str_contains($blob, 'motor') || str_contains($blob, 'actuator') || preg_match('/\bact\b/', $blob)) {
        return '£275';
    }
    return null;
}

/** HTML strip for AOV hub / products — kit prices visible, install POA. */
function icomplyAovKitPriceStripHtml(): string
{
    $rows = '';
    foreach (icomplyAovKitSkuCatalog() as $row) {
        $rows .= '<tr><th scope="row">' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8')
            . '</th><td>' . htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8') . ' <span class="text-zinc-500 text-xs">ex VAT</span></td></tr>';
    }
    return '<div class="aov-kit-prices overflow-x-auto my-6">'
        . '<h3 class="text-lg font-semibold text-black mb-2">AOV equipment kits (ex VAT)</h3>'
        . '<p class="text-sm text-zinc-600 mb-3">List prices for supply kits. Installation, labour, inspection, servicing, CO, fault-finding, commissioning, access and multi-visit work are <strong>POA</strong>.</p>'
        . '<table class="w-full text-sm border border-zinc-200 rounded-xl overflow-hidden">'
        . '<thead class="bg-zinc-50"><tr><th class="text-left p-3">SKU</th><th class="text-left p-3">Price</th></tr></thead>'
        . '<tbody class="divide-y">' . $rows . '</tbody></table></div>';
}
