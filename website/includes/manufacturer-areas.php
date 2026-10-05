<?php
/**
 * Manufacturer × area coverage, product lines, logos and unique local copy.
 * Nationwide: fire, AOV, barriers, access control, nurse call.
 * Other brands: Greater Manchester + Burnley only.
 * Tunstall is excluded.
 */
declare(strict_types=1);

require_once __DIR__ . '/mfr-wizards.php';

function manufacturerExcludedSlugs(): array
{
    return ['tunstall'];
}

function manufacturerIsExcluded(string $slug): bool
{
    return in_array(areaSlug($slug), manufacturerExcludedSlugs(), true);
}

/** @return list<string> */
function manufacturerNationwideServiceSlugs(): array
{
    return ['fire-alarms', 'aov-air-handling', 'barriers', 'access-control', 'nurse-call'];
}

/** Greater Manchester towns in areas.json, plus Burnley. */
function manufacturerLocalAreaNames(): array
{
    return [
        'Manchester', 'Salford', 'Bolton', 'Bury', 'Oldham', 'Rochdale', 'Stockport', 'Wigan',
        'Leigh', 'Atherton', 'Tyldesley', 'Horwich', 'Westhoughton', 'Farnworth', 'Kearsley', 'Little Lever',
        'Radcliffe', 'Whitefield', 'Prestwich', 'Swinton', 'Eccles', 'Walkden', 'Worsley', 'Pendlebury',
        'Irlam', 'Cadishead', 'Altrincham', 'Sale', 'Stretford', 'Urmston', 'Chorlton', 'Didsbury',
        'Withington', 'Wythenshawe', 'Cheadle', 'Cheadle Hulme', 'Bramhall', 'Hazel Grove', 'Marple', 'Romiley',
        'Hyde', 'Stalybridge', 'Dukinfield', 'Ashton-under-Lyne', 'Mossley', 'Droylsden', 'Denton', 'Failsworth',
        'Middleton', 'Chadderton', 'Heywood', 'Milnrow', 'Littleborough', 'Shaw', 'Royton', 'Lees',
        'Uppermill', 'Saddleworth', 'Burnley',
    ];
}

function manufacturerCoverageMode(array $entry): ?string
{
    $slug = (string)($entry['slug'] ?? '');
    if ($slug === '' || manufacturerIsExcluded($slug)) {
        return null;
    }
    $national = manufacturerNationwideServiceSlugs();
    foreach ($entry['services'] ?? [] as $service) {
        if (in_array((string)$service, $national, true)) {
            return 'nationwide';
        }
    }
    if (!empty($entry['services'])) {
        return 'local';
    }
    return null;
}

/** @return list<string> */
function manufacturerAreasFor(array $entry): array
{
    $mode = manufacturerCoverageMode($entry);
    if ($mode === null) {
        return [];
    }
    $known = getAreas();
    if ($mode === 'nationwide') {
        return array_values($known);
    }
    $want = array_fill_keys(manufacturerLocalAreaNames(), true);
    $out = [];
    foreach ($known as $area) {
        if (isset($want[$area])) {
            $out[] = $area;
        }
    }
    return $out;
}

function manufacturerAreaAllowed(array $entry, string $area): bool
{
    return in_array($area, manufacturerAreasFor($entry), true);
}

function manufacturerCoverageLabel(array $entry): string
{
    return manufacturerCoverageMode($entry) === 'nationwide'
        ? 'Every published town'
        : 'Greater Manchester and Burnley';
}

/** @return array<string, list<array{0:string,1:string}>> */
function manufacturerCuratedProductLines(): array
{
    static $rows = null;
    if ($rows === null) {
        $path = SITE_ROOT . '/data/manufacturer-product-lines.php';
        $loaded = is_file($path) ? require $path : [];
        $rows = is_array($loaded) ? $loaded : [];
    }
    return $rows;
}

