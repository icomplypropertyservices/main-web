<?php
/**
 * Town depth for nationwide AOV and barrier pages.
 *
 * Every sentence here is built from public facts for that place:
 *  - data/town-geo.json (postcodes.io: ONS Postcode Directory + OS Open Names):
 *    council, county, constituencies, postcode districts, wards, lat/lon.
 *  - data/barriers-places.json (ONS Census 2021 / NRS): housing, tenure, industry.
 *  - data/uk-towns-10k.json (ONS towns 2019): size, coastal class, workplace profile.
 * Straight-line distances are computed from coordinates. No reviews, projects,
 * job counts or response times are invented. iComply is not described as a
 * registered or certified body.
 */
declare(strict_types=1);

/** Page families with town depth switched on (staged rollout). */
const TOWN_DEPTH_FAMILIES = ['aov', 'barriers'];

const TOWN_DEPTH_YARD = [53.3925, -2.1265]; // Offerton, Stockport SK2 5DE

function townDepthGeo(string $slug): ?array
{
    static $geo = null;
    if ($geo === null) {
        $file = SITE_ROOT . '/data/town-geo.json';
        $d = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $geo = is_array($d['towns'] ?? null) ? $d['towns'] : [];
    }
    return $geo[$slug] ?? null;
}

function townDepthAllGeo(): array
{
    townDepthGeo('');
    static $all = null;
    if ($all === null) {
        $file = SITE_ROOT . '/data/town-geo.json';
        $d = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $all = is_array($d['towns'] ?? null) ? $d['towns'] : [];
    }
    return $all;
}

function townDepthCensus(string $slug): ?array
{
    static $idx = null;
    if ($idx === null) {
        $idx = [];
        $file = SITE_ROOT . '/data/barriers-places.json';
        $d = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        foreach ((array)$d as $r) {
            if (is_array($r) && !empty($r['slug'])) {
                $idx[(string)$r['slug']] = $r;
            }
        }
    }
    return $idx[$slug] ?? null;
}

function townDepthOns(string $slug): ?array
{
    static $idx = null;
    if ($idx === null) {
        $idx = [];
        $file = SITE_ROOT . '/data/uk-towns-10k.json';
        $d = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        foreach ((array)$d as $r) {
            if (is_array($r) && !empty($r['slug'])) {
                $idx[(string)$r['slug']] = $r;
            }
        }
    }
    return $idx[$slug] ?? null;
}

function townDepthLaCensus(string $council): ?array
{
    static $la = null;
    if ($la === null) {
        $file = SITE_ROOT . '/data/la-census.json';
        $d = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $la = is_array($d) ? $d : [];
    }
    return $la[$council] ?? null;
}

function townDepthMiles(float $la, float $lo, float $lb, float $lob): float
{
    $r = 3958.8;
    $p1 = deg2rad($la); $p2 = deg2rad($lb);
    $dp = $p2 - $p1; $dl = deg2rad($lob - $lo);
    $a = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;
    return 2 * $r * asin(min(1.0, sqrt($a)));
}

function townDepthBearing(float $la, float $lo, float $lb, float $lob): string
{
    $y = sin(deg2rad($lob - $lo)) * cos(deg2rad($lb));
    $x = cos(deg2rad($la)) * sin(deg2rad($lb)) - sin(deg2rad($la)) * cos(deg2rad($lb)) * cos(deg2rad($lob - $lo));
    $deg = fmod(rad2deg(atan2($y, $x)) + 360.0, 360.0);
    $pts = ['north', 'north-east', 'east', 'south-east', 'south', 'south-west', 'west', 'north-west'];
    return $pts[(int)round($deg / 45) % 8];
}

function townDepthList(array $items, string $and = 'and'): string
{
    $items = array_values(array_filter(array_map('strval', $items), static fn($s) => $s !== ''));
    if (count($items) <= 1) {
        return $items[0] ?? '';
    }
    $last = array_pop($items);
    return implode(', ', $items) . ' ' . $and . ' ' . $last;
}

function townDepthPick(int $seed, array $variants): string
{
    return $variants[$seed % count($variants)];
}

/** Council that owns building control / most local roads, from the geo row. */
function townDepthCouncil(array $g): string
{
    $d = trim((string)($g['district'] ?? ''));
    $c = trim((string)($g['county'] ?? ''));
    if ($d !== '') {
        return $d;
    }
    return $c;
}

