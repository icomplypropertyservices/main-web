/**
 * P0-CTR-MONEY (SEO brief 2026-10-19, C1-C15).
 * Exact title/meta per money page plus, on the x town pages, an authored
 * depth section, FAQ and a related-pages block that tops unique internal
 * links up to the brief floor. Data: website/data/money-ctr-c1-c15.json.
 * The PHP twin (website/includes/money-ctr.php) applies the same data to
 * statically exported pages. Never touches robots, canonical or sitemap.
 */
import doc from "../../website/data/money-ctr-c1-c15.json" with { type: "json" };
// GSC 21 Oct upgrade queue (scripts/gsc-2026-10-21/build_upgrade_pages.py). C1-C15 win on overlap.
import gscDoc from "../../website/data/gsc-upgrade-2026-10-21.json" with { type: "json" };

const PAGES = { ...(gscDoc.pages || {}), ...(doc.pages || {}) };
const SITE = "https://icomplypropertyservices.co.uk";

function esc(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

export function internalHrefs(html) {
  const out = new Set();
  const re = /href="([^"]+)"/gi;
  let m;
  while ((m = re.exec(html))) {
    let h = m[1];
    if (h.startsWith(SITE)) h = h.slice(SITE.length) || "/";
    if (!h.startsWith("/") || h.startsWith("//")) continue;
    h = h.split("#")[0].split("?")[0];
    if (!h || h.startsWith("/assets/") || /\.(css|js|png|jpe?g|webp|svg|ico|xml|json|webmanifest)$/i.test(h)) continue;
    if (h.length > 1) h = h.replace(/\/+$/, "");
    out.add(h);
  }
  return out;
}

function setMeta(html, attr, key, value) {
  const re = new RegExp(`(<meta\\s+${attr}="${key}"\\s+content=")[^"]*(")`, "i");
  return re.test(html) ? html.replace(re, `$1${esc(value)}$2`) : html;
}

export function moneyCtrPage(path) {
  return PAGES[path] || null;
}

export function applyMoneyCtr(path, html) {
  const page = PAGES[path];
  if (!page || typeof html !== "string") return html;
  let out = html.replace(/<title>[\s\S]*?<\/title>/i, `<title>${esc(page.title)}</title>`);
  out = setMeta(out, "name", "description", page.meta);
  out = setMeta(out, "property", "og:title", page.title);
  out = setMeta(out, "property", "og:description", page.meta);
  out = setMeta(out, "name", "twitter:title", page.title);
  out = setMeta(out, "name", "twitter:description", page.meta);
  if (out.includes('data-pe-slot="money-ctr"')) return out;

  let section = `<section class="money-ctr" style="max-width:72rem;margin:2rem auto;padding:0 1rem" data-pe-slot="money-ctr" data-brief="${esc(page.brief)}">`;
  if (page.h2 && Array.isArray(page.paras)) {
    section += `<h2>${esc(page.h2)}</h2>${page.paras.map((p) => `<p>${esc(p)}</p>`).join("")}`;
  }
  if (Array.isArray(page.faqs) && page.faqs.length) {
    section += `<h2>${esc(page.faqHeading || "Common questions")}</h2>`;
    section += page.faqs.map(([q, a]) => `<details class="faq-item"><summary>${esc(q)}</summary><p>${esc(a)}</p></details>`).join("");
  }
  const have = internalHrefs(out);
  const target = Number(page.linkTarget) || 0;
  const add = [];
  for (const [href, label] of page.links || []) {
    if (have.has(href) || href === path) continue;
    const isHub = (page.hubs || []).includes(href);
    if (!isHub && target && have.size >= target) continue;
    add.push([href, label]);
    have.add(href);
  }
  if (add.length) {
    section += `<nav class="money-ctr-links" aria-label="Related pages"><h2>${esc(page.linksHeading || "Related pages")}</h2><ul>${add.map(([href, label]) => `<li><a href="${esc(href)}">${esc(label)}</a></li>`).join("")}</ul></nav>`;
  }
  section += `</section>`;
  for (const anchor of ['<section id="faq"', "</article>", "</main>", "<footer", "</body>"]) {
    const i = out.indexOf(anchor);
    if (i !== -1) return out.slice(0, i) + section + out.slice(i);
  }
  return out;
}
