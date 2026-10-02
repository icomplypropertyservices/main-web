"""Unique copy for BUILDING fabric / repairs job-type pages.

Each slug has its own mechanism, survey points and a limit we will not cross.
The builder turns this into website/data/job-packs/building-fabric.json.
Do not invent catalogue £ prices. Quotes stay written POA after a look.
"""

from __future__ import annotations

# slug -> intro, body, cost driver, two job-specific FAQs (q, a), three focus lines, two search phrases
Brief = dict

BRIEFS: dict[str, Brief] = {}


def add(slug, intro, body, driver, q1, a1, q2, a2, focus, searches):
    BRIEFS[slug] = {
        "intro": intro.strip(),
        "body": body.strip(),
        "driver": driver.strip(),
        "faqs": [(q1.strip(), a1.strip()), (q2.strip(), a2.strip())],
        "focus": [f.strip() for f in focus],
        "searches": [s.strip() for s in searches],
    }


# ---------------------------------------------------------------------------
# Brickwork & masonry
# ---------------------------------------------------------------------------

add(
    "blockwork-partitions",
    "Blockwork partitions are the concrete-block walls used to split a room, a garage or a light commercial bay when a timber stud is the wrong build-up.",
    "We set the wall out to the line you have agreed, check the floor can take a block wall, and bed the blocks in a mortar that suits the job rather than whatever is left on the van. Openings get a lintel that has been specified. The face is left ready for plaster, or fair-faced if that was the scope. If the new wall is meant to carry a floor or a roof, a structural engineer sizes it before we build. You get photos, a written scope and a POA once we have seen the room.",
    "wall length, openings and whether the floor can carry blockwork",
    "Can you build a block wall that holds up a floor?",
    "Only after a structural engineer has sized it. We build the wall we are given; we do not guess a load-bearing partition.",
    "What finish do you leave?",
    "A straight face ready for plaster, or a fair face if the quote says the blocks stay on show.",
    ["Set-out to an agreed line", "Lintels specified, not guessed", "Load-bearing walls only with an engineer's design"],
    ["concrete block partition wall", "block wall to split a room"],
)

add(
    "brick-replacement",
    "Brick replacement is for single frost-blown, spalled or cracked bricks, cut out and matched, not a whole elevation taken down.",
    "We cut the failed brick out without knocking the neighbours loose, and we match colour, size and texture as closely as the current brick market allows. Older imperial bricks and soft reds often cannot be a perfect match, and we say so before we start. Mortar is pointed to the existing joint, including a lime mix on older solid walls rather than a hard cement ribbon that cracks the face. The quote is a written POA after we count the bricks and see the access.",
    "how many bricks, how close a match exists, and scaffold or ladder access",
    "Can you match my bricks exactly?",
    "Sometimes. Imperial and weathered bricks are often a near match. We show you the sample before we bed them.",
    "Will you use cement on an old solid wall?",
    "Not if the wall is lime. A hard cement joint traps moisture and blows the face off the bricks around the repair.",
    ["Cut out failed bricks only", "Colour and size matched as closely as stock allows", "Mortar matched to the existing joint"],
    ["spalled brick replacement", "replace cracked bricks"],
)

add(
    "brickwork-repairs",
    "Brickwork repairs cover the small masonry jobs that stop a wall getting worse: loose bricks, open joints, a failed pier, or impact damage at low level.",
    "The visit starts with what is actually loose, not a price for 'the wall'. We photograph the defect, tap for hollow joints and check whether the damage is local or a sign of movement. Local repairs are raked out, rebuilt and pointed. If the crack is wide, stepped or still moving, we stop and say you need a structural look before more mortar is thrown at it. Pricing is POA after that judgement.",
    "whether the damage is local or a sign of movement",
    "Do you repoint a cracked wall and call it fixed?",
    "No. A moving crack needs a cause. We repair settled local damage and we flag movement instead of hiding it.",
    "Is this the same as a full repoint?",
    "No. This job is the failed area. A whole-elevation repoint is quoted as its own scope.",
    ["Local rebuild of loose masonry", "Movement flagged instead of covered", "Photos with the written POA"],
    ["house brick repairs", "masonry repair visit"],
)

add(
    "chimney-brickwork",
    "Chimney brickwork is the stack itself: spalled bricks, open joints, a leaning pot course or a flaunching that has let the pots go.",
    "We look at the stack from a safe access, not from the pavement with binoculars if the quote is for works. Failed bricks are cut out, pots are reset only if the flaunching can hold them, and open joints are raked and pointed in a mix the stack can live with. If the stack is leaning, split or missing a pot, we say rebuild or take down rather than a cosmetic point. Roof flashings around the stack are a roofing job and are quoted separately if they are the leak. The figure is POA after access is known.",
    "stack height, safe access and whether the stack is still sound",
    "Can you point a leaning chimney?",
    "We will not point a stack that is leaning or split and call it safe. That one comes down or is rebuilt to a design.",
    "Does this include the lead flashing?",
    "No. Lead around the stack is chimney lead dressing under roofing. We will tell you if that is the actual leak.",
    ["Stack inspected from proper access", "Pots reset only if the flaunching can hold them", "Leaning stacks are not 'just pointed'"],
    ["chimney stack brick repair", "repoint a chimney"],
)

add(
    "external-brickwork-making-good",
    "External brickwork making good is the tidy-up after another trade has been through the wall: a new opening, a removed flue, a meter box or a scaffold tie.",
    "We tooth new bricks into the existing bond, keep the gauge, and point so the patch does not read as a cement frame. Where the opening was temporary, we build back to the original line. Making good does not include the lintel design or the window itself. If the hole is larger than the quote assumed, we remeasure before we build. You get a written POA based on the hole we can see.",
    "the size of the hole and how well the surrounding bond can be toothed",
    "Can you make good after a window company?",
    "Yes, if the opening is ready and any lintel is already in. We brick up to the frame; we do not supply the window.",
    "Will the patch disappear?",
    "New bricks weather slower than old ones. We match as closely as we can and we do not promise an invisible patch on day one.",
    ["Toothed into the existing bond", "Gauge kept so the courses line through", "Remeasured if the hole is bigger than the quote"],
    ["brick up a hole in the wall", "making good after removal"],
)

add(
    "garden-wall-building",
    "Garden wall building is a new boundary or retaining-height garden wall in brick or block, with foundations that suit the height.",
    "A low boundary wall and a wall holding back soil are different jobs. We agree height, piers and coping before we dig. Foundations are sized for the height; we do not sit a tall wall on a skim of concrete. If the wall retains a driveway or a neighbour's ground, we say so and we do not design that retaining wall ourselves. Copings are set to shed water. The quote is POA after we have seen the ground and the length.",
    "length, height and whether the wall is retaining soil",
    "Do you need building control for a garden wall?",
    "Often, once the wall is tall or next to a highway. We tell you when the height means you should ask building control before we build.",
    "Will you build a retaining wall?",
    "Only a simple garden wall. Soil-retaining walls need a design we do not invent on site.",
    ["Foundations sized for the height", "Copings set to shed water", "Retaining walls are not guessed"],
    ["new garden wall", "brick boundary wall"],
)

add(
    "landlord-brickwork-repairs",
    "Landlord brickwork repairs are the void and in-tenancy masonry jobs agents actually raise: a damaged pier, a loose air brick, impact at the bin store, or open joints letting water into a reveal.",
    "We work to an agent's list, photograph before and after, and keep the tenant's access in the note. The scope is the items on the list, not a free survey of the whole terrace. If we find movement or a leak that the list did not mention, we stop that item and send a separate POA rather than expanding the job on the day. Mortar on older landlord stock is matched, not smeared with a hard cement.",
    "how many items are on the agent's list and whether the property is occupied",
    "Can you invoice the agent with photos?",
    "Yes. Before-and-after photos and a short note of what was done travel with the invoice.",
    "Will you add extra bricks while you are there?",
    "Only if you agree a revised POA. We do not grow the list because we are already on site.",
    ["Agent's list, item by item", "Occupied access written into the visit", "Extra defects quoted separately"],
    ["landlord masonry repairs", "letting agent brickwork"],
)

add(
    "masonry-crack-repair",
    "Masonry crack repair is for a crack that has a cause we can see: a local settlement scar, a lintel bearing that has opened, or a joint that has sheared at a corner.",
    "We measure the crack, look at whether it is tapered, stepped or still opening, and we do not stitch a wall that is still moving. Local raking and repointing suits a crack that has stopped. Helical stitching or a lintel is only done against a structural engineer's specification. Covering a live crack with mortar is how you hide a problem, and we will not do it. The POA states which of those paths the crack is on.",
    "whether the crack is historic or still moving",
    "Can you stitch a cracked wall?",
    "Yes, when an engineer has specified the stitch. We do not decide bar size and spacing from the pavement.",
    "Is a hairline crack always structural?",
    "No. Many are old and quiet. We say which ones need watching and which ones need more than mortar.",
    ["Crack measured before any mortar goes in", "Live movement is not pointed over", "Stitching only to an engineer's spec"],
    ["cracked brick wall repair", "masonry crack stitching"],
)

add(
    "opening-forming-brickwork",
    "Opening forming in brickwork is cutting a new door or window opening, and it starts with a lintel design rather than a disc cutter.",
    "We do not cut a hole and prop it with timber 'for now' as the finished job. The opening width, the load above and the wall type decide the lintel. A structural engineer or the lintel manufacturer's table sets that. We then cut, needle or prop as the design says, install the lintel, and make the reveals good. Padstones and bearings are part of the scope, not an extra discovered at the end. Building control is usually involved, and we say that before we book the cut. Price is POA once width and load are known.",
    "opening width, what the wall is carrying, and building control",
    "Can you knock a doorway through this week?",
    "Only when the lintel is specified and any building-control notice you need is in hand. The cutter is the last step.",
    "Do you design the steel?",
    "No. We install the lintel the design names. We do not size a steel from a photo.",
    ["Lintel specified before the cut", "Bearings and padstones included in the scope", "Building control flagged up front"],
    ["form a new doorway", "cut a window opening in brick"],
)

add(
    "repointing-services",
    "Repointing is raking out failed mortar and packing a new joint that matches the wall, elevation by elevation, not a smear of mortar over the old joint.",
    "We rake to a proper depth, brush the joint out, and point in a mix chosen for the brick. Soft reds and pre-1919 solid walls usually want a lime mortar. A hard cement strap across those faces traps water and blows the arris off the brick within a few winters. We sample a small area first where the colour match matters. Access, elevation area and the mix are what the written POA is based on.",
    "elevation area, mortar type and access",
    "Do you repoint over the top of the old mortar?",
    "No. The joint is raked out. Buttering over a failed joint falls out.",
    "Is cement cheaper and stronger?",
    "It can be harder, which is the problem on an old wall. Stronger than the brick means the brick fails first.",
    ["Joints raked, not smeared", "Lime where the wall is lime", "Sample panel where colour matters"],
    ["repointing house walls", "lime mortar repointing"],
)

add(
    "air-brick-replacement",
    "Air brick replacement swaps a blocked, broken or buried ventilator so the floor void or cavity can still breathe.",
    "We check the air brick is actually a vent path, not a dummy, and that the ground level outside has not been built up over it. A new clay or plastic air brick is cut in and pointed. If a patio or a new path has covered the vent, replacing the grille does nothing until the outside level is dropped, and we say that. We do not seal air bricks to 'stop draughts' under a suspended timber floor. The POA covers the number of vents and any making good.",
    "how many vents, and whether the outside ground has buried them",
    "Can I block air bricks to stop draughts?",
    "Not under a suspended timber floor. Those vents are there so the joists do not sit in still, damp air.",
    "What if the path covers the vent?",
    "The path or the ground level has to drop. A new grille above a buried duct does not ventilate anything.",
    ["Vent path checked, not just the grille", "Buried vents called out", "Suspended floors are not sealed up"],
    ["replace air brick", "blocked underfloor vent"],
)

add(
    "below-dpc-brick-replace",
    "Below-DPC brick replacement is the course at and under the damp-proof course, where splash, soil and salts blow the face off the brick.",
    "We cut out the failed bricks below the DPC line and bed replacements without bridging the DPC with mortar, render or a new path. If the outside ground is above the DPC, new bricks will fail again until the level drops, and that groundwork is quoted separately. We do not inject a chemical DPC as part of a brick swap. The written POA separates the brick count from any level change.",
    "brick count and whether outside ground is bridging the DPC",
    "Will new bricks stop the damp inside?",
    "They stop the failed face. Damp inside is a different diagnosis. We do not sell a brick swap as a damp treatment.",
    "Can render be taken down over the DPC?",
    "Render that bridges the DPC is part of the problem. We stop the finish above the DPC line.",
    ["DPC left unbridged", "High ground levels quoted separately", "Not sold as a damp-proof treatment"],
    ["replace bricks below damp course", "spalled bricks at ground level"],
)

add(
    "brick-arch-repair",
    "Brick arch repair rebuilds a sagging or slipped soldier or gauged arch over a window or door, with support while the bricks are out.",
    "An arch is structure. We prop the masonry above before any brick comes out, rebuild to the original curve, and repoint the ring. Flat soldier courses that have dropped usually need the lintel behind them looking at; if that lintel has failed, rebuilding the face bricks alone will drop again. We say which one you have before we price. The POA includes the propping, not just the bricks.",
    "whether the arch is the support or only a face in front of a lintel",
    "Can you repoint a sagging arch?",
    "Pointing does not lift a dropped arch. The bricks have to come out under support and go back to the curve.",
    "Do you prop the wall?",
    "Yes. We do not pull arch bricks out of a live opening and hope.",
    ["Opening propped before bricks come out", "Curve rebuilt, not just pointed", "Failed lintel behind the arch is called out"],
    ["sagging brick arch", "repair window arch"],
)

add(
    "cavity-tray-repair-support",
    "Cavity tray repair support is the brickwork around a failed or missing tray over a window, a roof abutment or a meter box, where water is crossing the cavity.",
    "Water staining inside above an opening is often a tray, stop-ends or weep problem, not 'the bricks'. We open the outer leaf enough to see the tray, replace or reset it with stop-ends, and put weeps back so the cavity can drain. We do not inject the wall and hope. If the tray needs a longer opening than a local repair, we remeasure and requote. The figure is POA after we know how much outer leaf has to come off.",
    "how much outer leaf has to come off to reach the tray",
    "Is a damp patch above a window rising damp?",
    "Usually not. Staining above an opening is often a tray or a weep. We look before anyone talks about injection.",
    "Do you guarantee the cavity is dry?",
    "We repair the tray we can see and confirm weeps. We do not certify the whole cavity from one opening.",
    ["Tray and stop-ends checked", "Weeps put back so the cavity drains", "Not treated as rising damp by default"],
    ["cavity tray leak", "damp above window brickwork"],
)

add(
    "chimney-stack-repoint",
    "Chimney stack repoint is the joints on the stack only, once we know the bricks themselves are still sound.",
    "High, thin joints on a stack take weather on every face. We rake them out and point in a mix that will not trap water in the brick. If the bricks are already blown, pointing over them wastes the access cost, so the quote splits sound joints from bricks that need cutting out. Flaunching and pots are included only if the quote says so. Access is usually a scaffold or a safe tower, and that is the largest part of the POA.",
    "access to the stack and how many bricks are already blown",
    "Can you repoint a chimney from a ladder?",
    "Only when the stack and the roof make that safe. Most stacks are priced with proper access, not a hero ladder.",
    "Does repointing include new pots?",
    "No, unless the quote says the pots and flaunching are in. Open joints and loose pots are different defects.",
    ["Joints raked on all weathersides", "Blown bricks separated from the pointing price", "Access priced honestly"],
    ["repoint chimney stack", "chimney mortar repair"],
)

add(
    "garden-wall-rebuild",
    "A garden wall rebuild takes down a wall that has leaned, lost its piers or shed its coping, and builds it again rather than pushing it upright.",
    "Leaning garden walls are rarely 'a bit of pointing'. We agree what comes down, whether foundations can be reused, and how high it goes back. Neighbour foundations and party fences are identified before we start. Salvaged bricks are reused only if they are sound. A wall that retains soil is not rebuilt as a simple boundary. You get a written POA for the length we have measured.",
    "length, whether the foundation can be reused, and if soil is being held back",
    "Can you push a leaning wall straight?",
    "No. A wall that has leaned has failed. It comes down and goes back up.",
    "Will you reuse the old bricks?",
    "When they are sound. Spalled bricks go. We say what the salvage looks like before we price materials.",
    ["Failed wall taken down, not propped forever", "Foundations checked before reuse", "Soil-retaining walls are a different job"],
    ["rebuild leaning garden wall", "collapsed boundary wall"],
)

add(
    "landlord-pointing-pack",
    "A landlord pointing pack is a defined repoint of the elevations an agent has listed, usually gables, reveals or a front that has shed its joints before a let.",
    "We walk the list, mark the metres that are actually open, and quote those metres. Sound joints are left alone. The mix follows the wall: lime on older solid stock, a gauged mix only where the existing joint is already cement and the brick can take it. Occupied houses are sequenced around the tenant. Photos of each elevation go back to the agent with the invoice. The price is POA per the measured metres, not a guess from a street photo.",
    "measured metres of open joint, mix and occupied access",
    "Do you repoint the whole house because one gable is open?",
    "No. The pack is the listed metres. We mark anything else and leave it off the invoice until you add it.",
    "Can the tenant stay?",
    "Usually yes. We agree windows, drying lines and access before the scaffold or tower goes up.",
    ["Only open joints are priced", "Mix matched to the wall", "Agent photos with the invoice"],
    ["landlord repointing", "pointing before a let"],
)

# ---------------------------------------------------------------------------
# Roofing
# ---------------------------------------------------------------------------

add(
    "chimney-repairs",
    "Chimney repairs on the roofing side are the weatherings: flaunching, a loose pot, a cracked back gutter or the lead and soakers that let water in at the stack.",
    "We separate a roofing leak at the stack from a brick stack that needs rebuilding. Flaunching is renewed when it has cracked away from the pots. Soakers and step flashings are dressed back only if the lead is still sound; short, tired lead is renewed rather than taped. A back gutter full of debris is cleared and the outlet checked. If the stack itself is leaning or the bricks are gone, that is chimney brickwork and we say so. Access drives the written POA.",
    "access and whether the leak is lead, flaunching or the stack itself",
    "Is cement fillet a proper chimney repair?",
    "A cement fillet smeared over failed lead is a common leak. We renew the weathering the detail needs, we do not add another fillet on top.",
    "Do you take the stack down?",
    "Not on this job. If the masonry has failed we stop and quote the stack separately.",
    ["Lead and flaunching diagnosed separately", "Cement fillets are not the repair", "Failed stacks handed to brickwork"],
    ["chimney leak repair", "flaunching and pot repair"],
)

add(
    "emergency-roof-repair",
    "An emergency roof repair is a temporary or same-visit stop to water that is coming in now, so the ceiling and electrics are not left open overnight.",
    "We make the building keep water out: a slipped tile back in place, a sheet over a hole, a cleared gutter that is overflowing into the wall. The visit is not a full re-roof slipped in under the word emergency. If the covering is at the end of its life we say that, do the minimum to stop the water, and send a written POA for the proper repair. Tell us if water is reaching electrics. We do not climb an unsafe roof in high wind just because the phone was urgent.",
    "how the water is getting in and whether the roof is safe to get onto",
    "Will you re-roof the same day?",
    "No. The emergency visit stops the water. A new covering is surveyed and priced on its own.",
    "What if it is too windy to get on the roof?",
    "We do not go up. We will still try to keep water off electrics from a safe position and book the repair.",
    ["Stop the water first", "Temporary work is labelled temporary", "Unsafe roofs are not climbed"],
    ["emergency roofer", "roof leaking today"],
)

add(
    "epdm-flat-roof",
    "An EPDM flat roof is a single-ply rubber membrane dressed to the upstands, outlets and trims, not a coat of liquid painted over a tired felt.",
    "We look at the deck. A spongey chipboard or rotten ply is replaced before any membrane goes down; laying rubber over a soft deck is how the next leak starts. Falls, outlets and drip trims are set so water leaves the roof. Upstands are taken high enough behind the flashing. We name the membrane we are quoting and we do not invent a 30-year promise the manufacturer has not given you. The POA follows the measured area and the deck condition.",
    "roof area and whether the deck is sound",
    "Can you overlay EPDM on old felt?",
    "Only when the deck is dry and firm and the build-up still has a fall. A soft deck comes off first.",
    "Do you guarantee it for 30 years?",
    "We install to the system we named. We do not write a longer life than that system gives.",
    ["Deck checked before the membrane", "Outlets and trims are part of the roof", "System named in the quote"],
    ["EPDM rubber roof", "flat roof membrane"],
)

add(
    "fascia-and-soffit-replacement",
    "Fascia and soffit replacement renews the boards at the eaves, including the rotten timber hidden behind a later plastic cover.",
    "We do not cap rotten timber with a new uPVC face and leave the wet wood behind it. The old boards come off, the rafter feet are looked at, and ventilation into the loft is kept or put back. Guttering is reset on the new fascia so the fall still reaches the outlet. If the rafter ends are soft, that timber repair is quoted before the plastic goes on. The written POA is per run, with corners and gable ladders called out.",
    "length of eaves and whether the timber behind is rotten",
    "Can you overcap the existing fascias?",
    "Only if the timber behind is sound. Capping wet wood traps the rot.",
    "Will the loft still ventilate?",
    "Yes. We keep an air path at the eaves. Sealed soffits with no vent are how lofts grow mould.",
    ["Rotten timber removed, not capped", "Eaves ventilation kept", "Gutters reset to a fall"],
    ["replace fascia and soffit", "uPVC eaves boards"],
)

add(
    "felt-roofing",
    "Felt roofing is a built-up felt flat roof, still the right covering on many garages, bays and rear extensions when it is specified properly.",
    "We strip back to a deck we trust, renew rotten boards, and lay the felt system in the layers it was designed for, dressed into the outlets and up the abutments. A single patch on a roof that is cracked all over is quoted as a patch and described as a patch. Solar reflection, trims and drip edges are in the scope if the quote lists them. Measured area and deck repairs set the POA.",
    "area, deck condition and how many outlets",
    "Is a tin of felt paint a new roof?",
    "No. A coating over cracked felt is not a felt roof. We say when a coating is a short delay and when the felt has to come off.",
    "Do you leave the old felt on?",
    "Only if it is stuck, dry and the deck underneath is firm. Blistered felt comes off.",
    ["Deck repaired before new felt", "A patch is described as a patch", "Outlets dressed in, not left proud"],
    ["felt flat roof", "garage roof felt"],
)

