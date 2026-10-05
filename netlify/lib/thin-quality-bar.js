/**
 * CLOSE-Q1 / Q2 / Q3 shared floor for the edge ×town renderer
 * (netlify/edge-functions/town-matrix.js → renderTownPage).
 *
 * Q3 Open Graph is emitted only from renderTownPage's <head>, using
 * thinOgMeta() so service×town, keyword×town and job×town share one
 * implementation. og:image is the absolute HTTPS URL of the hero <img>
 * (data-pe-slot="q2-image-hero").
 *
 * Page Enrichment swaps copy and image files at the data-pe-slot hooks
 * documented in docs/pe-quality-bar-slots.md. Do not add a second OG helper.
 */
import fragments from "../../website/data/quality-bar-prose.json" with { type: "json" };

const ORIGIN = "https://icomplypropertyservices.co.uk";

const PHOTO = new Set([
  "access-control",
  "aov-air-handling",
  "cctv",
  "door-entry",
  "electrical",
  "emergency-lighting",
  "fire-alarms",
  "fire-risk-assessments",
  "gas-systems",
  "intercoms",
  "intruder-alarm",
  "nurse-call",
]);

// Networking / IT pack services: all three images stay on IT, network, Wi-Fi and cabling work.
const PACK_THIRD = {
  "it-support": "networking",
  networking: "structured-cabling",
  wifi: "networking",
  "structured-cabling": "networking-photo",
};

const POOL = [
  "building-maintenance",
  "fire-alarms",
  "electrical",
  "cctv",
  "access-control",
  "emergency-lighting",
  "gas-systems",
  "door-entry",
];

export function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function fill(template, ctx) {
  return String(template)
    .replaceAll("{place}", ctx.place)
    .replaceAll("{subject}", ctx.subject)
    .replaceAll("{service}", ctx.service)
    .replaceAll("{audience}", ctx.audience)
    .replaceAll("{visit}", ctx.visit)
    .replaceAll("{near}", ctx.near)
    .replaceAll("{housing}", ctx.housing)
    .replaceAll("{industry}", ctx.industry)
    .replaceAll("{pop}", ctx.pop);
}

function mixIndex(seed, pass, bankIndex, length) {
  let h = (seed ^ Math.imul(bankIndex + 1, 0x9e3779b9) ^ Math.imul(pass + 1, 0x85ebca6b)) >>> 0;
  h = Math.imul(h ^ (h >>> 16), 0x45d9f3b) >>> 0;
  return h % length;
}

/**
 * Unique town/service prose. Two passes over the shared banks, index mixed
 * by seed, so neighbouring towns do not repeat the same paragraph order.
 * @returns {string} HTML inside the Q1 slot
 */
function proseSlot(kind) {
  if (kind === "job") return "q1-job-town-prose";
  if (kind === "keyword") return "q1-keyword-town-prose";
  if (kind === "service") return "q1-service-town-prose";
  return "q1-thin-prose";
}

function paragraphHtml(body) {
  return String(body)
    .split(/\n\s*\n/)
    .map((chunk) => chunk.replace(/\s+/g, " ").trim())
    .filter(Boolean)
    .map((text) => `<p>${escapeHtml(text)}</p>`)
    .join("");
}

export function thinProseHtml(ctx) {
  const slot = proseSlot(ctx.kind);
  if (typeof ctx.peBody === "string" && ctx.peBody.trim()) {
    let html = paragraphHtml(ctx.peBody);
    if (ctx.gas && !/Gas Safe registered engineers/.test(ctx.peBody)) {
      html += "<p>Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.</p>";
    }
    return `<div data-pe-slot="${slot}" id="q1-thin-prose">${html}</div>`;
  }
  const banks = fragments.banks || [];
  const parts = [];
  if (ctx.blurb) {
    parts.push(`<p data-pe-slot="q1-town-blurb">${escapeHtml(ctx.blurb)}</p>`);
  }
  const seed = ctx.seed >>> 0;
  for (let pass = 0; pass < 2; pass++) {
    banks.forEach((bank, i) => {
      if (!bank.length) return;
      const raw = bank[mixIndex(seed, pass, i, bank.length)];
      parts.push(`<p>${escapeHtml(fill(raw, ctx))}</p>`);
    });
  }
  parts.push(`<p>${escapeHtml(fill("{pop} {housing} {industry} Places named alongside {place} include {near}.", ctx))}</p>`);
  if (ctx.gas) {
    parts.push("<p>Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.</p>");
  }
  return `<div data-pe-slot="${slot}" id="q1-thin-prose">${parts.join("")}</div>`;
}

