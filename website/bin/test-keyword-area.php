<?php
require_once __DIR__ . '/../includes/render.php';
ob_start();
renderKeywordAreaPage('eicr-report', 'Stockport');
$out = ob_get_clean();
$ok = strpos($out, 'Fatal') === false && strpos($out, 'EICR Report') !== false && strpos($out, 'Stockport') !== false;
echo ($ok ? 'OK' : 'FAIL') . ' len=' . strlen($out) . "\n";
echo 'title has area: ' . (strpos($out, 'in Stockport') !== false ? 'yes' : 'no') . "\n";
echo 'mfr links: ' . substr_count($out, 'pages/manufacturers/') . "\n";