function townDepthPct(?array $rows, string $needle): ?float
{
    foreach ((array)$rows as $r) {
        if (is_array($r) && stripos((string)($r['label'] ?? ''), $needle) !== false) {
            return round((float)$r['pct'], 1);
        }
    }
    return null;
}

/** @return list<array{name:string,slug:string,miles:float,dir:string}> */
function townDepthNearest(string $slug, array $familySlugs, int $limit = 6): array
{
    $me = townDepthGeo($slug);
    if (!$me) {
        return [];
    }
    $out = [];
    foreach (townDepthAllGeo() as $s => $g) {
        if ($s === $slug || !isset($familySlugs[$s])) {
            continue;
        }
        $m = townDepthMiles($me['lat'], $me['lon'], $g['lat'], $g['lon']);
        if ($m < 0.5 || $m > 40) {
            continue;
        }
        $out[] = ['name' => (string)$familySlugs[$s], 'slug' => $s, 'miles' => $m, 'dir' => townDepthBearing($me['lat'], $me['lon'], $g['lat'], $g['lon'])];
    }
    usort($out, static fn($a, $b) => $a['miles'] <=> $b['miles']);
    return array_slice($out, 0, $limit);
}

function townDepthNationRules(string $service, string $nation, bool $london, int $seed): array
{
    $paras = [];
    if ($service === 'aov') {
        switch ($nation) {
            case 'Scotland':
                $paras[] = 'In Scotland the design benchmark is the Building (Scotland) Regulations 2004 and Section 2 (Fire) of the Technical Handbooks, not English Approved Document B. Duties on the people who run a building sit under the Fire (Scotland) Act 2005 and the Fire Safety (Scotland) Regulations 2006.';
                break;
            case 'Wales':
                $paras[] = 'In Wales the Welsh edition of Approved Document B applies to new work, and the Regulatory Reform (Fire Safety) Order 2005 still places the maintenance duty on the responsible person. Higher-risk building rules in Wales are being phased in on a different timetable from England, so we work to whatever the building’s current strategy and the Welsh Government guidance say.';
                break;
            case 'Northern Ireland':
                $paras[] = 'In Northern Ireland new work is judged against the Building Regulations (Northern Ireland) 2012 and Technical Booklet E, and the people who run a building have duties under the Fire and Rescue Services (Northern Ireland) Order 2006 and the Fire Safety Regulations (Northern Ireland) 2010.';
                break;
            default:
                $paras[] = townDepthPick($seed, [
                    'In England new and altered smoke vents are designed to Approved Document B, usually with BS 9991 for flats or BS 9999 for other buildings behind it. Once the building is occupied, the Regulatory Reform (Fire Safety) Order 2005 makes the responsible person keep the system working.',
                    'For an English building the design reference is Approved Document B, normally read with BS 9991 (residential) or BS 9999 (non-residential). The duty to keep vents working afterwards sits with the responsible person under the Regulatory Reform (Fire Safety) Order 2005.',
                ]);
                $paras[0] .= ' Blocks of 18 metres or seven storeys and up are higher-risk buildings under the Building Safety Act 2022; we test and record the vents for the accountable person, but we are not the regulator.';
                break;
        }
        $paras[count($paras) - 1] .= ' ' . townDepthPick($seed >> 3, [
            'Vents are product-tested to BS EN 12101-2 (natural) or BS EN 12101-3 (powered).',
            'Product references: BS EN 12101-2 for natural vents, BS EN 12101-3 for fans, BS EN 12101-9 and -10 for panels and power supplies.',
            'Service visits follow BS 7346-8 and check the vents against BS EN 12101-2.',
        ]);
    } else {
        $paras[] = townDepthPick($seed, [
            'Powered barriers are machinery: BS EN 12453 for safety in use, with a declaration and lane risk assessment at handover.',
            'A powered arm is checked against BS EN 12453 and supplied under the Supply of Machinery (Safety) Regulations 2008.',
        ]);
        $paras[0] .= ' ' . townDepthPick($seed >> 2, [
            'Manual barriers and restrictors have no motor, but still need a true clearance sign and a lock in both positions.',
            'For manual arms and width or height restrictors we measure the real clearance after fitting and sign that figure.',
        ]);
        if ($nation === 'Scotland') {
            $paras[] = 'In Scotland, work on a private car park entrance may also need a road works permit if it touches the adopted road, which is handled by the council as roads authority.';
        } elseif ($london) {
            $paras[] = 'In London the borough is highway authority for most local roads, and Transport for London for its red routes, so any barrier footing near the boundary of an adopted road is checked against who owns that strip.';
        }
    }
    // Keep this section short: it is the same law for every town in a nation.
    return array_slice($paras, 0, 1);
}

