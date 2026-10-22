/**
 * CLOSE-Q1 (2026-10-19): town-specific depth for edge x town pages.
 *
 * Lean town pages (real town facts present) measured 540-700 prose words on
 * live, under the 800 bar. This block adds roughly 250-350 words built from
 * the town's own published data (ONS Census 2021 / Scotland's Census 2022
 * council tenure and dwelling mix, postcode districts, nearest covered towns,
 * straight-line distance from the Stockport yard), plus the duty that applies
 * to the service family in that nation. Variants and paragraph order are
 * picked by a town seed so neighbouring towns do not share sentence runs.
 *
 * Rules: price on application only where there is no list price (G9: EICR from £160 pages say "from £160", never POA), no attendance-time promise, no invented
 * statistic, reviews or accreditation. Gas wording is only "carried out by
 * Gas Safe registered engineers".
 */
import geo from "../../website/data/town-geo-slim.json" with { type: "json" };
import laCensus from "./la-census-slim.json" with { type: "json" };

/**
 * Two dual-ring slugs are whole metropolitan boroughs with no single-town row
 * in town-geo-slim.json (built from postcodes.io town centroids). Same row
 * shape: council, county, postcode districts, wards, constituency, lat, lng.
 * Coordinates are the approximate centre of each borough; districts are the
 * main ones inside it.
 */
export const BOROUGH_GEO = {
  trafford: ["Trafford", "Greater Manchester", ["M32", "M33", "M41", "WA14", "WA15"], [], "", 53.42, -2.35, null, null],
  tameside: ["Tameside", "Greater Manchester", ["OL6", "OL7", "SK14", "SK15", "SK16", "M34", "M43"], [], "", 53.48, -2.07, null, null],
};
export function geoRow(slug) {
  return geo[slug] || BOROUGH_GEO[slug] || null;
}

const YARD = { lat: 53.3925, lng: -2.1265 };
const DIRS = ["north", "north-east", "east", "south-east", "south", "south-west", "west", "north-west"];

