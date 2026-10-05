<?php
/**
 * Greater Manchester AOV and barrier pages that are not on the 10,000-resident
 * gazetteer (or, for boroughs, are not a single built-up area).
 * These are real /pages/aov/{slug} and /pages/barriers/{slug} pages.
 * They are not aliases of Manchester, Salford or a neighbouring town.
 */
declare(strict_types=1);

/**
 * @return array<string,array<string,mixed>>
 */
function icomplyGmServiceTownRecords(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $rows = [
        'cadishead' => icomplyGmTown(
            'Cadishead',
            6500,
            'Salford',
            53.4230,
            -2.4270,
            ['aov', 'barriers'],
            'M44',
            [
                'aov' => 'AOV smoke control in Cadishead | iComply',
                'barriers' => 'CAME barriers in Cadishead | iComply',
            ],
            [
                'aov' => 'Cadishead M44 smoke vents are on low blocks by Liverpool Road and the Ship Canal. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Cadishead M44 vehicle barriers are on yards off Cadishead Way, not the Liverpool Road terraces. CAME partner. Quote from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Cadishead is the M44 stretch of Salford along Liverpool Road toward the Manchester Ship Canal, with Irlam at the station end of the same outward code. Most homes are two-storey terraces and semis. The stairs that need smoke control are the low blocks and newer flats near the canal and Cadishead Way, not a city-centre shaft.',
                    'iComply surveys that vent from the Offerton workshop in SK2. The note names the actuator, the panel and the fire-alarm contact. The visit is carried out by our qualified engineers. The quote is price on application after the stair is seen.',
                ],
                'barriers' => [
                    'Vehicle barriers in Cadishead are on the yards off Cadishead Way and the private lanes that serve canalside industry in M44. They are not on the residential terraces of Liverpool Road, and the Ship Canal path is not a lane iComply controls.',
                    'CAME is the barrier partner for a new boom. An existing cabinet is kept when the survey shows the springs and safety edges are still the right kit. Our qualified engineers record the loop and the manual release before parts are ordered. The price is on application.',
                ],
            ],
            [
                'aov' => ['salford', 'eccles', 'urmston', 'stretford', 'manchester'],
                'barriers' => ['salford', 'eccles', 'urmston', 'stretford', 'manchester'],
            ]
        ),
        'chorlton' => icomplyGmTown(
            'Chorlton',
            15000,
            'Manchester',
            53.4410,
            -2.2770,
            ['aov', 'barriers'],
            'M21',
            [
                'aov' => 'AOV smoke control in Chorlton | iComply',
                'barriers' => 'CAME barriers in Chorlton | iComply',
            ],
            [
                'aov' => 'Chorlton M21 smoke vents are on mansion blocks off Barlow Moor Road and Wilbraham Road. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Chorlton M21 barriers are private courts and car parks off Barlow Moor Road, not the high street. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Chorlton-cum-Hardy is the M21 district between Chorlton Green, Beech Road and the Barlow Moor Road shops. The stairs that need an automatic opening vent are the mansion blocks and the newer apartments set behind Wilbraham Road. The two-storey semis on the side streets are a different job.',
                    'Those stairs often have a chain actuator on a landing window, or a roof vent that has not been cycled since the block was refurbished. iComply identifies the panel before a takeover is offered. Our qualified engineers test what the fire alarm actually opens. The quote is price on application.',
                ],
                'barriers' => [
                    'Chorlton does not have a ring of industrial gates. The lanes that come up are supermarket and office car parks off Barlow Moor Road, and the occasional private court behind a mansion block near Chorlton Green. A short boom in a tight M21 court is a different survey from a retail-park lane.',
                    'CAME is the barrier partner when the lane is being replaced. The incumbent manufacturer stays on the quote when the cabinet, springs and edges are sound. iComply will not bridge a safety loop to force the boom down between the shops.',
                ],
            ],
            [
                'aov' => ['manchester', 'sale', 'stretford', 'stockport', 'withington'],
                'barriers' => ['manchester', 'sale', 'stretford', 'stockport', 'withington'],
            ]
        ),
        'pendlebury' => icomplyGmTown(
            'Pendlebury',
            13000,
            'Salford',
            53.5070,
            -2.3230,
            ['aov', 'barriers'],
            'M27',
            [
                'aov' => 'AOV smoke control in Pendlebury | iComply',
                'barriers' => 'CAME barriers in Pendlebury | iComply',
            ],
            [
                'aov' => 'Pendlebury M27 smoke vents are on stairs toward Bolton Road and the Agecroft flats. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Pendlebury M27 barriers are yards off Bolton Road, not the Swinton civic centre. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Pendlebury is the M27 industrial edge between Swinton and the Agecroft side of the Irwell. Older blocks sit toward Bolton Road. Newer apartments stand on the former colliery land. The smoke vent is often a stair window on a three-storey core, or a lobby damper added when the flats replaced the works.',
                    'iComply writes down the panel make and whether the fire alarm still has a contact for that vent. Our qualified engineers do not treat a green LED as a test. The quote stays price on application until the stair and the interface are known.',
                ],
                'barriers' => [
                    'Barrier work in Pendlebury is the yards and staff car parks off Bolton Road and the industrial edge of M27, not the civic centre in Swinton. A lane here is usually one boom, a loop in the tarmac and a reader the site already owns.',
                    'CAME is the barrier partner for a new boom. Paxton or Videx release is specified as access control, separate from the barrier manufacturer. iComply does not assume a hospital or council contract because Salford Royal and the civic offices are a short drive away.',
                ],
            ],
            [
                'aov' => ['swinton', 'eccles', 'prestwich', 'salford'],
                'barriers' => ['swinton', 'eccles', 'prestwich', 'salford'],
            ]
        ),
        'shaw' => icomplyGmTown(
            'Shaw',
            11000,
            'Oldham',
            53.5780,
            -2.0920,
            ['aov', 'barriers'],
            'OL2',
            [
                'aov' => 'AOV smoke control in Shaw | iComply',
                'barriers' => 'CAME barriers in Shaw | iComply',
            ],
            [
                'aov' => 'Shaw OL2 smoke vents are on converted mill stairs around Market Street and Beal Lane. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Shaw OL2 barriers are on estates off the A663, behind Market Street. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Shaw, in the OL2 west of Shaw and Crompton, is mill-town terraces around Market Street and Beal Lane, with converted mill floors above the older commercial streets. A smoke vent here is commonly a chain actuator on a converted stair, or a roof hatch that Pennine rain has seized.',
                    'iComply asks for the fire strategy and a photo of the panel before the visit from Stockport SK2. Our qualified engineers free a dragging casement before fitting a new chain. The figure is price on application, not a package copied from a larger town.',
                ],
                'barriers' => [
                    'Shaw vehicle barriers sit on the small industrial estates and car parks off the A663 and the side streets behind Market Street, not on Beal Lane itself. The cabinets are often an older brand with a tired loop.',
                    'CAME is the barrier partner when the lane is replaced. The survey records boom length and the manual release before anyone orders a pack. iComply services the incumbent manufacturer when the hardware is still sound, and does not name a second partner for OL2.',
                ],
            ],
            [
                'aov' => ['oldham', 'rochdale', 'littleborough', 'ashton-under-lyne'],
                'barriers' => ['oldham', 'rochdale', 'littleborough', 'ashton-under-lyne'],
            ]
        ),
        'tameside' => icomplyGmTown(
            'Tameside',
            231071,
            'Greater Manchester',
            53.4890,
            -2.0990,
            ['aov', 'barriers'],
            'OL6',
            [
                'aov' => 'AOV smoke control in Tameside | iComply',
                'barriers' => 'CAME barriers in Tameside | iComply',
            ],
            [
                'aov' => 'Tameside smoke control covers stairs in Ashton OL6, Hyde SK14, Denton M34 and the other borough towns. iComply quotes each stair from SK2. 07517806082.',
                'barriers' => 'Tameside barriers are lanes in Ashton, Hyde, Denton or Dukinfield, quoted per site. CAME partner. Arranged from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Tameside is the metropolitan borough east of Manchester, not a single town. Ashton-under-Lyne (OL6–OL7), Hyde (SK14), Stalybridge (SK15), Dukinfield (SK16), Denton (M34), Droylsden (M43) and Mossley (OL5) already have their own pages where the gazetteer lists them. This page is the borough brief for an agent who holds stairs in more than one of those towns.',
                    'Smoke control on a Tameside block is still quoted per stair: the panel, the actuator and the fire-alarm contact. iComply arranges the visit from Offerton, SK2. Our qualified engineers do not write one price for the whole borough.',
                ],
                'barriers' => [
                    'A Tameside barrier enquiry usually names a car park in Ashton (OL6), a yard in Denton (M34) or Hyde (SK14), or a lane on an estate in Dukinfield (SK16) or Droylsden (M43). Mossley (OL5) and Stalybridge (SK15) are tighter streets. A boom there has to suit the width that is actually there.',
                    'CAME is the barrier partner for a new lane anywhere in the borough. Each town that already has a barrier page stays the right URL for that place. This borough page is for an instruction that covers more than one Tameside site. iComply prices each lane on application after the survey.',
                ],
            ],
            [
                'aov' => ['ashton-under-lyne', 'hyde', 'denton', 'dukinfield', 'stalybridge', 'droylsden'],
                'barriers' => ['ashton-under-lyne', 'hyde', 'denton', 'dukinfield', 'stalybridge', 'droylsden'],
            ]
        ),
        'trafford' => icomplyGmTown(
            'Trafford',
            235052,
            'Greater Manchester',
            53.4460,
            -2.3190,
            ['aov', 'barriers'],
            'M32',
            [
                'aov' => 'AOV smoke control in Trafford | iComply',
                'barriers' => 'CAME barriers in Trafford | iComply',
            ],
            [
                'aov' => 'Trafford smoke vents are on blocks in Stretford M32, Old Trafford M16 and the towns with their own pages. iComply quotes from SK2. 07517806082.',
                'barriers' => 'Trafford barriers are private lanes in M17, M16, M33 or M32. CAME partner. The Trafford Centre is attended only if that estate instructs. 07517806082.',
            ],
            [
                'aov' => [
                    'Trafford is the metropolitan borough on the south-west side of Manchester. Altrincham (WA14–WA15), Sale (M33), Stretford (M32) and Urmston (M41) have their own town pages. Old Trafford (M16) and the private estates around Trafford Park (M17) are the other places agents name on this borough page.',
                    'Stair vents here are on residential blocks in Stretford and Old Trafford, and on mixed-use stairs above shops. iComply does not claim a Trafford Centre contract. Our qualified engineers test the vent that is on the building in front of them. The quote is price on application.',
                ],
                'barriers' => [
                    'Vehicle barriers in Trafford are private car parks and yards: a staff lane in Trafford Park (M17), a residential court in Old Trafford (M16), or a retail car park in Sale (M33) or Stretford (M32). The Trafford Centre is a private estate. iComply attends only if that estate instructs the lane.',
                    'CAME is the barrier partner for a new boom. Altrincham, Sale, Stretford and Urmston keep their own barrier pages for a single-town instruction. This page is the borough sheet when the sites sit in more than one of those towns.',
                ],
            ],
            [
                'aov' => ['altrincham', 'sale', 'stretford', 'urmston', 'manchester'],
                'barriers' => ['altrincham', 'sale', 'stretford', 'urmston', 'manchester'],
            ]
        ),
        'withington' => icomplyGmTown(
            'Withington',
            14000,
            'Manchester',
            53.4330,
            -2.2290,
            ['aov', 'barriers'],
            'M20',
            [
                'aov' => 'AOV smoke control in Withington | iComply',
                'barriers' => 'CAME barriers in Withington | iComply',
            ],
            [
                'aov' => 'Withington M20 smoke vents are on private stairs and small blocks off Wilmslow Road. Hospital sites are not assumed. iComply quotes from SK2. 07517806082.',
                'barriers' => 'Withington M20 barriers are private car parks behind Wilmslow Road, not hospital grounds. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Withington is the M20 district along Wilmslow Road, between Withington Village and the Burton Road side streets. The buildings that need an automatic opening vent are converted houses in multiple occupation, small mansion blocks and the newer flats set off the main road.',
                    'Christie Hospital and the health buildings nearby are NHS sites. iComply does not assume a hospital contract from the postcode. On a private stair in M20 our qualified engineers identify the actuator and the fire-alarm interface, then test them. The price is on application.',
                ],
                'barriers' => [
                    'Withington has few industrial gates. The barriers that exist are private car parks behind the Wilmslow Road shops and the occasional court entrance on a flat block in M20. A short lane between terraces is surveyed for boom length and whether pedestrians share the opening.',
                    'CAME is the barrier partner if the lane is replaced. An existing cabinet is kept when it is sound. iComply will not specify a boom for the hospital grounds unless that site instructs the work.',
                ],
            ],
            [
                'aov' => ['manchester', 'stockport', 'sale', 'chorlton'],
                'barriers' => ['manchester', 'stockport', 'sale', 'chorlton'],
            ]
        ),
        'worsley' => icomplyGmTown(
            'Worsley',
            11950,
            'Salford',
            53.5000,
            -2.3840,
            ['aov'],
            'M28',
            [
                'aov' => 'AOV smoke control in Worsley | iComply',
            ],
            [
                'aov' => 'Worsley M28 smoke vents are on apartment stairs by the Bridgewater Canal, not on the village green. iComply surveys from SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Worsley is the M28 edge of Salford, around Worsley Green and the Bridgewater Canal, with newer housing toward Boothstown. The village green itself is not a stair core. Smoke vents turn up on the apartment blocks beside the canal and on the newer flats set back from the conservation streets.',
                    'Those cores are low. The vent is often a roof hatch or a stair window rather than a mechanical shaft. iComply surveys from Stockport SK2 and names the panel before a takeover is offered. Our qualified engineers record whether the vent failed open or failed shut. The quote is price on application.',
                ],
            ],
            [
                'aov' => ['swinton', 'eccles', 'salford', 'bolton', 'manchester'],
            ]
        ),
        'wythenshawe' => icomplyGmTown(
            'Wythenshawe',
            97660,
            'Manchester',
            53.3920,
            -2.2640,
            ['aov'],
            'M22',
            [
                'aov' => 'AOV smoke control in Wythenshawe | iComply',
            ],
            [
                'aov' => 'Wythenshawe M22 and M23 smoke vents are on stair cores in Baguley, Benchill and the Civic Centre blocks. iComply quotes each core from SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Wythenshawe is the large M22–M23 estate south of Manchester, taking in Baguley, Benchill, the Civic Centre and the Forum, with Northenden on the Mersey side. Census 2021 counted 97,660 usual residents, and a large share of homes are flats or social-rented houses. Stair vents matter on the taller blocks, where one actuator at the head of the stair is the smoke path.',
                    'Wythenshawe Hospital and Manchester Airport sit next to this district. iComply does not claim either contract. On a housing block our qualified engineers test the vent, the battery and the fire-alarm contact. The quote is for that core, on application, not the whole estate in one line.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'sale', 'altrincham'],
            ]
        ),
        'milnrow' => icomplyGmTown(
            'Milnrow',
            10370,
            'Rochdale',
            53.6100,
            -2.1110,
            ['barriers'],
            'OL16',
            [
                'barriers' => 'CAME barriers in Milnrow | iComply',
            ],
            [
                'barriers' => 'Milnrow OL16 barriers are on mill yards and private car parks between Rochdale and Newhey. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'barriers' => [
                    'Milnrow is the OL16 settlement east of Rochdale, between the town and Newhey, with stone terraces, the canal and small mill buildings. Vehicle barriers here are on the industrial units and the private car parks behind those mills, not on the canal towpath.',
                    'A lane is usually a single boom with a loop that has been planed or left full of water. CAME is the barrier partner for a replacement. The manufacturer already on the cabinet is serviced when the survey says it should stay. iComply quotes that lane on application from the Stockport SK2 workshop.',
                ],
            ],
            [
                'barriers' => ['rochdale', 'littleborough', 'oldham', 'shaw'],
            ]
        ),
    ];
    return $rows;
}

