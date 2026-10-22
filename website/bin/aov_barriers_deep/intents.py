"""Intent modules. {thing} = the product/topic label for the page (e.g. "AOV control panel").

No attendance-time promises. POA only.
"""

INTENTS = {
    "guide": {
        "phrase": "explained",
        "paras": [
            "This page sets out what a {thing} is, where it is used and what decides whether it suits a particular building or entrance. It is written for landlords, managing agents, facilities managers and site owners who need to make a decision and want the facts first.",
            "The first step with any {thing} is to describe the site: the address, the building or entrance, who uses it and what is installed now. Photos and rough measurements help. iComply then confirms what a visit would look at and what would be left out.",
            "Options are set out side by side in a written scope. Where there is a choice between products, the published manufacturer data for each is named so the comparison is fair. Prices are on application once the scope is agreed.",
            "Existing equipment is recorded before anything new is proposed. Often a repair, an adjustment or a change of part solves the problem without a full replacement, and the scope says so plainly.",
        ],
        "faqs": [
            ("Where do I start with a {thing}?", "Send the address, a description of the site, photos and any rough measurements. iComply replies with what a visit would cover. The quote is price on application."),
        ],
    },
    "installation": {
        "phrase": "installation",
        "paras": [
            "Installation of a {thing} starts with a survey, not a delivery. The site is measured, the existing equipment and interfaces are recorded, and the product is chosen from the maker's published data to suit what was found. Nothing is ordered until the scope is written and agreed.",
            "On the day, installation covers fixing, wiring or mechanical setting-up, and the checks that prove the {thing} works as intended. The maker's installation instructions are followed, and any departure needed for the site is written down with the reason.",
            "Installation ends with handover: a record of what was fitted, where, with which settings, and the test results. The responsible person or site owner gets that record for their file, along with any maker's documentation supplied with the product.",
            "Occupied buildings and live sites need planning. Access, isolation, residents or staff, and deliveries are agreed in advance. Where work has to be phased, the scope sets out the phases and what stays in service during each one.",
            "Interfaces are where installations go wrong. A {thing} that depends on a fire alarm, an access-control system or a power supply from another contractor is only as good as that link. The interface is named in the scope and tested at handover.",
        ],
        "faqs": [
            ("What is included in {thing} installation?", "Survey, product selection from published data, fixing and setting-up, testing, and a handover record. The written scope lists exactly what is included and what is not."),
            ("Can installation be phased?", "Yes, where a site has to stay in use. The scope sets out each phase and what remains in service."),
        ],
    },
    "installer": {
        "phrase": "installer",
        "paras": [
            "Choosing a {thing} installer is mostly about how they handle the parts before and after the fitting. A good installer measures before ordering, names the product and its published data, explains interfaces with other systems, and leaves a written record at the end.",
            "iComply acts as installer from its Stockport workshop for sites across the UK mainland. It does not claim accreditation badges it does not hold. What it offers is a written scope, products chosen from manufacturer data, and a handover record the next engineer can use.",
            "An installer should be clear about what they are not doing. If a fire alarm, an electrician or a groundworker is needed for part of the job, that is stated up front with who arranges it, so the client is not left managing gaps.",
            "Ask any {thing} installer how they will test the finished work and what you will receive afterwards. The answer should be specific: which tests, against which document, and what the record will show.",
        ],
        "faqs": [
            ("What should I ask a {thing} installer?", "How they measure, which product data they rely on, how they test, what interfaces they cover and what record you get at the end."),
            ("Does iComply hold manufacturer accreditations?", "This page makes no accreditation claims. Work follows published manufacturer instructions and data, with a written scope and record."),
        ],
    },
    "installers": {
        "phrase": "installers",
        "paras": [
            "When comparing {thing} installers, compare scopes, not headline prices. Two quotes that look different may cover different things: one includes testing and records, another leaves them out; one includes the interface with the fire alarm or access control, another assumes someone else will do it.",
            "A fair comparison needs the same information in each quote: measured dimensions, the named product and its published data, the tests to be carried out, the documents handed over, and any exclusions. iComply's scope is written so it can be compared line by line with others.",
            "Large or multi-site clients often use several installers. Consistent records across sites matter more than any single job. iComply uses the same record format on every site so a portfolio can be managed from one set of documents.",
            "Installers should be able to explain how they deal with surprises: an existing part that does not match the drawings, a hidden service in the ground, an interface that does not respond. The answer should be a written variation, not an unexplained extra on the invoice.",
        ],
        "faqs": [
            ("How do I compare {thing} installers fairly?", "Ask each for measured dimensions, the named product and data, the tests, the handover documents and exclusions, then compare line by line."),
            ("Can you work alongside our other contractors?", "Yes. The scope names the interfaces and who is responsible for each."),
        ],
    },
    "near-me": {
        "phrase": "near me",
        "paras": [
            "Searching for a {thing} near me usually means wanting someone who can get to the site and understands local buildings. iComply is based at 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE and quotes for sites across the UK mainland. Travel is part of the written quote, not a hidden extra.",
            "Being local matters less than being prepared. Before any visit, iComply asks for the address, photos, what is installed and what the problem is. That way the visit is planned with the right access and the right questions, wherever the site is.",
            "No attendance time is promised on this page. Visits are arranged by phone, WhatsApp or the contact form, and the date is confirmed in writing once access is agreed.",
            "Town pages for this guide cover places across the UK mainland, each with its own local note. They all lead back to the same written-scope approach and price on application.",
        ],
        "faqs": [
            ("Do you cover my area for {thing} work?", "iComply quotes for sites across the UK mainland from its Stockport workshop. Travel is included in the written quote."),
            ("How quickly can you attend?", "No attendance time is promised here. Visits are arranged by phone, WhatsApp or the contact form and confirmed in writing."),
        ],
    },
    "repair": {
        "phrase": "repair",
        "paras": [
            "{thing} repair starts with finding the cause. A symptom — something that will not open, will not close, shows a fault or makes a noise — can have several causes. The fault is reproduced on site and traced before any part is ordered, so the repair fixes the problem rather than a guess.",
            "Repairs use parts that match the original performance from the maker's published data, or a documented equivalent where the original is no longer available. The part used and the reason for the choice are written in the job record.",
            "Some faults leave equipment unsafe or out of service. Where that happens, the equipment is made safe and the responsible person is told in writing what is out of service and what interim measures are needed until the repair is complete.",
            "After a repair, the {thing} is tested in normal operation, and any related safety function is retested. The record shows the fault found, the cause, the repair and the test result.",
            "Recurring faults are treated differently from one-offs. If the same fault keeps coming back, the record history is reviewed and the root cause is looked for — a supply problem, a damaged part elsewhere, an installation issue — rather than resetting it again.",
        ],
        "faqs": [
            ("Can you repair a {thing} that someone else installed?", "Yes. The fault is traced on site and parts are matched from published data. The record shows what was found and done."),
            ("What if the part is no longer made?", "A documented equivalent is chosen from published data, and the reason is written in the job record."),
        ],
    },
    "maintenance": {
        "phrase": "maintenance",
        "paras": [
            "{thing} maintenance keeps equipment working between faults. It follows the maker's recommendations and the routine set in the building's own documents, with checks recorded so trends are visible over time.",
            "A maintenance visit covers visual inspection, cleaning where needed, mechanical and electrical checks, adjustment, functional testing and a written record. Defects found are listed with a recommended action and a priority.",
            "Planned maintenance is cheaper and less disruptive than reactive repair. Worn parts are spotted before they fail, and replacements can be planned into budgets rather than bought in a hurry.",
            "Maintenance records matter to insurers, auditors and the fire authority. iComply's records list each item checked, the result and any action, in a format that can be filed and compared visit to visit.",
            "The frequency of maintenance depends on the equipment, how heavily it is used and what the building's documents require. iComply follows that frequency, or proposes one in writing where none has been set.",
        ],
        "faqs": [
            ("How often should a {thing} be maintained?", "As set by the maker's recommendations and the building's own documents. Where none is set, a frequency is proposed in writing based on use."),
            ("What do I get after a maintenance visit?", "A written record of each item checked, the result and any recommended action with priority."),
        ],
    },
    "service": {
        "phrase": "service",
        "paras": [
            "A {thing} service is a planned visit to check, adjust and test the equipment so it keeps working as intended. It follows the maker's service schedule and the building's own routine, and produces a record that the site owner can file.",
            "Service visits are booked in advance and planned around access. The engineer arrives with the previous record so trends and repeat issues can be followed up rather than rediscovered.",
            "During a service, worn or failing parts are noted with a recommended action. Small adjustments are made on the day; larger repairs are quoted separately on application so the client can approve them.",
            "Service contracts can cover a single site or a portfolio. The scope sets out what each visit covers, how often visits happen and how faults found between visits are handled.",
        ],
        "faqs": [
            ("What happens during a {thing} service?", "Checks, adjustment, testing and a written record. Small adjustments are done on the day; larger repairs are quoted on application."),
            ("Can you service several sites?", "Yes. A portfolio scope sets out visit content and frequency for each site."),
        ],
    },
    "servicing": {
        "phrase": "servicing",
        "paras": [
            "{thing} servicing is the routine that keeps equipment ready between tests and faults. The visit records the condition of each component, tests the key functions and notes anything that will need attention soon.",
            "Servicing frequency is set by the maker's guidance and the building's documents. Heavily used or critical equipment may need more frequent visits; iComply proposes a frequency in writing where none exists.",
            "A good servicing record is comparable from one visit to the next. iComply uses the same format each time so changes stand out, such as a battery that is ageing or a part that is wearing faster than expected.",
            "Servicing is not a repair visit, but small faults are fixed where it is sensible to do so on the day. Anything larger is written up with a recommended action and quoted on application.",
        ],
        "faqs": [
            ("Is servicing the same as repair?", "No. Servicing checks and tests; small faults may be fixed on the day, but larger repairs are quoted separately."),
            ("How often is {thing} servicing needed?", "As set by the maker and the building's documents, or as proposed in writing where none exists."),
        ],
    },
    "commissioning": {
        "phrase": "commissioning",
        "paras": [
            "Commissioning a {thing} proves that it works as designed, in the building it is installed in, with the interfaces it depends on. It is more than switching on: every function is tested, every interface is proved and the results are recorded.",
            "Commissioning works from a written cause-and-effect or functional description. Where none exists, one is drawn up from the design and agreed before testing starts, so everyone knows what a pass looks like.",
            "Results are recorded item by item. Failures are fixed and retested, and the record shows both. The final commissioning record goes into the building's file with the maker's documentation.",
            "Recommissioning is needed after significant changes: a new panel, a change of interface, an extension. The scope says what will be retested and why.",
        ],
        "faqs": [
            ("What does {thing} commissioning include?", "Testing every function and interface against a written description, fixing and retesting failures, and a full record."),
            ("When is recommissioning needed?", "After significant changes such as a new panel, interface or extension."),
        ],
    },
    "engineer": {
        "phrase": "engineer",
        "paras": [
            "A {thing} engineer is the person who surveys, installs, tests, services and repairs the equipment on site. What matters most is that they work from the maker's published data and the building's own documents, and leave a record the next engineer can follow.",
            "iComply engineers arrive with the site information already gathered: the address, what is installed, photos, the reported fault and any previous records. That makes the visit about diagnosis and action rather than fact-finding.",
            "An engineer's visit ends with a record: what was found, what was done, what was tested, and what still needs doing. Recommended work is quoted on application.",
            "Engineers are not asked to work beyond what is safe or what the scope covers. Where a task needs another trade, that is stated and arranged rather than improvised.",
        ],
        "faqs": [
            ("What does a {thing} engineer do on site?", "Surveys, installs, tests, services and repairs, working from published data and the building's documents, and leaves a record."),
            ("Will the engineer fix everything on the first visit?", "Where parts and access allow. Otherwise the record lists the fault, the cause and the recommended work, quoted on application."),
        ],
    },
    "engineers": {
        "phrase": "engineers",
        "paras": [
            "When a site or portfolio needs {thing} engineers rather than a single visit, consistency becomes the issue. The same checks, the same record format and the same approach to defects need to apply on every visit, whoever attends.",
            "iComply uses standard record sheets and a written scope for each site so different engineers produce comparable results. Previous records travel with the job.",
            "Multi-site clients can ask for a single summary across sites as well as individual records. Defects are grouped by priority so the most important work is visible first.",
            "Engineers coordinate with other contractors where systems interconnect. The scope names the interfaces and who tests them.",
        ],
        "faqs": [
            ("Can your {thing} engineers cover several sites?", "Yes. Standard records and per-site scopes keep results comparable, with a summary across sites if needed."),
            ("Will we see the same engineer each time?", "Not always, but records and scopes keep visits consistent whoever attends."),
        ],
    },
    "emergency-repair": {
        "phrase": "emergency repair",
        "paras": [
            "Emergency {thing} repair means a fault that leaves equipment unsafe, out of service, or stuck in a way that affects safety or access. The call is treated as a priority instruction, but no attendance time is promised on this page. The visit is arranged by phone and confirmed in writing.",
            "The first aim on an emergency visit is to make safe: isolate, secure or set the equipment to a safe state, and tell the responsible person in writing what is out of service and what interim measures are needed.",
            "Once safe, the fault is traced and repaired if parts allow. If parts need ordering, the record says so and the follow-up repair is quoted on application.",
            "Emergency faults often reveal underlying issues. After the immediate repair, the record recommends any further checks so the same fault is less likely to recur.",
        ],
        "faqs": [
            ("Do you guarantee an emergency attendance time?", "No attendance time is promised here. Emergency calls are treated as priority instructions and arranged by phone, confirmed in writing."),
            ("What happens first on an emergency visit?", "The equipment is made safe and the responsible person is told in writing what is out of service."),
        ],
    },
    "emergency-installer": {
        "phrase": "emergency installer",
        "paras": [
            "An emergency {thing} installer is needed when equipment has failed beyond repair, been damaged, or been found missing where the fire strategy or site plan relies on it. The instruction is treated as a priority, but no attendance time is promised on this page.",
            "Emergency installation still starts with measurement and product selection from published data. Shortcuts at this stage create the next emergency. Where a temporary measure is needed while parts arrive, it is agreed in writing.",
            "The responsible person is told in writing what is out of service, what interim measures apply and what the permanent installation will involve.",
            "Emergency installation ends with the same testing and handover record as planned work.",
        ],
        "faqs": [
            ("Can you install a replacement {thing} urgently?", "The instruction is treated as a priority, but no attendance time is promised here. Measurement and product selection still come first."),
            ("What about interim measures?", "Agreed in writing with the responsible person while parts are sourced."),
        ],
    },
    "replacement": {
        "phrase": "replacement",
        "paras": [
            "{thing} replacement starts with recording what is there: the model, dimensions, fixings and settings. The replacement is chosen to match from the maker's published data, or to a new measured size if the site has changed.",
            "Replacement is also a chance to check the rest of the installation. A replaced part on a worn mechanism will not last. The visit notes related wear and recommends any further work.",
            "After replacement, the equipment is re-set, rebalanced or reconfigured as needed and tested. The record shows the old and new parts and the settings used.",
            "Where a direct replacement is not available, an equivalent is chosen from published data and the reasons are recorded.",
        ],
        "faqs": [
            ("How do you choose a {thing} replacement?", "By matching the existing model, dimensions and fixings from published data, or a new measured size if the site has changed."),
            ("Will anything else need doing?", "Related wear is noted and any further work is recommended in the record."),
        ],
    },
    "shortening": {
        "phrase": "shortening",
        "paras": [
            "{thing} shortening is done when an opening has narrowed — new kerbs, a new island, a post moved — or when an arm was supplied too long. The new length is measured on site from the clear opening, not taken from the old arm.",
            "Shortening changes the balance. The counterbalance springs or weights are adjusted to suit the new length and any accessories, and the arm is tested through its full movement.",
            "There is a limit to how far an arm can be shortened before it falls outside the range the barrier's springs or counterweights are set for. Beyond that, different springs or a different arm is needed; the scope says which.",
            "The record shows the old and new lengths, the spring or counterweight setting and the test result.",
        ],
        "faqs": [
            ("Can any barrier arm be shortened?", "Within the range the springs or counterweights are set for. Beyond that, different springs or a new arm is needed."),
            ("Does shortening affect balance?", "Yes. Springs or weights are re-set to suit the new length."),
        ],
    },
    "survey": {
        "phrase": "survey",
        "paras": [
            "A {thing} records what is on site in measured terms: dimensions, clearances, ground, existing equipment and constraints. It is the basis for any specification or quote.",
            "The survey output is a written report with measurements, photos and notes. It names what can be installed and what limits the options.",
            "Surveys are useful on their own — for budgeting, for tendering, or for confirming whether an existing installation meets the site's needs.",
            "Survey work is quoted on application. The report belongs to the client and can be used with any installer.",
        ],
        "faqs": [
            ("What does a {thing} include?", "Measured dimensions, clearances, ground, existing equipment, constraints, photos and notes in a written report."),
            ("Can we use the survey with another installer?", "Yes. The report belongs to the client."),
        ],
    },
}
