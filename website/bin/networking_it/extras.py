"""Extra family-level copy so every P0 hub clears the 800-word body floor with
property-specific material (building types, price factors, conduct on site)."""

PROPERTY_TYPES = {
    "it-support": [
        ("Letting agents and property managers", "A lettings office lives on its email, its property management software and the shared drive holding tenancy files. For {cn} we check that every desk can reach those systems, that former staff no longer have access and that tenant data is not sitting on a laptop that leaves the building every night."),
        ("Landlords and HMO operators", "Landlords often run a small business from home or a site office, with broadband in each property and a CCTV recorder or door-entry panel on the same router. {Cn} keeps the landlord's own systems apart from tenant broadband, so a tenant problem never becomes a business outage."),
        ("Offices and serviced suites", "In a small office a single faulty laptop, printer or mailbox can stop a whole team. {Cn} looks at the shared pieces first: the router, the switch under the desk, the printer's address and the licences, because those are the parts that break several people's day at once."),
        ("Managed blocks and commercial buildings", "Managing agents in blocks and mixed-use buildings deal with site offices, concierge desks and plant-room systems that need a working connection. {Cn} records who owns each device and contract so the managing agent, the freeholder and the tenants are not paying for the same thing twice."),
    ],
    "networking": [
        ("Offices", "Office networks grow one switch at a time. For {cn} we trace each cable, label each port and remove the loops and spare switches under desks that slow everyone down."),
        ("HMOs and shared houses", "In HMOs the network has to serve several households fairly. {Cn} separates tenant traffic from landlord devices and keeps the broadband router, any CCTV recorder and the door-entry panel on their own segments."),
        ("Blocks and managed buildings", "Blocks carry building systems on the network: door entry, access control, CCTV and sometimes lift or alarm signalling. {Cn} maps those systems before any change and agrees with the managing agent which devices must never lose connection."),
        ("Shops, warehouses and yards", "Card terminals, tills, stock scanners and yard cameras all need a connection that stays up. {Cn} in commercial premises plans for distance, dust and heat as well as the number of devices."),
    ],
    "wifi": [
        ("HMOs and shared houses", "Tenants in the top-floor rooms of a three-storey HMO are usually the ones complaining. {Cn} plans coverage room by room, with a separate network for tenants and another for any landlord devices such as cameras or smart heating."),
        ("Offices and meeting rooms", "Office Wi-Fi has to cope with every laptop, phone and video call at once. {Cn} looks at capacity and roaming as well as signal strength, because a full meeting room can fail even with five bars of signal."),
        ("Blocks and communal areas", "Communal Wi-Fi in blocks, receptions and lounges needs to be separate from residents' own broadband and from building systems. {Cn} keeps those networks apart and records who is responsible for each."),
        ("Warehouses, yards and outbuildings", "Long distances, racking and metal cladding are hard on Wi-Fi. {Cn} in commercial and industrial space often needs outdoor-rated units, careful placement and cabled backhaul."),
    ],
    "structured-cabling": [
        ("Office fit-outs", "Fit-outs are the cheapest time for {cn} because ceilings, floors and walls are open. We coordinate routes with the main contractor and the electrician so data and mains stay separated."),
        ("Refurbished HMOs and conversions", "Conversions often need data points in every let room, a cabinet in a cupboard and runs that cross fire-stopped floors. {Cn} records every penetration that needs reinstating."),
        ("Blocks and managed buildings", "In blocks the riser and communal ceilings carry the backbone. {Cn} in common parts is agreed with the managing agent, including access, asbestos records and making good."),
        ("Commercial and industrial units", "Warehouses and workshops need cable protected from damage and kept within distance limits. {Cn} there may mix copper outlets with fibre links between buildings."),
    ],
}

