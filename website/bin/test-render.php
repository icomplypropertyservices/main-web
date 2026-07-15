<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require 'C:/xampp/htdocs/icomply/includes/render.php';
ob_start();
try {
  renderServiceAreaPage('fire-alarms', 'Stockport');
  $h = ob_get_clean();
  echo "LEN=" . strlen($h) . "\n";
  echo (strpos($h, 'BS 5839') !== false ? "BS ok\n" : "BS FAIL\n");
  echo (strpos($h, 'Kentec') !== false ? "Kentec ok\n" : "Kentec FAIL\n");
  echo substr($h, 0, 500) . "\n";
} catch (Throwable $e) {
  ob_end_clean();
  echo "ERR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
