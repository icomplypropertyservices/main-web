<?php
/**
 * Per-keyword field notes for AOV pages.
 * Each entry is specific to that job. Do not interpolate a town name into a shared paragraph.
 *
 * @return array<string, array{heading:string,paragraphs:list<string>}>
 */
return [
    'ahu-commissioning' => [
        'heading' => 'Prove the sequence, then the fire interlock',
        'paragraphs' => [
            'An air-handling unit that spins up is not commissioned. We check motor rotation, damper end switches, frost and filter trips where they exist, and the written sequence the consultant actually specified.',
            'If the AHU is meant to stop or change mode on a fire signal, that input is tested with the fire-alarm cause-and-effect — not assumed because a relay is labelled “fire”. The handover is a sheet of as-left comments, not a badge.',
        ],
    ],
    'ahu-controls-installation' => [
        'heading' => 'Controls are the bit that fails safe',
        'paragraphs' => [
            'We fit the panel, sensors and damper actuators, then prove run, stop and the safety shutdown. Mechanical supply of the AHU casing stays with the mechanical contractor when that split is already on the job.',
            'Undocumented plant is common. We will redraw a practical I/O list if the old drawing is missing. A locked proprietary BMS network is called out rather than guessed.',
        ],
    ],
    'ahu-electrical-control' => [
        'heading' => 'When the plant “just stopped controlling itself”',
        'paragraphs' => [
            'Typical finds are a failed duct sensor, a drive that tripped and latched, a BMS point that no longer maps, or a fire interlock left in the operated state after a test nobody reset.',
            'We record setpoints before anyone “has a go” at the keypad. Replacement starters are matched to the motor plate, not to whatever frame is on the van.',
        ],
    ],
    'ahu-installation' => [
        'heading' => 'Electrical package around a new or replacement unit',
        'paragraphs' => [
            'Scope is power, isolators, control panel, field devices and the fire or smoke interlock. We do not pretend to manufacture the sheet-metal AHU.',
            'Changeovers are planned around the isolation window the FM team can actually give. Labelling is done so the next person can find the fire relay without lifting every lid.',
        ],
    ],
    'ahu-inverter-drive' => [
        'heading' => 'Drive choice starts at the motor plate',
        'paragraphs' => [
            'We read kilowatts, voltage, cable length and how the AHU panel or BMS tells the fan to start. Long motor cables and missing EMC practice are why some retrofitted drives trip the moment the fan is asked to ramp.',
            'Parameter lists are saved before a drive is swapped. After a board change we re-enter the ramp, the fire-stop input and the feedback signal, then run the fan against a closed and an open damper.',
        ],
    ],
    'ahu-panel' => [
        'heading' => 'The panel is not the whole air handler',
        'paragraphs' => [
            'Contactors, control transformers, overloads and the small relay that takes the fire signal are what we open the door to find. A dead HMI is often a 24 V supply, not a reason to replace the whole plant.',
            'If the same riser also holds an AOV panel, we keep the two systems labelled apart. Comfort ventilation and smoke control share a room more often than they share a cause-and-effect.',
        ],
    ],
    'air-handling-system-repair' => [
        'heading' => 'Repair means the failed part, not a new philosophy',
        'paragraphs' => [
            'We separate a seized damper motor, a burnt contactor and a sensor reading open-circuit. Each one stops the plant for a different reason and none of them is fixed by cycling the local isolator.',
            'If the repair touches a smoke or fire interlock, that circuit is re-proved before we leave. A fan that runs in hand but not in auto is a controls fault until shown otherwise.',
        ],
    ],
    'air-handling-unit-certification' => [
        'heading' => 'A visit record, not a scheme certificate we do not hold',
        'paragraphs' => [
            'What you get is a written note of what was inspected, what operated, and what was left defective. We do not issue a BAFE, FIRAS or other named accreditation for air handling, because we are not claiming one.',
            'Use the sheet for the O&M file and the insurer pack. Building control completion, if the job needs it, is a separate conversation with the designer and the approved inspector.',
        ],
    ],
    'air-handling-unit-installation' => [
        'heading' => 'Where our install scope starts and stops',
        'paragraphs' => [
            'Electrical installation, controls and life-safety interlocks are the work. Duct sizing, coil selection and crane lifts sit with the mechanical design.',
            'Before cables are pulled we want the control philosophy: what happens to the supply fan, the extract fan and any smoke damper when the fire alarm operates.',
        ],
    ],
    'air-handling-unit-maintenance' => [
        'heading' => 'A planned visit is filters, drives and the fire input',
        'paragraphs' => [
            'We look at drive cooling fans, contactor wear, sensor drift and whether the fire interlock still drops the plant out. Filter condition is noted; we do not sell a filter contract disguised as controls maintenance.',
            'Belts, bearings and coil cleaning are flagged for the mechanical contractor when that is what is actually worn. Mixing those tasks into an electrical visit is how defects get written down and never done.',
        ],
    ],
    'air-handling-unit-servicing' => [
        'heading' => 'Service is a repeatable list, written down',
        'paragraphs' => [
            'Each visit checks local isolators, panel condition, fault history if the controller stores it, and a functional run in auto. Findings go on the sheet with as-found and as-left.',
            'Intervals come from the manufacturer note or the FM specification. We will not invent a statutory “every AHU, every 90 days” rule on a web page.',
        ],
    ],
    'aov-actuator' => [
        'heading' => 'Force, stroke and hinge condition',
        'paragraphs' => [
            'A chain or spindle actuator is sized in newtons and millimetres of travel. A replacement that is “the same colour” can stall halfway if the stroke is short or the brackets have dropped.',
            'We also look at the vent itself. A twisted casement or a painted-shut hinge will cook a new actuator in a season. Current draw on opening is a better clue than the sticker on the body.',
        ],
    ],
    'aov-actuator-installation' => [
        'heading' => 'Mounting is the job, not just the wiring',
        'paragraphs' => [
            'Brackets have to take the opening force without tearing the vent frame. Cable bends at the chain, polarity into the panel output, and limit positions are set before anyone calls it finished.',
            '24 V actuators on a stair roof are the usual residential case. Façade louvres and heavier roof hatches may need a different body. We confirm that on the vent, not from a catalogue photo.',
        ],
    ],
    'aov-actuator-replacement' => [
        'heading' => 'Swap the actuator only after the vent will move',
        'paragraphs' => [
            'Failed actuators often follow a vent that has been dragging. We free the hinges, check the weather seal is not acting as a brake, then fit a unit with the correct stroke.',
            'Old 24 V bodies are frequently obsolete. If the panel output cannot drive the replacement, that is a panel conversation, not a reason to force a mismatch.',
        ],
    ],
    'aov-annual-service' => [
        'heading' => 'What an annual smoke-vent visit actually includes',
        'paragraphs' => [
            'Open and close from the panel and from the manual control, battery condition, a look at cables on the roof, and a note of vents that did not make full travel. Rain sensors are checked so they cannot hold a vent shut after a fire input.',
            'The gap between visits is whatever the fire strategy, insurer or manufacturer maintenance note asks for. We record the date. We do not stamp a fake annual certificate scheme.',
        ],
    ],
    'aov-battery-backup' => [
        'heading' => 'Standby is useless if the battery cannot take the vents',
        'paragraphs' => [
            'Smoke-control panels are meant to ride through a mains failure. We check charge voltage, battery date and whether the supply can still throw the connected actuators, not just light an LED.',
            'Power-supply design sits under BS EN 12101-10 where that standard applies to the kit. A swollen battery left in circuit is a fault, even if the panel has been reset quiet.',
        ],
    ],
    'aov-battery-replacement' => [
        'heading' => 'Date codes, not a reset',
        'paragraphs' => [
            'Most AOV panels use sealed lead-acid batteries in a matched pair or a single block, depending on the charger. We replace like for like on voltage and amp-hour, then confirm the panel leaves battery fault.',
            'A panel that returns to fault an hour later usually has a charger problem or an actuator short, not “a bad batch of batteries”. We do not keep swapping cells to hide that.',
        ],
    ],
    'aov-certification' => [
        'heading' => 'Paperwork you can file, with a clear limit',
        'paragraphs' => [
            'After testing we leave a record of which vents moved, which controls were used, battery condition and defects. Managing agents can put that in the building file.',
            'This is not a BAFE certificate, a fire-risk assessment or building-control sign-off. If the strategy needs a specialist smoke-control designer’s certificate, we say so instead of relabelling our visit sheet.',
        ],
    ],
    'aov-commissioning' => [
        'heading' => 'Commission against the cause-and-effect, not a demo open',
        'paragraphs' => [
            'We time the travel, confirm full stroke, operate the manual override, simulate the fire-alarm input and check comfort-mode rain closure does not block the fire open.',
            'If the fire strategy says the stair vent opens and the corridor vent stays shut, that is the test. Opening every window on site is not commissioning.',
        ],
    ],
    'aov-control-panel' => [
        'heading' => 'The panel is the logic, the vents are the proof',
        'paragraphs' => [
            'Outputs, fire inputs, manual switches and battery monitoring live here. A healthy display with a vent that does not move is still a failed system.',
            'Common UK residential kit includes SE Controls and WindowMaster panels, among others. We service them. We are not an appointed manufacturer partner and we do not use that phrase.',
        ],
    ],
    'aov-engineer' => [
        'heading' => 'Send the panel photo before the visit',
        'paragraphs' => [
            'Useful first information is the panel make, a photo of the fault LED, where the vents sit (stair head, corridor, shaft) and whether the fire alarm was altered recently.',
            'Roof work needs an agreed access method. We will not guess a cherry picker from a phone description of “a vent on the roof”.',
        ],
    ],
    'aov-fault-finding' => [
        'heading' => 'A fault LED is a starting point',
        'paragraphs' => [
            'We split supply faults, actuator over-current, open and short circuit on a vent line, rain-sensor lockout and a fire input that is stuck operated. Each one looks similar on a two-line display.',
            'If a recent fire-alarm panel change removed the interface, the AOV can sit in fault or never fire. That is diagnosed with the alarm engineer, not by condemning the vents.',
        ],
    ],
    'aov-fire-alarm-interface' => [
        'heading' => 'A volt-free contact is not a strategy',
        'paragraphs' => [
            'The fire alarm should present the input the smoke-control cause-and-effect describes — often a clean contact on fire in a specified zone, sometimes a different action per stair.',
            'We test by operating the alarm input the designer named. Linking “any device anywhere” because the cable was spare is how smoke gets pulled the wrong way. Polarity and fire-versus-fault terminals are checked on the drawing, then on the terminals.',
        ],
    ],
    'aov-for-apartment-blocks' => [
        'heading' => 'Stairs, lobbies and the leaseholder file',
        'paragraphs' => [
            'Purpose-built flats usually rely on a head-of-stair vent, sometimes with lobby or corridor vents into a shaft. The test has to match that building, not a generic “open all windows” script.',
            'Managing agents need a defect list a board can act on: seized roof vent, flat battery, missing manual point. We write those as separate lines because they are different orders.',
        ],
    ],
    'aov-for-high-rise' => [
        'heading' => 'Taller blocks: evidence, access, and no regulator costume',
        'paragraphs' => [
            'In England, residential buildings at or above 18 metres sit in the higher-risk building regime. Accountable persons ask whether the smoke control still does what the safety case says. We can test and record the vents. We are not the Building Safety Regulator and we do not write the safety case for you.',
            'Access is the practical constraint: roof edge, mast, closed stair during the test, and residents who need to know a vent will open and then shut again.',
        ],
    ],
    'aov-inspection' => [
        'heading' => 'Inspection without a repair hidden inside the price',
        'paragraphs' => [
            'An inspection reports condition: travel, batteries, controls, obvious cable damage, and whether the fire interface is even connected. Remedial prices come after, as separate lines.',
            'Buyers, freeholders and agents use this before they inherit a block. We do not turn an inspection into a sales survey for a full replacement unless the kit cannot be tested safely.',
        ],
    ],
    'aov-installation' => [
        'heading' => 'Install follows a fire strategy that already exists',
        'paragraphs' => [
            'We fit vents, actuators, the panel, manual controls and the alarm interface to the positions and free area the strategy sets out. We do not invent a smoke-control design on site to win the order.',
            'If there is no strategy, the honest next step is a designer or fire engineer, then an install. Wiring a roof light because the block “should have one” is not an installation method we will sign.',
        ],
    ],
    'aov-maintenance' => [
        'heading' => 'Maintenance is repeat tests plus the small failures',
        'paragraphs' => [
            'Expect a functional open and close, battery check, manual control, and a walk of the vents you can safely reach. Hinges, debris on roof lids and perished cable glands are the usual write-ups.',
            'We keep comfort ventilation (trickle open on a warm day) distinct from smoke mode. A rain sensor that closes the vent must still lose that argument when the fire input arrives.',
        ],
    ],
    'aov-maintenance-contract' => [
        'heading' => 'A contract is a diary and a defect log',
        'paragraphs' => [
            'Multi-block agents get a list of panels, battery dates and the last full travel of each stair vent. The visit frequency is the one in your specification, not a number we made up to look busy.',
            'Call-outs between visits are for faults on escape routes, priced as the contract says. A contract does not include rebuilding a shaft that was never finished.',
        ],
    ],
    'aov-near-me' => [
        'heading' => 'Where “near me” is honest',
        'paragraphs' => [
            'The yard is 17 Woodlands Park Road, Offerton, Stockport, SK2 5DE. Manchester is the short run. Burnley is a planned East Lancashire visit, typically under an hour and a quarter, not a same-hour pop-in.',
            'Because smoke vents are fire-protection work, we quote jobs elsewhere in the UK. Travel and nights are on that quote. The town directory lists places of 10,000 or more. A page is a job fact sheet, not a depot.',
        ],
    ],
    'aov-panel-installation' => [
        'heading' => 'Location, circuits, then cause-and-effect',
        'paragraphs' => [
            'Panels go where they can be reached on test day — usually a riser, stair lobby or plant cupboard agreed with the design. Field circuits for each vent and the manual switch are labelled to the core, not “vent 1” forever.',
            'Battery and mains monitoring are set before handover. A simulated fire input has to move the vents the strategy names. If the alarm panel is not ready, commissioning is incomplete and we say that.',
        ],
    ],
    'aov-panel-replacement' => [
        'heading' => 'Keep the field devices that still travel',
        'paragraphs' => [
            'Obsolete panels are replaced by mapping each output to a vent that has been proven to move. Actuators that stall are not “retained” just to make the quote smaller.',
            'The new panel still needs the fire-alarm interface and the manual override in the same places residents and the fire service expect. We do not relocate those without a reason written down.',
        ],
    ],
    'aov-repair' => [
        'heading' => 'Repair the fault that is actually present',
        'paragraphs' => [
            'Stuck shut, stuck open, and a panel in fault are three different repairs. A vent left open after a test may be a failed close limit or a manual switch left operated.',
            'Parts are matched to the installed brand where we can still get them. Where the body is obsolete we say so and price the replacement actuator or panel as its own line.',
        ],
    ],
    'aov-servicing' => [
        'heading' => 'Servicing is not a reset-and-leave',
        'paragraphs' => [
            'We operate vents, read battery health and write defects that failed the test. Clearing the panel buzzer without moving the stair vent is not a service.',
            'Logbook entries name the core and the vent. “AOV serviced” on its own is useless to the next engineer and to anyone reading the file after an incident.',
        ],
    ],
    'aov-system' => [
        'heading' => 'A system is vents, power, controls and the alarm',
        'paragraphs' => [
            'The pieces are the ventilator (roof, façade or shaft), the actuator, the control panel and batteries, the manual point, and the fire-alarm input. Miss one and you do not have smoke control.',
            'We describe what is on the building in those parts so a quote can be compared. A single line that says “AOV system” hides whether you have one stair hatch or twelve corridor louvres.',
        ],
    ],
    'aov-system-design' => [
        'heading' => 'We install to a design. We do not freelance the strategy.',
        'paragraphs' => [
            'Free area, location and what opens on which signal belong in the fire strategy, often with BS 9991 in mind for residential blocks and Approved Document B for the wider building rules.',
            'If you need that design, it comes from a competent fire engineer. Our design support is practical: can this roof take the vent, can the cable route reach, does the panel have the outputs. We will not sign off computational fluid dynamics we did not produce.',
        ],
    ],
    'aov-system-installation' => [
        'heading' => 'Installation order that avoids a half-live stair',
        'paragraphs' => [
            'Containment and cable first, panel next, actuators onto vents that already move by hand, then the alarm interface, then a witnessed test. Residents in an occupied block need the stair back and the vent closed at the end of the day.',
            'Weathering of a new roof upstand is part of the roof work, coordinated with whoever is cutting the opening. We do not leave a hole and call the electrics finished.',
        ],
    ],
    'aov-testing' => [
        'heading' => 'A test is a movement you can watch',
        'paragraphs' => [
            'Each vent called by the strategy is opened and closed. Time and whether it reached the end of travel are noted. The manual control is used as well as the panel.',
            'Testing on batteries, with mains isolated where it is safe to do so, shows whether standby is real. A test only on mains power misses the failure mode that matters after a fire has taken the supply.',
        ],
    ],
    'aov-testing-and-certification' => [
        'heading' => 'Test results and a record. That is the certificate.',
        'paragraphs' => [
            'You receive the list of devices tested, pass or fail, and defects. That is the document. It is not a third-party scheme certificate.',
            'Failed vents stay failed on the sheet. We do not turn a partial test into a pass because the panel display was green.',
        ],
    ],
    'apartment-block-smoke-control' => [
        'heading' => 'Common parts only, unless the strategy says otherwise',
        'paragraphs' => [
            'Residential smoke control is usually about the stair and the lobby or corridor approach, not the flats themselves. A vent inside a demise is a different problem and often not ours to open.',
            'Stay-put and evacuate strategies change what “working” means. We ask which one the building is using before we write the test method.',
        ],
    ],
    'automatic-opening-vent' => [
        'heading' => 'The name is literal',
        'paragraphs' => [
            'An automatic opening vent is a ventilator that opens on a fire signal without someone standing on the roof. It may be a roof hatch, a façade window or a louvre into a smoke shaft.',
            'Day-to-day it might also crack open for comfort if the panel allows that. Comfort mode is optional. Smoke mode is the reason the vent is there.',
        ],
    ],
    'automatic-opening-vent-installation' => [
        'heading' => 'New vents on existing blocks',
        'paragraphs' => [
            'Retrofits need a structural opening, weathering, power and a fire signal. The free area has to be the figure in the strategy, which is not the same as the hole size once blades and frames are in the way.',
            'Occupied buildings are phased so a stair is not left without its existing vent overnight, if it had one. If it never had one, we still do not cut a roof until the location is agreed.',
        ],
    ],
    'automatic-smoke-vent' => [
        'heading' => 'Smoke vent, not a trickle window',
        'paragraphs' => [
            'People search “automatic smoke vent” for the same stair and corridor kit. The test is still full opening on fire, close afterwards, and a battery that can do it without mains.',
            'A window restrictor fitted later for safety can stop the smoke stroke. We look for that before condemning the actuator.',
        ],
    ],
    'bs-9991' => [
        'heading' => 'BS 9991 is a design code, not our logo',
        'paragraphs' => [
            'BS 9991 is the usual UK code of practice people cite for fire safety in residential buildings, including smoke control of common stairs. We install and maintain to the strategy that was written against it. We are not a standards body and we do not “certify a building to BS 9991”.',
            'If the installed vents do not match the current version of the strategy, the finding is a defect list, not a quiet pass.',
        ],
    ],
    'bs-9991-smoke-control' => [
        'heading' => 'Stairs and the air that has to stay usable',
        'paragraphs' => [
            'In residential work the argument is usually whether the stair stays tenable long enough for the strategy you have chosen. The vent at the head of the stair is there for that, sometimes with a shaft serving lobbies.',
            'We can tell you whether the installed kit opens as designed. We cannot, from a maintenance visit, re-write the fire strategy or calculate a new shaft size.',
        ],
    ],
    'car-park-smoke-ventilation' => [
        'heading' => 'Most car parks are fans, not window AOVs',
        'paragraphs' => [
            'Enclosed car parks are often cleared with impulse or jet fans and a main extract, designed around BS 7346-7 and powered smoke fans to BS EN 12101-3. That is a different machine from a stair AOV.',
            'We will look at the electrical controls, fire-alarm start signals and panel faults. We will not quote a chain actuator to “solve” a car park that was designed around fans.',
        ],
    ],
    'colt-smoke-vent' => [
        'heading' => 'Colt kit, serviced without a partnership claim',
        'paragraphs' => [
            'Colt smoke ventilators and controls appear on older commercial and residential jobs. We test travel, controls and batteries and source replacements where the body is still supportable.',
            'We are not Colt. If a part is obsolete we say so. We do not put “approved Colt contractor” on a van or a page.',
        ],
    ],
    'commercial-ahu-maintenance' => [
        'heading' => 'Offices and retail plant rooms',
        'paragraphs' => [
            'Commercial AHU maintenance for us is controls, drives, interlocks and a written fault list. Filter changes and coil cleans are identified, then done by whoever holds the mechanical contract if that is not us on the day.',
            'Retail-park roof units and basement office plant fail differently: water in a control panel versus a drive full of dust. The visit notes say which.',
        ],
    ],
    'corridor-smoke-vent' => [
        'heading' => 'Corridor vents are easy to open at the wrong time',
        'paragraphs' => [
            'A corridor ventilator into a shaft or façade is there to pull smoke out of the access route, not into the stair. The cause-and-effect should say when it opens relative to the stair vent.',
            'We test that sequence. If both open together and the strategy says they should not, the finding is a logic fault, even if every actuator is healthy.',
        ],
    ],
    'corridor-smoke-ventilation' => [
        'heading' => 'Shaft, façade or nothing useful',
        'paragraphs' => [
            'Some corridors vent into a smoke shaft through a damper or an AOV at the shaft. Others use a façade louvre. The hardware and the test are not the same, and the access (ceiling void versus external elevation) changes the quote.',
            'Tell us which you have, with a photo of the device in the corridor ceiling or wall, before we price a day.',
        ],
    ],
    'emergency-smoke-ventilation' => [
        'heading' => 'Emergency means the stair is stuck shut or standing open',
        'paragraphs' => [
            'A vent locked closed on a live stair, or a roof hatch stuck open in rain, is the reactive job. Phone 07517806082 with the postcode and a panel photo. Attendance is priority where someone is free. It is not a promised 24-hour contract.',
            'We make safe what we can on the first visit and list parts if the actuator or panel is dead. Making safe is not the same as a full recommission.',
        ],
    ],
    'en-12101-smoke-vent' => [
        'heading' => 'Which part of EN 12101 you are actually quoting',
        'paragraphs' => [
            'BS EN 12101 is a family. Part 2 is natural smoke and heat exhaust ventilators. Part 10 is power supplies. Part 3 is powered smoke and heat exhaust fans. A stair AOV and a car-park fan are not interchangeable citations.',
            'We refer to the part that matches the kit on the wall. We do not claim the product CE or UKCA marking is ours.',
        ],
    ],
    'fire-rated-aov' => [
        'heading' => 'Fire resistance and free area are different questions',
        'paragraphs' => [
            'Some ventilators are also fire-resisting closures when shut. The test evidence belongs to the product as installed, including the frame and the seal. We do not invent a fire rating for a vent that has no plate and no paperwork.',
            'If the rating is unknown we write “not established on site” and you can take that to the designer. Guessing FD30 on a roof hatch helps nobody.',
        ],
    ],
    'high-rise-aov-maintenance' => [
        'heading' => 'Height changes the method, not the pass mark',
        'paragraphs' => [
            'The vent still has to open fully and close. What changes is access, wind on an exposed roof, and the number of people who must know the stair is being tested.',
            'Higher-risk residential buildings need the record for the safety case. We supply the test record. We do not register the building or act as the principal accountable person.',
        ],
    ],
    'hvac-electrical-installation' => [
        'heading' => 'HVAC electrics that meet a fire strategy',
        'paragraphs' => [
            'This is supplies to fans and controls, isolators, and the interlock that stops a comfort fan from fighting a smoke path. It is not a full mechanical install of ductwork.',
            'If the specification calls for a particular fire-rated cable on the smoke-control circuit, that is priced from the spec, not swapped for a general power cable because it was on the drum.',
        ],
    ],
    'industrial-air-handling-unit' => [
        'heading' => 'Industrial units: dirt, longer cables, bigger drives',
        'paragraphs' => [
            'Factory AHUs collect conductive dust in drives and corrosion on roof isolators. Fault-finding starts there, then at the fire or gas interlock if the plant is interlocked to a process shutdown.',
            'We will not treat an industrial extract that is part of a LEV or process system as if it were a residential stair vent. Tell us what the fan is extracting.',
        ],
    ],
    'life-safety-ventilation' => [
        'heading' => 'Life safety means the escape route, not the office temperature',
        'paragraphs' => [
            'Life-safety ventilation is the smoke-control kit: AOVs, smoke shafts, powered smoke extract where that is the design. Comfort cooling can share a plant room and still be a different system.',
            'Quotes separate the two. A failed office AHU is urgent for the tenant. A failed stair vent is urgent for the escape route. They do not jump the same queue.',
        ],
    ],
    'lobby-smoke-ventilation' => [
        'heading' => 'Lobbies are small volumes with a specific job',
        'paragraphs' => [
            'A lobby ventilator or damper is often the route from the flat entrance lobby into a smoke shaft. It may be a small louvre high on the wall, easy to paint over or to block with a new ceiling.',
            'We check it is still the device on the drawing and that it opens when that lobby is the one in alarm, not when the stair vent opens.',
        ],
    ],
    'louvre-aov' => [
        'heading' => 'Free area is the holes, not the structural opening',
        'paragraphs' => [
            'Louvre blades, frames and bird mesh reduce geometric free area. A 1 m² hole with blades is not a 1 m² ventilator. The product data sheet has the figure. The wall opening does not.',
            'Actuators on louvres fail at the blade linkage as often as at the motor. We watch a full open before we call the linkage sound.',
        ],
    ],
    'mechanical-smoke-extract' => [
        'heading' => 'Fans with a duty, a standby and a fire start',
        'paragraphs' => [
            'Mechanical smoke extract uses powered fans, usually cited to BS EN 12101-3, with a fire-alarm start and often a duty/standby arrangement. It is not repaired by fitting a window actuator.',
            'Our scope is the controls, supplies, changeover and the proof that the fan they named actually runs on the fire signal. Impeller and duct repairs are identified if they are why it will not move air.',
        ],
    ],
    'mechanical-smoke-ventilation' => [
        'heading' => 'Powered systems need a power story',
        'paragraphs' => [
            'We check the supply to the fans, the panel, any inverter, and the standby arrangement the design asked for. A fan that only runs in hand is not a smoke-control system.',
            'Natural AOVs elsewhere in the same building stay on their own test sheet so a passed roof vent cannot hide a dead extract fan.',
        ],
    ],
    'multi-site-aov-maintenance' => [
        'heading' => 'One method across the portfolio',
        'paragraphs' => [
            'Agents with several blocks get the same test steps and a sheet that compares battery age and failed vents by address. Mixing “engineer A’s notebook” and “engineer B’s memory” is how a seized vent survives three visits.',
            'Travel is batched. A nationwide fire-protection quote can include sites outside the North West; the day rate and travel are written down rather than implied by a local town page.',
        ],
    ],
    'natural-smoke-ventilation' => [
        'heading' => 'Natural means buoyancy and wind, not a fan',
        'paragraphs' => [
            'Natural smoke ventilation uses ventilators that open to let buoyancy and wind move smoke. The product side is typically BS EN 12101-2. If the design needed fans, this is the wrong page.',
            'We maintain and install the opening vents and their controls. We do not offer a CFD study as a maintenance extra.',
        ],
    ],
    'roof-aov' => [
        'heading' => 'Roof hatches: weather, hinges, wind',
        'paragraphs' => [
            'Head-of-stair roof vents take rain, bird mess and wind. Lids that slam or that will not close are a hinge and actuator problem, and a leak problem for the top landing.',
            'We look at the upstand, the closer, the chain or spindle, and the cable crossing the roof. A perished gland full of water is a classic fault that presents as “actuator failed”.',
        ],
    ],
    'roof-smoke-vent-installation' => [
        'heading' => 'Cutting a roof is a building job plus a controls job',
        'paragraphs' => [
            'Location comes from the strategy (almost always the head of the protected stair). The opening, kerb and weathering have to be weathertight before the actuator is the interesting part.',
            'Power and the fire signal still have to reach it. A vent with no monitored supply is a skylight.',
        ],
    ],
    'se-controls-aov' => [
        'heading' => 'SE Controls panels and actuators, without a franchise claim',
        'paragraphs' => [
            'SE Controls kit is common on UK residential stairs: chain actuators and panels in the OS family and related ranges. We fault-find outputs, batteries and rain-sensor behaviour on that equipment.',
            'We buy or fit parts as a contractor. We are not SE Controls and we do not claim approved-installer status. Obsolete ranges are identified from the label, not from a guess at the logo colour.',
        ],
    ],
    'smoke-control-engineers' => [
        'heading' => 'What you are hiring',
        'paragraphs' => [
            'A smoke-control visit from us is a tradesperson who will open the panel, move the vents and write down what failed. It is not a fire-engineer’s design appointment and not a risk assessment.',
            'If the question is “is this shaft big enough”, you need the designer. If the question is “why is the stair vent in fault”, that is this job.',
        ],
    ],
    'smoke-control-panel-service' => [
        'heading' => 'Panel service with the lid off',
        'paragraphs' => [
            'We read the fault log if it has one, check fuses and battery charge, operate each output and confirm the manual switch does what the fascia says. Sticky buttons and water in the back box are written up.',
            'A service that only presses silence is how a dead output survives until the stair is full of smoke.',
        ],
    ],
    'smoke-control-system' => [
        'heading' => 'Name the system before you buy a part',
        'paragraphs' => [
            'Smoke control might be a single roof AOV, a shaft with dampers, or powered extract. The phone call should establish which, because the spares and the test are different.',
            'We ask for the fire strategy page that lists the devices, or a photo set if the strategy is missing. Missing paperwork is reported. It is not papered over with a pass.',
        ],
    ],
    'smoke-control-system-maintenance' => [
        'heading' => 'Maintenance across mixed kit on one site',
        'paragraphs' => [
            'A block can have a stair AOV and a mechanical smoke fan in a basement. Both get a line on the sheet. Passing one does not pass the other.',
            'Defects are prioritised by whether the escape route still has its ventilator, not by which part is easiest to order.',
        ],
    ],
    'smoke-curtain-maintenance' => [
        'heading' => 'Curtains descend. They are not vents.',
        'paragraphs' => [
            'Smoke curtains (often cited to BS EN 12101-1) are fabric barriers that drop to hold smoke. Maintenance is a controlled descent, a look at the fabric and side guides, and the motor or gravity fail-safe. It is not an actuator stroke test.',
            'We will include curtains when they are on the same smoke-control system as the vents. We will not call a curtain an AOV on the certificate.',
        ],
    ],
    'smoke-shaft-aov' => [
        'heading' => 'The shaft only works if the openings do',
        'paragraphs' => [
            'A smoke shaft is a vertical path. AOVs or dampers at the lobby and a ventilator at the head have to open in the combination the design states. A head vent that opens while every lobby damper stays shut is not “close enough”.',
            'Access to the shaft head is often the whole job. We price that honestly rather than testing only the lobby you can reach from a step ladder.',
        ],
    ],
    'smoke-shaft-maintenance' => [
        'heading' => 'Shafts collect doors, debris and lost keys',
        'paragraphs' => [
            'Maintenance is operating each inlet and the outlet, checking nothing is stored in the shaft, and confirming doors or dampers are not wedged. Construction waste left in a shaft is a defect even if the actuator is new.',
            'We do not enter a shaft that has no safe access. The report says “not accessed” rather than “satisfactory”.',
        ],
    ],
    'smoke-vent-installation' => [
        'heading' => 'Installation tied to a location and a free area',
        'paragraphs' => [
            'The vent type (roof, window, louvre) is chosen from the strategy and the wall or roof you actually have. Actuator, panel and alarm interface are part of the same install, not a later extra we hope you remember.',
            'Commissioning is included in the sense that an untested vent is not installed. The record lists travel and the fire input used.',
        ],
    ],
    'smoke-vent-system' => [
        'heading' => 'System boundaries',
        'paragraphs' => [
            'A smoke-vent system includes every ventilator the cause-and-effect names, plus power and controls. Adding a comfort window on the same panel does not make that window a smoke vent unless the strategy says it is.',
            'We map what is connected before a maintenance contract starts, so you are not paying to “service” a vent that is only a skylight.',
        ],
    ],
    'stairwell-smoke-vent' => [
        'heading' => 'The head of the stair is the usual residential vent',
        'paragraphs' => [
            'In blocks designed around a protected stair, the ventilator at the top is the one that has to open. Corridor and lobby devices are additional, not a substitute, unless the strategy is explicit.',
            'We test it from the manual point at stair level as well as from the panel, because that is how someone on the landing will use it.',
        ],
    ],
    'window-aov' => [
        'heading' => 'A window that is also a smoke vent',
        'paragraphs' => [
            'Façade windows used as AOVs have to reach the smoke stroke, which may fight a later restrictor, a curtain or a secondary glazing unit. We look for those before replacing the chain.',
            'Child-safety restrictors and smoke control have to be reconciled in the design, not by whoever visited last leaving the vent half-latched.',
        ],
    ],
    'windowmaster-aov' => [
        'heading' => 'WindowMaster controls, serviced as installed kit',
        'paragraphs' => [
            'WindowMaster actuators and controllers turn up on façades and roofs. We test the controller outputs, the chain travel and the fire input on the equipment that is labelled as theirs.',
            'Same limit as other brands: we are not WindowMaster and we do not claim a partnership. If the controller is a generation they no longer support, you get that in writing with the options that remain.',
        ],
    ],
];
