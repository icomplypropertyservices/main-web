/**
 * Margin DEEP packs — edge twin of website/includes/margin-deep.php.
 * Locks: icomply-ops/seo/*-DEEP-LOCK-2026-10-05.md. Pack data: website/data/margin-deep/{pack}.json
 * (listed in ./margin-deep-packs.js). Town pages are hub × dual-ring 269; manufacturer × job heads
 * are Greater Manchester core 60 only. Body HTML matches the PHP renderer byte for byte.
 * Price on application only. No attendance-time or response-time promises.
 */
import allowlist from "../../website/data/dual-ring-allowlist.json" with { type: "json" };
import { MARGIN_DEEP_PACKS } from "./margin-deep-packs.js";

const NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE";
const PHONE = "07517 806082";
const WA = "https://wa.me/447517806082";
const EMAIL = "info@icomplypropertyservices.co.uk";
const BASE = "https://icomplypropertyservices.co.uk";

const TOWNS = new Map((allowlist.towns || []).filter((t) => t && t.slug).map((t) => [String(t.slug).toLowerCase(), t]));
const PACKS = new Map();
const HUBS = new Map();
for (const doc of [...MARGIN_DEEP_PACKS].sort((a, b) => String(a.pack).localeCompare(String(b.pack)))) {
  if (!doc || !doc.pack || !Array.isArray(doc.hubs)) continue;
  PACKS.set(doc.pack, doc);
  for (const hub of doc.hubs) {
    if (hub && hub.slug && !HUBS.has(hub.slug)) HUBS.set(hub.slug, { ...hub, pack: doc.pack });
  }
}

const esc = (value) => String(value)
  .replace(/&/g, "&amp;")
  .replace(/</g, "&lt;")
  .replace(/>/g, "&gt;")
  .replace(/"/g, "&quot;")
  .replace(/'/g, "&#039;");

const bytes = (s) => new TextEncoder().encode(s).length;

export function marginDeepHash(value) {
  let h = 2166136261;
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 16777619) >>> 0;
  }
  return h >>> 0;
}

export function marginDeepSlugs(pack = null) {
  return [...HUBS.values()].filter((hub) => pack === null || hub.pack === pack).map((hub) => hub.slug);
}

export function isMarginDeep(slug) {
  return HUBS.has(slug);
}

export function marginDeepGmOnly(slug) {
  return HUBS.get(slug)?.geo === "gm60";
}

function copyFor(slug) {
  const hub = HUBS.get(slug);
  return hub ? PACKS.get(hub.pack)?.keywords?.[slug] || null : null;
}

function gmTown(townSlug) {
  return TOWNS.get(townSlug)?.bucket === "gm_core";
}

export function marginDeepKeepsTown(keyword, town) {
  if (!HUBS.has(keyword)) return false;
  if (marginDeepGmOnly(keyword)) return gmTown(town);
  return TOWNS.has(town);
}

/** 301 for an older URL a DEEP keyword hub supersedes, else null. */
export function marginDeepRedirect(path) {
  const clean = (path.replace(/\.php$/i, "").replace(/\/+$/, "")) || "/";
  for (const doc of PACKS.values()) {
    const map = doc.redirects || {};
    if (Object.prototype.hasOwnProperty.call(map, clean)) return map[clean];
    const m = clean.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
    if (m && Object.prototype.hasOwnProperty.call(map, `/pages/jobs/${m[1]}`)) {
      const target = map[`/pages/jobs/${m[1]}`];
      const kw = target.split("/").pop();
      return marginDeepKeepsTown(kw, m[2]) ? `${target}/${m[2]}` : target;
    }
  }
  return null;
}

function title(name, town) {
  const lead = town ? `${name} in ${town}` : name;
  const full = `${lead} | iComply Property Services`;
  if (bytes(full) <= 60) return full;
  return bytes(`${lead} | iComply`) <= 70 ? `${lead} | iComply` : lead;
}

