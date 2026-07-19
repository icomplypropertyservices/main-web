<?php
/**
 * Merge high-value missing keywords into data/keywords.json (no wipe).
 * Usage: php bin/expand-high-value-keywords.php
 */
require_once __DIR__ . '/../config.php';

$path = SITE_ROOT . '/data/keywords.json';
$kw = loadJsonData('keywords', []);

$new = [
    'domestic-eicr' => [
        'name' => 'Domestic EICR',
        'service' => 'electrical',
        'related' => 'eicr',
        'intro' => 'A domestic EICR is the electrical installation condition report for houses, flats and small residential lets under BS 7671.',
        'body' => 'Landlords and homeowners use domestic EICRs to evidence safety of fixed wiring, consumer units and accessories. We inspect, code faults and retest after remedials across the North West.',
        'meta_desc' => 'Domestic EICR testing for homes and small lets in the North West. BS 7671 certificates from Stockport-based engineers.',
        'seo_keywords' => 'domestic EICR, home EICR, residential electrical certificate, EICR house, EICR flat',
        'focus_points' => [
            'Full BS 7671 domestic inspection',
            'C1/C2/C3 coding with clear remedials',
            'Certificate emailed after completion',
            'Landlord and owner-occupier options',
        ],
        'faq' => [
            ['How long does a domestic EICR take?', 'Most 1–3 bed homes are completed in a few hours depending on access and board location.'],
            ['Is a domestic EICR the same as a landlord certificate?', 'Yes for fixed wiring safety — landlords typically need it every 5 years or on change of tenancy.'],
        ],
    ],
    'eicr-near-me' => [
        'name' => 'EICR Near Me',
        'service' => 'electrical',
        'related' => 'eicr',
        'intro' => 'Looking for an EICR near you in Greater Manchester or the North West? Local Stockport-based engineers cover 150+ towns with fixed-price quotes.',
        'body' => 'Search intent for EICR near me usually means fast local attendance, clear pricing and a proper BS 7671 report. We book from our SK2 base into Manchester, Stockport, Bolton, Oldham and beyond.',
        'meta_desc' => 'EICR near me — local electrical condition reports across Greater Manchester and the North West.',
        'seo_keywords' => 'EICR near me, local EICR, electrical certificate near me, EICR engineer near me',
        'focus_points' => [
            'Local North West coverage',
            'Same-week slots where capacity allows',
            'Fixed-price before we attend',
            'Digital certificate turnaround',
        ],
        'faq' => [
            ['How far do you travel for an EICR?', 'We cover Greater Manchester, Cheshire, Lancashire, Merseyside and parts of Cumbria from Stockport.'],
            ['Can I book an EICR this week?', 'Often yes for landlord and domestic work when engineers have capacity.'],
        ],
    ],
    'periodic-inspection' => [
        'name' => 'Periodic Inspection',
        'service' => 'electrical',
        'related' => 'eicr',
        'intro' => 'A periodic inspection is the formal electrical installation condition assessment that produces your EICR under BS 7671.',
        'body' => 'We carry out periodic inspections for domestic, commercial and multi-let properties, recording observations, limitations and next inspection intervals on the official report form.',
        'meta_desc' => 'Periodic electrical inspection and EICR reporting for North West properties.',
        'seo_keywords' => 'periodic inspection, electrical periodic inspection, PIR electrical, BS 7671 inspection',
        'focus_points' => [
            'Scheduled re-inspection intervals',
            'Commercial and residential programmes',
            'Clear remedial schedules',
            'Integration with landlord portfolios',
        ],
        'faq' => [
            ['Is periodic inspection the same as EICR?', 'The EICR is the report produced from the periodic inspection process under BS 7671.'],
            ['How often should commercial sites re-inspect?', 'Often 1–5 years based on risk, environment and insurer requirements.'],
        ],
    ],
    'rewire' => [
        'name' => 'House Rewire',
        'service' => 'electrical',
        'related' => 'consumer-unit-upgrade',
        'intro' => 'A full or partial house rewire renews fixed cabling, accessories and often the consumer unit to current BS 7671 standards.',
        'body' => 'We survey existing wiring, plan safe isolation and reinstate finishes where agreed. Ideal for older properties, post-fire repairs, landlord upgrades and major renovations across the North West.',
        'meta_desc' => 'House rewire and partial rewire services in Greater Manchester and the North West.',
        'seo_keywords' => 'house rewire, full rewire, partial rewire, rewiring electrician, rewire cost',
        'focus_points' => [
            'Full and partial rewires',
            'Consumer unit upgrades with RCD/RCBO protection',
            'Certification on completion',
            'Coordination with renovation programmes',
        ],
        'faq' => [
            ['How long does a house rewire take?', 'A typical 3-bed can take several days depending on access, board location and making-good scope.'],
            ['Do you certify after a rewire?', 'Yes — electrical installation certificates and schedules are issued on completion.'],
        ],
    ],
    'fire-alarm-service' => [
        'name' => 'Fire Alarm Service',
        'service' => 'fire-alarms',
        'related' => 'fire-alarm-installation',
        'intro' => 'Fire alarm service covers planned maintenance, battery replacement, device testing and logbook updates under BS 5839.',
        'body' => 'We service addressable, conventional and wireless systems for commercial, multi-let and care sites. Visits include device tests, panel checks and documentation for insurers and fire officers.',
        'meta_desc' => 'Fire alarm servicing and maintenance to BS 5839 across the North West.',
        'seo_keywords' => 'fire alarm service, fire alarm maintenance, BS 5839 service, fire alarm engineer',
        'focus_points' => [
            '6-monthly service visits',
            'Battery and device replacements',
            'Panel fault diagnostics',
            'Logbooks ready for audit',
        ],
        'faq' => [
            ['How often should fire alarms be serviced?', 'Most non-domestic systems need servicing at least twice a year plus weekly user tests.'],
            ['Can you service systems you did not install?', 'Yes — we maintain major brands including Kentec, Advanced, C-Tec, Morley and more.'],
        ],
    ],
    'fire-alarm-installation' => [
        'name' => 'Fire Alarm Installation',
        'service' => 'fire-alarms',
        'related' => 'addressable-fire-alarm',
        'intro' => 'Fire alarm installation covers design, supply, commissioning and handover of detection and alarm systems to BS 5839.',
        'body' => 'From small conventional systems to networked addressable panels, we install, cause-and-effect programme and certificate systems for commercial and multi-let properties.',
        'meta_desc' => 'Fire alarm installation to BS 5839 across Greater Manchester and the North West.',
        'seo_keywords' => 'fire alarm installation, fire alarm installers, BS 5839 install, commercial fire alarm install',
        'focus_points' => [
            'Design to BS 5839',
            'Addressable and conventional',
            'Commissioning and user training',
            'Integration with AOV and door holders',
        ],
        'faq' => [
            ['Do you provide design certificates?', 'Yes — design, install and commissioning documentation is provided as required for the project.'],
            ['Can you replace an old panel and keep devices?', 'Often yes after survey — compatibility and loop loading are checked first.'],
        ],
    ],
    'emergency-lighting-test' => [
        'name' => 'Emergency Lighting Test',
        'service' => 'emergency-lighting',
        'related' => 'emergency-lighting-certificate',
        'intro' => 'Emergency lighting tests cover monthly functional checks and annual full-duration tests required under BS 5266.',
        'body' => 'We test, repair and certify emergency lighting for landlords, commercial sites and multi-let buildings, with clear records for compliance audits.',
        'meta_desc' => 'Emergency lighting testing and certification to BS 5266 in the North West.',
        'seo_keywords' => 'emergency lighting test, emergency lighting certificate, BS 5266 testing, monthly emergency light test',
        'focus_points' => [
            'Monthly and annual test programmes',
            'LED conversion options',
            'Failed fitting replacements',
            'Compliance certificates and logs',
        ],
        'faq' => [
            ['What is an annual full duration test?', 'Luminaires run for their rated duration (often 3 hours) to prove batteries still support escape lighting.'],
            ['Do landlords need emergency lighting tests?', 'Where emergency lighting is installed as part of the fire strategy, testing and records are expected.'],
        ],
    ],
    'emergency-lighting-certificate' => [
        'name' => 'Emergency Lighting Certificate',
        'service' => 'emergency-lighting',
        'related' => 'emergency-lighting-test',
        'intro' => 'An emergency lighting certificate records test results and system condition after monthly or annual BS 5266 testing.',
        'body' => 'We issue clear certificates and logs after testing, with failed fittings listed for repair so landlords and FM teams stay audit-ready.',
        'meta_desc' => 'Emergency lighting certificates after BS 5266 testing in the North West.',
        'seo_keywords' => 'emergency lighting certificate, emergency light cert, BS 5266 certificate',
        'focus_points' => [
            'Certificate after each planned test',
            'Remedial schedules for failed fittings',
            'Portfolio-friendly documentation',
            'Aligns with fire risk assessments',
        ],
        'faq' => [
            ['Who needs an emergency lighting certificate?', 'Any site where emergency lighting forms part of the fire safety strategy should keep test records and certificates.'],
            ['How quickly do we receive the certificate?', 'Typically with the engineer notes shortly after the visit once results are confirmed.'],
        ],
    ],
    'cp12' => [
        'name' => 'CP12 Gas Safety Certificate',
        'service' => 'gas-systems',
        'related' => 'gas-safety-certificate',
        'intro' => 'A CP12 is the landlord gas safety record confirming gas appliances and flues have been checked by a Gas Safe engineer.',
        'body' => 'We arrange landlord gas safety checks for single lets and portfolios, with certificates issued after inspection and any urgent isolation explained clearly.',
        'meta_desc' => 'CP12 landlord gas safety certificates across Greater Manchester and the North West.',
        'seo_keywords' => 'CP12, CP12 certificate, landlord gas safety, gas safety certificate landlord',
        'focus_points' => [
            'Landlord annual gas safety',
            'Portfolio scheduling',
            'Appliance and flue checks',
            'Digital certificate delivery',
        ],
        'faq' => [
            ['How often is a CP12 required?', 'Landlords must have gas appliances checked every 12 months and issue the record to tenants.'],
            ['Is CP12 the same as a boiler service?', 'Related but not identical — CP12 is the legal landlord safety record; servicing may be additional.'],
        ],
    ],
    'landlord-gas-safety' => [
        'name' => 'Landlord Gas Safety',
        'service' => 'gas-systems',
        'related' => 'cp12',
        'intro' => 'Landlord gas safety covers annual Gas Safe checks, CP12 records and remedial advice for rented homes and HMOs.',
        'body' => 'We help agents and landlords keep gas compliance on schedule across multi-property portfolios in the North West, with clear booking and certificate turnaround.',
        'meta_desc' => 'Landlord gas safety checks and CP12 certificates for North West rentals.',
        'seo_keywords' => 'landlord gas safety, landlord gas certificate, gas safety for landlords, HMO gas safety',
        'focus_points' => [
            'Annual landlord programmes',
            'HMO and multi-let cover',
            'Certificate tracking support',
            'Coordination with EICR packages',
        ],
        'faq' => [
            ['Do all rental properties need gas safety checks?', 'If there are gas appliances or flues, yes — annual Gas Safe checks and tenant records are required.'],
            ['Can you combine gas safety with EICR?', 'Yes — multi-service packages reduce visits for landlords and agents.'],
        ],
    ],
];

$added = 0;
foreach ($new as $slug => $meta) {
    $slug = keywordSlug($slug);
    if (!isset($kw[$slug])) {
        $kw[$slug] = $meta;
        $added++;
        echo "Added: {$slug}\n";
    } else {
        echo "Exists: {$slug}\n";
    }
}

ksort($kw);
if (!saveJsonData('keywords', $kw)) {
    // fallback write
    file_put_contents($path, json_encode($kw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
}

echo "Done. Added {$added}. Total keywords: " . count($kw) . "\n";
echo "Each keyword × " . count(getAreas()) . " areas is available via virtual routes.\n";
