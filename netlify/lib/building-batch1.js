/**
 * Batch 1 renderer (KEYWORD-JOBTYPE-SEO-PLAN-2026-10-21 §4): Manchester building work.
 * Paths: /pages/{building-service}/{gm60-town} and /pages/keywords/{p0-building-keyword}/{gm60-town}.
 * Title "{Service} {Town} | Same/Next-Day | iComply" (≤60), meta 140–155 ending "Free quote, call 07517 806082.",
 * H1 "{Service} in {Town}", hero call + WhatsApp under the H1, same-family photos only, ≥85 internal links.
 */
import geo from "../../website/data/town-geo-slim.json" with { type: "json" };
import photos from "../../website/data/building-batch1-photos.json" with { type: "json" };
import creditRows from "../../website/data/hero-image-credits.json" with { type: "json" };
import { SERVICES, KEYWORDS, FAMILIES, PLASTERBOARD, STEPS_COMMON, GENERIC, FAMILY_EXTRA, GENERIC_FAQS } from "./building-batch1-content.js";
import { MATRIX_CSS, MATRIX_HEADER, MATRIX_FOOTER, MATRIX_SITE_FOOTER } from "./page-shell.js";
import { townFactsBlock } from "./town-facts.js";
import laCensus from "./la-census-slim.json" with { type: "json" };

const ORIGIN = "https://icomplypropertyservices.co.uk";
const PHONE = "07517 806082";
export const GM60 = [
  ["altrincham", "Altrincham"], ["ashton-under-lyne", "Ashton-under-Lyne"], ["atherton", "Atherton"], ["bolton", "Bolton"],
  ["bramhall", "Bramhall"], ["bury", "Bury"], ["cadishead", "Cadishead"], ["chadderton", "Chadderton"], ["cheadle", "Cheadle"],
  ["cheadle-hulme", "Cheadle Hulme"], ["chorlton", "Chorlton"], ["denton", "Denton"], ["didsbury", "Didsbury"], ["droylsden", "Droylsden"],
  ["dukinfield", "Dukinfield"], ["eccles", "Eccles"], ["failsworth", "Failsworth"], ["farnworth", "Farnworth"], ["hazel-grove", "Hazel Grove"],
  ["heywood", "Heywood"], ["horwich", "Horwich"], ["hyde", "Hyde"], ["irlam", "Irlam"], ["kearsley", "Kearsley"], ["lees", "Lees"],
  ["leigh", "Leigh"], ["little-lever", "Little Lever"], ["littleborough", "Littleborough"], ["manchester", "Manchester"], ["marple", "Marple"],
  ["middleton", "Middleton"], ["milnrow", "Milnrow"], ["mossley", "Mossley"], ["oldham", "Oldham"], ["pendlebury", "Pendlebury"],
  ["prestwich", "Prestwich"], ["radcliffe", "Radcliffe"], ["rochdale", "Rochdale"], ["romiley", "Romiley"], ["royton", "Royton"],
  ["saddleworth", "Saddleworth"], ["sale", "Sale"], ["salford", "Salford"], ["shaw", "Shaw"], ["stalybridge", "Stalybridge"],
  ["stockport", "Stockport"], ["stretford", "Stretford"], ["swinton", "Swinton"], ["tameside", "Tameside"], ["trafford", "Trafford"],
  ["tyldesley", "Tyldesley"], ["uppermill", "Uppermill"], ["urmston", "Urmston"], ["walkden", "Walkden"], ["westhoughton", "Westhoughton"],
  ["whitefield", "Whitefield"], ["wigan", "Wigan"], ["withington", "Withington"], ["worsley", "Worsley"], ["wythenshawe", "Wythenshawe"],
];
const GM_NAME = new Map(GM60);
const GM_NAMES = new Map(GM60.map(([s, n]) => [s, { name: n }]));

