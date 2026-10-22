import imageFamilies from "../../website/data/service-image-families.json" with { type: "json" };
import { aliasFamily, pickImages } from "./image-pool.js";
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

/** Family of a service image slug (falls back to building for unknown slugs). */
export function imageFamilyOf(slug) {
  for (const [name, list] of Object.entries(imageFamilies.families)) if (list.includes(slug)) return name;
  return null;
}

/** Two extra photos for `slug`, taken from that service's own pool. */
export function familyPicks(slug, seed) {
  const family = aliasFamily(slug);
  const picks = pickImages(`${slug}|${seed}`, family, 3);
  return [picks[1] || picks[0], picks[2] || picks[0]];
}

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
  // Full murmur3 finaliser: the old single round left the low bits (the ones
  // % length reads) almost fixed, so neighbouring towns picked the same variants.
  h = Math.imul(h ^ (h >>> 16), 0x85ebca6b) >>> 0;
  h = Math.imul(h ^ (h >>> 13), 0xc2b2ae35) >>> 0;
  h = (h ^ (h >>> 16)) >>> 0;
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
      html += "<p>Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.</p>";
    }
    return `<div data-pe-slot="${slot}" id="q1-thin-prose">${html}</div>`;
  }
  const banks = fragments.banks || [];
  const parts = [];
  if (ctx.blurb) {
    parts.push(`<p data-pe-slot="q1-town-blurb">${escapeHtml(ctx.blurb)}</p>`);
  }
  const seed = ctx.seed >>> 0;
  // Short mode: a real town-facts block carries the local copy, so keep generic bank prose to 4 paragraphs.
  for (let pass = 0; pass < (ctx.short ? 1 : 2); pass++) {
    banks.forEach((bank, i) => {
      if (!bank.length || (ctx.short && i >= 4)) return;
      const raw = bank[mixIndex(seed, pass, i, bank.length)];
      parts.push(`<p>${escapeHtml(fill(raw, ctx))}</p>`);
    });
  }
  if (!ctx.short) parts.push(`<p>${escapeHtml(fill("{pop} {housing} {industry} Places named alongside {place} include {near}.", ctx))}</p>`);
  if (ctx.gas) {
    parts.push("<p>Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.</p>");
  }
  return `<div data-pe-slot="${slot}" id="q1-thin-prose">${parts.join("")}</div>`;
}

function absoluteOg(src) {
  if (/^https:\/\//i.test(src)) return src;
  const path = src.startsWith("/") ? src : `/${src}`;
  return `${ORIGIN}${path}`;
}

/**
 * CLOSE-Q1 (2026-10-19): extra bank paragraphs for lean town pages. Picks
 * `count` of the later banks (price, access, neighbours, standards, follow-up,
 * instructing client) by seed, so two towns rarely share the same pick.
 * @returns {string[]} plain-text paragraphs
 */
export function depthBankParagraphs(ctx, count = 3) {
  const banks = fragments.banks || [];
  const pool = [4, 5, 6, 7, 8, 9].filter((i) => banks[i] && banks[i].length);
  const seed = ctx.seed >>> 0;
  const ranked = pool
    .map((i) => [mixIndex(seed, 7, i, 1 << 30), i])
    .sort((a, b) => a[0] - b[0])
    .slice(0, count)
    .map((r) => r[1]);
  return ranked.map((i) => {
    const text = fill(banks[i][mixIndex(seed, 3, i, banks[i].length)], ctx).replace(/\s+/g, " ").trim();
    return text.charAt(0).toUpperCase() + text.slice(1);
  });
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
          `<img data-pe-slot="${slots[index]}" data-credit="iComply Property Services" src="${escapeHtml(image.src)}" alt="${escapeHtml(image.alt)}" width="1200" height="630">`
        )).join("")
        + `<figcaption>Image credit: iComply Property Services</figcaption></figure>`;
      return { html, heroSrc: picked[0].src, ogImage: absoluteOg(picked[0].src) };
    }
  }
  const slug = String(serviceSlug || "fire-alarms");
  const family = aliasFamily(slug);
  const picks = pickImages(`${slug}/${placeName}`, family, 3);
  const primary = family === "aov-air-handling" ? picks[0] : `/assets/images/services/${slug}.jpg`;
  const second = picks[1] || primary;
  const third = picks[2] || second;
  const creditFor = (src) => (/\/demolition(?:-(?:photo|context))?\.jpe?g$/i.test(src) ? "Tomwsulcer (CC0)" : "iComply Property Services");
  const altHero = `${subject} in ${placeName}`;
  const altWork = `${subject} equipment prepared for ${placeName}`;
  const altContext = `Property compliance visit arranged for ${placeName}`;
  const html = `<figure class="quality-bar-images" data-pe-slot="q2-thin-images">`
    + `<img data-pe-slot="q2-image-hero" data-credit="${escapeHtml(creditFor(primary))}" src="${primary}" alt="${escapeHtml(altHero)}" width="1200" height="630">`
    + `<img data-pe-slot="q2-image-work" data-credit="${escapeHtml(creditFor(second))}" src="${second}" alt="${escapeHtml(altWork)}" width="1200" height="630">`
    + `<img data-pe-slot="q2-image-context" data-credit="${escapeHtml(creditFor(third))}" src="${third}" alt="${escapeHtml(altContext)}" width="1200" height="630">`
    + `<figcaption>Image credit: ${escapeHtml(creditFor(primary))}</figcaption></figure>`;
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
      `We send a free written quote for ${ctx.subject} in ${ctx.place} once we know the building and how we get in${/eicr|electrical inspection/i.test(ctx.subject) ? ". EICRs start from £160" : ""}. Nothing is added without your say-so.`,
    ],
    [
      `Where is the team that covers ${ctx.place} based?`,
      `Our engineers cover ${ctx.place} from our base at 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Same-day and next-day slots are often available.`,
    ],
    [
      `What do you need from me to book ${ctx.subject} in ${ctx.place}?`,
      `Just the postcode, the type of property and anything you already know about the system. A couple of photos help. Call or WhatsApp 07517 806082, or email icomplypropertyservices@gmail.com.`,
    ],
  ];
  if (ctx.gas) {
    faqs.push([
      `Who carries out gas work for this ${ctx.place} visit?`,
      "Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.",
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
