<?php
/**
 * Keywords hub — physical /pages/keywords.php (pretty URL /pages/keywords).
 */
$hub = __DIR__ . '/keywords-hub.php';
if (is_file($hub)) {
    require $hub;
    return;
}
require __DIR__ . '/keywords/index.php';
