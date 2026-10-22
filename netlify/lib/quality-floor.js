/**
 * Shared quality floor for edge HTML: visible breadcrumbs, FAQ schema,
 * a real-photo slot, image credits on content images, and a local essay
 * written from service facts.
 */
import copy from "../../website/data/homeowner-copy.json" with { type: "json" };
import services from "../../website/data/services.json" with { type: "json" };
import creditsDoc from "../../website/data/hero-image-credits.json" with { type: "json" };
import distinctEssays from "../../website/data/distinct-essays.json" with { type: "json" };
import keywordServices from "../../website/data/keyword-services.json" with { type: "json" };
import keywordFallback from "../../website/data/keyword-service-fallback.json" with { type: "json" };
import imageManifest from "../../website/data/image-manifest.json" with { type: "json" };
import brandHeroes from "../../website/data/manufacturer-hero-images.json" with { type: "json" };
import resourceArticles from "../../website/data/resource-article-images.json" with { type: "json" };
import poolOverrides from "../../website/data/page-image-pool-overrides.json" with { type: "json" };
import p0Index from "../../website/data/seo-lock/p0-index.json" with { type: "json" };
import commercialJobsDoc from "../../website/data/commercial-jobs.json" with { type: "json" };
import { sanitizeInternalLinks } from "./internal-links.js";
import { landlordLines } from "./landlord-note.js";
import { aliasFamily, pickImages, isCollapsedAov, imageOwner, poolHas } from "./image-pool.js";

const CRUMB_VERB = {
  fit: "fitting", swap: "replacement", replace: "replacement", install: "installation",
  repair: "repair", service: "servicing", build: "construction",
};
const CRUMB_ACRONYM = {
  aov: "AOV", ahu: "AHU", grp: "GRP", epdm: "EPDM", eicr: "EICR", ev: "EV", pir: "PIR",
  upvc: "uPVC", lvt: "LVT", cctv: "CCTV", cp12: "CP12", cp44: "CP44", pat: "PAT", hmo: "HMO",
  cdm: "CDM", co: "CO", tv: "TV", ip: "IP", led: "LED", rcd: "RCD", mcb: "MCB",
  epc: "EPC", fra: "FRA", hd: "HD",
};

function crumbSubject(slug) {
  const parts = String(slug || "").split("-").filter(Boolean);
  if (!parts.length) return "";
  const last = parts[parts.length - 1].toLowerCase();
  if (CRUMB_VERB[last]) parts[parts.length - 1] = CRUMB_VERB[last];
  const out = [];
  for (let i = 0; i < parts.length; i++) {
    const lower = String(parts[i]).toLowerCase();
    if (lower === "d" && String(parts[i + 1] || "").toLowerCase() === "h") {
      out.push("D+H");
      i += 1;
      continue;
    }
    if (CRUMB_ACRONYM[lower]) out.push(CRUMB_ACRONYM[lower]);
    else if (out.length === 0) out.push(lower.charAt(0).toUpperCase() + lower.slice(1));
    else out.push(lower);
  }
  return out.join(" ");
}

function keywordService(slug) {
  const rows = p0Index.rows || p0Index;
  const row = rows[slug];
  const fromIndex = row && typeof row.service === "string" ? row.service : "";
  const service = keywordServices[slug] || keywordFallback[slug] || fromIndex || "";
  return service === "aov" ? "aov-air-handling" : service;
}

const COMMERCIAL_JOB_SERVICE = new Map(
  (Array.isArray(commercialJobsDoc.jobs) ? commercialJobsDoc.jobs : [])
    .filter((job) => job && job.slug)
    .map((job) => [String(job.slug), job.service === "aov" ? "aov-air-handling" : String(job.service || "")]),
);

function commercialJobService(slug) {
  return COMMERCIAL_JOB_SERVICE.get(String(slug || "")) || "";
}

function isServiceCard(imageKey, src) {
  return /^page-/.test(String(imageKey || "")) && /\/assets\/images\/services\/[a-z0-9-]+\.jpe?g(?:$|\?)/i.test(String(src || ""));
}

function serviceHero(path, service) {
  const key = service === "aov" ? "aov" : (service || "fire-alarms");
  if (key === "aov" || key === "aov-air-handling") return pickImages(path, key, 2)[0];
  return `/assets/images/services/${key}.jpg`;
}

const CREDIT = "iComply Property Services";
const REAL_JOB = new Set([
  "index", "company", "packages", "resources", "manufacturers", "areas",
  "about", "privacy", "terms", "renewals", "contact", "home",
]);
const BARRIER_BRANDS = new Set([
  "apt-controls", "atg-access", "avon-barrier", "dea", "designa", "eagle",
  "frontier-pitts", "heald", "hysecurity", "key-automation", "life", "newgate", "skidata",
]);
const creditBySrc = new Map();
for (const row of Array.isArray(creditsDoc) ? creditsDoc : []) {
  if (row && row.file) creditBySrc.set(String(row.file), row);
}

export function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function pagePath(html) {
  const canon = String(html).match(/<link[^>]+rel="canonical"[^>]+href="([^"]+)"/i)
    || String(html).match(/<link[^>]+href="([^"]+)"[^>]+rel="canonical"/i);
  if (canon) {
    try { return new URL(canon[1], "https://icomplypropertyservices.co.uk").pathname.replace(/\/$/, "") || "/"; }
    catch { return "/"; }
  }
  return "/";
}

function plainH1(html) {
  const m = String(html).match(/<h1\b[^>]*>([\s\S]*?)<\/h1>/i);
  if (!m) return "Property work";
  return m[1].replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim() || "Property work";
}

function titleTown(slug) {
  const small = new Set(["under", "upon", "le", "in", "the", "of", "on", "de", "y"]);
  return String(slug || "stockport").split("-").map((word, index) => {
    if (word === "st") return "St";
    if (index > 0 && small.has(word)) return word;
    return word.charAt(0).toUpperCase() + word.slice(1);
  }).join(" ");
}

export function serviceKeyFromPath(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  if (parts[0] !== "pages") return "default";
  if (parts[1] === "services") return copy.services[parts[2]] ? parts[2] : "default";
  if (parts[1] === "aov") return "aov";
  if (parts[1] === "barriers") return "barriers";
  if (parts[1] === "manufacturers" && BARRIER_BRANDS.has(parts[2] || "")) return "barriers";
  if (copy.services[parts[1]]) return parts[1];
  return "default";
}

