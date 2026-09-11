<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}
$services = getServices();
$areas = getAreas();
$pageTitleSafe = htmlspecialchars($pageTitle ?? SITE_NAME, ENT_QUOTES, 'UTF-8');
$metaDescSafe = htmlspecialchars(
    $metaDesc ?? 'Expert property compliance services across Greater Manchester and North West UK. EICR, Fire Alarms, Gas Safety, Emergency Lighting & more. Get your free quote today.',
    ENT_QUOTES,
    'UTF-8'
);
$metaKeywordsSafe = htmlspecialchars(
    $metaKeywords ?? 'property compliance, EICR, fire alarms, emergency lighting, gas safety, Manchester electrician',
    ENT_QUOTES,
    'UTF-8'
);
$homeUrl = rtrim(SITE_URL, '/') . '/';
$phoneHref = 'tel:' . preg_replace('/\s+/', '', PHONE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a2540">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <?php if (defined('GOOGLE_SITE_VERIFICATION') && GOOGLE_SITE_VERIFICATION !== ''): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars(GOOGLE_SITE_VERIFICATION, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php
    require_once __DIR__ . '/seo-monitor.php';
    $robotsContent = $metaRobots ?? 'index, follow, max-image-preview:large';
    if (function_exists('seoShouldNoindex') && seoShouldNoindex()) {
        $robotsContent = 'noindex,nofollow';
    }
    ?>
    <meta name="robots" content="<?= htmlspecialchars($robotsContent, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="sitemap" type="application/xml" title="Sitemap" href="<?= htmlspecialchars(url('/sitemap.xml'), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= $pageTitleSafe ?> | Property Compliance Experts</title>
    <meta name="description" content="<?= $metaDescSafe ?>">
    <meta name="keywords" content="<?= $metaKeywordsSafe ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_GB">
    <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= $pageTitleSafe ?> | Property Compliance Experts">
    <meta property="og:description" content="<?= $metaDescSafe ?>">
    <meta property="og:url" content="<?= htmlspecialchars(url($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>">
    <?php
    if (empty($ogImage)) {
        $ogImage = url('/assets/images/services/fire-alarms.jpg');
    }
    $ogImageSafe = htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8');
    ?>
    <meta property="og:image" content="<?= $ogImageSafe ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitleSafe ?> | Property Compliance Experts">
    <meta name="twitter:description" content="<?= $metaDescSafe ?>">
    <meta name="twitter:image" content="<?= $ogImageSafe ?>">
    <?php if (defined('SOCIAL_TWITTER') && SOCIAL_TWITTER !== ''): ?>
    <?php
    $twitterSite = SOCIAL_TWITTER;
    if (preg_match('~(?:twitter\.com|x\.com)/@?([A-Za-z0-9_]+)~', $twitterSite, $twMatch)) {
        $twitterSite = '@' . $twMatch[1];
    } elseif ($twitterSite[0] !== '@') {
        $twitterSite = '@' . ltrim($twitterSite, '@');
    }
    ?>
    <meta name="twitter:site" content="<?= htmlspecialchars($twitterSite, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php
    if (empty($canonicalUrl)) {
        require_once __DIR__ . '/share.php';
        $canonicalUrl = function_exists('currentPageUrl') ? currentPageUrl() : url('/');
    }
    ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" href="<?= htmlspecialchars(url('/assets/images/favicon.svg'), ENT_QUOTES, 'UTF-8') ?>" type="image/svg+xml">
    <link rel="manifest" href="<?= htmlspecialchars(url('/manifest.json'), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="<?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="geo.region" content="GB-MAN">
    <meta name="geo.placename" content="Stockport">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css">
