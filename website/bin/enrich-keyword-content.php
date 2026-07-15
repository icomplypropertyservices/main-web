<?php
/**
 * Generate unique SEO fields for every keyword in data/keywords.json.
 * Fields: intro, body, meta_desc, focus_points (array), faq (array of [q,a])
 */
require_once __DIR__ . '/../config.php';

$serviceCopy = [
    'electrical' => [
        'std' => 'BS 7671 (18th Edition)',
        'docs' => 'EICR certificates, installation certs and remedial schedules',
        'who' => 'landlords, letting agents, FM teams and homeowners',
        'verb' => 'inspect, install, rewire and certify',
    ],
    'fire-alarms' => [
        'std' => 'BS 5839',
        'docs' => 'service certificates, logbooks and commissioning packs',
        'who' => 'landlords, commercial occupiers and facilities managers',
        'verb' => 'design, install, service and certify',
    ],
    'emergency-lighting' => [
        'std' => 'BS 5266',
        'docs' => 'monthly/annual test records and duration certificates',
        'who' => 'landlords, care providers and commercial sites',
        'verb' => 'install, test, convert and certify',
    ],
    'gas-systems' => [
        'std' => 'Gas Safe practice and landlord gas safety rules',
        'docs' => 'CP12/CP44 certificates and service records',
        'who' => 'landlords, agents and commercial kitchens',
        'verb' => 'inspect, service, install and certificate',
    ],
    'cctv' => [
        'std' => 'BS EN 62676 and practical siting guidance',
        'docs' => 'system handover packs and viewing setup notes',
        'who' => 'businesses, landlords and multi-site operators',
        'verb' => 'design, install, maintain and upgrade',
    ],
    'access-control' => [
        'std' => 'BS EN 50133 and fire-release best practice',
        'docs' => 'commissioning sheets and user training notes',
        'who' => 'offices, blocks and multi-site estates',
        'verb' => 'design, install, program and maintain',
    ],
    'door-entry' => [
        'std' => 'multi-tenant door entry best practice',
        'docs' => 'handover documentation and panel programming notes',
        'who' => 'blocks of flats, landlords and managing agents',
        'verb' => 'install, repair, upgrade and maintain',
    ],
    'intercoms' => [
        'std' => 'commercial intercom and master-station practice',
        'docs' => 'commissioning records and user guides',
        'who' => 'offices, factories, schools and healthcare sites',
        'verb' => 'install, service and upgrade',
    ],
    'intruder-alarm' => [
        'std' => 'PD 6662 / BS EN 50131',
        'docs' => 'commissioning certificates and grade documentation',
        'who' => 'shops, offices, warehouses and homes',
        'verb' => 'install, maintain and upgrade',
    ],
    'nurse-call' => [
        'std' => 'HTM 08-03 guidance for healthcare call systems',
        'docs' => 'PPM records and system handover packs',
        'who' => 'care homes, nursing homes and healthcare estates',
        'verb' => 'install, maintain and revalidate',
    ],
    'aov-air-handling' => [
        'std' => 'BS 9991 / EN 12101 smoke-control practice',
        'docs' => 'service certificates and actuator test records',
        'who' => 'apartment blocks, high-rise and commercial landlords',
        'verb' => 'install, service and commission',
    ],
];

$openers = [
    'Looking for expert {kw} support across the North West?',
    'Need reliable {kw} from a local Stockport-based team?',
    'Searching for professional {kw} with clear fixed-price quotes?',
    'Want {kw} handled by engineers who work to current UK standards?',
    'Planning {kw} for a landlord, commercial or multi-site property?',
];

$middles = [
    'Icomply Property Services {verb} {kw} as part of our {svc} range, working to {std}.',
    'Our engineers {verb} {kw} for {who}, with documentation that stands up to audits and insurers.',
    'From first survey to final paperwork, we {verb} {kw} with transparent scope and fixed pricing after agreement.',
    'We specialise in {kw} alongside related {svc} works, so one team can cover install, service and certification.',
];

