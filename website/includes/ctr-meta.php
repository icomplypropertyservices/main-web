<?php
/**
 * Click-worthy <title> / meta description for keyword×town and service×town pages.
 * Phone, town, price-from where we publish one, and a same/next-day promise.
 * Gas wording: work is done by Gas Safe registered engineers — never claim iComply itself is registered.
 */
if (!defined('ICOMPLY_CTR_PHONE')) {
    define('ICOMPLY_CTR_PHONE', '07517 806082');
}

function icomplyCtrPriceFrom(string $slug, string $svcSlug = ''): string
{
    $s = strtolower($slug . ' ' . $svcSlug);
    if (preg_match('/eicr|electrical-(safety|inspection|installation-condition)|landlord-electrical/', $s)) {
        return '£160';
    }
    if (str_contains($s, 'fire-risk-assessment') && function_exists('fraGuidePriceGbp')) {
        return '£' . fraGuidePriceGbp();
    }
    return '';
}

function icomplyCtrIsGas(string $slug, string $svcSlug = ''): bool
{
    return (bool)preg_match('/gas|boiler|cp12/', strtolower($slug . ' ' . $svcSlug));
}

function icomplyCtrCut(string $s, int $max): string
{
    if (mb_strlen($s) <= $max) {
        return $s;
    }
    $cut = mb_substr($s, 0, $max - 1);
    $sp = mb_strrpos($cut, ' ');
    return rtrim(mb_substr($cut, 0, $sp ?: $max - 1), " ,.·|—-") . '…';
}

function icomplyCtrTitle(string $topic, string $town, string $slug, string $svcSlug = ''): string
{
    $price = icomplyCtrPriceFrom($slug, $svcSlug);
    $opts = [];
    if ($price !== '') {
        $opts[] = $topic . ' in ' . $town . ' from ' . $price . ' | Same/Next-Day';
        $opts[] = $topic . ' ' . $town . ' from ' . $price . ' | Same/Next-Day';
        $opts[] = $topic . ' ' . $town . ' from ' . $price;
    }
    $opts[] = $topic . ' in ' . $town . ' | Same/Next-Day Engineers';
    $opts[] = $topic . ' in ' . $town . ' | Same/Next-Day';
    $opts[] = $topic . ' ' . $town . ' | Same/Next-Day';
    $opts[] = $topic . ' in ' . $town . ' | iComply';
    foreach ($opts as $o) {
        if (mb_strlen($o) <= 60) {
            return $o;
        }
    }
    // Never drop the town: shorten the topic instead (strip " by …"/" and …" tails, then word-cut).
    $short = trim(preg_replace('/\s+(by|for|with|from)\s+.*$/i', '', $topic));
    foreach ([$short . ' in ' . $town . ' | iComply', $short . ' ' . $town . ' | iComply', $short . ' ' . $town] as $o) {
        if ($short !== '' && mb_strlen($o) <= 60) {
            return $o;
        }
    }
    $room = 60 - mb_strlen(' ' . $town);
    if ($room >= 12) {
        return icomplyCtrCut($short !== '' ? $short : $topic, $room) . ' ' . $town;
    }
    return icomplyCtrCut($topic . ' ' . $town, 60);
}

function icomplyCtrDesc(string $topic, string $town, string $slug, string $svcSlug = ''): string
{
    $price = icomplyCtrPriceFrom($slug, $svcSlug);
    $lead = $topic . ' in ' . $town . ($price !== '' ? ' from ' . $price : '') . '.';
    $who = icomplyCtrIsGas($slug, $svcSlug)
        ? ' Same/next-day visits, carried out by Gas Safe registered engineers.'
        : ' Same/next-day engineers from our Stockport team.';
    $tail = ' Free written quote, certificates emailed. Call or WhatsApp ' . ICOMPLY_CTR_PHONE . '.';
    foreach ([$lead . $who . $tail, $lead . $who . ' Call or WhatsApp ' . ICOMPLY_CTR_PHONE . '.', $lead . ' Same/next-day. Call ' . ICOMPLY_CTR_PHONE . '.'] as $d) {
        if (mb_strlen($d) <= 160) {
            return $d;
        }
    }
    return icomplyCtrCut($lead . ' Same/next-day. Call ' . ICOMPLY_CTR_PHONE . '.', 160);
}

/**
 * QM #158: PAT / asbestos / legionella have a service town URL (/pages/{slug}/{town}) and a keyword twin
 * (/pages/keywords/{slug}/{town}). The keyword twin gets its own cost/report intent. Returns null for other keywords.
 */
function icomplyKeywordTwinIntent(string $keywordSlug, string $kwName, string $town): ?array
{
    $labels = ['pat-testing' => 'Costs & Certificates', 'asbestos-survey' => 'Survey Types & Reports', 'legionella-risk-assessment' => 'Costs & Written Reports',
        'first-fix-electrical' => 'Costs & What Is Included', 'bathroom-fitting' => 'Costs & Design Options', 'kitchen-fitting' => 'Costs & Design Options',
        'property-renovation' => 'Costs & Project Stages', 'damp-proofing' => 'Costs & Treatment Options', 'bs-5306-extinguisher-service-cost' => 'Prices & What Affects Them'];
    if (!isset($labels[$keywordSlug])) {
        return null;
    }
    $l = $labels[$keywordSlug];
    $title = $kwName . ' ' . $town . ': ' . $l . ' | Same/Next-Day Engineers';
    if (strlen($title) > 70) {
        $title = $kwName . ' ' . $town . ': ' . $l . ' | Same/Next-Day';
    }
    return [
        'label' => $l,
        'title' => $title,
        'desc' => icomplyCtrCut($kwName . ' in ' . $town . ': ' . strtolower($l) . ' explained, and how to book. Same/next-day slots. Call or WhatsApp 07517 806082.', 160),
        'h1_tail' => ': ' . strtolower($l),
    ];
}
