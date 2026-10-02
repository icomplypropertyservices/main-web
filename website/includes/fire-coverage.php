<?php
/**
 * Fire-protection coverage vs local-only services.
 *
 * Fire protection (alarms, FRA, emergency lighting, AOV and the rest of the
 * fire-safety category) is published for every town in data/areas.json —
 * Jack's UK-mainland list as it exists in this repo.
 * Every other service×area landing is Manchester and Burnley only.
 * AOV is in the fire umbrella and is the quality page; fire keyword×town
 * doorways are not generated.
 */
declare(strict_types=1);

/** @return array<string, string> slug => name */
function getFireProtectionServices(): array
{
    $all = function_exists('getServices') ? getServices() : [];
    $slugs = [];
    if (function_exists('getServiceCategories')) {
        $cats = getServiceCategories();
        $slugs = $cats['fire-safety']['services'] ?? [];
    }
    if (!in_array('aov-air-handling', $slugs, true)) {
        $slugs[] = 'aov-air-handling';
    }
    $core = ['fire-alarms', 'fire-risk-assessments', 'emergency-lighting', 'aov-air-handling'];
    foreach ($core as $slug) {
        if (!in_array($slug, $slugs, true)) {
            $slugs[] = $slug;
        }
    }
    $out = [];
    foreach ($slugs as $slug) {
        $slug = (string)$slug;
        if (isset($all[$slug])) {
            $out[$slug] = $all[$slug];
        }
    }
    return $out;
}

function isFireProtectionService(string $serviceSlug): bool
{
    $serviceSlug = function_exists('areaSlug') ? areaSlug($serviceSlug) : $serviceSlug;
    return isset(getFireProtectionServices()[$serviceSlug]);
}

/**
 * Non-fire service×area towns.
 *
 * @return list<string>
 */
function getLocalCoverageAreas(): array
{
    $want = ['Manchester', 'Burnley'];
    $have = [];
    foreach (getAreas() as $area) {
        $have[(string)$area] = true;
    }
    $out = [];
    foreach ($want as $name) {
        if (isset($have[$name])) {
            $out[] = $name;
        }
    }
    return $out;
}

/**
 * Towns that get a /pages/{service}/{town} landing.
 *
 * @return list<string>
 */
function areasForService(string $serviceSlug): array
{
    $serviceSlug = function_exists('areaSlug') ? areaSlug($serviceSlug) : $serviceSlug;
    if (!isset(getServices()[$serviceSlug])) {
        return [];
    }
    if (isFireProtectionService($serviceSlug)) {
        return getAreas();
    }
    return getLocalCoverageAreas();
}

function serviceCoversArea(string $serviceSlug, string $areaNameOrSlug): bool
{
    $want = function_exists('areaSlug') ? areaSlug($areaNameOrSlug) : strtolower($areaNameOrSlug);
    foreach (areasForService($serviceSlug) as $area) {
        if (areaSlug((string)$area) === $want) {
            return true;
        }
    }
    return false;
}

/**
 * Keyword×town towns. Fire keywords stay as hubs only (no thin town spam).
 * Electrical and gas keep the full areas list. Everything else is Manchester + Burnley.
 *
 * @return list<string>
 */
function areasForKeyword(string $keywordSlug): array
{
    $keywordSlug = function_exists('keywordSlug') ? keywordSlug($keywordSlug) : $keywordSlug;
    $meta = function_exists('getMajorKeywords') ? (getMajorKeywords()[$keywordSlug] ?? null) : null;
    if (!is_array($meta)) {
        return [];
    }
    $svc = (string)($meta['service'] ?? '');
    if (isFireProtectionService($svc)) {
        return [];
    }
    if (function_exists('getElectricalGasFamilyServices') && in_array($svc, getElectricalGasFamilyServices(), true)) {
        return getAreas();
    }
    return getLocalCoverageAreas();
}

/** Jack-confirmed published fee for a standard written FRA. Not a guess. */
function fraPublishedPriceAmount(): string
{
    return '350';
}

function fraPublishedPriceLabel(): string
{
    return '£350';
}

function fraPriceSentence(): string
{
    return 'Standard written fire risk assessment: ' . fraPublishedPriceLabel()
        . '. That covers a suitable-and-sufficient FRA and a prioritised action plan for a typical premises. Very large or multi-building sites are confirmed before we book. Follow-on alarms, emergency lighting, doors and AOV work are quoted separately.';
}

/**
 * High-intent AOV guides to link from quality pages. Hubs only — not × every town.
 *
 * @return list<string>
 */
function aovPriorityKeywordSlugs(): array
{
    $want = ['aov-system', 'aov-maintenance', 'aov-control-panel', 'smoke-vent-system', 'smoke-shaft-aov'];
    if (!function_exists('getMajorKeywords')) {
        return $want;
    }
    $all = getMajorKeywords();
    $out = [];
    foreach ($want as $slug) {
        if (isset($all[$slug])) {
            $out[] = $slug;
        }
    }
    return $out;
}

