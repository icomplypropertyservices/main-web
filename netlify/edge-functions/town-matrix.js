import { scrubMeta } from "../lib/scrub-meta.js";
import { MATRIX_CSS, MATRIX_HEADER, MATRIX_FOOTER, MATRIX_SITE_FOOTER } from "../lib/page-shell.js";
/**
 * Unique town × keyword, town × service, manufacturer × town and job × town HTML.
 * Catalogue JSON is written by website/includes/matrix-catalogue.php.
 * Keyword variants ({service}--{stem}--…) and /matrix-sitemap/{n}.xml are
 * decoded from assets/matrix/variants.json. Dual-ring towns (269) are
 * index,follow for keyword, service and job pages. Manufacturer×town stays
 * on the Greater Manchester core. Other places stay noindex.
 */
import { renderVariantPage, renderVariantSitemap } from "../lib/variant-matrix.js";
import { breadcrumbHtml, isGmTown, isLocalTown, matrixRelatedHtml } from "../lib/link-blocks.js";
import { gmTownBlurb } from "../lib/gm-blurbs.js";
import { p0KeepsTown, renderNationwideP0Town } from "../lib/nationwide-p0.js";
import { withContactWidgets } from "../lib/contact-widgets.js";
import { applyMoneyCtr } from "../lib/money-ctr.js";
import { townFactsBlock } from "../lib/town-facts.js";
import { depthBankParagraphs, thinFaqHtml, thinFaqs, thinImages, thinOgMeta, thinProseHtml } from "../lib/thin-quality-bar.js";
import { townDepthBlock } from "../lib/town-depth.js";
import fireAlarmFamily from "../../website/data/fire-alarm-installer-family.json" with { type: "json" };
import mainlandTownDoc from "../../website/data/uk-mainland-towns-10k.json" with { type: "json" };
import gscKeepDoc from "../../website/data/gsc-keep-towns.json" with { type: "json" };

// Town pages Google already ranks (GSC impressions). Never 301 these to a hub;
// render them index,follow so the rankings are kept.
const GSC_KEEP = new Set(gscKeepDoc.paths || []);
const CTR_PHONE = "07517 806082";

function ctrPriceFrom(slug) {
  const s = String(slug || "");
  if (/eicr|electrical-(safety|inspection|installation-condition)|landlord-electrical/.test(s)) return "£160";
  return "";
}

export function ctrTitle(subject, town, slug) {
  const price = ctrPriceFrom(slug);
  const opts = [];
  if (price) {
    opts.push(`${subject} in ${town} from ${price} | Same/Next-Day`);
    opts.push(`${subject} ${town} from ${price} | Same/Next-Day`);
    opts.push(`${subject} ${town} from ${price}`);
  }
  opts.push(`${subject} in ${town} | Same/Next-Day Engineers`);
  opts.push(`${subject} in ${town} | Same/Next-Day`);
  opts.push(`${subject} ${town} | Same/Next-Day`);
  opts.push(`${subject} in ${town} | iComply`);
  for (const o of opts) if (o.length <= 60) return o;
  return `${`${subject} ${town}`.slice(0, 50).replace(/\s+\S*$/, "")} | iComply`;
}

export function ctrDescription(subject, town, slug, gas) {
  const price = ctrPriceFrom(slug);
  const lead = `${subject} in ${town}${price ? ` from ${price}` : ""}.`;
  const who = gas ? " Same/next-day visits, carried out by Gas Safe registered engineers." : " Same/next-day engineers from our Stockport team.";
  for (const d of [
    `${lead}${who} Fixed written quote, certificates emailed. Call or WhatsApp ${CTR_PHONE}.`,
    `${lead}${who} Call or WhatsApp ${CTR_PHONE}.`,
    `${lead} Same/next-day. Call ${CTR_PHONE}.`,
  ]) if (d.length <= 160) return d;
  return clampMeta(`${lead} Same/next-day. Call ${CTR_PHONE}.`);
}

const FIRE_P0 = new Set(fireAlarmFamily.p0 || []);
const FIRE_HUBS = new Set(fireAlarmFamily.hubs || []);
const MAINLAND_TOWNS = (mainlandTownDoc.towns || []).filter((town) => town && town.slug && Number(town.population) > 10000);
const MAINLAND = new Map(MAINLAND_TOWNS.map((town) => [town.slug, town]));
const NEAREST_MAINLAND = new Map();

function haversineMiles(a, b) {
  const earth = 3958.8;
  const dLat = ((b.lat - a.lat) * Math.PI) / 180;
  const dLon = ((b.lng - a.lng) * Math.PI) / 180;
  const lat1 = (a.lat * Math.PI) / 180;
  const lat2 = (b.lat * Math.PI) / 180;
  const h = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;
  return earth * (2 * Math.atan2(Math.sqrt(h), Math.sqrt(Math.max(0, 1 - h))));
}

function nearestMainland(slug, limit = 12) {
  const key = `${slug}:${limit}`;
  if (NEAREST_MAINLAND.has(key)) return NEAREST_MAINLAND.get(key);
  const here = MAINLAND.get(slug);
  if (!here) return [];
  const ranked = MAINLAND_TOWNS
    .filter((town) => town.slug !== slug)
    .map((town) => ({ town, miles: haversineMiles(here, town) }))
    .sort((a, b) => a.miles - b.miles)
    .slice(0, limit)
    .map((row) => row.town);
  NEAREST_MAINLAND.set(key, ranked);
  return ranked;
}

