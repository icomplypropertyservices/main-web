#!/usr/bin/env python3
"""Family copy for the trades lock. Run once; output is family-copy.json."""
import json
from pathlib import Path

OUT = Path(__file__).with_name("family-copy.json")

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

F = {}

F["plastering"] = pack(
    "plastering",
    "Plastering in a lived-in house is mostly making a flat, sound surface that paint and paper will sit on. We skim ceilings and walls, replaster rooms that have blown, run coving and cornice, take Artex off where it is safe to do so, dot-and-dab dry lining, and patch the hole left by a leak or a removed wall light. A full-house plaster is a different job from a patch: the whole room is protected, the old material is cut back to a sound edge, and the new coat is ruled flat. We talk you through bonding coat, hardwall and multi-finish in ordinary words. We do not ask you to choose a brand from a catalogue. The free survey looks at how hollow the old plaster is, whether the ceiling is lath and plaster or board, and how we get boards up the stairs.",
    [
        "Skimming is a thin finish coat over sound plaster or new board. If the background is live, we do not skim over it and hope. We cut the loose material out and dub it back out so the skim has something to hold.",
        "Coving and cornice hide the joint between wall and ceiling and they also crack when the ceiling moves. A repair is a short length let into the old run, not a smear of filler along the whole room.",
        "Artex removal is dusty. We protect the room, check what the texture is stuck to, and skim the ceiling afterwards so you are not left with a scarred board.",
        "Dry lining and dot-and-dab suit walls that are out of true. The boards need a proper fix and the sockets need extending. We do not leave the electrician with a box buried in the dab.",
        "Patch plastering after a leak has to go past the stain, not just over it. We stop when the surrounding plaster is firm. A small patch that flashes through paint is a failed patch.",
        "Lath and plaster ceilings in older {place} houses can be tired. We will say if a board-and-skim replacement is the honest repair rather than another skim on laths that have let go.",
        "Venetian and polished plaster are decorative finishes. They need a flat background and a sample agreed before the room is committed. They are not a way to hide a moving crack.",
        "Full-house plastering is sequenced around the other trades. Joiners, electricians and the decorator all need the plaster hard before they return. We write that order into the quote.",
    ],
    [
        "whether the ceiling is plasterboard or lath and plaster",
        "hollow patches you can hear when you tap the wall",
        "how furniture and carpets will be protected",
        "where the boards and the mixer will stand",
        "sockets and switches that need a temporary drop",
        "cracks that are still moving and should not be skimmed over",
        "Artex or other texture and what it is bonded to",
        "the drying time before paint, usually longer than people expect",
        "coving mitres at bays and chimney breasts",
        "access up a tight staircase with long boards",
        "whether a landlord needs the room back for a tenant on a named day",
        "making good around a new doorway or a removed wall",
    ],
    "Plaster is a finish, not a way of hiding damp. If the wall is wet we say so and we do not skim over it.",
)

