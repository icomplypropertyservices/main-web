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
    $head = $topic . ' ' . $town;
    $opts = [];
    if ($price !== '') {
        $opts[] = $head . ' from ' . $price . ' | Next-Day | ' . ICOMPLY_CTR_PHONE;
        $opts[] = $head . ' from ' . $price . ' | ' . ICOMPLY_CTR_PHONE;
    }
    $opts[] = $head . ' | Same/Next-Day | ' . ICOMPLY_CTR_PHONE;
    $opts[] = $head . ' | Call ' . ICOMPLY_CTR_PHONE;
    $opts[] = $head . ' | iComply';
    foreach ($opts as $o) {
        if (mb_strlen($o) <= 62) {
            return $o;
        }
    }
    return icomplyCtrCut($head, 55) . ' | iComply';
}

function icomplyCtrDesc(string $topic, string $town, string $slug, string $svcSlug = ''): string
{
    $price = icomplyCtrPriceFrom($slug, $svcSlug);
    $lead = $topic . ' in ' . $town . ($price !== '' ? ' from ' . $price : '') . '.';
    $who = icomplyCtrIsGas($slug, $svcSlug)
        ? ' Same/next-day slots, work by Gas Safe registered engineers.'
        : ' Same/next-day slots from our Stockport team.';
    $tail = ' Fixed written quote, certificates emailed. Call ' . ICOMPLY_CTR_PHONE . '.';
    $d = $lead . $who . $tail;
    if (mb_strlen($d) > 160) {
        $d = $lead . $who . ' Call ' . ICOMPLY_CTR_PHONE . '.';
    }
    if (mb_strlen($d) > 160) {
        $d = $lead . ' Same/next-day. Call ' . ICOMPLY_CTR_PHONE . '.';
    }
    return icomplyCtrCut($d, 160);
}