function fireInstallerNationwide(keyword, town) {
  return FIRE_P0.has(keyword) && MAINLAND.has(town);
}

const FIRE_DUTY = "Design, installation and commissioning follow BS 5839, carried out by experienced fire alarm engineers.";
const FIRE_NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE";

const SERVICE_HUB_ALIAS = {
  "eicr": "/pages/jobs/eicr",
  "eicr-certificate": "/pages/jobs/eicr",
  "eicrs": "/pages/jobs/eicr",
  "electrical-safety": "/pages/jobs/eicr",
  "pat": "/pages/services/pat-testing",
  "gas-safety": "/pages/services/gas-systems",
  "gas-safety-certificate": "/pages/services/gas-systems",
  "fra": "/pages/services/fire-risk-assessments",
  "fire-risk-assessment": "/pages/services/fire-risk-assessments",
  "ev-charging": "/pages/services/ev-chargers",
};
import { securityJobRedirect } from "../lib/security-dual-ring.js";
import { SECURITY_NEW_INTENTS, securityTownCopy } from "../lib/security-town-copy.js";

const RESERVED = new Set([
  "keywords", "services", "areas", "manufacturers", "resources", "packages",
  "jobs", "commercial", "aov", "barriers", "aov-air-handling", "products",
  "shop", "emergency-lighting-jobs", "asbestos-jobs",
]);

const FAMILY = new Set([
  "fire-alarms", "fire-risk-assessments", "fire-extinguishers", "fire-doors",
  "fire-stopping", "fire-suppression", "fire-signage", "fire-compartmentation",
  "dry-risers", "emergency-lighting", "barriers", "aov-air-handling",
]);

// Mirrors website fire-safety category plus AOV and barriers. These town pages stay UK-wide.
const NATIONWIDE_TOWN = new Set([
  "aov", "barriers", "aov-air-handling",
  "fire-alarms", "emergency-lighting", "fire-risk-assessments", "fire-extinguishers",
  "fire-doors", "fire-stopping", "fire-suppression", "sprinkler-systems", "dry-risers",
  "fire-signage", "evacuation-alerts", "kitchen-fire-suppression", "fire-compartmentation",
  "smoke-co-alarms",
]);

const IMAGE_SLUG = {};

const AUDIENCES = [
  "landlords",
  "managing agents",
  "facilities managers",
  "commercial tenants",
  "HMO operators",
  "care home managers",
  "school and college sites",
  "warehouse and industrial units",
  "retail units",
  "housing associations",
];

// Building trades: homeowner/landlord searchers ("plasterer manchester"), not
// facilities buyers. Trade nouns match what people type into Google.
const BUILD_TRADES = {
  plastering: "Plasterers",
  kitchens: "Kitchen Fitters",
  extensions: "Extension Builders",
  joinery: "Joiners",
  "loft-conversions": "Loft Conversion Builders",
  renovation: "Renovation Builders",
  "building-maintenance": "Builders",
  bathrooms: "Bathroom Fitters",
  bricklaying: "Bricklayers",
  "dry-lining": "Dryliners",
};
const BUILD_AUDIENCES = ["homeowners", "landlords", "homeowners and landlords"];

const VISIT_STYLES = [
  "a planned daytime visit",
  "an out-of-hours attendance where the site cannot close",
  "a landlord turnaround between tenancies",
  "a facilities visit booked around occupied floors",
  "a survey followed by a return visit for the agreed scope",
];

const GAS_SENTENCE = "Gas work is carried out by Gas Safe registered engineers.";

function stripBriefTokens(text) {
  return String(text || "")
    .replace(/\bb\d{4,6}\b/g, "")
    .replace(/\s{2,}/g, " ")
    .replace(/\s+([,.;:])/g, "$1")
    .trim();
}

function hashStr(value) {
  let h = 2166136261;
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h >>> 0;
}

function mix32(h) {
  h = Math.imul(h ^ (h >>> 16), 0x85ebca6b);
  h = Math.imul(h ^ (h >>> 13), 0xc2b2ae35);
  return (h ^ (h >>> 16)) >>> 0;
}

