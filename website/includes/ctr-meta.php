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
