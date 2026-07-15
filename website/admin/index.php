<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/build-status.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user'], $_POST['pass'])) {
    $passOk = ADMIN_PASS !== ''
        && hash_equals(ADMIN_USER, (string)$_POST['user'])
        && hash_equals(ADMIN_PASS, (string)$_POST['pass']);
    if ($passOk) {
        $_SESSION['admin'] = true;
        session_regenerate_id(true);
    } else {
        $error = ADMIN_PASS === ''
            ? 'Admin password not set — add ADMIN_PASS to config.local.php'
            : 'Invalid login';
        usleep(300000);
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['admin'])): ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Admin Login • Icomply</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css"></head>
<body class="bg-zinc-900 text-white flex items-center justify-center min-h-screen">
<div class="w-full max-w-sm">
    <h1 class="text-center text-3xl mb-8 tracking-tight">Icomply Admin</h1>
    <?php if (!empty($error)): ?><div class="bg-red-600 text-white p-3 rounded mb-4 text-sm"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="POST" class="bg-zinc-800 p-8 rounded-3xl space-y-4" autocomplete="off">
        <input type="text" name="user" placeholder="Username" class="w-full bg-zinc-900 px-5 py-3 rounded-xl text-sm" required>
        <input type="password" name="pass" placeholder="Password" class="w-full bg-zinc-900 px-5 py-3 rounded-xl text-sm" required>
        <button class="w-full bg-white text-zinc-900 py-3 rounded-2xl font-medium">Login</button>
    </form>
    <p class="text-center text-xs text-white/40 mt-6">Credentials from config / config.local.php</p>
</div>
</body></html>
<?php exit; endif;

$allServices = getServices();
$areas = getAreas();
$keywords = getMajorKeywords();
$status = getBuildStatus();
$flash = $_GET['msg'] ?? '';
$needs = !empty($status['needs_regen']);
$mfrCatalog = getManufacturerCatalog();
$mfrCount = count($mfrCatalog);
$featuredMfr = array_filter($mfrCatalog, static fn($c) => !empty($c['featured']));
if (!$featuredMfr) {
    $featuredMfr = array_slice($mfrCatalog, 0, 12, true);
}
$mfrIndexUrl = url('/pages/manufacturers/index.php');

