<?php
/**
 * Ensure each service has 30–50 SEO keyword landing pages.
 * Merges into data/keywords.json (keeps existing entries).
 *
 * Usage: php bin/expand-keywords-per-service.php
 */
require_once __DIR__ . '/../config.php';

/** @var array<string, list<array{0:string,1:string,2?:string}>> name, related slug optional */
$newByService = [
    'electrical' => [
        ['EICR Report', 'eicr'],
        ['EICR Certificate', 'eicr'],
        ['Landlord EICR', 'landlord-eicr'],
        ['Commercial EICR', 'commercial-eicr'],
        ['EICR Cost', 'eicr'],
        ['EICR Testing', 'fixed-wire-testing'],
        ['Electrical Installation Condition Report', 'eicr'],
        ['Periodic Electrical Inspection', 'eicr'],
        ['Electrical Safety Certificate', 'eicr'],
        ['Consumer Unit Upgrade', 'consumer-unit-upgrade'],
        ['Fuse Board Replacement', 'consumer-unit-upgrade'],
        ['Full House Rewire', 'residential-electrician'],
        ['Partial Rewire', 'electrical-wiring-installation'],
        ['PAT Testing', 'pat-testing'],
        ['Portable Appliance Testing', 'pat-testing'],
        ['EV Charger Installation', 'ev-charger-installation'],
        ['Home EV Charger', 'ev-charger-installation'],
        ['Commercial EV Charging', 'ev-charger-installation'],
        ['Three Phase Electrical Installation', 'commercial-electrical-installation'],
        ['Emergency Electrician', '24-hour-emergency-electrician'],
        ['Electrical Fault Finding', 'electrical-repair'],
        ['Socket Installation', 'electrical-installation'],
        ['Lighting Circuit Installation', 'electrical-installation'],
        ['Outside Socket Installation', 'electrical-installation'],
        ['Electrical Compliance Certificate', 'eicr'],
        ['HMO Electrical Certificate', 'landlord-eicr'],
        ['Office Electrical Testing', 'commercial-eicr'],
        ['Industrial Electrical Maintenance', 'commercial-electrical-maintenance'],
        ['Electrical Remedial Works', 'eicr'],
        ['RCBO Consumer Unit', 'consumer-unit-upgrade'],
        ['Electrical Inspection and Testing', 'eicr'],
        ['Landlord Electrical Safety Check', 'landlord-eicr'],
        ['BS 7671 Inspection', 'eicr'],
        ['Electrical Certificate for Lettings', 'landlord-eicr'],
        ['New Build Electrical Installation', 'new-electrical-installation'],
        ['Electrical Board Upgrade', 'consumer-unit-upgrade'],
        ['Distribution Board Installation', 'commercial-electrical-installation'],
        ['Emergency Lighting Electrical Works', 'emergency-lighting-installation'],
        ['Data Cabling Installation', 'electrical-installation'],
        ['Landlord Electrical Certificate North West', 'landlord-eicr'],
    ],
    'fire-alarms' => [
        ['Fire Alarm Installation', 'fire-alarm-installation'],
        ['Fire Alarm Servicing', 'fire-alarm-servicing'],
        ['Fire Alarm Certificate', 'fire-safety-certificate'],
        ['Fire Alarm Maintenance Contract', 'fire-alarm-maintenance'],
        ['BS 5839 Fire Alarm', 'bs-5839'],
        ['Addressable Fire Alarm System', 'addressable-fire-alarm'],
        ['Conventional Fire Alarm System', 'conventional-fire-alarm'],
        ['Wireless Fire Alarm System', 'wireless-fire-alarm'],
        ['Fire Alarm Panel Service', 'fire-alarm-panel'],
        ['Fire Detection System Installation', 'fire-alarm-installation'],
        ['Smoke Detector Installation Commercial', 'fire-alarm-installation'],
        ['Fire Alarm Call Point Installation', 'fire-alarm-installation'],
        ['Landlord Fire Alarm', 'landlord-fire-alarm'],
        ['HMO Fire Alarm System', 'hmo-fire-alarm-system'],
        ['Care Home Fire Alarm', 'nurse-call-system'],
        ['Office Fire Alarm Installation', 'commercial-fire-alarm'],
        ['Factory Fire Alarm System', 'commercial-fire-alarm'],
        ['Fire Alarm Zone Chart', 'fire-alarm-panel'],
        ['Fire Alarm Battery Replacement', 'fire-alarm-servicing'],
        ['Fire Alarm Fault Finding', 'fire-alarm-repair'],
        ['Fire Alarm Commissioning', 'fire-alarm-installation'],
        ['Fire Alarm Upgrade', 'fire-alarm-installation'],
        ['L1 Fire Alarm System', 'bs-5839'],
        ['L2 Fire Alarm System', 'bs-5839'],
        ['L3 Fire Alarm System', 'bs-5839'],
        ['Category LD2 Fire Alarm', 'landlord-fire-alarm'],
        ['Category LD3 Fire Alarm', 'landlord-fire-alarm'],
        ['Fire Alarm Weekly Test Support', 'fire-alarm-servicing'],
        ['Fire Alarm Logbook', 'fire-alarm-certificate'],
        ['Multi Site Fire Alarm Maintenance', 'fire-alarm-maintenance'],
        ['Aspirating Smoke Detection', 'vesda'],
        ['VESDA Installation', 'xtralis-vesda'],
        ['Beam Detection System', 'fire-alarm-installation'],
        ['Voice Alarm System', 'fire-alarm-installation'],
        ['Fire Alarm Engineers Near Me', 'fire-alarm-installation'],
        ['Fire Risk Assessment Support', 'fire-risk-assessment-support'],
        ['Fire Safety Order Compliance', 'fire-safety-certificate'],
        ['Kentec Panel Service', 'kentec-panel-service'],
        ['Advanced Fire Panel Service', 'fire-alarm-panel'],
        ['C-Tec Fire Alarm Service', 'fire-alarm-panel'],
        ['Hochiki Fire Detection', 'fire-alarm-installation'],
        ['Apollo Fire Detectors', 'fire-alarm-installation'],
        ['Fire Alarm Monitoring Setup', 'fire-alarm-installation'],
        ['Domestic Fire Alarm Installation', 'landlord-fire-alarm'],
        ['Commercial Fire Alarm Service', 'commercial-fire-alarm'],
    ],
    'emergency-lighting' => [
        ['Emergency Lighting Installation', 'emergency-lighting-installation'],
        ['Emergency Lighting Testing', 'emergency-lighting-testing'],
        ['Emergency Lighting Certificate', 'emergency-lighting-certificate'],
        ['Emergency Lighting Servicing', 'emergency-lighting-maintenance'],
        ['BS 5266 Emergency Lighting', 'bs-5266'],
        ['Emergency Exit Lighting', 'emergency-exit-lighting'],
        ['LED Emergency Lighting Conversion', 'led-emergency-lighting'],
        ['Maintained Emergency Lighting', 'emergency-lighting-installation'],
        ['Non Maintained Emergency Lighting', 'emergency-lighting-installation'],
        ['Self Test Emergency Lighting', 'emergency-lighting-testing'],
        ['Emergency Lighting Monthly Test', 'emergency-lighting-testing'],
        ['Emergency Lighting Annual Test', 'emergency-lighting-certificate'],
        ['Emergency Lighting Duration Test', 'emergency-lighting-testing'],
        ['Landlord Emergency Lighting', 'landlord-emergency-lighting'],
        ['Commercial Emergency Lighting', 'emergency-lighting-installation'],
        ['Office Emergency Lighting', 'emergency-lighting-installation'],
        ['Warehouse Emergency Lighting', 'emergency-lighting-installation'],
        ['Care Home Emergency Lighting', 'emergency-lighting-installation'],
        ['Emergency Bulkhead Lights', 'emergency-lighting-installation'],
        ['Emergency Twin Spot Lights', 'emergency-lighting-installation'],
        ['Exit Sign Installation', 'emergency-exit-lighting'],
        ['Running Man Exit Signs', 'emergency-exit-lighting'],
        ['Emergency Lighting Battery Replacement', 'emergency-lighting-servicing'],
        ['Emergency Lighting Upgrade', 'led-emergency-lighting'],
        ['Emergency Lighting Remedials', 'emergency-lighting-certificate'],
        ['Security Lighting Installation', 'external-security-lighting'],
        ['External Security Lighting', 'external-security-lighting'],
        ['Commercial Security Lighting', 'external-security-lighting'],
        ['PIR Security Lighting', 'external-security-lighting'],
        ['Car Park Lighting Installation', 'external-security-lighting'],
        ['Emergency Lighting Logbook', 'emergency-lighting-certificate'],
        ['Emergency Lighting Design', 'emergency-lighting-installation'],
        ['Emergency Lighting Survey', 'emergency-lighting-testing'],
        ['Multi Site Emergency Lighting Contract', 'emergency-lighting-maintenance'],
        ['Central Battery Emergency Lighting', 'emergency-lighting-installation'],
        ['Emergency Lighting Engineers', 'emergency-lighting-installation'],
        ['BS 5266 Compliance Certificate', 'emergency-lighting-certificate'],
        ['Escape Route Lighting', 'emergency-lighting-installation'],
        ['Open Area Emergency Lighting', 'emergency-lighting-installation'],
        ['Emergency Lighting for Landlords', 'landlord-emergency-lighting'],
    ],
    'gas-systems' => [
        ['Landlord Gas Safety Certificate', 'landlord-gas-safety-certificate'],
        ['Gas Safety Certificate', 'gas-safety-certificate'],
        ['CP12 Gas Certificate', 'cp12-gas-certificate'],
        ['CP44 Gas Certificate', 'cp12-gas-certificate'],
        ['Landlord Gas Certificate', 'landlord-gas-safety-certificate'],
        ['Gas Safe Engineer', 'gas-safe-register-engineer'],
        ['Boiler Service', 'gas-boiler-servicing'],
        ['Boiler Installation', 'gas-boiler-installation'],
        ['Commercial Gas Safety Certificate', 'commercial-gas-safety-certificate'],
        ['Gas Safety Check', 'gas-safety-certificate'],
        ['Annual Gas Safety Certificate', 'landlord-gas-safety-certificate'],
        ['Lettings Gas Certificate', 'landlord-gas-safety-certificate'],
        ['HMO Gas Safety Certificate', 'landlord-gas-safety-certificate'],
        ['Gas Certificate for Tenants', 'landlord-gas-safety-certificate'],
        ['Gas Appliance Service', 'gas-boiler-servicing'],
        ['Gas Cooker Installation', 'gas-installation'],
        ['Gas Hob Installation', 'gas-installation'],
        ['Commercial Boiler Service', 'commercial-gas'],
        ['Gas Tightness Test', 'gas-safety-certificate'],
        ['Gas Leak Detection', 'gas-repair'],
        ['Boiler Repair', 'gas-boiler-servicing'],
        ['Combi Boiler Installation', 'gas-boiler-installation'],
        ['Gas Fire Installation', 'gas-installation'],
        ['Carbon Monoxide Alarm Installation', 'carbon-monoxide-alarm-installation'],
        ['Landlord Gas Safety Inspection', 'landlord-gas-safety-certificate'],
        ['Gas Certificate Same Day', 'gas-safety-certificate'],
        ['Multi Property Gas Certificates', 'landlord-gas-safety-certificate'],
        ['Gas Safety Record', 'gas-safety-certificate'],
        ['LGSR Certificate', 'landlord-gas-safety-certificate'],
        ['Gas Compliance Certificate', 'gas-safety-certificate'],
        ['Commercial Kitchen Gas Safety', 'commercial-gas-safety-certificate'],
        ['Gas Meter Works', 'gas-installation'],
        ['Flue Installation', 'gas-boiler-installation'],
        ['Power Flush Heating', 'gas-boiler-servicing'],
        ['Heating System Service', 'gas-boiler-servicing'],
        ['Gas Engineer Near Me', 'gas-safe-register-engineer'],
        ['Emergency Gas Engineer', 'gas-repair'],
        ['Boiler Breakdown Repair', 'gas-boiler-servicing'],
        ['New Boiler Quote', 'gas-boiler-installation'],
        ['Gas Safety for Landlords North West', 'landlord-gas-safety-certificate'],
    ],
    'cctv' => [
        ['CCTV Installation', 'cctv-installation'],
        ['Commercial CCTV System', 'commercial-cctv-system'],
        ['IP CCTV System', 'ip-cctv-system'],
        ['HD CCTV Camera Installation', 'hd-cctv-camera'],
        ['CCTV for Business', 'commercial-cctv-system'],
        ['Shop CCTV Installation', 'commercial-cctv-system'],
        ['Warehouse CCTV', 'commercial-cctv-system'],
        ['Office CCTV Installation', 'commercial-cctv-system'],
        ['Domestic CCTV Installation', 'cctv-installation'],
        ['CCTV Camera Installation', 'cctv-installation'],
        ['NVR Installation', 'nvr-installation'],
        ['DVR Installation', 'cctv-recording'],
        ['CCTV Remote Viewing Setup', 'cctv-installation'],
        ['CCTV Maintenance', 'cctv-servicing'],
        ['CCTV Repair', 'cctv-servicing'],
        ['ANPR CCTV System', 'anpr-cctv-system'],
        ['Number Plate Recognition Camera', 'anpr-cctv-system'],
        ['PTZ Camera Installation', 'cctv-installation'],
        ['Dome Camera Installation', 'cctv-installation'],
        ['Bullet Camera Installation', 'cctv-installation'],
        ['CCTV System Design', 'cctv-installation'],
        ['Multi Site CCTV', 'commercial-cctv-system'],
        ['CCTV for Car Parks', 'commercial-cctv-system'],
        ['CCTV for Schools', 'commercial-cctv-system'],
        ['CCTV for Care Homes', 'commercial-cctv-system'],
        ['Hikvision CCTV Installation', 'hikvision'],
        ['Axis Camera Installation', 'axis-communications'],
        ['Dahua CCTV Installation', 'dahua'],
        ['CCTV Cabling Installation', 'cctv-installation'],
        ['Wireless CCTV Installation', 'cctv-installation'],
        ['4K CCTV System', 'hd-cctv-camera'],
        ['CCTV Monitoring Setup', 'cctv-installation'],
        ['Security Camera Installation', 'cctv-installation'],
        ['Video Surveillance System', 'cctv-installation'],
        ['CCTV Upgrade', 'cctv-installation'],
        ['CCTV Hard Drive Upgrade', 'nvr-installation'],
        ['External CCTV Installation', 'cctv-installation'],
        ['Internal CCTV Installation', 'cctv-installation'],
        ['CCTV Engineers Near Me', 'cctv-installation'],
        ['Business Security Cameras', 'commercial-cctv-system'],
    ],
    'access-control' => [
        ['Access Control Installation', 'access-control-installation'],
        ['Door Access Control System', 'door-access-control'],
        ['Paxton Net2 Install', 'paxton-net2-install'],
        ['Paxton Access Control', 'paxton-access'],
        ['Paxton Net2 Maintenance', 'paxton-net2-maintenance'],
        ['Salto Access Control', 'salto-access-control'],
        ['HID Access Control', 'access-control-installation'],
        ['Proximity Card Reader Installation', 'proximity-card-reader'],
        ['Fob Access Control', 'proximity-card-reader'],
        ['Biometric Access Control', 'biometric-access-control'],
        ['Fingerprint Access Control', 'biometric-access-control'],
        ['Keypad Access Control', 'access-control-installation'],
        ['Magnetic Lock Installation', 'access-control-installation'],
        ['Electric Strike Installation', 'access-control-installation'],
        ['Office Access Control', 'access-control-installation'],
        ['Multi Door Access Control', 'access-control-installation'],
        ['Multi Site Access Control', 'access-control-installation'],
        ['Access Control Maintenance', 'access-control-servicing'],
        ['Access Control Repair', 'access-control-servicing'],
        ['Gate Access Control', 'access-control-installation'],
        ['Car Park Barrier Access', 'access-control-installation'],
        ['Time Attendance Access Control', 'access-control-installation'],
        ['Cloud Access Control', 'access-control-installation'],
        ['Wireless Access Control', 'access-control-installation'],
        ['Fire Door Access Control Integration', 'access-control-installation'],
        ['Access Control Card Programming', 'access-control-installation'],
        ['Door Controller Installation', 'access-control-installation'],
        ['Access Control System Design', 'access-control-installation'],
        ['Commercial Access Control', 'access-control-installation'],
        ['Building Access System', 'access-control-installation'],
        ['Secure Door Entry Access', 'door-access-control'],
        ['Access Control Engineers', 'access-control-installation'],
        ['Visitor Access Management', 'access-control-installation'],
        ['Lift Access Control', 'access-control-installation'],
        ['ASSA ABLOY Access Control', 'access-control-installation'],
    ],
    'door-entry' => [
        ['Door Entry System Installation', 'door-entry-system'],
        ['Video Door Entry', 'video-door-entry'],
        ['Audio Door Entry', 'audio-door-entry'],
        ['Apartment Door Entry System', 'apartment-door-entry'],
        ['Block of Flats Door Entry', 'apartment-door-entry'],
        ['Videx Door Entry', 'videx-door-entry'],
        ['Fermax Door Entry', 'fermax-door-entry'],
        ['Aiphone Door Entry', 'aiphone'],
        ['GSM Door Entry System', 'video-door-entry'],
        ['IP Door Entry System', 'video-door-entry'],
        ['Colour Video Door Entry', 'video-door-entry'],
        ['Door Entry Handset Replacement', 'door-entry-system'],
        ['Door Entry Panel Replacement', 'door-entry-system'],
        ['Multi Tenant Door Entry', 'apartment-door-entry'],
        ['Trade Button Door Entry', 'door-entry-system'],
        ['Door Entry Maintenance', 'door-entry-servicing'],
        ['Door Entry Repair', 'door-entry-servicing'],
        ['Video Door Phone Installation', 'video-door-entry'],
        ['Door Entry System Upgrade', 'door-entry-system'],
        ['Smartphone Door Entry', 'video-door-entry'],
        ['BPT Door Entry', 'door-entry-system'],
        ['Comelit Door Entry', 'door-entry-system'],
        ['Urmet Door Entry', 'door-entry-system'],
        ['Commercial Door Entry', 'door-entry-system'],
        ['Residential Door Entry', 'apartment-door-entry'],
        ['Door Entry Cabling', 'door-entry-system'],
        ['Door Entry Engineers Near Me', 'door-entry-system'],
        ['Communal Door Entry System', 'apartment-door-entry'],
        ['Door Entry Access Integration', 'door-entry-system'],
        ['Video Intercom Door Entry', 'video-door-entry'],
    ],
    'intercoms' => [
        ['Intercom Installation', 'intercom-system'],
        ['Video Intercom System', 'video-intercom'],
        ['Audio Intercom System', 'audio-intercom'],
        ['Multi Tenant Intercom', 'multi-tenant-intercom'],
        ['Office Intercom System', 'intercom-system'],
        ['Commercial Intercom Installation', 'intercom-system'],
        ['Aiphone Intercom', 'aiphone-intercom'],
        ['IP Intercom System', 'video-intercom'],
        ['SIP Intercom Installation', 'intercom-system'],
        ['Master Station Intercom', 'intercom-system'],
        ['Door Station Intercom', 'intercom-system'],
        ['Factory Intercom System', 'intercom-system'],
        ['School Intercom System', 'intercom-system'],
        ['Hospital Intercom System', 'intercom-system'],
        ['Warehouse Intercom', 'intercom-system'],
        ['Intercom Maintenance', 'intercom-servicing'],
        ['Intercom Repair', 'intercom-servicing'],
        ['Wireless Intercom Installation', 'intercom-system'],
        ['Intercom System Upgrade', 'intercom-system'],
        ['Gate Intercom System', 'intercom-system'],
        ['Reception Intercom', 'intercom-system'],
        ['Multi Building Intercom', 'intercom-system'],
        ['Zenitel Intercom', 'intercom-system'],
        ['Commend Intercom', 'intercom-system'],
        ['Siedle Intercom', 'intercom-system'],
        ['Intercom Cabling Installation', 'intercom-system'],
        ['Intercom Engineers Near Me', 'intercom-system'],
        ['Business Intercom System', 'intercom-system'],
        ['Secure Entry Intercom', 'video-intercom'],
        ['Outdoor Intercom Station', 'intercom-system'],
    ],
    'intruder-alarm' => [
        ['Intruder Alarm Installation', 'intruder-alarm-installation'],
        ['Burglar Alarm Installation', 'burglar-alarm-system'],
        ['Wireless Intruder Alarm', 'wireless-intruder-alarm'],
        ['Wired Intruder Alarm System', 'intruder-alarm-installation'],
        ['Commercial Intruder Alarm', 'commercial-intruder-alarm'],
        ['Domestic Burglar Alarm', 'burglar-alarm-system'],
        ['Grade 2 Intruder Alarm', 'grade-2-intruder-alarm'],
        ['Grade 3 Intruder Alarm', 'intruder-alarm-installation'],
        ['PD 6662 Alarm System', 'pd6662-alarm-system'],
        ['Texecom Alarm Installation', 'texecom'],
        ['Ajax Alarm Installation', 'ajax-systems'],
        ['Intruder Alarm Maintenance', 'intruder-alarm-servicing'],
        ['Intruder Alarm Repair', 'intruder-alarm-servicing'],
        ['Alarm System Upgrade', 'intruder-alarm-installation'],
        ['Monitored Intruder Alarm', 'intruder-alarm-installation'],
        ['Police Response Alarm', 'intruder-alarm-installation'],
        ['PIR Sensor Installation', 'intruder-alarm-installation'],
        ['Door Contact Alarm Installation', 'intruder-alarm-installation'],
        ['Shock Sensor Installation', 'intruder-alarm-installation'],
        ['Alarm Keypad Installation', 'intruder-alarm-panel'],
        ['Intruder Alarm Panel Replacement', 'intruder-alarm-panel'],
        ['Shop Alarm Installation', 'commercial-intruder-alarm'],
        ['Office Alarm Installation', 'commercial-intruder-alarm'],
        ['Warehouse Alarm System', 'commercial-intruder-alarm'],
        ['Smart Home Alarm Installation', 'wireless-intruder-alarm'],
        ['Alarm Monitoring Setup', 'intruder-alarm-installation'],
        ['Intruder Alarm Engineers', 'intruder-alarm-installation'],
        ['Security Alarm Installation', 'burglar-alarm-system'],
        ['Alarm Battery Replacement', 'intruder-alarm-servicing'],
        ['Multi Site Alarm Maintenance', 'intruder-alarm-servicing'],
        ['Pyronix Alarm Installation', 'intruder-alarm-installation'],
        ['Honeywell Intruder Alarm', 'intruder-alarm-installation'],
        ['Risco Alarm Installation', 'intruder-alarm-installation'],
        ['Alarm System Design', 'intruder-alarm-installation'],
        ['Business Premises Alarm', 'commercial-intruder-alarm'],
    ],
    'nurse-call' => [
        ['Nurse Call System Installation', 'nurse-call-system'],
        ['Nurse Call Maintenance', 'nurse-call-maintenance'],
        ['Care Home Nurse Call', 'care-home-nurse-call'],
        ['Hospital Nurse Call System', 'hospital-nurse-call-system'],
        ['HTM 08-03 Nurse Call', 'htm-08-03'],
        ['Nurse Call Panel Installation', 'nurse-call-system'],
        ['Nurse Call Upgrade', 'nurse-call-system'],
        ['Wireless Nurse Call System', 'nurse-call-system'],
        ['Wired Nurse Call System', 'nurse-call-system'],
        ['Courtney Thorne Nurse Call', 'courtney-thorne'],
        ['Static Systems Nurse Call', 'nurse-call-system'],
        ['Intercall Nurse Call', 'nurse-call-system'],
        ['Tunstall Nurse Call', 'nurse-call-system'],
        ['Aid Call System', 'nurse-call-system'],
        ['Nurse Call Pear Lead Replacement', 'nurse-call-maintenance'],
        ['Nurse Call Point Installation', 'nurse-call-system'],
        ['Disabled Toilet Alarm Installation', 'nurse-call-system'],
        ['Emergency Pull Cord Installation', 'nurse-call-system'],
        ['Care Home Call System', 'care-home-nurse-call'],
        ['Nursing Home Nurse Call', 'care-home-nurse-call'],
        ['Residential Care Call System', 'care-home-nurse-call'],
        ['Nurse Call Service Contract', 'nurse-call-maintenance'],
        ['Nurse Call Repair', 'nurse-call-maintenance'],
        ['Nurse Call Battery Replacement', 'nurse-call-maintenance'],
        ['Multi Site Nurse Call Maintenance', 'nurse-call-maintenance'],
        ['Staff Attack Alarm System', 'nurse-call-system'],
        ['Wander Alert System', 'nurse-call-system'],
        ['Assisted Living Call System', 'nurse-call-system'],
        ['Nurse Call System Design', 'nurse-call-system'],
        ['Nurse Call Engineers', 'nurse-call-system'],
        ['Ascom Nurse Call', 'nurse-call-system'],
        ['Zettler Nurse Call', 'nurse-call-system'],
        ['Quantec Nurse Call', 'nurse-call-system'],
        ['Healthcare Call System Installation', 'hospital-nurse-call-system'],
        ['Nurse Call Commissioning', 'nurse-call-system'],
    ],
    'aov-air-handling' => [
        ['AOV Installation', 'aov-system'],
        ['AOV Maintenance', 'aov-maintenance'],
        ['Automatic Opening Vent Installation', 'aov-system'],
        ['Smoke Vent System', 'smoke-vent-system'],
        ['Smoke Control System', 'smoke-control-system'],
        ['Smoke Shaft AOV', 'smoke-shaft-aov'],
        ['AOV Actuator Installation', 'aov-system'],
        ['AOV Panel Installation', 'aov-system'],
        ['AOV Servicing', 'aov-maintenance'],
        ['AOV Testing and Certification', 'aov-maintenance'],
        ['BS 9991 Smoke Control', 'smoke-control-system'],
        ['EN 12101 Smoke Vent', 'smoke-vent-system'],
        ['Natural Smoke Ventilation', 'smoke-vent-system'],
        ['Mechanical Smoke Ventilation', 'smoke-control-system'],
        ['Stairwell Smoke Vent', 'smoke-shaft-aov'],
        ['Corridor Smoke Ventilation', 'smoke-control-system'],
        ['Car Park Smoke Ventilation', 'smoke-control-system'],
        ['AOV for Apartment Blocks', 'smoke-shaft-aov'],
        ['AOV for High Rise', 'smoke-control-system'],
        ['Air Handling Unit Installation', 'air-handling-unit-installation'],
        ['AHU Controls Installation', 'air-handling-unit-installation'],
        ['Smoke Control Panel Service', 'aov-maintenance'],
        ['AOV Fault Finding', 'aov-maintenance'],
        ['AOV Battery Replacement', 'aov-maintenance'],
        ['Multi Site AOV Maintenance', 'aov-maintenance'],
        ['SE Controls AOV', 'aov-system'],
        ['WindowMaster AOV', 'aov-system'],
        ['Colt Smoke Vent', 'smoke-vent-system'],
        ['AOV System Design', 'aov-system'],
        ['AOV Commissioning', 'aov-system'],
        ['Smoke Control Engineers', 'smoke-control-system'],
        ['Life Safety Ventilation', 'smoke-control-system'],
        ['AOV Annual Service', 'aov-maintenance'],
        ['Roof Smoke Vent Installation', 'smoke-vent-system'],
        ['Lobby Smoke Ventilation', 'smoke-control-system'],
    ],
];

