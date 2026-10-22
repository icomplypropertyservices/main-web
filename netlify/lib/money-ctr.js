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

// GSC rankings upgrade 22 Oct (scripts/gsc-2026-10-22/build_rank_upgrade.py): wins over C1-C15 and 21 Oct.
import rankDoc from "../../website/data/gsc-upgrade-2026-10-22.json" with { type: "json" };

const PAGES = { ...(gscDoc.pages || {}), ...(doc.pages || {}), ...(rankDoc.pages || {}) };
const INBOUND = rankDoc.inbound || {};
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

/** Hub -> upgraded page links (22 Oct queue). Idempotent; skips links the page already has. */
export function applyRankInbound(path, html) {
  const list = INBOUND[path];
  if (!list || typeof html !== "string" || html.includes('data-pe-slot="rank-inbound"')) return html;
  const have = internalHrefs(html);
  const add = list.filter(([href]) => href !== path && !have.has(href));
  if (!add.length) return html;
  const nav = `<nav class="money-ctr-links" data-pe-slot="rank-inbound" aria-label="Popular local pages" style="max-width:72rem;margin:2rem auto;padding:0 1rem"><h2>Popular local pages</h2><ul>${add.map(([href, label]) => `<li><a href="${esc(href)}">${esc(label)}</a></li>`).join("")}</ul></nav>`;
  for (const anchor of ["<footer", "</body>"]) {
    const i = html.indexOf(anchor);
    if (i !== -1) return html.slice(0, i) + nav + html.slice(i);
  }
  return html;
}

/** 22 Oct pages: add Service / FAQPage JSON-LD only where the page has none. */
export function rankSchema(path, page, html) {
  if (!page || page.brief !== "GSC-2026-10-22") return "";
  const ld = (html.match(/<script type="application\/ld\+json">[\s\S]*?<\/script>/g) || []).join(" ");
  const out = [];
  const url = SITE + path;
  if (!/"@type"\s*:\s*"Service"/.test(ld)) {
    out.push({ "@context": "https://schema.org", "@type": "Service", name: String(page.title).split(" | ")[0], url, description: page.meta,
      provider: { "@type": "LocalBusiness", name: "iComply Property Services", telephone: "07517 806082", email: "icomplypropertyservices@gmail.com", url: SITE },
      areaServed: page.town || "Greater Manchester and the North West" });
  }
  if (!/"@type"\s*:\s*"FAQPage"/.test(ld) && Array.isArray(page.faqs) && page.faqs.length) {
    out.push({ "@context": "https://schema.org", "@type": "FAQPage", mainEntity: page.faqs.map(([q, a]) => ({ "@type": "Question", name: q, acceptedAnswer: { "@type": "Answer", text: a } })) });
  }
  return out.map((o) => `<script type="application/ld+json">${JSON.stringify(o).replace(/</g, "\\u003c")}</script>`).join("");
}

export function applyMoneyCtr(path, rawHtml) {
  const html = applyRankInbound(path, rawHtml);
  const page = PAGES[path];
  if (!page || typeof html !== "string") return html;
  let out = html;
  const schema = rankSchema(path, page, out);
  if (schema) out = /<\/head>/i.test(out) ? out.replace(/<\/head>/i, schema + "</head>") : out + schema;
  out = out.replace(/<title>[\s\S]*?<\/title>/i, `<title>${esc(page.title)}</title>`);
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