F["rendering"] = pack(
    "rendering",
    "Rendering is the outside coat on a wall: sand and cement, silicone, monocouche, K-rend, acrylic or a Tyrolean finish. It keeps weather off the masonry and it is also what the street sees. We re-render whole elevations, patch cracks, remove pebble-dash that has let go, and paint render that is sound but tired. A render quote without looking at the substrate is a guess. We check whether the old coat is hollow, whether the wall has had cavity insulation, and where the water is getting in. Pebble-dash removal is noisy and it marks the brick underneath, so we agree the finish before we start. Silicone and monocouche are through-coloured. A patch in those systems has to be planned or it will show.",
    [
        "K-rend and other thin-coat systems need a clean, stable background and the beads and stops the manufacturer expects. We do not trowel a branded name onto dusty old render.",
        "Pebble-dash removal leaves a rough face. The new coat has to be thick enough to hide that, or the brick pattern ghosts through.",
        "Hairline cracks in render are often movement or a bad day-joint. We cut out to a line that can be made good, rather than painting a crack and calling it repaired.",
        "Render and external wall insulation are related but they are not the same visit. If the wall is insulated we do not fix through it without knowing what is behind.",
        "Painting render is only worth doing when the coat is stuck. We tap the elevation first. Hollow render gets cut out.",
        "Sills, bellcasts and the joint with the damp course decide whether the wall sheds water or drinks it. They are on the survey.",
        "A coloured render patch on a {place} semi will show if the batch does not match. We talk about that before you choose a patch over a full elevation.",
        "Scaffold or a safe tower is part of the job on anything above the ground floor. We do not quote a price that pretends the ladders are free.",
    ],
    [
        "hollow render you can hear when it is tapped",
        "cracks at window corners and at floor lines",
        "the finish you want: smooth, textured, or through-coloured",
        "pebble-dash or Tyrolean that is loose",
        "where rainwater runs off the cills",
        "whether the wall has cavity insulation",
        "paint that is peeling because the coat behind it is wet",
        "access for scaffold and where it stands",
        "neighbouring boundaries and shared walls",
        "the colour match on a patch",
        "ground level and the damp-proof course",
        "plants, paths and drives that the droppings will hit",
    ],
    "We match the system to the wall. A cheap coat on a wet substrate fails, and we will not sell you that.",
)

F["bricklaying"] = pack(
    "bricklaying",
    "Bricklaying for a house is garden walls, boundary repairs, repointing, lintels, spalled bricks, chimney rebuilds, brick arches and blockwork. New brickwork has to be tied, level and in the right mortar. Old brickwork in {place} is often soft brick and lime, and a hard cement point drives the damp into the face and the brick spalls. We look at the mortar before we price repointing. A garden wall that leans is not a repoint. It may need rebuilding, and we say which. Lintel replacement means propping. We do not cut a slot under a load and hope. Wall ties and brick arches are separate from a simple point, and they are named if the survey finds them.",
    [
        "Repointing is raked out to a proper depth and packed, not smeared over the old joint. Smear pointing cracks off and looks poor.",
        "Lime mortar suits older soft brick. We use it where the wall was built that way, and we do not default to a bag of strong cement.",
        "Spalled bricks are cut out and matched as closely as the yard allows. A bright new brick in a weathered wall is agreed with you first.",
        "A leaning garden wall is measured. If it is past a safe lean we rebuild it rather than point it and leave the lean.",
        "Chimney rebuilds need roof access and a weather plan. The stack is taken down to sound brick and rebuilt, flaunched and flued as agreed.",
        "Blockwork for an extension or a garage is set out from the drawing, not from a chalk line on the day with no check.",
        "Brick arches over openings sag when the mortar has gone. The repair is propped and rebuilt, not pointed from a ladder in the rain.",
        "Lintels are specified for the span and the load. We do not bed a too-short concrete lintel because it was on the van.",
    ],
    [
        "the mortar type, lime or cement",
        "bricks that are spalled or blown",
        "whether a wall leans or is merely stained",
        "piers and coping on a garden wall",
        "propping if a lintel or an opening is involved",
        "matching brick for patches",
        "foundations on a new wall, even a low one",
        "who owns a boundary before we rebuild it",
        "access for materials along a terrace entry",
        "chimney flashing if the stack is touched",
        "wall ties if the cavity is open",
        "making good the inside if we stitch a crack",
    ],
    "Pointing is not a cosmetic smear. If the joint is not raked out, the new mortar falls off.",
)

