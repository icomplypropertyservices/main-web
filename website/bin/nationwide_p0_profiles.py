"""Job-specific substance for the W1a nationwide P0 job hubs.

Each profile carries what differs between jobs in practice: what the customer
sees, the parts that usually turn out to be involved, and the checks the
engineer makes on that equipment. Shared wording lives in the builder.
"""

P = {}

# ---------------------------------------------------------------- AOV / smoke
P["aov-actuator-repair"] = dict(
    lede="Smoke vent actuators that hum, stall half-way or no longer pull the vent tight",
    symptoms="An actuator fault usually shows as a vent that opens a few centimetres and stops, a chain that runs out but leaves the leaf hanging, or a spindle motor that clicks without moving. On older stair heads the first sign is often a draught: the vent never quite closes, the latch is not made and the panel still reports the vent as shut because the limit switch was set years ago.",
    parts="The parts we see most are chain actuators with a stretched or kinked chain, linear spindle drives with stripped nuts, brackets that have pulled out of soft window frames, and flying leads chafed where they pass through the frame. Sometimes the actuator is fine and the cable from the control panel has a high-resistance joint in a junction box above the ceiling.",
    checks="We measure the current the actuator draws at the start of travel and compare it with the drive's rating, run it by hand off the bracket to prove whether the motor or the window is binding, and check the stroke against the opening the strategy needs. A replacement drive is matched on force, stroke, voltage and the connection type the panel expects, not on the nearest catalogue lookalike.",
    faq=("Can you fit a different make of actuator to my vent?", "Often yes, provided force, stroke, voltage and the panel interface match. We show you the alternative and its data sheet before anything is fitted."),
)
P["aov-call-out"] = dict(
    lede="Same-day attendance when a stair or lobby smoke vent is stuck open, stuck shut or in fault",
    symptoms="Call-outs usually start with a resident reporting rain coming in through an open roof vent, a panel buzzer that will not silence, or a fire alarm activation after which the vents never closed. In winter a vent stuck open over a stair can make a block cold within an hour, so the first job is to make the building weather-tight safely.",
    parts="On the day the cause is most often a tripped supply, a flat standby battery, a fire-alarm input that is still latched, a break-glass that has been pressed and not reset, or a drive that has jammed against debris on the frame. Less often it is a failed control board after a power surge.",
    checks="We read the panel status and the fire-alarm interface first, reset only what is safe to reset, close the vent under power where we can, and only lash it shut as a last resort with the duty holder told in writing. Before leaving we check the vent will still open on a fire signal and on the manual open switch.",
    faq=("What happens if the vent cannot be fixed on the first visit?", "We make the building secure, tell the responsible person exactly what state the vent is left in, and quote the follow-up repair from the measurements already taken."),
)
P["aov-panel-repair"] = dict(
    lede="Repairs to AOV and smoke control panels that show fault, will not reset or have lost their batteries",
    symptoms="Panel faults show up as a steady fault light, a buzzer that comes back after every reset, a dead display, or vents that respond to the open switch but not to the fire alarm. Some panels report a generic fault for a mains failure, a battery fault, an open-circuit drive line and a missing fire input alike, so the light alone tells you little.",
    parts="Typical findings are standby batteries past their life, a blown fuse on one drive output, a failed charger, corroded terminals in a damp riser, or a weather or rain sensor that has dropped out and put the panel into a fault state. Older boards may need a like-for-like exchange, and some are no longer supported by the maker.",
    checks="We read the panel's own fault indications, measure battery voltage under load, test each drive output with the vent connected, and prove the fire-alarm input by a witnessed signal from the fire panel. If a board has to be replaced we confirm the replacement's outputs and inputs suit the drives already fitted.",
    faq=("My panel is obsolete. Do I need a whole new system?", "Not always. A modern panel can usually drive existing actuators if their voltage and wiring suit. We check that first and set out both routes in writing."),
)
P["aov-replacement"] = dict(
    lede="Replacing worn or unsupported AOV systems vent by vent without leaving the stair unprotected",
    symptoms="Replacement is usually asked for when parts can no longer be sourced, repeated repairs have not held, a fire risk assessment has flagged the system, or a refurbishment has changed the stair and the old vent no longer gives the free area the strategy needs.",
    parts="A replacement may cover the vent leaf or window, the actuator, the control panel, the break-glass and open/close switches, the weather sensor and the cabling back to the fire alarm. Frames and kerbs on flat roofs are often in worse condition than the vent itself and need looking at before anything is ordered.",
    checks="We survey the existing free area, the opening angle, the drive force needed for the leaf size and wind load, the routes for new cable and the fire-alarm interface. The changeover is phased so the stair keeps a working vent, or the responsible person agrees interim measures in writing before any vent is out of service.",
    faq=("Will the stair be without smoke ventilation during the work?", "We plan to avoid that. Where a short gap is unavoidable it is agreed with the responsible person in advance, with interim measures recorded."),
)
P["aov-service"] = dict(
    lede="Planned servicing of smoke vents, actuators and AOV panels to keep them ready for a fire",
    symptoms="Most clients book a service because the fire risk assessment or insurer asks for one, or because nobody has a record of the vents being opened since handover. Unserviced vents are often found painted shut, held by a forgotten security bolt, or with batteries several years out of date.",
    parts="A service visit covers the panel, standby batteries, each vent and its drive, the manual open and close switches, the fire-alarm trigger, and any weather or wind sensor. Consumables such as batteries are replaced when they fail their load test, with the client told first.",
    checks="Each vent is opened from the fire-alarm signal and from the manual switch, timed, and checked for full opening and a clean close. Batteries are load-tested, terminals and fuses inspected, and the result for every vent is written up so the next visit can see what changed.",
    faq=("How often should smoke vents be serviced?", "Most buildings work to a weekly or monthly user test plus at least an annual service by a competent engineer. Your fire risk assessment may ask for more."),
)
P["colt-aov-service"] = dict(
    lede="Servicing Colt smoke vents, louvres and control equipment already installed in your building",
    symptoms="Colt kit is common on commercial roofs, warehouses and older stair heads, and the questions we get are usually about louvred ventilators that no longer close evenly, roof vents whose pneumatic or electric drives are sluggish, and control panels whose labels no longer match the zones on site.",
    parts="Typical service items are louvre blade linkages and bushes, actuators and their mounting points, seals, pneumatic lines and fittings where fitted, control panel batteries and the fire-alarm interface relay. Some older Colt panels are no longer current, which we tell you plainly.",
    checks="We operate each ventilator from the fire signal and from manual control, check blade travel and seal, look for corrosion on roof-mounted units, and test standby supply. Where a Colt part is obsolete we say so and set out a compatible alternative in writing before it is fitted.",
    faq=("Are you a Colt-approved contractor?", "No. We are an independent contractor who services existing Colt equipment. The model is confirmed on site and any substitute part is agreed with you first."),
)
P["d-h-aov-repair"] = dict(
    lede="Fault-finding and repair on D+H smoke vent drives and control units",
    symptoms="D+H chain and rack drives are fitted on a large share of UK stair heads and atrium windows. The common complaints are drives that stop short, control units in permanent fault after a power cut, and vents that open on test but not on a fire signal because the interface wiring was altered during later works.",
    parts="We regularly deal with D+H chain drives with worn chain links, rack drives with failed limit settings, brackets, control panels with failed batteries or charger stages, and keyswitch or break-glass stations. Tandem drives on large windows need both units checked, because one weak drive overloads the other.",
    checks="Drive current and travel are checked against the drive's data, tandem drives are proven to run in step, the control unit's line monitoring is checked so a cut cable shows as a fault, and the fire input is witnessed end to end. Any substitute drive is matched on force, stroke and connection.",
    faq=("Can a failed D+H drive be swapped for another brand?", "Sometimes, if force, stroke, speed and the control unit's line monitoring all match. We confirm that before ordering and write it down."),
)
P["se-controls-aov-repair"] = dict(
    lede="Repairs to SE Controls actuators, OS2 controllers and smoke shaft systems",
    symptoms="SE Controls kit is common in newer residential blocks with smoke shafts and in schools. Typical repair calls are a controller in fault after a fire alarm test, a shaft vent that opens but the lobby vent on the fire floor does not, or a natural ventilation window that will no longer close against its seal.",
    parts="Repairs usually involve chain or linear actuators, OS2-style controllers and their network wiring, smoke shaft head vents and lobby dampers, firefighter override switches and the interface to the fire panel. Damaged cabling where fit-out contractors have drilled through risers is also common.",
    checks="We check the controller event log, prove each actuator's travel and limit, test the cause-and-effect so the right lobby opens with the shaft head on each floor, and prove the firefighter override. Replacements are matched to the controller's network and monitoring requirements.",
    faq=("Our smoke shaft opens the wrong floor. Is that a repair or a redesign?", "Usually a repair: an addressing or interface fault. We trace it on site and only suggest design changes if the as-built strategy calls for it."),
)
P["se-controls-aov-service"] = dict(
    lede="Planned servicing for SE Controls smoke control and natural ventilation systems",
    symptoms="Blocks with SE Controls systems often run a mix of smoke-control vents and everyday ventilation windows on the same controllers. Service visits pick up windows left in comfort-vent positions, batteries that were never changed after handover, and weather sensors closing windows that should stay open on a fire signal.",
    parts="We service controllers and their standby batteries, chain and linear actuators, smoke shaft vents, lobby vents and dampers, weather and rain sensors, firefighter switches and the fire-alarm interface. Comfort-ventilation settings are recorded so we leave them as the building manager set them.",
    checks="Each floor is triggered in turn, timed, and checked to see that the lobby and shaft both open and that smoke-mode overrides comfort mode and weather closing. Battery standby is tested and the event log reviewed for faults that were reset and forgotten.",
    faq=("Do you reset our comfort ventilation settings?", "No. We record them before testing and put them back afterwards unless you ask us to change them."),
)
P["smoke-control-system-installation"] = dict(
    lede="Installing natural or mechanical smoke control to the building's fire strategy",
    symptoms="Installations come from new-build and conversion projects, refurbishments that have changed a stair, and fire risk assessments that found a common stair without the ventilation its height needs. The fire strategy or the fire engineer's design is the starting point; without it we help you get one rather than guess.",
    parts="A system can include roof or façade vents, smoke shafts with head vents and lobby dampers, actuators, controllers, firefighter switches, weather sensors, fire-rated cabling, standby power and, for mechanical systems, fans with their own control and duty/standby arrangement.",
    checks="We confirm free areas and positions against the design, route fire-rated cable to suit the building's construction, set the cause-and-effect with the fire alarm, and commission each zone with the client present. The handover pack lists what was installed, how it was tested and the user checks expected afterwards.",
    faq=("Can you design the smoke control system?", "We work to a fire strategy or engineer's design. If one does not exist we can point you to a fire engineer and install once the design is agreed."),
)
P["smoke-control-system-repair"] = dict(
    lede="Tracing and fixing faults across smoke control panels, vents, dampers and interfaces",
    symptoms="System faults are often spread across parts: a damper reporting open when it is shut, a fan that will not start in smoke mode, a lobby vent that opens on the wrong floor, or a panel full of faults after building works. These need tracing as a system, not one component at a time.",
    parts="We find damaged fire-rated cable, dampers with failed actuators or end switches, mechanical fans with failed starters or inverters, controllers with corrupted settings and fire-alarm interface relays wired to the wrong outputs after alterations.",
    checks="We work from the cause-and-effect matrix, test each input and output in sequence, compare damper and vent positions with the panel's reported state, and prove smoke mode for every zone before signing off. Where the matrix does not exist we record what the system actually does.",
    faq=("Our system was installed by a company that no longer exists. Can you still work on it?", "Yes. We identify the equipment on site, and if a component is unsupported we explain the compatible options."),
)
P["smoke-damper-testing"] = dict(
    lede="Drop testing and actuation checks on smoke and fire dampers in ductwork and shafts",
    symptoms="Damper testing is usually triggered by an insurer, a fire risk assessment or a building manager who cannot find the last test record. Dampers without access panels, or with panels hidden above fixed ceilings, are a common problem we plan around.",
    parts="We test motorised smoke dampers, combination fire and smoke dampers, and fusible-link fire dampers, including actuators, end switches, access panels and, where fitted, the damper's interface to the fire alarm or building management system.",
    checks="Each damper is located, labelled and operated: drop-tested where it has a fusible link, or driven closed and open where motorised, with the blade inspected through the access panel and the end-switch state checked against the controller. Inaccessible dampers are listed as such, with what would be needed to reach them.",
    faq=("What if a damper cannot be reached?", "We record it as not tested with the reason and its location, so you can arrange an access panel. It is never ticked as passed."),
)
P["smoke-vent-maintenance"] = dict(
    lede="Planned maintenance contracts for smoke vents, louvres and their drives",
    symptoms="Maintenance clients want the same engineer attending, the same checklist every visit and the history in one place. Most problems found on maintenance visits are small: a drive that has started to labour, a seal that has perished, a vent opened by a contractor and left unpowered.",
    parts="Maintenance covers vents and louvres, drives and brackets, seals and hinges, panels and batteries, manual switches, weather sensors and the fire-alarm link, with batteries replaced on schedule rather than when they finally fail.",
    checks="Visits follow a fixed schedule agreed with you, opening each vent from both the fire signal and the manual control, lubricating where the manufacturer allows, and comparing results with the last visit so slow deterioration is picked up early.",
    faq=("Can maintenance visits be booked around residents?", "Yes. We agree times with you and give residents notice of when vents will be operated."),
)
P["smoke-vent-repair"] = dict(
    lede="Repairs to smoke vents that leak, stick, will not latch or no longer answer the panel",
    symptoms="Common repair calls are a roof vent letting water in, a façade vent that opens but will not close against its seal, broken glass in a vent leaf, a vent damaged by wind while open, or a vent that has been bolted shut by a previous contractor to stop a leak.",
    parts="Typical repairs involve hinges, seals, glazing, leaf frames, upstands on flat roofs, gas struts and drives. Wind damage often bends the drive bracket and twists the frame, so the drive is checked even when it seems to work.",
    checks="We inspect the frame and seal with the vent closed, check the latch and drive position, run it from the panel and the manual switch, and test it in light rain conditions where possible. Any bolt or lashing found is removed only once the duty holder knows.",
    faq=("A previous contractor bolted our vent shut. Is that allowed?", "A vent that cannot open is a fire safety defect. We tell the responsible person and repair the vent so it can open again."),
)
P["smoke-vent-service"] = dict(
    lede="Servicing individual smoke vents and their end-of-line monitoring",
    symptoms="A vent service is often requested for a single stair head vent in a small block, where the building has one vent, one panel and one break-glass. These systems are easy to forget, and we often find the panel batteries dead and the vent last opened at handover.",
    parts="We service the vent, its drive, the panel or controller, the standby battery, the break-glass and open/close switches, and the end-of-line device that lets the panel spot a cut cable.",
    checks="The vent is opened by the fire alarm, the break-glass and the switch, timed and checked for full opening. We cut the line monitoring briefly to prove the panel reports it, load-test the battery and leave a dated record on site.",
    faq=("We only have one vent. Is a service still worth it?", "Yes. A single vent is the only smoke clearance for that stair, so it needs to work when the alarm goes."),
)