function placeFromPath(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  if (parts[0] !== "pages") return "Stockport";
  const head = parts[1] || "";
  if (["keywords", "jobs", "manufacturers"].includes(head) && parts[3]) return titleTown(parts[3]);
  if (head === "areas" && parts[2]) return titleTown(parts[2]);
  if (head === "services") return "Stockport";
  if (parts[2] && !["keywords", "jobs", "manufacturers", "areas", "resources", "packages"].includes(head)) {
    return titleTown(parts[2]);
  }
  return "Stockport";
}

function shortSubject(subject, key, path = "") {
  const parts = String(path || "").split("/").filter(Boolean);
  const slug = parts[1] === "services" ? parts[2] : parts[1];
  if (slug && services[slug]) return services[slug];
  if (key && key !== "default" && services[key]) return services[key];
  let cleaned = String(subject || "").replace(/\s+in\s+.+$/i, "").trim();
  cleaned = cleaned.replace(/\.\s*POA\.?.*$/i, "").trim();
  cleaned = cleaned.replace(/\btrade supply only\b/gi, "").replace(/\bproducts\s*(?:&(?:amp;)?|and)\s*service\b/gi, "");
  const leaf = (parts[parts.length - 1] || "").toLowerCase();
  if (leaf) {
    const escaped = leaf.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    cleaned = cleaned.replace(new RegExp(`\\s*\\(${escaped}\\)`, "i"), "");
  }
  cleaned = cleaned.replace(/\s+/g, " ").trim();
  const first = cleaned.split(".")[0].trim();
  return first || cleaned || "the work";
}

