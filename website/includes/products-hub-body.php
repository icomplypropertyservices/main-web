<?php
// Exported twice in one process (/pages/products and /products). Guard so the
// second include still prints this body.
if (!function_exists('icomplyBar5mCameGardImageMap')) {
function icomplyBar5mCameGardImageMap(): array {
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    $path = SITE_ROOT . '/data/bar-5m-came-gard-images.json';
    if (is_readable($path)) {
        $json = json_decode((string) file_get_contents($path), true);
        if (is_array($json)) {
            foreach ($json as $k => $v) {
                if (is_string($k) && is_string($v) && $v !== '') $map[strtolower($k)] = $v;
            }
        }
    }
    return $map;
}
}

/** Marketing AOV kit CDN map (Rev C). Keys: aov-act, aov-motor, aov-kit-1m2, … */
if (!function_exists('icomplyAovKitCdnImageMap')) {
function icomplyAovKitCdnImageMap(): array {
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    $path = SITE_ROOT . '/data/aov-kit-cdn-images.json';
    if (is_readable($path)) {
        $json = json_decode((string) file_get_contents($path), true);
        if (is_array($json)) {
            foreach ($json as $k => $v) {
                if (is_string($k) && is_string($v) && $v !== '') $map[strtolower($k)] = $v;
            }
        }
    }
    return $map;
}
}