function pick(list, seed) {
  return list[seed % list.length];
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function titleCaseSlug(slug) {
  return String(slug)
    .split("-")
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");
}

function words(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

function clampMeta(text) {
  const clean = String(text).replace(/\s+/g, " ").trim();
  if (clean.length <= 158) return clean;
  const cut = clean.slice(0, 158).replace(/\s+\S*$/, "");
  return cut.length >= 40 ? cut : clean.slice(0, 158);
}

function jsonLd(value) {
  return JSON.stringify(value).replace(/</g, "\\u003c");
}

function isFamilyService(slug, catalogue) {
  const family = catalogue.services && catalogue.services.family;
  if (Array.isArray(family) && family.length) return family.includes(slug);
  return FAMILY.has(slug);
}

function resolvePlace(townSlug, catalogue) {
  const places = catalogue.places || {};
  const nationwide = catalogue.nationwide || {};
  if (places[townSlug]) return { place: places[townSlug], tier: "core" };
  if (nationwide[townSlug]) return { place: nationwide[townSlug], tier: "nationwide" };
  return {
    place: {
      name: titleCaseSlug(townSlug),
      region: "North West",
      country: "England",
      population: 0,
      housing: "",
      industry: "",
      neighbours: [],
      synthetic: true,
    },
    tier: "other",
  };
}

export function renderTownPage(input) {
  const catalogue = input.catalogue || {};
  const keywords = catalogue.keywords || {};
  const services = catalogue.services || {};
  const labels = services.labels || services;
  const serviceKeywords = services.keywords || {};
  const kind = input.kind === "service" || input.kind === "manufacturer" || input.kind === "job"
    ? input.kind
    : "keyword";

  let keyword = null;
  let serviceSlug = "";
  let serviceName = "";
  let keywordKnown = false;
  let nationwideFlag = false;
  let path = "";
  let subject = "";

  if (kind === "manufacturer") {
    const brand = catalogue.manufacturers && catalogue.manufacturers[input.brand];
    if (!brand || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.brand || "")) return null;
    subject = brand.name || titleCaseSlug(input.brand);
    serviceSlug = brand.service || "building-maintenance";
    serviceName = labels[serviceSlug] || titleCaseSlug(serviceSlug);
    nationwideFlag = Boolean(brand.nationwide) || isFamilyService(serviceSlug, catalogue);
    keywordKnown = true;
    keyword = {
      name: subject,
      service: serviceSlug,
      intro: `We service, repair and maintain ${subject} equipment for landlords, agents and commercial sites, with engineers based in Stockport.`,
      focus: [
        `${subject} parts identified before the visit`,
        "Fixed written quote before work starts",
        "Certificates and reports emailed after the visit",
      ],
      gas: serviceSlug === "gas-systems",
    };
    path = `/pages/manufacturers/${input.brand}/${input.town}`;
  } else if (kind === "job") {
    const job = catalogue.jobs && catalogue.jobs[input.job];
    if (!job || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.job || "")) return null;
    subject = job.name || titleCaseSlug(input.job);
    serviceSlug = job.service || "building-maintenance";
    serviceName = labels[serviceSlug] || titleCaseSlug(serviceSlug);
    nationwideFlag = Boolean(job.nationwide) || isFamilyService(serviceSlug, catalogue);
    keywordKnown = true;
    keyword = {
      name: subject,
      service: serviceSlug,
      intro: `${subject} carried out by our ${serviceName} engineers, with a fixed written quote for your building.`,
      focus: job.focus && job.focus.length ? job.focus : [
        "Same-day and next-day slots where available",
        "Fixed written quote before work starts",
        "Clear written report after the visit",
      ],
      gas: Boolean(job.gas) || serviceSlug === "gas-systems",
    };
    path = `/pages/jobs/${input.job}/${input.town}`;
  } else if (kind === "keyword") {
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.keyword || "")) return null;
    keyword = keywords[input.keyword];
    keywordKnown = Boolean(keyword);
    if (!keyword) {
      const blob = input.keyword.replace(/-/g, " ");
      const gas = /\b(cp12|cp44|gas safety|boiler|gas certificate|landlord gas)\b/.test(blob)
        && !/\b(gas suppression|inert gas|clean agent)\b/.test(blob);
      keyword = {
        name: titleCaseSlug(input.keyword),
        service: gas ? "gas-systems" : "building-maintenance",
        intro: "",
        focus: [
          "Same-day and next-day slots where available",
          "Fixed written quote before work starts",
          "Certificates and reports emailed after the visit",
        ],
        gas,
        synthetic: true,
      };
    }
    serviceSlug = keyword.service || "electrical";
    serviceName = labels[serviceSlug] || titleCaseSlug(serviceSlug);
    subject = keyword.name;
    nationwideFlag = isFamilyService(serviceSlug, catalogue);
    path = `/pages/keywords/${input.keyword}/${input.town}`;
  } else {
    serviceSlug = input.service;
    serviceName = labels[serviceSlug];
    if (!serviceName || RESERVED.has(serviceSlug)) return null;
    keywordKnown = true;
    const related = (serviceKeywords[serviceSlug] || [])[0];
    const relatedKw = related ? keywords[related] : null;
    keyword = {
      name: serviceName,
      service: serviceSlug,
      intro: relatedKw && relatedKw.intro
        ? relatedKw.intro
        : `${serviceName} for landlords, managing agents and commercial sites, arranged from Stockport.`,
      focus: relatedKw && relatedKw.focus && relatedKw.focus.length
        ? relatedKw.focus
        : [
          `Survey and scope for ${serviceName}`,
          "Fixed written quote before work starts",
          "Certificates and reports emailed after the visit",
        ],
      gas: serviceSlug === "gas-systems" || Boolean(relatedKw && relatedKw.gas),
    };
    subject = serviceName;
    nationwideFlag = isFamilyService(serviceSlug, catalogue);
    path = `/pages/${serviceSlug}/${input.town}`;
  }

  const townSlug = input.town;
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(townSlug || "")) return null;
  const resolved = resolvePlace(townSlug, catalogue);
  const place = { ...resolved.place };
  const tier = resolved.tier;
  const fireTown = kind === "keyword" && fireInstallerNationwide(input.keyword, townSlug);
  const fireFamily = kind === "keyword" && FIRE_HUBS.has(input.keyword);
  const mainlandPlace = fireTown ? MAINLAND.get(townSlug) : null;
  if (mainlandPlace) {
    place.name = mainlandPlace.name || place.name;
    place.region = mainlandPlace.region || place.region;
    place.country = mainlandPlace.nation || place.country;
    place.population = Number(mainlandPlace.population) || place.population;
    place.county = mainlandPlace.county || "";
  }
  const indexable = keywordKnown && !keyword.synthetic && (tier === "core" || fireTown || GSC_KEEP.has(path));
  // Jack: every real page is indexable. Never emit noindex here.
  const robots = "index, follow";
  const seed = hashStr(`${kind}|${subject}|${serviceSlug}|${townSlug}`);
  const buildTrade = kind === "service" ? BUILD_TRADES[serviceSlug] : null;
  const audience = buildTrade ? pick(BUILD_AUDIENCES, seed) : pick(AUDIENCES, seed);
  const visit = pick(VISIT_STYLES, seed >>> 3);
  const neighbours = Array.isArray(place.neighbours) ? place.neighbours.filter(Boolean) : [];
  const near = neighbours.length ? neighbours.join(", ") : "Stockport, Manchester and the surrounding North West";
  const pop = place.population
    ? `${place.name} is home to about ${Number(place.population).toLocaleString("en-GB")} people.`
    : `We work across ${place.name} every week.`;
  const housing = place.housing
    ? `Census housing in ${place.name} includes ${place.housing}.`
    : `Buildings in ${place.name} include rented homes, shops, offices and light-industrial units.`;
  const industry = place.industry
    ? `Local businesses in ${place.name} include ${place.industry}, so we plan visits around working hours.`
    : `We work on homes, shops, offices and industrial units across ${place.name}.`;
  const focus = (keyword.focus && keyword.focus.length ? keyword.focus : [`Scope agreed before ${subject} starts`]).slice(0, 4);
  const canonical = `https://icomplypropertyservices.co.uk${path}`;
  const h1 = buildTrade
    ? `${buildTrade} in ${place.name} | Free Quote: 07517 806082`
    : `${subject} for ${audience} in ${place.name}`;
  const ctrSlug = kind === "keyword" ? input.keyword : serviceSlug;
  const ctrGas = Boolean(keyword.gas) || serviceSlug === "gas-systems" || serviceSlug === "heating";
  const buildTitle = buildTrade ? `${buildTrade} ${place.name} | Free Quote 07517 806082` : "";
  const title = buildTitle && buildTitle.length <= 60 ? buildTitle : ctrTitle(subject, place.name, ctrSlug);
  const description = ctrDescription(subject, place.name, ctrSlug, ctrGas);
  const imageSlug = IMAGE_SLUG[serviceSlug] || serviceSlug;

  const gas = keyword.gas || serviceSlug === "gas-systems" || serviceSlug === "heating";
  const packed = (kind === "keyword" || kind === "service")
    ? gmTownBlurb(kind, kind === "keyword" ? input.keyword : serviceSlug, townSlug)
    : null;
  let prose = packed && packed.blurb ? stripBriefTokens(packed.blurb) : "";
  if (gas && prose && !/Gas Safe registered engineers/.test(prose)) {
    prose = `${prose} ${GAS_SENTENCE}`;
  }
  const proseService = String(serviceName).replace(/\b([A-Z])([a-z]+)/g, (m, first, rest) => first.toLowerCase() + rest);
  const qualityCtx = {
    kind,
    seed,
    place: place.name,
    subject: kind === "manufacturer" ? `${subject} ${proseService}` : subject,
    service: proseService,
    audience,
    visit,
    near,
    housing,
    industry,
    pop,
    gas,
    blurb: prose,
  };
  const closeQPages = input.closeQPages && typeof input.closeQPages === "object" ? input.closeQPages : null;
  const pageSlug = kind === "keyword"
    ? input.keyword
    : kind === "job"
      ? input.job
      : kind === "service"
        ? serviceSlug
        : "";
  const pe = closeQPages && pageSlug ? closeQPages[`${kind}/${pageSlug}/${townSlug}`] : null;
  // Facts-only local block (council, postcodes, wards, travel, census) for every service family.
  const facts = townFactsBlock({
    slug: townSlug,
    town: place.name,
    subject,
    service: serviceSlug,
    names: MAINLAND,
    hrefFor: null, // only the P0 renderer links siblings; other matrix towns may 301
  });
  if (facts) qualityCtx.short = true;
  // SEO PR145: the 15 new security intents get town-specific copy so each x town page stands alone;
  // that copy replaces the long generic template prose on the two borough pages with no centre point.
  const secLocal = kind === "keyword" && SECURITY_NEW_INTENTS.has(input.keyword) ? securityTownCopy(input.keyword, townSlug) : null;
  if (secLocal) qualityCtx.short = true;
  if (pe && typeof pe.body === "string" && pe.body.trim()) {
    // Lean pages keep the first two authored paragraphs; the rest is a service guide repeated in every town.
    qualityCtx.peBody = facts ? pe.body.trim().split(/\n\s*\n/).slice(0, 2).join("\n\n") : pe.body;
  }
  if (pe && Array.isArray(pe.faqs)) {
    qualityCtx.peFaqs = pe.faqs;
  }
  const images = thinImages(imageSlug, subject, place.name, seed, pe && Array.isArray(pe.images) ? pe.images : null);
  let proseHtml = thinProseHtml(qualityCtx);
  const faqs = thinFaqs(qualityCtx).slice();
  if (fireTown || fireFamily) {
    const county = place.county ? `${place.county}, ` : "";
    const population = place.population
      ? `Published population is about ${Number(place.population).toLocaleString("en-GB")}. `
      : "";
    proseHtml += `<p data-pe-slot="fire-installer-nap">${escapeHtml(`We install and maintain ${subject.toLowerCase()} in ${place.name} from our base at ${FIRE_NAP}. ${place.name} is in ${county}${place.region}, ${place.country}. ${population}${FIRE_DUTY} Call 07517 806082 for a fixed written quote.`)}</p>`;
  }
  if (fireTown) {
    faqs.push([
      `Do you cover ${place.name} for ${subject}?`,
      `Yes. Our engineers cover ${place.name} from ${FIRE_NAP}. ${FIRE_DUTY} Call 07517 806082 for a fixed written quote.`,
    ]);
  }
  if (facts) {
    faqs.unshift(...facts.faqs);
    // CLOSE-Q1: lean pages sat at 540-700 words; add town-specific depth (>= 800 total).
    const depth = townDepthBlock({
      slug: townSlug,
      town: place.name,
      subject,
      service: serviceSlug,
      names: MAINLAND,
      nation: place.country || "",
      extra: depthBankParagraphs(qualityCtx, 4),
      // G9: list-price pages (EICR from £160) never carry a POA line.
      priceFrom: ctrPriceFrom(ctrSlug) || ctrPriceFrom(input.keyword || "") || ctrPriceFrom(input.job || "") || (/from £160\b/.test(`${title} ${description} ${proseHtml}`) ? "£160" : ""),
    });
    // Seeded block order (authored copy, town facts, local notes) for town-to-town uniqueness.
    const blocks = [proseHtml, facts.html, depth ? depth.html : ""];
    // Never lead with the facts block: its "{town}: council..." heading would sit straight under the H1.
    const orders = [[0, 1, 2], [0, 2, 1], [2, 0, 1], [2, 1, 0]];
    proseHtml = orders[mix32(hashStr(`order|${townSlug}|${subject}`)) % orders.length].map((i) => blocks[i]).join("");
    const shift = faqs.length ? mix32(hashStr(`faq|${townSlug}|${subject}`)) % faqs.length : 0;
    faqs.push(...faqs.splice(0, shift));
  }
  if (secLocal) {
    proseHtml += secLocal.html;
    faqs.push(...secLocal.faqs);
  }
  const faqBlock = (fireTown || fireFamily || facts || secLocal)
    ? `<section id="faq" class="faq" data-pe-slot="q5-thin-faq"><h2>Questions about ${escapeHtml(place.name)}</h2>${faqs.map(([q, a]) => (
      `<details class="faq-item"><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`
    )).join("")}</section>`
    : thinFaqHtml(qualityCtx);

  const hubHref = kind === "keyword"
    ? `/pages/keywords/${input.keyword}`
    : kind === "job"
      ? `/pages/jobs/${input.job}`
      : kind === "manufacturer"
        ? `/pages/manufacturers/${input.brand}`
        : `/pages/services/${serviceSlug}`;
  const relatedHtml = matrixRelatedHtml(catalogue, {
    kind,
    subject,
    serviceSlug,
    serviceName,
    townSlug,
    townName: place.name,
    keyword: input.keyword || "",
    job: input.job || "",
    brand: input.brand || "",
    selfPath: path,
    // pages with real town facts drop the repeated all-GM link cloud (installer hub keeps its link set)
    lean: Boolean(facts) && input.keyword !== "fire-alarm-installer",
  });
  const crumbItems = [
    { href: "/", label: "Home" },
    { href: hubHref, label: subject },
  ];
  if (isLocalTown(townSlug)) {
    crumbItems.push({ href: `/pages/areas/${townSlug}`, label: place.name });
  }
  crumbItems.push({ label: `${subject} in ${place.name}` });
  const crumbs = breadcrumbHtml(crumbItems);

  const focusHtml = focus.map((item) => `<li>${escapeHtml(item)}</li>`).join("");
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "BreadcrumbList",
        itemListElement: [
          { "@type": "ListItem", position: 1, name: "Home", item: "https://icomplypropertyservices.co.uk/" },
          { "@type": "ListItem", position: 2, name: serviceName, item: `https://icomplypropertyservices.co.uk/pages/services/${serviceSlug}` },
          { "@type": "ListItem", position: 3, name: `${subject} in ${place.name}`, item: canonical },
        ],
      },
      {
        "@type": "Service",
        name: `${subject} in ${place.name}`,
        serviceType: serviceName,
        areaServed: place.name,
        description,
        provider: {
          "@type": "LocalBusiness",
          "@id": "https://icomplypropertyservices.co.uk/#business",
          name: "iComply Property Services",
          url: "https://icomplypropertyservices.co.uk/",
          telephone: "+447517806082",
          email: "icomplypropertyservices@gmail.com",
          address: {
            "@type": "PostalAddress",
            streetAddress: "17 Woodlands Park Road, Offerton",
            addressLocality: "Stockport",
            postalCode: "SK2 5DE",
            addressCountry: "GB",
          },
          areaServed: "Stockport and the UK where the service is published",
        },
      },
      {
        "@type": "FAQPage",
        mainEntity: faqs.map(([q, a]) => ({
          "@type": "Question",
          name: q,
          acceptedAnswer: { "@type": "Answer", text: a },
        })),
      },
    ],
  };

  const html = `<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${escapeHtml(title)}</title>
<meta name="description" content="${escapeHtml(description)}">
<meta name="robots" content="${robots}">
<link rel="canonical" href="${canonical}">
${thinOgMeta({ title, description, canonical, image: images.ogImage })}
${MATRIX_CSS}
<script type="application/ld+json">${jsonLd(schema)}</script>
</head>
<body class="mx">
${MATRIX_HEADER}
<main>
${crumbs}
<h1>${escapeHtml(h1)}</h1>
${images.html}
<article id="local-copy">
${proseHtml}
${focusHtml ? `<ul>${focusHtml}</ul>` : ""}
${fireTown ? `<p>Parent: <a href="/pages/keywords/${escapeHtml(input.keyword)}">${escapeHtml(subject)} guide</a> · <a href="/pages/services/fire-alarms">Fire alarms</a> · <a href="/pages/keywords">Service guides</a>${isGmTown(townSlug) ? ` · <a href="/pages/areas/${escapeHtml(townSlug)}">Property services in ${escapeHtml(place.name)}</a>` : ` · <a href="/pages/areas/stockport">Stockport area hub</a>`}</p><h2>Nearby mainland towns</h2><ul>${nearestMainland(townSlug, 12).map((near) => `<li><a href="/pages/keywords/${escapeHtml(input.keyword)}/${escapeHtml(near.slug)}">${escapeHtml(subject)} in ${escapeHtml(near.name)}</a></li>`).join("")}</ul>` : ""}
${relatedHtml}
${faqBlock}
</article>
${MATRIX_FOOTER}
</main>
${MATRIX_SITE_FOOTER}
</body>
</html>`;

  return {
    html,
    title,
    description,
    h1,
    canonical,
    robots,
    indexable,
    path,
    status: 200,
    words: words(html.replace(/<[^>]+>/g, " ")),
  };
}

