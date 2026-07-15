<?php
$dir = __DIR__ . "/../templates/services";
$po = "<" . "?=";
$pc = "?" . ">";
$pattern = $po . " url('') " . $pc . "/";
$pattern2 = $po . "url('')" . $pc . "/";
foreach (glob($dir . "/*.php") as $file) {
    $c = file_get_contents($file);
    foreach ([$pattern, $pattern2] as $pat) {
        $offset = 0;
        while (($p = strpos($c, $pat, $offset)) !== false) {
            $start = $p + strlen($pat);
            // path ends at quote, space, or >
            $len = strcspn($c, "\"' >", $start);
            $path = substr($c, $start, $len);
            $rep = $po . " url('/" . $path . "') " . $pc;
            $c = substr($c, 0, $p) . $rep . substr($c, $start + $len);
            $offset = $p + strlen($rep);
        }
    }
    // schema site url alone
    $c = str_replace($po . " url('') " . $pc, $po . " SITE_URL " . $pc, $c);
    $c = str_replace($po . "url('')" . $pc, $po . " SITE_URL " . $pc, $c);
    file_put_contents($file, $c);
    $left = substr_count($c, "url('')");
    echo basename($file) . " emptyleft=$left\n";
}
echo "sample:\n";
echo implode("\n", array_slice(file($dir . "/fire-alarms.php"), 16, 5));