# ---------------------------------------------------------------- barriers
P["automatic-barrier-installation"] = dict(
    lede="New automatic rising-arm barriers for car parks, yards and private roads",
    symptoms="Installations usually start with a car park that has unauthorised parking, a yard that needs controlled HGV entry, or an old barrier that is not worth repairing. The questions we ask first are about lane width, traffic volume, who needs access and how they will open it.",
    parts="An installation includes the barrier cabinet and motor, the boom and any skirt or lights, ground loops and detectors, photocells, a safety edge where needed, an access control device such as a keypad, fob or ANPR, a concrete base or island, and a power supply from a suitable local isolator.",
    checks="We measure the lane, check for buried services before cutting loops, check the supply, and set the barrier up so safety devices stop and reverse the arm. Commissioning includes a risk assessment of the lane, a walked test with vehicles and pedestrians, and training for the site staff.",
    faq=("What barrier do you install?", "New installations are specified as CAME. iComply is a CAME partner, and the model is chosen after the lane survey."),
)
P["automatic-barrier-maintenance"] = dict(
    lede="Planned maintenance for automatic barriers to cut breakdowns and keep safety devices working",
    symptoms="Most barrier breakdowns start small: an arm that bounces at the end of travel, a detector that occasionally misses a car, a photocell knocked out of line by a vehicle. Maintenance visits catch these before the lane is blocked at rush hour.",
    parts="Maintenance covers the motor and gearbox, springs and balance, boom fixings, loops and detectors, photocells, safety edges, the manual release, limit settings and the control board, plus the access control devices on the lane.",
    checks="We check arm balance with the motor released, test every safety device stops and reverses the arm, check loop detector sensitivity with a vehicle, inspect the boom and fixings for impact damage, and record the cycle count where the board reports it.",
    faq=("How often should a barrier be maintained?", "Busy car parks often need two to four visits a year. Quieter private roads can run on one or two. We set the frequency from the cycle count and the site."),
)
P["automatic-barrier-repair"] = dict(
    lede="Repairs to automatic barriers that stop, drift, bounce or have lost a safety device",
    symptoms="Repair calls include an arm that rises and immediately drops, a barrier that will only open by hand, a lane that opens on its own, a boom drifting down after opening, and a control board showing an error code after a power cut or storm.",
    parts="Causes we find include worn gearboxes, broken balance springs, failed limit switches, encoders that have lost their position, control boards damaged by surges, failed loop detectors, and photocells that have been knocked out of alignment.",
    checks="We read the board's error codes, release the motor and check balance by hand, re-learn travel limits where needed, and prove every safety device. A barrier is not left in automatic mode with a safety device bypassed.",
    faq=("Can the barrier be left open until it is fixed?", "Yes, if that is safe for the site. We can lock it up in a safe position and agree the plan with you."),
)
P["barrier-arm-replacement"] = dict(
    lede="Replacing damaged barrier booms after vehicle strikes or wind damage",
    symptoms="Arms get hit. The common call is a boom snapped by a van or lorry, a bent arm that no longer meets its rest, a missing breakaway section, or LED strips and skirts torn off. Sometimes the arm looks fine but the hub that holds it has cracked.",
    parts="We fit replacement booms in the correct length and profile, fixing plates and hubs, breakaway couplings, arm rests, skirts and LED strips. The springs often need re-setting after a change, because a lighter or heavier arm changes the balance.",
    checks="We check the hub and output shaft for damage, fit the arm, rebalance the springs with the motor released, set the travel limits, and prove the safety devices. If the strike has also damaged the gearbox, we tell you before fitting a new arm to a failing unit.",
    faq=("Can you fit a breakaway arm so the next strike is cheaper?", "Often, if the barrier model supports one. We tell you what is available for your barrier."),
)
P["barrier-call-out"] = dict(
    lede="Same-day callouts for barriers stuck open, stuck shut or cycling up and down",
    symptoms="Call-outs are usually about a lane that is blocked: residents cannot get out, deliveries are queuing, or the barrier is cycling up and down on its own. Sometimes the arm is stuck open and the problem is security rather than access.",
    parts="On the day it is often a tripped supply, a failed detector, a fob reader or keypad fault, a photocell blocked by debris, a board fault after a power cut, or a sheared arm. Most can be made safe on the first visit and many repaired.",
    checks="We make the lane safe first, release the barrier if it has to stay open, then trace the fault from the board's errors and the safety devices. Before leaving we agree with you whether the barrier stays in automatic, locked open or locked shut.",
    faq=("How fast can you attend?", "Same-day or next-day attendance is usually available, depending on where the site is and the time of the call."),
)
P["barrier-loop-detector"] = dict(
    lede="Fixing induction loops and loop detectors that miss vehicles or trigger on their own",
    symptoms="Loop faults cause barriers that close on cars, fail to open when a car arrives, or open with nothing in the lane. They often follow resurfacing, a cracked saw-cut, water getting into a joint, or a detector set too sensitive for the site.",
    parts="The parts involved are the buried loop itself, the twisted feeder to the cabinet, joints, and the detector card or pack. A broken loop usually needs a new saw-cut and sealing. Detectors can sometimes be retuned rather than replaced.",
    checks="We measure loop resistance and insulation to earth, read the detector's frequency and fault indication, check the feeder is twisted and not run alongside mains, and test detection with a vehicle in the right position. Crossed loops are re-spaced to stop interference.",
    faq=("Our car park was resurfaced and the barrier stopped working. Why?", "Resurfacing often cuts or buries the loops. We test them and, if needed, cut new loops into the new surface."),
)
P["barrier-motor-replacement"] = dict(
    lede="Changing a failed barrier motor or gearbox on the existing cabinet",
    symptoms="A failing motor can be heard before it stops: grinding, slow travel, stalling under load or tripping the supply. Some motors burn out after years of the springs being out of balance and the motor doing work the springs should do.",
    parts="We replace motors and gearboxes, encoders, capacitors on AC drives, limit switches and couplings, and sometimes the control board if it was damaged by the motor failure. The new drive is matched to the cabinet, the boom length and the duty cycle of the lane.",
    checks="Before fitting we check the springs and balance, so the new motor is not overloaded from day one. After fitting we set the travel and slow-down, measure the current through the cycle and prove the safety devices.",
    faq=("Is it worth replacing just the motor?", "Usually, if the cabinet, arm and board are sound. If the barrier is near the end of its life we say so and set out both options."),
)
P["barrier-replacement"] = dict(
    lede="Replacing old or unsupported barriers with new CAME equipment",
    symptoms="Replacement makes sense when spares are no longer available, the cabinet is corroded, repeated repairs have not held, or the site's needs have changed, for example a longer lane or a new access control system.",
    parts="We remove the old barrier, check or rebuild the base, fit the new cabinet and boom, reuse loops and supply where they are sound, add photocells and edges where the risk assessment calls for them, and reconnect or upgrade the access control.",
    checks="We survey the lane and the existing supply and loops before quoting, plan the swap so the lane is out of use for as short a time as possible, then commission the new barrier with a walked test and a handover to site staff.",
    faq=("Can you reuse our existing loops and fobs?", "Often yes. We test the loops and check whether the fob or keypad system can connect to the new barrier before quoting."),
)
P["barrier-stuck-down"] = dict(
    lede="Help when a barrier arm is stuck down across the lane and vehicles cannot get out",
    symptoms="A barrier stuck down traps vehicles and residents and can block emergency access. Typical causes are a power cut, a tripped supply, a failed motor, a board in an error state, or a safety device that thinks something is in the way.",
    parts="We check the supply and fuses, the board's error code, the photocells and edges, the loop detector and the motor. Where the arm needs to be lifted straight away, we use the manual release and secure it.",
    checks="First we make the lane usable safely, using the manual release and securing the arm. Then we trace the fault. You will be told whether the barrier is left working, locked open, or waiting for a part.",
    faq=("Can we lift the arm ourselves?", "Many barriers have a manual release key. If you have it and know how, use it, but never force the arm. We can talk you through it on the phone."),
)
P["bft-barrier-repair"] = dict(
    lede="Fault-finding, repair and servicing for existing BFT barriers, including Giotto and Moovi models",
    symptoms="On BFT barriers we often see arms that bounce at the end of travel after spring wear, control boards showing fault codes after a storm, encoders that have lost their limit positions, and Giotto units with sluggish travel as their drives age.",
    parts="Typical BFT repairs involve balance springs, boom fixings, encoder and limit settings, control boards, loop detectors and photocells. Spare availability varies by model age, and the exact model is confirmed from the cabinet label.",
    checks="We read the BFT board's diagnostics, release the motor to check balance, re-learn travel if needed and prove each safety device. If a BFT part is no longer available we tell you and set out options.",
    faq=("Will you install a new BFT barrier?", "No. We repair and service existing BFT barriers. New barrier installations are specified as CAME."),
)
P["came-barrier-installation"] = dict(
    lede="New CAME barrier installations, specified and commissioned for your lane",
    symptoms="Clients come to us for a CAME barrier because they want a supported product with a parts supply, or because their site already uses CAME gates or barriers and they want one system. We start from the lane width, the traffic and the access method.",
    parts="A CAME installation typically includes a GARD-range barrier cabinet with its boom, photocells and safety devices, a CAME control board, loops and detectors, and the access control connection. Barrier model and boom length are chosen after the survey.",
    checks="We survey the lane, build or check the base, install and wire the barrier, set travel and force on the CAME board, and commission with a walked test and a lane risk assessment. Staff are shown how to use the manual release.",
    faq=("Which CAME barrier will we need?", "It depends on lane width, traffic volume and the site. We recommend a model after the survey and explain why."),
)
P["came-barrier-repair"] = dict(
    lede="Repairs to existing CAME barriers, from GARD series cabinets to older models",
    symptoms="CAME repair calls include arms that rise slowly or not at all, boards showing error messages after a power cut, encoders out of position, and older units where the springs have weakened. We work on current GARD models and older CAME barriers still in service.",
    parts="Common CAME repairs involve control boards, encoders, springs, motors, boom fixings and photocells. Because iComply is a CAME partner, parts for current models can usually be sourced quickly once the model is confirmed.",
    checks="We read the CAME board's error display, check balance with the motor released, run the board's travel learn, and prove photocells and safety edges stop and reverse the arm.",
    faq=("Our CAME barrier is very old. Can it still be repaired?", "Often yes. If parts are no longer made, we say so and offer a like-for-like CAME replacement."),
)
P["came-barrier-service"] = dict(
    lede="Planned servicing of CAME barriers and their safety devices",
    symptoms="A CAME barrier service is booked to keep a busy lane reliable and to meet insurance or site safety requirements. It catches spring wear, arm damage, loose fixings and drifting detectors before they cause a breakdown.",
    parts="We service the CAME cabinet, motor and gearbox, springs, boom and fixings, photocells, edges, loops and detector, the control board settings and the manual release.",
    checks="Balance is checked by hand, travel and force settings are reviewed on the CAME board, every safety device is tested, and the cycle count is recorded where available to plan the next service.",
    faq=("Does servicing keep the barrier safe as well as working?", "Yes. Every visit tests that safety devices stop and reverse the arm, not just that it opens."),
)
P["came-gard-gt4-installation"] = dict(
    lede="Installing CAME GARD GT4 barriers for lanes up to about four metres",
    symptoms="The CAME GARD GT4 is a compact barrier often chosen for private car parks, residential developments and staff parking, where the lane is narrow and traffic is moderate. Clients often ask whether it is strong enough for their lane; the survey answers that.",
    parts="A GT4 installation includes the GT4 cabinet and boom, the control board, photocells, loops or other vehicle detection, the access control connection and the base. Lights or a skirt on the boom are added if the risk assessment needs them.",
    checks="We confirm the lane width and traffic volume suit the GT4, install and wire it, set travel and force, and commission with a walked test. If the lane is too wide or busy, we recommend a larger GARD model instead.",
    faq=("Is the GT4 right for a busy car park?", "It suits moderate traffic and narrower lanes. For heavy use we recommend a larger GARD model after the survey."),
)
P["car-park-barrier-maintenance"] = dict(
    lede="Planned contracts for car-park barriers at entry and exit lanes",
    symptoms="Car parks put barriers through hundreds of cycles a day. Problems found on maintenance include worn springs, loose arms, misaligned photocells, ticket or ANPR integration faults and loops damaged by heavy vehicles.",
    parts="Maintenance covers the barriers at each lane, loops and detectors, photocells and edges, booms, the link to the ticket machine, ANPR or intercom, and the manual release procedures for staff.",
    checks="Each lane is tested for detection, opening, closing and safety reversal. We check the integration with the car-park system so a valid ticket or plate opens the barrier, and agree a visit schedule from the cycle counts.",
    faq=("Can you maintain barriers linked to our ticket or ANPR system?", "Yes. We test the barrier side and the signal from your system. The ticket or ANPR provider handles their own software."),
)
P["car-park-barrier-repair"] = dict(
    lede="Repairs to car-park barriers at entry and exit lanes to get traffic moving",
    symptoms="Car-park repair calls are usually urgent: a queue at the entry, a lane that will not close, an arm hit by a vehicle, or a barrier that opens for every car regardless of ticket. Each one costs the operator money or security.",
    parts="We repair motors, springs, arms, loops, detectors, photocells, control boards and the interface from the ticket machine or ANPR system. Arms are often replaced after strikes.",
    checks="We make the lane safe, find out whether the fault is in the barrier or the car-park system, fix the barrier side, and test opening on a valid signal and safety reversal before handing the lane back.",
    faq=("Whose problem is it, the barrier or the ticket system?", "We test the signal from your system to the barrier so you know which side the fault is on."),
)
P["car-park-barrier-servicing"] = dict(
    lede="Routine servicing for car-park barriers to meet operator and insurer requirements",
    symptoms="Operators book servicing to keep lanes reliable, to satisfy insurers and leases, and to have a record if a vehicle or pedestrian is hit by an arm. Servicing also spots the wear that leads to emergency call-outs.",
    parts="Servicing covers motors, gearboxes, springs, booms and fixings, loops and detectors, photocells, safety edges, control settings and manual releases at every lane in the car park.",
    checks="Each lane is cycled with a vehicle, safety devices are tested for stop and reverse, balance is checked by hand, and a dated record per barrier is left for the operator's file.",
    faq=("Do you provide service records per barrier?", "Yes. Each barrier gets its own record with results and any defects."),
)
P["faac-barrier-repair"] = dict(
    lede="Fault-finding, repair and servicing for existing FAAC barriers, including hydraulic models",
    symptoms="Many FAAC barriers are hydraulic, so as well as the usual board and safety faults we see oil leaks, a boom that drifts down under its own weight, slow travel in cold weather and pumps that run without moving the arm.",
    parts="FAAC repairs involve hydraulic pumps, rams and seals, oil top-ups or changes, by-pass valve settings, control boards, balance springs, loops and photocells. The exact model is confirmed on site.",
    checks="We check oil level and look for leaks, test the by-pass valve setting and manual release, check balance, and prove safety devices. Hydraulic units are not overfilled or run with the release open.",
    faq=("Will you install a new FAAC barrier?", "No. We repair and service existing FAAC barriers. New barrier installations are specified as CAME."),
)
P["nice-barrier-repair"] = dict(
    lede="Fault-finding, repair and servicing for existing Nice barriers such as the M-Bar, S-Bar and Wide ranges",
    symptoms="Nice barriers commonly come to us with faults after power cuts, encoder or limit faults that stop the arm short, failing batteries on units with battery backup, and radio receivers that no longer recognise remotes.",
    parts="Nice repairs involve control boards, encoders, batteries, springs, booms, loops, photocells and the receivers for remote controls. The model and its board are confirmed from the cabinet.",
    checks="We read the board's diagnostic flashes, re-learn travel where needed, check balance and test every safety device. Where a Nice part is unavailable we say so and set out options.",
    faq=("Will you install a new Nice barrier?", "No. We repair and service existing Nice barriers. New barrier installations are specified as CAME."),
)
P["rising-arm-barrier-repair"] = dict(
    lede="Repairs to rising-arm barriers that will not lift, will not lock down or stop mid-travel",
    symptoms="Rising-arm faults include arms that stop part-way, arms that will not stay locked down and can be lifted by hand, booms that slam at the end of travel, and barriers that open slowly as springs or drives wear.",
    parts="We repair drive units, springs, balance arms, end-of-travel stops and dampers, booms and their fixings, the control board and the safety devices across the lane.",
    checks="We check balance with the drive released, set the end stops and slow-down, test the locking at the closed position, and prove photocells and loops stop the arm before handing back.",
    faq=("Why does our barrier slam at the top?", "Usually the slow-down settings or the springs are off. We reset them so the arm is not damaged."),
)
P["vehicle-barrier-repair"] = dict(
    lede="Repairs to vehicle barriers at private roads, depots and residential sites",
    symptoms="Vehicle barriers at depots, private roads and residential developments are often in exposed spots, so we see water ingress into cabinets, corroded terminals, wind-damaged arms and faults after storms, alongside the usual motor and safety device problems.",
    parts="Repairs involve cabinet seals and terminals, control boards, motors, springs, booms, loops, photocells and the access control readers or intercoms that open the barrier.",
    checks="We open the cabinet and look for water and corrosion first, then trace the fault, repair it and prove the safety devices. Where the cabinet itself is failing we say so.",
    faq=("Can you fix the intercom or keypad too?", "Yes, where it opens the barrier. We test the whole chain from reader to arm."),
)

