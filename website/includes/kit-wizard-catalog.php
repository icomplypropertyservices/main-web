<?php
/**
 * Trade kit-builder catalogues.
 * Branded manufacturers only. Screwfix is a price reference for the same branded SKU.
 * Never list Screwfix own-brand / SFX / LAP / Time cable / BG boards.
 */
declare(strict_types=1);

if (!function_exists('kitWizardCatalog')) {

function kitCdn(string $file): string
{
    return 'https://cdn.shopify.com/s/files/1/1073/5550/4972/files/' . ltrim($file, '/');
}

function kitAsset(string $rel): string
{
    return '/assets/images/' . ltrim($rel, '/');
}

function kitLogo(string $brand): string
{
    $map = [
        'Hager' => kitAsset('manufacturers/hager-consumer-unit.jpg'),
        'Worcester Bosch' => kitAsset('manufacturers/worcester-bosch.jpg'),
        'Apollo' => kitAsset('manufacturers/apollo-fire.jpg'),
        'Hochiki' => kitAsset('manufacturers/hochiki.jpg'),
        'C-TEC' => kitAsset('manufacturers/c-tec.jpg'),
        'Advanced' => kitAsset('manufacturers/advanced-fire-panel.jpg'),
        'Kentec' => kitAsset('manufacturers/kentec.jpg'),
        'Paxton' => kitAsset('manufacturers/paxton.jpg'),
        'Aiphone' => kitAsset('manufacturers/aiphone.jpg'),
        'Schneider' => kitAsset('manufacturers/schneider-electrical.jpg'),
        'Salto' => kitAsset('manufacturers/salto-access.jpg'),
        'CAME' => kitCdn('nav-came-line.jpg'),
    ];
    return $map[$brand] ?? '';
}

/** Live Shopify product — no £ on the card (shop host owns price). */
function kitShopOpt(array $p): array
{
    return array_merge([
        'cta' => 'shop',
        'sell_price' => '',
        'screwfix_ref' => '',
    ], $p);
}

function kitPoaOpt(array $p): array
{
    return array_merge([
        'cta' => 'poa',
        'sell_price' => '',
        'sku' => '',
        'handle' => '',
        'variant_id' => '',
        'screwfix_ref' => '',
    ], $p);
}

/** Question step — not a sellable line, so no Enquire/POA badge and no cart SKU. */
function kitChoiceOpt(array $p): array
{
    return array_merge([
        'cta' => 'choose',
        'sell_price' => '',
        'sku' => '',
        'handle' => '',
        'variant_id' => '',
        'screwfix_ref' => '',
        'screwfix_inc' => '',
    ], $p);
}

/** Jack lock — Screwfix is a price reference, never a brand we list. */
function kitBrandPolicy(): string
{
    return 'Branded manufacturers only. Screwfix is a price reference for the same branded SKU (sell = that inc-VAT figure + 15%). Never Screwfix own-make / own-brand, LAP, Time (SFX cable), SFX accessories, British General / BG boards, or unbranded white-label sockets. If a line is only sold as Screwfix own-brand, skip it and quote a branded equivalent — enquire / POA. No invented £.';
}

/** @return list<string> */
function kitBannedBrandKeys(): array
{
    return [
        'lap',
        'time',
        'sfx',
        'screwfix',
        'british general',
        'bg',
        'bg electrical',
        'unbranded',
        'tunstall',
        'white-label',
        'white label',
        'own-brand',
        'own brand',
    ];
}

function kitBrandIsBanned(string $brand): bool
{
    $key = strtolower(trim($brand));
    if ($key === '') {
        return false;
    }
    if (str_contains($key, 'screwfix')) {
        return true;
    }
    foreach (kitBannedBrandKeys() as $banned) {
        if ($key === $banned || str_starts_with($key, $banned . ' ')) {
            return true;
        }
    }
    return false;
}

function kitWithPolicy(array $w): array
{
    $w['brand_policy'] = kitBrandPolicy();
    $summary = trim((string)($w['summary_blurb'] ?? ''));
    if ($summary === '') {
        $w['summary_blurb'] = kitBrandPolicy();
    } elseif (!str_contains(strtolower($summary), 'price reference')) {
        $w['summary_blurb'] = rtrim($summary, '.') . '. Screwfix is a price reference for the same branded SKU only — never own-brand / LAP / Time / SFX / BG.';
    }
    return $w;
}

/** @param array<string,array<string,mixed>> $wizards */
function kitAssertCatalogBrands(array $wizards): void
{
    foreach ($wizards as $w) {
        foreach ($w['steps'] ?? [] as $step) {
            foreach ($step['options'] ?? [] as $opt) {
                $brand = (string)($opt['brand'] ?? '');
                if ($brand !== '' && kitBrandIsBanned($brand)) {
                    throw new RuntimeException('Banned brand in kit ' . ($w['slug'] ?? '?') . ': ' . $brand);
                }
                $sell = (string)($opt['sell_price'] ?? '');
                if ($sell !== '') {
                    $ref = (string)($opt['screwfix_ref'] ?? '');
                    $inc = (string)($opt['screwfix_inc'] ?? '');
                    if ($brand !== 'Wylex' || $ref === '' || $inc === '') {
                        throw new RuntimeException('Invented sell price without a branded Wylex Screwfix ref in kit ' . ($w['slug'] ?? '?'));
                    }
                    $expected = number_format(round((float)$inc * 1.15, 2), 2, '.', '');
                    if ($expected !== $sell) {
                        throw new RuntimeException('Sell £' . $sell . ' is not Screwfix inc VAT ' . $inc . ' + 15% (£' . $expected . ') for ' . $ref);
                    }
                }
            }
        }
    }
}

/**
 * Wylex consumer units — Screwfix inc VAT fetched 2026-09-15 + 15% sell.
 * Same branded Wylex SKU only. Screwfix is not the manufacturer.
 *
 * @return list<array<string,mixed>>
 */
function kitWylexBoards(): array
{
    $logo = ''; // no unique Wylex still in repo — brand chip, not a fake photo
    $img = kitAsset('services/electrical.jpg');
    $rows = [
        ['id' => 'wylex-840jt', 'style' => 'populated-dual-rcd', 'label' => 'Wylex 10-way populated dual RCD', 'blurb' => 'Branded Wylex dual-RCD board. 10 ways.', 'ref' => '840JT', 'sf' => '129.99', 'sell' => '149.49'],
        ['id' => 'wylex-102jk', 'style' => 'populated-dual-rcd', 'label' => 'Wylex 12-way populated dual RCD', 'blurb' => 'Branded Wylex dual-RCD board. 12 ways.', 'ref' => '102JK', 'sf' => '162.49', 'sell' => '186.86'],
        ['id' => 'wylex-605vf', 'style' => 'hi-spd', 'label' => 'Wylex 14-way HI + SPD', 'blurb' => 'Branded Wylex high-integrity board with SPD.', 'ref' => '605VF', 'sf' => '137.99', 'sell' => '158.69'],
        ['id' => 'wylex-472vf', 'style' => 'hi-spd', 'label' => 'Wylex 9-way HI dual RCD + SPD', 'blurb' => 'Branded Wylex HI dual RCD with SPD.', 'ref' => '472VF', 'sf' => '169.99', 'sell' => '195.49'],
        ['id' => 'wylex-8298j', 'style' => 'part-pop', 'label' => 'Wylex 8-way part-populated main switch', 'blurb' => 'Branded Wylex part-populated main-switch board.', 'ref' => '8298J', 'sf' => '44.99', 'sell' => '51.74'],
        ['id' => 'wylex-1460j', 'style' => 'part-pop', 'label' => 'Wylex 5-way part-populated', 'blurb' => 'Branded Wylex part-populated board.', 'ref' => '1460J', 'sf' => '39.99', 'sell' => '45.99'],
        ['id' => 'wylex-5339j', 'style' => 'part-pop', 'label' => 'Wylex 11-way part-populated', 'blurb' => 'Branded Wylex part-populated board.', 'ref' => '5339J', 'sf' => '43.99', 'sell' => '50.59'],
        ['id' => 'wylex-5452p', 'style' => 'part-pop', 'label' => 'Wylex 19-way part-populated', 'blurb' => 'Branded Wylex part-populated board.', 'ref' => '5452P', 'sf' => '57.99', 'sell' => '66.69'],
        ['id' => 'wylex-356jk', 'style' => 'special', 'label' => 'Wylex garage 2-way', 'blurb' => 'Branded Wylex garage consumer unit.', 'ref' => '356JK', 'sf' => '49.99', 'sell' => '57.49'],
        ['id' => 'wylex-722jk', 'style' => 'special', 'label' => 'Wylex shower 1-way', 'blurb' => 'Branded Wylex shower consumer unit.', 'ref' => '722JK', 'sf' => '47.99', 'sell' => '55.19'],
    ];
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'id' => $r['id'],
            'label' => $r['label'],
            'blurb' => $r['blurb'] . ' Sell = Screwfix inc VAT of this same Wylex SKU + 15% (price reference only).',
            'brand' => 'Wylex',
            'image' => $img,
            'cover' => true,
            'logo' => $logo,
            'sku' => $r['ref'],
            'screwfix_ref' => $r['ref'],
            'screwfix_inc' => $r['sf'],
            'sell_price' => $r['sell'],
            'cta' => 'enquire',
            'show_if' => ['step' => 'cu-style', 'values' => [$r['style']]],
        ];
    }
    return $out;
}

