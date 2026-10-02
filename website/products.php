<?php
/**
 * Thin 301 — trade + AOV hub lives at /products (pages/products.php via router).
 * Avoids competing life-safety / trade hubs.
 */
require_once __DIR__ . '/config.php';
header('Location: ' . url('/products.php'), true, 301);
exit;
