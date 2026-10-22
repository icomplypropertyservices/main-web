/**
 * P0 trades, roofing, electrical and AOV pages from the 21 Oct 2026 lock.
 * Keyword hubs and the town essay used by the edge renderer.
 */
import { heroCta } from "./hero-cta.js";
import { landlordNote } from "./landlord-note.js";
import { creditName as licensedCredit } from "./quality-floor.js";
import { pickImages } from "./image-pool.js";
import { breadcrumbHtml } from "./link-blocks.js";
import { MATRIX_CSS, MATRIX_HEADER, MATRIX_FOOTER, MATRIX_SITE_FOOTER } from "./page-shell.js";
import { LOCAL_TOWN_SLUGS } from "./dual-ring-allowlist.js";
import indexDoc from "../../website/data/seo-lock/p0-index.json" with { type: "json" };
import copyDoc from "../../website/data/seo-lock/family-copy.json" with { type: "json" };
import gmTowns from "../../website/data/gm-blurbs/_gm_towns.json" with { type: "json" };

const ROWS = indexDoc.rows || {};
const COPY = copyDoc.families || {};
const TOPICS = copyDoc.topics || {};
const GM = gmTowns.filter((row) => Array.isArray(row) && row[0]);

export const TRADE_ALIAS = {
  plasterer: "plastering",
  plasterers: "plastering",
  builder: "building-maintenance",
  builders: "building-maintenance",
  joiner: "joinery",
  joiners: "joinery",
  bricklayers: "brickwork",
  "kitchen-fitter": "kitchens",
  "kitchen-fitters": "kitchens",
};

const NEW_SERVICES = new Set(["drainage", "driveways", "fencing", "demolition"]);

