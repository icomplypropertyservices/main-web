<?php
/**
 * AOV + vehicle-barrier manufacturer boards.
 * Every listed brand gets a wordmark, product lines, an honest note,
 * a real link, and the shared quote helper. CAME is the barrier partner.
 * Supply prices: published CAME 5m packs and the locked AOV kit list only.
 * Tunstall is never rendered here.
 */
declare(strict_types=1);

function icomplyMfrShowcaseServices(): array
{
    return ['aov-air-handling', 'barriers'];
}

function icomplyMfrShowcaseApplies(string $serviceSlug): bool
{
    return in_array(areaSlug($serviceSlug), icomplyMfrShowcaseServices(), true);
}

/** @return array<string,mixed> */
function icomplyMfrCoverageData(): array
{
    $data = loadJsonData('mfr-coverage', []);
    return is_array($data) ? $data : [];
}

/** @return list<string> */
function icomplyMfrExcludedSlugs(): array
{
    $raw = icomplyMfrCoverageData()['exclude'] ?? ['tunstall'];
    $out = [];
    foreach ((array)$raw as $name) {
        $out[] = areaSlug((string)$name);
    }
    if (!in_array('tunstall', $out, true)) {
        $out[] = 'tunstall';
    }
    return $out;
}

/** @return list<array<string,mixed>> */
function icomplyMfrCoverageRows(string $serviceSlug): array
{
    $serviceSlug = areaSlug($serviceSlug);
    $rows = icomplyMfrCoverageData()[$serviceSlug] ?? [];
    if (!is_array($rows)) {
        return [];
    }
    $banned = icomplyMfrExcludedSlugs();
    $clean = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = areaSlug((string)($row['slug'] ?? $row['name'] ?? ''));
        $name = trim((string)($row['name'] ?? ''));
        if ($slug === '' || $name === '' || in_array($slug, $banned, true) || in_array(areaSlug($name), $banned, true)) {
            continue;
        }
        $row['slug'] = $slug;
        $row['name'] = $name;
        $clean[] = $row;
    }
    usort($clean, static function (array $a, array $b): int {
        $ap = !empty($a['partner']) ? 0 : 1;
        $bp = !empty($b['partner']) ? 0 : 1;
        if ($ap !== $bp) {
            return $ap <=> $bp;
        }
        return strcasecmp((string)$a['name'], (string)$b['name']);
    });
    return $clean;
}

/** @return list<string> */
function icomplyCoverageManufacturerNames(string $serviceSlug): array
{
    $names = [];
    foreach (icomplyMfrCoverageRows($serviceSlug) as $row) {
        $names[] = (string)$row['name'];
    }
    return $names;
}

/** Published CAME 5m supply packs. Must stay identical to the products hub. */
function icomplyCameBarrierPacks(): array
{
    return [
        ['sku' => 'BAR-5M-STD', 'handle' => 'bar-5m-std', 'price' => '£5,850.00', 'blurb' => 'Standard 5m barrier pack'],
        ['sku' => 'BAR-5M-VIDEX', 'handle' => 'bar-5m-videx', 'price' => '£7,441.83', 'blurb' => '5m barrier + Videx'],
        ['sku' => 'BAR-5M-PAXTON', 'handle' => 'bar-5m-paxton', 'price' => '£8,375.45', 'blurb' => '5m barrier + Paxton'],
        ['sku' => 'BAR-5M-GSM', 'handle' => 'bar-5m-gsm', 'price' => '£7,393.18', 'blurb' => '5m barrier + GSM'],
        ['sku' => 'BAR-5M-ALLIN', 'handle' => 'bar-5m-allin', 'price' => '£5,199.99', 'blurb' => '5m barrier all-in (Jack-confirmed)'],
    ];
}

function icomplyCamePackBySku(string $sku): ?array
{
    foreach (icomplyCameBarrierPacks() as $pack) {
        if (strcasecmp($pack['sku'], $sku) === 0) {
            return $pack;
        }
    }
    return null;
}

