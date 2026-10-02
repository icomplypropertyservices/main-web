<?php
/**
 * Fourteen fortnight resource drafts (Guides landlord topics, Guides fire and commercial topics).
 * Educational guides — not HMO package landings, not keyword-matrix pages.
 */
declare(strict_types=1);

/**
 * @return array<string,array<string,mixed>>
 */
function wave1FortnightGuides(): array
{
    return [
        'gas-safety-certificate-landlords' => [
            'day' => 1, 'batch' => 'A', 'tag' => 'Gas',
            'cardTitle' => 'Gas safety certificate for landlords',
            'blurb' => 'What a CP12 / landlord gas safety record covers. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not issue them.',
            'pageTitle' => 'Landlord gas safety certificate (CP12) | North West',
            'metaDesc' => 'Plain-English guide to landlord gas safety records (often called CP12). Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not issue them.',
            'metaKeywords' => 'landlord gas safety certificate, CP12, CP44, gas safety record landlords North West, Stockport gas safety',
            'ogImage' => '/assets/images/services/gas-systems.jpg',
            'kicker' => 'Day 1 · Gas · Resource guide',
            'crumb' => 'Gas safety certificate',
            'h1' => 'Landlord gas safety certificate',
            'h1Accent' => 'what the record actually is',
            'lede' => 'A landlord gas safety record is the document agents and tenants ask for when gas appliances or flues are present. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not issue that record.',
            'formService' => 'Non-gas compliance',
            'formHeading' => 'Request a non-gas compliance quote',
            'formIntro' => 'iComply does not quote gas work or CP12 visits. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. Non-gas compliance is POA.',
            'formPlaceholder' => 'Postcode, appliance count, flue type, occupied or void…',
            'sections' => [
                [
                    'h2' => 'What the record is for',
                    'p' => [
                        'Where gas appliances or flues are present, private landlords in Great Britain generally need a gas safety check by an engineer, with a **gas safety certificate (CP12)** and a written record for the tenancy file. People still say **CP12**; the important part is a current record that matches the appliances on site.',
                        'It is not the same as a repair invoice or a manufacturer warranty stamp. The engineer records the appliances, the checks completed, and whether each appliance was safe to use at the time of the visit.',
                    ],
                ],
                [
                    'h2' => 'What is typically checked',
                    'p' => ['The visit usually covers the gas appliances and flues that serve the let. Exact scope depends on what is installed.'],
                    'ul' => [
                        'Boilers, gas fires, cookers and other appliances listed on the record',
                        'Flues and ventilation that those appliances rely on',
                        'Visible pipework and tightness / operating checks the engineer records on the day',
                        'A written record issued for the landlord or agent file',
                    ],
                    'note' => 'If an appliance is unsafe, the record should say so. iComply does not carry out the check and does not rewrite outcomes. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.',
                ],
                [
                    'h2' => 'Timing landlords actually use',
                    'p' => [
                        'The usual rhythm in England is **at least every 12 months** while gas remains in the property, with a copy available to tenants. New tenancies often need a current record before keys are released.',
                        'Scotland, Wales and Northern Ireland have their own frameworks — check the nation you let in. This page is high-level England guidance only.',
                    ],
                ],
                [
                    'h2' => 'How to book from Stockport',
                    'p' => [
                        'iComply works from Offerton SK2 on non-gas compliance only. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not attend to issue a CP12. Send non-gas enquiries via [contact](/contact).',
                        'Related reading: [landlord compliance checklist](/pages/resources/landlord-compliance-checklist) and [gas systems](/pages/services/gas-systems).',
                    ],
                ],
            ],
            'faqs' => [
                ['q' => 'Is a boiler service the same as a landlord gas safety record?', 'a' => 'No. A service is maintenance. The landlord record is the safety check and written outcome for the tenancy file. Many landlords book both on the same visit when the appliance is due.'],
                ['q' => 'Do you publish a CP12 price list?', 'a' => 'No. Appliance count, flues and access change the job. Ask for a written quote after you send the property details.'],
            ],
        ],
        'fire-risk-assessment-guide' => [
            'day' => 2, 'batch' => 'A', 'tag' => 'Fire',
            'cardTitle' => 'Fire risk assessments explained',
            'blurb' => 'What a suitable and sufficient FRA looks like, who typically needs one, and how the action plan is used.',
            'pageTitle' => 'Fire Risk Assessment Guide | Landlords & Commercial',
            'metaDesc' => 'High-level fire risk assessment guide for UK rented and commercial premises — what a suitable and sufficient FRA covers, action plans, and how to request a quote in the North West.',
            'metaKeywords' => 'fire risk assessment guide, FRA landlords, commercial fire risk assessment North West, Stockport FRA',
            'ogImage' => '/assets/images/services/fire-risk-assessments.jpg',
            'kicker' => 'Day 2 · Fire · Resource guide',
            'crumb' => 'Fire risk assessment',
            'h1' => 'Fire risk assessments',
            'h1Accent' => 'written findings, not a tick sheet',
            'lede' => 'A fire risk assessment is a structured look at who is at risk, what could start a fire, and which precautions are already in place — then a prioritised action plan.',
            'formService' => 'Fire risk assessment',
            'formHeading' => 'Request an FRA quote',
            'formIntro' => 'Storeys, use, sleeping risk and existing alarms all change the scope. We quote after we understand the building.',
            'formPlaceholder' => 'Address, storeys, use (let / office / care), existing FRA date…',
            'sections' => [
                [
                    'h2' => 'What “suitable and sufficient” means in practice',
                    'p' => [
                        'The phrase is from fire-safety law, not a marketing badge. A useful FRA describes the premises, the people who use them (including anyone who may need help to escape), ignition sources, and the precautions already installed.',
                        'It should end with actions you can actually schedule — not a photocopy of a single-let checklist pasted onto a converted terrace.',
                    ],
                ],
                [
                    'h2' => 'Who typically commissions one',
                    'ul' => [
                        'Landlords and agents of houses in multiple occupation and blocks with common parts',
                        'Employers and facilities teams for offices, shops and workplaces',
                        'Care and supported-living operators where sleeping risk and staffing change the plan',
                    ],
                    'p' => ['Single-family lets still have fire duties (alarms, escape, furniture) but the written FRA is most often requested for shared or commercial buildings. Confirm the duty that applies to your tenure.'],
                ],
                [
                    'h2' => 'What happens after the visit',
                    'p' => [
                        'You receive a written assessment and an action list. Some items are management (keep routes clear); others are physical (detection, emergency lighting, [fire doors](/pages/services/fire-doors)).',
                        'We can quote the follow-on work we already deliver — [fire alarms](/pages/services/fire-alarms), [emergency lighting](/pages/services/emergency-lighting), extinguishers — without pretending the FRA itself grants a licence.',
                    ],
                ],
                [
                    'h2' => 'Book from the North West',
                    'p' => ['Send the address and use via [contact](/contact). Related: [FRA service](/pages/services/fire-risk-assessments) and the [landlord checklist](/pages/resources/landlord-compliance-checklist).'],
                ],
            ],
            'faqs' => [
                ['q' => 'How often should an FRA be reviewed?', 'a' => 'Review when the building, occupancy or precautions change, and at a sensible interval even if nothing obvious changed. There is no single printed “every X years” figure that fits every premises.'],
                ['q' => 'Is this legal advice?', 'a' => 'No. It is practical inspection and documentation. Licensing and enforcement sit with the relevant authority.'],
            ],
        ],
        'smoke-and-co-alarms' => [
            'day' => 3, 'batch' => 'A', 'tag' => 'Alarms',
            'cardTitle' => 'Smoke and carbon monoxide alarms',
            'blurb' => 'Landlord duties for smoke and CO alarms in England, testing on the day of let, and when a wider fire system is needed.',
            'pageTitle' => 'Smoke & CO Alarms for Landlords | England Guide',
            'metaDesc' => 'Landlord smoke and carbon monoxide alarm duties in England — typical room coverage, testing on the day of let, and when a wider fire-alarm system is the right conversation.',
            'metaKeywords' => 'smoke alarms landlords, carbon monoxide alarms rented property, CO alarm regulations England, landlord smoke alarm duties',
            'ogImage' => '/assets/images/services/smoke-co-alarms.jpg',
            'kicker' => 'Day 3 · Alarms · Resource guide',
            'crumb' => 'Smoke and CO alarms',
            'h1' => 'Smoke and CO alarms',
            'h1Accent' => 'for rented homes in England',
            'lede' => 'Most private lets in England need working smoke alarms and, where there is a fixed combustion appliance, carbon monoxide alarms — tested on the day a tenancy starts.',
            'formService' => 'Smoke and CO alarms',
            'formHeading' => 'Request an alarm quote',
            'formIntro' => 'Tell us storeys, existing detectors and whether you need a simple domestic set or a wider fire system.',
            'formPlaceholder' => 'Storeys, rooms, existing alarms, gas/solid-fuel appliances…',
            'sections' => [
                [
                    'h2' => 'The duty in plain English',
                    'p' => [
                        'England’s smoke and carbon monoxide alarm rules for private landlords are about **working alarms in the right places**, not a particular brand. Typically: smoke coverage on each storey used as living accommodation, and CO alarms in rooms with a fixed combustion appliance (other than a gas cooker, under the current England rules).',
                        'Rules differ in Scotland, Wales and Northern Ireland. Always check the nation you let in.',
                    ],
                ],
                [
                    'h2' => 'Testing on the day of let',
                    'p' => [
                        'Landlords are generally expected to check alarms on the day the tenancy starts and to repair or replace them once a tenant reports a fault. Keep a simple note of that check in the tenancy file.',
                        'Tenants should still test weekly. The landlord duty does not disappear because a tenant forgot.',
                    ],
                ],
                [
                    'h2' => 'When a Grade D set is not enough',
                    'p' => [
                        'Shared houses, inner rooms and licence conditions often need a designed [fire alarm system](/pages/services/fire-alarms) rather than a handful of domestic detectors. An [FRA](/pages/services/fire-risk-assessments) is the honest way to decide — not a guess from a product catalogue.',
                    ],
                ],
                [
                    'h2' => 'How we can help',
                    'p' => ['We supply and position alarms, replace life-expired units, and quote a wider system when the house needs one. Start at [smoke and CO alarms](/pages/services/smoke-co-alarms) or [contact](/contact).'],
                ],
            ],
            'faqs' => [
                ['q' => 'Do I need a CO alarm next to a gas cooker?', 'a' => 'England’s current private-rented rules treat gas cookers differently from other fixed combustion appliances. Confirm the latest wording for your property type before you buy.'],
                ['q' => 'How long do alarms last?', 'a' => 'Most sealed units have a printed end-of-life date, commonly around 10 years. Replace when that date arrives or when the unit fails a test.'],
            ],
        ],
        'pat-testing-guide' => [
            'day' => 4, 'batch' => 'A', 'tag' => 'Electrical',
            'cardTitle' => 'PAT testing for lets and workplaces',
            'blurb' => 'What portable appliance testing is, who typically asks for it, and how it differs from an EICR.',
            'pageTitle' => 'PAT Testing Guide | Landlords & Workplaces',
            'metaDesc' => 'PAT testing explained for landlords, agents and workplaces — what portable appliance testing covers, how it differs from an EICR, and how to request a North West quote.',
            'metaKeywords' => 'PAT testing guide, portable appliance testing landlords, workplace PAT testing North West, PAT vs EICR',
            'ogImage' => '/assets/images/services/pat-testing.jpg',
            'kicker' => 'Day 4 · Electrical · Resource guide',
            'crumb' => 'PAT testing',
            'h1' => 'PAT testing',
            'h1Accent' => 'appliances, not the wiring',
            'lede' => 'Portable appliance testing looks at plug-in equipment. It does not replace an EICR of the fixed installation.',
            'formService' => 'PAT testing',
            'formHeading' => 'Request a PAT quote',
            'formIntro' => 'Approximate appliance count and site type (void, HMO, office) is enough to start a quote.',
            'formPlaceholder' => 'Site type, rough appliance count, access hours…',
            'sections' => [
                [
                    'h2' => 'What PAT covers',
                    'p' => [
                        'PAT is a combination of formal visual inspection and, where justified, electrical tests on portable and movable equipment — kettles, lamps, extension leads, office IT, landlord-supplied white goods.',
                        'It produces a record of items passed, failed or advised. Failed items should be taken out of service, not left with a green sticker from last year.',
                    ],
                ],
                [
                    'h2' => 'PAT is not an EICR',
                    'p' => [
                        'An [EICR](/pages/resources/eicr-guide) inspects the **fixed** installation: consumer units, circuits, sockets and lighting. PAT does not say those circuits are satisfactory.',
                        'Workplaces and furnished lets often need both conversations. Agents sometimes ask for PAT on landlord-owned appliances even when the law does not use the letters “PAT”.',
                    ],
                ],
                [
                    'h2' => 'How often?',
                    'p' => [
                        'There is no single statutory “every 12 months” for every toaster in England. Frequency is risk-based: construction sites and shared kitchens wear equipment harder than a locked spare-room lamp.',
                        'Insurers and corporate clients may set their own interval. We will not invent a legal annual duty that does not exist.',
                    ],
                ],
                [
                    'h2' => 'Book testing',
                    'p' => ['See [PAT testing](/pages/services/pat-testing) or send a count via [contact](/contact). Combined visits with an EICR are often the tidy option for voids.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Do tenants’ own appliances need PAT?', 'a' => 'Usually the landlord’s duty is about landlord-owned equipment and the installation. Workplace rules differ. Do not assume a tenant hairdryer is your test list.'],
                ['q' => 'Can you fail an item on visual inspection only?', 'a' => 'Yes. Damaged flexes, cracked plugs and crushed leads do not need a fancy tester to be unsafe.'],
            ],
        ],
        'epc-for-landlords' => [
            'day' => 5, 'batch' => 'A', 'tag' => 'Energy',
            'cardTitle' => 'EPCs for landlords',
            'blurb' => 'When a rented property needs an Energy Performance Certificate, how long it lasts, and what a rating does not promise.',
            'pageTitle' => 'EPC for Landlords | Ratings & Letability Guide',
            'metaDesc' => 'Energy Performance Certificates for UK landlords — when an EPC is needed to let, typical 10-year life, and why a rating is not a heating-system guarantee. Quote from Stockport.',
            'metaKeywords' => 'EPC landlords, energy performance certificate rental, landlord EPC North West, EPC letability',
            'ogImage' => '/assets/images/services/epc.jpg',
            'kicker' => 'Day 5 · Energy · Resource guide',
            'crumb' => 'EPC for landlords',
            'h1' => 'EPCs for landlords',
            'h1Accent' => 'a rating, not a boiler certificate',
            'lede' => 'An Energy Performance Certificate is a modelled rating of the building fabric and fixed services. It is not a gas safety record and it is not an EICR.',
            'formService' => 'EPC',
            'formHeading' => 'Request an EPC quote',
            'formIntro' => 'Domestic or non-domestic, floor area and access decide the visit. We quote after those facts.',
            'formPlaceholder' => 'Domestic or commercial, floor area, last EPC date if known…',
            'sections' => [
                [
                    'h2' => 'When landlords need one',
                    'p' => [
                        'In Great Britain you generally need a valid EPC to **market and let** a property (with limited exemptions). Agents will not list without one. Buy-to-let lenders and local licensing schemes often ask for a copy too.',
                        'A certificate usually lasts **10 years** unless the building is altered enough that the model is no longer true.',
                    ],
                ],
                [
                    'h2' => 'What the rating is — and is not',
                    'p' => [
                        'The band is calculated from insulation, heating, glazing and other fixed features. It does not prove the boiler is safe this year, and it does not replace [gas](/pages/services/gas-systems) or [electrical](/pages/services/electrical) safety records.',
                        'Minimum energy efficiency rules for privately rented homes in England and Wales have used band E as a floor, with exemptions. Confirm the current threshold before you assume a D or C is “enough forever”.',
                    ],
                ],
                [
                    'h2' => 'Domestic vs non-domestic',
                    'p' => [
                        'Houses and flats use the domestic methodology. Shops, offices and mixed-use buildings often need a non-domestic EPC. We will not force a domestic certificate onto a commercial unit to save a visit.',
                    ],
                ],
                [
                    'h2' => 'Book an assessment',
                    'p' => ['[EPC service](/pages/services/epc) or [contact](/contact). If you also need certificates for a void, say so — we can plan the same access window.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Will an EPC tell me my boiler is safe?', 'a' => 'No. Book a landlord gas safety record for that. The EPC only models energy performance.'],
                ['q' => 'Do you publish EPC fees online?', 'a' => 'No. Size, dwelling type and access change the job. Ask for a quote.'],
            ],
        ],
        'fire-door-inspection' => [
            'day' => 6, 'batch' => 'B', 'tag' => 'Fire doors',
            'cardTitle' => 'Fire door inspection guide',
            'blurb' => 'What a fire doorset is meant to do, common defects, and why a closer or seal is not a cosmetic extra.',
            'pageTitle' => 'Fire Door Inspection Guide | Seals, Closers & Gaps',
            'metaDesc' => 'Fire door inspection guide for landlords and FM teams — doorsets, intumescent seals, closers and common defects. Request a North West survey quote, no invented prices.',
            'metaKeywords' => 'fire door inspection, fire door survey, intumescent seals, fire door closer, fire doors North West',
            'ogImage' => '/assets/images/services/fire-doors.jpg',
            'kicker' => 'Day 6 · Fire doors · Resource guide',
            'crumb' => 'Fire door inspection',
            'h1' => 'Fire door inspection',
            'h1Accent' => 'the doorset, not just the leaf',
            'lede' => 'A fire door only works as a doorset: leaf, frame, seals, hinges, glazing and closer working together. A painted hollow-core door with a sticker is not that.',
            'formService' => 'Fire doors',
            'formHeading' => 'Request a fire-door quote',
            'formIntro' => 'Number of doors, location (flat entrance, stair, kitchen) and whether you need survey only or remedial work.',
            'formPlaceholder' => 'How many doors, building type, known defects…',
            'sections' => [
                [
                    'h2' => 'What inspectors actually look at',
                    'ul' => [
                        'Gaps at the head, jambs and threshold — oversized gaps leak smoke and fire',
                        'Intumescent strips and cold-smoke seals still present and continuous',
                        'Three suitable hinges, not decorative two-hinge hangings',
                        'A closer that fully latches the door from any angle, including with a seal drag',
                        'Glazing and letter plates that belong on a fire doorset',
                    ],
                ],
                [
                    'h2' => 'Common defects on rented stock',
                    'p' => [
                        'Tenant-fitted extra locks, planed meeting stiles, missing seals after a repaint, and closers wound so weak the door sits ajar. Each one can undo the rating the door was sold with.',
                        'An [FRA](/pages/services/fire-risk-assessments) should say which doors are fire doors. The survey then checks those doorsets against that intent.',
                    ],
                ],
                [
                    'h2' => 'Repair versus replace',
                    'p' => [
                        'Seals, closers and some ironmongery can be replaced. A warped leaf, the wrong core, or a frame that was never a fire frame usually means a new doorset. We will not sell a seal kit as a miracle fix.',
                    ],
                ],
                [
                    'h2' => 'Next step',
                    'p' => ['[Fire door services](/pages/services/fire-doors) or [contact](/contact). Mention if the FRA already listed the doors — it shortens the quote.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Can I plane a fire door that sticks?', 'a' => 'Often that destroys the lipping and the certified gaps. Get the doorset surveyed before anyone takes a plane to it.'],
                ['q' => 'Do all internal doors in a house need to be fire doors?', 'a' => 'No. The FRA and the layout decide. Escape routes, kitchens and protected stairs are the usual triggers.'],
            ],
        ],
        'fire-extinguisher-servicing' => [
            'day' => 7, 'batch' => 'B', 'tag' => 'Extinguishers',
            'cardTitle' => 'Fire extinguisher servicing',
            'blurb' => 'Why extinguishers need a competent annual service, what the engineer records, and when a unit should be replaced not restickered.',
            'pageTitle' => 'Fire Extinguisher Servicing | Annual Maintenance Guide',
            'metaDesc' => 'Fire extinguisher servicing explained — annual competent-person checks, types of unit, and when to replace rather than resticker. Quote for North West sites.',
            'metaKeywords' => 'fire extinguisher servicing, fire extinguisher maintenance, extinguisher certificate, commercial extinguishers North West',
            'ogImage' => '/assets/images/services/fire-extinguishers.jpg',
            'kicker' => 'Day 7 · Extinguishers · Resource guide',
            'crumb' => 'Extinguisher servicing',
            'h1' => 'Fire extinguisher servicing',
            'h1Accent' => 'annual checks, honest replacements',
            'lede' => 'Extinguishers are only useful if they still discharge and still match the risks on the floor. An annual service is how you know.',
            'formService' => 'Fire extinguishers',
            'formHeading' => 'Request an extinguisher quote',
            'formIntro' => 'Count the units and note the building use. We quote service, replacements or a first-fit after we see the list.',
            'formPlaceholder' => 'Number of extinguishers, site type, last service date if known…',
            'sections' => [
                [
                    'h2' => 'What the annual visit is for',
                    'p' => [
                        'A competent person inspects each unit: pressure, hose, safety pin, mounting, and whether the type still matches the hazard (water, foam, CO₂, powder, wet chemical).',
                        'You should receive a record of what was serviced, condemned or advised. A sticker without a report is a weak file.',
                    ],
                ],
                [
                    'h2' => 'Service is not a design',
                    'p' => [
                        'The [FRA](/pages/services/fire-risk-assessments) should say whether extinguishers are appropriate at all — some sleeping-risk buildings prefer escape over first-aid firefighting.',
                        'We will not fill a corridor with powder extinguishers just because a catalogue bundle exists.',
                    ],
                ],
                [
                    'h2' => 'When to replace',
                    'p' => [
                        'Corrosion, failed pressure, damaged hoses, missing parts, or a type that no longer matches the kitchen or electrical risk. Life-expired cylinders should leave the building, not get a fresh label.',
                    ],
                ],
                [
                    'h2' => 'Book servicing',
                    'p' => ['[Fire extinguishers](/pages/services/fire-extinguishers) or [contact](/contact). Same-day access with alarm or emergency-lighting visits is often the efficient option.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Do houses always need extinguishers?', 'a' => 'Not automatically. The FRA and the occupancy decide. Domestic lets more often need working alarms and a clear escape than a row of cylinders.'],
                ['q' => 'Do you sell a “certificate” as a product?', 'a' => 'You get a service record for the units we inspected. We do not sell a generic certificate with no visit.'],
            ],
        ],
        'kitchen-fire-suppression-guide' => [
            'day' => 8, 'batch' => 'B', 'tag' => 'Kitchens',
            'cardTitle' => 'Kitchen fire suppression',
            'blurb' => 'When a commercial kitchen needs a suppression system over the range, how it ties to the extract, and what a quote needs to know.',
            'pageTitle' => 'Kitchen Fire Suppression Guide | Commercial Catering',
            'metaDesc' => 'Commercial kitchen fire suppression explained — range protection, extract links, and what we need to quote. North West catering sites; no invented package prices.',
            'metaKeywords' => 'kitchen fire suppression, commercial kitchen fire system, range hood suppression, catering fire safety North West',
            'ogImage' => '/assets/images/services/kitchen-fire-suppression.jpg',
            'kicker' => 'Day 8 · Kitchens · Resource guide',
            'crumb' => 'Kitchen fire suppression',
            'h1' => 'Kitchen fire suppression',
            'h1Accent' => 'the range, not a hallway cylinder',
            'lede' => 'A wet-chemical or designed kitchen system protects the cooking line and often the extract. It is a different product from a portable extinguisher on a hook.',
            'formService' => 'Kitchen fire suppression',
            'formHeading' => 'Request a kitchen-system quote',
            'formIntro' => 'Photos of the canopy, number of appliances and whether the extract already has a system save a wasted visit.',
            'formPlaceholder' => 'Kitchen type, canopy length, existing system brand if any…',
            'sections' => [
                [
                    'h2' => 'When kitchens need more than an extinguisher',
                    'p' => [
                        'Deep-fat fryers, heavily used ranges and insurance conditions on catering sites often require a fixed suppression system over the cooking equipment, with nozzles aimed at the appliances and plenum.',
                        'The [FRA](/pages/services/fire-risk-assessments) and the insurer, not a sales leaflet, should drive that decision.',
                    ],
                ],
                [
                    'h2' => 'How it links to the building',
                    'p' => [
                        'A proper install considers gas shut-off, electric isolation, and whether the extract continues to run or shuts down as designed. Painting a cylinder silver and calling it “commercial kitchen ready” is not that.',
                    ],
                ],
                [
                    'h2' => 'Servicing',
                    'p' => [
                        'These systems need competent inspection on the interval the manufacturer and the risk assessment set. We service and quote replacements; we do not invent a universal monthly legal duty.',
                    ],
                ],
                [
                    'h2' => 'Talk to us',
                    'p' => ['[Kitchen fire suppression](/pages/services/kitchen-fire-suppression) or [contact](/contact). Care homes and pubs with small catering lines should say so — the design is not the same as a hotel production kitchen.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Will a wet-chemical extinguisher replace a canopy system?', 'a' => 'Sometimes the FRA accepts portable first-aid cover on a very small risk. A busy fryer line usually will not. We will not pretend they are interchangeable.'],
                ['q' => 'Do you price per nozzle online?', 'a' => 'No. Canopy length, appliance mix and shut-off interfaces change the job.'],
            ],
        ],
        'let-ready-void-checklist' => [
            'day' => 9, 'batch' => 'B', 'tag' => 'Landlords',
            'cardTitle' => 'Let-ready void checklist',
            'blurb' => 'Certificates and safety checks that commonly sit in a re-let file — without pretending one pack fits every house.',
            'pageTitle' => 'Let-Ready Void Checklist | Landlord Certificates',
            'metaDesc' => 'Practical let-ready void checklist for UK landlords — EICR, gas, smoke/CO, EPC and optional extras. High-level guidance; request a scoped quote, no catalogue prices.',
            'metaKeywords' => 'let ready checklist, void property certificates, landlord re-let compliance, void EICR gas smoke alarms',
            'ogImage' => '/assets/images/services/landlord-compliance.jpg',
            'kicker' => 'Day 9 · Landlords · Resource guide',
            'crumb' => 'Let-ready checklist',
            'h1' => 'Let-ready void checklist',
            'h1Accent' => 'the file before the keys',
            'lede' => 'Agents usually will not release keys until the safety file is current. This is a practical prompt list — not a legal minimum for every tenure.',
            'formService' => 'Landlord compliance — void / let-ready',
            'formHeading' => 'Request a void-pack quote',
            'formIntro' => 'List the certificates you already hold and the tenancy start date. We quote only the work that is actually due.',
            'formPlaceholder' => 'Address, last EICR / gas / EPC dates, furnished or unfurnished…',
            'sections' => [
                [
                    'h2' => 'Certificates agents ask for most',
                    'ul' => [
                        '[EICR](/pages/resources/eicr-guide) still in date (England private rented: commonly within 5 years, and often at change of tenancy)',
                        '[Landlord gas safety record](/pages/resources/gas-safety-certificate-landlords) if gas is present',
                        'Working [smoke and CO alarms](/pages/resources/smoke-and-co-alarms) tested on the day of let',
                        'Valid [EPC](/pages/resources/epc-for-landlords) if you are marketing the let',
                    ],
                    'note' => 'HMO licence conditions can add FRA, emergency lighting and alarm servicing. That is a different conversation from a single-family void — and not a package landing on this page.',
                ],
                [
                    'h2' => 'Often useful, not always mandatory',
                    'ul' => [
                        '[PAT](/pages/resources/pat-testing-guide) on landlord-owned appliances in a furnished let',
                        'Boiler service alongside the gas safety record',
                        'Legionella risk awareness for stored water — confirm what your agent actually wants',
                    ],
                ],
                [
                    'h2' => 'The house itself',
                    'p' => [
                        'A certificate file does not fix a broken closer, a blocked escape or a missing banister. Walk the property. Note damp, locks, and anything a new tenant will report in week one.',
                    ],
                ],
                [
                    'h2' => 'One access visit',
                    'p' => [
                        'If several items are due, say so on [contact](/contact). Combined attendance is usually kinder to the diary than three separate engineer days. See also the [landlord checklist](/pages/resources/landlord-compliance-checklist) and [Let Ready package](/pages/packages/let-ready).',
                    ],
                ],
            ],
            'faqs' => [
                ['q' => 'Is this the Let Ready package page?', 'a' => 'No. This is an educational checklist. The existing Let Ready package landing stays where it is. We will not invent a price for either.'],
                ['q' => 'Do you guarantee the council will accept the file?', 'a' => 'No. We issue the records for the work we completed. Licensing and enforcement sit with the authority.'],
            ],
        ],
        'commercial-fire-safety-basics' => [
            'day' => 10, 'batch' => 'B', 'tag' => 'Commercial',
            'cardTitle' => 'Commercial fire safety basics',
            'blurb' => 'A facilities-manager view of FRA, alarms, emergency lighting, doors and extinguishers — without a fake accreditation wall.',
            'pageTitle' => 'Commercial Fire Safety Basics | FM Guide North West',
            'metaDesc' => 'Commercial fire safety basics for FM and employers in the North West — FRA, alarms, emergency lighting, doors and extinguishers. Practical guidance; quote after scope.',
            'metaKeywords' => 'commercial fire safety, workplace fire safety North West, FM fire alarms emergency lighting, office FRA',
            'ogImage' => '/assets/images/services/fire-alarms.jpg',
            'kicker' => 'Day 10 · Commercial · Resource guide',
            'crumb' => 'Commercial fire safety',
            'h1' => 'Commercial fire safety',
            'h1Accent' => 'the systems FM actually books',
            'lede' => 'Most workplace fire programmes are a small set of repeating jobs: keep the FRA current, service the alarm, test the lighting, look after the doors.',
            'formService' => 'Commercial fire safety',
            'formHeading' => 'Request a commercial fire quote',
            'formIntro' => 'Site use, existing kit brands and last service dates are enough to start. No published bundle price.',
            'formPlaceholder' => 'Office / warehouse / retail, alarm type, last FRA date…',
            'sections' => [
                [
                    'h2' => 'Start with the assessment',
                    'p' => [
                        'A workplace [FRA](/pages/services/fire-risk-assessments) should drive the rest. Buying devices first and assessing later is how sites end up with the wrong category of alarm or lighting that does not cover the escape.',
                    ],
                ],
                [
                    'h2' => 'The usual maintenance set',
                    'ul' => [
                        '[Fire alarm servicing](/pages/resources/fire-alarm-servicing) on a BS 5839-informed interval',
                        '[Emergency lighting tests](/pages/resources/emergency-lighting-testing) — monthly function, annual duration',
                        '[Fire door](/pages/resources/fire-door-inspection) condition on escape routes',
                        '[Extinguishers](/pages/resources/fire-extinguisher-servicing) if the FRA still wants them',
                    ],
                ],
                [
                    'h2' => 'Logbooks beat memory',
                    'p' => [
                        'Insurers and enforcing officers ask for records. A shared spreadsheet is better than “the previous FM knew”. We issue visit records for the work we do; we do not invent a branded accreditation plaque.',
                    ],
                ],
                [
                    'h2' => 'North West sites',
                    'p' => ['From the Stockport yard we attend offices, industrial units and mixed-use blocks. [Commercial services](/pages/commercial) or [contact](/contact).'],
                ],
            ],
            'faqs' => [
                ['q' => 'Do you offer a named “Fire Ready” package here?', 'a' => 'The existing Fire Ready package page is unchanged. This article is the educational overview. Ask for a scoped quote either way.'],
                ['q' => 'Can you take over an unknown panel?', 'a' => 'Usually yes after we see the panel, cause-and-effect and the last service record. Unknown kit is why we do not publish a one-line price.'],
            ],
        ],
        'care-home-fire-and-nurse-call' => [
            'day' => 11, 'batch' => 'B', 'tag' => 'Care',
            'cardTitle' => 'Care home fire and nurse call',
            'blurb' => 'Why fire alarms and nurse-call systems have to work as one operation in care settings — without claiming a CQC badge we do not issue.',
            'pageTitle' => 'Care Home Fire Alarms & Nurse Call | North West',
            'metaDesc' => 'Care home fire alarms and nurse-call systems — how they work together, what a quote needs, and honest limits. Stockport-based contractor; not a regulator and not a CQC inspection.',
            'metaKeywords' => 'care home fire alarms, nurse call systems, care home emergency lighting, care fire safety North West',
            'ogImage' => '/assets/images/services/nurse-call.jpg',
            'kicker' => 'Day 11 · Care · Resource guide',
            'crumb' => 'Care home fire and nurse call',
            'h1' => 'Care home fire and nurse call',
            'h1Accent' => 'two systems, one night shift',
            'lede' => 'In care, the fire system and the nurse-call system are how staff know where to go. They should be designed and maintained with that night shift in mind.',
            'formService' => 'Care home fire / nurse call',
            'formHeading' => 'Request a care-site quote',
            'formIntro' => 'Beds, existing kit brands and whether you need fire, nurse call or both. We are not the regulator.',
            'formPlaceholder' => 'Home size, fire panel brand, nurse-call brand, last service dates…',
            'sections' => [
                [
                    'h2' => 'Why the two conversations travel together',
                    'p' => [
                        'A fire alarm tells you there is a device in alarm. A [nurse-call](/pages/services/nurse-call) system tells staff which room needs a person. Evacuation and stay-put strategies only work if staff can locate residents and visitors.',
                        'We do not issue CQC ratings and we do not pretend a service visit is an inspection by the regulator.',
                    ],
                ],
                [
                    'h2' => 'Fire side',
                    'p' => [
                        'Care premises usually need a robust [fire alarm](/pages/services/fire-alarms), [emergency lighting](/pages/services/emergency-lighting) on escape and staff routes, and an FRA that understands mobility and staffing. Detection in bedrooms is not a domestic Grade D conversation.',
                    ],
                ],
                [
                    'h2' => 'Nurse-call side',
                    'p' => [
                        'Pear-pushes, over-door lights, staff pagers or DECT, and logging. Retrofit in a live home needs a phasing plan — not a weekend rip-out with no temporary cover.',
                    ],
                ],
                [
                    'h2' => 'How to start',
                    'p' => ['Read [care homes](/pages/care-homes) or send kit brands via [contact](/contact). Photos of the panel and a nurse-call printer ticket help.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Are you a CQC inspector?', 'a' => 'No. We install, maintain and document systems. Regulatory inspection is a different body.'],
                ['q' => 'Can you work in an occupied home?', 'a' => 'Yes, with a method that keeps cover in place. That is why we quote after we understand occupancy, not from a web form alone.'],
            ],
        ],
        'electrical-safety-rented-homes' => [
            'day' => 12, 'batch' => 'B', 'tag' => 'Electrical',
            'cardTitle' => 'Electrical safety in rented homes',
            'blurb' => 'How the EICR sits in the tenancy file, what C1–C3 codes mean for a re-let, and what the report does not cover.',
            'pageTitle' => 'Electrical Safety in Rented Homes | EICR Duties',
            'metaDesc' => 'Electrical safety in privately rented homes — how the EICR fits the tenancy file, what C1–C3 codes mean, and what the report does not cover. North West landlords.',
            'metaKeywords' => 'electrical safety rented homes, landlord EICR duties, C1 C2 C3 EICR, private rented electrical regulations',
            'ogImage' => '/assets/images/services/electrical.jpg',
            'kicker' => 'Day 12 · Electrical · Resource guide',
            'crumb' => 'Electrical safety rented homes',
            'h1' => 'Electrical safety',
            'h1Accent' => 'in rented homes',
            'lede' => 'The EICR is the main written evidence that the fixed installation was inspected. It is not PAT, and it is not a promise that nothing will fail next winter.',
            'formService' => 'Electrical — EICR',
            'formHeading' => 'Request an EICR quote',
            'formIntro' => 'Consumer-unit count, property type and whether the house is occupied change the visit length.',
            'formPlaceholder' => 'Flats / house / HMO, number of boards, last EICR date…',
            'sections' => [
                [
                    'h2' => 'How this sits next to the EICR guide',
                    'p' => [
                        'Our [EICR guide](/pages/resources/eicr-guide) explains the report itself. This page is about the **tenancy file**: when agents ask for a satisfactory report, what unsatisfactory means for a move-in date, and what you still own after the engineer leaves.',
                    ],
                ],
                [
                    'h2' => 'Codes in a re-let conversation',
                    'ul' => [
                        '**C1** — danger present; make safe before anyone relies on that circuit',
                        '**C2** — potentially dangerous; usually treated as needing remedial work for a satisfactory outcome',
                        '**C3** — improvement recommended; not automatically a failed let, but do not ignore a pile of them',
                        '**FI** — further investigation; the report is incomplete until that work is done',
                    ],
                ],
                [
                    'h2' => 'England private-rented rhythm',
                    'p' => [
                        'A satisfactory EICR at least every five years is the familiar England private-rented pattern, plus a new report when the previous one expires or when the installation changes. Other nations differ. This is not legal advice.',
                    ],
                ],
                [
                    'h2' => 'What the report does not do',
                    'p' => [
                        'It does not test every tenant appliance ([PAT](/pages/resources/pat-testing-guide)). It does not rate energy efficiency ([EPC](/pages/resources/epc-for-landlords)). It does not make a dangerous DIY alteration safe until you book the remedial.',
                        'Book via [electrical services](/pages/services/electrical) or [contact](/contact).',
                    ],
                ],
            ],
            'faqs' => [
                ['q' => 'Can I let with an unsatisfactory EICR?', 'a' => 'Agents and many authorities will not treat that as a complete file. Remedial work and a satisfactory outcome (or a clear written position) is the usual path. We will not invent a workaround.'],
                ['q' => 'Do you guarantee five years without faults?', 'a' => 'No. The report describes condition on the day. Tenant alterations and wear continue after we leave.'],
            ],
        ],
        'booking-compliance-certificates' => [
            'day' => 13, 'batch' => 'B', 'tag' => 'Process',
            'cardTitle' => 'How to book compliance certificates',
            'blurb' => 'What to send so a local contractor can quote in one pass — access, certificate dates, and who will be on site.',
            'pageTitle' => 'How to Book Compliance Certificates | North West',
            'metaDesc' => 'How to book landlord and commercial compliance certificates with a local North West contractor — what to send, how quotes work, and how to reach iComply from Stockport SK2.',
            'metaKeywords' => 'book EICR, book gas safety certificate, book fire risk assessment, compliance certificates Stockport, quote property compliance',
            'ogImage' => '/assets/images/services/compliance-consultancy.jpg',
            'kicker' => 'Day 13 · Process · Resource guide',
            'crumb' => 'How to book',
            'h1' => 'How to book',
            'h1Accent' => 'certificates without a runaround',
            'lede' => 'The fastest quotes are boring: address, property type, what is due, and who holds the keys. That is the whole secret.',
            'formService' => 'Multi-service compliance booking',
            'formHeading' => 'Send the booking details',
            'formIntro' => 'Use this form or call. We reply with a written quote after scope — not a surprise invoice.',
            'formPlaceholder' => 'Address, jobs needed, access contact, tenancy or site constraints…',
            'sections' => [
                [
                    'h2' => 'What to send in the first message',
                    'ul' => [
                        'Full address and postcode',
                        'House, flat, HMO, office, care or industrial',
                        'Jobs: EICR, gas record, FRA, alarms, lighting, PAT, EPC, doors — list only what you need',
                        'Last certificate dates if you have them',
                        'Occupied, void, or restricted access hours',
                        'Who meets the engineer',
                    ],
                ],
                [
                    'h2' => 'How we quote',
                    'p' => [
                        'We do not publish a price grid. Two three-bed terraces are not the same job if one has two consumer units and a loft conversion. You get a written figure after scope.',
                        'If we cannot do the work, we will say so. We will not invent an accreditation to win the enquiry.',
                    ],
                ],
                [
                    'h2' => 'After you accept',
                    'p' => [
                        'We agree a date, attend, and issue the records for the work completed. Remedials are quoted separately unless you already asked us to include them.',
                    ],
                ],
                [
                    'h2' => 'Contact routes',
                    'p' => [
                        '[Contact form](/contact) · phone ' . PHONE . ' · WhatsApp · yard at 17 Woodlands Park Road, Offerton, Stockport SK2 5DE.',
                        'Related hubs: [landlord certificates](/pages/landlord-certificates) and [resources](/pages/resources).',
                    ],
                ],
            ],
            'faqs' => [
                ['q' => 'Can I book on WhatsApp?', 'a' => 'Yes. Send the same details you would put on the form. We still confirm the quote in writing.'],
                ['q' => 'Do you need the old certificates?', 'a' => 'They help. If they are lost, say so — we start from the property as found.'],
            ],
        ],
        'greater-manchester-property-compliance' => [
            'day' => 14, 'batch' => 'B', 'tag' => 'Local',
            'cardTitle' => 'Compliance across Greater Manchester',
            'blurb' => 'How a Stockport SK2 contractor covers GM lets and workplaces — without a doorway page for every postcode.',
            'pageTitle' => 'Property Compliance Greater Manchester | From Stockport SK2',
            'metaDesc' => 'Property compliance across Greater Manchester from a Stockport SK2 yard — EICR, gas, FRA, fire systems and landlord files. One quality local guide, not a thin page per town.',
            'metaKeywords' => 'property compliance Greater Manchester, landlord certificates Manchester Stockport, EICR FRA gas North West',
            'ogImage' => '/assets/images/services/building-maintenance.jpg',
            'kicker' => 'Day 14 · Local · Resource guide',
            'crumb' => 'Greater Manchester',
            'h1' => 'Greater Manchester',
            'h1Accent' => 'compliance from a Stockport yard',
            'lede' => 'We are based in Offerton SK2 and work across Greater Manchester and the wider North West. This is one honest local guide — not a cloned page for every postcode.',
            'formService' => 'North West compliance visit',
            'formHeading' => 'Request a GM / North West quote',
            'formIntro' => 'Postcode and job type are enough to start. If it is too far for the diary, we will say so.',
            'formPlaceholder' => 'Postcode, property type, certificates needed…',
            'sections' => [
                [
                    'h2' => 'Where we actually start from',
                    'p' => [
                        'The yard is **17 Woodlands Park Road, Offerton, Stockport SK2 5DE**. Stockport, the Heatons, SK1–SK3 and neighbouring GM boroughs are the everyday run. Manchester city lets, Salford fringes and the wider conurbation are routine when the job is clear.',
                    ],
                ],
                [
                    'h2' => 'What GM landlords usually book',
                    'ul' => [
                        'EICR and remedials on terrace and conversion stock',
                        'Annual gas safety records',
                        'FRA plus alarms or lighting when the house is shared or the block has common parts',
                        'Void packs before a new tenancy',
                    ],
                ],
                [
                    'h2' => 'What we will not do',
                    'p' => [
                        'We will not publish 200 thin “EICR in [town]” doorway articles. Town-level service pages already exist where the site has a real area pattern. This article plus the [Stockport](/pages/stockport-property-compliance) and [Manchester](/pages/manchester-property-compliance) hubs are the quality local set for wave 1.',
                    ],
                ],
                [
                    'h2' => 'Start a job',
                    'p' => ['[Areas we cover](/pages/areas) · [contact](/contact) · ' . PHONE . '.'],
                ],
            ],
            'faqs' => [
                ['q' => 'Do you only work in Stockport?', 'a' => 'No. SK2 is the yard. Greater Manchester and the wider North West are in scope when we can resource the visit.'],
                ['q' => 'Will you create a page for my village?', 'a' => 'Not as a thin doorway. If you need work, send the postcode — the certificate does not care about a unique landing URL.'],
            ],
        ],
    ];
}