function wordCount(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

const GARDEN_FAMILY = new Set([
  "fencing", "landscaping", "demolition", "driveways", "garden-clearance", "garden-maintenance", "patios",
]);
const GAS_PACKAGE_PATHS = new Set([
  "/pages/packages/compliance-bundle",
  "/pages/packages/landlord-pack",
  "/pages/landlords",
]);
const GAS_MANUFACTURERS = new Set([
  "alpha", "ariston", "baxi", "ferroli", "firebird", "glow-worm", "grant", "ideal",
  "intergas", "main-heating", "navien", "potterton", "ravenheat", "remeha", "vaillant",
  "viessmann", "vokera", "worcester-bosch",
]);
const OFFICE = "17 Woodlands Park Road, Offerton, Stockport SK2 5DE";
const hashToSource = new Map();
for (const [src, meta] of Object.entries(imageManifest)) {
  for (const variant of meta.variants || []) {
    const id = String(variant.webp || "").match(/\/opt\/([a-f0-9]+)-/i);
    if (id && !hashToSource.has(id[1])) hashToSource.set(id[1], src);
  }
}

function escapeReg(value) {
  return String(value).replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

export function isGasPath(path, subject = "") {
  const value = `${path} ${subject}`;
  const clean = String(path || "").replace(/\/$/, "") || "/";
  if (GAS_PACKAGE_PATHS.has(clean)) return true;
  const brand = clean.match(/^\/pages\/manufacturers\/([a-z0-9-]+)$/);
  if (brand && GAS_MANUFACTURERS.has(brand[1])) return true;
  if (/\/(?:gas-systems|heating)(?:\/|$)/.test(path)) return true;
  if (/heating/i.test(value)) return true;
  return /\bgas\b|cp12|boiler/i.test(value);
}

function fillCopy(text, place, subject) {
  const town = place || "Stockport";
  const job = subject || "the work";
  return String(text).replaceAll("{subject}", job).replaceAll("{place}", town);
}

function capPlaceMentions(text) {
  return String(text);
}

function packFor(key) {
  if (!key || key === "default" || !copy.services[key]) {
    return { key: "default", paragraphs: [], notes: [], facts: [] };
  }
  return {
    key,
    paragraphs: copy.services[key] || [],
    notes: (copy.notes && copy.notes[key]) || [],
    facts: (copy.facts && copy.facts[key]) || [],
  };
}

function distinctParagraphs(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  if (parts[1] === "manufacturers" && distinctEssays.manufacturers?.[parts[2]]) {
    return distinctEssays.manufacturers[parts[2]];
  }
  if (parts[1] === "packages" && distinctEssays.packages?.[parts[2]]) {
    return distinctEssays.packages[parts[2]];
  }
  return null;
}

export function essayHtml(path, subject, place) {
  const town = place || "Stockport";
  const own = distinctParagraphs(path);
  if (own) {
    return own.map((text) => `<p>${escapeHtml(capPlaceMentions(text, town))}</p>`).join("");
  }
  const key = serviceKeyFromPath(path);
  const pack = packFor(key);
  const job = shortSubject(subject, pack.key, path);
  const blocks = [];
  for (const list of [pack.paragraphs, pack.notes, pack.facts]) {
    for (const item of list || []) blocks.push(fillCopy(item, town, job));
  }
  for (const line of landlordLines(path, job, town)) blocks.push(line);
  return blocks.filter(Boolean).map((line) => `<p>${escapeHtml(line)}</p>`).join("");
}

function pageFamily(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  const leaf = (parts[parts.length - 1] || "").toLowerCase();
  if (path === "/" || path === "") return "hub:home";
  if (head === "services" && services[parts[2]]) return parts[2];
  if (head === "aov") return "aov-air-handling";
  if (head === "barriers" || (head === "manufacturers" && BARRIER_BRANDS.has(parts[2] || ""))) return "barriers";
  if (head === "keywords" && keywordService(parts[2])) return aliasFamily(keywordService(parts[2]));
  if (head === "jobs" && parts[2] && (services[parts[2]] || keywordService(parts[2]))) {
    return services[parts[2]] ? parts[2] : aliasFamily(keywordService(parts[2]));
  }
  if ((head === "keywords" || head === "jobs") && parts[2] && parts[2] !== "index") {
    const blob = String(parts[2]).replace(/-/g, " ");
    if (/\b(cp12|cp44|gas safety|boiler|gas certificate|landlord gas)\b/.test(blob)
      && !/\b(gas suppression|inert gas|clean agent)\b/.test(blob)) {
      return "gas-systems";
    }
    return "building-maintenance";
  }
  if (head === "jobs" || leaf === "jobs" || leaf === "become-a-subcontractor" || String(path).includes("become-a-subcontractor")) {
    return "hub:company";
  }
  if (head === "packages" || leaf === "packages") return "hub:packages";
  if (head === "areas" || leaf === "areas") return "hub:areas";
  if (head === "directories" || leaf === "directories") return "directory:directories";
  if (head === "resources" || leaf === "resources") return "hub:resources";
  if (head === "manufacturers" || leaf === "manufacturers") return "hub:manufacturers";
  if (head === "care-homes" || /care-home/.test(path)) return "nurse-call";
  if (/asbestos/.test(path)) return "asbestos-survey";
  if (head === "landlords" || /landlord/.test(leaf)) return "landlord-compliance";
  if (head === "commercial") {
    const service = commercialJobService(parts[0] === "pages" ? (parts[2] || "") : (parts[1] || ""));
    if (service && services[service]) return service;
    if (services["commercial-fit-out"]) return "commercial-fit-out";
  }
  if (services[head] && !["keywords", "jobs", "areas", "manufacturers", "packages", "resources"].includes(head)) return head;
  if (REAL_JOB.has(leaf)) return `hub:${leaf === "index" ? "home" : leaf}`;
  if (REAL_JOB.has(head)) return `hub:${head === "index" ? "home" : head}`;
  if (head === "products" || head === "shop" || leaf === "products" || leaf === "shop") return "products";
  if (head === "services" && !parts[2]) return "directory:services";
  if (head === "keywords" && !parts[2]) return "directory:keywords";
  return "directory:unknown";
}

export function manifestHas(src) {
  return Object.prototype.hasOwnProperty.call(imageManifest, src);
}

function pathParts(path) {
  return String(path || "").replace(/\.php$/i, "").replace(/\/+$/, "").split("/").filter(Boolean);
}

function isHubIndex(path, hub) {
  const parts = pathParts(path);
  if (parts[0] === "pages") parts.shift();
  return parts[0] === hub && (!parts[1] || parts[1] === "index");
}

const AOV_EQUIPMENT = /\/(?:smoke-vent-roof-1|smoke-vent-hatch-1|smoke-vent-cover-1|smoke-shaft-1|aov-release-switch-1|aov-control-panel-1|aov-control-panel-2|aov-roof-hatch-1)\.jpe?g(?:$|\?)/i;

function isAovEquipment(src) {
  return poolHas("aov", src) || AOV_EQUIPMENT.test(String(src || ""));
}

function isElectricalHero(src) {
  const value = String(src || "");
  return /\/services\/electrical\.jpe?g(?:$|\?)/i.test(value) || value.includes("21bb8660f87c");
}

function isElectricalServicePage(path) {
  const parts = pathParts(path);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  const slug = parts[0] === "pages" ? (parts[2] || "") : (parts[1] || "");
  return head === "electrical" || (head === "services" && slug === "electrical");
}

function pageImageKey(path) {
  const parts = pathParts(path);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  const slug = parts[0] === "pages" ? (parts[2] || "") : (parts[1] || "");
  if (head === "aov") return "aov";
  if (head === "aov-air-handling") return "aov-air-handling";
  if (head === "services" && slug === "aov") return "aov";
  if (head === "services" && slug === "aov-air-handling") return "aov-air-handling";
  if (head === "about") return "page-about";
  if (slug && poolOverrides[`${head}/${slug}`]) return poolOverrides[`${head}/${slug}`];
  if (head === "commercial") {
    const service = commercialJobService(slug);
    if (service) return `type-keywords-${service}`;
    return "page-commercial";
  }
  if (head === "keywords" && slug && slug !== "index") {
    const service = keywordService(slug) || "building-maintenance";
    return `type-keywords-${service}`;
  }
  if (head === "jobs" && slug && slug !== "index") {
    const row = (p0Index.rows || p0Index)[slug] || {};
    const service = row.service || keywordService(slug) || "building-maintenance";
    return `type-jobs-${service}`;
  }
  if (head === "resources" && slug && slug !== "index" && !isHubIndex(path, "resources")) {
    const service = resourceArticles[slug] || "compliance-consultancy";
    return `type-resources-${service}`;
  }
  return "";
}

function manufacturerSrc(path) {
  const parts = pathParts(path);
  const offset = parts[0] === "pages" ? 1 : 0;
  const brand = parts[offset + 1] || "";
  const town = parts[offset + 2] || "";
  const row = brandHeroes[brand] || {};
  let image = String(row.image || "");
  const service = String(row.service || "fire-alarms");
  const exactAssa = brand === "assa-abloy" && !town;
  if (image.endsWith("/assa-abloy.svg") && !exactAssa) image = "";
  if (/\/services\/aov-air-handling(?:-(?:photo|context))?\.jpe?g$/i.test(image)) image = "";
  if (isAovEquipment(image) || isElectricalHero(image) || /\/page-sets\//i.test(image) || /\/page-types\//i.test(image)) image = "";
  if (image) return image;
  if (service === "aov" || service === "aov-air-handling" || brand === "assa-abloy") {
    return "/assets/images/manufacturers/lines/assa-abloy-door-closers.svg";
  }
  return serviceHero(path, service);
}

function resourceArticleSrc(path) {
  const parts = pathParts(path);
  const slug = parts[parts.length - 1] || "";
  const service = resourceArticles[slug] || "compliance-consultancy";
  return serviceHero(path, service);
}

function borrowedImage(origin, path) {
  if (/\/assets\/images\/keywords\//i.test(origin)) return true;
  if (/\/real-job\/manufacturers\.jpe?g$/i.test(origin) && !isHubIndex(path, "manufacturers")) return true;
  if (/\/real-job\/resources\.jpe?g$/i.test(origin) && !isHubIndex(path, "resources")) return true;
  if (/\/assa-abloy\.svg$/i.test(origin)) {
    const parts = pathParts(path);
    const offset = parts[0] === "pages" ? 1 : 0;
    const brand = parts[offset + 1] || "";
    const town = parts[offset + 2] || "";
    if (brand !== "assa-abloy" || town) return true;
  }
  return false;
}

function canonicalSrc(path) {
  const imageKey = pageImageKey(path);
  if (imageKey) return pickImages(path, imageKey, 1)[0];
  if (path === "/" || path === "") return "/assets/images/real-job/home.jpg";
  const family = pageFamily(path);
  if (family === "products") return "/assets/images/real-job/products-hub.jpg";
  if (family.startsWith("directory:")) return "/assets/images/real-job/directory-hub.jpg";
  if (family.startsWith("hub:")) {
    const leaf = family.slice(4);
    if (leaf === "resources") {
      return isHubIndex(path, "resources") ? "/assets/images/real-job/resources.jpg" : resourceArticleSrc(path);
    }
    if (leaf === "manufacturers") {
      return isHubIndex(path, "manufacturers") ? "/assets/images/real-job/manufacturers.jpg" : manufacturerSrc(path);
    }
    if (leaf === "home") return "/assets/images/real-job/index.jpg";
    if (leaf === "jobs" || leaf === "company") {
      if (/\/(?:jobs|become-a-subcontractor)(?:\/|$)/.test(path) || path.includes("become-a-subcontractor")) {
        return "/assets/images/real-job/jobs-hub.jpg";
      }
      return "/assets/images/real-job/index.jpg";
    }
    return `/assets/images/real-job/${leaf}.jpg`;
  }
  if (family === "aov" || family === "aov-air-handling") return pickImages(path, family, 2)[0];
  return `/assets/images/services/${family}.jpg`;
}

function sourceOfImage(src) {
  const clean = String(src || "").split("?")[0];
  const hash = clean.match(/\/opt\/([a-f0-9]+)-/i);
  if (hash && hashToSource.has(hash[1])) return hashToSource.get(hash[1]);
  return clean;
}

function imageFamily(src) {
  const origin = sourceOfImage(src);
  const service = origin.match(/\/assets\/images\/services\/([a-z0-9-]+?)(?:-(?:photo|context))?\.jpe?g$/i);
  if (service) return service[1];
  const lane = origin.match(/\/assets\/images\/lanes\/([a-z0-9-]+)\//i) || String(src).match(/\/lanes\/([a-z0-9-]+)\//i);
  if (lane) return lane[1];
  const keyword = origin.match(/\/assets\/images\/keywords\/([a-z0-9-]+)\.jpe?g$/i);
  if (keyword) return aliasFamily(keywordService(keyword[1]));
  const trade = origin.match(/\/assets\/images\/trades\/([a-z0-9-]+)--/i);
  if (trade) return aliasFamily(trade[1] === "aov" ? "aov-air-handling" : trade[1]);
  const hub = origin.match(/\/assets\/images\/real-job\/([a-z0-9-]+)\.jpe?g$/i);
  if (hub) return `hub:${hub[1]}`;
  if (/\/assets\/images\/(?:products|shop)\//i.test(origin)) return "products";
  return "";
}

function isPlaceholderImage(src) {
  const origin = sourceOfImage(src);
  if (/\/lanes\//i.test(origin) || /\/lanes\//i.test(src)) return true;
  if (/\/aov-air-handling-(?:photo|context)\.jpe?g$/i.test(origin)) return false;
  return /-(?:photo|context)\./i.test(origin);
}

function slotSrc(path) {
  return canonicalSrc(path);
}

function crumbBlock(path, subject) {
  const parts = String(path).split("/").filter(Boolean);
  const items = [{ href: "/", label: "Home" }];
  const head = parts[0] === "pages" ? (parts[1] || "") : "";
  const pushHub = (href, label) => items.push({ href, label });
  if (head === "services") {
    pushHub("/pages/services", "Services");
  } else if (head === "keywords") {
    pushHub("/pages/keywords", "Guides");
    if (parts[2] && parts[3]) pushHub(`/pages/keywords/${parts[2]}`, crumbSubject(parts[2]));
  } else if (head === "jobs") {
    pushHub("/pages/jobs", "Jobs");
    if (parts[2] && parts[3]) pushHub(`/pages/jobs/${parts[2]}`, crumbSubject(parts[2]));
  } else if (head === "areas") {
    pushHub("/pages/areas", "Areas");
  } else if (head === "manufacturers") {
    pushHub("/pages/manufacturers", "Manufacturers");
    if (parts[2] && parts[3]) pushHub(`/pages/manufacturers/${parts[2]}`, parts[2].replace(/-/g, " "));
  } else if (head === "aov" || head === "barriers") {
    pushHub(`/pages/${head}`, head === "aov" ? "AOV" : "Barriers");
  } else if (services[head]) {
    pushHub(`/pages/services/${head}`, services[head]);
  } else if (head) {
    pushHub(`/pages/${head}`, head.replace(/-/g, " "));
  }
  items.push({ label: subject || "This page" });
  const lis = items.map((item, index) => {
    const name = escapeHtml(item.label);
    const href = item.href ? escapeHtml(item.href) : "";
    const body = href && index !== items.length - 1
      ? `<a itemprop="item" href="${href}"><span itemprop="name">${name}</span></a>`
      : `<span itemprop="name">${name}</span>`;
    return `<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">${body}<meta itemprop="position" content="${index + 1}"></li>`;
  }).join("");
  const origin = "https://icomplypropertyservices.co.uk";
  const ld = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: items.map((item, index) => ({
      "@type": "ListItem",
      position: index + 1,
      name: item.label,
      item: origin + (item.href || path || "/"),
    })),
  };
  return `<nav class="breadcrumb" aria-label="Breadcrumb"><ol itemscope itemtype="https://schema.org/BreadcrumbList">${lis}</ol></nav>`
    + `<script type="application/ld+json">${JSON.stringify(ld)}</script>`;
}

function faqBlock(subject, place, path) {
  const q1 = `How is ${subject} booked?`;
  const a1 = `Call 07517 806082 or WhatsApp the same number. The office is 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. You get a free written quote before work is agreed.`;
  const q2 = "Who attends the visit?";
  const a2 = isGasPath(path, subject)
    ? "Gas work is carried out by Gas Safe registered engineers."
    : "iComply arranges the visit from the office at 17 Woodlands Park Road, Offerton, SK2 5DE.";
  const ld = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: [
      { "@type": "Question", name: q1, acceptedAnswer: { "@type": "Answer", text: a1 } },
      { "@type": "Question", name: q2, acceptedAnswer: { "@type": "Answer", text: a2 } },
    ],
  };
  return `<section class="faq" id="quality-floor-faq"><h2>Questions</h2>`
    + `<details><summary>${escapeHtml(q1)}</summary><p>${escapeHtml(a1)}</p></details>`
    + `<details><summary>${escapeHtml(q2)}</summary><p>${escapeHtml(a2)}</p></details>`
    + `</section><script type="application/ld+json">${JSON.stringify(ld)}</script>`;
}

function absoluteImageUrl(url) {
  const value = String(url || "").trim();
  if (/^https:\/\//i.test(value)) return value;
  if (/^http:\/\//i.test(value)) return `https://${value.slice(7)}`;
  if (value.startsWith("//")) return `https:${value}`;
  const path = value.replace(/^https?:\/\/[^/]+/i, "") || "/";
  return `https://icomplypropertyservices.co.uk${path.startsWith("/") ? path : `/${path}`}`;
}

export function creditName(src) {
  let path = sourceOfImage(src);
  if (!path.startsWith("/")) path = path.replace(/^https?:\/\/[^/]+/i, "");
  if (/\/demolition(?:-(?:photo|context))?\.jpe?g$/i.test(path)) return "Tomwsulcer (CC0)";
  const base = path.replace(/-(?:photo|context)(\.[a-z0-9]+)$/i, "$1");
  const row = creditBySrc.get(path) || creditBySrc.get(base);
  if (!row) return "";
  const source = String(row.source || "");
  if (row.own && !/^https?:/i.test(source)) return CREDIT;
  if (!/^https?:/i.test(source)) return "";
  const artist = String(row.artist || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
  const license = String(row.license || "").replace(/<[^>]+>/g, " ").trim();
  const name = (artist || "Wikimedia Commons").slice(0, 120);
  return license ? `${name} (${license})` : name;
}

function imageAlt(src) {
  let path = sourceOfImage(src);
  if (!path.startsWith("/")) path = path.replace(/^https?:\/\/[^/]+/i, "");
  const base = path.replace(/-(?:photo|context)(\.[a-z0-9]+)$/i, "$1");
  const row = creditBySrc.get(path) || creditBySrc.get(base);
  if (!row || !/^https?:/i.test(String(row.source || ""))) return "";
  const given = String(row.alt || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
  if (given) return given.length > 180 ? "Photograph" : given;
  let title = String(row.title || "")
    .replace(/^File:/i, "")
    .replace(/\.(jpe?g|png|webp|gif|svg)$/i, "")
    .replace(/\s+-\s+geograph\.org\.uk\s+-\s+\d+\s*$/i, "")
    .replace(/<[^>]+>/g, " ")
    .replaceAll("&amp;", "&")
    .replace(/\s+/g, " ")
    .trim();
  if (!title || title.length > 180) return "Photograph";
  if (/[^\u0000-\u007F]/.test(title) || /\b(einem|einen|einer|für|fuer|und|der|die|das|dachluke|rauchabzug)\b/i.test(title)) {
    return "";
  }
  return title;
}

function neutralAlt(alt) {
  let text = String(alt || "");
  let passes = 0;
  while (text.includes("&amp;") && passes < 4) {
    text = text.replaceAll("&amp;", "&");
    passes += 1;
  }
  text = text
    .replace(/&#0?38;/g, "&")
    .replace(/^Completed\s+(.+?)\s+by\s+.+$/i, "$1")
    .replace(/\s+used by iComply\b.*/i, "")
    .replace(/\s+by\s+iComply Property Services\b.*/i, "")
    .replace(/\s+by\s+iComply\b.*/i, "")
    .replace(/\s+[—–-]\s+iComply(?: Property Services)?\b.*/i, "")
    .replace(/\s+/g, " ")
    .trim();
  return text || "Photograph";
}

function skipImg(attrs) {
  const src = (String(attrs).match(/\bsrc\s*=\s*"([^"]*)"/i) || [])[1] || "";
  const alt = (String(attrs).match(/\balt\s*=\s*"([^"]*)"/i) || [])[1] || "";
  const cls = (String(attrs).match(/\bclass\s*=\s*"([^"]*)"/i) || [])[1] || "";
  const width = Number((String(attrs).match(/\bwidth\s*=\s*"(\d+)"/i) || [])[1] || 0);
  if (/\/assa-abloy\.svg(?:$|\?)/i.test(src)) return false;
  if (/\.svg(?:$|\?)/i.test(src) || /favicon|\/logo/i.test(src)) return true;
  if (/\blogo\b|\bicon\b|\bbrand\b|mega-logo/i.test(cls)) return true;
  if (/\blogo\b/i.test(alt)) return true;
  if (width > 0 && width <= 64) return true;
  return false;
}

function stampCredits(html) {
  const held = [];
  let masked = String(html).replace(/<(header|nav|footer)\b[^>]*>[\s\S]*?<\/\1>/gi, (block) => {
    const cleaned = block
      .replace(/\sdata-credit="[^"]*"/gi, "")
      .replace(/\sdata-credit-stamped="[^"]*"/gi, "")
      .replace(/<span class="image-credit">[\s\S]*?<\/span>/gi, "");
    held.push(cleaned);
    return `\u0000HOLD${held.length - 1}\u0000`;
  });
  masked = masked
    .replace(/<figcaption>\s*Image credit:[\s\S]*?<\/figcaption>/gi, "")
    .replace(/<span class="image-credit">[\s\S]*?<\/span>/gi, "");
  const stamped = masked.replace(/<img\b([^>]*?)>/gi, (tag, attrs) => {
    let next = String(attrs).replace(/\s*\/\s*$/, "");
    const src = (next.match(/\bsrc\s*=\s*"([^"]*)"/i) || [])[1] || "";
    const skip = skipImg(next) || !src;
    const described = skip ? "" : imageAlt(src);
    if (described) {
      if (/\balt\s*=/.test(next)) next = next.replace(/\balt\s*=\s*"[^"]*"/i, `alt="${escapeHtml(described)}"`);
      else next += ` alt="${escapeHtml(described)}"`;
    } else {
      next = next.replace(/\balt\s*=\s*"([^"]*)"/i, (full, alt) => `alt="${escapeHtml(neutralAlt(alt))}"`);
    }
    next = next.replace(/\sdata-credit="[^"]*"/gi, "").replace(/\sdata-credit-stamped="[^"]*"/gi, "");
    if (skip) return `<img${next}>`;
    const name = creditName(src);
    if (!name) return `<img${next}>`;
    next += ` data-credit="${escapeHtml(name)}" data-credit-stamped="1"`;
    return `<img${next}><span class="image-credit">Image credit: ${escapeHtml(name)}</span>`;
  });
  return stamped.replace(/\u0000HOLD(\d+)\u0000/g, (_, index) => held[Number(index)]);
}

function dedupeRepeatedPhotos(html) {
  const text = String(html);
  const re = /<img\b[^>]*\bsrc="([^"]+)"[^>]*>/gi;
  const matches = [...text.matchAll(re)].filter((match) => /\/assets\/images\/.+\.(?:jpe?g|webp)(?:$|\?)/i.test(match[1]));
  const groups = new Map();
  for (const match of matches) {
    const key = match[1].split("?")[0];
    if (!groups.has(key)) groups.set(key, []);
    groups.get(key).push(match);
  }
  const dropAt = new Set();
  const replaceAt = new Map();
  for (const group of groups.values()) {
    if (group.length < 2) continue;
    const first = group[0];
    const real = group.some((match) => /data-pe-slot="real-photo"/i.test(match[0]));
    let tag = first[0];
    if (real && !/data-pe-slot="real-photo"/i.test(tag)) {
      tag = tag.replace(/<img\b/i, '<img data-pe-slot="real-photo"');
    }
    if (tag !== first[0]) replaceAt.set(first.index, tag);
    for (const extra of group.slice(1)) dropAt.add(extra.index);
  }
  if (!dropAt.size && !replaceAt.size) return text;
  return text.replace(re, (tag, src, offset) => {
    if (!/\/assets\/images\/.+\.(?:jpe?g|webp)(?:$|\?)/i.test(src)) return tag;
    if (dropAt.has(offset)) return "";
    return replaceAt.get(offset) || tag;
  });
}

function photoHtml(path, subject) {
  const imageKey = pageImageKey(path);
  const srcs = imageKey === "aov" || imageKey === "aov-air-handling"
    ? pickImages(path, imageKey, 2).slice(0, 2)
    : [slotSrc(path)];
  const imgs = srcs.map((src) => (
    `<img src="${escapeHtml(src)}" alt="${escapeHtml(`Photograph of ${subject}`)}" data-pe-slot="real-photo" width="1200" height="800">`
  )).join("");
  return `<figure class="real-job-photo" data-pe-slot="real-photo" data-real-job-photo="owner">${imgs}</figure>`;
}

function scrubFixedPricing(html) {
  const held = [];
  let out = String(html).replace(/[a-z0-9]+-fixed-price(?:-[a-z0-9]+)+/gi, (slug) => {
    held.push(slug);
    return `\u0001SLUG${held.length - 1}\u0001`;
  });
  const steps = [
    [/No fixed price is published/gi, "Price on application"],
    [/fixed written quotes/gi, "free written quotes"],
    [/fixed written quote/gi, "free written quote"],
    [/Every quote is fixed and written/gi, "Every quote is a free written quote"],
    [/fixed-pricing/gi, "a free written quote"],
    [/fixed pricing/gi, "a free written quote"],
    [/fixed-prices/gi, "free written quotes"],
    [/fixed prices/gi, "free written quotes"],
    [/fixed-price/gi, "free written quote"],
    [/\bPOA fixed quote\b/gi, "a free written quote"],
    [/fixed quotes/gi, "free written quotes"],
    [/fixed quote/gi, "free written quote"],
    [/\bfixed price\b/gi, "free written quote"],
  ];
  for (const [pattern, replacement] of steps) {
    out = out.replace(pattern, (match) => (/^[A-Z]/.test(match) && /^[a-z]/.test(replacement) ? replacement.charAt(0).toUpperCase() + replacement.slice(1) : replacement));
  }
  return out.replace(/\u0001SLUG(\d+)\u0001/g, (_, index) => held[Number(index)] || "");
}

function scrubSentences(html, path, subject) {
  const gas = isGasPath(path, subject);
  const family = pageFamily(path);
  const garden = GARDEN_FAMILY.has(family);
  const held = [];
  const masked = String(html).replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, (block) => {
    if (!gas) {
      block = block
        .replace(/do not describe iComply as Gas Safe[^.!?]*[.!?]/gi, "The office confirms who attends before the visit.")
        .replace(/do not claim the company is Gas Safe[^.!?]*[.!?]/gi, "The office confirms who attends before the visit.")
        .replace(/we do not describe iComply as Gas Safe registered[^.!?]*[.!?]/gi, "The office confirms who attends before the visit.")
        .replace(/Landlord gas safety certificates \(CP12\) are carried out by Gas Safe registered engineers\./gi, "The office confirms who attends before the visit.")
        .replace(/Gas work is carried out by Gas Safe registered engineers\./gi, "The office confirms who attends before the visit.");
    }
    held.push(block);
    return `\u0000SCRUB${held.length - 1}\u0000`;
  });
  const hand = new Set(["demolition", "driveways", "fencing", "drainage"]).has(family);
  const scrubbed = masked.replace(/>([^<]+)</g, (full, text) => {
    let body = text;
    if (hand) {
      body = body
        .replace(/Installation, maintenance, testing and certification\.?/gi, "We survey, quote and carry out the work.")
        .replace(/full certification/gi, "a written scope")
        .replace(/Certificates are emailed to you\./gi, "The written quote is emailed to you.")
        .replace(/Certificates are emailed/gi, "The written quote is emailed");
    }
    if (!/[.!?]/.test(body)) return `>${body}<`;
    text = body;
    const kept = text.split(/(?<=[.!?])\s+/).filter((sentence) => {
      const line = sentence.trim();
      if (!line) return false;
      if (/do not describe iComply as Gas Safe/i.test(line)) return false;
      if (/do not claim the company is Gas Safe/i.test(line)) return false;
      if (/we do not describe iComply as Gas Safe registered/i.test(line)) return false;
      if (!garden && /fill a skip and call the garden clear/i.test(line)) return false;
      if (!gas && /Gas Safe registered/i.test(line)) return false;
      if (/same shape every time/i.test(line)) return false;
      return true;
    });
    return `>${kept.join(" ")}<`;
  });
  return scrubbed.replace(/\u0000SCRUB(\d+)\u0000/g, (_, index) => held[Number(index)] || "");
}

function capTownPrefix(html, place) {
  const town = place || "Stockport";
  const re = new RegExp(`\\bIn ${escapeReg(town)},\\s+`, "g");
  let seen = 0;
  return String(html).replace(re, (match) => {
    seen += 1;
    return seen <= 4 ? match : "\u0002";
  }).replace(/\u0002([a-z])/g, (_, letter) => letter.toUpperCase()).replace(/\u0002/g, "");
}

function rewriteMetaImages(html, path) {
  const imageKey = pageImageKey(path);
  const want = imageKey ? pickImages(path, imageKey, 1)[0] : canonicalSrc(path);
  const family = pageFamily(path);
  const electricalPage = isElectricalServicePage(path);
  return String(html).replace(/<meta\b[^>]*>/gi, (tag) => {
    if (!/\b(?:property|name)\s*=\s*"(?:og:image(?::secure_url)?|twitter:image)"/i.test(tag)) return tag;
    return tag.replace(/\bcontent\s*=\s*"([^"]*)"/i, (full, url) => {
      const pathOnly = String(url).replace(/^https?:\/\/[^/]+/i, "");
      const origin = sourceOfImage(pathOnly);
      const imgFamily = imageFamily(pathOnly);
      const sharedHub = /\/real-job\/(?:company|home)\.jpe?g$/i.test(origin) && path !== "/";
      const collapsed = isCollapsedAov(origin) || isCollapsedAov(pathOnly);
      const borrowed = borrowedImage(origin, path);
      const blocked = isAovEquipment(origin) && imageKey !== "aov"
        || (isElectricalHero(origin) || isElectricalHero(pathOnly)) && !electricalPage
        || /\/page-(?:sets|types)\//i.test(origin) && !(imageKey && poolHas(imageKey, origin));
      let next = String(url);
      const serviceCard = isServiceCard(imageKey, origin);
      const owned = serviceCard || (imageKey ? poolHas(imageKey, origin) : (!imgFamily || (imgFamily === family && !isPlaceholderImage(pathOnly))));
      if (!serviceCard && (sharedHub || collapsed || borrowed || blocked || !owned)) {
        next = String(url).replace(/\/assets\/images\/[^"?#]+/, want);
      }
      return `content="${absoluteImageUrl(next)}"`;
    });
  });
}

function replaceImgSrc(attrs, src) {
  let next = String(attrs);
  if (!/\bsrc\s*=/.test(next)) next = ` src="${src}"${next}`;
  else next = next.replace(/\bsrc\s*=\s*"[^"]*"/i, `src="${src}"`);
  next = next.replace(/\s+srcset\s*=\s*"[^"]*"/gi, "");
  next = next.replace(/\s+data-srcset\s*=\s*"[^"]*"/gi, "");
  next = next.replace(/\s+data-perf\s*=\s*"[^"]*"/gi, "");
  next = next.replace(/\s+onerror\s*=\s*"[^"]*"/gi, "");
  next = next.replace(/\sdata-credit="[^"]*"/gi, "");
  next = next.replace(/\sdata-credit-stamped="[^"]*"/gi, "");
  const label = imageAlt(src) || "Photograph";
  if (/\balt\s*=/.test(next)) next = next.replace(/\balt\s*=\s*"[^"]*"/i, `alt="${escapeHtml(label)}"`);
  else next += ` alt="${escapeHtml(label)}"`;
  return next;
}

function confineImages(html, path) {
  const imageKey = pageImageKey(path);
  const want = imageKey ? pickImages(path, imageKey, 1)[0] : canonicalSrc(path);
  const wantFamily = pageFamily(path);
  const allowProducts = wantFamily === "products";
  const directory = wantFamily.startsWith("directory:") || wantFamily.startsWith("hub:");
  const electricalPage = isElectricalServicePage(path);
  let slot = 0;
  const nextSrc = () => {
    if (imageKey) {
      const picks = pickImages(path, imageKey, 6);
      const choice = picks[slot % picks.length];
      slot += 1;
      return choice;
    }
    if (wantFamily.startsWith("hub:") || wantFamily.startsWith("directory:") || wantFamily === "products" || !wantFamily) return want;
    const picks = pickImages(path, wantFamily, 6);
    const choice = picks[slot % picks.length];
    slot += 1;
    return choice;
  };
  let out = String(html).replace(/<img\b([^>]*?)>/gi, (tag, attrs) => {
    let next = String(attrs).replace(/\s*\/\s*$/, "");
    if (skipImg(next)) return `<img${next}>`;
    const src = (next.match(/\bsrc\s*=\s*"([^"]*)"/i) || [])[1] || "";
    if (!src) return `<img${next}>`;
    const origin = sourceOfImage(src);
    // A brand used on several services has one photo per service. Keep that photo on its own service page.
    const brandSvc = origin.match(/\/assets\/images\/manufacturers\/[a-z0-9-]+--([a-z0-9-]+)\.jpe?g$/i);
    if (brandSvc && brandSvc[1] === wantFamily) return `<img${next}>`;
    const family = imageFamily(src);
    const placeholder = isPlaceholderImage(src);
    const product = family === "products" || /\/(?:products|shop)\//i.test(src);
    const sharedHub = /\/real-job\/(?:company|home)\.jpe?g$/i.test(origin) && path !== "/";
    const collapsed = isCollapsedAov(origin) || isCollapsedAov(src);
    const owner = imageOwner(origin) || imageOwner(src);
    const servicePage = wantFamily && !wantFamily.startsWith("hub:") && !wantFamily.startsWith("directory:") && wantFamily !== "products";
    const foreign = servicePage && owner && owner !== wantFamily && !(imageKey && poolHas(imageKey, origin));
    const sharedMaintenance = wantFamily !== "building-maintenance" && (/\/services\/building-maintenance\.jpg/i.test(origin) || /19f957bd50f5/.test(src));
    const borrowed = borrowedImage(origin, path);
    const serviceCard = isServiceCard(imageKey, origin);
    const wrongSet = (isAovEquipment(origin) || isAovEquipment(src)) && imageKey !== "aov"
      || (isElectricalHero(origin) || isElectricalHero(src)) && !electricalPage && !serviceCard
      || /\/page-(?:sets|types)\//i.test(origin) && !(imageKey && poolHas(imageKey, origin));
    if (serviceCard) return `<img${next}>`;
    if (imageKey) {
      if (!poolHas(imageKey, origin) || sharedHub || collapsed || wrongSet) {
        return `<img${replaceImgSrc(next, nextSrc())}>`;
      }
      return `<img${next}>`;
    }
    if (sharedHub || collapsed || sharedMaintenance || foreign || borrowed || wrongSet) {
      return `<img${replaceImgSrc(next, nextSrc())}>`;
    }
    if (directory) {
      if (family.startsWith("hub:") && family !== wantFamily) return `<img${replaceImgSrc(next, want)}>`;
      if (!placeholder && family && !family.startsWith("hub:") && family !== "products") return `<img${next}>`;
      if (placeholder || family === "hub:home" || family === "hub:company" || !family) return `<img${replaceImgSrc(next, want)}>`;
      return `<img${next}>`;
    }
    if (!placeholder && family === wantFamily && family) return `<img${next}>`;
    if (allowProducts && product) return `<img${next}>`;
    return `<img${replaceImgSrc(next, nextSrc())}>`;
  });
  const replacement = imageKey ? pickImages(path, imageKey, 1)[0] : want;
  if (!String(replacement).includes("a055da46974c") && !String(replacement).includes("aov-air-handling.jpg")) {
    out = out.replace(/(?:https?:\/\/[^"'\s]+)?\/assets\/images\/opt\/a055da46974c-\d+\.webp/gi, replacement);
    out = out.replace(/(?:https?:\/\/[^"'\s]+)?\/assets\/images\/services\/aov-air-handling(?:-(?:photo|context))?\.jpe?g/gi, replacement);
  }
  if (imageKey !== "aov") {
    out = out.replace(/\/assets\/images\/services\/(?:smoke-vent-roof-1|smoke-vent-hatch-1|smoke-vent-cover-1|smoke-shaft-1|aov-release-switch-1|aov-control-panel-1|aov-control-panel-2|aov-roof-hatch-1)\.jpe?g/gi, replacement);
  }
  if (!electricalPage && !String(imageKey).startsWith("page-")) {
    out = out.replace(/(?:https?:\/\/[^"'\s]+)?\/assets\/images\/opt\/21bb8660f87c-\d+\.webp/gi, replacement);
    out = out.replace(/\/assets\/images\/services\/electrical\.jpe?g/gi, replacement);
  }
  return out;
}

function fixPlaceArticle(html, place) {
  const name = String(place || "").trim();
  if (!/^[A-Za-z]/.test(name)) return html;
  const vowel = /^[aeiou]/i.test(name);
  const escaped = name.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  return String(html).replace(new RegExp(`\\b(a|an)\\s+(${escaped})\\b`, "gi"), (_, art, town) => {
    const want = vowel ? "an" : "a";
    const cased = art[0] === art[0].toUpperCase() ? want.charAt(0).toUpperCase() + want.slice(1) : want;
    return `${cased} ${town}`;
  });
}

function spaceVisiblePhone(html) {
  const held = [];
  const masked = String(html).replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, (block) => {
    held.push(block);
    return `\u0000PHONE${held.length - 1}\u0000`;
  });
  const parts = masked.split(/(href\s*=\s*(?:"[^"]*"|'[^']*'))/gi);
  const joined = parts.map((part, index) => (index % 2 === 1 ? part : part.replaceAll("07517806082", "07517 806082"))).join("");
  return joined.replace(/\u0000PHONE(\d+)\u0000/g, (_, index) => held[Number(index)] || "");
}

export function applyQualityFloor(html, ctx = {}) {
  let out = String(html || "");
  if (!/<html[\s>]/i.test(out) && !/<body[\s>]/i.test(out)) return out;
  const path = ctx.path || pagePath(out);
  const subject = ctx.subject || plainH1(out);
  const place = ctx.place || placeFromPath(path);
  if (!out.includes('data-quality-floor="1"')) {
    const srcBlock = photoHtml(path, subject);
    const needsCrumbs = !/itemtype="https:\/\/schema\.org\/BreadcrumbList"/i.test(out);
    const needsFaq = !/"@type"\s*:\s*"FAQPage"/i.test(out) && !/itemtype="https:\/\/schema\.org\/FAQPage"/i.test(out);
    const block = `<section data-quality-floor="1" id="local-essay">`
      + (needsCrumbs ? crumbBlock(path, subject) : "")
      + srcBlock
      + essayHtml(path, subject, place)
      + (needsFaq ? faqBlock(subject, place, path) : "")
      + `</section>`;
    const article = out.toLowerCase().lastIndexOf("</article>");
    if (article !== -1) out = out.slice(0, article) + block + out.slice(article);
    else {
      const main = out.toLowerCase().lastIndexOf("</main>");
      if (main !== -1) out = out.slice(0, main) + block + out.slice(main);
      else {
        const body = out.toLowerCase().lastIndexOf("</body>");
        if (body !== -1) out = out.slice(0, body) + block + out.slice(body);
        else out += block;
      }
    }
  } else if (!out.includes('data-pe-slot="real-photo"')) {
    const figure = photoHtml(path, subject);
    const body = out.toLowerCase().lastIndexOf("</body>");
    out = body === -1 ? out + figure : out.slice(0, body) + figure + out.slice(body);
  }
  out = sanitizeInternalLinks(out);
  out = confineImages(out, path);
  out = rewriteMetaImages(out, path);
  out = scrubSentences(out, path, subject);
  out = capTownPrefix(out, place);
  out = out.replace(/\bpOA\./g, "POA.");
  out = scrubFixedPricing(out);
  if (!/"@type"\s*:\s*"FAQPage"/i.test(out) && !/itemtype="https:\/\/schema\.org\/FAQPage"/i.test(out)) {
    const faq = faqBlock(subject, place, path);
    const body = out.toLowerCase().lastIndexOf("</body>");
    out = body === -1 ? out + faq : out.slice(0, body) + faq + out.slice(body);
  }
  let bookingSeen = 0;
  out = out.replace(/<p\b[^>]*>[^<]*To book, call or WhatsApp 07517 806082[^<]*<\/p>/gi, (paragraph) => {
    bookingSeen += 1;
    return bookingSeen === 1 ? paragraph : "";
  });
  let quoteSeen = 0;
  out = out.replace(/a free written quote after scope/gi, (line) => {
    quoteSeen += 1;
    return quoteSeen === 1 ? line : "the written price follows the survey";
  });
  out = out.replace(/[^.<>]{0,180}is not optional — it is how you stay audit-ready\.?/g, "The visit is scoped to the building, then written up for the file.");
  out = out.replace(/Bolton \(BL1[–-]BL7\) is terraced houses/g, "Bolton (BL1–BL7) has terraced houses");
  out = spaceVisiblePhone(out);
  out = fixPlaceArticle(out, place);
  out = dedupeRepeatedPhotos(out);
  out = stampCredits(out);
  let passes = 0;
  while (out.includes("&amp;amp;") && passes < 4) {
    out = out.replaceAll("&amp;amp;", "&amp;");
    passes += 1;
  }
  return out;
}
