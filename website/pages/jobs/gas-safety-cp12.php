<?php
/**
 * Conversion landing — landlord gas safety record (CP12).
 * Does not replace /pages/services/gas-systems (there is no /pages/services/gas-safety route).
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/landlord-guide-prices.php';
$gasFrom = icomplyLandlordGuideMoney('gas');

$pageTitle = 'Gas safety certificate (CP12) for landlords';
$metaTitleExact = true;
$metaDesc = 'Landlord Gas Safety Records for Stockport & Greater Manchester agents and landlords. Annual checks — guide from ' . $gasFrom . ', fixed quote after scope.';
$ogTitle = 'Gas safety certificate (CP12)';
$ogDescription = 'CP12 for landlords and agents. Stockport-based — guide from ' . $gasFrom . '.';
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/services/gas-systems.jpg';
$ogImageAlt = 'Gas safety checks for landlords — iComply Property Services';
$canonicalUrl = url('/pages/jobs/gas-safety-cp12');
$metaKeywords = 'gas safety certificate landlords, CP12, landlord gas safety record, Stockport, Manchester';
$omitPriceRange = true;

$landing = [
    'priceKey' => 'gas',
    'kicker' => 'Landlords & letting agents · England PRS',
    'h1' => 'Gas safety certificate (CP12): annual duties landlords cannot skip',
    'lede' => 'A **Landlord Gas Safety Record** — often called a **CP12** — is the written proof that relevant gas appliances, flues and associated pipework at a rented property have been checked. For private rented landlords in England it sits alongside the EICR and, where they apply, fire documents.',
    'note' => 'General marketing education, not legal advice. Check current [HSE guidance for landlords on gas safety](https://www.hse.gov.uk/gas/landlords/index.htm) and Gas Safe Register advice before treating this summary as a complete statement of your duties. This page is England-focused.',
    'proof' => [
        'Stockport-based',
        'Greater Manchester / North West',
        'Guide from ' . $gasFrom,
    ],
    'crumbs' => [
        ['name' => 'Services', 'href' => '/pages/services'],
        ['name' => 'Gas safety (CP12)', 'href' => '/pages/jobs/gas-safety-cp12', 'current' => true],
    ],
    'scopeTitle' => 'What the record covers',
    'scope' => [
        'After a gas safety check, the engineer issues a Landlord Gas Safety Record. It records which appliances and flues were inspected, the results, and any defects or follow-up. Industry shorthand still calls it a CP12. The formal name on the paperwork is what belongs in the property file.',
        'It is not the same as a boiler service brochure, an insurance schedule, or an EICR. Keep gas records with the electrical and fire documents so agents can hand them over without a scramble.',
        'Where gas appliances or flues are present and the landlord duty applies, plan a check **at least every 12 months** by a **Gas Safe registered** engineer. Build the record into new-tenancy planning so it is current before tenants occupy — confirm the exact timing in current HSE and GOV.UK wording. Give tenants a copy within the period set out in that guidance. This page does not invent a day-count.',
    ],
    'scopeItems' => [
        [
            'title' => 'Typical check',
            'text' => 'A safety check, not a default full service. Typically: gas appliances that fall under the landlord duty (boilers, gas fires, cookers — the list depends on what is installed and who is responsible), associated flues and ventilation, and relevant installation pipework. The written record is the source of truth.',
        ],
        [
            'title' => 'Gas Safe registration',
            'text' => 'Gas work under landlord safety duties must be carried out by a Gas Safe registered engineer. Landlords and tenants should check the engineer’s ID on the public register. This page states that competence expectation only. It does not claim a firm registration number or badge.',
        ],
        [
            'title' => 'Records to keep',
            'text' => 'One file per property with the full record, a note of when tenants or agents were given copies, defect and remedial evidence, the next due date, and an agreed handback to the agent. HMO licence conditions in Greater Manchester may add local requirements — check the council as well as HSE guidance.',
        ],
        [
            'title' => 'No gas on site',
            'text' => 'If a property has no gas supply and no gas appliances, the paperwork position may differ. Confirm that against official guidance rather than assuming from a marketing page.',
        ],
    ],
    'stepsTitle' => 'From enquiry to the record',
    'steps' => [
        [
            'title' => 'Tell us the property',
            'text' => 'Postcode, property type (flat, house, HMO), appliance list if you have it, meter location, and access notes. Say if an EICR is due as well.',
        ],
        [
            'title' => 'Fixed quote after scope',
            'text' => 'Gas safety alone, or with an EICR on the same schedule where capacity allows. Guide from **' . $gasFrom . '**, then a **fixed quote** once scope is agreed. Same-week combinations are discussed against diary capacity, not promised for every job.',
        ],
        [
            'title' => 'Check and hand back the record',
            'text' => 'You receive the Landlord Gas Safety Record for the property file. If the engineer finds a problem, follow the written record and keep remedial evidence. Do not treat “next year” as compliance.',
        ],
    ],
    'faqs' => [
        [
            'q' => 'Is a CP12 the same as a boiler service?',
            'a' => 'Not necessarily. A Landlord Gas Safety Record is a safety check and certificate for landlord duties. A manufacturer-style service may be additional work. Ask for the scope to be clear on the quote.',
        ],
        [
            'q' => 'How often do landlords need a gas safety check?',
            'a' => 'Where the duty applies, at least annually, by a Gas Safe registered engineer. Confirm current wording on HSE’s landlord gas safety pages before treating that as a playbook rule.',
        ],
        [
            'q' => 'Do new tenants need a copy before they move in?',
            'a' => 'Official guidance sets when tenants must receive a copy of the record, often described as before occupation for new tenants. Verify the exact rule against HSE and GOV.UK, and keep proof of when copies were provided.',
        ],
        [
            'q' => 'Who can carry out the check?',
            'a' => 'A Gas Safe registered engineer. Check ID on the public register. This page does not assert a particular company’s registration status.',
        ],
        [
            'q' => 'Can I book gas safety and an EICR together?',
            'a' => 'Yes. Tell us the postcode, property type and which certificates are due. We can discuss one schedule where capacity allows. Gas safety on its own is guided from ' . $gasFrom . '; the fixed price follows the agreed scope.',
        ],
        [
            'q' => 'Do you cover Stockport and Greater Manchester?',
            'a' => 'Yes. We are based in Offerton, Stockport (SK2) and work across Greater Manchester and the wider North West. Include the postcode when you enquire.',
        ],
    ],
    'links' => [
        ['href' => '/pages/services/gas-systems', 'label' => 'Gas services'],
        ['href' => '/pages/services/landlord-compliance', 'label' => 'Landlord compliance service'],
        ['href' => '/pages/gas-safety-certificate', 'label' => 'Gas safety certificate hub'],
        ['href' => '/pages/jobs/eicr', 'label' => 'EICR for landlords'],
        ['href' => '/pages/jobs/fire-risk-assessment', 'label' => 'Fire risk assessment'],
        ['href' => '/pages/jobs/landlord-compliance', 'label' => 'Landlord compliance package'],
        ['href' => '/pages/landlords', 'label' => 'Landlords'],
        ['href' => '/pages/areas', 'label' => 'Areas we cover'],
        ['href' => '/contact', 'label' => 'Get a quote'],
    ],
    'sources' => [
        [
            'href' => 'https://www.hse.gov.uk/gas/landlords/index.htm',
            'label' => 'HSE — gas safety for landlords',
        ],
    ],
    'ctaTitle' => 'Enquire for gas safety',
    'ctaText' => 'Bring a Landlord Gas Safety Record up to date, or add an **EICR** on the same schedule. Guide from **' . $gasFrom . '**. The fixed quote follows what is included.',
];

$landing['gmTopic'] = 'Gas safety';
$landing['gmTowns'] = ['Wigan', 'Manchester', 'Stockport', 'Bolton'];
$landing['gmService'] = 'gas-systems';
$landing['gmKeyword'] = 'gas-safety-certificate';

require SITE_ROOT . '/includes/conversion-landing.php';