function kitRewireWizard(): array
{
    $elec = kitAsset('services/electrical.jpg');
    return [
        'slug' => 'rewire',
        'title' => 'Rewire kit builder',
        'short' => 'Rewire',
        'kicker' => 'Consumer unit / circuits',
        'blurb' => 'Wylex boards (priced where Screwfix stocks that Wylex SKU) plus Click Scolmore accessories (enquire / POA). Hager / MK / Schneider protection is enquire / POA. Branded manufacturers only — no Screwfix own-brand, LAP, Time/SFX cable, SFX accessories, BG boards or unbranded sockets.',
        'service' => 'electrical',
        'hero_image' => $elec,
        'summary_title' => 'Rewire kit',
        'summary_blurb' => 'Wylex sell price = Screwfix inc VAT of that same Wylex SKU + 15% (fetched 15 Sep 2026). Click CMA/VP codes are not stocked as those branded SKUs on Screwfix — enquire / POA. Hager / MK / Schneider stay enquire / POA until a matching branded Screwfix (or trade-list) price exists. Screwfix is a price reference, not a brand we sell.',
        'steps' => [
            [
                'id' => 'property',
                'title' => 'Property type',
                'short' => 'Property',
                'blurb' => 'Tells us how we size the board and circuits.',
                'options' => [
                    kitChoiceOpt(['id' => 'house', 'label' => 'House', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'Typical domestic rewire / CU change.']),
                    kitChoiceOpt(['id' => 'flat', 'label' => 'Flat / apartment', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'Often a compact CU and landlord access.']),
                    kitChoiceOpt(['id' => 'hmo', 'label' => 'HMO', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'More circuits and detection — quoted after survey.']),
                    kitChoiceOpt(['id' => 'commercial', 'label' => 'Commercial / landlord block', 'brand' => 'Schneider', 'logo' => kitLogo('Schneider'), 'image' => kitAsset('manufacturers/schneider-electrical.jpg'), 'blurb' => 'Distribution beyond a domestic CU is enquire / POA.']),
                ],
            ],
            [
                'id' => 'scope',
                'title' => 'Job scope',
                'short' => 'Scope',
                'blurb' => 'Full rewires and CU changes use the same branded Wylex / Click path.',
                'options' => [
                    kitChoiceOpt(['id' => 'cu-only', 'label' => 'Consumer-unit change', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'Replace the board, keep existing circuits where safe.']),
                    kitChoiceOpt(['id' => 'partial', 'label' => 'Partial rewire', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'Kitchen, extension or failed circuits.']),
                    kitChoiceOpt(['id' => 'full', 'label' => 'Full rewire', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'New circuits, Click accessories, Aico detection.']),
                ],
            ],
            [
                'id' => 'cu-style',
                'title' => 'Wylex board style',
                'short' => 'CU style',
                'blurb' => 'Wylex for priced boards. Hager, MK and Schneider are allowed branded protection — enquire / POA until we hold a real branded SKU and a matching Screwfix (or trade-list) price. No BG / British General boards.',
                'note' => 'If Screwfix does not stock that branded SKU, we do not invent a price. Own-brand / LAP / SFX boards are never listed.',
                'options' => [
                    kitChoiceOpt(['id' => 'populated-dual-rcd', 'label' => 'Populated dual RCD', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => '10- or 12-way populated Wylex dual-RCD boards.']),
                    kitChoiceOpt(['id' => 'hi-spd', 'label' => 'High integrity + SPD', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'HI boards with surge protection.']),
                    kitChoiceOpt(['id' => 'part-pop', 'label' => 'Part-populated main switch', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'Add branded RCBOs / MCBs to suit the job.']),
                    kitChoiceOpt(['id' => 'special', 'label' => 'Garage / shower unit', 'brand' => 'Wylex', 'image' => $elec, 'cover' => true, 'blurb' => 'Small dedicated Wylex units.']),
                    kitChoiceOpt(['id' => 'hager-poa', 'label' => 'Hager (enquire / POA)', 'brand' => 'Hager', 'logo' => kitLogo('Hager'), 'image' => kitAsset('manufacturers/hager-consumer-unit.jpg'), 'blurb' => 'Allowed branded protection. No Screwfix price attached to a Hager SKU here.']),
                    kitChoiceOpt(['id' => 'mk-poa', 'label' => 'MK (enquire / POA)', 'brand' => 'MK', 'image' => $elec, 'cover' => true, 'blurb' => 'Allowed branded protection. No invented £.']),
                    kitChoiceOpt(['id' => 'schneider-poa', 'label' => 'Schneider (enquire / POA)', 'brand' => 'Schneider', 'logo' => kitLogo('Schneider'), 'image' => kitAsset('manufacturers/schneider-electrical.jpg'), 'blurb' => 'Allowed branded protection. Enquire / POA.']),
                ],
            ],
            [
                'id' => 'cu',
                'title' => 'Wylex consumer unit',
                'short' => 'Wylex CU',
                'blurb' => 'Sell price is the branded Wylex Screwfix inc-VAT figure + 15%. Ref is the same Wylex SKU. Hager / MK / Schneider stay enquire / POA.',
                'note' => 'Screwfix is a price reference only. We supply the manufacturer named on the card — never Screwfix own-brand boards.',
                'options' => array_merge(kitWylexBoards(), [
                    kitPoaOpt(['id' => 'hager-cu', 'label' => 'Hager consumer unit', 'brand' => 'Hager', 'logo' => kitLogo('Hager'), 'image' => kitAsset('manufacturers/hager-consumer-unit.jpg'), 'blurb' => 'Branded Hager — enquire / POA. No invented £.', 'show_if' => ['step' => 'cu-style', 'values' => ['hager-poa']]]),
                    kitPoaOpt(['id' => 'mk-cu', 'label' => 'MK circuit protection', 'brand' => 'MK', 'image' => $elec, 'cover' => true, 'blurb' => 'Branded MK — enquire / POA. No invented £.', 'show_if' => ['step' => 'cu-style', 'values' => ['mk-poa']]]),
                    kitPoaOpt(['id' => 'schneider-cu', 'label' => 'Schneider circuit protection', 'brand' => 'Schneider', 'logo' => kitLogo('Schneider'), 'image' => kitAsset('manufacturers/schneider-electrical.jpg'), 'blurb' => 'Branded Schneider — enquire / POA. No invented £.', 'show_if' => ['step' => 'cu-style', 'values' => ['schneider-poa']]]),
                ]),
            ],
            [
                'id' => 'click-range',
                'title' => 'Click Scolmore finish',
                'short' => 'Click range',
                'blurb' => 'Accessories are Click Scolmore only (Mode, Deco, GridPro, Essentials, Aquip66, Metal Clad). Not BG / unbranded white-label.',
                'note' => 'Click CMA / VP codes are not stocked as those branded SKUs on Screwfix (search returns junk / BG). No invented £ — enquire / POA until a real trade-list scrape + 15% is attached.',
                'options' => [
                    kitPoaOpt(['id' => 'mode', 'label' => 'Click Mode', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true, 'blurb' => 'Screwed decorative range. Enquire / POA.']),
                    kitPoaOpt(['id' => 'deco', 'label' => 'Click Deco', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true, 'blurb' => 'Screwless decorative. Enquire / POA.']),
                    kitPoaOpt(['id' => 'gridpro', 'label' => 'Click GridPro', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true, 'blurb' => 'Modular grid. Enquire / POA.']),
                    kitPoaOpt(['id' => 'essentials', 'label' => 'Click Essentials', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true, 'blurb' => 'Contract white. Enquire / POA.']),
                    kitPoaOpt(['id' => 'aquip66', 'label' => 'Click Aquip66', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true, 'blurb' => 'IP66 outdoor / utility. Enquire / POA.']),
                    kitPoaOpt(['id' => 'metal-clad', 'label' => 'Click Metal Clad', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true, 'blurb' => 'Metal clad. Enquire / POA.']),
                ],
            ],
            [
                'id' => 'accessories',
                'title' => 'Click accessories',
                'short' => 'Accessories',
                'multi' => true,
                'optional' => true,
                'blurb' => 'Click Scolmore only. Quantities confirmed on the quote — enquire / POA.',
                'options' => [
                    kitPoaOpt(['id' => 'socket', 'label' => 'Sockets (13A)', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'switch', 'label' => 'Light switches', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'usb', 'label' => 'USB sockets', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'fcu', 'label' => 'Fused connection units', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'cooker', 'label' => 'Cooker control', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'isolator', 'label' => 'Isolators', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'data', 'label' => 'Data / RJ45', 'brand' => 'Click Scolmore', 'image' => $elec, 'cover' => true]),
                ],
            ],
            [
                'id' => 'cable',
                'title' => 'T&E cable (branded)',
                'short' => 'Cable',
                'multi' => true,
                'optional' => true,
                'blurb' => 'Prysmian or equivalent branded BASEC T&E. Never Time / SFX / Screwfix own-brand cable.',
                'note' => 'No Screwfix price is attached to a branded T&E SKU here — lengths are enquire / POA.',
                'options' => [
                    kitPoaOpt(['id' => 'te-1-0', 'label' => '1.0 mm² T&E', 'brand' => 'Prysmian', 'image' => $elec, 'cover' => true, 'blurb' => 'Lighting. Branded drum — enquire / POA.']),
                    kitPoaOpt(['id' => 'te-1-5', 'label' => '1.5 mm² T&E', 'brand' => 'Prysmian', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'te-2-5', 'label' => '2.5 mm² T&E', 'brand' => 'Prysmian', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'te-4', 'label' => '4.0 mm² T&E', 'brand' => 'Prysmian', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'te-6', 'label' => '6.0 mm² T&E', 'brand' => 'Prysmian', 'image' => $elec, 'cover' => true]),
                    kitPoaOpt(['id' => 'te-10', 'label' => '10.0 mm² T&E', 'brand' => 'Prysmian', 'image' => $elec, 'cover' => true]),
                ],
            ],
            [
                'id' => 'detection',
                'title' => 'Smoke / CO',
                'short' => 'Aico / Kidde',
                'multi' => true,
                'optional' => true,
                'blurb' => 'Aico or Kidde only — not unbranded detectors.',
                'note' => 'No branded Screwfix sell price is locked on these SKUs yet — enquire / POA.',
                'options' => [
                    kitPoaOpt(['id' => 'aico-smoke', 'label' => 'Aico smoke alarms', 'brand' => 'Aico', 'image' => kitAsset('services/smoke-co-alarms.jpg'), 'cover' => true, 'blurb' => 'Mains smoke to BS 5839-6. Enquire / POA.']),
                    kitPoaOpt(['id' => 'aico-heat', 'label' => 'Aico heat alarms', 'brand' => 'Aico', 'image' => kitAsset('services/smoke-co-alarms.jpg'), 'cover' => true]),
                    kitPoaOpt(['id' => 'aico-co', 'label' => 'Aico carbon monoxide', 'brand' => 'Aico', 'image' => kitAsset('services/smoke-co-alarms.jpg'), 'cover' => true]),
                    kitPoaOpt(['id' => 'kidde', 'label' => 'Kidde detection', 'brand' => 'Kidde', 'image' => kitAsset('services/smoke-co-alarms.jpg'), 'cover' => true, 'blurb' => 'Kidde where specified. Enquire / POA.']),
                ],
            ],
        ],
    ];
}

