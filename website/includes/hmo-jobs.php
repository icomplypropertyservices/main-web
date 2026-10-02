<?php
/**
 * HMO landlord package conversion pages (job lane).
 * £650 is the approved list for FRA + EICR + gas on one typical 6-bed HMO
 * in the North West (quote builder LAND-PKG-FRA-EICR-GAS-6BED-NW).
 * Discounted units use the same nearest-£1, half-up rule as that list.
 * Not a licence. Not legal advice. Draft / non-production until promoted.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

function hmoJobBundleListPence(): int
{
    return 65000;
}

/**
 * @return list<array{label:string,percent:float,pence:int,each:string}>
 */
function hmoJobBundleTiers(): array
{
    $rows = [
        ['label' => '1 property', 'percent' => 0.0],
        ['label' => '2–5 properties', 'percent' => 5.0],
        ['label' => '6–10 properties', 'percent' => 10.0],
        ['label' => '11–20 properties', 'percent' => 12.5],
        ['label' => '21 or more', 'percent' => 15.0],
    ];
    $list = hmoJobBundleListPence();
    $out = [];
    foreach ($rows as $row) {
        $pence = hmoJobDiscountPence($list, $row['percent']);
        $row['pence'] = $pence;
        $row['each'] = hmoJobFormatPence($pence);
        $out[] = $row;
    }
    return $out;
}

function hmoJobDiscountPence(int $listPence, float $percent): int
{
    if ($percent <= 0 || $listPence <= 0) {
        return $listPence;
    }
    $hundredths = (int)round($percent * 100);
    $scaled = $listPence * (10000 - $hundredths);
    $pounds = intdiv($scaled + 500000, 1000000);
    return $pounds * 100;
}

function hmoJobFormatPercent(float $percent): string
{
    $s = rtrim(rtrim(number_format($percent, 1, '.', ''), '0'), '.');
    if ($s === '') {
        $s = '0';
    }
    return $s . '%';
}

function hmoJobFormatPence(int $pence): string
{
    $pounds = intdiv($pence, 100);
    $rem = $pence % 100;
    $body = number_format($pounds);
    if ($rem !== 0) {
        $body .= '.' . str_pad((string)$rem, 2, '0', STR_PAD_LEFT);
    }
    return '£' . $body;
}

/**
 * @return array<string, array<string, mixed>>
 */