/** @return list<array{0:string,1:string}> */
function manufacturerFallbackLineRows(string $service, string $brand): array
{
    $map = [
        'fire-alarms' => [
            ['Addressable panels', "Addressable panels where {$brand} is the system already on the wall."],
            ['Detectors and call points', "Detectors, call points and bases matched to the {$brand} protocol."],
            ['Service batteries', "Panel batteries and service parts for planned {$brand} visits."],
        ],
        'aov-air-handling' => [
            ['Actuators', "Actuators and link arms for {$brand} smoke vents."],
            ['Control panels', "Smoke-control panels and overrides for {$brand} systems."],
            ['Vents and shafts', "Vents, shafts and interfaces supplied with {$brand} controls."],
        ],
        'barriers' => [
            ['Vehicle barriers', "{$brand} barrier arms and housings. Installation is POA."],
            ['Gate operators', "Sliding and swing operators in the {$brand} range."],
            ['Safety devices', "Loops, edges and photocells that sit with a {$brand} install."],
        ],
        'access-control' => [
            ['Readers', "Readers and credentials for {$brand} doors."],
            ['Controllers', "Door controllers and power supplies on {$brand} systems."],
            ['Tokens', "Cards, fobs and user admin for the {$brand} software on site."],
        ],
        'nurse-call' => [
            ['Call points', "Call points, pear leads and pull cords for {$brand} rooms."],
            ['Displays', "Corridor displays and reset points on {$brand} systems."],
            ['Panel power', "Panel batteries and power supplies checked on {$brand} visits."],
        ],
        'electrical' => [
            ['Distribution boards', "Boards and protective devices in the {$brand} range on site."],
            ['Protection devices', "MCBs, RCBOs and SPDs that fit the {$brand} assembly."],
            ['EV and supply', "{$brand} supply or charger equipment, quoted after the supply is known."],
        ],
        'emergency-lighting' => [
            ['Bulkheads', "Emergency bulkheads in the {$brand} range."],
            ['Exit signs', "Exit signs and conversions matched to {$brand} fittings."],
            ['Batteries', "Replacement batteries for {$brand} emergency packs."],
        ],
        'gas-systems' => [
            ['Boilers', "{$brand} boilers surveyed before any parts are named."],
            ['Controls', "{$brand} controls and flues that belong with the appliance."],
            ['Service parts', "Filters, seals and service parts for a {$brand} service visit."],
        ],
        'intruder-alarm' => [
            ['Panels', "{$brand} panels and keypads."],
            ['Detectors', "PIRs, contacts and shock sensors for {$brand} zones."],
            ['Signalling', "Signalling and app options where the {$brand} panel supports them."],
        ],
        'cctv' => [
            ['Cameras', "{$brand} cameras chosen for the scene, not a generic dome."],
            ['Recorders', "NVRs and storage used with {$brand} cameras."],
            ['Mounts', "Mounts, brackets and cabling for {$brand} installs."],
        ],
        'door-entry' => [
            ['Entrance panels', "{$brand} entrance panels and name modules."],
            ['Handsets', "Handsets and monitors for {$brand} apartments or desks."],
            ['Door release', "Lock release and power that the {$brand} panel expects."],
        ],
        'intercoms' => [
            ['Door stations', "{$brand} door stations for gates and lobbies."],
            ['Master stations', "Master stations and call routing for {$brand} systems."],
            ['Audio modules', "Audio and video modules matched to the {$brand} system on site."],
        ],
    ];
    return $map[$service] ?? [
        ['Installed systems', "{$brand} systems surveyed, installed and maintained."],
        ['Service parts', "Service parts matched to the {$brand} equipment on site."],
        ['Replacement devices', "Replacement devices for the {$brand} range, quoted after survey."],
    ];
}

function manufacturerLineLabel(string $brand, string $line): string
{
    if ($line === '') {
        return $brand;
    }
    if (stripos($line, $brand) !== false) {
        return $line;
    }
    return $brand . ' ' . $line;
}

function manufacturerLineSlug(string $line): string
{
    return areaSlug($line);
}

function manufacturerLineLogoPath(string $mfrSlug, string $lineSlug): string
{
    return '/assets/images/manufacturers/lines/' . $mfrSlug . '-' . $lineSlug . '.svg';
}

/**
 * @return list<array{name:string,label:string,slug:string,blurb:string,logo:string}>
 */
function manufacturerProductLines(array $entry): array
{
    $slug = (string)($entry['slug'] ?? '');
    $brand = (string)($entry['name'] ?? $slug);
    $service = (string)(($entry['services'][0] ?? 'fire-alarms'));
    $curated = manufacturerCuratedProductLines();
    $rows = $curated[$slug] ?? manufacturerFallbackLineRows($service, $brand);
    $out = [];
    foreach ($rows as $row) {
        $name = (string)($row[0] ?? '');
        $blurb = (string)($row[1] ?? '');
        if ($name === '') {
            continue;
        }
        $lineSlug = manufacturerLineSlug($name);
        $out[] = [
            'name' => $name,
            'label' => manufacturerLineLabel($brand, $name),
            'slug' => $lineSlug,
            'blurb' => $blurb,
            'logo' => manufacturerLineLogoPath($slug, $lineSlug),
        ];
    }
    return $out;
}