function kitHeatingWizard(): array
{
    $heat = kitAsset('services/heating.jpg');
    $wb = kitAsset('manufacturers/worcester-bosch.jpg');
    return [
        'slug' => 'heating',
        'title' => 'Central heating kit builder',
        'short' => 'Heating',
        'kicker' => 'Boiler / system',
        'blurb' => 'Worcester Bosch, Vaillant, Ideal and Baxi only. Gas Safe survey — no invented boiler £. No Screwfix own-brand boilers or unbranded white-label plant.',
        'service' => 'heating',
        'hero_image' => $heat,
        'summary_blurb' => 'Heating plant is quoted after survey. Branded manufacturers only. Screwfix is a price reference for the same branded SKU — never own-brand boilers.',
        'steps' => [
            [
                'id' => 'system',
                'title' => 'System type',
                'short' => 'System',
                'blurb' => 'Combi, system or heat-only.',
                'options' => [
                    kitChoiceOpt(['id' => 'combi', 'label' => 'Combi boiler', 'image' => $heat, 'cover' => true, 'blurb' => 'Most flats and smaller houses. Brand is the next step.']),
                    kitChoiceOpt(['id' => 'system', 'label' => 'System boiler', 'image' => $heat, 'cover' => true, 'blurb' => 'Brand is the next step.']),
                    kitChoiceOpt(['id' => 'heat-only', 'label' => 'Heat-only / conventional', 'image' => $heat, 'cover' => true, 'blurb' => 'Brand is the next step.']),
                ],
            ],
            [
                'id' => 'brand',
                'title' => 'Boiler brand',
                'short' => 'Brand',
                'blurb' => 'Worcester Bosch, Vaillant, Ideal or Baxi only. Output and flue are confirmed on survey. No Screwfix own-brand.',
                'options' => [
                    kitPoaOpt(['id' => 'worcester', 'label' => 'Worcester Bosch', 'brand' => 'Worcester Bosch', 'logo' => kitLogo('Worcester Bosch'), 'image' => $wb]),
                    kitPoaOpt(['id' => 'vaillant', 'label' => 'Vaillant', 'brand' => 'Vaillant', 'image' => $heat, 'cover' => true, 'blurb' => 'No unique Vaillant still in the repo — enquire / POA.']),
                    kitPoaOpt(['id' => 'ideal', 'label' => 'Ideal', 'brand' => 'Ideal', 'image' => $heat, 'cover' => true, 'blurb' => 'Enquire / POA after survey.']),
                    kitPoaOpt(['id' => 'baxi', 'label' => 'Baxi', 'brand' => 'Baxi', 'image' => $heat, 'cover' => true, 'blurb' => 'Enquire / POA after survey.']),
                ],
            ],
            [
                'id' => 'extras',
                'title' => 'Controls & extras',
                'short' => 'Extras',
                'multi' => true,
                'optional' => true,
                'blurb' => 'Matching manufacturer controls and extras — enquire / POA. Never Screwfix own-brand controls.',
                'options' => [
                    kitPoaOpt(['id' => 'worcester-controls', 'label' => 'Worcester programmer / thermostat', 'brand' => 'Worcester Bosch', 'logo' => kitLogo('Worcester Bosch'), 'image' => $wb, 'show_if' => ['step' => 'brand', 'values' => ['worcester']]]),
                    kitPoaOpt(['id' => 'worcester-filter', 'label' => 'Worcester system filter', 'brand' => 'Worcester Bosch', 'logo' => kitLogo('Worcester Bosch'), 'image' => $wb, 'show_if' => ['step' => 'brand', 'values' => ['worcester']]]),
                    kitPoaOpt(['id' => 'worcester-flue', 'label' => 'Worcester flue / plume kit', 'brand' => 'Worcester Bosch', 'logo' => kitLogo('Worcester Bosch'), 'image' => $wb, 'show_if' => ['step' => 'brand', 'values' => ['worcester']]]),
                    kitPoaOpt(['id' => 'vaillant-controls', 'label' => 'Vaillant controls / extras', 'brand' => 'Vaillant', 'image' => $heat, 'cover' => true, 'blurb' => 'Manufacturer extras — enquire / POA.', 'show_if' => ['step' => 'brand', 'values' => ['vaillant']]]),
                    kitPoaOpt(['id' => 'ideal-controls', 'label' => 'Ideal controls / extras', 'brand' => 'Ideal', 'image' => $heat, 'cover' => true, 'blurb' => 'Manufacturer extras — enquire / POA.', 'show_if' => ['step' => 'brand', 'values' => ['ideal']]]),
                    kitPoaOpt(['id' => 'baxi-controls', 'label' => 'Baxi controls / extras', 'brand' => 'Baxi', 'image' => $heat, 'cover' => true, 'blurb' => 'Manufacturer extras — enquire / POA.', 'show_if' => ['step' => 'brand', 'values' => ['baxi']]]),
                ],
            ],
        ],
    ];
}

