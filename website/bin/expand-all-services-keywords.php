<?php
/**
 * Expand services (fire safety + professional + construction) and seed 100s of keywords
 * with unique intro/body/meta/focus/faq. Merges into existing JSON (no wipe).
 *
 * Usage: php bin/expand-all-services-keywords.php
 */
require_once __DIR__ . '/../config.php';

// ---------------------------------------------------------------------------
// Services (slug => display name) — full catalogue
// ---------------------------------------------------------------------------
$services = [
    // Core compliance (existing)
    'electrical' => 'Electrical',
    'gas-systems' => 'Gas Systems',
    'nurse-call' => 'Nurse Call Systems',
    // Fire safety (expanded)
    'fire-alarms' => 'Fire Alarms',
    'emergency-lighting' => 'Emergency Lighting',
    'aov-air-handling' => 'AOV & Smoke Control',
    'fire-risk-assessments' => 'Fire Risk Assessments',
    'fire-extinguishers' => 'Fire Extinguishers',
    'fire-doors' => 'Fire Doors',
    'fire-stopping' => 'Fire Stopping',
    'fire-suppression' => 'Fire Suppression',
    'sprinkler-systems' => 'Sprinkler Systems',
    'dry-risers' => 'Dry & Wet Risers',
    'fire-signage' => 'Fire Safety Signage',
    'evacuation-alerts' => 'Evacuation Alert Systems',
    'kitchen-fire-suppression' => 'Kitchen Fire Suppression',
    'fire-compartmentation' => 'Fire Compartmentation',
    // Security & access (existing)
    'intruder-alarm' => 'Intruder Alarms',
    'cctv' => 'CCTV Systems',
    'access-control' => 'Access Control',
    'door-entry' => 'Door Entry Systems',
    'intercoms' => 'Intercoms',
    // Professional services
    'landlord-compliance' => 'Landlord Compliance',
    'facilities-management' => 'Facilities Management Support',
    'building-maintenance' => 'Building Maintenance',
    'project-management' => 'Project Management',
    'building-surveys' => 'Building Surveys',
    'cdm-support' => 'CDM Support',
    'property-refurbishment-pm' => 'Refurbishment Project Support',
    'compliance-consultancy' => 'Compliance Consultancy',
    // Construction & fit-out
    'kitchens' => 'Kitchen Fitting',
    'bathrooms' => 'Bathroom Fitting',
    'renovation' => 'Property Renovation',
    'plastering' => 'Plastering',
    'tiling' => 'Tiling',
    'painting-decorating' => 'Painting & Decorating',
    'joinery' => 'Joinery',
    'carpentry' => 'Carpentry',
    'flooring' => 'Flooring',
    'plumbing' => 'Plumbing',
    'heating' => 'Heating Installation',
    'roofing' => 'Roofing',
    'brickwork' => 'Brickwork & Masonry',
    'dry-lining' => 'Dry Lining',
    'loft-conversions' => 'Loft Conversions',
    'extensions' => 'Home Extensions',
    'insulation' => 'Insulation',
    'damp-proofing' => 'Damp Proofing',
    'windows-doors' => 'Windows & Doors',
    'rendering' => 'Rendering',
    'groundworks' => 'Groundworks',
    'landscaping' => 'Landscaping',
    'electrics-first-fix' => 'First Fix Electrical',
    'commercial-fit-out' => 'Commercial Fit-Out',
];

$categories = [
    'fire-safety' => [
        'label' => 'Fire Safety Systems',
        'blurb' => 'Detection, suppression, doors, FRA and life-safety compliance across the North West.',
        'services' => [
            'fire-alarms', 'emergency-lighting', 'aov-air-handling', 'fire-risk-assessments',
            'fire-extinguishers', 'fire-doors', 'fire-stopping', 'fire-suppression',
            'sprinkler-systems', 'dry-risers', 'fire-signage', 'evacuation-alerts',
            'kitchen-fire-suppression', 'fire-compartmentation',
        ],
    ],
    'electrical-gas' => [
        'label' => 'Electrical & Gas',
        'blurb' => 'EICR, rewires, EV, gas safety and landlord certificates.',
        'services' => ['electrical', 'gas-systems', 'electrics-first-fix'],
    ],
    'security-care' => [
        'label' => 'Security & Care Systems',
        'blurb' => 'CCTV, access, door entry, intercoms, nurse call and intruder alarms.',
        'services' => ['cctv', 'access-control', 'door-entry', 'intercoms', 'intruder-alarm', 'nurse-call'],
    ],
    'professional' => [
        'label' => 'Professional Services',
        'blurb' => 'Landlord packages, FM support, surveys, CDM and compliance consultancy.',
        'services' => [
            'landlord-compliance', 'facilities-management', 'building-maintenance',
            'project-management', 'building-surveys', 'cdm-support',
            'property-refurbishment-pm', 'compliance-consultancy',
        ],
    ],
    'construction' => [
        'label' => 'Construction & Fit-Out',
        'blurb' => 'Kitchens, bathrooms, renovation, plastering, joinery and full property works.',
        'services' => [
            'kitchens', 'bathrooms', 'renovation', 'plastering', 'tiling', 'painting-decorating',
            'joinery', 'carpentry', 'flooring', 'plumbing', 'heating', 'roofing', 'brickwork',
            'dry-lining', 'loft-conversions', 'extensions', 'insulation', 'damp-proofing',
            'windows-doors', 'rendering', 'groundworks', 'landscaping', 'commercial-fit-out',
        ],
    ],
];