function fitMeta(input) {
  let text = String(input).replace(/\s+/g, " ").trim();
  if (bytes(text) > 160) {
    const words = text.split(" ");
    while (words.length > 1 && bytes(words.join(" ")) > 158) words.pop();
    text = words.join(" ").replace(/[ .,;:—-]+$/u, "") + ".";
  }
  for (const pad of [" Quote POA.", " Survey first.", " Written scope.", " Stockport base.", " POA."]) {
    if (bytes(text) >= 140) break;
    if (bytes(text + pad) <= 160 && !text.includes(pad.trim())) text += pad;
  }
  return text;
}

function townMeta(name, place, who) {
  let text = `${name} in ${place} for ${who}. Surveyed and scoped from Stockport. Request a quote — POA.`;
  if (bytes(text) > 160) text = `${name} in ${place}. Surveyed and scoped from Stockport. Request a quote — POA.`;
  if (bytes(text) > 160) text = `${name} in ${place}. Scoped from Stockport. Quote POA.`;
  return fitMeta(text);
}

function localPara(keyword, name, town, pack) {
  const place = town.name;
  const where = town.bucket === "gm_core"
    ? `${place} is in the Greater Manchester core of our service area, about ${town.mi_manchester} miles from Manchester city centre on the town list this page uses.`
    : `${place} is on our Manchester and Burnley service ring, about ${town.mi_manchester} miles from Manchester and ${town.mi_burnley} miles from Burnley on the town list this page uses.`;
  const angles = pack.town_angles || [];
  let angle = angles.length ? String(angles[marginDeepHash(`${keyword}|${town.slug}`) % angles.length]) : "";
  angle = angle.split("{place}").join(place).split("{name}").join(name);
  return `${where} ${name} in ${place} is quoted from ${NAP}. ${angle} The quote is price on application once the scope is written down, and travel from the Stockport workshop is part of that quote. No attendance time is promised on this page.`.trim();
}

function siblings(keyword, related, limit = 18) {
  const pack = HUBS.get(keyword)?.pack;
  const same = marginDeepSlugs(pack);
  let at = same.indexOf(keyword);
  if (at < 0) at = 0;
  let ordered = [...same.slice(at + 1), ...same.slice(0, at)];
  if (related && related !== keyword && HUBS.has(related)) ordered = [related, ...ordered.filter((s) => s !== related)];
  return ordered.slice(0, limit);
}

