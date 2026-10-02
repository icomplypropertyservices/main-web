<?php
/**
 * Conversion landing — landlord compliance package (EICR, gas, FRA and alarms on one schedule).
 * Does not replace /pages/services/landlord-compliance or /pages/packages.
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/landlord-guide-prices.php';
$packFrom = icomplyLandlordGuideMoney('bundle');
$eicrFrom = icomplyLandlordGuideMoney('eicr');
$gasFrom = icomplyLandlordGuideMoney('gas');
$fraFrom = icomplyLandlordGuideMoney('fra');

$pageTitle = 'Landlord compliance & void certificates | iComply';
$metaTitleExact = true;
$metaDesc = 'EICR, gas and FRA on one schedule for Stockport & GM portfolios. Guide from ' . $packFrom . '. Fixed quote after scope.';
$ogTitle = 'Landlord compliance & void certificates';
$ogDescription = 'EICR ' . $eicrFrom . ', gas ' . $gasFrom . ', FRA ' . $fraFrom . '. Pack guide ' . $packFrom . '. Fixed quote after scope.';
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/services/landlord-compliance.jpg';
$ogImageAlt = 'Landlord compliance documents — iComply Property Services, Stockport';
$canonicalUrl = url('/pages/jobs/landlord-compliance');
$metaKeywords = 'landlord compliance package, EICR gas FRA, void certificates, Stockport, Greater Manchester';
$omitPriceRange = true;

$landing = [
    'priceKey' => 'bundle',
    'kicker' => 'Landlords, agents & small portfolios',
    'h1' => 'Landlord essentials — one schedule, one documentation pack',
    'lede' => 'One coordinated visit plan. One documentation pack for landlords and agents. **Fixed price after we agree scope.** Typically **EICR**, **gas safety (CP12 / Landlord Gas Safety Record)**, and **core fire and alarm checks as scoped** — with an FRA where the building needs one.',
    'note' => '“Landlord essentials” is a commercial way of packaging typical certificate work. It is not a statutory product name and it does not guarantee that every duty on a given property is covered. Inclusions are agreed in writing before a fixed price. This page is general information, not legal advice.',
    'proof' => [
        'Stockport-based',
        'Greater Manchester / North West',
        'Guide from ' . $packFrom,
    ],
    'crumbs' => [
        ['name' => 'Services', 'href' => '/pages/services'],
        ['name' => 'Landlord compliance package', 'href' => '/pages/jobs/landlord-compliance', 'current' => true],
    ],
    'scopeTitle' => 'What is typically discussed',
    'scope' => [
        'iComply Property Services is a Stockport-based team helping private landlords, letting agents and small portfolios across Greater Manchester and the wider North West bring the usual certificate set onto one schedule.',
        'The pack guide is **' . $packFrom . '** for **EICR (' . $eicrFrom . ')**, **gas safety (' . $gasFrom . ')** and **FRA (' . $fraFrom . ')** on one schedule. That is a guide, not the fixed quote. Property type, access and anything outside those three items change the job.',
        'The wider [landlord compliance service](/pages/services/landlord-compliance) stays the service page. This landing is the quote route for a coordinated schedule. It does not replace that page or the [packages hub](/pages/packages).',
    ],
    'scopeItems' => [
        [
            'title' => 'EICR',
            'text' => 'Electrical Installation Condition Report for the rented dwelling’s fixed installation. England PRS rhythm is commonly at least every 5 years, or sooner if the report requires it. See [EICR for landlords](/pages/jobs/eicr).',
        ],
        [
            'title' => 'Gas safety',
            'text' => 'Landlord Gas Safety Record (CP12 in common landlord language) where gas appliances or flues apply — typically an annual check by a Gas Safe registered engineer. See [gas safety (CP12)](/pages/jobs/gas-safety-cp12).',
        ],
        [
            'title' => 'Fire, alarms and FRA',
            'text' => 'Core fire and alarm checks as relevant to the property and any licence conditions. A suitable FRA is confirmed per property where the Fire Safety Order applies — notably many HMOs and common parts — and is not assumed for every single let. See [fire risk assessment](/pages/jobs/fire-risk-assessment).',
        ],
        [
            'title' => 'Not automatic',
            'text' => 'Full fire-alarm installs, emergency lighting programmes, extinguisher contracts, asbestos or Legionella surveys, and major electrical remedials after an unsatisfactory EICR are separate or optional. They are quoted POA when you want them on the same plan.',
        ],
    ],
    'stepsTitle' => 'How the schedule works',
    'steps' => [
        [
            'title' => 'Tell us the job',
            'text' => 'Postcodes, property types (flat, house, HMO), which certificates or checks are due, access notes, and any agent handback requirements.',
        ],
        [
            'title' => 'Fixed quote after scope',
            'text' => 'We confirm what is included and what is quoted separately. The pack guide is **' . $packFrom . '**. We aim to respond within 2 hours on business days once we have postcodes and property types. That is an aim, not a contractual SLA.',
        ],
        [
            'title' => 'Deliver and file the pack',
            'text' => 'Visits follow the agreed schedule. You receive the written reports for what was scoped — for example the EICR, the Landlord Gas Safety Record, and any fire or alarm evidence included — so they can sit in one property folder. Same-week coordination depends on capacity.',
        ],
    ],
    'shop' => [
        'title' => 'Prefer one invoice for alarms?',
        'text' => 'Landlords and agents can ask us to supply and fit alarms or emergency lighting from the trade shop, scoped with the certificate visit. Trades who are fitting detection themselves can browse the fire supplies hub. Either way the install quote stays POA until scope is agreed.',
        'href' => '/shop/fire/',
        'label' => 'Fire supplies',
    ],
    'faqs' => [
        [
            'q' => 'How much does the landlord package cost?',
            'a' => 'The published guide is ' . $packFrom . ' for EICR, gas safety and FRA together. You then receive a fixed quote. Property type, access and any work outside those three items change the job.',
        ],
        [
            'q' => 'Is this the same as a wider compliance brief?',
            'a' => 'No. This package focuses on the core landlord certificate conversation — typically EICR, gas safety, and core fire or alarm checks as scoped, with FRA where agreed. A wider brief (for example asbestos, Legionella or a larger fire package) is a separate conversation. Either way: scope first, fixed price after.',
        ],
        [
            'q' => 'Do you always include a fire risk assessment?',
            'a' => 'Only where it is agreed in scope for that building. FRA needs depend on occupation and whether the Fire Safety Order applies. Single self-contained lets and HMOs are not identical.',
        ],
        [
            'q' => 'Are you Gas Safe, NICEIC or BAFE registered?',
            'a' => 'This page does not claim firm accreditations. Engineers carrying out gas work must be Gas Safe registered where that duty applies. Landlords and tenants can check ID on the public register.',
        ],
        [
            'q' => 'Can agents use this for several landlords?',
            'a' => 'Yes. Tell us you are an agent, how many properties, and how you need certificates handed back. The ' . $packFrom . ' guide is per agreed schedule, not a portfolio total. The fixed price follows scope.',
        ],
        [
            'q' => 'Will everything happen in one visit?',
            'a' => 'Often we can coordinate certificates on one schedule to reduce access visits. Same-day or same-week completion depends on scope and capacity. We will say so when the diary needs a split plan.',
        ],
    ],
    'links' => [
        ['href' => '/pages/services/landlord-compliance', 'label' => 'Landlord compliance service'],
        ['href' => '/pages/services/electrical', 'label' => 'Electrical services'],
        ['href' => '/pages/services/gas-systems', 'label' => 'Gas services'],
        ['href' => '/pages/services/fire-risk-assessments', 'label' => 'Fire risk assessment service'],
        ['href' => '/pages/services/fire-alarms', 'label' => 'Fire alarms'],
        ['href' => '/pages/services/emergency-lighting', 'label' => 'Emergency lighting'],
        ['href' => '/pages/services/smoke-co-alarms', 'label' => 'Smoke and CO alarms'],
        ['href' => '/pages/packages', 'label' => 'Packages'],
        ['href' => '/pages/landlords', 'label' => 'Landlords'],
        ['href' => '/pages/landlord-certificates', 'label' => 'Landlord certificates hub'],
        ['href' => '/shop/fire/', 'label' => 'Fire supplies (trade shop)'],
        ['href' => '/pages/jobs/eicr', 'label' => 'EICR for landlords'],
        ['href' => '/pages/jobs/gas-safety-cp12', 'label' => 'Gas safety certificate (CP12)'],
        ['href' => '/pages/jobs/fire-risk-assessment', 'label' => 'Fire risk assessment landing'],
        ['href' => '/pages/areas', 'label' => 'Areas we cover'],
        ['href' => '/contact', 'label' => 'Get a quote'],
    ],
    'sources' => [
        [
            'href' => 'https://www.gov.uk/government/publications/electrical-safety-standards-in-the-private-and-social-rented-sectors-guidance/electrical-safety-standards-in-the-private-and-social-rented-sectors-guidance',
            'label' => 'GOV.UK — electrical safety standards in the private rented sector',
        ],
        [
            'href' => 'https://www.hse.gov.uk/gas/landlords/index.htm',
            'label' => 'HSE — gas safety for landlords',
        ],
    ],
    'ctaTitle' => 'Request a landlord compliance quote',
    'ctaText' => 'Tell us postcodes, property types (flat, house, HMO) and which certificates or checks are due, including alarms if you want them on the same schedule. Pack guide **' . $packFrom . '** (EICR ' . $eicrFrom . ', gas ' . $gasFrom . ', FRA ' . $fraFrom . '). The fixed price follows scope.',
];

require SITE_ROOT . '/includes/conversion-landing.php';
