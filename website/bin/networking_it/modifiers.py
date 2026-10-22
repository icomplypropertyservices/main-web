NO_TIME = ("We do not promise an attendance time, a fix time or a response time on this page. "
           "A visit or remote session is proposed when the diary allows, and the written quote is price on application.")

def modifier_copy(mod, name, core_name):
    """Return (intro, paragraph, [faq, faq]) for the search modifier on a P0 slug."""
    cn = core_name[0].lower() + core_name[1:] if not core_name[:3].isupper() else core_name
    if mod is None:
        return (f"{name} from iComply Property Services, arranged from our Stockport base for offices, letting agents, landlords and managed buildings.",
                f"This page is the main guide to {cn}. It explains what the work covers, how a job is scoped and what you receive at the end. Town pages for the 269 Greater Manchester, Manchester-ring and Burnley-ring towns we cover sit underneath it, so a building in Bolton, Blackburn or Macclesfield has its own local page. Related guides on the same service are linked further down.",
                [(f"Where do you provide {cn}?", "Across Greater Manchester and the 50-mile rings around Manchester and Burnley, 269 towns in total, arranged from Offerton, Stockport SK2."),
                 (f"How do I get a quote for {cn}?", "Send the address, the number of users or devices, what is on site and what the problem or requirement is. The quote is price on application after that scope.")])
    if mod.startswith("near-me"):
        extra = {"near-me": "",
                 "near-me-2": " The same search often comes from someone in a small office with a broken machine and a deadline, so we ask for the make, model and symptoms on the first call.",
                 "near-me-3": " Searches for local IT services usually mean the business wants a provider who can also come to the building, not only a call centre."}[mod]
        return (f"Looking for {cn} near you? iComply works from Stockport across 269 towns in Greater Manchester and the Manchester and Burnley rings.",
                f"'Near me' on this site means a team based at Offerton, Stockport, covering the Greater Manchester boroughs and the towns within 50 miles of Manchester and Burnley. It does not mean a national call centre. Each covered town has its own page, so you can check that your building's town is listed before you call.{extra} {NO_TIME}",
                [(f"Is there {cn} near me?", "If your building is in one of the 269 towns on our dual-ring list, yes. Open the town link on this page or search the areas list."),
                 ("Do you cover towns outside that list?", "Not on these pages. We would rather say no than send someone from too far away.")])
    if mod in ("urgent", "urgent-2", "call-out"):
        label = {"urgent": "emergency", "urgent-2": "urgent", "call-out": "call-out"}[mod]
        return (f"{name}: how {label} requests for {cn} are handled by iComply from Stockport, without attendance-time promises.",
                f"When systems stop, the first step is a call with the business owner or site contact so we understand what has failed and what the business cannot work without. Many {label} faults can be contained remotely first, for example by restoring a mailbox, re-enabling an account or bypassing a failed switch. Where a visit is needed, it is proposed when an engineer is free. {NO_TIME} If we cannot help in time for your needs, we say so straight away so you can make other arrangements.",
                [(f"Do you guarantee a time for {label} {cn}?", "No. We do not publish or promise attendance times. We tell you honestly when someone can help once we know the fault."),
                 (f"What should I do while waiting for {label} help?", "Do not keep restarting equipment or deleting files. Note what happened, photograph error messages and leave the affected device as it is unless it is overheating or smoking.")])
    if mod == "same-day":
        return (f"{name}: how iComply handles requests to look at IT faults today, without promising a slot.",
                f"Many people search for same-day help because something stopped working this morning. We take the details, try remote steps first and offer a visit when an engineer is free. 'Same day' describes what the enquiry asks for; it is not a guaranteed slot. {NO_TIME}",
                [("Can you come today?", "Sometimes, if an engineer is free and the site is within reach. We will tell you honestly when you call."),
                 ("What is the quickest way to get help?", "Phone 07517806082 with the address and fault. Remote help is often the quickest first step.")])
    if mod in ("out-of-hours", "out-of-hours-2"):
        return (f"{name}: how iComply handles IT requests outside office hours, with no round-the-clock attendance promise.",
                f"Enquiries can be sent at any time by phone, WhatsApp or the form. Work outside normal office hours, such as upgrades or migrations that must not interrupt the business, is planned in advance and written into the quote. We do not run a round-the-clock attendance service and this page does not promise one. {NO_TIME}",
                [("Do you offer round-the-clock cover?", "No round-the-clock attendance is offered. Planned out-of-hours work can be arranged and quoted in advance."),
                 ("Can upgrades be done at weekends?", "Yes, when agreed in advance and written into the quote.")])
    if mod in ("business", "small-business", "office", "commercial"):
        who = {"business": "businesses", "small-business": "small businesses with a handful of staff", "office": "offices",
               "commercial": "commercial premises such as shops, offices, warehouses and managed buildings"}[mod]
        extra = {"business": "We scope support around the systems the business depends on, not a generic package.",
                 "small-business": "Small firms rarely need an enterprise contract; they need someone who knows their set-up and writes down what was done.",
                 "office": "Office work covers the desks, printers, meeting rooms and the network cupboard.",
                 "commercial": "Commercial sites often share networks with CCTV, access control and tills, so changes are planned carefully."}[mod]
        return (f"{name} for {who}, arranged from Stockport across Greater Manchester and the Manchester and Burnley rings.",
                f"{extra} We start with a short audit of users, devices, accounts, cabling and suppliers, then quote the work on application. Hardware, licences and third-party contracts are listed as separate lines so the business can see where the money goes.",
                [(f"Is {cn} for {who} priced per user?", "Price on application after the audit. Some jobs suit per-user pricing, others per visit or per outlet."),
                 ("Can you take over from our current provider?", "Yes. We agree a handover date, collect passwords and documentation, and document the set-up.")])
    if mod.startswith("town:"):
        town = mod.split(":", 1)[1]
        notes = {"Manchester": "Manchester city-centre offices, Northern Quarter and Ancoats conversions, student HMOs in Fallowfield and Withington, and managed blocks across the city all ask for IT support.",
                 "Stockport": "Stockport is our home base at Offerton, SK2. Offices in the town centre, Cheadle and Bramhall, and landlords across SK1 to SK8, are the closest jobs to our base.",
                 "Burnley": "Burnley sits at the centre of our second 50-mile ring. Town-centre offices, landlords with terraced HMOs and businesses in Padiham and Nelson are covered from Stockport."}[town]
        return (f"IT support in {town} for offices, landlords and managed buildings, arranged by iComply from Stockport.",
                f"{notes} Visits are diary-booked from Stockport and remote help is offered where it fixes the problem. The {town} town page under this guide gives local detail. {NO_TIME}",
                [(f"Do you cover all of {town}?", f"Yes, {town} is on our dual-ring list. Neighbouring towns have their own pages."),
                 (f"Is there a {town} office?", "No. The office is 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE.")])
    if mod in ("engineer", "engineer-2"):
        return (f"{name}: what an iComply IT engineer does on site, and how a visit is scoped from Stockport.",
                "An IT engineer visit is for the jobs that need hands on hardware: replacing a failed switch, setting up desks for new starters, tracing cables, fixing printers or getting a meeting room working. We send a checklist before the visit so the engineer has access to the rooms, cupboards and passwords needed. " + NO_TIME,
                [("What does an IT engineer bring?", "Tools, test equipment and common cables. Specific parts are confirmed and quoted before the visit."),
                 ("Can the engineer show staff how things work?", "Short how-to sessions can be included in the visit scope.")])
    if mod == "local":
        return ("Local IT support from a Stockport-based team covering 269 towns in Greater Manchester and the Manchester and Burnley rings.",
                "Local means a team that can come to the building, knows the area and keeps records of your set-up between visits. Our base is Offerton, Stockport SK2. " + NO_TIME,
                [("Why choose local IT support?", "A local team can visit when remote help is not enough and builds up knowledge of your set-up."),
                 ("Is local support more expensive?", "Not necessarily. Price on application after scope.")])
    if mod == "landlord":
        return ("IT support for landlords, letting agents and HMO operators: tenant broadband, site offices, CCTV recorders and the systems behind a lettings business.",
                "Landlords need IT support for two different things: their own business systems (laptops, email, lettings software) and the shared systems in properties (tenant broadband, HMO Wi-Fi, CCTV recorders, door entry). We keep those separate in the scope, because who pays and who has access differs. Tenant-owned devices are not supported unless the landlord instructs it.",
                [("Do you support tenants' own devices?", "Not by default. The landlord or agent can instruct it in writing for shared systems."),
                 ("Can you look after broadband across several HMOs?", "Yes, each property is listed in the scope with its own network record.")])
    if mod in ("msp", "fully"):
        return (f"{name}: a managed service provider approach for small property and office businesses, scoped in writing from Stockport.",
                "A managed service provider (MSP) looks after IT on an agreed schedule rather than per fault. 'Fully managed' means the provider owns the day-to-day running of devices, accounts, security updates and backups, while the business keeps ownership of its accounts and data. Our scope lists every task so you can check it is being done.",
                [("What is an MSP?", "A managed service provider that runs IT for a business under an agreed scope."),
                 ("Do we keep our admin passwords?", "Yes. The business keeps owner-level access; we work with named admin accounts.")])
    if mod == "repair":
        return ("Server repair for small office servers and NAS units, diagnosed first and quoted on application from Stockport.",
                "Server faults range from a failed disk in a RAID array to a corrupt update or a dead power supply. We protect the data first, diagnose, then quote the repair or recovery. Where the server is near end of life, we also price a replacement or a move to cloud storage so the business can compare.",
                [("Can you recover data from a failed server?", "Often, depending on the fault. Specialist recovery labs are used for physically damaged drives, quoted separately."),
                 ("Should we repair or replace?", "We compare both on the quote.")])
    if mod == "office-365":
        return ("Office 365 support, now Microsoft 365, for mailboxes, Outlook, Teams and licences, from Stockport.",
                "Many businesses still call it Office 365. The service was renamed Microsoft 365, but the support is the same: mailboxes, Outlook profiles, shared calendars, Teams, OneDrive and licences. Older Office 365 plans may need moving to current plans; we explain the options.",
                [("Is Office 365 the same as Microsoft 365?", "Yes, Microsoft renamed most Office 365 plans to Microsoft 365."),
                 ("Can you fix Outlook not syncing?", "Yes, profile, mailbox and licence faults are common fixes.")])
    if mod == "installation":
        return (f"{name}: new installations surveyed, cabled, tested and labelled by iComply from Stockport.",
                "New installation work starts with a survey of routes, outlet positions and cabinet space. We coordinate with fit-out contractors where ceilings and walls are open, because that is the cheapest time to install. Every link is tested and the results handed over with the as-built schedule.",
                [("When is the best time to install cabling?", "During a fit-out or refurbishment, before ceilings and walls close."),
                 ("Do you provide test results?", "Yes, for every link installed.")])
    if mod == "optic":
        return ("Fibre optic cabling between floors, buildings and comms cabinets, installed and loss-tested by iComply.",
                "Fibre optic cable carries data as light, so it covers long distances and is immune to electrical interference. It is used for backbones between floors, links to outbuildings and yards, and runs too long for copper. We choose single-mode or multimode to suit distance and equipment.",
                [("Single-mode or multimode?", "Multimode suits shorter runs inside buildings; single-mode suits longer distances. We advise from the survey."),
                 ("Is fibre fragile?", "It needs correct bend radius and protection, which is part of the installation.")])
    raise KeyError(mod)
