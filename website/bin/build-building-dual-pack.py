#!/usr/bin/env python3
"""Build website/data/building-services-dual-p0.json and the three SVGs per trade cluster.

Town rows come from website/data/locks/dual-ring-269.csv.
Slug order comes from website/data/locks/building-services-p0.txt.
"""
from __future__ import annotations

import csv
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
LOCKS = ROOT / "data" / "locks"
OUT = ROOT / "data" / "building-services-dual-p0.json"
IMG = ROOT / "assets" / "images" / "building-dual"

# Published Greater Manchester crawl towns (building-hub-copy.php). Same slug wins
# over a same-named place outside that list (Cheadle SK8, not Cheadle ST10).
GM = {
    "bolton", "bury", "manchester", "oldham", "rochdale", "salford", "stockport",
    "tameside", "trafford", "wigan", "leigh", "atherton", "tyldesley", "horwich",
    "westhoughton", "farnworth", "kearsley", "little-lever", "radcliffe", "whitefield",
    "prestwich", "swinton", "eccles", "walkden", "worsley", "pendlebury", "irlam",
    "cadishead", "altrincham", "sale", "stretford", "urmston", "chorlton", "didsbury",
    "withington", "wythenshawe", "cheadle", "cheadle-hulme", "bramhall", "hazel-grove",
    "marple", "romiley", "hyde", "stalybridge", "dukinfield", "ashton-under-lyne",
    "mossley", "droylsden", "denton", "failsworth", "middleton", "chadderton",
    "heywood", "milnrow", "littleborough", "shaw", "royton", "lees", "uppermill",
    "saddleworth",
}

