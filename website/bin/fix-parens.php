<?php
$dir = __DIR__ . "/../templates/services";
foreach (glob($dir . "/*.php") as $file) {
    $c = file_get_contents($file);
    $orig = $c;
    // Fix: url('/path.jpg';  -> url('/path.jpg');
    $c = preg_replace("/url\('(\/[^']+\.jpg)';/", "url('$1');", $c);
    // Fix HTML: <?= url('/path.jpg')  without closing paren before ?>
    // Pattern: url('/assets/....jpg')  ?>  already ok
    // Pattern broken in HTML: url('/assets/foo.jpg')  vs url('/assets/foo.jpg" 
    // Fix: <?= url('/path') ?> where missing ) before ?>
    $c = preg_replace("/url\('(\/[^']+)'\s*\?>/", "url('$1') ?>", $c);
    // Fix: url('/path.jpg') without ) : url('/path.jpg')
    // more general: url('...'); missing )
    $c = preg_replace("/url\('(\/[^']+)';/", "url('$1');", $c);
    // In HTML attributes: url('/x') ?>  already
    // Fix cases like: url('/assets/foo.jpg')  missing paren: url('/assets/foo.jpg')
    // Actually after first replace ogImage should be fixed
    // Check for remaining url('...'); without )
    if (preg_match_all("/url\('([^']+)'(?!\))/", $c, $m)) {
        // url('x' not followed by )
        $c = preg_replace("/url\('([^']+)'(?!\))/", "url('$1')", $c);
    }
    file_put_contents($file, $c);
    $changed = ($c !== $orig) ? 'CHANGED' : 'same';
    // lint
    $tmp = sys_get_temp_dir() . '/lint_' . basename($file);
    // extract only PHP open block for lint is hard; full file has HTML
    echo basename($file) . " $changed\n";
    // show line 5
    $lines = explode("\n", $c);
    echo "  L5: " . trim($lines[4] ?? '') . "\n";
}
