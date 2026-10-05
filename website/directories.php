<?php
/**
 * Listings and directories — citation / NAP page.
 * Won URLs are appended in website/data/links-won.csv (platform,url,status,note).
 * Pending rows stay on the page without an outbound link until a URL exists.
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Listings and directories | iComply Property Services';
$metaDesc = 'Where to find iComply Property Services online. 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Call 07517806082.';
$canonicalUrl = url('/directories');
$metaRobots = 'index, follow';
$ogImage = url('/assets/images/services/building-maintenance.jpg');

$nap = '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE';
$phone = defined('PHONE') ? PHONE : '07517806082';
$email = defined('EMAIL') ? EMAIL : 'info@icomplypropertyservices.co.uk';

/** @return list<array{platform:string,url:string,status:string,note:string}> */
function icomplyDirectoryRows(string $file): array
{
    if (!is_file($file)) {
        return [];
    }
    $decoded = json_decode((string)file_get_contents($file), true);
    if (!is_array($decoded)) {
        return [];
    }
    $rows = [];
    foreach (['official', 'directories'] as $key) {
        foreach ($decoded[$key] ?? [] as $row) {
            if (!is_array($row) || empty($row['platform'])) {
                continue;
            }
            $rows[] = [
                'platform' => (string)$row['platform'],
                'url' => trim((string)($row['url'] ?? '')),
                'status' => (string)($row['status'] ?? 'pending'),
                'note' => (string)($row['note'] ?? ''),
                'group' => $key,
            ];
        }
    }
    return $rows;
}

/** @return list<array{platform:string,url:string,status:string,note:string,group:string}> */
function icomplyWonDirectoryRows(string $file): array
{
    if (!is_file($file)) {
        return [];
    }
    $handle = fopen($file, 'rb');
    if ($handle === false) {
        return [];
    }
    $header = fgetcsv($handle) ?: [];
    $index = [];
    foreach ($header as $i => $name) {
        $index[strtolower(trim((string)$name))] = $i;
    }
    $rows = [];
    while (($csv = fgetcsv($handle)) !== false) {
        $platform = trim((string)($csv[$index['platform'] ?? 0] ?? ''));
        $url = trim((string)($csv[$index['url'] ?? 1] ?? ''));
        if ($platform === '' || $url === '') {
            continue;
        }
        $rows[] = [
            'platform' => $platform,
            'url' => $url,
            'status' => trim((string)($csv[$index['status'] ?? 2] ?? 'live')) ?: 'live',
            'note' => trim((string)($csv[$index['note'] ?? 3] ?? '')),
            'group' => 'directories',
        ];
    }
    fclose($handle);
    return $rows;
}

$seed = icomplyDirectoryRows(SITE_ROOT . '/data/directories.json');
$won = icomplyWonDirectoryRows(SITE_ROOT . '/data/links-won.csv');
$byPlatform = [];
foreach ($seed as $row) {
    $byPlatform[strtolower($row['platform'])] = $row;
}
foreach ($won as $row) {
    $key = strtolower($row['platform']);
    if (isset($byPlatform[$key]) && $byPlatform[$key]['url'] === '') {
        $byPlatform[$key]['url'] = $row['url'];
        $byPlatform[$key]['status'] = $row['status'];
        if ($row['note'] !== '') {
            $byPlatform[$key]['note'] = $row['note'];
        }
        continue;
    }
    if (!isset($byPlatform[$key])) {
        $byPlatform[$key] = $row;
    }
}
$official = [];
$directories = [];
foreach ($byPlatform as $row) {
    if (($row['group'] ?? '') === 'official') {
        $official[] = $row;
    } else {
        $directories[] = $row;
    }
}

