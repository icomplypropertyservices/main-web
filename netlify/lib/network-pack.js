/**
 * Networking, data cabling and mesh Wi-Fi pack: town pages (lock 2026-10-20).
 *   /pages/keywords/{kw}/{town}  56 new keywords x the 60 Greater Manchester towns
 *   /pages/jobs/{job}/{town}     29 jobs x the 60 Greater Manchester towns
 *   /pages/{service}/{town}      networking, structured-cabling, wifi x the 269 dual-ring towns
 * Copy and lists: website/data/network-pack.json (bin/build-network-pack.py).
 * Every page: index, follow, self-canonical, >= 800 prose words, FAQPage +
 * Service schema, 3 real photos, >= 85 unique internal links. Price on
 * application; Same/Next-Day; "works with your Sky or BT hub" only.
 */
import pack from "../../website/data/network-pack.json" with { type: "json" };
import { geoRow } from "./town-depth.js";
import laCensus from "./la-census-slim.json" with { type: "json" };
import { MATRIX_CSS, MATRIX_HEADER, MATRIX_FOOTER, MATRIX_SITE_FOOTER } from "./page-shell.js";

const BASE = "https://icomplypropertyservices.co.uk";
const YARD = { lat: 53.3925, lng: -2.1265 };
const DIRS = ["north", "north-east", "east", "south-east", "south", "south-west", "west", "north-west"];
const GM = new Set(pack.gm60);
const DUAL = new Set(pack.dual269);
const SERVICES = pack.services;
const MONEY = [
  ["/pages/keywords/eicr", "EICR"],
  ["/pages/services/fire-alarms", "Fire alarms"],
  ["/pages/services/emergency-lighting", "Emergency lighting"],
  ["/pages/services/gas-systems", "Gas safety"],
  ["/pages/aov", "AOV and smoke control"],
];
const SECURITY = [
  ["/pages/services/cctv", "CCTV"],
  ["/pages/services/door-entry", "Door entry"],
  ["/pages/services/intercoms", "Intercoms"],
  ["/pages/services/access-control", "Access control"],
];
const LIVE_NAMES = {
  "data-cabling-installation": "Data cabling installation",
  "cctv-switch-and-poe-check": "CCTV switch and PoE check",
  "cctv-cabling-installation": "CCTV cabling installation",
  "cctv-after-network-change": "CCTV after a network change",
  "door-entry-cabling": "Door entry cabling",
  "intercom-cabling": "Intercom cabling",
  "intercom-cabling-installation": "Intercom cabling installation",
};

function esc(v) {
  return String(v).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}
function hash(s) {
  let h = 2166136261;
  for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 16777619); }
  h ^= h >>> 15; h = Math.imul(h, 0x2c1b3c6d); h ^= h >>> 12;
  return h >>> 0;
}
function fill(t, town, subject) {
  return t.replaceAll("{town}", town).replaceAll("{subject}", subject);
}
function pickSlots(slots, key, town, subject) {
  return slots.map((alts, i) => fill(alts[hash(`${key}|${i}`) % alts.length], town, subject)).join(" ");
}
function shuffle(list, key) {
  return list.map((x, i) => [hash(`${key}|${i}`), x]).sort((a, b) => a[0] - b[0]).map((r) => r[1]);
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
  return DIRS[Math.round((((Math.atan2(y, x) / t) + 360) % 360) / 45) % 8];
}
function list(items, and = "and") {
  const a = items.filter(Boolean);
  if (a.length <= 1) return a[0] || "";
  return `${a.slice(0, -1).join(", ")} ${and} ${a[a.length - 1]}`;
}
// Lower-case the first letter unless the word is an acronym or model (CCTV, Cat6, HMO, PoE, Wi-Fi).
function lc(s) {
  return /^[A-Z][a-z]+\b/.test(s) && !/^(Cat|Wi)/.test(s) ? s[0].toLowerCase() + s.slice(1) : s;
}
function townName(slug) {
  return (pack.towns[slug] && pack.towns[slug].name) || slug.replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
}
function point(slug) {
  const g = geoRow(slug);
  return g ? { lat: g[5], lng: g[6] } : null;
}
const NEAR_CACHE = new Map();
function nearest(slug, pool, limit) {
  const key = `${slug}|${pool === GM ? "gm" : "dual"}|${limit}`;
  if (NEAR_CACHE.has(key)) return NEAR_CACHE.get(key);
  const here = point(slug);
  const rows = [];
  for (const s of pool) {
    if (s === slug) continue;
    const p = point(s);
    rows.push({ slug: s, m: here && p ? miles(here, p) : 999, dir: here && p ? bearing(here, p) : "" });
  }
  rows.sort((a, b) => a.m - b.m || a.slug.localeCompare(b.slug));
  const out = rows.slice(0, limit);
  NEAR_CACHE.set(key, out);
  return out;
}

