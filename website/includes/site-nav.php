<?php
/**
 * Mega header + footer inventory dropdowns.
 * Keyword×town (200k+) is reached via hubs — not dumped as a flat list.
 */
declare(strict_types=1);

function icomplyNavH(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** @return array<string,mixed> */
function icomplyNavCatalog(): array
{
    static $c = null;
    if ($c !== null) {
        return $c;
    }

    $services = getServices();
    $areas = getAreas();
    $cats = function_exists('getServiceCategories') ? getServiceCategories() : [];
    $keywords = getMajorKeywords();

    $catBlocks = [];
    $used = [];
    foreach ($cats as $catKey => $cat) {
        $list = function_exists('getServicesInCategory') ? getServicesInCategory((string)$catKey) : [];
        if (!$list) {
            continue;
        }
        $kwForCat = [];
        foreach ($list as $slug => $name) {
            $used[$slug] = true;
            $kws = function_exists('getKeywordsForService') ? getKeywordsForService((string)$slug) : [];
            if ($kws) {
                $kwForCat[$slug] = [
                    'name' => (string)$name,
                    'keywords' => $kws,
                ];
            }
        }
        $catBlocks[(string)$catKey] = [
            'label' => (string)($cat['label'] ?? $catKey),
            'blurb' => (string)($cat['blurb'] ?? ''),
            'services' => $list,
            'keywords' => $kwForCat,
        ];
    }
    $orphan = [];
    foreach ($services as $slug => $name) {
        if (empty($used[$slug])) {
            $orphan[$slug] = $name;
        }
    }
    if ($orphan) {
        $catBlocks['other'] = [
            'label' => 'Other services',
            'blurb' => '',
            'services' => $orphan,
            'keywords' => [],
        ];
    }

    $areasByLetter = [];
    foreach ($areas as $area) {
        $area = (string)$area;
        $letter = strtoupper(substr($area, 0, 1));
        if ($letter === '') {
            $letter = '#';
        }
        $areasByLetter[$letter][] = $area;
    }
    ksort($areasByLetter);

    $popularKw = function_exists('getPopularKeywordSlugs') ? getPopularKeywordSlugs() : [];
    $featuredKw = [];
    foreach ($popularKw as $slug) {
        if (isset($keywords[$slug])) {
            $featuredKw[$slug] = (string)$keywords[$slug]['name'];
        }
    }

    $c = [
        'services' => $services,
        'areas' => $areas,
        'cats' => $catBlocks,
        'keywords' => $keywords,
        'areasByLetter' => $areasByLetter,
        'featuredKw' => $featuredKw,
        'popularAreas' => array_values(array_filter(
            ['Manchester', 'Burnley', 'Stockport', 'Salford', 'Bolton', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
            static fn($a) => in_array($a, $areas, true)
        )),
        'resources' => icomplyNavResourceLinks(),
        'packages' => icomplyNavPackageLinks(),
        'legal' => [
            ['href' => url('/privacy.php'), 'label' => 'Privacy policy'],
            ['href' => url('/terms.php'), 'label' => 'Terms & conditions'],
            ['href' => url('/pages/site-map.php'), 'label' => 'HTML site map'],
            ['href' => url('/sitemap.xml'), 'label' => 'XML sitemap'],
        ],
        'home' => rtrim(SITE_URL, '/') . '/',
        'phone' => defined('PHONE') ? PHONE : '',
        'phoneHref' => 'tel:' . preg_replace('/\s+/', '', defined('PHONE') ? PHONE : ''),
        'whatsapp' => defined('WHATSAPP') ? WHATSAPP : '',
        'email' => defined('EMAIL') ? EMAIL : '',
        'brand' => defined('SITE_NAME') ? SITE_NAME : 'Icomply Property Services',
        'js' => assetUrl('/assets/js/site-nav.js'),
        'logo' => assetUrl('/assets/images/brand/icomply-mark.svg'),
        'logoLight' => assetUrl('/assets/images/brand/icomply-logo.svg'),
        'logoDark' => assetUrl('/assets/images/brand/icomply-logo-on-dark.svg'),
    ];
    return $c;
}

/** @return list<array{href:string,label:string}> */
function icomplyNavResourceLinks(): array
{
    $links = [
        ['href' => url('/pages/resources/index.php'), 'label' => 'All resources'],
        ['href' => url('/pages/resources/index.php') . '#batch-a', 'label' => 'Batch A — days 1–5'],
        ['href' => url('/pages/resources/index.php') . '#batch-b', 'label' => 'Batch B — days 6–14'],
        ['href' => url('/pages/resources/index.php') . '#batch-c', 'label' => 'Batch C — SEO hubs'],
    ];
    $landers = [
        '/pages/landlord-certificates' => 'Landlord certificates',
        '/pages/gas-safety-certificate' => 'Gas safety certificate',
        '/pages/fire-risk-assessment' => 'Fire risk assessment',
        '/pages/electrical-safety-landlords' => 'Electrical safety for landlords',
        '/pages/commercial-fire-safety' => 'Commercial fire safety',
        '/pages/stockport-property-compliance' => 'Stockport property compliance',
        '/pages/manchester-property-compliance' => 'Manchester property compliance',
        '/pages/portable-appliance-testing' => 'PAT testing',
        '/pages/smoke-carbon-monoxide-alarms' => 'Smoke & CO alarms',
        '/pages/energy-performance-certificates' => 'Energy performance certificates',
        '/pages/emergency-lighting-compliance' => 'Emergency lighting compliance',
        '/pages/fire-door-compliance' => 'Fire door compliance',
    ];
    foreach ($landers as $path => $label) {
        $links[] = ['href' => url($path), 'label' => $label];
    }

    $dir = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/pages/resources';
    if (is_dir($dir)) {
        $files = glob($dir . '/*.php') ?: [];
        sort($files);
        foreach ($files as $file) {
            $slug = basename($file, '.php');
            if ($slug === 'index') {
                continue;
            }
            $links[] = [
                'href' => url('/pages/resources/' . $slug . '.php'),
                'label' => function_exists('keywordDisplayName') ? keywordDisplayName($slug) : $slug,
            ];
        }
    }

    $seen = [];
    $out = [];
    foreach ($links as $row) {
        if (isset($seen[$row['href']])) {
            continue;
        }
        $seen[$row['href']] = true;
        $out[] = $row;
    }
    return $out;
}

/** @return list<array{href:string,label:string}> */
function icomplyNavPackageLinks(): array
{
    return [
        ['href' => url('/pages/packages.php'), 'label' => 'All packages'],
        ['href' => url('/pages/packages/let-ready.php'), 'label' => 'Let-ready package'],
        ['href' => url('/pages/packages/fire-ready.php'), 'label' => 'Fire-ready package'],
        ['href' => url('/pages/packages/workplace-essentials.php'), 'label' => 'Workplace essentials'],
        ['href' => url('/pages/landlords.php'), 'label' => 'Landlords'],
        ['href' => url('/pages/commercial.php'), 'label' => 'Commercial / FM'],
        ['href' => url('/pages/care-homes.php'), 'label' => 'Care homes'],
        ['href' => url('/pages/pricing.php'), 'label' => 'Pricing guide (POA)'],
        ['href' => url('/pages/maintenance.php'), 'label' => 'Maintenance contracts'],
        ['href' => url('/pages/emergency.php'), 'label' => 'Emergency call-out'],
        ['href' => url('/pages/ev-chargers.php'), 'label' => 'EV chargers'],
        ['href' => url('/pages/about.php'), 'label' => 'About'],
        ['href' => url('/pages/faq.php'), 'label' => 'FAQ'],
        ['href' => url('/pages/reviews.php'), 'label' => 'Reviews'],
    ];
}

function icomplyNavLink(string $href, string $label, string $class = ''): string
{
    $cls = $class !== '' ? ' class="' . icomplyNavH($class) . '"' : '';
    return '<a href="' . icomplyNavH($href) . '"' . $cls . '>' . icomplyNavH($label) . '</a>';
}

require_once __DIR__ . '/site-nav-mega.php';
require_once __DIR__ . '/site-nav-footer.php';
