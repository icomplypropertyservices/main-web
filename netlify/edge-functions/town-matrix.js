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
import { thinFaqHtml, thinFaqs, thinImages, thinOgMeta, thinProseHtml } from "../lib/thin-quality-bar.js";
import fireAlarmFamily from "../../website/data/fire-alarm-installer-family.json" with { type: "json" };
import mainlandTownDoc from "../../website/data/uk-mainland-towns-10k.json" with { type: "json" };

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

const FIRE_DUTY = "Design, installation and commissioning follow BS 5839. Competent fire alarm engineers carry out the visit. iComply does not claim BAFE or NSI badges.";
const FIRE_NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE";

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
      intro: `${subject} equipment and visits are arranged from Stockport for sites that already use this manufacturer.`,
      focus: [
        `${subject} parts identified before the visit`,
        "Quote is price on application",
        "Paperwork left with the instructing client",
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
      intro: `${subject} is a job type under ${serviceName}, scoped per building rather than sold as a package price.`,
      focus: job.focus && job.focus.length ? job.focus : [
        "Scope agreed before anyone attends",
        "Quote is price on application",
        "Result left with the instructing client",
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
          "Scope agreed before the visit",
          "Quote is price on application",
          "Paperwork left with the instructing client",
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
          "Quote is price on application",
          "Paperwork handed to the instructing client",
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
  const indexable = keywordKnown && !keyword.synthetic && (tier === "core" || fireTown);
  const robots = indexable ? "index, follow" : "noindex, follow";
  const seed = hashStr(`${kind}|${subject}|${serviceSlug}|${townSlug}`);
  const audience = pick(AUDIENCES, seed);
  const visit = pick(VISIT_STYLES, seed >>> 3);
  const neighbours = Array.isArray(place.neighbours) ? place.neighbours.filter(Boolean) : [];
  const near = neighbours.length ? neighbours.join(", ") : "Stockport, Manchester and the surrounding North West";
  const pop = place.population
    ? `${place.name} has a published usual-resident population of about ${Number(place.population).toLocaleString("en-GB")}.`
    : `${place.name} is quoted from the address, not from an invented population figure.`;
  const housing = place.housing
    ? `Census housing in ${place.name} includes ${place.housing}.`
    : `Buildings in ${place.name} include rented homes, shops, offices and light-industrial units.`;
  const industry = place.industry
    ? `Local employment notes for ${place.name} include ${place.industry}, so visits are planned around occupied buildings.`
    : `Sites in ${place.name} are a mix of housing and commercial units. The scope follows the building, not a stock paragraph.`;
  const focus = (keyword.focus && keyword.focus.length ? keyword.focus : [`Scope agreed before ${subject} starts`]).slice(0, 4);
  const canonical = `https://icomplypropertyservices.co.uk${path}`;
  const h1 = `${subject} for ${audience} in ${place.name}`;
  const title = `${subject} in ${place.name} | ${serviceName} | iComply`;
  const description = clampMeta(
    `${subject} in ${place.name}, ${place.region}. ${housing} Quote is price on application (POA). Cover also reaches ${near}.`
  );
  const imageSlug = IMAGE_SLUG[serviceSlug] || serviceSlug;

  const gas = keyword.gas || serviceSlug === "gas-systems" || serviceSlug === "heating";
  const packed = (kind === "keyword" || kind === "service")
    ? gmTownBlurb(kind, kind === "keyword" ? input.keyword : serviceSlug, townSlug)
    : null;
  let prose = packed && packed.blurb ? stripBriefTokens(packed.blurb) : "";
  if (gas && prose && !/Gas Safe registered engineers/.test(prose)) {
    prose = `${prose} ${GAS_SENTENCE}`;
  }
  const qualityCtx = {
    kind,
    seed,
    place: place.name,
    subject,
    service: serviceName,
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
  if (pe && typeof pe.body === "string" && pe.body.trim()) {
    qualityCtx.peBody = pe.body;
  }
  if (pe && Array.isArray(pe.faqs)) {
    qualityCtx.peFaqs = pe.faqs;
  }
  // Property SEO pack services ship three on-topic images (catalogue services.pack_images).
  const packImageList = services.pack_images && Array.isArray(services.pack_images[imageSlug])
    ? services.pack_images[imageSlug]
    : null;
  const packImages = packImageList && packImageList.length >= 3
    ? [
      { src: packImageList[0], alt: `${subject} in ${place.name}` },
      { src: packImageList[1], alt: `${subject} work planned for ${place.name}` },
      { src: packImageList[2], alt: `${subject} project context near ${place.name}` },
    ]
    : null;
  const images = thinImages(imageSlug, subject, place.name, seed, pe && Array.isArray(pe.images) ? pe.images : packImages);
  let proseHtml = thinProseHtml(qualityCtx);
  const faqs = thinFaqs(qualityCtx).slice();
  if (fireTown || fireFamily) {
    const county = place.county ? `${place.county}, ` : "";
    const population = place.population
      ? `Published population is about ${Number(place.population).toLocaleString("en-GB")}. `
      : "";
    proseHtml += `<p data-pe-slot="fire-installer-nap">${escapeHtml(`${subject} in ${place.name} is arranged from ${FIRE_NAP}. ${place.name} is in ${county}${place.region}, ${place.country}. ${population}${FIRE_DUTY} The quote is price on application.`)}</p>`;
  }
  if (fireTown) {
    faqs.push([
      `Do you cover ${place.name} for ${subject}?`,
      `${subject} in ${place.name} is arranged from ${FIRE_NAP}. ${FIRE_DUTY} The quote is price on application.`,
    ]);
  }
  const faqBlock = (fireTown || fireFamily)
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
          "@type": "Organization",
          name: "iComply Property Services",
          url: "https://icomplypropertyservices.co.uk/",
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
<link rel="stylesheet" href="/assets/css/site.css">
<script type="application/ld+json">${jsonLd(schema)}</script>
</head>
<body>
<header>
<p><a href="/">iComply Property Services</a> · <a href="/pages/services">Services</a> · <a href="/pages/jobs">Jobs</a> · <a href="/pages/keywords">Keywords</a> · <a href="/pages/areas">Areas</a> · <a href="/pages/manufacturers">Manufacturers</a> · <a href="/directories">Directories</a> · <a href="/contact">Contact</a></p>
</header>
<main>
${crumbs}
<h1>${escapeHtml(h1)}</h1>
${images.html}
<article id="local-copy">
${proseHtml}
${focusHtml ? `<ul>${focusHtml}</ul>` : ""}
${fireTown ? `<p>Parent: <a href="/pages/keywords/${escapeHtml(input.keyword)}">${escapeHtml(subject)} guide</a> · <a href="/pages/services/fire-alarms">Fire alarms</a> · <a href="/pages/keywords">Keyword guides</a>${isGmTown(townSlug) ? ` · <a href="/pages/areas/${escapeHtml(townSlug)}">Property services in ${escapeHtml(place.name)}</a>` : ` · <a href="/pages/areas/stockport">Stockport area hub</a>`}</p><h2>Nearby mainland towns</h2><ul>${nearestMainland(townSlug, 12).map((near) => `<li><a href="/pages/keywords/${escapeHtml(input.keyword)}/${escapeHtml(near.slug)}">${escapeHtml(subject)} in ${escapeHtml(near.name)}</a></li>`).join("")}</ul>` : ""}
${relatedHtml}
${faqBlock}
</article>
</main>
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
    variantPromise = fetch(`${origin}/assets/matrix/variants.json`).then((response) => {
      if (!response.ok) throw new Error("variants");
      return response.json();
    }).catch((error) => {
      variantPromise = null;
      throw error;
    });
  }
  return variantPromise;
}

function variantResponse(rendered) {
  return new Response(rendered.html, {
    status: 200,
    headers: {
      "content-type": "text/html; charset=utf-8",
      "cache-control": "public, max-age=3600",
      "x-robots-tag": rendered.robots,
    },
  });
}

async function loadCatalogue(origin) {
  if (!cataloguePromise) {
    cataloguePromise = (async () => {
      const names = ["keywords", "places", "services", "nationwide", "manufacturers", "jobs"];
      const files = await Promise.all(names.map((name) => (
        fetch(`${origin}/assets/matrix/${name}.json`).then((response) => response.json())
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
  const aliasTo = placeAliasRedirect(path);
  if (aliasTo) {
    return Response.redirect(new URL(aliasTo, url.origin).toString(), 301);
  }
  const matrixTo = nonGmMatrixRedirect(path);
  if (matrixTo) {
    return Response.redirect(new URL(matrixTo, url.origin).toString(), 301);
  }
  // AOV, barriers and fire-family town HTML is exported. Do not replace it
  // with the Greater Manchester matrix renderer.
  const nationwidePage = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (nationwidePage && NATIONWIDE_TOWN.has(nationwidePage[1])) {
    return context.next();
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
    return context.next();
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
  if (!rendered) return context.next();
  return new Response(rendered.html, {
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
    "/matrix-sitemap/:part",
  ],
};
