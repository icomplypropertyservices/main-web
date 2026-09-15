<?php
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/hmo.php';
$HMO_PAGE = hmoTopicLandings()['hmo-fire-alarms'];
require SITE_ROOT . '/includes/hmo-topic-page.php';