$meta = [
    'electrical' => ['blurb' => 'EICR, rewires, consumer units, EV chargers, PAT and commercial electrical installs to BS 7671.', 'short' => 'EICR, rewires, EV chargers, PAT & commercial installs', 'standards' => 'BS 7671 (18th Edition) · EICR · Part P · EV charger regs'],
    'fire-alarms' => ['blurb' => 'BS 5839 design, install, service and certification for addressable, conventional and wireless systems.', 'short' => 'BS 5839 design, install, service & certification', 'standards' => 'BS 5839-1 · BS 5839-6 · BS EN 54 · Fire Safety Order'],
    'emergency-lighting' => ['blurb' => 'BS 5266 testing, upgrades, LED conversions and monthly/annual certification.', 'short' => 'BS 5266 testing, upgrades & LED conversions', 'standards' => 'BS 5266 · BS EN 1838 · monthly & annual tests'],
    'aov-air-handling' => ['blurb' => 'Smoke vents, AOV panels, smoke control and AHU-related life-safety systems.', 'short' => 'Smoke vents, AOV panels & smoke control', 'standards' => 'BS 9991 · EN 12101 · smoke control guidance'],
    'nurse-call' => ['blurb' => 'Care home and hospital nurse call design, install, upgrades and planned maintenance.', 'short' => 'Care home & hospital nurse call systems', 'standards' => 'HTM 08-03 · care home & hospital specifications'],
    'gas-systems' => ['blurb' => 'CP12 / CP44 landlord certificates, boilers, commercial gas and safety checks.', 'short' => 'CP12/CP44 landlord certs, boilers & commercial gas', 'standards' => 'Gas Safe · CP12 / CP44 · manufacturer servicing'],
    'intruder-alarm' => ['blurb' => 'PD 6662 / BS EN 50131 wired and wireless intruder systems with monitoring options.', 'short' => 'Wired & wireless intruder systems', 'standards' => 'PD 6662 · BS EN 50131'],
    'cctv' => ['blurb' => 'IP and HD CCTV design, install, remote viewing and NVR/DVR recording.', 'short' => 'IP / HD CCTV design, install & monitoring', 'standards' => 'BS EN 62676 · GDPR / DPA camera siting'],
    'access-control' => ['blurb' => 'Paxton, HID, Salto door access, credentials and fire-override integration.', 'short' => 'Paxton, HID, Salto door access', 'standards' => 'BS EN 50133 · EN 60839 · fire-release integration'],
    'door-entry' => ['blurb' => 'Video and audio door entry for flats, multi-tenant and commercial sites.', 'short' => 'Video & audio door entry', 'standards' => 'BS EN 60839 · multi-tenant residential standards'],
    'intercoms' => ['blurb' => 'Multi-tenant and commercial intercom systems with master/substation setups.', 'short' => 'Multi-tenant & commercial intercoms', 'standards' => 'Commercial intercom best practice'],
    'fire-risk-assessments' => ['blurb' => 'Suitable and sufficient fire risk assessments for landlords, commercial sites, HMOs and multi-occupied buildings.', 'short' => 'FRA for landlords, HMO & commercial sites', 'standards' => 'Regulatory Reform (Fire Safety) Order 2005 · Fire Safety Act · PAS 79 guidance'],
    'fire-extinguishers' => ['blurb' => 'Supply, install, service and certificate portable fire extinguishers to BS 5306.', 'short' => 'Extinguisher supply, service & BS 5306 certs', 'standards' => 'BS 5306 · manufacturer servicing schedules'],
    'fire-doors' => ['blurb' => 'Fire door surveys, installation, repairs, seals, closers and certification for blocks and commercial buildings.', 'short' => 'Fire door survey, install, repair & certify', 'standards' => 'BS 476 / EN 1634 · BS 8214 · Fire Safety Act'],
    'fire-stopping' => ['blurb' => 'Passive fire protection — penetration seals, cavity barriers and compartmentation reinstatement.', 'short' => 'Penetration seals & compartmentation works', 'standards' => 'BS 476 · manufacturer tested systems · Building Regs B'],
    'fire-suppression' => ['blurb' => 'Specialist suppression systems for plant rooms, server rooms and high-risk spaces.', 'short' => 'Specialist suppression for high-risk spaces', 'standards' => 'BS EN 15004 · manufacturer design standards'],
    'sprinkler-systems' => ['blurb' => 'Sprinkler design support, installation liaison, inspection and maintenance for commercial sites.', 'short' => 'Sprinkler inspection & maintenance support', 'standards' => 'BS EN 12845 · LPC rules · insurer requirements'],
    'dry-risers' => ['blurb' => 'Dry and wet riser inspection, testing, maintenance and remedial works for multi-storey buildings.', 'short' => 'Dry/wet riser test & maintenance', 'standards' => 'BS 9990 · BS 5041 · fire service access'],
    'fire-signage' => ['blurb' => 'Fire exit, escape route, extinguisher and mandatory safety signage design and installation.', 'short' => 'Fire exit & safety signage packages', 'standards' => 'BS ISO 7010 · BS 5499 · escape route guidance'],
    'evacuation-alerts' => ['blurb' => 'Evacuation alert systems, deaf alerters and PEEP-friendly life-safety notifications.', 'short' => 'Evacuation alerts & deaf alerters', 'standards' => 'BS 8629 · BS 5839-9 · inclusive evacuation'],
    'kitchen-fire-suppression' => ['blurb' => 'Commercial kitchen canopy and cooking-range suppression install and service.', 'short' => 'Commercial kitchen suppression systems', 'standards' => 'LPS 1223 · manufacturer kitchen systems'],
    'fire-compartmentation' => ['blurb' => 'Compartmentation surveys, drawings support and remedial fire-separating construction.', 'short' => 'Compartmentation surveys & remedial works', 'standards' => 'Building Regs B · BS 9991 / BS 9999 guidance'],
    'landlord-compliance' => ['blurb' => 'Bundled landlord compliance — EICR, gas, fire, emergency lighting and documentation packs.', 'short' => 'Bundled landlord certificates & packs', 'standards' => 'Homes Act · Fire Safety Act · landlord regs'],
    'facilities-management' => ['blurb' => 'Planned maintenance, multi-site compliance scheduling and FM engineer call-outs.', 'short' => 'PPM, multi-site compliance & call-outs', 'standards' => 'SFG20-style PPM · site-specific specs'],
    'building-maintenance' => ['blurb' => 'Reactive and planned building maintenance for residential blocks and commercial estates.', 'short' => 'Reactive & planned building maintenance', 'standards' => 'Site RAMS · landlord & commercial SLAs'],
    'project-management' => ['blurb' => 'On-site coordination for multi-trade compliance and refurbishment programmes.', 'short' => 'Multi-trade programme coordination', 'standards' => 'CDM 2015 awareness · programme control'],
    'building-surveys' => ['blurb' => 'Condition surveys, snagging and pre-works surveys to scope compliance and fabric repairs.', 'short' => 'Condition, snagging & pre-works surveys', 'standards' => 'RICS-style condition reporting practice'],
    'cdm-support' => ['blurb' => 'Practical CDM support for smaller works — pre-construction info, RAMS coordination and handover.', 'short' => 'CDM paperwork & site coordination support', 'standards' => 'CDM 2015 · HSE guidance'],
    'property-refurbishment-pm' => ['blurb' => 'Refurbishment sequencing for kitchens, bathrooms and multi-room upgrades with compliance in mind.', 'short' => 'Refurb sequencing with compliance built-in', 'standards' => 'Building Regs · landlord void standards'],
    'compliance-consultancy' => ['blurb' => 'Independent compliance advice for portfolios — prioritised action plans and audit readiness.', 'short' => 'Portfolio compliance advice & action plans', 'standards' => 'Fire Safety Order · BS standards suite'],
    'kitchens' => ['blurb' => 'Supply and fit kitchens for homes, HMOs and light commercial — units, worktops, sinks and first/second fix coordination.', 'short' => 'Kitchen supply & fit for homes and HMOs', 'standards' => 'Building Regs · gas/electric safe isolation'],
    'bathrooms' => ['blurb' => 'Full bathroom and wet-room fitting — sanitaryware, tiling coordination, ventilation and waterproofing.', 'short' => 'Bathroom & wet-room fitting', 'standards' => 'Part P · Part F · waterproofing best practice'],
    'renovation' => ['blurb' => 'End-to-end property renovation and void refurbs for landlords, investors and homeowners.', 'short' => 'Full property & void renovations', 'standards' => 'Building Regs · landlord letting standards'],
    'plastering' => ['blurb' => 'Skim, board finish, patch repairs and full-room plastering for residential and commercial interiors.', 'short' => 'Skim, board finish & patch plastering', 'standards' => 'BS EN 13914 · trade finish standards'],
    'tiling' => ['blurb' => 'Wall and floor tiling for kitchens, bathrooms and commercial wet areas with correct adhesives and tanking.', 'short' => 'Wall & floor tiling with tanking', 'standards' => 'BS 5385 · manufacturer adhesive systems'],
    'painting-decorating' => ['blurb' => 'Interior and exterior painting, decorating and redecoration packages for voids and occupied homes.', 'short' => 'Interior & exterior redecoration', 'standards' => 'Trade prep standards · low-VOC options'],
    'joinery' => ['blurb' => 'Custom joinery — doors, frames, cupboards, boxing and built-in storage.', 'short' => 'Doors, frames, cupboards & built-ins', 'standards' => 'BS 4787 doors · site joinery practice'],
    'carpentry' => ['blurb' => 'First and second fix carpentry, stud walls, skirting, architraves and site carpentry packages.', 'short' => '1st/2nd fix carpentry packages', 'standards' => 'NHBC-style detailing · site practice'],
    'flooring' => ['blurb' => 'Laminate, vinyl, engineered wood and commercial flooring supply and installation.', 'short' => 'Laminate, vinyl & engineered flooring', 'standards' => 'Manufacturer install warranties · subfloor prep'],
    'plumbing' => ['blurb' => 'Domestic and light commercial plumbing — bathrooms, kitchens, leaks, radiators and pipework.', 'short' => 'Domestic & light commercial plumbing', 'standards' => 'Water Regs · WRAS fittings · Gas Safe where required'],
    'heating' => ['blurb' => 'Boiler installs, radiator upgrades, system flushes and heating controls for homes and small commercial.', 'short' => 'Boilers, radiators & heating controls', 'standards' => 'Gas Safe · manufacturer instructions · Building Regs'],
    'roofing' => ['blurb' => 'Roof repairs, re-roofing, flat roofs, guttering and chimney works for residential properties.', 'short' => 'Roof repairs, flat roofs & gutters', 'standards' => 'BS 5534 · manufacturer roofing systems'],
    'brickwork' => ['blurb' => 'Brickwork repairs, repointing, openings and small masonry builds.', 'short' => 'Repointing, repairs & small masonry', 'standards' => 'BS 5628 / EN 1996 practice'],
    'dry-lining' => ['blurb' => 'Metal stud, plasterboard partitions, acoustic linings and fire-rated boarding.', 'short' => 'Partitions, acoustic & fire-rated board', 'standards' => 'BS 8212 · fire-rated board systems'],
    'loft-conversions' => ['blurb' => 'Loft conversion building works with insulation, stairs, fire doors and building control support.', 'short' => 'Loft conversion building packages', 'standards' => 'Building Regs Parts B/L/K · Party Wall awareness'],
    'extensions' => ['blurb' => 'Single-storey and small extension building packages — structure, envelope and first-fix readiness.', 'short' => 'Single-storey & small extensions', 'standards' => 'Building Regs · structural design input'],
    'insulation' => ['blurb' => 'Loft, cavity liaison, internal wall and floor insulation upgrades for comfort and EPC gains.', 'short' => 'Loft, IWI and floor insulation upgrades', 'standards' => 'Part L · PAS 2035 awareness where relevant'],
    'damp-proofing' => ['blurb' => 'Rising damp, penetrating damp diagnosis support, tanking and remedial damp treatments.', 'short' => 'Damp diagnosis, tanking & treatments', 'standards' => 'BS 6576 · manufacturer damp systems'],
    'windows-doors' => ['blurb' => 'uPVC and composite window and door replacement, fire door sets and ironmongery upgrades.', 'short' => 'Windows, doors & fire door sets', 'standards' => 'Part L · Part Q · fire door certification'],
    'rendering' => ['blurb' => 'External rendering, repairs and weatherproof coatings for houses and small blocks.', 'short' => 'External render & weatherproof coatings', 'standards' => 'BS EN 13914 · manufacturer render systems'],
    'groundworks' => ['blurb' => 'Foundations for small builds, drainage, hardstanding and site preparation.', 'short' => 'Foundations, drainage & hardstanding', 'standards' => 'Building Regs · NHBC-style groundworks practice'],
    'landscaping' => ['blurb' => 'Hard landscaping, fencing, patios and external works for residential plots.', 'short' => 'Patios, fencing & external works', 'standards' => 'Good practice drainage · boundary awareness'],
    'electrics-first-fix' => ['blurb' => 'First-fix electrical for new builds, renovations and conversions before plaster and second fix.', 'short' => 'First-fix electrics for renovations', 'standards' => 'BS 7671 · Part P notification where required'],
    'commercial-fit-out' => ['blurb' => 'Office and light commercial fit-out — partitions, services coordination, finishes and compliance.', 'short' => 'Office & light commercial fit-out', 'standards' => 'Building Regs · fire strategy coordination'],
];

