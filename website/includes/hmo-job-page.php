<?php
/**
 * Renderer for HMO landlord package conversion pages.
 * Expects hmo-jobs.php. Quote, call and WhatsApp on every page.
 */
declare(strict_types=1);

require_once __DIR__ . '/hmo-jobs.php';

function hmoJobRender(string $id): void
{
    $page = hmoJobPage($id);
    $bundleLabel = hmoJobFormatPence(hmoJobBundleListPence());
    $phone = defined('PHONE') ? (string)PHONE : '07517806082';
    $phoneHref = 'tel:' . preg_replace('/\s+/', '', $phone);
    $wa = 'https://wa.me/' . (defined('WHATSAPP') ? WHATSAPP : '447517806082')
        . '?text=' . rawurlencode((string)$page['wa']);
    $home = rtrim((string)SITE_URL, '/');

    $pageTitle = (string)$page['pageTitle'];
    $metaDesc = (string)$page['metaDesc'];
    $metaKeywords = (string)$page['keywords'];
    $canonicalUrl = url((string)$page['path']);
    $ogImage = url((string)$page['ogImage']);
    $ogImageAlt = (string)$page['h1'] . ' — iComply Property Services, Stockport';

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    $faqs = hmoJobFaqs($id, $bundleLabel);
    $jsonLd = hmoJobJsonLd($page, $faqs, $home, $bundleLabel);

    require SITE_ROOT . '/includes/header.php';
    echo '<script type="application/ld+json">'
        . json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>';
    ?>
<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/70 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="/" class="hover:text-white">Home</a>
            <?php foreach ($page['crumbs'] as $crumb): ?>
                <span aria-hidden="true">/</span>
                <?php if (!empty($crumb['current'])): ?>
                    <span class="text-white"><?= hmoJobH((string)$crumb['name']) ?></span>
                <?php else: ?>
                    <a href="<?= hmoJobH(url((string)$crumb['href'])) ?>" class="hover:text-white"><?= hmoJobH((string)$crumb['name']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= hmoJobH((string)$page['kicker']) ?>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-semibold tracking-tighter leading-[1.05] text-white">
                <?= hmoJobH((string)$page['h1']) ?>
            </h1>
            <?php if (!empty($page['primaryBundle'])): ?>
                <p class="mt-6 text-5xl font-semibold tracking-tight text-[#ff6b00]" data-hmo-bundle="650"><?= hmoJobH($bundleLabel) ?></p>
                <p class="mt-2 text-sm text-white">Per property · typical 6-bed HMO · North West. All-in. iComply is not VAT registered, so VAT is not added.</p>
            <?php endif; ?>
            <p class="mt-6 text-lg md:text-xl text-white max-w-2xl"><?= hmoJobRich((string)$page['lede']) ?></p>
            <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-white">
                <?php foreach ($page['proof'] as $item): ?>
                    <li><?= hmoJobH((string)$item) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
                <a href="<?= hmoJobH($phoneHref) ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Call <?= hmoJobH($phone) ?></a>
                <a href="<?= hmoJobH($wa) ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white font-semibold text-white hover:bg-white/10">WhatsApp</a>
            </div>
            <p class="mt-6 text-sm text-white/80 max-w-2xl">Not an HMO licence and not legal advice. The council decides the licence. We supply the certificates and practical fire work you book.</p>
        </div>
    </div>
</section>

<?php if (!empty($page['showCards'])): ?>
<section class="max-w-7xl mx-auto px-6 py-16" id="packages">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Choose a package</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2 text-black">Three HMO routes</h2>
    <div class="mt-8 grid md:grid-cols-3 gap-6">
        <?php foreach (hmoJobCards() as $card): ?>
            <article class="border border-zinc-200 rounded-3xl p-6 flex flex-col bg-white <?= !empty($card['bundle']) ? 'ring-2 ring-[#ff6b00]' : '' ?>"<?= !empty($card['bundle']) ? ' data-hmo-bundle="650"' : '' ?>>
                <h3 class="text-xl font-semibold text-black"><?= hmoJobH($card['label']) ?></h3>
                <p class="mt-3 text-2xl font-semibold text-[#0B1F3A]"><?= hmoJobH($card['price']) ?></p>
                <p class="mt-3 text-sm text-zinc-600 flex-1"><?= hmoJobH($card['blurb']) ?></p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="<?= hmoJobH(url($card['href'])) ?>" class="px-5 py-3 rounded-2xl bg-[#0B1F3A] text-white font-semibold text-sm">View package</a>
                    <a href="#quote" class="px-5 py-3 rounded-2xl border border-zinc-300 font-semibold text-sm text-black" data-hmo-service="<?= hmoJobH($card['label']) ?>">Quote</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($page['primaryBundle'])): ?>
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">What <?= hmoJobH($bundleLabel) ?> covers</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2 text-black">One visit plan, three certificates</h2>
    <div class="mt-8 grid md:grid-cols-2 gap-6">
        <div class="border border-zinc-200 rounded-3xl p-6 bg-white">
            <h3 class="font-semibold text-lg text-black">Included</h3>
            <ul class="mt-4 space-y-2 text-sm text-zinc-700">
                <li>Fire risk assessment for the HMO</li>
                <li>EICR on the fixed electrical installation</li>
                <li>Landlord gas safety record where gas is present</li>
                <li>One documentation pack for the agent or licence file</li>
            </ul>
            <p class="mt-4 text-sm text-zinc-600">Bought separately on the same list those three are £350 + £249 + £85. The bundle is <?= hmoJobH($bundleLabel) ?>, and the separate lines are not added again.</p>
        </div>
        <div class="border border-zinc-200 rounded-3xl p-6 bg-white">
            <h3 class="font-semibold text-lg text-black">Not included</h3>
            <ul class="mt-4 space-y-2 text-sm text-zinc-700">
                <li>The HMO licence itself</li>
                <li>Remedials and parts after the survey — those are POA</li>
                <li>Fire alarm installs, emergency lighting, fire doors</li>
                <li>Travel outside the North West — agreed before booking</li>
                <li>A house that is not a typical 6-bed HMO — we re-quote</li>
            </ul>
        </div>
    </div>
    <h3 class="mt-12 text-2xl font-semibold text-black">Portfolio price on the <?= hmoJobH($bundleLabel) ?></h3>
    <p class="mt-2 text-zinc-600 max-w-3xl">The multi-property discount applies once, to the bundle. Each property is rounded to the nearest pound, half up, then multiplied by the number of properties.</p>
    <div class="mt-6 overflow-x-auto">
        <table class="w-full text-sm border border-zinc-200 rounded-2xl bg-white">
            <thead class="bg-zinc-50 text-left">
                <tr>
                    <th class="px-4 py-3 font-semibold">Properties on the same instruction</th>
                    <th class="px-4 py-3 font-semibold">Discount</th>
                    <th class="px-4 py-3 font-semibold">Each property</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (hmoJobBundleTiers() as $tier): ?>
                    <tr class="border-t border-zinc-200">
                        <td class="px-4 py-3"><?= hmoJobH($tier['label']) ?></td>
                        <td class="px-4 py-3"><?= hmoJobH(hmoJobFormatPercent((float)$tier['percent'])) ?></td>
                        <td class="px-4 py-3 font-semibold"><?= hmoJobH($tier['each']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="#quote" class="px-8 py-4 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Get a quote</a>
        <a href="<?= hmoJobH($phoneHref) ?>" class="px-8 py-4 rounded-2xl border border-zinc-300 font-semibold text-black">Call <?= hmoJobH($phone) ?></a>
        <a href="<?= hmoJobH($wa) ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl bg-green-600 hover:bg-green-500 font-semibold text-white">WhatsApp</a>
    </div>
</section>
<?php elseif ($id === 'hmo-fire-safety'): ?>
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Typical 6-bed list</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2 text-black">Fire lines we can price now</h2>
    <p class="mt-4 text-zinc-600 max-w-3xl">These are the approved inspection lines for a typical 6-bed HMO in the North West. The same portfolio discount used on the <?= hmoJobH($bundleLabel) ?> bundle applies to these certificate lines. Installs, fire doors and remedials stay POA after survey.</p>
    <?php hmoJobPriceTable(hmoJobFireLines()); ?>
    <div class="mt-8 border border-[#ff6b00] rounded-3xl p-6 bg-orange-50">
        <h3 class="font-semibold text-lg text-black">When the <?= hmoJobH($bundleLabel) ?> bundle is the better price</h3>
        <p class="mt-2 text-sm text-zinc-700">If the same typical 6-bed also needs an EICR and gas safety, do not add those on top of the FRA. Book the <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= hmoJobH(url('/pages/jobs/hmo-compliance')) ?>">HMO compliance bundle at <?= hmoJobH($bundleLabel) ?></a>. Alarm, lighting and door work stays extra.</p>
        <a href="<?= hmoJobH(url('/pages/jobs/hmo-compliance')) ?>#quote" class="inline-block mt-4 px-6 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold">Quote the <?= hmoJobH($bundleLabel) ?> bundle</a>
    </div>
</section>
<?php elseif ($id === 'hmo-occupancy'): ?>
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Typical 6-bed list</div>
    <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mt-2 text-black">Re-let certificates</h2>
    <p class="mt-4 text-zinc-600 max-w-3xl">EICR and gas for a typical 6-bed HMO in the North West. Smoke and CO alarm checks are scoped on the visit — there is no separate published list price for that check. PAT and EPC stay POA.</p>
    <?php hmoJobPriceTable(hmoJobOccupancyLines()); ?>
    <div class="mt-8 border border-[#ff6b00] rounded-3xl p-6 bg-orange-50">
        <h3 class="font-semibold text-lg text-black">FRA due as well?</h3>
        <p class="mt-2 text-sm text-zinc-700">Then this is not EICR plus gas. FRA, EICR and gas on the same typical 6-bed are the <a class="text-[#ff6b00] font-semibold hover:underline" href="<?= hmoJobH(url('/pages/jobs/hmo-compliance')) ?>"><?= hmoJobH($bundleLabel) ?> compliance bundle</a>.</p>
        <a href="<?= hmoJobH(url('/pages/jobs/hmo-compliance')) ?>#quote" class="inline-block mt-4 px-6 py-3 rounded-2xl bg-[#ff6b00] text-white font-semibold">Quote the <?= hmoJobH($bundleLabel) ?> bundle</a>
    </div>
</section>
<?php endif; ?>

<section class="bg-zinc-50 border-y">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <h2 class="text-3xl font-semibold tracking-tight text-black">How booking works</h2>
        <div class="grid md:grid-cols-3 gap-6 mt-8">
            <?php
            $steps = [
                ['Tell us the house', 'Postcode, bedrooms, storeys, gas or no gas, and which certificates are due.'],
                ['We confirm the list price', 'A typical 6-bed in the North West uses the figures on this page. Anything else is confirmed before you are booked.'],
                ['You get the pack', 'Reports for the work that was booked, ready for the agent file. The licence application stays with you and the council.'],
            ];
            $n = 1;
            foreach ($steps as [$title, $text]): ?>
                <div class="bg-white border border-zinc-200 rounded-3xl p-6">
                    <div class="text-[#ff6b00] font-semibold text-sm">Step <?= $n++ ?></div>
                    <h3 class="font-semibold text-xl mt-2 text-black"><?= hmoJobH($title) ?></h3>
                    <p class="text-sm mt-3 text-zinc-600"><?= hmoJobH($text) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="max-w-3xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-semibold tracking-tight text-black">Questions</h2>
    <div class="mt-6 space-y-3">
        <?php foreach ($faqs as $faq): ?>
            <details class="border border-zinc-200 rounded-2xl p-5 bg-white">
                <summary class="font-semibold cursor-pointer"><?= hmoJobH($faq['q']) ?></summary>
                <p class="mt-3 text-sm text-zinc-700"><?= hmoJobH($faq['a']) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 pb-4">
    <h2 class="text-lg font-semibold text-black">Related</h2>
    <div class="mt-4 flex flex-wrap gap-2">
        <?php foreach (hmoJobRelatedLinks() as $link): ?>
            <a href="<?= hmoJobH(url($link['href'])) ?>" class="px-4 py-2 bg-white border border-zinc-200 rounded-full text-sm hover:border-[#ff6b00]"><?= hmoJobH($link['label']) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<section id="quote" class="bg-zinc-50 border-t">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="text-center mb-8">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Quote</div>
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2"><?= hmoJobH((string)$page['ctaTitle']) ?></h2>
            <p class="mt-3 text-zinc-600"><?= hmoJobRich((string)$page['ctaText']) ?></p>
        </div>
        <form action="<?= hmoJobH(url('/contact.php')) ?>" method="post" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 space-y-4 shadow-sm">
            <input type="hidden" name="csrf" value="<?= hmoJobH((string)$_SESSION['csrf']) ?>">
            <input type="hidden" name="gclid" value="<?= hmoJobH((string)($_GET['gclid'] ?? '')) ?>">
            <input type="hidden" name="fbclid" value="<?= hmoJobH((string)($_GET['fbclid'] ?? '')) ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" required maxlength="120" placeholder="Name or agency" aria-label="Name or agency" class="w-full border px-5 py-3.5 rounded-2xl">
                <input type="email" name="email" required placeholder="Email" aria-label="Email" class="w-full border px-5 py-3.5 rounded-2xl">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" required maxlength="40" placeholder="Phone" aria-label="Phone" class="w-full border px-5 py-3.5 rounded-2xl">
                <select name="service" id="hmo-job-service" required aria-label="HMO package" class="w-full border px-5 py-3.5 rounded-2xl bg-white">
                    <?php foreach (hmoJobServiceOptions() as $value => $label): ?>
                        <option value="<?= hmoJobH($value) ?>"<?= $value === $page['service'] ? ' selected' : '' ?>><?= hmoJobH($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="5" required maxlength="5000" aria-label="Property details" placeholder="Postcode, bedrooms, storeys, gas yes/no, licence renewal date…" class="w-full border px-5 py-3.5 rounded-2xl"></textarea>
            <button type="submit" class="w-full modern-btn text-white py-4 text-lg font-semibold rounded-2xl">Submit HMO quote</button>
            <p class="text-center text-xs text-zinc-500">By submitting you agree to our <a class="underline" href="<?= hmoJobH(url('/privacy.php')) ?>">Privacy Policy</a> and <a class="underline" href="<?= hmoJobH(url('/terms.php')) ?>">Terms</a>.</p>
        </form>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="<?= hmoJobH($wa) ?>" target="_blank" rel="noopener" class="px-5 py-2.5 rounded-2xl bg-green-600 text-white font-semibold">WhatsApp <?= hmoJobH($phone) ?></a>
            <a href="<?= hmoJobH($phoneHref) ?>" class="px-5 py-2.5 rounded-2xl border border-zinc-300 font-semibold text-black">Call <?= hmoJobH($phone) ?></a>
        </div>
        <p class="mt-6 text-center text-xs text-zinc-500">iComply Property Services · 17 Woodlands Park Road, Offerton, Stockport SK2 5DE · <?= hmoJobH($phone) ?> · <?= hmoJobH(defined('EMAIL') ? (string)EMAIL : 'info@icomplypropertyservices.co.uk') ?></p>
    </div>
</section>
<script>
(function () {
    var select = document.getElementById('hmo-job-service');
    if (!select) return;
    var map = {
        'Compliance bundle': 'HMO compliance bundle (£650)',
        'Fire safety pack': 'HMO fire safety pack',
        'Occupancy pack': 'HMO occupancy pack'
    };
    document.querySelectorAll('[data-hmo-service]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var val = map[btn.getAttribute('data-hmo-service')] || '';
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value === val) {
                    select.selectedIndex = i;
                }
            }
        });
    });
})();
</script>
    <?php
    require SITE_ROOT . '/includes/footer.php';
}