add(
    "flat-roof-installation",
    "Flat roof installation is a new covering and, where needed, a new deck on a bay, extension or garage, with a fall toward an outlet.",
    "A flat roof without a fall ponds, and ponding is the leak you get in year three. We agree the covering — felt, EPDM or a named system — and we check the joists if the ceiling below shows a sag or a stain. Insulation is added only when the quote says so, with ventilation thought through so we do not trap moisture in a cold roof. Building control applies to many replacements, and we tell you before we strip. The POA is the measured roof, not a photo from the garden.",
    "area, deck and joist condition, and which covering you want",
    "Which flat roof is best?",
    "The one that suits the deck, the falls and the abutments. We name it in the quote rather than selling a brand in the advert.",
    "Can you add insulation?",
    "Yes when it is in the scope. A warm roof and a cold roof are detailed differently so the deck does not sweat.",
    ["Fall toward an outlet", "Joists looked at if the ceiling sags", "Covering named before we strip"],
    ["new flat roof", "extension flat roof"],
)

add(
    "guttering-replacement",
    "Guttering replacement is a new run of gutter and outlets, set to a fall, not a length of gutter screwed to a rotten fascia.",
    "We check the fascia can hold a gutter. If it cannot, the fascia job is quoted first. Falls are set so water reaches the outlet instead of sitting over the joint outside the bedroom. Outlets, stop-ends and unions are in the same system. Downpipes are included only for the drops the quote lists. Cast iron is not swapped for plastic without you agreeing the look. The POA is per run once we have measured.",
    "measured run, fascia condition and how many downpipes",
    "Why does a new gutter still overflow?",
    "Usually the fall is wrong, the outlet is too small, or the downpipe is blocked. We set the fall and check the outlet.",
    "Do you replace cast iron like for like?",
    "We can. Plastic is only used when you have agreed to change the appearance.",
    ["Fascia checked before the gutter goes on", "Falls set to the outlet", "System matched, including unions"],
    ["new house gutters", "replace leaking guttering"],
)

add(
    "landlord-roof-repairs",
    "Landlord roof repairs are the listed leaks and slipped coverings on a rental, done so the agent has a photo and the tenant has a dry ceiling.",
    "We attend to the reported leak, find the path if we can do it safely, and repair that path: tiles, a flashing, a gutter joint. We do not re-roof a terrace because one bedroom has a stain. If the covering is widespread-failed we send a separate POA and make a temporary weather-stop if water is still coming in. Access through a tenanted loft is agreed before we arrive. Invoice notes say what was found, not just 'roof repaired'.",
    "the reported leak, tenant access and whether the covering is locally failed",
    "Can you just replace every tile on the street side?",
    "Only if that is the agreed scope. The repair job is the leak you reported.",
    "What do you send the agent?",
    "Photos and a line that says where the water was getting in, plus anything we refused to patch because the roof is finished.",
    ["Reported leak, not a surprise re-roof", "Temporary stop if water is still live", "Agent note says where the water entered"],
    ["landlord roof leak", "rental property roof repair"],
)

add(
    "roof-inspection",
    "A roof inspection is a look and a written note, not a repair disguised as a survey and not a drone fly-by sold as a specification.",
    "We get onto the roof where it is safe, or we say we could not. The note covers coverings, ridges, flashings, flat roofs, gutters and any daylight in the loft. It separates defects that are letting water in now from items that can wait. We do not price a full re-roof in the same breath as a missing tile. You can ask for a POA against the items you want done. The inspection fee itself is agreed before the visit, still without a made-up repair price on this page.",
    "roof size, safe access and whether you want prices after the note",
    "Do you use a drone instead of getting on the roof?",
    "A drone can show a ridge. It does not feel a spongy deck or see into a back gutter. We say what we actually inspected.",
    "Is an inspection a repair?",
    "No. You get findings. Repairs are a separate written POA.",
    ["What was inspected is written down", "Urgent leaks separated from items that can wait", "Repairs are not bundled into the look"],
    ["roof survey", "check the roof for leaks"],
)

add(
    "roof-leak-repair",
    "A roof leak repair starts at the stain indoors and works back to the covering, flashing or gutter that is actually letting weather in.",
    "Water rarely drips where it enters. We check the ceiling, the loft if it is safe, and the slope above: slipped or cracked tiles, an open lap, a split flashing, a blocked gutter or a flat-roof seam. The repair is the smallest honest fix. We say when the slope is past patching. We do not sell a new roof because a ceiling has a mark, and we do not silicone a ridge and call it done. Photos and a written POA follow the visit.",
    "where the water travels, and whether the covering is locally failed",
    "Can you find a leak that only shows in driving rain?",
    "Often, from the detail that faces that weather. If we cannot reproduce it we say what we checked and what we would open next.",
    "Will sealant on the tiles do?",
    "Sealant is not a tile. Failed coverings are replaced or the lap is remade.",
    ["Traced from the stain back to the entry", "Smallest honest fix", "A new roof is not the default quote"],
    ["find and fix roof leak", "ceiling stain from roof"],
)

add(
    "roof-repairs",
    "Roof repairs are the planned tile, slate, ridge and flashing jobs that are not an emergency and not a full strip.",
    "We count what is broken: cracked tiles, slipped slates, a mortar ridge that has emptied, a length of flashing that has lifted. Each item is on the quote. A ridge that has no mechanical fix on a windy site is quoted as a dry-ridge or a proper bed, not a bucket of mortar slapped back on. Scaffold or a tower is included when the eaves height needs it. The POA lists the items rather than one line that says 'roof'.",
    "the count of failed tiles, ridges and flashings, plus access",
    "Do you rebed a ridge in mortar only?",
    "Mortar-only ridges fail again on exposed roofs. We quote a fixing that suits the site.",
    "Can you repair slates with tiles?",
    "We match the covering. A tile in a slate roof is a leak and an eyesore.",
    ["Each failed item is on the quote", "Ridges fixed for the exposure", "Coverings matched"],
    ["tile and slate repairs", "roof ridge repair"],
)

add(
    "roof-tile-replacement",
    "Roof tile replacement puts matching tiles back where they are cracked, slipped or missing, and refixes them so the next wind does not lift the same course.",
    "We match profile and colour as closely as current stock allows. Interlocking tiles and old plain tiles are not interchangeable. Nails or clips are renewed on the tiles we lift, and we check the battens under a cluster of broken tiles before we cover them again. A handful of tiles is a repair. A slope where the tiles are porous across the face is a recovering conversation, and we will tell you which one you have. The POA is the number of tiles and the access.",
    "tile count, profile match and batten condition",
    "Can you match a tile from the 1980s?",
    "Often a near match. We show the tile before we load the roof with a different profile.",
    "What if the battens are rotten?",
    "We stop covering them. Rotten battens are quoted before new tiles go down.",
    ["Profile matched", "Fixings renewed on tiles we lift", "Rotten battens are not tiled over"],
    ["replace broken roof tiles", "slipped tile repair"],
)

add(
    "chimney-lead-dressing",
    "Chimney lead dressing is the step flashing, soakers and back gutter in lead, beaten and pointed so water sheds off the stack instead of into the loft.",
    "We do not dress lead that has cracked through. Short or split lead is renewed in a code that suits the detail, wedged into a raked joint and pointed. Soakers are checked under the tiles, because a pretty step flashing over missing soakers still leaks. Cement fillets are cut off rather than painted. If the brick joint is too soft to hold a wedge, the brickwork is made good first. Access and the number of faces set the POA.",
    "how many faces of lead, and whether the lead can be saved",
    "Can you paint the lead instead?",
    "Paint does not close a split. Split lead is replaced.",
    "What is a back gutter?",
    "The tray behind a stack that sits down the slope. It blocks and rots quietly. We clear it or renew it when it has failed.",
    ["Split lead is renewed, not painted", "Soakers checked under the tiles", "Cement fillets removed"],
    ["chimney lead flashing", "renew chimney soakers"],
)

add(
    "downpipe-replacement",
    "Downpipe replacement is the vertical pipe from the gutter outlet to the gully, including the shoes, branches and clips that have failed.",
    "A new gutter feeding a blocked or split downpipe still soaks the wall. We replace the drops the quote lists, keep the outlet size honest, and discharge into a gully that actually goes somewhere. We do not leave a shoe pouring onto a path against the brick. Cast iron to the floor on a period front is discussed before it becomes plastic. The POA is per drop, with height and any hoppers noted.",
    "number of drops, height and whether the gully works",
    "The wall is wet under the hopper. Is that the roof?",
    "Often it is the hopper or the pipe below it. We check the drop before anyone strips the tiles.",
    "Do you connect into the drain?",
    "We discharge into the existing gully. A new underground drain is groundworks and is quoted separately if the gully is dead.",
    ["Outlet size kept honest", "Water ends in a gully, not on the brick", "Cast iron discussed before it is swapped"],
    ["replace downpipe", "leaking rainwater pipe"],
)

add(
    "flat-roof-blister-repair",
    "A flat roof blister repair deals with local bubbles in felt where water or vapour has lifted the sheet, without pretending the whole roof is new.",
    "We open the blister, dry what we can, and patch in a way the felt system allows, tied into sound felt around it. A roof that is blistered across the deck is not a blister repair; it is a strip, and we will say that rather than sell twenty patches. If the deck is soft under the blister, the board is replaced under the patch. The written POA states how many blisters and whether the deck is involved.",
    "how many blisters, and whether the deck under them is soft",
    "Should every blister be cut?",
    "A dry, firm blister is sometimes left. A blister with water in it, or over a soft deck, is opened.",
    "Will patches outlast a new roof?",
    "No. Patches buy time on a roof that is otherwise sound. We say when the felt is finished.",
    ["Water-filled blisters opened", "Soft deck replaced under the patch", "A sea of blisters is a strip, not a patch"],
    ["flat roof bubbles", "repair felt blisters"],
)

add(
    "gutter-clear-and-reseal",
    "Gutter clear and reseal is a clean-out of silt and moss plus the joints that leak once the gutter is asked to carry water again.",
    "Clearing a gutter without touching the leaking union just moves the drip. We clear the run, flush to the outlet, and reseal or replace the joints that fail the flush. We also look at whether the fall is so flat that it will silt up again. Downpipes are checked for a free discharge. This is not a new gutter. If the trough is split or the fascia has gone, we quote replacement instead. The POA is per run we can safely reach.",
    "length we can reach, and how many joints actually leak",
    "How often should gutters be cleared?",
    "Trees overhead mean more than once a year. We say what we found in the trough so you can set a sensible return.",
    "Do you reseal every joint?",
    "No. Joints that pass the flush stay. Joints that drip are resealed or replaced.",
    ["Flushed, not just scooped", "Leaking joints resealed", "Split gutters are quoted as replacement"],
    ["clean and seal gutters", "gutter joint leak"],
)

add(
    "landlord-roof-leak-trace",
    "A landlord roof leak trace is the visit that finds why a tenant's ceiling is staining, before anyone orders a new roof from a photograph.",
    "We start inside: the room, the loft, the soil pipe, the tank overflow and the flashing line. Plenty of 'roof leaks' are a failed soil-pipe boot, a condensate pipe or a gutter pouring into the cavity. When it is the covering, we mark the spot and quote the repair. The agent gets a short note that says what it was. The trace is POA as a visit; the repair is a second figure if it was not obvious and safe to do the same day.",
    "whether we can get into the loft and what else might be dripping",
    "Is every ceiling stain a tile?",
    "No. Overflows, boots and gutters imitate roof leaks. We look at those before the tiles come off.",
    "Will you repair it on the same visit?",
    "When the cause is clear and safe. If it needs a scaffold or a new covering, you get a separate POA.",
    ["Inside looked at before the tiles", "Overflows and pipe boots ruled out", "Agent note names the cause"],
    ["trace a landlord roof leak", "tenant ceiling damp roof"],
)

add(
    "lead-flashing-renewal",
    "Lead flashing renewal replaces tired step, apron or abutment lead that has cracked, slipped out of its chase or been pointed over with sand and cement.",
    "We rake the chase, fit lead of a code that suits the length, wedge it, and point the joint. Long runs are joined the way lead needs to move; one long sheet will split. Aprons over tiles are dressed to the contour, not left standing off. We do not point a gap and leave the old lead in the cavity. Abutment length and access set the written POA.",
    "length of abutment and whether the chase will hold a wedge",
    "Is lead better than a sticky flashing tape?",
    "On a proper abutment, yes. Tape is a temporary. We quote lead unless you have asked for a short-term cover.",
    "Do you point with cement?",
    "The pointing holds the wedge. It is not a fillet smeared down the brick to hide missing lead.",
    ["Chase raked and re-wedged", "Lead length joined so it can move", "Tape is not sold as the finished flashing"],
    ["replace lead flashing", "new step flashing"],
)

# ---------------------------------------------------------------------------
# Rendering
# ---------------------------------------------------------------------------

add(
    "external-rendering",
    "External rendering is a new coat system on a wall that has been prepared, with beads, a bellcast and a finish that can breathe or shed as the specification says.",
    "We do not skim a pretty coat over blown render and paint it. Hollow areas come off, the background is keyed, and beads go on corners and stops. The base of the wall stops above the DPC with a bellcast so the render does not bridge damp into the brick. A through-colour finish and a painted sand-cement are different quotes. The written POA is the elevation area after we have sounded the wall.",
    "elevation area and how much of the old render is hollow",
    "Can you render straight over pebbledash?",
    "Only when the dash is stuck fast. Hollow dash comes off, or the new coat comes off with it.",
    "Will render stop damp?",
    "A proper coat sheds rain. It is not a damp-proof course, and it must not bridge the DPC.",
    ["Hollow render removed", "Bellcast kept above the DPC", "Finish named in the quote"],
    ["render the outside of a house", "external wall render"],
)

add(
    "house-rendering-package",
    "A house rendering package is the whole agreed elevations of a house, sequenced so scaffold, preparation and finish are one scope.",
    "We measure each elevation, mark hollow render, and include scaffold where the height needs it. Windows, sills and vents are masked and the coat is stopped correctly around them. One elevation done in a different texture is how a package looks unfinished, so the finish is agreed once. Painting, if the system needs it, is either in the quote or clearly out. The POA is the measured package, not a day rate guessed from the street.",
    "number of elevations, scaffold and how much substrate has failed",
    "Do you render one wall to try it?",
    "We can, and we say that the texture may not be repeatable years later if you add the other walls.",
    "Is scaffold extra?",
    "If the quote includes the upper floors, scaffold or a safe tower is in that quote. We do not add it as a surprise.",
    ["Elevations measured", "One finish agreed for the package", "Scaffold included when the height needs it"],
    ["render whole house", "house render package"],
)

add(
    "landlord-render-repairs",
    "Landlord render repairs are the patches an agent lists: a blown panel, a crack at a window, or a low-level band damaged by splash.",
    "We cut back to sound edges, key the background and patch to the existing texture as closely as a later patch allows. A perfect invisible blend on an old painted wall is rare, and we say so. If the patch is more than about a third of the elevation, we tell the agent a panel or the elevation is the honest scope. Occupied access and colour are agreed before we mix. The POA is the patch count we have seen.",
    "patch count and whether the rest of the elevation is also hollow",
    "Can the tenant stay while you patch?",
    "Yes for small patches. Scaffold and a full elevation are booked around the tenancy.",
    "Will you paint the whole wall so the patch matches?",
    "Only if the quote includes decoration. A render patch and a repaint are different lines.",
    ["Cut back to a sound edge", "Texture matched as closely as a patch allows", "A huge patch is requoted as an elevation"],
    ["landlord render patch", "blown render on a rental"],
)

add(
    "monocouche-render",
    "Monocouche is a through-colour scrape finish applied as one coat to a prepared background, so the colour is in the render rather than a paint film.",
    "We use it where the background is straight and sound enough for a one-coat system. Beads, stops and the bellcast are still required. Scraping is timed to the coat, not to the end of the day, or the colour goes patchy. We name the product and the colour in the quote. It is a poor choice over a wall that is still moving or hollow, and we will not put it there. Area and background set the POA.",
    "area, background straightness and the named colour",
    "Does monocouche need painting?",
    "No. The colour is in the coat. Painting it later is possible but it stops being a through-colour wall.",
    "Can you colour-match an old scrape coat?",
    "We can get close. Weathered monocouche does not match a new bag exactly, and we show a sample.",
    ["Product and colour named", "Scraped to the system, not rushed", "Not applied over hollow or moving walls"],
    ["monocouche scrape render", "through colour one coat"],
)

add(
    "pebbledash-repair",
    "Pebbledash repair patches a dash finish that has fallen away, with aggregate that is as close as we can get to the original stones.",
    "Dash rarely matches perfectly once it has weathered. We cut to a line, apply a butter coat and throw aggregate while it is live. A smear of mortar with a few stones pressed in is not a dash repair. If the dash is hollow across the elevation we say the patch will be the first of many. Low-level dash bridged over the DPC is cut back rather than replaced. The POA is the patch area after we have sounded it.",
    "patch area and how close the aggregate can be matched",
    "Why does dash fall off?",
    "Often the background was not keyed, water got behind, or a hard coat was put on a soft wall. We look at the edge of the failure.",
    "Can you remove all the dash?",
    "Yes, as a separate strip-and-render scope. A repair does not include taking the whole house back to brick.",
    ["Aggregate thrown onto a live coat", "Hollow dash is not patched forever", "DPC bridges are cut back"],
    ["pebbledash patch", "repair fallen dash"],
)

add(
    "render-crack-repair",
    "Render crack repair fills and reinforces cracks that are in the coat, and it stops when the crack is actually the wall moving.",
    "We open the crack enough to see if the render has debonded either side. A coat crack is cut out, meshed and filled. A crack that runs through the masonry, or that is wider at one end, is not a render job; it is masonry or a structural question, and we say that. Painting the crack with masonry paint is not the repair. The written POA is the length of crack that is actually in the coat.",
    "crack length and whether it is in the coat or the wall",
    "Will mesh stop the crack coming back?",
    "It holds a coat crack. It does not stop a wall that is still moving.",
    "Should I just paint it?",
    "Paint hides it until the next wet week. We cut the coat crack out.",
    ["Coat cracks opened, not painted over", "Movement cracks are not meshed and forgotten", "Debonded render either side is cut back"],
    ["crack in render", "render crack mesh"],
)

add(
    "render-repairs",
    "Render repairs are the local hollows, blows and impact scars in a coat that is otherwise still sound.",
    "We sound the wall, mark what is hollow, and cut that back to a stuck edge. The patch is built out to the same plane and finished to the existing texture. We do not trowel a skim over the whole elevation to hide one patch unless that skim is the quote. Corners get a bead if the old bead has rusted through. The POA lists the panels, not a vague 'render repairs' line with no area.",
    "the area that sounds hollow",
    "How do you find hollow render?",
    "By sounding it. A tap tells you where the coat has left the wall before it falls off.",
    "Will the patch be the same colour?",
    "After weathering, closer. On day one a new patch usually reads lighter, and we tell you that.",
    ["Hollow areas sounded and marked", "Cut back to a stuck edge", "Plane and texture matched"],
    ["blown render repair", "patch external render"],
)

add(
    "silicone-render-system",
    "A silicone render system is a thin, flexible, through-colour finish on a prepared base coat, used where the wall wants a modern coat that sheds water.",
    "It is only as good as the base under it. We put it on a specified base, with beads and a stop above the DPC, not as a paint-on miracle over cracked sand-cement. Insulation boards under silicone are a different system and are quoted as such. We name the manufacturer and the colour. The POA is the area of prepared wall, including how much old coat has to come off first.",
    "area, how much old render comes off, and whether insulation is involved",
    "Is silicone render waterproof?",
    "It sheds rain. It is not a tank. Cracks in the wall behind still need dealing with.",
    "Can it go over brick?",
    "Yes, on a base coat the system allows. Bare brick with no preparation is not the system.",
    ["Manufacturer and colour named", "Base coat first", "Insulation underneath is a separate system"],
    ["silicone render", "thin coat silicone wall"],
)

add(
    "through-colour-render",
    "Through-colour render carries the colour in the material, so a scuff does not show bare grey the way a painted coat does.",
    "We agree the colour from a sample, not from a screen photo. The background has to be flat enough that a thin colour coat does not shadow every bump. Day joints are planned so a long elevation is not a patchwork of dry edges. We do not add masonry paint 'to even it up' unless you ask, because that throws away the point of through-colour. Area and sample colour set the POA.",
    "area and the sample colour you have agreed",
    "Will it match the brochure?",
    "Brochures are printed. We use a sample on your wall so the colour is the one you signed.",
    "Can you paint it later?",
    "You can, but you then have a painted wall. We leave it unpainted unless the quote says otherwise.",
    ["Colour agreed from a sample", "Day joints planned", "Not painted over unless you ask"],
    ["through colour render", "coloured render no paint"],
)

add(
    "weatherproof-coatings",
    "Weatherproof coatings are breathable or elastomeric paints and thin coatings on a render or brick face that is already sound.",
    "A coating is not a new render. We clean, treat growth, and fill coat-deep cracks the product allows. We do not coat a hollow render, a face that is dusty and unbound, or a wall that is wet inside. The product is named, including whether it is breathable, because a film over a damp solid wall makes the damp worse. The POA is the area we are allowed to coat after the preparation look.",
    "area, how much preparation, and whether the wall is dry enough",
    "Will a coating fix blown render?",
    "No. Blown render comes off. Coating it glues the failure on for a season.",
    "Is it the same as silicone render?",
    "No. A coating is a film. Silicone render is a build-up. We say which one is on the quote.",
    ["Sound backgrounds only", "Product named, including breathability", "Hollow render is not coated"],
    ["masonry waterproof coating", "weatherproof wall paint"],
)

add(
    "ashlar-render-detail",
    "Ashlar render detail cuts false stone courses into a coat so a rendered wall reads as blockwork, which only works if the lines are straight.",
    "We set the course heights out before the coat goes on, and we cut them while the coat will take a clean line. Wobbly lines are not 'character'. The detail is quoted as an extra over the render system, because it is slower than a plain float. We do not cut ashlar into a coat that is already painted and hard unless that coat is being renewed. The POA separates the render area from the ashlar labour.",
    "elevation area and how many courses are cut",
    "Can you add ashlar lines to my existing render?",
    "Only if the coat is being renewed or is still green. Cutting a hard painted coat looks chewed.",
    "Is it real stone?",
    "No. It is a joint cut into render. We do not describe it as stone.",
    ["Courses set out first", "Cut while the coat will take a line", "Priced separately from a plain float"],
    ["ashlar render lines", "fake stone render joints"],
)

add(
    "bellcast-bead-replace",
    "A bellcast bead replacement puts the stop-bead back at the bottom of a render coat so water throws clear and the coat does not bridge the DPC.",
    "Missing or rusted bellcast beads let the coat crack at the bottom and let render or paint run down over the DPC. We cut the failed bead out, fix a new one on the line above the DPC, and make the coat good to it. If the render has already bridged the damp course, we cut that bridge back. This is a detail repair, not a new elevation. The POA is the length of bead and the making good.",
    "length of failed bead and whether render is bridging the DPC",
    "Why does the bottom of the render keep cracking?",
    "Often there is no bellcast, or the bead has rusted. Water sits on the edge and the coat lets go.",
    "Is this a damp treatment?",
    "It stops the coat bridging the DPC. It does not replace a damp survey if the inside is wet.",
    ["New bead set above the DPC", "Bridged render cut back", "Priced as a detail, not a new elevation"],
    ["replace bellcast bead", "render drip bead"],
)

