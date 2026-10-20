#!/usr/bin/env node
// Service pages only show photos from their own service family (Jack 20 Oct: no CCTV photo on an EICR page).
// Families: website/data/service-image-families.json.
// 1) every exported page with a quality-bar figure: all its photos share the hero photo's family;
// 2) /pages/services/<slug>: every /assets/images/services/* photo on the page is in <slug>'s family;
// 3) edge-rendered x-town pages: every keyword / service / job in the catalogue for 3 towns, same rule as (1).
// Usage: node website/bin/check-service-images.mjs dist
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import { p0KeepsTown, renderNationwideP0Town } from "../../netlify/lib/nationwide-p0.js";

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const fams = JSON.parse(fs.readFileSync(path.join(repo, "website/data/service-image-families.json"), "utf8")).families;
const famOf = (b) => Object.keys(fams).find((k) => fams[k].includes(b)) || null;
const IMG = /\/assets\/images\/services\/([a-z0-9-]+)\.(?:jpg|png|webp)/g;
let fail = 0, pages = 0;
const bad = (m) => { if (fail < 40) console.error("FAIL " + m); fail++; };

function figureRule(where, html) {
  for (const fig of html.match(/<figure class="quality-bar-images"[\s\S]*?<\/figure>/g) || []) {
    const bases = [...fig.matchAll(IMG)].map((m) => m[1]);
    if (!bases.length) continue;
    const want = famOf(bases[0]);
    if (!want) continue;
    for (const b of bases.slice(1)) if (famOf(b) !== want) bad(`${where}: '${b}' (${famOf(b)}) on a ${bases[0]} (${want}) page`);
  }
}

function walk(dir, out = []) {
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, e.name);
    if (e.isDirectory()) walk(p, out);
    else if (/\.(php|html)$/.test(e.name)) out.push(p);
  }
  return out;
}
for (const f of walk(path.join(dist, "pages"))) {
  const html = fs.readFileSync(f, "utf8");
  pages++;
  const rel = "/" + path.relative(dist, f).replace(/\.(php|html)$/, "");
  // Area hubs (/pages/areas/<town>) cover every service, so a mixed strip is right there.
  if (!/^\/pages\/areas\/[a-z0-9-]+$/.test(rel)) figureRule(rel, html);
  const m = rel.match(/^\/pages\/services\/([a-z0-9-]+)$/);
  if (m && famOf(m[1])) {
    const want = famOf(m[1]);
    const main = (html.split(/<main\b/)[1] || html).split(/<footer\b/)[0];
    for (const x of main.matchAll(IMG)) if (famOf(x[1]) && famOf(x[1]) !== want) bad(`${rel}: '${x[1]}' (${famOf(x[1])}) on a ${want} service page`);
  }
}

const catalogue = {};
for (const n of ["keywords", "places", "services", "nationwide", "manufacturers", "jobs"]) {
  catalogue[n] = JSON.parse(fs.readFileSync(path.join(dist, "assets/matrix", `${n}.json`), "utf8"));
}
const tp = path.join(repo, "website/data/close-q/town-prose.json");
const closeQPages = fs.existsSync(tp) ? JSON.parse(fs.readFileSync(tp, "utf8")).pages || {} : {};
const slugsOf = (v) => (Array.isArray(v) ? v : Object.keys(v || {})).map((x) => (typeof x === "string" ? x : x.slug)).filter(Boolean);
let edge = 0;
for (const [kind, key, list] of [["keyword", "keyword", catalogue.keywords], ["service", "service", catalogue.services], ["job", "job", catalogue.jobs]]) {
  for (const slug of slugsOf(list)) {
    for (const town of ["bolton", "waddington", "burnley"]) {
      let r = null;
      try {
        r = kind === "keyword"
          ? (p0KeepsTown(slug, town) && renderNationwideP0Town(slug, town)) || renderTownPage({ kind, keyword: slug, town, catalogue, closeQPages })
          : renderTownPage({ kind, [key]: slug, town, catalogue, closeQPages });
      } catch { r = null; }
      const html = typeof r === "string" ? r : r?.html || r?.body || "";
      if (!html) continue;
      edge++;
      figureRule(`/pages/${kind}s/${slug}/${town} (edge)`, html);
    }
  }
}
if (fail) { console.error(`check-service-images FAILED (${fail}) pages=${pages} edge=${edge}`); process.exit(1); }
console.log(`check-service-images OK (${pages} exported pages, ${edge} edge renders)`);
