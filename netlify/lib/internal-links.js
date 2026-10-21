/**
 * Rewrite internal hrefs onto URLs that are actually published.
 * /pages and /pages/{service} (with no town) go to the real service hub.
 * Town links stay only when that service publishes the town.
 */
import services from "../../website/data/services.json" with { type: "json" };
import fireAlarmFamily from "../../website/data/fire-alarm-installer-family.json" with { type: "json" };
import mainlandTownDoc from "../../website/data/uk-mainland-towns-10k.json" with { type: "json" };
import areasMainland from "../../website/data/areas-mainland.json" with { type: "json" };
import gscKeepDoc from "../../website/data/gsc-keep-towns.json" with { type: "json" };
import serveUpgradeDoc from "../../website/data/seo-upgrade/serve-200.json" with { type: "json" };
import { isGmTown, isLocalTown } from "./link-blocks.js";
import { p0KeepsTown } from "./nationwide-p0.js";

const GSC_KEEP = new Set(gscKeepDoc.paths || []);
const SERVE_UPGRADE = new Set(serveUpgradeDoc.paths || []);
const FIRE_P0 = new Set(fireAlarmFamily.p0 || []);
const MAINLAND = new Set((mainlandTownDoc.towns || []).map((town) => town && town.slug).filter(Boolean));
function areaSlug(name) {
  return String(name || "").toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
}
const AOV_BUILT = new Set((Array.isArray(areasMainland) ? areasMainland : []).map((row) => areaSlug(row && row.name)).filter(Boolean));

const NATIONWIDE = new Set([
  "aov", "barriers", "aov-air-handling",
  "fire-alarms", "emergency-lighting", "fire-risk-assessments", "fire-extinguishers",
  "fire-doors", "fire-stopping", "fire-suppression", "sprinkler-systems", "dry-risers",
  "fire-signage", "evacuation-alerts", "kitchen-fire-suppression", "fire-compartmentation",
  "smoke-co-alarms",
]);

const RESERVED = new Set([
  "keywords", "services", "areas", "manufacturers", "resources", "packages",
  "jobs", "commercial", "aov", "barriers", "aov-air-handling", "products",
  "shop", "emergency-lighting-jobs", "asbestos-jobs",
]);

/** Single-segment /pages/{slug} files that exist as their own pages. */
const TOP_PAGES = new Set([
  "about", "access-control-systems", "areas", "asbestos-jobs", "asbestos-landlords",
  "barriers", "care-homes", "commercial", "commercial-fire-safety", "directories",
  "electrical-safety-landlords", "emergency", "emergency-lighting-compliance",
  "emergency-lighting-jobs", "energy-performance-certificates", "ev-chargers", "faq",
  "fire-door-compliance", "fire-risk-assessment", "gas-safety-certificate", "jobs",
  "keywords", "landlord-certificates", "landlords", "legionella-landlords",
  "maintenance", "manchester-property-compliance", "manufacturers", "nurse-call-burnley",
  "nurse-call-manchester", "nurse-call-systems", "packages", "portable-appliance-testing",
  "pricing", "products", "resources", "reviews", "services", "shop", "site-map",
  "smoke-carbon-monoxide-alarms", "stockport-property-compliance", "water-wras",
  "contact", "renewals",
]);

export function rewriteInternalPath(pathname) {
  let path = String(pathname || "/");
  path = path.replace(/\.php$/i, "").replace(/\/index$/i, "");
  path = path.replace(/\/+$/, "") || "/";
  if (GSC_KEEP.has(path) || SERVE_UPGRADE.has(path)) return path;
  if (path === "/pages") return "/pages/services";

  let m = path.match(/^\/pages\/([a-z0-9-]+)$/);
  if (m) {
    const slug = m[1];
    if (!TOP_PAGES.has(slug) && !RESERVED.has(slug) && Object.prototype.hasOwnProperty.call(services, slug)) {
      return `/pages/services/${slug}`;
    }
    return path;
  }

  m = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) {
    if (isLocalTown(m[2]) || p0KeepsTown(m[1], m[2]) || (FIRE_P0.has(m[1]) && MAINLAND.has(m[2]))) return path;
    return `/pages/keywords/${m[1]}`;
  }
  m = path.match(/^\/pages\/manufacturers\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) return isGmTown(m[2]) ? path : `/pages/manufacturers/${m[1]}`;
  m = path.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) return isLocalTown(m[2]) ? path : `/pages/jobs/${m[1]}`;
  m = path.match(/^\/pages\/areas\/([a-z0-9-]+)$/);
  if (m) return isLocalTown(m[1]) ? path : "/pages/areas";
  m = path.match(/^\/pages\/aov-air-handling\/([a-z0-9-]+)$/);
  if (m) return AOV_BUILT.has(m[1]) ? path : "/pages/services/aov-air-handling";
  m = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && !RESERVED.has(m[1]) && !NATIONWIDE.has(m[1]) && !isLocalTown(m[2])) {
    return `/pages/services/${m[1]}`;
  }
  return path;
}

export function rewriteHref(href) {
  const raw = String(href || "");
  if (!raw || raw.startsWith("#") || /^mailto:/i.test(raw) || /^tel:/i.test(raw) || /^javascript:/i.test(raw)) {
    return href;
  }
  let origin = "";
  let path = raw;
  let suffix = "";
  if (/^https?:\/\//i.test(raw)) {
    let url;
    try { url = new URL(raw); } catch { return href; }
    if (!/(^|\.)icomplypropertyservices\.co\.uk$/i.test(url.hostname)) return href;
    origin = url.origin;
    path = url.pathname;
    suffix = url.search + url.hash;
  } else if (raw.startsWith("/")) {
    const cut = raw.search(/[?#]/);
    path = cut === -1 ? raw : raw.slice(0, cut);
    suffix = cut === -1 ? "" : raw.slice(cut);
  } else {
    return href;
  }
  const next = rewriteInternalPath(path);
  const norm = path.replace(/\/+$/, "") || "/";
  if (next === path || next === norm) return href;
  return origin + next + suffix;
}

export function sanitizeInternalLinks(html) {
  return String(html).replace(/href\s*=\s*(["'])([^"']*)\1/gi, (full, quote, href) => {
    const next = rewriteHref(href);
    if (next === href) return full;
    return `href=${quote}${next}${quote}`;
  });
}
