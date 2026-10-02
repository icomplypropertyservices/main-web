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
            'electrical', 'pat-testing', 'gas-systems', 'smoke-co-alarms', 'aov-air-handling', 'epc',
            'landlord-compliance', 'kitchens', 'bathrooms', 'heating', 'renovation',
        ];
        $commercial = [
            'fire-alarms', 'aov-air-handling', 'barriers', 'emergency-lighting', 'fire-risk-assessments', 'fire-doors',
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

if (!function_exists('icomplyFeaturedPushHubs')) {
    /**
     * Jack's two priority lines — primary nav pills and footer cards.
     * AOV is the smoke-control job hub. Barriers is the products hub anchor.
     *
     * @return list<array{id:string,label:string,title:string,path:string,note:string}>
     */
    function icomplyFeaturedPushHubs(): array {
        return [
            [
                'id' => 'aov',
                'label' => 'AOV',
                'title' => 'AOV & Smoke Control',
                'path' => '/pages/services/aov-air-handling',
                'note' => 'Smoke vents and AOV panels. EN 12101 / BS 9991. Install POA after scope.',
            ],
            [
                'id' => 'barriers',
                'label' => 'Barriers',
                'title' => 'Barriers',
                'path' => '/products#barriers',
                'note' => '5m barrier packs. Supply prices on the hub; install POA.',
            ],
        ];
    }
}

if (!function_exists('icomplyFeaturedPushHref')) {
    function icomplyFeaturedPushHref(array $hub): string {
        $path = (string)($hub['path'] ?? '/');
        $hash = '';
        if (str_contains($path, '#')) {
            [$path, $hash] = explode('#', $path, 2);
            $hash = '#' . $hash;
        }
        $href = function_exists('url') ? url($path) : $path;
        return $href . $hash;
    }
}

if (!function_exists('icomplyQualityHubLinks')) {
    /**
     * Wave-1 quality job hubs (Batch C). Every slug has a /pages/{slug} file.
     *
     * @return list<array{href:string,label:string,path:string}>
     */
    function icomplyQualityHubLinks(): array {
        if (!function_exists('wave1QualityHubs')) {
            $wave1 = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/includes/wave1.php';
            if (is_file($wave1)) {
                require_once $wave1;
            }
        }
        if (!function_exists('wave1QualityHubs')) {
            return [];
        }
        $out = [];
        foreach (wave1QualityHubs() as $slug => $hub) {
            $slug = (string)$slug;
            $out[] = [
                'href' => function_exists('url') ? url('/pages/' . $slug) : '/pages/' . $slug,
                'label' => (string)($hub['navLabel'] ?? $hub['h1'] ?? $slug),
                'path' => '/pages/' . $slug,
            ];
        }
        return $out;
    }
}

if (!function_exists('icomplyPackageHubLinks')) {
    /**
     * @return list<array{href:string,label:string,path:string}>
     */
    function icomplyPackageHubLinks(): array {
        $out = [[
            'href' => function_exists('url') ? url('/pages/packages.php') : '/pages/packages',
            'label' => 'All packages',
            'path' => '/pages/packages',
        ]];
        foreach (getPackageHubs() as $slug => $hub) {
            $out[] = [
                'href' => function_exists('url') ? url('/pages/packages/' . $slug . '.php') : '/pages/packages/' . $slug,
                'label' => (string)($hub['name'] ?? $slug),
                'path' => '/pages/packages/' . $slug,
            ];
        }
        return $out;
    }
}

if (!function_exists('icomplyBarrierJobHub')) {
    /** Keyword job that sits under the Barriers push. */
    function icomplyBarrierJobHub(): array {
        return [
            'href' => function_exists('url') ? url('/pages/keywords/car-park-barrier-access.php') : '/pages/keywords/car-park-barrier-access',
            'label' => 'Car park barrier access',
            'path' => '/pages/keywords/car-park-barrier-access',
        ];
    }
}

if (!function_exists('getPackageHubs')) {
    /** Wave 1 package hubs under /pages/packages/* */
    function getPackageHubs(): array {
        return [
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
