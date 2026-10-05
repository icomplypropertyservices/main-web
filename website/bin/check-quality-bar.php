<?php
/**
 * CLOSE-Q template floor. Renders the shared PHP slots and a few hub pages.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'icomplypropertyservices.co.uk';
require_once $root . '/config.php';
require_once SITE_ROOT . '/includes/quality-bar.php';
require_once SITE_ROOT . '/includes/render.php';

$fail = 0;
$ok = static function (bool $cond, string $message) use (&$fail): void {
    if ($cond) {
        echo "[PASS] {$message}\n";
    } else {
        $fail++;
        echo "[FAIL] {$message}\n";
    }
};

$proseWords = static function (string $html): int {
    $parts = [];
    if (preg_match_all('/<(p|h[1-4]|summary|dt|dd)\b[^>]*>(.*?)<\/\1>/si', $html, $m)) {
        foreach ($m[2] as $inner) {
            $parts[] = trim(html_entity_decode(strip_tags($inner)));
        }
    }
    $text = trim(preg_replace('/\s+/', ' ', implode(' ', $parts)) ?? '');
    return $text === '' ? 0 : count(preg_split('/\s+/', $text) ?: []);
};

$aov = icomplyQualityBarAovProseHtml()
    . icomplyQualityBarAovImages()['html']
    . icomplyQualityBarFaqHtml(icomplyQualityBarAovFaqs(), 'q6-aov-faq', 'q6-aov-faq-jsonld', 'Questions about AOV');
$ok($proseWords($aov) >= 800, 'AOV shell prose ' . $proseWords($aov));
$ok(substr_count($aov, '<img ') >= 3, 'AOV images');
$ok(str_contains($aov, 'q6-aov-prose') && str_contains($aov, 'q6-aov-faq') && str_contains($aov, 'FAQPage'), 'AOV slots');
$ok(!str_contains($aov, '£'), 'AOV shell has no price');

$homeFaq = icomplyQualityBarFaqHtml(icomplyQualityBarHomeFaqs(), 'q5-home-faq', 'q5-home-faq-jsonld', 'Questions about iComply');
$ok(substr_count($homeFaq, '<details') >= 3 && str_contains($homeFaq, 'q5-home-faq-jsonld'), 'homepage FAQ hook');
$ok(str_contains($homeFaq, '17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE'), 'homepage NAP');

$areaFaq = icomplyQualityBarFaqHtml(icomplyQualityBarAreaFaqs('Stockport'), 'q5-area-faq', 'q5-area-faq-jsonld', 'Questions about Stockport');
$ok(substr_count($areaFaq, '<details') >= 3 && str_contains($areaFaq, 'q5-area-faq'), 'area FAQ hook');

$thin = icomplyQualityBarThinBlock('service', 'CCTV', 'CCTV', 'cctv', 'Stockport', 'Local CCTV note.', false);
$ok($proseWords($thin) >= 800, 'PHP service×town prose ' . $proseWords($thin));
$ok(substr_count($thin, '<img ') >= 3, 'PHP thin images');
$ok(str_contains($thin, 'q1-service-town-prose') && str_contains($thin, 'property="og:image"') === false, 'thin prose slot (OG stays on the edge head)');
$ok(str_contains($thin, 'og:image') === false, 'image partial does not invent a second OG tag');

$gas = icomplyQualityBarThinBlock('job', 'Gas safety', 'Gas', 'gas-systems', 'Stockport', '', true);
$ok(str_contains($gas, 'carried out by Gas Safe registered engineers'), 'gas engineers wording');
$ok(str_contains($gas, 'iComply is not Gas Safe registered.'), 'not Gas Safe registered');
$ok(!preg_match('/iComply is Gas Safe registered\./', str_replace('iComply is not Gas Safe registered.', '', $gas)), 'no positive Gas Safe claim');

$qa = icomplyQualityBarFaqHtml(
    [['q' => 'Question from q?', 'a' => 'Answer from a.']],
    'q5-thin-faq',
    'q5-thin-faq-jsonld',
    'Questions'
);
$ok(str_contains($qa, 'Question from q?') && str_contains($qa, 'Answer from a.'), 'FAQ mapper reads q/a');
$ok(!str_contains($qa, 'question/answer'), 'FAQ mapper does not require question/answer keys');

$peTown = icomplyCloseQTownPage('service', 'cctv', 'stockport');
if (is_array($peTown) && isset($peTown['body'])) {
    $ok(str_contains($thin, 'Outward codes SK1'), 'PE town-prose body for service/cctv/stockport');
    $ok(str_contains($thin, 'data-pe-slot="q2-image-hero"') && str_contains($thin, 'hikvision.jpg'), 'PE town images');
    $ok(str_contains($thin, 'How do I ask for CCTV in Stockport?'), 'PE town faqs q/a');
}
$peArea = icomplyCloseQLoad('area-faqs.json');
if (!empty($peArea['homepage']['faqs'])) {
    $ok(str_contains($homeFaq, (string)$peArea['homepage']['faqs'][0]['q']), 'PE homepage.faqs');
}
if (!empty($peArea['stockport']['faqs'])) {
    $ok(str_contains($areaFaq, (string)$peArea['stockport']['faqs'][0]['q']), 'PE stockport.faqs');
}

ob_start();
renderAreaHubPage('Stockport');
$stockport = (string)ob_get_clean();
$ok(str_contains($stockport, 'data-pe-slot="q5-area-faq"'), 'Stockport area FAQ slot');
$ok(str_contains($stockport, 'q5-area-faq-jsonld'), 'Stockport FAQPage id');

ob_start();
renderAreaHubPage('Manchester');
$manchester = (string)ob_get_clean();
$ok(str_contains($manchester, 'data-pe-slot="q2-area-images"'), 'Manchester area image slot');
$ok(substr_count($manchester, '<img ') >= 3, 'Manchester content images ' . substr_count($manchester, '<img '));
$ok(str_contains($manchester, 'data-pe-slot="q5-area-faq"'), 'Manchester area FAQ slot');

require_once SITE_ROOT . '/includes/aov.php';
require_once SITE_ROOT . '/includes/aov-place.php';
ob_start();
aovRenderDirectory();
$directory = (string)ob_get_clean();
$ok(str_contains($directory, 'data-pe-slot="q6-aov-prose"'), 'AOV directory prose slot');
$ok($proseWords($directory) >= 800, 'AOV directory prose ' . $proseWords($directory));
$ok(substr_count($directory, '<img ') >= 3, 'AOV directory images');
$ok(str_contains($directory, 'rel="canonical"') && str_contains($directory, 'og:image'), 'AOV meta still complete');
if (!empty($peArea['manchester_images']['images'][2]['src'])) {
    $ok(str_contains($manchester, (string)$peArea['manchester_images']['images'][2]['src']), 'PE manchester_images');
}
if (!empty(icomplyCloseQLoad('aov-hub.json')['body'])) {
    $ok(str_contains($directory, 'automatic opening vents'), 'PE aov-hub.body');
}

echo $fail === 0 ? "PASS\n" : "FAIL {$fail}\n";
exit($fail === 0 ? 0 : 1);
