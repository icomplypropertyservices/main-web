<?php
/**
 * Chrome for a single /pages/jobs/{slug} article.
 * Callers supply unique title, description, H1 and body. Quotes stay POA.
 */
declare(strict_types=1);

/**
 * True when an internal href is a page this site can serve.
 */
function jobArticleHrefResolves(string $href): bool
{
    $path = parse_url($href, PHP_URL_PATH) ?: $href;
    $path = '/' . trim((string)$path, '/');
    if (str_ends_with(strtolower($path), '.php')) {
        $path = substr($path, 0, -4);
    }
    if (preg_match('#^/pages/jobs/([a-z0-9\-]+)$#', $path, $m)) {
        return is_file(SITE_ROOT . '/pages/jobs/' . $m[1] . '.php');
    }
    if (preg_match('#^/pages/keywords/([a-z0-9\-]+)$#', $path, $m)) {
        return function_exists('getMajorKeywords') && isset(getMajorKeywords()[$m[1]]);
    }
    if (preg_match('#^/pages/services/([a-z0-9\-]+)$#', $path, $m)) {
        return (function_exists('getServices') && isset(getServices()[$m[1]]))
            || is_file(SITE_ROOT . '/pages/services/' . $m[1] . '.php');
    }
    if (preg_match('#^/pages/manufacturers/([a-z0-9\-]+)$#', $path, $m)) {
        return function_exists('getManufacturerCatalog') && isset(getManufacturerCatalog()[$m[1]]);
    }
    return is_file(SITE_ROOT . $path . '.php') || is_file(SITE_ROOT . $path . '/index.php');
}

/**
 * Public path for a fire-alarm lane slug, or null when that route would 404.
 */
function fireLanePublicPath(string $slug): ?string
{
    $slug = keywordSlug($slug);
    if ($slug === '') {
        return null;
    }
    $dedicated = SITE_ROOT . '/pages/jobs/' . $slug . '.php';
    if ($slug !== 'fire-alarms' && is_file($dedicated)) {
        $head = (string)file_get_contents($dedicated, false, null, 0, 240);
        if (str_contains($head, 'renderFireLaneJob')) {
            return '/pages/jobs/' . $slug;
        }
    }
    if (function_exists('getMajorKeywords') && isset(getMajorKeywords()[$slug])) {
        return '/pages/keywords/' . $slug;
    }
    return null;
}

/**
 * @param array<string, mixed> $page
 */
