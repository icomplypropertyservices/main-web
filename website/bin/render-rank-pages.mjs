#!/usr/bin/env node
// GSC rankings 22 Oct: render every upgraded page and every inbound hub the way production
// serves it. Static pages come from dist with older money-ctr blocks stripped and re-applied;
// keyword x town pages go through town-matrix + page-finish + money-ctr.
// usage: node website/bin/render-rank-pages.mjs [dist] [out]
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const out = path.resolve(process.argv[3] || "/tmp/rank-render");
const tm = await import(path.join(repo, "netlify/edge-functions/town-matrix.js"));
const p0 = await import(path.join(repo, "netlify/lib/nationwide-p0.js"));
const { applyMoneyCtr } = await import(path.join(repo, "netlify/lib/money-ctr.js"));
const { withContactWidgets } = await import(path.join(repo, "netlify/lib/contact-widgets.js"));
const { scrubMeta } = await import(path.join(repo, "netlify/lib/scrub-meta.js"));
let finish = (h) => h;
try { const pf = await import(path.join(repo, "netlify/lib/page-finish.js")); finish = pf.finishPage || pf.pageFinish || pf.default || finish; } catch {}
const rj = (f, d) => { try { return JSON.parse(fs.readFileSync(f, "utf8")); } catch { return d; } };
const m = (n) => rj(path.join(dist, "assets/matrix", `${n}.json`), {});
const catalogue = { keywords: m("keywords"), places: m("places"), services: m("services"), nationwide: m("nationwide"), manufacturers: m("manufacturers"), jobs: m("jobs") };
const closeQPages = (rj(path.join(repo, "website/data/close-q/town-prose.json"), {}) || {}).pages || {};
const doc = rj(path.join(repo, "website/data/gsc-upgrade-2026-10-22.json"), { pages: {}, inbound: {} });
const paths = new Set([...Object.keys(doc.pages), ...Object.keys(doc.inbound)]);
const strip = (h) => h.replace(/<section class="money-ctr"[^>]*data-pe-slot="money-ctr"[\s\S]*?<\/section>/g, "").replace(/<nav class="money-ctr-links" data-pe-slot="rank-inbound"[\s\S]*?<\/nav>/g, "");
fs.rmSync(out, { recursive: true, force: true });
fs.mkdirSync(out, { recursive: true });
const rows = [];
for (const p of paths) {
  let html = null, status = 200;
  const kw = p.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (kw) {
    if (tm.nonGmMatrixRedirect(p)) status = 301;
    else {
      let r = p0.p0KeepsTown(kw[1], kw[2]) ? p0.renderNationwideP0Town(kw[1], kw[2]) : null;
      if (!r) r = tm.renderTownPage({ kind: "keyword", keyword: kw[1], town: kw[2], catalogue, closeQPages });
      if (r && r.status === 200) html = applyMoneyCtr(p, withContactWidgets(finish(scrubMeta(r.html), p)));
      else status = 404;
    }
  } else {
    for (const f of [`${p}.php`, `${p}/index.html`, `${p}.html`]) {
      const full = path.join(dist, f);
      if (fs.existsSync(full)) { html = applyMoneyCtr(p, strip(fs.readFileSync(full, "utf8"))); break; }
    }
    if (!html) status = 404;
  }
  const file = p.replace(/^\//, "").replace(/\//g, "__") + ".html";
  if (html) fs.writeFileSync(path.join(out, file), html);
  rows.push([p, doc.pages[p] ? "page" : "hub", status, html ? file : ""].join("\t"));
}
fs.writeFileSync(path.join(out, "index.tsv"), rows.join("\n") + "\n");
console.log(`rendered ${rows.filter((r) => r.split("\t")[2] === "200").length}/${rows.length} into ${out}`);
