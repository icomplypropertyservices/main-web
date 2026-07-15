<?php
/**
 * Download free stock photos for each service (Unsplash CDN — free to use with attribution).
 * Overwrites assets/images/services/{slug}.jpg when download succeeds.
 * Also downloads keyword-specific hero images into assets/images/keywords/.
 */
$root = dirname(__DIR__);
$outDir = $root . '/assets/images/services';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

// Curated free Unsplash images — UK trade / building services themes
// Format: images.unsplash.com photo IDs (Unsplash License — free commercial use)
$images = [
    // Electrician / electrical installation work
    'electrical' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=1400&h=900&q=80',
    // Fire safety equipment / extinguisher (fire alarm & detection theme)
    'fire-alarms' => 'https://images.unsplash.com/photo-1635424710928-0544e8512eae?auto=format&fit=crop&w=1400&h=900&q=80',
    // Modern commercial interior with ceiling lighting (emergency lighting theme)
    'emergency-lighting' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&h=900&q=80',
    // Industrial HVAC / ducting / mechanical plant (AOV / ventilation)
    'aov-air-handling' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?auto=format&fit=crop&w=1400&h=900&q=80',
    // Hospital / care corridor (nurse call systems)
    'nurse-call' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&h=900&q=80',
    // Engineer / mechanical plant (gas systems / boiler work)
    'gas-systems' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?auto=format&fit=crop&w=1400&h=900&q=80',
    // Security keypad / alarm panel theme
    'intruder-alarm' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1400&h=900&q=80',
    // CCTV / surveillance camera
    'cctv' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1400&h=900&q=80',
    // Access control / secure entry / warehouse security
    'access-control' => 'https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=1400&h=900&q=80',
    // Residential door / entry (door entry systems)
    'door-entry' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1400&h=900&q=80',
    // Multi-storey commercial building (intercom / building communications)
    'intercoms' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&h=900&q=80',
];

// Keyword-specific images (top commercial terms) — distinct Unsplash photos where possible
$keywordExtras = [
    // Electrical / EICR
    'eicr-report' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=1200&h=800&q=80',
    'landlord-eicr' => 'https://images.unsplash.com/photo-1555963966-b7ae5404b6ed?auto=format&fit=crop&w=1200&h=800&q=80',
    'eicr' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1200&h=800&q=80',
    // Fire alarms
    'fire-alarm-installation' => 'https://images.unsplash.com/photo-1563453392212-326f5e854473?auto=format&fit=crop&w=1200&h=800&q=80',
    'fire-alarm-servicing' => 'https://images.unsplash.com/photo-1582139329536-e7284fece509?auto=format&fit=crop&w=1200&h=800&q=80',
    'fire-alarm-panel' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1200&h=800&q=80',
    // Emergency lighting
    'emergency-lighting-certificate' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1200&h=800&q=80',
    'emergency-lighting-testing' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&h=800&q=80',
    // Gas / CP12
    'landlord-gas-safety-certificate' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=1200&h=800&q=80',
    'cp12-gas-certificate' => 'https://images.unsplash.com/photo-1581092162384-8987c1d64718?auto=format&fit=crop&w=1200&h=800&q=80',
    'gas-safety-certificate' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1200&h=800&q=80',
    // CCTV / security
    'cctv-installation' => 'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?auto=format&fit=crop&w=1200&h=800&q=80',
    'security-lighting-installation' => 'https://images.unsplash.com/photo-1513828583688-c52646db42da?auto=format&fit=crop&w=1200&h=800&q=80',
    'intruder-alarm-installation' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=1200&h=800&q=80',
    // Access control / Paxton / door entry
    'paxton-net2-install' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&h=800&q=80',
    'access-control-installation' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&h=800&q=80',
    'video-door-entry' => 'https://images.unsplash.com/photo-1556912173-46c336c7fd55?auto=format&fit=crop&w=1200&h=800&q=80',
    'paxton-access' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&h=800&q=80',
    // Nurse call / AOV
    'nurse-call-system' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&h=800&q=80',
    'aov-system' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?auto=format&fit=crop&w=1200&h=800&q=80',
];

function downloadTo(string $url, string $file): bool {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 45,
            'header' => "User-Agent: IcomplyStockBot/1.0\r\n",
            'follow_location' => 1,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false || strlen($data) < 3000) {
        return false;
    }
    // Basic JPEG/PNG sniff
    $isImg = str_starts_with($data, "\xFF\xD8")
        || str_starts_with($data, "\x89PNG")
        || str_contains(substr($data, 0, 20), 'JFIF')
        || str_contains(substr($data, 0, 20), 'Exif');
    if (!$isImg && strlen($data) < 8000) {
        return false;
    }
    file_put_contents($file, $data);
    return true;
}

echo "Service stock downloads\n";
$svcOk = 0;
$svcFail = 0;
foreach ($images as $slug => $url) {
    $file = $outDir . '/' . $slug . '.jpg';
    $ok = downloadTo($url, $file);
    echo ($ok ? 'OK  ' : 'FAIL') . " services/{$slug}.jpg\n";
    $ok ? $svcOk++ : $svcFail++;
}

$kwDir = $root . '/assets/images/keywords';
if (!is_dir($kwDir)) {
    mkdir($kwDir, 0755, true);
}
echo "\nKeyword stock downloads\n";
$kwOk = 0;
$kwFail = 0;
foreach ($keywordExtras as $slug => $url) {
    $file = $kwDir . '/' . $slug . '.jpg';
    $ok = downloadTo($url, $file);
    echo ($ok ? 'OK  ' : 'FAIL') . " keywords/{$slug}.jpg\n";
    $ok ? $kwOk++ : $kwFail++;
}

echo "\nDone. Services OK/FAIL: {$svcOk}/{$svcFail} | Keywords OK/FAIL: {$kwOk}/{$kwFail}\n";
