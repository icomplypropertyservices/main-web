<?php
/**
 * Mainland UK AOV town pages.
 *
 * Data: website/data/aov-towns.json (GeoNames cities5000, population > 10,000).
 * Pages: /pages/aov and /pages/aov/{slug}
 *
 * Copy is assembled from the town record (population, county, nation, straight-line
 * distance from Stockport). It does not invent jobs, depots, or prices.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

function aovTownCatalogue(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $path = SITE_ROOT . '/data/aov-towns.json';
    $raw = is_file($path) ? file_get_contents($path) : false;
    $decoded = $raw ? json_decode($raw, true) : null;
    $data = is_array($decoded) ? $decoded : ['count' => 0, 'towns' => []];
    return $data;
}

/** @return list<array<string,mixed>> */
function aovTowns(): array
{
    $towns = aovTownCatalogue()['towns'] ?? [];
    return is_array($towns) ? $towns : [];
}

function aovTownCount(): int
{
    return count(aovTowns());
}

function aovTownBySlug(string $slug): ?array
{
    static $index = null;
    if ($index === null) {
        $index = [];
        foreach (aovTowns() as $town) {
            if (!empty($town['slug'])) {
                $index[(string)$town['slug']] = $town;
            }
        }
    }
    return $index[$slug] ?? null;
}

/** @return list<string> */
function aovTownPaths(): array
{
    $paths = ['/pages/aov'];
    foreach (aovTowns() as $town) {
        $paths[] = '/pages/aov/' . $town['slug'];
    }
    return $paths;
}

function aovSeed(string $slug, string $extra = ''): int
{
    return abs(crc32($slug . '|' . $extra));
}

function aovPlaceLine(array $town): string
{
    $name = (string)$town['name'];
    $county = (string)$town['county'];
    $region = (string)$town['region'];
    $nation = (string)$town['nation'];
    if ($county === $name || str_starts_with($county, $name . ' ')) {
        $where = match ($region) {
            'North East' => 'the North East of England',
            'North West' => 'the North West of England',
            'East Midlands' => 'the East Midlands',
            'West Midlands' => 'the West Midlands',
            'East of England' => 'the East of England',
            'South East' => 'the South East of England',
            'South West' => 'the South West of England',
            default => $region,
        };
        return "{$name} is in {$where}.";
    }
    return "{$name} is in {$county}, {$region}.";
}

function aovOpening(array $town): string
{
    $label = (string)$town['label'];
    $name = (string)$town['name'];
    $pop = number_format((int)$town['population']);
    $km = (int)$town['km'];
    $bearing = (string)$town['bearing'];
    $county = (string)$town['county'];

    if ($km < 1) {
        return "{$label} is the base town. Icomply Property Services works from 17 Woodlands Park Road, Offerton, Stockport, SK2 5DE. "
            . "GeoNames records the population of {$name} as {$pop}. "
            . "This page is that Stockport record. It does not mean every block in the borough is on a contract.";
    }

    $distance = "{$label} is about {$km} km {$bearing} of Stockport, measured in a straight line from the GeoNames point for Stockport, not as a drive time. "
        . "The workshop is in Offerton, SK2 5DE. There is no depot in {$name}.";

    if (!empty($town['also']) && is_array($town['also'])) {
        $other = (string)($town['also'][0]['label'] ?? 'another record');
        return "{$label} is one GeoNames record named {$name}. Another page covers {$other}. "
            . "This record is the one in {$county}, population {$pop}. Send the postcode. "
            . $distance;
    }

    if ((string)$town['slug'] === 'london') {
        return "London is a single GeoNames record on this index, population {$pop}. "
            . "Croydon, Romford, and the other Greater London pages are separate. "
            . "This page is not a block address. {$distance}";
    }

    if ($county === 'Greater London') {
        return "{$label} is in Greater London. GeoNames population {$pop}. "
            . "It has its own page and is not folded into the London page. {$distance}";
    }

    return aovPlaceLine($town) . " GeoNames records the population as {$pop}. {$distance}";
}

