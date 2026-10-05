<?php
/**
 * Barrier manufacturers — catalog merge, partner panel, brand grid, CAME lane builder.
 * Does not replace includes/kit-wizard.php (AOV / trade kit builders live there).
 * Tunstall is never a barrier brand.
 */
declare(strict_types=1);

function barrierH(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** @return array<string,mixed> */
function barrierManufacturersData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $file = SITE_ROOT . '/data/barrier-manufacturers.json';
    $decoded = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
    $data = is_array($decoded) ? $decoded : [];
    return $data;
}

function barrierBrandIsExcluded(array $brand): bool
{
    $name = strtolower(trim((string)($brand['name'] ?? '')));
    $slug = strtolower(trim((string)($brand['slug'] ?? '')));
    if ($name === 'tunstall' || $slug === 'tunstall') {
        return true;
    }
    $exclude = barrierManufacturersData()['exclude'] ?? [];
    if (!is_array($exclude)) {
        return false;
    }
    foreach ($exclude as $item) {
        $key = strtolower(trim((string)$item));
        if ($key !== '' && ($key === $name || $key === $slug)) {
            return true;
        }
    }
    return false;
}

/** @return list<array<string,mixed>> */
function barrierManufacturerRecords(): array
{
    $brands = barrierManufacturersData()['brands'] ?? [];
    if (!is_array($brands)) {
        return [];
    }
    $out = [];
    foreach ($brands as $brand) {
        if (!is_array($brand) || barrierBrandIsExcluded($brand)) {
            continue;
        }
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower((string)($brand['slug'] ?? '')));
        $name = trim((string)($brand['name'] ?? ''));
        if ($slug === '' || $name === '') {
            continue;
        }
        $brand['slug'] = $slug;
        $brand['name'] = $name;
        $out[] = $brand;
    }
    usort($out, static function (array $a, array $b): int {
        if (!empty($a['partner']) !== !empty($b['partner'])) {
            return !empty($a['partner']) ? -1 : 1;
        }
        return strcasecmp((string)$a['name'], (string)$b['name']);
    });
    return $out;
}

/** @return list<string> */
function barrierManufacturerNames(): array
{
    $names = [];
    foreach (barrierManufacturerRecords() as $brand) {
        $names[] = (string)$brand['name'];
    }
    return $names;
}

function isBarrierManufacturerSlug(string $slug): bool
{
    $slug = strtolower($slug);
    foreach (barrierManufacturerRecords() as $brand) {
        if ((string)$brand['slug'] === $slug) {
            return true;
        }
    }
    return false;
}

/** @return array<string,mixed>|null */
function barrierManufacturerBySlug(string $slug): ?array
{
    $slug = strtolower($slug);
    foreach (barrierManufacturerRecords() as $brand) {
        if ((string)$brand['slug'] === $slug) {
            return $brand;
        }
    }
    return null;
}

function manufacturerSlugForLabel(string $name): string
{
    foreach (barrierManufacturerRecords() as $brand) {
        if (strcasecmp((string)$brand['name'], $name) === 0) {
            return (string)$brand['slug'];
        }
    }
    if (function_exists('getManufacturerCatalog')) {
        foreach (getManufacturerCatalog() as $slug => $entry) {
            if (strcasecmp((string)($entry['name'] ?? ''), $name) === 0) {
                return (string)$slug;
            }
        }
    }
    return function_exists('manufacturerSlugFromName') ? manufacturerSlugFromName($name) : strtolower($name);
}

function barrierLogoUrl(array $brand): string
{
    if (!empty($brand['partner']) && !empty($brand['partner_image'])) {
        return (string)$brand['partner_image'];
    }
    $slug = (string)($brand['slug'] ?? '');
    $rel = '/assets/images/manufacturers/' . $slug . '-logo.svg';
    if (is_file(SITE_ROOT . $rel)) {
        return function_exists('assetUrl') ? assetUrl($rel) : $rel;
    }
    return function_exists('assetUrl') ? assetUrl('/assets/images/services/barriers.svg') : '/assets/images/services/barriers.svg';
}

function barrierArtUrl(array $brand): string
{
    $slug = (string)($brand['slug'] ?? '');
    $rel = '/assets/images/manufacturers/' . $slug . '-barrier.svg';
    if (is_file(SITE_ROOT . $rel)) {
        return function_exists('assetUrl') ? assetUrl($rel) : $rel;
    }
    return barrierLogoUrl($brand);
}

