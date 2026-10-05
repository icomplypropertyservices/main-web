/**
 * Edge renderer checks for the 10,000-variant matrix.
 * Usage: node website/bin/check-keyword-variants.mjs
 */
import fs from "node:fs";
import { scoreHtml } from "./score-pages.mjs";
import {
  decodeVariant,
  encodeVariant,
  fitTitle,
  parseVariantKeyword,
  renderVariantPage,
  renderVariantSitemap,
  variantPath,
  variantSlug,
  variantTotalUrls,
} from "../../netlify/lib/variant-matrix.js";

const GAS_SENTENCE_GUARD = "Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers.";
const spec = JSON.parse(fs.readFileSync("/tmp/icomply-variant-spec.json", "utf8"));
const samples = JSON.parse(fs.readFileSync("/tmp/icomply-variant-samples.json", "utf8"));
let fail = 0;
const ok = (cond, message) => {
  if (!cond) {
    fail += 1;
    console.log(`FAIL: ${message}`);
  }
};

ok(variantTotalUrls(spec) === samples.total, `total ${variantTotalUrls(spec)} != ${samples.total}`);
for (const [global, path] of Object.entries(samples.paths)) {
  ok(variantPath(spec, Number(global)) === path, `path ${global} ${variantPath(spec, Number(global))} != ${path}`);
}

const electrical = spec.services.find((row) => row.slug === "electrical");
const decoded0 = decodeVariant(electrical, spec, 0);
ok(variantSlug("electrical", decoded0) === samples.electrical0, "electrical index 0 slug");

const rewireIndex = electrical.stems.findIndex((row) => row.slug === "rewire");
const cheapIndex = spec.modifiers.findIndex((row) => row.slug === "cheap");
const rewireCheap = encodeVariant(electrical, spec, rewireIndex, cheapIndex, 0, 0, 0);
const cheapParsed = decodeVariant(electrical, spec, rewireCheap);
const cheapSlug = variantSlug("electrical", cheapParsed);
ok(parseVariantKeyword(spec, cheapSlug).inSet, "rewire cheap is inside the 10000");

const stockport = renderVariantPage({ spec, keyword: cheapSlug, town: "stockport" });
const manchester = renderVariantPage({ spec, keyword: cheapSlug, town: "manchester" });
const bolton = renderVariantPage({ spec, keyword: cheapSlug, town: "bolton" });
const hub = renderVariantPage({ spec, keyword: cheapSlug, town: "" });
const burnley = renderVariantPage({ spec, keyword: cheapSlug, town: "burnley" });
const invalid = renderVariantPage({ spec, keyword: "electrical--not-a-real-stem--landlord--emergency--install--quote", town: "stockport" });

ok(stockport.status === 200 && stockport.indexable && stockport.robots === "index, follow", "stockport indexable");
ok(hub.indexable && hub.canonical.endsWith(`/pages/keywords/${cheapSlug}`), "hub canonical");
ok(stockport.canonical === `https://icomplypropertyservices.co.uk/pages/keywords/${cheapSlug}/stockport`, "town canonical");
ok(!burnley.indexable && burnley.status === 200 && burnley.robots.startsWith("noindex"), "burnley noindex 200");
ok(!invalid.indexable && invalid.status === 200, "unknown variant 200 noindex");
ok(stockport.html.includes("does not publish a low rate"), "cheap copy");
ok(stockport.html.includes("price on application"), "POA");
ok(!stockport.html.includes("£"), "no pound sign");
ok(!/NICEIC/i.test(stockport.html), "no NICEIC");
ok(!/does not carry out gas/i.test(stockport.html), "no gas denial");
ok(!stockport.html.includes(GAS_SENTENCE_GUARD), "electrical cheap page has no gas sentence");