add(
    "gable-render-refresh",
    "A gable render refresh is one gable taken back where it has failed, finished to match the rest of the house as closely as the system allows.",
    "Gables take the weather and they fail first. We scaffold that face, sound the coat, and renew what is hollow rather than painting the stains. The verge and the abutment with the roof are stopped properly so water cannot get behind the new coat. If the other elevations are a different texture we say the gable will not be invisible. The written POA is that one face, including access.",
    "gable size, hollow area and scaffold",
    "Can you paint the gable instead?",
    "If the coat is stuck, a coating might be enough, and we will say so. Hollow gable render wants coming off.",
    "Do you do the front at the same time?",
    "Only if you add it. The refresh is the gable on the quote.",
    ["One gable, properly accessed", "Hollow coat removed", "Roof abutment stopped so water cannot get behind"],
    ["re-render a gable", "gable end render"],
)

add(
    "insulation-render-system-advice",
    "Insulation render system advice is a straight conversation about external wall insulation and a render finish, before anyone boards a house and traps moisture.",
    "We look at the wall type, the eaves overhang, the window reveals, the DPC and how the house is ventilated. A solid-wall terrace with short eaves is a different job from a cavity house with room at the verge. We do not promise an EPC band, and we do not claim to be a PAS 2035 retrofit coordinator. If the detailing will not work we say do not do it. Any later install is a separate written POA.",
    "wall type, eaves depth and what you wanted the system to achieve",
    "Will external insulation fix my EPC?",
    "It can help a rating. We do not promise a band. An assessor models that, not a render quote.",
    "Can you just stick boards on?",
    "Not if the eaves, reveals and DPC cannot be detailed. Bad EWI is a damp problem with a pretty face.",
    ["Wall type and eaves checked first", "No promised EPC band", "A bad detail is refused"],
    ["external wall insulation advice", "EWI render"],
)

add(
    "k-render-repair",
    "K-render repair patches a silicone or through-colour K-rend style coat where it has been damaged, using the same product family where we can still get the colour.",
    "These thin coats show a patch more than a heavy sand-cement. We cut to a neat line, prime as the product asks, and finish to the same grain. If the colour code is unknown we trial a sample rather than guess from a fan deck in the van. Large areas of debonded coat are requoted as a panel. The POA is the area of damage we have marked.",
    "area of damage and whether the colour code is known",
    "Can you match K-rend colour?",
    "With the code, closely. Without it, we sample. We do not promise a photographic match on a weathered wall.",
    "Why has it debonded?",
    "Usually the base was wrong, or water got in at a stop. We look at the edge before we patch the middle.",
    ["Same product family where possible", "Colour sampled if the code is lost", "Debonded panels are not skimmed over"],
    ["K rend patch", "silicone render repair"],
)

add(
    "landlord-render-patch",
    "A landlord render patch is one or two made-good areas on a rental, small enough to do without turning it into a house render.",
    "Agents use this for impact damage, a leaked gutter scar, or a hollow the size of a dinner tray. We cut back, patch, and leave it ready for decoration if the wall was painted. We photograph it. If sounding shows the whole bay is hollow, we stop and send a POA for that bay rather than leaving a patch in a coat that is about to fall. Occupied access is confirmed the day before.",
    "the size of the marked damage and whether the bay around it is hollow",
    "Will you match the paint?",
    "We leave the patch ready. Painting the elevation is a decorating line unless the quote includes a touch-in.",
    "What if more falls off while you cut?",
    "We stop at a sound edge. If that edge is the whole bay, you get a revised figure before we carry on.",
    ["Small scope kept small", "Hollow bays requoted", "Photos for the agent"],
    ["small render patch rental", "landlord render making good"],
)

# ---------------------------------------------------------------------------
# Damp
# ---------------------------------------------------------------------------

add(
    "basement-tanking-support",
    "Basement tanking support is the lining of a below-ground wall or floor with a named tanking system, after we know where the water is coming from.",
    "We do not paint a slurry on a wet wall and call a cellar dry. The system — cementitious tanking or a cavity drain membrane — is chosen for the water and written down. Drainage, pumps and finishes are only in the quote if they are listed. A room that is partly below ground needs the detail at the ground line, not just the wet patch. We are honest when a structural waterproofing designer should specify it. The POA follows the area and the system.",
    "area below ground and which tanking system is appropriate",
    "Will tanking stop water if the drain is blocked?",
    "No. If water is standing because a drain has failed, the drain is the job. Tanking over a flood is not a specification.",
    "Do you guarantee a dry cellar?",
    "We install the system we named, to its details. We do not guarantee a cellar we have not designed.",
    ["System named before work starts", "Ground line detailed", "A blocked drain is not tanked over"],
    ["cellar tanking", "basement waterproofing lining"],
)

add(
    "chemical-dpc-injection",
    "Chemical DPC injection is a cream or fluid damp-proof course drilled into a wall, and it is only specified when the moisture pattern is actually rising damp.",
    "Most wet walls we see are condensation, bridging, a leak or a high outside path. We do not inject those. Where a moisture profile supports rising damp, we drill to the product's pattern, in the mortar line, and we say what has to happen next: contaminated plaster often has to come off, or the salts stay in the room. Injection without replaster is half a job, and the quote says so. The POA is the metre run after diagnosis, not a phone price per house.",
    "metre run, wall thickness and whether the plaster is salt-contaminated",
    "Should every damp house be injected?",
    "No. Injection does nothing for condensation or a leaking gutter. Diagnosis comes first.",
    "Do you replaster as well?",
    "If the plaster is contaminated, yes, and it is a separate line. Leaving salt-loaded plaster on the wall brings the damp look back.",
    ["Diagnosis before any drilling", "Not used for condensation or leaks", "Replaster called out when salts are in the coat"],
    ["chemical damp proof course", "DPC injection cream"],
)

add(
    "condensation-control-works",
    "Condensation control is about moisture from how the building is lived in and ventilated, not a chemical injected into the brick.",
    "We look at cold surfaces, blocked vents, a lack of extraction, and furniture tight against outside walls. The works might be an extractor that actually ducts outside, trickle or wall vents put back, or a lining on a cold bridge that has been specified. We do not sell a positive-input fan as a cure-all, and we do not inject a DPC for black mould in a bathroom corner. The written POA lists the measures, and we say what is a habit the occupant has to change.",
    "which rooms are wet, and whether extraction ducts outside",
    "Is black mould rising damp?",
    "Usually it is condensation on a cold surface. We look at ventilation before anyone drills the wall.",
    "Will a new fan fix it?",
    "A fan that dumps into the loft has not fixed it. We duct outside, and we say if the room is still unheated or sealed shut.",
    ["Extract ducts outside", "DPC injection is not the mould treatment", "Occupant habits stated honestly"],
    ["condensation and mould", "stop bedroom condensation"],
)

add(
    "damp-proofing",
    "Damp proofing, as a job, starts by naming the damp: rising, penetrating, condensation or a leak. The treatment follows the name.",
    "We do not arrive with one product for every stain. Outside ground levels, gutters, cavity trays, plumbing leaks and how the house is ventilated are looked at before any specification. The quote says which mechanism we are treating and which we are not. Mixed causes are split into lines so you are not paying for an injection the wall does not need. The figure is a written POA after the look.",
    "which mechanism is actually wetting the wall",
    "Can you quote damp proofing from a photo of mould?",
    "No. A photo cannot tell rising damp from a leak or from condensation. We look.",
    "Do you use one system for everything?",
    "No. The system follows the cause. A single product for every stain is how the stain comes back.",
    ["Cause named in the quote", "Gutters, ground levels and leaks checked first", "Mixed causes split into separate lines"],
    ["damp proofing survey and works", "treat damp walls"],
)

add(
    "damp-survey-support",
    "A damp survey support visit is a written diagnosis of why a wall or floor is wet, with recommended next steps and no pressure to buy a treatment the same day.",
    "We use the building: outside levels, rainwater, plumbing, ventilation and a moisture pattern. Meter readings alone are not a diagnosis, because salts and foil-backed paper fool a meter. The note says what we think it is, what would confirm it, and what we would not do. You can take the note and do nothing. If you want us to do the work, that is a separate POA. We do not claim to be an independent chartered surveyor.",
    "how many rooms are affected and whether we can see outside and in the loft",
    "Do you sell the treatment on the same visit?",
    "We can price it after, in writing. The survey is not a pitch that only ends in an injection.",
    "Will a damp meter prove rising damp?",
    "No. A meter is a clue. The pattern, the salts and the outside of the house decide it.",
    ["Written diagnosis", "Meters are not the whole answer", "Works are a separate POA"],
    ["damp survey", "why is this wall wet"],
)

add(
    "landlord-damp-remedial",
    "Landlord damp remedial is the work after a tenant complaint, scoped so the agent can show what was found and what was done.",
    "We separate a landlord repair (gutter, leak, bridged DPC, failed extractor) from condensation that needs ventilation and how the room is used. The tenant gets a clear visit, not a blame conversation from us. Mould is cleaned only when the cause is also being dealt with; wiping mould and leaving the cold wall is not a remedial. The agent gets photos and a short cause note. Each measure is a written POA.",
    "the complaint, access, and whether the cause is a repair or condensation",
    "Is the landlord always responsible for mould?",
    "Not always. We report the cause. A leaking gutter is a repair. A shut ventilator and drying clothes on a radiator is not an injection.",
    "Will you just wash the mould off?",
    "Washing without the cause is a repeat visit. We only clean as part of a scope that deals with why it grew.",
    ["Cause written for the agent", "Repairs separated from occupant moisture", "Mould wash is not the whole job"],
    ["landlord damp and mould", "tenant damp complaint"],
)

add(
    "penetrating-damp-repairs",
    "Penetrating damp repairs stop rain getting through the wall: failed joints, cracked render, a bridged cavity, a leaking gutter or a detail around an opening.",
    "The stain is on the weather side, worse after rain. We find the path outside and repair that path. Injecting a DPC does not stop rain coming through a crack. Cavity walls that are bridged with rubble or a failed tray are opened locally if that is the route. Inside plaster is renewed only when it has blown, and only after the outside is fixed. The POA names the outside defect.",
    "the outside path the rain is taking",
    "Why is it worse when it rains?",
    "That is the clue it is penetrating, not rising. We look at the weather face first.",
    "Do you inject for rain penetration?",
    "No. Injection is not a raincoat. The joint, the gutter or the tray is the repair.",
    ["Weather face inspected", "DPC injection is not the raincoat", "Inside plaster waits until the outside is fixed"],
    ["rain coming through the wall", "penetrating damp"],
)

add(
    "replaster-after-damp-proofing",
    "Replaster after damp proofing replaces salt-contaminated plaster so the wall can dry without the old coat pulling salts back to the surface.",
    "This is not a skim over the old plaster. Contaminated plaster comes off to the height the specification says, the wall is given the backing the system wants, and a finish goes on that will not break down with residual salts. We do not replaster a wall that is still being wetted by a leak. Drying time is explained: a new coat is not a dry wall on day one. The POA is the metre of wall and the height taken off.",
    "height and length of contaminated plaster",
    "Can you skim over damp plaster?",
    "No. Salts go through the skim. The contaminated coat comes off.",
    "How soon can we paint?",
    "When the new plaster is dry enough for the paint you are using. We say that in the note rather than promising a weekend turnaround.",
    ["Contaminated plaster removed", "Not a skim over salts", "Leak still active means we do not replaster yet"],
    ["replaster after DPC", "salt damaged plaster"],
)

add(
    "rising-damp-treatment",
    "Rising damp treatment is for moisture actually climbing from the ground, shown by the pattern low on the wall, not by a tide mark of mould in a cold corner.",
    "We check for a bridged DPC, a high path, a leaking pipe and condensation before we call it rising. Where the pattern fits, the treatment is a DPC that is continuous — repair of a physical DPC or a chemical injection to a specification — plus the plaster that has to come off. We do not treat one metre and leave a bridge next to it. Neighbouring ground levels are part of the look. The written POA is the wall run we are prepared to call rising damp.",
    "the length of wall that actually shows a rising pattern",
    "Is a tide mark always rising damp?",
    "No. Condensation and salt from an old leak leave marks too. We check the outside and the pipework.",
    "What if the path is too high?",
    "Then the path is the defect. Injection behind a bridged path does not hold.",
    ["Other causes ruled out first", "DPC kept continuous", "High paths are not injected behind"],
    ["rising damp treatment", "damp at the bottom of the wall"],
)

add(
    "tanking-system-installation",
    "Tanking system installation applies a named waterproof lining to a wall or floor that is holding back ground moisture.",
    "We follow the system's preparation: the background has to be sound, the joints taped or detailed as that product says, and the finish compatible with it. A tanking smear over gypsum plaster fails. Floor to wall joints are the usual leak and they are in the scope. We do not tank a wall that is moving. If a cavity drain and a pump are the honest system, we say that instead of forcing a slurry. The POA names the product and the square metres.",
    "square metres and the product's preparation",
    "Can you tank over plasterboard?",
    "Not if the board is gypsum and the system forbids it. The background is made ready first.",
    "Do you install pumps?",
    "Only when a cavity-drain system is what we have quoted. A slurry quote does not secretly include a pump.",
    ["Product preparation followed", "Floor-to-wall joint included", "The wrong system is refused"],
    ["install tanking", "waterproof wall tanking"],
)

add(
    "chimney-breast-damp-advice",
    "Chimney breast damp advice explains a wet or salt-stained breast, which is often rain down an unused flue or hygroscopic salts, not rising damp in the lounge.",
    "We look at the pot, the flashing, the cap and whether the flue is open to rain. An unused flue without a ventilated cap will wet the breast. Salts from old soot stain plaster even after the rain is stopped, and they need the plaster off, not a DPC in the party wall. We write what we found. Any flue cap, flashing or replaster is a separate POA. We do not inject the breast as a reflex.",
    "whether the flue is open, capped or still in use",
    "Should you inject a damp chimney breast?",
    "Almost never. Rain down the flue or salts in the plaster are the usual storey. Injection does not cap a pot.",
    "Do you sweep and cap?",
    "We can quote a cap and a flashing. A sweep, if the flue is live, is arranged as its own item.",
    ["Flue and pot looked at", "Salts are not called rising damp", "Injection is not the reflex"],
    ["damp chimney breast", "salts on chimney plaster"],
)

add(
    "condensation-mould-wash",
    "A condensation mould wash cleans mould growth off a surface as part of a ventilation plan, not as a standalone cure.",
    "We only book a wash when the cause is also on the quote: extraction, a vent put back, or a leak that has been stopped. Biocide on a wall that stays cold and wet is a wipe. We treat the surfaces agreed, protect occupied rooms, and we do not present a wash certificate as proof the house is dry. If the stain is penetrating damp, we say so and we do not wash it into a cure. The POA is the rooms on the list.",
    "which rooms, and whether the cause is on the same scope",
    "Will washing pass a council inspection?",
    "Inspectors look at the cause. A washed wall with no extractor is still a complaint waiting to return.",
    "Is the mould dangerous to clean?",
    "We treat it as contamination: protection, the product, and no dry-brushing spores around a bedroom.",
    ["Cause is on the same quote", "Not sold as a dryness certificate", "The wrong stain is not washed into a cure"],
    ["mould wash down", "clean condensation mould"],
)

add(
    "damp-after-leak-dry-down",
    "Damp after a leak dry-down is the controlled drying and check of a building that got wet from a pipe, a tank or a roof, once the leak itself has stopped.",
    "We confirm the leak is actually stopped. Wet plasterboard that has sagged comes off; plaster that will recover is left and monitored. We do not strip a whole house because a ceiling had a stain, and we do not box wet timber in and walk away. Dehumidifiers are used when the building needs them, not as a hired theatre. The note says what is still wet. The POA is the rooms affected.",
    "how far the water travelled and whether the leak has stopped",
    "Should all the plaster come off after a leak?",
    "No. Gypsum that has collapsed, yes. A stain that is drying can stay. We say which.",
    "Do you find the leak as well?",
    "This job starts after the leak is stopped. Tracing the leak is a separate visit if it is still live.",
    ["Leak confirmed stopped", "Only failed finishes stripped", "Timber is not boxed in wet"],
    ["dry out after a leak", "water damage dry down"],
)

add(
    "dpc-injection-to-solid-wall",
    "DPC injection to a solid wall is a chemical course in a solid brick or stone wall that has no cavity and has a genuine rising-damp pattern.",
    "Solid walls are thicker and the drill pattern has to suit that thickness. We do not use a cavity-wall habit on a nine-inch wall. Lime plaster and a cement tanking coat are not the same finish, and old solid walls usually want a breathable replaster. Outside ground and abutting paths are checked so we are not injecting a wall that is simply buried. The POA is the metre run and the wall thickness after diagnosis.",
    "wall thickness and metre run, after a rising-damp diagnosis",
    "Is a solid wall injected from both sides?",
    "The product's pattern for that thickness decides it. We do not guess a single skin of holes will treat a thick wall.",
    "Can you leave the lime plaster on?",
    "If it is salt-loaded, no. The replaster has to be breathable, not a gypsum skim.",
    ["Pattern suited to a solid wall", "Paths that bury the wall are dealt with first", "Replaster stays breathable"],
    ["DPC injection solid brick wall", "rising damp solid wall"],
)

add(
    "external-ground-level-damp",
    "External ground level damp is the wet caused by a path, a flower bed or a new patio sitting at or above the damp-proof course.",
    "The repair is to drop the level, or to detail a channel so the DPC is clear again, not to inject behind the soil. We look at how much has to come away and whether a drain or a step is involved. Render and plaster that bridge the same line are cut back. Inside drying is pointless until the outside level is honest. Groundworks beyond a simple reduction are quoted as groundworks. The POA follows the length we have measured.",
    "length of bridged DPC and how much the outside level has to drop",
    "Will injection fix a high path?",
    "No. The path is higher than the DPC. The level has to change.",
    "Do you excavate the whole garden?",
    "No. We lower the strip that bridges the course, and we say if that means a step or a channel.",
    ["Level lowered or channelled", "Injection is not the fix", "Bridging render cut back"],
    ["path too high damp", "ground level above DPC"],
)

add(
    "landlord-mould-action-visit",
    "A landlord mould action visit is a booked response to a mould complaint, with findings an agent can put on the file the same week.",
    "We record the rooms, the surfaces, the fans, the vents and any obvious building defect. The note separates a repair (leak, gutter, missing fan) from moisture the household is producing. Recommended actions are listed in order, with a POA only on the items you ask us to do. We do not arrive with a single spray and a disclaimer. Occupied visits are timed so the tenant knows we are coming.",
    "the rooms named in the complaint and whether we can see the fans and the outside",
    "What do you leave with the agent?",
    "A short written note: what we saw, what is a repair, and what depends on heating and ventilation.",
    "Can you do the repairs the same day?",
    "Small items sometimes. Anything that needs parts or drying is a written POA.",
    ["Complaint rooms recorded", "Repairs separated from household moisture", "Same-week note for the file"],
    ["mould inspection landlord", "action visit black mould"],
)

# ---------------------------------------------------------------------------
# Plastering
# ---------------------------------------------------------------------------

add(
    "artex-cover-up-plastering",
    "Artex cover-up plastering hides a textured ceiling or wall under a skim, and it starts with the question of whether that texture should be disturbed at all.",
    "Older textured coatings can contain asbestos. We do not sand, scrape or power-grind them. Where a coating is to be over-skimmed, we only do it when it is well stuck and an asbestos check, if one is needed, says it can stay. Loose texture is not skimmed over; it fails and takes the new coat with it. We overboard a ceiling that is not sound enough to skim. The written POA says skim or overboard, and why.",
    "whether the texture is stuck, and whether it needs an asbestos check before anyone touches it",
    "Will you scrape Artex off?",
    "Not by sanding or grinding. If it has to come off, that is a controlled removal after a check, not a skim job.",
    "Can you skim straight onto it?",
    "Only when it is firmly stuck and it is safe to leave in place. Otherwise we overboard.",
    ["No sanding of textured coatings", "Loose texture is overboarded", "Skim versus board is written in the quote"],
    ["skim over artex", "cover textured ceiling"],
)

add(
    "ceiling-plastering",
    "Ceiling plastering is a skim or a reboard-and-skim of a ceiling that has cracked, sagged or been damaged by a leak.",
    "We check why it failed. A crack along a board joint is a taping and skim job. A sag after a leak is often a board that has to come down. We do not skim a ceiling that moves when you touch it. Joists are looked at from above where the loft is safe, and rotten timber is a carpentry quote before any plaster goes up. The POA is the ceiling area and whether the boards stay.",
    "ceiling area and whether the boards are still firm",
    "Can you skim over a hairline ceiling crack?",
    "A single hairline can be reinforced and skimmed. A map of cracks usually means the boards or the joists, and we say so.",
    "What about the loft insulation?",
    "We put it back if we had to move it. We do not leave a ceiling bare of insulation because we were up there.",
    ["Cause of the crack checked", "Soft boards come down", "Joists looked at before a skim over a sag"],
    ["reskim a ceiling", "plaster ceiling after a leak"],
)

add(
    "commercial-plastering",
    "Commercial plastering is patch and area work in offices, shops and stairs where the finish has to be straight and the site has to reopen.",
    "We agree out-of-hours or a screened bay so dust is not the customer's problem. Metal bead is used on corners that take trolleys. We do not quote a domestic skim rate for a stair that needs protection, a tower and a night shift. Fire-rated boards, if the wall is a fire line, are a dry-lining specification and we do not swap in ordinary board. The POA states the hours and the area.",
    "area, access hours and whether the wall is a fire line",
    "Can you work while the office is open?",
    "Small patches, sometimes. Anything dusty is booked when the floor is empty or screened.",
    "Will you match a feature finish?",
    "We match a plain finish. Specialist textures are sampled before we promise them.",
    ["Hours agreed around the building", "Fire lines are not ordinary board", "Dust and protection are in the quote"],
    ["office plaster repairs", "commercial skim"],
)

add(
    "dot-and-dab-boarding",
    "Dot and dab boarding sticks plasterboard to a masonry wall with dabs of adhesive, and it only belongs on a wall that is dry and flat enough.",
    "We do not dot-and-dab a wet wall, a wall with live salts, or an outside wall that needs a ventilated lining. Dabs are set so the board is plumb and so there is a continuous ribbon at the edges where the system wants one, including at sockets. Services are in place before the boards go on. Skim is a separate line unless the quote says the boards are left ready for a decorator who will skim. The POA is the wall area.",
    "wall area and whether the masonry is dry",
    "Can you dab onto a damp wall?",
    "No. The dabs fail and the damp is trapped behind the board.",
    "When do the sockets go in?",
    "Before or with the boarding, to a marked layout. We do not board over open cables and hope.",
    ["Dry walls only", "Sockets marked before the boards", "Not used as a damp cover-up"],
    ["dot and dab plasterboard", "stick board to brick"],
)