function barrierLineImage(array $brand, array $line): string
{
    $image = trim((string)($line['image'] ?? ''));
    if ($image !== '') {
        return $image;
    }
    return barrierArtUrl($brand);
}

/** @param array<string,array<string,mixed>> $catalog */
function barrierMergeManufacturerCatalog(array $catalog): array
{
    foreach (barrierManufacturerCatalogEntries() as $slug => $entry) {
        if (!isset($catalog[$slug])) {
            $catalog[$slug] = $entry;
            continue;
        }
        $services = $catalog[$slug]['services'] ?? [];
        if (!in_array('barriers', $services, true)) {
            $services[] = 'barriers';
        }
        $catalog[$slug]['services'] = $services;
        $catalog[$slug]['product_lines'] = $entry['product_lines'];
        if (!empty($entry['partner'])) {
            $catalog[$slug]['partner'] = true;
            $catalog[$slug]['partner_label'] = $entry['partner_label'];
            $catalog[$slug]['partner_image'] = $entry['partner_image'];
            $catalog[$slug]['featured'] = true;
            $catalog[$slug]['blurb'] = $entry['blurb'];
        }
    }
    return $catalog;
}

/** @return array<string,array<string,mixed>> */
function barrierManufacturerCatalogEntries(): array
{
    $out = [];
    foreach (barrierManufacturerRecords() as $brand) {
        $slug = (string)$brand['slug'];
        $name = (string)$brand['name'];
        $lines = is_array($brand['lines'] ?? null) ? $brand['lines'] : [];
        $products = [];
        foreach ($lines as $i => $line) {
            if (!is_array($line)) {
                continue;
            }
            $products[] = [
                'id' => $slug . '-line-' . ($i + 1),
                'title' => $name . ' ' . (string)($line['name'] ?? 'barrier'),
                'blurb' => (string)($line['summary'] ?? ''),
                'price' => 'POA',
                'handle' => $slug . '-line-' . ($i + 1),
                'shopify_product_id' => '',
                'image' => barrierLineImage($brand, $line),
                'badge' => !empty($brand['partner']) ? 'CAME partner' : 'Barrier',
            ];
        }
        $out[$slug] = [
            'name' => $name,
            'slug' => $slug,
            'services' => ['barriers'],
            'blurb' => (string)($brand['blurb'] ?? ''),
            'seo_title' => $name . ' barriers | North West',
            'seo_desc' => 'Install and service ' . $name . ' rising-arm and parking barriers. iComply, Stockport. Supply quoted after survey. Install POA.',
            'seo_keywords' => $name . ' barrier, ' . $name . ' rising arm, parking barrier, ' . $name . ' North West, ' . $name . ' Manchester',
            'products' => $products,
            'product_lines' => $lines,
            'featured' => !empty($brand['partner']),
            'partner' => !empty($brand['partner']),
            'partner_label' => (string)($brand['partner_label'] ?? ''),
            'partner_image' => (string)($brand['partner_image'] ?? ''),
        ];
    }
    return $out;
}

/**
 * Published CAME 5m supply packs already on the products hub. Install stays POA.
 * No other combination has a catalogue price.
 *
 * @return array<string,array{sku:string,price:string,label:string}>
 */
function barrierCamePublishedPacks(): array
{
    return [
        'std' => ['sku' => 'BAR-5M-STD', 'price' => '£5,850.00', 'label' => 'Standard 5m barrier pack'],
        'videx' => ['sku' => 'BAR-5M-VIDEX', 'price' => '£7,441.83', 'label' => '5m barrier + Videx'],
        'paxton' => ['sku' => 'BAR-5M-PAXTON', 'price' => '£8,375.45', 'label' => '5m barrier + Paxton'],
        'gsm' => ['sku' => 'BAR-5M-GSM', 'price' => '£7,393.18', 'label' => '5m barrier + GSM'],
        'allin' => ['sku' => 'BAR-5M-ALLIN', 'price' => '£5,199.99', 'label' => '5m barrier all-in'],
    ];
}

