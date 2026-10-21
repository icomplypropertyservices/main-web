#!/usr/bin/env node
// Build step (after static-export.php): netlify/lib/seo-basics.js on every exported page.
// usage: node website/bin/apply-seo-basics.mjs [dist]
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
const here = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(here, "../..");
const dist = path.resolve(process.argv[2] || path.join(repo, "dist"));
const { applySeoBasics } = await import(path.join(repo, "netlify/lib/seo-basics.js"));
const { moneyCtrPage } = await import(path.join(repo, "netlify/lib/money-ctr.js"));
let seen = 0, changed = 0;
function walk(d) {
  for (const e of fs.readdirSync(d, { withFileTypes: true })) {
    const f = path.join(d, e.name);
    if (e.isDirectory()) { if (e.name !== "assets") walk(f); continue; }
    if (!/\.(php|html)$/.test(e.name)) continue;
    const html = fs.readFileSync(f, "utf8");
    if (!/<html/i.test(html.slice(0, 2000))) continue;
    seen++;
    let p = "/" + path.relative(dist, f).replace(/\.(php|html)$/, "").replace(/\/index$/, "");
    const out = applySeoBasics(html, { authored: Boolean(moneyCtrPage(p)) });
    if (out !== html) { fs.writeFileSync(f, out); changed++; }
  }
}
walk(dist);
console.log(`apply-seo-basics: ${changed}/${seen} pages updated`);
