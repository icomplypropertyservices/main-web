<?php
/**
 * SEO helpers for iComply Property Services
 * Rank-focused local content, FAQs, schema, breadcrumbs.
 */
require_once __DIR__ . '/local-content.php';

/** Public production domain used in sitemap / canonicals when not on localhost deploy */
if (!defined('SITE_PUBLIC_URL')) {
    define('SITE_PUBLIC_URL', 'https://www.icomplypropertyservices.co.uk');
}

function seo_public_url(string $path = ''): string {
    $base = (strpos(SITE_URL, 'localhost') !== false) ? SITE_PUBLIC_URL : SITE_URL;
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function seo_current_url(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return site_url(ltrim(strtok($uri, '?'), '/'));
}

function seo_title(string $title): string {
    // Keep SERP titles short (~50–60 chars). Brand only if room remains.
    $title = trim($title);
    if (mb_strlen($title) <= 55) {
        $withBrand = $title . ' | iComply';
        if (mb_strlen($withBrand) <= 60) return $withBrand;
    }
    if (mb_strlen($title) > 60) {
        return mb_substr($title, 0, 57) . '…';
    }
    return $title;
}

/** Standards / compliance keywords per service for on-page SEO */
function service_standards(string $slug): array {
    $map = [
        'electrical' => ['BS 7671', 'EICR', 'PAT testing', 'electrical regulations', 'electrical testing', 'EV charger install'],
        'fire-alarms' => ['BS 5839', 'fire detection', 'L1–L5 categories', 'addressable systems', 'commissioning certificates'],
        'emergency-lighting' => ['BS 5266', 'maintained / non-maintained', 'exit signage', 'duration testing', 'self-test LED'],
        'aov-air-handling' => ['BS 9991 guidance', 'smoke ventilation', 'AOV controls', 'smoke shafts', 'fire strategy support'],
        'barriers' => ['CAME barriers partner', 'vehicle barriers', 'gate operators', 'safety edges and loops', 'install POA'],
        'nurse-call' => ['HTM 08-03 aligned', 'care home systems', 'wireless / wired', 'panel upgrades', 'handset repair'],
        'gas-systems' => ['landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers', 'iComply does not issue CP12', 'no Gas Safe registration'],
        'intruder-alarm' => ['BS 4737 / PD 6662 practice', 'wired & wireless', 'PIR detection', 'app control', 'ARC-ready'],
        'cctv' => ['IP / HD CCTV', 'NVR recording', 'remote viewing', 'retail & warehouse', 'GDPR-aware install'],
        'access-control' => ['card / fob / biometric', 'multi-door control', 'audit trails', 'time zones', 'fire door release'],
        'door-entry' => ['video door entry', 'audio door entry', 'apartment blocks', 'riser upgrades', 'handset replacement'],
        'intercoms' => ['video intercom', 'audio intercom', 'multi-tenant', 'office systems', 'fault finding'],
        'legionella-risk-assessment' => ['HSE L8', 'HSG274', 'water hygiene', 'Legionella risk assessment', 'POA'],
        'asbestos-survey' => ['CAR 2012', 'duty to manage', 'management survey', 'refurbishment survey', 'POA'],
    ];
    return $map[$slug] ?? ['UK installation', 'servicing', 'certification'];
}

/** Long-form intro paragraph for service×area pages (unique enough via placeholders) */
function seo_combo_intro(string $serviceName, string $slug, string $area): string {
    if (function_exists('icomplyCopyIsGasTopic') && icomplyCopyIsGasTopic($slug, $serviceName) && function_exists('icomplyGasLegalSentence')) {
        return icomplyGasLegalSentence() . ' ' . $serviceName . ' in ' . $area . ' is not carried out by iComply. Non-gas compliance in ' . $area . ' is quoted POA.';
    }
    $standards = implode(', ', array_slice(service_standards($slug), 0, 3));
    return "Looking for professional {$serviceName} in {$area}? iComply Property Services provides design, installation, "
        . "maintenance and certification for landlords, managing agents, facilities teams and businesses across {$area} "
        . "and the wider North West. Our engineers work to UK best practice including {$standards}, with clear paperwork "
        . "you can show insurers, freeholders and local authorities. Based in Stockport (SK2), we cover {$area} with "
        . "appointments booked when the diary allows and "
        . ((function_exists('isPoaService') && isPoaService($slug))
            ? "a price-on-application quote once scope is clear."
            : "fixed-price quotes whenever the scope is clear.");
}

function seo_combo_why(string $serviceName, string $area): array {
    return [
        "Local {$area} coverage from a Stockport-based UK compliance team",
        "Clear scope, fixed-price quotes where possible, and written reports",
        "{$serviceName} install, service and certification under one contractor",
        "Documentation packs suitable for landlords, insurers and block managers",
        "Responsive scheduling across Greater Manchester and the North West",
    ];
}

function seo_combo_process(string $serviceName, string $area): array {
    return [
        ['step' => '1', 'title' => 'Free quote', 'text' => "Tell us the property type, postcode and {$serviceName} requirement in {$area}."],
        ['step' => '2', 'title' => 'Site survey', 'text' => "We confirm standards, access, and any existing equipment before finalising price."],
        ['step' => '3', 'title' => 'Works & test', 'text' => "Qualified engineers complete install or service, then test and document the system."],
        ['step' => '4', 'title' => 'Certification', 'text' => "You receive certificates, labels and recommendations for ongoing compliance in {$area}."],
    ];
}

/** FAQ pairs for schema + on-page (service level) */
function service_faqs(string $slug, string $serviceName, string $area = ''): array {
    $loc = $area !== '' ? " in {$area}" : ' across the North West';
    $base = [
        'electrical' => [
            ['q' => "How often do I need an EICR{$loc}?", 'a' => "Most rented homes need an EICR at least every 5 years (or on change of tenancy). Commercial intervals depend on risk and insurer requirements — we advise based on the property type{$loc}."],
            ['q' => "How is electrical work booked{$loc}?", 'a' => "Appointments are booked when an engineer is available. Emergency fault-finding and consumer unit issues are prioritised for {$area} and surrounding postcodes."],
            ['q' => "Are quotes fixed-price?", 'a' => "Where the scope is clear after survey or photos, we issue fixed-price quotes for EICR, PAT, installs and upgrades."],
        ],
        'fire-alarms' => [
            ['q' => "What standard do fire alarms follow{$loc}?", 'a' => "We design and maintain systems with BS 5839 practice in mind, matched to your fire risk assessment and building use{$loc}."],
            ['q' => "How often should fire alarms be serviced?", 'a' => "Typically at least every 6 months for many commercial systems, with weekly user tests. We set a maintenance plan for your site{$loc}."],
            ['q' => "Can you upgrade from conventional to addressable?", 'a' => "Yes. We survey existing cabling and devices, then propose a phased or full addressable upgrade with certification."],
        ],
        'emergency-lighting' => [
            ['q' => "What is BS 5266 emergency lighting?", 'a' => "BS 5266 is the key UK code of practice for emergency lighting. We install, test and certificate systems to support safe escape routes{$loc}."],
            ['q' => "How often should emergency lights be tested?", 'a' => "Monthly function tests and annual full-duration tests are common. We can run testing programmes and keep logbooks for your {$area} properties."],
            ['q' => "Do you supply self-test LED fittings?", 'a' => "Yes — self-test bulkheads and exit signs reduce labour while keeping compliance evidence for landlords and FM teams."],
        ],
        'barriers' => [
            ['q' => "Who is the barriers partner{$loc}?", 'a' => "CAME is our barriers partner. Gard barriers and gate operators are specified with us. Supply prices for the published 5m packs are on the products hub. Installation is POA after survey."],
            ['q' => "Do you install gates as well as barriers?", 'a' => "Yes. Sliding and swing operators are quoted with the barrier or on their own once the opening, safety edges and access control are known."],
            ['q' => "Can a barrier share fobs with the building?", 'a' => "Often yes, after the survey checks the barrier inputs and the access platform. Paxton and Videx options are listed as supply packs, not assumed."],
        ],
        'aov-air-handling' => [
            ['q' => "What is an AOV system?", 'a' => "Automatic Opening Vents help clear smoke from stairs and corridors. We install and maintain AOV and related smoke control plant for multi-storey buildings{$loc}."],
            ['q' => "Do you service existing smoke vents{$loc}?", 'a' => "Yes. We inspect actuators, controls, interfaces and air handling plant, then provide a clear remedial report."],
            ['q' => "Can you work to a fire strategy?", 'a' => "We coordinate with your fire strategy and risk assessment so vents, controls and cause-and-effect logic match the building design."],
        ],
        'nurse-call' => [
            ['q' => "Do you install nurse call in care homes{$loc}?", 'a' => "Yes — wired and wireless nurse call for care homes, supported living and clinical settings{$loc}, with HTM-aligned maintenance options."],
            ['q' => "Can you repair handsets and panels?", 'a' => "We fault-find, replace handsets, upgrade panels and expand coverage room-by-room without unnecessary full rip-outs."],
            ['q' => "Do you offer maintenance contracts?", 'a' => "Yes. Planned visits keep systems reliable and create an audit trail for CQC and internal compliance teams."],
        ],
        'gas-systems' => [
            ['q' => "Does iComply issue landlord gas safety certificates{$loc}?", 'a' => "No. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply does not carry out gas work or issue CP12 or gas safety certificates."],
            ['q' => "Can iComply service boilers{$loc}?", 'a' => "No. Boiler installation, servicing and repair are gas work. Landlord gas safety certificates (CP12), carried out by Gas Safe registered engineers. iComply is not Gas Safe registered."],
            ['q' => "What can iComply quote{$loc}?", 'a' => "Electrical, fire, water hygiene and asbestos work is quoted POA. Gas work stays with a Gas Safe registered engineer."],
        ],
        'intruder-alarm' => [
            ['q' => "Do you install wireless alarms{$loc}?", 'a' => "Yes — wireless and hybrid systems for homes and businesses{$loc}, including app control options."],
            ['q' => "Can alarms be monitored?", 'a' => "We can install ARC-ready systems. Monitoring contracts are arranged to suit your insurer and risk profile."],
            ['q' => "Will you take over an existing system?", 'a' => "Often yes after a health check. We confirm panel type, sensors and signalling before quoting service or upgrade."],
        ],
        'cctv' => [
            ['q' => "Do you install IP CCTV{$loc}?", 'a' => "Yes — IP/HD camera systems with NVR recording and secure remote viewing for managers{$loc}."],
            ['q' => "Is CCTV GDPR compliant?", 'a' => "We design camera views to avoid unnecessary private intrusion and advise on signage and data retention best practice."],
            ['q' => "Can you expand an existing system?", 'a' => "We add cameras, upgrade recorders and migrate storage while keeping as much existing cabling as practical."],
        ],
        'access-control' => [
            ['q' => "What access control options do you offer?", 'a' => "Card, fob, PIN and biometric readers for single doors through to multi-door sites with audit trails and time zones."],
            ['q' => "Can access control integrate with fire alarms?", 'a' => "Yes — door release strategies are coordinated so escape routes remain safe while security is maintained."],
            ['q' => "Do you support multi-tenant buildings{$loc}?", 'a' => "Yes. We set user groups for tenants, cleaners and contractors across blocks{$loc}."],
        ],
        'door-entry' => [
            ['q' => "Do you upgrade old door entry systems{$loc}?", 'a' => "Yes — full panel and handset upgrades for flats and offices{$loc}, including riser works where needed."],
            ['q' => "Video or audio door entry?", 'a' => "Both. Video is popular for apartments; audio remains a robust budget option for many blocks."],
            ['q' => "Can residents use mobile apps?", 'a' => "Many modern systems support mobile answering. We specify based on building infrastructure and budget."],
        ],
        'intercoms' => [
            ['q' => "Do you install video intercoms{$loc}?", 'a' => "Yes — video and audio intercoms for flats, offices and mixed-use buildings{$loc}."],
            ['q' => "Can you repair a single handset?", 'a' => "Often yes. We diagnose whether the fault is handset, wiring or door station before replacing parts."],
            ['q' => "Do intercoms work with access control?", 'a' => "They frequently integrate with door release and access control for a single visitor journey."],
        ],
        'legionella-risk-assessment' => [
            ['q' => "Do you offer Legionella risk assessments{$loc}?", 'a' => "Yes. We scope a written assessment of the water system{$loc}. Sampling is only added when it helps. Price on application."],
            ['q' => "Are quotes fixed on a price list?", 'a' => "No. Legionella and water hygiene work is POA after we know stored water, outlets and access."],
            ['q' => "Do you claim a named lab accreditation here?", 'a' => "No. We do not invent laboratory or training badges on this page. Method is confirmed at quote stage."],
        ],
        'asbestos-survey' => [
            ['q' => "Do you carry out asbestos surveys{$loc}?", 'a' => "Yes — management or refurbishment surveys scoped to the building and planned works{$loc}. POA."],
            ['q' => "Do you remove asbestos?", 'a' => "Licensed removal is not this service. If the survey says removal is needed, that work is appointed separately."],
            ['q' => "Do you list a starting price?", 'a' => "No. Surveys are POA. Size, access and how intrusive the visit must be all change the figure."],
        ],
    ];
    $faqs = $base[$slug] ?? [
        ['q' => "Do you provide {$serviceName}{$loc}?", 'a' => "Yes. iComply installs, services and certificates {$serviceName}{$loc} for residential and commercial clients."],
        ['q' => "How do I get a quote?", 'a' => "Call, WhatsApp or use our online form with the postcode and property type for a fast fixed-price style quote."],
        ['q' => "What areas do you cover?", 'a' => "Greater Manchester and 150+ North West towns from our Stockport base."],
    ];
    // Personalise area name in answers if empty area was used in templates
    if ($area === '') {
        foreach ($faqs as &$f) {
            $f['q'] = str_replace(' in ', ' across the North West — ', $f['q']);
            $f['q'] = str_replace(' across the North West across the North West', ' across the North West', $f['q']);
        }
    }
    return $faqs;
}

function nearby_areas(string $area, int $limit = 12): array {
    global $areas;
    $list = $GLOBALS['areas'] ?? $areas ?? [];
    $idx = array_search($area, $list, true);
    if ($idx === false) return array_slice($list, 0, $limit);
    $out = [];
    for ($i = 1; count($out) < $limit && $i < count($list); $i++) {
        $out[] = $list[($idx + $i) % count($list)];
    }
    return $out;
}

function render_breadcrumbs(array $crumbs): string {
    // $crumbs = [['name'=>'Home','url'=>'index.php'], ...]
    $items = '';
    $schema = [];
    $pos = 1;
    $html = '<nav aria-label="Breadcrumb" class="text-sm text-zinc-500 mb-6"><ol class="flex flex-wrap items-center gap-2">';
    $last = count($crumbs) - 1;
    foreach ($crumbs as $i => $c) {
        $name = htmlspecialchars($c['name']);
        $url = htmlspecialchars($c['url']);
        if ($i < $last) {
            $html .= '<li><a class="hover:text-[#ff6b00]" href="' . $url . '">' . $name . '</a></li><li aria-hidden="true">/</li>';
        } else {
            $html .= '<li class="text-zinc-800 font-medium" aria-current="page">' . $name . '</li>';
        }
        $schema[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $c['name'],
            'item' => site_url($c['url'] === 'index.php' ? '' : $c['url']),
        ];
    }
    $html .= '</ol></nav>';
    $json = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $schema,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $html .= '<script type="application/ld+json">' . $json . '</script>';
    return $html;
}

function render_faq_section(array $faqs, string $heading = 'Frequently asked questions'): string {
    if (!$faqs) return '';
    $html = '<section class="mt-16" id="faqs"><h2 class="text-2xl font-extrabold tracking-tight mb-6">' . htmlspecialchars($heading) . '</h2><div class="space-y-4">';
    $schemaMain = [];
    foreach ($faqs as $f) {
        $q = htmlspecialchars($f['q']);
        $a = htmlspecialchars($f['a']);
        $html .= '<details class="bg-white border rounded-2xl p-5 group"><summary class="font-semibold cursor-pointer list-none flex justify-between gap-4">' . $q . '<span class="text-[#ff6b00]">+</span></summary><p class="mt-3 text-sm text-zinc-600 leading-relaxed">' . $a . '</p></details>';
        $schemaMain[] = [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ];
    }
    $html .= '</div>';
    $json = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $schemaMain,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $html .= '<script type="application/ld+json">' . $json . '</script></section>';
    return $html;
}

/** Homepage URL with trailing slash, matching sitemap.xml. */
function icomply_home_url(): string
{
    return rtrim((string)SITE_URL, '/') . '/';
}

/** Stable @id shared by header, home and service JSON-LD. */
function icomply_business_id(): string
{
    return rtrim((string)SITE_URL, '/') . '/#business';
}

/**
 * Absolute URL for canonicals, Open Graph and JSON-LD.
 * url() keeps CSS/icons root-relative; schema and og:image must not.
 */
function icomply_absolute_url(string $path = '/'): string
{
    $built = url($path);
    if (preg_match('#^https?://#i', $built)) {
        if ($built === rtrim((string)SITE_URL, '/')) {
            return icomply_home_url();
        }
        return $built;
    }
    return rtrim((string)SITE_URL, '/') . '/' . ltrim($built, '/');
}

function icomply_telephone_e164(): string
{
    $raw = (defined('WHATSAPP') && (string)WHATSAPP !== '') ? (string)WHATSAPP : (string)PHONE;
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if ($digits !== '' && str_starts_with($digits, '0')) {
        $digits = '44' . substr($digits, 1);
    }
    if ($digits !== '' && !str_starts_with($digits, '44')) {
        $digits = '44' . ltrim($digits, '0');
    }
    return $digits === '' ? (string)PHONE : ('+' . $digits);
}

/** @return list<string> */
function icomply_same_as(): array
{
    $urls = [];
    foreach (['SOCIAL_FACEBOOK', 'SOCIAL_INSTAGRAM', 'SOCIAL_LINKEDIN', 'SOCIAL_TWITTER', 'SOCIAL_YOUTUBE', 'SOCIAL_GOOGLE'] as $const) {
        if (!defined($const)) {
            continue;
        }
        $value = trim((string)constant($const));
        if ($value !== '') {
            $urls[] = $value;
        }
    }
    if (defined('WHATSAPP') && (string)WHATSAPP !== '') {
        $wa = preg_replace('/\D+/', '', (string)WHATSAPP) ?? '';
        if ($wa !== '') {
            $urls[] = 'https://wa.me/' . $wa;
        }
    }
    return array_values(array_unique($urls));
}

/** @return list<array<string,string>> */
function icomply_area_served_nodes(): array
{
    $areas = [
        ['City', 'Stockport'],
        ['AdministrativeArea', 'Greater Manchester'],
        ['AdministrativeArea', 'Cheshire'],
        ['AdministrativeArea', 'Lancashire'],
        ['AdministrativeArea', 'Merseyside'],
        ['AdministrativeArea', 'Cumbria'],
        ['AdministrativeArea', 'North West England'],
    ];
    $out = [];
    foreach ($areas as [$type, $name]) {
        $out[] = ['@type' => $type, 'name' => $name];
    }
    return $out;
}

function icomply_jsonld_script(array $data): string
{
    $json = json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
    );
    if ($json === false) {
        return '';
    }
    return '<script type="application/ld+json">' . $json . '</script>' . "\n";
}