function aovRulesParagraph(array $town): string
{
    $label = (string)$town['label'];
    $nation = (string)$town['nation'];
    $band = (string)$town['band'];
    $county = (string)$town['county'];

    if ($nation === 'Scotland') {
        return "{$label} is in Scotland. Fire safety in the building is set by the Building (Scotland) Regulations and the Technical Handbooks, domestic or non-domestic, Section 2. "
            . "An English specification pasted onto a block in {$label} is the wrong document. "
            . "The ventilator may still be built to BS EN 12101. The pass or fail is the Scottish handbook and the fire strategy for that building.";
    }

    if ($nation === 'Wales') {
        return "Wales writes its own building regulations. In {$label} the fire-safety guidance is Approved Document B issued for Wales, not the English volumes. "
            . "Do not send an English fire strategy and expect it to be read as Welsh guidance. "
            . "Smoke-control equipment is commonly specified to BS EN 12101. The survey follows the fire strategy for the building in front of us.";
    }

    $height = ($band === 'city' || $band === 'large' || $county === 'Greater London')
        ? "If a residential building in {$label} is at least 18 metres or seven storeys, the higher-risk building rules under the Building Safety Act apply in England. This page cannot know the height. Send the floor count."
        : "A {$label} enquiry often has no 18-metre residential building. Do not assume the higher-risk building regime. Send the floor count before anyone names a legal category.";

    return "Smoke control for a building in {$label} is judged under the Building Regulations for England. "
        . "Approved Document B is the usual guidance, volume 1 for dwellings and volume 2 for other buildings. "
        . "Stair vents and lobby vents are checked against that building’s fire strategy. "
        . "BS EN 12101 is the series for the smoke and heat control equipment. {$height}";
}

function aovBookingParagraph(array $town): string
{
    $label = (string)$town['label'];
    $km = (int)$town['km'];
    if ($km < 1) {
        return "Surveys in {$label} are diary slots from the Offerton workshop. A same-week visit happens only when the diary has the gap.";
    }
    if ($km < 50) {
        return "{$label} is close enough to sit on the Stockport diary. It is still a booked slot. There is not a van based in the town.";
    }
    if ($km < 160) {
        return "{$km} km puts {$label} on a booked day from Stockport. It is not a gap between North West jobs.";
    }
    return "{$km} km makes {$label} a planned mainland visit. Travel is part of the written quote. It is not a local call-out.";
}

function aovSurveyParagraph(array $town): string
{
    $label = (string)$town['label'];
    return "A survey in {$label} starts from the fire strategy and the panel on the wall. "
        . "The town page does not know the actuator, the stair count, or whether the vent failed open or closed. "
        . "If the panel make is visible, send it. SE Controls, Nuaire, Brooks, Geze and D+H turn up often. Other makes are in scope. "
        . "A stair vent, a roof hatch and a smoke shaft are different jobs. The quote needs the address.";
}

/** @return list<string> */
function aovTechnicalPoints(array $town): array
{
    $pool = [
        'A vent that stops short of its fire position is a fail, even when the motor ran.',
        'Rain and wind sensors must not hold the vent shut after the fire signal. That override is part of the test.',
        'Failed open is a weather and security fault. Failed closed is a smoke-control fault. The note has to say which.',
        'The panel battery needs a dated discharge test. A lit lamp is not that test.',
        'The fire-alarm interface is tested with the vent. Cause and effect is not left as an assumption.',
        'A replacement actuator has to match voltage, stroke and force. The old part number is not enough if the vent was changed.',
        'A stair vent and a corridor vent are different devices. The certificate names the floor and the device.',
        'The manual control beside the stair should open the vent without a delay written for another floor.',
    ];
    $seed = aovSeed((string)$town['slug'], 'tech');
    $picked = [];
    $count = count($pool);
    for ($i = 0; $i < 3; $i++) {
        $picked[] = $pool[($seed + $i * 3) % $count];
    }
    return array_values(array_unique($picked));
}