function camePartnerPanelHtml(string $context = 'barriers'): string
{
    $came = barrierManufacturerBySlug('came');
    if ($came === null) {
        return '';
    }
    $phone = defined('PHONE') ? (string)PHONE : '07517806082';
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', $phone);
    $img = barrierH(barrierLogoUrl($came));
    $mfr = barrierH(url('/pages/manufacturers/came'));
    $hub = barrierH(url('/pages/services/barriers'));
    $wizard = barrierH(url('/pages/services/barriers') . '#came-lane-builder');
    $note = match ($context) {
        'access-control' => 'Door access and a vehicle lane are often the same project. Barrier supply on those jobs is through our CAME partnership.',
        'manufacturers' => 'CAME is the barrier partner in this directory. Other barrier makers are listed with their own product lines.',
        'products' => 'The 5m packs below are published CAME supply prices. Installation is POA. Other lengths and other manufacturers are quoted after survey.',
        default => 'Barrier projects lead with CAME. Other manufacturers on this page are linked with their own product lines.',
    };
    return '<section class="max-w-7xl mx-auto px-6 py-10" aria-label="CAME partner">'
        . '<div class="grid lg:grid-cols-5 gap-8 items-center bg-white border-2 border-[#ff6b00] rounded-3xl p-6 md:p-8">'
        . '<div class="lg:col-span-2 bg-[#0B1F3A] rounded-2xl p-4">'
        . '<img src="' . $img . '" alt="CAME barrier range. iComply is a CAME partner." class="w-full h-48 object-contain bg-white rounded-xl" width="640" height="192" loading="lazy">'
        . '<p class="mt-3 text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold">CAME partner</p>'
        . '</div>'
        . '<div class="lg:col-span-3">'
        . '<h2 class="text-2xl md:text-3xl font-semibold tracking-tight text-black">Authorised CAME partner</h2>'
        . '<p class="mt-3 text-zinc-700 leading-relaxed">iComply is a CAME partner for the rising-arm barriers we supply and install. ' . barrierH($note) . ' We do not publish a partner or accreditation number.</p>'
        . '<div class="mt-5 flex flex-wrap gap-3">'
        . '<a class="px-5 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold" href="' . $wizard . '">CAME lane builder</a>'
        . '<a class="px-5 py-3 rounded-2xl border border-[#0B1F3A] font-semibold" href="' . $mfr . '">CAME manufacturer</a>'
        . '<a class="px-5 py-3 rounded-2xl border border-zinc-300 font-semibold" href="' . barrierH($phoneHref) . '">' . barrierH($phone) . '</a>'
        . ($context === 'barriers' ? '' : '<a class="px-5 py-3 rounded-2xl border border-zinc-300 font-semibold" href="' . $hub . '">Barriers hub</a>')
        . '</div></div></div></section>';
}

