<?php
/**
 * Admin actions: add service, run page generators, update build manifest.
 */
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/build-status.php';

if (empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$php = 'C:\\xampp\\php\\php.exe';
if (!is_file($php)) {
    $php = PHP_BINARY ?: 'php';
}
$bin = SITE_ROOT . DIRECTORY_SEPARATOR . 'bin';

if ($action === 'add_service') {
    $slug = keywordSlug($_POST['new_slug'] ?? '');
    $name = trim((string)($_POST['new_name'] ?? ''));
    if ($slug && $name) {
        $custom = loadServices();
        $custom[$slug] = $name;
        saveServices($custom);
        markBuildDirty('custom service added: ' . $slug);
        header('Location: index.php?msg=' . rawurlencode('Service added: ' . $name . ' — regeneration needed'));
        exit;
    }
    header('Location: index.php?msg=' . rawurlencode('Invalid service data'));
    exit;
}

if ($action === 'regenerate') {
    // Full build path
    if (!empty($_POST['full'])) {
        $path = $bin . DIRECTORY_SEPARATOR . 'full-build.php';
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($path) . ' 2>&1';
        $out = [];
        $code = 0;
        exec($cmd, $out, $code);
        $summary = 'full-build exit ' . $code . ': ' . trim(implode(' | ', array_slice($out, -6)));
        header('Location: index.php?msg=' . rawurlencode($summary));
        exit;
    }

    $logs = [];
    $results = [];
    $run = function (string $key, string $script) use ($php, $bin, &$logs, &$results) {
        $path = $bin . DIRECTORY_SEPARATOR . $script;
        if (!is_file($path)) {
            $logs[] = "missing {$script}";
            $results[$key] = ['ok' => false, 'exit' => 127, 'out' => 'missing'];
            return;
        }
        $cmd = escapeshellarg($php) . ' ' . escapeshellarg($path) . ' 2>&1';
        $out = [];
        $code = 0;
        exec($cmd, $out, $code);
        $tail = trim(implode(' | ', array_slice($out, -3)));
        $logs[] = $script . ' (exit ' . $code . '): ' . $tail;
        $results[$key] = ['ok' => $code === 0, 'exit' => $code, 'out' => $tail];
    };

    if (!empty($_POST['combo'])) {
        $run('combo', 'generate-site.php');
    }
    if (!empty($_POST['keywords'])) {
        $run('keywords', 'generate-keyword-pages.php');
    }
    if (!empty($_POST['areas'])) {
        $run('areas', 'generate-area-hubs.php');
    }
    if (!empty($_POST['services'])) {
        $run('services', 'generate-service-hubs.php');
    }
    if (!empty($_POST['sitemap'])) {
        $run('sitemap', 'generate-sitemap.php');
    }

    recordBuildComplete($results, implode('; ', $logs));
    header('Location: index.php?msg=' . rawurlencode(implode(' · ', $logs)));
    exit;
}

header('Location: index.php');
