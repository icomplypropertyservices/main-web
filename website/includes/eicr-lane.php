<?php
/**
 * EICR / electrical testing job lane.
 *
 * One published list price: £249 for a typical North West 6-bed HMO
 * (code ELEC-EICR-6BED-NW). All-in. Remedials are extra.
 * 1-bed, other domestic sizes, and commercial EICRs stay price on application.
 * No invented fees, scheme badges, or “satisfactory” outcome without the visit.
 */
declare(strict_types=1);

function eicrLaneSlugs(): array
{
    return [
        'eicr',
        'eicr-certificate',
        'eicr-cost',
        'eicr-near-me',
        'eicr-price',
        'eicr-report',
        'eicr-testing',
        'commercial-eicr',
        'domestic-eicr',
        'landlord-eicr',
        'fixed-wire-testing',
        'electrical-certificate-for-lettings',
        'electrical-certification',
        'electrical-compliance-certificate',
        'electrical-inspection',
        'electrical-inspection-and-testing',
        'electrical-installation-condition-report',
        'electrical-safety-certificate',
        'electrical-safety-inspection',
        'hmo-electrical-certificate',
        'landlord-electrical-certificate',
        'landlord-electrical-certificate-north-west',
        'landlord-electrical-safety-check',
        'office-electrical-testing',
        'periodic-electrical-inspection',
        'periodic-inspection',
        'periodic-inspection-report',
        'bs-7671-inspection',
    ];
}

function eicrLaneIsSlug(string $slug): bool
{
    return in_array(keywordSlug($slug), eicrLaneSlugs(), true);
}

/** @return array{display:string,amount:string,code:string,name:string,scope:string} */
function eicrLaneListPrice(): array
{
    return [
        'display' => '£249',
        'amount' => '249.00',
        'code' => 'ELEC-EICR-6BED-NW',
        'name' => 'EICR — typical 6-bed HMO, North West',
        'scope' => 'Typical 6-bed HMO in the North West',
    ];
}

function eicrLanePriceSentence(): string
{
    $price = eicrLaneListPrice();
    return 'The only published EICR list price is ' . $price['display']
        . ' for a ' . strtolower($price['scope'])
        . ' (code ' . $price['code'] . '), per property, all-in. '
        . 'iComply is not VAT registered, so VAT is not added. '
        . 'Remedials and parts are quoted after the inspection and are not included. '
        . 'A 1-bed, any other domestic size, and every commercial EICR are price on application.';
}

function eicrLaneStripDenials(string $text): string
{
    $needles = [
        'Pricing for this search is POA after we confirm scope — Icomply does not invent pound prices.',
        'Cost and price enquiries are answered with a written POA quote, never a guessed £ figure.',
        'EICR price for a flat, house, HMO or commercial unit is POA. Circuit count, access and number of boards decide the inspection time — we do not invent a pound price here.',
        'we do not invent a pound price here.',
        'No invented pound prices.',
        'No. Property size and access vary too much for an honest invented figure.',
        'Do you publish a from-£ EICR list?',
    ];
    $text = str_replace($needles, '', $text);
    $text = preg_replace("/[ \t]{2,}/", ' ', $text) ?? $text;
    return trim($text);
}

/**
 * @return array<string, array<string, mixed>>
 */
