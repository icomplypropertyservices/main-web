/**
 * Visible related-link blocks for thin matrix pages.
 *
 * Live town × service / keyword / job / manufacturer HTML is rendered by the
 * Netlify edge function (netlify/edge-functions/town-matrix.js), not by the
 * mega-nav PHP chrome. These blocks are in-body <a href> lists.
 *
 * A  Parent hubs, including /pages/areas/{town}
 * B  Related services (or sibling keywords / jobs / brands) in the same town
 * C  Nearby Greater Manchester towns for the same leaf (borough adjacency)
 * D  Jobs and keyword guides
 * E  Manufacturer hubs for the trade
 * F  Quote, packages, commercial, resources, directories
 *
 * A "Same work across Greater Manchester" list covers the other GM towns for
 * the same leaf so a thin page clears 80 unique internal targets without
 * repeating an anchor. Only catalogue slugs are linked. Barriers and AOV town
 * files are not guessed here (some GM localities 404 on those templates).
 */

const GM_TOWNS = [
  ["altrincham", "Altrincham"],
  ["ashton-under-lyne", "Ashton-under-Lyne"],
  ["atherton", "Atherton"],
  ["bolton", "Bolton"],
  ["bramhall", "Bramhall"],
  ["bury", "Bury"],
  ["cadishead", "Cadishead"],
  ["chadderton", "Chadderton"],
  ["cheadle", "Cheadle"],
  ["cheadle-hulme", "Cheadle Hulme"],
  ["chorlton", "Chorlton"],
  ["denton", "Denton"],
  ["didsbury", "Didsbury"],
  ["droylsden", "Droylsden"],
  ["dukinfield", "Dukinfield"],
  ["eccles", "Eccles"],
  ["failsworth", "Failsworth"],
  ["farnworth", "Farnworth"],
  ["hazel-grove", "Hazel Grove"],
  ["heywood", "Heywood"],
  ["horwich", "Horwich"],
  ["hyde", "Hyde"],
  ["irlam", "Irlam"],
  ["kearsley", "Kearsley"],
  ["lees", "Lees"],
  ["leigh", "Leigh"],
  ["little-lever", "Little Lever"],
  ["littleborough", "Littleborough"],
  ["manchester", "Manchester"],
  ["marple", "Marple"],
  ["middleton", "Middleton"],
  ["milnrow", "Milnrow"],
  ["mossley", "Mossley"],
  ["oldham", "Oldham"],
  ["pendlebury", "Pendlebury"],
  ["prestwich", "Prestwich"],
  ["radcliffe", "Radcliffe"],
  ["rochdale", "Rochdale"],
  ["romiley", "Romiley"],
  ["royton", "Royton"],
  ["saddleworth", "Saddleworth"],
  ["sale", "Sale"],
  ["salford", "Salford"],
  ["shaw", "Shaw"],
  ["stalybridge", "Stalybridge"],
  ["stockport", "Stockport"],
  ["stretford", "Stretford"],
  ["swinton", "Swinton"],
  ["tameside", "Tameside"],
  ["trafford", "Trafford"],
  ["tyldesley", "Tyldesley"],
  ["uppermill", "Uppermill"],
  ["urmston", "Urmston"],
  ["walkden", "Walkden"],
  ["westhoughton", "Westhoughton"],
  ["whitefield", "Whitefield"],
  ["wigan", "Wigan"],
  ["withington", "Withington"],
  ["worsley", "Worsley"],
  ["wythenshawe", "Wythenshawe"],
];

const GM_NAME = Object.fromEntries(GM_TOWNS);
const GM_SLUGS = GM_TOWNS.map(([slug]) => slug);

/** Borough groups. A town can sit in more than one group (Leigh, Heywood). */
const GM_GROUPS = [
  ["bolton", "leigh", "atherton", "tyldesley", "horwich", "westhoughton", "farnworth", "kearsley", "little-lever"],
  ["bury", "radcliffe", "whitefield", "prestwich", "heywood"],
  ["manchester", "chorlton", "didsbury", "withington", "wythenshawe", "failsworth"],
  ["oldham", "chadderton", "shaw", "royton", "lees", "uppermill", "saddleworth", "middleton"],
  ["rochdale", "heywood", "milnrow", "littleborough", "middleton"],
  ["salford", "swinton", "eccles", "walkden", "worsley", "pendlebury", "irlam", "cadishead"],
  ["stockport", "cheadle", "cheadle-hulme", "bramhall", "hazel-grove", "marple", "romiley"],
  ["tameside", "hyde", "stalybridge", "dukinfield", "ashton-under-lyne", "mossley", "droylsden", "denton"],
  ["trafford", "altrincham", "sale", "stretford", "urmston", "chorlton"],
  ["wigan", "leigh", "atherton", "tyldesley"],
];

