<?php
declare(strict_types=1);

function icomplyMegaHeaderHtml(): string
{
    $n = icomplyNavCatalog();
    $home = icomplyNavH($n['home']);
    $brand = icomplyNavH($n['brand']);
    $logo = icomplyNavH($n['logo']);
    $phone = icomplyNavH($n['phone']);
    $phoneHref = icomplyNavH($n['phoneHref']);
    $wa = icomplyNavH($n['whatsapp']);
    $js = icomplyNavH($n['js']);
    $svcHub = icomplyNavH(url('/pages/services/index.php'));
    $areaHub = icomplyNavH(url('/pages/areas/index.php'));
    $contact = icomplyNavH(url('/contact.php'));
    $products = icomplyNavH(url('/products.php'));
    $shopAll = '/shop/';
    $hubElectrical = '/shop/electrical/';
    $hubFire = '/shop/fire/';
    $hubSecurity = '/shop/security/';
    $hubGas = '/shop/gas/';
    $shopLive = icomplyNavH(function_exists('icomplyTradeShopUrl') ? icomplyTradeShopUrl() : 'https://shop.icomplypropertyservices.co.uk');
    $kitsHub = icomplyNavH(url('/pages/kits'));
    $aovHub = icomplyNavH(url('/pages/services/aov-air-handling.php'));
    $aovFeatured = '<div class="mega-featured mega-featured--aov">'
        . '<p class="mega-featured-title">Priority — AOV &amp; smoke control</p>'
        . '<a class="mega-featured-link" href="' . $aovHub . '">AOV &amp; Smoke Control</a>'
        . '<p class="mega-note">Smoke vents, AOV panels, EN 12101 / BS 9991. Quotes POA after scope.</p>'
        . '</div>';

    $svcCols = '';
    foreach ($n['cats'] as $catKey => $cat) {
        $svcCols .= '<div class="mega-col">';
        $svcCols .= '<p class="mega-col-title">' . icomplyNavH($cat['label']) . '</p>';
        foreach ($cat['services'] as $slug => $name) {
            $svcCols .= icomplyNavLink(url('/pages/services/' . rawurlencode((string)$slug) . '.php'), (string)$name);
        }
        $svcCols .= '</div>';
    }

    $areaCols = '<div class="mega-col">';
    $areaCols .= '<p class="mega-col-title">Popular towns</p>';
    foreach ($n['popularAreas'] as $area) {
        $areaCols .= icomplyNavLink(url('/pages/areas/' . areaSlug($area) . '.php'), (string)$area);
    }
    $areaCols .= icomplyNavLink(url('/pages/areas/index.php'), 'All ' . count($n['areas']) . ' areas →', 'mega-more');
    $areaCols .= '</div><div class="mega-col mega-col--wide">';
    $areaCols .= '<p class="mega-col-title">A–Z (jump to hub)</p>';
    $areaCols .= '<div class="mega-letters">';
    foreach (array_keys($n['areasByLetter']) as $letter) {
        $areaCols .= '<a href="' . $areaHub . '#letter-' . icomplyNavH((string)$letter) . '">' . icomplyNavH((string)$letter) . '</a>';
    }
    $areaCols .= '</div><p class="mega-note">Every town has its own area page. Keyword×town pages open from a keyword hub.</p></div>';

    $drawer = icomplyMobileDrawerHtml($n);
    $svcCount = count($n['services']);
    $areaCount = count($n['areas']);

    return <<<HTML
<header class="site-header mega-header" data-site-header>
  <div class="mega-bar">
    <a class="mega-logo" href="{$home}">
      <img src="{$logo}" width="36" height="36" alt="iComply" class="mega-logo-img" decoding="async">
      <span class="mega-wordmark"><b>iComply</b><small>Property Services</small></span>
    </a>
    <nav class="mega-desktop" aria-label="Primary">
      <a class="nav-link" href="{$home}">Home</a>
      <div class="mega-item" data-mega>
        <button type="button" class="mega-trigger" aria-expanded="false" aria-controls="mega-services" aria-haspopup="true">Services</button>
        <div id="mega-services" class="mega-panel" hidden>
          <div class="mega-panel-inner mega-panel-inner--wide">
            <div class="mega-panel-head">
              <a href="{$svcHub}">All {$svcCount} services →</a>
            </div>
            {$aovFeatured}
            <div class="mega-grid">{$svcCols}</div>
          </div>
        </div>
      </div>
      <div class="mega-item" data-mega>
        <button type="button" class="mega-trigger" aria-expanded="false" aria-controls="mega-areas" aria-haspopup="true">Areas</button>
        <div id="mega-areas" class="mega-panel" hidden>
          <div class="mega-panel-inner">
            <div class="mega-panel-head"><a href="{$areaHub}">All {$areaCount} towns →</a></div>
            <div class="mega-grid">{$areaCols}</div>
          </div>
        </div>
      </div>
      <div class="mega-item" data-mega>
        <button type="button" class="mega-trigger" aria-expanded="false" aria-controls="mega-shop" aria-haspopup="true">Shop</button>
        <div id="mega-shop" class="mega-panel mega-panel--shop" hidden>
          <div class="mega-panel-inner">
            <div class="mega-panel-head"><a href="{$shopAll}">Trade supplies →</a></div>
            <div class="mega-grid mega-grid--shop">
              <div class="mega-col">
                <p class="mega-col-title">Fire</p>
                <a href="{$hubFire}">Fire supplies hub</a>
                <a href="{$aovHub}">AOV &amp; smoke control (service)</a>
              </div>
              <div class="mega-col">
                <p class="mega-col-title">Category hubs</p>
                <a href="{$hubFire}">Fire</a>
                <a href="{$hubElectrical}">Electrical</a>
                <a href="{$hubSecurity}">Security</a>
                <a href="{$hubGas}">Gas</a>
                <a href="{$kitsHub}">Kit builders</a>
                <a href="{$shopAll}" class="mega-more">All supplies →</a>
                <a href="{$shopLive}" class="mega-more" target="_blank" rel="noopener">Shopify checkout →</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <a class="nav-link" href="{$products}">Products</a>
      <a class="nav-link" href="{$contact}">Contact</a>
    </nav>
    <div class="mega-tools">
      <a class="mega-quote" href="{$contact}">Get a quote</a>
      <button type="button" class="mega-burger" id="nav-toggle" aria-expanded="false" aria-controls="mega-drawer">Menu</button>
    </div>
  </div>
  {$drawer}
</header>
<script src="{$js}" defer></script>
HTML;
}

