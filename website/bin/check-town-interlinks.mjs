// CLOSE-Q8 gate (2026-10-14): thin x town pages (keyword, job, service and
// nationwide P0 keyword x town) must carry >= 80 unique internal links.
// Renders a deterministic sample with the edge renderer using the catalogue
// written by static-export (dist/assets/matrix). Run after static-export.
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import { p0KeepsTown, renderNationwideP0Town } from "../../netlify/lib/nationwide-p0.js";
import { isLocalTown } from "../../netlify/lib/link-blocks.js";

const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = process.argv[2] || path.join(repo, "dist");
const MIN = 80;
const catalogue = {};
for (const name of ["keywords", "places", "services", "nationwide", "manufacturers", "jobs"]) {
  catalogue[name] = JSON.parse(fs.readFileSync(path.join(dist, "assets/matrix", `${name}.json`), "utf8"));
}
let closeQPages = {};
for (const f of [path.join(repo, "website/data/close-q/town-prose.json"), path.join(dist, "assets/close-q/town-prose.json")]) {
  if (fs.existsSync(f)) { closeQPages = JSON.parse(fs.readFileSync(f, "utf8")).pages || {}; break; }
}

function uniqueInternal(html) {
  const out = new Set();
  for (const m of html.matchAll(/<a\b[^>]*\bhref="([^"]+)"/gi)) {
    let href = m[1].replace(/&amp;/g, "&");
    if (/^(#|mailto:|tel:|javascript:)/i.test(href)) continue;
    let u;
    try { u = new URL(href, "https://icomplypropertyservices.co.uk/"); } catch { continue; }
    if (!u.hostname.endsWith("icomplypropertyservices.co.uk")) continue;
    out.add((u.pathname.replace(/\/+$/, "") || "/").replace(/\.php$/, ""));
  }
  return out.size;
}

const TOWNS = ["manchester", "stockport", "bolton", "wigan", "oldham", "rochdale", "salford", "bury", "burnley", "preston"];
const pick = (arr, n) => arr.filter((_, i) => i % Math.max(1, Math.floor(arr.length / n)) === 0).slice(0, n);
const samples = [];
const kwSlugs = Object.keys(catalogue.keywords || {});
for (const [i, k] of pick(kwSlugs, 20).entries()) samples.push(["keyword", k, TOWNS[i % TOWNS.length]]);
for (const [i, j] of Object.keys(catalogue.jobs || {}).entries()) samples.push(["job", j, TOWNS[i % TOWNS.length]]);
const svcLabels = (catalogue.services && catalogue.services.labels) || {};
for (const [i, s] of pick(Object.keys(svcLabels).filter((s) => !["aov", "barriers", "aov-air-handling"].includes(s)), 16).entries()) samples.push(["service", s, TOWNS[i % TOWNS.length]]);
for (const [k, t] of [["fire-alarm-installation", "manchester"], ["aov-installation", "leeds"], ["barrier-installation", "york"], ["fire-risk-assessment", "york"], ["fire-door-installer", "liverpool"], ["smoke-vent-installer", "preston"]]) samples.push(["p0", k, t]);

let fail = 0;
let checked = 0;
for (const [kind, slug, town] of samples) {
  let r = null;
  if (kind === "p0") r = p0KeepsTown(slug, town) ? renderNationwideP0Town(slug, town) : null;
  else if (kind === "keyword") r = p0KeepsTown(slug, town) ? renderNationwideP0Town(slug, town) : renderTownPage({ kind, keyword: slug, town, catalogue, closeQPages });
  else if (kind === "job") r = renderTownPage({ kind, job: slug, town, catalogue, closeQPages });
  else r = isLocalTown(town) ? renderTownPage({ kind, service: slug, town, catalogue, closeQPages }) : null;
  if (!r || !r.html || r.status === 404) continue;
  if (String(r.robots || "").includes("noindex")) continue;
  checked++;
  const n = uniqueInternal(r.html);
  const label = `${kind} ${slug}/${town}`;
  if (n < MIN) { fail++; console.log(`FAIL ${n} < ${MIN} ${label}`); }
  else console.log(`ok ${n} ${label}`);
}
if (checked < 20) { console.log(`FAIL only ${checked} pages rendered (need >= 20)`); fail++; }
console.log(fail ? `check-town-interlinks: ${fail} failure(s)` : `check-town-interlinks: OK (${checked} pages >= ${MIN})`);
process.exit(fail ? 1 : 0);