// ---------------------------------------------------------------------------
// Keyword seed phrases per service (name only; related derived)
// ---------------------------------------------------------------------------
$kwSeed = [
    'electrical' => [
        'EICR Report', 'EICR Certificate', 'Landlord EICR', 'Commercial EICR', 'PAT Testing',
        'Consumer Unit Upgrade', 'Full House Rewire', 'EV Charger Installation', 'Emergency Electrician',
        'Electrical Fault Finding', 'Three Phase Installation', 'HMO Electrical Certificate',
        'Fixed Wire Testing', 'Electrical Remedial Works', 'RCBO Upgrade', 'Distribution Board Install',
        'Landlord Electrical Safety Check', 'New Build Electrical', 'Data Cabling Installation',
        'Electrical Compliance Certificate', 'Office Electrical Testing', 'Industrial Electrical Maintenance',
        'Socket Installation', 'Lighting Circuit Installation', 'Outside Socket Installation',
        'Fuse Board Replacement', 'Partial Rewire', 'Home EV Charger', 'Commercial EV Charging',
        'BS 7671 Inspection', 'Electrical Certificate for Lettings', 'Portable Appliance Testing',
    ],
    'fire-alarms' => [
        'Fire Alarm Installation', 'Fire Alarm Servicing', 'Fire Alarm Certificate', 'BS 5839 Fire Alarm',
        'Addressable Fire Alarm', 'Conventional Fire Alarm', 'Wireless Fire Alarm', 'Fire Alarm Panel Service',
        'Landlord Fire Alarm', 'HMO Fire Alarm System', 'Commercial Fire Alarm', 'Fire Alarm Maintenance Contract',
        'L1 Fire Alarm System', 'L2 Fire Alarm System', 'L3 Fire Alarm System', 'VESDA Installation',
        'Beam Detection System', 'Voice Alarm System', 'Fire Alarm Commissioning', 'Fire Alarm Upgrade',
        'Fire Alarm Battery Replacement', 'Fire Alarm Fault Finding', 'Domestic Fire Alarm Installation',
        'Fire Detection System Installation', 'Smoke Detector Installation Commercial', 'Multi Site Fire Alarm Maintenance',
        'Fire Alarm Logbook', 'Fire Alarm Weekly Test Support', 'Category LD2 Fire Alarm', 'Category LD3 Fire Alarm',
        'Kentec Panel Service', 'Apollo Fire Detectors', 'Hochiki Fire Detection', 'Fire Alarm Monitoring Setup',
    ],
    'emergency-lighting' => [
        'Emergency Lighting Installation', 'Emergency Lighting Testing', 'Emergency Lighting Certificate',
        'BS 5266 Emergency Lighting', 'LED Emergency Lighting Conversion', 'Emergency Exit Lighting',
        'Maintained Emergency Lighting', 'Non Maintained Emergency Lighting', 'Self Test Emergency Lighting',
        'Emergency Lighting Monthly Test', 'Emergency Lighting Annual Test', 'Landlord Emergency Lighting',
        'Commercial Emergency Lighting', 'Office Emergency Lighting', 'Warehouse Emergency Lighting',
        'Care Home Emergency Lighting', 'Emergency Bulkhead Lights', 'Emergency Lighting Servicing',
        'Emergency Lighting Duration Test', 'Emergency Lighting Upgrade', 'Central Battery Emergency Lighting',
        'Emergency Lighting Design', 'Escape Route Lighting', 'Open Area Emergency Lighting',
    ],
    'aov-air-handling' => [
        'AOV System Installation', 'AOV Servicing', 'Smoke Vent Installation', 'Smoke Control System Maintenance',
        'AOV Panel Replacement', 'Stairwell Smoke Vent', 'Corridor Smoke Vent', 'AOV Actuator Replacement',
        'Smoke Shaft Maintenance', 'Natural Smoke Ventilation', 'Mechanical Smoke Extract', 'AOV Commissioning',
        'AOV Fault Finding', 'High Rise AOV Maintenance', 'Apartment Block Smoke Control',
        'EN 12101 Smoke Vent', 'AOV Battery Replacement', 'Smoke Curtain Maintenance',
    ],
    'fire-risk-assessments' => [
        'Fire Risk Assessment', 'Commercial Fire Risk Assessment', 'Landlord Fire Risk Assessment',
        'HMO Fire Risk Assessment', 'Block of Flats Fire Risk Assessment', 'Office Fire Risk Assessment',
        'Warehouse Fire Risk Assessment', 'Care Home Fire Risk Assessment', 'Retail Fire Risk Assessment',
        'School Fire Risk Assessment', 'Fire Risk Assessment Review', 'Type 1 Fire Risk Assessment',
        'Type 3 Fire Risk Assessment', 'Type 4 Fire Risk Assessment', 'PAS 79 Fire Risk Assessment',
        'Fire Safety Order Assessment', 'Fire Risk Assessment Certificate', 'Multi Site Fire Risk Assessments',
        'Communal Area Fire Risk Assessment', 'Fire Risk Assessment Action Plan', 'FRA for Managing Agents',
        'Fire Risk Assessment North West', 'Suitable and Sufficient FRA', 'Fire Safety Audit',
        'Significant Findings Fire Risk', 'Evacuation Strategy Review', 'Fire Risk Assessment Update',
        'Annual Fire Risk Assessment', 'New Premises Fire Risk Assessment', 'Change of Use Fire Risk Assessment',
    ],
    'fire-extinguishers' => [
        'Fire Extinguisher Service', 'Fire Extinguisher Supply', 'Fire Extinguisher Installation',
        'CO2 Fire Extinguisher', 'Foam Fire Extinguisher', 'Water Fire Extinguisher', 'Powder Fire Extinguisher',
        'Wet Chemical Extinguisher', 'Fire Extinguisher Certificate', 'BS 5306 Extinguisher Service',
        'Extinguisher Maintenance Contract', 'Fire Blanket Supply', 'Extinguisher Wall Mounting',
        'Commercial Extinguisher Package', 'Landlord Extinguisher Service', 'Office Fire Extinguishers',
        'Kitchen Fire Extinguishers', 'Extinguisher Replacement', 'Extinguisher Survey',
        'Fire Point Setup', 'Multi Site Extinguisher Service',
    ],
    'fire-doors' => [
        'Fire Door Installation', 'Fire Door Survey', 'Fire Door Inspection', 'Fire Door Certification',
        'Fire Door Repair', 'Fire Door Closer Replacement', 'Fire Door Seals', 'FD30 Fire Door',
        'FD60 Fire Door', 'Apartment Fire Door', 'Commercial Fire Door', 'Fire Door Ironmongery',
        'Fire Door Gap Remedial', 'Glazed Fire Door', 'Fire Door Frame Repair', 'Landlord Fire Door Survey',
        'Block Fire Door Upgrade', 'Fire Door Intumescent Seals', 'Fire Exit Door Maintenance',
        'Fire Door Compliance Check', 'Master Key Fire Door Sets', 'Fire Door Replacement Programme',
    ],
    'fire-stopping' => [
        'Fire Stopping Installation', 'Fire Stopping Survey', 'Penetration Seals', 'Cavity Barrier Installation',
        'Fire Collar Installation', 'Pipe Fire Stopping', 'Cable Fire Stopping', 'Service Riser Fire Stopping',
        'Passive Fire Protection', 'Fire Stopping Remedial Works', 'Compartment Wall Fire Stopping',
        'Fire Rated Sealant Works', 'Fire Batt Installation', 'Putty Pad Installation',
        'Landlord Fire Stopping', 'Commercial Fire Stopping Package', 'Fire Stopping Certificate',
        'Retrofit Fire Stopping', 'Open State Cavity Barrier', 'Linear Gap Seals',
    ],
    'fire-suppression' => [
        'Fire Suppression System Installation', 'Fire Suppression Servicing', 'Server Room Suppression',
        'Plant Room Suppression', 'Clean Agent Suppression', 'Inert Gas Suppression',
        'Fire Suppression Commissioning', 'Suppression Cylinder Service', 'Suppression System Design Support',
        'Data Centre Fire Suppression', 'Archive Room Suppression', 'Suppression Detection Integration',
        'Fire Suppression Maintenance Contract', 'Local Application Suppression',
    ],
    'sprinkler-systems' => [
        'Sprinkler System Maintenance', 'Sprinkler System Inspection', 'Sprinkler System Testing',
        'Commercial Sprinkler Service', 'Sprinkler Valve Inspection', 'Sprinkler Head Replacement',
        'Sprinkler System Survey', 'Residential Sprinkler Advice', 'Sprinkler Flow Test',
        'Sprinkler System Remedial Works', 'Multi Site Sprinkler Contracts', 'Sprinkler Compliance Check',
        'Warehouse Sprinkler Maintenance', 'Sprinkler Alarm Interface',
    ],
    'dry-risers' => [
        'Dry Riser Testing', 'Dry Riser Maintenance', 'Wet Riser Testing', 'Dry Riser Inlet Inspection',
        'Dry Riser Outlet Service', 'Rising Main Test', 'Dry Riser Certificate', 'High Rise Dry Riser',
        'Dry Riser Remedial Works', 'Landing Valve Replacement', 'Dry Riser Pressure Test',
        'Fire Service Inlet Box', 'Dry Riser Annual Test', 'Wet Riser Maintenance',
    ],
    'fire-signage' => [
        'Fire Exit Signage', 'Escape Route Signs', 'Fire Safety Signs Installation', 'Photoluminescent Signage',
        'Fire Extinguisher Signs', 'Fire Action Notices', 'Assembly Point Signs', 'No Smoking Fire Signs',
        'Mandatory Fire Signage Package', 'Commercial Fire Sign Survey', 'Landlord Fire Signage',
        'BS ISO 7010 Signage', 'Directional Escape Signs', 'Fire Door Keep Shut Signs',
    ],
    'evacuation-alerts' => [
        'Evacuation Alert System', 'Deaf Alerter Installation', 'Vibrating Pillow Alarmer',
        'BS 8629 Evacuation Alert', 'PEEP Alert Devices', 'Visual Alarm Devices', 'Fire Alarm Strobe Installation',
        'Inclusive Evacuation System', 'Care Home Evacuation Alerts', 'Flats Evacuation Alert System',
        'Wireless Deaf Alerter', 'Evacuation Alert Maintenance',
    ],
    'kitchen-fire-suppression' => [
        'Kitchen Fire Suppression System', 'Canopy Suppression Install', 'Commercial Kitchen Fire System',
        'Wet Chemical Kitchen System', 'Kitchen Suppression Servicing', 'Restaurant Fire Suppression',
        'Takeaway Kitchen Fire Safety', 'Kitchen Hood Suppression', 'Kitchen Suppression Certificate',
        'Ansul Style Kitchen System Service', 'Catering Fire Suppression Maintenance',
    ],
    'fire-compartmentation' => [
        'Fire Compartmentation Survey', 'Compartmentation Remedial Works', 'Fire Separating Wall Works',
        'Floor Compartmentation', 'Service Riser Compartmentation', 'Compartment Drawings Support',
        'Fire Barrier Installation', 'Landlord Compartmentation Survey', 'Block Compartmentation Upgrade',
        'Fire Integrity Survey', 'Hidden Voids Fire Survey', 'Compartmentation Certificate Support',
    ],
    'gas-systems' => [
        'Landlord Gas Safety Certificate', 'CP12 Gas Certificate', 'CP44 Gas Certificate', 'Boiler Service',
        'Gas Safety Check', 'Commercial Gas Installation', 'Gas Cooker Installation', 'Gas Leak Investigation',
        'Boiler Installation', 'Gas Meter Relocation Support', 'HMO Gas Safety', 'Portfolio Gas Certificates',
        'Gas Appliance Service', 'Landlord Gas Safety North West', 'Annual Gas Safety Certificate',
        'Gas Safe Engineer Stockport', 'Commercial Kitchen Gas Works', 'Gas Valve Replacement',
    ],
    'nurse-call' => [
        'Nurse Call System Installation', 'Nurse Call System Maintenance', 'Care Home Nurse Call',
        'Hospital Nurse Call Upgrade', 'Wireless Nurse Call', 'Nurse Call Handset Replacement',
        'Emergency Pull Cord System', 'Staff Attack System', 'Nurse Call Panel Service',
        'Nursing Home Call System', 'Disabled Toilet Alarm', 'Nurse Call Commissioning',
        'HTM Nurse Call Support', 'Multi Site Nurse Call Contract',
    ],
    'intruder-alarm' => [
        'Intruder Alarm Installation', 'Intruder Alarm Monitoring', 'Wireless Intruder Alarm',
        'Wired Intruder Alarm', 'Burglar Alarm Service', 'Alarm Panel Upgrade', 'PIR Sensor Installation',
        'Commercial Intruder Alarm', 'Home Intruder Alarm', 'Alarm Maintenance Contract',
        'Intruder Alarm Repair', 'Grade 2 Intruder Alarm', 'Grade 3 Intruder Alarm', 'Alarm Keypad Replacement',
        'Shop Alarm Installation', 'Warehouse Intruder Alarm',
    ],
    'cctv' => [
        'CCTV Installation', 'IP CCTV System', 'HD CCTV Installation', 'CCTV Maintenance',
        'Remote Viewing CCTV', 'NVR Installation', 'DVR CCTV System', 'Dome Camera Installation',
        'Bullet Camera Installation', 'Commercial CCTV Package', 'Landlord CCTV Installation',
        'Warehouse CCTV', 'Retail CCTV System', 'ANPR Camera Advice', 'CCTV System Upgrade',
        'Multi Site CCTV Monitoring Setup', 'Outdoor CCTV Installation', 'Indoor CCTV Installation',
    ],
    'access-control' => [
        'Access Control Installation', 'Paxton Net2 Install', 'Card Access System', 'Fob Access Control',
        'Door Access Control', 'Magnetic Lock Installation', 'Access Control Maintenance',
        'Biometric Access Control', 'Office Access Control', 'Apartment Access Control',
        'Fire Release Access Control', 'Access Control Upgrade', 'Proximity Reader Installation',
        'Multi Door Access Control', 'HID Access Control', 'Salto Access System Support',
    ],
    'door-entry' => [
        'Video Door Entry', 'Audio Door Entry', 'Door Entry System Installation', 'Door Entry Repair',
        'Block Door Entry Upgrade', 'GSM Door Entry', 'IP Door Entry System', 'Landlord Door Entry',
        'Trade Button Door Entry', 'Door Entry Handset Replacement', 'Panel Replacement Door Entry',
        'Multi Tenant Door Entry', 'Video Door Entry Flats', 'Door Entry Maintenance Contract',
    ],
    'intercoms' => [
        'Intercom System Installation', 'Office Intercom System', 'Factory Intercom', 'Master Station Intercom',
        'Wireless Intercom Install', 'Intercom Repair', 'School Intercom System', 'Warehouse Intercom',
        'Door Intercom Upgrade', 'Commercial Intercom Package', 'Intercom Cabling', 'Intercom Handset Supply',
    ],
    'landlord-compliance' => [
        'Landlord Compliance Package', 'Void Property Compliance', 'HMO Compliance Package',
        'Landlord Safety Certificates Bundle', 'Portfolio Compliance Management', 'Letting Compliance Check',
        'Landlord Fire and Electrical Package', 'New Tenancy Compliance Pack', 'Annual Landlord Compliance',
        'Multi Property Landlord Package', 'Agent Compliance Support', 'Landlord Certificate Bundle North West',
        'Rent Ready Compliance Package', 'Landlord Audit Pack',
    ],
    'facilities-management' => [
        'Facilities Management Compliance', 'PPM Contract Compliance', 'Multi Site FM Support',
        'Reactive Maintenance Call Out', 'FM Electrical Support', 'FM Fire Safety Support',
        'Building Services PPM', 'Facilities Helpdesk Engineer', 'Estate Compliance Scheduling',
        'Commercial PPM Package', 'Out of Hours FM Support', 'FM Compliance Reporting',
    ],
    'building-maintenance' => [
        'Building Maintenance Contract', 'Reactive Building Repairs', 'Block Maintenance Services',
        'Commercial Building Maintenance', 'Landlord Building Maintenance', 'Handyman Plus Compliance',
        'Estate Maintenance Package', 'Planned Building Maintenance', 'Fabric Repairs Package',
        'Communal Area Maintenance', 'Building Defect Remedial',
    ],
    'project-management' => [
        'Construction Project Coordination', 'Multi Trade Project Management', 'Refurbishment Project Manager',
        'Landlord Refurb Project Support', 'Compliance Project Coordination', 'Site Project Supervision',
        'Programme Management Building Works', 'Fit Out Project Support', 'Void Works Project Management',
    ],
    'building-surveys' => [
        'Building Condition Survey', 'Snagging Survey', 'Pre Purchase Building Survey Support',
        'Landlord Condition Report', 'Dilapidations Support Survey', 'Void Property Survey',
        'Defect Survey', 'Schedule of Condition', 'Pre Works Survey', 'Commercial Condition Survey',
    ],
    'cdm-support' => [
        'CDM Support Small Works', 'Pre Construction Information Pack', 'Construction Phase Plan Support',
        'CDM RAMS Coordination', 'Principal Contractor Support Small Sites', 'CDM Handover Pack',
        'Health and Safety File Support', 'CDM 2015 Compliance Support',
    ],
    'property-refurbishment-pm' => [
        'Property Refurbishment Management', 'Kitchen Bathroom Refurb Coordination', 'Full House Refurb Package',
        'Investor Refurb Project', 'Void Refurb Management', 'Staged Refurbishment Plan',
        'Multi Room Renovation Coordination',
    ],
    'compliance-consultancy' => [
        'Fire Compliance Consultancy', 'Property Compliance Advice', 'Portfolio Compliance Audit',
        'Landlord Compliance Consultancy', 'Building Compliance Action Plan', 'Regulatory Compliance Review',
        'Multi Site Compliance Strategy', 'Insurance Compliance Support',
    ],
    'kitchens' => [
        'Kitchen Fitting', 'Kitchen Installation', 'Kitchen Supply and Fit', 'HMO Kitchen Fitting',
        'Rental Kitchen Refurb', 'Kitchen Worktop Fitting', 'Kitchen Unit Installation', 'New Kitchen Fit Out',
        'Kitchen Sink Installation', 'Kitchen Remodel', 'Apartment Kitchen Fitting', 'Landlord Kitchen Upgrade',
        'Kitchen First Fix Coordination', 'Bespoke Kitchen Fitting', 'Flat Pack Kitchen Install',
        'Kitchen Splashback Tiling', 'Utility Room Fitting', 'Kitchen Replacement North West',
    ],
    'bathrooms' => [
        'Bathroom Fitting', 'Bathroom Installation', 'Wet Room Installation', 'Bathroom Refurbishment',
        'Landlord Bathroom Upgrade', 'HMO Bathroom Fitting', 'Shower Installation', 'Bathroom Suite Fitting',
        'Bathroom Tiling Package', 'Bathroom Waterproofing', 'Disabled Bathroom Adaptation Support',
        'En Suite Installation', 'Bathroom Extractor Fan Install', 'Commercial Washroom Fit Out',
        'Bathroom Plumbing and Fit', 'Full Bathroom Renovation',
    ],
    'renovation' => [
        'Property Renovation', 'House Renovation', 'Flat Renovation', 'Void Property Renovation',
        'Landlord Renovation Package', 'Full House Refurbishment', 'Light Touch Renovation',
        'Investment Property Renovation', 'Kitchen and Bathroom Renovation', 'Cosmetic Renovation Package',
        'Structural Ready Renovation', 'End of Tenancy Renovation', 'Commercial Unit Renovation',
        'Period Property Renovation Support',
    ],
    'plastering' => [
        'Plastering Services', 'Skim Coat Plastering', 'Plasterboard Installation', 'Patch Plastering',
        'Full Room Plastering', 'Ceiling Plastering', 'Dot and Dab Boarding', 'Re-plaster After Damp',
        'Commercial Plastering', 'Landlord Plastering Repairs', 'Artex Cover Up Plastering',
        'Smooth Finish Plastering', 'Plastering for Renovation',
    ],
    'tiling' => [
        'Wall Tiling', 'Floor Tiling', 'Bathroom Tiling', 'Kitchen Tiling', 'Porcelain Tile Installation',
        'Ceramic Tile Fitting', 'Tile Removal and Retile', 'Wet Room Tiling', 'Commercial Floor Tiling',
        'Splashback Tiling', 'Large Format Tiling', 'Landlord Tiling Package',
    ],
    'painting-decorating' => [
        'Painting and Decorating', 'Interior Painting', 'Exterior Painting', 'Void Redecoration',
        'Landlord Painting Package', 'Wallpapering Services', 'Commercial Painting', 'Ceiling Painting',
        'Woodwork Painting', 'Spray Painting Walls', 'Full House Decorating', 'End of Tenancy Painting',
    ],
    'joinery' => [
        'Joinery Services', 'Custom Cupboards', 'Built In Wardrobes', 'Door Hanging', 'Skirting Boards Fitting',
        'Architrave Installation', 'Boxing In Pipes', 'Staircase Joinery Repairs', 'Window Board Fitting',
        'Bespoke Shelving', 'Landlord Joinery Repairs', 'Fire Door Joinery Support',
    ],
    'carpentry' => [
        'Carpentry Services', 'First Fix Carpentry', 'Second Fix Carpentry', 'Stud Wall Construction',
        'Timber Frame Repairs', 'Floor Joist Repairs', 'Door Frame Installation', 'Site Carpentry Package',
        'Loft Floor Carpentry', 'Partition Carpentry',
    ],
    'flooring' => [
        'Laminate Flooring Installation', 'Vinyl Flooring Fitting', 'Engineered Wood Flooring',
        'Carpet Preparation Works', 'Commercial Vinyl Flooring', 'Floor Levelling', 'Underlay Installation',
        'Landlord Flooring Package', 'Kitchen Flooring Installation', 'Bathroom Vinyl Flooring',
        'Solid Wood Floor Fitting', 'Click Floor Installation',
    ],
    'plumbing' => [
        'Plumbing Services', 'Emergency Plumber', 'Bathroom Plumbing', 'Kitchen Plumbing', 'Leak Detection Support',
        'Radiator Installation', 'Toilet Installation', 'Tap Replacement', 'Pipework Replacement',
        'Landlord Plumbing Repairs', 'Blocked Waste Support', 'Water Softener Installation Support',
        'Outside Tap Installation', 'Shower Pump Installation',
    ],
    'heating' => [
        'Boiler Installation', 'Combi Boiler Install', 'Radiator Replacement', 'Heating System Flush',
        'Smart Thermostat Installation', 'Central Heating Upgrade', 'Boiler Repair Support',
        'Landlord Heating Upgrade', 'System Boiler Installation', 'Underfloor Heating Support',
        'Heating Controls Upgrade', 'Powerflush Heating System',
    ],
    'roofing' => [
        'Roof Repairs', 'Flat Roof Installation', 'Guttering Replacement', 'Roof Tile Replacement',
        'Chimney Repairs', 'Roof Leak Repair', 'Felt Roofing', 'EPDM Flat Roof', 'Landlord Roof Repairs',
        'Fascia and Soffit Replacement', 'Roof Inspection', 'Emergency Roof Repair',
    ],
    'brickwork' => [
        'Brickwork Repairs', 'Repointing Services', 'Brick Replacement', 'Garden Wall Building',
        'Opening Forming Brickwork', 'Chimney Brickwork', 'Landlord Brickwork Repairs', 'Masonry Crack Repair',
        'Blockwork Partitions', 'External Brickwork Making Good',
    ],
    'dry-lining' => [
        'Dry Lining Installation', 'Metal Stud Partition', 'Acoustic Dry Lining', 'Fire Rated Boarding',
        'Plasterboard Partition Walls', 'Ceiling Dry Lining', 'Dot and Dab Dry Lining',
        'Commercial Dry Lining', 'Shaft Wall Boarding Support', 'Insulation Backed Dry Lining',
    ],
    'loft-conversions' => [
        'Loft Conversion', 'Dormer Loft Conversion', 'Velux Loft Conversion', 'Loft Stairs Installation',
        'Loft Insulation for Conversion', 'Loft Conversion Building Works', 'Fire Door for Loft Conversion',
        'Loft Floor Strengthening', 'Hip to Gable Conversion Support', 'Building Control Loft Support',
    ],
    'extensions' => [
        'Home Extension Building', 'Single Storey Extension', 'Rear Extension Build', 'Side Return Extension',
        'Extension Groundworks Package', 'Extension First Fix Package', 'Orangery Base Works',
        'Kitchen Extension Build', 'Landlord Extension Support', 'Extension Envelope Works',
    ],
    'insulation' => [
        'Loft Insulation', 'Internal Wall Insulation', 'Floor Insulation', 'Room in Roof Insulation',
        'Insulation Upgrade Package', 'Sound Insulation Boarding', 'Pipe Insulation', 'EPC Insulation Works',
        'Landlord Insulation Upgrade', 'Cold Bridging Insulation Works',
    ],
    'damp-proofing' => [
        'Damp Proofing', 'Rising Damp Treatment', 'Penetrating Damp Repairs', 'Tanking System Installation',
        'Damp Survey Support', 'Condensation Control Works', 'Basement Tanking Support',
        'Landlord Damp Remedial', 'Chemical DPC Injection', 'Replaster After Damp Proofing',
    ],
    'windows-doors' => [
        'Window Replacement', 'uPVC Window Fitting', 'Composite Door Installation', 'Fire Door Set Supply',
        'French Door Installation', 'Bi Fold Door Support', 'Window Cill Replacement', 'Door Lock Upgrade',
        'Landlord Window Replacement', 'Secondary Glazing Support', 'Patio Door Installation',
    ],
    'rendering' => [
        'External Rendering', 'Render Repairs', 'Monocouche Render', 'Silicone Render System',
        'Pebbledash Repair', 'House Rendering Package', 'Render Crack Repair', 'Landlord Render Repairs',
        'Weatherproof Coatings', 'Through Colour Render',
    ],
    'groundworks' => [
        'Groundworks Contractor', 'Foundation Dig', 'Drainage Installation', 'Patio Base Preparation',
        'Hardstanding Installation', 'Trench Dig Services', 'Soakaway Installation Support',
        'Extension Groundworks', 'Driveway Base Works', 'Foul Drainage Alterations Support',
    ],
    'landscaping' => [
        'Hard Landscaping', 'Patio Installation', 'Fence Installation', 'Garden Walling',
        'Block Paving Installation', 'Turf Laying', 'Garden Clearance for Works', 'Path Installation',
        'Landlord External Works', 'Decking Installation',
    ],
    'electrics-first-fix' => [
        'First Fix Electrical', 'New Build First Fix Electrics', 'Renovation First Fix Wiring',
        'Consumer Unit First Fix', 'Cable Runs First Fix', 'Back Box Installation', 'Lighting First Fix',
        'Socket First Fix', 'Conversion First Fix Electrics', 'Loft Conversion Electrics First Fix',
    ],
    'commercial-fit-out' => [
        'Commercial Fit Out', 'Office Fit Out', 'Retail Fit Out Support', 'Partition Fit Out',
        'Cat A Fit Out Support', 'Cat B Fit Out Package', 'Shop Fit Out Works', 'Warehouse Office Fit Out',
        'Light Commercial Refurbishment', 'Reception Fit Out', 'Meeting Room Fit Out',
    ],
];