function kitFireWizard(): array
{
    $fire = kitAsset('services/fire-alarms.jpg');
    return [
        'slug' => 'fire-alarm',
        'title' => 'Fire alarm kit builder',
        'short' => 'Fire alarm',
        'kicker' => 'BS 5839',
        'blurb' => 'Advanced, C-TEC, Kentec panels with Apollo or Hochiki devices — live branded Shopify SKUs. No invented £. No Screwfix own-brand detectors or panels.',
        'service' => 'fire-alarms',
        'hero_image' => $fire,
        'summary_blurb' => 'Known branded SKUs deep-link to Shopify. Loop counts and cause-and-effect stay enquire / POA. Screwfix is a price reference only — never own-brand fire kit.',
        'steps' => [
            [
                'id' => 'system',
                'title' => 'System type',
                'short' => 'Type',
                'blurb' => 'Conventional or addressable.',
                'options' => [
                    kitChoiceOpt(['id' => 'conventional', 'label' => 'Conventional', 'brand' => 'C-TEC', 'logo' => kitLogo('C-TEC'), 'image' => kitCdn('ctec-cfp-panel.jpg?v=1788882289'), 'blurb' => 'C-TEC CFP / Kentec Sigma class.']),
                    kitChoiceOpt(['id' => 'addressable', 'label' => 'Addressable', 'brand' => 'Advanced', 'logo' => kitLogo('Advanced'), 'image' => kitCdn('advanced-mxpro.jpg?v=1788896194'), 'blurb' => 'Advanced MxPro 5 / C-TEC XFP / Kentec Syncro.']),
                ],
            ],
            [
                'id' => 'panel',
                'title' => 'Control panel',
                'short' => 'Panel',
                'blurb' => 'Branded panels from the live shop.',
                'options' => [
                    kitShopOpt(['id' => 'ctec-cfp', 'label' => 'C-TEC CFP 4-wire', 'brand' => 'C-TEC', 'logo' => kitLogo('C-TEC'), 'image' => kitCdn('ctec-cfp-panel.jpg?v=1788882289'), 'sku' => 'CFP702-4', 'handle' => 'ctec-cfp-4-wire', 'variant_id' => '58735444099404', 'show_if' => ['step' => 'system', 'values' => ['conventional']], 'blurb' => 'CFP702-4 family.']),
                    kitShopOpt(['id' => 'kentec-sigma', 'label' => 'Kentec Sigma CP', 'brand' => 'Kentec', 'logo' => kitLogo('Kentec'), 'image' => kitCdn('kentec-sigma.jpg?v=1788896199'), 'sku' => 'K11020M2', 'handle' => 'kentec-sigma-cp', 'variant_id' => '58740980449612', 'show_if' => ['step' => 'system', 'values' => ['conventional']]]),
                    kitShopOpt(['id' => 'advanced-mx', 'label' => 'Advanced MxPro 5', 'brand' => 'Advanced', 'logo' => kitLogo('Advanced'), 'image' => kitCdn('advanced-mxpro.jpg?v=1788896194'), 'sku' => 'MX-5101', 'handle' => 'advanced-mxpro5', 'variant_id' => '58740979827020', 'show_if' => ['step' => 'system', 'values' => ['addressable']]]),
                    kitShopOpt(['id' => 'ctec-xfp', 'label' => 'C-TEC XFP', 'brand' => 'C-TEC', 'logo' => kitLogo('C-TEC'), 'image' => kitCdn('ctec-xfp-panel.jpg?v=1788882600'), 'sku' => 'XFP501E/X', 'handle' => 'ctec-xfp-panel', 'variant_id' => '58735654928716', 'show_if' => ['step' => 'system', 'values' => ['addressable']]]),
                    kitShopOpt(['id' => 'kentec-syncro', 'label' => 'Kentec Syncro AS', 'brand' => 'Kentec', 'logo' => kitLogo('Kentec'), 'image' => kitCdn('kentec-syncro.jpg?v=1788896205'), 'sku' => 'A81161M2', 'handle' => 'kentec-syncro-as', 'variant_id' => '58740980941132', 'show_if' => ['step' => 'system', 'values' => ['addressable']]]),
                ],
            ],
            [
                'id' => 'detectors',
                'title' => 'Detectors',
                'short' => 'Heads',
                'multi' => true,
                'blurb' => 'Apollo and Hochiki from the live shop.',
                'options' => [
                    kitShopOpt(['id' => 'ap-xp95', 'label' => 'Apollo XP95 detector', 'brand' => 'Apollo', 'logo' => kitLogo('Apollo'), 'image' => kitCdn('apollo-xp95-optical-official.jpg?v=1788979787'), 'sku' => '55000-600APO', 'handle' => 'apollo-xp95-detector', 'variant_id' => '58736200941900']),
                    kitShopOpt(['id' => 'ap-disc', 'label' => 'Apollo Discovery detector', 'brand' => 'Apollo', 'logo' => kitLogo('Apollo'), 'image' => kitCdn('apollo-discovery-optical-official.jpg?v=1788979793'), 'sku' => '58000-600APO', 'handle' => 'apollo-discovery-detector', 'variant_id' => '58736201728332']),
                    kitShopOpt(['id' => 'ap-s65', 'label' => 'Apollo Series 65 detector', 'brand' => 'Apollo', 'logo' => kitLogo('Apollo'), 'image' => kitCdn('apollo-s65-optical-official.jpg?v=1788979797'), 'sku' => '55000-317APO', 'handle' => 'apollo-series-65-detector', 'variant_id' => '58736199041356']),
                    kitShopOpt(['id' => 'ho-aln', 'label' => 'Hochiki ALN-EN optical', 'brand' => 'Hochiki', 'logo' => kitLogo('Hochiki'), 'image' => kitCdn('hochiki-aln-en.jpg?v=1788883972'), 'sku' => 'ALN-EN', 'handle' => 'hochiki-aln-en', 'variant_id' => '58736611754316']),
                    kitShopOpt(['id' => 'ho-atj', 'label' => 'Hochiki ATJ-EN heat', 'brand' => 'Hochiki', 'logo' => kitLogo('Hochiki'), 'image' => kitCdn('hochiki-atj-en.jpg?v=1788883975'), 'sku' => 'ATJ-EN', 'handle' => 'hochiki-atj-en', 'variant_id' => '58736613753164']),
                ],
            ],
            [
                'id' => 'alert',
                'title' => 'Call points & sounders',
                'short' => 'Alert',
                'multi' => true,
                'optional' => true,
                'blurb' => 'Apollo, Hochiki and C-TEC notification.',
                'options' => [
                    kitShopOpt(['id' => 'ap-mcp', 'label' => 'Apollo XP95 MCP', 'brand' => 'Apollo', 'logo' => kitLogo('Apollo'), 'image' => kitCdn('apollo-mcp-xp95.jpg?v=1788895958'), 'sku' => '55100-905APO', 'handle' => 'apollo-xp95-mcp', 'variant_id' => '58737147707724']),
                    kitShopOpt(['id' => 'ho-mcp', 'label' => 'Hochiki HCP-E', 'brand' => 'Hochiki', 'logo' => kitLogo('Hochiki'), 'image' => kitCdn('hochiki-hcp-e.png?v=1788895959'), 'sku' => 'HCP-E', 'handle' => 'hochiki-hcp-e', 'variant_id' => '58737148985676']),
                    kitShopOpt(['id' => 'ho-ws2', 'label' => 'Hochiki CHQ-WS2 sounder', 'brand' => 'Hochiki', 'logo' => kitLogo('Hochiki'), 'image' => kitCdn('hochiki-chq-ws2.jpg?v=1788977439'), 'sku' => 'CHQ-WS2', 'handle' => 'hochiki-chq-ws2', 'variant_id' => '58749565763916']),
                    kitShopOpt(['id' => 'ctec-snd', 'label' => 'C-TEC ActiV sounder', 'brand' => 'C-TEC', 'logo' => kitLogo('C-TEC'), 'image' => kitCdn('c-tec-bf430c-cc-dr-65-conventional-wall-s.jpg?v=1788982726'), 'sku' => 'BF430C/CC/DR/65', 'handle' => 'ctec-bf430-sounder', 'variant_id' => '58750264574284']),
                ],
            ],
        ],
    ];
}

