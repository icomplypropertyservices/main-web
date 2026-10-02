<?php
/**
 * Non-production CAME barrier pages for UK mainland towns over 10,000 people.
 * Not included in the default static export or sitemap.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}
if (!function_exists('executeTemplateVars')) {
    require_once __DIR__ . '/render.php';
}

function barriersCatalog(): array
{
    $loaded = loadJsonData('barriers-towns', []);
    return is_array($loaded) ? $loaded : [];
}

function barriersMeta(): array
{
    $meta = barriersCatalog()['meta'] ?? [];
    return is_array($meta) ? $meta : [];
}

/** @return list<array<string,mixed>> */
function barriersTowns(): array
{
    $towns = barriersCatalog()['towns'] ?? [];
    return is_array($towns) ? array_values($towns) : [];
}

function barriersTownBySlug(string $slug): ?array
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (barriersTowns() as $town) {
            if (!empty($town['slug'])) {
                $index[(string)$town['slug']] = $town;
            }
        }
    }
    return $index[$slug] ?? null;
}

function barriersCameImage(): string
{
    $images = loadJsonData('bar-5m-came-gard-images', []);
    if (is_array($images)) {
        foreach ($images as $url) {
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }
    }
    return '';
}

function barriersSeed(string $key): int
{
    return abs(crc32($key));
}

function barriersPick(array $pool, int $seed, int $offset = 0): string
{
    if (!$pool) {
        return '';
    }
    return (string)$pool[($seed + $offset) % count($pool)];
}

function barriersPopulationSentence(array $town): string
{
    $name = (string)$town['name'];
    $official = (string)($town['settlement_name'] ?? $name);
    $pop = number_format((int)$town['population']);
    $year = (int)$town['population_year'];
    $basis = (string)($town['population_basis'] ?? '');

    if ($basis === 'region') {
        return "Greater London had {$pop} usual residents in the ONS Census {$year}. "
            . "This is the London overview. Each London borough with more than 10,000 usual residents has its own page.";
    }
    if ($basis === 'local-authority') {
        return "The London borough of {$name} had {$pop} usual residents in the ONS Census {$year}. "
            . "ONS does not split Greater London into separate built-up towns, so the borough is the place used here.";
    }
    if ($basis === 'settlement' && str_contains($official, 'Aidrie')) {
        return "The NRS mid-{$year} settlement published as “{$official}” has a population of {$pop}. "
            . "That lookup spells Airdrie as Aidrie. The figure is for the combined settlement, not Airdrie on its own.";
    }
    if ($basis === 'settlement' && $official !== $name) {
        return "The NRS mid-{$year} settlement “{$official}” has a published population of {$pop}. "
            . "That is the contiguous settlement, which is wider than the {$name} council area. This page is filed under {$name}.";
    }
    if ($basis === 'settlement') {
        return "The NRS mid-{$year} settlement of {$name} has a published population of {$pop}.";
    }
    if ($official !== $name) {
        return "The ONS Census {$year} built-up area published as “{$official}” has {$pop} usual residents. "
            . "On this page that place is {$name}.";
    }
    return "The ONS Census {$year} built-up area of {$name} has {$pop} usual residents.";
}

function barriersCoverageSentence(array $town): string
{
    $name = (string)$town['name'];
    $base = 'Icomply is based at ' . ADDRESS . '.';
    if (!empty($town['northwest_round'])) {
        return "{$base} {$name} is already on our North West coverage list, so a survey is arranged from Stockport.";
    }
    if (($town['region'] ?? '') === 'North West') {
        return "{$base} {$name} is in the North West. We confirm a survey date from Stockport. This page does not promise a same-week visit.";
    }
    return "{$base} Enquiries for {$name} are taken there, and a survey is arranged before any order. Travel is confirmed on the quote.";
}

function barriersSizeSentence(array $town): string
{
    $name = (string)$town['name'];
    $seed = barriersSeed((string)$town['slug'] . '|size');
    $pools = [
        'small' => [
            "In a town of this size the usual job is one lane: a staff car park, a yard, or a residents' entrance.",
            "Most {$name} enquiries are a single arm across a private car park or works entrance.",
            "Smaller {$name} sites still need a surveyed loop and photocell layout. A short arm is not a reason to skip the safety devices.",
        ],
        'medium' => [
            "Towns of this size often need a barrier on a commercial park, a school or hospital approach, or a residents' car park.",
            "{$name} schemes are often one main entrance plus a pedestrian gate beside it. Both are surveyed together.",
            "A medium {$name} site may share the barrier with fob readers or an intercom. That link is designed on the survey, not assumed.",
        ],
        'large' => [
            "Larger {$name} sites are often more than one lane: retail parks, yard entrances and managed residential schemes.",
            "{$name} projects at this scale need a duty cycle that survives peak arrival, not a domestic operator left on a busy lane.",
            "We plan {$name} barriers around the lane width, the queue and the safety devices, then choose the CAME GARD duty to match.",
        ],
        'major' => [
            "In {$name} the work is usually multi-lane: retail approaches, logistics yards and managed car parks.",
            "{$name} entrances are specified for peak flow. The arm length and opening speed are chosen after the lane is measured.",
            "City sites in {$name} often connect the barrier to access control or ANPR. We only do that once the fail-state and fire exit are clear.",
        ],
    ];
    $class = (string)($town['size_class'] ?? 'medium');
    $pool = $pools[$class] ?? $pools['medium'];
    return barriersPick($pool, $seed, 0);
}