// ---------------------------------------------------------------------------
// Write services + meta + categories
// ---------------------------------------------------------------------------
saveJsonData('services', $services);
saveJsonData('service-meta', $meta);
saveJsonData('service-categories', $categories);

// ---------------------------------------------------------------------------
// Merge keywords with unique content
// ---------------------------------------------------------------------------
$existing = loadJsonData('keywords', []);
if (!is_array($existing)) {
    $existing = [];
}

$serviceCopy = [];
foreach ($meta as $slug => $m) {
    $serviceCopy[$slug] = [
        'std' => $m['standards'] ?? 'UK standards and manufacturer guidance',
        'docs' => 'handover packs, certificates and job records',
        'who' => 'landlords, homeowners, FM teams and commercial clients',
        'verb' => 'survey, deliver and document',
        'name' => $services[$slug] ?? $slug,
    ];
}

// Tune verbs for families
foreach (['fire-alarms', 'emergency-lighting', 'aov-air-handling', 'fire-extinguishers', 'fire-doors', 'fire-stopping', 'fire-suppression', 'sprinkler-systems', 'dry-risers', 'fire-signage', 'evacuation-alerts', 'kitchen-fire-suppression', 'fire-compartmentation'] as $s) {
    if (isset($serviceCopy[$s])) {
        $serviceCopy[$s]['verb'] = 'design, install, service and certify';
        $serviceCopy[$s]['who'] = 'landlords, managing agents, commercial occupiers and facilities managers';
        $serviceCopy[$s]['docs'] = 'service certificates, assessment reports and compliance documentation';
    }
}
if (isset($serviceCopy['fire-risk-assessments'])) {
    $serviceCopy['fire-risk-assessments']['verb'] = 'assess, report and prioritise';
    $serviceCopy['fire-risk-assessments']['docs'] = 'suitable and sufficient FRA reports with action plans';
}
foreach (['kitchens', 'bathrooms', 'renovation', 'plastering', 'tiling', 'painting-decorating', 'joinery', 'carpentry', 'flooring', 'plumbing', 'heating', 'roofing', 'brickwork', 'dry-lining', 'loft-conversions', 'extensions', 'insulation', 'damp-proofing', 'windows-doors', 'rendering', 'groundworks', 'landscaping', 'commercial-fit-out', 'electrics-first-fix'] as $s) {
    if (isset($serviceCopy[$s])) {
        $serviceCopy[$s]['verb'] = 'plan, install and finish';
        $serviceCopy[$s]['who'] = 'homeowners, landlords, investors and light commercial clients';
        $serviceCopy[$s]['docs'] = 'scope sheets, warranties and completion photos';
    }
}
foreach (['landlord-compliance', 'facilities-management', 'building-maintenance', 'project-management', 'building-surveys', 'cdm-support', 'property-refurbishment-pm', 'compliance-consultancy'] as $s) {
    if (isset($serviceCopy[$s])) {
        $serviceCopy[$s]['verb'] = 'plan, coordinate and report';
        $serviceCopy[$s]['who'] = 'landlords, agents, FM teams and portfolio owners';
        $serviceCopy[$s]['docs'] = 'action plans, schedules and compliance reports';
    }
}