add(
    "full-room-plastering",
    "Full-room plastering is every wall and, if included, the ceiling of one room taken to a finish you can decorate.",
    "We protect floors and routes, set beads on every external corner, and skim to a consistent finish rather than a different trowel mark on each wall. Furniture is out of the room before we start; we do not skim around a sofa. If the boards are new, the skim waits until the joints are taped. The quote lists the room and whether the ceiling is in. The POA is that room after we have seen it.",
    "room size and whether the ceiling is included",
    "How long before we can paint?",
    "When the plaster has dried. We do not tell you to paint a wet skim over a weekend.",
    "Do you move furniture?",
    "No. The room is empty before we arrive. We protect the route out.",
    ["Whole room, one finish", "Beads on external corners", "Drying time stated"],
    ["plaster a whole room", "skim all the walls"],
)

add(
    "landlord-plastering-repairs",
    "Landlord plastering repairs are the patches a void or a tenant actually needs: a hole behind a door, a blown patch, a ceiling scar after a leak.",
    "We patch to a line a decorator can paint, not a proud lump of filler. The void list is priced item by item. If a leak is still marked on the ceiling we ask if it is fixed before we skim. Occupied patches are small and timed. We do not turn a three-patch void into a full reskim unless the walls are that far gone, in which case we say so. Photos go back with the invoice. The figure is POA off the list.",
    "how many patches, and whether any leak is still live",
    "Will the patch flash through the paint?",
    "A proud or under-filled patch will. We leave it flush so the decorator is not filling our work.",
    "Can you do this in a day on a void?",
    "Many lists, yes. Drying still applies before anybody paints.",
    ["Void list priced item by item", "Patches left flush", "Live leaks are not skimmed over"],
    ["landlord plaster patches", "void skim repairs"],
)

add(
    "patch-plastering",
    "Patch plastering makes good a hole, a chase or a blown area and feathers it into the surrounding finish.",
    "The patch is cut back to sound plaster, filled in coats if it is deep, and finished flush. A chase for a cable is plastered, not stuffed with one-coat that cracks along the line. We match the existing texture where there is one, and we say when the old wall is so rough that a flush patch will still read. The POA is the number and size of patches.",
    "number and depth of the patches",
    "Can you patch a chase the same day as the electrician?",
    "We can fill it. Decoration waits until it is dry. We do not promise paint the same afternoon.",
    "What is a blown patch?",
    "Plaster that has left the wall and sounds hollow. It comes off. Filler on top of it falls off.",
    ["Cut back to sound plaster", "Chases filled so they do not crack on the line", "Finished flush for the decorator"],
    ["plaster patch", "make good a hole in plaster"],
)

add(
    "plasterboard-installation",
    "Plasterboard installation is fixing boards to studs, joists or a resilient bar, ready for tape and skim or a specialist finish.",
    "We fix to a layout: staggered joints, screws not nails as a habit on ceilings, and the right board. Moisture board in a bathroom, fire-rated board only where the specification calls for that rating, and ordinary board everywhere else. We do not call a pink board a fire wall. Insulation and services are in before the last side is closed. The POA is the board area and the board type.",
    "area and which board type the room actually needs",
    "Is plasterboard the same as a skim?",
    "No. This job is the boards. Skim is listed if you want a paint finish from us.",
    "Can any board be a fire board?",
    "No. Fire rating is a tested system. We fit the board the specification names.",
    ["Board type matched to the room", "Fire board only when specified", "Services in before the wall is closed"],
    ["fit plasterboard", "board a room"],
)

add(
    "plastering-for-renovation",
    "Plastering for renovation is the finish package on a refurb: rooms that have been chased, boarded and altered, skimmed once the other trades are out of the walls.",
    "We book it after first fix, not before the cables move again. Each room is listed. We flag walls that are out of plumb so you are not surprised when a skim does not make a bowed wall straight. Dot-and-dab on a damp solid wall is refused in favour of a lining that can dry. The POA is the schedule of rooms.",
    "the room schedule and whether first fix is actually finished",
    "Can you skim before the electrician is finished?",
    "We can, and then we patch. It is cheaper to wait until the chases are done.",
    "Will skim straighten a bowed wall?",
    "No. Skim follows the wall. A bowed wall needs a lining or it stays bowed.",
    ["After first fix", "Rooms listed, not a whole-house guess", "Bowed walls are flagged"],
    ["renovation skim", "plaster after a refurb"],
)

add(
    "plastering-services",
    "Plastering services cover the ordinary domestic and light-commercial skim, board and patch work we schedule from Stockport.",
    "Tell us the rooms and send photos of the walls as they are. We say whether it is a patch, a skim or a reboard. We do not sell a full reskim because a photo shows one crack. Materials are the ordinary gypsum systems unless the wall is lime or wet, in which case we change the specification and explain why. The written POA follows that decision.",
    "what the walls are doing now, room by room",
    "Do you only do full rooms?",
    "No. Patches, ceilings and full rooms are all on this service. The quote says which.",
    "Are you a skim-only gang?",
    "We board and we skim. If the board is wrong, skimming it is a waste of your money.",
    ["Patch, skim or reboard decided up front", "Lime and wet walls get a different spec", "Photos beat a guess"],
    ["plasterer North West", "skim and board"],
)

add(
    "re-plaster-after-damp",
    "Re-plaster after damp is the new wall finish once the damp has been diagnosed and the wetting has been stopped.",
    "We take off plaster that is salt-blown or debonded, to the height agreed, and we put back a finish that matches the treatment. Gypsum straight onto a freshly injected wall with salts in it fails. If the damp is still active we will not plaster it pretty. Drying is part of the conversation. The POA is the wall area and the system, written so it matches the damp quote rather than fighting it.",
    "wall area and the damp treatment it has to match",
    "Can you replaster before the damp works?",
    "You can, and you will do it twice. We plaster after the cause is dealt with.",
    "What height do you hack off?",
    "The height the salts have reached, plus the margin the specification asks for. We mark it before we start.",
    ["Salts hacked off, not skimmed", "Finish matched to the damp system", "No new coat on a wall that is still getting wet"],
    ["replaster damp wall", "hack off salt plaster"],
)

add(
    "skim-coat-plastering",
    "A skim coat is the thin finish plaster on board or on a sound backing, trowelled so it is ready for decoration.",
    "Skim is not a levelling coat for a wall that waves by half an inch. We say when you need backing plaster or a board instead. Joints in board are taped before the skim. The finish is two-coat skim to a standard you can paint, not a one-pass wipe. Rooms are priced by area. The POA assumes the backing is sound; if it is not, we stop and say so.",
    "area and whether the backing is sound enough to skim",
    "How thick is a skim?",
    "A few millimetres. It follows the wall. It does not bury a bad background.",
    "Do you tape joints?",
    "Yes. Untaped board joints crack through the skim.",
    ["Backing checked first", "Joints taped", "A skim is not a straightening coat"],
    ["skim coat walls", "finish plaster"],
)

add(
    "smooth-finish-plastering",
    "Smooth-finish plastering takes a rough, textured or patchy wall back to a flat surface a decorator can paint without fighting the texture.",
    "The route is either a skim over a sound keyed background or an overboard where the texture is loose or unsafe to skim. We agree which, especially on old ceilings. The aim is a consistent sheen under emulsion, not a glass laboratory wall. External corners are beaded where they are broken. The POA is the surfaces on the list.",
    "which surfaces, and whether they can be skimmed or need boarding",
    "Will the walls be perfectly flat?",
    "They will be a decorating finish. Old walls that bow still bow. We do not promise a laser-flat Victorian wall.",
    "Do you remove wallpaper first?",
    "Yes if it is on the surfaces we are skimming. Vinyl paper left on is a failure waiting.",
    ["Skim or overboard agreed", "Wallpaper off the surfaces we finish", "A decorating finish, not a false promise of perfect flatness"],
    ["smooth walls over texture", "flat plaster finish"],
)

add(
    "artex-overlay-to-smooth",
    "An Artex overlay to smooth boards over a textured ceiling and skims the new boards, leaving the old coating undisturbed underneath.",
    "This is the usual route when a textured coating should not be sanded. We check the ceiling is fixed well enough to take boards, drop the level by the board thickness, and deal with coving or a new shadow gap honestly. Lights and roses are allowed for. We still do not scrape the texture off as a shortcut. The POA is the ceiling area plus the making good at the edges.",
    "ceiling area, fittings and whether the old ceiling will hold new boards",
    "How much lower will the ceiling be?",
    "About the thickness of the board and skim. We say so before we start, especially under a low landing.",
    "Does overlay remove asbestos?",
    "No. It leaves a stuck coating in place. Removal is a different, controlled job after a test.",
    ["Old coating left undisturbed", "New level explained", "Edges and lights allowed for"],
    ["overboard artex ceiling", "board over textured ceiling"],
)

add(
    "ceiling-crack-repair",
    "Ceiling crack repair reinforces and fills cracks that are in the finish, and it stops being a plaster job when the ceiling is moving.",
    "A straight crack along a board joint is raked, taped and filled. A crack that opens seasonally, or a ceiling that has dropped, is a boarding or a joist conversation. We do not caulk a structural crack and paint it. Hairline map-cracking in old skim is explained: sometimes it is age, and a full skim is the honest finish. The POA is the ceilings we have looked at.",
    "whether the crack is a joint, age, or movement",
    "Will filler alone hold?",
    "On a dead hairline, sometimes. On a board joint, no. It needs tape.",
    "What if the crack comes back?",
    "Then the ceiling is moving. We would rather say that on the first visit than sell filler twice.",
    ["Joints taped", "Movement is not caulked", "A map of cracks may need a skim, not a dab"],
    ["repair ceiling cracks", "crack along plasterboard joint"],
)

add(
    "cornice-repair",
    "Cornice repair pieces-in or stabilises a damaged plaster cornice, coving or the surround of a ceiling rose.",
    "We match the profile as closely as a stock or a run mould allows, and we say when a section is beyond a piecing-in. Ornamental fibrous plaster is not the same as a polystyrene cove, and we do not replace one with the other without you agreeing. Loose lengths are refixed before they fall. Painting is left to the decorator unless the quote includes it. The POA is the metres and the profile.",
    "metres of damage and whether the profile can be matched",
    "Can you match an old pattern?",
    "Simple runs, usually. A one-off fibrous pattern may only be a near match, and we show you.",
    "Do you pull it all down?",
    "Only the lengths that have failed. Sound cornice stays.",
    ["Profile matched or the difference explained", "Loose lengths refixed", "Polystyrene is not slipped in as plaster"],
    ["repair plaster cornice", "damaged coving"],
)

add(
    "lime-plastering-support",
    "Lime plastering support is a breathable lime finish on solid or historic walls where gypsum would trap moisture.",
    "We do not skim gypsum over a sound lime wall to make it 'modern and smooth' if the wall needs to breathe. The background is checked, loose lime is held, and new coats are lime where that is the specification. This is slower than gypsum and it cures rather than drying overnight. We are clear about that programme. Where the wall has been tanked in cement already, we say what is possible. The POA is the area and the number of coats.",
    "area and how many lime coats the background needs",
    "Can you just skim it with multifinish?",
    "On a dry modern board, yes. On a damp solid wall in lime, no. Gypsum fails and holds the damp.",
    "How long does lime take?",
    "Longer than gypsum. We programme the coats. We do not promise it is paint-ready the next morning.",
    ["Breathable finish where the wall needs it", "Gypsum not smeared over lime", "Programme is slower and we say so"],
    ["lime plaster walls", "breathable plaster"],
)

# ---------------------------------------------------------------------------
# Dry lining
# ---------------------------------------------------------------------------

add(
    "acoustic-dry-lining",
    "Acoustic dry lining is a board build-up meant to cut noise, and it only works if the specification is followed rather than a single sheet of ordinary board.",
    "We fit the layers the acoustic specification names: board type, bars or studs, insulation in the void, and sealed perimeters. A gap at the skirting or an unsealed socket undoes the system. We do not promise a decibel figure the manufacturer has not published for that build-up. Party walls and separating floors are sensitive, and we say when a specialist design is required. The POA is the area and the named build-up.",
    "the named acoustic build-up and the area",
    "Will one layer of plasterboard soundproof a wall?",
    "No. Mass, isolation and sealing do. One board on dabs is not a soundproof wall.",
    "Can you guarantee the neighbour disappears?",
    "No. We install the system you specified. We do not invent a decibel promise.",
    ["Build-up named", "Perimeters sealed", "No invented decibel figure"],
    ["soundproof dry lining", "acoustic plasterboard wall"],
)

add(
    "ceiling-dry-lining",
    "Ceiling dry lining boards a ceiling, either direct to joists or on a metal grid, ready for skim or a specified finish.",
    "We check the joists are sound enough to take boards. A ceiling that has sagged after a leak is not boarded over the soft boards. MF grid is used where the joists will not give a flat line or where services have to run below them. Boards are staggered and screwed. Insulation is put back in a loft. The POA states direct-fix or grid.",
    "ceiling area and whether the joists will take a direct board",
    "What is an MF ceiling?",
    "A metal grid hung below the structure, used when the joists are uneven or services need a zone.",
    "Do you skim it?",
    "Skim is included only if the quote says so. Otherwise the boards are left ready.",
    ["Joists checked", "Grid when the line needs it", "Loft insulation put back"],
    ["board a ceiling", "MF plasterboard ceiling"],
)

add(
    "commercial-dry-lining",
    "Commercial dry lining is partitions and linings in offices and light commercial units, set out to a drawing rather than to a conversation in the corridor.",
    "We work to the lines on the drawing, with deflection heads where the slab above moves, and the board the fire or acoustic note asks for. Door openings are framed for the door that is coming, not a domestic lining. We coordinate with the electrician so boxes are in before the second side. Out-of-hours is quoted if the unit is live. The POA follows the metre run on the drawing.",
    "metre run, board specification and whether the unit is occupied",
    "Can you copy the partition from next door?",
    "We can match a build-up we can see. A fire rating still has to be the system on the drawing, not a lookalike.",
    "Do you supply the doors?",
    "Door linings are framed. The door set is included only if the quote lists it.",
    ["Set out from the drawing", "Fire and acoustic boards as specified", "Second side closed after services"],
    ["office dry lining", "commercial partition boarding"],
)

add(
    "dot-and-dab-dry-lining",
    "Dot and dab dry lining is plasterboard stuck back to a masonry wall with adhesive dabs, used on dry internal walls that are straight enough.",
    "External solid walls and any wall with damp get a different lining. Dabs plus a continuous ribbon where the board needs it, plumb, with sockets cut to a layout. We leave a gap at the floor the system wants so the board is not standing in a future spill. Skim is separate unless listed. The POA is the wall area.",
    "wall area and whether the masonry is dry and internal",
    "Why not dab the outside walls?",
    "Because a cold, damp solid wall behind a sealed board grows mould in the void. Those walls are specified differently.",
    "How do you get the boards plumb?",
    "With the dabs, checked as we go. A wavy brick wall is not followed blindly.",
    ["Internal dry walls", "Sockets to a layout", "Outside walls are not dabbed to hide damp"],
    ["dot and dab lining", "plasterboard on dabs"],
)

add(
    "dry-lining-installation",
    "Dry lining installation is the general boarding of walls and ceilings in a house or flat that is ready for it.",
    "We agree board type, ceiling or walls, and whether the finish is skimmed by us. Studs and joists are checked for line. We do not dry-line over live mould and call it a refurbishment. Sequence is after the carcass of the partition and after first-fix marks. The written POA lists the surfaces.",
    "which surfaces and which board",
    "Is dry lining the same as plastering?",
    "Dry lining is the boards. Plastering is the finish on them. The quote says if you are getting both.",
    "Can you line one room?",
    "Yes. One room is a normal job. We do not need the rest of the house.",
    ["Surfaces listed", "Board type agreed", "Mould is not boarded over"],
    ["dry line a room", "install plasterboard lining"],
)

add(
    "fire-rated-boarding",
    "Fire-rated boarding is a tested board system on a wall or ceiling that needs a fire performance, not a thicker sheet of ordinary plasterboard.",
    "We fit the board, the screws, the joints and the openings the test requires. A letterbox, a socket or a gap above the head can destroy the rating, and we flag them. We do not certify a wall we have invented. If you need a stated rating, it comes from the system manufacturer or the designer, and we install that. The POA names the system.",
    "the system named and the area, including openings",
    "Is pink board automatically fire rated?",
    "No. Colour is not a test. The system on the quote is what gets installed.",
    "Will you sign a fire certificate?",
    "We confirm what we installed. We do not invent a fire certificate for a build-up nobody specified.",
    ["System named", "Openings flagged", "No invented fire certificate"],
    ["fire rated plasterboard", "fire line boarding"],
)

add(
    "insulation-backed-dry-lining",
    "Insulation-backed dry lining is a board with insulation laminated to it, fixed to a wall to warm the surface, with the moisture risk thought through.",
    "On a solid outside wall this can trap moisture if the room is sealed and the wall is wet. We look at the wall first. The lining is fixed as the product says, with a vapour control where it is required, and sockets are detailed so they are not a cold hole. We do not promise an EPC band from a lining. The POA is the wall area and the product.",
    "wall area, product thickness and whether the wall is dry",
    "Will this cure damp?",
    "No. It warms a dry wall. A wet wall is diagnosed before it is lined.",
    "Does it count as external insulation?",
    "No. It is an internal lining. Eaves and cavities are a different system.",
    ["Wet walls refused", "Product named", "No promised EPC band"],
    ["insulated plasterboard", "thermal board lining"],
)

add(
    "metal-stud-partition",
    "A metal stud partition is a lightweight framed wall in steel stud and track, boarded both sides, used where timber stud is the wrong spec.",
    "We set out to the drawing, fix track to a structure that can take it, and use a deflection head under a concrete soffit that moves. Stud spacing suits the board and the height. Insulation and services go in before the second side. Door openings are framed for the door width you are actually hanging. The POA is the metre run and the height.",
    "length, height and whether a deflection head is required",
    "Are metal studs stronger than timber?",
    "They are straighter and they suit commercial specs. 'Stronger' depends on the wall. We build the one that is specified.",
    "Can you hide pipes in the stud?",
    "Yes, before the second side goes on. We do not notch a fire stop out without saying so.",
    ["Set out from the drawing", "Deflection head where the soffit moves", "Services in before the second board"],
    ["metal stud wall", "steel stud partition"],
)

add(
    "plasterboard-partition-walls",
    "Plasterboard partition walls divide a room with a stud wall, boarded and usually skimmed, for a bedroom, an office or a landlord layout.",
    "We confirm the wall is not taking structure. A partition under a ceiling is not a beam. Layout, door position and sockets are agreed before the track goes down. Both sides are boarded, joints staggered. If the wall needs to slow sound, we say a single empty stud will disappoint you. The POA is the length and whether a door lining is included.",
    "length, door position and whether any sound performance is expected",
    "Can the wall hold a basin or a TV?",
    "Only with noggins where we know the load. Tell us before the boards go on.",
    "Do you need building control?",
    "Sometimes, if the layout affects escape or a fire line. We say so before we build a bedroom in a flat.",
    ["Non-load-bearing, and we keep it that way", "Door and sockets agreed first", "Sound expectations stated"],
    ["stud wall plasterboard", "partition a room"],
)

add(
    "shaft-wall-boarding-support",
    "Shaft wall boarding support is the lining of a service riser or shaft with the board the fire note requires, working with whoever owns the shaft.",
    "Shafts are tight, often live, and often a fire line. We board what we can reach safely, to the system named, and we do not invent access into a riser full of gas and soil. Coordination with the building manager is part of the job. Incomplete boards around pipes are pointed out, because a gap is not a fire stop. The POA is the area we can actually reach.",
    "access into the shaft and the board system named",
    "Can you board around live services?",
    "We board to the system where it is safe. We do not squeeze a fire board into a riser we cannot work in.",
    "Is the gap around a pipe your fire stopping?",
    "No. A gap is a gap. Fire stopping is a specified seal, quoted as itself.",
    ["System named", "Access agreed with the building", "Gaps are not called fire stops"],
    ["riser shaft boarding", "service shaft lining"],
)

add(
    "board-and-skim-ready-pack",
    "A board-and-skim-ready pack is boarding left with joints taped and beads on, so a skim can happen without another making-good visit.",
    "We fix the boards, tape the joints, and bead the corners that need it. Screws are set below the face. The room is left clean enough to skim. We do not leave untaped joints and call it ready. If the studs are out of line we say so before the skim price is agreed. The POA is the area prepared.",
    "area and how much beading the corners need",
    "Is it ready to paint?",
    "No. It is ready to skim. Paint goes on the skim.",
    "Who skims?",
    "Us, if you add it. The pack itself stops at taped boards and beads.",
    ["Joints taped", "Beads on", "Not described as paint-ready"],
    ["board ready for skim", "taped plasterboard pack"],
)

add(
    "ceiling-mf-grid-boarding",
    "Ceiling MF grid boarding hangs a metal grid and boards it, giving a flat ceiling under uneven joists or under services.",
    "We hang the grid to a line, with hangers into structure that will hold it, not into a lath that has already failed. Service zones are agreed so we are not cutting the grid apart for pipes afterwards. Boards are screwed and staggered. Access hatches are framed if you need one. The POA is the ceiling area and the drop.",
    "area, drop and whether an access hatch is needed",
    "How far down does it come?",
    "The drop you agree. We measure headroom before we hang it, especially on a landing.",
    "Can lights sit in the grid?",
    "Yes if we know the positions. A hole cut later in the wrong place is a board replacement.",
    ["Hangers into sound structure", "Drop agreed against headroom", "Hatches and lights planned"],
    ["MF ceiling grid", "suspended plasterboard ceiling"],
)

add(
    "curved-wall-boarding-support",
    "Curved wall boarding support bends board around a curve that has been framed for it, rather than faceting a wall and calling it round.",
    "The studs have to be close enough for the radius. We use a board that will take the curve, in the thickness the radius allows, and we say when the radius is too tight and the wall should be formed another way. Skim follows the curve; a flat skim technique cracks it. This is slower than a straight wall and the POA says so.",
    "the radius and the length of curve",
    "Can any plasterboard bend?",
    "No. Thickness and score pattern depend on the radius. Too tight and we will not force it.",
    "Will the curve stay fair?",
    "If the studs are fair. We do not board a polygonal stud and skim it into a pretend curve.",
    ["Radius checked before the board", "Studs close enough", "Too-tight curves are refused"],
    ["bend plasterboard", "curved wall lining"],
)

add(
    "dot-and-dab-after-damp",
    "Dot and dab after damp is boarding a wall only once it is dry and the cause has been dealt with, using dabs the system allows on that background.",
    "We will not dab board over a wall that is still wet or salt-laden. If the treatment wants a membrane or a ventilated cavity, dabs are the wrong fixing and we say so. Where dabs are acceptable, the wall is flat and dry, and the boards are set plumb. The POA waits on the damp note, not on a wish to hide the stain this week.",
    "whether the wall is actually dry enough to dab",
    "Can we board it so the tenant does not see the stain?",
    "Hiding a live stain is how the mould moves into the void. We board when the wall is ready.",
    "What if the specification wants a membrane?",
    "Then we follow that. Dabs are not a substitute for a lining system.",
    ["Wall dry first", "The damp specification wins", "Stains are not boarded over live"],
    ["board a wall after damp treatment", "dab after DPC"],
)

