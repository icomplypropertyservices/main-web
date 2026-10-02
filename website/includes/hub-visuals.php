<?php
/**
 * Real photos for service hubs and area pages.
 * Local JPEGs under assets/images, plus Shopify / BigCommerce CDN stills
 * listed in data/hub-visuals.json. Never emit a local path that is not on disk.
 */
declare(strict_types=1);

function hubVisualCatalog(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $path = SITE_ROOT . '/data/hub-visuals.json';
    $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

function hubFamilyForService(string $slug): string
{
    $map = hubVisualCatalog()['service_family'] ?? [];
    $family = $map[$slug] ?? 'works';
    return is_string($family) && $family !== '' ? $family : 'works';
}

function hubRotateOffset(string $seed, int $count): int
{
    if ($count <= 1) {
        return 0;
    }
    $n = (int) sprintf('%u', crc32($seed));
    return $n % $count;
}

/**
 * @param list<array<string,mixed>> $items
 * @return list<array<string,mixed>>
 */
function hubRotate(array $items, string $seed, int $limit): array
{
    $count = count($items);
    if ($count === 0 || $limit <= 0) {
        return [];
    }
    $offset = hubRotateOffset($seed, $count);
    $out = [];
    $take = min($limit, $count);
    for ($i = 0; $i < $take; $i++) {
        $out[] = $items[($offset + $i) % $count];
    }
    return $out;
}

function hubLocalFileUrl(string $rel): ?string
{
    $rel = '/' . ltrim($rel, '/');
    if (!is_file(SITE_ROOT . $rel)) {
        return null;
    }
    return url($rel);
}

function hubFileHash(string $rel): string
{
    static $cache = [];
    $rel = '/' . ltrim($rel, '/');
    if (isset($cache[$rel])) {
        return $cache[$rel];
    }
    $path = SITE_ROOT . $rel;
    $cache[$rel] = is_file($path) ? (md5_file($path) ?: '') : '';
    return $cache[$rel];
}

function hubImageKey(string $src): string
{
    $src = preg_replace('/\?.*$/', '', trim($src)) ?? trim($src);
    return strtolower($src);
}

function hubAreaSiblingIndex(string $areaName): int
{
    if ($areaName === '' || !function_exists('getAreas')) {
        return 0;
    }
    $areas = getAreas();
    $index = array_search($areaName, $areas, true);
    if ($index === false && function_exists('areaFromSlug')) {
        $resolved = areaFromSlug($areaName);
        if (is_string($resolved) && $resolved !== '') {
            $index = array_search($resolved, $areas, true);
        }
    }
    return $index === false ? 0 : (int) $index;
}

/**
 * Distinct real photos for a family. Order is stable so sibling towns
 * walk the list instead of hashing onto the same file.
 *
 * @return list<array{src:string,alt:string,fit:string}>
 */
function hubDistinctImagePool(string $family): array
{
    static $cache = [];
    if (isset($cache[$family])) {
        return $cache[$family];
    }
    $rows = [];
    $seen = [];
    $push = static function (string $src, string $alt, string $fit) use (&$rows, &$seen): void {
        $key = hubImageKey($src);
        if ($key === '' || isset($seen[$key])) {
            return;
        }
        $seen[$key] = true;
        $rows[] = ['src' => $src, 'alt' => $alt, 'fit' => $fit];
    };

    // AOV and nurse-call pools are small. Keep them last so other services
    // do not claim those stills and reprint them on AOV town pages.
    $families = ($family === 'areas' || $family === 'works' || $family === 'featured')
        ? ['fire', 'electrical', 'gas', 'security', 'nurse-call', 'aov']
        : [$family];

    if ($family === 'areas' || $family === 'works' || $family === 'featured') {
        foreach (hubVisualCatalog()['local_unique'] ?? [] as $row) {
            if (!is_array($row)) {
                continue;
            }
            $src = hubLocalFileUrl((string) ($row['src'] ?? ''));
            if ($src !== null) {
                $push($src, (string) ($row['alt'] ?? 'Property services'), 'cover');
            }
        }
    }

    foreach ($families as $name) {
        foreach (hubProductsForFamily($name) as $product) {
            $push($product['image'], $product['alt'], 'contain');
        }
    }

    $cache[$family] = $rows;
    return $rows;
}

/**
 * One primary per sibling town. Index walks the pool, so the first N towns
 * never share a src when the pool has N distinct files.
 *
 * @return array{src:string,alt:string,fit:string}|null
 */
function hubPrimaryForArea(string $family, string $areaName, string $alt, int $shift = 0): ?array
{
    $pool = hubDistinctImagePool($family);
    $count = count($pool);
    if ($count === 0) {
        return null;
    }
    $index = hubAreaSiblingIndex($areaName) + $shift;
    $item = $pool[($index % $count + $count) % $count];
    return [
        'src' => $item['src'],
        'alt' => $alt !== '' ? $alt : $item['alt'],
        'fit' => $item['fit'],
    ];
}

/**
 * One primary src per service hub. A JPEG shared by several services stays
 * with the first service; every later service takes the next unclaimed photo.
 *
 * @return array<string, array{src:string,alt:string,fit:string}>
 */
function hubServicePrimaryMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [];
    $claimed = [];
    $services = function_exists('getServices') ? array_keys(getServices()) : [];
    $groups = [];
    foreach ($services as $slug) {
        $hash = hubFileHash('/assets/images/services/' . $slug . '.jpg');
        $groups[$hash][] = $slug;
    }
    foreach ($groups as $hash => $slugs) {
        if ($hash === '' || $slugs === []) {
            continue;
        }
        $owner = $slugs[0];
        $src = hubLocalFileUrl('/assets/images/services/' . $owner . '.jpg');
        if ($src === null) {
            continue;
        }
        $map[$owner] = ['src' => $src, 'alt' => '', 'fit' => 'cover'];
        $claimed[hubImageKey($src)] = true;
    }
    foreach ($services as $index => $slug) {
        if (isset($map[$slug])) {
            continue;
        }
        $pool = hubDistinctImagePool(hubFamilyForService($slug));
        $count = count($pool);
        $picked = null;
        for ($step = 0; $step < $count; $step++) {
            $item = $pool[($index + $step) % $count];
            if (isset($claimed[hubImageKey($item['src'])])) {
                continue;
            }
            $picked = $item;
            break;
        }
        if ($picked === null) {
            continue;
        }
        $map[$slug] = ['src' => $picked['src'], 'alt' => '', 'fit' => $picked['fit']];
        $claimed[hubImageKey($picked['src'])] = true;
    }
    return $map;
}

