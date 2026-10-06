"""Fencing DEEP cores: industrial, commercial, perimeter, LPS 1175."""

CORES = {
    "industrial": {
        "label": "industrial fencing", "thing": "industrial fencing", "context": "industrial",
        "hook": "heavy-duty palisade, mesh and gates planned around vehicles, yards and operating hours",
        "paras": [
            "Industrial fencing protects yards, plant, stock and vehicles, and it has to survive forklifts, lorries reversing and years of weather. The usual systems are steel palisade and heavy welded mesh, with manual swing or sliding gates for vehicle access, and sometimes impact protection where vehicles turn close to the fence line.",
            "Industrial sites rarely stop for fencing work. Installation is phased around deliveries, shift patterns and yard movements, with exclusion zones around the working area and temporary barriers where a section of the old fence has to come down before the new one goes up. The sequence is agreed with the site manager in writing.",
            "Underground services are a real risk on industrial land. Drainage, power cables, gas pipes and data ducts often run close to boundaries. Utility drawings are requested, the line is scanned with a cable avoidance tool before digging, and trial holes are dug by hand where services are suspected.",
            "Ground on industrial sites is often hard standing, made ground or old foundations. Posts may need core drilling through concrete, longer posts in made ground, or base plates where digging is not possible. These decisions affect the cost and are made on site rather than assumed.",
            "Vehicle gates on industrial boundaries take heavy use. Manual swing gates need strong hinges, gate stops and drop bolts set into proper keeps. Sliding gates need a clear run-back area and a level track or a cantilever arrangement. Gate leaves and posts are sized for the opening, not taken from a standard list.",
            "Many industrial fences sit on boundaries with neighbouring businesses or public land. The line, height and toppings are agreed before work, and toppings are positioned so they do not overhang a neighbour's land or a public footway without agreement.",
        ],
        "focus": [
            "Palisade or heavy mesh with vehicle gates",
            "Work phased around operations",
            "Services located before digging",
            "Core drilling or base plates on hard standing",
            "Price on application",
        ],
        "faqs": [
            ("Can you fence while our site is operating?", "Yes. Work is phased around deliveries and shifts, with exclusion zones and temporary barriers where needed, agreed with the site manager."),
            ("Can posts go into concrete hard standing?", "Yes. Posts can be core drilled into concrete or base plated where the slab can take the load. The approach is confirmed on site."),
            ("Do you install industrial gates?", "Yes, manual swing and sliding gates in matching palisade or mesh. Powered gate automation is a separate specification."),
        ],
        "cross": ["steel-palisade", "palisade-gate", "perimeter"],
    },
    "commercial": {
        "label": "commercial fencing", "thing": "commercial fencing", "context": "commercial",
        "hook": "boundaries for offices, retail, car parks and business parks that look right and keep working",
        "paras": [
            "Commercial fencing has to balance security with appearance. A business park, retail unit or office frontage wants a boundary that looks tidy, keeps sightlines for customers and cameras, and still deters casual intrusion. Welded mesh panel systems, railings and lower-profile palisade are the most common choices.",
            "Commercial sites often have landlords, managing agents and tenants with different interests. The scope identifies who instructs the work, who approves the colour and profile, and who needs to be told about access changes. Landlord consent and lease terms are the client's to check, and iComply flags where they may apply.",
            "Colour and finish are noticed on commercial sites. Powder-coated mesh and railings in green, black or a corporate colour, with matching gates and posts, give a consistent look. Colours vary slightly between batches and makers, so extensions to an existing fence are matched as closely as possible and samples are checked when the match matters.",
            "Car parks need fencing that copes with vehicles. Low kerb rails, bollards and fence lines set back from parking bays reduce damage. Where vehicles regularly clip a fence, adding protection is better value than replacing panels again and again.",
            "Pedestrian routes and accessibility shape commercial fence layouts. Gates on footpaths need a clear width, a level threshold and ironmongery that can be used easily. Fences next to public footways are kept free of sharp toppings at hand height.",
            "Commercial fencing work is planned around opening hours. Noisy work, deliveries and areas fenced off for safety are scheduled with the site, and the working area is kept tidy and safe for staff and the public at the end of each day.",
        ],
        "focus": [
            "Mesh, railings or palisade chosen for the frontage",
            "Landlord, agent and tenant roles noted",
            "Colour and finish matched",
            "Car park protection and pedestrian routes",
            "Work planned around opening hours",
        ],
        "faqs": [
            ("What is the best fence for a business park?", "Welded mesh panel systems are the most common because they are secure, see-through for cameras and look tidy. Railings suit frontages, and palisade suits service yards."),
            ("Can you match our corporate colour?", "Powder-coated systems are available in many colours. Batches vary slightly, so samples are checked where the match matters."),
            ("Do we need landlord consent?", "It depends on your lease. We flag where consent may be needed, and the tenant or agent confirms it before work starts."),
        ],
        "cross": ["mesh", "railings", "security"],
    },
    "perimeter": {
        "label": "perimeter fencing", "thing": "perimeter fencing", "context": "industrial",
        "hook": "a continuous boundary around the whole site with gates, corners and weak points designed in",
        "paras": [
            "Perimeter fencing is a fence planned around the whole boundary of a site rather than a single run. The point is continuity: one weak section, an unfenced corner or a poorly fitted gate undoes the rest. A perimeter survey walks the full line and records every change in ground, every structure the fence meets and every opening.",
            "Junctions are where perimeters fail. Where a fence meets a building, a wall, a hedge or a neighbour's fence, a gap is often left. Those junctions are closed with matching panels, wing panels or toppings, and the method is written into the scope for each one.",
            "Watercourses, culverts, ditches and drainage outlets along a boundary need careful treatment. A fence across a ditch leaves a gap underneath unless a grille or a hinged section is designed in. Any work affecting a watercourse may need consent from the relevant authority, which the owner obtains.",
            "Perimeter fences are often combined with other security measures. Cameras, detection systems, lighting and signs work better when the fence line is straight, clear of vegetation and free of climbing aids such as bins, pallets or trees close to the fence. The survey notes climbing aids for the owner to deal with.",
            "Long perimeters are planned in phases. Material deliveries, the order of sections, where temporary fencing is needed and how the site stays secure overnight are set out before work starts. On larger sites, each phase is checked and signed off as it is completed.",
            "Perimeter fencing is quoted for the full line with the gates, junctions, toppings and phasing included. Partial quotes for selected sections are also possible, and the scope makes clear which parts of the perimeter are included.",
        ],
        "focus": [
            "Full boundary walked and recorded",
            "Junctions with buildings and walls closed",
            "Ditches and culverts treated",
            "Climbing aids flagged",
            "Phased installation that keeps the site secure",
        ],
        "faqs": [
            ("Why is a full perimeter survey needed?", "A fence is only as good as its weakest point. Walking the full line finds the gaps, junctions and openings that need treatment."),
            ("Can you fence across a ditch?", "Yes, with a grille or a hinged section so the gap underneath is closed. Consents for watercourses are obtained by the owner."),
            ("Can perimeter fencing work with CCTV?", "Yes. A straight, clear fence line helps cameras and detection. We can coordinate with your security installer."),
        ],
        "cross": ["security", "anti-climb", "industrial"],
    },
    "lps": {
        "label": "LPS 1175 fencing", "thing": "LPS 1175 fencing", "context": "industrial",
        "hook": "certified security fence systems supplied and installed exactly to the tested configuration",
        "paras": [
            "LPS 1175 is a Loss Prevention Standard published by the Loss Prevention Certification Board (LPCB, part of BRE) for the intruder resistance of building components, including fences and gates. Products are tested by attacking them with defined tool sets for defined times, and a certified product is given a rating that describes the level of resistance it achieved.",
            "Issue 8 of LPS 1175 uses a two-part rating, a letter for the tool category and a number for the delay time, such as A1 up to F10. Earlier issues used security ratings SR1 to SR8. Both may appear in specifications, so the scope records exactly which rating the client's specification requires.",
            "A rating belongs to the product as tested. To keep it valid, the fence must be installed with the same panels, posts, fixings, foundations and details that were tested, following the manufacturer's installation instructions. Substituting a cheaper post, a different bolt or a different panel can mean the installed fence is not the certified product.",
            "iComply supplies and installs LPS 1175 certified fence systems where a specification calls for them, working to the manufacturer's instructions and keeping records of the products and installation. iComply does not certify fences and does not hold LPCB certification itself; the product certificate comes from the manufacturer.",
            "LPS 1175 fences are used on critical sites such as utility, data, telecoms, transport and high-value storage. They are usually part of a wider security design that includes detection, cameras and access control, and the fence specification is set by the client, their security consultant or the network operator.",
            "Certified fences are heavier, more expensive and slower to install than standard systems. A survey confirms the ground, access for heavy panels, foundation design and gate details before a quote is given, and the quote names the certified system and its rating.",
        ],
        "focus": [
            "LPCB standard for intruder resistance",
            "Issue 8 ratings from A1 to F10; older SR1 to SR8",
            "Installed exactly to the tested configuration",
            "Product certificate from the manufacturer",
            "Price on application",
        ],
        "faqs": [
            ("Is iComply LPS 1175 certified?", "No. LPS 1175 certification applies to manufacturers' products. We supply and install certified systems to the manufacturer's instructions where a specification requires them."),
            ("What does an LPS 1175 rating mean?", "It describes how long a product resisted attack with a defined set of tools. Issue 8 uses ratings such as A1 to F10; earlier issues used SR1 to SR8."),
            ("Why does installation matter for LPS 1175?", "The rating applies to the tested configuration. Changing posts, fixings or foundations can mean the installed fence no longer matches the certified product."),
        ],
        "cross": ["mesh-358", "security", "perimeter"],
    },
}
