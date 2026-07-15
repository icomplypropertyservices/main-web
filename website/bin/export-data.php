<?php
/**
 * One-shot: export keywords + areas from current config into data/*.json
 * Run before replacing config.php
 */
require_once __DIR__ . '/../config.php';

$kw = getMajorKeywords();
file_put_contents(
    __DIR__ . '/../data/keywords.json',
    json_encode($kw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);
echo 'keywords: ' . count($kw) . "\n";

file_put_contents(
    __DIR__ . '/../data/areas.json',
    json_encode($areas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);
echo 'areas: ' . count($areas) . "\n";

$manufacturers = [
    'by_service' => [
        'electrical' => ['NICEIC Approved Equipment','Schneider Electric','Hager','Wylex','MK Electric','Crabtree','Fusebox','Rolec EV','Myenergi','GivEnergy'],
        'fire-alarms' => ['Kentec','Advanced Electronics','C-Tec','Morley','Hochiki','Apollo','Gent','Notifier','Honeywell','Ziton'],
        'emergency-lighting' => ['Emergi-Lite','Mackwell','Cooper Lighting','Legrand','Ansell','Thorlux','Fagerhult','Eaton','ABB','Zumtobel'],
        'aov-air-handling' => ['SE Controls','Nuaire','Brooks','Ventilux','Geze','D+H Mechatronic','TROX','Colt','Smoke Control','Assa Abloy'],
        'nurse-call' => ['Courtney Thorne','Static Systems Group','Intercall','Aid Call','Tunstall','Ascom','Schrack Seconet','Zettler','Ackermann','Rauland'],
        'gas-systems' => ['Worcester Bosch','Vaillant','Ideal','Baxi','Glow-worm','Potterton','Intergas','Remeha','Alpha','Ferroli'],
        'intruder-alarm' => ['Texecom','Honeywell','Pyronix','DSC','Visonic','Risco','Scantronic','Ajax','Paradox','GJD'],
        'cctv' => ['Hikvision','Axis Communications','Dahua','Bosch','Hanwha Vision','Avigilon','Vivotek','Uniview','Milesight','Wisenet'],
        'access-control' => ['Paxton','HID Global','Salto Systems','ASSA ABLOY','Honeywell','Gallagher','Stanley Security','CDVI','TDSi','Kantech'],
        'door-entry' => ['Videx','Fermax','BPT','Comelit','Aiphone','Paxton','Door Entry Direct','Urmet','Elvox','Golmar'],
        'intercoms' => ['Aiphone','Commend','Zenitel','Barix','Stentofon','TOA','Siedle','Clear-Com','Vingtor-Stentofon','Legrand'],
    ],
    // Must match files in assets/images/manufacturers/
    'images_by_service' => [
        'fire-alarms' => ['kentec', 'advanced-fire-panel', 'c-tec', 'hochiki', 'apollo-fire'],
        'electrical' => ['schneider-electrical', 'hager-consumer-unit', 'myenergi-ev-charger', 'rolec-ev'],
        'cctv' => ['hikvision', 'axis-camera', 'dahua-cctv'],
        'access-control' => ['paxton', 'salto-access'],
        'door-entry' => ['videx', 'fermax-door-entry', 'aiphone'],
        'intercoms' => ['aiphone'],
        'intruder-alarm' => ['texecom'],
        'gas-systems' => ['worcester-bosch'],
        'emergency-lighting' => ['kentec'],
        'nurse-call' => ['aiphone'],
        'aov-air-handling' => ['kentec'],
    ],
    'keyword_images' => [
        'electrical' => ['electrical-installation','eicr','ev-charger-installation'],
        'fire-alarms' => ['fire-alarm-panel','kentec-fire-alarm-panel','fire-alarm-installation'],
        'emergency-lighting' => ['emergency-lighting-system','led-emergency-lighting','emergency-exit-lighting'],
        'aov-air-handling' => ['aov-system','smoke-vent-system','air-handling-unit-installation'],
        'nurse-call' => ['nurse-call-system','hospital-nurse-call-system','care-home-nurse-call'],
        'gas-systems' => ['gas-boiler-installation','gas-safety-certificate','gas-installation'],
        'intruder-alarm' => ['intruder-alarm-panel','burglar-alarm-system','wireless-intruder-alarm'],
        'cctv' => ['cctv-installation','hd-cctv-camera','ip-cctv-system'],
        'access-control' => ['access-control-system','biometric-access-control','proximity-card-reader'],
        'door-entry' => ['video-door-entry','door-entry-system','audio-door-entry'],
        'intercoms' => ['video-intercom','aiphone-intercom','intercom-system'],
    ],
    'seo_keywords' => [
        'electrical' => 'electrical installation, EICR, PAT testing, certified electrician, commercial electrician, EV charger installation, electrical compliance',
        'fire-alarms' => 'fire alarm installation, fire alarm servicing, BS 5839, fire detection system, fire alarm certification, commercial fire alarm',
        'emergency-lighting' => 'emergency lighting installation, BS 5266, emergency lighting testing, emergency lighting certification, landlord emergency lighting',
        'aov-air-handling' => 'AOV installation, AOV maintenance, smoke vent system, BS 9991, automatic opening vent',
        'nurse-call' => 'nurse call system installation, nurse call maintenance, HTM 08-03, care home nurse call',
        'gas-systems' => 'gas safety certificate, gas boiler servicing, landlord gas safety, CP44, gas installation',
        'intruder-alarm' => 'intruder alarm installation, burglar alarm, BS 4737, commercial intruder alarm',
        'cctv' => 'CCTV installation, IP CCTV system, commercial CCTV, video surveillance',
        'access-control' => 'access control installation, biometric access control, door access control',
        'door-entry' => 'door entry installation, video door entry, audio door entry, apartment door entry',
        'intercoms' => 'intercom installation, video intercom, audio intercom, multi tenant intercom',
    ],
];

file_put_contents(
    __DIR__ . '/../data/manufacturers.json',
    json_encode($manufacturers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
);
echo "manufacturers.json written\n";
echo "done\n";
