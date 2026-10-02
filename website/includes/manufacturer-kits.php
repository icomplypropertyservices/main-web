<?php
/**
 * Manufacturer hub → real trade-kit deep links.
 * Catalogue SKUs only (no template "From £" placeholders).
 * Contextual links (AOV list, Paxton/Videx barrier packs) point at products-hub anchors.
 */
declare(strict_types=1);

require_once __DIR__ . '/shopify.php';

/**
 * @return list<array<string,mixed>>
 */
function manufacturerCatalogueProducts(): array
{
    static $products = null;
    if ($products !== null) {
        return $products;
    }
    $catalog = getShopCatalog();
    $products = [];
    foreach ($catalog['products'] ?? [] as $product) {
        if (!is_array($product)) {
            continue;
        }
        $handle = strtolower(trim((string)($product['handle'] ?? '')));
        if ($handle === '') {
            continue;
        }
        $products[] = $product;
    }
    return $products;
}

/**
 * @return list<string>
 */
function manufacturerKitNeedles(string $name, string $slug): array
{
    $needles = [];
    $name = strtolower(trim($name));
    $slug = strtolower(trim($slug));
    if (strlen($name) >= 4) {
        $needles[] = $name;
    }
    $aliases = [
        'worcester-bosch' => ['worcester bosch', 'worcester'],
        'glow-worm' => ['glow-worm', 'glow worm'],
        'c-tec' => ['c-tec', 'ctec'],
        'advanced-electronics' => ['advanced electronics'],
        'mk-electric' => ['mk electric'],
        'schneider-electric' => ['schneider electric', 'schneider'],
        'honeywell' => ['honeywell'],
        'fireangel' => ['fireangel', 'fire angel'],
        'salto-systems' => ['salto'],
    ];
    foreach ($aliases[$slug] ?? [] as $alias) {
        $needles[] = $alias;
    }
    $needles = array_values(array_unique(array_filter($needles, static fn(string $n): bool => strlen($n) >= 4)));
    usort($needles, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    return $needles;
}

function manufacturerKitBlobMatches(string $blob, array $needles): bool
{
    foreach ($needles as $needle) {
        $pattern = '/(^|[^a-z0-9])' . preg_quote($needle, '/') . '([^a-z0-9]|$)/';
        if (preg_match($pattern, $blob)) {
            return true;
        }
    }
    return false;
}

/**
 * Real catalogue SKUs for a brand. Empty when the live shards have no match.
 *
 * @return list<array<string,mixed>>
 */
function manufacturerCatalogueKits(string $slug, int $limit = 8): array
{
    $entry = getManufacturerBySlug($slug);
    if ($entry === null) {
        return [];
    }
    $needles = manufacturerKitNeedles((string)($entry['name'] ?? ''), (string)($entry['slug'] ?? $slug));
    if ($needles === []) {
        return [];
    }
    $hits = [];
    foreach (manufacturerCatalogueProducts() as $product) {
        $type = strtolower((string)($product['product_type'] ?? ''));
        if (in_array($type, ['service contract', 'service package'], true)) {
            continue;
        }
        $blob = strtolower(
            (string)($product['handle'] ?? '') . ' '
            . (string)($product['title'] ?? '') . ' '
            . (string)($product['id'] ?? '')
        );
        if (!manufacturerKitBlobMatches($blob, $needles)) {
            continue;
        }
        $hits[] = $product;
    }
    usort($hits, static function (array $a, array $b): int {
        $pa = stripos((string)($a['price'] ?? ''), 'poa') === false ? 0 : 1;
        $pb = stripos((string)($b['price'] ?? ''), 'poa') === false ? 0 : 1;
        if ($pa !== $pb) {
            return $pa <=> $pb;
        }
        return strcasecmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
    });
    if ($limit > 0) {
        $hits = array_slice($hits, 0, $limit);
    }
    return $hits;
}

/**
 * One contextual kit card when the brand has a published products-hub target
 * and no (or in addition to) catalogue SKUs.
 *
 * @return list<array<string,mixed>>
 */
function manufacturerContextKits(string $slug): array
{
    $entry = getManufacturerBySlug($slug);
    if ($entry === null) {
        return [];
    }
    $services = $entry['services'] ?? [];
    $cards = [];
    if (in_array('aov-air-handling', $services, true)) {
        $cards[] = [
            'id' => $slug . '-aov-kits',
            'title' => 'AOV equipment kits',
            'blurb' => 'Supply list on the products hub (motor, actuator, controller, sensor, stairwell kit). Installation is POA.',
            'price' => 'Kit list',
            'handle' => '',
            'public_href' => url('/products.php') . '#aov-kits',
            'image' => '/assets/images/services/aov-air-handling.jpg',
            'badge' => 'Kit',
        ];
    }
    $barriers = [
        'came' => ['CAME GARD barrier packs', 'Kit list', 'Partner boom barriers. Supply prices are on the products hub. Installation is POA. Nationwide.'],
        'paxton' => ['BAR-5M-PAXTON', '£8,375.45', '5m barrier pack with Paxton. Supply price ex VAT. Install POA.'],
        'videx' => ['BAR-5M-VIDEX', '£7,441.83', '5m barrier pack with Videx. Supply price ex VAT. Install POA.'],
    ];
    if (isset($barriers[$slug])) {
        [$sku, $price, $blurb] = $barriers[$slug];
        $cards[] = [
            'id' => strtolower($sku),
            'title' => $sku . ' barrier pack',
            'blurb' => $blurb,
            'price' => $price,
            'handle' => '',
            'public_href' => url('/products.php') . '#barrier-packs',
            'image' => '/assets/images/services/access-control.jpg',
            'badge' => 'Kit',
        ];
    }
    return $cards;
}

/**
 * Kits rendered on a brand page: catalogue SKUs first, then contextual hub links.
 *
 * @return list<array<string,mixed>>
 */
function manufacturerPageKits(string $slug, int $limit = 8): array
{
    $kits = manufacturerCatalogueKits($slug, $limit);
    foreach (manufacturerContextKits($slug) as $card) {
        if (count($kits) >= $limit) {
            break;
        }
        $kits[] = $card;
    }
    return $kits;
}

/**
 * Brands that have at least one real catalogue PDP. Keyed by manufacturer slug.
 *
 * @return array<string, list<array<string,mixed>>>
 */
function manufacturerCatalogueKitIndex(): array
{
    static $index = null;
    if ($index !== null) {
        return $index;
    }
    $index = [];
    foreach (getManufacturerCatalog() as $slug => $entry) {
        $kits = manufacturerCatalogueKits((string)$slug, 6);
        if ($kits !== []) {
            $index[(string)$slug] = $kits;
        }
    }
    return $index;
}