function icomplyAreaPopulation(string $area): ?int
{
    $data = loadJsonData('area-population', []);
    $map = $data['population'] ?? $data;
    if (!is_array($map) || !array_key_exists($area, $map)) {
        return null;
    }
    return (int)$map[$area];
}

function icomplyMfrTownIndexable(string $area): bool
{
    $pop = icomplyAreaPopulation($area);
    return $pop !== null && $pop > 10000;
}

/** @return list<string> */
function icomplyMfrIndexableTowns(): array
{
    $out = [];
    foreach (getAreas() as $area) {
        if (icomplyMfrTownIndexable((string)$area)) {
            $out[] = (string)$area;
        }
    }
    return $out;
}

function icomplyMfrPageTitle(string $serviceName, string $area): string
{
    $title = $area === ''
        ? ($serviceName . ' | North West')
        : ($serviceName . ' in ' . $area . ' | iComply');
    if (mb_strlen($title) <= 70) {
        return $title;
    }
    $short = $area === '' ? $serviceName : ($serviceName . ' in ' . $area);
    $withBrand = $short . ' | iComply';
    if (mb_strlen($withBrand) <= 70) {
        return $withBrand;
    }
    return mb_substr($short, 0, 58) . ' | iComply';
}

function icomplyMfrMetaDesc(string $serviceSlug, string $area): string
{
    $where = $area !== '' ? $area : 'the North West';
    if (areaSlug($serviceSlug) === 'barriers') {
        $text = 'CAME partner vehicle barriers in ' . $where . '. Published 5m pack prices are supply only, ex VAT. Installation is quoted after survey. Call 07517806082.';
    } else {
        $text = 'AOV and smoke control in ' . $where . '. Every brand we install or service is listed, with product lines and a quote helper. Installation is POA. Call 07517806082.';
    }
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? $text;
    if (mb_strlen($text) > 160) {
        $text = rtrim(mb_substr($text, 0, 157), " \t,.;") . '.';
    }
    return $text;
}

/** @return list<array{0:string,1:string}> */
function icomplyMfrFaqs(string $serviceSlug): array
{
    if (areaSlug($serviceSlug) === 'barriers') {
        return [
            ['Are you a CAME barrier partner?', 'Yes. New barrier supply on these pages is through CAME. The five published 5m pack prices are supply only, ex VAT. Installation is quoted after survey.'],
            ['Can you repair a barrier from another manufacturer?', 'We will look at existing booms from the brands listed. Parts and labour are POA. If a safe repair is not realistic we say so and can quote a CAME replacement.'],
            ['Do you publish a price for installation?', 'No. Civils, power, loops and commissioning are POA. Only the CAME supply packs already published on this site have figures.'],
        ];
    }
    if (areaSlug($serviceSlug) === 'aov-air-handling') {
        return [
            ['Which AOV manufacturers do you work on?', 'Every brand in the manufacturer grid on this page. If your panel is not listed, call 07517806082 and we will say whether we can attend it.'],
            ['Are brand prices listed?', 'No. The iComply AOV equipment kit list is separate and ex VAT. Manufacturer supply and all installation are POA after survey.'],
            ['Can you service a system that is already fitted?', 'Yes, once we have identified the manufacturer and the fault. We do not guess a model from a photo of a closed vent.'],
        ];
    }
    return [];
}