$existing = loadJsonData('keywords', []);
if (!is_array($existing)) {
    $existing = [];
}

$services = getServices();
$added = 0;
$skipped = 0;
$byServiceAfter = [];

foreach ($newByService as $service => $items) {
    if (!isset($services[$service])) {
        continue;
    }
    foreach ($items as $row) {
        $name = $row[0];
        $related = $row[1] ?? '';
        $slug = keywordSlug($name);
        if ($slug === '') {
            continue;
        }
        if (isset($existing[$slug])) {
            $skipped++;
            continue;
        }
        // Ensure related points to something sensible
        $relatedSlug = keywordSlug($related);
        if ($relatedSlug === '' || $relatedSlug === $slug) {
            $relatedSlug = $slug;
        }
        // Prefer related that already exists
        if (!isset($existing[$relatedSlug])) {
            // find first existing for this service
            foreach ($existing as $es => $em) {
                if (($em['service'] ?? '') === $service) {
                    $relatedSlug = $es;
                    break;
                }
            }
        }
        $existing[$slug] = [
            'name' => $name,
            'service' => $service,
            'related' => $relatedSlug,
        ];
        $added++;
    }
}

// Pad any service still under 30 with generated variants from core terms
foreach ($services as $service => $serviceName) {
    $count = 0;
    $firstSlug = null;
    foreach ($existing as $slug => $meta) {
        if (($meta['service'] ?? '') === $service) {
            $count++;
            if ($firstSlug === null) {
                $firstSlug = $slug;
            }
        }
    }
    $target = 35;
    if ($count >= 30) {
        continue;
    }
    $fillers = [
        "{$serviceName} Installation",
        "{$serviceName} Servicing",
        "{$serviceName} Maintenance",
        "{$serviceName} Repair",
        "{$serviceName} Certificate",
        "{$serviceName} Testing",
        "{$serviceName} Engineers",
        "{$serviceName} Near Me",
        "Commercial {$serviceName}",
        "Landlord {$serviceName}",
        "{$serviceName} Quote",
        "{$serviceName} Cost",
        "{$serviceName} North West",
        "{$serviceName} Manchester",
        "{$serviceName} Stockport",
        "{$serviceName} Compliance",
        "{$serviceName} Contract",
        "{$serviceName} Upgrade",
        "{$serviceName} Inspection",
        "{$serviceName} Specialists",
    ];
    foreach ($fillers as $name) {
        if ($count >= $target) {
            break;
        }
        $slug = keywordSlug($name);
        if (isset($existing[$slug])) {
            continue;
        }
        $existing[$slug] = [
            'name' => $name,
            'service' => $service,
            'related' => $firstSlug ?? $slug,
        ];
        $added++;
        $count++;
    }
}

// Count final
foreach ($existing as $slug => $meta) {
    $s = $meta['service'] ?? 'unknown';
    $byServiceAfter[$s] = ($byServiceAfter[$s] ?? 0) + 1;
}

saveJsonData('keywords', $existing);

echo "Keywords expand complete\n";
echo "Added: {$added}\n";
echo "Skipped existing: {$skipped}\n";
echo "Total keywords: " . count($existing) . "\n\n";
echo "Per service:\n";
ksort($byServiceAfter);
foreach ($byServiceAfter as $s => $c) {
    $flag = $c < 20 ? '  *** BELOW 20 ***' : ($c < 30 ? '  (under 30)' : '');
    echo "  {$s}: {$c}{$flag}\n";
}
