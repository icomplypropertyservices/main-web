<?php
/**
 * Landlord conversion guide prices — single source for the four job landings.
 * Figures are guides. The fixed quote is confirmed after scope.
 */
declare(strict_types=1);

/**
 * @return array<string, array{amount:int,name:string,service:string,path:string,guide:string}>
 */
function icomplyLandlordGuidePrices(): array
{
    return [
        'eicr' => [
            'amount' => 249,
            'name' => 'EICR',
            'service' => 'Electrical',
            'path' => '/pages/jobs/eicr',
            'guide' => 'Guide for a landlord EICR. The fixed quote follows circuits, access and any remedials.',
        ],
        'gas' => [
            'amount' => 85,
            'name' => 'Gas safety (CP12)',
            'service' => 'Gas Systems',
            'path' => '/pages/jobs/gas-safety-cp12',
            'guide' => 'Guide for a landlord gas safety record. Appliance count and access can change the fixed quote.',
        ],
        'fra' => [
            'amount' => 350,
            'name' => 'Fire risk assessment',
            'service' => 'Fire Risk Assessments',
            'path' => '/pages/jobs/fire-risk-assessment',
            'guide' => 'Guide for an HMO or other multi-occupied FRA. Layout and related installs are confirmed on the quote.',
        ],
        'bundle' => [
            'amount' => 650,
            'name' => 'Landlord compliance pack',
            'service' => 'Landlord Compliance',
            'path' => '/pages/jobs/landlord-compliance',
            'guide' => 'Guide for EICR, gas safety and FRA on one schedule. Alarm installs and remedials are quoted separately.',
        ],
    ];
}

/**
 * @return array{amount:int,name:string,service:string,path:string,guide:string}
 */
function icomplyLandlordGuidePrice(string $key): array
{
    $all = icomplyLandlordGuidePrices();
    if (!isset($all[$key])) {
        throw new InvalidArgumentException('Unknown landlord guide price: ' . $key);
    }
    return $all[$key];
}

function icomplyLandlordGuideMoney(string $key): string
{
    return '£' . (string)icomplyLandlordGuidePrice($key)['amount'];
}

/** Root-relative contact URL. Service must match an existing contact-form option. */
function icomplyLandlordQuoteHref(string $service): string
{
    return '/contact?service=' . rawurlencode($service);
}