function hmoJobPages(): array
{
    $bundle = hmoJobFormatPence(hmoJobBundleListPence());
    $phone = defined('PHONE') ? PHONE : '07517806082';

    return [
        'hmo' => [
            'id' => 'hmo',
            'path' => '/pages/jobs/hmo',
            'file' => '/pages/jobs/hmo.php',
            'primaryBundle' => false,
            'pageTitle' => 'HMO landlord packages | iComply',
            'metaDesc' => 'HMO landlord packages from iComply in Stockport. FRA, EICR and gas together are ' . $bundle . ' for a typical 6-bed HMO in the North West. Quote, call or WhatsApp.',
            'keywords' => 'HMO landlord package, HMO compliance bundle, HMO EICR gas FRA, Stockport, Greater Manchester',
            'ogImage' => '/assets/images/services/electrical.jpg',
            'kicker' => 'HMO landlords and agents',
            'h1' => 'HMO landlord packages',
            'lede' => 'Three ways to book an HMO. The compliance bundle is **' . $bundle . '** for a fire risk assessment, an EICR and gas safety on the **same typical 6-bed HMO in the North West**. Fire-safety and re-let work are quoted from the same list where a line exists, and POA where it does not.',
            'crumbs' => [
                ['name' => 'Landlords', 'href' => '/pages/landlords'],
                ['name' => 'HMO packages', 'href' => '/pages/jobs/hmo', 'current' => true],
            ],
            'proof' => [
                $bundle . ' bundle · typical 6-bed · North West',
                'Stockport team · SK2 5DE',
                'Not a licence · not legal advice',
            ],
            'service' => 'HMO landlord packages',
            'wa' => 'Hi iComply, I need a quote for an HMO landlord package. If it is FRA, EICR and gas on a typical 6-bed in the North West, I have seen the ' . $bundle . ' bundle.',
            'showCards' => true,
            'ctaTitle' => 'Tell us which HMO package you need',
            'ctaText' => 'Postcode, number of lets, storeys, and whether gas is present. If the house is a typical 6-bed in the North West and you want FRA, EICR and gas together, the list price is **' . $bundle . '**.',
        ],
        'hmo-compliance' => [
            'id' => 'hmo-compliance',
            'path' => '/pages/jobs/hmo-compliance',
            'file' => '/pages/jobs/hmo-compliance.php',
            'primaryBundle' => true,
            'pageTitle' => 'HMO compliance bundle ' . $bundle . ' | iComply',
            'metaDesc' => 'HMO compliance bundle: FRA, EICR and gas safety for ' . $bundle . ' on a typical 6-bed HMO in the North West. iComply, Stockport. Remedials POA. Not a licence.',
            'keywords' => 'HMO compliance package, HMO bundle £650, HMO EICR, HMO FRA, HMO gas safety Stockport',
            'ogImage' => '/assets/images/services/electrical.jpg',
            'kicker' => 'FRA + EICR + gas',
            'h1' => 'HMO compliance bundle',
            'lede' => 'One property. Three certificates. **' . $bundle . '** for a typical 6-bed HMO in the North West: fire risk assessment, EICR and landlord gas safety. Those three are not added again on top of the bundle.',
            'crumbs' => [
                ['name' => 'Landlords', 'href' => '/pages/landlords'],
                ['name' => 'HMO packages', 'href' => '/pages/jobs/hmo'],
                ['name' => 'Compliance bundle', 'href' => '/pages/jobs/hmo-compliance', 'current' => true],
            ],
            'proof' => [
                $bundle . ' per typical 6-bed HMO',
                'North West list · all-in · no VAT added',
                'Portfolio discount on the bundle',
            ],
            'service' => 'HMO compliance bundle (£650)',
            'wa' => 'Hi iComply, I want the HMO compliance bundle (FRA + EICR + gas) at ' . $bundle . ' for a typical 6-bed in the North West.',
            'showCards' => false,
            'ctaTitle' => 'Book the ' . $bundle . ' HMO bundle',
            'ctaText' => 'Send the address, postcode, number of bedrooms and whether gas is on site. We confirm it is a typical 6-bed North West HMO before the **' . $bundle . '** list applies. Call ' . $phone . ' if the licence date is close.',
        ],
        'hmo-fire-safety' => [
            'id' => 'hmo-fire-safety',
            'path' => '/pages/jobs/hmo-fire-safety',
            'file' => '/pages/jobs/hmo-fire-safety.php',
            'primaryBundle' => false,
            'pageTitle' => 'HMO fire safety pack | iComply',
            'metaDesc' => 'HMO fire safety for Stockport and the North West: FRA, alarm inspection, emergency lighting and doors as required. The £650 bundle applies when FRA, EICR and gas are booked together.',
            'keywords' => 'HMO fire safety, HMO fire risk assessment, HMO fire alarm, HMO emergency lighting Stockport',
            'ogImage' => '/assets/images/services/fire-alarms.jpg',
            'kicker' => 'Life safety on an HMO',
            'h1' => 'HMO fire safety pack',
            'lede' => 'Start with the fire risk assessment, then only the detection, lighting or door work the house needs. The **' . $bundle . ' compliance bundle** is the right price when you also want EICR and gas on the same typical 6-bed — this page does not sell that trio as a fire-only pack.',
            'crumbs' => [
                ['name' => 'Landlords', 'href' => '/pages/landlords'],
                ['name' => 'HMO packages', 'href' => '/pages/jobs/hmo'],
                ['name' => 'Fire safety pack', 'href' => '/pages/jobs/hmo-fire-safety', 'current' => true],
            ],
            'proof' => [
                'FRA list £350 · typical 6-bed',
                'Alarms and lighting quoted from the list',
                'Installs and doors POA after survey',
            ],
            'service' => 'HMO fire safety pack',
            'wa' => 'Hi iComply, I need an HMO fire safety quote (FRA and any alarms, lighting or doors). I know the £650 bundle is only when FRA, EICR and gas are together.',
            'showCards' => false,
            'ctaTitle' => 'Request an HMO fire safety quote',
            'ctaText' => 'Tell us storeys, number of lets, whether a fire alarm or emergency lighting is already fitted, and if EICR and gas are due as well. If all three certificates are due on a typical 6-bed in the North West, use the **' . $bundle . ' bundle** instead of pricing them separately.',
        ],
        'hmo-occupancy' => [
            'id' => 'hmo-occupancy',
            'path' => '/pages/jobs/hmo-occupancy',
            'file' => '/pages/jobs/hmo-occupancy.php',
            'primaryBundle' => false,
            'pageTitle' => 'HMO occupancy pack | iComply',
            'metaDesc' => 'HMO re-let certificates: EICR and gas safety on the North West 6-bed list, plus smoke and CO as scoped. Add an FRA and the job becomes the £650 bundle.',
            'keywords' => 'HMO re-let certificates, HMO occupancy pack, HMO EICR gas, HMO void Stockport',
            'ogImage' => '/assets/images/services/smoke-co-alarms.jpg',
            'kicker' => 'Re-let and void',
            'h1' => 'HMO occupancy pack',
            'lede' => 'EICR and gas safety so a shared house can be re-let, plus smoke and CO checks as scoped. This is not the **' . $bundle . ' bundle** unless a fire risk assessment is due on the same typical 6-bed. Then FRA, EICR and gas are priced once, at ' . $bundle . '.',
            'crumbs' => [
                ['name' => 'Landlords', 'href' => '/pages/landlords'],
                ['name' => 'HMO packages', 'href' => '/pages/jobs/hmo'],
                ['name' => 'Occupancy pack', 'href' => '/pages/jobs/hmo-occupancy', 'current' => true],
            ],
            'proof' => [
                'EICR £249 · gas £85 · typical 6-bed',
                'Smoke and CO scoped on the visit',
                'FRA added → ' . $bundle . ' bundle',
            ],
            'service' => 'HMO occupancy pack',
            'wa' => 'Hi iComply, I need an HMO re-let quote (EICR and gas, smoke/CO if required). Add the FRA only if it is due — I know that becomes the £650 bundle on a typical 6-bed.',
            'showCards' => false,
            'ctaTitle' => 'Request an HMO re-let quote',
            'ctaText' => 'Postcode, bedrooms, and whether the fire risk assessment is already in date. If it is due as well, ask for the **' . $bundle . ' compliance bundle** rather than EICR and gas on their own.',
        ],
    ];
}