let cataloguePromise = null;
let variantPromise = null;
let closeQTownPagesPromise = null;

/**
 * pages map from website/data/close-q/town-prose.json.
 * The edge bundle reads the repo file when it is present, otherwise the
 * copy static-export publishes at /assets/close-q/town-prose.json
 * (/data/* is a public 404). Empty object keeps the generated floor.
 */
async function loadCloseQTownPages(origin) {
  if (!closeQTownPagesPromise) {
    closeQTownPagesPromise = readCloseQTownPages(origin);
  }
  return closeQTownPagesPromise;
}

async function readCloseQTownPages(origin) {
  if (typeof Deno !== "undefined" && typeof Deno.readTextFile === "function") {
    try {
      const fileUrl = new URL("../../website/data/close-q/town-prose.json", import.meta.url);
      const data = JSON.parse(await Deno.readTextFile(fileUrl));
      if (data && data.pages && typeof data.pages === "object") return data.pages;
    } catch {
      // File is owned by the Page Enrichment checkout and may be absent here.
    }
  }
  try {
    const response = await fetch(new URL("/assets/close-q/town-prose.json", origin));
    if (response.ok) {
      const data = await response.json();
      if (data && data.pages && typeof data.pages === "object") return data.pages;
    }
  } catch {
    // Published copy missing. Generated floor still renders.
  }
  return {};
}

