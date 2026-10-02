<?php
/**
 * Trade products hub — self-canonical /products. Body in products-hub-body.php.
 */
require_once __DIR__ . '/../config.php';
if (function_exists('icomplyRequireShopify')) {
    icomplyRequireShopify();
} elseif (is_file(SITE_ROOT . '/includes/shopify.php')) {
    require_once SITE_ROOT . '/includes/shopify.php';
}
if (is_file(SITE_ROOT . '/includes/aov-kit-prices.php')) {
    require_once SITE_ROOT . '/includes/aov-kit-prices.php';
}
$pageTitle = 'Trade Products | Electrical, Fire, Security & Gas';
$metaDesc = 'iComply trade products — Electrical, Fire, Security and Gas. AOV install POA.';
$metaKeywords = 'icomply shop, trade electrical, fire, security, gas, AOV, barrier';
$ogImage = url('/assets/images/services/fire-alarms.jpg');
$canonicalUrl = url('/products.php');
$shop = function_exists('shopifyStoreUrl') ? shopifyStoreUrl() : 'https://shop.icomplypropertyservices.co.uk';
$aovService = url('/pages/services/aov-air-handling.php');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
require SITE_ROOT . '/includes/header.php';
require SITE_ROOT . '/includes/products-hub-body.php';
require SITE_ROOT . '/includes/footer.php';