add(
    "dry-lining-after-first-fix",
    "Dry lining after first fix closes the walls once cables, pipes and noggins are already in the right places.",
    "We walk the first fix before we close it. A cable that is not in a zone, or a pipe with no route, is photographed and left open rather than boarded into a problem. Boards go on when the other trades have signed their positions. Second-side insulation is in before we close. The POA is the area ready to close, not the area somebody hopes will be ready.",
    "which rooms are actually ready to close",
    "Will you board if the electrician is half done?",
    "We board the walls that are finished. The rest stay open so they are not ripped off next week.",
    "Who marks the sockets?",
    "The positions are agreed and marked before the board covers them.",
    ["First fix checked before it disappears", "Unready walls left open", "Insulation in before the second side"],
    ["board after wiring", "close walls after first fix"],
)

add(
    "dry-lining-around-steel",
    "Dry lining around steel boxes in a beam or column with the board build-up a fire note requires, rather than a single skin to make it look finished.",
    "Steel loses strength in heat. If the steel needs a fire protection, the board, the thickness and the fixings come from that specification. We do not wrap a universal beam in ordinary board and call it protected. Gaps at the wall and the ceiling are part of the detail. If nobody has specified the minutes, we ask before we board. The POA names the build-up.",
    "the steel size and the protection specified",
    "Is one layer of plasterboard enough?",
    "Only if that is the specified protection. Often it is not. We do not guess the minutes.",
    "Do you paint the steel as well?",
    "Intumescent paint is a different system. If that is what the design says, boarding may be the wrong quote.",
    ["Specification before any board", "Ordinary board is not fire protection", "Junctions at wall and ceiling included"],
    ["board around a steel beam", "fire case a universal beam"],
)

add(
    "fire-shaft-boarding",
    "Fire shaft boarding lines a shaft that is part of a fire separation, with the system and the access agreed before the boards are ordered.",
    "We need the rating and the system, the chance to reach the shaft, and a way to work that does not leave the building's escape worse while we do it. Boards, fixings and any pattress for services follow the system. We photograph the closed lining. We do not certificate the whole building from one shaft. The POA is the area we have been given access to.",
    "access and the named fire system",
    "Can you work in an occupied block?",
    "Yes if the manager controls the shaft and the escape. We do not prop fire doors open as a convenience.",
    "Will this sign off the fire risk assessment?",
    "No. We complete the lining we were asked to do. The assessor decides what the FRA says.",
    ["System and access agreed first", "Escape kept usable", "We do not sign off the whole FRA"],
    ["fire shaft lining", "riser fire boarding"],
)

add(
    "insulation-lined-partition",
    "An insulation-lined partition is a stud wall with insulation in the void as part of the build, for warmth or to help with sound.",
    "We agree the stud depth and the insulation product so it actually fills the void without stuffing it so tight it is useless. A vapour control layer goes where the build-up needs one, on the warm side. Both sides are boarded after the insulation is in, not before. This is not a fire rating unless the boards are the fire system. The POA is the wall area and the product.",
    "stud depth and the insulation product",
    "Does insulation in a stud make it fire rated?",
    "No. Fire rating is the board system. Insulation is not a substitute.",
    "Which side does the vapour barrier go?",
    "On the warm side, when the build-up calls for one. We do not put it on both sides and trap moisture.",
    ["Void filled as the product intends", "Vapour control on the warm side", "Not described as a fire rating"],
    ["insulated stud wall", "partition with insulation"],
)

add(
    "landlord-partition-board",
    "Landlord partition boarding closes or patches stud walls in a rental: a hole kicked through, a missing side in a void, or a lining after a leak.",
    "We match the board that is already there unless it was the wrong board. A bathroom side gets a moisture-resistant board. We do not use ordinary board in a wet zone to save a visit. The other side of the wall is checked for services before we screw. Photos and a flush finish a decorator can paint are the standard. The POA is the sheets on the list.",
    "how many sheets and whether the room is a wet zone",
    "Can you patch a hole rather than a whole side?",
    "Yes, if the studs around the hole are sound. A soft stud is replaced first.",
    "Will the tenant be in?",
    "We agree that. Dusty boarding in a bedroom is a timed visit, not a surprise.",
    ["Wet rooms get the right board", "Services checked before we screw", "Patches left flush"],
    ["patch a hole in plasterboard", "landlord boarding"],
)

# ---------------------------------------------------------------------------
# Insulation
# ---------------------------------------------------------------------------

add(
    "cold-bridging-insulation-works",
    "Cold bridging insulation works warm the specific cold spots that grow mould — a concrete lintel, a reveal, a floor edge — rather than insulating a whole house on a hunch.",
    "We find the cold line, check it is not a leak, and line or detail that line with a product that will not trap moisture in the wall. Window reveals are thin; the lining has to still let the window open. We do not promise the mould never returns if the room stays unventilated. The POA is the bridges we have marked.",
    "which details are cold, and whether a leak has been ruled out",
    "Is a mouldy corner always a cold bridge?",
    "Often it is a cold spot plus still air. Sometimes it is a leak. We check before we stick on a lining.",
    "Will the windows still open?",
    "Yes. If a reveal lining would jam the sash or the opener, we say so and change the detail.",
    ["The cold detail is marked", "Leaks ruled out first", "Ventilation still matters"],
    ["cold bridge mould", "insulate a cold lintel"],
)

add(
    "epc-insulation-works",
    "EPC insulation works are the fabric measures an assessor has already pointed at — loft, floor edge, or a lining — installed so the next certificate has something real to model.",
    "We do not issue the EPC as part of this job, and we do not promise a band. We install the measure that was recommended, to a sensible depth or thickness, with ventilation kept at the eaves where a loft is involved. If the recommendation does not suit the building we say so before we fill a loft that has no air path. The POA is the measure, not the certificate.",
    "the measure the assessor named, and whether the building can take it",
    "Will this move me to a C?",
    "An assessor models that. We install the works. We do not sell a band.",
    "Do you lodge the EPC?",
    "Not on this job. An EPC is a separate instruction if you want one from us.",
    ["The recommended measure, not a guess", "No promised band", "Eaves ventilation kept in a loft"],
    ["insulation for an EPC", "loft insulation before a certificate"],
)

add(
    "floor-insulation",
    "Floor insulation is insulation under a suspended timber floor or within a floor build-up, fitted so the joists can still breathe.",
    "We lift boards where we have to, set the insulation so it is supported, and keep air bricks clear. A plastic sheet laid wrong can wet the joists; we do not staple a random membrane in and hope. Concrete floors are a different build-up and are quoted as such. Access and how much floor comes up set the POA.",
    "floor area and how the floor is built",
    "Do you take the whole floor up?",
    "Only the boards we need. If the floor is a floating chipboard with no void, we say it is a different job.",
    "Will it make the room much warmer?",
    "It stops a cold floor. It is not a heating system. We do not invent a temperature rise.",
    ["Air bricks kept clear", "Joists not wrapped in a wet membrane", "Concrete floors quoted separately"],
    ["insulate a timber floor", "underfloor insulation"],
)

add(
    "insulation-upgrade-package",
    "An insulation upgrade package is a defined set of fabric measures — typically loft, hatch, and pipework — priced as one scope for a house or a landlord.",
    "Each measure is listed. We do not throw in cavity-wall insulation we cannot see, and we do not claim a grant. Loft depth is brought toward the level we agreed, hatches are insulated, and tanks and pipes in the loft are lagged if they are on the list. Ventilation at the eaves is protected. The POA is the list after we have been in the loft.",
    "what is on the list and what the loft is like now",
    "Do you do cavity wall insulation?",
    "Not as a bag of beads pumped blind. If a cavity measure is wanted it is specified on its own.",
    "Is there a grant?",
    "We do not advertise a grant on this page. If a scheme applies to you, it is checked outside this quote.",
    ["Measures listed one by one", "Eaves ventilation protected", "No grant claim on the page"],
    ["house insulation package", "loft and pipe insulation"],
)

add(
    "internal-wall-insulation",
    "Internal wall insulation lines the inside of an external wall to warm the room, and it is only specified when the moisture path has been thought about.",
    "Solid walls, cavities and already-damp walls behave differently. We look at thickness, sockets, radiators, skirtings and the DPC. A lining that seals a wet wall makes the wall wetter. We name the product and we do not promise an EPC band or a PAS 2035 role we are not appointed to. The POA is the wall area after that look.",
    "wall area, wall type and whether the wall is dry",
    "Will I lose much room?",
    "You lose the thickness of the system, plus skim. We say the millimetres before we start.",
    "Is this safe on a solid wall?",
    "Sometimes, with the right product and a dry wall. Sometimes it is a damp risk. We say which.",
    ["Wall type checked", "Thickness explained", "No promised EPC band"],
    ["internal wall insulation", "insulate solid walls inside"],
)

add(
    "landlord-insulation-upgrade",
    "A landlord insulation upgrade is the loft and related fabric top-up an agent wants before a let or after an EPC recommendation.",
    "We measure what is already up there. A loft with 50 mm is a different job from one with 250 mm and a boarding deck. Boarded lofts that have crushed the insulation are called out. We keep the eaves path open and we lag the pipes we can see if they are on the list. Access through a tenanted house is agreed. The agent gets a note of the depth left behind. The figure is POA after the loft look.",
    "existing depth, whether the loft is boarded, and tenant access",
    "Is 100 mm enough?",
    "It is more than nothing and less than current usual depths. We say what is there and what we are asked to add.",
    "What if the loft has a bedroom in it?",
    "Then it is a room-in-roof, not a cold loft. We do not bury the slope in a cold-loft quilt.",
    ["Existing depth measured", "Boarded lofts called out", "Note of the depth we leave"],
    ["landlord loft insulation", "top up rental loft"],
)

add(
    "loft-insulation",
    "Loft insulation is a quilt or roll laid in a cold loft, between and over the joists, without blocking the ventilation at the eaves.",
    "We look at the depth already there, the tanks, the cables and the walkways. Cables are not buried under insulation where that would overheat them; we lift them above. The loft hatch is a hole in the insulation unless it is on the quote. Boarding for storage is a separate line because boards crush the quilt. The POA follows the loft area and the depth we are adding.",
    "loft area, current depth and whether you want to store boards up there",
    "Can I board the loft for storage afterwards?",
    "Yes, on legs that keep the insulation deep. Boards laid on the joists squash it flat.",
    "Do you move the tanks?",
    "We work around them and lag them if that is listed. Replacing a tank is not this job.",
    ["Eaves left open", "Cables lifted clear", "Storage boarding is not allowed to crush the quilt"],
    ["lay loft insulation", "top up loft roll"],
)

add(
    "pipe-insulation",
    "Pipe insulation lags cold and heating pipes so they do not drip condensate or lose heat, especially in a loft, a garage or a void.",
    "We fit lagging of a thickness that actually covers the pipe, including bends and valves as far as the product allows. A spiral of old foam that has split is replaced, not taped. We identify condensate pipes that should not be lagged into a problem, and we do not cover a weeping joint with insulation to hide it. The POA is the metre run we can reach.",
    "metre run and whether the pipes are in a loft, a void or a cupboard",
    "Why do loft pipes drip in summer?",
    "Cold pipes sweat. Lagging stops the warm loft air dropping water on the ceiling.",
    "Will you lag a leaking joint?",
    "No. The joint is repaired first. Insulation is not a bandage.",
    ["Bends included", "Split foam replaced", "Leaks are not lagged over"],
    ["lag pipes", "pipe insulation loft"],
)

add(
    "room-in-roof-insulation",
    "Room-in-roof insulation is the lining of a slope and a dwarf wall in a loft room, detailed so the roof can still breathe.",
    "A cold-loft quilt stuffed into a slope is the wrong build-up. We look at ventilation from eaves to ridge, the rafter depth, and whether a vapour control is needed on the warm side. Headroom is measured before we add thickness. Dormer cheeks are included only if they are on the quote. We do not promise a warm room in January from insulation alone. The POA is the slope area.",
    "slope area, rafter depth and how the roof is ventilated",
    "Can you insulate between the rafters only?",
    "Sometimes. If the rafter is too shallow, some insulation has to go under it, and you lose headroom. We measure first.",
    "Will this cause condensation in the roof?",
    "It can, if the ventilation or the vapour control is wrong. That is why we look before we fill the slope.",
    ["Not a cold-loft quilt in a slope", "Ventilation path kept", "Headroom measured"],
    ["insulate a loft room", "room in roof insulation"],
)

add(
    "sound-insulation-boarding",
    "Sound insulation boarding adds mass or a resilient layer to a wall or ceiling to reduce noise, following a build-up rather than a single extra sheet.",
    "We agree what you are trying to quieten: airborne voices or impact from above. They are different. The boards, bars and insulation are the named build-up, with sealed edges. We do not promise you will not hear the neighbour. Sockets and downlighters are detailed because a hole is a leak for sound. The POA is the area and the build-up.",
    "the noise you are trying to reduce and the build-up",
    "Will acoustic board stop footfall from the flat above?",
    "Footfall is impact. A board on your ceiling may help and may not. We do not promise silence.",
    "Do you lift their floor?",
    "Not unless we have access and it is on the quote. Most of these jobs are on your side.",
    ["Airborne and impact distinguished", "Edges sealed", "No promise of silence"],
    ["soundproof a ceiling", "acoustic boarding"],
)

add(
    "cold-bridge-reveal-insulation",
    "Cold-bridge reveal insulation lines the window or door reveal that is colder than the wall around it and grows mould in the corner of the frame.",
    "Reveals are narrow. We use a thin product that still lets the window open and the curtain sit. The lining stops short of jamming the hinge. If the mould is from a failed seal letting rain in, insulation is the wrong first step and we say so. The POA is the number of reveals.",
    "how many reveals, and whether the frame is leaking rain",
    "Will the window still close?",
    "Yes. If the reveal is too tight for a lining we say so instead of forcing one.",
    "Is this the same as new windows?",
    "No. The frames stay. We are lining the cold masonry beside them.",
    ["Window still operates", "Rain leaks are not insulated over", "Thin product for a narrow reveal"],
    ["insulate window reveals", "mould around window lining"],
)

add(
    "eaves-ventilation-with-insulation",
    "Eaves ventilation with insulation puts the air path back at the eaves when a loft has been filled so far that the quilt blocks the vent.",
    "We fit rafter trays or an equivalent spacer, pull the quilt back off the soffit, and leave a clear route from outside air into the loft. Blocking that route is how roof timbers sweat. We do not rip out all the insulation; we reset it. Soffit vents are cleared if they are painted shut. The POA is the eaves length we can reach.",
    "eaves length and how badly the quilt has blocked the vent",
    "Why does the loft smell damp after new insulation?",
    "Often the eaves were buried. The insulation is doing its job and the air path is not.",
    "Do you remove the insulation?",
    "No. We reset it behind a tray so the thickness stays and the air can pass.",
    ["Rafter trays or equivalent", "Quilt pulled off the soffit", "Insulation kept, ventilation restored"],
    ["eaves vents blocked by insulation", "loft ventilation trays"],
)

add(
    "insulation-after-loft-boarding",
    "Insulation after loft boarding lifts storage boards that have crushed the quilt, and reinstates depth underneath on legs.",
    "Boards screwed to the joists flatten insulation to almost nothing. We lift them, add depth between and over the joists, and refit a boarding zone on stilts where you still need to store. The whole loft does not need to be a floor. Cables and pipes are kept accessible. The POA is the boarded area plus the loft depth you want back.",
    "how much is boarded and how deep the crushed quilt is",
    "Can you leave the boards down?",
    "Only if they are raised. Boards on the joists and a proper loft depth cannot occupy the same space.",
    "Will I still be able to walk in the loft?",
    "On the boarded zone we put back. The rest is insulation, not a floor.",
    ["Crushed quilt reinstated", "Storage kept on legs", "Cables left reachable"],
    ["loft boards crushing insulation", "raise loft boarding"],
)

add(
    "insulation-for-epc-band-lift-support",
    "Insulation for an EPC band-lift is support for measures an assessor thinks could move a rating, installed without us pretending to be the assessor.",
    "We read the recommendation, look at whether the building can take it, and quote the measure. We do not alter a lodged certificate, and we do not guarantee the points. If the recommendation is wrong for the wall — a wet solid wall, a loft with no ventilation — we say do not do it. Any new EPC is a separate instruction. The POA is the measure we are prepared to install.",
    "the recommendation and whether the building can actually take it",
    "Can you guarantee the new band?",
    "No. The model belongs to the assessor. We install fabric, we do not lodge a score.",
    "What if the recommendation looks wrong?",
    "We say so. A measure that wets the building is not a favour to the rating.",
    ["Recommendation read, not invented", "Unsafe measures refused", "No guaranteed band"],
    ["EPC band insulation", "raise an EPC with loft insulation"],
)

add(
    "internal-wall-insulated-lining",
    "An internal wall insulated lining is the installed lining system on an external wall: boards, fixings and the finish build-up named in the quote.",
    "This is the works, not the advice visit. The product, thickness and treatment of sockets, radiators and skirtings are on the quote. We protect the room and sequence decoration after the lining is dry. A wall that failed the moisture look does not get lined under a different name. The POA is the square metres on the drawing or the marked walls.",
    "square metres and the product already chosen",
    "Do you move the radiators?",
    "If they are on the wall we are lining, yes, and that is listed. We do not leave a radiator buried in the lining.",
    "Who decorates?",
    "We leave a finish ready for paint unless decoration is on the quote.",
    ["Product and thickness on the quote", "Radiators and sockets planned", "Wet walls are not lined under another name"],
    ["fit internal wall lining", "insulated lining installation"],
)

add(
    "landlord-loft-top-up",
    "A landlord loft top-up adds insulation where a rental loft is below the depth you have asked for, with photos for the file.",
    "We record the depth on arrival and the depth we leave, in millimetres, not 'topped up'. Hatches, tanks and downlighters are noted. Downlighters that are not rated to be covered are left clear. Eaves vents stay open. Tenant lofts with stored belongings are only done if the loft is clear or you have agreed we move what we can. The POA is the loft after that look.",
    "current depth, downlighters and whether the loft is clear",
    "What depth do you take it to?",
    "The depth you specify. We tell you what is there now so the top-up is a number.",
    "Can you do it with the tenant's boxes up there?",
    "Not properly. The loft needs to be clear enough to lay the quilt.",
    ["Depth recorded in millimetres", "Uncovered downlighters left clear", "Eaves left open"],
    ["top up landlord loft", "rental loft insulation depth"],
)

add(
    "loft-hatch-insulation",
    "Loft hatch insulation treats the hatch as the hole it is: a lid that is insulated and draught-sealed, not a bare board into the loft.",
    "We insulate the lid with a thickness that still lets it close, fit a seal, and check the frame is not leaking loft air into the landing. A hatch in a tight airing cupboard is measured before we add a quilt that stops it shutting. The loft around the opening is pulled back so it does not jam the lid. The POA is the hatch, and any surround we have to make good.",
    "whether the lid can still close once it is insulated",
    "Is the hatch worth doing if the loft is already deep?",
    "Yes. A bare hatch is a cold, draughty square metre on the landing.",
    "Do you replace the hatch?",
    "We insulate the one you have if it is sound. A broken hatch is a joinery replacement, quoted separately.",
    ["Lid still closes", "Draught seal fitted", "A broken hatch is not disguised with quilt"],
    ["insulate loft hatch", "draughty loft hatch"],
)

# ---------------------------------------------------------------------------
# Windows and doors
# ---------------------------------------------------------------------------

add(
    "bi-fold-door-support",
    "Bi-fold door support is the opening, the lintel question and the making-good around a folding door set, not a promise that any wall can take a four-metre opening.",
    "The structural opening is designed before we talk about the doors. We prepare the aperture, the sill and the reveals to the frame that has been chosen, and we make the internal finishes good. We do not size the steel. Trickle ventilation and the threshold drip are part of a proper install and we flag them. The POA separates building work from the door supply if the doors are yours.",
    "opening width and whether a lintel design already exists",
    "Can you knock through and fit them in a week?",
    "Only with a specified lintel and the doors on site. The opening is the slow part.",
    "Do you supply the doors?",
    "When the quote says so. If you have bought them, we fit to those frames and we check them before we cut the wall.",
    ["Lintel specified first", "Threshold and ventilation flagged", "Supply and building work split on the quote"],
    ["bi-fold door opening", "prepare for folding doors"],
)

add(
    "composite-door-installation",
    "Composite door installation is a new composite door and frame into an existing opening that has been checked for square, sill and brickwork.",
    "We measure the opening, not the old door. Out-of-square openings are made true or the new frame is packed honestly and the difference trimmed. The threshold is set so water falls away and the door still clears the floor inside. Locks are handed the way you need. We do not cut a structural mullion out to force a wider door. The POA is the door specified plus the making good we can see.",
    "the opening and the door specification",
    "Will it match my windows?",
    "Colour can be close. A composite slab will not look like a 30-year-old window profile, and we say that.",
    "Do you take the old frame out?",
    "Yes. We check what comes away with it, including a rotten sill, before the new frame goes in.",
    ["Opening measured properly", "Threshold set to shed water", "Rotten sills found before the new door is trapped"],
    ["fit a composite door", "new front door composite"],
)

add(
    "door-lock-upgrade",
    "A door lock upgrade swaps the lock, cylinder or multipoint furniture for a better one, without destroying a fire door or a composite edge.",
    "We match the lock to the door. A fire door keeps a lock the door can live with, not a random mortice that cuts the door edge away. Multipoint locks on uPVC are identified before parts are ordered. We do not advertise ourselves as a 24-hour locksmith, and we do not force entry as a service. You get a working lock and the keys the quote says. The POA is the lock type after we have seen the door.",
    "the door type and the lock that is in it now",
    "Can you open a door the tenant has locked?",
    "Not as a lockout service. We change locks we are asked to change by someone who is allowed to instruct us.",
    "Will a new cylinder fit a fire door?",
    "Often, if the lock case stays. We do not rout a certified door to take a different case.",
    ["Lock matched to the door", "Fire doors are not cut about", "Not a lockout service"],
    ["upgrade a door lock", "replace a multipoint lock"],
)

add(
    "fire-door-set-supply",
    "Fire door set supply is a door, frame and ironmongery bought as a set that belongs together, not a door slab hung in the old frame.",
    "Fire performance sits in the set: leaf, frame, seals, closer and glazing. We supply what the schedule names and we say when the opening will not take it without joinery. We do not put a fire-door sticker on a door we have planed to fit. Installation is included only if the quote says install. Certification papers that come with the set are handed over; we do not invent a certificate. The POA is the set specified.",
    "the rating and the opening it has to fit",
    "Can you cut the door down to suit?",
    "Only within the manufacturer's trim allowance. Beyond that the set is wrong and we reorder, we do not plane the rating off.",
    "Is the old frame all right?",
    "Usually not. A fire door in a softwood lining with no seals is not the set.",
    ["Leaf, frame and ironmongery as a set", "Trim limits respected", "No invented certificate"],
    ["supply a fire door set", "FD30 door and frame"],
)

