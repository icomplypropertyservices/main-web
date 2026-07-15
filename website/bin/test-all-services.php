<?php
require 'C:/xampp/htdocs/icomply/includes/render.php';
$services = array_keys(getServices());
foreach ($services as $s) {
  ob_start();
  try {
    renderServiceAreaPage($s, 'Manchester');
    $h = ob_get_clean();
    $ok = strlen($h) > 5000 && strpos($h, 'DOCTYPE') !== false;
    echo ($ok ? 'OK' : 'FAIL') . " $s len=" . strlen($h) . "\n";
  } catch (Throwable $e) {
    ob_end_clean();
    echo "ERR $s " . $e->getMessage() . "\n";
  }
}
ob_start();
renderKeywordPage('kentec-fire-alarm-panel');
$h = ob_get_clean();
echo (strlen($h)>5000?'OK':'FAIL') . " keyword len=" . strlen($h) . "\n";
