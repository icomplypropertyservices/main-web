"""Fencing DEEP intents. {thing} may be singular or plural, so sentences avoid is/are agreement on it."""

INTENTS = {
    "guide": {
        "tail": "It sets out the options, the specification choices and the questions worth asking before you commit to {thing}.",
        "paras": [
            "Before choosing {thing}, it helps to write down what the boundary must achieve. Privacy, security, keeping children or animals in, marking a line, screening bins or plant, and meeting a landlord's or insurer's requirement all point to different products. A short list of priorities makes the survey quicker and the recommendation clearer.",
            "The specification for {thing} is usually described in a handful of terms: height, material, profile or panel type, post type and foundation, finish and colour, and gate details. When comparing quotes, checking each of those terms side by side shows whether two prices are for the same thing.",
            "Most buyers underestimate how much the ground affects the job. A level lawn, a sloping bank, a tarmac car park and a strip of made ground beside a building all need different post and foundation methods. Asking how the posts will be set, and what happens if buried services or old concrete are found, avoids surprises.",
            "Lifespan depends on the material and the care it gets. Timber needs treatment and will eventually need parts replaced. Galvanised and powder-coated steel lasts much longer with little maintenance. Asking what maintenance the chosen option needs, and how often, is part of choosing well.",
            "Planning rules, boundary agreements and lease terms can all affect {thing}. Height near a highway, conservation areas, listed buildings, planning conditions on newer estates and covenants on some properties are worth checking early. iComply flags the points it sees; the owner makes the checks and decisions.",
            "Good access makes installation simpler. Clear routes for materials, somewhere to unload, room to work on both sides of the line and a plan for any planting or structures near the boundary all help. The survey notes access so the plan is realistic.",
            "A written scope protects both sides. It lists what is included, what is excluded, how waste is removed, how the site is left and what happens if something unexpected is found. With {thing}, as with most building work, the clearer the scope, the fewer disagreements at the end.",
            "iComply quotes {thing} price on application after a survey or after reviewing clear photos and measurements. There are no headline prices, because the cost depends on length, height, ground, access and specification, all of which differ from site to site.",
        ],
        "faqs": [
            ("What should I decide before getting a quote?", "Write down what the fence must achieve, the rough length and height you have in mind, and any gates. The survey then confirms the details and the right specification."),
            ("Why is there no price list?", "Cost depends on length, height, ground, access and specification. Every job is quoted price on application from a written scope."),
        ],
    },
    "near-me": {
        "tail": "It explains how local enquiries are handled from the Stockport workshop, which towns are covered and how a survey visit is arranged.",
        "paras": [
            "iComply works from a workshop in Offerton, Stockport, and covers Greater Manchester and the surrounding towns of the North West ring. For {thing}, a local survey means the person pricing the job has walked the line, seen the ground and measured the gates, which gives a far more reliable quote than a desk estimate.",
            "Survey visits are booked by arrangement at a time that suits the site. For homes, that is often an evening or weekend; for commercial and industrial sites, it is usually during working hours with the site manager present. No attendance time is promised in advance; the visit is agreed directly.",
            "Searching for {thing} near you usually means wanting someone who knows local conditions. Across Greater Manchester, clay soils, Pennine exposure on the eastern edges, older terraces with shared boundaries and large industrial estates all shape how fencing is specified and installed.",
            "Local work keeps travel and delivery simple. Materials are ordered for the job once the scope is agreed and delivered to site or collected from local suppliers. Waste is taken away rather than left in a skip on the street unless a skip has been agreed.",
            "Photos help a local enquiry move quickly. A few photos of the boundary, a rough measurement of the length and height, and a note of any gates, slopes or access issues mean the first conversation can be specific. Send them by WhatsApp or email, and a survey can be arranged if one is needed.",
            "Covering a wide local area means iComply sees most types of boundary: terraced back yards, suburban gardens, new-build estates, schools, business parks, depots and farm land. That experience feeds into the advice given on site about which {thing} option suits the boundary in front of you.",
            "Local planning authorities across Greater Manchester and the surrounding boroughs apply the same national rules on fence heights, but conservation areas, listed buildings and estate conditions differ. iComply flags where a local check may be needed, and the owner contacts the authority.",
            "A local contractor is easy to reach after the job too. If something needs adjusting once the new work has settled in, such as a gate that needs rehanging or a fixing that needs tightening, the same team that installed it can come back by arrangement.",
        ],
        "faqs": [
            ("Do you cover my area?", "iComply covers Greater Manchester and the surrounding North West towns from its Stockport workshop. Send your postcode and we will confirm."),
            ("How quickly can you visit?", "Survey visits are booked by arrangement. We do not promise attendance times in advance, but we agree a time directly with you."),
        ],
    },
    "installation": {
        "tail": "It walks through how installation is planned, from setting out and post foundations to finishing, waste removal and handover.",
        "paras": [
            "Installation of {thing} starts with setting out. The line is marked with pegs and a string line from the confirmed boundary points, corners and gate positions. Panel or bay widths are worked out from the ends so cut sections fall where they are least visible, and the post positions are marked before any digging starts.",
            "Before digging, the line is checked for buried services using utility drawings where available and a cable avoidance tool. Where services are suspected, holes are dug by hand. Old foundations, roots and rubble found in the ground are dealt with as they are found, and any change to the plan is agreed.",
            "Posts are set to the depth and foundation the system and the ground require. They are set plumb in both directions and in line, and braced while concrete cures. Getting the posts right is most of the job, because everything else hangs from them.",
            "Once the posts are set, rails, panels, boards, pales or mesh are fixed in order along the run. Levels are checked as the work progresses so the top line is consistent, and adjustments are made for slopes by stepping or raking as agreed in the scope.",
            "Gates are hung once their posts have set firm. Hinges, latches, stops and drop bolts are fitted, and the gate is adjusted so it swings freely and closes properly. A gate that is hung on posts before the concrete has cured will often drop later.",
            "Finishing includes fitting caps, treating any cut ends or drilled steel, checking every fixing, clearing offcuts and spoil, and leaving the ground tidy along both sides of the line. Old fencing and waste are removed from site as agreed.",
            "At handover, the work is walked with the client, gates are demonstrated, and any maintenance needs are explained, such as treating timber or keeping vegetation clear of mesh. Product details and any manufacturer information are passed on.",
            "Installation time depends on the length, the ground, access and the weather. The plan agreed in the scope sets out the sequence and any phasing, and the client is kept informed if conditions on the day change the plan.",
        ],
        "faqs": [
            ("How are posts set?", "Posts are set to the depth and foundation the system and ground need, plumbed in both directions and braced while the concrete cures."),
            ("Do you take away the old fence?", "Yes, removal and disposal of the old fence and waste is included where it is written into the scope."),
        ],
    },
    "repair": {
        "tail": "It covers how damage is assessed, when a repair is sensible and when replacement is the better value.",
        "paras": [
            "Repairs to {thing} start with finding the cause. A leaning section, a broken panel or a sagging gate may come from a failed post, a loose fixing, wind damage, vehicle impact, tree roots or simple age. Fixing the cause matters, otherwise the same failure returns.",
            "Many repairs are local. Single panels, boards, pales, mesh panels, rails and fixings can usually be replaced without disturbing the rest of the line, provided matching parts are available. The repair is matched to the existing material, height and finish as closely as possible.",
            "Posts are the most common repair. Rotten timber posts are replaced or supported with concrete repair spurs, and leaning steel or concrete posts are reset in new foundations. Where many posts along a run have failed, replacing the run is usually better value than repairing post by post.",
            "Storm damage often affects several bays at once. Panels blown out, posts snapped at ground level and gates wrenched off their hinges are common after high winds. Making the boundary safe comes first, followed by a permanent repair once materials are available.",
            "Vehicle damage on commercial and industrial sites tends to bend posts, rails and pales. Bent steel is replaced rather than straightened where its strength or finish has been compromised, and protection such as bollards or barriers is suggested where impacts keep happening.",
            "Repairs involving steel include treating any cut, drilled or welded areas with a zinc-rich primer or cold galvanising before any topcoat. Leaving bare steel exposed causes rust to spread under the remaining coating.",
            "A repair quote sets out what has failed, why, what is being replaced and what is being left. Where the remaining parts are near the end of their life, the quote says so honestly, so the decision between repair and replacement is an informed one.",
            "Insurance claims for fence damage often need photos and a written description. iComply can provide photos of the damage and a written scope and quote to support a claim; the claim itself is between the owner and the insurer.",
        ],
        "faqs": [
            ("Is it worth repairing or should I replace?", "If most of the posts and panels are sound, repair is usually sensible. If many posts have failed or the materials are near the end of their life, replacement is often better value. The quote explains which."),
            ("Can you help with an insurance claim?", "We can provide photos and a written scope and quote to support a claim. The claim itself is between you and your insurer."),
        ],
    },
    "replacement": {
        "tail": "It explains how an old fence is assessed, taken down and replaced along the agreed line without leaving the boundary open longer than planned.",
        "paras": [
            "Replacement means taking down an existing fence and putting a new one up along the agreed line. It is the right choice when the posts have failed along much of the run, when the material has reached the end of its life, or when the owner wants a different type of fence.",
            "Replacing {thing} usually follows the old line unless the owner confirms a change. Where the old fence was on a shared boundary, the owner is encouraged to agree the replacement with the neighbour first, including the height and which way the finished face points.",
            "The old fence is taken down section by section, and old concrete footings are broken out where new posts must go in the same positions. Where old footings are too large to remove, new posts are offset and the layout is adjusted. Waste is removed and recycled where possible.",
            "Replacement is a good time to reconsider the specification. Concrete posts and gravel boards instead of timber, a heavier panel for an exposed garden, mesh instead of palisade for better visibility, or a different height can all be considered while the line is open.",
            "On sites where the boundary cannot be left open, replacement is phased so only a short section is down at any time, or temporary fencing is used until the new line is complete.",
        ],
        "faqs": [
            ("Will the new fence go on the same line?", "Yes, unless you confirm a change. On shared boundaries, we recommend agreeing the line and finished face with the neighbour first."),
            ("Can the boundary stay secure during replacement?", "Yes. Work can be phased, or temporary fencing used, so the site is not left open."),
        ],
    },
    "contractors": {
        "tail": "It sets out what to expect from a fencing contractor, how iComply scopes, manages and finishes work, and how to compare quotes fairly.",
        "paras": [
            "Choosing a contractor for {thing} is mostly about clarity and workmanship. A good contractor surveys before quoting, writes a scope that lists what is included, explains the specification in plain terms and tells you what happens if something unexpected is found in the ground.",
            "iComply Property Services is a property maintenance and installation business based in Stockport. It carries out fencing for homes, landlords, managing agents, schools, commercial and industrial sites across Greater Manchester and the surrounding towns, with work quoted price on application.",
            "Comparing quotes is easier when each one names the same things: length, height, product, post type, foundation, finish, gates, waste removal and making good. A low price that leaves out posts, gravel boards or removal is not a like-for-like comparison.",
            "Insurance, risk assessments and method statements are normal for commercial and school work. iComply provides the documents the site requires, follows site rules and inductions, and works to the agreed hours.",
            "After installation, a contractor should be easy to contact for adjustments. Gates settle, fixings may need tightening and timber moves in its first season. iComply returns by arrangement to put right any issues with its own workmanship.",
        ],
        "faqs": [
            ("Do you provide risk assessments and method statements?", "Yes, for commercial, school and industrial work, along with following site inductions and rules."),
            ("How do I compare fencing quotes?", "Check that each quote names the same length, height, product, posts, foundation, finish, gates and waste removal."),
        ],
    },
    "cost": {
        "tail": "It explains what drives the cost of {thing}, why quotes are price on application, and how to get an accurate written quote.",
        "paras": [
            "The cost of {thing} depends on a handful of factors: the length of the run, the height, the material and system, the number and type of gates, the ground conditions, access for materials and the amount of old fence to remove. Change any of those and the price changes.",
            "Ground and access often make the biggest difference between two similar-looking jobs. Digging through tarmac, concrete, roots or made ground takes longer than digging in clear soil, and materials carried by hand through a house cost more to install than materials unloaded beside the line.",
            "Specification choices change both the upfront cost and the lifetime cost. Concrete posts cost more than timber at the start but rarely need replacing. Powder-coated steel costs more than bare galvanised but looks better for longer. A quote that explains those trade-offs helps you choose.",
            "iComply does not publish price lists, because a headline price cannot reflect the ground, the access and the specification on your site. Every job is quoted price on application after a survey or a review of clear photos and measurements.",
            "To get an accurate quote quickly, send the address, the approximate length and height, photos of the boundary and any gates, and a note of what you want the fence to achieve. A written scope and quote follow, listing what is included.",
        ],
        "faqs": [
            ("How much does fencing cost?", "It depends on length, height, material, gates, ground, access and removal of any old fence. Every job is quoted price on application from a written scope."),
            ("Can you quote from photos?", "Often, if the photos are clear and include measurements. Some sites need a survey visit first."),
        ],
    },
    "emergency": {
        "tail": "It explains how a fallen or storm-damaged fence is made safe and then repaired properly, without promising attendance times.",
        "paras": [
            "A fallen fence is often more urgent than it looks. A gap in a boundary can let children or pets out, leave a site open, or put a heavy panel at risk of falling onto a footpath or a car. Making the area safe comes first, by laying down or securing loose panels and closing the gap where possible.",
            "Most fences fall because the posts have failed at ground level or because high winds have caught solid panels. The cause is checked so the repair deals with it, rather than simply standing the old fence back up on the same failed posts.",
            "Requests for help with a fallen fence are handled by arrangement. iComply does not promise attendance times, but it will tell you honestly when a visit can be made. A temporary fix, such as propping, securing panels or closing the gap with temporary fencing, may be arranged before the permanent repair.",
            "The permanent repair may be new posts, replacement panels, a part replacement or a new run, depending on what is found. Photos of the damage taken at the time help both the repair quote and any insurance claim.",
            "After storms, concrete posts and gravel boards, heavier or slatted panels, and closeboard built in place are often recommended for exposed boundaries, because they cope better with wind than lightweight panels on timber posts.",
        ],
        "faqs": [
            ("Can you come out today?", "We do not promise attendance times. Contact us with photos and we will tell you honestly when a visit can be made."),
            ("Should I try to stand the fence back up?", "Only if it is safe. Laying panels flat or securing them is usually safer until the posts are repaired."),
        ],
    },
    "hire": {
        "tail": "It covers how temporary fence hire is scoped: the layout, the period, delivery and collection, bracing and responsibility for checks.",
        "paras": [
            "Temporary fencing hire covers supply, installation, any changes to the layout during the hire period, and collection at the end. The scope records the length, number of panels, feet, couplers, bracing, gates and any covering, along with the delivery address and the start and end dates.",
            "Hire periods range from a single event to many months on a construction site. Longer hires often involve moving sections as the work progresses, adding gates or extending the line, and those changes are agreed and recorded as they happen.",
            "During the hire, someone needs to check the fence regularly, especially after high winds or when the site layout changes. The hire scope makes clear whether iComply carries out those checks or whether the site team does.",
            "Lost or damaged panels, feet and clamps are a common issue with temporary fencing. The scope sets out how missing or damaged items are handled so there are no surprises at collection.",
            "Hire is quoted price on application. The price depends on the quantity, the period, delivery distance, installation, any changes during the period and collection.",
        ],
        "faqs": [
            ("How long can I hire temporary fencing for?", "From a single event to many months. Changes to the layout during the hire can be arranged."),
            ("Who checks the fence during the hire?", "The scope states whether iComply or your site team carries out regular checks."),
        ],
    },
}

ANGLES = ["guide", "installation", "repair", "near-me", "replacement", "cost"]
