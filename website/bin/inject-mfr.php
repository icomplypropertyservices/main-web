<?php
$map = [
  'electrical' => 'electrical',
  'gas-systems' => 'gas-systems',
  'cctv' => 'cctv',
  'access-control' => 'access-control',
  'door-entry' => 'door-entry',
  'intercoms' => 'intercoms',
  'intruder-alarm' => 'intruder-alarm',
  'nurse-call' => 'nurse-call',
];
$dir = 'C:/xampp/htdocs/icomply/templates/services';
foreach ($map as $file => $slug) {
  $path = "$dir/$file.php";
  $c = file_get_contents($path);
  if (strpos($c, 'manufacturerImagesHtml') !== false) {
    echo "skip $file\n";
    continue;
  }
  // Replace the first flex-wrap gap-3 block after Manufacturers heading with dynamic helpers
  $block = "        <div class=\"flex flex-wrap gap-3\">\n"
    . "            <?= manufacturerTagsHtml('$slug') ?>\n"
    . "        </div>\n"
    . "        <div class=\"grid grid-cols-2 md:grid-cols-4 gap-4 mt-6\">\n"
    . "            <?= manufacturerImagesHtml('$slug') ?>\n"
    . "        </div>";
  $new = preg_replace(
    '/<div class="flex flex-wrap gap-3">\s*(?:<span class="px-4 py-2 bg-white border rounded-full[^>]*>.*?<\/span>\s*)+<\/div>/s',
    $block,
    $c,
    1,
    $count
  );
  if ($count) {
    file_put_contents($path, $new);
    echo "updated $file\n";
  } else {
    echo "NOMATCH $file\n";
  }
}
