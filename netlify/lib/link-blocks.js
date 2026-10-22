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
import townGeo from "../../website/data/town-geo-slim.json" with { type: "json" };
import townCoords from "../../website/data/town-coords-slim.json" with { type: "json" };
import areasMainland from "../../website/data/areas-mainland.json" with { type: "json" };
import { BOROUGH_GEO } from "./town-depth.js";

import { LOCAL_TOWN_SET } from "./dual-ring-allowlist.js";
import { spokenSubject } from "./spoken-subject.js";

function mainlandAreaSlug(name) {
  return String(name || "").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
}
const AOV_BUILT = new Set((Array.isArray(areasMainland) ? areasMainland : []).map((row) => mainlandAreaSlug(row && row.name)).filter(Boolean));

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

/** Dual-ring allowlist: area hubs, local services, keyword×town and job×town. */
export function isLocalTown(slug) {
  return LOCAL_TOWN_SET.has(slug);
}

export function gmTownName(slug) {
  return GM_NAME[slug] || "";
}

function nearestPlaceSlugs(townSlug, places, limit = 8) {
  const here = townCoords[townSlug];
  if (!Array.isArray(here) || !places) return [];
  return Object.keys(places)
    .filter((slug) => slug !== townSlug && places[slug] && places[slug].name && Array.isArray(townCoords[slug]))
    .map((slug) => ({
      slug,
      name: places[slug].name,
      miles: milesApart(
        { lat: here[0], lng: here[1] },
        { lat: townCoords[slug][0], lng: townCoords[slug][1] },
      ),
    }))
    .filter((row) => row.miles <= 12)
    .sort((a, b) => a.miles - b.miles)
    .slice(0, limit);
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
  return `/pages/${ids.service || ids.serviceSlug}/${townSlug}`;
}

/**
 * @param {object} catalogue Edge catalogue (keywords, services, jobs, manufacturers).
 * @param {object} identity Page identity. kind is service|keyword|job|manufacturer|variant.
 */
/**
 * CLOSE-Q8 (2026-10-14): lean town pages (real facts present) carry the 24
 * Greater Manchester coverage towns nearest to the page's own town (by
 * coordinates from town-geo-slim) instead of none. Towns without coordinates
 * go last. The hub size cap is untouched.
 */
const LEAN_COVERAGE = 24;
function geoOf(slug) {
  const g = townGeo[slug] || BOROUGH_GEO[slug];
  return g && Number.isFinite(g[5]) && Number.isFinite(g[6]) ? { lat: g[5], lng: g[6] } : null;
}
function milesApart(a, b) {
  const t = Math.PI / 180;
  const h = Math.sin((b.lat - a.lat) * t / 2) ** 2 + Math.cos(a.lat * t) * Math.cos(b.lat * t) * Math.sin((b.lng - a.lng) * t / 2) ** 2;
  return 3958.8 * 2 * Math.asin(Math.sqrt(h));
}
function nearestSlice(items, townSlug, size) {
  const here = geoOf(townSlug);
  if (!here) return items.slice(0, size);
  return items
    .map((item, i) => {
      const g = geoOf(item.slug);
      return [g ? milesApart(here, g) : Infinity, i, item];
    })
    .sort((a, b) => a[0] - b[0] || a[1] - b[1])
    .slice(0, size)
    .map((row) => row[2]);
}

