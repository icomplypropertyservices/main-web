<?php
/**
 * Visible service prices — single source of truth.
 * Confirmed figures only. Do not add other pound prices here.
 */
declare(strict_types=1);

/**
 * @return array<string, array{id:string,label:string,amount:int,display:string,service_slugs:list<string>,keyword_slugs:list<string>}>
 */
function icomplyServicePriceCatalog(): array
{
    return [
        'eicr' => [
            'id' => 'eicr',
            'label' => 'EICR',
            'amount' => 249,
            'display' => '£249',
            'service_slugs' => ['electrical'],
            'keyword_slugs' => [
                'eicr',
                'eicr-cost',
                'eicr-price',
                'eicr-certificate',
                'eicr-report',
                'landlord-eicr',
                'landlord-electrical-certificate',
            ],
        ],
        'fra' => [
            'id' => 'fra',
            'label' => 'FRA',
            'amount' => 350,
            'display' => '£350',
            'service_slugs' => ['fire-risk-assessments'],
            'keyword_slugs' => [
                'fire-risk-assessment',
            ],
        ],
        'gas-safety' => [
            'id' => 'gas-safety',
            'label' => 'Gas safety',
            'amount' => 85,
            'display' => '£85',
            'service_slugs' => ['gas-systems'],
            'keyword_slugs' => [
                'gas-safety',
                'gas-safety-certificate',
                'gas-safety-certificate-cost',
                'cp12',
                'cp12-cost',
                'landlord-gas-safety-certificate',
            ],
        ],
        'compliance-bundle' => [
            'id' => 'compliance-bundle',
            'label' => 'Compliance bundle',
            'amount' => 650,
            'display' => '£650',
            'service_slugs' => ['landlord-compliance'],
            'keyword_slugs' => [
                'landlord-compliance-package',
            ],
        ],
    ];
}

/**
 * @return array{id:string,label:string,amount:int,display:string,service_slugs:list<string>,keyword_slugs:list<string>}|null
 */
function icomplyServicePriceById(string $id): ?array
{
    $catalog = icomplyServicePriceCatalog();
    return $catalog[$id] ?? null;
}

/**
 * Price for a service hub (by slug) or a keyword page (keyword wins; no service fallback).
 *
 * @return array{id:string,label:string,amount:int,display:string,service_slugs:list<string>,keyword_slugs:list<string>}|null
 */
function icomplyVisibleServicePrice(?string $serviceSlug = null, ?string $keywordSlug = null): ?array
{
    $catalog = icomplyServicePriceCatalog();
    if ($keywordSlug !== null && $keywordSlug !== '') {
        $keywordSlug = strtolower(trim($keywordSlug));
        foreach ($catalog as $row) {
            if (in_array($keywordSlug, $row['keyword_slugs'], true)) {
                return $row;
            }
        }
        return null;
    }
    if ($serviceSlug === null || $serviceSlug === '') {
        return null;
    }
    $serviceSlug = strtolower(trim($serviceSlug));
    foreach ($catalog as $row) {
        if (in_array($serviceSlug, $row['service_slugs'], true)) {
            return $row;
        }
    }
    return null;
}

function icomplyServicePriceSentence(?array $price): string
{
    if ($price === null) {
        return '';
    }
    return $price['label'] . ' ' . $price['display']
        . '. Work outside that published price is quoted after scope — no other published prices.';
}

/**
 * @param 'on-dark'|'on-light' $tone
 */
function icomplyServicePriceNoteHtml(?array $price, string $tone = 'on-dark'): string
{
    if ($price === null) {
        return '';
    }
    $label = htmlspecialchars($price['label'], ENT_QUOTES, 'UTF-8');
    $display = htmlspecialchars($price['display'], ENT_QUOTES, 'UTF-8');
    $muted = $tone === 'on-light' ? 'text-zinc-600' : 'text-white/70';
    $strong = $tone === 'on-light' ? 'text-[#0B1F3A]' : 'text-white';
    return '<p class="mt-4 text-sm font-semibold ' . $strong . '">'
        . $label . ' <span class="text-[#ff6b00]">' . $display . '</span>'
        . ' <span class="font-normal ' . $muted . '">· published price · other work quoted after scope</span></p>';
}

/**
 * Replace guide-row pound amounts that are not in the catalog with POA,
 * and force named EICR / FRA / gas safety / compliance bundle rows onto the catalog.
 *
 * @param list<array<string,mixed>> $categories
 * @return list<array<string,mixed>>
 */
function icomplySyncGuidePriceCategories(array $categories): array
{
    foreach ($categories as &$cat) {
        if (!isset($cat['items']) || !is_array($cat['items'])) {
            continue;
        }
        foreach ($cat['items'] as &$item) {
            if (!is_array($item)) {
                continue;
            }
            $name = (string)($item['name'] ?? '');
            $synced = icomplyGuidePriceDisplayForName($name);
            if ($synced !== null) {
                $item['from'] = $synced;
                continue;
            }
            $from = (string)($item['from'] ?? '');
            if (preg_match('/£/', $from)) {
                $item['from'] = 'POA';
            }
        }
        unset($item);
    }
    unset($cat);
    return $categories;
}

function icomplyGuidePriceDisplayForName(string $name): ?string
{
    $n = strtolower(trim($name));
    $id = match ($n) {
        'eicr' => 'eicr',
        'fra', 'fra (fire risk assessment)' => 'fra',
        'compliance bundle' => 'compliance-bundle',
        'gas safety', 'landlord gas safety (cp12)' => 'gas-safety',
        default => null,
    };
    if ($id === null) {
        return null;
    }
    return icomplyServicePriceById($id)['display'] ?? null;
}