/**
 * Canonical LocalBusiness node. Extra keys replace top-level fields.
 * @param array<string,mixed> $extra
 * @return array<string,mixed>
 */
function icomply_local_business(array $extra = []): array
{
    $logo = icomply_absolute_url('/assets/images/brand/icomply-logo.svg');
    $node = [
        '@type' => ['LocalBusiness', 'HomeAndConstructionBusiness'],
        '@id' => icomply_business_id(),
        'name' => SITE_NAME,
        'description' => 'Property maintenance and compliance in Stockport and across Greater Manchester and the North West, including EICR, gas safety, fire risk assessments, kitchens, CCTV, Legionella and asbestos surveys.',
        'url' => icomply_home_url(),
        'telephone' => icomply_telephone_e164(),
        'email' => EMAIL,
        'image' => $logo,
        'logo' => $logo,
        'priceRange' => '££',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '17 Woodlands Park Road, Offerton',
            'addressLocality' => 'Stockport',
            'addressRegion' => 'Greater Manchester',
            'postalCode' => 'SK2 5DE',
            'addressCountry' => 'GB',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => 53.3904,
            'longitude' => -2.1219,
        ],
        'areaServed' => icomply_area_served_nodes(),
        'openingHoursSpecification' => [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens' => '08:00',
            'closes' => '18:00',
        ]],
        'sameAs' => icomply_same_as(),
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => icomply_telephone_e164(),
            'contactType' => 'customer service',
            'email' => EMAIL,
            'areaServed' => 'GB',
            'availableLanguage' => ['English'],
        ],
    ];
    if (defined('SOCIAL_GOOGLE') && trim((string)SOCIAL_GOOGLE) !== '') {
        $node['hasMap'] = trim((string)SOCIAL_GOOGLE);
    }
    return array_merge($node, $extra);
}

