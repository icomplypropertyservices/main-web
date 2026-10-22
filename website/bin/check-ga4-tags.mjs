#!/usr/bin/env node
// Fail unless every built page AND a sample of edge-rendered pages carry BOTH GA4 IDs
// (G-RN048B7861 + G-G8NTX4JMC9) and exactly one gtag.js loader.
// usage: node website/bin/check-ga4-tags.mjs [dist] [edgeSample=400]
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const N = Number(process.argv[3] || 400);
const IDS = ["G-RN048B7861", "G-G8NTX4JMC9"];
const fails = [];
const ok = (html) => IDS.every((id) => html.includes(id)) && (html.match(/googletagmanager\.com\/gtag\/js\?id=/g) || []).length === 1;
const isPage = (html) => /<html[\s>]/i.test(html) && /<\/head>/i.test(html) && !/http-equiv="refresh"/i.test(html.slice(0, 2000));

// 1) static export
let pages = 0, skipped = 0;
const walk = (d) => { for (const e of fs.readdirSync(d, { withFileTypes: true })) {
  const p = path.join(d, e.name);
  if (e.isDirectory()) { if (!/^(assets|_|\.)/.test(e.name) || d !== dist) walk(p); continue; }
  if (!/\.(php|html?)$/.test(e.name)) continue;
  const h = fs.readFileSync(p, "utf8");
  if (!isPage(h)) { skipped++; continue; }
  pages++;
  if (!ok(h)) fails.push("static " + path.relative(dist, p));
} };
walk(dist);

// 2) edge renders through the real town-matrix handler, assets served from dist
const fileFor = (p) => [p, `${p}.php`, `${p}/index.html`, `${p}.html`].map((f) => path.join(dist, f)).find((f) => fs.existsSync(f) && fs.statSync(f).isFile());
globalThis.fetch = async (u) => { const f = fileFor(decodeURIComponent(new URL(String(u)).pathname)); return f ? new Response(fs.readFileSync(f)) : new Response("nf", { status: 404 }); };
const tm = (await import(path.join(repo, "netlify/edge-functions/town-matrix.js"))).default;
const urls = [];
for (const f of fs.readdirSync(dist).filter((f) => /^(sitemap|matrix-sitemap).*\.xml$/.test(f)))
  for (const m of fs.readFileSync(path.join(dist, f), "utf8").matchAll(/<loc>https?:\/\/[^/<]+(\/pages\/[^<]+)<\/loc>/g)) urls.push(m[1]);
const fixed = ["/pages/keywords/eicr/stockport", "/pages/keywords/aov-maintenance/stockport", "/pages/electrical/bolton", "/pages/manufacturers/came/stockport", "/pages/jobs/car-park-barrier-repair/manchester", "/pages/demolition/bolton", "/pages/aov/manchester"];
const pick = [...fixed, ...urls.sort(() => Math.random() - 0.5).slice(0, N)];
let edge = 0, edgeStatic = 0, edgeOther = 0;
for (const p of pick) {
  let served = "edge";
  const ctx = { next: async () => { served = "static"; const f = fileFor(p); return f ? new Response(fs.readFileSync(f), { headers: { "content-type": "text/html" } }) : new Response("nf", { status: 404 }); } };
  let r; try { r = await tm(new Request("https://icomplypropertyservices.co.uk" + p), ctx); } catch (e) { fails.push(`edge-error ${p} ${e.message}`); continue; }
  if (r.status !== 200 || !(r.headers.get("content-type") || "").includes("html")) { edgeOther++; continue; }
  const h = await r.text();
  if (served === "static") edgeStatic++; else edge++;
  if (!ok(h)) fails.push(`edge(${served}) ${p}`);
}
for (const f of [...fails.filter((x) => !x.startsWith("static")), ...fails.filter((x) => x.startsWith("static"))].slice(0, 30)) console.log("FAIL", f);
console.log(`check-ga4-tags: ${fails.length ? "FAIL" : "OK"} static_pages=${pages} (non-page/redirect files skipped=${skipped}) edge_rendered=${edge} edge_passthrough_static=${edgeStatic} edge_non_html_or_redirect=${edgeOther} fails=${fails.length}`);
process.exit(fails.length ? 1 : 0);
