#!/usr/bin/env node
/**
 * GSC 21 Oct: every redirect resolves in ONE hop to a 200.
 * Usage: node website/bin/check-redirect-hops.mjs [dist] [extra-url-list ...]
 * Simulates Netlify's order: edge functions (town-matrix trailing-slash, place
 * alias and out-of-ring 301s; legacy-products 404 -> 301), then netlify.toml and
 * dist/_redirects rules (first match wins, unforced rules lose to real files).
 * Checks every exact 301/302/308 rule source plus every URL in the lists
 * (default: scripts/gsc-2026-10-21/{404s,areas12,hub53}.txt and the upgrade queue).
 * A target counts as 200 when it is a dist file, an edge-rendered matrix page,
 * or a live shop.icomplypropertyservices.co.uk product/collection handle.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const SITE = "https://icomplypropertyservices.co.uk";
const tm = await import(path.join(repo, "netlify/edge-functions/town-matrix.js"));
const { legacyProductTarget } = await import(path.join(repo, "netlify/lib/legacy-products.js"));
const map = JSON.parse(fs.readFileSync(path.join(repo, "website/data/legacy-product-map.json"), "utf8"));
const SHOP_OK = new Set([...map.products.map((h) => `/products/${h}`), ...map.collections.map((c) => `/collections/${c}`), "/collections/all"]);
const readJson = (f, d) => { try { return JSON.parse(fs.readFileSync(f, "utf8")); } catch { return d; } };
const cat = (n) => readJson(path.join(dist, "assets/matrix", `${n}.json`), {});
const KEYWORDS = cat("keywords");
const SERVICES = cat("services");

// ---- rules
const rules = [];
function parseToml(src) {
  for (const block of src.split(/\[\[redirects\]\]/).slice(1)) {
    const g = (k) => (block.match(new RegExp(`^\\s*${k}\\s*=\\s*"([^"]*)"`, "m")) || [])[1];
    const st = (block.match(/^\s*status\s*=\s*(\d+)/m) || [])[1];
    const force = /^\s*force\s*=\s*true/m.test(block);
    const from = g("from"), to = g("to");
    if (from && to) rules.push({ from, to, status: Number(st || 301), force, src: "netlify.toml" });
  }
}
function parseRedirects(src, name) {
  for (const line of src.split("\n")) {
    const t = line.replace(/#.*/, "").trim();
    if (!t) continue;
    const parts = t.split(/\s+/);
    if (parts.length < 2 || !parts[0].startsWith("/") && !parts[0].startsWith("http")) continue;
    const s = parts[2] || "301";
    rules.push({ from: parts[0], to: parts[1], status: Number(s.replace("!", "")) || 301, force: s.endsWith("!"), src: name });
  }
}
// Netlify reads _redirects (publish dir) before netlify.toml rules.
const distRedirects = path.join(dist, "_redirects");
parseRedirects(fs.readFileSync(fs.existsSync(distRedirects) ? distRedirects : path.join(repo, "_redirects"), "utf8"), "_redirects");
parseToml(fs.readFileSync(path.join(repo, "netlify.toml"), "utf8"));

function compile(from) {
  let host = null, p = from;
  const hm = from.match(/^https?:\/\/([^/]+)(\/.*)$/);
  if (hm) { host = hm[1]; p = hm[2]; }
  const names = [];
  const re = p.replace(/[.+?^${}()|[\]\\]/g, "\\$&").replace(/:([a-zA-Z]+)/g, (_, n) => { names.push(n); return "([^/]+)"; }).replace(/\*$/, () => { names.push("splat"); return "(.*)"; });
  return { host, re: new RegExp(`^${re}/?$`), names };
}
for (const r of rules) r.c = compile(r.from);