/** @return list<array{q:string,a:string}> */
function barriersFaqs(array $town): array
{
    $name = (string)$town['name'];
    $coverage = !empty($town['northwest_round'])
        ? "{$name} is on the North West list covered from our Stockport base. Send the postcode and a photo of the entrance and we will book a survey."
        : "Send the {$name} postcode, lane width and a photo of the entrance. We confirm whether we can survey it and when, before any order.";
    return [
        [
            'q' => "Do you install automatic barriers in {$name}?",
            'a' => $coverage,
        ],
        [
            'q' => "How much does a barrier cost in {$name}?",
            'a' => "Price on application. Arm length, safety devices, power and any access-control link change the cost. This page does not publish a fee.",
        ],
        [
            'q' => 'Are you a CAME partner?',
            'a' => 'Yes. Icomply supplies and installs CAME automatic barriers, including the GARD range, and can maintain CAME barriers that are still supportable.',
        ],
        [
            'q' => "Can you repair an existing barrier in {$name}?",
            'a' => 'Yes, when it can be made safe. If the cabinet, spring or safety devices are beyond repair we say so and quote a replacement instead of patching it.',
        ],
    ];
}

/** @return list<array<string,mixed>> */
function barriersNearby(array $town, int $limit = 6): array
{
    $seed = barriersSeed((string)$town['slug'] . '|near');
    $region = (string)($town['region'] ?? '');
    $nation = (string)($town['nation'] ?? '');
    $slug = (string)$town['slug'];
    $sameRegion = [];
    $sameNation = [];
    foreach (barriersTowns() as $other) {
        if (($other['slug'] ?? '') === $slug) {
            continue;
        }
        if ((string)($other['region'] ?? '') === $region) {
            $sameRegion[] = $other;
        } elseif ((string)($other['nation'] ?? '') === $nation) {
            $sameNation[] = $other;
        }
    }
    $pool = $sameRegion ?: $sameNation;
    if (!$pool) {
        return [];
    }
    $start = $seed % count($pool);
    $picked = [];
    $seen = [];
    for ($i = 0; $i < count($pool) && count($picked) < $limit; $i++) {
        $candidate = $pool[($start + $i) % count($pool)];
        $key = (string)$candidate['slug'];
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $picked[] = $candidate;
    }
    return $picked;
}

function renderBarriersTownPage(string $slug): void
{
    $slug = areaSlug($slug);
    $town = barriersTownBySlug($slug);
    if ($town === null || (int)($town['population'] ?? 0) <= 10000) {
        http_response_code(404);
        echo 'Barrier town page not found';
        icomplyRequestExit();
        return;
    }

    $name = (string)$town['name'];
    $seed = barriersSeed($slug . '|open');
    $openers = [
        "Automatic barriers in {$name} are supplied and installed by Icomply, a CAME partner.",
        "Car parks, yards and gated entrances in {$name} can use a CAME automatic barrier from Icomply.",
        "Icomply specifies CAME automatic barriers for {$name}, including the GARD range when the lane suits it.",
        "For a vehicle entrance in {$name}, Icomply surveys the lane and supplies a CAME barrier as a CAME partner.",
    ];
    $intro = barriersPick($openers, $seed, 0) . ' ' . barriersPopulationSentence($town) . ' ' . barriersSizeSentence($town);
    $body = 'The arm length, opening speed and safety kit — photocells, induction loops and edges as the lane requires — are chosen after survey, in line with BS EN 12453. '
        . 'We do not leave a barrier that can close on a vehicle because a loop or photocell was skipped. '
        . 'Where the site already has access control, an intercom or ANPR, we connect the barrier only after the fire exit and the fail-state are clear. '
        . barriersCoverageSentence($town);

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    executeTemplateVars(SITE_ROOT . '/templates/barriers-town.php', [
        'town' => $town,
        'intro' => $intro,
        'body' => $body,
        'faqs' => barriersFaqs($town),
        'nearby' => barriersNearby($town),
        'cameImage' => barriersCameImage(),
        'csrf' => (string)$_SESSION['csrf'],
    ]);
}

function renderBarriersIndexPage(): void
{
    $groups = [];
    foreach (barriersTowns() as $town) {
        $nation = (string)($town['nation'] ?? 'UK');
        $region = (string)($town['region'] ?? '');
        $groups[$nation][$region][] = $town;
    }
    foreach ($groups as &$regions) {
        ksort($regions);
        foreach ($regions as &$towns) {
            usort($towns, static fn($a, $b) => strcasecmp((string)$a['name'], (string)$b['name']));
        }
    }
    unset($regions, $towns);
    ksort($groups);

    executeTemplateVars(SITE_ROOT . '/templates/barriers-index.php', [
        'groups' => $groups,
        'meta' => barriersMeta(),
        'townCount' => count(barriersTowns()),
    ]);
}