function esc(v) { return String(v).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }
function hash(s) { let h = 2166136261; for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); } return h >>> 0; }
function rng(seed) { let s = seed || 1; return () => { s ^= s << 13; s >>>= 0; s ^= s >>> 17; s ^= s << 5; s >>>= 0; return s / 4294967296; }; }
function expand(text, r) { return String(text).replace(/\[([^\]]+)\]/g, (_, body) => { const o = body.split("|"); return o[Math.floor(r() * o.length)]; }); }
function shuffle(a, r) { const b = a.slice(); for (let i = b.length - 1; i > 0; i--) { const j = Math.floor(r() * (i + 1)); [b[i], b[j]] = [b[j], b[i]]; } return b; }
function lcFirst(s) { return /^[A-Z][a-z]/.test(s) ? s.charAt(0).toLowerCase() + s.slice(1) : s; }
function list(a, and = "and") { a = a.filter(Boolean); return a.length <= 1 ? (a[0] || "") : `${a.slice(0, -1).join(", ")} ${and} ${a[a.length - 1]}`; }
function lowerLabel(s) { return s.split(" ").map((w) => (/^[A-Z][a-z]+$/.test(w) ? w.toLowerCase() : w)).join(" "); }

export function batch1Match(path) {
  let m = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && KEYWORDS[m[1]] && GM_NAME.has(m[2])) return { kind: "keyword", slug: m[1], town: m[2] };
  m = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && SERVICES[m[1]] && GM_NAME.has(m[2])) return { kind: "service", slug: m[1], town: m[2] };
  return null;
}

export function batch1Paths() {
  const out = [];
  for (const k of Object.keys(KEYWORDS)) for (const [t] of GM60) out.push(`/pages/keywords/${k}/${t}`);
  for (const s of Object.keys(SERVICES)) for (const [t] of GM60) out.push(`/pages/${s}/${t}`);
  return out;
}

