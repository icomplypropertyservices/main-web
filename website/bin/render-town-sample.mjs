// CLOSE-Q1 gate helper (2026-10-19): render a fixed sample of keyword, job and
// service x town pages with the edge renderer into <out>/ for
// check-town-words.py. Run after static-export (needs dist/assets/matrix).
// usage: node website/bin/render-town-sample.mjs [dist] [out]
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import { p0KeepsTown, renderNationwideP0Town } from "../../netlify/lib/nationwide-p0.js";
import { LOCAL_TOWN_SET } from "../../netlify/lib/dual-ring-allowlist.js";

const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = process.argv[2] || path.join(repo, "dist");
const out = process.argv[3] || "/tmp/town-sample";
const catalogue = {};
for (const n of ["keywords", "places", "services", "nationwide", "manufacturers", "jobs"]) {
  catalogue[n] = JSON.parse(fs.readFileSync(path.join(dist, "assets/matrix", `${n}.json`), "utf8"));
}
const tp = path.join(repo, "website/data/close-q/town-prose.json");
const closeQPages = fs.existsSync(tp) ? JSON.parse(fs.readFileSync(tp, "utf8")).pages || {} : {};
const TOWNS = ["manchester", "stockport", "bolton", "wigan", "oldham", "rochdale", "salford", "bury", "burnley", "trafford",
  "tameside", "altrincham", "sale", "stretford", "denton", "hyde", "leigh", "middleton", "heywood", "prestwich"];
const SPECS = [
  ["keyword", "eicr"], ["keyword", "gas-safety-certificate"], ["keyword", "access-control-near-me"],
  ["keyword", "aov-maintenance-contract"], ["keyword", "cctv-installation"], ["keyword", "fire-alarm-installer"],
  ["job", "eicr"], ["job", "gas-safety-cp12"], ["service", "cctv"], ["service", "electrical"],
];
fs.mkdirSync(out, { recursive: true });
const rows = [];
for (const [kind, slug] of SPECS) {
  for (const town of TOWNS) {
    let r = null;
    if (kind === "keyword") r = (p0KeepsTown(slug, town) && renderNationwideP0Town(slug, town)) || renderTownPage({ kind, keyword: slug, town, catalogue, closeQPages });
    else if (kind === "job") r = renderTownPage({ kind, job: slug, town, catalogue, closeQPages });
    else r = renderTownPage({ kind, service: slug, town, catalogue, closeQPages });
    if (!r || !r.html) continue;
    const file = `${kind}__${slug}__${town}.html`;
    fs.writeFileSync(path.join(out, file), r.html);
    rows.push({ kind, slug, town, file, robots: r.robots || "" });
  }
}
// Wide pass (no similarity): every dual-ring town for each group, plus one
// manufacturer, so doubled words, noindex and the word floor are checked on
// all 269 towns and every page type.
const brand = Object.keys(catalogue.manufacturers || {}).sort()[0];
const wideSpecs = [...SPECS, ...(brand ? [["manufacturer", brand]] : [])];
for (const [kind, slug] of wideSpecs) {
  for (const town of LOCAL_TOWN_SET) {
    if (kind !== "manufacturer" && TOWNS.includes(town)) continue;
    let r = null;
    if (kind === "keyword") r = (p0KeepsTown(slug, town) && renderNationwideP0Town(slug, town)) || renderTownPage({ kind, keyword: slug, town, catalogue, closeQPages });
    else if (kind === "job") r = renderTownPage({ kind, job: slug, town, catalogue, closeQPages });
    else if (kind === "manufacturer") r = renderTownPage({ kind, brand: slug, town, catalogue, closeQPages });
    else r = renderTownPage({ kind, service: slug, town, catalogue, closeQPages });
    if (!r || !r.html || r.status === 404) continue;
    const file = `wide__${kind}__${slug}__${town}.html`;
    fs.writeFileSync(path.join(out, file), r.html);
    rows.push({ kind, slug, town, file, robots: r.robots || "", wide: true, words: kind !== "manufacturer" });
  }
}
fs.writeFileSync(path.join(out, "index.json"), JSON.stringify(rows));
console.log(`render-town-sample: ${rows.length} pages -> ${out}`);