function spokenLinkLabel(slug, name, townName = "") {
  const spoken = spokenSubject(slug, name);
  return townName ? `${spoken} in ${townName}` : spoken;
}

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
    parents.push({ href: "/pages/keywords", label: "All service guides" });
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
  if (townSlug && isLocalTown(townSlug)) {
    const linkedName = townName || gmTownName(townSlug);
    parents.push({ href: `/pages/areas/${townSlug}`, label: `Property services in ${linkedName}` });
  }
  parents.push({ href: "/pages/areas", label: "Greater Manchester areas" });

  const sameTown = [];
  // P2-20-5 (2026-10-20): manufacturer x town pages also link the related services in
  // the same town, so thinner brand catalogues (ABB, Allegion, CDVI...) clear 80 links.
  if (kind === "service" || kind === "keyword" || kind === "job" || kind === "variant" || kind === "manufacturer") {
    // CLOSE-Q8: lean town pages link every related service in the same town (16), not 8.
    for (const slug of relatedServiceSlugs(catalogue, serviceSlug, identity.lean ? 16 : 8)) {
      const name = serviceLabel(catalogue, slug);
      // Service×town pages outside the dual-ring towns 301 (or are not
      // exported), so never link them from a town page.
      if (!name || !townSlug || !isLocalTown(townSlug)) continue;
      const href = slug === "aov-air-handling" && !AOV_BUILT.has(townSlug)
        ? "/pages/services/aov-air-handling"
        : `/pages/${slug}/${townSlug}`;
      sameTown.push({ href, label: `${name} in ${townName}` });
    }
  }
  if (kind === "keyword" || kind === "variant") {
    const siblings = ((catalogue.services && catalogue.services.keywords) || {})[serviceSlug] || [];
    for (const slug of siblings) {
      if (!keywords[slug] || slug === identity.keyword) continue;
      if (!townSlug) continue;
      sameTown.push({
        href: `/pages/keywords/${slug}/${townSlug}`,
        label: spokenLinkLabel(slug, keywords[slug].name, townName),
      });
    }
  }
  if (kind === "job") {
    for (const [slug, job] of Object.entries(jobs)) {
      if (slug === identity.job || !job || job.service !== serviceSlug || !townSlug) continue;
      sameTown.push({ href: `/pages/jobs/${slug}/${townSlug}`, label: spokenLinkLabel(slug, job.name, townName) });
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
  const distanceNear = nearestPlaceSlugs(townSlug, catalogue.places || {}, 8);
  for (const town of distanceNear) {
    nearby.push({
      href: leafHref(kind === "variant" ? "keyword" : kind, identity, town.slug),
      label: `${subject} in ${town.name}`,
      slug: town.slug,
    });
  }
  const nearSet = new Set(distanceNear.length ? distanceNear.map((town) => town.slug) : adjacentGmSlugs(townSlug, 12));
  for (const town of gmTowns) {
    if (!town.slug || town.slug === townSlug) continue;
    const item = {
      href: leafHref(kind === "variant" ? "keyword" : kind, identity, town.slug),
      label: `${subject} in ${town.name}`,
      slug: town.slug,
    };
    if (!distanceNear.length && nearSet.has(town.slug) && nearby.length < 12) nearby.push(item);
    else if (!nearSet.has(town.slug)) coverage.push(item);
  }

  const cross = [];
  const serviceKeywords = ((catalogue.services && catalogue.services.keywords) || {})[serviceSlug] || [];
  for (const slug of serviceKeywords) {
    const row = keywords[slug];
    if (!row) continue;
    if (!HUB_SKIP.has(slug)) {
      cross.push({ href: `/pages/keywords/${slug}`, label: spokenLinkLabel(slug, row.name) });
    }
    if (townSlug && isLocalTown(townSlug) && kind !== "keyword" && kind !== "variant") {
      cross.push({ href: `/pages/keywords/${slug}/${townSlug}`, label: spokenLinkLabel(slug, row.name, townName) });
    }
  }
  const jobEntries = Object.entries(jobs).filter(([, job]) => job && job.service === serviceSlug);
  const jobFallback = Object.entries(jobs).slice(0, 4);
  const jobList = (jobEntries.length ? jobEntries : jobFallback).slice(0, 4);
  for (const [slug, job] of jobList) {
    if (kind === "job" && slug === identity.job) continue;
    cross.push({ href: `/pages/jobs/${slug}`, label: spokenLinkLabel(slug, job.name) });
    if (townSlug && isLocalTown(townSlug)) cross.push({ href: `/pages/jobs/${slug}/${townSlug}`, label: spokenLinkLabel(slug, job.name, townName) });
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
      ["Same work across Greater Manchester", dedupe(identity.lean ? nearestSlice(coverage, townSlug, LEAN_COVERAGE) : coverage, selfPath)],
      ["Jobs and guides", dedupe(cross, selfPath)],
      ["Manufacturers", dedupe(brands, selfPath)],
      ["Quotes and guides", dedupe(utility, selfPath)],
    ],
  });
}

export function breadcrumbHtml(items) {
  const list = items.filter((item) => item && item.label);
  const parts = list.map((item, index) => {
    const name = escapeHtml(item.label);
    const last = index === list.length - 1;
    const href = item.href && !last ? escapeHtml(item.href) : "";
    const body = href
      ? `<a itemprop="item" href="${href}"><span itemprop="name">${name}</span></a>`
      : `<span itemprop="name">${name}</span>`;
    return `<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">${body}<meta itemprop="position" content="${index + 1}"></li>`;
  });
  return `<nav class="breadcrumb" aria-label="Breadcrumb"><ol itemscope itemtype="https://schema.org/BreadcrumbList">${parts.join("")}</ol></nav>`;
}