add(
    "french-door-installation",
    "French door installation fits a pair of doors into an opening that is square, weathered and strong enough, with a threshold that keeps rain out.",
    "Pairs show every error in the opening. We check the lintel, the sill and the brick reveals before the doors come off the van. The meeting stile is adjusted so it closes without a gap you can see daylight through. Trickle vents, if the doors need them, are not thrown away. Making good plaster and the outside mastic are in the quote if we have seen them. The POA is the pair and the opening.",
    "the opening and the door pair specified",
    "Do you need a new lintel?",
    "If the opening is new or the old lintel has failed, yes, and that is designed before we fit doors.",
    "Will they be weatherproof?",
    "A proper threshold and seals, yes. A pair dumped on an out-of-level sill will leak, so we do not do that.",
    ["Opening checked before the doors come off", "Meeting stile adjusted", "Threshold set for rain"],
    ["fit French doors", "French door replacement"],
)

add(
    "landlord-window-replacement",
    "Landlord window replacement swaps failed windows on a rental for new frames, timed around the tenancy and recorded for the agent.",
    "We list each window. Failed sealed units, rotten sashes and windows that will not lock are the usual reasons. The replacement is a window of the style you have agreed, installed, masticed and left operable. We do not replace a whole elevation because one unit has misted, unless that is the instruction. Occupied rooms are one window at a time where we can. The POA is the schedule of windows.",
    "how many windows and whether the property is occupied",
    "Can the tenant stay?",
    "Yes for a planned swap. We agree which rooms and when, so nobody comes home to an open hole.",
    "Do you match the rest of the street?",
    "We match the style you specify. A conservation front may need something the estate cannot have in white plastic, and we say so.",
    ["Windows listed one by one", "Occupied swaps planned", "One misted unit is not an excuse to replace the house"],
    ["replace landlord windows", "rental window replacement"],
)

add(
    "patio-door-installation",
    "Patio door installation fits a sliding or in-line patio door, with the sill level and the reveals packed so the doors run.",
    "Sliders fail when the sill is twisted. We set the sill, check the operation before we foam and mastic, and leave the doors locking. Drainage slots in the sill are left open, not buried in render. Internal plaster making-good is listed if the old frame damages it. A new opening through the wall is not hidden inside a 'supply and fit' line. The POA is the door and the opening we have measured.",
    "the measured opening and whether the wall opening already exists",
    "Why do patio doors stick?",
    "Usually the sill has settled or was never level. We set it before we glaze the pressure in.",
    "Do you brick up the sides?",
    "Making good the reveals, yes. A structural change to the opening is a separate brickwork quote.",
    ["Sill set level", "Drainage slots kept open", "Structural openings are not hidden in the fit"],
    ["fit patio doors", "sliding door installation"],
)

add(
    "secondary-glazing-support",
    "Secondary glazing support fits an inner glazing panel behind an existing window, used where the outer window has to stay.",
    "We measure to the staff bead or the reveal that will actually hold the panel. Listed and conservation windows are not ripped out. The secondary panel is there for draughts and noise, and we do not describe it as a replacement window or a fire escape. Hinges and catches are shown so the occupant can still escape and still clean. The POA is the number of panels.",
    "how many openings and whether the outer window has to remain",
    "Is secondary glazing a new window?",
    "No. The outer window stays. This is an inner panel.",
    "Will it stop all the road noise?",
    "It reduces it. We do not promise silence, especially with trickle vents open.",
    ["Outer window stays", "Escape and cleaning still possible", "Not sold as a replacement window"],
    ["secondary glazing", "inner glazing panel"],
)

add(
    "upvc-window-fitting",
    "uPVC window fitting is a new plastic window into a prepared opening, squared, packed, foamed and sealed, and left opening the way it should.",
    "We do not face-fix a window over a rotten timber frame and hope. The old frame comes out, the opening is checked, and packers go at the fixings. Drainage and trickle vents are kept. Sills that are rotten are replaced rather than buried. We clear our own debris. The POA is per window after measuring.",
    "each opening, once measured",
    "Can you fit over the old frame?",
    "Only when the old frame is sound and the opening still works. A rotten frame comes out.",
    "Do you plaster inside?",
    "Making good the reveal is included if we have seen that the old frame will take the plaster with it.",
    ["Old rotten frames removed", "Packed and squared", "Vents and drainage kept"],
    ["fit uPVC windows", "replace plastic windows"],
)

add(
    "window-cill-replacement",
    "Window cill replacement renews a rotten or cracked cill — timber, concrete or plastic — so water is thrown clear of the wall under the window.",
    "A failed cill lets water into the reveal and looks like 'damp'. We cut the failed cill out, check the frame bottom, and bed a new one with a fall and a drip. If the window frame itself has gone at the bottom, a new cill alone will not hold and we say so. Internal plaster that has blown is pointed out. The POA is the number of cills and the material.",
    "how many cills and whether the frame bottom is sound",
    "Is a cracked cill causing the damp inside?",
    "Often yes, under that window. We check the frame and the joint before we call it rising damp.",
    "Can you replace a concrete cill?",
    "Yes. It is heavier and the reveal needs making good. We price the material we are actually fitting.",
    ["Fall and drip on the new cill", "Frame bottom checked", "Not mistaken for rising damp"],
    ["replace window cill", "rotten window sill"],
)

add(
    "window-replacement",
    "Window replacement is a new window in place of one that has failed, measured and specified before it is ordered.",
    "We note the style, the opening lights, the glass and any trickle vent. Safety glass is used where the rules put it, and we say where. The installation takes the old window out, fits the new one square, and seals it. One window is a fair job; we do not inflate it into a full-house quote. The written POA is the window we have measured.",
    "style, size and glass, measured on site",
    "How long from measure to fit?",
    "Long enough for the frame to be made. We do not promise a same-week manufacturing time.",
    "Do you do one window?",
    "Yes. One measured window is a complete job.",
    ["Measured before it is ordered", "Safety glass where it is required", "One window is an acceptable job"],
    ["replace a window", "new window supply and fit"],
)

add(
    "composite-door-threshold",
    "A composite door threshold repair or replacement stops the weather coming under the door, and it deals with a sill that has dropped or a seal that has gone.",
    "We look at the threshold, the door bottom and the outside paving. A new seal on a twisted sill still leaks. If the slab outside is higher than the sill, water will always sit there and the paving has to change. We do not pack a door up so far that it will not latch. The POA is the threshold work after we have seen which of those it is.",
    "whether the sill, the seal or the paving is the leak",
    "Why does the new door leak at the floor?",
    "Usually the sill is out of level, the seal has rolled, or the path is higher than the threshold.",
    "Can you just replace the rubber?",
    "Yes, when the sill is straight. We say so rather than selling a whole door.",
    ["Sill, seal and paving separated", "Door still latches", "A rubber is not sold if the sill has failed"],
    ["composite door leaking threshold", "replace door sill"],
)

add(
    "door-closer-power-adjust",
    "Door closer power adjustment sets a closer so the door actually latches, without winding it so tight that people cannot open it.",
    "We adjust speed and power on a closer that is the right size for the door. A closer that is too small, or a door that is binding, will not be fixed by another turn of the screw. Fire doors are adjusted so they close fully onto the latch, not left on a catch. We do not remove a closer from a fire door. The POA is the number of closers.",
    "how many closers, and whether the door is binding",
    "Why does the door slam?",
    "The closing speed is high, or the latch is catching late. We adjust. If the closer is the wrong power, we say replace it.",
    "Can you take the closer off?",
    "Not on a fire door. The closer is part of how that door works.",
    ["Adjusted, not just wound tighter", "Binding doors identified", "Fire-door closers stay on"],
    ["adjust door closer", "door closer too strong"],
)

add(
    "external-door-weather-seal",
    "An external door weather seal replacement renews the draught and rain seals on a door that otherwise still fits.",
    "We match the seal carrier. A strip of foam stuck to the frame is a temporary. Brush seals, blade seals and threshold seals are different and we fit the one the door was made for. If daylight shows because the door has dropped, a seal is not the repair and we say the door needs hanging. The POA is the door and the seal type.",
    "the seal the door was built to take",
    "Will a new seal stop a dropped door?",
    "No. If the door has dropped out of the frame, it needs hanging or a new hinge, not a fatter seal.",
    "Does this include the letterbox?",
    "Only if the quote says the letterbox seal is in. A draught under the door and a draught through the letterbox are different.",
    ["Seal matched to the door", "Dropped doors are not 'fixed' with foam", "Temporary foam is not the finished job"],
    ["draught seal front door", "replace door weather strip"],
)

add(
    "failed-double-glazed-unit",
    "A failed double glazed unit is a misted or blown sealed unit, replaced in the existing sash where the sash is sound.",
    "We measure the unit, not the window, and we order glass with the right specification: toughened where it needs to be, warm-edge if that was agreed. The beading comes off, the unit goes in on the right packers, and the beads go back. A rotten sash is not re-glazed and left to fall apart. uPVC and timber are different jobs and the quote says which. The POA is the unit after measuring.",
    "unit size and whether the sash will accept a new one",
    "Why has the glass misted?",
    "The seal between the panes has failed. Cleaning will not clear it. The unit is replaced.",
    "Do you replace the whole window?",
    "Not if the frame is sound. The job is the unit.",
    ["Unit measured from the sash", "Toughened where required", "Rotten sashes are not re-glazed and ignored"],
    ["misted double glazing", "replace a sealed unit"],
)

add(
    "fire-door-glazing-bead",
    "Fire door glazing bead replacement puts back the glazed aperture with beads and glass the door system allows.",
    "Ordinary glazing beads and ordinary glass do not belong in a fire door. We replace like for like with the glass and beads the door requires, or we say the aperture has been altered and the door is compromised. We do not open up a vision panel that was not there. If the door has already been cut about, the honest answer may be a new leaf. The POA is the aperture we have seen.",
    "whether the existing glass and beads are still the right system",
    "Can you put normal glass in to save money?",
    "No. The glass in a fire door is part of the door. Ordinary glass is refused.",
    "What if the hole has been enlarged?",
    "Then it may no longer be that door. We say if a new leaf is the honest repair.",
    ["Glass and beads to the door system", "Ordinary glass refused", "Cut-about doors are not restickered"],
    ["fire door glass bead", "replace fire door glazing"],
)

add(
    "landlord-lock-change-support",
    "Landlord lock-change support swaps cylinders or locks between tenancies, on the instruction of the agent, and hands the keys over properly.",
    "We change what is on the list: a cylinder, a night latch, a multipoint. Doors are left locking, and we check we have not broken a multipoint by changing only the cylinder. Fire doors are not cut to take a different lock. Keys are labelled to the property. We do not change a lock because a caller asks us to; the instruction comes from the agent. The POA is the list of doors.",
    "how many doors and which lock type",
    "Who can ask you to change the lock?",
    "The agent or the owner who instructs the job. We do not change locks on a doorstep request.",
    "Do you keep a key?",
    "No. Keys go to the agent as agreed. We do not retain a key to a rental.",
    ["Agent instruction only", "Multipoint checked after a cylinder swap", "Keys labelled and handed over"],
    ["landlord lock change", "change locks between tenants"],
)

add(
    "letterbox-intumescent-upgrade",
    "A letterbox intumescent upgrade fits a letterplate that a fire door is allowed to have, with the intumescent the plate needs.",
    "A domestic letterbox cut into a fire door can undo the door. We fit a letterplate rated for the job, or we refuse the hole. Existing oversized holes are reported, not covered with a bigger plate. We do not certificate the whole door because the letterplate is new. The POA is the door we have looked at.",
    "whether the door is a fire door and what hole is already there",
    "Can every fire door have a letterbox?",
    "No. Only with a plate the door can take. If it cannot, post goes elsewhere.",
    "Will this make the door compliant?",
    "It fixes the letterplate. It does not repair seals, closers or a leaf that has been planed.",
    ["Rated plate or no hole", "Oversized holes reported", "Not a whole-door certificate"],
    ["intumescent letterbox", "fire door letterplate"],
)

# ---------------------------------------------------------------------------
# Building maintenance
# ---------------------------------------------------------------------------

add(
    "block-maintenance-services",
    "Block maintenance is the planned and reactive fabric work on a residential block: communal doors, stairs, gutters and the small failures that become complaints.",
    "We work to the managing agent's list and the access rules of the block. Communal jobs are photographed. Anything that is a fire door, a leak into a flat, or an electrical fault is named for what it is rather than lost in a handyman line. We do not pretend a maintenance visit is a fire risk assessment. The POA is either a visit rate you have agreed or a quote for the list.",
    "the list and the access rules of the block",
    "Do you cover evenings for a block?",
    "When the job needs the building empty or the manager present, we book that. We do not promise a night gang on this page.",
    "Is this a full repairing lease?",
    "No. We do the instructed items. The lease is the landlord's document.",
    ["Agent's list", "Fire doors and leaks named properly", "Not sold as a fire risk assessment"],
    ["block maintenance", "communal repairs"],
)

add(
    "building-defect-remedial",
    "Building defect remedial is the repair of a defect someone has already found: a leak path, a failed detail, a making-good after a survey.",
    "We read the note, look at the defect, and quote the repair of that defect. If the survey is wrong about the cause we say so before we build the wrong thing. The remedial is photographed. We do not take on design liability for a defect that needs an engineer or a warranty claim. The POA describes the repair we are actually doing.",
    "the defect as written, checked on site",
    "What if you disagree with the survey?",
    "We say so. Building the wrong repair because it was on a list helps nobody.",
    "Do you deal with the NHBC claim?",
    "No. We repair what you instruct. Claims are between you and the warranty.",
    ["Cause checked before we repair", "Photos of the remedial", "Warranty claims are not our paperwork"],
    ["remedial building works", "repair a surveyed defect"],
)

add(
    "building-maintenance-contract",
    "A building maintenance contract is an agreed way of booking fabric repairs across a property or a small portfolio, with a list of what is included.",
    "The contract says which buildings, which trades, and how a reactive visit is instructed. It is not an open promise to rebuild the estate. Planned items and reactive items are separated. Fire, gas and electrical certificates stay in their own services. You get a written POA or a schedule of rates you have agreed, not a number invented on this page.",
    "which buildings and which trades sit in the contract",
    "Is every repair included?",
    "No. The contract lists what is. Roofs, leaks and a kicked door are different scales and the schedule says so.",
    "How do we raise a job?",
    "The way the contract says: a name, a photo and the address. We do not take jobs from every resident's text unless that is the agreement.",
    ["Buildings and trades named", "Reactive and planned separated", "No open-ended rebuild promise"],
    ["building maintenance contract", "planned fabric repairs"],
)

add(
    "commercial-building-maintenance",
    "Commercial building maintenance is fabric repairs on offices, shops and light industrial units, booked around the people who are trying to work there.",
    "We agree access, induction and what 'make good' means in a customer-facing unit. A ceiling tile, a door, a leak over a shop floor and a damaged partition are typical. We do not start dusty works in a trading shop without a time. Anything electrical beyond a lamp is handed to our electricians rather than bodged. The POA is the visit or the quoted item.",
    "access hours and the item to be repaired",
    "Can you come before the shop opens?",
    "Yes when that is booked. We do not turn up at noon to cut plasterboard in a trading shop.",
    "Do you do the electrics?",
    "Lamps and simple communal fittings on a maintenance visit. Circuit faults go to an electrician.",
    ["Booked around trading hours", "Electrical faults passed to electricians", "Make-good agreed"],
    ["shop and office repairs", "commercial fabric maintenance"],
)

add(
    "communal-area-maintenance",
    "Communal area maintenance keeps stairs, landings, entrances and bin stores usable: doors, closer adjustments, minor fabric and the things residents trip on.",
    "We repair what the manager has listed. Loose nosings, a failed closer, a broken pane in a communal screen and a bin-store door are typical. We do not redecorate the block because we were there for a closer. Fire doors are adjusted, not propped, and not planed. Photos go to the manager. The POA is the list.",
    "the manager's list and access to the block",
    "Will you repaint the staircase?",
    "Only if decoration is on the list. A repair visit is not a decoration contract.",
    "Who lets you in?",
    "The manager or the code they give us. We do not expect a resident to be responsible for communal access.",
    ["Manager's list only", "Fire doors not planed or propped", "Decoration is a separate instruction"],
    ["communal hallway repairs", "stair and landing maintenance"],
)

add(
    "estate-maintenance-package",
    "An estate maintenance package is a repeating round of fabric items across a small estate: gutters, communal doors, external timber and the defects the caretaker logs.",
    "We agree the round, the report you get back, and what is too big for the round and needs its own quote. A roof leak is not swallowed inside a caretaker package. External painting is not implied. The package has a written scope and a POA or a rate you have already agreed. This page does not publish a price.",
    "the size of the estate and what the round includes",
    "Does the package include roofs?",
    "Inspection of a gutter on the round, maybe. A roof repair is quoted on its own.",
    "What report do we get?",
    "A short note of what was done and what needs a separate quote.",
    ["Round agreed in writing", "Big repairs quoted outside the package", "No published package price"],
    ["estate maintenance", "caretaker repair package"],
)

add(
    "fabric-repairs-package",
    "A fabric repairs package is a defined batch of building-fabric items — brick, render, timber, doors, gutters — done together so a property is handed back in one go.",
    "We survey the list, drop anything that is a different trade's certificate, and price the fabric items we can see. The package is not a day-rate for 'whatever we find'. Extra items are a revised POA. You get photos and a completion note. Kitchens, rewires and gas are not hidden inside a fabric package.",
    "the surveyed list of fabric items",
    "Can you add the kitchen while you are there?",
    "Not inside this package. A kitchen is its own scope.",
    "How do you stop the list growing?",
    "The quote is the list. New items wait for a revised figure.",
    ["Fabric items only", "Survey before the price", "Extras are a revised POA"],
    ["fabric repair package", "building fabric making good"],
)

add(
    "handyman-plus-compliance",
    "Handyman-plus-compliance is small fabric jobs done by people who will not 'just sort' a fire door, a gas flue or a consumer unit.",
    "We hang the shelves, ease the door, patch the plaster and reset the gutter clip. If the job is actually an EICR remedial, a gas defect or a fire-door replacement, we stop and book the right service. The value is knowing the difference. The POA is the small list, with anything regulated taken off it.",
    "which items are truly small fabric, and which are regulated",
    "Will you change a consumer unit on a handyman visit?",
    "No. That is electrical work with its own certificate.",
    "What will you do?",
    "The unregulated fabric list: easing, patching, small timber, minor rainwater, ironmongery that is not a fire-door modification.",
    ["Regulated work taken off the list", "Small fabric done properly", "Fire doors are not planed"],
    ["handyman who knows compliance", "small building repairs"],
)

add(
    "landlord-building-maintenance",
    "Landlord building maintenance is the reactive fabric list for rentals: the jobs between tenancies and the ones that cannot wait for a void.",
    "Agents send a list and access. We do the fabric items, photograph them, and send back anything that is gas, electrical certification or a roof replacement as its own quote. Occupied visits are booked with the tenant. We do not upsell a refurbishment from a dripping overflow. The POA is the list.",
    "the agent's list and whether the property is tenanted",
    "How fast can you attend?",
    "Urgent water-in and no-access-to-the-building jobs are prioritised. A squeaky door waits on the round. We say which yours is.",
    "Do you talk to the tenant about the deposit?",
    "No. We repair and we photograph. The deposit is the agent's conversation.",
    ["Agent list and photos", "Regulated work split out", "No refurbishment upsell from a drip"],
    ["landlord repairs", "rental maintenance visit"],
)

add(
    "planned-building-maintenance",
    "Planned building maintenance is a scheduled round of fabric checks and minor repairs, so the same gutters, doors and roofs are not a surprise every winter.",
    "We agree the assets and the months. The visit note says what was checked, what was done, and what is wearing out. It is not a PPM certificate for gas or electrics. Minor repairs within the round are listed; anything bigger is a POA of its own. You can stop the round. Nothing on this page is a locked-in annual price.",
    "which assets are on the round",
    "Is this SFG20?",
    "It is a planned round in that spirit. We do not claim an SFG20 licence or a particular software schedule unless we have agreed one.",
    "What is a minor repair?",
    "The ones named in the round: a reseat, a seal, a reset. A new roof is not minor.",
    ["Assets and months agreed", "Notes say what is wearing out", "No locked-in price on this page"],
    ["planned maintenance fabric", "scheduled building repairs"],
)

add(
    "reactive-building-repairs",
    "Reactive building repairs are the one-off fabric jobs that are not on a contract: a leak, a broken door, a failed section of gutter, a hole in a ceiling.",
    "You tell us the address and what has happened. We look, we make safe if something is actively damaging the building, and we quote the repair POA. Making safe is not the full repair. We say which trade it actually is before we start. The written figure follows the look.",
    "what has failed and whether it is still getting worse",
    "Is making safe the repair?",
    "No. A sheet over a hole or a tap isolated is making safe. The repair is the quote after.",
    "Do you cover evenings?",
    "When the failure warrants it and we have someone. We do not print a 24-hour promise for every squeak.",
    ["Make-safe separated from the repair", "Right trade named", "POA after the look"],
    ["reactive building repair", "one-off fabric repair"],
)

add(
    "bin-store-repair",
    "Bin store repair fixes the doors, frames, latches and cladding on a bin store that has been hit, rotted or left not shutting.",
    "We repair the door that will not latch, the frame that has split, and the closer or the spring that has gone. A store that is rotting from the bottom up is quoted as timber replacement, not a new latch on compost. We leave it shutting so it does not bang all night. The POA is the store we have seen.",
    "what is actually broken on the store",
    "Can you stop the door slamming?",
    "Yes, if a closer or a stay will do it. A twisted frame needs the frame, not another spring.",
    "Do you wash the bins?",
    "No. We repair the structure. Cleaning is not the job.",
    ["Door left shutting", "Rotten frames replaced rather than re-latched", "Not a bin-cleaning visit"],
    ["repair bin store doors", "bin store frame"],
)

add(
    "ceiling-tile-replace",
    "Ceiling tile replacement puts matching tiles back in a suspended ceiling where they are stained, broken or missing.",
    "We match the tile if the grid still has that type. A stained tile from a leak is replaced after the leak is dealt with, or the new tile stains too. Grid that is bent is straightened or the damaged tee is replaced. We do not mix random tiles and call it done. The POA is the tile count.",
    "how many tiles and whether the leak has stopped",
    "Can you match an old tile?",
    "If that tile is still made, yes. If not, we say the ceiling will have a different tile and where.",
    "Why did it stain?",
    "Usually a leak or a pipe above. We will not keep replacing tiles under an active leak.",
    ["Leak stopped first", "Tile type matched", "Bent grid noted"],
    ["replace ceiling tiles", "stained suspended ceiling tile"],
)