function hmoJobPage(string $id): array
{
    $pages = hmoJobPages();
    if (!isset($pages[$id])) {
        throw new InvalidArgumentException('Unknown HMO job page: ' . $id);
    }
    return $pages[$id];
}

/**
 * @return list<array{href:string,label:string,price:string,blurb:string,bundle:bool}>
 */
function hmoJobCards(): array
{
    return [
        [
            'href' => '/pages/jobs/hmo-compliance',
            'label' => 'Compliance bundle',
            'price' => hmoJobFormatPence(hmoJobBundleListPence()),
            'blurb' => 'FRA, EICR and gas on one typical 6-bed HMO in the North West. The three certificates are not added on top.',
            'bundle' => true,
        ],
        [
            'href' => '/pages/jobs/hmo-fire-safety',
            'label' => 'Fire safety pack',
            'price' => 'From the list',
            'blurb' => 'FRA at £350 for a typical 6-bed, then alarm, lighting and door work only where the house needs it.',
            'bundle' => false,
        ],
        [
            'href' => '/pages/jobs/hmo-occupancy',
            'label' => 'Occupancy pack',
            'price' => 'EICR £249 + gas £85',
            'blurb' => 'Re-let file without a new FRA. If the FRA is due as well, the job is the £650 bundle.',
            'bundle' => false,
        ],
    ];
}

/**
 * Lines that match the approved 6-bed North West list. No invented figures.
 *
 * @return list<array{name:string,price:string,note:string}>
 */
function hmoJobFireLines(): array
{
    return [
        ['name' => 'Fire risk assessment', 'price' => '£350', 'note' => 'Typical 6-bed HMO. Remedials quoted after the assessment.'],
        ['name' => 'Fire alarm inspection', 'price' => '£150', 'note' => 'Inspection visit. Remedials and parts after survey.'],
        ['name' => 'Emergency lighting check', 'price' => '£125', 'note' => 'Check for a typical 6-bed. Remedials after survey.'],
        ['name' => 'AOV / smoke ventilation inspection', 'price' => '£200', 'note' => 'Where an AOV is fitted. Remedials after survey.'],
    ];
}

/**
 * @return list<array{name:string,price:string,note:string}>
 */
function hmoJobOccupancyLines(): array
{
    return [
        ['name' => 'EICR', 'price' => '£249', 'note' => 'Typical 6-bed HMO. Remedials quoted after the inspection.'],
        ['name' => 'Gas safety (CP12)', 'price' => '£85', 'note' => 'Landlord gas safety record. Gas Safe registered engineers. Remedials after the visit.'],
    ];
}
