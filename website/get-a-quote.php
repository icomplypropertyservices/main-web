<?php
/**
 * Customer quote builder — approved North West list prices (Jack 2026-09-30).
 * Route: /get-a-quote. /quote-builder redirects here.
 */
require_once __DIR__ . '/config.php';
require_once SITE_ROOT . '/includes/share.php';
require_once SITE_ROOT . '/includes/quote-builder.php';

$pageTitle = 'Get a quote | North West list prices';
$metaDesc = 'Build a landlord services quote for a typical 6-bed HMO in the North West. FRA, EICR, gas safety, fire alarm and emergency lighting inspections, with multi-property discounts. All-in prices. iComply is not VAT registered.';
$metaKeywords = 'landlord quote, FRA price, EICR HMO, gas safety CP12, fire alarm inspection, North West property compliance';
$canonicalUrl = url('/get-a-quote.php');
$ogImage = url('/assets/images/services/fire-alarms.jpg');

$catalog = quoteBuilderCatalog();
$staticExport = (string)(getenv('ICOMPLY_STATIC_EXPORT') ?: ($_ENV['ICOMPLY_STATIC_EXPORT'] ?? $_SERVER['ICOMPLY_STATIC_EXPORT'] ?? '')) !== '';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
$waText = rawurlencode('Hi iComply, I am building a services quote and would like to book');
$waUrl = 'https://wa.me/' . WHATSAPP . '?text=' . $waText;
$formAction = $staticExport ? '/thank-you' : url('/contact.php');

$byGroup = [];
foreach ($catalog['services'] as $service) {
    if (!is_array($service)) {
        continue;
    }
    $byGroup[(string)($service['group'] ?? 'certs')][] = $service;
}

$flash = quoteBuilderTakeFormFlash();
$formErrors = $flash['errors'];
$posted = $flash['post'];
$rawCount = trim((string)($posted['property_count'] ?? '1'));
if (!preg_match('/^\d{1,4}$/', $rawCount)) {
    $rawCount = '1';
}
$previewProperties = quoteBuilderClampProperties((int)$rawCount);
$previewTier = quoteBuilderTier($catalog, $previewProperties);
$previewHundredths = quoteBuilderPercentHundredths((float)($previewTier['percent'] ?? 0));
$initialQuote = quoteBuilderCalculate($previewProperties, quoteBuilderSelectionFromPost($posted));

$extraHead = '<link rel="stylesheet" href="' . htmlspecialchars(assetUrl('/assets/css/quote-builder.css'), ENT_QUOTES, 'UTF-8') . '">';

require SITE_ROOT . '/includes/header.php';

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => $pageTitle,
            'description' => $metaDesc,
            'isPartOf' => ['@type' => 'WebSite', 'name' => SITE_NAME, 'url' => SITE_URL],
            'inLanguage' => 'en-GB',
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Get a quote', 'item' => $canonicalUrl],
            ],
        ],
    ],
];

/**
 * @param array<string,mixed> $service
 */
