// Render every network-pack town page with the edge renderer into <out>/ (for check-network-pack.py).
// usage: node website/bin/render-network-pack.mjs [out]
import fs from "node:fs";
import path from "node:path";
import { renderNetworkPackTown, networkPackMatch } from "../../netlify/lib/network-pack.js";
import { renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import pack from "../../website/data/network-pack.json" with { type: "json" };

const out = process.argv[2] || "/tmp/network-pack";
fs.rmSync(out, { recursive: true, force: true });
fs.mkdirSync(out, { recursive: true });
const rows = [];
const p0k = new Set(JSON.parse(fs.readFileSync(new URL("../../website/data/network-pack-lists.json", import.meta.url))).p0_keywords);
const p0j = new Set(JSON.parse(fs.readFileSync(new URL("../../website/data/network-pack-lists.json", import.meta.url))).p0_jobs);
const specs = [];
for (const k of Object.keys(pack.keywords)) for (const t of pack.gm60) specs.push(["keyword", k, t, p0k.has(k)]);
for (const j of Object.keys(pack.jobs)) for (const t of pack.gm60) specs.push(["job", j, t, p0j.has(j)]);
for (const s of Object.keys(pack.services)) for (const t of pack.dual269) specs.push(["service", s, t, true]);
let viaMatrix = 0;
for (const [kind, slug, town, p0] of specs) {
  const r = renderNetworkPackTown({ kind, slug, town });
  // The shared renderer must hand the same pages to the pack (checks call renderTownPage).
  const m = renderTownPage({ kind, [kind === "keyword" ? "keyword" : kind === "job" ? "job" : "service"]: slug, town, catalogue: {} });
  if (m && m.html === r.html) viaMatrix++;
  const file = `${kind}__${slug}__${town}.html`;
  fs.writeFileSync(path.join(out, file), r.html);
  rows.push({ kind, slug, town, file, p0, path: r.path, canonical: r.canonical, robots: r.robots });
}
// Outside the town sets: must not render (edge 301s to the hub).
const outside = [["/pages/keywords/mesh-wifi-installation/accrington"], ["/pages/jobs/cat6-cabling/preston"], ["/pages/wifi/london"]]
  .map(([p]) => ({ path: p, ok: networkPackMatch(p)?.ok === true }));
fs.writeFileSync(path.join(out, "index.json"), JSON.stringify({ rows, viaMatrix, outside }));
console.log(`render-network-pack: ${rows.length} pages -> ${out} (renderTownPage parity ${viaMatrix}/${rows.length})`);
