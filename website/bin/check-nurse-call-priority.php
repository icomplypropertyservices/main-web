<?php
/**
 * Hard gate for the nurse-call priority pages.
 * Unique copy, no Tunstall, no template slop, Manchester + Burnley, care-homes links.
 *
 * Usage: php website/bin/check-nurse-call-priority.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once SITE_ROOT . '/includes/wave1.php';
require_once SITE_ROOT . '/includes/nurse-call-priority.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$fail = 0;
$note = static function (string $msg) use (&$fail): void {
    echo $msg . PHP_EOL;
    $fail++;
};

$slop = '/\b(looking for|searching for|comprehensive|seamless|leverage|delve|tailored|peace of mind|state-of-the-art|cutting-edge|world-class|our team of experts|it\'s important|in today\'s|furthermore|moreover|elevate|unlock|nestled|holistic|synergy|utilize|goes beyond|not just|whether you|we understand|at the heart|second to none|tapestry|major player)\b/i';

$hubs = nurseCallPriorityHubs();
$words = nurseCallPriorityKeywordOverlay();

foreach (['nurse-call-systems', 'nurse-call-manchester', 'nurse-call-burnley'] as $slug) {
    if (!isset($hubs[$slug])) {
        $note("FAIL missing hub {$slug}");
        continue;
    }
    if (!is_file(SITE_ROOT . '/pages/' . $slug . '.php')) {
        $note("FAIL missing page file {$slug}");
    }
}

$blobs = [];
foreach ($hubs as $slug => $hub) {
    $blob = json_encode($hub, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $blobs[$slug] = $blob;
    if (stripos($blob, 'tunstall') !== false) {
        $note("FAIL Tunstall mentioned in hub {$slug}");
    }
    if (preg_match($slop, $blob)) {
        $note("FAIL slop phrase in hub {$slug}");
    }
    $meta = (string)($hub['metaDesc'] ?? '');
    $len = mb_strlen($meta);
    if ($len < 70 || $len > 165) {
        $note("FAIL hub {$slug} meta length {$len}");
    }
    if (strpos($blob, '/pages/care-homes') === false) {
        $note("FAIL hub {$slug} missing care-homes link");
    }
    if (strpos($blob, '07517806082') === false) {
        $note("FAIL hub {$slug} missing phone");
    }
    if (!is_file(SITE_ROOT . '/pages/' . $slug . '.php')) {
        $note("FAIL stub missing {$slug}");
    }
}

if (strpos($blobs['nurse-call-systems'] ?? '', '/pages/nurse-call-manchester') === false
    || strpos($blobs['nurse-call-systems'] ?? '', '/pages/nurse-call-burnley') === false) {
    $note('FAIL main hub missing Manchester or Burnley link');
}

foreach ($words as $slug => $meta) {
    $blob = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $blobs['kw:' . $slug] = $blob;
    if (stripos($blob, 'tunstall') !== false) {
        $note("FAIL Tunstall in keyword {$slug}");
    }
    if (preg_match($slop, $blob)) {
        $note("FAIL slop phrase in keyword {$slug}");
    }
    $desc = (string)($meta['meta_desc'] ?? '');
    $len = mb_strlen($desc);
    if ($len < 70 || $len > 165) {
        $note("FAIL keyword {$slug} meta length {$len}: {$desc}");
    }
    if (strpos($blob, '07517806082') === false) {
        $note("FAIL keyword {$slug} missing phone");
    }
    $loaded = getMajorKeywords()[$slug] ?? null;
    if (!$loaded || stripos((string)($loaded['intro'] ?? ''), 'tunstall') !== false) {
        $note("FAIL keyword overlay not live for {$slug}");
    }
}

if (isset(getMajorKeywords()['tunstall-nurse-call'])) {
    $note('FAIL tunstall-nurse-call keyword still published');
}
if (getManufacturerBySlug('tunstall')) {
    $note('FAIL Tunstall manufacturer still in catalog');
}
$nurseNames = getManufacturers('nurse-call');
foreach ($nurseNames as $name) {
    if (stripos((string)$name, 'tunstall') !== false) {
        $note('FAIL Tunstall still listed under nurse-call manufacturers');
    }
}

$sentences = [];
foreach ($blobs as $id => $blob) {
    $plain = html_entity_decode(strip_tags($blob));
    foreach (preg_split('/(?<=[.!?])\s+/', $plain) as $sentence) {
        $sentence = trim($sentence);
        if (mb_strlen($sentence) < 70) {
            continue;
        }
        $key = mb_strtolower($sentence);
        if (isset($sentences[$key]) && $sentences[$key] !== $id) {
            $note('FAIL duplicate sentence in ' . $sentences[$key] . ' and ' . $id . ': ' . mb_substr($sentence, 0, 90));
        }
        $sentences[$key] = $id;
    }
}

function ncRender(string $path): string
{
    putenv('ICOMPLY_STATIC_EXPORT=1');
    $_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
    $_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['REQUEST_URI'] = $path;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_GET = [];
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    ob_start();
    routerHandleRequest();
    return (string)ob_get_clean();
}

require_once SITE_ROOT . '/includes/router.php';

$pages = [
    '/pages/nurse-call-systems',
    '/pages/nurse-call-manchester',
    '/pages/nurse-call-burnley',
    '/pages/services/nurse-call',
    '/pages/keywords/warden-call',
    '/pages/keywords/care-home-nurse-call',
    '/pages/care-homes',
];
foreach ($pages as $path) {
    $html = ncRender($path);
    if (!str_contains($html, '<h1')) {
        $note("FAIL render {$path} missing h1");
        continue;
    }
    if (stripos($html, 'tunstall') !== false) {
        $note("FAIL render {$path} contains Tunstall");
    }
    if (!str_contains($html, '07517806082')) {
        $note("FAIL render {$path} missing phone");
    }
    if (!str_contains($html, 'care-homes') && $path !== '/pages/care-homes') {
        $note("FAIL render {$path} missing care-homes cross-link");
    }
    echo "OK   render {$path} (" . strlen($html) . " bytes)\n";
}

$root = SITE_ROOT;
$hits = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['php', 'json', 'md', 'html'], true)) {
        continue;
    }
    $rel = substr($file->getPathname(), strlen($root));
    if (str_contains($rel, '/bin/check-nurse-call-priority.php')
        || $rel === '/router.php'
        || $rel === '/includes/router.php'
        || $rel === '/bin/static-export.php'
        || $rel === '/config.php') {
        continue;
    }
    $text = (string)file_get_contents($file->getPathname());
    if (stripos($text, 'tunstall') !== false) {
        $hits[] = $rel;
    }
}
if ($hits) {
    $note('FAIL Tunstall still present in: ' . implode(', ', $hits));
} else {
    echo "OK   no Tunstall string under website/\n";
}

echo $fail === 0 ? "PASS nurse-call priority gate\n" : "FAIL ({$fail})\n";
exit($fail === 0 ? 0 : 1);