/**
 * @return array{src:string,alt:string,fit:string}|null
 */
function hubServicePrimary(string $serviceSlug, string $alt): ?array
{
    $map = hubServicePrimaryMap();
    if (!isset($map[$serviceSlug])) {
        return null;
    }
    $item = $map[$serviceSlug];
    $item['alt'] = $alt;
    return $item;
}

/**
 * Primary, gallery and product cards for one town page. Gallery and cards
 * skip the primary src so the hero is not repeated on the same page.
 *
 * @return array{primary:?array,gallery:list<array{src:string,alt:string,fit:string}>,cards:list<array{title:string,alt:string,image:string,href:string,price:string}>}
 */
function hubPageVisuals(string $family, string $areaName, int $galleryLimit = 6, int $cardLimit = 4, string $alt = '', int $shift = 0): array
{
    $primary = hubPrimaryForArea($family, $areaName, $alt, $shift);
    $pool = hubDistinctImagePool($family);
    $count = count($pool);
    $gallery = [];
    $used = [];
    if ($primary !== null) {
        $used[hubImageKey($primary['src'])] = true;
    }
    if ($count > 0 && $galleryLimit > 0) {
        $start = (hubAreaSiblingIndex($areaName) + $shift + 1) % $count;
        for ($step = 0; $step < $count && count($gallery) < $galleryLimit; $step++) {
            $item = $pool[($start + $step) % $count];
            $key = hubImageKey($item['src']);
            if (isset($used[$key])) {
                continue;
            }
            $used[$key] = true;
            $gallery[] = $item;
        }
    }

    $cards = [];
    $productFamily = ($family === 'areas' || $family === 'works') ? 'featured' : $family;
    foreach (hubProductsForFamily($productFamily) as $product) {
        if (count($cards) >= $cardLimit) {
            break;
        }
        $key = hubImageKey($product['image']);
        if (isset($used[$key])) {
            continue;
        }
        $used[$key] = true;
        $cards[] = $product;
    }

    return ['primary' => $primary, 'gallery' => $gallery, 'cards' => $cards];
}

/**
 * @return array{src:string,alt:string}|null
 */
function hubHeroImage(string $serviceSlug, string $alt): ?array
{
    foreach (['.jpg', '.png', '-photo.jpg'] as $suffix) {
        $rel = '/assets/images/services/' . $serviceSlug . $suffix;
        $src = hubLocalFileUrl($rel);
        if ($src !== null) {
            return ['src' => $src, 'alt' => $alt];
        }
    }
    $fallback = hubLocalFileUrl('/assets/images/services/fire-alarms.jpg');
    if ($fallback === null) {
        return null;
    }
    return ['src' => $fallback, 'alt' => $alt];
}