function icomplySitemapIsFireAreaPath(string $path): bool
{
    if (!preg_match('#^/pages/([a-z0-9\-]+)/([a-z0-9\-]+)$#', $path, $m)) {
        return false;
    }
    if (!isFireProtectionService($m[1])) {
        return false;
    }
    $area = function_exists('areaFromSlug') ? areaFromSlug($m[2]) : null;
    return $area !== null && serviceCoversArea($m[1], $area);
}

function fireAreaPriority(string $serviceSlug): string
{
    return match (areaSlug($serviceSlug)) {
        'aov-air-handling' => '0.82',
        'fire-risk-assessments' => '0.78',
        'fire-alarms' => '0.74',
        'emergency-lighting' => '0.72',
        default => '0.64',
    };
}

function fireH(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Extra body for service hubs (AOV quality, FRA fee, vehicle barriers).
 */
function fireHubExtraHtml(string $serviceSlug): string
{
    $serviceSlug = areaSlug($serviceSlug);
    if ($serviceSlug === 'aov-air-handling') {
        $areas = count(areasForService($serviceSlug));
        $guides = '';
        $keywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
        foreach (aovPriorityKeywordSlugs() as $slug) {
            $name = (string)($keywords[$slug]['name'] ?? keywordDisplayName($slug));
            $guides .= '<a class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00]" href="'
                . fireH(url('/pages/keywords/' . $slug)) . '">' . fireH($name) . '</a>';
        }
        return '<section class="max-w-7xl mx-auto px-6 py-16">'
            . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Priority · smoke control</div>'
            . '<h2 class="text-3xl md:text-4xl font-semibold tracking-tight text-black mt-2">AOV and smoke control across ' . (int)$areas . ' towns</h2>'
            . '<div class="mt-6 max-w-3xl space-y-4 text-lg text-zinc-700 leading-relaxed">'
            . '<p>Automatic opening vents are the life-safety system we push hardest. On a stair or smoke shaft they are what keeps the escape route usable when a fire alarm is sounding. A panel fault, a tired actuator or a rain sensor left in the wrong state is not a cosmetic defect.</p>'
            . '<p>We design, install, commission and maintain natural smoke ventilation and related smoke-control controls to BS EN 12101 and BS 9991, with cause-and-effect tied back to the fire alarm. SE Controls, WindowMaster, Geze, D+H, Nuaire and Brooks equipment is serviced rather than ripped out by default.</p>'
            . '<p>Installation is quoted after a survey. Equipment kit prices, where we publish them, sit on the products page and are not a labour figure. Every town in the area list has its own AOV page — we do not pad that with thin keyword-and-town doorway copies.</p>'
            . '</div>'
            . '<div class="mt-8 grid md:grid-cols-3 gap-6">'
            . '<div class="p-6 bg-white border rounded-3xl"><h3 class="font-semibold text-lg">What we attend</h3><p class="mt-2 text-sm text-zinc-600">Roof and façade AOVs, stair and lobby vents, chain and linear actuators, smoke-control panels, batteries, rain and wind sensors, and the fire-alarm interface.</p></div>'
            . '<div class="p-6 bg-white border rounded-3xl"><h3 class="font-semibold text-lg">How a visit runs</h3><p class="mt-2 text-sm text-zinc-600">We prove the vent opens on a fire signal, check the fail position, record battery and actuator health, and leave a note the freeholder can file with the FRA.</p></div>'
            . '<div class="p-6 bg-white border rounded-3xl"><h3 class="font-semibold text-lg">Alongside the rest of fire</h3><p class="mt-2 text-sm text-zinc-600"><a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/services/fire-alarms')) . '">Fire alarms</a>, <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/services/emergency-lighting')) . '">emergency lighting</a> and a <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/services/fire-risk-assessments')) . '">fire risk assessment (' . fireH(fraPublishedPriceLabel()) . ')</a> are the usual companions — quoted on their own pages.</p></div>'
            . '</div>'
            . '<div class="mt-8 flex flex-wrap gap-2">' . $guides . '</div>'
            . '</section>';
    }
    if ($serviceSlug === 'fire-risk-assessments') {
        return '<section class="max-w-7xl mx-auto px-6 py-12">'
            . '<div class="bg-white border-2 border-[#ff6b00] rounded-3xl p-8 md:p-10">'
            . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Published fee</div>'
            . '<h2 class="text-3xl font-semibold tracking-tight mt-2">Fire risk assessment ' . fireH(fraPublishedPriceLabel()) . '</h2>'
            . '<p class="mt-4 text-lg text-zinc-700 max-w-3xl leading-relaxed">' . fireH(fraPriceSentence()) . '</p>'
            . '<p class="mt-4 text-sm text-zinc-600">AOV and smoke control are in the same fire-protection coverage. If the action plan names a vent, stair shaft or alarm, those are separate quotes — start with <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/services/aov-air-handling')) . '">AOV &amp; smoke control</a>.</p>'
            . '</div></section>';
    }
    if ($serviceSlug === 'access-control') {
        return barrierAccessCalloutHtml('hub');
    }
    if (isFireProtectionService($serviceSlug)) {
        $n = count(areasForService($serviceSlug));
        return '<section class="max-w-7xl mx-auto px-6 pb-4">'
            . '<p class="text-zinc-600 max-w-3xl">This fire-protection service is published for all ' . (int)$n
            . ' towns in the area list, with <a class="text-[#ff6b00] font-semibold" href="'
            . fireH(url('/pages/services/aov-air-handling')) . '">AOV &amp; smoke control</a> as the priority companion page.</p></section>';
    }
    return '';
}

