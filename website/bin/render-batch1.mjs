// Render every Batch 1 town page (building service × GM-60, P0 building keyword × GM-60) to a folder for checks.
// Usage: node website/bin/render-batch1.mjs <outdir>
import fs from "node:fs";
import path from "node:path";
import { batch1Paths, batch1Match, renderBatch1 } from "../../netlify/lib/building-batch1.js";
import { withContactWidgets } from "../../netlify/lib/contact-widgets.js";
import { scrubMeta } from "../../netlify/lib/scrub-meta.js";
const out = process.argv[2] || "/tmp/batch1";
fs.rmSync(out, { recursive: true, force: true });
let n = 0;
for (const p of batch1Paths()) {
  const r = renderBatch1(batch1Match(p));
  const f = path.join(out, p + ".html");
  fs.mkdirSync(path.dirname(f), { recursive: true });
  fs.writeFileSync(f, withContactWidgets(scrubMeta(r.html)));
  n++;
}
console.log(`render-batch1: ${n} pages -> ${out}`);