/** @returns {{html:string,title:string,description:string,robots:string}|null} */
export function renderMarginDeepTown(keyword, townSlug) {
  if (!marginDeepKeepsTown(keyword, townSlug)) return null;
  const copy = copyFor(keyword);
  const pack = PACKS.get(HUBS.get(keyword).pack);
  const town = TOWNS.get(townSlug);
  if (!copy || !town) return null;
  const name = copy.name;
  const place = town.name;
  const pageTitle = title(name, place);
  const description = townMeta(name, place, pack.town_who || "property owners and managers");
  const canonical = `${BASE}/pages/keywords/${keyword}/${townSlug}`;
  const images = (HUBS.get(keyword).images || []).slice(0, 3);
  const ogImage = BASE + (images[0] || "/assets/images/services/building-maintenance.jpg");
  const paras = String(copy.body || "").trim().split(/(?:\r\n|\r|\n){2,}/);
  paras.unshift(localPara(keyword, name, town, pack), String(copy.intro || ""));
  const bodyHtml = paras.map((p) => p.trim()).filter(Boolean).map((p) => `<p>${esc(p)}</p>`).join("");
  const focusHtml = (copy.focus_points || []).map((p) => `<li>${esc(p)}</li>`).join("");
  const faqs = [...(copy.faq || [])];
  faqs.push([
    `Do you cover ${place} for ${name}?`,
    `Yes. ${place} is on the published town list and is quoted from the Stockport workshop. Phone or WhatsApp ${PHONE}, or email ${EMAIL}, and name the site in ${place}. The reply is price on application.`,
  ]);
  let faqHtml = "";
  const faqSchema = [];
  for (const faq of faqs) {
    if (!Array.isArray(faq) || faq.length < 2) continue;
    faqHtml += `<h3>${esc(faq[0])}</h3><p>${esc(faq[1])}</p>`;
    faqSchema.push({ "@type": "Question", name: String(faq[0]), acceptedAnswer: { "@type": "Answer", text: String(faq[1]) } });
  }
  let links = "";
  for (const parent of pack.parents || []) {
    if (Array.isArray(parent) && parent.length >= 2) links += `<li><a href="${esc(parent[0])}">${esc(parent[1])}</a></li>`;
  }
  links += `<li><a href="${esc(`/pages/keywords/${keyword}`)}">${esc(`${name} guide`)}</a></li>`;
  links += '<li><a href="/contact">Request a quote</a></li>';
  links += gmTown(townSlug)
    ? `<li><a href="${esc(`/pages/areas/${townSlug}`)}">${esc(`Property services in ${place}`)}</a></li>`
    : '<li><a href="/pages/areas/stockport">Stockport area hub</a></li>';
  for (const other of siblings(keyword, copy.related || "")) {
    const otherTown = marginDeepKeepsTown(other, townSlug);
    const href = `/pages/keywords/${other}${otherTown ? `/${townSlug}` : ""}`;
    const label = `${copyFor(other)?.name || other}${otherTown ? ` in ${place}` : ""}`;
    links += `<li><a href="${esc(href)}">${esc(label)}</a></li>`;
  }
  const gallery = images.map((src, i) => `<figure><img src="${esc(src)}" alt="${esc(`${name} in ${place} — photograph ${i + 1}`)}" width="1200" height="800" loading="${i === 0 ? "eager" : "lazy"}"></figure>`).join("");
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Service",
        name: `${name} in ${place}`,
        description,
        areaServed: place,
        url: canonical,
        provider: { "@type": "LocalBusiness", name: "iComply Property Services", telephone: PHONE, email: EMAIL, address: NAP },
      },
      { "@type": "FAQPage", mainEntity: faqSchema },
    ],
  };
  const contact = `<section id="contact"><h2>Contact</h2><p>Phone <a href="tel:07517806082">${esc(PHONE)}</a> · WhatsApp <a href="${esc(WA)}">${esc(PHONE)}</a> · Email <a href="mailto:${esc(EMAIL)}">${esc(EMAIL)}</a></p><p>Workshop: ${esc(NAP)}. Price on application.</p></section>`;
  const html = '<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">'
    + '<meta name="viewport" content="width=device-width, initial-scale=1">'
    + `<title>${esc(pageTitle)}</title>`
    + `<meta name="description" content="${esc(description)}">`
    + '<meta name="robots" content="index, follow">'
    + `<link rel="canonical" href="${esc(canonical)}">`
    + '<meta property="og:type" content="website">'
    + '<meta property="og:locale" content="en_GB">'
    + '<meta property="og:site_name" content="iComply Property Services">'
    + `<meta property="og:title" content="${esc(pageTitle)}">`
    + `<meta property="og:description" content="${esc(description)}">`
    + `<meta property="og:url" content="${esc(canonical)}">`
    + `<meta property="og:image" content="${esc(ogImage)}">`
    + `<script type="application/ld+json">${JSON.stringify(schema)}</script>`
    + '</head><body><main>'
    + `<nav><a href="/">Home</a> / <a href="${esc(`/pages/keywords/${keyword}`)}">${esc(name)}</a> / <span>${esc(place)}</span></nav>`
    + `<h1>${esc(`${name} in ${place}`)}</h1>`
    + gallery
    + `<article id="local-copy">${bodyHtml}<ul>${focusHtml}</ul></article>`
    + `<section><h2>${esc(`${name} FAQ`)}</h2>${faqHtml}</section>`
    + `<section><h2>Related guides</h2><ul>${links}</ul></section>`
    + contact
    + '</main></body></html>';
  return { html, title: pageTitle, description, robots: "index, follow" };
}
