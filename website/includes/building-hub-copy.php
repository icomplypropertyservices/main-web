<?php
/**
 * Copy for building trades that already exist in services.json.
 * No new service slugs. Greater Manchester towns only. Quotes POA.
 */
declare(strict_types=1);

/** @return list<string> */
function icomplyGreaterManchesterBoroughs(): array
{
    $known = array_fill_keys(getAreas(), true);
    $out = [];
    foreach ([
        'Bolton', 'Bury', 'Manchester', 'Oldham', 'Rochdale',
        'Salford', 'Stockport', 'Tameside', 'Trafford', 'Wigan',
    ] as $name) {
        if (isset($known[$name])) {
            $out[] = $name;
        }
    }
    return $out;
}

/**
 * Boroughs plus the Greater Manchester towns already published on the site.
 * Burnley stays on the wider North West list. It is not added here.
 *
 * @return list<string>
 */
function icomplyGreaterManchesterTownNames(): array
{
    $known = array_fill_keys(getAreas(), true);
    $out = icomplyGreaterManchesterBoroughs();
    $seen = array_fill_keys($out, true);
    $towns = [
        'Leigh', 'Atherton', 'Tyldesley', 'Horwich', 'Westhoughton', 'Farnworth', 'Kearsley', 'Little Lever',
        'Radcliffe', 'Whitefield', 'Prestwich', 'Swinton', 'Eccles', 'Walkden', 'Worsley', 'Pendlebury',
        'Irlam', 'Cadishead', 'Altrincham', 'Sale', 'Stretford', 'Urmston', 'Chorlton', 'Didsbury',
        'Withington', 'Wythenshawe', 'Cheadle', 'Cheadle Hulme', 'Bramhall', 'Hazel Grove', 'Marple', 'Romiley',
        'Hyde', 'Stalybridge', 'Dukinfield', 'Ashton-under-Lyne', 'Mossley', 'Droylsden', 'Denton', 'Failsworth',
        'Middleton', 'Chadderton', 'Heywood', 'Milnrow', 'Littleborough', 'Shaw', 'Royton', 'Lees',
        'Uppermill', 'Saddleworth',
    ];
    foreach ($towns as $name) {
        if (!isset($known[$name]) || isset($seen[$name])) {
            continue;
        }
        $seen[$name] = true;
        $out[] = $name;
    }
    return $out;
}

/** @return list<string> */
function icomplyBuildingWorkSlugs(): array
{
    return array_keys(icomplyBuildingHubRows());
}

/**
 * @return array<string,mixed>|null
 */
function icomplyBuildingHubCopy(string $slug): ?array
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : $slug;
    $rows = icomplyBuildingHubRows();
    if (!isset($rows[$slug])) {
        return null;
    }
    $row = $rows[$slug];
    $services = function_exists('getServices') ? getServices() : [];
    $name = trim((string)($services[$slug] ?? $row['name']));
    $intro = [
        (string)$row['lead'],
        'Each Greater Manchester borough already on this site, and the towns listed with them, has its own ' . $name . ' page. Visits are arranged from Offerton, Stockport, SK2. ' . (string)$row['local'],
        'Quotes are price on application (POA) after ' . (string)$row['scope'] . '. This page does not publish a fee, a review or an accreditation.',
        'The work is carried out by relevant qualified people. Where a visit needs a specialist ticket, iComply uses approved subcontractors.',
    ];
    if (!empty($row['gas'])) {
        $intro[] = 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.';
    }
    $faqs = [
        [
            'Which Greater Manchester places have a ' . $name . ' page?',
            'Bolton, Bury, Manchester, Oldham, Rochdale, Salford, Stockport, Tameside, Trafford and Wigan, plus the towns already published with them, such as Altrincham, Sale and Ashton-under-Lyne. Each link is this trade in that place.',
        ],
        [
            'How is ' . $name . ' priced?',
            'Quotes are price on application after ' . (string)$row['scope'] . '. There is no fee list on this page.',
        ],
        [
            'Who attends?',
            'The work is carried out by relevant qualified people. Where a visit needs a specialist ticket, iComply uses approved subcontractors.',
        ],
    ];
    if (!empty($row['faq']) && is_array($row['faq'])) {
        $faqs[] = $row['faq'];
    }

    return [
        'hero_accent' => (string)$row['accent'],
        'areas_note' => 'Greater Manchester boroughs and towns already on the site. Each link is ' . $name . ' in that place. Quotes are price on application.',
        'pillars' => $row['pillars'],
        'intro' => $intro,
        'sections' => [
            [
                'h2' => (string)$row['h2'],
                'p' => $row['paragraphs'],
            ],
        ],
        'faq' => $faqs,
        'cta_line' => (string)$row['cta'],
        'quote_placeholder' => (string)$row['placeholder'],
    ];
}

/**
 * @return list<array{0:string,1:string}>
 */
function icomplyBuildingHubLinks(string $slug): array
{
    $slug = function_exists('areaSlug') ? areaSlug($slug) : $slug;
    $rows = icomplyBuildingHubRows();
    if (!isset($rows[$slug]['links']) || !is_array($rows[$slug]['links'])) {
        return [];
    }
    return $rows[$slug]['links'];
}

/**
 * Existing slugs only. Keys must already be in services.json.
 *
 * @return array<string,array<string,mixed>>
 */