function local_business_schema(array $extra = []): array
{
    $node = icomply_local_business($extra);
    $node['@context'] = 'https://schema.org';
    return $node;
}

/** Homepage hero services — the local SEO landing set. */
function icomply_top_service_labels(): array
{
    return [
        'electrical' => 'Electrical and EICR',
        'gas-systems' => 'Gas safety and CP12',
        'fire-risk-assessments' => 'Fire risk assessments',
        'landlord-compliance' => 'Landlord compliance',
        'kitchens' => 'Kitchen fitting',
        'renovation' => 'Property renovation',
        'cctv' => 'CCTV installation',
        'legionella-risk-assessment' => 'Legionella risk assessment',
        'asbestos-survey' => 'Asbestos surveys',
    ];
}

/**
 * @param array<string,string> $services slug => name
 * @return array<string,mixed>
 */
function icomply_home_jsonld(string $pageTitle, string $metaDesc, array $services): array
{
    $offers = [];
    foreach (icomply_top_service_labels() as $slug => $label) {
        if (!isset($services[$slug])) {
            continue;
        }
        $serviceUrl = url('/pages/services/' . $slug . '.php');
        $offers[] = [
            '@type' => 'Offer',
            'url' => $serviceUrl,
            'itemOffered' => [
                '@type' => 'Service',
                'name' => $label,
                'url' => $serviceUrl,
                'provider' => ['@id' => icomply_business_id()],
                'areaServed' => ['@type' => 'City', 'name' => 'Stockport'],
            ],
        ];
    }
    $business = icomply_local_business([
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'Property maintenance and compliance',
            'itemListElement' => $offers,
        ],
    ]);
    $home = icomply_home_url();
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            $business,
            [
                '@type' => 'WebSite',
                '@id' => $home . '#website',
                'url' => $home,
                'name' => SITE_NAME,
                'description' => $metaDesc,
                'inLanguage' => 'en-GB',
                'publisher' => ['@id' => icomply_business_id()],
            ],
            [
                '@type' => 'WebPage',
                '@id' => $home . '#webpage',
                'url' => $home,
                'name' => $pageTitle,
                'description' => $metaDesc,
                'isPartOf' => ['@id' => $home . '#website'],
                'about' => ['@id' => icomply_business_id()],
                'inLanguage' => 'en-GB',
            ],
        ],
    ];
}