/**
 * @param string $service 'aov' or 'barriers'
 * @param array<string,string> $familySlugs slug => display name for that page family
 * @return array{sections:list<array{h:string,p:list<string>,list?:list<array{href:string,label:string}>}>,faq:list<array{q:string,a:string}>}
 */
function townDepth(string $service, string $slug, string $name, string $nation, array $familySlugs): array
{
    $g = townDepthGeo($slug);
    if ($g) {
        // "Tunstall" is a nurse-call brand on this site; keep it out of town copy.
        $noBrand = static fn($w) => stripos((string)$w, 'tunstall') === false;
        $g['wards'] = array_values(array_filter((array)($g['wards'] ?? []), $noBrand));
        foreach ((array)($g['districts'] ?? []) as $k => $dd) {
            $g['districts'][$k]['wards'] = array_values(array_filter((array)($dd['wards'] ?? []), $noBrand));
        }
    }
    $c = townDepthCensus($slug);
    $censusScope = $name;
    $isAov = $service === 'aov';
    if (!$c && $g) {
        $la = townDepthLaCensus(townDepthCouncil($g));
        if ($la) {
            $council = townDepthCouncil($g);
            $censusScope = "the {$council} council area";
            $rows = static fn(array $r) => array_map(static fn($x) => ['label' => $x[0], 'count' => $x[1], 'pct' => $x[2]], $r);
            $c = ['housing' => $rows($la['housing']), 'tenure' => $rows($la['tenure']), 'industry' => [],
                'source' => 'ONS Census 2021, whole council area', 'code' => '',
                '_note' => 'ONS Census 2021 tables TS044 and TS054 for the whole ' . $council . ' council area (' . $la['code'] . '), not just ' . $name . '. ' . number_format((float)$la['households']) . ' households. We do not hold town-level census figures for this place. They describe homes, not installed ' . ($isAov ? 'smoke vents' : 'barriers') . '.',
                'population' => 0, '_households' => $la['households']];
        }
    }
    $o = townDepthOns($slug);
    $seed = crc32($slug . '|' . $service . '|depth');
    $sections = [];
    $faq = [];
    $nation = $nation !== '' ? $nation : (string)($g['ons_region'] ?? '');
    $isAov = $service === 'aov';
    $what = $isAov ? 'smoke-vent' : 'barrier';
    $london = ($g['ons_region'] ?? '') === 'London' || ($c['region'] ?? '') === 'London';

    // 1. Where the place sits.
    if ($g) {
        $council = townDepthCouncil($g);
        $county = (string)($g['county'] ?? '');
        $ocs = array_slice((array)($g['outcodes'] ?? []), 0, 6);
        $wards = array_slice((array)($g['wards'] ?? []), 0, 8);
        $cons = (array)($g['constituencies'] ?? []);
        $miles = townDepthMiles(TOWN_DEPTH_YARD[0], TOWN_DEPTH_YARD[1], (float)$g['lat'], (float)$g['lon']);
        $dir = townDepthBearing(TOWN_DEPTH_YARD[0], TOWN_DEPTH_YARD[1], (float)$g['lat'], (float)$g['lon']);
        $mi = (int)round($miles);
        $p = [];
        $lead = $council !== '' ? "{$name} sits in the {$council} council area" . ($county !== '' && $county !== $council ? ", {$county}" : '') . '.' : "{$name} is in " . ($county ?: $nation) . '.';
        if ($ocs) {
            $lead .= ' ' . townDepthPick($seed, [
                'The postcode districts around the centre are ' . townDepthList($ocs) . '.',
                'Addresses here mostly start ' . townDepthList($ocs, 'or') . '.',
                'Send us a postcode beginning ' . townDepthList($ocs, 'or') . ' and we can place the building straight away.',
            ]);
        }
        $p[] = $lead;
        if ($wards) {
            $p[] = townDepthPick($seed >> 1, [
                'Council wards that share those districts include ' . townDepthList($wards) . '.',
                'Wards covered by the same postcode districts: ' . townDepthList($wards) . '.',
            ]) . ($cons ? (count($cons) > 1 ? ' Parliamentary constituencies: ' . implode('; ', $cons) . '.' : ' Parliamentary constituency: ' . $cons[0] . '.') : '');
        }
        $dl = [];
        foreach ((array)($g['districts'] ?? []) as $dd) {
            if (!empty($dd['wards'])) {
                $dl[] = $dd['oc'] . ' (' . implode(', ', array_slice($dd['wards'], 0, 5)) . ')';
            }
        }
        if (count($dl) >= 2) {
            $p[] = 'By postcode district: ' . implode('; ', array_slice($dl, 0, 5)) . '.';
        }
        if ($mi <= 3) {
            $p[] = "Our yard at Offerton, Stockport SK2 5DE is effectively on the doorstep, so {$what} visits here fit around other local work.";
        } else {
            $band = $mi <= 25
                ? 'That is a day visit from the yard, and travel is usually a small line on the quote.'
                : ($mi <= 90
                    ? 'Visits at that range are batched into planned days, and travel is written on the quote rather than hidden in the rate.'
                    : 'At that distance travel, and any overnight stay, is priced and written on the quote before anything is booked.');
            $p[] = "{$name} is about {$mi} miles {$dir} of our Offerton, Stockport SK2 5DE yard in a straight line. {$band}";
        }
        $sections[] = ['h' => townDepthPick($seed >> 2, ["Where {$name} sits for a {$what} visit", "{$name}: council, postcodes and travel", "Planning a {$what} job in {$name}"]), 'p' => $p];

        $faq[] = [
            'q' => townDepthPick($seed >> 4, ["Do you cover " . townDepthList(array_slice($ocs, 0, 4), 'and') . "?", "Which {$name} postcodes do you take {$what} work in?"]),
            'a' => 'Yes. ' . ($ocs ? townDepthList($ocs) . ' are all quoted from the Stockport yard' : 'The whole area is quoted from the Stockport yard') . ", about {$mi} miles away in a straight line. Send the full postcode and a photo of the " . ($isAov ? 'control panel or the vent' : 'barrier cabinet or the entrance') . '.' . ($wards ? ' That takes in wards such as ' . townDepthList(array_slice($wards, $seed % max(1, count($wards) - 3), 3)) . '.' : ''),
        ];
        $nearFaq = townDepthNearest($slug, $familySlugs, 3);
        $faq[] = [
            'q' => townDepthPick($seed >> 6, ["How far is {$name} from your yard?", "Is {$name} too far for a {$what} visit?"]),
            'a' => "{$name} is about {$mi} miles {$dir} of Offerton, Stockport SK2 5DE as the crow flies" . ($nearFaq ? '. The nearest other places we have pages for are ' . townDepthList(array_map(static fn($n) => $n['name'] . ' (' . (int)max(1, round($n['miles'])) . ' miles ' . $n['dir'] . ')', $nearFaq)) . ', so visits can be combined' : '') . '. Travel is shown on the quote.',
        ];
        if ($isAov) {
            $faq[] = [
                'q' => "Who signs off a replacement smoke vent in {$name}?",
                'a' => ($nation === 'Scotland'
                    ? "A building warrant may be needed from {$council} for new or altered smoke ventilation; like-for-like repairs usually are not. We tell you which applies before we start."
                    : "Like-for-like repairs normally need no application. New or altered smoke ventilation is building work, so it goes to {$council} building control or a registered building control approver" . ($nation === 'England' ? ', or to the Building Safety Regulator if the block is a higher-risk building' : '') . '.'),
            ];
        } else {
            $hw = ($g['county_type'] ?? '') === 'County' && $county !== '' ? $county . ' County Council' : $council;
            $faq[] = [
                'q' => "Can a barrier go across an entrance off a public road in {$name}?",
                'a' => "Only on land you own or control. If the arm, its footing or the queue it creates would sit on the adopted road or pavement, {$hw} as highway authority" . ($london ? ' (or TfL on a red route)' : '') . ' has the say.',
            ];
        }
    }

    // 2. Building stock from the census.
    $stock = [];
    if ($c) {
        $flats = townDepthPct($c['housing'] ?? null, 'Flat');
        $det = townDepthPct($c['housing'] ?? null, 'Detached');
        $terr = townDepthPct($c['housing'] ?? null, 'Terraced');
        $social = townDepthPct($c['tenure'] ?? null, 'social');
        $prent = townDepthPct($c['tenure'] ?? null, 'Private rented');
        $ind = array_slice((array)($c['industry'] ?? []), 0, 3);
        $topInd = $ind ? strtolower(preg_replace('/;.*$/', '', (string)$ind[0]['label'])) : '';
        $src = (string)($c['source'] ?? 'ONS Census 2021');
        if ($flats !== null) {
            if ($isAov) {
                $stock[] = $flats >= 30
                    ? "Flats, maisonettes and apartments are {$flats}% of homes in the {$src} count for {$censusScope}. That is a large flatted share, so most of our calls here are likely to be head-of-stair roof vents, corridor vents into shafts and lobby vents in purpose-built blocks."
                    : ($flats >= 15
                        ? "{$flats}% of homes in {$censusScope} are flats or maisonettes ({$src}). Smoke vents sit in the purpose-built blocks within that share, and in schools, care homes, offices and shopping centres rather than in ordinary houses."
                        : "Only {$flats}% of homes in {$censusScope} are flats ({$src})" . ($det !== null && $det >= 30 ? ", with detached houses at {$det}%" : ($terr !== null && $terr >= 30 ? ", with terraces at {$terr}%" : '')) . '. Houses do not have AOVs, so work here tends to be the smaller number of flat blocks plus schools, care homes, offices, retail units and industrial roofs.');
            } else {
                $stock[] = $flats >= 30
                    ? "{$flats}% of homes in {$censusScope} are flats ({$src}), so shared residents’ car parks under or behind blocks are common, and an arm controlled by fob, ANPR or intercom is the usual request."
                    : ($flats >= 15
                        ? "Flats make up {$flats}% of homes in {$censusScope} ({$src}). Barrier work is likely to split between residents’ car parks at those blocks and commercial yards."
                        : "With flats at only {$flats}% of homes ({$src}), most barrier requests in {$name} are likely to come from business premises, schools, clinics and private car parks rather than residents’ parking.");
            }
        }
        if ($social !== null && $social >= 18) {
            $stock[] = "Social renting is {$social}% of households" . ($prent !== null ? " and private renting {$prent}%" : '') . ', so housing associations and councils manage much of the stock; ' . ($isAov ? 'their fire-safety teams usually want a vent-by-vent record they can file against each block.' : 'they usually want a barrier spec that survives heavy use and a key or fob plan for residents.');
        } elseif ($prent !== null && $prent >= 22) {
            $stock[] = "Private renting is {$prent}% of households in {$censusScope}, " . ($isAov ? 'so managing agents and freeholders are often the people who call about a vent that will not close.' : 'so managing agents of apartment blocks are often the people who ask about car-park barriers.');
        }
        if ($topInd !== '') {
            $list = [];
            foreach ($ind as $r) {
                $list[] = strtolower(preg_replace('/;.*$/', '', (string)$r['label'])) . ' (' . round((float)$r['pct'], 1) . '%)';
            }
            $hint = '';
            if (str_contains($topInd, 'health')) {
                $hint = $isAov ? 'Care homes and clinics with protected stairs are part of the AOV picture there.' : 'Clinic, surgery and care-home car parks are a typical barrier site where health is a big employer.';
            } elseif (str_contains($topInd, 'manufactur')) {
                $hint = $isAov ? 'Factory and warehouse roofs often carry smoke and heat exhaust ventilators, which are a different job from a stair vent.' : 'Industrial estates and factory yards usually need heavier, faster arms and loop detection for HGVs, plus height restrictors on staff car parks.';
            } elseif (str_contains($topInd, 'transport') || str_contains($topInd, 'storage')) {
                $hint = $isAov ? 'Warehouse and distribution sheds are large single volumes where roof smoke ventilators, not stair vents, are the usual system.' : 'Distribution yards want long booms, HGV loops and height restrictors to keep high vehicles off car-park decks.';
            } elseif (str_contains($topInd, 'education')) {
                $hint = $isAov ? 'Schools and colleges with atria and enclosed stairs are a common source of AOV faults.' : 'School and college car parks often want a barrier that stays open at drop-off times and locks outside them.';
            } elseif (str_contains($topInd, 'retail') || str_contains($topInd, 'wholesale')) {
                $hint = $isAov ? 'Shopping centres and retail units with malls or mezzanines usually have their own smoke-control strategy.' : 'Retail parks and shop service yards often pair a powered arm with a height restrictor at the customer entrance.';
            } else {
                $hint = $isAov ? 'Offices and mixed-use blocks make up much of the non-residential AOV work in a place like this.' : 'Office car parks and mixed-use blocks make up much of the commercial barrier work in a place like this.';
            }
            $stock[] = "The largest employment groups among residents are " . townDepthList($list) . ". {$hint} Those are residents’ jobs, not a list of our clients.";
        }
    } elseif ($o) {
        $bits = [];
        if (!empty($o['workplace'])) {
            $bits[] = 'classed as a ' . strtolower((string)$o['workplace']) . ' town';
        }
        if (!empty($o['coastal']) && stripos((string)$o['coastal'], 'non-coastal') === false) {
            $bits[] = 'on the coast, where salt air shortens the life of roof vent hinges and actuator glands';
        }
        if ($bits) {
            $stock[] = 'In the ONS towns data, ' . $name . ' is ' . implode(', and ', $bits) . '.';
        }
    }
    if ($o && !empty($o['coastal']) && $c && stripos((string)$o['coastal'], 'non-coastal') === false) {
        $stock[] = $isAov
            ? "{$name} is a coastal town in the ONS classification. Salt air corrodes roof-vent hinges, chain actuators and cable glands faster, so we look harder at those parts on exposed lids."
            : "{$name} is classed by ONS as coastal. Salt spray is hard on barrier cabinets and arm fixings, so we specify sealed housings and stainless fixings near the front.";
    }
    if ($c) {
        $tables = [];
        foreach (['Homes by type' => 'housing', 'Households by tenure' => 'tenure', 'Largest industries (employed residents)' => 'industry', 'Largest occupations' => 'occupation'] as $label => $key) {
            $rows = [];
            foreach ((array)($c[$key] ?? []) as $r) {
                if (is_array($r) && isset($r['label'], $r['count'])) {
                    $rows[] = [str_replace('Whole house or bungalow: ', '', (string)$r['label']), number_format((float)$r['count']), round((float)$r['pct'], 1) . '%'];
                }
            }
            if ($rows) {
                $tables[$label] = $rows;
            }
        }
        if ($tables && !empty($c['_note'])) {
            $stockTables = ['tables' => $tables, 'note' => (string)$c['_note'], 'scope' => ucfirst($censusScope)];
        } elseif ($tables) {
            $note = (string)($c['source'] ?? 'ONS Census 2021') . (!empty($c['code']) ? ' ' . $c['code'] : '') . '. ' . (!empty($c['_households']) ? number_format((float)$c['_households']) . ' households' : number_format((float)($c['population'] ?? 0)) . ' usual residents')
                . (isset($c['median_age']) ? ', median age ' . $c['median_age'] : '') . (isset($c['employed_pct']) ? ', ' . $c['employed_pct'] . '% of over-16s employed' : '')
                . '. Counts are rounded by the publisher. They describe residents and homes, not installed ' . ($isAov ? 'smoke vents' : 'barriers') . '.';
            $stockTables = ['tables' => $tables, 'note' => $note, 'scope' => ucfirst($censusScope)];
        }
    }
    if ($stock) {
        $sections[] = ['h' => $isAov ? townDepthPick($seed >> 5, ["Which {$name} buildings tend to have smoke vents", "Building stock in {$name} and what it means for AOVs"]) : townDepthPick($seed >> 5, ["Where barriers get used in {$name}", "{$name} sites that usually want a barrier"]), 'p' => $stock, 'tables' => $stockTables ?? null];
    }
    if ($isAov && $c) {
        $flats = townDepthPct($c['housing'] ?? null, 'Flat');
        $faq[] = [
            'q' => "Is my {$name} block a higher-risk building?",
            'a' => $nation === 'England'
                ? 'If it has at least two homes and is at least 18 metres or seven storeys tall, yes, under the Building Safety Act 2022. ' . ($flats !== null ? "{$flats}% of homes in {$censusScope} are flats, but most blocks are lower than that. " : '') . 'Either way the smoke vents still need routine testing under the Fire Safety Order.'
                : 'The 18-metre higher-risk regime is English law. In ' . $nation . ' the vents are judged against the building’s own fire strategy and the national rules, and they still need routine testing.',
        ];
    }
    if (!$isAov) {
        $faq[] = [
            'q' => "Do you fit manual barriers and height restrictors in {$name}, or only powered arms?",
            'a' => 'Both: manual swing and drop arms, width restrictors and height restrictors as well as CAME powered arms.',
        ];
    }

    // 3. Rules.
    $sections[] = ['h' => $isAov ? "Rules that apply to smoke vents in {$name}" : "Standards behind a barrier in {$name}", 'p' => townDepthNationRules($service, $nation, $london, $seed)];

    // 4. Nearest pages of the same family.
    $near = townDepthNearest($slug, $familySlugs);
    if ($near) {
        $links = [];
        foreach ($near as $n) {
            $links[] = ['href' => url('/pages/' . $service . '/' . $n['slug']), 'label' => $n['name'] . ' (' . (int)max(1, round($n['miles'])) . ' mi ' . $n['dir'] . ')'];
        }
        $sections[] = ['h' => "Nearest " . ($isAov ? 'AOV' : 'barrier') . " pages to {$name}", 'p' => ['Straight-line distances from the centre of ' . $name . '. Each page uses its own council, postcode and census figures.'], 'list' => $links];
    }

    // Seeded order for the first sections (rules stay after stock, nearby last).
    if (count($sections) >= 3 && ($seed % 2) === 1) {
        [$sections[0], $sections[1]] = [$sections[1], $sections[0]];
    }
    if (($seed % 3) === 2) {
        $faq = array_reverse($faq);
    }
    return ['sections' => $sections, 'faq' => $faq];
}

