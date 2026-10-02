<?php
/**
 * Manufacturer product-line cards with original wordmarks.
 * Logos link to the manufacturer page when that brand is in the catalog.
 * Kit-builder links appear only when a sibling wizard page is present.
 */
declare(strict_types=1);

function manufacturerProductLineData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $path = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/manufacturer-product-lines.json';
    $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

function manufacturerProductLineGroup(string $key): ?array
{
    $groups = manufacturerProductLineData()['groups'] ?? [];
    $group = $groups[$key] ?? null;
    return is_array($group) ? $group : null;
}

/** Kit builder URL when the sibling wizard file or catalog is on this site. */
function icomplyKitWizardHref(string $slug): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug) ?? '');
    if ($slug === '') {
        return '';
    }
    $root = defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__);
    $files = [
        $root . '/pages/kits/' . $slug . '.php',
        $root . '/pages/kits/' . $slug . '/index.php',
    ];
    $ready = false;
    foreach ($files as $file) {
        if (is_file($file)) {
            $ready = true;
            break;
        }
    }
    if (!$ready && function_exists('kitWizardBySlug')) {
        $wizard = kitWizardBySlug($slug);
        $ready = is_array($wizard);
    }
    if (!$ready) {
        return '';
    }
    return function_exists('url') ? url('/pages/kits/' . $slug) : '/pages/kits/' . $slug;
}

function manufacturerProductLineLogoUrl(string $slug): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug) ?? '');
    $rel = '/assets/images/brand-logos/' . $slug . '.svg';
    return function_exists('url') ? url($rel) : $rel;
}

/**
 * @param array<string,mixed> $brand
 * @param array<string,mixed> $group
 * @return array{primary:string,primary_label:string,wizard:string}
 */
function manufacturerProductLineLinks(array $brand, array $group): array
{
    $slug = (string) ($brand['slug'] ?? '');
    $name = (string) ($brand['name'] ?? $slug);
    $primary = '';
    $primaryLabel = 'Manufacturer';
    if ($slug !== '' && function_exists('getManufacturerBySlug') && getManufacturerBySlug($slug)) {
        $primary = url('/pages/manufacturers/' . $slug . '.php');
        $primaryLabel = 'Brand page';
    }
    $wizard = icomplyKitWizardHref((string) ($group['wizard'] ?? ''));
    if ($primary === '' && $wizard !== '') {
        $primary = $wizard;
        $primaryLabel = 'Kit builder';
        $wizard = '';
    }
    $shopHref = '';
    $shop = (string) ($brand['shop_href'] ?? '');
    if ($shop !== '') {
        $shopHref = function_exists('url') ? url($shop) : $shop;
    }
    if ($primary === '') {
        if ($shopHref !== '') {
            $primary = $shopHref;
            $primaryLabel = 'Shop range';
            $shopHref = '';
        } else {
            $primary = function_exists('url') ? url('/contact.php') : '/contact.php';
            $primaryLabel = 'Enquire';
        }
    }
    return [
        'primary' => $primary,
        'primary_label' => $primaryLabel,
        'wizard' => $wizard,
        'shop' => $shopHref,
        'name' => $name,
    ];
}

/**
 * @param list<string> $keys
 */
function manufacturerProductLinesHtmlFor(array $keys, string $variant = 'full'): string
{
    $html = '';
    foreach ($keys as $key) {
        $html .= manufacturerProductLinesHtml($key, $variant);
    }
    return $html;
}

function manufacturerProductLineGroupsForPage(string $serviceSlug, string $keywordSlug = ''): array
{
    $map = [
        'aov-air-handling' => 'aov',
        'fire-alarms' => 'fire',
        'access-control' => 'access-control',
        'nurse-call' => 'nurse-call',
    ];
    $keys = [];
    if (isset($map[$serviceSlug])) {
        $keys[] = $map[$serviceSlug];
    }
    $keywordSlug = strtolower($keywordSlug);
    if (str_contains($keywordSlug, 'barrier')) {
        $keys[] = 'barrier';
    }
    if (str_contains($keywordSlug, 'aov') && !in_array('aov', $keys, true)) {
        $keys[] = 'aov';
    }
    return array_values(array_unique($keys));
}

