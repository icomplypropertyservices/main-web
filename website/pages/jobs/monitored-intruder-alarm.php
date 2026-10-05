<?php
/** Security dual-ring P0 job hub. */
require_once __DIR__ . '/../../config.php';
require_once SITE_ROOT . '/includes/security-dual-ring.php';
securityDualRingRender('job', 'monitored-intruder-alarm', '');