PRICE_FACTORS = {
    "it-support": "What changes the price for {cn}: the number of users and devices, whether the work can be done remotely, how many sites are involved, the state of existing documentation and passwords, any licences or hardware needed, and whether work has to happen outside normal office hours. All of these are listed on the quote, which is price on application.",
    "networking": "What changes the price for {cn}: the number of devices and switch ports, whether new cabling is needed, the equipment brand, the number of networks to separate, how much documentation already exists, and any work that must be done outside trading hours. The quote is price on application and lists each item.",
    "wifi": "What changes the price for {cn}: floor area and number of floors, wall and floor construction, the number of users and devices, whether access points can be cabled, the brand chosen and whether a survey is needed first. The quote is price on application and lists each item.",
    "structured-cabling": "What changes the price for {cn}: outlet count, cable category, route lengths, ceiling and floor types, containment, cabinet work, access restrictions in occupied buildings and any fire-stopping to reinstate. The quote is price on application and lists each item.",
}

ON_SITE = {
    "it-support": "On site we work around the people using the systems. Changes that will interrupt staff are agreed first, and anything that needs a restart is planned for a quiet moment. We do not install software or change settings beyond the agreed scope without asking.",
    "networking": "On site we agree a change window before touching live equipment, take configuration backups, and test business systems such as card terminals, door entry and CCTV before and after. Nothing is unplugged in a comms cupboard without labelling it first.",
    "wifi": "On site we walk the building with a measuring device, check the wired links behind each access point and confirm coverage with the client in the rooms that matter most. Ceiling work in occupied areas is agreed in advance.",
    "structured-cabling": "On site we work to method statements and risk assessments supplied with the quote, protect floors and furniture, keep fire doors closed and leave work areas clean each day. Asbestos records are checked before any drilling.",
}

FAMILY_FAQS = {
    "it-support": [("Do you keep our passwords?", "The business keeps owner-level access. Where we need admin access we use named accounts and hand them back or disable them at the end."),
                   ("Can you help with GDPR?", "We follow the access and data rules the business sets and can advise on technical measures. Legal compliance stays with the business.")],
    "networking": [("Will the network go down during the work?", "Short interruptions are sometimes unavoidable; they are planned and agreed in advance."),
                   ("Do you work on CCTV and door-entry networks?", "We handle the network side. Camera and panel programming stays with the CCTV, access control or door-entry service.")],
    "wifi": [("Can tenants have their own Wi-Fi network?", "Yes. Tenant networks can be separated from landlord or business devices, with terms set by the landlord."),
             ("Do you install outdoor Wi-Fi?", "Outdoor-rated units for yards, car parks and gardens can be scoped after a survey.")],
    "structured-cabling": [("Do you do the electrical work as well?", "Electrical supplies are scoped under our electrical service and certified separately."),
                           ("Is CCTV cabling included?", "We can run the data cabling for cameras. Camera supply and set-up stay with the CCTV service; this is network-side work only.")],
}

# Bluetooth cores sit under the Wi-Fi & Bluetooth service but need their own wording.
PROPERTY_TYPES["bluetooth"] = [
    ("Offices and hot desks", "Shared desks mean headsets and keyboards get paired to the wrong laptop. {Cn} names each device, the desk it belongs to and the computer it should connect to, so the next person does not inherit someone else's pairing."),
    ("Meeting and conference rooms", "Speakerphones, room cameras and presentation dongles are the usual meeting-room complaints. {Cn} tests them in the calling app the business actually uses, with the room's own screen and laptop."),
    ("Letting offices and site offices", "Card readers, label printers and barcode scanners used by lettings and maintenance teams often rely on Bluetooth. {Cn} checks the device, the phone or tablet app and the account behind it."),
    ("Busy radio environments", "Bluetooth shares the 2.4 GHz band with Wi-Fi, cordless kit and some wireless cameras. {Cn} in busy offices and blocks checks for interference as well as pairing."),
]
PRICE_FACTORS["bluetooth"] = "What changes the price for {cn}: the number of devices, how many computers or phones each must pair with, whether firmware updates are needed, the apps involved and whether a visit is required or the job can be done remotely. The quote is price on application and lists each item."
ON_SITE["bluetooth"] = "On site we pair and test each device with the person who uses it, update firmware only with the owner's agreement and leave short reconnect instructions at each desk or meeting room."
FAMILY_FAQS["bluetooth"] = [("Do you supply Bluetooth headsets or speakerphones?", "We can advise and supply, or set up devices the business already owns."),
                            ("Can Bluetooth devices interfere with Wi-Fi?", "They share a band, so heavy use can affect each other; channel planning on the Wi-Fi side usually helps.")]
