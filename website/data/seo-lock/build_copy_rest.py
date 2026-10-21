#!/usr/bin/env python3
import json
from pathlib import Path

def pack(work, guide, paragraphs, checks, extra):
    intents = {
        "guide": "This page is the homeowner and landlord guide to {name}. " + extra,
        "near": "You searched {name} because you want someone who will come to {place}. We work from Offerton in Stockport and we book {work} around the people who live there. " + extra,
        "cost": "The honest answer on {name} is that the price follows the building. We survey {place} for free and write the quote before any work starts. " + extra,
        "quote": "Ask for {name} with the address, a photo and whether anyone is in the house. The written quote for {place} names what is included. " + extra,
        "emergency": "If {name} cannot wait, call 07517 806082, say {place}, and tell us what has failed. We will say if a same-day visit is realistic. " + extra,
        "installers": "{name} means the people who do the work, not a directory. We survey {place} and the same enquiry is the one we come back to finish. " + extra,
        "servicing": "{name} is the follow-up visit: check, adjust, or repair what was agreed. You get a short note of what we found in {place}. " + extra,
    }
    return {"work": work, "guide": guide, "paragraphs": paragraphs, "checks": checks, "intents": intents, "fallback": paragraphs[0]}

R = {}

R["joinery"] = pack("joinery",
    "Joinery and carpentry in a house is doors, skirting, architrave, stairs, stud walls, built-in wardrobes and decking. A joiner hangs a door so it latches, scribes skirting to a floor that is not flat, and builds a stud wall that is straight and ready for plasterboard. Fire doors are a particular job: the leaf, the closers, the gaps and the ironmongery have to match the reason the door is there. We do not plane a fire door to nothing and leave the closer off. Stair repairs deal with creaks, broken spindles and treads that have gone. Decking is set out, ventilated and fixed so it does not rock. The survey in {place} measures the openings and the floors before we cut anything.",
    ["Door hanging includes the lining if the old one is out of square. A new door in a twisted frame will always look wrong.",
     "Skirting and architrave are scribed and mitred. We do not fill a 15 mm gap and call it a fit.",
     "Stud walls are set out from the drawing, nogged for the plasterboard, and headed out for doors. Sockets are marked so the electrician is not guessing.",
     "Built-in wardrobes are made to the alcove, including the ceiling that is not level. We agree shelves and hanging rail before we cut the board.",
     "Stair repairs start with what is loose. We do not carpet over a moving tread and hope the creak goes.",
     "Decking needs airflow underneath and a frame that is not sitting in soil. We set the falls so water leaves the house wall.",
     "Internal doors in a rental are often slammed out of alignment. We rehang and adjust before we replace a door that is still sound.",
     "Fire door fitting is explained in plain words: why the gaps matter and why the closer stays on."],
    ["opening sizes, not a guess from the old door","floors that run out and affect skirting","fire door rating if one is required","where a stud wall meets the ceiling","stair winders and what is actually loose","decking levels against the damp course","alcove widths for wardrobes","ironmongery you want to keep","decorating after the joinery","access for long lengths of timber","landlord standards between tenancies","making good plaster where a frame is eased"],
    "Joinery is measured and scribed. A door that does not latch is not finished.")

R["flooring"] = pack("flooring",
    "Flooring is laminate, LVT, engineered wood, vinyl, sanding, levelling, screed, and the joist or subfloor repairs that have to happen first. A pretty floor on a bouncing or damp subfloor fails. We check moisture, level and movement before we name the product. Laminate needs expansion gaps. LVT needs a flat floor or the joints show every bump. Engineered wood moves with the room and needs the right acclimatising. Sanding an original floor is only worth it when the boards are thick enough. Screed and levelling compound are the unglamorous part that makes the finish look right. Joist repairs are opened up, sistered or replaced, and boarded again.",
    ["Laminate over an uneven floor drums and the joints open. We level first or we tell you the floor is the wrong choice.",
     "LVT is only as flat as the floor under it. Trowel ridges and old adhesive show through.",
     "Engineered boards need a dry subfloor. We test. We do not stick wood to a damp slab and hope.",
     "Floor sanding creates dust. We protect the stairs and we stop if the boards are too thin to sand again.",
     "Vinyl in a bathroom or a kitchen needs a sound floor and sealed edges. It is not a cure for a rotten chipboard.",
     "Screeding a room is a level and a dry-time. Furniture goes back when it is ready, not when the calendar says so.",
     "Joist repair means lifting boards. We agree how much of the room is opened before we start.",
     "Subfloor boards that are soft over a leaking pipe get replaced, and the leak is named rather than boarded over."],
    ["moisture in the slab or the timber","how far the floor is out of level","skirting off or left on","doors that will need easing","furniture and who moves it","adhesive left from an old vinyl","underfloor heating if there is any","the stair nosing at the landing","expansion gaps at pipes","a tenant's date to move back","waste removal of the old floor","thresholds between rooms"],
    "We will not lay a finish on a floor that moves or is wet. The survey is where that is found.")