/** @return array<string,mixed>|null */
function icomplyMfrServiceCopy(string $serviceSlug): ?array
{
    if (areaSlug($serviceSlug) === 'barriers') {
        return [
            'intro' => [
                'iComply supplies vehicle barriers as a CAME partner and services existing booms from the other manufacturers listed below. The only prices on this page are the CAME 5m supply packs already published for BAR-5M-STD, BAR-5M-VIDEX, BAR-5M-PAXTON, BAR-5M-GSM and BAR-5M-ALLIN.',
                'Installation, induction loops, safety edges, power and commissioning are quoted after we see the lane. We do not invent a call-out fee. Phone 07517806082.',
            ],
            'pillars' => [
                ['title' => 'CAME partner supply', 'text' => 'New lanes are specified on CAME. Pack prices below are supply only, ex VAT.'],
                ['title' => 'Existing barriers', 'text' => 'We fault-find the other brands on this page when a safe repair is still realistic.'],
                ['title' => 'Survey before install', 'text' => 'Lane width, loops, power and safety devices are priced after the visit, not from a menu.'],
            ],
        ];
    }
    if (areaSlug($serviceSlug) === 'aov-air-handling') {
        return [
            'intro' => [
                'iComply installs and services automatic opening vents and the smoke-control plant listed below. Each card names the product lines we actually work on for that manufacturer.',
                'There is no manufacturer price list here. The iComply AOV equipment kits have locked ex VAT supply figures. Labour, inspection and commissioning stay POA. Phone 07517806082.',
            ],
            'pillars' => [
                ['title' => 'Brand identified first', 'text' => 'We name the panel or actuator from the label on site before ordering parts.'],
                ['title' => 'Smoke strategy', 'text' => 'Work follows the fire strategy, BS 9991 and BS EN 12101. We do not claim a certificate we have not issued.'],
                ['title' => 'POA installation', 'text' => 'Actuator swaps, panel faults and new stair schemes are quoted after survey.'],
            ],
        ];
    }
    return null;
}

function icomplyMfrLocalHtml(string $serviceSlug, string $area): string
{
    if ($area === '' || !function_exists('area_profile')) {
        return '';
    }
    $p = area_profile($area);
    $pop = icomplyAreaPopulation($area);
    $gate = ($pop !== null && $pop > 10000)
        ? 'This town is in the over-10,000 population set, so this page is indexed.'
        : 'This place is under 10,000 population. The indexed town list is on the service hub. The manufacturers below are the same set.';
    $kind = areaSlug($serviceSlug) === 'barriers'
        ? 'Barrier work here is mostly car parks, yards and residential gates among ' . $p['stock'] . '.'
        : 'Smoke-vent work here is mostly stairs and common parts among ' . $p['stock'] . '.';
    $html = '<p class="mt-5 text-lg text-zinc-700 leading-relaxed">' . htmlspecialchars($kind . ' Postcodes we treat as local are ' . $p['districts'] . '. Travel from our Stockport SK2 base is ' . $p['travel'] . '.', ENT_QUOTES, 'UTF-8') . '</p>';
    $html .= '<p class="mt-4 text-lg text-zinc-700 leading-relaxed">' . htmlspecialchars($gate . ' Phone 07517806082.', ENT_QUOTES, 'UTF-8') . '</p>';
    return $html;
}

function icomplyMfrWordmark(string $name, bool $partner): string
{
    $safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $len = mb_strlen($name);
    $size = $len > 22 ? 16 : ($len > 16 ? 18 : ($len > 12 ? 22 : 26));
    $bar = $partner ? '<rect x="0" y="0" width="6" height="72" fill="#FF6B00"/>' : '';
    $label = $partner ? '<text x="304" y="18" text-anchor="end" fill="#FF6B00" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="700">PARTNER</text>' : '';
    return '<svg class="mfr-wordmark" viewBox="0 0 320 72" role="img" aria-label="' . $safe . ' wordmark">'
        . '<rect width="320" height="72" rx="12" fill="#0B1F3A"/>' . $bar . $label
        . '<text x="160" y="44" text-anchor="middle" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="' . $size . '" font-weight="700">' . $safe . '</text>'
        . '</svg>';
}

/**
 * @param array<string,mixed> $catalog
 * @return array<string,mixed>
 */