/**
 * Extra body for service×area pages.
 */
function fireAreaExtraHtml(string $serviceSlug, string $area): string
{
    $serviceSlug = areaSlug($serviceSlug);
    $areaH = fireH($area);
    if ($serviceSlug === 'aov-air-handling') {
        $keywords = function_exists('getMajorKeywords') ? getMajorKeywords() : [];
        $guides = '';
        foreach (aovPriorityKeywordSlugs() as $slug) {
            $name = (string)($keywords[$slug]['name'] ?? keywordDisplayName($slug));
            $guides .= '<a class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00]" href="'
                . fireH(url('/pages/keywords/' . $slug)) . '">' . fireH($name) . '</a>';
        }
        $angles = function_exists('service_local_angle') ? service_local_angle($serviceSlug, 'AOV & Smoke Control', $area) : '';
        return '<section class="max-w-7xl mx-auto px-6 py-12">'
            . '<div class="rounded-3xl border-2 border-[#ff6b00] bg-white p-8 md:p-10">'
            . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">AOV · ' . $areaH . '</div>'
            . '<h2 class="text-3xl font-semibold tracking-tight mt-2">Smoke control that has to open when the alarm sounds</h2>'
            . '<div class="mt-5 space-y-4 text-zinc-700 leading-relaxed max-w-3xl">'
            . '<p>In ' . $areaH . ', automatic opening vents are how stairwells, lobbies and smoke shafts stay tenable. We treat AOV as fire protection, not as a window job: the vent, the actuator, the control panel and the signal from the fire alarm are one system.</p>'
            . '<p>' . fireH($angles) . '</p>'
            . '<p>A typical ' . $areaH . ' visit proves the vent travels on a fire input, checks it has not been left latched after a weekly test, measures battery standby, and confirms rain or wind sensors are not holding the vent shut. Where the fire strategy expects a specific floor to open, we test that cause-and-effect rather than a single green light on the panel.</p>'
            . '<p>Standards we work to: BS EN 12101 for smoke and heat exhaust ventilation, BS 9991 for residential blocks, and Approved Document B where the design is still on natural smoke ventilation. We will say when a shaft needs a specialist smoke-control designer. We will not invent a CFD study on a service visit.</p>'
            . '<p>Installation and remedial works in ' . $areaH . ' are quoted after survey. Kit prices on the products page are equipment only. A standard <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/fire-risk-assessments/' . areaSlug($area))) . '">fire risk assessment is ' . fireH(fraPublishedPriceLabel()) . '</a>. <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/fire-alarms/' . areaSlug($area))) . '">Fire alarms</a> and <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/emergency-lighting/' . areaSlug($area))) . '">emergency lighting</a> in ' . $areaH . ' are separate fire-protection pages.</p>'
            . '</div>'
            . '<div class="mt-8 grid md:grid-cols-2 gap-4 text-sm">'
            . '<div class="p-5 bg-zinc-50 rounded-2xl"><h3 class="font-semibold">Common ' . $areaH . ' faults</h3><ul class="mt-2 space-y-1 text-zinc-600"><li>Actuator stalled or clutch slipping</li><li>Panel in fault after a mains failure</li><li>Vent blocked by a resident or a maintenance latch</li><li>Fire-alarm interface not firing the right vent</li></ul></div>'
            . '<div class="p-5 bg-zinc-50 rounded-2xl"><h3 class="font-semibold">What you leave with</h3><ul class="mt-2 space-y-1 text-zinc-600"><li>Functional test against the fire signal</li><li>Battery and actuator notes</li><li>Defects written so an FRA action plan can use them</li><li>A quote only where a part or a visit is actually required</li></ul></div>'
            . '</div>'
            . '<h3 class="mt-8 font-semibold">AOV guides — hubs, not a town-spam grid</h3>'
            . '<div class="mt-3 flex flex-wrap gap-2">' . $guides . '</div>'
            . '</div></section>';
    }
    if ($serviceSlug === 'fire-risk-assessments') {
        return '<section class="max-w-7xl mx-auto px-6 py-8"><div class="bg-white border-2 border-[#ff6b00] rounded-3xl p-8">'
            . '<h2 class="text-2xl font-semibold">FRA in ' . $areaH . ' — ' . fireH(fraPublishedPriceLabel()) . '</h2>'
            . '<p class="mt-3 text-zinc-700 leading-relaxed max-w-3xl">' . fireH(fraPriceSentence()) . ' If the findings in ' . $areaH . ' include smoke vents, book <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/aov-air-handling/' . areaSlug($area))) . '">AOV &amp; smoke control in ' . $areaH . '</a> as its own job.</p>'
            . '</div></section>';
    }
    if ($serviceSlug === 'access-control') {
        return barrierAccessCalloutHtml('area', $area);
    }
    if ($serviceSlug === 'fire-alarms' || $serviceSlug === 'emergency-lighting') {
        return '<section class="max-w-7xl mx-auto px-6 py-8"><p class="text-zinc-700 max-w-3xl leading-relaxed">Fire protection in ' . $areaH . ' includes this page plus <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/aov-air-handling/' . areaSlug($area))) . '">AOV &amp; smoke control</a> and a <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/pages/fire-risk-assessments/' . areaSlug($area))) . '">fire risk assessment (' . fireH(fraPublishedPriceLabel()) . ')</a>. Vehicle barriers are access control, not smoke control — see <a class="text-[#ff6b00] font-semibold" href="' . fireH(url('/products#barriers')) . '">barrier packs</a>.</p></section>';
    }
    return '';
}