$openers = [
    'Looking for expert {kw} across Greater Manchester and the North West?',
    'Need reliable {kw} from a Stockport-based team with fixed-price quotes?',
    'Searching for professional {kw} with clear scope and documentation?',
    'Planning {kw} for a home, rental portfolio or commercial site?',
    'Want {kw} delivered to current UK standards with local engineers?',
];
$middles = [
    'Icomply Property Services {verb} {kw} as part of our {svc} offering, working to {std}.',
    'Our team {verb} {kw} for {who}, with {docs} on completion where applicable.',
    'From first survey to handover, we {verb} {kw} with transparent pricing after scope is agreed.',
    'We handle {kw} alongside related {svc} works so one contractor can reduce site disruption.',
];
$closers = [
    'Based in Offerton, Stockport (SK2 5DE), we cover 150+ towns across the North West.',
    'Tell us your postcode, property type and timescales — we aim to respond on business days within 2 hours.',
    'Ask about multi-property packages if you manage several sites or a void programme.',
    'Browse local pages for your town or request a combined quote with related services.',
];

function icSlug(string $name): string {
    $s = strtolower(trim($name));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string)$s, '-');
}

function buildUniqueContent(string $name, string $serviceSlug, array $copy, int $salt): array {
    $svc = $copy['name'] ?? $serviceSlug;
    $std = $copy['std'] ?? 'UK standards';
    $docs = $copy['docs'] ?? 'documentation';
    $who = $copy['who'] ?? 'clients';
    $verb = $copy['verb'] ?? 'deliver';

    $openers = [
        "Looking for expert {$name} across Greater Manchester and the North West?",
        "Need reliable {$name} from a Stockport-based team with fixed-price quotes?",
        "Searching for professional {$name} with clear scope and documentation?",
        "Planning {$name} for a home, rental portfolio or commercial site?",
        "Want {$name} delivered to current UK standards with local engineers?",
    ];
    $middles = [
        "Icomply Property Services {$verb} {$name} as part of our {$svc} offering, working to {$std}.",
        "Our team {$verb} {$name} for {$who}, with {$docs} on completion where applicable.",
        "From first survey to handover, we {$verb} {$name} with transparent pricing after scope is agreed.",
        "We handle {$name} alongside related {$svc} works so one contractor can reduce site disruption.",
    ];
    $closers = [
        'Based in Offerton, Stockport (SK2 5DE), we cover 150+ towns across the North West.',
        'Tell us your postcode, property type and timescales — we aim to respond on business days within 2 hours.',
        'Ask about multi-property packages if you manage several sites or a void programme.',
        'Browse local pages for your town or request a combined quote with related services.',
    ];
    $o = $openers[$salt % count($openers)];
    $m = $middles[($salt + 1) % count($middles)];
    $c = $closers[($salt + 2) % count($closers)];
    $intro = "{$o} {$m}";
    $body = "{$m} {$c} Typical jobs include survey, agreed works, quality checks and paperwork suitable for landlords, insurers and facilities managers. Local engineers attend from Stockport with North West coverage including Manchester, Bolton, Liverpool, Preston, Chester and surrounding towns.";
    $meta = "{$name} across the North West. {$svc} from Icomply Property Services in Stockport. Fixed-price quotes, local engineers, full documentation.";
    $seo = strtolower("{$name}, {$name} North West, {$name} Manchester, {$name} Stockport, {$svc}, property services Greater Manchester");
    $focus = [
        "Survey and fixed-price quote for {$name}",
        "Local North West engineers from Stockport (SK2)",
        "Documentation aligned to {$std}",
        "Suitable for {$who}",
    ];
    $faq = [
        ["What does {$name} include?", "Scope is confirmed after survey — typically labour, agreed materials and documentation for {$name} under our {$svc} service."],
        ["Do you cover my town for {$name}?", 'Yes — we cover 150+ towns across Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria from Stockport.'],
        ["How quickly can you start {$name}?", 'Many jobs can be surveyed same week depending on capacity and access. Emergency and priority works are prioritised where possible.'],
        ["Do you provide certificates for {$name}?", "Where the work type requires certification or a formal report, we issue the relevant paperwork with your job pack."],
    ];
    return compact('intro', 'body', 'meta', 'seo', 'focus', 'faq');
}

