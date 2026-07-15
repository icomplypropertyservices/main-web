<?php
/**
 * Privacy Policy — UK GDPR-aligned notice for lead-gen + Shopify storefront.
 */
require_once __DIR__ . '/config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Privacy Policy | How We Use Your Data';
$metaDesc = 'Privacy policy for Icomply Property Services (Stockport). How we collect, use and protect personal data from enquiries, quotes, compliance work and Shopify shop orders. UK GDPR rights explained.';
$metaKeywords = 'Icomply privacy policy, data protection, UK GDPR, Stockport property compliance, Shopify privacy';
$canonicalUrl = url('/privacy.php');
$updated = '12 July 2026';

require SITE_ROOT . '/includes/header.php';
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-[#0a2540] text-white">
    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 20%,#ff6b00,transparent 40%),radial-gradient(circle at 80% 0%,#3b82f6,transparent 35%);"></div>
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-18">
        <nav class="text-xs text-white/50 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
            <span>/</span>
            <span class="text-white/60">Legal</span>
            <span>/</span>
            <span class="text-white/80">Privacy Policy</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Legal · UK GDPR
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Privacy <span class="text-[#ff6b00]">Policy</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                How <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> collects, uses and protects personal data when you contact us,
                request a quote, use our website, or buy products through our shop.
            </p>
            <p class="mt-4 text-sm text-white/50">Last updated: <?= htmlspecialchars($updated, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>

<!-- QUICK LINKS / TOC -->
<section class="bg-white border-b">
    <div class="max-w-4xl mx-auto px-6 py-6">
        <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3">On this page</div>
        <nav class="flex flex-wrap gap-2" aria-label="Privacy policy sections">
            <?php
            $toc = [
                'who-we-are' => 'Who we are',
                'data-we-collect' => 'Data we collect',
                'how-we-use' => 'How we use data',
                'legal-bases' => 'Legal bases',
                'sharing' => 'Sharing',
                'cookies' => 'Cookies',
                'shopify' => 'Shopify store',
                'retention' => 'Retention',
                'your-rights' => 'Your rights',
                'security' => 'Security',
                'changes' => 'Changes',
                'contact-us' => 'Contact',
            ];
            foreach ($toc as $id => $label): ?>
                <a href="#<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                   class="px-3 py-1.5 rounded-full text-xs font-medium border bg-white text-black hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</section>

<!-- BODY -->
<section class="max-w-4xl mx-auto px-6 py-14 md:py-16">
    <div class="space-y-6 text-black leading-relaxed">

        <article id="who-we-are" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">1. Who we are</h2>
            <p class="text-zinc-700">
                <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> (“we”, “us”, “our”) of
                <?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?> provides property compliance services
                (electrical, fire, gas, emergency lighting, security and related work) and related product sales
                via our website and shop. We are a UK-based small business serving Greater Manchester and the North West.
            </p>
            <p class="mt-3 text-zinc-700">
                <strong>Contact:</strong>
                <a class="text-[#ff6b00] font-medium" href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
                ·
                <a class="text-[#ff6b00] font-medium" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', PHONE), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                ·
                <a class="text-[#ff6b00] font-medium" href="<?= url('/contact.php') ?>">Contact form</a>
            </p>
        </article>

        <article id="data-we-collect" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">2. Data we collect</h2>
            <p class="text-zinc-700 mb-3">Depending on how you interact with us, we may collect:</p>
            <ul class="list-disc pl-5 space-y-2 text-zinc-700">
                <li><strong>Identity &amp; contact data</strong> — name, email, phone number, company name</li>
                <li><strong>Property &amp; job details</strong> — address, postcode, service required and notes you submit in quote or contact forms</li>
                <li><strong>Marketing attribution</strong> — e.g. gclid, fbclid when present with form submissions (to measure ad performance)</li>
                <li><strong>Technical data</strong> — IP address, browser type and similar diagnostics for security and site reliability</li>
                <li><strong>Shop / order data</strong> — if you purchase via our Shopify-powered store (payment and fulfilment processed by Shopify)</li>
            </ul>
            <p class="mt-3 text-sm text-zinc-500">We only ask for what we need to respond to enquiries, deliver services or fulfil orders.</p>
        </article>

        <article id="how-we-use" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">3. How we use your data</h2>
            <ul class="list-disc pl-5 space-y-2 text-zinc-700">
                <li>To respond to enquiries and provide quotes, services and support</li>
                <li>To fulfil product orders and process payments (via Shopify where applicable)</li>
                <li>To improve our website, services and customer experience</li>
                <li>To meet legal, insurance and compliance obligations relating to our work</li>
                <li>With consent or legitimate interest, limited marketing about our services (you can opt out at any time)</li>
            </ul>
        </article>

        <article id="legal-bases" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">4. Legal bases (UK GDPR)</h2>
            <p class="text-zinc-700">
                We process personal data under one or more of the following bases:
            </p>
            <ul class="list-disc pl-5 space-y-2 text-zinc-700 mt-3">
                <li><strong>Contract</strong> — or steps prior to entering a contract (quotes, service delivery, orders)</li>
                <li><strong>Legitimate interests</strong> — running, securing and improving our business in ways that do not override your rights</li>
                <li><strong>Legal obligation</strong> — where the law requires us to retain or disclose information</li>
                <li><strong>Consent</strong> — where required (for example certain marketing); you may withdraw consent at any time</li>
            </ul>
        </article>

        <article id="sharing" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">5. Sharing your data</h2>
            <p class="text-zinc-700">
                We may share data with trusted service providers who help us operate (hosting, email, analytics,
                payment and e‑commerce platforms such as Shopify), professional advisers, and authorities when
                required by law. We do <strong>not</strong> sell your personal data.
            </p>
        </article>

        <article id="cookies" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">6. Cookies &amp; tracking</h2>
            <p class="text-zinc-700">
                We may use essential cookies for site function and optional analytics or advertising tags when
                configured (for example Google Analytics / Ads). You can control cookies via your browser settings.
                Advertising click IDs may be stored with form leads so we can measure campaign performance.
            </p>
        </article>

        <article id="shopify" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">7. Shopify store</h2>
            <p class="text-zinc-700">
                When you shop through our Shopify-powered storefront or Buy Buttons, Shopify processes payment and
                order data under their terms and privacy policy. Review Shopify’s documentation for full details of
                their processing. Browse products on our
                <a class="text-[#ff6b00] font-medium" href="<?= url('/shop/index.php') ?>">shop</a>
                or manufacturer pages for brand-specific kits and parts.
            </p>
        </article>

        <article id="retention" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">8. Retention</h2>
            <p class="text-zinc-700">
                We keep enquiry and job records for as long as needed to provide services, handle warranties or claims,
                and meet legal retention periods, then delete or anonymise where practicable.
            </p>
        </article>

        <article id="your-rights" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">9. Your rights</h2>
            <p class="text-zinc-700">
                Under UK data protection law you may have rights to access, rectify, erase, restrict or object to
                processing, and data portability. To exercise rights, contact us using the details above or via our
                <a class="text-[#ff6b00] font-medium" href="<?= url('/contact.php') ?>">contact page</a>.
                You may also complain to the Information Commissioner’s Office (ICO) at
                <a class="text-[#ff6b00] font-medium" href="https://ico.org.uk" target="_blank" rel="noopener noreferrer">ico.org.uk</a>.
            </p>
        </article>

        <article id="security" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">10. Security</h2>
            <p class="text-zinc-700">
                We take reasonable technical and organisational measures to protect personal data. No method of
                transmission over the internet is fully secure; please avoid sending highly sensitive information
                by unencrypted email unless necessary.
            </p>
        </article>

        <article id="changes" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">11. Changes</h2>
            <p class="text-zinc-700">
                We may update this policy from time to time. The “Last updated” date at the top of this page will
                change when we do. Continued use of the site after updates constitutes awareness of the revised policy.
            </p>
        </article>

        <article id="contact-us" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">12. Questions</h2>
            <p class="text-zinc-700">
                Privacy questions:
                <a class="text-[#ff6b00] font-medium" href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
                or
                <a class="text-[#ff6b00] font-medium" href="<?= url('/contact.php') ?>">contact us online</a>.
            </p>
        </article>

        <!-- Related links -->
        <div class="grid sm:grid-cols-2 gap-4 pt-2">
            <div class="p-6 bg-zinc-50 border border-zinc-200 rounded-3xl">
                <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-2">Related legal</div>
                <a class="block text-[#ff6b00] font-semibold hover:underline" href="<?= url('/terms.php') ?>">Terms &amp; Conditions →</a>
                <p class="text-sm text-zinc-600 mt-2">Website use, quotes, services and shop purchases.</p>
            </div>
            <div class="p-6 bg-zinc-50 border border-zinc-200 rounded-3xl">
                <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-2">Get in touch</div>
                <a class="block text-[#ff6b00] font-semibold hover:underline" href="<?= url('/contact.php') ?>">Contact / free quote →</a>
                <p class="text-sm text-zinc-600 mt-2">Call, WhatsApp or send a message about your property.</p>
            </div>
        </div>

        <div class="p-6 md:p-8 bg-[#0a2540] text-white rounded-3xl">
            <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold mb-2">Explore Icomply</div>
            <h2 class="text-xl font-semibold tracking-tight mb-3">Services, shop &amp; brands</h2>
            <p class="text-white/75 text-sm mb-5 max-w-xl">
                Looking for compliance work, trade products or manufacturer support across the North West?
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="<?= url('/contact.php') ?>" class="px-5 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white text-sm">Contact us</a>
                <a href="<?= url('/shop/index.php') ?>" class="px-5 py-3 rounded-2xl bg-white text-[#0a2540] font-semibold text-sm hover:bg-zinc-100">Shop products</a>
                <a href="<?= url('/pages/manufacturers/index.php') ?>" class="px-5 py-3 rounded-2xl border border-white/40 font-semibold text-sm hover:bg-white/10">Manufacturers</a>
                <a href="<?= url('/pages/services/index.php') ?>" class="px-5 py-3 rounded-2xl border border-white/40 font-semibold text-sm hover:bg-white/10">Services</a>
            </div>
        </div>

        <?= shareButtonsHtml($pageTitle, $metaDesc) ?>
    </div>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