# ---------------------------------------------------------------- fire
P["addressable-fire-alarm-installation"] = dict(
    lede="Addressable fire alarm systems for larger or more complex buildings",
    symptoms="Addressable systems are usually chosen for larger buildings, blocks of flats with communal areas, HMOs over several floors, care homes and offices, where knowing exactly which device has operated saves time in an alarm and makes false alarms easier to trace.",
    parts="An addressable installation includes the panel, loops of fire-rated cable, addressable detectors, call points, sounders and beacons, interface modules for door holders, AOVs and lifts, and, where needed, remote indicators or repeater panels.",
    checks="We design zones and loops from a survey and the fire risk assessment, install and address each device, program the cause-and-effect, then commission with every device tested and a loop resistance and insulation test recorded. A zone plan is left beside the panel.",
    faq=("Should we choose addressable or conventional?", "Addressable suits larger and more complex buildings. Smaller premises often suit conventional. We explain the choice after the survey."),
)
P["advanced-fire-alarm-repair"] = dict(
    lede="Repairs to Advanced Electronics fire alarm panels, including MxPro systems",
    symptoms="Advanced panels are common in larger commercial and multi-occupancy buildings. Typical calls are loop faults after building work, earth faults, network faults between panels, and devices reporting dirty or missing.",
    parts="We work on Advanced panels and their loop cards, network cards, power supplies and batteries, and the compatible loop devices fitted. Programming changes on networked systems are planned with the client before they are made.",
    checks="We read the panel's event log, isolate only the affected loop or zone, measure loop resistance and insulation, locate the fault device or cable section, and test the repaired loop before clearing faults.",
    faq=("Can you change programming on our Advanced panel?", "Where we have the access level to do so safely, yes. Changes are agreed with you first and recorded."),
)
P["c-tec-fire-alarm-repair"] = dict(
    lede="Repairs to C-Tec fire alarm panels and devices in conventional and addressable systems",
    symptoms="C-Tec panels are common in smaller commercial premises, HMOs and schools. We see zone faults from damaged cables, sounder circuit faults, battery and charger failures, and addressable panels reporting device faults after decorating or dust.",
    parts="Repairs involve C-Tec conventional and addressable panels, zone and sounder circuits, power supplies and batteries, detectors, call points, sounders and interface units.",
    checks="We read the panel, test the faulty circuit end to end, find and repair the cable or device, test batteries and the charger, and prove every device on the affected zone or loop.",
    faq=("Our C-Tec panel keeps showing a fault after we reset it. Why?", "A fault that returns has not been fixed. We trace the cause on the circuit rather than reset it again."),
)
P["conventional-fire-alarm-installation"] = dict(
    lede="Conventional zoned fire alarm systems for smaller premises and HMOs",
    symptoms="Conventional systems suit smaller shops, offices, small HMOs and buildings with a few clearly separated areas, where showing the zone of an alarm is enough to find it quickly.",
    parts="A conventional installation includes a zoned panel, detectors and call points wired on zone circuits, sounder circuits, batteries and, where needed, interface units for door holders or other equipment.",
    checks="We plan zones from the survey and the fire risk assessment, install and wire each zone, test each detector and call point, and leave a zone chart and logbook with the panel.",
    faq=("Can a conventional system be upgraded later?", "Partly. Some panels can grow, but going addressable usually means a new panel and devices."),
)
P["emergency-fire-alarm-repair"] = dict(
    lede="Urgent repair when a fire alarm is in fault, keeps false alarming or will not reset",
    symptoms="Emergency calls are about alarms that will not stop, panels that will not reset, repeated false alarms waking a building at night, or a system that has gone into fault and left part of the building unprotected.",
    parts="Frequent causes are dirty or failed detectors, damaged cables, faulty call points, water ingress into devices, failed batteries and panels damaged by power surges.",
    checks="We find out what operated, isolate only what has to be isolated, make the system safe, fix the cause or the faulty device, and tell the responsible person if any part remains disabled and what interim measures are needed.",
    faq=("Can you disable the alarm until you arrive?", "Only the responsible person can decide that, and they must put interim measures in place. We can advise on the phone."),
)
P["false-alarm-investigation"] = dict(
    lede="Finding the cause of repeated false alarms and unwanted fire signals",
    symptoms="False alarms often follow a pattern: the same detector, the same time of day, cooking or showers nearby, dust from building work, or aerosol and vape use. Each one disrupts residents and staff and can lead to call-out charges from the fire service.",
    parts="Common causes are the wrong detector type for the room, detectors near kitchens or bathrooms, dirty detectors, insects in heat or smoke chambers, damaged call points and faults on the cabling.",
    checks="We read the panel log to build the pattern, inspect the device and its location, check the detector type suits the room, and recommend a fix such as relocating, changing type or cleaning, recorded so you can show the steps taken.",
    faq=("Will changing the detector type reduce cover?", "Not if the right type is chosen. The new detector must still meet the design category for that room."),
)
P["fire-alarm-call-out"] = dict(
    lede="Same-day fire alarm callouts for panels in fault or alarms that will not reset",
    symptoms="Call-outs usually start with a panel bleeping, a fault light on, a sounder that will not stop, or an alarm that went off overnight and now will not reset.",
    parts="On the day the cause is often a failed battery, a detector in fault, a damaged cable, a call point that has been pressed, or a power supply fault.",
    checks="We read the panel, find the cause, reset only when the cause is cleared, test the affected zone, and leave a note saying what was found and what still needs doing.",
    faq=("How fast can you come out?", "Same-day or next-day attendance is usually available, depending on location and time of call."),
)
P["fire-alarm-panel-repair"] = dict(
    lede="Repairs to fire alarm panels with faults on power, batteries, zones or loop cards",
    symptoms="Panel repairs cover dead or frozen displays, constant fault lights, panels that will not charge their batteries, and zone or loop cards that have failed after a surge or water leak.",
    parts="We repair or replace power supplies, chargers, batteries, zone and loop cards, display boards, and the fuses and terminals inside the panel.",
    checks="We test the mains and battery supply, the charger output, each zone or loop, and the panel's outputs to sounders and interfaces, before and after the repair.",
    faq=("Do I need a new panel?", "Not always. Many panel faults can be repaired. We tell you honestly when a panel is beyond economic repair."),
)
P["fire-alarm-panel-replacement"] = dict(
    lede="Replacing old fire alarm panels while keeping compatible devices where possible",
    symptoms="Panels are replaced when they are obsolete, parts are unavailable, the panel is failing repeatedly, or the building needs features the old panel cannot provide, such as more zones or new interfaces.",
    parts="A panel replacement covers the new panel, batteries, connection of existing zones or loops, any devices that are not compatible with the new panel, and the interfaces to door holders, AOVs and other systems.",
    checks="We check the compatibility of existing devices before quoting, plan the changeover to keep protection in place, then test every device and interface after the new panel is in.",
    faq=("Will we need new detectors too?", "Only if they are not compatible with the new panel. We check before quoting."),
)
P["fire-alarm-ppm"] = dict(
    lede="Planned preventive maintenance contracts for fire alarm systems",
    symptoms="PPM contracts keep fire alarms tested and serviced on a fixed schedule, so the building has a record for insurers and fire risk assessments, and faults are found before an emergency.",
    parts="PPM visits cover the panel, batteries, every detector, call point and sounder over the year, interfaces with other systems, and the logbook.",
    checks="Each visit tests a share of devices so all are covered across the year, tests batteries, checks the panel and interfaces, and records results in the logbook.",
    faq=("How often are PPM visits?", "Usually every six months, sometimes quarterly for larger or higher-risk buildings."),
)
P["fire-alarm-replacement"] = dict(
    lede="Replacing a whole fire alarm system that is obsolete or no longer suits the building",
    symptoms="Full replacement makes sense when the system is old, parts are unavailable, the building has changed, or the fire risk assessment says the current system is not suitable.",
    parts="Replacement can include the panel, all devices, new cabling where the old is unsuitable, sounders and beacons, and interfaces to other systems.",
    checks="We survey the building and the fire risk assessment, design the new system, install it with the old system kept running where possible, and commission and test before removing the old one.",
    faq=("Can the old system stay live during the work?", "Usually yes. We plan the changeover so the building is protected throughout."),
)
P["fire-extinguisher-servicing"] = dict(
    lede="Annual servicing of portable fire extinguishers on your premises",
    symptoms="Extinguisher servicing is required each year, and insurers and fire risk assessments expect to see it. Common findings are extinguishers past their extended service date, missing signs, damaged hoses and the wrong type for the risk.",
    parts="Servicing covers each extinguisher's weight or pressure, seals, hoses and horns, the safety pin, signs and brackets, and the type against the risk in each area.",
    checks="Each unit is inspected, weighed or pressure-checked, labelled with the service date, and listed on a record. Units that need an extended service or replacement are identified.",
    faq=("How often do extinguishers need servicing?", "Once a year, with monthly visual checks by staff."),
)
P["gent-fire-alarm-service"] = dict(
    lede="Servicing existing Gent fire alarm systems, including Vigilon and Nano panels",
    symptoms="Gent systems are common in larger commercial buildings, schools and hospitals. Service calls are usually routine, but we also see loop faults, device faults after refurbishment and network faults between panels.",
    parts="We service Gent panels, batteries, loop devices, sounders, interfaces and network connections.",
    checks="We test devices across the system, review the panel log, test batteries, and check interfaces with other systems. Faults are reported with their location.",
    faq=("Are you Gent approved?", "No. We service existing Gent systems as an independent contractor. The model is confirmed on site."),
)
P["kentec-fire-alarm-repair"] = dict(
    lede="Repairs to Kentec fire alarm panels such as the Syncro range",
    symptoms="Kentec Syncro panels are widely used with a range of loop devices. Typical repairs are loop faults, earth faults, battery failures and device faults after works.",
    parts="We repair Kentec panels, loop cards, power supplies and batteries, and the loop devices connected to them.",
    checks="We read the panel log, isolate only the affected loop, test resistance and insulation, find the fault and test the loop after the repair.",
    faq=("Can you work on the devices connected to our Kentec panel?", "Yes. We check the device make and compatibility on site."),
)
P["morley-fire-alarm-repair"] = dict(
    lede="Repairs to Morley fire alarm panels such as the ZXSe, DXc and Max ranges",
    symptoms="Morley panels are common in older and newer commercial buildings. We see loop and zone faults, network faults, battery failures and older panels with failing displays.",
    parts="We repair Morley panels, loop cards, power supplies, batteries, displays and the devices connected to them.",
    checks="We read the panel, isolate the affected circuit, test it and fix the cause, then test the circuit before clearing the fault.",
    faq=("Our Morley panel is old. Can it still be repaired?", "Often yes. If parts are unavailable we explain the options."),
)
P["wireless-fire-alarm-installation"] = dict(
    lede="Wireless fire alarm systems for buildings where cabling is difficult",
    symptoms="Wireless systems suit listed buildings, occupied premises, temporary buildings and places where running cable would be disruptive or damaging.",
    parts="A wireless installation includes the panel, radio translators or expanders, wireless detectors, call points and sounders, and batteries in each device.",
    checks="We do a radio survey before installation, place devices for good signal, commission each device, and test signal strength and battery levels.",
    faq=("Do wireless devices need batteries changed?", "Yes, typically every few years. We record battery levels at each service."),
)