F["extensions"] = pack(
    "extensions",
    "A house extension is a single storey, a double storey, a rear, a side return, a wrap-around, a kitchen extension, a garage conversion, a porch or an orangery. The building work is the shell, the opening-up, the roof, the floor and the making good. Planning and building regulations are part of the conversation, not a surprise at the end. We survey the garden, the drains, the neighbour's wall and the way into the site. A side return in a terrace is tight. A wrap-around changes the kitchen and the living room together. We write what is in the quote: excavations, brick or block, the roof type, the steel if a wall comes out, and who does the kitchen at the end. We do not hand you a brochure price for 'an extension'.",
    [
        "Single-storey rear extensions are the common job. The drain run under the new floor is found before we dig, not after the concrete is in.",
        "Double-storey work needs a proper design for the foundations and the joint with the existing roof. We will not start from a sketch on a napkin.",
        "Side-return extensions steal the side alley. We check headroom, the neighbour's gutter and how materials get to the back.",
        "Garage conversions fail when the floor is too low, the walls are single skin and the roof is a flimsy asbestos or felt lid. We say so on the survey.",
        "A porch is small and still needs a damp course, a roof that does not dump water on the step, and a door that actually fits.",
        "Steels for a knock-through are calculated and the padstones are sized. We do not bed a beam on a single brick and call it done.",
        "The quote separates shell from fit-out when you want it that way, so the kitchen and the plaster are not hidden inside one vague line.",
        "Neighbours get told before the digger arrives. Party-wall matters are flagged. We do not pretend they are optional on a terrace.",
    ],
    [
        "drains under the proposed floor",
        "the neighbour's foundations and gutters",
        "access for muck and materials",
        "whether you want shell only or a finished room",
        "the steel if a wall is coming out",
        "roof type: flat, tiled, or a lantern",
        "building regulations and who is inspecting",
        "how long the kitchen will be out of use",
        "electrics and the consumer unit capacity",
        "the floor level against the garden",
        "asbestos in an old garage roof",
        "where the family will live during the dusty weeks",
    ],
    "We build the extension we surveyed. A fixed brochure price with no drawing is not a quote.",
)

F["loft-conversions"] = pack(
    "loft conversions",
    "A loft conversion is a dormer, a hip-to-gable, a mansard, a Velux room-in-the-roof, or the simpler boarding and ladder that makes the loft usable without calling it a bedroom. The structure is the job: joists that were not designed for a bedroom, the stairs, the fire door, the insulation and the way out. We survey the loft, the landing and the roof pitch before we talk about a dormer. A hip-to-gable changes the outside of the house and the neighbour's view of the roof. A Velux conversion keeps the roofline and only works if the headroom is already there. Boarding and a ladder are honest jobs when a full conversion is more than you need. We do not sell you a bedroom that fails the headroom.",
    [
        "Dormers need a structural opening in the roof and a proper cheek and roof. We do not bolt a timber box to rafters and hope.",
        "Hip-to-gable conversions change the gable wall. Party-wall and the tile match are discussed before the scaffold goes up.",
        "Mansards are heavier and less common on ordinary semis. If the roof is not suitable we say so.",
        "Velux loft rooms live or die on headroom and the stair. We measure both on the survey.",
        "Loft boarding for storage is not a conversion. We still check the joists before we load them with board and boxes.",
        "A loft ladder has to land somewhere safe on the landing. We do not cut a hole over the stairwell.",
        "Insulation and ventilation stop the new room sweating. They are in the quote, not an extra on the last day.",
        "Fire separation and the stair enclosure are explained in plain words. A bedroom in the loft without a proper way down is not finished.",
    ],
    [
        "headroom at the ridge and at the eaves",
        "the existing joist size and spacing",
        "where the stairs can land",
        "the water tank and the electrics already in the loft",
        "tile type and whether a dormer will match",
        "party-wall neighbours",
        "insulation and how the roof will breathe",
        "the fire door and the landing",
        "scaffold and how long it stands",
        "whether you need storage only or a room",
        "velux positions and the rafters they cut",
        "what you will do for a toilet if the room is a bedroom",
    ],
    "We measure the loft before we name the type of conversion. Headroom is not a feeling.",
)