async function loadVariants(origin) {
  if (!variantPromise) {
    variantPromise = fetchJsonRetry(`${origin}/assets/matrix/variants.json`).catch((error) => {
      variantPromise = null;
      throw error;
    });
  }
  return variantPromise;
}

function variantResponse(rendered) {
  return new Response(withContactWidgets(scrubMeta(rendered.html)), {
    status: 200,
    headers: {
      "content-type": "text/html; charset=utf-8",
      "cache-control": "public, max-age=3600",
      "x-robots-tag": rendered.robots,
    },
  });
}

async function fetchJsonRetry(href, tries = 3) {
  let lastError;
  for (let i = 0; i < tries; i++) {
    try {
      const response = await fetch(href);
      if (response.ok) return await response.json();
      lastError = new Error(`${href} ${response.status}`);
    } catch (error) {
      lastError = error;
    }
  }
  throw lastError;
}

async function loadCatalogue(origin) {
  if (!cataloguePromise) {
    cataloguePromise = (async () => {
      const names = ["keywords", "places", "services", "nationwide", "manufacturers", "jobs"];
      const files = await Promise.all(names.map((name) => (
        fetchJsonRetry(`${origin}/assets/matrix/${name}.json`)
      )));
      return {
        keywords: files[0],
        places: files[1],
        services: files[2],
        nationwide: files[3],
        manufacturers: files[4],
        jobs: files[5],
      };
    })().catch((error) => {
      cataloguePromise = null;
      throw error;
    });
  }
  return cataloguePromise;
}