function hmoJobH(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function hmoJobRich(string $text): string
{
    $safe = hmoJobH($text);
    return preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $safe) ?? $safe;
}

/**
 * @param list<array{name:string,price:string,note:string}> $lines
 */
function hmoJobPriceTable(array $lines): void
{
    echo '<div class="mt-6 overflow-x-auto"><table class="w-full text-sm border border-zinc-200 bg-white rounded-2xl">';
    echo '<thead class="bg-zinc-50 text-left"><tr><th class="px-4 py-3">Line</th><th class="px-4 py-3">List</th><th class="px-4 py-3">Scope</th></tr></thead><tbody>';
    foreach ($lines as $line) {
        echo '<tr class="border-t border-zinc-200"><td class="px-4 py-3 font-semibold">' . hmoJobH($line['name']) . '</td>';
        echo '<td class="px-4 py-3">' . hmoJobH($line['price']) . '</td>';
        echo '<td class="px-4 py-3 text-zinc-600">' . hmoJobH($line['note']) . '</td></tr>';
    }
    echo '</tbody></table></div>';
}

/**
 * @return list<array{q:string,a:string}>
 */
function hmoJobFaqs(string $id, string $bundleLabel): array
{
    $shared = [
        [
            'q' => 'Is this an HMO licence?',
            'a' => 'No. Licensing is the local authority. These packages are certificates and practical fire work. This page is not legal advice.',
        ],
        [
            'q' => 'Does the price include remedials?',
            'a' => 'No. The list covers the inspection or assessment named. Parts and remedial labour are quoted after we see the findings.',
        ],
        [
            'q' => 'Do you add VAT?',
            'a' => 'iComply is not VAT registered, so VAT is not added to these list prices.',
        ],
    ];
    $specific = [
        'hmo' => [
            [
                'q' => 'Which package is £650?',
                'a' => 'Only the compliance bundle: FRA, EICR and gas together on a typical 6-bed HMO in the North West. Fire-only and re-let-only jobs use their own lines.',
            ],
        ],
        'hmo-compliance' => [
            [
                'q' => 'What if the HMO has no gas?',
                'a' => 'The £650 bundle is not used. EICR is £249 and the FRA is £350 on the typical 6-bed list. Tell us on the form so we do not include a gas record you do not need.',
            ],
            [
                'q' => 'What if I already have a current FRA?',
                'a' => 'Use the occupancy pack for EICR and gas. Do not pay £650 for a certificate you are not booking.',
            ],
        ],
        'hmo-fire-safety' => [
            [
                'q' => 'Is the fire pack £650?',
                'a' => 'No. £650 is FRA plus EICR plus gas. A fire-only visit uses the FRA at £350 and the inspection lines shown, with installs quoted after survey.',
            ],
        ],
        'hmo-occupancy' => [
            [
                'q' => 'When does a re-let become the £650 bundle?',
                'a' => 'When the fire risk assessment is booked as well, on a typical 6-bed HMO in the North West. FRA, EICR and gas are then one £650 line.',
            ],
        ],
    ];
    return array_merge($specific[$id] ?? [], $shared);
}