const ANCHORS = ["manchester", "stockport", "salford", "bolton", "oldham", "rochdale", "bury", "tameside", "trafford", "wigan"];

const CLUSTERS = [
  ["access-control", "door-entry", "cctv", "intercoms", "intruder-alarm"],
  ["electrical", "electrics-first-fix", "emergency-lighting", "fire-alarms", "pat-testing", "eicr"],
  ["fire-alarms", "fire-risk-assessments", "dry-risers", "emergency-lighting", "fire-doors", "fire-extinguishers", "fire-stopping", "fire-signage"],
  ["gas-systems", "electrical", "fire-risk-assessments", "plumbing", "heating"],
  ["building-maintenance", "brickwork", "joinery", "plastering", "building-surveys", "damp-proofing", "roofing"],
  ["aov-air-handling", "fire-alarms", "access-control"],
  ["barriers", "access-control", "cctv", "door-entry"],
  ["cctv", "intruder-alarm", "access-control", "door-entry"],
  ["plumbing", "heating", "bathrooms", "gas-systems"],
  ["landlord-compliance", "electrical", "gas-systems", "fire-alarms", "epc"],
];

const HUB_SKIP = new Set([
  "air-source-heat-pumps-service-agreement",
  "loft-conversion-fixed-price-package",
  "bs-5306-extinguisher-service-cost",
]);

const RESERVED = new Set([
  "keywords", "services", "areas", "manufacturers", "resources", "packages",
  "jobs", "commercial", "aov", "barriers", "aov-air-handling", "products",
  "shop", "emergency-lighting-jobs", "asbestos-jobs",
]);

export function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

export function isGmTown(slug) {
  return Object.prototype.hasOwnProperty.call(GM_NAME, slug);
}

export function gmTownName(slug) {
  return GM_NAME[slug] || "";
}

export function adjacentGmSlugs(townSlug, limit = 12) {
  const seen = new Set();
  const out = [];
  const push = (slug) => {
    if (!slug || slug === townSlug || seen.has(slug) || !GM_NAME[slug]) return;
    seen.add(slug);
    out.push(slug);
  };
  for (const group of GM_GROUPS) {
    if (!group.includes(townSlug)) continue;
    for (const slug of group) push(slug);
  }
  for (const slug of ANCHORS) push(slug);
  for (const slug of GM_SLUGS) push(slug);
  return out.slice(0, limit);
}

function listHtml(title, items) {
  if (!items.length) return "";
  const lis = items.map((item) => `<li><a href="${escapeHtml(item.href)}">${escapeHtml(item.label)}</a></li>`).join("");
  return `<h3>${escapeHtml(title)}</h3><ul>${lis}</ul>`;
}

export function renderRelatedLinks({ heading, blocks }) {
  const parts = blocks.map(([title, items]) => listHtml(title, items)).filter(Boolean);
  if (!parts.length) return "";
  return `<section class="related-links" aria-label="Related services and areas"><h2>${escapeHtml(heading)}</h2>${parts.join("")}</section>`;
}

function dedupe(items, selfPath) {
  const seen = new Set();
  const out = [];
  for (const item of items) {
    if (!item || !item.href || !item.label) continue;
    if (item.href === selfPath) continue;
    if (seen.has(item.href)) continue;
    seen.add(item.href);
    out.push(item);
  }
  return out;
}

function serviceSlugs(catalogue) {
  const services = catalogue.services || {};
  const labels = services.labels || {};
  const excluded = new Set(services.excluded || ["barriers", "aov-air-handling"]);
  return Object.keys(labels).filter((slug) => labels[slug] && !excluded.has(slug) && !RESERVED.has(slug));
}

function serviceLabel(catalogue, slug) {
  const labels = (catalogue.services && catalogue.services.labels) || {};
  return labels[slug] || "";
}