R["tiling"] = pack("tiling",
    "Tiling is wall tiles, floor tiles, bathroom and kitchen splashbacks, porcelain, and regrouting when the tiles are sound but the joints are not. The background has to be flat and, in a wet area, tanked. We set out so you are not left with a sliver against the door. Porcelain needs the right adhesive and the right trowel. Regrouting is cutting out the old joint, not painting over mould. A bathroom retile often follows a leak, so we look at the boards behind before we stick new tiles to a soft wall.",
    ["Wall tiles on blown plaster fall off. We strip back to a sound face or we board.",
     "Floor tiles need a flat, stiff floor. A timber floor that bounces will crack grout however nice the tile is.",
     "Bathroom tiling starts after the tray and the tanking, not before.",
     "Kitchen splashbacks are set out from the worktop. Sockets are cut neatly, not chewed out.",
     "Porcelain is harder on tools and on the adhesive. We do not fix it with a leftover tub from a ceramic job.",
     "Regrouting a mouldy joint without a fan still working means the mould comes back. We say if the fan is the real job.",
     "Movement joints at the tray and at internal corners stop the grout cracking in a month.",
     "We agree the layout, including any feature tile, before the first row goes on."],
    ["flatness of the wall or floor","tanking in wet zones","tile size and how it sets out","trim colour at external corners","grout colour","sockets and valves that need cutting","the tray or bath already fitted","floor stiffness","adhesive suitable for the tile","who is supplying the tiles","drying time before the shower is used","mould in old grout and the fan"],
    "Tiles are stuck to a prepared background. We do not tile over paint that is already letting go.")

R["decorating"] = pack("decorating",
    "Painting and decorating is interior rooms, exteriors, wallpaper, end-of-tenancy decoration and the mould wash that has to happen before new paint. A decorator's quote depends on the surface: new plaster, old paper, stained ceilings, rotten exterior timber. We prepare. We do not roll emulsion over greasy kitchen walls and call it two coats. Wallpaper is lined where it needs it. Exterior painting includes the putty, the bare timber and the parts you cannot see from the path. End-of-tenancy work is agreed against the inventory, not against a hope that one coat hides five years.",
    ["New plaster needs a mist coat. Skipping it is why the first proper coat flashes.",
     "Stained ceilings from a leak are sealed before they are painted, or the stain returns.",
     "Wallpaper stripping can pull the plaster face off. We test a patch and we tell you if the wall then needs a skim.",
     "Exterior joinery is rubbed down, filled and primed. Paint on rotten timber is not a repair.",
     "End-of-tenancy decorating is listed room by room so you and the agent can see what was done.",
     "Commercial decorating is booked around the people using the building. We agree which rooms are empty on which day.",
     "Mould is cleaned and the cause is named. Paint is not a mould treatment.",
     "Colour changes over dark paint need more coats. The quote says so rather than surprising you."],
    ["new plaster or old paint","wallpaper to come off","stains and whether they are dry","exterior timber condition","access for high stairwells","colours and who is supplying paint","furniture moved or covered","an inventory if it is a rental","mould and ventilation","how many rooms in one visit","gloss work on doors and skirting","the date a tenant returns"],
    "Preparation is the job. Two coats on a dirty wall will not last, and we will not pretend it will.")

R["groundworks"] = pack("groundworks",
    "Groundworks is the digging: foundations, excavation, site clearance, concrete bases, retaining walls and the landscaping that follows. Water and the ground conditions decide the job. We do not price a foundation from a photograph of a lawn. A concrete base for a shed is not the same as a base for an extension. Retaining walls hold ground up and they need drainage behind them or they lean. Clearance includes what leaves the site. Landscaping after the dig is levels, topsoil and a surface you can walk on, agreed before we cart the spoil away.",
    ["Foundations are dug to the depth the design needs, and we stop if the ground is not what everyone expected.",
     "Excavation next to a neighbour's wall is careful and it is discussed. Undermining a party wall is not a surprise we spring.",
     "A concrete base is level, thick enough, and on a compacted bed. A thin skin of concrete on soft ground cracks.",
     "Retaining walls get weep holes or a drain. A pretty wall with wet clay behind it moves.",
     "Site clearance is priced by what is there: sheds, spoil, green waste. We look first.",
     "Levels against the house damp course matter. We do not build the garden up over the DPC.",
     "Access for a mini digger through a terrace entry is often the real constraint. We measure the gate.",
     "Spoil stays or goes. The quote says which, because carting muck away is a cost."],
    ["the gate width for a machine","drain runs we might cut","neighbour's wall and foundations","damp-proof course height","what the base is for","spoil leaving the site","services in the ground, including your own electric","retaining height","topsoil put back or not","concrete thickness","weather and a wet clay site","who marks the lawn we must not track over"],
    "We dig what was set out. A foundation price without a look at the ground is not a price.")

