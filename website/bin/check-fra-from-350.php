<?php
/**
 * FRA "From £350" wording on the two FRA hubs (Jack 19 Oct; Marketing copy verbatim).
 * These two pages carry Marketing's exact title/meta. The usual 60/155 length limits are
 * deliberately NOT applied here (Marketing override, these two pages only).
 * og:title/og:description and twitter:* must match the title/meta exactly.
 * £350 may appear only in the "from £350 ... typical 6-bed HMO" wording; no other £ figure.
 * /pages/jobs/fra is checked by check-fra-job-lane.php.
 * Usage: php website/bin/check-fra-from-350.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$pages = [
    '/pages/services/fire-risk-assessments' => [
        'title' => 'Fire Risk Assessment (FRA) Stockport & NW | From £350 typical 6-bed HMO | Other properties POA',
        'meta' => 'Fire risk assessments from £350 for a typical 6-bed HMO in the North West. Other properties and multi-site jobs are POA. Same/next-day availability. Call 07517 806082.',
    ],
    '/pages/fire-risk-assessment' => [
        'title' => 'Fire Risk Assessment UK Mainland | From £350 typical 6-bed HMO NW | Other properties POA',
        'meta' => 'FRA from £350 for a typical 6-bed HMO in the North West. Other properties, nationwide and multi-site jobs are POA. Call 07517 806082 or email icomplypropertyservices@gmail.com.',
    ],
];

$runner = sys_get_temp_dir() . '/fra-from-350-render.php';
file_put_contents($runner, "<?php\n\$site = " . var_export($root, true) . ";\n" . <<<'PHP'
$path = $argv[1] ?? '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $path;
$_SERVER['QUERY_STRING'] = '';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['SCRIPT_NAME'] = '/router.php';
require $site . '/router.php';
PHP);

$fail = 0;
$bad = function (string $msg) use (&$fail): void {
    fwrite(STDERR, "FAIL {$msg}\n");
    $fail++;
};
$attr = function (string $html, string $re): ?string {
    return preg_match($re, $html, $m) ? html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8') : null;
};

foreach ($pages as $path => $opt) {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($runner) . ' ' . escapeshellarg($path);
    $html = (string)shell_exec($cmd . ' 2>/dev/null');
    if (!str_contains($html, '<title>')) {
        $bad("{$path} did not render");
        continue;
    }
    $title = $attr($html, '~<title>([^<]*)</title>~');
    $desc = $attr($html, '~<meta name="description" content="([^"]*)"~');
    $ogT = $attr($html, '~<meta property="og:title" content="([^"]*)"~');
    $ogD = $attr($html, '~<meta property="og:description" content="([^"]*)"~');
    $twT = $attr($html, '~<meta name="twitter:title" content="([^"]*)"~');
    $twD = $attr($html, '~<meta name="twitter:description" content="([^"]*)"~');

    if ($title !== $opt['title']) $bad("{$path} title is not Marketing's copy: " . var_export($title, true));
    if ($desc !== $opt['meta']) $bad("{$path} meta is not Marketing's copy: " . var_export($desc, true));
    if ($ogT !== $opt['title']) $bad("{$path} og:title out of sync: " . var_export($ogT, true));
    if ($twT !== $opt['title']) $bad("{$path} twitter:title out of sync: " . var_export($twT, true));
    if ($ogD !== $opt['meta']) $bad("{$path} og:description out of sync");
    if ($twD !== $opt['meta']) $bad("{$path} twitter:description out of sync");
    foreach (['title' => $title, 'meta' => $desc] as $k => $v) {
        if (preg_match('/£\s*\d/', str_replace('£350', '', (string)$v))) $bad("{$path} {$k} has a £ figure other than £350");
        if (!preg_match('/from £350 (for a )?typical 6-bed HMO/i', (string)$v)) $bad("{$path} {$k} lacks the 'from £350 typical 6-bed HMO' wording");
        if (!str_contains((string)$v, 'POA')) $bad("{$path} {$k} lacks POA");
    }
    if (preg_match('~<meta name="robots" content="[^"]*noindex~i', $html)) $bad("{$path} is noindex");
    fwrite(STDOUT, "{$path}\n  title: {$title}\n  meta:  {$desc}\n");
}
@unlink($runner);

if ($fail > 0) {
    fwrite(STDERR, "FRA From £350 check FAILED ({$fail})\n");
    exit(1);
}
fwrite(STDOUT, "FRA From £350 check OK\n");
exit(0);
