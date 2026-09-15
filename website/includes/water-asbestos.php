<?php
/**
 * Legionella + asbestos catalogue: honest UK copy, POA only.
 * No invented accreditations, prices, licences or lab claims.
 */
declare(strict_types=1);

function waterAsbestosServiceSlugs(): array
{
    return ['legionella-risk-assessment', 'asbestos-survey'];
}

function isPoaService(string $slug): bool
{
    $slug = areaSlug($slug);
    if (in_array($slug, waterAsbestosServiceSlugs(), true)) {
        return true;
    }
    $pricing = strtoupper((string)(getServiceMeta($slug)['pricing'] ?? ''));
    return $pricing === 'POA';
}

function waterAsbestosKeywordCatalog(): array
{
    return [
        'legionella-risk-assessment' => [
            'name' => 'Legionella Risk Assessment',
            'service' => 'legionella-risk-assessment',
            'related' => 'legionella-testing',
            'intro' => 'A Legionella risk assessment looks at how stored and circulated water could allow Legionella bacteria to grow, and what a landlord or other dutyholder should do about it. iComply scopes assessments for rented homes, blocks and workplaces across the North West. Price on application after we know the system type and access.',
            'body' => 'UK dutyholders use HSE Approved Code of Practice L8 and HSG274 as the usual reference for managing Legionella risk. An assessment typically records water storage, outlets, temperatures, little-used parts of the system and who is responsible for flushing or temperature checks. Sampling is not automatically required for every property — we only recommend it when the assessment suggests it would help. We do not list catalogue prices; quotes are POA once scope is clear. We do not claim a named laboratory accreditation on this page.',
            'meta_desc' => 'Legionella risk assessment across Greater Manchester and the North West. Water hygiene scoped from Stockport. POA after we confirm the system.',
            'focus_points' => [
                'Written risk assessment for the water system you actually have',
                'Temperature, storage and little-used outlet notes',
                'Sampling only when the assessment supports it',
                'POA quote — no invented fee list',
            ],
            'faq' => [
                ['Do landlords always need a Legionella risk assessment?', 'HSE expects dutyholders to consider Legionella risk. Many simple domestic systems are lower risk, but rented and commercial sites still need a suitable assessment. We confirm what is appropriate after you describe the property.'],
                ['Do you always take water samples?', 'No. Assessment comes first. Sampling is useful on some stored-water or complex systems and is quoted separately as POA if recommended.'],
                ['What does it cost?', 'Price on application. Outlets, tanks, access and whether sampling is needed all change the work. You get a written figure after scope — we do not publish a made-up price.'],
            ],
            'seo_keywords' => 'legionella risk assessment, Legionnaires disease risk assessment, water hygiene North West, legionella Stockport, legionella Manchester, POA',
        ],
        'legionella-testing' => [
            'name' => 'Legionella Testing',
            'service' => 'legionella-risk-assessment',
            'related' => 'legionella-water-testing',
            'intro' => 'Legionella testing means taking water samples where an assessment or control scheme says it is useful. iComply arranges sampling as part of a scoped water-hygiene visit. POA — we do not invent a per-sample price here.',
            'body' => 'Testing without a risk picture is easy to over- or under-do. We start from how the system is used, then agree outlets to sample if testing is justified. Results need a competent interpretation against your control scheme, not a lone number on a sheet. Laboratory identity and method are confirmed at quote stage; this page does not invent a UKAS claim.',
            'meta_desc' => 'Legionella water testing arranged after a scoped assessment. North West coverage from Stockport. POA.',
            'focus_points' => [
                'Samples only where they add something to the assessment',
                'Outlet list agreed before the visit',
                'Written notes you can file with the risk record',
                'POA after outlet count and access are known',
            ],
            'faq' => [
                ['Is legionella testing a legal annual must?', 'Not as a blanket rule for every house. Frequency follows risk and HSE guidance, not a one-size calendar we invented.'],
                ['Can you test today without an assessment?', 'We can discuss urgent concerns, but a proper assessment still sits behind useful testing. Tell us the property type.'],
                ['Are prices listed?', 'No. Testing is POA. Dip-prices online are not how we quote.'],
            ],
            'seo_keywords' => 'legionella testing, legionella water test, water sample legionella, Stockport legionella testing, Manchester legionella testing',
        ],
        'legionella-water-testing' => [
            'name' => 'Legionella Water Testing',
            'service' => 'legionella-risk-assessment',
            'related' => 'water-hygiene-testing',
            'intro' => 'Legionella water testing is the sample-and-report step used on some tanks, calorifiers and complex loops. iComply quotes it as POA alongside or after a risk assessment.',
            'body' => 'Hot- and cold-water systems differ. We ask about tanks, showers, dead-legs and vulnerable occupants before recommending samples. A result is not a certificate that “the building is safe forever” — it is evidence at a point in time. Control measures (flushing, temperature, cleaning) still matter.',
            'meta_desc' => 'Legionella water testing for tanks and outlets in the North West. Scoped, POA, no invented lab badges.',
            'focus_points' => [
                'Hot and cold outlets agreed in writing',
                'Fits beside an L8-style risk record',
                'Clear next steps if results need action',
                'POA only',
            ],
            'faq' => [
                ['Do you remove Legionella from the pipework?', 'Testing does not clean a system. If control work is needed we discuss that separately and still quote POA.'],
                ['Will I get a piece of paper?', 'You get a written record of what was sampled and what was advised. We do not invent a branded “certificate product”.'],
            ],
            'seo_keywords' => 'legionella water testing, water sample for legionella, North West water testing, Stockport, Manchester',
        ],
        'water-hygiene-testing' => [
            'name' => 'Water Hygiene Testing',
            'service' => 'legionella-risk-assessment',
            'related' => 'water-risk-assessment',
            'intro' => 'Water hygiene testing covers temperature checks, little-used outlets and, where justified, laboratory samples. iComply treats it as part of Legionella control, not a standalone gadget visit. POA after scope.',
            'body' => 'Hygiene work is mostly about how water sits and moves. We look at sentinel temperatures, shower heads and stored water when they exist. Landlords and FM teams use the notes for their file. We do not sell a numbered “water hygiene certificate” product with a fake price.',
            'meta_desc' => 'Water hygiene testing and Legionella control notes across Greater Manchester. POA from Stockport.',
            'focus_points' => [
                'Temperature and outlet hygiene notes',
                'Sampling only when it helps the scheme',
                'Useful for landlords, agents and FM',
                'POA quote',
            ],
            'faq' => [
                ['Is this the same as a Legionella risk assessment?', 'Related but not identical. Assessment describes risk and responsibilities; testing collects measurements or samples against that picture.'],
            ],
            'seo_keywords' => 'water hygiene testing, water hygiene North West, legionella control, Stockport water hygiene',
        ],
        'legionnaires-disease-risk-assessment' => [
            'name' => 'Legionnaires Disease Risk Assessment',
            'service' => 'legionella-risk-assessment',
            'related' => 'legionella-risk-assessment',
            'intro' => 'A Legionnaires’ disease risk assessment is the same duty as a Legionella risk assessment: understand whether the water system could expose people, then record controls. iComply writes that in plain UK English. POA.',
            'body' => 'People search both names. The underlying HSE expectation does not change. We describe the system, who might be exposed, and proportionate controls. We will not claim a medical diagnosis service or a guaranteed “disease-free” building.',
            'meta_desc' => 'Legionnaires’ disease risk assessment for North West property. Same duty as Legionella RA. POA.',
            'focus_points' => [
                'Plain-English written assessment',
                'Proportionate to the water system',
                'No medical claims',
                'POA',
            ],
            'faq' => [
                ['Is Legionnaires’ different from Legionella?', 'Legionnaires’ disease is the illness; Legionella is the bacterium. The property duty is to manage the risk in water systems.'],
            ],
            'seo_keywords' => 'Legionnaires disease risk assessment, legionella risk assessment, North West, Stockport, Manchester',
        ],
        'landlord-legionella-risk-assessment' => [
            'name' => 'Landlord Legionella Risk Assessment',
            'service' => 'legionella-risk-assessment',
            'related' => 'commercial-legionella-risk-assessment',
            'intro' => 'Landlords remain responsible for considering Legionella risk in rented homes. iComply offers a scoped landlord Legionella risk assessment from Stockport — houses, flats and small shared lets, not a published let-fee. POA after property details.',
            'body' => 'A typical house with a combi and no stored cold-water tank is often lower risk than a property with tanks, unused en-suites or a shared system. We still need to see or be told how water is stored and used. The written note is for your management file, not a substitute for ongoing flushing if that is the agreed control. Pair with the landlord hub and the service page; we do not invent an annual test price or a UKAS line.',
            'meta_desc' => 'Landlord Legionella risk assessment in Greater Manchester and the North West. POA after property details.',
            'focus_points' => [
                'Rented houses, flats and small HMOs',
                'Honest lower-risk vs stored-water cases',
                'File-ready written record',
                'POA',
            ],
            'faq' => [
                ['Do I need this every year?', 'Review when the system or occupancy changes, and at an interval that matches risk — not a date we invented for marketing.'],
                ['Can this go with my EICR and gas cert?', 'Yes, many landlords book water hygiene in the same conversation as other certificates. Each item is still scoped and POA.'],
            ],
            'seo_keywords' => 'landlord legionella risk assessment, landlord water hygiene, rented property legionella Stockport Manchester',
        ],
        'commercial-legionella-risk-assessment' => [
            'name' => 'Commercial Legionella Risk Assessment',
            'service' => 'legionella-risk-assessment',
            'related' => 'landlord-legionella-risk-assessment',
            'intro' => 'Commercial and multi-occupied buildings usually need a more detailed Legionella risk assessment than a simple flat. iComply scopes offices, retail, care and blocks from Stockport. POA.',
            'body' => 'Plant rooms, stored water, little-used wings and vulnerable occupants change the work. We ask for schematic information if you have it, and we walk the outlets we can access. Larger schemes may need a written control programme; we say so instead of pretending a one-page visit covers a hospital-grade system.',
            'meta_desc' => 'Commercial Legionella risk assessment for North West workplaces and blocks. POA after we see complexity.',
            'focus_points' => [
                'Workplaces, retail and multi-let',
                'Access and plant information requested up front',
                'Honest about when a larger scheme is needed',
                'POA',
            ],
            'faq' => [
                ['Do you write an L8 scheme of control?', 'We record controls that fit the building. A full written scheme for a complex site is scoped separately — still POA, never a fake package price.'],
            ],
            'seo_keywords' => 'commercial legionella risk assessment, workplace water hygiene, office legionella Manchester Stockport',
        ],
        'water-risk-assessment' => [
            'name' => 'Water Risk Assessment',
            'service' => 'legionella-risk-assessment',
            'related' => 'legionella-risk-assessment',
            'intro' => 'A water risk assessment in this context means assessing Legionella and related hygiene risk in the domestic or commercial water system. iComply quotes POA from Stockport.',
            'body' => 'Searchers use “water risk assessment” and “Legionella risk assessment” for the same duty. We keep the language aligned with HSE L8/HSG274 and avoid extra product names. No invented accreditations.',
            'meta_desc' => 'Water risk assessment (Legionella) across the North West. POA. Stockport-based team.',
            'focus_points' => [
                'Same duty as Legionella RA',
                'Written, property-specific notes',
                'POA',
            ],
            'faq' => [
                ['Is this a drinking-water quality test?', 'No. It is about Legionella and system hygiene risk, not a full DWI drinking-water chemistry suite.'],
            ],
            'seo_keywords' => 'water risk assessment, legionella water risk, North West, Stockport, Manchester',
        ],
        'asbestos-survey' => [
            'name' => 'Asbestos Survey',
            'service' => 'asbestos-survey',
            'related' => 'asbestos-management-survey',
            'intro' => 'An asbestos survey records where asbestos-containing materials may be, so a dutyholder can manage them. iComply scopes management and refurbishment-style surveys for North West property. Price on application. We do not carry out licensed asbestos removal on this service page.',
            'body' => 'The Control of Asbestos Regulations 2012 sit behind the duty to manage in non-domestic premises and in the common parts of many multi-occupied buildings. Survey type depends on whether you are only managing the building or about to refurbish or strip it. Sampling, where needed, is agreed in the scope. We do not invent a UKAS, BOHS or HSE-licence claim here. Licensed removal, if required, is by others.',
            'meta_desc' => 'Asbestos survey for management or refurbishment across Greater Manchester. POA. No invented licences or prices.',
            'focus_points' => [
                'Management vs refurbishment survey scoped honestly',
                'Sampling only where the visit needs it',
                'Written register-style notes for the dutyholder file',
                'POA — licensed removal is not this service',
            ],
            'faq' => [
                ['Do you remove asbestos?', 'Not as a licensed removal contractor on this page. If removal is required we say so and you appoint a suitable licensed contractor.'],
                ['Which survey do I need?', 'A management survey is for normal occupation. A refurbishment or demolition survey is for intrusive works. Tell us the planned work.'],
                ['What does it cost?', 'POA. Size, age, access and how intrusive the survey must be all change the quote. We do not publish a fake starting price.'],
            ],
            'seo_keywords' => 'asbestos survey, asbestos testing, asbestos management survey, Stockport asbestos, Manchester asbestos, POA',
        ],
        'asbestos-testing' => [
            'name' => 'Asbestos Testing',
            'service' => 'asbestos-survey',
            'related' => 'asbestos-sample-testing',
            'intro' => 'Asbestos testing means sampling suspect material and having it analysed so you know what you are managing. iComply includes testing in a scoped survey visit when needed. POA. No invented lab badge.',
            'body' => 'A sample is only useful if the location is recorded and the result goes into your management plan. We will not scrape random decorative coatings “just in case” without a reason. Analysis is arranged as part of the quote; we do not name a certificate brand we do not hold on this page.',
            'meta_desc' => 'Asbestos testing and sampling as part of a scoped survey. North West. POA.',
            'focus_points' => [
                'Suspect materials recorded before sampling',
                'Results for the management file',
                'POA after sample count is known',
            ],
            'faq' => [
                ['Can I post you a bit of tile?', 'Uncontrolled sampling can spread fibres. Arrange a scoped visit instead of posting debris.'],
                ['Is every textured coating asbestos?', 'No. Only analysis tells you. We sample when the survey scope includes it.'],
            ],
            'seo_keywords' => 'asbestos testing, asbestos sample, artex testing, North West asbestos testing, Stockport, Manchester',
        ],
        'asbestos-management-survey' => [
            'name' => 'Asbestos Management Survey',
            'service' => 'asbestos-survey',
            'related' => 'asbestos-refurbishment-survey',
            'intro' => 'A management survey is the usual starting point for the duty to manage asbestos during normal occupation. iComply scopes these for commercial units, common parts and workplaces. POA.',
            'body' => 'The surveyor looks at reasonably accessible materials that could be disturbed in day-to-day use or maintenance. It is not a demolition survey. Areas that cannot be seen are recorded as such instead of pretended. Recommendations stay within manage, label, monitor or seek removal by others.',
            'meta_desc' => 'Asbestos management survey for occupied North West buildings. POA. Duty to manage, no fake licence claims.',
            'focus_points' => [
                'Normal occupation and maintenance access',
                'Inaccessible areas called out honestly',
                'Register-style output for the dutyholder',
                'POA',
            ],
            'faq' => [
                ['Will you open up every void?', 'Not on a management survey. Intrusive opening belongs on a refurbishment or demolition survey.'],
            ],
            'seo_keywords' => 'asbestos management survey, duty to manage asbestos, commercial asbestos survey Manchester Stockport',
        ],
        'asbestos-refurbishment-survey' => [
            'name' => 'Asbestos Refurbishment Survey',
            'service' => 'asbestos-survey',
            'related' => 'asbestos-management-survey',
            'intro' => 'A refurbishment survey is more intrusive and is used before strip-out, major works or demolition. iComply quotes these as POA once we know the rooms and the planned work.',
            'body' => 'You need this when a management survey is not enough because works will open walls, floors or plant. Access, isolation and making-good expectations must be agreed. We will not treat a refurbishment survey as a cheap add-on line with a made-up price.',
            'meta_desc' => 'Asbestos refurbishment survey before works in the North West. POA after we know the opening-up.',
            'focus_points' => [
                'Matched to the planned refurbishment or strip',
                'Intrusive access agreed in writing',
                'POA',
            ],
            'faq' => [
                ['Can you do this while staff are in the building?', 'Sometimes, room by room, if access and isolation are safe. We say when an empty area is required.'],
            ],
            'seo_keywords' => 'asbestos refurbishment survey, asbestos demolition survey, refurb asbestos North West',
        ],
        'landlord-asbestos-survey' => [
            'name' => 'Landlord Asbestos Survey',
            'service' => 'asbestos-survey',
            'related' => 'commercial-asbestos-survey',
            'intro' => 'Landlords of non-domestic parts and many blocks need to manage asbestos in common areas. iComply scopes landlord asbestos surveys from Stockport — management or refurbishment, quoted POA, never a published pack price.',
            'body' => 'A single private house that is purely domestic is a different duty from a house converted to flats with shared halls. We ask which you have. Inside a tenanted dwelling, access and type of survey still follow the planned works. Licensed removal is by others. We do not invent a “landlord pack price” or a UKAS / licence claim on this page.',
            'meta_desc' => 'Landlord asbestos survey for common parts and rented stock in the North West. POA.',
            'focus_points' => [
                'Common parts vs purely domestic explained',
                'File-ready notes for agents',
                'POA',
            ],
            'faq' => [
                ['Does every rented terrace need a full survey?', 'Not automatically. Duty depends on the building and whether you are managing non-domestic or common parts. Describe the property and we will say what is proportionate.'],
            ],
            'seo_keywords' => 'landlord asbestos survey, HMO asbestos, common parts asbestos Stockport Manchester',
        ],
        'commercial-asbestos-survey' => [
            'name' => 'Commercial Asbestos Survey',
            'service' => 'asbestos-survey',
            'related' => 'landlord-asbestos-survey',
            'intro' => 'Commercial asbestos surveys support the duty to manage in workplaces, shops and industrial units. iComply quotes POA after floor area, era and access are known.',
            'body' => 'Older industrial and retail stock around Greater Manchester often has historic materials. A survey is how you stop surprises during maintenance. We still will not claim licensed removal or a named analyst accreditation on this page.',
            'meta_desc' => 'Commercial asbestos survey across Greater Manchester and the North West. POA.',
            'focus_points' => [
                'Workplaces and industrial units',
                'Management or refurbishment scoped to the job',
                'POA',
            ],
            'faq' => [
                ['Can you survey a live warehouse?', 'Often yes if safe routes are agreed. Some plant rooms need a shutdown — we tell you before attending.'],
            ],
            'seo_keywords' => 'commercial asbestos survey, workplace asbestos, industrial asbestos survey Manchester Stockport',
        ],
        'asbestos-sample-testing' => [
            'name' => 'Asbestos Sample Testing',
            'service' => 'asbestos-survey',
            'related' => 'asbestos-testing',
            'intro' => 'Asbestos sample testing is the analysis step for materials collected during a scoped survey. Quoted POA per agreed sample count. No postal DIY kit story.',
            'body' => 'Each sample should have a location, a photograph where useful, and a result that feeds the register. Bulk analysis method is confirmed at quote time. We do not advertise a consumer “kit price”.',
            'meta_desc' => 'Asbestos sample testing arranged with a scoped survey. North West. POA.',
            'focus_points' => [
                'Samples tied to rooms and materials',
                'Results for the register',
                'POA',
            ],
            'faq' => [
                ['How many samples will you take?', 'As few as needed to answer the survey question — agreed in scope, not a surprise add-on story.'],
            ],
            'seo_keywords' => 'asbestos sample testing, bulk sample asbestos, North West asbestos analysis',
        ],
        'asbestos-inspection' => [
            'name' => 'Asbestos Inspection',
            'service' => 'asbestos-survey',
            'related' => 'asbestos-survey',
            'intro' => 'An asbestos inspection can mean a re-inspection of known materials or a first look that leads to a full survey. iComply will say which you need. POA.',
            'body' => 'Re-inspections check condition of items already on a register. A first visit to an unknown building is usually a survey, not a ten-minute glance. We will not sell “inspection” as a cheaper survey with the same legal weight.',
            'meta_desc' => 'Asbestos inspection or re-inspection of known materials. North West. POA.',
            'focus_points' => [
                'Re-inspection vs full survey explained',
                'Condition notes for the existing register',
                'POA',
            ],
            'faq' => [
                ['Is an inspection enough before I knock a wall down?', 'No. Intrusive works need the right survey type, not a visual glance.'],
            ],
            'seo_keywords' => 'asbestos inspection, asbestos reinspection, asbestos register check Stockport Manchester',
        ],
    ];
}