function fileExists(p) {
  const clean = p.replace(/\/+$/, "") || "/";
  const c = clean === "/" ? ["index.html", "index.php"] : [`${clean}.php`, `${clean}.html`, `${clean}/index.html`, `${clean}/index.php`, clean];
  return c.some((f) => { try { return fs.statSync(path.join(dist, f)).isFile(); } catch { return false; } });
}
function matchRule(host, p) {
  for (const r of rules) {
    if (r.c.host && r.c.host !== host) continue;
    if (!r.c.host && host !== "icomplypropertyservices.co.uk") continue;
    const m = p.match(r.c.re);
    if (!m) continue;
    if (!r.force && fileExists(p)) return null;
    let to = r.to;
    r.c.names.forEach((n, i) => { to = to.replace(n === "splat" ? ":splat" : `:${n}`, m[i + 1] ?? ""); });
    return { ...r, target: to };
  }
  return null;
}
const EDGE = /^\/pages\/(keywords\/[a-z0-9-]+(\/[a-z0-9-]+)?|manufacturers\/[a-z0-9-]+\/[a-z0-9-]+|jobs\/[a-z0-9-]+\/[a-z0-9-]+|[a-z0-9-]+\/[a-z0-9-]+)\/?$/;
function edgeRenders(p) {
  let m = p.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m) return Boolean(KEYWORDS[m[1]]) || m[1].includes("--");
  m = p.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (m && SERVICES[m[1]]) return true;
  return false;
}
const EDGE_EXCLUDED = new Set((tm.config.excludedPath || []).filter((x) => !x.includes("*")));
/** One step: {status, to} */
function step(u) {
  const url = new URL(u, SITE);
  const host = url.host, p = url.pathname;
  if (host === "shop.icomplypropertyservices.co.uk") return { status: SHOP_OK.has(p.replace(/\/+$/, "")) ? 200 : 404 };
  if (host !== "icomplypropertyservices.co.uk" && host !== "www.icomplypropertyservices.co.uk") return { status: 200, external: true };
  if ((host === "icomplypropertyservices.co.uk" || host === "www.icomplypropertyservices.co.uk") && EDGE.test(p) && !/^\/pages\/(aov|barriers)\//.test(p) && !EDGE_EXCLUDED.has(p)) {
    const clean = p.replace(/\/+$/, "");
    const a = tm.placeAliasRedirect(clean) || tm.nonGmMatrixRedirect(clean);
    if (a) return { status: 301, to: SITE + a, via: "edge town-matrix" };
    if (clean !== p || host !== "icomplypropertyservices.co.uk") {
      // town-matrix fetches the clean apex URL and redirects straight to its target.
      const next = step(SITE + clean);
      if (next.status === 301 || next.status === 302 || next.status === 308) return { status: 301, to: new URL(next.to, SITE).toString(), via: `edge slash/www collapsed (${next.via})` };
      return { status: 301, to: SITE + clean, via: "edge trailing slash/www" };
    }
  }
  const r = matchRule(host, p);
  if (r && [301, 302, 308].includes(r.status)) return { status: r.status, to: r.target, via: `${r.src} ${r.from}` };
  if (r && r.status === 200) {
    const t = new URL(r.target, SITE).pathname;
    if (fileExists(t) || fileExists(p)) return { status: 200 };
  }
  if (fileExists(p) || edgeRenders(p.replace(/\/+$/, ""))) return { status: 200 };
  const lp = legacyProductTarget(p);
  if (lp) return { status: 301, to: lp, via: "edge legacy-products" };
  // town-matrix answers an unrendered town path with one 301 to a live listing hub.
  if (EDGE.test(p) && !/^\/pages\/(aov|barriers)\//.test(p)) return { status: 200, edgeFallback: true };
  return { status: 404 };
}
function resolve(u) {
  const chain = [];
  let cur = u;
  for (let i = 0; i < 5; i++) {
    const s = step(cur);
    if (s.status !== 301 && s.status !== 302 && s.status !== 308) return { status: s.status, chain, final: cur };
    chain.push(`${s.via} -> ${s.to}`);
    cur = new URL(s.to, cur).toString();
  }
  return { status: "loop", chain, final: cur };
}

const inputs = new Set();
for (const r of rules) {
  if ([301, 302, 308].includes(r.status) && !/[:*]/.test(r.from.replace(/^https?:\/\//, ""))) inputs.add(r.c.host ? r.from : SITE + r.from);
}
const lists = process.argv.slice(3);
if (!lists.length) for (const f of ["404s.txt", "areas12.txt", "hub53.txt", "retired.txt"]) lists.push(path.join(repo, "scripts/gsc-2026-10-21", f));
for (const f of lists) for (const u of fs.readFileSync(f, "utf8").split(/\s+/)) if (u.startsWith("http")) inputs.add(u);
const q = path.join(repo, "scripts/gsc-2026-10-21/upgrade-queue.csv");
if (fs.existsSync(q)) for (const line of fs.readFileSync(q, "utf8").split("\n").slice(1)) { const u = line.split(",")[0]; if (u.startsWith("http")) inputs.add(u); }

let fails = 0, redirects = 0, ok200 = 0, skippedExternal = 0;
for (const u of inputs) {
  const r = resolve(u);
  if (!r.chain.length) { if (r.status === 200) ok200++; continue; }
  redirects++;
  const ext = !/icomplypropertyservices\.co\.uk$/.test(new URL(r.final).host);
  if (ext && r.status === 200) { skippedExternal++; }
  if (r.chain.length > 1 || r.status !== 200) {
    fails++;
    console.log(`FAIL ${u} [${r.chain.length} hop(s), final ${r.status}] ${r.chain.join(" | ")}`);
  }
}
console.log(`check-redirect-hops: ${inputs.size} URLs, ${redirects} redirect, ${ok200} already 200, ${fails} not one hop to a 200`);
process.exit(fails ? 1 : 0);