function absoluteOg(src) {
  if (/^https:\/\//i.test(src)) return src;
  const path = src.startsWith("/") ? src : `/${src}`;
  return `${ORIGIN}${path}`;
}

export function thinImages(serviceSlug, subject, placeName, seed, peImages) {
  if (Array.isArray(peImages)) {
    const picked = [];
    for (const image of peImages) {
      if (!image || typeof image.src !== "string" || !image.src.trim()) continue;
      picked.push({ src: image.src.trim(), alt: String(image.alt || "") });
      if (picked.length === 3) break;
    }
    if (picked.length === 3) {
      const slots = ["q2-image-hero", "q2-image-work", "q2-image-context"];
      const html = `<figure class="quality-bar-images" data-pe-slot="q2-thin-images">`
        + picked.map((image, index) => (
          `<img data-pe-slot="${slots[index]}" src="${escapeHtml(image.src)}" alt="${escapeHtml(image.alt)}" width="1200" height="630">`
        )).join("")
        + `</figure>`;
      return { html, heroSrc: picked[0].src, ogImage: absoluteOg(picked[0].src) };
    }
  }
  const slug = String(serviceSlug || "fire-alarms");
  const primary = `/assets/images/services/${slug}.jpg`;
  const packThird = PACK_THIRD[slug];
  let second = PHOTO.has(slug) || packThird
    ? `/assets/images/services/${slug}-photo.jpg`
    : `/assets/images/services/${POOL[seed % POOL.length]}.jpg`;
  if (second === primary) second = "/assets/images/services/building-maintenance.jpg";
  let third = packThird
    ? `/assets/images/services/${packThird}.jpg`
    : `/assets/images/services/${POOL[(seed + 3) % POOL.length]}.jpg`;
  if (third === primary || third === second) third = "/assets/images/services/cctv.jpg";
  if (third === primary || third === second) third = "/assets/images/services/electrical.jpg";
  if (third === primary || third === second) third = "/assets/images/services/access-control.jpg";
  const altHero = `${subject} in ${placeName}`;
  const altWork = `${subject} equipment prepared for ${placeName}`;
  const altContext = `Property compliance visit arranged for ${placeName}`;
  const html = `<figure class="quality-bar-images" data-pe-slot="q2-thin-images">`
    + `<img data-pe-slot="q2-image-hero" src="${primary}" alt="${escapeHtml(altHero)}" width="1200" height="630">`
    + `<img data-pe-slot="q2-image-work" src="${second}" alt="${escapeHtml(altWork)}" width="1200" height="630">`
    + `<img data-pe-slot="q2-image-context" src="${third}" alt="${escapeHtml(altContext)}" width="1200" height="630">`
    + `</figure>`;
  return {
    html,
    heroSrc: primary,
    ogImage: `${ORIGIN}${primary}`,
  };
}

function faqPair(row) {
  if (!row || typeof row !== "object") return null;
  if (Object.prototype.hasOwnProperty.call(row, "q") || Object.prototype.hasOwnProperty.call(row, "a")) {
    const q = String(row.q || "").trim();
    const a = String(row.a || "").trim();
    return q && a ? [q, a] : null;
  }
  if (Array.isArray(row)) {
    const q = String(row[0] || "").trim();
    const a = String(row[1] || "").trim();
    return q && a ? [q, a] : null;
  }
  return null;
}

export function thinFaqs(ctx) {
  if (Array.isArray(ctx.peFaqs)) {
    const mapped = ctx.peFaqs.map(faqPair).filter(Boolean);
    if (mapped.length >= 3) return mapped;
  }
  const faqs = [
    [
      `How is ${ctx.subject} in ${ctx.place} quoted?`,
      `The ${ctx.place} quote for ${ctx.subject} is price on application after the scope names the building and the access. This page does not publish a fee.`,
    ],
    [
      `Where is the team that covers ${ctx.place} based?`,
      `Visits for ${ctx.subject} in ${ctx.place} are arranged from 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Diary time depends on access and the agreed scope.`,
    ],
    [
      `What should ${ctx.audience} in ${ctx.place} send first?`,
      `Send the postcode, the property type and anything already known about the ${ctx.service} installation. ${ctx.place} paperwork is confirmed before ${ctx.visit} is booked.`,
    ],
  ];
  if (ctx.gas) {
    faqs.push([
      `Who carries out gas work for this ${ctx.place} visit?`,
      "Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.",
    ]);
  }
  return faqs;
}

export function thinFaqHtml(ctx) {
  const faqs = thinFaqs(ctx);
  const items = faqs.map(([q, a]) => (
    `<details class="faq-item"><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`
  )).join("");
  return `<section id="faq" class="faq" data-pe-slot="q5-thin-faq"><h2>Questions about ${escapeHtml(ctx.place)}</h2>${items}</section>`;
}

/**
 * Q3 — the only Open Graph snippet for thin ×town HTML.
 * title and description must be the page <title> and meta description unchanged.
 * image must be an absolute https URL.
 */
export function thinOgMeta({ title, description, canonical, image }) {
  const t = escapeHtml(title);
  const d = escapeHtml(description);
  const c = escapeHtml(canonical);
  const img = escapeHtml(image);
  return `<meta property="og:type" content="website">`
    + `<meta property="og:title" content="${t}">`
    + `<meta property="og:description" content="${d}">`
    + `<meta property="og:url" content="${c}">`
    + `<meta property="og:image" content="${img}">`;
}
