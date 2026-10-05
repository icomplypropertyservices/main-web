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
        'brinnington' => icomplyGmTown(
            'Brinnington',
            9000,
            'Stockport',
            53.4320,
            -2.1480,
            ['aov', 'barriers'],
            'SK5',
            [
                'aov' => 'AOV smoke control in Brinnington | iComply',
                'barriers' => 'CAME barriers in Brinnington | iComply',
            ],
            [
                'aov' => 'Brinnington SK5 smoke vents are on low blocks off Brinnington Road, not a city-centre shaft. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Brinnington SK5 barriers are on yards at the edge of the estate, not the residential streets. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Brinnington is the SK5 estate north of Stockport, between Reddish and the Tame. Most homes are houses and low flats off Brinnington Road. The stairs that need smoke control are those low blocks, not a tower core.',
                    'iComply surveys that vent from the Offerton workshop in SK2, a short run from the estate. Our qualified engineers name the actuator, the panel and the fire-alarm contact. The quote is price on application after the stair is seen.',
                ],
                'barriers' => [
                    'Vehicle barriers in Brinnington are on the commercial yards at the edge of the SK5 estate, not on the residential streets off Brinnington Road. A lane here is usually one boom and a loop.',
                    'CAME is the barrier partner for a new boom. An existing cabinet is kept when the survey shows the springs and safety edges are still the right kit. The price is on application from Stockport SK2.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'hyde', 'sale'],
                'barriers' => ['stockport', 'manchester', 'hyde', 'sale'],
            ],
            '/pages/areas/stockport',
            'Stockport'
        ),
        'castleton' => icomplyGmTown(
            'Castleton',
            10000,
            'Rochdale',
            53.5900,
            -2.1750,
            ['aov', 'barriers'],
            'OL11',
            [
                'aov' => 'AOV smoke control in Castleton | iComply',
                'barriers' => 'CAME barriers in Castleton | iComply',
            ],
            [
                'aov' => 'Castleton OL11 smoke vents are on converted stairs between Rochdale and Heywood. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Castleton OL11 barriers are mill yards and private car parks, not the canal towpath. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Castleton is the OL11 stretch between Rochdale and Heywood, with terraces, the canal and older mill floors. A smoke vent here is commonly a chain actuator on a converted stair, or a roof hatch that rain has seized.',
                    'iComply asks for the panel make before the visit from Stockport SK2. Our qualified engineers free a dragging casement before fitting a new chain. The figure is price on application.',
                ],
                'barriers' => [
                    'Barrier work in Castleton is the yards and staff car parks off the mill streets in OL11, not the canal path. The cabinets are often an older brand with a tired loop.',
                    'CAME is the barrier partner when the lane is replaced. The manufacturer already on the cabinet is serviced when the survey says it should stay. iComply quotes that lane on application.',
                ],
            ],
            [
                'aov' => ['rochdale', 'oldham', 'manchester', 'stockport'],
                'barriers' => ['rochdale', 'oldham', 'manchester', 'stockport'],
            ],
            '/pages/areas/rochdale',
            'Rochdale'
        ),
        'davyhulme' => icomplyGmTown(
            'Davyhulme',
            19000,
            'Trafford',
            53.4520,
            -2.3680,
            ['aov', 'barriers'],
            'M41',
            [
                'aov' => 'AOV smoke control in Davyhulme | iComply',
                'barriers' => 'CAME barriers in Davyhulme | iComply',
            ],
            [
                'aov' => 'Davyhulme M41 smoke vents are on private stairs near Urmston. Hospital sites are not assumed. iComply quotes from SK2. 07517806082.',
                'barriers' => 'Davyhulme M41 barriers are private car parks, not hospital grounds. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Davyhulme is the M41 district beside Urmston. Trafford General sits in this postcode. iComply does not assume a hospital contract. The stairs we are asked about are private blocks and converted buildings off the residential streets.',
                    'On a private stair in M41 our qualified engineers identify the actuator and the fire-alarm interface, then test them. The quote is price on application from the Stockport SK2 workshop.',
                ],
                'barriers' => [
                    'Davyhulme barriers are private car parks and yards in M41, not the hospital grounds and not the residential cul-de-sacs. A short lane is surveyed for boom length and whether pedestrians share the opening.',
                    'CAME is the barrier partner if the lane is replaced. An existing cabinet is kept when it is sound. iComply will not specify a boom for a hospital site unless that site instructs the work.',
                ],
            ],
            [
                'aov' => ['urmston', 'stretford', 'sale', 'manchester'],
                'barriers' => ['urmston', 'stretford', 'sale', 'manchester'],
            ],
            '/pages/areas/urmston',
            'Urmston'
        ),
        'dialstone' => icomplyGmTown(
            'Dialstone',
            5500,
            'Stockport',
            53.4020,
            -2.1450,
            ['aov', 'barriers'],
            'SK2',
            [
                'aov' => 'AOV smoke control in Dialstone | iComply',
                'barriers' => 'CAME barriers in Dialstone | iComply',
            ],
            [
                'aov' => 'Dialstone SK2 smoke vents are on low blocks off Dialstone Lane, next to the Offerton workshop. Price on application. 07517806082.',
                'barriers' => 'Dialstone SK2 barriers are private courts off Dialstone Lane, not the lane itself. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Dialstone is the SK2 neighbourhood along Dialstone Lane, on the Offerton side of Stockport. The workshop is in the same outward code. Homes are mostly houses. Smoke vents turn up on the occasional low block set off the lane.',
                    'Because the stair is close to SK2, iComply can survey it without treating the visit as a long trip. Our qualified engineers still name the panel and test the fire-alarm contact. The quote is price on application.',
                ],
                'barriers' => [
                    'Dialstone does not have an industrial ring road of gates. The barriers that exist are private courts and small car parks off Dialstone Lane in SK2. A short boom in a tight court is a different survey from a retail-park lane.',
                    'CAME is the barrier partner when the lane is replaced. iComply records the loop and the manual release before parts are ordered. The price is on application.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'hyde', 'sale'],
                'barriers' => ['stockport', 'manchester', 'hyde', 'sale'],
            ],
            '/pages/areas/stockport',
            'Stockport'
        ),
        'edgeley' => icomplyGmTown(
            'Edgeley',
            14000,
            'Stockport',
            53.4000,
            -2.1700,
            ['aov', 'barriers'],
            'SK3',
            [
                'aov' => 'AOV smoke control in Edgeley | iComply',
                'barriers' => 'CAME barriers in Edgeley | iComply',
            ],
            [
                'aov' => 'Edgeley SK3 smoke vents are on stairs off Castle Street and near the station. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Edgeley SK3 barriers are yards and private car parks, not Castle Street. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Edgeley is the SK3 district south-west of Stockport station, around Castle Street and the terraces toward Edgeley Park. The stairs that need an automatic opening vent are the flats and converted buildings off the shopping street, not the two-storey terraces themselves.',
                    'iComply surveys from Offerton SK2. Our qualified engineers identify the panel before a takeover is offered and test what the fire alarm actually opens. The quote is price on application.',
                ],
                'barriers' => [
                    'Vehicle barriers in Edgeley are on yards and private car parks behind the SK3 streets, not on Castle Street and not at the football ground unless that site instructs the lane. A lane here is usually one boom.',
                    'CAME is the barrier partner for a new boom. The incumbent manufacturer stays on the quote when the cabinet, springs and edges are sound. iComply will not bridge a safety loop to force the boom down.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'sale', 'hyde'],
                'barriers' => ['stockport', 'manchester', 'sale', 'hyde'],
            ],
            '/pages/areas/stockport',
            'Stockport'
        ),
        'gatley' => icomplyGmTown(
            'Gatley',
            21627,
            'Stockport',
            53.3930,
            -2.2320,
            ['barriers'],
            'SK8',
            [
                'barriers' => 'CAME barriers in Gatley | iComply',
            ],
            [
                'barriers' => 'Gatley SK8 barriers are private car parks off the village, not the green. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'barriers' => [
                    'Gatley is the SK8 village south of Stockport, toward Cheadle. The green and the shopping street are not a lane iComply controls. Vehicle barriers here are private car parks and office courts set off those streets.',
                    'CAME is the barrier partner for a new boom. An existing cabinet is kept when the survey shows it should stay. iComply quotes that lane on application from the Stockport SK2 workshop. Smoke vents in Gatley stay on the separate AOV page.',
                ],
            ],
            [
                'barriers' => ['stockport', 'sale', 'altrincham', 'manchester'],
            ],
            '/pages/areas/cheadle',
            'Cheadle'
        ),
        'heaton-mersey' => icomplyGmTown(
            'Heaton Mersey',
            12000,
            'Stockport',
            53.4100,
            -2.2100,
            ['aov', 'barriers'],
            'SK4',
            [
                'aov' => 'AOV smoke control in Heaton Mersey | iComply',
                'barriers' => 'CAME barriers in Heaton Mersey | iComply',
            ],
            [
                'aov' => 'Heaton Mersey SK4 smoke vents are on apartment stairs between Didsbury and Stockport. iComply surveys from SK2. 07517806082.',
                'barriers' => 'Heaton Mersey SK4 barriers are private courts, not the park. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Heaton Mersey is the SK4 district between Stockport and Didsbury, with semis along the park and newer apartments set back from the main road. The smoke vent is on those apartment stairs, not on a house landing.',
                    'iComply writes down the panel make and whether the fire alarm still has a contact for that vent. Our qualified engineers do not treat a green LED as a test. The quote stays price on application.',
                ],
                'barriers' => [
                    'Heaton Mersey has few industrial gates. The barriers that exist are private courts and small car parks in SK4, not a boom across the park. Boom length and pedestrian use are written down before a pack is ordered.',
                    'CAME is the barrier partner when the lane is replaced. The survey records the manual release. iComply services the incumbent manufacturer when the hardware is still sound.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'sale', 'hyde'],
                'barriers' => ['stockport', 'manchester', 'sale', 'hyde'],
            ],
            '/pages/areas/stockport',
            'Stockport'
        ),
        'heaton-norris' => icomplyGmTown(
            'Heaton Norris',
            15000,
            'Stockport',
            53.4200,
            -2.1650,
            ['aov', 'barriers'],
            'SK4',
            [
                'aov' => 'AOV smoke control in Heaton Norris | iComply',
                'barriers' => 'CAME barriers in Heaton Norris | iComply',
            ],
            [
                'aov' => 'Heaton Norris SK4 smoke vents are on stairs toward the M60 and Stockport centre. iComply surveys from SK2. 07517806082.',
                'barriers' => 'Heaton Norris SK4 barriers are yards off the main road, not the terraces. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Heaton Norris is the SK4 district north of Stockport centre, toward the pyramid and the M60. Terraces make up most of the streets. Newer flats and low blocks are where an automatic opening vent is asked about.',
                    'Those cores are low. The vent is often a stair window or a roof hatch. iComply surveys from Stockport SK2 and names the panel before a takeover is offered. The quote is price on application.',
                ],
                'barriers' => [
                    'Barrier work in Heaton Norris is the yards off the main road in SK4, not a gate on the terraced streets. A lane is usually one boom, a loop in the tarmac and a reader the site already owns.',
                    'CAME is the barrier partner for a new boom. Paxton or Videx release is specified as access control, separate from the barrier manufacturer. The price is on application.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'hyde', 'sale'],
                'barriers' => ['stockport', 'manchester', 'hyde', 'sale'],
            ],
            '/pages/areas/stockport',
            'Stockport'
        ),
        'lostock' => icomplyGmTown(
            'Lostock',
            12000,
            'Bolton',
            53.5720,
            -2.5000,
            ['aov', 'barriers'],
            'BL6',
            [
                'aov' => 'AOV smoke control in Lostock | iComply',
                'barriers' => 'CAME barriers in Lostock | iComply',
            ],
            [
                'aov' => 'Lostock BL6 smoke vents are on apartment stairs near Lostock Junction, not Lostock Hall in Preston. iComply quotes from SK2. 07517806082.',
                'barriers' => 'Lostock BL6 barriers are business-park lanes near the junction, not the residential closes. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Lostock in this page is the BL6 district of Bolton around Lostock Junction, not Lostock Hall near Preston. Houses line most streets. Smoke vents are on the apartment stairs and the newer blocks set off the junction.',
                    'The drive from Stockport SK2 is longer than a Stockport estate, so the visit is batched. Our qualified engineers still test the vent, the battery and the fire-alarm contact. The quote is for that core, on application.',
                ],
                'barriers' => [
                    'Vehicle barriers in Lostock are the business-park lanes and private car parks near BL6, not a boom on a residential close. The site has to say which lane. iComply does not treat the railway junction as a gate.',
                    'CAME is the barrier partner for a new boom. The survey records boom length and the manual release before anyone orders a pack. Existing manufacturers are serviced when the hardware is sound.',
                ],
            ],
            [
                'aov' => ['bolton', 'manchester', 'stockport', 'sale'],
                'barriers' => ['bolton', 'manchester', 'stockport', 'sale'],
            ],
            '/pages/areas/bolton',
            'Bolton'
        ),
        'newhey' => icomplyGmTown(
            'Newhey',
            7000,
            'Rochdale',
            53.6020,
            -2.0900,
            ['aov', 'barriers'],
            'OL16',
            [
                'aov' => 'AOV smoke control in Newhey | iComply',
                'barriers' => 'CAME barriers in Newhey | iComply',
            ],
            [
                'aov' => 'Newhey OL16 smoke vents are on converted mill stairs east of Milnrow. iComply surveys from Stockport SK2. Price on application. 07517806082.',
                'barriers' => 'Newhey OL16 barriers are mill yards, not the village street. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Newhey is the OL16 village east of Milnrow, with stone terraces and small mill buildings. A smoke vent here is commonly a chain actuator on a converted stair. Pennine rain seizes roof hatches. A lid that will not close is both a leak and a failed smoke vent.',
                    'iComply asks for a photo of the panel before travelling from Stockport SK2. Our qualified engineers free a dragging casement before fitting a new chain. The figure is price on application.',
                ],
                'barriers' => [
                    'Newhey vehicle barriers sit on the mill yards and private car parks in OL16, not on the village street and not on the path up to the moor. Milnrow has its own barrier page for the next settlement west.',
                    'CAME is the barrier partner when the lane is replaced. A loop full of water is written down, not bridged out. iComply quotes that lane on application.',
                ],
            ],
            [
                'aov' => ['rochdale', 'oldham', 'stockport', 'manchester'],
                'barriers' => ['rochdale', 'oldham', 'stockport', 'manchester'],
            ],
            '/pages/areas/rochdale',
            'Rochdale'
        ),
        'partington' => icomplyGmTown(
            'Partington',
            8000,
            'Trafford',
            53.4200,
            -2.4300,
            ['aov', 'barriers'],
            'M31',
            [
                'aov' => 'AOV smoke control in Partington | iComply',
                'barriers' => 'CAME barriers in Partington | iComply',
            ],
            [
                'aov' => 'Partington M31 smoke vents are on low blocks in the village, not the Carrington works. iComply quotes from SK2. 07517806082.',
                'barriers' => 'Partington M31 barriers are lanes on the Carrington industrial edge, not the village street. CAME partner. Quoted from Stockport SK2. 07517806082.',
            ],
            [
                'aov' => [
                    'Partington is the M31 village west of Sale. Most homes are houses. The stairs that need smoke control are the low blocks in the village. The chemical and industrial sites at Carrington are a different instruction and are not assumed from the postcode.',
                    'iComply surveys a village stair from Stockport SK2 and names the panel before a takeover is offered. Our qualified engineers record whether the vent failed open or failed shut. The quote is price on application.',
                ],
                'barriers' => [
                    'Vehicle barriers for a Partington enquiry are usually a lane on the Carrington industrial edge in M31, not a boom on the village street. iComply attends only the lane the site instructs. A works entrance and a village court are quoted as separate lanes.',
                    'CAME is the barrier partner for a new boom. The survey records the loop, the safety edges and the manual release. The price is on application from Stockport SK2.',
                ],
            ],
            [
                'aov' => ['sale', 'altrincham', 'urmston', 'manchester'],
                'barriers' => ['sale', 'altrincham', 'urmston', 'manchester'],
            ],
            '/pages/areas/sale',
            'Sale'
        ),
        'reddish' => icomplyGmTown(
            'Reddish',
            22200,
            'Stockport',
            53.4380,
            -2.1600,
            ['aov'],
            'SK5',
            [
                'aov' => 'AOV smoke control in Reddish | iComply',
            ],
            [
                'aov' => 'Reddish SK5 smoke vents are on low blocks between Reddish North and Reddish South. iComply surveys from Stockport SK2. Price on application. 07517806082.',
            ],
            [
                'aov' => [
                    'Reddish is the SK5 district north of Stockport, between Reddish North and Reddish South. The published barrier page already uses the Census 2021 usual-resident count of 22,200. Homes are mostly semis and terraces, with flats on some streets. Smoke vents are on those low blocks, not a city shaft.',
                    'iComply surveys the stair from Offerton SK2. Our qualified engineers name the actuator and test the fire-alarm contact. Vehicle barriers in Reddish stay on the existing barrier page. The smoke-vent quote is price on application.',
                ],
            ],
            [
                'aov' => ['stockport', 'manchester', 'hyde', 'sale'],
            ],
            '/pages/areas/stockport',
            'Stockport'
        ),
    ];
    $rows = icomplyGmEnrichFireTownFamilies($rows);
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
    array $neighbours,
    string $areaHub = '',
    string $areaLabel = ''
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
        'area_hub' => $areaHub !== '' ? $areaHub : '/pages/areas/' . $slug,
        'area_label' => $areaLabel !== '' ? $areaLabel : $name,
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