const PLACE_ALIAS_BARRIERS = {"trafford":"hale-trafford","tameside":"hyde-tameside","shaw":"shaw-oldham","milnrow":"rochdale","pendlebury":"swinton-salford","cadishead":"irlam","chorlton":"manchester","withington":"manchester","crosby":"crosby-sefton","ince":"ince-in-makerfield","thornton-cleveleys":"cleveleys","ainsdale":"southport","ambleside":"kendal","appleton":"warrington","birchwood":"warrington","burscough":"ormskirk","carnforth":"lancaster","clayton-le-moors":"accrington","cockermouth":"workington","culcheth":"warrington","dalton-in-furness":"barrow-in-furness","frodsham":"runcorn","garstang":"preston-preston","halton":"runcorn","handforth":"wilmslow","helsby":"runcorn","holmes-chapel":"crewe","hoylake":"wallasey","keswick":"workington","kirkham":"blackpool","longridge":"preston-preston","maryport":"workington","millom":"barrow-in-furness","penketh":"warrington","rainford":"st-helens-st-helens","risley":"warrington","rossendale":"rawtenstall","shevington":"wigan","stockton-heath":"warrington","tarporley":"chester","tarvin":"chester","wesham":"blackpool","whalley":"blackburn-blackburn-with-darwen","windermere":"kendal"};
const PLACE_ALIAS_AOV = {"worsley":"walkden","wythenshawe":"manchester","pendlebury":"salford","cadishead":"eccles","trafford":"hale-trafford","chorlton":"manchester","withington":"manchester","tameside":"hyde-tameside","shaw":"shaw-oldham"};

