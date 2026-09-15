<?php
/**
 * HMO Fire Safety Pack — FRA plus life-safety work as required.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
$HMO_VARIANT = hmoPackageVariants()['hmo-fire-safety'];
require SITE_ROOT . '/includes/hmo-package-page.php';
