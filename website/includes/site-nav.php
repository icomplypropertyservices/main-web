<?php
/**
 * Mega header + footer inventory dropdowns.
 * Keyword×town (200k+) is reached via hubs — not dumped as a flat list.
 */
declare(strict_types=1);

function icomplyNavH(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** @return array<string,mixed> */
function icomplyNavCatalog(): array
{
    static $c = null;
    if ($c !== null) {
        return $c;
    }

    $services = getServices();
    $areas = getAreas();
    $cats = function_exists('getServiceCategories') ? getServiceCategories() : [];
    $keywords = getMajorKeywords();

    $catBlocks = [];
    $used = [];
    foreach ($cats as $catKey => $cat) {
        $list = function_exists('getServicesInCategory') ? getServicesInCategory((string)$catKey) : [];
        if (!$list) {
            continue;
        }
        $kwForCat = [];
        foreach ($list as $slug => $name) {
            $used[$slug] = true;
            $kws = function_exists('getKeywordsForService') ? getKeywordsForService((string)$slug) : [];
            if ($kws) {
                $kwForCat[$slug] = [
                    'name' => (string)$name,
                    'keywords' => $kws,
                ];
            }
        }
        $catBlocks[(string)$catKey] = [
            'label' => (string)($cat['label'] ?? $catKey),
            'blurb' => (string)($cat['blurb'] ?? ''),
            'services' => $list,
            'keywords' => $kwForCat,
        ];
    }
    $orphan = [];
    foreach ($services as $slug => $name) {
        if (empty($used[$slug])) {
            $orphan[$slug] = $name;
        }
    }
    if ($orphan) {
        $catBlocks['other'] = [
            'label' => 'Other services',
            'blurb' => '',
            'services' => $orphan,
            'keywords' => [],
        ];
    }

    $areasByLetter = [];
    foreach ($areas as $area) {
        $area = (string)$area;
        $letter = strtoupper(substr($area, 0, 1));
        if ($letter === '') {
            $letter = '#';
        }
        $areasByLetter[$letter][] = $area;
    }
    ksort($areasByLetter);

    $popularKw = function_exists('getPopularKeywordSlugs') ? getPopularKeywordSlugs() : [];
    $featuredKw = [];
    foreach ($popularKw as $slug) {
        if (isset($keywords[$slug])) {
            $featuredKw[$slug] = (string)$keywords[$slug]['name'];
        }
    }

    $c = [
        'services' => $services,
        'areas' => $areas,
        'cats' => $catBlocks,
        'keywords' => $keywords,
        'areasByLetter' => $areasByLetter,
        'featuredKw' => $featuredKw,
        'popularAreas' => array_values(array_filter(
            ['Manchester', 'Stockport', 'Salford', 'Bolton', 'Oldham', 'Rochdale', 'Wigan', 'Liverpool', 'Preston', 'Chester', 'Warrington', 'Blackpool'],
            static fn($a) => in_array($a, $areas, true)
        )),
        'resources' => icomplyNavResourceLinks(),
        'packages' => icomplyNavPackageLinks(),
        'legal' => [
            ['href' => url('/privacy.php'), 'label' => 'Privacy policy'],
            ['href' => url('/terms.php'), 'label' => 'Terms & conditions'],
            ['href' => url('/pages/site-map.php'), 'label' => 'HTML site map'],
            ['href' => url('/sitemap.xml'), 'label' => 'XML sitemap'],
        ],
        'home' => rtrim(SITE_URL, '/') . '/',
        'phone' => defined('PHONE') ? PHONE : '',
        'phoneHref' => 'tel:' . preg_replace('/\s+/', '', defined('PHONE') ? PHONE : ''),
        'whatsapp' => defined('WHATSAPP') ? WHATSAPP : '',
        'email' => defined('EMAIL') ? EMAIL : '',
        'brand' => defined('SITE_NAME') ? SITE_NAME : 'Icomply Property Services',
        'js' => url('/assets/js/site-nav.js'),
        'logo' => url('/assets/images/favicon-32.png'),
    ];
    return $c;
}

/** @return list<array{href:string,label:string}> */
function icomplyNavResourceLinks(): array
{
    $links = [
        ['href' => url('/pages/resources/index.php'), 'label' => 'All resources'],
        ['href' => url('/pages/resources/index.php') . '#batch-a', 'label' => 'Batch A — days 1–5'],
        ['href' => url('/pages/resources/index.php') . '#batch-b', 'label' => 'Batch B — days 6–14'],
        ['href' => url('/pages/resources/index.php') . '#batch-c', 'label' => 'Batch C — SEO hubs'],
    ];
    $landers = [
        '/pages/landlord-certificates' => 'Landlord certificates',
        '/pages/gas-safety-certificate' => 'Gas safety certificate',
        '/pages/fire-risk-assessment' => 'Fire risk assessment',
        '/pages/electrical-safety-landlords' => 'Electrical safety for landlords',
        '/pages/commercial-fire-safety' => 'Commercial fire safety',
        '/pages/stockport-property-compliance' => 'Stockport property compliance',
        '/pages/manchester-property-compliance' => 'Manchester property compliance',
        '/pages/portable-appliance-testing' => 'PAT testing',
        '/pages/smoke-carbon-monoxide-alarms' => 'Smoke & CO alarms',
        '/pages/energy-performance-certificates' => 'Energy performance certificates',
        '/pages/emergency-lighting-compliance' => 'Emergency lighting compliance',
        '/pages/fire-door-compliance' => 'Fire door compliance',
    ];
    foreach ($landers as $path => $label) {
        $links[] = ['href' => url($path), 'label' => $label];
    }

    $dir = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/pages/resources';
    if (is_dir($dir)) {
        $files = glob($dir . '/*.php') ?: [];
        sort($files);
        foreach ($files as $file) {
            $slug = basename($file, '.php');
            if ($slug === 'index') {
                continue;
            }
            $links[] = [
                'href' => url('/pages/resources/' . $slug . '.php'),
                'label' => function_exists('keywordDisplayName') ? keywordDisplayName($slug) : $slug,
            ];
        }
    }

    $seen = [];
    $out = [];
    foreach ($links as $row) {
        if (isset($seen[$row['href']])) {
            continue;
        }
        $seen[$row['href']] = true;
        $out[] = $row;
    }
    return $out;
}

/** @return list<array{href:string,label:string}> */
function icomplyNavPackageLinks(): array
{
    return [
        ['href' => url('/pages/packages.php'), 'label' => 'All packages'],
        ['href' => url('/pages/packages/let-ready.php'), 'label' => 'Let-ready package'],
        ['href' => url('/pages/packages/fire-ready.php'), 'label' => 'Fire-ready package'],
        ['href' => url('/pages/packages/workplace-essentials.php'), 'label' => 'Workplace essentials'],
        ['href' => url('/pages/landlords.php'), 'label' => 'Landlords'],
        ['href' => url('/pages/commercial.php'), 'label' => 'Commercial / FM'],
        ['href' => url('/pages/care-homes.php'), 'label' => 'Care homes'],
        ['href' => url('/pages/pricing.php'), 'label' => 'Pricing guide (POA)'],
        ['href' => url('/pages/maintenance.php'), 'label' => 'Maintenance contracts'],
        ['href' => url('/pages/emergency.php'), 'label' => 'Emergency call-out'],
        ['href' => url('/pages/ev-chargers.php'), 'label' => 'EV chargers'],
        ['href' => url('/pages/about.php'), 'label' => 'About'],
        ['href' => url('/pages/faq.php'), 'label' => 'FAQ'],
        ['href' => url('/pages/reviews.php'), 'label' => 'Reviews'],
    ];
}

function icomplyNavLink(string $href, string $label, string $class = ''): string
{
    $cls = $class !== '' ? ' class="' . icomplyNavH($class) . '"' : '';
    return '<a href="' . icomplyNavH($href) . '"' . $cls . '>' . icomplyNavH($label) . '</a>';
}

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
    $kwHub = icomplyNavH(url('/pages/keywords/index.php'));
    $resHub = icomplyNavH(url('/pages/resources/index.php'));
    $pkgHub = icomplyNavH(url('/pages/packages.php'));
    $contact = icomplyNavH(url('/contact.php'));

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

    $kwCols = '<div class="mega-col">';
    $kwCols .= '<p class="mega-col-title">Featured keyword hubs</p>';
    foreach ($n['featuredKw'] as $slug => $name) {
        $kwCols .= icomplyNavLink(url('/pages/keywords/' . rawurlencode((string)$slug) . '.php'), (string)$name);
    }
    $kwCols .= icomplyNavLink(url('/pages/keywords/index.php'), 'All ' . count($n['keywords']) . ' keyword hubs →', 'mega-more');
    $kwCols .= '</div>';
    $kwShown = 0;
    foreach ($n['cats'] as $cat) {
        if ($kwShown >= 3) {
            break;
        }
        if (empty($cat['keywords'])) {
            continue;
        }
        $kwShown++;
        $kwCols .= '<div class="mega-col"><p class="mega-col-title">' . icomplyNavH($cat['label']) . '</p>';
        $i = 0;
        foreach ($cat['keywords'] as $svcSlug => $block) {
            foreach ($block['keywords'] as $kSlug => $meta) {
                if ($i >= 8) {
                    break 2;
                }
                $kwCols .= icomplyNavLink(url('/pages/keywords/' . rawurlencode((string)$kSlug) . '.php'), (string)($meta['name'] ?? $kSlug));
                $i++;
            }
        }
        $kwCols .= '</div>';
    }

    $resCols = '<div class="mega-col">';
    $resCols .= '<p class="mega-col-title">Guides &amp; hubs</p>';
    foreach (array_slice($n['resources'], 0, 16) as $row) {
        $resCols .= icomplyNavLink($row['href'], $row['label']);
    }
    $resCols .= icomplyNavLink(url('/pages/resources/index.php'), 'All resources →', 'mega-more');
    $resCols .= '</div><div class="mega-col"><p class="mega-col-title">Water &amp; asbestos</p>';
    $resCols .= icomplyNavLink(url('/pages/services/legionella-risk-assessment.php'), 'Legionella risk assessment');
    $resCols .= icomplyNavLink(url('/pages/services/asbestos-survey.php'), 'Asbestos survey');
    $resCols .= icomplyNavLink(url('/pages/resources/legionella-risk-assessment.php'), 'Legionella guide');
    $resCols .= icomplyNavLink(url('/pages/resources/asbestos-survey.php'), 'Asbestos guide');
    $resCols .= '</div>';

    $pkgCols = '<div class="mega-col">';
    $pkgCols .= '<p class="mega-col-title">Packages &amp; audiences</p>';
    foreach ($n['packages'] as $row) {
        $pkgCols .= icomplyNavLink($row['href'], $row['label']);
    }
    $pkgCols .= '</div>';

    $drawer = icomplyMobileDrawerHtml($n);
    $svcCount = count($n['services']);
    $areaCount = count($n['areas']);
    $kwCount = count($n['keywords']);

    return <<<HTML
<header class="site-header mega-header" data-site-header>
  <div class="mega-bar">
    <a class="mega-logo" href="{$home}">
      <img src="{$logo}" width="32" height="32" alt="" class="mega-logo-img">
      <span>{$brand}</span>
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
        <button type="button" class="mega-trigger" aria-expanded="false" aria-controls="mega-keywords" aria-haspopup="true">Keywords</button>
        <div id="mega-keywords" class="mega-panel" hidden>
          <div class="mega-panel-inner mega-panel-inner--wide">
            <div class="mega-panel-head"><a href="{$kwHub}">All {$kwCount} keyword hubs →</a></div>
            <div class="mega-grid">{$kwCols}</div>
          </div>
        </div>
      </div>
      <div class="mega-item" data-mega>
        <button type="button" class="mega-trigger" aria-expanded="false" aria-controls="mega-resources" aria-haspopup="true">Resources</button>
        <div id="mega-resources" class="mega-panel" hidden>
          <div class="mega-panel-inner">
            <div class="mega-panel-head"><a href="{$resHub}">Resource library →</a></div>
            <div class="mega-grid">{$resCols}</div>
          </div>
        </div>
      </div>
      <div class="mega-item" data-mega>
        <button type="button" class="mega-trigger" aria-expanded="false" aria-controls="mega-packages" aria-haspopup="true">Packages</button>
        <div id="mega-packages" class="mega-panel" hidden>
          <div class="mega-panel-inner">
            <div class="mega-panel-head"><a href="{$pkgHub}">Packages &amp; landlords →</a></div>
            <div class="mega-grid">{$pkgCols}</div>
          </div>
        </div>
      </div>
      <a class="nav-link" href="{$contact}">Contact</a>
      <a class="mega-wa" href="https://wa.me/{$wa}" target="_blank" rel="noopener">WhatsApp</a>
    </nav>
    <div class="mega-tools">
      <a class="mega-phone" href="{$phoneHref}">{$phone}</a>
      <a class="mega-quote" href="{$contact}">Book quote</a>
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

    $svc = '';
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

    $kws = '<a class="drawer-all" href="' . icomplyNavH(url('/pages/keywords/index.php')) . '">All keyword hubs →</a>';
    foreach ($n['featuredKw'] as $slug => $name) {
        $kws .= icomplyNavLink(url('/pages/keywords/' . rawurlencode((string)$slug) . '.php'), (string)$name);
    }

    $res = '';
    foreach (array_slice($n['resources'], 0, 20) as $row) {
        $res .= icomplyNavLink($row['href'], $row['label']);
    }
    $pkg = '';
    foreach ($n['packages'] as $row) {
        $pkg .= icomplyNavLink($row['href'], $row['label']);
    }

    return <<<HTML
<div id="mega-drawer" class="mega-drawer" hidden>
  <nav class="mega-drawer-inner" aria-label="Mobile">
    <a href="{$home}">Home</a>
    <details class="drawer-acc" open><summary>Services</summary><div>{$svc}</div></details>
    <details class="drawer-acc"><summary>Areas</summary><div>{$areas}</div></details>
    <details class="drawer-acc"><summary>Keywords</summary><div>{$kws}</div></details>
    <details class="drawer-acc"><summary>Resources</summary><div>{$res}</div></details>
    <details class="drawer-acc"><summary>Packages / Landlords</summary><div>{$pkg}</div></details>
    <a href="{$siteMap}">Site map</a>
    <a class="drawer-cta" href="{$phoneHref}">Call {$phone}</a>
    <a class="drawer-cta drawer-cta--wa" href="https://wa.me/{$wa}" target="_blank" rel="noopener">WhatsApp</a>
    <a class="drawer-cta drawer-cta--quote" href="{$contactDrawer}">Contact</a>
  </nav>
</div>
HTML;
}

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
      <p>Property compliance — electrical, fire, gas, water hygiene and asbestos surveys across Greater Manchester and the North West. Quotes are scoped; Legionella and asbestos are POA.</p>
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
