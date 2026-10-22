"""Topic bank for the networking, data cabling and mesh Wi-Fi pack (lock 20 Oct 2026).

Each topic has heading alternatives and paragraphs. A paragraph is a list of
sentence slots; each slot lists alternative wordings. Town pages pick one
wording per slot from a town seed, so neighbouring towns do not share long
runs of identical text. Placeholders: {town}, {subject}.

Rules: price on application, no list prices, no attendance-time promise
beyond Same/Next-Day, no scheme or accreditation claims, no Sky/BT logos or
partner claims ("works with your Sky or BT hub" only).
"""

TOPICS = {
    "cable-grades": {
        "h2": [
            "Cat5e, Cat6, Cat6a or fibre in {town}",
            "Which cable suits a {town} building: Cat5e, Cat6, Cat6a or fibre",
            "Choosing the cable grade for {town} sites",
        ],
        "paras": [
            [
                ["Cat5e still carries gigabit Ethernet to a desk, a TV or a camera, and it is the cheapest copper to run where the building only needs 1 Gb/s.",
                 "Cat5e is rated for gigabit Ethernet, which is plenty for most homes, a single camera or a desk phone.",
                 "For a plain gigabit link to a desk, a smart TV or a door station, Cat5e does the job."],
                ["Cat6 adds headroom: it handles gigabit comfortably and 10 Gb/s over shorter runs, so it is our usual choice for new data points in {town} offices and flats.",
                 "Cat6 gives more margin against interference and supports 10 Gb/s over short runs, so most {town} installs we do now use it.",
                 "Cat6 is the normal grade we fit in {town} today because it costs little more than Cat5e and leaves room for faster equipment."],
                ["Cat6a is thicker and stiffer, carries 10 Gb/s to the full channel length and suits Wi-Fi 6 and 6E access points or comms rooms that will be upgraded later.",
                 "Where access points, a server or a busy switch stack need 10 Gb/s along the whole run, Cat6a is the grade to specify, though it needs bigger containment.",
                 "Cat6a is worth it for backbone runs and high-end access points; it is bulkier, so we check trunking and bend space before quoting it."],
            ],
            [
                ["Between buildings, between floors of larger blocks and on any run longer than copper allows, fibre is the answer.",
                 "When a link has to cross a yard, reach an outbuilding or climb several floors, fibre avoids the length limit and electrical issues that copper has between buildings.",
                 "For links to a garage, a workshop or another block, and for long risers where copper would run out of length, fibre makes sense."],
                ["It does not carry power, so a camera or access point at the far end still needs a local supply or a PoE switch fed from that building.",
                 "Fibre carries no power, which means the far end needs a media converter or switch with its own supply.",
                 "Because fibre carries data only, we plan a powered switch or converter at the remote end."],
                ["We will tell you which grade fits the job in {town}, and why, before any cable goes in.",
                 "The quote names the grade for each run so you know what is being installed.",
                 "You get the cable grade written on the quote, run by run, with the reason for each choice."],
            ],
        ],
    },
    "distance-testing": {
        "h2": [
            "Run lengths, testing and labelling",
            "The 90 m rule, test results and labels",
            "How we test and label every run",
        ],
        "paras": [
            [
                ["A copper Ethernet run is limited to 90 m of fixed cable, the permanent link, plus patch leads at each end, giving a 100 m channel.",
                 "Structured cabling standards allow a 90 m permanent link from patch panel to outlet, with up to 10 m of patch leads in total.",
                 "Copper data cable has a 90 m limit for the fixed part of the run and 100 m including patch leads."],
                ["In a long {town} terrace, a mill conversion or a block with the comms cupboard at one end, we measure the route first, because a run that goes over length can work on the day and then drop out under load.",
                 "We measure the real route in {town} buildings, round corners and up risers, because runs that go over length tend to fail intermittently.",
                 "Long routes are measured before we start, since an over-length run often works at first and then gives slow or dropping links."],
            ],
            [
                ["Each run is tested end to end after termination and the result is recorded against the outlet number.",
                 "Every outlet is tested after it is terminated, and the result goes on the handover sheet with its number.",
                 "We test each link once it is terminated and record the pass against its label."],
                ["Both ends carry matching labels, so the faceplate number in a {town} bedroom or office matches the port on the patch panel.",
                 "Matching labels at the faceplate and the patch panel mean anyone can trace a port in seconds.",
                 "Labels match at both ends, so the next engineer, or your IT person, can find any port without guessing."],
                ["That record matters when a landlord, a tenant or a future contractor needs to trace a fault.",
                 "It saves time later, when a fault has to be traced or a point moved.",
                 "Good records are what make later moves and fault finding quick and cheap."],
            ],
        ],
    },
    "data-points": {
        "h2": [
            "Data points, faceplates and patch panels",
            "Adding data points and a patch panel in {town}",
            "Faceplates, back boxes and the comms cabinet",
        ],
        "paras": [
            [
                ["A data point is a faceplate with one or two RJ45 sockets, fed by its own cable back to a patch panel.",
                 "Each data point is an outlet on the wall, usually single or double, with its own run back to the patch panel.",
                 "A wired data point is simply a socket on the wall with its own cable running back to a central patch panel."],
                ["We fit them flush where walls allow, or in surface boxes and trunking where chasing a {town} wall or a listed finish is not sensible.",
                 "Flush back boxes are used where we can chase or drop cable inside a stud wall; elsewhere we use neat surface trunking.",
                 "In plastered walls we go flush where we can, and use surface trunking in solid walls, rented flats or finished rooms where chasing is unwelcome."],
                ["Typical positions are behind a TV, at a desk, by the router, at each access point and at camera or door-station locations.",
                 "Common spots are the TV wall, home-office desks, the router position, ceiling access points and camera points.",
                 "Most jobs put points at the TV, desks, ceiling access points and any camera, recorder or door-entry location."],
            ],
            [
                ["The cables meet at a patch panel in a cupboard, under-stairs space or wall cabinet, with a switch beside it.",
                 "All runs end at a patch panel, usually in a small wall cabinet with the switch and router alongside.",
                 "At the central point the cables terminate on a patch panel in a cabinet, where short patch leads connect them to the switch."],
                ["That keeps the router, switch and any CCTV recorder in one ventilated place rather than on a shelf or the hall floor.",
                 "It gives the router, switch and recorder a tidy, ventilated home instead of a tangle behind the sofa.",
                 "Equipment then lives in one place with power and airflow, which helps reliability."],
                ["In {town} blocks and HMOs the cabinet usually sits in a locked landlord cupboard, apart from tenant areas.",
                 "For shared buildings in {town} we put the cabinet in a landlord-only cupboard so tenants cannot unplug building systems.",
                 "In a {town} HMO or block, the cabinet goes in a locked landlord space, not in a tenant's room."],
            ],
        ],
    },
    "poe": {
        "h2": [
            "PoE budgets for cameras, access points and door stations",
            "Power over Ethernet: sizing the switch",
            "Getting the PoE budget right in {town}",
        ],
        "paras": [
            [
                ["Power over Ethernet sends power down the data cable, so cameras, ceiling access points and many video door stations need no socket nearby.",
                 "PoE lets one cable carry both data and power to a camera, an access point or a door station.",
                 "With PoE, a single Ethernet cable powers the device at the far end, which is why it suits cameras and ceiling access points."],
                ["The switch has a total power budget in watts, and each port has a limit set by its standard: 802.3af gives up to about 15 W, 802.3at about 30 W and 802.3bt more again.",
                 "Each PoE switch has an overall budget and per-port limits; 802.3af ports supply roughly 15 W, 802.3at roughly 30 W and 802.3bt up to 60 W or more.",
                 "PoE comes in classes: 802.3af at about 15 W a port, 802.3at at about 30 W and 802.3bt for higher-power kit, all within the switch's total budget."],
            ],
            [
                ["We add up the worst-case draw of every device, including camera heaters, infrared at night and pan-tilt motors, and leave spare capacity for one or two more.",
                 "The budget is worked out from each device's maximum draw, with infrared, heaters and motors included, plus headroom for later additions.",
                 "We total the maximum figures from the datasheets, not the typical ones, because night-time infrared and heaters push draw up."],
                ["A switch that runs out of budget switches ports off, which looks like cameras dropping at night or a door station rebooting.",
                 "When a budget is exceeded the switch cuts power to some ports, often at night, and cameras appear to go offline for no reason.",
                 "An undersized switch is a common cause of cameras that vanish after dark in {town} properties we are called to."],
                ["On longer runs we also check cable grade and length, since voltage drop on thin or copper-clad cable can starve a device.",
                 "Cheap copper-clad aluminium cable and long runs cause voltage drop, so we use solid copper for PoE.",
                 "We only use solid copper cable for PoE; copper-clad aluminium heats up and drops voltage."],
            ],
        ],
    },
    "mesh-vs": {
        "h2": [
            "Mesh, extenders, boosters or wired access points",
            "Mesh Wi-Fi compared with boosters and extenders",
            "Which Wi-Fi fix fits a {town} home or office",
        ],
        "paras": [
            [
                ["A plug-in extender or booster repeats the router's signal, and it usually halves the speed for devices connected through it because it receives and sends on the same radio.",
                 "Plug-in extenders and boosters are cheap, but most repeat the signal on one radio, so devices behind them get far less speed than the router delivers.",
                 "A booster or extender listens to the router and rebroadcasts it, which costs speed and adds delay, especially through two walls."],
                ["Mesh systems use several nodes that work as one network with one name, and better ones keep a dedicated radio for the link between nodes.",
                 "Mesh Wi-Fi spreads several nodes around the property under one network name, and devices move between them as you walk around.",
                 "A mesh kit puts two or more matched nodes around the building, handing devices between them without a second network name."],
                ["The best results come when the nodes are joined by Ethernet backhaul, a cable between each node and the router, rather than by wireless.",
                 "Mesh works best with wired backhaul: each node gets a cable to the router, so its radios serve devices instead of relaying.",
                 "Wire the nodes together with Ethernet backhaul and a mesh system performs close to wired access points."],
            ],
            [
                ["Wired ceiling access points, powered by PoE from a switch, are the step up for offices, HMOs and larger {town} houses.",
                 "For offices, HMOs and bigger {town} homes, ceiling-mounted access points on PoE give the most even coverage.",
                 "Ceiling access points fed by PoE are what we fit when a {town} building needs coverage on every floor and room."],
                ["They are managed together, so channels and power are set to stop neighbouring units fighting each other.",
                 "They are set up as one system, with channel and power planning so they cover rooms without interfering.",
                 "Each unit is tuned for channel and transmit power so coverage overlaps cleanly."],
                ["We will say plainly when a simple mesh pair is enough and when cabling access points is worth it.",
                 "If a two-node mesh will fix the problem, we say so; if the building needs cabled access points, we explain why.",
                 "Not every job needs cabling: sometimes moving the router and adding one mesh node is the answer."],
            ],
        ],
    },
    "dead-spots": {
        "h2": [
            "What causes Wi-Fi dead spots",
            "Why the signal dies upstairs or at the back",
            "Dead spots in {town} buildings and how we find them",
        ],
        "paras": [
            [
                ["Solid brick and block walls, chimney breasts and concrete floors absorb Wi-Fi, which is why the back bedroom or the loft conversion often has no signal.",
                 "Thick brick walls, chimney breasts and concrete floors soak up the signal, so rooms two walls away from the router struggle.",
                 "Most dead spots come from the building itself: solid walls, chimney breasts, concrete floors and steel."],
                ["Foil-backed insulation boards, mirrored wardrobes, underfloor heating and metal stud walls block it further, and kitchens add fridges and microwaves.",
                 "Foil-backed plasterboard and insulation, mirrors, metal studs and underfloor heating pipes all cut signal further.",
                 "Foil-faced insulation, metal stud partitions, mirrors and appliances add to the loss, especially in extensions."],
                ["Garden offices, garages and outbuildings in {town} are usually out of reach of an indoor router altogether.",
                 "Outbuildings, garden rooms and garages rarely get a usable signal from a router inside the house.",
                 "An outbuilding or garden office normally needs its own cabled or point-to-point link, not a booster on the windowsill."],
            ],
            [
                ["Router position matters as much as the walls: a hub in a cupboard, behind the TV or by the front door at floor level loses much of its range.",
                 "Where the hub sits makes a big difference; one left in a meter cupboard or on the floor by the front door covers far less.",
                 "Hubs are often left where the line comes in, low down by the front door, which is one of the worst places for coverage."],
                ["Neighbouring networks on the same channel, common in {town} terraces and flats, also slow things down.",
                 "In {town} flats and terraces, dozens of neighbouring networks compete for the same channels.",
                 "Busy channels from neighbours in {town} terraces and blocks add interference that looks like a weak signal."],
                ["We measure signal and speed room by room, then fix the cause rather than just adding hardware.",
                 "A walk-round with a meter shows where the signal drops, so the fix targets the real cause.",
                 "We test each room first, so you only pay for the fix the building actually needs."],
            ],
        ],
    },
    "sky-bt": {
        "h2": [
            "CCTV and door entry on a Sky or BT hub",
            "Works with your Sky or BT hub: what to know",
            "Router swaps, port forwarding and cloud apps",
        ],
        "paras": [
            [
                ["Every recorder, camera and video door station we fit works with your Sky or BT hub; the question is how the app reaches it from outside.",
                 "CCTV and door entry we install works with your Sky or BT hub, and the important choice is how remote viewing is set up.",
                 "The kit works with your Sky or BT hub; what matters is the remote-access method."],
                ["Port forwarding opens a port on the router to the recorder, which works but exposes the device and breaks whenever the router or its address changes.",
                 "Port forwarding points a router port at the recorder; it works, but it puts the device on the internet and fails if the router is replaced.",
                 "The old method is port forwarding, which opens the recorder to the internet and needs redoing with every new router."],
                ["Cloud or P2P apps connect outwards from the recorder instead, so nothing is opened on the router and the app keeps working after most changes.",
                 "Cloud or P2P connection has the recorder dial out to the maker's service, so no ports are opened and the app survives most router changes.",
                 "We prefer cloud or P2P access: the device connects out, nothing is opened inbound, and it copes with router changes better."],
            ],
            [
                ["We also set a DHCP reservation on the hub so the recorder or door station always gets the same local address.",
                 "A DHCP reservation on the router keeps the recorder on a fixed local address.",
                 "We reserve the device's address in the hub's DHCP settings so it does not move."],
                ["Without one, a power cut or hub restart can give the recorder a new address and the app, or any forwarded port, stops finding it.",
                 "If that is not done, a restart can hand out a different address and remote viewing quietly stops.",
                 "Without a reservation, the address can change after a reboot and remote viewing drops."],
            ],
            [
                ["When a provider swaps the hub, which happens on upgrades and moves between Sky and BT, the Wi-Fi name, password, reservations and port rules all change.",
                 "A new hub from Sky or BT arrives with a new network name, password and blank settings, so Wi-Fi cameras and door stations lose their connection.",
                 "Router swaps reset everything: Wi-Fi details, address reservations and any port forwarding."],
                ["Wi-Fi cameras and doorbells drop off, forwarded ports vanish and the app shows the recorder offline even though it is still recording locally.",
                 "Cameras on Wi-Fi disconnect, port rules are gone and the app reports the system offline, while the recorder still records on site.",
                 "The result is an app that says offline, even though the recorder in the cupboard is still recording."],
                ["We reconnect each device, set reservations on the new hub, move remote viewing to cloud or P2P where the recorder supports it, and write the settings down for next time.",
                 "On a visit we rejoin each device, redo the reservations, switch to cloud access where possible and leave a written note of the settings.",
                 "We rejoin the devices, re-reserve addresses, move to cloud or P2P where we can and leave the settings on record for the next swap."],
            ],
        ],
    },
    "hmo-split": {
        "h2": [
            "Landlords and HMOs: tenant broadband and building systems",
            "Keeping tenant Wi-Fi apart from building systems",
            "Shared houses and blocks in {town}",
        ],
        "paras": [
            [
                ["In an HMO or block, tenant broadband and building systems such as CCTV, door entry and fire alarm signalling should not share one open network.",
                 "In shared houses and blocks we keep tenant internet separate from the landlord's CCTV, door entry and other building systems.",
                 "Tenant Wi-Fi and building systems are better kept apart, so a tenant cannot unplug or reach the CCTV recorder."],
                ["We use a separate network or VLAN for building kit, a guest or tenant network for residents, and the cabinet in a landlord-only cupboard.",
                 "That usually means a separate VLAN or a second router for building systems, tenant Wi-Fi on its own network, and a locked cabinet.",
                 "The usual set-up is one network for building equipment, one for tenants, and the switch locked away in a landlord space."],
                ["It also makes it clear who owns which line when a {town} tenant moves out or a provider contract changes.",
                 "It keeps ownership clear when tenants change or a line is cancelled, which is a common cause of cameras going offline in {town} HMOs.",
                 "Clear separation avoids the familiar problem of the building's cameras riding on a tenant's line that is then cancelled."],
            ],
        ],
    },
    "survey-visit": {
        "h2": [
            "How a visit works in {town}",
            "Booking, the visit and what you get",
            "Survey, quote and handover",
        ],
        "paras": [
            [
                ["Send the address, what is not working or what you want added, and photos of the router, cabinet and problem rooms if you can.",
                 "Start by sending the address, a short description and, if possible, photos of the hub, any cabinet and the rooms affected.",
                 "Tell us the address and the problem or plan; photos of the router position and the rooms help us scope it."],
                ["Same-day and next-day visits are available, and price is on application once we know the building and the scope.",
                 "We offer same-day and next-day slots, and the price is on application after the scope is agreed.",
                 "Same-day or next-day visits can usually be arranged; the cost is price on application, set out in writing before work starts."],
                ["Call or WhatsApp 07517 806082, or email icomplypropertyservices@gmail.com.",
                 "Phone or WhatsApp 07517 806082, or email icomplypropertyservices@gmail.com.",
                 "You can reach us on 07517 806082 by phone or WhatsApp, or at icomplypropertyservices@gmail.com."],
            ],
            [
                ["At handover you get the test results, a labelled plan of points and access points, and the network names and admin details stored with you, not with us.",
                 "You are left with a list of what was fitted, the test results, labels that match the plan, and the admin passwords handed to you.",
                 "At the end you get test results, a simple plan of each point and node, and all passwords."],
                ["Cables are clipped or in trunking, holes are made good, and old boosters or loose leads we replace are taken away.",
                 "We tidy as we go: cables are fixed, holes filled and replaced kit removed.",
                 "Work is left neat, with cable fixed, holes made good and old kit removed if you want it gone."],
            ],
        ],
    },
    "office": {
        "h2": [
            "Small office and shop networks",
            "Networks for {town} offices, shops and clinics",
            "Switches, guest Wi-Fi and the comms cabinet",
        ],
        "paras": [
            [
                ["A small office needs a router, a managed switch sized for the ports in use, and wired points for desks, printers and phones.",
                 "For a small office we plan the router, a switch with spare ports, and wired points for desks, printers and card machines.",
                 "Offices and shops usually need a proper switch, wired desks and printers, and Wi-Fi from ceiling access points."],
                ["Guest Wi-Fi goes on its own network so visitors never see the office PCs, tills or cameras.",
                 "Visitors get a separate guest network, isolated from the office machines and the till.",
                 "Guest and staff Wi-Fi are split so customers cannot reach office devices."],
                ["A small UPS keeps the router and switch up through short power blips, which stops card machines and phones dropping.",
                 "A small UPS on the cabinet rides through power blips that otherwise reboot the router and drop calls.",
                 "We often add a small UPS so brief power dips do not take the network down."],
            ],
        ],
    },
    "troubleshooting": {
        "h2": [
            "Finding the fault, not guessing",
            "How we troubleshoot a slow or dropping network",
            "Fault finding in {town}",
        ],
        "paras": [
            [
                ["Slow or dropping networks usually come down to a short list of causes: a failing cable or termination, a loop between switch ports, a duplicate address, an overloaded PoE switch, or Wi-Fi channel clashes.",
                 "Most network faults trace back to a damaged cable, a bad termination, a switch loop, an address clash, an overloaded PoE budget or crowded Wi-Fi channels.",
                 "When a network is slow or keeps dropping, the cause is usually physical cabling, a switch loop, an address conflict, PoE overload or Wi-Fi interference."],
                ["We test the line speed at the hub first, then each cable, port and access point in turn, so the fix is aimed at the real problem.",
                 "We start at the incoming line, then test cables, switch ports and Wi-Fi in order, ruling things out one by one.",
                 "The method is simple: test the line, then the cabling, then the switch and then the Wi-Fi, recording each result."],
                ["If the fault is with the broadband line itself, we give you the test results to take to the provider.",
                 "Where the problem is the provider's line, you get the readings to show them.",
                 "If it turns out to be the line, we give you the evidence to send to your provider."],
            ],
        ],
    },
}

