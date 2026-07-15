<?php
require_once __DIR__ . "/../includes/render.php";
ob_start();
renderKeywordPage("eicr");
$o = ob_get_clean();
echo (strpos($o, "061828") !== false ? "contrast-ok " : "no-contrast ");
echo (strpos($o, "Fatal") === false ? "render-ok " : "fail ");
echo "len=" . strlen($o) . "\n";
echo (strpos($o, "Looking for expert") !== false ? "generic-opener-found\n" : "unique-copy-likely\n");
