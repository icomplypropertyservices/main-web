<?php
/**
 * Mega-header IA helpers (owned by Mega Header agent).
 * Safe to include when config already defines these — guarded with function_exists.
 */

if (!function_exists('getNavAudienceServices')) {
    /** Curated Domestic vs Commercial slugs for mega-nav (only those present in getServices()). */
    function getNavAudienceServices(): array {
        $all = function_exists('getServices') ? getServices() : [];
        $domestic = [
            'electrical', 'pat-testing', 'gas-systems', 'smoke-co-alarms', 'epc',
            'landlord-compliance', 'kitchens', 'bathrooms', 'heating', 'renovation',
        ];
        $commercial = [
            'fire-alarms', 'emergency-lighting', 'fire-risk-assessments', 'fire-doors',
            'epc', 'pat-testing', 'facilities-management', 'cctv', 'access-control',
            'commercial-fit-out', 'nurse-call', 'compliance-consultancy',
        ];
        $pick = static function (array $slugs) use ($all): array {
            $out = [];
            foreach ($slugs as $slug) {
                if (isset($all[$slug])) {
                    $out[$slug] = $all[$slug];
                }
            }
            return $out;
        };
        return [
            'domestic' => $pick($domestic),
            'commercial' => $pick($commercial),
        ];
    }
}

if (!function_exists('getPackageHubs')) {
    /** Wave 1 package hubs under /pages/packages/* */
    function getPackageHubs(): array {
        return [
            'hmo' => [
                'name' => 'HMO Packages',
                'tagline' => 'EICR + gas + FRA for licensed HMOs',
                'audience' => 'Domestic',
            ],
            'hmo-compliance' => [
                'name' => 'HMO Compliance',
                'tagline' => 'Core HMO certificate bundle',
                'audience' => 'Domestic',
            ],
            'hmo-fire-safety' => [
                'name' => 'HMO Fire Safety',
                'tagline' => 'FRA, alarms, lighting, doors',
                'audience' => 'Domestic',
            ],
            'hmo-occupancy' => [
                'name' => 'HMO Occupancy',
                'tagline' => 'Re-let certificates and alarms',
                'audience' => 'Domestic',
            ],
            'let-ready' => [
                'name' => 'Let Ready',
                'tagline' => 'Landlord / void compliance pack',
                'audience' => 'Domestic',
            ],
            'workplace-essentials' => [
                'name' => 'Workplace Essentials',
                'tagline' => 'Office & commercial basics',
                'audience' => 'Commercial',
            ],
            'fire-ready' => [
                'name' => 'Fire Ready',
                'tagline' => 'Life-safety systems pack',
                'audience' => 'Commercial',
            ],
        ];
    }
}
