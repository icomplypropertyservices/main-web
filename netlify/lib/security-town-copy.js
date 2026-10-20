/**
 * Town-specific copy for the 15 new security keyword intents on keyword x town pages (SEO PR145, 2026-10-20).
 * Each town gets its own wording: eight slots of 5-19 sentence variants (pairwise coprime counts),
 * picked by a per-town index (website/data/security-town-variants.json, built by
 * website/bin/build-security-town-variants.py) so nearby towns share as little wording as possible. Facts come from website/data/security-dual-ring-p0.json (county,
 * population, straight-line miles to Manchester and Burnley, nearest covered towns).
 * No invented reviews, response times, projects or prices.
 */
import doc from "../../website/data/security-dual-ring-p0.json" with { type: "json" };
import variantDoc from "../../website/data/security-town-variants.json" with { type: "json" };
const VARIANTS = variantDoc.towns || {};
import laCensus from "./la-census-slim.json" with { type: "json" };
import geo from "../../website/data/town-geo-slim.json" with { type: "json" };

const TOWN_LIST = Object.keys(doc.towns).sort();
const INDEX = new Map(TOWN_LIST.map((s, i) => [s, i]));
export const SECURITY_NEW_INTENTS = new Set(
  Object.entries(doc.intents).filter(([, v]) => v.status === "create").map(([s]) => s),
);

