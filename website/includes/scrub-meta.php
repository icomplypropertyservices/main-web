<?php
/** Mirror of netlify/lib/scrub-meta.js: drop builder/meta sentences from rendered HTML. */
function icomplyScrubMeta(string $html): string
{
    $meta = '\b(?:this|the) (?:page|readout|pack|guide)\b[^.!?"<>]*?\b(?:does not|doesn\'t|exists|is generated|was built)\b|on this page does not|\bdoes not invent\b|fee language|not a single template|generic town paragraph|invented fee|no invented|answered honestly|badge this page does not print';
    $html = preg_replace(['/ for a page that does not invent a day rate/i', '/,? and this page does not\./i', '/; this page does not [^.!?"<>]*\./i', '/ — Icomply does not invent pound prices/', '/\bThis page does not (claim|name|show|state|say|print|publish a registration)/', '/ and this page does not claim/', '/What this page does not decide/', '/POA quote — no invented fee list/'], ['', '.', '.', '', 'We do not $1', ' and we do not claim', 'What we do not decide', 'Price on application'], $html);
    do {
        $prev = $html;
        $html = preg_replace_callback('/(^|[.!?]\s+|>\s*)([^.!?"<>]*?(?:' . $meta . ')[^.!?"<>]*[.!?])/i', static function ($m) {
        return str_starts_with(trim($m[1]), '>') ? rtrim($m[1]) : rtrim($m[1]) . ' ';
    }, $html);
    } while ($html !== $prev);
    $html = preg_replace(['#<p>\s*</p>#', '# +</(p|li|summary)>#'], ['', '</$1>'], $html);
    return $html;
}
