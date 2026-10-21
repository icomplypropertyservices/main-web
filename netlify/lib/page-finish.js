/** Twin of website/includes/page-finish.php: under-H1 call/WhatsApp row + breadcrumb (visible + BreadcrumbList). */
import { heroCta } from "./hero-cta.js";

const CSS = '<style id="pf-css">.hero-cta{display:flex;flex-wrap:wrap;gap:.6rem;margin:.35rem 0 1.1rem}.hero-cta a{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:.55rem 1rem;border-radius:.55rem;color:#fff;font-weight:700;text-decoration:none}.hero-cta a.call{background:#FF6B00}.hero-cta a.wa{background:#0B1F3A}.pf-bc{font-size:.85rem;color:#333333;margin:.5rem 0}.pf-bc a{color:#333333;text-decoration:underline}.pf-bc ol{list-style:none;display:flex;flex-wrap:wrap;gap:.35rem;padding:0;margin:0}.pf-bc li+li:before{content:"›";margin-right:.35rem}</style>';
const SITE = "https://icomplypropertyservices.co.uk";
const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
const dec = (s) => String(s).replace(/<[^>]+>/g, "").replace(/&amp;/g, "&").replace(/&#0?39;|&apos;/g, "'").replace(/&quot;/g, '"').replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/\s+/g, " ").trim();
const tc = (s) => String(s).replace(/-/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());

export function finishPage(html, path) {
  let s = String(html);
  const m = s.match(/<h1\b[^>]*>([\s\S]*?)<\/h1>/i);
  if (!m) return s;
  const h1 = dec(m[1]);
  if (!path) { const c = s.match(/<link[^>]+rel="canonical"[^>]+href="https?:\/\/[^/"]+([^"]*)"/i); path = c ? c[1] : "/"; }
  const segs = String(path || "/").split("?")[0].split("/").filter(Boolean);
  if (!segs.length) return s;
  const crumbs = [["Home", "/"]];
  if (segs.length >= 3 && segs[0] === "pages") crumbs.push([tc(segs[segs.length - 2]), "/" + segs.slice(0, -1).join("/")]);
  crumbs.push([h1 || tc(segs[segs.length - 1]), "/" + segs.join("/")]);
  let add = s.includes('id="pf-css"') ? "" : CSS;
  if (!s.toLowerCase().includes("breadcrumblist")) {
    const items = crumbs.map(([n, u], i) => ({ "@type": "ListItem", position: i + 1, name: n, item: SITE + u }));
    add += `<script type="application/ld+json">${JSON.stringify({ "@context": "https://schema.org", "@type": "BreadcrumbList", itemListElement: items }).replace(/</g, "\\u003c")}</script>`;
  }
  if (add && /<\/head>/i.test(s)) s = s.replace(/<\/head>/i, add + "</head>");
  if (!/aria-label="breadcrumb"|class="[^"]*breadcrumb/i.test(s)) {
    const last = crumbs.length - 1;
    const lis = crumbs.map(([n, u], i) => (i === last ? `<li aria-current="page">${esc(n)}</li>` : `<li><a href="${esc(u)}">${esc(n)}</a></li>`)).join("");
    s = s.replace(/<h1\b/i, `<nav aria-label="Breadcrumb" class="pf-bc breadcrumb"><ol>${lis}</ol></nav><h1`);
  }
  if (!s.includes('data-cta="hero"')) s = s.replace(/<\/h1>/i, "</h1>" + heroCta(h1));
  const og = s.match(/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i);
  let hasSlot = /real[- ]photo|photo-slot/i.test(s);
  if (!hasSlot) {
    const t = s.replace(/(<body\b[\s\S]*?<h1\b[\s\S]*?)<img\b(?![^>]*(?:logo|\/brand\/))/i, '$1<img data-photo-slot="real-photo"');
    if (t !== s) { s = t; hasSlot = true; }
  }
  if (!hasSlot && og) {
    const src = og[1].replace(/^https?:\/\/[^/]+/, "");
    const fig = `<figure data-pe-slot="q2-image-hero" data-photo-slot="real-photo" class="pf-hero" style="margin:0 0 1.1rem"><img src="${esc(src)}" alt="${esc(h1)}" width="1200" height="630" loading="eager" style="width:100%;height:auto;min-height:200px;max-height:420px;object-fit:cover;border-radius:.6rem"><figcaption style="font-size:.8rem;color:#555555;margin-top:.3rem">Photo credits: <a href="/pages/photo-credits">photographers, sources and licences</a></figcaption></figure>`;
    s = s.replace(/(<div class="hero-cta" data-cta="hero">[\s\S]*?<\/div>)/, (m0) => m0 + fig);
  }
  if (/<body\b[\s\S]*<img\b/i.test(s) && !/photo credit|image credit|photo:|credit:|©|data-credit|figcaption/i.test(s)) {
    const p = '<p class="pf-credit" data-credit style="font-size:.8rem;color:#555555;margin:1rem 0">Photo credits: <a href="/pages/photo-credits">photographers, sources and licences</a></p>';
    s = /<\/main>/i.test(s) ? s.replace(/<\/main>/i, p + "</main>") : s.replace(/<footer\b/i, p + "<footer");
  }
  return s;
}