function barrierBrandGridHtml(string $areaName = ''): string
{
    $areaSlugVal = $areaName !== '' && function_exists('areaSlug') ? areaSlug($areaName) : '';
    $cards = '';
    foreach (barrierManufacturerRecords() as $brand) {
        $slug = (string)$brand['slug'];
        $name = (string)$brand['name'];
        $partner = !empty($brand['partner']);
        $href = url('/pages/manufacturers/' . $slug);
        if ($areaSlugVal !== '') {
            $href = url('/pages/manufacturers/' . $slug . '/' . $areaSlugVal);
        }
        $lines = '';
        foreach ((array)($brand['lines'] ?? []) as $line) {
            if (!is_array($line)) {
                continue;
            }
            $lineName = (string)($line['name'] ?? '');
            if ($lineName === '') {
                continue;
            }
            $lines .= '<li class="text-sm text-zinc-700"><span class="text-[#ff6b00] font-semibold">' . barrierH($lineName) . '</span>'
                . ' — ' . barrierH((string)($line['summary'] ?? '')) . '</li>';
        }
        $badge = $partner
            ? '<span class="inline-flex px-2.5 py-1 rounded-full bg-[#0B1F3A] text-white text-xs font-semibold tracking-wide">CAME partner</span>'
            : '<span class="inline-flex px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-700 text-xs font-semibold">Manufacturer</span>';
        $logoClass = $partner ? 'h-20 object-contain bg-white' : 'h-20 object-contain';
        $photo = $partner
            ? barrierLineImage($brand, (array)($brand['lines'][0] ?? []))
            : barrierArtUrl($brand);
        $photoClass = $partner
            ? 'mt-4 w-full h-36 object-contain rounded-xl bg-white border'
            : 'mt-4 w-full h-36 object-contain rounded-xl bg-[#0B1F3A]';
        $cta = $areaName !== '' ? ($name . ' in ' . $areaName) : ($name . ' manufacturer');
        $cards .= '<article class="bg-white border rounded-3xl overflow-hidden flex flex-col' . ($partner ? ' border-[#ff6b00] border-2' : '') . '">'
            . '<a href="' . barrierH($href) . '" class="block ' . ($partner ? 'bg-white' : 'bg-[#0B1F3A]') . '">'
            . '<img src="' . barrierH(barrierLogoUrl($brand)) . '" alt="' . barrierH($partner ? 'CAME partner range' : ($name . ' wordmark')) . '" class="w-full ' . $logoClass . ' p-4" width="320" height="80" loading="lazy">'
            . '</a>'
            . '<div class="p-5 flex flex-col flex-1">'
            . '<div class="flex items-start justify-between gap-2">'
            . '<h3 class="font-semibold text-lg text-black">' . barrierH($name) . '</h3>' . $badge
            . '</div>'
            . '<ul class="mt-3 space-y-1.5 flex-1">' . $lines . '</ul>'
            . '<img src="' . barrierH($photo) . '" alt="' . barrierH($name . ' barrier') . '" class="' . $photoClass . '" width="320" height="144" loading="lazy">'
            . '<a class="mt-4 text-sm font-semibold text-[#ff6b00]" href="' . barrierH($href) . '">'
            . barrierH($cta) . ' →</a>'
            . '</div></article>';
    }
    $heading = $areaName !== ''
        ? 'Barrier manufacturers in ' . $areaName
        : 'Barrier manufacturers';
    $intro = $areaName !== ''
        ? 'Every rising-arm and parking barrier maker we install is linked for ' . $areaName . '. CAME is the partner. Tunstall is not a barrier brand.'
        : 'Every rising-arm and parking barrier maker we install is on this page. CAME is the partner. Open a manufacturer for the product lines, or use the CAME lane builder. Tunstall is not listed.';
    return '<section class="max-w-7xl mx-auto px-6 py-12" id="barrier-manufacturers">'
        . '<div class="mb-8">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Manufacturers</div>'
        . '<h2 class="text-3xl font-semibold tracking-tight text-black mt-2">' . barrierH($heading) . '</h2>'
        . '<p class="mt-2 text-zinc-600 max-w-3xl">' . barrierH($intro) . '</p>'
        . '</div>'
        . '<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">' . $cards . '</div>'
        . '</section>';
}

