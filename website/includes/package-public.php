<?php
/**
 * Package / AOV public URL + price rules (Jack / CoS).
 * Kits/products: show £. AOV service package seed (aov-air-handling-package): POA + kit ladder.
 * Install/labour = POA. Non-AOV package £ kept; dead Shopify handles retarget to service hubs.
 */
declare(strict_types=1);

require_once __DIR__ . '/aov-kit-prices.php';

/**
 * @param array<string,mixed> $product
 * @return array{href:?string,price:?string,cta:?string,is_aov_package:bool,blurb:?string}
 */
function icomplyPackagePublicOverride(array $product): array
{
    $handle = strtolower(trim((string)($product['handle'] ?? $product['id'] ?? '')));
    $title = strtolower(trim((string)($product['title'] ?? '')));
    $blob = $handle . ' ' . $title;

    $serviceMap = [
        'aov-air-handling-package' => '/pages/services/aov-air-handling.php',
        'electrical-compliance-package' => '/pages/services/electrical.php',
        'fire-alarm-service-package' => '/pages/services/fire-alarms.php',
        'emergency-lighting-package' => '/pages/services/emergency-lighting.php',
        'nurse-call-systems-package' => '/pages/services/nurse-call.php',
        'gas-safety-package' => '/pages/services/gas-systems.php',
        'intruder-alarm-package' => '/pages/services/intruder-alarms.php',
        'cctv-systems-package' => '/pages/services/cctv.php',
        'access-control-package' => '/pages/services/access-control.php',
        'door-entry-package' => '/pages/services/door-entry.php',
        'intercoms-package' => '/pages/services/intercoms.php',
    ];

    $isAovPackage = ($handle === 'aov-air-handling-package')
        || (str_contains($handle, 'aov') && str_contains($handle, 'package') && !str_contains($handle, 'kit-1m2'));

    if ($isAovPackage) {
        $dest = $serviceMap['aov-air-handling-package'];
        return [
            'href' => function_exists('url') ? url($dest) : '/pages/services/aov-air-handling',
            'price' => 'POA',
            'cta' => 'Get a quote',
            'is_aov_package' => true,
            'blurb' => 'AOV equipment kits: AOV-SENSOR £60, AOV-MOTOR/ACT £275, heavy £950, AOV-CTRL £400, AOV-KIT-1M2 stairwell £2,450 (ex VAT). Installation and labour POA — not a draft "From £349" package.',
        ];
    }

    $kit = icomplyAovKitPriceOverride($product);
    if ($kit !== null) {
        return [
            'href' => null,
            'price' => $kit,
            'cta' => null,
            'is_aov_package' => false,
            'blurb' => null,
        ];
    }

    if (isset($serviceMap[$handle])) {
        return [
            'href' => function_exists('url') ? url($serviceMap[$handle]) : $serviceMap[$handle],
            'price' => 'POA',
            'cta' => 'Get a quote',
            'is_aov_package' => false,
            'blurb' => 'Equipment kits may show list prices; installation and labour are POA.',
        ];
    }

    return ['href' => null, 'price' => null, 'cta' => null, 'is_aov_package' => false, 'blurb' => null];
}
