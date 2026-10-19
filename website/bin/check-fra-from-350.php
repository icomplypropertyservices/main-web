<?php
/**
 * FRA "From £350" wording on the two FRA hubs (Jack, 19 Oct).
 * £350 is allowed in <title>/meta/og/twitter on these pages ONLY in this wording:
 *   /pages/services/fire-risk-assessments  title says "From £350" (<=60), meta present (<=155)
 *   /pages/fire-risk-assessment            title says "From £350" (<=60), meta carries
 *       "From £350 for a typical 6-bed HMO NW, other properties POA" (<=155)
 * No other £ figure is allowed in those head tags. /pages/jobs/fra is checked by check-fra-job-lane.php.
 * Usage: php website/bin/check-fra-from-350.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$phrase = 'From £350 for a typical 6-bed HMO NW, other properties POA';
$pages = [
    '/pages/services/fire-risk-assessments' => ['metaPhrase' => false],
    '/pages/services/fire-risk-assessment' => ['metaPhrase' => false, 'optional' => true],
    '/pages/fire-risk-assessment' => ['metaPhrase' => true],
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
        if (!empty($opt['optional'])) {
            fwrite(STDOUT, "skip {$path} (served by redirect)\n");
            continue;
        }
        $bad("{$path} did not render");
        continue;
    }
    $title = $attr($html, '~<title>([^<]*)</title>~');
    $desc = $attr($html, '~<meta name="description" content="([^"]*)"~');
    $ogT = $attr($html, '~<meta property="og:title" content="([^"]*)"~');
    $ogD = $attr($html, '~<meta property="og:description" content="([^"]*)"~');
    $twT = $attr($html, '~<meta name="twitter:title" content="([^"]*)"~');
    $twD = $attr($html, '~<meta name="twitter:description" content="([^"]*)"~');

    if ($title === null || !str_contains($title, 'From £350')) $bad("{$path} title lacks 'From £350': " . var_export($title, true));
    if ($title !== null && mb_strlen($title) > 60) $bad("{$path} title " . mb_strlen($title) . " chars > 60");
    if ($desc === null || $desc === '') $bad("{$path} has no meta description");
    if ($desc !== null && mb_strlen($desc) > 155) $bad("{$path} meta " . mb_strlen($desc) . " chars > 155");
    if (!empty($opt['metaPhrase']) && ($desc === null || !str_contains($desc, $phrase))) $bad("{$path} meta lacks '{$phrase}'");
    if (!empty($opt['metaPhrase']) && ($title === null || !preg_match('/6-Bed HMO NW/i', $title))) $bad("{$path} title lacks 6-bed HMO NW");
    if ($ogT === null || $ogT === '' || !str_contains($ogT, 'From £350')) $bad("{$path} og:title out of sync: " . var_export($ogT, true));
    if ($twT !== $ogT) $bad("{$path} twitter:title != og:title");
    if ($ogD !== $desc) $bad("{$path} og:description != meta description");
    if ($twD !== $desc) $bad("{$path} twitter:description != meta description");
    foreach (['title' => $title, 'meta' => $desc, 'og:title' => $ogT] as $k => $v) {
        $rest = str_replace('£350', '', (string)$v);
        if (preg_match('/£\s*\d/', $rest)) $bad("{$path} {$k} has a £ figure other than £350: {$v}");
        if (preg_match('/£350/', (string)$v) && !str_contains((string)$v, 'From £350') && !str_contains((string)$v, 'from £350')) {
            $bad("{$path} {$k} shows £350 outside the 'From £350' wording: {$v}");
        }
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
