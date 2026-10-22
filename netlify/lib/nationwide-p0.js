import { keywordTwinIntent } from "./keyword-twin.js";
import { MATRIX_CSS, MATRIX_HEADER, MATRIX_FOOTER, MATRIX_SITE_FOOTER } from "./page-shell.js";
/**
 * Nationwide P0 keyword × town HTML for the edge renderer.
 * Town set is UK TOP 5000. GM towns stay even when they are outside that list.
 */
import family from "../../website/data/nationwide-3line-p0.json" with { type: "json" };
import keywordPack from "../../website/data/nationwide-3line-p0-keywords.json" with { type: "json" };
import townDoc from "../../website/data/uk-top5000-towns.json" with { type: "json" };
import { gmTownName, isGmTown } from "./link-blocks.js";
import { townFactsBlock } from "./town-facts.js";
import { geoRow, townDepthBlock } from "./town-depth.js";
import { depthBankParagraphs } from "./thin-quality-bar.js";
import townGeo from "../../website/data/town-geo-slim.json" with { type: "json" };

const P0 = new Map((family.hubs || []).map((hub) => [hub.slug, hub]));
const TOWNS = new Map((townDoc.towns || []).filter((town) => town && town.slug).map((town) => [town.slug, town]));
const NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE";
const ORIGIN = "https://icomplypropertyservices.co.uk";

const PARENTS = {
  "aov-air-handling": ["/pages/services/aov-air-handling", "/pages/aov"],
  barriers: ["/pages/services/barriers", "/pages/barriers"],
  "fire-alarms": ["/pages/services/fire-alarms"],
  "emergency-lighting": ["/pages/services/emergency-lighting", "/pages/services/fire-alarms"],
  "fire-doors": ["/pages/services/fire-doors", "/pages/services/fire-alarms"],
  "fire-extinguishers": ["/pages/services/fire-extinguishers", "/pages/services/fire-alarms"],
  "fire-risk-assessments": ["/pages/services/fire-risk-assessments", "/pages/services/fire-alarms"],
  "fire-stopping": ["/pages/services/fire-stopping", "/pages/services/fire-alarms"],
  "fire-suppression": ["/pages/services/fire-suppression", "/pages/services/fire-alarms"],
  "sprinkler-systems": ["/pages/services/sprinkler-systems", "/pages/services/fire-alarms"],
};

export function isNationwideP0(slug) {
  return P0.has(slug);
}