function waterAsbestosServiceCopy(string $slug): ?array
{
    if ($slug === 'legionella-risk-assessment') {
        return [
            'hero_accent' => 'For landlords — assessed, sampled only if needed, POA.',
            'pillars' => [
                ['title' => 'Landlord risk assessment', 'text' => 'A written look at stored water, outlets, temperatures and who does the controls — aligned with HSE Approved Code of Practice L8 and HSG274, not a made-up certificate product.'],
                ['title' => 'Testing when useful', 'text' => 'Water samples are recommended only when the system and occupancy justify them. Not a default extra on every let.'],
                ['title' => 'Honest commercial terms', 'text' => 'Every job is price on application. We do not publish fake starting fees, annual “must-test” prices or laboratory badges we have not stated.'],
            ],
            'intro' => [
                'Landlords remain responsible for considering Legionella risk in rented homes. iComply writes a scoped Legionella risk assessment for single lets, small portfolios, blocks and workplaces across Greater Manchester and the wider North West — from Offerton, Stockport SK2.',
                'A typical house with a combi boiler and no stored cold-water tank is often lower risk than a property with tanks, unused en-suites or a shared system. We still need to see or be told how water is stored and used. The written note is for your management file, not a substitute for ongoing flushing if that is the agreed control.',
                'Sampling is a tool, not the whole service. We do not invent a legal “annual water test” for every terrace. Quotes are POA after you send the postcode, property type and a short note on stored water or plant.',
            ],
            'sections' => [
                [
                    'h2' => 'What a landlord Legionella assessment covers',
                    'p' => [
                        'UK dutyholders use HSE ACOP L8 and HSG274 as the usual reference for managing Legionella risk. An assessment typically records water storage, outlets, temperatures, little-used parts of the system and who is responsible for flushing or temperature checks.',
                        'We describe the system you actually have. We will not claim a medical diagnosis service or a guaranteed “disease-free” building. Legionnaires’ disease is the illness; Legionella is the bacterium — the property duty is to manage the risk in water systems.',
                    ],
                ],
                [
                    'h2' => 'When water samples help — and when they do not',
                    'p' => [
                        'Testing without a risk picture is easy to over- or under-do. Assessment comes first. Sampling is useful on some stored-water or complex systems and is quoted separately as POA if recommended.',
                        'A result is evidence at a point in time, not a certificate that the building is safe forever. Control measures (flushing, temperature, cleaning) still matter. Laboratory identity and method are confirmed at quote stage — this page does not invent a UKAS claim.',
                    ],
                ],
                [
                    'h2' => 'Review, not a date we invented',
                    'p' => [
                        'Review when the system or occupancy changes, and at an interval that matches risk — not a calendar date invented for marketing. Many landlords book water hygiene in the same conversation as an EICR or gas safety record. Each item is still scoped and POA.',
                    ],
                ],
            ],
            'cta_line' => 'Postcode, property type and whether you have stored water. No catalogue fee.',
            'quote_placeholder' => 'Postcode, rented or commercial, stored tanks / unused showers, access notes…',
        ];
    }
    if ($slug === 'asbestos-survey') {
        return [
            'hero_accent' => 'For landlords — surveyed, sampled if needed, POA.',
            'pillars' => [
                ['title' => 'Management survey', 'text' => 'For normal occupation and the duty to manage under the Control of Asbestos Regulations 2012 — accessible materials, honest inaccessible notes.'],
                ['title' => 'Refurbishment survey', 'text' => 'For planned opening-up, strip-out or demolition. Scoped to the rooms and trades involved — not a cheap add-on line.'],
                ['title' => 'Testing & honest limits', 'text' => 'Sampling is part of scope when needed. Licensed asbestos removal is not this service. No invented UKAS, BOHS or HSE-licence line.'],
            ],
            'intro' => [
                'Landlords of non-domestic parts and many blocks need to manage asbestos in common areas. A single private house that is purely domestic is a different duty from a house converted to flats with shared halls. iComply scopes the survey type to the building you actually have — from Stockport SK2 across the North West.',
                'A management survey is the usual starting point during normal occupation. A refurbishment or demolition survey is more intrusive and is used before strip-out or opening-up. We will not sell one as if it were the other.',
                'Price on application. Tell us age, floor area, access and whether walls are coming open. Licensed removal, if required, is appointed separately. We do not invent a “landlord pack price”.',
            ],
            'sections' => [
                [
                    'h2' => 'Duty to manage — common parts versus a single let',
                    'p' => [
                        'The Control of Asbestos Regulations 2012 sit behind the duty to manage in non-domestic premises and in the common parts of many multi-occupied buildings. Inside a tenanted dwelling, access and type of survey still follow the planned works.',
                        'Not every rented terrace automatically needs a full commercial-style survey. Duty depends on the building. Describe the property and we will say what is proportionate.',
                    ],
                ],
                [
                    'h2' => 'Sampling that belongs on a register',
                    'p' => [
                        'A sample is only useful if the location is recorded and the result goes into your management plan. We will not scrape random decorative coatings “just in case” without a reason. Do not post debris — uncontrolled sampling can spread fibres.',
                        'Analysis is arranged as part of the quote. This page does not name a certificate brand or laboratory badge we have not stated.',
                    ],
                ],
                [
                    'h2' => 'What this service is not',
                    'p' => [
                        'Licensed asbestos removal is by others. If the survey says removal is required, appoint a suitable licensed contractor. Re-inspections check condition of items already on a register; a first visit to an unknown building is usually a survey, not a ten-minute glance.',
                    ],
                ],
            ],
            'cta_line' => 'Building age, floor area, access and whether walls are coming open.',
            'quote_placeholder' => 'Postcode, age, common parts or single let, planned works, access…',
        ];
    }
    return null;
}

