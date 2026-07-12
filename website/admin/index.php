<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['user'] === ADMIN_USER && $_POST['pass'] === ADMIN_PASS) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Invalid login';
    }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: index.php'); exit; }

if (empty($_SESSION['admin'])): ?>
<!DOCTYPE html><html><head><title>Admin Login • Icomply</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css"></head>
<body class="bg-zinc-900 text-white flex items-center justify-center min-h-screen">
<div class="w-full max-w-sm">
    <h1 class="text-center text-3xl mb-8 tracking-tight">Icomply Admin</h1>
    <?php if (!empty($error)): ?><div class="bg-red-600 text-white p-3 rounded mb-4 text-sm"><?= $error ?></div><?php endif; ?>
    <form method="POST" class="bg-zinc-800 p-8 rounded-3xl space-y-4">
        <input type="text" name="user" placeholder="Username" value="jackscott" class="w-full bg-zinc-900 px-5 py-3 rounded-xl text-sm" required>
        <input type="password" name="pass" placeholder="Password" class="w-full bg-zinc-900 px-5 py-3 rounded-xl text-sm" required>
        <button class="w-full bg-white text-zinc-900 py-3 rounded-2xl font-medium">Login</button>
    </form>
    <p class="text-center text-xs text-white/40 mt-6">Hardcoded credentials for demo</p>
</div>
</body></html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html><head><title>Admin • Icomply Property Services</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css"></head>
<body class="bg-zinc-50">
<div class="max-w-6xl mx-auto p-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="font-semibold text-3xl">Admin Dashboard</div>
            <div class="text-sm text-zinc-500">Manage services, generate SEO landing pages</div>
        </div>
        <a href="?logout=1" class="text-sm px-4 py-2 bg-zinc-200 rounded-xl">Logout</a>
    </div>
    
    <?php if (isset($_GET['added'])): ?>
        <div class="mb-6 p-4 bg-emerald-100 text-emerald-800 rounded-2xl">Service added successfully. Generate its page below.</div>
    <?php endif; ?>
    
    <div class="grid md:grid-cols-2 gap-8">
        <!-- Services -->
        <div class="bg-white rounded-3xl p-8 border">
            <h2 class="font-semibold mb-5">Services (<?= count($allServices ?? $services) ?>)</h2>
            <div class="space-y-2 text-sm max-h-[420px] overflow-auto pr-2">
                <?php foreach (($allServices ?? $services) as $slug => $name): ?>
                    <div class="flex justify-between border-b pb-2 items-center">
                        <div><?= $name ?> <span class="text-xs text-zinc-400">(<?= $slug ?>)</span></div>
                        <span class="text-xs text-zinc-400">Generate via bin/generate-site.php</span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <form method="POST" action="generate.php" class="mt-8 pt-6 border-t">
                <input type="hidden" name="action" value="add_service">
                <div class="text-xs font-medium mb-2">Add New Service</div>
                <div class="flex gap-3">
                    <input name="new_slug" placeholder="new-service-slug" class="flex-1 border px-4 py-2 rounded-xl text-sm" required>
                    <input name="new_name" placeholder="Display Name" class="flex-1 border px-4 py-2 rounded-xl text-sm" required>
                    <button class="px-6 bg-[#0a2540] text-white rounded-xl text-sm">Add</button>
                </div>
            </form>
        </div>
        
        <!-- Areas -->
        <div class="bg-white rounded-3xl p-8 border">
            <h2 class="font-semibold mb-5">Generate Area Pages (<?= count($areas) ?>)</h2>
            <div class="max-h-[420px] overflow-auto text-sm pr-2 grid grid-cols-2 gap-x-6">
                <?php foreach ($areas as $area): ?>
                    <div class="py-px flex justify-between">
                        <span><?= $area ?></span>
                        <span class="text-xs text-zinc-400">Generated via bin/</span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-xs mt-4 text-zinc-500">Click any area to instantly create its dedicated landing page with all services linked.</div>
        </div>
    </div>
    
    <div class="mt-8 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800">
        <strong>Single Source of Truth:</strong> <code>templates/combo.php</code> + <code>config.php</code><br>
        <strong>Build Command:</strong> <code>php bin/generate-site.php --limit=150</code><br>
        Generated pages are overwritten on every run. Never edit them directly.
    </div>
</div>
</body></html>