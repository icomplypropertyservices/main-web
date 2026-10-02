<?php
/**
 * Jack pilot: non-fire services × Manchester and Burnley only.
 * Fire × area nationwide stays with the fire workstream. Draft / non-prod.
 */
declare(strict_types=1);

/**
 * Display name => confirmed slug. Both names are in areas.json.
 *
 * @return array<string, string>
 */
function getJackPilotAreas(): array
{
    return [
        'Manchester' => 'manchester',
        'Burnley' => 'burnley',
    ];
}

/** @return list<string> */
function getJackPilotAreaNames(): array
{
    return array_keys(getJackPilotAreas());
}

/** @return list<string> */
function getJackPilotAreaSlugs(): array
{
    return array_values(getJackPilotAreas());
}

function isJackPilotAreaSlug(string $areaOrSlug): bool
{
    return in_array(areaSlug($areaOrSlug), getJackPilotAreaSlugs(), true);
}

function jackPilotAreaName(string $areaOrSlug): ?string
{
    $slug = areaSlug($areaOrSlug);
    foreach (getJackPilotAreas() as $name => $expect) {
        if ($expect === $slug) {
            return $name;
        }
    }
    return null;
}

/**
 * @return list<string> empty when burnley and manchester resolve from areas.json
 */
function jackPilotAreaSlugErrors(): array
{
    $errors = [];
    foreach (getJackPilotAreas() as $name => $expect) {
        $actual = areaSlug($name);
        if ($actual !== $expect) {
            $errors[] = "{$name} slug is {$actual}, expected {$expect}";
        }
        $fromList = function_exists('areaFromSlug') ? areaFromSlug($expect) : null;
        if ($fromList !== $name) {
            $errors[] = "areas.json has no {$name} at slug {$expect}";
        }
    }
    return $errors;
}

/** @return list<string> */
function getFireServiceSlugs(): array
{
    $cats = function_exists('getServiceCategories') ? getServiceCategories() : [];
    $slugs = $cats['fire-safety']['services'] ?? [];
    $out = [];
    foreach ($slugs as $slug) {
        $slug = areaSlug((string)$slug);
        if ($slug !== '') {
            $out[$slug] = true;
        }
    }
    return array_keys($out);
}

function isFireServiceSlug(string $slug): bool
{
    return in_array(areaSlug($slug), getFireServiceSlugs(), true);
}

/**
 * Non-catalogue services that still get the two pilot landings.
 *
 * @return array<string, string>
 */
function getJackPilotExtraServices(): array
{
    return [
        'ev-charging' => 'EV Charging',
    ];
}

/**
 * Every non-fire catalogue service, plus EV.
 *
 * @return array<string, string>
 */
function getJackPilotServices(): array
{
    $out = [];
    foreach (getServices() as $slug => $name) {
        if (!isFireServiceSlug((string)$slug)) {
            $out[(string)$slug] = (string)$name;
        }
    }
    foreach (getJackPilotExtraServices() as $slug => $name) {
        $out[$slug] = $name;
    }
    return $out;
}

function jackPilotServiceName(string $slug): ?string
{
    $all = getJackPilotServices();
    $slug = areaSlug($slug);
    return $all[$slug] ?? null;
}

function jackPilotServiceAreaPublished(string $serviceSlug, string $area): bool
{
    return jackPilotServiceName($serviceSlug) !== null && isJackPilotAreaSlug($area);
}

function jackPilotServiceAreaPath(string $serviceSlug, string $area): string
{
    return '/pages/' . areaSlug($serviceSlug) . '/' . areaSlug($area);
}

/** @return list<string> */
function jackPilotExportPaths(): array
{
    $paths = [];
    foreach (array_keys(getJackPilotServices()) as $slug) {
        foreach (getJackPilotAreaSlugs() as $areaSlug) {
            $paths[] = jackPilotServiceAreaPath((string)$slug, $areaSlug);
        }
    }
    return $paths;
}