$added = 0;
$enriched = 0;
$i = 0;
foreach ($kwSeed as $serviceSlug => $names) {
    if (!isset($services[$serviceSlug])) {
        continue;
    }
    $copy = $serviceCopy[$serviceSlug] ?? [];
    $firstSlug = null;
    foreach ($names as $idx => $name) {
        $slug = icSlug($name);
        if ($firstSlug === null) {
            $firstSlug = $slug;
        }
        $related = $idx > 0 ? icSlug($names[max(0, $idx - 1)]) : ($names[1] ?? $name);
        $related = icSlug(is_string($related) ? $related : $name);
        $salt = crc32($slug . $serviceSlug) & 0xffff;

        if (!isset($existing[$slug])) {
            $content = buildUniqueContent($name, $serviceSlug, $copy, $salt);
            $existing[$slug] = [
                'name' => $name,
                'service' => $serviceSlug,
                'related' => $related,
                'intro' => $content['intro'],
                'body' => $content['body'],
                'meta_desc' => $content['meta'],
                'seo_keywords' => $content['seo'],
                'focus_points' => $content['focus'],
                'faq' => $content['faq'],
            ];
            $added++;
        } else {
            // Keep existing; fill missing SEO fields only
            $row = $existing[$slug];
            if (empty($row['service'])) {
                $row['service'] = $serviceSlug;
            }
            if (empty($row['name'])) {
                $row['name'] = $name;
            }
            if (empty($row['intro']) || empty($row['body']) || empty($row['meta_desc'])) {
                $content = buildUniqueContent($row['name'] ?? $name, $row['service'] ?? $serviceSlug, $copy, $salt);
                foreach (['intro' => 'intro', 'body' => 'body', 'meta_desc' => 'meta', 'seo_keywords' => 'seo'] as $k => $ck) {
                    if (empty($row[$k])) {
                        $row[$k] = $content[$ck === 'meta' ? 'meta' : ($ck === 'seo' ? 'seo' : $ck)];
                    }
                }
                if (empty($row['focus_points'])) {
                    $row['focus_points'] = $content['focus'];
                }
                if (empty($row['faq'])) {
                    $row['faq'] = $content['faq'];
                }
                $enriched++;
            }
            $existing[$slug] = $row;
        }
        $i++;
    }
}

// Sort keys for stable diffs
ksort($existing);
saveJsonData('keywords', $existing);

echo "Services: " . count($services) . "\n";
echo "Categories: " . count($categories) . "\n";
echo "Keywords total: " . count($existing) . " (added {$added}, enriched {$enriched})\n";
echo "Done.\n";
