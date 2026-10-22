"""Fencing DEEP cores. Specs are typical published ranges, stated as ranges, never product claims.
Standards (BS 1722, LPS 1175, Secured by Design) only "where specified": certified products are
supplied and fitted when a specification asks for them; iComply does not self-certify.
Electric / automatic gates are out of scope (ELECTRIC-GATES-DEEP is canonical)."""

CORES = {
    "fencing": {
        "label": "fencing", "thing": "fencing", "context": "mixed",
        "hook": "boundary, security and garden fencing chosen from a measured site survey",
        "paras": [
            "Fencing covers a wide range of products, from a featheredge garden boundary to a 3 m steel palisade line around a depot. The right choice starts with what the fence has to do: mark a boundary, keep people or animals in, keep intruders out, screen a view, reduce noise or protect children. Once the job is clear, the height, material, post system and gate positions follow from the ground and the length measured on site.",
            "Most fencing enquiries fall into three families. Timber fencing such as closeboard, panel and post-and-rail suits gardens, communal areas and screening. Steel mesh and palisade suit commercial and industrial boundaries where strength and visibility matter. Railings suit frontages, schools and public spaces where a fence must look tidy for decades. Mixed sites often use more than one family, and the scope says where each run starts and stops.",
            "Ground conditions drive more of the cost and the lifespan than most people expect. Clay, made ground, tree roots, buried services, slopes and hard surfaces all change how posts are set. Posts are concreted, driven or base-plated to suit what is found when the line is walked, and a trial hole is dug where the ground is unknown rather than guessing a foundation depth.",
            "Height is a planning question as well as a practical one. Many boundary fences can be put up without an application, but limits apply next to highways and on some frontages, and listed buildings, conservation areas and planning conditions can change the position. iComply flags where a height or position may need a check with the local planning authority; the decision belongs to the owner and the authority.",
            "Boundary lines are confirmed by the owner before a post goes in. Where a fence replaces an older one, the new line normally follows the old post positions unless the owner says otherwise. Where neighbours share a boundary, the owner is encouraged to agree the line and the finished face first. iComply fences the line it is shown and does not decide a legal boundary.",
            "Finish and maintenance are part of the choice. Pressure-treated timber still benefits from a preservative or stain every few years. Galvanised steel resists corrosion for a long time, and a powder-coated finish over galvanising adds colour and a further barrier. Cut ends, drilled holes and damaged coating are treated on site so corrosion does not start where the fence was altered.",
            "A written fencing scope lists the run lengths, the height, the product family and profile, the post type and foundation, the gate positions and hardware, the finish, what happens to any old fence and how the site is left. That document is what the price on application is based on, so two quotes can be compared on the same facts.",
        ],
        "focus": [
            "Run length, height and gate positions measured on site",
            "Post and foundation chosen for the ground found",
            "Timber, mesh, palisade or railing matched to the purpose",
            "Planning and boundary points flagged for the owner",
            "Written scope and price on application",
        ],
        "faqs": [
            ("Which type of fencing is right for my site?", "It depends on what the fence has to do. Timber suits gardens and screening, steel mesh and palisade suit commercial and industrial security, and railings suit frontages and schools. A survey records the purpose, the ground and the length before a type is recommended."),
            ("Do I need planning permission for a new fence?", "Many fences do not, but height limits apply in some positions, such as next to a highway, and listed buildings, conservation areas or planning conditions can change things. We flag where a check with the local planning authority is sensible; the owner makes that check."),
            ("How long does fencing last?", "It depends on the material, the finish, the ground and the exposure. Treated timber lasts longer with regular preservative, and galvanised or powder-coated steel lasts far longer than bare steel. The scope states the finish so expectations are clear."),
        ],
        "cross": ["security", "closeboard", "palisade"],
    },
    "fence": {
        "label": "fence", "thing": "fence", "context": "mixed",
        "hook": "a single fence line put up, put right or replaced to the boundary you confirm",
        "paras": [
            "A fence job is usually a single run along a boundary, a side return or a yard edge rather than a whole site. The questions are simple but they matter: how long is the run, how high does it need to be, what is it made of now, what condition are the posts in, and is there a gate. Answering those on site avoids ordering the wrong panels or the wrong post lengths.",
            "Most failed fences fail at the posts. Timber posts rot at ground level, concrete posts crack where a panel has been forced, and steel posts loosen in shallow or soft foundations. Looking at the posts first tells you whether the job is a repair, a part replacement or a new line, and that decision is written down before anything is ordered.",
            "Replacing a fence on the same line is the most common job. The old panels, boards and posts are taken down, the old concrete is broken out or new holes are set alongside it, and the new posts are set plumb and in line. Gravel boards keep timber off the soil and are worth including on almost every timber fence.",
            "Slopes need a decision before work starts. Panels and mesh can be stepped down a slope, which leaves triangular gaps at the bottom that are filled with gravel boards, or the fence can be raked to follow the ground, which suits closeboard and some mesh systems. Which is right depends on the look you want and what the fence must keep in or out.",
            "Neighbours and access are part of the job. Most fences can be built from one side, but finishing the far face, setting posts against an existing wall or working around planting is easier with agreed access. The finished face of a boundary fence conventionally faces outwards, and the scope records which way it is to face.",
            "A fence is quoted after a visit or from clear photos with measurements. The written scope gives the run length, height, product, post type, gate details and how waste is taken away, and the price is on application once that scope is agreed.",
        ],
        "focus": [
            "Run length, height and post condition recorded first",
            "Repair, part replacement or new line decided in writing",
            "Gravel boards, stepping or raking agreed for slopes",
            "Finished face and access agreed before work starts",
            "Price on application",
        ],
        "faqs": [
            ("Can you replace just part of a fence?", "Yes, where the remaining posts and panels are sound. If most posts have rotted or loosened, a new line is usually the better value, and the scope explains why."),
            ("Which way should the fence face?", "Conventionally the finished face of a boundary fence faces outwards, towards the neighbour or the road, with the posts and rails on the owner's side. The scope records the agreed direction."),
            ("What happens to the old fence?", "It is taken down and removed from site unless you ask to keep any part of it. Removal is written into the scope."),
        ],
        "cross": ["closeboard", "posts", "panel-fencing"],
    },
}
