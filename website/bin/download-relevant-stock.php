<?php
/**
 * Download equipment-relevant FREE images (Wikimedia Commons + free Unsplash trade photos).
 * Goal: each service image clearly shows the product type (panel, camera, boiler, etc.)
 *
 * License: Wikimedia CC/PD; Unsplash license — free commercial use.
 * Prefer replacing with your own site photos before production go-live.
 */
$root = dirname(__DIR__);
$svcDir = $root . '/assets/images/services';
$kwDir = $root . '/assets/images/keywords';
foreach ([$svcDir, $kwDir] as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0755, true);
    }
}

/**
 * Multiple candidate URLs per asset — first successful download wins.
 * Sources prioritise REAL equipment over generic office/lifestyle stock.
 */
$services = [
    'electrical' => [
        // Electrical distribution / consumer unit style panels
        'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Distribution_board.jpg/1280px-Distribution_board.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8f/Fuse_box.jpg/1280px-Fuse_box.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1e/Electrical_panel.jpg/1280px-Electrical_panel.jpg',
        'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'fire-alarms' => [
        // Smoke detector / fire safety equipment
        'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Smoke_detector.jpg/1280px-Smoke_detector.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4d/Smoke_Detector.jpg/1280px-Smoke_Detector.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Manual_call_point.jpg/1280px-Manual_call_point.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Fire_alarm_break_glass.jpg/1280px-Fire_alarm_break_glass.jpg',
        'https://images.unsplash.com/photo-1582139329536-e7284fece509?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'emergency-lighting' => [
        // UK emergency exit / emergency light
        'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e4/Exit_light_sign.JPG/1280px-Exit_light_sign.JPG',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Exit_sign_-_inside.jpg/1280px-Exit_sign_-_inside.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Emergency_exit_sign.jpg/1280px-Emergency_exit_sign.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/An_emergency_exit_sign.jpg/1280px-An_emergency_exit_sign.jpg',
        'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'aov-air-handling' => [
        // HVAC / rooftop / ventilation plant
        'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Rooftop_HVAC_unit.jpg/1280px-Rooftop_HVAC_unit.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/Air_handling_unit.jpg/1280px-Air_handling_unit.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/HVAC_on_roof.jpg/1280px-HVAC_on_roof.jpg',
        'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'nurse-call' => [
        // Hospital / care corridor / medical equipment
        'https://upload.wikimedia.org/wikipedia/commons/thumb/8/88/Hospital_corridor.jpg/1280px-Hospital_corridor.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Hospital_room.jpg/1280px-Hospital_room.jpg',
        'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1631815588090-d4bfec5b1ccb?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'gas-systems' => [
        // Wall-mounted domestic boiler (most relevant)
        'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Wall_mounted_boiler_in_kitchen.jpg/1280px-Wall_mounted_boiler_in_kitchen.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Combi_boiler.jpg/1280px-Combi_boiler.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9c/Gas_boiler.jpg/1280px-Gas_boiler.jpg',
        'https://images.unsplash.com/photo-1581092160562-40aa08e78837?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'intruder-alarm' => [
        // Alarm keypad / security system
        'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Security_system_keypad.jpg/1280px-Security_system_keypad.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Home_security_system.jpg/1280px-Home_security_system.jpg',
        'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1557324232-b8917d3c3dcb?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'cctv' => [
        // Real outdoor CCTV cameras
        'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Three_Surveillance_cameras.jpg/1280px-Three_Surveillance_cameras.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f4/Fixed_outdoor_security_camera_-_Hillsboro%2C_Oregon.JPG/1280px-Fixed_outdoor_security_camera_-_Hillsboro%2C_Oregon.JPG',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Security_Cameras.jpg/1280px-Security_Cameras.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Outdoor_wireless_security_camera_at_Nuthurst%2C_Sussex_1.jpg/960px-Outdoor_wireless_security_camera_at_Nuthurst%2C_Sussex_1.jpg',
        'https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'access-control' => [
        // Card reader / electronic door access
        'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/RFID_card_reader.jpg/1280px-RFID_card_reader.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Access_control_reader.jpg/1280px-Access_control_reader.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1c/Proximity_card_reader.jpg/1280px-Proximity_card_reader.jpg',
        'https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'door-entry' => [
        // Door / video entry style
        'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Video_door_phone.jpg/1280px-Video_door_phone.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Door_intercom.jpg/1280px-Door_intercom.jpg',
        'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
    'intercoms' => [
        'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Door_intercom.jpg/1280px-Door_intercom.jpg',
        'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Video_door_phone.jpg/1280px-Video_door_phone.jpg',
        'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1400&h=900&q=85',
        'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&h=900&q=85',
    ],
];

// Keyword-specific extras (product-relevant)
$keywords = [
    'eicr' => $services['electrical'],
    'eicr-report' => $services['electrical'],
    'eicr-certificate' => $services['electrical'],
    'landlord-eicr' => $services['electrical'],
    'consumer-unit-upgrade' => $services['electrical'],
    'pat-testing' => $services['electrical'],
    'ev-charger-installation' => [
        'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?auto=format&fit=crop&w=1200&h=800&q=85',
        'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1200&h=800&q=85',
        'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1200&h=800&q=85',
    ],
    'fire-alarm-panel' => $services['fire-alarms'],
    'fire-alarm-installation' => $services['fire-alarms'],
    'fire-alarm-servicing' => $services['fire-alarms'],
    'smoke-alarm-installation' => $services['fire-alarms'],
    'kentec-fire-alarm-panel' => $services['fire-alarms'],
    'emergency-lighting-testing' => $services['emergency-lighting'],
    'emergency-lighting-certificate' => $services['emergency-lighting'],
    'security-lighting-installation' => $services['emergency-lighting'],
    'landlord-gas-safety-certificate' => $services['gas-systems'],
    'cp12-gas-certificate' => $services['gas-systems'],
    'gas-safety-certificate' => $services['gas-systems'],
    'gas-boiler-installation' => $services['gas-systems'],
    'cctv-installation' => $services['cctv'],
    'ip-cctv-system' => $services['cctv'],
    'paxton-access' => $services['access-control'],
    'paxton-net2-install' => $services['access-control'],
    'access-control-installation' => $services['access-control'],
    'video-door-entry' => $services['door-entry'],
    'door-entry-system' => $services['door-entry'],
    'nurse-call-system' => $services['nurse-call'],
    'aov-system' => $services['aov-air-handling'],
    'intruder-alarm-installation' => $services['intruder-alarm'],
];

function downloadImage(string $url, string $dest): bool {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 50,
            'follow_location' => 1,
            'header' => "User-Agent: IcomplyRelevantImageBot/2.0 (property compliance site; free stock)\r\nAccept: image/*\r\n",
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false || strlen($data) < 5000) {
        return false;
    }
    // Reject HTML error pages
    $head = substr($data, 0, 200);
    if (stripos($head, '<!DOCTYPE') !== false || stripos($head, '<html') !== false) {
        return false;
    }
    $isJpeg = str_starts_with($data, "\xFF\xD8\xFF");
    $isPng = str_starts_with($data, "\x89PNG");
    if (!$isJpeg && !$isPng) {
        // Still allow if large binary
        if (strlen($data) < 15000) {
            return false;
        }
    }
    // Convert PNG to keep .jpg extension by saving raw (browsers OK) or just save
    file_put_contents($dest, $data);
    return filesize($dest) > 5000;
}