/**
 * @param list<string> $families
 * @param array<string,string> $titles
 * @param array<string,string> $metas
 * @param array<string,list<string>> $copy
 * @param array<string,list<string>> $neighbours
 * @return array<string,mixed>
 */
function icomplyGmTown(
    string $name,
    int $population,
    string $county,
    float $lat,
    float $lng,
    array $families,
    string $outward,
    array $titles,
    array $metas,
    array $copy,
    array $neighbours
): array {
    $slug = function_exists('areaSlug') ? areaSlug($name) : strtolower($name);
    return [
        'name' => $name,
        'slug' => $slug,
        'population' => $population,
        'nation' => 'England',
        'county' => $county,
        'region' => 'North West',
        'lat' => $lat,
        'lng' => $lng,
        'gm_local' => true,
        'families' => $families,
        'outward' => $outward,
        'area_hub' => '/pages/areas/' . $slug,
        'titles' => $titles,
        'metas' => $metas,
        'copy' => $copy,
        'neighbours' => $neighbours,
    ];
}

function icomplyGmServiceTown(string $slug): ?array
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : strtolower($slug);
    $rows = icomplyGmServiceTownRecords();
    return $rows[$slug] ?? null;
}

function icomplyGmServiceTownServes(string $family, string $slug): bool
{
    $town = icomplyGmServiceTown($slug);
    if ($town === null) {
        return false;
    }
    return in_array($family, $town['families'], true);
}

/** @return list<string> */
function icomplyGmServiceTownRoutes(): array
{
    $routes = [];
    foreach (icomplyGmServiceTownRecords() as $town) {
        foreach ($town['families'] as $family) {
            $routes[] = '/pages/' . $family . '/' . $town['slug'];
        }
    }
    return $routes;
}