R["drainage"] = pack("drainage",
    "Drainage work for a house is blocked drains, repairs, unblocking, CCTV surveys of the pipe (a camera in the drain, not a security camera), soakaways, relining, gullies and manholes. A smell or a slow sink can be a trap, a collapse or a root. We do not quote a new drain run because a gulley is full of leaves. The survey, often with a camera, shows the pipe. Emergency call-outs are for sewage where it should not be, or a drain that is flooding the garden in the rain. Relining suits a pipe that is cracked but still in line. A collapse is a dig. Soakaways are sized to the roof they serve and to the ground they sit in.",
    ["Blocked drains are cleared and then, if it blocked for a reason, we camera them. Clearing the same roots every month is not a repair.",
     "A CCTV drain survey is a recording of the pipe. You can see the crack, the offset or the root. It is not a guess from the manhole.",
     "Gully repairs stop the path water going into the house. We reset or replace the pot and the pipe that leaves it.",
     "Manhole repairs are frames, covers and benching. A rocking cover on a {place} pavement is a trip and a rattle.",
     "Soakaways fail when they are too small or the ground is clay. We say if a soakaway is the wrong answer.",
     "Relining avoids a dig across a drive when the pipe is a candidate for it. If it is collapsed, we excavate.",
     "Emergency drainage is about stopping the damage today and booking the proper repair, not about a permanent patch at midnight.",
     "We mark where the pipe runs before anyone digs the garden. Hitting your own drain is an avoidable mess."],
    ["where the smell or the flood actually is","manholes we can lift","whether it is foul or rain water","trees near the run","a camera survey or a simple clear","the drive or path we might dig","shared drains with a neighbour","soakaway ground, clay or gravel","gullies at the foot of downpipes","covers that rock","a backup into the kitchen in heavy rain","access for a jetter"],
    "A camera in a drain tells the truth. We do not dig the garden on a hunch, and we do not call a drain survey CCTV security.")

R["driveways"] = pack("driveways",
    "Driveways and the paving around them are block paving, tarmac, resin, imprinted concrete, patios and dropped kerbs. The base is the job. A pretty block on a soft base sinks, and a resin drive on a cracked tarmac telegraphs every crack. We look at levels, the damp course of the house, and where the water will go. A dropped kerb is a council matter as well as a building one, and we tell you that before you think the quote includes the pavement. Patios are falls away from the house. Repairs to sinking blocks are lifted, the base made good, and the blocks relaid, not just sanded over.",
    ["Block paving is edge-restrained. Without a proper edge the drive creeps into the garden.",
     "Tarmac needs a sound base and a thickness. A skim coat over rubble does not last a winter.",
     "Resin is only as good as the surface under it. We repair that surface first.",
     "Imprinted concrete is a finish and a slab. Cracks and colour are discussed before it is poured, not after.",
     "A patio against the house falls away from the wall. We do not lay flags that tip water into the air brick.",
     "Dropped kerbs need the right permission. We do not cut the pavement and hope.",
     "Paving repairs match as well as the old stock allows. We show you the closest block before we order a pallet.",
     "Drainage of the drive is part of the quote. A new impermeable drive that dumps water on the neighbour is a bad drive."],
    ["levels against the damp course","where rainwater will go","the existing base, if we are overlaying","edge restraints","a dropped kerb and the council","manholes in the drive","the colour and the block you want","access for wagons of aggregate","gates and the width of the car","patio falls","trees and roots under the area","a neighbour's lower garden"],
    "The base and the falls matter more than the colour of the block. We price those, not a photograph of someone else's drive.")