add(
    "communal-light-repair",
    "Communal light repair deals with a dark landing or stair: a lamp, a fitting or a photocell that has failed, and it stops when the fault is in the circuit.",
    "We replace lamps and failed bulkheads that are safe to swap, and we record it. If the circuit is dead, tripping, or needs a new cable, that is an electrician's job and we book it as such rather than leaving a connector block in a stair. Emergency lights are tested by the emergency-lighting service, not guessed at on a handyman visit. The POA is the fitting we can see.",
    "whether it is a lamp or a circuit fault",
    "Do you certify the communal electrics?",
    "No. A lamp swap is maintenance. An EICR or a circuit repair is the electrical service.",
    "What about emergency lights?",
    "A tube swap might be maintenance. A failed emergency system is the emergency-lighting job, with its own record.",
    ["Lamps and failed fittings", "Circuit faults passed to an electrician", "Emergency systems are not guessed"],
    ["communal landing light", "stair light repair"],
)

add(
    "door-closer-communal",
    "A communal door closer repair or replacement makes an entrance or stair door close and latch, which is how those doors earn their keep.",
    "We fit a closer of a size the door can take, on fixings that hold in the frame. A fire door gets a closer and stays off a wedge. If the door is binding or the frame has swollen, we ease the cause rather than winding the closer until residents cannot open it. The POA is per door.",
    "the door and whether it binds",
    "Residents have wedged it open. Can you just remove the closer?",
    "Not if it is a fire door. We make it close properly and we tell the manager about the wedge.",
    "Why is it so heavy?",
    "The power is too high or the door is binding. We set the power and ease the door.",
    ["Closer sized to the door", "Fire doors are not wedged", "Binding eased rather than overpowered"],
    ["communal door closer", "entrance door will not shut"],
)

add(
    "entrance-matwell-repair",
    "Entrance matwell repair resets a loose, rocking or missing entrance mat frame so it is not a trip at the front door.",
    "We refix or replace the frame, and we seat the mat so it does not rock. If the screed under the frame has broken up, that is filled or the area is recast; a new frame on rubble still rocks. The mat itself is replaced only if the quote says so. The POA is the entrance we have seen.",
    "whether the frame, the mat or the screed has failed",
    "Is a rocking mat a repair?",
    "Yes. It is a trip. We do not leave it because it is 'only the mat'.",
    "Do you supply a new mat?",
    "When it is on the quote. Often the frame is the failure and the mat is fine.",
    ["Frame seated", "Broken screed dealt with", "Left as a level walking surface"],
    ["repair entrance matwell", "loose entrance mat"],
)

add(
    "estate-noticeboard-fix",
    "An estate noticeboard fix re-secures or replaces a communal board that has come off the wall or lost its door.",
    "We fix into something that will hold it, not into soft plaster with the same plugs that failed. A lockable door is put back if the board had one. We do not decide what notices the residents may post. Position stays where it was unless the manager wants it moved. The POA is the board.",
    "the wall it has to go back on and whether the door is intact",
    "Can you move it to a new wall?",
    "Yes if the manager asks. We check the wall will take it.",
    "Do you supply the cork or the letters?",
    "We repair the board. Printed notices are the manager's.",
    ["Fixed into a sound wall", "Lockable door put back if it had one", "Notices are not our content"],
    ["refix notice board", "communal noticeboard repair"],
)

add(
    "gutter-clear-block",
    "A gutter clear on a block is the communal gutters and outlets emptied so they stop pouring down the face of the building.",
    "We clear the runs we can reach safely, flush to the hopper, and note the joints that leak once the water is moving. High blocks need proper access and that is in the POA. We do not lean a ladder on a balcony and call it a clearance. Residents' windows are told via the manager. A split gutter is quoted as a repair, not cleared and left.",
    "access height and the length of gutter",
    "How often does a block need this?",
    "Trees and flat roofs nearby mean more often. We say what we pulled out so you can set the next visit.",
    "Do you repair while you are up there?",
    "Small reseals, if agreed. A split run is a quote, not a surprise extra.",
    ["Safe access priced", "Leaking joints noted", "Split gutters not ignored"],
    ["clear block gutters", "communal gutter clearance"],
)

add(
    "ironmongery-batch-replace",
    "An ironmongery batch replace swaps a set of handles, hinges, closers or numerals across a block or a portfolio so they match and they work.",
    "We count the items, check fire doors before we change hinges or closers, and keep the same function. A fire door hinge is not a domestic butt hinge. Numerals and knockers are straightforward. We label what we have done. The POA is the count after a walk-round, not a guess from a spreadsheet.",
    "the count and which doors are fire doors",
    "Can you change hinges on a fire door?",
    "Only for hinges that door can take. We do not lighten a fire door with the wrong hinge.",
    "Will they all match?",
    "Yes, that is the point of a batch. We agree the product once.",
    ["Counted on a walk-round", "Fire-door ironmongery respected", "One product agreed"],
    ["replace door handles in a block", "batch door ironmongery"],
)

add(
    "landlord-reactive-visit",
    "A landlord reactive visit is one attendance to a reported fabric fault, with a finding even if the repair itself has to wait for parts.",
    "We attend the address, see the fault, and either repair it or explain what is needed. A visit that finds a different problem — a leak rather than 'damp', a closer rather than 'the door is broken' — is written that way. We do not charge a made-up price on this page; the visit terms are agreed when you book. Parts that are not on the van are a follow-up POA.",
    "the reported fault and access",
    "What if nobody is in?",
    "We cannot invent access. Missed access is recorded. We do not force a door.",
    "Do you fix it the same day?",
    "When the fault and the parts allow. If not, you get the finding and a figure.",
    ["Finding written down", "No forced access", "Parts that are not on the van are a follow-up"],
    ["landlord call-out repair", "reactive maintenance visit"],
)

# ---------------------------------------------------------------------------
# Building surveys — written notes, not a chartered survey practice
# ---------------------------------------------------------------------------

add(
    "building-condition-survey",
    "A building condition survey from us is a plain-English look at the fabric of a property, with priorities, not a chartered building survey.",
    "We walk the inside and the outside, the loft if it is safe, and the rainwater. The note separates things that are letting water in now from things that can wait. We do not value the house and we do not claim RICS membership. Defects are photographed. If you want repairs, each one is its own POA. The survey itself is scoped before we visit.",
    "the size of the property and whether the loft and the outside are accessible",
    "Is this a RICS survey?",
    "No. It is a condition note from a contractor who can also repair. Say if you need a chartered survey instead.",
    "Will you price the repairs in the note?",
    "We can add a POA against the items you care about. The note itself is the findings.",
    ["Plain-English priorities", "Not a valuation", "Not sold as a chartered survey"],
    ["condition survey", "what is wrong with the house"],
)

add(
    "commercial-condition-survey",
    "A commercial condition survey records the fabric of a shop, office or light industrial unit before a lease, a purchase or a programme of works.",
    "We look at roofs, cladding, doors, wet areas and the plant rooms we are allowed into. The note is for an occupier or a landlord, not a red-book valuation. Access limits are written down: if we could not get on the roof, the note says so. Recommended works are priorities, not a tender. The visit is scoped in writing, POA, with no invented fee on this page.",
    "unit size and which roofs and plant rooms we can enter",
    "Can you survey from the photos the agent sent?",
    "No. If we have not stood in it, we have not surveyed it.",
    "Will this satisfy a lender?",
    "Probably not. Lenders usually want their own valuer. Ours is a fabric note.",
    ["Access limits written down", "Not a valuation", "Priorities, not a tender"],
    ["commercial building condition", "shop unit survey"],
)

add(
    "defect-survey",
    "A defect survey is a look at one problem — a crack, a leak, a failed floor — written so you know what to do next.",
    "We investigate the defect you named. We say what we think the cause is, what we ruled out, and what we could not see. A crack survey is not a whole-house survey slipped in. Structural movement is referred to an engineer rather than given a confident wrong answer. The POA is the investigation, and any opening-up is agreed before we cut.",
    "the defect you named and whether we may open it up",
    "Will you open the wall?",
    "Only if you have agreed. A look from the outside is sometimes enough, and sometimes it is not. We say which.",
    "Can you be certain?",
    "We say how sure we are. Hidden structure stays hidden until it is opened.",
    ["One defect, properly", "Opening-up agreed", "Engineer referred when it is structural"],
    ["investigate a crack", "defect report"],
)

add(
    "dilapidations-support-survey",
    "Dilapidations support is a fabric schedule of condition-related wants at the end or start of a lease, from the building side, not a solicitor's claim.",
    "We list the items we can see: finishes, doors, roofs, rainwater, yards. We do not draft the legal claim and we do not price the other side's schedule as if it were gospel. Where an item is fair we say so. Where it is betterment we say that too. The document is a support to your adviser. The visit is a written POA.",
    "the unit and whether a schedule already exists",
    "Do you act as the surveyor on the claim?",
    "No. We give you the fabric list. Your solicitor or surveyor runs the claim.",
    "Will you price their schedule?",
    "We can price the items that are real building work, separately, so you can see the number.",
    ["Fabric list, not a legal claim", "Betterment called out", "Your adviser stays in charge"],
    ["dilapidations building schedule", "end of lease repairs"],
)

add(
    "landlord-condition-report",
    "A landlord condition report records the fabric of a rental at a point in time, with photos an agent can keep on the file.",
    "It is not a tenant inventory of socks and sofas. We note roofs, windows, damp signs, doors, gutters and anything unsafe. It is also not an EICR or a gas certificate. Those are named if they are missing, not faked. The report is dated. Repairs are optional POAs. The report visit is scoped before we go.",
    "the property and whether loft and outside are included",
    "Is this an inventory?",
    "No. We do not schedule the tenant's goods. It is the building.",
    "Does it replace the gas certificate?",
    "No. If the gas record is missing we say it is missing.",
    ["Dated photos", "Not an inventory", "Certificates are not invented"],
    ["landlord condition report", "rental property condition"],
)

add(
    "pre-purchase-building-survey-support",
    "Pre-purchase survey support is a second pair of eyes on the fabric before you buy, from people who repair these houses, not a valuation for the lender.",
    "We look for the expensive items: roof, damp pattern, movement, windows, services that are obviously at the end of life. We do not tell you what to pay for the house. If a chartered survey has already been done, we can look at the items it flagged. The note is blunt. The visit is agreed as a POA before we travel.",
    "the house and whether a survey already exists",
    "Will you tell me not to buy it?",
    "We will tell you the fabric facts. The decision to buy stays yours.",
    "Can you come to the viewing?",
    "We can visit by agreement with the vendor. We do not turn up uninvited.",
    ["Expensive fabric items first", "Not a valuation", "Vendor access agreed"],
    ["survey before I buy", "pre purchase building look"],
)

add(
    "pre-works-survey",
    "A pre-works survey scopes a repair or a refurb before anyone starts, so the quote is based on the building rather than a text message.",
    "We measure the items you want done, check access, and note the things that will change the price: rotten timber, a failed deck, a wall that is wet. The result is a scope you can say yes to. We do not start the works on the back of the survey unless you have accepted a POA. Discovery items are listed, not hidden.",
    "the works you think you want",
    "Is the survey free if I go ahead?",
    "Only if we have said that in writing. Otherwise it is its own small scope. We do not bury a condition in this page.",
    "What if you find rot?",
    "It goes on the scope before we price the pretty work on top of it.",
    ["Measured scope", "Discovery items listed", "Works start only after a POA is accepted"],
    ["survey before quoting works", "pre works inspection"],
)

add(
    "schedule-of-condition",
    "A schedule of condition records the state of a property on a given day, so later arguments have a dated baseline.",
    "We photograph and describe roofs, elevations, interiors and external areas room by room. It is a record, not a specification of repairs and not a legal dilaps claim. Limits are stated: furniture not moved, loft not entered, roof not walked. The schedule is dated and described as ours, a contractor's record, not a court expert's report. The POA is the size of the record.",
    "size of the property and any areas we cannot enter",
    "Can this be used in a dispute?",
    "It is evidence of what we saw. It is not an expert-witness report. Your solicitor decides how to use it.",
    "Do you move the tenant's furniture?",
    "No. What we could not see is written as not seen.",
    ["Dated room-by-room record", "Limits written down", "Not an expert-witness report"],
    ["schedule of condition", "condition baseline photos"],
)

add(
    "snagging-survey",
    "A snagging survey lists the unfinished and defective items on a new or newly refurbished property before you accept them.",
    "We walk it with a list: decoration, doors that do not latch, mastic, leaks, missing ironmongery, external works. Cosmetic taste is separated from items that are actually incomplete. We do not project-manage the developer into fixing them. You get the list. Structural concerns are flagged for an engineer rather than snagged as a paint run. The visit is a POA.",
    "the property and whether it is a new build or a refurb",
    "Will the builder listen to you?",
    "They might listen to the list. We do not pretend to compel a developer.",
    "Do you include the garden?",
    "Yes if it is in the plot we were asked to walk. We say what we did not walk.",
    ["Incomplete separated from taste", "Developer not compelled by us", "Structure flagged, not snagged as paint"],
    ["snagging list", "new build snagging"],
)

add(
    "void-property-survey",
    "A void property survey is the fabric look at an empty rental before you decide the works to get it let.",
    "We note what must be done for the next tenant and what can wait: damp signs, windows, doors, leaks, dangerous floors, missing fans. It is not the compliance pack. Gas, electric and alarms are pointed at if they are due, and booked as those services. The void list can become a fabric POA. The survey is the look.",
    "the empty property and how much of it we can access",
    "Will you write the refurb spec?",
    "We will list the fabric works we recommend. A full interior-design spec is not the survey.",
    "Can you start the next day?",
    "Only on items you have accepted a price for. The survey is not an instruction to strip the house.",
    ["Must-do separated from can-wait", "Compliance jobs pointed at, not faked", "No silent instruction to start"],
    ["void inspection", "empty rental survey"],
)

add(
    "access-equipment-survey-support",
    "Access equipment survey support decides how a roof, a gable or a stair can be reached safely before we price the work.",
    "We look at height, ground, balconies and where a tower or a scaffold can stand. The note says ladder, tower or scaffold, and what we will not climb. That decision changes the repair price, so it is made first. We do not pretend a drone replaces standing on a roof for a repair. The visit is a small POA or part of the repair quote, as we agree.",
    "height and where any tower or scaffold would stand",
    "Can you always use a ladder?",
    "No. If the eaves or the ground make a ladder unsafe, the quote includes proper access or we do not go up.",
    "Is a drone enough?",
    "Enough to see a ridge, sometimes. Not enough to repair it, and not enough to feel a soft deck.",
    ["Access chosen before the repair price", "Unsafe climbs refused", "A drone is not a repair"],
    ["scaffold or tower survey", "how to access the roof"],
)

add(
    "damp-and-condensation-survey",
    "A damp and condensation survey separates rising damp, leaks, rain penetration and condensation, in writing, without a treatment already loaded in the van.",
    "We look outside and inside: gutters, ground levels, fans, cold corners and the pattern of the stain. A moisture meter is a clue, not the verdict. The note recommends the next action, which might be a gutter repair or a fan, not a chemical DPC. You can take the note elsewhere. Any works are a later POA.",
    "which rooms and whether we can see the outside and the fans",
    "Do you sell injection at the end?",
    "Only if the pattern supports it, and only as a separate quote. The survey does not require you to buy it.",
    "Can you test for mould species?",
    "We do not sell laboratory mould typing as part of this visit. We identify the moisture mechanism.",
    ["Causes separated", "Meter is not the verdict", "Works are optional and later"],
    ["damp and condensation survey", "is it mould or rising damp"],
)

add(
    "fire-damage-reinstatement-survey",
    "A fire damage reinstatement survey records what the fire and the water have done to the fabric, so reinstatement can be scoped.",
    "We list what has to come off, what can stay, and what is structural enough to need an engineer. We do not settle the insurance claim and we do not start stripping on the survey visit unless you have a separate instruction. Charred structure is not given a cheerful 'it will be fine'. The note is dated and photographic. The survey is a written POA.",
    "how far we can safely enter",
    "Will you deal with the insurer?",
    "We can give you the fabric list. The claim is yours and your loss adjuster's.",
    "Is the building safe to enter?",
    "If it is not, we say so and we stop. A survey is not a reason to walk a failing floor.",
    ["What comes off and what can stay", "Engineer flagged for structure", "The claim is not ours to settle"],
    ["fire damage survey", "reinstatement after a fire"],
)

add(
    "landlord-inventory-plus-condition",
    "Inventory-plus-condition adds a fabric condition note alongside an inventory you already have, or a simple condition schedule if you need both on one visit.",
    "We do not replace a professional clerk if you need a signed inventory of every item. We do record the building: marks, damp, doors, windows, meters we can see. The two are labelled so a later argument knows which is the tenant's goods and which is the fabric. Photos are dated. The visit scope is agreed POA.",
    "whether you need goods listed as well as fabric",
    "Is this a legal inventory?",
    "Not by itself. If you need a clerked inventory, use a clerk. We will do the fabric beside it.",
    "Do you record meter readings?",
    "Yes when we can see the meters and you have asked. We do not force a cupboard.",
    ["Goods and fabric labelled separately", "Not a substitute for a clerk", "Dated photos"],
    ["inventory and condition", "check-in condition report"],
)

add(
    "leak-origin-visual-survey",
    "A leak origin visual survey traces a leak as far as it can be traced without ripping the building apart, and says where opening-up should happen.",
    "We follow the stain, the loft, the pipes we can see, the gutter and the roof detail. We rule things out. If the next step is a ceiling down or a tile off, we ask before we open it. The result is a cause or a shortlist, not a guess dressed as certainty. The repair is a separate POA. The survey is the looking.",
    "where the water shows and which voids we can enter",
    "Can you always find it?",
    "Not from a visual survey. We say what we excluded and what has to be opened.",
    "Do you use a thermal camera?",
    "When it will add something. A camera is not a diagnosis on its own, and we do not sell the visit as a gadget.",
    ["Things ruled out", "Opening-up asked for", "Repair quoted separately"],
    ["find the leak", "trace where water is coming from"],
)

# ---------------------------------------------------------------------------
# Joinery
# ---------------------------------------------------------------------------

add(
    "architrave-installation",
    "Architrave installation is the trim around a door or window, mitred and fixed so the joint does not open when the timber moves.",
    "We set the margin off the lining, mitre the corners, and fix into the lining rather than into fresh plaster that will not hold a nail. Profiles are matched to the rest of the house where we can still buy them. MDF and timber are different quotes. The POA is the number of openings and the profile.",
    "how many openings and which profile",
    "Can you match old Victorian architrave?",
    "If the profile is still made or can be run, yes. If not, we show you the nearest section before we fit a whole room of it.",
    "Do you fill and paint?",
    "We leave it ready. Decoration is included only if the quote says so.",
    ["Mitres that can move", "Fixed into the lining", "Profile agreed before we cut a whole room"],
    ["fit architrave", "door trim carpentry"],
)

add(
    "bespoke-shelving",
    "Bespoke shelving is shelves built for the alcove or the wall you have, fixed so they carry the books or the files you said they would.",
    "We measure the alcove, agree the material and the loads, and put fixings into structure. A floating shelf into dot-and-dab plasterboard will come down, and we will not fix it that way. Edges and finishes are agreed: paint-grade or a veneer. The POA is the design we have sketched, not a guess per metre from a photo.",
    "the alcove and what the shelves have to carry",
    "Can you match the chimney alcoves?",
    "Yes, that is the usual job. We scribe to the wall rather than leaving a wedge of daylight.",
    "Will they hold a television?",
    "Only if we have framed for that load. Tell us before we build a decorative shelf.",
    ["Scribed to the alcove", "Fixings into structure", "Load agreed"],
    ["alcove shelves", "built in shelving"],
)

add(
    "boxing-in-pipes",
    "Boxing-in pipes builds a timber and board duct around pipes so they can be decorated, with a way back to valves and hatches.",
    "We do not box a leaking pipe or a valve you will need next winter with no hatch. The duct is straight, beaded or edged as agreed, and screwed where it has to come off. Soil pipes and heating pipes are identified so we do not bury an access. Fire-rated ducts are only built when someone has specified the rating. The POA is the run.",
    "the pipe run and where the valves are",
    "Can you box the stopcock?",
    "We can box the run and leave a hatch. We do not seal a stopcock in.",
    "Is the box fire rated?",
    "Only if that is specified. A bathroom duct is not a fire shaft.",
    ["Hatches at valves", "Leaks are not boxed in", "Fire rating only when specified"],
    ["box in pipes", "pipe boxing joinery"],
)

add(
    "built-in-wardrobes",
    "Built-in wardrobes are a carcass and doors made to the alcove, with hanging rail and shelves that match how the room is used.",
    "We measure, agree doors — hinged or sliding — and build to the ceiling line you want. Sliding gear is specified for the door weight. A wardrobe on a damp external wall is ventilated or we say it will grow mould behind it. Decoration and mirrors are listed if they are in. The POA is the measured alcove.",
    "alcove size and door type",
    "Can you fit around a sloped ceiling?",
    "Yes. The carcass is scribed to the slope. We do not leave a triangular hole and call it fitted.",
    "Do you supply the doors only?",
    "We can. A door-only quote is not a carcass. The quote says which.",
    ["Measured to the alcove", "Damp external walls called out", "Sliding gear sized to the doors"],
    ["fitted wardrobes", "alcove wardrobe"],
)

add(
    "custom-cupboards",
    "Custom cupboards are one-off units — understairs, airing, or a run of storage — built to the space rather than forced from a catalogue box.",
    "We agree shelves, doors and what has to remain reachable: a boiler, a cylinder, a consumer unit. Those get proper doors and ventilation, not a sealed MDF tomb. Hinges are sized to the door. The POA follows a measure and a sketch.",
    "the space and what must stay reachable",
    "Can you box the boiler?",
    "With ventilation and a door that the service engineer can actually use. We do not seal a boiler in.",
    "Will it match the kitchen?",
    "We can use a similar door. An exact kitchen-range match depends on the door still being made.",
    ["Access to services kept", "Boilers ventilated", "Built to the space"],
    ["understairs cupboard", "bespoke cupboard"],
)

add(
    "door-hanging",
    "Door hanging is fitting a door so it closes into the lining, with even gaps and a latch that meets the keep.",
    "We hang on the right hinges, plane only within what the door allows, and set the latch. A fire door is hung to its gaps and is not planed to 'make it fit' beyond the trim the manufacturer allows. Floors that are out of level are dealt with by the door or by a threshold, and we say which. The POA is per door.",
    "the door and whether it is a fire door",
    "Can you hang a door on the old hinges?",
    "If they are the right size and they are sound. Fire doors need hinges that suit the door.",
    "The door sticks at the bottom. Will you cut it?",
    "We find out why. A new carpet is different from a twisted lining. Fire doors are trimmed only within allowance.",
    ["Even gaps", "Latch meets the keep", "Fire doors trimmed only within allowance"],
    ["hang a door", "fit internal doors"],
)