function townDepthHtml(array $depth, string $name, string $tone = 'light'): string
{
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $out = '';
    foreach ($depth['sections'] as $sec) {
        $out .= '<section class="mt-10"><h2 class="text-2xl font-semibold text-black">' . $h($sec['h']) . '</h2>';
        foreach ($sec['p'] as $p) {
            $out .= '<p class="mt-3 text-zinc-700 leading-relaxed">' . $h($p) . '</p>';
        }
        if (!empty($sec['tables'])) {
            $out .= '<div class="mt-4 grid sm:grid-cols-2 gap-4 text-sm">';
            foreach ($sec['tables']['tables'] as $label => $rows) {
                $out .= '<table class="w-full border rounded-xl"><caption class="text-left font-semibold px-3 py-2">' . $h(($sec['tables']['scope'] ?? $name) . ': ' . strtolower($label)) . '</caption><tbody>';
                foreach ($rows as $r) {
                    $out .= '<tr class="border-t"><td class="px-3 py-1">' . $h($r[0]) . '</td><td class="px-2 py-1 text-right">' . $h($r[1]) . '</td><td class="px-3 py-1 text-right">' . $h($r[2]) . '</td></tr>';
                }
                $out .= '</tbody></table>';
            }
            $out .= '</div><p class="mt-2 text-xs text-zinc-500">' . $h($sec['tables']['note']) . '</p>';
        }
        if (!empty($sec['list'])) {
            $out .= '<ul class="mt-3 flex flex-wrap gap-2">';
            foreach ($sec['list'] as $l) {
                $out .= '<li><a class="inline-block px-4 py-2 border rounded-full text-sm hover:border-[#ff6b00]" href="' . $h($l['href']) . '">' . $h($l['label']) . '</a></li>';
            }
            $out .= '</ul>';
        }
        $out .= '</section>';
    }
    if ($depth['faq']) {
        $out .= '<section class="mt-10"><h2 class="text-2xl font-semibold text-black">' . $h("Questions about {$name}") . '</h2><div class="mt-3 divide-y border rounded-2xl">';
        foreach ($depth['faq'] as $f) {
            $out .= '<details class="p-4"><summary class="font-semibold cursor-pointer">' . $h($f['q']) . '</summary><p class="mt-2 text-zinc-700">' . $h($f['a']) . '</p></details>';
        }
        $out .= '</div></section>';
        $ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
        foreach ($depth['faq'] as $f) {
            $ld['mainEntity'][] = ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]];
        }
        $out .= '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    return $out;
}