$closers = [
    'Based in Stockport (SK2), we cover Greater Manchester, Lancashire, Cheshire, Merseyside and Cumbria with same-week appointments where capacity allows.',
    'Tell us your postcode, property type and any panel brand — we aim to respond within 2 hours on business days.',
    'Browse local pages for your town, or request a multi-property package if you manage several sites.',
    'Manufacturer support is listed below so you can find us when searching for your exact equipment brand.',
];

$keywords = loadJsonData('keywords', []);
$updated = 0;

foreach ($keywords as $slug => &$meta) {
    if (!is_array($meta)) {
        continue;
    }
    $name = $meta['name'] ?? keywordDisplayName($slug);
    $svc = $meta['service'] ?? 'electrical';
    $svcName = getServices()[$svc] ?? keywordDisplayName($svc);
    $c = $serviceCopy[$svc] ?? $serviceCopy['electrical'];

    // Deterministic variety from slug hash
    $h = abs(crc32($slug));
    $opener = $openers[$h % count($openers)];
    $middle = $middles[($h >> 3) % count($middles)];
    $closer = $closers[($h >> 6) % count($closers)];

    $map = [
        '{kw}' => $name,
        '{svc}' => $svcName,
        '{std}' => $c['std'],
        '{docs}' => $c['docs'],
        '{who}' => $c['who'],
        '{verb}' => $c['verb'],
    ];
    $fill = static function (string $t) use ($map): string {
        return str_replace(array_keys($map), array_values($map), $t);
    };

    // Keep agent-enriched text if already high quality (long custom intro)
    $hasCustom = !empty($meta['intro']) && strlen((string)$meta['intro']) > 280 && !empty($meta['body']);

    if (!$hasCustom) {
        $meta['intro'] = $fill($opener) . ' ' . $fill($middle) . ' We issue ' . $c['docs'] . ' where the job scope includes testing or certification.';
        $meta['body'] = $fill($closer) . ' Whether you need a one-off visit for ' . $name
            . ' or a planned maintenance contract, our team delivers clear quotes, professional workmanship and full ' . $svcName
            . ' documentation for ' . $c['who'] . '. Related searches often include local installation, servicing, repairs and compliance certificates for ' . $name . '.';
    }

    if (empty($meta['meta_desc']) || strlen((string)$meta['meta_desc']) < 80) {
        $meta['meta_desc'] = substr(
            $name . ' across Greater Manchester & the North West. Icomply ' . $c['verb']
            . ' with fixed-price quotes, ' . $c['std'] . ' focus and local engineers from Stockport.',
            0,
            158
        );
    }

    if (empty($meta['focus_points']) || !is_array($meta['focus_points'])) {
        $meta['focus_points'] = [
            'Survey, specification and fixed-price quote for ' . $name,
            'Install, upgrade or remedial works by local North West engineers',
            'Testing and ' . $c['docs'],
            'Manufacturer-aware support within our ' . $svcName . ' service',
        ];
    }

    if (empty($meta['faq']) || !is_array($meta['faq'])) {
        $meta['faq'] = [
            [
                'What is included in ' . $name . '?',
                'Scope depends on the site, but typically covers assessment, labour, materials agreed in the quote, commissioning where required, and ' . $c['docs'] . ' when certification is part of the job.',
            ],
            [
                'Do you offer ' . $name . ' near me in the North West?',
                'Yes. From Stockport we cover 150+ towns including Manchester, Bolton, Liverpool, Preston and surrounding areas, with local engineers for ' . $name . '.',
            ],
            [
                'How quickly can you attend for ' . $name . '?',
                'We aim for same-week appointments where capacity and site access allow, and we prioritise reactive faults when engineers are available.',
            ],
            [
                'How do I get a quote for ' . $name . '?',
                'Use the form on this page, call, or WhatsApp with postcode, property type and any brand already on site. We aim to reply within 2 hours on business days.',
            ],
        ];
    }

    // SEO keywords string unique to keyword
    if (empty($meta['seo_keywords'])) {
        $meta['seo_keywords'] = implode(', ', [
            $name,
            $name . ' North West',
            $name . ' Manchester',
            $name . ' Stockport',
            $name . ' installation',
            $name . ' cost',
            $svcName,
            'property compliance',
        ]);
    }

    $updated++;
}
unset($meta);

saveJsonData('keywords', $keywords);
echo "Enriched keywords: {$updated}\n";