# Topics every page covers, in default order, per service.
SERVICE_TOPICS = {
    "networking": ["office", "troubleshooting", "data-points", "cable-grades", "poe", "sky-bt", "hmo-split", "mesh-vs", "dead-spots", "distance-testing", "survey-visit"],
    "structured-cabling": ["cable-grades", "distance-testing", "data-points", "poe", "hmo-split", "sky-bt", "mesh-vs", "dead-spots", "office", "survey-visit"],
    "wifi": ["mesh-vs", "dead-spots", "sky-bt", "cable-grades", "poe", "hmo-split", "data-points", "troubleshooting", "survey-visit"],
}

# Topic FAQs (question, answer). Seeded picks add 2-3 of these to each page.
TOPIC_FAQS = {
    "cable-grades": [
        ("Is Cat5e still good enough?", "For gigabit links to desks, TVs, cameras and door stations, yes. We fit Cat6 for most new points because it gives more headroom for little extra cost, and Cat6a or fibre where 10 Gb/s or long runs are needed."),
        ("When do I need fibre instead of copper?", "Between buildings, to outbuildings, up tall risers or on any route longer than the 90 m copper limit. Fibre carries no power, so the far end needs a powered switch or converter."),
    ],
    "distance-testing": [
        ("How long can an Ethernet cable run be?", "90 m of fixed cable from patch panel to outlet, plus patch leads, for a 100 m channel. Longer runs need fibre or a switch part way."),
        ("Do you test the cables?", "Yes. Every run is tested end to end after termination, labelled at both ends and recorded on the handover sheet."),
    ],
    "data-points": [
        ("Can you add data points without chasing walls?", "Often, yes. We drop cable inside stud walls, use floor voids and loft space, or fit neat surface trunking where walls are solid."),
        ("Where should the patch panel go?", "Somewhere ventilated with power, usually a cupboard, under-stairs space or a small wall cabinet. In HMOs and blocks it goes in a landlord-only cupboard."),
    ],
    "poe": [
        ("What is a PoE budget?", "The total power a PoE switch can supply across its ports. We add up the maximum draw of every camera, access point and door station, including night-time infrared, and leave headroom."),
        ("Why do my cameras drop at night?", "A common cause is a PoE switch running out of power budget when infrared and heaters come on. Thin or copper-clad cable on long runs is another."),
    ],
    "mesh-vs": [
        ("Is mesh better than a Wi-Fi booster?", "Usually. A booster repeats the signal and roughly halves speed for devices behind it; a mesh system works as one network, and with wired backhaul it performs far better."),
        ("Do mesh nodes need cables?", "They work without, but joining each node to the router with Ethernet backhaul gives much better speed and stability."),
    ],
    "dead-spots": [
        ("Why is there no Wi-Fi upstairs?", "Concrete or joisted floors, chimney breasts, foil-backed insulation and the hub's position by the front door are the usual causes. We measure each room before recommending a fix."),
        ("Can you get Wi-Fi to a garden office or garage?", "Yes, usually with a buried or clipped Ethernet or fibre link and an access point inside, or a point-to-point wireless bridge where cabling is impractical."),
    ],
    "sky-bt": [
        ("Will my CCTV work with a Sky or BT hub?", "Yes. The systems we fit work with your Sky or BT hub. We set a DHCP reservation and use the recorder's cloud or P2P app where it has one, so remote viewing is not tied to port forwarding."),
        ("Why did my cameras go offline after a new router?", "A new hub has a new Wi-Fi name and password and none of the old address reservations or port rules. We reconnect the devices and set them up again on the new hub."),
        ("Port forwarding or cloud viewing?", "Cloud or P2P where the recorder supports it. Port forwarding works but exposes the recorder and breaks when the router changes."),
    ],
    "hmo-split": [
        ("Should tenant Wi-Fi share a network with the CCTV?", "No. We put building systems on their own network or VLAN, give tenants a separate network, and lock the cabinet in a landlord space."),
    ],
    "survey-visit": [
        ("How much does it cost?", "Price on application. We confirm the scope first, then send a written quote before any work starts."),
        ("How quickly can you come out?", "Same-day and next-day visits are available. Call or WhatsApp 07517 806082 or email icomplypropertyservices@gmail.com."),
    ],
    "office": [
        ("Can visitors use our Wi-Fi safely?", "Yes, on a separate guest network that cannot reach office PCs, tills or cameras."),
    ],
    "troubleshooting": [
        ("What if the problem is my broadband line?", "We test the line at the hub first. If the fault is on the provider's side, we give you the readings to take to them."),
    ],
}