/** @param array<string,mixed> $n */
function icomplyMobileDrawerHtml(array $n): string
{
    $home = icomplyNavH($n['home']);
    $phoneHref = icomplyNavH($n['phoneHref']);
    $phone = icomplyNavH($n['phone']);
    $wa = icomplyNavH($n['whatsapp']);
    $contactDrawer = icomplyNavH(url('/contact.php'));
    $siteMap = icomplyNavH(url('/pages/site-map.php'));

    $aovHubDrawer = icomplyNavH(url('/pages/services/aov-air-handling.php'));
    $svc = '<a class="drawer-featured" href="' . $aovHubDrawer . '">AOV &amp; Smoke Control</a>';
    $svc .= '<p class="drawer-note">Priority — smoke vents, AOV panels, EN 12101 / BS 9991. POA after scope.</p>';
    foreach ($n['cats'] as $cat) {
        $svc .= '<details class="drawer-acc"><summary>' . icomplyNavH($cat['label']) . '</summary><div>';
        foreach ($cat['services'] as $slug => $name) {
            $svc .= icomplyNavLink(url('/pages/services/' . rawurlencode((string)$slug) . '.php'), (string)$name);
        }
        $svc .= '</div></details>';
    }

    $areas = '<a class="drawer-all" href="' . icomplyNavH(url('/pages/areas/index.php')) . '">All ' . count($n['areas']) . ' areas →</a>';
    foreach ($n['popularAreas'] as $area) {
        $areas .= icomplyNavLink(url('/pages/areas/' . areaSlug($area) . '.php'), (string)$area);
    }

    $productsD = icomplyNavH(url('/products.php'));
    $hubElectricalD = '/shop/electrical/';
    $hubFireD = '/shop/fire/';
    $hubSecurityD = '/shop/security/';
    $hubGasD = '/shop/gas/';
    $kitsHub = icomplyNavH(url('/pages/kits'));

    return <<<HTML
<div id="mega-drawer" class="mega-drawer" hidden>
  <nav class="mega-drawer-inner" aria-label="Mobile">
    <a href="{$home}">Home</a>
    <details class="drawer-acc" open><summary>Services</summary><div>{$svc}</div></details>
    <details class="drawer-acc"><summary>Areas</summary><div>{$areas}</div></details>
    <details class="drawer-acc"><summary>Shop</summary><div>
      <a href="{$hubFireD}">Fire</a>
      <a href="{$aovHubDrawer}">AOV &amp; smoke control (service)</a>
      <a href="{$hubElectricalD}">Electrical</a>
      <a href="{$hubSecurityD}">Security</a>
      <a href="{$hubGasD}">Gas</a>
      <a href="{$kitsHub}">Kit builders</a>
      <a href="/shop/">All supplies</a>
    </div></details>
    <a href="{$productsD}">Products</a>
    <a class="drawer-cta drawer-cta--quote" href="{$contactDrawer}">Get a quote</a>
  </nav>
</div>
HTML;
}
