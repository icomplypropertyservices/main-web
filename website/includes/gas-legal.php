<?php
/**
 * Landlord gas safety certificates (CP12) are carried out by Gas Safe
 * registered engineers. iComply itself is not Gas Safe registered.
 * Do not say iComply does not carry out gas work, and do not say iComply
 * does not issue CP12.
 */
declare(strict_types=1);

function icomplyGasLegalPhrase(): string
{
    return 'carried out by Gas Safe registered engineers';
}

function icomplyGasLegalSentence(): string
{
    return 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. '
        . 'iComply is not Gas Safe registered.';
}

function icomplyTextMentionsGas(string $text): bool
{
    return (bool) preg_match('/\b(?:gas|CP12|CP44)\b/i', $text);
}

function icomplyCopyIsGasTopic(string $slug, string $name = ''): bool
{
    if ($slug === 'gas-systems') {
        return true;
    }
    $blob = str_replace('-', ' ', strtolower($slug . ' ' . $name));
    if (preg_match('/\b(?:inert gas|gas suppression|clean agent|gaseous)\b/', $blob)) {
        return false;
    }
    return (bool) preg_match('/\b(?:cp12|cp44|gas safety|gas certificate|landlord gas|boiler|gas engineer|gas cooker|gas meter|gas fire|commercial gas|gas pipe|gas leak|gas appliance)\b/', $blob);
}

function icomplyKeywordRecordIsGas(array $meta, string $slug): bool
{
    if (($meta['service'] ?? '') === 'gas-systems') {
        return true;
    }
    return icomplyCopyIsGasTopic($slug, (string)($meta['name'] ?? ''));
}

function icomplyGasServiceBlurb(bool $short = false): string
{
    if ($short) {
        return 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.';
    }
    return 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. '
        . 'iComply is not Gas Safe registered. '
        . 'This page explains the record landlords ask for. Electrical, fire, water hygiene and asbestos work on the same property is quoted separately. Price on application.';
}

function icomplyGasServiceStandards(): string
{
    return 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.';
}

/**
 * Body copy for the gas service hub. iComply is not Gas Safe registered.
 *
 * @return array<string,mixed>
 */