function esc(v) {
  return String(v).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
function hash(s) {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
  h = Math.imul(h ^ (h >>> 15), 0x2c1b3c6d) >>> 0;
  return Math.imul(h ^ (h >>> 13), 0x297a2d39) >>> 0;
}
function miles(a, b) {
  const t = Math.PI / 180;
  const h = Math.sin((b.lat - a.lat) * t / 2) ** 2 + Math.cos(a.lat * t) * Math.cos(b.lat * t) * Math.sin((b.lng - a.lng) * t / 2) ** 2;
  return 2 * 3958.8 * Math.asin(Math.min(1, Math.sqrt(h)));
}
function bearing(a, b) {
  const t = Math.PI / 180;
  const y = Math.sin((b.lng - a.lng) * t) * Math.cos(b.lat * t);
  const x = Math.cos(a.lat * t) * Math.sin(b.lat * t) - Math.sin(a.lat * t) * Math.cos(b.lat * t) * Math.cos((b.lng - a.lng) * t);
  return DIRS[Math.round((((Math.atan2(y, x) / t) + 360) % 360) / 45) % 8];
}
function list(items, and = "and") {
  const a = items.filter(Boolean);
  if (a.length <= 1) return a[0] || "";
  return `${a.slice(0, -1).join(", ")} ${and} ${a[a.length - 1]}`;
}
const pct = (x) => `${Math.round(x * 10) / 10}%`;

/** Service family for the duty and checklist paragraphs. */
export function depthFamily(serviceSlug) {
  const s = String(serviceSlug || "");
  if (s === "gas-systems" || s === "heating") return "gas";
  if (s === "nurse-call") return "care";
  if (s === "aov-air-handling" || s === "aov") return "aov";
  if (s === "barriers") return "barriers";
  if (s === "asbestos-survey") return "asbestos";
  if (["plumbing", "water-wras", "legionella-risk-assessment"].includes(s)) return "water";
  if (["electrical", "ev-chargers", "pat-testing", "electrics-first-fix", "epc"].includes(s)) return "electrical";
  if (["cctv", "access-control", "intruder-alarm", "door-entry", "intercoms", "barriers"].includes(s)) return "security";
  if (/^fire-|^aov|emergency-lighting|dry-risers|evacuation-alerts|sprinkler|smoke-co-alarms|kitchen-fire-suppression/.test(s)) return "fire";
  return "building";
}

function nationOf(council, fallback) {
  const row = council ? (laCensus[council] || laCensus[council.replace(/^(The )?City of /, "")]) : null;
  const code = row ? String(row[8] || "") : "";
  if (code.startsWith("S")) return "Scotland";
  if (code.startsWith("W")) return "Wales";
  if (code.startsWith("E")) return "England";
  return fallback || "England";
}

// Dwelling-led notes. {t} town, {s} subject, {p} share of that dwelling type.
const STOCK = {
  terraced: {
    electrical: [
      "Terraced houses are the largest share of homes across {c} ({p}). In that stock the consumer unit is usually by the front door or under the stairs, and older terraces in {t} can still have a mix of rewired and original circuits, so the {s} scope asks when the last rewire or board change happened.",
      "With terraces at {p} of homes in {c}, a lot of {s} work in {t} is two-up two-down or through-terrace housing. Cable routes are short, but loft and cellar access decide how long a test takes, so tell us whether either is boarded or locked.",
    ],
    gas: [
      "Terraces make up {p} of homes in {c}. In {t} terraces the boiler is often in a kitchen or a back bedroom with a flue onto a rear yard or alley, and the flue route matters for the gas safety check, which is carried out by Gas Safe registered engineers.",
      "In a stock where terraced houses are {p} of homes ({c}), many {t} boilers vent into narrow rear yards. Say where the flue terminates and whether a neighbour's window or extension has been built near it since the last check.",
    ],
    fire: [
      "Terraced homes are {p} of the stock across {c}. Where a terrace in {t} has been split into flats or let as an HMO, the shared stair becomes the escape route, and the alarm, the doors and the lighting on that stair are what the {s} scope is written around.",
      "With {p} of homes in {c} terraced, converted terraces are a common {s} enquiry from {t}. The questions are how many lettings share the stair, whether the flat doors self-close, and whether the alarm is mains-wired and interlinked.",
    ],
    security: [
      "Terraces are {p} of homes across {c}. In {t} that means a lot of rear yards and back alleys, so camera and door positions are usually about the rear gate and the yard wall as much as the front door.",
      "In {c}, {p} of homes are terraced. For {s} in {t} that often means narrow frontages, shared alleys and limited cable routes, which is why we ask for photos of the front and the rear before quoting.",
    ],
    water: [
      "Terraced houses are {p} of homes across {c}. Older {t} terraces can still have original lead or galvanised supply pipes and tanks in the loft, so the {s} scope asks what pipework is visible at the stopcock and in the roof space.",
      "With terraces at {p} of the stock in {c}, many {t} properties have a cold-water tank in the loft and a stopcock under the sink or in a cellar. Tell us where both are before the visit.",
    ],
    building: [
      "Terraced houses are {p} of homes across {c}. In {t} that means party walls on both sides, rear access through yards or alleys, and skips or materials that may need a permit on the street, all of which belong in the {s} scope.",
      "With {p} of homes in {c} terraced, a lot of {s} work in {t} shares walls with neighbours. Where the Party Wall etc. Act 1996 applies, the notice is the owner's step, and we plan the work around it.",
    ],
  },
  semi: {
    electrical: [
      "Semi-detached houses are the biggest share of homes in {c} ({p}). In {t} semis, extensions and garage conversions are common, and those later circuits are where an {s} visit most often finds a mix of old and new wiring.",
      "With semis at {p} of homes across {c}, many {t} properties have a side passage, a garage and at least one extension. Tell us about those, because each can add circuits, and outbuildings may have their own supply.",
    ],
    gas: [
      "Semi-detached homes are {p} of the stock in {c}. In {t} semis the boiler may have been moved during a kitchen extension, so the flue and the gas run can be newer than the house, and the check, carried out by Gas Safe registered engineers, follows what is there now.",
      "With {p} of homes in {c} semi-detached, side-wall flues near a neighbour's boundary are common in {t}. Say if anything has been built close to the flue since the last gas safety record.",
    ],
    fire: [
      "Semi-detached houses make up {p} of homes across {c}. Most {s} enquiries from {t} therefore come from the share that is flats, HMOs, care settings and commercial premises, where the duty sits with a responsible person.",
      "In {c}, {p} of homes are semis. For {s} in {t} the work is usually in converted houses, small blocks, shops and offices rather than family homes, and the scope starts from who the responsible person is.",
      "Semis lead the housing mix in {c} at {p}. Family semis in {t} rarely need more than domestic smoke and heat alarms, so {s} requests usually concern the converted, shared and commercial buildings in the town.",
    ],
    security: [
      "Semis are {p} of homes in {c}. In {t} the side passage and the drive are the usual weak points, so {s} positions are agreed against those and against what a camera or reader would see beyond the boundary.",
      "With semi-detached homes at {p} of the stock in {c}, many {t} sites have a drive, a side gate and a garage. Those three decide most of the {s} layout, so photos of each help the quote.",
    ],
    water: [
      "Semi-detached houses are {p} of homes in {c}. Many {t} semis have had a kitchen or bathroom moved, so the {s} scope asks where the stopcock is now and whether there is still a loft tank.",
      "With {p} of homes in {c} semis, combination boilers have replaced tanks in a lot of {t} houses. Tell us if a tank or cylinder is still fitted, because it changes the {s} work.",
    ],
    building: [
      "Semi-detached houses are {p} of the stock in {c}. In {t} that usually means one party wall, side access and room for an extension, and the {s} scope says which of those the work touches.",
      "With semis at {p} of homes across {c}, side and rear extensions are a common {s} request in {t}. Planning and building control questions go to the council; we quote the build once the drawings exist.",
    ],
  },
  detached: {
    electrical: [
      "Detached houses are {p} of homes in {c}, a high share. Larger {t} houses mean longer cable runs, more circuits and often a detached garage or outbuilding with its own supply, all of which add test time.",
      "With detached homes at {p} across {c}, {s} work in {t} often includes outbuildings, garden supplies or an EV point. List them, because each is part of the inspection if it is fed from the house.",
    ],
    gas: [
      "Detached homes are {p} of the stock in {c}. In {t} that can mean larger boilers, more than one appliance and longer flue runs, and the gas check is carried out by Gas Safe registered engineers on every appliance listed.",
      "With {p} of homes in {c} detached, {t} properties often have a boiler plus a gas fire or hob. Name every gas appliance so the visit covers all of them.",
    ],
    fire: [
      "Detached houses are {p} of homes in {c}. {s} enquiries from {t} tend to come from care homes, schools, offices and converted larger houses, where a responsible person holds the fire risk assessment.",
      "In {c}, {p} of homes are detached. Larger converted houses in {t} are a typical {s} site, and the question is how the building is occupied now, not how it was built.",
    ],
    security: [
      "Detached houses are {p} of homes in {c}. In {t} that means more perimeter, longer drives and gates, so {s} quotes start from a site sketch showing where cover is wanted.",
      "With detached homes at {p} of the stock in {c}, {t} sites often have long boundaries and outbuildings. The {s} layout is agreed against those, not against a standard kit size.",
    ],
    water: [
      "Detached houses are {p} of homes across {c}. In {t} that can mean longer supply runs, outside taps and more than one bathroom, so the {s} scope lists each.",
      "With {p} of homes in {c} detached, unvented cylinders and multiple bathrooms are common in {t}. Say what hot-water system is fitted.",
    ],
    building: [
      "Detached houses are {p} of homes in {c}. {s} work on a detached house in {t} avoids party-wall notices but often needs more access equipment and longer material runs.",
      "With detached homes at {p} across {c}, {s} in {t} is usually scoped around access to all four elevations and the drive for deliveries.",
    ],
  },
  flats: {
    electrical: [
      "Flats are {p} of homes across {c}. In {t} blocks the landlord's supply to the common parts is a separate installation from each flat, and the {s} scope says which of the two is being tested.",
      "With flats at {p} of the stock in {c}, {s} in {t} often means a riser, a landlord's board and individual flat boards. Name which ones the instruction covers.",
    ],
    gas: [
      "Flats are {p} of homes in {c}. In {t} blocks the gas supply, the meter position and any shared flue matter, and checks are carried out by Gas Safe registered engineers flat by flat.",
      "With {p} of homes in {c} in flats, {t} gas work often needs access to a meter cupboard in the common parts as well as the flat itself. Say who holds that key.",
    ],
    fire: [
      "Flats are {p} of homes across {c}. In {t} blocks the common parts carry duties under fire safety law, and the {s} scope covers the shared areas the responsible person controls.",
      "With flats at {p} of the stock in {c}, {s} in {t} is mostly about common stairs, corridors and plant rooms in blocks, and the person who holds the fire risk assessment.",
    ],
    security: [
      "Flats are {p} of homes in {c}. In {t} blocks the main entrance, the bin store and the car park are the usual {s} points, and residents should be told what a camera covers.",
      "With {p} of homes in {c} in flats, door entry and fob systems are a large share of {s} enquiries from {t}. Say how many flats and doors the system serves.",
    ],
    water: [
      "Flats are {p} of homes across {c}. In {t} blocks the {s} scope separates the shared tanks, pumps and risers from the pipework inside each flat.",
      "With flats at {p} of homes in {c}, stored water in {t} blocks is the main {s} risk point. Say whether the block has a tank room or booster pumps.",
    ],
    building: [
      "Flats are {p} of homes in {c}. {s} in {t} blocks usually needs the managing agent's consent, resident notice and a plan for the common parts.",
      "With flats at {p} across {c}, {s} work in {t} often sits in the common parts or a single flat in a block, and the lease decides who instructs it.",
    ],
  },
};

// AOV and barrier pages: one dwelling note whatever the dominant type. {k} type label.
const STOCK_ANY = {
  aov: [
    "{k} are the largest share of homes across {c} ({p}), and flats are {f}. AOV work in {t} sits in the purpose-built and converted blocks within that flat share, plus schools, care homes and offices with a smoke-vented stair.",
    "Across {c}, {k} lead the housing mix at {p}, with flats at {f}. Smoke vents in {t} are therefore a block and commercial question: the stair or corridor head, the panel and the fire-alarm input.",
  ],
  barriers: [
    "{k} are the largest share of homes across {c} ({p}), and flats are {f}. Barrier requests in {t} come mainly from residents' car parks behind blocks, business yards, schools and clinics rather than private drives.",
    "Across {c}, {k} lead the housing mix at {p}, with flats at {f}. In {t} the usual barrier sites are shared car parks and yards where a manual arm or a width or height restrictor controls who drives in.",
  ],
};
const KIND_LABEL = { detached: "Detached houses", semi: "Semi-detached houses", terraced: "Terraced houses", flats: "Flats" };

// Duties by family. {t} town, {s} subject, {priv}/{soc}/{own} tenure shares, {c} council.
const DUTY = {
  electrical: {
    England: [
      "Private renting is {priv} of households across {c}. Those landlords in {t} must have the fixed wiring inspected and tested at least every five years under the Electrical Safety Standards in the Private Rented Sector (England) Regulations 2020, and give tenants a copy of the report.",
      "{priv} of households in {c} rent privately. For those {t} lets, the Electrical Safety Standards in the Private Rented Sector (England) Regulations 2020 set a five-year maximum between inspections, with any urgent remedial work finished within 28 days.",
    ],
    Wales: [
      "Private renting is {priv} of households across {c}. In Wales, the Renting Homes (Fitness for Human Habitation) (Wales) Regulations 2022 require an electrical condition report at least every five years for occupation contracts in {t}.",
    ],
    Scotland: [
      "Private renting is {priv} of households across {c}. Scottish landlords in {t} need an electrical installation condition report at least every five years under the repairing standard, with portable appliance checks on anything they supply.",
    ],
  },
  gas: {
    any: [
      "Private renting is {priv} of households across {c} and social renting {soc}. Landlords in {t} need an annual gas safety check under the Gas Safety (Installation and Use) Regulations 1998, carried out by Gas Safe registered engineers, with the record given to tenants within 28 days.",
      "With {priv} of households in {c} renting privately and {soc} in social housing, many {t} landlords hold a gas safety duty. The annual check is carried out by Gas Safe registered engineers and the record goes to the tenant within 28 days.",
    ],
  },
  fire: {
    England: [
      "{priv} of households in {c} rent privately and {soc} rent from a social landlord. In {t} blocks and HMOs the responsible person must keep a fire risk assessment under the Regulatory Reform (Fire Safety) Order 2005, and the Fire Safety (England) Regulations 2022 add checks and resident information in multi-occupied buildings.",
      "Across {c}, social renting is {soc} of households and private renting {priv}. For {t} buildings with common parts, the Regulatory Reform (Fire Safety) Order 2005 puts the duty on a responsible person, and the Fire Safety (England) Regulations 2022 add fire door checks in blocks over 11 metres.",
      "Rented homes are a large part of {c}: {priv} of households rent privately and {soc} from a social landlord. Whoever controls a workplace, a shop or the shared parts of a block in {t} is the responsible person, and keeping alarms, lighting and doors maintained is part of that duty.",
      "In {t}, as across {c} ({own} owner-occupied, {priv} private rented, {soc} social rented), fire safety duties fall on the responsible person for each non-domestic building and each set of common parts. Keep the fire risk assessment to hand when you ask for {s}.",
    ],
    Wales: [
      "{priv} of households in {c} rent privately and {soc} socially. In {t}, the Regulatory Reform (Fire Safety) Order 2005 applies to common parts and workplaces, and the responsible person keeps the fire risk assessment.",
    ],
    Scotland: [
      "{priv} of households in {c} rent privately and {soc} socially. In {t}, the Fire (Scotland) Act 2005 places the duty on whoever controls the premises, and HMOs also need a licence from the council.",
    ],
  },
  security: {
    any: [
      "Owner-occupiers are {own} of households across {c} and renters make up most of the rest. Wherever a camera or door reader in {t} records people beyond a private home, UK GDPR and the Data Protection Act 2018 apply, so the scope notes signage and who can view the footage.",
      "In {c}, {own} of households own their home, {soc} rent socially and {priv} privately. In shared {t} buildings, cameras covering common parts are covered by UK GDPR, so the managing agent or landlord should name who controls the recordings.",
    ],
  },
  water: {
    any: [
      "Private renting is {priv} of households across {c}. Landlords in {t} are expected to assess the legionella risk in the water system, following HSE guidance (ACoP L8 and HSG274), and keep a record of the findings.",
      "With {priv} of households in {c} renting privately and {soc} socially, many {t} buildings have a landlord responsible for the water system. HSE guidance (ACoP L8) asks for a risk assessment and simple controls proportionate to the system.",
    ],
  },
  aov: {
    England: [
      "Across {c}, {soc} of households rent socially and {priv} privately. Where a block in {t} has an AOV, the responsible person under the Regulatory Reform (Fire Safety) Order 2005 keeps it maintained, and the Fire Safety (England) Regulations 2022 add checks in multi-occupied buildings.",
    ],
    Wales: [
      "Across {c}, {soc} of households rent socially and {priv} privately. Where a block in {t} has an AOV, the responsible person under the Regulatory Reform (Fire Safety) Order 2005 keeps it in working order.",
    ],
    Scotland: [
      "Across {c}, {soc} of households rent socially and {priv} privately. In {t}, the Fire (Scotland) Act 2005 puts the duty to keep smoke ventilation working on whoever controls the premises.",
    ],
  },
  barriers: {
    any: [
      "Owner-occupiers are {own} of households across {c}. Barriers in {t} are mostly on shared or commercial land, so the instruction comes from the landowner or managing agent, and the quote names who holds the key or the override.",
    ],
  },
  care: {
    England: [
      "Care homes in {t} are registered and inspected by the Care Quality Commission. A call system that works, and a record of how it was tested, is part of the evidence a home keeps for that inspection.",
    ],
    Wales: [
      "Care homes in {t} are regulated by Care Inspectorate Wales. A tested call system and a written record of the test are part of the evidence a home keeps.",
    ],
    Scotland: [
      "Care homes in {t} are inspected by the Care Inspectorate. A tested call system and a written record of the test are part of the evidence a home keeps.",
    ],
  },
  asbestos: {
    any: [
      "Owner-occupiers are {own} of households across {c}, but common parts of {t} blocks and every non-domestic building fall under the duty to manage in the Control of Asbestos Regulations 2012. Buildings put up or refurbished before 2000 are the usual survey request.",
    ],
  },
  building: {
    any: [
      "Owner-occupiers are {own} of households across {c}, private renters {priv} and social renters {soc}. That mix decides who instructs {s} in {t}: the owner, the landlord or the housing provider, and the quote is addressed to that person.",
      "In {c}, {own} of households own their home and {priv} rent privately. Building Regulations sign-off for {s} in {t} goes through the council or an approved inspector, and that step is named in the quote.",
    ],
  },
};

const CHECK = {
  aov: [
    "For AOV work in {t}, send the postcode ({o}), the panel make, the number of vents and storeys, and whether a vent is stuck open or shut.",
  ],
  barriers: [
    "For barrier work in {t}, send the postcode ({o}), the lane width, whether the barrier is manual or powered, and who needs access.",
  ],
  electrical: [
    "For {s} in {t}, send the full postcode ({o}), the age of the consumer unit if known, the date of the last report, and whether the property is occupied.",
    "Before we quote {s} in {t}, tell us the postcode ({o}), how many bedrooms or units there are, and where the meter and the main board sit.",
  ],
  gas: [
    "For {s} in {t}, send the postcode ({o}), the number of gas appliances, the date on the last gas safety record and the tenant's contact.",
    "Before a gas visit in {t}, tell us the postcode ({o}), the boiler make if visible, and whether the meter is inside or in a box outside.",
  ],
  fire: [
    "For {s} in {t}, send the postcode ({o}), the number of storeys, how the building is occupied, and the current fire risk assessment if there is one.",
    "Before we quote {s} in {t}, tell us the postcode ({o}), the panel make if fitted, and who the responsible person is.",
    "An enquiry for {s} in {t} moves faster with the postcode ({o}), photos of the panel or equipment, the last service sheet and the name of whoever will meet us on site.",
  ],
  security: [
    "For {s} in {t}, send the postcode ({o}), photos of the doors or areas to cover, and whether the site has broadband and power where the kit will go.",
    "Before we quote {s} in {t}, tell us the postcode ({o}), how many entrances there are and who should hold the admin login.",
  ],
  water: [
    "For {s} in {t}, send the postcode ({o}), whether there is a tank or cylinder, and how many outlets the building has.",
    "Before we quote {s} in {t}, tell us the postcode ({o}), the age of the building and whether any outlets are rarely used.",
  ],
  care: [
    "For {s} in {t}, send the postcode ({o}), the number of rooms, the current panel make and whether the home is fully occupied.",
  ],
  asbestos: [
    "For {s} in {t}, send the postcode ({o}), the approximate build year, any planned refurbishment and whether a previous survey exists.",
  ],
  building: [
    "For {s} in {t}, send the postcode ({o}), photos of the area, any drawings, and whether the building is occupied during the work.",
    "Before we quote {s} in {t}, tell us the postcode ({o}), what access there is for deliveries and whether the owner has planning or building control approval.",
  ],
};

const BATCH = [
  "If you also manage property in {n}, say so. Each address gets its own quote, but nearby sites can sometimes share a diary day.",
  "Managing agents with buildings in {t} and {n} can send the addresses together. They are still quoted one by one, each with its own free written quote.",
  "The places nearest to {t} on our town list are {n}. A second site there is a separate scope, not an add-on to this one.",
  "Portfolios often cross boundaries: {t} sits close to {n}. List every address in one message and each gets its own written scope.",
];

function fill(text, map) {
  return text.replace(/\{(\w+)\}/g, (m, k) => (k in map ? map[k] : m));
}
function choose(arr, seed, salt) {
  if (!arr || !arr.length) return "";
  return arr[hash(`${seed}|${salt}`) % arr.length];
}

/**
 * @param {object} o
 * @param {string} o.slug town slug, o.town display name, o.subject, o.service (service slug)
 * @param {Map|null} o.names slug->{name} of towns that have a page in this family (for "nearest")
 * @param {string[]} o.extra extra paragraphs (e.g. bank picks) to shuffle in
 * @returns {{html:string, paragraphs:string[]}|null}
 */
export function townDepthBlock({ slug, town, subject, service, names = null, extra = [], nation = "", priceFrom = "" }) {
  const g = geoRow(slug);
  if (!g) return null;
  const [council, , ocs, , , lat, lng] = g;
  const seed = `${slug}|${subject}`;
  const fam = depthFamily(service);
  const laRow = council ? (laCensus[council] || laCensus[council.replace(/^(The )?City of /, "")]) : null;
  const nat = nationOf(council, nation);
  const c = council ? `the ${council} council area` : town;
  const paras = [];
  if (laRow) {
    const [, det, semi, terr, flat, own, soc, priv] = laRow;
    const types = [["detached", det], ["semi", semi], ["terraced", terr], ["flats", flat]].filter(([, v]) => v != null);
    types.sort((a, b) => b[1] - a[1]);
    // Lead with the dominant dwelling type; also cover flats when they are a big share.
    const picks = [types[0]];
    const flatRow = types.find(([k]) => k === "flats");
    if (flatRow && flatRow !== types[0] && flatRow[1] >= 20) picks.push(flatRow);
    else if (types[1]) picks.push(types[1]);
    if (STOCK_ANY[fam]) {
      const line = choose(STOCK_ANY[fam], seed, "stock-any");
      paras.push(fill(line, { t: town, c, k: KIND_LABEL[types[0][0]], p: pct(types[0][1]), f: pct(flat) }));
      picks.length = 0;
    }
    for (const [kind, v] of picks) {
      const bank = (STOCK[kind] || {})[fam] || (STOCK[kind] || {}).building || [];
      const line = choose(bank, seed, `stock-${kind}`);
      if (line) paras.push(fill(line, { t: town, s: subject, c, p: pct(v) }));
    }
    const duty = DUTY[fam] || DUTY.building;
    const dl = duty[nat] || duty.any || duty.England || [];
    const d = choose(dl, seed, "duty");
    if (d) paras.push(fill(d, { t: town, s: subject, c, priv: pct(priv), soc: pct(soc), own: pct(own) }));
  }
  if (Array.isArray(ocs) && ocs.length) {
    const ck = choose(CHECK[fam] || CHECK.building, seed, "check");
    if (ck) paras.push(`${fill(ck, { t: town, s: subject, o: list(ocs.slice(0, 4), "or") })} Phone or WhatsApp 07517 806082, or email icomplypropertyservices@gmail.com. ${priceFrom ? `${subject} from ${priceFrom}, confirmed in a free written quote.` : "The quote is price on application."}`);
  }
  if (names && Number.isFinite(lat) && Number.isFinite(lng)) {
    const here = { lat, lng };
    const near = [];
    for (const [s, row] of Object.entries(geo)) {
      if (s === slug || !names.has(s)) continue;
      const m = miles(here, { lat: row[5], lng: row[6] });
      if (m < 0.5 || m > 35) continue;
      near.push([m, s, row]);
    }
    near.sort((a, b) => a[0] - b[0]);
    const pick = near.slice(1, 4).map(([m, s, row]) => `${(names.get(s) && names.get(s).name) || s} (${Math.max(1, Math.round(m))} miles ${bearing(here, { lat: row[5], lng: row[6] })})`);
    if (pick.length >= 2) {
      const mi = Math.round(miles(YARD, here));
      paras.push(`${fill(choose(BATCH, seed, "batch"), { t: town, n: list(pick, "or") })} ${town} itself is about ${Math.max(1, mi)} miles from the Offerton yard.`);
    }
  }
  for (const e of extra) if (e) paras.push(e);
  if (paras.length < 2) return null;
  // Seeded order so neighbouring towns do not share the same paragraph sequence.
  const order = paras.map((p, i) => [hash(`${seed}|order|${i}`), p]).sort((a, b) => a[0] - b[0]).map((r) => r[1]);
  const html = `<section id="town-depth" data-pe-slot="q1-town-depth"><h2>${esc(`${subject} in ${town}: local notes`)}</h2>${order.map((p) => `<p>${esc(p)}</p>`).join("")}</section>`;
  return { html, paragraphs: order };
}
