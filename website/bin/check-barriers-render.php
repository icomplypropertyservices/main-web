<?php
/**
 * CLI smoke test for nationwide barrier pages.
 * Usage: php website/bin/check-barriers-render.php
 */
putenv('ICOMPLY_STATIC_EXPORT=1');
$_ENV['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['ICOMPLY_STATIC_EXPORT'] = '1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

require dirname(__DIR__) . '/includes/router.php';

function barriersRender(string $path): array
{
    if (!headers_sent()) {
        http_response_code(200);
    }
    $_SERVER['REQUEST_URI'] = $path;
    $_GET = [];
    ob_start();
    routerHandleRequest();
    return [http_response_code(), (string)ob_get_clean()];
}

$fail = 0;
$check = static function (bool $ok, string $msg) use (&$fail): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $msg . "\n";
    if (!$ok) {
        $fail++;
    }
};

[$code, $hub] = barriersRender('/pages/services/barriers');
$check($code === 200 && str_contains($hub, 'CAME partner'), 'hub 200 and CAME partner');
$check(str_contains($hub, '>979<') || str_contains($hub, '979 town'), 'hub place count');
foreach (getManufacturers('barriers') as $name) {
    $slug = areaSlug($name);
    $check(str_contains($hub, '/pages/manufacturers/' . $slug), 'hub links ' . $slug);
}
$check(!str_contains($hub, 'From £'), 'hub has no From £');

$places = json_decode((string)file_get_contents(dirname(__DIR__) . '/data/barriers-places.json'), true);
$bySlug = [];
foreach ($places as $row) {
    $bySlug[$row['slug']] = $row;
}
$seen = [];
foreach (['manchester', 'westminster', 'aberdeen', 'cardiff', 'glasgow', 'inverness'] as $slug) {
    [$c, $html] = barriersRender('/pages/barriers/' . $slug);
    $pop = number_format((int)$bySlug[$slug]['population']);
    $check($c === 200 && str_contains($html, $pop), $slug . ' contains population ' . $pop);
    $check(!str_contains(strtolower($html), 'local depot'), $slug . ' no local depot');
    $check(!str_contains(strtolower($html), 'local engineers'), $slug . ' no local engineers');
    $check(str_contains($html, 'pages/manufacturers/came'), $slug . ' deep-links CAME');
    $seen[$slug] = $html;
}
$check($seen['manchester'] !== $seen['aberdeen'], 'Manchester and Aberdeen HTML differ');
$check($seen['westminster'] !== $seen['glasgow'], 'Westminster and Glasgow HTML differ');

[$c, $html] = barriersRender('/pages/barriers/not-a-real-town-zz');
$check(str_contains($html, 'Page Not Found') || str_contains($html, 'could not be found'), 'unknown town 404 page');

[$c, $html] = barriersRender('/pages/keywords/came-partner');
$check($c === 200 && str_contains($html, 'CAME partner'), 'came-partner guide');
$check(!str_contains($html, 'Local engineers'), 'guide is not the North West keyword template');

[$c, $html] = barriersRender('/pages/keywords/came-gard-barrier/manchester');
$check(str_contains($html, 'Keyword not found'), 'barrier keyword × town is not published');

[$c, $came] = barriersRender('/pages/manufacturers/came');
$check($c === 200 && str_contains($came, 'CAME partner'), 'CAME page states the partnership');
$check(!str_contains($came, 'Authorised install'), 'CAME page does not say Authorised');
$check(!str_contains($came, 'From £'), 'CAME products are POA');
$check(str_contains($came, 'pages/keywords/came-gard-barrier'), 'CAME links the Gard guide');

[$c, $faac] = barriersRender('/pages/manufacturers/faac');
$check($c === 200 && str_contains($faac, 'not a FAAC partnership'), 'FAAC is service, not a partner');
$check(!str_contains($faac, 'Authorised install'), 'FAAC page does not say Authorised');
$check(!str_contains($faac, 'From £'), 'FAAC products are POA');

[$c, $kentec] = barriersRender('/pages/manufacturers/kentec');
$check($c === 200 && str_contains($kentec, 'Authorised install'), 'non-barrier brands keep their existing badge');

echo $fail === 0 ? "ALL PASS\n" : "FAILURES {$fail}\n";
exit($fail === 0 ? 0 : 1);
