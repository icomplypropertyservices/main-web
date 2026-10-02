<?php
/**
 * Original wordmark and lane illustrations for barrier manufacturers.
 * Not official trademark artwork. Identification only.
 *
 * Usage: php bin/generate-barrier-images.php
 */
require_once __DIR__ . '/../config.php';
require_once SITE_ROOT . '/includes/barriers.php';

$dir = SITE_ROOT . '/assets/images/manufacturers';
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    fwrite(STDERR, "Cannot create {$dir}\n");
    exit(1);
}

$serviceDir = SITE_ROOT . '/assets/images/services';
if (!is_dir($serviceDir)) {
    mkdir($serviceDir, 0755, true);
}

function barrierSvgText(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function barrierWrite(string $path, string $svg): void
{
    file_put_contents($path, $svg);
    echo $path . "\n";
}

$hero = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675" role="img" aria-label="Vehicle barriers">
  <rect width="1200" height="675" fill="#0B1F3A"/>
  <rect x="0" y="470" width="1200" height="205" fill="#12263f"/>
  <rect x="80" y="520" width="1040" height="8" fill="#ff6b00" opacity="0.35"/>
  <rect x="860" y="250" width="70" height="280" rx="6" fill="#f4f4f5"/>
  <rect x="872" y="270" width="46" height="18" fill="#0B1F3A"/>
  <rect x="120" y="300" width="760" height="16" fill="#ff6b00"/>
  <circle cx="180" cy="560" r="36" fill="#18181b"/>
  <circle cx="320" cy="560" r="36" fill="#18181b"/>
  <rect x="150" y="500" width="200" height="40" rx="8" fill="#e4e4e7"/>
  <text x="80" y="160" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="64" font-weight="700">Vehicle barriers</text>
  <text x="80" y="220" fill="#ff6b00" font-family="Arial, Helvetica, sans-serif" font-size="28">CAME partner · rising-arm and parking lanes</text>
</svg>
SVG;
barrierWrite($serviceDir . '/barriers.svg', $hero);

foreach (barrierManufacturerRecords() as $brand) {
    $slug = (string)$brand['slug'];
    $name = (string)$brand['name'];
    $accent = (string)($brand['accent'] ?? '#ff6b00');
    $safeName = barrierSvgText($name);
    $safeAccent = barrierSvgText($accent);
    $words = preg_split('/\s+/u', $name) ?: [$name];
    $words = array_values(array_filter($words, static fn($w) => $w !== ''));
    if (count($words) > 2) {
        $mid = (int)ceil(count($words) / 2);
        $lines = [
            implode(' ', array_slice($words, 0, $mid)),
            implode(' ', array_slice($words, $mid)),
        ];
    } else {
        $lines = $words;
    }
    $longest = 1;
    foreach ($lines as $line) {
        $n = function_exists('mb_strlen') ? mb_strlen($line) : strlen($line);
        $longest = max($longest, $n);
    }
    $logoSize = $longest > 12 ? 22 : ($longest > 8 ? 28 : 36);
    $textBlock = '';
    $startY = count($lines) === 1 ? 132 : 112;
    foreach ($lines as $i => $line) {
        $y = $startY + ($i * ($logoSize + 8));
        $textBlock .= '<text x="28" y="' . $y . '" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="' . $logoSize . '" font-weight="700">' . barrierSvgText($line) . '</text>';
    }
    $caption = !empty($brand['partner']) ? 'Partner' : 'Barriers';

    $logo = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 160" role="img" aria-label="{$safeName} wordmark">
  <rect width="320" height="160" rx="16" fill="#0B1F3A"/>
  <rect x="0" y="0" width="8" height="160" fill="{$safeAccent}"/>
  {$textBlock}
  <text x="28" y="142" fill="#ff6b00" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="700" letter-spacing="2">{$caption}</text>
</svg>
SVG;
    barrierWrite($dir . '/' . $slug . '-logo.svg', $logo);

    $sceneSize = $longest > 14 ? 56 : 72;
    $scene = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 480" role="img" aria-label="{$safeName} rising-arm barrier">
  <rect width="1200" height="480" fill="#0B1F3A"/>
  <rect x="0" y="0" width="12" height="480" fill="{$safeAccent}"/>
  <rect x="96" y="300" width="18" height="120" rx="2" fill="#f8fafc"/>
  <rect x="96" y="292" width="760" height="14" rx="3" fill="#ff6b00"/>
  <text x="96" y="160" fill="#ffffff" font-family="Arial, Helvetica, sans-serif" font-size="{$sceneSize}" font-weight="700">{$safeName}</text>
  <text x="96" y="210" fill="#ff6b00" font-family="Arial, Helvetica, sans-serif" font-size="22" letter-spacing="3">RISING-ARM BARRIER</text>
</svg>
SVG;
    barrierWrite($dir . '/' . $slug . '-barrier.svg', $scene);
}

echo "done\n";