/**
 * @param array<string,mixed> $page
 * @param list<array{q:string,a:string}> $faqs
 * @return list<array<string,mixed>>
 */
function hmoJobJsonLd(array $page, array $faqs, string $home, string $bundleLabel): array
{
    $crumbs = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $home . '/'],
    ];
    $pos = 2;
    foreach ($page['crumbs'] as $crumb) {
        $crumbs[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $crumb['name'],
            'item' => $home . $crumb['href'],
        ];
    }
    $entities = [];
    foreach ($faqs as $faq) {
        $entities[] = [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
        ];
    }
    $graph = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $crumbs,
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ],
    ];
    if (!empty($page['primaryBundle'])) {
        $graph[] = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'HMO compliance bundle',
            'serviceType' => 'FRA, EICR and landlord gas safety',
            'areaServed' => 'North West England',
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => 'iComply Property Services',
                'telephone' => '+447517806082',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '17 Woodlands Park Road',
                    'addressLocality' => 'Offerton, Stockport',
                    'postalCode' => 'SK2 5DE',
                    'addressCountry' => 'GB',
                ],
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => '650',
                'priceCurrency' => 'GBP',
                'description' => $bundleLabel . ' for a typical 6-bed HMO in the North West. FRA, EICR and gas are included once. Remedials POA. VAT is not added.',
                'url' => $home . $page['path'],
            ],
        ];
    }
    return $graph;
}

