<?php
/**
 * Admin-only service management.
 * Page generation has moved to bin/generate-site.php
 */
session_start();
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['admin'])) { 
    header('Location: index.php'); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_service') {
    $slug = trim($_POST['new_slug'] ?? '');
    $name = trim($_POST['new_name'] ?? '');
    if ($slug && $name) {
        $custom = loadServices();
        $custom[$slug] = $name;
        saveServices($custom);
        header('Location: index.php?added=1');
        exit;
    }
}

echo "Use bin/generate-site.php to generate pages.";
?>