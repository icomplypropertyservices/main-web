"""Extra modifier-specific paragraphs (two per search modifier) so pages that
share a job core still carry their own material. {cn} = core name, {name} = page name."""

MOD_EXTRA = {
    None: [
        "Start here if you are not sure which job you need. The guide sets out the usual causes, the order we check things in and the paperwork you keep, and the related guides further down split the work into narrower jobs such as repairs, surveys or set-ups.",
        "If a previous contractor has left notes, passwords or drawings, send them with the enquiry. Existing records shorten the first visit and often change the quote, because less time is spent rediscovering what is already in the building.",
    ],
    "near-me": [
        "Searching 'near me' from a phone usually means something has already gone wrong at the building. Tell us the town and postcode first so we can confirm it is inside the 269-town list, then the symptoms, then who can let an engineer in.",
        "Being local matters most for repeat visits. A provider based in Stockport can come back to the same building, already knowing where the comms cupboard is, which router the broadband company fitted and which devices were added since the last visit.",
    ],
    "near-me-2": [
        "For a desktop that will not boot, write down any beeps, lights or on-screen messages before switching it off again. Those details often point to the failed part and let us bring the right spare on the first visit.",
        "If the machine holds the only copy of business files, say so straight away. We will not reinstall, wipe or swap a drive until the data question is answered, even if that makes the repair slower.",
    ],
    "near-me-3": [
        "IT services cover more than repairs: accounts for new staff, laptops for leavers to hand back, licences that renew, backups that need checking and broadband contracts that expire. A local provider can pick up all of these rather than a separate supplier for each.",
        "When comparing providers near you, ask who will actually attend, whether they keep notes on your set-up, how passwords are handed back at the end and what is excluded from the quote. We answer those questions in writing before you instruct us.",
    ],
    "urgent": [
        "An emergency for one business is routine for another. We ask what has stopped: is nobody able to work, is one person locked out, or is a building system such as door entry or CCTV offline? The answer decides whether we start remotely, send someone or suggest a temporary workaround.",
        "Keep a note of what changed just before the failure: a power cut, a builder in the cupboard, a new device, a software update or a broadband engineer visit. That one line often saves more time than anything else in an emergency.",
    ],
    "urgent-2": [
        "Urgent requests are triaged by impact. A whole office unable to send email or reach the shared drive is handled differently from one slow laptop. Telling us how many people are affected helps us suggest the right first step.",
        "While waiting, check the simple things we would ask about anyway: is the router showing its normal lights, has the broadband provider posted an outage, and does the fault affect wired and wireless devices or only one of them.",
    ],
    "call-out": [
        "A call-out is a visit for a fault that cannot be dealt with remotely. Before we come out we ask for photos of the comms cupboard, the error messages and the devices involved, so the engineer can bring likely spares and the right cables.",
        "Call-outs are logged in the same way as planned visits: what was found, what was fixed, what was left and what is quoted next. If the same fault comes back, the history shows whether it is the same cause or a new one.",
    ],
    "same-day": [
        "The fastest route to a fix is usually remote. If we can connect to the affected computer with the user's consent, many account, email and software faults are dealt with in the same call. Hardware faults need someone at the building.",
        "If a visit is needed and an engineer is free, we will say so. If not, we will say that too, and suggest a workaround such as a spare laptop, a mobile hotspot or forwarding email, so the business can keep going.",
    ],
    "out-of-hours": [
        "Planned work outside office hours suits jobs that would interrupt staff: server updates, firewall swaps, migrations and network cut-overs. These are booked in advance with a written plan, a rollback step and a named contact at the business.",
        "For faults outside office hours, send the details by phone message, WhatsApp or the form. The message is picked up when someone is available, and we reply with the next step rather than promising a time.",
    ],
    "out-of-hours-2": [
        "Searching for 24-hour help usually means a system failed in the evening or at a weekend. Most small offices can wait for the next working session if email is forwarded and staff know which systems to avoid, and we will advise on that when you call.",
        "Where a business genuinely needs out-of-hours cover, such as a site office that runs at weekends, the hours and arrangements are agreed in a written contract and priced on application, rather than implied by a page title.",
    ],
    "business": [
        "Business IT is about the systems that earn money: the accounts package, the CRM or property software, email and the shared files. We rank faults by their effect on those systems, not by who shouts loudest.",
        "Growing businesses often add staff faster than they add IT. We check that each new person has their own account, the right licence, multi-factor sign-in and a device that is patched, because shared logins and hand-me-down laptops cause most of the problems we are called about.",
    ],
    "small-business": [
        "A small business with five or ten people usually has one person who 'does the IT' as well as their real job. We work with that person, take the routine work off them and leave notes they can follow.",
        "For small firms we keep the set-up simple: one antivirus product, one backup method, one place for files and one admin account owned by the business. Simple set-ups break less and are cheaper to look after.",
    ],
    "office": [
        "In an office the common faults are predictable: the printer that disappears after a power cut, the meeting-room screen that will not show a laptop, the Wi-Fi that drops at the far end of the floor and the new starter whose laptop is not ready on day one.",
        "Office moves and refits are the best time to fix long-standing problems. If you are moving floors or buildings, tell us early so cabling, broadband and desk set-ups are planned together.",
    ],
    "commercial": [
        "Commercial premises add their own systems to the network: tills and card terminals, stock scanners, CCTV recorders, access control and sometimes building management controllers. A change made for the office PCs must not stop those.",
        "Commercial clients also tend to have more than one party involved: tenant, landlord, managing agent and specialist suppliers. The quote says who instructs the work, who pays and who must be told before anything is switched off.",
    ],
    "town:Manchester": [
        "Manchester jobs range from serviced offices in Spinningfields and Piccadilly to converted mills in Ancoats and student houses in Rusholme. Older buildings often have thick walls and awkward cable routes that affect both network and Wi-Fi work.",
        "Parking and access in the city centre are planned ahead. Tell us about loading bays, building management sign-in and any permits needed, so the visit is not lost to access problems.",
    ],
    "town:Stockport": [
        "Stockport jobs are close to base, which helps with follow-up visits and collecting kit for repair. We cover the town centre, Edgeley, Heaton Moor, Cheadle Hulme, Hazel Grove, Marple and the rest of the SK postcodes.",
        "Many Stockport clients are landlords and small offices in converted houses and older commercial buildings, where broadband enters in odd places and Wi-Fi struggles through solid walls.",
    ],
    "town:Burnley": [
        "Burnley and Pendle have a lot of stone-built terraces converted to HMOs and small offices. Thick walls make Wi-Fi and cabling routes the usual topics, alongside everyday IT support.",
        "Jobs in Burnley are diary-booked from Stockport. Grouping several properties or tasks into one visit makes the best use of the trip, so tell us about anything else that needs looking at.",
    ],
    "engineer": [
        "Our engineers carry test equipment for cables and Wi-Fi as well as the usual tools, so a visit can confirm whether the fault is in the device, the cable or the network rather than guessing.",
        "Before leaving site the engineer goes through the visit note with the client contact: what was done, what is still outstanding and what will be quoted. Nobody should have to ring afterwards to find out what happened.",
    ],
    "engineer-2": [
        "People search for an IT engineer when they want a person, not a ticket number. We tell you who is coming, what they will need access to and what they will bring.",
        "IT engineers on our visits also check the basics around the reported fault: whether the device is still supported, whether its updates are current and whether its data is backed up. Anything found is noted, not fixed without agreement.",
    ],
    "local": [
        "Local support also means local knowledge: which broadband providers serve the street, which buildings have awkward cable routes and which landlords and agents already have records with us.",
        "If you have several properties in neighbouring towns, we can group them into one scope with a record for each building, so support stays consistent across the portfolio.",
    ],
    "landlord": [
        "For HMOs, tenant broadband is often included in the rent. When it fails, the landlord gets the calls. We set up the network so that faults are easier to diagnose remotely and tenants know who to contact.",
        "Landlords with lettings software, rent accounts and tenant records on a laptop are holding personal data. We help make sure that laptop is encrypted, backed up and protected by multi-factor sign-in.",
    ],
    "msp": [
        "MSPs are judged on what they actually do each month. Our reports list patches applied, backups checked, tickets closed and risks found, so you can see the work rather than trust a monthly fee.",
        "If you are moving from another MSP, we ask for their documentation, admin accounts and licence list, and agree a changeover date so nothing is left without cover.",
    ],
    "fully": [
        "A fully managed service suits businesses with no internal IT person. We become the first point of contact for staff, manage suppliers and keep the asset and licence lists up to date.",
        "Fully managed does not mean unlimited. Projects such as office moves, new cabling or migrations are quoted separately, and the contract lists what is and is not covered.",
    ],
    "repair": [
        "Before any server repair we confirm when the last good backup was taken and whether it can be restored. If not, protecting the data comes first and may change the order of work.",
        "Replacement parts for older servers can be hard to find. We check availability before quoting and give the client the option of a newer replacement where parts are scarce.",
    ],
    "office-365": [
        "Old Office 365 business plans have been renamed and some features moved between plans. We check which plan each user has and whether a cheaper or more suitable plan exists for their work.",
        "Common Office 365 support jobs include Outlook profiles that will not open, shared mailboxes that do not appear, calendar permissions and phones that stop receiving email after a password change.",
    ],
    "installation": [
        "For installations we provide a written specification before work starts: cable category, outlet count, containment type, cabinet arrangement and the test standard. That specification is what the finished job is checked against.",
        "Installation in occupied buildings is phased so that staff can keep working. We agree which areas are done when, and any noisy work such as drilling is scheduled with the client.",
    ],
    "optic": [
        "Fibre runs between buildings need protection: ducts, armoured cable or aerial routes depending on the site. We survey the route and agree it with the owner before quoting.",
        "Each fibre core is loss-tested after termination and the results recorded. Connector type and fibre grade are matched to the equipment at each end.",
    ],
}

MOD_EXTRA["near-me-2@managed-it"] = [
    "Managed IT from a nearby provider means the monthly checks are done by people who can also come to the building. When a switch fails or a new starter needs a desk set up, the same team that watches the systems does the visit.",
    "Ask any managed IT provider near you for a sample monthly report and a list of what the fee excludes. Ours lists patching, backup checks, user changes and tickets, with projects quoted separately on application.",
]


def mod_extra(mod, core_key):
    if mod is None:
        return []
    return MOD_EXTRA.get(f"{mod}@{core_key}") or MOD_EXTRA.get(mod, [])