/**
 * @return list<array{title:string,alt:string,image:string,href:string,price:string}>
 */
function hubProductsForFamily(string $family): array
{
    $all = hubVisualCatalog()['products'] ?? [];
    $rows = $all[$family] ?? [];
    if (!is_array($rows) || $rows === []) {
        $rows = $all['featured'] ?? [];
    }
    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $image = (string) ($row['image'] ?? '');
        $title = trim((string) ($row['title'] ?? ''));
        if ($image === '' || $title === '' || !preg_match('#^https://#', $image)) {
            continue;
        }
        $out[] = [
            'title' => $title,
            'alt' => trim((string) ($row['alt'] ?? $title)) ?: $title,
            'image' => $image,
            'href' => (string) ($row['href'] ?? 'https://shop.icomplypropertyservices.co.uk'),
            'price' => trim((string) ($row['price'] ?? 'POA')) ?: 'POA',
        ];
    }
    return $out;
}

/**
 * @param list<array{title:string,alt:string,image:string,href:string,price:string}> $products
 * @return list<array{title:string,alt:string,image:string,href:string,price:string}>
 */
function hubRotateProducts(array $products, string $seed): array
{
    $count = count($products);
    if ($count <= 1) {
        return $products;
    }
    $offset = hubRotateOffset($seed, $count);
    return array_merge(array_slice($products, $offset), array_slice($products, 0, $offset));
}

/**
 * Gallery stills and product cards that do not share an image.
 * Local files are included only when they exist and differ from the hero photo.
 *
 * @return array{gallery:list<array{src:string,alt:string,fit:string}>,cards:list<array{title:string,alt:string,image:string,href:string,price:string}>}
 */
function hubVisualSplit(string $seed, int $galleryLimit = 6, int $cardLimit = 4, string $serviceSlug = ''): array
{
    $family = $serviceSlug !== '' ? hubFamilyForService($serviceSlug) : 'featured';
    $productFamily = $family === 'works' ? 'featured' : $family;
    $products = hubRotateProducts(hubProductsForFamily($productFamily), $seed);

    $gallery = [];
    $seenSrc = [];
    $seenHash = [];
    $addLocal = static function (string $rel, string $alt) use (&$gallery, &$seenSrc, &$seenHash, $galleryLimit): void {
        if (count($gallery) >= $galleryLimit) {
            return;
        }
        $src = hubLocalFileUrl($rel);
        if ($src === null || isset($seenSrc[$src])) {
            return;
        }
        $hash = hubFileHash($rel);
        if ($hash !== '' && isset($seenHash[$hash])) {
            return;
        }
        $seenSrc[$src] = true;
        if ($hash !== '') {
            $seenHash[$hash] = true;
        }
        $gallery[] = ['src' => $src, 'alt' => $alt, 'fit' => 'cover'];
    };

    if ($serviceSlug !== '') {
        $heroHash = hubFileHash('/assets/images/services/' . $serviceSlug . '.jpg');
        if ($heroHash !== '') {
            $seenHash[$heroHash] = true;
        }
        $addLocal('/assets/images/services/' . $serviceSlug . '-photo.jpg', 'Site photo');
        if (function_exists('getKeywordImages')) {
            foreach (getKeywordImages($serviceSlug) as $kw) {
                $kw = preg_replace('/[^a-z0-9\-]/', '', (string) $kw) ?? '';
                if ($kw === '') {
                    continue;
                }
                $addLocal('/assets/images/keywords/' . $kw . '.jpg', ucwords(str_replace('-', ' ', $kw)));
            }
        }
    }

    $cardStart = 0;
    foreach ($products as $i => $product) {
        if (count($gallery) >= $galleryLimit) {
            $cardStart = $i;
            break;
        }
        if (isset($seenSrc[$product['image']])) {
            continue;
        }
        $seenSrc[$product['image']] = true;
        $gallery[] = ['src' => $product['image'], 'alt' => $product['alt'], 'fit' => 'contain'];
        $cardStart = $i + 1;
    }

    if (count($gallery) < $galleryLimit) {
        $locals = hubVisualCatalog()['local_unique'] ?? [];
        if (is_array($locals)) {
            foreach (hubRotate($locals, $seed . '|local', count($locals)) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $addLocal((string) ($row['src'] ?? ''), (string) ($row['alt'] ?? 'Property services'));
            }
        }
    }

    $cards = [];
    for ($i = $cardStart; $i < count($products) && count($cards) < $cardLimit; $i++) {
        $cards[] = $products[$i];
    }
    if ($family === 'works' && count($cards) < $cardLimit) {
        foreach (hubProductsForFamily('featured') as $product) {
            if (count($cards) >= $cardLimit) {
                break;
            }
            if (isset($seenSrc[$product['image']])) {
                continue;
            }
            $cards[] = $product;
        }
    }

    return ['gallery' => $gallery, 'cards' => $cards];
}