function kitElWizard(): array
{
    $el = kitAsset('services/emergency-lighting.jpg');
    return [
        'slug' => 'emergency-lighting',
        'title' => 'Emergency lighting kit builder',
        'short' => 'Emergency lighting',
        'kicker' => 'BS 5266',
        'blurb' => 'Branded Espire / Eaton fittings when a manufacturer SKU exists. No live EL SKUs on Shopify today — enquire / POA. Never Screwfix own-brand emergency fittings. No invented £.',
        'service' => 'emergency-lighting',
        'hero_image' => $el,
        'summary_blurb' => 'Fittings and central battery are quoted after a lux / occupancy survey. Branded manufacturers only — Screwfix is a price reference, never own-brand EL.',
        'steps' => [
            [
                'id' => 'building',
                'title' => 'Building',
                'short' => 'Building',
                'options' => [
                    kitChoiceOpt(['id' => 'small', 'label' => 'Small commercial / landlord', 'brand' => 'Espire', 'image' => $el, 'cover' => true]),
                    kitChoiceOpt(['id' => 'multi', 'label' => 'Multi-storey / care', 'brand' => 'Eaton', 'image' => $el, 'cover' => true]),
                ],
            ],
            [
                'id' => 'fittings',
                'title' => 'Fitting type',
                'short' => 'Fittings',
                'multi' => true,
                'blurb' => 'Typical emergency lighting categories — branded Espire / Eaton quoted after survey.',
                'options' => [
                    kitPoaOpt(['id' => 'nm', 'label' => 'Non-maintained bulkhead', 'brand' => 'Espire', 'image' => $el, 'cover' => true]),
                    kitPoaOpt(['id' => 'm', 'label' => 'Maintained bulkhead', 'brand' => 'Espire', 'image' => $el, 'cover' => true]),
                    kitPoaOpt(['id' => 'exit', 'label' => 'Exit sign', 'brand' => 'Eaton', 'image' => $el, 'cover' => true]),
                    kitPoaOpt(['id' => 'selftest', 'label' => 'Self-test', 'brand' => 'Eaton', 'image' => $el, 'cover' => true]),
                ],
            ],
        ],
    ];
}

function kitAovWizard(): array
{
    $aov = kitAsset('services/aov-air-handling.jpg');
    return [
        'slug' => 'aov',
        'title' => 'AOV / smoke ventilation kit builder',
        'short' => 'AOV',
        'kicker' => 'Ventlux / KAC',
        'blurb' => 'Built-in AOV kit: Ventlux controllers, vents and KAC orange call points from the live Shopify catalogue. Never Screwfix own-brand vents.',
        'service' => 'aov-air-handling',
        'hero_image' => $aov,
        'summary_blurb' => 'Known branded Ventlux / KAC SKUs open on Shopify. Cause-and-effect remains enquire / POA. Screwfix is a price reference only.',
        'steps' => [
            [
                'id' => 'controller',
                'title' => 'AOV controller',
                'short' => 'Panel',
                'options' => [
                    kitShopOpt(['id' => 'svm', 'label' => 'Ventlux SVM single-zone', 'brand' => 'Ventlux', 'image' => kitCdn('svm-panel.jpg?v=1788977786'), 'sku' => '204135', 'handle' => 'ventlux-svm-controller', 'variant_id' => '58749592731980']),
                    kitShopOpt(['id' => 'svl', 'label' => 'Ventlux SVL multi-zone', 'brand' => 'Ventlux', 'image' => kitCdn('22800220.jpg?v=1788977789'), 'sku' => '22800220', 'handle' => 'ventlux-svl-controller', 'variant_id' => '58749592994124']),
                ],
            ],
            [
                'id' => 'vent',
                'title' => 'Vent / actuator',
                'short' => 'Vent',
                'options' => [
                    kitShopOpt(['id' => 'hatch', 'label' => 'Ventlux AOV roof hatch VSK285', 'brand' => 'Ventlux', 'image' => kitCdn('AOV_Roof_Hatch_inter.jpg?v=1788977928'), 'sku' => 'VSK285', 'handle' => 'ventlux-vsk285-hatch', 'variant_id' => '58749597024588']),
                    kitShopOpt(['id' => 'rvs', 'label' => 'Ventlux RV/S6A roof-window kit', 'brand' => 'Ventlux', 'image' => kitCdn('rvs6a.jpg?v=1788977925'), 'sku' => 'RV/S6A/KUF/Kit', 'handle' => 'ventlux-rvs6a-kit', 'variant_id' => '58749596795212']),
                    kitShopOpt(['id' => 'hcv', 'label' => 'Ventlux HCV chain-drive', 'brand' => 'Ventlux', 'image' => kitCdn('hcv-chain.jpg?v=1788977801'), 'sku' => '31010100', 'handle' => 'ventlux-hcv-chain', 'variant_id' => '58749593551180']),
                ],
            ],
            [
                'id' => 'detect',
                'title' => 'Detection & MCP',
                'short' => 'Detect',
                'multi' => true,
                'options' => [
                    kitShopOpt(['id' => 'sd', 'label' => 'Ventlux AOV smoke detector', 'brand' => 'Ventlux', 'image' => kitCdn('A_SD_CON_DB.jpg?v=1788978073'), 'sku' => 'A/SD/CON/DB', 'handle' => 'ventlux-aov-smoke-detector', 'variant_id' => '58749604364620']),
                    kitShopOpt(['id' => 'vsk321', 'label' => 'Ventlux VSK321 AOV call point', 'brand' => 'Ventlux', 'image' => kitCdn('VSK321B.jpg?v=1788977933'), 'sku' => 'VSK321B', 'handle' => 'ventlux-vsk321', 'variant_id' => '58749597122892']),
                    kitShopOpt(['id' => 'kac', 'label' => 'KAC MCP1A-A-AOV orange', 'brand' => 'KAC', 'image' => kitCdn('kac-smoke-vent-orange-manual-call-point.jpg?v=1788979230'), 'sku' => 'MCP1A-A-AOV', 'handle' => 'kac-mcp-aov', 'variant_id' => '58749770236236']),
                    kitShopOpt(['id' => 'rain', 'label' => 'Ventlux wind & rain sensor', 'brand' => 'Ventlux', 'image' => kitCdn('121329.jpg?v=1788978065'), 'sku' => '121329', 'handle' => 'ventlux-wind-rain-121329', 'variant_id' => '58749603742028']),
                ],
            ],
        ],
    ];
}

