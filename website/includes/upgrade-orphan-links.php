<?php
/**
 * Internal links for keyword×town pages that had almost no impressions
 * because nothing on the site pointed at them.
 */
declare(strict_types=1);

function icomplyUpgradeOrphanRows(): array
{
    $file = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/seo-upgrade/orphans.json';
    if (!is_file($file)) {
        return [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    return is_array($decoded['rows'] ?? null) ? $decoded['rows'] : [];
}

function icomplyUpgradeOrphanLinksForTown(string $townSlug): string
{
    $townSlug = strtolower(trim($townSlug));
    if ($townSlug === '') {
        return '';
    }
    $items = [];
    foreach (icomplyUpgradeOrphanRows() as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (strtolower((string)($row['town'] ?? '')) !== $townSlug) {
            continue;
        }
        $href = (string)($row['href'] ?? '');
        $label = (string)($row['label'] ?? '');
        if ($href === '' || $label === '') {
            continue;
        }
        $items[] = '<li><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a></li>';
    }
    if ($items === []) {
        return '';
    }
    return '<section class="section" data-orphan-links="town">'
        . '<h2>Local guides for this town</h2>'
        . '<ul>' . implode('', $items) . '</ul></section>';
}