# Royal Mail outward code plus a landmark that is not the town name,
# so two towns that share a district still differ after a town-name swap.
OUTWARD = {
    "abram": ("WN2", "Bickershaw lane"),
    "accrington": ("BB5", "Abbey Street"),
    "adwick-le-street": ("DN6", "the Great North Road"),
    "alfreton": ("DE55", "the A61"),
    "allerton": ("L18", "Menlove Avenue"),
    "alsager": ("ST7", "the station side"),
    "altrincham": ("WA14", "the market"),
    "armthorpe": ("DN3", "Church Street"),
    "ashton-in-makerfield": ("WN4", "Gerard Street"),
    "ashton-under-lyne": ("OL6", "the market hall"),
    "atherton": ("M46", "Market Street"),
    "bacup": ("OL13", "the valley road"),
    "baildon": ("BD17", "the moor edge"),
    "barnoldswick": ("BB18", "the canal wharf"),
    "barnsley": ("S70", "the market"),
    "barrow-in-furness": ("LA14", "the shipyard side"),
    "batley": ("WF17", "Commercial Street"),
    "bebington": ("CH63", "the village cross"),
    "beighton": ("S20", "the Rother edge"),
    "belper": ("DE56", "King Street"),
    "bentley": ("DN5", "the Askern road"),
    "biddulph": ("ST8", "High Street"),
    "bingley": ("BD16", "the canal locks"),
    "birkenhead": ("CH41", "Hamilton Square"),
    "blackburn": ("BB1", "the cathedral quarter"),
    "blackpool": ("FY1", "the promenade"),
    "blacon": ("CH1", "the estate centre"),
    "bolsover": ("S44", "the castle hill"),
    "bolton": ("BL1", "the town hall"),
    "bolton-upon-dearne": ("S63", "the Dearne valley"),
    "bootle": ("L20", "the strand"),
    "bradford": ("BD1", "Centenary Square"),
    "bramhall": ("SK7", "the park side"),
    "bredbury": ("SK6", "the A560"),
    "brierfield": ("BB9", "Colne Road"),
    "brighouse": ("HD6", "the canal basin"),
    "bromborough": ("CH62", "the village"),
    "brymbo": ("LL11", "the steelworks hill"),
    "buckley": ("CH7", "the common"),
    "burngreave": ("S4", "Spital Hill"),
    "burnley": ("BB11", "the town hall"),
    "bury": ("BL9", "the rock"),
    "buxton": ("SK17", "the crescent"),
    "cadishead": ("M44", "the ship canal"),
    "castleford": ("WF10", "the river bridge"),
    "chadderton": ("OL9", "the Broadway"),
    "chapel-allerton": ("LS7", "Harrogate Road"),
    "chapeltown": ("S35", "the A629"),
    "cheadle": ("SK8", "the village green"),
    "cheadle-hulme": ("SK8", "the station district"),
    "chester": ("CH1", "the rows"),
    "chesterfield": ("S40", "the crooked spire"),
    "childwall": ("L16", "Woolton Road"),
    "chorley": ("PR7", "the market"),
    "chorlton": ("M21", "Beech Road"),
    "clayton-le-woods": ("PR6", "the A6"),
    "cleckheaton": ("BD19", "the market"),
    "cleveleys": ("FY5", "the seafront"),
    "clitheroe": ("BB7", "the castle"),
    "colne": ("BB8", "the high street"),
    "congleton": ("CW12", "the town bridge"),
    "conisbrough": ("DN12", "the castle"),
    "crewe": ("CW1", "the station"),
    "cross-hills": ("BD20", "the A629"),
    "cudworth": ("S72", "the Barnsley road"),
    "darton": ("S75", "the station"),
    "darwen": ("BB3", "the market"),
    "denton": ("M34", "Crown Point"),
    "derby": ("DE1", "the market place"),
    "dewsbury": ("WF12", "the market"),
    "didsbury": ("M20", "the village"),
    "dingle": ("L8", "Park Road"),
    "dinnington": ("S25", "the high street"),
    "doncaster": ("DN1", "the minster"),
    "dronfield": ("S18", "the civic centre"),
    "droylsden": ("M43", "Market Street"),
    "dukinfield": ("SK16", "the town hall"),
    "eccles": ("M30", "the precinct"),
    "elland": ("HX5", "the bridge"),
    "ellesmere-port-town": ("CH65", "the port"),
    "failsworth": ("M35", "Oldham Road"),
    "farnworth": ("BL4", "Market Street"),
    "fazakerley": ("L9", "Longmoor Lane"),
    "featherstone": ("WF7", "Station Lane"),
    "fleetwood": ("FY7", "the docks"),
    "flint": ("CH6", "the castle"),
    "formby": ("L37", "Chapel Lane"),
    "fulwood": ("S10", "Manchester Road"),
    "fylde": ("FY8", "the coastal parishes"),
    "garforth": ("LS25", "Main Street"),
    "garston": ("L19", "Speke Road"),
    "glossop": ("SK13", "Norfolk Square"),
    "golborne": ("WA3", "High Street"),
    "great-harwood": ("BB6", "Queen Street"),
    "great-sankey": ("WA5", "the A57"),
    "guiseley": ("LS20", "Otley Road"),
    "hale": ("WA15", "the village"),
    "halewood": ("L26", "Leathers Lane"),
    "halifax": ("HX1", "the Piece Hall"),
    "harrogate": ("HG1", "Parliament Street"),
    "haslingden": ("BB4", "Deardengate"),
    "hawarden": ("CH5", "the Gladstone side"),
    "haxby": ("YO32", "The Village"),
    "haydock": ("WA11", "the industrial estate"),
    "hazel-grove": ("SK7", "the A6"),
    "heanor": ("DE75", "the market place"),
    "heckmondwike": ("WF16", "the market"),
    "heswall": ("CH60", "Telegraph Road"),
    "heysham": ("LA3", "the port"),
    "heywood": ("OL10", "Market Street"),
    "hindley": ("WN2", "Market Street"),
    "hollinwood": ("OL8", "the A62"),
    "horbury": ("WF4", "High Street"),
    "horsforth": ("LS18", "Town Street"),
    "horwich": ("BL6", "the Rivington side"),
    "hoyland-nether": ("S74", "the market"),
    "huddersfield": ("HD1", "the station"),
    "huyton": ("L36", "Derby Road"),
    "hyde": ("SK14", "the market"),
    "ilkley": ("LS29", "The Grove"),
    "ince-in-makerfield": ("WN1", "the industrial edge"),
    "irlam": ("M44", "the station"),
    "kearsley": ("BL4", "the Irwell"),
    "keighley": ("BD21", "the market"),
    "kendal": ("LA9", "the yards"),
    "kidsgrove": ("ST7", "the station"),
    "kippax": ("LS25", "High Street"),
    "kirk-sandall": ("DN3", "the Don"),
    "kirkby": ("L32", "the civic centre"),
    "kirkby-in-ashfield": ("NG17", "the station"),
    "knaresborough": ("HG5", "the castle"),
    "knottingley": ("WF11", "the river"),
    "knotty-ash": ("L14", "East Prescot Road"),
    "knutsford": ("WA16", "King Street"),
    "lancaster": ("LA1", "the castle"),
    "leeds": ("LS1", "the station"),
    "leek": ("ST13", "the market"),
    "lees": ("OL4", "High Street"),
    "leigh": ("WN7", "the market"),
    "leyland": ("PR25", "Hough Lane"),
    "litherland": ("L21", "Linacre Road"),
    "little-hulton": ("M38", "Manchester Road"),
    "little-lever": ("BL3", "Market Street"),
    "littleborough": ("OL15", "the canal"),
    "liverpool": ("L1", "the waterfront"),
    "liversedge": ("WF15", "the A62"),
    "lofthouse": ("WF3", "Leeds Road"),
    "longton": ("ST3", "the Strand"),
    "lymm": ("WA13", "the cross"),
    "lytham-st-annes": ("FY8", "the green"),
    "macclesfield": ("SK10", "the market"),
    "maghull": ("L31", "the station"),
    "maltby": ("S66", "High Street"),
    "manchester": ("M1", "Piccadilly"),
    "mansfield": ("NG18", "the market"),
    "mansfield-woodhouse": ("NG19", "Station Street"),
    "market-drayton": ("TF9", "the market"),
    "marple": ("SK6", "the canal"),
    "matlock": ("DE4", "the river"),
    "mexborough": ("S64", "High Street"),
    "middleton": ("M24", "the market"),
    "middlewich": ("CW10", "the canal"),
    "milnrow": ("OL16", "the east side"),
    "mirfield": ("WF14", "the station"),
    "mold": ("CH7", "High Street"),
    "morecambe": ("LA4", "the promenade"),
    "moreton": ("CH46", "the cross"),
    "morley": ("LS27", "Queen Street"),
    "mossley": ("OL5", "the mill"),
    "mossley-hill": ("L18", "Rose Lane"),
    "nantwich": ("CW5", "the square"),
    "nelson": ("BB9", "the market"),
    "neston": ("CH64", "the cross"),
    "new-mills": ("SK22", "the torrs"),
    "newcastle-under-lyme": ("ST5", "the high street"),
    "newport": ("TF10", "the high street"),
    "newton-le-willows": ("WA12", "the station"),
    "northallerton": ("DL7", "the high street"),
    "northwich": ("CW9", "the viaduct"),
    "old-swan": ("L13", "St Oswalds Street"),
    "oldham": ("OL1", "the market"),
    "ormskirk": ("L39", "the clock tower"),
    "ossett": ("WF5", "the market"),
    "otley": ("LS21", "the market"),
    "padiham": ("BB12", "Burnley Road"),
    "pendlebury": ("M27", "the industrial edge"),
    "penistone": ("S36", "the market"),
    "pinxton": ("NG16", "the wharf"),
    "pontefract": ("WF8", "the castle"),
    "poulton-le-fylde": ("FY6", "the market"),
    "poynton": ("SK12", "the green"),
    "prenton": ("CH43", "the village"),
    "prescot": ("L34", "Eccleston Street"),
    "prestatyn": ("LL19", "the high street"),
    "preston": ("PR1", "the flag market"),
    "prestwich": ("M25", "Bury New Road"),
    "pudsey": ("LS28", "the market"),
    "radcliffe": ("M26", "the market"),
    "rainhill": ("L35", "the village"),
    "ramsbottom": ("BL0", "the market"),
    "rastrick": ("HD6", "the common"),
    "rawmarsh": ("S62", "the high street"),
    "rawtenstall": ("BB4", "the market"),
    "rhosllannerchrugog": ("LL14", "the high street"),
    "ripley": ("DE5", "the market"),
    "ripon": ("HG4", "the cathedral"),
    "rochdale": ("OL11", "the town hall"),
    "romiley": ("SK6", "the station"),
    "rotherham": ("S60", "the minster"),
    "royton": ("OL2", "the east side"),
    "runcorn": ("WA7", "the old town"),
    "saddleworth": ("OL3", "the valleys"),
    "sale": ("M33", "the precinct"),
    "salford": ("M3", "the quays"),
    "sandbach": ("CW11", "the cobbles"),
    "selby": ("YO8", "the abbey"),
    "shaw": ("OL2", "the west side"),
    "sheffield": ("S1", "the station"),
    "shipley": ("BD18", "the market"),
    "shirebrook": ("NG20", "the market"),
    "skelmersdale": ("WN8", "the concourse"),
    "skipton": ("BD23", "the castle"),
    "south-elmsall": ("WF9", "the station"),
    "southport": ("PR8", "Lord Street"),
    "speke": ("L24", "the industrial estate"),
    "st-helens": ("WA10", "the town hall"),
    "stafford": ("ST16", "the market"),
    "stalybridge": ("SK15", "the canal"),
    "staveley": ("S43", "the market"),
    "stockport": ("SK1", "the viaduct"),
    "stocksbridge": ("S36", "the steelworks"),
    "stoke-on-trent": ("ST1", "the station"),
    "stone": ("ST15", "the high street"),
    "stretford": ("M32", "the mall"),
    "sutton-in-ashfield": ("NG17", "the market"),
    "swinton": ("M27", "the civic centre"),
    "tameside": ("OL6", "the borough offices"),
    "timperley": ("WA15", "the station"),
    "todmorden": ("OL14", "the market"),
    "trafford": ("M16", "the borough side"),
    "tyldesley": ("M29", "Elliott Street"),
    "ulverston": ("LA12", "the market"),
    "uppermill": ("OL3", "the high street"),
    "urmston": ("M41", "the station"),
    "uttoxeter": ("ST14", "the market"),
    "wakefield": ("WF1", "the cathedral"),
    "walkden": ("M28", "the shopping centre"),
    "wallasey": ("CH44", "Liscard"),
    "warrington": ("WA1", "the market"),
    "wath-upon-dearne": ("S63", "the high street"),
    "west-derby": ("L12", "the village"),
    "westhoughton": ("BL5", "Market Street"),
    "wetherby": ("LS22", "the market"),
    "whiston": ("L35", "the hospital district"),
    "whitchurch": ("SY13", "the high street"),
    "whitefield": ("M45", "Bury New Road"),
    "widnes": ("WA8", "the town centre"),
    "wigan": ("WN1", "the market"),
    "wilmslow": ("SK9", "the station"),
    "winsford": ("CW7", "the high street"),
    "withington": ("M20", "the hospital district"),
    "wombwell": ("S73", "the high street"),
    "woolton": ("L25", "the village"),
    "worksop": ("S80", "the market"),
    "worsley": ("M28", "the green"),
    "wrexham": ("LL11", "the high street"),
    "wythenshawe": ("M22", "the civic centre"),
    "yeadon": ("LS19", "the high street"),
    "york": ("YO1", "the minster"),
}

