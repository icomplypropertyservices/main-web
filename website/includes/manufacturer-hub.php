<?php
/**
 * Manufacturer hub helpers: product-line labels, logo marks,
 * and manufacturer × area pages (one route per brand per town).
 */
declare(strict_types=1);

/**
 * Photos we already have, keyed by catalogue slug.
 *
 * @return array<string,string>
 */
function manufacturerLogoFileMap(): array
{
    return [
        'advanced-electronics' => 'advanced-fire-panel.jpg',
        'aiphone' => 'aiphone.jpg',
        'apollo' => 'apollo-fire.jpg',
        'axis-communications' => 'axis-camera.jpg',
        'c-tec' => 'c-tec.jpg',
        'dahua' => 'dahua-cctv.jpg',
        'fermax' => 'fermax-door-entry.jpg',
        'hager' => 'hager-consumer-unit.jpg',
        'hikvision' => 'hikvision.jpg',
        'hochiki' => 'hochiki.jpg',
        'kentec' => 'kentec.jpg',
        'myenergi' => 'myenergi-ev-charger.jpg',
        'paxton' => 'paxton.jpg',
        'rolec-ev' => 'rolec-ev.jpg',
        'salto-systems' => 'salto-access.jpg',
        'schneider-electric' => 'schneider-electrical.jpg',
        'texecom' => 'texecom.jpg',
        'videx' => 'videx.jpg',
        'worcester-bosch' => 'worcester-bosch.jpg',
    ];
}

function manufacturerLogoUrl(string $slug): string
{
    $slug = areaSlug($slug);
    $map = manufacturerLogoFileMap();
    $file = $map[$slug] ?? ($slug . '.jpg');
    $rel = '/assets/images/manufacturers/' . $file;
    if (is_file(SITE_ROOT . $rel)) {
        return url($rel);
    }
    return '';
}

function manufacturerLogoHtml(string $name, string $slug, string $class = 'w-10 h-10'): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $src = manufacturerLogoUrl($slug);
    if ($src !== '') {
        return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . $safeName . ' logo" class="' . $class . ' rounded-xl object-cover bg-white border" loading="lazy" width="40" height="40">';
    }
    $letters = '';
    foreach (preg_split('/\s+/', trim($name)) ?: [] as $part) {
        $letters .= strtoupper(substr((string)$part, 0, 1));
        if (strlen($letters) >= 3) {
            break;
        }
    }
    if ($letters === '') {
        $letters = '•';
    }
    return '<span class="' . $class . ' inline-flex items-center justify-center rounded-xl bg-white text-[#0B1F3A] border text-[10px] font-bold tracking-wide shrink-0" role="img" aria-label="' . $safeName . ' logo">' . htmlspecialchars($letters, ENT_QUOTES, 'UTF-8') . '</span>';
}