function eicrLaneKeywordReplacements(): array
{
    $sentence = eicrLanePriceSentence();
    $sharedFaq = [
        [
            'What does the £249 EICR list price include?',
            'Inspection and testing of the fixed installation, and the Electrical Installation Condition Report, for a typical 6-bed HMO in the North West. It does not include remedial works, parts, or a different size of property.',
        ],
        [
            'Is £249 the price of every EICR?',
            'No. It is the list for that 6-bed HMO scope only. 1-bed dwellings and commercial installations are price on application. We confirm the figure before you are booked.',
        ],
        [
            'What if the report is unsatisfactory?',
            'C1, C2 and FI observations need remedial work, quoted after the inspection. The original report stays on file. A satisfactory outcome is not sold in advance of the visit.',
        ],
    ];
    return [
        'eicr-cost' => [
            'intro' => 'EICR cost on this site is one published list price, not a menu of guessed fees. ' . $sentence,
            'body' => 'A studio, a 3-bed house and a shop are not the same visit as a typical 6-bed HMO. Those stay price on application once bedrooms, consumer units and access are known. The £249 list is per property and covers the inspection plus the report. Coded remedials are a second quote. Stockport engineers cover Greater Manchester and the wider North West. Travel outside the North West is agreed before booking. The report describes the installation on the day of the test.',
            'meta_desc' => 'EICR list price £249 for a typical North West 6-bed HMO. Other sizes and commercial EICRs are POA. Remedials extra. Icomply, Stockport.',
            'focus_points' => [
                '£249 list for a typical North West 6-bed HMO only',
                '1-bed and commercial EICRs are price on application',
                'Remedials quoted from the coded report, not bundled in',
                'All-in list — VAT is not added',
            ],
            'faq' => $sharedFaq,
        ],
        'eicr-price' => [
            'intro' => 'EICR price, for the one scope we publish, is £249. ' . $sentence,
            'body' => 'Landlords searching “EICR price” usually want a number before they book. The published number is the 6-bed HMO list. Anything else — fewer bedrooms, a different layout, or a commercial board — is confirmed in writing after we know the installation. Reports are coded to BS 7671. We do not publish a cheaper “from” price for a smaller flat, and we do not relabel the HMO list as a commercial fee.',
            'meta_desc' => 'EICR price £249 for a typical 6-bed HMO in the North West. 1-bed and commercial EICR prices are POA. Icomply Stockport.',
            'focus_points' => [
                'Published list: £249, typical 6-bed HMO, North West',
                'No invented from-price for flats or shops',
                'Remedials priced after C1, C2, C3 and FI coding',
                'Confirmed before booking',
            ],
            'faq' => $sharedFaq,
        ],
    ];
}

/**
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
/**
 * Keyword pages that currently read as if iComply holds a scheme badge.
 * They stay in the catalogue. The copy no longer claims membership.
 *
 * @return array<string, array<string, mixed>>
 */
function eicrLaneAccreditationReplacements(): array
{
    return [
        'niceic-certified' => [
            'name' => 'NICEIC explained',
            'intro' => 'NICEIC is a UK competent-person scheme. This page explains the term. iComply does not publish a NICEIC membership number and does not claim to be NICEIC certified.',
            'body' => 'An EICR is assessed against BS 7671. A scheme logo is a separate question. If a client, insurer or Building Control body requires a named scheme, say so when you enquire and we will tell you whether we can take the job. We will not invent a membership to win the work. The published EICR list price is £249 for a typical North West 6-bed HMO. Other sizes are price on application.',
            'meta_desc' => 'What NICEIC means, and what iComply does not claim. EICR testing is to BS 7671. List price £249 for a typical North West 6-bed HMO.',
            'focus_points' => [
                'NICEIC is a scheme name, not a result on your EICR',
                'No membership number is published on this site',
                'Inspection and testing still follow BS 7671',
                '£249 list price is only the typical 6-bed HMO EICR',
            ],
            'faq' => [
                ['Are you NICEIC certified?', 'We do not claim NICEIC certification on this website, and we do not publish a membership number. Ask if your file specifically requires a named scheme.'],
                ['Does an EICR need a scheme logo?', 'The report needs a competent inspection and test to BS 7671. A scheme logo is not a substitute for the coded observations.'],
                ['What is the EICR list price?', eicrLanePriceSentence()],
            ],
        ],
        'part-p-certified' => [
            'name' => 'Part P explained',
            'intro' => 'Part P is the Building Regulations requirement for electrical safety in dwellings in England. This page explains that. iComply does not claim a Part P scheme registration here.',
            'body' => 'Some new circuits, consumer-unit changes and work in special locations are notifiable. Many like-for-like repairs are not. We say which is which when the job is scoped. An EICR is a condition report on an existing installation. It is not a Part P notification and it is not an Electrical Installation Certificate for new work. The only published EICR list price is £249 for a typical North West 6-bed HMO.',
            'meta_desc' => 'Part P explained without a scheme-badge claim. EICR list price £249 for a typical North West 6-bed HMO. Other electrical work is quoted after scope.',
            'focus_points' => [
                'Part P is a Building Regulations duty, not a logo we print',
                'Notifiable work is confirmed per job',
                'An EICR is not a Part P certificate',
                'New-work certification is quoted separately',
            ],
            'faq' => [
                ['Are you Part P certified?', 'We do not claim a Part P scheme registration on this page. Where a job is notifiable, we tell you how notification will be handled before work starts.'],
                ['Is an EICR a Part P certificate?', 'No. An EICR reports the condition of the existing installation. New or altered circuits use a different certificate.'],
                ['What does the EICR list price cover?', eicrLanePriceSentence()],
            ],
        ],
    ];
}