/**
 * Pack B (2026-10-05): GM local towns that already ship AOV/barriers also serve
 * fire-alarms, emergency-lighting and fire-doors town pages.
 *
 * @param array<string, array<string, mixed>> $rows
 * @return array<string, array<string, mixed>>
 */
function icomplyGmEnrichFireTownFamilies(array $rows): array
{
    $families = ['fire-alarms', 'emergency-lighting', 'fire-doors'];
    $slugs = [
        'brinnington', 'castleton', 'davyhulme', 'dialstone', 'edgeley', 'gatley',
        'heaton-mersey', 'heaton-norris', 'lostock', 'newhey', 'partington', 'reddish',
    ];
    $neighbourDefault = ['manchester', 'stockport', 'sale', 'bolton'];
    foreach ($slugs as $slug) {
        if (!isset($rows[$slug])) {
            continue;
        }
        $name = (string) $rows[$slug]['name'];
        $outward = (string) ($rows[$slug]['outward'] ?? '');
        foreach ($families as $fam) {
            if (!in_array($fam, $rows[$slug]['families'], true)) {
                $rows[$slug]['families'][] = $fam;
            }
            if ($fam === 'fire-alarms') {
                $label = 'Fire alarms';
                $meta = $name . ' ' . $outward . ' fire alarm install, service and BS 5839 work from Stockport SK2. Price on application. 07517806082.';
                $copy = [
                    'Fire alarm work in ' . $name . ' covers detection, panels and cause-and-effect for the stairs and common parts that actually need it.',
                    'iComply surveys from the Offerton workshop in SK2. Our qualified engineers name the panel, the devices and the interfaces before a price is locked. Quotes stay on application after the visit.',
                ];
            } elseif ($fam === 'emergency-lighting') {
                $label = 'Emergency lighting';
                $meta = $name . ' ' . $outward . ' emergency lighting tests and upgrades from Stockport SK2. Price on application. 07517806082.';
                $copy = [
                    'Emergency lighting in ' . $name . ' is about escape routes, duration tests and fittings that still work when the mains drop.',
                    'iComply tests and upgrades from Stockport SK2. Our qualified engineers record the circuit and the duration result. The quote is price on application.',
                ];
            } else {
                $label = 'Fire doors';
                $meta = $name . ' ' . $outward . ' fire door survey, repair and certification from Stockport SK2. Price on application. 07517806082.';
                $copy = [
                    'Fire doors in ' . $name . ' fail on gaps, seals, closers and ironmongery long before the leaf looks tired.',
                    'iComply inspects from Stockport SK2. Our qualified engineers note the defects and the certification route. Pricing is on application after the survey.',
                ];
            }
            if (!isset($rows[$slug]['titles'][$fam])) {
                $rows[$slug]['titles'][$fam] = $label . ' in ' . $name . ' | iComply';
            }
            if (!isset($rows[$slug]['metas'][$fam])) {
                $rows[$slug]['metas'][$fam] = $meta;
            }
            if (!isset($rows[$slug]['copy'][$fam])) {
                $rows[$slug]['copy'][$fam] = $copy;
            }
            if (!isset($rows[$slug]['neighbours'][$fam])) {
                $rows[$slug]['neighbours'][$fam] = $neighbourDefault;
            }
        }
    }
    return $rows;
}