F["kitchens"] = pack(
    "kitchens",
    "Kitchen fitting is the units, the worktops, the splashback, the sink, the doors and the making good when the old kitchen comes out. It is also the moment the plumber and the electrician have to be in the right order. We fit kitchens you have bought and we refit tired ones: new doors, a new worktop, a tiled splashback. A full installation includes levelling the units, scribing to uneven walls, and connecting the appliances we agreed. We do not plumb gas ourselves as if we were the Gas Safe engineer. Gas work is carried out by Gas Safe registered engineers. The free survey in {place} checks the walls, the floor, the waste and whether the consumer unit can take the oven and the hob.",
    [
        "Worktops are templated when the units are in, not guessed from an old plan. A stone top that is wrong is an expensive mistake.",
        "Kitchen door replacement freshens a sound carcase. We hinge and align. We do not hang new doors on carcasses that are falling apart.",
        "Splashbacks in tile or panel need a flat wall. We pack or board first if the plaster is blown behind the tiles.",
        "A renovation that keeps the layout is quicker than a move-everything refit. We say which one your waste and your electrics will actually allow.",
        "Plumbing for a sink or a dishwasher is part of the fit when it is on the quote. We test the waste before we push the unit back.",
        "End panels, cornice and pelmet are the bits that make a kitchen look finished. They are listed, not forgotten.",
        "We protect floors and we take the old kitchen away if that was agreed. A hallway full of old units is not a finished job.",
        "Landlords refitting between tenancies need a date. We book the electrician and the decorator around that date rather than leaving you to chase three people.",
    ],
    [
        "whether the units are supply-only or ours to fit",
        "the waste position and the water stop tap",
        "appliance ratings and the consumer unit",
        "gas hobs, which are carried out by Gas Safe registered engineers",
        "wall condition behind the old tiles",
        "worktop material and joints",
        "how long you can be without a sink",
        "the floor, if it is out of level",
        "extraction and where the duct can go",
        "lighting under the wall units",
        "old units leaving the house",
        "the tenant date if the house is a rental",
    ],
    "We fit what was surveyed. Moving a sink across the room is a different quote because the waste is a different quote.",
)

F["bathrooms"] = pack(
    "bathrooms",
    "Bathroom fitting covers a full refit, a bath swap, an en-suite, a wet room, a walk-in shower and a disabled adaptation. Water, waste, ventilation and the tray or the bath have to agree with each other. We strip out, move wastes where the joists allow, board and tank wet areas, and fit the suite you chose. Electric showers are an electrical job as well as a plumbing one, and they are on the quote when you want one. We do not use a CCTV photograph or a plastering photograph to show a bathroom. The survey looks at the floor, the stack, the fan and whether the ceiling below is someone's living room. A wet room needs a fall and a drain. A walk-in shower needs a tray that does not rock.",
    [
        "Wet rooms fail at the tanking and the fall, not at the tiles. We get the floor right before anyone picks a tile.",
        "A bath replacement in a {place} terrace often means a door that is narrower than the bath. We measure the route in, not just the room.",
        "En-suites stolen from a bedroom need a waste that can fall to the stack. If it cannot, we say so before the stud wall goes up.",
        "Walk-in showers need a solid tray, a screen that meets the tray, and a fan that actually extracts.",
        "Disabled adaptations are about the person who will use the room: rails, the shower seat, the door swing. We ask, we do not guess.",
        "Tiles are a separate trade page, but a bathroom is not finished with bare board. The quote says who is tiling.",
        "Extract fans are part of stopping the mould coming back. We duct them outside, not into the loft to sweat on the joists.",
        "Landlords want a bathroom a tenant can use on a date. We test the wastes and the fan before we call it done.",
    ],
    [
        "the route a bath or a tray takes into the house",
        "joist direction before a waste is moved",
        "the soil stack and the fall",
        "ventilation to outside air",
        "tanking on wet walls and floors",
        "electric shower supply and isolation",
        "ceiling condition in the room below",
        "who is tiling and when",
        "the door swing in a small room",
        "mould that is really a fan problem",
        "whether the floor is timber or concrete",
        "a tenant moving back on a named day",
    ],
    "A bathroom is waterproofing and waste first, and the suite second. Pretty tiles on a flat tray with no fall will leak.",
)

print("partial", len(F))
json.dump({"partial": True, "families": F}, open("/tmp/fam_partial.json","w"))
print("ok")
