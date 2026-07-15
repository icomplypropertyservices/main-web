<?php
require 'C:/xampp/htdocs/icomply/includes/render.php';
$path = comboTemplatePath('fire-alarms');
echo "PATH=$path\n";
$code = file_get_contents($path);
$placeholders = [
  'SERVICE_NAME' => 'Fire Alarms',
  'SERVICE_SLUG' => 'fire-alarms',
  'AREA' => 'Stockport',
  'AREA_SLUG' => 'stockport',
  'SEO_KEYWORDS' => 'test',
  'MANUFACTURER_TAGS' => 'tags',
  'MANUFACTURER_IMAGES' => 'imgs',
  'KEYWORD_IMAGE_1' => 'a',
  'KEYWORD_IMAGE_2' => 'b',
  'KEYWORD_IMAGE_3' => 'c',
];
$out = applyTemplatePlaceholders($code, $placeholders);
$lines = explode("\n", $out);
for ($i = 0; $i < 15; $i++) {
  echo ($i+1) . "| " . $lines[$i] . "\n";
}
// find potential bad php
if (preg_match_all('/url\([^\)]*$/m', $out, $m)) {
  echo "UNCLOSED url():\n";
  print_r($m);
}
// lint via temp file
file_put_contents('C:/xampp/htdocs/icomply/bin/_eval_debug.php', $out);
passthru('C:\\xampp\\php\\php.exe -l C:\\xampp\\htdocs\\icomply\\bin\\_eval_debug.php');
