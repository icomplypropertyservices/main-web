<?php
/**
 * Terms & Conditions — website, services and Shopify shop for UK small business.
 */
require_once __DIR__ . '/config.php';
require_once SITE_ROOT . '/includes/share.php';

$pageTitle = 'Terms & Conditions | Website, Services & Shop';
$metaDesc = 'Terms and conditions for Icomply Property Services (Stockport): website use, compliance service quotes, installations and Shopify product purchases across Greater Manchester and the North West.';
$metaKeywords = 'Icomply terms and conditions, property compliance terms, Shopify shop terms, Stockport electrician terms';
$canonicalUrl = url('/terms.php');
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
            <span class="text-white/80">Terms &amp; Conditions</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                Legal · England &amp; Wales
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Terms &amp; <span class="text-[#ff6b00]">Conditions</span>
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                These terms govern use of the <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> website,
                enquiries, compliance services, and product purchases made through our shop (including Shopify).
            </p>
            <p class="mt-4 text-sm text-white/50">Last updated: <?= htmlspecialchars($updated, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</section>

<!-- QUICK LINKS / TOC -->
<section class="bg-white border-b">
    <div class="max-w-4xl mx-auto px-6 py-6">
        <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-3">On this page</div>
        <nav class="flex flex-wrap gap-2" aria-label="Terms sections">
            <?php
            $toc = [
                'about' => 'About these terms',
                'who-we-are' => 'Who we are',
                'website' => 'Website information',
                'quotes' => 'Quotes & services',
                'shop' => 'Shop & products',
                'installation' => 'Installation',
                'liability' => 'Liability',
                'ip' => 'Intellectual property',
                'acceptable-use' => 'Acceptable use',
                'privacy' => 'Privacy',
                'law' => 'Governing law',
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

        <article id="about" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">1. About these terms</h2>
            <p class="text-zinc-700">
                These terms govern use of the <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> website,
                enquiries, compliance services, and product purchases made through our shop (including Shopify).
                By using this site or instructing us, you agree to these terms. If you do not agree, please do not
                use the site or place an order.
            </p>
        </article>

        <article id="who-we-are" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">2. Who we are</h2>
            <p class="text-zinc-700">
                <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?><br>
                <?= htmlspecialchars(ADDRESS, ENT_QUOTES, 'UTF-8') ?><br>
                Tel:
                <a class="text-[#ff6b00] font-medium" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', PHONE), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
                · Email:
                <a class="text-[#ff6b00] font-medium" href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>
            </p>
            <p class="mt-3 text-sm text-zinc-500">
                We are a UK small business offering property compliance lead generation, local engineering services
                and trade product sales. Specific scopes of work are set out in individual quotations.
            </p>
        </article>

        <article id="website" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">3. Website information</h2>
            <p class="text-zinc-700">
                Content on this site (including service, area, keyword and manufacturer pages) is for general
                information. It does not replace a site survey, formal quotation or statutory advice.
                Specifications, standards references and manufacturer names describe typical work we undertake;
                suitability for your property is confirmed after assessment.
            </p>
            <p class="mt-3 text-zinc-700">
                Browse our
                <a class="text-[#ff6b00] font-medium" href="<?= url('/pages/services/index.php') ?>">services</a>,
                <a class="text-[#ff6b00] font-medium" href="<?= url('/pages/manufacturers/index.php') ?>">manufacturers</a>
                and
                <a class="text-[#ff6b00] font-medium" href="<?= url('/shop/index.php') ?>">shop</a>
                for more detail — always treat published content as guidance until confirmed in writing.
            </p>
        </article>

        <article id="quotes" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">4. Quotes &amp; services</h2>
            <ul class="list-disc pl-5 space-y-2 text-zinc-700">
                <li>Quotes are based on information you provide and may change after survey or access to site conditions.</li>
                <li>Work proceeds under a written or emailed quotation / order confirmation accepted by you.</li>
                <li>You must provide safe access, accurate information and any required permissions or keys.</li>
                <li>Documentation or certification is issued for the work we complete, to the standards and scope agreed in the quote (for example electrical, fire or gas work where applicable). We do not claim certifications we have not issued for your specific job.</li>
                <li>Payment terms are stated on the quote or invoice; late payment may attract interest and suspension of non-critical works.</li>
                <li>Request a free quote via our <a class="text-[#ff6b00] font-medium" href="<?= url('/contact.php') ?>">contact page</a>, phone or WhatsApp.</li>
            </ul>
        </article>

        <article id="shop" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">5. Shop &amp; product sales</h2>
            <ul class="list-disc pl-5 space-y-2 text-zinc-700">
                <li>Online product sales may be fulfilled via Shopify. Checkout terms of Shopify and any shipping/returns policy shown at checkout also apply.</li>
                <li>Prices are in GBP unless stated otherwise and may exclude VAT if shown as such.</li>
                <li>Images and descriptions are illustrative; always check product details before purchase. Manufacturer pages describe brands we commonly install and supply — they are not endorsements by those manufacturers unless expressly stated.</li>
                <li>Risk in goods passes on delivery; title passes on full payment.</li>
                <li>Distance-selling cancellation rights for consumers may apply to goods, subject to statutory exceptions (for example sealed goods opened for hygiene or safety reasons).</li>
            </ul>
            <p class="mt-3 text-sm text-zinc-500">
                Visit the <a class="text-[#ff6b00] font-medium" href="<?= url('/shop/index.php') ?>">Icomply shop</a>
                for trade products and install kits.
            </p>
        </article>

        <article id="installation" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">6. Installation of purchased goods</h2>
            <p class="text-zinc-700">
                Buying a product does not automatically include installation. Installation, commissioning and any
                related certification are separate services and must be booked and quoted independently unless
                expressly included in the product listing or order confirmation.
            </p>
        </article>

        <article id="liability" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">7. Liability</h2>
            <p class="text-zinc-700">
                Nothing in these terms limits liability for death or personal injury caused by negligence, fraud,
                or any other liability that cannot be limited by law. Subject to that, we are not liable for
                indirect or consequential loss, and our total liability for any claim relating to website use is
                limited to £100, and for services or goods to the price paid for the relevant service or goods.
            </p>
        </article>

        <article id="ip" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">8. Intellectual property</h2>
            <p class="text-zinc-700">
                Site content, branding and materials are owned by us or our licensors. Manufacturer names and logos
                remain the property of their respective owners and are used for identification of products and
                systems we work with. You may not copy or reuse our content for commercial purposes without permission.
            </p>
        </article>

        <article id="acceptable-use" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">9. Acceptable use</h2>
            <p class="text-zinc-700">
                You must not misuse the site (including introducing malware, scraping at scale without permission,
                submitting false information, or attempting to disrupt services). We may suspend access where we
                reasonably believe these terms have been breached.
            </p>
        </article>

        <article id="privacy" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">10. Privacy</h2>
            <p class="text-zinc-700">
                How we handle personal data is described in our
                <a class="text-[#ff6b00] font-medium" href="<?= url('/privacy.php') ?>">Privacy Policy</a>.
                By using this site you acknowledge that policy.
            </p>
        </article>

        <article id="law" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">11. Governing law</h2>
            <p class="text-zinc-700">
                These terms are governed by the laws of England and Wales. Courts of England and Wales have exclusive
                jurisdiction, without prejudice to mandatory consumer protections in your place of residence.
            </p>
        </article>

        <article id="contact-us" class="bg-white border border-zinc-200 rounded-3xl p-6 md:p-8 scroll-mt-24">
            <h2 class="text-xl md:text-2xl font-semibold tracking-tight mb-3">12. Contact</h2>
            <p class="text-zinc-700">
                Questions about these terms:
                <a class="text-[#ff6b00] font-medium" href="<?= url('/contact.php') ?>">Contact us</a>
                or email
                <a class="text-[#ff6b00] font-medium" href="mailto:<?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(EMAIL, ENT_QUOTES, 'UTF-8') ?></a>.
            </p>
        </article>

        <!-- Related links -->
        <div class="grid sm:grid-cols-2 gap-4 pt-2">
            <div class="p-6 bg-zinc-50 border border-zinc-200 rounded-3xl">
                <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-2">Related legal</div>
                <a class="block text-[#ff6b00] font-semibold hover:underline" href="<?= url('/privacy.php') ?>">Privacy Policy →</a>
                <p class="text-sm text-zinc-600 mt-2">How we collect, use and protect personal data (UK GDPR).</p>
            </div>
            <div class="p-6 bg-zinc-50 border border-zinc-200 rounded-3xl">
                <div class="text-xs uppercase tracking-[2px] text-zinc-500 font-semibold mb-2">Get in touch</div>
                <a class="block text-[#ff6b00] font-semibold hover:underline" href="<?= url('/contact.php') ?>">Contact / free quote →</a>
                <p class="text-sm text-zinc-600 mt-2">Discuss a job, bulk trade order or site survey.</p>
            </div>
        </div>

        <div class="p-6 md:p-8 bg-[#0a2540] text-white rounded-3xl">
            <div class="text-xs uppercase tracking-[2px] text-[#ff6b00] font-semibold mb-2">Explore Icomply</div>
            <h2 class="text-xl font-semibold tracking-tight mb-3">Ready to work with us?</h2>
            <p class="text-white/75 text-sm mb-5 max-w-xl">
                Compliance services, trade kits and manufacturer support across Greater Manchester and the North West.
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