function downloadFirst(array $urls, string $dest): array {
    foreach ($urls as $i => $url) {
        if (downloadImage($url, $dest)) {
            return [true, $i, $url];
        }
    }
    return [false, -1, ''];
}

echo "=== SERVICE IMAGES (equipment-relevant free stock) ===\n";
$ok = 0;
$fail = 0;
$sources = [];
foreach ($services as $slug => $urls) {
    $dest = $svcDir . '/' . $slug . '.jpg';
    // Force re-download for relevance fix
    if (is_file($dest)) {
        @unlink($dest);
    }
    [$success, $idx, $url] = downloadFirst($urls, $dest);
    if ($success) {
        $ok++;
        $sources[$slug] = $url;
        $kb = round(filesize($dest) / 1024);
        echo "OK   {$slug}.jpg ({$kb} KB) source#{$idx}\n";
    } else {
        $fail++;
        echo "FAIL {$slug}.jpg — all sources failed\n";
    }
}

echo "\n=== KEYWORD PRODUCT IMAGES ===\n";
$okKw = 0;
$failKw = 0;
foreach ($keywords as $slug => $urls) {
    $dest = $kwDir . '/' . $slug . '.jpg';
    if (is_file($dest)) {
        @unlink($dest);
    }
    [$success] = downloadFirst($urls, $dest);
    if ($success) {
        $okKw++;
        echo "OK   keywords/{$slug}.jpg\n";
    } else {
        $failKw++;
        // Fall back: copy service image if we have mapping
        echo "FAIL keywords/{$slug}.jpg\n";
    }
}

// Map every keyword slug → service image as fallback after successful service downloads
require_once $root . '/config.php';
$copied = 0;
foreach (getMajorKeywords() as $slug => $meta) {
    $dest = $kwDir . '/' . $slug . '.jpg';
    if (is_file($dest) && filesize($dest) > 8000) {
        continue;
    }
    $svc = $meta['service'] ?? 'electrical';
    $src = $svcDir . '/' . $svc . '.jpg';
    if (is_file($src)) {
        copy($src, $dest);
        $copied++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Services OK/FAIL: {$ok}/{$fail}\n";
echo "Keyword dedicated OK/FAIL: {$okKw}/{$failKw}\n";
echo "Keyword filled from service image: {$copied}\n";
echo "Done. Hard-refresh browser (Ctrl+F5) to see new images.\n";