# Straight-line miles where the allowlist left gm_forced rows blank.
FORCED_MILES = {
    "withington": (4.2, 27.0),
    "worsley": (7.1, 18.4),
    "wythenshawe": (8.0, 31.0),
    "chorlton": (3.6, 26.0),
    "cadishead": (8.8, 22.0),
    "heywood": (8.5, 16.0),
    "milnrow": (10.2, 14.5),
    "mossley": (10.4, 18.0),
    "pendlebury": (4.8, 18.5),
    "romiley": (8.2, 26.5),
    "shaw": (8.8, 16.8),
    "tameside": (7.5, 22.0),
    "trafford": (4.5, 26.0),
}

SLUGS = {
    # slug: (name, service, cluster, gas, detail)
    "plasterers": ("Plasterers", "plastering", "plaster", False, "a crew moving through more than one room in a let, including a ceiling that has lost its key"),
    "plasterer": ("Plasterer", "plastering", "plaster", False, "one tradesperson on a single ceiling or a patch where a leak was dried out"),
    "plasterer-near-me": ("Plasterer near me", "plastering", "plaster", False, "a near-me search that is still booked from the Stockport diary, not a claim that a van is already on the street"),
    "plastering": ("Plastering", "plastering", "plaster", False, "the wet-plaster sequence from bonding coat to a finish that can take paint"),
    "plastering-near-me": ("Plastering near me", "plastering", "plaster", False, "a near-me plastering search for a room that has to be empty while the skim dries"),
    "plastering-company": ("Plastering company", "plastering", "plaster", False, "a company enquiry that wants a written scope for several rooms, not a day-rate guess"),
    "plastering-contractor": ("Plastering contractor", "plastering", "plaster", False, "a contractor enquiry from a main contractor who needs the skim after first fix"),
    "dryliners": ("Dryliners", "dry-lining", "dryline", False, "a drylining crew for metal stud partitions and a taped joint ready for decoration"),
    "dryliner": ("Dryliner", "dry-lining", "dryline", False, "one dryliner boarding a single wall or a small stud run"),
    "dry-lining": ("Dry lining", "dry-lining", "dryline", False, "boarding onto studs or dabs, with joints taped before decoration"),
    "drylining": ("Drylining", "dry-lining", "dryline", False, "the same boarding trade under the one-word spelling landlords type into search"),
    "dry-lining-company": ("Dry lining company", "dry-lining", "dryline", False, "a company brief for a whole floor of partitions, not a single patch board"),
    "drylining-contractor": ("Drylining contractor", "dry-lining", "dryline", False, "a contractor slot inside a refurbishment where the boards follow the services"),
    "skimming": ("Skimming", "plastering", "plaster", False, "a skim coat over sound plasterboard or an older wall that is still flat enough"),
    "wall-skimming": ("Wall skimming", "plastering", "plaster", False, "walls only, leaving the ceiling out of the scope when it is still sound"),
    "ceiling-skimming": ("Ceiling skimming", "plastering", "plaster", False, "a ceiling skim, including over boards that were fixed after a leak"),
    "plasterboard-installation": ("Plasterboard installation", "dry-lining", "dryline", False, "new boards on a ceiling or a stud, before any skim is priced"),
    "dot-and-dab": ("Dot and dab", "dry-lining", "dryline", False, "boards fixed with dabs to a masonry wall, where the wall is dry and flat enough"),
    "bricklayers": ("Bricklayers", "brickwork", "brick", False, "a bricklaying crew for a garden wall or a small opening that needs making good"),
    "bricklayer": ("Bricklayer", "brickwork", "brick", False, "one bricklayer on a pier, a threshold or a short run of facing brick"),
    "bricklayer-near-me": ("Bricklayer near me", "brickwork", "brick", False, "a near-me bricklayer search that is still scheduled from Stockport once the bond and the access are known"),
    "brickwork": ("Brickwork", "brickwork", "brick", False, "new facing brick, alterations and making good around an opening"),
    "brickwork-company": ("Brickwork company", "brickwork", "brick", False, "a company enquiry for a wall that needs a drawing, a bond and a written scope"),
    "garden-wall-building": ("Garden wall building", "brickwork", "brick", False, "a new garden wall, including the footing conversation before any bricks are ordered"),
    "garden-wall-building-company": ("Garden wall building company", "brickwork", "brick", False, "a company search for a garden wall, with height, piers and who owns the boundary written down first"),
    "garden-wall": ("Garden wall", "brickwork", "brick", False, "an existing garden wall that needs a look before anyone talks about rebuilding it"),
    "garden-wall-construction": ("Garden wall construction", "brickwork", "brick", False, "construction of a garden wall from the footing up, not a patch on a leaning pier"),
    "garden-wall-repair": ("Garden wall repair", "brickwork", "brick", False, "a repair where a pier has cracked or coping stones have come loose"),
    "brickwork-repairs": ("Brickwork repairs", "brickwork", "brick", False, "local brick repairs, stitching a crack or replacing spalled faces"),
    "repointing": ("Repointing", "brickwork", "brick", False, "raking out failed joints and packing them again in a mortar that suits the brick"),
    "brick-repointing": ("Brick repointing", "brickwork", "brick", False, "repointing facing brick, including a sample joint before the whole elevation is opened"),
    "fire-door-installer": ("Fire door installer", "fire-doors", "firedoor", False, "fitting a fire door set, leaf, frame, closer and seals, to the rating the opening needs"),
    "fire-door-installation": ("Fire door installation", "fire-doors", "firedoor", False, "the installation visit itself, from the opening size to the closer and the gaps"),
    "fire-door-fitters": ("Fire door fitters", "fire-doors", "firedoor", False, "fitters who hang the leaf and set the closer, rather than a paper survey only"),
    "fire-doors": ("Fire doors", "fire-doors", "firedoor", False, "the door sets themselves, FD30 or FD60, and what has to be true of the frame and the seals"),
    "fire-door-survey": ("Fire door survey", "fire-doors", "firedoor", False, "a survey of leaf, frame, gaps, closer, seals and signage, written as findings"),
    "fire-door-inspection": ("Fire door inspection", "fire-doors", "firedoor", False, "an inspection pass that records what is there, without inventing a scheme badge"),
    "fire-door-replacement": ("Fire door replacement", "fire-doors", "firedoor", False, "taking one door set out and hanging another that matches the opening and the rating"),
    "fire-door-repair": ("Fire door repair", "fire-doors", "firedoor", False, "a repair to a closer, a seal or a leaf that is still the right door for the opening"),
    "fd30-fire-door": ("FD30 fire door", "fire-doors", "firedoor", False, "an FD30 door set, which is a 30-minute fire-resistance claim for that product, not a company badge"),
    "fd60-fire-door": ("FD60 fire door", "fire-doors", "firedoor", False, "an FD60 door set, which is a 60-minute fire-resistance claim for that product, not a company badge"),
    "boiler-replacement": ("Boiler replacement", "gas-systems", "gas", True, "removing one boiler and fitting another, with the flue and the system written into the scope"),
    "boiler-repair": ("Boiler repair", "gas-systems", "gas", True, "a repair after a lockout, a leak or a loss of heat, once the appliance is identified"),
    "boiler-installation": ("Boiler installation", "gas-systems", "gas", True, "a first installation, including where the flue can terminate and how the condensate runs"),
    "boiler-service": ("Boiler service", "gas-systems", "gas", True, "a service visit that checks the appliance, the flue and the safety devices"),
    "boiler-engineer": ("Boiler engineer", "gas-systems", "gas", True, "the person who attends the boiler, booked as a visit rather than as a company registration claim"),
    "boiler-engineer-near-me": ("Boiler engineer near me", "gas-systems", "gas", True, "a near-me boiler search that is still arranged from Stockport once the make, model and postcode are known"),
    "new-boiler": ("New boiler", "gas-systems", "gas", True, "a new appliance, combi or otherwise, chosen after the heat loss and the flue route are known"),
    "combi-boiler-installation": ("Combi boiler installation", "gas-systems", "gas", True, "installing a combi where the cold main and the flue route can support it"),
    "combi-boiler-replacement": ("Combi boiler replacement", "gas-systems", "gas", True, "swapping an older combi, including whether the flue and the condensate still comply"),
    "gas-engineer": ("Gas engineer", "gas-systems", "gas", True, "a gas visit for an appliance or a let, arranged as work carried out by Gas Safe registered engineers"),
    "gas-engineer-near-me": ("Gas engineer near me", "gas-systems", "gas", True, "a near-me gas search with the postcode and the appliance list sent before a date is offered"),
    "gas-safe-engineer": ("Gas safety engineer", "gas-systems", "gas", True, "a search that uses Gas Safe wording. The visit is carried out by Gas Safe registered engineers. This company does not print a registration badge"),
    "24-hour-gas-engineer": ("24 hour gas engineer", "gas-systems", "gas", True, "an out-of-hours gas enquiry. Attendance still depends on the diary and the address, and the quote stays price on application"),
    "emergency-gas-engineer": ("Emergency gas engineer", "gas-systems", "gas", True, "an urgent gas enquiry, such as a smell of gas reported to the emergency service first, then a follow-up visit once the site is safe"),
    "gas-safety-certificate": ("Gas safety certificate", "gas-systems", "gas", True, "the landlord gas safety record, often still called a certificate, listing appliances and flues that were checked"),
    "cp12": ("CP12", "gas-systems", "gas", True, "the landlord gas safety record people still call a CP12"),
    "cp12-certificate": ("CP12 certificate", "gas-systems", "gas", True, "the CP12 name on a landlord file, meaning the current gas safety record for that let"),
    "landlord-gas-safety-certificate": ("Landlord gas safety certificate", "gas-systems", "gas", True, "the record a landlord or agent files for a let that has gas appliances or flues"),
    "landlord-gas-certificate": ("Landlord gas certificate", "gas-systems", "gas", True, "the shorter name for the same landlord gas safety record"),
    "gas-certificate": ("Gas certificate", "gas-systems", "gas", True, "a gas certificate search that, for a let, means the landlord gas safety record"),
    "annual-gas-safety-certificate": ("Annual gas safety certificate", "gas-systems", "gas", True, "the annual landlord gas safety record, booked before the previous record runs out"),
    "boiler-breakdown": ("Boiler breakdown", "gas-systems", "gas", True, "a breakdown with no heat or no hot water, diagnosed before anyone promises a repair"),
    "boiler-breakdown-repair": ("Boiler breakdown repair", "gas-systems", "gas", True, "the repair that follows a breakdown, once the fault and the parts position are known"),
    "builders": ("Builders", "building-maintenance", "build", False, "a general building crew for a small extension, a strip-out or making good after another trade"),
    "builder": ("Builder", "building-maintenance", "build", False, "one builder for a defined piece of making good, not an open-ended handy visit"),
    "builder-near-me": ("Builder near me", "building-maintenance", "build", False, "a near-me builder search that still starts with photos and the Stockport diary"),
    "building-company": ("Building company", "building-maintenance", "build", False, "a company enquiry for a scoped building job with a start note and a finish note"),
    "building-contractor": ("Building contractor", "building-maintenance", "build", False, "a contractor role inside a larger job, with the interface to other trades written down"),
    "general-builder": ("General builder", "building-maintenance", "build", False, "general building across a few trades on one address, each line still scoped"),
    "general-builders": ("General builders", "building-maintenance", "build", False, "the plural search for a general building crew on a house or a small commercial unit"),
    "property-maintenance": ("Property maintenance", "building-maintenance", "build", False, "planned or reactive maintenance on a let or a managed building"),
    "building-maintenance": ("Building maintenance", "building-maintenance", "build", False, "maintenance of the fabric, booked as a list of defects rather than a blank call-out"),
    "home-renovation": ("Home renovation", "renovation", "build", False, "a home renovation split into phases so plaster, joinery and finishes are not one vague line"),
    "house-renovation": ("House renovation", "renovation", "build", False, "a house renovation with the rooms, the occupancy and the sequence written before a date"),
    "house-extension": ("House extension", "extensions", "build", False, "an extension enquiry that starts with planning, the opening in the existing wall, and who is designing it"),
    "rear-extension": ("Rear extension", "extensions", "build", False, "a rear extension, including how the kitchen stays usable and where the foundations sit"),
    "side-extension": ("Side extension", "extensions", "build", False, "a side extension, including the boundary, the gutter and the existing side window"),
    "loft-conversion": ("Loft conversion", "loft-conversions", "build", False, "a loft conversion enquiry covering stairs, structure and fire separation, not a storage boarding job"),
    "garage-conversion": ("Garage conversion", "extensions", "build", False, "turning a garage into a room, including the floor, the insulation and the new opening"),
    "joiners": ("Joiners", "joinery", "joinery", False, "a joinery crew for doors, linings, stairs or fitted timber"),
    "joiner": ("Joiner", "joinery", "joinery", False, "one joiner on a door, a sash repair or a short run of lining"),
    "joiner-near-me": ("Joiner near me", "joinery", "joinery", False, "a near-me joiner search with photos of the opening sent before the visit"),
    "joinery": ("Joinery", "joinery", "joinery", False, "purpose-made or replacement joinery, measured on site"),
    "joinery-company": ("Joinery company", "joinery", "joinery", False, "a company brief for several doors or a stair, with drawings or a measured survey first"),
    "carpenters": ("Carpenters", "carpentry", "joinery", False, "carpentry for studs, door casings and first-fix timber"),
    "carpenter": ("Carpenter", "carpentry", "joinery", False, "one carpenter on first fix or a single door casing"),
    "carpenter-near-me": ("Carpenter near me", "carpentry", "joinery", False, "a near-me carpenter search that is booked from Stockport once the timber job is clear"),
    "damp-proofing": ("Damp proofing", "damp-proofing", "damp", False, "a damp enquiry that starts by separating condensation, penetration and rising damp"),
    "damp-proofing-company": ("Damp proofing company", "damp-proofing", "damp", False, "a company search for damp work, with the cause written before any injection or tanking is discussed"),
    "rising-damp-treatment": ("Rising damp treatment", "damp-proofing", "damp", False, "treatment aimed at rising damp, only after the survey says that is the cause"),
    "damp-specialist": ("Damp survey", "damp-proofing", "damp", False, "a damp survey that names the cause. The page uses survey wording rather than a badge"),
    "rendering": ("Rendering", "rendering", "render", False, "an external render system, from the substrate to the finish coat"),
    "external-rendering": ("External rendering", "rendering", "render", False, "render on the outside of the building, including beads, sills and how the rain runs off"),
    "renderers": ("Renderers", "rendering", "render", False, "a rendering crew for an elevation, not a patch of masonry paint"),
    "monocouche-render": ("Monocouche render", "rendering", "render", False, "a through-colour monocouche coat, scraped back, on a substrate that can take it"),
    "plumber": ("Plumber", "plumbing", "plumb", False, "a plumbing visit for a leak, a tap, a soil pipe or a bathroom connection"),
    "plumbers": ("Plumbers", "plumbing", "plumb", False, "the plural search for plumbing on a let, a house or a small commercial unit"),
    "plumber-near-me": ("Plumber near me", "plumbing", "plumb", False, "a near-me plumber search with the leak or the fitting described before anyone travels"),
    "emergency-plumber": ("Emergency plumber", "plumbing", "plumb", False, "an urgent plumbing leak. The attendance is still quoted price on application once the address is known"),
}