function waterAsbestosAreaIntro(string $slug, string $area): string
{
    $p = function_exists('area_profile') ? area_profile($area) : ['districts' => $area, 'stock' => 'local property', 'travel' => 'from our Stockport base', 'focus' => 'local compliance'];
    if ($slug === 'legionella-risk-assessment') {
        return "Landlords and agents in {$area} ({$p['districts']}) typically ask us to document Legionella risk on {$p['stock']}. We quote POA after we know whether the property has stored water, little-used outlets or shared plant — a combi-fed house is not the same job as a tanked block. Travel is {$p['travel']}. Local focus: {$p['focus']}. This is a scoped assessment for your file, not a published per-town fee or an invented annual test.";
    }
    if ($slug === 'asbestos-survey') {
        return "In {$area} ({$p['districts']}) stock includes {$p['stock']}. Landlord asbestos work here is scoped to occupation versus refurbishment — common parts and conversions are a different duty from a purely domestic terrace. Quotes are POA. Travel is {$p['travel']}. Local focus: {$p['focus']}. Licensed removal, if required, is by others.";
    }
    return '';
}

/**
 * SEO outline used for keyword hubs + area landings (marketing briefs were not
 * mounted on disk; headings follow HSE L8 / HSG274 and CAR 2012 landlord duties).
 * No invented prices, certificates, reviews or accreditations.
 *
 * @return array<string,mixed>
 */