// Recent leads (last 20 from JSONL) — auth-protected page only
$recentLeads = [];
$leadsFile = (defined('SITE_ROOT') ? SITE_ROOT : dirname(__DIR__)) . '/data/leads.jsonl';
if (is_file($leadsFile) && is_readable($leadsFile)) {
    $lines = @file($leadsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines) && $lines) {
        foreach (array_reverse(array_slice($lines, -20)) as $line) {
            $row = json_decode((string)$line, true);
            if (is_array($row)) {
                $recentLeads[] = $row;
            }
        }
    }
}
$truncateLead = static function (string $text, int $max = 80): string {
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $max) {
            return mb_substr($text, 0, $max, 'UTF-8') . '…';
        }
        return $text;
    }
    if (strlen($text) > $max) {
        return substr($text, 0, $max) . '…';
    }
    return $text;
};
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Admin • Icomply Property Services</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">
<style>
  .badge-ok { background:#d1fae5; color:#065f46; }
  .badge-bad { background:#fee2e2; color:#991b1b; }
  .badge-warn { background:#fef3c7; color:#92400e; }
</style>
</head>
<body class="bg-zinc-50">
<div class="max-w-6xl mx-auto p-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="font-semibold text-3xl">Admin Dashboard</div>
            <div class="text-sm text-zinc-500">Build status · pages · regenerate SEO stubs</div>
        </div>
        <div class="flex items-center gap-3">
            <a href="shopify.php" class="text-sm px-4 py-2 bg-[#ff6b00] text-white rounded-xl font-semibold">Shopify setup</a>
            <a href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>/" target="_blank" class="text-sm px-4 py-2 bg-white border rounded-xl">View site</a>
            <a href="?logout=1" class="text-sm px-4 py-2 bg-zinc-200 rounded-xl">Logout</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="mb-6 p-4 bg-emerald-100 text-emerald-800 rounded-2xl text-sm break-words"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- REGEN INDICATOR -->
    <?php if ($needs): ?>
    <div class="mb-6 p-6 bg-amber-50 border-2 border-amber-400 rounded-3xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-xs font-bold tracking-widest text-amber-700 uppercase">Regeneration needed</div>
                <div class="text-xl font-semibold text-amber-900 mt-1">Site stubs / sitemap are out of date or incomplete</div>
                <ul class="mt-3 text-sm text-amber-900 space-y-1">
                    <?php foreach ($status['reasons'] as $r): ?>
                        <li>• <?= htmlspecialchars($r, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="POST" action="generate.php" class="shrink-0">
                <input type="hidden" name="action" value="regenerate">
                <input type="hidden" name="combo" value="1">
                <input type="hidden" name="keywords" value="1">
                <input type="hidden" name="areas" value="1">
                <input type="hidden" name="services" value="1">
                <input type="hidden" name="sitemap" value="1">
                <input type="hidden" name="full" value="1">
                <button class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl font-semibold shadow">
                    Full regenerate now
                </button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="mb-6 p-5 bg-emerald-50 border border-emerald-300 rounded-3xl flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <div class="text-xs font-bold tracking-widest text-emerald-700 uppercase">Build healthy</div>
            <div class="text-lg font-semibold text-emerald-900">No regeneration required</div>
            <div class="text-sm text-emerald-800 mt-1">
                Last build: <?= htmlspecialchars($status['built_at'] ?? 'unknown', ENT_QUOTES, 'UTF-8') ?>
                · <?= (int)$status['actual']['total_php_pages'] ?> PHP pages under /pages
            </div>
        </div>
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium badge-ok">OK</span>
    </div>
    <?php endif; ?>

    <!-- RECENT LEADS -->
    <div class="bg-white rounded-3xl p-8 border mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="font-semibold text-xl">Recent leads</h2>
                <div class="text-sm text-zinc-500 mt-0.5">
                    Last <?= count($recentLeads) ?> of up to 20 · <code>data/leads.jsonl</code>
                </div>
            </div>
        </div>
        <?php if (!$recentLeads): ?>
            <p class="text-sm text-zinc-500">No leads yet<?= is_file($leadsFile) ? '' : ' (file not found)' ?>.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 border-b">
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Phone</th>
                        <th class="py-2 pr-4">Service</th>
                        <th class="py-2 pr-4">Time</th>
                        <th class="py-2">Message</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentLeads as $lead):
                    $msg = $truncateLead((string)($lead['message'] ?? ''), 80);
                    $timeRaw = (string)($lead['timestamp'] ?? '');
                ?>
                    <tr class="border-b last:border-0 align-top">
                        <td class="py-3 pr-4 font-medium"><?= htmlspecialchars((string)($lead['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="py-3 pr-4">
                            <?php $em = (string)($lead['email'] ?? ''); ?>
                            <?php if ($em !== ''): ?>
                                <a class="text-[#ff6b00] hover:underline" href="mailto:<?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 pr-4"><?= htmlspecialchars((string)($lead['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="py-3 pr-4"><?= htmlspecialchars((string)($lead['service'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="py-3 pr-4 text-zinc-500 whitespace-nowrap"><?= htmlspecialchars($timeRaw, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="py-3 text-zinc-600 max-w-xs"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- PAGE INVENTORY -->
    <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-4 mb-4">
        <?php
        $cards = [
            ['Services', $status['inventory']['services'], 'data', null],
            ['Areas', $status['inventory']['areas'], 'data', null],
            ['Keywords', $status['inventory']['keywords'], 'data', null],
            ['Page stubs', $status['actual']['total_php_pages'], 'pages', null],
            ['Manufacturers', $mfrCount, 'catalog', $mfrIndexUrl],
        ];
        foreach ($cards as [$label, $n, $sub, $href]): ?>
        <div class="bg-white border rounded-2xl p-5">
            <div class="text-xs text-zinc-500 uppercase tracking-wider"><?= $label ?></div>
            <div class="text-3xl font-semibold mt-1"><?= (int)$n ?></div>
            <div class="text-xs text-zinc-400 mt-1">
                <?php if ($href): ?>
                    <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="text-[#ff6b00] font-medium hover:underline">
                        <?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?> →
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FEATURED BRANDS (catalog) -->
    <div class="bg-white border rounded-2xl p-5 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
                <div class="text-xs text-zinc-500 uppercase tracking-wider">Featured brands</div>
                <div class="text-sm text-zinc-600 mt-0.5"><?= count($featuredMfr) ?> featured · <?= (int)$mfrCount ?> in catalog</div>
            </div>
            <a href="<?= htmlspecialchars($mfrIndexUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank"
               class="text-sm font-semibold text-[#ff6b00] hover:underline shrink-0">Manufacturers index →</a>
        </div>
        <div class="flex flex-wrap gap-2">
            <?php foreach (array_slice($featuredMfr, 0, 24, true) as $mSlug => $mEntry): ?>
                <a href="<?= htmlspecialchars(url('/pages/manufacturers/' . rawurlencode((string)$mSlug) . '.php'), ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank"
                   class="px-3 py-1.5 bg-zinc-50 border rounded-full text-xs font-medium text-zinc-800 hover:border-[#ff6b00] transition">
                    <?= htmlspecialchars($mEntry['name'] ?? (string)$mSlug, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
            <?php if (!$featuredMfr): ?>
                <span class="text-xs text-zinc-400">No manufacturers in catalog yet.</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- JOB STATUS -->
    <div class="bg-white rounded-3xl p-8 border mb-8">
        <h2 class="font-semibold text-xl mb-4">Generator jobs</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-zinc-500 border-b">
                        <th class="py-2 pr-4">Job</th>
                        <th class="py-2 pr-4">Script</th>
                        <th class="py-2 pr-4">Actual / Expected</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($status['jobs'] as $key => $job): ?>
                    <tr class="border-b last:border-0">
                        <td class="py-3 pr-4 font-medium"><?= htmlspecialchars($job['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="py-3 pr-4 text-zinc-500"><code><?= htmlspecialchars($job['script'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td class="py-3 pr-4"><?= (int)$job['actual'] ?> / <?= (int)$job['expected'] ?></td>
                        <td class="py-3">
                            <?php if (!empty($job['ok'])): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold badge-ok">Up to date</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold badge-bad">Needs regen</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-xs text-zinc-500">
            Combo by service:
            <?php foreach ($status['actual']['combo_by_service'] as $slug => $n): ?>
                <span class="inline-block mr-2 mb-1 px-2 py-0.5 bg-zinc-100 rounded"><?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>: <?= (int)$n ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SAMPLE PAGES -->
    <div class="bg-white rounded-3xl p-8 border mb-8">
        <h2 class="font-semibold text-xl mb-4">Pages (samples)</h2>
        <div class="grid md:grid-cols-2 gap-3 text-sm">
            <?php foreach ($status['samples'] as $s): ?>
                <div class="flex items-center justify-between border rounded-xl px-4 py-3">
                    <div>
                        <div class="font-medium"><?= htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="text-xs text-zinc-500"><?= htmlspecialchars($s['path'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($s['exists']): ?>
                            <span class="text-xs badge-ok px-2 py-1 rounded-full">exists</span>
                            <a class="text-xs text-[#ff6b00] font-medium" target="_blank"
                               href="<?= htmlspecialchars(url($s['path'] === '/' ? '/' : $s['path']), ENT_QUOTES, 'UTF-8') ?><?= $s['path'] === '/' ? '/' : '' ?>">Open</a>
                        <?php else: ?>
                            <span class="text-xs badge-bad px-2 py-1 rounded-full">missing</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Services -->
        <div class="bg-white rounded-3xl p-8 border">
            <h2 class="font-semibold mb-5">Services (<?= count($allServices) ?>)</h2>
            <div class="space-y-2 text-sm max-h-[360px] overflow-auto pr-2">
                <?php foreach ($allServices as $slug => $name):
                    $n = $status['actual']['combo_by_service'][$slug] ?? 0;
                    $ok = $n >= count($areas);
                ?>
                    <div class="flex justify-between border-b pb-2 items-center gap-2">
                        <div>
                            <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                            <span class="text-xs text-zinc-400">(<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>)</span>
                            <span class="text-xs <?= $ok ? 'text-emerald-600' : 'text-red-600' ?>"><?= $n ?> areas</span>
                        </div>
                        <a class="text-xs text-[#ff6b00] shrink-0" href="<?= htmlspecialchars(url('/pages/services/' . $slug . '.php'), ENT_QUOTES, 'UTF-8') ?>" target="_blank">View</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" action="generate.php" class="mt-8 pt-6 border-t">
                <input type="hidden" name="action" value="add_service">
                <div class="text-xs font-medium mb-2">Add custom service <span class="text-amber-600">(marks regen needed)</span></div>
                <div class="flex gap-3">
                    <input name="new_slug" placeholder="new-service-slug" class="flex-1 border px-4 py-2 rounded-xl text-sm" required pattern="[a-z0-9\-]+">
                    <input name="new_name" placeholder="Display Name" class="flex-1 border px-4 py-2 rounded-xl text-sm" required>
                    <button class="px-6 bg-[#0a2540] text-white rounded-xl text-sm">Add</button>
                </div>
            </form>
        </div>

        <!-- Areas + regen form -->
        <div class="bg-white rounded-3xl p-8 border">
            <h2 class="font-semibold mb-5">Areas (<?= count($areas) ?>) · Keywords (<?= count($keywords) ?>)</h2>
            <div class="max-h-[200px] overflow-auto text-sm pr-2 grid grid-cols-2 gap-x-6 mb-4">
                <?php foreach (array_slice($areas, 0, 60) as $area): ?>
                    <div class="py-px"><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-zinc-500 mb-4">Data: <code>data/areas.json</code>, <code>data/keywords.json</code></p>

            <form method="POST" action="generate.php" class="pt-6 border-t space-y-3">
                <input type="hidden" name="action" value="regenerate">
                <div class="text-xs font-medium flex items-center gap-2">
                    Regenerate site pages
                    <?php if ($needs): ?>
                        <span class="badge-warn px-2 py-0.5 rounded-full text-[10px] font-bold">ACTION REQUIRED</span>
                    <?php endif; ?>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="combo" value="1" <?= empty($status['jobs']['combo']['ok']) ? 'checked' : 'checked' ?>>
                    Service × area stubs
                    <?php if (empty($status['jobs']['combo']['ok'])): ?><span class="text-red-600 text-xs">needs regen</span><?php endif; ?>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="keywords" value="1" checked>
                    Keyword stubs
                    <?php if (empty($status['jobs']['keywords']['ok'])): ?><span class="text-red-600 text-xs">needs regen</span><?php endif; ?>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="areas" value="1" checked>
                    Area hubs
                    <?php if (empty($status['jobs']['areas']['ok'])): ?><span class="text-red-600 text-xs">needs regen</span><?php endif; ?>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="services" value="1" checked>
                    Service hubs
                    <?php if (empty($status['jobs']['services']['ok'])): ?><span class="text-red-600 text-xs">needs regen</span><?php endif; ?>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="sitemap" value="1" checked>
                    Sitemap
                    <?php if (empty($status['jobs']['sitemap']['ok'])): ?><span class="text-red-600 text-xs">needs regen</span><?php endif; ?>
                </label>
                <button class="w-full px-6 py-3 bg-[#ff6b00] text-white rounded-xl text-sm font-semibold">
                    Run selected generators
                </button>
            </form>
        </div>
    </div>

    <div class="mt-8 p-4 bg-zinc-100 border rounded-2xl text-xs text-zinc-600 space-y-1">
        <div><strong>CLI full build:</strong> <code>php bin/full-build.php</code></div>
        <div><strong>Debug suite:</strong> <code>php bin/debug-check.php</code></div>
        <div><strong>HTTP smoke:</strong> <code>php bin/http-check.php</code></div>
        <div><strong>Manifest:</strong> <code>data/build-manifest.json</code></div>
        <div>Runtime render: stubs → <code>includes/render.php</code> → <code>templates/services/*</code></div>
    </div>
</div>
</body></html>