function kitIntercomWizard(): array
{
    $int = kitAsset('services/intercoms.jpg');
    return [
        'slug' => 'intercom',
        'title' => 'Intercom kit builder',
        'short' => 'Intercom',
        'kicker' => 'Videx / Bell / Aiphone',
        'blurb' => 'Videx and Bell System kits from the live shop. Aiphone is enquire / POA until a shop SKU exists. Branded manufacturers only — never Screwfix own-brand door entry.',
        'service' => 'intercoms',
        'hero_image' => $int,
        'steps' => [
            [
                'id' => 'type',
                'title' => 'System type',
                'short' => 'Type',
                'options' => [
                    kitChoiceOpt(['id' => 'audio', 'label' => 'Audio', 'brand' => 'Videx', 'image' => kitCdn('videx-8k-audio.jpg?v=1788980487')]),
                    kitChoiceOpt(['id' => 'video', 'label' => 'Video', 'brand' => 'Videx', 'image' => kitCdn('videx-cvk8k.jpg?v=1788980494')]),
                    kitChoiceOpt(['id' => 'gsm', 'label' => 'GSM / 4G', 'brand' => 'Videx', 'image' => kitCdn('videx-gsm4k.jpg?v=1788980501')]),
                    kitChoiceOpt(['id' => 'ip', 'label' => 'IP', 'brand' => 'Videx', 'image' => kitCdn('videx-ipvk-1s.jpg?v=1788983726')]),
                ],
            ],
            [
                'id' => 'kit',
                'title' => 'Entrance kit',
                'short' => 'Kit',
                'options' => [
                    kitShopOpt(['id' => 'videx-audio', 'label' => 'Videx 8000 audio kit', 'brand' => 'Videx', 'image' => kitCdn('videx-8k-audio.jpg?v=1788980487'), 'sku' => '8K-1S', 'handle' => 'videx-8000-audio-kit', 'variant_id' => '58749970022732', 'show_if' => ['step' => 'type', 'values' => ['audio']]]),
                    kitShopOpt(['id' => 'bell-audio', 'label' => 'Bell System 900 audio kit', 'brand' => 'Bell System', 'image' => kitCdn('bell-de901.jpg?v=1788980919'), 'sku' => '901', 'handle' => 'bell-900-audio-kit', 'variant_id' => '58749999579468', 'show_if' => ['step' => 'type', 'values' => ['audio']]]),
                    kitShopOpt(['id' => 'videx-video', 'label' => 'Videx 8000 videokit', 'brand' => 'Videx', 'image' => kitCdn('videx-cvk8k.jpg?v=1788980494'), 'sku' => 'CVK8KS', 'handle' => 'videx-8000-videokit', 'variant_id' => '58749970678092', 'show_if' => ['step' => 'type', 'values' => ['video']]]),
                    kitShopOpt(['id' => 'bell-video', 'label' => 'Bell System Bellissimo video kit', 'brand' => 'Bell System', 'image' => kitCdn('bell-debsxa_f8235a18-8b39-4499-84a3-8c78f4c22c16.jpg?v=1788980976'), 'sku' => 'BS1', 'handle' => 'bell-bellissimo-kit', 'variant_id' => '58750003151180', 'show_if' => ['step' => 'type', 'values' => ['video']]]),
                    kitShopOpt(['id' => 'videx-gsm', 'label' => 'Videx GSM4K 4G', 'brand' => 'Videx', 'image' => kitCdn('videx-gsm4k.jpg?v=1788980501'), 'sku' => 'GSM4K-1S/4G', 'handle' => 'videx-gsm4k-sim', 'variant_id' => '58749970874700', 'show_if' => ['step' => 'type', 'values' => ['gsm']]]),
                    kitShopOpt(['id' => 'videx-ip', 'label' => 'Videx IPure IP videokit', 'brand' => 'Videx', 'image' => kitCdn('videx-ipvk-1s.jpg?v=1788983726'), 'sku' => 'IPVK-1', 'handle' => 'videx-ipure-ipvk', 'variant_id' => '58750382145868', 'show_if' => ['step' => 'type', 'values' => ['ip']]]),
                    kitPoaOpt(['id' => 'aiphone', 'label' => 'Aiphone (survey)', 'brand' => 'Aiphone', 'logo' => kitLogo('Aiphone'), 'image' => kitAsset('manufacturers/aiphone.jpg'), 'blurb' => 'No live Aiphone SKU on Shopify — enquire / POA.']),
                ],
            ],
            [
                'id' => 'parts',
                'title' => 'Handset / lock / camera',
                'short' => 'Parts',
                'multi' => true,
                'optional' => true,
                'options' => [
                    kitShopOpt(['id' => 'bell-801', 'label' => 'Bell 801 handset', 'brand' => 'Bell System', 'image' => kitCdn('bell-de801.jpg?v=1788980944'), 'sku' => '801', 'handle' => 'bell-801-handset', 'variant_id' => '58750001643852']),
                    kitShopOpt(['id' => 'bell-203', 'label' => 'Bell 203 lock release', 'brand' => 'Bell System', 'image' => kitCdn('bell-de203.jpg?v=1788980959'), 'sku' => '203', 'handle' => 'bell-203-lock', 'variant_id' => '58750002856268']),
                    kitShopOpt(['id' => 'videx-cam', 'label' => 'Videx 8830 camera module', 'brand' => 'Videx', 'image' => kitCdn('videx-camera-8830.jpg?v=1788980525'), 'sku' => '8830', 'handle' => 'videx-8000-camera', 'variant_id' => '58749971759436']),
                ],
            ],
        ],
    ];
}