CLUSTERS = {
    "plaster": [
        "Skim and bonding coats are priced after the wall is seen. A blown ceiling is not the same job as a flat board.",
        "Rooms have to be empty enough for boards, angles and a clean finish. Furniture left in the middle of the room changes the time.",
        "Drying time is part of the scope. Paint is not booked for the same afternoon as a fresh skim.",
        "Patches after a leak wait until the substrate is dry. Skimming over a wet stain traps the problem.",
        "Angles, window reveals and the joint with the ceiling are written into the scope when they are part of the room.",
        "External corners get a bead where the wall is exposed to knocks, such as a hallway or a stair.",
        "The finish is what decoration needs. A skim that is still ridged will show through paint.",
        "Waste plaster leaves the address with the visit when that was in the quote. It is not assumed.",
    ],
    "dryline": [
        "Metal stud or timber stud is chosen when the partition height and the services are known.",
        "Dot and dab needs a wall that is dry. A damp wall is a different job and is not boarded over.",
        "Boards are fixed, then joints are taped. Decoration sits after that, not instead of it.",
        "Socket and switch cuts are marked from the drawing or from the boxes that are already there.",
        "A ceiling board after a leak includes how the joists will hold it. The stain alone is not the scope.",
        "Acoustic layers are only included when the brief asks for them. A standard partition is not sold as acoustic.",
        "Door openings in a new partition need a lining and a head. That is a separate line from the boarding.",
        "Fire-rated boarding is specified when the partition is part of a fire line. Ordinary board is not described as fire rated.",
    ],
    "brick": [
        "A garden wall starts with height, piers and the footing. A photo of a leaning pier is not a design.",
        "Facing brick is matched as closely as the yard can supply. An exact historic match is not promised from a thumbnail.",
        "Repointing opens the joint, cleans it and packs a mortar that suits the brick. A hard mortar on a soft brick is avoided.",
        "Coping and damp course position are part of a new wall. They are not an optional extra discovered at the end.",
        "Openings that were cut for a window or a door are made good to the bond, not filled with mortar only.",
        "Boundary walls need the owner and the neighbour question settled before the footing is dug.",
        "Spalled faces are cut out and replaced where the brick has failed. Painting the spall is not the repair.",
        "Access for materials, including where the sand sits, is agreed so a narrow ginnel is not a surprise.",
    ],
    "firedoor": [
        "A fire door visit looks at the leaf, the frame, the closer, the gaps and the seals. One of those missing is a finding.",
        "FD30 and FD60 name a fire-resistance period for a door set. They are not a company scheme badge.",
        "Replacement means the new set suits the opening and the rating the building already calls for.",
        "A closer that does not latch is recorded. The page does not invent a certificate number for the closer.",
        "Gaps and smoke seals are measured and written down. A visual glance from the corridor is not a survey.",
        "Signage on a fire door is noted when it is missing. The note is a finding, not a new fire strategy.",
        "Glazing in a fire door has to match the door set. Ordinary glass is not described as part of an FD30 leaf.",
        "This page does not claim BAFE or NSI. The record is the inspection or the installation note for that opening.",
    ],
    "gas": [
        "The appliance make and model, and whether the property is a let, are collected before a date is fixed.",
        "The flue route and the ventilation are part of a boiler visit. They are not assumed from a kitchen photo.",
        "A breakdown is diagnosed first. Parts are only ordered once the fault is identified.",
        "A landlord gas safety record lists the appliances and flues that were checked. It is not a blank pass.",
        "Combi, system and regular boilers are not interchangeable in the quote. The existing system is named.",
        "Condensate, gas pipe size and the cold main are checked when a new combi is discussed.",
        "Out-of-hours attendance is only offered when the diary and the address allow it. It is not a promise on the page.",
        "If an appliance is unsafe, that stays on the record. The visit does not turn a fail into a pass.",
    ],
    "build": [
        "A building enquiry is split into phases. Strip-out, structure, plaster and finish are not one line.",
        "An extension starts with who is designing it and whether planning or permitted development applies. This page does not grant consent.",
        "A loft conversion is stairs, structure and fire separation. Boarding a loft for storage is a different job.",
        "A garage conversion includes the floor, the insulation and the new front treatment. The old door opening is part of the scope.",
        "Making good after another trade is measured. It is not an open invitation to rebuild the room.",
        "Occupied houses need a sequence so a kitchen or a bathroom is not out of use without a date.",
        "Party walls and boundaries are flagged when the work is close to them. The page does not give legal advice.",
        "Waste and scaffold, when needed, are lines in the quote. They are not discovered after the start.",
    ],
    "joinery": [
        "Doors are measured in the opening. A catalogue leaf that does not fit the frame is not forced in.",
        "Linings, architrave and the stop are part of a door job when the old ones have been cut about.",
        "Stair work is specified from a measured survey. A photo of a creak is not a new stair.",
        "First-fix timber follows the partitions. It is booked when the studs exist, not before.",
        "Sash repairs name the cords, the weights or the draught strips that are actually in the brief.",
        "Fitted timber in a rental is scoped so it can be maintained. Bespoke is not assumed.",
        "Fire door joinery sits with the fire door pages when the leaf is a fire door set. Ordinary joinery is not described as a fire door.",
        "Finish carpentry is after plaster is dry. Skirting on a wet wall will move.",
    ],
    "damp": [
        "The first question is the cause: condensation, penetrating damp or rising damp. They are not the same treatment.",
        "A tide mark alone does not prove rising damp. The survey says what was tested.",
        "Penetrating damp from a gutter, a pointing failure or a flat roof is a building defect first.",
        "Condensation in a let is often ventilation and heating pattern. It is not automatically an injection job.",
        "Plaster that has failed because of salts is replaced only when the cause is controlled.",
        "External ground levels and air bricks are looked at before anyone talks about a chemical damp course.",
        "The report names the rooms and the readings or the observations. It is not a one-word diagnosis.",
        "Any following plaster or render is a separate line, quoted after the damp scope is agreed.",
    ],
    "render": [
        "The substrate is checked before a render system is named. Paint over a failed render is not a render job.",
        "Beads, sills and stops are in the scope when the elevation needs them.",
        "A monocouche coat is through-colour and scraped. It is not a sand-and-cement scratch coat with masonry paint.",
        "Cracks are read first. A crack that is structural is not skimmed over and called render.",
        "Rainwater discharge is part of the elevation. A new render under a broken gutter will stain.",
        "Colour is agreed from a sample. A screen photo is not the specification.",
        "Access, including whether a tower or scaffold is required, is a line in the quote.",
        "Windows and doors are protected. Masking is part of the visit, not a favour.",
    ],
    "plumb": [
        "A leak is isolated before parts are discussed. The stop tap position is asked for at the enquiry.",
        "Taps, wastes and traps are ordinary plumbing. A gas appliance is not booked on this page.",
        "Soil-pipe and waste falls are checked when a bathroom or a kitchen is being altered.",
        "An urgent leak is still quoted price on application once the address and the source are known.",
        "Landlord voids list the fittings that failed. The visit does not become a full bathroom unless that was scoped.",
        "Outside taps and overflows are small jobs only when access and the pipe route are clear.",
        "Hot water cylinders and vents are described as plumbing when there is no gas work in the brief.",
        "Making good tiles or plaster after a pipe repair is a separate line if the surface has to be opened.",
    ],
}