/**
 * @return array<string,string>
 */
function hmoJobServiceOptions(): array
{
    return [
        'HMO landlord packages' => 'HMO landlord packages',
        'HMO compliance bundle (£650)' => 'HMO compliance bundle (£650) — FRA + EICR + gas',
        'HMO fire safety pack' => 'HMO fire safety pack',
        'HMO occupancy pack' => 'HMO occupancy pack (re-let)',
    ];
}

/**
 * @return list<array{href:string,label:string}>
 */
function hmoJobRelatedLinks(): array
{
    return [
        ['href' => '/pages/jobs/hmo', 'label' => 'All HMO packages'],
        ['href' => '/pages/jobs/hmo-compliance', 'label' => '£650 compliance bundle'],
        ['href' => '/pages/jobs/hmo-fire-safety', 'label' => 'Fire safety pack'],
        ['href' => '/pages/jobs/hmo-occupancy', 'label' => 'Occupancy pack'],
        ['href' => '/pages/landlords', 'label' => 'Landlord services'],
        ['href' => '/pages/packages', 'label' => 'Other packages'],
        ['href' => '/pages/services/electrical', 'label' => 'Electrical / EICR'],
        ['href' => '/pages/services/gas-systems', 'label' => 'Gas safety'],
        ['href' => '/pages/services/fire-risk-assessments', 'label' => 'Fire risk assessments'],
        ['href' => '/pages/services/fire-alarms', 'label' => 'Fire alarms'],
        ['href' => '/pages/services/emergency-lighting', 'label' => 'Emergency lighting'],
        ['href' => '/contact', 'label' => 'Contact'],
    ];
}