// town-geo-slim matches the wrong Cheadle (Staffs) and Chorlton (Cheshire East) and has no row for the
// Tameside and Trafford localities, so these four use their Greater Manchester borough and centre point.
const GEO_FIX = {
  cheadle: { council: "Stockport", lat: 53.3936, lng: -2.2125, ocs: ["SK8"],
    lead: "Cheadle sits in the Stockport council area, west of Stockport town centre and close to Gatley, Cheadle Hulme and Manchester Airport. Addresses here start SK8. It is in the Cheadle parliamentary constituency." },
  chorlton: { council: "Manchester", lat: 53.4416, lng: -2.277, ocs: ["M21"],
    lead: "Chorlton (Chorlton-cum-Hardy) is in south Manchester, in the Manchester City Council area, between Stretford, Whalley Range and Didsbury. Addresses start M21. The Chorlton and Chorlton Park wards cover most of it." },
  tameside: { council: "Tameside", lat: 53.4806, lng: -2.081, ocs: ["OL6", "SK14", "SK15", "M34"],
    lead: "Tameside is the borough east of Manchester that takes in Ashton-under-Lyne, Hyde, Stalybridge, Denton, Droylsden, Dukinfield and Mossley. Postcodes include OL5, OL6, OL7, SK14, SK15, SK16, M34 and M43." },
  trafford: { council: "Trafford", lat: 53.4497, lng: -2.3519, ocs: ["M32", "M33", "M41", "WA14"],
    lead: "Trafford is the borough south-west of Manchester that includes Altrincham, Sale, Stretford, Urmston and Partington. Postcodes include M16, M32, M33, M41, WA14 and WA15." },
};
const YARD = { lat: 53.3925, lng: -2.1265 };
function fixFacts(slug, town, lab) {
  const f = GEO_FIX[slug];
  const mi = Math.round(miles(YARD, f));
  const paras = [f.lead, `${town} is about ${mi} miles ${bearing(YARD, f)} of our Offerton, Stockport SK2 5DE yard in a straight line, so ${lab} visits here are a normal day trip with travel included in the quote.`];
  const row = laCensus[f.council];
  if (row) {
    const [hh, det, semi, terr, flat, own, soc, priv, code] = row;
    const pc = (x) => (x == null ? "?" : `${Math.round(x * 10) / 10}%`);
    paras.push(`Across the whole ${f.council} council area (${Number(hh).toLocaleString("en-GB")} households, ${code}): detached ${pc(det)}, semi-detached ${pc(semi)}, terraced ${pc(terr)}, flats ${pc(flat)}; owned ${pc(own)}, social rented ${pc(soc)}, private rented ${pc(priv)} (ONS Census 2021).`);
  }
  return { html: paras.map((p) => `<p>${esc(p)}</p>`).join(""), faqs: [[`Do you cover ${list(f.ocs.slice(0, 3))} for ${lab}?`, `Yes. ${list(f.ocs)} are covered by our Stockport team, about ${mi} miles away. Send the postcode and a photo, or call 07517 806082.`]] };
}
function point(slug) { const f = GEO_FIX[slug]; if (f) return f; const g = geo[slug]; return g ? { council: g[0], lat: g[5], lng: g[6], ocs: g[2] || [] } : null; }
function miles(a, b) { const t = Math.PI / 180; const h = Math.sin((b.lat - a.lat) * t / 2) ** 2 + Math.cos(a.lat * t) * Math.cos(b.lat * t) * Math.sin((b.lng - a.lng) * t / 2) ** 2; return 2 * 3958.8 * Math.asin(Math.min(1, Math.sqrt(h))); }
const DIRS = ["north", "north-east", "east", "south-east", "south", "south-west", "west", "north-west"];
function bearing(a, b) { const t = Math.PI / 180; const y = Math.sin((b.lng - a.lng) * t) * Math.cos(b.lat * t); const x = Math.cos(a.lat * t) * Math.sin(b.lat * t) - Math.sin(a.lat * t) * Math.cos(b.lat * t) * Math.cos((b.lng - a.lng) * t); return DIRS[Math.round((((Math.atan2(y, x) / t) + 360) % 360) / 45) % 8]; }
export function gmNear(slug, limit = 12) {
  const here = point(slug); if (!here) return [];
  return GM60.filter(([s]) => s !== slug && point(s)).map(([s, n]) => { const p = point(s); return { slug: s, name: n, miles: miles(here, p), dir: bearing(here, p) }; })
    .filter((x) => x.miles >= 0.4).sort((a, b) => a.miles - b.miles).slice(0, limit);
}
function borough(slug) {
  const p = point(slug);
  const council = p && p.council ? p.council : "Greater Manchester";
  return council === GM_NAME.get(slug) ? "Greater Manchester" : council;
}

export function batch1Title(label, short, town) {
  for (const t of [`${label} ${town} | Same/Next-Day | iComply`, `${label} ${town} | Same/Next-Day`,
    `${short} ${town} | Same/Next-Day | iComply`, `${short} ${town} | Same/Next-Day`]) if (t.length <= 60) return t;
  return `${short} ${town} | Same/Next-Day`.slice(0, 60);
}

export function batch1Meta(label, town, gas, slug) {
  const b = borough(slug);
  const head = `${label} in ${town}, ${b}.`;
  const tail = " Free quote, call 07517 806082.";
  const mids = gas
    ? [" Carried out by Gas Safe registered engineers. Same/next-day visits from Stockport.", " Carried out by Gas Safe registered engineers, same/next-day visits.",
      " Carried out by Gas Safe registered engineers. Same/next-day.", " Carried out by Gas Safe registered engineers."]
    : [" Same/next-day visits from our Stockport team, written quote before work starts.", " Same/next-day visits from our Stockport team, written quote first.",
      " Same/next-day visits from our Stockport team, written quotes.", " Same/next-day visits from our Stockport team.", " Same/next-day visits from Stockport.", " Same/next-day visits."];
  let best = null;
  for (const m of mids) { const d = head + m + tail; if (d.length >= 140 && d.length <= 155) return d; if (d.length <= 155 && (!best || d.length > best.length)) best = d; }
  if (best && best.length < 140) {
    const pads = [" Homes and lets.", " For homes and lets.", " Homeowners and landlords.", " Local team.", " Local."];
    for (const p of pads) { const d = best.replace(tail, p + tail); if (d.length >= 140 && d.length <= 155) return d; }
  }
  return best || (head + tail);
}

