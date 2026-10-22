#!/usr/bin/env node
/**
 * P0 quality gate. Renders every locked hub and one town page per slug.
 * Fails unless every page has 800+ body words, a unique title, meta and H1,
 * the shared call/WhatsApp row directly under the H1, images with alt text,
 * breadcrumbs, FAQ schema, indexable robots, and none of 07471 / BAFE-backed / info@.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { renderTownPage } from "../netlify/edge-functions/town-matrix.js";
import { renderTradesHub, tradesRows, tradesWordCount } from "../netlify/lib/trades-lock.js";

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const services = JSON.parse(fs.readFileSync(path.join(repo, "website/data/services.json"), "utf8"));
const serviceSlugs = new Set(Object.keys(services));
const rows = tradesRows();
const outCsv = path.join(repo, "website/data/seo-lock/quality-gate-report.csv");

const catalogue = {
  keywords: {},
  places: {
    manchester: {
      name: "Manchester",
      region: "North West",
      country: "England",
      population: 550000,
      housing: "41% flats",
      industry: "22% professional services",
      neighbours: ["Salford", "Stockport", "Trafford"],
    },
  },
  services: { labels: {}, keywords: {} },
  nationwide: {},
  manufacturers: {},
  jobs: {},
};
for (const [slug, meta] of Object.entries(rows)) {
  catalogue.services.labels[meta.service] = meta.service;
  if (meta.kind === "job") {
    catalogue.jobs[slug] = { name: meta.name, service: meta.service, gas: false, nationwide: meta.family === "aov", focus: ["Free survey", "Written quote"] };
  } else {
    catalogue.keywords[slug] = {
      name: meta.name,
      service: meta.service,
      intro: `${meta.name} for homeowners and landlords.`,
      focus: ["Free survey", "Written quote", "Homeowners and landlords"],
      gas: false,
    };
  }
}

function proseWords(html) {
  const article = (String(html).split('id="local-copy"')[1] || html).split("</article>")[0];
  return article
    .replace(/<script[\s\S]*?<\/script>/gi, " ")
    .replace(/<[^>]+>/g, " ")
    .trim()
    .split(/\s+/)
    .filter(Boolean).length;
}

function field(html, re) {
  const m = html.match(re);
  return m ? m[1].trim() : "";
}

function imageProblems(html) {
  const problems = [];
  const imgs = [...html.matchAll(/<img\b[^>]*>/gi)].map((m) => m[0]);
  if (imgs.length < 2) problems.push("images<2");
  for (const tag of imgs) {
    if (!/\balt="[^"]+"/.test(tag)) problems.push("img-missing-alt");
    const src = (tag.match(/\bsrc="([^"]+)"/) || [])[1] || "";
    if (src.startsWith("/assets/") && !fs.existsSync(path.join(repo, "website", src))) {
      problems.push(`missing-file:${src}`);
    }
  }
  const families = [...html.matchAll(/data-image-family="([^"]+)"/g)].map((m) => m[1]);
  if (families.length && new Set(families).size > 1) problems.push("mixed-image-family");
  return problems;
}

function linkProblems(html) {
  const problems = [];
  if (!/aria-label="Breadcrumb"|class="crumbs"|breadcrumb/i.test(html)) problems.push("no-breadcrumb");
  const hrefs = [...html.matchAll(/\bhref="([^"]+)"/g)].map((m) => m[1]);
  if (hrefs.filter((h) => h.startsWith("/")).length < 8) problems.push("few-internal-links");
  for (const href of hrefs) {
    if (href.startsWith("tel:") || href.startsWith("mailto:") || href.startsWith("https://wa.me/") || href.startsWith("https://icomplypropertyservices.co.uk")) continue;
    if (!href.startsWith("/")) {
      problems.push(`external:${href.slice(0, 40)}`);
      continue;
    }
    const service = href.match(/^\/pages\/services\/([a-z0-9-]+)$/);
    if (service && !serviceSlugs.has(service[1])) problems.push(`unknown-service:${service[1]}`);
    if (/\s/.test(href)) problems.push("link-space");
  }
  return problems;
}

function check(kind, slug, html) {
  const problems = [];
  const words = proseWords(html);
  const title = field(html, /<title>([^<]*)<\/title>/i);
  const meta = field(html, /<meta name="description" content="([^"]*)"/i);
  const h1 = field(html, /<h1[^>]*>(.*?)<\/h1>/i);
  if (words < 800) problems.push(`words:${words}`);
  if (title.length < 15 || title.length > 90) problems.push(`title:${title.length}`);
  if (meta.length < 40 || meta.length > 180) problems.push(`meta:${meta.length}`);
  if (!h1) problems.push("no-h1");
  if ((html.match(/<h1[\s>]/gi) || []).length !== 1) problems.push("h1-count");
  if (!/<\/h1><div class="hero-cta" data-cta="hero">/.test(html)) problems.push("cta-not-after-h1");
  if (!html.includes('href="tel:07517806082"') || !html.includes("https://wa.me/447517806082")) problems.push("cta-targets");
  if (!html.includes("Call 07517 806082")) problems.push("cta-label");
  if (/noindex/i.test(html)) problems.push("noindex");
  if (html.includes("07471") || html.includes("BAFE-backed") || html.includes("info@")) problems.push("banned-phrase");
  if (!html.includes("FAQPage")) problems.push("no-faq-schema");
  problems.push(...imageProblems(html));
  problems.push(...linkProblems(html));
  return { kind, slug, words, title, meta, h1, problems, pass: problems.length === 0 };
}

const results = [];
const seenTitle = new Map();
const seenMeta = new Map();
const seenH1 = new Map();

for (const slug of Object.keys(rows)) {
  const hub = renderTradesHub(slug);
  const town = renderTownPage({
    kind: rows[slug].kind === "job" ? "job" : "keyword",
    keyword: slug,
    job: slug,
    town: "manchester",
    catalogue,
  });
  results.push(check("hub", slug, hub || ""));
  results.push(check("town", slug, town && town.html ? town.html : ""));
}

for (const row of results) {
  for (const [map, value, label] of [[seenTitle, row.title, "title"], [seenMeta, row.meta, "meta"], [seenH1, row.h1, "h1"]]) {
    if (!value) continue;
    if (map.has(value)) {
      row.problems.push(`dup-${label}:${map.get(value)}`);
      row.pass = false;
    } else {
      map.set(value, `${row.kind}:${row.slug}`);
    }
  }
}

const shop = fs.readFileSync(path.join(repo, "website/shop/fire/index.html"), "utf8");
const shopProblems = [];
if (shop.includes("07471") || shop.includes("info@") || shop.includes("BAFE-backed")) shopProblems.push("banned-phrase");
if (!/<\/h1><div class="hero-cta" data-cta="hero">/.test(shop)) shopProblems.push("cta-not-after-h1");
if (!shop.includes("07517 806082") || !shop.includes("tel:07517806082")) shopProblems.push("phone");
results.push({ kind: "shop", slug: "fire", words: tradesWordCount(shop), title: "Fire supplies", meta: "", h1: "Fire supplies", problems: shopProblems, pass: shopProblems.length === 0 });

const words = results.filter((r) => r.kind !== "shop").map((r) => r.words).sort((a, b) => a - b);
const min = words[0] || 0;
const median = words.length ? words[Math.floor(words.length / 2)] : 0;
const failed = results.filter((r) => !r.pass);
const lines = ["kind,slug,pass,words,problems"];
for (const row of results) {
  const problems = row.problems.join("|").replaceAll('"', "'");
  lines.push(`${row.kind},${row.slug},${row.pass ? "pass" : "fail"},${row.words},"${problems}"`);
}
fs.writeFileSync(outCsv, lines.join("\n") + "\n");
console.log(`quality-gate pages=${results.length} failed=${failed.length} min_words=${min} median_words=${median}`);
if (failed.length) {
  for (const row of failed.slice(0, 25)) {
    console.error(`FAIL ${row.kind} ${row.slug} words=${row.words} ${row.problems.slice(0, 6).join(",")}`);
  }
  process.exit(1);
}
console.log("quality-gate OK");