$h = static function (string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
};
$renderRows = static function (array $rows) use ($h): void {
    echo '<ul class="mt-4 divide-y divide-zinc-200 border border-zinc-200 rounded-2xl bg-white">';
    foreach ($rows as $row) {
        $url = (string)$row['url'];
        $live = $url !== '' && preg_match('#^https?://#', $url);
        echo '<li class="px-5 py-4">';
        echo '<div class="font-semibold text-[#061828]">' . $h((string)$row['platform']) . '</div>';
        if ($live) {
            echo '<a class="text-[#ff6b00] font-medium break-all" href="' . $h($url) . '">' . $h($url) . '</a>';
        } else {
            echo '<p class="text-sm text-zinc-500">Not listed here yet.</p>';
        }
        if (($row['note'] ?? '') !== '') {
            echo '<p class="mt-1 text-sm text-zinc-600">' . $h((string)$row['note']) . '</p>';
        }
        echo '</li>';
    }
    echo '</ul>';
};

require SITE_ROOT . '/includes/header.php';
?>
<section class="bg-[#061828] text-white">
    <div class="max-w-3xl mx-auto px-6 py-14">
        <nav class="text-xs text-white/60 mb-4" aria-label="Breadcrumb">
            <a class="hover:text-white" href="<?= $h(rtrim(SITE_URL, '/') . '/') ?>">Home</a>
            <span> / </span>
            <span>Listings and directories</span>
        </nav>
        <h1 class="text-4xl font-semibold tracking-tight">Find us online</h1>
        <p class="mt-4 text-lg text-white/80">iComply Property Services is based in Offerton, Stockport, and arranges compliance and property work across Greater Manchester. This page lists the official profiles and directory entries we keep in the same name, address and phone number.</p>
        <p class="mt-3 text-white/70">Quotes are price on application. Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.</p>
    </div>
</section>
<main class="max-w-3xl mx-auto px-6 py-12 space-y-12">
    <section aria-labelledby="nap-heading">
        <h2 id="nap-heading" class="text-2xl font-semibold text-[#061828]">Name, address and phone</h2>
        <dl class="mt-4 bg-zinc-50 border border-zinc-200 rounded-2xl p-6 space-y-3 text-[#061828]">
            <div><dt class="text-xs uppercase tracking-wide text-zinc-500">Name</dt><dd class="font-semibold"><?= $h(defined('SITE_NAME') ? SITE_NAME : 'iComply Property Services') ?></dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-zinc-500">Address</dt><dd><?= $h($nap) ?></dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-zinc-500">Phone</dt><dd><a class="text-[#ff6b00] font-semibold" href="tel:<?= $h(preg_replace('/\s+/', '', $phone)) ?>"><?= $h($phone) ?></a></dd></div>
            <div><dt class="text-xs uppercase tracking-wide text-zinc-500">Email</dt><dd><a class="text-[#ff6b00] font-semibold" href="mailto:<?= $h($email) ?>"><?= $h($email) ?></a></dd></div>
        </dl>
    </section>
    <section aria-labelledby="official-heading">
        <h2 id="official-heading" class="text-2xl font-semibold text-[#061828]">Official profiles</h2>
        <p class="mt-2 text-zinc-600">Profiles we publish ourselves. A row without a link is waiting for the public URL.</p>
        <?php $renderRows($official); ?>
    </section>
    <section aria-labelledby="directories-heading">
        <h2 id="directories-heading" class="text-2xl font-semibold text-[#061828]">Directories</h2>
        <p class="mt-2 text-zinc-600">Directory listings use the address above. New confirmed URLs are added from the citations log. Nothing is shown as a link until that URL exists.</p>
        <?php $renderRows($directories); ?>
    </section>
    <section class="related-links" aria-label="Related pages">
        <h2 class="text-2xl font-semibold text-[#061828]">On this website</h2>
        <ul class="mt-4 flex flex-wrap gap-3">
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/contact')) ?>">Contact / quote</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/pages/about')) ?>">About</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/pages/services')) ?>">Services</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/pages/areas')) ?>">Areas</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/pages/areas/stockport')) ?>">Stockport</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/pages/jobs')) ?>">Jobs</a></li>
            <li><a class="px-4 py-2 border rounded-full text-sm font-semibold hover:border-[#ff6b00]" href="<?= $h(url('/pages/manufacturers')) ?>">Manufacturers</a></li>
        </ul>
    </section>
</main>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
