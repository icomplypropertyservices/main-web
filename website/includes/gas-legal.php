<?php
/**
 * iComply holds no Gas Safe registration.
 * Gas work and CP12 / gas safety certificates must be described as carried out
 * by a Gas Safe registered engineer. Do not claim iComply performs that work.
 */
declare(strict_types=1);

function icomplyGasLegalPhrase(): string
{
    return 'carried out by a Gas Safe registered engineer';
}

/** Short site footer and gas-page CTA line. Engineers are registered; iComply is not. */
function icomplySpecialistWorksLine(): string
{
    return 'Gas works are carried out by Gas Safe registered engineers. Some specialist works may be carried out by approved subcontractors.';
}

function icomplyGasLegalSentence(): string
{
    return 'Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. '
        . 'iComply does not carry out gas work, does not issue CP12 or gas safety certificates, and is not Gas Safe registered.';
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
        return 'Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. iComply does not issue them.';
    }
    return icomplyGasLegalSentence()
        . ' This page explains the record landlords ask for. iComply quotes electrical, fire, water hygiene and asbestos work on the same property. Price on application.';
}

function icomplyGasServiceStandards(): string
{
    return 'Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. iComply does not issue CP12 or gas safety certificates.';
}

function icomplyGasMetaDesc(string $name, string $area = ''): string
{
    $where = $area !== '' ? ' in ' . $area : ' across the North West';
    $desc = 'Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. '
        . $name . $where . '. iComply does not issue them.';
    if (strlen($desc) > 160) {
        $desc = 'Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. iComply does not issue them.';
    }
    return $desc;
}

function icomplyGasKeywordIntro(string $name, string $area = ''): string
{
    $where = $area !== '' ? ' in ' . $area : ' across the North West';
    return $name . $where . ' is listed for landlords and agents who need landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. '
        . 'iComply Property Services does not carry out gas work and does not issue CP12 or gas safety certificates.';
}

function icomplyGasKeywordBody(string $name, string $area = ''): string
{
    $phone = defined('PHONE') ? PHONE : '';
    $legal = icomplyGasLegalSentence();
    if ($area === '' || !function_exists('area_profile')) {
        return $legal . ' This ' . $name . ' page is a guide only. iComply quotes non-gas compliance (electrical, fire, water hygiene, asbestos) POA from Stockport SK2. Call ' . $phone . '.';
    }
    $p = area_profile($area);
    $nearby = function_exists('icomplyNearbyTowns') ? icomplyNearbyTowns($area, 4) : [];
    $nearbyText = $nearby ? implode(', ', $nearby) : 'other listed North West towns';
    $authority = trim((string)($p['authority'] ?? ''));
    $authorityNote = $authority !== '' ? $authority : 'none stored on this town profile';
    return $legal
        . ' ' . $name . ' in ' . $area . ' is not a gas visit carried out by iComply.'
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
            'Does iComply issue a ' . $name . ' or a CP12?',
            'No. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. iComply does not carry out gas work or issue gas safety certificates.',
        ],
        [
            'Does iComply hold a Gas Safe registration?',
            'No. iComply does not hold a Gas Safe registration and does not show a Gas Safe logo, badge, or registration number. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer.',
        ],
    ];
}

/** @return list<string> */
function icomplyGasKeywordPoints(): array
{
    return [
        'Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer',
        'iComply does not carry out gas work or issue CP12 certificates',
        'Non-gas compliance on the same property is quoted POA',
        'No Gas Safe logo, badge, or registration number',
    ];
}

/** @return list<string> */
function icomplyGasLocalAngles(string $serviceName, string $area): array
{
    return [
        "In {$area}, rented homes with gas appliances need a current landlord gas safety record. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. iComply does not issue that record.",
        "{$area} landlords still ask for CP12 paperwork. iComply does not carry out the gas check in {$area}. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer.",
        "Boiler and flue questions in {$area} are gas work. iComply does not install, service, or repair boilers in {$area}. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer.",
        "Commercial kitchens around {$area} may need a gas safety record. That visit is not carried out by iComply. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer.",
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
            ['Does iComply issue landlord gas safety certificates (CP12)?', 'No. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer. iComply does not carry out gas work or issue CP12 or gas safety certificates.'],
            ['Does iComply hold a Gas Safe registration?', 'No. iComply does not hold a Gas Safe registration. This site does not show a Gas Safe logo, badge, or registration number. Landlord gas safety certificates (CP12), carried out by a Gas Safe registered engineer.'],
            ['What can iComply quote on a property that also has gas?', 'Electrical, fire, water hygiene and asbestos work is quoted POA. Gas work and CP12 records stay with a Gas Safe registered engineer.'],
        ],
        'copy' => [
            'hero_accent' => 'Not carried out by iComply.',
            'intro' => [
                $legal,
                'Use this page to see what a landlord gas safety record is, and to book the non-gas compliance iComply does quote: electrical testing, fire alarms, emergency lighting, water hygiene and asbestos surveys. Price on application after scope is agreed.',
                'There is no Gas Safe logo, badge, or registration number on this site, because iComply does not hold a Gas Safe registration.',
            ],
            'pillars' => [
                ['title' => 'What a CP12 is', 'text' => 'A landlord gas safety certificate (CP12) is the written record of a gas safety check. It is carried out by a Gas Safe registered engineer, not by iComply.'],
                ['title' => 'What iComply does not do', 'text' => 'iComply does not install, service, or repair boilers, and does not issue CP12 or gas safety certificates.'],
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
        . ' iComply does not install, service, or repair ' . $brand . ' boilers or gas appliances.';
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
