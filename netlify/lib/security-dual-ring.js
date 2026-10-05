/**
 * Security dual-ring P0 town pages for the Netlify edge.
 * Hubs are static PHP. Town pages (keyword and job) are rendered here so
 * non-Greater-Manchester dual-ring towns are not redirected to the hub.
 */
import { securityDualRingPack } from "./security-dual-ring-pack.js";

const NAP = securityDualRingPack.nap;
const PHONE = securityDualRingPack.phone;

export function securityDualRingMatch(path) {
  const cleaned = String(path || "").replace(/\/+$/, "") || "/";
  let match = cleaned.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (match) {
    return securityRow("keyword", match[1], match[2]);
  }
  match = cleaned.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (match) {
    return securityRow("job", match[1], match[2]);
  }
  return null;
}

function securityRow(surface, slug, townSlug) {
  const intent = securityDualRingPack.intents[slug];
  const town = securityDualRingPack.towns[townSlug];
  if (!intent || !town) return null;
  if (surface === "job" && intent.kind !== "both") return null;
  return { surface, slug, townSlug, intent, town };
}

export function securityDualRingHandles(path) {
  return securityDualRingMatch(path) !== null;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function fitMeta(text) {
  let value = String(text).replace(/\s+/g, " ").trim();
  if (!value.includes("POA")) value = `${value.replace(/\.$/, "")}. Request a quote — POA.`;
  for (const pad of [" Call 07517806082.", " Stockport base.", " SK2 5DE."]) {
    if (value.length >= 140) break;
    if ((value + pad).length <= 160) value += pad;
  }
  if (value.length > 160) {
    let cut = value.slice(0, 160);
    const pos = cut.lastIndexOf(" ");
    if (pos > 40) cut = cut.slice(0, pos);
    value = cut.replace(/[ ,;:—-]+$/g, "");
    if (!value.includes("POA")) {
      const suffix = " POA.";
      cut = value.slice(0, 160 - suffix.length);
      const room = cut.lastIndexOf(" ");
      value = `${(room > 40 ? cut.slice(0, room) : cut).replace(/[ ,;:—-]+$/g, "")}${suffix}`;
    }
  }
  if (value.length > 160) {
    const cut = value.slice(0, 160);
    const pos = cut.lastIndexOf(" ");
    value = pos > 40 ? cut.slice(0, pos) : cut;
  }
  return value;
}

function titleFor(name, town) {
  if (!town) {
    const hub = `${name} | iComply Property Services`;
    return hub.length <= 65 ? hub : `${name} — iComply`;
  }
  const full = `${name} in ${town} | iComply Property Services`;
  if (full.length <= 65) return full;
  return `${name} in ${town} — iComply`;
}

function localParagraphs(intent, town) {
  const townName = town.name;
  const county = town.county || "the dual ring";
  const popSentence = town.population
    ? `${townName} has a published population of about ${Number(town.population).toLocaleString("en-GB")}.`
    : `A population figure is not invented for ${townName}. The survey uses the address you send.`;
  const miles = town.mi_manchester != null && town.mi_burnley != null
    ? `${townName} is about ${town.mi_manchester} miles from Manchester and about ${town.mi_burnley} miles from Burnley.`
    : `Miles from Manchester and Burnley for ${townName} are taken from the postcode on the quote, not from a blank row.`;
  const near = (town.near || [])
    .map((slug) => securityDualRingPack.towns[slug]?.name)
    .filter(Boolean)
    .join(", ") || "the neighbouring towns on the dual ring";
  return [
    `${intent.name} in ${townName} is scoped for buildings in ${county}. ${popSentence} ${miles} Nearby dual-ring towns for the same work include ${near}.`,
    `Engineers travel from ${NAP}. The quote for ${intent.name} in ${townName} is price on application after the building, the existing equipment and the access are known. Travel sits inside that quote. There is no catalogue fee and no separate mystery call-out price on this page.`,
    `This town page keeps the full description of the work, then adds ${townName} to the heading, the opening and the request. It is the local page for a search in this town, and it links back to the hub and to the service page rather than to a removed URL.`,
    `Ask for ${intent.name} in ${townName} with the postcode and a photo of the panel, recorder or door. Phone ${PHONE}. Say whether the building is a home, a rented block, a shop or a warehouse so the quote names the right scope.`,
  ];
}

export function renderSecurityDualRing(path) {
  const row = securityDualRingMatch(path);
  if (!row) return null;
  const { surface, slug, townSlug, intent, town } = row;
  const baseParas = surface === "job" ? intent.job_paragraphs : intent.paragraphs;
  const paragraphs = [...localParagraphs(intent, town), ...(baseParas || [])];
  const h1 = `${intent.name} in ${town.name}`;
  const title = titleFor(intent.name, town.name);
  const description = fitMeta(
    `${intent.name} in ${town.name}. ${town.county || "The dual ring"} sites for landlords, agents and commercial occupiers. Request a quote — POA.`
  );
  const canonicalPath = surface === "job"
    ? `/pages/jobs/${slug}/${townSlug}`
    : `/pages/keywords/${slug}/${townSlug}`;
  const canonical = `https://icomplypropertyservices.co.uk${canonicalPath}`;
  const image = intent.images?.[0]?.src || "/assets/images/services/cctv.jpg";
  const ogImage = `https://icomplypropertyservices.co.uk${image}`;
  const hub = surface === "job" ? `/pages/jobs/${slug}` : `/pages/keywords/${slug}`;
  const images = (intent.images || []).map((item) => (
    `<figure><img src="${escapeHtml(item.src)}" alt="${escapeHtml(item.alt)}" width="640" height="360"></figure>`
  )).join("");
  const faqs = (intent.faqs || []).map((faq) => (
    `<details><summary>${escapeHtml(faq.q)}</summary><p>${escapeHtml(faq.a)}</p></details>`
  )).join("");
  const body = paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("");
  const faqLd = JSON.stringify({
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: (intent.faqs || []).map((faq) => ({
      "@type": "Question",
      name: faq.q,
      acceptedAnswer: { "@type": "Answer", text: faq.a },
    })),
  });
  return `<!DOCTYPE html><html lang="en-GB"><head><meta charset="utf-8">`
    + `<meta name="viewport" content="width=device-width, initial-scale=1">`
    + `<title>${escapeHtml(title)}</title>`
    + `<meta name="description" content="${escapeHtml(description)}">`
    + `<meta name="robots" content="index, follow">`
    + `<link rel="canonical" href="${escapeHtml(canonical)}">`
    + `<meta property="og:type" content="website">`
    + `<meta property="og:locale" content="en_GB">`
    + `<meta property="og:locale:alternate" content="en_US">`
    + `<meta property="og:site_name" content="iComply Property Services">`
    + `<meta property="og:title" content="${escapeHtml(title)}">`
    + `<meta property="og:description" content="${escapeHtml(description)}">`
    + `<meta property="og:url" content="${escapeHtml(canonical)}">`
    + `<meta property="og:image" content="${escapeHtml(ogImage)}">`
    + `<script type="application/ld+json">${faqLd}</script>`
    + `</head><body>`
    + `<header><p><a href="/">iComply Property Services</a> · <a href="/pages/services">Services</a> · <a href="${hub}">${escapeHtml(intent.name)}</a> · <a href="/contact">Contact</a></p></header>`
    + `<main><article id="local-copy" data-seo-body="1">`
    + `<h1>${escapeHtml(h1)}</h1>${body}`
    + `<h2>Photographs</h2>${images}`
    + `<p><a href="${escapeHtml(intent.service_href)}">${escapeHtml(intent.service_name)}</a> · <a href="${hub}">${escapeHtml(intent.name)} hub</a></p>`
    + `</article><section data-seo-faq="1"><h2>${escapeHtml(intent.name)} questions</h2>${faqs}</section></main></body></html>`;
}
