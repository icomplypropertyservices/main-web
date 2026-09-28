<?php
declare(strict_types=1);

function icomplyFooterHtml(): string
{
    $n = icomplyNavCatalog();
    $phone = icomplyNavH($n['phone']);
    $phoneHref = icomplyNavH($n['phoneHref']);
    $email = icomplyNavH($n['email']);
    $wa = icomplyNavH($n['whatsapp']);
    $brand = icomplyNavH($n['brand']);
    $year = date('Y');
    $contact = icomplyNavH(url('/contact.php'));
    $svcCount = count($n['services']);
    $areaCount = count($n['areas']);
    $kwCount = count($n['keywords']);

    $svcDrop = '';
    foreach ($n['cats'] as $cat) {
        $svcDrop .= '<details class="foot-sub"><summary>' . icomplyNavH($cat['label']) . '</summary><div class="foot-links">';
        foreach ($cat['services'] as $slug => $name) {
            $svcDrop .= icomplyNavLink(url('/pages/services/' . rawurlencode((string)$slug) . '.php'), (string)$name);
        }
        $svcDrop .= '</div></details>';
    }

    $areaDrop = '';
    foreach ($n['areasByLetter'] as $letter => $list) {
        $areaDrop .= '<details class="foot-sub" id="foot-letter-' . icomplyNavH((string)$letter) . '"><summary>' . icomplyNavH((string)$letter) . '</summary><div class="foot-links">';
        foreach ($list as $area) {
            $areaDrop .= icomplyNavLink(url('/pages/areas/' . areaSlug((string)$area) . '.php'), (string)$area);
        }
        $areaDrop .= '</div></details>';
    }

    $kwDrop = '';
    foreach ($n['cats'] as $cat) {
        if (empty($cat['keywords'])) {
            continue;
        }
        $kwDrop .= '<details class="foot-sub"><summary>' . icomplyNavH($cat['label']) . '</summary>';
        foreach ($cat['keywords'] as $svcSlug => $block) {
            $kwDrop .= '<details class="foot-sub"><summary>' . icomplyNavH($block['name']) . '</summary><div class="foot-links">';
            foreach ($block['keywords'] as $kSlug => $meta) {
                $kwDrop .= icomplyNavLink(url('/pages/keywords/' . rawurlencode((string)$kSlug) . '.php'), (string)($meta['name'] ?? $kSlug));
            }
            $kwDrop .= '</div></details>';
        }
        $kwDrop .= '</details>';
    }

    $matrixDrop = '<p class="foot-note">Every keyword hub has a page for every town (' . $kwCount . ' × ' . $areaCount . '). Open a hub, then pick the town — we do not dump 200,000 links here.</p>';
    $matrixDrop .= '<details class="foot-sub"><summary>Open a keyword hub (then pick a town)</summary><div class="foot-links">';
    foreach ($n['featuredKw'] as $slug => $name) {
        $matrixDrop .= icomplyNavLink(url('/pages/keywords/' . rawurlencode((string)$slug) . '.php'), (string)$name);
    }
    $matrixDrop .= icomplyNavLink(url('/pages/keywords/index.php'), 'Full keyword index →');
    $matrixDrop .= '</div></details>';
    $matrixDrop .= '<details class="foot-sub"><summary>Open an area page (then pick a keyword)</summary><div class="foot-links">';
    foreach ($n['popularAreas'] as $area) {
        $matrixDrop .= icomplyNavLink(url('/pages/areas/' . areaSlug((string)$area) . '.php'), (string)$area);
    }
    $matrixDrop .= icomplyNavLink(url('/pages/areas/index.php'), 'All ' . $areaCount . ' areas →');
    $matrixDrop .= '</div></details>';

    $resDrop = '<div class="foot-links">';
    foreach ($n['resources'] as $row) {
        $resDrop .= icomplyNavLink($row['href'], $row['label']);
    }
    $resDrop .= '</div>';

    $pkgDrop = '<div class="foot-links">';
    foreach ($n['packages'] as $row) {
        $pkgDrop .= icomplyNavLink($row['href'], $row['label']);
    }
    $pkgDrop .= '</div>';

    $legalDrop = '<div class="foot-links">';
    $legalDrop .= icomplyNavLink(url('/contact.php'), 'Contact / quote');
    $legalDrop .= icomplyNavLink($n['phoneHref'], 'Call ' . $n['phone']);
    $legalDrop .= '<a href="mailto:' . $email . '">' . $email . '</a>';
    foreach ($n['legal'] as $row) {
        $legalDrop .= icomplyNavLink($row['href'], $row['label']);
    }
    $legalDrop .= '</div>';

    $social = function_exists('socialIconsHtml') ? socialIconsHtml('dark') : '';
    $svcHub = icomplyNavH(url('/pages/services/index.php'));
    $areaHub = icomplyNavH(url('/pages/areas/index.php'));
    $kwHub = icomplyNavH(url('/pages/keywords/index.php'));
    $privacy = icomplyNavH($n['legal'][0]['href']);
    $terms = icomplyNavH($n['legal'][1]['href']);
    $siteMap = icomplyNavH($n['legal'][2]['href']);

    return <<<HTML
<footer class="site-footer" data-site-footer>
  <div class="foot-wrap">
    <div class="foot-nap">
      <div class="foot-brand">{$brand}</div>
      <p>Property compliance — electrical, fire, gas, water hygiene and asbestos surveys across Greater Manchester and the North West. Quotes are POA until scope is confirmed. Call {$phone}.</p>
      <p><span class="foot-label">Phone</span> <a href="{$phoneHref}">{$phone}</a></p>
      <p><span class="foot-label">Email</span> <a href="mailto:{$email}">{$email}</a></p>
      <p><span class="foot-label">Address</span> 17 Woodlands Park Road, Offerton, Stockport SK2 5DE</p>
      {$social}
      <div class="foot-cta-row">
        <a class="foot-cta foot-cta--wa" href="https://wa.me/{$wa}?text=Hi%20iComply%2C%20I%20need%20a%20quote" target="_blank" rel="noopener">WhatsApp</a>
        <a class="foot-cta foot-cta--quote" href="{$contact}">Request a quote</a>
      </div>
    </div>
    <div class="foot-drops">
      <details class="foot-drop" open>
        <summary>Services <span>({$svcCount})</span></summary>
        <a class="foot-all" href="{$svcHub}">All services hub →</a>
        {$svcDrop}
      </details>
      <details class="foot-drop">
        <summary>Areas <span>({$areaCount})</span></summary>
        <a class="foot-all" href="{$areaHub}">All towns hub →</a>
        {$areaDrop}
      </details>
      <details class="foot-drop">
        <summary>Keyword hubs <span>({$kwCount})</span></summary>
        <a class="foot-all" href="{$kwHub}">All keyword hubs →</a>
        {$kwDrop}
      </details>
      <details class="foot-drop">
        <summary>Keyword × town matrix</summary>
        {$matrixDrop}
      </details>
      <details class="foot-drop">
        <summary>Resources</summary>
        {$resDrop}
      </details>
      <details class="foot-drop">
        <summary>Shop / supplies</summary>
        <div class="foot-links">
          <a href="https://shop.icomplypropertyservices.co.uk/" target="_blank" rel="noopener">Shop</a>
          <a href="https://shop.icomplypropertyservices.co.uk/" target="_blank" rel="noopener">Products / trade materials</a>
          <a href="{$svcHub}">Services</a>
          <a href="{$areaHub}">Areas</a>
          <a href="{$contact}">Contact</a>
          <a href="/shop/">Trade shop hubs</a>
          <a href="/shop/electrical/">Electrical</a>
          <a href="/shop/fire/">Fire</a>
          <a href="/shop/security/">Security</a>
          <a href="/shop/gas/">Gas</a>
          <!-- Marketing https://marketing.icomplypropertyservices.co.uk — optional until DNS live -->
        </div>
      </details>
      <details class="foot-drop">
        <summary>Packages / Landlords</summary>
        {$pkgDrop}
      </details>
      <details class="foot-drop">
        <summary>Contact &amp; legal</summary>
        {$legalDrop}
      </details>
    </div>
    <div class="foot-base">
      <div>© {$year} {$brand}. All rights reserved.</div>
      <div class="foot-base-links">
        <a href="{$contact}">Contact</a>
        <a href="{$privacy}">Privacy</a>
        <a href="{$terms}">Terms</a>
        <a href="{$siteMap}">Site map</a>
      </div>
    </div>
  </div>
</footer>
<a href="https://wa.me/{$wa}?text=Hi%20Icomply%2C%20I%20need%20a%20quote%20for%20compliance%20services" target="_blank" rel="noopener" aria-label="WhatsApp" class="wa-float">💬</a>
<div id="mobile-sticky-cta" class="mobile-sticky-cta">
  <a href="{$phoneHref}">Call {$phone}</a>
  <a class="sticky-quote" href="{$contact}">Free quote</a>
</div>
HTML;
}
