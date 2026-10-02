<?php
/**
 * Pack cross-link from the FRA lane. £650 = FRA + EICR + gas for a typical 6-bed HMO.
 * Does not replace /pages/packages.
 */
require_once dirname(__DIR__, 2) . '/config.php';
$FRA_LANE = 'bundle';
require SITE_ROOT . '/includes/fra-job-lane.php';
