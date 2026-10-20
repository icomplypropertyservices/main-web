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
// SEO PR145: the 15 new security keyword intents, on a 20-town spread across the 269-town
// security ring (incl. the two borough pages and the closest neighbour pairs), so
// check-town-words enforces >= 800 words and worst-pair <= 0.70 for each intent.
const SECURITY_TOWNS_SAMPLE = ["tameside", "trafford", "chapel-allerton", "flint", "leeds", "chorley", "hale", "timperley",
  "kearsley", "little-lever", "macclesfield", "prestwich", "brighouse", "rastrick", "ossett", "wakefield", "buckley", "mold",
  "morecambe", "prestatyn"];
const SECURITY_SPECS = ["emergency-cctv-repair", "emergency-access-control-repair", "emergency-alarm-call-out", "24-hour-cctv-engineer",
  "same-day-cctv-repair", "same-day-alarm-repair", "security-systems-near-me", "security-system-installation", "anpr-installation",
  "anpr-near-me", "access-control-emergency", "intruder-alarm-emergency", "door-entry-near-me", "cctv-installers-near-me",
  "alarm-installers-near-me"];
for (const slug of SECURITY_SPECS) {
  for (const town of SECURITY_TOWNS_SAMPLE) {
    const r = (p0KeepsTown(slug, town) && renderNationwideP0Town(slug, town)) || renderTownPage({ kind: "keyword", keyword: slug, town, catalogue, closeQPages });
    if (!r || !r.html) { console.error(`render-town-sample: no page for keyword ${slug}/${town}`); process.exitCode = 1; continue; }
    const file = `security__${slug}__${town}.html`;
    fs.writeFileSync(path.join(out, file), r.html);
    rows.push({ kind: "keyword", slug, town, file, robots: r.robots || "" });
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
// TOWN_SAMPLE_ONLY=security limits the index to the security keyword sample (PR145 branch, whose
// base predates the GM depth copy from #146/#147; the release branch runs the full index).
const only = process.env.TOWN_SAMPLE_ONLY || "";
const indexRows = only ? rows.filter((r) => r.file.startsWith(`${only}__`)) : rows;
fs.writeFileSync(path.join(out, "index.json"), JSON.stringify(indexRows));
console.log(`render-town-sample: ${rows.length} pages -> ${out}`);