$renderCard = static function (array $service) use ($posted, $previewHundredths): void {
    $id = (string)$service['id'];
    $superseded = in_array($id, ['fra', 'eicr', 'gas'], true);
    $mode = (string)($service['qty'] ?? 'once');
    $poa = !empty($service['poa']) || $service['pence'] === null;
    $listPence = $poa ? null : (int)$service['pence'];
    $unitPence = $poa ? null : quoteBuilderUnitPence($service, !empty($service['discount']) ? $previewHundredths : 0);
    $figure = $poa ? 'POA' : quoteBuilderFormatPence((int)$unitPence);
    $was = (!$poa && $unitPence !== null && $listPence !== null && $unitPence !== $listPence)
        ? quoteBuilderFormatPence($listPence)
        : '';
    $listLabel = $poa ? 'POA' : quoteBuilderFormatPence((int)$listPence);
    $discount = !empty($service['discount']);
    $flag = $poa
        ? 'Price on application'
        : ($discount ? 'Certificate discount applies' : 'List price — no multi-property discount');
    $value = (string)$service['name'] . ' — ' . $listLabel;
    $checked = isset($posted['svc_' . $id]) && (string)$posted['svc_' . $id] !== '';
    $bundleOn = isset($posted['svc_bundle']) && (string)$posted['svc_bundle'] !== '';
    if ($id === 'callout-first' && (int)($posted['qty_callout-extra'] ?? 0) > 0) {
        $checked = true;
    }
    $locked = $superseded && $bundleOn;
    $hoursVal = (int)($posted['qty_' . $id] ?? 0);
    if ($hoursVal < 0) {
        $hoursVal = 0;
    }
    if ($hoursVal > 24) {
        $hoursVal = 24;
    }
    $eachVal = (int)($posted['qty_' . $id] ?? 1);
    if ($eachVal < 1) {
        $eachVal = 1;
    }
    if ($eachVal > 99) {
        $eachVal = 99;
    }
    $idAttr = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    $priceHtml = '<span class="qb-price">'
        . '<span class="qb-price-was" data-was-for="' . $idAttr . '"' . ($was === '' ? ' hidden' : '') . '>' . htmlspecialchars($was, ENT_QUOTES, 'UTF-8') . '</span>'
        . '<span class="qb-price-figure" data-price-for="' . $idAttr . '">' . htmlspecialchars($figure, ENT_QUOTES, 'UTF-8') . '</span> '
        . '<span class="qb-meta">' . htmlspecialchars((string)$service['unit'], ENT_QUOTES, 'UTF-8') . '</span>'
        . '</span>';
    ?>
    <?php if ($mode === 'hours'): ?>
    <div class="qb-card qb-card--qty">
            <div>
                <label class="qb-name" for="qty-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$service['name'], ENT_QUOTES, 'UTF-8') ?></label>
                <?= $priceHtml ?>
                <span class="qb-meta"><?= htmlspecialchars((string)$service['detail'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($flag, ENT_QUOTES, 'UTF-8') ?>.</span>
                <span id="qty-help-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" class="sr-only">Additional hours. Leave at 0 if you only need the first hour.</span>
            </div>
            <input id="qty-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-hours="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" name="qty_<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" type="number" inputmode="numeric" min="0" max="24" value="<?= $hoursVal ?>" aria-describedby="qty-help-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
    </div>
        <?php else: ?>
    <div class="qb-item">
        <label class="qb-card<?= $locked ? ' is-locked' : '' ?>">
            <input id="svc-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" type="checkbox" name="svc_<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" data-svc="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-qty-mode="<?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?>"<?= $superseded ? ' data-superseded="1"' : '' ?><?= $checked ? ' checked' : '' ?><?= $locked ? ' disabled' : '' ?>>
            <span>
                <span class="qb-name"><?= htmlspecialchars((string)$service['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?= $priceHtml ?>
                <span class="qb-meta"><?= htmlspecialchars((string)$service['detail'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="qb-meta"><?= htmlspecialchars($flag, ENT_QUOTES, 'UTF-8') ?>.</span>
                <?php if ($superseded): ?>
                    <span class="qb-lock">Not added on top of the bundle.</span>
                <?php endif; ?>
            </span>
        </label>
        <?php if ($mode === 'each'): ?>
            <div class="qb-qty" data-qty-for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"<?= $checked ? '' : ' hidden' ?>>
                <label for="qty-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">Quantity</label>
                <input id="qty-<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" data-each="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" name="qty_<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" type="number" inputmode="numeric" min="1" max="99" value="<?= $eachVal ?>">
            </div>
        <?php endif; ?>
    </div>
        <?php endif; ?>
    <?php
};
?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<script type="application/json" id="quote-builder-catalog"><?= json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>" class="hover:text-white">Home</a>
            <span aria-hidden="true">/</span>
            <span class="text-white/80">Get a quote</span>
        </nav>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
            <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
            Approved list · 30 September 2026
        </div>
        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05] max-w-3xl">
            Build a services quote.<br>
            <span class="text-[#ff6b00]">Live total as you tick.</span>
        </h1>
        <p class="mt-6 text-lg text-white/80 max-w-2xl"><?= htmlspecialchars((string)$catalog['baselineNote'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="mt-3 text-white/70 max-w-2xl"><?= htmlspecialchars((string)$catalog['vatNote'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>" class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="px-8 py-4 rounded-2xl border border-white/40 font-semibold hover:bg-white/10">WhatsApp</a>
        </div>
    </div>
</section>

<section class="qb-page max-w-7xl mx-auto px-6 py-10 md:py-14">
    <?php if ($staticExport): ?>
    <form name="quote-builder" method="POST" action="/thank-you" data-netlify="true" netlify-honeypot="bot-field" hidden>
        <input type="hidden" name="form-name" value="quote-builder">
        <input name="bot-field">
        <input name="name">
        <input name="email">
        <input name="phone">
        <input name="postcode">
        <input name="property_count">
        <input name="tier">
        <input name="quote_total">
        <textarea name="services_summary"></textarea>
        <textarea name="notes"></textarea>
        <input name="source" value="quote-builder">
    </form>
    <?php endif; ?>

    <form id="quote-builder-form" name="quote-builder" method="POST" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>"<?= $staticExport ? ' data-netlify="true" netlify-honeypot="bot-field"' : '' ?> data-phone="<?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="form-name" value="quote-builder">
        <input type="hidden" name="source" value="quote-builder">
        <?php if (!$staticExport): ?>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <input type="hidden" name="gclid" value="<?= htmlspecialchars($_GET['gclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="fbclid" value="<?= htmlspecialchars($_GET['fbclid'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <p class="sr-only">
            <label>Leave this blank if you are a person <input name="bot-field" tabindex="-1" autocomplete="off"></label>
        </p>

        <div class="qb-layout">
            <div class="qb-picker">
                <ol class="qb-tiers" id="qb-tiers" aria-label="Multi-property discount tiers">
                    <?php foreach ($catalog['tiers'] as $tier):
                        if (!is_array($tier)) {
                            continue;
                        }
                        $tierId = (string)($tier['id'] ?? '');
                        $isCurrent = $tierId === (string)$initialQuote['tierId'];
                        $tierPercent = (float)($tier['percent'] ?? 0);
                        $tierPctLabel = $tierPercent > 0
                            ? quoteBuilderFormatPercent($tierPercent) . ' off'
                            : 'List price';
                        ?>
                        <li data-tier-id="<?= htmlspecialchars($tierId, ENT_QUOTES, 'UTF-8') ?>"<?= $isCurrent ? ' class="is-current" aria-current="step"' : '' ?>>
                            <span class="qb-tier-range"><?= htmlspecialchars(quoteBuilderTierRangeLabel($tier), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="qb-tier-pct"><?= htmlspecialchars($tierPctLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <p id="qb-baseline" class="qb-note mt-4"><?= htmlspecialchars((string)$catalog['baselineNote'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="qb-intro mt-3">The property count sets the discount for every certificate and inspection you tick. Those services are priced on each property. Call-outs, one-off fault-finds and remedials stay as a single visit and do not take the discount.</p>

                <?php foreach ($catalog['groups'] as $group):
                    if (!is_array($group)) {
                        continue;
                    }
                    $gid = (string)($group['id'] ?? '');
                    $items = $byGroup[$gid] ?? [];
                    if (!$items) {
                        continue;
                    }
                    $isAdvanced = $gid === 'advanced';
                    if ($isAdvanced): ?>
                        <details class="qb-advanced">
                            <summary><?= htmlspecialchars((string)$group['title'], ENT_QUOTES, 'UTF-8') ?></summary>
                            <p class="qb-intro"><?= htmlspecialchars((string)$group['intro'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="qb-cards">
                                <?php foreach ($items as $service) {
                                    $renderCard($service);
                                } ?>
                            </div>
                        </details>
                    <?php else: ?>
                        <fieldset class="qb-group">
                            <legend><?= htmlspecialchars((string)$group['title'], ENT_QUOTES, 'UTF-8') ?></legend>
                            <p class="qb-intro"><?= htmlspecialchars((string)$group['intro'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="qb-cards">
                                <?php foreach ($items as $service) {
                                    $renderCard($service);
                                } ?>
                            </div>
                        </fieldset>
                    <?php endif;
                endforeach; ?>
                <noscript>
                    <p class="qb-note mt-4">JavaScript is off, so the total will not update on this page. Tick what you need and send the form — we will price the list from your selection.</p>
                </noscript>
            </div>

            <aside class="qb-summary" aria-label="Quote summary">
                <div class="qb-total-card">
                    <div class="qb-total-head">
                        <div class="qb-count">
                            <label class="qb-label" for="qb-property-count">Number of properties</label>
                            <div class="qb-stepper">
                                <button class="qb-step" type="button" id="qb-count-dec" aria-label="Fewer properties">−</button>
                                <input class="qb-field" id="qb-property-count" name="property_count" type="number" inputmode="numeric" min="1" max="999" value="<?= htmlspecialchars($rawCount, ENT_QUOTES, 'UTF-8') ?>" required aria-describedby="qb-baseline qb-next-tier">
                                <button class="qb-step" type="button" id="qb-count-inc" aria-label="More properties">+</button>
                            </div>
                        </div>
                        <div class="qb-total-readout">
                            <p class="qb-kicker">Indicative total</p>
                            <p class="qb-total-figure" id="qb-total"><?= htmlspecialchars((string)$initialQuote['totalLabel'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="qb-tier" id="qb-tier"><?= htmlspecialchars((string)$initialQuote['tierLabel'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <p class="qb-next" id="qb-next-tier"<?= $initialQuote['nextTierLabel'] === '' ? ' hidden' : '' ?>><?= htmlspecialchars((string)$initialQuote['nextTierLabel'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <p class="qb-announce" id="qb-announce" aria-live="polite" aria-atomic="true"><?= htmlspecialchars((string)$initialQuote['announce'], ENT_QUOTES, 'UTF-8') ?></p>
                    <ul class="qb-lines" id="qb-lines" aria-label="Selected services">
                        <?php if (!$initialQuote['lines']): ?>
                            <li>No services selected yet.</li>
                        <?php else: ?>
                            <?php foreach ($initialQuote['lines'] as $line): ?>
                                <li>
                                    <span><?= htmlspecialchars((string)$line['name'] . ' × ' . (string)$line['qty'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="qb-line-amt">
                                        <?php if (empty($line['poa']) && (int)$line['qty'] > 1): ?>
                                            <span class="qb-line-unit"><?= htmlspecialchars((string)$line['unitLabel'] . ' each', ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <?= htmlspecialchars((string)$line['lineLabel'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <p class="qb-saving" id="qb-saving"<?= $initialQuote['savingLabel'] === '' ? ' hidden' : '' ?>><?= htmlspecialchars((string)$initialQuote['savingLabel'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="qb-bundle-msg" id="qb-bundle-note"<?= $initialQuote['bundleNote'] === '' ? ' hidden' : '' ?>><?= htmlspecialchars((string)$initialQuote['bundleNote'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="qb-bundle-msg" id="qb-callout-note"<?= $initialQuote['calloutNote'] === '' ? ' hidden' : '' ?>><?= htmlspecialchars((string)$initialQuote['calloutNote'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="qb-confirm"><?= htmlspecialchars((string)$catalog['confirmNote'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="qb-lead">
                    <h2>Send this quote</h2>
                    <p class="qb-meta">We aim to reply within 2 hours on business days. Nothing is booked until we confirm.</p>
                    <div id="qb-errors" class="qb-errors" role="alert" tabindex="-1"<?= $formErrors ? '' : ' hidden' ?>>
                        <?php foreach ($formErrors as $formError): ?>
                            <p><?= htmlspecialchars((string)$formError, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endforeach; ?>
                    </div>
                    <div>
                        <label class="qb-label" for="qb-name">Name</label>
                        <input class="qb-field" id="qb-name" name="name" type="text" required maxlength="120" autocomplete="name" value="<?= htmlspecialchars((string)($posted['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="qb-label" for="qb-email">Email</label>
                        <input class="qb-field" id="qb-email" name="email" type="email" required autocomplete="email" value="<?= htmlspecialchars((string)($posted['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="qb-label" for="qb-phone">Phone</label>
                        <input class="qb-field" id="qb-phone" name="phone" type="tel" required maxlength="40" autocomplete="tel" value="<?= htmlspecialchars((string)($posted['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="qb-label" for="qb-postcode">Postcode</label>
                        <input class="qb-field" id="qb-postcode" name="postcode" type="text" required maxlength="10" autocomplete="postal-code" placeholder="SK2 5DE" value="<?= htmlspecialchars((string)($posted['postcode'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label class="qb-label" for="qb-notes">Notes</label>
                        <textarea class="qb-field" id="qb-notes" name="notes" rows="4" maxlength="5000" placeholder="Access, panel brand, or anything we should know"><?= htmlspecialchars((string)($posted['notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <textarea id="qb-services-summary" name="services_summary" hidden><?= htmlspecialchars((string)$initialQuote['summary'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    <input type="hidden" id="qb-quote-total" name="quote_total" value="<?= htmlspecialchars((string)$initialQuote['totalLabel'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="qb-tier-value" name="tier" value="<?= htmlspecialchars((string)$initialQuote['tierLabel'], ENT_QUOTES, 'UTF-8') ?>">
                    <button class="qb-submit" type="submit">Send quote request</button>
                    <p class="qb-status" id="qb-status" role="status"></p>
                    <div class="qb-actions">
                        <a href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                        <a class="qb-wa" href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">WhatsApp</a>
                    </div>
                    <p class="qb-meta">By sending you agree to our <a href="<?= url('/privacy.php') ?>">Privacy Policy</a> and <a href="<?= url('/terms.php') ?>">Terms</a>.</p>
                </div>
            </aside>
        </div>
    </form>
</section>
<script src="<?= htmlspecialchars(assetUrl('/assets/js/quote-builder.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
