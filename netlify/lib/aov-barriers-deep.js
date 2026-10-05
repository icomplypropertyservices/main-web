/**
 * AOV + Barriers DEEP P0 — edge twin of website/includes/aov-barriers-deep.php.
 *
 * SEO lock: icomply-ops/seo/AOV-BARRIERS-DEEP-LOCK-2026-10-05.md (corrected: barrier
 * theme is manual + width/height; wind theme withdrawn). 179 hubs; town pages are
 * hub × UK TOP 5000 (GM core places kept); manufacturer × job heads (geo "gm60") are
 * Greater Manchester core 60 only. POA only, no attendance-time promises.
 */
import pack from "../../website/data/aov-barriers-deep-p0.json" with { type: "json" };
import townDoc from "../../website/data/uk-top5000-towns.json" with { type: "json" };
import { gmTownName, isGmTown } from "./link-blocks.js";

const HUBS = new Map((pack.hubs || []).map((hub) => [hub.slug, hub]));
const KEYWORDS = pack.keywords || {};
const TOWNS = new Map((townDoc.towns || []).filter((town) => town && town.slug).map((town) => [town.slug, town]));
const NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE";
const ORIGIN = "https://icomplypropertyservices.co.uk";
const PARENTS = {
  "aov-air-handling": ["/pages/services/aov-air-handling", "/pages/aov"],
  barriers: ["/pages/services/barriers", "/pages/barriers"],
};

export function isAovBarriersDeep(slug) {
  return HUBS.has(slug);
}

export function aovBarriersDeepSlugs() {
  return [...HUBS.keys()];
}

export function aovBarriersDeepGmOnly(slug) {
  return HUBS.get(slug)?.geo === "gm60";
}