function waterAsbestosSeoOutline(): array
{
    return [
        'legionella' => [
            'primary' => [
                'legionella-risk-assessment',
                'landlord-legionella-risk-assessment',
                'legionella-testing',
                'water-hygiene-testing',
            ],
            'secondary' => [
                'legionella-water-testing',
                'legionnaires-disease-risk-assessment',
                'commercial-legionella-risk-assessment',
                'water-risk-assessment',
            ],
            'quality_hub' => 'legionella-landlords',
            'resource' => 'legionella-risk-assessment',
            'service' => 'legionella-risk-assessment',
            'h1_family' => 'Legionella risk assessment for landlords',
            'intent' => 'Dutyholder assessment first; sampling only when justified; POA; Stockport + full area set.',
        ],
        'asbestos' => [
            'primary' => [
                'asbestos-survey',
                'landlord-asbestos-survey',
                'asbestos-management-survey',
                'asbestos-testing',
            ],
            'secondary' => [
                'asbestos-refurbishment-survey',
                'commercial-asbestos-survey',
                'asbestos-sample-testing',
                'asbestos-inspection',
            ],
            'quality_hub' => 'asbestos-landlords',
            'resource' => 'asbestos-survey',
            'service' => 'asbestos-survey',
            'h1_family' => 'Asbestos survey for landlords',
            'intent' => 'CAR 2012 duty to manage; management vs refurbishment; no licensed removal claim; POA.',
        ],
        'cta' => ['/contact', 'tel:07517806082', 'whatsapp'],
        'exclude' => ['hmo-package-landings'],
    ];
}