/**
 * SERP title and meta description for a service hub.
 * Titles include the brand so the header does not append a second suffix.
 * @return array{title:string,description:string}
 */
function icomply_service_hub_seo(string $slug, string $serviceName, bool $poa): array
{
    $top = [
        'electrical' => [
            'title' => 'Electrical and EICR in Stockport | Icomply',
            'description' => 'EICR, rewires and electrical installation in Stockport and Greater Manchester. BS 7671 testing and certification. Written quote after scope.',
        ],
        'gas-systems' => [
            'title' => 'Gas Safety and CP12 in Stockport | Icomply',
            'description' => 'Landlord gas safety (CP12) and gas servicing in Stockport and Greater Manchester. Gas Safe checks. Written quote after scope.',
        ],
        'fire-risk-assessments' => [
            'title' => 'Fire Risk Assessments Stockport | Icomply',
            'description' => 'Fire risk assessments for landlords, HMOs and commercial sites in Stockport and Greater Manchester. Written quote after scope.',
        ],
        'landlord-compliance' => [
            'title' => 'Landlord Compliance Stockport | Icomply',
            'description' => 'Landlord compliance in Stockport and Greater Manchester, including EICR, gas safety and fire risk support. Written quote after scope.',
        ],
        'kitchens' => [
            'title' => 'Kitchen Fitting in Stockport | Icomply',
            'description' => 'Kitchen fitting in Stockport and Greater Manchester. Supply and installation for homes and rentals. Written quote after scope.',
        ],
        'renovation' => [
            'title' => 'Property Renovation Stockport | Icomply',
            'description' => 'Property renovation in Stockport and Greater Manchester. Refurbishment scoped to the building. Written quote after we confirm the job.',
        ],
        'cctv' => [
            'title' => 'CCTV Installation Stockport | Icomply',
            'description' => 'CCTV design and installation in Stockport and Greater Manchester. IP cameras, recording and remote viewing. Written quote after scope.',
        ],
        'legionella-risk-assessment' => [
            'title' => 'Legionella Assessment Stockport | Icomply',
            'description' => 'Legionella risk assessments in Stockport and Greater Manchester. Water hygiene scoped to the system. Price on application.',
        ],
        'asbestos-survey' => [
            'title' => 'Asbestos Surveys in Stockport | Icomply',
            'description' => 'Asbestos management and refurbishment surveys in Stockport and Greater Manchester. Removal is booked separately. Price on application.',
        ],
    ];
    if (isset($top[$slug])) {
        return $top[$slug];
    }

    $title = $serviceName . ' in Stockport | Icomply';
    if (mb_strlen(htmlspecialchars($title, ENT_QUOTES, 'UTF-8')) > 70) {
        $title = $serviceName . ' | Icomply';
    }

    if ($poa) {
        $description = 'Professional ' . $serviceName . ' in Stockport and Greater Manchester. Price on application after scope. No published fee.';
    } else {
        $description = 'Professional ' . $serviceName . ' in Stockport and Greater Manchester. Installation, testing and certification. Written quote after scope.';
    }
    if (mb_strlen(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) > 165) {
        $description = $poa
            ? ($serviceName . ' in Stockport and Greater Manchester. Price on application after scope. No published fee.')
            : ($serviceName . ' in Stockport and Greater Manchester. Written quote after scope from our Offerton team.');
    }
    if (mb_strlen(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) < 70) {
        $description .= ' Local team based in Offerton, SK2 5DE.';
    }
    return ['title' => $title, 'description' => $description];
}

