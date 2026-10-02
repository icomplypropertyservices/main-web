<?php
/**
 * AOV manufacturer plates and equipment-kit chooser.
 * Wordmarks are original name plates, not copied trademark artwork.
 * We do not claim approved-installer or scheme accreditation.
 */
declare(strict_types=1);

/** @return list<array{slug:string,name:string,line:string}> */
function aovManufacturers(): array
{
    return [
        ['slug' => 'se-controls', 'name' => 'SE Controls', 'line' => 'Stair actuators and smoke-control panels, including OS-family kit still on residential blocks.'],
        ['slug' => 'windowmaster', 'name' => 'WindowMaster', 'line' => 'Façade and roof chain actuators and their controllers.'],
        ['slug' => 'd-h-mechatronic', 'name' => 'D+H Mechatronic', 'line' => 'Chain and locking drives used on smoke vents and façade windows.'],
        ['slug' => 'geze', 'name' => 'GEZE', 'line' => 'Window drives and vent controls where that is the label on the actuator.'],
        ['slug' => 'simon-rwa', 'name' => 'Simon RWA', 'line' => 'Smoke-vent drives and compact control panels.'],
        ['slug' => 'colt', 'name' => 'Colt', 'line' => 'Natural smoke ventilators and older Colt control kits.'],
        ['slug' => 'bilco', 'name' => 'Bilco', 'line' => 'Roof hatches used as head-of-stair vents.'],
        ['slug' => 'cambric', 'name' => 'Cambric', 'line' => 'Smoke ventilators and related roof kit.'],
        ['slug' => 'group-scs', 'name' => 'Group SCS', 'line' => 'Smoke-control systems and interfaces on managed blocks.'],
        ['slug' => 'smoke-control', 'name' => 'Smoke Control', 'line' => 'Packaged smoke-control equipment under that trade name.'],
        ['slug' => 'trox', 'name' => 'TROX', 'line' => 'Dampers and shaft components where the smoke path is mechanical or a shaft.'],
        ['slug' => 'nuaire', 'name' => 'Nuaire', 'line' => 'Fans and air-handling plant, including smoke-mode interlocks.'],
        ['slug' => 'flaktgroup', 'name' => 'FlaktGroup', 'line' => 'Fans and air-handling units, not a stair chain actuator.'],
        ['slug' => 'systemair', 'name' => 'Systemair', 'line' => 'Extract and supply fans and their controls.'],
        ['slug' => 'kingspan-air', 'name' => 'Kingspan Air', 'line' => 'Ventilation products listed against this service. Confirm the badge on site before parts are ordered.'],
        ['slug' => 'brooks', 'name' => 'Brooks', 'line' => 'Listed for smoke-vent work. We match the part to the label, not the logo on this page.'],
        ['slug' => 'ventilux', 'name' => 'Ventilux', 'line' => 'Listed on the AOV set. If the panel is actually emergency lighting, that is a different visit.'],
        ['slug' => 'assa-abloy', 'name' => 'Assa Abloy', 'line' => 'Door and vent hardware when that is what the smoke strategy names. Not every Assa product is an AOV.'],
    ];
}

function aovBrandWordmarkUrl(string $slug): string
{
    $svg = '/assets/images/manufacturers/' . $slug . '.svg';
    if (is_file(SITE_ROOT . $svg)) {
        return url($svg);
    }
    return function_exists('manufacturerImageUrl')
        ? manufacturerImageUrl($slug, 'aov-air-handling')
        : url('/assets/images/services/aov-air-handling.jpg');
}