function eicrLaneOverlayKeyword(string $slug, array $row): array
{
    $slug = keywordSlug($slug);
    $accreditation = eicrLaneAccreditationReplacements();
    if (isset($accreditation[$slug])) {
        return array_merge($row, $accreditation[$slug]);
    }
    if (!eicrLaneIsSlug($slug)) {
        return $row;
    }
    $replacements = eicrLaneKeywordReplacements();
    if (isset($replacements[$slug])) {
        $row = array_merge($row, $replacements[$slug]);
    }
    foreach (['intro', 'body', 'meta_desc'] as $field) {
        if (!empty($row[$field]) && is_string($row[$field])) {
            $row[$field] = eicrLaneStripDenials($row[$field]);
        }
    }
    $sentence = eicrLanePriceSentence();
    $intro = (string)($row['intro'] ?? '');
    if (!str_contains($intro, '£249')) {
        $row['intro'] = rtrim($intro, " \t.") . '. ' . $sentence;
    }
    $faqs = is_array($row['faq'] ?? null) ? $row['faq'] : [];
    $hasPriceFaq = false;
    foreach ($faqs as $i => $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $faqs[$i][0] = eicrLaneStripDenials((string)$faq[0]);
        $faqs[$i][1] = eicrLaneStripDenials((string)$faq[1]);
        $blob = $faqs[$i][0] . ' ' . $faqs[$i][1];
        if (str_contains($blob, '£249') || stripos($blob, 'list price') !== false) {
            $hasPriceFaq = true;
        }
    }
    if (!$hasPriceFaq) {
        $faqs[] = [
            'What is the EICR list price?',
            $sentence . ' We confirm it still applies to your property before you are booked.',
        ];
    }
    $row['faq'] = $faqs;
    return $row;
}

/** @return list<array{0:string,1:string}> */
function eicrLaneLinkTargets(): array
{
    return [
        ['/pages/services/electrical.php', 'Electrical service hub'],
        ['/pages/keywords/eicr.php', 'EICR'],
        ['/pages/keywords/eicr-testing.php', 'EICR testing'],
        ['/pages/keywords/landlord-eicr.php', 'Landlord EICR'],
        ['/pages/keywords/domestic-eicr.php', 'Domestic EICR'],
        ['/pages/keywords/commercial-eicr.php', 'Commercial EICR'],
        ['/pages/keywords/eicr-report.php', 'EICR report'],
        ['/pages/keywords/eicr-certificate.php', 'EICR certificate'],
        ['/pages/keywords/eicr-cost.php', 'EICR cost'],
        ['/pages/keywords/eicr-price.php', 'EICR price'],
        ['/pages/keywords/fixed-wire-testing.php', 'Fixed wire testing'],
        ['/pages/keywords/electrical-installation-condition-report.php', 'Condition report'],
        ['/pages/keywords/hmo-electrical-certificate.php', 'HMO electrical certificate'],
        ['/pages/resources/eicr-guide.php', 'EICR guide'],
        ['/pages/electrical-safety-landlords.php', 'Landlord electrical safety'],
        ['/pages/pricing.php', 'Pricing guide'],
        ['/contact.php', 'Request a quote'],
    ];
}