/**
 * Service hub JSON-LD: one LocalBusiness, plus Service, breadcrumbs and FAQ.
 * @param list<array{0:string,1:string}> $faqs
 * @return array<string,mixed>
 */
function icomply_service_hub_jsonld(
    string $serviceName,
    string $pageTitle,
    string $metaDesc,
    string $canonicalUrl,
    string $imageUrl,
    array $faqs,
    bool $poa
): array {
    $faqEntities = [];
    foreach ($faqs as $faq) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => (string)$faq[0],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => (string)$faq[1],
            ],
        ];
    }
    $home = icomply_home_url();
    return [
        '@context' => 'https://schema.org',
        '@graph' => [
            icomply_local_business(),
            [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl . '#webpage',
                'url' => $canonicalUrl,
                'name' => $pageTitle,
                'description' => $metaDesc,
                'inLanguage' => 'en-GB',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    'name' => SITE_NAME,
                    'url' => $home,
                ],
                'about' => ['@id' => icomply_business_id()],
                'mainEntity' => ['@id' => $canonicalUrl . '#service'],
            ],
            [
                '@type' => 'Service',
                '@id' => $canonicalUrl . '#service',
                'name' => $serviceName,
                'description' => $metaDesc,
                'url' => $canonicalUrl,
                'image' => $imageUrl,
                'serviceType' => $serviceName,
                'provider' => ['@id' => icomply_business_id()],
                'areaServed' => icomply_area_served_nodes(),
                'offers' => [
                    '@type' => 'Offer',
                    'name' => ($poa ? 'Price on application — ' : 'Written quote — ') . $serviceName,
                    'description' => $poa
                        ? ('Request a scoped price-on-application quote for ' . $serviceName . '. No published fee.')
                        : ('Request a written quote for ' . $serviceName . ' after scope is confirmed.'),
                    'priceCurrency' => 'GBP',
                    'url' => url('/contact.php'),
                ],
                'brand' => [
                    '@type' => 'Brand',
                    'name' => SITE_NAME,
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $canonicalUrl . '#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => url('/pages/services/index.php')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceName, 'item' => $canonicalUrl],
                ],
            ],
            [
                '@type' => 'FAQPage',
                '@id' => $canonicalUrl . '#faq',
                'mainEntity' => $faqEntities,
            ],
        ],
    ];
}
