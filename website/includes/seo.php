<?php
/**
 * SEO helpers for Icomply Property Services
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
        $withBrand = $title . ' | Icomply';
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
        'electrical' => ['BS 7671', 'EICR', 'PAT testing', 'Part P', 'NICEIC-aligned practice', 'EV charger install'],
        'fire-alarms' => ['BS 5839', 'fire detection', 'L1–L5 categories', 'addressable systems', 'commissioning certificates'],
        'emergency-lighting' => ['BS 5266', 'maintained / non-maintained', 'exit signage', 'duration testing', 'self-test LED'],
        'aov-air-handling' => ['BS 9991 guidance', 'smoke ventilation', 'AOV controls', 'smoke shafts', 'fire strategy support'],
        'nurse-call' => ['HTM 08-03 aligned', 'care home systems', 'wireless / wired', 'panel upgrades', 'handset repair'],
        'gas-systems' => ['Gas Safe', 'landlord gas safety', 'CP12 / CP44', 'boiler servicing', 'commercial gas'],
        'intruder-alarm' => ['BS 4737 / PD 6662 practice', 'wired & wireless', 'PIR detection', 'app control', 'ARC-ready'],
        'cctv' => ['IP / HD CCTV', 'NVR recording', 'remote viewing', 'retail & warehouse', 'GDPR-aware install'],
        'access-control' => ['card / fob / biometric', 'multi-door control', 'audit trails', 'time zones', 'fire door release'],
        'door-entry' => ['video door entry', 'audio door entry', 'apartment blocks', 'riser upgrades', 'handset replacement'],
        'intercoms' => ['video intercom', 'audio intercom', 'multi-tenant', 'office systems', 'fault finding'],
    ];
    return $map[$slug] ?? ['UK installation', 'servicing', 'certification'];
}

/** Long-form intro paragraph for service×area pages (unique enough via placeholders) */
function seo_combo_intro(string $serviceName, string $slug, string $area): string {
    $standards = implode(', ', array_slice(service_standards($slug), 0, 3));
    return "Looking for professional {$serviceName} in {$area}? Icomply Property Services provides design, installation, "
        . "maintenance and certification for landlords, managing agents, facilities teams and businesses across {$area} "
        . "and the wider North West. Our engineers work to UK best practice including {$standards}, with clear paperwork "
        . "you can show insurers, freeholders and local authorities. Based in Stockport (SK2), we cover {$area} with "
        . "same-week appointments where diary capacity allows and fixed-price quotes whenever the scope is clear.";
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
            ['q' => "Do you offer same-week electrical work{$loc}?", 'a' => "Yes where diary capacity allows. Emergency fault-finding and consumer unit issues are prioritised for {$area} and surrounding postcodes."],
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
            ['q' => "Do you issue landlord gas safety certificates{$loc}?", 'a' => "Yes. Gas safety checks and certification for rented properties{$loc}, with clear records for landlords and agents."],
            ['q' => "Can you service commercial boilers?", 'a' => "We handle domestic and many commercial gas servicing needs — tell us plant type and access for an accurate quote."],
            ['q' => "How quickly can you attend{$loc}?", 'a' => "Routine services are diary-booked; urgent unsafe situations are prioritised. Call us with the postcode for the next slot."],
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
    ];
    $faqs = $base[$slug] ?? [
        ['q' => "Do you provide {$serviceName}{$loc}?", 'a' => "Yes. Icomply installs, services and certificates {$serviceName}{$loc} for residential and commercial clients."],
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

function local_business_schema(array $extra = []): array {
    $base = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        '@id' => site_url() . '#business',
        'name' => SITE_NAME,
        'image' => site_url('assets/images/og-image.jpg'),
        'url' => site_url(),
        'telephone' => PHONE,
        'email' => EMAIL,
        'priceRange' => 'GBP',
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
            'latitude' => 53.3915,
            'longitude' => -2.1268,
        ],
        'areaServed' => [
            '@type' => 'AdministrativeArea',
            'name' => 'North West England',
        ],
        'openingHoursSpecification' => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday'],
            'opens' => '08:00',
            'closes' => '18:00',
        ],
    ];
    return array_merge($base, $extra);
}