function hubGallerySectionHtml(string $kicker, string $title, string $lead, array $items): string
{
    if ($items === []) {
        return '';
    }
    $html = '<section class="bg-white border-y">'
        . '<div class="max-w-7xl mx-auto px-6 py-16">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">' . htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8') . '</div>'
        . '<h2 class="text-3xl font-semibold tracking-tight text-black mt-2">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>'
        . '<p class="mt-2 text-zinc-600 max-w-2xl">' . htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-4">';
    foreach ($items as $item) {
        $src = (string) ($item['src'] ?? '');
        $alt = (string) ($item['alt'] ?? '');
        if ($src === '') {
            continue;
        }
        $fit = (($item['fit'] ?? 'cover') === 'contain') ? 'object-contain bg-white' : 'object-cover';
        $html .= '<figure class="rounded-3xl overflow-hidden border bg-zinc-100">'
            . '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="w-full h-44 md:h-52 ' . $fit . '" loading="lazy" width="800" height="520">'
            . '</figure>';
    }
    $html .= '</div></div></section>';
    return $html;
}

function hubProductSectionHtml(string $kicker, string $title, string $lead, array $cards): string
{
    if ($cards === []) {
        return '';
    }
    $shop = htmlspecialchars(url('/shop/index.php'), ENT_QUOTES, 'UTF-8');
    $html = '<section class="max-w-7xl mx-auto px-6 py-16">'
        . '<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">'
        . '<div>'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">' . htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8') . '</div>'
        . '<h2 class="text-3xl font-semibold tracking-tight text-black mt-2">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>'
        . '<p class="mt-2 text-zinc-600 max-w-2xl">' . htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div>'
        . '<a href="' . $shop . '" class="text-sm font-semibold text-[#ff6b00]">Trade shop →</a>'
        . '</div>'
        . '<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">';
    foreach ($cards as $card) {
        $src = (string) ($card['image'] ?? '');
        $titleText = (string) ($card['title'] ?? 'Product');
        $alt = (string) ($card['alt'] ?? $titleText);
        $href = (string) ($card['href'] ?? $shop);
        $price = (string) ($card['price'] ?? 'POA');
        if ($src === '') {
            continue;
        }
        $html .= '<article class="group bg-white border border-zinc-200 rounded-3xl overflow-hidden hover:border-[#ff6b00] hover:shadow-lg transition flex flex-col">'
            . '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="block bg-white overflow-hidden" target="_blank" rel="noopener">'
            . '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="w-full h-44 object-contain bg-white group-hover:scale-105 transition duration-300" loading="lazy" width="480" height="360">'
            . '</a>'
            . '<div class="p-5 flex flex-col flex-1">'
            . '<div class="text-xs font-semibold text-[#ff6b00] mb-1">' . htmlspecialchars($price, ENT_QUOTES, 'UTF-8') . ' <span class="text-zinc-400 font-normal">supply</span></div>'
            . '<h3 class="font-semibold text-black leading-snug"><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="hover:text-[#ff6b00]" target="_blank" rel="noopener">' . htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8') . '</a></h3>'
            . '<p class="mt-2 text-sm text-zinc-600 flex-1">Installation is quoted separately.</p>'
            . '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="mt-4 inline-flex text-sm font-semibold text-[#ff6b00]" target="_blank" rel="noopener">View product →</a>'
            . '</div></article>';
    }
    $html .= '</div></section>';
    return $html;
}

/**
 * @return list<array{src:string,alt:string}>
 */
function hubAreaHeroTiles(int $limit = 6): array
{
    $locals = hubVisualCatalog()['local_unique'] ?? [];
    $tiles = [];
    if (!is_array($locals)) {
        return $tiles;
    }
    foreach (array_slice($locals, 0, $limit) as $row) {
        if (!is_array($row)) {
            continue;
        }
        $src = hubLocalFileUrl((string) ($row['src'] ?? ''));
        if ($src === null) {
            continue;
        }
        $tiles[] = ['src' => $src, 'alt' => (string) ($row['alt'] ?? 'Property services')];
    }
    return $tiles;
}
