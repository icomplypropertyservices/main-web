<?php
/**
 * One-shot: convert {{PLACEHOLDER}} templates to pure extract vars (executeTemplateVars).
 * Usage: php bin/convert-templates-off-eval.php [--dry-run]
 */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv ?? [], true);

$htmlRaw = [
    'MANUFACTURER_TAGS',
    'MANUFACTURER_IMAGES',
    'KEYWORD_FOCUS_HTML',
    'KEYWORD_FAQ_HTML',
    'MFR_SERVICES_HTML',
    'MFR_PRODUCTS_HTML',
    'MFR_RELATED_HTML',
];

$files = [
    $root . '/templates/combo.php',
    $root . '/templates/keyword.php',
    $root . '/templates/area.php',
    $root . '/templates/service.php',
    $root . '/templates/manufacturer.php',
];

function convertPlaceholders(string $c, array $htmlRaw): string {
    // 1) Inside single-quoted PHP strings → concatenation with $VAR
    $c = preg_replace_callback(
        "/'((?:\\\\'|[^'])*)'/",
        static function (array $m): string {
            $inner = $m[1];
            if (strpos($inner, '{{') === false) {
                return $m[0];
            }
            $parts = preg_split('/\{\{([A-Z0-9_]+)\}\}/', $inner, -1, PREG_SPLIT_DELIM_CAPTURE);
            $out = [];
            $n = count($parts);
            for ($i = 0; $i < $n; $i++) {
                if ($i % 2 === 0) {
                    if ($parts[$i] !== '') {
                        $out[] = "'" . $parts[$i] . "'";
                    }
                } else {
                    $out[] = '$' . $parts[$i];
                }
            }
            if (!$out) {
                return "''";
            }
            return implode(' . ', $out);
        },
        $c
    );

    // 2) Double-quoted strings: {{KEY}} → {$KEY}
    $c = preg_replace_callback(
        '/"((?:\\\\.|[^"\\\\])*)"/',
        static function (array $m): string {
            $inner = $m[1];
            if (strpos($inner, '{{') === false) {
                return $m[0];
            }
            $inner = preg_replace('/\{\{([A-Z0-9_]+)\}\}/', '{$1}', $inner);
            // Fix {$1} style - actually need {$VAR}
            $inner = preg_replace('/\{\{([A-Z0-9_]+)\}\}/', '${$1}', $m[1]);
            // redo properly
            $inner = preg_replace('/\{\{([A-Z0-9_]+)\}\}/', '{$' . '$1' . '}', $m[1]);
            return null; // placeholder — see below
        },
        $c
    );

    // Fix step 2 properly (previous callback was broken; re-do cleanly)
    // Re-read: if step 2 messed up, skip - templates mostly use single quotes.
    // Actually the broken callback returns null which becomes empty string - BAD.
    // Re-implement without broken double-quote path: only process remaining {{}} in HTML.

    // 2b) Remaining {{KEY}} (HTML body etc.)
    $c = preg_replace_callback(
        '/\{\{([A-Z0-9_]+)\}\}/',
        static function (array $m) use ($htmlRaw): string {
            $k = $m[1];
            if (in_array($k, $htmlRaw, true)) {
                return '<?= $' . $k . ' ?>';
            }
            return '<?= htmlspecialchars($' . $k . ", ENT_QUOTES, 'UTF-8') ?>";
        },
        $c
    );

    return $c;
}

// Clean converter without broken double-quote step
function convertPlaceholdersV2(string $c, array $htmlRaw): string {
    // 1) Single-quoted strings → concat
    $c = preg_replace_callback(
        "/'((?:\\\\'|[^'])*)'/",
        static function (array $m): string {
            $inner = $m[1];
            if (strpos($inner, '{{') === false) {
                return $m[0];
            }
            $parts = preg_split('/\{\{([A-Z0-9_]+)\}\}/', $inner, -1, PREG_SPLIT_DELIM_CAPTURE);
            $out = [];
            $n = count($parts);
            for ($i = 0; $i < $n; $i++) {
                if ($i % 2 === 0) {
                    if ($parts[$i] !== '') {
                        $out[] = "'" . $parts[$i] . "'";
                    }
                } else {
                    $out[] = '$' . $parts[$i];
                }
            }
            if (!$out) {
                return "''";
            }
            return implode(' . ', $out);
        },
        $c
    );

    // 2) Remaining {{KEY}}
    $c = preg_replace_callback(
        '/\{\{([A-Z0-9_]+)\}\}/',
        static function (array $m) use ($htmlRaw): string {
            $k = $m[1];
            if (in_array($k, $htmlRaw, true)) {
                return '<?= $' . $k . ' ?>';
            }
            return '<?= htmlspecialchars($' . $k . ", ENT_QUOTES, 'UTF-8') ?>";
        },
        $c
    );

    return $c;
}

foreach ($files as $path) {
    if (!is_file($path)) {
        echo "MISSING $path\n";
        continue;
    }
    $orig = file_get_contents($path);
    $next = convertPlaceholdersV2($orig, $htmlRaw);
    $left = preg_match_all('/\{\{[A-Z0-9_]+\}\}/', $next);
    echo basename($path) . ': braces_left=' . (int)$left . ' changed=' . ($orig !== $next ? 'yes' : 'no') . "\n";
    if (!$dry && $orig !== $next) {
        // Banner update
        $next = preg_replace(
            '/\*\s*Placeholders:.*/',
            '* Pure PHP vars via executeTemplateVars() (no {{}} / eval).',
            $next,
            1
        );
        file_put_contents($path, $next);
        echo "  wrote $path\n";
    }
}

echo "done\n";