function kitAccessWizard(): array
{
    $ac = kitAsset('services/access-control.jpg');
    return [
        'slug' => 'access-control',
        'title' => 'Access control kit builder',
        'short' => 'Access control',
        'kicker' => 'Paxton / CAME',
        'blurb' => 'Paxton door access is enquire / POA until a shop SKU exists. CAME selectors and locks are live Shopify SKUs. Branded Paxton / CAME / Salto only — never Screwfix own-brand access.',
        'service' => 'access-control',
        'hero_image' => $ac,
        'steps' => [
            [
                'id' => 'use',
                'title' => 'Use',
                'short' => 'Use',
                'options' => [
                    kitChoiceOpt(['id' => 'door', 'label' => 'Door access (Paxton)', 'brand' => 'Paxton', 'logo' => kitLogo('Paxton'), 'image' => kitAsset('manufacturers/paxton.jpg')]),
                    kitChoiceOpt(['id' => 'gate', 'label' => 'Gate / barrier access (CAME)', 'brand' => 'CAME', 'logo' => kitLogo('CAME'), 'image' => kitCdn('sel-digital.jpg?v=1789114188')]),
                ],
            ],
            [
                'id' => 'kit',
                'title' => 'Controller / hardware',
                'short' => 'Hardware',
                'options' => [
                    kitPoaOpt(['id' => 'paxton-net2', 'label' => 'Paxton Net2 (survey)', 'brand' => 'Paxton', 'logo' => kitLogo('Paxton'), 'image' => kitAsset('manufacturers/paxton.jpg'), 'blurb' => 'No live Paxton SKU on Shopify — enquire / POA.', 'show_if' => ['step' => 'use', 'values' => ['door']]]),
                    kitPoaOpt(['id' => 'salto', 'label' => 'Salto wireless lock (survey)', 'brand' => 'Salto', 'logo' => kitLogo('Salto'), 'image' => kitAsset('manufacturers/salto-access.jpg'), 'show_if' => ['step' => 'use', 'values' => ['door']]]),
                    kitShopOpt(['id' => 'came-sel', 'label' => 'CAME SEL key / digital selector', 'brand' => 'CAME', 'logo' => kitLogo('CAME'), 'image' => kitCdn('sel-digital.jpg?v=1789114188'), 'sku' => 'SET-I', 'handle' => 'came-sel', 'variant_id' => '58763474633036', 'show_if' => ['step' => 'use', 'values' => ['gate']]]),
                    kitShopOpt(['id' => 'came-lock', 'label' => 'CAME electric lock', 'brand' => 'CAME', 'logo' => kitLogo('CAME'), 'image' => kitCdn('kiaro.jpg?v=1789114195'), 'sku' => '001LOCK81', 'handle' => 'came-locks', 'variant_id' => '58763475386700', 'show_if' => ['step' => 'use', 'values' => ['gate']]]),
                ],
            ],
            [
                'id' => 'cred',
                'title' => 'Credentials',
                'short' => 'Access',
                'multi' => true,
                'optional' => true,
                'options' => [
                    kitPoaOpt(['id' => 'fob', 'label' => 'Fobs / cards', 'brand' => 'Paxton', 'logo' => kitLogo('Paxton'), 'image' => kitAsset('manufacturers/paxton.jpg')]),
                    kitPoaOpt(['id' => 'keypad', 'label' => 'Keypad', 'brand' => 'CAME', 'logo' => kitLogo('CAME'), 'image' => kitCdn('sel-digital.jpg?v=1789114188')]),
                    kitPoaOpt(['id' => 'intercom', 'label' => 'Link to intercom', 'brand' => 'Videx', 'image' => kitCdn('videx-8k-audio.jpg?v=1788980487')]),
                ],
            ],
        ],
    ];
}