function cameLaneWizardHtml(): string
{
    $came = barrierManufacturerBySlug('came');
    if ($came === null) {
        return '';
    }
    $phone = defined('PHONE') ? (string)PHONE : '07517806082';
    $packs = barrierCamePublishedPacks();
    $packJson = barrierH(json_encode($packs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $lines = '';
    foreach ((array)($came['lines'] ?? []) as $line) {
        if (!is_array($line)) {
            continue;
        }
        $id = strtolower(preg_replace('/[^a-z0-9]+/', '-', (string)($line['name'] ?? 'line')) ?? 'line');
        $lines .= '<label class="flex gap-3 p-3 border rounded-2xl bg-white cursor-pointer">'
            . '<input type="radio" name="came_line" value="' . barrierH($id) . '" class="mt-1" ' . ($lines === '' ? 'checked' : '') . '>'
            . '<span><span class="font-semibold text-black">' . barrierH((string)($line['name'] ?? '')) . '</span>'
            . '<span class="block text-sm text-zinc-600">' . barrierH((string)($line['summary'] ?? '')) . '</span></span>'
            . '</label>';
    }
    $addons = '';
    foreach ($packs as $key => $pack) {
        $addons .= '<label class="flex gap-3 p-3 border rounded-2xl bg-white cursor-pointer">'
            . '<input type="radio" name="came_addon" value="' . barrierH($key) . '" class="mt-1" ' . ($key === 'std' ? 'checked' : '') . '>'
            . '<span><span class="font-semibold text-black">' . barrierH($pack['label']) . '</span>'
            . '<span class="block text-xs text-zinc-500">' . barrierH($pack['sku']) . '</span></span></label>';
    }
    return '<section id="came-lane-builder" class="max-w-7xl mx-auto px-6 py-12">'
        . '<div class="bg-[#0B1F3A] text-white rounded-3xl p-6 md:p-10">'
        . '<p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">CAME partner · lane builder</p>'
        . '<h2 class="text-3xl font-semibold tracking-tight mt-2">Build a CAME barrier enquiry</h2>'
        . '<p class="mt-3 text-white/75 max-w-3xl">Choose the Gard line, boom length and access add-on. A supply price is shown only for the published 5m CAME packs. Every other combination is quoted after survey. Installation is always POA. This builder is separate from the AOV and trade kit-wizard templates.</p>'
        . '<form class="mt-8 grid lg:grid-cols-2 gap-8" action="' . barrierH(url('/contact')) . '" method="get">'
        . '<input type="hidden" name="service" value="CAME barrier — partner enquiry">'
        . '<div class="space-y-3"><h3 class="font-semibold">1. Product line</h3>' . $lines . '</div>'
        . '<div class="space-y-6">'
        . '<div><h3 class="font-semibold mb-3">2. Boom length</h3>'
        . '<div class="flex flex-wrap gap-2">'
        . '<label class="px-4 py-2 bg-white/10 rounded-full"><input type="radio" name="boom" value="4m"> 4m</label>'
        . '<label class="px-4 py-2 bg-white/10 rounded-full"><input type="radio" name="boom" value="5m" checked> 5m</label>'
        . '<label class="px-4 py-2 bg-white/10 rounded-full"><input type="radio" name="boom" value="6m"> 6m</label>'
        . '<label class="px-4 py-2 bg-white/10 rounded-full"><input type="radio" name="boom" value="8m"> 6–8m survey</label>'
        . '</div></div>'
        . '<div><h3 class="font-semibold mb-3">3. Access add-on</h3><div class="space-y-2">' . $addons . '</div></div>'
        . '<div class="bg-white text-black rounded-2xl p-5" data-came-quote data-packs="' . $packJson . '">'
        . '<p class="text-xs uppercase tracking-wider text-[#ff6b00] font-semibold">Supply price</p>'
        . '<p class="mt-2 text-2xl font-semibold" data-came-price>£5,850.00</p>'
        . '<p class="text-sm text-zinc-600" data-came-note>Published 5m CAME supply price, ex VAT. Installation POA.</p>'
        . '<p class="mt-2 text-sm">Call <a class="font-semibold text-[#ff6b00]" href="tel:' . barrierH(preg_replace('/\s+/', '', $phone) ?? '') . '">' . barrierH($phone) . '</a></p>'
        . '</div>'
        . '<button class="px-6 py-3 rounded-2xl bg-[#ff6b00] font-semibold" type="submit">Send this CAME enquiry</button>'
        . '</div></form></div>'
        . '<script>
document.addEventListener("DOMContentLoaded",function(){
  var box=document.querySelector("[data-came-quote]");
  if(!box) return;
  var packs={};
  try{packs=JSON.parse(box.getAttribute("data-packs")||"{}");}catch(e){packs={};}
  var price=box.querySelector("[data-came-price]");
  var note=box.querySelector("[data-came-note]");
  function sync(){
    var boom=(document.querySelector("input[name=boom]:checked")||{}).value||"";
    var addon=(document.querySelector("input[name=came_addon]:checked")||{}).value||"std";
    var pack=packs[addon];
    if(boom==="5m" && pack){
      price.textContent=pack.price;
      note.textContent="Published 5m CAME supply price for "+pack.sku+" ("+pack.label+"), ex VAT. Installation POA.";
    }else{
      price.textContent="Quote after survey";
      note.textContent="No catalogue supply price for this length. Installation is POA. We do not invent a figure.";
    }
  }
  document.querySelectorAll("input[name=boom],input[name=came_addon]").forEach(function(el){el.addEventListener("change",sync);});
  sync();
});
</script></section>';
}

function barrierPagesBlockHtml(string $serviceSlug, string $areaName = '', bool $withWizard = false): string
{
    if ($serviceSlug === 'access-control') {
        return camePartnerPanelHtml('access-control');
    }
    if ($serviceSlug !== 'barriers') {
        return '';
    }
    $html = camePartnerPanelHtml('barriers') . barrierBrandGridHtml($areaName);
    if ($withWizard) {
        $html .= cameLaneWizardHtml();
    }
    return $html;
}

function barrierManufacturerAreaInnerHtml(array $brand, string $areaName): string
{
    $slug = (string)$brand['slug'];
    $name = (string)$brand['name'];
    $partner = !empty($brand['partner']);
    $lines = '';
    foreach ((array)($brand['lines'] ?? []) as $line) {
        if (!is_array($line)) {
            continue;
        }
        $img = barrierH(barrierLineImage($brand, $line));
        $lines .= '<article class="bg-white border rounded-3xl overflow-hidden">'
            . '<img src="' . $img . '" alt="' . barrierH($name . ' ' . (string)($line['name'] ?? '')) . '" class="w-full h-40 object-cover bg-zinc-50" loading="lazy" width="480" height="160">'
            . '<div class="p-5"><h3 class="font-semibold text-lg">' . barrierH((string)($line['name'] ?? '')) . '</h3>'
            . '<p class="mt-2 text-sm text-zinc-600">' . barrierH((string)($line['summary'] ?? '')) . '</p>'
            . '<p class="mt-3 text-sm font-semibold text-[#0B1F3A]">Supply quoted after survey · install POA</p></div></article>';
    }
    $towns = '';
    if (function_exists('getAreas')) {
        foreach (getAreas() as $town) {
            $towns .= '<a class="px-3 py-1.5 bg-white border rounded-full text-sm hover:border-[#ff6b00]" href="'
                . barrierH(url('/pages/manufacturers/' . $slug . '/' . areaSlug((string)$town))) . '">'
                . barrierH((string)$town) . '</a>';
        }
    }
    $phone = defined('PHONE') ? (string)PHONE : '07517806082';
    $badge = $partner
        ? '<p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">CAME partner</p>'
        : '<p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Barrier manufacturer</p>';
    $partnerCopy = $partner
        ? '<p class="mt-4 text-zinc-700">iComply is an authorised CAME partner for rising-arm barriers we supply and install in ' . barrierH($areaName) . '. We do not publish a partner or accreditation number.</p>'
        : '';
    return '<div class="max-w-7xl mx-auto px-6 py-12">'
        . $badge
        . '<h1 class="text-4xl font-semibold tracking-tight text-black mt-2">' . barrierH($name) . ' barriers in ' . barrierH($areaName) . '</h1>'
        . '<img src="' . barrierH($partner ? barrierLogoUrl($brand) : barrierArtUrl($brand)) . '" alt="' . barrierH($partner ? 'CAME partner range' : ($name . ' barrier')) . '" class="mt-6 w-full max-h-72 object-contain bg-zinc-50 rounded-3xl border" width="960" height="288">'
        . '<p class="mt-6 text-lg text-zinc-700 max-w-3xl">' . barrierH((string)($brand['blurb'] ?? '')) . '</p>'
        . $partnerCopy
        . '<div class="mt-6 flex flex-wrap gap-3">'
        . '<a class="px-5 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold" href="' . barrierH(url('/pages/manufacturers/' . $slug)) . '">All ' . barrierH($name) . ' product lines</a>'
        . '<a class="px-5 py-3 rounded-2xl border font-semibold" href="' . barrierH(url('/pages/barriers/' . areaSlug($areaName))) . '">All barrier brands in ' . barrierH($areaName) . '</a>'
        . ($partner ? '<a class="px-5 py-3 rounded-2xl border font-semibold" href="' . barrierH(url('/pages/services/barriers') . '#came-lane-builder') . '">CAME lane builder</a>' : '')
        . '<a class="px-5 py-3 rounded-2xl border font-semibold" href="tel:' . barrierH(preg_replace('/\s+/', '', $phone) ?? '') . '">' . barrierH($phone) . '</a>'
        . '</div>'
        . '<h2 class="mt-12 text-2xl font-semibold">Product lines</h2>'
        . '<div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">' . $lines . '</div>'
        . '<h2 class="mt-12 text-2xl font-semibold">' . barrierH($name) . ' in every town</h2>'
        . '<div class="mt-4 flex flex-wrap gap-2">' . $towns . '</div>'
        . '</div>';
}

function barrierManufacturerAreaExportHtml(string $mfrSlug, string $areaName): string
{
    $brand = barrierManufacturerBySlug($mfrSlug);
    if ($brand === null || $areaName === '') {
        return '';
    }
    $title = barrierH((string)$brand['name'] . ' barriers in ' . $areaName . ' | iComply');
    $desc = barrierH((string)($brand['blurb'] ?? ''));
    $canonical = barrierH(url('/pages/manufacturers/' . $mfrSlug . '/' . areaSlug($areaName)));
    return '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . $title . '</title>'
        . '<meta name="description" content="' . $desc . '">'
        . '<link rel="canonical" href="' . $canonical . '">'
        . '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">'
        . '</head><body class="bg-zinc-50 text-black">'
        . barrierManufacturerAreaInnerHtml($brand, $areaName)
        . '</body></html>';
}

function renderBarrierManufacturerAreaPage(string $mfrSlug, string $areaSlugValue): void
{
    $brand = barrierManufacturerBySlug($mfrSlug);
    $areaName = function_exists('areaFromSlug') ? areaFromSlug($areaSlugValue) : null;
    if ($brand === null || $areaName === null) {
        http_response_code(404);
        echo 'Barrier area not found';
        if (function_exists('icomplyRequestExit')) {
            icomplyRequestExit();
        }
        return;
    }
    $entry = barrierManufacturerCatalogEntries()[$mfrSlug] ?? [];
    $pageTitle = (string)$brand['name'] . ' barriers in ' . $areaName;
    $metaDesc = (string)($brand['blurb'] ?? '');
    $metaKeywords = (string)($entry['seo_keywords'] ?? '');
    $canonicalUrl = url('/pages/manufacturers/' . $mfrSlug . '/' . areaSlug($areaName));
    $ogImage = !empty($brand['partner']) && !empty($brand['partner_image'])
        ? (string)$brand['partner_image']
        : barrierLogoUrl($brand);
    require SITE_ROOT . '/templates/barrier-manufacturer-area.php';
}

function icomplyShopifyLibraryIsPhp(): bool
{
    $file = SITE_ROOT . '/includes/shopify.php';
    if (!is_file($file)) {
        return false;
    }
    return str_starts_with(ltrim((string)file_get_contents($file)), '<?php');
}

function icomplyRequireShopify(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    if (icomplyShopifyLibraryIsPhp()) {
        require_once SITE_ROOT . '/includes/shopify.php';
    }
}

icomplyRequireShopify();

if (!function_exists('shopifyBuyButtonScript')) {
    function shopifyBuyButtonScript(): string
    {
        return '';
    }
}

if (!function_exists('shopifyStoreUrl')) {
    function shopifyStoreUrl(): string
    {
        if (defined('SHOPIFY_STORE_URL') && SHOPIFY_STORE_URL !== '') {
            return (string)SHOPIFY_STORE_URL;
        }
        return '';
    }
}

if (!function_exists('shopifyCardFromManufacturerProduct')) {
    /**
     * @param array<string,mixed> $product
     */
    function shopifyCardFromManufacturerProduct(array $product, string $slug, string $brand): string
    {
        $title = barrierH((string)($product['title'] ?? $brand));
        $blurb = barrierH((string)($product['blurb'] ?? ''));
        $price = trim((string)($product['price'] ?? ''));
        $priceHtml = ($price === '' || strcasecmp($price, 'POA') === 0)
            ? 'Quote after survey'
            : barrierH($price) . ' <span class="text-xs text-zinc-500 font-normal">supply · install POA</span>';
        $img = barrierH((string)($product['image'] ?? ''));
        $badge = barrierH((string)($product['badge'] ?? ''));
        $imgHtml = $img !== ''
            ? '<img src="' . $img . '" alt="' . $title . '" class="w-full h-36 object-contain bg-zinc-50" loading="lazy" width="320" height="144">'
            : '';
        return '<article class="bg-white border rounded-3xl overflow-hidden flex flex-col">'
            . $imgHtml
            . '<div class="p-5 flex-1 flex flex-col">'
            . ($badge !== '' ? '<div class="text-xs font-semibold uppercase tracking-wider text-[#ff6b00]">' . $badge . '</div>' : '')
            . '<h3 class="mt-1 font-semibold text-lg text-black">' . $title . '</h3>'
            . '<p class="mt-2 text-sm text-zinc-600 flex-1">' . $blurb . '</p>'
            . '<div class="mt-4 font-semibold text-[#0B1F3A]">' . $priceHtml . '</div>'
            . '</div></article>';
    }
}
