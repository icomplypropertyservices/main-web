<?php
/**
 * Conversion landing — EICR for landlords.
 * Does not replace /pages/services/electrical (there is no /pages/services/eicr route).
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once SITE_ROOT . '/includes/landlord-guide-prices.php';
$eicrFrom = icomplyLandlordGuideMoney('eicr');

$pageTitle = 'EICR for landlords | Stockport & Manchester';
$metaTitleExact = true;
$metaDesc = 'Electrical Installation Condition Reports for England PRS landlords and agents. Stockport-based — guide from ' . $eicrFrom . ', fixed quote after scope.';
$ogTitle = 'EICR for landlords — Stockport & Manchester';
$ogDescription = 'EICR for portfolios and single lets. Guide from ' . $eicrFrom . '. Fixed quote after scope.';
$ogImage = rtrim(SITE_URL, '/') . '/assets/images/services/electrical.jpg';
$ogImageAlt = 'Electrical installation work for landlord EICR — iComply Property Services';
$canonicalUrl = url('/pages/jobs/eicr');
$metaKeywords = 'EICR landlords, electrical installation condition report, Stockport, Greater Manchester';
$omitPriceRange = true;

$landing = [
    'priceKey' => 'eicr',
    'kicker' => 'Landlords & letting agents · England PRS',
    'h1' => 'EICR for landlords: frequency, satisfactory vs unsatisfactory, and remedial timelines',
    'lede' => 'An **Electrical Installation Condition Report (EICR)** is a formal inspection and testing report on the fixed electrical installation — consumer unit, wiring, sockets, lighting circuits and related fixed equipment as scoped by the inspector. For private landlords in England it is one of the core safety documents you should be able to produce.',
    'note' => 'General marketing education, not legal advice. Check current [GOV.UK PRS electrical safety guidance](https://www.gov.uk/government/publications/electrical-safety-standards-in-the-private-and-social-rented-sectors-guidance/electrical-safety-standards-in-the-private-and-social-rented-sectors-guidance) and [SI 2020/312](https://www.legislation.gov.uk/uksi/2020/312/regulation/3). This page covers England. Duties elsewhere differ.',
    'proof' => [
        'Stockport-based',
        'Greater Manchester / North West',
        'Guide from ' . $eicrFrom,
    ],
    'crumbs' => [
        ['name' => 'Services', 'href' => '/pages/services'],
        ['name' => 'EICR for landlords', 'href' => '/pages/jobs/eicr', 'current' => true],
    ],
    'scopeTitle' => 'What landlords need on file',
    'scope' => [
        'In England, private landlords of relevant residential premises are generally required to ensure the electrical installation is inspected and tested, and to obtain a report, in line with the Electrical Safety Standards regulations (often referred to via **SI 2020/312**) and the associated GOV.UK guidance.',
        'In practice that usually means a valid EICR for the rented dwelling’s fixed installation, copies you can share with tenants and agents, and — if the report is unsatisfactory — remedial work or further investigation with **written evidence**. Exact coverage for new tenancies and particular dwelling types should be read from current GOV.UK guidance, not from this page alone.',
        'If you hold an **HMO licence** in Stockport or elsewhere in Greater Manchester, licensing conditions can sit alongside national electrical rules. Check your council’s landlord pages as well.',
    ],
    'scopeItems' => [
        [
            'title' => 'How often',
            'text' => 'At least every five years, or sooner if the previous report states a shorter next-inspection interval. Diary the date on the report. If a report is lost or clearly out of date, arrange a fresh inspection.',
        ],
        [
            'title' => 'Satisfactory',
            'text' => 'The inspector has not identified issues that prevent a satisfactory outcome under the standard used. File the full report, note the next inspection date, and share copies as required. It is a point-in-time report, not a promise that nothing will need attention before the next due date.',
        ],
        [
            'title' => 'Unsatisfactory',
            'text' => 'The installation needs remedial work and/or further investigation as set out in the report. Plan from the written observations. Keep the original report plus invoices or written confirmation that required work was done.',
        ],
        [
            'title' => 'Remedial timelines',
            'text' => 'Follow the timescales in the report and in current GOV.UK guidance. This page does not invent day-counts. Observation codes (C1, C2, C3 and FI) are technical; if the overall result is unsatisfactory, treat remedials as a priority and follow the written report.',
        ],
    ],
    'stepsTitle' => 'From enquiry to the report',
    'steps' => [
        [
            'title' => 'Tell us the property',
            'text' => 'Postcode and property type (flat, house, HMO or portfolio), access notes, and any previous EICR. Confirm tenant notice, keys and where the consumer unit is.',
        ],
        [
            'title' => 'Fixed quote after scope',
            'text' => 'The published guide is **' . $eicrFrom . '**. That is not the fixed quote. Inspection and remedials may be separate line items. No obligation until you accept a fixed price.',
        ],
        [
            'title' => 'Test and file the report',
            'text' => 'Testing may briefly interrupt power. You receive the full report. If it is unsatisfactory, we can discuss remedial options against the written findings — we do not guarantee an outcome before testing.',
        ],
    ],
    'faqs' => [
        [
            'q' => 'Is an EICR the same as a PAT test?',
            'a' => 'No. An EICR covers the fixed electrical installation. Portable appliance testing is a different exercise, usually aimed at appliances. A PAT certificate is not a substitute for an EICR where the PRS electrical safety rules require an installation condition report.',
        ],
        [
            'q' => 'How long does an EICR take?',
            'a' => 'It depends on the size and complexity of the installation — a small flat is not the same job as a large HMO. The quote after scope should reflect access and property type.',
        ],
        [
            'q' => 'What if my report is unsatisfactory?',
            'a' => 'Arrange the required remedials or further investigation, keep written evidence, and follow timescales in current GOV.UK guidance. File everything with the property compliance pack.',
        ],
        [
            'q' => 'Can tenants refuse access?',
            'a' => 'Landlords still need to meet their duties. Access is usually handled through tenancy terms and reasonable notice. Build notice time into the renewal diary so the certificate does not lapse while access is arranged.',
        ],
        [
            'q' => 'Do you cover Stockport and Greater Manchester?',
            'a' => 'Yes. We are based in Offerton, Stockport (SK2) and work across Greater Manchester and the wider North West. Tell us the postcode and property type when you request a quote.',
        ],
        [
            'q' => 'Will you guarantee a satisfactory EICR?',
            'a' => 'No. A competent EICR reports the condition found on the day. We do not guarantee a satisfactory result before testing. If the result is unsatisfactory, remedial options can be discussed from the written report.',
        ],
    ],
    'links' => [
        ['href' => '/pages/services/electrical', 'label' => 'Electrical services'],
        ['href' => '/pages/services/landlord-compliance', 'label' => 'Landlord compliance service'],
        ['href' => '/pages/electrical-safety-landlords', 'label' => 'Electrical safety for landlords'],
        ['href' => '/pages/resources/eicr-guide', 'label' => 'EICR guide'],
        ['href' => '/pages/jobs/gas-safety-cp12', 'label' => 'Gas safety certificate (CP12)'],
        ['href' => '/pages/jobs/fire-risk-assessment', 'label' => 'Fire risk assessment'],
        ['href' => '/pages/jobs/landlord-compliance', 'label' => 'Landlord compliance package'],
        ['href' => '/pages/landlords', 'label' => 'Landlords'],
        ['href' => '/pages/areas', 'label' => 'Areas we cover'],
        ['href' => '/contact', 'label' => 'Get a quote'],
    ],
    'sources' => [
        [
            'href' => 'https://www.gov.uk/government/publications/electrical-safety-standards-in-the-private-and-social-rented-sectors-guidance/electrical-safety-standards-in-the-private-and-social-rented-sectors-guidance',
            'label' => 'GOV.UK — electrical safety standards in the private rented sector',
        ],
        [
            'href' => 'https://www.legislation.gov.uk/uksi/2020/312/regulation/3',
            'label' => 'Legislation.gov.uk — SI 2020/312 regulation 3',
        ],
    ],
    'ctaTitle' => 'Book an EICR quote',
    'ctaText' => 'Single let, HMO or a small portfolio — or line electrical checks up with gas and fire on one schedule. Share the postcode and property type. Guide from **' . $eicrFrom . '**. The fixed price follows scope.',
];

require SITE_ROOT . '/includes/conversion-landing.php';