function placeAliasRedirect(path) {
  let m = path.match(/^\/pages\/(barriers|aov)\/([a-z0-9-]+)$/);
  if (!m) return null;
  const family = m[1];
  const slug = m[2];
  // #106 publishes real Greater Manchester AOV and barrier pages. Leave those on 200.
  if (isGmTown(slug)) return null;
  const map = family === "barriers" ? PLACE_ALIAS_BARRIERS : PLACE_ALIAS_AOV;
  const dest = map[slug];
  if (!dest || dest === slug) return null;
  return `/pages/${family}/${dest}`;
}

export function nonGmMatrixRedirect(path) {
  if (GSC_KEEP.has(path)) return null;
  let m = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) {
    if (isLocalTown(m[2]) || p0KeepsTown(m[1], m[2]) || fireInstallerNationwide(m[1], m[2])) return null;
    return `/pages/keywords/${m[1]}`;
  }
  m = path.match(/^\/pages\/manufacturers\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) return isGmTown(m[2]) ? null : `/pages/manufacturers/${m[1]}`;
  m = path.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) return isLocalTown(m[2]) ? null : `/pages/jobs/${m[1]}`;
  m = path.match(/^\/pages\/areas\/([a-z0-9-]+)$/);
  if (m) return isLocalTown(m[1]) ? null : "/pages/areas";
  m = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (!m || RESERVED.has(m[1]) || NATIONWIDE_TOWN.has(m[1]) || isLocalTown(m[2])) return null;
  return `/pages/services/${m[1]}`;
}