function hashStr(value) {
  let h = 2166136261;
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h >>> 0;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

const ACRONYM = {
  aov: "AOV", grp: "GRP", epdm: "EPDM", eicr: "EICR", ev: "EV", pir: "PIR",
  upvc: "uPVC", lvt: "LVT", cctv: "CCTV", cp12: "CP12", pat: "PAT", hmo: "HMO",
  cdm: "CDM", co: "CO", tv: "TV", ip: "IP", led: "LED", rcd: "RCD", mcb: "MCB",
};

const VERB = {
  fit: "fitting", fitting: "fitting", swap: "replacement", replace: "replacement",
  replacement: "replacement", install: "installation", installation: "installation",
  repair: "repair", repairs: "repairs", service: "servicing", servicing: "servicing",
};

function sentenceToken(part, index) {
  const lower = String(part).toLowerCase();
  if (ACRONYM[lower]) return ACRONYM[lower];
  if (index === 0) return lower.charAt(0).toUpperCase() + lower.slice(1);
  return lower;
}

function naturalizeSlug(slug) {
  const parts = String(slug).split("-").filter(Boolean);
  if (!parts.length) return "";
  const last = parts[parts.length - 1].toLowerCase();
  if (VERB[last]) parts[parts.length - 1] = VERB[last];
  const out = [];
  for (let i = 0; i < parts.length; i++) {
    if (parts[i].toLowerCase() === "d" && String(parts[i + 1] || "").toLowerCase() === "h") {
      out.push("D+H");
      i += 1;
      continue;
    }
    out.push(sentenceToken(parts[i], out.length));
  }
  return out.join(" ");
}

export function spokenSubject(slug, name) {
  const titled = titleCaseSlug(slug);
  const given = String(name || "").trim();
  if (!given || given.toLowerCase() !== titled.toLowerCase()) return given || titled;
  const topic = TOPICS[slug] || topicFor(slug, "");
  if (topic) {
    const lead = String(topic).split(/\s+is\s+/i)[0].replace(/^(?:an|a)\s+/i, "").trim();
    if (lead && lead.length >= 3 && lead.length <= 48 && !/[.?!,:]/.test(lead)) {
      return lead.charAt(0).toUpperCase() + lead.slice(1);
    }
  }
  return naturalizeSlug(slug) || given;
}

function titleCaseSlug(slug) {
  return String(slug)
    .split("-")
    .filter(Boolean)
    .map((part) => {
      if (part === "aov") return "AOV";
      if (part === "grp") return "GRP";
      if (part === "epdm") return "EPDM";
      if (part === "eicr") return "EICR";
      if (part === "ev") return "EV";
      if (part === "pir") return "PIR";
      if (part === "upvc") return "uPVC";
      if (part === "lvt") return "LVT";
      if (part === "d" ) return "D";
      return part.charAt(0).toUpperCase() + part.slice(1);
    })
    .join(" ")
    .replace(/\bD H\b/g, "D+H");
}

export function tradesRow(slug) {
  return ROWS[slug] || null;
}

export function tradesHas(slug) {
  return Boolean(ROWS[slug]);
}

export function tradesIsNewService(slug) {
  return NEW_SERVICES.has(slug);
}

export function tradesRows() {
  return ROWS;
}

function intentOf(slug) {
  const s = String(slug);
  if (s.startsWith("emergency-") || s.includes("-emergency")) return "emergency";
  if (/-near-me$/.test(s)) return "near";
  if (/-cost$|-prices$/.test(s)) return "cost";
  if (/-quote$/.test(s)) return "quote";
  if (/installers|installer|plasterers|roofers|joiners|bricklayers|fitters|decorators|renderers/.test(s)) return "installers";
  if (/servicing|service|repairs|repair|repointing|regrouting|maintenance/.test(s)) return "servicing";
  return "guide";
}

function topicFor(slug, family) {
  const stem = String(slug)
    .replace(/^emergency-/, "")
    .replace(/-(near-me|cost|prices|quote|cheap|affordable)$/g, "");
  if (TOPICS[stem]) return TOPICS[stem];
  const keys = Object.keys(TOPICS).sort((a, b) => b.length - a.length);
  for (const key of keys) {
    if (stem === key || stem.startsWith(`${key}-`) || stem.endsWith(`-${key}`) || stem.includes(key)) {
      return TOPICS[key];
    }
  }
  const fam = COPY[family] || COPY.roofing;
  return fam.fallback;
}

function pickParas(family, seed, count) {
  const fam = COPY[family] || COPY["general-building"];
  const bank = fam.paragraphs || [];
  if (!bank.length) return [];
  const start = seed % bank.length;
  let step = bank.length > 1 ? (seed % (bank.length - 1)) + 1 : 1;
  const gcd = (a, b) => (b ? gcd(b, a % b) : a);
  if (gcd(step, bank.length) !== 1) step = 1;
  const out = [];
  const take = Math.min(count, bank.length);
  for (let n = 0; n < take; n++) out.push(bank[(start + n * step) % bank.length]);
  return out;
}

function words(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

function eicrLine(slug) {
  return /eicr|electrical-safety-certificate|landlord-eicr/.test(slug)
    ? " An EICR is listed from £160. "
    : " ";
}

export function tradesEssay(slug, placeName, townSlug) {
  const row = ROWS[slug];
  const family = row?.family || "general-building";
  const fam = COPY[family] || COPY["general-building"];
  const name = spokenSubject(slug, row?.name || titleCaseSlug(slug));
  const place = placeName || "your town";
  const intent = intentOf(slug);
  const topic = topicFor(slug, family);
  const seed = hashStr(`${slug}|${townSlug || "hub"}|${family}`);
  const paras = pickParas(family, seed, 6).map((p) => p.replaceAll("{name}", name).replaceAll("{place}", place).replaceAll("{work}", fam.work));
  const intentCopy = (fam.intents && fam.intents[intent]) || fam.intents.guide;
  const townLine = townSlug
    ? `${place} is one of the towns we cover from 17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE. Tell us the postcode and whether anyone is living in the property. The survey is free, the written quote is free, and you can start with a free chat on the phone or WhatsApp.`
    : `We cover Greater Manchester and the towns within 50 miles of Manchester or Burnley from our Stockport base. The survey is free, the written quote is free, and a chat costs nothing.`;
  const price = `We do not put a rate card on this page.${eicrLine(slug)}The price follows the building, the access and the materials, and it is written down before anyone starts.`;
  const checks = fam.checks || [];
  const work = fam.work || "the work";
  const article = /^[aeiou]/i.test(work) ? "an" : "a";
  const frames = [
    (item) => `In ${place}, ${article} ${work} survey looks at ${item}. That is what we write into the quote.`,
    (item) => `Before anyone starts ${work} in ${place}, we ask about ${item}.`,
    (item) => `People in ${place} often only notice ${item} once we are stood in front of it.`,
    (item) => `A photo on WhatsApp is not enough for ${item}. We look at it on site and say what we found.`,
    (item) => `The written quote for ${place} names ${item}, so making good is not a surprise at the end.`,
    (item) => `If ${item} is worse than it looked, ${work} in ${place} takes longer and we say so before we book the day.`,
    (item) => `The free survey includes ${item}. You see it with us, rather than reading it for the first time on an invoice.`,
    (item) => `Access, waste and the neighbours all sit next to ${item}. In ${place} we plan that before the van arrives.`,
    (item) => `When ${work} is finished in ${place}, ${item} should be left so the next trade, or you, can live with it.`,
    (item) => `We check ${item} once and write the result down. You should not have to pay a second visit to discover it.`,
    (item) => `The age of the house and the weather both affect ${item}. Around ${place} we allow time for that in the quote.`,
    (item) => `The part people forget until the end is ${item}. We put it on the list at the survey.`,
  ];
  const checkParas = [];
  if (checks.length) {
    const take = Math.min(10, checks.length);
    for (let n = 0; n < take; n++) {
      const idx = (seed + n * 3) % checks.length;
      const frame = frames[(seed + n) % frames.length];
      checkParas.push(frame(checks[idx]));
    }
  }
  const guide = String(fam.guide || "").replaceAll("{name}", name).replaceAll("{place}", place).replaceAll("{work}", fam.work);
  const chunks = [
    `This page is about ${name}${townSlug ? ` in ${place}` : ""}. ${topic}`,
    intentCopy.replaceAll("{name}", name).replaceAll("{place}", place).replaceAll("{work}", fam.work),
    guide,
    price,
    townLine,
    ...paras,
    ...checkParas,
  ];
  return chunks.map((p) => String(p).replace(/\s+/g, " ").trim()).filter(Boolean);
}

export function tradesFaqs(slug, placeName) {
  const row = ROWS[slug];
  const name = spokenSubject(slug, row?.name || titleCaseSlug(slug));
  const place = placeName || "your area";
  const eicr = /eicr|electrical-safety-certificate|landlord-eicr/.test(slug);
  return [
    [
      `How do I get a quote for ${name} in ${place}?`,
      `Call 07517 806082 or send a WhatsApp with the address and a photo. The survey is free. You get a written quote before any work is agreed. ${eicr ? "EICRs are listed from £160." : "There is no rate card on this page."}`,
    ],
    [
      `Do you cover ${place}?`,
      `Yes. ${name} in ${place} is arranged from our office at 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. Same-day and next-day visits are often available when the diary allows. We will say so honestly if the date you want is full.`,
    ],
    [
      `Who is this for?`,
      `Homeowners and landlords. We explain the work in plain language, book around people who live in the house, and email the quote to you.${/gas|cp12|boiler/i.test(slug) ? " Gas work is carried out by Gas Safe registered engineers." : ""}`,
    ],
    [
      `What should I send before the visit?`,
      `The postcode, what has failed or what you want built, and a photo if you have one. You do not need to know the trade name. ${landlordNote(`/pages/keywords/${slug}/${place}`, name, place)}`,
    ],
  ];
}

function imageKey(slug, family) {
  const stem = String(slug)
    .replace(/^emergency-/, "")
    .replace(/-(near-me|cost|prices|quote)$/g, "");
  return `${family}--${stem}`;
}

export function tradesImages(slug, placeName) {
  const isTrade = NEW_SERVICES.has(slug);
  const row = ROWS[slug] || {
    family: isTrade ? slug : "general-building",
    service: isTrade ? slug : "building-maintenance",
    name: titleCaseSlug(slug),
  };
  const key = imageKey(slug, row.family);
  const service = row.service || "building-maintenance";
  const aov = service === "aov-air-handling" || service === "aov" || row.family === "aov";
  const picks = aov ? pickImages(`/pages/${slug}/${placeName || ""}`, "aov-air-handling", 3) : null;
  const real = picks ? picks[0] : `/assets/images/services/${service}.jpg`;
  const tradePhoto = ["fencing", "drainage", "driveways", "demolition"].includes(service) || ["fencing", "drainage", "driveways", "demolition"].includes(row.family);
  const src = picks ? picks[1] : (tradePhoto ? real : `/assets/images/trades/${key}.webp`);
  const third = picks ? picks[2] : src;
  const name = spokenSubject(slug, row.name || titleCaseSlug(slug));
  const place = placeName ? ` in ${placeName}` : "";
  const alt = `${name}${place}, ${row.family.replace(/-/g, " ")} photograph`;
  const creditAttr = (file) => {
    const credit = licensedCredit(file);
    return credit ? ` data-credit="${escapeHtml(credit)}"` : "";
  };
  const html = `<figure class="quality-bar-images trades-images" data-pe-slot="real-photo" data-image-key="${escapeHtml(key)}" data-image-family="${escapeHtml(row.family)}" data-real-job-photo="owner">`
    + `<img${creditAttr(real)} data-pe-slot="real-photo" src="${real}" alt="${escapeHtml(`Photograph of ${name}${place}`)}" width="1200" height="675">`
    + `<img${creditAttr(src)} src="${src}" alt="${escapeHtml(alt)}" width="1200" height="675" loading="lazy">`
    + `<img${creditAttr(third)} src="${third}" alt="${escapeHtml(`${name} detail${place}`)}" width="1200" height="675" loading="lazy">`
    + `</figure>`;
  return { html, src, key, family: row.family, alt };
}

export function tradesLinkHtml(slug, townSlug) {
  const row = ROWS[slug];
  const family = row?.family || (NEW_SERVICES.has(slug) ? slug : "general-building");
  const service = row?.service || (NEW_SERVICES.has(slug) ? slug : "building-maintenance");
  const siblings = Object.entries(ROWS)
    .filter(([other, meta]) => other !== slug && meta.family === family && meta.kind !== "job")
    .slice(0, 24);
  const towns = (family === "aov" ? LOCAL_TOWN_SLUGS : GM.map((r) => r[0])).filter((t) => t !== townSlug);
  const pageHref = (town) => {
    if (row) return town ? `/pages/keywords/${slug}/${town}` : `/pages/keywords/${slug}`;
    return town ? `/pages/${slug}/${town}` : `/pages/services/${slug}`;
  };
  const items = [];
  items.push(`<li><a href="/pages/services/${service}">${escapeHtml(titleCaseSlug(service))} hub</a></li>`);
  const spoken = spokenSubject(slug, row?.name || titleCaseSlug(slug));
  items.push(`<li><a href="${pageHref("")}">${escapeHtml(spoken)} guide</a></li>`);
  items.push(`<li><a href="/pages/services">All services</a></li>`);
  items.push(`<li><a href="/pages/areas">Areas we cover</a></li>`);
  items.push(`<li><a href="/contact">Contact and free quote</a></li>`);
  for (const [other, meta] of siblings) {
    const href = townSlug ? `/pages/keywords/${other}/${townSlug}` : `/pages/keywords/${other}`;
    items.push(`<li><a href="${href}">${escapeHtml(spokenSubject(other, meta.name))}</a></li>`);
  }
  const townNames = new Map(GM.map((r) => [r[0], r[1]]));
  for (const town of towns) {
    const label = townNames.get(town) || titleCaseSlug(town);
    items.push(`<li><a href="${pageHref(town)}">${escapeHtml(spoken + " in " + label)}</a></li>`);
  }
  const relatedServices = ["plastering", "roofing", "electrical", "aov-air-handling", "kitchens", "bathrooms", "drainage", "brickwork"]
    .filter((s) => s !== service);
  for (const s of relatedServices) {
    items.push(`<li><a href="/pages/services/${s}">${escapeHtml(titleCaseSlug(s))}</a></li>`);
  }
  return `<section class="related-links"><h2>Related pages</h2><ul>${items.join("")}</ul></section>`;
}

function faqHtml(faqs, place) {
  return `<section id="faq" class="faq"><h2>Questions about ${escapeHtml(place || "this work")}</h2>${faqs.map(([q, a]) => (
    `<details class="faq-item"><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`
  )).join("")}</section>`;
}

function faqSchema(faqs) {
  return {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: faqs.map(([q, a]) => ({
      "@type": "Question",
      name: q,
      acceptedAnswer: { "@type": "Answer", text: a },
    })),
  };
}

export function tradesProseHtml(slug, placeName, townSlug) {
  const chunks = tradesEssay(slug, placeName, townSlug);
  return chunks.map((p) => `<p>${escapeHtml(p)}</p>`).join("");
}

export function renderTradesHub(slug) {
  const row = ROWS[slug];
  if (!row) return null;
  const name = spokenSubject(slug, row.name);
  const h1 = name;
  const title = [`${name} | Free survey | iComply`, `${name} | iComply`].find((item) => item.length <= 60)
    || `${name.slice(0, 48).replace(/\s+\S*$/, "")} | iComply`;
  const description = `${name} for homeowners and landlords. Free survey, free written quote and a free chat. Call 07517 806082 or WhatsApp. Based in Stockport.`.slice(0, 155);
  const faqs = tradesFaqs(slug, "the North West");
  const images = tradesImages(slug, "");
  const canonical = `https://icomplypropertyservices.co.uk/pages/${row.kind === "job" ? "jobs" : "keywords"}/${slug}`;
  const crumbs = breadcrumbHtml([
    { href: "/", label: "Home" },
    { href: "/pages/services", label: "Services" },
    { href: `/pages/services/${row.service}`, label: titleCaseSlug(row.service) },
    { label: name },
  ]);
  const schema = faqSchema(faqs);
  const serviceSchema = {
    "@context": "https://schema.org",
    "@type": "Service",
    name,
    serviceType: titleCaseSlug(row.service),
    provider: {
      "@type": "LocalBusiness",
      name: "iComply Property Services",
      telephone: "+447517806082",
      email: "icomplypropertyservices@gmail.com",
    },
  };
  return `<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${escapeHtml(title)}</title>
<meta name="description" content="${escapeHtml(description)}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="${canonical}">
${MATRIX_CSS}
<script type="application/ld+json">${JSON.stringify(schema).replace(/</g, "\\u003c")}</script>
<script type="application/ld+json">${JSON.stringify(serviceSchema).replace(/</g, "\\u003c")}</script>
</head>
<body class="mx">
${MATRIX_HEADER}
<main>
${crumbs}
<h1>${escapeHtml(h1)}</h1>${heroCta(h1)}
${images.html}
<article id="local-copy" data-trades-lock="1">
${tradesProseHtml(slug, "", "")}
${tradesLinkHtml(slug, "")}
${faqHtml(faqs, name)}
</article>
${MATRIX_FOOTER}
</main>
${MATRIX_SITE_FOOTER}
</body>
</html>`;
}

export function tradesWordCount(html) {
  return words(String(html).replace(/<script[\s\S]*?<\/script>/gi, " ").replace(/<style[\s\S]*?<\/style>/gi, " ").replace(/<[^>]+>/g, " "));
}

export { words, GM, LOCAL_TOWN_SLUGS };