function eicrLaneLinksHtml(): string
{
    $html = '<nav class="mt-6 flex flex-wrap gap-2" aria-label="EICR testing pages">';
    foreach (eicrLaneLinkTargets() as [$path, $label]) {
        $html .= '<a href="' . htmlspecialchars(url($path), ENT_QUOTES, 'UTF-8') . '" '
            . 'class="px-3 py-1.5 bg-white border border-zinc-300 rounded-full text-sm font-semibold text-[#0B1F3A] hover:border-[#ff6b00] hover:text-[#ff6b00]">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

function eicrLaneOfferSchema(): array
{
    $price = eicrLaneListPrice();
    return [
        '@type' => 'Offer',
        'name' => $price['name'],
        'sku' => $price['code'],
        'price' => $price['amount'],
        'priceCurrency' => 'GBP',
        'description' => 'List price for inspection, testing and the Electrical Installation Condition Report on a typical 6-bed HMO in the North West. All-in; VAT is not added. Remedials excluded. 1-bed and commercial EICRs are price on application.',
        'url' => url('/pages/services/electrical.php') . '#eicr-lane',
    ];
}

/** @return array{hero_accent:string,intro:list<string>,pillars:list<array{title:string,text:string}>} */
function eicrLaneServiceCopy(): array
{
    return [
        'hero_accent' => 'EICR testing. £249 for a typical 6-bed HMO.',
        'intro' => [
            'The electrical testing job is an Electrical Installation Condition Report: a visual inspection and instrument tests of the fixed wiring to BS 7671, with observations coded C1, C2, C3 or FI.',
            eicrLanePriceSentence(),
            'PAT testing, rewires, consumer-unit changes and EV chargers are different jobs and are quoted after their own scope. We do not display a competent-person scheme badge on this lane. England private-rented reports are commonly renewed at least every five years; other nations and commercial sites follow their own rules. The page is not legal advice.',
        ],
        'pillars' => [
            ['title' => 'Inspection and testing', 'text' => 'Dead and live tests on the circuits in the agreed scope. If a circuit cannot be isolated or reached, that limitation is written on the report rather than filled in.'],
            ['title' => 'The condition report', 'text' => 'You receive the EICR for the installation as found that day. It is not a guarantee of later years, and a satisfactory result is not promised before the visit.'],
            ['title' => 'Remedials, separately', 'text' => 'C1, C2 and FI items are quoted after the report. They are outside the £249 list. C3 items are improvement recommendations.'],
        ],
        'band' => 'EICR list price £249 for a typical North West 6-bed HMO. Other sizes, commercial sites and remedials are confirmed before booking.',
        'cta_line' => 'Postcode, bedrooms and consumer units. We confirm whether the £249 list applies before you are booked.',
    ];
}

function eicrLanePanelHtml(string $context = 'page'): string
{
    $price = eicrLaneListPrice();
    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };
    $phone = defined('PHONE') ? (string)PHONE : '';
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', $phone);
    $wa = defined('WHATSAPP') ? (string)WHATSAPP : '';
    $waText = rawurlencode('EICR quote. The published list is £249 for a typical North West 6-bed HMO. My property is: ');
    $quoteHref = '#quote';

    $html = '<section id="eicr-lane" data-eicr-price="249" data-eicr-code="' . $h($price['code']) . '" '
        . 'class="mt-8 rounded-3xl border-2 border-[#0B1F3A] bg-white p-6 md:p-8 shadow-sm" aria-labelledby="eicr-lane-heading">';
    $html .= '<p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">EICR testing lane</p>';
    $html .= '<h2 id="eicr-lane-heading" class="mt-2 text-2xl md:text-3xl font-semibold tracking-tight text-[#0B1F3A]">'
        . $h($price['name']) . '</h2>';
    $html .= '<p class="mt-4 text-4xl font-semibold text-[#0B1F3A]">' . $h($price['display'])
        . ' <span class="text-base font-medium text-zinc-600">list price · per property</span></p>';
    $html .= '<p class="mt-3 text-zinc-800 leading-relaxed">' . $h(eicrLanePriceSentence()) . '</p>';
    $html .= '<ul class="mt-4 space-y-2 text-sm text-zinc-800">';
    $points = [
        'Scope: ' . $price['scope'] . '. Code ' . $price['code'] . '.',
        'Includes the inspection, the tests and the EICR for that property.',
        'Does not include remedials, parts, or a retest after repairs.',
        '1-bed / small dwelling: price on application.',
        'Commercial EICR: price on application.',
        'Outside the North West, travel is agreed before booking.',
        'Power is isolated circuit by circuit while tests are done. Access to the consumer unit and rooms is required.',
    ];
    foreach ($points as $point) {
        $html .= '<li class="flex gap-2"><span class="text-[#ff6b00] font-bold" aria-hidden="true">●</span><span>'
            . $h($point) . '</span></li>';
    }
    $html .= '</ul>';
    $html .= '<div class="mt-6 flex flex-wrap gap-3">';
    $html .= '<a href="' . $h($quoteHref) . '" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request an EICR quote</a>';
    if ($phone !== '') {
        $html .= '<a href="' . $h($phoneHref) . '" class="px-6 py-3 rounded-2xl bg-[#0B1F3A] font-semibold text-white">'
            . $h($phone) . '</a>';
    }
    if ($wa !== '') {
        $html .= '<a href="https://wa.me/' . $h($wa) . '?text=' . $waText . '" target="_blank" rel="noopener" '
            . 'class="px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold text-white">WhatsApp</a>';
    }
    $html .= '</div>';
    $html .= '<p class="mt-4 text-sm text-zinc-600">Context: ' . $h($context)
        . '. The list price is confirmed against the property before you are booked. '
        . 'An EICR is not PAT testing and not an Electrical Installation Certificate for new work.</p>';
    $html .= eicrLaneLinksHtml();
    $html .= '</section>';
    return $html;
}