export function p0KeepsTown(keyword, town) {
  return P0.has(keyword) && (TOWNS.has(town) || isGmTown(town));
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function byteLength(value) {
  return new TextEncoder().encode(value).length;
}

function fitMeta(text) {
  let value = String(text).replace(/\s+/g, " ").trim();
  // PHP strlen() counts UTF-8 bytes. An em dash is 3 bytes; swap it only when it exceeds 160.
  if (byteLength(value) > 160 && value.includes("—")) {
    value = value.replace("—", "-");
  }
  if (byteLength(value) > 160) {
    const bytes = new TextEncoder().encode(value);
    let end = 160;
    while (end > 0 && (bytes[end] & 0b11000000) === 0b10000000) end -= 1;
    let cut = new TextDecoder().decode(bytes.slice(0, end));
    const space = cut.lastIndexOf(" ");
    if (space > 40) cut = cut.slice(0, space);
    value = cut.replace(/[ .,;:—-]+$/, "");
    const extra = " Call 07517 806082.";
    if (!value.endsWith("806082.") && byteLength(value) + byteLength(extra) <= 160) value += extra;
  }
  for (const pad of [" Book today.", " Free written quote.", " Certificates emailed.", " Call 07517 806082."]) {
    if (byteLength(value) >= 140 && value.length >= 140) break;
    if (value.toLowerCase().includes(pad.trim().toLowerCase().replace(/\.$/, ""))) continue;
    if (byteLength(value) + byteLength(pad) <= 160) value += pad;
  }
  return value;
}

export function p0Title(name, town) {
  const lead = town ? `${name} in ${town}` : name;
  for (const t of [`${lead} | Same/Next-Day Engineers`, `${lead} | Same/Next-Day`, `${lead} | iComply`]) {
    if (t.length <= 60) return t;
  }
  return `${lead.slice(0, 50).replace(/\s+\S*$/, "")} | iComply`;
}

export function p0Description(name, place) {
  let text = `${name} in ${place}. Same/next-day engineers, written quote, certificates emailed. Call or WhatsApp 07517 806082.`;
  if (byteLength(text) > 160) text = `${name} in ${place}. Same/next-day engineers, written quote. Call 07517 806082.`;
  if (byteLength(text) > 160) text = `${name} in ${place}. Same/next-day. Call 07517 806082.`;
  return fitMeta(text);
}

function placeFor(townSlug) {
  if (TOWNS.has(townSlug)) return TOWNS.get(townSlug);
  if (!isGmTown(townSlug)) return null;
  return {
    name: gmTownName(townSlug) || townSlug,
    slug: townSlug,
    county: "Greater Manchester",
    region: "North West",
    nation: "England",
    population: 0,
  };
}

const HUB_LABELS = {
  "/pages/services/aov-air-handling": "AOV and smoke ventilation services",
  "/pages/aov": "AOV hub",
  "/pages/services/barriers": "Barrier services",
  "/pages/barriers": "Barriers hub",
  "/pages/services/fire-alarms": "Fire alarm services",
  "/pages/services/emergency-lighting": "Emergency lighting services",
  "/pages/services/fire-doors": "Fire door services",
  "/pages/services/fire-extinguishers": "Fire extinguisher services",
  "/pages/services/fire-risk-assessments": "Fire risk assessment services",
  "/pages/services/fire-stopping": "Fire stopping services",
  "/pages/services/fire-suppression": "Fire suppression services",
  "/pages/services/sprinkler-systems": "Sprinkler system services",
};
const hubLabel = (href) => HUB_LABELS[href] || href.split("/").pop().replace(/-/g, " ");
const P0_MIN_LINKS = 90; // gate is 85; keep a small margin for catalogue changes

const FAMILY = (service) => (service === "barriers" ? "barriers" : service === "aov-air-handling" ? "aov" : "fire");
const UTILITY = [
  ["/pages/services", "All services"],
  ["/pages/keywords", "All service guides"],
  ["/pages/areas", "Areas we cover"],
  ["/pages/commercial", "Commercial property services"],
  ["/pages/landlords", "Landlord compliance"],
  ["/pages/care-homes", "Care homes"],
  ["/pages/packages", "Compliance packages"],
  ["/pages/resources", "Guides and resources"],
  ["/pages/manufacturers", "Manufacturer hubs"],
  ["/pages/about", "About iComply"],
];

function milesBetween(a, b) {
  const rad = Math.PI / 180;
  const dLat = (b.lat - a.lat) * rad;
  const dLng = (b.lng - a.lng) * rad;
  const h = Math.sin(dLat / 2) ** 2 + Math.cos(a.lat * rad) * Math.cos(b.lat * rad) * Math.sin(dLng / 2) ** 2;
  return 3959 * 2 * Math.asin(Math.sqrt(h));
}

/**
 * CLOSE-Q8 (2026-10-14): P0 keyword x town pages carried ~20 links. Add the
 * same keyword in the nearest list towns, the other P0 guides of the same
 * family in this town, every parent service hub and the site utility pages.
 */
function p0ExtraLinks(keywordSlug, townSlug, townName, service, existing = "") {
  const out = [];
  const seen = new Set([`/pages/keywords/${keywordSlug}/${townSlug}`]);
  for (const m of existing.matchAll(/href="([^"]+)"/g)) seen.add(m[1]);
  const push = (href, label) => {
    if (seen.has(href)) return;
    seen.add(href);
    out.push(`<li><a href="${href}">${escapeHtml(label)}</a></li>`);
  };
  const name = keywordPack[keywordSlug]?.name || keywordSlug;
  let nearPool = [];
  const here = TOWNS.get(townSlug) || (() => {
    const g = geoRow(townSlug);
    return g ? { slug: townSlug, lat: g[5], lng: g[6] } : null;
  })();
  if (here && Number.isFinite(here.lat) && Number.isFinite(here.lng)) {
    const near = [...TOWNS.values()]
      .filter((t) => t.slug !== townSlug && Number.isFinite(t.lat) && Number.isFinite(t.lng))
      .map((t) => [milesBetween(here, t), t])
      .sort((a, b) => a[0] - b[0])
      ;
    nearPool = near.map(([, t]) => t);
    for (const t of nearPool.slice(0, 30)) push(`/pages/keywords/${keywordSlug}/${t.slug}`, `${name} in ${t.name}`);
  }
  const family = FAMILY(service);
  for (const other of P0.keys()) {
    if (other === keywordSlug || FAMILY(keywordPack[other]?.service) !== family) continue;
    if (!p0KeepsTown(other, townSlug)) continue;
    push(`/pages/keywords/${other}/${townSlug}`, `${keywordPack[other]?.name || other} in ${townName}`);
    if (out.length >= 60) break;
  }
  // Own family hubs plus the fire alarm service hub only (not every family).
  const ownHubs = Object.entries(PARENTS)
    .filter(([svc]) => FAMILY(svc) === family)
    .flatMap(([, hubs]) => hubs);
  for (const href of [...ownHubs, "/pages/services/fire-alarms"]) push(href, hubLabel(href));
  for (const [href, label] of UTILITY) push(href, label);
  // Top up with more nearby towns until the page reaches P0_MIN_LINKS unique
  // internal links (~6 fixed links come from the breadcrumb, quote and area hubs).
  for (const t of nearPool.slice(30)) {
    if (seen.size + 2 >= P0_MIN_LINKS) break;
    push(`/pages/keywords/${keywordSlug}/${t.slug}`, `${name} in ${t.name}`);
  }
  return out.join("");
}

function p0Seed(keywordSlug, townSlug) {
  let h = 2166136261;
  const v = `p0|${keywordSlug}|${townSlug}`;
  for (let i = 0; i < v.length; i++) { h ^= v.charCodeAt(i); h = Math.imul(h, 16777619); }
  // murmur3 finaliser: FNV low bits alone correlate between town names.
  h = Math.imul(h ^ (h >>> 16), 0x85ebca6b);
  h = Math.imul(h ^ (h >>> 13), 0xc2b2ae35);
  return (h ^ (h >>> 16)) >>> 0;
}
const P0_AUDIENCES = ["landlords", "managing agents", "facilities managers", "block managers", "housing associations", "care home managers"];
const P0_VISITS = ["a planned daytime visit", "a surveyed site visit", "a booked visit agreed with the site contact"];
const FAMILY_LABEL = { fire: "Fire safety", aov: "Smoke control", barriers: "Barrier" };

export function renderNationwideP0Town(keywordSlug, townSlug) {
  const hub = P0.get(keywordSlug);
  const pack = keywordPack[keywordSlug];
  const place = placeFor(townSlug);
  if (!hub || !pack || !place) return null;
  const name = pack.name || hub.name;
  const townName = place.name;
  const twin = keywordTwinIntent(keywordSlug, name, townName);
  const title = twin ? twin.title : p0Title(name, townName);
  const description = twin ? twin.desc : p0Description(name, townName);
  const canonical = `${ORIGIN}/pages/keywords/${keywordSlug}/${townSlug}`;
  const images = (hub.images || []).slice(0, 3);
  const ogImage = `${ORIGIN}${images[0] || "/assets/images/services/fire-alarms.jpg"}`;
  const popLine = Number(place.population) > 0 ? ` It is home to about ${Number(place.population).toLocaleString("en-GB")} people.` : "";
  const workName = name.replace(/\bInstallers?\b/g, "Installation").replace(/\bContractors?\b/g, "Services");
  const area = (place.county && place.county !== townName) ? place.county : (place.region || "the UK");
  // #140 natural wording, three seeded variants (CLOSE-Q1 town-to-town uniqueness).
  const local = [
    `We provide ${workName.toLowerCase()} in ${townName}, ${area}.${popLine} Our engineers work from ${NAP}, with same-day and next-day slots where the diary allows. You get a written quote before any work starts, with travel included.`,
    `Need ${workName.toLowerCase()} in ${townName}? We cover ${townName} and the rest of ${area} from ${NAP}.${popLine} Same-day and next-day slots are often available, and the written quote includes travel, so there are no surprises.`,
    `Our team carries out ${workName.toLowerCase()} for buildings across ${townName}, ${area}.${popLine} We travel from ${NAP}, often with same-day or next-day slots. Before anything starts you get a written quote, travel included.`,
  ][p0Seed(`local|${townSlug}`, keywordSlug) % 3];
  const body = String(pack.body || "").replace(/Nearby places on the same town list include [^.]*\./g, "");
  // Town pages carry a short service summary; the full guide stays on the keyword hub.
  // Paragraphs 0, 2, 7, 8 and 10-15 of the generated pack repeat placeholders
  // ("the UK mainland") or a mismatched example enquiry, so they are left off.
  let packParas = body.split(/\n\s*\n/).map((part) => part.trim()).filter(Boolean);
  // CLOSE-Q1 (2026-10-19): paragraphs 1 and 6 carried placeholders ("Travel to the UK mainland")
  // and other-trade lines (boom arms on fire pages). Keep one of 3-5, picked by town seed so
  // neighbouring towns rarely share it, plus the short closing paragraph 14 when it is clean.
  if (packParas.length >= 16) {
    const all = packParas;
    const keep = [3, 4, 5][p0Seed(keywordSlug, townSlug) % 3];
    packParas = [all[keep]];
    if (all[14] && all[14].split(/\s+/).length <= 60) packParas.push(all[14]);
  }
  const booking = `To book ${workName.toLowerCase()} in ${townName}, send us the full postcode, a few photos of the existing equipment, the type of building and whether it will be occupied on the day. We reply with a written quote and a date. After the visit, certificates and reports are emailed to you so they can sit with the building records.`;
  const localHtml = `<p>${escapeHtml(local)}</p>`;
  const bodyHtml = [...packParas, booking].map((para) => `<p>${escapeHtml(para)}</p>`).join("");
  // Two of the pack FAQs, picked by town seed (the full list stays on the keyword hub).
  const packFaqs = Array.isArray(pack.faq) ? pack.faq : [];
  const faqStart = packFaqs.length ? (p0Seed(townSlug, keywordSlug) % packFaqs.length) : 0;
  const faqs = [...packFaqs.slice(faqStart), ...packFaqs.slice(0, faqStart)].slice(0, 2);
  faqs.push([
    [
      `How do I ask for ${name} in ${townName}?`,
      `Call 07517 806082, WhatsApp us or use the contact form with the address in ${townName}. We will send a written quote, and same-day or next-day slots are often available.`,
    ],
    [
      `What is the first step for ${name.toLowerCase()} in ${townName}?`,
      `Send the ${townName} address, the building type and a few photos by WhatsApp (07517 806082) or email (icomplypropertyservices@gmail.com). We reply with a written quote and a date.`,
    ],
  ][p0Seed(`ask|${townSlug}`, keywordSlug) % 2]);
  const facts = townFactsBlock({
    slug: townSlug,
    town: townName,
    subject: name,
    service: pack.service,
    names: TOWNS,
    hrefFor: (s) => `/pages/keywords/${keywordSlug}/${s}`,
  });
  if (facts) faqs.unshift(...facts.faqs);
  // CLOSE-Q1: town-specific depth so P0 x town pages clear 800 prose words.
  const seed = p0Seed(keywordSlug, townSlug);
  const bankCtx = {
    seed,
    place: townName,
    subject: name,
    service: FAMILY_LABEL[FAMILY(pack.service)] || "Fire safety",
    audience: P0_AUDIENCES[seed % P0_AUDIENCES.length],
    visit: P0_VISITS[(seed >>> 4) % P0_VISITS.length],
    near: "the towns listed below",
    housing: "",
    industry: "",
    pop: "",
  };
  const depth = townDepthBlock({ slug: townSlug, town: townName, subject: name, service: pack.service, names: TOWNS, nation: place.nation || "", extra: depthBankParagraphs(bankCtx, 3) });
  // Seeded FAQ order so same-council towns do not share one long FAQ run.
  const faqShift = faqs.length ? p0Seed(`faq|${townSlug}`, keywordSlug) % faqs.length : 0;
  faqs.push(...faqs.splice(0, faqShift));
  const faqRows = faqs.filter((faq) => Array.isArray(faq) && faq.length >= 2);
  const faqHtml = faqRows
    .map(([q, a]) => `<h3>${escapeHtml(q)}</h3><p>${escapeHtml(a)}</p>`)
    .join("");
  // P2-20-4 (2026-10-20): the visible FAQ is also emitted as FAQPage JSON-LD.
  const faqLd = faqRows.length
    ? `<script type="application/ld+json">${JSON.stringify({
      "@context": "https://schema.org",
      "@type": "FAQPage",
      mainEntity: faqRows.map(([q, a]) => ({ "@type": "Question", name: String(q), acceptedAnswer: { "@type": "Answer", text: String(a) } })),
    }).replace(/</g, "\\u003c")}</script>`
    : "";
  const parents = PARENTS[pack.service] || PARENTS["fire-alarms"];
  let links = parents.map((href) => `<li><a href="${href}">${escapeHtml(hubLabel(href))}</a></li>`).join("");
  links += `<li><a href="/pages/keywords/${keywordSlug}">${escapeHtml(name)} guide (full scope)</a></li>`;
  links += `<li><a href="/contact">Request a quote</a></li><li><a href="/pages/areas/stockport">Stockport area hub</a></li>`;
  if (isGmTown(townSlug)) {
    links += `<li><a href="/pages/areas/${townSlug}">Property services in ${escapeHtml(townName)}</a></li>`;
  }
  const siblings = [...P0.keys()].filter((o) => o !== keywordSlug && keywordPack[o]?.service === pack.service);
  const start = siblings.length ? Math.abs([...townSlug].reduce((h, c) => (h * 31 + c.charCodeAt(0)) | 0, 7)) % siblings.length : 0;
  for (const other of [...siblings.slice(start), ...siblings.slice(0, start)].slice(0, 8)) {
    const label = keywordPack[other]?.name || other;
    links += `<li><a href="/pages/keywords/${other}">${escapeHtml(label)}</a></li>`;
  }
  links += p0ExtraLinks(keywordSlug, townSlug, townName, pack.service, links);
  // Seeded block order (pack copy, town facts, local notes) for town-to-town uniqueness.
  // The local paragraph leads; pack copy, town facts and local notes follow in a
  // seeded order, and the pack copy never sits straight after the local paragraph
  // in two orders out of three.
  const blocks = [bodyHtml, facts ? facts.html : "", depth ? depth.html : ""];
  const orders = [[1, 0, 2], [2, 0, 1], [1, 2, 0], [2, 1, 0], [0, 1, 2], [0, 2, 1]];
  const articleHtml = localHtml + orders[p0Seed(`order|${townSlug}`, keywordSlug) % orders.length].map((i) => blocks[i]).join("");
  const gallery = images.map((src, index) => (
    `<figure><img src="${escapeHtml(src)}" alt="${escapeHtml(`${name} in ${townName} — photograph ${index + 1}`)}" width="1200" height="630"></figure>`
  )).join("");
  const html = `<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${escapeHtml(title)}</title>
<meta name="description" content="${escapeHtml(description)}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="${canonical}">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_GB">
<meta property="og:site_name" content="iComply Property Services">
<meta property="og:title" content="${escapeHtml(title)}">
<meta property="og:description" content="${escapeHtml(description)}">
<meta property="og:url" content="${canonical}">
<meta property="og:image" content="${escapeHtml(ogImage)}">
${faqLd}
${MATRIX_CSS}
</head><body class="mx">
${MATRIX_HEADER}
<main>
<nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/pages/keywords/${keywordSlug}">${escapeHtml(name)}</a> / <span>${escapeHtml(townName)}</span></nav>
<h1>${escapeHtml(`${name} in ${townName}${twin ? twin.h1Tail : ""}`)}</h1>
${gallery}
<article id="local-copy">${articleHtml}</article>
<section><h2>${escapeHtml(name)} FAQ</h2>${faqHtml}</section>
<section class="related-links"><h2>Related guides</h2><ul>${links}</ul></section>
<p>Call or WhatsApp 07517 806082 for a written quote. Our base: ${escapeHtml(NAP)}.</p>
${MATRIX_FOOTER}
</main>
${MATRIX_SITE_FOOTER}
</body></html>`;
  return {
    html,
    title,
    description,
    canonical,
    robots: "index, follow",
    status: 200,
    path: `/pages/keywords/${keywordSlug}/${townSlug}`,
  };
}