const gasService = spec.services.find((row) => row.slug === "gas-systems");
const gasDecoded = decodeVariant(gasService, spec, 0);
const gasSlug = variantSlug("gas-systems", gasDecoded);
const gasPage = renderVariantPage({ spec, keyword: gasSlug, town: "bolton" });
ok(gasPage.html.includes("Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers."), "gas sentence");
ok(gasPage.html.includes("iComply is not Gas Safe registered."), "not Gas Safe registered");
ok(!/iComply is Gas Safe registered\./.test(gasPage.html.replace("iComply is not Gas Safe registered.", "")), "no positive Gas Safe claim");

function shingles(html) {
  const text = html
    .replace(/<script[\s\S]*?<\/script>/gi, " ")
    .replace(/<[^>]+>/g, " ")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, " ")
    .trim()
    .split(/\s+/);
  const out = new Set();
  for (let i = 0; i <= text.length - 6; i++) out.add(text.slice(i, i + 6).join(" "));
  return out;
}
function jaccard(a, b) {
  let inter = 0;
  for (const item of a) if (b.has(item)) inter += 1;
  const union = a.size + b.size - inter;
  return union === 0 ? 1 : inter / union;
}
const overlap = jaccard(shingles(stockport.html), shingles(manchester.html));
const overlapBolton = jaccard(shingles(stockport.html), shingles(bolton.html));
ok(overlap < 0.55, `stockport/manchester overlap ${overlap.toFixed(3)}`);
ok(overlapBolton < 0.55, `stockport/bolton overlap ${overlapBolton.toFixed(3)}`);

for (const page of [stockport, manchester, bolton, hub, gasPage]) {
  const scored = scoreHtml(page.html, { local: ["stockport", "manchester", "bolton", "greater manchester", "greater"] });
  ok(scored.score === 100, `${page.path} score ${scored.score} ${JSON.stringify(scored.checks)} words=${scored.words} title=${scored.title.length} meta=${scored.metaLength}`);
  ok((page.html.match(/<h1[\s>]/gi) || []).length === 1, `one h1 ${page.path}`);
}

const small = spec.services.find((row) => row.slug === "pat-testing");
for (const service of [electrical, small]) {
  const seenTitle = new Set();
  const seenMeta = new Set();
  for (let n = 0; n < spec.per_service; n++) {
    const decoded = decodeVariant(service, spec, n);
    const title = fitTitle({
      stemLabel: decoded.stem.label,
      stemSlug: decoded.stem.slug,
      aud: decoded.audience.title,
      mod: decoded.modifier.title,
      scope: decoded.scope.title,
      intent: decoded.intent.title,
      place: "Greater Manchester",
    });
    seenTitle.add(title);
    const page = n < 30 ? renderVariantPage({ spec, keyword: variantSlug(service.slug, decoded), town: "" }) : null;
    if (page) seenMeta.add(page.description);
  }
  ok(seenTitle.size === spec.per_service, `${service.slug} hub titles ${seenTitle.size}`);
  ok(seenMeta.size === 30, `${service.slug} meta sample ${seenMeta.size}`);
}

const part0 = renderVariantSitemap(spec, 0);
ok(part0 && part0.includes(`<loc>https://icomplypropertyservices.co.uk${samples.paths["0"]}</loc>`), "sitemap part 0 starts at global 0");
ok((part0.match(/<loc>/g) || []).length === spec.chunk, `sitemap part 0 size ${(part0.match(/<loc>/g) || []).length}`);
const lastPart = samples.parts - 1;
const lastXml = renderVariantSitemap(spec, lastPart);
const lastCount = (lastXml.match(/<loc>/g) || []).length;
const remainder = samples.total - lastPart * spec.chunk;
ok(lastCount === remainder, `last sitemap part ${lastCount} != ${remainder}`);
ok(lastXml.includes(`<loc>https://icomplypropertyservices.co.uk${samples.paths[String(samples.total - 1)]}</loc>`), "last url present");
ok(renderVariantSitemap(spec, samples.parts) === null, "out of range sitemap is null");

if (fail) {
  console.log(`FAIL ${fail}`);
  process.exit(1);
}
console.log("PASS");