function aovBrandGridHtml(): string
{
    $html = '<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">';
    foreach (aovManufacturers() as $brand) {
        $href = htmlspecialchars(url('/pages/manufacturers/' . $brand['slug'] . '.php'), ENT_QUOTES, 'UTF-8');
        $src = htmlspecialchars(aovBrandWordmarkUrl($brand['slug']), ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8');
        $line = htmlspecialchars($brand['line'], ENT_QUOTES, 'UTF-8');
        $html .= '<a href="' . $href . '" class="bg-white border border-zinc-200 rounded-2xl overflow-hidden hover:border-[#ff6b00] transition block">'
            . '<img src="' . $src . '" alt="' . $name . ' wordmark" width="320" height="120" class="w-full h-24 object-cover bg-[#f7f4ef]" loading="lazy">'
            . '<div class="p-4"><div class="font-semibold text-black">' . $name . '</div>'
            . '<p class="mt-1 text-sm text-zinc-600">' . $line . '</p>'
            . '<span class="mt-2 inline-block text-sm font-semibold text-[#ff6b00]">Brand page and spares →</span></div></a>';
    }
    $html .= '</div>';
    return $html;
}

/** @return list<array{id:string,title:string,price:string,blurb:string,image:string,shop:string}> */
function aovKitChoices(): array
{
    require_once SITE_ROOT . '/includes/aov-kit-prices.php';
    $images = [];
    $imgFile = SITE_ROOT . '/data/aov-kit-cdn-images.json';
    if (is_file($imgFile)) {
        $decoded = json_decode((string)file_get_contents($imgFile), true);
        if (is_array($decoded)) {
            $images = $decoded;
        }
    }
    $shop = defined('SHOPIFY_STORE_URL') ? rtrim((string)SHOPIFY_STORE_URL, '/') : 'https://shop.icomplypropertyservices.co.uk';
    $blurbs = [
        'aov-kit-1m2' => 'Stairwell package around 1 m². Supply list price. Installation, access and commissioning stay POA.',
        'aov-act' => 'Standard actuator. Match stroke and force to the vent before you buy.',
        'aov-act-hvy' => 'Heavy actuator for larger louvres or roof hatches.',
        'aov-motor' => 'Motor body for a standard vent drive.',
        'aov-motor-hvy' => 'Heavy motor where the vent weight is above a standard chain.',
        'aov-ctrl' => 'Control panel. Batteries and the fire-alarm input are a separate check.',
        'aov-sensor' => 'Rain or wind sensor. It must not hold a vent shut after a fire signal.',
    ];
    $out = [];
    foreach (icomplyAovKitSkuCatalog() as $id => $row) {
        $img = (string)($images[$id] ?? '');
        $out[] = [
            'id' => $id,
            'title' => $row['label'],
            'price' => $row['price'],
            'blurb' => $blurbs[$id] ?? 'Supply kit. Installation is POA.',
            'image' => $img,
            'shop' => $shop . '/search?q=' . rawurlencode($row['label']),
        ];
    }
    return $out;
}

function aovKitWizardHtml(string $context): string
{
    $kits = aovKitChoices();
    $json = htmlspecialchars(json_encode($kits, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
    $products = htmlspecialchars(url('/products.php') . '#aov-kits', ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars((string)PHONE, ENT_QUOTES, 'UTF-8');
    $tel = htmlspecialchars(aovPhoneHref(), ENT_QUOTES, 'UTF-8');
    $cards = '';
    foreach ($kits as $kit) {
        $cards .= '<article class="p-4 border border-zinc-200 rounded-2xl bg-white" data-aov-kit-card="' . htmlspecialchars($kit['id'], ENT_QUOTES, 'UTF-8') . '">'
            . ($kit['image'] !== '' ? '<img src="' . htmlspecialchars($kit['image'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($kit['title'], ENT_QUOTES, 'UTF-8') . '" class="w-full h-28 object-contain mb-3" loading="lazy">' : '')
            . '<h3 class="font-semibold text-black">' . htmlspecialchars($kit['title'], ENT_QUOTES, 'UTF-8') . '</h3>'
            . '<p class="text-sm text-zinc-600 mt-1">' . htmlspecialchars($kit['blurb'], ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p class="mt-2 font-semibold text-[#ff6b00]">' . htmlspecialchars($kit['price'], ENT_QUOTES, 'UTF-8') . ' <span class="text-xs text-zinc-500 font-normal">ex VAT · install POA</span></p>'
            . '<div class="mt-3 flex flex-wrap gap-3 text-sm font-semibold">'
            . '<a class="text-[#0B1F3A]" href="' . htmlspecialchars($kit['shop'], ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">Shop search →</a>'
            . '<a class="text-[#ff6b00]" href="' . $products . '">Kit list →</a>'
            . '</div></article>';
    }
    $ctx = htmlspecialchars($context, ENT_QUOTES, 'UTF-8');
    return <<<HTML
<section id="kit" class="bg-zinc-50 border-y" data-aov-wizard data-context="{$ctx}" data-kits="{$json}">
  <div class="max-w-7xl mx-auto px-6 py-14">
    <p class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Equipment</p>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Choose a supply kit, or describe the fault</h2>
    <p class="mt-3 text-zinc-600 max-w-3xl">List prices are for the supply SKUs Jack locked. Installation, access equipment, commissioning and multi-visit work are priced after we know the building. These name plates are ours. We are not an approved partner of any brand on this page, and we do not hold a BAFE or FIRAS badge for smoke control.</p>
    <form class="mt-6 grid md:grid-cols-2 gap-4 bg-white border border-zinc-200 rounded-3xl p-5" data-aov-wizard-form>
      <label class="block text-sm font-semibold text-black">What do you need?
        <select name="need" class="mt-2 w-full border border-zinc-300 rounded-xl px-3 py-3 font-normal" data-aov-need>
          <option value="">Select</option>
          <option value="aov-kit-1m2">Stairwell vent package (about 1 m²)</option>
          <option value="aov-act">Standard actuator</option>
          <option value="aov-act-hvy">Heavy actuator or large louvre</option>
          <option value="aov-motor">Standard motor</option>
          <option value="aov-motor-hvy">Heavy motor</option>
          <option value="aov-ctrl">Control panel</option>
          <option value="aov-sensor">Rain or wind sensor</option>
          <option value="engineer">An engineer, not a box — vent stuck, panel in fault, or a test</option>
        </select>
      </label>
      <label class="block text-sm font-semibold text-black">Brand on the equipment, if you can read it
        <select name="brand" class="mt-2 w-full border border-zinc-300 rounded-xl px-3 py-3 font-normal" data-aov-brand>
          <option value="">Not sure</option>
HTML
        . aovBrandOptionsHtml()
        . <<<HTML
        </select>
      </label>
      <p class="md:col-span-2 text-sm text-zinc-700" data-aov-wizard-result>Pick a kit or choose an engineer visit. Supply prices show here. Labour does not.</p>
      <div class="md:col-span-2 flex flex-wrap gap-3">
        <a href="{$tel}" class="px-5 py-3 rounded-2xl bg-[#0B1F3A] text-white font-semibold">Call {$phone}</a>
        <a href="#quote" class="px-5 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold">Put this on the quote form</a>
      </div>
    </form>
    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">{$cards}</div>
  </div>
</section>
<script src="/assets/js/aov-kit.js" defer></script>
HTML;
}

function aovBrandOptionsHtml(): string
{
    $html = '';
    foreach (aovManufacturers() as $brand) {
        $html .= '<option value="' . htmlspecialchars($brand['slug'], ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
}