function manufacturerProductLinesHtml(string $groupKey, string $variant = 'full'): string
{
    $group = manufacturerProductLineGroup($groupKey);
    if ($group === null) {
        return '';
    }
    $brands = $group['brands'] ?? [];
    if (!is_array($brands) || $brands === []) {
        return '';
    }
    $compact = $variant === 'compact';
    $title = htmlspecialchars((string) ($group['title'] ?? 'Product lines'), ENT_QUOTES, 'UTF-8');
    $kicker = htmlspecialchars((string) ($group['kicker'] ?? 'Product lines'), ENT_QUOTES, 'UTF-8');
    $intro = htmlspecialchars((string) ($group['intro'] ?? ''), ENT_QUOTES, 'UTF-8');
    $wizard = icomplyKitWizardHref((string) ($group['wizard'] ?? ''));
    $wizardLabel = $groupKey === 'barrier' ? 'Barriers kit builder' : 'AOV kit builder';

    $cards = '';
    $brandCount = 0;
    foreach ($brands as $brand) {
        if (!is_array($brand)) {
            continue;
        }
        $slug = strtolower((string) ($brand['slug'] ?? ''));
        if ($slug === '' || $slug === 'tunstall') {
            continue;
        }
        $cards .= manufacturerProductLineCardHtml($brand, $group, $compact);
        $brandCount++;
    }
    if ($cards === '') {
        return '';
    }

    $wizardBtn = '';
    if ($wizard !== '') {
        $wizardBtn = '<a class="mfr-line-wizard" href="' . htmlspecialchars($wizard, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($wizardLabel, ENT_QUOTES, 'UTF-8') . '</a>';
    }

    $id = 'product-lines-' . preg_replace('/[^a-z0-9\-]/', '', $groupKey);
    $solo = $brandCount === 1 ? ' mfr-lines--solo' : '';
    return '<section class="mfr-lines' . ($compact ? ' mfr-lines--compact' : '') . $solo . '" id="' . $id . '" aria-label="' . $title . '">'
        . '<div class="mfr-lines-head">'
        . '<div><p class="mfr-lines-kicker">' . $kicker . '</p>'
        . '<h2 class="mfr-lines-title">' . $title . '</h2>'
        . ($intro !== '' ? '<p class="mfr-lines-intro">' . $intro . '</p>' : '')
        . '</div>'
        . $wizardBtn
        . '</div>'
        . '<div class="mfr-line-grid">' . $cards . '</div>'
        . '</section>';
}

/**
 * @param array<string,mixed> $brand
 * @param array<string,mixed> $group
 */
function manufacturerProductLineCardHtml(array $brand, array $group, bool $compact): string
{
    $slug = strtolower((string) ($brand['slug'] ?? ''));
    $links = manufacturerProductLineLinks($brand, $group);
    $name = htmlspecialchars($links['name'], ENT_QUOTES, 'UTF-8');
    $href = htmlspecialchars($links['primary'], ENT_QUOTES, 'UTF-8');
    $logo = htmlspecialchars(manufacturerProductLineLogoUrl($slug), ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($links['primary_label'], ENT_QUOTES, 'UTF-8');

    $lines = '';
    foreach ($brand['lines'] ?? [] as $line) {
        if (!is_array($line)) {
            continue;
        }
        $lineName = htmlspecialchars((string) ($line['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $blurb = htmlspecialchars((string) ($line['blurb'] ?? ''), ENT_QUOTES, 'UTF-8');
        if ($lineName === '') {
            continue;
        }
        $lines .= '<li class="mfr-line-chip"><span class="mfr-line-name">' . $lineName . '</span>'
            . ($blurb !== '' && !$compact ? '<span class="mfr-line-blurb">' . $blurb . '</span>' : '')
            . '</li>';
    }

    $actions = '<a class="mfr-line-go" href="' . $href . '">' . $label . '</a>';
    if ($links['wizard'] !== '') {
        $actions .= '<a class="mfr-line-go mfr-line-go--ghost" href="'
            . htmlspecialchars($links['wizard'], ENT_QUOTES, 'UTF-8') . '">Kit builder</a>';
    } elseif (($links['shop'] ?? '') !== '') {
        $actions .= '<a class="mfr-line-go mfr-line-go--ghost" href="'
            . htmlspecialchars($links['shop'], ENT_QUOTES, 'UTF-8') . '">Shop range</a>';
    }

    return '<article class="mfr-line-card" id="product-line-' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '">'
        . '<a class="mfr-line-logo" href="' . $href . '">'
        . '<img src="' . $logo . '" alt="' . $name . '" width="560" height="96" loading="lazy" decoding="async">'
        . '</a>'
        . '<div class="mfr-line-body">'
        . '<h3 class="mfr-line-brand"><a href="' . $href . '">' . $name . '</a></h3>'
        . '<ul class="mfr-line-chips">' . $lines . '</ul>'
        . '<div class="mfr-line-actions">' . $actions . '</div>'
        . '</div></article>';
}

function manufacturerProductLinesForBrandHtml(string $slug): string
{
    $slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug) ?? '');
    if ($slug === '' || $slug === 'tunstall') {
        return '';
    }
    $groups = manufacturerProductLineData()['groups'] ?? [];
    $html = '';
    foreach ($groups as $key => $group) {
        if (!is_array($group)) {
            continue;
        }
        foreach ($group['brands'] ?? [] as $brand) {
            if (!is_array($brand) || strtolower((string) ($brand['slug'] ?? '')) !== $slug) {
                continue;
            }
            $title = (string) ($brand['name'] ?? '') . ' product lines';
            $html .= '<section class="mfr-lines mfr-lines--solo" id="product-lines-' . htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') . '" aria-label="'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">'
                . '<div class="mfr-lines-head"><div>'
                . '<p class="mfr-lines-kicker">' . htmlspecialchars((string) ($group['kicker'] ?? 'Product lines'), ENT_QUOTES, 'UTF-8') . '</p>'
                . '<h2 class="mfr-lines-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>'
                . '</div></div>'
                . '<div class="mfr-line-grid">' . manufacturerProductLineCardHtml($brand, $group, false) . '</div>'
                . '</section>';
        }
    }
    return $html;
}

/** Shop-hub markup (shop.css), same data as the main site cards. */
function manufacturerProductLinesShopHtml(string $groupKey): string
{
    $html = manufacturerProductLinesHtml($groupKey, 'full');
    return $html;
}
