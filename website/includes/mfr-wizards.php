<?php
/**
 * Built-in quote wizards for AOV and barriers manufacturer pages.
 *
 * Markup is namespaced (mfr-wizard / mfr-line-card). It does not reuse
 * shop `.product-card`, products-hub kit articles, or `.aov-kit-prices`.
 * List prices stay on /products so this file cannot drift from that card grid.
 */
declare(strict_types=1);

function manufacturerWizardFamily(array $entry): ?string
{
    $services = $entry['services'] ?? [];
    if (in_array('barriers', $services, true) || ($entry['slug'] ?? '') === 'came') {
        return 'barriers';
    }
    if (in_array('aov-air-handling', $services, true)) {
        return 'aov';
    }
    return null;
}

function manufacturerIsBarriersPartner(array $entry): bool
{
    return ($entry['slug'] ?? '') === 'came' || !empty($entry['partner']);
}

/**
 * @param list<array{label:string,slug:string,blurb:string,logo:string}> $lines
 */
function manufacturerWizardHtml(array $entry, string $area, array $lines): string
{
    $family = manufacturerWizardFamily($entry);
    if ($family === null) {
        return manufacturerNextStepLinksHtml($entry, $area);
    }
    $brand = (string)($entry['name'] ?? '');
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $productsUrl = $family === 'barriers' ? url('/products') . '#barrier-packs' : url('/products') . '#aov-kits';
    $serviceSlug = $family === 'barriers' ? 'barriers' : 'aov-air-handling';
    $serviceName = function_exists('getServices') ? (getServices()[$serviceSlug] ?? $serviceSlug) : $serviceSlug;

    $html = '<section class="mfr-wizard" id="wizard" data-mfr-wizard data-family="' . $h($family) . '">';
    $html .= '<div class="mfr-wizard-head">';
    $html .= '<p class="mfr-kicker">Quote wizard</p>';
    $html .= '<h2>' . $h($brand) . ' in ' . $h($area) . '</h2>';
    if ($family === 'barriers' && manufacturerIsBarriersPartner($entry)) {
        $html .= '<p class="mfr-partner-banner">CAME is our barriers partner. This wizard collects the boom, gate and access detail. Supply prices for the 5m packs stay on the products hub. Installation is POA after survey.</p>';
    } elseif ($family === 'aov') {
        $html .= '<p class="mfr-wizard-lead">Tell us the vent job and the ' . $h($brand) . ' range. Equipment-kit list prices are linked, not reprinted as shop cards. Installation, labour and commissioning are POA.</p>';
    }
    $html .= '</div>';

    $html .= '<ol class="mfr-wizard-progress" aria-hidden="true"><li data-dot="1">Job</li><li data-dot="2">Range</li><li data-dot="3">Kit</li><li data-dot="4">Contact</li></ol>';

    $html .= '<fieldset class="mfr-wizard-step" data-step="1"><legend>What do you need in ' . $h($area) . '?</legend><div class="mfr-wizard-options">';
    $jobs = $family === 'barriers'
        ? ['New barrier or gate', 'Service an existing operator', 'Fault — arm, loop or safety edge', 'Connect access control or GSM']
        : ['New smoke vent or shaft', 'Service actuators and panel', 'Fault — vent will not open or close', 'Cause-and-effect with the fire alarm'];
    foreach ($jobs as $i => $job) {
        $id = 'wiz-job-' . $i;
        $html .= '<label class="mfr-wizard-option" for="' . $id . '"><input id="' . $id . '" type="radio" name="wizard_job" value="' . $h($job) . '"' . ($i === 0 ? ' checked' : '') . '> <span>' . $h($job) . '</span></label>';
    }
    $html .= '</div></fieldset>';

    $html .= '<fieldset class="mfr-wizard-step" data-step="2"><legend>' . $h($brand) . ' product line</legend><div class="mfr-wizard-options">';
    foreach ($lines as $i => $line) {
        $id = 'wiz-line-' . $i;
        $html .= '<label class="mfr-wizard-option" for="' . $id . '">';
        $html .= '<input id="' . $id . '" type="radio" name="wizard_line" value="' . $h($line['label']) . '"' . ($i === 0 ? ' checked' : '') . '>';
        $logo = function_exists('url') ? url($line['logo']) : $line['logo'];
        $html .= '<img class="mfr-line-logo" src="' . $h($logo) . '" alt="" width="72" height="40" loading="lazy">';
        $html .= '<span><strong>' . $h($line['label']) . '</strong><small>' . $h($line['blurb']) . '</small></span>';
        $html .= '</label>';
    }
    $html .= '</div></fieldset>';

    $html .= '<fieldset class="mfr-wizard-step" data-step="3"><legend>Which equipment should the quote mention?</legend><div class="mfr-wizard-options">';
    if ($family === 'barriers') {
        $kits = [
            '5m standard pack (see products hub)',
            '5m with Videx',
            '5m with Paxton',
            '5m with GSM',
            '5m all-in pack',
            'Not a 5m pack — survey the opening',
        ];
    } else {
        $kits = [
            'Actuator / motor (AOV-ACT or AOV-MOTOR on the products hub)',
            'Heavy actuator or motor',
            'Control panel (AOV-CTRL)',
            'Sensor (AOV-SENSOR)',
            'Stairwell kit (AOV-KIT-1M2)',
            'Not sure — survey only',
        ];
    }
    foreach ($kits as $i => $kit) {
        $id = 'wiz-kit-' . $i;
        $html .= '<label class="mfr-wizard-option" for="' . $id . '"><input id="' . $id . '" type="radio" name="wizard_kit" value="' . $h($kit) . '"' . ($i === 0 ? ' checked' : '') . '> <span>' . $h($kit) . '</span></label>';
    }
    $html .= '</div>';
    $html .= '<p class="mfr-wizard-linkout">List prices are not drawn here as product cards. <a href="' . $h($productsUrl) . '">Open ' . $h($serviceName) . ' prices on the products hub</a>. Installation remains POA.</p>';
    $html .= '</fieldset>';

    $html .= '<div class="mfr-wizard-nav"><button type="button" class="mfr-wizard-back" data-wiz-back>Back</button><button type="button" class="mfr-wizard-next" data-wiz-next>Next</button></div>';
    $html .= '</section>';
    return $html;
}

function manufacturerNextStepLinksHtml(array $entry, string $area): string
{
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $slug = (string)($entry['slug'] ?? '');
    $brand = (string)($entry['name'] ?? '');
    $primary = (string)(($entry['services'][0] ?? 'fire-alarms'));
    $areaSlug = function_exists('areaSlug') ? areaSlug($area) : $area;
    $serviceName = function_exists('getServices') ? (getServices()[$primary] ?? $primary) : $primary;
    $links = [
        [url('/pages/manufacturers/' . $slug), $brand . ' manufacturer hub'],
        [url('/pages/services/' . $primary), $serviceName . ' service'],
        [url('/pages/' . $primary . '/' . $areaSlug), $serviceName . ' in ' . $area],
        [url('/products'), 'Trade products hub'],
        [url('/contact'), 'Quote form'],
        [url('/shop'), 'Trade shop'],
    ];
    $html = '<section class="mfr-link-panel" id="next-steps"><p class="mfr-kicker">Next steps</p>';
    $html .= '<h2>Links for ' . $h($brand) . ' in ' . $h($area) . '</h2>';
    $html .= '<ul class="mfr-link-row">';
    foreach ($links as [$href, $label]) {
        $html .= '<li><a class="mfr-text-link" href="' . $h($href) . '">' . $h($label) . '</a></li>';
    }
    $html .= '</ul></section>';
    return $html;
}