function icomplyApplyMfrCoverageCatalog(array $catalog): array
{
    $services = function_exists('getServices') ? getServices() : [];
    foreach (icomplyMfrShowcaseServices() as $serviceSlug) {
        foreach (icomplyMfrCoverageRows($serviceSlug) as $row) {
            $slug = (string)$row['slug'];
            $name = (string)$row['name'];
            if (!isset($catalog[$slug])) {
                $blurb = (string)$row['framing'];
                if (mb_strlen($blurb) > 160) {
                    $blurb = rtrim(mb_substr($blurb, 0, 157), " \t,.;") . '.';
                }
                $products = [];
                if (!empty($row['partner'])) {
                    foreach (icomplyCameBarrierPacks() as $pack) {
                        $products[] = [
                            'id' => strtolower($pack['sku']),
                            'title' => $pack['sku'] . ' — ' . $pack['blurb'],
                            'blurb' => 'CAME partner supply pack. Installation POA.',
                            'price' => $pack['price'],
                            'handle' => '',
                            'public_href' => url('/pages/services/barriers.php') . '#mfr-came',
                            'image' => '/assets/images/services/access-control.jpg',
                            'badge' => '',
                        ];
                    }
                }
                $catalog[$slug] = [
                    'name' => $name,
                    'slug' => $slug,
                    'services' => [$serviceSlug],
                    'blurb' => $blurb,
                    'seo_title' => $name . ' | North West',
                    'seo_desc' => $blurb,
                    'seo_keywords' => $name . ', ' . ($services[$serviceSlug] ?? $serviceSlug) . ', North West, Stockport',
                    'products' => $products,
                    'featured' => !empty($row['partner']),
                ];
            } elseif (!in_array($serviceSlug, $catalog[$slug]['services'] ?? [], true)) {
                $catalog[$slug]['services'][] = $serviceSlug;
            }
        }
    }
    return $catalog;
}

