/**
 * Facts-only local block for edge keyword x town and manufacturer x town pages.
 * Data: website/data/town-geo-slim.json (postcodes.io / ONSPD; census from ONS 2021).
 * No invented reviews, projects, response times or statistics.
 */
import geo from "../../website/data/town-geo-slim.json" with { type: "json" };

const YARD = { lat: 53.3925, lng: -2.1265 };
const DIRS = ["north", "north-east", "east", "south-east", "south", "south-west", "west", "north-west"];

function esc(v) {
  return String(v).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
function miles(a, b) {
  const r = 3958.8, t = Math.PI / 180;
  const h = Math.sin((b.lat - a.lat) * t / 2) ** 2 + Math.cos(a.lat * t) * Math.cos(b.lat * t) * Math.sin((b.lng - a.lng) * t / 2) ** 2;
  return 2 * r * Math.asin(Math.min(1, Math.sqrt(h)));
}
function bearing(a, b) {
  const t = Math.PI / 180;
  const y = Math.sin((b.lng - a.lng) * t) * Math.cos(b.lat * t);
  const x = Math.cos(a.lat * t) * Math.sin(b.lat * t) - Math.sin(a.lat * t) * Math.cos(b.lat * t) * Math.cos((b.lng - a.lng) * t);
  const d = ((Math.atan2(y, x) / t) + 360) % 360;
  return DIRS[Math.round(d / 45) % 8];
}
function list(items, and = "and") {
  const a = items.filter(Boolean);
  if (a.length <= 1) return a[0] || "";
  return `${a.slice(0, -1).join(", ")} ${and} ${a[a.length - 1]}`;
}
function hash(s) {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
  return h >>> 0;
}

export function hasTownFacts(slug) {
  return Boolean(geo[slug]);
}

/** names: Map slug -> {name} of towns that have a page in this family. */
export function nearestTowns(slug, names, limit = 6) {
  const g = geo[slug];
  if (!g) return [];
  const here = { lat: g[5], lng: g[6] };
  const out = [];
  for (const [s, row] of Object.entries(geo)) {
    if (s === slug || !names.has(s)) continue;
    const m = miles(here, { lat: row[5], lng: row[6] });
    if (m < 0.5 || m > 35) continue;
    out.push({ slug: s, name: names.get(s).name || s, miles: m, dir: bearing(here, { lat: row[5], lng: row[6] }) });
  }
  out.sort((a, b) => a.miles - b.miles);
  return out.slice(0, limit);
}

function serviceLine(service, town, c, council) {
  const [flats, social, ind, indPct, scope] = c;
  if (scope === "la" || scope === "las") town = `the ${council} council area (whole authority)`;
  const src = scope === "las" ? "Scotland's Census 2022" : "ONS Census 2021";
  const indTxt = ind ? ` The largest employment group among residents is ${ind.toLowerCase()} (${indPct}%).` : "";
  if (service === "aov-air-handling") {
    if (flats == null) return "";
    return flats >= 25
      ? `Flats are ${flats}% of homes in ${town} (${src}), so stair and corridor smoke vents in purpose-built blocks are the usual AOV work.${indTxt}`
      : `Flats are ${flats}% of homes in ${town} (${src}); AOVs here sit in the flat blocks within that share and in schools, care homes, offices and shops.${indTxt}`;
  }
  if (service === "barriers") {
    if (flats == null) return "";
    return flats >= 25
      ? `With flats at ${flats}% of homes (${src}), residents' car parks behind blocks are a common barrier site in ${town}, alongside manual arms and width or height restrictors.${indTxt}`
      : `Flats are only ${flats}% of homes in ${town} (${src}), so barrier requests tend to come from business yards, schools, clinics and private car parks.${indTxt}`;
  }
  const bits = [];
  if (flats != null) bits.push(`flats are ${flats}% of homes`);
  if (social != null) bits.push(`social renting is ${social}% of households`);
  if (!bits.length) return "";
  return `In ${town}, ${list(bits)} (${src}). Shared parts of blocks and HMOs carry fire-safety duties under the Regulatory Reform (Fire Safety) Order 2005 in England and Wales, or the equivalent law in Scotland.${indTxt}`;
}

/**
 * @returns {{html:string, faqs:Array<[string,string]>}|null}
 */
export function townFactsBlock({ slug, town, subject, service, hrefFor, names }) {
  const g = geo[slug];
  if (!g) return null;
  const [council, county, ocs, wards, cons, lat, lng, census] = g;
  const seed = hash(`${slug}|${subject}`);
  const mi = Math.round(miles(YARD, { lat, lng }));
  const dir = bearing(YARD, { lat, lng });
  const paras = [];
  let lead = slug === "london" ? `This page covers London; the centre point we measure from is in ${council || "Westminster"}.` : council ? `${town} sits in the ${council} council area${county && county !== council ? `, ${county}` : ""}.` : `${town}${county ? ` is in ${county}` : ""}.`;
  if (ocs.length) {
    lead += [
      ` Postcode districts around the centre: ${list(ocs)}.`,
      ` Addresses here mostly start ${list(ocs, "or")}.`,
      ` Give us a postcode starting ${list(ocs, "or")} and we can place the site.`,
    ][seed % 3];
  }
  if (wards.length) lead += ` Wards in those districts include: ${wards.join("; ")}.`;
  if (cons) lead += ` Constituency: ${cons}.`;
  paras.push(lead);
  paras.push(mi <= 3
    ? `Our yard at Offerton, Stockport SK2 5DE is close by, so ${subject.toLowerCase()} visits in ${town} fit around other local work.`
    : `${town} is about ${mi} miles ${dir} of our Offerton, Stockport SK2 5DE yard in a straight line. ${mi <= 25 ? "That is a day visit, with travel as a small line on the quote." : mi <= 90 ? "Visits at that range are batched into planned days, with travel written on the quote." : "Travel, and any overnight stay, is priced on the quote before anything is booked."}`);
  if (census) {
    const line = serviceLine(service, town, census, council);
    if (line) paras.push(line);
  }
  const near = names ? nearestTowns(slug, names, 6) : [];
  let html = `<section id="town-facts"><h2>${esc(`${town}: council, postcodes and travel`)}</h2>${paras.map((p) => `<p>${esc(p)}</p>`).join("")}`;
  if (near.length && hrefFor) {
    html += `<h3>${esc(`${subject} near ${town}`)}</h3><ul>${near.map((n) => `<li><a href="${esc(hrefFor(n.slug))}">${esc(`${n.name} (${Math.max(1, Math.round(n.miles))} mi ${n.dir})`)}</a></li>`).join("")}</ul>`;
  }
  else if (near.length) {
    html += `<p>${esc(`Nearest other towns, straight line from central ${town}: ${near.map((n) => `${n.name} ${Math.max(1, Math.round(n.miles))} mi ${n.dir}`).join("; ")}.`)}</p>`;
  }
  html += `</section>`;
  const faqs = [];
  if (ocs.length) {
    faqs.push([`Do you cover ${list(ocs.slice(0, 3))} for ${subject.toLowerCase()}?`,
      `Yes. ${list(ocs)} are quoted from the Stockport yard, about ${mi} miles away. Send the full postcode and a photo, or call 07517806082.`]);
  }
  if (near.length) {
    faqs.push([`Can a ${town} visit be combined with nearby sites?`,
      `Often. The nearest other places we cover are ${list(near.slice(0, 3).map((n) => `${n.name} (${Math.max(1, Math.round(n.miles))} miles ${n.dir})`))}.`]);
  }
  return { html, faqs };
}
