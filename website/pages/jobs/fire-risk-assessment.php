<?php
/**
 * Conversion landing — fire risk assessment for HMOs, shared housing and other multi-occupied buildings.
 * Does not replace /pages/services/fire-risk-assessments or /pages/fire-risk-assessment.
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/landlord-guide-prices.php';
$fraFrom = icomplyLandlordGuideMoney('fra');

$pageTitle = 'Fire risk assessment for HMOs | Stockport & MCR';
$metaTitleExact = true;
$metaDesc = 'FRA support for HMO and shared housing landlords in Stockport and Greater Manchester. Competent assessment — guide from ' . $fraFrom . ', fixed quote after scope.';
$ogTitle = 'Fire risk assessment for HMOs';
$ogDescription = 'FRA for HMO landlords — guide from ' . $fraFrom . '. Not legal advice.';
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/services/fire-risk-assessments.jpg';
$ogImageAlt = 'Fire risk assessment for HMOs and shared housing — iComply Property Services';
$canonicalUrl = url('/pages/jobs/fire-risk-assessment');
$metaKeywords = 'fire risk assessment HMO, FRA landlords Manchester, fire risk assessment Stockport';
$omitPriceRange = true;

$landing = [
    'priceKey' => 'fra',
    'kicker' => 'HMOs, shared housing & multi-occupied buildings',
    'h1' => 'Fire risk assessment for HMOs: what Stockport and Manchester landlords should have on file',
    'lede' => 'Houses in multiple occupation are not the same fire-safety problem as a single self-contained let. Shared escape routes, denser occupation and building-wide detection mean landlords and agents need a clear **fire risk assessment (FRA)** — and the paperwork to show what was found and what was done next.',
    'note' => 'General marketing education, not legal advice. Check current GOV.UK fire safety guidance for the responsible person and your local council’s HMO pages. This page is England-focused (Stockport and Greater Manchester).',
    'proof' => [
        'Stockport-based',
        'Greater Manchester / North West',
        'Guide from ' . $fraFrom,
    ],
    'crumbs' => [
        ['name' => 'Services', 'href' => '/pages/services'],
        ['name' => 'Fire risk assessment', 'href' => '/pages/jobs/fire-risk-assessment', 'current' => true],
    ],
    'scopeTitle' => 'Why shared housing is scoped differently',
    'scope' => [
        'In a typical shared house or bedsit-style HMO, people rely on common corridors, stairs and exits. Detection often serves the whole building. That is why fire risk for HMOs is usually treated more carefully than for a simple single-family let.',
        'Where the Regulatory Reform (Fire Safety) Order applies — commonly including **HMO common parts** and other multi-occupied buildings — the responsible person needs a **suitable and sufficient** fire risk assessment, with significant findings acted on and the assessment kept under review. Applicability is fact-specific. Some commercial and other multi-occupied buildings sit in that same “check whether the Order applies” category. Do not assume every single self-contained let needs the same FRA as an HMO, and do not assume “no FRA” for a multi-occupied building without checking official guidance.',
        'An HMO licence in Stockport, Manchester or another Greater Manchester borough can require detection, emergency lighting, fire doors or management standards on top of national rules. Those conditions are set by that council. They are not automatically the same in every borough, and a licence is not proof that the fire file is complete. The existing [fire risk assessment service](/pages/services/fire-risk-assessments) is the service page for this work — this landing does not replace it.',
    ],
    'scopeItems' => [
        [
            'title' => 'What a suitable FRA looks at',
            'text' => 'At a high level: hazards (ignition, fuel and how the building is used), people at risk, existing measures (detection, escape, doors as present, emergency lighting where fitted, management and resident information), findings and actions, and when to review. This page does not claim compliance with a named British Standard for a typical job. The competent person confirms the guidance used on that building.',
        ],
        [
            'title' => 'Who should carry it out',
            'text' => 'Someone competent for the size, layout and risk of the building. Do not treat a DIY checklist as a finished HMO FRA. This page uses competent-person language only. It does not claim BAFE, IFE or any other badge.',
        ],
        [
            'title' => 'Documents to keep',
            'text' => 'The full FRA, an action log with evidence, related alarm or emergency-lighting records where those systems exist, the HMO licence and conditions, a review diary, and an agreed agent handback. Keep fire documents with the gas record and the EICR.',
        ],
        [
            'title' => 'Related works, quoted separately',
            'text' => 'An FRA is not automatically an install package. Fire alarms, emergency lighting and extinguishers are scoped and quoted separately (POA) unless they are written into an agreed package. See the [fire alarm service](/pages/services/fire-alarms) and [emergency lighting service](/pages/services/emergency-lighting).',
        ],
    ],
    'stepsTitle' => 'From enquiry to the assessment',
    'steps' => [
        [
            'title' => 'Tell us the building',
            'text' => 'HMO size, number of floors, borough (Stockport, Manchester or other North West) and postcode. Access to common parts, any previous FRA, alarm and emergency-lighting records, and licence conditions help the scope.',
        ],
        [
            'title' => 'Fixed quote after scope',
            'text' => 'FRA alone, or a coordinated plan with related checks where diary capacity allows. Guide from **' . $fraFrom . '**, then a **fixed quote** once scope is agreed. Related installs stay separate unless included in that scope.',
        ],
        [
            'title' => 'Assess and file the actions',
            'text' => 'You receive the assessment and a findings trail for the property file. Review when layout, occupation or significant works change, and follow the assessor’s recommendation — not a single invented interval.',
        ],
    ],
    'shop' => [
        'title' => 'Supply the detection kit, or ask us to supply and fit',
        'text' => 'Landlords and agents usually want the assessment and, where needed, supply and fit on one quote. Trades who are fitting the job themselves can browse fire detection materials on the trade shop. Emergency lighting install is quoted as a service; do not treat the shop link as a finished EL design.',
        'href' => '/shop/fire/',
        'label' => 'Fire supplies',
    ],
    'faqs' => [
        [
            'q' => 'Do all landlords need a fire risk assessment?',
            'a' => 'Not in identical form for every property. Where the Fire Safety Order applies — often including HMO common parts and other multi-occupied buildings — the responsible person needs a suitable and sufficient FRA. Single self-contained lets without shared common parts may sit differently. Check GOV.UK guidance and take competent advice if you are unsure.',
        ],
        [
            'q' => 'Is an HMO licence enough without an FRA?',
            'a' => 'Usually no, as a complete answer. Licensing and fire risk assessment overlap but they are not the same document trail. Local licence conditions can add requirements. They do not automatically replace a suitable FRA where the Order applies.',
        ],
        [
            'q' => 'Can I write the FRA myself?',
            'a' => 'For most HMOs, treating a DIY checklist as your FRA is a weak evidence trail. Use a competent person. Floor plans, prior certificates and access notes help you prepare. They are not a substitute for competence.',
        ],
        [
            'q' => 'How often should an FRA be reviewed?',
            'a' => 'Review when something material changes — layout, occupation, significant works, or after a fire or near-miss — and at intervals the assessor recommends for that building. This page does not invent an “every X years” rule.',
        ],
        [
            'q' => 'Do you cover commercial buildings as well as HMOs?',
            'a' => 'Ask us to scope it. Commercial and other multi-occupied buildings are fact-specific under the Fire Safety Order. Tell us the building type, floors and borough. The guide is ' . $fraFrom . ' where the assessment is in scope; the fixed quote follows that scope. The service page is linked above; this landing does not replace it.',
        ],
        [
            'q' => 'Will you quote a fixed price up front?',
            'a' => 'The published guide is ' . $fraFrom . '. You get a fixed price after scope is agreed. Alarms, emergency lighting and extinguishers are quoted separately unless they are included in an agreed package.',
        ],
    ],
    'links' => [
        ['href' => '/pages/services/fire-risk-assessments', 'label' => 'Fire risk assessment service'],
        ['href' => '/pages/services/fire-alarms', 'label' => 'Fire alarms'],
        ['href' => '/pages/services/emergency-lighting', 'label' => 'Emergency lighting'],
        ['href' => '/pages/services/smoke-co-alarms', 'label' => 'Smoke and CO alarms'],
        ['href' => '/pages/fire-risk-assessment', 'label' => 'Fire risk assessment hub'],
        ['href' => '/shop/fire/', 'label' => 'Fire supplies (trade shop)'],
        ['href' => '/pages/jobs/eicr', 'label' => 'EICR for landlords'],
        ['href' => '/pages/jobs/gas-safety-cp12', 'label' => 'Gas safety certificate (CP12)'],
        ['href' => '/pages/jobs/landlord-compliance', 'label' => 'Landlord compliance package'],
        ['href' => '/pages/landlords', 'label' => 'Landlords'],
        ['href' => '/pages/areas', 'label' => 'Areas we cover'],
        ['href' => '/contact', 'label' => 'Get a quote'],
    ],
    'sources' => [
        [
            'href' => 'https://www.gov.uk/workplace-fire-safety-your-responsibilities',
            'label' => 'GOV.UK — fire safety responsibilities',
        ],
    ],
    'ctaTitle' => 'Request an FRA quote',
    'ctaText' => 'Tell us HMO size, floors and borough (Stockport, Manchester or other North West), or describe a commercial / multi-occupied building so we can say whether it is in scope. Guide from **' . $fraFrom . '**. The fixed price follows what is included.',
];

require SITE_ROOT . '/includes/conversion-landing.php';