if (!function_exists('icomplyAovKitImageUrl')) {
function icomplyAovKitImageUrl(string $sku): string {
    $map = icomplyAovKitCdnImageMap();
    $key = strtolower(str_replace('_', '-', $sku));
    if (isset($map[$key])) return $map[$key];
    // SKU AOV-MOTOR-HVY → aov-motor-hvy
    return $map[$key] ?? ('/assets/images/products/' . $sku . '.jpg');
}
}
?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
  <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
    <nav class="text-xs text-white/50 mb-6" aria-label="Breadcrumb">
      <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a> / <span class="text-white/80">Products</span>
    </nav>
    <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter">Trade products<br><span class="text-[#FF6B00]">&amp; materials</span></h1>
    <p class="mt-5 text-lg text-white/80 max-w-2xl">Electrical, Fire, Security and Gas — Shopify store or Stockport install. This hub does not redirect to packages.</p>
    <div class="mt-8 flex flex-wrap gap-3">
      <a href="<?= htmlspecialchars($shop, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-[#FF6B00] font-semibold" target="_blank" rel="noopener">Open shop.icomplypropertyservices.co.uk</a>
      <a href="<?= url('/shop/index.php') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold">On-site trade shop</a>
      <a href="<?= url('/contact.php') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Enquire</a>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10">
  <a href="<?= htmlspecialchars($aovService, ENT_QUOTES, 'UTF-8') ?>" class="block p-8 md:p-10 bg-white border-2 border-[#FF6B00] rounded-3xl hover:shadow-lg transition">
    <span class="inline-block text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-[#FF6B00] text-white mb-3">Priority · AOV</span>
    <h2 class="text-2xl md:text-3xl font-semibold text-black">AOV &amp; Smoke Control</h2>
    <p class="mt-2 text-zinc-600 max-w-2xl">Equipment kits show SoT list prices; <strong>installation is POA</strong>.</p>
    <span class="mt-4 inline-block text-sm font-semibold text-[#FF6B00]">POA / Get a quote →</span>
  </a>
  <?php if (function_exists('icomplyAovKitPriceStripHtml')): ?>
  <div class="mt-8 bg-white border border-zinc-200 rounded-3xl p-6 md:p-8"><?= icomplyAovKitPriceStripHtml() ?></div>
  <?php endif; ?>
  <div class="mt-8 bg-white border border-zinc-200 rounded-3xl p-6 md:p-8">
    <h3 class="text-lg font-semibold mb-2">AOV equipment kits (ex VAT)</h3>
    <p class="text-sm text-zinc-600 mb-3">Install / labour POA. Photos from Marketing CDN Rev C.</p>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ([
        ['AOV-MOTOR','£275'],['AOV-MOTOR-HVY','£950'],['AOV-ACT','£275'],['AOV-ACT-HVY','£950'],
        ['AOV-CTRL','£400'],['AOV-SENSOR','£60'],['AOV-KIT-1M2','£2,450']
      ] as [$sku,$price]):
        $aovImg = icomplyAovKitImageUrl($sku);
      ?>
      <article class="p-4 border rounded-2xl">
        <img src="<?= htmlspecialchars($aovImg, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($sku, ENT_QUOTES, 'UTF-8') ?>" class="w-full h-28 object-contain mb-2" loading="lazy" width="240" height="112" onerror="this.style.display='none'">
        <div class="text-xs font-semibold text-[#FF6B00]">AOV kit</div>
        <h4 class="font-semibold"><?= htmlspecialchars($sku, ENT_QUOTES, 'UTF-8') ?></h4>
        <div class="text-[#FF6B00] font-semibold"><?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?> <span class="text-xs text-zinc-500 font-normal">ex VAT · install POA</span></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 pb-12">
  <h2 class="text-2xl font-semibold text-black mb-2">Barrier packs (5m)</h2>
  <p class="text-sm text-zinc-600 mb-6">SoT supply prices. Install POA. Images from Marketing CAME GARD CDN map.</p>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php
    $barCame = icomplyBar5mCameGardImageMap();
    $barPacks = [
      ['sku'=>'BAR-5M-STD','handle'=>'bar-5m-std','price'=>'£5,850.00','blurb'=>'Standard 5m barrier pack'],
      ['sku'=>'BAR-5M-VIDEX','handle'=>'bar-5m-videx','price'=>'£7,441.83','blurb'=>'5m barrier + Videx'],
      ['sku'=>'BAR-5M-PAXTON','handle'=>'bar-5m-paxton','price'=>'£8,375.45','blurb'=>'5m barrier + Paxton'],
      ['sku'=>'BAR-5M-GSM','handle'=>'bar-5m-gsm','price'=>'£7,393.18','blurb'=>'5m barrier + GSM'],
      ['sku'=>'BAR-5M-ALLIN','handle'=>'bar-5m-allin','price'=>'£5,199.99','blurb'=>'5m barrier all-in (Jack-confirmed)'],
    ];
    foreach ($barPacks as $bp):
      $img = $barCame[$bp['handle']] ?? $barCame[strtolower($bp['sku'])] ?? '';
      if ($img === '') $img = '/assets/images/products/' . $bp['sku'] . '.jpg';
    ?>
    <article class="p-6 bg-white border border-zinc-200 rounded-3xl">
      <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($bp['sku'], ENT_QUOTES, 'UTF-8') ?>" class="w-full h-40 object-contain mb-4" loading="lazy" width="320" height="160" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'w-full h-40 mb-4 rounded-2xl bg-zinc-100 flex items-center justify-center text-xs text-zinc-400',textContent:'Photo pending'}))">
      <div class="text-xs font-semibold uppercase tracking-wider text-[#FF6B00]">Barrier</div>
      <h3 class="mt-2 font-semibold text-lg text-black"><?= htmlspecialchars($bp['sku'], ENT_QUOTES, 'UTF-8') ?></h3>
      <p class="mt-1 text-sm text-zinc-600"><?= htmlspecialchars($bp['blurb'], ENT_QUOTES, 'UTF-8') ?></p>
      <div class="mt-4 text-[#FF6B00] font-semibold"><?= htmlspecialchars($bp['price'], ENT_QUOTES, 'UTF-8') ?> <span class="text-xs text-zinc-500 font-normal">ex VAT · install POA</span></div>
      <a class="mt-4 inline-flex text-sm font-semibold text-[#0B1F3A]" href="<?= url('/contact.php') ?>">Enquire →</a>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 pb-12">
  <h2 class="text-2xl font-semibold text-black mb-2">Nurse call</h2>
  <article class="max-w-md p-6 bg-white border border-zinc-200 rounded-3xl">
    <img src="https://cdn11.bigcommerce.com/s-sj1h3u/products/275/images/2012/CT_altra_room_unit__21104.1789637267.386.513.jpg" alt="NC-ANN" class="w-full h-32 object-contain mb-4" loading="lazy" width="320" height="128" onerror="this.style.display='none'">
    <div class="text-xs font-semibold uppercase tracking-wider text-[#FF6B00]">Nurse call</div>
    <h3 class="mt-2 font-semibold text-lg text-black">NC-ANN</h3>
    <p class="mt-1 text-sm text-zinc-600">Nurse call annunciator / annual service SKU (SoT).</p>
    <div class="mt-4 text-[#FF6B00] font-semibold">£199.99 <span class="text-xs text-zinc-500 font-normal">ex VAT · install POA</span></div>
    <a class="mt-4 inline-flex text-sm font-semibold text-[#0B1F3A]" href="<?= url('/contact.php') ?>">Enquire →</a>
  </article>
</section>

<section class="max-w-7xl mx-auto px-6 pb-16">
  <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
    <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="/shop/electrical/"><div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Electrical</div><h2 class="text-xl font-semibold mt-2">Electrical</h2></a>
    <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="/shop/fire/"><div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Fire</div><h2 class="text-xl font-semibold mt-2">Fire</h2></a>
    <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="/shop/security/"><div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Security</div><h2 class="text-xl font-semibold mt-2">Security</h2></a>
    <a class="p-8 bg-white border rounded-3xl hover:border-[#FF6B00] transition" href="/shop/gas/"><div class="text-xs uppercase tracking-[3px] text-[#FF6B00] font-semibold">Gas</div><h2 class="text-xl font-semibold mt-2">Gas</h2></a>
  </div>
</section>