function renderJobArticle(array $page): void
{
    $slug = keywordSlug((string)($page['slug'] ?? ''));
    $title = trim((string)($page['title'] ?? ''));
    $meta = trim((string)($page['meta'] ?? ''));
    $h1 = trim((string)($page['h1'] ?? ''));
    $paragraphs = array_values(array_filter((array)($page['paragraphs'] ?? []), 'is_string'));
    $points = array_values(array_filter((array)($page['points'] ?? []), 'is_string'));
    $faqs = [];
    foreach ((array)($page['faqs'] ?? []) as $faq) {
        if (!is_array($faq) || count($faq) < 2) {
            continue;
        }
        $q = trim((string)$faq[0]);
        $a = trim((string)$faq[1]);
        if ($q !== '' && $a !== '') {
            $faqs[] = [$q, $a];
        }
    }
    $links = [];
    foreach ((array)($page['links'] ?? []) as $link) {
        if (!is_array($link) || count($link) < 2) {
            continue;
        }
        $href = trim((string)$link[0]);
        $label = trim((string)$link[1]);
        if ($href !== '' && $label !== '' && jobArticleHrefResolves($href)) {
            $links[] = [$href, $label];
        }
    }
    if (
        $slug === ''
        || $title === ''
        || $meta === ''
        || $h1 === ''
        || count($paragraphs) < 2
        || count($points) < 4
        || count($faqs) < 3
        || $links === []
    ) {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }

    $accent = trim((string)($page['accent'] ?? ''));
    $kicker = trim((string)($page['kicker'] ?? 'Job'));
    $lede = trim((string)($page['lede'] ?? $paragraphs[0]));
    $image = trim((string)($page['image'] ?? '/assets/images/services/fire-alarms.jpg'));
    $imageAlt = trim((string)($page['image_alt'] ?? $h1));
    $crumbService = trim((string)($page['service_label'] ?? 'Services'));
    $crumbServiceHref = trim((string)($page['service_href'] ?? '/pages/services'));
    $parentLabel = trim((string)($page['parent_label'] ?? ''));
    $parentHref = trim((string)($page['parent_href'] ?? ''));
    $formOptions = array_values(array_filter((array)($page['form_options'] ?? []), 'is_string'));
    if ($formOptions === []) {
        $formOptions = [$h1];
    }

    $pageTitle = $title;
    $metaTitleExact = true;
    $metaDesc = $meta;
    $metaKeywords = trim((string)($page['keywords'] ?? $h1 . ', Stockport, Manchester, North West'));
    $ogTitle = $h1;
    $ogDescription = $meta;
    $ogImage = url($image);
    $ogImageAlt = $imageAlt;
    $canonicalUrl = url('/pages/jobs/' . $slug);
    $omitPriceRange = true;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    $h = static function (string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    };

    $faqEntities = [];
    foreach ($faqs as [$q, $a]) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
        ];
    }
    $crumbs = [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim(SITE_URL, '/') . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $crumbService, 'item' => url($crumbServiceHref)],
    ];
    $pos = 3;
    if ($parentLabel !== '' && $parentHref !== '') {
        $crumbs[] = ['@type' => 'ListItem', 'position' => $pos, 'name' => $parentLabel, 'item' => url($parentHref)];
        $pos++;
    }
    $crumbs[] = ['@type' => 'ListItem', 'position' => $pos, 'name' => $h1, 'item' => $canonicalUrl];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Service',
                'name' => $h1,
                'serviceType' => $h1,
                'description' => $meta,
                'url' => $canonicalUrl,
                'areaServed' => 'North West England',
                'provider' => [
                    '@type' => 'LocalBusiness',
                    'name' => SITE_NAME,
                    'telephone' => PHONE,
                    'url' => SITE_URL,
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => $crumbs,
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities,
            ],
        ],
    ];

    require_once SITE_ROOT . '/includes/share.php';
    require SITE_ROOT . '/includes/header.php';
    ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<section class="relative overflow-hidden bg-[#0B1F3A] text-white">
    <div class="relative max-w-7xl mx-auto px-6 py-14 md:py-20 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <nav class="text-xs text-white/60 mb-6 flex flex-wrap gap-2 items-center" aria-label="Breadcrumb">
                <a href="<?= rtrim(SITE_URL, '/') ?>/" class="hover:text-white">Home</a>
                <span>/</span>
                <a href="<?= $h(url($crumbServiceHref)) ?>" class="hover:text-white"><?= $h($crumbService) ?></a>
                <?php if ($parentLabel !== '' && $parentHref !== ''): ?>
                <span>/</span>
                <a href="<?= $h(url($parentHref)) ?>" class="hover:text-white"><?= $h($parentLabel) ?></a>
                <?php endif; ?>
                <span>/</span>
                <span class="text-white/90"><?= $h($h1) ?></span>
            </nav>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs tracking-widest uppercase mb-5">
                <span class="w-2 h-2 rounded-full bg-[#ff6b00]"></span>
                <?= $h($kicker) ?>
            </div>
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tighter leading-[1.05]">
                <?= $h($h1) ?><?php if ($accent !== ''): ?><br><span class="text-[#ff6b00]"><?= $h($accent) ?></span><?php endif; ?>
            </h1>
            <p class="mt-6 text-lg text-white/80 max-w-xl"><?= $h($lede) ?></p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#quote" class="px-6 py-3 rounded-2xl bg-[#ff6b00] hover:bg-orange-600 font-semibold text-white">Request a written quote</a>
                <a href="tel:<?= $h(preg_replace('/\s+/', '', (string)PHONE)) ?>" class="px-6 py-3 rounded-2xl border border-white/40 font-semibold hover:bg-white/10"><?= $h((string)PHONE) ?></a>
            </div>
        </div>
        <div class="hidden lg:block">
            <img src="<?= $h(url($image)) ?>" alt="<?= $h($imageAlt) ?>" class="w-full h-80 object-cover rounded-3xl border border-white/10" width="960" height="640">
        </div>
    </div>
</section>
<section class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <?php foreach ($paragraphs as $para): ?>
        <p class="mt-4 text-lg text-zinc-700 leading-relaxed first:mt-0"><?= $h($para) ?></p>
        <?php endforeach; ?>
        <h2 class="mt-10 text-2xl font-semibold text-[#0B1F3A]">What the visit covers</h2>
        <ul class="mt-4 space-y-3">
            <?php foreach ($points as $point): ?>
            <li class="flex gap-3 text-zinc-800"><span class="text-[#ff6b00] font-bold">●</span><span><?= $h($point) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <p class="mt-8 text-sm text-zinc-600">Price on application after we know the site. We do not publish a fee on this page.</p>
        <?php
        require_once SITE_ROOT . '/includes/quality-bar.php';
        $jobImageSlug = 'fire-alarms';
        if (preg_match('#/assets/images/services/([a-z0-9\-]+)\.jpg#', $image, $jobImgMatch)) {
            $jobImageSlug = $jobImgMatch[1];
        }
        echo icomplyQualityBarImages($jobImageSlug, $h1, 'q2-hub-images')['html'];
        ?>
    </div>