function manufacturerLineLogoSvg(string $brand, string $line): string
{
    $label = manufacturerLineLabel($brand, $line);
    $seed = abs(crc32(strtolower($brand . '|' . $line)));
    $bgs = ['#0B1F3A', '#10283F', '#143049', '#0E2438', '#1A3148'];
    $bg = $bgs[$seed % count($bgs)];
    $accent = ($seed % 2 === 0) ? '#ff6b00' : '#ff9a4a';
    $size = strlen($label) > 28 ? 18 : (strlen($label) > 20 ? 22 : 26);
    $esc = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    };
    $brandEsc = $esc($brand);
    $lineEsc = $esc($line);
    return '<?xml version="1.0" encoding="UTF-8"?>'
        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 270" role="img" aria-label="' . $esc($label) . '">'
        . '<rect width="480" height="270" rx="28" fill="' . $bg . '"/>'
        . '<rect x="28" y="28" width="72" height="10" rx="5" fill="' . $accent . '"/>'
        . '<text x="28" y="128" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="' . $size . '" font-weight="700">' . $lineEsc . '</text>'
        . '<text x="28" y="178" fill="' . $accent . '" font-family="Arial, Helvetica, sans-serif" font-size="18">' . $brandEsc . '</text>'
        . '<text x="28" y="224" fill="#cbd5e1" font-family="Arial, Helvetica, sans-serif" font-size="14">Product line</text>'
        . '</svg>';
}

function manufacturerWriteLineLogos(): int
{
    $dir = SITE_ROOT . '/assets/images/manufacturers/lines';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot create logo directory');
    }
    $n = 0;
    foreach (getManufacturerCatalog() as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        foreach (manufacturerProductLines($entry) as $line) {
            $path = SITE_ROOT . $line['logo'];
            file_put_contents($path, manufacturerLineLogoSvg((string)$entry['name'], $line['name']));
            $n++;
        }
    }
    return $n;
}

function manufacturerLocalIntro(array $entry, string $area): string
{
    if (!function_exists('area_profile')) {
        require_once __DIR__ . '/local-content.php';
    }
    $p = area_profile($area);
    $brand = (string)$entry['name'];
    $slug = (string)$entry['slug'];
    $seed = area_seed($area, 'mfr-intro|' . $slug);
    $lines = manufacturerProductLines($entry);
    $line = $lines[$seed % count($lines)];
    $lineB = $lines[($seed + 2) % count($lines)];
    $areas = manufacturerAreasFor($entry);
    $idx = array_search($area, $areas, true);
    $pos = ($idx === false ? 1 : $idx + 1);
    $total = count($areas);
    $primary = (string)(($entry['services'][0] ?? 'fire-alarms'));
    $serviceName = getServices()[$primary] ?? $primary;
    $angle = service_local_angle($primary, $serviceName, $area);
    $openers = [
        "{$brand} work in {$area} starts from the label on the equipment. The range named first on the survey is {$line['label']}.",
        "A {$area} call that says {$brand} still needs the generation: {$line['label']}, or {$lineB['label']} if the building was extended later.",
        "{$area} ({$p['districts']}) is {$p['stock']}. {$brand} jobs there are scoped around {$line['label']} before a second range is offered.",
        "From the Stockport workshop, {$area} is {$p['travel']}. The {$brand} note names {$line['label']} so the quote matches the kit, not a catalogue guess.",
        "The usual {$area} brief is {$p['focus']}. Where the system is {$brand}, the survey starts on {$line['label']}.",
        "{$p['region']} buildings around {$area} often mix {$brand} generations. We separate {$line['label']} from {$lineB['label']} before ordering parts.",
    ];
    $patterns = [
        'The rating label and the supply arrangement are photographed before a part is recommended.',
        'Fault-finding stays on the installed range until a survey shows it is unsupported.',
        'The visit note lists what was tested, what was left in service, and what needs a return.',
        'Spares are matched to the range on site rather than a lookalike from another maker.',
        'Interfaces to doors, vents or barriers are checked before the job is signed off.',
        'Keys, access equipment and a person on site are agreed before the diary slot.',
        'Install labour is not given a made-up figure on this page. The quote follows the survey.',
        'Battery, fuse and safety-device checks are written down even when the main kit looks healthy.',
    ];
    $closers = [
        "Travel: {$p['travel']}. Building stock we see: {$p['stock']}.",
        "Postcodes in view: {$p['districts']}. The local priority is {$p['focus']}.",
        $angle,
    ];
    $text = $openers[$seed % count($openers)]
        . ' ' . $patterns[intdiv($seed, 6) % count($patterns)]
        . ' ' . $closers[intdiv($seed, 36) % count($closers)];
    if (manufacturerIsBarriersPartner($entry)) {
        $text .= ' CAME is our barriers partner, so Gard barriers and gate operators are specified with us. Supply packs are priced on the products hub. Installation is POA.';
    } elseif (in_array('aov-air-handling', $entry['services'] ?? [], true)) {
        $bits = [
            " Smoke-vent work follows the fire strategy: actuators, the control panel and the stair or lobby vent. Installation is POA.",
            " Generic AOV kit list prices stay on the products hub. This page is the {$brand} range and the survey, not a second price card.",
            " Override, rain sensor and panel are tested together so a vent that opens still closes.",
        ];
        $text .= $bits[$seed % count($bits)];
    }
    $text .= " This is {$brand} page {$pos} of {$total} on the published town list.";
    return $text;
}