add(
    "fire-door-joinery-support",
    "Fire door joinery support is the hanging, the seals and the closer as a joinery job, done so we do not destroy the door while we fit it.",
    "We hang within the trim allowance, fit the seals the leaf needs, and set the closer so it latches. We do not plane the lipping off, we do not cut an oversized letterbox, and we do not stick a certificate on a door that no longer matches its frame. If the lining is the wrong size, the lining is replaced as part of a set. The POA is the door we have seen.",
    "the leaf, the frame and the gaps",
    "Can you ease a fire door that sticks?",
    "A little, within the allowance. If the lipping would be planed off, the answer is a different door or a frame adjustment, not a smaller door.",
    "Do you issue the fire certificate?",
    "We confirm what we fitted. We do not invent a certificate the door manufacturer did not issue.",
    ["Trim allowance respected", "Seals and closer set to latch", "No sticker on a ruined leaf"],
    ["hang a fire door", "fire door joinery"],
)

add(
    "joinery-services",
    "Joinery services are the second-fix timber jobs: doors, trims, boxing, shelves and repairs, measured before they are cut.",
    "Send the list. We say what is a hanging, what is a trim package and what is really a carpenter's first fix. Site measure beats a text. Fire doors and ordinary doors are not mixed up on the same line. The written POA is the list.",
    "the list of timber items",
    "Do you make kitchens?",
    "Kitchen fitting is its own service. We do the joinery around one, not a full kitchen supply.",
    "Can you copy a moulding?",
    "Simple mouldings, yes. A one-off knife is explained before we promise a match.",
    ["Measured list", "Fire doors kept separate", "Kitchens are not slipped into a joinery line"],
    ["second fix joinery", "carpenter for doors and trims"],
)

add(
    "landlord-joinery-repairs",
    "Landlord joinery repairs are the doors, trims and thresholds a void or a tenant needs, photographed for the agent.",
    "We ease doors, refix skirtings that have been kicked off, replace a snapped architrave and rehang a door that no longer latches. Fire doors are not planed to shut a complaint. The list is priced item by item. Occupied access is booked. The POA is that list.",
    "the agent's joinery list",
    "Will you plane every sticking door?",
    "We find out why it sticks. A fire door is not planed down to please a carpet.",
    "Can you do a whole void of doors?",
    "Yes, if they are on the list and on site. We do not hang doors that have not been delivered.",
    ["Item by item", "Fire doors protected", "Photos for the agent"],
    ["landlord door repairs", "void joinery"],
)

add(
    "skirting-boards-fitting",
    "Skirting boards are scribed to the floor, mitred on external corners and fixed so the joint stays shut.",
    "We match the height and profile already in the house where we can. Floors that are out of level are scribed, not packed with a wedge of mastic. Cable behind the skirting is agreed with the electrician before we fix over it. MDF in a bathroom is called out as a bad idea. The POA is the metre run.",
    "metre run and profile",
    "Can you match 1970s skirting?",
    "Often with a stock profile. We bring a sample if the house has a unusual torus or ogee.",
    "Do you take the old skirting off?",
    "Yes when it is being replaced. We check what is behind it.",
    ["Scribed to the floor", "Profile matched", "MDF not used where it will swell"],
    ["fit skirting boards", "replace skirting"],
)

add(
    "staircase-joinery-repairs",
    "Staircase joinery repairs deal with loose treads, a broken nosing, a failed string fixing or a handrail that moves, short of rebuilding the flight.",
    "We find what is loose. A creak is sometimes a wedge, sometimes a tread that has split. We do not glue a handrail to a wall and call a falling stair safe. If the string or the newel has failed, we say the repair is bigger. Balusters are refixed to the spacing that is already there; we do not remove them. The POA is what we have found to be loose.",
    "what is actually loose on the flight",
    "Can you stop the creak?",
    "Often, by fixing the tread that moves. A stair that is coming off the wall is not a creak.",
    "Do you replace the whole staircase?",
    "Not on this job. A failed string or newel is quoted as itself.",
    ["Loose parts found", "A failing stair is not glued and left", "Balusters stay"],
    ["repair loose stair tread", "creaking staircase"],
)

add(
    "window-board-fitting",
    "Window board fitting replaces or installs the internal board under a window, scribed to the reveals and given a fall if it needs one.",
    "We look at the board that is there. A rotten timber board under a leaking window is replaced after the leak is understood, or the new board rots too. The nose and the horns into the reveal are agreed. Tiles, paint-grade timber and moisture-resistant board are different. The POA is per window.",
    "how many boards and whether the window above them leaks",
    "The board is swollen. Is that the window?",
    "Often yes. We check the cill and the frame before we trap a new board under the same leak.",
    "Can you match the old nose?",
    "We can get close. A sample is better than a surprise thickness.",
    ["Leak checked first", "Scribed to the reveals", "Material suited to a wet window"],
    ["fit a window board", "replace internal window sill"],
)

add(
    "architrave-replacement",
    "Architrave replacement takes off a split or missing trim and fits a new run around that opening, matched as closely as the profile allows.",
    "We remove the old trim without tearing the lining out, and we pack the new architrave to an even margin. If the lining itself is rotten, trim will not save it and we say so. Corners are mitred. The POA is the openings.",
    "the openings and the profile",
    "Can you replace one length?",
    "Yes. A patch length is fair if the profile matches. A whole opening looks better when one leg is a different section, and we will say that.",
    "Will you fill the nail holes?",
    "We leave it ready for the decorator unless filling is on the quote.",
    ["Lining saved if it is sound", "Profile matched", "A rotten lining is not hidden with trim"],
    ["replace architrave", "new door trim"],
)

add(
    "banister-repair",
    "Banister repair refixes a loose handrail, a broken baluster or a wobbly newel so the stair can be used.",
    "We tighten what is loose and replace what is broken, in a section as close as we can get. A handrail that has come out of the wall is refixed into structure, not into plaster. If the whole balustrade is below a sensible height or the spindles are wide enough for a child to pass, we say so rather than tightening it and walking away. The POA is the repair we have seen.",
    "what is loose or missing",
    "Can you match a turned spindle?",
    "Common turns, yes. A one-off turning is a near match, and we show it.",
    "Is the stair legal when you have tightened it?",
    "Tight is not the same as compliant. We tell you if the height or the gaps are the real issue.",
    ["Refixed into structure", "Broken parts replaced", "Unsafe gaps are reported, not ignored"],
    ["repair banister", "loose handrail"],
)

add(
    "cupboard-conversion-joinery",
    "Cupboard conversion joinery turns a store or an underused cupboard into a usable space — shelves, a rail, a door that shuts — without pretending it is a habitable room.",
    "We look at ventilation, the door, and whether anyone intends to sleep in it. A cupboard is not a bedroom, and we will not fit it out as one in a flat where that would be a problem. Shelves and a proper door are the job. Electrics are the electrician. The POA is the cupboard measured.",
    "the cupboard and what you want to store",
    "Can you make it a bedroom?",
    "No. We will not dress a cupboard up as a bedroom.",
    "Will you add a light?",
    "The electrician does the light. We leave the carcass ready and say where the fitting is going.",
    ["Storage, not a bedroom", "Door left shutting", "Electrics kept as electrics"],
    ["convert a cupboard", "understairs storage joinery"],
)

add(
    "door-lining-replacement",
    "Door lining replacement takes out a split, swollen or out-of-square lining and fits a new one the door can hang in.",
    "A door will not hang properly in a twisted lining. We replace the lining, pack it plumb, and rehang the door if it is still fit. Fire-door linings are the frame that belongs with the door, not a standard softwood lining. Making good plaster around the lining is on the quote if we have seen the state of it. The POA is the opening.",
    "the opening and whether the door is a fire door",
    "Can you reuse the door?",
    "If it is flat and the right size, yes. A twisted door and a new lining is still a bad door.",
    "Do you replaster the reveal?",
    "Making good the edge, yes, when the old lining takes the plaster with it.",
    ["Lining packed plumb", "Fire doors get the right frame", "The old door is reused only if it is fit"],
    ["replace door lining", "new door frame internal"],
)

add(
    "kitchen-bulkhead-joinery",
    "Kitchen bulkhead joinery builds the box or pelmet above wall units, scribed to the ceiling and ready for decoration or a door.",
    "We build it after the units are in, or to a unit line that will not move. Access to services above the units is left if there is a valve. The bulkhead is fixed to structure, not just to the unit back. It is not a kitchen supply. The POA is the run.",
    "the run above the units",
    "Can you do this before the kitchen is fitted?",
    "Only to a line the fitter will actually meet. Otherwise we wait for the units.",
    "Will you paint it?",
    "We leave it ready. Paint is the decorator unless it is listed.",
    ["Built to the unit line", "Access to valves kept", "Fixed to structure"],
    ["kitchen bulkhead", "pelmet above wall units"],
)

add(
    "landlord-door-repair-joinery",
    "Landlord door repair joinery deals with a single rental door: it will not latch, it is off its hinges, or the threshold has gone.",
    "We repair that door. A fire door is treated as a fire door. A bedroom door gets eased, rehinged or rehung. We do not replace every door in the house because one failed. Photos and a line for the agent. The POA is the door.",
    "which door and what it is doing",
    "The tenant says the front door is unsafe. Can you change the lock only?",
    "If the door itself has failed, a new lock on a failed door is not the repair. We say which it is.",
    "How soon?",
    "A door that will not secure the flat is treated as urgent. A sticking bedroom door is booked.",
    ["One door, properly", "Fire doors respected", "A lock is not a substitute for a failed door"],
    ["repair a rental door", "landlord door will not shut"],
)

add(
    "loft-hatch-joinery",
    "Loft hatch joinery replaces or trims a hatch so it closes, seals and, if asked, carries insulation on the lid.",
    "We fit a hatch that sits in the opening, with a catch that holds it shut and a seal if draughts are the complaint. The opening is not enlarged into a joist without a trimmer, and enlarging it is a carpentry job we will name. The lid can take insulation if that is on the quote. The POA is the hatch.",
    "the opening and whether you want the lid insulated",
    "Can you make the hole bigger for a ladder?",
    "Only with a trimmer so we are not cutting a joist and walking away. That is quoted as carpentry.",
    "Will it stop the draught?",
    "A seal and a lid that closes, yes. A warped lid is replaced rather than sealed harder.",
    ["Lid closes and latches", "Joists are not cut casually", "Insulation on the lid if you asked"],
    ["new loft hatch", "draughty loft hatch joinery"],
)

# ---------------------------------------------------------------------------
# Carpentry
# ---------------------------------------------------------------------------

add(
    "carpentry-services",
    "Carpentry services are the structural and first-fix timber jobs: studs, joist repairs, door casings and site woodwork measured on the day.",
    "We separate carpentry from second-fix joinery so you know who is coming. If the job is a beam or a load-bearing wall, a structural engineer sizes it and we fit that size. Rot is opened until we find sound timber. The written POA follows the look.",
    "what the timber is doing and whether it is structural",
    "Do you design steels?",
    "No. We fit the timber or the trimming around a design somebody has already sized.",
    "Is this the same as a joiner?",
    "Joinery is the second fix: doors, trims, cupboards. Carpentry is the carcass of the timber.",
    ["Structural work only to a design", "Rot chased to sound timber", "Joinery is a different visit"],
    ["site carpenter", "first fix carpentry"],
)

add(
    "door-frame-installation",
    "Door frame installation sets an external or heavy frame plumb, fixed into the structure, ready for the door that belongs in it.",
    "We check the opening, the DPC under an external frame, and the fixings into masonry or stud. A frame out of plumb makes every later hinge a compromise. External frames get a weather detail. Fire-door frames are the ones that belong with the leaf. The POA is the frame.",
    "the opening and the frame type",
    "Can you fit a frame in an opening that is too small?",
    "Not by cutting structure out. The opening is adjusted properly or the frame is the wrong one.",
    "Do you hang the door the same day?",
    "If the door is on site and the frame can take it. A frame-only quote stops at the frame.",
    ["Set plumb", "External frames weathered", "Fire-door frames matched to the leaf"],
    ["fit a door frame", "new external door frame"],
)

add(
    "first-fix-carpentry",
    "First-fix carpentry is the timber carcass of a job: studs, noggins, joist trimming and the wood that other trades build on.",
    "We work to a drawing or a marked layout. Noggins go where basins, radiators and kitchen units will hang. Joists are trimmed properly around openings rather than notched until they are a matchstick. We leave the work ready for the electrician and the plumber, and we do not board over it. The POA is the layout.",
    "the layout and what has to be supported",
    "Do you come back for the doors?",
    "That is second fix. We can do it later. It is not hidden in the first-fix price.",
    "Who marks the walls?",
    "We set out from the drawing you have agreed. A change on the day is a change of price.",
    ["Noggins where the units will be", "Joists trimmed, not hacked", "Left ready for the other trades"],
    ["first fix carpenter", "stud and noggin"],
)

add(
    "floor-joist-repairs",
    "Floor joist repairs deal with a joist that is rotten, badly notched or split, once the floor is open and we can see it.",
    "We open the floor where we have to, find how far the rot goes, and repair or sister the timber that is agreed. A joist that is carrying a wall is not sistered on a hunch; an engineer sizes that. We treat the cause — a leak, a blocked air brick — or the new timber rots too. Air paths under suspended floors are kept. The POA is what we have exposed.",
    "how far the rot or the notch goes, and what the joist carries",
    "Can you repair from the cellar only?",
    "Sometimes. If the rot is at the end in the wall, we may have to open more, and we say so before we do.",
    "Will you sister every joist?",
    "No. Sound joists stay. The quote is the ones that have failed.",
    ["Opened until the rot is understood", "Load-bearing repairs engineered", "The leak or the vent is part of the cause"],
    ["rotten floor joist", "repair floor joists"],
)

add(
    "loft-floor-carpentry",
    "Loft floor carpentry lays a boarded floor in a loft on joists that can take it, or on new joists where the ceiling joists cannot.",
    "Ceiling joists are often not floor joists. We do not board a loft for storage or a room by screwing chipboard to undersized ceiling ties. If the loft is to be walked properly, the structure is looked at and, where it needs it, designed. The hatch and the trimmers are part of the job. Insulation is not crushed under the new boards. The POA follows that decision.",
    "what the loft floor is for, and the size of the existing joists",
    "Can you just board over the insulation?",
    "Only on legs, for light storage. A floor you will walk needs structure, and it must not flatten the quilt.",
    "Will this make a bedroom?",
    "Boarding is not a conversion. A habitable room needs structure, stairs, fire and building control. We say that before anyone moves a bed up.",
    ["Ceiling joists are not assumed to be a floor", "Insulation not crushed", "A bedroom is a conversion, not a sheet of board"],
    ["board the loft", "loft floor joists"],
)

add(
    "partition-carpentry",
    "Partition carpentry builds a timber stud wall, set out to a line, with a door opening where you asked for one.",
    "We confirm it is not holding the building up. Studs are at a spacing the board can span, noggins are in, and the head is fixed to structure. A door opening is the size of the lining that is coming. We do not build a stud under a sagging ceiling and call it a support. The POA is the length and height.",
    "length, height and door position",
    "Can it be soundproof?",
    "A standard empty stud is not. If sound matters we price insulation and board layers, or we tell you it will disappoint.",
    "Do you plasterboard it?",
    "Boarding can be on the same quote. A stud-only price stops at the timber.",
    ["Non-load-bearing, confirmed", "Door opening sized", "Sound expectations priced honestly"],
    ["build a stud wall", "timber partition"],
)

add(
    "second-fix-carpentry",
    "Second-fix carpentry is the later timber: hanging doors, fixing skirtings and architraves, and the ironmongery that finishes the job.",
    "We come back when the plaster is dry enough and the floors are in, or we tell you the trim will be wrong if we do it before. Doors are hung, ironmongery set, trims scribed. Fire doors are hung to their gaps. The POA is the schedule of second-fix items.",
    "the schedule and whether plaster and floors are ready",
    "Can you second-fix before the floor is laid?",
    "Skirtings, no. Doors sometimes. We say what will be wasted if we go too early.",
    "Is second fix the same as joinery?",
    "On site, yes, this is that visit: doors and trims rather than the studs.",
    ["After plaster and floors where it matters", "Fire-door gaps kept", "Schedule priced, not a day that drifts"],
    ["second fix carpenter", "hang doors and fit skirting"],
)

add(
    "site-carpentry-package",
    "A site carpentry package is an agreed bundle of first and second fix on one project, so the same carpenter owns the timber from stud to door.",
    "The package lists the walls, the trims and the doors. It does not silently include a staircase, a steel or a kitchen. Variations are written. We sequence around the other trades. The POA is the package after we have seen the job, not a rate card on this page.",
    "what is on the package list",
    "What is not included?",
    "Anything not on the list: kitchens, stairs, structural steels, decoration.",
    "Can we add a wardrobe later?",
    "Yes, as a variation with its own figure.",
    ["List written down", "Variations priced", "Stairs and kitchens are not implied"],
    ["carpentry package", "first and second fix bundle"],
)

add(
    "stud-wall-construction",
    "Stud wall construction is the timber frame of a new internal wall, ready for board, in the position you have marked.",
    "We set it out, fix it, and leave it straight. Services routes are agreed so we are not drilling a random path later. A wall that needs to carry a basin or a door closer gets noggins now. Load-bearing is a different wall. The POA is the metres.",
    "metres and what will be fixed to the wall later",
    "How thick is the wall?",
    "The stud depth plus boards. We say the finished thickness before we set it out, so it does not steal a corridor.",
    "Can you build it off a floating floor?",
    "We fix the head and the abutments into structure. A wall held only by a floating floor is a wall that will move.",
    ["Finished thickness agreed", "Noggins for the known loads", "Fixed into structure"],
    ["construct a stud wall", "timber stud partition"],
)

add(
    "timber-frame-repairs",
    "Timber frame repairs replace or sister rotten or failed studs and plates in a timber wall, once the rot is understood.",
    "We open the cladding or the board far enough to see sound timber. A patch on the face of a rotten stud is refused. The cause — a leak, a bridged DPC, a failed cill — is named. Structural plates are not cut out without a way to hold the wall up, and an engineer is involved where the wall is holding the building. The POA is the timber we have found.",
    "how far the rot goes and what the frame is holding",
    "Can you repair a rotten sole plate?",
    "Yes, in sections, with the wall held. We do not pull a plate out and hope the studs hang.",
    "Will you reclad as well?",
    "Cladding is on the quote only if it had to come off and you want it back on. We do not assume a new elevation.",
    ["Opened to sound timber", "Cause named", "The wall is held while plates are replaced"],
    ["repair timber frame wall", "rotten stud replacement"],
)

add(
    "carpentry-after-leak",
    "Carpentry after a leak replaces the timber a leak has destroyed: a rotten sill, a swelled chipboard floor, a ceiling batten that has gone.",
    "We start when the leak is stopped. Wet timber that will dry and stay sound is left. Timber that has lost its section is cut out to sound wood and replaced. Chipboard floors that have swollen are the boards, not a skim over the lump. We do not close a wet void with new plasterboard the same day. The POA is the timber we have to replace.",
    "which timber has actually failed, after the leak has stopped",
    "Should all the floor come up?",
    "No. The boards that have swollen or lost their strength come up. The rest stay.",
    "Who repairs the plaster?",
    "Plaster is the plastering job. We do the timber, and we say when the boards are ready for them.",
    ["Leak stopped first", "Only failed timber replaced", "Wet voids are not boarded shut"],
    ["rotten timber after a leak", "replace a swollen chipboard floor"],
)

add(
    "door-stop-and-ironmongery",
    "Door stop and ironmongery is the latch, the keeps, the hinges and the stop that make a door shut, short of a new door.",
    "We adjust or replace the ironmongery that has failed. A missing stop is why the latch never meets. A fire door keeps ironmongery that belongs on it. We do not pack a hinge with cardboard as the finished repair. The POA is the door.",
    "which part of the door furniture has failed",
    "The door rattles. Is that a new door?",
    "Usually it is a keep, a stop or a hinge. We do not sell a door for a rattle.",
    "Can you fit a chain and a spyhole?",
    "Yes on an ordinary entrance door. On a fire door we only fit furniture the door can have.",
    ["Stop and keep set so it latches", "Fire-door furniture respected", "A rattle is not a new door"],
    ["adjust a door latch", "fit a door stop"],
)

add(
    "flat-roof-edge-carpentry",
    "Flat roof edge carpentry repairs the timber kerb, the firrings or the deck edge that the covering is trying to dress to.",
    "A new membrane on a rotten kerb fails at the edge. We replace the rotten timber, set a fall if the firrings have collapsed, and leave an edge the roofer can dress to. We do not felt it ourselves unless the quote includes the covering. Decayed timber is cut back to sound wood. The POA is the edge we have opened.",
    "how much of the edge timber is soft",
    "Do you do the felt as well?",
    "Only if the covering is on the quote. Otherwise we leave the edge ready for the roofer.",
    "Why is the edge the leak?",
    "Because the drip and the upstand live there. A rotten kerb opens the covering.",
    ["Rotten kerbs replaced", "Falls corrected if the firrings have gone", "Covering is a separate line unless included"],
    ["flat roof timber edge", "replace roof kerb"],
)

add(
    "floorboard-replacement",
    "Floorboard replacement lifts broken or rotten boards and fits boards that match the floor as closely as today's timber allows.",
    "We do not screw a sheet of chipboard into a Victorian floor and call it a match, unless you have asked for a deck to carpet over. Sound boards stay. Nails and pipes underneath are found before we fix. Ventilation under a suspended floor is kept. The POA is the boards we have marked.",
    "how many boards and whether you need a match or a deck",
    "Can you match the old boards?",
    "Width and species, as closely as stock allows. We show a board before we rip a whole room out.",
    "What about the squeak?",
    "A squeak may be the fixing or the joist. We say if new boards will not cure it.",
    ["Sound boards kept", "Pipes found before we fix", "Chipboard is not slipped into a boarded floor"],
    ["replace floorboards", "broken floorboard"],
)

add(
    "loft-hatch-trimmer",
    "A loft hatch trimmer frames a hatch opening properly when the hole needs to be larger or the existing joists have been cut and left.",
    "We do not cut a ceiling joist and leave it hanging on the plasterboard. A trimmer carries the cut joist back to structure. If the joists are already not a floor, we say a bigger hatch does not make them one. Insulation and the lid are a separate line if you want them. The POA is the opening.",
    "the opening size and the direction of the joists",
    "Can you fit a folding ladder?",
    "Yes if the opening and the joists can take it. The ladder is listed. The trimmer is the structure.",
    "Is this a loft conversion?",
    "No. It is a safe opening. A room in the loft is a different project.",
    ["Cut joists carried by a trimmer", "Not left hanging on plasterboard", "A bigger hole is not a conversion"],
    ["trim a loft hatch", "cut a joist for a loft ladder"],
)