</section>
<section class="bg-zinc-50 border-y">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-2xl font-semibold text-[#0B1F3A]">Related jobs</h2>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php foreach ($links as [$href, $label]): ?>
            <a href="<?= $h(url($href)) ?>" class="px-4 py-2 rounded-full bg-white border border-zinc-200 text-sm font-semibold text-[#0B1F3A] hover:border-[#ff6b00]"><?= $h($label) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="bg-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold text-[#0B1F3A] text-center">Questions about this job</h2>
        <div class="mt-8 space-y-3">
            <?php foreach ($faqs as [$q, $a]): ?>
            <details class="bg-white border-2 border-zinc-200 rounded-2xl p-5">
                <summary class="font-bold text-[#0B1F3A] cursor-pointer"><?= $h($q) ?></summary>
                <p class="mt-3 text-sm text-zinc-800 leading-relaxed"><?= $h($a) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
        <div class="mt-10"><?= shareButtonsHtml($h1, $meta) ?></div>
    </div>
</section>
<section id="quote" class="bg-[#0B1F3A] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <h2 class="text-3xl font-semibold text-center">Quote for <?= $h($h1) ?></h2>
        <p class="mt-2 text-center text-white/80">Tell us the postcode and what is already on site. The figure is written after that scope.</p>
        <form action="<?= $h(url('/contact.php')) ?>" method="POST" class="mt-8 bg-white text-zinc-900 rounded-3xl p-6 md:p-8 space-y-4">
            <input type="hidden" name="csrf" value="<?= $h((string)$_SESSION['csrf']) ?>">
            <input type="hidden" name="job" value="<?= $h($slug) ?>">
            <div class="grid md:grid-cols-2 gap-4">
                <input type="text" name="name" placeholder="Full name" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                <input type="email" name="email" placeholder="Email" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <input type="tel" name="phone" placeholder="Phone" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl">
                <select name="service" required class="w-full border border-zinc-300 px-4 py-3 rounded-xl bg-white">
                    <?php foreach ($formOptions as $option): ?>
                    <option value="<?= $h($option) ?>"><?= $h($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <textarea name="message" rows="4" required placeholder="Postcode, what is on site, and whether this is a new job or a fault…" class="w-full border border-zinc-300 px-4 py-3 rounded-xl"></textarea>
            <button type="submit" class="w-full py-4 rounded-xl bg-[#ff6b00] hover:bg-orange-600 text-white font-semibold">Request a written quote</button>
        </form>
    </div>
</section>
    <?php
    require SITE_ROOT . '/includes/footer.php';
}

function renderFireLaneJob(string $slug): void
{
    $slug = keywordSlug($slug);
    $job = null;
    if (function_exists('fireAlarmsLaneJobs')) {
        foreach (fireAlarmsLaneJobs() as $row) {
            if (keywordSlug((string)($row['slug'] ?? '')) === $slug) {
                $job = $row;
                break;
            }
        }
    }
    $content = is_array($job['content'] ?? null) ? $job['content'] : [];
    $copy = fireLaneJobCopy($slug);
    $intro = trim((string)($content['intro'] ?? ''));
    $body = trim((string)($content['body'] ?? ''));
    $meta = trim((string)($content['meta_desc'] ?? ''));
    $points = array_values(array_filter((array)($content['focus_points'] ?? []), 'is_string'));
    $faqs = [];
    foreach ((array)($content['faq'] ?? []) as $faq) {
        if (is_array($faq) && count($faq) >= 2) {
            $faqs[] = [(string)$faq[0], (string)$faq[1]];
        }
    }
    if ($job === null || $copy === null || $intro === '' || $body === '' || $meta === '') {
        http_response_code(404);
        require SITE_ROOT . '/404.php';
        return;
    }

    $links = [];
    foreach ($copy['links'] as $href => $label) {
        $links[] = [$href, $label];
    }

    renderJobArticle([
        'slug' => $slug,
        'title' => $copy['title'],
        'meta' => $meta,
        'keywords' => (string)($content['seo_keywords'] ?? ''),
        'h1' => $copy['h1'],
        'accent' => $copy['accent'],
        'kicker' => $copy['kicker'],
        'lede' => $intro,
        'paragraphs' => [$intro, $body],
        'points' => $points,
        'faqs' => $faqs,
        'links' => $links,
        'image' => '/assets/images/services/fire-alarms.jpg',
        'image_alt' => $copy['h1'] . ' — iComply Property Services, Stockport',
        'service_label' => 'Fire alarms',
        'service_href' => '/pages/services/fire-alarms',
        'parent_label' => 'Job lane',
        'parent_href' => '/pages/jobs/fire-alarms#' . $copy['lane'],
        'form_options' => $copy['form_options'],
    ]);
}

/**
 * Unique titles and H1s for fire-alarm jobs that are not in the keyword catalogue.
 *
 * @return array{title:string,h1:string,accent:string,kicker:string,lane:string,links:array<string,string>,form_options:list<string>}|null
 */
function fireLaneJobCopy(string $slug): ?array
{
    $pages = [
        'fire-alarm-replacement' => [
            'title' => 'Fire alarm replacement to BS 5839 | Stockport & North West',
            'h1' => 'Fire alarm replacement',
            'accent' => 'panel and system, commissioned to BS 5839',
            'kicker' => 'Fire alarms · Install',
            'lane' => 'install',
            'links' => [
                '/pages/jobs/fire-alarms#install' => 'Fire alarm install lane',
                '/pages/services/fire-alarms' => 'Fire alarms service',
                '/pages/keywords/fire-alarm-installation' => 'Fire alarm installation',
                '/pages/jobs/fire-alarm-call-out' => 'Fire alarm call-out',
            ],
            'form_options' => ['Fire alarm replacement', 'Fire alarm panel replacement', 'Fire alarm installation'],
        ],
        'bs-5839-maintenance' => [
            'title' => 'BS 5839 fire alarm maintenance | Stockport & North West',
            'h1' => 'BS 5839 fire alarm maintenance',
            'accent' => 'planned inspection and servicing',
            'kicker' => 'Fire alarms · Maintain',
            'lane' => 'maintain',
            'links' => [
                '/pages/jobs/fire-alarms#maintain' => 'Fire alarm maintenance lane',
                '/pages/services/fire-alarms' => 'Fire alarms service',
                '/pages/keywords/fire-alarm-maintenance' => 'Fire alarm maintenance',
                '/pages/jobs/fire-alarm-ppm' => 'Fire alarm PPM',
            ],
            'form_options' => ['BS 5839 maintenance', 'Fire alarm maintenance contract', 'Fire alarm servicing'],
        ],
        'fire-alarm-ppm' => [
            'title' => 'Fire alarm PPM programme | Stockport & North West',
            'h1' => 'Fire alarm PPM',
            'accent' => 'a dated programme for the sites you run',
            'kicker' => 'Fire alarms · Maintain',
            'lane' => 'maintain',
            'links' => [
                '/pages/jobs/fire-alarms#maintain' => 'Fire alarm maintenance lane',
                '/pages/services/fire-alarms' => 'Fire alarms service',
                '/pages/keywords/fire-alarm-maintenance-contract' => 'Fire alarm maintenance contract',
                '/pages/jobs/bs-5839-maintenance' => 'BS 5839 maintenance',
            ],
            'form_options' => ['Fire alarm PPM', 'Multi-site fire alarm maintenance', 'Fire alarm maintenance contract'],
        ],
        'false-alarm-investigation' => [
            'title' => 'False alarm investigation | Stockport & North West',
            'h1' => 'False alarm investigation',
            'accent' => 'find the device, then the cause',
            'kicker' => 'Fire alarms · Service',
            'lane' => 'service',
            'links' => [
                '/pages/jobs/fire-alarms#service' => 'Fire alarm service lane',
                '/pages/services/fire-alarms' => 'Fire alarms service',
                '/pages/keywords/fire-alarm-fault-finding' => 'Fire alarm fault finding',
                '/pages/jobs/fire-alarm-call-out' => 'Fire alarm call-out',
            ],
            'form_options' => ['False alarm investigation', 'Fire alarm fault finding', 'Fire alarm call-out'],
        ],
        'fire-alarm-call-out' => [
            'title' => 'Fire alarm call-out | Stockport & North West',
            'h1' => 'Fire alarm call-out',
            'accent' => 'panel fault or a system that will not reset',
            'kicker' => 'Fire alarms · Service',
            'lane' => 'service',
            'links' => [
                '/pages/jobs/fire-alarms#service' => 'Fire alarm service lane',
                '/pages/services/fire-alarms' => 'Fire alarms service',
                '/pages/keywords/fire-alarm-repair' => 'Fire alarm repair',
                '/pages/jobs/false-alarm-investigation' => 'False alarm investigation',
            ],
            'form_options' => ['Fire alarm call-out', 'Fire alarm panel fault', 'Fire alarm repair'],
        ],
    ];
    return $pages[$slug] ?? null;
}
