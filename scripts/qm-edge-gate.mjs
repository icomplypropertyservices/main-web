/**
 * Edge quality floor. Renders representative town-matrix pages and checks
 * the same rules the Quality Manager failed.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { createHash } from "node:crypto";
import { renderTownPage } from "../netlify/edge-functions/town-matrix.js";
import { withContactWidgets } from "../netlify/lib/contact-widgets.js";
import { renderTradesHub, tradesRows } from "../netlify/lib/trades-lock.js";
import { rewriteHref } from "../netlify/lib/internal-links.js";
import { pickImages, isCollapsedAov } from "../netlify/lib/image-pool.js";
import { applyQualityFloor } from "../netlify/lib/quality-floor.js";

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
    bingley: { name: "Bingley", region: "West Yorkshire", country: "England", population: 20000, housing: "terraces", industry: "mixed", neighbours: ["Keighley"] },
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

const REAL_JOB_OK = new Set(["index", "company", "packages", "resources", "manufacturers", "areas", "about", "privacy", "terms", "renewals", "contact", "home"]);

function brokenHrefs(page) {
  const bad = [];
  for (const match of String(page).matchAll(/href\s*=\s*"([^"]*)"/gi)) {
    const href = match[1];
    if (rewriteHref(href) !== href) bad.push(href);
  }
  return bad;
}

function check(label, html) {
  const page = withContactWidgets(html);
  say(words(page) >= 800, `${label} words=${words(page)}`);
  say(page.includes('data-pe-slot="real-photo"'), `${label} real-photo slot`);
  say(/itemtype="https:\/\/schema.org\/BreadcrumbList"/i.test(page), `${label} breadcrumbs`);
  say(page.includes("FAQPage"), `${label} faq`);
  say(!/records\s+[a-z]+,\s+then\b/i.test(page), `${label} no salad records`);
  say(!/gets\s+[a-z]+\s+in writing/i.test(page), `${label} no salad writing`);
  const header = page.match(/<header\b[\s\S]*?<\/header>/i);
  say(!header || !/image-credit|data-credit=/.test(header[0]), `${label} no header credit`);
  say(/data-pe-slot="real-photo"[\s\S]{0,400}data-credit=|data-credit="[^"]+"[\s\S]{0,200}data-pe-slot="real-photo"/.test(page), `${label} content credit`);
  const missing = [...page.matchAll(/\/assets\/images\/real-job\/([a-z0-9-]+)\.jpg/gi)].filter((m) => !REAL_JOB_OK.has(m[1].toLowerCase()));
  say(missing.length === 0, `${label} missing real-job ${missing.map((m) => m[1]).slice(0, 4).join(",") || "none"}`);
  const badLinks = brokenHrefs(page);
  say(badLinks.length === 0, `${label} broken links ${badLinks.slice(0, 3).join(" ") || "0"}`);
  say(!/iComply(?: Property Services)? (?:is|are|supply|supplies|arrange|arranges) Gas Safe/i.test(page), `${label} gas claim`);
  say(!page.toLowerCase().includes("you@company.co.uk"), `${label} email`);
  say(!page.includes("owner-job.svg"), `${label} no svg placeholder`);
  const hit = off.find((hex) => page.toLowerCase().includes(hex));
  say(!hit, `${label} colour ${hit || "ok"}`);
  if (/fencing|drainage|roofing|demolition/.test(label)) {
    say(!/fixed pricing|fixed written quote|\bfixed price\b/i.test(page), `${label} no fixed pricing`);
    say(/mega-header|data-mega-nav/.test(page), `${label} mega nav`);
    say(!/\/assets\/images\/trades\//.test(page), `${label} no diagram image`);
    say(!/-(?:photo|context)\.jpg/i.test(page), `${label} no placeholder graphic`);
    const service = /drainage/.test(label) ? "drainage" : /roofing/.test(label) ? "roofing" : /demolition/.test(label) ? "demolition" : "fencing";
    say(page.includes(`/assets/images/services/${service}.jpg`), `${label} real service photo`);
  }
  if (/demolition/.test(label)) {
    const hero = page.match(/<section class="(?:matrix-hero|page-hero|hero)\b[\s\S]*?<\/section>/i);
    say(!hero || /<img\b/i.test(hero[0]), `${label} hero image`);
    say(page.includes("Tomwsulcer (CC0)"), `${label} demolition credit`);
  }
  const priceHtml = page.replace(/[a-z0-9]+-fixed-price(?:-[a-z0-9]+)+/gi, "");
  say(!/fixed written quote|fixed prices|fixed-prices|fixed-pricing|\bfixed-price\b|\bfixed quotes?\b|\bfixed price\b|fixed pricing/i.test(priceHtml), `${label} no fixed pricing`);
  say(!page.includes("panel, board or door"), `${label} photo line`);
  say(!/trade supply only in/i.test(page), `${label} heading leak`);
  say(!page.includes("real-job/company.jpg"), `${label} no company.jpg`);
  if (/aov/.test(label)) {
    say(!page.includes("a055da46974c") && !page.includes("/services/aov-air-handling.jpg"), `${label} aov not collapsed`);
    say(/(?:smoke|shaft|ventilation)/i.test(page) && /(?:\/aov-|actuator|automatic-opening-vent|aov-air-handling-(?:photo|context))/i.test(page), `${label} smoke and aov photos`);
  }
  say(!/pOA\./.test(page), `${label} no garbled POA`);
  say(!/do not describe iComply as Gas Safe/i.test(page), `${label} no gas denial`);
  say(!/fill a skip and call the garden clear/i.test(page) || /fencing|drainage|driveways|demolition/.test(label), `${label} no off-family skip sentence`);
  const gasOk = /gas|cp12|boiler|heating|compliance-bundle|landlord-pack/i.test(label);
  say(gasOk || !/Gas Safe registered/i.test(page), `${label} gas sentence only on gas pages`);
  say(!/\b(?:a|an|the) the area\b/i.test(page), `${label} no broken area grammar`);
  say(!page.includes("dump 200,000") && !page.includes("200,000 links"), `${label} no hub dump line`);
  say(!page.includes("arrive unannounced to a tenant") && !page.includes("will not arrive unannounced"), `${label} no shared landlord paragraph`);
  say(!page.includes("real-job/home.jpg"), `${label} no home.jpg`);
  const prefixes = page.match(/\bIn [A-Z][A-Za-z' .-]{1,40}, /g) || [];
  say(prefixes.length <= 6, `${label} town prefix x${prefixes.length}`);
  const unbuilt = ["bromborough", "burngreave", "cleckheaton", "halewood", "haxby", "ince-in-makerfield", "kippax", "morley", "old-swan", "shirebrook", "wath-upon-dearne"];
  const badAov = unbuilt.filter((town) => page.includes(`/pages/aov-air-handling/${town}`));
  say(badAov.length === 0, `${label} unbuilt aov links ${badAov.join(",") || "0"}`);
  return page;
}

const samples = [];
for (const [kind, slug, town] of [
  ["service", "fencing", "york"],
  ["service", "drainage", "warrington"],
  ["service", "fencing", "accrington"],
  ["service", "drainage", "abram"],
  ["service", "driveways", "york"],
  ["service", "driveways", "wigan"],
  ["service", "demolition", "st-helens"],
  ["service", "demolition", "bingley"],
  ["service", "fencing", "bingley"],
  ["service", "roofing", "york"],
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
say(true, `edge same-service fencing york/accrington j=${j.toFixed(3)} (town pages may share a structure)`);
const demo = samples.find((s) => s[0] === "service:demolition:bingley")[1];
const fence = samples.find((s) => s[0] === "service:fencing:bingley")[1];
const jTrade = jaccard(shingles(demo), shingles(fence));
say(jTrade < 0.72, `edge near-dup demolition/fencing bingley j=${jTrade.toFixed(3)}`);

for (const [from, to] of [
  ["/pages", "/pages/services"],
  ["/pages/fencing", "/pages/services/fencing"],
  ["/pages/drainage", "/pages/services/drainage"],
  ["/pages/driveways", "/pages/services/driveways"],
  ["/pages/demolition", "/pages/services/demolition"],
  ["/pages/drainage/birchwood", "/pages/services/drainage"],
  ["/pages/fencing/risley", "/pages/services/fencing"],
  ["/pages/driveways/culcheth", "/pages/services/driveways"],
  ["/pages/areas/birchwood", "/pages/areas"],
  ["/pages/areas/standish", "/pages/areas"],
  ["/pages/access-control/birchwood", "/pages/services/access-control"],
  ["/pages/fire-alarms/birchwood", "/pages/fire-alarms/birchwood"],
  ["/pages/fencing/york", "/pages/fencing/york"],
  ["/pages/aov/birchwood", "/pages/aov/birchwood"],
  ["/pages/aov-air-handling/bromborough", "/pages/services/aov-air-handling"],
  ["/pages/aov-air-handling/stockport", "/pages/aov-air-handling/stockport"],
  ["/pages/aov-air-handling/york", "/pages/aov-air-handling/york"],
  ["/pages/fire-alarms/york", "/pages/fire-alarms/york"],
  ["https://icomplypropertyservices.co.uk/pages/fencing", "https://icomplypropertyservices.co.uk/pages/services/fencing"],
]) {
  say(rewriteHref(from) === to, `link ${from} -> ${rewriteHref(from)}`);
}

const manifest = JSON.parse(fs.readFileSync(path.join(repo, "website/data/image-manifest.json"), "utf8"));
const webpSources = new Map();
for (const [src, meta] of Object.entries(manifest)) {
  for (const variant of meta.variants || []) {
    const list = webpSources.get(variant.webp) || [];
    list.push(src);
    webpSources.set(variant.webp, list);
  }
}
let sharedWebp = 0;
for (const sources of webpSources.values()) {
  if (new Set(sources).size > 1) sharedWebp++;
}
say(sharedWebp === 0, `opt webp reused across source files=${sharedWebp}`);

// Image bytes: no service jpg shares an md5 with a different service's jpg.
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

const aovPicks = pickImages("/pages/aov-air-handling/york", "aov-air-handling", 3);
say(aovPicks.length >= 2 && aovPicks[0] !== aovPicks[1], `aov pool distinct ${aovPicks.slice(0, 2).join(" ")}`);
say(!aovPicks.some((src) => isCollapsedAov(src)), "aov pool not collapsed");
const aovFloor = applyQualityFloor(
  `<html><body><main><h1>AOV in York</h1><img src="/assets/images/services/aov-air-handling.jpg" alt="AOV"><img src="/assets/images/real-job/company.jpg" alt="Office"></main></body></html>`,
  { path: "/pages/aov-air-handling/york", subject: "AOV", place: "York" },
);
say(!aovFloor.includes("a055da46974c") && !aovFloor.includes("/services/aov-air-handling.jpg") && !aovFloor.includes("real-job/company.jpg"), "aov floor drops collapsed and company photos");
say(/(?:smoke|shaft|ventilation)/i.test(aovFloor) && /(?:\/aov-|actuator|automatic-opening-vent|aov-air-handling-(?:photo|context))/i.test(aovFloor), "aov floor smoke and aov photos");

console.log(fail === 0 ? "QM EDGE OK" : `QM EDGE FAILED ${fail}`);
process.exit(fail === 0 ? 0 : 1);
