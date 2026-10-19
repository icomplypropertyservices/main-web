<?php
/**
 * Call-focused upgrades for service hubs with high impressions and no clicks
 * (GSC 28d to 2026-10-15: gas-systems, damp-proofing, access-control, aov-air-handling).
 * Adds a click-worthy title/meta, quick-answer section, extra FAQs and internal links.
 * Gas: work is done by Gas Safe registered engineers. iComply itself is NOT Gas Safe registered.
 */

function icomplyServiceBoost(string $slug): ?array
{
    $phone = '07517 806082';
    $b = [
        'gas-systems' => [
            'title' => 'Landlord Gas Safety Certificate (CP12) | Same/Next-Day',
            'meta' => 'Landlord gas safety certificates (CP12), boiler servicing and gas checks across Greater Manchester. Same/next-day slots, carried out by Gas Safe registered engineers. Call ' . $phone . '.',
            'answer' => [
                'h2' => 'Gas safety certificate in Greater Manchester — quick answer',
                'p' => [
                    'Landlords in England need a gas safety check on every gas appliance, flue and pipework at least every 12 months, and must give tenants the record (CP12) within 28 days. The check is carried out by a Gas Safe registered engineer; iComply arranges and books it and can add the EICR, smoke/CO alarms and EPC on the same visit.',
                    'Same or next-day slots are often available across Stockport, Manchester, Tameside, Trafford and Cheshire East. Call or WhatsApp ' . $phone . ' with the postcode and number of appliances.',
                ],
            ],
            'faq' => [
                ['How often does a landlord gas safety check need doing?', 'Every 12 months for each gas appliance and flue in a rented home. You can do the check up to two months early and keep the original renewal date.'],
                ['How fast can you book a CP12?', 'Same or next-day slots are often available in Stockport and Greater Manchester. Call ' . $phone . ' with the postcode.'],
                ['Can you do the EICR and gas check together?', 'Yes. Most landlords book the gas check, EICR and smoke/CO alarm check as one visit so the tenant is only disturbed once.'],
            ],
            'links' => [
                ['/pages/keywords/landlord-gas-safety-certificate', 'Landlord gas safety certificate'],
                ['/pages/keywords/gas-boiler-servicing', 'Boiler servicing'],
                ['/pages/jobs/eicr', 'EICR from £160'],
                ['/pages/packages/let-ready', 'Let Ready package'],
            ],
        ],
        'damp-proofing' => [
            'title' => 'Damp Proofing & Rising Damp Treatment Stockport | ' . $phone,
            'meta' => 'Damp surveys, rising damp treatment, condensation and mould fixes, then replastering. Greater Manchester landlords and homeowners. Call ' . $phone . '.',
            'answer' => [
                'h2' => 'Damp proofing — quick answer',
                'p' => [
                    'Most damp in North West homes is condensation, penetrating damp (gutters, pointing, render) or rising damp from a failed or bridged damp-proof course. We find the cause first, fix the source, then treat and replaster — so the mould does not come back.',
                    'For landlords, damp and mould is now a hazard councils act on quickly. Call or WhatsApp ' . $phone . ' for a survey slot in Stockport, Manchester and surrounding towns.',
                ],
            ],
            'faq' => [
                ['Is it rising damp or condensation?', 'Condensation shows as mould high on cold walls and around windows; rising damp shows as a tide mark up to about 1m with salts. A survey with moisture readings confirms which before any treatment is sold.'],
                ['Do you replaster after damp proofing?', 'Yes. After a chemical DPC or tanking we replaster with a salt-resistant render or plaster system and make good.'],
                ['Can you help with a council damp and mould notice?', 'Yes. We survey, fix the cause and give you a written record of the work for the tenancy file.'],
            ],
            'links' => [
                ['/pages/keywords/rising-damp-treatment', 'Rising damp treatment'],
                ['/pages/keywords/damp-survey-support', 'Damp survey'],
                ['/pages/keywords/replaster-after-damp-proofing', 'Replaster after damp proofing'],
                ['/pages/services/plastering', 'Plastering'],
                ['/pages/services/building-surveys', 'Building surveys'],
            ],
        ],
        'access-control' => [
            'title' => 'Access Control Installation & Repair Manchester | ' . $phone,
            'meta' => 'Access control, fobs, maglocks, door entry and car park barriers for offices, blocks and warehouses across Greater Manchester. Fast repairs. Call ' . $phone . '.',
            'answer' => [
                'h2' => 'Access control — quick answer',
                'p' => [
                    'We install, repair and upgrade access control for offices, apartment blocks, warehouses and car parks: fob and card readers, keypads, maglocks with fire release, door entry panels, and barrier lanes. Existing Paxton, Videx, Comelit and Came systems are supported.',
                    'Locked out, a door that will not release, or a barrier stuck up? Call or WhatsApp ' . $phone . ' — fault visits across Stockport and Manchester are often same or next day.',
                ],
            ],
            'faq' => [
                ['Can you add fobs or users to an existing system?', 'Yes. We can add and delete fobs, cards and users on most common systems and replace failed readers.'],
                ['Does access control need to release on fire alarm?', 'Doors on escape routes should fail safe and release on fire alarm activation. We wire the fire release and test it.'],
                ['How quickly can you fix a door or barrier fault?', 'Fault visits in Stockport and Greater Manchester are often same or next day. Call ' . $phone . '.'],
            ],
            'links' => [
                ['/pages/keywords/access-control-system', 'Access control systems'],
                ['/pages/services/door-entry', 'Door entry'],
                ['/pages/services/barriers', 'Car park barriers'],
                ['/pages/services/cctv', 'CCTV'],
            ],
        ],
        'aov-air-handling' => [
            'title' => 'AOV & Smoke Vent Servicing and Repair | Same/Next-Day',
            'meta' => 'AOV and smoke ventilation servicing, repairs and panel upgrades to BS 9999 / EN 12101 for blocks across the North West. Same/next-day fault visits. Call ' . $phone . '.',
            'answer' => [
                'h2' => 'AOV servicing — quick answer',
                'p' => [
                    'Automatic opening vents (AOVs) in common stairs and corridors must be tested regularly and serviced at least once a year by a competent engineer, with the result recorded for the Responsible Person and the building safety file.',
                    'We service, repair and upgrade AOV panels, actuators, smoke heads and break-glass points on existing systems, and install new smoke vents. Call or WhatsApp ' . $phone . ' for a fault visit or service quote.',
                ],
            ],
            'faq' => [
                ['How often should AOVs be serviced?', 'At least annually by a competent engineer, with regular function tests (often weekly with the fire alarm test) recorded in the logbook.'],
                ['Can you repair an AOV that will not close?', 'Yes. Common causes are failed actuators, chain or linkage faults, panel batteries and smoke head faults. Most are fixed on the first visit.'],
                ['Do you work with existing smoke control panels?', 'Yes. We service and repair most common panel brands and can upgrade obsolete panels.'],
            ],
            'links' => [
                ['/pages/aov', 'AOV & smoke control hub'],
                ['/pages/manufacturers/smoke-control', 'Smoke control products'],
                ['/pages/services/fire-alarms', 'Fire alarms'],
                ['/pages/maintenance', 'Planned maintenance contracts'],
            ],
        ],
    ];
    return $b[$slug] ?? null;
}