function identity(m) {
  if (m.kind === "service") {
    const s = SERVICES[m.slug];
    return { label: s.label, short: s.label, fam: s.fam, noun: s.noun, svc: m.slug, gas: false, kw: null, hubHref: `/pages/services/${m.slug}`, hubLabel: s.label };
  }
  const k = KEYWORDS[m.slug];
  const svc = k.fam === "gas" ? "gas-systems" : k.hub;
  return { label: k.label, short: k.short, fam: k.fam, noun: lowerLabel(k.label), svc, gas: k.fam === "gas", kw: k, hubHref: `/pages/keywords/${m.slug}`, hubLabel: `${k.label} guide` };
}

const FAM_SERVICE = { gas: "gas-systems", "fire-doors": "fire-doors" };
function familyBank(fam) { return FAMILIES[fam] || FAMILIES.renovation; }

const decode = (t) => t.replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&lt;/g, "<").replace(/&gt;/g, ">");
export const CREDITS = Object.fromEntries(creditRows.filter((r) => /^https?:\/\//.test(r.source || "")).map((r) => [r.file, {
  artist: decode(String(r.artist || "").replace(/<\/li>/g, "; ").replace(/<[^>]+>/g, "")).replace(/\s+/g, " ").replace(/^[\s;]+|[\s;]+$/g, "") || "Unknown author",
  license: r.license || "", source: r.source,
}]));
export function creditHtml(src) {
  const c = CREDITS[src];
  if (!c) return "";
  return `<figcaption class="b1-credit">Example photo, not an iComply job. Photo: ${esc(c.artist)}, ${esc(c.license)}, via <a href="${esc(c.source)}" rel="nofollow noopener" target="_blank">Wikimedia Commons</a>. <a href="/pages/photo-credits">All photo credits</a></figcaption>`;
}

function imagesHtml(fam, label, town) {
  const set = photos[fam] || [];
  if (!set.length) return { html: "", og: `${ORIGIN}/assets/images/og-image.jpg`, n: 0 };
  const html = `<div class="b1-photos">${set.map((p, i) => `<figure><img src="${esc(p.src)}" alt="${esc(`${p.scene} – example photo, ${lowerLabel(label)} in ${town}`)}" width="1200" height="630"${i ? ' loading="lazy"' : ' fetchpriority="high"'}>${creditHtml(p.src)}</figure>`).join("")}</div>`;
  return { html, og: ORIGIN + set[0].src, n: set.length };
}

function linkList(items) { return `<ul class="b1-links">${items.map((i) => `<li><a href="${esc(i.href)}">${esc(i.label)}</a></li>`).join("")}</ul>`; }

const CSS = `<style>
.b1-cta{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0 14px}
.b1-cta a{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 20px;border-radius:8px;font-weight:700;font-size:1.05rem;text-decoration:none}
.b1-call{background:#FF6B00;color:#fff!important}
.b1-wa{background:#25D366;color:#073b1d!important}
.b1-sub{margin:0 0 12px;font-size:.95rem}
.b1-photos{display:grid;grid-template-columns:2fr 1fr;gap:8px;margin:0 0 18px}
.b1-photos img{width:100%;height:auto;border-radius:8px;object-fit:cover}
.b1-photos figure{margin:0}
.b1-photos figure:first-child{grid-row:span 2}
.b1-photos figure:only-child{grid-column:1/-1}
.b1-credit{font-size:.75rem;line-height:1.35;color:#444;margin:4px 2px 0}
.b1-credit a{color:#444;text-decoration:underline}
.b1-links{columns:2;gap:16px;padding-left:18px}
@media(min-width:900px){.b1-links{columns:3}}
.mx main h1{margin:.3em 0 .2em;font-size:clamp(1.6rem,4.6vw,2.4rem);line-height:1.15}
</style>`;

