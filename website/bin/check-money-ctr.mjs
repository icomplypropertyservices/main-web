#!/usr/bin/env node
/**
 * P0-CTR-MONEY gate (SEO brief 2026-10-19, C1-C15).
 * Usage: node website/bin/check-money-ctr.mjs [dist]
 * Asserts for every page in website/data/money-ctr-c1-c15.json:
 *  - exact title (<=60) and meta (<=155), also og/twitter title+description.
 *    Titles are the brief strings, except Same/Next-Day is kept where the page
 *    carried it on main (Jack 19 Oct; flagged sameNextDay in the data file).
 *  - no "£" in related-link labels other than EICR "from £160"
 *  - robots index,follow and absolute https self-canonical
 *  - listed in the published sitemap (dist/sitemap*.xml)
 *  - x town pages: >=800 prose words and >=80 unique internal links
 *  - no info@ address and no "Gas Safe" wording other than
 *    "carried out by Gas Safe registered engineers" in the authored block
 * Static pages are read from dist; edge-rendered x town pages are rendered
 * with netlify/edge-functions/town-matrix.js against dist/assets/matrix.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const SITE = "https://icomplypropertyservices.co.uk";
const doc = JSON.parse(fs.readFileSync(path.join(repo, "website/data/money-ctr-c1-c15.json"), "utf8"));
const tm = await import(path.join(repo, "netlify/edge-functions/town-matrix.js"));
const p0 = await import(path.join(repo, "netlify/lib/nationwide-p0.js"));
const { applyMoneyCtr } = await import(path.join(repo, "netlify/lib/money-ctr.js"));

const readJson = (f, d) => { try { return JSON.parse(fs.readFileSync(f, "utf8")); } catch { return d; } };
const m = (n) => readJson(path.join(dist, "assets/matrix", `${n}.json`), {});
const catalogue = { keywords: m("keywords"), places: m("places"), services: m("services"), nationwide: m("nationwide"), manufacturers: m("manufacturers"), jobs: m("jobs") };
const closeQPages = (readJson(path.join(dist, "assets/close-q/town-prose.json"), {}) || {}).pages || {};

const locs = new Map();
const addLoc = (u) => locs.set(u, (locs.get(u) || 0) + 1);
for (const f of fs.readdirSync(dist)) {
  if (!/^sitemap.*\.xml$/.test(f)) continue;
  for (const mm of fs.readFileSync(path.join(dist, f), "utf8").matchAll(/<loc>([^<]+)<\/loc>/g)) addLoc(mm[1].trim());
}
const sitemapDir = path.join(dist, "sitemaps");
if (fs.existsSync(sitemapDir)) for (const f of fs.readdirSync(sitemapDir)) {
  if (!f.endsWith(".xml")) continue;
  for (const mm of fs.readFileSync(path.join(sitemapDir, f), "utf8").matchAll(/<loc>([^<]+)<\/loc>/g)) addLoc(mm[1].trim());
}

const NATIONWIDE = /^\/pages\/(aov|barriers|aov-air-handling|fire-alarms|emergency-lighting|fire-risk-assessments|fire-extinguishers|fire-doors|fire-stopping|fire-suppression|sprinkler-systems|dry-risers)\/[a-z0-9-]+$/;
function staticHtml(p) {
  for (const f of [`${p}.php`, `${p}/index.html`, `${p}.html`]) {
    const full = path.join(dist, f);
    if (fs.existsSync(full)) return fs.readFileSync(full, "utf8");
  }
  return null;
}
function pageHtml(p) {
  const kw = p.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (kw && !NATIONWIDE.test(p)) {
    if (tm.nonGmMatrixRedirect(p)) return { err: `edge 301 → ${tm.nonGmMatrixRedirect(p)}` };
    let r = p0.p0KeepsTown(kw[1], kw[2]) ? p0.renderNationwideP0Town(kw[1], kw[2]) : null;
    if (!r) r = tm.renderTownPage({ kind: "keyword", keyword: kw[1], town: kw[2], catalogue, closeQPages });
    if (!r || r.status !== 200) return { err: "edge render failed" };
    return { html: applyMoneyCtr(p, r.html) };
  }
  const html = staticHtml(p);
  return html ? { html } : { err: "not exported" };
}
const dec = (s) => String(s).replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&pound;/g, "£");
function grab(html, re) { const mm = html.match(re); return mm ? dec(mm[1]) : null; }
function proseWords(html) {
  let main = (html.match(/<main[\s\S]*<\/main>/i) || [html])[0];
  main = main.replace(/<script[\s\S]*?<\/script>/gi, " ").replace(/<style[\s\S]*?<\/style>/gi, " ").replace(/<nav[\s\S]*?<\/nav>/gi, " ").replace(/<footer[\s\S]*?<\/footer>/gi, " ");
  const chunks = main.match(/<(p|summary|h[1-3])\b[^>]*>[\s\S]*?<\/\1>/gi) || [];
  return chunks.map((c) => dec(c.replace(/<[^>]+>/g, " "))).join(" ").split(/\s+/).filter((w) => /[A-Za-z0-9£]/.test(w)).length;
}
function internalLinks(html) {
  const out = new Set();
  for (const mm of html.matchAll(/href="([^"]+)"/gi)) {
    let h = dec(mm[1]);
    if (h.startsWith(SITE)) h = h.slice(SITE.length) || "/";
    if (!h.startsWith("/") || h.startsWith("//")) continue;
    h = h.split("#")[0].split("?")[0];
    if (!h || h.startsWith("/assets/") || /\.(css|js|png|jpe?g|webp|svg|ico|xml|json|webmanifest)$/i.test(h)) continue;
    out.add(h.length > 1 ? h.replace(/\/+$/, "") : h);
  }
  return out;
}

let fails = 0;
const fail = (p, msg) => { fails++; console.log(`FAIL ${p}: ${msg}`); };
const rows = [];
for (const [p, page] of Object.entries(doc.pages || {})) {
  if (page.title.length > 60) fail(p, `data title ${page.title.length} > 60`);
  if (page.meta.length > 155) fail(p, `data meta ${page.meta.length} > 155`);
  const got = pageHtml(p);
  if (!got.html) { fail(p, got.err); continue; }
  const html = got.html;
  const title = grab(html, /<title>([^<]*)<\/title>/i);
  const meta = grab(html, /<meta\s+name="description"\s+content="([^"]*)"/i);
  const ogt = grab(html, /<meta\s+property="og:title"\s+content="([^"]*)"/i);
  const ogd = grab(html, /<meta\s+property="og:description"\s+content="([^"]*)"/i);
  const robots = grab(html, /<meta\s+name="robots"\s+content="([^"]*)"/i) || "";
  const canonical = grab(html, /<link\s+rel="canonical"\s+href="([^"]*)"/i);
  if ((html.match(/<title>/gi) || []).length !== 1) fail(p, "title tag count != 1");
  if (title !== page.title) fail(p, `title "${title}" != brief "${page.title}"`);
  if (meta !== page.meta) fail(p, `meta "${meta}" != brief`);
  if (ogt !== null && ogt !== page.title) fail(p, "og:title not brief title");
  if (ogd !== null && ogd !== page.meta) fail(p, "og:description not brief meta");
  const twt = grab(html, /<meta\s+name="twitter:title"\s+content="([^"]*)"/i);
  const twd = grab(html, /<meta\s+name="twitter:description"\s+content="([^"]*)"/i);
  if (twt !== null && twt !== page.title) fail(p, "twitter:title not page title");
  if (twd !== null && twd !== page.meta) fail(p, "twitter:description not page meta");
  if (!/Same\/Next-Day/.test(title || "")) fail(p, "Same/Next-Day missing from title (Jack 19 Oct)");
  // C10 rule (SEO scorecard 2026-10-20): a page whose own service has a list price shown in the body
  // keeps "from £X" in title + meta, never POA alone (G9).
  {
    const own = /fire-risk-assessment/.test(p) ? "£350" : (/eicr/.test(p) ? "£160" : "");
    const bodyText = html.replace(/<head[\s\S]*?<\/head>/i, "");
    if (own && bodyText.includes(own)) {
      if (!new RegExp(`from ${own}`, "i").test(title || "")) fail(p, `body shows ${own} but title lacks "from ${own}"`);
      if (!new RegExp(`from ${own}`, "i").test(meta || "")) fail(p, `body shows ${own} but meta lacks "from ${own}"`);
    }
  }
  for (const [, label] of page.links || []) if (/£(?!160)/.test(label)) fail(p, `price in link label "${label}"`);
  if (!/^index,\s*follow/i.test(robots)) fail(p, `robots "${robots}"`);
  if (canonical !== `${SITE}${p}`) fail(p, `canonical "${canonical}"`);
  const n = locs.get(`${SITE}${p}`) || 0;
  if (!n) fail(p, "missing from sitemap");
  if (n > 1) fail(p, `listed ${n} times across sitemap shards`);
  const block = (html.match(/<section class="money-ctr"[^>]*>[\s\S]*?<\/section>/) || [""])[0];
  if (!block) fail(p, "money-ctr block missing");
  if (/info@/i.test(block)) fail(p, "info@ in money-ctr block");
  for (const g of block.match(/[^.]*Gas Safe[^.]*/g) || []) if (!/carried out by Gas Safe registered engineers/i.test(g)) fail(p, `gas wording: ${g.trim()}`);
  const words = proseWords(html);
  const links = internalLinks(html).size;
  if (page.kind === "xtown") {
    if (words < 800) fail(p, `prose words ${words} < 800`);
    if (links < 80) fail(p, `unique internal links ${links} < 80`);
  }
  rows.push(`${page.brief}\t${p}\ttitle=${title.length}\tmeta=${meta.length}\twords=${words}\tlinks=${links}\t${robots}`);
}
console.log(rows.join("\n"));
if (fails) { console.log(`check-money-ctr: ${fails} failure(s)`); process.exit(1); }
console.log(`check-money-ctr OK (${rows.length} pages)`);