R["fencing"] = pack("fencing",
    "Fencing is panels, close-board, concrete posts, repairs and garden gates. A fence is only as good as the posts. We set posts in the right ground, to a depth that holds, and we do not spike a post into rubble and call it a boundary. Close-board is built on site to follow a slope. Panel fences are quicker and they still need posts that are plumb. Repairs replace the rotten post rather than nailing a new panel to it. Gates are hung to the post that can carry them, with a brace that does not sag. Who owns the boundary is your question to answer; we will not move a fence onto a neighbour because it looks tidier.",
    ["Concrete posts outlast timber in wet {place} clay. We talk about that where the old posts have rotted at the ground.",
     "Close-board follows the slope. Stepping a panel fence is the alternative, and it looks different. We agree which.",
     "A gate needs a slamming post and a hanging post that are not already loose.",
     "Panel replacement on rotten gravel boards and soft posts is a waste. We replace what has failed.",
     "Arris rails and boards on a close-board run are the repair when the posts are still sound.",
     "We ask about the neighbour before we take a run of fence down. A surprise demolition of a shared boundary is not how we work.",
     "Gravel boards keep the panel out of the soil. Skipping them is why the bottom of the fence rots.",
     "The quote says the height, the style and who is taking the old timber away."],
    ["who owns the boundary","post condition at ground level","the slope","gates and the width of the opening","gravel boards","concrete or timber posts","the neighbour's plants on the line","access along the run","old concrete that is still sound","height you are allowed and want","waste timber","whether the dog can get out while the fence is down"],
    "Posts first, panels second. A new panel on a rotten post is not a repair.")

R["damp"] = pack("damp proofing",
    "Damp proofing for a house is finding why the wall is wet, then doing the thing that stops it. Rising damp, penetrating damp, condensation, a failed damp-proof course, tanking, basement waterproofing, cavity wall ties, mould, dry rot and woodworm are not one product. Condensation is often ventilation and heating, not an injection. Penetrating damp is a gutter, a pointing failure or a high garden. Rising damp is a specific diagnosis, not a label for every tide mark. We survey. An emergency visit is for water that is damaging the house today. Tanking a wet basement is a system, with the floor and the walls thought about together. We do not sell an injection cream for a leaking gutter.",
    ["A damp survey is the first job. It includes the outside: gutters, ground levels, pointing, and the cavity if we can see it.",
     "Condensation mould in a bedroom is often the fan, the cold wall and the way the room is used. We treat the cause.",
     "Penetrating damp shows after rain. We look at the elevation in the weather, not only at the stain indoors.",
     "A damp-proof course injection is offered when the diagnosis supports it, not as a default.",
     "Tanking and basement waterproofing are designed. A painted slurry on a wet wall with no drainage is not a system.",
     "Cavity wall ties and debris in the cavity bridge damp. If that is the cause, an injection will not fix it.",
     "Dry rot and woodworm need the timber opened up enough to see the extent. We do not spray a skirting and leave the joist.",
     "Mould is cleaned after the moisture is dealt with. Painting over it is not the treatment."],
    ["gutters and downpipes","ground levels against the wall","the existing damp-proof course","condensation or a true damp path","salt staining","timber that is soft","ventilation and the fan","a basement or a cellar","cavity insulation or rubble","how long the stain has been there","a tenant's health concerns, explained plainly","what we will not inject"],
    "We diagnose first. Injection cream is not the answer to a blocked gutter.")

R["insulation"] = pack("insulation",
    "Insulation work is the loft, the cavity, internal wall insulation, external wall insulation and the underfloor quilt that stops a suspended floor being cold. It is also spray-foam removal, which is its own job because foam in a roof can hide rotten timber and can cause trouble when you sell. We do not pump a cavity that is full of rubble or already damp. A loft top-up is straightforward when the loft is empty and dangerous when it is full of boarding, tanks and old wiring. Internal insulation loses a little room and needs a careful vapour check. External insulation changes the look of the house and the details at windows and sills. We survey the loft and the walls before we name a thickness.",
    ["Loft insulation is laid so the eaves can still breathe. Blocking the ventilation causes the condensation the insulation was meant to avoid.",
     "Cavity wall insulation is only for a cavity that is suitable. We check. A wet or rubble-filled cavity is a no.",
     "Internal wall insulation is detailed at the reveals and the floor so you do not create a cold strip and a mould line.",
     "External wall insulation is a system with beads, sills and a render. It is not a slab stuck on and forgotten.",
     "Underfloor insulation in a suspended floor needs access and a still-sound joist. We do not staple quilt to rotten timber.",
     "Spray foam removal is slow and it is dusty. We inspect the rafters as the foam comes off and we tell you what we find.",
     "Boarding a loft after insulating it needs a raised platform so the quilt is not crushed.",
     "Electrics and downlights in an insulated ceiling are checked so a lamp is not buried in quilt."],
    ["eaves ventilation","what is already in the cavity","the loft hatch and boarding","tanks and pipes that need lagging","downlights and wiring","internal room sizes if we line the walls","the outside appearance if we insulate outside","spray foam and the rafters under it","access under a suspended floor","moisture already in the wall","a mortgage or sale that has asked about foam","the thickness you actually have room for"],
    "Insulation that blocks ventilation or covers a damp wall creates a new problem. The survey is there to stop that.")

print("rest1", len(R))
json.dump(R, open("/tmp/fam_rest1.json","w"))