/** Which pack page a path is, or null. */
export function networkPackMatch(path) {
  let m = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && pack.keywords[m[1]]) return { kind: "keyword", slug: m[1], town: m[2], ok: GM.has(m[2]) };
  m = path.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && pack.jobs[m[1]]) return { kind: "job", slug: m[1], town: m[2], ok: GM.has(m[2]) };
  m = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && SERVICES[m[1]]) return { kind: "service", slug: m[1], town: m[2], ok: DUAL.has(m[2]) };
  return null;
}

/** Hub to 301 to when a pack slug is asked for a town outside its set. */
export function networkPackHubFor(match) {
  if (!match) return null;
  if (match.kind === "keyword") return `/pages/keywords/${match.slug}`;
  if (match.kind === "job") return `/pages/jobs/${match.slug}`;
  return `/pages/services/${match.slug}`;
}

function factsParagraphs(slug, town, subject, service, key) {
  const g = geoRow(slug);
  const out = [];
  if (!g) {
    out.push(`We cover ${town} from our Offerton, Stockport yard, with travel included in the written quote.`);
    return out;
  }
  const [council, county, ocs, wards, cons, lat, lng, census, districts] = g;
  const mi = Math.max(1, Math.round(miles(YARD, { lat, lng })));
  const dir = bearing(YARD, { lat, lng });
  let lead = council ? `${town} sits in the ${council} council area${county && county !== council ? `, ${county}` : ""}.` : `${town} is in ${county || "the North West"}.`;
  if (ocs && ocs.length) {
    lead += [
      ` Postcode districts around the centre include ${list(ocs)}.`,
      ` Addresses here mostly start ${list(ocs, "or")}.`,
      ` Send a postcode starting ${list(ocs, "or")} and we can place the building.`,
    ][hash(`${key}|oc`) % 3];
  }
  if (cons) lead += ` Parliamentary constituency: ${cons}.`;
  out.push(lead);
  const dl = Array.isArray(districts) ? districts.filter((d) => d && d[0] && d[1] && d[1].length) : [];
  if (dl.length) out.push(dl.slice(0, 4).map(([oc, ws]) => `${oc} takes in ${list(ws.slice(0, 5))}.`).join(" "));
  else if (wards && wards.length) out.push(`Wards near the centre include ${list(wards.slice(0, 6))}.`);
  out.push(mi <= 3
    ? `Our yard at Offerton, Stockport SK2 5DE is close by, so ${lc(subject)} work in ${town} fits easily around other local jobs.`
    : `${town} is about ${mi} miles ${dir} of our Offerton yard in a straight line. ${mi <= 25 ? "That is a normal day visit for us, with travel included in the quote." : "We plan visits at that distance into set days, with travel included in the quote."}`);
  if (Array.isArray(census)) {
    const [flats, social] = census;
    if (flats != null) {
      out.push(flats >= 25
        ? `Flats make up ${flats}% of homes in ${town} (ONS Census 2021), so much of our ${lc(SERVICES[service].name)} work here is in blocks and converted houses, where risers, comms cupboards and shared door entry decide the cable routes.`
        : `Flats are ${flats}% of homes in ${town} (ONS Census 2021); most requests here come from houses, home offices, shops and HMOs, where the hub position and solid walls decide how far Wi-Fi reaches.`);
    }
    if (social != null) out.push(`Social renting accounts for ${social}% of households in ${town} (ONS Census 2021). For landlords and agents we keep tenant broadband separate from building systems and record who owns each line.`);
  }
  const la = council ? (laCensus[council] || laCensus[council.replace(/^(The )?City of /, "")]) : null;
  if (la) {
    const [hh, det, semi, terr, flat, own, soc, priv, code] = la;
    const pc = (x) => (x == null ? "?" : `${Math.round(x * 10) / 10}%`);
    out.push(`Across the whole ${council} council area (${Number(hh).toLocaleString("en-GB")} households, ${code}): detached ${pc(det)}, semi-detached ${pc(semi)}, terraced ${pc(terr)} and flats ${pc(flat)}; owned ${pc(own)}, social rented ${pc(soc)} and private rented ${pc(priv)} (ONS Census 2021). ${terr != null && terr >= 30 ? "Terraces mean long, narrow houses with the hub at the front, which is why rear rooms and kitchen extensions so often lose Wi-Fi." : "A mix like this means everything from semis with loft conversions to purpose-built flats, each with its own cable routes."}`);
  }
  return out;
}

