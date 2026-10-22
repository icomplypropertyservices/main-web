"""Sector modules (mainly AOV). {thing} = product label."""

SECTORS = {
    "apartment-block": {
        "label": "apartment blocks",
        "paras": [
            "In apartment blocks, the {thing} protects the common stair and corridors that residents use to escape and the fire service uses to get in. Most purpose-built blocks follow a stay-put or delayed-evacuation strategy, so keeping the common parts clear of smoke for longer is central to the design.",
            "Access to the common parts is usually through the managing agent. Residents need notice of tests that sound alarms or open vents, and works in corridors are planned so they do not block escape routes.",
            "Records for apartment blocks go to the responsible person — often a freeholder, a residents' management company or a right-to-manage company — via the managing agent. iComply writes them so they can be shared without explanation.",
            "Defects in apartment blocks are often reported by residents first: a vent stuck open, a panel beeping, a draught from a vent that will not close. Those reports are valuable and are cross-checked on site.",
        ],
        "faqs": [
            ("Who instructs {thing} work in an apartment block?", "Usually the managing agent on behalf of the freeholder, residents' management company or right-to-manage company."),
        ],
    },
    "flats": {
        "label": "blocks of flats",
        "paras": [
            "Blocks of flats are where most AOVs in the UK are found. The common stair and corridors are the escape route, and a {thing} keeps them clearer of smoke for longer so residents and firefighters can use them.",
            "Approved Document B guidance for blocks of flats sets out common approaches for stairs and corridors, and the building's fire strategy says which applies. Older blocks may predate current guidance; the survey records what is installed, not what a new block would have.",
            "Landlords and managing agents have duties under the Regulatory Reform (Fire Safety) Order 2005 for the common parts. Some higher-rise residential buildings have further duties under the Fire Safety (England) Regulations 2022; the responsible person should check which apply to their building.",
            "Leaseholders often ask what they are paying for. A clear written record of what was tested and found helps managing agents explain service charges.",
        ],
        "faqs": [
            ("Do all blocks of flats need a {thing}?", "It depends on the building's design and fire strategy. The survey records what is installed and what the strategy relies on."),
        ],
    },
    "car-park": {
        "label": "car parks",
        "paras": [
            "Car parks, especially enclosed and basement car parks, may rely on smoke ventilation to keep escape routes and firefighting access usable. Depending on design, that can mean natural openings, a {thing}, or mechanical extract and impulse fans.",
            "Car park ventilation often serves two roles: everyday fume control and smoke clearance in a fire. The fire mode is what matters for this page, and it is tested from the fire signal, not the normal running mode.",
            "Access in car parks is planned around traffic. Bays may need coning off, and work at height over driving aisles needs a safe system of work.",
            "Car park environments are dirty and damp. Detectors, actuators and vents suffer from dust and exhaust residue, so cleaning and condition checks are part of the visit.",
        ],
        "faqs": [
            ("Is car park ventilation the same as smoke ventilation?", "Often the same equipment serves both, but the fire mode is tested separately from the fire signal."),
        ],
    },
    "care-home": {
        "label": "care homes",
        "paras": [
            "Care homes often use progressive horizontal evacuation, moving residents away from a fire into protected areas rather than straight outside. Smoke control, including a {thing}, helps keep those protected routes usable for longer.",
            "Testing in care homes is planned with the manager. Alarms and vents opening can distress residents, so times, notice and areas are agreed in advance.",
            "Care home inspections look for evidence of maintenance. Records that clearly show what was tested and when support the home's fire risk assessment.",
            "Corridors in care homes fill with equipment — hoists, trolleys, chairs. Anything blocking a vent or detector is recorded and reported.",
        ],
        "faqs": [
            ("Can {thing} tests be planned around residents?", "Yes. Times, notice and areas are agreed with the manager in advance."),
        ],
    },
    "commercial": {
        "label": "commercial buildings",
        "paras": [
            "Commercial buildings — offices, shops, hotels, mixed-use schemes — use smoke control to protect escape stairs, corridors, atria and, in some cases, basement areas. The {thing} is one part of the building's fire strategy, often designed under BS 9999.",
            "Commercial sites usually have a facilities manager and set working hours. Testing and works are planned to avoid disruption, with out-of-hours visits where the building cannot be disturbed.",
            "Commercial tenants may be responsible for some systems and landlords for others. The scope makes clear who owns which part.",
            "Commercial insurers and auditors expect clear records. iComply's records are written to be filed and audited.",
        ],
        "faqs": [
            ("Can {thing} work be done out of hours?", "Yes, where the building cannot be disturbed. It is planned and quoted accordingly on application."),
        ],
    },
    "factory": {
        "label": "factories and warehouses",
        "paras": [
            "Factories and warehouses often use natural smoke and heat exhaust ventilation through roof ventilators, sometimes with smoke curtains dividing the roof into reservoirs. A {thing} in this setting usually covers large roof areas and heavy-duty actuators.",
            "Roof access is the main planning item: fragile sheets, rooflights, edge protection and safe access routes. Work at height is planned before the visit and written into the scope.",
            "Changes to racking, mezzanines and partitions can change how smoke moves. Changes seen on site are reported for review.",
            "Industrial sites often run shifts. Visits are planned with the site manager around production and deliveries.",
        ],
        "faqs": [
            ("How do you access factory roof vents?", "Through a planned safe system of work — fragile roof sheets, rooflights and edge protection are considered before the visit."),
        ],
    },
    "high-rise": {
        "label": "high-rise buildings",
        "paras": [
            "High-rise residential and mixed-use buildings depend heavily on smoke control in stairs, lobbies and corridors. A {thing} in a tall building may be part of a smoke shaft, a pressurisation system or a mechanical extract arrangement.",
            "The Fire Safety (England) Regulations 2022 added duties for some high-rise residential buildings. The responsible person should check which apply. iComply's records are written to support those duties.",
            "Tall buildings have more floors, more vents and more interfaces. Floor-by-floor testing is planned so every vent and every cause-and-effect is proved.",
            "Access to roofs and plant areas in high-rise buildings is controlled. Permits and access arrangements are agreed in advance.",
        ],
        "faqs": [
            ("Are there extra duties for high-rise buildings?", "Some high-rise residential buildings have further duties under the Fire Safety (England) Regulations 2022. The responsible person should check which apply."),
        ],
    },
    "hmo": {
        "label": "HMOs",
        "paras": [
            "Houses in multiple occupation range from converted houses to purpose-built blocks. Larger HMOs and those converted into flats may have a {thing} on the common stair. The local housing authority's licensing conditions and the fire risk assessment set what is expected.",
            "HMO landlords are often responsible for the common parts directly. Clear records help with licensing inspections and with the fire risk assessment.",
            "Access in HMOs is arranged with the landlord or agent, with notice to tenants where tests will sound alarms or open vents.",
            "In converted buildings, vents and actuators are often retrofitted into old frames. Frame condition and fixing are checked as part of the visit.",
        ],
        "faqs": [
            ("Do HMOs need a {thing}?", "It depends on the building, licensing conditions and fire risk assessment. The survey records what is installed and relied on."),
        ],
    },
}