function barrierAccessCalloutHtml(string $context, string $area = ''): string
{
    $local = getLocalCoverageAreas();
    $townLinks = '';
    foreach ($local as $town) {
        $townLinks .= '<a class="px-4 py-2 bg-white border rounded-full text-sm font-medium hover:border-[#ff6b00]" href="'
            . fireH(url('/pages/access-control/' . areaSlug($town))) . '">Barriers &amp; access in ' . fireH($town) . '</a>';
    }
    $where = $area !== '' ? ' in ' . fireH($area) : '';
    return '<section id="barriers" class="max-w-7xl mx-auto px-6 py-12">'
        . '<div class="bg-[#0B1F3A] text-white rounded-3xl p-8 md:p-10">'
        . '<div class="text-xs uppercase tracking-[3px] text-[#ff6b00] font-semibold">Vehicle barriers · access control</div>'
        . '<h2 class="text-3xl font-semibold tracking-tight mt-2">Car-park barriers' . $where . ', linked from access control</h2>'
        . '<p class="mt-4 text-white/80 max-w-3xl leading-relaxed">Barrier arms are access control: readers, GSM, Videx or Paxton at the pedestal, fire-release where the fire strategy needs the lane to open, and a supply pack that is not a smoke vent. AOV stays on the fire-protection pages. These 5m barrier packs are the ones we publish prices for. Installation is quoted after the lane, loop and supply are seen.</p>'
        . '<ul class="mt-6 grid sm:grid-cols-2 gap-2 text-sm text-white/90">'
        . '<li>BAR-5M-STD — £5,850 ex VAT</li>'
        . '<li>BAR-5M-VIDEX — £7,441.83 ex VAT</li>'
        . '<li>BAR-5M-PAXTON — £8,375.45 ex VAT</li>'
        . '<li>BAR-5M-GSM — £7,393.18 ex VAT</li>'
        . '<li>BAR-5M-ALLIN — £5,199.99 ex VAT</li>'
        . '<li>Install / civils — POA</li>'
        . '</ul>'
        . '<div class="mt-6 flex flex-wrap gap-2">'
        . '<a class="px-4 py-2 bg-[#ff6b00] rounded-full text-sm font-semibold" href="' . fireH(url('/products#barriers')) . '">Barrier packs on products</a>'
        . '<a class="px-4 py-2 bg-white/10 border border-white/30 rounded-full text-sm font-semibold" href="' . fireH(url('/pages/services/access-control')) . '">Access control hub</a>'
        . '<a class="px-4 py-2 bg-white/10 border border-white/30 rounded-full text-sm font-semibold" href="' . fireH(url('/shop/security')) . '">Security shop</a>'
        . $townLinks
        . '</div>'
        . ($context === 'hub'
            ? '<p class="mt-4 text-xs text-white/60">Local access-control pages are Manchester and Burnley. Fire protection, including AOV, is the nationwide area list.</p>'
            : '')
        . '</div></section>';
}