function relatedServiceSlugs(catalogue, serviceSlug, limit = 8) {
  const allowed = new Set(serviceSlugs(catalogue));
  const picked = [];
  const push = (slug) => {
    if (!slug || slug === serviceSlug || !allowed.has(slug) || picked.includes(slug)) return;
    picked.push(slug);
  };
  for (const cluster of CLUSTERS) {
    if (!cluster.includes(serviceSlug)) continue;
    for (const slug of cluster) push(slug);
  }
  for (const slug of serviceSlugs(catalogue).sort()) push(slug);
  return picked.slice(0, limit);
}

function leafHref(kind, ids, townSlug) {
  if (kind === "keyword") return `/pages/keywords/${ids.keyword}/${townSlug}`;
  if (kind === "job") return `/pages/jobs/${ids.job}/${townSlug}`;
  if (kind === "manufacturer") return `/pages/manufacturers/${ids.brand}/${townSlug}`;
  if (kind === "variant") return `/pages/keywords/${ids.keyword}/${townSlug}`;
  return `/pages/${ids.service}/${townSlug}`;
}

/**
 * @param {object} catalogue Edge catalogue (keywords, services, jobs, manufacturers).
 * @param {object} identity Page identity. kind is service|keyword|job|manufacturer|variant.
 */
export function matrixRelatedHtml(catalogue, identity) {
  const kind = identity.kind || "service";
  const townSlug = identity.townSlug || "";
  const townName = identity.townName || gmTownName(townSlug) || "Greater Manchester";
  const subject = identity.subject || "This work";
  const serviceSlug = identity.serviceSlug || "";
  const serviceName = identity.serviceName || serviceLabel(catalogue, serviceSlug) || "Property services";
  const selfPath = identity.selfPath || "";
  const keywords = catalogue.keywords || {};
  const jobs = catalogue.jobs || {};
  const manufacturers = catalogue.manufacturers || {};
  const gmTowns = Array.isArray(identity.towns) && identity.towns.length
    ? identity.towns.filter((row) => row && row.slug && GM_NAME[row.slug])
    : GM_TOWNS.map(([slug, name]) => ({ slug, name }));

  const parents = [];
  if (kind === "keyword" || kind === "variant") {
    parents.push({ href: `/pages/keywords/${identity.keyword}`, label: `${subject} guide` });
    parents.push({ href: "/pages/keywords", label: "All keyword guides" });
  } else if (kind === "job") {
    parents.push({ href: `/pages/jobs/${identity.job}`, label: `${subject}` });
    parents.push({ href: "/pages/jobs", label: "All job types" });
  } else if (kind === "manufacturer") {
    parents.push({ href: `/pages/manufacturers/${identity.brand}`, label: subject });
    parents.push({ href: "/pages/manufacturers", label: "All manufacturers" });
  } else {
    parents.push({ href: `/pages/services/${serviceSlug}`, label: serviceName });
    parents.push({ href: "/pages/services", label: "All services" });
  }
  if (serviceSlug && kind !== "service") {
    const label = serviceLabel(catalogue, serviceSlug);
    if (label) parents.push({ href: `/pages/services/${serviceSlug}`, label: label });
  }
  if (townSlug && GM_NAME[townSlug]) {
    parents.push({ href: `/pages/areas/${townSlug}`, label: `Property services in ${townName}` });
  }
  parents.push({ href: "/pages/areas", label: "Greater Manchester areas" });

  const sameTown = [];
  if (kind === "service" || kind === "keyword" || kind === "job" || kind === "variant") {
    for (const slug of relatedServiceSlugs(catalogue, serviceSlug, 8)) {
      const name = serviceLabel(catalogue, slug);
      if (!name || !townSlug) continue;
      sameTown.push({ href: `/pages/${slug}/${townSlug}`, label: `${name} in ${townName}` });
    }
  }
  if (kind === "keyword" || kind === "variant") {
    const siblings = ((catalogue.services && catalogue.services.keywords) || {})[serviceSlug] || [];
    for (const slug of siblings) {
      if (!keywords[slug] || slug === identity.keyword) continue;
      if (!townSlug) continue;
      sameTown.push({
        href: `/pages/keywords/${slug}/${townSlug}`,
        label: `${keywords[slug].name} in ${townName}`,
      });
    }
  }
  if (kind === "job") {
    for (const [slug, job] of Object.entries(jobs)) {
      if (slug === identity.job || !job || job.service !== serviceSlug || !townSlug) continue;
      sameTown.push({ href: `/pages/jobs/${slug}/${townSlug}`, label: `${job.name} in ${townName}` });
      if (sameTown.length >= 8) break;
    }
  }
  if (kind === "manufacturer") {
    for (const [slug, brand] of Object.entries(manufacturers)) {
      if (slug === identity.brand || !brand || brand.service !== serviceSlug || !townSlug) continue;
      sameTown.push({ href: `/pages/manufacturers/${slug}/${townSlug}`, label: `${brand.name} in ${townName}` });
      if (sameTown.length >= 6) break;
    }
  }

  const nearby = [];
  const coverage = [];
  const nearSet = new Set(adjacentGmSlugs(townSlug, 12));
  for (const town of gmTowns) {
    if (!town.slug || town.slug === townSlug) continue;
    const item = {
      href: leafHref(kind === "variant" ? "keyword" : kind, identity, town.slug),
      label: `${subject} in ${town.name}`,
    };
    if (nearSet.has(town.slug) && nearby.length < 12) nearby.push(item);
    else coverage.push(item);
  }

  const cross = [];
  const serviceKeywords = ((catalogue.services && catalogue.services.keywords) || {})[serviceSlug] || [];
  for (const slug of serviceKeywords) {
    const row = keywords[slug];
    if (!row) continue;
    if (!HUB_SKIP.has(slug)) {
      cross.push({ href: `/pages/keywords/${slug}`, label: row.name });
    }
    if (townSlug && kind !== "keyword" && kind !== "variant") {
      cross.push({ href: `/pages/keywords/${slug}/${townSlug}`, label: `${row.name} in ${townName}` });
    }
  }
  const jobEntries = Object.entries(jobs).filter(([, job]) => job && job.service === serviceSlug);
  const jobFallback = Object.entries(jobs).slice(0, 4);
  const jobList = (jobEntries.length ? jobEntries : jobFallback).slice(0, 4);
  for (const [slug, job] of jobList) {
    if (kind === "job" && slug === identity.job) continue;
    cross.push({ href: `/pages/jobs/${slug}`, label: job.name });
    if (townSlug) cross.push({ href: `/pages/jobs/${slug}/${townSlug}`, label: `${job.name} in ${townName}` });
  }
  if (kind !== "job") cross.push({ href: "/pages/jobs", label: "Job types" });

  const brands = [];
  for (const [slug, brand] of Object.entries(manufacturers)) {
    if (!brand || (serviceSlug && brand.service !== serviceSlug)) continue;
    brands.push({ href: `/pages/manufacturers/${slug}`, label: brand.name });
    if (brands.length >= 4) break;
  }
  brands.push({ href: "/pages/manufacturers", label: "Manufacturer hubs" });

  const utility = [
    { href: "/contact", label: "Request a quote" },
    { href: "/pages/commercial", label: "Commercial property services" },
    { href: "/pages/landlords", label: "Landlord compliance" },
    { href: "/pages/care-homes", label: "Care homes" },
    { href: "/pages/packages", label: "Compliance packages" },
    { href: "/pages/resources", label: "Guides and resources" },
    { href: "/pages/resources/landlord-compliance-checklist", label: "Landlord compliance checklist" },
    { href: "/directories", label: "Listings and directories" },
    { href: "/products", label: "Trade products" },
    { href: "/shop/", label: "Supplies hubs" },
    { href: "/pages/about", label: "About iComply" },
  ];

  const heading = townSlug
    ? `Related for ${subject} in ${townName}`
    : `Related for ${subject}`;
  return renderRelatedLinks({
    heading,
    blocks: [
      ["Hubs", dedupe(parents, selfPath)],
      [`Related in ${townName}`, dedupe(sameTown, selfPath)],
      ["Nearby areas", dedupe(nearby, selfPath)],
      ["Same work across Greater Manchester", dedupe(coverage, selfPath)],
      ["Jobs and guides", dedupe(cross, selfPath)],
      ["Manufacturers", dedupe(brands, selfPath)],
      ["Quotes and guides", dedupe(utility, selfPath)],
    ],
  });
}

export function breadcrumbHtml(items) {
  const parts = items.filter((item) => item && item.label).map((item, index) => {
    if (!item.href || index === items.length - 1) {
      return `<span>${escapeHtml(item.label)}</span>`;
    }
    return `<a href="${escapeHtml(item.href)}">${escapeHtml(item.label)}</a>`;
  });
  return `<nav aria-label="Breadcrumb">${parts.join(" / ")}</nav>`;
}