CONTEXT = {
    "aov": [
        "AOV and smoke control work is usually instructed by a landlord, freeholder, managing agent or facilities manager. A commercial tenant may instruct it where the lease requires. The reply confirms whether another party needs to approve the scope.",
        "Typical buildings include purpose-built blocks of flats, converted houses split into flats, care homes, student accommodation, offices, hotels, mixed-use schemes, warehouses and enclosed car parks. Each has its own fire strategy and its own constraints on access.",
        "Smoke control interacts with the fire alarm, door closers, lifts and sometimes the building management system. The visit records those interfaces so faults that cross between systems are traced to the right place.",
        "Buildings change over their life: new partitions, refurbished common parts, changed uses. Where a change could affect smoke control, it is noted for the responsible person and, if needed, the fire engineer.",
        "Records are often the weakest part of a smoke-control installation. iComply's visit leaves a plain record of what is installed, what was tested and what needs attention, so the next visit starts from facts.",
        "Occupied buildings mean residents, staff or visitors. Testing that opens vents or sounds alarms is arranged with notice, and work is planned so escape routes stay usable.",
    ],
    "barrier": [
        "Barrier work is usually instructed by a site owner, managing agent, facilities manager or estate manager. On shared sites, the reply confirms who else needs to agree the scope — for example a freeholder or a residents' company.",
        "Typical sites include private car parks, residential estates, office and retail car parks, warehouse yards, depots, schools, hospitals, farms and private access roads. Each has its own traffic, users and constraints.",
        "Barriers sit where vehicles and pedestrians meet. The layout keeps people out of the arm's path where possible, with a separate footway and clear markings.",
        "Ground conditions decide foundations. Buried services are checked before any digging, and the barrier position may move to avoid them.",
        "Signage and markings tell drivers what to expect: access method, height or width limit, contact details. They are part of the scope, not an afterthought.",
        "Records list the barrier model, arm length, settings, measured clearances and test results, so future repairs and replacements start from facts.",
    ],
}

SHARED = {
    "aov": [
        "Quotes are price on application. iComply does not publish catalogue prices for smoke-control work because two buildings with the same vent can need very different access, testing and interface work. The written scope sets out what is included so the price can be judged fairly.",
        "To start, send the address, the type of building, what is installed (photos of the panel and vents help), any previous service records and the reason for the enquiry. Phone 07517806082, use WhatsApp or the contact form.",
        "iComply does not claim accreditation badges it does not hold, and does not describe products by ratings their makers have not published. Figures in a scope come from site measurement or the maker's documents.",
        "The workshop is at 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Sites across the UK mainland are quoted from there, with travel included in the written quote.",
        "Handover documents typically include the scope, the test results, a list of defects with priorities, photos where useful and any maker's documentation supplied. They are written to be filed in the building's fire-safety records.",
        "Where a building's fire strategy is unclear or missing, iComply records what is installed and how it behaves and recommends the responsible person consult a fire engineer on design questions. Design decisions are not made on the fly during a service visit.",
    ],
    "barrier": [
        "Quotes are price on application. Barrier jobs vary with ground, cabling, access control and safety measures, so a catalogue price would mislead. The written scope lists measurements, products and exclusions so the price can be judged fairly.",
        "To start, send the address, photos of the entrance from both directions, rough measurements of the opening and any headroom limits, how many vehicles use it and what you want the barrier to achieve. Phone 07517806082, use WhatsApp or the contact form.",
        "Dimensions in a scope are either measured on site or taken from the manufacturer's published data for the named product. iComply never invents a product rating, and does not describe a barrier as impact-tested unless its maker publishes that classification.",
        "The workshop is at 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Sites across the UK mainland are quoted from there, with travel included in the written quote.",
        "Handover documents include the measured layout, the product and arm details, spring or counterbalance settings, safety test results for powered barriers, and signage details.",
        "The deeper barrier guides on this site cover manual barriers, arm length, height clearance and height restriction, and opening, lane and width restriction. Each links back to a written-scope approach and price on application.",
    ],
}