function linkList(title, items) {
  if (!items.length) return "";
  return `<section class="np-links"><h2>${esc(title)}</h2><ul class="np-link-list">${items.map(([h, t]) => `<li><a href="${esc(h)}">${esc(t)}</a></li>`).join("")}</ul></section>`;
}

function linkBlocks(kind, slug, townSlug, town, service) {
  const blocks = [];
  const pool = kind === "service" ? DUAL : GM;
  const near = nearest(townSlug, pool, 16);
  const selfBase = kind === "keyword" ? `/pages/keywords/${slug}` : kind === "job" ? `/pages/jobs/${slug}` : `/pages/${slug}`;
  const label = kind === "service" ? SERVICES[slug].name : (kind === "keyword" ? pack.keywords[slug] : pack.jobs[slug]).name;
  blocks.push(linkList(`${label} in nearby towns`, near.map((n) => [`${selfBase}/${n.slug}`, `${label} in ${townName(n.slug)}${n.m < 999 ? ` (${Math.max(1, Math.round(n.m))} mi ${n.dir})` : ""}`])));
  const svc = Object.keys(SERVICES).flatMap((s) => [[`/pages/services/${s}`, SERVICES[s].name], [`/pages/${s}/${townSlug}`, `${SERVICES[s].name} in ${town}`]]);
  blocks.push(linkList("Networking, cabling and Wi-Fi", [...svc, ...SECURITY]));
  const kws = Object.keys(pack.keywords).filter((k) => k !== slug);
  const sameSvc = kws.filter((k) => pack.keywords[k].service === service);
  const otherSvc = kws.filter((k) => pack.keywords[k].service !== service);
  const ordered = [...shuffle(sameSvc, `${townSlug}|${slug}|sib`), ...shuffle(otherSvc, `${townSlug}|${slug}|oth`)];
  if (GM.has(townSlug)) {
    blocks.push(linkList(`More guides for ${town}`, ordered.slice(0, 20).map((k) => [`/pages/keywords/${k}/${townSlug}`, `${pack.keywords[k].name} in ${town}`])));
    const jobs = shuffle(Object.keys(pack.jobs).filter((j) => j !== slug), `${townSlug}|${slug}|jobs`).slice(0, 8);
    if (kind === "keyword" && pack.jobs[slug]) jobs.unshift(slug);
    blocks.push(linkList(`Jobs in ${town}`, jobs.map((j) => [`/pages/jobs/${j}/${townSlug}`, `${pack.jobs[j].name} job in ${town}`])));
    if (kind === "job") blocks.push(linkList("Guide for this job", [[`/pages/keywords/${slug}/${townSlug}`, `${label} guide for ${town}`], [`/pages/keywords/${slug}`, `${label} guide`]]));
  } else {
    blocks.push(linkList("Guides", ordered.slice(0, 20).map((k) => [`/pages/keywords/${k}`, pack.keywords[k].name])));
    blocks.push(linkList("Jobs", shuffle(Object.keys(pack.jobs), `${townSlug}|${slug}|jobs`).slice(0, 8).map((j) => [`/pages/jobs/${j}`, `${pack.jobs[j].name} job`])));
  }
  const used = new Set(GM.has(townSlug) ? [] : ordered.slice(0, 20));
  const hubs = shuffle(Object.keys(pack.keywords).filter((k) => k !== slug && !used.has(k)), `${slug}|hubs|${townSlug}`).slice(0, 10);
  blocks.push(linkList("Guide hubs", hubs.map((k) => [`/pages/keywords/${k}`, `${pack.keywords[k].name} guide`])));
  blocks.push(linkList("Existing cabling guides", Object.keys(LIVE_NAMES).map((k) => [`/pages/keywords/${k}/${townSlug}`, `${LIVE_NAMES[k]} in ${town}`]).filter(() => DUAL.has(townSlug))));
  const areas = [[`/pages/areas/${townSlug}`, `Property services in ${town}`], ...nearest(townSlug, DUAL, 3).map((n) => [`/pages/areas/${n.slug}`, `Property services in ${townName(n.slug)}`])];
  blocks.push(linkList("Area hubs", areas));
  blocks.push(linkList("Compliance services", MONEY));
  return blocks.join("");
}

