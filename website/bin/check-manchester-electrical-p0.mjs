import { readFileSync } from "fs";
import { renderManchesterElectricalFromSpec } from "../../netlify/lib/manchester-electrical-p0.js";

const spec = JSON.parse(readFileSync(new URL("../assets/matrix/manchester-electrical-p0.json", import.meta.url), "utf8"));
let fail = 0;
let pages = 0;
for (const intent of spec.intents) {
  const surfaces = intent.kind === "keyword" ? ["keyword"] : ["keyword", "job"];
  const towns = ["", ...spec.towns.map((town) => town.slug)];
  for (const surface of surfaces) {
    for (const town of towns) {
      const page = renderManchesterElectricalFromSpec(spec, { surface, slug: intent.slug, town });
      pages += 1;
      const images = (page.html.match(/<img /g) || []).length;
      const bad = !page
        || page.words < 800
        || page.description.length < 140
        || page.description.length > 160
        || images < 3
        || !page.html.includes(`rel="canonical" href="${page.canonical}"`)
        || !page.html.includes('property="og:title"')
        || !page.html.includes('property="og:image"')
        || page.html.toLowerCase().includes("gas safe")
        || page.html.toLowerCase().includes("approved subcontractor")
        || (intent.slug !== "eicr" && page.html.includes("£"))
        || (intent.slug === "nic-electrician" && !/does not claim niceic/i.test(page.html));
      if (bad) {
        fail += 1;
        if (fail < 8) console.log("[FAIL]", page && page.path, page && page.words, page && page.description.length);
      }
    }
  }
}
if (pages !== 10309) {
  fail += 1;
  console.log("[FAIL] pages", pages);
}
console.log(fail === 0 ? `PASS pages=${pages}` : `FAIL ${fail}`);
process.exit(fail === 0 ? 0 : 1);