export default async (request, context) => {
  const url = new URL(request.url);
  const path = url.pathname.replace(/\/+$/, "") || "/";
  // One canonical form: strip trailing slashes (GSC 404s on /pages/areas/x/,
  // duplicates on /pages/keywords/x/). Exported files never use the slash form.
  if (path !== "/" && url.pathname !== path && !path.startsWith("/matrix-sitemap")) {
    return Response.redirect(new URL(path + url.search, url.origin).toString(), 301);
  }
  const aliasTo = placeAliasRedirect(path);
  if (aliasTo) {
    return Response.redirect(new URL(aliasTo, url.origin).toString(), 301);
  }
  const securityTo = securityJobRedirect(path);
  if (securityTo) {
    return Response.redirect(new URL(securityTo, url.origin).toString(), 301);
  }
  const matrixTo = nonGmMatrixRedirect(path);
  if (matrixTo) {
    return Response.redirect(new URL(matrixTo, url.origin).toString(), 301);
  }
  // AOV, barriers and fire-family town HTML is exported. Do not replace it
  // with the Greater Manchester matrix renderer.
  const nationwidePage = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (nationwidePage && NATIONWIDE_TOWN.has(nationwidePage[1])) {
    const staticResponse = await context.next();
    // Dual-ring towns listed in the sitemap but missing from the static
    // export fall back to the matrix renderer instead of 404ing.
    if (staticResponse.status !== 404) {
      return staticResponse;
    }
    // Town outside the export (old 50-mile ring / GM purge URLs): 301 to the
    // live service hub instead of a 404.
    if (!isLocalTown(nationwidePage[2])) {
      const hub = nationwidePage[1] === "aov" ? "/pages/aov" : `/pages/services/${nationwidePage[1]}`;
      return Response.redirect(new URL(hub, url.origin).toString(), 301);
    }
  }
  const sitemapMatch = path.match(/^\/matrix-sitemap\/(\d+)(?:\.xml)?$/);
  if (sitemapMatch) {
    try {
      const spec = await loadVariants(url.origin);
      const xml = renderVariantSitemap(spec, Number(sitemapMatch[1]));
      if (!xml) {
        return new Response("Not found", { status: 404, headers: { "content-type": "text/plain; charset=utf-8" } });
      }
      return new Response(xml, {
        status: 200,
        headers: {
          "content-type": "application/xml; charset=utf-8",
          "cache-control": "public, max-age=86400",
        },
      });
    } catch {
      return new Response("Variant sitemap unavailable", {
        status: 503,
        headers: { "content-type": "text/plain; charset=utf-8" },
      });
    }
  }
  const variantTown = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const variantHub = path.match(/^\/pages\/keywords\/([a-z0-9-]+)$/);
  const variantSlug = variantTown?.[1] || variantHub?.[1] || "";
  if (variantSlug.includes("--")) {
    try {
      const spec = await loadVariants(url.origin);
      const rendered = renderVariantPage({
        spec,
        keyword: variantSlug,
        town: variantTown ? variantTown[2] : "",
      });
      if (rendered.status === 301 && rendered.location) {
        return Response.redirect(new URL(rendered.location, url.origin).toString(), 301);
      }
      return variantResponse(rendered);
    } catch {
      return new Response("Variant page unavailable", {
        status: 503,
        headers: { "content-type": "text/plain; charset=utf-8" },
      });
    }
  }
  const manufacturerMatch = path.match(/^\/pages\/manufacturers\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const jobMatch = path.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const keywordMatch = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const serviceMatch = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (!manufacturerMatch && !jobMatch && !keywordMatch && !serviceMatch) {
    return context.next();
  }
  let catalogue;
  let closeQPages = {};
  try {
    catalogue = await loadCatalogue(url.origin);
  } catch {
    // Transient asset fetch failure: a retryable 503, never a false 404.
    return new Response("Temporarily unavailable", {
      status: 503,
      headers: { "content-type": "text/plain; charset=utf-8", "retry-after": "120" },
    });
  }
  try {
    closeQPages = await loadCloseQTownPages(url.origin);
  } catch {
    closeQPages = {};
  }
  let rendered = null;
  if (manufacturerMatch) {
    rendered = renderTownPage({
      kind: "manufacturer",
      brand: manufacturerMatch[1],
      town: manufacturerMatch[2],
      catalogue,
      closeQPages,
    });
  } else if (jobMatch) {
    rendered = renderTownPage({
      kind: "job",
      job: jobMatch[1],
      town: jobMatch[2],
      catalogue,
      closeQPages,
    });
  } else if (keywordMatch) {
    if (p0KeepsTown(keywordMatch[1], keywordMatch[2])) {
      rendered = renderNationwideP0Town(keywordMatch[1], keywordMatch[2]);
    }
    if (!rendered && !(catalogue.keywords || {})[keywordMatch[1]]) {
      // Unknown keyword (retired slug): 410 Gone, like a dead hub,
      // instead of a 200 noindex soft 404.
      const hub = await fetch(new URL(`/pages/keywords/${keywordMatch[1]}`, url.origin), { redirect: "manual" });
      if ((hub.status === 301 || hub.status === 308) && hub.headers.get("location")) {
        return Response.redirect(new URL(hub.headers.get("location"), url.origin).toString(), 301);
      }
      if (hub.status === 404) {
        const page = await fetch(new URL("/404.html", url.origin)).then((r) => (r.ok ? r.text() : "Not found")).catch(() => "Not found");
        return new Response(page, { status: 410, headers: { "content-type": "text/html; charset=utf-8", "x-robots-tag": "noindex" } });
      }
    }
    if (!rendered) {
      rendered = renderTownPage({
        kind: "keyword",
        keyword: keywordMatch[1],
        town: keywordMatch[2],
        catalogue,
        closeQPages,
      });
    }
  } else {
    rendered = renderTownPage({
      kind: "service",
      service: serviceMatch[1],
      town: serviceMatch[2],
      catalogue,
      closeQPages,
    });
  }
  if (!rendered) {
    const fallback = await context.next();
    if (fallback.status !== 404) return fallback;
    // Unknown brand/job/service in a town path (old slugs, /pages/undefined/x):
    // 301 to the closest live hub rather than a 404.
    let to = "/pages/areas";
    const svcHub = path.match(/^\/pages\/services\/([a-z0-9-]+)$/);
    if (svcHub) {
      // /pages/services/<slug> with no exported hub: send to the live service page, never to /pages/areas.
      to = SERVICE_HUB_ALIAS[svcHub[1]] || "/pages/services";
    } else if (manufacturerMatch) to = "/pages/manufacturers";
    else if (jobMatch) to = "/pages/jobs";
    else if (keywordMatch) to = "/pages/keywords";
    else if (serviceMatch && isLocalTown(serviceMatch[2])) to = `/pages/areas/${serviceMatch[2]}`;
    return Response.redirect(new URL(to, url.origin).toString(), 301);
  }
  return new Response(applyMoneyCtr(path, withContactWidgets(scrubMeta(rendered.html))), {
    status: 200,
    headers: {
      "content-type": "text/html; charset=utf-8",
      "cache-control": "public, max-age=3600",
      "x-robots-tag": rendered.robots,
    },
  });
};

export const config = {
  path: [
    "/pages/keywords/:keyword",
    "/pages/keywords/:keyword/",
    "/pages/keywords/:keyword/:town",
    "/pages/keywords/:keyword/:town/",
    "/pages/manufacturers/:brand/:town",
    "/pages/manufacturers/:brand/:town/",
    "/pages/jobs/:job/:town",
    "/pages/jobs/:job/:town/",
    "/pages/:service/:town",
    "/pages/:service/:town/",
    "/pages/:section/",
    "/pages/",
    "/matrix-sitemap/:part",
  ],
  // Static AOV/barrier town pages (~420 KB) 502'd intermittently when
  // proxied through this function. Their aliases live in netlify.toml.
  excludedPath: [
    "/pages/aov/*",
    "/pages/barriers/*",
    // Wave-1 package 301s live in _redirects; the edge fallback would
    // otherwise 301 these unknown "service/town" paths to /pages/areas.
    "/pages/packages/landlord-essentials",
    "/pages/packages/landlord-essentials/",
    "/pages/packages/hmo-fire-package",
    "/pages/packages/hmo-fire-package/",
  ],
};