/**
 * Guide figures for the two pilot towns. Not fixed quotes.
 * EICR £249 matches the pricing page (small commercial / multi-let).
 * Gas £85 is the Jack guide for a straightforward gas safety visit on these landings.
 *
 * @return array{label:string,from:string,note:string}|null
 */
function jackPilotGuidePrice(string $serviceSlug): ?array
{
    $map = [
        'electrical' => [
            'label' => 'EICR — small commercial / multi-let',
            'from' => '£249',
            'note' => 'Guide only, the same figure as the pricing page. Not a fixed quote. Domestic flats and houses are listed separately there.',
        ],
        'gas-systems' => [
            'label' => 'Gas safety visit',
            'from' => '£85',
            'note' => 'Guide only for a straightforward visit in this town. Not a fixed quote. Appliance count, flue type and access change the written price.',
        ],
    ];
    return $map[areaSlug($serviceSlug)] ?? null;
}

function jackPilotGuidePriceHtml(string $serviceSlug, string $areaName): string
{
    $price = jackPilotGuidePrice($serviceSlug);
    if ($price === null || !jackPilotServiceAreaPublished($serviceSlug, $areaName)) {
        return '';
    }
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $pricing = function_exists('url') ? url('/pages/pricing.php') : '/pages/pricing';
    return '<aside class="p-6 bg-white border rounded-3xl" data-guide-price="' . $h($price['from']) . '">'
        . '<p class="text-xs uppercase tracking-widest text-zinc-500">Guide price · ' . $h($areaName) . '</p>'
        . '<p class="mt-2 text-2xl font-semibold text-black">' . $h($price['label']) . ' from ' . $h($price['from']) . '</p>'
        . '<p class="mt-2 text-sm text-zinc-600">' . $h($price['note'])
        . ' <a class="text-[#ff6b00] font-semibold" href="' . $h($pricing) . '">Full pricing guide</a>.</p>'
        . '</aside>';
}

/**
 * Hubs that cross-link on each pilot landing.
 *
 * @return array<string, list<string>>
 */
function jackPilotHubGroups(): array
{
    return [
        'Electrical' => ['electrical', 'electrics-first-fix', 'pat-testing', 'epc', 'ev-charging'],
        'Gas' => ['gas-systems', 'heating'],
        'CCTV & security' => ['cctv', 'access-control', 'door-entry', 'intercoms', 'intruder-alarm'],
        'Water & plumbing' => ['plumbing', 'legionella-risk-assessment'],
        'Asbestos' => ['asbestos-survey'],
        'Building' => ['building-maintenance', 'building-surveys', 'facilities-management', 'commercial-fit-out'],
    ];
}

function jackPilotHubLinksHtml(string $areaName): string
{
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $html = '';
    foreach (jackPilotHubGroups() as $label => $slugs) {
        $html .= '<div class="mb-4"><h3 class="font-semibold text-black mb-2">' . $h($label) . ' in ' . $h($areaName) . '</h3><div class="chip-cloud">';
        foreach ($slugs as $slug) {
            $name = jackPilotServiceName($slug);
            if ($name === null) {
                continue;
            }
            $href = function_exists('url')
                ? url(jackPilotServiceAreaPath($slug, $areaName))
                : jackPilotServiceAreaPath($slug, $areaName);
            $html .= '<a class="area-chip" href="' . $h($href) . '">' . $h($name) . '</a>';
        }
        $html .= '</div></div>';
    }
    return $html;
}

function jackPilotFireLinkOutHtml(): string
{
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $services = function_exists('getServices') ? getServices() : [];
    $html = '<div class="chip-cloud">';
    foreach (getFireServiceSlugs() as $slug) {
        if (!isset($services[$slug])) {
            continue;
        }
        $href = function_exists('url') ? url('/pages/services/' . $slug) : '/pages/services/' . $slug;
        $html .= '<a class="kw-chip" href="' . $h($href) . '">' . $h((string)$services[$slug]) . '</a>';
    }
    $html .= '</div>';
    return $html;
}