function manufacturerAreaTitle(string $brand, string $area): string
{
    $candidates = [
        $brand . ' in ' . $area . ' | iComply',
        $brand . ' | ' . $area . ' | iComply',
        $area . ' | ' . $brand,
    ];
    foreach ($candidates as $title) {
        $len = strlen($title);
        if ($len >= 15 && $len <= 70) {
            return $title;
        }
    }
    $tail = ' | ' . $area;
    $room = 70 - strlen($tail);
    $short = rtrim(substr($brand, 0, max(8, $room)));
    $title = $short . $tail;
    if (strlen($title) > 70) {
        $title = substr($title, 0, 70);
    }
    return $title;
}

function manufacturerAreaMeta(array $entry, string $area): string
{
    if (!function_exists('area_profile')) {
        require_once __DIR__ . '/local-content.php';
    }
    $p = area_profile($area);
    $brand = (string)$entry['name'];
    $lines = manufacturerProductLines($entry);
    $line = $lines[0]['label'] ?? $brand;
    $scope = manufacturerCoverageMode($entry) === 'nationwide'
        ? 'North West town list'
        : 'Greater Manchester and Burnley';
    $desc = $brand . ' in ' . $area . ' (' . $p['districts'] . '). ' . $line . '. Install and service. ' . $scope . '. Written quote, no invented install fee.';
    if (strlen($desc) > 165) {
        $desc = $brand . ' in ' . $area . '. ' . $line . '. ' . $scope . '. Written quote after survey.';
    }
    if (strlen($desc) > 165) {
        $desc = substr($desc, 0, 162);
        $desc = preg_replace('/\s+\S*$/', '', $desc) . '.';
    }
    if (strlen($desc) < 70) {
        $desc .= ' Stockport engineers attend with the range named on site.';
    }
    if (strlen($desc) > 165) {
        $desc = substr($desc, 0, 165);
    }
    return $desc;
}

/** @return list<array{q:string,a:string}> */
function manufacturerAreaFaqs(array $entry, string $area): array
{
    if (!function_exists('area_profile')) {
        require_once __DIR__ . '/local-content.php';
    }
    $p = area_profile($area);
    $brand = (string)$entry['name'];
    $lines = manufacturerProductLines($entry);
    $line = $lines[0]['label'] ?? $brand;
    $faqs = [
        [
            'q' => "Do you work on {$brand} in {$area}?",
            'a' => "Yes. {$area} is on the published {$brand} list ({$p['districts']}). We survey before naming {$line} parts.",
        ],
        [
            'q' => "Which {$brand} product lines do you cover around {$area}?",
            'a' => 'This page lists ' . implode(', ', array_map(static fn($l) => $l['label'], $lines)) . '. The visit confirms which one is actually installed.',
        ],
        [
            'q' => "How are {$brand} prices handled for {$area}?",
            'a' => 'Installation and labour are POA after survey. Where a supply kit has a published list price, it stays on the products hub rather than being redrawn here.',
        ],
    ];
    if (manufacturerIsBarriersPartner($entry)) {
        $faqs[] = [
            'q' => "Is CAME your barriers partner for {$area}?",
            'a' => "Yes. CAME is our barriers partner. Gard barriers and gate operators for {$area} are specified with us. 5m pack supply prices are on the products hub. Installation is POA.",
        ];
    }
    return $faqs;
}