if (!function_exists('manufacturerProductLines')) {
/**
 * Named product lines for a brand. Prices are not part of a line.
 *
 * @param array<string,mixed> $entry
 * @return list<array{name:string,href:string,blurb:string}>
 */
function manufacturerProductLines(array $entry): array
{
    $lines = [];
    foreach ($entry['lines'] ?? [] as $line) {
        if (is_string($line)) {
            $line = ['name' => $line];
        }
        if (!is_array($line)) {
            continue;
        }
        $name = trim((string)($line['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $lines[] = [
            'name' => $name,
            'href' => (string)($line['href'] ?? ''),
            'blurb' => (string)($line['blurb'] ?? ''),
        ];
    }
    if ($lines !== []) {
        if (!empty($entry['partner']) && (string)($entry['slug'] ?? '') === 'came') {
            $pack = url('/products.php') . '#barrier-packs';
            foreach ($lines as $i => $line) {
                if ($line['href'] === '') {
                    $lines[$i]['href'] = $pack;
                }
            }
        }
        return $lines;
    }
    foreach ($entry['products'] ?? [] as $product) {
        if (!is_array($product)) {
            continue;
        }
        $title = trim((string)($product['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $lines[] = [
            'name' => $title,
            'href' => '',
            'blurb' => (string)($product['blurb'] ?? ''),
        ];
        if (count($lines) >= 3) {
            break;
        }
    }
    return $lines;
}
} // function_exists manufacturerProductLines

if (!function_exists('manufacturerHubProductLinesHtml')) {
function manufacturerHubProductLinesHtml(array $entry, int $limit = 4): string
{
    $html = '';
    $n = 0;
    foreach (manufacturerProductLines($entry) as $line) {
        if ($limit > 0 && $n >= $limit) {
            break;
        }
        $n++;
        $label = htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8');
        if ($line['href'] !== '') {
            $html .= '<a href="' . htmlspecialchars($line['href'], ENT_QUOTES, 'UTF-8') . '" class="px-2 py-0.5 rounded-full bg-zinc-50 border text-[11px] hover:border-[#ff6b00]">' . $label . '</a>';
        } else {
            $html .= '<span class="px-2 py-0.5 rounded-full bg-zinc-50 border text-[11px] text-zinc-700">' . $label . '</span>';
        }
    }
    return $html;
}
} // function_exists manufacturerHubProductLinesHtml

/**
 * @return list<string> paths /pages/manufacturers/{brand}/{area}
 */
function manufacturerAreaRoutes(): array
{
    $routes = [];
    $areas = getAreas();
    foreach (getManufacturerCatalog() as $slug => $entry) {
        if (icomplyManufacturerIsDenied((string)$slug, (string)($entry['name'] ?? ''))) {
            continue;
        }
        foreach ($areas as $area) {
            $routes[] = '/pages/manufacturers/' . $slug . '/' . areaSlug((string)$area);
        }
    }
    return $routes;
}

function renderManufacturerAreaPage(string $mfrSlug, string $areaSlugVal): void
{
    require_once SITE_ROOT . '/includes/share.php';
    require_once SITE_ROOT . '/includes/manufacturer-kits.php';

    $entry = getManufacturerBySlug($mfrSlug);
    $area = areaFromSlug($areaSlugVal);
    if ($entry === null || $area === null || icomplyManufacturerIsDenied($mfrSlug, (string)($entry['name'] ?? ''))) {
        http_response_code(404);
        echo 'Manufacturer area not found';
        icomplyRequestExit();
        return;
    }

    $name = (string)$entry['name'];
    $slug = (string)($entry['slug'] ?? $mfrSlug);
    $nationwide = !empty($entry['nationwide']) || !empty($entry['barrier']);
    $partner = !empty($entry['partner']);
    $lines = manufacturerProductLines($entry);
    $pageTitle = $name . ' in ' . $area . ' | Install & trade kits';
    $metaDesc = $name . ' install, service and trade kits in ' . $area
        . ($nationwide ? '. Barriers and this brand are covered nationwide from our Stockport base.' : '. North West engineers, quote on request.')
        . ' Installation is POA.';
    $metaKeywords = $name . ' ' . $area . ', ' . $name . ' install, ' . $name . ' service';
    $canonicalUrl = url('/pages/manufacturers/' . $slug . '/' . areaSlug($area));
    $brandUrl = url('/pages/manufacturers/' . $slug . '.php');
    $primary = $entry['services'][0] ?? 'fire-alarms';
    $ogImage = manufacturerLogoUrl($slug);
    if ($ogImage === '') {
        $ogImage = manufacturerImageUrl($slug, $primary);
    }

    require SITE_ROOT . '/includes/header.php';
    ?>
<section class="page-hero bg-[#0B1F3A] text-white">
    <div class="max-w-7xl mx-auto px-6 py-14">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <a href="<?= url('/pages/manufacturers/index.php') ?>" class="hover:text-white">Manufacturers</a>
            <span>/</span>
            <a href="<?= htmlspecialchars($brandUrl, ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></a>
            <span>/</span>
            <span class="text-white/80"><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
        <div class="flex items-start gap-4">
            <?= manufacturerLogoHtml($name, $slug, 'w-16 h-16 text-sm') ?>
            <div>
                <?php if ($partner): ?>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-2">Partner brand</div>
                <?php elseif ($nationwide): ?>
                <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold mb-2">Nationwide</div>
                <?php endif; ?>
                <h1 class="text-4xl md:text-5xl font-semibold tracking-tighter"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> <span class="text-[#ff6b00]">in <?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></span></h1>
                <p class="mt-4 text-white/80 max-w-2xl"><?= $nationwide
                    ? 'Barriers are a nationwide priority. This town page sits on the manufacturer × every-area matrix.'
                    : 'Local install and service page on the manufacturer × every-area matrix.' ?> Supply is quoted from real kits where we list them. Installation is POA.</p>
            </div>
        </div>
    </div>
</section>
<section class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-semibold text-black">Product lines</h2>
    <div class="mt-4 flex flex-wrap gap-2">
        <?php if ($lines === []): ?>
        <p class="text-zinc-600">Tell us the model. We quote this brand in <?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?> without a placeholder price.</p>
        <?php else: foreach ($lines as $line): ?>
            <?php if ($line['href'] !== ''): ?>
            <a href="<?= htmlspecialchars($line['href'], ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00]"><?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php else: ?>
            <span class="px-4 py-2 bg-white border rounded-full text-sm"><?= htmlspecialchars($line['name'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        <?php endforeach; endif; ?>
    </div>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="<?= htmlspecialchars($brandUrl, ENT_QUOTES, 'UTF-8') ?>#quote" class="px-6 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold">Quote for <?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></a>
        <a href="<?= htmlspecialchars($brandUrl, ENT_QUOTES, 'UTF-8') ?>#products" class="px-6 py-3 rounded-2xl border font-semibold">Brand kits</a>
    </div>
</section>
<?php
    echo shareButtonsHtml($pageTitle, $metaDesc);
    require SITE_ROOT . '/includes/footer.php';
}
