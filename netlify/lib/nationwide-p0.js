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
  for (const pad of [" Book today.", " Fixed written quote.", " Certificates emailed.", " Call 07517 806082."]) {
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
  let text = `${name} in ${place}. Same/next-day engineers, fixed written quote, certificates emailed. Call or WhatsApp 07517 806082.`;
  if (byteLength(text) > 160) text = `${name} in ${place}. Same/next-day engineers, fixed written quote. Call 07517 806082.`;
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
  ["/pages/keywords", "All keyword guides"],
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
    const g = townGeo[townSlug];
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

export function renderNationwideP0Town(keywordSlug, townSlug) {
  const hub = P0.get(keywordSlug);
  const pack = keywordPack[keywordSlug];
  const place = placeFor(townSlug);
  if (!hub || !pack || !place) return null;
  const name = pack.name || hub.name;
  const townName = place.name;
  const title = p0Title(name, townName);
  const description = p0Description(name, townName);
  const canonical = `${ORIGIN}/pages/keywords/${keywordSlug}/${townSlug}`;
  const images = (hub.images || []).slice(0, 3);
  const ogImage = `${ORIGIN}${images[0] || "/assets/images/services/fire-alarms.jpg"}`;
  const popLine = Number(place.population) > 0 ? ` It is home to about ${Number(place.population).toLocaleString("en-GB")} people.` : "";
  const workName = name.replace(/\bInstallers?\b/g, "Installation").replace(/\bContractors?\b/g, "Services");
  const local = `We provide ${workName.toLowerCase()} in ${townName}, ${(place.county && place.county !== townName) ? place.county : (place.region || "the UK")}.${popLine} Our engineers work from ${NAP}, with same-day and next-day slots where the diary allows. You get a fixed written quote before any work starts, with travel included.`;
  const body = String(pack.body || "").replace(/Nearby places on the same town list include [^.]*\./g, "");
  // Town pages carry a short service summary; the full guide stays on the keyword hub.
  // Paragraphs 0, 2, 7, 8 and 10-15 of the generated pack repeat placeholders
  // ("the UK mainland") or a mismatched example enquiry, so they are left off.
  let packParas = body.split(/\n\s*\n/).map((part) => part.trim()).filter(Boolean);
  if (packParas.length >= 16) packParas = [1, 4, 6].map((i) => packParas[i]);
  const booking = `To book ${workName.toLowerCase()} in ${townName}, send us the full postcode, a few photos of the existing equipment, the type of building and whether it will be occupied on the day. We reply with a fixed written quote and a date. After the visit, certificates and reports are emailed to you so they can sit with the building records.`;
  const paras = [local, ...packParas, booking];
  const bodyHtml = paras.map((para) => `<p>${escapeHtml(para)}</p>`).join("");
  const faqs = Array.isArray(pack.faq) ? pack.faq.slice(0, 2) : [];
  faqs.push([
    `How do I ask for ${name} in ${townName}?`,
    `Call 07517 806082, WhatsApp us or use the contact form with the address in ${townName}. We will send a fixed written quote, and same-day or next-day slots are often available.`,
  ]);
  const facts = townFactsBlock({
    slug: townSlug,
    town: townName,
    subject: name,
    service: pack.service,
    names: TOWNS,
    hrefFor: (s) => `/pages/keywords/${keywordSlug}/${s}`,
  });
  if (facts) faqs.unshift(...facts.faqs);
  const faqHtml = faqs
    .filter((faq) => Array.isArray(faq) && faq.length >= 2)
    .map(([q, a]) => `<h3>${escapeHtml(q)}</h3><p>${escapeHtml(a)}</p>`)
    .join("");
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
${MATRIX_CSS}
</head><body class="mx">
${MATRIX_HEADER}
<main>
<nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/pages/keywords/${keywordSlug}">${escapeHtml(name)}</a> / <span>${escapeHtml(townName)}</span></nav>
<h1>${escapeHtml(`${name} in ${townName}`)}</h1>
${gallery}
<article id="local-copy">${bodyHtml}${facts ? facts.html : ""}</article>
<section><h2>${escapeHtml(name)} FAQ</h2>${faqHtml}</section>
<section class="related-links"><h2>Related guides</h2><ul>${links}</ul></section>
<p>Call or WhatsApp 07517 806082 for a fixed written quote. Our base: ${escapeHtml(NAP)}.</p>
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