SECTIONS = [
    "What the enquiry needs",
    "Scope before a date",
    "Who the page is for",
    "Paperwork left on site",
    "Access and occupancy",
    "Diary from Stockport",
    "Manchester and Burnley rings",
    "How the quote is given",
]


def load_towns() -> list[dict]:
    rows = list(csv.DictReader((LOCKS / "dual-ring-269.csv").open()))
    if len(rows) != 269:
        raise SystemExit(f"expected 269 towns, got {len(rows)}")
    towns = []
    seen_district = set()
    for i, row in enumerate(rows, start=1):
        slug = row["slug"].strip()
        if slug not in OUTWARD:
            raise SystemExit(f"missing outward code for {slug}")
        code, landmark = OUTWARD[slug]
        district = f"{code} {landmark}"
        if district in seen_district:
            raise SystemExit(f"district collision {district}")
        seen_district.add(district)
        pop_raw = (row.get("population") or "").strip()
        population = int(pop_raw) if pop_raw else None
        def miles(key: str) -> float | None:
            raw = (row.get(key) or "").strip()
            if raw:
                return float(raw)
            forced = FORCED_MILES.get(slug)
            if not forced:
                return None
            return forced[0] if key == "mi_manchester" else forced[1]
        county = (row.get("county") or "").strip()
        if slug in GM and not county:
            county = "Greater Manchester"
        if slug in GM:
            county = county or "Greater Manchester"
        towns.append({
            "slug": slug,
            "name": row["name"].strip(),
            "county": county or "the dual ring",
            "population": population,
            "mi_manchester": miles("mi_manchester"),
            "mi_burnley": miles("mi_burnley"),
            "circles": (row.get("circles") or "").strip(),
            "outward": code,
            "district": district,
            "row": i,
            "gm": slug in GM,
        })
    return towns