# ---------------------------------------------------------------- shared pools
# Each hub draws a different handful of these, so no two hubs share the same set.
POOL = {
"aov-smoke": [
"Smoke ventilation exists to keep an escape stair or corridor clear enough for people to get out and for firefighters to get in. When {equipment} does not work, smoke that reaches the stair has nowhere to go, so a fault that looks minor on a panel can matter a great deal in a fire.",
"Residents rarely notice smoke vents until something goes wrong. The most common report we hear is a cold draught or water on the top landing, which usually means a vent that has not closed fully. That is worth a visit on its own, because a vent that does not close properly may also not open properly.",
"On flat roofs the vent upstand, flashing and kerb take the weather year after year. We look at them every time we are on the roof, because a leaking kerb rots the frame the actuator is fixed to, and a drive pulling against a soft frame will eventually tear its bracket out.",
"Smoke control equipment is tied to the fire alarm, and many faults sit on the boundary between the two. We test the signal from the fire panel to the smoke control panel as well as the vent itself, so the result tells you whether the system works as a whole.",
"Firefighter override switches let the fire service open or close vents by hand. They are often behind a key that nobody on site can find. We check the switch works, that it is labelled, and that the building manager knows where the key is kept.",
"Batteries are the most common single cause of smoke control faults. A panel can run for months on mains with a dead battery and show only a small fault light. When the mains goes in a fire, the vents will not move. Batteries are load-tested, not just measured at rest.",
"Weather sensors close vents in rain and wind for everyday comfort. In smoke mode they must be overridden. We test that a fire signal opens vents even when the weather sensor is telling them to close.",
"Many blocks have had their stairs repainted since the vents went in. Paint across the gap between leaf and frame can be enough to stop a drive. We check the leaf moves freely by hand before we blame the actuator.",
"Free area is the clear opening the smoke can escape through. If a vent only opens to half its intended angle, the free area may be much less than the strategy assumes. We check opening angles against the design where it is available.",
"We work from the building's fire strategy or the fire risk assessment where they exist. If the system on site does not match what those documents describe, we write that down clearly for the responsible person rather than adjusting things to suit.",
"In occupied blocks we tell residents before operating vents, especially in winter or wet weather, and we close every vent before leaving. A vent left open after testing is one of the complaints building managers most often pass on to us.",
"Cable runs for smoke control should be fire-resisting so the vent can still be driven during a fire. On older or altered buildings we often find sections replaced with ordinary cable. We list any we see so they can be put right.",
"Access to roof vents is planned before the visit. Some need a roof hatch key, some a cat ladder, and some can only be reached safely with a harness system or a mobile platform. Knowing which saves a wasted trip.",
"Where a vent has been taped, bolted or lashed shut to stop a leak, it no longer protects the stair. We remove such fixes only after telling the responsible person, then repair the real cause of the leak.",
"Mechanical smoke extract systems add fans, dampers and their controls to the picture. Fans are run in smoke mode, their duty and standby sets are swapped, and dampers are checked to move to the correct position for each zone.",
"After the work we leave a short written record that the building manager can file with the fire risk assessment: what was tested, what passed, what failed and what we recommend next.",
],
"barrier": [
"A barrier is a machine that moves over people and vehicles many times a day, so safety devices matter as much as the motor. Photocells, safety edges and loops must stop and reverse the arm before it touches anything. We test those on every visit, whatever the reason for the call.",
"The balance of the arm is the single most important setting on most barriers. When springs are right, the motor only guides the arm. When they are wrong, the motor lifts the full weight and wears out quickly. We check balance with the drive released before changing parts.",
"Barriers usually sit in the open, so cabinets take rain, road salt and condensation. We look for water inside, rusted fixings and corroded terminals, because these cause intermittent faults that are hard to trace later.",
"Most sites need the lane kept usable while we work. We plan the visit so the barrier can be locked open, or we agree a short closure time with the site, and we put cones and signs out while the arm is being worked on.",
"Ground loops sit under the road surface and detect vehicles by the change in their magnetic field. A cracked saw-cut, a pothole or resurfacing can damage them. Loop faults often look like barrier faults, so we test the loops early.",
"Access control decides who the barrier opens for: fobs, keypads, intercoms, ANPR cameras or ticket machines. When a barrier will not open for a valid user, the fault may be in that system rather than the barrier. We test the signal at the barrier board to find out which.",
"Vehicle strikes are a common cause of damage. After a strike we check the hub, gearbox and output shaft, not just the arm, because a bent shaft will wreck a new boom within weeks.",
"Power cuts and storms can reset or damage barrier boards. Some lose their travel limits and need a relearn; others are damaged by surges. Where a site has repeated surge damage, we suggest protection at the supply.",
"Manual release lets site staff lift the arm by hand in a power cut or breakdown. We make sure it works, that the key is on site and that someone knows how to use it safely.",
"Pedestrians should not walk through a barrier lane. Where they do, because there is no separate footpath, we flag it in the risk assessment and suggest signs, a separate gate or extra safety devices.",
"Barrier faults at the start of a working day cause queues, frustrated tenants and sometimes damaged vehicles. We aim to make the lane usable on the first visit, even if a part has to be ordered for the full repair.",
"We work to BS EN 12453 for the safety of powered barriers. That means measuring how the arm behaves when it meets an obstruction, and making sure safety devices are fitted where the risk calls for them.",
"Old barriers often have a history of quick fixes: bypassed safety devices, extra springs, a detector from another make. We note what we find so the client knows what state the barrier is in.",
"Booms come in different lengths, profiles and materials. Skirts, lights and breakaway sections all add weight. Any change to the arm means rebalancing the springs and checking the drive can cope.",
"On sites with more than one barrier, faults often repeat across lanes because they are the same age and type. We look at the other lanes while we are there and let you know if the same problem is coming.",
"Lane markings, signs and lighting help drivers stop at the right point. We note faded markings or missing signs, because a driver who stops in the wrong place may not be detected or may be hit by the arm.",
"Barrier control boards store settings for force, speed, slow-down and auto-close time. We record those settings before changing anything, so they can be put back if a part is swapped.",
"Fobs and remotes get lost, and old ones keep working long after a tenant leaves. Where the access control allows it, we can help the site manager clear old users when the barrier is serviced.",
"Heavy goods vehicles, buses and vans with high sides need longer detection and a longer open time. Where the lane takes larger vehicles, we set loops and timers for them, not just for cars.",
"Concrete bases and islands crack over time, especially where vehicles clip them. A loose base lets the cabinet rock, which strains the arm and gearbox. We check the fixings and the base while we are on site.",
"After the visit we leave a dated record for each barrier: what we found, what we did, any defects still open and when the next service is due.",
],
"fire": [
"A fire alarm only protects people if it is heard and acted on. Panels in fault, isolated zones and detectors removed during decoration all reduce cover without anyone noticing. Every visit ends with the system's condition written down for the responsible person.",
"BS 5839-1 covers fire detection and alarm systems in non-domestic buildings, and BS 5839-6 covers dwellings including many HMOs. We work to the part that applies to the building and record any departures we see from it.",
"Batteries keep the alarm running in a power cut. They have a limited life, typically around four years, and can fail without warning. We load-test them rather than read the voltage at rest, which can look fine on a dead battery.",
"Logbooks are often missing or incomplete. We fill in our visit and tell the responsible person if earlier entries are missing, because the weekly test and the service history are part of what a fire risk assessor will check.",
"Detectors need the right type for the room. Smoke detectors in kitchens or bathrooms cause false alarms; heat detectors in bedrooms give late warning. Where the type does not suit the room, we say so and recommend the right one.",
"Weekly call point testing is the responsible person's job, not the contractor's. We show site staff how to do it, which call point to use each week and how to record it, so the system is tested between our visits.",
"Building works are a common cause of fire alarm faults: cut cables, covered detectors, dust in sensing chambers. If works are planned, we can advise on isolating only the right zones and putting the system back afterwards.",
"Cause-and-effect is what the system does when a device operates: sounders, door releases, AOVs, lifts, plant shutdown. We test the outputs that matter for the building, not just whether the panel shows an alarm.",
"Sounders have to be heard throughout the building. We check sound levels in rooms where people sleep or work alone, because a sounder in a corridor may not wake someone behind a closed door.",
"In occupied buildings we tell people before sounding the alarm, and we test in a way that keeps disruption to a minimum. In care homes and sheltered housing we agree timings with staff first.",
"Cables for fire alarms should be fire-resisting and properly supported. Cables cable-tied to plastic trunking can drop in a fire. We note unsupported or damaged cable when we see it.",
"Old panels are not automatically unsafe, but parts get harder to source. We tell you when a panel is nearing the end of its supported life, so a replacement can be planned rather than forced by a failure.",
"Remote signalling to an alarm receiving centre, where fitted, is tested with the receiving centre's agreement so the fire service is not sent out unnecessarily.",
"Door holders release fire doors when the alarm sounds. We check they let go on an alarm and that doors then close fully and latch, because a held-open fire door does nothing in a fire.",
"False alarms are not just a nuisance. Repeated alarms lead to people ignoring the sounders, and in some areas the fire service may charge or reduce its response. Finding the cause is part of keeping the system effective.",
"Escape routes need to stay usable while we work. We do not leave tools, ladders or boxes in stairs or corridors, and if a zone has to be isolated for a while, the responsible person knows which areas are covered and which are not.",
"In HMOs and blocks of flats, the alarm in the common parts and the alarms inside each flat may be separate systems. We check which is which before testing, so residents are not woken by a test they were not told about.",
"Manual call points are the simplest part of the system and are often the first to be damaged or covered. We check each one is visible, unobstructed and signed, and that its cover or test key is in place.",
"Panel zone charts and device labels should match the building as it is now. After refurbishments we often find rooms renamed or split, so we update labels and charts where we can and list the rest.",
"Ceiling heights, beams and air movement all affect where detectors should go. When we add or move detectors we space them to the standard for the room, not just where the old one was.",
"Many of our clients are landlords and managing agents with several buildings. We keep records building by building, so a portfolio can see which sites are due a visit and which have open defects.",
"Equipment brought in from another contractor's design is checked for compatibility before it is connected. Mixing detector protocols on one loop is a common cause of faults that come and go.",
"After the visit the responsible person gets a written record: devices tested, faults found, work done, anything still outstanding and when the next visit is due.",
],
}