function icomplyGasServicePageCopy(): array
{
    return [
        'hero_accent' => 'Carried out by Gas Safe registered engineers.',
        'pillars' => [
            ['title' => 'The landlord record', 'text' => 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. The record lists the appliances and flues that were checked.'],
            ['title' => 'Not a company registration', 'text' => 'iComply is not Gas Safe registered. This page does not show a registration number or a Gas Safe badge.'],
            ['title' => 'POA only', 'text' => 'Postcode and appliance count first. The quote is price on application. There is no per-appliance price list on this page.'],
        ],
        'intro' => [
            'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.',
            'Landlords and agents in Greater Manchester still ask what the record is, and what else can be booked on the same address. Electrical, fire, water hygiene and asbestos work can be quoted separately. Each of those stays price on application.',
            'If an appliance is unsafe, that stays on the record. iComply will not turn a fail into a pass, and will not put a company Gas Safe logo on the paperwork.',
        ],
        'sections' => [
            [
                'h2' => 'What landlords are asking for',
                'p' => [
                    'People still say CP12. The document that matters is a current landlord gas safety record listing the appliances and flues that were checked. CP44 is a name you may hear for some non-domestic records. The record has to match the site.',
                    'iComply is based at 17 Woodlands Park Road, Offerton, Stockport, SK2 5DE. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.',
                ],
            ],
        ],
        'cta_line' => 'Postcode and appliance count. No catalogue gas fee. iComply is not Gas Safe registered.',
        'quote_placeholder' => 'Postcode, appliances, occupied or void. Gas work is carried out by Gas Safe registered engineers.',
    ];
}

function icomplyGasMetaDesc(string $name, string $area = ''): string
{
    $where = $area !== '' ? ' in ' . $area : ' across the North West';
    $desc = 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. '
        . $name . $where . '. iComply is not Gas Safe registered.';
    if (strlen($desc) > 160) {
        $desc = 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.';
    }
    return $desc;
}

function icomplyGasKeywordIntro(string $name, string $area = ''): string
{
    $where = $area !== '' ? ' in ' . $area : ' across the North West';
    return $name . $where . ' is listed for landlords and agents who need landlord gas safety certificates (CP12). '
        . 'They are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.';
}

function icomplyGasKeywordBody(string $name, string $area = ''): string
{
    $phone = defined('PHONE') ? PHONE : '';
    $legal = icomplyGasLegalSentence();
    if ($area === '' || !function_exists('area_profile')) {
        return $legal . ' This ' . $name . ' page is a guide from Stockport SK2. Electrical, fire, water hygiene and asbestos work on the same property is quoted separately, price on application. Call ' . $phone . '.';
    }
    $p = area_profile($area);
    $nearby = function_exists('icomplyNearbyTowns') ? icomplyNearbyTowns($area, 4) : [];
    $nearbyText = $nearby ? implode(', ', $nearby) : 'other listed North West towns';
    $authority = trim((string)($p['authority'] ?? ''));
    $authorityNote = $authority !== '' ? $authority : 'none stored on this town profile';
    return $legal
        . ' ' . $name . ' in ' . $area . ' is quoted price on application from Stockport SK2.'
        . ' Postcode districts recorded for ' . $area . ': ' . ($p['districts'] ?? '') . '.'
        . ' Nearby towns on the same list: ' . $nearbyText . '.'
        . ' Local authority note: ' . $authorityNote . '.'
        . ' Non-gas compliance in ' . $area . ' is POA. Call ' . $phone . '.';
}

/** @return list<array{0:string,1:string}> */
function icomplyGasKeywordFaqs(string $name): array
{
    return [
        [
            'Who carries out a ' . $name . ' or a CP12?',
            'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.',
        ],
        [
            'Does iComply hold a Gas Safe registration?',
            'No. iComply does not hold a Gas Safe registration and does not show a Gas Safe logo, badge, or registration number. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.',
        ],
    ];
}

/** @return list<string> */
function icomplyGasKeywordPoints(): array
{
    return [
        'Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers',
        'iComply is not Gas Safe registered',
        'Non-gas compliance on the same property is quoted POA',
        'No Gas Safe logo, badge, or registration number',
    ];
}

/** @return list<string> */
function icomplyGasLocalAngles(string $serviceName, string $area): array
{
    return [
        "In {$area}, rented homes with gas appliances need a current landlord gas safety record. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.",
        "{$area} landlords still ask for CP12 paperwork. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. The quote is price on application.",
        "Boiler and flue questions in {$area} are listed with the appliance count. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.",
        "Commercial kitchens around {$area} may need a gas safety record. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.",
    ];
}

function icomplyGasServiceHubCopy(): array
{
    $legal = icomplyGasLegalSentence();
    return [
        'title' => 'Gas systems information | North West',
        'meta' => $legal,
        'blurb' => icomplyGasServiceBlurb(false),
        'standards' => icomplyGasServiceStandards(),
        'faqs' => [
            ['Who carries out landlord gas safety certificates (CP12)?', 'Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.'],
            ['Does iComply hold a Gas Safe registration?', 'No. iComply does not hold a Gas Safe registration. This site does not show a Gas Safe logo, badge, or registration number. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers.'],
            ['What can iComply quote on a property that also has gas?', 'Electrical, fire, water hygiene and asbestos work is quoted POA. Gas work and CP12 records stay with a Gas Safe registered engineer.'],
        ],
        'copy' => [
            'hero_accent' => 'Carried out by Gas Safe registered engineers.',
            'intro' => [
                $legal,
                'Use this page to see what a landlord gas safety record is, and to book the non-gas compliance iComply does quote: electrical testing, fire alarms, emergency lighting, water hygiene and asbestos surveys. Price on application after scope is agreed.',
                'There is no Gas Safe logo, badge, or registration number on this site, because iComply does not hold a Gas Safe registration.',
            ],
            'pillars' => [
                ['title' => 'What a CP12 is', 'text' => 'A landlord gas safety certificate (CP12) is the written record of a gas safety check. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.'],
                ['title' => 'Company registration', 'text' => 'iComply is not Gas Safe registered. This page does not show a registration number or a Gas Safe badge.'],
                ['title' => 'What iComply can quote', 'text' => 'Non-gas compliance on the same property is POA. Call ' . (defined('PHONE') ? PHONE : '') . ' or use the quote form.'],
            ],
        ],
    ];
}

function icomplyManufacturerEntryIsGas(array $entry): bool
{
    $services = $entry['services'] ?? [];
    if (in_array('gas-systems', $services, true)) {
        return true;
    }
    $blob = (string)($entry['blurb'] ?? '') . ' ' . (string)($entry['seo_desc'] ?? '') . ' ' . (string)($entry['name'] ?? '');
    return icomplyTextMentionsGas($blob) || (bool) preg_match('/\bboiler\b/i', $blob);
}

function icomplyGasBrandBlurb(string $brand): string
{
    return $brand . ' appears on this page as a trade-supply brand. '
        . icomplyGasLegalSentence()
        . ' Quotes for ' . $brand . ' appliances are price on application.';
}

function icomplySentenceClaimsGasWork(string $sentence): bool
{
    if (!preg_match('/\b(?:gas|CP12|CP44|boiler)\b/i', $sentence)) {
        return false;
    }
    if (preg_match('/\b(?:inert gas|gas suppression|clean agent)\b/i', $sentence)
        && !preg_match('/\b(?:CP12|CP44|boiler|gas safety)\b/i', $sentence)) {
        return false;
    }
    $negated = (bool) preg_match('/\b(?:does not|do not|is not|are not|not Gas Safe)\b/i', $sentence);
    $positive = (bool) preg_match(
        '/\b(?:iComply|iComply|we|our)\s+(?:issue|issues|install|installs|service|services|repair|repairs|deliver|delivers|provide|provides)\b/i',
        $sentence
    );
    if ($negated && !$positive) {
        return false;
    }
    if (preg_match('/\b(?:iComply|iComply|we|our engineers)\s+(?:are|is)\s+Gas Safe registered\b/i', $sentence)) {
        return true;
    }
    return (bool) preg_match(
        '/\b(?:iComply|iComply|we|our)\b.{0,200}\b(?:issue|issues|issuing|install|installs|installed|service|services|servicing|repair|repairs|deliver|delivers|provide|provides|attend|surveys|certificate|certify|certifies|carry out|carries out)\b.{0,120}\b(?:gas|CP12|CP44|boiler)\b/is',
        $sentence
    ) || (bool) preg_match(
        '/\b(?:gas|CP12|CP44|boiler)\b.{0,120}\b(?:iComply|iComply|we|our)\b.{0,100}\b(?:issue|install|service|repair|deliver|provide|attend|certificate)\b/is',
        $sentence
    );
}

function icomplyNeutralizeGasFragment(string $text): string
{
    if (!preg_match('/\b(?:gas|CP12|CP44|boiler)\b/i', $text)) {
        return $text;
    }
    $parts = preg_split('/(?<=[.!?])\s+/', $text);
    if ($parts === false) {
        return $text;
    }
    $changed = false;
    foreach ($parts as $i => $part) {
        if (icomplySentenceClaimsGasWork($part)) {
            $parts[$i] = icomplyGasLegalSentence();
            $changed = true;
        }
    }
    return $changed ? implode(' ', $parts) : $text;
}

function icomplyStripTimingFragment(string $text): string
{
    $text = preg_replace('/(?<![\w\/-])same-week(?![\w-])/i', 'diary-dependent', $text) ?? $text;
    $text = preg_replace('/(?<![\w\/-])same week(?![\w-])/i', 'diary-dependent', $text) ?? $text;
    $text = preg_replace('/(?<![\w\/-])same-day(?![\w-])/i', 'diary-dependent', $text) ?? $text;
    $text = preg_replace('/(?<![\w\/-])same day(?![\w-])/i', 'diary-dependent', $text) ?? $text;
    $text = preg_replace('/\bwithin 24 hours\b/i', 'when the diary allows', $text) ?? $text;
    $text = preg_replace('/\bwithin 2 hours\b/i', 'when the diary allows', $text) ?? $text;
    $text = preg_replace('/\bon time, every time\b/i', '', $text) ?? $text;
    return $text;
}

function icomplyHtmlMentionsGas(string $html): bool
{
    return stripos($html, 'gas') !== false
        || stripos($html, 'cp12') !== false
        || stripos($html, 'cp44') !== false;
}

function icomplyHtmlNeedsGasRewrite(string $html): bool
{
    foreach ([
        'we issue', 'We issue', 'we install', 'We install', 'we service', 'We service',
        'we repair', 'We repair', 'we deliver', 'We deliver', 'we provide', 'We provide',
        'our engineers', 'Our engineers', 'iComply deliver', 'iComply provide', 'iComply install',
        'iComply installs', 'iComply arranges', 'iComply surveys', 'iComply engineers',
        'iComply inspects', 'issues CP12', 'issue CP12', 'issuing CP12',
        'same-week', 'same week', 'Same-week', 'within 24 hours', 'within 2 hours',
        'on time, every time',
    ] as $needle) {
        if (str_contains($html, $needle)) {
            return true;
        }
    }
    return (bool) preg_match('/(?<![\w\/-])same-day(?![\w-])/i', $html)
        || (bool) preg_match('/(?<![\w\/-])same day(?![\w-])/i', $html);
}

function icomplyApplyGasLegalHtml(string $html): string
{
    if ($html === '' || !icomplyHtmlMentionsGas($html)) {
        return $html;
    }
    if (icomplyHtmlNeedsGasRewrite($html)) {
        $html = preg_replace_callback('/>([^<]*)</', static function (array $m): string {
            $inner = $m[1];
            if ($inner === '' || !preg_match('/[A-Za-z]/', $inner)) {
                return $m[0];
            }
            $next = icomplyNeutralizeGasFragment(icomplyStripTimingFragment($inner));
            return $next === $inner ? $m[0] : '>' . $next . '<';
        }, $html) ?? $html;
        $html = preg_replace_callback(
            '/\b(content|alt|title|aria-label)="([^"]*)"/',
            static function (array $m): string {
                $val = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
                $next = icomplyNeutralizeGasFragment(icomplyStripTimingFragment($val));
                if ($next === $val) {
                    return $m[0];
                }
                return $m[1] . '="' . htmlspecialchars($next, ENT_QUOTES, 'UTF-8') . '"';
            },
            $html
        ) ?? $html;
    }
    if (!str_contains($html, icomplyGasLegalPhrase())) {
        $note = '<p class="gas-legal-note">' . htmlspecialchars(icomplyGasLegalSentence(), ENT_QUOTES, 'UTF-8') . '</p>';
        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $note . '</body>', $html, 1) ?? ($html . $note);
        } else {
            $html .= $note;
        }
    }
    return $html;
}
