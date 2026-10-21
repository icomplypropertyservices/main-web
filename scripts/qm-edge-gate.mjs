/**
 * Edge quality floor. Renders representative town-matrix pages and checks
 * the same rules the Quality Manager failed.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { renderTownPage } from "../netlify/edge-functions/town-matrix.js";
import { withContactWidgets } from "../netlify/lib/contact-widgets.js";
import { renderTradesHub, tradesRows } from "../netlify/lib/trades-lock.js";

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const services = JSON.parse(fs.readFileSync(path.join(repo, "website/data/services.json"), "utf8"));
const rows = tradesRows();
let fail = 0;
const say = (ok, msg) => {
  console.log(`${ok ? "[PASS]" : "[FAIL]"} ${msg}`);
  if (!ok) fail++;
};

const catalogue = {
  keywords: {},
  places: {
    york: { name: "York", region: "Yorkshire", country: "England", population: 210000, housing: "mixed", industry: "tourism", neighbours: ["Selby"] },
    warrington: { name: "Warrington", region: "Cheshire", country: "England", population: 210000, housing: "estates", industry: "logistics", neighbours: ["Widnes"] },
    "st-helens": { name: "St Helens", region: "Merseyside", country: "England", population: 180000, housing: "terraces", industry: "manufacturing", neighbours: ["Wigan"] },
    accrington: { name: "Accrington", region: "Lancashire", country: "England", population: 35000, housing: "terraces", industry: "mixed", neighbours: ["Burnley"] },
    abram: { name: "Abram", region: "Greater Manchester", country: "England", population: 10000, housing: "estates", industry: "mixed", neighbours: ["Wigan"] },
  },
  services: { labels: services, keywords: {} },
  nationwide: {},
  manufacturers: {},
  jobs: {},
};
for (const [slug, meta] of Object.entries(rows)) {
  if (meta.kind === "job") catalogue.jobs[slug] = { name: meta.name, service: meta.service, gas: /gas|cp12/i.test(slug), nationwide: false, focus: ["Written quote"] };
  else catalogue.keywords[slug] = { name: meta.name, service: meta.service, intro: `${meta.name} for homeowners and landlords.`, focus: ["Written quote"], gas: /gas|cp12/i.test(slug) };
}
for (const slug of ["fencing", "drainage", "driveways", "demolition"]) {
  catalogue.services.labels[slug] = services[slug] || slug;
}

function words(html) {
  const main = (String(html).split(/<main\b[^>]*>/i)[1] || html).split(/<\/main>/i)[0];
  return main.replace(/<script[\s\S]*?<\/script>/gi, " ").replace(/<style[\s\S]*?<\/style>/gi, " ").replace(/<[^>]+>/g, " ").trim().split(/\s+/).filter(Boolean).length;
}
function shingles(html) {
  const text = String(html).replace(/<script[\s\S]*?<\/script>/gi, " ").replace(/<[^>]+>/g, " ").toLowerCase().trim().split(/\s+/);
  const set = new Set();
  for (let i = 0; i + 4 < text.length; i++) set.add(text.slice(i, i + 5).join(" "));
  return set;
}
function jaccard(a, b) {
  let inter = 0;
  for (const x of a) if (b.has(x)) inter++;
  const union = a.size + b.size - inter;
  return union ? inter / union : 1;
}
const off = ["#ffb27a", "#ffb080", "#f7f4ef", "#ffd3b0", "#fff4eb", "#0f172a", "#f4f6f9"];

function check(label, html) {
  const page = withContactWidgets(html);
  say(words(page) >= 800, `${label} words=${words(page)}`);
  say(page.includes('data-real-job-photo="owner"'), `${label} photo slot`);
  say(/itemtype="https:\/\/schema.org\/BreadcrumbList"/i.test(page), `${label} breadcrumbs`);
  say(page.includes("FAQPage"), `${label} faq`);
  say(!/<img\b(?![^>]*data-credit=)[^>]*>/i.test(page), `${label} credits`);
  say(!/iComply(?: Property Services)? (?:is|are|supply|supplies|arrange|arranges) Gas Safe/i.test(page), `${label} gas claim`);
  say(!page.toLowerCase().includes("you@company.co.uk"), `${label} email`);
  say(!page.includes("owner-job.svg"), `${label} no svg placeholder`);
  const hit = off.find((hex) => page.toLowerCase().includes(hex));
  say(!hit, `${label} colour ${hit || "ok"}`);
  if (/fencing|drainage/.test(label)) say(!/fixed pricing/i.test(page), `${label} no fixed pricing`);
  return page;
}

const samples = [];
for (const [kind, slug, town] of [
  ["service", "fencing", "york"],
  ["service", "drainage", "warrington"],
  ["service", "fencing", "accrington"],
  ["service", "drainage", "abram"],
  ["service", "driveways", "york"],
  ["service", "demolition", "st-helens"],
  ["keyword", "gas-engineer", "york"],
]) {
  const input = { kind, town, catalogue };
  if (kind === "keyword") input.keyword = slug;
  else input.service = slug;
  const rendered = renderTownPage(input);
  samples.push([`${kind}:${slug}:${town}`, check(`${kind}:${slug}:${town}`, rendered.html || "")]);
}
const gasHub = renderTradesHub("gas-engineer");
if (gasHub) check("hub:gas-engineer", gasHub);

const a = samples.find((s) => s[0] === "service:fencing:york")[1];
const b = samples.find((s) => s[0] === "service:fencing:accrington")[1];
const j = jaccard(shingles(a), shingles(b));
say(j < 0.72, `edge near-dup fencing york/accrington j=${j.toFixed(3)}`);

// Image bytes: no service jpg shares an md5 with a different service's jpg.
import { createHash } from "node:crypto";
const dir = path.join(repo, "website/assets/images/services");
const byHash = new Map();
for (const name of fs.readdirSync(dir)) {
  if (!name.endsWith(".jpg")) continue;
  const hash = createHash("md5").update(fs.readFileSync(path.join(dir, name))).digest("hex");
  const stem = name.replace(/-(photo|context)\.jpg$/, "").replace(/\.jpg$/, "");
  const list = byHash.get(hash) || [];
  list.push(stem);
  byHash.set(hash, list);
}
let shared = 0;
for (const stems of byHash.values()) {
  if (new Set(stems).size > 1) shared++;
}
say(shared === 0, `service image hashes shared across services=${shared}`);

console.log(fail === 0 ? "QM EDGE OK" : `QM EDGE FAILED ${fail}`);
process.exit(fail === 0 ? 0 : 1);