INTENT = {
"repair": "A repair visit starts with finding out exactly what the fault is and what has already been tried. We ask for photos of the panel or cabinet, any error codes and the last service record. On site we confirm the fault, trace its cause, fix it where parts allow, and test the system as a whole before calling it done.",
"service": "A service visit is planned and follows a fixed checklist, so results can be compared from one visit to the next. Small defects are put right on the day where possible; anything bigger is written up with a recommendation and a quote.",
"servicing": "Servicing follows a fixed checklist each visit, so wear can be tracked over time. Minor defects are fixed on the day where possible, and anything bigger is reported with a recommendation.",
"maintenance": "Maintenance is booked on a schedule agreed with you, so the equipment is looked at before it fails. Each visit follows the same checklist, and results are compared with the last visit to spot slow wear.",
"installation": "An installation starts with a survey of the site and, for fire and smoke systems, the fire strategy or fire risk assessment. We then set out the design and the work in writing, install, commission with you present, and hand over the records and user instructions.",
"replacement": "Replacement starts with a survey of what is there now and what can be reused. We plan the changeover so protection or access is kept as far as possible, install the new equipment, test it fully and remove what has been replaced.",
"call-out": "Call-outs are for faults that cannot wait. We ask what has happened, what the site needs, and whether anyone is at risk. On site the first priority is making things safe; the second is fixing the fault, if parts allow on the day.",
"fault": "When a lane or system is stuck, the first job is to make it safe and usable. The second is to find the real cause so it does not happen again tomorrow.",
"testing": "Testing produces a record of each item tested and its result, with anything that could not be tested listed with the reason. That record goes on the building's file for the fire risk assessor and insurer.",
}

LINE_STANDARD = {
"aov-smoke": "Smoke ventilation work is carried out with reference to BS EN 12101 for the components and BS 7346-8 for the system, alongside the building's fire strategy.",
"barrier": "Powered barrier work is carried out with reference to BS EN 12453, which covers the safety of powered gates and barriers in use.",
"fire": "Fire alarm work is carried out with reference to BS 5839-1 or BS 5839-6, whichever applies to the building, and to the fire risk assessment.",
"fire-extinguisher": "Extinguisher servicing is carried out with reference to BS 5306-3.",
}