function icomplyBuildingHubRows(): array
{
    static $rows = null;
    if ($rows !== null) {
        return $rows;
    }
    $rows = [
        'kitchens' => [
            'name' => 'Kitchens',
            'accent' => 'Measured, supplied and fitted. POA.',
            'lead' => 'Kitchen supply and fit from iComply covers units, worktops, sinks and the coordination of the waste, the electrics and any gas hob. Homes, HMOs and light commercial tea points are scoped from the room, not from a showroom pack.',
            'local' => 'A kitchen in a Stockport terrace is a different measure from a Trafford flat or a Wigan void.',
            'scope' => 'the room is measured and the services in the way of the units are known',
            'gas' => true,
            'h2' => 'What the kitchen visit covers',
            'paragraphs' => [
                'We measure the walls, the window positions, the existing services and how the room is used. Units and worktops are then specified to that measure. A gas hob is isolated and reconnected only by a Gas Safe registered engineer.',
                'Tiling, flooring and decorating that follow the fit stay on their own service pages and are quoted with the kitchen when you want one programme.',
            ],
            'faq' => ['Do you publish a kitchen price?', 'No. The quote is price on application after the measure. Worktops, appliances and how far the services have to move all change the job.'],
            'cta' => 'Postcode, who uses the kitchen, and whether a gas hob is staying. POA.',
            'placeholder' => 'Postcode, room size if you know it, gas hob or electric, occupied or void…',
            'pillars' => [
                ['title' => 'Measure first', 'text' => 'Walls, windows, wastes and the consumer unit are recorded before units are ordered.'],
                ['title' => 'Services coordinated', 'text' => 'Plumbing and electrical first and second fix are planned with the carcasses, not after the worktops are on.'],
                ['title' => 'POA', 'text' => 'No unit price list. The written quote follows the measure.'],
            ],
            'links' => [
                ['/pages/services/plumbing', 'Plumbing'],
                ['/pages/services/tiling', 'Tiling'],
                ['/pages/services/electrical', 'Electrical'],
                ['/pages/gas-safety-certificate', 'Gas safety certificate'],
            ],
        ],
        'bathrooms' => [
            'name' => 'Bathrooms',
            'accent' => 'Stripped, tanked and fitted. POA.',
            'lead' => 'Bathroom and wet-room fitting covers sanitaryware, wastes, tanking, ventilation and the making good around the fittings. The quote starts from the room as it is, including whether the floor can take a former.',
            'local' => 'A Salford flat and a Bury house do not share a bathroom layout just because both are in Greater Manchester.',
            'scope' => 'the room, the soil pipe and the ventilation route are known',
            'gas' => true,
            'h2' => 'Wet rooms and ordinary bathrooms',
            'paragraphs' => [
                'Tanking and the fall to the waste are agreed before trays or formers are fixed. Ventilation is part of the job when the existing fan is missing or dumping into the loft.',
                'If a gas appliance sits in the same room, that appliance is not worked on by iComply. Wall and floor tiling can be included in the same programme or left on the tiling page.',
            ],
            'faq' => ['Is a wet room the same quote as a bath swap?', 'No. A former, the tanking and the floor build-up are a different scope from replacing a suite on the existing wastes. Both are price on application.'],
            'cta' => 'Postcode, bath or wet room, and which floor the room is on. POA.',
            'placeholder' => 'Postcode, bath or wet room, storey, occupied or void…',
            'pillars' => [
                ['title' => 'Wastes and soil', 'text' => 'The existing stack and the run of the waste decide what can move.'],
                ['title' => 'Tanking', 'text' => 'Wet areas are tanked to the system being used. A coat of adhesive is not described as a tanking system.'],
                ['title' => 'POA', 'text' => 'Suite prices are not listed. The quote follows the strip-out and the services.'],
            ],
            'links' => [
                ['/pages/services/plumbing', 'Plumbing'],
                ['/pages/services/tiling', 'Tiling'],
                ['/pages/services/electrical', 'Electrical'],
                ['/pages/services/kitchens', 'Kitchens'],
            ],
        ],
        'plastering' => [
            'name' => 'Plastering',
            'accent' => 'Skim, patch and board finish. POA.',
            'lead' => 'Plastering covers skim coats, patch repairs, board finish and ceilings. It is a finish trade. It does not include a structural opinion on a crack, and it does not include dry lining unless that is asked for as well.',
            'local' => 'Ceiling plaster in an Oldham terrace is quoted from the room, including how much has already blown.',
            'scope' => 'the rooms and the ceiling area are seen',
            'gas' => false,
            'h2' => 'Skim, patches and ceilings',
            'paragraphs' => [
                'We record which walls are being skimmed, which are patch repairs, and whether the ceilings are in the same visit. A ceiling that has lost its key is a reboard conversation, which sits with dry lining if the laths will not take a skim.',
                'The existing ceiling plastering guide is still the keyword page for that job. This service page is the trade, with a town page for each Greater Manchester place.',
            ],
            'faq' => ['Do you skim over blown ceilings?', 'Only when the background will take it. If the ceiling has failed, the quote says so and the boarded-ceiling option is priced separately.'],
            'cta' => 'Rooms, ceilings or both, and whether the property is occupied. POA.',
            'placeholder' => 'Postcode, rooms, ceilings, occupied or void…',
            'pillars' => [
                ['title' => 'Finish, not structure', 'text' => 'Skim and patch work follow the agreed rooms. A crack that needs a structural engineer is said to be one.'],
                ['title' => 'Ceilings included when scoped', 'text' => 'Ceiling plaster is part of this trade when the visit names the ceilings.'],
                ['title' => 'POA', 'text' => 'No price per square metre on this page.'],
            ],
            'links' => [
                ['/pages/keywords/ceiling-plastering', 'Ceiling plastering'],
                ['/pages/services/dry-lining', 'Dry lining'],
                ['/pages/services/painting-decorating', 'Painting and decorating'],
            ],
        ],
        'painting-decorating' => [
            'name' => 'Painting and decorating',
            'accent' => 'Prepared, then coated. POA.',
            'lead' => 'Painting and decorating covers preparation and coatings inside voids and occupied homes, and exterior coatings when access is included in the quote. It is not a fire-coating certificate and it is not a damp treatment.',
            'local' => 'A void in Rochdale that needs every room is a different specification from a hallway and one bedroom in Sale.',
            'scope' => 'the rooms or the elevation, and the preparation, are listed',
            'gas' => false,
            'h2' => 'Voids and occupied rooms',
            'paragraphs' => [
                'Preparation is part of the scope: filling, making good after other trades, and what happens to existing paper. Low-odour coatings are used when the brief asks for them. We do not describe a mist coat as a finished system.',
                'Exterior work includes the access needed to reach the elevation. That access is in the POA quote rather than assumed.',
            ],
            'faq' => ['Will you paint over damp?', 'No. If the wall is wet, decorating waits until the cause is dealt with. Damp diagnosis is a separate service.'],
            'cta' => 'Rooms or elevation, occupied or void, and any colour schedule. POA.',
            'placeholder' => 'Postcode, rooms or exterior, occupied or void…',
            'pillars' => [
                ['title' => 'Preparation named', 'text' => 'The quote says what is filled, washed or made good before any finish coat.'],
                ['title' => 'Voids and lived-in homes', 'text' => 'Furniture, floor protection and how long a room can be empty change the programme.'],
                ['title' => 'POA', 'text' => 'No room-rate card on this page.'],
            ],
            'links' => [
                ['/pages/services/plastering', 'Plastering'],
                ['/pages/services/damp-proofing', 'Damp'],
                ['/pages/keywords/ceiling-painting', 'Ceiling painting'],
            ],
        ],
        'tiling' => [
            'name' => 'Tiling',
            'accent' => 'Walls, floors and tanking. POA.',
            'lead' => 'Tiling covers wall and floor tiles in kitchens, bathrooms and other wet areas, including the tanking underneath when the substrate needs it. Adhesive and grout follow the tile and the background.',
            'local' => 'A small Oldham cloakroom and a Trafford wet room are both quoted from the metreage and the preparation, not from a pack price.',
            'scope' => 'the areas, the tile and the background are known',
            'gas' => false,
            'h2' => 'Background before tile',
            'paragraphs' => [
                'We look at whether the wall is plasterboard, tile backer or an existing tile bed, and whether the floor is timber or solid. Tanking is specified where water will sit. A tile on an untanked wet wall is not offered as a waterproof finish.',
                'Kitchen splashbacks and bathroom walls can be programmed with the fitting trades. They are still priced as tiling.',
            ],
            'faq' => ['Do you supply the tiles?', 'Supply can be included or you can provide tiles. Either way the quote states who is buying them and what happens if the delivery is short.'],
            'cta' => 'Rooms, approximate area, and who is supplying the tiles. POA.',
            'placeholder' => 'Postcode, walls or floors, wet room or kitchen, tile supply…',
            'pillars' => [
                ['title' => 'Wet areas tanked', 'text' => 'Showers and wet rooms get the tanking system the substrate needs.'],
                ['title' => 'Floors checked', 'text' => 'Movement and flatness are looked at before a rigid tile goes down.'],
                ['title' => 'POA', 'text' => 'No price per square metre is published here.'],
            ],
            'links' => [
                ['/pages/services/bathrooms', 'Bathrooms'],
                ['/pages/services/kitchens', 'Kitchens'],
                ['/pages/services/flooring', 'Flooring'],
            ],
        ],
        'flooring' => [
            'name' => 'Flooring',
            'accent' => 'Subfloor, then the finish. POA.',
            'lead' => 'Flooring supply and fit covers laminate, vinyl and engineered wood, including the preparation of the subfloor. It is not a structural floor design and it is not a screed specification unless that is written into the quote.',
            'local' => 'A Wigan void with several rooms is measured room by room. Door bars and thresholds are part of that measure.',
            'scope' => 'the rooms, the subfloor and the product are known',
            'gas' => false,
            'h2' => 'What has to be flat first',
            'paragraphs' => [
                'We check how level the floor is, whether it is damp, and what is happening at the doors. A finish laid over a hollow or a wet slab is not described as a completed floor.',
                'Skirtings that have to come off and go back are carpentry. Say if you want that in the same visit.',
            ],
            'faq' => ['Can you lay onto existing tiles or laminate?', 'Sometimes, when the existing floor is sound and flat enough for the new product. If it is not, the quote includes the uplift.'],
            'cta' => 'Rooms, product if you have chosen one, and the subfloor if you know it. POA.',
            'placeholder' => 'Postcode, rooms, laminate vinyl or wood, subfloor…',
            'pillars' => [
                ['title' => 'Subfloor first', 'text' => 'Flatness and moisture are checked before a finish is fixed.'],
                ['title' => 'Product named', 'text' => 'The quote says whether iComply supplies the floor or fits materials you provide.'],
                ['title' => 'POA', 'text' => 'No catalogue rate per square metre.'],
            ],
            'links' => [
                ['/pages/services/carpentry', 'Carpentry'],
                ['/pages/services/tiling', 'Tiling'],
                ['/pages/keywords/skirting-boards-fitting', 'Skirting boards'],
            ],
        ],
        'joinery' => [
            'name' => 'Joinery',
            'accent' => 'Made to the opening. POA.',
            'lead' => 'Joinery covers doors, frames, linings, cupboards, boxing and built-in storage made or fitted to the opening on site. It is the fitting trade for those items. Fire door inspections stay on the fire doors service.',
            'local' => 'A door and frame in a Manchester flat is measured at the opening. A standard leaf size is not assumed.',
            'scope' => 'the openings or the cupboard locations are measured',
            'gas' => false,
            'h2' => 'Doors, frames and built-ins',
            'paragraphs' => [
                'We measure the opening, the floor finish that is coming, and the ironmongery. Boxing around pipes is included when it is drawn or pointed out. Skirting and architrave that belong to the same opening can be in the same quote.',
                'Windows and external door sets are the windows and doors service. Internal doors and frames are this one.',
            ],
            'faq' => ['Do you make fire doors?', 'Fire door sets are specified on the fire doors service. This page is joinery: doors, frames, cupboards and boxing measured to the job.'],
            'cta' => 'How many openings, and whether the doors are internal. POA.',
            'placeholder' => 'Postcode, number of doors or cupboards, internal or external…',
            'pillars' => [
                ['title' => 'Measured openings', 'text' => 'Leaves and frames are specified from the opening, including the floor build-up.'],
                ['title' => 'Boxing and storage', 'text' => 'Pipe boxing and built-in cupboards are quoted when the locations are known.'],
                ['title' => 'POA', 'text' => 'No door-set price list.'],
            ],
            'links' => [
                ['/pages/services/carpentry', 'Carpentry'],
                ['/pages/services/windows-doors', 'Windows and doors'],
                ['/pages/services/fire-doors', 'Fire doors'],
            ],
        ],
        'carpentry' => [
            'name' => 'Carpentry',
            'accent' => 'First fix, second fix, studs. POA.',
            'lead' => 'Carpentry covers first and second fix: timber stud walls, noggins, skirting, architrave, door casings and the site carpentry around other trades. Metal-stud partitions are dry lining. This page is the timber and second-fix work.',
            'local' => 'Stud walls in a Bolton flat and skirting in a Bramhall house are both site carpentry, quoted from the drawings or the void.',
            'scope' => 'the drawings or the rooms are seen',
            'gas' => false,
            'h2' => 'Stud walls and skirting',
            'paragraphs' => [
                'A stud wall quote says where it runs, whether it is timber, and what it has to carry. Skirting and architrave are measured to the rooms after the floor finish is known, because the height changes.',
                'The existing guides for stud wall construction and skirting boards are still on the site. Those keyword pages link the same Greater Manchester towns.',
            ],
            'faq' => ['Are stud walls and skirting the same visit?', 'They can be, when both are in the scope. First fix usually happens before plaster. Second fix waits for the floors and the decoration programme.'],
            'cta' => 'Studs, skirting, or both, plus floor finish if it is already chosen. POA.',
            'placeholder' => 'Postcode, stud walls or skirting, drawings if you have them…',
            'pillars' => [
                ['title' => 'First fix', 'text' => 'Studs, noggins and grounds go in before the finish trades.'],
                ['title' => 'Second fix', 'text' => 'Skirting, architrave and hanging internal doors follow the floors.'],
                ['title' => 'POA', 'text' => 'No linear-metre rate is published.'],
            ],
            'links' => [
                ['/pages/keywords/stud-wall-construction', 'Stud wall construction'],
                ['/pages/keywords/skirting-boards-fitting', 'Skirting boards'],
                ['/pages/services/dry-lining', 'Dry lining'],
                ['/pages/services/joinery', 'Joinery'],
            ],
        ],
        'brickwork' => [
            'name' => 'Brickwork',
            'accent' => 'Repairs, pointing, small builds. POA.',
            'lead' => 'Brickwork covers repointing, local repairs, small masonry builds and openings when a lintel specification is already known. It is not a structural engineer’s report. A crack that needs that report is identified as such.',
            'local' => 'A gable in Rochdale and a garden wall in Altrincham are inspected on site before anyone prices the mortar or the access.',
            'scope' => 'the elevation or the wall is inspected',
            'gas' => false,
            'h2' => 'Pointing and local repairs',
            'paragraphs' => [
                'We look at the mortar, the brick face and whether water is getting in. Repointing is quoted to the area that needs it, not to a whole elevation by default. New openings need a lintel that is specified. We do not invent a beam size on the page.',
                'Masonry crack repair remains its own keyword guide, linked to the same towns.',
            ],
            'faq' => ['Will you diagnose every crack?', 'We will say what the brickwork visit can repair. Movement that needs a structural engineer is not written up as a repointing job.'],
            'cta' => 'Which wall, and whether it is pointing, a repair or a small build. POA.',
            'placeholder' => 'Postcode, elevation, pointing or rebuild, access…',
            'pillars' => [
                ['title' => 'Seen first', 'text' => 'Mortar, brick and access are checked before a quantity is agreed.'],
                ['title' => 'Small builds', 'text' => 'Boundary and garden walls are quoted when the line and the height are known.'],
                ['title' => 'POA', 'text' => 'No rate per square metre of pointing.'],
            ],
            'links' => [
                ['/pages/keywords/masonry-crack-repair', 'Masonry crack repair'],
                ['/pages/services/rendering', 'Rendering'],
                ['/pages/services/roofing', 'Roofing'],
            ],
        ],
        'roofing' => [
            'name' => 'Roofing',
            'accent' => 'Inspected, then quoted. POA.',
            'lead' => 'Roofing covers repairs, re-covering of small roofs, flat-roof repairs, gutters and chimney flashings on houses. The quote follows a look at the roof. Scaffold or other access is included in that quote when the work cannot be reached safely without it.',
            'local' => 'A leaking valley in Stockport and a flat roof in Salford are different jobs even when the postcode is still Greater Manchester.',
            'scope' => 'the roof has been seen and the access is known',
            'gas' => false,
            'h2' => 'Repairs before a full strip',
            'paragraphs' => [
                'We say whether the visit is a local repair or a larger re-cover. A slipped tile is not written up as a new roof. Flat roofs are quoted to the deck we can see, and if the deck is rotten the quote stops and says so.',
                'There is no per-tile price on this page. Height and access change the figure.',
            ],
            'faq' => ['Do you include scaffolding?', 'When the repair cannot be done safely without it, access is part of the POA quote. It is not a hidden extra after the work starts.'],
            'cta' => 'What is leaking or missing, and whether anyone has been in the loft. POA.',
            'placeholder' => 'Postcode, pitched or flat, leak or gutter, access notes…',
            'pillars' => [
                ['title' => 'Roof seen first', 'text' => 'The quote names the slope, the flat area or the gutter that is in scope.'],
                ['title' => 'Access included', 'text' => 'Scaffold or a tower is priced when the work needs it.'],
                ['title' => 'POA', 'text' => 'No tile or gutter price list.'],
            ],
            'links' => [
                ['/pages/services/brickwork', 'Brickwork'],
                ['/pages/services/insulation', 'Insulation'],
                ['/pages/services/rendering', 'Rendering'],
            ],
        ],
        'loft-conversions' => [
            'name' => 'Loft conversions',
            'accent' => 'Building work after the design is known. POA.',
            'lead' => 'Loft conversion building work covers the shell that has been designed: floors, stairs, insulation, partitions and the coordination of fire doors where the design asks for them. iComply does not grant planning permission or building-control approval. Those sit with the local authority.',
            'local' => 'A dormer in Trafford and a rooflight conversion in Bury are only quoted once the drawings or a measured survey exist.',
            'scope' => 'drawings or a measured survey set out the stair, the floor and the roof',
            'gas' => false,
            'h2' => 'What this page does not decide',
            'paragraphs' => [
                'Planning and building regulations are applications to the council. We can carry out the building work that a consented design describes, and we will say when a design is not there yet. Party-wall matters with a neighbour are not decided on this page.',
                'Insulation, stairs and the fire separation named on the drawing are in the building scope. An electrical design and a steel design, when the job needs them, are identified rather than guessed.',
            ],
            'faq' => ['Can you start from a photo of the loft?', 'A photo is enough to book a survey. It is not enough to quote a conversion. The price follows the design and the structure that is actually there.'],
            'cta' => 'Postcode, dormer or rooflights, and whether drawings exist. POA.',
            'placeholder' => 'Postcode, dormer or rooflights, drawings yes or no…',
            'pillars' => [
                ['title' => 'Design first', 'text' => 'The building quote follows drawings or a survey, not a bedroom count.'],
                ['title' => 'Council approvals', 'text' => 'Planning and building control remain with the local authority.'],
                ['title' => 'POA', 'text' => 'No typical conversion price is published.'],
            ],
            'links' => [
                ['/pages/services/insulation', 'Insulation'],
                ['/pages/services/carpentry', 'Carpentry'],
                ['/pages/services/electrical', 'Electrical'],
            ],
        ],
        'extensions' => [
            'name' => 'Extensions',
            'accent' => 'Structure and first fix, after drawings. POA.',
            'lead' => 'Extension building covers single-storey and other small extensions once the design, the ground and the structure are known. iComply does not grant planning permission or building-control approval. The council does.',
            'local' => 'A kitchen extension in Wigan and a side return in Chorlton are quoted from the drawings and what is under the ground, not from a floor-area tariff.',
            'scope' => 'the drawings and the ground conditions are known',
            'gas' => false,
            'h2' => 'From the ground to first fix',
            'paragraphs' => [
                'Foundations, the shell, the roof tie-in and first-fix readiness are the building scope when they are on the drawing. Structural calculations are by the designer named for that role. We do not invent a foundation depth on this page.',
                'Kitchens, electrics and plumbing that follow the shell are the matching service pages, programmed with the extension when you want one quote.',
            ],
            'faq' => ['Do you handle the planning application?', 'Planning and building regulations are lodged with the local authority. This page is the building work that follows an agreed design.'],
            'cta' => 'Postcode, single storey or otherwise, and whether drawings exist. POA.',
            'placeholder' => 'Postcode, extension type, drawings, ground you know about…',
            'pillars' => [
                ['title' => 'Drawings before a price', 'text' => 'The quote follows the design and a look at the site.'],
                ['title' => 'Ground included when scoped', 'text' => 'Foundations are priced when the design and the ground are known.'],
                ['title' => 'POA', 'text' => 'No price per square metre of extension.'],
            ],
            'links' => [
                ['/pages/services/groundworks', 'Groundworks'],
                ['/pages/services/kitchens', 'Kitchens'],
                ['/pages/services/roofing', 'Roofing'],
            ],
        ],
        'damp-proofing' => [
            'name' => 'Damp proofing',
            'accent' => 'Cause first, then the treatment. POA.',
            'lead' => 'Damp work starts with the source: rising damp, penetrating damp, a leak, or condensation. A chemical treatment is not sold from a phone description. Tanking is quoted only when the wall and the floor build-up have been seen.',
            'local' => 'A basement wall in central Manchester and a gable in Mossley are inspected before any treatment is named.',
            'scope' => 'the moisture source has been checked on site',
            'gas' => false,
            'h2' => 'Diagnosis before injection',
            'paragraphs' => [
                'We look at the outside ground level, the gutters, the plaster and the meter readings we actually take. If the cause is a leaking pipe or a blocked gutter, that is the repair. It is not written up as rising damp.',
                'Decorating over a wet wall is refused until the wall is ready. That sequencing is part of an honest quote.',
            ],
            'faq' => ['Do you guarantee a house will never be damp again?', 'No. The quote treats the cause we find and the areas we can see. A new leak later is a new visit.'],
            'cta' => 'Where the wall is wet, and whether it is a basement. POA.',
            'placeholder' => 'Postcode, rising, penetrating or not sure, basement yes or no…',
            'pillars' => [
                ['title' => 'Source first', 'text' => 'Gutters, ground levels and leaks are checked before a damp treatment is named.'],
                ['title' => 'Tanking when it fits', 'text' => 'Tanking is quoted for the construction that is there, not as a default.'],
                ['title' => 'POA', 'text' => 'No injection price list.'],
            ],
            'links' => [
                ['/pages/services/plumbing', 'Plumbing'],
                ['/pages/services/plastering', 'Plastering'],
                ['/pages/services/painting-decorating', 'Decorating'],
            ],
        ],
        'insulation' => [
            'name' => 'Insulation',
            'accent' => 'Lofts, floors and internal walls. POA.',
            'lead' => 'Insulation covers loft insulation, internal wall lining and floor insulation where the space allows it. It does not promise an EPC band. A certificate rating depends on the whole building, not on one product.',
            'local' => 'A loft in Hyde is measured for depth, hatches, tanks and downlights before anyone says what will fit.',
            'scope' => 'the areas are measured and the ventilation is understood',
            'gas' => false,
            'h2' => 'What gets measured',
            'paragraphs' => [
                'Loft work records the existing depth, the hatch, pipes, tanks and any lights that cannot be buried. Internal wall insulation is quoted with the lining and the sockets that have to move. Floor insulation is only offered when the floor can be lifted or the design already shows a zone for it.',
                'Cavity fill is not assumed. If a cavity is unsuitable, the quote says so.',
            ],
            'faq' => ['Will this raise the EPC to a particular band?', 'No. We do not guarantee a rating. An EPC is its own service and looks at the whole building.'],
            'cta' => 'Loft, wall or floor, and roughly how much is already there. POA.',
            'placeholder' => 'Postcode, loft wall or floor, existing insulation if known…',
            'pillars' => [
                ['title' => 'Measured areas', 'text' => 'Depth, hatches and services are recorded before material is ordered.'],
                ['title' => 'No rating promise', 'text' => 'The work is the insulation specified. It is not a guaranteed EPC band.'],
                ['title' => 'POA', 'text' => 'No pack price for a loft.'],
            ],
            'links' => [
                ['/pages/services/epc', 'EPC'],
                ['/pages/services/loft-conversions', 'Loft conversions'],
                ['/pages/services/dry-lining', 'Dry lining'],
            ],
        ],
        'rendering' => [
            'name' => 'Rendering',
            'accent' => 'Elevation seen, then coated. POA.',
            'lead' => 'External rendering covers repairs and new render coatings on houses and small blocks. The system follows the background and the exposure. Scaffold, where the elevation needs it, is part of the quote.',
            'local' => 'A rendered gable in Tameside is inspected for cracks, hollow areas and the ground level before a specification is written.',
            'scope' => 'the elevation and the access are known',
            'gas' => false,
            'h2' => 'Background and water',
            'paragraphs' => [
                'We check whether the wall is brick, block or an old render, and whether water is trapped behind it. A new coat over a failed background is not offered as a repair. Bellcasts and sills are included when they are part of keeping water off the wall.',
                'Painting a sound render can sit with decorating. A failed render is this service.',
            ],
            'faq' => ['Do you render over damp brickwork?', 'Not until the reason the wall is wet is dealt with. Otherwise the new coat traps the water.'],
            'cta' => 'Which elevation, and whether the render has blown. POA.',
            'placeholder' => 'Postcode, which walls, cracks or blown areas, access…',
            'pillars' => [
                ['title' => 'Elevation inspected', 'text' => 'Hollow render, cracks and sills are recorded first.'],
                ['title' => 'Access in the quote', 'text' => 'Scaffold is priced when the wall cannot be reached without it.'],
                ['title' => 'POA', 'text' => 'No price per square metre of render.'],
            ],
            'links' => [
                ['/pages/services/brickwork', 'Brickwork'],
                ['/pages/services/damp-proofing', 'Damp'],
                ['/pages/services/painting-decorating', 'Decorating'],
            ],
        ],
        'dry-lining' => [
            'name' => 'Dry lining',
            'accent' => 'Studs, board and ceilings. POA.',
            'lead' => 'Dry lining covers metal stud partitions, plasterboard linings, boarded ceilings and the setting out of those walls before skim or tape-and-joint. Timber studs that are carpentry can be boarded under this visit when the quote says so.',
            'local' => 'A partition in a Stockport office and a ceiling reboard in a Denton house are both set out on site before the board is fixed.',
            'scope' => 'the layout, the ceiling area and any fire or acoustic requirement are known',
            'gas' => false,
            'h2' => 'Partitions and ceilings',
            'paragraphs' => [
                'The quote names the wall lines, the board type and whether the finish is tape-and-joint or a skim by the plastering service. A fire-rated or acoustic board is only specified when the design or the brief asks for that performance. We do not invent a fire rating.',
                'Ceiling dry lining already has its own keyword guide. Stud walls that are timber sit with carpentry. Both still have Greater Manchester town pages.',
            ],
            'faq' => ['Is dry lining the same as plastering?', 'No. This page is the framing and the board. Skim over that board is plastering, included only when the quote says so.'],
            'cta' => 'Walls, ceilings, or both, and any acoustic or fire note from the design. POA.',
            'placeholder' => 'Postcode, partitions or ceilings, fire or acoustic requirement…',
            'pillars' => [
                ['title' => 'Set out first', 'text' => 'Wall lines and ceiling levels are agreed before board is fixed.'],
                ['title' => 'Board to the brief', 'text' => 'Standard, moisture, acoustic or fire-rated board is used only when the brief names it.'],
                ['title' => 'POA', 'text' => 'No boarding rate on this page.'],
            ],
            'links' => [
                ['/pages/keywords/ceiling-dry-lining', 'Ceiling dry lining'],
                ['/pages/keywords/stud-wall-construction', 'Stud walls'],
                ['/pages/services/plastering', 'Plastering'],
                ['/pages/services/carpentry', 'Carpentry'],
            ],
        ],
        'windows-doors' => [
            'name' => 'Windows and doors',
            'accent' => 'Measured openings, then the fit. POA.',
            'lead' => 'This page is the building-side supply and fit of windows, external doors and frames. Openings are measured on site. Fire door inspections and fire door maintenance stay on the fire doors service.',
            'local' => 'A bay in a Chorlton house and a flat entrance in Salford are measured separately. A catalogue width is not assumed.',
            'scope' => 'each opening has been measured',
            'gas' => false,
            'h2' => 'Windows, external doors and frames',
            'paragraphs' => [
                'We measure the structural opening, the cill and how the existing frame is fixed. Internal finishing, including making good the plaster reveals, is stated in the quote. Ironmongery is named rather than left as “standard”.',
                'Internal doors and cupboards are joinery. A fire door that the fire strategy requires is specified with the fire doors service, then fitted to that specification.',
            ],
            'faq' => ['Are these fire doors?', 'Only when the opening is specified as a fire door set. Everyday window and external door replacement is this page. Fire door surveys are separate.'],
            'cta' => 'How many windows and external doors, and the postcode. POA.',
            'placeholder' => 'Postcode, number of windows and doors, material if known…',
            'pillars' => [
                ['title' => 'Site measure', 'text' => 'Each opening is measured. A brochure size is not the order.'],
                ['title' => 'Making good', 'text' => 'The quote says whether reveals are plastered and decorated as part of the fit.'],
                ['title' => 'POA', 'text' => 'No window price list.'],
            ],
            'links' => [
                ['/pages/services/joinery', 'Joinery'],
                ['/pages/services/fire-doors', 'Fire doors'],
                ['/pages/services/plastering', 'Plastering'],
            ],
        ],
        'renovation' => [
            'name' => 'Renovation',
            'accent' => 'Voids and occupied homes. POA.',
            'lead' => 'Renovation covers void property works and occupied-home refurbishment: strip-out, the building trades and the sequencing of compliance visits the let actually needs. It is not a single package price and it does not invent a “full refurb” fee.',
            'local' => 'A void in Bolton is scoped room by room. An occupied house in Didsbury is scoped around the people who are still living there.',
            'scope' => 'the rooms, the occupancy and the certificates the file needs are listed',
            'gas' => true,
            'h2' => 'Void works and lived-in refurbishment',
            'paragraphs' => [
                'The programme names which trades are in: kitchens, bathrooms, plaster, decoration, flooring, joinery and the electrical or gas records the instruction includes. Each of those trades still has its own page. The renovation quote is the sequence, not a new catalogue.',
                'Void property renovation remains a keyword guide, with town pages for the same Greater Manchester places.',
            ],
            'faq' => ['Is a void a fixed package?', 'No. Two voids with the same bedroom count can need different kitchens, boards and certificates. The quote is price on application after the survey.'],
            'cta' => 'Void or occupied, postcode, and which trades you already know you need. POA.',
            'placeholder' => 'Postcode, void or occupied, rooms, certificates the let needs…',
            'pillars' => [
                ['title' => 'Surveyed rooms', 'text' => 'The scope lists the rooms and the trades, including what is left alone.'],
                ['title' => 'Certificates sequenced', 'text' => 'Electrical and gas records are booked around the building work, not after a tenant has moved in, when the instruction says so.'],
                ['title' => 'POA', 'text' => 'No refurbishment package price.'],
            ],
            'links' => [
                ['/pages/keywords/void-property-renovation', 'Void property renovation'],
                ['/pages/services/kitchens', 'Kitchens'],
                ['/pages/services/bathrooms', 'Bathrooms'],
                ['/pages/services/property-refurbishment-pm', 'Refurbishment sequencing'],
            ],
        ],
        'groundworks' => [
            'name' => 'Groundworks',
            'accent' => 'Drainage, reduced dig, small foundations. POA.',
            'lead' => 'Groundworks covers drainage runs, reduced-level digs, hardstanding and foundations for the small builds already designed. It is not a civil engineering design and it does not invent a foundation depth.',
            'local' => 'A drain run in Eccles and a patio base in Urmston are set out on the ground that is there, including what the scan or the trial hole shows.',
            'scope' => 'the levels, the drain route or the foundation design are known',
            'gas' => false,
            'h2' => 'What is under the surface',
            'paragraphs' => [
                'We need the line of the drain or the edge of the slab, and any information you already have on existing services. If a trial hole changes the ground, the quote is revised before the dig continues. Hardstanding is specified with a fall so water leaves the building.',
                'Extension foundations follow the extension design. They are not priced from a photograph of the garden.',
            ],
            'faq' => ['Do you scan for services?', 'Existing drawings and any scan you already have are used. If the route is unknown, the quote says what has to be proved before the machine starts.'],
            'cta' => 'Drainage, hardstanding or foundations, plus the postcode. POA.',
            'placeholder' => 'Postcode, drain, driveway or foundations, any drawings…',
            'pillars' => [
                ['title' => 'Line agreed', 'text' => 'Drains and slabs are set out before the dig.'],
                ['title' => 'Ground can change the price', 'text' => 'If the ground is not what the design assumed, that is reported before work continues.'],
                ['title' => 'POA', 'text' => 'No day rate for an excavator is published.'],
            ],
            'links' => [
                ['/pages/services/extensions', 'Extensions'],
                ['/pages/services/landscaping', 'Landscaping'],
                ['/pages/services/plumbing', 'Plumbing'],
            ],
        ],
        'landscaping' => [
            'name' => 'Landscaping',
            'accent' => 'Patios, fences and falls. POA.',
            'lead' => 'Hard landscaping covers patios, paths, fencing and small external works on residential plots. Falls and where the water goes are part of the job. Planting schemes and garden design are not sold as a product on this page.',
            'local' => 'A patio in Sale and a fence line in Leigh are measured on site, including the boundaries you point out.',
            'scope' => 'the plot is measured and the levels are seen',
            'gas' => false,
            'h2' => 'Hard surfaces and boundaries',
            'paragraphs' => [
                'We measure the area, the levels against the house damp course, and the fence bays. A patio that bridges a damp course is not built that way. Fence posts are specified to the ground we find, not to a catalogue panel count alone.',
                'Drainage under a new hard surface sits with groundworks when the run is more than a simple fall to an existing gully.',
            ],
            'faq' => ['Do you design gardens?', 'This page is the hard landscaping you can walk on or the fence you have asked for. A planting design is not part of the quote unless it is written in.'],
            'cta' => 'Patio, path or fence, and the postcode. POA.',
            'placeholder' => 'Postcode, patio path or fence, approximate size…',
            'pillars' => [
                ['title' => 'Levels against the house', 'text' => 'New surfaces stay below the damp course and fall away from the wall.'],
                ['title' => 'Boundaries you identify', 'text' => 'Fence lines follow the boundary you confirm. We do not decide a legal boundary.'],
                ['title' => 'POA', 'text' => 'No patio pack price.'],
            ],
            'links' => [
                ['/pages/services/groundworks', 'Groundworks'],
                ['/pages/services/brickwork', 'Brickwork'],
                ['/pages/services/rendering', 'Rendering'],
            ],
        ],
        'commercial-fit-out' => [
            'name' => 'Commercial fit-out',
            'accent' => 'Partitions, services, finishes. POA.',
            'lead' => 'Commercial fit-out covers light office and shop work: partitions, the coordination of electrical and plumbing services, and the finishes on the drawing. It is not a claim to be principal designer, and it does not replace the fire strategy.',
            'local' => 'A unit in Manchester city centre and a first-floor office in Stockport are surveyed for access, the existing services and the hours the building will allow.',
            'scope' => 'the drawing, the unit and the working hours are known',
            'gas' => false,
            'h2' => 'What is coordinated',
            'paragraphs' => [
                'Partitions, doors, decoration and the service routes are listed against the drawing. Fire alarms, emergency lighting and any fire doors the strategy names stay on their own service pages and are booked into the programme rather than implied.',
                'Out-of-hours working is quoted when the building requires it. It is not assumed to be free.',
            ],
            'faq' => ['Are you the principal designer?', 'Not by default. If a project needs a principal designer under the construction regulations, that role is appointed separately and named. This page is the fit-out work.'],
            'cta' => 'Unit address, drawings if you have them, and the hours we can work. POA.',
            'placeholder' => 'Address, office or shop, drawings, access hours…',
            'pillars' => [
                ['title' => 'Drawing led', 'text' => 'Partitions and finishes follow the drawing and a site survey.'],
                ['title' => 'Services kept separate', 'text' => 'Electrical, plumbing and fire work are named in the programme, on their own pages.'],
                ['title' => 'POA', 'text' => 'No fit-out price per square foot.'],
            ],
            'links' => [
                ['/pages/services/dry-lining', 'Dry lining'],
                ['/pages/services/electrical', 'Electrical'],
                ['/pages/services/fire-alarms', 'Fire alarms'],
            ],
        ],
        'plumbing' => [
            'name' => 'Plumbing',
            'accent' => 'Leaks, wastes and pipework. POA.',
            'lead' => 'Plumbing covers leaks, wastes, bathrooms, kitchens and pipework in homes and light commercial buildings. Water fittings rules apply where the job changes a supply. Gas appliances are not part of this trade.',
            'local' => 'A leak in a Prestwich flat and a bathroom waste in Wigan are both attended from Stockport once the fault and the access are known.',
            'scope' => 'the fault or the installation is described and, where it matters, seen',
            'gas' => true,
            'h2' => 'Wholesome water and wastes',
            'paragraphs' => [
                'We identify the fitting, the leak or the new run, and whether the water undertaker expects a notification. Backflow protection is used when the fittings rules require it. A temporary repair is described as temporary.',
                'Boiler work and landlord gas records are not booked as plumbing. They stay with Gas Safe registered engineers.',
            ],
            'faq' => ['Do you repair boilers?', 'No. Boiler installation, servicing and landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.'],
            'cta' => 'What is leaking or being installed, and the postcode. POA.',
            'placeholder' => 'Postcode, leak, bathroom or pipework, access…',
            'pillars' => [
                ['title' => 'Fault named', 'text' => 'The quote says which fitting, waste or run is in scope.'],
                ['title' => 'Fittings rules', 'text' => 'New supplies follow the water fittings rules, including notification where it is required.'],
                ['title' => 'POA', 'text' => 'No call-out menu.'],
            ],
            'links' => [
                ['/pages/services/bathrooms', 'Bathrooms'],
                ['/pages/services/kitchens', 'Kitchens'],
                ['/pages/services/water-wras', 'Water fittings'],
                ['/pages/gas-safety-certificate', 'Gas safety certificate'],
            ],
        ],
        'electrics-first-fix' => [
            'name' => 'First fix electrical',
            'accent' => 'Cables in, before the plaster. POA.',
            'lead' => 'First fix electrical is the containment and cabling for renovations, conversions and small new work, before plaster and second fix. It is planned to BS 7671. Second fix and the certificate follow when the accessories are on.',
            'local' => 'A rewire first fix in a Stockport terrace is walked with the other trades so the cables are in before the plasterers return.',
            'scope' => 'the drawing or the room list and the consumer unit are known',
            'gas' => false,
            'h2' => 'Before the walls close',
            'paragraphs' => [
                'The first fix quote lists the circuits, the routes and what is left for second fix. Chasing and making good are stated, not assumed. An EICR of the finished installation is a separate visit when the instruction includes one.',
                'EV chargers and consumer-unit changes that are not part of a first fix stay on the electrical service page.',
            ],
            'faq' => ['Does first fix include the certificate?', 'The certificate follows the completed installation, including second fix and test. A first-fix visit on its own does not produce a finished EICR.'],
            'cta' => 'Rooms or drawings, and whether the board is being changed. POA.',
            'placeholder' => 'Postcode, rewire or extension, drawings, consumer unit…',
            'pillars' => [
                ['title' => 'Routes agreed', 'text' => 'Cable routes are agreed with the plaster and joinery programme.'],
                ['title' => 'Second fix separate', 'text' => 'Accessories and the test happen when the finishes allow them.'],
                ['title' => 'POA', 'text' => 'No per-point price list.'],
            ],
            'links' => [
                ['/pages/services/electrical', 'Electrical'],
                ['/pages/services/plastering', 'Plastering'],
                ['/pages/services/renovation', 'Renovation'],
            ],
        ],
        'building-maintenance' => [
            'name' => 'Building maintenance',
            'accent' => 'Reactive and planned visits. POA.',
            'lead' => 'Building maintenance is the planned and reactive upkeep of the building fabric: doors, skirtings, minor plaster, decorating touch-ups, leaks reported to plumbing, and the small jobs a landlord or agent lists. It is not a compliance contract by another name.',
            'local' => 'A reported door in Manchester and a list of void snags in Bolton are both quoted from the list, with photos when you have them.',
            'scope' => 'the snag list or the reported fault is clear',
            'gas' => false,
            'h2' => 'Lists, not a blank day',
            'paragraphs' => [
                'Send the address and the list. We will say which lines this visit can close and which belong to electrical, gas, roofing or a specialist ticket. A day on site is not sold as unlimited work.',
                'Where a line needs a specialist ticket, an approved subcontractor attends. The quote names that rather than folding it into a general handy visit.',
            ],
            'faq' => ['Is this a retained contract?', 'It can be planned visits or a one-off list. Either way the scope is written down and the price is on application. There is no unnamed retainer on this page.'],
            'cta' => 'Address and the list of jobs, or the fault. POA.',
            'placeholder' => 'Address, snag list or fault, access hours…',
            'pillars' => [
                ['title' => 'A written list', 'text' => 'The visit closes the lines that were agreed, and reports the ones that were not.'],
                ['title' => 'Right trade', 'text' => 'Gas, electrical test and roof work are passed to the service that actually does them.'],
                ['title' => 'POA', 'text' => 'No hourly menu is published.'],
            ],
            'links' => [
                ['/pages/services/plumbing', 'Plumbing'],
                ['/pages/services/carpentry', 'Carpentry'],
                ['/pages/services/renovation', 'Renovation'],
            ],
        ],
        'property-refurbishment-pm' => [
            'name' => 'Refurbishment project support',
            'accent' => 'Sequence the trades. POA.',
            'lead' => 'Refurbishment project support is the sequencing of kitchens, bathrooms and multi-room voids so the building trades and the compliance visits fit one programme. It does not replace those trades and it does not publish a project-management percentage.',
            'local' => 'A portfolio void in Salford is sequenced from the survey and the date the property has to be ready, when you have one.',
            'scope' => 'the property, the trades and the target dates are known',
            'gas' => true,
            'h2' => 'Order of work',
            'paragraphs' => [
                'Strip-out, first fix, plaster, second fix, decoration and the certificates are put in an order the building can actually follow. Gas and electrical records are booked when the installation is ready to be inspected, not before the boards are on.',
                'The building work itself stays on the kitchen, bathroom, plastering and renovation pages. This page is the programme around them.',
            ],
            'faq' => ['Do you charge a percentage of the build?', 'No percentage is published. The support is quoted price on application once the property and the trades are known.'],
            'cta' => 'Address, which trades, and any date the property must be ready. POA.',
            'placeholder' => 'Address, void or occupied, trades, target date…',
            'pillars' => [
                ['title' => 'One programme', 'text' => 'Trades are ordered so plaster is not waiting on cables that were never booked.'],
                ['title' => 'Certificates at the right time', 'text' => 'Electrical and gas visits are placed when the installation can actually be inspected.'],
                ['title' => 'POA', 'text' => 'No management percentage on this page.'],
            ],
            'links' => [
                ['/pages/services/renovation', 'Renovation'],
                ['/pages/keywords/void-property-renovation', 'Void property renovation'],
                ['/pages/services/kitchens', 'Kitchens'],
                ['/pages/services/bathrooms', 'Bathrooms'],
            ],
        ],
    ];
    return $rows;
}