export function renderBatch1(m) {
  const town = GM_NAME.get(m.town);
  if (!town) return null;
  const id = identity(m);
  const path = m.kind === "keyword" ? `/pages/keywords/${m.slug}/${m.town}` : `/pages/${m.slug}/${m.town}`;
  const canonical = ORIGIN + path;
  const r = rng(hash(`${path}|b1`));
  const title = batch1Title(id.label, id.short, town);
  const description = batch1Meta(id.label, town, id.gas, m.town);
  const h1 = `${id.label} in ${town}`;
  const fam = familyBank(id.fam);
  const lab = lowerLabel(id.label);
  // Headings use the service noun ("Plastering"), not the trade noun ("Plasterers").
  const hn = id.kw ? lab : id.noun;
  const Hn = id.kw ? id.label : hn[0].toUpperCase() + hn.slice(1);
  const near = gmNear(m.town, 12);
  const nearNames = near.map((n) => n.name);
  const b = borough(m.town);
  const ocs = (point(m.town) || {}).ocs || [];

  // --- copy
  const intro = [];
  if (m.slug === "plasterboard-installation") {
    intro.push(PLASTERBOARD.intros[Math.floor(r() * PLASTERBOARD.intros.length)]);
    intro.push(PLASTERBOARD.boards[Math.floor(r() * PLASTERBOARD.boards.length)]);
    intro.push(PLASTERBOARD.methods[Math.floor(r() * PLASTERBOARD.methods.length)]);
    intro.push(PLASTERBOARD.finish[Math.floor(r() * PLASTERBOARD.finish.length)]);
  } else if (id.kw) {
    for (const p of id.kw.intro) intro.push(expand(p, r));
  }
  const openers = [
    `[Looking for|Need] ${lab} in ${town}? [We are a Stockport-based team|Our team is based in Stockport] and [work in|cover] ${town} and the rest of ${b === "Greater Manchester" ? "Greater Manchester" : `the ${b} area`} [every week|week in, week out].`,
    `[We do|We carry out] ${lab} for homeowners and landlords in ${town}${ocs.length ? `, [including|across] ${list(ocs.slice(0, 4))} postcodes` : ""}.`,
    `[Homeowners in ${town} call us for|In ${town}, people call us for] ${lab} [when they want a clear quote and a tidy job|because they want a written quote and a tidy finish].`,
  ];
  const lead = expand(openers[Math.floor(r() * openers.length)], r);
  const famParas = shuffle([...fam.paras, ...(FAMILY_EXTRA[id.fam] || [])], r).slice(0, 4).map((p) => expand(p, r));
  const generic = shuffle(GENERIC, r).slice(0, 7).map((p) => expand(p.replace(/\{s\}/g, lab).replace(/\{phone\}/g, PHONE), r));
  const why = expand(`[Why do people in ${town} choose us|What people in ${town} tell us they want] for ${lab}? [A written quote before anything starts, a start date that is kept, and a tidy site.|A clear written quote, a start date we keep and a clean finish.] [We are a small local team, so you deal with the same people from the first call to the last day.|You deal with the same small team from the first call to handover.]`, r);
  const scope = m.slug === "plasterboard-installation" ? PLASTERBOARD.scope[Math.floor(r() * PLASTERBOARD.scope.length)]
    : (id.kw && id.kw.scope.length ? id.kw.scope : fam.jobs);
  const jobs = shuffle(scope, r);
  const extraJobs = m.kind === "keyword" ? shuffle(fam.jobs, r).filter((j) => !jobs.includes(j)).slice(0, 3) : [];
  const dropStep = 1 + Math.floor(r() * 4);
  const steps = STEPS_COMMON.filter((_, i) => i !== dropStep).map((s) => expand(s, r));
  const quoteBits = shuffle(fam.quote, r);
  const poa = expand(`[Every job is priced on application (POA)|Prices are on application (POA)] because [no two ${lab} jobs are the same|${lab} jobs vary a lot]. What changes the price: ${list(quoteBits)}. [You get the price in writing before any work starts|The written quote comes before we start], and it does not change unless the job does.`, r);
  const localLine = near.length
    ? expand(`From ${town} we [also cover|regularly work in] ${list(nearNames.slice(0, 6))}. [The closest|Nearest] ${near[0].name} is about ${Math.max(1, Math.round(near[0].miles))} ${Math.max(1, Math.round(near[0].miles)) === 1 ? "mile" : "miles"} ${near[0].dir}.`, r)
    : "";
  const facts = GEO_FIX[m.town] ? fixFacts(m.town, town, lab) : townFactsBlock({ slug: m.town, town, subject: id.label, service: id.svc, names: GM_NAMES, hrefFor: null });
  // Homeowner trade pages: the HMO / RR(FS)O fire-duty line belongs on fire pages only.
  if (facts) {
    const drop = (t) => t.replace(/ ?Shared parts of blocks and HMOs carry fire-safety duties under the Regulatory Reform \(Fire Safety\) Order 2005 in England and Wales, or the equivalent law in Scotland\./g, "");
    facts.html = drop(facts.html);
    if (facts.faqs) facts.faqs = facts.faqs.map(([q, a]) => [drop(q), drop(a)]);
  }
  const homeTips = ([
    `[Before the visit|Ahead of the survey], [take a few photos of the area and any problem you can see|snap some photos of the job and any damage] and send them to ${PHONE} on WhatsApp. It helps us bring the right materials.`,
    `[If you rent the home|If you are a tenant], let your landlord know first. [If you are the landlord|Landlords], we can arrange access with your tenant.`,
    `We [agree working hours with you before the job starts|confirm working hours when we book the job], and [tell you if a job needs an early start|agree any early or late working with you].`,
  ].map((x) => expand(x, r)));

  // --- FAQs
  const faqs = [];
  if (m.slug === "plasterboard-installation") for (const f of shuffle(PLASTERBOARD.faqs, r).slice(0, 4)) faqs.push(f);
  if (id.kw) for (const f of id.kw.faqs) faqs.push(f);
  for (const f of shuffle(fam.faqs, r).slice(0, 2)) faqs.push(f);
  for (const f of shuffle(GENERIC_FAQS, r).slice(0, 3)) faqs.push(f);
  faqs.push([expand(`[Do you cover ${town} for ${lab}?|Is ${town} in your area?|Do you work in ${town}?]`, r), expand(`[Yes.|Yes, every week.] ${town} is [in the area we cover from Stockport|covered by our Stockport team]${near.length ? `, [along with|as are] ${list(nearNames.slice(0, 3))}` : ""}. [Call or WhatsApp ${PHONE} for a free quote.|For a free quote, call or WhatsApp ${PHONE}.]`, r)]);
  faqs.push([expand(`[How much does ${lab} cost in ${town}?|What does ${lab} cost?|Do you publish prices for ${lab}?]`, r), expand(`[Prices are on application.|We price each job on application (POA).] [We give you a written quote after seeing the job, at no cost.|After a free visit you get the price in writing.]`, r)]);
  faqs.push([expand(`[Can you come out the same or next day?|How quickly can you visit ${town}?]`, r), expand(`[Often, yes.|Usually the same or next day.] [Call ${PHONE} and we give you the earliest slot we have in ${town}.|Ring ${PHONE} and we offer the first free slot in ${town}.]`, r)]);
  if (facts) for (const f of facts.faqs) faqs.push(f);
  if (id.gas && !faqs.some(([q, a]) => /Gas Safe registered engineers/.test(a))) faqs.push(["Who carries out the gas work?", "All gas work is carried out by Gas Safe registered engineers."]);
  const faqUse = faqs.slice(0, 12);

  // --- links (unique hrefs)
  const seen = new Set([path]);
  const take = (items) => items.filter((i) => (seen.has(i.href) ? false : (seen.add(i.href), true)));
  const hrefFor = (t) => (m.kind === "keyword" ? `/pages/keywords/${m.slug}/${t}` : `/pages/${m.slug}/${t}`);
  const nearLinks = take(near.slice(0, 10).map((n) => ({ href: hrefFor(n.slug), label: `${id.label} in ${n.name} (${Math.max(1, Math.round(n.miles))} mi ${n.dir})` })));
  const allTown = take(GM60.map(([s, n]) => ({ href: hrefFor(s), label: `${n}` })));
  const siblingSvcs = take(Object.entries(SERVICES).map(([s, v]) => ({ href: `/pages/${s}/${m.town}`, label: `${v.label} in ${town}` })));
  const famKws = Object.entries(KEYWORDS).filter(([s, k]) => s !== m.slug && (k.fam === id.fam || (id.fam === "plastering" && k.fam === "rendering")));
  const otherKws = Object.entries(KEYWORDS).filter(([s, k]) => s !== m.slug && !famKws.some(([x]) => x === s));
  const kwLinks = take([...famKws, ...shuffle(otherKws, r).slice(0, Math.max(0, 10 - famKws.length))].slice(0, 12).map(([s, k]) => ({ href: `/pages/keywords/${s}/${m.town}`, label: `${k.label} in ${town}` })));
  const hubs = take([
    { href: id.hubHref, label: id.hubLabel },
    { href: `/pages/services/${id.svc}`, label: `${SERVICES[id.svc] ? SERVICES[id.svc].label : id.label} – service page` },
    { href: `/pages/areas/${m.town}`, label: `All services in ${town}` },
    { href: "/pages/jobs/eicr", label: "EICR from £160" },
    { href: "/pages/services/gas-systems", label: "Gas services" },
    { href: "/pages/services/landlord-compliance", label: "Landlord compliance" },
    { href: "/pages/services", label: "All services" },
    { href: "/pages/keywords", label: "Service guides" },
    { href: "/pages/areas", label: "Areas we cover" },
    { href: "/contact.php", label: "Contact us" },
    { href: "/", label: "iComply home" },
  ]);

  // --- sections
  const img = imagesHtml(id.fam, id.label, town);
  const secJobs = `<section><h2>${esc(`${Hn} jobs we do in ${town}`)}</h2><p>${esc(lead)}</p>${intro.map((p) => `<p>${esc(p)}</p>`).join("")}<ul>${[...jobs, ...extraJobs].map((j) => `<li>${esc(j)}</li>`).join("")}</ul>${[famParas[0], generic[5], famParas[1]].map((p) => `<p>${esc(p)}</p>`).join("")}</section>`;
  const secRuns = `<section><h2>${esc(`How a ${hn} job runs`)}</h2><ol>${steps.map((s) => `<li>${esc(s)}</li>`).join("")}</ol>${shuffle([...famParas.slice(2), generic[0], generic[1], generic[6], homeTips[0], `${homeTips[1]} ${homeTips[2]}`], r).map((p) => `<p>${esc(p)}</p>`).join("")}</section>`;
  const secAreas = `<section><h2>${esc(`Areas near ${town} we cover`)}</h2>${localLine ? `<p>${esc(localLine)}</p>` : ""}${facts ? facts.html.replace(/<section id="town-facts"><h2>[^<]*<\/h2>/, "").replace(/<\/section>$/, "") : ""}${linkList(nearLinks)}<h3>${esc(`${Hn}${id.kw ? "" : " work"} across Greater Manchester`)}</h3>${linkList(allTown)}</section>`;
  const secFaq = `<section id="faq"><h2>${esc(`${Hn} FAQs – ${town}`)}</h2>${faqUse.map(([q, a]) => `<details class="faq-item" open><summary>${esc(q)}</summary><p>${esc(a)}</p></details>`).join("")}</section>`;
  const secQuote = `<section><h2>${esc(`Get a ${hn} quote`)}</h2><p>${esc(poa)}</p>${shuffle([generic[2], generic[3], generic[4], why], r).map((p) => `<p>${esc(p)}</p>`).join("")}<p>${esc(`Call or WhatsApp ${PHONE}, or email icomplypropertyservices@gmail.com with your ${town} address, a short description and a few photos.`)}</p><div class="b1-cta"><a class="b1-call" href="tel:07517806082">Call ${PHONE}</a><a class="b1-wa" href="https://wa.me/447517806082">WhatsApp us</a></div><h3>${esc(`Other building work in ${town}`)}</h3>${linkList(siblingSvcs)}${kwLinks.length ? `<h3>${esc(`Related jobs in ${town}`)}</h3>${linkList(kwLinks)}` : ""}<h3>Useful pages</h3>${linkList(hubs)}</section>`;
  const middle = r() < 0.5 ? [secRuns, secAreas] : [secRuns, secAreas];

  const crumbs = `<nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a> › <a href="${esc(id.hubHref)}">${esc(id.hubLabel)}</a> › <a href="/pages/areas/${esc(m.town)}">${esc(town)}</a></nav>`;
  const schema = { "@context": "https://schema.org", "@graph": [
    { "@type": "BreadcrumbList", itemListElement: [
      { "@type": "ListItem", position: 1, name: "Home", item: `${ORIGIN}/` },
      { "@type": "ListItem", position: 2, name: id.hubLabel, item: ORIGIN + id.hubHref },
      { "@type": "ListItem", position: 3, name: h1, item: canonical } ] },
    { "@type": "Service", name: h1, serviceType: id.label, areaServed: { "@type": "City", name: town }, description,
      provider: { "@type": "LocalBusiness", "@id": `${ORIGIN}/#business`, name: "iComply Property Services", url: `${ORIGIN}/`, telephone: "+447517806082", email: "icomplypropertyservices@gmail.com",
        address: { "@type": "PostalAddress", streetAddress: "17 Woodlands Park Road, Offerton", addressLocality: "Stockport", postalCode: "SK2 5DE", addressCountry: "GB" } } },
    { "@type": "FAQPage", mainEntity: faqUse.map(([q, a]) => ({ "@type": "Question", name: q, acceptedAnswer: { "@type": "Answer", text: a } })) },
  ] };

  const html = `<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(title)}</title>
<meta name="description" content="${esc(description)}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="${canonical}">
<meta property="og:title" content="${esc(title)}">
<meta property="og:description" content="${esc(description)}">
<meta property="og:url" content="${canonical}">
<meta property="og:type" content="website">
<meta property="og:image" content="${esc(img.og)}">
${MATRIX_CSS}
${CSS}
<script type="application/ld+json">${JSON.stringify(schema).replace(/</g, "\\u003c")}</script>
</head>
<body class="mx">
${MATRIX_HEADER}
<main>
${crumbs}
<h1>${esc(h1)}</h1>
<div class="b1-cta" data-b1="hero-cta"><a class="b1-call" href="tel:07517806082">Call ${PHONE}</a><a class="b1-wa" href="https://wa.me/447517806082">WhatsApp us</a></div>
<p class="b1-sub">${esc(`Same/next-day visits in ${town}. Free written quote, prices on application.`)}</p>
${img.html}
<article id="local-copy">
${secJobs}
${middle.join("\n")}
${secFaq}
${secQuote}
</article>
${MATRIX_FOOTER}
</main>
${MATRIX_SITE_FOOTER}
</body>
</html>`;
  return { html, title, description, h1, canonical, robots: "index, follow", path, status: 200, photos: img.n };
}
