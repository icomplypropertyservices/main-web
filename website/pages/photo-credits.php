<?php
/**
 * Photo credits: generated from data/hero-image-credits.json (third-party photos only).
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/photo-credits.php';

$seoFamily = 'static';
$pageTitle = 'Photo Credits | iComply Property Services';
$metaDesc = 'Authors, licences and source links for the stock and Wikimedia Commons photos used on iComply Property Services pages. Example photos, not our jobs.';
$ogImage = url('/assets/images/og-image.jpg');
$canonicalUrl = url('/pages/photo-credits');
$credits = icomplyPhotoCredits();
ksort($credits);
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
require SITE_ROOT . '/includes/header.php';
?>
<main class="bg-white">
<section class="max-w-5xl mx-auto px-6 py-12">
    <h1 class="text-3xl md:text-4xl font-bold text-[#061828]">Photo credits</h1>
    <p class="mt-4 text-zinc-800">Some pages use photos from Wikimedia Commons to show the kind of work described. They are example photos, not photos of iComply jobs. Each one is listed below with its author, licence and a link to the original file. Questions about a photo: call 07517 806082 or email icomplypropertyservices@gmail.com.</p>
    <div class="mt-8 overflow-x-auto">
    <table class="w-full text-sm border-collapse">
        <thead><tr class="text-left border-b-2 border-zinc-300"><th class="py-2 pr-4">Photo</th><th class="py-2 pr-4">Author</th><th class="py-2 pr-4">Licence</th><th class="py-2">Source</th></tr></thead>
        <tbody>
        <?php foreach ($credits as $file => $c): ?>
            <tr class="border-b border-zinc-200 align-top">
                <td class="py-2 pr-4"><img src="<?= $h($file) ?>" alt="<?= $h($c['title']) ?>" width="120" height="63" loading="lazy" class="rounded w-[120px] h-auto"></td>
                <td class="py-2 pr-4"><?= $h($c['artist']) ?></td>
                <td class="py-2 pr-4"><?= $h($c['license']) ?></td>
                <td class="py-2"><a class="underline text-[#061828]" href="<?= $h($c['source']) ?>" rel="nofollow noopener" target="_blank"><?= $h($c['title']) ?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</section>
</main>
<?php require SITE_ROOT . '/includes/footer.php'; ?>