export function aovBarriersDeepKeepsTown(keyword, town) {
  if (!HUBS.has(keyword)) return false;
  if (aovBarriersDeepGmOnly(keyword)) return isGmTown(town);
  return TOWNS.has(town) || isGmTown(town);
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function byteLength(value) {
  return new TextEncoder().encode(value).length;
}

// Same rules as icomplyNationwide3lineFitMeta() (PHP strlen counts UTF-8 bytes).
function fitMeta(text) {
  let value = String(text).replace(/\s+/g, " ").trim();
  if (byteLength(value) > 160 && value.includes("—")) value = value.replace("—", "-");
  if (byteLength(value) > 160) {
    const bytes = new TextEncoder().encode(value);
    let end = 160;
    while (end > 0 && (bytes[end] & 0b11000000) === 0b10000000) end -= 1;
    let cut = new TextDecoder().decode(bytes.slice(0, end));
    const space = cut.lastIndexOf(" ");
    if (space > 40) cut = cut.slice(0, space);
    value = cut.replace(/[ .,;:—-]+$/, "");
    const extra = " Request a quote — POA.";
    if (!value.endsWith("POA") && byteLength(value) + byteLength(extra) <= 160) value += extra;
  }
  for (const pad of [" Scope is written first.", " No catalogue price.", " Call 07517806082.", " Scope first.", " POA only."]) {
    if (byteLength(value) >= 140 && value.length >= 140) break;
    if (byteLength(value) + byteLength(pad) <= 160) value += pad;
  }
  return value;
}

export function deepTitle(name, town) {
  const lead = town ? `${name} in ${town}` : name;
  const full = `${lead} | iComply Property Services`;
  return byteLength(full) <= 60 ? full : `${lead} — iComply`;
}

export function deepTownMeta(name, place, line) {
  const who = line === "aov" ? "landlords, agents and building managers" : "car parks, estates and private sites";
  let text = `${name} in ${place} for ${who}. Measured, scoped from Stockport. Request a quote — POA.`;
  if (byteLength(text) > 160) text = `${name} in ${place}. Measured and scoped from Stockport. Request a quote — POA.`;
  if (byteLength(text) > 160) text = `${name} in ${place}. Scoped from Stockport. Quote POA.`;
  return fitMeta(text);
}

function placeFor(keyword, townSlug) {
  const gm = isGmTown(townSlug);
  if (aovBarriersDeepGmOnly(keyword) && !gm) return null;
  if (TOWNS.has(townSlug)) return TOWNS.get(townSlug);
  if (!gm) return null;
  return {
    name: gmTownName(townSlug) || townSlug,
    slug: townSlug,
    county: "Greater Manchester",
    region: "North West",
    nation: "England",
    population: 0,
  };
}

export function deepLocal(name, town, line) {
  const place = town.name;
  const pop = Number(town.population || 0);
  const where = `${place} is in ${town.county || ""}, ${town.region || ""}, ${town.nation || "England"}.`;
  const popLine = pop > 0 ? ` Published population on the town list used for this page is ${pop.toLocaleString("en-GB")}.` : "";
  const subject = line === "aov"
    ? `Buildings in ${place} are surveyed on their own drawings, fire strategy and access, not on a national script.`
    : `Entrances in ${place} are measured on site — clear width, headroom, ground and turning space — before anything is specified.`;
  return `${where}${popLine} ${name} in ${place} is quoted from ${NAP}. ${subject} The quote is price on application after the scope is written down, and travel from the Stockport workshop is part of that quote. No attendance time is promised on this page.`;
}

export function deepSiblings(keyword, related = "", limit = 24) {
  const line = HUBS.get(keyword)?.line || "";
  const same = [...HUBS.values()].filter((hub) => hub.line === line).map((hub) => hub.slug);
  const at = Math.max(0, same.indexOf(keyword));
  let ordered = [...same.slice(at + 1), ...same.slice(0, at)];
  if (related && related !== keyword && HUBS.has(related)) {
    ordered = [related, ...ordered.filter((slug) => slug !== related)];
  }
  return ordered.slice(0, limit);
}

export function renderAovBarriersDeepTown(keywordSlug, townSlug) {
  const hub = HUBS.get(keywordSlug);
  const kw = KEYWORDS[keywordSlug];
  const town = hub && kw ? placeFor(keywordSlug, townSlug) : null;
  if (!hub || !kw || !town) return null;
  const name = kw.name || hub.name;
  const line = hub.line || "barrier";
  const service = kw.service || hub.service || "barriers";
  const place = town.name;
  const title = deepTitle(name, place);
  const description = deepTownMeta(name, place, line);
  const canonical = `${ORIGIN}/pages/keywords/${keywordSlug}/${townSlug}`;
  const images = (hub.images || []).slice(0, 3);
  const ogImage = `${ORIGIN}${images[0] || `/assets/images/services/${service}.jpg`}`;
  const paras = [
    deepLocal(name, town, line),
    String(kw.intro || ""),
    ...String(kw.body || "").split(/\n\s*\n/),
  ].map((part) => part.trim()).filter(Boolean);
  const bodyHtml = paras.map((para) => `<p>${escapeHtml(para)}</p>`).join("");
  const focusHtml = (kw.focus_points || []).map((point) => `<li>${escapeHtml(point)}</li>`).join("");
  const faqs = Array.isArray(kw.faq) ? kw.faq.slice() : [];
  faqs.push([
    `Do you cover ${place} for ${name}?`,
    `Yes. ${place} is quoted from the Stockport workshop at ${NAP}. Phone 07517806082 or use the contact form and name the site in ${place}. The reply is price on application.`,
  ]);
  const validFaqs = faqs.filter((faq) => Array.isArray(faq) && faq.length >= 2);
  const faqHtml = validFaqs.map(([q, a]) => `<h3>${escapeHtml(q)}</h3><p>${escapeHtml(a)}</p>`).join("");
  const parents = PARENTS[service] || PARENTS.barriers;
  let links = parents.map((href) => `<li><a href="${href}">${escapeHtml(href.split("/").pop().replace(/-/g, " "))}</a></li>`).join("");
  links += `<li><a href="/pages/keywords/${keywordSlug}">${escapeHtml(`${name} guide`)}</a></li>`;
  links += `<li><a href="/contact">Request a quote</a></li><li><a href="/pages/areas/stockport">Stockport area hub</a></li>`;
  if (isGmTown(townSlug)) {
    links += `<li><a href="/pages/areas/${townSlug}">${escapeHtml(`Property services in ${place}`)}</a></li>`;
  }
  for (const other of deepSiblings(keywordSlug, kw.related || "")) {
    const otherTown = aovBarriersDeepKeepsTown(other, townSlug);
    const href = `/pages/keywords/${other}${otherTown ? `/${townSlug}` : ""}`;
    const label = `${KEYWORDS[other]?.name || other}${otherTown ? ` in ${place}` : ""}`;
    links += `<li><a href="${href}">${escapeHtml(label)}</a></li>`;
  }
  const gallery = images.map((src, index) => (
    `<figure><img src="${escapeHtml(src)}" alt="${escapeHtml(`${name} in ${place} — photograph ${index + 1}`)}" width="1200" height="800" loading="${index === 0 ? "eager" : "lazy"}"></figure>`
  )).join("");
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Service",
        name: `${name} in ${place}`,
        description,
        areaServed: place,
        url: canonical,
        provider: { "@type": "LocalBusiness", name: "iComply Property Services", telephone: "07517806082", address: NAP },
      },
      {
        "@type": "FAQPage",
        mainEntity: validFaqs.map(([q, a]) => ({ "@type": "Question", name: q, acceptedAnswer: { "@type": "Answer", text: a } })),
      },
    ],
  };
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
<script type="application/ld+json">${JSON.stringify(schema).replace(/</g, "\\u003c")}</script>
</head><body><main>
<nav><a href="/">Home</a> / <a href="/pages/keywords/${keywordSlug}">${escapeHtml(name)}</a> / <span>${escapeHtml(place)}</span></nav>
<h1>${escapeHtml(`${name} in ${place}`)}</h1>
${gallery}
<article id="local-copy">${bodyHtml}<ul>${focusHtml}</ul></article>
<section><h2>${escapeHtml(`${name} FAQ`)}</h2>${faqHtml}</section>
<section><h2>Related guides</h2><ul>${links}</ul></section>
<p>Workshop: ${escapeHtml(NAP)}. Phone 07517806082. Price on application.</p>
</main></body></html>`;
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