/** @return list<array{src:string,alt:string}> */
function manufacturerGallery(array $entry, string $area): array
{
    $brand = (string)$entry['name'];
    $slug = (string)$entry['slug'];
    $primary = (string)(($entry['services'][0] ?? 'fire-alarms'));
    $items = [];
    $push = static function (string $src, string $alt) use (&$items): void {
        if ($src === '') {
            return;
        }
        foreach ($items as $have) {
            if ($have['src'] === $src) {
                return;
            }
        }
        $items[] = ['src' => $src, 'alt' => $alt];
    };
    $rel = '/assets/images/manufacturers/' . $slug . '.jpg';
    if (is_file(SITE_ROOT . $rel)) {
        $push(url($rel), $brand . ' equipment used on ' . $area . ' surveys');
    }
    foreach (['.jpg', '.png'] as $ext) {
        $svc = '/assets/images/services/' . $primary . $ext;
        if (is_file(SITE_ROOT . $svc)) {
            $push(url($svc), (getServices()[$primary] ?? $primary) . ' work in ' . $area);
            break;
        }
    }
    if (!function_exists('getKeywordImages')) {
        // config already loaded
    }
    foreach (array_slice(getKeywordImages($primary), 0, 2) as $kw) {
        foreach (['.jpg', '.png'] as $ext) {
            $path = '/assets/images/keywords/' . $kw . $ext;
            if (is_file(SITE_ROOT . $path)) {
                $push(url($path), $brand . ' related ' . str_replace('-', ' ', (string)$kw) . ' in ' . $area);
                break;
            }
        }
    }
    if (in_array('aov-air-handling', $entry['services'] ?? [], true)) {
        $aovMap = manufacturerJsonStringMap('aov-kit-cdn-images');
        foreach (['aov-act', 'aov-ctrl', 'aov-kit-1m2'] as $sku) {
            if (!empty($aovMap[$sku])) {
                $push($aovMap[$sku], 'AOV supply kit ' . $sku . ' — list price on the products hub, install POA');
            }
        }
    }
    if (($entry['slug'] ?? '') === 'came') {
        foreach (manufacturerJsonStringMap('bar-5m-came-gard-images') as $handle => $src) {
            $push($src, 'CAME Gard reference for ' . $handle . ' — supply price on the products hub');
            if (count($items) >= 6) {
                break;
            }
        }
    }
    return array_slice($items, 0, 6);
}

/** @return array<string, string> */
function manufacturerJsonStringMap(string $name): array
{
    static $cache = [];
    if (isset($cache[$name])) {
        return $cache[$name];
    }
    $path = SITE_ROOT . '/data/' . $name . '.json';
    $map = [];
    if (is_file($path)) {
        $json = json_decode((string)file_get_contents($path), true);
        if (is_array($json)) {
            foreach ($json as $k => $v) {
                if (is_string($k) && is_string($v) && $v !== '') {
                    $map[strtolower($k)] = $v;
                }
            }
        }
    }
    return $cache[$name] = $map;
}

function manufacturerH(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function manufacturerLineCardsHtml(array $entry): string
{
    $html = '<div class="mfr-line-grid">';
    foreach (manufacturerProductLines($entry) as $line) {
        $html .= '<article class="mfr-line-card">';
        $html .= '<img class="mfr-line-logo" src="' . manufacturerH(url($line['logo'])) . '" alt="' . manufacturerH($line['label'] . ' product line logo') . '" width="240" height="135" loading="lazy">';
        $html .= '<h3>' . manufacturerH($line['label']) . '</h3>';
        $html .= '<p>' . manufacturerH($line['blurb']) . '</p>';
        $html .= '</article>';
    }
    $html .= '</div>';
    return $html;
}

function manufacturerGalleryHtml(array $entry, string $area): string
{
    $shots = manufacturerGallery($entry, $area);
    if (!$shots) {
        return '';
    }
    $html = '<div class="mfr-gallery">';
    foreach ($shots as $shot) {
        $html .= '<figure class="mfr-gallery-item"><img src="' . manufacturerH($shot['src']) . '" alt="' . manufacturerH($shot['alt']) . '" width="640" height="400" loading="lazy"></figure>';
    }
    $html .= '</div>';
    return $html;
}

function manufacturerAreaChipsHtml(array $entry): string
{
    $slug = (string)$entry['slug'];
    $html = '<div class="chip-cloud">';
    foreach (manufacturerAreasFor($entry) as $area) {
        $href = url('/pages/manufacturers/' . $slug . '/' . areaSlug($area));
        $html .= '<a class="mfr-text-link" href="' . manufacturerH($href) . '">' . manufacturerH($area) . '</a>';
    }
    $html .= '</div>';
    return $html;
}