function esc(v) {
  return String(v).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
function nameOf(slug) {
  return (doc.towns[slug] && doc.towns[slug].name) || slug.replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
}
function list(a) {
  a = a.filter(Boolean);
  return a.length <= 1 ? (a[0] || "") : `${a.slice(0, -1).join(", ")} and ${a[a.length - 1]}`;
}
function popText(n) {
  if (!n) return "";
  if (n >= 100000) return `${Math.round(n / 1000).toLocaleString("en-GB")},000-odd residents`;
  return `around ${(Math.round(n / 100) * 100).toLocaleString("en-GB")} residents`;
}

// Kit nouns per service so the sentences talk about the right hardware.
const KIT = {
  cctv: { kit: "cameras and recorder", part: "a camera head or the recorder", test: "export a clip" },
  "access-control": { kit: "readers, locks and controller", part: "a reader, a maglock or the controller", test: "present a fob and walk through" },
  "intruder-alarm": { kit: "panel, detectors and sounder", part: "a detector, the panel or the bell box", test: "set and unset the system" },
  "door-entry": { kit: "door panel, handsets and lock release", part: "the door panel, a handset or the lock release", test: "call the door from a flat and release it" },
  intercoms: { kit: "intercom panel and handsets", part: "the panel or a handset", test: "call and release the door" },
  anpr: { kit: "ANPR cameras, lighting and the gate or barrier link", part: "a camera, the lighting or the gate link", test: "read a plate and open the gate" },
  security: { kit: "cameras, alarms and door hardware", part: "a camera, a detector or a door reader", test: "run each system once with you" },
};

/**
 * @returns {{html:string, faqs:Array<[string,string]>}|null}
 */
export function securityTownCopy(intentSlug, townSlug) {
  const it = doc.intents[intentSlug];
  const t = doc.towns[townSlug];
  if (!it || it.status !== "create" || !t || !INDEX.has(townSlug)) return null;
  const i = INDEX.get(townSlug);
  const town = t.name;
  const intent = it.name;
  const JOB = {
    "emergency-cctv-repair": "emergency CCTV repair", "emergency-access-control-repair": "emergency access control repair",
    "emergency-alarm-call-out": "an emergency alarm call-out", "24-hour-cctv-engineer": "a 24-hour CCTV engineer visit",
    "same-day-cctv-repair": "same-day CCTV repair", "same-day-alarm-repair": "same-day alarm repair",
    "security-systems-near-me": "security systems work", "security-system-installation": "security system installation",
    "anpr-installation": "ANPR installation", "anpr-near-me": "ANPR camera work", "access-control-emergency": "emergency access control work",
    "intruder-alarm-emergency": "emergency intruder alarm work", "door-entry-near-me": "door entry work",
    "cctv-installers-near-me": "CCTV installation", "alarm-installers-near-me": "alarm installation",
  };
  const job = JOB[intentSlug] || `${it.service_name || "security"} work`;
  const svc = it.service_name || "security";
  const k = KIT[it.service] || KIT[String(it.service || "").split("-")[0]] || KIT.security;
  const county = t.county && t.county !== town ? t.county : "";
  const mm = Math.round(t.mi_manchester || 0);
  const mb = Math.round(t.mi_burnley || 0);
  // Tameside and Trafford are whole boroughs with no single centre point: use the towns inside them.
  const borough = t.mi_manchester == null;
  const members = borough ? Object.values(doc.towns).filter((x) => x.county === town).map((x) => x.name) : [];
  const near = borough ? members : (t.near || []).map(nameOf);
  const n1 = near[0] || "the next town along";
  const n2 = near[1] || n1;
  const pop = popText(t.population);
  const closer = mm <= mb ? `Manchester (${mm} miles)` : `Burnley (${mb} miles)`;
  const urgent = /emergency|same-day|24-hour|call-out/.test(intentSlug);
  // Per-town base variant per slot (website/data/security-town-variants.json, built by
  // website/bin/build-security-town-variants.py so nearby towns share as little wording as possible).
  // Two towns share a slot's wording only when their index difference divides by that
  // slot's count (5, 7, 8, 9, 11, 13, 17, 19 are pairwise coprime), so any two towns share at
  // most two slots, and the index map is chosen so that nearby towns share none where possible.
  const idx = VARIANTS[townSlug] ?? i;
  const SLOT_OFF = { 5: 0, 7: 1, 8: 2, 9: 3, 11: 4, 13: 5, 17: 6, 19: 7 };
  const v = (count, j) => (idx + SLOT_OFF[count] + j) % count;

  const s1all = [ // 5
    `${town}${county ? `, in ${county},` : ""} is on our list of 269 towns, so ${job} here is booked from the Stockport workshop like any other local job.`,
    `For ${job} we treat ${town} as home ground: ${county ? `${county} is` : "the area is"} inside the patch our engineers drive every week.`,
    `People in ${town} who search for ${job} reach the same Stockport team that covers ${n1}${near[1] ? ` and ${n2}` : ""}.`,
    `${job.charAt(0).toUpperCase() + job.slice(1)} in ${town} is not passed to a call centre or a franchise; the engineer who surveys is from the Offerton workshop.`,
    `Our ${svc} engineers already work around ${town}${county ? ` and the rest of ${county}` : ""}, which keeps ${job} straightforward to arrange.`,
  ];
  const s1 = `${s1all[v(5, 0)]} ${s1all[v(5, 3)]} ${s1all[v(5, 4)]}`;
  const s2all = [ // 7
    `${town} has ${pop || "a mix of homes and businesses"}, and most requests come from landlords and agents with lets, small blocks and shop units rather than large estates.`,
    `With ${pop || "a settled population"}, ${town} produces a steady run of rented flats, corner shops and small industrial units that need their ${k.kit} kept working.`,
    `The buildings we see in ${town} range from terraced lets to offices over shops; each one gets a ${svc} scope written for that address.`,
    `In a town the size of ${town} (${pop || "a few thousand homes"}) the same agent often manages several doors, so we keep one record per building.`,
    `${town}'s housing stock${pop ? `, home to ${pop},` : ""} means older wiring routes and shared hallways are common, and the survey notes both before any ${svc} work.`,
    `Commercial occupiers in ${town} tend to ask about ${k.kit} for a single unit; landlords tend to ask about shared entrances and bin stores.`,
    `We rarely find two identical sites in ${town}: a converted mill, a parade of shops and a new-build block all need different ${k.kit}.`,
  ];
  const s2 = `${s2all[v(7, 0)]} ${s2all[v(7, 4)]} ${s2all[v(7, 6)]}`;
  const s3all = [ // 8
    `${town} is about ${mm} miles from Manchester and ${mb} from Burnley in a straight line, so the visit is planned around the nearer of the two, ${closer}.`,
    `Straight-line distance from ${town}: ${mm} miles to Manchester, ${mb} to Burnley. We use that to slot the visit into a day already working that side of the region.`,
    `${closer.split(" (")[0]} is the closer of our two reference points to ${town}, at ${closer.split("(")[1].replace(")", "")}; the diary is built around that.`,
    `Because ${town} sits ${mm} miles from central Manchester, travel is counted once in the written quote rather than as a separate charge on the day.`,
    `Engineers heading to ${town} usually come in from the ${mm < mb ? "Manchester" : "Burnley"} side; ${mb} miles to Burnley and ${mm} to Manchester are the straight-line figures we work from.`,
    `${town} is ${mm <= 15 ? "close enough to Manchester for a short hop" : mm <= 30 ? "a normal day run from Manchester" : "at the outer edge of our Manchester and Burnley rings"}, ${mm} and ${mb} miles respectively.`,
    `We map ${town} at ${mm} miles from Manchester; jobs further than 30 miles are grouped so one trip covers more than one address where you have several.`,
    `Travel to ${town} (${mm} miles from Manchester, ${mb} from Burnley) is priced into the quote, and the arrival window is agreed with whoever holds the keys.`,
  ];
  const s3fixed = `${s3all[v(8, 0)]} ${s3all[v(8, 5)]} ${s3all[v(8, 7)]} ${s3all[v(8, 2)]}`;
  const s4all = [ // 9
    urgent ? `When ${job} is needed in ${town}, tell us whether the site is insecure right now; that decides whether we make it safe first and repair second.` : `Before anyone travels to ${town}, we ask what is already fitted, because reusing sound ${k.kit} usually lowers the quote.`,
    `A photo of ${k.part} from a ${town} site lets the engineer bring the likely parts on the first visit.`,
    `If the fault in ${town} is ${k.part}, we say so plainly and quote that part, not a whole new system.`,
    `For a ${town} address the survey records who installed the existing ${k.kit}, whether logins are known, and what still works.`,
    `Some ${town} jobs are one door or one camera; others are a full site. The quote names exactly which, item by item.`,
    `Where ${town} tenants are in, we agree times with the agent so the ${svc} work does not leave a door insecure overnight.`,
    `Old systems are common around ${town}. If ${k.part} can no longer be supported, the quote offers the nearest sensible replacement.`,
    `We note power, cable routes and mounting at the ${town} site before talking about new equipment.`,
    `If you manage property in ${town} and ${n1}, one survey trip can usually cover both, with separate written quotes.`,
  ];
  const la = borough ? laCensus[town] : null;
  const s3 = borough
    ? `${town} is a whole borough rather than one town centre, taking in ${list(members)}. `
      + (la ? `Across the borough's ${Number(la[0]).toLocaleString("en-GB")} households, ${la[4]}% are flats, ${la[3]}% terraced and ${la[2]}% semi-detached, with ${la[7]}% privately rented (ONS Census 2021, ${la[8]}). ` : "")
      + `Postcodes and wards by town: ${Object.entries(doc.towns).filter(([, x]) => x.county === town).map(([sl, x]) => `${x.name} ${(geo[sl] && geo[sl][2] || []).slice(0, 3).join(", ")}${geo[sl] && geo[sl][3] && geo[sl][3].length ? ` (wards include ${list(geo[sl][3].slice(0, 2))})` : ""}`).join("; ")}. `
      + `We quote ${job} in ${town} per building, whichever of those towns it is in, and travel from the Stockport workshop is part of the quote.`
    : s3fixed;
  const s4 = `${s4all[v(9, 0)]} ${s4all[v(9, 5)]} ${s4all[v(9, 8)]} ${s4all[v(9, 2)]}`;
  const s5all = [ // 11
    `Nearby, we also cover ${list(near.slice(0, 3)) || "the surrounding towns"}, so a second ${town}-area address can share the trip.`,
    `${n1} is one of the towns next to ${town} on our list; sites in both can go on the same day sheet.`,
    `Agents covering ${town} and ${n2} often send both addresses together, and we quote each one separately.`,
    `Our route through ${town} often continues to ${n1}${near[2] ? ` or ${near[2]}` : ""}, which helps fit urgent work in.`,
    `The towns around ${town} that we serve include ${list(near) || "several neighbours"}; each has its own page with local details.`,
    `If your portfolio stretches from ${town} towards ${n2}, ask for a programme quote that lists each building.`,
    `Neighbouring ${n1} shares much of ${town}'s housing mix, so the questions we ask are similar but the scope stays per building.`,
    `A visit to ${town} can be paired with work in ${n1}; you will still get one written quote per address.`,
    `${town} borders towns we visit every week, including ${n1}, so there is usually an engineer close by.`,
    `For multi-site clients, ${town} and ${n2} are often booked in the same week to keep the handover simple.`,
    `We keep ${town} and its neighbours ${list(near.slice(0, 2)) || "nearby"} on the same planning map, which shortens lead times.`,
  ];
  const s5 = `${s5all[v(11, 0)]} ${s5all[v(11, 6)]} ${s5all[v(11, 9)]} ${s5all[v(11, 2)]}`;
  const s6all = [ // 13
    `After the work in ${town}, test it yourself: ${k.test} while the engineer is still there.`,
    `The handover for a ${town} job shows how to ${k.test}, who holds the logins, and where the records are kept.`,
    `We leave ${town} sites with a short written note of what was found, what changed and what was left alone.`,
    `Passwords for the ${town} system stay with you or your agent, never only with the installer.`,
    `If a ${town} tenant needs to ${k.test}, the engineer shows them on the day so the agent is not called later.`,
    `The record for each ${town} address names the equipment, the date and the result, ready for an insurer or a landlord file.`,
    `A follow-up visit in ${town}, if needed, is quoted again in writing; there are no surprise return charges.`,
    `Our engineer checks the finished ${town} job against the accepted list item by item before leaving.`,
    `Fire release and escape routes are checked on ${town} doors wherever ${svc} hardware touches them.`,
    `For ${town} blocks, the note says which flats were tested and which could not be reached.`,
    `Where recordings or access logs from a ${town} site are involved, we agree in writing who can see them.`,
    `Any part we remove from a ${town} site is listed, and you can keep it if you prefer.`,
    `We photograph the finished ${k.kit} at the ${town} address and attach the photos to the report.`,
  ];
  const s6 = `${s6all[v(13, 0)]} ${s6all[v(13, 7)]} ${s6all[v(13, 11)]} ${s6all[v(13, 2)]}`;
  const s7all = [ // 17
    `To book ${job} in ${town}, send the postcode and a photo to icomplypropertyservices@gmail.com or call 07517806082.`,
    `Phone 07517806082 with the ${town} address and what has gone wrong, and we reply with a price-on-application quote.`,
    `Email the ${town} postcode, the building type and who holds the keys; the written quote comes back before any date is fixed.`,
    `For ${town}, a WhatsApp photo of the ${k.kit} is often enough to start the quote.`,
    `Tell us if the ${town} site is occupied, void or commercial; it changes how the visit is booked.`,
    `The quickest way to get ${job} moving in ${town} is a call on 07517806082 with the postcode to hand.`,
    `We need three things for a ${town} quote: the address, what is fitted now, and the name of the person on site.`,
    `${town} quotes are price on application and include travel; nothing is booked until you accept in writing.`,
    `Send the ${town} details by email and say whether it is a new install, a fault or a service.`,
    `If the ${town} building has a managing agent, copy them in so access is agreed first time.`,
    `For a ${town} job, mention any lift, gate or car-park access so the engineer can plan the route in.`,
    `A short description of the ${town} fault, plus the make on the ${k.part.split(",")[0].replace(/^an? |^the /, "")}, speeds up the quote.`,
    `Landlords in ${town} can instruct us directly; agents can instruct on the landlord's behalf.`,
    `Call 07517806082 from ${town} and ask for a ${svc} survey; we confirm the visit window by text.`,
    `${town} requests received by email get a written reply with the scope, the price and the next free slot.`,
    `For ${job}, ${town} customers get the same fixed written quote as every other town we cover.`,
    `Give us the ${town} address and the best contact number, and we will arrange the survey around your diary.`,
  ];
  const s7 = `${s7all[v(17, 0)]} ${s7all[v(17, 9)]} ${s7all[v(17, 14)]} ${s7all[v(17, 2)]}`;

  const s8all = [ // 19
    `Common calls from ${town} include a door that will not latch after a power cut and a system nobody has the code for.`,
    `In ${town} we often meet ${k.kit} fitted by a firm that has since closed, with no handover file left behind.`,
    `Shared entrances in ${town} take the most wear, so they are checked first on any ${svc} visit.`,
    `Weather on exposed ${town} frontages shortens the life of outdoor ${k.kit}; the survey looks for water ingress.`,
    `Insurers sometimes ask ${town} landlords for proof that ${k.kit} works; the visit report covers that.`,
    `Bin stores, back gates and car parks are the parts of ${town} sites most often left out of older quotes.`,
    `A change of tenant in ${town} is a good moment to reset codes, fobs or handsets, and we can do it on the same visit.`,
    `Some ${town} buildings have two systems from different eras; the quote says which one we are working on.`,
    `Battery and power supply faults are behind many ${town} call-outs, and they are cheap to put right once found.`,
    `Where a ${town} unit is empty, the agent's keyholder arrangements decide the visit time.`,
    `Shops in ${town} usually want the work done before opening; we can book an early start where access allows.`,
    `Flats over shops in ${town} often share a door with the business below, which affects who can approve the work.`,
    `We check that nothing new blocks an escape route in the ${town} building, however small the job.`,
    `If ${town} residents complain about a buzzing handset, a dead camera or a false alarm, those notes help the diagnosis.`,
    `Cable that has been painted over or boxed in is common in ${town} conversions, and the survey allows for it.`,
    `Old wiring in ${town} terraces can limit where new ${k.kit} goes; the quote shows the route agreed.`,
    `For ${town} sites with a caretaker, we brief them so they can deal with simple questions after we leave.`,
    `A ${town} landlord with several lets can ask for one schedule covering every address, with a line per building.`,
    `The age of the ${k.kit} at a ${town} site is noted, so you can plan replacement before it fails.`,
  ];
  const s8 = `${s8all[v(19, 0)]} ${s8all[v(19, 6)]} ${s8all[v(19, 13)]} ${s8all[v(19, 2)]}`;
  const heading = `${intent} in ${town}: what to expect locally`;
  const base = [`${s1} ${s2}`, `${s3} ${s4}`, `${s5} ${s6}`, s8];
  // Paragraph order differs between neighbouring towns too (7 orders, by town index).
  const ORDERS = [[0, 1, 2, 3], [1, 3, 0, 2], [2, 0, 3, 1], [3, 2, 1, 0], [0, 3, 1, 2], [2, 1, 0, 3], [1, 0, 2, 3]];
  const paras = ORDERS[idx % 7].map((n) => base[n]).concat([s7]);
  const html = `<section id="security-town-local" data-pe-slot="security-town-local"><h2>${esc(heading)}</h2>${paras.map((p) => `<p>${esc(p)}</p>`).join("")}</section>`;
  const faqs = [[`Do you cover ${town} for ${job}?`, borough ? `Yes. ${town} borough, including ${list(members.slice(0, 4))}, is covered by our Stockport team. Call 07517806082 or email icomplypropertyservices@gmail.com.` : `Yes. ${town} is about ${mm} miles from Manchester and ${mb} from Burnley in a straight line, inside the area our Stockport team covers. Call 07517806082 or email icomplypropertyservices@gmail.com.`]];
  return { html, faqs };
}
