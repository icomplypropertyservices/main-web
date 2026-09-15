<?php
/**
 * HMO Occupancy Pack — re-let certificates and alarms.
 */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
$HMO_VARIANT = hmoPackageVariants()['hmo-occupancy'];
require SITE_ROOT . '/includes/hmo-package-page.php';
