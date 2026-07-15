<?php
$dir = __DIR__ . "/../templates/services";
$po = "<" . "?=";
$pc = "?" . ">";
foreach (glob($dir . "/*.php") as $file) {
    $c = file_get_contents($file);
    $c = str_replace("<" . "?<" . "?=", $po, $c);
    $c = str_replace("url('') " . $pc . "/contact.php", "url('/contact.php') " . $pc, $c);
    $c = str_replace("url('')" . $pc . "/contact.php", "url('/contact.php') " . $pc, $c);
    $c = str_replace("require __DIR__ . '/../../includes/header.php';", "require SITE_ROOT . '/includes/header.php';", $c);
    $c = str_replace("require __DIR__ . '/../../includes/footer.php';", "require SITE_ROOT . '/includes/footer.php';", $c);
    $c = str_replace("require_once __DIR__ . '/../../config.php';" . "\n", "", $c);
    $c = str_replace("require_once __DIR__ . '/../../config.php';", "", $c);
    $c = preg_replace('/\$ogImage\s*=\s*SITE_URL\s*\.\s*[\'"](\/assets\/[^\'"]+)[\'"]\s*;/', '\$ogImage = url(\'$1\');', $c);
    $needle = $po . " SITE_URL " . $pc . "/";
    $needle2 = $po . "SITE_URL" . $pc . "/";
    $offset = 0;
    while (($p = strpos($c, $needle2, $offset)) !== false || ($p = strpos($c, $needle, $offset)) !== false) {
        // find which matched
        $p2 = strpos($c, $needle2, $offset);
        $p1 = strpos($c, $needle, $offset);
        if ($p2 === false) { $p = $p1; $len = strlen($needle); }
        elseif ($p1 === false) { $p = $p2; $len = strlen($needle2); }
        else { if ($p1 < $p2) { $p = $p1; $len = strlen($needle); } else { $p = $p2; $len = strlen($needle2); } }
        $start = $p + $len;
        $end = strcspn($c, "\"' >", $start);
        $path = substr($c, $start, $end);
        $replacement = $po . " url('/" . $path . "') " . $pc;
        $c = substr($c, 0, $p) . $replacement . substr($c, $start + $end);
        $offset = $p + strlen($replacement);
    }
    // broken url with mismatched quote
    $c = preg_replace("/url\('(\/assets\/[^'\"]+\.jpg)\"/", "url('$1') " . $pc . '"', $c);
    $c = str_replace('$GLOBALS[\'areas\']', 'getAreas()', $c);
    $c = str_replace('$GLOBALS[\'services\']', 'getServices()', $c);
    $c = str_replace('"url": "' . $po . ' SITE_URL ' . $pc . '"', '"url": ' . $po . ' json_encode(SITE_URL) ' . $pc, $c);
    $c = str_replace('"url": "' . $po . 'SITE_URL' . $pc . '"', '"url": ' . $po . ' json_encode(SITE_URL) ' . $pc, $c);
    file_put_contents($file, $c);
    echo "fixed " . basename($file) . "\n";
}
foreach (glob($dir . "/*.php") as $file) {
    $c = file_get_contents($file);
    $bad = [];
    if (strpos($c, "<" . "?<" . "?=") !== false) $bad[] = "double";
    if (strpos($c, "url('')") !== false) $bad[] = "emptyurl";
    if (strpos($c, "SITE_URL" . $pc) !== false) $bad[] = "siteurl";
    if (strpos($c, "__DIR__") !== false) $bad[] = "dir";
    if ($bad) echo "ISSUES " . basename($file) . " " . implode(",", $bad) . "\n";
}
echo implode("", array_slice(file($dir . "/fire-alarms.php"), -10));
echo "done\n";