export function renderNetworkPackTown({ kind, slug, town: townSlug }) {
  const match = networkPackMatch(kind === "keyword" ? `/pages/keywords/${slug}/${townSlug}` : kind === "job" ? `/pages/jobs/${slug}/${townSlug}` : `/pages/${slug}/${townSlug}`);
  if (!match || !match.ok) return null;
  const town = townName(townSlug);
  const angle = kind === "keyword" ? pack.keywords[slug] : kind === "job" ? pack.jobs[slug] : null;
  const service = angle ? angle.service : slug;
  const svcName = SERVICES[service].name;
  const subject = angle ? angle.name : svcName;
  const key = `${kind}|${slug}|${townSlug}`;
  const path = kind === "keyword" ? `/pages/keywords/${slug}/${townSlug}` : kind === "job" ? `/pages/jobs/${slug}/${townSlug}` : `/pages/${slug}/${townSlug}`;
  const canonical = BASE + path;
  const hubHref = networkPackHubFor(match);

  // Lead
  const leadSlots = angle ? angle.lead : null;
  let leadText;
  if (angle) leadText = angle.lead.map((slot, i) => pickSlots([slot], `${key}|lead${i}`, town, subject)).join(" ");
  else {
    const svcLeads = {
      networking: [["Networking in {town} covers new installs, office and HMO networks, switches, comms cabinets and fault finding.", "We install and fix networks in {town} homes, offices, shops and shared buildings.", "For {town} landlords, businesses and homeowners we plan, cable and support networks that stay up."]],
      "structured-cabling": [["Data cabling in {town}: Cat5e, Cat6, Cat6a and fibre, data points, patch panels and PoE runs.", "We run structured data cabling in {town} buildings, tested and labelled at both ends.", "From one extra data point to a full office, we cable {town} properties properly."]],
      wifi: [["Wi-Fi and mesh Wi-Fi in {town}: dead-spot fixes, access points and CCTV or door entry that works with your Sky or BT hub.", "We fix weak Wi-Fi in {town} homes, HMOs and offices with mesh, wired backhaul and ceiling access points.", "Wi-Fi in {town} that reaches every room, measured first and fixed at the cause."]],
    };
    leadText = pickSlots(svcLeads[service], `${key}|lead`, town, subject);
  }
  leadText += " " + pickSlots([[
    `We are based at 17 Woodlands Park Road, Offerton, Stockport SK2 5DE, and same-day and next-day visits to ${town} are available.`,
    `Our engineers work from Offerton, Stockport, and ${town} visits can usually be booked same-day or next-day.`,
    `${town} is covered from our Stockport base, with same-day and next-day slots for most jobs.`,
  ], [
    "Price is on application, confirmed in writing before work starts.",
    "Every job is price on application, with a written quote first.",
    "Pricing is on application once the scope is agreed.",
  ]], `${key}|base`, town, subject);

  const sections = [];
  if (kind === "job") {
    const stepsLead = [
      `What happens on a ${lc(subject)} job in ${town}`,
      `${subject} in ${town}: the visit`,
      `The ${lc(subject)} job, step by step`,
    ][hash(`${key}|steps-h`) % 3];
    const close = pickSlots([[
      "Before any extra work we explain what we found and what it would take.",
      "If we find more than expected, we stop and explain before going further.",
      "Anything outside the agreed scope is discussed and quoted before it is done.",
    ], [
      `You get the test results and settings written down, so the next visit to the ${town} property starts from a record.`,
      "Results, labels and admin details are handed over at the end.",
      "We leave a written record of what was done, tested and changed.",
    ]], `${key}|steps`, town, subject);
    sections.push(`<section id="np-job"><h2>${esc(stepsLead)}</h2><p>${esc(fill(angle.steps.join(" "), town, subject))} ${esc(close)}</p></section>`);
  }
  const primary = angle ? angle.topics : pack.service_topics[service].slice(0, 3);
  const topicIds = [...new Set([...primary, ...pack.service_topics[service]])];
  const topicSections = topicIds.map((tid) => {
    const t = pack.topics[tid];
    const full = primary.includes(tid);
    const h2 = fill(t.h2[hash(`${key}|${tid}|h2`) % t.h2.length], town, subject);
    // The Sky/BT topic always keeps its first paragraph ("works with your Sky or BT hub").
    const paras = full ? t.paras : [t.paras[tid === "sky-bt" ? 0 : hash(`${key}|${tid}|one`) % t.paras.length]];
    return `<section class="np-topic" id="np-${tid}"><h2>${esc(h2)}</h2>${paras.map((p, j) => `<p>${esc(pickSlots(p, `${key}|${tid}|${j}`, town, subject))}</p>`).join("")}</section>`;
  });
  const facts = factsParagraphs(townSlug, town, subject, service, key);
  const factsH = [`${town}: buildings, postcodes and travel`, `Working in ${town} and nearby`, `${subject} around ${town}: local notes`][hash(`${key}|fh`) % 3];
  const factsSection = `<section id="np-town"><h2>${esc(factsH)}</h2>${shuffle(facts, `${key}|facts`).map((p) => `<p>${esc(p)}</p>`).join("")}</section>`;
  const body = shuffle([...topicSections, factsSection], `${key}|order`);

  // FAQs
  const faqs = [];
  if (angle) for (const [q, a] of angle.faqs) faqs.push([fill(q, town, subject), fill(a, town, subject)]);
  else for (const [q, a] of pack.service_faqs[service]) faqs.push([q, a]);
  const topicFaqs = topicIds.flatMap((tid) => (pack.topic_faqs[tid] || []).map((f) => [tid, f]));
  for (const [, [q, a]] of shuffle(topicFaqs, `${key}|faq`).slice(0, 4)) faqs.push([q, a]);
  faqs.push([`Do you cover ${town}?`, `Yes. ${town} is in our working area from Offerton, Stockport, with same-day and next-day visits available. Call or WhatsApp 07517 806082 or email icomplypropertyservices@gmail.com.`]);
  const faqHtml = `<section id="faq" class="faq"><h2>${esc(`${subject} in ${town}: questions`)}</h2>${shuffle(faqs, `${key}|faqorder`).map(([q, a]) => `<details class="faq-item"><summary>${esc(q)}</summary><p>${esc(a)}</p></details>`).join("")}</section>`;

  const focus = angle ? angle.focus.map((f) => fill(f, town, subject)) : ["Survey and written quote first", "Same/Next-Day visits", "Tested, labelled and documented"];
  const imgs = SERVICES[service].image;
  const imgOrder = shuffle(imgs, `${key}|img`);
  const alt = `${subject} in ${town}`;
  const imagesHtml = `<figure class="quality-bar-images np-images">${imgOrder.map((b, i) => `<img src="/assets/images/services/${b}.jpg" alt="${esc(alt)} photo ${i + 1}" width="640" height="420" loading="${i ? "lazy" : "eager"}">`).join("")}<figcaption>Photos: Wikimedia Commons, used under their CC licences.</figcaption></figure>`;

  const h1 = [
    `${subject} in ${town} and nearby`,
    `${subject} for ${town} homes, landlords and businesses`,
    `${subject} in ${town}: survey, install and test`,
  ][hash(`${key}|h1`) % 3];
  const titleRaw = kind === "job" ? `${subject} job in ${town} | iComply` : `${subject} in ${town} | iComply`;
  const title = titleRaw.length > 65 ? `${subject} in ${town}` : titleRaw;
  const description = `${subject} in ${town} from our Stockport base. ${service === "wifi" ? "Works with your Sky or BT hub. " : ""}Same/Next-Day visits, price on application. Call 07517 806082.`.slice(0, 300);
  const crumbs = [["/", "Home"], [hubHref, subject], [`/pages/areas/${townSlug}`, town]];
  const crumbHtml = `<nav class="mx-crumbs" aria-label="Breadcrumb">${crumbs.map(([h, t]) => `<a href="${esc(h)}">${esc(t)}</a>`).join(" › ")} › <span>${esc(`${subject} in ${town}`)}</span></nav>`;
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      { "@type": "BreadcrumbList", itemListElement: [...crumbs.map(([h, t], i) => ({ "@type": "ListItem", position: i + 1, name: t, item: BASE + h })), { "@type": "ListItem", position: 4, name: `${subject} in ${town}`, item: canonical }] },
      {
        "@type": "Service", name: `${subject} in ${town}`, serviceType: SERVICES[service].label, areaServed: town, description, url: canonical,
        provider: { "@type": "LocalBusiness", "@id": `${BASE}/#business`, name: "iComply Property Services", url: `${BASE}/`, telephone: "+447517806082", email: "icomplypropertyservices@gmail.com",
          address: { "@type": "PostalAddress", streetAddress: "17 Woodlands Park Road, Offerton", addressLocality: "Stockport", postalCode: "SK2 5DE", addressCountry: "GB" } },
      },
      { "@type": "FAQPage", mainEntity: faqs.map(([q, a]) => ({ "@type": "Question", name: q, acceptedAnswer: { "@type": "Answer", text: a } })) },
    ],
  };
  const og = `<meta property="og:type" content="website"><meta property="og:title" content="${esc(title)}"><meta property="og:description" content="${esc(description)}"><meta property="og:url" content="${canonical}"><meta property="og:image" content="${BASE}/assets/images/services/${imgOrder[0]}.jpg"><meta name="twitter:card" content="summary_large_image">`;
  const html = `<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(title)}</title>
<meta name="description" content="${esc(description)}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="${canonical}">
${og}
${MATRIX_CSS}
<style>.np-images{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin:1rem 0}.np-images img{width:100%;height:auto;aspect-ratio:16/10;object-fit:cover;border-radius:8px}.np-images figcaption{grid-column:1/-1;font-size:.75rem;color:#64748b}.np-link-list{columns:2;column-gap:1.5rem}@media(max-width:640px){.np-images{grid-template-columns:1fr}.np-link-list{columns:1}}</style>
<script type="application/ld+json">${JSON.stringify(schema).replace(/</g, "\\u003c")}</script>
</head>
<body class="mx">
${MATRIX_HEADER}
<main>
${crumbHtml}
<h1>${esc(h1)}</h1>
${imagesHtml}
<article id="local-copy">
<section id="np-lead"><p>${esc(leadText)}</p></section>
${sections.join("")}
${body.join("")}
<ul>${focus.map((f) => `<li>${esc(f)}</li>`).join("")}</ul>
${linkBlocks(kind, slug, townSlug, town, service)}
${faqHtml}
</article>
${MATRIX_FOOTER}
</main>
${MATRIX_SITE_FOOTER}
</body>
</html>`;
  return { html, title, description, h1, canonical, robots: "index, follow", indexable: true, path, status: 200, words: html.replace(/<[^>]+>/g, " ").split(/\s+/).filter(Boolean).length };
}