/** @return list<array{q:string,a:string}> */
function aovFaqs(array $town): array
{
    $label = (string)$town['label'];
    $pop = number_format((int)$town['population']);
    $km = (int)$town['km'];
    $near = $town['near'][0] ?? null;
    $nearText = is_array($near)
        ? $near['name'] . ' is the nearest other town on this index, about ' . $near['km'] . ' km away.'
        : 'Nearest towns are linked at the bottom of this page.';
    $rules = match ((string)$town['nation']) {
        'Scotland' => 'The Building (Scotland) Regulations and Technical Handbook Section 2. Not English guidance.',
        'Wales' => 'Approved Document B issued for Wales, plus the fire strategy for the building.',
        default => 'The Building Regulations for England and Approved Document B, plus the fire strategy for the building.',
    };
    $office = $km < 1
        ? "Yes. The workshop is in Offerton, Stockport, SK2 5DE. GeoNames population for Stockport is {$pop}."
        : "No. The only workshop is in Offerton, Stockport, SK2 5DE. {$label} is about {$km} km away in a straight line.";

    return [
        [
            'q' => "Do you have an office in {$label}?",
            'a' => $office,
        ],
        [
            'q' => "Which fire-safety rules apply to an AOV in {$label}?",
            'a' => $rules . ' BS EN 12101 covers the smoke and heat control equipment. The population figure on this page (' . $pop . ') does not change the standard.',
        ],
        [
            'q' => "What do you need before you quote AOV work in {$label}?",
            'a' => 'The building address, the floor count, and the panel make if you can read it. There is no price on this page. A town of ' . $pop . ' people is not a quote.',
        ],
        [
            'q' => "Which listed town is closest to {$label}?",
            'a' => $nearText . ' Use that page only if the building is actually there.',
        ],
    ];
}

function aovMetaDescription(array $town): string
{
    $label = (string)$town['label'];
    $pop = number_format((int)$town['population']);
    $km = (int)$town['km'];
    if ($km < 1) {
        $text = "AOV smoke control in {$label}. Population {$pop}. Workshop at Offerton, SK2 5DE. No price on this page. Send the building address and floor count.";
    } else {
        $text = "AOV smoke control in {$label}. Population {$pop}. About {$km} km from Stockport, straight-line. Survey booked from SK2 5DE. No depot and no price on this page.";
    }
    if (mb_strlen($text) > 160) {
        $text = "AOV smoke control in {$label}. About {$km} km from Stockport. Survey from SK2 5DE. No depot and no price on this page.";
    }
    if (mb_strlen($text) < 80) {
        $text .= ' Send the address and floor count.';
    }
    return $text;
}

/** @return list<array{heading:string,paragraph:string,points?:list<string>}> */
function aovSections(array $town): array
{
    $label = (string)$town['label'];
    $rules = ['heading' => "Fire-safety rules in {$label}", 'paragraph' => aovRulesParagraph($town)];
    $survey = [
        'heading' => "What a survey in {$label} checks",
        'paragraph' => aovSurveyParagraph($town),
        'points' => aovTechnicalPoints($town),
    ];
    $booking = ['heading' => "Booking the visit to {$label}", 'paragraph' => aovBookingParagraph($town)];
    $order = aovSeed((string)$town['slug'], 'layout') % 3;
    return match ($order) {
        1 => [$booking, $rules, $survey],
        2 => [$survey, $booking, $rules],
        default => [$rules, $survey, $booking],
    };
}

function renderAovTownIndex(): void
{
    $towns = aovTowns();
    $catalogue = aovTownCatalogue();
    $byLetter = [];
    foreach ($towns as $town) {
        $letter = strtoupper(substr((string)$town['name'], 0, 1));
        $byLetter[$letter][] = $town;
    }
    ksort($byLetter);
    $pageTitle = 'AOV towns over 10,000 | Icomply';
    $metaDesc = count($towns) . ' AOV pages for UK mainland towns with a GeoNames population over 10,000. England, Wales and mainland Scotland. No depot and no price.';
    $metaKeywords = 'AOV smoke control, automatic opening vents, UK towns, BS EN 12101, Stockport';
    $canonicalUrl = url('/pages/aov');
    $ogImage = url('/assets/images/services/aov-air-handling.jpg');
    require SITE_ROOT . '/includes/header.php';
    require SITE_ROOT . '/templates/aov-town-index.php';
}

function renderAovTownPage(array $town): void
{
    $pageTitle = 'AOV in ' . $town['label'] . ' | Icomply';
    $metaDesc = aovMetaDescription($town);
    $metaKeywords = 'AOV ' . $town['label'] . ', smoke vent ' . $town['name'] . ', BS EN 12101, smoke control ' . $town['county'];
    $canonicalUrl = url('/pages/aov/' . $town['slug']);
    $ogImage = url('/assets/images/services/aov-air-handling.jpg');
    $sections = aovSections($town);
    $faqs = aovFaqs($town);
    $opening = aovOpening($town);
    require SITE_ROOT . '/includes/header.php';
    require SITE_ROOT . '/templates/aov-town.php';
}
