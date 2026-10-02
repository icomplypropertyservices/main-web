<?php
/**
 * Subcontractor onboarding. Pretty URL /become-a-subcontractor.
 * WebPage schema only — not a job advert and not a JobPosting.
 */
require_once __DIR__ . '/config.php';
require_once SITE_ROOT . '/includes/subcontractor-form.php';

$pageTitle = 'Become a subcontractor | iComply Property Services';
$metaDesc = 'Apply to subcontract with iComply Property Services. Electrical, fire, emergency lighting, AOV, CCTV, nurse call and general maintenance across the UK. Call 07517806082.';
$metaKeywords = 'subcontractor, electrical, EICR, fire alarms, emergency lighting, AOV, CCTV, access control, nurse call, general maintenance, UK';
$canonicalUrl = url('/become-a-subcontractor.php');
$metaRobots = 'index, follow';
$pageSchemaType = 'WebPage';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);

require SITE_ROOT . '/includes/header.php';
?>

<section class="page-hero relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20">
        <nav class="crumbs text-sm mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars(rtrim(SITE_URL, '/') . '/', ENT_QUOTES, 'UTF-8') ?>">Home</a>
            <span aria-hidden="true">/</span>
            <span>Become a subcontractor</span>
        </nav>
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]" aria-hidden="true"></span>
                Subcontractors · UK-wide
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                Are you a highly skilled tradesperson with a passion for high-quality work?
            </h1>
            <p class="mt-5 text-lg text-white/80 max-w-2xl">
                <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> takes on property compliance and maintenance across the UK.
                If your work is careful and consistent, we would like to hear from you.
            </p>
            <div class="hero-actions mt-6">
                <a class="px-8 py-4 rounded-2xl bg-white text-[#0B1F3A] font-semibold" href="<?= htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8') ?>">Call <?= htmlspecialchars(PHONE, ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </div>
    </div>
</section>

<section class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-14 md:py-16 grid lg:grid-cols-3 gap-6">
        <article class="border border-zinc-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold tracking-tight">Trades we need</h2>
            <ul class="mt-4 space-y-2 text-zinc-700 text-sm leading-relaxed">
                <li>Electrical / EICR</li>
                <li>Fire alarms</li>
                <li>Emergency lighting</li>
                <li>AOV smoke vents</li>
                <li>CCTV / access control</li>
                <li>Nurse call</li>
                <li>General maintenance</li>
            </ul>
        </article>
        <article class="border border-zinc-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold tracking-tight">What we offer</h2>
            <p class="mt-4 text-zinc-700 text-sm leading-relaxed">
                Steady work, and we handle the admin and the customers.
                You focus on the workmanship. Customer quotes stay POA until the scope is confirmed.
            </p>
            <p class="mt-4 text-zinc-700 text-sm leading-relaxed">
                Coverage is UK-wide. We look for people who can support national work, not only one town.
            </p>
        </article>
        <article class="border border-zinc-200 rounded-3xl p-6 md:p-8">
            <h2 class="text-xl font-semibold tracking-tight">What we ask</h2>
            <ul class="mt-4 space-y-2 text-zinc-700 text-sm leading-relaxed">
                <li>Proof of qualifications</li>
                <li>Public liability insurance</li>
                <li>Right to work in the UK</li>
                <li>Own tools and transport</li>
            </ul>
        </article>
    </div>
</section>

<section id="onboarding-form" class="bg-zinc-50 border-b">
    <div class="max-w-3xl mx-auto px-6 py-16 md:py-20">
        <div class="mb-8">
            <div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Onboarding</div>
            <h2 id="onboarding-heading" class="text-3xl md:text-4xl font-semibold tracking-tight mt-2">Tell us about your work</h2>
            <p class="mt-3 text-zinc-600">
                <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> reviews each application before any work is offered.
                National coverage is welcome.
            </p>
        </div>
        <?= icomplySubcontractorFormMarkup() ?>
        <script>
        (function () {
            var form = document.querySelector('form[name="subcontractor-onboarding"]');
            if (!form) return;
            var box = document.getElementById('sub-errors');
            function show(msg, field) {
                if (!box) {
                    window.alert(msg);
                    return;
                }
                box.hidden = false;
                box.textContent = msg;
                if (field) {
                    field.setAttribute('aria-invalid', 'true');
                    field.focus();
                } else {
                    box.focus();
                }
            }
            form.addEventListener('submit', function (event) {
                form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
                if (box) {
                    box.hidden = true;
                    box.textContent = '';
                }
                var file = form.querySelector('input[name="documents"]');
                if (file && file.files && file.files[0] && file.files[0].size > 8 * 1024 * 1024) {
                    event.preventDefault();
                    show('Please keep uploaded documents to 8MB or less.', file);
                    return;
                }
                var trades = form.querySelectorAll('input[name="trades"]:checked');
                if (!trades.length) {
                    event.preventDefault();
                    var firstTrade = form.querySelector('input[name="trades"]');
                    show('Select at least one trade.', firstTrade);
                }
            });
        }());
        </script>
    </div>
</section>

<?php require SITE_ROOT . '/includes/footer.php'; ?>