function icomplyMfrShowcaseHtml(string $serviceSlug, string $area = ''): string
{
    if (!icomplyMfrShowcaseApplies($serviceSlug)) {
        return '';
    }
    $serviceSlug = areaSlug($serviceSlug);
    $rows = icomplyMfrCoverageRows($serviceSlug);
    if (!$rows) {
        return '';
    }
    $services = getServices();
    $serviceName = $services[$serviceSlug] ?? $serviceSlug;
    $place = $area !== '' ? $area : 'the North West';
    $isBarriers = $serviceSlug === 'barriers';
    $phone = defined('PHONE') ? (string)PHONE : '07517806082';
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', $phone);
    $wa = defined('WHATSAPP') ? (string)WHATSAPP : '447517806082';

    $kitOptions = '';
    if ($isBarriers) {
        foreach (icomplyCameBarrierPacks() as $pack) {
            $kitOptions .= '<option value="' . htmlspecialchars($pack['sku'] . ' ' . $pack['price'] . ' ex VAT supply, install POA', ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($pack['sku'] . ' — ' . $pack['price'] . ' ex VAT supply', ENT_QUOTES, 'UTF-8') . '</option>';
        }
    } else {
        $aovFile = SITE_ROOT . '/includes/aov-kit-prices.php';
        if (is_file($aovFile)) {
            require_once $aovFile;
        }
        if (function_exists('icomplyAovKitSkuCatalog')) {
            foreach (icomplyAovKitSkuCatalog() as $sku => $row) {
                $kitOptions .= '<option value="' . htmlspecialchars($row['label'] . ' ' . $row['price'] . ' ex VAT supply, install POA', ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($row['label'] . ' — ' . $row['price'] . ' ex VAT supply', ENT_QUOTES, 'UTF-8') . '</option>';
            }
        }
    }

    $brandOptions = '';
    $cards = '';
    $listItems = [];
    $i = 0;
    foreach ($rows as $row) {
        $i++;
        $name = (string)$row['name'];
        $slug = (string)$row['slug'];
        $partner = !empty($row['partner']);
        $brandOptions .= '<option value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
        $href = htmlspecialchars(url('/pages/manufacturers/' . $slug . '.php'), ENT_QUOTES, 'UTF-8');
        $lines = '';
        foreach ((array)($row['lines'] ?? []) as $line) {
            $lines .= '<li>' . htmlspecialchars((string)$line, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $site = trim((string)($row['site'] ?? ''));
        $siteHtml = '';
        if ($site !== '' && preg_match('#^https://#', $site)) {
            $siteHtml = '<a class="mfr-link" href="' . htmlspecialchars($site, ENT_QUOTES, 'UTF-8') . '" rel="noopener noreferrer">Manufacturer site</a>';
        }
        $packHtml = '';
        if ($partner) {
            $packHtml .= '<table class="mfr-kit"><caption>CAME partner supply packs (ex VAT). Installation POA.</caption><tbody>';
            foreach (icomplyCameBarrierPacks() as $pack) {
                $packHtml .= '<tr><th scope="row">' . htmlspecialchars($pack['sku'], ENT_QUOTES, 'UTF-8') . '</th><td>'
                    . htmlspecialchars($pack['blurb'], ENT_QUOTES, 'UTF-8') . '</td><td>'
                    . htmlspecialchars($pack['price'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
            }
            $packHtml .= '</tbody></table>';
        } elseif (!empty($row['pack_sku'])) {
            $pack = icomplyCamePackBySku((string)$row['pack_sku']);
            if ($pack) {
                $packHtml = '<p class="mfr-pack-note">Published combined pack <strong>' . htmlspecialchars($pack['sku'], ENT_QUOTES, 'UTF-8')
                    . '</strong>: ' . htmlspecialchars($pack['price'], ENT_QUOTES, 'UTF-8') . ' ex VAT supply. Installation POA.</p>';
            }
        }
        $badge = $partner ? '<p class="mfr-partner-kicker">Barrier partner</p>' : '';
        $cards .= '<article class="mfr-card bg-white border rounded-3xl' . ($partner ? ' mfr-card--partner' : '') . '" id="mfr-' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '">'
            . $badge
            . icomplyMfrWordmark($name, $partner)
            . '<h3>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h3>'
            . '<p class="mfr-framing">' . htmlspecialchars((string)$row['framing'], ENT_QUOTES, 'UTF-8') . '</p>'
            . '<h4>Product lines</h4><ul class="mfr-lines">' . $lines . '</ul>'
            . $packHtml
            . '<div class="mfr-actions">'
            . '<a class="mfr-link" href="' . $href . '">Brand page</a>'
            . $siteHtml
            . '<button type="button" class="mfr-link mfr-quote-btn" data-mfr-quote="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">Quote helper</button>'
            . '<a class="mfr-link" href="' . htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</a>'
            . '</div></article>';
        $listItems[] = [
            '@type' => 'ListItem',
            'position' => $i,
            'name' => $name,
            'url' => url('/pages/manufacturers/' . $slug . '.php'),
        ];
    }

    $townHtml = '';
    if ($area === '') {
        $townHtml .= '<nav class="mfr-towns" aria-label="Towns over 10000 population"><h3>Town pages (population over 10,000)</h3><ul>';
        foreach (icomplyMfrIndexableTowns() as $town) {
            $townHtml .= '<li><a href="' . htmlspecialchars(url('/pages/' . $serviceSlug . '/' . areaSlug($town) . '.php'), ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($serviceName . ' in ' . $town, ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        $townHtml .= '</ul></nav>';
    }

    $csrf = htmlspecialchars((string)($_SESSION['csrf'] ?? ''), ENT_QUOTES, 'UTF-8');
    $serviceValue = htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8');
    $heading = $isBarriers
        ? 'Barrier manufacturers in ' . $place
        : 'AOV manufacturers in ' . $place;
    $lead = $isBarriers
        ? 'CAME is the partner for new barrier supply. Every other brand is one we will service when the existing lane still allows a safe repair. Count: ' . count($rows) . '.'
        : 'Every AOV and smoke-control manufacturer we install or service is on this grid. Count: ' . count($rows) . '.';

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $heading,
        'numberOfItems' => count($rows),
        'itemListElement' => $listItems,
    ];

    $aovStrip = '';
    if (!$isBarriers && function_exists('icomplyAovKitPriceStripHtml')) {
        $aovStrip = icomplyAovKitPriceStripHtml();
    }

    $html = '<section class="mfr-board" id="manufacturers" data-mfr-service="' . htmlspecialchars($serviceSlug, ENT_QUOTES, 'UTF-8') . '" data-mfr-count="' . count($rows) . '">';
    $html .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    $html .= '<div class="mfr-board-head"><p class="mfr-kicker">Manufacturers</p><h2>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h2>';
    $html .= '<p>' . htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') . ' Phone <a href="' . htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</a>.</p></div>';
    $html .= $aovStrip;
    $html .= '<form id="mfr-quote" class="mfr-wizard bg-white border rounded-3xl" action="' . htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') . '" method="post">';
    $html .= '<h3>' . ($isBarriers ? 'Barrier quote helper' : 'AOV quote helper') . '</h3>';
    $html .= '<p class="mfr-wizard-note">Choose the brand and the job. Supply figures appear only for published CAME packs'
        . ($isBarriers ? '' : ' or the iComply AOV kit list') . '. Installation stays POA.</p>';
    $html .= '<input type="hidden" name="csrf" value="' . $csrf . '">';
    $html .= '<input type="hidden" name="service" value="' . $serviceValue . '">';
    $html .= '<div class="mfr-wizard-grid">';
    $html .= '<label>Brand<select id="mfr-quote-brand" name="mfr_brand">' . $brandOptions . '</select></label>';
    $html .= '<label>Job<select id="mfr-quote-job" name="mfr_job"><option>New install</option><option>Service existing</option><option>Fault finding</option><option>Parts only</option></select></label>';
    $html .= '<label>' . ($isBarriers ? 'CAME supply pack' : 'iComply equipment kit (optional)') . '<select id="mfr-quote-kit"><option value="">Not selected — quote POA</option>' . $kitOptions . '</select></label>';
    $html .= '<label>Name<input name="name" required maxlength="120" autocomplete="name"></label>';
    $html .= '<label>Email<input type="email" name="email" required autocomplete="email"></label>';
    $html .= '<label>Phone<input type="tel" name="phone" required maxlength="40" autocomplete="tel"></label>';
    $html .= '</div>';
    $html .= '<label class="mfr-notes">Notes<textarea id="mfr-quote-notes" name="message" rows="4" required maxlength="5000" placeholder="Postcode, lane or stair, and what is on site."></textarea></label>';
    $html .= '<div class="mfr-actions"><button type="submit" class="mfr-submit">Send quote request</button>';
    $html .= '<a class="mfr-link" href="' . htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '</a>';
    $html .= '<a class="mfr-link" href="https://wa.me/' . htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') . '" rel="noopener noreferrer">WhatsApp</a></div>';
    $html .= '</form>';
    $html .= '<div class="mfr-grid">' . $cards . '</div>' . $townHtml;
    $html .= '<script>(function(){var form=document.getElementById("mfr-quote");if(!form)return;var brand=document.getElementById("mfr-quote-brand");var notes=document.getElementById("mfr-quote-notes");document.querySelectorAll("[data-mfr-quote]").forEach(function(btn){btn.addEventListener("click",function(){if(brand)brand.value=btn.getAttribute("data-mfr-quote")||"";form.scrollIntoView({behavior:"smooth",block:"start"});});});form.addEventListener("submit",function(){var kit=document.getElementById("mfr-quote-kit");var job=document.getElementById("mfr-quote-job");var place=' . json_encode($place) . ';var prefix="Brand: "+(brand?brand.value:"")+"\\nJob: "+(job?job.value:"")+"\\nKit: "+(kit&&kit.value?kit.value:"POA")+"\\nPlace: "+place+"\\n";if(notes && notes.value.indexOf("Brand: ")!==0){notes.value=prefix+notes.value;}});})();</script>';
    $html .= '</section>';
    return $html;
}
