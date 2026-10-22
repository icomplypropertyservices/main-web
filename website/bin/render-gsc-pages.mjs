#!/usr/bin/env node
// GSC 21 Oct: render every upgrade-queue page and every restored keyword x town page
// (65) the way production serves it, into <out>/<path>.html plus <out>/index.tsv
// (path, family, status). Static pages are read from dist; keyword x town pages go
// through town-matrix + money-ctr. usage: node website/bin/render-gsc-pages.mjs [dist] [out]
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const out = path.resolve(process.argv[3] || "/tmp/gsc-render");
const tm = await import(path.join(repo, "netlify/edge-functions/town-matrix.js"));
const p0 = await import(path.join(repo, "netlify/lib/nationwide-p0.js"));
const { applyMoneyCtr } = await import(path.join(repo, "netlify/lib/money-ctr.js"));
const { withContactWidgets } = await import(path.join(repo, "netlify/lib/contact-widgets.js"));
const { scrubMeta } = await import(path.join(repo, "netlify/lib/scrub-meta.js"));
const rj = (f, d) => { try { return JSON.parse(fs.readFileSync(f, "utf8")); } catch { return d; } };
const m = (n) => rj(path.join(dist, "assets/matrix", `${n}.json`), {});
const catalogue = { keywords: m("keywords"), places: m("places"), services: m("services"), nationwide: m("nationwide"), manufacturers: m("manufacturers"), jobs: m("jobs") };
const closeQPages = (rj(path.join(repo, "website/data/close-q/town-prose.json"), {}) || {}).pages || {};
const KW = rj(path.join(repo, "website/data/keywords.json"), {});
const gsc = rj(path.join(repo, "website/data/gsc-upgrade-2026-10-21.json"), { pages: {} }).pages;
const paths = new Set(Object.keys(gsc));
for (const f of ["areas12.txt", "hub53.txt"]) for (const u of fs.readFileSync(path.join(repo, "scripts/gsc-2026-10-21", f), "utf8").split(/\s+/)) if (u) paths.add(new URL(u).pathname);
fs.rmSync(out, { recursive: true, force: true });
fs.mkdirSync(out, { recursive: true });
const rows = [];
for (const p of paths) {
  let html = null, status = 200;
  const kw = p.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (kw) {
    const to = tm.nonGmMatrixRedirect(p);
    if (to) { status = 301; }
    else {
      let r = p0.p0KeepsTown(kw[1], kw[2]) ? p0.renderNationwideP0Town(kw[1], kw[2]) : null;
      if (!r) r = tm.renderTownPage({ kind: "keyword", keyword: kw[1], town: kw[2], catalogue, closeQPages });
      if (r && r.status === 200) html = applyMoneyCtr(p, withContactWidgets(scrubMeta(r.html)));
      else status = 404;
    }
  } else {
    for (const f of [`${p}.php`, `${p}/index.html`, `${p}.html`]) {
      const full = path.join(dist, f);
      if (fs.existsSync(full)) { html = applyMoneyCtr(p, fs.readFileSync(full, "utf8").replace(/<section class="money-ctr"[^>]*data-brief="GSC-2026-10-21"[\s\S]*?<\/nav><\/section>|<section class="money-ctr"[^>]*data-brief="GSC-2026-10-21"[\s\S]*?<\/section>/, "")); break; }
    }
    if (!html) status = 404;
  }
  const slug = (p.match(/^\/pages\/keywords\/([a-z0-9-]+)/) || [])[1];
  const family = (slug && KW[slug] ? KW[slug].service : p.split("/")[2]) + (kw ? ":town" : ":hub");
  const file = p.replace(/^\//, "").replace(/\//g, "__") + ".html";
  if (html) fs.writeFileSync(path.join(out, file), html);
  rows.push([p, family, status, html ? file : ""].join("\t"));
}
fs.writeFileSync(path.join(out, "index.tsv"), rows.join("\n") + "\n");
console.log(`rendered ${rows.filter((r) => r.split("\t")[2] === "200").length}/${rows.length} pages into ${out}`);
