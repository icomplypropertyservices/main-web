<?php
/**
 * Curated product lines. Missing slugs fall back to service ranges in manufacturer-areas.php.
 * Each row: [line name, blurb]. Labels are prefixed with the brand when the line name does not already include it.
 *
 * @return array<string, list<array{0:string,1:string}>>
 */
return [
    'se-controls' => [
        ['OS2', 'OS2 smoke-control panels for stair and lobby vents specified as SE Controls.'],
        ['SHEV', 'SHEV control and override equipment used with SE Controls smoke ventilation.'],
        ['NVLogiQ', 'NVLogiQ natural ventilation controls where the building is not a simple one-shot stair vent.'],
    ],
    'nuaire' => [
        ['XBOX', 'XBOX extract and supply fans on residential and commercial air systems.'],
        ['MRXBOX', 'MRXBOX heat-recovery units where the specification names that Nuaire range.'],
        ['Smoke extract', 'Smoke extract fans and ancillaries kept separate from comfort ventilation.'],
    ],
    'brooks' => [
        ['Smoke vents', 'Automatic smoke vents supplied and serviced as Brooks equipment.'],
        ['Actuators', 'Actuators and link arms matched to the Brooks vent already on the roof or stair.'],
        ['Control panels', 'Brooks-style smoke-control panels, override points and battery checks.'],
    ],
    'ventilux' => [
        ['Roof hatches', 'Roof hatch AOVs for stair discharge and smoke shafts.'],
        ['Stairwell vents', 'Wall and roof stair vents with the Ventilux-style package used on residential cores.'],
        ['Control panels', 'Control panels, rain sensors and fire-alarm interface for those vents.'],
    ],
    'geze' => [
        ['Slimchain', 'Slimchain chain drives for facade and smoke vents.'],
        ['Powerchain', 'Powerchain drives where the vent load is above a slim chain.'],
        ['E 250', 'E 250 chain drives and the matching Geze control gear.'],
    ],
    'dh-mechatronic' => [
        ['CDC', 'CDC chain drives for smoke and comfort vents.'],
        ['ZA', 'ZA rack-and-pinion drives for heavier vents and rooflights.'],
        ['DXD', 'DXD control panels and override stations for D+H systems.'],
    ],
    'd-h-mechatronic' => [
        ['CDC', 'CDC chain drives for smoke and comfort vents.'],
        ['ZA', 'ZA rack-and-pinion drives for heavier vents and rooflights.'],
        ['DXD', 'DXD control panels and override stations for D+H systems.'],
    ],
    'trox' => [
        ['EK-JS', 'EK-JS smoke-control dampers where TROX is named on the fire strategy.'],
        ['FKRS-EU', 'FKRS-EU fire dampers and the inspection access they need.'],
        ['Smoke extract', 'TROX smoke-extract components kept distinct from everyday air handling.'],
    ],
    'colt' => [
        ['Seefire', 'Seefire smoke ventilators for roofs and facades.'],
        ['Kameleon', 'Kameleon natural ventilators where day-to-day airflow and smoke venting share a unit.'],
        ['Smoke shafts', 'Shaft and lobby ventilation coordinated with the Colt equipment already installed.'],
    ],
    'smoke-control' => [
        ['Stair AOVs', 'Stair and lobby automatic opening vents.'],
        ['Smoke shafts', 'Shaft systems and the fans or dampers that serve them.'],
        ['Control panels', 'Control panels, overrides and fire-alarm cause-and-effect.'],
    ],
    'assa-abloy' => [
        ['Aperio', 'Aperio wireless locks where the door schedule names ASSA ABLOY.'],
        ['Door closers', 'Door closers and hold-open devices that must release with the fire alarm.'],
        ['Panic hardware', 'Panic and emergency exit hardware surveyed with the escape route.'],
    ],
    'group-scs' => [
        ['Smoke-control panels', 'Group SCS smoke-control panels and power supplies.'],
        ['Interfaces', 'Fire-alarm and BMS interfaces on those panels.'],
        ['Actuator kits', 'Actuator kits and fixings used on the vents the panel drives.'],
    ],
    'windowmaster' => [
        ['MotorLink', 'MotorLink drives and controls for facade ventilation.'],
        ['WMX', 'WMX control equipment where WindowMaster is the specified system.'],
        ['Smoke vents', 'Smoke-vent modes kept separate from everyday comfort opening.'],
    ],
    'simon-rwa' => [
        ['Chain drives', 'Simon RWA chain drives for smoke and day-to-day vents.'],
        ['Rack drives', 'Rack drives for heavier roof vents.'],
        ['Control panels', 'RWA control panels, overrides and battery standby.'],
    ],
    'bilco' => [
        ['Roof hatches', 'Bilco roof hatches used as access or smoke outlets.'],
        ['Smoke vents', 'Smoke-vent hatches and the actuators fitted to them.'],
        ['Safety rails', 'Hatch safety rails and the access needed for a proper test.'],
    ],
    'cambric' => [
        ['Actuators', 'Cambric actuators and link sets for smoke vents.'],
        ['Control interfaces', 'Control interfaces, fuses and batteries for Cambric panels.'],
        ['Smoke vents', 'Vent assemblies commissioned against the residential fire strategy.'],
    ],
    'kingspan-air' => [
        ['Smoke shafts', 'Kingspan Air smoke shafts and the builders\' work around them.'],
        ['Roof vents', 'Roof vents and terminals that serve those shafts.'],
        ['Controls', 'Control panels and sensors that open and close the system on test.'],
    ],
    'flaktgroup' => [
        ['Smoke extract fans', 'FlaktGroup smoke extract fans and their isolators.'],
        ['Car park fans', 'Car park impulse and extract fans where smoke clearance is part of the design.'],
        ['Controls', 'Fan controls, run-on and fire-alarm interface.'],
    ],
    'systemair' => [
        ['AXC', 'AXC fans used for smoke or comfort extract.'],
        ['Smoke extract', 'Smoke extract fans kept on the life-safety side of the specification.'],
        ['Roof fans', 'Roof fans and the curbs, isolators and controls that belong with them.'],
    ],
    'came' => [
        ['Gard GT', 'Gard GT barriers for staff and visitor car parks. CAME is our barriers partner.'],
        ['Gard 4', 'Gard 4 barrier operators where the boom and housing follow that CAME range.'],
        ['BXV', 'BXV sliding-gate operators for yards and residential gates.'],
        ['Krono', 'Krono industrial sliding operators for heavier gates.'],
        ['Frog', 'Frog underground swing operators where the leaf must stay clear.'],
        ['Fast', 'Fast swing-gate operators for lighter leaves and shorter runs.'],
    ],
    'kentec' => [
        ['Syncro', 'Syncro addressable panels for commercial and multi-loop sites.'],
        ['Taktis', 'Taktis analogue addressable systems where that Kentec range is specified.'],
        ['Sigma XT', 'Sigma XT extinguishing and compact panels for plant rooms and smaller risks.'],
    ],
    'advanced-electronics' => [
        ['MxPro 5', 'MxPro 5 addressable panels and the loop devices that sit with them.'],
        ['Axis EN', 'Axis EN panels where the site is already on that Advanced range.'],
        ['Loop devices', 'Call points, sounders and interfaces matched to the Advanced protocol on site.'],
    ],
    'c-tec' => [
        ['CFP', 'CFP conventional panels for smaller commercial risks.'],
        ['XFP', 'XFP addressable panels for one- and two-loop buildings.'],
        ['ZFP', 'ZFP networked panels where the site has outgrown a single XFP.'],
        ['CAST', 'CAST protocol devices where the loop is specified as C-TEC CAST.'],
    ],
    'apollo' => [
        ['XP95', 'XP95 detectors and bases on existing Apollo loops.'],
        ['Discovery', 'Discovery devices where drift compensation and the Discovery protocol are required.'],
        ['Soteria', 'Soteria devices for newer Apollo specifications.'],
    ],
    'hochiki' => [
        ['ESP', 'ESP addressable devices and bases.'],
        ['CDX', 'CDX conventional devices where the panel is not addressable.'],
        ['Bases and isolators', 'Bases, isolators and call points matched to the Hochiki loop.'],
    ],
    'paxton' => [
        ['Net2', 'Net2 controllers, readers and tokens for card and fob doors.'],
        ['Paxton10', 'Paxton10 where the site wants the newer Paxton platform.'],
        ['Entry', 'Paxton Entry door stations and monitors, including sites that also have barriers.'],
    ],
    'hid-global' => [
        ['iCLASS SE', 'iCLASS SE credentials and readers.'],
        ['Signo', 'Signo readers where the specification has moved off older HID heads.'],
        ['Credentials', 'Cards, fobs and mobile credentials programmed to the reader family on site.'],
    ],
    'salto-systems' => [
        ['XS4', 'XS4 electronic locks and escutcheons.'],
        ['KS', 'KS keys and cylinders for Salto stand-alone and online doors.'],
        ['Wall readers', 'Wall readers and the Salto software that issues the users.'],
    ],
    'tdsi' => [
        ['MICROgarde', 'MICROgarde controllers and readers.'],
        ['GARDiS', 'GARDiS where the site is on the newer TDSi platform.'],
        ['EXCEL4', 'EXCEL4 controllers still found on older TDSi estates.'],
    ],
    'courtney-thorne' => [
        ['Aidcall', 'Aidcall nurse call points, displays and power supplies.'],
        ['Touchsafe', 'Touchsafe wireless points where a hard wire is not practical.'],
        ['Pear leads', 'Pear leads, pull cords and reset parts for occupied rooms.'],
    ],
    'quantec' => [
        ['Quantec addressable', 'Quantec addressable call points and displays.'],
        ['Pear leads', 'Pear leads and call leads for bedrooms and wet rooms.'],
        ['Panel batteries', 'Panel batteries and power supplies on planned visits.'],
    ],
    'intercall' => [
        ['600', 'Intercall 600 equipment still in use on older homes.'],
        ['700', 'Intercall 700 call points and displays.'],
        ['Pear leads', 'Pear leads and pull-cord spares matched to the Intercall system on site.'],
    ],
    'texecom' => [
        ['Premier Elite', 'Premier Elite panels, keypads and expanders.'],
        ['Veritas', 'Veritas panels where the home or shop is on that Texecom range.'],
        ['Capture', 'Capture detectors and contacts for new zones.'],
    ],
    'hikvision' => [
        ['AcuSense', 'AcuSense cameras and NVRs for sites that want that analytics tier.'],
        ['ColorVu', 'ColorVu cameras where colour images after dark are the brief.'],
        ['DeepinView', 'DeepinView cameras where the specification names that Hikvision tier.'],
    ],
    'worcester-bosch' => [
        ['Greenstar', 'Greenstar combi and system boilers.'],
        ['Greenstar 4000', 'Greenstar 4000 where that is the current Worcester range on the wall.'],
        ['Controls', 'Worcester controls and the flue, filter and service parts that belong with the boiler.'],
    ],
    'vaillant' => [
        ['ecoTEC', 'ecoTEC boilers and the flue systems specified with them.'],
        ['ecoFIT', 'ecoFIT where the property is on that Vaillant range.'],
        ['Controls', 'Vaillant controls and service parts. Quotes are after we see the appliance.'],
    ],
    'hager' => [
        ['Invicta', 'Invicta distribution boards and protective devices.'],
        ['Design 30', 'Design 30 consumer units for dwellings.'],
        ['EV distribution', 'Hager distribution used beside EV charge points, quoted after the supply is known.'],
    ],
    'wylex' => [
        ['Metal consumer units', 'Wylex metal consumer units and the devices that fit them.'],
        ['NH boards', 'NH-style boards still found on older Wylex installs.'],
        ['Protection devices', 'MCBs, RCBOs and SPDs matched to the Wylex board on site.'],
    ],
    'myenergi' => [
        ['zappi', 'zappi EV chargers.'],
        ['eddi', 'eddi diverters where solar or surplus energy is part of the brief.'],
        ['harvi', 'harvi energy monitors used with the myenergi devices.'],
    ],
    'videx' => [
        ['VX', 'VX door-entry panels and handsets.'],
        ['IP', 'Videx IP systems where the building can support network cabling.'],
        ['GSM', 'GSM door entry and the SIM arrangements that go with it.'],
    ],
];
