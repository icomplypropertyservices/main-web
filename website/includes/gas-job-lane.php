<?php
/**
 * Gas safety / CP12 job lane.
 * List price £85 matches the approved North West rate card (typical 6-bed HMO).
 * Draft pages only — this file does not deploy or promote production.
 */
declare(strict_types=1);

if (!defined('SITE_ROOT')) {
    require_once dirname(__DIR__) . '/config.php';
}
require_once SITE_ROOT . '/includes/share.php';

if (!function_exists('gasJobLanePrice')) {
/**
 * @return array{amount:string,pence:int,name:string,unit:string,note:string}
 */
function gasJobLanePrice(): array
{
    return [
        'amount' => '£85',
        'pence' => 8500,
        'name' => 'Landlord gas safety record (CP12)',
        'unit' => 'per property',
        'note' => 'List price for a typical North West 6-bed HMO. All-in. iComply is not VAT registered, so VAT is not added. Remedials and parts are quoted after the visit. Travel outside the North West is agreed before booking. Commercial plant rooms are not this price.',
    ];
}

/**
 * @return list<array{href:string,label:string,text:string}>
 */
function gasJobLaneLandlordLinks(): array
{
    return [
        ['href' => '/pages/landlords', 'label' => 'Landlords & letting agents', 'text' => 'Portfolio landing for certificates, fire and voids.'],
        ['href' => '/pages/services/landlord-compliance', 'label' => 'Landlord compliance service', 'text' => 'Bundle gas with the other certificates on the same schedule.'],
        ['href' => '/pages/landlord-certificates', 'label' => 'Landlord certificates hub', 'text' => 'EICR, gas, FRA, alarms and EPC in one file.'],
        ['href' => '/pages/resources/gas-safety-certificate-landlords', 'label' => 'Landlord gas safety guide', 'text' => 'What the record covers and the usual 12-month rhythm.'],
        ['href' => '/pages/resources/landlord-compliance-checklist', 'label' => 'Landlord compliance checklist', 'text' => 'Gas alongside electrical, alarms and EPC.'],
        ['href' => '/pages/resources/let-ready-void-checklist', 'label' => 'Let-ready void checklist', 'text' => 'Certificates to have ready before keys go out.'],
        ['href' => '/pages/gas-safety-certificate', 'label' => 'Gas safety certificate hub', 'text' => 'The existing quality hub for landlord gas records.'],
        ['href' => '/pages/services/gas-systems', 'label' => 'Gas systems service', 'text' => 'Wider gas work beyond the CP12 visit.'],
    ];
}

/**
 * @return array<string,array<string,mixed>>
 */
function gasJobLanePages(): array
{
    $price = gasJobLanePrice();
    $amount = $price['amount'];

    return [
        'gas-safety' => [
            'kind' => 'hub',
            'crumb' => 'Gas safety job lane',
            'kicker' => 'Job lane · Gas safety / CP12',
            'h1' => 'Gas safety and CP12',
            'lede' => 'Book a landlord gas safety record for a typical North West property. The list price is ' . $amount . ', with the written record back in the tenancy file.',
            'pageTitle' => 'Gas safety / CP12 job lane | ' . $amount . ' | iComply',
            'metaDesc' => 'Gas safety and CP12 job lane for landlords and agents. Landlord gas safety record ' . $amount . ' for a typical North West 6-bed HMO. Stockport-based. Book, call or WhatsApp.',
            'metaKeywords' => 'gas safety certificate, CP12, landlord gas safety, ' . $amount . ', Stockport, Manchester, North West',
            'scopeTitle' => 'What this lane is for',
            'scope' => [
                'This lane is the booking path for a landlord gas safety record — the document people still call a CP12. It is a safety check and a written record, not a boiler service and not a commercial plant-room survey.',
                'The ' . $amount . ' figure is the approved list price for a typical North West 6-bed HMO. Tell us the postcode and what is installed so we can confirm the visit before you are booked.',
            ],
            'points' => [
                ['title' => 'CP12 at ' . $amount, 'text' => 'One property, one landlord gas safety record, Gas Safe registered engineer, tenant-ready paperwork.'],
                ['title' => 'Landlords and agents', 'text' => 'Single lets, voids and portfolios. Link the record to the rest of the landlord file.'],
                ['title' => 'Not included at ' . $amount, 'text' => 'Boiler servicing, remedials, parts, and commercial plant rooms. Those are quoted after we know the scope.'],
            ],
            'steps' => [
                ['title' => 'Send the property', 'text' => 'Postcode, house or HMO, appliance list if you have it, and whether it is occupied or a void.'],
                ['title' => 'We confirm ' . $amount, 'text' => 'If it matches a typical North West landlord record, the list price stands. If it does not, we say so before booking.'],
                ['title' => 'Record in the file', 'text' => 'You get the Landlord Gas Safety Record. Failed appliances stay failed on the record, with a remedial quote if you want the repair.'],
            ],
            'faqs' => [
                ['q' => 'What does ' . $amount . ' cover?', 'a' => 'A landlord gas safety record for a typical North West 6-bed HMO. The price is all-in. iComply is not VAT registered, so VAT is not added. Remedials and parts are quoted after the visit.'],
                ['q' => 'Is this a boiler service?', 'a' => 'No. The CP12 visit is the safety check and the written record. A service is separate work and is quoted on its own.'],
                ['q' => 'Do you cover landlords outside Stockport?', 'a' => 'Yes, across Greater Manchester and the wider North West. Travel outside the North West is agreed before booking.'],
            ],
        ],
        'gas-safety-cp12' => [
            'kind' => 'job',
            'crumb' => 'CP12 gas safety record',
            'kicker' => 'CP12 · ' . $amount . ' list price',
            'h1' => 'Gas safety certificate (CP12)',
            'lede' => 'A Landlord Gas Safety Record for rented homes with gas appliances or flues. List price ' . $amount . ' for a typical North West 6-bed HMO.',
            'pageTitle' => 'Gas safety certificate (CP12) ' . $amount . ' | iComply',
            'metaDesc' => 'Book a landlord gas safety certificate (CP12) for ' . $amount . '. Typical North West 6-bed HMO, all-in, no VAT added. Gas Safe record for Stockport and Greater Manchester landlords.',
            'metaKeywords' => 'CP12, gas safety certificate price, landlord gas safety record ' . $amount . ', Gas Safe Stockport',
            'scopeTitle' => 'What the ' . $amount . ' visit includes',
            'scope' => [
                'The engineer checks the gas appliances, flues and relevant installation pipework that fall under the landlord safety record, then issues the written record for the tenancy file.',
                'Unsafe appliances are recorded as unsafe. We do not rewrite a fail into a pass. Any repair is a separate quote after the visit.',
            ],
            'points' => [
                ['title' => 'The record', 'text' => 'Landlord Gas Safety Record (often called CP12) listing the appliances and flues that were checked.'],
                ['title' => 'Who attends', 'text' => 'A Gas Safe registered engineer. Check the engineer’s ID on the public register. This page does not publish a registration number.'],
                ['title' => 'Price boundary', 'text' => $amount . ' is the typical North West 6-bed HMO list price. Smaller or larger homes are confirmed before booking. Commercial sites are not this price.'],
            ],
            'steps' => [
                ['title' => 'Tell us the appliances', 'text' => 'Boiler, hob, fire or other gas appliances, plus access notes and the postcode.'],
                ['title' => 'Confirm the ' . $amount . ' list price', 'text' => 'We confirm the visit matches the typical North West landlord record before it is booked.'],
                ['title' => 'Hand the record back', 'text' => 'The certificate goes to you or your agent for the tenancy file, with defects written as found.'],
            ],
            'faqs' => [
                ['q' => 'Is ' . $amount . ' a fixed price for every property?', 'a' => 'It is the list price for a typical North West 6-bed HMO landlord gas safety record. Send the postcode and appliance list and we confirm before booking. Commercial plant rooms are scoped separately.'],
                ['q' => 'Is VAT added on top?', 'a' => 'No. iComply is not VAT registered, so VAT is not added. The ' . $amount . ' figure is all-in for that typical record.'],
                ['q' => 'How often is a CP12 due?', 'a' => 'Where the landlord duty applies in England, the usual rhythm is at least every 12 months, with a copy for tenants. This is general guidance, not legal advice. Check current HSE landlord gas safety pages.'],
                ['q' => 'Can an agent book several properties?', 'a' => 'Yes. Use the landlord pages linked below and list the addresses. Each typical property uses the same ' . $amount . ' list price; we confirm anything that sits outside that scope.'],
            ],
        ],
        'landlord-gas-safety' => [
            'kind' => 'job',
            'crumb' => 'Landlord gas safety',
            'kicker' => 'Landlords & letting agents',
            'h1' => 'Landlord gas safety',
            'lede' => 'Annual gas safety records for private landlords and letting agents. Book the CP12 at ' . $amount . ' and keep it with the rest of the landlord file.',
            'pageTitle' => 'Landlord gas safety | CP12 ' . $amount . ' | iComply',
            'metaDesc' => 'Landlord gas safety records (CP12) for Stockport and Greater Manchester agents. ' . $amount . ' list price for a typical North West 6-bed HMO, with links to the landlord compliance pages.',
            'metaKeywords' => 'landlord gas safety, CP12 landlords, letting agent gas certificate, ' . $amount . ', Stockport',
            'scopeTitle' => 'For the tenancy file',
            'scope' => [
                'Landlords and agents use this page when a rented home has gas appliances or flues and the safety record is due. The booking price for a typical North West property is ' . $amount . '.',
                'Pair the record with the landlord certificate hub, the compliance checklist and the void checklist. Those pages cover EICR, alarms and EPC. This page stays on gas.',
            ],
            'points' => [
                ['title' => 'Single lets and voids', 'text' => 'One address, one record, booked before the new tenancy where you can.'],
                ['title' => 'Letting agents', 'text' => 'Send a list of due dates. We confirm which addresses match the ' . $amount . ' list price.'],
                ['title' => 'Shared houses', 'text' => 'A typical 6-bed HMO is the baseline for ' . $amount . '. Tell us if licence conditions ask for anything beyond the gas record.'],
            ],
            'steps' => [
                ['title' => 'Pick the landlord path', 'text' => 'Start here, or open the landlords landing if you also need EICR, fire or a portfolio schedule.'],
                ['title' => 'Book the CP12', 'text' => 'The gas record is ' . $amount . ' for a typical North West 6-bed HMO. Use the form, the phone number or WhatsApp.'],
                ['title' => 'File it with the other certificates', 'text' => 'Keep the gas record next to the EICR and the alarm notes so the agent is not chasing three contractors.'],
            ],
            'faqs' => [
                ['q' => 'Where do I book the ' . $amount . ' CP12?', 'a' => 'On this page, or on the CP12 job page in this lane. Both use the same list price and the same quote form.'],
                ['q' => 'Is this legal advice about landlord duties?', 'a' => 'No. It is a booking page for the gas safety record. Check HSE guidance for the duty that applies to the tenancy.'],
                ['q' => 'Which landlord pages sit next to this one?', 'a' => 'The landlords landing, the landlord compliance service, the certificates hub, the gas safety guide and the compliance checklist. They are linked below.'],
            ],
        ],
    ];
}

function gasJobLaneRender(string $slug): void
{
    $pages = gasJobLanePages();
    if (!isset($pages[$slug])) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }

    $page = $pages[$slug];
    $price = gasJobLanePrice();
    $links = gasJobLaneLandlordLinks();
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', (string)PHONE);
    $waText = 'Hi iComply, I want a landlord gas safety record (CP12) at the £85 list price. Postcode: ';
    $waHref = 'https://wa.me/' . rawurlencode((string)WHATSAPP) . '?text=' . rawurlencode($waText);
    $canonicalPath = '/pages/jobs/' . $slug;

    $pageTitle = (string)$page['pageTitle'];
    $metaDesc = (string)$page['metaDesc'];
    $metaKeywords = (string)$page['metaKeywords'];
    $ogTitle = (string)$page['h1'] . ' — ' . $price['amount'];
    $ogDescription = $metaDesc;
    $ogImage = url('/assets/images/services/gas-systems.jpg');
    $ogImageAlt = 'Gas safety checks for landlords — iComply Property Services';
    $canonicalUrl = url($canonicalPath);

    $home = rtrim(SITE_URL, '/');
    $crumbs = [
        ['name' => 'Landlords', 'href' => '/pages/landlords'],
        ['name' => 'Gas safety job lane', 'href' => '/pages/jobs/gas-safety'],
    ];
    if ($slug !== 'gas-safety') {
        $crumbs[] = ['name' => (string)$page['crumb'], 'href' => $canonicalPath];
    }

    $crumbItems = [[
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => $home . '/',
    ]];
    $pos = 2;
    foreach ($crumbs as $crumb) {
        $crumbItems[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $crumb['name'],
            'item' => $home . $crumb['href'],
        ];
    }
    $faqEntities = [];
    foreach ($page['faqs'] as $faq) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
        ];
    }
    $jsonLd = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $crumbItems,
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqEntities,
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $price['name'],
            'serviceType' => 'Landlord gas safety record',
            'url' => $home . $canonicalPath,
            'areaServed' => 'North West England',
            'provider' => [
                '@type' => 'ProfessionalService',
                'name' => 'iComply Property Services',
                'telephone' => '+447517806082',
                'email' => 'info@icomplypropertyservices.co.uk',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '17 Woodlands Park Road, Offerton',
                    'addressLocality' => 'Stockport',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => '85.00',
                'priceCurrency' => 'GBP',
                'url' => $home . '/pages/jobs/gas-safety-cp12',
                'description' => $price['note'],
            ],
        ],
    ];

    $services = getServices();
    $selectedService = $services['gas-systems'] ?? 'Gas Systems';
    $heading = 'Book a CP12 — ' . $price['amount'];
    $sub = 'Postcode, property type and appliances. We confirm the ' . $price['amount'] . ' list price before the visit is booked.';
    $showHeading = false;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    require SITE_ROOT . '/includes/header.php';
    ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20 grid lg:grid-cols-[1.4fr_0.8fr] gap-10 items-center">
        <div>
            <nav class="text-xs text-white/70 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
                <a href="/" class="hover:text-white">Home</a>
                <?php foreach ($crumbs as $i => $crumb): ?>
                    <span aria-hidden="true">/</span>
                    <?php if ($i === count($crumbs) - 1): ?>
                        <span class="text-white"><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars(url($crumb['href']), ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white"><?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= htmlspecialchars((string)$page['kicker'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05]">
                <?= htmlspecialchars((string)$page['h1'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="mt-6 text-lg md:text-xl text-white/80 max-w-2xl"><?= htmlspecialchars((string)$page['lede'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#book" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Book CP12 — <?= htmlspecialchars($price['amount'], ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">Call <?= htmlspecialchars((string)PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                <a href="<?= htmlspecialchars($waHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
            </div>
        </div>
        <div class="bg-white text-black rounded-3xl p-7 md:p-8" data-gas-price="85">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">List price</div>
            <div class="mt-2 text-5xl font-semibold tracking-tight text-[#0B1F3A]"><?= htmlspecialchars($price['amount'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="mt-2 font-semibold"><?= htmlspecialchars($price['name'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="text-sm text-zinc-500"><?= htmlspecialchars($price['unit'], ENT_QUOTES, 'UTF-8') ?></div>
            <p class="mt-4 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars($price['note'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="#book" class="mt-6 inline-flex px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Book this visit</a>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16 md:py-20">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Scope</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2"><?= htmlspecialchars((string)$page['scopeTitle'], ENT_QUOTES, 'UTF-8') ?></h2>
    <?php foreach ($page['scope'] as $para): ?>
        <p class="mt-4 text-lg text-zinc-700 leading-relaxed max-w-3xl"><?= htmlspecialchars((string)$para, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>
    <div class="mt-8 grid md:grid-cols-3 gap-4">
        <?php foreach ($page['points'] as $point): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-6">
                <h3 class="font-semibold text-lg text-black"><?= htmlspecialchars((string)$point['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars((string)$point['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($page['kind'] === 'hub'): ?>
<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Pages in this lane</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Hub and booking pages</h2>
        <div class="mt-8 grid md:grid-cols-3 gap-4">
            <?php foreach ($pages as $pageSlug => $card): ?>
                <a href="<?= htmlspecialchars(url('/pages/jobs/' . $pageSlug), ENT_QUOTES, 'UTF-8') ?>" class="block bg-zinc-50 border border-zinc-200 rounded-3xl p-6 hover:border-[#ff6b00]">
                    <div class="text-xs uppercase tracking-wider text-[#ff6b00] font-semibold"><?= $pageSlug === 'gas-safety' ? 'Hub' : 'Page' ?></div>
                    <h3 class="mt-2 font-semibold text-xl text-black"><?= htmlspecialchars((string)$card['h1'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-2 text-sm text-zinc-600"><?= htmlspecialchars((string)$card['lede'], ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="mt-4 inline-block text-sm font-semibold text-[#ff6b00]">Open →</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-10 flex flex-wrap gap-3 items-center">
        <span class="text-sm font-semibold text-black">This lane</span>
        <?php foreach ($pages as $pageSlug => $card): ?>
            <a href="<?= htmlspecialchars(url('/pages/jobs/' . $pageSlug), ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 rounded-full border text-sm font-semibold <?= $pageSlug === $slug ? 'bg-[#0B1F3A] text-white border-[#0B1F3A]' : 'bg-white text-black border-zinc-200 hover:border-[#ff6b00]' ?>">
                <?= htmlspecialchars((string)$card['crumb'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">How it works</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Three steps, then the record</h2>
    <div class="grid md:grid-cols-3 gap-6 mt-10">
        <?php $n = 1; foreach ($page['steps'] as $step): ?>
            <div class="bg-white border border-zinc-200 rounded-3xl p-7">
                <div class="text-[#ff6b00] font-semibold text-sm">Step <?= $n++ ?></div>
                <h3 class="font-semibold text-xl text-black mt-2"><?= htmlspecialchars((string)$step['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-zinc-600 mt-3 leading-relaxed"><?= htmlspecialchars((string)$step['text'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="bg-white border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Landlord links</div>
        <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Landlord pages that sit with this lane</h2>
        <ul class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <?php foreach ($links as $link): ?>
                <li>
                    <a class="block h-full bg-zinc-50 border border-zinc-200 rounded-2xl px-5 py-4 hover:border-[#ff6b00]" href="<?= htmlspecialchars(url((string)$link['href']), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="block font-semibold text-black"><?= htmlspecialchars((string)$link['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="block mt-1 text-sm text-zinc-600"><?= htmlspecialchars((string)$link['text'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="mt-6 text-sm text-zinc-600">
            Official guidance:
            <a class="text-[#ff6b00] font-semibold hover:underline" href="https://www.hse.gov.uk/gas/landlords/index.htm" target="_blank" rel="noopener">HSE — gas safety for landlords</a>.
            This lane is not legal advice.
        </p>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">FAQ</div>
    <h2 class="text-3xl font-semibold tracking-tight text-black mt-2">Price and booking</h2>
    <div class="mt-8 space-y-3">
        <?php foreach ($page['faqs'] as $faq): ?>
            <details class="bg-white border border-zinc-200 rounded-2xl p-5 group">
                <summary class="font-semibold text-black cursor-pointer list-none flex items-center justify-between gap-4">
                    <span><?= htmlspecialchars((string)$faq['q'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-[#ff6b00] text-xl leading-none group-open:rotate-45 transition shrink-0" aria-hidden="true">+</span>
                </summary>
                <p class="mt-3 text-sm text-zinc-600 leading-relaxed"><?= htmlspecialchars((string)$faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section id="book" class="bg-[#0B1F3A] text-white border-t border-white/10 scroll-mt-24">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Book</div>
        <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-white mt-2"><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="mt-3 text-white/80"><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Call <?= htmlspecialchars((string)PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($waHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp the £85 CP12</a>
        </div>
        <div class="mt-8">
            <?php require SITE_ROOT . '/includes/quote-form.php'; ?>
        </div>
        <p class="mt-8 text-sm text-white/80 leading-relaxed">
            iComply Property Services, 17 Woodlands Park Road, Offerton, Stockport SK2 5DE
            · <a class="underline" href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            · <a class="underline" href="mailto:info@icomplypropertyservices.co.uk">info@icomplypropertyservices.co.uk</a>
        </p>
    </div>
</section>
    <?php
    require SITE_ROOT . '/includes/footer.php';
}
}

$gasJobSlug = $GAS_JOB ?? '';
gasJobLaneRender(is_string($gasJobSlug) ? $gasJobSlug : '');