function kitGatesWizard(): array
{
    $hero = kitCdn('icomply-came-hero.jpg');
    $logo = kitLogo('CAME');
    return [
        'slug' => 'gates',
        'title' => 'Gates kit builder',
        'short' => 'Gates',
        'kicker' => 'CAME sliding / swing / underground',
        'blurb' => 'Official CAME stills from the Shopify CDN. Pick type, leaf size, operator family, safety and controls. CAME only — never Screwfix own-brand gate kit.',
        'service' => 'access-control',
        'hero_image' => $hero,
        'summary_blurb' => 'Branded CAME SKUs deep-link to Shopify. Leaf weight and site survey remain enquire / POA. Screwfix is a price reference only. No invented £.',
        'steps' => [
            [
                'id' => 'type',
                'title' => 'Gate type',
                'short' => 'Type',
                'options' => [
                    kitChoiceOpt(['id' => 'sliding', 'label' => 'Sliding', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-bxv.jpg?v=1788884792'), 'blurb' => 'BXV / BXL / BKV families — pick the operator next.']),
                    kitChoiceOpt(['id' => 'swing', 'label' => 'Swing', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-ati.jpg?v=1788884798'), 'blurb' => 'ATI / AXO / FERNI / KRONO / AXI — pick the operator next.']),
                    kitChoiceOpt(['id' => 'underground', 'label' => 'Underground', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-frog.jpg?v=1788905635'), 'blurb' => 'FROG / FROG-X / STYLO — pick the operator next.']),
                ],
            ],
            [
                'id' => 'size',
                'title' => 'Leaf / width / weight',
                'short' => 'Size',
                'blurb' => 'Guides the operator family. Final selection is surveyed.',
                'options' => [
                    kitChoiceOpt(['id' => 'light', 'label' => 'Light domestic (to ~400 kg / 3 m)', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitChoiceOpt(['id' => 'mid', 'label' => 'Mid (400–1000 kg)', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitChoiceOpt(['id' => 'industrial', 'label' => 'Industrial (1000 kg+)', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitChoiceOpt(['id' => 'swing-3', 'label' => 'Swing leaves to 3 m', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitChoiceOpt(['id' => 'swing-5', 'label' => 'Swing leaves 3–5 m+', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitChoiceOpt(['id' => 'ug-std', 'label' => 'Underground standard leaf', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['underground']]]),
                    kitChoiceOpt(['id' => 'ug-heavy', 'label' => 'Underground heavy / plus', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero, 'cover' => true, 'show_if' => ['step' => 'type', 'values' => ['underground']]]),
                ],
            ],
            [
                'id' => 'operator',
                'title' => 'Operator family',
                'short' => 'Operator',
                'options' => [
                    kitShopOpt(['id' => 'bxv', 'label' => 'CAME BXV sliding', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-bxv.jpg?v=1788884792'), 'sku' => '801MS-0150', 'handle' => 'came-bxv-sliding', 'variant_id' => '58737140433228', 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitShopOpt(['id' => 'bxv-kit', 'label' => 'CAME BXV sliding kit', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('bxv-kit.jpg?v=1789114124'), 'sku' => '8K01MS-0600', 'handle' => 'came-bxv-kits', 'variant_id' => '58763461755212', 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitShopOpt(['id' => 'bxl', 'label' => 'CAME BXL sliding', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-bxl.jpg?v=1788905632'), 'sku' => '801MS-0140', 'handle' => 'came-bxl-sliding', 'variant_id' => '58763461984588', 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitShopOpt(['id' => 'bkv', 'label' => 'CAME BKV industrial sliding', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-bkv.jpg?v=1788905627'), 'sku' => 'BKV15AGS', 'handle' => 'came-bkv-sliding', 'variant_id' => '58741816230220', 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                    kitShopOpt(['id' => 'ati', 'label' => 'CAME ATI swing', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-ati.jpg?v=1788884798'), 'sku' => '801MP-0190', 'handle' => 'came-ati-swing', 'variant_id' => '58737142038860', 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitShopOpt(['id' => 'axo', 'label' => 'CAME AXO swing', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-axo.jpg?v=1788905663'), 'sku' => '001AX302304', 'handle' => 'came-axo', 'variant_id' => '58741817573708', 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitShopOpt(['id' => 'ferni', 'label' => 'CAME FERNI swing', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-ferni.jpg?v=1788905667'), 'sku' => '001FE40230', 'handle' => 'came-ferni', 'variant_id' => '58741818786124', 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitShopOpt(['id' => 'krono', 'label' => 'CAME KRONO swing', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-krono.jpg?v=1788905675'), 'sku' => '001KR310S', 'handle' => 'came-krono', 'variant_id' => '58741818884428', 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitShopOpt(['id' => 'axi', 'label' => 'CAME AXI swing', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-axi.jpg?v=1788905684'), 'sku' => '801MP-0030', 'handle' => 'came-axi', 'variant_id' => '58741819179340', 'show_if' => ['step' => 'type', 'values' => ['swing']]]),
                    kitShopOpt(['id' => 'frog', 'label' => 'CAME FROG underground', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-frog.jpg?v=1788905635'), 'sku' => '001FROG-A', 'handle' => 'came-frog', 'variant_id' => '58741816820044', 'show_if' => ['step' => 'type', 'values' => ['underground']]]),
                    kitShopOpt(['id' => 'frog-x', 'label' => 'CAME FROG-X underground', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-frog-x.jpg?v=1788905639'), 'sku' => '801MI-0030', 'handle' => 'came-frog-x', 'variant_id' => '58741816983884', 'show_if' => ['step' => 'type', 'values' => ['underground']]]),
                    kitShopOpt(['id' => 'stylo', 'label' => 'CAME STYLO underground', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-stylo.jpg?v=1788905678'), 'sku' => '001STYLO-ME', 'handle' => 'came-stylo', 'variant_id' => '58741819048268', 'show_if' => ['step' => 'type', 'values' => ['underground']]]),
                ],
            ],
            [
                'id' => 'safety',
                'title' => 'Safety',
                'short' => 'Safety',
                'multi' => true,
                'blurb' => 'CAME DIR photocells and DF edges.',
                'options' => [
                    kitShopOpt(['id' => 'dir', 'label' => 'CAME DIR photocells', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-dir.jpg?v=1788884805'), 'sku' => '001DIR10', 'handle' => 'came-dir-photocells', 'variant_id' => '58737144856908']),
                    kitShopOpt(['id' => 'df', 'label' => 'CAME DF safety edges', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('dlx_44d80142-7c58-4943-9a7f-f33cad66041d.jpg?v=1789114198'), 'sku' => 'DF', 'handle' => 'came-df-edges', 'variant_id' => '58763476074828']),
                    kitShopOpt(['id' => 'cgz', 'label' => 'CAME CGZ rack (sliding)', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-cgz-mod4_69429cf2-0bdb-4523-a5c6-8b28c153b449.jpg?v=1789504134'), 'sku' => '009CGZ', 'handle' => 'came-cgz-rack', 'variant_id' => '58763484987724', 'show_if' => ['step' => 'type', 'values' => ['sliding']]]),
                ],
            ],
            [
                'id' => 'controls',
                'title' => 'Controls / access',
                'short' => 'Controls',
                'multi' => true,
                'optional' => true,
                'options' => [
                    kitShopOpt(['id' => 'top', 'label' => 'CAME TOP remotes', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-top.jpg?v=1788905695'), 'sku' => '806TS-0122', 'handle' => 'came-top-remotes', 'variant_id' => '58741819572556']),
                    kitShopOpt(['id' => 'zlx', 'label' => 'CAME ZLX230 panel', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-zlx230.jpg?v=1788905700'), 'sku' => 'ZLX230-P', 'handle' => 'came-zlx230', 'variant_id' => '58741819703628']),
                    kitShopOpt(['id' => 'sel', 'label' => 'CAME SEL keypad / key', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('sel-digital.jpg?v=1789114188'), 'sku' => 'SET-I', 'handle' => 'came-sel', 'variant_id' => '58763474633036']),
                    kitShopOpt(['id' => 'x1', 'label' => 'CAME BPT X1 intercom kit', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-x1-kit-8K40CF-021_b7f5a8d2-5020-4a14-b336-77720b03e601.jpg?v=1789504127'), 'sku' => '8K40CF-021', 'handle' => 'came-x1-kit', 'variant_id' => '58763477844300']),
                ],
            ],
        ],
    ];
}

function kitBarriersWizard(): array
{
    $logo = kitLogo('CAME');
    $hero = kitCdn('came-gard-gt4.jpg?v=1788884803');
    return [
        'slug' => 'barriers',
        'title' => 'Barriers kit builder',
        'short' => 'Barriers',
        'kicker' => 'CAME partner',
        'blurb' => 'CAME is our barrier partner. GARD boom barriers with DIR photocells, TOP remotes and ZLX controls. CAME only — never Screwfix own-brand barriers.',
        'service' => 'access-control',
        'hero_image' => $hero,
        'summary_blurb' => 'Boom length is confirmed on site. Live branded CAME SKUs open on Shopify. Screwfix is a price reference only.',
        'steps' => [
            [
                'id' => 'type',
                'title' => 'Barrier type',
                'short' => 'Type',
                'options' => [
                    kitShopOpt(['id' => 'gt4', 'label' => 'CAME GARD GT4 (to ~4–6 m)', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-gard-gt4.jpg?v=1788884803'), 'sku' => 'GARD-GT4-4M', 'handle' => 'came-gard-gt4', 'variant_id' => '58763466899788']),
                    kitShopOpt(['id' => 'gt8', 'label' => 'CAME GARD GT8 (longer boom)', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('gard-gt8.jpg?v=1789114153'), 'sku' => '803BB-0180', 'handle' => 'came-gard-gt8', 'variant_id' => '58763466146124']),
                    kitShopOpt(['id' => 'gard4', 'label' => 'CAME GARD 4', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('gard-4.jpg?v=1789114157'), 'sku' => '001G4040Z', 'handle' => 'came-gard-4', 'variant_id' => '58763467325772']),
                ],
            ],
            [
                'id' => 'boom',
                'title' => 'Boom length',
                'short' => 'Boom',
                'options' => [
                    kitChoiceOpt(['id' => 'b4', 'label' => 'Up to 4 m', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero]),
                    kitChoiceOpt(['id' => 'b6', 'label' => 'Up to 6 m', 'brand' => 'CAME', 'logo' => $logo, 'image' => $hero]),
                    kitChoiceOpt(['id' => 'b8', 'label' => '6–8 m (survey)', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('gard-gt8.jpg?v=1789114153')]),
                ],
            ],
            [
                'id' => 'safety',
                'title' => 'Safety',
                'short' => 'Safety',
                'multi' => true,
                'options' => [
                    kitShopOpt(['id' => 'dir', 'label' => 'CAME DIR photocells', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-dir.jpg?v=1788884805'), 'sku' => '001DIR10', 'handle' => 'came-dir-photocells', 'variant_id' => '58737144856908']),
                    kitShopOpt(['id' => 'df', 'label' => 'CAME DF safety edges', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('dlx_44d80142-7c58-4943-9a7f-f33cad66041d.jpg?v=1789114198'), 'sku' => 'DF', 'handle' => 'came-df-edges', 'variant_id' => '58763476074828']),
                ],
            ],
            [
                'id' => 'controls',
                'title' => 'Controls / access',
                'short' => 'Controls',
                'multi' => true,
                'optional' => true,
                'options' => [
                    kitShopOpt(['id' => 'top', 'label' => 'CAME TOP remotes', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-top.jpg?v=1788905695'), 'sku' => '806TS-0122', 'handle' => 'came-top-remotes', 'variant_id' => '58741819572556']),
                    kitShopOpt(['id' => 'sel', 'label' => 'CAME SEL keypad / key', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('sel-digital.jpg?v=1789114188'), 'sku' => 'SET-I', 'handle' => 'came-sel', 'variant_id' => '58763474633036']),
                    kitShopOpt(['id' => 'zlx', 'label' => 'CAME ZLX230 panel', 'brand' => 'CAME', 'logo' => $logo, 'image' => kitCdn('came-zlx230.jpg?v=1788905700'), 'sku' => 'ZLX230-P', 'handle' => 'came-zlx230', 'variant_id' => '58741819703628']),
                ],
            ],
        ],
    ];
}

/** @return array<string,array<string,mixed>> */
function kitWizardCatalog(): array
{
    $list = [
        kitBarriersWizard(),
        kitAovWizard(),
        kitGatesWizard(),
        kitRewireWizard(),
        kitHeatingWizard(),
        kitFireWizard(),
        kitElWizard(),
        kitIntercomWizard(),
        kitAccessWizard(),
    ];
    $out = [];
    foreach ($list as $w) {
        $w = kitWithPolicy($w);
        $out[$w['slug']] = $w;
    }
    kitAssertCatalogBrands($out);
    return $out;
}
}
