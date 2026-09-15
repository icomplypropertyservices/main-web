<?php
/**
 * HMO packages + high-intent topic helpers.
 * POA only — no invented prices, accreditations or reviews.
 */
if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}

/** Core HMO compliance bundle + optional add-ons already sold on the site. */
function hmoComplianceBundle(): array
{
    return [
        'name' => 'HMO Compliance Package',
        'tagline' => 'EICR + gas safety + fire risk assessment in one visit plan',
        'price' => 'POA',
        'includes' => [
            'Electrical Installation Condition Report (EICR) for the HMO installation',
            'Landlord gas safety record (CP12 / CP44 as required) where gas is present',
            'Suitable and sufficient fire risk assessment (FRA) for the HMO / multi-occupied house',
            'Single documentation pack for licence, agent and insurer files',
            'Coordinated access plan to reduce repeat visits for tenants',
        ],
        'addons' => [
            ['slug' => 'emergency-lighting', 'label' => 'Emergency lighting testing / install'],
            ['slug' => 'fire-alarms', 'label' => 'Fire alarm design, install or service'],
            ['slug' => 'fire-doors', 'label' => 'Fire door survey and upgrades'],
            ['slug' => 'smoke-co-alarms', 'label' => 'Smoke & CO alarm checks'],
            ['slug' => 'fire-extinguishers', 'label' => 'Extinguisher supply and service'],
        ],
    ];
}

/**
 * @return list<array{q:string,a:string}>
 */
function hmoCommonFaqs(): array
{
    return [
        [
            'q' => 'What is in the HMO compliance package?',
            'a' => 'The core bundle combines an EICR, a landlord gas safety check where gas appliances or flues are present, and a fire risk assessment for the HMO or multi-occupied house. Emergency lighting, fire alarms and fire doors can be added after we confirm what is already fitted and what your licence or FRA asks for. Price is POA — we issue a fixed quote once scope and access are agreed.',
        ],
        [
            'q' => 'Do you publish fixed HMO package prices online?',
            'a' => 'No. HMO layouts, storeys, shared plant and licence conditions vary too much for an honest catalogue price. We quote POA and confirm a fixed price after we understand the property, certificates due and access.',
        ],
        [
            'q' => 'Is this legal advice or a guarantee of an HMO licence?',
            'a' => 'No. We provide practical compliance work and certificates. Licensing, management regulations and enforcement sit with the local housing authority. Confirm current duties for each property and take legal advice where needed.',
        ],
        [
            'q' => 'Which areas do you cover for HMO work?',
            'a' => 'We are based in Offerton, Stockport SK2 and cover Greater Manchester and the wider North West — including Manchester, Stockport, Salford, Bolton, Oldham and neighbouring towns. Tell us the postcode on the quote form.',
        ],
    ];
}

function hmoFaqJsonLd(array $faqs): string
{
    $main = [];
    foreach ($faqs as $faq) {
        $q = trim((string) ($faq['q'] ?? ''));
        $a = trim((string) ($faq['a'] ?? ''));
        if ($q === '' || $a === '') {
            continue;
        }
        $main[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }
    if ($main === []) {
        return '';
    }
    $json = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $main,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return '<script type="application/ld+json">' . $json . '</script>';
}

/**
 * @param list<array{name:string,url:string}> $crumbs
 */
function hmoBreadcrumbJsonLd(array $crumbs): string
{
    $items = [];
    $pos = 1;
    foreach ($crumbs as $crumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $crumb['name'],
            'item' => $crumb['url'],
        ];
    }
    $json = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return '<script type="application/ld+json">' . $json . '</script>';
}

/**
 * @param list<array{name:string,href:string,current?:bool}> $crumbs
 */
function hmoHeroBreadcrumbs(array $crumbs): string
{
    $html = '<nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">';
    $last = count($crumbs) - 1;
    foreach ($crumbs as $i => $crumb) {
        $name = htmlspecialchars((string) $crumb['name'], ENT_QUOTES, 'UTF-8');
        $href = htmlspecialchars((string) $crumb['href'], ENT_QUOTES, 'UTF-8');
        if ($i > 0) {
            $html .= '<span aria-hidden="true">/</span>';
        }
        if ($i === $last || !empty($crumb['current'])) {
            $html .= '<span class="text-white/80">' . $name . '</span>';
        } else {
            $html .= '<a href="' . $href . '" class="hover:text-white">' . $name . '</a>';
        }
    }
    $html .= '</nav>';
    return $html;
}

function hmoEnsureSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
}

/**
 * @return list<array{href:string,label:string}>
 */
function hmoInternalLinks(): array
{
    return [
        ['href' => url('/pages/packages/hmo'), 'label' => 'HMO packages'],
        ['href' => url('/pages/packages/hmo-compliance'), 'label' => 'HMO compliance package'],
        ['href' => url('/pages/hmo-eicr'), 'label' => 'HMO EICR'],
        ['href' => url('/pages/hmo-fra'), 'label' => 'HMO fire risk assessment'],
        ['href' => url('/pages/hmo-gas-safety'), 'label' => 'HMO gas safety'],
        ['href' => url('/pages/resources/hmo-licence-compliance-checklist'), 'label' => 'HMO licence checklist'],
        ['href' => url('/pages/landlords'), 'label' => 'Landlord services'],
        ['href' => url('/pages/packages'), 'label' => 'All packages'],
        ['href' => url('/pages/resources/landlord-compliance-checklist'), 'label' => 'Landlord checklist'],
        ['href' => url('/pages/services/electrical'), 'label' => 'Electrical / EICR'],
        ['href' => url('/pages/services/gas-systems'), 'label' => 'Gas safety'],
        ['href' => url('/pages/services/fire-risk-assessments'), 'label' => 'Fire risk assessments'],
        ['href' => url('/pages/services/fire-alarms'), 'label' => 'Fire alarms'],
        ['href' => url('/pages/services/emergency-lighting'), 'label' => 'Emergency lighting'],
        ['href' => url('/pages/services/fire-doors'), 'label' => 'Fire doors'],
    ];
}

function hmoLinkChipsHtml(array $links = []): string
{
    if ($links === []) {
        $links = hmoInternalLinks();
    }
    $html = '<div class="flex flex-wrap gap-2">';
    foreach ($links as $link) {
        $html .= '<a href="' . htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') . '"'
            . ' class="px-4 py-2 bg-white border rounded-full text-sm hover:border-[#ff6b00] transition">'
            . htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') . '</a>';
    }
    $html .= '</div>';
    return $html;
}