def load_slugs() -> list[dict]:
    order = [line.strip() for line in (LOCKS / "building-services-p0.txt").read_text().splitlines() if line.strip()]
    if len(order) != 100 or len(set(order)) != 100:
        raise SystemExit("P0 file must contain 100 unique slugs")
    missing = [slug for slug in order if slug not in SLUGS]
    extra = [slug for slug in SLUGS if slug not in order]
    if missing or extra:
        raise SystemExit(f"slug map mismatch missing={missing} extra={extra}")
    by_cluster: dict[str, list[str]] = {}
    out = []
    for slug in order:
        name, service, cluster, gas, detail = SLUGS[slug]
        by_cluster.setdefault(cluster, []).append(slug)
        angle = (
            f"The {name} page is about {detail}. "
            f"It is filed as dual-ring intent {order.index(slug) + 1} and does not reuse the next intent's scope."
        )
        out.append({
            "slug": slug,
            "name": name,
            "service": service,
            "cluster": cluster,
            "gas": gas,
            "angle": angle,
            "related": slug,
        })
    for row in out:
        peers = by_cluster[row["cluster"]]
        idx = peers.index(row["slug"])
        row["related"] = peers[(idx + 1) % len(peers)]
    return out


def write_svgs() -> None:
    IMG.mkdir(parents=True, exist_ok=True)
    palette = {
        "plaster": ("#f4efe6", "#c4b49a", "#8c7355"),
        "dryline": ("#e7eef2", "#9eb4c2", "#3d5a6c"),
        "brick": ("#f6ebe4", "#c46b4a", "#6e3b32"),
        "firedoor": ("#f3e8e4", "#8f3d3d", "#2c2c2c"),
        "gas": ("#e7f0ea", "#3d7a62", "#1d3d36"),
        "build": ("#f3eee4", "#b08948", "#3e4a3a"),
        "joinery": ("#f6f0e6", "#a67c52", "#4a3424"),
        "damp": ("#e8eef1", "#6d8b9a", "#24343c"),
        "render": ("#f4f1ea", "#d9d0c1", "#6b6458"),
        "plumb": ("#e7f1f4", "#3d7c8c", "#1c3a42"),
    }
    for cluster, colours in palette.items():
        for n, colour in enumerate(colours, start=1):
            svg = f'''<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">
<rect width="1200" height="800" fill="#f7f4ef"/>
<rect x="80" y="70" width="1040" height="660" rx="28" fill="{colour}"/>
<rect x="160" y="160" width="420" height="280" fill="#fff" opacity="0.9"/>
<rect x="640" y="160" width="380" height="180" fill="#061828" opacity="0.85"/>
<rect x="160" y="480" width="860" height="28" fill="#ff6b00"/>
<text x="160" y="640" font-family="Georgia, serif" font-size="42" fill="#061828">{cluster} {n}</text>
</svg>
'''
            (IMG / f"{cluster}-{n}.svg").write_text(svg)


def main() -> None:
    towns = load_towns()
    slugs = load_slugs()
    write_svgs()
    images = {
        cluster: [f"/assets/images/building-dual/{cluster}-{n}.svg" for n in (1, 2, 3)]
        for cluster in CLUSTERS
    }
    payload = {
        "nap": "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE",
        "phone": "07517806082",
        "gas_duty": "The visit is carried out by Gas Safe registered engineers.",
        "gas_denial": "iComply does not claim a Gas Safe registration.",
        "arrange": "Where this trade needs a ticket the Stockport workshop does not hold, the visit is arranged and carried out by a qualified tradesperson booked for that scope.",
        "sections": SECTIONS,
        "clusters": {
            key: {"facts": facts, "images": images[key]}
            for key, facts in CLUSTERS.items()
        },
        "slugs": slugs,
        "towns": towns,
    }
    OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n")
    print(f"wrote {OUT} slugs={len(slugs)} towns={len(towns)} gm={sum(1 for t in towns if t['gm'])}")


if __name__ == "__main__":
    main()