function hmoQuoteFormHtml(string $csrf, string $defaultService, string $placeholder = ''): string
{
    $services = function_exists('getServices') ? getServices() : [];
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
    $wa = 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode('Hi Icomply, I need an HMO compliance quote');
    if ($placeholder === '') {
        $placeholder = 'HMO address / postcode, storeys, number of lets, certificates due (EICR / gas / FRA), licence status…';
    }
    $hmoOptions = [
        'HMO Compliance Package' => 'HMO Compliance Package (EICR + gas + FRA)',
        'HMO EICR' => 'HMO EICR',
        'HMO fire risk assessment' => 'HMO fire risk assessment',
        'HMO gas safety' => 'HMO gas safety',
        'HMO emergency lighting' => 'HMO emergency lighting',
        'HMO fire alarms' => 'HMO fire alarms',
        'HMO fire doors' => 'HMO fire doors',
        'HMO licence compliance' => 'HMO licence compliance (mixed)',
    ];
    ob_start();
    ?>
<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-10">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Free quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">Request an HMO quote</h2>
            <p class="mt-3 text-zinc-600">POA until we agree scope — then a fixed-price quote. No obligation. We aim to respond within 2 hours on business days.</p>
        </div>
        <form action="<?= htmlspecialchars(url('/contact.php'), ENT_QUOTES, 'UTF-8') ?>" method="POST" class="bg-white border rounded-3xl p-6 md:p-8 space-y-5 shadow-sm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name / agency" required maxlength="120" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required maxlength="40" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" required class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <option value="">Select HMO service…</option>
                    <?php foreach ($hmoOptions as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"<?= $defaultService === $value ? ' selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    <?php foreach ($services as $name): ?>
                        <option value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?> (single)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000" placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit HMO quote</button>
            <p class="text-center text-xs text-zinc-500">
                By submitting you agree to our
                <a href="<?= htmlspecialchars(url('/privacy.php'), ENT_QUOTES, 'UTF-8') ?>" class="underline hover:text-black">Privacy Policy</a>
                and
                <a href="<?= htmlspecialchars(url('/terms.php'), ENT_QUOTES, 'UTF-8') ?>" class="underline hover:text-black">Terms</a>.
            </p>
        </form>
        <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm">
            <a href="<?= htmlspecialchars($wa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-5 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-semibold">WhatsApp</a>
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0a2540]"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars(url('/contact'), ENT_QUOTES, 'UTF-8') ?>" class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black hover:border-[#0a2540]">Contact</a>
        </div>
    </div>
</section>
    <?php
    return (string) ob_get_clean();
}

/**
 * Topic + area landing definitions (unique copy, no doorway spam).
 *
 * @return array<string, array<string, mixed>>
 */
function hmoTopicLandings(): array
{
    $gmTowns = [
        ['name' => 'Stockport', 'href' => url('/pages/areas/stockport')],
        ['name' => 'Manchester', 'href' => url('/pages/areas/manchester')],
        ['name' => 'Salford', 'href' => url('/pages/areas/salford')],
        ['name' => 'Bolton', 'href' => url('/pages/areas/bolton')],
        ['name' => 'Oldham', 'href' => url('/pages/areas/oldham')],
        ['name' => 'Rochdale', 'href' => url('/pages/areas/rochdale')],
        ['name' => 'Bury', 'href' => url('/pages/areas/bury')],
        ['name' => 'Wigan', 'href' => url('/pages/areas/wigan')],
        ['name' => 'Altrincham', 'href' => url('/pages/areas/altrincham')],
        ['name' => 'Sale', 'href' => url('/pages/areas/sale')],
    ];

    return [
        'hmo-eicr' => [
            'path' => '/pages/hmo-eicr',
            'file' => 'pages/hmo-eicr.php',
            'pageTitle' => 'HMO EICR | Electrical Certificates Greater Manchester',
            'metaDesc' => 'HMO EICR (electrical installation condition report) for licensed and licensable houses in multiple occupation across Greater Manchester. Stockport-based. POA after scope.',
            'metaKeywords' => 'HMO EICR, HMO electrical certificate, HMO electrical safety Greater Manchester, landlord EICR Stockport, HMO wiring test Manchester',
            'ogImage' => url('/assets/images/services/electrical.jpg'),
            'h1' => 'HMO EICR',
            'h1Accent' => 'Greater Manchester',
            'badge' => 'Electrical · HMO landlords',
            'intro' => 'An Electrical Installation Condition Report for an HMO is not just a “landlord EICR with a different label”. Shared kitchens, extra consumer units, landlord supplies and tenant alterations all change how the inspection is scoped and recorded. Icomply inspects and certificates HMO electrical installations from Offerton, Stockport, across Greater Manchester.',
            'body' => [
                'Private rented electrical safety rules in England commonly expect a satisfactory EICR at least every five years, and often at a change of tenancy. In an HMO the inspector must be clear about which installation is being tested — landlord common parts, individual lets, and any shared plant rooms — so the report matches how the house is actually used.',
                'We do not invent pass rates or same-day guarantees. If the installation needs C1/C2/FI remedial work, we say so in the report and quote the follow-on work separately. Bundle the EICR with gas safety and an FRA in the HMO compliance package when several certificates fall due together.',
            ],
            'points' => [
                'Scope agreed before we attend — storeys, lets, consumer units and access',
                'Report coded to BS 7671 practice with a clear summary for agents',
                'Optional remedials quoted after the inspection — not buried in a package price',
                'Can be booked with HMO gas safety and FRA on one visit plan',
            ],
            'faqs' => [
                ['q' => 'How often does an HMO need an EICR?', 'a' => 'In England, private rented electrical safety rules commonly require a satisfactory EICR at least every five years, and often when a tenancy changes if the report has expired. Your HMO licence or previous report may ask for a shorter interval. We confirm the last report date when you book.'],
                ['q' => 'Can one EICR cover a whole HMO?', 'a' => 'An EICR covers the installation we agree to inspect. HMOs often have landlord common-part wiring plus supplies into individual lets. We define those boundaries on site so the certificate is usable for licensing and agent files.'],
                ['q' => 'Do you cover HMO EICR in Manchester and Stockport?', 'a' => 'Yes. Dedicated pages for HMO EICR in Stockport and HMO EICR in Manchester sit under this Greater Manchester hub. We travel from SK2 across the conurbation.'],
            ],
            'serviceSlug' => 'electrical',
            'areaLinks' => [
                ['href' => url('/pages/hmo-eicr/stockport'), 'label' => 'HMO EICR in Stockport'],
                ['href' => url('/pages/hmo-eicr/manchester'), 'label' => 'HMO EICR in Manchester'],
                ['href' => url('/pages/electrical/stockport'), 'label' => 'Electrical in Stockport'],
                ['href' => url('/pages/electrical/manchester'), 'label' => 'Electrical in Manchester'],
            ],
            'towns' => $gmTowns,
            'defaultService' => 'HMO EICR',
        ],
        'hmo-fra' => [
            'path' => '/pages/hmo-fra',
            'file' => 'pages/hmo-fra.php',
            'pageTitle' => 'HMO Fire Risk Assessment | FRA Greater Manchester',
            'metaDesc' => 'HMO fire risk assessments for multi-occupied houses across Greater Manchester. Suitable and sufficient FRA with a prioritised action plan. Stockport-based. POA after scope.',
            'metaKeywords' => 'HMO fire risk assessment, HMO FRA Greater Manchester, HMO FRA Stockport, HMO fire safety Manchester, licensed HMO FRA',
            'ogImage' => url('/assets/images/services/fire-risk-assessments.jpg'),
            'h1' => 'HMO fire risk assessment',
            'h1Accent' => 'Greater Manchester',
            'badge' => 'Fire safety · HMO landlords',
            'intro' => 'A suitable and sufficient fire risk assessment is one of the documents licensing officers and responsible persons most often ask for on an HMO. We assess the building as it is used — escape routes, detection, doors, lighting and management — and hand back a written action plan. Based in Stockport SK2, we cover Greater Manchester.',
            'body' => [
                'HMO fire safety is not a photocopy of a single-let checklist. Shared kitchens, inner rooms, basement lets and converted houses change both the hazards and the precautions. Our FRA is practical guidance for the responsible person; it is not a fire certificate, a licence decision or legal advice.',
                'Where the assessment points to detection, emergency lighting or fire doors, we can quote those trades from the same team. Combine FRA with EICR and gas safety in the HMO compliance package when you are preparing a licence application or renewal.',
            ],
            'points' => [
                'Written FRA with prioritised recommendations — not a one-line “pass”',
                'Reviews means of escape, detection, lighting and doorsets as found',
                'Optional follow-on quotes for alarms, emergency lighting and fire doors',
                'Documentation formatted for agent, licence and insurer files',
            ],
            'faqs' => [
                ['q' => 'Does every HMO need a fire risk assessment?', 'a' => 'The Regulatory Reform (Fire Safety) Order applies to the common parts of many HMOs and blocks. Licensing schemes and your own fire strategy may also require a current FRA. Confirm the duty for each property with your local authority — we carry out the assessment; we do not decide the licence.'],
                ['q' => 'How often should an HMO FRA be reviewed?', 'a' => 'Review when the building, occupancy or fire precautions change, and at sensible intervals even if nothing obvious has changed. There is no single statutory “every X years” date that fits every house. We date the assessment and note when a review is wise.'],
                ['q' => 'Can you do FRA plus fire alarms on the same job?', 'a' => 'Yes. If the FRA recommends detection or emergency lighting work, we can quote install or service as an add-on. Scope is confirmed before we start so you are not buying a surprise upgrade.'],
            ],
            'serviceSlug' => 'fire-risk-assessments',
            'areaLinks' => [
                ['href' => url('/pages/hmo-fra/stockport'), 'label' => 'HMO FRA in Stockport'],
                ['href' => url('/pages/hmo-fra/manchester'), 'label' => 'HMO FRA in Manchester'],
                ['href' => url('/pages/fire-risk-assessments/stockport'), 'label' => 'FRA in Stockport'],
                ['href' => url('/pages/fire-risk-assessments/manchester'), 'label' => 'FRA in Manchester'],
            ],
            'towns' => $gmTowns,
            'defaultService' => 'HMO fire risk assessment',
        ],
        'hmo-gas-safety' => [
            'path' => '/pages/hmo-gas-safety',
            'file' => 'pages/hmo-gas-safety.php',
            'pageTitle' => 'HMO Gas Safety | CP12 Greater Manchester',
            'metaDesc' => 'HMO gas safety certificates (CP12 / landlord gas safety record) for shared houses across Greater Manchester. Gas Safe checks of shared and individual appliances. POA after scope.',
            'metaKeywords' => 'HMO gas safety, HMO CP12, HMO gas certificate Greater Manchester, landlord gas safety HMO Stockport, HMO boiler check Manchester',
            'ogImage' => url('/assets/images/services/gas-systems.jpg'),
            'h1' => 'HMO gas safety',
            'h1Accent' => 'Greater Manchester',
            'badge' => 'Gas · HMO landlords',
            'intro' => 'HMO gas safety work is usually an annual landlord gas safety record — still the same legal duty as a single let, but with more appliances, shared boilers and tighter access windows. Icomply’s Gas Safe engineers inspect and certificate HMO gas installations across Greater Manchester from our Stockport SK2 base.',
            'body' => [
                'A typical licensed HMO may have a shared boiler, several cookers and extra flues. We allow realistic appointment time and record each appliance on the safety record so agents and licensing officers can see what was checked. If an appliance is unsafe, the record reflects that status — we do not rewrite outcomes to “help the licence through”.',
                'Where the house also needs an EICR or FRA, book the HMO compliance package so one coordinator plans access. Gas-only visits remain available when that is all that is due.',
            ],
            'points' => [
                'Landlord gas safety record (CP12 / CP44 as required) for HMO appliances',
                'Shared plant and individual cookers recorded clearly',
                'Remedial quotes if appliances fail — separate from the safety check',
                'Portfolio booking for several HMOs on neighbouring streets',
            ],
            'faqs' => [
                ['q' => 'Is HMO gas safety different from a normal CP12?', 'a' => 'The duty to keep gas appliances and flues safe still applies. HMOs often have more appliances and shared heating, so the inspection takes longer and the record must list what was actually checked. Access across several tenants needs planning.'],
                ['q' => 'Do all cookers in an HMO need checking?', 'a' => 'Appliances you provide as the landlord generally sit on the safety record. Tenant-owned appliances can still affect safety in the room — tell us what is landlord-supplied when you book so the scope is honest.'],
                ['q' => 'Can you combine HMO gas safety with an EICR?', 'a' => 'Yes. The HMO compliance package is built for EICR + gas + FRA on one schedule. Gas-only bookings are fine if the electrical report is already in date.'],
            ],
            'serviceSlug' => 'gas-systems',
            'areaLinks' => [
                ['href' => url('/pages/hmo-gas-safety/stockport'), 'label' => 'HMO gas safety in Stockport'],
                ['href' => url('/pages/hmo-gas-safety/manchester'), 'label' => 'HMO gas safety in Manchester'],
                ['href' => url('/pages/gas-systems/stockport'), 'label' => 'Gas safety in Stockport'],
                ['href' => url('/pages/gas-systems/manchester'), 'label' => 'Gas safety in Manchester'],
            ],
            'towns' => $gmTowns,
            'defaultService' => 'HMO gas safety',
        ],
    ];
}

/**
 * Area-specific HMO topic pages (Stockport / Manchester).
 *
 * @return array<string, array<string, mixed>>
 */
function hmoAreaTopicLandings(): array
{
    $topics = hmoTopicLandings();

    $areas = [
        'stockport' => [
            'name' => 'Stockport',
            'slug' => 'stockport',
            'region' => 'Greater Manchester',
            'postcodes' => 'SK1–SK8 and neighbouring SK districts',
            'travel' => 'local coverage from our Offerton SK2 5DE yard',
            'stock' => 'terraced conversions, suburban family HMOs and town-centre multi-lets',
        ],
        'manchester' => [
            'name' => 'Manchester',
            'slug' => 'manchester',
            'region' => 'Greater Manchester core',
            'postcodes' => 'M1–M40 and inner suburbs',
            'travel' => 'typically under 40 minutes from Stockport SK2',
            'stock' => 'Victorian terraces, student houses and converted city stock',
        ],
    ];

    $copy = [
        'hmo-eicr' => [
            'stockport' => [
                'pageTitle' => 'HMO EICR Stockport | Electrical Certificate SK2',
                'metaDesc' => 'HMO EICR in Stockport and SK postcodes — electrical installation condition reports for licensed HMOs. Local engineers from Offerton SK2. POA after scope.',
                'h1Accent' => 'Stockport',
                'intro' => 'Stockport’s HMO stock is often older terrace and semi conversions — extra sockets, split boards and DIY tenant alterations are common findings. We inspect HMO installations across SK1–SK8 from Offerton, with the same EICR discipline we use on Greater Manchester portfolios.',
                'local' => 'If you manage houses in Offerton, Heaton Chapel, Cheadle, Hazel Grove or the town centre, send the last EICR date and a photo of the consumer unit. We will tell you whether a standalone HMO EICR or the full compliance package is the better booking.',
            ],
            'manchester' => [
                'pageTitle' => 'HMO EICR Manchester | Electrical Certificate',
                'metaDesc' => 'HMO EICR in Manchester — electrical condition reports for multi-let houses and licensed HMOs. Stockport-based engineers covering M postcodes. POA after scope.',
                'h1Accent' => 'Manchester',
                'intro' => 'Manchester HMOs — student terraces in the south, city conversions and inner-suburb shared houses — often have more than one board and a mix of landlord and tenant wiring. We travel from Stockport to inspect and certificate those installations with a clearly scoped EICR.',
                'local' => 'Withington, Fallowfield, Rusholme, Hulme and the northern M postcodes are regular runs. Tell us storeys, number of lets and whether the common parts have a separate supply so the quote matches the house, not a generic “3-bed” assumption.',
            ],
        ],
        'hmo-fra' => [
            'stockport' => [
                'pageTitle' => 'HMO Fire Risk Assessment Stockport | FRA SK2',
                'metaDesc' => 'HMO fire risk assessments in Stockport — suitable and sufficient FRA for licensed and licensable HMOs. Local from Offerton SK2. POA after scope.',
                'h1Accent' => 'Stockport',
                'intro' => 'Stockport Council licensing and Greater Manchester fire-safety expectations both look for a current, property-specific FRA on many HMOs. We walk the escape routes, detection and doorsets in SK houses as they are actually occupied — not from a template written for a different town.',
                'local' => 'Town-centre conversions and suburban family HMOs fail for different reasons (inner rooms vs tired FD30s). Describe the layout when you book so the assessment time is realistic. Follow-on fire door or alarm work is quoted only if you ask for it.',
            ],
            'manchester' => [
                'pageTitle' => 'HMO Fire Risk Assessment Manchester | FRA',
                'metaDesc' => 'HMO fire risk assessments in Manchester for multi-let houses and licensed HMOs. Written FRA and action plan. Engineers from Stockport SK2. POA after scope.',
                'h1Accent' => 'Manchester',
                'intro' => 'Manchester HMO FRAs have to deal with dense terrace layouts, basement rooms and shared kitchens that a single-let assessment never sees. We produce a written fire risk assessment for the responsible person and a prioritised list of improvements — without pretending it is a licence grant.',
                'local' => 'Student belts and inner-city conversions are the bulk of our Manchester HMO FRA work. If your licence condition names a detection grade or emergency lighting, say so on the form so we can align the visit with those add-ons.',
            ],
        ],
        'hmo-gas-safety' => [
            'stockport' => [
                'pageTitle' => 'HMO Gas Safety Stockport | CP12 SK postcodes',
                'metaDesc' => 'HMO gas safety certificates in Stockport — CP12 / landlord gas safety records for shared houses. Local Gas Safe visits from Offerton SK2. POA after scope.',
                'h1Accent' => 'Stockport',
                'intro' => 'Annual HMO gas safety in Stockport is often a shared boiler plus several cookers across a terrace conversion. We diary realistic time, record every landlord appliance and return a gas safety record you can file with the licence pack.',
                'local' => 'SK2, SK1, SK3 and the Heatons are short hops from the yard. For a street of HMOs, send a list — we will propose a single-day or two-day run rather than six separate call-outs.',
            ],
            'manchester' => [
                'pageTitle' => 'HMO Gas Safety Manchester | CP12 Certificate',
                'metaDesc' => 'HMO gas safety certificates in Manchester — landlord CP12 records for multi-let houses. Gas Safe inspections from our Stockport team. POA after scope.',
                'h1Accent' => 'Manchester',
                'intro' => 'Manchester HMO gas work is access-heavy: multiple tenants, several cookers and a shared boiler that nobody wants offline on a Friday. We plan the visit, inspect landlord appliances and issue the safety record without inflating the job into a “package” you did not ask for.',
                'local' => 'South Manchester student houses and inner-M conversions are typical. If you also need an EICR or FRA in the same week, use the HMO compliance package so one person owns the diary.',
            ],
        ],
    ];

    $out = [];
    foreach ($topics as $topicKey => $topic) {
        foreach ($areas as $areaKey => $area) {
            $extra = $copy[$topicKey][$areaKey];
            $path = $topic['path'] . '/' . $area['slug'];
            $out[$topicKey . '-' . $areaKey] = [
                'path' => $path,
                'file' => ltrim($path, '/') . '.php',
                'pageTitle' => $extra['pageTitle'],
                'metaDesc' => $extra['metaDesc'],
                'metaKeywords' => $topic['metaKeywords'] . ', ' . $area['name'],
                'ogImage' => $topic['ogImage'],
                'h1' => $topic['h1'],
                'h1Accent' => $extra['h1Accent'],
                'badge' => $topic['badge'] . ' · ' . $area['name'],
                'intro' => $extra['intro'],
                'body' => array_merge($topic['body'], [$extra['local']]),
                'points' => $topic['points'],
                'faqs' => $topic['faqs'],
                'serviceSlug' => $topic['serviceSlug'],
                'areaLinks' => [
                    ['href' => url($topic['path']), 'label' => $topic['h1'] . ' — Greater Manchester'],
                    ['href' => url('/pages/areas/' . $area['slug']), 'label' => 'All services in ' . $area['name']],
                    ['href' => url('/pages/' . $topic['serviceSlug'] . '/' . $area['slug']), 'label' => 'Service page in ' . $area['name']],
                    ['href' => url('/pages/packages/hmo-compliance'), 'label' => 'HMO compliance package'],
                ],
                'towns' => [
                    ['name' => $area['name'] . ' area hub', 'href' => url('/pages/areas/' . $area['slug'])],
                    ['name' => 'HMO EICR in ' . $area['name'], 'href' => url('/pages/hmo-eicr/' . $area['slug'])],
                    ['name' => 'HMO FRA in ' . $area['name'], 'href' => url('/pages/hmo-fra/' . $area['slug'])],
                    ['name' => 'HMO gas safety in ' . $area['name'], 'href' => url('/pages/hmo-gas-safety/' . $area['slug'])],
                    ['name' => 'Greater Manchester HMO packages', 'href' => url('/pages/packages/hmo')],
                ],
                'defaultService' => $topic['defaultService'],
                'parentTopic' => $topicKey,
                'areaName' => $area['name'],
                'stock' => $area['stock'],
            ];
        }
    }
    return $out;
}

function hmoSitemapPaths(): array
{
    $paths = [
        '/pages/packages/hmo',
        '/pages/packages/hmo-compliance',
        '/pages/resources/hmo-licence-compliance-checklist',
    ];
    foreach (hmoTopicLandings() as $page) {
        $paths[] = $page['path'];
    }
    foreach (hmoAreaTopicLandings() as $page) {
        $paths[] = $page['path'];
    }
    return $paths;
}
